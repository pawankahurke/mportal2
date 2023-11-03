<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
require_once '../include/common_functions.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-setTimeZone.php';
$pdo = pdo_connect();
$user_name = $_SESSION["user"]["username"];
if ($_FILES['notify_file']['size'] > 0) {
    $file = $_FILES['notify_file']['tmp_name'];
    $insertedRecords = 0;
    $failedRecords = 0;
    $duplicaterecord = 0;
    $key = '';
    $handle = fopen($file, "r");
    $total = count(file($file, FILE_SKIP_EMPTY_LINES)) - 1;
    $olddetails = [];
    $newdetails = [];
    $dupname = [];
    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {

        if ($data[1] && $data[1] != 'Site' && $data[0] && $data[0] != 'Name') {
//            $testCriteria = url::postToText(safe_json_decode($data[4], true));
          $testCriteria = safe_json_decode($data[4], true);
            if (!empty($testCriteria)) {
//                $notifyname = url::postToText($data[0]);

//                $group = url::toText($data[1]);
//                $notifyType = url::toText(getFilterType($data[2]));
//                $npriority = url::toText($data[3]);
//                $seconds = url::toText($data[4]);
//                $criteria = url::toText($data[5]);
//                $searchTxt = url::toText($data[6]);
//                $where_txt = url::toText($data[7]);
//                $showTxt = url::toText($data[8]);
//                $scrip = url::toText($data[9]);
                $notifyname = $data[0];
                $group = $data[1];
                $notifyType = getFilterType($data[2]);
                $npriority = $data[3];
                $seconds = $data[4];
                $criteria = $data[5];
                $searchTxt = $data[6];
                $where_txt = $data[7];
                $showTxt = $data[8];
                $scrip = $data[9];
                $notify = checkNotificationDup($pdo, $notifyname);
                $plain_txt = getPlainTxt($searchTxt, $where_txt, $showTxt);
                $dartConfigId = getDartConfigure($pdo, $scrip, $showTxt);
                $priority = getPriority($npriority);
                $enabled = 0;
                if ($data[11] == 'Enabled') {
                    $enabled = 1;
                }
              logs::log('Name' . $notifyname);
              logs::log('Countable' . safe_count($notify));
                if (safe_count($notify) == 0) {
                    $notifySt = $pdo->prepare("insert into  " . $GLOBALS['PREFIX'] . "event.Notifications(name,username,console,ntype,priority,seconds,threshold,last_run,next_run,criteria,search_txt,show_txt,where_txt,scrip,enabled,created,dartConfigId,plain_txt,group_include) values(?,?,1,?,?,?,0,0,0,?,?,?,?,?,?,?,?,?,'All');");
                    $notifySt->execute([$notifyname, $user_name, $notifyType, $priority, $seconds, $criteria, $searchTxt, $showTxt, $where_txt, $scrip, $enabled, time(), $dartConfigId, $plain_txt]);
                    $lastId = $pdo->lastInsertId();
                    if ($lastId > 0) {
                        $insertedRecords = $insertedRecords + 1;
                    }
                } else {
                  logs::log('Update' . $notifyname);
                    try {
                        $notifySt = $pdo->prepare("update  " . $GLOBALS['PREFIX'] . "event.Notifications set ntype=?,priority=?,seconds=?,criteria =?,search_txt=?,show_txt=?,where_txt=?,scrip=?,modified =?,dartConfigId =?,plain_txt =?,enabled=?,compCriteria = NULL where name = ?;");
                        $notifyres = $notifySt->execute([$notifyType, $priority, $seconds, $criteria, $searchTxt, $showTxt, $where_txt, $scrip, time(), $dartConfigId, $plain_txt, $enabled, $notifyname]);
                        if ($notifyres) {
                            $duplicaterecord = $duplicaterecord + 1;
                        }
                    } catch (PDOException $e) {
                      logs::log('Connection failed: ' . $e->getMessage());
                    }
                }
            } else {
                $failedRecords = $failedRecords + 1;
            }
        }
    }


    echo json_encode(array("status" => "success", "total" => $total, "insert" => $insertedRecords, "failed" => $failedRecords, "duplicate" => $duplicaterecord));
}

function getFilterType($item)
{
    $typearray = 0;
    if ($item == 'Availability') {
        $typearray = 1;
    }
    if ($item == 'Security') {
        $typearray = 2;
    }
    if ($item == 'Resource') {
        $typearray = 3;
    }
    if ($item == 'Maintenance') {
        $typearray = 4;
    }
    if ($item == 'Events of Interest') {
        $typearray = 5;
    }
    return $typearray;
}

function checkNotificationDup($pdo, $name)
{

    $chkSql = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where name = ?');
    $chkSql->execute([$name]);
    $chkcount = $chkSql->fetchAll(PDO::FETCH_ASSOC);

    return $chkcount;
}

function getPriority($priority)
{

    if ($priority == 'P1') {
        return 1;
    }
    if ($priority == 'P2') {
        return 2;
    }
    if ($priority == 'P3') {
        return 3;
    }
}

function getPlainTxt($search_txt, $show_txt, $where_txt)
{
    $param = str_replace('$event->', '', $search_txt);
    $show_txt = str_replace('$event->', '', $show_txt);
    $plain_txt = '';
    if (strpos($param, '&&')) {
        $plain_txt = $param;
    } else if (strpos($param, 'strpos') !== false) {
        $par = explode('like', $where_txt);
        if (safe_count($par) > 0) {

            $par[1] = str_replace('%', '', $par[1]);
            $plain_txt = $show_txt . " like " . $par[1];
        }
    } else {
        $p = str_replace('->', '.', $param);
        $arr = array('>', '<', '==');
        $r = array();
        foreach ($arr as $ar) {

            if (strpos($p, $ar) !== false) {
                $r = explode($ar, $p);

                if (safe_count($r) > 0) {
                    $r[0] = str_replace('.', '->', str_replace(' ', '_', trim($r[0])));
                    $r[1] = str_replace(' ', '_', trim($r[1]));
                    $logc = str_replace('==', '=', $ar);
                    array_push($r, $logc);
                    $plain_txt = $r[0] . " " . $logc . " " . $r[1];
                    break;
                }
            }
        }
    }



    return $plain_txt;
}

function getDartConfigure($pdo, $dart, $show_txt)
{

    $shtxt = str_replace('$event->', '', $show_txt);

    $dartSql = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.dart_config where dartConfig = ? and dartNo = ?');
    $dartSql->execute([$shtxt, $dart]);
    $dartRes = $dartSql->fetch(PDO::FETCH_ASSOC);
    return $dartRes['id'];
}
