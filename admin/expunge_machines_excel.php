<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once '../config.php';
include_once '../lib/l-dbConnect.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

$fileName = url::requestToAny('fileName');

$localpath = '/home/nanoheal/'.$fileName;
$url_download = str_replace('/visualization', '/storage/expunge/', getenv('VISUALISATION_SERVICE_DASH_URL')).$fileName;

$file = fopen($localpath, 'w');
echo $url_download;
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
    dieWithMessage('cURL request fail: ' . $curl_error_msg . " URL=" . $url_download);
}

curl_close($ch);
fclose($file);

if (!file_exists($localpath)) {
    dieWithMessage('File not found by url: ' . $url_download);
}

$excelReader = PHPExcel_IOFactory::createReaderForFile($localpath);
$excelObj = $excelReader->load($localpath);
$worksheet = $excelObj->getSheet(0);
$lastRow = $worksheet->getHighestRow();

$ids = [];

for ($row = 2; $row <= $lastRow; $row++) {
    $ids[] = $worksheet->getCell('B'.$row)->getValue();
}

echo '<pre>';
$pdo = pdo_connect();

$filename = 'GENERIC';
$qMarks = str_repeat('?,', count($ids) - 1) . '?';

$machstmt = $pdo->prepare("select site,host from core.Census where host IN ($qMarks)");
$machstmt->execute($ids);
$machdata = $machstmt->fetchAll(PDO::FETCH_ASSOC);

//$machine_array = [];
$datenow = date('d-m-Y', time());
$fp = fopen('expunge_' . $filename . '_' . $datenow . '_log.txt', 'a');

$datenow_with_time = date('d-m-Y h:i:s', time());
fwrite($fp, '------ Expunging Machines (Bulk from Excel file) ' . $datenow_with_time . PHP_EOL);

echo '<a href="expunge_' . $filename . '_' . $datenow . '_log.txt">Download Log File for ' . $filename . '</a><br/><br/>';

if(count($machdata) === 0){
    fwrite($fp, 'Nothing to deleting ' . $datenow_with_time. PHP_EOL);    
    die();
}

foreach ($machdata as $value) {

    $sitename = $value['site'];
    $machine = $value['host'];

    fwrite($fp, 'Site : ' . $sitename . ' ---- Machine : ' . $machine . ' === '. PHP_EOL);
    // Delete from Asset
    $asset_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'asset.Machine where host = ? and cust = ?');
    $asset_stmt->execute([$machine, $sitename]);

    $asst_stmt = $pdo->prepare('select machineid from ' . $GLOBALS['PREFIX'] . 'asset.Machine where host = ? and cust = ?');
    $asst_stmt->execute([$machine, $sitename]);
    $asst_data = $asst_stmt->fetch(PDO::FETCH_ASSOC);

    $machineid = $asst_data['machineid']; //error

    $asset_stmt2 = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'asset.AssetData where machineid = ? ');
    $asset_stmt2->execute([$machineid]);

    // Delete from Events
    $asset_stmt = $pdo->prepare('delete from  ' . $GLOBALS['PREFIX'] . 'event.Events where machine = ? and customer = ?');
    $asset_stmt->execute([$machine, $sitename]);

    // Delete for MUM
    $mum_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'swupdate.UpdateMachines where machine = ? and sitename = ?;');
    $mum_del_stmt->execute([$machine, $sitename]);

    $cen_stmt = $pdo->prepare('select id, censusuniq from ' . $GLOBALS['PREFIX'] . 'core.Census where site = ? and host = ? limit 1');
    $cen_stmt->execute([$sitename, $machine]);
    $cen_data = $cen_stmt->fetch(PDO::FETCH_ASSOC);

    deleteDataFromRedis($machine);

    if ($cen_data) {

        $census_id = $cen_data['id'];
        $censusuniq = $cen_data['censusuniq'];

        // delete queries
        $mch_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'softinst.Machine where id = ?');
        $mch_del_stmt->execute([$census_id]);

        $pat_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'softinst.PatchStatus where id = ?');
        $pat_del_stmt->execute([$census_id]);

        // Machine Groups
        $mgrp_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap using ' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap left join '
            . $GLOBALS['PREFIX'] . 'core.Census on (' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap.censusuniq = ' . $GLOBALS['PREFIX'] . 'core.Census.censusuniq) where id = ?;');
        $mgrp_stmt->execute([$census_id]);

        // MachineGroups delete
        $mgdelete_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.MachineGroups where mgroupuniq = ?');
        $mgdelete_stmt->execute([$censusuniq]);

        // ValueMap Delete
        $valumap_stmt = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.ValueMap where censusuniq = ?");
        $valumap_stmt->execute([$censusuniq]);

        // Census
        $cen_del_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.Census where id = ?');
        $cen_del_stmt->execute([$census_id]);

        //Census Perm
        // $cenp_del_stmt = $pdo->prepare('delete from '.$GLOBALS['PREFIX'].'core.CensusPerm where id = ?');
        // $cenp_del_stmt->execute([$census_id]);

        fwrite($fp, 'Expunge Status : Success' . PHP_EOL);
    } else {
        fwrite($fp, 'Expunge Status : Failed' . PHP_EOL);
    }
}
fclose($fp);

function deleteDataFromRedis($machine)
{
    $ServiceTag = $machine;
    $redis = RedisLink::connect();
    $redis->select(0);
    $redis->del("$ServiceTag", 0, -1);
}
