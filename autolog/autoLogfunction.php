<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
require_once '../include/common_functions.php';
include_once '../lib/l-db.php';
LOG_getLogDetails();

function LOG_getLogDetails() {
    $selectedType = url::requestToStringAz09('type');
    $db = pdo_connect();
    $res = checkModulePrivilege('loginaudit', 2);
//    $recordList = [];
    if (!$res) {
        $jsonData = array();
        echo json_encode($jsonData);
        exit();
    }
//    $draw = 1;

    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    if(empty(url::postToInt('nextPage'))){
        $curPage = 0;
    }else{
        $curPage = url::postToInt('nextPage') - 1;
    }

    $orderVal = url::postToStringAz09('order') === 'deviceName' ? "rawReference->>'$.name'" : url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        $orderStr = 'order by '.$orderVal.' '.$sortVal;
        $orderStr1 = 'order by '.$orderVal.' '.$sortVal;
    } else {
        $orderStr = ' order by created desc';
        $orderStr1 = ' order by JobCreatedTime desc';
    }
    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    $notifSearch = url::postToText('notifSearch');
    if ($notifSearch != '') {
        // $whereSearch = " and  (module LIKE '%" . $notifSearch . "%'
        //     OR action  LIKE '%" . $notifSearch . "%'
        //     OR username  LIKE '%" . $notifSearch . "%'
        //     OR useremail  LIKE '%" . $notifSearch . "%'
        //     OR created  LIKE '%" . $notifSearch . "%'
        //     OR refName  LIKE '%" . $notifSearch . "%'
        //     OR status  LIKE '%" . $notifSearch . "%')";

        $whereSearch1 = " and  (JobType LIKE '%" . $notifSearch . "%'
            OR MachineTag  LIKE '%" . $notifSearch . "%'
            OR FROM_UNIXTIME(JobCreatedTime) LIKE '%" . $notifSearch . "%'
            OR SelectionType  LIKE '%" . $notifSearch . "%'
            OR Dart LIKE '%" . $notifSearch . "%'
            OR AgentName LIKE '%" . $notifSearch . "%'
            OR AgentUniqId LIKE '%" . $notifSearch . "%'
            OR IDX LIKE '%" . $notifSearch . "%'
            OR MachineOs LIKE '%" . $notifSearch . "%'
            OR ProfileName LIKE '%" . $notifSearch . "%'
            OR ProfileSequence LIKE '%" . $notifSearch . "%'
            OR FROM_UNIXTIME(ClientExecutedTime) LIKE '%" . $notifSearch . "%'
            OR ClientTimeZone LIKE '%" . $notifSearch . "%'
            OR DartExecutionProof LIKE '%" . $notifSearch . "%')";
    }else {
        $whereSearch = '';
        $whereSearch1 = '';
    }
    if($limitStart > 0){
        $limitStr = " LIMIT ".$limitStart.",".$limitCount;
    }else{
        $limitStr = " LIMIT ".$limitStart.",".$limitEnd;
    }

    $userId = $_SESSION['user']['userid'];
    $roleId = $_SESSION['user']['role_id'];
    $sql = $db->prepare("SELECT user_email FROM ".$GLOBALS['PREFIX']."core.Users where userid = ?");
    $sql->execute([$userId]);
    $SqlRes = $sql->fetch();
    $userEmail = $SqlRes['user_email'];
    $filterStr = '';
    $filterStr1 = '';
    $usrArr = getChildDetails($userId, 'user_email');
    $usrArrData = array_merge([$userEmail], $usrArr);

//    $last24hrs = date('Y-m-d H:i:s', strtotime('-30 Days'));
    $in = str_repeat('?,', safe_count($usrArrData) - 1) . '?';
    if ((int) $roleId != 96) {
        $filterStr = "useremail in($in) and ";
        $filterStr1 = "AgentUniqId in ($in) and ";
    }

    if ($selectedType == 'notif'){
        $moduleName = 'Notification';
        $moduleName1 = 'Notification';
    }elseif ($selectedType == 'trbl') {
        $moduleName1 = 'Interactive';
    }elseif ($selectedType == 'solution') {
        $moduleName1 = 'Push Solution API';
    }elseif ($selectedType == 'distribution') {
        $moduleName1 = 'Software Distribution';
    }
   
    if ($filterStr1 != '') {
        $params = array_merge($usrArrData, [$moduleName]);
        $params1 = array_merge($usrArrData, [$moduleName1]);
    } else {
        $params = array_merge([$moduleName]);
        $params1 = [$moduleName1];
    }
