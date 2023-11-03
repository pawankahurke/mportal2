<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

ini_set('memory_limit', '-1');

ob_start();
include('config.php');
include('lib/l-dbConnect.php');

global $ftp_server;
global $ftp_user_name;
global $ftp_user_pass;

$clientName = false;
if (url::issetInGet('rcode') && !url::isEmptyInGet('rcode')) {
    $pdo = pdo_connect();
    $reqdata['function'] = 'getclientdownloadinfo';
    $reqdata['data']['regcode'] = url::getToAny('rcode');
    $licensingDetails = MAKE_CURL_CALL($reqdata);

    $siteDetails = $licensingDetails['site'];

    $browserType = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : false;
    if ($browserType && stripos($browserType, 'android') !== false) {
        $clientType = 'client_android_name';
    } else if ($browserType && stripos($browserType, 'mac') !== false) {
        $clientType = 'client_mac_name';
    } else {
        $clientType = (strpos($browserType, 'WOW64') || strpos($browserType, 'wow64') || strpos($browserType, 'Win64') || strpos($browserType, 'win64')) ? 'client_64_name' : 'client_32_name';
    }

    if ($siteDetails) {
        if (isset($siteDetails[$clientType]) && !empty($siteDetails[$clientType]) && !is_null($siteDetails[$clientType])) {
            $clientName = $siteDetails[$clientType];
        } else {
            if (isset($siteDetails['serverid']) && !empty($siteDetails['serverid']) && !is_null($siteDetails['serverid'])) {
                $srdata = $licensingDetails['server'];
                if ($srdata && isset($srdata[$clientType]) && !empty($srdata[$clientType]) && !is_null($srdata[$clientType])) {
                    $clientName = $srdata[$clientType];
                }
            }
        }
        $sitename = $siteDetails['sitename'];
        $urldownload = $siteDetails['urldownload'];
    } else {
        $sitename = 'NOSITE';
    }
}

$cdata = $licensingDetails['customer'];

$apidata = $licensingDetails['api'];

if ($cdata['customer_name'] == 'IBM_MDM') {
    $domainurl = 'https://ibm-mdmlicense.nanoheal.com/';
} else {
    if ($urldownload != '') {
        $downloadurl = explode('/', $urldownload);
        $domainurl = $downloadurl[0] . '//' . $downloadurl[2] . '/';
    } else {
        $domainurl = $apidata['domainurl'];
    }
}

if ($clientName) {
    $login = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);
    ftp_chdir($ftp_conn, 'setups/live');
    $fileInfo = pathinfo($clientName);
    $localPath = './' . $clientName;
    $remotePath = '/home/nanoheal/setups/live/' . $clientName;
    $extension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
    $regCode = url::requestToAny('rcode');
    $siteemailid = url::requestToAny('seid');
    $clientNameSegments = explode(".", $clientName);
    $encsitename = base64_encode($sitename);
    $myDomain = base64_encode($domainurl);
    $downloadFileName = $clientNameSegments[0] . '-' . $regCode . '-' . $siteemailid . '-' . $encsitename . '-' . $myDomain . '.' . $extension;

    ob_clean();

    if (ftp_get($ftp_conn, $localPath, $remotePath, FTP_BINARY)) {
        header('Content-Length: ' . filesize("../download/" . $clientName));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        readfile($localPath);
        @unlink($localPath);
        exit;
    } else {
        exit('Unable to download');
    }

    return false;
}

function MAKE_CURL_CALL($data)
{
    global $licenseapiurl;

    $data_string = json_encode($data);

    $header = array(
        'PHPSESSID: ' . session_id(),
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $licenseapiurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $presdata = safe_json_decode($result, true);
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
    return $presdata;
}
