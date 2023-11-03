<?php

include_once '../config.php';
include_once '../lib/l-dbConnect.php';

// error_reporting(E_ALL);
// ini_set('display_errors', 'On');

$pdo = pdo_connect();

$date = date("Y-m-d");
$today = strtotime($date);
$weekdate = strtotime(date("Y-m-d", strtotime($date)) . " -1 week");
// echo "currentDate:".$today.PHP_EOL;
// echo "weekDate:".$weekdate.PHP_EOL;exit;

delete308($pdo);

function delete308($pdo)
{
    $sql = "delete from  " . $GLOBALS['PREFIX'] . "event.Events where scrip = 308 limit 50000";
    $stmt = $pdo->prepare($sql);
    $res = $stmt->execute();

    if ($res) {
        $stmt1 = $pdo->prepare("select count(*) as count from  " . $GLOBALS['PREFIX'] . "event.Events where scrip = 308");
        $stmt1->execute();
        $crmres = $stmt1->fetch(PDO::FETCH_ASSOC);

        $count = $crmres['count'];
        writelog("Current Entries for 308 in events Table:" . $count . PHP_EOL);
    } else {
        writelog("Error in deleting the 308 entries");
    }
}

function writelog($message)
{
    logs::log(__FILE__, __LINE__, $message, ['tag' => "c-eventLog"]);
}
