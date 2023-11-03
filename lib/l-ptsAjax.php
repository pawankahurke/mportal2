<?php





include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-user.php';
include_once '../lib/l-pts.php';
include_once '../lib/l-pts_sb.php';
include_once '../lib/l-stripe.php';
include_once '../lib/l-ptsReport.php';
include_once '../lib/l-redis.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

nhRole::dieIfnoRoles(['site']); // roles: site

if (url::issetInRequest('function')) { // roles: site
    $key = '';
    $functionName = url::requestToText('function'); // roles: site
    $functionName($key);
}






function get_AllCustomerList()
{
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];
    $key = '';
    $allCustomers = PTS_GetAllCustomers($key, $pdo, $ch_id);

    $tempArr = '';
    if (safe_count($allCustomers) > 0) {
        foreach ($allCustomers as $key => $val) {
            $custNo = $val['customerNum'];
            $tempArr .= '<option value="' . $custNo . '">' . $custNo . '</option>';
        }
    } else {
        $tempArr = '<option value="">No data available</option>';
    }
    echo json_encode($tempArr);
}

function get_OrderNumber()
{
    $custNumber = url::issetInRequest('custNum') ? url::requestToAny('custNum') : "";
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];
    $key = '';
    $i = 0;
    $order = '';
    $allCustomers = PTS_GetAllOrdersForCustomer($key, $pdo, $custNumber);

    if (safe_count($allCustomers) > 0) {
        $order = '<option "all">All</option>';
        foreach ($allCustomers as $value) {
            $orders = $value['orderNum'];
            $order .= '<option "' . $orders . '">' . $orders . '</option>';
        }
    } else {
        $order = '<option "">No data available</option>';
    }
    echo json_encode($order);
}


function get_CustomerGrid_old($key)
{
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];

    $allCustomers = PTS_GetAllCustomers($key, $pdo, $ch_id);
    $jsonResult = format_CustomerGrid($allCustomers);
    $totalCustomers = safe_count($allCustomers);
    $jsonData = array("draw" => 0, "recordsTotal" => $totalCustomers, "recordsFiltered" => $totalCustomers, "data" => $jsonResult);
    echo json_encode($jsonData);
}



function get_CustomerGrid($key)
{
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];



    $allOrders = PTS_GetEntityOrder($key, $pdo, $ch_id);
    $jsonResult = format_SB_OrderGridList($allOrders);

    $totalOrders = safe_count($allOrders);

    $jsonData = array("draw" => 0, "recordsTotal" => $totalOrders, "recordsFiltered" => $totalOrders, "data" => $jsonResult);
    echo json_encode($jsonData);
}


function get_exportOrder()
{
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];
    $username = $_SESSION["user"]["logged_username"];
    $allOrders = PTS_GetEntityOrder($key, $pdo, $ch_id);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Order Number");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Purchase Date");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Sku Name");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Order Key");
    $objPHPExcel = PTS_FormatOrderExcel($objPHPExcel, $allOrders);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $username . '_Orders.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function PTS_FormatOrderExcel($objPHPExcel, $result)
{
    $index = 2;
    if (safe_count($result) > 0) {
        foreach ($result as $key => $value) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['orderNum']);
            if ($value['purchaseDate'] > 0) {
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, date("m/d/Y", $value['orderDate']));
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, "-");
            }
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['skuDesc']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['licenseKey']);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available');
    }
    return $objPHPExcel;
}

function get_OrderGrid($key)
{
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];
    $customerNumber = UTIL_GetString('custNumber', '');

    $allSites = PTS_GetCustomerOrders($key, $conn, $customerNumber);
    $jsonResult = format_OrderGrid($allSites, $conn);
    $totalSites = safe_count($allSites);
    $jsonData = array("draw" => 0, "recordsTotal" => $totalSites, "recordsFiltered" => $totalSites, "data" => $jsonResult);
    echo json_encode($jsonData);
}


function get_OrdersDeviceGrid($key)
{
    $custId = UTIL_GetString('custId', '');
    $processId = UTIL_GetString('procId', '');
    $custNumber = UTIL_GetString('custNum', '');
    $ordNumber = UTIL_GetString('ordNum', '');

    $allDevices = PTS_GetSitesDevices($key, null, $custId, $processId, $custNumber, $ordNumber);
    $allDevicesStatus = getAllMachinesStatus($custNumber);

    $jsonResult = format_OrdersDeviceGrid($allDevices, $allDevicesStatus);
    $totalDevices = safe_count($allDevices);
    $jsonData = array("draw" => 0, "recordsTotal" => $totalDevices, "recordsFiltered" => $totalDevices, "data" => $jsonResult);
    echo json_encode($jsonData);
}








function format_CustomerGrid($customerResult)
{
    $jsonArray = array();
    if (safe_count($customerResult) > 0) {
        foreach ($customerResult as $key => $value) {
            $rowID = $value['compId'] . '---' . $value['processId'] . '---' . $value['customerNum'] . '---' . 0;
            $customerNum = createUnLimitedPTag($value['customerNum'], $value['customerNum']);
            $customerName = createUnLimitedPTag($value['coustomerFirstName'], $value['coustomerFirstName']);
            $customerStatus = creatPTag('Enabled', 'Enabled');
            $emailId = createUnLimitedPTag($value['emailId'], $value['emailId']);
            $jsonArray[] = array("DT_RowId" => $rowID, 'custNumber' => $customerNum, 'custEmail' => $emailId, 'custName' => $customerName);
        }
    }
    return $jsonArray;
}


function format_SB_OrderGrid($orderResult)
{
    $jsonArray = array();
    if (safe_count($orderResult) > 0) {
        foreach ($orderResult as $key => $value) {
            $rowID = $value['id'];;
            $orderNum = createUnLimitedPTag($value['orderNum'], $value['orderNum']);
            $skuName = createUnLimitedPTag($value['skuDesc'], $value['skuDesc']);
            $createdDate = createUnLimitedPTag(date('d-m-Y H:i:s', $value['orderDate']), date('d-m-Y H:i:s', $value['orderDate']));
            $orderKey = createUnLimitedPTag($value['licenseKey'], $value['licenseKey']);

            $jsonArray[] = array("DT_RowId" => $rowID, 'orderNumber' => $orderNum, 'skuName' => $skuName, 'createdDate' => $createdDate, 'orderKey' => $orderKey);
        }
    }
    return $jsonArray;
}


function format_SB_OrderGridList($orderResult)
{
    $jsonArray = array();
    if (safe_count($orderResult) > 0) {
        foreach ($orderResult as $value) {
            $rowID = $value['id'];;
            $orderNum = createUnLimitedPTag($value['orderNum'], $value['orderNum']);
            $skuName = createUnLimitedPTag($value['skuDesc'], $value['skuDesc']);
            $createdDate = createUnLimitedPTag(date('d-m-Y H:i:s', $value['orderDate']), date('d-m-Y H:i:s', $value['orderDate']));
            $orderKey = createUnLimitedPTag($value['licenseKey'], $value['licenseKey']);

            $jsonArray[] = array("DT_RowId" => $rowID, 'orderNumber' => $orderNum, 'skuName' => $skuName, 'createdDate' => $createdDate, 'orderKey' => $orderKey);
        }
    }
    return $jsonArray;
}


function format_OrderGrid($ordersResult, $conn)
{
    $jsonArray = array();
    if (safe_count($ordersResult) > 0) {
        foreach ($ordersResult as $key => $value) {
            $isExpiring = isContractExpiring($value['contractEndDate'], $value['orderDate']);
            $rowID = $value['compId'] . '---' . $value['processId'] . '---' . $value['customerNum'] . '---' . $value['orderNum'] . '---' . $isExpiring . '---' . $value['id'];
            $installedCnt = creatPTag($value['installedCnt'], $value['installedCnt']);
            $orderNum = creatPTag($value['orderNum'], $value['orderNum']);
            $skuDetail = PTS_GetSKUDetails('', $conn, "skuRef", $value['SKUNum']);
            if ($skuDetail['trial'] == '1') {
                $orderStatus = 'Trial';
            } else {
                $orderStatus = PTS_GetOrdersPayementStatus($key, $value['customerNum'], $value['orderNum'], $value['payRefNum']);
            }
            $orderStatus = creatPTag($orderStatus, $orderStatus);
            $jsonArray[] = array("DT_RowId" => $rowID, 'orderNum' => $orderNum, 'installCount' => $installedCnt);
        }
    }
    return $jsonArray;
}



