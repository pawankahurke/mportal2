<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
require_once '../lib/l-sql.php';
require_once '../lib/l-gsql.php';
require_once '../lib/l-rcmd.php';
require_once '../lib/l-util.php';
require_once '../include/common_functions.php';
include_once '../communication/l-comm.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

nhRole::dieIfnoRoles(['softwaredetails']); // roles: softwaredetails

//Replace $routes['post'] with if else
if (url::requestToStringAz09('function') === 'get_MessageConfig') { // roles: softwaredetails
    get_MessageConfig();
} else if (url::requestToStringAz09('function') === 'add_Message') { // roles: softwaredetails
    add_Message();
} else if (url::requestToStringAz09('function') === 'edit_Message') { // roles: softwaredetails
    edit_Message();
} else if (url::requestToStringAz09('function') === 'deleteMessage') { // roles: softwaredetails
    delete_Message();
} else if (url::requestToStringAz09('function') === 'get_MessageDetails') { // roles: softwaredetails
    get_MessageDetails();
}

//Replace $routes['get'] with if else
if (url::requestToStringAz09('function') === 'get_MessageAudit') { // roles: softwaredetails
    get_MessageAudit();
} else if (url::requestToStringAz09('function') === 'getMessageAuditDetail') { // roles: softwaredetails
    get_MessageAuditDetail();
} else if (url::requestToStringAz09('function') === 'getMessageAuditDetailTime') { // roles: softwaredetails
    get_MessageAuditDetailTime();
}



function get_MessageConfig()
{
    $db = pdo_connect();
    $user = $_SESSION['user']['adminEmail'];
    $recordlist = array();
    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.MessageDetails ");
    $sql->execute();
    $sqlRes = $sql->fetchAll();
    $i = 1;
    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $value) {
            $CreationTime = $value['createtime'];
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {

                $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $CreationTime, "m/d/Y h:i A");

                $time = '<p class="ellipsis" title="' . $userLoggedTime . '">' . $userLoggedTime . '</p>';
            } else {
                $time = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $CreationTime) . '">' . date("m/d/Y h:i A", $CreationTime) . '</p>';
            }
            $msgName = '<p class="ellipsis" id="' . $value['name'] . '" title="' . $value['name'] . '">' . $value['name'] . '</p>';
            $message = '<p class="ellipsis" id="' . $value['message'] . '" title="' . $value['message'] . '">' . $value['message'] . '</p>';
            $url = '<p class="ellipsis" title="' . $value['url'] . '">' . $value['url'] . '</p>';
            $recordlist[] = array($i, $msgName, $message, $url, $time, "id" => $value['id'], "name" => $value['name']);
            $i++;
        }
    } else {
        $recordlist = array();
    }
    echo json_encode($recordlist);
}

function add_Message()
{
    $user = $_SESSION['user']['adminEmail'];
    $title = url::requestToText('title');
    $message = url::requestToText('msg');
    $url = url::requestToText('url');
    $frequency = url::requestToText('frequency');
    $time = url::requestToText('time');
    $liveTime = url::requestToText('livetime');
    $button1 = url::requestToText('button1');
    $button2 = url::requestToText('button2');
    $createtime = time();

    $db = pdo_connect();
    $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.MessageDetails (name,message,url,button1,button2,time,frequency,livetime,username,createtime) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $sql->execute([$title, $message, $url, $button1, $button2, $time, $frequency, $liveTime, $user, $createtime]);
    create_auditLog('Message Configuration', 'INSERT', 'Success', [$title, $message, $url, $button1, $button2, $time, $frequency, $liveTime, $user, $createtime], 'Message Configuration()');
    $sqlRes = $db->lastInsertId();
    if ($sqlRes) {
        echo "DONE";
    } else {
        echo "FAIL";
    }
}

