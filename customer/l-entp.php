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
include_once '../lib/l-customer.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';
include_once '../lib/l-entpExport.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

if (url::issetInRequest('function')) {
    $function = url::requestToAny('function');

    if ($function) {
        $result = $function();
        echo $result;
    } else {
        echo "Your key has been expired";
    }
}

function ENTP_GetResellerGrid()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('channelId', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];

    $result = ENTP_GetResellers($conn, $loggedEid);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        foreach ($result as $key => $value) {
            $customer = UTIL_CreatPTag(UTIL_GetTrimmedSiteName($value['companyName']));
            $firstName = UTIL_CreatPTag($value['firstName']);
            $lastName = UTIL_CreatPTag($value['lastName']);
            $email = UTIL_CreatPTag($value['emailId']);
            $status = ($value['status'] == 1 || $value['status'] == "1") ? UTIL_CreatPTag('Enabled') : UTIL_CreatPTag('Disabled');

            $rowId = $value['eid'] . '---' . $value['customerNo'];
            $recordList[] = array(
                "DT_RowId" => $rowId,
                'reseller' => $customer,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'status' => $status
            );
        }
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function ENTP_GetCustomerGrid()
{
    $conn = db_connect();
    $channelId = UTIL_GetInteger('channelId', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];

    $result = ENTP_GetCustomers($conn, $channelId);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        foreach ($result as $key => $value) {
            $customer = UTIL_CreatPTag(UTIL_GetTrimmedSiteName($value['companyName']));
            $firstName = UTIL_CreatPTag($value['firstName']);
            $lastName = UTIL_CreatPTag($value['lastName']);
            $email = UTIL_CreatPTag($value['emailId']);
            $status = ($value['status'] == 1 || $value['status'] == "1") ? UTIL_CreatPTag('Enabled') : UTIL_CreatPTag('Disabled');

            $rowId = $value['eid'] . '---' . $value['customerNo'];
            $recordList[] = array(
                "DT_RowId" => $rowId,
                'customer' => $customer,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'status' => $status
            );
        }
    }

    $jsonData = array("draw" => 1, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function ENTP_GetResellers($db, $loggedEid)
{

    $customerType = $_SESSION["user"]["customerType"];
    if ($customerType == 0) {
        $sql = "SELECT firstName,lastName,emailId,companyName,status,eid,customerNo FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = '2'";
    } else {
        $sql = "SELECT firstName,lastName,emailId,companyName,status,eid,customerNo FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE entityId = '$loggedEid' AND ctype = '2'";
    }
    mysqli_query("SET CHARACTER SET utf8 ");
    $res = find_many($sql, $db);
    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function ENTP_GetSkuDtlsByCid()
{
    $cid = $_SESSION['user']['cId'];
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);


    $select_sku = "select eid,entityId,skulist from channel where eid='$cid' limit 1";
    $res_sku = find_one($select_sku, $db);

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $sql = "select id,skuName from skuMaster where id in($idval)";
    $resSKU = find_many($sql, $db);

    if (safe_count($resSKU) > 0) {
        echo json_encode($resSKU);
    }
}

function ENTP_GetCustomers($db, $channelId)
{

    $sql = "SELECT companyName,firstName,lastName,emailId,status,customerNo,eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE channelId = '$channelId' AND ctype = '5'";
    mysqli_query("SET CHARACTER SET utf8 ");
    $res = find_many($sql, $db);

    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}

function ENTP_AddReseller()
{
    $key = '';
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];

    $sname          = UTIL_GetString('reselFirstName', "");
    $slname         = UTIL_GetString('reselLastName', "");
    $scname         = UTIL_GetString('reselCompName', "");
    $semail         = UTIL_GetString('reselEmail', "");
    $compAddr       = UTIL_GetString('reselCompAddr', "");
    $compCity       = UTIL_GetString('reselCompCity', "");
    $compState      = UTIL_GetString('reselCompState', "");
    $comzipcode     = UTIL_GetString('reselCompZipcode', "");
    $compwebsite    = UTIL_GetString('reselCompWebsite', "");
    $skuVal         = UTIL_GetString('reselSkus', "");
    $language       = UTIL_GetString('language', "en");

    $retVal = ENTP_CreateReseller($key, $conn, $loggedEid, $sname, $slname, $scname, $semail, $compAddr, $compCity, $compState, $comzipcode, $compwebsite, $skuVal, $language);
    echo json_encode($retVal);
}

