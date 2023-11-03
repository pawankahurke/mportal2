<?php

include_once('l-user.php');

function CUSTAJX_GetCustDetails()
{

    $key = '';
    $conn = db_connect();
    $cid = $_SESSION['user']['cId'];
    $pid = $_SESSION['user']['pid'];
    $custNum = url::requestToText('custNum');
    $ordNum = url::requestToText('ordNum');

    $result = CUST_GetCustomerDetail($key, $conn, $cid, $pid, $custNum, $ordNum);
    print_json_data($result);
}

function CUSTAJX_GetCustDetailsById()
{
    $key = '';
    $conn = NanoDB::connect();
    $id = url::requestToText('id');

    $result = CUST_GetCustomerDetailById($key, $conn, $id);
    print_json_data($result);
}

function getMachineDetails()
{
    $key = '';
    $conn = NanoDB::connect();
    $sid = strip_tags(UTIL_GetString('sid', ''));
    $result = CUST_GetMachineDetails($key, $conn, $sid);
    print_json_data($result);
}

function getInstalledOrderDetails()
{
    global $download_ClientUrl;
    $key = '';
    $conn = NanoDB::connect();
    $sid = strip_tags(UTIL_GetString('sid', ''));
    $result = CUST_GetMachineDetails($key, $conn, $sid);
    $result['downloadUrl'] = $download_ClientUrl . 'eula.php?sid=' . $result['downloadId'];
    print_json_data($result);
}

function getNotInstalledOrderDetails()
{
    global $download_ClientUrl;

    $key = '';
    $conn = NanoDB::connect();
    $cid = strip_tags(UTIL_GetString('cid', ''));
    $pid = strip_tags(UTIL_GetString('pid', ''));
    $custNum = strip_tags(UTIL_GetString('custNum', ''));
    $ordNum = strip_tags(UTIL_GetString('ordNum', ''));
    $result = CUST_GetCustomerDetail($key, $conn, $cid, $pid, $custNum, $ordNum);
    $result['downloadUrl'] = $download_ClientUrl . 'eula.php?id=' . $result['downloadId'];

    print_json_data($result);
}

function getDetailedCustomers()
{
    global $download_ClientUrl;

    $key = '';
    $conn = NanoDB::connect();
    $cid = strip_tags(UTIL_GetString('compId', ''));
    $pid = strip_tags(UTIL_GetString('processId', ''));
    $custNum = strip_tags(UTIL_GetString('customerNum', ''));
    $result = CUST_GetOrders($key, $conn, $cid, $pid, $custNum);
    foreach ($result as $key => $value) {
        $url = $download_ClientUrl . 'eula.php?id=' . $value['downloadId'];

        $recordList[] = array(
            "ordNum" => '<p class="ellipsis" onclick="" title="' . $value['orderNum'] . '">' . $value['orderNum'] . '</p>',
            "email" => '<p class="ellipsis" onclick="" title="' . date("m/d/Y H:i", $value['createdDate']) . '">' . date("m/d/Y H:i", $value['createdDate']) . '</p>',
            "enddate" => '<p class="ellipsis" onclick="" title="' . date("m/d/Y H:i", $value['contractEndDate']) . '">' . date("m/d/Y H:i", $value['contractEndDate']) . '</p>',
            "url" => '<p title="' . $url . '">' . $url . '</p>',
        );
    }
    $jsonData = array("draw" => 1, "recordsTotal" => safe_count($result), "recordsFiltered" => safe_count($result), "data" => $recordList);

    print_json_data($jsonData);
}

function CUSTAJX_GetAviraDetailsGrid()
{
    $key = '';
    $conn = NanoDB::connect();
    $sid = url::requestToText('sid');
    $aviraDetails = RSLR_GetAviraDetails($key, $conn, $sid);

    foreach ($aviraDetails as $key => $value) {

        $recordList[] = array(
            "serviceTag" => $value['serviceTag'],
            "prodName" => '<p class="ellipsis" onclick="" title="' . $value['productName'] . '">' . $value['productName'] . '</p>',
            "prodDate" => '<p class="ellipsis" onclick="" title="' . $value['productDate'] . '">' . $value['productDate'] . '</p>',
            "licenseExpire" => '<p class="ellipsis" onclick="" title="' . $value['licenseExpiration'] . '">' . $value['licenseExpiration'] . '</p>',
        );
    }
    $jsonData = array("draw" => 1, "recordsTotal" => safe_count($aviraDetails), "recordsFiltered" => safe_count($aviraDetails), "data" => $recordList);
    print_json_data($jsonData);
}

function CUSAJX_IsAviraInstalled()
{
    $key = '';
    $conn = NanoDB::connect();
    $sid = url::requestToText('sid');
    $isAviraInstalled = RSLS_IsAviraInstalled($key, $conn, $sid);

    if ($isAviraInstalled == 'true') {
        $msg = "1";
        print_data($msg);
    } else {
        $msg = "0";
        print_data($msg);
    }
}

function CUSTAJX_IsValidAddCustomer()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION['user']['cId'];

    $isValid = RSLS_IsValidAddCustomer($key, $conn, $loggedEid);
    if ($isValid == '1') {
        $resArray = array("status" => '1', "msg" => "You are still on our Trial plan. Please buy licenses to add more customers");
    } else if ($isValid == '2') {
        $resArray = array("status" => '2', "msg" => "You have 0 unused licenses. Please buy more licenses to add more devices");
    } else if ($isValid == '3') {
        $resArray = array("status" => '3', "msg" => "");
    }
    print_json_data($resArray);
}

function CUSTAJX_getOTClist()
{

    $db = NanoDB::connect();
    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $sql_Coust = "select id, otcCode, productName, licenseCnt, usedLicense, contractEndDate from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where ch_id=? and status=1 and restricted = '0'";
    $pdo = $db->prepare($sql_Coust);
    $pdo->execute([$cid]);
    $res_Coust = $pdo->fetchAll(PDO::FETCH_ASSOC);

    $str = '<option value="">Please select OTC</option>';
    if (is_array($res_Coust) && safe_count($res_Coust) > 0) {
        foreach ($res_Coust as $value) {

            $str .= '<option value="' . $value['id'] . '">' . $value['productName'] . ' (' . $value['otcCode'] . ')</option>';
        }
    } else {
        $str = '<option value="">No OTC Available</option>';
    }
    print_data($str);
}

function CUSTAJX_getAviraOTCDtl()
{

    $db = NanoDB::connect();
    $otcId = url::requestToText('otcCode');
    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $sql_Coust = "select otcCode,productName,licenseCnt,usedLicense,contractEndDate,licenseKey, used, pending from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where ch_id=? and id=?";
    $pdo = $db->prepare($sql_Coust);
    $pdo->execute([$cid, $otcId]);
    $res_Coust = $pdo->fetch(PDO::FETCH_ASSOC);

    $sql_Cust = "select sum(noOfPc) pcCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder where licenseKey='" . $res_Coust['licenseKey'] . "'";
    $pdo = $db->prepare($sql_Cust);
    $pdo->execute([$res_Coust['licenseKey']]);
    $res_cust = $pdo->fetch(PDO::FETCH_ASSOC);

    $str = $res_Coust['licenseCnt'] . '---' . $res_Coust['pending'] . '---' . $res_Coust['contractEndDate'] . '---' . $res_Coust['used'];
    print_data($str);
}

function CUSTAJX_getAviraCustDtl()
{
    global $download_ClientUrl;
    $db = NanoDB::connect();
    $otcId = url::requestToText('otcCode');
    $customerType = $_SESSION['user']['customerType'];

    if ($customerType == 2 || $customerType == '2') {
        $loggedEid = $_SESSION["user"]["cId"];
    } elseif ($customerType == 5 || $customerType == '5') {
        $loggedEid = $_SESSION["user"]["channelId"];
    }

    $draw = 1;
    $jsonData = '';
    if ($customerType == 0 || $customerType == 1) {
        $sql_Coust = "select otcCode,productName,licenseCnt,usedLicense,contractEndDate,licenseKey,ch_id from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where (id=? or otcCode=?)";
        $bind = [$otcId, $otcId];
    } else {
        $sql_Coust = "select otcCode,productName,licenseCnt,usedLicense,contractEndDate,licenseKey,ch_id from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where ch_id=? and (id=? or otcCode=?)";
        $bind = [$loggedEid, $otcId, $otcId];
    }

    $pdo = $db->prepare($sql_Coust);
    $pdo->execute($bind);
    $res_Coust = $pdo->fetch(PDO::FETCH_ASSOC);

    if (is_array($res_Coust) && safe_count($res_Coust) > 0) {

        $otcRef = $res_Coust['licenseKey'];
        if ($customerType == 0 || $customerType == 1) {
            $passChId = $res_Coust['ch_id'];
        } else if (intval($customerType) == 5) {
            $passChId = $_SESSION["user"]["cId"];
        } else {
            $passChId = $loggedEid;
        }

        $totalRecords = RSLR_GetOTCBasedCustomer('', $db, $passChId, $customerType, $otcRef);

        print_json_data($totalRecords);
    } else {
        $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
        print_json_data($jsonData);
    }
}