function format_OrdersDeviceGrid($devicesResult, $devicesStatus)
{
    $jsonArray = array();
    if (safe_count($devicesResult) > 0) {
        $pdo = pdo_connect();
        $today = time();
        foreach ($devicesResult as $key => $value) {
            $_SESSION['searchType'] = "ServiceTag";
            $_SESSION['searchValue'] = $value['serviceTag'];

            if ($_SESSION['user']['loginType'] == 'PTS') {
                $devicename = creatPTag($value['serviceTag'], $value['serviceTag']);
            } else {
                $devicename = creatAnchorTag($value['serviceTag'], $value['serviceTag'], $value['siteName'], $value['censusid']);
            }
            if ($value['installationDate'] != NULL || $value['installationDate'] != "") {
                $install_Date = date("Y-m-d", $value['installationDate']);
            } else {
                $install_Date = "-";
            }
            $installDt = creatPTag($install_Date, $install_Date);
            $uninstallDt = creatPTag(date("Y-m-d", $value['uninstallDate']), date("Y-m-d", $value['uninstallDate']));
            $status = ($value['uninstallDate'] < $today) ? creatPTag('Inactive', 'Inactive') : creatPTag('Active', 'Active');
            $sid = $value['sid'];

            $chatLink = 'https://egain.compucom.com/system/templates/chat/OdTzSrvceM/chat.html?subActivity=Chat&entryPointId=1132&templateName=OdTzSrvceM&languageCode=en&countryCode=US&ver=v11&fieldname_1=OD Tech Services&fieldname_2=&fieldname_3=&fieldname_4=&fieldname_6=&fieldname_7=OD Tech Services&fieldname_8=Technician Initiated Chat Support Request';
            $formattedLink = formatChatLink($chatLink, $value['coustomerFirstName'], $value['coustomerLastName'], $value['emailId']);
            $machineOSImg = PTSAJX_GetMachineStatusImg($value['serviceTag'], $devicesStatus[$value['serviceTag']]['os'], $devicesStatus[$value['serviceTag']]['status']);
            $rdLink = "../resolutions/index.php?machine=" . $value['serviceTag'] . "&custnum=" . $value['customerNum'] . "&ordnum=" . $value['orderNum'] . "&censusid=" . $value['censusid'] . "&type=myacnt";
            $rowId = $devicename . '---' . $sid;
            $remotrType = "LMI";
            $jsonArray[] = array(
                "DT_RowId" => $rowId,
                'devicename' => $devicename,
                'installDt' => $installDt,
                'uninstallDt' => $uninstallDt,
                'status' => $status,
                'onlineStatus' => '<img style="height: 17px !important;width: 22px;" title="' . $devicesStatus[$value['serviceTag']]['status'] . '" src="../js/entitlement/images/' . $machineOSImg . '.png" alt="" localized=""><a href="' . $rdLink . '" target="_blank"><span style="font-size: 18px;margin-left: 60%;margin-top: -7%;" class="glyphicon glyphicon-cog" id="addDeviceIncrese" style="cursor: pointer;" title="Remote Diagnosis" localized=""></span></a>'
                    . '<a href="' . $formattedLink . '" target="_blank"><img id="myAccount_chatlink" title="Contact Support Team- Tech" src="../vendors/images/chat-icon-grey.svg" localized="" style="margin-left: 10px;"></a><img onclick="Machineremote(\'' . $value['serviceTag'] . '\')" id="LMI_popupLink" title="Remote Machine LMI" src="../vendors/images/lmichat-icon-grey.png" width="20" height="20" localized="" style="margin-left: 6px;cursor: pointer;">'
            );
        }
    }
    return $jsonArray;
}

function creatPTag($ptag_val, $ptag_title)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        if (strlen($ptag_val) > 15) {
            $ptagStr = '<p class="ellipsis" title="' . $ptag_title . '">' . substr($ptag_val, 0, 13) . '..</p>';
        } else {
            $ptagStr = '<p class="ellipsis" title="' . $ptag_title . '">' . $ptag_val . '</p>';
        }
    }
    return trim($ptagStr);
}

function get_SKUForCountry($key)
{
    $skuResult = '';
    $pdo = pdo_connect();
    $isKeyValid = DASH_ValidateKey($key);
    $country = UTIL_GetString('ccode', '');
    $osType = UTIL_GetString('platform', '');
    $result = PTS_GetSKUForCountryWithOS($key, $conn, $country, "", 1, $osType);

    if (safe_count($result) > 0) {
        $skuResult = array("status" => "SUCCESS", "records" => $result);
    } else {
        $skuResult = array("status" => "FAILED", "records" => []);
    }
    echo json_encode($skuResult);
}

function get_ProvisionSKU($key)
{
    $skuResult = '';
    $pdo = pdo_connect();
    $isKeyValid = DASH_ValidateKey($key);

    $result = PTS_SB_GetProvisionSKU($key, $conn);

    if (safe_count($result) > 0) {
        $skuResult = array("status" => "SUCCESS", "records" => $result);
    } else {
        $skuResult = array("status" => "FAILED", "records" => []);
    }
    echo json_encode($skuResult);
}

function get_SKUSPrices($key)
{
    $pdo = pdo_connect();
    $skuResult = '';
    $selSku = UTIL_GetString('sel_skus', '');
    $skuType = UTIL_GetString('skuType', '');
    $selSkuArr = explode(",", $selSku);
    $result = getSKUPrice($key, $conn, $selSkuArr, $skuType);
    echo $result;
}

function get_UpdatedSKUSPrices($key)
{
    $pdo = pdo_connect();
    $skuResult = '';
    $selSku = UTIL_GetString('sel_skus', '');
    $skuType = UTIL_GetString('skuType', '');
    $selSkuArr = safe_json_decode($selSku, true);
    $result = getUpdatedSKUPrice($key, $conn, $selSkuArr, $skuType);
    echo $result;
}

function get_RenewUpgradeSKUForCountry($key)
{
    $skuResult = '';
    $pdo = pdo_connect();
    $isKeyValid = DASH_ValidateKey($key);

    $country = UTIL_GetString('ccode', '');
    $skuRef = UTIL_GetString('skuRef', '');
    $skuType = UTIL_GetString('skuType', '');
    $result = PTS_GetSKUForCountry($key, $conn, $country, $skuRef, $skuType);

    if (safe_count($result) > 0) {
        $skuResult = array("status" => "SUCCESS", "records" => $result);
    } else {
        $skuResult = array("status" => "FAILED", "records" => []);
    }
    echo json_encode($skuResult);
}

function get_RenewDetails($key)
{
    $pdo = pdo_connect();
    $id = UTIL_GetString('orderId', '');
    $custDetails = PTS_GetOrderDetails($key, $conn, $id);
    echo json_encode($custDetails);
}

function create_NewProvision($key)
{
    $pdo = pdo_connect();
    $cId = $_SESSION['user']['cId'];

    $customerCountry = UTIL_GetString('ccode', '');
    $SKU = UTIL_GetString('skuplan', '');
    $customerNumber = UTIL_GetString('custNumber', '');
    $orderNumber = UTIL_GetString('orderNumber', '');
    $customerFirstName = UTIL_GetString('custFName', '');
    $customerLastName = UTIL_GetString('custLName', '');
    $customerEmailId = UTIL_GetString('custEmail', '');
    $customerPhone = UTIL_GetString('custPhone', '');
    $orderDate = UTIL_GetString('orderDate', '');
    $ServiceDate = UTIL_GetString('ServiceDate', '');
    $osType = UTIL_GetString('osType', '');

    $result = PTS_SB_CreateNewProvision($key, $conn, $SKU, $customerNumber, $orderNumber, $customerFirstName, $customerLastName, $customerEmailId, $customerPhone, $orderDate, $cId);
    echo json_encode($result);
}

function get_CustomerDetails($key)
{

    $pdo = pdo_connect();
    $compId = UTIL_GetString("compId", "");
    $proccessId = UTIL_GetString("proccessId", "");
    $custNumber = UTIL_GetString("custNumber", "");
    $orderNumber = UTIL_GetString("orderNumber", "");
    $custDetails = PTS_GetCustomerDetails($key, $conn, $compId, $proccessId, $custNumber, $orderNumber);
    $skuDetail = PTS_GetSKUDetails('', $conn, "skuRef", $custDetails['SKUNum']);
    $Order_Status = PTS_GETOrderStatus($key, $conn, $custNumber, $orderNumber);
    $Install_Status = PTS_GETInstallStatus($key, $conn, $custNumber, $orderNumber);
    $today = time();
    if ($Order_Status[0]['contractEndDate'] < $today) {
        $order_status = "Expired";
    } else {
        if (safe_sizeof($Install_Status) > 0) {
            $revokeStatus = $Install_Status[0]['revokeStatus'];
            if ($Install_Status[0]['uninsdormatStatus'] === '' || $Install_Status[0]['uninsdormatStatus'] === NULL) {
                if ($Install_Status[0]['downloadStatus'] == 'EXE') {
                    $order_status = "Install";
                } else if ($Install_Status[0]['downloadStatus'] == 'CANCEL') {
                    $order_status = "CANCEL";
                } else {
                    $order_status = "NotInstall";
                }
            } else {
                $order_status = "Unistalled-Active";
            }
        } else {
            $order_status = "Active Recurring";
            $revokeStatus = "NA";
        }
    }

    if ($skuDetail['trial'] == '1') {
        $trialValue = '1';
    } else {
        $trialValue = '0';
    }
    $custDetails['orderDate'] = $custDetails['orderDate1'];
    $custDetails['contractEndDate'] = date("Y-m-d", $custDetails['contractEndDate']);
    $deviceDetails = PTS_GetSitesDevices($key,  null, $compId, $proccessId, $custNumber, $orderNumber);

    $historyDetails = PTS_GetOrderHistory($key, $conn, $compId, $proccessId, $custNumber, $orderNumber, array());

    $resultArray = array("details" => $custDetails, "devices" => $deviceDetails, "history" => $historyDetails, "trial" => $trialValue, "orderstatus" => $order_status, "revokeStatus" => $revokeStatus);
    echo json_encode($resultArray);
}

function isContractExpiring($contractEndDate, $orderDate)
{

    $oneMonth = strtotime("+30 days");
    $ten_days = strtotime("+10 days");

    $temp = abs($contractEndDate - $orderDate);
    $days = floor($temp / (60 * 60 * 24));
    if ($days == 10) {
        return 0;
    } else if ($contractEndDate <= $oneMonth) {
        return 1;
    } else {
        return 0;
    }
}

