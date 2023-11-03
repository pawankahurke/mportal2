<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-util.php';
include_once '../lib/l-profilewizard.php';
include_once '../lib/l-customer.php';
include_once '../lib/l-reseller.php';
include_once '../lib/l-user.php';
include_once '../lib/l-sqlitedb.php';
include_once '../lib/l-crmdetls.php';
include_once '../lib/l-provElastic.php';
include_once '../lib/l-dashboardAPI.php';
include_once '../include/common_functions.php';




//Replace $routes['post'] with if else
if (url::postToText('function') === 'AJAX_GetMachineList') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_GetMachineList();
} else if (url::postToText('function') === 'MSP_Create_Site') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    MSP_Create_Site();
} else if (url::postToText('function') === 'AJAX_get_UserDartDetails') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_get_UserDartDetails();
} else if (url::postToText('function') === 'AJAX_Audit_GridData') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_Audit_GridData();
} else if (url::postToText('function') === 'AJAX_get_LoginDetails') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_get_LoginDetails();
} else if (url::postToText('function') === 'AJAX_Get_SelectedSitesMachines') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_Get_SelectedSitesMachines();
} else if (url::postToText('function') === 'AJAX_DEPL_AddSubnetId') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_DEPL_AddSubnetId();
} else if (url::postToText('function') === 'AJAX_UpdateImpersonationCreds') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_UpdateImpersonationCreds();
} else if (url::postToText('function') === 'AJAX_ImpersonationCreds') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_ImpersonationCreds();
} else if (url::postToText('function') === 'AJAX_GetImpersonationCreds') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_GetImpersonationCreds();
} else if (url::postToText('function') === 'AJAX_CheckDeployScan') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_CheckDeployScan();
} else if (url::postToText('function') === 'AJAX_ResetDeployScan') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_ResetDeployScan();
} else if (url::postToText('function') === 'Ajax_DeploymentSubnetDetails') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    Ajax_DeploymentSubnetDetails();
} else if (url::postToText('function') === 'AJAX_Get_RightPane') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_Get_RightPane();
} else if (url::postToText('function') === 'AJAX_Update_Session') { // roles: any
    AJAX_Update_Session();
} else if (url::postToText('function') === 'AJAX_GetMachOnlineStatus') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_GetMachOnlineStatus();
} else if (url::postToText('function') === 'AJAX_GetGroupInfo') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    AJAX_GetGroupInfo();
} else if (url::postToText('function') === 'returnSearchType') { // roles: site
    nhRole::dieIfnoRoles(['site']); // roles: site
    returnSearchType();
}


//Replace $routes['get'] with if else
if (url::postToText('function') === 'AJAX_GetGroupMachineList') { // roles: site
    AJAX_GetGroupMachineList();
} else if (url::postToText('function') === 'AJAX_get_CustomerDetails') { // roles: site
    AJAX_get_CustomerDetails();
} else if (url::postToText('function') === 'Ajax_audit_Data') { // roles: site
    Ajax_audit_Data();
} else if (url::postToText('function') === 'AJAX_get_UserDetails') { // roles: site
    AJAX_get_UserDetails();
} else if (url::getToText('function') === 'AJAX_export_LoginRangeDetails') { // roles: site
    AJAX_export_LoginRangeDetails();
} else if (url::postToText('function') === 'AJAX_get_LoginRangeDetails') { // roles: site
    AJAX_get_LoginRangeDetails();
} else if (url::postToText('function') === 'AJAX_getImporsonationDetails') { // roles: site
    AJAX_getImporsonationDetails();
} else if (url::postToText('function') === 'AJAX_GetDeploymentLeftList') { // roles: site
    AJAX_GetDeploymentLeftList();
} else if (url::postToText('function') === 'AJAX_GetDeploymentRightList') { // roles: site
    AJAX_GetDeploymentRightList();
} else if (url::postToText('function') === 'AJAX_DEPL_CheckSubnetVlues') { // roles: site
    AJAX_DEPL_CheckSubnetVlues();
} else if (url::postToText('function') === 'AJAX_DEPL_CheckScanJob') { // roles: site
    AJAX_DEPL_CheckScanJob();
} else if (url::postToText('function') === 'AJAX_DEPL_DeployJob') { // roles: site
    AJAX_DEPL_DeployJob();
} else if (url::postToText('function') === 'AJAX_DEPL_DeployJobConfirm') { // roles: site
    AJAX_DEPL_DeployJobConfirm();
} else if (url::postToText('function') === 'Ajax_DeploymentAuditGrid') { // roles: site
    Ajax_DeploymentAuditGrid();
} else if (url::postToText('function') === 'AJAX_DEPL_Get_ExportDetails') { // roles: site
    AJAX_DEPL_Get_ExportDetails();
} else if (url::postToText('function') === 'Ajax_DeploymentAuditDetailsGrid') { // roles: site
    Ajax_DeploymentAuditDetailsGrid();
} else if (url::getToText('function') === 'AJAX_exportsite') { // roles: site
    AJAX_exportsite();
} else if (url::postToText('function') === 'AJAX_SearchMachineDetail') { // roles: site
    AJAX_SearchMachineDetail();
} else if (url::getToText('function') === 'AJAX_Export_DartAudit') { // roles: site
    AJAX_Export_DartAudit();
} else if (url::postToText('function') === 'AJAX_expunge') { // roles: site
    AJAX_expunge_func();
}


function MSP_GetAvailableLicenseCount()
{
    $loggedEid = $_SESSION["user"]["cId"];
    $conn = db_connect();
    $today = time();

    $licensesDetails = MSP_GetAvailableLicenses($conn, $today, $loggedEid);
    return json_encode($licensesDetails);
}



function MSP_GetResellerGrid()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];

    $result = MSP_GetMSPResellers($conn, $loggedEid);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatResellerGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_GetSitesGrid()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];

    $result = MSP_GetMSPSites($conn, $loggedEid);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatSitesGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_CreateReseller()
{
    $key = '';
    $conn = db_connect();
    $fullname = UTIL_GetString('reselFirstName', "");
    $lname = UTIL_GetString('reselLastName', "");
    $companyname = UTIL_GetString('reselCompName', "");
    $emailid = UTIL_GetString('reselEmail', "");
    $compAddr = UTIL_GetString('reselCompAddr', "");
    $compCity = UTIL_GetString('reselCompCity', "");
    $compState = UTIL_GetString('reselCompState', "");
    $zipcode = UTIL_GetString('reselCompZipcode', "");
    $website = UTIL_GetString('reselCompWebsite', "");
    $language = UTIL_GetString('language', "en");

    $retVal = RSLR_AddMSPReseller($key, $conn, $fullname, $lname, $companyname, $emailid, $compAddr, $compCity, $compState, $zipcode, $website, 'dashboard', $language);
    echo json_encode($retVal);
}

function MSP_Create_Site()
{
    global $base_url;
    $key = '';
    $pdo = pdo_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $sitename = UTIL_GetString('sitename', "");
    $pccount = 0;
    $agentEmail = $_SESSION["user"]["adminEmail"];
    $custNo = date("Y") . '000' . $loggedEid;
    $sitename = MSP_GetFilteredSiteName($sitename, $custNo);
    $isSiteNameExist = RSLR_IsSiteNameExist($pdo, $sitename);

    if ($isSiteNameExist == "EXIST") {
        echo json_encode(array("status" => "error", "msg" => 'Site name already exist'));
        exit();
    }

    $customerDetails = RSLR_Entity_Dtl($loggedEid);
    $fname = $customerDetails['firstName'];
    $lname = $customerDetails['lastName'];
    $email = $customerDetails['emailId'];
    $name = $customerDetails['companyName'];
    $entyId = $customerDetails['entityId'];
    $chnlId = $customerDetails['channelId'];

    $provision_urlStr = MSP_Create_CustomerSiteOrder($pdo, $fname, $lname, $email, '', $loggedEid, $name, $chnlId, $trialSite, '', $pccount, $agentEmail, $sitename);

    if ($provision_urlStr != "NOTDONE") {
        MSP_assignSites($key, $sitename, $entyId, $chnlId, $loggedEid, $pdo);

        $downLoadUrl = $base_url . 'eula.php?id=' . $provision_urlStr;
        $retVal = array("status" => "success", "msg" => 'Site created successfully', "link" => $downLoadUrl);
    } else {
        $retVal = array("status" => "error", "msg" => 'Fail to create account. Please try again.');
    }
    return json_encode($retVal);
}