function edit_Message()
{

    $title = url::requestToText('title');
    $message = url::requestToText('msg');
    $url = url::requestToText('url');
    $id = url::requestToText('msgId');
    $frequency = url::requestToText('frequency');
    $time = url::requestToText('time');
    $liveTime = url::requestToText('livetime');
    $button1 = url::requestToText('button1');
    $button2 = url::requestToText('button2');
    $user = $_SESSION['user']['adminEmail'];

    $params = array_merge([$title, $message, $url, $button1, $button2, $time, $frequency, $liveTime, $user, $id]);
    $db = pdo_connect();
    $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "softinst.MessageDetails SET name = ?, message = ?,"
        . " url= ?,  button1= ?,  button2= ?,  "
        . "time= ?, frequency= ?,livetime =?, username=? WHERE id = ?");
    $sql->execute($params);
    create_auditLog('Message Configuration', 'UPDATE', 'Success', $params, 'edit_Message()');
    $sqlRes = $sql->rowCount();
    if ($sqlRes) {
        echo "DONE";
    } else {
        echo "FAIL";
    }
}

function get_MessageDetails()
{

    $db = pdo_connect();
    $id = url::requestToText('msgId');
    $sql = $db->prepare("SELECT message,url,name,button1,button2,time,frequency,livetime FROM " . $GLOBALS['PREFIX'] . "softinst.MessageDetails WHERE id = ?");
    $sql->execute([$id]);
    $sqlRes = $sql->fetch();

    if (safe_count($sqlRes) > 0) {
        $res = array(
            "message" => $sqlRes['message'], "url" => $sqlRes['url'], "title" => $sqlRes['name'],
            "button1" => $sqlRes['button1'], "button2" => $sqlRes['button2'], "time" => $sqlRes['time'],
            "frequency" => $sqlRes['frequency'], "livetime" => $sqlRes['livetime']
        );
        echo json_encode($res);
    }
}

function delete_Message()
{

    $db = pdo_connect();
    $id = url::requestToText('msgId');

    $sql = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.MessageDetails WHERE id = ?");
    $sql->execute([$id]);
    create_auditLog('Message Configuration', 'DELETE', 'Success', $id, 'delete_Message()');
    $sqlRes = $db->lastInsertId();

    $sql = $db->prepare("Select * FROM " . $GLOBALS['PREFIX'] . "softinst.MessageDetails WHERE id = ?");
    $sql->execute([$id]);
    $Res = $sql->fetch();

    if ($Res) {
        echo "FAIL";
    } else {
        echo "DONE";
    }
}

function get_MessageAudit()
{

    $db = pdo_connect();
    $recordlist = array();

    $agentUniqId = $_SESSION['user']['adminEmail'];

    $username = $_SESSION['user']['logged_username'];
    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $userId = $_SESSION["user"]["userid"];

    $auditSearch = $searchValue;
    $siteName = UTIL_GetUserSiteList($db, $userId);
    $custList = $siteName['custNo'];
    $ordList = $siteName['ordNo'];
    $dataScope = GetSiteScope($db, $searchValue, $searchtype);
    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $sql = $db->prepare("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ? and JobType = 'Message' group by JobCreatedTime order by JobCreatedTime desc");
        $sql->execute(['Machine : ' . $auditSearch]);
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $sql = $db->prepare("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ? and JobType = 'Message' group by JobCreatedTime order by JobCreatedTime desc");
        $sql->execute(['Site : ' . $auditSearch]);
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $sql = $db->prepare("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ?  group by JobCreatedTime order by JobCreatedTime desc");
        $sql->execute(['Group : ' . $auditSearch]);
    }
    $res = $sql->fetchAll();
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $profileName = explode(':', $res[$key]['ProfileName'])[1];
            $profilename = '<p class="ellipsis" title="' . utf8_encode($profileName) . '">' . utf8_encode($profileName) . '</p>';
            $jobcreatedtime = $res[$key]['JobCreatedTime'];
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {

                $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $jobcreatedtime, "m/d/Y h:i A");

                $time = '<p class="ellipsis" title="' . $userLoggedTime . '">' . $userLoggedTime . '</p>';
            } else {
                $time = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $jobcreatedtime) . '">' . date("m/d/Y h:i A", $jobcreatedtime) . '</p>';
            }

            $agentname = $res[$key]['AgentName'];
            $profname = utf8_encode($res[$key]['ProfileName']);
            $id = $res[$key]['BID'];

            $recordlist[] = array($profilename, $time, $agentname, $profname, $id);
        }
        echo json_encode($recordlist);
    } else {
        echo json_encode($recordlist);
    }
}

