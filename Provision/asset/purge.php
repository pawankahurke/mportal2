<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created
20-Sep-02   EWB     Giant refactoring
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Local Navigation
10-Dec-02   EWB     Position Independant Debug Checking
16-Jan-03   EWB     Don't require register_globals
10-Feb-03   EWB     Sandbox libaries
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move debug_note line below $debug. 
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Purge Asset Database';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('local.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');


function fontcolor($size, $color, $msg)
{
    $face = "verdana,helvetica";
    return "<font face='$face' color='$color' size='$size'>$msg</font>";
}

function newline()
{
    echo "<br>\n";
}


function purge_table($name, $db)
{
    $sql = "delete from $name";
    redcommand($sql, $db);
}

function purge_all($authuser, $db)
{
    $tables = array('Machine', 'AssetData', 'DataName');

    reset($tables);
    foreach ($tables as $key => $data) {
        purge_table($data, $db);
    }

    $real = @$GLOBALS['real_sql'];
    if ($real) {
        $msg = "asset database purged by $authuser.";
        debug_note($msg);
        logs::log(__FILE__, __LINE__, $msg, 0);
    }
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

$confirm  = get_integer('confirm', 0);
$dbg      = get_integer('debug', 1);

$user = user_data($authuser, $db);
$self = $comp['self'];
$file = $comp['file'];

$priv_admin = @$user['priv_admin'];
$priv_debug = @$user['priv_debug'];

$test_sql = ('purge.php' == $file) ? 0 : 1;
$real_sql = ('purge.php' == $file) ? 1 : 0;
$debug    = ($priv_debug) ? $dbg : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_note("debug:$debug, admin:$priv_admin, test:$test_sql, confirm:$confirm");

db_change($GLOBALS['PREFIX'] . 'asset', $db);

if ($priv_admin) {
    newline();
    newline();
    if ($confirm) {
        purge_all($authuser, $db);
        $msg  = "The database has been cleared.<br>";
        $msg .= "<br>";
        $msg .= "<br>";
        $msg .= "<a href='index.php'>Home</a><br>";
    } else {
        $y    = "$self?confirm=1";
        $n    = "index.php";
        /*
 |  This will clear all the current asset database tables.  
 |  All of the current database values would be lost.
 |  However the saved searches would not be affacted.
 */
        $msg  = "This will clear all the current asset database ";
        $msg .= "tables.<br>All of the current ";
        $msg .= "database values would be lost.<br>However the saved ";
        $msg .= "searches would not be affected.<br>";
        $msg .= "<br>";
        $msg .= "Would you like to clear the asset database?<br>";
        $msg .= "<br>";
        $msg .= "<a href='$y'>Yes, clear it.</a><br>";
        $msg .= "<br>";
        $msg .= "<a href='$n'>No, don't do anything</a><br>";
    }
} else {
    $msg  = "This operation requires administative access.<br>";
    $msg .= "<br>";
    $msg .= "Permission denied.";
    $msg .= "<br>";
    $msg .= "<a href='index.php'>Home</a><br>";
}

$msg = fontcolor(2, 'black', $msg);
echo "<p>$msg</p>\n";

newline();
newline();

echo head_standard_html_footer($authuser, $db);
