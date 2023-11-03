<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once ( '../lib/l-util.php' );

include_once '../includes/common_functions.php';


$ostype = url::requestToAny('osType');

$db = pdo_connect();
if ($ostype == 2) {
    $sql_scrip = $db->prepare("select distinct name,num from " . $GLOBALS['PREFIX'] . "iosprofile.Scripsnew where num > 2000 and num < 3000");
} else if ($ostype == 3) {
    $sql_scrip = $db->prepare("select distinct name,num from " . $GLOBALS['PREFIX'] . "iosprofile.Scripsnew where num > 3000 and num < 4000");
} else if ($ostype == 4) {
    $sql_scrip = $db->prepare("select distinct name,num from " . $GLOBALS['PREFIX'] . "iosprofile.Scripsnew where num > 4000 and num < 5000");
}
$sql_scrip->execute();
$fileType = 'iosScripGeneration.php';

while ($rows = $sql_scrip->fetchAll()) {
    $scripnum=$rows['num'];
    $scrpname=$rows['name'];
    $name='<a href="#" onclick="hide_show(\''.$scripnum.'\',\''.$scrpname.'\')" style="color:#5882FA;">'.$scrpname.'</a>';
    $iosConfigData[] = array($rows['num'], $name,$scripnum,$scrpname);
}
echo json_encode($iosConfigData);
