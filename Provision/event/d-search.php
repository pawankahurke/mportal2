<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
17-Feb-03   EWB     Created.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Move debug_note line below $debug.
18-Jun-03   EWB     Slave Database.
20-Jun-03   EWB     No Slave Database.
28-Aug-03   EWB     Display creation date
28-Aug-03   EWB     Sort by creation date
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.

*/

$title  = 'Debug Event Filter';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
//  include ( '../lib/l-slav.php'  );
include('local.php');
include('../lib/l-head.php');
include('../lib/l-cnst.php');


function again()
{
    $self = server_var('PHP_SELF');
    $ord  = "$self?ord";
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('debug.php', 'home');
    $a[] = html_link("$ord=0", 'name');
    $a[] = html_link("$ord=2", 'user');
    $a[] = html_link("$ord=4", 'id');
    $a[] = html_link("$ord=7", 'create');
    $a[] = html_link("$ord=6", 'modify');
    return jumplist($a);
}

function simple_date($when)
{
    $text = '<br>';
    if ($when > 0) {
        $date = date('m/d/y', $when);
        $time = date('H:i:s', $when);
        $text = "$date<br>$time";
    }
    return $text;
}

function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if ($args) {
        echo "<tr valign=\"top\">\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}

function color_data($args, $color, $head)
{
    $td = ($head) ? 'th' : 'td';
    if ($args) {
        echo "<tr bgcolor=\"$color\" valign=\"top\">\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}

function table_header()
{
    echo "<br>\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}


function table_footer()
{
    echo "</table>\n<br clear='all'>\n<br>\n";
}


function delete_search($id, $db)
{
    $sql = "delete from SavedSearches where id = $id";
    $res = redcommand($sql, $db);
    PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
}

function report($id, $db)
{
    $reports = '';
    if ($id > 0) {
        $sql = "select * from Reports where search_list like '%,$id,%'";
        $res = command($sql, $db);
        if ($res) {
            $count = mysqli_num_rows($res);
            if ($count) {
                while ($row = mysqli_fetch_array($res)) {
                    $id        = $row['id'];
                    $name      = $row['name'];
                    $owner     = $row['username'];
                    $scope     = ($row['global']) ? 'g' : 'l';
                    $reports  .= "$owner($scope) report: $name [$id]<br>";
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $reports;
}


function notify($id, $db)
{
    $notify = '';
    if ($id > 0) {
        $sql  = "select * from Notifications\n";
        $sql .= " where search_id = $id";
        $res  = command($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res)) {
                while ($row = mysqli_fetch_array($res)) {
                    $id      = $row['id'];
                    $name    = $row['name'];
                    $owner   = $row['username'];
                    $scope   = ($row['global']) ? 'g' : 'l';
                    $notify .= "$owner($scope) notify: $name [$id]<br>";
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $notify;
}

function detail_search($id, $db)
{
    $row = array();
    $sql = "select * from SavedSearches where id = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    if ($row) {
        table_header();
        reset($row);
        foreach ($row as $key => $data) {
            $num = intval($key);

            if (($num == 0) && ($key != '0')) {
                $valu = ($data == '') ? '<br>' : $data;
                $args = array($key, $valu);
                table_data($args, 0);
            }
        }
        table_footer();
    }
}


function debug_search($ord, $db)
{
    $num = 0;
    $glob = 0;
    $locl = 0;
    $used = 0;
    $unused = 0;
    switch ($ord) {
        case  0:
            $order = 'name, username, global';
            break;
        case  1:
            $order = 'name desc, username, global';
            break;
        case  2:
            $order = 'username, name, global';
            break;
        case  3:
            $order = 'username desc, name, global';
            break;
        case  4:
            $order = 'id';
            break;
        case  5:
            $order = 'id desc';
            break;
        case  6:
            $order = 'modified desc, name, username,global';
            break;
        case  7:
            $order = 'created desc, name, username, global';
            break;
        default:
            $order = 'name, username, global';
            break;
    }

    $sql = "select * from SavedSearches\n order by $order";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            $self = server_var('PHP_SELF');
            table_header();
            $color = 'white';
            $head  = array('Name', 'User', 'Id', 'Modify', 'Create', 'Used', 'Action');
            table_data($head, 'white', 1);
            while ($row = mysqli_fetch_array($res)) {
                $id     = $row['id'];
                $name   = $row['name'];
                $user   = $row['username'];
                $global = $row['global'];
                $modify = $row['modified'];
                $create = $row['created'];

                $report = report($id, $db);
                $notify = notify($id, $db);
                $usage  = $notify . $report;

                $act    = "$self?id=$id&action";
                $delete = html_link("$act=delete", 'delete');
                $detail = html_link("$act=detail", 'detail');

                if ($usage == '') {
                    $unused++;
                    $usage  = '<br>';
                    $color  = 'lemonchiffon';
                    $action = "$detail<br>$delete";
                } else {
                    $action = $detail;
                    $color  = 'aquamarine';
                    $used++;
                }

                if ($global)
                    $glob++;
                else
                    $locl++;

                $scop   = ($global) ? 'g' : 'l';

                $mod    = simple_date($modify);
                $crt    = simple_date($create);
                $owner  = "$user($scop)";
                $args   = array($name, $owner, $id, $mod, $crt, $usage, $action);
                color_data($args, $color, 0);
            }

            table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    echo "<h2>$num searches found, $glob global, $locl local, $used used, $unused unused.</h2>";
}


/*
    |  Main program
    */

$ord  = intval(get_argument('ord', 0, 0));
$id   = intval(get_argument('id', 0, 0));
$priv = intval(get_argument('priv', 0, 1));
$dbg  = intval(get_argument('debug', 0, 1));
$mst  = intval(get_argument('master', 0, 0));

/*************************************
    $mdb = db_connect();
    if ($mst)
    {
        $sdb = false;
        $db  = $mdb;
    }
    else
    {
        $sdb = db_slave($mdb);
        if ($sdb)
        {
            db_change($GLOBALS['PREFIX'].'core',$sdb);
            $db = $sdb;
        }
        else
        {
            $db = $mdb;
        }
    }
 ************************************/

$db = db_connect();
$mdb = $db;
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);


$date = datestring(time());

echo "<h2>$date</h2>";

$action = trim(get_argument('action', 0, 'display'));
$user   = user_data($authuser, $db);
$debug  = @($user['priv_debug']) ? $dbg  : 0;
$admin  = @($user['priv_admin']) ? $priv : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
/*********************
    if ($sdb)
        debug_note("replicated database mdb:$mdb, sdb:$sdb");
    else
        debug_note("normal database");
 *********************/
if ($admin) {

    echo again();

    db_change($GLOBALS['PREFIX'] . 'event', $db);
    switch ($action) {
        case 'display':
            debug_search($ord, $db);
            break;
        case 'delete':
            delete_search($id, $db);
            break;
        case 'detail':
            detail_search($id, $db);
            break;
    }

    echo again();
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

echo head_standard_html_footer($authuser, $db);
