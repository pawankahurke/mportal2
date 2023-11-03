<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Nov-02   NL      create file
22-Nov-02   NL      filter sitename to authuser access
22-Nov-02   NL      uncomment Add and Delete functionality
22-Nov-02   NL      alter_priv (required to Add, Edit or Delete)
22-Nov-02   NL      move redcommand to common.php
22-Nov-02   NL      remove fontspeak
22-Nov-02   NL      change display() arguments to a cols array
 3-Dec-02   NL      change title
 5-Dec-02   NL      view_machines in Action column should always be displayed
 5-Dec-02   NL      "There are no sites listed" else block was in wrong place
 6-Dec-02   EWB     Reorginization Day
16-Dec-02   EWB     Fixed short php tags
08-Feb-03   AAM     Removed extraneous global.
10-Feb-03   EWB     Uses sandbox libraries.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     changed database name to 'swupdate'.
 2-Apr-03   EWB     show number of machines for each site.
25-Apr-03   EWB     site filters.
29-Apr-03   EWB     l-cust not needed.
30-Apr-03   EWB     user site filters.
30-Mar-04   EWB     Better navigation tags.
31-Mar-04   EWB     Group assign command.
 1-Apr-04   EWB     Add link is now debug only.
24-Oct-05   BTE     Renamed update_census to SULS_UpdateCensus.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title   = 'Set Site Version';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('header.php');
include('../lib/l-head.php');


function plural($n, $name)
{
    $word = ($n == 1) ? $name : $name . 's';
    return "$n $word";
}


/*
    |  Note that the add sites command is no longer
    |  really needed, since new sites are now created 
    |  automatically at logging time.  However, I'm
    |  keeping it around as a debug command, just
    |  to create unused sites for testing.
    */

function again($alter_priv, $dpriv)
{
    $a = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $su  = 'su-alter.php?action';
    if ($alter_priv) {
        $a[] = html_link("$su=grp", 'group');
    }
    $a[] = html_link('#sites', 'sites');
    if ($dpriv) {
        $a[] = html_link("$su=add", 'add');
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link('census.php', 'debug');
        $a[] = html_link($href, 'again');
        $a[] = html_link($home, 'home');
    }
    return jumplist($a);
}

function table_data($rows, $head)
{
    $td = ($head) ? "th" : "td";
    if ($rows) {
        echo "<tr>\n";
        reset($rows);
        foreach ($rows as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


function SULS_UpdateCensus($access, $db)
{
    $census = array();
    $sql  = "select * from UpdateMachines\n";
    $sql .= " where sitename in ($access)";
    $list = find_many($sql, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $site = $row['sitename'];
            $census[$site] = 0;
        }
        reset($list);
        foreach ($list as $key => $row) {
            $site = $row['sitename'];
            $census[$site]++;
        }
    }
    return $census;
}



function table_header($args)
{
    echo '<table border="2" align="left" cellspacing="2" cellpadding="2" width="100%">' . "\n";
    table_data($args, 1);
}

function display($id, $alter_priv, $cols)
{
    $args  = array();

    $edit = html_link("su-alter.php?id=$id&action=edit", '[edit]');
    $del  = html_link("su-act.php?id=$id&action=conf", '[delete]');
    $mach = html_link("mu-list.php?siteid=$id", '[view machines]');

    if ($alter_priv)
        $action_string = "$edit<br>\n$del<br>\n$mach";
    else
        $action_string = $mach;

    $args[] = $action_string;

    reset($cols);
    foreach ($cols as $k => $v) {
        $args[] = $v;
    }
    table_data($args, 0);
}

function table_end($db)
{
    echo '</table><br clear="all"><br>' . "\n\n";
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, 0, 0, $db);

$alter_priv  = @($user['priv_updates']) ? 1 : 0;
$debug_priv  = @($user['priv_debug']) ?   1 : 0;
$filter      = @($user['filtersites']) ?  1 : 0;
$site_list   = find_sites($authuser, $filter, $db);
$dbg         = get_integer('debug', 1);
db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
$debug = ($debug_priv) ? $dbg : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

echo mark('sites');
echo again($alter_priv, $debug_priv);

//  Get sitenames from database
if ($site_list) {
    $census = SULS_UpdateCensus($site_list, $db);
    $sql = "select * from UpdateSites\n"
        . " where sitename in ($site_list)\n"
        . " order by sitename";
    $list = find_many($sql, $db);

    if ($list) {
        $head = array('Action', 'Site Name', 'Number of machines', 'Desired Version');
        table_header($head);

        reset($list);
        foreach ($list as $key => $row) {
            $version = @trim($row['version']);
            $site    = @trim($row['sitename']);
            $id      = @intval($row['id']);
            $num     = @intval($census[$site]);

            $site    = ($site     == '') ? '<br>' : $site;
            $version = ($version  == '') ? '<br>' : $version;
            $machine = plural($num, 'machine');

            $datas = array($site, $machine, $version);
            display($id, $alter_priv, $datas);
        }
        table_end($db);
    } else {
        echo "There are no sites listed for user <b>$authuser</b>.";
    }
}


echo again($alter_priv, $debug_priv);

echo head_standard_html_footer($authuser, $db);