function ENTP_CreateReseller($key, $db, $loggedEid, $sname, $slname, $scname, $semail, $compAddr, $compCity, $compState, $comzipcode, $compwebsite, $skuVal, $language)
{

    $sql = "select eid,reportserver from " . $GLOBALS['PREFIX'] . "agent.channel P where P.eid = '$loggedEid'";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        $eid   = $res['eid'];
        $serverList = $res["reportserver"];

        $name       = $scname;
        $regnum     = '';
        $refnum     = '';
        $website    = $compwebsite;
        $address    = $compAddr;
        $city       = $compCity;
        $statprov   = $compState;
        $zipcode    = $comzipcode;
        $country    = '';
        $fname      = $sname;
        $lname      = $slname;
        $email      = $semail;
        $phnumber   = '';
        $loginusing = 'Email';
        $agentVal   = '';

        $entityId       = $eid;
        $channelId      = 0;
        $subchannelId   = 0;
        $outsourcedId   = 0;

        $res_reseller = RSLR_IsExist($key, $db, $name, $email);
        $res_user     = USER_IsExist($key, $db, $email);

        if ($res_reseller == true) {
            return array("status" => "error", "msg" => 'Company Name or Email Id Already exist');
        } else if ($res_user == true) {
            return array("status" => "error", "msg" => 'Email id Already exist');
        } else {

            $autoinc_sql = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'agent' AND   TABLE_NAME   = 'channel'";
            $autoinc_res = find_one($autoinc_sql, $db);
            $incrementId = $autoinc_res['AUTO_INCREMENT'];
            $resName = $name . '_' . $incrementId;
            $channelInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
                referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
                ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
                VALUES ('$entityId', 0, 0, $outsourcedId, '$resName', '$regnum', '$refnum', '$fname', '$lname', '$email', '$phnumber', '$address', '$city',
                '$zipcode', '$country', '$statprov', '$website','2','Channel','Commercial', '0', '$skuVal', '$serverList', '1','$loginusing', '" . time() . "', '', '', 1)";
            $channel_res = redcommand($channelInsertSql, $db);
            if ($channel_res) {

                $cid        = mysqli_insert_id();
                $pid        = RSLR_AddProcess($key, $db, $cid, $resName, 2);
                if ($pid == 0) {
                    $del_reseller = '';
                } else {
                    $userId = RSLR_AddSignupUser($resName, $fname, $lname, $email, $phnumber, $cid, 2, "dashboard", $language);
                }
                return array("status" => "success", "msg" => 'Your account has been successfully created, please check your mail for further instructions.');
            } else {
                return array("status" => "error", "msg" => 'Fail to create account. Please try again.');
            }
        }
    } else {
        return array("status" => "error", "msg" => 'Fail to create account. Please try again.');
    }
}

function ENTP_GetEntityDetails()
{
    $db = db_connect();
    $eid = UTIL_GetInteger("eid", "0");
    $sql = "SELECT firstName,lastName,emailId,companyName,address,city,zipcode,website,skulist,province " .
        "FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = '$eid' LIMIT 1";
    $res = find_one($sql, $db);
    $result = array();
    if (safe_count($res)) {
        $result['firstName'] = $res['firstName'];
        $result['lastName'] = $res['lastName'];
        $result['emailId'] = $res['emailId'];
        $result['companyName'] = UTIL_GetTrimmedSiteName($res['companyName']);
        $result['address'] = $res['address'];
        $result['city'] = $res['city'];
        $result['zipcode'] = $res['zipcode'];
        $result['website'] = $res['website'];
        $result['skulist'] = $res['skulist'];
        $result['province'] = $res['province'];
    }
    echo json_encode($result);
}


