<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  15-Apr-19   JHN     Creation.

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

function getMailConfiguration($db)
{
    $sql  = "SELECT * FROM " . $GLOBALS['PREFIX'] . "install.mailConfig";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) != 1) {
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            return array();
        } else {
            $row = mysqli_fetch_array($res);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            return $row;
        }
    }
}

/*
  |  Main program
 */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser = install_login($db);
$authuserdata = install_user($authuser, $db);
$priv_admin = @($authuserdata['priv_admin']) ? 1 : 0;
$priv_servers = @($authuserdata['priv_servers']) ? 1 : 0;

$comp = component_installed();

$tlsChecked = '';
$sslChecked = '';
// get mail configuration
$mailData = getMailConfiguration($db);
if (safe_count($mailData) > 0) {
    $smtp_id = $mailData['id'];
    $smtp_name = $mailData['name'];
    $smtp_host = $mailData['host'];
    $smtp_port = $mailData['port'];
    $smtp_username = $mailData['username'];
    $smtp_password = $mailData['password'];
    $smtp_security = $mailData['security'];
    if ($smtp_security == 'TLS') {
        $tlsChecked = 'checked';
    } else {
        $sslChecked = 'checked';
    }
    $smtp_fromemail = $mailData['fromemail'];
} else {
    $smtp_id = '';
    $smtp_name = '';
    $smtp_host = '';
    $smtp_port = '';
    $smtp_username = '';
    $smtp_password = '';
    $smtp_fromemail = '';
}

$action = safe_count($mailData) > 0 ? 'updatesmtp' : 'createsmtp';
$id = get_integer('id', 0);
$submit = safe_count($mailData) > 0 ? 'Update' : 'Create';
$title = $submit . ' Mail Configuration (SMTP)';
//$all_setting = get_argument('all_setting', 0, '');
//$helpfile = ($action == 'add') ? 'strtadd.php' : 'strtedit.php';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users


$referer = server_var('HTTP_REFERER');

//JS_CheckUncheckAll();
newlines(1);

table_header();
$args = array();
$args[] = "<form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
//$args[] = "<form method='post' action='help/$helpfile' target='help'>" .
//        "<input type='submit' value='Help'></form>";
$args[] = "<form method='post' action='mail-act.php' name='myForm'>\n" .
    "<input type='hidden' name='action' value='$action'>\n" .
    "<input type='hidden' name='smtpid' value='$smtp_id'>\n" .
    "<input type='submit' value='$submit'>";
table_data($args, 0, 'left');
table_footer();

table_header();
$args = array('<b>Name:</b> ', "<input type='text' name='name' value=\"$smtp_name\">");
table_data($args, 0, 'left');
$args = array('<b>Host:</b> ', "<input type='text' name='host' value=\"$smtp_host\">");
table_data($args, 0, 'left');
$args = array('<b>Port:</b> ', "<input type='text' name='port' value=\"$smtp_port\">");
table_data($args, 0, 'left');
$args = array('<b>Username:</b> ', "<input type='text' name='username' value=\"$smtp_username\">");
table_data($args, 0, 'left');
$args = array('<b>Password:</b> ', "<input type='password' name='password' value=\"$smtp_password\">");
table_data($args, 0, 'left');
$args = array('<b>Security:</b> ', "<input type='radio' name='security' value='TLS' $tlsChecked> TLS &nbsp;"
    . "<input type='radio' name='security' value='SSL' $sslChecked> SSL<br/>");
table_data($args, 0, 'left');
$args = array('<b>From email:</b> ', "<input type='text' name='fromemail' value=\"$smtp_fromemail\">");
table_data($args, 0, 'left');

table_footer();

newlines(1);

table_header();
$args = array();
$args[] = "<input type='submit' value='$submit'></form>";
$args[] = "</form><form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
//$args[] = "<form method='post' action='help/$helpfile' target='help'>" .
//        "<input type='submit' value='Help'></form>";
table_data($args, 0, 'left');
table_footer();

echo head_standard_html_footer($authuser, $db);
