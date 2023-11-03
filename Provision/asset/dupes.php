<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
18-Feb-03   EWB     Created.
19-Feb-03   EWB     Table header, id, count.
20-Feb-03   EWB     Port to 3.1 tree.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move debug_note line below $debug. 
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Check for Duplicates';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-rcmd.php');
include('../lib/l-rprt.php');
include('../lib/l-user.php');


function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if ($args) {
        echo "<tr>\n";
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




function purge_dupe($data, $db)
{
    $self  = server_var('PHP_SELF');
    $name  = $data['name'];
    $table = $data['table'];
    $type  = $data['type'];
    $user  = $data['user'];
    $qname = safe_addslashes($name);
    $sql = '';
    if ($type == 'global') {
        $sql = "select * from $table where name = '$qname' and global = 1";
    }
    if ($type == 'local') {
        $sql = "select * from $table where name = '$qname' and username = '$user'";
    }
    if ($sql) {
        $res = command($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_array($res)) {
                $name   = $row['name'];
                $user   = $row['username'];
                $id     = $row['id'];
                $scope  = ($row['global']) ? 'g' : 'l';
                $owner  = "$user($scope)";
                $act    = "$self?id=$id&table=$table&action";
                $delete = "<a href='$act=delete'>delete</a>";
                $detail = "<a href='$act=detail'>detail</a>";
                $action = "$delete<br>$detail";
                $args   = array($table, $name, $id, $owner, $action);
                table_data($args, 0);
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}

/*
    |  There are two kinds of duplicate records.
    |
    |    1. two global records with the same name.
    |    2. two records with the same name and the same owner.
    */


function check_dupes(&$dupes, $table, $db)
{
    $sql = "select * from $table where global = 1 order by name";
    $res = redcommand($sql, $db);
    if ($res) {
        $pname = '';
        while ($row = mysqli_fetch_array($res)) {
            $name = $row['name'];
            $user = $row['username'];
            if ($name == $pname) {
                $temp['name']  = $name;
                $temp['table'] = $table;
                $temp['type']  = 'global';
                $temp['user']  = $user;
                $dupes[] = $temp;
            }
            $pname = $name;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $sql = "select * from $table order by name, username";
    $res = redcommand($sql, $db);
    if ($res) {
        $pname = '';
        $puser = '';
        while ($row = mysqli_fetch_array($res)) {
            $name = $row['name'];
            $user = $row['username'];
            if (($name == $pname) && ($user == $puser)) {
                $temp['name']  = $name;
                $temp['table'] = $table;
                $temp['type']  = 'local';
                $temp['user']  = $user;
                $dupes[] = $temp;
            }
            $pname = $name;
            $puser = $user;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}


function check_duplicates($db)
{
    $dupes = array();
    check_dupes($dupes, 'AssetReports', $db);
    check_dupes($dupes, 'AssetSearches', $db);
    $n = safe_count($dupes);
    if ($n > 0) {
        table_header();
        $head = array('Table', 'Name', 'Id', 'Owner', 'Action');
        table_data($head, 1);
        reset($dupes);
        foreach ($dupes as $key => $data) {
            purge_dupe($data, $db);
        }
        table_footer();
    } else {
        $n = 'No';
    }
    $msg = "$n duplicate record(s) found.";
    $msg = fontspeak($msg);
    echo "<br><br>\n$msg<br>\n";
}


function delete_record($id, $table, $db)
{
    $sql = "delete from $table where id = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        $msg = "Record $id removed from $table.";
        $msg = fontspeak($msg);
        echo "$msg<br>\n";
    }
    check_duplicates($db);
}

function detail_record($id, $table, $db)
{
    $row = array();
    $sql = "select * from $table where id = $id";
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
    check_duplicates($db);
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


$date = datestring(time());

echo "<h2>$date</h2>";

$ord    = intval(get_argument('ord', 0, 0));
$id     = intval(get_argument('id', 0, 0));
$priv   = intval(get_argument('priv', 0, 1));
$dbg    = intval(get_argument('debug', 0, 0));
$action = trim(get_argument('action', 0, 'display'));
$table  = trim(get_argument('table', 0, ''));
$user   = user_data($authuser, $db);
$debug  = @($user['priv_debug']) ? $dbg  : 0;
$admin  = @($user['priv_admin']) ? $priv : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    switch ($action) {
        case 'display':
            check_duplicates($db);
            break;
        case 'delete':
            delete_record($id, $table, $db);
            break;
        case 'detail':
            detail_record($id, $table, $db);
            break;
    }
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

$self  = server_var('PHP_SELF');
$again = "<a href='$self'>again<a>";
$home  = "<a href='index.php'>home<a>";

echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$again&nbsp;&nbsp;$home<br><br>\n";

echo head_standard_html_footer($authuser, $db);