//  $result = NanoDB::find_many("SELECT *, refName as deviceName
//       FROM ".$GLOBALS['PREFIX']."core.AuditLog
//       WHERE " . $filterStr . " module = ?
//       AND action not like '%view%'
//       AND created > NOW() - INTERVAL 30 DAY
//       AND action not like '%export%' $whereSearch $orderStr $limitStr", null,$params );

    // $sql2 = $db->prepare("SELECT *, refName as deviceName
    //  FROM ".$GLOBALS['PREFIX']."core.AuditLog
    //  WHERE " . $filterStr . " module = ?
    //  AND action not like '%view%'
    //  AND created > NOW() - INTERVAL 30 DAY
    //  AND action not like '%export%' $whereSearch $orderStr $limitStr");

    // $sql2->execute($params);
    // $result = $sql2->fetchAll(PDO::FETCH_ASSOC);

    // $sql = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.AuditLog
    //   WHERE " . $filterStr . " created > NOW() - INTERVAL 30 DAY
    //   and  module = ?
    //   and action not like '%view%'
    //   and action not like '%export%' $whereSearch $orderStr");

    // $sql->execute($params);
    // $totCount = safe_count($sql->fetchAll(PDO::FETCH_ASSOC));

    $sql3 = $db->prepare("SELECT AID, MachineTag as deviceName, FROM_UNIXTIME(JobCreatedTime) as JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId as agentEmailID, IDX, JobType, MachineOs, ProfileName, ProfileSequence, ClientTimeZone, FROM_UNIXTIME(ClientExecutedTime) as ClientExecutedTime, JobStatus, DartExecutionProof
     FROM communication.Audit
     WHERE ". $filterStr1 ." JobType = ?
     AND Date(FROM_UNIXTIME(JobCreatedTime)) > Date(NOW() - INTERVAL 30 DAY) $whereSearch1 $orderStr1 $limitStr");

    $sql3->execute($params1);
    $result1 = $sql3->fetchAll(PDO::FETCH_ASSOC);

    $sql4 = $db->prepare("SELECT AID, MachineTag as deviceName, FROM_UNIXTIME(JobCreatedTime) as JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId as agentEmailID, IDX, JobType, MachineOs, ProfileName, ProfileSequence, ClientTimeZone, FROM_UNIXTIME(ClientExecutedTime) as ClientExecutedTime, JobStatus, DartExecutionProof
     FROM communication.Audit
     WHERE ". $filterStr1 ." JobType = ?
     AND Date(FROM_UNIXTIME(JobCreatedTime)) > Date(NOW() - INTERVAL 30 DAY) $whereSearch1 $orderStr1");

    $sql4->execute($params1);
    $totCount = $sql4->rowCount();

    if (safe_sizeof($result1) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, '');
       // $dataArr['html'] = Format_autoLogDataMysql($result,$db);
        $dataArr['html'] = Format_autoLogJobDataMysql($result1);
        
        echo json_encode($dataArr);
    }
}