function CUSTAJX_getNHCustDtl()
{
    global $download_ClientUrl;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $otcId = url::requestToAny('otcCode');
    $customerType = $_SESSION['user']['customerType'];
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = 1;
    $jsonData = '';
    $sql_Coust = "select id,chnl_id,skuNum,skuDesc,licenseCnt,installCnt,purchaseDate,contractEndDate,orderNum from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id='$loggedEid' and (id='$otcId' or orderNum='$otcId')";
    $res_Coust = find_one($sql_Coust, $db);
    if (is_array($res_Coust) && safe_count($res_Coust) > 0) {

        $otcRef = $res_Coust['orderNum'];

        $totalRecords = RSLR_GetNHLicBasedCustomer('', $db, $loggedEid, $customerType, $otcRef);

        if (is_array($totalRecords) && safe_count($totalRecords) > 0) {
            foreach ($totalRecords as $key => $value) {

                $url = $download_ClientUrl . 'eula.php?id=' . $value['downloadId'];
                $rowId = $value['compId'] . '##' . $value['processId'] . '##' . $value['customerNum'] . '##' . $value['orderNum'];

                $custName = CUSTAJX_CreatPTag($value['coustomerFirstName']);
                $pccount = CUSTAJX_CreatPTag($value['noOfPc'] . '/' . $value['installedCnt']);
                $recordList[] = array('DT_RowId' => $rowId, 'customername' => $custName, 'pccount' => $pccount, 'sitename' => $value['siteName'], 'custNum' => $value['customerNum'], 'custId' => $value['downloadId'], 'url' => $url);
            }
        } else {
            $recordList = [];
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalRecords), "recordsFiltered" => safe_count($totalRecords), "data" => $recordList);
    } else {
        $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    }
    print_json_data($jsonData);
}

function CUSTAJX_getAviraInstallDtl()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $otcCode = url::requestToAny('otcCode');
    $customerType = $_SESSION['user']['customerType'];

    if ($customerType == 2 || $customerType == '2') {
        $loggedEid = $_SESSION["user"]["cId"];
    } elseif ($customerType == 5 || $customerType == '5') {
        $loggedEid = $_SESSION["user"]["channelId"];
    }
    $next30Days = strtotime('+30 days');
    if ($customerType == 0 || $customerType == 1) {
        $sql_Coust = "select otcCode,productName,licenseCnt,usedLicense,contractEndDate,licenseKey,ch_id,restricted from aviraLicenses where otcCode='$otcCode'";
    } else {
        $sql_Coust = "select otcCode,productName,licenseCnt,usedLicense,contractEndDate,licenseKey,ch_id,restricted from aviraLicenses where ch_id='$loggedEid' and otcCode='$otcCode'";
    }
    $res_Coust = find_one($sql_Coust, $db);

    if (is_array($res_Coust) && safe_count($res_Coust) > 0) {

        if ($customerType == 0 || $customerType == 1) {
            $chRef = $res_Coust['ch_id'];
            $compIdSql = "SELECT GROUP_CONCAT(eid) as compId FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE channelId = '$chRef' AND ctype = '5' LIMIT 1";
        } else {
            $compIdSql = "SELECT GROUP_CONCAT(eid) as compId FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE channelId = '$loggedEid' AND ctype = '5' LIMIT 1";
        }

        $compIdRes = find_one($compIdSql, $db);

        $compId = $compIdRes['compId'];
        $otcRef = $res_Coust['licenseKey'];
        $restricted = $res_Coust['restricted'];

        $renew_sql = "SELECT count(S.sid) renewCnt FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C LEFT JOIN "
            . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum "
            . " and S.revokeStatus='I' where  C.compId in ($compId) AND C.licenseKey = '$otcRef' AND uninstallDate <= '$next30Days' GROUP BY C.customerNum,S.serviceTag";
        $renew_res = find_one($renew_sql, $db);

        $sql_cust = "select licenseCnt, used, pending from aviraLicenses S  where S.otcCode='$otcCode'";
        $res_cust = find_one($sql_cust, $db);

        $totalLic = $res_cust['licenseCnt'];
        $ununsed = $res_cust['pending'];
        if (isset($renew_res['renewCnt'])) {
            $renewCount = ($renew_res['renewCnt'] > 0) ? $renew_res['renewCnt'] : "0";
        } else {
            $renewCount = "0";
        }
        $msg = $totalLic . '---' . $ununsed . '---' . $renewCount . '---' . $restricted;
        print_data($msg);
    } else {
        $msg = "0---0---0---0";
        print_data($msg);
    }
}

function CUSTAJX_getNHLIClist()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $sql_Coust = "select id,chnl_id,skuNum,skuDesc,licenseCnt,installCnt,purchaseDate,contractEndDate,orderNum from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id='$cid' and status='1' and nh_lic='1' and trial='0'";
    $res_Coust = find_many($sql_Coust, $db);
    $str = '<option value="">Please select licenses</option>';
    foreach ($res_Coust as $value) {
        $str .= '<option value="' . $value['id'] . '">' . $value['skuDesc'] . ' (' . $value['orderNum'] . ')</option>';
    }
    print_data($str);
}

function CUSTAJX_getNHOTCDtl()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $otcId = url::requestToAny('otcCode');
    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $sql_Coust = "select id,chnl_id,skuNum,skuDesc,licenseCnt,installCnt,purchaseDate,contractEndDate,orderNum from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id='$cid' and (id='$otcId' or orderNum='$otcId')";
    $res_Coust = find_one($sql_Coust, $db);
    $uniDate = date("m/d/Y", $res_Coust['contractEndDate']);

    $sql_Cust = "select sum(noOfPc) pcCnt from customerOrder where nhOrderKey='" . $res_Coust['orderNum'] . "'";
    $res_cust = find_one($sql_Cust, $db);

    $insCnt = 0;
    if (is_array($res_cust) && safe_count($res_cust) > 0) {
        if ($res_cust['pcCnt'] != null) {
            $insCnt = $res_cust['pcCnt'];
        }
    }
    $pendingCnt = $res_Coust['licenseCnt'] - $insCnt;
    $str = $res_Coust['licenseCnt'] . '---' . $pendingCnt . '---' . $uniDate . '---' . $insCnt;
    print_data($str);
}

function CUSTAJX_changeAviraConfiguration()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $downloadId = url::requestToAny('downloadId');
    $unistallModule = url::requestToAny('unistallModule');
    $allModules = url::requestToAny('allModules');

    $sql_cust = "select * from customerOrder where WHERE downloadId = '$downloadId'";
    $res_cust = find_one($sql_cust, $db);
    if (is_array($res_cust) && safe_count($res_cust) > 0) {
        $update = "UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET aviraModules= '$allModules', aviraVrnUpdt = '$unistallModule' WHERE downloadId = '$downloadId'";
        $updateRes = redcommand($update, $db);
        $custNO = $res_cust['customerNum'];
        $orderNo = $res_cust['orderNum'];

        $sql_serv = "select * from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where S.customerNum='$custNO' and S.orderNum='$orderNo' limit 1";
        $res_serv = find_one($sql_serv, $db);
        if (is_array($res_serv) && safe_count($res_serv) > 0) {

            $cust_ini = $res_serv['iniValues'];
            $sid = $res_serv['sid'];

            $strAviraMod = "AvModules=" . $allModules;
            $strAviraVer = "AviraOldRemoval=" . $unistallModule;
            $aviraMod = "AvModules=AvGuard,AvMailScanner,AvWebGuard,AvRootKit,AvProActiv,AvMgtFirewall";
            $aviraVrnUpdt = "AviraOldRemoval=0";
            if ($allModules != '' && $allModules != null) {
                $cust_ini = str_replace($aviraMod, $strAviraMod, $cust_ini);
            }
            $cust_ini = str_replace($aviraVrnUpdt, $strAviraVer, $cust_ini);

            $update_serv = "UPDATE  " . $GLOBALS['PREFIX'] . "agent.serviceRequest SET iniValues= '$cust_ini' WHERE sid = '$sid'";
            redcommand($update_serv, $db);
        }
    }
    $msg = "success";
    print_data($msg);
}

