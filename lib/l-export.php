<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-setTimeZone.php';

function Export_DeployData($data)
{

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Host');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Mac Address');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'IP Address');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Client Available');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Client Version');

    $index = 2;
    if ($data && !empty($data[0]['macaddress'])) {
        foreach ($data as $key => $val) {
            if ($val['macaddress'] != '') {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $val['host'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $val['macaddress'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $val['ipaddress'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $val['isclientavl'] . '');
                if ($val['clientversion'] == 0 || $val['clientversion'] == '0') {
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '');
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $val['clientversion'] . '');
                }
                $index++;
            }
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available in table');
    }

    $fn = "DeploymentDetails.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function EXPORT_ServicesGridData($data, $ProfileName, $Username, $searchtype)
{

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Action ID');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Triggered Time');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Scope');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Status');

    $index = 2;
    foreach ($data as $key => $val) {

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $val['AID'] . '');
        $jobCreatedTime = $val['JobCreatedTime'];
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $jobCreatedTime, $index, 'B', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $selectionType = UTIL_GetTrimmedGroupName($val['SelectionType']);
        if ($searchtype == "ServiceTag") {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $selectionType . '');
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $selectionType . ' : ' . $val['MachineTag']);
        }

        $state = $val['JobStatus'];
        switch ($state) {
            case "2":
                $proof = 'Completed';
                break;
            case "3":
                $proof = 'Failed';
                break;
            case "0":
                $proof = 'Pending';
                break;
            default:
                $proof = 'Pending';
                break;
        }
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $proof . '');

        $index++;
    }
    $Username = UTIL_GetTrimmedSiteName($Username);
    $customfilename = $Username . '_Services_' . str_replace(' ', '', $ProfileName);
    $fn = "$customfilename.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function EXPORT_SummaryExportList($summaryExportData)
{

    $index = 2;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Day');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Type');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Device');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Issue');
    if ($summaryExportData) {

        foreach ($summaryExportData as $key => $summarylist) {
            $tempDay = strtotime($summarylist[0]);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, PHPExcel_Shared_Date::PHPToExcel($tempDay));
            $objPHPExcel->getActiveSheet()->getStyle('A' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");

            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $summarylist[1]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $summarylist[2]);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $summarylist[3]);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    $fn = "SummaryReport.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function EXPORT_GetCapacityReportDataExport()
{
    $index = 2;
    global $db;
    $db = db_connect();
    $key = '';
    $user = $_SESSION['user']['username'];
    $rparentname = $_SESSION['rparentName'];
    $passLevel = $_SESSION['passlevel'];
    $sites = DASH_GetSites($key, $db, $user);
    foreach ($sites as $value) {
        $siteVal .= "'" . $value . "',";
    }
    $siteVal = rtrim($siteVal, ',');

    if ($passLevel == 'Sites') {
        $siteVal = "'" . $rparentname . "'";
    } else {
        $siteVal = $siteVal;
    }

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $capResult = DASH_GetAllCapacityData($searchType, $dataScope, $siteVal);

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Serial Number');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Devices');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'CPU Used');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'RAM Used');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Disk Space Used');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Battery Status');
    $objPHPExcel = EXPORT_CreateCapacityExportSheet($capResult, $objPHPExcel);
    return $objPHPExcel;
}

function EXPORT_CreateCapacityExportSheet($capacityResultArr, $objPHPExcel)
{
    $index = 2;
    if (safe_count($capacityResultArr) > 0) {
        foreach ($capacityResultArr as $key => $val) {
            if ($val['cpuUsage'] == "") {
                $cpuUsage = '-';
            } else {
                $cpuUsage = $val['cpuUsage'] . '%';
            }

            if ($val['ramUsage'] == "") {
                $ramUsage = '-';
            } else {
                $ramUsed = 100 - (int) $val['ramUsage'];
                $ramUsage = $ramUsed . '%';
            }

            if ($val['hardDiskUsage'] == "") {
                $hardDiskUsage = '-';
            } else {
                $hardDiskUsed = 100 - (int) $val['hardDiskUsage'];
                $hardDiskUsage = $hardDiskUsed . '%';
            }
            if ($val['bateryState'] == "NA") {
                $bateryState = 'No Battery';
            } else {
                $bateryState = ($val['bateryState'] != "") ? $val['bateryState'] : "-";
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $val['host']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, ($val['machine'] != "") ? $val['machine'] : "-");
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $cpuUsage);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $ramUsage);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $hardDiskUsage);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $bateryState);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, "No records found");
    }
    return $objPHPExcel;
}