function MSP_assignSites($key, $siteName, $entityId, $channelId, $cid, $pdo)
{
    try {
        $ctype = $_SESSION["user"]["customerType"];
        $stmt = $pdo->prepare("select id,username,customer from " . $GLOBALS['PREFIX'] . "core.Customers where customer= '$siteName' limit 1");
        $stmt->execute([$siteName]);
        $serl_res = $stmt->fetch(PDO::FETCH_ASSOC);

        if (safe_count($serl_res) > 0) {
        } else {

            if ($entityId != 0) {

                $entty_stmt = $pdo->prepare("select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id = ?");
                $entty_stmt->execute([$entityId]);
                $ent_res = $entty_stmt->fetchAll(PDO::FETCH_ASSOC);
                if (safe_count($ent_res) > 0) {
                    foreach ($ent_res as $entValue) {
                        $addEnSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES (?, ?, ?, ?)");
                        $result1 = $addEnSite->execute([$entValue['username'], $siteName, 0, 0]);
                    }
                }
            }

            if ($channelId != 0) {

                $sqlChannel = $pdo->prepare("select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id = ?");
                $sqlChannel->execute([$channelId]);
                $chnl_res = $sqlChannel->fetchAll(PDO::FETCH_ASSOC);
                if (safe_count($chnl_res) > 0) {
                    foreach ($chnl_res as $chnlValue) {
                        $addEnSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES (?, ?, ?, ?)");
                        $result1 = $addEnSite->execute([$chnlValue['username'], $siteName, 0, 0]);
                    }
                }
            }

            if ($cid != 0) {
                $sqlCustmr = $pdo->prepare("select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id = ?");
                $sqlCustmr->execute([$cid]);
                $custmr_res = $sqlCustmr->fetchAll(PDO::FETCH_ASSOC);
                if (safe_count($custmr_res) > 0) {
                    foreach ($custmr_res as $value) {

                        $addcustSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES (?, ?, ?, ?)");
                        $result1 = $addcustSite->execute([$value['username'], $siteName, 0, 1]);
                    }
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}



function MSP_GetDatatableSqlLimit()
{
    $start = UTIL_GetString('start', '1');
    $length = UTIL_GetString('length', '10');
    $limit = '';

    if ($length == -1) {
        $limit = ' LIMIT ' . $start . ' 10';
    } else {
        $limit = ' LIMIT ' . $start . ' ' . $length;
    }
    return $limit;
}


function MSP_CreatPTag($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}


function MSP_CreateCheckBox($value, $className)
{
    $str = '<div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" class="check ' . $className . '" name="' . $value . '" id="' . $value . '" value="' . $value . '">
                        <span class="checkbox-material">
                            <span class="check">
                            </span>
                        </span>
                    </label>
                </div>
            </div>';
    return $str;
}


function MSP_CreatSelectOption($value, $lable, $selected)
{
    $optionStr = '<option value="' . trim($value) . '" ' . $selected . '>' . trim($lable) . '</option>';
    return $optionStr;
}


function MSP_GetValue($value, $default)
{
    $return = '';
    if ($value === "" || $value === "NULL" || $value === "undefined" || $value === undefined || $value === NULL) {
        $return = $default;
    } else {
        $return = $value;
    }
    return trim($return);
}


function MSP_GetCustomerStatus($status)
{
    if ($status == 1 || $status == '1') {
        $str = 'Enabled';
    } else {
        $str = 'Disabled';
    }
    return $str;
}

function CUST_Get_MSPRoleItems($db)
{
    $res_pro = CUST_GetOptionsData($db, '11', 'process_data');
    $provVal = $res_pro['value'];
    $roleItems = explode(",", $provVal);
    return $roleItems;
}

function MSP_GetFilteredSiteName($tempSiteName, $customerNum)
{
    $res_site = preg_replace("/[^a-zA-Z0-9\s]/", "", $tempSiteName);
    $sitename = preg_replace('/\s+/', '_', $res_site);
    $sitename = trim($sitename) . '__' . trim($customerNum);
    return $sitename;
}

function MSP_CreateHeadersForExcel($objPHPExcel, $headers, $activeSheet)
{
    try {
        $i = 65;
        $objPHPExcel->setActiveSheetIndex($activeSheet);
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        foreach ($headers as $width => $value) {
            $columnLetter = chr($i);
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnLetter)->setWidth($width);
            $objPHPExcel->getActiveSheet()->setCellValue($columnLetter . '1', $value);
            $i++;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}




function MSP_GetMSPResellers($db, $loggedEid)
{

    $customerType = $_SESSION["user"]["customerType"];
    if ($customerType == 0) {
        $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '2'";
    } else {
        $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE entityId = '$loggedEid' AND ctype = '2'";
    }
    mysql_query("SET CHARACTER SET utf8 ");
    $res = find_many($sql, $db);
    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}



function MSP_FormatResellerGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $customer = MSP_CreatPTag($value['companyName']);
        $firstName = MSP_CreatPTag($value['firstName']);
        $lastName = MSP_CreatPTag($value['lastName']);
        $email = MSP_CreatPTag($value['emailId']);
        $status = MSP_GetCustomerStatus($value['status']);
        $status = MSP_CreatPTag($status);

        $rowId = $value['status'] . '---' . $value['eid'] . '---' . $value['customerNo'];
        $array[] = array(
            "DT_RowId" => $rowId,
            'reseller' => $customer,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'status' => $status
        );
    }
    return $array;
}

function MSP_ExportAllReseller()
{
    $conn = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $username = $_SESSION["user"]["logged_username"];

    $result = MSP_GetMSPResellers($conn, $loggedEid);
    $headers = array(30 => "Reseller", 31 => "First Name", 32 => "Last Name", 33 => "Reseller Email", 29 => "Status");

    $objPHPExcel = new PHPExcel();
    $objPHPExcel = MSP_CreateHeadersForExcel($objPHPExcel, $headers, 0);
    $objPHPExcel = MSP_FormatResellerExcel($objPHPExcel, $result);

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $username . '_Resellers.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}


function MSP_FormatResellerExcel($objPHPExcel, $resultArray)
{
    $index = 2;
    foreach ($resultArray as $key => $value) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['companyName']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['firstName']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['lastName']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['emailId']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, MSP_GetCustomerStatus($value['status']));
        $index++;
    }

    return $objPHPExcel;
}



function MSP_GetOrdersGrid()
{
    $conn = db_connect();
    $customerType = $_SESSION["user"]["customerType"];

    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $key = '';

    $result = MSP_Get_MSPOrdersGrid($conn, $loggedEid, $customerType);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatOrderGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}


function MSP_Get_MSPOrdersGrid($conn, $loggedEid, $customerType)
{
    if ($customerType == 2) {
        $where = "WHERE O.chnl_id=C.eid AND (O.chnl_id='$loggedEid' OR O.chnl_id IN (SELECT eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE channelId = '$loggedEid' AND ctype = 5))";
    } else if ($customerType == 5) {
        $where = "WHERE O.chnl_id=C.eid AND O.chnl_id='$loggedEid'";
    } else if ($customerType < 2) {
        $where = '';
    }

    $sql = "SELECT O.orderNum, O.purchaseDate, O.licenseCnt, O.installCnt, O.contractEndDate FROM ";
    $sql .= "agent.orderDetails O, " . $GLOBALS['PREFIX'] . "agent.channel C $where";
    $result = find_many($sql, $conn);
    return $result;
}


function MSP_FormatOrderGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {

        $order = MSP_CreatPTag($value['orderNum']);
        if ($value['purchaseDate'] > 0) {
            $purchaseDate = MSP_CreatPTag(date("m-d-Y", $value['purchaseDate']));
        } else {
            $purchaseDate = MSP_CreatPTag("");
        }
        if ($value['contractEndDate'] > 0) {
            $expireDate = MSP_CreatPTag(date("m-d-Y", $value['contractEndDate']));
        } else {
            $expireDate = MSP_CreatPTag("");
        }
        $used_total = MSP_CreatPTag($value['installCnt'] . '/' . $value['licenseCnt']);

        $rowId = $value['orderNum'];

        $array[] = array(
            "DT_RowId" => $rowId,
            'order' => $order,
            'purchaseDate' => $purchaseDate,
            'expireDate' => $expireDate,
            'used_total' => $used_total
        );
    }
    return $array;
}


function MSP_ExportAllOrders()
{
    $conn = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $username = $_SESSION["user"]["logged_username"];
    $customerType = $_SESSION["user"]["customerType"];
    $result = MSP_Get_MSPOrdersGrid($conn, $loggedEid, $customerType);

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Order Number");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Purchase Date");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Expiring Date");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Used");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Total");

    $objPHPExcel = MSP_FormatOrderExcel($objPHPExcel, $result);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $username . '_Orders.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}


function MSP_FormatOrderExcel($objPHPExcel, $resultArray)
{
    $index = 2;
    foreach ($resultArray as $key => $value) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['orderNum']);
        if ($value['purchaseDate'] > 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, date("m/d/Y", $value['purchaseDate']));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, "-");
        }
        if ($value['contractEndDate'] > 0) {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, date("m/d/Y", $value['contractEndDate']));
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, "-");
        }
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['installCnt']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value['licenseCnt']);
        $index++;
    }

    return $objPHPExcel;
}



function MSP_GetCustomerGrid()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $ctype = $_SESSION["user"]["cd_ctype"];
    $eid = UTIL_GetString('id', '');
    $key = '';

    $result = MSP_GetMSPCustomers($key, $conn, $loggedEid);


    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatCustomerGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}


