<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
17-Feb-03   EWB     Created.
20-Feb-03   EWB     Detects and reports missing saved searches.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed $legend to standard_html_header()
14-Apr-03   NL      Move debug_note line below $debug.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Debug Notify Console';
$legend = '../pub/priority.gif';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-head.php');
include('local.php');
include('../lib/l-ntfy.php');
include('../lib/l-user.php');


function userlist($db)
{
    $usr = array();
    $sql = "select distinct username from Users";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $usr[] = $row['username'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usr;
}

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

function table_header()
{
    echo "<br>\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}


function table_footer()
{
    echo "</table>\n<br clear='all'>\n<br>\n";
}


function search($sid, $db)
{
    $search = '';
    $sql = "select * from SavedSearches where id = $sid";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            while ($row = mysqli_fetch_array($res)) {
                $id      = $row['id'];
                $name    = $row['name'];
                $owner   = $row['username'];
                $scope   = ($row['global']) ? 'g' : 'l';
                $search  = "$owner($scope) [$id] $name";
            }
        }
    }
    return $search;
}

function delete_console($id, $db)
{
    $sql = "delete from Console where nid = $id";
    $res = redcommand($sql, $db);
}

function detail_console($id, $db)
{
    $row = array();
    $sql = "select * from Notifications where id = $id";
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


function sum_events($username, $db)
{
    $events = '';
    $qu  = safe_addslashes($username);
    $sql = "select count(*) from Console where username = '$qu'";
    $res = command($sql, $db);
    if ($res) {
        $cnt = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        if ($cnt > 0) {
            $events = "$cnt events";
        }
    }
    return $events;
}


function events($nid, $db)
{
    $events = "";
    $sql = "select * from Console where nid = $nid";
    $result = command($sql, $db);
    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            $owners = array();
            $times  = array();
            $events = "";
            while ($row = mysqli_fetch_array($result)) {
                $owner = $row['username'];
                $time  = $row['servertime'];
                if (isset($owners[$owner]))
                    $owners[$owner]++;
                else
                    $owners[$owner] = 1;
                if (!isset($times[$owner]))
                    $times[$owner] = $time;
                elseif ($times[$owner] < $time)
                    $times[$owner] = $time;
            }
            foreach ($owners as $key => $data) {
                $time    = date("D M d H:i:s", $times[$key]);
                $events .= "$time $key ($data)<br>";
            }
        }
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
    }
    return $events;
}

function debug_console($ord, $owners, $priorities, $db)
{
    $now  = time();
    $num  = 0;
    $gbl  = 0;
    $lcl  = 0;
    $invd = 0;
    $disb = 0;
    if ($owners) {
        table_header();
        foreach ($owners as $key => $user) {
            $events = sum_events($user, $db);

            if ($events) {
                $args = array($user, $events);
                color_data($args, 'white', 0);
            }
        }
        table_footer();
    }
    switch ($ord) {
        case  0:
            $order = 'name, username, global, last_run';
            break;
        case  1:
            $order = 'name desc, username, last_run';
            break;
        case  2:
            $order = 'enabled desc, priority, name, username';
            break;
        case  3:
            $order = 'last_run desc, name, username';
            break;
        case  4:
            $order = 'enabled desc, name, username';
            break;
        case  5:
            $order = 'username, name, enabled, priority';
            break;
        default:
            $order = 'name, username, last_run';
            break;
    }

    $sql = "select * from Notifications order by $order";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            $self = server_var('PHP_SELF');
            table_header();
            $color = 'white';
            $head  = array('name', 'owner', 'id', 'events', 'action');
            color_data($head, 'white', 1);
            while ($row = mysqli_fetch_array($res)) {
                $id       = $row['id'];
                $name     = $row['name'];
                $user     = $row['username'];
                $glob     = $row['global'];
                $priority = $row['priority'];
                $enabled  = $row['enabled'];
                $last     = $row['last_run'];
                $sid      = $row['search_id'];

                $events   = events($id, $db);

                if ($enabled == 1) {
                    $enab  = '';
                    $color = $priorities[$priority];
                } else {
                    if ($enabled) {
                        $enab  = '(i)';
                        $color = 'grey';
                        $invd++;
                    } else {
                        $enab  = '(d)';
                        $color = 'white';
                        $disb++;
                    }
                }

                $created  = @$row['created'];
                $modified = @$row['modified'];

                $scop = ($glob) ? 'g' : 'l';

                if ($glob)
                    $gbl++;
                else
                    $lcl++;

                if ($events == '') {
                    $events = '<br>';
                }
                $act    = "$self?id=$id&action";
                $delete = "<a href='$act=delete'>delete</a>";
                $detail = "<a href='$act=detail'>detail</a>";
                $action = "$delete<br>$detail";
                $owner  = "$user($scop)$enab";
                $args   = array($name, $owner, $id, $events, $action);
                color_data($args, $color, 0);
            }

            table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $msg = "$num notifications found, $gbl global, $lcl local";
    if ($disb) $msg .= ", $disb disabled";
    if ($invd) $msg .= ", $invd invalid";
    echo "<h2>$msg.</h2>";
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo standard_html_header($title, $comp, $authuser, 0, 0, $legend, $db);


$date = datestring(time());

echo "<h2>$date</h2>";

$ord    = intval(get_argument('ord', 0, 0));
$id     = intval(get_argument('id', 0, 0));
$priv   = intval(get_argument('priv', 0, 1));
$dbg    = intval(get_argument('debug', 0, 1));
$action = trim(get_argument('action', 0, 'display'));
$user   = user_data($authuser, $db);
$debug  = @($user['priv_debug']) ? $dbg  : 0;
$admin  = @($user['priv_admin']) ? $priv : 0;
$owners = userlist($db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'event', $db);
    switch ($action) {
        case 'display':
            debug_console($ord, $owners, $priorities, $db);
            break;
        case 'delete':
            delete_console($id, $db);
            break;
        case 'detail':
            detail_console($id, $db);
            break;
    }
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

$self  = server_var('PHP_SELF');
$again = "<a href='$self'>again<a>";
$home  = "<a href='debug.php'>home<a>";

echo "<br>\n&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$again&nbsp;&nbsp;$home<br><br>\n";

echo head_standard_html_footer($authuser, $db);