function renew_Provision($key)
{
    $pdo = pdo_connect();
    $cId = $_SESSION['user']['cId'];

    $customerNumber = UTIL_GetString('custNumber', '');
    $oldOrderNumber = UTIL_GetString('oldOrderNumber', '');
    $oldSkuRef = UTIL_GetString('oldSkuRef', '');
    $newOrderNumber = UTIL_GetString('newOrderNumber', '');
    $newSkuRef = UTIL_GetString('newSkuRef', '');

    $result = PTS_RenewProvision($key, $conn, $cId, $customerNumber, $oldOrderNumber, $oldSkuRef, $newOrderNumber, $newSkuRef);
    echo json_encode($result);
}

function upgrade_Provision($key)
{
    $pdo = pdo_connect();
    $cId = $_SESSION['user']['cId'];

    $customerNumber = UTIL_GetString('custNumber', '');
    $oldOrderNumber = UTIL_GetString('oldOrderNumber', '');
    $oldSkuRef = UTIL_GetString('oldSkuRef', '');
    $newOrderNumber = UTIL_GetString('newOrderNumber', '');
    $newSkuRef = UTIL_GetString('newSkuRef', '');

    $result = PTS_UpgradeProvision($key, $conn, $cId, $customerNumber, $oldOrderNumber, $oldSkuRef, $newOrderNumber, $newSkuRef);
    echo json_encode($result);
}

function check_Payment($key)
{
    global $base_url;
    $pdo = pdo_connect();
    echo "Base_Url--->" . $base_url;
    die();
    $customerNumber = UTIL_GetString('custNumber', '');
    $orderNumber = UTIL_GetString('orderNumber', '');
    $orderDetails = PTS_GetOrderDetailsByNumber($key, $conn, $customerNumber, $orderNumber);
    $downloadId = $orderDetails['downloadId'];
    $downloadUrl = $base_url . "eula.php?id=" . $downloadId;
    $result = array("status" => "SUCCESS", "link" => $downloadUrl);



    echo json_encode($result);
}

function check_Regenerate($key)
{
    global $base_url;
    $pdo = pdo_connect();
    $customerNumber = UTIL_GetString('custNumber', '');
    $orderNumber = UTIL_GetString('orderNumber', '');
    $orderDetails = PTS_GetOrderDetailsByNumber($key, $conn, $customerNumber, $orderNumber);
    $downloadId = $orderDetails['downloadId'];
    $downloadUrl = $base_url . "eula.php?id=" . $downloadId;
    $result = array("status" => "SUCCESS", "link" => $downloadUrl);

    echo json_encode($result);
}

function check_Revoke($key)
{
    global $base_url;
    $pdo = pdo_connect();
    $customerNumber = UTIL_GetString('custNumber', '');
    $orderNumber = UTIL_GetString('orderNumber', '');
    $orderDetails = RevokeUrlByServiceTag($key, $conn, $customerNumber, $orderNumber);
    if (safe_sizeof($orderDetails) > 0) {
        $downloadId = $orderDetails['downloadId'];
        $downloadUrl = $base_url . "eula.php?id=" . $downloadId;
        $result = array("status" => "SUCCESS", "link" => $downloadUrl);
    } else {
        $result = array("status" => "FAILED", "link" => "");
    }
    echo json_encode($result);
}

function send_PaymentMail($key)
{
    global $base_url;
    global $payment_url;
    $pdo = pdo_connect();
    $cId = $_SESSION['user']['cId'];
    $result = [];

    $customerNumber = UTIL_GetString('custNumber', '');
    $orderNumber = UTIL_GetString('orderNumber', '');
    $custEmail = UTIL_GetString('emailId', '');
    $sendPaymentMail = PTS_SendPaymentMail($key, $conn, $customerNumber, $orderNumber, $custEmail);

    if ($sendPaymentMail) {
        $result = array("status" => "SUCCESS");
    } else {
        $result = array("status" => "FAILED");
    }
    echo json_encode($result);
}

function addDevices($key)
{
    global $payment_url;
    $pdo = pdo_connect();
    $result = [];
    $customerNumber = UTIL_GetString('custNumber', '');
    $orderNumber = UTIL_GetString('orderNumber', '');
    $noofpc = UTIL_GetString('noofpc', '');
    $orderDetails = PTS_GetOrderDetailsByNumber($key, $conn, $customerNumber, $orderNumber);
    $oldnoofpc = $orderDetails['noOfPc'];

    if ($noofpc > $oldnoofpc && $noofpc <= 5) {
        $paymentId = PTS_CreateCustomerHistory($key, $conn, $customerNumber, $orderNumber, $noofpc, "4");
        $paymentUrl = $payment_url . "index.php?id=" . $paymentId;
        $result = array("status" => "SUCCESS", "link" => trim($paymentUrl));
    } else {
        $result = array("status" => "FAILED", "link" => "", "message" => "Add device not done.");
    }
    echo json_encode($result);
}

function createTrialProvision($key, $customerEmail, $customerName)
{
    $pdo = pdo_connect();
    $provision = PTS_CreateTrialProvision($key, $conn, $customerEmail, $customerName, '');
    echo json_encode($provision);
}

function PTSAJX_GetMachineStatusImg($machine, $os, $status)
{
    $statusArray['Online'] = array('windowsOn', 'androidOn', 'macOn', 'linuxOn', 'iosOn');
    $statusArray['online'] = array('windowsOn', 'androidOn', 'macOn', 'linuxOn', 'iosOn');
    $statusArray['Offline'] = array('windowsOff', 'androidOff', 'macOff', 'linuxOff', 'iosOff');
    $statusArray['offline'] = array('windowsOff', 'androidOff', 'macOff', 'linuxOff', 'iosOff');
    $statusArray['Unknown'] = array('windowsOff', 'androidOff', 'macOff', 'linuxOff', 'iosOff');
    $statusArray['unknown'] = array('windowsOff', 'androidOff', 'macOff', 'linuxOff', 'iosOff');

    if (!isset($status)) {
        $status = "Unknown";
    }

    if (!empty($machine)) {
        if (stripos($os, 'Windows') !== FALSE) {
            $osstatus = 0;
        } else if (stripos($os, 'Android') !== FALSE) {
            $osstatus = 1;
        } else if (stripos($os, 'OS X') !== FALSE) {
            $osstatus = 2;
        } else if (stripos($os, 'Linux') !== FALSE) {
            $osstatus = 3;
        } else if (stripos($os, 'IOS') !== FALSE) {
            $osstatus = 4;
        } else {
            $osstatus = 0;
        }
    } else {
        $osstatus = 0;
    }
    return $statusArray[$status][$osstatus];
}





function PTSAJAX_ManagerInstalReport()
{

    $pdo = pdo_connect();
    $startDate = UTIL_GetString('startDate', '');
    $endDate = UTIL_GetString('endDate', '');
    $instalType = UTIL_GetString('instalType', '');

    $fromDate = strtotime($startDate);
    $toDate = strtotime($endDate);

    $reportData = PTS_ManagerInstallReport($fromDate, $toDate, $instalType, $pdo);
    echo json_encode($reportData);
}

function PTSAJAX_ManagerUsageReport()
{

    $pdo = pdo_connect();
    $chkBox = UTIL_GetString('chkVal', '');
    $radVal = UTIL_GetString('radVal', '');
    $employeeId = UTIL_GetString('employId', '');
    $serialNum = UTIL_GetString('serialNum', '');
    $startDate = UTIL_GetString('fromDate', '');
    $endDate = UTIL_GetString('toDate', '');
    $processId = UTIL_GetString('processId', '');
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    $reportData = PTS_ManagerUsageReport($chkBox, $radVal, $employeeId, $serialNum, $startDate, $endDate, $processId, $pdo);
    echo json_encode($reportData);
}

function PTSAJAX_ManagerSalesReport()
{

    $pdo = pdo_connect();
    $fromDate = UTIL_GetString('fromDate', '');
    $toDate = UTIL_GetString('toDate', '');
    $installType = UTIL_GetString('instalType', '');
    $pId = UTIL_GetString('processId', '');
    $cId = UTIL_GetString('channelId', '');
    $customerType = UTIL_GetString('customerType', '');
    $sDate = strtotime($toDate);
    $eDate = strtotime($toDate);

    $reportData = PTS_ManagerSalesReport($customerType, $cId, $pId, $sDate, $eDate, $installType, $pdo);
    echo json_encode($reportData);
}

function PTSAJAX_get_exportSkuList($cid, $pid)
{

    $feildData = PTS_getDropdownList($cid, $pid);
    return $feildData;
}

function PTSAJAX_getRefundList()
{

    $pdo = pdo_connect();
    $searchType = UTIL_GetString('searchType', '');
    $searchVal = UTIL_GetString('searchId', '');
    $processId = UTIL_GetString('processId', '');

    $reportData = PTS_refundData($searchType, $searchVal, $processId, $pdo);
    echo json_encode($reportData);
}

function PTSAJAX_createRefund()
{

    $pdo = pdo_connect();
    $referenceNum = UTIL_GetString('referenceNum', '');
    $compId = UTIL_GetString('channelId', '');
    $processId = UTIL_GetString('processId', '');


    $refundResponse = PTS_refund($referenceNum, $compId, $processId, $pdo);

    echo json_encode($refundResponse);
}

