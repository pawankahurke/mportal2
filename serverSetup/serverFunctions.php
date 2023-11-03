<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-elastic.php';
require_once '../lib/l-vizualization.php';
include_once '../lib/l-setTimeZone.php';


nhRole::dieIfnoRoles(['site', 'licensedetails']); // roles: site, licensedetails


//Replace $routes['get'] with if else
if (url::postToText('function') === 'getServerConfiguration') { // roles: site, licensedetails
    getServerConfiguration();
} else if (url::postToText('function') === 'CurlFunction') { // roles: site, licensedetails
    CurlFunction();
}


function getServerConfiguration()
{
    $selected = url::requestToAny('selected');
    global $serverProtocol;
    global $reportingurl;
    $db = pdo_connect();
    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'dashboard_config' limit 1");
    $sql->execute();
    $res = $sql->fetch(PDO::FETCH_ASSOC);
    $confdata = safe_json_decode($res['value'], true);
    $wsurl = $confdata['wsurl'];
    if ($selected == 2) {
        $value = "Node Url";
        $url = $wsurl;
    } else if ($selected == 0) {
        $url = $confdata['licenseurl'];
    }

    $response = array(["value" => $value, "Url" => $url, "reportUrl" => $reportingurl]);

    echo json_encode($response);
}

function CurlFunction()
{
    $url2 = url::requestToAny('reportingurl');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(), 'Content-Type: text'
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $url2));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    $result = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo $httpcode;
}
