<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Giant refactoring.
20-Sep-02   EWB     Some library files become 8.3 ... sigh
 8-Oct-02   EWB     Squashed a a few warnings.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
13-Dec-02   EWB     Fixed short php tags
 6-Jan-03   EWB     Minimal quoting.
 6-Jan-03   EWB     Does not require php register_globals
16-Jan-03   EWB     Access to php_self without register globals
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
26-Feb-03   EWB     genconfig factored into l-gbox.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
11-Apr-03   EWB     register globals, initialize days & priority.
15-Apr-03   EWB     include l-date.php, assumeyear();
29-Mar-04   EWB     Don't show event.Events.deleted in config genbox
20-Oct-04   EWB     Update links for editing a notification.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('local.php');
include('../lib/l-slct.php');
include('../lib/l-msql.php');
include('../lib/l-gbox.php');
include('../lib/l-date.php');
include('../lib/l-head.php');


$action = strval(get_argument('action', 0, 'none'));

switch ($action) {
    case 'delete':
        $title = 'Notification Console Record Deleted';
        break;
    case 'purge':
        $title = 'Notification Console Records Purged';
        break;
    case 'edit':
        $title = 'Edit Console Notification';
        break;
    case 'update':
        $title = 'Console Update';
        break;
    case 'confirmpurge':
        $title = 'Confirm Purge';
        break;
    case 'confirmdelete':
        $title = 'Confirm Delete';
        break;
    case 'confirmsuspend':
        $title = 'Confirm Suspend';
        break;
    case 'suspend':
        $title = 'Notification Suspended';
        break;
    default:
        $title = 'Console Action';
        break;
}


/*
    |  Creates a unique array ... i.e. we remove all
    |  duplicate elements, So there is only one of everything.
    */

function unique($list)
{
    $rezz = array();
    reset($list);
    foreach ($list as $key => $data) {
        $temp[$data] = $data;
    }
    foreach ($temp as $key => $data) {
        $rezz[] = $data;
    }
    if (safe_count($rezz) > 1) {
        sort($rezz);
    }
    return $rezz;
}


/*
    |  Creates a new list that contains only the
    |  elemements of $list which did not exist 
    |  in $remove.
    */

function exclude($list, $remove)
{
    $temp = array();
    reset($list);
    foreach ($list as $key => $data) {
        $temp[$data] = 1;
    }
    reset($remove);
    foreach ($remove as $key => $data) {
        $temp[$data] = 0;
    }
    $rezz = array();
    reset($temp);
    foreach ($temp as $key => $data) {
        if ($temp[$key])
            $rezz[] = $key;
    }
    return $rezz;
}


/* 
    |  Most days are 24 hours.  However April has a single
    |  day of 23 hours, and October has one of 25 hours.
    |
    |  Sun Apr  1 2001 (23 hours) 1:59 EST -> 3:00 AM EDT
    |  Sun Oct 28 2001 (25 hours) 1:59 EDT -> 1:00 AM EST
    |
    |  No such time as 2:30 AM, Sunday April 1st 2001.
    */

function midnight($tdate)
{
    $hour  = (60 * 60);
    $tday  = getdate($tdate);
    $delta = $tday['seconds'] + (60 * ($tday['minutes'] + (60 * $tday['hours'])));
    if ($delta <= 0)
        $ydate = $tdate;
    else {
        $ydate  = $tdate - $delta;
        $yday   = getdate($tdate);
        $ydate += (($tday['hours'] - $yday['hours']) * $hour);
    }
    return $ydate;
}

/*
    |  Default suspend expiration is a week
    |  from midnight tonight, or 8 days from
    |  midnight last night, in server local
    |  time zone.
    */

function expire_suspend($now)
{
    return midnight($now + (3600 * 24 * 8));
}

function shortdate($utime)
{
    $date = getdate($utime);
    $year = $date['year'];
    $mon  = $date['mon'];
    $day  = $date['mday'];
    $msg  = sprintf("%d/%02d", $mon, $day);
    if (assumeyear($mon) != $year)
        $msg .= sprintf("/%02d", $year % 100);
    if (($date['hours']) || ($date['minutes']))
        $msg .= sprintf(" %02d:%02d", $date['hours'], $date['minutes']);
    return $msg;
}

