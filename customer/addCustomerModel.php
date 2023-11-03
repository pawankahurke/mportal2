<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-reseller.php';
include_once '../lib/l-user.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
require_once './aviraCustomerFunction.php';
require_once './addCustomerFunction.php';

$function = url::requestToText('function');
$function();

function createProcess()
{
    addProcess();
}

function createSKU()
{
    addSKU();
}

function createServer()
{
    addServer();
}

function createAdvServer()
{
    addAdvServer();
}

function editServer()
{
    updateServer();
}

function editAdvServer()
{
    updateAdvServer();
}


function createSite()
{
    addSite();
}

function createProvision()
{
    $returnMsg = getUrl();
    echo $returnMsg;
}

function getEntitlementDet()
{
    $returnMsg = get_details();
    echo $returnMsg;
}

function getCustomerNoDtl()
{
    $returnMsg = get_details_cust_num();
    echo $returnMsg;
}

function getServicetagDtl()
{
    $returnMsg = selServReqDetByServiceTag();
    echo $returnMsg;
}

function regenerateOrder()
{

    $returnMsg = get_orderRegerate_url();
    echo $returnMsg;
}

function regenarate_servicetag()
{

    $returnMsg = getServiceTagRegn_url();
    echo json_encode($returnMsg);
}

function revokeOrder()
{
    $returnMsg = get_orderRevoke_url();
    echo json_encode($returnMsg);
}

function get_SkuBy_CustmomerId()
{
    $returnMsg = getSkuByCustmomerId();
    echo $returnMsg;
}

function get_SiteBy_SKUId()
{
    $returnMsg = getSiteBySKUId();
    echo $returnMsg;
}

function get_OrderNo()
{
    $returnMsg = getOrderNo();
    echo $returnMsg;
}

function get_serviceTag()
{

    $cId = url::issetInRequest('cId') ? url::requestToAny('cId') : '';
    $pId = getProcessByCompany($cId);
    $orderNo = url::issetInRequest('orderNo') ? url::requestToAny('orderNo') : '';
    $custNo = url::issetInRequest('custNo') ? url::requestToAny('custNo') : '';
    $servicetag = getServiceTagList($cId, $pId, $orderNo, $custNo);
    echo $servicetag;
}

function get_hostserviceTag()
{

    $cId = url::issetInRequest('cId') ? url::requestToAny('cId') : '';
    $pId = getProcessByCompany($cId);
    $orderNo = url::issetInRequest('orderNo') ? url::requestToAny('orderNo') : '';
    $custNo = url::issetInRequest('custNo') ? url::requestToAny('custNo') : '';
    $hostName = url::issetInRequest('hostName') ? url::requestToAny('hostName') : '';
    $hostList = getHostNames($cId, $pId, $orderNo, $custNo, $hostName);
    echo $hostList;
}

function getProgressData()
{

    $dateMin = url::issetInRequest('dateMin') ? url::requestToAny('dateMin') : '';
    $dateMax = url::issetInRequest('dateMax') ? url::requestToAny('dateMax') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToAny('companyId') : '';
    $pId = getProcessByCompany($cId);

    $today = time();
    $mnth_back = strtotime("-30 days", $today);

    if ($dateMin == '') {
        $dateMin = $mnth_back;
    } else {
        $dateMin = $dateMin;
    }

    if ($dateMax == '') {
        $dateMax = $today;
    } else {
        $dateMax = $dateMax;
    }

    $progrs_data = get_orders_data($dateMax, $dateMin, $cId, $pId);


    if ($progrs_data) {
        echo "%%DONE%%" . json_encode($progrs_data);
    } else {
        echo "%%NOTDONE%%";
    }
}


function userListGrid()
{
    $result = getuserListGridData();
    echo json_encode($result);
}

function serviceRequesttGrid()
{

    $result = getServiceRequestData();
    echo json_encode($result);
}


function orderListGrid()
{

    $result = getorderListGridData();
    echo json_encode($result);
}


function orderHistoryGrid()
{
    $result = get_Customer_Order_History();
    echo json_encode($result);
}


function orderBycustomerNo()
{

    $result = getOrderBycustomerNo();
    echo $result;
}




function get_CompanyList()
{

    $result = get_company_value();
    echo $result;
}


function get_EntityCompanyList()
{

    $result = get_company_channel();
    echo $result;
}


function createUser()
{

    $userName = url::issetInRequest('userName') ? url::requestToAny('userName') : '';
    $userEmail = url::issetInRequest('userEmail') ? url::requestToAny('userEmail') : '';
    $roleId = url::issetInRequest('roleId') ? url::requestToAny('roleId') : '';
    $cId = $_SESSION["user"]["cId"];
    $agentCorpId = url::issetInRequest('agentCorpId') ? url::requestToAny('agentCorpId') : '';
    $siteList = url::issetInRequest('sitelist') ? url::requestToAny('sitelist') : '';

    $result = addUserCoreChannel($userName, $userEmail, $agentCorpId, $cId, $roleId, $siteList);
    echo $result;
}