function MSP_GetCustomerSitesGrid()
{

    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $custId = url::requestToAny('custid');
    $key = '';


    $result = MSP_GetMSPCustomerSites_OLD($key, $conn, $custId);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatCustomerSiteGrid_OLD($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_getSiteGridData()
{

    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $custId = url::requestToAny('custid');
    $key = '';

    $result = getUniqSiteNames($custId, $conn);


    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatCustomerSiteGridData($result, $custId, $conn);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function getUniqSiteNames($cid, $db)
{

    $sql_chnl = "select C.companyName,C.emailId,C.eid from " . $GLOBALS['PREFIX'] . "agent.channel C where eid='$cid'";
    $chnl_res = find_one($sql_chnl, $db);
    if (safe_count($chnl_res) > 0) {
        $emailId = $chnl_res['emailId'];
        $sql_cust = "select S.customer from " . $GLOBALS['PREFIX'] . "core.Customers S,core.Users U where S.username=U.username and U.user_email = '$emailId' and U.ch_id='$cid' group by S.customer";
        $cust_res = find_many($sql_cust, $db);
        return $cust_res;
    }
}

function MSP_GetSitesDeviceGrid()
{

    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $custId = url::requestToAny('custId');
    $procId = url::requestToAny('procId');
    $custNum = url::requestToAny('custNum');
    $ordNum = url::requestToAny('ordNum');

    $key = '';
    $result = MSP_GetCustomerSites_OLD($key, $conn, $custId, $procId, $custNum, $ordNum);

    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatCustDeviceGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_GetEntityGrid()
{
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $custId = url::requestToAny('custid');
    $key = '';

    // @warn: I think this code is not work properly.
    $resObj = DashboardAPI("GET", "entity");

    $result = $resObj->result;

    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatEntityGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_FormatEntityGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $id = 1;
        $firstName = $value->firstName;
        $lastName = $value->lastName;
        $email = $value->emailId;
        $company = $value->companyName;
        $companyName = explode('_', $company);
        $rowId = $value->eid;
        $array[] = array(
            "DT_RowId" => $rowId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'company' => $companyName[0]
        );
    }

    return $array;
}

function MSP_GetTenantGrid()
{


    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $custId = url::requestToAny('custid');
    $key = '';

    $resObj = DashboardAPI("GET", "/channel/" . $loggedEid);

    $result = $resObj->result;

    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);

        $recordList = MSP_FormatTenantGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_FormatTenantGrid($resultArray)
{
    $array = [];

    $firstName = $resultArray->firstName;
    $lastName = $resultArray->lastName;
    $email = $resultArray->emailId;
    $company = $resultArray->companyName;
    $companyName = explode('_', $company)[0];
    $activeState = $resultArray->serverActivation;

    $tenantStatus = 'Inactive';
    if ($activeState == '1') {
        $tenantStatus = 'Active';
    }
    $rowId = $id;
    $array[] = array(
        "DT_RowId" => $rowId,
        'firstName' => $firstName,
        'lastName' => $lastName,
        'email' => $email,
        'company' => $companyName,
        'actStatus' => $tenantStatus
    );


    return $array;
}

function MSP_GetTenantGrid_Old()
{
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $key = '';
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $loggedEid = 1;
    $sql = "select * from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$loggedEid' limit 1";
    $result = find_one($sql, $db);

    if (safe_count($result) > 0) {

        $totalCount = safe_count($result);

        $firstName = $result['firstName'];
        $lastName = $result['lastName'];
        $email = $result['emailId'];
        $company = $result['companyName'];
        $activeState = $result['serverActivation'];
        $tenantStatus = 'Inactive';
        if ($activeState == '1') {
            $tenantStatus = 'Active';
        }
        $parts = explode('_', $company);
        $last = array_pop($parts);
        $parts = array(implode('_', $parts), $last);

        $rowId = 1;
        $recordList[] = array(
            "DT_RowId" => $rowId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'company' => $parts[0],
            'actStatus' => $tenantStatus
        );
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_getSiteDnlURL()
{
    global $base_url;
    $loggedEid = $_SESSION["user"]["cId"];
    $conn = db_connect();

    $custId = url::requestToAny('custId');
    $procId = url::requestToAny('procId');
    $custNum = url::requestToAny('custNum');
    $ordNum = url::requestToAny('ordNum');
    $sql = "select id,customerNum,orderNum,downloadId,contractEndDate from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum='$custNum' and orderNum='$ordNum' and compId='$custId' and processId='$procId' limit 1";
    $res = find_one($sql, $conn);

    if (safe_count($res)) {
        $downLoadUrl = $base_url . 'eula.php?id=' . $res['downloadId'];
        echo $downLoadUrl;
    } else {
        echo '';
    }
}


function MSP_GetMSPCustomers($key, $db, $loggedEid)
{
    $sql = "select C.eid,C.companyName,C.firstName,C.lastName,C.emailId,C.status, C.createdTime from " . $GLOBALS['PREFIX'] . "agent.channel C where C.channelId='$loggedEid'";
    $res = find_many($sql, $db);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function MSP_GetMSPCustomerSites_OLD($key, $db, $loggedEid)
{
    $sql = "select C.siteName,C.id,C.customerNum,C.orderNum,C.compId,C.processId,count(S.sid) installedCnt from "
        . "agent.customerOrder C left join " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and "
        . "C.orderNum=S.orderNum and S.revokeStatus='I' where C.compId = '$loggedEid' group by C.customerNum,C.orderNum";
    $res = find_many($sql, $db);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function MSP_GetMSPCustomerSites($key, $db, $loggedEid)
{
    $params = '{
                "query": {
                    "term": {
                        "compId": "' . $loggedEid . '"
                    }
                }
            }';

    $customers = EL_GetCurl("customerorder", $params);
    $customersArr = EL_FormatCurldata($customers);

    if (safe_count($customersArr) > 0) {
        return $customersArr;
    } else {
        return array();
    }
}

function MSP_GetCustomerSites_OLD($key, $db, $custId, $procId, $custnum, $ordNum)
{

    $sql = "select C.siteName,C.id,C.customerNum,C.orderNum,C.compId,C.processId,C.orderDate,C.contractEndDate,S.serviceTag,S.installationDate,S.uninstallDate, "
        . "S.downloadStatus,S.revokeStatus,S.orderStatus,S.sid,S.approveStatus from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.serviceRequest S "
        . "where C.customerNum=S.customerNum and C.orderNum=S.orderNum and C.customerNum='$custnum' and "
        . "C.orderNum='$ordNum' and C.compId='$custId' and C.processId= '$procId' and S.revokeStatus='I' group by S.serviceTag";
    $res = find_many($sql, $db);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function MSP_GetCustomerSites($key, $db, $custId, $procId, $custnum, $ordNum)
{

    $params = '{
                    "query": {
                        "query_string": {
                            "query": "(customerNum:' . $custnum . ') AND (orderNum:' . $ordNum . ')",
                            "default_operator": "AND"
                        }
                    }
                }';

    $devices = EL_GetCurl("servicerequest", $params);
    $devicesArr = EL_FormatCurldata($devices);
    if (safe_count($devices)) {
        return $devicesArr;
    } else {
        return array();
    }
}


function MSP_FormatCustomerSiteGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $customerNum = $value['customerNum'];
        $orderNum = $value['orderNum'];
        $devices = ELPROV_GetCustomersDevices($customerNum, $orderNum);
        $siteName = MSP_CreatPTag(UTIL_GetTrimmedGroupName($value['siteName']));
        $customerNum = $value['customerNum'];
        $orderNum = $value['orderNum'];
        $compId = $value['compId'];
        $processId = $value['processId'];
        $installCnt = safe_count($devices);
        $rowId = $customerNum . '---' . $orderNum . '---' . $compId . '---' . $processId;
        $array[] = array(
            "DT_RowId" => $rowId,
            'sites' => $siteName,
            'installCount' => $installCnt
        );
    }

    return $array;
}

function MSP_FormatCustomerSiteGridData($resultArray, $cid, $db)
{
    $array = [];
    foreach ($resultArray as $key => $value) {

        $sitename = $value['customer'];
        $ser_res = getInstallCountDetails($sitename, $cid, $db);


        $siteName = MSP_CreatPTag(UTIL_GetTrimmedGroupName($value['customer']));

        $compId = $cid;
        $processId = $value->process_id;
        $installCnt = $ser_res['cnt'];


        $status_details = $installCnt;
        $processId = 0;
        $rowId = $sitename . '---' . $compId . '---' . $processId;

        $array[] = array(
            "DT_RowId" => $rowId,
            'sites' => $siteName,
            'installCount' => $status_details
        );
    }

    return $array;
}

function getInstallCountDetails($sitename, $cid, $db)
{

    $sql_ser = "select count(S.sid) cnt from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.siteName='$sitename' and S.compId='$cid' and S.revokeStatus='I'";
    $res = find_one($sql_ser, $db);
    return $res;
}

function MSP_FormatCustomerSiteGrid_OLD($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $customerNum = $value['customerNum'];
        $orderNum = $value['orderNum'];
        $siteName = MSP_CreatPTag(UTIL_GetTrimmedGroupName($value['siteName']));
        $customerNum = $value['customerNum'];
        $orderNum = $value['orderNum'];
        $compId = $value['compId'];
        $processId = $value['processId'];
        $installCnt = $value['installedCnt'];
        $rowId = $customerNum . '---' . $orderNum . '---' . $compId . '---' . $processId;
        $array[] = array(
            "DT_RowId" => $rowId,
            'sites' => $siteName,
            'installCount' => $installCnt
        );
    }

    return $array;
}




function MSP_ExportAllSites()
{
    $conn = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $username = $_SESSION["user"]["logged_username"];
    $loggedEid = $_SESSION["user"]["cId"];
    $custId = url::requestToAny('custid');
    $key = '';

    $result = MSP_GetMSPCustomerSites_OLD("", $conn, $custId);

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Sites");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Number of devices");
    $objPHPExcel->getActiveSheet()->setTitle("Sites's Details");

    $objPHPExcel = MSP_FormatSitesExcel($objPHPExcel, $result);
    $objPHPExcel = MSP_exportDeviceDetails($objPHPExcel, $key, $conn, $result);

    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $username . '_Sites.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function MSP_FormatSitesExcel($objPHPExcel, $resultArray)
{
    $index = 2;

    foreach ($resultArray as $key => $value) {
        $customerNum = $value['customerNum'];
        $orderNum = $value['orderNum'];

        $deviceCount = $value['installedCnt'];
        $siteName = UTIL_GetTrimmedGroupName($value['siteName']);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $siteName);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $deviceCount);
        $index++;
    }

    return $objPHPExcel;
}

function MSP_exportDeviceDetails($objPHPExcel, $key, $conn, $sitesArray)
{

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Site Name");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Device Name");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Installed Date");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Uninstalled Date");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Status");
    $objPHPExcel->getActiveSheet()->setTitle("Devices's Details");
    $index = 2;
    foreach ($sitesArray as $key => $value) {
        $siteName = $value['siteName'];
        $custId = $value['compId'];
        $procId = $value['processId'];
        $custNum = $value['customerNum'];
        $ordNum = $value['orderNum'];
        $result = MSP_GetCustomerSites_OLD("", $conn, $custId, $procId, $custNum, $ordNum);

        $objPHPExcel = MSP_FormatDevicesExcel($objPHPExcel, $result, $siteName);
    }
    return $objPHPExcel;
}

function MSP_FormatDevicesExcel($objPHPExcel, $resultArray, $siteName)
{
    $today = time();
    $index = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();
    foreach ($resultArray as $key => $value) {
        $index++;
        $siteName = UTIL_GetTrimmedGroupName($siteName);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $siteName);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['serviceTag']);

        $installDate = $value['installationDate'];
        if (empty($installDate)) {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '-');
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, PHPExcel_Shared_Date::PHPToExcel($installDate));
            $objPHPExcel->getActiveSheet()->getStyle('C' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");
        }

        $uninstallDate = $value['uninstallDate'];
        if (empty($uninstallDate)) {
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '-');
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, PHPExcel_Shared_Date::PHPToExcel($uninstallDate));
            $objPHPExcel->getActiveSheet()->getStyle('D' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");
        }

        $status = ($value['uninstallDate'] < $today) ? 'Inactive' : 'Active';

        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $status);
    }
    return $objPHPExcel;
}



function MSP_FormatCustomerGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $customer = MSP_CreatPTag($value['companyName']);
        $firstName = MSP_CreatPTag($value['firstName']);
        $lastName = MSP_CreatPTag($value['lastName']);
        $email = MSP_CreatPTag($value['emailId']);
        $status = MSP_GetCustomerStatus($value['status']);
        $status = MSP_CreatPTag($status);

        $rowId = $value['status'] . '---' . $value['eid'];
        $status = $value['status'];
        $staVal = '';
        if ($status == 1 || $status == '1') {
            $staVal = '<p style="color:green;">Active</p>';
        } elseif ($status == 0 || $status == '0') {
            $staVal = '<p style="color:red;">InActive</p>';
        }

        $array[] = array(
            "DT_RowId" => $rowId,
            'customer' => utf8_encode($customer),
            'status' => utf8_encode($staVal)
        );
    }

    return $array;
}

function MSP_FormatResDeviceGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $devicename = MSP_CreatPTag($value['serviceTag']);
        $installDt = MSP_CreatPTag(date("m-d-Y", $value['orderDate']));
        $uninstallDt = MSP_CreatPTag(date("m-d-Y", $value['contractEndDate']));
        $status = MSP_CreatPTag($value['orderStatus']);
        $sid = $value['sid'];

        $rowId = $devicename . '---' . $sid;
        $array[] = array(
            "DT_RowId" => $rowId,
            'devicename' => $devicename,
            'installDt' => $installDt,
            'status' => $status
        );
    }
    return $array;
}



function MSP_FormatCustDeviceGrid($resultArray)
{
    $array = [];
    $today = time();
    foreach ($resultArray as $key => $value) {
        $devicename = MSP_CreatPTag($value['serviceTag']);
        if (empty($value['installationDate'])) {
            $installDt = MSP_CreatPTag('-');
        } else {
            $installDt = MSP_CreatPTag(date("m-d-Y", $value['installationDate']));
        }
        if (empty($value['uninstallDate'])) {
            $uninstallDt = MSP_CreatPTag('-');
        } else {
            $uninstallDt = MSP_CreatPTag(date("m-d-Y", $value['uninstallDate']));
        }

        $status = ($value['uninstallDate'] < $today) ? MSP_CreatPTag('Inactive') : MSP_CreatPTag('Active');
        $sid = $value['sid'];

        $rowId = $devicename . '---' . $sid;
        $array[] = array(
            "DT_RowId" => $rowId,
            'devicename' => $devicename,
            'installDt' => $installDt,
            'uninstallDt' => $uninstallDt,
            'status' => $status
        );
    }
    return $array;
}