function query($sql, $db)
{
    $result = command($sql, $db);
    if (!$result) sqlerror($sql, $db);
    return $result;
}

function table_row($msg)
{
    return "<tr>\n$msg\n</tr>\n";
}

function table_col($msg)
{
    return "<td>\n$msg\n</td>\n";
}

function findevents($db, $elist)
{
    $result = false;
    if (safe_count($elist)) {
        $list   = implode(",", $elist);
        $sql    = "select * from Events where idx in ($list)";
        $result = query($sql, $db);
    }
    return $result;
}

function show_table($ev)
{
    echo "<table border='2' align='left' cellpadding='2'>\n";
    foreach ($ev as $key => $data) {
        if (is_string($key)) {
            if (is_string($data))
                $data = nl2br($data);
            else
                $data = strval($data);
            if ($data) {
                $msg  = table_col(fontspeak($key));
                $msg .= table_col(fontspeak($data));
                echo table_row($msg);
            }
        }
    }
    echo "</table>";
    echo "<br clear=\"all\">\n";
}

function show_console($row)
{
    echo "<br>";
    $events = buildlist(",", $row['event_list']);
    $count  = safe_count($events);
    $row['servertime'] = datestring($row['servertime']);
    $row['expire']     = datestring($row['expire']);
    $row['event_list'] = "$count events";
    $row['config']     = "";
    show_table($row);
}


/*
    |   Returns the original array, except that
    |   the empty elements have been filtered out.
    */

function filter($p)
{
    $list = array();
    $n = safe_count($p);
    for ($i = 0; $i < $n; $i++) {
        $elem = $p[$i];
        if ($elem)
            $list[] = $elem;
    }
    return $list;
}


/*
    |  We store the event list as a comma separated
    |  list with leading and trailing commas.  This
    |  returns an array with the actual list, without
    |  the empty elements.
    */

function buildlist($delim, $elist)
{
    $p = explode($delim, $elist);
    $list = filter($p);
    return $list;
}

function showlink($link, $text)
{
    $ln  = "<a href=\"$link\">\n";
    $ln .= "    $text\n";
    $ln .= "</a>";
    echo $ln;
}

/*
    |  Returns true if seek is one of the
    |  members in the string list.
    */

function member($seek, $list)
{
    $list = explode(',', $list);
    if (safe_count($list)) {
        foreach ($list as $key => $data) {
            if ($data == $seek) return 1;
        }
    }
    return 0;
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


$id        = intval(get_argument('id', 0, 0));
$nid       = intval(get_argument('nid', 0, 21));
$debug     = intval(get_argument('debug', 0, 0));
$days      = intval(get_argument('days', 0, 21));
$priority  = intval(get_argument('priority', 0, 3));

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

$cid   = $id;
$debug = ($debug) ? 1 : 0;

$need_cid = "edit,confirmdelete,delete,confirmsuspend,suspend";
$need_nid = "confirmpurge,purge,update";
$valid    = "$need_nid,$need_cid";

$error = '';
$good  = member($action, $valid);
$self  = server_var('PHP_SELF');

if ($debug) {
    echo "cid:$cid,nid:$nid,action:$action<br>";
    echo "need_cid:$need_cid<br>";
    echo "need_nid:$need_nid<br>";
    echo "valid:$valid<br>";
}

if (!$good) {
    $error = "Error -- action $action invalid or not recognized.<br>";
}

if ($good) {
    if (member($action, $need_cid) && ($cid <= 0)) {
        $error .= "Error -- no console identifier specified.<br>";
        $good = 0;
    }
}

if ($good) {
    if (member($action, $need_nid) && ($nid <= 0)) {
        $error .= "Error -- no notification id specified.<br>";
        $good = 0;
    }
}


if ($good) {
    if ($db == 0) {
        $error .= "Error -- can not contact mysql server<br>";
        $good = 0;
    }
}

if (!$good) {
    $action = '';
    $cid = 0;
    $nid = 0;
    $error = "<p><b>$error</b></p>";
    echo fontspeak($error);
}

db_change($GLOBALS['PREFIX'] . 'event', $db);
if ($action == 'confirmdelete') {
    $good   = 0;
    $sql    = "select * from Console where (id = $cid) and (username = '$authuser')";
    $result = query($sql, $db);
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result);
            show_console($row);
            $good = 1;
        }
    }
    if ($good) {
        $delete = 'Do you really want to delete this record?<br><br>';
        $yes    = "<a href='$self?action=delete&id=$cid'>[Yes]</a>";
        $no     = "<a href='console.php'>[No]</a>";
        $msg    = "<br>$delete$yes&nbsp;&nbsp;&nbsp;$no<br><br>";
    } else {
        $msg = '<p>No such records found</p>';
    }
    echo fontspeak($msg);
}

