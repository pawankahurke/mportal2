<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 5-Sep-03   EWB     Created.
 4-Nov-03   EWB     Target display.
 4-Nov-03   EWB     Don't show password.
31-Mar-04   EWB     Site independant download records.
25-Mar-05   EWB     Column Sorting
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title = 'Updates Download Table';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-tabs.php');
include('header.php');
include('../lib/l-head.php');


function again($debug)
{
    $self = server_var('PHP_SELF');
    $act  = "$self?ord";
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    if ($debug) {
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link($home, 'home');
        $a[] = html_link($href, 'again');
    }
    $a[] = html_link('sites.php', 'sites');
    $a[] = html_link('census.php', 'machines');
    return jumplist($a);
}



function show($txt)
{
    return ($txt == '') ? '<br>' : $txt;
}


function order($ord)
{
    switch ($ord) {
        case  0:
            return 'name, id';
        case  1:
            return 'name desc, id';
        case  2:
            return 'id';
        case  3:
            return 'id desc';
        case  4:
            return 'owner, id';
        case  5:
            return 'owner desc, id';
        case  6:
            return 'global, id';
        case  7:
            return 'global desc, id';
        case  8:
            return 'version, id';
        case  9:
            return 'version desc, id';
        case 10:
            return 'username, id';
        case 11:
            return 'username desc, id';
        case 12:
            return 'filename, id';
        case 13:
            return 'filename desc, id';
        case 14:
            return 'target, id';
        case 15:
            return 'target desc, id';
        case 16:
            return 'cmdline, id';
        case 17:
            return 'cmdline desc, id';
        case 18:
            return 'url desc, id';
        case 19:
            return 'url, id';
        case 20:
            return 'password desc, id';
        case 21:
            return 'password, id';
        default:
            return order(0);
    }
}


function display_census($db, $ord, $showpass)
{
    $rows = 0;
    $ords = order($ord);
    $sql  = "select * from Downloads\n order by $ords";
    $set  = find_many($sql, $db);
    if ($set) {
        $self = server_var('PHP_SELF');

        $o = "$self?ord";
        $name = ($ord ==  0) ? "$o=1"  : "$o=0";   // name     0/1
        $id   = ($ord ==  2) ? "$o=3"  : "$o=2";   // id       2/3
        $ownr = ($ord ==  4) ? "$o=5"  : "$o=4";   // ownr     4/5
        $glob = ($ord ==  6) ? "$o=7"  : "$o=6";   // glob     6/7
        $vers = ($ord ==  8) ? "$o=9"  : "$o=8";   // vers     8/9
        $user = ($ord == 10) ? "$o=11" : "$o=10";  // user     10/11
        $file = ($ord == 12) ? "$o=13" : "$o=12";  // file     12/13
        $targ = ($ord == 14) ? "$o=15" : "$o=14";  // targ     14/15
        $cmd  = ($ord == 16) ? "$o=17" : "$o=16";  // cmd      16/17
        $url  = ($ord == 18) ? "$o=19" : "$o=18";  // url      18/19

        $id   = html_link($id, 'Id');
        $name = html_link($name, 'Name');
        $ownr = html_link($ownr, 'Owner');
        $glob = html_link($glob, 'Global');
        $vers = html_link($vers, 'Version');
        $user = html_link($user, 'User');
        $file = html_link($file, 'File');
        $targ = html_link($targ, 'Target');
        $cmd  = html_link($cmd, 'Cmd');
        $url  = html_link($url, 'URL');
        $args = array($id, $name, $ownr, $glob, $vers, $user, $file, $targ, $cmd, $url);
        if ($showpass) $args[] = 'pass';
        $cols = safe_count($args);
        $rows = safe_count($set);
        $text = "Downloads &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $id   = $row['id'];
            $revl = $row['revision'];
            $glob = $row['global'];
            $name = show($row['name']);
            $ownr = show($row['owner']);
            $site = show($row['sitename']);
            $vers = show($row['version']);
            $user = show($row['username']);
            $pass = show($row['password']);
            $file = show($row['filename']);
            $targ = show($row['target']);
            $cmd  = show($row['cmdline']);
            $url  = show($row['url']);

            $args = array($id, $name, $ownr, $glob, $vers, $user, $file, $targ, $cmd, $url);
            if ($showpass) $args[] = $pass;
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo "<h2>No records found</h2>";
    }
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$ord = get_integer('ord', 0);
$xxx = get_integer('xxx', 0);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, '', 0, $db);


$date = datestring(time());

echo "<h2>$date</h2>";

$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;
if (!$debug) $xxx = 0;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
    echo again($debug);
    display_census($db, $ord, $xxx);
    echo again($debug);
    db_change($GLOBALS['PREFIX'] . 'core', $db);
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

echo head_standard_html_footer($authuser, $db);
