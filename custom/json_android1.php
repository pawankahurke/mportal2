<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';

ini_set('max_execution_time', 7000);
ini_set('max_input_time', 300);
ini_set('upload_max_filesize', '60M');
ini_set('max_execution_time', '999');
ini_set('memory_limit', '128M');
ini_set('post_max_size', '60M');

$db = pdo_connect();

$reqsiteval = url::requestToText('site');
$json = '';



$Sql = $db->prepare("SELECT id, packageName, path, fileName, packageDesc, androidIcon, version, androiddate, androidnotify, androidUninstall,"
        . "androidSite, preinstall, oninstall, postdownload, status from ".$GLOBALS['PREFIX']."softinst.Packages WHERE platform = 'android' AND "
        . "sourceType = '5' AND status = 'Uploaded' order by id");
$Sql->execute();
$sqlRes = $Sql->fetchAll();

$size = safe_count($sqlRes);

if ($size == 0 || $reqsiteval == "") {
    echo "No Records Found";
} else {

    foreach ($sqlRes as $key => $value) {
        $siteListPData = [];
        $siteList = explode(',', $value['androidSite']);
        foreach ($siteList as $sitekey => $siteval) {
            $siteListPData[] = explode('__', $siteval)[0];
        }

        if (in_array($reqsiteval, $siteListPData)) {
            $parsedData[$value['packageName']] = $sqlRes[$key];
        }
    }

    $json .= '{"Apps" :[';
    foreach ($parsedData as $key => $value) {
        $tempArr = explode(".", $value['fileName']);

        $json .= '{"id":"' . $value['id'] . '",'
                . '"fileName":"' . $value['packageDesc'] . '",'
                . '"packageName":"' . $value['packageName'] . '",'
                . '"packageDesc":"' . $value['packageDesc'] . '",'
                . '"appUrl":"' . $value['path'] . '",'
                . '"date":"' . $value['androiddate'] . '",'
                . '"notify":"' . $value['androidnotify'] . '",'
                . '"uninstall":"' . $value['androidUninstall'] . '",'
                . '"iconUrl":"' . $value['androidIcon'] . '",'
                . '"version":"' . $value['version'] . '",'
                . '"preinstall":"' . $value['preinstall'] . '",'
                . '"postdownload":"' . $value['postdownload'] . '",'
                . '"oninstall":"' . $value['oninstall'] . '"},';
    }
    $json = rtrim($json, ",");
    $json .= ']}';

    echo $json;
}

?>
