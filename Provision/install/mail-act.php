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
include('../lib/l-dberr.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-cnst.php');

function special_header($msg, $span)
{
    $msg = "<font color='white'>$msg</font>";
    $msg = fontspeak($msg);
    $msg = "<tr><th colspan='$span' bgcolor='#333399'>$msg</th></tr>\n";
    return $msg;
}

function span_data($n, $msg)
{
    $msg = fontspeak($msg);
    $msg = "<tr><td colspan='$n'>$msg</td></tr>\n";
    return $msg;
}

function message($s)
{
    $msg = stripslashes($s);
    echo "<br>\n$msg<br>\n<br>\n";
}

function table_header()
{
    echo "\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $s = fontspeak($data);
            echo "<$td>$s</$td>\n";
        }
        echo "</tr>\n";
    }
}

function createMailConfig($authuser, $db)
{

    $msg = '';
    $problem = 0;

    $now = time();

    $smtpName = trim(get_argument('name', 1, ''));
    $smtpHost = trim(get_argument('host', 1, ''));
    $smtpPort = trim(get_argument('port', 1, ''));
    $smtpUser = trim(get_argument('username', 1, ''));
    $smtpPass = trim(get_argument('password', 1, ''));
    $smtpSec = trim(get_argument('security', 1, ''));
    $smtpFromEmail = trim(get_argument('fromemail', 1, ''));

    if ($smtpName == '' || $smtpHost == '' || $smtpPort == '' || $smtpSec == '') {
        //$smtpUser == '' || $smtpPass == '' || 
        $msg = "Mail configuration field values cannot be blank.";
    }

    if ($msg == '') {

        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "install.mailConfig SET name='$smtpName', host='$smtpHost', port='$smtpPort', "
            . "username='$smtpUser', password='$smtpPass', security='$smtpSec', fromemail='$smtpFromEmail', "
            . "createduser='$authuser', createdtime='$now', modifieduser='$authuser', lastmodified='$now'";
        $res = redcommand($sql, $db);
        if (!$res) {
            $problem = 1;
        }

        if ($problem) {
            $msg = "Unable to create Mail Configuration";
        } else {
            $msg = "Mail Configuration created successfully";
            $log = "install: Mail Config '$smtpName' added by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    }
    message($msg);
}

function updateMailConfig($authuser, $db)
{

    $msg = '';
    $problem = 0;

    $now = time();

    $smtpid = trim(get_argument('smtpid', 1, ''));
    $smtpName = trim(get_argument('name', 1, ''));
    $smtpHost = trim(get_argument('host', 1, ''));
    $smtpPort = trim(get_argument('port', 1, ''));
    $smtpUser = trim(get_argument('username', 1, ''));
    $smtpPass = trim(get_argument('password', 1, ''));
    $smtpSec = trim(get_argument('security', 1, ''));
    $smtpFromEmail = trim(get_argument('fromemail', 1, ''));

    if ($smtpName == '' || $smtpHost == '' || $smtpPort == '' || $smtpUser == '' || $smtpPass == '' || $smtpSec == '') {
        $msg = "Mail configuration field values cannot be blank.";
    }

    if ($msg == '') {

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "install.mailConfig SET name='$smtpName', host='$smtpHost', port='$smtpPort', "
            . "username='$smtpUser', password='$smtpPass', security='$smtpSec', fromemail='$smtpFromEmail', "
            . "modifieduser='$authuser', lastmodified='$now' WHERE id = '$smtpid'";
        $res = redcommand($sql, $db);
        if (!$res) {
            $problem = 1;
        }

        if ($problem) {
            $msg = "Unable to update Mail Configuration";
        } else {
            $msg = "Mail Configuration updated successfully";
            $log = "install: Mail Config '$smtpName' added by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    }
    message($msg);
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

$action = strval(get_argument('action', 0, 'none'));
$id = get_argument('id', 0, 0);

switch ($action) {
    case 'createsmtp':
        $title = 'Create SMTP Configuration';
        break;
    case 'updatesmtp':
        $title = 'Update SMTP Configuration';
        break;
    default:
        break;
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users

switch ($action) {
    case 'createsmtp':
        createMailConfig($authuser, $db);
        break;
    case 'updatesmtp':
        updateMailConfig($authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
