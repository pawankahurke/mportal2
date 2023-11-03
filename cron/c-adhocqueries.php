<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../asset/adhoc_helper.php';

global $elastic_url;
global $elastic_username;
global $elastic_password;

$elastic_url = "https://a07150e6ff924561a03682e150728afa.us-central1.gcp.cloud.es.io:9243/";
$elastic_username = 'writer';
$elastic_password = 'ut@AZ$5Ra?JA9!mwz';



function setUpCronEnv($pdo) {
    global $cron;

    $pending = 0;
    $processing = 0;
    $queryTotal = $pdo->prepare("select count(id) as count, cronstatus from ".$GLOBALS['PREFIX']."agent.adhocInfoPortal group by cronstatus order by id asc");
    $queryTotal->execute();
    $result = $queryTotal->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $value) {
        if ($value['cronstatus'] == 1) {
            $processing = $value['count'];
        } else if ($value['cronstatus'] == 0) {
            $pending = $value['count'];
        }
    }
    if ($processing == 0 && $pending > 0) {
        $cron = 1;
    } else {
        $cron = 0;
    }
}

function getQueryDetails($pdo) {
    $query = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."agent.adhocInfoPortal where cronstatus = ? order by id asc limit 1");
    $query->execute([0]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function setProcessExecution($pdo, $id) {
    $st = time();

    $queryInsert = $pdo->prepare("update ".$GLOBALS['PREFIX']."agent.adhocInfoPortal set startTime = ?, status = ?, cronstatus = ? where id = ?");
    $queryInsert->execute([$st, 'Process Running', 1, $id]);
}

function endProcessExecution($pdo, $id, $filename) {
    $status = 'Completed';
    $now = time();
    $queryInsert = $pdo->prepare("update ".$GLOBALS['PREFIX']."agent.adhocInfoPortal set endTime = ?, fileName = ?, status = ?,cronstatus = ? where id = ?");
    $queryInsert->execute([$now, $filename, $status, 2, $id]);
}

function getRunningQueryDetails($pdo) {
    $query = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."agent.adhocInfoPortal where cronstatus = ? order by id asc limit 1");
    $query->execute([1]);
    $result = $query->fetch(PDO::FETCH_ASSOC);
    return $result;
}

function updateProcessExecution($queryDetails, $pdo) {
    $retryunit = $queryDetails['retryunit'];
    if($retryunit >= 5) {
                $sql = $pdo->prepare("update ".$GLOBALS['PREFIX']."agent.adhocInfoPortal set cronstatus = ?, retryunit = ? where id = ?");
        $sql->execute([0, 0, $queryDetails['id']]);
    } else {
                $newretryunit = ($retryunit + 1);
        $sql = $pdo->prepare("update ".$GLOBALS['PREFIX']."agent.adhocInfoPortal set retryunit = ? where id = ?");
        $sql->execute([$newretryunit, $queryDetails['id']]);
    }
}


$pdo = pdo_connect();

setUpCronEnv($pdo);

if ($cron == 1) {
    $queryDetails = getQueryDetails($pdo);
    $aid = $queryDetails['id'];
    setProcessExecution($pdo, $aid);
    $result = cron_adhoc_query($pdo, $aid, $queryDetails);

    $filename = $result['filename'];
    endProcessExecution($pdo, $aid, $filename);
} else if ($cron == 0) {
        $queryDetails = getRunningQueryDetails($pdo);
    updateProcessExecution($queryDetails, $pdo);
    
    } else {
    echo 'Nothing to execute';
}
