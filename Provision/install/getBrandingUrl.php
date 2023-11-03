<?php

//ini_set('display_errors', 'On');
//error_reporting(-1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

//ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-slct.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-svbt.php');
include('../lib/l-config.php');

logs::log("RPC_START", ["request" => $_REQUEST, "post" => $_POST,  "get" => $_GET]);

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

global $ftp_server;
global $ftp_username;
global $ftp_userpass;

$regcode = url::requestToText('regcode');
$siteemailid = url::requestToText('siteemailid');


// @warn $regcode is not filtering enoth. (probably  SQL Injection Issues )
$sql = "select siteid, sitename, brandingurl from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$regcode' limit 1";
$res = redcommand($sql, $db);

if ($res) {
    if (mysqli_num_rows($res)) {
        $data = mysqli_fetch_assoc($res);
        $brandingUrl = $data['brandingurl'];
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
}

if ($brandingUrl == '') {
    exit('Unable to download');
}

// $checkBranding = explode('//', $brandingUrl);
// if(safe_count($checkBranding) > 1) {
//     header('location: ' . $brandingUrl);
// } else {
//     header('location: /home/nanoheal/setups/branding/' . $brandingUrl);
// }

$DOMAIN = getenv("DASHBOARD_SERVICE_HOST");
header('Location: ' . "https://$DOMAIN/Dashboard/Provision/download/download_helper.php?rcode=$regcode&seid=$siteemailid&type=branding");
exit;

/*$ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
$login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
ftp_chdir($ftp_conn, '/home/nanoheal/setups/branding');

$localPath = "../download/".$brandingUrl;
$remotePath = '/home/nanoheal/setups/branding/'.$brandingUrl;

$defLocalPath = "../download/cust_Default_Branding.zip";
$defRemotePath = '/home/nanoheal/setups/branding/cust_Default_Branding.zip';

ob_clean();

if (ftp_get($ftp_conn, $localPath, $remotePath, FTP_BINARY)) {
    header('Content-Length: ' . filesize("../download/" . $brandingUrl));
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $brandingUrl . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    readfile($localPath); // send the file
    @unlink($localPath);
    exit;  // make sure no extraneous characters get appended
} else {
    $res = ftp_get($ftp_conn, $defLocalPath, $defRemotePath, FTP_BINARY);
    if($res) {
        header('Content-Length: ' . filesize("../download/cust_Default_Branding.zip"));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=cust_Default_Branding.zip');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        readfile($defLocalPath); // send the file
        @unlink($defLocalPath);
        exit;
    }
    exit('Unable to download');
}*/