function ENTP_CreateCustomer()
{
    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $custCompName   = UTIL_GetString('custCompName', '');
    $custEmail      = UTIL_GetString('custEmail', '');
    $compNameExist  = RSLR_IsValueExist($conn, $custCompName);
    $compEmailExist = RSLR_IsValueExist($conn, $custEmail);

    if ($compNameExist) {
        $array = array("msg" => 'Customer Name already exist');
    } elseif ($compEmailExist) {
        $array = array("msg" => 'Customer email already exist');
    } else {
        $createCustomer = ENTP_Create_Customer($conn, $loggedEid);
        $array = $createCustomer;
    }

    return json_encode($array);
}


function ENTP_Create_Customer($db, $resellerId)
{

    $custCompName   = UTIL_GetString('custCompName', '');
    $custCompAddr   = UTIL_GetString('custCompAddr', '');
    $custCompCity   = UTIL_GetString('custCompCity', '');
    $custCompState  = UTIL_GetString('custCompState', '');
    $custCompZipcode = UTIL_GetString('custCompZipcode', '');
    $custCompCountry = UTIL_GetString('custCompCountry', '');
    $custCompWebsite = UTIL_GetString('custCompWebsite', '');
    $custFirstName  = UTIL_GetString('custFirstName', '');
    $custLastName   = UTIL_GetString('custLastName', '');
    $custEmail      = UTIL_GetString('custEmail', '');
    $aviraotc       = UTIL_GetString('aviraotc', '');
    $language       = UTIL_GetString('language', 'en');
    $skuVal         = UTIL_GetString('skuVal', '');
    $pccount        = 5;
    $trialSite      = 0;
    $agentEmail     = $_SESSION['user']['adminEmail'];
    $ctype          = 5;
    $entityId = $_SESSION["user"]["entityId"];
    $cId      = $_SESSION["user"]["cId"];

    $resellerDetails = ENTP_Get_Entity_Dtl($db, $cId);
    $serverUrl = $resellerDetails['reportserver'];
    $trialEndDate = time() + 2678400;
    $customerInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
     referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
     ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status, trialEnabled, trialStartDate, trialEndDate)
     VALUES ('$entityId', '$resellerId', '0', '0', '$custCompName', '0', '0', '$custFirstName', '$custLastName', '$custEmail', '0', '$custCompAddr', '$custCompCity',
     '$custCompZipcode', '$custCompCountry', '$custCompState', '$custCompWebsite','5','','Commercial','', '$skuVal', '$serverUrl', '0','Email','" . time() . "', '', '', 1, '1', '" . time() . "', '0')";

    $customerInsertRes = redcommand($customerInsertSql, $db);

    if ($customerInsertRes) {
        $custId = mysqli_insert_id();
        $custNo = date("Y") . '000' . $custId;
        $roleId  = USER_UserRoleWithCtype('', $db, $ctype);
        $userKey    = USER_UserKey($db);
        $roleItems   = ENTP_Get_MSPRoleItems($db);
        $insertProccess = ENTP_Create_ProcessMaster($db, $roleItems, $custId, $custCompName, $custFirstName, $custLastName);
        $createUser = ENTP_Create_User($db, $custId, $entityId, $resellerId, $roleId, $custFirstName, $custFirstName, $custLastName, $custEmail, $userKey);

        if ($createUser) {
            $userName = preg_replace('/\s+/', '_', $custFirstName);
            $sitename = ENTP_GetFilteredSiteName($custCompName, $custNo);

            $insertSite  = CUST_AddAviraSignupSites($db, $resellerId, $sitename);
            $createSite  = USER_InsertSite($db, $userName, $sitename);
            $sendMail    = User_SendEmail($db, $custFirstName, $custEmail, $userKey, '10', $resellerId, $language);

            return array("status" => "success", "msg" => $custCompName . " created Successfully, please check mail", "error" =>  mysqli_error($db));
        } else {
        }
    } else {
        return array("msg" => 'Fail to create new customer. Please try later.', "error" =>  mysqli_error($db));
    }
}