function PTSAJX_ExportCustomerDetails($key)
{
    $pdo = pdo_connect();
    $customerNumber = UTIL_GetString('custNumber', '');
    $exportType = UTIL_GetString('exportType', '');

    if ($exportType == "all" || $exportType == "All") {
        $customerDetails = PTS_GetAllOrdersForCustomer($key, $pdo, $customerNumber);
    } else {
        $orderNumber = UTIL_GetString('exportType', '');
        $customerDetails[] = PTS_GetOrderDetailsByNumber($key, $pdo, $customerNumber, $orderNumber);
    }
    $objPHPExcel = PTSAJX_CreateExportSheets($pdo);
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel = PTSAJX_FillExportSheets($objPHPExcel, $customerDetails, $conn);
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $customerNumber . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function PTSAJX_CreateExportSheets($conn)
{
    $objPHPExcel = PTSAJX_GetCustomerExportSheets();
    $objPHPExcel = PTSAJX_GetOrdersExportSheets($objPHPExcel);
    return $objPHPExcel;
}

function PTSAJX_GetCustomerExportSheets()
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle("Customer Details");
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Contract No");
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Work Order No");
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "First Name");
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(50);
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Last Name");
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Email");
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Sku Description");
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('G1', "Order Date");
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('H1', "Contract End Date");

    return $objPHPExcel;
}

function PTSAJX_GetOrdersExportSheets($objPHPExcel)
{
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);
    $objPHPExcel->getActiveSheet()->setTitle("Devices Details");
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Contract No");
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Work Order No");
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Device Name");
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Installed On");
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Status");
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Online Status");
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('G1', "Uninstall Date");
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('H1', "Manufacture");
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('I1', "ModelNum");
    return $objPHPExcel;
}

function PTSAJX_FillExportSheets($objPHPExcel, $customerDetails, $conn)
{
    $index = 2;
    $objPHPExcel->setActiveSheetIndex(0);
    foreach ($customerDetails as $key => $value) {
        $objPHPExcel = PTSAJX_FillCustomersCell($objPHPExcel, $value, $conn, $index);
        $compId = $value['compId'];
        $processId = $value['processId'];
        $customerNumber = $value['customerNum'];
        $orderNumber = $value['orderNum'];
        $allDevices = PTS_GetSitesDevices("", null, $compId, $processId, $customerNumber, $orderNumber);
        $allDevicesStatus = getAllMachinesStatus($customerNumber);
        $innerIndex = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();
        $innerIndex++;
        $objPHPExcel = PTSAJX_FillOrdersCell($objPHPExcel, $allDevices, $allDevicesStatus, $conn, $innerIndex);
        $index++;
    }
    return $objPHPExcel;
}

function PTSAJX_FillCustomersCell($objPHPExcel, $customerDetails, $conn, $index)
{
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $customerDetails['customerNum']);
    $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $customerDetails['orderNum']);
    $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $customerDetails['coustomerFirstName']);
    $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $customerDetails['coustomerLastName']);
    $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $customerDetails['emailId']);
    $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $customerDetails['SKUDesc']);
    $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $customerDetails['orderDate']);
    $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, $customerDetails['contractEndDate']);
    return $objPHPExcel;
}

function PTSAJX_FillOrdersCell($objPHPExcel, $deviceDetailsArr, $devicesStatus, $conn, $innerIndex)
{
    $objPHPExcel->setActiveSheetIndex(1);
    $today = time();
    foreach ($deviceDetailsArr as $key => $deviceDetails) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $innerIndex, $deviceDetails['customerNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $innerIndex, $deviceDetails['orderNum']);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $innerIndex, $deviceDetails['serviceTag']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $innerIndex, date("Y-m-d", $deviceDetails['installationDate']));
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $innerIndex, ($deviceDetails['uninstallDate'] < $today) ? 'Inactive' : 'Active');
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $innerIndex, $devicesStatus[$deviceDetails['serviceTag']]['status']);
        $objPHPExcel->getActiveSheet()->setCellValue('G' . $innerIndex, ($deviceDetails['uninstallDate'] != 'NULL') ? date("Y-m-d", $deviceDetails['uninstallDate']) : '-');
        $objPHPExcel->getActiveSheet()->setCellValue('H' . $innerIndex, $deviceDetails['machineManufacture']);
        $objPHPExcel->getActiveSheet()->setCellValue('I' . $innerIndex, $deviceDetails['machineModelNum']);
        $innerIndex++;
    }
    return $objPHPExcel;
}

function PTSAJAX_getCustomerDetails()
{

    $pdo = pdo_connect();
    $custList = [];

    $customerDetails = PTS_customerDetails($pdo);
    foreach ($customerDetails as $key => $value) {
        $customerNo = $value['customerNum'];
        $orderNo = $value['orderNum'];
        $orderDate = $value['orderDate'];
        if (empty($orderDate) || ($orderDate == "")) {
            $orderDate = "-";
        } else if (!empty($orderDate) || ($orderDate != "")) {
            $orderDate = date('m/d/Y', $orderDate);
        }
        $contractEndDate = date('m/d/Y', $value['contractEndDate']);
        $transationType = isset($value['transationType']) ? $value['transationType'] : '';
        $paymentStatus = isset($value['paymentStatus']) ? $value['paymentStatus'] : '';
        $SKUDesc = isset($value['SKUDesc']) ? $value['SKUDesc'] : '';
        $provCode = isset($value['provCode']) ? $value['provCode'] : '';
        $refund = isset($value['refund']) ? $value['refund'] : '';
        $cancelDate = isset($value['cancelDate']) ? $value['cancelDate'] : '';
        $payRefNum = isset($value['payRefNum']) ? $value['payRefNum'] : '';
        $todayDate = date('m/d/y');
        if ($provCode == '01') {
            if (($refund = '1') && ($cancelDate != '')) {
                $status = 'Cancelled';
            } else if ($payRefNum == '' || $payRefNum == null) {
                $status = 'Payment Pending';
            } else if ($contractEndDate < $todayDate) {
                $status = 'Expired';
            } else if ($contractEndDate >= $todayDate) {
                $status = 'Active';
            }
        } else if ($provCode == '02') {
            if ($contractEndDate >= $todayDate) {
                $status = 'Active';
            } else if ($contractEndDate < $todayDate) {
                $status = 'Expired';
            }
        }
        $custList[] = array($customerNo, $orderNo, $orderDate, $contractEndDate, $SKUDesc, $status);
    }
    echo json_encode($custList, TRUE);
}

function getSkuListByType($key)
{
    $jsonResult = [];
    $pdo = pdo_connect();
    $companyName = url::requestToAny('companyname');
    $skuType = url::requestToAny('skuType');
    $skuResult = PTS_GetSKU_BySKUType($key, $pdo, "US", $companyName, $skuType);

    logs::log("getSkuListByType", $skuResult);

    if (safe_count($skuResult) > 0) {
        $jsonResult = array("status" => "SUCCESS", "result" => $skuResult);
    } else {
        $jsonResult = array("status" => "FAILED", "result" => [], "message" => "No Records Available for this SKU Type");
    }
    echo json_encode($jsonResult);
}

function PTSAJAX_ManagerBarGraphData()
{
    $pdo = pdo_connect();
    $pid = $_SESSION['user']['pid'];
    $cid = $_SESSION['user']['cId'];

    $ManagerReportData = PTS_ManagerBarGraphData($pdo, $pid, $cid);
    $result = json_encode($ManagerReportData);
    echo $result;
}

function PTSAJAX_getCustomerDetailsForSales()
{

    $pdo = pdo_connect();
    $pid = $_SESSION['user']['pid'];
    $cid = $_SESSION['user']['cId'];
    $draw = url::requestToAny('draw');

    $customerDetails = PTS_customerSalesDetails($pdo, $pid, $cid);
    $totalRecords = safe_count($customerDetails);

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $customerDetails);
    echo json_encode($jsonData);
}

function PTSAJAX_getEntitlement()
{

    $pdo = pdo_connect();

    $orderNum = url::postToAny('id');
    $type = url::postToAny('type');

    $result = getEntitlementDetails($pdo, $orderNum, $type);

    $orderCnt = $result[0]['cnt'];
    $entResCnt = safe_count($result);

    $showDet = '%%%order_numbers%%%';
    $showDet .= $entResCnt . '%%%';

    if ($entResCnt > 0) {

        if ($entResCnt == 1) {
            $cust_num_list = $result[0]['customerNum'];
            $showDet .= '<label class="ent_show_labels_heading" style="color:#333333; font-size: 18px;">System Serial Number : </label><label id="ent_show_cust_no" onchange=show_cust_num() style="font-size: 18px; color: #5858FA; margin-right: 1%">' . $cust_num_list . '</label><label class="ent_show_line1"></label>';
        } else {
            $showDet .= '<label class="ent_show_labels_heading" style="color:#333333; font-size: 18px;">System Serial Number : </label><select id="ent_show_cust_no" onchange=show_cust_num() style="font-size: 17px; color: #5858FA; margin-right: 1%">';
            for ($i = 0; $i < $entResCnt; $i++) {
                $showDet .= '<option value="' . $result[$i]['customerNum'] . '">' . $result[$i]['customerNum'] . '</option>';
            }
            $showDet .= '</select><label class="ent_show_line1"></label>';
        }
    } else {
        $showDet .= '<label class="ent_show_labels_heading" style="color:#FF9933; font-size: 18px;">Not found !</label><label id="ent_show_cust_no" style="font-size: 16px; font-weight: bold;"></label>';
    }

    echo $showDet;
}

