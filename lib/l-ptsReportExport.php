<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';


function PTS_InstalExcelCreation($data)
{

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:AC1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(30);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sl.no');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Customer Number');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Order Number');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Customer First name');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Customer Last name');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Customer Email');

    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Refund Amount');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Cancel Date');


    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'SKU Description');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Order Date');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Created Date');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Contract End Date');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Phone Number');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Reference Number');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Transaction Date');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Service Tag');
    $objPHPExcel->getActiveSheet()->setCellValue('Q1', 'Installation Date');
    $objPHPExcel->getActiveSheet()->setCellValue('R1', 'Download Status');
    $objPHPExcel->getActiveSheet()->setCellValue('S1', 'Revoke Status');
    $objPHPExcel->getActiveSheet()->setCellValue('T1', 'Brand Name');
    $objPHPExcel->getActiveSheet()->setCellValue('U1', 'Model No');
    $objPHPExcel->getActiveSheet()->setCellValue('V1', 'Operating System');
    $objPHPExcel->getActiveSheet()->setCellValue('W1', 'Current Version');
    $objPHPExcel->getActiveSheet()->setCellValue('X1', 'Old Version');
    $objPHPExcel->getActiveSheet()->setCellValue('Y1', 'Uninstall Date');
    $objPHPExcel->getActiveSheet()->setCellValue('Z1', 'Uninstall Status');


    $index = 2;
    $i = 1;
    foreach ($data as $key => $val) {

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $i);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $val['customerNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $val['orderNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $val['coustomerFirstName']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $val['coustomerLastName']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $val['emailId']);

        $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $val['refundAmt']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['cancelDate'], $index, 'H', 'mm/dd/yyyy hh:mm:ss AM/PM');

        $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $val['SKUDesc']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['orderDate'], $index, 'J', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['createdDate'], $index, 'K', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['contractEndDate'], $index, 'L', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $index, $val['phone_id']);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $index, $val['payRefNum']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['transactionDate'], $index, 'O', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $index, $val['serviceTag']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['installationDate'], $index, 'Q', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel->getActiveSheet()->setCellValue('R' . $index, $val['downloadStatus']);
        $objPHPExcel->getActiveSheet()->setCellValue('S' . $index, $val['revokeStatus']);
        $objPHPExcel->getActiveSheet()->setCellValue('T' . $index, $val['machineManufacture']);
        $objPHPExcel->getActiveSheet()->setCellValue('U' . $index, $val['machineModelNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('V' . $index, $val['machineOS']);
        $objPHPExcel->getActiveSheet()->setCellValue('W' . $index, $val['clientVersion']);
        $objPHPExcel->getActiveSheet()->setCellValue('X' . $index, $val['oldVersion']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['uninsdormatDate'], $index, 'Y', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel->getActiveSheet()->setCellValue('Z' . $index, $val['uninsdormatStatus']);
        

        $index++;
        $i++;
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = "ManagerInstallReport.xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function PTS_UsageExcelCreation($reportData)
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
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sl.no');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Entered Service Tag');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Case/Order Number');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Agent ID');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Agent Name');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Service Tag');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Site Name');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Execution Start Time (CDT)');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Dart Number');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Tile Name');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Type of Execution');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Detail Resolution Report');
    $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Total Execution Time');
    $objPHPExcel->getActiveSheet()->setCellValue('N1', 'Version number');
    $objPHPExcel->getActiveSheet()->setCellValue('O1', 'Brand');
    $objPHPExcel->getActiveSheet()->setCellValue('P1', 'Model Number');


    $index = 2;
    $j = 1;
    foreach ($reportData as $key => $val) {

                $agentDetails = findAgentDetails($val['agentId'], $val['orderno'], $val['customerno']);
                $typeOfExecution = findExecutionType($val['text2']);
                $timeTaken = findTimetakenToExecute($val['text1'], $val['dartno']);

        if (!empty($val['Text1'])) {
            $r = strrpos($val['Text1'], "Total duration of sequence:");
            $res = substr($val['Text1'], $r);
            $ress = explode(" ", $res);
            $ress[3];
            $ress = explode(":", $ress[3]);
            $totalExeTime = $ress[1] . " Seconds";
        } else {
            $totalExeTime = "";
        }


        $executionTime = gmdate("Y-m-d H:i:s", $val['executiontime']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $j);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $val['customerno']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $val['orderno']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $val['agentId']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $val['firstName']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $val['serviceTag']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $val['sitename']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $val['executiontime'], $index, 'H', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $val['dartno']);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $val['tileName']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $typeOfExecution);
        $objPHPExcel->getActiveSheet()->setCellValue('L' . $index, $val['Text1']);
        $objPHPExcel->getActiveSheet()->setCellValue('M' . $index, $totalExeTime);
        $objPHPExcel->getActiveSheet()->setCellValue('N' . $index, $val['clientversion']);
        $objPHPExcel->getActiveSheet()->setCellValue('O' . $index, $val['brandname']);
        $objPHPExcel->getActiveSheet()->setCellValue('P' . $index, $val['machineModelNum']);

        $index++;
        $j++;
    }

    $objPHPExcel->setActiveSheetIndex(0);
    $fn = "ManagerUsageReport.xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function findAgentDetails($agentId, $orderno, $customerno)
{

    $pdo = pdo_connect();
    $result = array();
    $processId = $_SESSION['manager']['processId'];

    if ($val['agentId'] != '' && $val['agentId'] != NULL) {

        $agentSql = $pdo->prepare("select phone_id,center,region,email,approvedBy,first_name from ".$GLOBALS['PREFIX']."agent.Agent where " .
            "(email='" . $val['agentId'] . "' or phone_id='" . $val['agentId'] . "') limit 1");
        $agentSql->execute();
        $agentRes = $agentSql->fetch();

        $result['firstName'] = $agentRes['first_name'];
        $result['approvedBy'] = $agentRes['approvedBy'];
        $result['agentEmail'] = $agentRes['email'];
        $result['region'] = $agentRes['region'];
        $result['centre'] = $agent['center'];
    }
        $skuSql = $pdo->prepare("select emailId, SKUDesc, SKUNum from ".$GLOBALS['PREFIX']."agent.customerOrder where customerNum = '" . $val['customerno'] . "' and" .
        "orderNum = '" . $val['orderno'] . "' and processId = '$processId' order by id desc limit 1");
    $skuSql->execute();
    $skuRes = $skuSql->fetch();

    $result['email'] = $skuRes['emailId'];
    $result['SKUdesc'] = $skuRes['SKUDesc'];
    $result['SKUnum'] = $skuRes['SKUNum'];

    return $result;
}