function createAdvUser()
{

    $result = addUserCoreChannel();
    echo $result;
}

function updateAdvUser()
{

    $result = updateUserCoreChannel();
    echo $result;
}

function exportUserDetails()
{
    $pdo = pdo_connect();
    $index = 2;
    $cId = $_SESSION['user']['cId'];
    $export_sql = $pdo->prepare("select userid,username,user_email,user_phone_no,userStatus,role_id from " . $GLOBALS['PREFIX'] . "core.Users C where C.ch_id=? and (username != 'hfn' and username != 'admin')");
    $export_sql->execute([$cId]);
    $export_res = $export_sql->fetchAll();

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Username');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'User Email');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'User Phone');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Role Name');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'User Status');

    if ($export_res) {
        foreach ($export_res as $key => $val) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $val['username'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $val['user_email'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $val['user_phone_no'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . getRoleName($val['role_id'], $db) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . getUserStatusForCustomer($val['userStatus']) . '');
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Records Available');
    }

    $fn = "User_Details.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}


function validatepasswrd()
{
    $password = url::issetInRequest('password') ? url::requestToAny('password') : '';
    $repassword = url::issetInRequest('repassword') ? url::requestToAny('repassword') : '';
    $resetSession = url::issetInRequest('resetSession') ? url::requestToAny('resetSession') : '';
    $res = validatePassword($password, $repassword, $resetSession);
    echo $res;
}


function change_password()
{
    $oldpassword = url::issetInRequest('oldpassword') ? url::requestToAny('oldpassword') : '';
    $password = url::issetInRequest('password') ? url::requestToAny('password') : '';
    $userEmail = url::issetInRequest('userEmail') ? url::requestToAny('userEmail') : '';

    $res = changePassword($oldpassword, $password, $userEmail);
}


function updatepasswrd()
{
    $resetSession = url::issetInRequest('resetSession') ? url::postToAny('resetSession') : '';
    // $pwd = url::issetInRequest('password') ? url::postToAny('password') : '';  this was replaced
    $pwd = url::issetInRequest('password') ? $_REQUEST['password'] : '';
    $sql = "SELECT user_email FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userKey=? limit 1";
    $user = NanoDB::connect()->prepare($sql);
    $user->execute([$resetSession]);
    $user = $user->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $res = updatePassword($resetSession, $pwd);
    }

    if ($res["success"]) {
        nhUser::unblockUserLogin($user['user_email']);
    }
    echo json_encode($res);

    exit;
}

function pass_reset()
{
    $resetSession = url::issetInRequest('resetSession') ? url::requestToAny('resetSession') : '';
    $pwd = url::issetInRequest('password') ? url::requestToAny('password') : '';

    if ($resetSession != '') {
        $_SESSION["signup"]["vcode"] = $resetSession;
        $_SESSION["signup"]["password"] = $pwd;
        $_SESSION["signup"]["flow"] = "signup";
        echo 1;
    } else {
        $_SESSION["signup"]["vcode"] = '';
        $_SESSION["signup"]["password"] = '';
        $_SESSION["signup"]["flow"] = "";
        echo 0;
    }
}

function regeneratePassword()
{
    $useremail = url::issetInRequest('email') ? url::requestToAny('email') : '';
    $res = regenerateUserPassword($useremail);
    echo $res;
}

function validatevid()
{
    $resetSession = url::issetInPost('resetSession') ? url::postToText('resetSession') : '';
    $res = checkvid($resetSession);
    echo $res;
}

function validatesignupvid()
{
    $resetSession = url::issetInRequest('resetSession') ? url::requestToAny('resetSession') : '';
    $res = chec_signkvid($resetSession);
    echo $res;
}


function regenerateProvision()
{

    $res = regen_customer();
    echo $res;
}



function check_custome_order_details()
{


    $custNumber = url::issetInRequest('custNumber') ? url::requestToAny('custNumber') : '';
    $orderNo = url::issetInRequest('custOrdMchVal') ? url::requestToAny('custOrdMchVal') : '';
    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $ord_res = getRegenarateRevoke($custNumber, $orderNo, $cid);

    echo $ord_res;
}



function check_custome_order_details_byid()
{

    $ord_res = checkCustomeOrderDetailsById();
    echo $ord_res;
}

function getSKU_List()
{

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $skuListt = getSKUList($cid);
    echo $skuListt;
}

function get_contactEndDate()
{

    $contactDate = getContractEndDate();
    echo $contactDate;
}

function updateUser()
{
    $res = updateUserDtl();
    echo $res;
}

function deleteUser()
{
    $res = deleteUserDtl();
    echo $res;
}

function checkEmail()
{
    $res = checkAgentEmail();
    echo $res;
}


function get_graphData()
{

    $returnData = get_totalDeviceStatus();
    echo json_encode($returnData);
}


function get_installCount()
{

    $returnData = get_installReportGraphData();
    echo json_encode($returnData);
}


function getOrderSummary()
{

    $returnData = get_entitleOrderSummary();
    echo $returnData;
}

function getOnlineOrOffline()
{


    $serviceTag = url::issetInRequest('serviceTag') ? url::requestToAny('serviceTag') : '';
    $ordr_num = url::issetInRequest('ordr_num') ? url::requestToAny('ordr_num') : '';
    $cust_num = url::issetInRequest('cust_num') ? url::requestToAny('cust_num') : '';

    $status = get_onlineOrOffline($serviceTag, $cust_num, $ordr_num);

    if ($status != 0 && $status != 1 && $status != 2) {
        echo '%%%%%DONE%%%%%' . $cust_num . '%%%%%' . $ordr_num . '%%%%%' . $serviceTag . '%%%%%Online';
    } else if ($status == 1) {
        echo '%%%%%DONE%%%%%' . $cust_num . '%%%%%' . $ordr_num . '%%%%%' . $serviceTag . '%%%%%Offline';
    } else {
        echo '%%%%%NOTDONE%%%%%' . $cust_num . '%%%%%' . $ordr_num . '%%%%%' . $serviceTag . '%%%%%Not';
    }
}


function createTrialProvision()
{

    $returnMsg = getTrialUrl();
    echo $returnMsg;
}


function createMSPCustomer()
{

    $returnMsg = addMSPCustomer();
    echo $returnMsg;
}


function get_autoOrderNo()
{

    $returnMsg = getAutoOrderNo();
    echo $returnMsg;
}

function get_sites()
{

    $returnMsg = getSiteByid();
    echo $returnMsg;
}


function createEntitySKU()
{
    $returnMsg = addEntitySKU();
    echo json_encode($returnMsg);
}


function getEnServerList()
{
    $returnMsg = getServerDtl();
    echo $returnMsg;
}

function getChServerList()
{
    $returnMsg = getServerDtlForChannel();
    echo $returnMsg;
}

function getChSignUpServerList()
{
    $returnMsg = getServerDtlForChannel();
    echo $returnMsg;
}

function get_EntitySKUList()
{

    $returnMsg = getSkuTypeDtls();
    echo $returnMsg;
}

function get_OutSourcedList()
{
    $returnMsg = getOutSourcedList();
    echo $returnMsg;
}


function createEntity()
{
    $returnMsg = addEntityInfo();
    echo json_encode($returnMsg);
}

function createChannel()
{
    $returnMsg = addChannelInfo();
    echo json_encode($returnMsg);
}

function createResolvChannel()
{
    $returnMsg = addResolvChannelInfo();
    echo json_encode($returnMsg);
}

function createSubChannel()
{
    $returnMsg = addSubChannelInfo();
    echo json_encode($returnMsg);
}


function createOutSource()
{
    $returnMsg = addOutSourceInfo();
    echo json_encode($returnMsg);
}

function createCustomerInfo()
{


    $returnMsg = addNewCustomer();
    echo json_encode($returnMsg);
}

function addSubscriber()
{
    $returnMsg = getConsumerProvisionUrl();
    echo json_encode($returnMsg);
}

function addEntitlement_Subscriber()
{
    $returnMsg = getEntitleConsumerProvisionUrl();
    echo json_encode($returnMsg);
}

function get_channelSkuList()
{
    $cid = $_SESSION['user']['cId'];
    $returnMsg = getSkuDtlsByCidForChannel($cid);
    echo $returnMsg;
}

function get_signupchannelSkuList()
{
    $returnMsg = getSkuDtlsByCidForChannel('');
    echo $returnMsg;
}

function getSkuListForCustomers()
{
    $cid = $_SESSION['user']['cId'];
    $returnMsg = getProSKUList1($cid);
    echo $returnMsg;
}

function get_subChannelSkuList()
{
    $serviceTag = url::issetInRequest('serviceTag') ? url::requestToAny('serviceTag') : '';
    $returnMsg = getSkuTypeDtls();
    echo $returnMsg;
}


function get_chnlCustDtl()
{
    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $returnMsg = get_Entity_Dtl($cid);
    echo $returnMsg['companyName'] . '##' . $returnMsg['emailId'];
}

function get_channelDtl()
{
    $cid = url::issetInRequest('eid') ? url::requestToAny('eid') : '';
    $returnMsg = get_Entity_Dtl($cid);
    echo json_encode($returnMsg);
}

function get_editSkulist()
{

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $editSkulist = getSelectedEditSKU($cid);
    echo $editSkulist;
}

function get_editServerlist()
{

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $editSkulist = getSelectedEditserver($cid);
    echo $editSkulist;
}

function get_editSkulistChannel()
{

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $editSkulist = getSelectedEditSKUChannel($cid);
    echo $editSkulist;
}

function get_editServerlistChannel()
{

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $editSkulist = getSelectedEditserverChannel($cid);
    echo $editSkulist;
}


function updateEntity()
{
    $returnMsg = updateEntityInfo();
    echo json_encode($returnMsg);
}

function updateChannel()
{
    $returnMsg = updateChannelInfo();
    echo json_encode($returnMsg);
}


function updateSubChannel()
{
    $returnMsg = updateSubChannelInfo();
    echo json_encode($returnMsg);
}


function updateOutSource()
{
    $returnMsg = updateOutSourceInfo();
    echo json_encode($returnMsg);
}

function updateCustomerInfo()
{
    $returnMsg = updateNewCustomer();
    echo json_encode($returnMsg);
}

function resendPasswordMail()
{
    $cid = url::issetInRequest('eid') ? url::requestToAny('eid') : '';
    $returnMsg = resendMail($cid);
    echo $returnMsg;
}

function selectedOutsource()
{
    $cid = url::issetInRequest('eid') ? url::requestToAny('eid') : '';
    $returnMsg = get_outsourcePartner($id);
    echo $returnMsg;
}


function OrderHistory()
{
    $history = get_Order_History();
    return json_encode($history);
}

function getUserDetails()
{
    $userid = url::requestToAny('userid');
    $res = getUserDtl($userid);
    $chid = $res['ch_id'];
    $agentRoleId = $res['role_id'];
    $username = $res['username'];
    $firstName = ($res['firstName'] != "") ? $res['firstName'] : $res['username'];
    $lastname = $res['lastName'];
    $useremail = $res['user_email'];
    $usercoprId = $res['user_phone_no'];
    $userRights = getUserEeditRights($agentRoleId);

    $entiList = " ";
    $channelList = " ";
    $subchList = " ";
    $customerList = " ";

    $selectedUserEntityDtls = get_Entity_Dtl($chid);
    $selectedUserEntityCtype = $selectedUserEntityDtls["ctype"];
    switch ($selectedUserEntityCtype) {
        case 0:
            $entityIds = $res['entity_id'];
            $channelIds = $res['channel_id'];
            $subchIds = $res['subch_id'];
            $customerIds = $res['customer_id'];

            $entiList_Temp = get_userEntityList($entityIds, $customerIds, 0);
            $customerList_Temp = (explode("##", $entiList_Temp));
            $customerList .= $customerList_Temp[1];
            $entiList = $customerList_Temp[0];

            $channelList_Temp = get_userEntityList($channelIds, $customerIds, 1);
            $customerList_Temp = (explode("##", $channelList_Temp));
            $customerList .= $customerList_Temp[1];
            $channelList = $customerList_Temp[0];

            $subchList_Temp = get_userEntityList($subchIds, $customerIds, 2);
            $customerList_Temp = (explode("##", $subchList_Temp));
            $customerList .= $customerList_Temp[1];
            $subchList = $customerList_Temp[0];

            break;
        case 1:
            $entityIds = $res['entity_id'];
            $channelIds = $res['channel_id'];
            $subchIds = $res['subch_id'];
            $customerIds = $res['customer_id'];

            $channelList_Temp = get_userEntityList($channelIds, $customerIds, 1);
            $customerList_Temp = (explode("##", $channelList_Temp));
            $customerList .= $customerList_Temp[1];
            $channelList = $customerList_Temp[0];

            $subchList_Temp = get_userEntityList($subchIds, $customerIds, 2);
            $customerList_Temp = (explode("##", $subchList_Temp));
            $customerList .= $customerList_Temp[1];
            $subchList = $customerList_Temp[0];

            break;
        case 2:
            $channelIds = $res['channel_id'];
            $subchIds = $res['subch_id'];
            $customerIds = $res['customer_id'];

            $subchList_Temp = get_userEntityList($subchIds, $customerIds, 2);
            $customerList_Temp = (explode("##", $subchList_Temp));
            $customerList .= $customerList_Temp[1];
            $subchList = $customerList_Temp[0];
            break;
        case 3:
            $subchIds = $res['subch_id'];
            $customerIds = $res['customer_id'];

            $subchList = get_userEntityList($subchIds, $customerIds, 3);
            $customerList_Temp = (explode("##", $subchList));
            $customerList .= $customerList_Temp[1];
            break;
        case 4:
            break;
        case 5:
            break;
        default:
            break;
    }


    $res = $firstName . "##" . $useremail . "##" . $usercoprId . "##" . $userRights . "##" . $selectedUserEntityCtype . "##" . $entiList . "##" . $channelList . "##" . $subchList . "##" . $customerList . "##" . $selectedUserEntityDtls['eid'] . "##" . $lastname;
    echo $res;
}


function updateDetails()
{
    $updateMsg = updateChannelDetails();
    echo $updateMsg;
}

function get_OrderDetails()
{
    $details = get_Order_Details();
    echo json_encode($details);
}

function get_OrderDetails_CustomerNum()
{
    $details = get_Order_Details_CustomerNum();
    echo json_encode(array("detail" => $details[0], "siteList" => $details[1]));
}

function isRefNumberExist()
{
    try {
        $pdo = pdo_connect();
        $number = url::requestToAny('refnumber');

        $sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder where orderNum =? limit 1");
        $sql->execute([$number]);
        $sql_res = $sql->fetch();

        if (safe_count($sql_res) > 0) {
            return 1;
        } else {
            return 0;
        }
    } catch (Exception $exc) {
        error_log($exc, 0);
    }
}

function addMoreSubcription()
{
    $pdo = pdo_connect();
    $name = url::issetInPost('name') ? url::postToAny('name') : '';
    $refnum = url::issetInPost('companyVatId') ? url::postToAny('companyVatId') : '';
    $fname = url::issetInPost('fname') ? url::postToAny('fname') : '';
    $lname = url::issetInPost('lname') ? url::postToAny('lname') : '';
    $email = url::issetInPost('email') ? url::postToAny('email') : '';
    $cId = url::issetInPost('selectedCid') ? url::postToAny('selectedCid') : '';
    $orderRefNumber = url::issetInPost('orderRefNumber') ? url::postToAny('orderRefNumber') : '';

    $sql_cust_order = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder where orderNum =? limit 1");
    $sql_cust_order->execute([$orderRefNumber]);
    $sql_cust_order_res = $sql_cust_order->fetch();
    if (safe_count($sql_cust_order_res) > 0) {
        $msg = array("msg" => 'Order Number already exist.');
    } else {
        $skuVal = url::issetInPost('skuVal') ? url::postToAny('skuVal') : '';
        $returnMsg = generateProvUrl($refnum, $orderRefNumber, $fname, $lname, $email, $skuVal, $cId, $name);
        if ($returnMsg == "NOTDONE") {
            $msg = array("msg" => "Fail to create new provision. Please try later.");
        } else {
            $msg = array("link" => urldecode($returnMsg), "msg" => "Please Copy following link.");
        }
    }


    echo json_encode($msg);
}

function getChnlDtl()
{
    $selVal = url::issetInRequest('selVal') ? url::requestToAny('selVal') : '';
    $eid = url::issetInRequest('eid') ? url::requestToAny('eid') : '';
    $ctype = url::issetInRequest('ctype') ? url::requestToAny('ctype') : '';
    $returnMsg = get_ChnlDtl($selVal);
    echo $returnMsg;
}

function get_SelSiteList()
{
    $eid = url::issetInRequest('eid') ? url::requestToAny('eid') : '';
    $returnMsg = getSelSiteList($eid);
    echo $returnMsg;
}


function getHierarchiArray()
{
    $result = getHierarchiArrayFunction();
    echo $result;
}

function paymentModeCheck()
{
    $result = paymentModeCheck_Function();
    echo $result;
}

function updateRenew()
{

    $result = upgradeRenewUrl();
    echo json_encode($result);
}

function getProduct_List()
{

    $cid = isset($_SESSION["signupchnlid"]) ? $_SESSION["signupchnlid"] : '';
    $skuListt = getEntitySKUList($cid);
    echo $skuListt;
}

function getSite_List()
{

    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $skuListt = get_Customers_SitesList($cid);
    echo $skuListt;
}

function add_subscription_grid()
{
    $grid_result = get_add_subscription_temp_grid();
    echo json_encode($grid_result);
}

function add_subscription_grid_Param()
{
    $grid_result = get_add_subscription_temp_grid();
    echo json_encode($grid_result);
}

function add_subscriberByProduct()
{
    $result = getProductProvisionUrl();
    echo json_encode($result);
}

function add_subscriberByConsumer()
{
    $result = getConsumer_Product_ProvisionUrl();
    echo json_encode($result);
}

function change_site_name()
{

    $result = updateSiteName();
    echo $result;
}

function entitlement_grid()
{
    $grid_result = get_entitlement_grid();
    echo json_encode($grid_result);
}

function fetchDownloadUrl()
{
    $result = get_fetchDownloadUrl();
    echo $result;
}

function get_validateLicenseKey()
{
    $licenseKey = url::requestToAny('licenseKey');
    $custId = url::requestToAny('licenseKey');
    $result = validateLicenseKey($licenseKey);
    echo $result;
}

function get_validateSite()
{
    $custId = url::requestToAny('custId');
    $licenseKey = url::requestToAny('licenseKey');
    $result = getEntitlementSKU($custId, '');
    echo $result;
}


function user_signup()
{

    $firstname = url::issetInRequest('firstname') ? url::requestToAny('firstname') : '';
    $lastname = url::issetInRequest('lastname') ? url::requestToAny('lastname') : '';
    $phoneId = url::issetInRequest('phoneId') ? url::requestToAny('phoneId') : '';
    $companyName = url::issetInRequest('companyName') ? url::requestToAny('companyName') : '';
    $emailId = url::issetInRequest('emailId') ? url::requestToAny('emailId') : '';
    $customerType = url::issetInRequest('customerType') ? url::requestToAny('customerType') : '';
    $msg = userSignUp($firstname, $lastname, $phoneId, $companyName, $emailId, $customerType);
    echo $msg;
}

function confirmTrial()
{
    $skuVal = url::requestToAny('skuVal');
    $compId = url::requestToAny('companyId');
    $licenseKey = url::requestToAny('licenseKey');

    $res = get_confirmTrial($skuVal, $compId, $licenseKey);
    echo $res;
}

function confirmEntitleMentTrial()
{
    $skuVal = url::requestToAny('skuVal');
    $compId = url::requestToAny('companyId');

    $res = get_confirmEntitleMentTrial($skuVal, $compId);
    echo $res;
}

function validateSubscriptionLicenseKey()
{
    $pdo = pdo_connect();
    $licenseKey = url::requestToAny('licenseKey');
    $sql_license = $pdo->prepare("select id,customerNum,orderNum, licenseKey,SKUNum,sessionid,sessionIni from " . $GLOBALS['PREFIX'] . "agent.customerOrder where subscriptionKey=? limit 1");
    $sql_license->execute([$licenseKey]);
    $license_res = $sql_license->fetch();
    if (safe_count($license_res) > 0) {
        if ($license_res['sessionIni'] == '') {
            echo 0;
        } else {
            echo 1;
        }
    } else {
        echo 2;
    }
}

function entitlement_DownloadURL()
{
    $sid = url::requestToAny('sid');
    $cid = url::requestToAny('cid');
    $url = getEntitleUrl($sid, $cid);
    echo $url;
}


function CustomersLevel()
{
    $result = getCustomersLevel();
    echo $result;
}

function userChannelList()
{
    $result = getUserChannelList();
    echo $result;
}

function edit_userChannelList()
{
    $result = getEditUserChannelList();
    echo $result;
}

function userSubChannelList()
{
    $result = getUserSubChannelList();
    echo $result;
}

function customersSiteList()
{
    $result = getCustomersSiteList();
    echo $result;
}

function edit_customersSiteList()
{
    $result = getEdit_CustomersSiteList();
    echo $result;
}

function user_Hierarchy()
{
    $result = getUser_Hierarchy();
    echo $result;
}

function salesAgentForCustomer()
{
    $result = getSalesAgentForCustomer();
    echo $result;
}



function add_orderDetails()
{

    addOrderDeatils();
}

function add_customer()
{

    createCustomer();
}

function add_reseller()
{

    createReseller();
}


function addSitename()
{

    $res = creteSite();
    echo json_encode($res);
}

function addAviraOtc()
{
    $otcVal = url::requestToAny('aviraOtc');
    $new_email = url::requestToAny('aviraEmail');
    $new_compName = url::requestToAny('aviraCompName');

    $res = generateAviraLicense($otcVal, $new_email, $new_compName, 1, 0);
    echo json_encode($res);
}


function get_installDetails()
{

    $res = getInstallDetails();
    echo json_encode($res);
}






function getLicenseCount()
{
    $key = '';
    $pdo = pdo_connect();

    $jsonRes = array("totalLicenses" => 0, "unusedLicenses" => 0, "expiryLicenses" => 0);
    $loggedEid = $_SESSION["user"]["cId"];
    $licenseCountRes = RSLR_GetLicenseCount($key, $db, $loggedEid);

    if (safe_count($licenseCountRes) > 0) {
        $jsonRes["totalLicenses"] = $licenseCountRes['lseCnt'];
        $jsonRes["unusedLicenses"] = $licenseCountRes['lseCnt'] - $licenseCountRes['insCnt'];
    }
    echo json_encode($jsonRes);
}


function getOrderGridData()
{
    $key = '';
    $pdo = pdo_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToAny('draw');
    $whereClause = getWhereClause("chnl_id", "desc");
    $totalCount = RSLR_GetOrderDetailsGrid($key, $db, $loggedEid, "");
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());

    if (safe_count($totalCount) != 0) {
        $resultArray = RSLR_GetOrderDetailsGrid($key, $db, $loggedEid, $whereClause);
        foreach ($resultArray as $key => $value) {
            $recordList[] = array(
                "orderNum" => '1111',
                "licenses" => $value['licenseCnt'],
                "purchaseDate" => ($value['purchaseDate'] != "") ? date("m/d/Y h:i A", $value['purchaseDate']) : "Not Available",
                "validity" => '1 Year'
            );
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalCount), "recordsFiltered" => safe_count($totalCount), "data" => $recordList);
    }
    echo json_encode($jsonData);
}