function CUSTAJX_SetDefaultGateway()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $alterTable = CUSTAJX_AlterTable($db, "agent", "customerOrder", "gateWayStatus", "ENUM('1','0')", "0");
    $downloadId = UTIL_GetString('downloadId', '');
    $GatewayMachine = UTIL_GetString('GatewayMachine', '');

    $customeDetails = CUST_GetCustomerDetailByDownloadId($db, $downloadId);
    $compId = $customeDetails['compId'];
    $processId = $customeDetails['processId'];
    $customerNum = $customeDetails['customerNum'];
    $orderNum = $customeDetails['orderNum'];
    $serviceTag = $GatewayMachine;
    $cust_ini = $customeDetails['sessionIni'];
    $siteName = $customeDetails['siteName'];
    $aviraModules = $customeDetails['aviraModules'];
    $uninstallDate = $customeDetails['contractEndDate'];
    $aviraVrnUpdt = $customeDetails['aviraVrnUpdt'];

    RSLR_InsertGatewayMachine("", $db, $compId, $processId, $customerNum, $orderNum, $serviceTag, $cust_ini, $siteName, $aviraModules, $aviraVrnUpdt, $uninstallDate);
    $msg = "success";
    print_data($msg);
}

function CUSTAJX_getNHInstallDtl()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $otcId = url::requestToAny('nhId');
    $next15Days = strtotime('+15 days');
    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $sql_Coust = "select id,chnl_id,skuNum,skuDesc,licenseCnt,installCnt,purchaseDate,contractEndDate,orderNum from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id='$cid' and (id='$otcId' or orderNum='$otcId')";
    $res_Coust = find_one($sql_Coust, $db);
    if (is_array($res_Coust) && safe_count($res_Coust) > 0) {
        $sql_Cust = "select sum(noOfPc) pcCnt from customerOrder where nhOrderKey='" . $res_Coust['orderNum'] . "'";
        $res_cust = find_one($sql_Cust, $db);

        $insCnt = 0;
        if (is_array($res_cust) && safe_count($res_cust) > 0) {
            if ($res_cust['pcCnt'] != null) {
                $insCnt = $res_cust['pcCnt'];
            }
        }
        $pendingCnt = $res_Coust['licenseCnt'] - $insCnt;
        $totalLic = $res_Coust['licenseCnt'];

        $renew_sql = "SELECT count(S.sid) renewCnt FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C LEFT JOIN "
            . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum "
            . " and S.revokeStatus='I' where  C.nhOrderKey = '$otcId' AND uninstallDate <= '$next15Days' GROUP BY C.customerNum,S.serviceTag";
        $renew_res = find_one($renew_sql, $db);

        $renewCount = ($renew_res['renewCnt'] > 0) ? $renew_res['renewCnt'] : "0";
        $msg = $totalLic . '---' . $pendingCnt . '---' . $renewCount;
        print_data($msg);
    } else {
        $msg = '0---0---0';
        print_data($msg);
    }
}

function CUSAJX_Avira_GetExcelObject($custRes, $conn)
{
    $objPHPExcel = CUSAJX_Avira_CreateOTCSheet($conn);
    $objPHPExcel = CUSAJX_Avira_CreateCustomersSheet($custRes, $objPHPExcel, $conn);
    $objPHPExcel = CUSAJX_Avira_CreateDeviceSheet($custRes, $objPHPExcel, $conn);
    $customerType = $_SESSION['user']['customerType'];

    if ($customerType != 5) {
        $objPHPExcel = CUSAJX_Avira_ActivatedOTCSheet($conn, $objPHPExcel);
    }

    return $objPHPExcel;
}

function CUSAJX_Avira_CreateOTCSheet($conn)
{
    $loggedEid = $_SESSION['user']['cId'];
    $objPHPExcel = CUSAJX_Avira_GetOTCSheetHeader();
    $ordeDetails = RSLR_GetOrderDetailsGrid_PDO("", $conn, $loggedEid, "");
    $index = 2;

    foreach ($ordeDetails as $key => $octArr) {

        $otcLicense = $octArr['aviraOtc'];
        $aviraDetails = RSLR_GetAviraLicenses($conn, "otcCode", $otcLicense);
        $customers = RSLR_GetCustomersForOTCCode($conn, $loggedEid, $otcLicense);

        if (is_array($customers) && safe_count($customers) > 0) {

            foreach ($customers as $key => $val) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $octArr['aviraOtc']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $aviraDetails['emailId']);
                $compName = CUSTAJX_GetTrimmedCompName($aviraDetails['companyname']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $compName);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $octArr['skuDesc']);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $aviraDetails['licenseCnt']);
                $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $octArr['purchaseDate'], $index, 'F', "dddd, mmmm d, yyyy hh:mm:ss");
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $val['coustomerFirstName']);
                $index++;
            }
        } else {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $octArr['aviraOtc']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $aviraDetails['emailId']);
            $compName = CUSTAJX_GetTrimmedCompName($aviraDetails['companyname']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $compName);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $octArr['skuDesc']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $aviraDetails['licenseCnt']);
            $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $octArr['purchaseDate'], $index, 'F', "dddd, mmmm d, yyyy hh:mm:ss");
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, 'Customer Not Available');
            $index++;
        }
    }

    return $objPHPExcel;
}

function CUSAJX_Avira_GetOTCSheetHeader()
{
    try {
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', "OTC Code");
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "Email");
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "Company Name");
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(60);
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "Description");
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('E1', "Licenses");
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('F1', "Purchase Date");
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "Customer Name");

        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle("OTC Details");

        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function CUSAJX_Avira_ActivatedOTCSheet($conn, $objPHPExcel)
{
    $loggedEid = $_SESSION['user']['cId'];
    $objPHPExcel = CUSAJX_Avira_GetOTCListSheetHeader($objPHPExcel);
    $ordeDetails = RSLR_GetResellerAllOTC($conn, $loggedEid);
    $index = 2;
    $now = time();
    $purchaseDt = '';
    $dayDiff = '';
    foreach ($ordeDetails as $key => $octArr) {
        $firstDt = $octArr['createdDate'];
        if ($firstDt != '') {
            $purchaseDt = date("m/d/Y", $firstDt);

            $datediff = $now - $firstDt;
            $dayDiff = floor($datediff / (60 * 60 * 24));
        }
        $siteName = $octArr['siteName'];
        $lastAlive = CUSAJX_Avira_LastAlive($siteName, $conn);
        $aliveDt = '';
        if ($lastAlive != 0) {
            $aliveDt = date("m/d/Y", $lastAlive);
        }
        $dateTimeNow = time();
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $octArr['otcCode']);
        if ($firstDt != '') {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, PHPExcel_Shared_Date::PHPToExcel($firstDt));
            $objPHPExcel->getActiveSheet()->getStyle('B' . $index)->getNumberFormat()->setFormatCode("mm-dd-yyyy");
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '');
        }
        $contractEDate = strtotime($octArr['contractEndDate']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, PHPExcel_Shared_Date::PHPToExcel($contractEDate));
        $objPHPExcel->getActiveSheet()->getStyle('C' . $index)->getNumberFormat()->setFormatCode("mm-dd-yyyy");

        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $octArr['runtime']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $octArr['licenseCnt']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $octArr['productId']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $octArr['productName']);
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, $octArr['firstName']);
        $compName = CUSTAJX_GetTrimmedCompName($octArr['companyname']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $compName);
        $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $octArr['emailId']);
        $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $dayDiff);
        $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $lastAlive, $index, 'L', "dd-mm-yyyy");
        $index++;
    }
    return $objPHPExcel;
}