if ($action == 'delete') {
    $sql    = "delete from Console where (id = $cid) and (username = '$authuser')";
    $result = query($sql, $db);
    if ($result) {
        echo fontspeak('<p>Record deleted successfully.</p>');
    }
}

if ($action == 'confirmpurge') {
    $good   = 0;
    $sql    = "select * from Console where (nid = $nid) and (username = '$authuser')";
    $result = query($sql, $db);
    if ($result) {
        $count = mysqli_num_rows($result);
        if ($count > 0) {
            $row  = mysqli_fetch_array($result);
            $name = $row['name'];
            $good = 1;
        }
    }
    if ($good) {
        $purge   = "There are $count notification events of type <i>$name</i><br>";
        $purge  .= "Do you really want to delete all $count of them?<br><br>";
        $yes     = "<a href='$self?action=purge&nid=$nid'>[Yes]</a>";
        $no      = "<a href='console.php'>[No]</a>";
        $msg     = "$purge$yes&nbsp;&nbsp;&nbsp;$no<br><br>";
    } else {
        $err  = '<p>No such records found.</p>';
        $back = "<a href='console.php'>Back to console.</a>";
        $msg  = "$err<br><br>$back";
    }
    echo fontspeak($msg);
}

if ($action == 'purge') {
    $sql    = "select * from Console where (nid = $nid) and (username = '$authuser')";
    $result = query($sql, $db);
    if ($result) {
        $count = mysqli_num_rows($result);
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        if ($count <= 0) {
            echo fontspeak('<p>No such records found.</p>');
        } else {
            $sql    = "delete from Console where (nid = $nid) and (username = '$authuser')";
            $result = query($sql, $db);
            if ($result) {
                echo fontspeak("<p>$count record(s) deleted successfully.</p>");
            }
        }
    }
}