function getCustomerGridData()
{
    $key = '';
    $pdo = pdo_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToAny('draw');
    $whereClause = getWhereClause("companyName", "desc");
    $totalCount = RSLR_GetCustomerDetailsGrid($key, $db, $loggedEid, "");
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());

    if (safe_count($totalCount) != 0) {
        $resultArray = RSLR_GetCustomerDetailsGrid($key, $db, $loggedEid, $whereClause);
        foreach ($resultArray as $key => $value) {
            $customerNum = 1;
            $orderNum = 1;
            $recordList[] = array("companyName" => '<p class="ellipsis" onclick="getCustomersDevices(' . $customerNum . ',' . $orderNum . ');" title="' . $value['companyName'] . '">' . $value['companyName'] . '</p>', "licenses" => $value['noOfPc']);
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalCount), "recordsFiltered" => safe_count($totalCount), "data" => $recordList);
    }
    echo json_encode($jsonData);
}


function getCustomerDevicesData()
{
    $key = '';
    $pdo = pdo_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToAny('draw');
    $whereClause = getWhereClause("companyName", "desc");
    $totalCount = RSLR_GetCustomerDevicesGrid($key, $db, $loggedEid, "");
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());

    if (safe_count($totalCount) != 0) {
        $resultArray = RSLR_GetCustomerDevicesGrid($key, $db, $loggedEid, $whereClause);
        foreach ($resultArray as $key => $value) {
            $customerNum = 1;
            $orderNum = 1;
            $recordList[] = array("companyName" => '<p class="ellipsis" onclick="getCustomersDevices(' . $customerNum . ',' . $orderNum . ');" title="' . $value['companyName'] . '">' . $value['companyName'] . '</p>', "licenses" => $value['noOfPc']);
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalCount), "recordsFiltered" => safe_count($totalCount), "data" => $recordList);
    }
    echo json_encode($jsonData);
}