function CUSAJX_Avira_GetOTCListSheetHeader($objPHPExcel)
{
    try {
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(3);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', "OTC");
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "FirstActivationDate");
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "ExpiryDate");
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "AviraRuntime");
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->setCellValue('E1', "AviraUsers");
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('F1', "AviraProductID");
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "AviraProductName");
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('H1', "Name");
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('I1', "Company");
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('J1', "Email");
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('K1', "DaysActive");
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('L1', "LastAliveDate");

        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle("OTC Activation Details");

        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function CUSAJX_Avira_CreateCustomersSheet($customersArray, $objPHPExcel, $conn)
{
    try {
        $index = 2;

        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', "Customer Name");
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "Total Devices Assigned");
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "Number Of Devices");
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "OTC Code");

        $ch_id = $_SESSION['user']['cId'];

        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle("Customer's Details");

        foreach ($customersArray as $key => $value) {
            $licenseKey = $value['licenseKey'];
            $OTCDetails = RSLR_GetOTCDetails($conn, $ch_id, $licenseKey);

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['coustomerFirstName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['noOfPc']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['installedCnt']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $OTCDetails['otcCode']);
            $index++;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}

function CUSAJX_Avira_LastAlive($sitename, $conn)
{
    $sql_census = "select max(last) lastAlive from " . $GLOBALS['PREFIX'] . "core.Census C where C.site=?";
    $pdo = NanoDB::connect()->prepare($sql_census);
    $pdo->execute(array($sitename));
    $result = $pdo->fetch(PDO::FETCH_ASSOC);

    if (is_array($result) && safe_count($result) > 0) {
        return $result['lastAlive'];
    } else {
        return 0;
    }
}

function CUSAJX_Avira_CreateDeviceSheet($customersArray, $objPHPExcel, $conn)
{
    try {
        $highestRow = 2;
        $objPHPExcel = CUSAJX_Avira_GetDeviceSheetHeader($objPHPExcel);

        $ch_id = $_SESSION['user']['cId'];

        foreach ($customersArray as $key => $custArr) {
            $compId = $custArr['compId'];
            $processId = $custArr['processId'];
            $customerNum = $custArr['customerNum'];
            $orderNum = $custArr['orderNum'];

            $deviceRes = RSLR_GetCustomerDevicesGrid($key, $conn, $compId, $processId, $customerNum, $orderNum, "");
            $objPHPExcel->setActiveSheetIndex(2);
            $highestRow = $objPHPExcel->setActiveSheetIndex(2)->getHighestRow();

            if (is_array($deviceRes) && safe_count($deviceRes) > 0) {
                foreach ($deviceRes as $key => $value) {
                    $highestRow++;
                    $serviceTag = ($value['serviceTag'] != '') ? $value['serviceTag'] : "-";
                    $os = ($value['machineOS'] != '') ? $value['machineOS'] : "-";
                    $modalNo = ($value['machineModelNum'] != '') ? $value['machineModelNum'] : "-";
                    $NHInstall = ($value['installationDate'] != '') ? $value['installationDate'] : "-";
                    $NHUninstall = ($value['uninstallDate'] != '') ? $value['uninstallDate'] : "-";

                    if ($value['aviraId'] != '') {
                        $deviceStatus = CUSAJX_GetAviraStatus($value['aviraId']);
                        $aviraDetails = RSLR_GetAviraDetails($key, $conn, $value['sid']);
                        $aviraInstall = $aviraDetails[0]['productDate'];
                        $aviraUninstall = $aviraDetails[0]['licenseExpiration'];
                        $productName = $aviraDetails[0]['productName'];
                    } else {
                        $deviceStatus = 'Not Installed';
                        $aviraInstall = '-';
                        $aviraUninstall = '-';
                        $productName = "";
                    }

                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, $custArr['coustomerFirstName']);
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, $serviceTag);
                    $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, $os);
                    $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, $modalNo);

                    $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, strtotime($aviraInstall), $highestRow, 'E', "dddd, mmmm d, yyyy hh:mm:ss");
                    $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, strtotime($aviraUninstall), $highestRow, 'F', "dddd, mmmm d, yyyy hh:mm:ss");
                    $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $NHInstall, $highestRow, 'G', "dddd, mmmm d, yyyy hh:mm:ss");
                    $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $NHUninstall, $highestRow, 'H', "dddd, mmmm d, yyyy hh:mm:ss");
                    $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, strip_tags($deviceStatus));
                    $objPHPExcel->getActiveSheet()->setCellValue('J' . $highestRow, $productName);
                }
            }
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}

function CUSAJX_Avira_GetDeviceSheetHeader($objPHPExcel)
{
    try {
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(2);

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', "Customer Name");
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('B1', "Device ID");
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('C1', "Operating System");
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('D1', "Model No.");
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('E1', "Avira Installed");
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('F1', "Avira Uninstalled");
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "NH Installed");
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('H1', "NH Unistalled");
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->setCellValue('I1', "Avira Status");
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('J1', "Product Name");

        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle("Customer's Devices");

        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function CUSTAJX_GetAviraCustomerDetails()
{
    $key = '';
    $conn = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $cid = url::requestToAny('cid');
    $pid = url::requestToAny('pid');
    $custNum = url::requestToAny('custNum');
    $ordNum = url::requestToAny('ordNum');

    $custDetails = CUST_GetCustomerDetail($key, $conn, $cid, $pid, $custNum, $ordNum);
    $serviceReqs = CUST_GetServiceRequests($key, $conn, $cid, $pid, $custNum, $ordNum);
    $licenseKey = $custDetails['licenseKey'];
    $custDetails['used'] = safe_count($serviceReqs);
    $custDetails['pending'] = $custDetails['noOfPc'] - safe_count($serviceReqs);
    $custDetails['otcDetails'] = RSLR_AviraOtcDetails($conn, $loggedEid, $licenseKey);
    print_json_data($custDetails);
}

function CUSTAJX_EditAviraCustomer()
{
    $conn = db_connect();

    $loggedEid = $_SESSION["user"]["cId"];
    $custNum = url::requestToAny('custNum');
    $ordNum = url::requestToAny('ordNum');
    $pcCount = url::requestToAny('pcCnt');
    $OTC = url::requestToAny('OTC');

    $result = RSLR_EditAviraCustomer($conn, $custNum, $ordNum, $pcCount);
    if ($result['status'] == 1) {
        $OTCDetails = RSLR_GetOTCDetails($conn, $loggedEid, $OTC);
        $updateLicense = RSLR_Update_EditAviraLicense($conn, $loggedEid, $OTCDetails);
    }
    print_json_data($result);
}

function CUSTAJX_disableCust()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $chId = url::requestToAny('chId');
    $sql_change = "update channel C set C.status=0 where C.eid='$chId'";
    $updRes = redcommand($sql_change, $db);
    if ($updRes) {
        $msg = 'done';
        print_data($msg);
    } else {
        $msg = 'fail';
        print_data($msg);
    }
}

function CUSTAJX_enableCust()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $chId = url::requestToAny('chId');
    $sql_change = "update channel C set C.status=1 where C.eid='$chId'";
    $updRes = redcommand($sql_change, $db);
    if ($updRes) {
        $msg = 'done';
        print_data($msg);
    } else {
        $msg = "fail";
        print_data($msg);
    }
}

function CUSTAJX_CreatPTag($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == null || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}

function CUSTAJX_CreatSelectedOption($value, $lable, $selected)
{
    $optionStr = '<option value=' . trim($value) . ' ' . $selected . '>' . trim($lable) . '</option>';
    return $optionStr;
}

function CUSTAJX_MSPOrderDetails()
{
    $key = '';
    $str = '<option value="">Please select order number</option>';
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $orderList = RSLR_GetOrderDetailsGrid($key, $conn, $loggedEid, "");
    if (is_array($orderList) && safe_count($orderList) > 0) {
        $str .= '<option value="new">New Order Number</option>';
        foreach ($orderList as $key => $value) {
            $str .= '<option value="' . $value['orderNum'] . '--' . $value['licenseCnt'] . '--' . $value['installCnt'] . '--' . date("Y-m-d H:i:s", $value['contractEndDate']) . '">' . $value['orderNum'] . '</option>';
        }
    } else {
        $str = '<option value="">No orders are available</option>';
    }
    print_r($str);
}

function CUSTAJX_ResellerCustomers()
{
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION['user']['cId'];
    $loggedCtype = $_SESSION['user']['customerType'];
    $username = $_SESSION["user"]["username"];

    // if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
    // @warn function CUST_GetAllCustomerForUsers do not exists.
    //     $customerList = CUST_GetAllCustomerForUsers("", $conn, $loggedEid, $loggedCtype);
    //     if (is_array($customerList) && safe_count($customerList) > 0) {
    //         print_json_data($customerList);
    //     }
    // } else {
    $sites = USER_GetSiteWithUsername($conn, $username);
    $i = 0;
    foreach ($sites as $value) {
        $customerList[$i]['eid'] = $value;
        $temp = UTIL_GetTrimmedGroupName($value);
        $customerList[$i]['companyName'] = $temp;
        $i++;
    }
    print_json_data($customerList);
    // }
}