function MSP_IsTrialReseller()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails O WHERE O.chnl_id = '$loggedEid' AND  O.trial != '1'";
    $res = find_many($sql, $conn);
    if (safe_count($res) > 0) {
        return "NOT_TRIAL";
    } else {
        return "TRIAL";
    }
}


function MSP_CreateCustomer()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $custCompName = UTIL_GetString('custCompName', '');
    $custEmail = UTIL_GetString('custEmail', '');
    $compNameExist = RSLR_IsValueExist($conn, $custCompName);
    $compEmailExist = RSLR_IsValueExist($conn, $custEmail);

    if ($compNameExist) {
        $array = array("msg" => 'Customer Name already exist');
    } elseif ($compEmailExist) {
        $array = array("msg" => 'Customer email already exist');
    } else {
        $createCustomer = CUST_Create_MSPCustomer($conn, $loggedEid);
        $array = $createCustomer;
    }

    echo json_encode($array);
}



function CUST_Create_MSPCustomer($db, $resellerId)
{
    global $CRMEN;

    $custCompName = UTIL_GetString('custCompName', '');
    $custCompAddr = UTIL_GetString('custCompAddr', '');
    $custCompCity = UTIL_GetString('custCompCity', '');
    $custCompState = UTIL_GetString('custCompState', '');
    $custCompZipcode = UTIL_GetString('custCompZipcode', '');
    $custCompCountry = UTIL_GetString('custCompCountry', '');
    $custCompWebsite = UTIL_GetString('custCompWebsite', '');
    $custFirstName = UTIL_GetString('custFirstName', '');
    $custLastName = UTIL_GetString('custLastName', '');
    $custEmail = UTIL_GetString('custEmail', '');
    $custLicence = UTIL_GetString('custLicence', '');
    $aviraotc = UTIL_GetString('aviraotc', '');
    $language = UTIL_GetString('language', 'en');
    $pccount = UTIL_GetString('custLicence', '');
    $roleId = UTIL_GetString('custRole', '');
    $trialSite = 0;
    $agentEmail = $_SESSION['user']['adminEmail'];
    $ctype = 5;
    $entityId = $_SESSION["user"]["entityId"];
    $cId = $_SESSION["user"]["cId"];

    $resellerDetails = MSP_GetEntityDetail($db, $cId);
    $serverUrl = $resellerDetails['reportserver'];
    $skulist = $resellerDetails['skulist'];
    $trialEndDate = time() + 2678400;
    $customerInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
     referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
     ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status, trialEnabled, trialStartDate, trialEndDate)
     VALUES ('$entityId', '$resellerId', '0', '0', '$custCompName', '0', '0', '$custFirstName', '$custLastName', '$custEmail', '0', '$custCompAddr', '$custCompCity',
     '$custCompZipcode', '$custCompCountry', '$custCompState', '$custCompWebsite','5','','Commercial','', '', '$serverUrl', '0','Email','" . time() . "', '', '', 1, '1', '" . time() . "', '" . $trialEndDate . "')";

    $customerInsertRes = redcommand($customerInsertSql, $db);

    if ($customerInsertRes) {
        $custId = mysql_insert_id();
        $custNo = date("Y") . '000' . $custId;
        $provision = MSP_Create_MSPProvision($db, $custId, $custCompName, $custFirstName, $custLastName, $custEmail, $trialSite, $pccount, $agentEmail);
        $userKey = USER_UserKey($db);


        $createUser = MSP_Create_MSPUser($db, $custId, $entityId, $resellerId, $roleId, $custFirstName, $custFirstName, $custLastName, $custEmail, $userKey);

        if ($createUser) {
            $userName = preg_replace('/\s+/', '_', $custFirstName);
            $sitename = MSP_GetFilteredSiteName($custCompName, $custNo);

            $insertSite = CUST_AddAviraSignupSites($db, $resellerId, $sitename);
            $alter = MSP_AlterTable($db, "agent", "channel", "clientlogo", "VARCHAR(250)", "default");
            $UIDirectory = CUST_CreateClient_UIDirectory($custCompName);
            $createSite = USER_InsertSite($db, $custFirstName, $sitename, $custLastName);
            $sendMail = User_SendEmail($db, $custFirstName, $custEmail, $userKey, '10', $resellerId, $language);
            if ($CRMEN == 1) {
                RSLR_CRMlogin($custFirstName, $custLastName, $custEmail, $custCompName, $custNo, $custId);
            }
            return $provision;
        } else {
        }
    } else {
        return array("msg" => 'Fail to create new customer. Please try later.', "error" => mysql_error($db));
    }
}

function MSP_Create_MSPProvision($db, $custId, $custCompName, $custFirstName, $custLastName, $customerEmailId, $trialSite, $pccount, $agentEmail)
{
    global $base_url;
    $custId = mysql_insert_id();
    $updateCustomerNo = CUST_UpdateCustomerNo($db, $custId);
    $custNo = date("Y") . '000' . $custId;
    $custOrderNo = MSP_GetAutoOrderNo($db);
    $roleItems = CUST_Get_MSPRoleItems($db);

    $insertProccess = MSP_Create_MSPProcessMaster($db, $roleItems, $custId, $custCompName, $custFirstName, $custLastName);
    $resellerId = $_SESSION['user']['cId'];
    if ($insertProccess) {
        $processId = mysql_insert_id();
        $sitename = MSP_GetFilteredSiteName($custCompName, $custNo);
        $provision_urlStr = MSP_Create_MSPCustomerOrder($db, $custNo, $custOrderNo, $custFirstName, $custLastName, $customerEmailId, '', $custId, $custCompName, $resellerId, $trialSite, '', $pccount, $agentEmail, $sitename);
        if ($provision_urlStr != "NOTDONE") {
            RSLR_CreateTrialOrder($custId, $pccount);
            $downLoadUrl = $base_url . 'eula.php?id=' . $provision_urlStr;
            return array("msg" => $custCompName . ' created Successfully', "status" => "success", "link" => $downLoadUrl);
        } else {
            return array("msg" => 'Fail to create new customer. Please try later', "status" => "failed");
        }
    }
}

function MSP_Create_MSPProcessMaster($db, $roleItems, $custId, $custCompName, $custFirstName, $custLastName)
{
    global $base_url;
    $proList = array();
    foreach ($roleItems as $element) {
        $roleModule = explode("=", $element);
        $proList[$roleModule[0]] = $roleModule[1];
    }
    $custNo = date("Y") . '000' . $custId;
    $setup32bit = $proList['32bitdeploy'];
    $setup64bit = $proList['64bitdeploy'];
    $deploy32bit = $proList['32bitsetup'];
    $deploy64bit = $proList['64bitsetup'];
    $macSetup = $proList['macsetup'];
    $androidSetup = $proList['androidsetup'];
    $phoneNo = $proList['phoneno'];
    $chatLink = $proList['chaturl'];
    $privacyLink = $proList['privacyurl'];
    $nodeURL = $proList['wsurl'];
    $ftpURL = $proList['ftpurl'];
    $folnDarts = $proList['followOnDarts'];
    $linux32bit = $proList['linuxsetup'];
    $linux64bit = $proList['linuxsetup64'];
    $currentDate = time();
    $downloaderPath = $base_url . 'eula.php';
    $sitename = MSP_GetFilteredSiteName($custCompName, $custNo);

    $process_sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$custId',processName = '" . $custCompName . "' ,siteCode = '" . $sitename . "',"
        . "deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',"
        . "setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',"
        . "WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='$downloaderPath',"
        . "phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',folnDarts='" . $folnDarts . "',"
        . "privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',"
        . "macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',"
        . "macProfileName='profile_mac',linuxsetup='$linux32bit',linuxsetup64='$linux64bit'";

    $process_result = redcommand($process_sql, $db);
    return $process_result;
    $_SESSION["selected"]["ctype"] = '';
    $_SESSION["selected"]["eid"] = '';
}


function MSP_Create_MSPCustomerOrder($db, $crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $channelId, $trialSite, $otcCode, $noOfPc, $agentEmail, $sitename)
{
    $valLic = 5;
    $pId = CUST_GetProcessId($db, $cId);
    $customerNumber = MSP_GetAutoCustNo($db);
    $customerOrder = MSP_GetAutoOrderNo($db);
    $provCode = '01';
    $noOfDays = 1830;
    $key = '';
    if ($cId == '' || $pId == '') {
        return "NOTDONE";
        exit();
    }
    $custDtl = MSP_GetEntityDetail($db, $channelId);
    $reportserver = $custDtl["reportserver"];

    $curDate = date("m-d-Y H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(Date("m-d-Y", strtotime('+5 years')));

    $process_detail = CUST_GetProcessData($key, $db, $pId);
    $variation = $process_detail['variation'];
    $locale = $process_detail['locale'];
    $downloadPath = $process_detail['downloaderPath'];
    $sendEmail = $process_detail['sendMail'];
    $backUp = $process_detail['backupCheck'];
    $respectiveDB = $process_detail['DbIp'];

    $seesionSet = CUST_SetProcessDetailSession($process_detail);
    $fileString = create_ini_parameters($sitename, $dateOfOrder, $customerNumber, $customerOrder, $customerEmailId, "", $provCode, $reportserver, '0', '', '');

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = CUST_GetCustDownloadId($db);
        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',"
            . " refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', "
            . " coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '', "
            . " emailId= '" . trim($customerEmailId) . "',SKUNum = '002-0000/MSP30D5PCTRIAL', SKUDesc = 'Nanoheal Trial(30 days)', "
            . " orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '0', "
            . " sessionid = '" . $sessionid . "', sessionIni = '" . mysql_real_escape_string($fileString) . "', "
            . " validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='',"
            . " provCode = '" . $provCode . "',remoteSessionURL = '',agentId = '" . $agentEmail . "',"
            . " processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',advSub='1',"
            . " licenseKey = ''";
        $result = redcommand($sql_ser, $db);
        if ($result) {
            return $downloadId;
        } else {
            return "NOTDONE";
        }
    } else {
        return "NOTDONE";
    }
}