function ENTP_Get_MSPRoleItems($db)
{
    $res_pro = CUST_GetOptionsData($db, '11', 'process_data');
    $provVal = $res_pro['value'];
    $roleItems = explode(",", $provVal);
    return $roleItems;
}

function ENTP_Create_ProcessMaster($db, $roleItems, $custId, $custCompName, $custFirstName, $custLastName)
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
    $currentDate = time();
    $downloaderPath = $base_url . 'eula.php';
    $sitename = ENTP_GetFilteredSiteName($custCompName, $custNo);

    $process_sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$custId',processName = '" . $custCompName . "' ,siteCode = '" . $sitename . "',"
        . "deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',"
        . "setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',"
        . "WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='$downloaderPath',"
        . "phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',folnDarts='" . $folnDarts . "',"
        . "privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',"
        . "macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',"
        . "macProfileName='profile_mac'";

    $process_result = redcommand($process_sql, $db);
    return $process_result;
    $_SESSION["selected"]["ctype"] = '';
    $_SESSION["selected"]["eid"] = '';
}

function ENTP_Create_User($db, $ch_id, $entityId, $channelId, $roleId, $userName, $firstName, $lastName, $userEmail, $userKey)
{
    $userName = preg_replace('/\s+/', '_', $userName);
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

function ENTP_GetFilteredSiteName($tempSiteName, $customerNum)
{
    $res_site = preg_replace("/[^a-zA-Z0-9\s]/", "", $tempSiteName);
    $sitename = preg_replace('/\s+/', '_', $res_site);
    $sitename = trim($sitename) . '__' . trim($customerNum);
    return $sitename;
}

function ENTP_NewProvision()
{
    global $base_url;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $conn = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $customerNumber = UTIL_GetString('customerNum', '');
    $orderNum    = UTIL_GetString('orderNum', '');
    $SKU         = UTIL_GetString('skuVal', '');
    $cId         = UTIL_GetString('eid', '');

    $crmCustomerNum = ENTP_GetAutoCustNo($db);
    $crmOrderNum = ENTP_GetAutoCustNo($db);

    $pId = ENTP_GetProcessByCompany($cId);
    $currentDate = time();
    $remoteSession = "";
    $finStatusMsg = "";
    $oldCustOrd = "";

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = ENTP_Get_Entity_Dtl($cId);
    $companyName = $custDtl['companyName'];
    $reportserver = $custDtl["reportserver"];
    $email = $custDtl["emailId"];
    $customerFirstName = $custDtl["firstName"];
    $customerLastName = $custDtl["lastName"];
    $customerCountry = $custDtl["country"];
    $compId = $custDtl["eid"];

    $skuDetls = ENTP_GetNoofPcsBySku($SKU);
    $provCode = $skuDetls[1];
    $noOfDays = $skuDetls[0];
    if ($noofpc != "") {
        $noOfPc = $noofpc;
    } else {
        $noOfPc = $skuDetls[2];
    }

    $skuDesc = $skuDetls[3];
    $skuname = $skuDetls[4];
    $skuRef = $skuDetls[5];
    $payment_mode = $skuDetls[7];

    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");

    $sql_prod = "select * from processMaster P where P.pId = '$pId'";
    $res_prod = find_one($sql_prod, $db);

    $cmpName = preg_replace('/\s+/', '_', $companyName);
    $siteSku = preg_replace('/\s+/', '_', $skuRef);

    if ($new_site != "") {
        $sitename = $new_site;
    } else {
        $sitename = $cmpName . '_' . $siteSku;
    }


    $_SESSION['lob']['phoneNumber'] = $res_prod['phoneNo'];
    $_SESSION['lob']['chatLink'] = $res_prod['chatLink'];
    $_SESSION['lob']['ReplyEmailId'] = $res_prod['replyEmailId'];
    $_SESSION['lob']['serviceLink'] = $res_prod['serviceLink'];
    $_SESSION['lob']['privacyLink'] = $res_prod['privacyLink'];
    $_SESSION['lob']['variation'] = $res_prod['variation'];
    $_SESSION['lob']['locale'] = $res_prod['locale'];
    $_SESSION['lob']['SWLangCode'] = $res_prod['SWLangCode'];
    $_SESSION['lob']['folnDarts'] = $res_prod['folnDarts'];
    $_SESSION['lob']['FtpConfUrl'] = $res_prod['FtpConfUrl'];
    $_SESSION['lob']['WsServerUrl'] = $res_prod['WsServerUrl'];
    $_SESSION['lob']['DeployPath32'] = $res_prod['setupName32'];
    $_SESSION['lob']['DeployPath64'] = $res_prod['setupName64'];


    $variation    = $res_prod['variation'];
    $locale       = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail    = $res_prod['sendMail'];
    $backUp       = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];
    $subKey   = '';
    $licenKey = '';
    $backUpCapacity = 0;

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $orderNum, $email, $contractEDate, $provCode, $reportserver);

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = ENTP_GetDownloadId();
        $sessionid = md5(mt_rand());

        $sql_ser = "insert into customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $orderNum . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($email) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',subscriptionKey='" . $subKey . "',licenseKey='" . $licenKey . "'";
        $result = redcommand($sql_ser, $db);

        $dUrl = $base_url . 'eula.php?id=' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {
            $finStatusMsg = array("status" => "SUCCESS", "msg" => "Your $skuname account has been activated, Please click on NEXT button to add device", "link" => $dUrl, "payment_mode" => $payment_mode, "paymentstr" => $paymentstr);
        } else {
            $finStatusMsg = array("status" => "FAILED", "msg" => "Error to create entry", "link" => "");
        }
    } else {
        $finStatusMsg = array("status" => "FAILED", "msg" => "Error to create entry", "link" => "");
    }

    echo json_encode($finStatusMsg);
}