function PTSAJAX_getCustomerDetailsForEntitlement()
{

    $pdo = pdo_connect();


    $customer_no = url::issetInRequest('cust_num') ? url::requestToText('cust_num') : '';
    $search_type = url::issetInRequest('searchType') ? url::requestToText('searchType') : '';
    $input_val = url::issetInRequest('txtVal') ? url::requestToText('txtVal') : '';


    $wh = '';
    $count = 0;
    if ($search_type == 'orderNumber') {
        $wh .= 'and orderNum="' . $input_val . '"';

        $sql_cust = $pdo->prepare("select count(id) count from customerOrder where customerNum = ? and (orderNum = ? or oldorderNum = ?");
        $sql_cust->execute([$customer_no, $input_val, $input_val]);
        $res_cust = $sql_cust->fetch();
        $count = $res_cust['count'];
    }

    if ($customer_no) {

        $ent_sql = $pdo->prepare("select * from customerOrder where customerNum = ? " . $wh . " and id in (select max(id) from customerOrder where customerNum = ? group by orderNum ) order by id DESC");
        $ent_sql->execute([$customer_no, $customer_no]);
        $ent_res = $ent_sql->fetchAll();
    }

    $entResCnt = safe_count($ent_res);

    if ($entResCnt > 0) {


        $arr_old_order_nums = array();
        for ($i = 0; $i < $entResCnt; $i++) {
            $arr_old_order_nums[] = $ent_res[$i]['oldorderNum'];
        }

        $stringDet = '%%%order_number_row%%%';
        $stringDet .= $entResCnt . '%%%';


        for ($i = 0; $i < $entResCnt; $i++) {
            if (in_array($ent_res[$i]['orderNum'], $arr_old_order_nums)) {
            } else {

                $show_regenerate = 0;
                $radial_point = 0;
                $old_order_num = 0;
                $radial_point_id = 0;
                $agent_phone_id_first = $ent_res[$i]['agentId'];
                $agent_center_first = 'empty';
                $order_date = date('m/d/y', $ent_res[$i]['orderDate']);
                $contract_end_date = date('m/d/y', $ent_res[$i]['contractEndDate']);
                $cur_timestamp = time();

                $sql_customerOrder = $pdo->prepare("select oldorderNum from customerOrder where customerNum = '" . $ent_res[$i]['customerNum'] . "' and oldorderNum = '" . $ent_res[$i]['orderNum'] . "' and processId = ?");
                $sql_customerOrder->execute([$pId]);
                $res_customerOrder = $sql_customerOrder->fetch();

                if (safe_count($res_customerOrder) > 0) {
                    $oldorderNum = true;
                } else {
                    $oldorderNum = false;
                }

                $skuNum = $ent_res[$i]['SKUNum'];
                $query = $pdo->prepare("select renew, upgrade, renewDays, ppid, nanoheal_prod, trial from fieldValues where value=? and pid=?");
                $query->execute([$skuNum, $pId]);
                $SkuData = $query->fetch();


                if (safe_count($SkuData) > 0) {
                    $upgradeStatus = trim($SkuData['upgrade']);
                    $renewStatus = $SkuData['renew'];
                    $rewDays = $SkuData['renewDays'];
                    $ppid = $SkuData['ppid'];
                    $nanoheal_prod = $SkuData['nanoheal_prod'];
                    $trial = $SkuData['trial'];
                }



                if ($cur_timestamp <= $ent_res[$i]['contractEndDate']) {
                    $status = 'Active';
                } else {
                    $status = 'Expired';
                }


                if (in_array($ent_res[$i]['orderNum'], $arr_old_order_nums)) {

                    $old_order_num = 1;
                    $status = 'Renewed';
                }

                if ($ent_res[$i]['provCode'] == '02') {
                    $provStatus = 1;
                } else {
                    $provStatus = 0;
                }

                $upd_order_num = 0;
                if ($ent_res[$i]['oldorderNum'] != '') {
                    $upd_order_num = 1;
                }

                if ($ent_res[$i]['mappCCNum'] != '') {
                    $paymentStatus = "done";
                } else {
                    $paymentStatus = "notdone";
                }

                $insertedId = $ent_res[$i]['id'];

                $radial_point = 1;
                $radial_point_id = '';

                $stringDet = '';

                if ($oldorderNum || $status === "Cancelled") {
                    $upgradeStatus = 0;
                }

                echo "%%%order_number_row%%%";
                echo "##" . $ent_res[$i]['customerNum'];
                echo "##" . $ent_res[$i]['orderNum'];
                echo "##" . $order_date;
                echo "##" . $contract_end_date;
                echo "##" . $ent_res[$i]['SKUNum'];
                echo "##" . $ent_res[$i]['SKUDesc'];
                echo "##" . $ent_res[$i]['lineOfBusiness'];
                echo "##" . $ent_res[$i]['emailId'];
                echo "##" . $status . "_" . $paymentStatus . "_" . $insertedId . "_" . $ppid . "_" . $nanoheal_prod . "_" . $ent_res[$i]['licenseKey'] . "_" . $trial;
                echo "##" . $renewStatus . '_' . $upgradeStatus . '_' . $rewDays . "_" . $ent_res[$i]['provCode'] . "_" . $count . "_" . $ent_res[$i]['noOfPc'];
                echo "##" . $ent_res[$i]['BackUpStatus'];
                echo "##" . $old_order_num . '_' . $upd_order_num;
                echo "##" . $ent_res[$i]['agentId'];
                echo "##";

                $order_num = $ent_res[$i]['orderNum'];

                $first_order = find_first_order_num($customer_no, $order_num);

                service_request_block($customer_no, $order_num, $agent_phone_id_first, $agent_center_first, $show_regenerate);






                if ($order_num == $first_order) {
                    $hist_SKUNum = $ent_res[$i]['SKUNum'];
                    $hist_SKUDesc = $ent_res[$i]['SKUDesc'];
                    $hist_order_date = $ent_res[$i]['orderDate'];
                    $hist_orderNum = $ent_res[$i]['orderNum'];

                    $history_list = "";

                    $real_date = date('m/d/Y', $hist_order_date);
                    $history_list .= $hist_SKUNum . "~~" . $hist_SKUDesc . "~~" . $real_date . "~~" . $hist_orderNum . "~~" . $agent_phone_id_first;
                    $history_list .= "@@";


                    echo "%%%history_list%%%" . $history_list;
                    echo "##";
                } else {

                    $history = '';
                    $history_list = create_history_details_custnum($customer_no, $order_num, $history);
                }
            }
        }
    } else {
        echo "%%%order_number_row%%%";
        echo "NOT_FOUND";
    }
}

function find_first_order_num($cn, $on)
{

    $pdo = pdo_connect();


    $cId = $_SESSION['agent']['companyId'];
    $pId = $_SESSION['agent']['processId'];

    $ent_sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum = ? AND orderNum = ? and processId = ? limit 1");
    $ent_sql->execute([$cn, $on, $pId]);
    $ent_res = $ent_sql->fetchAll();

    if ($ent_res[0]['oldorderNum']) {
        $return_val = $ent_res[0]['oldorderNum'];
        find_first_order_num($cn, $ent_res[0]['oldorderNum']);
    } else {
        $return_val = $on;
    }
    return $return_val;
}

function service_request_block($customer_no, $order_num, $agent_phone_id_first, $agent_center_first, $show_regenerate)
{
    $pdo = pdo_connect();


    $cId = $_SESSION['agent']['companyId'];
    $pId = $_SESSION['agent']['processId'];
    $ent_sql_service_req = $pdo->prepare("select a1.* from " . $GLOBALS['PREFIX'] . "agent.serviceRequest a1 inner join (select max(sid) as max from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum = ? AND orderNum = ? group by serviceTag order by sid desc) a2 on a1.sid = a2.max");
    $ent_sql_service_req->execute([$customer_no, $order_num]);
    $ent_res_service_res = $ent_sql_service_req->fetchAll();

    if (safe_count($ent_res_service_res) > 0) {


        for ($k = 0; $k < safe_count($ent_res_service_res); $k++) {

            $agent_phone_id = $ent_res_service_res[$k]['agentPhoneId'];
            $agent_center = '';

            $activateStatus = $ent_res_service_res[$k]['activateStatus'];

            if (($agent_phone_id == NULL) || ($agent_phone_id == 0) || ($agent_phone_id == '')) {

                $agent_phone_id = 'empty';
                $agent_center = 'empty';
                if ($k == 0) {
                    $agent_phone_id_first = $agent_phone_id;
                    $agent_center_first = $agent_center;
                }
            } else {
                $sql_agent = $pdo->prepare("select * from Agent where phone_id = ? limit 1");
                $sql_agent->execute([$agent_phone_id]);
                $sql_agent_res = $sql_agent->fetch();

                if (safe_count($sql_agent_res) > 0) {
                    $agent_center = $sql_agent_res['center'];
                } else {
                    $agent_center = 'empty';
                }

                if ($k == 0) {
                    $agent_phone_id_first = $agent_phone_id;
                    $agent_center_first = $agent_center;
                }
            }

            $uninsStatus = 'UNINS_EMPTY';
            if ($ent_res_service_res[$k]['uninsdormatStatus'] != '' && $ent_res_service_res[$k]['uninsdormatStatus'] != NULL) {
                $uninsStatus = 'UNINS_DONE';
            } else {
                $uninsStatus = 'UNINS_EMPTY';
            }

            if (($ent_res_service_res[$k]['downloadStatus'])) {

                $install_date = date('m/d/Y', $ent_res_service_res[$k]['installationDate']);
                echo "%%%service_request_row%%%";
                echo "@@" . $ent_res_service_res[$k]['machineManufacture'];
                echo "@@" . $ent_res_service_res[$k]['machineModelNum'];
                echo "@@" . $ent_res_service_res[$k]['serviceTag'];
                echo "@@" . $install_date;
                echo "@@" . $ent_res_service_res[$k]['downloadStatus'];
                echo "@@" . $ent_res_service_res[$k]['revokeStatus'];
                echo "@@" . $agent_phone_id;
                echo "@@" . $agent_center;
                echo "@@" . $uninsStatus;
                echo "@@" . $activateStatus;
                echo "@@";

                if (($ent_res_service_res[$k]['revokeStatus'] == 'R')) {

                    $show_regenerate = 1;
                }
                if (($ent_res_service_res[$k]['revokeStatus'] == 'I')) {

                    $show_regenerate = 0;
                }
            } else {
                $show_regenerate = 1;
            }
        }
    } else {
        echo "%%%service_request_row%%%";
    }
    echo "##" . $agent_phone_id_first;
    echo "##" . $agent_center_first;
    echo "##" . $show_regenerate;
    echo "##";
}