function getWhereClause($defaultOrder, $ordertype)
{
    $where = '';
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $draw = url::requestToAny('draw');

    if ($length != -1) {
        $limit = " limit $start , $length ";
    } else {
        $limit = '';
    }

    // $searchVal = url::requestToAny('search')['value'];
    $orderval = url::requestToAny('order')[0]['column'];

    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = " order by $defaultOrder $ordertype ";
    } else {
        $orderValues = " order by $defaultOrder $ordertype ";
    }

    $where = $orderValues . $limit;
    return $where;
}






function getUserGridData()
{
    $key = '';
    $pdo = pdo_connect();
    $ch_id = $_SESSION["user"]["cId"];
    $draw = url::requestToAny('draw');
    $whereClause = getWhereClause("ch_id", "desc");
    $user_res = USER_GetAllUsers($key, $db, $ch_id);

    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    if (safe_count($user_res) != 0) {
        $jsonData = createUserGridDataArray($user_res);
    }
    echo json_encode($jsonData);
}

function createUserGridDataArray($user_res)
{
    $role_name_array = array("0" => "Admin", "1" => 'Agent', "2" => 'Reseller', "5" => "Customer", "6" => "Not Available");
    $userStatusArray = array("Active" => 'Active', "Disabled" => 'Disabled', "In Active" => 'Pending');

    foreach ($user_res as $key => $value) {
        $userId = $value["userid"];
        $firstName = ($value['username'] != '') ? $value['firstName'] : $value['username'];
        $userEmail = $value['user_email'];
        $role_name = $role_name_array[USER_Entity_Role_Name($value['ch_id'], $db)];
        $userStatus = $userStatusArray[USER_Status($value['userStatus'], $value['password'])];

        $recordList[] = array(
            "firstName" => '<p class="ellipsis" onclick="" title="' . $firstName . '">' . $firstName . '</p>',
            "user_email" => '<p class="ellipsis" onclick="" title="' . $userEmail . '">' . $userEmail . '</p>',
            "role_name" => '<p class="ellipsis" onclick="" title="' . $role_name . '">' . $role_name . '</p>',
            "userStatus" => '<p class="ellipsis" onclick="" title="' . $userStatus . '">' . $userStatus . '</p>'
        );
    }
    return $recordList;
}


