<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
require_once '../include/common_functions.php';
include_once '../lib/l-db.php';
include_once '../lib/l-formatGrid.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-export.php';
include_once '../lib/l-setTimeZone.php';
include_once '../lib/l-msp.php';
include_once '../lib/l-util.php';

$fromDate = UTIL_GetString('from', '');
$toDate = UTIL_GetString('to', '');
$level = UTIL_GetString('type', '');
$sublist = UTIL_GetString('sublist', '');
$selectedType = UTIL_GetString('selectedType', '');

$cres = checkModulePrivilege('dartexport', 2);
if (!$cres) {
    echo 'Permission denied';
    exit();
}
ExportAuditLog($fromDate, $toDate, $level, $sublist, $selectedType);

function ExportAuditLog($fromDate, $toDate, $level, $sublistval, $selectedType)
{

    $headerArray = array("A" => "Device name", "B" => "Creation time", "C" => "Scope", "D" => "Dart", "E" => "Executed by", "F" => "Executed by - Email", "G" => "JobType", "H" => "Machine OS", "I" => "Solution Name", "J" => "Solution Sequence", "K" => "Client time zone", "L" => "Client Execution time", "M" => "Status");

    try {
        $objPHPExcel = GetExcelSheetObject($headerArray, 30);
        if ($level == 'User') {
            $fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
            $toDate = date('Y-m-d H:i:s', strtotime($toDate));
            $res = GetAuditDataUser($fromDate, $toDate, $sublistval, $selectedType);
        }
        if (safe_count($res) > 0) {
            $objPHPExcel = CreateAuditExcelSheet($objPHPExcel, $res);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . 2, 'No Data Available');
        }

        if ($selectedType == 'Notification') {
            $fn = "Notification_Log.xls";
        }elseif($selectedType == 'Interactive') {
            $fn = "Agent_Push_Log.xls";
        }elseif ($selectedType == 'Solution') {
            $fn = "Solution_API_Log.xls";
        }elseif ($selectedType == 'Distribution') {
            $fn = "Software_Distribution_Log.xls";
        }

        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fn . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function GetExcelSheetObject($headers, $width)
{
    try {
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        foreach ($headers as $key => $value) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($key)->setWidth($width);
            $objPHPExcel->getActiveSheet()->setCellValue($key . '1', $value);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}

function CreateAuditExcelSheet($objPHPExcel, $resultArray)
{
    try {
        $db = pdo_connect();
        $index = 2;
        foreach ($resultArray as $key => $value) {
          //  $time = $logintime = date('m/d/Y H:i:s', strtotime($value['created']));
            $userId = $_SESSION['user']['userid'];
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
            $sql->execute([$userId]);
            $SqlRes = $sql->fetch();
            // $userTimeZone = !empty($SqlRes['timezone']) ? $SqlRes['timezone'] : 'UTC';
            // date_default_timezone_set('UTC');
            // $datetime = new DateTime(strval($value['created']));
            // $time = $datetime->format('m/d/Y H:i:s');
            // $la_time = new DateTimeZone($userTimeZone);
            // $datetime->setTimezone($la_time);
            // $logintime =  $datetime->format('m/d/Y H:i:s');

            
            if($value['JobStatus'] == 0){
                $JobStatus = 'Pending';
            }elseif($value['JobStatus'] == 2){
                $JobStatus = 'Completed';
            }elseif($value['JobStatus'] == 3){
                $JobStatus = 'Failed';
            }else{
                $JobStatus = 'Pending';
            }

            $ClientTimeZone = ($value['ClientTimeZone'] == null) ? 'NA' : $value['ClientTimeZone'];
            $ClientExecutedTime = ($value['ClientExecutedTime'] == null) ? 'NA' : $value['ClientExecutedTime'];
            $JobCreatedTime = ($value['JobCreatedTime'] == null) ? 'NA' : $value['JobCreatedTime'];

            date_default_timezone_set('UTC');
            if($JobCreatedTime != 'NA'){
                $datetime = new DateTime(strval($JobCreatedTime));
                $JobCreatedTime = $datetime->format('m/d/Y H:i:s');
            }
            
            if($ClientExecutedTime != 'NA'){
                $datetime = new DateTime(strval($ClientExecutedTime));
                $ClientExecutedTime = $datetime->format('m/d/Y H:i:s');
            }
           
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['deviceName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $JobCreatedTime);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['SelectionType']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['Dart']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value['AgentName']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $value['agentEmailID']);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $value['JobType']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, $value['MachineOs']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $value['ProfileName']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $value['ProfileSequence']);
            $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $ClientTimeZone);
            $objPHPExcel->getActiveSheet()->setCellValue('L' . $index, $ClientExecutedTime);
            $objPHPExcel->getActiveSheet()->setCellValue('M' . $index, $JobStatus);

            $index++;
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function GetAuditDataUser($fromdate, $todate, $username, $selectedType)
{
    $db = pdo_connect();
    $fromdate = strtotime($fromdate);
    $todate = strtotime($todate);
    $usrArr = array();

    if ($selectedType == 'Notification'){
        $selectedType1 = 'Notification';
    }elseif ($selectedType == 'Interactive') {
        $selectedType1 = 'Interactive';
    }elseif ($selectedType == 'Solution') {
        $selectedType1 = 'Push Solution API';
    }elseif ($selectedType == 'Distribution') {
        $selectedType1 = 'Software Distribution';
    }else{
        $selectedType1 = '';
    }

    if ($username == 'All') {
        $uId = $_SESSION['user']['userid'];
        // $usrArr = getChildDetails($uId, 'username');
        // $loggeduser = $_SESSION['user']['logged_username'];
        // array_push($usrArr, $loggeduser);

        $userId = $_SESSION['user']['userid'];
        $roleId = $_SESSION['user']['role_id'];

        $sql = $db->prepare("SELECT user_email FROM ".$GLOBALS['PREFIX']."core.Users where userid = ?");
        $sql->execute([$uId]);
        $SqlRes = $sql->fetch();
        $userEmail = $SqlRes['user_email'];
        $usrArr = getChildDetails($userId, 'user_email');
        $usrArr = array_merge([$userEmail], $usrArr);

        $filterStr = '';
        $in1 = str_repeat('?,', safe_count($usrArr) - 1) . '?';
        
        if ((int) $roleId != 96) {
            $filterStr = " AgentUniqId in ($in1) and ";
        }

        if ($filterStr != '') {
            $params = array_merge([$selectedType1], $usrArr, [$fromdate, $todate]);
        } else {
            $params = array_merge([$selectedType1], [$fromdate, $todate]);
        }
    } else {
        $params = array_merge([$selectedType1], [$username], [$fromdate, $todate]);
    }

    if ($username == 'All') {
       // $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.AuditLog WHERE  module = ? and username in ($in1) and unix_timestamp(created) >= ? and unix_timestamp(created) <= ? order by created asc");

       $sql2 = $db->prepare("SELECT MachineTag as deviceName, FROM_UNIXTIME(JobCreatedTime) as JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId as agentEmailID, JobType, MachineOs, ProfileName, ProfileSequence, ClientTimeZone, FROM_UNIXTIME(ClientExecutedTime) as ClientExecutedTime, JobStatus FROM communication.Audit WHERE JobType = ? AND ".$filterStr." JobCreatedTime >= ? AND JobCreatedTime <= ? order by JobCreatedTime asc");
    } else {
        //$sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.AuditLog WHERE module = ? and username =?  and unix_timestamp(created) >= ? and unix_timestamp(created) <= ? order by created asc");

        $sql2 = $db->prepare("SELECT MachineTag as deviceName, FROM_UNIXTIME(JobCreatedTime) as JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId as agentEmailID, JobType, MachineOs, ProfileName, ProfileSequence, ClientTimeZone, FROM_UNIXTIME(ClientExecutedTime) as ClientExecutedTime, JobStatus FROM communication.Audit WHERE JobType = ? AND AgentName LIKE ? AND JobCreatedTime >= ? AND JobCreatedTime <= ? order by JobCreatedTime asc");
    }
    $sql2->execute($params);
    $result = $sql2->fetchAll();

    return $result;
}