function get_MessageAuditDetail()
{
    $db = pdo_connect();

    $searchtype = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    // $agentUniqId = $_SESSION['user']['adminEmail'];
    // $detailType = url::requestToText('type');

    // $where = '';
    $recordList = array();
    if (url::issetInRequest('start') && url::requestToText('length')) {
        $start = url::requestToText('start');
        $length = url::requestToText('length');
        $limit = " limit $start , $length";
    }
    // $searchVal = strip_tags(url::requestToAny('search')['value']);
    $draw = url::requestToText('draw');
    $auditSearch = $searchValue;

    // if ($orderval != '') {
    //     $orderColoumn = strip_tags(url::requestToAny('columns')[$orderval]['data']);
    //     $ordertype = strip_tags(url::requestToAny('order')[0]['dir']);
    //     $orderValues = " order by $orderColoumn $ordertype";
    // }

    $bid = url::requestToText('bid');
    $packageName = url::requestToText('PackageName');
    $pack = url::requestToText('pack');
    $_SESSION['bid'] = $bid;
    $_SESSION['pack'] = $pack;
    $_SESSION['packageName'] = $packageName;

    if (url::requestToText('searchVal') != '') {
        $name = url::getToText('searchVal');
        $append_search = "and MachineTag  LIKE '%" . strtolower($name) . "%'";
    }

    // $currentTimestamp = time();
    // $yestDTStamp = strtotime("-90 days", $currentTimestamp);
    // $where = '';

    $dataScope = GetSiteScope($db, $searchValue, $searchtype);
  $auditSql = $db->prepare("select AID,BID,SelectionType,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof,AgentUniqId from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ? and JobType= 'Message' $append_search");

  if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $auditSql->execute(['Machine : ' . $auditSearch]);
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $auditSql->execute(['Site : ' . $auditSearch]);
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $auditSql->execute(['Group : ' . $auditSearch]);
    }
    $auditRes = $auditSql->fetchAll(PDO::FETCH_ASSOC);
    create_auditLog('Message Configurations', 'SELECT', 'Success', $auditRes, 'get_MessageAuditDetail()');
    $allMachines = array();
    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where site = ?");
    $sql->execute([$searchValue]);
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    $completearr = array();
    foreach ($auditRes as $key => $val) {
        array_push($completearr, $val);
    }
    $allMachines = array();
    $audiMachines = array();
    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where site = ?");
    $sql->execute([$searchValue]);
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    create_auditLog('Message Configurations', 'SELECT', 'Success', $res, 'get_MessageAuditDetail()');

  foreach ($res as $key => $val) {
        $host = $val['host'];
        array_push($allMachines, $host);
    }
    foreach ($auditRes as $key => $value) {
        $host = $value['MachineTag'];
        array_push($audiMachines, $host);
    }
    $OtherMach = array_diff($allMachines, $audiMachines);

    foreach ($OtherMach as $k => $v) {
        $newvalue['JobStatus'] = 40;
        $newvalue['AID'] = '';
        $newvalue['BID'] = '';
        $newvalue['SelectionType'] = 'Site :' . $searchValue;
        $newvalue['MachineTag'] = $v;
        $newvalue['JobCreatedTime'] = '';
        $newvalue['AgentName'] = '';
        $newvalue['JobType'] = '';
        $newvalue['MachineOs'] = '';
        $newvalue['ProfileName'] = $pack;
        $newvalue['ClientTimeZone'] = '';
        $newvalue['ClientExecutedTime'] = '';
        $newvalue['DartExecutionProof'] = '';
        $newvalue['AgentUniqId'] = '';
        array_push($completearr, $newvalue);
    }
    $auditRes = $completearr;
    $totalRecords = safe_count($auditRes);
    $i = 0;
    if (url::issetInRequest('export')) {
        FormatAuditExport($auditRes, 'exportAuditDetail');
    } else {
        foreach ($auditRes as $key => $row) {

            $auditId = $row['AID'];
            $eventListN = $row['DartExecutionProof'];
            $status = $row['JobStatus'];
            if ($status == 0) {
                $status = 'Pending';
            } else if ($status == 1) {
                $status = 'Received';
            } else if ($status == 2) {
                $status = 'Snooze';
            } else if ($status == 3) {
                $status = 'Open';
            } else if ($status == 18) {
                $status = 'Expired';
            } else if ($status == 20) {
                $status = 'Cleared';
            } else if ($status == 40) {
                $status = 'Offline';
            } else {
                $status = 'Frequency over';
            }
            $proof = '<p class="ellipsis" title="' . $status . '">' . $status . '</p>';
            if (strpos($row['SelectionType'], 'Site ') !== false) {
                $siteName = explode('__', $row['SelectionType']);
                $selectT = $siteName[0];
            } else {
                $selectT = $row['SelectionType'];
            }
            $recordList[] = array('SelectionType' => '<p class="ellipsis" title="' . $selectT . '">' . $selectT . '</p>', 'MachineTag' => '<p class="ellipsis" title="' . $row['MachineTag'] . '">' . $row['MachineTag'] . '</p>', 'Status' => $proof);
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
        echo json_encode($jsonData);
    }
}