function ENTP_GetAutoCustNo($db)
{
    $custnum = rand(1000000, 9999999999);

    $cm_query = "select id,customerNum,orderNum from customerOrder where customerNum='$custnum' OR orderNum='$custnum'";
    $res_cm = find_one($cm_query, $db);
    $count = safe_count($res_cm);
    if ($count > 0) {
        ENTP_GetAutoCustNo($db);
    } else {
        return $custnum;
    }
}

function ENTP_GetProcessByCompany($cid)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $fld_sql = "select pId,cId,processName from processMaster where cId='$cid' order by pId desc limit 1";
    $fld_res = find_one($fld_sql, $db);

    $pId = $fld_res['pId'];
    return $pId;
}

function ENTP_GetNoofPcsBySku($SKU)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $sql = "select id,upgrdSku,renewSku,skuName,skuRef,ppid,licensePeriod,licenseCnt,description,skuName,payment_mode,site_criteria,tax,skuPrice from skuMaster where (skuRef='$SKU' or id='$SKU') limit 1";
    $resSKU = find_one($sql, $db);
    if (safe_count($resSKU) > 0) {
        $description = $resSKU['description'];
        $provCode = '01';
        $noOfDays = $resSKU['licensePeriod'];
        $noPC = $resSKU['licenseCnt'];
        $skuname = $resSKU['skuName'];
        $skuRef = $resSKU['skuRef'];
        $skuId = $resSKU['id'];
        $payMode = $resSKU['payment_mode'];
        $site_criteria = $resSKU['site_criteria'];
        $tax = $resSKU['site_criteria'];
        $skuprice = $resSKU['site_criteria'];
    }

    return array($noOfDays, $provCode, $noPC, $description, $skuname, $skuRef, $skuId, $payMode, $site_criteria, $tax, $skuprice);
}

