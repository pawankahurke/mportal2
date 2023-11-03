<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-mail.php';
require_once 'l-db.php';
include_once '../include/NH-Config_API.php';


function RSLR_GetAllReseller($key, $db, $cId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $reseller_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE (entityId = '$cId' OR eid = '$cId') and ctype = '2'";
        $reseller_res = find_many($reseller_sql, $db);
        if (safe_count($reseller_res) > 0) {
            return $reseller_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_GetAllSKUS($key, $db, $eid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sku_sql = "SELECT skulist FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = '$eid' LIMIT 1";
        $sku_res = find_one($sku_sql, $db);
        if (safe_count($sku_res) > 0) {
            return $sku_res['skulist'];
        } else {
            return '';
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetAllDtls($key, $db, $eid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sku_sql = "SELECT skulist FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = '$eid' LIMIT 1";
        $sku_res = find_one($sku_sql, $db);
        if (safe_count($sku_res) > 0) {
            $skuList = $sku_res['skulist'];

            $sku1_sql = "SELECT id, skuName FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE id IN ($skuList)";
            $sku1_res = find_many($sku1_sql, $db);
            return $sku1_res;
        } else {
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_IsExist($key, $db, $name, $email)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "SELECT eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE (companyName=? OR emailId=?) LIMIT 1";
        $pdb = NanoDB::connect();
        $pdo = $pdb->prepare($sql);
        $pdo->execute(array($name, $email));
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_SKUDetails($key, $db, $skuId)
{

    $key = DASH_ValidateKey($key);
    $eid = $_SESSION['user']['cId'];
    if ($key) {
        $sku_sql = "SELECT provCode, noOfDays, licenseCnt, licensePeriod, description, skuName, skuRef, trial, degrdSku, renewDays, upgrade"
            . " FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE (id = '$skuId' OR skuRef = '$skuId') and chId = '$eid'  LIMIT 1";
        $sku_res = find_one($sku_sql, $db);
        if (safe_count($sku_res) > 0) {
            return $sku_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_AddReseller($key, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $eid = $_SESSION["user"]["cId"];
        $chdtl = get_Entity_Dtl($eid);
        $serverList = $chdtl["reportserver"];

        $name = url::postToAny('name');
        $regnum = url::postToAny('regnumber');
        $refnum = url::postToAny('refnumber');
        $website = url::postToAny('website');
        $address = url::postToAny('addr');
        $city = url::postToAny('city');
        $statprov = url::postToAny('stprov');
        $zipcode = url::postToAny('zpcode');
        $country = url::postToAny('country');
        $fname = url::postToAny('fname');
        $lname = url::postToAny('lname');
        $email = url::postToAny('email');
        $phnumber = url::postToAny('phnumber');
        $loginusing = 'Email';
        $skuVal = $chdtl['skulist'];
        $agentVal = url::postToAny('agentVal');

        $entityId = $_SESSION["user"]["entityId"];
        $channelId = $_SESSION["user"]["channelId"];
        $subchannelId = $_SESSION["user"]["subchannelId"];
        $outsourcedId = $_SESSION["user"]["outsourcedId"];


        $res_reseller = RSLR_IsExist($key, $db, $name, $email);
        $res_user = USER_IsExist($key, $db, $email);


        if ($res_reseller == true) {
            return array("msg" => 'Reseller Name or Email Id Already exist');
        } else if ($res_user == true) {
            return array("msg" => 'User email id Already exist');
        } else {
            $channelInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
            referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
            ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
            VALUES ('$entityId', 0, 0, $outsourcedId, '$name', '$regnum', '$refnum', '$fname', '$lname', '$email', '$phnumber', '$address', '$city',
            '$zipcode', '$country', '$statprov', '$website','2','0','0', '0', '$skuVal', '$serverList', '1','$loginusing', '" . time() . "', '$logoPath', '$logoIconPath', 1)";

            $channel_res = redcommand($channelInsertSql, $db);
            if ($channel_res) {

                $cid = mysqli_insert_id();
                $addAgent = RSLR_InsertSalesAgents('', $db, $cid, $agentVal);
                $pid = RSLR_AddProcess($key, $db, $cid, $name, 2);
                if ($pid == 0) {
                    $del_reseller = '';
                } else {
                    $userId = RSLR_AddResellerUser($name, $fname, $lname, $email, $phnumber, $cid, 2, $country, $entityId);
                }
                return array("msg" => 'Reseller created Successfully');
            } else {
                return array("msg" => 'Fail to create channel. Please try later.');
            }
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_AddProcess($key, $db, $cid, $name, $ctype)
{
    $key = DASH_ValidateKey($key);
    global $base_url;
    if ($key) {
        $pro_sql = "select S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1";
        $res_pro = find_one($pro_sql, $db);
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
        $termsCondition = $proList['termsCondition'];
        $defltProfile = $proList['defaultProfile'];
        $followOnDarts = $proList['followOnDarts'];
        $downLoadPath = $base_url . 'eula.php';


        $currentDate = time();

        if ($ctype == 5 || $ctype == '5') {
            $res1 = preg_replace("/[^a-zA-Z0-9\s]/", "", $name);
            $sitename = preg_replace('/\s+/', '_', $res1);
        } else {
            $sitename = '';
        }


        $process_sql = "insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$cid',processName = '" . $name . "',metaSitename='" . $sitename . "',siteCode='" . $sitename . "',deployPath32 = '" . $deploy32bit . "',\n"
            . "deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "'\n"
            . ",createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',\n"
            . "dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='$downLoadPath',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',\n"
            . "privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',\n"
            . "andProfileName='profile_android',macProfileName='profile_mac',DPName='" . $defltProfile . "',termsConditionUrl='" . $termsCondition . "',folnDarts='" . $followOnDarts . "'";
        $process_result = redcommand($process_sql, $db);
        if ($process_result) {
            $processId = mysqli_insert_id($db);
        } else {
            $processId = 0;
        }

        return $processId;
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_UpdateReseller($key, $db)
{

    global $base_url;
    $entityId = $_SESSION["user"]["entityId"];
    $channelId = $_SESSION["user"]["channelId"];
    $subchannelId = $_SESSION["user"]["subchannelId"];
    $outsourcedId = $_SESSION["user"]["outsourcedId"];

    $skuVal = url::postToAny('skuVal');

    $editId = url::postToAny('editId');
    $name = url::postToAny('name');
    $regnum = url::postToAny('regnumber');
    $refnum = url::postToAny('refnumber');
    $website = url::postToAny('website');
    $address = url::postToAny('addr');
    $city = url::postToAny('city');
    $statprov = url::postToAny('stprov');
    $zipcode = url::postToAny('zpcode');
    $country = url::postToAny('country');
    $fname = url::postToAny('fname');
    $lname = url::postToAny('lname');
    $email = url::postToAny('email');
    $phnumber = url::postToAny('phnumber');
    $ctype = url::postToAny('ctype');
    $loginusing = url::postToAny('loginusing');
    $agentVal = url::postToAny('agentVal');
    $orderinfo = '';
    $hirearchyId = '';
    $outsrcId = $outsourcedId;


    $channelInsertSql = "update channel set firstName='$fname',lastName='$lname',phoneNo='$phnumber',address='$address',city='$city',zipCode='$zipcode',country='$country',province='$statprov',website='$website',businessLevel='$ctype',entyHirearchy='$hirearchyId',ordergen='$orderinfo',skulist='$skuVal',reportserver='$serverList',loginUsing='$loginusing' $logo $logoicon where eid='$editId'";
    $channel_res = redcommand($channelInsertSql, $db);
    if ($channel_res) {
        $upAgent = RSLR_UpdateSalesAgents($key, $db, $editId, $agentVal);
        return array("msg" => 'Reseller updated Successfully', "status" => 1);
    } else {
        return array("msg" => 'Fail to update channel. Please try later.', "status" => 0);
    }
}

function RSLR_AddResellerUser($name, $userName, $userLastName, $userEmail, $userPhone, $eid, $ctype, $country, $enid)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $db;
    global $base_url;

    $db = NanoDB::connect();

    $cId = $_SESSION["user"]["cId"];
    $user_type = $_SESSION["user"]["customerType"];
    $cksum = md5(mt_rand());
    $name = preg_replace('/\s+/', '_', $name);
    try {
        $sql_core = "select userid,username from " . $GLOBALS['PREFIX'] . "core.Users where (username='$name' or user_email = '$userEmail') limit 1";
        $res_core = find_one($sql_core, $db);
        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $roleId = USER_UserRoleWithCtype_PDO('', $db, $ctype);
            $resetId = USER_DownloadId_PDO('', $db);

            $sql_user = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,role_id,username,firstName,lastName, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
            VALUES ( ?,?,?,?,?,'',?,?,'0','', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, ?, '', 0, '', '', 95, '', '',?)";
            $pdo = $db->prepare($sql_user);
            $result_user = $pdo->execute(array($eid, $roleId, $name, $userName, $userLastName, $userEmail, $userPhone, $cksum, $resetId));
            $adminId = $db->lastInsertId();

            if ($result_user) {

                if ($ctype == 1) {

                    $upd_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.entity_id = CONCAT(U.entity_id, ',', ? )  where U.role_id=O.id and O.name = 'user_superadmin' and U.ch_id=?";
                    $pdo = $db->prepare($upd_enty);
                    $result_enty = $pdo->execute(array($eid, $cId));
                } else if ($ctype == 2) {

                    $chnl_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.channel_id = CONCAT(U.channel_id, ',', ?) where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'";
                    $pdo = $db->prepare($chnl_admin);
                    $chnl_result = $pdo->execute(array($eid));

                    $upd_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.channel_id = CONCAT(U.channel_id, ',',?)  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=?";
                    $pdo = $db->prepare($upd_chnl);
                    $result_chnl = $pdo->execute(array($eid, $cId));
                } else if ($ctype == 3) {

                    $sub_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',', ?) where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'";
                    $pdo = $db->prepare($sub_admin);
                    $sub_result = $pdo->execute(array($eid));

                    $upd_sub1 = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',',?)  where U.role_id=O.id and O.name = 'user_superadmin' and U.ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=2)";
                    $pdo = $db->prepare($upd_sub1);
                    $result_sub1 = $pdo->execute(array($eid, $cId));

                    $upd_sub2 = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',',?)  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=?";
                    $pdo = $db->prepare($upd_sub2);
                    $result_sub2 = $pdo->execute(array($eid, $cId));
                } else if ($ctype == 5) {

                    $cust_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',', ?) where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name in ('user_superadmin')";
                    $pdo = $db->prepare($cust_admin);
                    $result_admin = $pdo->execute(array($eid));

                    if ($user_type == 1) {

                        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',?)  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=?";
                        $pdo = $db->prepare($cust_enty);
                        $result_cust = $pdo->execute(array($eid, $cId));
                    } else if ($user_type == 2) {

                        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',?)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin','user_reselleradmin') and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=2)";
                        $pdo = $db->prepare($cust_enty);
                        $result_cust = $pdo->execute(array($eid, $cId));

                        $cust_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',', ?)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin','user_reselleradmin') and ch_id=?";
                        $pdo = $db->prepare($cust_chnl);
                        $result_chnl = $pdo->execute(array($eid, $cId));
                    } else if ($user_type == 3) {

                        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',?)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin',,'user_reselleradmin') and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=2)";
                        $pdo = $db->prepare($cust_enty);
                        $result_cust = $pdo->execute(array($eid, $cId));

                        $cust_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',?)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin',,'user_reselleradmin') and ch_id in (select C.channelId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=3)";
                        $pdo = $db->prepare($cust_chnl);
                        $result_chnl = $pdo->execute(array($eid, $cId));

                        $cust_sub = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',?)  where U.role_id=O.id and O.name = 'user_superadmin' and  ch_id=?";
                        $pdo = $db->prepare($cust_chnl);
                        $result_chnl = $pdo->execute(array($eid, $cId));
                    }
                }

                $sql_usrck = "insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username=?,level='1',cksum=?";
                $pdo = $db->prepare($sql_usrck);
                $result_ck = $pdo->execute(array($name, $cksum));

                $resetLink = $base_url . 'reset-password.php?vid=' . $resetId;
                if ($ctype == 2) {
                    RSLR_SendMail($userName, $userEmail, $resetLink, $country, $enid);
                }

                if ($ctype == 5) {
                    CUST_SendMail($userName, $userEmail, $resetLink, $country, $enid);
                }

                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function RSLR_UpdateEntityId($db, $eid, $cId)
{
    try {
        $upd_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.entity_id = CONCAT(U.entity_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name = 'user_superadmin' \n"
            . "and U.ch_id='$cId'";
        $result_enty = redcommand($upd_enty, $db);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_UpdateChannelId($db, $eid, $cId)
{
    try {
        $chnl_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.channel_id = CONCAT(U.channel_id, ',', $eid) \n"
            . "where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and \n"
            . "O.name = 'user_superadmin'";
        $chnl_result = redcommand($chnl_admin, $db);

        $upd_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.channel_id = CONCAT(U.channel_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name = 'user_superadmin' \n"
            . "and ch_id='$cId'";
        $result_chnl = redcommand($upd_chnl, $db);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_UpdateSubchnId($db, $eid, $cId)
{
    try {
        $sub_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.subch_id = CONCAT(U.subch_id, ',', $eid) \n"
            . "where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and \n"
            . "O.name = 'user_superadmin'";
        $sub_result = redcommand($sub_admin, $db);

        $upd_sub1 = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.subch_id = CONCAT(U.subch_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name = 'user_superadmin' and \n"
            . "U.ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where \n"
            . "C.eid='$cId' and C.ctype=2)";
        $result_sub1 = redcommand($upd_sub1, $db);

        $upd_sub2 = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.subch_id = CONCAT(U.subch_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name = 'user_superadmin' \n"
            . "and ch_id='$cId'";
        $result_sub2 = redcommand($upd_sub2, $db);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_UpdateCustId($db, $eid, $cId, $user_type)
{
    try {
        $cust_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',', $eid) \n"
            . "where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id \n"
            . "and O.name in ('user_superadmin')";
        $result_admin = redcommand($cust_admin, $db);

        if ($user_type == 1) {

            RSLR_UpdateUserTypeOne($db, $eid, $cId, $user_type);
        } else if ($user_type == 2) {

            RSLR_UpdateUserTypeTwo($db, $eid, $cId, $user_type);
        } else if ($user_type == 3) {

            RSLR_UpdateUserTypeThree($db, $eid, $cId, $user_type);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_UpdateUserTypeOne($db, $eid, $cId, $user_type)
{
    try {
        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name = 'user_superadmin' and ch_id='$cId'";
        $result_cust = redcommand($cust_enty, $db);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_UpdateUserTypeTwo($db, $eid, $cId, $user_type)
{
    try {
        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin','user_reselleradmin') \n"
            . "and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=2)";
        $result_cust = redcommand($cust_enty, $db);

        $cust_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin','user_reselleradmin') \n"
            . "and ch_id='$cId'";
        $result_chnl = redcommand($cust_chnl, $db);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_UpdateUserTypeThree($db, $eid, $cId, $user_type)
{
    try {
        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin',,'user_reselleradmin') \n"
            . "and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=2)";
        $result_cust = redcommand($cust_enty, $db);

        $cust_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin',,'user_reselleradmin') \n"
            . "and ch_id in (select C.channelId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=3)";
        $result_chnl = redcommand($cust_chnl, $db);

        $cust_sub = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O \n"
            . "SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  \n"
            . "where U.role_id=O.id and O.name = 'user_superadmin' and  ch_id='$cId'";
        $result_sub = redcommand($cust_sub, $db);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function RSLR_SendMail($uname, $to, $url, $country, $enid)
{

    global $base_url;
    $db = NanoDB::connect();
    $key = '';
    $chProdtl = CUST_GetChannelProcessDetails_PDO($key, $db, $enid);
    $fromEmail = $chProdtl['fromName'];

    $subject = "";
    $message = '';
    $select_template = "select mailTemplate, subjectline from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='2' and chId=? and country=? limit 1";
    $pdo = $db->prepare($select_template);
    $pdo->execute(array($enid, $country));
    $res_template = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res_template) > 0) {
        $message = $res_template['mailTemplate'];
        $subject = $res_template['subjectline'];
    } else {
        $select_template = "select mailTemplate, subjectline from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='2' and chId=? and country='USA' limit 1";
        $pdo = $db->prepare($select_template);
        $pdo->execute(array($enid));
        $res_template = $pdo->fetch(PDO::FETCH_ASSOC);
        $message = $res_template['mailTemplate'];
        $subject = $res_template['subjectline'];
    }

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

function RSLR_InsertSalesAgents($key, $db, $ch_id, $agentArray)
{
    $tempArray = explode(',', $agentArray);
    if (safe_count($tempArray) > 0) {
        foreach ($tempArray as $value) {
            $str = explode('--', $value);
            $inserSql = "insert into " . $GLOBALS['PREFIX'] . "agent.salesAgents set ch_id='$ch_id',agentName='" . $str[1] . "',agentEmail='" . $str[0] . "'";
            redcommand($inserSql, $db);
        }
        return true;
    } else {
        return true;
    }
}

function RSLR_UpdateSalesAgents($key, $db, $ch_id, $agentArray)
{
    $tempArray = explode(',', $agentArray);
    if (safe_count($tempArray) > 0) {
        $delSql = "DELETE FROM " . $GLOBALS['PREFIX'] . "agent.salesAgents WHERE ch_id = '$ch_id'";
        redcommand($delSql, $db);
        foreach ($tempArray as $value) {
            $str = explode('--', $value);
            $inserSql = "insert into " . $GLOBALS['PREFIX'] . "agent.salesAgents set ch_id='$ch_id',agentName='" . $str[1] . "',agentEmail='" . $str[0] . "'";
            redcommand($inserSql, $db);
        }
        return true;
    } else {
        return true;
    }
}



function RSLR_GetAllSalesAgents($key, $db, $eid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $agn_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.salesAgents WHERE ch_id = '$eid'";
        $agn_res = find_many($agn_sql, $db);
        if (safe_count($agn_res) > 0) {
            return $agn_res;
        } else {
            return '';
        }
    } else {
    }
}




function RSLR_GetLicenseCount($key, $db, $loggedEid, $NH_lic)
{
    $key = DASH_ValidateKey($key);
    $compIds = '';
    if ($key) {
        $sql_Coust = "SELECT SUM(licenseCnt) lseCnt, SUM(installCnt) insCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id='$loggedEid' and orderNum='$NH_lic' LIMIT 1";
        $res_Coust = find_one($sql_Coust, $db);

        if ($res_Coust['lseCnt'] != '') {
            $nextThirtyDays = strtotime("+30 days");
            $renew_sql = "SELECT COUNT(sid) as renewcount FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S,customerOrder C WHERE C.customerNum=S.customerNum and C.orderNum=S.orderNum and C.nhOrderKey='$NH_lic' S.compId IN
                              (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where channelId = '$loggedEid' and ctype = '5')
                                AND S.uninstallDate <= '$nextThirtyDays' AND S.orderStatus = NULL and S.downloadStatus='EXE' and revokeStatus='I' LIMIT 1";
            $renew_res = find_one($renew_sql, $db);
            $res_Coust['renewLicensesCnt'] = $renew_res['renewcount'];
            return $res_Coust;
        } else {
            return array("lseCnt" => 0, "insCnt" => 0, "renewLicensesCnt" => 0);
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetPTSLicenseCount($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);
    $nextThirtyDays = strtotime("+30 days");
    $today = time();
    $compIds = '';
    if ($key) {

        $sql_Coust = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE compId='$loggedEid' group by customerNum,orderNum";
        $res_Coust = find_many($sql_Coust, $db);
        $lseCnt = safe_count($res_Coust);

        $sql_install = "select count(S.sid) insCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.serviceRequest S where "
            . "C.customerNum=S.customerNum and C.orderNum=S.orderNum and S.revokeStatus='I' "
            . "and C.compId=S.compId and C.compId='$loggedEid'";
        $res_install = find_one($sql_install, $db);

        $sql_renew = "SELECT count(C.id) renewLicensesCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.skuMaster S "
            . "where C.contractEndDate BETWEEN '$today' and '$nextThirtyDays' and C.compId = '$loggedEid' and  "
            . "S.skuRef=C.SKUNum and S.renew = '1'";
        $res_renew = find_one($sql_renew, $db);

        return array("lseCnt" => $lseCnt, "insCnt" => $res_install['insCnt'], "renewLicensesCnt" => $res_renew['renewLicensesCnt']);
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_GetEntity_RenewDevices($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $nextThirtyDays = strtotime("+15 days");
        $renew_sql = "SELECT S.sid, S.customerNum, S.orderNum,  S.serviceTag, S.installationDate, S.uninstallDate,
                    S.compId,CH.companyName FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S
                    JOIN " . $GLOBALS['PREFIX'] . "agent.customerOrder C on S.compId=C.compId
                    JOIN " . $GLOBALS['PREFIX'] . "agent.channel CH on CH.eid = C.compId
                    AND S.compId IN (select eid from " . $GLOBALS['PREFIX'] . "agent.channel
                    where entityId=? AND ctype = '5') AND S.orderStatus = NULL AND S.uninstallDate <= ? group by S.customerNum,
                    S.orderNum, S.serviceTag order by S.sid desc";

        $bind = array($loggedEid, $nextThirtyDays);
        $pdo = $db->prepare($renew_sql);
        $pdo->execute($bind);
        $renew_res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        return $renew_res;
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetReseller_RenewDevices($key, $db, $loggedEid, $customerNum, $orderNum, $compId, $prcId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $nextThirtyDays = strtotime("+15 days");
        $renew_sql = "SELECT S.sid, S.customerNum, S.orderNum,  S.serviceTag, S.installationDate, S.uninstallDate,
                    S.compId,CH.companyName FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S
                    JOIN " . $GLOBALS['PREFIX'] . "agent.customerOrder C on S.compId=C.compId
                    JOIN " . $GLOBALS['PREFIX'] . "agent.channel CH on CH.eid = C.compId
                    AND S.compId IN ($compId) and S.customerNum=C.customerNum and S.orderNum=C.orderNum and S.customerNum='$customerNum' and S.orderNum='$orderNum'  AND S.orderStatus='Active' group by S.customerNum,
                    S.orderNum, S.serviceTag order by S.sid desc";
        $renew_res = find_many($renew_sql, $db);
        return $renew_res;
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetRenewDevices($key, $db, $loggedEid, $compId, $prcId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $nextThirtyDays = strtotime("+30 days");
        $renew_sql = "SELECT S.sid, S.customerNum, S.orderNum,  S.serviceTag, S.installationDate, S.uninstallDate,
                    S.compId,CH.companyName FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S
                    JOIN " . $GLOBALS['PREFIX'] . "agent.customerOrder C on S.compId=C.compId
                    JOIN " . $GLOBALS['PREFIX'] . "agent.channel CH on CH.eid = C.compId
                    AND S.compId IN (select eid from " . $GLOBALS['PREFIX'] . "agent.channel
                    where eid = '$loggedEid' AND ctype = '5') AND S.orderStatus = NULL AND S.uninstallDate <= '$nextThirtyDays' group by S.customerNum,
                    S.orderNum, S.serviceTag order by S.sid desc";
        $renew_res = find_many($renew_sql, $db);
        return $renew_res;
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_GetOrderDetailsGrid($key, $db, $loggedEid, $whereClause)
{
    $key = DASH_ValidateKey($key);
    $ctype = $_SESSION["user"]["customerType"];
    if ($key) {
        if ($ctype == 0) {
            $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE  nh_lic='0' group by aviraOtc";
        } else if ($ctype == 1) {
            $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE  chnl_id in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where ctype='2') and nh_lic='0'";
        } else {
            if ($whereClause != '') {
                $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                    . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id='$loggedEid' and id='$whereClause' limit 1";
            } else {
                if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
                    $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                        . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id='$loggedEid' and nh_lic='0'";
                } else {
                    $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                        . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id='$loggedEid' and nh_lic='1'";
                }
            }
        }
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

function RSLR_GetAviraOrderDetailsGrid($key, $db, $loggedEid, $whereClause)
{
    $key = DASH_ValidateKey($key);
    $ctype = $_SESSION["user"]["customerType"];
    $aviraIns = $_SESSION["user"]["Avira_Inst"];
    $bindings = array($loggedEid);

    if ($key) {
        if ($ctype == 2 && ($aviraIns == 1 || $aviraIns == '1')) {
            if ($whereClause != '') {
                $sql = "SELECT aviraOtc, skuDesc, licenseCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id=? and id=? limit 1";
                $bindings = array($loggedEid, $whereClause);
            } else {
                $sql = "SELECT aviraOtc, skuDesc, licenseCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id=? and nh_lic='0'";
            }
        } else if (($ctype == 5) && ($aviraIns == 1 || $aviraIns == '1')) {
            $sql = "select O.aviraOtc, O.skuDesc, O.licenseCnt from " . $GLOBALS['PREFIX'] . "agent.orderDetails O," . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses L where C.licenseKey=L.licenseKey and O.chnl_id=L.ch_id and C.compId=? and O.aviraOtc=L.otcCode";
        } else if (($ctype == 1) && ($aviraIns == 1 || $aviraIns == '1')) {
            $sql = "SELECT aviraOtc, skuDesc, licenseCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where ctype='2' and entityId=?) and nh_lic='0'";
        } else if (($ctype == 0) && ($aviraIns == 1 || $aviraIns == '1')) {
            $sql = "SELECT aviraOtc, skuDesc, licenseCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where ctype='2') and nh_lic='0'";
            $bindings = '';
        }

        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetAllRemainingLicenses($key, $db, $chnlid, $licId)
{
    $dt = time();
    $sql = "select chnl_id,licenseCnt as licenseCnt,installCnt as installCnt from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id=? and contractEndDate >= ? and id=? and status=1";
    $pdo = $db->prepare($sql);
    $pdo->execute(array($chnlid, $dt, $licId));
    $res = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function RSLR_GetRemainingLicenses($key, $db, $chnlid)
{
    $dt = time();
    $sql = "select * from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id='$chnlid' and contractEndDate >='$dt' and status=1 limit 1";
    $res = find_one($sql, $db);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}



function RSLR_GetSkuDetailsGrid($key, $db, $loggedEid, $whereClause)
{
    $wh = '';
    $key = DASH_ValidateKey($key);


    if ($key) {
        $sql = "select id, provCode, skuName,skuRef,noOfDays,skuPrice,licenseCnt from " . $GLOBALS['PREFIX'] . "agent.skuMaster where chId=?";
        $bindings = array($loggedEid);

        if ($whereClause != "") {
            $sql .= ' and skuType=?';
            $bindings[] = $whereClause;
        }

        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_GetCustomerDetailsGrid($key, $db, $loggedEid, $whereClause)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "SELECT C.coustomerFirstName, C.noOfPc, C.customerNum, C.orderNum, C.compId, C.processId, C.siteName , A.eid
                from " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.channel A WHERE C.compId = A.eid AND
                A.channelId = '74' and A.ctype = '5' GROUP BY C.customerNum";
        $res = find_many($sql, $db);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function RSLR_GetAllCustomers($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select C.customerNum,C.coustomerFirstName, C.licenseKey, C.compId, C.processId, C.siteName, C.downloadId, count(S.sid) installedCnt,C.noOfPc from " . $GLOBALS['PREFIX'] . "agent.customerOrder C left join
                " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum and S.revokeStatus='I' group by
                C.customerNum,C.orderNum";

        $pdo = $db->prepare($sql);
        $pdo->execute();
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetEntityCustomers($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);


    if ($key) {

        $sql = "select C.customerNum,C.coustomerFirstName, C.licenseKey, C.orderNum , C.compId, C.processId, C.siteName, C.downloadId, count(S.sid) installedCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder C left join
                " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum and S.revokeStatus='I'
                where C.compId in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where entityId=?)";
        $bindings = array($loggedEid);

        if (url::issetInRequest('searchVal')) {
            $searchVal = url::requestToAny('searchVal');
            $sql .= " and (C.customerNum like ? OR C.orderNum like ? OR C.emailId like ?) ";
            $bindings = array_merge($bindings, array('%' . $searchVal . '%', '%' . $searchVal . '%', '%' . $searchVal . '%'));
        }

        $sql .= " group by C.customerNum,C.orderNum";
        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetResellerCustomers($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select C.customerNum,C.coustomerFirstName, C.licenseKey, C.orderNum, C.compId, C.processId, C.siteName, C.downloadId, C.noOfPc, count(S.sid) installedCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder C left join
                " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum and S.revokeStatus='I'
                where C.compId in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where channelId=?) group by C.customerNum,C.orderNum";
        $pdo = $db->prepare($sql);
        $pdo->execute(array($loggedEid));
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetCustomer($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select C.customerNum,C.coustomerFirstName, C.licenseKey, C.orderNum, C.compId, C.processId, C.noOfPc, C.downloadId,count(S.sid) installedCnt from " . $GLOBALS['PREFIX'] . "agent.customerOrder C left join
                " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum and S.revokeStatus='I'
                where C.compId=?";
        $bindings = array($loggedEid);

        if (url::issetInRequest('searchVal')) {
            $searchVal = url::requestToAny('searchVal');
            $sql .= " and (C.customerNum like ? OR C.orderNum like ? OR C.emailId like ?)";
            $bindings = array_merge($bindings, array('%' . $searchVal . '%', '%' . $searchVal . '%', '%' . $searchVal . '%'));
        }

        $sql .= " group by C.customerNum,C.orderNum";

        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetSkuBasedCustomer($key, $db, $loggedEid, $customerType, $skuRef)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "SELECT C.*,count(S.sid) installedCnt FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C LEFT JOIN "
            . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum "
            . " and S.revokeStatus='I' where  C.compId=? AND C.SKUNum=?";
        $bindings = array($loggedEid, $skuRef);

        if (url::issetInRequest('searchVal')) {
            $searchVal = url::requestToAny('searchVal');
            $sql .= " AND (C.customerNum like ? OR C.orderNum like ? OR C.emailId like ?) ";
            $bindings = array_merge($bindings, array('%' . $searchVal . '%', '%' . $searchVal . '%', '%' . $searchVal . '%'));
        }

        $sql .= " GROUP BY C.customerNum,S.serviceTag";

        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetOTCBasedCustomer($key, $db, $loggedEid, $customerType, $OTCRef)
{
    global $download_ClientUrl;
    $key = DASH_ValidateKey($key);

    if ($key) {
        if (url::issetInRequest('searchVal')) {
            $searchVal = url::requestToAny('searchVal');
            $searchValWh = " AND (C.customerNum like '%$searchVal%' OR C.orderNum like '%$searchVal%' OR C.emailId like '%$searchVal%') ";
        } else {
            $searchValWh = "";
        }

        if ($customerType == 5) {
            $compId = $loggedEid;
        } else {
            $compId = "select eid from channel where channelId='$loggedEid' and ctype='5'";
        }
        $draw = 1;

        $sql = "SELECT C.id,C.downloadId,C.coustomerFirstName,C.customerNum,C.orderNum,C.compId,C.processId,sum(C.noOfPc) as noOfPc,C.siteName,L.ctype,L.status,L.restricted FROM " . $GLOBALS['PREFIX'] . "agent.channel L," . $GLOBALS['PREFIX'] . "agent.customerOrder C"
            . "  where  C.compId in ($compId) and C.compId=L.eid  AND C.licenseKey = '$OTCRef' $searchValWh GROUP BY C.customerNum";
        $res = find_many($sql, $db);

        if (safe_count($res) > 0) {

            foreach ($res as $value) {

                $cmpId = $value['compId'];
                $custNo = $value['customerNum'];
                $ordNum = $value['orderNum'];
                $insCnt = 0;
                $sql_ser = "SELECT count(S.sid) installedCnt FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.customerNum='$custNo' and S.orderNum='$ordNum' and S.revokeStatus='I' and  S.compId ='$cmpId' GROUP BY S.customerNum,S.orderNum";
                $res_ser = find_one($sql_ser, $db);
                if (safe_count($res_ser) > 0) {
                    $insCnt = $res_ser['installedCnt'];
                } else {
                    $insCnt = 0;
                }
                $url = $download_ClientUrl . 'eula.php?id=' . $value['downloadId'];
                $rowId = $value['compId'] . '##' . $value['processId'] . '##' . $value['customerNum'] . '##' . $value['orderNum'] . '##' . $value['status'] . '##' . $value['restricted'] . '##' . $customerType;
                $custName = CUSTAJX_CreatPTag($value['coustomerFirstName']);
                $pccount = CUSTAJX_CreatPTag($value['noOfPc'] . '/' . $insCnt);
                $recordList[] = array('DT_RowId' => $rowId, 'customername' => $custName, 'pccount' => $pccount, 'sitename' => $value['siteName'], 'custNum' => $value['customerNum'], 'custId' => $value['downloadId'], 'url' => $url);
            }
            $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($res), "recordsFiltered" => safe_count($res), "data" => $recordList);
        } else {
            $recordList = [];
            $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
        }
        return $jsonData;
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetNHLicBasedCustomer($key, $db, $loggedEid, $customerType, $OTCRef)
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
            . "" . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and C.orderNum=S.orderNum "
            . " and S.revokeStatus='I' where  C.compId in (select eid from channel where channelId='$loggedEid' and ctype='5') AND C.nhOrderKey = '$OTCRef' $searchValWh GROUP BY C.customerNum";
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

function RSLR_GetPTSBasedCustomer($key, $db, $loggedEid, $customerType)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.compId = '$loggedEid' GROUP BY customerNum";
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



function RSLR_GetCustomerDevicesGrid($key, $db, $compId, $processId, $customerNum, $orderNum, $whereClause)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "select C.sid, C.serviceTag, C.orderStatus, C.uninstallDate, C.installationDate, C.machineOS, C.machineModelNum, C.customerNum, C.orderNum,C.gatewayMachine "
            . ", C.aviraId, C.downloadStatus, C.revokeStatus from " . $GLOBALS['PREFIX'] . "agent.serviceRequest C where  C.compId = '$compId' AND C.processId = '$processId'
          AND C.customerNum = '$customerNum' AND C.orderNum = '$orderNum' and C.revokeStatus='I' group by C.sessionid";
        $res = find_many($sql, $db);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}



function RSLR_GetNextLevelEntities($key, $db, $eid, $columnName)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        echo $sql = "select eid from " . $GLOBALS['PREFIX'] . "agent.channel where $columnName = '$eid'";
        $res = find_many($sql, $db);
        if (safe_count($res)) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_GetDeviceDtls($key, $db, $deviceSid, $custNum, $ordNum)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT customerNum, serviceTag, machineManufacture, machineModelNum, macAddress, machineOS  "
            . "FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE sid = '$deviceSid' and customerNum='$custNum' and orderNum='$ordNum' and "
            . "downloadStatus='EXE' and revokeStatus='I' order by sid desc LIMIT 1";
        $res = find_one($sql, $db);
        if (safe_count($res)) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_GetCustOTC($key, $db, $compId, $proId, $customerNum, $orderNum)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    $loggedEid = $_SESSION["user"]["cId"];
    if ($key) {
        $sql = "select C.customerNum,C.orderNum,A.otcCode from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where C.licenseKey=A.licenseKey and C.customerNum=? and C.orderNum=? and C.compId=? and C.processId=? LIMIT 1";

        $bindings = array($customerNum, $orderNum, $compId, $proId);
        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            $array = $res;
            $otc = $res['otcCode'];


            $sql_alic = "select otcCode,id from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where ch_id=? and otcCode !=?";

            $bindings = array($loggedEid, $otc);
            $pdo = $db->prepare($sql_alic);
            $pdo->execute($bindings);
            $res_alic = $pdo->fetchAll(PDO::FETCH_ASSOC);

            $str = '<option >Select OTC</option>';
            foreach ($res_alic as $key => $value) {
                $str .= '<option value="' . $value['otcCode'] . '" >' . $value['otcCode'] . '</option>';
            }
            $array = array("currentOtc" => $otc, "otcList" => $str, "status" => "success");
        } else {
            $array = array("status" => "error");
        }
    } else {
        echo "Your key has been expired";
    }

    return $array;
}

function RSLR_GetSelOTCCnt($key, $db, $loggedEid, $newOTC, $oldOTC, $customerNum, $orderNum, $compId, $proId)
{
    $key = DASH_ValidateKey($key);
    $array = array();

    if ($key) {

        $sql = "select C.customerNum,C.orderNum,A.otcCode,C.noOfPc from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where C.licenseKey=A.licenseKey and C.customerNum=? and C.orderNum=? and C.compId=? and C.processId=? LIMIT 1";
        $bindings = array($customerNum, $orderNum, $compId, $proId);
        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            $insCnt = $res['noOfPc'];
            $sql_avira = "select ch_id,otcCode,productName,used,pending from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where otcCode=? limit 1";
            $bindings = array($newOTC);
            $pdo = $db->prepare($sql_avira);
            $pdo->execute($bindings);
            $res_avira = $pdo->fetch(PDO::FETCH_ASSOC);
            $penCnt = $res_avira['pending'];

            if ($penCnt >= $insCnt) {
                return array("status" => "continue", "msg" => "");
            } else {
                return array("status" => "fail", "msg" => "<span>New OTC license count are lesser than license assigned to selected customer.</span>");
            }
        }
    }
}

function RSLR_UpdateCustNewOTC($key, $db, $loggedEid, $newOTC, $oldOTC, $customerNum, $orderNum, $compId, $proId)
{

    $key = DASH_ValidateKey($key);
    $array = array();

    if ($key) {

        $sql_avira = "select id,ch_id,otcCode,productName,used,pending,licenseKey,licenseCnt,contractEndDate from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where otcCode=? and ch_id=? limit 1";
        $pdo = $db->prepare($sql_avira);
        $pdo->execute(array($newOTC, $loggedEid));
        $res_avira = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res_avira) > 0) {

            $newOTC = $res_avira['otcCode'];
            $newOTCID = $res_avira['id'];
            $licenseKey = $res_avira['licenseKey'];
            $used = $res_avira['used'];
            $licenseCnt = $res_avira['licenseCnt'];
            $pending = $res_avira['pending'];
            $contractEndDate = strtotime($res_avira['contractEndDate']);

            $sql_ordDtl = "SELECT orderNum,licenseCnt,id FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails where aviraOtc=? and chnl_id=? order by id desc limit 1";
            $pdo = $db->prepare($sql_ordDtl);
            $pdo->execute(array($newOTC, $loggedEid));
            $res_ordDtl = $pdo->fetch(PDO::FETCH_ASSOC);
            $nhOrderKey = $res_ordDtl['orderNum'];

            $sql = "select C.customerNum,C.orderNum,A.otcCode,C.noOfPc,C.sessionIni from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where C.licenseKey=A.licenseKey and C.customerNum=? and C.orderNum=? and C.compId=? and C.processId=? LIMIT 1";
            $pdo = $db->prepare($sql);
            $pdo->execute(array($customerNum, $orderNum, $compId, $proId));
            $res = $pdo->fetch(PDO::FETCH_ASSOC);

            $insCnt = $res['noOfPc'];
            $sessionIni = $res['sessionIni'];

            $totalUsed = $used + $insCnt;
            if ($pending == 0) {
                $totalPending = $licenseCnt - $insCnt;
            } else {
                $totalPending = $pending - $insCnt;
            }

            $sql_aviraOrder = "update " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET used=?,pending=? where id=? and otcCode=?";
            $pdo = $db->prepare($sql_aviraOrder);
            $result_aviraOrder = $pdo->execute(array($totalUsed, $totalPending, $newOTCID, $newOTC));

            $sql_oldavira = "select id,ch_id,otcCode,productName,used,pending,licenseKey,licenseCnt,contractEndDate from " . $GLOBALS['PREFIX'] . "agent.aviraLicenses where otcCode=? and ch_id=? limit 1";
            $pdo = $db->prepare($sql_oldavira);
            $pdo->execute(array($oldOTC, $loggedEid));
            $res_oldavira = $pdo->fetch(PDO::FETCH_ASSOC);

            if (safe_count($res_oldavira) > 0) {

                $oldOTC = $res_oldavira['otcCode'];
                $oldOTCID = $res_oldavira['id'];
                $oldlicenseKey = $res_oldavira['licenseKey'];
                $oldused = $res_oldavira['used'];
                $oldlicenseCnt = $res_oldavira['licenseCnt'];
                $oldpending = $res_oldavira['pending'];
                $oldcontEDate = strtotime($res_oldavira['contractEndDate']);

                if ($oldused != 0) {
                    $totalOldUsed = $oldused - $insCnt;
                } else {
                    $totalOldUsed = $insCnt;
                }
                $totalOldPending = $oldpending + $insCnt;

                $OldcontractEDate = Date("m/d/Y", $oldcontEDate);

                $contractEDate = Date("m/d/Y", $contractEndDate);

                $tpos = strrpos($cust_ini, "UniDays", 0);
                $oldUnidays = 'UniDays=' . $OldcontractEDate;
                $newUnidays = 'UniDays=' . $contractEDate;
                $fcust_ini = str_replace($oldUnidays, $newUnidays, $sessionIni);

                $sql_custOrder = "update " . $GLOBALS['PREFIX'] . "agent.customerOrder C SET C.nhOrderKey=?,C.licenseKey=?, C.contractEndDate=?,sessionIni=? where C.customerNum=? and C.orderNum=? and C.compId=? and C.processId=?";
                $pdo = $db->prepare($sql_custOrder);
                $result_custOrder = $pdo->execute(array($nhOrderKey, $licenseKey, $contractEndDate, $fcust_ini, $customerNum, $orderNum, $compId, $proId));

                $sel_ser = "select S.customerNum,S.orderNum,S.serviceTag,S.iniValues,S.uninstallDate from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.customerNum=? and S.orderNum=? and S.compId=? and S.processId=?";
                $pdo = $db->prepare($sel_ser);
                $pdo->execute(array($customerNum, $orderNum, $compId, $proId));
                $res_sel = $pdo->fetchAll(PDO::FETCH_ASSOC);

                foreach ($res_sel as $value) {

                    $serINI = $value['iniValues'];
                    $serTag = $value['serviceTag'];

                    $oldUnidays = 'UniDays=' . $OldcontractEDate;
                    $newUnidays = 'UniDays=' . $contractEDate;
                    $ser_ini = str_replace($oldUnidays, $newUnidays, $serINI);

                    $sql_updser = "update " . $GLOBALS['PREFIX'] . "agent.serviceRequest C SET C.uninstallDate=?,C.iniValues=? where C.customerNum=? and C.orderNum=? and C.compId=? and C.processId=? and C.serviceTag=? and C.downloadStatus='EXE' and C.revokeStatus='I'";
                    $pdo = $db->prepare($sql_updser);
                    $result_updser = $pdo->execute(array($contractEndDate, $ser_ini, $customerNum, $orderNum, $compId, $proId, $serTag));
                }

                $sql_aviraOldOrder = "update " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET used=?,pending=? where otcCode=? and ch_id=?";
                $pdo = $db->prepare($sql_aviraOldOrder);
                $result_aviraOldOrder = $pdo->execute(array($totalOldUsed, $totalOldPending, $oldOTC, $loggedEid));
            }

            return array("status" => "success", "msg" => "<span>OTC has been updated successfully. Now customer is attached with new OTC.</span>");
        } else {
            return array("status" => "ERROR", "msg" => "<span>Fail to update new OTC.</span>");
        }
    }
}

function RSLR_RevokeaviraSubscription($key, $db, $customerNum, $orderNum, $servicetag)
{
    $key = DASH_ValidateKey($key);
    $array = array();

    if ($key) {

        $sql_ser = "update " . $GLOBALS['PREFIX'] . "agent.serviceRequest set revokeStatus='U' where serviceTag=? and customerNum=? and orderNum=? and downloadStatus='EXE' and revokeStatus='I'";
        $pdo = $db->prepare($sql_ser);
        $pdo->execute(array($servicetag, $customerNum, $orderNum));
        $result = $pdo->fetch(PDO::FETCH_ASSOC);

        if ($result) {

            $sql_sitename = "select siteName from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where serviceTag=? and customerNum=? and orderNum=? and downloadStatus='EXE' and revokeStatus='U' order by sid desc limit 1";
            $pdo = $db->prepare($sql_sitename);
            $pdo->execute(array($servicetag, $customerNum, $orderNum));
            $res_sitename = $pdo->fetch(PDO::FETCH_ASSOC);
            $sitename = $res_sitename['siteName'];

            $sql_census = "select id from " . $GLOBALS['PREFIX'] . "core.Census where host=? and site=?";
            $pdo = $db->prepare($sql_census);
            $pdo->execute(array($servicetag, $sitename));
            $result_census = $pdo->fetch(PDO::FETCH_ASSOC);
            $mid = $result_census['id'];

            RSLR_removeRevokePC($mid);
            return array("status" => "Success", "msg" => "<span>Revoke license success.</span>");
        } else {
            return array("status" => "ERROR", "msg" => "<span>Fail to revoke.</span>");
        }
    }
}

function RSLR_removeRevokePC($mid)
{
    $action = 'exp';
    $res = NH_Config_API_exp($mid, $action);
    return $res;
}

function RSLR_GetEntityDtls($key, $db, $cId)
{

    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT reportserver FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = '$cId' LIMIT 1";
        $res = find_one($sql, $db);
        if (safe_count($res)) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_GetEntityDtlsByName($key, $db, $cmpName)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE companyName = '$cmpName' LIMIT 1";
        $res = find_one($sql, $db);
        if (safe_count($res)) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_GetUniqueCustomerNums($key, $db, $customerNum, $ordNum, $cId, $pId)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "select customerNum,orderNum,siteName,processId,compId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=? and orderNum=? and compId=? and processId=? limit 1";
        $pdo = $db->prepare($sql);
        $pdo->execute(array($customerNum, $ordNum, $cId, $pId));
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_UpdateLicenseCounts($key, $db, $chnl_id, $orderNum)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.orderDetails O SET O.installCnt = O.installCnt + 1 WHERE O.orderNum=? AND O.chnl_id=?";
        $pdb = NanoDB::connect();
        $pdo = $pdb->prepare($sql);
        $result = $pdo->execute(array($orderNum, $chnl_id));

        return $result;
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_GetCustomerDetails($key, $db, $custNumber, $ordNum)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT sessionIni, siteName FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum = '$custNumber' and orderNum='$ordNum' ORDER BY id DESC LIMIT 1";
        $res = find_one($sql, $db);
        if (safe_count($res) > 0) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_GetCustomerDetails_PDO($key, $db, $custNumber, $ordNum)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT sessionIni, siteName FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.customerNum=? and orderNum=? ORDER BY id DESC LIMIT 1";
        $pdo = $db->prepare($sql);
        $pdo->execute(array($custNumber, $ordNum));
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_InsertServiceRequest($key, $db, $deviceDtls, $newOrderNumber, $uninstallDate, $cId, $pId, $custNum, $oldOrdNum)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $customerNum = $deviceDtls['customerNum'];
        $custDetails = RSLR_GetCustomerDetails_PDO($key, $db, $custNum, $newOrderNumber);
        $installDate = time();
        $sessionIni = $custDetails['sessionIni'];

        $siteName = $custDetails['siteName'];
        $sessionid = md5(mt_rand());

        $serviceTag = $deviceDtls['serviceTag'];
        $manufacturer = $deviceDtls['machineManufacture'];
        $modelNum = $deviceDtls['machineModelNum'];
        $macAddress = $deviceDtls['macAddress'];
        $os = $deviceDtls['machineOS'];

        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest (customerNum, orderNum, sessionid, serviceTag,
                    installationDate, uninstallDate, iniValues, agentPhoneId, createdTime, backupCapacity,
                    downloadStatus, oldServiceTag, revokeStatus, machineManufacture, machineModelNum, pcNo,
                    machineName, machineOS, clientVersion, oldVersion, assetStatus, uninsdormatStatus,
                    uninsdormatDate, downloadId, macAddress, processId, compId, siteName, subscriptionKey,
                     licenseKey, orderStatus) VALUES (?, ?, ?, ?, ?, ?, ?, '', '', 5, 'EXE', NULL, 'I', ?, ?, 1, NULL, ?, NULL, NULL, 0, NULL, 0, NULL, ?, ?, ?, ?, NULL, NULL, 'Renewed')";
        $pdo = $db->prepare(array($customerNum, $newOrderNumber, $sessionid, $serviceTag, $installDate, $uninstallDate, $sessionIni, $manufacturer, $modelNum, $os, $macAddress, $pId, $cId, $siteName));
        $result = $pdo->execute([$searchVal]);

        return $result;
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_InsertGatewayMachine($key, $db, $compId, $processId, $customerNum, $orderNum, $serviceTag, $cust_ini, $siteName, $aviraModules, $aviraVrnUpdt, $uninstallDate)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $installDate = time();
        $sessionid = md5(mt_rand());
        $rSessionUrl = '';

        $strAviraMod = "AvModules=" . $aviraModules;
        $strAviraVer = "AviraOldRemoval=" . $aviraVrnUpdt;
        $aviraMod = "AvModules=AvGuard,AvMailScanner,AvWebGuard,AvRootKit,AvProActiv,AvMgtFirewall";
        $aviraVrnUpdt = "AviraOldRemoval=0";
        if ($aviraModules != '' && $aviraModules != NULL) {
            $cust_ini = str_replace($aviraMod, $strAviraMod, $cust_ini);
        }
        $cust_ini = str_replace($aviraVrnUpdt, $strAviraVer, $cust_ini);
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

        $insertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest (customerNum, orderNum, sessionid, serviceTag,
                    installationDate, uninstallDate, iniValues, agentPhoneId, createdTime, backupCapacity,
                    downloadStatus, oldServiceTag, revokeStatus, machineManufacture, machineModelNum, pcNo,
                    machineName, machineOS, clientVersion, oldVersion, assetStatus, uninsdormatStatus,
                    uninsdormatDate, downloadId, macAddress, processId, compId, siteName, subscriptionKey,
                     licenseKey, orderStatus) VALUES ($customerNum, $orderNum, '$sessionid', '$serviceTag',
                     '$installDate', '$uninstallDate', '$fcust_ini', '', '', 5, 'D', NULL, 'I', '', '', 1,
                     NULL, '', NULL, NULL, 0, NULL, 0, NULL, '', '$processId', '$compId', '$siteName', NULL, NULL, 'Active')";
        $result = redcommand($insertSql, $db);
        return $result;
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetProcessDetails($key, $db, $pId)
{
    $key = DASH_ValidateKey($key);
    $array = array();

    if ($key) {

        $sql = "select downloaderPath, DbIp from processMaster P where P.pId=?";
        $pdb = NanoDB::connect();
        $pdo = $pdb->prepare($sql);
        $pdo->execute([$pId]);
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_AddMSPReseller($key, $db, $sname, $slname, $scname, $semail, $compAddr, $compCity, $compState, $comzipcode, $compwebsite, $signupsource, $language)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $eid = $_SESSION['user']['cId'];

        $sql = "select eid,reportserver from " . $GLOBALS['PREFIX'] . "agent.channel P where P.eid = '$eid'";
        $res = find_one($sql, $db);
        if (safe_count($res) > 0) {
            $eid = $res['eid'];
            $serverList = $res["reportserver"];

            $name = $scname;
            $regnum = '';
            $refnum = '';
            $website = $compwebsite;
            $address = $compAddr;
            $city = $compCity;
            $statprov = $compState;
            $zipcode = $comzipcode;
            $country = '';
            $fname = $sname;
            $lname = $slname;
            $email = $semail;
            $phnumber = '';
            $loginusing = 'Email';
            $skuVal = '';
            $agentVal = '';

            $entityId = $eid;
            $channelId = 0;
            $subchannelId = 0;
            $outsourcedId = 0;

            $res_reseller = RSLR_IsExist($key, $db, $name, $email);
            $res_user = USER_IsExist($key, $db, $email);

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
                '$zipcode', '$country', '$statprov', '$website','2','Channel','Commercial', '0', '$skuVal', '$serverList', '1','$loginusing', '" . time() . "', '$logoPath', '$logoIconPath', 1)";

                $channel_res = redcommand($channelInsertSql, $db);
                if ($channel_res) {

                    $cid = mysqli_insert_id();
                    $pid = RSLR_AddProcess($key, $db, $cid, $resName, 2);
                    if ($pid == 0) {
                        $del_reseller = '';
                    } else {
                        $userId = RSLR_AddSignupUser($resName, $fname, $lname, $email, $phnumber, $cid, 2, $signupsource, $language);
                        RSLR_CRMResellerlogin($fname, $lname, $email, $name, $cid);
                    }
                    return array("status" => "success", "msg" => 'Your account has been successfully created, please check your mail for further instructions.');
                } else {
                    return array("status" => "error", "msg" => 'Fail to create account. Please try again.');
                }
            }
        } else {
            return array("status" => "error", "msg" => 'Fail to create account. Please try again.');
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_AddSignupReseller($key, $db, $sname, $slname, $scname, $semail, $signupsource, $language)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $pdb = NanoDB::connect();

        $sql = "select eid from " . $GLOBALS['PREFIX'] . "agent.channel P where P.companyName = 'NH_MSP'";

        $pdo = $pdb->prepare($sql);
        $pdo->execute();
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        $eid = $res['eid'];
        $chdtl = RSLR_Entity_Dtl($eid);
        $serverList = $chdtl["reportserver"];

        $name = $scname;
        $regnum = '';
        $refnum = '';
        $website = '';
        $address = '';
        $city = '';
        $statprov = '';
        $zipcode = '';
        $country = '';
        $fname = $sname;
        $lname = $slname;
        $email = $semail;
        $phnumber = '';
        $loginusing = 'Email';
        $skuVal = '';
        $agentVal = '';

        $entityId = $eid;
        $channelId = 0;
        $subchannelId = 0;
        $outsourcedId = 0;


        $res_reseller = RSLR_IsExist($key, $db, $name, $email);
        $res_user = USER_IsExist($key, $db, $email);


        if ($res_reseller == true) {
            return array("status" => "error", "msg" => '<span>Company Name or Email Id Already exist</span>');
        } else if ($res_user == true) {
            return array("status" => "error", "msg" => '<span>Email id Already exist</span>');
        } else {
            $autoinc_sql = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'agent' AND   TABLE_NAME   = 'channel'";
            $pdo = $pdb->prepare($autoinc_sql);
            $pdo->execute();
            $autoinc_res = $pdo->fetch(PDO::FETCH_ASSOC);

            $incrementId = $autoinc_res['AUTO_INCREMENT'];
            $resName = $name . '_' . $incrementId;
            $channelInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
            referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
            ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
            VALUES (?, 0, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,'2','Channel','Commercial', '0', ?, ?, '1',?, '" . time() . "', ?, ?, 1)";

            $pdo = $pdb->prepare($channelInsertSql);
            $channel_res = $pdo->execute($entityId, $outsourcedId, $resName, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $skuVal, $serverList, $loginusing, $logoPath, $logoIconPath);

            if ($channel_res) {

                $cid = $pdb->lastInsertId();
                $pid = RSLR_AddProcess_PDO($key, $db, $cid, $resName, 2);
                if ($pid == 0) {
                    $del_reseller = '';
                } else {
                    $userId = RSLR_AddSignupUser($resName, $fname, $lname, $email, $phnumber, $cid, 2, $signupsource, $language);
                    $parentId = 0;
                    RSLR_AviraCRMlogin($fname, $lname, $email, $name, $cid, 2, $parentId);
                }
                return array("status" => "success", "msg" => '<span>Your account has been successfully created, please check your mail for further instructions.</span>');
            } else {
                return array("status" => "error", "msg" => '<span>Fail to create account. Please try again.</span>');
            }
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_AddSignupCustomer($key, $db, $sname, $slname, $scname, $semail, $signupsource, $language)
{

    global $base_url;
    global $CRMEN;
    global $reseller_name;
    global $trialDays;
    $key = DASH_ValidateKey($key);

    if ($key) {

        $pdb = NanoDB::connect();
        $sql = "select eid from " . $GLOBALS['PREFIX'] . "agent.channel P where P.companyName = 'Nanoheal' AND P.ctype='1'";
        $pdo = $pdb->prepare($sql);
        $pdo->execute();
        $res = $pdo->fetch(PDO::FETCH_ASSOC);
        $eid = $res['eid'];

        $sql_chnl = "select eid from " . $GLOBALS['PREFIX'] . "agent.channel P where P.companyName = ? AND P.ctype='2'";
        $pdo = $pdb->prepare($sql_chnl);
        $pdo->execute([$reseller_name]);
        $res_chnl = $pdo->fetch(PDO::FETCH_ASSOC);
        $chid = $res_chnl['eid'];

        $chdtl = RSLR_Entity_Dtl($eid);
        $serverList = $chdtl["reportserver"];

        $name = $scname;
        $regnum = '';
        $refnum = '';
        $website = '';
        $address = '';
        $city = '';
        $statprov = '';
        $zipcode = '';
        $country = '';
        $fname = $sname;
        $lname = $slname;
        $email = $semail;
        $phnumber = '';
        $loginusing = 'Email';
        $skuVal = '';
        $agentVal = '';

        $entityId = $eid;
        $channelId = $chid;
        $subchannelId = 0;
        $outsourcedId = 0;


        $res_reseller = RSLR_IsExist($key, $db, $name, $email);
        $res_user = USER_IsExist($key, $db, $email);
        $logoPath = '';
        $logoIconPath = '';

        if ($res_reseller == true) {
            return array("status" => "error", "msg" => '<span>Company Name or Email Id Already exist</span>');
        } else if ($res_user == true) {
            return array("status" => "error", "msg" => '<span>Email id Already exist</span>');
        } else {
            $trialStartDate = time();
            $curDate = date("Y-m-d H:i:s");
            $trialEndDate = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$trialDays day");

            $channelInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
            referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
            ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status, trialEnabled, trialStartDate, trialEndDate)
            VALUES (?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,'5','Customer','Commercial', '0', ?, ?, '1', ?, '" . time() . "', ?, ?, 1, '1', ?, ?)";

            $pdo = $pdb->prepare($channelInsertSql);
            $channel_res = $pdo->execute([$entityId, $channelId, $outsourcedId, $name, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $skuVal, $serverList, $loginusing, $logoPath, $logoIconPath, $trialStartDate, $trialEndDate]);

            if ($channel_res) {

                $cid = $pdb->lastInsertId();
                $pid = RSLR_AddProcess($key, $db, $cid, $name, 5);
                if ($pid == 0) {
                    $del_reseller = '';
                } else {
                    $userId = RSLR_AddSignupUser($name, $fname, $lname, $email, $phnumber, $cid, 2, $signupsource, $language);
                    $trialSite = 0;
                    $pccount = 5;
                    $agentEmail = '';
                    $custNo = date("Y") . '000' . $cid;
                    $custOrderNo = CUST_AutoCustNo_PDO($pdb);
                    $sitename = CUSTAJX_GetFilteredSiteName($name, $custNo);
                    $provision_urlStr = RSLR_customerOrder($db, $custNo, $custOrderNo, $fname, $lname, $email, '', $cid, $name, $channelId, $trialSite, '', $pccount, $agentEmail, $sitename);

                    if ($provision_urlStr != "NOTDONE") {
                        RSLR_assignSites($key, $sitename, $entityId, $channelId, $cid, $db);
                        CUST_CreateClient_UIDirectory($name);
                        $downLoadUrl = $base_url . 'eula.php?id=' . $provision_urlStr;
                        $_SESSION["trial"]["dwnlId"] = $downLoadUrl;

                        if ($CRMEN == 1 && $signupsource == 'crmsignup') {
                            RSLR_CRMlLeadlogin($fname, $lname, $email, $name, $custNo, $cid);
                        } else if ($CRMEN == 1 && $signupsource != 'crmsignup') {
                            RSLR_CRMlogin($fname, $lname, $email, $name, $custNo, $cid);
                        }

                        $trialOrder = RSLR_CreateTrialOrder($cid, 5);
                        $selectedLang = isset($_SESSION['localization']) ? $_SESSION['localization'] : "en";

                        return array("status" => "success", "msg" => 'Your account has been successfully created, please check your mail for further instructions.', "userId" => base64_encode($email));
                    } else {
                        return array("status" => "error", "msg" => 'Fail to create account. Please try again.');
                    }
                }
            } else {
                return array("status" => "error", "msg" => 'Fail to create account. Please try again.');
            }
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_assignSites($key, $siteName, $entityId, $channelId, $cid, $db)
{

    $db = NanoDB::connect();

    try {

        $serl_cust = "select id,username,customer from " . $GLOBALS['PREFIX'] . "core.Customers where customer=? limit 1";
        $pdo = $db->prepare($serl_cust);
        $pdo->execute([$siteName]);
        $serl_res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($serl_res) > 0) {
        } else {

            if ($entityId != 0) {

                $sqlEntity = "select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=?";
                $pdo = $db->prepare($sqlEntity);
                $pdo->execute([$entityId]);
                $ent_res = $pdo->fetchAll(PDO::FETCH_ASSOC);

                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {
                        $addEnSite = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES (?, ?, 0, 0)";
                        $pdo = $db->prepare($addEnSite);
                        $result1 = $pdo->execute([$entValue['username'], $siteName]);
                    }
                }
            }


            if ($channelId != 0) {

                $sqlChannel = "select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=?";
                $pdo = $db->prepare($sqlChannel);
                $pdo->execute([$channelId]);
                $chnl_res = $pdo->fetchAll(PDO::FETCH_ASSOC);

                if (safe_count($chnl_res) > 0) {
                    foreach ($chnl_res as $chnlValue) {
                        $addEnSite = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES (?, ?, 0, 0)";
                        $pdo = $db->prepare($sqlChannel);
                        $result1 = $pdo->execute([$chnlValue['username'], $siteName]);
                    }
                }
            }

            if ($cid != 0) {
                $sqlCustmr = "select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=?";
                $pdo = $db->prepare($sqlCustmr);
                $pdo->execute([$cid]);
                $custmr_res = $pdo->fetchAll(PDO::FETCH_ASSOC);

                if (safe_count($custmr_res) > 0) {
                    foreach ($custmr_res as $value) {
                        $addcustSite = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES (?, ?, 0, 1)";
                        $pdo = $db->prepare($addcustSite);
                        $result1 = $pdo->execute([$value['username'], $siteName]);
                    }
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function RSLR_customerOrder($db, $crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName, $channelId, $trialSite, $otcCode, $noOfPc, $agentEmail, $sitename)
{
    global $trialDays;
    $valLic = 5;
    $pId = CUST_GetProcessId($db, $cId);
    $customerNumber = CUST_AutoCustNo_PDO($db);
    $customerOrder = CUST_AutoOrderNo_PDO($db);
    $provCode = '01';
    $noOfDays = $trialDays;
    $key = '';
    if ($cId == '' || $pId == '') {
        return "NOTDONE";
    }
    $custDtl = RSLR_Entity_Dtl($channelId);
    $reportserver = $custDtl["reportserver"];

    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEnd = strtotime(Date("m/d/Y", strtotime("+$noOfDays days")));

    $process_detail = CUST_GetProcessData($key, $db, $pId);
    $variation = $process_detail['variation'];
    $locale = $process_detail['locale'];
    $downloadPath = $process_detail['downloaderPath'];
    $sendEmail = $process_detail['sendMail'];
    $backUp = $process_detail['backupCheck'];
    $respectiveDB = $process_detail['DbIp'];

    $seesionSet = CUST_SetProcessDetailSession($process_detail);
    $fileString = create_ini_parameters_PDO($sitename, $dateOfOrder, $customerNumber, $customerOrder, $customerEmailId, "", $provCode, $reportserver, '0', '', '');

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = CUST_GetCustDownloadId($db);
        $sessionid = md5(mt_rand());

        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = ?, orderNum = ?,"
            . " refCustomerNum = ?,refOrderNum = ?, coustomerFirstName = ?, "
            . " coustomerLastName = ? , coustomerCountry = '', "
            . " emailId= ?,SKUNum = '002-0000/MSP30D5PCTRIAL', SKUDesc = 'Nanoheal Trial(30 days)', "
            . " orderDate = ?, contractEndDate = ?, backupCapacity = '0', "
            . " sessionid = ?, sessionIni = ?, "
            . " validity = ?, noOfPc = '0',oldorderNum ='',"
            . " provCode = ?,remoteSessionURL = '',agentId = ?,"
            . " processId= ?,compId=?,downloadId=?,siteName=?,advSub='1',"
            . " licenseKey = ''";

        $pdo = $db->prepare($sql_ser);
        $result = $pdo->execute([
            $customerNumber, $customerOrder, $crmCustomerNum, $crmOrderNum, $customerFirstName,
            $customerLastName, trim($customerEmailId), $dateOfOrder, $contractEnd, $sessionid, safe_addslashes($fileString),
            trim($noOfDays), $provCode, $agentEmail, $pId, $cId, $downloadId, $sitename
        ]);

        if ($result) {
            return $downloadId;
        } else {
            return "NOTDONE";
        }
    } else {
        return "NOTDONE";
    }
}

function RSLR_CreateTrialOrder($chid, $pcno)
{
    global $CRMEN;
    global $crmAccountId;
    global $crmAccountName;
    global $trialDays;
    try {

        $db = NanoDB::connect();

        $customerOrder = RSLR_getAutoOrderNo();
        $skuNum = '';
        $skuDesc = 'Tria sku';
        $dt = time();
        $downlUrl = $_SESSION["trial"]["dwnlId"];
        unset($_SESSION["trial"]["dwnlId"]);
        $noOfDays = $trialDays;
        $curDate = date("Y-m-d H:i:s");
        $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");
        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.orderDetails set "
            . "chnl_id=?, orderNum=?,skuNum=?,"
            . "skuDesc=?, licenseCnt=?, installCnt = '0' , purchaseDate =?, "
            . "orderDate=?,contractEndDate=?, noofDays = '30', payRefNum = '', "
            . "transRefNum = '', amount = '0', status = '1'";

        $pdo = $db->prepare($sql_ser);
        $result = $pdo->execute([$chid, $customerOrder, $skuNum, $skuDesc, $pcno, $dt, $dt, $contractEnd]);

        if ($result) {

            if ($CRMEN == 1) {
                $orderDate = date("Y-m-d");
                $orderEnd = Date("Y-m-d", strtotime("+$noOfDays days"));
                $parameters1 = array(
                    array("name" => "sku_type_c", "value" => "new"),
                    array("name" => "country_c", "value" => "USA"),
                    array("name" => "tax_c", "value" => "0"),
                    array("name" => "order_id_c", "value" => $customerOrder),
                    array("name" => "quantity_c", "value" => $pcno),
                    array("name" => "total_amount_c", "value" => "0"),
                    array("name" => "gross_amount_c", "value" => "0"),
                    array("name" => "order_start_date_c", "value" => $orderDate),
                    array("name" => "order_end_date_c", "value" => $orderEnd),
                    array("name" => "download_link_c", "value" => ""),
                    array("name" => "invoice_number_c", "value" => ""),
                    array("name" => "order_status_c", "value" => "Active"),
                    array("name" => "payment_status_c", "value" => "Active"),
                    array("name" => "sku_name_c", "value" => "trial"),
                    array("name" => "account_name", "value" => $crmAccountName),
                    array("name" => "account_id", "value" => $crmAccountId),
                    array("name" => "download_link_c", "value" => $downlUrl),
                );

                $sql_insCnt = "select id,emailId,chId,crmUserId,crmLeadId,mauticId,downloadCnt,installCnt from " . $GLOBALS['PREFIX'] . "agent.contactDetails where chId=? limit 1";
                $pdo = $db->prepare($sql_insCnt);
                $pdo->execute([$chid]);
                $res_DnlCnt = $pdo->fetch(PDO::FETCH_ASSOC);

                if (safe_count($res_DnlCnt) > 0) {
                    $contactId = $res_DnlCnt['crmUserId'];
                }
            }
            return $result;
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function RSLR_AddPaySignupReseller($key, $db, $signupsource)
{
    global $base_url;
    global $dashPayurl;
    $key = DASH_ValidateKey($key);

    if ($key) {
        $data = safe_json_decode(file_get_contents('php://input'), true);

        $pdb = NanoDB::connect();


        $sql = "select eid from " . $GLOBALS['PREFIX'] . "agent.channel P where P.companyName = 'NH_MSP'";
        $pdo = $pdb->prepare($sql);
        $pdo->execute();
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        $eid = $res['eid'];

        $chdtl = RSLR_Entity_Dtl($eid);
        $serverList = $chdtl["reportserver"];

        $name = strip_tags($data['company_name']);
        $regnum = '';
        $refnum = '';
        $website = '';
        $address = strip_tags($data['street']);
        $city = strip_tags($data['city']);
        $statprov = strip_tags($data['state']);
        $zipcode = strip_tags($data['postal']);
        $country = strip_tags($data['country']);
        $fname = strip_tags($data['first_name']);
        $lname = strip_tags($data['last_name']);
        $email = strip_tags($data['email_address']);
        $phnumber = strip_tags($data['phone_num']);
        $ch_id = strip_tags($data['chid']);
        $loginusing = 'Email';
        $skuVal = '';
        $agentVal = '';
        $pcCnt = strip_tags($data['pcCnt']);
        $sku = strip_tags($data['sku']);
        $entityId = $eid;
        $channelId = 0;
        $subchannelId = 0;
        $outsourcedId = 0;
        $purchse_type = strip_tags($data['purchse_type']);

        $sql_sku = "select skuName,skuRef,ppid,description,skuPrice from skuMaster where skuRef=?";

        $pdo = $pdb->prepare($sql_sku);
        $pdo->execute([$sku]);
        $res_sku = $pdo->fetch(PDO::FETCH_ASSOC);

        $skuDesc = $res_sku['skuName'];
        $payprice = '$' . $res_sku['skuPrice'];
        $NH_price = $res_sku['skuPrice'];
        $total1 = $pcCnt * $NH_price;
        $payTotal = '$' . $total1;

        $purchaseDt = date("F j, Y");

        if ($purchse_type == 'new') {

            $autoinc_sql = "SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'agent' AND   TABLE_NAME   = 'channel'";
            $pdo = $pdb->prepare($autoinc_sql);
            $pdo->execute();
            $autoinc_res = $pdo->fetch(PDO::FETCH_ASSOC);

            $incrementId = $autoinc_res['AUTO_INCREMENT'];
            $resName = $name . '_' . $incrementId;

            $res_reseller = RSLR_IsExist($key, $db, $resName, $email);
            $res_user = USER_IsExist($key, $db, $email);


            if ($res_reseller == true) {
                return array("status" => "error", "msg" => 'Company Name or Email Id Already exist.');
            } else if ($res_user == true) {
                return array("status" => "error", "msg" => 'Email id Already exist.');
            } else {

                $channelInsertSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
                referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
                ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
                VALUES (?, 0, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,'2','Channel','Commercial', '0', ?, ?, '1', ? , '" . time() . "', ?, ?, 1)";

                $pdo = $pdb->prepare($channelInsertSql);
                $channel_res = $pdo->execute($entityId, $outsourcedId, $resName, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $skuVal, $serverList, $loginusing, $logoPath, $logoIconPath);

                if ($channel_res) {

                    $cid = $pdb->lastInsertId();
                    $pid = RSLR_AddProcess_PDO($key, $db, $cid, $resName, 2);
                    if ($pid == 0) {
                        $del_reseller = '';
                    } else {
                        RSLR_createPurchaseOrder($cid, $pcCnt, $sku);
                        CUST_CreateClient_UIDirectory($resName);
                        RSLR_AddPaySignupUser($resName, $fname, $lname, $email, $phnumber, $cid, $skuDesc, $purchaseDt, $pcCnt, $payprice, $payTotal);
                        $resetLink = $dashPayurl . 'index.php';
                    }
                    $msg = array("status" => "success", "msg" => 'Your account has been successfully created, please check your mail for further instructions.');
                } else {
                    $msg = array("status" => "error", "msg" => 'Fail to create account. Please try again.');
                }
                return $msg;
            }
        } elseif ($purchse_type == 'exist') {

            $channelInsertSql = "update " . $GLOBALS['PREFIX'] . "agent.channel set trialEnabled='0', phoneNo=?, address=?, city=?, zipCode=?, country=?, province=? where eid=?";
            $pdo = $pdb->prepare($channelInsertSql);
            $channel_res = $pdo->execute([$phnumber, $address, $city, $zipcode, $country, $statprov, $ch_id]);

            if ($channel_res) {
                RSLR_createPurchaseOrder($ch_id, $pcCnt, $sku);
                $resetLink = $dashPayurl . 'home/myaccount.php';
                RSLR_PurchaseSendMail($fname, $email, $resetLink, $skuDesc, $purchaseDt, $pcCnt, $payprice, $payTotal);
                $msg = array("status" => "success", "msg" => 'Invoice and account details has been sent to your email id.Please check your email id to continue.');
            } else {
                $msg = array("status" => "error", "msg" => 'Fail to create account. Please try again.');
            }
            return $msg;
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_payLogin($username, $pwd)
{

    $encryptedpwd = md5($pwd);
    $loginSql = "select userid,ch_id,username,user_email,user_phone_no,role_id,priv_admin,firstName,cksum from " . $GLOBALS['PREFIX'] . "core.Users where (user_email=? or user_phone_no=?) and password=? limit 1";
    $pdb = NanoDB::connect();
    $pdo = $pdb->prepare($loginSql);
    $pdo->execute([$username, $username, $encryptedpwd]);
    $res1 = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res1) > 0) {
        $msg = array("status" => "success", "userkey" => $res1['cksum']);
    } else {
        $msg = array("status" => "error", "userkey" => "");
    }
    return $msg;
}

function RSLR_getLoginDtl($uid, $pwd)
{
    $encryptedpwd = md5($pwd);
    $loginSql = "select U.userid,U.ch_id,username,U.user_email,U.firstName,U.lastName,C.companyName,C.address,C.city,C.province,C.zipCode,C.country,C.phoneNo from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C where U.cksum=? and U.ch_id=C.eid  limit 1";

    $pdb = NanoDB::connect();
    $pdo = $pdb->prepare($loginSql);
    $pdo->execute([$uid]);
    $res1 = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res1) > 0) {
        return array("status" => "success", "data" => $res1);
    } else {
        return array("status" => "error");
    }
}

function RSLR_createPurchaseOrder($chid, $pcno, $sku)
{
    global $CRMEN;
    global $crmAccountId;
    global $crmAccountName;

    try {

        $pdb = NanoDB::connect();
        $customerOrder = RSLR_getAutoOrderNo();

        $sql_sku = "select skuName,skuRef,ppid,description,skuPrice from " . $GLOBALS['PREFIX'] . "agent.skuMaster where skuRef=?";
        $pdo = $pdb->prepare($sql_sku);
        $pdo->execute([$sku]);
        $res_sku = $pdo->fetch(PDO::FETCH_ASSOC);


        $skuNum = $res_sku['skuRef'];
        $skuDesc = $res_sku['skuName'];
        $dt = time();
        $NH_price = $res_sku['skuPrice'];
        $total1 = $pcno * $NH_price;
        $total = $total1;
        $noOfDays = 367;
        $curDate = date("Y-m-d H:i:s");
        $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");
        $sql_ser = "insert into " . $GLOBALS['PREFIX'] . "agent.orderDetails set "
            . "chnl_id=?, orderNum=?,skuNum=?,"
            . "skuDesc=?, licenseCnt=?, installCnt = '0' , purchaseDate=?, "
            . "orderDate=?,contractEndDate=?, noofDays=?, payRefNum = '', "
            . "transRefNum = '', amount = ?, status = '1',nh_lic='1',trial = '0'";

        $pdo = $pdb->prepare($sql_ser);
        $result = $pdo->execute([$chid, $customerOrder, $skuNum, $skuDesc, $pcno, $dt, $dt, $contractEnd, $noOfDays, $total]);

        if ($result) {

            if ($CRMEN == 1) {
                $orderDate = date("Y-m-d");
                $orderEnd = Date("Y-m-d", strtotime("+$noOfDays days"));
                $parameters1 = array(
                    array("name" => "sku_type_c", "value" => "new"),
                    array("name" => "country_c", "value" => "USA"),
                    array("name" => "tax_c", "value" => "0"),
                    array("name" => "order_id_c", "value" => $customerOrder),
                    array("name" => "quantity_c", "value" => $pcno),
                    array("name" => "total_amount_c", "value" => $total),
                    array("name" => "gross_amount_c", "value" => $total),
                    array("name" => "order_start_date_c", "value" => $orderDate),
                    array("name" => "order_end_date_c", "value" => $orderEnd),
                    array("name" => "download_link_c", "value" => ""),
                    array("name" => "invoice_number_c", "value" => ""),
                    array("name" => "order_status_c", "value" => "Pending"),
                    array("name" => "payment_status_c", "value" => "Pending"),
                    array("name" => "sku_name_c", "value" => "professional"),
                    array("name" => "account_name", "value" => $crmAccountName),
                    array("name" => "account_id", "value" => $crmAccountId),
                );

                $sql_insCnt = "select id,emailId,chId,crmUserId,crmLeadId,mauticId,downloadCnt,installCnt from " . $GLOBALS['PREFIX'] . "agent.contactDetails where chId=? limit 1";
                $pdo = $pdb->prepare($sql_insCnt);
                $pdo->execute([$chid]);
                $res_DnlCnt = $pdo->fetch(PDO::FETCH_ASSOC);

                if (safe_count($res_DnlCnt) > 0) {
                    $contactId = $res_DnlCnt['crmUserId'];
                    RSLR_pushOrderCRM($contactId, $parameters1);
                    RSLR_updateTrialStat($contactId);
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function RSLR_getAutoOrderNo()
{
    $ordernum = rand(1000000, 9999999999);
    $cm_query = "select chnl_id,orderNum,skuNum from orderDetails where orderNum=?";
    $pdb = NanoDB::connect();
    $pdo = $pdb->prepare($cm_query);
    $pdo->execute([$ordernum]);
    $res_cm = $pdo->fetch(PDO::FETCH_ASSOC);

    $count = safe_count($res_cm);
    if ($count > 0) {
        return RSLR_getAutoOrderNo();
    } else {
        return $ordernum;
    }
}

function RSLR_AddPaySignupUser($name, $userName, $userLastName, $userEmail, $userPhone, $eid, $plan, $purchaseDt, $quantity, $payprice, $payTotal)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $base_url;
    global $rootURL;
    global $signupPassUrl;

    $pdb = NanoDB::connect();

    $cId = $_SESSION["user"]["cId"];
    $user_type = $_SESSION["user"]["customerType"];
    $cksum = md5(mt_rand());
    $name = preg_replace('/\s+/', '_', $name);
    try {
        $sql_core = "select userid,username from " . $GLOBALS['PREFIX'] . "core.Users where (username=? or user_email=?) limit 1";
        $pdo = $pdb->prepare($sql_core);
        $pdo->execute([$name, $userEmail]);
        $res_core = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $roleId = USER_UserRoleWithCtype_PDO('', $pdb, 4);
            $resetId = USER_DownloadId_PDO('', $pdb);
            $sql_user = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,role_id,username,firstName,lastName, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
            VALUES ( ?,?,?,?,?,'',?,?,'0','', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, ?, '', 0, '', '', 95, '', '',?)";

            $pdo = $pdb->prepare($sql_user);
            $result_user = $pdo->execute([$eid, $roleId, $name, $userName, $userLastName, $userEmail, $userPhone, $cksum, $resetId]);

            $adminId = $pdb->lastInsertId();
            if ($result_user) {

                $sql_usrck = "insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username=?,level='1',cksum=?";
                $pdo = $pdb->prepare($sql_usrck);
                $result_ck = $pdo->execute([$name, $cksum]);


                $resetLink = $signupPassUrl . '?vid=' . $resetId;
                RSLR_PurchaseSendMail($userName, $userEmail, $resetLink, $plan, $purchaseDt, $quantity, $payprice, $payTotal);

                return 'Reseller added successfully.';
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function RSLR_AddSignupUser($name, $userName, $userLastName, $userEmail, $userPhone, $eid, $ctype, $signupsource, $language)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $db;
    global $base_url;
    global $rootURL;
    global $signupPassUrl;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "core", $db);

    $cId = $_SESSION["user"]["cId"];
    $user_type = $_SESSION["user"]["customerType"];
    $cksum = md5(mt_rand());
    $name = preg_replace('/\s+/', '_', $name);
    try {
        $sql_core = "select userid,username from " . $GLOBALS['PREFIX'] . "core.Users where (username='$name' or user_email = '$userEmail') limit 1";
        $res_core = find_one($sql_core, $db);
        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $roleId = USER_UserRoleWithCtype('', $db, 5);
            $resetId = USER_DownloadId('', $db);
            $sql_user = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,role_id,username,firstName,lastName, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
            VALUES ( '$eid','$roleId','" . $name . "','" . $userName . "','" . $userLastName . "','','" . $userEmail . "','" . $userPhone . "','0','', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '','$resetId')";
            $result_user = redcommand($sql_user, $db);

            $adminId = mysqli_insert_id();
            if ($result_user) {

                if ($ctype == 1) {

                    $upd_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.entity_id = CONCAT(U.entity_id, ',',$eid)  where U.role_id=O.id and O.name = 'user_superadmin' and U.ch_id='$cId'";
                    $result_enty = redcommand($upd_enty, $db);
                } else if ($ctype == 2) {

                    $chnl_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.channel_id = CONCAT(U.channel_id, ',', $eid) where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'";
                    $chnl_result = redcommand($chnl_admin, $db);

                    $upd_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.channel_id = CONCAT(U.channel_id, ',',$eid)  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id='$cId'";
                    $result_chnl = redcommand($upd_chnl, $db);
                } else if ($ctype == 3) {

                    $sub_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',', $eid) where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'";
                    $sub_result = redcommand($sub_admin, $db);

                    $upd_sub1 = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',',$eid)  where U.role_id=O.id and O.name = 'user_superadmin' and U.ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=2)";
                    $result_sub1 = redcommand($upd_sub1, $db);

                    $upd_sub2 = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',',$eid)  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id='$cId'";
                    $result_sub2 = redcommand($upd_sub2, $db);
                } else if ($ctype == 5) {

                    $cust_admin = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',', $eid) where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name in ('user_superadmin')";
                    $result_admin = redcommand($cust_admin, $db);

                    if ($user_type == 1) {

                        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id='$cId'";
                        $result_cust = redcommand($cust_enty, $db);
                    } else if ($user_type == 2) {

                        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin','user_reselleradmin') and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=2)";
                        $result_cust = redcommand($cust_enty, $db);

                        $cust_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin','user_reselleradmin') and ch_id='$cId'";
                        $result_chnl = redcommand($cust_chnl, $db);
                    } else if ($user_type == 3) {



                        $cust_enty = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin',,'user_reselleradmin') and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=2)";
                        $result_cust = redcommand($cust_enty, $db);

                        $cust_chnl = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  where U.role_id=O.id and O.name in ('user_superadmin','user_channeladmin',,'user_reselleradmin') and ch_id in (select C.channelId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid='$cId' and C.ctype=3)";
                        $result_chnl = redcommand($cust_chnl, $db);

                        $cust_sub = "update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',',$eid)  where U.role_id=O.id and O.name = 'user_superadmin' and  ch_id='$cId'";
                        $result_sub = redcommand($cust_sub, $db);
                    }
                }

                $sql_usrck = "insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $name . "',level='1',cksum='" . $cksum . "'";
                $result_ck = redcommand($sql_usrck, $db);

                if ($signupsource == "dashboard") {
                    $resetLink = $base_url . 'reset-password.php?vid=' . $resetId;
                } else if ($signupsource == "website") {
                    $planid = '';
                    if (isset($_SESSION['user']['webplanid'])) {
                        $planid = $_SESSION['user']['webplanid'];
                    }
                    if ($planid != '') {
                        unset($_SESSION['user']['webplanid']);
                        $resetLink = $signupPassUrl . '?vid=' . $resetId . '&planid=' . $planid;
                    } else {
                        $resetLink = $signupPassUrl . '?vid=' . $resetId;
                    }
                } else if ($signupsource == "crmsignup") {
                    $resetLink = $signupPassUrl . '?vid=' . $resetId;
                }

                if ($ctype == 2 || $ctype == 5) {
                    RSLR_PassSendMail($userName, $userEmail, $resetLink, $language);
                }

                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function RSLR_PurchaseSendMail($userName, $userEmail, $resetLink, $plan, $purchaseDt, $quantity, $payprice, $payTotal)
{

    global $base_url;
    $subject = "Order Details";
    $to = $userEmail;
    $from = getenv('SMTP_USER_LOGIN');

    $message = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="initial-scale=1.0"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge"/>
<meta name="format-detection" content="telephone=no"/>
<title>testpaymail</title>
<link href="../assets/css/family=OpenSans.css" rel="stylesheet" type="text/css">
<style type="text/css">

    /* Resets: see reset.css for details */
    .ReadMsgBody { width: 100%; background-color: #ffffff;}
    .ExternalClass {width: 100%; background-color: #ffffff;}
    .ExternalClass, .ExternalClass p, .ExternalClass span,
    .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
    #outlook a{ padding:0;}
    body{width: 100%; height: 100%; background-color: #ffffff; margin:0; padding:0;}
    body{ -webkit-text-size-adjust:none; -ms-text-size-adjust:none; }
    html{width:100%;}
    table {mso-table-lspace:0pt; mso-table-rspace:0pt; border-spacing:0;}
    table td {border-collapse:collapse;}
    table p{margin:0;}
    br, strong br, b br, em br, i br { line-height:100%; }
    div, p, a, li, td { -webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
    h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
    span a { text-decoration: none !important;}
    a{ text-decoration: none !important; }
    img{height: auto !important; line-height: 100%; outline: none; text-decoration: none;  -ms-interpolation-mode:bicubic;}
    .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited,
    .yshortcuts a:hover, .yshortcuts a span { text-decoration: none !important; border-bottom: none !important;}
    /*mailChimp class*/
    .default-edit-image{
    height:20px;
    }
    ul{padding-left:10px; margin:0;}
    .tpl-repeatblock {
    padding: 0px !important;
    border: 1px dotted rgba(0,0,0,0.2);
    }
    .tpl-content{
    padding:0px !important;
    }
    @media only screen and (max-width:800px){
    table[style*="max-width:800px"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
    table[style*="max-width:800px"] img{width:100% !important; height:auto !important; max-width:100% !important;}
    }
    @media only screen and (max-width: 640px){
    /* mobile setting */
    table[class="container"]{width:100%!important; max-width:100%!important; min-width:100%!important;
    padding-left:20px!important; padding-right:20px!important; text-align: center!important; clear: both;}
    td[class="container"]{width:100%!important; padding-left:20px!important; padding-right:20px!important; clear: both;}
    table[class="full-width"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
    table[class="full-width-center"] {width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    table[class="force-240-center"]{width:240px !important; clear: both; margin:0 auto; float:none;}
    table[class="auto-center"] {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"]{width: auto!important; max-width:75%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    table[class="col-3"],table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important;}
    table[class="col-2"]{width:47.3%!important; max-width:100%!important;}
    *[class="full-block"]{width:100% !important; display:block !important; clear: both; padding-top:10px; padding-bottom:10px;}
    /* image */
    td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important;}
    /* helper */
    table[class="space-w-20"]{width:3.57%!important; max-width:20px!important; min-width:3.5% !important;}
    table[class="space-w-20"] td:first-child{width:3.5%!important; max-width:20px!important; min-width:3.5% !important;}
    table[class="space-w-25"]{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
    table[class="space-w-25"] td:first-child{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
    table[class="space-w-30"] td:first-child{width:5.35%!important; max-width:30px!important; min-width:5.35% !important;}
    table[class="fix-w-20"]{width:20px!important; max-width:20px!important; min-width:20px!important;}
    table[class="fix-w-20"] td:first-child{width:20px!important; max-width:20px!important; min-width:20px !important;}
    *[class="h-10"]{display:block !important;  height:10px !important;}
    *[class="h-20"]{display:block !important;  height:20px !important;}
    *[class="h-30"]{display:block !important; height:30px !important;}
    *[class="h-40"]{display:block !important;  height:40px !important;}
    *[class="remove-640"]{display:none !important;}
    *[class="text-left"]{text-align:left !important;}
    *[class="clear-pad"]{padding:0 !important;}
    }
    @media only screen and (max-width: 479px){
    /* mobile setting */
    table[class="container"]{width:100%!important; max-width:100%!important; min-width:124px!important;
    padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
    td[class="container"]{width:100%!important; padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
    table[class="full-width"],table[class="full-width-479"]{width:100%!important; max-width:100%!important; min-width:124px!important; clear: both;}
    table[class="full-width-center"] {width: 100%!important; max-width:100%!important; min-width:124px!important; text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"]{width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
    table[class="col-3"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
    table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important; }
    table[class="col-2"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
    *[class="full-block-479"]{display:block !important; width:100% !important; clear: both; padding-top:10px; padding-bottom:10px; }
    /* image */
    td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:124px !important;}
    td[class="image-min-80"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:80px !important;}
    td[class="image-min-100"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:100px !important;}
    /* halper */
    table[class="space-w-20"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-20"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-25"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-25"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-30"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
    table[class="space-w-30"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
    *[class="remove-479"]{display:none !important;}
    table[width="595"]{width:100% !important;}
    img{max-width:280px !important;}
    .resize-font, .resize-font *{
      font-size: 37px !important;
      line-height: 48px !important;
    }
    }
    td ul{list-style: initial; margin:0; padding-left:20px;}td ul{list-style: initial; margin:0; padding-left:20px;}body{background-color:#efefef;} .default-edit-image{height:20px;} tr.tpl-repeatblock , tr.tpl-repeatblock > td{ display:block !important;} .tpl-repeatblock {padding: 0px !important;border: 1px dotted rgba(0,0,0,0.2);} table[width="595"]{width:100% !important;}a img{ border: 0 !important;}
a:active{color:initial } a:visited{color:initial }
.tpl-content{padding:0 !important;}
.full-mb,*[fix="full-mb"]{width:100%!important;} .auto-mb,*[fix="auto-mb"]{width:auto!important;}
</style>
<!--[if gte mso 15]>
<style type="text/css">
a{text-decoration: none !important;}
body { font-size: 0; line-height: 0; }
tr { font-size:1px; mso-line-height-alt:0; mso-margin-top-alt:1px; }
table { font-size:1px; line-height:0; mso-margin-top-alt:1px; }
body,table,td,span,a,font{font-family: Arial, Helvetica, sans-serif !important;}
a img{ border: 0 !important;}
</style>
<![endif]-->
<!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
</head>
<body  style="font-size:12px; width:100%; height:100%;">
<table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #efefef; width: 800px; max-width: 800px; outline: rgb(239, 239, 239) solid 1px; box-shadow: rgb(224, 224, 224) 0px 0px 5px; margin: 0px auto;"><!-- START ORDER-TABLE --><tr><td valign="top" align="center" class="container" style="background-color: #ffffff; " bgcolor="#ffffff">
            <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="background-color: #ffffff; min-width: 600px; width: 600px; margin: 0px auto;"><tbody><tr><td valign="top" align="center">
                  <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start title --><tbody><tr dup="0"><td valign="top">
                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><!-- start space --><tbody><tr><td valign="top" height="30" style="height: 30px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                          </tr><!-- end space --><tr><td align="center" style="font-size: 24px; color: #333333; font-weight: bold; text-align: center; font-family: Open Sans, Arial, Helvetica, sans-serif; word-break: break-word; line-height: 32px;"><span style="text-decoration: none; line-height: 32px; font-size: 24px; font-weight: bold; font-family: "Open Sans", Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif"><a href="#" style="color: #333333; text-decoration: none !important; border-style: none; line-height: 32px; font-size: 24px; font-weight: bold; font-family: "Open Sans", Arial, Helvetica, sans-serif;" data-mce-href="#" border="0"><font face="Open Sans, Arial, Helvetica, sans-serif">&nbsp;</font></a><font color="#9db1c5" style="font-size: 24px; font-weight: bold; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Order Details</font></font></font></span></td>
                          </tr><!-- start space --><tr><td valign="top" height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                            </td>
                          </tr><!-- end space --></tbody></table></td>
                    </tr><!-- end title --><tr dup="0"><td valign="top" align="center">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="width: 560px; margin: 0px auto;"><!-- start space --><tbody><tr><td valign="top" height="15" style="height: 15px; font-size: 0px; line-height: 0; border-collapse: collapse;">
                          </td>
                        </tr><!-- end space --><tr><td valign="top">
                              <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color: #e1edf4; margin: 0px auto;"><tbody><tr dup="0"><td valign="top" height="25" width="100" style="font-size: 14px; color: #333333; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; width: 100px; line-height: 22px;" align="left"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Product Plan : </font></span></td>
                                <td valign="top" style="background-color:#fafafa; border: 1px solid #e1edf4;" bgcolor="#fafafa">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="height: 100%; margin: 0px auto;"><tbody><tr><td valign="top" style="font-size: 14px; color: #888888; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; line-height: 22px;" align="left">
                                       ' . $plan . '
                                      </td>
                                    </tr></tbody></table></td>
                              </tr><tr dup="0"><td valign="top" height="25" width="100" style="font-size: 14px; color: #333333; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; width: 100px; line-height: 22px;" align="left"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Purchase date&nbsp;: </font></span></td>
                                <td valign="top" style="background-color:#fafafa; border: 1px solid #e1edf4;" bgcolor="#fafafa">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="height: 100%; margin: 0px auto;"><tbody><tr><td valign="top" style="font-size: 14px; color: #888888; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; line-height: 22px;" align="left">
                                        ' . $purchaseDt . '
                                      </td>
                                    </tr></tbody></table></td>
                              </tr><tr dup="0"><td valign="top" height="25" width="100" style="font-size: 14px; color: #333333; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; width: 100px; line-height: 22px;" align="left"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Quantity : </font></span></td>
                                <td valign="top" style="background-color:#fafafa; border: 1px solid #e1edf4;" bgcolor="#fafafa">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="height: 100%; margin: 0px auto;"><tbody><tr><td valign="top" style="font-size: 14px; color: #888888; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; line-height: 22px;" align="left">
                                        ' . $quantity . '
                                      </td>
                                    </tr></tbody></table></td>
                              </tr><tr dup="0"><td valign="top" height="25" width="100" style="font-size: 14px; color: #333333; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; width: 100px; line-height: 22px;" align="left"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Price per quantity : </font></span></td>
                                <td valign="top" style="background-color:#fafafa; border: 1px solid #e1edf4;" bgcolor="#fafafa">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="height: 100%; margin: 0px auto;"><tbody><tr><td valign="top" style="font-size: 14px; color: #888888; font-weight: normal; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; line-height: 22px;" align="left">
                                        ' . $payprice . '
                                      </td>
                                    </tr></tbody></table></td>
                              </tr><tr dup="0"><td valign="top" height="25" width="100" style="font-size: 16px; color: #333333; font-weight: bold; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; width: 100px; line-height: 24px;" align="left">
                                    Order total :
                                </td>
                                <td valign="top" style="background-color:#fafafa; border: 1px solid #e1edf4;" bgcolor="#fafafa">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="height: 100%; margin: 0px auto;"><tbody><tr><td valign="top" style="font-size: 16px; color: #333333; font-weight: bold; text-align: left; font-family: Open Sans, Arial, Helvetica, sans-serif; padding: 15px; word-break: break-word; line-height: 24px;" align="left">
                                        ' . $payTotal . '
                                      </td>
                                    </tr></tbody></table></td>
                              </tr></tbody></table></td>
                          </tr><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                          </tr><!-- end space --></tbody></table></td>
                    </tr><tr dup="0"><td valign="top" align="center" class="clear-pad" style="padding-left:20px; padding-right:20px;">
                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr dup="0"><td valign="top" align="center">
                              <table width="95%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td align="center" style="font-size: 14px; color: #888888; font-weight: normal; text-align: center; font-family: Open Sans, Arial, Helvetica, sans-serif; word-break: break-word; line-height: 22px;"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Please click on Continue button to access NH Dashboard</font></span></td>
                                </tr><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                </tr><!-- end space --></tbody></table></td>
                          </tr><tr><td valign="top" align="center" class="clear-pad" style="padding-left:20px; padding-right:20px;">
                              <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><!--start button--><tbody><tr><td valign="top" align="center">
                                    <table width="auto" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0px auto;"><tbody><tr><!-- start duplicate button --><td valign="top" class="full-block-479" dup="0">
                                          <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin: 0px auto;"><tbody><tr><td valign="top" style="padding-top:5px; padding-bottom:5px; padding-left:5px; padding-right:5px;">
                                                <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="border-radius: 5px; background-color: #6ab451; margin: 0px auto;"><tbody><tr><td width="auto" align="center" valign="middle" height="45" style="font-size: 14px; color: #ffffff; font-weight: normal; text-align: center; font-family: Open Sans, Arial, Helvetica, sans-serif; background-clip: padding-box; padding-left: 25px; padding-right: 25px; word-break: break-word; line-height: 22px;"><a href="' . $resetLink . '"><span style="line-height: 22px; font-size: 14px; font-weight: 400; font-family: Open Sans, Arial, Helvetica, sans-serif;"><font face="Open Sans, Arial, Helvetica, sans-serif">Continue</font></span></a></td>
                                                  </tr></tbody></table></td>
                                            </tr></tbody></table></td>
                                        <!-- end duplicate button -->
                                      </tr></tbody></table></td>
                                </tr><!--end button--></tbody></table></td>
                          </tr><!-- start space --><tr><td valign="top" height="30" style="height: 30px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                          </tr><!-- end space --></tbody></table></td>
                    </tr></tbody></table></td>
              </tr></tbody></table></td>
        </tr><!-- END ORDER-TABLE --></table></body>
</html>';

    if (send_mail($to, $subject, $message, $headers)) {
        return 1;
    } else {
        return 0;
    }
}



function RSLR_PassSendMail($userName, $userEmail, $resetLink, $language)
{

    if ($language == "undefined" || $language == "") {
        $language = "en";
    }
    $selectedLang = isset($_SESSION['localization']) ? $_SESSION['localization'] : "en";
    $db = NanoDB::connect();
    global $base_url;
    $fromEmailId = getenv('SMTP_USER_LOGIN');

    $select_template = $db->prepare("SELECT mailTemplate, subjectline FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype= ?  AND language= ? LIMIT ? ");
    $select_template->execute([10, $selectedLang, 1]);
    $res_template = $select_template->fetchAll(PDO::FETCH_ASSOC);

    $message = $res_template['mailTemplate'];
    $subject = $res_template['subjectline'];

    $NHimage = $base_url . '/vendors/images/20161103171845_nanoheal_logo.png';
    $NHFinalimage = $base_url . '/vendors/images/20161027170825_nanoheal_logo_final.png';
    $Picture1 = $base_url . '/vendors/images/20161103171453_Picture1.png';
    $facebookImg = $base_url . '/vendors/images/set13-social-facebook-gray.png';
    $twitterImg = $base_url . '/vendors/images/set13-social-twitter-gray.png';
    $resetLink = $resetLink . "&lng=" . $selectedLang . "";

    $forgotpassword = $base_url . 'forgot-password.php';

    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('NANANOHEAL_FINAL', $NHFinalimage, $message);
    $message = str_replace('PICTURE1', $Picture1, $message);
    $message = str_replace('FACEBOOK_SOCIAL', $facebookImg, $message);
    $message = str_replace('PASSURL', $resetLink, $message);
    $message = str_replace('TWITTER_SOCIAL', $twitterImg, $message);
    $message = str_replace('FORGOTPASSWORD', $forgotpassword, $message);

    $headers = "";
    $headers .= "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";

    if (!mail($userEmail, $subject, $message, $headers)) {
        return 0;
    } else {
        return 1;
    }
}

function RSLR_Entity_Dtl($id)
{
    $db = NanoDB::connect();
    $sql_ch = $db->prepare("select firstName, lastName, emailId, companyName, entityId, channelId, reportserver from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $sql_ch->execute([$id]);
    $res_core = $sql_ch->fetch(PDO::FETCH_ASSOC);
    return $res_core;
}

function RSLR_GetAviraDetails($key, $db, $sid)
{
    $avira_sql = "select S.serviceTag,A.* from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S," . $GLOBALS['PREFIX'] . "agent.aviraDetails A where S.aviraId=A.id and S.sid=?";
    $pdo = $db->prepare($avira_sql);
    $pdo->execute(array($sid));
    $avira_res = $pdo->fetchAll(PDO::FETCH_ASSOC);

    return $avira_res;
}


function RSLR_GetAviraDetailsById($key, $db, $aviraId)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        try {
            $avira_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.aviraDetails WHERE id='$aviraId' LIMIT 1";
            $avira_res = find_one($avira_sql, $db);

            return $avira_res;
        } catch (Exception $exc) {
            logs::log(__FILE__, __LINE__, $exc, 0);
        }
    } else {
    }
}

function RSLS_IsAviraInstalled($key, $pdb, $sid)
{
    $pdb = NanoDB::connect();
    $avira_sql = "SELECT aviraId FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S  WHERE S.sid=? LIMIT 1";
    $pdo = $pdb->prepare($avira_sql);
    $pdo->execute([$sid]);
    $avira_res = $pdo->fetch(PDO::FETCH_ASSOC);

    if ($avira_res['aviraId'] == '') {
        return 'false';
    } else {
        return 'true';
    }
}



function RSLS_IsValidAddCustomer($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);
    $flag = '3';
    if ($key) {
        $sql = "SELECT contractEndDate, licenseCnt, installCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id = '$loggedEid' AND status=1 AND trial = '0'";
        $result = find_many($sql, $db);
        if (safe_count($result) > 0) {
            $today = time();
            foreach ($result as $value) {
                if ($value['contractEndDate'] > $today) {
                    if ($value['licenseCnt'] > $value['installCnt']) {
                        $flag = '3';
                    } else {
                        $flag = '2';
                    }
                } else {
                    $flag = '2';
                }
            }
        } else {
            $flag = '1';
        }
        return $flag;
    } else {
        return "Your key has expired";
    }
}


function RSLR_AviraOtcDetails($db, $cid, $otcId)
{
    $countArray = [];
    $licenseSql = "SELECT otcCode, productName, licenseCnt, usedLicense, contractEndDate, licenseKey, used, pending FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE ch_id='$cid' AND ( id='$otcId' OR licenseKey = '$otcId') LIMIT 1";
    $licenseRes = find_one($licenseSql, $db);

    if ($licenseRes['licenseKey'] != "") {
        $licenseKey = $licenseRes['licenseKey'];

        $pccountSql = "SELECT sum(noOfPc) pcCnt FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE licenseKey='" . $licenseKey . "'";
        $pccountRes = find_one($pccountSql, $db);

        $pendingCnt = $licenseRes['licenseCnt'] - $pccountRes['pcCnt'];

        $countArray['pending'] = $licenseRes['pending'];
        $countArray['licenses'] = $licenseRes['licenseCnt'];
        $countArray['contractEndDate'] = $licenseRes['contractEndDate'];
        $countArray['pccount'] = $licenseRes['used'];
        $countArray['otcCode'] = $licenseRes['otcCode'];
    }
    return $countArray;
}


function RSLR_GetOTCDetails($db, $cid, $otcId)
{
    $customerType = $_SESSION['user']['customerType'];

    if ($customerType == 2) {
        $licenseSql = "SELECT id, ch_id, emailId, companyName, licenseKey, licenseCnt, otcCode, productName, contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE ch_id=? AND ( id=? OR licenseKey=? OR otcCode=?) LIMIT 1";
        $binds = array($cid, $otcId, $otcId, $otcId);
    } else {
        $licenseSql = "SELECT id, ch_id, emailId, companyName, licenseKey, licenseCnt, otcCode, productName, contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE ( id='$otcId' OR licenseKey = '$otcId' OR otcCode='$otcId') LIMIT 1";
        $binds = array($otcId, $otcId, $otcId);
    }

    $pdo = $db->prepare($licenseSql);
    $pdo->execute($binds);
    $licenseRes = $pdo->fetch(PDO::FETCH_ASSOC);
    return $licenseRes;
}


function RSLR_GetAllOTC($db, $cid)
{
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE ch_id='$cid'";
    $result = find_many($sql, $db);
    return $result;
}


function RSLR_GetAviraLicenses($db, $columnname, $val)
{

    $sql = "SELECT emailId, companyname, licenseCnt FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE ?=? LIMIT 1";
    $pdo = $db->prepare($sql);
    $pdo->execute(array($columnname, $val));
    $result = $pdo->fetch(PDO::FETCH_ASSOC);

    return $result;
}


function RSLR_GetResellerAllOTC($db, $cid)
{
    $cust_type = $_SESSION["user"]["customerType"];
    if ($cust_type == 0 || $cust_type == 1) {
        $sql = "select A.*,L.firstName,C.siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.channel L," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where C.licenseKey=A.licenseKey and C.agentId=L.emailId and A.ch_id=L.eid group by A.otcCode;";
        $bind = '';
    } else {
        $sql = "select A.*,L.firstName,C.siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.channel L," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where C.licenseKey=A.licenseKey and C.agentId=L.emailId and A.ch_id=? group by A.otcCode;";
        $bind = array($cid);
    }

    $pdo = $db->prepare($sql);
    $pdo->execute($bind);
    $result = $pdo->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}


function RSLR_GetCustomersForOTCCode($db, $cid, $otcCode)
{
    $cust_type = $_SESSION["user"]["customerType"];

    if ($cust_type == 0 || $cust_type == 1) {
        $sql = "SELECT C.* FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.aviraLicenses A  WHERE C.licenseKey=A.licenseKey AND A.otcCode=?";
        $bind = array($otcCode);
    } else {
        $sql = "SELECT C.* FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.aviraLicenses A  WHERE C.licenseKey=A.licenseKey AND A.otcCode=? AND A.ch_id=?";
        $bind = array($otcCode, $cid);
    }

    $pdo = $db->prepare($sql);
    $pdo->execute($bind);
    $result = $pdo->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

function RSLR_EditAviraCustomer($db, $custNumber, $ordNumber, $pcCount)
{
    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET noOfPc='$pcCount' WHERE customerNum='$custNumber' AND orderNum = '$ordNumber'";
    $updateRes = redcommand($updateSql, $db);
    if ($updateRes) {
        return array("status" => 1, "message" => "<span>Customer updated successfully</span>");
    } else {
        return array("status" => 0, "message" => "<span>Customer update failed</span>");
    }
}

function RSLR_Get_OTCDetailsByOTC_Code($db, $otc_code)
{
    $sql = "SELECT id, ch_id, emailId, companyName, otcCode, licenseCnt, contractEndDate  FROM " . $GLOBALS['PREFIX'] . "agent.aviraLicenses WHERE otcCode = '$otc_code' LIMIT 1";
    $result = find_one($sql, $db);
    if (safe_count($result) > 0)
        return 1;
    else
        return 0;
}

function RSLR_Update_EditAviraLicense($db, $loggedEid, $OTCDetails)
{
    $otcCode = $OTCDetails['otcCode'];
    $prodName = $OTCDetails['productName'];
    $contractEndDate = $OTCDetails['contractEndDate'];
    $licenseKey = $OTCDetails['licenseKey'];
    $total_licenses = $OTCDetails['licenseCnt'];

    $used_sql = "SELECT SUM(noOfPc) used_count FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE licenseKey='" . $licenseKey . "' LIMIT 1";
    $used_res = find_one($used_sql, $db);
    $used_count = $used_res['used_count'];
    $pending_count = $total_licenses - $used_count;


    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET used = '$used_count',  pending = '$pending_count' WHERE otcCode = '$otcCode' AND  licenseKey = '$licenseKey'";
    $updateRes = redcommand($updateSql, $db);

    if ($updateRes)
        return TRUE;
    else
        return FALSE;
}

function RSLR_payPurchase($key, $fullname, $lname, $companyname, $emailid, $planid)
{
    $db = NanoDB::connect();
    $msg = '';
    $sql_ch = "select firstName,companyname,email from " . $GLOBALS['PREFIX'] . "agent.signup where (companyname = ? or email = ?) limit 1";

    $pdo = $db->prepare($sql_ch);
    $pdo->execute([$companyname, $emailid]);
    $res_core = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res_core) == 0) {
        $resetId = USER_DownloadId('', $db);
        $createdDate = time();
        $updateSql = "insert into " . $GLOBALS['PREFIX'] . "agent.signup SET firstName=?,lastName=?,companyname=?, email=?, createdDate=? ,vCode=?,sku=?";
        $pdo = $db->prepare($updateSql);
        $updateRes = $pdo->execute([$fullname, $lname, $companyname, $emailid, $createdDate, $resetId, $planid]);

        if ($updateRes) {
            $msg = $resetId;
        } else {
            $msg = 'Error';
        }
    } else {
        $cmpName = $res_core['companyname'];
        $email = $res_core['email'];
        if ($cmpName == $companyname) {
            $msg = 'CompanyDuplicate';
        }
        if ($email == $emailid) {
            $msg = 'EmailDuplicate';
        }
    }

    return $msg;
}

function RSLR_validateUsers($key, $db, $companyname, $emailid)
{
}

function RSLR_getSignupDtl($id)
{
    $sql_ch = "select firstName,companyname,email,id,sku,lastName from " . $GLOBALS['PREFIX'] . "agent.signup where vCode=? limit 1";
    $pdb = NanoDB::connect();
    $pdo = $pdb->prepare($sql_ch);
    $pdo->execute([$id]);
    $res_core = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($res_core) > 0) {
        return array("status" => "success", "data" => $res_core);
    } else {
        return array("status" => "error");
    }
}

function RSLR_getServiceTagStatus($db, $serviceTag)
{

    $sql_ch = "SELECT S.downloadStatus FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S WHERE S.serviceTag = '$serviceTag' LIMIT 1";
    $res_core = find_one($sql_ch, $db);

    if (safe_count($res_core) > 0) {

        if ($res_core['downloadStatus'] == 'EXE' || $res_core['downloadStatus'] == 'I')
            return 'Installed';
        else
            return 'Not Installed';
    } else {
        return 'Not Installed';
    }
}

function RSLR_ServiceTagDetais($db, $serviceTag)
{
    $sql_ch = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S WHERE S.serviceTag = '$serviceTag' AND S.downloadStatus = 'EXE' LIMIT 1";
    $res_core = find_one($sql_ch, $db);

    if (safe_count($res_core) > 0) {

        if ($res_core['downloadStatus'] == 'EXE' || $res_core['downloadStatus'] == 'I')
            return $res_core;
        else
            return array();
    } else {
        return array();
    }
}



function RSLR_SKUDetailsByName($key, $db, $skuName, $eid)
{
    $sku_sql = "SELECT chId, skuName, description, provCode, noOfDays, licenseCnt, skuRef, trial, degrdSku, renewDays, upgrade"
        . " FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE (skuName = '$skuName' OR description = '$skuName') and chId = '$eid'  LIMIT 1";
    $sku_res = find_one($sku_sql, $db);
    if (safe_count($sku_res) > 0) {
        return $sku_res;
    } else {
        return array();
    }
}

function RSLR_VerifyOTC($db, $otcCode, $emailid, $companyName)
{

    $exist_res = RSLR_Get_OTCDetailsByOTC_Code($db, $otcCode);
    if ($exist_res == 1) {
        $valLic = array("licsCnt" => 0, "licsKey" => "", "used" => 0, "status" => "DUPLICATE");
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
            $result = curl_exec($ch);
            curl_close($ch);
            $json = safe_json_decode($result);

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
                $contracEndDate = strtotime($expire_date);

                $newEndDate = date("d/m/Y", $contracEndDate);
                $dt = time();
                if ($contracEndDate > $dt) {
                    $valLic = array("licsCnt" => $licsCnt, "licsKey" => $licsKey, "pendingCount" => $licsCnt, "contractEnds" => $newEndDate, "used" => 0, "status" => "SUCCESS");
                } else {
                    $valLic = array("licsCnt" => $licsCnt, "licsKey" => $licsKey, "pendingCount" => $licsCnt, "contractEnds" => $newEndDate, "used" => 0, "status" => "ERROR", "message" => "<span>Contract End Date is expired</span>");
                }
            } else {
                if ($avrmessage == null) {
                    $avrmessage = $json->error->message;
                }
                $valLic = array("licsCnt" => 0, "licsKey" => 0, "aviraid" => 0, "pendingCount" => 0, "contractEnds" => "", "used" => 0, "status" => "ERROR", "message" => $avrmessage);
            }
        } catch (Exception $ex) {
            logs::log(__FILE__, __LINE__, $ex, 0);
            return "Exception : " . $ex;
        }
    }
    return $valLic;
}

function RSLR_IsValueExist($db, $value)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $channel_sql = "SELECT eid FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE (companyName = '$value' OR emailId = '$value')";
        $channel_res = find_many($channel_sql, $db);

        $customer_sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE (coustomerFirstName = '$value' OR emailId = '$value')";
        $customer_res = find_many($customer_sql, $db);

        $user_sql = "SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE user_email = '$value'";
        $user_res = find_many($user_sql, $db);

        if ((safe_count($channel_res) > 0) || (safe_count($customer_res) > 0) || (safe_count($user_res) > 0)) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_IsSiteNameExist($pdo, $siteName)
{
    $res1 = preg_replace("/[^a-zA-Z0-9\s]/", "", $siteName);
    $siteName1 = preg_replace('/\s+/', '_', $res1);

    $stmt = $pdo->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder where (siteName = ? or siteName = ?)");
    $stmt->execute([$siteName1, $siteName]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (safe_count($res) > 0) {
        return "EXIST";
    } else {
        $cust_stmt = $pdo->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE customer = ? LIMIT 1");
        $cust_stmt->execute([$siteName1]);
        $customerRes = $cust_stmt->fetch(PDO::FETCH_ASSOC);
        if (safe_count($customerRes) > 0) {
            return "EXIST";
        } else {
            return "NOTEXIST";
        }
    }
}

function RSLR_GetMSPCustomers($key, $db, $loggedEid)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "SELECT C.customerNum, C.coustomerFirstName, C.coustomerLastName, C.emailId, S.status from " . $GLOBALS['PREFIX'] . "agent.customerOrder C ";
        $sql .= "left join " . $GLOBALS['PREFIX'] . "agent.channel S ON C.compId=S.eid where C.compId in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where ";
        $sql .= "channelId='$loggedEid') group by C.customerNum,C.orderNum ";

        $res = find_many($sql, $db);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function RSLR_GetChannelDetails($db)
{

    $customerType = $_SESSION["user"]["customerType"];
    $selectedId = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    if ($selectedId == '') {
        if ($customerType == 1) {
            $entityId = $_SESSION["user"]["cId"];
            $wh = "eid = '$entityId'";
        }
        if ($customerType == 2) {
            $channelId = $_SESSION["user"]["cId"];
            $wh = "eid = '$channelId'";
        }
        if ($customerType == 3) {
            $subchannelId = $_SESSION["user"]["cId"];
            $wh = "eid = '$subchannelId'";
        }

        if ($customerType == 4) {
            $outsourcedId = $_SESSION["user"]["cId"];
            $wh = "eid = '$outsourcedId'";
        }

        if ($customerType == 5) {
            $compId = $_SESSION["user"]["cId"];
            $wh = "eid = '$compId'";
        }
    } else if ($selectedId != '') {
        $wh = "eid = '$selectedId'";
    }
    $custQuery = "select eid,companyName,regNo,referenceNo,firstName,lastName,emailId,phoneNo,customerNo,reportserver,skulist,entityId,channelId,subchannelId,outsourcedId from " . $GLOBALS['PREFIX'] . "agent.channel where $wh";
    $res = find_one($custQuery, $db);

    return $res;
}


function RSLR_GetOTCDetailsFromAvira($db, $otcCode, $emailid, $companyName)
{

    $url = "https://license.avira.com/service/api";
    $username = 'nanoheal';
    $password = 'QZhPR5J7tsT2zqVG';
    $data_string = '{"jsonrpc":"2.0","method":"processActivationByKey","params":{"email":"' . $emailid . '","code":"' . $otcCode . '", "language":"en","company":"' . $companyName . '"},"id":2}';

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        $result = curl_exec($ch);
        curl_close($ch);
        $json = safe_json_decode($result);
        $jsonArray = safe_json_decode($result, TRUE);
        $msgStatus = $json->result->status;
        $avrmessage = $json->result->message;
        if ($msgStatus == 'success') {
            return $jsonArray;
        } else {
            if ($avrmessage == null) {
                $avrmessage = $json->error->message;
            }
            return $valLic = array("licsCnt" => 0, "licsKey" => 0, "aviraid" => 0, "pendingCount" => 0, "contractEnds" => "", "used" => 0, "status" => "ERROR", "message" => $avrmessage);
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
    return $valLic;
}


function RSLR_CreateOTCHistory($db, $otcOwnerDetails, $latestOTCDetails)
{
    $otcHistoryExist = RSLR_IsTableExist($db, "agent", "aviraLicensesHistory");
    $otcCode = $otcOwnerDetails['otcCode'];
    if (!$otcHistoryExist) {
        RSLR_CreateOTCHistoryTable($db);
    }
    RSLR_MakeOTCHistory($db, $otcCode, $otcOwnerDetails, $latestOTCDetails);
    return TRUE;
}


function RSLR_IsTableExist($db, $databasename, $tablename)
{

    try {
        if ($database !== '' && $tablename !== '') {
            $sql = "SELECT * FROM information_schema.tables
                    WHERE table_schema = '$databasename'
                    AND table_name = '$tablename'
                    LIMIT 1";
            $res = find_one($sql, $db);

            if (safe_count($res) > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        echo $exc->getTraceAsString();
    }
}


function RSLR_CreateOTCHistoryTable($db)
{
    $sql = "CREATE TABLE " . $GLOBALS['PREFIX'] . "agent.aviraLicensesHistory (
	id INT(11) NOT NULL AUTO_INCREMENT,
	ch_id INT(11) NOT NULL DEFAULT '0',
	emailId VARCHAR(60) NULL DEFAULT NULL,
	companyname VARCHAR(100) NULL DEFAULT NULL,
	otcCode VARCHAR(255) NULL DEFAULT NULL,
	productName VARCHAR(255) NULL DEFAULT NULL,
	licenseCnt INT(10) NOT NULL DEFAULT '0',
	usedLicense INT(10) NOT NULL DEFAULT '0',
	contractEndDate VARCHAR(50) NULL DEFAULT NULL,
	licenseKey TEXT NOT NULL,
	productId VARCHAR(50) NULL DEFAULT NULL,
	runtime INT(10) NULL DEFAULT NULL,
	status TINYINT(1) UNSIGNED ZEROFILL NOT NULL DEFAULT '1',
	used INT(10) NOT NULL DEFAULT '0' COMMENT 'this will vary on basis of customer addition',
	pending INT(10) NOT NULL DEFAULT '0' COMMENT 'this will vary on basis of customer addition',
	createdDate VARCHAR(25) NULL DEFAULT NULL,
	restricted ENUM('1','0') NULL DEFAULT '0',
	PRIMARY KEY (id)) COLLATE='latin1_swedish_ci' ENGINE=InnoDB";
    $res = redcommand($sql, $db);
    return TRUE;
}


function RSLR_MakeOTCHistory($db, $otcCode, $otcOwnerDetails, $latestOTCDetails)
{
    $channelId = $otcOwnerDetails['ch_id'];
    $emailid = $otcOwnerDetails['emailId'];
    $companyName = $otcOwnerDetails['companyName'];
    $product_name = $latestOTCDetails['product_name'];
    $licsCnt = $latestOTCDetails['users'];
    $activated_users = $latestOTCDetails['activated_users'];
    $expire_date = $latestOTCDetails['expire_date'];
    $licsKey = $latestOTCDetails['licenseKey'];
    $productId = $latestOTCDetails['product_id'];
    $runtime = $latestOTCDetails['runtime'];
    $status = $latestOTCDetails['status'];

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.aviraLicensesHistory (ch_id,emailId,companyname,otcCode,productName, licenseCnt, "
        . "usedLicense,contractEndDate,licenseKey,productId,runtime,status, used, pending, createdDate) VALUES "
        . "('$channelId','$emailid','$companyName','$otcCode', '$product_name','$licsCnt','$activated_users',"
        . "'$expire_date', '" . $licsKey . "','" . $productId . "', '$runtime','$status',0,'$licsCnt', '" . time() . "')";
    $res = redcommand($sql, $db);
    return TRUE;
}


function RSLR_IsOTCForSingleCustomer($db, $ch_id, $otcCode)
{
    $sql = "SELECT C.id, A.otcCode FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.aviraLicenses A WHERE C.licenseKey=A.licenseKey AND "
        . "A.otcCode = '$otcCode' AND A.ch_id = '$ch_id'";
    $res = find_many($sql, $db);
    if (safe_count($res) > 1) {
        return FALSE;
    } else {
        return TRUE;
    }
}


function RSLR_UpdateOTCLicenseCounts($db, $otcOwnerDetails, $latestOTCDetails)
{
    $latestLicenseCount = $latestOTCDetails['users'];
    $ch_id = $otcOwnerDetails['ch_id'];
    $otcCode = $otcOwnerDetails['otcCode'];
    $licenseKey = $latestOTCDetails['licenseKey'];
    $latestContractEDate = strtotime($latestOTCDetails['expire_date']);
    $isSingleCustomer = RSLR_IsOTCForSingleCustomer($db, $ch_id, $otcCode);

    if ($isSingleCustomer) {
        $sql1 = "UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A SET C.noOfPc='$latestLicenseCount', C.licenseKey='$licenseKey' WHERE C.licenseKey=A.licenseKey and A.otcCode='$otcCode'";
        $res1 = redcommand($sql1, $db);

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET licenseCnt='$latestLicenseCount', licenseKey='$licenseKey', used = $latestLicenseCount WHERE ch_id='$ch_id' AND otcCode = '$otcCode'";
        $res = redcommand($sql, $db);

        $sql2 = "update " . $GLOBALS['PREFIX'] . "agent.orderDetails C SET C.licenseCnt='$latestLicenseCount',C.contractEndDate='$latestContractEDate' where C.chnl_id='$ch_id' and C.aviraOtc='$otcCode'";
        $res2 = redcommand($sql2, $db);
    } else {
    }
}


function RSLR_UpdateOTCLicenseDates($db, $otcOwnerDetails, $latestOTCDetails)
{
    $latestLicenseCount = $latestOTCDetails['users'];
    $ch_id = $otcOwnerDetails['ch_id'];
    $otcCode = $otcOwnerDetails['otcCode'];
    $licenseKey = $latestOTCDetails['licenseKey'];
    $latestContractEDate = strtotime($latestOTCDetails['expire_date']);
    $isSingleCustomer = RSLR_IsOTCForSingleCustomer($db, $ch_id, $otcCode);
    if ($isSingleCustomer) {
        $sql1 = "select C.id, C.contractEndDate, C.sessionIni from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where C.licenseKey=A.licenseKey and A.otcCode='$otcCode' and A.ch_id='$ch_id' limit 1";
        $res1 = find_one($sql1, $db);

        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.aviraLicenses SET contractEndDate='" . $latestOTCDetails['expire_date'] . "', licenseKey='$licenseKey' WHERE ch_id='$ch_id' AND otcCode = '$otcCode'";
        $res = redcommand($sql, $db);
        if ($res) {
            if (safe_count($res1) > 0) {
                $id = $res1['id'];
                $contractEDate = date("m/d/Y", $res1['contractEndDate']);

                $sessionIni = $res1['sessionIni'];
                $needle = "UniDays=" . $contractEDate;
                $value = "UniDays=" . date("m/d/Y", $latestContractEDate);
                $finalIni = RSLR_AlterIniValues($sessionIni, $needle, $value);
                $sql2 = "UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET contractEndDate='$latestContractEDate', sessionIni = '$finalIni', licenseKey='$licenseKey' WHERE id='$id'";
                $res2 = redcommand($sql2, $db);
                if ($res2) {
                    RSLR_UpdateServiceTagIniDate($db, $otcOwnerDetails, $latestOTCDetails);
                }
            } else {
            }
        } else {
        }
    } else {
    }
}


function RSLR_UpdateServiceTagIniDate($db, $otcOwnerDetails, $latestOTCDetails)
{
    $licenseKey = $latestOTCDetails['licenseKey'];
    $ch_id = $otcOwnerDetails['ch_id'];
    $otcCode = $otcOwnerDetails['otcCode'];
    $sql = "SELECT S.sid, S.iniValues, S.uninstallDate,C.contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S, " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.aviraLicenses A where S.compId=C.compId AND S.processId=C.processId "
        . "AND S.customerNum=C.customerNum AND S.orderNum=C.orderNum AND C.licenseKey=A.licenseKey and A.otcCode='$otcCode'";
    $res = find_many($sql, $db);
    if (safe_count($res) > 0) {
        $latestContractEDate = strtotime($latestOTCDetails['expire_date']);
        foreach ($res as $key => $value) {
            $sid = $value['sid'];
            $iniValue = $value['iniValues'];

            $contractEDate = date("m/d/Y", $value['uninstallDate']);
            $needle = "UniDays=" . $contractEDate;
            $value = "UniDays=" . date("m/d/Y", $latestContractEDate);
            $unistallDt = $latestContractEDate;
            $finalIni = RSLR_AlterIniValues($iniValue, $needle, $value);
            RSLR_ChangeServiceTagIniValue($db, $sid, $unistallDt, $finalIni);
        }
    } else {
    }
}


function RSLR_ChangeServiceTagIniValue($db, $sid, $latestContractEDate, $iniValues)
{

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.serviceRequest SET uninstallDate = '$latestContractEDate', iniValues = '$iniValues' WHERE sid='$sid'";
    $res = redcommand($sql, $db);
    if ($res) {
        return TRUE;
    } else {
        return FALSE;
    }
}



function RSLR_AlterIniValues($iniValue, $needle, $value)
{
    if ($iniValue !== "") {
        $iniValue = str_replace($needle, trim($value), $iniValue);
    }

    return $iniValue;
}

function RSLR_ClientURLEmail($db, $userName, $userEmail, $url, $language)
{

    global $base_url;
    $fromEmailId = getenv('SMTP_USER_LOGIN');
    $select_template = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype='11' AND language='$language' LIMIT 1";
    $res_template = find_one($select_template, $db);

    $message = $res_template['mailTemplate'];
    $subject = $res_template['subjectline'];

    $message = str_replace('USERNAME', $userName, $message);
    $message = str_replace('URL', $url, $message);


    $headers .= "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";

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


//    if (!mail($userEmail, $subject, $message, $headers)) {
    if (!CURL::sendDataCurl($url, $arrayPost)) {
        return 0;
    } else {
        return 1;
    }
}

function RSLR_GetLicenseCount_PDO($key, $db, $loggedEid, $NH_lic)
{
    $key = DASH_ValidateKey($key);
    $compIds = '';

    if ($key) {

        $sql_Coust = "SELECT SUM(licenseCnt) lseCnt, SUM(installCnt) insCnt FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id=? and orderNum=? LIMIT 1";
        $pdo = $db->prepare($sql_Coust);
        $pdo->execute(array($loggedEid, $NH_lic));
        $res_Coust = $pdo->fetch(PDO::FETCH_ASSOC);

        if ($res_Coust['lseCnt'] != '') {
            $nextThirtyDays = strtotime("+30 days");
            $renew_sql = "SELECT COUNT(sid) as renewcount FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest S,customerOrder C WHERE C.customerNum=S.customerNum and C.orderNum=S.orderNum and C.nhOrderKey=? S.compId IN
                              (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where channelId = ? and ctype = '5')
                                AND S.uninstallDate <= ? AND S.orderStatus = NULL and S.downloadStatus='EXE' and revokeStatus='I' LIMIT 1";

            $pdo = $db->prepare($renew_sql);
            $pdo->execute(array($NH_lic, $loggedEid, $nextThirtyDays));
            $renew_res = $pdo->fetch(PDO::FETCH_ASSOC);

            $res_Coust['renewLicensesCnt'] = $renew_res['renewcount'];
            return $res_Coust;
        } else {
            return array("lseCnt" => 0, "insCnt" => 0, "renewLicensesCnt" => 0);
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetOrderDetailsGrid_PDO($key, $db, $loggedEid, $whereClause)
{
    $key = DASH_ValidateKey($key);
    $ctype = $_SESSION["user"]["customerType"];
    $bindings = '';

    if ($key) {
        if ($ctype == 0) {
            $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE  nh_lic='0' group by aviraOtc";
        } else if ($ctype == 1) {
            $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE  chnl_id in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where ctype='2') and nh_lic='0'";
        } else {
            if ($whereClause != '') {
                $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                    . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id=? and id=? limit 1";
                $bindings = array($loggedEid, $whereClause);
            } else {
                if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
                    $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                        . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id=? and nh_lic='0'";
                    $bindings = array($loggedEid);
                } else {
                    $sql = "SELECT aviraOtc, skuDesc, purchaseDate, orderNum, licenseCnt, installCnt, contractEndDate"
                        . ", noofDays, skuNum FROM " . $GLOBALS['PREFIX'] . "agent.orderDetails WHERE chnl_id=? and nh_lic='1'";
                    $bindings = array($loggedEid);
                }
            }
        }

        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetCustomerDevicesGrid_PDO($key, $db, $compId, $processId, $customerNum, $orderNum, $whereClause)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "select C.sid, C.serviceTag, C.orderStatus, C.uninstallDate, C.installationDate, C.machineOS, C.machineModelNum, C.customerNum, C.orderNum,C.gatewayMachine "
            . ", C.aviraId, C.downloadStatus, C.revokeStatus from " . $GLOBALS['PREFIX'] . "agent.serviceRequest C where  C.compId=? AND C.processId=?
          AND C.customerNum=? AND C.orderNum=? and C.revokeStatus='I' group by C.sessionid";

        $bindings = array($compId, $processId, $customerNum, $orderNum);
        $pdo = $db->prepare($sql);
        $pdo->execute($bindings);
        $res = $pdo->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetEntityDtls_PDO($key, $db, $cId)
{

    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT reportserver FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid=? LIMIT 1";
        $pdo = $db->prepare($sql);
        $pdo->execute(array($cId));
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_SKUDetails_PDO($key, $db, $skuId)
{

    $key = DASH_ValidateKey($key);
    $eid = $_SESSION['user']['cId'];
    if ($key) {
        $sku_sql = "SELECT provCode, noOfDays, licenseCnt, licensePeriod, description, skuName, skuRef, trial, degrdSku, renewDays, upgrade"
            . " FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE (id=? OR skuRef=?) and chId=?  LIMIT 1";
        $pdo = $db->prepare($sku_sql);
        $pdo->execute(array($skuId, $skuId, $eid));
        $sku_res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($sku_res) > 0) {
            return $sku_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function RSLR_GetDeviceDtls_PDO($key, $db, $deviceSid, $custNum, $ordNum)
{
    $key = DASH_ValidateKey($key);
    $array = array();
    if ($key) {
        $sql = "SELECT customerNum, serviceTag, machineManufacture, machineModelNum, macAddress, machineOS  "
            . "FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE sid=? and customerNum=? and orderNum=? and "
            . "downloadStatus='EXE' and revokeStatus='I' order by sid desc LIMIT 1";

        $pdo = $db->prepare($sql);
        $pdo->execute(array($deviceSid, $custNum, $ordNum));
        $res = $pdo->fetch(PDO::FETCH_ASSOC);

        if (safe_count($res)) {
            $array = $res;
        }
    } else {
        echo "Your key has been expired";
    }
    return $array;
}

function RSLR_AddProcess_PDO($key, $db, $cid, $name, $ctype)
{
    $key = DASH_ValidateKey($key);
    global $base_url;

    if ($key) {

        $pro_sql = $db->prepare("select S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type= ?  and S.name= ? limit ? ");
        $pro_sql->execute(['11', 'process_data', 1]);
        $res_pro = $pro_sql->fetchAll(PDO::FETCH_ASSOC);

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
        $termsCondition = $proList['termsCondition'];
        $defltProfile = $proList['defaultProfile'];
        $followOnDarts = $proList['followOnDarts'];
        $downLoadPath = $base_url . 'eula.php';


        $currentDate = time();

        if ($ctype == 5 || $ctype == '5') {
            $res1 = preg_replace("/[^a-zA-Z0-9\s]/", "", $name);
            $sitename = preg_replace('/\s+/', '_', $res1);
        } else {
            $sitename = '';
        }

        $process_result = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId= ?,processName = ?,metaSitename= ?,siteCode= ?,deployPath32 = ?,deployPath64 = ?,setupName32= ?,setupName64= ?,createdDate = ?,FtpConfUrl= ?,WsServerUrl= ?,dateCheck= ?,backupCheck= ?,sendMail=? ,serverUrl= ?,downloaderPath= ? ,phoneNo= ?,chatLink= ?,privacyLink= ? ,status= ?,androidsetup= ?,macsetup= ?,profileName= ?,andProfileName= ?,macProfileName= ?,DPName= ?,termsConditionUrl= ?,folnDarts= ? ")->execute([$cid, $name, $sitename, $sitename, $deploy32bit, $deploy64bit, $setup32bit, $setup64bit, $currentDate, $ftpURL, $nodeURL, 1, 0, 0, '', $downLoadPath, $phoneNo, $chatLink, $privacyLink, 1, $androidSetup, $macSetup, 'profile', 'profile_android', 'profile_mac', $defltProfile, $termsCondition, $followOnDarts]);

        if ($process_result) {
            $processId = $db->lastInsertId();
        } else {
            $processId = 0;
        }

        return $processId;
    } else {
        echo "Your key has been expired";
    }
}