if ($action == 'confirmsuspend') {
    $events   = array();
    $machines = array();
    $good     = 0;
    $remote   = 0;
    $error    = '';
    $sql      = "select * from Console where (id = $cid)";
    $result   = query($sql, $db);
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row    = mysqli_fetch_array($result);
            $owner  = $row['username'];
            if ($owner == $authuser) {
                $nid    = $row['nid'];
                $events = buildlist(',', $row['event_list']);
            } else
                $error = "Console event $cid does not belong to you.";
        } else {
            $error = "Console event $cid no longer exists.";
        }
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
    } else {
        $error = "mysql error while locating console event $cid.";
    }

    if (safe_count($events)) {
        $result = findevents($db, $events);
        if ($result) {
            if (mysqli_num_rows($result)) {
                while ($ev = mysqli_fetch_array($result)) {
                    $machines[] = $ev['machine'];
                }
                unset($ev);
                $machines = unique($machines);
            } else {
                $error = "No associated events were found for console event $cid.";
            }
            ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        }
    }

    if (safe_count($machines)) {
        $row['machines'] = implode(',', $machines);
        show_console($row);
        $good = 1;
    }

    $suspend = 0;
    $mlist   = '';
    if ($good) {
        $good    = 0;
        $sql     = "select * from Notifications where (id = $nid)";
        $result  = query($sql, $db);
        if ($result) {
            if (mysqli_num_rows($result) == 1) {
                $not     = mysqli_fetch_array($result);
                $owner   = $not['username'];
                if ($owner == $authuser) {
                    $suspend = $not['suspend'];
                    $mlist   = $not['machines'];
                    $good    = 1;
                } else {
                    $error  = 'This event was created by a global notification';
                    $error .= ' that does not belong to you.<br>';
                    $remote = 1;
                }
                $url  = 'notify.php';
                $arg  = "act=edit&nid=$nid";
                $href = "$url?$arg";
                $edit = html_link($href, '[Edit]');
            } else {
                $error = "Notification $nid is missing.";
            }
            ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        } else {
            $error = "mysql error looking for notification $nid.";
        }
    }

    if ($good) {
        $list_a = array();
        $now    = time();
        $msg    = "<br><br>";
        $expire = expire_suspend($now);
        $when   = shortdate($expire);
        $moot   = 0;
        if (($suspend > $now) && ($mlist)) {
            $list_a   = explode(',', $mlist);
            $machines = exclude($machines, $list_a);
            $mlist    = str_replace(',', ', ', $mlist);
            $list_b   = implode(', ', $machines);
            $date     = shortdate($suspend);

            $msg .= "This notification is already suspended from $mlist";
            $msg .= " until $date.<br><br>";

            if ($expire <= $suspend) {
                if (safe_count($machines))
                    $msg .= "There will be no change to the suspension from $mlist.<br><br>";
                else
                    $moot = 1;
            } else {
                $msg .= "Suspension of $mlist will be extended";
                $msg .= " until $when.<br><br>";
            }
        }

        if ($moot) {
            $back   = "<a href='console.php'>[Back]</a>";
            $msg   .= "$edit&nbsp;&nbsp;&nbsp;$back\n";
        } else {
            $susp = '';
            if (safe_count($machines)) {
                $list_b  = implode(", ", $machines);
                if (safe_count($list_a))
                    $susp = "Notification will also";
                else
                    $susp = "This notification will";
                $susp .= " be suspended from $list_b until $when.<br><br>";
            }
            $susp   .= "Would you like to proceed with the suspension?";
            $yes     = "<a href='$self?action=suspend&id=$cid'>[Yes]</a>";
            $no      = "<a href='console.php'>[No]</a>";
            $msg    .= "$susp<br><br>$yes&nbsp;&nbsp;&nbsp;$no&nbsp;&nbsp;&nbsp;$edit\n";
        }
    } else {
        $msg = ($error) ? $error : 'Unanticipated mysql failure.';
    }
    if ($remote) {
        $msg .= "<br>\n<br>\n$edit\n";
    }

    echo fontspeak("<p>$msg</p>");
}



if ($action == 'suspend') {
    $error    = '';
    $events   = array();
    $machines = array();
    $sql      = "select * from Console where (id = $cid) and (username = '$authuser')";
    $result   = query($sql, $db);

    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row    = mysqli_fetch_array($result);
            $nid    = $row['nid'];
            $events = buildlist(',', $row['event_list']);
        } else {
            $error = "Console event $cid no longer exists in the database.";
        }
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
    } else {
        $error = "mysql error while locating console event $cid.";
    }

    if (safe_count($events)) {
        $result = findevents($db, $events);
        if ($result) {
            if (mysqli_num_rows($result)) {
                while ($ev = mysqli_fetch_array($result)) {
                    $machines[] = $ev['machine'];
                }
                unset($ev);
            } else {
                $error = "No associated events were found for console event $cid.";
            }
            ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        }
    }

    $good = 0;
    if (safe_count($machines)) {
        $sql = "select * from Notifications where (id = $nid) and (username = '$authuser')";
        $result = query($sql, $db);
        $events = array();
        if ($result) {
            if (mysqli_num_rows($result) == 1) {
                $row     = mysqli_fetch_array($result);
                $suspend = $row['suspend'];
                $mlist   = $row['machines'];
                $global  = $row['global'];
                $name    = $row['name'];
                $good    = 1;
            } else {
                $error = "Notification $nid is missing or does not belong to you.";
            }
            ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        } else {
            $error = "mysql error while locating notification $nid.";
        }
    }

    if ($good) {
        $good = 0;
        $now  = time();
        if ($suspend > $now) {
            if ($mlist) {
                $new   = implode(',', $machines);
                $mlist = "$mlist,$new";
                $machines = explode(',', $mlist);
            }
        }

        $machines = unique($machines);
        $expire   = expire_suspend($now);
        if ($expire > $suspend) $suspend = $expire;
        $mlist    = implode(',', $machines);
        $sql      = "update Notifications set";
        $sql     .= " suspend=$suspend, machines = '$mlist'";
        $sql     .= " where (id = $nid)";

        $result = query($sql, $db);
        if ($result) {
            $good  = 1;
            $scope = ($global) ? 'global' : 'local';
            $date  = shortdate($suspend);
            $mlist = str_replace(',', ', ', $mlist);
            $msg   = "The $scope notification <b>$name</b> has been suspended until $date on $mlist.";
        } else {
            $error = "mysql error suspending notification $nid.";
        }
    }

    if (!$good) {
        $msg = ($error) ? $error : "Unanticipated failure updating notification $nid.";
    }
    echo fontspeak("<p>$msg</p>");
}


