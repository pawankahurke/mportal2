<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once '../lib/l-dbConnect.php';

$pdo = pdo_connect();
echo '<pre>';

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

function checkSWMachineEntry($pdo,$sitename,$machine){
    $stmt = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."asset.cmdbSoftwareData where site= ? and host=?");
    $stmt->execute([$sitename,$machine]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$data){
        $swinsert = $pdo->prepare("insert into ".$GLOBALS['PREFIX']."asset.cmdbSoftwareData(site,host) 
        select site,host from ".$GLOBALS['PREFIX']."core.Census where site = ? and host = ?");
        $swinsert->execute([$sitename,$machine]);
        $swresult = $pdo->lastInsertId();
        $result = true;
    }else{
        $swinsert = $pdo->prepare("update ".$GLOBALS['PREFIX']."asset.cmdbSoftwareData set status = 0 where site = ? and host = ? order by servertime desc limit 1");
        $swinsert->execute([$sitename,$machine]);
        $swresult = $pdo->lastInsertId();
        $result = false; 
    }
    return $result;
}

function checkHWMachineEntry($pdo,$sitename,$machine){
    $stmt = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."asset.cmdbHardwareData where site= ? and host=?");
    $stmt->execute([$sitename,$machine]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(!$data){
        $swinsert = $pdo->prepare("insert into ".$GLOBALS['PREFIX']."asset.cmdbHardwareData(site,host) 
        select site,host from ".$GLOBALS['PREFIX']."core.Census where site = ? and host = ?");
        $swinsert->execute([$sitename,$machine]);
        $swresult = $pdo->lastInsertId();
        $result = true;
    }else{
        $swinsert = $pdo->prepare("update ".$GLOBALS['PREFIX']."asset.cmdbHardwareData set status = 0 where site = ? and host = ? order by servertime desc limit 1");
        $swinsert->execute([$sitename,$machine]);
        $swresult = $pdo->lastInsertId();
        $result = false;
    }
    return $result;
}
//Final
$stmt = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."core.Census where site like '%SCCC%'");
// $stmt = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."core.Census where site = 'JSFB_Desktop'");
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($data as $key => $value) {
    $sitename = $value['site'];
    $machine = $value['host'];

    $resultSW = checkSWMachineEntry($pdo,$sitename,$machine);

    if($resultSW){
        echo "New Entries inserted in ".$GLOBALS['PREFIX']."asset.cmdbSoftwareData ".PHP_EOL;
    }else{
        echo "Table ".$GLOBALS['PREFIX']."asset.cmdbSoftwareData already updated ".PHP_EOL;
    }

    $resultHW = checkHWMachineEntry($pdo,$sitename,$machine);

    if($resultHW){
        echo "New Entries inserted in ".$GLOBALS['PREFIX']."asset.cmdbHardwareData ".PHP_EOL;
    }else{
        echo "Table ".$GLOBALS['PREFIX']."asset.cmdbHardwareData already updated ".PHP_EOL;
    }
}