function CUSTAJX_ResellerCustomersForUserAdd()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $loggedCtype = $_SESSION['user']['customerType'];
    $username = $_SESSION["user"]["username"];

    // if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
    //     // @warn function CUST_GetAllCustomerForUsers do not exists.
    //     $customerList = CUST_GetAllCustomerForUsers("", $conn, $loggedEid, $loggedCtype);

    //     if (is_array($customerList) && safe_count($customerList) > 0) {
    //         echo json_encode($customerList);
    //     }
    // } else {
    $sites = USER_GetSiteWithUsername($conn, $username);
    $i = 0;
    foreach ($sites as $value) {
        $customerList[$i]['eid'] = $value;
        $customerList[$i]['companyName'] = UTIL_GetTrimCompanyName($value);
        $i++;
    }
    echo json_encode($customerList);
    // }
}

function CUSTAJX_VerifyOTC()
{
    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);

    $otcCode = UTIL_GetString('otcCode', '');
    $emailid = UTIL_GetString('email', '');
    $companyName = UTIL_GetString('compName', '');
    $vallidOtc = RSLR_VerifyOTC($conn, $otcCode, $emailid, $companyName);
    print_json_data($vallidOtc);
}


//function CUSTAJX_ResendUserMail()
//{
//    global $base_url;
//    $conn = db_connect();
//    db_change($GLOBALS['PREFIX'] . "agent", $conn);
//
//    $sel_userId = UTIL_GetString('sel_userId', '');
//    $language = UTIL_GetString('language', '');
//    $userDetails = USER_GetUserDetail('', $conn, $sel_userId);
//    $username = $userDetails['username'];
//    $toemailId = strtolower($userDetails['user_email']);
//    $userkey = USER_UserKey($conn);
//    $updateResult = USER_UpdateUserKey($conn, $sel_userId, $userkey);
//    $url = $base_url . 'reset-password.php?vid=' . $userkey;
//    $fromEmailId = 'support@nanoheal.com';
//    $mailType = UTIL_GetInteger('mailType', 10);
//
//    $result = USER_SendUserEmails($conn, $username, $toemailId, $fromEmailId, $mailType, $url, $language);
//    print_data($result);
//}

// new function CUSTAJX_ResendUserMail

function CUSTAJX_ResendUserMail($userMail)
{
  global $base_url;
  $conn = db_connect();
  $userInfo = nhUser::getUserInfo($userMail);
  if (isset($userInfo['user_email'])){
    $username = $userInfo['user_email'];
    $toemailId = $userInfo['user_email'];
    $fromEmailId = 'support@nanoheal.com';
    $userkey = USER_UserKey($conn);
    USER_UpdateUserKey($conn, $userInfo['userid'], $userkey);
    $url = $base_url . 'reset-password.php?vid=' . $userkey;
    $result = USER_SendUserEmails($conn, $username, $toemailId, $fromEmailId, 10, $url, '');
    return $result;
  }

}

function CUSTAJX_IsSiteExist()
{
    $conn = db_connect();
    $sitename = UTIL_GetString('siteName', '');
    $sql_ch = "select * from " . $GLOBALS['PREFIX'] . "agent.channel where companyName ='$sitename'";
    $res_core = find_one($sql_ch, $conn);
    if (is_array($res_core) && safe_count($res_core) > 0) {
        $msg = "TRUE";
        print_data($msg);
    } else {
        $msg = "FALSE";
        print_data($msg);
    }
}

function CUSTAJX_DoNotShowTrialPop()
{
    $conn = db_connect();
    $isChecked = UTIL_GetString('isChecked', '');
    $loggedEid = $_SESSION['user']['cId'];

    if ($isChecked == 'true') {
        $_SESSION["user"]["showAVTrial"] = 0;
        $_SESSION["user"]["showAVTrialButton"] = 1;
        $_SESSION["user"]["showAVBuy"] = 0;
        $showTrialBox = '1';
    } else {
        $_SESSION["user"]["showAVTrial"] = 1;
        $_SESSION["user"]["showAVTrialButton"] = 0;
        $_SESSION["user"]["showAVBuy"] = 0;
        $showTrialBox = '0';
    }

    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel SET showTrialBox = '$showTrialBox' WHERE eid = '$loggedEid'";
    $updateRes = redcommand($updateSql, $conn);
    $msg = "TRUE";
    print_data($msg);
}

function CUSTAJX_StartTrial()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];

    $startDate = time();
    $endDate = $startDate + 2592000;
    $roleId = USER_Role_Id(1, $conn);

    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel SET trialStartDate = '$startDate', trialEndDate = '$endDate', trialEnabled = '1',"
        . " showTrialBox = '1' WHERE eid = '$loggedEid'";
    $updateRes = redcommand($updateSql, $conn);

    $updateRoleSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET role_id = '$roleId' WHERE ch_id = '$loggedEid'";
    $updateRoleRes = redcommand($updateRoleSql, $conn);
    $_SESSION["user"]["showAVTrial"] = 0;
    $_SESSION["user"]["showAVTrialButton"] = 0;
    $_SESSION["user"]["showAVBuy"] = 1;
    $date = date("m/d/Y", $endDate);
    print_data($date);
}

function CUSTAJX_AlterTable($db, $dbName, $tableName, $columnName, $dataType, $defaultValue)
{
    $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$tableName' "
        . "AND COLUMN_NAME = '$columnName'";
    $result = find_one($sql, $db);

    if (is_array($result) && safe_count($result) > 0) {
        return true;
    } else {
        $alertSql = "ALTER TABLE " . $GLOBALS['PREFIX'] . "agent.$tableName ADD COLUMN $columnName $dataType NULL DEFAULT '$defaultValue'";
        $alterRes = redcommand($alertSql, $db);
        return true;
    }
}

function CUSTAJX_GetFilteredSiteName($tempSiteName, $customerNum)
{
    $res_site = preg_replace("/[^a-zA-Z0-9\s]/", "", $tempSiteName);
    $sitename = preg_replace('/\s+/', '_', $res_site);
    $sitename = trim($sitename) . '__' . trim($customerNum);
    return $sitename;
}

function CUSTAJX_GetTrimmedCompName($tempCompName)
{
    $companyName = '';
    if (strpos($tempCompName, "_") !== false) {
        $tempArray = explode("_", $tempCompName);
        if (is_numeric($tempArray[1])) {
            $companyName = $tempArray[0];
        } else {
            $companyName = $tempCompName;
        }
    } else {
        $companyName = $tempCompName;
    }
    return $companyName;
}

function hasNewLine($string)
{
    $found = false;
    foreach (array("\r", "\n", "\r\n", "\n\r") as $token) {
        if (strpos($string, $token) !== false) {
            $found = true;
            break;
        }
    }
    return $found;
}

function CUSTAJX_VerifyOTCDetails()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $resultArray = [];
    $selectedOTC = UTIL_GetString('selectedOTC', '');
    $otcOwnerDetails = RSLR_GetOTCDetails($conn, $loggedEid, $selectedOTC);
    $emailid = $otcOwnerDetails['emailId'];
    $companyName = $otcOwnerDetails['companyName'];
    $oldLicenseCnt = $otcOwnerDetails['licenseCnt'];
    $oldContractEndDate = strtotime($otcOwnerDetails['contractEndDate']);
    $latestOTCDetails = RSLR_GetOTCDetailsFromAvira($conn, $selectedOTC, $emailid, $companyName);
    $otcStatus = $latestOTCDetails['result']['status'];

    if ($otcStatus !== "ERROR") {
        $resultArray = CUSTAJX_UpdateOTCDetails($conn, $oldLicenseCnt, $oldContractEndDate, $otcOwnerDetails, $latestOTCDetails['result']);
    } else {
        $resultArray = array("status" => "ERROR", "message" => $latestOTCDetails['result']['message']);
    }
    print_json_data($resultArray);
}