function create_history_details_custnum($cn, $on, $history_list)
{

    $pdo = pdo_connect();


    $cId = $_SESSION['agent']['companyId'];
    $pId = $_SESSION['agent']['processId'];

    $his_sql = $pdo->prepare("select SKUNum,SKUDesc,orderDate,orderNum,oldorderNum,mappCCNum,payRefNum,agentId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum = ? AND orderNum = ? and processId = ? limit 1");
    $his_sql->execute([$cn, $on, $pId]);
    $his_res = $his_sql->fetchAll();



    if (safe_count($his_res) > 0) {

        $hist_SKUNum = $his_res[0]['SKUNum'];
        $hist_SKUDesc = $his_res[0]['SKUDesc'];
        $hist_order_date = $his_res[0]['orderDate'];
        $hist_orderNum = $his_res[0]['orderNum'];
        $hist_agentPh = $his_res[0]['agentId'];

        $real_date = date('m/d/Y', $hist_order_date);
        $history_list .= $hist_SKUNum . "~~" . $hist_SKUDesc . "~~" . $real_date . "~~" . $hist_orderNum . "~~" . $hist_agentPh;
        $history_list .= "@@";

        if ($his_res[0]['oldorderNum'] != 0 || $his_res[0]['oldorderNum'] != '') {
            create_history_details_custnum($cn, $his_res[0]['oldorderNum'], $history_list);
        } else {
            echo "%%%history_list%%%" . $history_list;
            echo "##";
        }
    }
}

function selServReqDetByServiceTag()
{

    $pdo = pdo_connect();


    $servTag = url::issetInRequest('servTag') ? url::requestToText('servTag') : '';
    $cust_num = url::issetInRequest('cust_num') ? url::requestToText('cust_num') : '';
    $ordr_num = url::issetInRequest('ordr_num') ? url::requestToText('ordr_num') : '';
    $provcode = url::issetInRequest('provcode') ? url::requestToText('provcode') : '';
    $val_i = url::issetInRequest('i') ? url::requestToAny('i') : '';
    $cId = $_SESSION['agent']['companyId'];
    $pId = $_SESSION['agent']['processId'];

    $servTag_sql = $pdo->prepare("select sid,customerNum,orderNum,sessionid,serviceTag, installationDate,uninstallDate,machineOS,agentPhoneId,downloadStatus,revokeStatus,machineManufacture,machineModelNum,oldServiceTag from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where serviceTag = ? and customerNum = ? and orderNum = ?  order by sid DESC");
    $servTag_sql->execute([$servTag, $cust_num, $ordr_num]);
    $servTag_res = $servTag_sql->fetchAll();

    $sql = $pdo->prepare("select count(*) count,customerNum,orderNum,SKUNum,mappCCNum,payRefNum,refund, oldorderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=? and (orderNum =? or  oldorderNum = ?)  order by id DESC limit 1");
    $sql->execute([$cust_num, $ordr_num, $ordr_num]);
    $paymentData = $sql->fetch();

    if (safe_count($paymentData) > 0) {
        $sku = $paymentData['SKUNum'];
        $sql2 = $pdo->prepare("select ppid from fieldValues where value=? ");
        $sql2->execute([$sku]);
        $ppidResult = $sql2->fetch();
        $ppid = $ppidResult['ppid'];

        if (trim($paymentData['mappCCNum']) !== "") {
            $payment = "done";
        } else {
            $payment = "notDone";
        }

        $count = $paymentData['count'];
    }

    $sql_checkUpgrade = $pdo->prepare("select orderNum from customerOrder where customerNum = '' and oldorderNum = '' order by id desc limit 1");
    $sql_checkUpgrade->execute();
    $res_checkUpgrade = $sql_checkUpgrade->fetch();

    if (safe_count($res_checkUpgrade) > 0) {
        $upgradeStatus = false;
    } else {
        $upgradeStatus = true;
    }

    $contentDis = '<div style="height:100px; width:100%; float:left; overflow:auto;"><table class="servTagDet" cellspacing="0" style="width:100%;padding-top:5px;">';
    $contentDis .= '<tr style="background:#D8D8D8"><td><label class="ent_show_line4">System Serial No</label></td><td><label class="ent_show_line4">Install Date</label></td><td><label class="ent_show_line4">Uninstall Date</label></td><td><label class="ent_show_line4">Machine OS</label></td><td><label class="ent_show_line4">Manufacture</label></td><td><label class="ent_show_line4">ModelNum</label></td><td><label class="ent_show_line4">RVK/REGN</label></td></tr>';

    $status = '';
    if (safe_count($servTag_res) > 0) {

        for ($i = 0; $i < safe_count($servTag_res); $i++) {

            $activateStatus = $servTag_res[$i]['activateStatus'];

            if ($servTag_res[$i]['serviceTag'] == '') {
                $servTag = 'empty';
            } else {
                $servTag = $servTag_res[$i]['serviceTag'];
            }

            if ($servTag_res[$i]['installationDate'] == '') {
                $instDt = 'empty';
            } else {
                $instDt = date('m/d/Y', $servTag_res[$i]['installationDate']);
            }

            if ($servTag_res[$i]['uninstallDate'] == '') {
                $endDt = 'empty';
            } else {
                $endDt = date('m/d/Y', $servTag_res[$i]['uninstallDate']);
            }

            if ($servTag_res[$i]['agentPhoneId'] == '') {
                $agentId = 'empty';
            } else {
                $agentId = $servTag_res[$i]['agentPhoneId'];
            }

            if ($servTag_res[$i]['machineManufacture'] == '') {
                $manuFact = 'empty';
            } else {
                $manuFact = $servTag_res[$i]['machineManufacture'];
            }

            if ($servTag_res[$i]['machineModelNum'] == '') {
                $modNo = 'empty';
            } else {
                $modNo = $servTag_res[$i]['machineModelNum'];
            }

            if ($servTag_res[$i]['machineOS'] == '') {
                $machineOs = 'empty';
            } else {
                $machineOs = $servTag_res[$i]['machineOS'];
            }



            $currTime = time();
            $uninstallDate = $servTag_res[$i]['uninstallDate'];
            if ($i == 0) {
                if (intval($count) <= 1) {
                    if (intval($ppid) !== 0) {
                        if (intval($paymentData['refund']) !== 1) {
                            if ($payment === "done") {

                                if (intval($currTime) < intval($uninstallDate)) {
                                    if (($servTag_res[$i]['downloadStatus'] == 'G' || $servTag_res[$i]['downloadStatus'] == 'D') && $servTag_res[$i]['revokeStatus'] == 'I') {
                                        $status = '<span onclick="regenrt_revoke_div(\'' . $servTag_res[$i]['serviceTag'] . '\',\'' . $servTag_res[$i]['customerNum'] . '\',\'' . $servTag_res[$i]['orderNum'] . '\',\'REGEN\', \'' . $provcode . '\', \'' . $val_i . '\')" id="new-regenarate" class="actionBtn">Regenerate</span>';
                                    } else if ($servTag_res[$i]['downloadStatus'] == 'CANCEL') {
                                        $status = 'Cancelled';
                                    } else if (trim($servTag_res[$i]['revokeStatus']) == 'R') {
                                        $status = 'Revoked';
                                    } else {
                                        if (intval($activateStatus) !== 0 && $upgradeStatus) {
                                            $status = '<span onclick="regenrt_revoke_div(\'' . $servTag_res[$i]['serviceTag'] . '\',\'' . $servTag_res[$i]['customerNum'] . '\',\'' . $servTag_res[$i]['orderNum'] . '\',\'REVK\', \'' . $provcode . '\', \'' . $val_i . '\')" id="new-regenarate" class="actionBtn">Revoke</span>';
                                        } else {
                                            $status = '-';
                                        }
                                    }
                                } else {
                                    $status = "-";
                                }
                            } else {
                                $status = "-";
                            }
                        } else {
                            $status = "Cancelled";
                        }
                    } else {
                        if (intval($currTime) < intval($uninstallDate)) {
                            if (($servTag_res[$i]['downloadStatus'] == 'G' || $servTag_res[$i]['downloadStatus'] == 'D') && $servTag_res[$i]['revokeStatus'] == 'I') {
                                $status = '<span onclick="regenrt_revoke_div(\'' . $servTag_res[$i]['serviceTag'] . '\',\'' . $servTag_res[$i]['customerNum'] . '\',\'' . $servTag_res[$i]['orderNum'] . '\',\'REGEN\' , \'' . $servTag_res[$i]['provCode'] . '\', \'' . $val_i . '\')" id="new-regenarate" class="actionBtn">Regenerate</span>';
                            } else if ($servTag_res[$i]['downloadStatus'] == 'CANCEL') {
                                $status = 'Cancelled';
                            } else if (trim($servTag_res[$i]['revokeStatus']) == 'R') {
                                $status = 'Revoked';
                            } else {
                                if (intval($activateStatus) !== 0 && $upgradeStatus) {
                                    $status = '<span onclick="regenrt_revoke_div(\'' . $servTag_res[$i]['serviceTag'] . '\',\'' . $servTag_res[$i]['customerNum'] . '\',\'' . $servTag_res[$i]['orderNum'] . '\',\'REVK\', \'' . $provcode . '\', \'' . $val_i . '\')" id="new-regenarate" class="actionBtn">Revoke</span>';
                                } else {
                                    $status = '-';
                                }
                            }
                        } else {
                            $status = "-";
                        }
                    }
                } else {
                    $status = "-";
                }
            }
            if (trim($servTag_res[$i]['oldServiceTag']) !== $servTag) {
                if (trim($servTag_res[$i]['oldServiceTag']) !== "") {
                    $allRecords = getAllServiceTagData(trim($servTag_res[$i]['oldServiceTag']), $cust_num, $ordr_num, $provcode, $val_i, "");
                }
            }

            $contentDis .= '<tr><td><label class="ent_show_line4">' . $servTag . '</label></td><td><label class="ent_show_line4">' . $instDt . '</label></td><td><label class="ent_show_line4">' . $endDt . '</label></td><td><label class="ent_show_line4">' . $machineOs . '</label></td><td><label class="ent_show_line4">' . $manuFact . '</label></td><td><label class="ent_show_line4">' . $modNo . '</label></td><td><label class="ent_show_line4">' . $status . '</label></td></tr>';
            $contentDis .= $allRecords;
            $contentDis .= '<tr bgcolor="#D8D8D8" style="height:2px;"><td colspan=7></td>';

            $status = '';
        }
    } else {
        $contentDis .= '<tr><td colspan=7>No System has installed(Empty Details)</td></tr>';
        $contentDis .= '<tr bgcolor="#D8D8D8" style="height:2px;"><td colspan=7></td>';
    }

    $contentDis .= '</table></div>';

    $sertag_list = getServiceRequestDet($cust_num, $ordr_num, $provcode, $val_i);
    echo $contentDis . '%%' . $sertag_list;
}

