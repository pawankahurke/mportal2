<?php

ini_set('memory_limit', '-1');

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-dberr.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('../lib/l-config.php');

$downloadURL = "https://".$_SERVER["HTTP_HOST"] ."/Dashboard/Provision/download";
function dieWithMessage($message)
{
    http_response_code(500);
    die($message);
}
$clientName = false;
if (url::issetInGet('rcode') && !url::isEmptyInGet('rcode')) {
    $pdo = NanoDB::connect();
    $sql = $pdo->prepare("select * from install.Sites where regcode=?");
    $sql->execute([url::getToAny('rcode')]);
    $data = $sql->fetch(PDO::FETCH_ASSOC);

    $browserType = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : false;
    if ($browserType && stripos($browserType, 'android') !== false) {
        $clientType = 'client_android_name';
    } else if ($browserType && stripos($browserType, 'mac') !== false) {
        $clientType = 'client_mac_name';
    } else {
        $clientType = (strpos($browserType, 'WOW64') || strpos($browserType, 'wow64') || strpos($browserType, 'Win64') || strpos($browserType, 'win64')) ? 'client_64_name' : 'client_32_name';
    }

    if ($data) {
        if ($data['client_64_name'] == null || $data['client_32_name'] == null) {
            dieWithMessage('Error: "client_64_name" or "client_32_name" field\'s not found');
        }
        if (isset($data[$clientType]) && !empty($data[$clientType]) && !is_null($data[$clientType])) {
            $clientName = $data[$clientType];
        } else {
            if (isset($data['serverid']) && !empty($data['serverid']) && !is_null($data['serverid'])) {
                $siteServerId = $data['serverid'];
                $sql = $pdo->prepare("select * from install.Servers where serverid=?");
                $sql->execute([$siteServerId]);
                $srdata = $sql->fetch(PDO::FETCH_ASSOC);
                if ($srdata && isset($srdata[$clientType]) && !empty($srdata[$clientType]) && !is_null($srdata[$clientType])) {
                    $clientName = $srdata[$clientType];
                }
            }
        }
        $sitename = $data['sitename'];
//        $urldownload = $data['urldownload'];
        $urldownload = $downloadURL;
    } else {
        $sitename = 'NOSITE';
        dieWithMessage('Error: regCode not found');
    }
}

// To use customer specific url
$cstmt = $pdo->prepare("select cid, customer_name from " . $GLOBALS['PREFIX'] . "install.Customers where tenant_id = ? limit 1");
$cstmt->execute([$data['installuserid']]);
$cdata = $cstmt->fetch(PDO::FETCH_ASSOC);

// To get the domain name
$stmt = $pdo->prepare("select domainurl from " . $GLOBALS['PREFIX'] . "install.apiConfig order by id limit 1");
$stmt->execute();
$apidata = $stmt->fetch(PDO::FETCH_ASSOC);

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
    $clientName = url::getToAny('type') === 'branding' ? $data['brandingurl'] : $clientName;
    $pathFile = url::getToAny('type') === 'branding' ? 'branding' : 'live';

    $fileInfo = pathinfo($clientName);
    //  $localPath = $_SERVER['DOCUMENT_ROOT'] . "/Provision/download/" . $clientName;
    //$localPath = $data['urldownload']. $clientName;
    $localPath = '/home/nanoheal/setups/live/' . $clientName;
    //  $remotePath = '/home/nanoheal/setups/live/' . $clientName;
    $extension = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
    $regCode = url::requestToAny('rcode');
    $siteemailid = url::requestToAny('seid');
    $clientNameSegments = explode(".", $clientName);
    $encsitename = base64_encode($sitename);
    $domainurl = $domainurl . "Dashboard/";
    $myDomain = base64_encode($domainurl);
    $downloadFileName = $clientNameSegments[0] . '-' . $regCode . '-' . $siteemailid . '-' . $encsitename . '-' . $myDomain . '.' . $extension;

//    preg_match('/\/\/(.*?)\//', $data['urldownload'], $domain_download_arr);
    preg_match('/\/\/(.*?)\//', $downloadURL, $domain_download_arr);
    if (preg_match('#^https:\/\/#', $clientName)) {
        // clientName contain full link, not only file path on storage service.
        $url_download =   $clientName;
    } else {
        $url_download =  'https://' . $domain_download_arr[1] . '/storage/setups/' . $pathFile . '/' . $clientName;
    }


    if (url::getToAny('type') === 'branding') {
        header('Location: ' . $url_download);
        exit;
    }


    $localpath = '/home/nanoheal/setups/live/' . $clientName;
    if (file_exists($localpath)) {
        unlink($localpath);
    }

    $file = fopen($localpath, 'w');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url_download);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FILE, $file);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);
    if (curl_errno($ch)) {
        $curl_error_msg = curl_error($ch);

        dieWithMessage('cURL request fail (2): ' . $curl_error_msg . " URL=" . $url_download . " downloadFileName=$downloadFileName");
    }
    curl_close($ch);
    fclose($file);

    if (!file_exists($localpath)) {
        dieWithMessage('File not found by url: ' . $url_download);
    }

    ob_clean();
    header('Content-Length: ' . filesize($localpath));
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

    readfile($localpath); // send the file
    exit;  // make sure no extraneous characters get appended

    // header('Location: ' . $url_download);
}