function MSP_Create_CustomerSiteOrder($pdo, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $channelId, $trialSite, $otcCode, $noOfPc, $agentEmail, $sitename)
{
    $pId = CUST_GetProcessId($pdo, $cId);
    $oldProvisionDetails = MSP_GetOldProvisionDetails($pdo, $cId, $pId);
    $customerNumber = $oldProvisionDetails['customerNum'];
    $crmCustomerNum = MSP_GetAutoCustNo($pdo);
    $customerOrder = MSP_GetAutoOrderNo($pdo);
    $crmOrderNum = MSP_GetAutoOrderNo($pdo);

    $provCode = '01';
    $noOfDays = 1830;
    $key = '';
    if ($cId == '' || $pId == '') {
        return "NOTDONE";
        exit();
    }
    $custDtl = MSP_GetEntityDetail($pdo, $channelId);
    $reportserver = $custDtl["reportserver"];

    $curDate = date("m-d-Y H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(Date("m-d-Y", strtotime('+5 years')));

    $process_detail = CUST_GetProcessData($key, $pdo, $pId);
    $variation = $process_detail['variation'];
    $locale = $process_detail['locale'];
    $downloadPath = $process_detail['downloaderPath'];
    $sendEmail = $process_detail['sendMail'];
    $backUp = $process_detail['backupCheck'];
    $respectiveDB = $process_detail['DbIp'];

    $seesionSet = CUST_SetProcessDetailSession($process_detail);
    $fileString = create_ini_parameters($sitename, $dateOfOrder, $customerNumber, $customerOrder, $customerEmailId, "", $provCode, $reportserver, '0', '', '');

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = CUST_GetCustDownloadId($pdo);
        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = ?, orderNum = ?, refCustomerNum = ?,refOrderNum = ?, coustomerFirstName = ?, "
            . " coustomerLastName = ?, coustomerCountry = ?, emailId= ?,SKUNum = ?, SKUDesc = ?, orderDate = ?, contractEndDate = ?, "
            . "backupCapacity = ?, sessionid = ?, sessionIni = ?, validity = ?, noOfPc = ?, oldorderNum = ?, provCode = ?, remoteSessionURL = ?, "
            . "agentId = ?, processId= ?, compId = ?,downloadId = ?, siteName = ?, advSub = ?, licenseKey = ?";
        $file_String = mysql_real_escape_string($fileString);
        $stmt = $pdo->prepare($sql_ser);
        $bindings = [
            $customerNumber, $customerOrder, $crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, '',
            trim($customerEmailId), '', '', $dateOfOrder, $contractEnd, '0', $sessionid, $file_String, trim($noOfDays), trim($noOfPc),
            '', $provCode, '', $agentEmail, $pId, $cId, $downloadId, $sitename, '1', ''
        ];

        $result = $stmt->execute($bindings);
        if ($result) {
            $id = $pdo->lastInsertId();
            MSP_PushCustomerOrder_EL($id, $pdo);
            return $downloadId;
        } else {
            return "NOTDONE";
        }
    } else {
        return "NOTDONE";
    }
}

function MSP_PushCustomerOrder_EL($id, $pdo)
{
    $stmt = $pdo->prepare('SELECT * FROM ' . $GLOBALS['PREFIX'] . 'agent.customerOrder WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res) > 0) {
        $tempId = array("index" => array("_id" => $id));
        unset($res['sessionIni']);
        $pdata = $res;
        $fdata .= str_replace(array('[', ']'), '', json_encode($tempId)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($pdata)) . PHP_EOL;
        $push = EL_PushData('customerorder', $fdata, $id);
    }

    return TRUE;
}


function MSP_Create_MSPUser($db, $ch_id, $entityId, $channelId, $roleId, $userName, $firstName, $lastName, $userEmail, $userKey)
{
    $username = $firstName . $lastName;
    $userName = preg_replace('/\s+/', '_', $username);
    $checkSum = md5(mt_rand());

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users SET ch_id = '$ch_id', entity_id = '0', channel_id = '0', subch_id = '0', customer_id='0', ";
    $sql .= "role_id='$roleId', username = '$userName', firstName = '$firstName', lastName = '$lastName', user_email = '$userEmail', ";
    $sql .= "user_phone_no = '', password = '', notify_mail = '', report_mail = '', logo_file = '', logo_x = '', ";
    $sql .= "logo_y = '', footer_left = '', footer_right = '', revusers = '', cksum = '$checkSum', ";
    $sql .= "asset_report_sender = '', disable_cache = '0', event_notify_sender = '', event_report_sender = '', jpeg_quality = '95', ";
    $sql .= "meter_report_sender = '', rept_css = '', clogo = '', passwordDate = NULL, userKey = '$userKey', userSession = '', ";
    $sql .= "imgPath = '', passwordHistory = ''";
    $res = redcommand($sql, $db);
    return $res;
}

function MSP_Update_UIDirectoryPath($db, $eid, $path)
{

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel SET clientlogo=? WHERE eid=?";
    $pdo = $db->prepare($sql);
    $res = $pdo->execute([$path, $eid]);

    $updateSqlLite = MSP_Update_SqlLiteDB($db, $eid, $path);
    return TRUE;
}

function MSP_Update_SqlLiteDB($db, $eid, $path)
{
    $mid = MSP_GetProfileMapIds($db);
    $res = MSP_GetProfileDBUpload($key, $db, "$mid", $eid, $path);
    return TRUE;
}

function MSP_GetProfileMapIds($db)
{
    $loggedEid = $_SESSION['user']['cId'];
    $entityEid = $_SESSION['user']['entityId'];
    $adminEid = 1;
    $sql = "SELECT customerid, profileid FROM " . $GLOBALS['PREFIX'] . "profile.profileMap";
    $pdo = $db->prepare($sql);
    $pdo->execute();
    $res = $pdo->fetchAll(PDO::FETCH_ASSOC);
    $str = '';


    if (array_search($loggedEid, array_column($res, 'customerid')) !== false) {
        $finalEid = $loggedEid;
    } else if (array_search($entityEid, array_column($res, 'customerid')) !== false) {
        $finalEid = $entityEid;
    } else {
        $finalEid = $adminEid;
    }

    foreach ($res as $key => $value) {
        if ($value['customerid'] == $finalEid) {
            $str .= $value['profileid'] . ",";
        }
    }
    $str = rtrim($str, ",");
    return $str;
}


function MSP_Get_MSPCustomerDetails()
{
    $db = db_connect();
    $customerId = UTIL_GetString('cust_id', '');
    $sql = "SELECT C.id, C.noOfPc, CH.companyName, CH.firstName, CH.lastName, CH.emailId, CH.website, CH.country, CH.address, CH.city, ";
    $sql .= "CH.province, CH.zipCode FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.channel CH WHERE C.id = '$customerId' AND C.compId=CH.eid LIMIT 1";
    $res = find_one($sql, $db);
    $companyDetails['id'] = MSP_GetValue($res["id"], "0");
    $companyDetails['companyName'] = MSP_GetValue($res["companyName"], "-");
    $companyDetails['website'] = MSP_GetValue($res["website"], "-");
    $companyDetails['address'] = MSP_GetValue($res["address"], "-");
    $companyDetails['city'] = MSP_GetValue($res["city"], "-");
    $companyDetails['province'] = MSP_GetValue($res["province"], "0");
    $companyDetails['zipCode'] = MSP_GetValue($res["zipCode"], "0");
    $companyDetails['firstName'] = MSP_GetValue($res["firstName"], "-");
    $companyDetails['lastName'] = MSP_GetValue($res["lastName"], "-");
    $companyDetails['emailId'] = MSP_GetValue($res["emailId"], "-");
    $companyDetails['noOfPc'] = MSP_GetValue($res["noOfPc"], 0);
    MSP_GetProfileMapIds($db);
    return json_encode($companyDetails);
}


function MSP_IsLicensesExist($db, $cId, $enteredLicenses)
{
    $alter = MSP_AlterTable($db, "agent", "serviceRequest", "nhOrderKey", "VARCHAR(250)", "0");
    if ($alter) {
        $total_sql = "SELECT sum(O.licenseCnt) as totalLicense FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails O WHERE O.chnl_id = '$cId' LIMIT 1";
        $total_res = find_one($total_sql, $db);

        $install_sql = "SELECT count(S.sid) as installLicense FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S, " . $GLOBALS['PREFIX'] . "agent.orderDetails O WHERE "
            . "S.nhOrderKey = O.orderNum AND O.chnl_id = '$cId' and S.revokeStatus='I' and uninsdormatStatus IS NULL";
        $install_res = find_one($install_sql, $db);
        $avaibleLicense = (int) $total_res['totalLicense'] - (int) $install_res['installLicense'];
        if ((int) $enteredLicenses <= (int) $avaibleLicense) {
            return "EXIST";
        } else {
            return $avaibleLicense;
        }
    } else {
    }
}


function MSP_LicensesInstalled($db, $cust_id, $enteredLicenses)
{
    $sql = "SELECT COUNT(S.sid) as installed FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S, " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum=S.customerNum ";
    $sql .= "AND C.orderNum=S.orderNum AND C.id = '$cust_id' AND S.revokeStatus = 'I' AND S.uninsdormatStatus IS NULL";

    $res = find_one($sql, $db);
    $installedLicense = $res['installed'];
    if ((int) $enteredLicenses <= (int) $installedLicense) {
        return FALSE;
    } else {
        return TRUE;
    }
}

