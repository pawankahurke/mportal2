<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 5-Dec-03   EWB     Created.
 9-Dec-03   EWB     Reports number of records found.
 6-Apr-05   EWB     New primary key.
 7-Apr-05   EWB     Can remove records.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
 
*/

$title = 'Scrip Cache';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-tabs.php');
include('../lib/l-gsql.php');
include('../lib/l-head.php');


function again($debug)
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $act  = "$self?ord";
    $a   = array();
    if ($debug) {
        $a[] = html_link('index.php', 'home');
    }
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link($href, 'again');
    return jumplist($a);
}


function short_date($x)
{
    $text = '<br>';
    if ($x > 0) {
        //      $date = date('m/d/y',$x);
        //      $time = date('H:i:s',$x);
        //      $text = "$date<br>$time";
        $text = date('m/d/y H:i:s', $x);
    }
    return $text;
}



function order($ord)
{
    switch ($ord) {
        case  0:
            return 'scrip, modified desc, description';
        case  1:
            return 'scrip desc, modified desc, description';
        case  2:
            return 'modified desc, scrip, description';
        case  3:
            return 'modified, scrip, description';
        case  4:
            return 'description, scrip, modified';
        case  5:
            return 'description desc, scrip, modified';
        case  6:
            return 'id';
        case  7:
            return 'id desc';
        default:
            return order(0);
    }
}


function display_cache($db, $ord)
{
    $ords = order($ord);
    $sql = "select * from EventScrips\n order by $ords";
    $set = find_many($sql, $db);
    if ($set) {
        $self = server_var('PHP_SELF');
        $o    = "$self?ord";
        $scop = ($ord == 0) ?  "$o=1"  : "$o=0";
        $when = ($ord == 2) ?  "$o=3"  : "$o=2";
        $text = ($ord == 4) ?  "$o=5"  : "$o=4";
        $indx = ($ord == 6) ?  "$o=7"  : "$o=6";

        $cmd  = "$self?act=del&id";
        $indx = html_link($indx, 'Id');
        $scop = html_link($scop, 'Scrip');
        $text = html_link($text, 'Description');
        $when = html_link($when, 'Modified');
        $args = array($scop, $text, $indx, $when, 'Action');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Scrip Cache &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        foreach ($set as $key => $row) {
            $indx = $row['id'];
            $scop = $row['scrip'];
            $text = $row['description'];
            $when = $row['modified'];
            $when = short_date($when);
            $link = html_link("$cmd=$indx", 'delete');
            $args = array($scop, $text, $indx, $when, $link);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function delete_cache($db, $id)
{
    if ($id > 0) {
        $sql = "delete from EventScrips where id = $id";
        $res = redcommand($sql, $db);
        if (affected($res, $db))
            $text = 'Record removed';
        else
            $text = 'Nothing has changed';
        echo "<p>$text.</p>\n";
    }
    display_cache($db, 0);
}


/*
    |  Main program
    */

$db = db_connect();
$auth = process_login($db);
$comp = component_installed();

$ord = get_integer('ord', 0);
$id  = get_integer('id', 0);
$act = get_string('act', 'show');
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, '', '', 0, $db);


$date = datestring(time());

echo "<h2>$date</h2>";

$user  = user_data($auth, $db);
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'event', $db);
    echo again($debug);
    if (($act == 'del') && ($id > 0))
        delete_cache($db, $id);
    else
        display_cache($db, $ord);
    echo again($debug);
    db_change($GLOBALS['PREFIX'] . 'core', $db);
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

echo head_standard_html_footer($auth, $db);