if ($action == 'edit') {
    $good   = 0;
    $error  = "Console event $cid is no longer exists.";
    $sql    = "select * from Console where (id = $cid)";
    $result = query($sql, $db);
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row      = mysqli_fetch_array($result);
            $username = $row['username'];
            if ($username != $authuser)
                $error = "Console event $cid does not belong to you.";
            else {
                $nid        = $row['nid'];
                $name       = $row['name'];
                $config     = $row['config'];
                $priority   = $row['priority'];
                $expire     = $row['expire'];
                $servertime = $row['servertime'];

                $events     = buildlist(',', $row['event_list']);
                $count      = safe_count($events);

                show_console($row);
                $good = 1;
            }
        }
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
    }


    if ($good) {
        $good    = 0;
        $missing = 1;
        $sql     = "select * from Notifications where (id = $nid)";
        $result  = query($sql, $db);
        if ($result) {
            if (mysqli_num_rows($result) == 1) {
                $row  = mysqli_fetch_array($result);
                $username = $row['username'];
                $missing = 0;
                $good = ($username == $authuser) ? 1 : 0;
            }
            ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        }
        if (!$good) {
            if ($missing) {
                $msg  = "This is an orphaned event ... the notification";
                $msg .= " that created it no longer exists.";
            } else {
                $href = "notify.php?act=edit&nid=$nid";
                $edit = html_link($href, '[Edit]');

                $msg  = "This event was created by a global notification";
                $msg .= " that does not belong to you. ";
                $msg .= "&nbsp;$edit<br>";
            }
            $msg .= "<br>You may still edit these records, but the notification will not ";
            $msg .= " be updated.";
            echo fontspeak("<br><p>$msg</p>");
        }
        $good = 1;
    }

    if ($good) {
        $good   = 0;
        $error  = "No console events of type $nid found.";
        $sql    = "select * from Console where (nid = $nid) and (username = '$authuser')";
        $result = query($sql, $db);
        if ($result) {
            $count  = mysqli_num_rows($result);
            if ($count) {
                $good = 1;
            }
        }
    }


    if ($debug) {
        echo "action:$action, cid:$cid, nid:$nid, count:$count, result:$result<br>";
    }

    if ($good) {
        echo fontspeak("<p>There are $count notification events like this one.</p>");
        echo "<form method='post' action='$self'>\n";
        echo "<input type='hidden' name='action' value='update'>\n";
        echo "<input type='hidden' name='nid' value='$nid'>\n";
        if ($debug) {
            echo "<input type='hidden' name='debug' value='1'>\n";
        }
?>
        <table border="0" padding="3">
            <tr>
                <td>
                    <font face="verdana,helvetica" size="2">
                        Priority:
                    </font>
                </td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <?php
                        $options = range(1, 5);
                        echo html_select('priority', $options, $priority, 0);
                        ?>
                    </font>
                </td>
            </tr>
            <tr>
                <td>
                    <font face="verdana,helvetica" size="2">
                        Expires in:
                    </font>
                </td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <?php
                        unset($options);
                        if ($expire == 0x7fffffff)
                            $days = 0;
                        else {
                            $week  = 7 * 24 * 3600;
                            $age   = $expire - $servertime;
                            $weeks = intval(round($age / $week));
                            $days  = $weeks * 7;
                        }
                        $options['7']  = '1 Week';
                        $options['14'] = '2 Weeks';
                        $options['21'] = '3 Weeks';
                        $options['28'] = '4 Weeks';
                        $options['0']  = 'Never';
                        echo html_select('days', $options, $days, 1);
                        ?>
                    </font>
                </td>
            </tr>
            <tr>
                <td>
                    <font face="verdana,helvetica" size="2">
                        Details:
                    </font>
                </td>
                <td>
                    <table cellpadding="3" cellspacing="0" bordercolor="COCOCO" border="1">
                        <?php
                        $fnames = event_fields($db);
                        $cfg = buildlist(':', $config);
                        $def = array();
                        $ncn = safe_count($cfg);
                        for ($i = 0; $i < $ncn; $i++) {
                            $def[$cfg[$i]] = 1;
                        }
                        echo genboxes($fnames, $def);
                        ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <br>
                </td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <input type=submit value="Submit">
                    </font>
                </td>
            </tr>
        </table>
        </form>
<?php
    }

    if (!$good) {
        $msg = ($error) ? $error : "Unanticipated failure editing console record $cid.";
        echo fontspeak("<br><p>$msg</p>");
    }
}

