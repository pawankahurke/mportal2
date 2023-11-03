<?php

/*
Revision history:

Date        Who     What
----        ---     ----
14-Feb-03   EWB     Created.
21-Feb-03   EWB     Allow delete AssetSearches
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
 8-Apr-03   EWB     Handle 'not contain' expression.
 6-Oct-03   EWB     Show displayfields.
 9-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

$title  = 'Debug Asset Queries';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');
include('../lib/l-tabs.php');


function color_data($args, $color, $head)
{
    $td = ($head) ? 'th' : 'td';
    if ($args) {
        echo "<tr bgcolor='$color'>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


// same as find_many, but not redcommand

function find_some($sql, $db)
{
    $tmp = array();
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $tmp[] = $row;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $tmp;
}

function detail_query($id, $db)
{
    $sql = "select * from AssetSearches where id = $id";
    $row = find_one($sql, $db);
    if ($row) {
        echo table_header();
        reset($row);
        foreach ($row as $key => $data) {
            $valu = ($data == '') ? '<br>' : $data;
            $args = array($key, $valu);
            color_data($args, 'white', 0);
        }
        echo table_footer();
    }
}

function delete_query($id, $db)
{
    $sql = "delete from AssetSearches where id = $id";
    $res = redcommand($sql, $db);
    $sql = "delete from AssetSearchCriteria where assetsearchid = $id";
    $res = redcommand($sql, $db);
}


function report($id, $db)
{
    $rep = '';
    $sql = "select * from AssetReports where searchid = $id";
    $tmp = find_some($sql, $db);
    if ($tmp) {
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $id   = $row['id'];
            $name = $row['name'];
            $user = $row['username'];
            $scop = ($row['global']) ? 'g' : 'l';
            $enab = ($row['enabled']) ? '' : 'd';
            $rep .= "$user($scop)$enab [$id] $name<br>";
        }
    }
    return $rep;
}


function criteria($id, $db)
{
    $crit = '';
    $sql = "select * from AssetSearchCriteria where assetsearchid = $id";
    $tmp = find_some($sql, $db);
    if ($tmp) {
        $compare = array('unknown', '=', '!=', 'contains', 'begins with', 'ends with', '<', '>', '<=', '>=', 'not contain');
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $fld   = $row['fieldname'];
            $val   = $row['value'];
            $op    = $row['comparison'];
            $cmp   = $compare[$op];
            $text  = htmlspecialchars("'$fld' $cmp '$val'");
            $crit .= "$text<br>";
        }
    }
    if ($crit == '') $crit = '<br>';
    return $crit;
}


function display($df)
{
    $list = array();
    $temp = explode(':', $df);
    reset($temp);
    foreach ($temp as $xxx => $name) {
        if ($name) {
            $list[] = $name;
        }
    }
    sort($list);
    return join("<br>\n", $list);
}


function debug_query($ord, $db)
{
    $num = 0;
    $gbl = 0;
    $lcl = 0;
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
            $order = 'modified, name, username,global';
            break;
        case  7:
            $order = 'created, name, username, global';
            break;
        default:
            $order = 'name, username, global';
            break;
    }

    $sql = "select * from AssetSearches order by $order";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            echo table_header();
            $self = server_var('PHP_SELF');
            $head = explode('|', 'Name|Owner|Id|Display|Criteria|Report|Action');
            color_data($head, 'white', 1);

            while ($row = mysqli_fetch_assoc($res)) {
                $id   = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $glob = $row['global'];
                $df   = $row['displayfields'];
                $scop = ($glob) ? 'g' : 'l';

                $disp = display($df);
                $rprt = report($id, $db);
                $crit = criteria($id, $db);

                $act    = "$self?id=$id&action";
                $delete = html_link("$act=delete", 'delete');
                $detail = html_link("$act=detail", 'detail');

                if ($rprt == '') {
                    $rprt = '<br>';
                    $color = 'lemonchiffon';
                    $action = "$delete<br>$detail";
                } else {
                    $color  = 'aquamarine';
                    $action = $detail;
                }

                if ($glob)
                    $gbl++;
                else
                    $lcl++;

                $owner  = "$user($scop)";
                $args   = array($name, $owner, $id, $disp, $crit, $rprt, $action);
                color_data($args, $color, 0);
            }

            echo table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    echo "<h2>$num queries found, $gbl global, $lcl local.</h2>";
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
$dbg    = intval(get_argument('debug', 0, 1));
$action = trim(get_argument('action', 0, 'display'));
$db    = db_connect();
$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? $dbg : 0;
$admin = @($user['priv_admin']) ? $priv : 0;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    switch ($action) {
        case 'display':
            debug_query($ord, $db);
            break;
        case 'delete':
            delete_query($id, $db);
            break;
        case 'detail':
            detail_query($id, $db);
            break;
    }
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

$self  = server_var('PHP_SELF');
$again = html_link($self, 'again');
$home  = html_link('debug.php', 'home');

echo "<br>\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$again&nbsp;&nbsp;$home<br><br>\n";

echo head_standard_html_footer($authuser, $db);
