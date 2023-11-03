<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';

logs::log("RPC MobileRegister:", [
  'REQUEST_URI' => $_SERVER['REQUEST_URI'],
  'QUERY_STRING' => $_SERVER['QUERY_STRING'],
  "getallheaders" => getallheaders(),
  "input" => file_get_contents('php://input'),
  "request" => $_REQUEST,
  "post" => $_POST,
  "get" => $_GET,
  "_FILES" => $_FILES
]);

$ServiceTag = url::issetInRequest('ServiceTag') ? url::requestToAny('ServiceTag') : '';
$OrderNumber = url::issetInRequest('OrderNumber') ? url::requestToAny('OrderNumber') : '';
$VersionNumber = url::issetInRequest('VersionNumber') ? url::requestToAny('VersionNumber') : '';
$MobileOS = url::issetInRequest('MobileOS') ? url::requestToAny('MobileOS') : '';
$SiteID = url::issetInRequest('SiteID') ? url::requestToAny('SiteID') : '';
$MobileID = url::issetInRequest('MobileID') ? url::requestToAny('MobileID') : '';
$MessagingType = url::issetInRequest('MessagingType') ? url::requestToAny('MessagingType') : '';
$machineManufacture = url::issetInRequest('machineManufacture') ? str_replace("'", "", url::requestToAny('machineManufacture')) : '';
$machineModelNum = url::issetInRequest('machineModelNum') ? str_replace("'", "", url::requestToAny('machineModelNum')) : '';
$uninstallDate = url::issetInRequest('uninstallDate') ? strtotime(url::requestToAny('uninstallDate')) : '';
$installDate = time();

$dbo = NanoDB::connect();

$isInserted = false;

$copdo = $dbo->prepare("SELECT customerNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE orderNum = ?");
$copdo->execute([$OrderNumber]);
$res = $copdo->fetch();
$CustomerNumber = 0;

if ($res && $res['customerNum']) {
  $CustomerNumber = $res['customerNum'];
}

$pdo = $dbo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE serviceTag=? AND siteName=?");
$pdo->execute([$ServiceTag, $SiteID]);

if ($pdo->fetch(PDO::FETCH_ASSOC)) {
  $isInserted = true;
}

$result = null;

if ($isInserted) {
  $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.serviceRequest set installationDate=?,uninstallDate=?,machineManufacture=?, machineModelNum=?, MobileID=?, MobileType=?, clientVersion=? where customerNum=? and orderNum=? and serviceTag=? and siteName=?";
  $pdo = $dbo->prepare($sql);
  $bindings = [$installDate, $uninstallDate, $machineManufacture, $machineModelNum, $MobileID, $MessagingType, $VersionNumber, $CustomerNumber, $OrderNumber, $ServiceTag, $SiteID];
  $result = $pdo->execute($bindings);

  logs::log("RPC MobileRegister:", ["input" => file_get_contents('php://input'), "sql" => $sql, "bindings" => $bindings, "result" =>  $result]);
} else {
  $createdTime = time();
  $query = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest (`serviceTag`,`siteName`,`customerNum`, `orderNum`, `MobileID`, `MobileType`, `clientVersion`, `createdTime`, `uninsdormatDate` ,`machineManufacture` ,`machineModelNum`,`installationDate`,`uninstallDate`) VALUES (?, ?, ?, ?, ?, ?, ?, ? , ?, ?, ?,?,?)";
  $bindings = [$ServiceTag, $SiteID, $CustomerNumber, $OrderNumber, $MobileID, $MessagingType, $VersionNumber, $createdTime, $createdTime, $machineManufacture, $machineModelNum, $installDate, $uninstallDate];
  $pdo = $dbo->prepare($query);
  $result = $pdo->execute($bindings);
  logs::log("RPC MobileRegister:", ["input" => file_get_contents('php://input'), "sql" => $sql, "bindings" => $bindings, "result" =>  $result]);
}

$costmt = $dbo->prepare("update " . $GLOBALS['PREFIX'] . "agent.customerOrder set contractEndDate = ? where orderNum = ?");
$costmt->execute([$uninstallDate, $OrderNumber]);

if (!$result) {
  echo "1";
  die('There was an error running the query');
} else {
  try {
    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);
    $Redisres = $redis->lrange("$ServiceTag", 0, -1);
    if (safe_count($Redisres) > 0) {
      $redis->del("$ServiceTag");
      $redis->rpush(trim($ServiceTag, '"'), trim($ServiceTag, '"'), trim($CustomerNumber, '"'), trim($OrderNumber, '"'), trim($VersionNumber, '"'), trim($MobileOS, '"'), "Online", trim($MobileID, '"'));
    } else {
      $redis->rpush(trim($ServiceTag, '"'), trim($ServiceTag, '"'), trim($CustomerNumber, '"'), trim($OrderNumber, '"'), trim($VersionNumber, '"'), trim($MobileOS, '"'), "Online", trim($MobileID, '"'));
    }
  } catch (Exception $exc) {
    logs::log(__FILE__, __LINE__, $exc, 0);
    echo "1";
  }

  echo "0";
}