function getAllServiceTagData($servTag, $cust_num, $ordr_num, $provcode, $val_i, $contentDis)
{

    $pdo = pdo_connect();


    $cId = $_SESSION['agent']['companyId'];
    $pId = $_SESSION['agent']['processId'];

    $servTag_sql = $pdo->prepare("select sid,customerNum,orderNum,sessionid,serviceTag, installationDate,uninstallDate,agentPhoneId,downloadStatus,revokeStatus,machineManufacture,machineModelNum,oldServiceTag from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where serviceTag = ? and customerNum = ? and orderNum = ?  order by sid DESC limit 1");
    $servTag_sql->execute([$servTag, $cust_num, $ordr_num]);
    $servTag_res = $servTag_sql->fetchAll();

    $status = '';
    if (safe_count($servTag_res) > 0) {

        for ($i = 0; $i < safe_count($servTag_res); $i++) {

            if ($servTag_res[$i]['serviceTag'] == '') {
                $servTag = 'empty';
            } else {
                $servTag = $servTag_res[$i]['serviceTag'];
            }

            if ($servTag_res[$i]['installationDate'] == '') {
                $instDt = 'empty';
            } else {
                $instDt = date('m/d/Y', $servTag_res[$i]['installationDate']);
            }

            if ($servTag_res[$i]['uninstallDate'] == '') {
                $endDt = 'empty';
            } else {
                $endDt = date('m/d/Y', $servTag_res[$i]['uninstallDate']);
            }

            if ($servTag_res[$i]['agentPhoneId'] == '') {
                $agentId = 'empty';
            } else {
                $agentId = $servTag_res[$i]['agentPhoneId'];
            }

            if ($servTag_res[$i]['machineManufacture'] == '') {
                $manuFact = 'empty';
            } else {
                $manuFact = $servTag_res[$i]['machineManufacture'];
            }

            if ($servTag_res[$i]['machineModelNum'] == '') {
                $modNo = 'empty';
            } else {
                $modNo = $servTag_res[$i]['machineModelNum'];
            }
            $currTime = time();
            $uninstallDate = $servTag_res[$i]['uninstallDate'];


            if (trim($servTag_res[$i]['revokeStatus']) == 'R') {
                $status = 'Revoked';
            } else {
                $status = '-';
            }

            $contentDis .= '<tr><td><label class="ent_show_line4">' . $servTag . '</label></td><td><label class="ent_show_line4">' . $instDt . '</label></td><td><label class="ent_show_line4">' . $endDt . '</label></td><td><label class="ent_show_line4">' . $agentId . '</label></td><td><label class="ent_show_line4">' . $manuFact . '</label></td><td><label class="ent_show_line4">' . $modNo . '</label></td><td><label class="ent_show_line4">' . $status . '</label></td></tr>';
            if (trim($servTag_res[$i]['oldServiceTag']) !== $servTag) {
                if (trim($servTag_res[$i]['oldServiceTag']) !== "") {
                    return getAllServiceTagData(trim($servTag_res[$i]['oldServiceTag']), $cust_num, $ordr_num, $provcode, $val_i, $contentDis);
                } else {
                    return $contentDis;
                }
            } else {
                return $contentDis;
            }
        }
    }
}

function getServiceRequestDet($cust_num, $ordr_num, $provcode, $val_i)
{
    $pdo = pdo_connect();


    $cId = $_SESSION['agent']['companyId'];
    $pId = $_SESSION['agent']['processId'];

    $sql = $pdo->prepare("select customerNum,orderNum,serviceTag,installationDate,agentPhoneId,oldServiceTag,downloadStatus,revokeStatus FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE orderNum = '" . $ordr_num . "' AND customerNum = '" . $cust_num . "' ORDER BY sid desc");
    $sql->execute();
    $result = $sql->fetchAll();
    $restrictServiceTag = [];
    $firstServiceTag = "";
    if (safe_count($result) > 0) {
        $returnData = "<select id='serviceTagDetails" . $val_i . "' onchange='showServTagDetails(this,\"" . $val_i . "\",\"" . $cust_num . "\", \"" . $ordr_num . "\", \"" . $provcode . "\")'; style='font-size: 17px; color: #5858FA; margin-right: 1%'>";
        for ($i = 0; $i < safe_count($result); $i++) {
            if ($i === 0) {
                $firstServiceTag = trim($result[$i]['serviceTag']);
            }

            if (!in_array(trim($result[$i]['serviceTag']), $restrictServiceTag)) {
                if (trim($result[$i]['oldServiceTag']) !== "") {
                    $restrictServiceTag[trim($result[$i]['oldServiceTag'])] = trim($result[$i]['oldServiceTag']);
                }

                $returnData .= "<option value='" . trim($result[$i]['serviceTag']) . "'>" . trim($result[$i]['serviceTag']) . "</option>";
            } else {
                if (trim($result[$i]['oldServiceTag']) !== "") {
                    $restrictServiceTag[trim($result[$i]['oldServiceTag'])] = trim($result[$i]['oldServiceTag']);
                }
            }
        }

        $returnData .= "</select>";
        return $returnData;
    }
}

function get_onlineOrOffilneMachine()
{

    $serviceTag = url::issetInRequest('serviceTag') ? url::requestToAny('serviceTag') : '';
    $ordr_num = url::issetInRequest('ordr_num') ? url::requestToAny('ordr_num') : '';
    $cust_num = url::issetInRequest('cust_num') ? url::requestToAny('cust_num') : '';

    $status = getOnlineOrOffline($serviceTag, $cust_num, $ordr_num, 1);

    $pdo = pdo_connect();

    $sql_serviceRequest = $pdo->prepare("select activateStatus from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum = ? and orderNum = ? and serviceTag = ? order by sid desc limit 1");
    $sql_serviceRequest->execute([$cust_num, $ordr_num, $serviceTag]);
    $res_serviceRequest = $sql_serviceRequest->fetch();

    if (safe_count($res_serviceRequest) > 0) {
        $activateStatus = $res_serviceRequest['activateStatus'];
    }

    if ($status == 0) {
        echo '%%%%%DONE%%%%%' . $cust_num . '%%%%%' . $ordr_num . '%%%%%' . $serviceTag . '%%%%%Online' . "%%%%%" . $activateStatus;
    } else if ($status == 1) {
        echo '%%%%%DONE%%%%%' . $cust_num . '%%%%%' . $ordr_num . '%%%%%' . $serviceTag . '%%%%%Offline' . "%%%%%" . $activateStatus;
    } else if ($status == 2) {
        echo '%%%%%NOTDONE%%%%%' . $cust_num . '%%%%%' . $ordr_num . '%%%%%' . $serviceTag . '%%%%%Not';
    }
}