function CUSTAJX_UpdateOTCDetails($conn, $oldLicenseCnt, $oldContractEndDate, $otcOwnerDetails, $latestOTCDetails)
{
    $resultArray = [];
    $latestContractEnds = strtotime($latestOTCDetails['expire_date']);
    $latestLicenseCount = $latestOTCDetails['users'];
    $ch_id = $otcOwnerDetails['ch_id'];

    if (($latestLicenseCount > $oldLicenseCnt) && ($latestContractEnds > $oldContractEndDate)) {

        RSLR_UpdateOTCLicenseCounts($conn, $otcOwnerDetails, $latestOTCDetails);
        RSLR_UpdateOTCLicenseDates($conn, $otcOwnerDetails, $latestOTCDetails);
        RSLR_CreateOTCHistory($conn, $otcOwnerDetails, $latestOTCDetails);
        $resultArray = array("status" => "SUCCESS", "message" => "License Count is updated from $oldLicenseCnt to $latestLicenseCount and Contract end date is updated to " . date("l, F d, Y h:i:s", $latestContractEnds));
        return $resultArray;
    }
    if ($latestLicenseCount > $oldLicenseCnt) {

        RSLR_UpdateOTCLicenseCounts($conn, $otcOwnerDetails, $latestOTCDetails);
        RSLR_CreateOTCHistory($conn, $otcOwnerDetails, $latestOTCDetails);
        $resultArray = array("status" => "SUCCESS", "message" => "License Count is updated from $oldLicenseCnt to $latestLicenseCount");
        return $resultArray;
    }
    if ($latestContractEnds > $oldContractEndDate) {

        RSLR_UpdateOTCLicenseDates($conn, $otcOwnerDetails, $latestOTCDetails);
        RSLR_CreateOTCHistory($conn, $otcOwnerDetails, $latestOTCDetails);
        $resultArray = array("status" => "SUCCESS", "message" => "Contract end date is updated to " . date("l, F d, Y h:i:s", $latestContractEnds));
        return $resultArray;
    }

    $resultArray = array("status" => "ERROR", "message" => "No updates found");
    return $resultArray;
}

function CUSTAJX_GetCRMDetails()
{
    $key = '';
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $customerType = $_SESSION['user']['customerType'];
    $crmDetails = CRM_GetCRMTypeView($key, $conn, $loggedEid, $customerType);

    if (is_array($crmDetails) && safe_count($crmDetails) > 0) {
        echo "configured";
    } else {
        echo "not configured";
    }
}

function CUSTAJX_SetCRMDetails()
{

    $key = '';
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $crmData['selectedCrm'] = url::issetInRequest('selectedCrm') ? url::requestToAny('selectedCrm') : "";
    $crmData['CRMlogin_value'] = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $crmData['custName'] = url::issetInRequest('custName') ? url::requestToAny('custName') : "";
    $crmData['custId'] = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $crmData['custSiteName'] = url::issetInRequest('custSiteName') ? url::requestToAny('custSiteName') : "";
    $crmData['crm_url'] = url::issetInRequest('crm_url') ? url::requestToAny('crm_url') : "";
    $crmData['crm_username'] = url::issetInRequest('crm_username') ? url::requestToAny('crm_username') : "";
    $crmData['crm_password'] = url::issetInRequest('crm_password') ? url::requestToAny('crm_password') : "";
    $crmData['crm_key'] = url::issetInRequest('crm_key') ? url::requestToAny('crm_key') : "";

    $result = CRM_UpdateCredentials($loggedEid, $conn, $crmData);
    print_json_data($result);
}

function CUSTAJX_ExportCRMDetails()
{
    $key = '';
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];

    $crmDetails = CRM_GetCRMType($key, $conn, $loggedEid);
    if (is_array($crmDetails) && safe_count($crmDetails) > 0) {
        $crmNotifs = CRM_GetCRMNotifications($key, $conn, $loggedEid);
        $objPHPExcel = CUSTAJX_Create_CRMDetailsSheet($crmDetails, $crmNotifs);
    }

    $fn = "Customer Details.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function CUSTAJX_Create_CRMDetailsSheet($crmDetails, $crmNotifs)
{
    global $crmList;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setTitle("CRM_Details");
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(50);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', "CRM System");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "CRM Url");
    $objPHPExcel->getActiveSheet()->setCellValue('A2', $crmList[$crmDetails['crmType']]);
    $objPHPExcel->getActiveSheet()->setCellValue('B2', $crmDetails['crmIP']);

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setTitle("Notifications_Details");
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "CRM System");

    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Notification Name");
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(60);
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Category");
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Subcategory");
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Priority");
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Enabled");
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "State");
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    if ($crmDetails['crmType'] == "CW") {
        $objPHPExcel->getActiveSheet()->setCellValue('G1', "Services");
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    }

    if (is_array($crmNotifs) && safe_count($crmNotifs) > 0) {
        $index = 2;
        foreach ($crmNotifs as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $result['notifName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $result['category']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $result['subcategory']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $result['priority']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, ($result['enabled'] == 1) ? "Enabled" : "Disabled");
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $result['state']);

            if ($crmDetails['crmType'] == "CW") {
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $result['services']);
            }
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A2', "No notifications are configured yet");
    }
    return $objPHPExcel;
}

function CRM_GetNotificationsList()
{
    $response = CRM_GetNotifications();
    print_json_data($response);
}

function GET_notificationListData()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $configData['Nid'] = url::issetInRequest('Nid') ? url::requestToAny('Nid') : "";
    $configData['CRMlogin_value'] = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $configData['custId'] = url::issetInRequest('custId') ? url::requestToAny('custId') : "";

    $response = CRM_GetNotificationsList_Details($configData, $db);
    print_json_data($response);
}

function Get_NotificationForm()
{
    $getForm = View_NotificationForm();
    print_data($getForm);
}

function GET_selectedNotifications()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $selectedNIDlists = isset($_POST['selectedNIDlists']) ? $_POST['selectedNIDlists'] : "";
    $response = GET_selectedNotificationsLists($selectedNIDlists, $db);
    print_json_data($response);
}

function CRM_AddCategory()
{

    $CategoryData = url::issetInPost('CategoryData') ? url::postToAny('CategoryData') : "";
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $response = Add_CRMCategory($CategoryData, $db);
    return $response;
}

function CRM_AddSubCategory()
{
    $SubCategoryData = url::issetInPost('SubCategoryData') ? url::postToAny('SubCategoryData') : "";
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $response = Add_CRMSubCategory($SubCategoryData, $db);
    return $response;
}

function Get_Category()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $CRMlogin_value = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $custId = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $response = GET_CRMCategory($db, $CRMlogin_value, $custId);
    print_json_data($response);
}

function Get_Services()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $CRMlogin_value = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $custId = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $response = GET_CRMServices($db, $CRMlogin_value, $custId);
    print_json_data($response);
}

function Get_SubCategory()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $CRMlogin_value = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $custId = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $response = GET_CRMSubCategory($db, $CRMlogin_value, $custId);
    print_json_data($response);
}

function CRMconfigure()
{
    $configurationData = url::issetInRequest('configurationData') ? url::requestToAny('configurationData') : "";
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $response = configureCRM($configurationData, $db);
    echo json_encode($response);
}

function CRM_AddServices()
{
    $servicesData = url::issetInPost('crmServicesData') ? url::postToAny('crmServicesData') : "";
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $response = AddCRMservices($servicesData, $db);
}

function CUSTAJX_GetCRMcustomers()
{
    $conn = db_connect();

    $loggedEid = $_SESSION['user']['cId'];
    $CustomerType = '5';

    $crmcustomDetails = CRM_GetResellerCustomers($CustomerType, $conn, $loggedEid);
}

function CUSTAJX_GetSitelist()
{

    $db = db_connect();
    $custType = $_SESSION['user']['customerType'];
    $cid = $_SESSION['user']['cId'];
    $result = CRMDEtails_Getsitelists($db, $custType, $cid);
}

function CUSTAJX_GetSitelistConfigured()
{

    $db = db_connect();
    $custType = $_SESSION['user']['customerType'];
    $cid = $_SESSION['user']['cId'];
    $custSiteName = url::issetInRequest('custSiteName') ? url::requestToAny('custSiteName') : "";
    $result = CRMDEtails_Getsitelists_Configured($custSiteName, $db, $custType, $cid);
}

function CUSTAJX_GetCRMcustSiteList()
{
    $Cid = url::issetInRequest('Cid') ? url::requestToAny('Cid') : "";

    $conn = db_connect();

    $crmcustomSites = CRM_GetReseller_CustomersSites($conn, $Cid);
}

function CUSTAJX_GetCRMcrmDtlSiteList()
{
    $Cid = url::issetInRequest('Cid') ? url::requestToAny('Cid') : "";

    $conn = db_connect();

    $crmcustomSites = CRM_GetcrmDetails_CustomersSites($conn, $Cid);
    echo json_encode($crmcustomSites);
}

function CUSTAJX_GetSummary()
{
    $Cid = $_SESSION['user']['cId'];
    $ctype = $_SESSION['user']['customerType'];

    $conn = db_connect();

    $crmcustomSites = CRMDTLS_GetummaryDtls($conn, $Cid, $ctype);
    echo json_encode($crmcustomSites);
}

