<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Giant refactoring.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
 6-Jan-03   EWB     Does not require php register_globals
16-Jan-03   EWB     Access to $_SERVER variables.
10-Feb-03   EWB     Uses new database.
11-Feb-03   EWB     db_change()
13-Feb-03   EWB     Display human-readable timestamps.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz no $debug
31-Jan-05   EWB     Closes the <h2> header correctly.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title = 'Notification Console Details';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-head.php');
include('local.php');


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
        if (strlen($elem))
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


function query($sql, $db)
{
    $result = command($sql, $db);
    if (!$result) sqlerror($sql, $db);
    return $result;
}


function findevents($db, $elist)
{
    $res = false;
    if ($elist) {
        $lst = implode(',', $elist);
        $sql = "select * from Events where idx in ($lst)";
        $res = query($sql, $db);
    }
    return $res;
}

function table_row($msg)
{
    return "<tr>\n$msg\n</tr>\n";
}

function table_col($msg)
{
    return "<td>\n$msg\n</td>\n";
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
            $len = strlen($data);
            if ($len > 0) {
                $msg  = table_col(fontspeak($key));
                $msg .= table_col(fontspeak($data));
                echo table_row($msg);
            }
        }
    }
    echo "</table>";
    echo "<br clear=\"all\">\n";
}

/*
    |  string2 is the path to cust.exe
    |  clientsize is the size of cust.exe
    |
    |  It turns out that these are never used.
    |  We no longer display them.
    */

function show_events($db, $events)
{
    $n = safe_count($events);
    if ($n > 0) {
        $res = findevents($db, $events);
        if ($res) {
            $count = mysqli_num_rows($res);
            if ($count < $n) {
                if ($count) {
                    $msg  = "It seems that some of the events in the event list ";
                    $msg .= "are no longer present in the database.<br>\n";
                    $msg .= "Perhaps they may have expired.&nbsp;&nbsp;";
                    $msg .= "Showing $count of the original $n events.\n";
                } else {
                    $msg  = "Sorry, none of these events are still present in the database.<br>\n";
                    $msg .= "Perhaps they may have expired.<br>\n";
                }
                echo fontspeak("<p><b><font size='3'>$msg</font></b></p>");
            }
            if ($count) {
                $i = 0;
                while ($ev = mysqli_fetch_array($res)) {
                    $i++;
                    echo "<h2>Event $i of $count</h2>\n";
                    $ev['string2']    = '';  // path of cust.exe
                    $ev['clientsize'] = '';  // size of cust.exe
                    $ev['servertime'] = datestring($ev['servertime']);
                    $ev['entered']    = datestring($ev['entered']);
                    show_table($ev);
                    echo "<br clear=\"all\">\n";
                }
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
}


function delete($id)
{
    $href = "cnsl-act.php?id=$id&action=confirmdelete";
    $msg = "$id&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$href\">[Delete]</a>";
    return $msg;
}


function show_header($row, $events)
{
    $name = $row['name'];
    $id   = $row['id'];
    $count = safe_count($events);
    $row['servertime'] = datestring($row['servertime']);
    $row['expire']     = datestring($row['expire']);
    $row['event_list'] = "$count events";
    $row['id'] = delete($id);
    echo "<h2>$name ($count events)<h2>\n";
    show_table($row);
    echo "<br clear=\"all\">\n";
}

function showlink($link, $text)
{
    $ln  = "<a href=\"$link\">\n";
    $ln .= "    $text\n";
    $ln .= "</a>";
    echo $ln;
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
//if (trim($msg)) debug_note($msg);   // ...display any errors to debug users  

$id     = intval(get_argument('id', 0, 0));
$result = false;
$count  = 0;

if ($id > 0) {
    db_change($GLOBALS['PREFIX'] . 'event', $db);
    $sql   = "select * from Console";
    $sql  .= " where (username = '$authuser')";
    $sql  .= " and (id = $id)";
    $result = query($sql, $db);
}

$link = fontspeak('Back to console');
showlink('console.php', $link);

if ($result) {
    $count = mysqli_num_rows($result);
}

if ($count <= 0) {
    $err = "Sorry, no records were found.";
    $err = fontspeak("<p>$err</p");
    echo $err;
} else {
    $row    = mysqli_fetch_array($result);
    $events = buildlist(',', $row['event_list']);
    show_header($row, $events);
    show_events($db, $events);
}

echo "<br clear=\"all\">\n";

showlink('console.php', $link);

echo head_standard_html_footer($authuser, $db);
