<?php

/*
Revision history:

Date        Who     What
----        ---     ----
22-Aug-02   EWB     Created
26-Aug-02   EWB     Supply 2nd arg to mysql_select_db
26-Aug-02   EWB     Better explanations.
26-Aug-02   EWB     Requires admin access to purge.
28-Aug-02   EWB     Minor phrasing changes, Alex suggestions.
30-Aug-02   EWB     Audit trail for database purge.
 9-Sep-02   EWB     Link back to machines page.
20-Sep-02   EWB     Giant refactoring
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Uses standard header
13-Jan-03   EWB     Removed 'Home' link.
13-Jan-03   EWB     Don't require register_globals
20-Jan-03   AAM     Removed reference to crevl.
23-Jan-03   EWB     More factoring, minimal quotes.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
 6-Mar-03   NL      Add calls to config_navigate() & config_info()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz $debug non-existant
13-May-05   EWB     Works with new gconfig database.
12-Oct-05   BTE     Updated to work with new gconfig tables.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/


ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('local.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');

function para($txt)
{
    return "<p>$txt</p>\n";
}

function purge_table($name, $db)
{
    $sql = "delete from $name";
    redcommand($sql, $db);
}


function gconfig_names()
{
    return array(
        'Cache',
        'Descriptions',
        'Descriptions_cksum',
        'GroupSettings',
        'GroupSettings_cksum',
        'InvalidVars',
        'LegacyCache',
        'Revisions',
        'Scrips',
        'Scrips_cksum',
        'SemClears',
        'SemClears_cksum',
        'ValueMap',
        'ValueMap_cksum',
        'VarValues',
        'VarValues_cksum',
        'VarVersions',
        'VarVersions_cksum',
        'Variables',
        'Variables_cksum'
    );
}


function purge_all($auth, $name, $db)
{
    if (mysqli_select_db($db, $name)) {
        $set = gconfig_names();
        reset($set);
        foreach ($set as $key => $tab) {
            purge_table($tab, $db);
        }
        $text = "config: gconfig tables purged by $auth";
        debug_note($text);
        logs::log(__FILE__, __LINE__, $text, 0);
        echo para('The database has been cleared.');
    }
    echo para(html_link('index.php', 'Home'));
}


/*
 |  This will clear all the current site administration database
 |  tables.  All of the current database values would be lost.
 |  This will also clear all of the browser interface information
 |  as well.
 */

function confirm($self)
{
    $yref = "$self?yes=1";
    $nref = 'index.php';
    $ylnk = html_link($yref, 'Yes, clear it.');
    $nlnk = html_link($nref, "No, don't do anything");

    echo <<< WHAT

        <p>
          This will clear all the current site administration
          database<br>tables.&nbsp;
          All of the current database values would be lost.<br>
          This will also clear all of the browser interface
          information<br>as well.
        </p>

        <p>
          Would you like to clear the site administration database?
        </p>

        <p>$ylnk</p>
        <p>$nlnk</p>
WHAT;
}

function noaccess()
{
    echo para('This operation requires administative access.');
    echo para('Permission denied.');
    echo para(html_link('index.php', 'Home'));
}


/*
    |  Main program
    */

$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();

$yes  = get_integer('yes', 0);

$user = user_data($auth, $db);
$name = 'Purge Site Administration Database';
$msg  = ob_get_contents();          // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, $auth, 0, 0, 0, $db);

$priv_admin = @($user['priv_admin']) ? 1 : 0;
$priv_debug = @($user['priv_debug']) ? 1 : 0;
$file = $comp['file'];
$self = $comp['self'];

$debug = $priv_debug;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_note("debug:$debug admin:$priv_admin yes:$yes auth:$auth self:$self");

if ($priv_admin) {
    if ($yes) {
        /* Purge the GConfig tables only! */
        purge_all($auth, 'core', $db);
    } else {
        confirm($self);
    }
} else {
    noaccess();
}
echo head_standard_html_footer($auth, $db);