function GET_TicketLists_Onchange()
{
    $Cid = $_SESSION['user']['cId'];
    $siteName = url::issetInRequest('siteName') ? url::requestToAny('siteName') : "";

    $db = db_connect();

    $crmcustomSites = CRM_GetSiteDataList($siteName, $db, $Cid);
    print_json_data($crmcustomSites);
}

function GET_Configurations()
{
    $Cid = $_SESSION['user']['cId'];
    $selectedDataId = url::issetInRequest('selectedDataId') ? url::requestToAny('selectedDataId') : "";

    $conn = db_connect();

    $configs = CRMDTLS_editconfigs($selectedDataId, $conn, $Cid);
    echo json_encode($configs);
}

function CUSTAJX_updateConfigs()
{
    $Cid = $_SESSION['user']['cId'];
    $selectedChks = url::issetInRequest('selectedChks') ? url::requestToAny('selectedChks') : "";
    $editID_configs = url::issetInRequest('editID_configs') ? url::requestToAny('editID_configs') : "";

    $conn = db_connect();

    $configs = CRMDTLS_updateconfigs($selectedChks, $editID_configs, $conn, $Cid);
    echo $configs;
}

function CUSTAJX_GetCRM_singlecustSite()
{

    $Cid = url::issetInRequest('Cid') ? url::requestToAny('Cid') : "";

    $conn = db_connect();

    $crmcustomSites = CRM_GetReseller_singleCustomerSite($conn, $Cid);
}

function CUSTAJX_getCRM_DataMapping()
{
    $conn = db_connect();

    $crmType = url::issetInRequest('selectedCrm') ? url::requestToAny('selectedCrm') : "";
    $CRMlogin_value = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $custId = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $custSiteName = url::issetInRequest('custSiteName') ? url::requestToAny('custSiteName') : "";
    $custName = url::issetInRequest('custName') ? url::requestToAny('custName') : "";

    if ($CRMlogin_value == '2') {
        $custId = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    } elseif ($CRMlogin_value == '5') {
        $custId = $_SESSION['user']['cId'];
    }

    $mapformUI = get_CRMMapUI($crmType, $conn, $custId);
    echo json_encode($mapformUI);
}

function CUSTAJX_getskippedCRM_DataMapping()
{

    $conn = db_connect();

    $crmData['crmType'] = url::issetInRequest('selectedCrm') ? url::requestToAny('selectedCrm') : "";
    $crmData['CRMlogin_value'] = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $crmData['custId'] = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $crmData['custSiteName'] = url::issetInRequest('custSiteName') ? url::requestToAny('custSiteName') : "";
    $crmData['custName'] = url::issetInRequest('custName') ? url::requestToAny('custName') : "";
    $crmData['custId'] = url::issetInRequest('custId') ? url::requestToAny('custId') : "";

    $mapformUI = get_SkippedMapUI($crmData, $conn);
    echo json_encode($mapformUI);
}

function getDatname_Lists()
{

    $customerId = url::issetInRequest('customerId') ? url::requestToAny('customerId') : "";
    $conn = db_connect();
    $crmcustomSites = CRM_GetDataLists($conn, $customerId);
    echo json_encode($crmcustomSites);
}

function getNHDatname_Lists()
{
    $customerId = url::issetInRequest('customerId') ? url::requestToAny('customerId') : "";
    $categoryId = url::issetInRequest('categoryId') ? url::requestToAny('categoryId') : "";
    $conn = db_connect();
    $crmcustomSites = CRM_GetNHDataLists($conn, $customerId, $categoryId);
}

function getSNDatname_Lists()
{

    $customerId = url::issetInRequest('customerId') ? url::requestToAny('customerId') : "";
    $categoryId = url::issetInRequest('categoryId') ? url::requestToAny('categoryId') : "";
    $conn = db_connect();
    $crmcustomSites = CRM_GetSNDataLists($conn, $customerId, $categoryId);
}

function config_NewDataNames()
{

    $configData['customerId'] = url::issetInRequest('customerId') ? url::requestToAny('customerId') : "";
    $configData['categoryId'] = url::issetInRequest('categoryId') ? url::requestToAny('categoryId') : "";
    $configData['CRMlogin_value'] = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $configData['categoryName'] = url::issetInRequest('categoryName') ? url::requestToAny('categoryName') : "";
    $configData['NH_DataId'] = url::issetInRequest('NH_DataId') ? url::requestToAny('NH_DataId') : "";
    $configData['NH_DataName'] = url::issetInRequest('NH_DataName') ? url::requestToAny('NH_DataName') : "";
    $configData['SN_DataVal'] = url::issetInRequest('SN_DataVal') ? url::requestToAny('SN_DataVal') : "";
    $configData['SN_DataName'] = url::issetInRequest('SN_DataName') ? url::requestToAny('SN_DataName') : "";
    $conn = db_connect();
    $crmcustomSites = CRM_congifNewdataLists($conn, $configData);
}

function config_editedDataNames()
{

    $configData['DMedit_id'] = url::issetInRequest('DMedit_id') ? url::requestToAny('DMedit_id') : "";
    $configData['customerId'] = url::issetInRequest('customerId') ? url::requestToAny('customerId') : "";
    $configData['categoryId'] = url::issetInRequest('categoryId') ? url::requestToAny('categoryId') : "";
    $configData['categoryName'] = url::issetInRequest('categoryName') ? url::requestToAny('categoryName') : "";
    $configData['NH_DataId'] = url::issetInRequest('NH_DataId') ? url::requestToAny('NH_DataId') : "";
    $configData['NH_DataName'] = url::issetInRequest('NH_DataName') ? url::requestToAny('NH_DataName') : "";
    $configData['SN_DataVal'] = url::issetInRequest('SN_DataVal') ? url::requestToAny('SN_DataVal') : "";
    $configData['SN_DataName'] = url::issetInRequest('SN_DataName') ? url::requestToAny('SN_DataName') : "";
    $conn = db_connect();
    echo $crmcustomSites = CRM_congifEditdataLists($conn, $configData);
}

function unconfigure_Dataid()
{

    $id = url::issetInRequest('id') ? url::requestToAny('id') : "";
    $conn = db_connect();
    $res = CRM_unconfigdataList($conn, $id);
    echo $res;
}

function EDIT_DataMapValues()
{
    $id = url::issetInRequest('id') ? url::requestToAny('id') : "";
    $conn = db_connect();
    $res = CRM_EditdataListValue($conn, $id);
    echo $res;
}

