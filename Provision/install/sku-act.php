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

function __decrypt($string)
{
    $privKey = '-----BEGIN PRIVATE KEY----- 
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 
i+uDHY3gWW2rbxOQFpITF1jCXnx4RJy7zQ== 
-----END PRIVATE KEY-----';

    openssl_private_decrypt(base64_decode($string), $decrypted, $privKey);

    return $decrypted;
}

function import_license_key($authuser, $db)
{

    $msg = '';
    $problem = 0;

    $now = time();

    $licenseKeyEnc = trim(get_argument('licensekey', 1, ''));

    if ($licenseKeyEnc == '') {
        $msg = "License key field value cannot be blank.";
    }

    if ($msg == '') {
        // Decrypt the value and update into skuOfferings table
        $decrypted_txt = __decrypt($licenseKeyEnc);
        $licenseKeyDec = safe_json_decode($decrypted_txt, true);

        $name = $licenseKeyDec['name'];
        $description = $licenseKeyDec['description'];
        $category = $licenseKeyDec['category'];
        $billingtype = $licenseKeyDec['billingtype'];
        $quantity = $licenseKeyDec['quantity'];
        $amount = $licenseKeyDec['amount'];
        $trialperiod = $licenseKeyDec['trialperiod'];
        $billingcycle = $licenseKeyDec['billingcycle'];

        // skuOfferings insert
        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "install.skuOfferings SET name='$name', description='$description', "
            . "published='1', category='$category', billingtype='$billingtype', quantity='$quantity', "
            . "amount = '$amount', trialperiod='$trialperiod', billingcycle='$billingcycle' ";
        $res = redcommand($sql, $db);
        if (!$res) {
            $problem = 1;
        }

        if ($problem) {
            $msg = "License key import failed";
        } else {
            $msg = "License key imported successfully";
            $log = "install: License key added by $authuser.";
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
    case 'importlicense':
        $title = 'Import License Key';
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
    case 'importlicense':
        import_license_key($authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