function EXPORT_CompDetailData($complianceData, $draw, $showItem, $showStatus)
{

    $item = explode(",", $showItem);
    $status = explode(",", $showStatus);

    $ItemType = null;
    $statusType = null;

    foreach ($item as $key => $val) {
        if ($val == 1) {
            $ItemType .= "All,";
            break;
        } else if ($val == 5) {
            $ItemType .= "Availability,";
        } else if ($val == 10) {
            $ItemType .= "Maintenance,";
        } else if ($val == 9) {
            $ItemType .= "Events,";
        } else if ($val == 7) {
            $ItemType .= "Security,";
        } else if ($val == 8) {
            $ItemType .= "Resources,";
            break;
        }
    }

    $ItemType = rtrim($ItemType, ",");


    foreach ($status as $key => $val) {
        if ($val == 1) {
            $statusType .= "Ok,";
        } else if ($val == 2) {
            $statusType .= "Warning,";
        } else if ($val == 3) {
            $statusType .= "Alert,";
            break;
        }
    }
    $statusType = rtrim($statusType, ",");

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

    $status = getStatusText($showItem, $showStatus);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Showing compliance items with status ' . $ItemType . ' and ' . $statusType . ' for the past 15 days');

    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Machine');
    $objPHPExcel->getActiveSheet()->setCellValue('B2', 'Last Event');
    $objPHPExcel->getActiveSheet()->setCellValue('C2', 'Event Count');

    $i = 3;
    foreach ($complianceData as $key => $val) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val['host']);
        $clienttime = $val['clienttime'];
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, PHPExcel_Shared_Date::PHPToExcel($clienttime));
        $objPHPExcel->getActiveSheet()->getStyle('B' . $i)->getNumberFormat()->setFormatCode("mm/dd/yyyy hh:mm AM/PM");
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val['eventcount']);
        $i++;
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = "Compliance.xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function getStatusText($showItem, $showStatus)
{

    $item = '';
    $status = '';

    if (strpos($showItem, '5') !== false) {
        $item .= 'Availability,';
    }
    if (strpos($showItem, '7') !== false) {
        $item .= 'Security,';
    }
    if (strpos($showItem, '8') !== false) {
        $item .= 'Resources,';
    }
    if (strpos($showItem, '9') !== false) {
        $item .= 'Events,';
    }
    if (strpos($showItem, '10') !== false) {
        $item .= 'Maintenance,';
    }
    if (strpos($showStatus, '1') !== false) {
        $status .= 'Ok,';
    }
    if (strpos($showStatus, '2') !== false) {
        $status .= 'Warning,';
    }
    if (strpos($showStatus, '3') !== false) {
        $status .= 'Alert,';
    }

    $data['status'] = rtrim($status, ',');
    $data['item'] = rtrim($item, ',');

    return $data;
}

function EXPORT_NHConfigGridData($NHGridData, $NHProfileDetails, $ProfileName)
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Agent Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Scope');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Triggered Time');

    $i = 2;
    if (safe_count($NHGridData) > 0) {
        foreach ($NHGridData as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val[0]);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $val[1]);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val[2]);

            $jobCreatedTime = strtotime($val[3]);
            $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $jobCreatedTime, $i, 'D', 'mm/dd/yyyy hh:mm:ss AM/PM');

            $i++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'No data available');
    }
    $objPHPExcel->getActiveSheet()->setTitle($ProfileName);
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = $ProfileName . ".xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function EXPORT_censusReport($result, $machineData, $displayColumn)
{

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);

    $columns = explode(",", $displayColumn);
    $index = 65;
    foreach ($columns as $val) {
        $objPHPExcel->getActiveSheet()->getColumnDimension(chr($index))->setWidth(25);
        $objPHPExcel->getActiveSheet()->setCellValue(chr($index) . '1', $val);
        $index++;
    }

    $i = 2;
    foreach ($result as $value) {
        $j = 65;
        foreach ($columns as $col) {
            if ($col == 'Site Name') {
                if (strpos($value['site'], "__") !== false) {
                    $tempArray = UTIL_GetTrimmedGroupName($value['site']);
                    $companyName = $tempArray;
                } else {
                    $companyName = $value['site'];
                }
                $objPHPExcel->getActiveSheet()->setCellValue(chr($j) . $i, $companyName);
            } else if ($col == 'Machine Name') {
                $objPHPExcel->getActiveSheet()->setCellValue(chr($j) . $i, $value['host']);
            } else if ($col == 'Born Date') {
                $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $value['born'], $i, chr($j), 'mm/dd/yyyy hh:mm:ss AM/PM');
            } else if ($col == 'Last Reported') {
                $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $value['last'], $i, chr($j), 'mm/dd/yyyy hh:mm:ss AM/PM');
            } else if ($col == 'Last Asset Synced') {
                $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $value['lastsync'], $i, chr($j), 'mm/dd/yyyy hh:mm:ss AM/PM');
            } else if ($col == 'IP address') {
                $objPHPExcel->getActiveSheet()->setCellValue(chr($j) . $i, $machineData[$value['machineid']][$col][0]);
            } else if ($col == 'IMEI NO') {
                if ($machineData[$value['machineid']][$col][1] != '') {
                    $objPHPExcel->getActiveSheet()->setCellValue(chr($j) . $i, $machineData[$value['machineid']][$col][0] . "," . $machineData[$value['machineid']][$col][1]);
                } else {
                    $objPHPExcel->getActiveSheet()->setCellValue(chr($j) . $i, $machineData[$value['machineid']][$col][0]);
                }
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue(chr($j) . $i, $machineData[$value['machineid']][$col]);
            }
            $j++;
        }
        $i++;
    }

    $objPHPExcel->setActiveSheetIndex(0);
    $fn = "CensusData.xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function EXPORT_GCMDetailsFn($data)
{
    $ProfileName = "GCM_ID_Details";
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Service Tag');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'GCM ID');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Machine OS');

    $i = 2;
    if (safe_count($data) > 0) {
        foreach ($data as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val['serviceTag']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $val['MobileID']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val['machineOS']);
            $i++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'No data available');
    }
    $objPHPExcel->getActiveSheet()->setTitle($ProfileName);
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = $ProfileName . ".xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function EXPORT_skuList($result)
{

    $exportFile = 'skuList';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sku Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Sku Description');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'License Count');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Validity');

    $i = 2;
    if (safe_count($result) > 0) {
        foreach ($result as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $val['skuName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $val['description']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $val['licenseCnt']);
            if ($val['noOfDays'] == '') {
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, '-');
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $i, $val['noOfDays']);
            }

            $i++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, 'No data available');
    }
    $objPHPExcel->getActiveSheet()->setTitle($exportFile);
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = $exportFile . ".xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}