function get_MessageAuditDetailTime()
{
    $timezone = $_SESSION['timezone'];
    $db = pdo_connect();
    $recordlist = array();
    $detailType = url::requestToStringAz09('type');
    $to = url::getToText('to');
    $from = url::getToText('from');
    $reset = date_default_timezone_get();
    date_default_timezone_set($timezone);
    $to = strtotime($to);
    $from = strtotime($from);
    date_default_timezone_set($reset);
    $agentUniqId = $_SESSION['user']['adminEmail'];

    $username = $_SESSION['user']['logged_username'];
    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $userId = $_SESSION["user"]["userid"];

    $auditSearch = $searchValue;
    $siteName = UTIL_GetUserSiteList($db, $userId);
    $custList = $siteName['custNo'];
    $ordList = $siteName['ordNo'];
    $dataScope = GetSiteScope($db, $searchValue, $searchtype);
    $range = 'and JobCreatedTime >= ? and JobCreatedTime <= ?';
  if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
    $sql = $db->prepare("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ? and JobType = 'Message' and JobCreatedTime >= ? and JobCreatedTime <= ? group by JobCreatedTime order by JobCreatedTime desc");
    $sql->execute(['Machine : ' . $auditSearch, $from, $to]);
  } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
    $sql = $db->prepare("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ? and JobType = 'Message' and JobCreatedTime >= ? and JobCreatedTime <= ? group by JobCreatedTime order by JobCreatedTime desc");
    $sql->execute(['Site : ' . $auditSearch, $from, $to]);
  } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
    $sql = $db->prepare("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = ?  and JobCreatedTime >= ? and JobCreatedTime <= ? group by JobCreatedTime order by JobCreatedTime desc");
    $sql->execute(['Group : ' . $auditSearch, $from, $to]);
    }
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    if (!$res) $res = [];
    create_auditLog('Message Configurations', 'SELECT', 'Success', $res, 'get_MessageAuditDetailTime()');
    FormatDetailExport($res);
}