function checkTrialSite()
{

    $retVal = getResellerSite();
    echo $retVal;
}

function verifyOTC()
{

    $otcCode = url::requestToAny('otcCode');
    $emailid = url::requestToAny('email');
    $companyName = url::requestToAny('compName');
    $status = url::requestToAny('status');
    $sitename = url::requestToAny('sitename');
    $cmpRet = validateCustomerName($sitename);
    if ($cmpRet == 'NOT') {
        $otcExist = getOTCDetailsByOTCCode($otcCode);

        if (safe_count($otcExist) > 0) {
            $array['status'] = "Duplicate";
        } else {
            $generateAvira = generateAviraLicense($otcCode, $emailid, $companyName, $status, 1);
            $aviraStatus = $generateAvira['status'];
            if ($aviraStatus == 'SUCCESS') {
                $array['licenseCount'] = $generateAvira['licsCnt'];
                $array['pendingCount'] = $generateAvira['licsCnt'];
                $array['contractEDate'] = $generateAvira['contractEnds'];
                $array['pcCount'] = $generateAvira['licsCnt'];
                $array['used'] = $generateAvira['used'];
                $array['status'] = "New";
            } else {
                $array['status'] = "ERROR";
            }
        }
    } else {
        $array['status'] = $cmpRet;
    }
    echo json_encode($array);
}


