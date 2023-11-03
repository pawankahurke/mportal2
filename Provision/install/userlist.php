<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-May-03   NL      Creation.
29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
02-Jun-03   NL      Display boolean fields as yes/no instead of 1/0.
02-Jun-03   NL      Call install_html_footer (has its own version).
02-Jun-03   NL      Change title.
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
31-Jul-03   EWB     Uses install_login($db);
8-Aug-03    NL      Change column order to match form field order on add/edit pages.
 3-Sep-03   NL      Display friendly message if no users exist.
29-Sep-03   NL      Add "View" action.
 8-Oct-03   NL      Add some function descriptions;
                    Clean up extraneous semi-colon and obsolete comment.
23-Oct-03   NL      BACK OUT: Add "View" action.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
03-Oct-08   BTE     Bug 4828: Change customization feature of server.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-cnst.php');


function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}

function bold($msg)
{
    return "<b>$msg</b>";
}

// this is gross .. makes the entire contents into
// a table.  oh well.

function silly_table()
{
    return '<table width="100%" border="0"><tr><td>';
}

function mark($name)
{
    echo "<a name='$name'></a>\n";
}

function marklink($link, $text)
{
    return "<a href='$link'>$text</a>\n";
}

function brace()
{
    return "&nbsp;|&nbsp;";
}

/*
    |  Note that the mark for "top" is now included in header.inc.
    |  The mark for "bottom" is included in footer.inc.
    */

function jumptable($tags)
{
    $args = explode(',', $tags);
    $n = safe_count($args);
    if ($n > 0) {
        $msg = '';
        for ($i = 0; $i < $n; $i++) {
            $name = $args[$i];
            $link = marklink("#$name", $name);
            if ($i) $msg .= brace();
            $msg .= $link;
        }
        $msg .= brace();
        $msg .= "<a href='userdata.php?action=add'>add user</a>";
        echo "<p>[ $msg ]</p>";
    }
}

function table_header()
{
    echo "\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}

function table_data($args, $head, $align = 'left')
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $s = fontspeak($data);
            echo "<$td align=$align>$s</$td>\n";
        }
        echo "</tr>\n";
    }
}

function span_data($n, $msg, $xtra = '')
{
    $msg = "<tr><td colspan='$n' $xtra>$msg</td></tr>\n";
    return $msg;
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function get_all_users_data($db)
{
    $usersdata = array();
    $sql  = "SELECT * FROM Users ORDER BY installuser";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $id   = $row['installuserid'];
                $usersdata[$id]['installuser']   = $row['installuser'];
                $usersdata[$id]['priv_servers']  = ($row['priv_servers'])   ? 'Yes' : 'No';
                $usersdata[$id]['priv_email']    = ($row['priv_email'])     ? 'Yes' : 'No';
                $usersdata[$id]['priv_admin']    = ($row['priv_admin'])     ? 'Yes' : 'No';
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usersdata;
}

/*
    |   show_user
    |
    |   Displays a row in an HTML table listing user data.
    |
    |   parameters:
    |       $id:        The id for the user (Users.installuserid) whose data is being dislayed.
    |       $row:       The row of data from the Users table.
    |       $db:        The handle to the database connection.
    */
function show_user($id, $row, $db)
{
    $name           = $row['installuser'];
    $priv_servers   = $row['priv_servers'];
    $priv_email     = $row['priv_email'];
    $priv_admin     = $row['priv_admin'];

    $args   = array($name, $priv_admin, $priv_servers, $priv_email);
    $edit   = "<a href='userdata.php?id=$id&action=edit'>[edit]</a>";
    $del    = "<a href='user-act.php?id=$id&action=delete'>[delete]</a>";
    $args[] = "$edit<br>$del";

    table_data($args, 0);
}

/*
    |  Main program
    */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser       = install_login($db);
$authuserdata   = install_user($authuser, $db);
$priv_admin     = @($authuserdata['priv_admin'])  ? 1 : 0;
$priv_servers   = @($authuserdata['priv_servers']) ? 1 : 0;

if (!$priv_admin)  header("Location: userdata.php");

$comp = component_installed();

$title   = 'Users';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

newlines(2);

echo silly_table();

mark('users');
jumptable('top,bottom,users');
table_header();
$args[] = 'User Name';
$args[] = 'Administrator?';
$args[] = 'Enabled for ASI Server?';
$args[] = 'Enabled for Email Distribution?';
$args[] = 'Action';

table_data($args, 1);

$usersdata   = get_all_users_data($db);
if (safe_count($usersdata)) {
    reset($usersdata);
    foreach ($usersdata as $key => $data) {
        show_user($key, $data, $db);
    }
} else {
    echo span_data(safe_count($args), "<br>No users currently exist.<br><br>");
}

table_footer();

newlines(1);
jumptable('top,bottom,users');

table_footer();

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