function MSP_RenewDevices()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $compId = UTIL_GetString('compId', '');
    $customerNumber = UTIL_GetString('customerNumber', '');
    $nextThirtyDays = strtotime("+30 days");
    $result = MSP_GetRenewDevices($conn, $compId, $customerNumber, $nextThirtyDays);
    $totalCount = safe_count($result);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatRenewDevices($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function MSP_GetRenewDevices($db, $compId, $customerNumber, $nextThirtyDays)
{
    $renew_sql = "SELECT S.sid, S.customerNum, S.orderNum,  S.serviceTag, S.installationDate, S.uninstallDate,S.compId FROM "
        . "agent.serviceRequest S JOIN " . $GLOBALS['PREFIX'] . "agent.customerOrder C on S.compId=C.compId AND S.compId IN ('$compId') and "
        . "S.customerNum=C.customerNum and S.orderNum=C.orderNum and S.customerNum='$customerNumber' AND S.uninstallDate <= '$nextThirtyDays' "
        . "AND S.orderStatus='Active' group by S.customerNum, S.orderNum, S.serviceTag order by S.sid desc";

    $renew_res = find_many($renew_sql, $db);
    return $renew_res;
}

function MSP_FormatRenewDevices($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {
        $order = MSP_CreatPTag($value['orderNum']);
        $device = MSP_CreatPTag($value['serviceTag']);
        $insDate = MSP_CreatPTag(date("m-d-Y", $value['installationDate']));
        $uninsDate = MSP_CreatPTag(date("m-d-Y", $value['uninstallDate']));
        $checkBox = MSP_CreateCheckBox($value['sid'] . '---' . $value['orderNum'], "renewCheck");

        $rowId = $value['customerNum'] . "---" . $value['orderNum'] . "---" . $value['compId'] . "---" . $value['sid'];
        $array[] = array(
            "DT_RowId" => $rowId,
            'checkBox' => $checkBox,
            'order' => $order,
            'device' => $device,
            'insDate' => $insDate,
            'uninsDate' => $uninsDate
        );
    }
    return $array;
}

function MSP_RenewSelectedDevices()
{
    $loggedEid = $_SESSION["user"]["cId"];
    $agentEmailId = $_SESSION['user']['adminEmail'];
    $conn = db_connect();
    $today = time();

    $licensesDetails = MSP_GetAvailableLicenses($conn, $today, $loggedEid);

    if ($licensesDetails !== 0) {
        $selecDevices = UTIL_GetString('selecDevices', '');
        $selecDevicesArr = explode(",", $selecDevices);
        $availableCnt = $licensesDetails['availableCnt'];

        if ($availableCnt >= safe_count($selecDevicesArr)) {
            $noOfPc = safe_count($selecDevicesArr);
        } else if ($availableCnt < safe_count($selecDevicesArr)) {
            $noOfPc = safe_count($selecDevicesArr) - $availableCnt;
        }

        $customerNumber = UTIL_GetString('customerNumber', '');
        $oldOrderNumber = UTIL_GetString('orderNumber', '');
        $siteName = UTIL_GetString('siteName', '');
        $compId = UTIL_GetString('compId', '');
        $pId = UTIL_GetString('pid', '');
        $newOrderNum = MSP_GetAutoOrderNo($db);
        $orderDate = time();

        $renewProvision = CUST_RenewProvision($customerNumber, "$oldOrderNumber", $newOrderNum, $siteName, $siteName, $siteName, "", $compId, $siteName, "", $noOfPc, $orderDate, "", "");
        if ($renewProvision) {
            $result = MSP_RenewDevicesProccess($conn, $loggedEid, $compId, $pId, $customerNumber, $newOrderNum, $selecDevicesArr, $today, $agentEmailId);
            return json_encode(array("status" => "success", "msg" => "Devices renewed successfully.", "data" => $result));
        }
    } else {
        return json_encode(array("status" => "failed", "msg" => "Dont have enough licenses for renew"));
    }
}

function MSP_RenewDevicesProccess($conn, $loggedEid, $cId, $pId, $customerNumber, $newOrderNum, $selecDevicesArr, $date, $agentEmailId)
{
    $licensesDetails = MSP_GetAvailableOrders($conn, $date, $loggedEid);
    $renewResult = [];
    foreach ($licensesDetails as $key => $value) {

        $totalLicense = $value['licenseCnt'];
        $installedLicense = $value['installCnt'];
        $uninstallDate = $value['contractEndDate'];
        $loopDeviceArray = $selecDevicesArr;
        if ($totalLicense > $installedLicense) {
            foreach ($loopDeviceArray as $key => $sid) {
                if ($totalLicense > $installedLicense) {
                    $deviceInfo = MSP_GetDeviceDetail($conn, $sid);
                    $insert = MSP_InsertServiceRequest($conn, $deviceInfo, $newOrderNum, $uninstallDate, $cId, $pId, $customerNumber, $agentEmailId);
                    if ($insert) {
                        array_push($renewResult, $deviceInfo["serviceTag"]);
                        unset($selecDevicesArr[$key]);
                        $installedLicense++;
                    }
                } else {
                    break;
                }
            }
        }
    }
    return $renewResult;
}

function MSP_InsertServiceRequest($db, $deviceDtls, $newOrderNumber, $uninstallDate, $cId, $pId, $customerNum, $agentEmailId)
{
    $custDetails = RSLR_GetCustomerDetails($key, $db, $customerNum, $newOrderNumber);
    $installDate = time();
    $sessionIni = $custDetails['sessionIni'];

    $siteName = $custDetails['siteName'];
    $sessionid = md5(mt_rand());

    $serviceTag = $deviceDtls['serviceTag'];
    $manufacturer = $deviceDtls['machineManufacture'];
    $modelNum = $deviceDtls['machineModelNum'];
    $macAddress = $deviceDtls['macAddress'];
    $os = $deviceDtls['machineOS'];

    $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest (`customerNum`, `orderNum`, `sessionid`, `serviceTag`,
                    `installationDate`, `uninstallDate`, `iniValues`, `agentPhoneId`, `createdTime`, `backupCapacity`,
                    `downloadStatus`, `oldServiceTag`, `revokeStatus`, `machineManufacture`, `machineModelNum`, `pcNo`,
                    `machineName`, `machineOS`, `clientVersion`, `oldVersion`, `assetStatus`, `uninsdormatStatus`,
                    `uninsdormatDate`, `downloadId`, `macAddress`, `processId`, `compId`, `siteName`, `subscriptionKey`,
                     `licenseKey`, `orderStatus`) VALUES ($customerNum, $newOrderNumber, '$sessionid', '$serviceTag',
                     '$installDate', '$uninstallDate', '$sessionIni', '$agentEmailId', '', 5, 'EXE', NULL, 'I', '$manufacturer', '$modelNum', 1,
                     NULL, '$os', NULL, NULL, 0, NULL, 0, NULL, '$macAddress', '$pId', '$cId', '$siteName', NULL, NULL, 'Renewed')";
    $result = redcommand($insertSql, $db);
    return $result;
}


function MSP_AlterTable($db, $dbName, $tableName, $columnName, $dataType, $defaultValue)
{
    $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '$dbName' AND TABLE_NAME = '$tableName' "
        . "AND COLUMN_NAME = '$columnName'";
    $result = find_one($sql, $db);

    if (safe_count($result) > 0) {
        return TRUE;
    } else {
        $alertSql = "ALTER TABLE " . $GLOBALS['PREFIX'] . "agent.$tableName ADD COLUMN $columnName $dataType NULL DEFAULT '$defaultValue'";
        $alterRes = redcommand($alertSql, $db);
        return TRUE;
    }
}



function MSP_GetAutoCustNo($pdo)
{
    $custnum = rand(1000000, 9999999999);

    $stmt = $pdo->prepare("select id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum = ?");
    $stmt->execute([$custnum]);
    $res_cm = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = safe_count($res_cm);
    if ($count > 0) {
        MSP_GetAutoCustNo($pdo);
    } else {
        return $custnum;
    }
}



function MSP_GetAutoOrderNo($pdo)
{
    $ordernum = rand(1000000, 9999999999);

    $stmt = $pdo->prepare("select id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where orderNum = ? OR oldorderNum = ?");
    $stmt->execute([$ordernum, $ordernum]);
    $res_cm = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = safe_count($res_cm);
    if ($count > 0) {
        MSP_GetAutoOrderNo($pdo);
    } else {
        return $ordernum;
    }
}



function MSP_GetEntityDetail($pdo, $cId)
{

    $res = checkModulePrivilege('customers', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    $sql = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = ? LIMIT 1");
    $sql->execute([$cId]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res) > 0) {
        return $res;
    } else {
        return [];
    }
}

function MSP_GetDeviceDetail($db, $sid)
{
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE sid = '$sid' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return [];
    }
}


function MSP_UpdateCustomer()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $pccount = UTIL_GetString('edit_pcCnt', '0');
    $custId = UTIL_GetString('customerId', '0');

    $IsLicensesExist = MSP_IsLicensesExist($conn, $loggedEid, $pccount);
    $LicenseInstalled = MSP_LicensesInstalled($conn, $custId, $pccount);

    if ($IsLicensesExist != "EXIST") {
        $array = array("msg" => 'You have only ' . $IsLicensesExist . ' licenses remained');
    } else if ($LicenseInstalled == FALSE) {
        $array = array("msg" => 'Please enter count greater than installed count');
    } else {
        $updateCustomer = MSP_Update_MSPCustomerLicenses($conn);
        $updateDetails = MSP_Update_MSPCustomerDetails($conn);
        $array = $updateDetails;
    }

    return json_encode($array);
}



function MSP_Update_MSPCustomerLicenses($db)
{

    $cust_id = UTIL_GetString('customerId', '0');
    $pccount = UTIL_GetString('edit_pcCnt', '0');

    $customerUpdateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder set noOfPc = '$pccount' WHERE id = '$cust_id'";
    $customerInsertRes = redcommand($customerUpdateSql, $db);
    if ($customerInsertRes) {
        $array = array("status" => "success", "msg" => "Customer updated successfully");
    } else {
        $array = array("status" => "failed", "msg" => "Customer updation failed, please after some time");
    }
    return $array;
}


function MSP_Update_MSPCustomerDetailsExist($db)
{
    $trialSiteEmail = UTIL_GetString('trialSiteEmail', '0');

    if ($trialSiteEmail == "1" || $trialSiteEmail == 1) {
        $custEmail = UTIL_GetString('edit_email', '-');
        $compEmailExist = RSLR_IsValueExist($conn, $custEmail);
        if ($compEmailExist == TRUE) {
            return array("status" => "failed", "msg" => "Entered email address already in use");
        } else {
            return MSP_Update_MSPCustomerDetails($db);
        }
    } else {
        return MSP_Update_MSPCustomerDetails($db);
    }
}


function MSP_Update_MSPCustomerDetails($db)
{
    $trialSiteEmail = UTIL_GetString('trialSiteEmail', '0');
    $custEmail = UTIL_GetString('edit_email', '');
    $eid = UTIL_GetString('customerEid', '');
    $compWeb = UTIL_GetString('edit_compWeb', '0');
    $companyAddr = UTIL_GetString('edit_companyAddr', '0');
    $companyCity = UTIL_GetString('edit_companyCity', '0');
    $companyState = UTIL_GetString('edit_companyState', '0');
    $companyZip = UTIL_GetString('edit_companyZip', '0');
    $firstName = UTIL_GetString('edit_firstName', '0');
    $lastName = UTIL_GetString('edit_lastName', '0');
    $custEmailStr = '';

    if ($trialSiteEmail == "1" || $trialSiteEmail == 1) {
        $custEmailStr = ", emailId = '" . $custEmail . "'";
    } else {
        $custEmailStr = "";
    }

    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel SET firstName = '$firstName' , lastName = '$lastName' $custEmailStr , address = '$companyAddr' , "
        . "city = '$companyCity' , zipCode = '$companyZip' , province = '$companyState' , website = '$compWeb' WHERE eid = $eid";

    $updateRes = redcommand($updateSql, $db);
    return array("status" => "success", "msg" => "Customer updated successfully");
}



function MSP_GetCustomerDetailGrid()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $custId = UTIL_GetString('customerId', '0');
    $key = '';

    $result = MSP_GetCustomerDetails($conn, $custId);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatCustomerDetails($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}


function MSP_GetCustomerDetails($db, $custId)
{
    $sql = "SELECT C.orderNum, C.emailId, C.contractEndDate, C.downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum ";
    $sql .= "IN(SELECT customerNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE id = '$custId')";
    $res = find_many($sql, $db);
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}