function addCust_SendEmail($db, $userName, $userEmail, $passid, $mailType, $enid)
{
    global $base_url;
    $pdo = pdo_connect();
    $resetLink = $base_url . 'reset-password.php?vid=' . $passid;

    $key = '';
    $chProdtl = addCust_GetChannelProcessDetails($db, $enid);
    $fromEmail = $chProdtl['fromName'];

    $select_template = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype=? and country='USA' limit 1");
    $select_template->execute([$mailType]);
    $res_template = $select_template->fetch();


    $subject = $res_template['subjectline'];
    $message = $res_template['mailTemplate'];

    $NHimage = $base_url . '/vendors/images/20161103171845_nanoheal_logo.png';
    $NHFinalimage = $base_url . '/vendors/images/20161027170825_nanoheal_logo_final.png';
    $Picture1 = $base_url . '/vendors/images/20161103171453_Picture1.png';
    $facebookImg = $base_url . '/vendors/images/set13-social-facebook-gray.png';
    $twitterImg = $base_url . '/vendors/images/set13-social-twitter-gray.png';
    $forgotpassword = $base_url . 'forgot-password.php';

    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('NANOHEAL_FINAL', $NHFinalimage, $message);
    $message = str_replace('PICTURE1', $Picture1, $message);
    $message = str_replace('FACEBOOK_SOCIAL', $facebookImg, $message);
    $message = str_replace('PASSURL', $resetLink, $message);
    $message = str_replace('TWITTER_SOCIAL', $twitterImg, $message);
    $message = str_replace('FORGOTPASSWORD', $forgotpassword, $message);

    $fromEmailId = $fromEmail;

    $headers .= "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";
//    if (!mail($userEmail, $subject, $message, $headers)) {
    // send from visualisationService
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $userEmail,
      'subject' => $subject,
      'text' =>'',
      'html' => $message,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
  if (!CURL::sendDataCurl($url, $arrayPost)) {
    return 0;
  } else {
    return 1;
  }
}

