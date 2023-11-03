<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-crmdetls.php';
include_once '../include/common_functions.php';

global $pdo;
$pdo = pdo_connect();



function addOrderDeatils()
{
    global $pdo;
    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function createCustomer()
{
    global $pdo;
    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function createReseller()
{
    global $pdo;
    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}


function getRenewDevices()
{

    global $pdo;
    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}



function creteSite()
{

    global $pdo;
    global $base_url;
    $logoPath = $base_url;
    $logoIconPath = $base_url;

    $entityId = $_SESSION["user"]["entityId"];
    $channelId = $_SESSION["user"]["channelId"];
    $subchannelId = $_SESSION["user"]["subchannelId"];
    $outsourcedId = $_SESSION["user"]["outsourcedId"];

    $selectedId = isset($_SESSION["selected"]["eid"]) ? $_SESSION["selected"]["eid"] : '';

    $chdtl = getChannelDetails();
    $serverUrl = $chdtl['reportserver'];
    $skulist = $chdtl['skulist'];
    if ($selectedId != '') {
        $selectctype = $_SESSION["selected"]["ctype"];
        $entityId = $chdtl['entityId'];
        $channelId = $chdtl['channelId'];
        $subchannelId = $chdtl['subchannelId'];
        $outsourcedId = $chdtl['outsourcedId'];

        if ($selectctype == 1) {
            $entityId = $selectedId;
        } else if ($selectctype == 2) {
            $channelId = $selectedId;
        } else if ($selectctype == 3) {
            $subchannelId = $selectedId;
        }
    }

    $name = url::issetInRequest('sitename') ? url::requestToAny('sitename') : '';
    $trialSite = url::issetInRequest('trialSite') ? url::requestToAny('trialSite') : '';
    $pccount = url::issetInRequest('pcCnt') ? url::requestToAny('pcCnt') : 0;
    $otcid = url::issetInRequest('aviraOtc') ? url::requestToAny('aviraOtc') : '';
    $avira_Inst = isset($_SESSION["user"]["Avira_Inst"]) ? $_SESSION["user"]["Avira_Inst"] : 0;

    $new_otc = url::issetInRequest('new_otc') ? url::requestToAny('new_otc') : '';
    $new_email = url::issetInRequest('new_email') ? url::requestToAny('new_email') : '';
    $new_compName = url::issetInRequest('new_compName') ? url::requestToAny('new_compName') : '';
    $firstName = url::issetInRequest('firstName') ? url::requestToAny('firstName') : '';
    $lastName = url::issetInRequest('lastName') ? url::requestToAny('lastName') : '';
    $status = url::issetInRequest('status') ? url::requestToAny('status') : '1';
    $language = url::issetInRequest('language') ? url::requestToAny('language') : 'en';
    $gatewayStatus = url::issetInRequest('defaultGateway') ? url::requestToAny('defaultGateway') : '0';

    if ($gatewayStatus == '1' || $gatewayStatus == 1) {
        $gateWayArray['GatewayMachine'] = url::requestToAny('GatewayMachine');
        $gateWayArray['GatewayHost'] = url::requestToAny('GatewayHost');
        $gateWayArray['GatewayIP'] = url::requestToAny('GatewayIP');
        $gateWayArray['GatewayPort'] = url::requestToAny('GatewayPort');
        $gateWayArray['GatewayDomain'] = url::requestToAny('GatewayDomain');
        $gateWayArray['GatewayUN'] = url::requestToAny('GatewayUN');
        $gateWayArray['GatewayPassword'] = url::requestToAny('GatewayPassword');
        $gateWayArray['GatewayMachine'] = url::requestToAny('GatewayMachine');
    }
    $aviraStatus = '';
    $chnl_email = '';
    if ($firstName == '') {
        $firstName = $name;
    }
    if ($status === 1 || $status === '1') {
        $chnl_email = $firstName;
    } else {
        $chnl_email = $new_email;
    }

    if ($status == '0' || $status == 0) {
        $emailExistSql = $pdo->prepare("select  eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE emailId= ? LIMIT 1");
        $emailExistSql->execute([$new_email]);
        $emailExistRes = $emailExistSql->fetch();
        $emailUserSql = $pdo->prepare("select  userid from " . $GLOBALS['PREFIX'] . "core.Users  where user_email=? LIMIT 1");
        $emailUserSql->execute([$new_email]);
        $emailUserRes = $emailUserSql->fetch();
        if (safe_count($emailExistRes) > 0) {
            return array("msg" => 'Given email address already in use.');
        } else if (safe_count($emailUserRes) > 0) {
            return array("msg" => 'Given email address already in use.');
        }
    }

    $restrict = 0;
    if ($status == '0' || $status == 0) {
        $restrict = 1;
    }

    $cmpStatus = array("restrict" => $restrict, "trial" => $trialSite, "addSubscription" => 0);
    $sql_ch = $pdo->prepare("select  * from " . $GLOBALS['PREFIX'] . "agent.channel where companyName =?");
    $sql_ch->execute([$name]);
    $res_core = $sql_ch->fetch();
    if (safe_count($res_core) > 0) {
        return array("msg" => 'Site name already exist.');
    } else {

        if ($otcid == 'new' && $avira_Inst == 1) {
            $aviraRet = generateAviraLicense($new_otc, $new_email, $new_compName, 0, $restrict);
            $aviraStatus = $aviraRet['status'];
            $errorMessage = $aviraRet['message'];
            if ($aviraStatus == 'ERROR') {
                return array("msg" => $errorMessage);
            } elseif ($aviraStatus == 'DUPLICATE') {
                return array("msg" => 'Entered OTC code alredy used.');
            }
        }

        $autoinc_sql = $pdo->prepare("select  AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'agent' AND   TABLE_NAME   = 'channel'");
        $autoinc_sql->execute();
        $autoinc_res = $autoinc_sql->fetch();
        $incrementId = $autoinc_res['AUTO_INCREMENT'];
        $resName = $name . '_' . $incrementId;

        $channelInsertSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
         referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
         ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo,restricted, status)
         VALUES ('$entityId', '$channelId', '$subchannelId', '$outsourcedId', '$resName', '', '', '$firstName', '$lastName', '$chnl_email', '', '', '',
         '', '', '', '','5','','Commercial','', '', '$serverUrl', '0','Email','" . time() . "', '', '','$restrict', 1)");
        $channelInsertSql->execute();
        $cust_result = $pdo->lastInsertId();
        if ($cust_result) {

            $custId = mysqli_insert_id();
            $year = date("Y");
            $custNo = $year . '000' . $custId;


            $updateCust = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.channel set customerNo=?,referenceNo=? where eid=?");
            $updateCust->execute([$custNo, $custNo, $custId]);
            $cust_update = $pdo->lastInsertId();

            $pro_sql = $pdo->prepare("select  S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pro_sql->execute();
            $res_pro = $pro_sql->fetch();
            $provVal = $res_pro['value'];
            $roleItems = explode(",", $provVal);
            $proList = array();
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
            $dUrl = $base_url . 'eula.php';
            $sitename = getFilteredSiteName($name, $custNo);
            $process_sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$custId',processName = '" . $resName . "' ,siteCode = '" . $sitename . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='$dUrl',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',folnDarts='" . $folnDarts . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac'");
            $process_sql->execute();
            $process_result = $pdo->lastInsertId();
            if ($process_result) {
                $processId = mysqli_insert_id();
                if ($otcid == 'new' && $avira_Inst == 1 && $status == 0) {
                    $otcid = $aviraRet['aviraid'];
                    $pccount = $aviraRet['licsCnt'];
                }
                $provision_urlStr = custSiteProvUrl($custNo, $custNo, $firstName, $lastName, $new_email, '', $custId, $resName, $channelId, $cmpStatus, $new_otc, $pccount, $gatewayStatus, $gateWayArray);
                $provision_urlArr = explode("--", $provision_urlStr);
                $provision_url = $provision_urlArr[0];
                $pcno = $provision_urlArr[1];

                if ($provision_url != "NOTDONE") {

                    $downLoadUrl = $base_url . 'eula.php?id=' . $provision_url;
                    if ($restrict == 1 || $restrict == '1') {
                        addCust_URLEmail($firstName, $new_email, $downLoadUrl, $language);
                    }

                    $path = createClient_UIDirectory($name);
                    update_UIDirectoryPath($path, $path);
                    RSLR_AviraCRMlogin($firstName, $lastName, $new_email, $name, $custId, 5, $channelId);
                    return array("link" => $provision_url, "msg" => "Success", "clientUrl" => $downLoadUrl);
                } else {
                    return array("msg" => "Fail");
                }
            }
            $_SESSION["selected"]["ctype"] = '';
            $_SESSION["selected"]["eid"] = '';
            return array("msg" => 'Customer created Successfully');
        } else {
            return array("msg" => 'Fail to create new customer. Please try later.');
        }
    }
}


function addAviraSubscription()
{

    global $pdo;
    global $base_url;
    $logoPath = $base_url;
    $logoIconPath = $base_url;

    $entityId = $_SESSION["user"]["entityId"];
    $channelId = $_SESSION["user"]["channelId"];
    $subchannelId = $_SESSION["user"]["subchannelId"];
    $outsourcedId = $_SESSION["user"]["outsourcedId"];

    $pdo = pdo_connect();

    $selectedId = isset($_SESSION["selected"]["eid"]) ? $_SESSION["selected"]["eid"] : '';

    $chdtl = getChannelDetails();
    $serverUrl = $chdtl['reportserver'];
    $skulist = $chdtl['skulist'];
    if ($selectedId != '') {
        $selectctype = $_SESSION["selected"]["ctype"];
        $entityId = $chdtl['entityId'];
        $channelId = $chdtl['channelId'];
        $subchannelId = $chdtl['subchannelId'];
        $outsourcedId = $chdtl['outsourcedId'];

        if ($selectctype == 1) {
            $entityId = $selectedId;
        } else if ($selectctype == 2) {
            $channelId = $selectedId;
        } else if ($selectctype == 3) {
            $subchannelId = $selectedId;
        }
    }

    $name = url::issetInRequest('sitename') ? url::requestToAny('sitename') : '';
    $trialSite = url::issetInRequest('trialSite') ? url::requestToAny('trialSite') : '';
    $pccount = url::issetInRequest('pcCnt') ? url::requestToAny('pcCnt') : 0;
    $otcid = url::issetInRequest('aviraOtc') ? url::requestToAny('aviraOtc') : '';
    $avira_Inst = isset($_SESSION["user"]["Avira_Inst"]) ? $_SESSION["user"]["Avira_Inst"] : 0;

    $new_otc = url::issetInRequest('new_otc') ? url::requestToAny('new_otc') : '';
    $new_email = url::issetInRequest('new_email') ? url::requestToAny('new_email') : '';
    $new_compName = url::issetInRequest('new_compName') ? url::requestToAny('new_compName') : '';
    $firstName = url::issetInRequest('firstName') ? url::requestToAny('firstName') : '';
    $lastName = url::issetInRequest('lastName') ? url::requestToAny('lastName') : '';
    $status = url::issetInRequest('status') ? url::requestToAny('status') : '1';
    $custno = url::issetInRequest('custno') ? url::requestToAny('custno') : '';
    $orderno = url::issetInRequest('orderno') ? url::requestToAny('orderno') : '1';
    $custId = url::issetInRequest('cust_id') ? url::requestToAny('cust_id') : '';
    $proc_id = url::issetInRequest('proc_id') ? url::requestToAny('proc_id') : '';
    $language = url::issetInRequest('language') ? url::requestToAny('language') : 'en';


    $_SESSION["selected"]["custno"] = $custno;

    if ($firstName == '') {
        $firstName = $name;
    }
    if ($status === 1 || $status === '1') {
        $chnl_email = $firstName;
    } else {
        $chnl_email = $new_email;
    }

    if ($otcid == 'new' && $avira_Inst == 1) {
        $aviraRet = generateAviraLicense($new_otc, $new_email, $new_compName, 0, 0);
        $aviraStatus = $aviraRet['status'];
        $errorMessage = $aviraRet['message'];
        if ($aviraStatus == 'ERROR') {
            return array("msg" => $errorMessage);
        } elseif ($aviraStatus == 'DUPLICATE') {
            return array("msg" => 'Entered OTC code alredy used.');
        }
    }
    $restrict = 0;
    if ($status == '0' || $status == 0) {
        $restrict = 1;
    }

    $cmpStatus = array("restrict" => $restrict, "trial" => $trialSite, "addSubscription" => 1);
    $year = date("Y");
    $custNo = $year . '000' . $custId;
    $custOrder = getAutoOrderNo();
    if ($otcid == 'new' && $avira_Inst == 1 && $status == 0) {
        $otcid = $aviraRet['aviraid'];
        $pccount = $aviraRet['licsCnt'];
    }
    $provision_urlStr = custSiteProvUrl($custNo, $custOrder, $firstName, $lastName, $new_email, '', $custId, $name, $channelId, $cmpStatus, $new_otc, $pccount, 0, array());
    $provision_urlArr = explode("--", $provision_urlStr);
    $provision_url = $provision_urlArr[0];
    $pcno = $provision_urlArr[1];

    if ($provision_url != "NOTDONE") {

        $downLoadUrl = $base_url . 'eula.php?id=' . $provision_url;
        if ($restrict == 1 || $restrict == '1') {
            addCust_URLEmail($firstName, $new_email, $downLoadUrl, $language);
        }

        return array("link" => $provision_url, "msg" => "Success", "clientUrl" => $downLoadUrl);
    } else {
        return array("msg" => "Fail");
    }
}


function custSiteProvUrl($crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $channelId, $cmpStatus, $otcid, $pccount, $gateway, $gateWayArray)
{

    global $base_url;
    $pdo = pdo_connect();
    $valLic = 5;
    $agentEmail = $_SESSION["user"]["loguser"];
    $otcCode = url::requestToAny('aviraOtc');
    $avira_Inst = $_SESSION["user"]["Avira_Inst"];
    $agentLogEmail = $_SESSION['user']['adminEmail'];
    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';
    $aviraLicenceInfo = '';
    $licsCnt = '';
    $licsKey = '';
    $currentDate = time();
    $remoteSession = '';
    $provCode = '01';
    $finStatusMsg = '';
    $gatewayStatus = 0;
    $gateWayString = '';

    $oldCustOrd = '';

    if (isset($_SESSION["selected"]["custno"])) {
        $customerNumber = $_SESSION["selected"]["custno"];
        unset($_SESSION["selected"]["custno"]);
    } else {
        $customerNumber = getAutoCustNo();
    }

    $customerOrder = getAutoOrderNo();

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = get_Entity_Dtl($cId);
    $reportserver = $custDtl["reportserver"];
    $trialSite = $cmpStatus['trial'];
    $restrict = $cmpStatus['restrict'];


    if ($restrict == 1 || $restrict == '1') {
        $nh_mspSql = $pdo->prepare("select  eid from " . $GLOBALS['PREFIX'] . "agent.channel where companyName = 'NH_MSP' LIMIT 1");
        $nh_mspSql->execute();
        $nh_mspRes = $nh_mspSql->fetch();
        $eid = $nh_mspRes['eid'];
        $userKey = getDownloadId1();
        $entityId = $_SESSION['user']['entityId'];
        $channelId = $_SESSION['user']['cId'];
        $roleSql = $pdo->prepare("select  assignedRole FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE statusVal=0 LIMIT 1");
        $roleSql->execute();
        $roleRes = $roleSql->fetch();
        $roleId = $roleRes['assignedRole'];

        if ($cmpStatus['addSubscription'] == 0) {
            $createUser = createCustomerUser($cId, $entityId, $channelId, $roleId, $customerFirstName, $customerFirstName, $customerLastName, $customerEmailId, $userKey);
            $createMail = addCust_SendEmail($customerFirstName, $customerEmailId, $userKey, '10', $channelId);
        }
    }
    if ($trialSite == 1 || $trialSite == '1') {
        $noOfDays = 30;
        $noOfPc = $pccount;
    } else if ($trialSite == 0 || $trialSite == '0') {
        $noOfDays = 367;
        $noOfPc = $pccount;
    }

    $skuDesc = '';
    $skuname = '';
    $skuRef = '';
    if ($avira_Inst == 1) {

        $OTCdetails = getOTCdetails($otcid);
        $licsKey = $OTCdetails['licenseKey'];
        $licsOtc = $OTCdetails['otcCode'];
        $valLic = $pccount;
        $nhdetails = getNHdetails($licsOtc);
        $contractAviraDate = $OTCdetails['contractEndDate'];
        $curDate = date("Y-m-d H:i:s");
        $dateOfOrder = strtotime($curDate);
        $contractEDate = Date("m/d/Y", strtotime($contractAviraDate));
        $contractEnd = strtotime($contractAviraDate);
        $updateRes = add_updateAviraLicenseCount($OTCdetails, $pccount);
        $nhLickey = $nhdetails['orderNum'];

        $now = time();
        $avira_date = strtotime($contractAviraDate);
        $datediff = $avira_date - $now;

        $noOfDays = floor($datediff / (60 * 60 * 24));
    } else {
        $curDate = date("Y-m-d H:i:s");
        $dateOfOrder = strtotime($curDate);
        $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
        $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");
    }

    $sql_prod = $pdo->prepare("select  * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $sql_prod->execute([$pId]);
    $res_prod = $sql_prod->fetch();


    $sitename = $res_prod["siteCode"];

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
    $_SESSION['lob']['defaultProfile'] = $res_prod['DPName'];

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    if ($avira_Inst == 1) {
        $OTCdetails = getOTCdetails($otcid);
        $licsKey = $OTCdetails['licenseKey'];
        $valLic = $pccount;
    } else if (($trialSite == 1 || $trialSite == '1') && ($avira_Inst == 0)) {
        createTrialOrder($channelId, $pccount);
    }

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver, '0', '', '');
    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = getCustDownloadId();
        $sessionid = md5(mt_rand());
        if ($avira_Inst == 1) {
            if ($gateway == '1' || $gateway == 1) {
                $squidInfo = getSquidConfigDetails($pccount);
                $gatewayStatus = 1;
                $gateWayString .= 'GatewayIP=' . trim($gateWayArray['GatewayIP']) . PHP_EOL;
                $gateWayString .= 'GatewayPort=' . trim($gateWayArray['GatewayPort']) . PHP_EOL;
                $gateWayString .= 'GatewayDomain=' . trim($gateWayArray['GatewayDomain']) . PHP_EOL;
                $gateWayString .= 'GatewayUN=' . trim($gateWayArray['GatewayUN']) . PHP_EOL;
                $gateWayString .= 'GatewayPassword=' . trim($gateWayArray['GatewayPassword']) . PHP_EOL;
                $htpassword = crypt_apr1_md5($gateWayArray['GatewayPassword']);
                $gateWayString .= 'GatewayHTPassword=' . trim($htpassword) . PHP_EOL;
                $gateWayString .= 'GatewayStatus=0' . PHP_EOL;
                $gateWayString .= 'GatewayEnabled=1' . PHP_EOL;
                $gateWayString .= 'SquidConfURL=' . trim($squidInfo['configUrl']) . PHP_EOL;
                $gateWayString .= 'Squid32bitURL=' . trim($squidInfo['32bitDownloadeUrl']) . PHP_EOL;
                $gateWayString .= 'Squid64bitURL=' . trim($squidInfo['64bitDownloadeUrl']) . PHP_EOL;
                $fileString .= $gateWayString;
                $serviceTag = $gateWayArray['GatewayMachine'];
            } else {
                $gateWayString .= 'GatewayEnabled=0' . PHP_EOL;
                $fileString .= $gateWayString;
            }
        }
        $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $agentLogEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',createdDate='" . time() . "',siteName='" . $sitename . "',advSub='1',licenseKey = '" . $licsKey . "',nhOrderKey='" . $nhLickey . "',gateWayStatus='" . $gatewayStatus . "'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        $dUrl = $downloadId;
        if ($result > 0) {
            if ($gateway == '1' || $gateway == 1) {

                insert_GatewayMachine($cId, $pId, $customerNumber, $customerOrder, $serviceTag, $fileString, $sitename, $contractEnd);
            }

            pushorg($downloadId, $sessionid);
            addSignupSites($channelId, $sitename, $restrict);
            if ($restrict == 1) {
                addCust_SignupSites($customerFirstName, $sitename);
            }
            $finStatusMsg = $dUrl . '--' . $valLic;
        } else {
            $finStatusMsg = "NOTDONE--" . $valLic;
        }
    } else {
        $finStatusMsg = "NOTDONE--" . $valLic;
    }

    return $finStatusMsg;
}


function insert_GatewayMachine($compId, $processId, $customerNum, $orderNum, $serviceTag, $cust_ini, $siteName, $uninstallDate)
{
    global $pdo;
    $installDate = time();
    $sessionid = md5(mt_rand());
    $rSessionUrl = '';


    $cust_ini = str_replace('GatewayStatus=0', 'GatewayStatus=1', $cust_ini);

    $appendString = "AviraInstallerURL=1#https://install.avira-update.com/package/antivirus/win/en-us/avira_antivirus_en-us.exe,7#https://install.avira-update.com/package/antivirus/win/de-de/avira_antivirus_de-de.exe
AviraConnectURL=1#https://ulqa.avira.com/package/oeavira/win/int/avira_en.exe,7#https://package.avira.com/package/oeavira/win/int/avira_de.exe
RemoteSessionURL=" . $rSessionUrl;
    $ftpString1 = "\nAviraInstallerURL=1#https://install.avira-update.com/package/antivirus/win/en-us/avira_antivirus_en-us.exe,7#https://install.avira-update.com/package/antivirus/win/de-de/avira_antivirus_de-de.exe
AviraConnectURL=1#https://ulqa.avira.com/package/oeavira/win/int/avira_en.exe,7#https://package.avira.com/package/oeavira/win/int/avira_de.exe
RemoteSessionURL=" . $rSessionUrl;

    $string1 = substr($cust_ini, -1);
    $val = hasNewLine($string1);
    if ($val == 1) {
        $fcust_ini = $cust_ini . $appendString;
    } else {
        $fcust_ini = $cust_ini . $ftpString1;
    }

    $insertSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest (customerNum, orderNum, sessionid, serviceTag,
                    installationDate, uninstallDate, iniValues, agentPhoneId, createdTime, backupCapacity,
                    downloadStatus, oldServiceTag, revokeStatus, machineManufacture, machineModelNum, pcNo,
                    machineName, machineOS, clientVersion, oldVersion, assetStatus, uninsdormatStatus,
                    uninsdormatDate, downloadId, macAddress, processId, compId, siteName, subscriptionKey,
                     licenseKey, orderStatus, gatewayMachine) VALUES ($customerNum, $orderNum, '$sessionid', '$serviceTag',
                     '$installDate', '$uninstallDate', '$fcust_ini', '', '', 5, 'D', NULL, 'I', '', '', 1,
                     NULL, '', NULL, NULL, 0, NULL, 0, NULL, '', '$processId', '$compId', '$siteName', NULL, NULL, 'Active', '1')");
    $insertSql->execute();
    $result = $pdo->lastInsertId();
    return $result;
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

function getCustDownloadId()
{

    try {

        $pdo = pdo_connect();

        $downloadId = getcustno1();

        $sql_Coust = $pdo->prepare("select  id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where downloadId=?");
        $sql_Coust->execute([$downloadId]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return getCustDownloadId();
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function validateOTC($otc)
{

    try {

        $pdo = pdo_connect();

        $sql_Coust = $pdo->prepare("select  id,ch_id,otcCode from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where otcCode=? limit 1");
        $sql_Coust->execute([$otc]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return 1;
        }
        return 0;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function createTrialOrder($chid, $pcno)
{

    try {

        $pdo = pdo_connect();
        $customerOrder = getAutoOrderNo();
        $skuNum = '';
        $skuDesc = 'Tria sku';
        $dt = time();

        $noOfDays = 30;
        $curDate = date("Y-m-d H:i:s");
        $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");
        $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.orderDetails set chnl_id = '" . $chid . "', orderNum = '" . $customerOrder . "',skuNum = '$skuNum',skuDesc = '" . $skuDesc . "', licenseCnt = '$pcno', installCnt = '0' , purchaseDate = '$dt', orderDate= '$dt',contractEndDate = '" . $contractEnd . "', noofDays = '30', payRefNum = '', transRefNum = '', amount = '0', status = '1'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function getcustno1()
{


    try {

        $character_set_array = array();
        $character_set_array[] = array('count' => 40, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@$%&()__0123456789');
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

function pushorg($custNo, $sessionid)
{
    global $db_user;
    global $base_url;
    global $pdo;
    $dbIp = '104.154.53.224:4791';
    try {
        if ($dbIp != '') {
            $conn = mysqli_connect($dbIp, $db_user, 'R@6C0Ez27XydE1ycKtQ3nv22Bz99f47uBCjt#');
            if (!$conn) {
                return 0;
            } else {
                $url = rtrim($base_url, '/');
                $queryString = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.orderDetails (customerNo, sessionId, reportUrl) VALUES ('$custNo', '$sessionid', '$url')");
                $queryString->execute();
                $result = $pdo->lastInsertId();
                if ($result > 0) {
                    return 1;
                } else {
                    return 0;
                }
            }

            mysqli_close($conn);
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}


function getInstallDetails()
{

    try {

        $pdo = pdo_connect();

        $selectedId = isset($_SESSION["selected"]["eid"]) ? $_SESSION["selected"]["eid"] : '';

        $chdtl = getChannelDetails();
        if ($selectedId != '') {

            $selectctype = $_SESSION["selected"]["ctype"];
            $entityId = $chdtl['entityId'];
            $channelId = $chdtl['channelId'];
            $subchannelId = $chdtl['subchannelId'];

            if ($selectctype == 1) {
                $entityId = $selectedId;
            } else if ($selectctype == 2) {
                $channelId = $selectedId;
            } else if ($selectctype == 3) {
                $subchannelId = $selectedId;
            }
        }

        $sql_Coust = $pdo->prepare("select  sum(licenseCnt) lseCnt,sum(installCnt) insCnt from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id=?");
        $sql_Coust->execute([$channelId]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            $totalCnt = $res_Coust['lseCnt'];
            $installCnt = $res_Coust['insCnt'];
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getOrderDetails()
{

    try {

        $pdo = pdo_connect();

        $selectedId = isset($_SESSION["selected"]["eid"]) ? $_SESSION["selected"]["eid"] : '';

        $chdtl = getChannelDetails();
        if ($selectedId != '') {

            $selectctype = $_SESSION["selected"]["ctype"];
            $entityId = $chdtl['entityId'];
            $channelId = $chdtl['channelId'];
            $subchannelId = $chdtl['subchannelId'];

            if ($selectctype == 1) {
                $entityId = $selectedId;
            } else if ($selectctype == 2) {
                $channelId = $selectedId;
            } else if ($selectctype == 3) {
                $subchannelId = $selectedId;
            }
        }

        $sql_Coust = $pdo->prepare("select  * from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id=? limit 1");
        $sql_Coust->execute([$channelId]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            $totalCnt = $res_Coust['lseCnt'];
            $installCnt = $res_Coust['insCnt'];
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getResellerSite()
{

    try {

        $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
        $pdo = pdo_connect();

        $sql_Coust = $pdo->prepare("select  C.username,C.customer,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U,core.Customers C where U.ch_id=? and U.username=C.username limit 1");
        $sql_Coust->execute([$cid]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return 'Exist';
        } else {
            $sql_order = $pdo->prepare("select  chnl_id,orderNum,skuNum,skuDesc from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id=? limit 1");
            $sql_order->execute([$cid]);
            $res_order = $sql_order->fetch();
            if (safe_count($res_order) > 0) {
                return 'Exist';
            }
            return 'Nil';
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getAllCustomer()
{

    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}


function renewOrderList()
{

    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}


function renewOrderCnt()
{

    try {
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function generateAviraLicense($otcCode, $emailid, $companyName, $status, $restricted)
{

    $pdo = pdo_connect();

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
    $exist_sql = $pdo->prepare("select  ch_id FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE otcCode = ? LIMIT 1");
    $exist_sql->execute([$otcCode]);
    $exist_res = $exist_sql->fetch();
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
                $dt = time();
                $queryString = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.aviraLicenses (ch_id,emailId,companyname,otcCode,productName, licenseCnt, usedLicense,contractEndDate,licenseKey,productId,runtime,status, used, pending,createdDate, restricted) VALUES ('$channelId','$emailid','$companyName','$otcCode', '$product_name','$licsCnt','$activated_users','$expire_date', '" . $licsKey . "','" . $productId . "', '$runtime','1',0,'$licsCnt','$dt', '$restricted')");
                $queryString->execute();
                $res = $pdo->lastInsertId();
                $aviraId = mysqli_insert_id();

                $contractEnd = strtotime($expire_date);
                $customerOrder = getAutoOrderNo();

                $datediff = $contractEnd - $dt;

                $noOfDays = floor($datediff / (60 * 60 * 24));

                $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.orderDetails set chnl_id = '" . $channelId . "', orderNum = '" . $customerOrder . "',skuNum = '',skuDesc = '" . $product_name . "', licenseCnt = '$licsCnt', installCnt = '0' , purchaseDate = '$dt', orderDate= '$dt',contractEndDate = '" . $contractEnd . "', noofDays = '$noOfDays', payRefNum = '',nh_lic='0',trial='0', transRefNum = '', amount = '0', status = '1',aviraOtc='$otcCode'");
                $sql_ser->execute();
                $result_ser = $pdo->lastInsertId();

                $valLic = array("licsCnt" => $licsCnt, "licsKey" => $licsKey, "aviraid" => $aviraId, "contractEnds" => $contractEnd, "used" => 0, "status" => "SUCCESS", "licOrder" => $customerOrder);
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

function getOTCdetails($otcid)
{
    $pdo = pdo_connect();
    $sql = $pdo->prepare("select  * FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE (id = ? or otcCode=?) LIMIT 1");
    $sql->execute([$otcid, $otcid]);
    $res = $sql->fetch();
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}

function getNHdetails($otcid)
{
    $pdo = pdo_connect();
    $sql = $pdo->prepare("select  id,chnl_id,orderNum,skuNum,skuDesc from " . $GLOBALS['PREFIX'] . "agent.orderDetails where aviraOtc=? LIMIT 1");
    $sql->execute([$otcid]);
    $res = $sql->fetch();
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}

function getOTCDetailsByOTCCode($otccode)
{
    $pdo = pdo_connect();
    $sql = $pdo->prepare("select  * FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE otcCode =? LIMIT 1");
    $sql->execute([$otccode]);
    $res = $sql->fetch();
    if (safe_count($res) > 0) {
        return $res;
    } else {
        return array();
    }
}

function getOTCDetailsCount($otcId)
{
    $pdo = pdo_connect();
    $cid = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $sql_Coust = $pdo->prepare("select  otcCode,productName,licenseCnt,usedLicense,contractEndDate,licenseKey from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where ch_id=? and id=?");
    $sql_Coust->execute([$cid, $otcId]);
    $res_Coust = $sql_Coust->fetch();

    $sql_Cust = $pdo->prepare("select  sum(noOfPc) pcCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder where licenseKey='" . $res_Coust['licenseKey'] . "'");
    $sql_Cust->execute();
    $res_cust = $sql_Cust->fetch();

    $pendingCnt = $res_Coust['licenseCnt'] - $res_cust['pcCnt'];
    $array['licenseCount'] = $res_Coust['licenseCnt'];
    $array['pendingCount'] = $pendingCnt;
    $array['contractEDate'] = $res_Coust['contractEndDate'];
    $array['pcCount'] = $res_cust['pcCnt'];
    return $array;
}


function validateCustomerName($companyName)
{

    global $base_url;
    $str = '';
    $pdo = pdo_connect();
    $msg = '';
    $sql_channel = $pdo->prepare("select  eid,ctype from " . $GLOBALS['PREFIX'] . "agent.channel C where C.companyName=? limit 1");
    $sql_channel->execute([$companyName]);
    $res_channel = $sql_channel->fetch();
    if (safe_count($res_channel) > 0) {
        $ctype = $res_channel['ctype'];
        if ($ctype == 2 || $ctype == '2') {
            $msg = "Customer name shouldn't be same as Reseller name.";
        } else if ($ctype == 5 || $ctype == '5') {
            $msg = "Customer name alredy registered.";
        }
    } else {
        $msg = 'NOT';
    }

    return $msg;
}


function add_updateAviraLicenseCount($otcDetails, $noOfPc)
{
    $pdo = pdo_connect();

    $otcCode = $otcDetails['otcCode'];
    $prodName = $otcDetails['productName'];
    $contractEndDate = $otcDetails['contractEndDate'];
    $licenseKey = $otcDetails['licenseKey'];

    $updateSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET used = used + " . $noOfPc . ",  pending = pending - " . $noOfPc . " WHERE otcCode = ? AND productName = ? AND contractEndDate = ? AND licenseKey = ?");
    $updateSql->execute([$otcCode, $prodName, $contractEndDate, $licenseKey]);
    $updateRes = $pdo->lastInsertId();

    if ($updateRes)
        return TRUE;
    else
        return FALSE;
}

function edit_updateAviraLicenseCount($otcDetails, $noOfPc)
{
    $pdo = pdo_connect();

    $otcCode = $otcDetails['otcCode'];
    $prodName = $otcDetails['productName'];
    $contractEndDate = $otcDetails['contractEndDate'];
    $licenseKey = $otcDetails['licenseKey'];

    $updateSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET used = used + " . $noOfPc . ",  pending = pending - " . $noOfPc . " WHERE otcCode = ? AND productName = ? AND contractEndDate = ? AND licenseKey = ?");
    $updateSql->execute([$otcCode, $prodName, $contractEndDate, $licenseKey]);
    $updateRes = $pdo->lastInsertId();

    if ($updateRes)
        return TRUE;
    else
        return FALSE;
}


function createCustomerUser($eid, $entityId, $channelId, $roleId, $userName, $firstName, $lastName, $userEmail, $userKey, $siteName)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }
    $pdo = pdo_connect();
    $user_sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,entity_id,channel_id,subch_id,customer_id,role_id,username,firstName,
        lastName, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report,
        priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug,
        priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left,
        footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender,
        event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)VALUES ( ?,'" . $entityId . "',"
        . "'" . $channelId . "','0','0','" . $roleId . "','" . $userName . "','" . $firstName . "','" . $lastName . "',"
        . "'','" . $userEmail . "','','0' ,'', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', "
        . "'', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
    $user_sql->execute([$eid, $userKey]);
    $user_res = $pdo->lastInsertId();
    $insertId = mysqli_insert_id();
    return $insertId;
}

function addCust_SignupSites($username, $sitename)
{
    $pdo = pdo_connect();
    $addEnSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $username . "', '" . $sitename . "', 0, 0)");
    $addEnSite->execute();
    $result1 = $pdo->lastInsertId();
    return true;
}


function createClient_UIDirectory($companyName)
{
    global $ClientUIDefaultLocation;
    global $ClientUIFTPLocation;
    $path = $ClientUIFTPLocation . $companyName;
    $copyDir = fullCopyDirectory($ClientUIDefaultLocation, $path);
    return $path;
}

function fullCopyDirectory($source, $destination)
{
    $temp = fopen('CUST_FullCopyDirectory.txt', 'w');
    fwrite($temp, "Destination Path=" . $destination);
    $i = 0;
    $oldumask = umask(0);
    mkdir($destination, 0777);
    umask($oldumask);

    $files = scandir($source);
    foreach ($files as $file) {
        $i++;
        if (in_array($file, array(".", "..")))
            continue;
        if (copy($source . "/" . $file, $destination . "/" . $file)) {
            $delete[] = $source . $file;
        }
        fwrite($temp, "Loop" . $i);
    }
    fclose($temp);
    return TRUE;
}

function update_UIDirectoryPath($eid, $path)
{
    global $pdo;
    $sql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.channel SET clientlogo= ? WHERE eid = ?");
    $sql->execute([$path, $eid]);
    $res = $pdo->lastInsertId();
    return TRUE;
}

function getFilteredSiteName($tempSiteName, $customerNum)
{
    $res_site = preg_replace("/[^a-zA-Z0-9\s]/", "", $tempSiteName);
    $sitename = preg_replace('/\s+/', '_', $res_site);
    $sitename = trim($sitename) . '__' . trim($customerNum);
    return $sitename;
}

function getSquidConfigDetails($noOfPc)
{
    global $pdo;
    $sql = "select  configUrl, 32bitDownloadeUrl, 64bitDownloadeUrl FROM " . $GLOBALS['PREFIX'] . "agent.squidConfDetails WHERE ";
    if ($noOfPc <= 10) {
        $sql .= "noOfPc = 10 LIMIT 1";
    }
    if ($noOfPc > 10 && $noOfPc <= 50) {
        $sql .= "noOfPc = 50 LIMIT 1";
    }
    if ($noOfPc > 50 && $noOfPc <= 100) {
        $sql .= "noOfPc = 100 LIMIT 1";
    }
    if ($noOfPc > 100 && $noOfPc <= 200) {
        $sql .= "noOfPc = 200 LIMIT 1";
    }
    if ($noOfPc > 200 && $noOfPc <= 500) {
        $sql .= "noOfPc = 500 LIMIT 1";
    }
    if ($noOfPc > 500 && $noOfPc <= 750) {
        $sql .= "noOfPc = 750 LIMIT 1";
    }
    if ($noOfPc > 750 && $noOfPc <= 1000) {
        $sql .= "noOfPc = 1000 LIMIT 1";
    }
    if ($noOfPc > 1000 && $noOfPc <= 2000) {
        $sql .= "noOfPc = 2000 LIMIT 1";
    }
    if ($noOfPc > 2000) {
        $sql .= "noOfPc > 2000 LIMIT 1";
    }
    $pdoSql = $pdo->prepare($sql);
    $pdoSql->execute();
    $res = $pdoSql->fetch();
    return $res;
}


function crypt_apr1_md5($plainpasswd)
{
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $len = strlen($plainpasswd);
    $text = $plainpasswd . '$apr1$' . $salt;
    $bin = pack("H32", md5($plainpasswd . $salt . $plainpasswd));
    for ($i = $len; $i > 0; $i -= 16) {
        $text .= substr($bin, 0, min(16, $i));
    }
    for ($i = $len; $i > 0; $i >>= 1) {
        $text .= ($i & 1) ? chr(0) : $plainpasswd[0];
    }
    $bin = pack("H32", md5($text));
    for ($i = 0; $i < 1000; $i++) {
        $new = ($i & 1) ? $plainpasswd : $bin;
        if ($i % 3) $new .= $salt;
        if ($i % 7) $new .= $plainpasswd;
        $new .= ($i & 1) ? $bin : $plainpasswd;
        $bin = pack("H32", md5($new));
    }
    for ($i = 0; $i < 5; $i++) {
        $k = $i + 6;
        $j = $i + 12;
        if ($j == 16) $j = 5;
        $tmp = $bin[$i] . $bin[$k] . $bin[$j] . $tmp;
    }
    $tmp = chr(0) . chr(0) . $bin[11] . $tmp;
    $tmp = strtr(
        strrev(substr(base64_encode($tmp), 2)),
        "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
        "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
    );

    return "$" . "apr1" . "$" . $salt . "$" . $tmp;
}