function MSP_FormatCustomerDetails($resultArray)
{
    global $base_url;
    $array = [];
    foreach ($resultArray as $key => $value) {
        $ordernum = MSP_CreatPTag($value['orderNum']);
        $email = MSP_CreatPTag($value['emailId']);
        $endDate = MSP_CreatPTag(date("m-d-Y", $value['contractEndDate']));

        $url = $base_url . 'eula.php?id=' . $value['downloadId'];
        $link = '<a href="#" onclick="customer_link(\'' . $url . '\')" style="color:#0096D6">Copy</a>';
        $rowId = $value['orderNum'];

        $array[] = array(
            "DT_RowId" => $rowId,
            'order' => $ordernum,
            'email' => $email,
            'endDate' => $endDate,
            'link' => $link
        );
    }
    return $array;
}



function MSP_DisableCustomer()
{
    $conn = db_connect();
    $chId = UTIL_GetString('chId', '');

    $sql_change = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel C SET C.status=0 WHERE C.eid='$chId'";
    $updRes = redcommand($sql_change, $conn);
    if ($updRes) {
        return 'done';
    } else {
        return 'fail';
    }
}



function MSP_EnableCustomer()
{
    $conn = db_connect();
    $chId = UTIL_GetString('chId', '');

    $sql_change = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel C SET C.status=1 WHERE C.eid='$chId'";
    $updRes = redcommand($sql_change, $conn);
    if ($updRes) {
        return 'done';
    } else {
        return 'fail';
    }
}

function MSP_GetAvailableLicenses($db, $date, $chnlid)
{
    $sql = "SELECT SUM(licenseCnt) AS totalLincens, SUM(installCnt) as installLincens FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails O WHERE "
        . "O.chnl_id='$chnlid' AND O.contractEndDate >='$date' AND O.licenseCnt != O.installCnt AND O.status=1";
    $res = find_one($sql, $db);

    if (safe_count($res) > 0) {
        $res['availableCnt'] = $res['totalLincens'] - $res['installLincens'];
        return $res;
    } else {
        return 0;
    }
}

function MSP_GetAvailableOrders($db, $date, $chnlid)
{
    $sql = "SELECT id, chnl_id, orderNum, licenseCnt, installCnt, contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails O WHERE "
        . "O.chnl_id='$chnlid' AND O.contractEndDate >='$date' AND O.licenseCnt != O.installCnt AND O.status=1";
    $res = find_many($sql, $db);

    if (safe_count($res) > 0) {
        return $res;
    } else {
        return 0;
    }
}

function MSP_UpdateLicenseCounts($key, $db, $chnl_id, $orderNum)
{
    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.orderDetails O SET O.installCnt = O.installCnt + 1 WHERE O.orderNum = '$orderNum'
                AND O.chnl_id = '$chnl_id'";
    $result = redcommand($sql, $db);
    return $result;
}


function MSP_ExportAllCustomers()
{
    $conn = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $username = $_SESSION["user"]["logged_username"];
    $result = MSP_GetMSPCustomers($key, $conn, $loggedEid);

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Customer");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "First Name");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Last Name");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Customer Email");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Status");
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Created Date");
    $objPHPExcel->getActiveSheet()->setTitle("Customer's Details");

    $objPHPExcel = MSP_FormatCustomerExcel($objPHPExcel, $result);
    $objPHPExcel = MSP_exportOrdersDetails($objPHPExcel);
    $objPHPExcel = MSP_customerDeviceDetailsDetails($objPHPExcel);

    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $username . '_Customers.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}


function MSP_FormatCustomerExcel($objPHPExcel, $resultArray)
{
    $index = 2;
    if (safe_count($resultArray) > 0) {
        foreach ($resultArray as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['companyName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['firstName']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['lastName']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['emailId']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, MSP_GetCustomerStatus($value['status']));
            $firstDt = $value['createdTime'];
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, PHPExcel_Shared_Date::PHPToExcel($firstDt));
            $objPHPExcel->getActiveSheet()->getStyle('F' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    return $objPHPExcel;
}

function MSP_exportOrdersDetails($objPHPExcel)
{

    $conn = db_connect();
    $customerType = $_SESSION["user"]["customerType"];
    $loggedEid = $_SESSION["user"]["cId"];
    $key = '';

    $result = MSP_Get_MSPOrdersGrid($conn, $loggedEid, $customerType);

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Order Number");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Purchase Date");
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Expiring Date");
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Used/Total");

    $objPHPExcel->getActiveSheet()->setTitle("Order's Details");

    $index = 2;
    if (safe_count($result) > 0) {
        foreach ($result as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['orderNum']);

            $purchaseDate = $value['purchaseDate'];
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, PHPExcel_Shared_Date::PHPToExcel($purchaseDate));
            $objPHPExcel->getActiveSheet()->getStyle('B' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");

            $contractEDate = $value['contractEndDate'];
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, PHPExcel_Shared_Date::PHPToExcel($contractEDate));
            $objPHPExcel->getActiveSheet()->getStyle('C' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");

            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['installCnt'] . '/' . $value['licenseCnt']);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    return $objPHPExcel;
}

function MSP_customerDeviceDetailsDetails($objPHPExcel)
{

    $conn = db_connect();
    $customerType = $_SESSION["user"]["customerType"];
    $loggedEid = $_SESSION["user"]["cId"];
    $key = '';

    $result = MSP_getResellerCustomerDtl();

    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(2);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Order number");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Site name");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Device name");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Installed Date");
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Contract End Date");
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Status");

    $objPHPExcel->getActiveSheet()->setTitle("Device's Details");
    $index = 2;
    if (safe_count($result) > 0) {
        foreach ($result as $key => $value) {

            $custNo = $value['customerNum'];
            $ordNo = $value['orderNum'];
            $compId = $value['compId'];
            $procId = $value['processId'];

            $sql_ser = "select S.orderNum,S.siteName,S.installationDate,S.uninstallDate,S.orderStatus,S.serviceTag from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.customerNum='$custNo' and orderNum='$ordNo' and processId='$procId' and compId='$compId'";
            $res = find_many($sql_ser, $conn);
            foreach ($res as $key => $value1) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value1['orderNum']);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value1['siteName']);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value1['serviceTag']);

                $installDate = $value1['installationDate'];
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, PHPExcel_Shared_Date::PHPToExcel($installDate));
                $objPHPExcel->getActiveSheet()->getStyle('D' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");

                $uninstallDate = $value1['uninstallDate'];
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, PHPExcel_Shared_Date::PHPToExcel($uninstallDate));
                $objPHPExcel->getActiveSheet()->getStyle('E' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");

                $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $value1['orderStatus']);
                $index++;
            }
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
    return $objPHPExcel;
}

function MSP_getResellerCustomerDtl()
{

    $conn = db_connect();
    $customerType = $_SESSION["user"]["customerType"];
    $loggedEid = $_SESSION["user"]["cId"];
    $key = '';

    $sql = "select C.customerNum,C.orderNum,C.siteName,C.processId,C.compId from " . $GLOBALS['PREFIX'] . "agent.customerOrder C where C.compId in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where channelId = '$loggedEid') group by C.customerNum,C.orderNum";
    $res = find_many($sql, $conn);

    return $res;
}

function MSP_FormatPurchaseOrderExcel($objPHPExcel, $resultArray)
{
    $index = 2;
    foreach ($resultArray as $key => $value) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['companyName']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['firstName']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['lastName']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['emailId']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, MSP_GetCustomerStatus($value['status']));
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $value['createdt']);
        $index++;
    }

    return $objPHPExcel;
}




function MSP_GetMSPSites($conn, $loggedEid)
{
    $sql = "select C.customerNum,C.coustomerFirstName, C.licenseKey, C.orderNum, C.compId, C.processId, C.downloadId,C.siteName,count(S.sid) installedCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder C left join
                " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum and S.revokeStatus='I'
                where C.compId = '$loggedEid' group by C.customerNum,C.orderNum";
    $res = find_many($sql, $conn);
    return $res;
}


function MSP_FormatSitesGrid($resultArray)
{
    global $base_url;
    $array = [];
    foreach ($resultArray as $key => $value) {
        $siteName = MSP_CreatPTag($value['siteName']);
        $noOfInstall = MSP_CreatPTag($value['installedCnt']);
        $downloadId = $value['downloadId'];

        if ($downloadId == '' || $downloadId == 'undefined') {
            $url = "'Url is not available'";
        } else {
            $url = "'" . $base_url . 'eula.php?id=' . $downloadId . "'";
        }
        $copyUrl = '<a href="#" onclick="customer_link(' . $url . ')" style="color:#0096D6">Copy Url</a>';

        $rowId = $value['downloadId'];
        $array[] = array(
            "DT_RowId" => $rowId,
            'sitename' => $siteName,
            'noofinstall' => $noOfInstall,
            'copyurl' => $copyUrl
        );
    }
    return $array;
}

