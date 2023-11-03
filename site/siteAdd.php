<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
require_once '../lib/l-sql.php';
require_once '../lib/l-gsql.php';
require_once '../lib/l-rcmd.php';
require_once '../lib/l-util.php';
global $licensePostapiUrl;
$pdo = pdo_connect();
$sitename = url::postToAny('sitename');
$custlist = url::postToAny('site_sub');
$plan = url::postToAny('site_plan');
$users = url::postToAny('users');

$servers = CURL::getContentByURL($licensePostapiUrl . "?action=listservers");

$serverslist = safe_json_decode($servers, true);
$serverId = 0;
$first = 0;
foreach ($serverslist as $key => $value) {
    if ($first == 0) {
        $serverId = $key;
        break;
    }
}

$postRequest = array(
    'action' => 'insertSite',
    "sitename" => htmlentities(strip_tags($sitename)),
    "custlist" => htmlentities(strip_tags($custlist)),
    "skulist" => htmlentities(strip_tags($plan)),
    "serverid" => strip_tags($serverId),
    "username" => $_SESSION['user']['username']
);

$cURLConnection = curl_init($licensePostapiUrl);
curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $postRequest);
curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($cURLConnection, CURLOPT_SSL_VERIFYPEER, false);

$apiResponse = curl_exec($cURLConnection);
curl_close($cURLConnection);

if (stripos($apiResponse, 'success') !== false) {

    $userList = explode(',', $users);
    $user_in = str_repeat('?,', safe_count($userList) - 1) . '?';
    $userSql = $pdo->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.Users WHERE userid IN ($user_in)");
    $userSql->execute($userList);
    $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($userRes as $value) {
        $insSql = $pdo->prepare("insert into ".$GLOBALS['PREFIX']."core.Customers set username = ?,customer=?,confstatus = '1'");
        $insSql->execute([$value['username'], $sitename]);
    }
    $_SESSION["user"]["site_list"] = array_merge($_SESSION["user"]["site_list"], array($sitename => $sitename));
    echo json_encode(array("status" => "success"));
}