function CUSAJX_evenDeletetItem()
{
    $conn = db_connect();
    $deletedataID = url::requestToAny('deletedataID');
    try {

        $query = "delete from " . $GLOBALS['PREFIX'] . "dashboard.EventItems where eventitemid = '" . $deletedataID . "' ";
        $result = redcommand($query, $conn);
        if ($result) {
            $query_1 = "delete from " . $GLOBALS['PREFIX'] . "dashboard.Criteria where itemid = '" . $deletedataID . "' ";
            $result_1 = redcommand($query_1, $conn);
            echo "success";
        } else {
            echo "Failed";
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSTAJX_SetCRMDetailsComp()
{
    $db = db_connect();

    $configData['CRMlogin_value'] = url::issetInRequest('CRMlogin_value') ? url::requestToAny('CRMlogin_value') : "";
    $configData['custName'] = url::issetInRequest('custName') ? url::requestToAny('custName') : "";
    $configData['custId'] = url::issetInRequest('custId') ? url::requestToAny('custId') : "";
    $configData['custSiteName'] = url::issetInRequest('custSiteName') ? url::requestToAny('custSiteName') : "";
    $configData['crmjsonData'] = url::issetInRequest('crmjsonData') ? url::requestToAny('crmjsonData') : "";
    $configData['crmClosejsonData'] = url::issetInRequest('crmClosejsonData') ? url::requestToAny('crmClosejsonData') : "";
    $configData['ticketType'] = url::issetInRequest('ticketType') ? url::requestToAny('ticketType') : "";

    $conn = db_connect();
    echo $crmcustomSites = CRM_conguredata($db, $configData);
}

function CUSTAJAX_ExportTickets()
{

    $siteName = url::issetInRequest('siteName') ? url::requestToAny('siteName') : "";
    $startDate = url::issetInRequest('startDate') ? url::requestToAny('startDate') : "";
    $endDate = url::issetInRequest('endDate') ? url::requestToAny('endDate') : "";
    $custType = $_SESSION['user']['customerType'];
    $cid = $_SESSION['user']['cId'];
    $db = db_connect();
    $result = CRM_exportTicketdtls($siteName, $startDate, $endDate, $custType, $cid, $db);
}

function GET_TicketLists()
{
    $custType = $_SESSION['user']['customerType'];
    $cid = $_SESSION['user']['cId'];
    $siteName = url::issetInRequest('siteName') ? url::requestToAny('siteName') : "";
    $db = db_connect();
    $result = CRM_GetTicketListData($custType, $cid, $siteName, $db);
    print_json_data($result);
}

function CustjsonViewEdit()
{

    $id = url::issetInRequest('selectedDataId') ? url::requestToAny('selectedDataId') : "";
    $db = db_connect();
    $result = CRM_GetJsonData($id, $db);
    echo $result;
}

function CustjsoncloseViewEdit()
{

    $id = url::issetInRequest('selectedDataId') ? url::requestToAny('selectedDataId') : "";
    $db = db_connect();
    $result = CRM_GetCloseJsonData($id, $db);
    echo $result;
}

function CUST_getSitename()
{

    $id = url::issetInRequest('selectedDataId') ? url::requestToAny('selectedDataId') : "";
    $db = db_connect();
    $result = CRM_GetSiteNameJsonData($id, $db);
    echo $result;
}

function CUST_UpdateJson()
{

    $siteName = url::issetInRequest('selectedSiteName') ? url::requestToAny('selectedSiteName') : "";
    $jsonData = url::issetInRequest('jsonData') ? url::requestToAny('jsonData') : "";
    $db = db_connect();
    $result = CRM_UpdateSiteNameJsonData($siteName, $jsonData, $db);
    echo $result;
}

function CUST_UpdateCloseJson()
{

    $siteName = url::issetInRequest('selectedSiteName') ? url::requestToAny('selectedSiteName') : "";
    $jsonData = url::issetInRequest('jsonData') ? url::requestToAny('jsonData') : "";
    $db = db_connect();
    $result = CRM_UpdateSiteNameCloseJsonData($siteName, $jsonData, $db);
    echo $result;
}

function CUSTAJX_GetJsonDataC()
{

    $customerSite = url::issetInRequest('customerSite') ? url::requestToAny('customerSite') : "";
    $db = db_connect();
    $result = CRM_SiteJsonData($customerSite, $db);
    print_r($result);
    die();
}

function CUST_comppayload()
{

    $selectedDataTeid = url::issetInRequest('selectedDataTeid') ? url::requestToAny('selectedDataTeid') : "";
    $db = db_connect();
    $result = CRM_getPayoloadJsonData($selectedDataTeid, $db);
    print_json_data($result);
}

function CustViewAction()
{
    $selectedDataTeid = url::issetInRequest('selectedDataId') ? url::requestToAny('selectedDataId') : "";
    $db = db_connect();
    $result = CRM_getActonDetails($selectedDataTeid, $db);
    print_json_data($result);
}

function CUSTAJX_ResetPassword()
{
    global $base_url;
    $pdo = NanoDB::connect();
    $pwd = url::postToAny('password');
    $pwdhash = password_hash($pwd, PASSWORD_DEFAULT);
    $oldpass = url::postToAny('oldpass');
    $oldpwd = $oldpass;
    $sel_userId = $_SESSION["user"]["userid"];
    $timestamp = time();
    $timestamp1 = strtotime('+90 day', $timestamp);
    $checkPwdSql = $pdo->prepare("SELECT password, passwordHistory,username,firstName,lastName FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = ? limit 1");
    $checkPwdSql->execute([$sel_userId]);
    $checkPwdRes = $checkPwdSql->fetch(PDO::FETCH_ASSOC);
    if (is_array($checkPwdRes) && safe_count($checkPwdRes) > 0) {
        $currentPwd = $checkPwdRes["password"];
        $pwdHistory = $checkPwdRes["passwordHistory"];
        $pwdHistArray = explode(',', $pwdHistory);
        $fname = $checkPwdRes['firstName'];
        $lname = $checkPwdRes['lastName'];
        if (password_verify($oldpwd, $currentPwd)) {
            if (safe_count($pwdHistArray) > 4) {
                unset($pwdHistArray[0]);
                $pwdHistory = implode(",", $pwdHistArray);
            }

            if (password_verify($pwd, $currentPwd)) {
                echo 2;
                $tempp = 2;
            }

            if ($fname != '' && $tempp != 2) {
                if (preg_match("/$fname/i", $pwd)) {
                    echo 4;
                    $tempf = 4;
                }
            }
            if ($lname != '' && $tempp != 2) {
                if ((strlen($lname) >= 1) && preg_match("/$lname/i", $pwd)) {
                    echo 5;
                    $templ = 5;
                }
            }

            if (($currentPwd != $pwdhash) && ($tempf != 4) && ($templ != 5)) {
                $updSql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET password = ?, passwordDate = ? where userid = ?");
                $resetRes = $updSql->execute([$pwdhash, $timestamp1, $sel_userId]);
                if ($resetRes) {
                    echo 1;
                } else {
                    echo 0;
                }
            }
        } else {
            echo 6;
        }
    }
}

function getwsurl()
{
    $pdo = NanoDB::connect();

    $confstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = ? limit 1");
    $confstmt->execute(['dashboard_config']);
    $confres = $confstmt->fetch(PDO::FETCH_ASSOC);
    $confdata = safe_json_decode($confres['value'], true);
    $wsurl = $confdata['wsurl'];

    echo $wsurl;
}

function CUST_TimeZones()
{
    $timezones = array(
        'Pacific/Midway' => "(GMT-11:00) Midway Island",
        'US/Hawaii' => "(GMT-10:00) Hawaii",
        'US/Alaska' => "(GMT-09:00) Alaska",
        'US/Pacific' => "(GMT-08:00) Pacific Time (US &amp; Canada)",
        'US/Mountain' => "(GMT-07:00) Mountain Time (US &amp; Canada)",
        'America/Mexico_City' => "(GMT-06:00) Mexico City",
        'US/Eastern' => "(GMT-05:00) Eastern Time (US &amp; Canada)",
        'America/Caracas' => "(GMT-04:30) Caracas",
        'Canada/Atlantic' => "(GMT-04:00) Atlantic Time (Canada)",
        'Canada/Newfoundland' => "(GMT-03:30) Newfoundland",
        'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
        'Atlantic/Stanley' => "(GMT-02:00) Stanley",
        'Atlantic/Azores' => "(GMT-01:00) Azores",
        'Europe/London' => "(GMT) London",
        'Europe/Amsterdam' => "(GMT+01:00) Amsterdam",

        'Europe/Athens' => "(GMT+02:00) Athens",

        'Asia/Baghdad' => "(GMT+03:00) Baghdad",

        'Asia/Tehran' => "(GMT+03:30) Tehran",
        'Asia/Baku' => "(GMT+04:00) Baku",

        'Asia/Kabul' => "(GMT+04:30) Kabul",
        'Asia/Karachi' => "(GMT+05:00) Karachi",
        'Asia/Kolkata' => "(GMT+05:30) Kolkata",
        'Asia/Kathmandu' => "(GMT+05:45) Kathmandu",
        'Asia/Yekaterinburg' => "(GMT+06:00) Ekaterinburg",
        'Asia/Novosibirsk' => "(GMT+07:00) Novosibirsk",
        'Asia/Krasnoyarsk' => "(GMT+08:00) Krasnoyarsk",

        'Asia/Tokyo' => "(GMT+09:00) Tokyo",
        'Australia/Adelaide' => "(GMT+09:30) Adelaide",
        'Asia/Yakutsk' => "(GMT+10:00) Yakutsk",

        'Asia/Vladivostok' => "(GMT+11:00) Vladivostok",
        'Asia/Magadan' => "(GMT+12:00) Magadan",
    );
    $str = "";
    foreach ($timezones as $key => $value) {
        $str .= CUSTAJX_CreatSelectedOption('"' . $key . '"', $value, "");
    }
    $str = trim($str);

    print_data($str);
}

function CUSAJX_GetAllUser_Role()
{
    $str = "";
    $conn = NanoDB::connect();
    $allRoles = USER_GetAllRoles($conn);
    foreach ($allRoles as $role) {
        $roleId = $role['assignedRole'];
        $str .= CUSTAJX_CreatSelectedOption('"' . $roleId . '"', $role['displayName'], "");
    }
    $str = trim($str);

    print_data($str);
}