function MSP_GetOldProvisionDetails($pdo, $compId, $processId, $emailId)
{
    $stmt = $pdo->prepare("SELECT customerNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.compId = ? AND C.processId = ? ORDER BY C.id ASC LIMIT 1");
    $stmt->execute([$compId, $processId]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if (safe_count($res) > 0) {
        return $res;
    }
}





function MSP_UploadClientLogo()
{
    $db = db_connect();
    $jsonlist = [];
    $eid = UTIL_GetString('eid', '');
    $res = MSP_GetEntityDetail($db, $eid);
    $clientlogo = $res['clientlogo'];

    if ($clientlogo == 'default') {
        $companyname = $res['companyName'];
        $UIDirectory = CUST_CreateClient_UIDirectory($companyname);
        $updatePath = MSP_Update_UIDirectoryPath($db, $eid, $UIDirectory);
        $clientlogo = $UIDirectory;
    }

    if (is_array($_FILES)) {
        $userclientlogo = $_FILES['upload_logo']['name'];
        if (strpos($clientlogo, '.png') !== false || strpos($clientlogo, '.jpg') !== false || strpos($clientlogo, '.jpeg') !== false) {

            $array = explode('/', $clientlogo);
            array_pop($array);
            $path = implode('/', $array);
            $path = $path . "/" . $userclientlogo;
        } else {
            $path = "$clientlogo/" . $userclientlogo;
        }

        if (is_uploaded_file($_FILES['upload_logo']['tmp_name'])) {
            move_uploaded_file($_FILES['upload_logo']['tmp_name'], $path);
            chmod($path, 0777);
        }

        $sql = "update " . $GLOBALS['PREFIX'] . "agent.channel set clientlogo = '$path' where eid = '$eid'";
        $result = redcommand($sql, $db);

        if ($result) {
            $jsonlist = array('msg' => 'valid');
        } else {
            $jsonlist = array('msg' => 'invalid');
        }
    }
    echo json_encode($jsonlist);
}

function MSP_GetProfileDBUpload($key, $db, $mid, $eid, $path)
{

    $sqlres = PRFL_GetProfileExportList($key, $db, $mid);
    db_change($GLOBALS['PREFIX'] . 'event', $db);
    if ($sqlres) {
        $db = new SQLDBFirstCustomer($path);
        if (!$db) {
        } else {
            chmod($path . "/profile_" . time() . ".db", 0777);

            $sql = "
                CREATE TABLE main (
                mid         INT PRIMARY KEY NOT NULL,
                menuItem    TEXT,
                type        TEXT,
                parentId    TEXT,
                profile     TEXT,
                dart	    TEXT,
                variable    TEXT,
                varValue    TEXT,
                shortDesc   TEXT,
                description TEXT,
                image       TEXT,
                tileSize    TEXT,
                tileDesc    TEXT,
                iconPos     TEXT,
                OS          TEXT,
                page        TEXT,
                lang        TEXT,
                status      TEXT,
                themeColo   TEXT,
                themeFont   TEXT,
                theme       TEXT,
                follow      TEXT,
                addon	    TEXT,
                addonDart   TEXT,
                authFalg    TEXT,
                usageType   TEXT,
                sequence    TEXT,
                isRD        TEXT,
                scheduleTime TEXT);";

            $sql1 = "
                CREATE TABLE ConfigurationMaster
                (Id               INT PRIMARY KEY NOT NULL,
                Vesrion           TEXT,
                DART              TEXT,
                Description       TEXT);";

            $sql2 = "
                    CREATE TABLE ConfigurationDetails
                    (Id           INT PRIMARY KEY NOT NULL,
                    cid           NUMERIC,
                    Variable      TEXT,
                    VarType       TEXT,
                    VarValue      TEXT,
                    wn_id         TEXT,
                    def           NUMERIC,
                    scop          NUMERIC,
                    pwsc          NUMERIC,
                    descval       TEXT);";

            $sql3 = "
                    CREATE TABLE ServiceLog_Master
                    (mid             INT PRIMARY KEY NOT NULL,
                    dartNo           INT,
                    tileName         TEXT,
                    varValues        TEXT,
                    successDesc      TEXT,
                    terminateDesc    TEXT,
                    Type             TEXT);";

            $sql4 = "
                    CREATE TABLE Status_Master
                    (sm_id           INT PRIMARY KEY NOT NULL,
                    dart             NUMERIC,
                    statusName       TEXT,
                    isEnabled        TEXT);";

            $sql5 = "
                    CREATE TABLE Status_Details
                    (sd_id           INT PRIMARY KEY NOT NULL,
                    page             TEXT,
                    profile          TEXT,
                    varValues        TEXT,
                    variable         TEXT,
                    dartfrom         TEXT,
                    darttoExecute    TEXT,
                    description      TEXT,
                    logicType        TEXT,
                    logicPara        TEXT,
                    dispBtn          TEXT,
                    url              TEXT,
                    status           INTEGER,
                    title            TEXT,
                    parent           TEXT,
                    UISection        TEXT,
                    GUIType          TEXT,
                    addCss           TEXT,
                    functiontoCall   TEXT,
                    ImageFileName    TEXT,
                    usageType        TEXT);";

            $ret = $db->exec($sql);
            $ret1 = $db->exec($sql1);
            $ret2 = $db->exec($sql2);
            $ret3 = $db->exec($sql3);
            $ret4 = $db->exec($sql4);
            $ret5 = $db->exec($sql5);
            if (!$ret) {
                echo $db->lastErrorMsg();
            } else {

                foreach ($sqlres as $key => $value) {

                    $return .= '(';
                    $return .= '"' . $value['mid'] . '", ';
                    $return .= '"' . $value['menuItem'] . '", ';
                    $return .= '"' . $value['type'] . '", ';
                    $return .= '"' . $value['parentId'] . '", ';
                    $return .= '"' . $value['profile'] . '", ';
                    $return .= '"' . $value['dart'] . '", ';
                    $return .= '"' . $value['variable'] . '", ';
                    $return .= '"' . $value['varValue'] . '", ';
                    $return .= '"' . $value['shortDesc'] . '", ';
                    $return .= '"' . $value['description'] . '", ';
                    $return .= '"' . $value['image'] . '", ';
                    $return .= '"' . $value['tileSize'] . '", ';
                    $return .= '"' . $value['tileDesc'] . '", ';
                    $return .= '"' . $value['iconPos'] . '", ';
                    $return .= '"' . $value['OS'] . '", ';
                    $return .= '"' . $value['page'] . '", ';
                    $return .= '"' . $value['lang'] . '", ';
                    $return .= '"' . $value['status'] . '", ';
                    $return .= '"' . $value['themeColo'] . '", ';
                    $return .= '"' . $value['themeFont'] . '", ';
                    $return .= '"' . $value['theme'] . '", ';
                    $return .= '"' . $value['follow'] . '", ';
                    $return .= '"' . $value['addon'] . '", ';
                    $return .= '"' . $value['addonDart'] . '", ';
                    $return .= '"' . $value['authFalg'] . '", ';
                    $return .= '"' . $value['usageType'] . '", ';
                    $return .= '"' . $value['sequence'] . '",';
                    $return .= '"' . '1' . '",';
                    $return .= '"' . 'null' . '"';
                    $return .= "),";

                    if ($value['type'] == 'L3') {
                        $seq .= "," . $value['sequence'] . ",";
                    }
                    $seq = ltrim($seq, ",");
                    $seq = rtrim($seq, ",");
                }
                $return = rtrim($return, ",");
                $sql1 = "INSERT INTO main (mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,"
                    . "tileDesc,iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart,authFalg,usageType,sequence,"
                    . "isRD,scheduleTime) VALUES" . $return;

                $seqQuery = PRFL_GetSequenceQuery($key, $db, $seq);
                $seqQueryres = explode("#", $seqQuery);
                $sql2 = "INSERT INTO ConfigurationMaster (Id, Vesrion,DART,Description) VALUES" . $seqQueryres[0];

                $seqDetailQuery = PRFL_GetSequenceDetailQuery($key, $db, $seqQueryres[1]);
                $sql3 = "INSERT INTO ConfigurationDetails (Id, cid, Variable, VarType,VarValue, wn_id, def, scop, pwsc, descval) VALUES" . $seqDetailQuery;

                $servicelogQuery = PRFL_GetServiceLogMasterQuery($key, $db);
                $sql4 = "INSERT INTO ServiceLog_Master (mid, dartNo, tileName, varValues, successDesc, terminateDesc, Type) VALUES" . $servicelogQuery;

                $statusmasterQuery = PRFL_GetStatusMasterQuery($key, $db);
                $sql5 = "INSERT INTO Status_Master (sm_id, dart, statusName, isEnabled) VALUES" . $statusmasterQuery;

                $statusdetailQuery = PRFL_GetStatusDetailQuery($key, $db);
                $sql6 = "INSERT INTO Status_Details (sd_id, page, profile, varValues, variable, dartfrom, dartToExecute, description, logicType, logicPara, dispBtn, url, status, title, parent, UISection, GUIType, addCss, functionToCall, ImageFileName, usageType) VALUES" . $statusdetailQuery;

                $ret = $db->exec($sql1);
                $ret1 = $db->exec($sql2);
                $ret2 = $db->exec($sql3);
                $ret3 = $db->exec($sql4);
                $ret4 = $db->exec($sql5);
                $ret5 = $db->exec($sql6);
                if (!$ret) {
                } else {

                    $name = "/profile_'" . time() . "'.db";
                    readfile($path . $name);
                    return $res;
                    exit;
                }
            }
            $db->close();
        }
    }
}

function MSP_addsignUp()
{

    $key = '';
    $conn = db_connect();
    $fullname = url::requestToAny('fullname');
    $lname = url::requestToAny('lname');
    $companyname = url::requestToAny('companyname');
    $emailid = url::requestToAny('emailid');

    $retVal = RSLR_AddSignupCustomer($key, $conn, $fullname, $lname, $companyname, $emailid, 'dashboard');
    echo json_encode($retVal);
}

function MSP_GetTrialDownloadURL()
{
    $cId = $_SESSION['user']['cId'];
    if (isset($cId)) {
        global $base_url;
        $response = [];
        $db = db_connect();
        $sql = "SELECT downloadId, CH.emailId, CH.firstName FROM " . $GLOBALS['PREFIX'] . "agent.channel CH," . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.compId=CH.eid AND CH.eid='$cId' AND CH.trialEnabled='1' ORDER BY id ASC LIMIT 1;";
        $res = find_one($sql, $db);
        if (safe_count($res) > 0) {
            $download = $base_url . "eula.php?id=" . trim($res['downloadId']);
            $userName = trim($res['firstName']);
            $userEmail = trim($res['emailId']);

            $clientMail = RSLR_ClientURLEmail($db, $userName, $userEmail, $download, "en");
            $response = array('status' => "SUCCESS", 'link' => $download);
        } else {
            $sql = "SELECT C.coustomerFirstName, C.emailId, C.downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.skuMaster S WHERE C.SKUNum=S.skuRef AND S.trial='1' AND C.compId='$cId' LIMIT 1";
            $res = find_one($sql, $db);
            $download = $base_url . "eula.php?id=" . trim($res['downloadId']);
            $userName = trim($res['coustomerFirstName']);
            $userEmail = trim($res['emailId']);
            $response = array('status' => "SUCCESS", 'link' => $download);
        }
        echo json_encode($response);
    } else {
        header('Location: ../index.php');
    }
}

function MSP_GetProcessSetupDetails()
{
    $db = db_connect();
    $cId = $_SESSION['user']['cId'];
    $downloadId = url::requestToAny('urlField');

    if (isset($downloadId)) {

        $sql = "SELECT P.processName, C.sessionid, P.pId, P.deployPath32, P.deployPath64, P.androidsetup, P.macsetup, P.linuxsetup FROM " . $GLOBALS['PREFIX'] . "agent.processMaster P, " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE P.cId=C.compId AND P.pId=C.processId AND C.downloadId='$downloadId' LIMIT 1";
        $res = find_one($sql, $db);
        echo json_encode($res);
    }
}

function MSP_GetCustDnlDetails()
{
    global $base_url;
    $db = db_connect();
    $custId = url::requestToAny('chId');
    $array = [];

    if (isset($custId)) {

        $sql = "select C.id,C.customerNum,C.orderNum,C.downloadId,C.siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder C where C.compId='$custId'";
        $res = find_many($sql, $db);

        if (safe_count($res) > 0) {
            foreach ($res as $value) {
                $ordernum = MSP_CreatPTag($value['orderNum']);
                $site = MSP_CreatPTag(UTIL_GetTrimmedGroupName($value['siteName']));

                $url = $base_url . 'eula.php?id=' . $value['downloadId'];
                $link = '<a href="#" onclick="customer_link(\'' . $url . '\')" style="color:#0096D6">Copy</a>';
                $rowId = $value['orderNum'];

                $array[] = array($ordernum, $site, $url);
            }
        } else {
            $array[] = array("orderNum" => '', "siteName" => '', "url" => '');
        }


        echo json_encode($array);
    }
}
