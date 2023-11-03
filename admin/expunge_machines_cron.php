<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
$cronConfig = new CronFunction();
if ($cronConfig->startPermission('expunge_machines', 'EXPUNGE MACHINES') != 'true') {
    echo 'no launch rights';
    exit();
}
$cronConfig->updateMonitoring('EXPUNGE MACHINES');

if ((int)getenv('EXPUNGE_IF_DEVICE_NOT_ACTIVE') <= 0) {
    echo "Env var is not EXPUNGE_IF_DEVICE_NOT_ACTIVE === any date";
    die();
}

include_once '../config.php';
include_once '../lib/l-dbConnect.php';

function send_line_to_log($str)
{
    $url = 'http://' . getenv('DASHBOARD_SERVICE_HOST') . '/Dashboard/admin/expunge_append_line_to_log.php';
    $xxx = CURL::sendDataCurl($url, array(
        'token' => getenv('APP_SECRET_KEY'),
        'line' => $str,
    ));
}

echo '<pre>';
$pdo = pdo_connect();

$removeTimeTreshold = time() - ((int)getenv('EXPUNGE_IF_DEVICE_NOT_ACTIVE') * 3600 * 24);

$filename = 'GENERIC';
$machstmt = $pdo->prepare("select site, host from " . $GLOBALS['PREFIX'] . "core.Census where last <= ? ");
$machstmt->execute([$removeTimeTreshold]);
$machdata = $machstmt->fetchAll(PDO::FETCH_ASSOC);

$datenow_with_time = date('d-m-Y h:i:s', time());

send_line_to_log('------ Expunging Machines (Bulk from cron job) ' . $datenow_with_time);

if (count($machdata) === 0) {
    send_line_to_log('Nothing to deleting ' . $datenow_with_time);
    die();
}

foreach ($machdata as $value) {

    $sitename = $value['site'];
    $machine = $value['host'];

    send_line_to_log('Site : ' . $sitename . ' ---- Machine : ' . $machine . ' === ');

    // Delete from Asset
    $asset_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'asset.Machine where host = ? and cust = ?');
    $asset_stmt->execute([$machine, $sitename]);

    $asst_stmt = $pdo->prepare('select machineid from ' . $GLOBALS['PREFIX'] . 'asset.Machine where host = ? and cust = ?');
    $asst_stmt->execute([$machine, $sitename]);
    $asst_data = $asst_stmt->fetch(PDO::FETCH_ASSOC);

    $machineid = $asst_data['machineid'];

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

        send_line_to_log('Expunge Status : Success');
    } else {
        send_line_to_log('Expunge Status : Failed');
    }
}

function deleteDataFromRedis($machine)
{
    $ServiceTag = $machine;
    $redis = RedisLink::connect();
    $redis->select(0);
    $redis->del("$ServiceTag", 0, -1);
}