function findExecutionType($text)
{

    $pos1 = strpos($text, "Type of run:");
    if ($pos1 === false) {
        $typeofExecution = '';
    } else {
        $typeofExe = explode("Type of run:", $row['text2']);
        $pos2 = strpos($typeofExe[1], "Items to enable:");
        if ($pos2 === false) {
            $typeofExecution = $typeofExe[1];
        } else {
            $typeofExe1 = explode("Items to enable:", $typeofExe[1]);
            $typeofExecution = $typeofExe1[0];
        }
    }

    $TOE = strpos($typeofExecution, "Consumer");

    if ($TOE === false) {
        $typeofExecution = 'Remote';
    } else {
        $typeofExecution = 'Client';
    }
    return $typeofExecution;
}

function findTimetakenToExecute($str, $dartNum)
{
    $pdo = pdo_connect();
    $str1 = strip_tags($str);
    $str2 = str_replace("Sequence completed successfully", "", $str1);
    $str3 = str_replace("successfully", "", $str2);
    $strre = str_replace("Successful", "", $str3);
    $str4 = str_replace("Seocnds", "Seconds", $strre);

    $pos = strpos($str, "Sequence name:");
    $tileName = '';

    if ($pos === false) {

        if ($dartNum == 256) {
            $tileName = 'Windows Tweaks';
        } else {
            $tileName = '';
        }
    } else {

        $tailData = explode(":", $str);

        $tstr = explode("[", $tailData[1]);
        $tstr1 = explode("]", $tstr[1]);
        $tile = $tstr1[0];
        if ($tile != '') {
            $tilename_sql = $pdo->prepare("select profile from " . $profile . " where varValue like '%" . $tile . "%' limit 1");
            $tilename_sql->execute();
            $tilename_result = $tilename_sql->fetchAll();
            foreach ($tilename_result as $key => $value) {
                $tileName = $value['profile'];
            }
        } else {
            if ($dartNum == 256) {
                $tileName = 'Windows Tweaks';
            } else {
                $tileName = '';
            }
        }
    }
    $time_Taken = '';
    $pos3 = strpos($str4, "Total time taken by the sequence :");
    if ($pos3 === false) {
        $time_Taken = '';
    } else {
        $typeofTime = explode("Total time taken by the sequence :", $str4);
        $time_Taken = trim($typeofTime[1]);
    }

    if ($dartNum == 286) {
        $pos286 = strpos($str4, "Total duration of sequence:");
        if ($pos286 === false) {
            $time_Taken = '';
        } else {
            $typeofTime1 = explode("Total duration of sequence:", $str4);
            $time_Taken = trim($typeofTime1[1]);
                    }
    }
    return array('timeTaken' => $time_Taken, 'tileName' => $tileName, 'str' => $str4);
}

function PTS_SalesExcelCreation($salesData)
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
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);


    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Customer Number');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Order Number');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Customer First name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Customer Last name');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Customer Email');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'SKU Description');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Order Date');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Created Date');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Contract End Date');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Quantity');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Payment Reference  Number');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Transaction Date');

    $index = 2;
    $j = 1;

    foreach ($salesData as $key => $row) {

        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $row['customerNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $row['orderNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $row['coustomerFirstName']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $row['coustomerLastName']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $row['emailId']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $row["SKUDesc"]);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $row['orderDate'], $index, 'G', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $row['createDate'], $index, 'H', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $row['contractEndDate'], $index, 'I', 'mm/dd/yyyy hh:mm:ss AM/PM');
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $row["noOfPc"]);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $row['payRefNum']);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $row['transactionDate'], $index, 'L', 'mm/dd/yyyy hh:mm:ss AM/PM');

        $index++;
        $j++;
    }
    $objPHPExcel->setActiveSheetIndex(0);
    $fn = "ManagerSalesReport.xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}