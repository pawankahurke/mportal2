<?php

/*
Revision history:

Date        Who     What
----        ---     ----
18-May-03   NL      Creation.
29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
02-Jun-03   NL      B/c admin can see other startup options, differentiate btw viewer and creator.
02-Jun-03   NL      Call install_html_footer (has its own version).
02-Jun-03   NL      Dynamic title.
03-Jun-03   NL      Bold Labels.
04-Jun-03   NL      Change all labels to sentence case.
06-Jun-03   NL      Oops. get_argument('action','add',0) --> get_argument('action',0,'add').
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
31-Jul-03   EWB     Uses install_login($db);
 8-Aug-03   NL      Add form field "Available to all".
15-Sep-03   NL      Direct help buttons to corresponding help page.
 8-Oct-03   NL      Cleaned up long lines.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
03-Oct-08   BTE     Bug 4828: Change customization feature of server.
30-Sep-19   SHG     Mac/iOS/Linux client upload option added. 
*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-slct.php');
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

function table_header()
{
    echo "\n<table border='0' align='left' cellspacing='0' cellpadding='6'>\n";
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function table_data($args, $head, $align = 'center')
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr valign=top>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td align=$align>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}

function get_server_data($id, $db)
{
    $serverdata = array();
    $sql  = "SELECT * FROM Servers WHERE serverid = $id";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $serverdata['servername']   = $row['servername'];
                $serverdata['serverurl']    = $row['serverurl'];
                $serverdata['installuserid'] = $row['installuserid'];
                $serverdata['global']       = $row['global'];
                $serverdata['notifyemail']  = $row['notifyemail'];
                $serverdata['reportemail']  = $row['reportemail'];
                $serverdata['url']          = $row['url'];
                $serverdata['streamingurl'] = $row['streamingurl'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $dsql = "select domainurl from apiConfig limit 1";
    $dres = command($dsql, $db);
    if ($dres) {
        if (mysqli_num_rows($dres)) {
            $domaindata = mysqli_fetch_assoc($dres);
            $serverdata['domainname'] = $domaindata['domainurl'];
        }
    }

    return $serverdata;
}


/*
    |  Main program
    */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser       = install_login($db);
$authuserdata   = install_user($authuser, $db);
$admin          = @($authuserdata['priv_admin'])  ? 1 : 0;
$serv           = @($authuserdata['priv_servers']) ? 1 : 0;

if (!$serv)  header("Location: index.php");

$comp = component_installed();

$action = get_argument('action', 0, 'add');
$id     = get_argument('id', 0, 0);
$title   = ucwords($action) . ' ASI Server Information';
$helpfile = ($action == 'add') ? 'servadd.php' : 'servedit.php';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $admin, $serv, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$referer   = server_var('HTTP_REFERER');
$submit    = ($action == 'add') ? 'Enter' : 'Update';
$label_pwd = ($action == 'add') ? 'Password' : 'New password';
$instr_pwd = ($action == 'add') ? '' : '<br><span class=footnote>The ASI server password.<br>Enter only if you want to change the password.</span>';

if ($action != 'add')
    $serverdata = get_server_data($id, $db);
$servername     = ($action == 'add') ? '' : $serverdata['servername'];
$domainname     = ($action == 'add') ? '' : $serverdata['domainname'];
$serverurl     = ($action == 'add') ? '' : $serverdata['serverurl'];
$installuserid  = ($action == 'add') ? '' : $serverdata['installuserid'];
$global         = ($action == 'add') ? 0  : $serverdata['global'];
$notifyemail    = ($action == 'add') ? '' : $serverdata['notifyemail'];
$reportemail    = ($action == 'add') ? '' : $serverdata['reportemail'];
$url            = ($action == 'add') ? '' : $serverdata['url'];
$streamingurl   = ($action == 'add') ? '' : $serverdata['streamingurl'];

newlines(1);

table_header();
$args = array();

$args[] = "<form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
$args[] = "<form method='post' action='help/$helpfile' target='help'>" .
    "<input type='submit' value='Help'></form>";