if ($action == 'update') {
    $config = genconfig($db, true, $_POST);
    $count = 0;
    $sql = "select * from Console where (nid = $nid) and (username = '$authuser')";
    $result = query($sql, $db);
    if ($result) {
        $count = mysqli_num_rows($result);
    }
    if ($count <= 0) {
        echo fontspeak('<p>No records found to update...</p>');
    } else {
        $row  = mysqli_fetch_array($result);
        $name = $row['name'];
        ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
        echo fontspeak("<p>Updating $count <b>$name</b> record(s)...</p>");
        $sql  = "update Console set priority = $priority";
        $sql .= ", config = '$config'";
        if (isset($days)) {
            $maxtime = 0x7fffffff;
            if ($days) {
                $delta  = $days * 3600 * 24;
                $expire = "servertime + $delta";
            } else {
                $expire = 0x7fffffff;
            }
            $sql .= ", expire = $expire";
        }
        $sql .= " where nid = $nid";
        if ($debug) {
            echo "sql:$sql<br>config:$config<br>priority:$priority, days:$days<br>";
            echo "expire:$expire<br>";
        }
        $result = query($sql, $db);
        if ($result) {
            echo fontspeak("<p>Updated $count <b>$name</b> record(s).</p>");
        }
    }
    $sql = "select * from Notifications where (id = $nid) and (username = '$authuser')";
    $result = query($sql, $db);
    if ($result) {
        if (mysqli_num_rows($result) == 1) {
            $row    = mysqli_fetch_array($result);
            $name   = $row['name'];
            $global = $row['global'];
            ((mysqli_free_result($result) || (is_object($result) && (get_class($result) == "mysqli_result"))) ? true : false);
            $config = genconfig($db, true, $_POST);
            $sql = "update Notifications set priority = $priority";
            if (isset($days))
                $sql .= ", days = $days";
            $sql .= ", config = '$config'";
            $sql .= " where id = $nid";
            $result = query($sql, $db);
            if ($result) {
                $scope = ($global) ? 'global' : 'local';
                echo fontspeak("<p>Updated $scope notification <b>$name</b>.</p>");
            }
        }
    }
}

if (!member($action, "confirmdelete,confirmpurge,confirmsuspend")) {
    $doc = "Back to console.";
    $doc = fontspeak("<p>$doc</p>");
    showlink("console.php", $doc);
}


// this is good to look at if there are paramater passing
// questions

if ($debug) {
    phpinfo();
}
echo head_standard_html_footer($authuser, $db);
?>