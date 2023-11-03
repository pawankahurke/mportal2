<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
require_once '../include/common_functions.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-setTimeZone.php';

$site = url::postToAny('alertexportsite');
$notifyType = url::issetInPost('notifyType') ? url::postToAny('notifyType') : 'list';
$db = pdo_connect();
$username = $_SESSION['user']['username'];
if ($notifyType == 'list') {
  $headerArray = array("A" => "Name", "B" => "Site", "C" => "Type", "D" => "Created");

  try {
    $objPHPExcel = getExcelActiveSheet($headerArray, 30);
    if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $userTimeZone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date_default_timezone_get();
            $myTimeZone = $userTimeZone;
            $toTimeZone = date_default_timezone_get();
            date_default_timezone_set($myTimeZone);
        }

        $res = GetNotificationbySite($db, $site, $username, $notifyType);
        if (safe_count($res) > 0) {
            $objPHPExcel = getExcelSheet($objPHPExcel, $res);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . 2, 'No Data Available');
        }

//        $fn = "Notification.xls";
        $fn = "Notification.csv";
        $objPHPExcel->setActiveSheetIndex(0);
//        header('Content-Type: application/vnd.ms-excel');
        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="' . $fn . '"');
        header('Cache-Control: max-age=0');
//        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        ob_end_clean();
        $objWriter->save('php://output');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
} else {

    $headerArray = array("A" => "Name", "B" => "Site", "C" => "Type", "D" => "Priority", "E" => "Seconds", "F" => "Criteria", "G" => "Search Text", "H" => "Where Text", "I" => "Show Text", "J" => "Scrip", "K" => "Created", "L" => "Status");

    try {
        $objPHPExcel = getExcelActiveSheet($headerArray, 25);
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $userTimeZone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date_default_timezone_get();
            $myTimeZone = $userTimeZone;
            $toTimeZone = date_default_timezone_get();
            date_default_timezone_set($myTimeZone);
        }

        $res = GetNotificationbySite($db, $site, $username, $notifyType);

        if (safe_count($res) > 0) {
            $objPHPExcel = getExcelConfigSheet($objPHPExcel, $res);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . 2, 'No Data Available');
        }

        $fn = "Notification.csv";
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-type: text/csv');
        header('Content-Disposition: attachment;filename="Notification.csv"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
        ob_end_clean();
        $objWriter->save('php://output');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function getExcelActiveSheet($headers, $width)
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

function getExcelSheet($objPHPExcel, $resultArray)
{
    try {
        $index = 2;
        foreach ($resultArray as $key => $value) {
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['created'], "m/d/Y h:i A");
            } else {
                $userLoggedTime = date("m/d/Y h:i A", $value['created']);
            }
            $alertType = getAlertType($value['ntype']);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['name']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['group_include']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $alertType);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $userLoggedTime);
            $index++;
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getExcelConfigSheet($objPHPExcel, $resultArray)
{
    try {
        $index = 2;
        foreach ($resultArray as $key => $value) {
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['created'], "m/d/Y h:i A");
            } else {
                $userLoggedTime = date("m/d/Y h:i A", $value['created']);
            }
            $criteriaStr = '';
            $criteria = array();
            if (!empty($value['compCriteria'])) {
                $compCriteria = safe_json_decode($value['compCriteria'], true);
            } else {
                $compCriteria = array();
            }

            if (!empty($value['criteria'])) {
                $criteria = safe_json_decode($value['criteria'], true);
            } else {
                $criteria = array();
            }

            if (is_array($criteria) && is_array($compCriteria)) {
              $resultCriteria = json_encode(array_merge($criteria, $compCriteria));
              $status = '';
              if ($value['enabled'] == '1') {
                $status = 'Enabled';
              } else {
                $status = 'Disabled';
              }
              $priority = getPriority($value['priority']);
              $alertType = getAlertType($value['ntype']);
              $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['name']);
              $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['group_include']);
              $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $alertType);
              $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $priority);
              $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value['seconds']);
              $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $resultCriteria);
              $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $value['search_txt']);
              $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, $value['where_txt']);
              $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $value['show_txt']);
              $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $value['scrip']);
              $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $userLoggedTime);
              $objPHPExcel->getActiveSheet()->setCellValue('L' . $index, $status);
              $index++;
            }
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getAlertType($alerttype)
{
    $alerttxt = '';
    if ($alerttype == 1) {
        $alerttxt = 'Availability';
    } else if ($alerttype == 2) {
        $alerttxt = 'Security';
    } else if ($alerttype == 3) {
        $alerttxt = 'Resource';
    } else if ($alerttype == 4) {
        $alerttxt = 'Maintenance';
    } else {
        $alerttxt = 'Events of Interest';
    }

    return $alerttxt;
}

function getPriority($priority)
{
    $prioritytxt = '';
    if ($priority == 1) {
        return 'P1';
    }
    if ($priority == 2) {
        return 'P2';
    }
    if ($priority == 3) {
        return 'P3';
    }

    return $prioritytxt;
}

function GetNotificationbySite($db, $siteAccessList, $username, $notifyType)
{
    if ($notifyType == 'list') {

        if ($siteAccessList == 'All') {
            $sql = $db->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications where enabled = ?");
            $sql->execute([1]);
        } else {
            $sql = $db->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications where (group_include like ? or group_include = ?) AND enabled = ?");
            $sql->execute(["%$siteAccessList%", 'All', 1]);
        }
    } else {

        if ($siteAccessList == 'All') {
            $sql = $db->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications");
            $sql->execute();
        } else {
            $sql = $db->prepare("SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Notifications where (group_include like ? or group_include = ?)");
            $sql->execute(["%$siteAccessList%", 'All']);
        }
    }



    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}