function ENTP_Get_Entity_Dtl($id)
{

    global $db;
    global $base_url;

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $sql_ch = "select * from channel where eid='$id' limit 1";
    $res_core = find_one($sql_ch, $db);
    if ($res_core["ctype"] == 1 || $res_core["ctype"] == 2) {
        $res_core["outsourcedList"] = get_outsourcePartner($res_core["outsourcedId"]);
    }

    return $res_core;
}

function ENTP_GetDownloadId()
{

    try {

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "agent", $db);

        $downloadId = ENTP_GetPasswordId();

        $sql_Coust = "select id,customerNum,orderNum from customerOrder where downloadId='$downloadId'";
        $res_Coust = find_one($sql_Coust, $db);
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return ENTP_GetDownloadId();
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function ENTP_GetPasswordId()
{


    try {

        $character_set_array = array();
        $character_set_array[] = array('count' => 6, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $character_set_array[] = array('count' => 2, 'characters' => '0123456789');
        $temp_array = array();
        foreach ($character_set_array as $character_set) {
            for ($i = 0; $i < $character_set['count']; $i++) {
                $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
            }
        }
        shuffle($temp_array);
        $randomNo = implode('', $temp_array);
        return $randomNo;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}


function ENTP_GetSkuDetail($db, $skuId)
{
    $sql = "SELECT id, skuType, skuName, licensePeriod, noOfDays FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE id='$skuId' LIMIT 1";
    $res = find_one($sql, $db);
    return $res;
}


function ENTP_GetCustomerOrderGrid()
{

    $conn       = db_connect();
    $draw       = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid  = $_SESSION["user"]["cId"];
    $custId     = url::requestToAny('custid');
    $key        = '';


    $result = ENTP_GetMSPCustomerOrder($key, $conn, $custId);
    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);

        $recordList = ENTP_FormatCustomerOrderGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}

function ENTP_GetMSPCustomerOrder($key, $db, $loggedEid)
{
    $sql = "select C.siteName,C.id,C.customerNum,C.orderNum,C.compId,C.processId,FROM_UNIXTIME(C.orderDate, '%Y-%d-%m') orderDt,FROM_UNIXTIME(C.contractEndDate, '%Y-%d-%m') contractEndDt,C.noOfPc from"
        . " " . $GLOBALS['PREFIX'] . "agent.customerOrder C  where C.compId = '$loggedEid' group by C.customerNum,C.orderNum";
    $res = find_many($sql, $db);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function ENTP_FormatCustomerOrderGrid($resultArray)
{
    $array = [];
    $db = db_connect();
    foreach ($resultArray as $key => $value) {
        $customerNum = $value['customerNum'];
        $orderNum    = $value['orderNum'];
        $siteName    = $value['siteName'];
        $orderDt = $value['orderDt'];
        $noOfPc    = $value['noOfPc'];
        $contractEndDt    = $value['contractEndDt'];
        $compId      = $value['compId'];
        $processId   = $value['processId'];
        $installCnt  = $value['installedCnt'];

        $instalSql = "SELECT count(serviceTag) as count FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE " .
            "customerNum = '$customerNum' and orderNum = '$orderNum' and downloadStatus= 'EXE' AND revokeStatus='I'";
        $instalRes = find_one($instalSql, $db);

        if (safe_count($instalRes) > 0) {
            $count = $instalRes['count'];
        } else {
            $count = 0;
        }

        $rowId    = $customerNum . '---' . $orderNum . '---' . $compId . '---' . $processId;
        $array[] = array(
            "DT_RowId" => $rowId,
            'orderNum' => $orderNum,
            'pcCnt' => $noOfPc,
            'instal' => $count,
            'orderDt' => $orderDt,
            'cntDt' => $contractEndDt,
        );
    }

    return $array;
}

function ENTP_editReseller()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);

    $sname          = UTIL_GetString('reselFirstName', "");
    $slname         = UTIL_GetString('reselLastName', "");
    $compAddr       = UTIL_GetString('reselCompAddr', "");
    $compCity       = UTIL_GetString('reselCompCity', "");
    $compState      = UTIL_GetString('reselCompState', "");
    $comzipcode     = UTIL_GetString('reselCompZipcode', "");
    $compwebsite    = UTIL_GetString('reselCompWebsite', "");
    $skuVal         = UTIL_GetString('reselSkus', "");
    $eid            = UTIL_GetInteger('reselEid', 0);

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel SET firstName='$sname',lastName='$slname',address='$compAddr'," .
        "city='$compCity',zipcode='$comzipcode',website='$compwebsite',skulist='$skuVal',province='$compState' WHERE eid = $eid";
    $res = redcommand($sql, $db);

    if ($res) {
        echo json_encode(array("status" => "success", "msg" => 'Your account has been successfully updated.'));
    } else {
        echo json_encode(array("status" => "fail", "msg" => 'Some error occured try again later.'));
    }
}

function getSkuDetails()
{
    $conn = db_connect();
    $skuId = UTIL_GetString('skuId', '');

    $skuDtls = ENTP_GetSkuDetail($conn, $skuId);

    $licensePeriod = $skuDtls['licensePeriod'];
    $date = date("Y-m-d");
    $mod_date = strtotime($date . "+ " . $licensePeriod . " days");

    echo date("Y-m-d", $mod_date);
}

function ENTP_exportResellerData()
{

    $db = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];

    $result = ENTP_GetResellers($db, $loggedEid);

    Export_Reseller($result);
}

