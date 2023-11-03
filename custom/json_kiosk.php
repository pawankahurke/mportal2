<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
require_once("../include/common_functions.php");

ini_set('max_execution_time', 7000);
ini_set('max_input_time', 300);
ini_set('upload_max_filesize', '60M');
ini_set('max_execution_time', '999');
ini_set('memory_limit', '128M');
ini_set('post_max_size', '60M');

if (url::requestToAny('debug') != '323') {
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $chrome = '/Chrome/';
    $firefox = '/Firefox/';
    $ie = '/Trident/';
    if (preg_match($chrome, $browser) || preg_match($firefox, $browser) || preg_match($ie, $browser)) {
        echo "Not Allowed";
        die();
    }
}

$db = pdo_connect();

$site = url::requestToText('site');

$Sql = $db->prepare("select CONCAT_WS(';',scripStatus,userlist,userConfig,lockScreen) as configData,enableEmergency,emergencyContacts from ".$GLOBALS['PREFIX']."profile.ConfigKiosk where sitename=? limit 1");
$Sql->execute([$site]);
$sqlRes = $Sql->fetch();

$size = safe_count($sqlRes);
if ($size == 0) {

   $Sql = $db->prepare("select CONCAT_WS(';',scripStatus,userlist,userConfig,lockScreen) as configData,enableEmergency,emergencyContacts from ".$GLOBALS['PREFIX']."profile.ConfigKiosk where sitename='All' limit 1");
   $Sql->execute();
   $sqlRes = $Sql->fetch();
   
} 
$enableEmergency= $sqlRes['enableEmergency'];

$emergencyContacts = $sqlRes['emergencyContacts'];

if($enableEmergency == 'TRUE'){
   
    echo $sqlRes['configData'].';'.$emergencyContacts;
} else {
    echo $sqlRes['configData'].';'.$enableEmergency;
}


?>