$args[] = "<form enctype=\"multipart/form-data\" method='post' action='serv-act.php'>\n" .
    "<input type='file' id=\"executable_client_32_id\" name='executable_client_32' value=\"\" style=\"display:none\">"
    . "<input type='file' id=\"executable_client_64_id\" name='executable_client_64' value=\"\" style=\"display:none\">"
    . "<input type='file' id=\"executable_client_apk_id\" name='executable_client_apk' value=\"\" style=\"display:none\">"
    . "<input type='file' id=\"executable_client_mac_id\" name='executable_client_mac' value=\"\" style=\"display:none\">"
    . "<input type='file' id=\"executable_client_ios_id\" name='executable_client_ios' value=\"\" style=\"display:none\">"
    . "<input type='file' id=\"executable_client_linux_id\" name='executable_client_linux' value=\"\" style=\"display:none\">"
    . "<input type='hidden' name='action' value='$action'>\n" .
    "<input type='hidden' name='id' value='$id'>\n" .
    "<input type='submit' value='$submit'>";
table_data($args, 0, 'left');
table_footer();

table_header();

$label = '<b>ASI server name:</b> ';
$help  = "<br><span class=footnote>The \"friendly\" name for the ASI server.</span>";
$field = "<input type='text' name='servername' value=\"$servername\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Domain name:</b> ';
$help  = "<br><span class=footnote>Domain name of licensing server</span>";
$field = "<input type='text' name='domainname' value=\"$domainname\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>ASI server API path:</b> ';
$help  = "<br><span class=footnote>ASI server api path</span>";
$field = "<input type='text' name='serverurl' value=\"$serverurl\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Notification email:</b> ';
$help  = "<br><span class=footnote>The default notification recipient's email address.</span>";
$field = "<input type='text' name='notifyemail' value=\"$notifyemail\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Report email:</b> ';
$help  = "<br><span class=footnote>The default report recipient's email address.</span>";
$field = "<input type='text' name='reportemail' value=\"$reportemail\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>ASI server URL:</b> ';
$help  = '<br><span class=footnote>The URL for the ASI server.</span>';
$field = "<input type='text' name='url' value=\"$url\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>ASI streaming URL:</b> ';
$help  = '<br><span class=footnote>The streaming URL for the ASI server.<br/>'
    . 'Streaming URL should not end with forward slash ( / )</span>';
$field = "<input type='text' name='strmngurl' value=\"$streamingurl\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Upload 32 Bit Client:</b> ';
$help  = '<br><span class=footnote>The 32 bit client to be downloaded<br/></span>';
$field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_32_id').click();\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Upload 64 Bit Client:</b> ';
$help  = '<br><span class=footnote>The 64 bit client to be downloaded<br/></span>';
$field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_64_id').click();\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Upload Android Client:</b> ';
$help  = '<br><span class=footnote>The Android client to be downloaded<br/></span>';
$field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_apk_id').click();\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Upload Mac Client:</b> ';
$help  = '<br><span class=footnote>The Mac client to be downloaded<br/></span>';
$field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_mac_id').click();\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Upload iOS Client:</b> ';
$help  = '<br><span class=footnote>The Ios client to be downloaded<br/></span>';
$field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_ios_id').click();\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');

$label = '<b>Upload Linux Client:</b> ';
$help  = '<br><span class=footnote>The Linux client to be downloaded<br/></span>';
$field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_linux_id').click();\">";
$args  = array($label . $help, $field);
table_data($args, 0, 'left');


if ($admin) {
    $checked = ($global) ? 'CHECKED' : '';
    $label = '<b>Available to all:</b> ';
    $hidden = "<input type='hidden' name='global' value=0>\n";
    $field = "<input type='checkbox' name='global' value=1 $checked>";
    $args  = array($label, $hidden . $field);
    table_data($args, 0, 'left');
}

table_footer();

newlines(1);

table_header();
$args   = array();
$args[] = "<input type='submit' value='$submit'></form>";
$args[] = "</form><form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
$args[] = "<form method='post' action='help/$helpfile' target='help'>" .
    "<input type='submit' value='Help'></form>";

table_data($args, 0, 'left');
table_footer();

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