function ENTP_exportCustomerData()
{

    $db = db_connect();
    $loggedEid = $_SESSION['user']['cId'];

    $result = ENTP_GetCustomers($db, $loggedEid);
    Export_Customer($result);
}

function ENTP_exportOrderData()
{

    $db = db_connect();
    $loggedEid = $_SESSION['user']['cId'];
    $data = array();

    $channelSql = "SELECT GROUP_CONCAT(eid) as eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE channelId = $loggedEid limit 1";
    $channeleRes = find_one($channelSql, $db);

    $channelId = $channeleRes['eid'];

    $sql = "SELECT C.siteName,C.id,C.customerNum,C.orderNum,C.compId,C.processId," .
        "FROM_UNIXTIME(C.orderDate, '%Y-%d-%m') orderDt,FROM_UNIXTIME(C.contractEndDate, '%Y-%d-%m') contractEndDt," .
        "C.noOfPc,companyName FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.channel a  WHERE a.eid =C.compId and C.compId in ($channelId) group by C.customerNum,C.orderNum";

    $result = find_many($sql, $db);
    if (safe_count($result) > 0) {
        foreach ($result as $key => $val) {
            $custNum = $val['customerNum'];
            $orderNum = $val['orderNum'];
            $sql1 = "SELECT DISTINCT(count(serviceTag)) as count FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest " .
                "WHERE customerNum = '$custNum' and orderNum = '$orderNum' and downloadStatus= 'EXE' AND revokeStatus='I' limit 1";
            $sql1Res = find_one($sql1, $db);

            $data[$key]['customerNum'] = $custNum;
            $data[$key]['orderNum'] = $orderNum;
            $data[$key]['companyName'] = $val['companyName'];
            $data[$key]['orderDt'] = $val['orderDt'];
            $data[$key]['contractEndDt'] = $val['contractEndDt'];
            $data[$key]['noOfPc'] = $val['noOfPc'];
            $data[$key]['instalCount'] = safe_count($sql1Res) > 0 ? $sql1Res['count'] : 0;
        }
    }

    Export_OrderDetails($data);
}
