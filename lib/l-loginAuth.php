<?php


function isUserLoggedIn($loginStatus)
{
    if ($loginStatus == 0) {
        $_SESSION["loguser"] = $username;
        $_SESSION["logpass"] = $pwd;
        return "LOGERR";
    }
}

function CreateLoginInfo($db)
{
    $db = db_connect();
    $user_name = url::requestToAny('username');
    $pwd = url::requestToAny('pwd');
    $login_time = time();
    $ip = $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.login_info (username, login_time, sessionid, ip) VALUES (?,?,?,?)";
    $data_array = array($user_name, $login_time, session_id(), $ip);
    $res = db_set_rows($db, $sql, $data_array);
    return $res;
}

function getUserStatus($userId, $db)
{
    try {
        $umsg = 0;
        $lmsg = 0;
        $pmsg = 0;
        $currentTimestamp = time();
        $agentsql = "select userid,userStatus,loginStatus,passwordDate from " . $GLOBALS['PREFIX'] . "core.Users where (user_email = '$userId' or user_phone_no= '$userId')  limit 1";
        $resultStatus = find_one($agentsql, $db);

        $userstatus = $resultStatus['userStatus'];
        $loginStatus = $resultStatus['loginStatus'];
        $passwordDate = $resultStatus['passwordDate'];
        if ($userstatus == 0) {
            $umsg = 0;
        } else {
            $umsg = 1;
        }
        if ($loginStatus == 1) {
            $lmsg = 0;
        } else {
            $lmsg = 1;
        }

        $numDays = ($passwordDate - $currentTimestamp) / 24 / 60 / 60;

        if ($numDays <= 0) {
            $pmsg = 0;
        } else {
            $pmsg = 1;
        }

        return $umsg . '##' . $lmsg . '##' . $pmsg;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function updateUserLoginStatus($userSession, $username, $db)
{
    try {
        $unixdate = time();
        $sql = "update " . $GLOBALS['PREFIX'] . "core.Users set userSession=?,loginStatus=? where (user_email=? or user_phone_no = ?)";
        $data_array = array($userSession, 1, $username, $username);
        $res = db_set_rows($db, $sql, $data_array);
        return true;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function setCompanyDetailsSessions($companyDetailArray)
{
    $customerType = isset($companyDetailArray['ctype']) ? $companyDetailArray['ctype'] : "";
    $busslevel = isset($companyDetailArray['businessLevel']) ? $companyDetailArray['businessLevel'] : "";
    $entyHirearchy = isset($companyDetailArray['entyHirearchy']) ? $companyDetailArray['entyHirearchy'] : "";
    $coporateNo = isset($companyDetailArray['customerNo']) ? $companyDetailArray['customerNo'] : "";
    $addcustomer = isset($companyDetailArray['addcustomer']) ? $companyDetailArray['addcustomer'] : "";
    $skulist = isset($companyDetailArray['skulist']) ? $companyDetailArray['skulist'] : "";
    $eId = isset($companyDetailArray['eid']) ? $companyDetailArray['eid'] : "";
    $pid = isset($companyDetailArray['pId']) ? $companyDetailArray['pId'] : "";
    $companyName = isset($companyDetailArray['companyName']) ? $companyDetailArray['companyName'] : "";
    $lnxProfileName = isset($companyDetailArray['lnxProfileName']) ? $companyDetailArray['lnxProfileName'] : "";
    $iosProfileName = isset($companyDetailArray['iosProfileName']) ? $companyDetailArray['iosProfileName'] : "";
    $macProfileName = isset($companyDetailArray['macProfileName']) ? $companyDetailArray['macProfileName'] : "";
    $andProfileName = isset($companyDetailArray['andProfileName']) ? $companyDetailArray['andProfileName'] : "";
    $profileName = isset($companyDetailArray['profileName']) ? $companyDetailArray['profileName'] : "";
    $proccessName = isset($companyDetailArray['processName']) ? $companyDetailArray['processName'] : "";
    $logo = isset($companyDetailArray['logo']) ? $companyDetailArray['logo'] : "";

    if ($customerType == 1) {
        $entityId = $eId;
    } else {
        $entityId = $companyDetailArray['entityId'];
    }

    if ($customerType == 2) {
        $channelId = $eId;
    } else {
        $channelId = $companyDetailArray['channelId'];
    }

    if ($customerType == 3) {
        $subchannelId = $eId;
    } else {
        $subchannelId = $companyDetailArray['subchannelId'];
    }

    if ($customerType == 4) {
        $outsourcedId = $eId;
    } else {
        $outsourcedId = $companyDetailArray['outsourcedId'];
    }

    $_SESSION["user"]["skulist"] = $skulist;
    $_SESSION["user"]["entityId"] = $entityId;
    $_SESSION["user"]["channelId"] = $channelId;
    $_SESSION["user"]["subchannelId"] = $subchannelId;
    $_SESSION["user"]["outsourcedId"] = $outsourcedId;
    $_SESSION["user"]["customerType"] = $customerType;
    $_SESSION["user"]["pid"] = $pid;
    $_SESSION["user"]["companyName"] = $companyName;
    $_SESSION["user"]["logo"] = $logo;
    $_SESSION["user"]["processName"] = $proccessName;
    $_SESSION["user"]["profileName"] = $profileName;
    $_SESSION["user"]["andprofileName"] = $andProfileName;
    $_SESSION["user"]["macprofileName"] = $macProfileName;
    $_SESSION["user"]["iosprofileName"] = $iosProfileName;
    $_SESSION["user"]["lnxprofileName"] = $lnxProfileName;
    $_SESSION["user"]["busslevel"] = $busslevel;
    $_SESSION["user"]["entyHirearchy"] = $entyHirearchy;
    $_SESSION["user"]["addcustomer"] = $addcustomer;
    $_SESSION["user"]["coporateNo"] = $coporateNo;
    return true;
}

function getChannelDtl($cid, $db)
{
    try {
        if ($cid != "") {
            $channel_sql = "select C.eid,C.entityId,C.channelId,C.subchannelId,C.outsourcedId,C.companyName,C.firstName,C.lastName,C.emailId,C.phoneNo,"
                . "C.businessLevel,C.ordergen,C.skulist,C.reportserver,C.addcustomer,C.logo,C.ctype,C.customerNo,C.entyHirearchy,P.pId,P.profileName,"
                . "P.andProfileName,P.macProfileName,P.iosProfileName,P.lnxProfileName,P.logoName,P.processName,P.downloaderPath from " . $GLOBALS['PREFIX'] . "agent.channel C,"
                . "processMaster P where C.eid='$cid' and C.eid=P.cId limit 1";

            $channel_res = find_one($channel_sql, $db);
            return $channel_res;
        } else {
            return array();
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}


function getAdminSites($adminid, $db)
{

    $siteSql = "select C.customer from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username = U.username and U.userid = '$adminid'  group by C.customer";
    $resultSite = find_many($siteSql, $db);

    $agent_sites = array();
    foreach ($resultSite as $row) {
        $agent_sites[] = "'" . $row['customer'] . "'";
    }
    $sitesCount = safe_count($agent_sites);
    $returnDate = array($sitesCount, $agent_sites);
    return $returnDate;
}

function get_sitelist($user_sites, $db, $adminid)
{

    $sites = implode(",", $user_sites);
    $where = " customer in ($sites) group by customer";

    $siteList = array();
    try {

        $siteQuery = "select customer as name from Customers where $where";
        $siteListdata = find_many($siteQuery, $db);

        foreach ($siteListdata as $value) {

            $siteList[$value['name']] = $value['name'];
        }

        return $siteList;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);

        return null;
    }
}
