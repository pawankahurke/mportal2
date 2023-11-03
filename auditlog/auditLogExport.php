<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
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

$cres = checkModulePrivilege('dartexport', 2);
if (!$cres) {
    echo 'Permission denied';
    exit();
}
ExportAuditLog($fromDate, $toDate, $level, $sublist);

function ExportAuditLog($fromDate, $toDate, $level, $sublistval)
{

    $headerArray = array("A" => "Module", "B" => "Action", "C" => "User", "D" => "Email", "E" => "Status", "F" => "Local Time", "G" => "GMT Time");

    try {

        $objPHPExcel = GetExcelSheetObject($headerArray, 30);
        if ($level == 'User') {
            $fromDate = date('Y-m-d H:i:s', strtotime($fromDate));
            $toDate = date('Y-m-d H:i:s', strtotime($toDate));
            $res = GetAuditDataUser($fromDate, $toDate, $sublistval);
        }
        if (safe_count($res) > 0) {
            $objPHPExcel = CreateAuditExcelSheet($objPHPExcel, $res);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . 2, 'No Data Available');
        }

        $fn = "AuditLog.xls";
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
            $time = $logintime = date('m/d/Y H:i:s', strtotime($value['created']));
            $userId = $_SESSION['user']['userid'];
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
            $sql->execute([$userId]);
            $SqlRes = $sql->fetch();
            $userTimeZone = !empty($SqlRes['timezone']) ? $SqlRes['timezone'] : 'UTC';
            date_default_timezone_set('UTC');
            $datetime = new DateTime(strval($value['created']));
            $time = $datetime->format('m/d/Y H:i:s');
            $la_time = new DateTimeZone($userTimeZone);
            $datetime->setTimezone($la_time);
            $logintime =  $datetime->format('m/d/Y H:i:s');
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['module']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['action']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['useremail']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value['status']);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $logintime);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $time);

            $index++;
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function GetAuditDataUser($fromdate, $todate, $username)
{
    $db = pdo_connect();
    $fromdate = strtotime($fromdate);
    $todate = strtotime($todate);
    $usrArr = array();

    if ($username == 'All') {
        $uId = $_SESSION['user']['userid'];
        $usrArr = getChildDetails($uId, 'username');
        $loggeduser = $_SESSION['user']['logged_username'];
        array_push($usrArr, $loggeduser);
        $params = array_merge($usrArr, [$fromdate, $todate]);
    } else {
        $params = array_merge([$username], [$fromdate, $todate]);
    }

    if ($username == 'All') {
        $in1 = str_repeat('?,', safe_count($usrArr) - 1) . '?';
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.AuditLog WHERE  username in ($in1) and unix_timestamp(created) >= ? and unix_timestamp(created) <= ? order by created asc");
    } else {

        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.AuditLog WHERE username =?  and unix_timestamp(created) >= ? and unix_timestamp(created) <= ? order by created asc");
    }
    $sql2->execute($params);
    $result = $sql2->fetchAll();


    return $result;
}
