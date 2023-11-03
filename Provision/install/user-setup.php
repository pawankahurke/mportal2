<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  16-Aug-19   JHN     File Creation.
  16-Aug-19   JHN     separte file created for the tenant import.

 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
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
include('../lib/l-svbt.php');

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

function generate_import_token($db)
{

    $chksql = "select * from " . $GLOBALS['PREFIX'] . "install.tokenManager limit 1";
    $chkres = command($chksql, $db);
    if ($chkres) {
        $tokendata = mysqli_fetch_assoc($chkres);
        if (isset($tokendata['tokenval'])) {
            $token = $tokendata['tokenval'];
            echo '<br/>Token already exists.<br/>Token# ' . $token . '<br/><br/>';
        } else {
            $token = uniqid('NH');
            $createdtime = time();
            $sql = "insert into " . $GLOBALS['PREFIX'] . "install.tokenManager (tokenval, createdtime) values "
                . "('$token', '$createdtime')";
            $res = redcommand($sql, $db);
            if ($res) {
                echo '<br/>Token has been generated successfully.<br/>Token# ' . $token . '<br/><br/>';
            }
        }
    } else {
        echo 'Some error occured!';
    }
}

function import_tenant_key($db)
{

    $msg = '';
    $problem = 0;
    $serverflag = false;
    $userflag = false;
    $now = time();

    $tenantActKey = trim(get_argument('tenantactkey', 1, ''));

    if ($tenantActKey == '') {
        $msg = "Tenant Import key field value cannot be blank.";
    }

    if ($msg == '') {
        $key = 'hnhj7vqj9n';
        $c = base64_decode($tenantActKey);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

        if (hash_equals($hmac, $calcmac)) {     //PHP 5.6+ timing attack safe comparison
            $slaveTenantInfo = unserialize($original_plaintext);
            //echo '<pre>'; print_r($slaveTenantInfo); die();
            $userInfo = $slaveTenantInfo['user'];
            $serverInfo = $slaveTenantInfo['server'];

            $masterToken = $userInfo['token'];
            $tksql = "select tokenval from " . $GLOBALS['PREFIX'] . "install.tokenManager limit 1";
            $tkres = command($tksql, $db);
            if ($tkres) {
                $tokenData = mysqli_fetch_assoc($tkres);
                if (isset($tokenData['tokenval']) && $tokenData['tokenval'] == $masterToken) {
                    if (safe_count($serverInfo) > 0) {
                        $serverflag = true;
                        $sersql = "insert into " . $GLOBALS['PREFIX'] . "install.Servers set ";
                        foreach ($serverInfo as $key => $value) {
                            $sersql .= $key . " = '$value',";
                        }
                        $servsql = rtrim($sersql, ',');
                        redcommand($servsql, $db);
                    }

                    if (safe_count($userInfo) > 0) {
                        $userflag = true;
                        $usrsql = "insert into " . $GLOBALS['PREFIX'] . "install.Users set ";
                        foreach ($userInfo as $key => $value) {
                            if ($key == 'installuser') {
                                $instusername = $value;
                            }
                            $usrsql .= $key . " = '$value',";
                        }
                        $usersql = rtrim($usrsql, ',');

                        $chksql = "select count(installuserid) as icnt from " . $GLOBALS['PREFIX'] . "install.Users where installuser = '$instusername' limit 1";
                        $chkres = command($chksql, $db);
                        if ($chkres) {
                            $instuserdata = mysqli_fetch_assoc($chkres);
                            if ($instuserdata['icnt'] > 0) {
                                $msg = "Tenant has been already activated with this key.";
                            } else {
                                $userres = redcommand($usersql, $db);
                                if ($userres) {
                                    $msg = "Tenant key imported successfully.";
                                    $log = "install: Tenant key created.";
                                    logs::log(__FILE__, __LINE__, $log, 0);
                                } else {
                                    $msg = "Tenant key import failed. Query failed to execute!";
                                }
                            }
                        }
                    }
                } else {
                    $msg = "Tenant key import failed. Invalid token!";
                }
            } else {
                $msg = "Tenant key import failed. Please try again!";
            }
        } else {
            $msg = "Invalid tenant activation key!";
        }
    }
    message($msg);
}

/*
  |  Main program
 */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

/*$authuser = install_login($db);
$authuserdata = install_user($authuser, $db);
$priv_admin = @ ($authuserdata['priv_admin']) ? 1 : 0;
$priv_servers = @ ($authuserdata['priv_servers']) ? 1 : 0;*/

$comp = component_installed();

$action = strval(get_argument('action', 0, 'none'));
$id = get_argument('id', 0, 0);

switch ($action) {
    case 'generateimporttoken':
        $title = 'Generate Token';
        break;
    case 'importtenantkey':
        $title = 'Import Tenant Key';
        break;
    default:
        $title = 'Action Unknown';
        break;
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users

switch ($action) {
    case 'generateimporttoken':
        generate_import_token($db);
        break;
    case 'importtenantkey':
        import_tenant_key($db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