function Format_autoLogJobDataMysql($data, $db =''){
    $recordList = [];
    $i = 0;
    foreach ($data as $key => $row) {
        $deviceName = $row['deviceName'];
        $createdTime = ($row['JobCreatedTime'] == null) ? '-' : $row['JobCreatedTime'];
        $SelectionType = $row['SelectionType'];
        $dart = $row['Dart'];
        $name = $row['AgentName'];
        $mail = $row['agentEmailID'];
        $idx = $row['IDX'];
        $JobType = $row['JobType'];
        $MachineOs = $row['MachineOs'];
        $ProfileName = $row['ProfileName'];
        $ProfileSequence = $row['ProfileSequence'];
        $ClientTimeZone = ($row['ClientTimeZone'] == null) ? 'NA' : $row['ClientTimeZone'];
        $ClientExecutedTime = ($row['ClientExecutedTime'] == null) ? 'NA' : $row['ClientExecutedTime'];
        $DartExecutionProof = ($row['DartExecutionProof'] == null) ? 'NA' : $row['DartExecutionProof'];
        $id = $row['AID'];
        
        if($row['JobStatus'] == 0){
            $JobStatus = 'Pending';
        }elseif($row['JobStatus'] == 2){
            $JobStatus = 'Completed';
        }elseif($row['JobStatus'] == 3){
            $JobStatus = 'Failed';
        }else{
            $JobStatus = 'Pending';
        }

        $ProfileName = implode(" ",explode("%20",$ProfileName));
        $ProfileName = implode(",",explode("%2C",$ProfileName));
        $ProfileName = implode("-",explode("%2D",$ProfileName));
       
        // $time = $logintime = date('m/d/Y H:i:s', strtotime($row['created']));
        // $userId = $_SESSION['user']['userid'];
        // $sql = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.Users where userid=?");
        // $sql->execute([$userId]);
        // $SqlRes = $sql->fetch();
        // $userTimeZone = !empty($SqlRes['timezone']) ? $SqlRes['timezone'] : 'UTC';

        // date_default_timezone_set('UTC');
        // $datetime = new DateTime(strval($row['created']));
        // $time = $datetime->format('m/d/Y H:i:s');
        
        // $la_time = new DateTimeZone($userTimeZone);
        // $datetime->setTimezone($la_time);
        // $logintime = $datetime->format('m/d/Y H:i:s');

        date_default_timezone_set('UTC');
        if($createdTime != '-'){
            $datetime = new DateTime(strval($createdTime));
            $createdTime = $datetime->format('m/d/Y H:i:s');
        }
        
        if($ClientExecutedTime != 'NA'){
            $datetime = new DateTime(strval($ClientExecutedTime));
            $ClientExecutedTime = $datetime->format('m/d/Y H:i:s');
        }

        $recordList[$i][] = $deviceName;
        $recordList[$i][] = $createdTime;
        $recordList[$i][] = $SelectionType;
        $recordList[$i][] = $dart;
        $recordList[$i][] = $name;
        $recordList[$i][] = $mail;
       // $recordList[$i][] = $idx;
        // $recordList[$i][] = $JobType;
        $recordList[$i][] = $MachineOs;
        $recordList[$i][] = $ProfileName;
        $recordList[$i][] = $ProfileSequence;
        $recordList[$i][] = $ClientTimeZone;
        $recordList[$i][] = $ClientExecutedTime;
        $recordList[$i][] = $JobStatus;
        // $recordList[$i][] = $DartExecutionProof;
        $recordList[$i][] = $id;
        $i++;
    }
    return $recordList;
}

function Format_autoLogDataMysql($data, $db){
    $recordList = [];
    $i = 0;
    foreach ($data as $key => $row) {
        $uName = $row['username'];
        $deviceName = $row['deviceName'];
        $mail = $row['useremail'];
        $module = $row['module'];
        $action = $row['action'];
        $ip = $row['ip'];
        $refName = $row['refName'];
        $publicip= $row['ip'];
        $browser= $row['agent'];
        $newbrowser =explode("-", $browser);
        $browser= $newbrowser[0];
        $time = $logintime = date('m/d/Y H:i:s', strtotime($row['created']));
        $userId = $_SESSION['user']['userid'];
        $sql = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."core.Users where userid=?");
        $sql->execute([$userId]);
        $SqlRes = $sql->fetch();
        $userTimeZone = !empty($SqlRes['timezone']) ? $SqlRes['timezone'] : 'UTC';

        date_default_timezone_set('UTC');
        $datetime = new DateTime(strval($row['created']));
        $time = $datetime->format('m/d/Y H:i:s');
        $la_time = new DateTimeZone($userTimeZone);
        $datetime->setTimezone($la_time);
        $logintime = $datetime->format('m/d/Y H:i:s');
                    $rname = '';
        if (isset($refName)) {
            $rname = $refName;
        } else {
            $rname = 'NA';
        }

        $stat = $row['status'];
        $id = $row['audit_id'];

        $recordList[$i][] = $action;
        $recordList[$i][] = $uName;
        $recordList[$i][] = $deviceName;
        $recordList[$i][] = $mail;
        $recordList[$i][] = $logintime;
        $recordList[$i][] = $time;
        $recordList[$i][] = $rname;
        $recordList[$i][] = $stat;
        $recordList[$i][] = $id;
        $i++;
    }
    return $recordList;
}