function addCust_URLEmail($db, $userName, $userEmail, $url, $language)
{
    global $base_url;
    $pdo = pdo_connect();
    $fromEmailId = getenv('SMTP_USER_LOGIN');
    $select_template = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype='11' AND language=? LIMIT 1");
    $select_template->execute([$language]);
    $res_template = $select_template->fetch();

    $message = $res_template['mailTemplate'];
    $subject = $res_template['subjectline'];

    $message = str_replace('USERNAME', $userName, $message);
    $message = str_replace('URL', $url, $message);


    $headers = "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";

//    if (!mail($userEmail, $subject, $message, $headers)) {
    // send from visualisationService
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $userEmail,
      'subject' => $subject,
      'text' =>'',
      'html' => $message,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
    if (!CURL::sendDataCurl($url, $arrayPost)) {
        return 0;
    } else {
        return 1;
    }
}

function addCust_GetChannelProcessDetails($db, $cId)
{
    $pdo = pdo_connect();
    $sql_prod = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.cId =? limit 1");
    $sql_prod->execute([$cId]);
    $res_prod = $sql_prod->fetch();
    if (safe_count($res_prod) > 0) {
        return $res_prod;
    } else {
        return array();
    }
}

function add_aviraSubscription()
{
    $result = addAviraSubscription();
    echo json_encode($result);
}

function regenerate()
{
    $result = regenerateOTP();
}