function FormatDetailExport($res)
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ExportMessageAudit.csv"');


    $finalArr = array();
    $ExportArr = array();
    $titleArray = array();
    $columnArray = safe_array_keys($res[safe_array_keys($res)[0]]);
    foreach ($columnArray as $key => $eachColumns) {
        if ($eachColumns != '') {
            $titleArray[0] = 'Message Name';
            $titleArray[1] = 'Triggerred Time';
            $titleArray[2] = 'Agent';
        }
    }
    $finalArr[0] = $titleArray;
    if (safe_count($res) > 0) {
        foreach ($res as $key => $val) {

            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $Triggertime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['JobCreatedTime']);
            } else {
                $Triggertime = $val['JobCreatedTime'] == '' ? '-' : date("m/d/Y h:i A", $val['JobCreatedTime']);
            }

            if ($val['JobCreatedTime'] == '') {
                $Triggertime =  '-';
            }

            $ExportArr['Title'] = explode(':', $val['ProfileName'])[1];
            $ExportArr['Triggertime'] = $Triggertime;
            $ExportArr['AgentName'] = $val['AgentName'];
            array_push($finalArr, $ExportArr);
        }
    }
    ob_clean();
    $fp = fopen('php://output', 'wb');
    foreach ($finalArr as $line) {
        fputcsv($fp, $line, ',');
    }
    fclose($fp);
}

function FormatAuditExport($result, $type)
{
    if ($type == 'exportAuditDetail') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ExportAuditDetails.csv"');
    } else {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ExportAudit.csv"');
    }

    $finalArr = array();
    $ExportArr = array();
    $titleArray = array();
    $columnArray = safe_array_keys($result[safe_array_keys($result)[0]]);
    foreach ($columnArray as $key => $eachColumns) {
        if ($eachColumns != '') {
            $titleArray[0] = 'Job Id';
            $titleArray[1] = 'Title';
            $titleArray[2] = 'Level';
            $titleArray[3] = 'Machine Name';
            $titleArray[4] = 'Status';
            $titleArray[5] = 'Triggered Time';
            $titleArray[6] = 'Received Time';
            $titleArray[7] = 'Triggered By';
        }
    }
    $finalArr[0] = $titleArray;
    if (safe_count($result) > 0) {
        foreach ($result as $key => $val) {
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $Triggertime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['JobCreatedTime'], "Y/m/d");
            } else {
                $Triggertime = $val['JobCreatedTime'] == '' ? '-' : date("m/d/Y h:i A", $val['JobCreatedTime']);
            }

            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $Receivetime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['ClientExecutedTime'], "Y/m/d");
            } else {
                $Receivetime = date("m/d/Y h:i A", $val['ClientExecutedTime']);
            }

            if ($val['JobCreatedTime'] == '') {
                $Triggertime =  '-';
            }

            if ($val['ClientExecutedTime'] == '') {
                $Receivetime =  '-';
            }


            if ($val['JobStatus'] == 0) {
                $status = 'Pending';
            } else if ($val['JobStatus'] == 1) {
                $status = 'Received';
            } else if ($val['JobStatus'] == 2) {
                $status = 'Snooze';
            } else if ($val['JobStatus'] == 3) {
                $status = 'Open';
            } else if ($val['JobStatus'] == 18) {
                $status = 'Expired';
            } else if ($val['JobStatus'] == 20) {
                $status = 'Cleared';
            } else if ($val['JobStatus'] == 40) {
                $status = 'Offline';
            } else {
                $status = 'Frequency over';
            }
            $ExportArr['AID'] = $val['AID'];
            $ExportArr['Title'] = explode(':', $val['ProfileName'])[1];
            $ExportArr['level'] = explode(':', $val['SelectionType'])[0];
            $ExportArr['MachineTag'] = $val['MachineTag'];
            $ExportArr['status'] = $status;
            $ExportArr['Triggertime'] = $Triggertime;
            $ExportArr['Receivetime'] = $Receivetime;
            $ExportArr['AgentUniqId'] = $val['AgentUniqId'];
            array_push($finalArr, $ExportArr);
        }
    }
    ob_clean();
    $fp = fopen('php://output', 'wb');
    foreach ($finalArr as $line) {
        fputcsv($fp, $line, ',');
    }
    fclose($fp);
}