function checkRemoteUrl()
{
    $pdo = pdo_connect();


    $custno = url::requestToAny('custno');
    $ordrno = url::requestToAny('ordrno');

    $agntEmail = $_SESSION['agent']['email'];

    $sql = $pdo->prepare("select remoteCredentialDetails as rcd from " . $GLOBALS['PREFIX'] . "agent.Agent where email=?");
    $sql->execute([$agntEmail]);
    $res = $sql->fetch();

    $srSql = $pdo->prepare("select serviceTag, machineos, clientVersion from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum=? and orderNum=? limit 1;");
    $srSql->execute();
    $srRes = $srSql->fetch([$custno, $ordrno]);
    $servicetag = $srRes['serviceTag'];
    $machineos = $srRes['machineos'];
    $client_ver = $srRes["clientVersion"];

    if ($res['rcd'] !== '') {
        echo 'EXIST###' . $res['rcd'] . '###' . $servicetag . "###" . $machineos . "###" . $client_ver;
    } else {
        echo 'NOTEXIST###NO###' . $servicetag . "###" . $machineos . "###" . $client_ver;
    }
}

function saveRemoteUrl()
{
    $pdo = pdo_connect();

    $agntEmail = $_SESSION['agent']['email'];

    $loginid = url::issetInRequest('loginid') ? url::requestToAny('loginid') : '';
    $password = url::issetInRequest('password') ? url::requestToAny('password') : '';

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'jfsibm';
    $secret_iv = 'jfsibm@nanoheal.com';
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);
    $passwordencode = openssl_encrypt($password, $encrypt_method, $key, 0, $iv);
    $encryptedPwd = base64_encode($passwordencode);

    $remoteUrlJson = '{"loginid":"' . $loginid . '", "password":"' . $encryptedPwd . '"}';

    $sql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.Agent set remoteCredentialDetails = ? where email=?");
    $sql->execute([$remoteUrlJson, $agntEmail]);
    $res = $pdo->lastInsertId();
    if ($res) {
        echo 'DONE';
    } else {
        echo 'NOTDONE';
    }
}

function getRemoteUrl()
{
    $loginid = url::requestToAny('loginid');
    $password = url::requestToAny('password');
    $type = url::requestToAny('type');

    if ($type == 'EXIST') {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'jfsibm';
        $secret_iv = 'jfsibm@nanoheal.com';
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        $decyptRes = openssl_decrypt(base64_decode($password), $encrypt_method, $key, 0, $iv);
        $password = $decyptRes;
    }

    $soapclient = new SoapClient('https://secure.logmeinrescue.com/api/api.asmx?wsdl');

    try {
        $loginparams = array(
            'sEmail' => $loginid,
            'sPassword' => $password
        );

        $result = $soapclient->login($loginparams);

        if ($result->loginResult == 'login_OK') {
            $requestAuthCodeResult = $soapclient->requestAuthCode($loginparams);

            if ($requestAuthCodeResult->requestAuthCodeResult == 'requestAuthCode_OK') {
                $notechconsole = "0";
                $authcode = $requestAuthCodeResult->sAuthCode;

                $requestPINCodeParams = array(
                    'notechconsole' => $notechconsole,
                    'authcode' => $authcode
                );

                $requestPINCodeResult = $soapclient->requestPINCode($requestPINCodeParams);

                if ($requestPINCodeResult->requestPINCodeResult == 'requestPINCode_OK') {
                    $jsondata = 'success###' . 'https://secure.logmeinrescue.com/R?i=2&Code=' . $requestPINCodeResult->iPINCode;
                } else {

                    if ($requestPINCodeResult->requestPINCodeResult == 'requestPINCode_NoTechConsoleRunning') {
                        $jsondata = 'error###Please login to LMI application.';
                    } else if ($requestPINCodeResult->requestPINCodeResult == 'requestPINCode_NotTechnician') {
                        $jsondata = 'error###Error generating the URL. Please try again.';
                    } else {
                        $jsondata = 'success###' . $requestPINCodeResult->requestPINCodeResult;
                    }
                }
            } else {
                $jsondata = 'error###Invalid Auth Code';
            }
        } else {
            $jsondata = 'error###Invalid LMI login id or password.';
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $jsondata = 'error###Error connecting to the Log Me In server';
    }
    echo $jsondata;
}

function sendRemoteUrl()
{

    $emailto = url::requestToAny('emailid');
    $remotelink = url::requestToAny('remoteUrl');
    $code = url::requestToAny('Code');

    $remoteurl = $remotelink . '&Code=' . $code;

    $body = "<html>";

    $to = $emailto;
    $toName = 'Customer';

    $fromEmail = 'noreply@bask.com';

    $fromName = 'Bask support team';


    $from_name = 'noreply@bask.com';
    $from_mail = 'noreply@bask.com';
    $hostname = $_SERVER['HTTP_HOST'];
    $subject = 'Remote Session URL';
    $message = "Hi, <br/><br/> Please find the Log Me In Remote Session URL <br/><br/> <p style='color:#1f497d; text-decoration:underline;'>" . $remoteurl . '</p><br/><br/>';
    $message .= 'This is an automated mail. please do not reply back to this mail. </br></br><b>Server :</b> ' . $hostname;
    $boundary = "XYZ-" . date("dmYis") . "-ZYX";

    $header = "--$boundary\r\n";
    $header .= "Content-Transfer-Encoding: 8bits\r\n";
    $header .= "Content-Type: text/html; charset=ISO-8859-1\r\n\r\n";
    $header .= "$message\r\n";
    $header .= "--$boundary\r\n";
    $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
    $header .= "$content\r\n";
    $header .= "--$boundary--\r\n";

    $header2 = "MIME-Version: 1.0\r\n";
    $header2 .= "From: " . $from_name . " \r\n";
    $header2 .= "Return-Path: $from_mail\r\n";
    $header2 .= "Content-type: multipart/mixed; boundary=\"$boundary\"\r\n";
    $header2 .= "$boundary\r\n";
    //    $res = mail($to, $subject, $header, $header2, "-r" . $from_mail);
    //    if ($res) {
    // send from visualisationService
    $arrayPost = array(
        'from' => getenv('SMTP_USER_LOGIN'),
        'to' => $to,
        'subject' => $subject,
        'text' => '',
        'html' => $message,
        'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
    $result = CURL::sendDataCurl($url, $arrayPost);
    if ($result) {
        echo 'DONE';
    } else {
        echo 'NOTDONE';
    }
}

function creatAnchorTag($ptag_val, $ptag_title, $siteName, $censusid)
{

    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        if (strlen($ptag_val) > 12) {
            $ptagStr = "<p class='ellipsis' id='" . $ptag_title . "' title='" . $ptag_title . "'> <a href='javascript:'"
                . "onclick='redirect(&quot;$ptag_title&quot;,&quot;$siteName&quot;,&quot;$censusid&quot;);' style='text-color:#ffedsw;color: #2695ca;text-decoration: none;' >" . substr($ptag_val, 0, 13) . "..</a></p>";
        } else {
            $ptagStr = "<p class='ellipsis' id='" . $ptag_title . "' title='" . $ptag_title . "'> <a href='javascript:'"
                . "onclick='redirect(&quot;$ptag_title&quot;,&quot;$siteName&quot;,&quot;$censusid&quot;);' style='text-color:#ffedsw;color: #2695ca;text-decoration: none;' >" . $ptag_val . "</a></p>";
        }
    }
    return trim($ptagStr);
}

function createUnLimitedPTag($ptag_val, $ptag_title)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_title . '">' . $ptag_val . '</p>';
    }
    return trim($ptagStr);
}



function getCustomerDetails($key)
{
    $result = PTS_SB_getCustomerDetailsFunc($key);

    foreach ($result as $value) {

        $parts = explode('_', $value['companyName']);
        $last = array_pop($parts);
        $parts = array(implode('_', $parts), $last);

        $compOption .= '<option value="' . $value['compid'] . '">' . $parts[0] . '</option>';
    }
    echo $compOption;
}

function submitCommercialData($key)
{
    $skuid = url::requestToAny('skuid');
    $custid = url::requestToAny('custid');
    $subType = url::requestToAny('subType');

    $result = PTS_SB_submitCommercialDataFunc($key, $skuid, $custid, $subType);

    echo json_encode($result);
}

function attachSiteKey($key)
{
    $sitename   = url::requestToAny('sitename');
    $sitekey    = url::requestToAny('sitekey');

    $result = PTS_SB_attachSiteKey($key, $sitename, $sitekey);

    echo json_encode($result);
}


function activae_tenant()
{

    $pdo = pdo_connect();


    $eid           = $_SESSION['user']['cd_eid'];
    $licensekey    = url::requestToAny('licKey');

    $ret = activateTenant($eid, $licensekey, $pdo);
    echo $ret;
}

function updateSessionDy()
{
    $pdo = pdo_connect();
    $stype = url::requestToAny('stype');
    $svalu = url::requestToAny('svalue');

    if ($stype === 'LicenseKey') {
        $_SESSION['user']['licenseKey'] = $svalu;
    }
    $_SESSION["user"]["showIntroductoryPopup"] = 0;
    ob_clean();
    echo 'Session Update Success';
}
