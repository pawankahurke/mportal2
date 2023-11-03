<?php





include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once 'l-db.php';

function CUST_GetAllCustomer($key, $db, $cId, $ctype)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        if ($ctype == 1 || $ctype == '1') {
            $reseller_sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE entityId in (?) and ctype=?");
            $reseller_sql->execute([$cId, 5]);
        } else if ($ctype == 2 || $ctype == '2') {
            $reseller_sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE channelId in (?) and ctype=?");
            $reseller_sql->execute([$cId, 5]);
        } else if ($ctype == 5 || $ctype == '5') {
            $reseller_sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid in (?) and ctype=?");
            $reseller_sql->execute([$cId, 5]);
        } else {
            $reseller_sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype=?");
            $reseller_sql->execute([5]);
        }

        $reseller_res = $reseller_sql->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($reseller_res) > 0) {
            return $reseller_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function CUST_GetProcessDetails($key, $db, $pId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql_prod = "select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = '$pId'";
        $res_prod = find_one($sql_prod, $db);
        if (safe_count($res_prod) > 0) {
            return $res_prod;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function CUST_GetChannelProcessDetails($key, $db, $cId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql_prod = "select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.cId = '$cId' limit 1";
        $res_prod = find_one($sql_prod, $db);
        if (safe_count($res_prod) > 0) {
            return $res_prod;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function CUST_GetChannelProcessDetails_PDO($key, $db, $cId)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql_prod = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.cId = ? limit 1");
        $sql_prod->execute([$cId]);
        $res_prod = $sql_prod->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res_prod) > 0) {
            return $res_prod;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function CUST_AddCustomer($key, $db)
{

    $pdb = NanoDB::connect();
    $custId = CUST_InsertCustomer($key, $db);
    $name = url::postToAny('customername');
    $email = url::postToAny('email');
    $refnumber = "";
    $fname     = url::postToAny('firstname');
    $lname     = url::postToAny('lastname');

    if ($custId != 0 || $custId != '0') {

        if ($refnumber != '') {
            $custNo = $refnumber;
        } else {
            $year = date("Y");
            $custNo = $year . '000' . $custId;
        }

        $updateCust = "update " . $GLOBALS['PREFIX'] . "agent.channel set customerNo=?,referenceNo=? where eid=?";
        $pdo = $pdb->prepare($updateCust);
        $cust_update = $pdo->execute(array($custNo, $custNo, $custId));

        $pid = RSLR_AddProcess_PDO($key, $db, $custId, $name, 5);

        if ($pid == 0) {
        } else {
            $addCoreUser = RSLR_AddResellerUser($name, $fname, $lname, $email, '0', $custId, 5);
            if ($addCoreUser == '0' || $addCoreUser == 0) {
                return array("msg" => 'Some error occurred, Please try again later');
            } else {
                return array("msg" => 'Customer created successfully');
            }
        }
    } else {
        return array("msg" => 'Fail to create new customer. Please try later.');
    }
}



function CUST_AddSubscription($key, $db)
{
    $eid = $_SESSION['user']['cId'];
    $loggedUserType = $_SESSION['user']['customerType'];
    $customerDetails = RSLR_GetEntityDtls_PDO($key, $db, $eid);

    if ($loggedUserType == 5 || $loggedUserType == '5') {
        $name = UTIL_GetString('subsname', '');
        $custNo = UTIL_GetString('customerNo', '');
        $ordernum = UTIL_GetString('ordernum', '');
        $fname = UTIL_GetString('firstname', '');
        $lname = UTIL_GetString('lastname', '');
        $email = UTIL_GetString('email', '');
        $phnumber = UTIL_GetString('phonenumber', '0');
        $skuVal = UTIL_GetString('subsskus', '');

        $pcCnt = '';
        $orderDate = '';

        $isCustExist  = CUST_IsNumberExist_PDO($key, $db, $custNo);
        $isOrderExist = CUST_IsNumberExist_PDO($key, $db, $ordernum);

        if ($isCustExist == 'true') {
            return array("msg" => "Entered customer number already exist,", "status" => 0);
        } else if ($isOrderExist == 'true') {
            return array("msg" => "Entered order number already exist,", "status" => 0);
        } else {
            $provisionId = CUST_GenerateProvUrl_PDO($custNo, $ordernum, $name, $lname, $email, $skuVal, $eid, $name, $pcCnt, $orderDate);
            $url = explode("##", $provisionId);

            if ($provisionId != "NOTDONE") {
                return array("link" => $url[0], "msg" => "customer id: " . $url[1], "status" => 1);
            } else {
                return array("msg" => "Fail to create new provision. Please try later.", "status" => 0);
            }
        }
    } else {
        return array("msg" => "You don't have enough rigths to create subscriptions", "status" => 0);
    }
}


function CUST_GenerateProvUrl_PDO($customerNumber, $customerOrder, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $pcCnt, $orderDate)
{

    global $base_url;
    global $download_ClientUrl;
    $db = NanoDB::connect();

    $agentEmail = $_SESSION["user"]["adminEmail"];
    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $crmCustomerNum = CUST_AutoCustNo_PDO($db);
    $crmOrderNum = CUST_AutoCustNo_PDO($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls_PDO('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetails_PDO('', $db, $SKU);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['noOfDays'];
    $noOfPc = $skuDetls['licenseCnt'];
    $skuDesc = $skuDetls['description'];
    $skuname = $skuDetls['skuName'];
    $skuRef = $skuDetls['skuRef'];
    $trial = $skuDetls['trial'];
    $degrade = $skuDetls['degrdSku'];
    $renew = $skuDetls['renewDays'];
    $upgrade = $skuDetls['upgrade'];

    $curDate = date("Y-m-d H:i:s");
    $sDays = 0;
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(date("Y-m-d H:i:s", $dateOfOrder) . " +$noOfDays day");
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();


    $res_prod = CUST_GetProcessDetails_PDO('', $db, $pId);
    $sitename = CUST_getSkuSite_PDO($cId, $pId, $SKU);
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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $upgrade . ',' . $upgrade;

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters_PDO($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId_PDO('', $db);

        $sessionid = md5(mt_rand());

        $customerCountry = trim($customerCountry);
        $customerEmailId = trim($customerEmailId);
        $skuRef = trim($skuRef);
        $fileString = mysql_real_escape_string($fileString);
        $noOfDays = trim($noOfDays);
        $noOfPc =  trim($noOfPc);

        $sql_ser = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = ?, orderNum = ?,
                refCustomerNum = ?,refOrderNum = ?, coustomerFirstName =?,
                coustomerLastName = ? , coustomerCountry = ?,
                emailId= ?,SKUNum = ?, SKUDesc = ?,
                orderDate =?, contractEndDate = ?, backupCapacity = ?,
                sessionid = ?, sessionIni = ?, validity = ?,
                noOfPc = ?,oldorderNum =?,provCode = ?,remoteSessionURL =?,
                agentId = ?,processId= ?,compId=?,downloadId=?,siteName=?,createdDate=?");

        $sql_ser->execute([
            $customerNumber, $customerOrder, $crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName,
            $customerCountry, $customerEmailId, $skuRef, $skuDesc, $dateOfOrder, $contractEnd, $backUpCapacity,
            $sessionid, $fileString, $noOfDays, $noOfPc, $oldCustOrd, $provCode, $remoteSession, $agentEmail, $pId, $cId, $downloadId, $sitename, $curDt
        ]);

        $result = $sql_ser->rowcount();
        $dUrl = $download_ClientUrl . 'eula.php?id=' . $downloadId . '##' . $downloadId;
        $mailStatus = '';

        if ($result > 0) {
            $finStatusMsg = $dUrl;
        } else {
            $finStatusMsg = "NOTDONE";
        }
    } else {
        $finStatusMsg = "NOTDONE";
    }

    return $finStatusMsg;
}

function CUST_RenewSubscription($key, $db)
{
    $eid = $_SESSION['user']['cId'];
    $pid = $_SESSION['user']['pid'];
    $loggedUserType = $_SESSION['user']['customerType'];
    $customerDetails = RSLR_GetEntityDtls_PDO($key, $db, $eid);


    if ($loggedUserType == 5 || $loggedUserType == '5') {
        $name = UTIL_GetString('subsname', '');
        $custnum = UTIL_GetString('customerNo', '');
        $oldOrdernum = UTIL_GetString('ordernum', '');
        $newordernum = UTIL_GetString('neworder', '');
        $isOrderExist = CUST_IsNumberExist_PDO($key, $db, $newordernum);

        if ($isOrderExist == 'true') {
            return array("msg" => "This order number already exist, please user other number", "status" => 0);
        }

        $email = UTIL_GetString('email', '');
        $phnumber = UTIL_GetString('phonenumber', '0');
        $skuVal = UTIL_GetString('subsskus', '');
        $contractEdate = UTIL_GetString('contractEdate', '');

        $pcCnt = '';
        $orderDate = '';
        $lname = '';
        $provisionId = CUST_GenerateProvUrl_PDO($custnum, $oldOrdernum, $name, $lname, $email, $skuVal, $eid, $name, $pcCnt, $orderDate);
        $url = explode("##", $provisionId);

        if ($provisionId != "NOTDONE") {
            return array("link" => $url[0], "msg" => "customer id: " . $url[1], "status" => 1);
        } else {
            return array("msg" => "Fail to create new provision. Please try later.", "status" => 0);
        }
    } else {
        return array("msg" => "You don't have enough rigths to create subscriptions", "status" => 0);
    }
}

function CUST_InsertCustomer($key, $db)
{
    global $base_url;
    global $download_ClientUrl;
    $ctype          = $_SESSION["user"]["customerType"];
    $eid            = $_SESSION["user"]["cId"];
    $serverUrl      = $_SESSION["user"]["reportServer"];

    $ctype       = '';
    $loginusing  = '';
    $orderinfo   = '';
    $hirearchyId = '';
    $entityId    = $_SESSION["user"]["entityId"];
    $channelId   = $eid;
    $subchannelId = 0;
    $outsourcedId = 0;

    $name      = UTIL_GetString('customername', '0');
    $email     = UTIL_GetString('email', '0');
    $regnumber = UTIL_GetString('regnumber', '0');
    $refnumber = UTIL_GetString('refnumber', '0');
    $website   = UTIL_GetString('website', 'NA');
    $address   = UTIL_GetString('address', 'NA');
    $city      = UTIL_GetString('city', 'NA');
    $statprov  = UTIL_GetString('stprov', 'NA');
    $zipcode   = UTIL_GetString('zipcode', '0');
    $country   = UTIL_GetString('country', 'NA');
    $fname     = UTIL_GetString('firstname', '');
    $lname     = UTIL_GetString('lastname', '');
    $phnumber  = UTIL_GetString('phnumber', '0');
    $skuVal    = UTIL_GetString('skuValue', '0');
    $loginusing = UTIL_GetString('loginUsing', 'Email');
    $addCustomer = UTIL_GetString('addcustomer', '0');
    $pcCnt     = UTIL_GetString('pcCnt', '');
    $ordergen  = UTIL_GetString('cust_ordergen', '');
    $channelInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
         referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
         ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
         VALUES ('$entityId', '$channelId', '$subchannelId', '$outsourcedId', '$name', '$regnumber', '$refnumber', '$fname', '$lname', '$email', '$phnumber', '$address', '$city',
         '$zipcode', '$country', '$statprov', '$website','5','$hirearchyId','$ctype','$ordergen', '$skuVal', '$serverUrl', '$addCustomer','$loginusing','" . time() . "', '', '', 1)";
    $cust_result = redcommand($channelInsertSql, $db);

    if ($cust_result) {
        return mysqli_insert_id($db);
    } else {
        return 0;
    }
}

function CUST_UpdateCustomer($key, $db)
{

    global $base_url;
    global $download_ClientUrl;
    $entityId       = $_SESSION["user"]["entityId"];
    $channelId      = $_SESSION["user"]["channelId"];
    $subchannelId   = $_SESSION["user"]["subchannelId"];
    $outsourcedId   = $_SESSION["user"]["outsourcedId"];

    $editId      = url::postToAny('editId');
    $name        = url::postToAny('name');
    $regnum      = url::postToAny('regnumber');
    $refnum      = url::postToAny('refnumber');
    $website     = url::postToAny('website');
    $address     = url::postToAny('addr');
    $city        = url::postToAny('city');
    $statprov    = url::postToAny('stprov');
    $zipcode     = url::postToAny('zpcode');
    $country     = url::postToAny('country');
    $fname       = url::postToAny('fname');
    $lname       = url::postToAny('lname');
    $email       = url::postToAny('email');
    $phnumber    = url::postToAny('phnumber');
    $ctype       = url::postToAny('ctype');
    $loginusing  = url::postToAny('loginusing');

    $editMode    = url::postToAny('editSubVal');
    $channelId   = url::postToAny('reseller_id');
    $agentVal    = url::postToAny('salesagent');
    $chstr = '';
    if ($channelId != '') {
        $chstr = 'channelId=' . $channelId . ',';
    }

    if ($editMode == 1) {
        $skuVal      = url::postToAny('skuValue');
        $pcCnt       = url::postToAny('pcCnt');
        $orderDate   = strtotime(url::postToAny('orderDate'));
    }

    $orderinfo   = '';
    $hirearchyId = '';
    $outsrcId    = $outsourcedId;


    $channelInsertSql = "update channel set $chstr firstName='$fname',lastName='$lname',phoneNo='$phnumber',address='$address',city='$city',zipCode='$zipcode',country='$country',province='$statprov',website='$website',businessLevel='$ctype',entyHirearchy='$hirearchyId',ordergen='$orderinfo',skulist='$skuVal',loginUsing='$loginusing' $logo $logoicon where eid='$editId'";
    $channel_res = redcommand($channelInsertSql, $db);
    if ($channel_res) {
        $addAgent = RSLR_updateSalesAgents('', $db, $editId, $agentVal);
        if ($editMode == 1) {
            $year = date("Y");
            $custNo = $year . '000' . $editId;
            $provision_url = CUST_GenerateProvUrl($custNo, $custNo, $fname, $lname, $email, $skuVal, $editId, $name, $agentVal, $pcCnt, $orderDate);
            if ($provision_url != "NOTDONE") {
                return array("link" => urldecode($provision_url), "msg" => "$name has been successfully updated.", "status" => 1);
            } else {
                return array("msg" => "Fail to create new provision. Please try later.", "status" => 0);
            }
        } else {

            return array("msg" => 'Customer updated Successfully', "status" => '1');
        }
    } else {
        return array("msg" => 'Fail to update channel. Please try later.', "status" => '0');
    }
}

function CUST_GenerateProvUrl($customerNumber, $customerOrder, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $pcCnt, $orderDate)
{

    global $base_url;
    global $download_ClientUrl;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $agentEmail = $_SESSION["user"]["adminEmail"];

    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $crmCustomerNum = CUST_AutoCustNo($db);
    $crmOrderNum  = CUST_AutoOrderNo($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetails('', $db, $SKU);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['noOfDays'];
    $noOfPc   = $skuDetls['licenseCnt'];
    $skuDesc  = $skuDetls['description'];
    $skuname  = $skuDetls['skuName'];
    $skuRef   = $skuDetls['skuRef'];
    $trial   = $skuDetls['trial'];
    $degrade   = $skuDetls['degrdSku'];
    $renew   = $skuDetls['renewDays'];
    $upgrade   = $skuDetls['upgrade'];

    $curDate  = date("Y-m-d H:i:s");
    $sDays = 0;
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(date("Y-m-d H:i:s", $dateOfOrder) . " +$noOfDays day");
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();


    $res_prod = CUST_GetProcessDetails('', $db, $pId);
    $sitename = CUST_getSkuSite($cId, $pId, $SKU);
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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $upgrade . ',' . $upgrade;

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId('', $db);

        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',\n"
            . "refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', \n"
            . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', \n"
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', \n"
            . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', \n"
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', \n"
            . "noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',\n"
            . "agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',createdDate='" . $curDt . "'";
        $result = redcommand($sql_ser, $db);

        $dUrl = $download_ClientUrl . 'eula.php?id=' . $downloadId . '##' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {

            $finStatusMsg = $dUrl;
        } else {
            $finStatusMsg = "NOTDONE";
        }
    } else {
        $finStatusMsg = "NOTDONE";
    }

    return $finStatusMsg;
}

function CUST_GenerateMSPProvUrl($customerNumber, $customerOrder, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $pcCnt, $orderDate, $siteName)
{

    global $base_url;
    global $download_ClientUrl;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $agentEmail = $_SESSION["user"]["adminEmail"];

    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $crmCustomerNum = CUST_AutoCustNo($db);
    $crmOrderNum  = CUST_AutoOrderNo($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetails('', $db, $SKU);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['noOfDays'];
    $noOfPc   = $pcCnt;
    $skuDesc  = $skuDetls['description'];
    $skuname  = $skuDetls['skuName'];
    $skuRef   = $skuDetls['skuRef'];
    $trial   = $skuDetls['trial'];
    $degrade   = $skuDetls['degrdSku'];
    $renew   = $skuDetls['renewDays'];
    $upgrade   = $skuDetls['upgrade'];

    $curDate  = date("Y-m-d H:i:s");
    $sDays = 0;
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(date("Y-m-d H:i:s", $dateOfOrder) . " +$noOfDays day");
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();

    $res_prod = CUST_GetProcessDetails('', $db, $pId);
    $sitename = $siteName;
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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $upgrade . ',' . $upgrade;

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId('', $db);

        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',\n"
            . "refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', \n"
            . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', \n"
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', \n"
            . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', \n"
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', \n"
            . "noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',\n"
            . "agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',createdDate='" . $curDt . "'";
        $result = redcommand($sql_ser, $db);

        $dUrl = $download_ClientUrl . 'eula.php?id=' . $downloadId . '##' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {

            $finStatusMsg = $dUrl;
        } else {
            $finStatusMsg = "NOTDONE";
        }
    } else {
        $finStatusMsg = "NOTDONE";
    }

    return $finStatusMsg;
}



function CUST_RenewProvUrl($customerNumber, $customerOrder, $newordernum, $customerFirstName, $customerLastName, $customerEmailId, $phnumber, $contractEdate, $SKU, $cId, $companyName, $pcCnt, $orderDate)
{

    global $base_url;
    global $download_ClientUrl;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $agentEmail = $_SESSION["user"]["adminEmail"];

    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $crmCustomerNum = CUST_AutoCustNo($db);
    $crmOrderNum  = CUST_AutoOrderNo($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetails('', $db, $SKU);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['noOfDays'];
    $noOfPc   = $skuDetls['licenseCnt'];
    $skuDesc  = $skuDetls['description'];
    $skuname  = $skuDetls['skuName'];
    $skuRef   = $skuDetls['skuRef'];
    $trial   = $skuDetls['trial'];
    $degrade   = $skuDetls['degrdSku'];
    $renew   = $skuDetls['renewDays'];
    $upgrade   = $skuDetls['upgrade'];

    $curDate  = date("Y-m-d H:i:s");
    $sDays = 0;
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(date("Y-m-d H:i:s", $dateOfOrder) . " +$noOfDays day");
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();

    $res_prod = CUST_GetProcessDetails('', $db, $pId);
    $sitename = CUST_getSkuSite($cId, $pId, $SKU);
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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $upgrade . ',' . $upgrade;

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $newordernum, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId('', $db);

        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $newordernum . "',\n"
            . "refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', \n"
            . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', \n"
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', \n"
            . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', \n"
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', \n"
            . "noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $customerOrder . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',\n"
            . "agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',createdDate='" . $curDt . "'";
        $result = redcommand($sql_ser, $db);

        $dUrl = $download_ClientUrl . 'eula.php?id=' . $downloadId . '##' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {

            $finStatusMsg = $dUrl;
        } else {
            $finStatusMsg = "NOTDONE";
        }
    } else {
        $finStatusMsg = "NOTDONE";
    }

    return $finStatusMsg;
}

function CUST_RESTAPI_RenewProvUrl($customerNumber, $customerOrder, $newordernum, $customerFirstName, $customerLastName, $customerEmailId, $phnumber, $contractEdate, $SKU, $cId, $companyName, $pcCnt, $orderDate)
{

    global $base_url;
    global $download_ClientUrl;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $agentEmail = $_SESSION["user"]["adminEmail"];

    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $crmCustomerNum = CUST_AutoCustNo($db);
    $crmOrderNum  = CUST_AutoOrderNo($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetailsByName("", $db, $SKU, $cId);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['noOfDays'];
    $noOfPc   = $skuDetls['licenseCnt'];
    $skuDesc  = $skuDetls['description'];
    $skuname  = $skuDetls['skuName'];
    $skuRef   = $skuDetls['skuRef'];
    $trial   = $skuDetls['trial'];
    $degrade   = $skuDetls['degrdSku'];
    $renew   = $skuDetls['renewDays'];
    $upgrade   = $skuDetls['upgrade'];

    $curDate  = date("Y-m-d H:i:s");
    $sDays = 0;
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(date("Y-m-d H:i:s", $dateOfOrder) . " +$noOfDays day");
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();

    $res_prod = CUST_GetProcessDetails('', $db, $pId);
    $sitename = CUST_getSkuSite($cId, $pId, $SKU);
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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $upgrade . ',' . $upgrade;

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $newordernum, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId('', $db);

        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $newordernum . "',\n"
            . "refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', \n"
            . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', \n"
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', \n"
            . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', \n"
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', \n"
            . "noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $customerOrder . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',\n"
            . "agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',createdDate='" . $curDt . "'";

        $result = redcommand($sql_ser, $db);

        $dUrl = $download_ClientUrl . 'eula.php?id=' . $downloadId . '##' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {

            $finStatusMsg = $dUrl;
        } else {
            $finStatusMsg = "NOTDONE";
        }
    } else {
        $finStatusMsg = "NOTDONE";
    }

    return $finStatusMsg;
}




function CUST_RenewProvision($customNum, $oldOrderNum, $orderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $siteName, $agentVal, $pcCnt, $orderDate, $uniDate, $liceOrderNum)
{

    global $base_url;
    global $download_ClientUrl;
    $remoteSession = '';
    $finStatusMsg = '';
    $sDays = 0;

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $agentEmail = $_SESSION["user"]["adminEmail"];
    $pId = getProcessByCompany($cId);

    $refCustomerNum = CUST_AutoCustNo($db);
    $refOrderNum  = CUST_AutoOrderNo($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetails('', $db, $SKU);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['licensePeriod'];
    $dateOfOrder = $orderDate;
    $contractEnd = $uniDate;
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();

    $res_prod = CUST_GetProcessDetails('', $db, $pId);

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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $res_prod['showUpgradeClient'];

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($siteName, $orderDate, $customNum, $orderNum, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId('', $db);

        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customNum . "', orderNum = '" . $orderNum . "',\n"
            . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', \n"
            . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '', \n"
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '', SKUDesc = '', \n"
            . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '', \n"
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', \n"
            . "noOfPc = '" . trim($pcCnt) . "',oldorderNum ='" . $oldOrderNum . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',\n"
            . "agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $customerFirstName . "',nhOrderKey='" . $liceOrderNum . "',createdDate='" . $curDt . "'";
        $result = redcommand($sql_ser, $db);

        if ($result > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }

    return $finStatusMsg;
}

function CUST_getSkuSite($cid, $pid, $sku)
{

    global $db;
    global $base_url;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);
    $sitename = '';
    $select_site = "select C.siteId,R.parameters,R.sitename from custSkuMaster C,skuMaster S,RegCode R where C.cId = '$cid' and C.pId='$pid' and C.skuId=S.id and ( S.id='$sku' or S.skuRef = '$sku') and C.siteId=R.id and C.status=1 limit 1";
    $res_site = find_one($select_site, $db);
    if (safe_count($res_site) > 0) {
        $sitename = $res_site['parameters'];
    }

    return $sitename;
}


function CUST_getSkuSite_PDO($cid, $pid, $sku)
{

    global $base_url;
    $db = NanoDB::connect();

    $sitename = '';
    $select_site = $db->prepare("select C.siteId,R.parameters,R.sitename from " . $GLOBALS['PREFIX'] . "agent.custSkuMaster C,skuMaster S,RegCode R where C.cId = ? and C.pId=? and C.skuId=S.id and ( S.id=? or S.skuRef = ?) and C.siteId=R.id and C.status=1 limit 1");
    $select_site->execute([$cid, $pid, $sku, $sku]);
    $res_site = $select_site->fetch();

    if (safe_count($res_site) > 0) {
        $sitename = $res_site['parameters'];
    }

    return $sitename;
}

function sendProvisionUrl($uname, $to, $url, $noOfPc, $customerOrder, $contractMailDate, $contractMailSDate, $agentVal, $companyName, $skuname, $country, $enid)
{

    global $db;
    global $base_url;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $reselUserArr = explode('--', $agentVal);
    $reselUser = $reselUserArr[1];

    $key = '';
    $chProdtl = CUST_GetChannelProcessDetails($key, $db, $enid);
    $fromEmail = $chProdtl['fromName'];


    $message = '';
    $message1 = '';
    $down_message = '';
    $subject = '';
    $subject1 = '';
    $agentEmail = $reselUserArr[0];
    $select_template = "select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='7' and chId='$enid' and country='$country' limit 1";
    $res_template = find_one($select_template, $db);
    if (safe_count($res_template) > 0) {
        $message = $res_template['mailTemplate'];
        $message1 = $res_template['mailTemplate'];
        $subject     = $res_template['subjectline'];
    } else {
        $select_template = "select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='7' and chId='$enid' and country='USA' limit 1";
        $res_template = find_one($select_template, $db);
        $message = $res_template['mailTemplate'];
        $message1 = $res_template['mailTemplate'];
        $subject     = $res_template['subjectline'];
    }


    $select_Download = "select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='8' and chId='$enid' and country='$country' limit 1";
    $res_downlaod = find_one($select_Download, $db);
    if (safe_count($res_downlaod) > 0) {
        $down_message = $res_downlaod['mailTemplate'];
        $subject1     = $res_downlaod['subjectline'];
    } else {
        $select_Download = "select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='8' and chId='$enid' and country='USA' limit 1";
        $res_downlaod = find_one($select_Download, $db);
        $down_message = $res_downlaod['mailTemplate'];
        $subject1     = $res_downlaod['subjectline'];
    }




    $NHimage = $base_url . '/vendors/images/20130309_RS_017P8278.jpg';
    $HPimage = $base_url . '/vendors/images/hp.gif';
    $ximage = $base_url . '/vendors/images/x.gif';

    $message = str_replace('USERNAME', $uname, $message);
    $message = str_replace('NHIMG', $NHimage, $message);
    $message = str_replace('HPIMG', $HPimage, $message);
    $message = str_replace('CMPNAME', $companyName, $message);
    $message = str_replace('SKUNAME', $skuname, $message);
    $message = str_replace('NOD', $noOfPc, $message);
    $message = str_replace('SDATE', $contractMailSDate, $message);
    $message = str_replace('EDATE', $contractMailDate, $message);
    $message = str_replace('XGIF', $ximage, $message);

    $message1 = str_replace('USERNAME', $reselUser, $message1);
    $message1 = str_replace('NHIMG', $NHimage, $message1);
    $message1 = str_replace('HPIMG', $HPimage, $message1);
    $message1 = str_replace('CMPNAME', $companyName, $message1);
    $message1 = str_replace('SKUNAME', $skuname, $message1);
    $message1 = str_replace('NOD', $noOfPc, $message1);
    $message1 = str_replace('SDATE', $contractMailSDate, $message1);
    $message1 = str_replace('EDATE', $contractMailDate, $message1);
    $message1 = str_replace('XGIF', $ximage, $message1);


    $down_message = str_replace('USERNAME', $uname, $down_message);
    $down_message = str_replace('NHIMG', $NHimage, $down_message);
    $down_message = str_replace('HPIMG', $HPimage, $down_message);
    $down_message = str_replace('PASSURL', $url, $down_message);
    $down_message = str_replace('XGIF', $ximage, $down_message);

    $fromEmailId = $fromEmail;

    USER_SendMail($uname, $to, $fromEmailId, $message, $subject);
    USER_SendMail($uname, $to, $fromEmailId, $down_message, $subject);
    USER_SendMail($reselUser, $agentEmail, $fromEmailId, $message1, $subject1);
}


function CUST_AutoCustNo($db)
{

    $custnum = rand(1000000, 9999999999);

    $cm_query = "select id,customerNum,orderNum from customerOrder where customerNum='$custnum'";
    $res_cm   = find_one($cm_query, $db);
    $count    = safe_count($res_cm);
    if ($count > 0) {
        CUST_AutoCustNo($db);
    } else {
        return $custnum;
    }
}
function CUST_AutoOrderNo($db)
{

    $ordernum = rand(1000000, 9999999999);

    $cm_query = "select id,customerNum,orderNum from customerOrder where orderNum='$ordernum' OR oldorderNum='$ordernum'";
    $res_cm   = find_one($cm_query, $db);
    $count    = safe_count($res_cm);
    if ($count > 0) {
        CUST_AutoOrderNo($db);
    } else {
        return $ordernum;
    }
}


function getProcessByCompany($cid)
{
    global $db;
    $db = pdo_connect();
    $fld_sql = "select pId,cId,processName from " . $GLOBALS['PREFIX'] . "agent.processMaster where cId=? order by pId desc limit 1";

    $pdo = $db->prepare($fld_sql);
    $pdo->execute(array($cid));
    $fld_res = $pdo->fetch(PDO::FETCH_ASSOC);
    $pId     = $fld_res['pId'];

    return $pId;
}


function create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportId, $trial, $degrdSku, $renew)
{
    global $db;
    global $base_url;
    $db           = db_connect();
    db_change($GLOBALS['PREFIX'] . "install", $db);
    global $file_path;
    $INI_FILEPATH = 'customer/ini/';
    $INI_FILENAME = 'NanoHeal';

    //$sql_reg = "select * from install.Servers where serverid='$reportId' limit 1";
	$sql_reg = "select * from ".$GLOBALS['PREFIX']."install.Servers where serverid='$reportId' limit 1";
    $res_reg = find_one($sql_reg, $db);

    if (safe_count($res_reg) > 0) {
        $url1 = explode("https://", $res_reg["url"]);
        if ($res_reg['advanced'] == 1) {
            $asseturl = explode("https://", $res_reg["asseturl"]);
            $configurl = explode("https://", $res_reg["configurl"]);
        } elseif ($res_reg['advanced'] == 0) {
            $asseturl = explode("https://", $res_reg["url"]);
            $configurl = explode("https://", $res_reg["url"]);
        }
        $url2 = explode("http://", $res_reg["url"]);
        if (safe_count($url1) > 1) {
            $url_domain         = explode(":", $url1[1]);
            $url_asset          = explode(":", $asseturl[1]);
            $url_config         = explode(":", $configurl[1]);
            $logserver          = $url_domain[0];
            $configserver       = $url_config[0];
            $assetserver        = $url_asset[0];
            $updateserver       = '';
            $installationserver = '';
            $mumserver          = '';
            $proserver          = '';
            $custdomain         = $url_domain[0];
        } else {
            $url_log            = explode(":", $url2[1]);
            $url_asset          = explode(":", $asseturl[1]);
            $url_config         = explode(":", $configurl[1]);
            $logserver          = $url_log[0];
            $configserver       = $url_config[0];
            $assetserver        = $url_asset[0];
            $updateserver       = '';
            $installationserver = '';
            $mumserver          = '';
            $proserver          = '';
            $custdomain         = $url_log[0];
        }

        $username   = $res_reg["username"];
        $password   = $res_reg["password"];
        $followonid = '';
        $delay      = '';

        $file       = $file_path . $INI_FILEPATH . $INI_FILENAME . '.ini';
        $fileString = file_get_contents($file);
        $serviceNum = 0;
        $fileString = str_replace('LogServer=', 'LogServer=' . $logserver, $fileString);
        $fileString = str_replace('AssetServer=', 'AssetServer=' . $assetserver, $fileString);
        $fileString = str_replace('ConfigServer=', 'ConfigServer=' . $configserver, $fileString);
        $fileString = str_replace('CustName=', 'CustName=' . $sitename, $fileString);
        $fileString = str_replace('CustDomain=', 'CustDomain=' . $custdomain, $fileString);
        $fileString = str_replace('ClientRealm=', 'ClientRealm=' . $sitename, $fileString);
        $fileString = str_replace('ClientUser=', 'ClientUser=' . trim($username), $fileString);
        $fileString = str_replace('ConfigPassword=', 'ConfigPassword=' . trim($password), $fileString);
        $fileString = str_replace('HPCPassword=', 'HPCPassword=' . trim($password), $fileString);
        $fileString = str_replace('CustomerNo=', 'CustomerNo=' . trim($customerNumber), $fileString);
        $fileString = str_replace('OrderNo=', 'OrderNo=' . trim($customerOrder), $fileString);
        $fileString = str_replace('CustomerEmail=', 'CustomerEmail=' . trim($customerEmailId), $fileString);
        $fileString = str_replace('UniDays=', 'UniDays=' . trim($contractEDate), $fileString);
        $fileString = str_replace('SWLangCode=', 'SWLangCode=' . trim($_SESSION['lob']['SWLangCode']), $fileString);
        $fileString = str_replace('HFNProvCode=', 'HFNProvCode=' . trim($provCode), $fileString);
        $fileString = str_replace('DisplayPhNo=', 'DisplayPhNo=' . trim($_SESSION['lob']['phoneNumber']), $fileString);
        $fileString = str_replace('ChatLink=', 'ChatLink=' . urlencode(trim($_SESSION['lob']['chatLink'])), $fileString);
        $fileString = str_replace('PrivacyStmntLink=', 'PrivacyStmntLink=' . trim($_SESSION['lob']['privacyLink']), $fileString);
        $fileString = str_replace('termsAndCondition=', 'termsAndCondition=' . trim($_SESSION['lob']['termsConditionLink']), $fileString);
        $fileString = str_replace('FollowOnDarts=', 'FollowOnDarts=' . $_SESSION['lob']['folnDarts'], $fileString);
        $fileString = str_replace('StartupProfName=', 'StartupProfName=' . trim($_SESSION['lob']['defaultProfile']), $fileString);
        $fileString = str_replace('Variation=', 'Variation=' . trim($_SESSION['lob']['variation']), $fileString);
        $fileString = str_replace('FtpConfUrl=', 'FtpConfUrl=' . trim($_SESSION['lob']['FtpConfUrl']), $fileString);
        $fileString = str_replace('WsServerUrl=', 'WsServerUrl=' . trim($_SESSION['lob']['WsServerUrl']), $fileString);
        $fileString = str_replace('showUpgradeClient=', 'showUpgradeClient=' . trim($_SESSION['lob']['showUpgradeClient']), $fileString);
        $fileString = str_replace('degrdSku=', 'degrdSku=' . trim($degrdSku), $fileString);
        $fileString = str_replace('trial=', 'trial=' . trim($trial), $fileString);
        $fileString = str_replace('renewDays=', 'renewDays=' . trim($renew), $fileString);
        $fileString = str_replace('DeployPath32=', 'DeployPath32=' . trim($_SESSION['lob']['DeployPath32']), $fileString);
        $fileString = str_replace('DeployPath64=', 'DeployPath64=' . trim($_SESSION['lob']['DeployPath64']), $fileString);
        $fileString = str_replace('FCMURL=', 'FCMURL=' . trim($base_url . '/communication/MobileRegister.php'), $fileString);
        return $fileString;
    } else {
        return 'FAIL';
    }
}


function CUST_SendMail($uname, $to, $url, $country, $enid)
{
    global $base_url;
    $db = NanoDB::connect();

    $select_template = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='4' and chId=? and country=? limit 1");
    $select_template->execute([$enid, $country]);
    $res_template = $select_template->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res_template) == 0) {
        $select_template = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='4' and chId=? and country='USA' limit 1");
        $select_template->execute([$enid]);
        $res_template = $select_template->fetch(PDO::FETCH_ASSOC);
    }

    $key = '';
    $chProdtl = CUST_GetChannelProcessDetails_PDO($key, $db, $enid);
    $fromEmail = $chProdtl['fromName'];

    $subject = $res_template['subjectline'];
    $message = $res_template['mailTemplate'];

    $NHimage = $base_url . '/vendors/images/20130309_RS_017P8278.jpg';
    $HPimage = $base_url . '/vendors/images/hp.gif';
    $ximage = $base_url . '/vendors/images/x.gif';

    $message = str_replace('USERNAME', $uname, $message);
    $message = str_replace('NHIMG', $NHimage, $message);
    $message = str_replace('HPIMG', $HPimage, $message);
    $message = str_replace('PASSURL', $url, $message);
    $message = str_replace('XGIF', $ximage, $message);

    $fromEmailId = $fromEmail;
    USER_SendMail($uname, $to, $fromEmailId, $message, $subject);
}


function addCoreSites($cid, $siteName, $agentVal)
{

    try {

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "core", $db);
        $reselUserArr = explode('--', $agentVal);
        $reselUser = $reselUserArr[1];

        $loggedEId      = $_SESSION["user"]["cId"];

        $serl_cust = "select * from Customers where customer= '$siteName' limit 1";
        $serl_res = find_one($serl_cust, $db);


        if (safe_count($serl_res) > 0) {
        } else {
            $name = preg_replace('/\s+/', '_', $reselUser);
            $addResSite = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $name . "', '" . $siteName . "', 0, 0)";
            $resresult = redcommand($addResSite, $db);

            $sqlEntity = "select U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id in ('$loggedEId','$cid')";
            $ent_res = find_many($sqlEntity, $db);

            foreach ($ent_res as $entValue) {
                $addEnSite = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)";
                $result1 = redcommand($addEnSite, $db);
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function CUST_GetDownloadUrl($key, $db, $compId)
{
    global $base_url;
    global $download_ClientUrl;
    $url = '';
    $url_sql = "SELECT downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE compId = '$compId' LIMIT 1";
    $url_res = find_one($url_sql, $db);
    if (safe_count($url_res) > 0) {
        $downloadId     = $url_res['downloadId'];
        if ($downloadId == '' || $downloadId == 'undefined') {
            $url = 'Url is not available';
        } else {
            $url = $download_ClientUrl . 'eula.php?id=' . $downloadId;
        }
    } else {
        $url = 'Url is not available';
    }
    return $url;
}

function CUST_GetUserDetail($userid)
{

    global $db;
    db_change($GLOBALS['PREFIX'] . "core", $db);

    $user_sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users C where C.userid='$userid' limit 1";
    $user_res = find_one($user_sql, $db);
    if (safe_count($user_res) > 0) {
        return $user_res;
    } else {
        return array();
    }
}
function CUST_orderDtl($cid)
{

    global $base_url;
    global $db;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $ord_sql = "select customerNum,orderNum,SKUNum,FROM_UNIXTIME(orderDate,'%m/%d/%Y') ordDate,noOfPc from customerOrder where compId='$cid' order by id desc limit 1";
    $ord_res = find_one($ord_sql, $db);
    if (safe_count($ord_res) > 0) {
        return $ord_res;
    } else {
        return array();
    }
}

function CUST_orderDtlList($cid)
{

    global $base_url;
    global $db;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $ord_sql = "select customerNum,orderNum,SKUNum,FROM_UNIXTIME(orderDate,'%m/%d/%Y') ordDate,FROM_UNIXTIME(contractEndDate,'%m/%d/%Y') entDate,noOfPc,SKUDesc,downloadId,compId,processId from customerOrder where compId='$cid' order by id asc";
    $ord_res = find_many($ord_sql, $db);
    if (safe_count($ord_res) > 0) {
        return $ord_res;
    } else {
        return array();
    }
}

function CUST_installDtl($custNo, $ordNo, $cid, $pid)
{

    global $base_url;
    global $db;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $ord_sql = "select count(sid)cnt from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='$custNo' and orderNum='$ordNo' and processId='$pid' and compId='$cid' and revokeStatus='I'";
    $ord_res = find_one($ord_sql, $db);
    $cnt = $ord_res['cnt'];
    return $cnt;
}

function getCustResellerDtl()
{

    global $base_url;
    global $db;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $ord_sql = "select A.eid,A.entityId,A.companyName,A.firstName,A.lastName,A.emailId,U.userid from " . $GLOBALS['PREFIX'] . "agent.channel A,core.Users U where A.companyName='HP PI' and A.eid=U.ch_id and A.emailId=U.user_email order by eid asc limit 1";
    $ord_res = find_one($ord_sql, $db);
    if (safe_count($ord_res) > 0) {
        return $ord_res;
    } else {
        return array();
    }
}

function CUST_VerifyAndCreateCustomer($key, $db)
{
    global $base_url;
    $ctype = $_SESSION["user"]["customerType"];
    $eid = $_SESSION["user"]["cId"];
    $serverUrl = $_SESSION["user"]["reportServer"];
    $resultArray = [];


    if ($ctype == 1 || $ctype == '1') {
        return array("msg" => 'You don`t have rights to create customers.');
    } else if ($ctype == 2 || $ctype == '2') {

        $name = url::postToAny('customername');
        $email = url::postToAny('email');
        if (preg_match('/[\'^�$%&*()}{@#~?><>,|=_+�-]/', $name)) {
            $resultArray = array("msg" => 'Customer Name should not contains special characters.');
        } else {
            $isCustExist = RSLR_IsExist($key, $db, $name, $email);

            if ($isCustExist == true) {

                $resultArray = array("msg" => 'Customer Name or Email Id already exist.');
            } else {
                $resultArray = CUST_AddCustomer($key, $db);
            }
        }
    }
}

function CUST_InsertRegCodeSite($key, $db, $cid, $pid, $username, $siteName, $skuid)
{

    $admin = $_SESSION["user"]["username"];
    $adminid = $_SESSION["user"]["adminid"];
    global $db_host;

    $sql_site = "select parameters,sitename from RegCode where (sitename='" . $siteName . "' OR parameters='" . $siteName . "')";
    $res_site = find_one($sql_site, $db);

    if (safe_count($res_site) == 0) {

        $startDate = time();
        $endDate = strtotime('+367 day', time());
        $REPORT_URL = 'https://' . $db_host . ':443/main/rpc/rpc.php';

        $reg_sql = "insert into RegCode SET cId='$cid',pId='$pid',parameters='" . $siteName . "',startDate= '" . $startDate . "',endDate='" . $endDate . "',url='" . $REPORT_URL . "',sitename='" . $siteName . "'";
        $reg_result = redcommand($reg_sql, $db);

        if ($reg_result) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}



function CUST_Update_Insert_Servicetag($key, $db, $customerNumber, $customerOrder, $cId, $pId, $sid, $deviceDetails)
{
    $currentDate = time();
    $sessionid = md5(mt_rand());
    $companyName = $_SESSION["user"]["companyName"];
    $email = $_SESSION["user"]["adminEmail"];
    $downloadId = CUST_ServiceDownloadId($key, $db);

    $update_sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.serviceRequest SET revokeStatus='R' WHERE sid = ? ");
    $update_sql->execute([$sid]);
    $result_update = $update_sql->rowCount();

    $deviceDetailser = $deviceDetails['serviceTag'];
    $uninstallDate = $deviceDetails['uninstallDate'];
    $iniValues = mysql_real_escape_string($deviceDetails['iniValues']);
    $machineManufacture = $deviceDetails['machineManufacture'];
    $machineModelNum =  $deviceDetails['machineModelNum'];
    $backupCapacity = $deviceDetails['backupCapacity'];
    $serviceTag =  $deviceDetails['serviceTag'];
    $pcNo = $deviceDetails['pcNo'];
    $siteName = $deviceDetails['siteName'];

    $sql_seriveTag = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest SET customerNum = ?, orderNum = ?,
                    serviceTag = ?, sessionid = ?, agentPhoneId =?,
                    uninstallDate = ?, iniValues = ?,
                    machineManufacture = ?, machineModelNum = ?,
                    createdTime = ?, backupCapacity = ?, downloadStatus  =?,
                    oldServiceTag = ?, revokeStatus = ?, pcNo = ?,
                    downloadId=?,siteName=?,processId=?,compId=?");

    $sql_seriveTag->execute([
        $deviceDetailser, $customerNumber, $customerOrder, $sessionid, $email, $uninstallDate, $iniValues, $machineManufacture,
        $machineModelNum, $currentDate, $backupCapacity, 'G', $serviceTag, 'I', $pcNo, $downloadId, $siteName, $pId, $cId
    ]);
    $result_service = $sql_seriveTag->rowCount();

    $insertedId = $db->lastInsertId();

    if ($insertedId != 0 || $insertedId != '') {
        return $downloadId;
    } else {
        return '';
    }
}

function CUST_ServiceDownloadId($key, $db)
{
    try {

        $downloadId = USER_PasswordId($key);
        $sql_Coust = $db->prepare("SELECT sid FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE downloadId=?");
        $sql_Coust->execute([$downloadId]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);

        if ($count > 0) {
            return CUSTAJX_ServiceDownloadId($key, $db);
        }

        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}


function CUST_RevokeOrder($key, $db, $customerNumber, $customerOrder, $cId, $pId, $sid)
{
    global $base_url;
    global $download_ClientUrl;

    $deviceDetails = RSLR_GetDeviceDtls_PDO($key, $db, $sid);
    if (safe_count($deviceDetails) > 0) {
        if ($deviceDetails['revokeStatus'] != 'R') {

            $processResult = RSLR_GetProcessDetails($key, $db, $pId);
            $downloadPath = $processResult['downloaderPath'];
            $respectiveDB = $processResult['DbIp'];
            $downloadId = CUST_Update_Insert_Servicetag($key, $db, $customerNumber, $customerOrder, $cId, $pId, $sid, $deviceDetails);

            if ($downloadId != '') {
                return $download_ClientUrl . 'eula.php?sid=' . $downloadId;
            } else {
                return '';
            }
        } else {
            return 0;
        }
    }
}

function CUST_RegenerateOrder($key, $db, $customerNumber, $customerOrder, $cId, $pId, $sid)
{
    global $base_url;
    global $download_ClientUrl;

    $deviceDetails = RSLR_GetDeviceDtls_PDO($key, $db, $sid);
    if (safe_count($deviceDetails) > 0) {

        if ($deviceDetails['revokeStatus'] == 'I' && $deviceDetails['downloadStatus'] != 'EXE') {

            $downloadPath = $download_ClientUrl . 'eula.php?sid=' . $deviceDetails['downloadId'];
            return array("status" => 1, "link" => $downloadPath, "msg" => "Please click on Copy button to copy url");
        } else {
            return array("status" => 0, "link" => "", "msg" => "This machine is not available for regenerate");
        }
    } else {
        return array("status" => 2, "link" => "", "msg" => "Something went wrong");
    }
}




function CUST_GetCustomerDetail($key, $db, $cid, $pid, $custNum, $ordNum)
{
    $sql = $db->prepare("SELECT C.customerNum, C.coustomerFirstName,CH.firstName,CH.lastName,CH.emailId, C.siteName, C.downloadId,
            C.licenseKey, C.SKUNum, C.SKUDesc, C.contractEndDate, C.noOfPc, C.aviraModules, C.aviraVrnUpdt, CH.phoneNo,
            CH.emailId, CH.country FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.channel CH WHERE C.compId = ?
            AND C.processId = ? AND C.customerNum = ? AND C.orderNum = ? AND C.compId=CH.eid LIMIT 1");
    $sql->execute([$cid, $pid, $custNum, $ordNum]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    return $res;
}

function CUST_GetCustomerDetailById($key, $db, $id)
{
    $db = NanoDB::connect();
    $sql = $db->prepare("SELECT C.customerNum, C.coustomerFirstName,C.SKUNum, CH.phoneNo, CH.emailId, CH.country FROM
            " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.channel CH WHERE C.id = ? AND C.compId=CH.eid LIMIT 1");
    $sql->execute([$id]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    return $res;
}


function CUST_GetCustomerDetailByDownloadId($db, $downloadId)
{
    $sql = "SELECT C.customerNum, C.orderNum, C.sessionIni, C.coustomerFirstName, C.siteName, C.downloadId, C.licenseKey, "
        . "C.compId, C.processId, C.contractEndDate, C.aviraModules, C.aviraVrnUpdt FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE "
        . "C.downloadId = '$downloadId' LIMIT 1";
    $res = find_one($sql, $db);
    return $res;
}



function CUST_IsOrderExist($key, $db, $cid, $pid, $custNum, $ordNum)
{
    $db = NanoDB::connect();
    $sql = $db->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.compId = ? AND C.processId = ? AND C.customerNum = ? AND C.orderNum = ? LIMIT 1");
    $sql->execute([$cid, $pid, $custNum, $ordNum]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res) > 0) {
        return 'true';
    } else {
        return 'false';
    }
}


function CUST_IsCustNumberExist($key, $db, $custNum)
{
    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum = '$custNum' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return 'true';
    } else {
        return 'false';
    }
}


function CUST_IsNumberExist($key, $db, $number)
{
    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.orderNum = '$number' OR C.customerNum = '$number' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return 'true';
    } else {
        return 'false';
    }
}

function CUST_IsNumberExist_PDO($key, $db, $number)
{
    $sql = $db->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.orderNum = ? OR C.customerNum = ? LIMIT 1");
    $sql->execute([$number, $number]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res) > 0) {
        return 'true';
    } else {
        return 'false';
    }
}


function CUST_IsNameExist($key, $db, $name)
{
    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.coustomerFirstName = '$name' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return 'true';
    } else {
        return 'false';
    }
}


function CUST_IsEmailExist($key, $db, $email)
{
    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.emailId = '$email' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return 'true';
    } else {
        return 'false';
    }
}




function CUST_GetAllOrders($key, $db, $cid, $pid, $custNumber)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $db = NanoDB::connect();
        $sql = $db->prepare("SELECT id, customerNum, orderNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum = ?");
        $sql->execute([$custNumber]);
        $res = $sql->fetchAll();

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function CUST_GetOrders($key, $db, $cid, $pid, $custNumber)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $db = NanoDB::connect();
        $sql = $db->prepare("SELECT orderNum, downloadId, emailId, contractEndDate, createdDate FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum = ? group by customerNum, orderNum");
        $sql->execute([$custNumber]);
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function CUST_GetMachineDetails($key, $db, $sid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $db = NanoDB::connect();
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S WHERE S.sid = ? LIMIT 1");
        $sql->execute([$sid]);
        $res = $sql->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function CUST_IsOrderRenewable($key, $db, $custNum, $ordNum)
{
    $next45DaysDate = strtotime(' +45 days');
    $sql = "SELECT contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum = '$custNum' AND C.orderNum = '$ordNum' ORDER BY id DESC LIMIT 1";
    $res = find_one($sql, $db);
    $contractEDate = $res['contractEndDate'];
    if ($contractEDate <= $next45DaysDate) {
        return true;
    } else {
        return false;
    }
}


function CUST_GetServiceRequests($key, $db, $cid, $pid, $custNum, $ordNum)
{
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S WHERE S.compId = '$cid' AND S.processId = '$pid' AND S.customerNum = '$custNum' AND S.orderNum = '$ordNum' AND S.revokeStatus = 'I'";
    $res = find_many($sql, $db);
    return $res;
}


function CUST_GetCustomerEid($key, $db, $columnEid, $columnName, $ctype)
{
    $sql = "SELECT GROUP_CONCAT(eid) as compId FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE $columnName = '$columnEid' AND ctype = '$ctype' LIMIT 1";
    $res = find_one($sql, $db);
    return $res['compId'];
}



function CUST_AviraOrderDetailsGrid($key, $db, $compId)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "SELECT O.* FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails O, " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.compId = '$compId' AND C.nhOrderKey=O.orderNum";
        $res = find_many($sql, $db);
        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function CUST_GetOTCBasedCustomer($key, $db, $loggedEid, $customerType, $OTCRef)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        if (url::issetInRequest('searchVal')) {
            $searchVal = url::requestToAny('searchVal');
            $searchValWh = " AND (C.customerNum like '%$searchVal%' OR C.orderNum like '%$searchVal%' OR C.emailId like '%$searchVal%') ";
        } else {
            $searchValWh = "";
        }
        $sql = "SELECT C.*,count(S.sid) installedCnt FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C LEFT JOIN "
            . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum "
            . " and S.revokeStatus='I' where  C.compId in ($loggedEid) AND C.licenseKey = '$OTCRef' $searchValWh GROUP BY C.customerNum";
        $res = find_many($sql, $db);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function CUST_CreateAviraCustomer($db, $resellerId)
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
    $pccount        = UTIL_GetString('pcCnt', '0');
    $trialSite      = 0;

    $entityId     = $_SESSION["user"]["entityId"];
    $resellerDetails = RSLR_GetChannelDetails($db);
    $serverUrl = $resellerDetails['reportserver'];
    $skulist   = $resellerDetails['skulist'];

    $customerInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
     referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
     ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
     VALUES ('$entityId', '$resellerId', '0', '0', '$custCompName', '0', '0', '$custFirstName', '$custLastName', '$custEmail', '0', '$custCompAddr', '$custCompCity',
     '$custCompZipcode', '$custCompCountry', '$custCompState', '$custCompWebsite','5','','Commercial','', '', '$serverUrl', '0','Email','" . time() . "', '', '', 1)";
    $customerInsertRes = redcommand($customerInsertSql, $db);

    if ($customerInsertRes) {
        $custId = mysqli_insert_id();
        $updateCustomerNo = CUST_UpdateCustomerNo($db, $custId);
        $res_pro = CUST_GetOptionsData($db, '11', 'process_data');
        $provVal = $res_pro['value'];
        $roleItems = explode(",", $provVal);
        $proList = array();
        $insertProccess = CUST_InsertProcessMasterData($db, $roleItems, $custId, $custCompName, $custFirstName, $custLastName);
        if ($insertProccess) {
            $processId = mysqli_insert_id();
            $provision_urlStr = CUST_InsertCustomerOrderData($db, $crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $channelId, $trialSite, $otcCode, $pccount, $agentEmail, $custFirstName, $custLastName, $base_url);
        }
        return array("msg" => 'Customer created Successfully');
    } else {
        return array("msg" => 'Fail to create new customer. Please try later.', "error" =>  mysqli_error($db));
    }
}



function CUST_InsertProcessMasterData($db, $roleItems, $custId, $name, $custFirstName, $custLastName)
{
    global $base_url;
    foreach ($roleItems as $element) {
        $roleModule = explode("=", $element);
        $proList[$roleModule[0]] = $roleModule[1];
    }

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
    $sitename = preg_replace('/\s+/', '_', $name);

    $process_sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$custId',processName = '" . $name . "' ,siteCode = '" . $sitename . "',"
        . "deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',"
        . "setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',"
        . "WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='$downloaderPath',"
        . "phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',folnDarts='" . $folnDarts . "',"
        . "privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',"
        . "macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',"
        . "macProfileName='profile_mac'";
    $process_result = redcommand($process_sql, $db);

    if ($process_result) {
        $processId = mysqli_insert_id();
        $provision_urlStr = CUST_InsertCustomerOrderData($db, $custNo, $name, $name, '', '', '', $custId, $name, $resellerId, $trialSite, $otcid, $pccount, $agentEmail, $custFirstName, $custLastName, $base_url);
        $provision_urlArr = explode("--", $provision_urlStr);
        $provision_url = $provision_urlArr[0];

        if ($provision_url != "NOTDONE") {
            $downLoadUrl = $downloaderPath . '?id=' . $provision_url;
            return array("link" => $provision_url, "msg" => "Success", "clientUrl" => $downLoadUrl);
        } else {
            return array("msg" => "Fail");
        }
    }
    $_SESSION["selected"]["ctype"] = '';
    $_SESSION["selected"]["eid"] = '';
}


function CUST_InsertCustomerOrderData($db, $crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $channelId, $trialSite, $otcCode, $pccount, $agentEmail)
{
    $valLic = 5;
    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';
    $aviraLicenceInfo = '';
    $licsCnt = '';
    $licsKey = '';

    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $customerNumber = CUST_GetAutoCustNo($db);
    $customerOrder  = CUST_GetAutoOrderNo($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = RSLR_GetEntityDetail($db, $cId);
    $reportserver = $custDtl["reportserver"];

    if ($trialSite == 1 || $trialSite == '1') {
        $provCode = '01';
        $noOfDays = 30;
        $noOfPc = $pccount;
    } else if ($trialSite == 0 || $trialSite == '0') {
        $provCode = '01';
        $noOfDays = 1830;
        $noOfPc = $pccount;
    }
    $skuDesc = '';
    $skuname = '';
    $skuRef = '';

    $OTCdetails = CUST_GetOTCdetails($db, $otcCode);
    $licsKey    = $OTCdetails['licenseKey'];
    $valLic     = $pccount;
    $contractAviraDate = $OTCdetails['contractEndDate'];
    $licsKey     = $OTCdetails['licenseKey'];
    $curDate     = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime($contractAviraDate));
    $contractEnd = strtotime($contractAviraDate);

    $nhdetails = CUST_GetNHOrderDetails($db, $otcid);
    $nhLickey = $nhdetails['orderNum'];

    $process_detail = CUST_GetProcessData($key, $db, $pId);
    $sitename    = $process_detail["siteCode"];
    $variation   = $process_detail['variation'];
    $locale      = $process_detail['locale'];
    $downloadPath = $process_detail['downloaderPath'];
    $sendEmail   = $process_detail['sendMail'];
    $backUp      = $process_detail['backupCheck'];
    $respectiveDB = $process_detail['DbIp'];

    $seesionSet = CUST_SetProcessDetailSession($process_detail);
    $fileString = create_ini_parameters($sitename, $dateOfOrder, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver, '0', '', '');

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = CUST_GetCustDownloadId($db);
        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',"
            . " refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', "
            . " coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', "
            . " emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
            . " orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', "
            . " sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', "
            . " validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',"
            . " provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $agentEmail . "',"
            . " processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',advSub='1',"
            . " licenseKey = '" . $licsKey . "',nhOrderKey='" . $nhLickey . "'";
        $result = redcommand($sql_ser, $db);
    } else {
        $finStatusMsg = "NOTDONE--" . $valLic;
    }

    return $finStatusMsg;
}

function CUST_SetProcessDetailSession($process_detail)
{
    $_SESSION['lob']['phoneNumber']   = $process_detail['phoneNo'];
    $_SESSION['lob']['chatLink']      = $process_detail['chatLink'];
    $_SESSION['lob']['ReplyEmailId']  = $process_detail['replyEmailId'];
    $_SESSION['lob']['serviceLink']   = $process_detail['serviceLink'];
    $_SESSION['lob']['privacyLink']   = $process_detail['privacyLink'];
    $_SESSION['lob']['variation']     = $process_detail['variation'];
    $_SESSION['lob']['locale']        = $process_detail['locale'];
    $_SESSION['lob']['SWLangCode']    = $process_detail['SWLangCode'];
    $_SESSION['lob']['folnDarts']     = $process_detail['folnDarts'];
    $_SESSION['lob']['FtpConfUrl']    = $process_detail['FtpConfUrl'];
    $_SESSION['lob']['WsServerUrl']   = $process_detail['WsServerUrl'];
    $_SESSION['lob']['DeployPath32']  = $process_detail['setupName32'];
    $_SESSION['lob']['DeployPath64']  = $process_detail['setupName64'];
    $_SESSION['lob']['defaultProfile'] = $process_detail['DPName'];
    return TRUE;
}

function CUST_GetCustDownloadId($pdo)
{

    try {

        $downloadId = CUST_GetRandomCode();
        $sql_Coust = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE downloadId='$downloadId'";
        $stmt = $pdo->prepare($sql_Coust);
        $stmt->execute([$downloadId]);
        $res_Coust = $stmt->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res_Coust) > 0) {
            return CUST_GetCustDownloadId($pdo);
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function CUST_GetRandomCode()
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
function CUST_GetProcessId($pdo, $cid)
{
    $fld_sql = "SELECT pId,cId,processName FROM " . $GLOBALS['PREFIX'] . "agent.processMaster WHERE cId=? ORDER BY pId DESC LIMIT 1";
    $stmt = $pdo->prepare($fld_sql);
    $stmt->execute([$cid]);
    $fld_res = $stmt->fetch(PDO::FETCH_ASSOC);
    $pId     = $fld_res['pId'];
    return $pId;
}
function CUST_AddAviraSignupSites($db, $channelId, $siteName)
{

    try {
        $siteName = preg_replace('/\s+/', '_', $siteName);
        $serl_cust = "select * from " . $GLOBALS['PREFIX'] . "core.Customers where customer= '$siteName' limit 1";
        $serl_res = find_one($serl_cust, $db);

        if (safe_count($serl_res) > 0) {
        } else {

            $sqlEntity = "select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id='$channelId' and U.user_priv=0";
            $ent_res = find_many($sqlEntity, $db);
            if (safe_count($ent_res) > 0) {

                foreach ($ent_res as $entValue) {

                    $addEnSite = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)";
                    $result1 = redcommand($addEnSite, $db);
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}


function CUST_UpdateAviraLicenseCount($db, $otcDetails, $noOfPc)
{
    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $conn);

    $otcCode         = $otcDetails['otcCode'];
    $prodName         = $otcDetails['productName'];
    $contractEndDate = $otcDetails['contractEndDate'];
    $licenseKey     = $otcDetails['licenseKey'];

    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET used = used + $noOfPc,  pending = pending - $noOfPc WHERE otcCode = '$otcCode' AND productName = '$prodName' AND contractEndDate = '$contractEndDate' AND licenseKey = '$licenseKey'";
    $updateRes = redcommand($updateSql, $conn);

    if ($updateRes)
        return TRUE;
    else
        return FALSE;
}

function CUST_GetOTCdetails($db, $otcid)
{
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE (id = '$otcid' or otcCode='$otcid') LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}

function CUST_GenerateAviraLicense($otcCode, $emailid, $companyName, $status)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $chdtl = getChannelDetails();
    $selectedId = $_SESSION["user"]["cId"];
    if ($selectedId == '') {
        $selectctype = $_SESSION["selected"]["ctype"];

        $entityId = $chdtl['entityId'];
        $channelId = $chdtl['channelId'];
        $subchannelId = $chdtl['subchannelId'];
        if ($selectctype == 1) {
            $entityId = $selectedId;
        } else if ($selectctype == 2) {
            $channelId = $chdtl['eid'];
        } else if ($selectctype == 3) {
            $subchannelId = $selectedId;
        }
    } else {
        $channelId = $selectedId;
    }
    $exist_sql = "SELECT ch_id FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE otcCode = '$otcCode' LIMIT 1";
    $exist_res = find_one($exist_sql, $db);
    if (safe_count($exist_res) > 0) {
        $valLic = array("licsCnt" => 0, "licsKey" => "", "used" => 0, "status" => "DUPLICATE");
        return $valLic;
    } else {
        $url = "https://license.avira.com/service/api";
        $username = 'nanoheal';
        $password = 'QZhPR5J7tsT2zqVG';
        $data_string = '{"jsonrpc":"2.0","method":"processActivationByKey","params":{"email":"' . $emailid . '","code":"' . $otcCode . '", "language":"en","company":"' . $companyName . '"},"id":2}';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            $result1 = curl_exec($ch);
            curl_close($ch);
            $json = safe_json_decode($result1);
            $jsonArray = safe_json_decode($result1->result, TRUE);
            $msgStatus = $json->result->status;
            $avrmessage = $json->result->message;
            if ($msgStatus == 'success') {
                $licsCnt = $json->result->users;
                $licsKey = $json->result->licenseKey;
                $product_name = $json->result->product_name;
                $activated_users = $json->result->activated_users;
                $expire_date = $json->result->expire_date;
                $runtime = $json->result->runtime;
                $productId = $json->result->product_id;

                $queryString = "INSERT INTO aviraLicenses (ch_id,emailId,companyname,otcCode,productName, licenseCnt, usedLicense,contractEndDate,licenseKey,productId,runtime,status, used, pending) VALUES ('$channelId','$emailid','$companyName','$otcCode', '$product_name','$licsCnt','$activated_users','$expire_date', '" . $licsKey . "','" . $productId . "', '$runtime','$status',0,'$licsCnt')";
                $res = redcommand($queryString, $db);
                $aviraId = mysqli_insert_id();
                $dt = time();
                $contractEnd = strtotime($expire_date);
                $customerOrder = getAutoOrderNo();

                $sql_ser = "insert into orderDetails set chnl_id = '" . $channelId . "', orderNum = '" . $customerOrder . "',skuNum = '',skuDesc = '" . $product_name . "', licenseCnt = '$licsCnt', installCnt = '0' , purchaseDate = '$dt', orderDate= '$dt',contractEndDate = '" . $contractEnd . "', noofDays = '30', payRefNum = '',nh_lic='0',trial='0', transRefNum = '', amount = '0', status = '1',aviraOtc='$otcCode'";
                $result_ser = redcommand($sql_ser, $db);
                $valLic = array("licsCnt" => $licsCnt, "licsKey" => $licsKey, "aviraid" => $aviraId, "contractEnds" => $contractEnd, "used" => 0, "status" => "SUCCESS", "licOrder" => $customerOrder);
                RSLR_MakeOTCHistory($db, $otcCode, $chdtl, $jsonArray);
            } else {
                $valLic = array("licsCnt" => 0, "licsKey" => 0, "aviraid" => 0, "contractEnds" => "", "used" => 0, "status" => $avrmessage);
            }
            return $valLic;
        } catch (Exception $ex) {
            logs::log(__FILE__, __LINE__, $ex, 0);
            return "Exception : " . $ex;
        }
    }
}

function CUST_UpdateCustomerNo($db, $custId)
{
    $year = date("Y");
    $custNo = $year . '000' . $custId;

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.channel SET customerNo='$custNo',referenceNo='$custNo' WHERE eid='$custId'";
    $res = redcommand($sql, $db);
    return TRUE;
}

function CUST_GetOptionsData($db, $type, $name)
{
    $sql = "SELECT S.value FROM " . $GLOBALS['PREFIX'] . "core.Options S WHERE S.type='$type' AND S.name= '$name' LIMIT 1";
    $res = find_one($sql, $db);
    return $res;
}

function CUST_GetProcessData($key, $pdo, $pId)
{
    $sql_prod = "SELECT pId, siteCode, phoneNo, downloaderPath, chatLink, replyEmailId, serviceLink, privacyLink, variation, locale, SWLangCode, "
        . "folnDarts, FtpConfUrl, WsServerUrl, setupName32, setupName64, DPName, sendMail, backupCheck, DbIp "
        . "FROM " . $GLOBALS['PREFIX'] . "agent.processMaster P WHERE P.pId=?";
    $stmt = $pdo->prepare($sql_prod);
    $stmt->execute([$pId]);
    $res_prod = $stmt->fetch(PDO::FETCH_ASSOC);

    return $res_prod;
}

function CUST_GetNHOrderDetails($db, $otcid)
{
    $sql = "SELECT id,chnl_id,orderNum,skuNum,skuDesc FROM orderDetails WHERE aviraOtc='$otcid' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}


function CUST_CreateClient_UIDirectory($companyName)
{
    global $ClientUIDefaultLocation;
    global $ClientUIFTPLocation;
    $companyName = preg_replace("/[^a-zA-Z0-9\s]/", "", $companyName);
    $companyName = preg_replace('/\s+/', '', $companyName);
    $path = $ClientUIFTPLocation . $companyName;
    $copyDir = CUST_FullCopyDirectory($ClientUIDefaultLocation, $path);
    return $path;
}

function CUST_FullCopyDirectory($source, $destination)
{
    $i = 0;
    $oldumask = umask(0);
    mkdir($destination, 0777);
    umask($oldumask);
    $oldumask1 = '';
    $files = scandir($source);
    foreach ($files as $file) {
        $oldumask1 = umask(0);
        $i++;
        if (in_array($file, array(".", "..")))
            continue;
        if (copy($source . "/" . $file, $destination . "/" . $file)) {
            $filePer = $destination . "/" . $file;
            chmod($filePer, 0777);
        }
        umask($oldumask1);
    }
    return TRUE;
}


function CUST_AutoOrderNo_PDO($db)
{

    $ordernum = rand(1000000, 9999999999);
    $cm_query = $db->prepare("select id,customerNum,orderNum from customerOrder where orderNum=? OR oldorderNum=?");
    $cm_query->execute([$ordernum, $ordernum]);
    $res_cm = $cm_query->fetch(PDO::FETCH_ASSOC);

    $count = safe_count($res_cm);
    if ($count > 0) {
        CUST_AutoOrderNo($db);
    } else {
        return $ordernum;
    }
}

function CUST_AutoCustNo_PDO($db)
{

    $custnum = rand(1000000, 9999999999);
    $cm_query = $db->prepare("select id,customerNum,orderNum from customerOrder where customerNum=?");
    $cm_query->execute([$custnum]);
    $res_cm = $cm_query->fetch(PDO::FETCH_ASSOC);

    $count = safe_count($res_cm);
    if ($count > 0) {
        CUST_AutoCustNo($db);
    } else {
        return $custnum;
    }
}

function CUST_GetProcessDetails_PDO($key, $db, $pId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql_prod = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
        $sql_prod->execute([$pId]);
        $res_prod = $sql_prod->fetch();

        if (safe_count($res_prod) > 0) {
            return $res_prod;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function create_ini_parameters_PDO($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportId, $trial, $degrdSku, $renew)
{
    global $db;
    global $base_url;

    $db = pdo_connect();
    global $base_url;
    $INI_FILEPATH = 'customer/ini/';
    $INI_FILENAME = 'NanoHeal';

    $sql_reg = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "install.Servers where serverid= ?  limit 1 ");
    $sql_reg->execute([$reportId]);
    $res_reg_all = $sql_reg->fetch();

    $res_reg = $res_reg_all[0];

    if (safe_count($res_reg_all) > 0) {
        $url1 = explode("https://", $res_reg["url"]);
        if ($res_reg['advanced'] == 1) {
            $asseturl = explode("https://", $res_reg["asseturl"]);
            $configurl = explode("https://", $res_reg["configurl"]);
        } elseif ($res_reg['advanced'] == 0) {
            $asseturl = explode("https://", $res_reg["url"]);
            $configurl = explode("https://", $res_reg["url"]);
        }
        $url2 = explode("http://", $res_reg["url"]);
        if (safe_count($url1) > 1) {
            $url_domain = explode(":", $url1[1]);
            $url_asset = explode(":", $asseturl[1]);
            $url_config = explode(":", $configurl[1]);
            $logserver = $url_domain[0];
            $configserver = $url_config[0];
            $assetserver = $url_asset[0];
            $updateserver = '';
            $installationserver = '';
            $mumserver = '';
            $proserver = '';
            $custdomain = $url_domain[0];
        } else {
            $url_log = explode(":", $url2[1]);
            $url_asset = explode(":", $asseturl[1]);
            $url_config = explode(":", $configurl[1]);
            $logserver = $url_log[0];
            $configserver = $url_config[0];
            $assetserver = $url_asset[0];
            $updateserver = '';
            $installationserver = '';
            $mumserver = '';
            $proserver = '';
            $custdomain = $url_log[0];
        }

        $username = $res_reg["username"];
        $password = $res_reg["password"];
        $followonid = '';
        $delay = '';

        $file = $base_url . $INI_FILEPATH . $INI_FILENAME . '.ini';
        $fileString = file_get_contents($file);
        $serviceNum = 0;
        $fileString = str_replace('LogServer=', 'LogServer=' . $logserver, $fileString);
        $fileString = str_replace('AssetServer=', 'AssetServer=' . $assetserver, $fileString);
        $fileString = str_replace('ConfigServer=', 'ConfigServer=' . $configserver, $fileString);
        $fileString = str_replace('CustName=', 'CustName=' . $sitename, $fileString);
        $fileString = str_replace('CustDomain=', 'CustDomain=' . $custdomain, $fileString);
        $fileString = str_replace('ClientRealm=', 'ClientRealm=' . $sitename, $fileString);
        $fileString = str_replace('ClientUser=', 'ClientUser=' . trim($username), $fileString);
        $fileString = str_replace('ConfigPassword=', 'ConfigPassword=' . trim($password), $fileString);
        $fileString = str_replace('HPCPassword=', 'HPCPassword=' . trim($password), $fileString);
        $fileString = str_replace('CustomerNo=', 'CustomerNo=' . trim($customerNumber), $fileString);
        $fileString = str_replace('OrderNo=', 'OrderNo=' . trim($customerOrder), $fileString);
        $fileString = str_replace('CustomerEmail=', 'CustomerEmail=' . trim($customerEmailId), $fileString);
        $fileString = str_replace('UniDays=', 'UniDays=' . trim($contractEDate), $fileString);
        $fileString = str_replace('SWLangCode=', 'SWLangCode=' . trim($_SESSION['lob']['SWLangCode']), $fileString);
        $fileString = str_replace('HFNProvCode=', 'HFNProvCode=' . trim($provCode), $fileString);
        $fileString = str_replace('DisplayPhNo=', 'DisplayPhNo=' . trim($_SESSION['lob']['phoneNumber']), $fileString);
        $fileString = str_replace('ChatLink=', 'ChatLink=' . urlencode(trim($_SESSION['lob']['chatLink'])), $fileString);
        $fileString = str_replace('PrivacyStmntLink=', 'PrivacyStmntLink=' . trim($_SESSION['lob']['privacyLink']), $fileString);
        $fileString = str_replace('termsAndCondition=', 'termsAndCondition=' . trim($_SESSION['lob']['termsConditionLink']), $fileString);
        $fileString = str_replace('FollowOnDarts=', 'FollowOnDarts=' . $_SESSION['lob']['folnDarts'], $fileString);
        $fileString = str_replace('StartupProfName=', 'StartupProfName=' . trim($_SESSION['lob']['defaultProfile']), $fileString);
        $fileString = str_replace('Variation=', 'Variation=' . trim($_SESSION['lob']['variation']), $fileString);
        $fileString = str_replace('FtpConfUrl=', 'FtpConfUrl=' . trim($_SESSION['lob']['FtpConfUrl']), $fileString);
        $fileString = str_replace('WsServerUrl=', 'WsServerUrl=' . trim($_SESSION['lob']['WsServerUrl']), $fileString);
        $fileString = str_replace('showUpgradeClient=', 'showUpgradeClient=' . trim($_SESSION['lob']['showUpgradeClient']), $fileString);
        $fileString = str_replace('degrdSku=', 'degrdSku=' . trim($degrdSku), $fileString);
        $fileString = str_replace('trial=', 'trial=' . trim($trial), $fileString);
        $fileString = str_replace('renewDays=', 'renewDays=' . trim($renew), $fileString);
        $fileString = str_replace('DeployPath32=', 'DeployPath32=' . trim($_SESSION['lob']['DeployPath32']), $fileString);
        $fileString = str_replace('DeployPath64=', 'DeployPath64=' . trim($_SESSION['lob']['DeployPath64']), $fileString);
        $fileString = str_replace('FCMURL=', 'FCMURL=' . trim($base_url . '/communication/MobileRegister.php'), $fileString);
        return $fileString;
    } else {
        return 'FAIL';
    }
}

function CUST_RenewProvision_PDO($customNum, $oldOrderNum, $orderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $siteName, $agentVal, $pcCnt, $orderDate, $uniDate, $liceOrderNum)
{

    global $base_url;
    global $download_ClientUrl;
    $remoteSession = '';
    $finStatusMsg = '';
    $sDays = 0;

    $db = pdo_connect();

    $agentEmail = $_SESSION["user"]["adminEmail"];
    $pId = getProcessByCompany($cId);

    $refCustomerNum = CUST_AutoCustNo_PDO($db);
    $refOrderNum  = CUST_AutoCustNo_PDO($db);

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $custDtl = RSLR_GetEntityDtls_PDO('', $db, $cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = RSLR_SKUDetails_PDO('', $db, $SKU);
    $provCode = $skuDetls['provCode'];
    $noOfDays = $skuDetls['licensePeriod'];
    $dateOfOrder = $orderDate;
    $contractEnd = $uniDate;
    $contractEDate = Date("m/d/Y", $contractEnd);
    $contractMailEDate = Date("F d,Y", $contractEnd);
    $contractMailSDate = Date("F d,Y", $orderDate);

    $curDt = time();

    $res_prod = CUST_GetProcessDetails_PDO('', $db, $pId);

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
    $_SESSION['lob']['termsConditionLink'] = $res_prod['termsConditionUrl'];
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];
    $_SESSION['lob']['showUpgradeClient'] = $res_prod['showUpgradeClient'];

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters_PDO($siteName, $orderDate, $customNum, $orderNum, $customerEmailId, $contractEDate, $provCode, $reportserver, $trial, $degrade, $renew);

    if ($fileString != 'FAIL' && $fileString != '') {

        $downloadId = USER_DownloadId('', $db);

        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customNum . "', orderNum = '" . $orderNum . "',\n"
            . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', \n"
            . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '', \n"
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '', SKUDesc = '', \n"
            . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '', \n"
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', \n"
            . "noOfPc = '" . trim($pcCnt) . "',oldorderNum ='" . $oldOrderNum . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',\n"
            . "agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $customerFirstName . "',nhOrderKey='" . $liceOrderNum . "',createdDate='" . $curDt . "'";
        $result = redcommand($sql_ser, $db);

        $sql_ser = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = ?, orderNum = ?,
                refCustomerNum = ?,refOrderNum = ?, coustomerFirstName =?,
                coustomerLastName = ? , coustomerCountry = '',
                emailId= ?,SKUNum = '', SKUDesc = '',
                orderDate = ?, contractEndDate = ?, backupCapacity = '',
                sessionid =?, sessionIni =?, validity = ?,
                noOfPc =?,oldorderNum =?,provCode = ?,remoteSessionURL = ?,
                agentId = ?,processId= ?,compId=?,downloadId=?,siteName=?,nhOrderKey=?,createdDate=?");
        $sql_ser->execute([
            $customNum, $orderNum, $refCustomerNum, $refOrderNum, $customerFirstName, $customerLastName, $customerEmailId,
            $dateOfOrder, $contractEnd, $sessionid, $fileString, $noOfDays, $pcCnt, $agentEmail, $oldOrderNum,
            $provCode, $remoteSession, $pId, $cId, $downloadId, $customerFirstName, $liceOrderNum, $curDt
        ]);
        $result = $sql_ser->rowCount();

        if ($result > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }

    return $finStatusMsg;
}
