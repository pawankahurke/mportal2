<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../include/common_functions.php';
include_once '../lib/l-rocketChat.php';

global $pdo;
$pdo = pdo_connect();

function get_select_field()
{

    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("select F.id,F.fid,F.sub,F.displayName,F.value,F.description,F.provCode,F.period from " . $GLOBALS['PREFIX'] . "agent.fieldValues F ," . $GLOBALS['PREFIX'] . "agent.custSkuMaster CS where CS.skuId=F.id and CS.cId=? and CS.pid=?");
    $pdoQuery->execute([$cid, $pid]);
    $fld_res = $pdoQuery->fetchAll();

    $str = '';
    $i = 0;
    foreach ($fld_res as $value) {

        $str .= '<input type="checkbox" name="skudata" value="' . $value['id'] . '" class="skuVal"/>' . $value['description'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $i++;

        if (($i % 3) == 0) {

            $str .= '<br />';
        }
    }
    return $str;
}

function getSKUfield($swd)
{

    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("select skuList from " . $GLOBALS['PREFIX'] . "agent.serviceMaster where sId=? limit 1");
    $pdoQuery->execute([$swd]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skuList']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("select id,fid,sub,displayName,value,description,provCode,period from " . $GLOBALS['PREFIX'] . "agent.fieldValues where id IN (?)");
    $pdoQuery->execute([$idval]);
    $fld_res = $pdoQuery->fetchAll();

    $str = '';
    $i = 0;
    $str1 = "";
    foreach ($fld_res as $value) {

        $str .= "<option value='" . $value['id'] . "'>" . $value['description'] . "</option>";
    }
    $skuList = $str1 . $str;
    return $skuList;
}

function getSKUList($cid)
{
    global $pdo;

    $pId = getProcessByCompany($cid);

    $pdoQuery = $pdo->prepare("select F.id,F.fid,F.sub,F.displayName,F.value,F.description,F.provCode,F.period from " . $GLOBALS['PREFIX'] . "agent.fieldValues F ," . $GLOBALS['PREFIX'] . "agent.custSkuMaster CS where CS.skuId=F.id and F.fid=3 and CS.cId=? and CS.pid=? and CS.status=1");
    $pdoQuery->execute([$cid, $pId]);
    $fld_res = $pdoQuery->fetchAll();

    $count = safe_count($fld_res);
    $str = '';
    $str1 = '';

    $str1 = "<option value='0' selected>Please select SKU </option>";

    foreach ($fld_res as $value) {

        $str .= "<option value='" . $value['value'] . "'>" . $value['description'] . "</option>";
    }
    $skuList = $str1 . $str;

    return $skuList;
}

function getProSKUList($cid)
{
    $pdo = pdo_connect();
    $pdoQuery = $pdo->prepare("select eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("select id,skuRef,skuName from " . $GLOBALS['PREFIX'] . "agent.skuMaster where id in(?)");
    $pdoQuery->execute([$idval]);
    $resSKU = $pdoQuery->fetchAll();

    $str = '';

    $str1 = "<option value='0' selected>Please select SKU </option>";

    foreach ($resSKU as $value) {

        $str .= "<option value='" . $value['skuRef'] . "'>" . $value['skuName'] . "</option>";
    }
    $skuList = $str1 . $str;

    return $skuList;
}

function getProSKUList1($cid)
{

    $pdo = pdo_connect();
    $pdoQuery = $pdo->prepare("select eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("select id,skuRef,skuName from " . $GLOBALS['PREFIX'] . "agent.skuMaster where id in(" . $res_sku['skulist'] . ")");
    $pdoQuery->execute();
    $resSKU = $pdoQuery->fetchAll();

    $str = '';

    if (safe_count($resSKU) > 0) {
        $str .= "<option value=''>Select SKU</option>";
        foreach ($resSKU as $value) {

            $str .= "<option value='" . $value['skuRef'] . "'>" . $value['skuName'] . "</option>";
        }
    } else {
        $str = "<option value='' selected>No SKU Available for this user</option>";
    }

    return $str;
}

function getOutSourcedList()
{
    global $pdo;
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("SELECT eid,entityId,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE entityId = '$cid' and ctype = 4");
    $pdoQuery->execute();
    $res_outsrc = $pdoQuery->fetchAll();

    $str = "<option value='0' selected>Please select Outsource </option>";
    if (safe_count($res_outsrc) == 0) {
        $str = "<option value='0' selected>No Outsource available</option>";
    } else {
        foreach ($res_outsrc as $value) {
            $str .= "<option value='" . $value['eid'] . "'>" . $value['companyName'] . "</option>";
        }
    }
    return $str;
}

function getEntitySKUList($cid)
{

    global $pdo;

    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("SELECT eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid='$cid' limit 1");
    $pdoQuery->execute();
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("SELECT id,upgrdSku,renewSku,skuName,skuRef,ppid from " . $GLOBALS['PREFIX'] . "agent.skuMaster where id in($idval) and skuType='3'");
    $pdoQuery->execute();
    $fld_res = $pdoQuery->fetchAll();

    $count = safe_count($fld_res);
    $str = '';
    $str1 = '';

    $str1 = "<option value='' selected>Please select SKU </option>";

    foreach ($fld_res as $value) {

        $str .= "<option value='" . $value['id'] . "'>" . $value['skuName'] . "</option>";
    }
    $skuList = $str1 . $str;

    return $skuList;
}

function getSKUVal($cid)
{

    global $pdo;

    $pId = getProcessByCompany($cid);

    $pdoQuery = $pdo->prepare("SELECT F.id,F.fid,F.sub,F.displayName,F.value,F.description,F.provCode,F.period from " . $GLOBALS['PREFIX'] . "agent.fieldValues F ," . $GLOBALS['PREFIX'] . "agent.custSkuMaster CS where CS.skuId=F.id and F.fid=3 and CS.cId='$cid' and CS.pid='$pId' and CS.status=1");
    $pdoQuery->execute();
    $fld_res = $pdoQuery->fetchAll();

    $count = safe_count($fld_res);
    $str = '';
    $str1 = '';

    $str1 = "<option value='0' selected>Please select SKU </option>";

    foreach ($fld_res as $value) {

        $str .= "<option value='" . $value['id'] . "'>" . $value['description'] . "</option>";
    }
    $skuList = $str1 . $str;

    return $skuList;
}

function getSiteList()
{

    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("SELECT id,parameters,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where cId=? and pId=?");
    $pdoQuery->execute([$cid, $pid]);
    $site_res = $pdoQuery->fetchAll();

    $count = safe_count($site_res);
    $str = '';
    $str1 = '';
    if ($count > 1) {
        $str1 = "<option value='0' selected>Please select site </option>";
    }

    foreach ($site_res as $value) {

        $str .= "<option value='" . $value['id'] . "'>" . $value['sitename'] . "</option>";
    }
    $siteList = $str1 . $str;

    return $siteList;
}

function getProSiteList()
{

    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("SELECT id,parameters,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where cId=? and pId=?");
    $pdoQuery->execute([$cid, $pid]);
    $site_res = $pdoQuery->fetchAll();

    $count = safe_count($site_res);
    $str = '';
    $str1 = '';
    if ($count > 1) {
        $str1 = "<option value='0' selected>Please select site </option>";
    }

    foreach ($site_res as $value) {

        $str .= "<option value='" . $value['sitename'] . "'>" . $value['sitename'] . "</option>";
    }
    $siteList = $str1 . $str;

    return $siteList;
}

function getCoreSiteList()
{
    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("SELECT C.companyName,C.eid from " . $GLOBALS['PREFIX'] . "agent.channel C  where C.eid=?");
    $pdoQuery->execute([$cid]);
    $chnl_res = $pdoQuery->fetch();
    $compName = preg_replace('/\s+/', '_', $chnl_res['companyName']);
    $pdoQuery = $pdo->prepare("SELECT C.customer from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Customers C where U.username=C.username and U.username=? group by C.customer");
    $pdoQuery->execute([$compName]);
    $site_res = $pdoQuery->fetchAll();

    $str = '';

    foreach ($site_res as $value) {

        $str .= '<option value="' . $value['customer'] . '" data-original-title="' . $value['customer'] . '" data-toggle="tooltip" title="' . $value['customer'] . '">' . $value['customer'] . '</option>';
    }
    $siteList = $str;

    return $siteList;
}

function getEditProSiteList($adminId)
{
    global $pdo;
    $ch_id = '';

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];
    $sites = array();
    $pdoQuery = $pdo->prepare("SELECT C.customer , U.ch_id from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Customers C where U.username=C.username and U.userid= ?");
    $pdoQuery->execute([$adminId]);
    $res = $pdoQuery->fetchAll();
    foreach ($res as $value) {
        $sites[] = $value['customer'];
        $ch_id = $value['ch_id'];
    }

    $pdoQuery = $pdo->prepare("SELECT C.customer from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Customers C where U.username=C.username and U.ch_id=? group by C.customer");
    $pdoQuery->execute([$ch_id]);
    $site_res = $pdoQuery->fetchAll();

    $count = safe_count($site_res);
    $str = '';
    $str1 = '';

    foreach ($site_res as $value) {

        if (in_array($value['customer'], $sites)) {
            $str .= "<option value='" . $value['customer'] . "' selected>" . $value['customer'] . "</option>";
        } else {
            $str .= "<option value='" . $value['customer'] . "'>" . $value['customer'] . "</option>";
        }
    }
    $siteList = $str1 . $str;

    return $siteList;
}

function getSiteByid()
{
    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];
    $siteid = url::issetInRequest('siteid') ? url::requestToText('siteid') : '';

    $pdoQuery = $pdo->prepare("SELECT id,parameters,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where cId=? and pId=?");
    $pdoQuery->execute([$cid, $pid]);
    $site_res = $pdoQuery->fetchAll();

    $count = safe_count($site_res);
    $str = '';
    $str1 = '';
    if ($count > 1) {
        $str1 = "<option value='0'>Please select site </option>";
    }

    foreach ($site_res as $value) {

        if ($value['id'] == $siteid) {

            $str .= "<option value='" . $value['sitename'] . "' selected>" . $value['sitename'] . "</option>";
        } else {
            $str .= "<option value='" . $value['sitename'] . "'>" . $value['sitename'] . "</option>";
        }
    }
    $siteList = $str1 . $str;

    return $siteList;
}

function addProcess()
{

    global $pdo;

    $pdo = pdo_connect();

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $processName = url::issetInPost('processName') ? url::postToText('processName') : '';
    $phoneNo = url::issetInPost('phoneNo') ? url::postToText('phoneNo') : '';
    $chatLink = url::issetInPost('chatLink') ? url::postToText('chatLink') : '';
    $privacyLink = url::issetInPost('privacyLink') ? url::postToText('privacyLink') : '';
    $currentDate = time();

    if ($processName != '') {

        try {
            $pdoQuery = $pdo->prepare("SELECT processName,serverId from " . $GLOBALS['PREFIX'] . "agent.processMaster where processName='" . $processName . "'");
            $pdoQuery->execute();
            $res_ser = $pdoQuery->fetch();
            if (safe_count($res_ser) > 0) {
                echo 'Process already exists.';
            } else {

                $pdoQuery = $pdo->prepare("SELECT DbIp,serverId,serverUrl,deployPath32,deployPath64,setupName32,setupName64,logoName,folnDarts,profileName,downloaderPath from " . $GLOBALS['PREFIX'] . "agent.processMaster where pId=? and cId=?");
                $pdoQuery->execute([$pid, $cid]);
                $res_pro = $pdoQuery->fetch();

                $dbIp = $res_pro['DbIp'];
                $serverId = $res_pro['serverId'];
                $serverUrl = $res_pro['serverUrl'];
                $deployPath32 = $res_pro['deployPath32'];
                $deployPath64 = $res_pro['deployPath64'];
                $setupName32 = $res_pro['setupName32'];
                $setupName64 = $res_pro['setupName64'];
                $logoName = $res_pro['logoName'];
                $folnDarts = $res_pro['folnDarts'];
                $profileName = $res_pro['profileName'];
                $downloaderPath = $res_pro['downloaderPath'];

                $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET processName = '" . $processName . "',DbIp='" . $dbIp . "',serverId='" . $serverId . "',serverUrl='" . $serverUrl . "',deployPath32='" . $deployPath32 . "',deployPath64='" . $deployPath64 . "',setupName32='" . $setupName32 . "',setupName64='" . $setupName64 . "',folnDarts='" . $folnDarts . "',profileName='" . $profileName . "',logoName='" . $logoName . "',createdDate = '" . $currentDate . "',dateCheck=1,backupCheck=0,sendMail=0,downloaderPath='" . $downloaderPath . "',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "'");
                $process_sql->execute();
                $process_result = $pdo->lastInsertId();

                if ($process_result) {
                    $insertId = mysqli_insert_id();
                    $process_list = getProcessList($insertId);
                    echo 'Process details added.##' . $process_list;
                } else {
                    echo 'This Process details already registred with us.';
                }
            }
        } catch (Exception $exc) {
            logs::log(__FILE__, __LINE__, $exc, 0);
            echo 'Error occured to add process.';
        }
    } else {
        echo 'Variable missing to add record Process.';
    }
}

function getProcessList($insertId)
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT pId,description,processName from " . $GLOBALS['PREFIX'] . "agent.processMaster where pId=?");
    $pdoQuery->execute([$insertId]);
    $res = $pdoQuery->fetchAll();

    $str = "<select style='height: 35px;width: 56.8%;padding: 5px;margin-top: 15px;' id='proVal' name='proVal'>";
    foreach ($res as $value) {

        $str .= "<option value='" . $value['pId'] . "' selected>" . $value['processName'] . "</option>";
    }

    $str .= '</select>';

    return $str;
}

function getServerList($insertId)
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT serverid,servername from " . $GLOBALS['PREFIX'] . "install.Servers");
    $pdoQuery->execute();
    $res = $pdoQuery->fetchAll();
    $str = "<select style='height: 35px;width: 56.8%;padding: 5px;margin-top: 15px;' id='serVal' name='serVal'>";
    foreach ($res as $value) {

        if ($insertId == $value['sId']) {
            $str .= "<option value='" . $value['serverid'] . "' selected>" . $value['servername'] . "</option>";
        } else {
            $str .= "<option value='" . $value['serverid'] . "'>" . $value['servername'] . "</option>";
        }
    }
    $str .= '</select>';
    return $str;
}

function addSite()
{

    $pdo = pdo_connect();

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];
    $admin = $_SESSION["user"]["username"];
    $adminid = $_SESSION["user"]["adminid"];
    $siteName = url::issetInRequest('siteName') ? url::requestToText('siteName') : '';
    $skuid = url::issetInRequest('skuid') ? url::requestToText('skuid') : '';

    $pdoQuery = $pdo->prepare("SELECT count(sid) insCnt from (select sid from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.compId in(select eid from " . $GLOBALS['PREFIX'] . "agent.channel where channelId='$cid' and ctype=5) and revokeStatus = 'I' group by S.customerNum,S.orderNum,S.serviceTag) as X");
    $pdoQuery->execute();
    $install_licenseRes = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT sum(licenseCnt) total from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id=?");
    $pdoQuery->execute([$cid]);
    $total_licenseRes = $pdoQuery->fetch();

    if ($install_licenseRes['insCnt'] >= $total_licenseRes['total']) {
        echo "You don't have enough licenses, please buy more";
    } else {
        $pdoQuery = $pdo->prepare("SELECT parameters,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where (sitename='" . $siteName . "' OR parameters='" . $siteName . "')");
        $pdoQuery->execute();
        $res_site = $pdoQuery->fetch();

        if (safe_count($res_site) == 0) {

            $pdoQuery = $pdo->prepare("SELECT DbIp,deployPath32,deployPath64,setupName32,setupName64,downloaderPath,profileName,FtpConfUrl,WsServerUrl,serverId from " . $GLOBALS['PREFIX'] . "agent.processMaster where pId = ? limit 1");
            $pdoQuery->execute([$pid]);
            $res_proDtl = $pdoQuery->fetch();

            $dbIp = $res_proDtl['DbIp'];
            $startDate = time();
            $endDate = strtotime('+367 day', time());
            $REPORT_URL = 'https://' . $dbIp . ':443/main/rpc/rpc.php';

            $reg_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.RegCode SET cId=?,pId=?,parameters='" . $siteName . "',startDate= '" . $startDate . "',endDate='" . $endDate . "',url='" . $REPORT_URL . "',sitename='" . $siteName . "'");
            $reg_sql->execute([$cid, $pid]);

            $reg_result = $pdo->lastInsertId();

            if ($reg_result) {
                $siteid = mysqli_insert_id();

                $pdoQuery = $pdo->prepare("SELECT adminId from " . $GLOBALS['PREFIX'] . "agent.Agent A where A.swId IN (select  S.sId from " . $GLOBALS['PREFIX'] . "agent.customerMaster C,serviceMaster S where C.swId=S.sId and C.cId= ?) limit 1");
                $pdoQuery->execute([$cid]);
                $res_sp = $pdoQuery->fetch();

                $coreId = $res_sp['adminId'];

                $pdoQuery = $pdo->prepare("SELECT U.username from " . $GLOBALS['PREFIX'] . "core.Users U  where U.userid= ?");
                $pdoQuery->execute([$coreId]);
                $res_core = $pdoQuery->fetch();
                $spName = $res_core['username'];

                $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $admin . "', customer = '" . $siteName . "', sitefilter = '0', owner = '1', notify_sender = ''");
                $ins_customer->execute();
                $cust_result = $pdo->lastInsertId();

                $ins_serProvider = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $spName . "', customer = '" . $siteName . "', sitefilter = '0', owner = '0', notify_sender = ''");
                $ins_serProvider->execute();
                $serProvider_result = $pdo->lastInsertId();

                echo "Site created successfully.##" . $siteid;
            } else {
                echo "Error to create site name.##0";
            }
        } else {
            echo 'site name already exists.';
        }
    }
}

function getSiteByCust($siteId)
{
    global $pdo;

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];

    $pdoQuery = $pdo->prepare("SELECT id,parameters,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where cId=? and pId=?");
    $pdoQuery->execute([$cid, $pid]);
    $site_res = $pdoQuery->fetchAll();

    $count = safe_count($site_res);
    $str = '';
    $str1 = '';
    if ($count > 1) {
        $str1 = "<option value='0'>Please select site </option>";
    }

    foreach ($site_res as $value) {
        if ($siteId == $value['id']) {
            $str .= "<option value='" . $value['id'] . "' selected>" . $value['sitename'] . "</option>";
        } else {
            $str .= "<option value='" . $value['id'] . "'>" . $value['sitename'] . "</option>";
        }
    }
    $siteList = $str1 . $str;

    echo $siteList;
}

function addSKU()
{

    global $pdo;

    $pdo = pdo_connect();

    $skuName = url::issetInPost('skuName') ? url::postToText('skuName') : '';
    $conPeriod = url::issetInPost('conPeriod') ? url::postToText('conPeriod') : '';
    $skuType = url::issetInPost('skuType') ? url::postToText('skuType') : '';
    $pcCount = url::issetInPost('pcCount') ? url::postToText('pcCount') : '';
    $skuDesc = $pcCount . 'PC,' . $conPeriod;
    if ($conPeriod == '1Y') {
        $skuPeriod = 1;
    } else {
        $skuPeriod = 0;
    }

    if ($conPeriod != '') {

        if ($conPeriod == '1Y') {
            $noOfDays = 365;
        } elseif ($conPeriod == '3D') {
            $noOfDays = 3;
        } elseif ($conPeriod == '30D') {
            $noOfDays = 30;
        } elseif ($conPeriod == '6M') {
            $noOfDays = 184;
        } elseif ($conPeriod == '3M') {
            $noOfDays = 94;
        }
    }

    $pieces = explode("_", $skuType);
    $fid = $pieces[1];
    $proCode = $pieces[0];

    $pdoQuery = $pdo->prepare("SELECT max(value) as skuValue from " . $GLOBALS['PREFIX'] . "agent.fieldValues");
    $pdoQuery->execute();
    $select_result = $pdoQuery->fetch();
    $skuValue = explode("-", $select_result['skuValue']);
    $sku_value = $skuValue[0] . '-' . ($skuValue[1] + 1);

    $squ_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.fieldValues SET fid = '" . $fid . "',sub = '0',value='" . $sku_value . "',displayName = '" . $skuName . "',description = '" . $skuDesc . "',provCode = '" . $proCode . "',period = '" . $skuPeriod . "',noOfDays='" . $noOfDays . "',noPC='" . $pcCount . "'");
    $squ_sql->execute();
    $squ_result = $pdo->lastInsertId();
    if ($squ_result) {
        $sku_list = get_select_field();
        echo 'SKU details added.##' . $sku_list;
    } else {
        echo 'This SKU details already registred with us.';
    }
}

function addCustomer()
{
    global $pdo;

    $pdo = pdo_connect();

    $pid = $_SESSION["user"]["pid"];
    $cid = $_SESSION["user"]["cId"];
    $swId = $_SESSION["user"]["swId"];
    $crmId = $_SESSION['user']['adminid'];

    $companyName = url::issetInRequest('companyName') ? url::requestToText('companyName') : '';
    $firstName = url::issetInRequest('firstName') ? url::requestToText('firstName') : '';
    $lastName = url::issetInRequest('lastName') ? url::requestToText('lastName') : '';
    $emailId = url::issetInRequest('emailId') ? url::requestToText('emailId') : '';
    $contactNo = url::issetInRequest('contactNo') ? url::requestToText('contactNo') : '';
    $processId = url::issetInRequest('processId') ? url::requestToText('processId') : '';
    $skuVal = url::issetInRequest('skuValues') ? url::requestToText('skuValues') : '';
    $managerId = url::issetInRequest('managerId') ? url::requestToText('managerId') : '';
    $addDevice = url::issetInRequest('addDevice') ? url::requestToText('addDevice') : '';
    $entitle = url::issetInRequest('entitle') ? url::requestToText('entitle') : '';

    $currentDate = time();
    $loginBy = 'email';
    $pcCount = 0;

    if ($companyName != '' && $firstName != '' && $emailId != '') {

        try {

            $pdoQuery = $pdo->prepare("SELECT  sId,companyName,custmerNo,orderNo,showEntitlement,addDevice,customerType from " . $GLOBALS['PREFIX'] . "agent.serviceMaster where sId=?");
            $pdoQuery->execute([$swId]);
            $res_ser = $pdoQuery->fetch();
            $serviceProvide = $res_ser['companyName'];

            $siteName = $serviceProvide . '_' . $companyName;

            $adminId = addCoreUser($companyName, $emailId, $emailId, $siteName);

            if ($adminId != 0) {

                $pdoQuery = $pdo->prepare("SELECT cId,companyName,contactPerson,emailId from " . $GLOBALS['PREFIX'] . "agent.customerMaster where companyName='" . $companyName . "' or emailId='" . $emailId . "' limit 1");
                $pdoQuery->execute();
                $res_comp = $pdoQuery->fetch();

                if (safe_count($res_comp) > 0) {

                    if ($companyName == $res_comp['companyName']) {
                        return 'Company name already exists.';
                    } elseif ($emailId == $res_comp['emailId']) {
                        return 'Email id already exists.';
                    }
                } else {

                    $showCustNo = $res_ser['custmerNo'];
                    $showOrdNo = $res_ser['orderNo'];
                    $customerType = $res_ser['customerType'];
                    $addSite = 0;
                    $addCust = 0;

                    $cust_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerMaster SET swId = ?, crmId = ?, companyName = '" . $companyName . "',contactPerson = '" . $firstName . "',emailId = '" . $emailId . "',serverLabel = '" . $companyName . "',loginBy = '" . $loginBy . "',createdDate = '" . $currentDate . "',status=1,pcCount='.$pcCount.',showCustNo='.$showCustNo.',showOrdNo='.$showOrdNo.',addDevice='.$addDevice.',addSite='.$addSite.',addCust='.$addCust.',entitlement='.$entitle.',customerType=?");
                    $cust_sql->execute([$swId, $crmId, $customerType]);
                    $cust_result = $pdo->lastInsertId();

                    if ($cust_result) {
                        $customerId = mysqli_insert_id();
                        $year = date("Y");
                        $custNo = $year . '000' . $customerId;
                        $updateCust = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.customerMaster set customerNo=? where cId=?");
                        $updateCust->execute([$custNo, $customerId]);
                        $cust_update = $pdo->lastInsertId();

                        $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster where pId=? limit 1");
                        $pdoQuery->execute([$processId]);
                        $res_process = $pdoQuery->fetch();

                        $startDate = time();

                        $add_pro = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId=?,processName='" . $companyName . "', description='" . $res_process["description"] . "', siteCode='" . $companyName . "', metaSiteName='" . $companyName . "', createdDate='$startDate', DbIp='" . $res_process["DbIp"] . "', DbPassword='" . $res_process["DbPassword"] . "', serverId='" . $res_process["serverId"] . "', siteCreate='" . $res_process["siteCreate"] . "', dateCheck='" . $res_process["dateCheck"] . "', backupCheck='" . $res_process["backupCheck"] . "', sendMail='" . $res_process["sendMail"] . "', serverUrl='" . $res_process["serverUrl"] . "', phoneNo='" . $res_process["phoneNo"] . "', replyEmailId='" . $res_process["replyEmailId"] . "', chatLink='" . $res_process["chatLink"] . "', serviceLink='" . $res_process["serviceLink"] . "', privacyLink='" . $res_process["privacyLink"] . "', variation='" . $res_process["variation"] . "', locale='" . $res_process["locale"] . "', videoUrl='" . $res_process["videoUrl"] . "', fromName='" . $res_process["fromName"] . "', SWLangCode='" . $res_process["SWLangCode"] . "', subjectLine='" . $res_process["subjectLine"] . "', welComeMail='" . $res_process["welComeMail"] . "', deployPath32='" . $res_process["deployPath32"] . "', deployPath64='" . $res_process["deployPath64"] . "', setupName32='" . $res_process["setupName32"] . "', setupName64='" . $res_process["setupName64"] . "', androidsetup='" . $res_process["androidsetup"] . "', downloaderPath='" . $res_process["downloaderPath"] . "', logoName='" . $res_process["logoName"] . "', folnDarts='" . $res_process["folnDarts"] . "', downType='" . $res_process["downType"] . "', downlrName='" . $res_process["downlrName"] . "', profileName='" . $res_process["profileName"] . "', FtpConfUrl='" . $res_process["FtpConfUrl"] . "', WsServerUrl='" . $res_process["WsServerUrl"] . "', status='1', profileExt='" . $res_process["profileExt"] . "'");
                        $add_pro->execute([$customerId]);
                        $pro_result = $pdo->lastInsertId();
                        $proId = mysqli_insert_id();

                        $endDate = strtotime('+367 day', time());
                        $REPORT_URL = 'https://' . $res_process["DbIp"] . ':443/main/rpc/rpc.php';

                        $reg_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.RegCode SET cId='.$customerId.',pId='.$proId.',parameters='" . $siteName . "',startDate= '" . $startDate . "',endDate='" . $endDate . "',url='" . $REPORT_URL . "',sitename='" . $siteName . "'");
                        $reg_sql->execute();
                        $reg_result = $pdo->lastInsertId();
                        $sitId = mysqli_insert_id();

                        $status = 1;
                        $skuvalues = explode(",", $skuVal);
                        foreach ($skuvalues as $skuId) {

                            $pdoQuery = $pdo->prepare("SELECT noPC from " . $GLOBALS['PREFIX'] . "agent.fieldValues where id=?");
                            $pdoQuery->execute([$skuId]);
                            $res = $pdoQuery->fetch();
                            $pcCount = $res['noPC'];
                            $sql[] = '(' . $customerId . ', ' . $proId . ',' . $skuId . ',' . $pcCount . ',' . $sitId . ',' . time() . ',' . $status . ')';
                        }

                        $sqlQry = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.custSkuMaster (cId, pId, skuId, pcNo, siteId,createdDate, status) VALUES " . implode(',', $sql));
                        $sqlQry->execute();
                        $result = $pdo->lastInsertId();

                        $passid = getPasswordId1();

                        $agent_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.Agent SET adminId=?,cid = ?,swId=0,pid = ?,first_name = '" . $firstName . "',email = '" . $emailId . "',userType='2',userStatus=1,provisionAccess=1,dashboardAccess=1,role_id=?,addDevice=1,addSite=0,addCust=0,entitlement=1,resetsession='" . $passid . "',resetPassid='1',customerId=?");
                        $agent_sql->execute([$adminId, $customerId, $proId, $adminId, $customerId]);
                        $agent_result = $pdo->lastInsertId();

                        if ($agent_result) {

                            if ($managerId != '') {

                                $updatePdo = $pdo->prepare("update Agent SET customerId = CONCAT(customerId,',', $customerId) WHERE id IN (?)");
                                $updatePdo->execute([$managerId]);
                                $agent_result = $pdo->lastInsertId();
                            }

                            $manage_agent = $pdo->prepare("insert into manageAgent SET cid=?,pid=?,managersId='" . $emailId . "',Name= '" . $firstName . "',importOption=1,cancelOption=1,usageOption=1,admin=1");
                            $manage_agent->execute([$customerId, $processId]);
                            $agent_result = $pdo->lastInsertId();
                            echo 'Process completed.';
                            sendNewUserEmail($firstName, $emailId, $passid, $customerId);
                        } else {
                            echo ' ';
                        }
                    } else {
                        echo 'Please try later.';
                    }
                }
                if ($cust_result != 0 && $adminId != 0) {
                    echo ' Customer created successfully##2';
                }
            } else {
                echo 'Company (Customer) name already registered';
            }
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    }
}

function checkAgentEmail()
{
    global $pdo;

    $pdo = pdo_connect();

    $emailid = url::issetInRequest('email') ? url::requestToText('email') : '';

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.Agent where email = '" . $emailid . "'");
    $pdoQuery->execute();
    $sqlRes = $pdoQuery->fetch();

    if (safe_count($sqlRes) > 0) {
        echo 'EXIST';
    } else {
        echo 'NO';
    }
}

function getRegenarateRevoke($customerNo, $orderNo, $cid_val)
{

    $pdo = pdo_connect();

    $cid = $_SESSION["user"]["cId"];
    $pId = getProcessByCompany($cid_val);
    if ($orderNo != '') {
        $pdoQuery = $pdo->prepare("SELECT CO.customerNum,CO.orderNum,CO.coustomerFirstName customerFirstName,CO.coustomerLastName customerLastName,CO.noOfPc,CO.SKUNum,CO.SKUDesc,CO.provCode,DATE_FORMAT(FROM_UNIXTIME(CO.orderDate), '%m/%d/%Y') orderDate, DATE_FORMAT(FROM_UNIXTIME(CO.contractEndDate), '%m/%d/%Y') contractEndDate,CO.contractEndDate contractEDate,CO.emailId,CO.siteName siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder CO where CO.customerNum = '" . $customerNo . "' and CO.orderNum = '" . $orderNo . "' and CO.processId = ?");
    } else {
        $pdoQuery = $pdo->prepare("SELECT CO.customerNum,CO.orderNum,CO.coustomerFirstName customerFirstName,CO.coustomerLastName customerLastName,CO.noOfPc,CO.SKUNum,CO.SKUDesc,CO.provCode,DATE_FORMAT(FROM_UNIXTIME(CO.orderDate), '%m/%d/%Y') orderDate, DATE_FORMAT(FROM_UNIXTIME(CO.contractEndDate), '%m/%d/%Y') contractEndDate,CO.contractEndDate contractEDate,CO.emailId,CO.oldorderNum,CO.siteName siteName " . $GLOBALS['PREFIX'] . "agent.from " . $GLOBALS['PREFIX'] . "agent.customerOrder CO where CO.customerNum = '" . $customerNo . "' and CO.processId = ?");
    }
    $pdoQuery->execute([$pId]);
    $resOrd = $pdoQuery->fetchAll();

    if (safe_count($resOrd) > 0) {
        for ($j = 0; $j < safe_count($resOrd); $j++) {
            if (@$resOrd[$j]['oldorderNum'] != 0) {
                $oldOrdData[] = @$resOrd[$j]['oldorderNum'];
            }
        }

        $k = 0;
        $finAr = array();
        for ($i = 0; $i < safe_count($resOrd); $i++) {
            $cur_timestamp = time();
            $date_differnce = getDatesDifference($cur_timestamp, $resOrd[$i]['contractEDate']);

            $finAr[$k]['noOfPc'] = $resOrd[$i]['noOfPc'];
            $finAr[$k]['emailId'] = $resOrd[$i]['emailId'];
            $finAr[$k]['customerFirstName'] = $resOrd[$i]['customerFirstName'];
            $finAr[$k]['customerLastName'] = $resOrd[$i]['customerLastName'];
            $finAr[$k]['orderDate'] = $resOrd[$i]['orderDate'];
            $finAr[$k]['contractEndDate'] = $resOrd[$i]['contractEndDate'];
            $finAr[$k]['SKUDesc'] = $resOrd[$i]['SKUDesc'];
            $finAr[$k]['SKUNum'] = $resOrd[$i]['SKUNum'];
            $finAr[$k]['orderNum'] = $resOrd[$i]['orderNum'];
            $finAr[$k]['provCode'] = $resOrd[$i]['provCode'];
            $finAr[$k]['datediff'] = $date_differnce;
            $finAr[$k]['oldordAr'] = @$oldOrdData;
            $finAr[$k]['installedStatus'] = '';
            $finAr[$k]['insPcCount'] = '';
            $finAr[$k]['siteName'] = $resOrd[$i]['siteName'];
            $k++;
        }

        $finCnt = safe_count($finAr);

        $finStatusMsg = "%%DONE%%" . json_encode($finAr) . "%%" . $finCnt . "%%";
    } else {
        $finStatusMsg = "%%NOTDONE%%";
    }

    return $finStatusMsg;
}

function checkCustomeOrderDetailsById()
{

    $pdo = pdo_connect();
    $custNumber = url::issetInRequest('custNumber') ? url::requestToText('custNumber') : '';
    $custOrdMchVal = url::issetInRequest('custOrdMchVal') ? url::requestToText('custOrdMchVal') : '';
    $orderchk = url::issetInRequest('orderchk') ? url::requestToText('orderchk') : '0';

    $finStatusMsg = '';

    if ($orderchk == 1) {
        $searched_str = "," . $custOrdMchVal . ",";
        $pdoQuery = $pdo->prepare("SELECT CO.customerNum,CO.orderNum,CO.oldorderNum oldorderNum,CO.noOfPc noOfPc,CO.coustomerCountry coustomerCountry,CO.emailId emailId, DATE_FORMAT(FROM_UNIXTIME(CO.orderDate), '%m/%d/%Y') orderDate, DATE_FORMAT(FROM_UNIXTIME(CO.contractEndDate), '%m/%d/%Y') contractEndDate,CO.SKUDesc SKUDesc, CO.SKUNum SKUNum,CO.orderNum orderNum,CO.lineOfBusiness lineOfBusiness,CO.provCode provCode from " . $GLOBALS['PREFIX'] . "agent.customerOrder CO where (CO.customerNum != '" . $custNumber . "' and orderNum = '" . $custOrdMchVal . "') OR LOCATE(? , CO.oldorderNum)");
        $pdoQuery->execute([$searched_str]);
        $resCheck = $pdoQuery->fetchAll();
    } else {
        $resCheck = array();
    }

    if (safe_count($resCheck) > 0) {
        echo "%%EXISTS%%" . $resCheck[0]['customerNum'] . "%%" . $resCheck[0]['orderNum'] . "%%";
    } else {
        $pdoQuery = $pdo->prepare("SELECT CO.oldorderNum oldorderNum,CO.noOfPc noOfPc,CO.coustomerCountry coustomerCountry,CO.emailId emailId, DATE_FORMAT(FROM_UNIXTIME(CO.orderDate), '%m/%d/%Y') orderDate, DATE_FORMAT(FROM_UNIXTIME(CO.contractEndDate), '%m/%d/%Y') contractEndDate,CO.SKUDesc SKUDesc,CO.contractEndDate contractEDate, CO.SKUNum SKUNum,CO.orderNum orderNum,CO.lineOfBusiness lineOfBusiness,CO.provCode provCode from " . $GLOBALS['PREFIX'] . "agent.customerOrder CO where CO.customerNum = '" . $custNumber . "' and orderNum = '" . $custOrdMchVal . "'");
        $pdoQuery->execute();
        $resMch = $pdoQuery->fetch();

        $pdoQuery = $pdo->prepare("SELECT CO.oldorderNum oldorderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder CO where CO.customerNum = '" . $custNumber . "'");
        $pdoQuery->execute();
        $resOldOrd = $pdoQuery->fetchAll();

        $oldOrdData = array();
        if (safe_count($resOldOrd) > 0) {
            for ($j = 0; $j < safe_count($resOldOrd); $j++) {
                if ($resOldOrd[$j]['oldorderNum'] != 0) {
                    $oldOrdData[] = $resOldOrd[$j]['oldorderNum'];
                }
            }
            if (safe_count($oldOrdData) > 0) {
                $resMch['oldordAr'] = $oldOrdData;
            }
        }

        if (safe_count($resMch) > 0) {

            $cur_timestamp = time();
            $date_differnce = getDatesDifference($cur_timestamp, $resMch['contractEDate']);
            $resMch['datediff'] = $date_differnce;

            $finStatusMsg = "%%DONE%%" . json_encode($resMch) . "%%" . json_encode($resMch) . "%%";
        } else {
            $finStatusMsg = "%%NOTDONE%%%%";
        }
    }

    return $finStatusMsg;
}

function getCuesomerNoByCompany($cId)
{
    global $pdo;

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT swId,companyName,cId,customerNo from " . $GLOBALS['PREFIX'] . "agent.customerMaster where cId=?");
    $pdoQuery->execute([$cId]);
    $res_cm = $pdoQuery->fetch();
    return $res_cm['customerNo'];
}

function getAutoCustNo()
{

    global $pdo;

    $pdo = pdo_connect();

    $custnum = rand(1000000, 9999999999);

    $pdoQuery = $pdo->prepare("SELECT id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=?");
    $pdoQuery->execute([$custnum]);
    $res_cm = $pdoQuery->fetch();
    $count = safe_count($res_cm);
    if ($count > 0) {
        getAutoCustNo();
    } else {
        return $custnum;
    }
}

function getAutoOrderNo()
{
    global $pdo;

    $pdo = pdo_connect();
    $ordernum = rand(1000000, 9999999999);

    $pdoQuery = $pdo->prepare("SELECT id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where orderNum=? OR oldorderNum=?");
    $pdoQuery->execute([$ordernum, $ordernum]);
    $res_cm = $pdoQuery->fetch();
    $count = safe_count($res_cm);
    if ($count > 0) {
        getAutoOrderNo();
    } else {
        return $ordernum;
    }
}

function getDatesDifference($startTimestamp, $endTimestamp)
{

    $numDays = abs($startTimestamp - $endTimestamp) / 60 / 60 / 24;
    return $numDays;
}

function addServer()
{
    global $pdo;

    $pdo = pdo_connect();

    $serverName = url::issetInPost('serverName') ? url::postToAny('serverName') : '';
    $serURL = url::issetInPost('serURL') ? url::postToAny('serURL') : '';

    if ($serverName != '' && $serURL != '') {
        try {

            $pdoQuery = $pdo->prepare("SELECT serverid,servername from " . $GLOBALS['PREFIX'] . "install.Servers where url like '%" . $serURL . "%'");
            $pdoQuery->execute();
            $res_ser = $pdoQuery->fetch();
            if (safe_count($res_ser) > 0) {
                echo 'Server already exists.';
            } else {
                $cust_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "install.Servers SET servername = '" . $serverName . "',url='" . $serURL . "'");
                $cust_sql->execute();
                $cust_result = $pdo->lastInsertId();

                if ($cust_result) {
                    $insertId = mysqli_insert_id();
                    echo 'Server details added.';
                } else {
                    echo 'This server details already registred with us.';
                }
            }
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    } else {
        echo 'Variable missing to add record';
    }
}

function updateServer()
{

    global $pdo;

    $pdo = pdo_connect();

    $serverid = url::issetInPost('advserverid') ? url::postToAny('advserverid') : '';
    $serURL = url::issetInPost('serURL') ? url::postToAny('serURL') : '';

    if ($serverid != '' && $serURL != '') {
        try {

            $updatePdo = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "install.Servers SET url='" . $serURL . "' where  serverid=?");
            $updatePdo->execute([$serverid]);
            $cust_result = $pdo->lastInsertId();
            if ($cust_result) {
                echo 'Server details updated.';
            } else {
                echo 'This server details already registred with us.';
            }
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    } else {
        echo 'Variable missing to add record';
    }
}

function addAdvServer()
{

    global $pdo;

    $pdo = pdo_connect();

    $serverName = url::issetInPost('advserverName') ? url::postToAny('advserverName') : '';
    $serURL = url::issetInPost('advserURL') ? url::postToAny('advserURL') : '';
    $assetURL = url::issetInPost('assetURL') ? url::postToAny('assetURL') : '';
    $configURL = url::issetInPost('configURL') ? url::postToAny('configURL') : '';

    if ($serverName != '' && $serURL != '') {
        try {

            $pdoQuery = $pdo->prepare("SELECT serverid,servername from " . $GLOBALS['PREFIX'] . "install.Servers where servername = ?");
            $pdoQuery->execute([$serverName]);
            $res_ser = $pdoQuery->fetch();
            if (safe_count($res_ser) > 0) {
                echo 'Server already exists.';
            } else {
                $cust_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "install.Servers SET servername = '" . $serverName . "',url='" . $serURL . "',asseturl='" . $assetURL . "',configurl='" . $configURL . "',advanced='1'");
                $cust_sql->execute();
                $cust_result = $pdo->lastInsertId();

                if ($cust_result) {
                    $insertId = mysqli_insert_id();
                    echo 'Server details added.';
                } else {
                    echo 'This server details already registred.';
                }
            }
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    } else {
        echo 'Please fill all the values to add record';
    }
}

function updateAdvServer()
{

    global $pdo;

    $pdo = pdo_connect();

    $serverid = url::issetInPost('advserverid') ? url::postToAny('advserverid') : '';
    $serURL = url::issetInPost('advserURL') ? url::postToAny('advserURL') : '';
    $assetURL = url::issetInPost('assetURL') ? url::postToAny('assetURL') : '';
    $configURL = url::issetInPost('configURL') ? url::postToAny('configURL') : '';

    if ($serverid != '' && $serURL != '') {
        try {

            $updatePdo = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "install.Servers SET url='" . $serURL . "',asseturl='" . $assetURL . "',configurl='" . $configURL . "' where serverid=?");
            $updatePdo->execute([$serverid]);
            $cust_result = $pdo->lastInsertId();

            if ($cust_result) {
                echo 'Server details updated.';
            } else {
                echo 'This server details already registred.';
            }
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    } else {
        echo 'Please fill all the values to add record';
    }
}

function getDbIp($serVal)
{

    global $pdo;

    $pdo = pdo_connect();

    try {

        $pdoQuery = $pdo->prepare("SELECT url from " . $GLOBALS['PREFIX'] . "install.Servers where serverid=?");
        $pdoQuery->execute([$serVal]);
        $res_server = $pdoQuery->fetch();

        if (safe_count($res_server) > 0) {

            $url = $res_server['url'];

            if (strpos($url, '//') !== false) {
                $urlVal = explode("//", $url);
                $dbVal = explode(":", $urlVal[1]);
                $dbIp = $dbVal[0];
            } else {

                $dbVal = explode(":", $url);
                $dbIp = $dbVal[0];
            }
        }
        return $dbIp;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addCoreUser($userName, $notify_mail, $report_mail, $site)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }
    global $pdo;

    $pdo = pdo_connect();
    $cksum = md5(mt_rand());

    try {

        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $userName . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $sql_user = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users (username, password, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css)
            VALUES ( '" . $userName . "', MD5('" . $userName . "'), '" . $notify_mail . "', '" . $report_mail . "', 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '')");
            $sql_user->execute();
            $result_user = $pdo->lastInsertId();

            $adminId = $result_user;

            if ($adminId) {

                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $userName . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();

                $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $userName . "', customer = '" . $site . "', sitefilter = '0', owner = '0', notify_sender = '" . $notify_mail . "'");
                $ins_customer->execute();
                $cust_result = $pdo->lastInsertId();

                $sql_role = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Role ( userid, priv_user, priv_group, priv_tools, priv_admin_panel, priv_report_global, priv_group_global, priv_soln_push, priv_service_log, priv_global_event, priv_global_notification, priv_global_asset, priv_global_assetreport, priv_global_eventreport) VALUES (?, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1);");
                $sql_role->execute([$adminId]);
                $role_result = $pdo->lastInsertId();

                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function sendCust($to, $agent_id)
{

    global $base_url;
    $fromName = 'nanoheal';
    $fromEmail = getenv('SMTP_USER_LOGIN');
    $subject = 'process mail';
    $body = 'Process completed your user id =' . $to . '\n <a href="' . $base_url . '/?vid=' . $agent_id . '">Click Here</a>';
    $headers = '';
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";

    // send from visualisationService
    $arrayPost = array(
        'from' => getenv('SMTP_USER_LOGIN'),
        'to' => $to,
        'subject' => $subject,
        'text' =>'',
        'html' => $body,
        'token' => getenv('APP_SECRET_KEY'),
      );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
    // if (!mail($to, $subject, $body, $headers)) {
    if (!CURL::sendDataCurl($url, $arrayPost)) {
      return 0;
    } else {
      return 1;
    }
}

function pickupSiteCode($SKU, $pId)
{

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT R.sitename sitename from " . $GLOBALS['PREFIX'] . "agent.fieldValues F,custSkuMaster C,RegCode R where F.value='" . trim($SKU) . "' and F.id = C.skuId and C.siteId = R.id and C.pId = ? limit 1");
    $pdoQuery->execute([$pId]);
    $resDetail = $pdoQuery->fetch();

    $siteName = $resDetail['sitename'];

    return $siteName;
}

function getSiteCode($siteId, $pId)
{

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT  parameters,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where id=? limit 1");
    $pdoQuery->execute([$siteId]);
    $resDetail = $pdoQuery->fetch();

    $siteName = $resDetail['sitename'];

    return $siteName;
}

function getNoofPcsBySku($SKU)
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT id,upgrdSku,renewSku,skuName,skuRef,ppid,licensePeriod,licenseCnt,description,skuName,payment_mode,site_criteria,tax,skuPrice from " . $GLOBALS['PREFIX'] . "agent.skuMaster where (skuRef='$SKU' or id=?) limit 1");
    $pdoQuery->execute([$SKU]);
    $resSKU = $pdoQuery->fetch();
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

function getNoofPcsBySkuByid($SKU)
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT id,upgrdSku,renewSku,skuName,skuRef,ppid,licensePeriod,licenseCnt,description,skuName from " . $GLOBALS['PREFIX'] . "agent.skuMaster where (skuRef=? or id=?) limit 1");
    $pdoQuery->execute([$SKU, $SKU]);
    $resSKU = $pdoQuery->fetch();
    if (safe_count($resSKU) > 0) {
        $description = $resSKU['description'];
        $provCode = '01';
        $noOfDays = $resSKU['licensePeriod'];
        $noPC = $resSKU['licenseCnt'];
        $skuname = $resSKU['skuName'];
        $skuRef = $resSKU['skuRef'];
    }

    return array($noOfDays, $provCode, $noPC, $description, $skuname, $skuRef);
}

function getUrl()
{

    global $base_url;
    $pdo = pdo_connect();

    $customerNumber = url::issetInRequest('customerNumber') ? url::requestToText('customerNumber') : '';
    $customerOrder = url::issetInRequest('customerOrder') ? url::requestToText('customerOrder') : '';
    $customerFirstName = url::issetInRequest('customerFirstName') ? url::requestToText('customerFirstName') : '';
    $customerEmailId = url::issetInRequest('customerEmailId') ? url::requestToText('customerEmailId') : '';
    $SKU = url::issetInRequest('SKU') ? url::requestToText('SKU') : '';
    $contractEnddate = url::issetInRequest('contractEnd') ? url::requestToText('contractEnd') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';
    $email = $_SESSION["user"]["adminEmail"];
    $companyName = $_SESSION["user"]["companyName"];

    $pId = getProcessByCompany($cId);

    $customerLastName = '';
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = get_Entity_Dtl($cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = getNoofPcsBySku($SKU);
    $provCode = $skuDetls[1];
    $noOfDays = $skuDetls[0];
    $noOfPc = $skuDetls[2];
    $skuDesc = $skuDetls[3];
    $skuname = $skuDetls[4];
    $skuRef = $skuDetls[5];
    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver);

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = getDownloadId1();

        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        $dUrl = $base_url . 'eula.php?id=' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {
            addCoreSites($cId, $sitename);
            $finStatusMsg = "%%NOTDONE%%DATABASEDONE%%" . $companyName . " has been successfully provisioned for this order <br><span style='color:#FB1962;'></span>%%" . $dUrl . "%%" . $mailStatus . "%%";
        } else {
            $finStatusMsg = "%%NOTDONE%%Sorry,Problem In creating record into database%%";
        }
    } else {
        $finStatusMsg = "%%NOTDONE%%Sorry,Problem In creating ini record%%";
    }

    return $finStatusMsg;
}

function getConsumerProvisionUrl()
{

    global $base_url;
    $pdo = pdo_connect();

    $successStr = "Subscription";
    $bussiLevel = url::issetInRequest('bussiLevel') ? url::requestToText('bussiLevel') : '';

    $customerNumber = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $crmCustomerNum = url::issetInRequest('refCustomerNum') ? url::requestToText('refCustomerNum') : '';
    $crmOrderNum = url::issetInRequest('orderRefNumber') ? url::requestToText('orderRefNumber') : '';

    if ($customerNumber == '') {
        $successStr = "Subscriber";
        $customerNumber = getAutoCustNo();
    }

    $customerOrder = getAutoOrderNo();

    $customerFirstName = url::issetInRequest('customerFirstName') ? url::requestToText('customerFirstName') : '';
    $customerEmailId = url::issetInRequest('customerEmailId') ? url::requestToText('customerEmailId') : '';
    $SKU = url::issetInRequest('skuVal') ? url::requestToText('skuVal') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';

    $frdSitename = url::issetInRequest('fsite_name') ? url::requestToText('fsite_name') : '';
    $site_option = url::issetInRequest('site_option') ? url::requestToText('site_option') : '';
    $email = $_SESSION["user"]["adminEmail"];
    $companyName = $_SESSION["user"]["companyName"];

    $pId = getProcessByCompany($cId);

    if ($bussiLevel == 'Commercial' && $crmCustomerNum == '') {
        $crmCustomerNum = getAutoCustNo();
    }

    if ($bussiLevel == 'Consumer' && $crmCustomerNum == '') {
        $crmCustomerNum = $crmOrderNum;
    }

    $customerLastName = '';
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = get_Entity_Dtl($cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = getNoofPcsBySku($SKU);
    $provCode = $skuDetls[1];
    $noOfDays = $skuDetls[0];
    $noOfPc = $skuDetls[2];
    $skuDesc = $skuDetls[3];
    $skuname = $skuDetls[4];
    $skuRef = $skuDetls[5];
    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ? limit 1");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

    $cmpName = preg_replace('/\s+/', '_', $companyName);
    $siteSku = preg_replace('/\s+/', '_', $skuRef);

    $sel_sitename = url::requestToAny('sel_sitename');
    if ($sel_sitename == "") {
        if ($site_option == 'company_name') {
            $sitename = $cmpName;
        } elseif ($site_option == 'sku_name' || $bussiLevel == 'Consumer') {
            $sitename = $cmpName . '_' . $siteSku;
        } elseif ($site_option == 'friendly_name') {
            $sitename = $frdSitename;
        } else {
            $sitename = $res_prod["siteCode"];
        }
    } else {
        $sitename = $sel_sitename;
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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where (refOrderNum=? or refCustomerNum =?) and processId = ? and compId =? limit 1 ");
    $pdoQuery->execute([$crmOrderNum, $crmCustomerNum, $pId, $cId]);
    $chk_res = $pdoQuery->fetch();
    if (safe_count($chk_res) == 0) {

        $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver);

        if ($fileString != 'FAIL' || $fileString != '') {

            $downloadId = getDownloadId1();

            $sessionid = md5(mt_rand());

            $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "'");
            $sql_ser->execute();
            $result = $pdo->lastInsertId();

            $dUrl = $base_url . 'eula.php?id=' . $downloadId;
            $mailStatus = '';
            if ($result > 0) {
                addCoreSites($cId, $sitename);
                $finStatusMsg = array("msg" => "$successStr added successfully for this order", "link" => $dUrl);
            } else {
                $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
            }
        } else {
            $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
        }
    } else {
        $finStatusMsg = array("msg" => "Entered customer/Order reference no already exist.", "link" => "");
    }
    return $finStatusMsg;
}

function getEntitleConsumerProvisionUrl()
{

    global $base_url;
    $pdo = pdo_connect();

    $customerNumber = url::issetInRequest('refCustomerNum') ? url::requestToText('refCustomerNum') : '';
    $customerOrder = url::issetInRequest('orderRefNumber') ? url::requestToText('orderRefNumber') : '';
    $crmCustomerNum = getAutoCustNo();
    $crmOrderNum = getAutoOrderNo();

    $customerFirstName = url::issetInRequest('customerFirstName') ? url::requestToText('customerFirstName') : '';
    $customerLastName = url::issetInRequest('customerLastName') ? url::requestToText('customerLastName') : '';
    $customerEmailId = url::issetInRequest('customerEmailId') ? url::requestToText('customerEmailId') : '';

    $SKU = url::issetInRequest('skuVal') ? url::requestToText('skuVal') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';
    $custId = url::issetInRequest('custId') ? url::requestToText('custId') : '';

    $email = $_SESSION["user"]["adminEmail"];
    $companyName = $_SESSION["user"]["companyName"];

    $pId = getProcessByCompany($cId);

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = get_Entity_Dtl($cId);
    $customerCountry = $custDtl["country"];
    $backUpCapacity = '';
    $reportserver = $custDtl["reportserver"];

    $skuDetls = getNoofPcsBySku($SKU);
    $provCode = $skuDetls[1];
    $noOfDays = $skuDetls[0];
    $noOfPc = $skuDetls[2];
    $skuDesc = $skuDetls[3];
    $skuname = $skuDetls[4];
    $skuRef = $skuDetls[5];
    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();
    $subKey = '';
    if ($custId != "") {
        $pdoQuery = $pdo->prepare("SELECT siteName,subscriptionKey from " . $GLOBALS['PREFIX'] . "agent.customerOrder where id = ?");
        $pdoQuery->execute([$custId]);
        $res_site = $pdoQuery->fetch();
        $sitename = $res_site['siteName'];
        $subKey = $res_site["subscriptionKey"];
    } else {
        return array("msg" => "No site available", "link" => "");
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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where (customerNum=? or orderNum =?) and processId = ? and compId =? limit 1 ");
    $pdoQuery->execute([$crmOrderNum, $crmCustomerNum, $pId, $cId]);
    $chk_res = $pdoQuery->fetch();
    if (safe_count($chk_res) == 0) {

        $keyList = subscriptionKeyGen();
        $licenKey = $keyList["1"];

        $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver);

        if ($fileString != 'FAIL' || $fileString != '') {

            $downloadId = getDownloadId1();

            $sessionid = md5(mt_rand());

            $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',subscriptionKey='" . $subKey . "'");
            $sql_ser->execute();
            $result = $pdo->lastInsertId();

            $dUrl = $base_url . 'eula.php?id=' . $downloadId;
            $mailStatus = '';
            if ($result > 0) {
                addCoreSites($cId, $sitename);
                $finStatusMsg = array("msg" => "Your order has been added successfully, please copy below URL to install application. This URL will be valid for $noOfPc machines", "link" => $dUrl);
            } else {
                $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
            }
        } else {
            $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
        }
    } else {
        $finStatusMsg = array("msg" => "Entered customer/Order reference no already exist.", "link" => "");
    }
    return $finStatusMsg;
}

function getProductProvisionUrl()
{

    global $base_url;
    $pdo = pdo_connect();

    $paymentstr = '';

    $SKU = url::issetInRequest('skuVal') ? url::requestToText('skuVal') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';
    $new_site = url::issetInRequest('site_name') ? url::requestToText('site_name') : '';
    $noofpc = url::issetInRequest('noofpc') ? url::requestToText('noofpc') : '';
    $busslevel = $_SESSION["user"]["busslevel"];
    $licenseKey = url::issetInRequest('licenseKey') ? url::requestToText('licenseKey') : '';
    if ($busslevel == 'Commercial') {
        $customerNumber = getAutoCustNo();
        $customerOrder = getAutoOrderNo();
        $crmCustomerNum = getAutoCustNo();
        $crmOrderNum = getAutoOrderNo();
    } else if ($busslevel == 'Consumer') {
        $customerNumber = '';
        $customerOrder = '';
        $crmCustomerNum = '';
        $crmOrderNum = '';
    }

    $pId = getProcessByCompany($cId);

    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $siteExist = isSiteExist($new_site, $cId);
    if ($siteExist == 1) {
        return $finStatusMsg = array("msg" => "EXIST", "link" => "");
    }
    $custDtl = get_Entity_Dtl($cId);
    $companyName = $custDtl['companyName'];
    $reportserver = $custDtl["reportserver"];
    $email = $custDtl["emailId"];
    $customerFirstName = $custDtl["firstName"];
    $customerLastName = $custDtl["lastName"];
    $customerCountry = $custDtl["country"];
    $bussiLevel = $custDtl["businessLevel"];
    $compId = $custDtl["eid"];

    $skuDetls = getNoofPcsBySku($SKU);
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

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $keyList = subscriptionKeyGen();
    if ($busslevel == 'Commercial') {
        $subKey = $keyList["1"];
        if ($licenseKey != "") {
            $licenKey = $licenseKey;
        } else {
            $licenKey = $keyList["1"];
        }
    } else if ($busslevel == 'Consumer') {
        $subKey = $keyList["1"];
        if ($licenseKey != "") {
            $licenKey = $licenseKey;
        } else {
            $licenKey = '';
        }
    }

    if ($payment_mode == "Prepaid") {
        $skuprice = $skuDetls[10];
        $tax = $skuDetls[9];
        $subtotal = round($skuprice * $noOfPc);
        if ($tax_temp != 0 || $tax != '0') {
            $tax = round(($subtotal * $tax_temp) / 100);
        } else {
            $tax = 0;
        }
        $total = $subtotal + $tax;
        $paymentstr = "<table style='width:100%' class='table payment_table'>"
            . "<caption><p><span class='highlight' style='color:#48b2e4;!important;'>Payment Detail</span><span class='highlight'>: You selected paid SKU, find below payment details.</span></p></caption>"
            . "<tr><th>Item</th><th>Description</th><th>Qty</th><th>Rate</th><th>Sub Total</th><th>Tax</th><th>Total</th></tr>"
            . "<tr><td>" . $skuname . "</td><td>" . $skuDesc . "</td><td>" . $noOfPc . "</td><td>" . $skuprice . "</td><td>" . $subtotal . "</td>"
            . "<td>" . $tax . "</td><td>" . $total . "$</td></tr>"
            . "</table>";
    } else {
        $paymentstr = '';
    }

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $email, $contractEDate, $provCode, $reportserver);

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = getDownloadId1();

        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($email) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',subscriptionKey='" . $subKey . "',licenseKey='" . $licenKey . "'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        $dUrl = $base_url . 'eula.php?id=' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {
            $finStatusMsg = array("msg" => "Your $skuname account has been activated, Please click on NEXT button to add device", "link" => $dUrl, "payment_mode" => $payment_mode, "paymentstr" => $paymentstr);
        } else {
            $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
        }
    } else {
        $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
    }

    return $finStatusMsg;
}

function getConsumer_Product_ProvisionUrl()
{

    global $base_url;
    $pdo = pdo_connect();

    $SKU = url::issetInRequest('skuVal') ? url::requestToText('skuVal') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';
    $new_site = url::issetInRequest('site_name') ? url::requestToText('site_name') : '';
    $noofpc = url::issetInRequest('noofpc') ? url::requestToText('noofpc') : '';
    $licenseKey = url::issetInRequest('licenseKey') ? url::requestToText('licenseKey') : '';
    $busslevel = $_SESSION["user"]["busslevel"];

    $selCid = $noofpc = isset($_SESSION["selected"]["eid"]) ? trim($_SESSION["selected"]["eid"]) : '';

    $customerNumber = getAutoCustNo();
    $customerOrder = getAutoOrderNo();
    $crmCustomerNum = getAutoCustNo();
    $crmOrderNum = getAutoOrderNo();

    $pId = getProcessByCompany($cId);

    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }

    $siteExist = isSiteExist($new_site, $cId);
    if ($siteExist == 1) {
        return $finStatusMsg = array("msg" => "EXIST", "link" => "");
    }

    $custDtl = get_Entity_Dtl($cId);
    $companyName = $custDtl['companyName'];
    $reportserver = $custDtl["reportserver"];
    $email = $custDtl["emailId"];
    $customerFirstName = $custDtl["firstName"];
    $customerLastName = $custDtl["lastName"];
    $customerCountry = $custDtl["country"];
    $bussiLevel = $custDtl["businessLevel"];

    $skuDetls = getNoofPcsBySku($SKU);
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

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    if ($payment_mode == "Prepaid") {
        $skuprice = $skuDetls[10];
        $tax = $skuDetls[9];
        $subtotal = round($skuprice * $noOfPc);
        if ($tax_temp != 0 || $tax != '0') {
            $tax = round(($subtotal * $tax_temp) / 100);
        } else {
            $tax = 0;
        }
        $total = $subtotal + $tax;
        $paymentstr = "<table style='width:100%' class='table payment_table'>"
            . "<caption><p><span class='highlight' style='color:#48b2e4;!important;'>Payment Detail</span><span class='highlight'>: You selected paid SKU, find below payment details.</span></p></caption>"
            . "<tr><th>Item</th><th>Description</th><th>Qty</th><th>Rate</th><th>Sub Total</th><th>Tax</th><th>Total</th></tr>"
            . "<tr><td>" . $skuname . "</td><td>" . $skuDesc . "</td><td>" . $noOfPc . "</td><td>" . $skuprice . "</td><td>" . $subtotal . "</td>"
            . "<td>" . $tax . "</td><td>" . $total . "$</td></tr>"
            . "</table>";
    } else {
        $paymentstr = '';
    }

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $email, $contractEDate, $provCode, $reportserver);

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = getDownloadId1();

        $sessionid = md5(mt_rand());

        $keyList = subscriptionKeyGen();
        if ($licenseKey == '') {
            $subKey = $keyList["1"];
            $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($email) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '0',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "',subscriptionKey='" . $subKey . "'");
        } else {
            $subKey = $licenseKey;
            $sql_ser = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($email) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "' where subscriptionKey='$subKey'");
        }

        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        $dUrl = $base_url . 'eula.php?id=' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {

            if ($selCid != '') {
                add_CustomerSites($cId, $sitename);
            } else {
                addCoreSites($cId, $sitename);
            }
            $finStatusMsg = array("msg" => "Your $skuname account has been activated, Please click on NEXT button to add device", "link" => $dUrl, "", "payment_mode" => $payment_mode, "paymentstr" => $paymentstr);
        } else {
            $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
        }
    } else {
        $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
    }

    return $finStatusMsg;
}

function isSiteExist($siteName, $compId)
{
    global $base_url;
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE siteName = ? AND compId != ? limit 1");
    $pdoQuery->execute([$siteName, $compId]);
    $existRes = $pdoQuery->fetch();

    if (safe_count($existRes) > 0) {
        return 1;
    } else {
        return 0;
    }
}

function upgradeRenewUrl()
{

    global $base_url;
    $pdo = pdo_connect();

    $bussiLevel = url::issetInRequest('bussiLevel') ? url::requestToText('bussiLevel') : '';

    $customerNumber = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $crmCustomerNum = url::issetInRequest('refCustomerNum') ? url::requestToText('refCustomerNum') : '';
    $oldCustOrd = url::issetInRequest('orderRefNumber') ? url::requestToText('orderRefNumber') : '';
    $orderNewNumber = url::issetInRequest('orderNewNumber') ? url::requestToText('orderNewNumber') : '';

    $customerOrder = getAutoOrderNo();

    $customerFirstName = url::issetInRequest('customerFirstName') ? url::requestToText('customerFirstName') : '';
    $customerEmailId = url::issetInRequest('customerEmailId') ? url::requestToText('customerEmailId') : '';
    $SKU = url::issetInRequest('skuVal') ? url::requestToText('skuVal') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';

    $frdSitename = url::issetInRequest('fsite_name') ? url::requestToText('fsite_name') : '';
    $site_option = url::issetInRequest('site_option') ? url::requestToText('site_option') : '';
    $email = $_SESSION["user"]["adminEmail"];
    $companyName = $_SESSION["user"]["companyName"];

    $pId = getProcessByCompany($cId);

    $customerLastName = '';
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = get_Entity_Dtl($cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = getNoofPcsBySku($SKU);
    $provCode = $skuDetls[1];
    $noOfDays = $skuDetls[0];
    $noOfPc = $skuDetls[2];
    $skuDesc = $skuDetls[3];
    $skuname = $skuDetls[4];
    $skuRef = $skuDetls[5];
    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

    $cmpName = preg_replace('/\s+/', '_', $companyName);
    $siteSku = preg_replace('/\s+/', '_', $skuRef);

    $sel_sitename = url::requestToAny('sel_sitename');
    if ($sel_sitename == "") {
        if ($site_option == 'company_name') {
            $sitename = $cmpName;
        } elseif ($site_option == 'sku_name' || $bussiLevel == 'Consumer') {
            $sitename = $cmpName . '_' . $siteSku;
        } elseif ($site_option == 'friendly_name') {
            $sitename = $frdSitename;
        } else {
            $sitename = $res_prod["siteCode"];
        }
    } else {
        $sitename = $sel_sitename;
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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver);

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = getDownloadId1();

        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $orderNewNumber . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $email . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        $dUrl = $base_url . 'eula.php?id=' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {
            addCoreSites($cId, $sitename);
            $finStatusMsg = array("msg" => $companyName . " has been successfully provisioned for this order", "link" => $dUrl);
        } else {
            $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
        }
    } else {
        $finStatusMsg = array("msg" => "Error to create entry", "link" => "");
    }

    return $finStatusMsg;
}

function generateProvUrl($crmCustomerNum, $crmOrderNum, $customerFirstName, $customerLastName, $customerEmailId, $SKU, $cId, $companyName)
{

    global $base_url;
    $pdo = pdo_connect();

    $agentEmail = $_SESSION["user"]["loguser"];

    $pId = getProcessByCompany($cId);
    $customerCountry = '';
    $backUpCapacity = '';

    $currentDate = time();
    $remoteSession = '';

    $finStatusMsg = '';

    $oldCustOrd = '';

    $customerNumber = getAutoCustNo();
    $customerOrder = getAutoOrderNo();

    if ($cId == '' || $pId == '') {
        $finStatusMsg = "%%LOGINPROB%%";
        return $finStatusMsg;
    }
    $custDtl = get_Entity_Dtl($cId);
    $reportserver = $custDtl["reportserver"];

    $skuDetls = getNoofPcsBySkuByid($SKU);
    $provCode = $skuDetls[1];
    $noOfDays = $skuDetls[0];
    $noOfPc = $skuDetls[2];
    $skuDesc = $skuDetls[3];
    $skuname = $skuDetls[4];
    $skuRef = $skuDetls[5];
    $curDate = date("Y-m-d H:i:s");
    $dateOfOrder = strtotime($curDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +$noOfDays day");

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

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

    $variation = $res_prod['variation'];
    $locale = $res_prod['locale'];
    $downloadPath = $res_prod['downloaderPath'];
    $sendEmail = $res_prod['sendMail'];
    $backUp = $res_prod['backupCheck'];
    $respectiveDB = $res_prod['DbIp'];

    $fileString = create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportserver);

    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = getDownloadId1();

        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "',refCustomerNum = '" . $crmCustomerNum . "',refOrderNum = '" . $crmOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '" . $backUpCapacity . "', sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', noOfPc = '" . trim($noOfPc) . "',oldorderNum ='" . $oldCustOrd . "',provCode = '" . $provCode . "',remoteSessionURL = '" . $remoteSession . "',agentId = '" . $agentEmail . "',processId= '$pId',compId='$cId',downloadId='" . $downloadId . "',siteName='" . $sitename . "'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        $dUrl = $base_url . 'eula.php?id=' . $downloadId;
        $mailStatus = '';
        if ($result > 0) {

            addCoreSites($cId, $sitename);
            $finStatusMsg = $dUrl;
        } else {
            $finStatusMsg = "NOTDONE";
        }
    } else {
        $finStatusMsg = "NOTDONE";
    }

    return $finStatusMsg;
}

function create_ini_parameters($sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $reportId, $trial, $degrdSku, $renew)
{
    global $pdo;
    global $base_url;
    $pdo = pdo_connect();

    global $file_path;
    $INI_FILEPATH = 'customer/ini/';
    $INI_FILENAME = 'NanoHeal';

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "install.Servers where serverid=? limit 1");
    $pdoQuery->execute([$reportId]);
    $res_reg = $pdoQuery->fetch();

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

        $file = $file_path . $INI_FILEPATH . $INI_FILENAME . '.ini';
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
        $fileString = str_replace('ChatLink=', 'ChatLink=' . trim($_SESSION['lob']['chatLink']), $fileString);
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

        return $fileString;
    } else {
        return 'FAIL';
    }
}

function addSignupSites($channelId, $siteName, $restrict)
{

    try {

        $pdo = pdo_connect();

        $siteName = preg_replace('/\s+/', '_', $siteName);

        $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Customers where customer= ? limit 1");
        $pdoQuery->execute([$siteName]);
        $serl_res = $pdoQuery->fetch();

        if (safe_count($serl_res) > 0) {
        } else {
            if ($restrict == '0' || $restrict == 0) {
                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$channelId]);
                $ent_res = $pdoQuery->fetchAll();
                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {

                        $addEnSite = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            } else {
                $pdoQuery = $pdo->prepare(" select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U "
                    . "where U.ch_id IN (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where companyName = 'NH_MSP')");
                $pdoQuery->execute();
                $ent_res = $pdoQuery->fetchAll();
                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {

                        $addEnSite = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function addCoreSites($cid, $siteName)
{

    try {

        $pdo = pdo_connect();

        $entityId = $_SESSION["user"]["entityId"];
        $channelId = $_SESSION["user"]["channelId"];
        $subchannelId = $_SESSION["user"]["subchannelId"];
        $outsourcedId = $_SESSION["user"]["outsourcedId"];
        $ctype = $_SESSION["user"]["customerType"];

        $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Customers where customer= '$siteName' limit 1");
        $pdoQuery->execute();
        $serl_res = $pdoQuery->fetch();

        if (safe_count($serl_res) > 0) {
        } else {

            if ($entityId != 0) {

                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$entityId]);
                $ent_res = $pdoQuery->fetchAll();
                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {
                        if ($ctype == 2 || $ctype == '2') {

                            $chnid = $entValue['channel_id'];
                            $ch_id = explode(",", $chnid);
                            foreach ($ch_id as $value) {
                                if ($value == $cid) {
                                    $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                                    $addEnSite->execute();
                                    $result1 = $pdo->lastInsertId();
                                }
                            }
                        }

                        if ($ctype == 3 || $ctype == '3') {

                            $subid = $entValue['subch_id'];
                            $sub_id = explode(",", $subid);
                            foreach ($sub_id as $value) {
                                if ($value == $cid) {
                                    $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                                    $addEnSite->execute();
                                    $result1 = $pdo->lastInsertId();
                                }
                            }
                        }

                        if ($ctype == 5 || $ctype == '5') {

                            $cusid = $entValue['customer_id'];
                            $cust_id = explode(",", $cusid);
                            foreach ($cust_id as $value) {
                                if ($value == $cid) {
                                    $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                                    $addEnSite->execute();
                                    $result1 = $pdo->lastInsertId();
                                }
                            }
                        }
                    }
                }
            }

            if ($channelId != 0) {

                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$channelId]);
                $chnl_res = $pdoQuery->fetchAll();
                if (safe_count($chnl_res) > 0) {

                    foreach ($chnl_res as $chnlValue) {

                        if ($ctype == 3 || $ctype == '3') {

                            $subid = $chnlValue['subch_id'];
                            $sub_id = explode(",", $subid);
                            foreach ($sub_id as $value) {
                                if ($value == $cid) {
                                    $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $chnlValue['username'] . "', '" . $siteName . "', 0, 0)");
                                    $addEnSite->execute();
                                    $result1 = $pdo->lastInsertId();
                                }
                            }
                        }

                        if ($ctype == 5 || $ctype == '5') {

                            $cusid = $chnlValue['customer_id'];
                            $cust_id = explode(",", $cusid);
                            foreach ($cust_id as $value) {
                                if ($value == $cid) {
                                    $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $chnlValue['username'] . "', '" . $siteName . "', 0, 0)");
                                    $addEnSite->execute();
                                    $result1 = $pdo->lastInsertId();
                                }
                            }
                        }
                    }
                }
            }

            if ($subchannelId != 0) {

                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$subchannelId]);
                $subchnl_res = $pdoQuery->fetchAll();
                if (safe_count($subchnl_res) > 0) {
                    foreach ($subchnl_res as $subChnlValue) {

                        if ($ctype == 5 || $ctype == '5') {

                            $cusid = $subChnlValue['customer_id'];
                            $cust_id = explode(",", $cusid);
                            foreach ($cust_id as $value) {
                                if ($value == $cid) {
                                    $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $subChnlValue['username'] . "', '" . $siteName . "', 0, 0)");
                                    $addEnSite->execute();
                                    $result1 = $pdo->lastInsertId();
                                }
                            }
                        }
                    }
                }
            }

            $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
            $pdoQuery->execute([$cid]);
            $custmr_res = $pdoQuery->fetchAll();
            if (safe_count($custmr_res) > 0) {
                foreach ($custmr_res as $value) {

                    $addcustSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 1)");
                    $addcustSite->execute();
                    $result1 = $pdo->lastInsertId();
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function add_CustomerSites($cid, $siteName)
{

    try {

        $pdo = pdo_connect();

        $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Customers where customer=? limit 1");
        $pdoQuery->execute([$siteName]);
        $serl_res = $pdoQuery->fetch();

        if (safe_count($serl_res) > 0) {
        } else {

            $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid =?");
            $pdoQuery->execute([$cid]);
            $chnl_res = $pdoQuery->fetch();

            $entityId = $chnl_res['entityId'];
            $channelId = $chnl_res['channelId'];
            $subchannelId = $chnl_res['subchannelId'];
            $outsourcedId = $chnl_res['outsourcedId'];
            $ctype = $_SESSION["user"]["customerType"];

            if ($entityId != 0) {

                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$entityId]);
                $ent_res = $pdoQuery->fetchAll();
                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {

                        $cusid = $entValue['customer_id'];
                        $cust_id = explode(",", $cusid);
                        foreach ($cust_id as $value) {
                            if ($value == $cid) {
                                $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                                $addEnSite->execute();
                                $result1 = $pdo->lastInsertId();
                            }
                        }
                    }
                }
            }

            if ($channelId != 0) {

                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$channelId]);
                $chnl_res = $pdoQuery->fetchAll();
                if (safe_count($chnl_res) > 0) {

                    foreach ($chnl_res as $chnlValue) {

                        $cusid = $chnlValue['customer_id'];
                        $cust_id = explode(",", $cusid);
                        foreach ($cust_id as $value) {
                            if ($value == $cid) {
                                $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $chnlValue['username'] . "', '" . $siteName . "', 0, 0)");
                                $addEnSite->execute();
                                $result1 = $pdo->lastInsertId();
                            }
                        }
                    }
                }
            }

            if ($subchannelId != 0) {

                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $pdoQuery->execute([$subchannelId]);
                $subchnl_res = $pdoQuery->fetchAll();
                if (safe_count($subchnl_res) > 0) {
                    foreach ($subchnl_res as $subChnlValue) {

                        $cusid = $subChnlValue['customer_id'];
                        $cust_id = explode(",", $cusid);
                        foreach ($cust_id as $value) {
                            if ($value == $cid) {
                                $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $subChnlValue['username'] . "', '" . $siteName . "', 0, 0)");
                                $addEnSite->execute();
                                $result1 = $pdo->lastInsertId();
                            }
                        }
                    }
                }
            }

            $pdoQuery = $pdo->prepare("SELECT U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
            $pdoQuery->execute([$cid]);
            $custmr_res = $pdoQuery->fetchAll();
            if (safe_count($custmr_res) > 0) {
                foreach ($custmr_res as $value) {

                    $addcustSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 1)");
                    $addcustSite->execute();
                    $result1 = $pdo->lastInsertId();
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function addCoreSites_1($cid, $siteName)
{

    try {

        $pdo = pdo_connect();

        $entityId = $_SESSION["user"]["entityId"];
        $channelId = $_SESSION["user"]["channelId"];
        $subchannelId = $_SESSION["user"]["subchannelId"];
        $outsourcedId = $_SESSION["user"]["outsourcedId"];

        $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Customers where customer=? limit 1");
        $pdoQuery->execute([$siteName]);
        $serl_res = $pdoQuery->fetch();

        if (safe_count($serl_res) > 0) {
        } else {

            if ($entityId != 0) {
                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O where U.ch_id=? and U.role_id=O.id and O.name='user_superadmin'");
                $pdoQuery->execute([$entityId]);
                $ent_res = $pdoQuery->fetchAll();
                if (safe_count($ent_res) > 0) {
                    foreach ($ent_res as $value) {

                        $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            }
            if ($channelId != 0) {
                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O where U.ch_id=? and U.role_id=O.id and O.name='user_superadmin'");
                $pdoQuery->execute([$channelId]);
                $chnl_res = $pdoQuery->fetchAll();
                if (safe_count($chnl_res) > 0) {
                    foreach ($chnl_res as $value) {

                        $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }

                if ($outsourcedId == 0) {

                    $pdoQuery = $pdo->prepare("SELECT outsourcedId from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? ");
                    $pdoQuery->execute([$channelId]);
                    $chout_res = $pdoQuery->fetch();
                    $chOut_id = $chout_res['outsourcedId'];

                    $pdoQuery = $pdo->prepare("SELECT U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O where U.ch_id=? and U.role_id=O.id and O.name='user_superadmin'");
                    $pdoQuery->execute([$chOut_id]);
                    $outsrc_res = $pdoQuery->fetchAll();
                    if (safe_count($outsrc_res) > 0) {
                        foreach ($outsrc_res as $value) {

                            $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 0)");
                            $addEnSite->execute();
                            $result1 = $pdo->lastInsertId();
                        }
                    }
                }
            }

            if ($subchannelId != 0) {
                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O where U.ch_id=? and U.role_id=O.id and O.name='user_superadmin'");
                $pdoQuery->execute([$subchannelId]);
                $subchnl_res = $pdoQuery->fetchAll();
                if (safe_count($subchnl_res) > 0) {
                    foreach ($subchnl_res as $value) {

                        $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            }

            if ($outsourcedId != 0) {
                $pdoQuery = $pdo->prepare("SELECT U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O where U.ch_id=? and U.role_id=O.id and O.name='user_superadmin'");
                $pdoQuery->execute([$outsourcedId]);
                $outsrc_res = $pdoQuery->fetchAll();
                if (safe_count($outsrc_res) > 0) {
                    foreach ($outsrc_res as $value) {

                        $addEnSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            }
            $pdoQuery = $pdo->prepare("SELECT U.username,U.userid from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O where U.ch_id=? and U.role_id=O.id and O.name='user_superadmin'");
            $pdoQuery->execute([$cid]);
            $custmr_res = $pdoQuery->fetchAll();
            if (safe_count($custmr_res) > 0) {
                foreach ($custmr_res as $value) {

                    $addcustSite = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $value['username'] . "', '" . $siteName . "', 0, 1)");
                    $addcustSite->execute();
                    $result1 = $pdo->lastInsertId();
                }
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function getDownloadId1()
{

    try {

        $pdo = pdo_connect();

        $downloadId = getPasswordId1();

        $pdoQuery = $pdo->prepare("SELECT id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where downloadId=?");
        $pdoQuery->execute([$downloadId]);
        $res_Coust = $pdoQuery->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return getDownloadId1();
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function regen_customer()
{
    global $base_url;
    $pdo = pdo_connect();

    $customerNumber = url::issetInRequest('customerNumber') ? url::requestToText('customerNumber') : '';
    $customerOrder = url::issetInRequest('customerOrder') ? url::requestToText('customerOrder') : '';
    $cId = url::issetInRequest('companyId') ? url::requestToText('companyId') : '';
    $email = $_SESSION["user"]["adminEmail"];
    $companyName = $_SESSION["user"]["companyName"];

    $pId = getProcessByCompany($cId);

    $downloadId = getPasswordId1();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
    $pdoQuery->execute([$pId]);
    $res_prod = $pdoQuery->fetch();

    $downloadPath = $res_prod['downloaderPath'];

    $pdoQuery = $pdo->prepare("SELECT downloadId,customerNum,orderNum,contractEndDate from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum = '" . $customerNumber . "' and orderNum = '" . $customerOrder . "' and processId = ? and compId=?");
    $pdoQuery->execute([$pId, $cId]);
    $res_Coust = $pdoQuery->fetch();
    $count = safe_count($res_Coust);

    if ($count > 0) {
        $customerId = $res_Coust['downloadId'];
        $contractEndDate = $res_Coust['contractEndDate'];
        $today = time();
        if ($contractEndDate > $today) {
            $dUrl = $base_url . 'eula.php?id=' . $customerId;
            $finStatusMsg = "%%DONE%%" . $companyName . " has been successfully regenerated for this order %%" . $dUrl . "%%";
        } else {
            $finStatusMsg = "%%NOTDONE%%Contract has been already expaired%%";
        }
    } else {
        $finStatusMsg = "%%NOTDONE%%Customer number or Order number not exists%%";
    }
    return $finStatusMsg;
}

function get_Order_Details()
{
    $pdo = pdo_connect();

    $customerNum = url::requestToAny('cid');
    echo $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where eid =? limit 1");
    $pdoQuery->execute([$customerNum]);
    $res_Coust1 = $pdoQuery->fetch();
    return $res_Coust1;
}

function get_Order_Details_CustomerNum()
{
    $pdo = pdo_connect();

    $str = '';
    $customerNum = url::requestToAny('customerNo');
    $pdoQuery = $pdo->prepare("SELECT O.customerNum , O.compId , O.coustomerFirstName , O.refCustomerNum, O.coustomerLastName , O.coustomerCountry , C.phoneNo , O.emailId ,"
        . "C.businessLevel,C.skulist,C.ordergen , C.zipCode , C.province, C.city, C.address  from " . $GLOBALS['PREFIX'] . "agent.customerOrder O, channel C where O.compId=C.eid and O.customerNum=? limit 1");
    $pdoQuery->execute([$customerNum]);
    $res_Coust1 = $pdoQuery->fetch();
    $str = order_site_list($customerNum);
    return array($res_Coust1, $str);
}

function order_site_list($customerNum)
{
    $pdo = pdo_connect();
    $str = '';
    $pdoQuery = $pdo->prepare("SELECT siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=? group by siteName");
    $pdoQuery->execute([$customerNum]);
    $res_Cust_Site = $pdoQuery->fetchAll();
    $str .= '<option value="">Select Site</option>';
    foreach ($res_Cust_Site as $key => $value) {
        $str .= '<option value="' . $value["siteName"] . '">' . $value["siteName"] . '</option>';
    }
    return $str;
}

function getCustomerOrderId($customerNumber, $customerOrder)
{
    $pdo = pdo_connect();

    $cId = $_SESSION["user"]["cId"];
    $pId = $_SESSION["user"]["pid"];

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum = '" . $customerNumber . "' and orderNum = '" . $customerOrder . "' and processId =? ");
    $pdoQuery->execute([$pId]);
    $res_Coust = $pdoQuery->fetch();

    if (safe_count($res_Coust) > 0) {
        $customerOrderId = $res_Coust['id'];
    } else {
        $customerOrderId = 0;
    }
    return $customerOrderId;
}

function unixdate_to_realdate($timestamp)
{
    if ($timestamp == 0) {
        $date = 0;
    } else {
        @$date = gmdate("m/d/Y", $timestamp);
    }
    return $date;
}

function get_dates_difference($startTimestamp, $endTimestamp)
{

    $numDays = round(abs($startTimestamp - $endTimestamp) / 60 / 60 / 24);
    return $numDays;
}

function getServiceDownloadId()
{

    try {

        $pdo = pdo_connect();

        $downloadId = getPasswordId1();

        $pdoQuery = $pdo->prepare("SELECT sid,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where downloadId=?");
        $pdoQuery->execute([$downloadId]);
        $res_Coust = $pdoQuery->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return getServiceDownloadId();
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function get_company_channel()
{

    global $pdo;

    $customerType = $_SESSION["user"]["customerType"];

    if ($customerType == 1) {
        $entityId = $_SESSION["user"]["cId"];
        $wh = "entityId = '$entityId' and channelId=0 and subchannelId=0";
    }
    if ($customerType == 2) {
        $channelId = $_SESSION["user"]["cId"];
        $wh = "channelId = '$channelId' and subchannelId=0";
    }
    if ($customerType == 3) {
        $subchannelId = $_SESSION["user"]["cId"];
        $wh = "subchannelId = '$subchannelId'";
    }

    if ($customerType == 4) {
        $outsourcedId = $_SESSION["user"]["cId"];
        $wh = "outsourcedId = '$outsourcedId'";
    }

    if ($customerType == 5) {
        $compId = $_SESSION["user"]["cId"];
        $wh = "eid = '$compId'";
    }

    $pdoQuery = $pdo->prepare("SELECT eid,companyName,regNo,referenceNo,firstName,lastName,emailId,phoneNo,customerNo from " . $GLOBALS['PREFIX'] . "agent.channel where " . $wh . " and ctype=5");
    $pdoQuery->execute();
    $fld_res = $pdoQuery->fetchAll();

    $companyList = '';
    if (safe_count($fld_res) > 0) {

        foreach ($fld_res as $value) {

            $values = $value['eid'] . 'HFNFH' . $value['customerNo'];
            $cid = $value['eid'];
            $customerNum = $value['customerNo'];
            $onclick = "customerClick(this,'" . $cid . "','" . $customerNum . "')";
            $companyList .= '<li style="color:#009966;" id="' . $value['eid'] . '" value="' . $values . '" onclick="' . $onclick . '" class="aside-list__item" >' . $value['companyName'] . '</li>';
        }
        return $companyList;
    }
}

function getSkuByCustmomerId()
{

    global $pdo;

    $fid = url::issetInRequest('fid') ? url::requestToAny('fid') : '';

    $pdoQuery = $pdo->prepare("SELECT F.displayName,F.value,F.provCode from " . $GLOBALS['PREFIX'] . "agent.custSkuMaster S,fieldValues F where S.cId =? and F.Id = S.skuId");
    $pdoQuery->execute([$fid]);
    $fld_res = $pdoQuery->fetchAll();

    $str = '<option style="left:-10px;" value="0">Select Plan..</option>';
    $option = '';
    foreach ($fld_res as $value) {
        $option .= '<option id="' . $value['provCode'] . '" value="' . $value['value'] . '" >' . $value['displayName'] . '</option>';
    }
    $finalStr = $str . $option;

    return $finalStr;
}

function getSiteBySKUId()
{

    global $pdo;

    $fid = url::issetInRequest('fid') ? url::requestToAny('fid') : '';
    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';

    $pdoQuery = $pdo->prepare("SELECT  R.id,R.sitename from " . $GLOBALS['PREFIX'] . "agent.custSkuMaster S,RegCode R where S.cId =? and S.skuId=? and S.siteId=R.id");
    $pdoQuery->execute([$cid, $fid]);
    $fld_res = $pdoQuery->fetchAll();

    $str = '<option style="left:-10px;" value="0">Select Site..</option>';
    $option = '';
    foreach ($fld_res as $value) {
        $option .= '<option id="' . $value['id'] . '" value="' . $value['id'] . '" >' . $value['sitename'] . '</option>';
    }
    $finalStr = $str . $option;

    return $finalStr;
}

function getProcessByCompany($cid)
{

    global $pdo;

    $pdoQuery = $pdo->prepare("SELECT pId,cId,processName from " . $GLOBALS['PREFIX'] . "agent.processMaster where cId=? order by pId desc limit 1");
    $pdoQuery->execute([$cid]);
    $fld_res = $pdoQuery->fetch();
    $pId = $fld_res['pId'];
    return $pId;
}

function getOrderNo()
{

    global $pdo;

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $custNo = url::issetInRequest('custNo') ? url::requestToAny('custNo') : '';

    $pId = getProcessByCompany($cid);

    $pdoQuery = $pdo->prepare("SELECT orderNum,customerNum,compId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where processId=? and customerNum=?");
    $pdoQuery->execute([$pId, $custNo]);
    $fld_res = $pdoQuery->fetchAll();

    $option = '';
    foreach ($fld_res as $value) {

        $cid = $value['compId'];
        $customerNum = $value['customerNum'];
        $orderNum = $value['orderNum'];
        $onclick = "orderClick(this,'" . $cid . "','" . $customerNum . "','" . $orderNum . "')";

        $ordervalue = $value['customerNum'] . 'HFNFH' . $value['orderNum'] . 'HFNFH' . $value['compId'];

        $option .= '<li style="color:#009966;" id="' . $value['orderNum'] . '" value="' . $ordervalue . '"  onclick="' . $onclick . '" class="aside-list__item">' . $value['orderNum'] . '</li>';
    }
    $finalStr = $option;

    return $finalStr;
}

function getOrderBycustomerNo()
{

    global $pdo;

    $cid = url::issetInRequest('cid') ? url::requestToAny('cid') : '';
    $pId = getProcessByCompany($cid);

    $pdoQuery = $pdo->prepare("SELECT orderNum,customerNum,compId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where processId=?");
    $pdoQuery->execute([$pId]);
    $fld_res = $pdoQuery->fetchAll();

    $option = '';
    foreach ($fld_res as $value) {
        $ordervalue = $value['customerNum'] . 'HFN' . $value['orderNum'] . 'HFN' . $value['compId'];

        $option .= '<li style="color:#009966;" id="' . $value['orderNum'] . '" value="' . $ordervalue . '" >' . $value['orderNum'] . '</li>';
    }
    $finalStr = $option;

    return $finalStr;
}

function getServiceTagList($cId, $pId, $orderNo, $customerNo)
{

    global $pdo;

    $provCode = '01';
    $serviceTagStr = '';
    $pdoQuery = $pdo->prepare("SELECT customerNum,orderNum,serviceTag from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where  customerNum = ? and orderNum=? and processId = ? group by serviceTag ORDER BY sid DESC");
    $pdoQuery->execute([$customerNo, $orderNo, $pId]);
    $serviceTag_res = $pdoQuery->fetchAll();

    $serviceTagCnt = safe_count($serviceTag_res);

    if ($serviceTagCnt > 0) {

        $i = 1;

        foreach ($serviceTag_res as $value) {
            $ordervalue = $value['customerNum'] . 'HFNS' . $value['orderNum'] . "HFNS" . $value['serviceTag'];

            $serviceTag = $value['serviceTag'];
            $customerNum = $value['customerNum'];
            $orderNum = $value['orderNum'];
            $onclick = "servicetagClick(this,'" . $customerNum . "','" . $orderNum . "','" . $serviceTag . "')";

            $serviceTagStr .= '<li  style="cursor:pointer" value="' . $ordervalue . '" onclick="' . $onclick . '" class="aside-list__item">' . $value['serviceTag'] . '</li>';
        }
    }

    return $serviceTagStr;
}

function getuserListGridData()
{
    global $pdo;
    $cId = url::issetInRequest('cId') ? url::requestToAny('cId') : '';
    $recordList = array();
    $where = '';
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $draw = url::requestToAny('draw');
    $limit = " limit $start , $length";
    $searchVal = url::requestToAny('search')['value'];
    $orderval = url::requestToAny('order')[0]['column'];

    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = "order by $orderColoumn $ordertype";
    }

    if ($searchVal != '') {
        $where = " AND username like '%" . $searchVal . "%' OR user_email like '%" . $searchVal . "%' OR user_phone_no like '%" . $searchVal . "%'";
    } else {
        $where = '';
    }

    $pdoQuery = $pdo->prepare("SELECT userid,username,user_email,user_phone_no,userStatus,role_id from " . $GLOBALS['PREFIX'] . "core.Users C where C.ch_id='$cId' and (username != 'hfn' and username != 'admin') $where");
    $pdoQuery->execute();
    $notfCnt = $pdoQuery->fetchAll();
    $totalRecords = safe_count($notfCnt);

    $pdoQuery = $pdo->prepare("SELECT userid,username, firstName, user_email,user_phone_no,userStatus,role_id from " . $GLOBALS['PREFIX'] . "core.Users C where C.ch_id=? and (username != 'hfn' and username != 'admin') $where $orderValues $limit ");
    $pdoQuery->execute([$cId]);
    $customer_res = $pdoQuery->fetchAll();
    if ($totalRecords > 0) {
        foreach ($customer_res as $key => $value) {
            $role_name = getRoleName($value['role_id']);
            $userId = $value["userid"];
            $userStatus = getUserStatusForCustomer($value['userStatus']);
            $userDetails = '<a href="javascript:;" data-target="#user_details_div" onclick="getUserDetail(' . "'$userId'" . ');" data-toggle="modal">Detail</a>';
            $recordList[] = array(
                "DT_RowId" => $value['userid'],
                "username" => ($value['firstName'] != "") ? $value['firstName'] : $value['username'],
                "user_email" => $value['user_email'],
                "user_phone_no" => $value['user_phone_no'],
                "role_id" => $role_name,
                "userStatus" => $userStatus,
                "userDetail" => $userDetails,
            );
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
        return $jsonData;
    } else {
        $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => $recordList);
        return $jsonData;
    }
}

function getUserStatusForCustomer($userStatus)
{
    try {
        $status = '';
        if ($userStatus == 0) {
            $status = 'In Active';
        } else if ($userStatus == 1) {
            $status = 'Active';
        } else {
            $status = '';
        }
        return $status;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getRoleName($role_id)
{
    global $pdo;
    try {
        $pdoQuery = $pdo->prepare("SELECT id,name,value,type FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE type=10 and id=?");
        $pdoQuery->execute([$role_id]);
        $role = $pdoQuery->fetch();
        $roleName = explode("_", $role['name']);
        return $roleName[1];
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getorderListGridData()
{
    global $pdo;

    $cId = url::issetInRequest('cId') ? url::requestToAny('cId') : '';
    $customerType = url::requestToAny('customerType');
    $pId = getProcessByCompany($cId);
    $url = "";
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $limit = " limit $start , $length";
    // $searchVal = url::requestToAny('search')['value'];
    $draw = url::requestToAny('draw');

    $orderval = url::requestToAny('order')[0]['column'];

    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = "order by $orderColoumn $ordertype";
    }

    $wh = '';
    if ($customerType == 1) {
        $entityId = $_SESSION["user"]["cId"];
        $wh = "entityId = '$entityId' and channelId=0 and subchannelId=0";
    }
    if ($customerType == 2) {
        $channelId = $_SESSION["user"]["cId"];
        $wh = "channelId = '$channelId' and subchannelId=0";
    }
    if ($customerType == 3) {
        $subchannelId = $_SESSION["user"]["cId"];
        $wh = "subchannelId = '$subchannelId'";
    }

    if ($customerType == 4) {
        $outsourcedId = $_SESSION["user"]["cId"];
        $wh = "outsourcedId = '$outsourcedId'";
    }

    if ($customerType == 5) {
        $compId = $_SESSION["user"]["cId"];
        $wh = "eid = '$compId'";
    }

    $tempUrl = get_downloadUrl($pId);

    $pdoQuery = $pdo->prepare("SELECT count(id) count  from " . $GLOBALS['PREFIX'] . "agent.customerOrder   where customerNum IN (select customerNo from " . $GLOBALS['PREFIX'] . "agent.channel where $wh and ctype=5)");
    $pdoQuery->execute();
    $cntRes = $pdoQuery->fetch();
    $totalRecords = $cntRes["count"];

    $pdoQuery = $pdo->prepare("SELECT id , orderNum , customerNum , orderDate , processId , compId , downloadId , SKUDesc , SKUNum , coustomerCountry from " . $GLOBALS['PREFIX'] . "agent.customerOrder   where customerNum IN (select customerNo from " . $GLOBALS['PREFIX'] . "agent.channel where " . $wh . " and ctype=5) " . $orderValues . " " . $limit);
    $pdoQuery->execute();
    $customer_res = $pdoQuery->fetchAll();
    if (safe_count($customer_res) > 0) {
        foreach ($customer_res as $key => $val) {

            if ($tempUrl != "No URL Found") {
                $url = $tempUrl . "?id=" . $val['downloadId'];
            } else {
                $url = "No URL Found";
            }

            $tempSkuDtls = get_SkuDetails($val['SKUNum']);
            $sku = $tempSkuDtls[0];
            $type = $tempSkuDtls[1];

            $recordList[] = array(
                "id" => $val['orderNum'],
                "cid" => $val['compId'],
                "DT_RowId" => $val['compId'],
                "orderNum" => $val['orderNum'],
                "customerNum" => $val['customerNum'],
                "type" => $type,
                "orderDate" => date("d-m-Y H:i:s", $val['orderDate']),
                "SKUNum" => $sku,
                "url" => $url,
            );
        }
    } else {
        $recordList[] = array(
            "id" => '',
            "cid" => '',
            "DT_RowId" => '',
            "orderNum" => '',
            "customerNum" => '',
            "type" => 'No Orders Found for this user',
            "orderDate" => '',
            "SKUNum" => '',
            "url" => '',
        );
        $totalRecords = 0;
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
    return $jsonData;
}

function get_Customer_Order_History()
{
    $pdo = pdo_connect();
    $recordList = array();
    $url = '';
    $where = '';
    $orderNum = url::requestToAny('orderNum');
    $custNum = url::requestToAny('custNum');

    $draw = url::requestToAny('draw');
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $limit = " limit $start , $length";

    $pdoQuery = $pdo->prepare("SELECT count(sid) as count from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum =? AND orderNum =? AND revokeStatus = 'I'");
    $pdoQuery->execute([$custNum, $orderNum]);
    $custTotalRes = $pdoQuery->fetch();
    $totalRecords = $custTotalRes['count'];

    $pdoQuery = $pdo->prepare("SELECT sid, serviceTag, installationDate, uninstallDate, downloadStatus , revokeStatus , machineManufacture, machineModelNum, machineOS, macAddress , processId , compId from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum =? AND orderNum = $orderNum AND revokeStatus = 'I' $where $limit");
    $pdoQuery->execute([$custNum]);
    $custRes = $pdoQuery->fetchAll();

    if (safe_count($custRes) > 0) {
        foreach ($custRes as $key => $val) {
            $deatail = "Operating System :" . $val['machineOS'] . " Chassis Manufacturer :" . $val['machineManufacture'] . " Model Number:" . $val['machineModelNum'];
            $installDate = ($val['installationDate'] != '') ? date("d-m-Y H:i:s", $val['installationDate']) : "";
            $uninstallDate = ($val['uninstallDate'] != '') ? date("d-m-Y H:i:s", $val['uninstallDate']) : "";
            $recordList[] = array(
                "id" => $val['sid'],
                "devCID" => $val['compId'],
                "devPID" => $val['processId'],
                "downStatus" => $val['downloadStatus'],
                "revokeStatus" => $val['revokeStatus'],
                "device_id" => $val['serviceTag'],
                "install_date" => $installDate,
                "valid_till" => $uninstallDate,
                "device_info" => $deatail,
            );
        }
    } else {
        $deatail = "";
        $recordList[] = array(
            "id" => "",
            "devCID" => "",
            "devPID" => "",
            "device_id" => "",
            "install_date" => "No service tags available for this order",
            "valid_till" => "",
            "device_info" => "",
        );
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
    return $jsonData;
}

function get_downloadUrl($processId)
{
    global $pdo;
    try {
        $pdoQuery = $pdo->prepare("SELECT downloaderPath from " . $GLOBALS['PREFIX'] . "agent.processMaster where pid =? limit 1");
        $pdoQuery->execute([$processId]);
        $urlRes = $pdoQuery->fetch();

        if ($urlRes['downloaderPath'] != "") {
            return $urlRes['downloaderPath'];
        } else {
            return "No URL Found";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function get_SkuDetails($skuNum)
{
    global $pdo;
    try {
        $skuType = '';
        $pdoQuery = $pdo->prepare("SELECT skuType , skuName FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuRef =? limit 1");
        $pdoQuery->execute([$skuNum]);
        $skuDtlsRes = $pdoQuery->fetch();
        if ($skuDtlsRes['skuType'] == 3) {
            $skuType = 'New';
        } else if ($skuDtlsRes['skuType'] == 8) {
            $skuType = 'Renew';
        } else if ($skuDtlsRes['skuType'] == 10) {
            $skuType = 'Upgrade';
        } else {
            $skuType = 'Not Found';
        }

        return array($skuDtlsRes['skuName'], $skuType);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getContractEndDate()
{
    global $pdo;

    $SKU = url::issetInRequest('SKU') ? url::requestToAny('SKU') : '';

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT id,upgrdSku,renewSku,skuName,skuRef,ppid,licensePeriod from " . $GLOBALS['PREFIX'] . "agent.skuMaster where skuRef=?  limit 1");
    $pdoQuery->execute([$SKU]);
    $resSKU = $pdoQuery->fetch();
    if (safe_count($resSKU) > 0) {

        $noOfDays = $resSKU['licensePeriod'];
        $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    }

    return $contractEDate;
}

function getPasswordId1()
{
    try {

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

function getCompanyname($cId)
{
    global $pdo;

    $pdoQuery = $pdo->prepare("SELECT  companyName from " . $GLOBALS['PREFIX'] . "agent.customerMaster where cId = '" . $cId . "'");
    $pdoQuery->execute();
    $res_user = $pdoQuery->fetch();
    $companyName = $res_user['companyName'];
    return $companyName;
}

function sendNewUserEmail($userName, $userEmail, $passid, $cId)
{
    global $base_url;
    $to = $userEmail;
    $toName = $userName;

    $subject = "Nanoheal new user authentication";

    $resetLink = $base_url . 'reset-password.php?vid=' . $passid;

    $body = "
                <html>
                <body>
                        <div style='color:#0000CC; font-family:Courier New, Courier, monospace; font-size:13px;'>
                                Hi $userName ,
                                <br /><br />
                                Please <a href='" . $resetLink . "' style='color:#CC0000'>click here</a> to set your new password and log into the Nanoheal portal.<br /><br />

                                Note: This is a system generated email. Please do not reply to this email.<br /><br />

                                Thanks, <br />
                                Nanoheal support team
                        </div>
                </body>
                </html>
                ";

    $fromEmail = 'noreply@nanoheal.com';
    $fromName = 'Support';

    $headers = '';
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";

    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $to,
      'subject' => $subject,
      'text' =>'',
      'html' => $body,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
//    if (!mail($to, $subject, $body, $headers)) {
    if (!CURL::sendDataCurl($url, $arrayPost)) {
      return 0;
    } else {
      return 1;
    }
}

function createNewUserDb($userName, $userEmail, $provChk, $dashChk, $userChk, $cId, $pId, $companyList, $roleId, $agentPhoneId, $siteList)
{
    global $pdo;

    $approvdBy = $_SESSION["user"]["adminEmail"];
    $adminId = $_SESSION["user"]["adminid"];
    $role_id = $_SESSION["user"]["role_id"];
    $swId = $_SESSION["user"]["swId"];

    $customerId = 0;

    if ($companyList == 'undefined' || $companyList == '') {
        $customerId = $cId;
    } else {
        $customerId = $companyList;
    }

    $passid = getPasswordId1();
    $msg = '';
    $pdoQuery = $pdo->prepare("SELECT email from " . $GLOBALS['PREFIX'] . "agent.Agent where (email = ? or phone_id = ?)");
    $pdoQuery->execute([$userEmail, $agentPhoneId]);
    $res_user = $pdoQuery->fetchAll();

    $pdoQuery = $pdo->prepare("SELECT crmId from " . $GLOBALS['PREFIX'] . "agent.customerMaster where cId=?");
    $pdoQuery->execute([$cId]);
    $res_custuser = $pdoQuery->fetch;

    $admId = addNewCoreUser($userName, $userEmail, $userEmail, $siteList);

    $addSite = 0;
    $addCust = 0;
    $addDevice = 0;
    $entitlement = 0;
    if (safe_count($res_user) == 0 && $admId != 0) {

        if ($provChk == 1 || $provChk == '1') {

            $addDevice = 1;
            $entitlement = 1;
        }

        $time = time();
        $sql_ser = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.Agent set adminId =?,swId=?, cid = '$cId', pid =?, first_name = '" . $userName . "', email = '" . $userEmail . "',phone_id='" . $agentPhoneId . "', approvedBy = '" . $approvdBy . "', provisionAccess = '" . $provChk . "', dashboardAccess = '" . $dashChk . "' ,addDevice=?,addSite=?,addCust=?,entitlement=?, userStatus = '1',userType='0',role_id=?,resetsession='" . $passid . "',resetPassid='1',customerId=?,passwordResetDate=?  ");
        $sql_ser->execute([$admId, $swId, $pId, $addDevice, $addSite, $addCust, $entitlement, $roleId, $customerId, $time]);

        if ($provChk == 1 || $dashChk == 1) {
            $result = $pdo->lastInsertId();
            $provAgentId = mysqli_insert_id();

            $emailStatus = sendNewUserEmail($userName, $userEmail, $passid, $cId);
            $msg = 'DONE';
        } else {
            $provAgentId = '';
            $msg = 'NOTDONE';
        }
    } else {
        $msg = 'DUPLICATE';
    }

    return $msg;
}

function addUserCoreChannel()
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    $eid = $_SESSION["user"]["cId"];
    $name = url::issetInRequest('userName') ? url::requestToAny('userName') : '';
    $lastName = url::issetInRequest('lastname') ? url::requestToAny('lastname') : '';
    $userEmail = url::issetInRequest('userEmail') ? url::requestToAny('userEmail') : '';
    $roleId = url::issetInRequest('roleId') ? url::requestToAny('roleId') : '';
    $userPhone = url::issetInRequest('agentCorpId') ? url::requestToAny('agentCorpId') : '';
    $sitelist = url::issetInRequest('sitelist') ? url::requestToAny('sitelist') : '';
    $entityId = url::issetInRequest('entityId') ? url::requestToAny('entityId') : '0';
    $channelId = url::issetInRequest('channelId') ? url::requestToAny('channelId') : '0';
    $subchannelId = url::issetInRequest('entityId') ? url::requestToAny('subchannelId') : '0';
    $customerId = url::issetInRequest('customerId') ? url::requestToAny('customerId') : '0';
    $userType = url::issetInRequest('userType') ? url::requestToAny('userType') : '0';
    if ($entityId == 'null') {
        $entityId = '0';
    }
    if ($channelId == 'null') {
        $channelId = '0';
    }
    if ($subchannelId == 'null') {
        $subchannelId = '0';
    }
    if ($customerId == 'null') {
        $customerId = '0';
    }
    global $pdo;

    $pdo = pdo_connect();

    $cksum = md5(mt_rand());

    try {
        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where user_email='" . $userEmail . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 'DUPLICATE';
        } else {

            $resetId = getDownloadId1();
            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,entity_id,channel_id,subch_id,customer_id,role_id,username,firstName,lastName, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
            VALUES ( '$eid','" . $entityId . "','" . $channelId . "','" . $subchannelId . "','" . $customerId . "','$roleId','" . $name . "','" . $name . "','" . $lastName . "','','" . $userEmail . "','" . $userPhone . "','$userType' ,'', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
            $sql_user->execute([$resetId]);
            $result_user = $pdo->lastInsertId();
            if ($result_user) {
                $adminId = mysqli_insert_id();
                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $name . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();

                $sites = explode(",", $sitelist);

                foreach ($sites as $id) {

                    $site = addUserSites($id);
                    $sitename = $site['siteName'];
                    $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $name . "', customer = '" . $sitename . "', sitefilter = '0', owner = '0'");
                    $ins_customer->execute();
                    $cust_result = $pdo->lastInsertId();
                }

                $res = sendTrialCust($name, $userEmail, $resetId);

                return 'DONE';
            } else {
                return 'NOTDONE';
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function updateUserCoreChannel()
{

    $eid = $_SESSION["user"]["cId"];
    $userid = url::issetInRequest('userid') ? url::requestToAny('userid') : '';
    $name = url::issetInRequest('userName') ? url::requestToAny('userName') : '';
    $lname = url::issetInRequest('lastname') ? url::requestToAny('lastname') : '';
    $userEmail = url::issetInRequest('userEmail') ? url::requestToAny('userEmail') : '';
    $roleId = url::issetInRequest('roleId') ? url::requestToAny('roleId') : '';
    $userPhone = url::issetInRequest('agentCorpId') ? url::requestToAny('agentCorpId') : '';
    $sitelist = url::issetInRequest('sitelist') ? url::requestToAny('sitelist') : '';
    $entityId = url::issetInRequest('entityId') ? url::requestToAny('entityId') : '0';
    $channelId = url::issetInRequest('channelId') ? url::requestToAny('channelId') : '0';
    $subchannelId = url::issetInRequest('entityId') ? url::requestToAny('subchannelId') : '0';
    $customerId = url::issetInRequest('customerId') ? url::requestToAny('customerId') : '0';
    $userType = url::issetInRequest('userType') ? url::requestToAny('userType') : '0';
    if ($entityId == 'null') {
        $entityId = '0';
    }
    if ($channelId == 'null') {
        $channelId = '0';
    }
    if ($subchannelId == 'null') {
        $subchannelId = '0';
    }
    if ($customerId == 'null') {
        $customerId = '0';
    }
    global $pdo;

    $pdo = pdo_connect();

    try {

        $sql_user = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users SET firstName = '" . $name . "',lastName = '" . $lname . "', entity_id = '" . $entityId . "', channel_id = '" . $channelId . "', subch_id = '" . $subchannelId . "' ,customer_id = '" . $customerId . "',role_id = '" . $roleId . "'  WHERE userid = '" . $userid . "'");
        $sql_user->execute();
        $result_user = $pdo->lastInsertId();

        $del_customer = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username = '" . $name . "'");
        $del_customer->execute();
        $del_result = $pdo->lastInsertId();
        if ($result_user) {
            $sites = explode(",", $sitelist);
            foreach ($sites as $id) {

                $site = addUserSites($id);
                $sitename = $site['siteName'];

                $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $name . "', customer = '" . $sitename . "', sitefilter = '0', owner = '0'");
                $ins_customer->execute();
                $cust_result = $pdo->lastInsertId();
            }

            return 'DONE';
        } else {
            return 'NOTDONE';
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addUserSites($siteId)
{

    global $pdo;

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder where id in (?) and siteName != ''");
    $pdoQuery->execute([$siteId]);
    $res_site = $pdoQuery->fetch();
    return $res_site;
}

function addNewCoreUser($userName, $notify_mail, $report_mail, $sitelist)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $adminName = $_SESSION["user"]["username"];
    $cksum = md5(mt_rand());

    try {
        $userName = str_replace(' ', '_', $userName);

        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $userName . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {

            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (username, password, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css)
        VALUES ( '" . $userName . "', MD5('" . $userName . "'), '" . $notify_mail . "', '" . $report_mail . "', 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '')");
            $sql_user->execute();
            $result_user = $pdo->lastInsertId();

            $adminId = mysqli_insert_id();

            if ($result_user) {

                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $userName . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                // $result_ck = $pdo->lastInsertId();

                $sites = explode(",", $sitelist);
                foreach ($sites as $site) {

                    if ($site != '0') {
                        $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $userName . "', customer = '" . $site . "', sitefilter = '0', owner = '1', notify_sender = '" . $notify_mail . "'");
                        $ins_customer->execute();
                        // $cust_result = $pdo->lastInsertId();
                    }
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

function validatePassword($password, $repassword, $resetSession)
{

    global $pdo;

    $msg = "";
    if ($password == '') {
        $msg = '* Please enter password.';
    } else if ($repassword == '') {
        $msg = '* Please enter reset password.';
    } else if ($password != $repassword) {
        $msg = '* The password & confirm password are not same.';
    } else if (mb_strlen($password) < 8) {
        $msg = '* Password field should be atlease 8 characters.';
    } else if (!preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)) {
        $msg = '* password must contain numeric and special character.';
    }
    $valid = true;
    if ($msg == "") {
        $pdoQuery = $pdo->prepare("SELECT passwordHistory from " . $GLOBALS['PREFIX'] . "agent.Agent where resetsession = ?");
        $pdoQuery->execute([$resetSession]);
        $res_passhistory = $pdoQuery->fetch();
        $passHistory = $res_passhistory['passwordHistory'];
        if ($passHistory != '') {
            $passHistory_array = explode(",", $passHistory);
            $passCount = safe_count($passHistory_array);
            if ($passCount > 9) {
                $passHistory_array = array_slice($passHistory_array, -8, 8);
            }

            $encrypt_method = "AES-256-CBC";
            $secret_key = 'jfsibm';
            $secret_iv = 'jfsibm@nanoheal.com';
            $key = hash('sha256', $secret_key);
            $iv = substr(hash('sha256', $secret_iv), 0, 16);

            if ($passCount > 0) {
                for ($i = 0; $i <= safe_count($passHistory_array); $i++) {
                    $decrypted_string = openssl_decrypt(base64_decode($passHistory_array[$i]), $encrypt_method, $key, 0, $iv);
                    if ($decrypted_string == $password) {
                        $valid = false;
                    }
                }
            } else {
                $valid = true;
            }
        } else {
            $valid = true;
        }
        if ($valid == false) {
            $msg = '* You have already used that password, try another.';
        } else {
            $msg = 'DONE';
        }
        return $msg;
    } else {
        return $msg;
    }
}

function changePassword($oldpassword, $password, $userEmail)
{
    global $pdo;
    $msg = "";

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'jfsibm';
    $secret_iv = 'jfsibm@nanoheal.com';

    $key = hash('sha256', $secret_key);

    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    $newPwd = openssl_encrypt($oldpassword, $encrypt_method, $key, 0, $iv);
    $encryptedPwd = base64_encode($newPwd);

    $newPasswordencode = openssl_encrypt($password, $encrypt_method, $key, 0, $iv);
    $newPassword = base64_encode($newPasswordencode);

    if (!preg_match_all('$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[\d])(?=\S*[\W])\S*$', $password)) {
        echo $msg = '0';
        return;
    }

    $pdoQuery = $pdo->prepare("SELECT passwordHistory from " . $GLOBALS['PREFIX'] . "agent.Agent where email = ? and  password=?");
    $pdoQuery->execute([$userEmail, $encryptedPwd]);
    $res_passhistory = $pdoQuery->fetch();
    $passHistory = $res_passhistory['passwordHistory'];
    $passHistory_array = explode(",", $passHistory);

    $arr_count = safe_count($passHistory_array);
    if ($arr_count == 1) {

        $msg = '-1';
    } else {

        $encrypt_method = "AES-256-CBC";
        $secret_key = 'jfsibm';
        $secret_iv = 'jfsibm@nanoheal.com';
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $curDate = date("Y-m-d H:i:s");
        $unixdate = strtotime(date("Y-m-d H:i:s", strtotime($curDate)) . " +90 day");

        $valid = true;
        for ($i = 0; $i <= safe_count($passHistory_array); $i++) {
            $decrypted_string = openssl_decrypt(base64_decode($passHistory_array[$i]), $encrypt_method, $key, 0, $iv);
            if ($decrypted_string == $password) {
                $valid = false;
            }
        }
        if ($valid == false) {
            $msg = '3';
        } else {

            $updSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.Agent SET password = '" . $newPassword . "' , passwordHistory = concat(passwordHistory,'," . $newPassword . "'), passwordDate =? where email =?");
            $updSql->execute([$unixdate, $userEmail]);
            $resetRes = $pdo->lastInsertId();
            if ($resetRes) {
                $msg = '1';
            } else {
                $msg = '2';
            }
        }
    }
    return $msg;
}

function updatePassword($resetsession, $pwd)
{
    $db = pdo_connect();
    $pwd = urldecode($pwd);
    // url decode of + is an empty space so replace empty space with  +  and then pass to hash it.
    if (strpos($pwd, ' ') !== false) {
        $pwd = str_replace(' ', '+', $pwd);
    }    
    $mdPass = password_hash($pwd, PASSWORD_DEFAULT);
    $timestamp = time();
    $timestamp1 = strtotime('+90 day', $timestamp);

    $checkPwdSql = "SELECT password, passwordHistory, username, user_email, firstName, lastName FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userKey=? limit 1";
    $bindings = array($resetsession);
    $pdo = $db->prepare($checkPwdSql);
    $pdo->execute($bindings);
    $checkPwdRes = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($checkPwdRes) > 0) {
        $currentPwd = $checkPwdRes["password"];
        $pwdHistory = $checkPwdRes["passwordHistory"];
        $pwdHistArray = explode(',', $pwdHistory);
        $username = $checkPwdRes['username'];
        $user_email = $checkPwdRes['user_email'];
        $fname = $checkPwdRes['firstName'];
        $lname = $checkPwdRes['lastName'];

        if (strlen(trim($pwd)) < 8) {
            return ["error" => "Password should be minimum 8 characters"];
        }

        if (strlen(trim($pwd)) > 255) {
            return ["error" => "Password should be maximum 255 characters"];
        }

        if ($user_email === $pwd || strtolower($user_email) === strtolower($pwd)) {
            return ["error" => "Password cannot contains user's email"];
        }

        if (!checkRegPassword($pwd)) {
            return ["error" => "Password should be contain lowercase letters, uppercase letters, numbers, and special characters"];
        }

        if (safe_count($pwdHistArray) > 4) {
            unset($pwdHistArray[0]);
            $pwdHistory = implode(",", $pwdHistArray);
        }

        if (password_verify($pwd, $currentPwd)) {
            return ["error" => 'This password is already in use, please use different password.'];
        } else {
            foreach ($pwdHistArray as $pswd) {
                if (password_verify($pwd, $pswd)) {
                    return ["error" => 'This password is already in use, please use different password.'];
                }
            }
        }

        if ($fname != '') {
            if (preg_match("/$fname/i", $pwd)) {
                return ["error" => "Password cannot contains user's first name"];
            }
        }
        if ($lname != '') {
            if ((strlen($lname) > 3) && preg_match("/$lname/i", $pwd)) {
                return ["error" => "Password cannot contains user's last name"];
            }
        }
        if ($pwdHistory != "") {
            $updatedPwdHist = $pwdHistory . ',' . $mdPass;
        } else {
            $updatedPwdHist = $mdPass;
        }

        $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET password=?,passwordDate=?,userStatus='1',passwordHistory=?, userKey='' where userKey=?";
        $bindings = array($mdPass, $timestamp1, $updatedPwdHist, $resetsession);
        $pdo = $db->prepare($updSql);
        $resetRes = $pdo->execute($bindings);

        if ($resetRes) {
            return ["success" => true];
        } else {
            return ["error" => "password could not be updated"];
        }
    } else {
        return ["error" => "Can't update password, invalid session"];
    }
}
function updatePassword_old_del($resetsession, $pwd)
{
    $db = pdo_connect();
    $mdPass = md5($pwd);
    $timestamp = time();
    $timestamp1 = strtotime('+90 day', $timestamp);

    $checkPwdSql = "SELECT password, passwordHistory,username,firstName,lastName FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userKey=? limit 1";
    $bindings = array($resetsession);
    $pdo = $db->prepare($checkPwdSql);
    $pdo->execute($bindings);
    $checkPwdRes = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($checkPwdRes) > 0) {
        $currentPwd = $checkPwdRes["password"];
        $pwdHistory = $checkPwdRes["passwordHistory"];
        $pwdHistArray = explode(',', $pwdHistory);
        $username = $checkPwdRes['username'];
        $fname = $checkPwdRes['firstName'];
        $lname = $checkPwdRes['lastName'];

        if (safe_count($pwdHistArray) > 4) {
            unset($pwdHistArray[0]);
            $pwdHistory = implode(",", $pwdHistArray);
        }

        if ($currentPwd == $mdPass) {
            return 2;
        } else if (in_array($mdPass, $pwdHistArray)) {
            return 2;
        }

        if ($fname != '') {
            if (preg_match("/$fname/i", $pwd)) {
                return 4;
            }
        }
        if ($lname != '') {
            if ((strlen($lname) > 3) && preg_match("/$lname/i", $pwd)) {
                return 5;
            }
        }
        if ($pwdHistory != "") {
            $updatedPwdHist = $pwdHistory . ',' . $mdPass;
        } else {
            $updatedPwdHist = $mdPass;
        }

        $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET password=?,passwordDate=?,userStatus='1',passwordHistory=?, userKey='' where userKey=?";
        $bindings = array($mdPass, $timestamp1, $updatedPwdHist, $resetsession);
        $pdo = $db->prepare($updSql);
        $resetRes = $pdo->execute($bindings);

        if ($resetRes) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 6;
    }
}

function set_pass_reset($resetsession, $pwd)
{
    global $pdo;

    $mdPass = md5($pwd);
    $timestamp = time();
    $timestamp1 = strtotime('+1111 day', $timestamp);

    if ($resetsession) {
        $_SESSION["signup"]["vcode"] = $resetsession;
        $_SESSION["signup"]["password"] = $mdPass;
        return 1;
    } else {
        $_SESSION["signup"]["vcode"] = '';
        $_SESSION["signup"]["password"] = '';
        return 0;
    }
}

function regenerateUserPassword($useremail)
{
    global $pdo;
    $passid = getPasswordId1();
    $now = time();

    $pdoQuery = $pdo->prepare("SELECT cid, first_name FROM Agent where email = '" . $useremail . "' limit 1");
    $pdoQuery->execute();
    $sqlRes = $pdoQuery->fetch();

    $userName = $sqlRes['first_name'];
    $customerId = $sqlRes['cid'];

    sendNewUserEmail($userName, $useremail, $passid, $customerId);

    $reSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.Agent SET resetsession='" . $passid . "', passwordResetDate='" . $now . "' where email='" . $useremail . "'");
    $reSql->execute();
    $reSqlRes = $pdo->lastInsertId();

    if ($reSqlRes) {
        return 1;
    } else {
        return 0;
    }
}

function checkRegPassword($pwd): bool
{

    //for digit
    $i = 0;
    $digitpattern = '/(\d)/';

    //For lower case
    $lowerpattern = '/([a-z])/';

    //For upper case
    $upperpattern = '/([A-Z])/';

    // For symbols
    $symbolpattern = '/([^a-zA-Z0-9\s])/';

    if (preg_match($digitpattern, $pwd)) {
        $i++;
    }
    if (preg_match($lowerpattern, $pwd)) {
        $i++;
    }
    if (preg_match($upperpattern, $pwd)) {
        $i++;
    }
    if (preg_match($symbolpattern, $pwd)) {
        $i++;
    }
    if ($i >= 3) {
        return true;
    } else {
        return false;
    }
}

function checkvid($resetSession)
{
    $db = pdo_connect();
    $resetSql = "select userid,passwordDate,username,user_email from " . $GLOBALS['PREFIX'] . "core.Users where userKey=? limit 1";
    $bindings = array($resetSession);
    $pdo = $db->prepare($resetSql);
    $pdo->execute($bindings);
    $resetRes = $pdo->fetch(PDO::FETCH_ASSOC);
    $count = safe_count($resetRes);


    if ($count > 0 && isset($resetRes['user_email'])) {
        return 'DONE##' . $resetRes['user_email'];
    } else {
        return 'NOTDONE##' . 'Invalid case';
    }
}

function chec_signkvid($resetSession)
{
    global $pdo;

    $msg = '';
    $pdoQuery = $pdo->prepare("SELECT email from " . $GLOBALS['PREFIX'] . "agent.signup where vCode=? limit 1");
    $pdoQuery->execute([$resetSession]);
    $resetRes = $pdoQuery->fetch();
    $count = safe_count($resetRes);
    if ($count > 0) {
        return 'DONE##' . $resetRes['user_email'];
    } else {
        return 'NOTDONE##' . 'Invalid case';
    }
}

function getAdminUsers()
{

    global $pdo;

    $pdoQuery = $pdo->prepare("SELECT id,email from " . $GLOBALS['PREFIX'] . "agent.Agent where userType=2 or swId !='0'");
    $pdoQuery->execute();
    $agent_res = $pdoQuery->fetchAll();
    $option = '';
    foreach ($agent_res as $value) {
        $option .= '<option id="' . $value['id'] . '" value="' . $value['id'] . '" >' . $value['email'] . '</option>';
    }
    $finalStr = $option;

    return $finalStr;
}

function getProcessListbyswId($swId)
{

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT pId,description,processName from " . $GLOBALS['PREFIX'] . "agent.processMaster where swId =?");
    $pdoQuery->execute([$swId]);
    $res = $pdoQuery->fetchAll();
    $str = '';
    foreach ($res as $value) {

        $str .= "<option value='" . $value['pId'] . "' selected>" . $value['processName'] . "</option>";
    }

    return $str;
}

function getSKUListbyswId($swId)
{

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT skuList from " . $GLOBALS['PREFIX'] . "agent.serviceMaster where sid =? limit 1");
    $pdoQuery->execute([$swId]);
    $res = $pdoQuery->fetch();
    $skures = $res['skuList'];

    $pdoQuery = $pdo->prepare("SELECT id,displayName,value from " . $GLOBALS['PREFIX'] . "agent.fieldValues where id in (?)");
    $pdoQuery->execute([$skures]);
    $fld_res = $pdoQuery->fetchAll();

    $str = '';
    $i = 0;
    $str = "<option value='0' selected>Please select SKU </option>";
    foreach ($fld_res as $value) {
        $str .= "<option value='" . $value['value'] . "'>" . $value['displayName'] . "</option>";
    }
    return $str;
}

function getUserRights()
{

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT id,name,value,type FROM Options WHERE type=10");
    $pdoQuery->execute();
    $role = $pdoQuery->fetchAll();
    $roleList = '';
    foreach ($role as $value) {
        $roleName = explode("_", $value['name']);
        $roleList .= "<option value='" . $value['id'] . "'>" . ucfirst($roleName[1]) . "</option>";
    }

    return $roleList;
}

function getUserEeditRights($roleId)
{

    $pdo = pdo_connect();

    $ctype = $_SESSION["user"]["customerType"];

    $pdoQuery = $pdo->prepare("SELECT id,name,value,type FROM Options WHERE type=10");
    $pdoQuery->execute();
    $role = $pdoQuery->fetchAll();
    $roleList = '';
    foreach ($role as $value) {
        $roleName = explode("_", $value['name']);
        if ($ctype != 4) {
            if ($value['id'] == $roleId) {
                $roleList .= "<option value='" . $value['id'] . "' selected>" . ucfirst($roleName[1]) . "</option>";
            } else {
                $roleList .= "<option value='" . $value['id'] . "'>" . ucfirst($roleName[1]) . "</option>";
            }
        } else if ($ctype == 4 && $roleName[1] == 'outsource') {
            $roleList .= "<option value='" . $value['id'] . "'>" . ucfirst($roleName[1]) . "</option>";
        }
    }

    return $roleList;
}

function getUserDtl($uId)
{

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Users where userid =? limit 1");
    $pdoQuery->execute([$uId]);
    $res_agent = $pdoQuery->fetch();
    $count = safe_count($res_agent);
    if ($count > 0) {
        return $res_agent;
    } else {
        return null;
    }
}

function updateUserDtl()
{

    $userName = url::issetInRequest('userName') ? url::requestToAny('userName') : '';
    $userEmail = url::issetInRequest('userEmail') ? url::requestToAny('userEmail') : '';
    $roleId = url::issetInRequest('roleId') ? url::requestToAny('roleId') : '';
    $uid = url::issetInRequest('uid') ? url::requestToAny('uid') : '';
    $agentPhoneId = url::issetInRequest('editagentCorpId') ? url::requestToAny('editagentCorpId') : '';
    $uStatusId = url::issetInRequest('uStatusId') ? url::requestToAny('uStatusId') : '';
    $sitelist = url::issetInRequest('sitelist') ? url::requestToAny('sitelist') : '';

    $pdo = pdo_connect();

    $updSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users SET role_id=?,user_phone_no='" . $agentPhoneId . "' where  userid=?");
    $updSql->execute([$roleId, $uid]);
    $resetRes = $pdo->lastInsertId();
    if ($resetRes) {

        $pdoQuery = $pdo->prepare("SELECT U.username from " . $GLOBALS['PREFIX'] . "core.Users U where U.userid=? limit 1");
        $pdoQuery->execute([$uid]);
        $res_c = $pdoQuery->fetch();
        $cUsername = $res_c['username'];

        $del_customer = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.Customers where username =?");
        $del_customer->execute([$cUsername]);
        $del_result = $pdo->lastInsertId();

        $sites = explode(",", $sitelist);
        foreach ($sites as $site) {

            if ($site != '0') {

                $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $cUsername . "', customer = '" . $site . "', sitefilter = '0', owner = '0'");
                $ins_customer->execute();
                $cust_result = $pdo->lastInsertId();
            }
        }

        return 'User details updated successfully';
    } else {
        return 'Error to update user details please try again';
    }
}

function deleteUserDtl()
{
    try {
        $uid = url::issetInRequest('uid') ? url::requestToAny('uid') : '';
        $logged_uid = $_SESSION["user"]["userid"];

        if ($uid == $logged_uid) {
            return "Not having enough rights to delete selected user";
        } else if ($uid != '' && $uid != 'undefined') {
            $pdo = pdo_connect();

            $agent_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_deleted (`userid`,`username`,`password`) select userid , username , password from " . $GLOBALS['PREFIX'] . "core.Users where userid =?");
            $agent_sql->execute([$uid]);
            $agent_result = $pdo->lastInsertId();

            if ($agent_result) {
                $agentdel_sql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
                $agentdel_sql->execute([$uid]);
                $resetRes = $pdo->lastInsertId();
                if ($resetRes) {
                    return 'User deleted successfully';
                } else {
                    return 'Error to delete user details please try again';
                }
            } else {
                return 'Error to delete user details please try again';
            }
        } else {
            return 'Error to delete user details please try again';
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getUserRole()
{

    global $pdo;

    $pdo = pdo_connect();

    $roleId = 0;
    try {

        $pdoQuery = $pdo->prepare("SELECT id,name from " . $GLOBALS['PREFIX'] . "core.Options where name='user_superadmin' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            $roleId = $res_core['id'];
        } else {

            $roleId = 0;
        }

        return $roleId;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function getUserRoleByName($roleName)
{

    global $pdo;

    $pdo = pdo_connect();

    $roleId = 0;
    try {

        $pdoQuery = $pdo->prepare("SELECT id,name from " . $GLOBALS['PREFIX'] . "core.Options where name=? limit 1");
        $pdoQuery->execute([$roleName]);
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            $roleId = $res_core['id'];
        } else {

            $roleId = 0;
        }

        return $roleId;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function sendTrialCust($userName, $userEmail, $passid)
{

    global $base_url;
    $to = $userEmail;
    $toName = $userName;

    $subject = "Nanoheal new user authentication";

    $resetLink = $base_url . 'reset-password.php?vid=' . $passid;

    $body = "
                            <html>
                            <body>
                                    <div style='color:#0000CC; font-family:Courier New, Courier, monospace; font-size:13px;'>
                                            Hi $userName ,
                                            <br /><br />
                                            Please <a href='" . $resetLink . "' style='color:#CC0000'>click here</a> to set your new password and log into the Nanoheal portal.<br /><br />

                                            Note: This is a system generated email. Please do not reply to this email.<br /><br />

                                            Thanks, <br />
                                            Nanoheal support team
                                    </div>
                            </body>
                            </html>
                            ";

    $fromEmail = 'noreply@nanoheal.com';
    $fromName = 'Support';

    $headers = '';
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";
//    if (!mail($to, $subject, $body, $headers)) {
    // send from visualisationService
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $to,
      'subject' => $subject,
      'text' =>'',
      'html' => $body,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
  if (!CURL::sendDataCurl($url, $arrayPost)) {
  return 0;
    } else {
        return 1;
    }
}

function addMSPCustomer()
{

    global $base_url;
    $pdo = pdo_connect();

    $customerName = url::issetInRequest('customerName') ? url::requestToAny('customerName') : '';
    $customerEmail = url::issetInRequest('customerEmail') ? url::requestToAny('customerEmail') : '';
    $companyName = url::issetInRequest('companyName') ? url::requestToAny('companyName') : '';
    $skulist = url::issetInRequest('skulist') ? url::requestToAny('skulist') : '';
    $swId = $_SESSION["user"]["swId"];
    $pid = $_SESSION["user"]["pid"];
    $admin_email = $_SESSION["user"]["adminEmail"];
    $loginBy = 'email';
    $coreCompany = str_replace(' ', '', $companyName);

    $time = time();
    $cendDate = strtotime('+367 day', time());
    $site = str_replace(' ', '_', strtolower($customerName));

    $pdoQuery = $pdo->prepare("SELECT swId,companyName,contactPerson,emailId from " . $GLOBALS['PREFIX'] . "agent.customerMaster where companyName=? or emailId = ?  limit 1");
    $pdoQuery->execute([$companyName, $customerEmail]);
    $res_comp = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT id,first_name,phone_id,email from " . $GLOBALS['PREFIX'] . "agent.Agent where email=? limit 1");
    $pdoQuery->execute([$customerEmail]);
    $res_agent = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT parameters,reg_code,startDate,endDate,url,sitename from " . $GLOBALS['PREFIX'] . "agent.RegCode where parameters=? limit 1");
    $pdoQuery->execute([$site]);
    $res_regcode = $pdoQuery->fetch();

    if (safe_count($res_comp) > 0 || safe_count($res_agent) > 0) {

        if (safe_count($res_comp) > 0) {
            echo "Company name already registred.";
        } elseif (safe_count($res_agent) > 0) {
            echo "Email address already registred.";
        } elseif (safe_count($res_regcode) > 0) {
            echo "Site name already exists.";
        } else {
            echo "Error occured to create customer.";
        }
    } else {

        try {

            $cust_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.customerMaster SET swId = ?, crmId = '0', companyName = '" . $companyName . "',contactPerson = '" . $customerName . "',emailId = '" . $customerEmail . "',serverLabel = '" . $companyName . "',loginBy = '" . $loginBy . "',createdDate = '" . $time . "',endDate='" . $cendDate . "',status=1,pcCount='0',showCustNo='1',showOrdNo='1',addDevice='0',addSite='0',addCust='0',entitlement='1',customerType='2'");
            $cust_sql->execute([$swId]);
            $cust_result = $pdo->lastInsertId();

            $customerId = mysqli_insert_id();
            if ($cust_result) {

                $year = date("Y");
                $custNo = $year . '000' . $customerId;
                $updateCust = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.customerMaster set customerNo=? where cId=?");
                $updateCust->execute([$custNo, $customerId]);
                $cust_update = $pdo->lastInsertId();

                $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.processMaster where pId=? limit 1");
                $pdoQuery->execute([$pid]);
                $res_pross = $pdoQuery->fetch();
                if (safe_count($res_pross) > 0) {
                    $dbip = $res_pross['DbIp'];
                    $sql_pross = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster ( swId, cId, processName, description, siteCode, metaSiteName, createdDate, DbIp, serverId, siteCreate, dateCheck, backupCheck, sendMail, "
                        . "serverUrl, phoneNo, replyEmailId, chatLink, serviceLink, privacyLink,"
                        . " deployPath32, deployPath64, setupName32, setupName64, androidsetup, downloaderPath, logoName, folnDarts,"
                        . " downType, downlrName, profileName, andProfileName, macProfileName, FtpConfUrl, WsServerUrl, status) VALUES "
                        . " ( 0, '$customerId', '" . $companyName . "', '" . $res_pross['description'] . "', "
                        . " '" . $res_pross['siteCode'] . "', '" . $res_pross['metaSiteName'] . "', "
                        . " '$time', '" . $res_pross['DbIp'] . "', '" . $res_pross['serverId'] . "', "
                        . " '" . $res_pross['siteCreate'] . "', '" . $res_pross['dateCheck'] . "', "
                        . " '" . $res_pross['backupCheck'] . "', '" . $res_pross['sendMail'] . "',"
                        . " '" . $res_pross['serverUrl'] . "', '" . $res_pross['phoneNo'] . "', '" . $res_pross['replyEmailId'] . "', "
                        . " '" . $res_pross['chatLink'] . "', '" . $res_pross['serviceLink'] . "', '" . $res_pross['privacyLink'] . "',"
                        . " '" . $res_pross['deployPath32'] . "', '" . $res_pross['deployPath64'] . "', '" . $res_pross['setupName32'] . "', '" . $res_pross['setupName64'] . "',"
                        . " '" . $res_pross['androidsetup'] . "', '" . $res_pross['downloaderPath'] . "', '" . $res_pross['logoName'] . "', '" . $res_pross['folnDarts'] . "',"
                        . " '" . $res_pross['downType'] . "', '" . $res_pross['downlrName'] . "', '" . $res_pross['profileName'] . "', "
                        . " '" . $res_pross['andProfileName'] . "', '" . $res_pross['macProfileName'] . "', '" . $res_pross['FtpConfUrl'] . "', '" . $res_pross['WsServerUrl'] . "', 1);");
                    $sql_pross->execute();
                    $result_pross = $pdo->lastInsertId();
                    $pId = mysqli_insert_id();

                    $adminId = addTrialCoreUser($coreCompany, $customerEmail, $customerEmail, $site);

                    $roleId = getUserRole();

                    $startDate = time();
                    $endDate = strtotime('+367 day', time());
                    $REPORT_URL = 'https://' . $dbip . ':443/main/rpc/rpc.php';

                    $reg_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.RegCode SET cId=?,pId=?,parameters='" . $site . "',startDate= '" . $startDate . "',endDate='" . $endDate . "',url='" . $REPORT_URL . "',sitename='" . $site . "'");
                    $reg_sql->execute([$customerId, $pId]);
                    $reg_result = $pdo->lastInsertId();
                    $sitId = mysqli_insert_id();

                    $skuItems = explode(",", $skulist);

                    foreach ($skuItems as $value) {
                        $sqlQry_custSku = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.custSkuMaster SET cId=?, pId=?, skuId=?, pcNo='0', siteId=?,createdDate=?, status=1");
                        $sqlQry_custSku->execute([$customerId, $pId, $value, $sitId, $startDate]);
                        $result_custSku = $pdo->lastInsertId();
                    }

                    $customerOrder = getAutoOrderNo();
                    $resetId = getDownloadId1();
                    $time = time();
                    $sql_agent = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.Agent ( adminId, swId, cid, pid, first_name,"
                        . " email, approvedBy, userType, userPermission,"
                        . " userStatus, provisionAccess, dashboardAccess, role_id, passChange, addDevice, "
                        . " addSite, addCust, entitlement, resetPassid, resetsession, passwordHistory,passwordResetDate,"
                        . " passwordThreshold,loginStatus,customerId) "
                        . " VALUES (?, '0', ?, ?,  '" . $companyName . "', "
                        . " '" . $customerEmail . "', '" . $admin_email . "', 2, NULL,"
                        . " 1, 1, 1, ?, NULL, 1, 1, 1, 1, 1, '" . $resetId . "', '',?,"
                        . " 0,  0,?)");
                    $sql_agent->execute([$adminId, $customerId, $pId, $roleId, $time, $customerId]);
                    $result_agent = $pdo->lastInsertId();

                    if ($result_agent) {

                        $agentId = mysqli_insert_id();
                        $update_agent = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.Agent SET customerId = CONCAT(customerId,','," . $customerId . ") WHERE email =?");
                        $update_agent->execute([$admin_email]);
                        $agent_result = $pdo->lastInsertId();

                        $comList1 = $_SESSION["user"]["customerId"];
                        $comList = $comList1 . ',' . $customerId;
                        $_SESSION["user"]["customerId"] = $comList;

                        $dUrl = '';
                        $mailStatus = '';
                        $finStatusMsg = "%%NOTDONE%%DATABASEDONE%%" . $companyName . " created successfully.<br><span style='color:#FB1962;'></span>%%" . $dUrl . "%%" . $mailStatus . "%%";

                        sendTrialCust($customerName, $customerEmail, $resetId);
                        return $finStatusMsg;
                    } else {
                        $finStatusMsg = "%%NOTDONE%%DATABASEDONE%%Fail to " . $companyName . ".<br><span style='color:#FB1962;'></span>%%" . $dUrl . "%%" . $mailStatus . "%%";

                        return $finStatusMsg;
                    }
                } else {
                    return 'PMFail';
                }
            } else {
                return 'Customer Fail';
            }
        } catch (Exception $ex) {
            logs::log(__FILE__, __LINE__, $ex, 0);
        }
    }
}

function getProvisionUrls()
{

    global $base_url;
    $pdo = pdo_connect();

    $pId = $_SESSION["user"]["pid"];
    $cId = $_SESSION["user"]["cId"];

    $customerType = $_SESSION["user"]["customerType"];

    if ($customerType == 1) {
        $entityId = $_SESSION["user"]["cId"];
        $wh = "entityId = '$entityId'";
    }
    if ($customerType == 2) {
        $channelId = $_SESSION["user"]["cId"];
        $wh = "channelId = '$channelId'";
    }
    if ($customerType == 3) {
        $subchannelId = $_SESSION["user"]["cId"];
        $wh = "subchannelId = '$subchannelId'";
    }

    if ($customerType == 4) {
        $outsourcedId = $_SESSION["user"]["cId"];
        $wh = "outsourcedId = '$outsourcedId'";
    }

    if ($customerType == 5) {
        $compId = $_SESSION["user"]["cId"];
        $wh = "compId = '$compId'";
    }
    if ($customerType == 5) {
        $pdoQuery = $pdo->prepare("SELECT customerNum,orderNum,siteName,downloadId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where compId='" . $cId . "'");
    } else {
        $pdoQuery = $pdo->prepare("select customerNum,orderNum,siteName,downloadId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where compId in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where " . $wh . " and ctype=5)");
    }

    $pdoQuery->execute();
    $res = $pdoQuery->fetchAll();
    $str = '<tr style="border-bottom:1px solid #ddd;"><td style="text-align: center;">Customer no</td><td style="text-align: center;">Order no</td><td style="text-align: center;">Site name</td><td style="text-align: center;">Download URL</td></tr>';
    if (safe_count($res) > 0) {

        foreach ($res as $value) {

            $dUrl = $base_url . 'eula.php?id=' . $value['downloadId'];
            $str .= '<tr style="border-bottom:1px solid #ddd;"><td>' . $value['customerNum'] . '</td>';
            $str .= '<td>' . $value['orderNum'] . '</td>';
            $str .= '<td>' . $value['siteName'] . '</td>';
            $str .= '<td>' . $dUrl . '</td></tr>';
        }
    }
    return $str;
}

function addChannel()
{

    global $pdo;

    $pdo = pdo_connect();

    $companyName = url::issetInRequest('companyRegName') ? url::requestToText('companyRegName') : '';
    $companyRegNo = url::issetInRequest('companyRegNo') ? url::requestToText('companyRegNo') : '';
    $companyVatId = url::issetInRequest('companyVatId') ? url::requestToText('companyVatId') : '';
    $website = url::issetInRequest('website') ? url::requestToText('website') : '';
    $address = url::issetInRequest('companyAddress') ? url::requestToText('companyAddress') : '';
    $city = url::issetInRequest('companyCity') ? url::requestToText('companyCity') : '';
    $zipCode = url::issetInRequest('companyZipCode') ? url::requestToText('companyZipCode') : '';
    $country = url::issetInRequest('country') ? url::requestToText('country') : '';
    $province = url::issetInRequest('province') ? url::requestToText('province') : '';
    $firstName = url::issetInRequest('firstName') ? url::requestToText('firstName') : '';
    $lastName = url::issetInRequest('lastName') ? url::requestToText('lastName') : '';
    $emailId = url::issetInRequest('emailId') ? url::requestToText('emailId') : '';
    $phoneNo = url::issetInRequest('contactNo') ? url::requestToText('contactNo') : '';
    $loginUsing = url::issetInRequest('loginUsing') ? url::requestToText('loginUsing') : '';
    $orderInfo = url::issetInRequest('orderInfo') ? url::requestToText('orderInfo') : '';
    $ctype = url::issetInRequest('ctype') ? url::requestToText('ctype') : '';
    $skulist = url::issetInRequest('skulist') ? url::requestToText('skulist') : '';
    $reportserver = url::issetInRequest('reportserver') ? url::requestToText('reportserver') : '';
    $addcustomer = url::issetInRequest('addcustomer') ? url::requestToText('addcustomer') : '';
    $ordergen = url::issetInRequest('ordergen') ? url::requestToText('ordergen') : '';

    $currentDate = time();
    $loginBy = 'email';
    $pcCount = 0;

    $companyName = preg_replace('/[^a-zA-Z0-9\']/', '_', $companyName);
    $companyName = str_replace("'", '', $companyName);

    $customerName = preg_replace('/[^a-zA-Z0-9\']/', '_', $firstName);
    $customerName = str_replace("'", '', $customerName);

    if ($companyName != '' && $firstName != '' && $emailId != '') {

        $pdoQuery = $pdo->prepare("SELECT companyName,contactPerson,emailId from " . $GLOBALS['PREFIX'] . "agent.channelMaster where (companyName=? or emailId=?) limit 1");
        $pdoQuery->execute([$companyName, $emailId]);
        $res_comp = $pdoQuery->fetch();

        if (safe_count($res_comp) > 0) {

            if ($companyName == $res_comp['companyName']) {
                echo 'Company name already exists.';
            } elseif ($emailId == $res_comp['emailId']) {
                echo 'Email id already exists.';
            }
        } else {

            $sessionKey = getPasswordId1();

            $cust_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.channelMaster SET companyName = '" . $companyName . "',companyRegNo = '" . $companyRegNo . "',companyVatId = '" . $companyVatId . "',firstName = '" . $firstName . "',lastName = '" . $lastName . "',emailId = '" . $emailId . "',phoneNo='" . $phoneNo . "',address='" . $address . "',city='" . $city . "',zipCode='" . $zipCode . "',country='" . $country . "',province='" . $province . "',website='" . $website . "',ctype='" . $ctype . "',entityId=' 0',channelId=' 0',subchannelId=' 0',outsourcedId=' 0',ordergen=? ,skuList='" . $skulist . "',addcustomer='" . $addcustomer . "',sessionKey='" . $sessionKey . "',createdtime = ' $currentDate',status=1");
            $cust_sql->execute([$ordergen]);
            $cust_result = $pdo->lastInsertId();
            if ($cust_result) {

                $customerId = mysqli_insert_id();

                $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
                $pdoQuery->execute();
                $res_pro = $pdoQuery->fetch();
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

                $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.channelDetails SET cid='" . $customerId . "',deploy32bit = '" . $deploy32bit . "',deploy64bit = '" . $deploy64bit . "',setup32bit='" . $setup32bit . "',setup64bit='" . $setup64bit . "',ftpUrl='" . $ftpURL . "',nodeUrl='" . $nodeURL . "',supportNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',windowsProfile='profile',androidProfile='profile_android',macProfile='profile_mac'");
                $process_sql->execute();
                $process_result = $pdo->lastInsertId();
                if ($process_result) {
                    sendTrialCust($firstName, $emailId, $sessionKey);
                    echo 'Entity created successfully';
                }
            }
        }
    }
}

function addEntitySKU()
{

    global $pdo;

    $pdo = pdo_connect();

    $skuName = url::issetInPost('skuName') ? url::postToText('skuName') : '';
    $skuRef = url::issetInPost('skuRef') ? url::postToText('skuRef') : '';
    $skuDiscr = url::issetInPost('skuDiscr') ? url::postToText('skuDiscr') : '';
    $skuType = url::issetInPost('skuType') ? url::postToText('skuType') : '';
    $upgradeSku = url::issetInPost('upgradeSku') ? url::postToText('upgradeSku') : '';
    $renewSku = url::issetInPost('renewSku') ? url::postToText('renewSku') : '';
    $renewDays = url::issetInPost('renewDays') ? url::postToText('renewDays') : '';
    $pcCount = url::issetInPost('pcCount') ? url::postToText('pcCount') : '';
    $skuPrice = url::issetInPost('skuPrice') ? url::postToText('skuPrice') : '';
    $platFormAmt = url::issetInPost('platFormAmt') ? url::postToText('platFormAmt') : '';
    $conPeriod = url::issetInPost('conPeriod') ? url::postToText('conPeriod') : '';
    $localization = url::issetInPost('localization') ? url::postToText('localization') : '';
    $payProvider = url::issetInPost('payProvider') ? url::postToText('payProvider') : '';
    $accountId = url::issetInPost('accountId') ? url::postToText('accountId') : '';
    $accountName = url::issetInPost('accountName') ? url::postToText('accountName') : '';
    $userName = url::issetInPost('userName') ? url::postToText('userName') : '';
    $payPassword = url::issetInPost('payPassword') ? url::postToText('payPassword') : '';
    $apiKey = url::issetInPost('apiKey') ? url::postToText('apiKey') : '';
    $paymentmode = url::issetInPost('paymentmode') ? url::postToText('paymentmode') : '';
    $paymentGate = url::issetInPost('paymentgateway') ? url::postToText('paymentgateway') : '';
    $fixedSite = url::issetInPost('site_criteria') ? 1 : 0;

    $pdoQuery = $pdo->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster where skuName like '%" . $skuName . "%' AND skuRef like '%" . $skuRef . "%' limit 1");
    $pdoQuery->execute();
    $sku_check_res = $pdoQuery->fetch();

    if (safe_count($sku_check_res) > 0) {
        return array('msg' => 'This SKU already registred with us.');
    } else {

        $sku_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.skuMaster SET skuName = '" . $skuName . "',skuType = '" . $skuType . "',skuRef='" . $skuRef . "',description='" . $skuDiscr . "',upgrdSku = '" . $upgradeSku . "',renewSku = '" . $renewSku . "',renewDays = '" . $renewDays . "',licenseCnt = '" . $pcCount . "',skuPrice='" . $skuPrice . "',platformPrice='" . $platFormAmt . "',licensePeriod='" . $conPeriod . "',localization='" . $localization . "' ,payment_mode='" . $paymentmode . "' ,ppid='" . $paymentGate . "' ,site_criteria='" . $fixedSite . "'");
        $sku_sql->execute();
        $sku_result = $pdo->lastInsertId();
        if ($sku_result) {

            $skuId = mysqli_insert_id();

            return array('msg' => 'SKU details added.');
        } else {
            return array('msg' => 'This SKU details already registred with us.');
        }
    }
}

function getSkuTypeDtls()
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT id,skuName from " . $GLOBALS['PREFIX'] . "agent.skuMaster");
    $pdoQuery->execute();
    $resSKU = $pdoQuery->fetchAll();
    $str = '';
    if (safe_count($resSKU) > 0) {

        $str = '<h2>Customer Sku <a href="javascript:;" data-toggle="modal" data-target="#create_sku" class="material-icons icon-ic_control_point_24px"></a></h2><div class="nicescroll" style="overflow: hidden; outline: none; height: 150px;" data-cheight="680">';

        foreach ($resSKU as $value) {
            $str .= '<div class="form-group">
                        <div class="checkbox"><label><input type="checkbox" name="skuVal[]" value="' . $value['id'] . '">' . $value['skuName'] . '</label>
                                <a href="javascript:;" class="remove"><i class="material-icons icon-ic_block_24px"></i></a>
                        </div>
                </div>';
        }
    }
    $str .= '</div>';
    return $str;
}

function getSkuDtlsByCid($cid)
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("SELECT id,skuName from " . $GLOBALS['PREFIX'] . "agent.skuMaster where id in(?)");
    $pdoQuery->execute([$idval]);
    $resSKU = $pdoQuery->fetchAll();

    $str = '';
    if (safe_count($resSKU) > 0) {

        $str = '<h2>Customer Sku <a href="javascript:;" data-toggle="modal" data-target="#create_sku" class="material-icons icon-ic_control_point_24px"></a></h2>';

        foreach ($resSKU as $value) {

            $str .= '<div class="form-group">
                        <div class="checkbox"><label><input type="checkbox" name="skuVal[]" value="' . $value['id'] . '">' . $value['skuName'] . '</label>
                                <a href="javascript:;" class="remove"><i class="material-icons icon-ic_block_24px"></i></a>
                        </div>
                </div>';
        }
        $str .= '</tbody>';
    }
    return $str;
}

function getSkuDtlsByCidForChannel($cid)
{
    $pdo = pdo_connect();

    $pay_icon = '';
    $selBussiLevel = url::issetInRequest('selBussiLevel') ? url::requestToAny('selBussiLevel') : '';
    if ($selBussiLevel == 'Consumer' || $selBussiLevel == 'Commercial') {
        if ($selBussiLevel == 'Commercial') {
            $pdoQuery = $pdo->prepare("SELECT eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where companyName='NH_MSP' limit 1");
        } else if ($selBussiLevel == 'Consumer') {
            $pdoQuery = $pdo->prepare("SELECT eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where companyName='NH_PTS' limit 1");
        }
    } else {
        $pdoQuery = $pdo->prepare("SELECT eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    }
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("SELECT id,skuName,payment_mode from " . $GLOBALS['PREFIX'] . "agent.skuMaster where id in(?)");
    $pdoQuery->execute([$idval]);
    $resSKU = $pdoQuery->fetchAll();

    $str = '';
    if (safe_count($resSKU) > 0) {

        $str = '<h2>Customer Sku </h2>';

        foreach ($resSKU as $value) {
            if ($value["payment_mode"] == "Prepaid") {
                $pay_icon = '&nbsp;&nbsp;&nbsp;<img src="../images/dollar.png" width="16px" height="16px"></span>';
            } else {
                $pay_icon = '';
            }
            $str .= '<div class="form-group">
                        <div class="checkbox"><label><input type="checkbox" name="skuVal[]" value="' . $value['id'] . '">' . $value['skuName'] . $pay_icon . '</label>
                                <a href="javascript:;" class="remove"><i class="material-icons icon-ic_block_24px"></i></a>
                        </div>
                </div>';
        }
        $str .= '</tbody>';
    }
    return $str;
}

function getSkuDtlsByCidForCustomer($cid)
{
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT eid,entityId,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['skulist']);
    $ids = '';
    foreach ($roleItems as $value) {
        $ids .= $value . ",";
    }
    $idval = rtrim($ids, ',');

    $pdoQuery = $pdo->prepare("SELECT id,skuName from " . $GLOBALS['PREFIX'] . "agent.skuMaster where id in(?)");
    $pdoQuery->execute([$idval]);
    $resSKU = $pdoQuery->fetchAll();

    $str = '';
    if (safe_count($resSKU) > 0) {

        foreach ($resSKU as $value) {

            $str .= '<option value="' . $value['id'] . '">' . $value['skuName'] . '</option>';
        }
    }
    return $str;
}

function addentityCoreUser($name, $userName, $userLastName, $userEmail, $userPhone, $eid, $ctype)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $cId = $_SESSION["user"]["cId"];
    $user_type = $_SESSION["user"]["customerType"];
    $cksum = md5(mt_rand());
    $name = preg_replace('/\s+/', '_', $name);
    try {
        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $name . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $roleId = getUserRole();
            $resetId = getDownloadId1();
            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,role_id,username,firstName,lastName, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
            VALUES ( ?,?,'" . $name . "','" . $userName . "','" . $userLastName . "','','" . $userEmail . "','" . $userPhone . "','0','', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
            $sql_user->execute([$eid, $roleId, $resetId]);
            $result_user = $pdo->lastInsertId();

            $adminId = mysqli_insert_id();
            if ($result_user) {

                if ($ctype == 1) {

                    $upd_enty = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.entity_id = CONCAT(U.entity_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and U.ch_id=?");
                    $upd_enty->execute([$cId]);
                    $result_enty = $pdo->lastInsertId();
                } else if ($ctype == 2) {

                    $chnl_admin = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.channel_id = CONCAT(U.channel_id, ',', " . $eid . ") where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'");
                    $chnl_admin->execute();
                    $chnl_result = $pdo->lastInsertId();

                    $upd_chnl = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.channel_id = CONCAT(U.channel_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=?");
                    $upd_chnl->execute([$cId]);
                    $result_chnl = $pdo->lastInsertId();
                } else if ($ctype == 3) {

                    $sub_admin = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ',', " . $eid . ") where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'");
                    $sub_admin->execute();
                    $sub_result = $pdo->lastInsertId();

                    $upd_sub1 = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and U.ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=2)");
                    $upd_sub1->execute([$cId]);
                    $result_sub1 = $pdo->lastInsertId();

                    $upd_sub2 = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.subch_id = CONCAT(U.subch_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=?");
                    $upd_sub2->execute([$cId]);
                    $result_sub2 = $pdo->lastInsertId();
                } else if ($ctype == 5) {

                    $cust_admin = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ',', " . $eid . ") where U.ch_id=C.eid and C.ctype=0 and U.role_id=O.id and O.name = 'user_superadmin'");
                    $cust_admin->execute();
                    $result_admin = $pdo->lastInsertId();

                    if ($user_type == 1) {

                        $cust_enty = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=?");
                        $cust_enty->execute([$cId]);
                        $result_cust = $pdo->lastInsertId();
                    } else if ($user_type == 2) {

                        $cust_enty = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=2)");
                        $cust_enty->execute([$cId]);
                        $result_cust = $pdo->lastInsertId();

                        $cust_chnl = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id=");
                        $cust_chnl->execute([$cId]);
                        $result_chnl = $pdo->lastInsertId();
                    } else if ($user_type == 3) {

                        $cust_enty = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id in (select C.entityId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=2)");
                        $cust_enty->execute([$cId]);
                        $result_cust = $pdo->lastInsertId();

                        $cust_chnl = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and ch_id in (select C.channelId from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=? and C.ctype=3)");
                        $cust_chnl->execute([$cId]);
                        $result_chnl = $pdo->lastInsertId();

                        $cust_sub = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Options O SET U.customer_id = CONCAT(U.customer_id, ','," . $eid . ")  where U.role_id=O.id and O.name = 'user_superadmin' and  ch_id=?");
                        $cust_sub->execute([$cId]);
                        $result_sub = $pdo->lastInsertId();
                    }
                }

                $cust_sub = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $name . "',level='1',cksum='" . $cksum . "'");
                $cust_sub->execute();
                $result_ck = $pdo->lastInsertId();
                $res = sendTrialCust($userName, $userEmail, $resetId);

                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addsignUpCoreUser($name, $userName, $userEmail, $userPhone, $eid)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $userPass = $_SESSION["signup"]["password"];
    $cksum = md5(mt_rand());
    $name = preg_replace('/\s+/', '_', $name);
    try {
        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $name . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $roleId = getUserRole();
            $resetId = getDownloadId1();
            $mdPass = md5($userPass);
            $timestamp = time();
            $timestamp1 = strtotime('+1111 day', $timestamp);

            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,role_id,username, password,user_email,user_phone_no,user_priv, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey,passwordDate,userStatus)
            VALUES ( ?,?,'" . $name . "','" . $mdPass . "','" . $userEmail . "','" . $userPhone . "','0', '', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?,?,1)");
            $sql_user->execute([$eid, $roleId, $resetId, $timestamp1]);
            $result_user = $pdo->lastInsertId();

            if ($result_user) {
                $adminId = mysqli_insert_id();
                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $name . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();
                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addChannelCoreUser($userName, $userEmail, $userPhone, $eid, $cid)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $cksum = md5(mt_rand());

    try {

        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $userName . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {

            $resetId = getDownloadId1();
            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (entityId,channelId,username,user_email,user_phone_no, password, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
        VALUES (?,?,'" . $userName . "','" . $userEmail . "','" . $userPhone . "', '', '', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
            $sql_user->execute([$eid, $cid, $resetId]);
            $result_user = $pdo->lastInsertId();

            $adminId = mysqli_insert_id();

            if ($result_user) {

                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $userName . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();
                sendTrialCust($userName, $userEmail, $resetId);
                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addSubChannelCoreUser($userName, $userEmail, $userPhone, $eid, $cid, $scid)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $cksum = md5(mt_rand());

    try {

        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $userName . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {

            $resetId = getDownloadId1();
            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (entityId,channelId,subchannelId,username,user_email,user_phone_no,password, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
        VALUES ( ?,?,'$scid','" . $userName . "','" . $userEmail . "','" . $userPhone . "', '', '', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
            $sql_user->execute([$eid, $cid, $resetId]);
            $result_user = $pdo->lastInsertId();

            $adminId = mysqli_insert_id();

            if ($result_user) {

                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $userName . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();
                sendTrialCust($userName, $userEmail, $resetId);
                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addOutSouCoreUser($name, $userName, $userEmail, $userPhone, $eid)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $cksum = md5(mt_rand());
    $name = preg_replace('/\s+/', '_', $name);
    try {
        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $name . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {
            $roleName = 'user_outsource';
            $roleId = getUserRoleByName($roleName);
            $resetId = getDownloadId1();
            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (ch_id,role_id,username, password,user_email,user_phone_no, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
            VALUES (?,?,'" . $name . "','','" . $userEmail . "','" . $userPhone . "', '', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
            $sql_user->execute([$eid, $roleId, $resetId]);
            $result_user = $pdo->lastInsertId();

            $adminId = mysqli_insert_id();
            if ($result_user) {

                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $name . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();
                $res = sendTrialCust($userName, $userEmail, $resetId);

                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function addCustomerCoreUser($userName, $userEmail, $userPhone, $eid, $cid, $scid, $outId, $custId)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    global $pdo;

    $pdo = pdo_connect();

    $cksum = md5(mt_rand());

    try {

        $pdoQuery = $pdo->prepare("SELECT userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username='" . $userName . "' limit 1");
        $pdoQuery->execute();
        $res_core = $pdoQuery->fetch();
        if (safe_count($res_core) > 0) {
            return 0;
        } else {

            $resetId = getDownloadId1();
            $sql_user = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.Users (entityId,channelId,subchannelId,outsourcedId,customerId,username, password, notify_mail, report_mail, priv_admin, priv_notify, priv_report, priv_areport, priv_search, priv_aquery, priv_downloads, priv_updates, priv_config, priv_asset, priv_debug, priv_restrict, priv_provis, priv_audit, priv_csrv, filtersites, logo_file, logo_x, logo_y, footer_left, footer_right, revusers, cksum, asset_report_sender, disable_cache, event_notify_sender, event_report_sender, jpeg_quality, meter_report_sender, rept_css,userKey)
        VALUES (?,?,?,?,?,'" . $userName . "','" . $userEmail . "','" . $userPhone . "', '', '', '', 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '', '', '', '', 0, '" . $cksum . "', '', 0, '', '', 95, '', '',?)");
            $sql_user->execute([$eid, $cid, $scid, $outId, $custId, $resetId]);
            $result_user = $pdo->lastInsertId();

            $adminId = mysqli_insert_id();

            if ($result_user) {

                $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum SET username='" . $userName . "',level='1',cksum='" . $cksum . "'");
                $sql_usrck->execute();
                $result_ck = $pdo->lastInsertId();
                sendTrialCust($userName, $userEmail, $resetId);
                return $adminId;
            } else {
                return 0;
            }
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function getServerDtl()
{

    global $pdo;

    $pdoQuery = $pdo->prepare("SELECT serverid,servername,advanced from " . $GLOBALS['PREFIX'] . "install.Servers");
    $pdoQuery->execute();
    $res_comp = $pdoQuery->fetchAll();
    $str = '';

    if (safe_count($res_comp) > 0) {

        $str = '<h2>Report Server <a href="javascript:;" data-toggle="modal" data-target="#report_server" class="material-icons icon-ic_control_point_24px"></a></h2><div class="nicescroll" style="overflow: hidden; outline: none; height: 150px;" data-cheight="680">';

        foreach ($res_comp as $value) {
            $serverid = $value['serverid'];
            if ($value['advanced'] == 1) {
                $str .= '<div class="form-group">
                            <div class="checkbox"><label><input type="checkbox" name="server[]" value="' . $value['serverid'] . '" >' . $value['servername'] . '<span>&nbsp;&nbsp;&nbsp;<img src="../images/advance.png" width="16px" height="16px"></span></label>
                                    <a href="javascript:;" class="remove" ><i class="material-icons icon-ic_block_24px"></i></a>
                            </div>
                    </div>';
            } else {

                $str .= '<div class="form-group">
                            <div class="checkbox"><label><input type="checkbox" name="server[]" value="' . $value['serverid'] . '" >' . $value['servername'] . '</label>
                                    <a href="javascript:;" class="remove" ><i class="material-icons icon-ic_block_24px"></i></a>
                            </div>
                    </div>';
            }
        }
    }
    $str .= '</div>';
    return $str;
}

function getServerDtlForChannel()
{

    global $pdo;

    $checked = '';
    $selBussiLevel = url::issetInRequest('selBussiLevel') ? url::requestToAny('selBussiLevel') : '';
    if ($selBussiLevel == 'Consumer' || $selBussiLevel == 'Commercial') {
        if ($selBussiLevel == 'Commercial') {
            $checked = 'checked="checked" disabled ';
            $pdoQuery = $pdo->prepare("SELECT reportserver from " . $GLOBALS['PREFIX'] . "agent.channel where companyName='NH_MSP' limit 1");
        } else if ($selBussiLevel == 'Consumer') {
            $checked = 'checked="checked" disabled ';
            $pdoQuery = $pdo->prepare("SELECT reportserver from " . $GLOBALS['PREFIX'] . "agent.channel where companyName='NH_PTS' limit 1");
        }
        $pdoQuery->execute();
        $res = $pdoQuery->fetch();
        $reportServer = $res["reportserver"];
        $pdoQuery = $pdo->prepare("SELECT serverid,servername from " . $GLOBALS['PREFIX'] . "install.Servers where serverid in (" . $reportServer . ")");
    } else {
        $pdoQuery = $pdo->prepare("SELECT serverid,servername from " . $GLOBALS['PREFIX'] . "install.Servers");
    }
    $pdoQuery->execute();
    $res_comp = $pdoQuery->fetchAll();
    $str = '';
    if (safe_count($res_comp) > 0) {

        $str = '<h2>Report Server</h2>';

        foreach ($res_comp as $value) {
            $str .= '<div class="form-group">
                            <div class="checkbox"><label><input type="checkbox" ' . $checked . ' name="server[]" value="' . $value['serverid'] . '" >' . $value['servername'] . '</label>
                                        <a href="javascript:;" class="remove"><i class="material-icons icon-ic_block_24px"></i></a>
                            </div>
                    </div>';
        }
    }
    return $str;
}

function addEntityInfo()
{

    global $pdo;
    global $base_url;
    $logoPath = $base_url;
    $logoIconPath = $base_url;

    $targetDir = "../images/provision_images/";

    if (is_uploaded_file($_FILES['entity_uplogo']['tmp_name'])) {
        if (move_uploaded_file($_FILES['entity_uplogo']['tmp_name'], "$targetDir/" . $_FILES['entity_uplogo']['name'])) {
            $logoPath .= 'images/provision_images/' . $_FILES['entity_uplogo']['name'];
        }
    }

    if (is_uploaded_file($_FILES['entity_upicon']['tmp_name'])) {
        if (move_uploaded_file($_FILES['entity_upicon']['tmp_name'], "$targetDir/" . $_FILES['entity_upicon']['name'])) {
            $logoIconPath .= 'images/provision_images/' . $_FILES['entity_upicon']['name'];
        }
    }

    $serverList = url::postToAny('server');
    $skuVal = url::postToAny('skuVal');

    $name = url::postToAny('companyRegName');
    $regnum = url::postToAny('companyRegNo');
    $refnum = url::postToAny('companyVatId');
    $website = url::postToAny('website');
    $address = url::postToAny('companyAddress');
    $city = url::postToAny('companyCity');
    $statprov = url::postToAny('province');
    $zipcode = url::postToAny('companyZipCode');
    $country = url::postToAny('country');
    $fname = url::postToAny('firstName');
    $lname = url::postToAny('lastName');
    $email = url::postToAny('emailId');
    $phnumber = url::postToAny('contactNo');
    $ctype = url::postToAny('ctype');
    $loginusing = url::postToAny('loginusing');
    $orderinfo = url::postToAny('orderinfo');
    $hirearchyId = url::postToAny('hirearchyId');

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where (companyName  =? or emailId =?)");
    $pdoQuery->execute([$name, $email]);
    $res_core = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Users where user_email =?");
    $pdoQuery->execute([$email]);
    $res_user = $pdoQuery->fetch();

    if (safe_count($res_core) > 0 || safe_count($res_user) > 0) {
        if (safe_count($res_core) > 0) {
            return array("msg" => 'Entity Name or Email Id Already exist');
        } else {
            return array("msg" => 'User email id Already exist');
        }
    } else {

        $entityInsertSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
              referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
              ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
              VALUES (0, 0, 0, 0,?, ?,?,?, ?, ?, ?, ?,?, ?, ?,?, ?,'1',?,?, ?, ?,?, '1',?, '" . time() . "',?, ?, 1)");
        $entityInsertSql->execute([$name, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $hirearchyId, $ctype, $orderinfo, $skuVal, $serverList, $loginusing, $logoPath, $logoIconPath]);
        $entity_result = $pdo->lastInsertId();

        if ($entity_result) {

            $eid = mysqli_insert_id();
            $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pdoQuery->execute();
            $res_pro = $pdoQuery->fetch();
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
            $linux32bit = $proList['linuxsetup'];
            $linux64bit = $proList['linuxsetup64'];
            $currentDate = time();
            $sitename = preg_replace('/\s+/', '_', $name);
            $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$eid',processName = '" . $name . "',siteCode = '" . $sitename . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac',linuxsetup='" . $linux32bit . "',linuxsetup64='" . $linux64bit . "'");
            $process_sql->execute();
            $process_result = $pdo->lastInsertId();

            $processId = mysqli_insert_id();
            if ($process_result) {

                addentityCoreUser($name, $fname, $lname, $email, $phnumber, $eid, 1);
            }

            return array("msg" => 'Entity created Successfully');
        } else {
            return array("msg" => 'Fail to create Entity. Please try later.');
        }
    }
}

function addChannelInfo()
{

    global $pdo;
    global $base_url;
    $logoPath = $base_url;
    $logoIconPath = $base_url;

    $entityId = $_SESSION["user"]["entityId"];
    $channelId = $_SESSION["user"]["channelId"];
    $subchannelId = $_SESSION["user"]["subchannelId"];
    $outsourcedId = $_SESSION["user"]["outsourcedId"];
    $orderinfo = $_SESSION["user"]["ordergen"];

    $pdo = pdo_connect();

    if (is_array($_FILES)) {

        $targetDir = "../images/provision_images/";
        if (is_uploaded_file($_FILES['channel_uplogo']['tmp_name'])) {

            if (move_uploaded_file($_FILES['channel_uplogo']['tmp_name'], "$targetDir/" . $_FILES['channel_uplogo']['name'])) {
                $logoPath .= 'images/provision_images/' . $_FILES['channel_uplogo']['name'];
            }
        }
        if (is_uploaded_file($_FILES['channel_upicon']['tmp_name'])) {
            if (move_uploaded_file($_FILES['channel_upicon']['tmp_name'], "$targetDir/" . $_FILES['channel_upicon']['name'])) {
                $logoIconPath .= 'images/provision_images/' . $_FILES['channel_upicon']['name'];
            }
        }
    }

    $serverList = url::postToAny('server');
    $skuVal = url::postToAny('skuVal');
    $agentVal = url::postToAny('agentVal');

    $name = url::postToAny('name');
    $regnum = url::postToAny('regnumber');
    $refnum = url::postToAny('companyVatId');
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
    $hirearchyId = url::postToAny('hirearchyId');
    $outsrcId = (url::postToAny('outsrcpart') != 0) ? url::postToAny('outsrcpart') : 0;

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where (companyName  =? or emailId = ?)");
    $pdoQuery->execute([$name, $email]);
    $res_core = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ?");
    $pdoQuery->execute([$email]);
    $res_user = $pdoQuery->fetch();

    if (safe_count($res_core) > 0 || safe_count($res_user) > 0) {
        if (safe_count($res_core) > 0) {
            return array("msg" => 'Channel Name or Email Id Already exist', "status" => "error");
        } else {
            return array("msg" => 'User email id Already exist', "status" => "error");
        }
    } else {
        $channelInsertSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
            referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
            ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
            VALUES (?, 0, 0, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?,?,'2',?,?, ?, ?, ?, '1',?, '" . time() . "', ?,?, 0)");
        $channelInsertSql->execute([$entityId, $outsrcId, $name, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $statprov, $website, $hirearchyId, $ctype, $orderinfo, $skuVal, $serverList, $loginusing, $logoPath, $logoIconPath]);

        $channel_res = $pdo->lastInsertId();
        if ($channel_res) {

            $cid = mysqli_insert_id();
            $addAgent = insertSalesAgents($cid, $agentVal);
            $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pdoQuery->execute();
            $res_pro = $pdoQuery->fetch();
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
            $linux32bit = $proList['linuxsetup'];
            $linux64bit = $proList['linuxsetup64'];
            $currentDate = time();
            $sitename = preg_replace('/\s+/', '_', $name);
            $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$cid',processName = '" . $name . "',siteCode = '" . $sitename . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac',linuxsetup='" . $linux32bit . "',linuxsetup64='" . $linux64bit . "'");
            $process_sql->execute();
            $process_result = $pdo->lastInsertId();
            if ($process_result) {
                $processId = mysqli_insert_id();
                $userId = addentityCoreUser($name, $fname, $lname, $email, $phnumber, $cid, 2);
            }

            return array("msg" => 'Channel created Successfully', "status" => "success");
        } else {
            return array("msg" => 'Fail to create channel. Please try later.', "status" => "error");
        }
    }
}

function insertSalesAgents($ch_id, $agentArray)
{
    $pdo = pdo_connect();
    $tempArray = explode(',', $agentArray);
    if (safe_count($tempArray) > 0) {
        foreach ($tempArray as $value) {
            $str = explode('--', $value);
            $inserSql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.salesAgents set ch_id=?,agentName='" . $str[1] . "',agentEmail='" . $str[0] . "'");
            $inserSql->execute([$ch_id]);
            $pdo->lastInsertId();
        }
        return true;
    } else {
        return true;
    }
}

function addResolvChannelInfo()
{

    global $pdo;
    global $base_url;
    $logoPath = $base_url;
    $logoIconPath = $base_url;
    $signup = $_SESSION["signup"]["channel"];
    $ctype = url::postToAny('ctype');

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE companyName = 'HP' limit 1");
    $pdoQuery->execute();
    $res = $pdoQuery->fetch();
    $entityId = $res["eid"];
    $orderinfo = $res["ordergen"];
    $skuVal = $res["skulist"];
    $serverList = $res["reportserver"];

    if (is_array($_FILES)) {

        $targetDir = "../images/provision_images/";
        if (is_uploaded_file($_FILES['channel_uplogo']['tmp_name'])) {

            if (move_uploaded_file($_FILES['channel_uplogo']['tmp_name'], "$targetDir/" . $_FILES['channel_uplogo']['name'])) {
                $logoPath .= 'images/provision_images/' . $_FILES['channel_uplogo']['name'];
            }
        }
        if (is_uploaded_file($_FILES['channel_upicon']['tmp_name'])) {
            if (move_uploaded_file($_FILES['channel_upicon']['tmp_name'], "$targetDir/" . $_FILES['channel_upicon']['name'])) {
                $logoIconPath .= 'images/provision_images/' . $_FILES['channel_upicon']['name'];
            }
        }
    }

    $name = url::postToAny('name');
    $regnum = url::postToAny('regnumber');
    $refnum = url::postToAny('companyVatId');
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
    $hirearchyId = 'Channel';
    $outsrcId = 0;

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where (companyName  = ? or emailId = ?)");
    $pdoQuery->execute([$name, $email]);
    $res_core = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ?");
    $pdoQuery->execute([$email]);
    $res_user = $pdoQuery->fetch();

    if (safe_count($res_core) > 0 || safe_count($res_user) > 0) {
        if (safe_count($res_core) > 0) {
            return array("msg" => 'Channel Name or Email Id Already exist', "status" => "error");
        } else {
            return array("msg" => 'User email id Already exist', "status" => "error");
        }
    } else {
        $channelInsertSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
            referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
            ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
            VALUES ('$entityId', 0, 0, $outsrcId, '$name', '$regnum', '$refnum', '$fname', '$lname', '$email', '$phnumber', '$address', '$city',
            '$zipcode', '$country', '$statprov', '$website','5','$hirearchyId','$ctype', '$orderinfo', '$skuVal', '$serverList', '1','$loginusing', '" . time() . "', '$logoPath', '$logoIconPath', 0)");
        $channelInsertSql->execute();

        $channel_res = $pdo->lastInsertId();
        if ($channel_res) {

            $cid = mysqli_insert_id();
            $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pdoQuery->execute();
            $res_pro = $pdoQuery->fetch();
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
            $currentDate = time();
            $sitename = preg_replace('/\s+/', '_', $name);
            $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$cid',processName = '" . $name . "',siteCode = '" . $sitename . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac'");
            $process_sql->execute();
            $process_result = $pdo->lastInsertId();
            if ($process_result) {
                $processId = mysqli_insert_id();
                $userId = addsignUpCoreUser($name, $fname, $email, $phnumber, $cid);
            }
            $_SESSION['signupchnlid'] = $cid;
            return array("msg" => 'Customer has been created successfully, please click on next button to move ahead.', "status" => "success");
        } else {
            return array("msg" => 'Fail to create Entity. Please try later.', "status" => "error");
        }
    }
}

function addSubChannelInfo()
{

    global $pdo;
    global $base_url;
    $logoPath = $base_url;
    $logoIconPath = $base_url;

    $entityId = $_SESSION["user"]["entityId"];
    $channelId = $_SESSION["user"]["channelId"];
    $subchannelId = $_SESSION["user"]["subchannelId"];
    $outsourcedId = $_SESSION["user"]["outsourcedId"];
    $orderinfo = $_SESSION["user"]["ordergen"];

    $pdo = pdo_connect();

    $targetDir = "../images/provision_images/";
    if (is_uploaded_file($_FILES['subchannel_uplogo']['tmp_name'])) {
        if (move_uploaded_file($_FILES['subchannel_uplogo']['tmp_name'], "$targetDir/" . $_FILES['subchannel_uplogo']['name'])) {
            $logoPath .= 'images/provision_images/' . $_FILES['subchannel_uplogo']['name'];
        }
    }

    if (is_uploaded_file($_FILES['subchannel_upicon']['tmp_name'])) {
        if (move_uploaded_file($_FILES['subchannel_upicon']['tmp_name'], "$targetDir/" . $_FILES['subchannel_upicon']['name'])) {
            $logoIconPath .= 'images/provision_images/' . $_FILES['subchannel_upicon']['name'];
        }
    }

    $serverList = url::postToAny('server');
    $skuVal = url::postToAny('skuVal');

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

    $hirearchyId = url::postToAny('hirearchyId');

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where (companyName  =? or emailId =?)");
    $pdoQuery->execute([$name, $email]);
    $res_core = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Users where user_email =?");
    $pdoQuery->execute([$email]);
    $res_user = $pdoQuery->fetch();

    if (safe_count($res_core) > 0 || safe_count($res_user) > 0) {
        if (safe_count($res_core) > 0) {
            return array("msg" => 'Subchannel Name or Email Id Already exist');
        } else {
            return array("msg" => 'User email id Already exist');
        }
    } else {

        $channelInsertSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
         referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
         ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
         VALUES (?, ?, 0, 0, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?,'3',?,?, ?, ?, ?, '1',?, '" . time() . "', ?, ?, 0)");
        $channelInsertSql->execute([$entityId, $channelId, $name, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $hirearchyId, $ctype, $orderinfo, $skuVal, $serverList, $loginusing, $logoPath, $logoIconPath]);

        $subCh_result = $pdo->lastInsertId();
        if ($subCh_result) {

            $scid = mysqli_insert_id();
            $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pdoQuery->execute();
            $res_pro = $pdoQuery->fetch();
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
            $linux32bit = $proList['linuxsetup'];
            $linux64bit = $proList['linuxsetup64'];
            $currentDate = time();
            $sitename = preg_replace('/\s+/', '_', $name);
            $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$scid',processName = '" . $name . "' ,siteCode = '" . $sitename . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac',linuxsetup='" . $linux32bit . "',linuxsetup64='" . $linux64bit . "'");
            $process_sql->execute();
            $process_result = $pdo->lastInsertId();
            if ($process_result) {
                $processId = mysqli_insert_id();
                addentityCoreUser($name, $fname, $lname, $email, $phnumber, $scid, 3);
            }
            return array("msg" => 'Sub Channel created Successfully');
        } else {
            return array("msg" => 'Fail to create sub channel. Please try later.');
        }
    }
}

function addOutSourceInfo()
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
    $orderinfo = url::postToAny('orderinfo');
    $hirearchyId = url::postToAny('hirearchyId');

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where (companyName  = ? or emailId = ?)");
    $pdoQuery->execute([$name, $email]);
    $res_core = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ?");
    $pdoQuery->execute([$email]);
    $res_user = $pdoQuery->fetch();

    if (safe_count($res_core) > 0 || safe_count($res_user) > 0) {
        if (safe_count($res_core) > 0) {
            return array("msg" => 'Outsource Partner Name or Email Id Already exist');
        } else {
            return array("msg" => 'User email id Already exist');
        }
    } else {
        $channelInsertSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
        referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype, entyHirearchy,businessLevel,
        ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
        VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,'4',?,?, ?, '0', 0, '0',?, '" . time() . "', ?, ?, 0)");
        $channelInsertSql->execute([$entityId, $channelId, $subchannelId, $name, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $hirearchyId, $ctype, $orderinfo, $loginusing, $logoPath, $logoIconPath]);

        $outPart_result = $pdo->lastInsertId();
        if ($outPart_result) {

            $ouid = mysqli_insert_id();
            $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pdoQuery->execute();
            $res_pro = $pdoQuery->fetch();
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
            $linux32bit = $proList['linuxsetup'];
            $linux64bit = $proList['linuxsetup64'];
            $currentDate = time();
            $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId='$ouid',processName = '" . $name . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac',linuxsetup='" . $linux32bit . "',linuxsetup64='" . $linux64bit . "'");
            $process_sql->execute();
            $process_result = $pdo->lastInsertId();
            if ($process_result) {
                $processId = mysqli_insert_id();
                addOutSouCoreUser($name, $fname, $email, $phnumber, $ouid);
            }
            return array("msg" => 'Outsource partner created Successfully');
        } else {
            return array("msg" => 'Fail to create out source partner. Please try later.');
        }
    }
}

function addNewCustomer()
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

    if (is_array($_FILES)) {
        $targetDir = "../images/provision_images/";
        if (is_uploaded_file($_FILES['customer_uplogo']['tmp_name'])) {
            if (move_uploaded_file($_FILES['customer_uplogo']['tmp_name'], "$targetDir/" . $_FILES['customer_uplogo']['name'])) {
                $logoPath .= 'images/provision_images/' . $_FILES['customer_uplogo']['name'];
            }
        }
        if (is_uploaded_file($_FILES['customer_appLogo']['tmp_name'])) {
            if (move_uploaded_file($_FILES['customer_appLogo']['tmp_name'], "$targetDir/" . $_FILES['customer_appLogo']['name'])) {
                $logoIconPath .= 'images/provision_images/' . $_FILES['customer_appLogo']['name'];
            }
        }
    }

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

    $name = url::issetInPost('name') ? url::postToAny('name') : '';
    $regnum = url::issetInPost('regnumber') ? url::postToAny('regnumber') : '';
    $refnum = url::issetInPost('companyVatId') ? url::postToAny('companyVatId') : '';
    $website = url::issetInPost('website') ? url::postToAny('website') : '';
    $address = url::issetInPost('addr') ? url::postToAny('addr') : '';
    $city = url::issetInPost('city') ? url::postToAny('city') : '';
    $statprov = url::issetInPost('stprov') ? url::postToAny('stprov') : '';
    $zipcode = url::issetInPost('zpcode') ? url::postToAny('zpcode') : '';
    $country = url::issetInPost('country') ? url::postToAny('country') : '';
    $fname = url::issetInPost('fname') ? url::postToAny('fname') : '';
    $lname = url::issetInPost('lname') ? url::postToAny('lname') : '';
    $email = url::issetInPost('email') ? url::postToAny('email') : '';
    $phnumber = url::issetInPost('phnumber') ? url::postToAny('phnumber') : '';
    $skuVal = url::issetInPost('skuVal') ? url::postToAny('skuVal') : '';
    $sales_agents = url::issetInPost('sales_agent') ? url::postToAny('sales_agent') : '';
    $orderRefNumber = url::issetInPost('orderRefNumber') ? url::postToAny('orderRefNumber') : '';
    $localization = url::issetInPost('local') ? url::postToAny('local') : '';
    $orderinfo = url::postToAny('orderinfo');
    $hirearchyId = url::postToAny('hirearchyId');
    $ctype = url::postToAny('ctype');

    $wh = '';
    if ($refnum != '') {
        $wh = "or referenceNo='$refnum'";
    }

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where (companyName  = ? or emailId = ? " . $wh . ")");
    $pdoQuery->execute([$name, $email]);
    $res_core = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder where orderNum = ? limit 1");
    $pdoQuery->execute([$refnum]);
    $sql_cust_order_res = $pdoQuery->fetch();

    if (safe_count($res_core) > 0) {
        if ($refnum != '') {
            return array("msg" => 'Customer Name or Email Id or Reference Number already exist.');
        } else {
            return array("msg" => 'Customer Name or Email Id already exist.');
        }
    } else if (safe_count($sql_cust_order_res) > 0) {
        return array("msg" => 'Order Number already exist.');
    } else {

        $channelInsertSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "agent.channel (entityId, channelId, subchannelId, outsourcedId, companyName, regNo,
         referenceNo, firstName, lastName, emailId, phoneNo, address, city, zipCode, country, province, website,ctype,entyHirearchy,businessLevel,
         ordergen, skulist, reportserver, addcustomer,loginUsing, createdtime, logo, iconLogo, status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'5',?,?,?,?,?, '0','Email','" . time() . "',?,?, 1)");
        $channelInsertSql->execute([$entityId, $channelId, $subchannelId, $outsourcedId, $name, $regnum, $refnum, $fname, $lname, $email, $phnumber, $address, $city, $zipcode, $country, $statprov, $website, $hirearchyId, $ctype, $orderinfo, $skulist, $serverUrl, $logoPath, $logoIconPath]);

        $cust_result = $pdo->lastInsertId();
        if ($cust_result) {

            $custId = mysqli_insert_id();
            $addAgent = insertSalesAgents($custId, $sales_agents);
            if ($refnum != '') {
                $custNo = $refnum;
            } else {
                $year = date("Y");
                $custNo = $year . '000' . $custId;
            }

            $updateCust = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.channel set customerNo='$custNo',referenceNo=? where eid=?");
            $updateCust->execute([$custNo, $custId]);
            $cust_update = $pdo->lastInsertId();

            $pdoQuery = $pdo->prepare("SELECT S.value from " . $GLOBALS['PREFIX'] . "core.Options S where S.type='11' and S.name= 'process_data' limit 1");
            $pdoQuery->execute();
            $res_pro = $pdoQuery->fetch();
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
            $linux32bit = $proList['linuxsetup'];
            $linux64bit = $proList['linuxsetup64'];
            $currentDate = time();
            $dUrl = $base_url . 'eula.php';
            $sitename = preg_replace('/\s+/', '_', $name);
            $process_sql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.processMaster SET cId=?,processName = '" . $name . "' ,siteCode = '" . $sitename . "',deployPath32 = '" . $deploy32bit . "',deployPath64 = '" . $deploy64bit . "',setupName32='" . $setup32bit . "',setupName64='" . $setup64bit . "',createdDate = '" . $currentDate . "',FtpConfUrl='" . $ftpURL . "',WsServerUrl='" . $nodeURL . "',dateCheck=1,backupCheck=0,sendMail=0,serverUrl='',downloaderPath='$dUrl',phoneNo='" . $phoneNo . "',chatLink='" . $chatLink . "',privacyLink='" . $privacyLink . "',status=1,androidsetup='" . $androidSetup . "',macsetup='" . $macSetup . "',profileName='profile',andProfileName='profile_android',macProfileName='profile_mac',linuxsetup='" . $linux32bit . "',linuxsetup64='" . $linux64bit . "'");
            $process_sql->execute([$custId]);
            $process_result = $pdo->lastInsertId();
            if ($process_result) {
                $processId = mysqli_insert_id();
                $addCoreUser = addentityCoreUser($name, $fname, $lname, $email, $phnumber, $custId, 5);
                if ($addCoreUser != 0) {
                    $provision_url = 'DONE';
                    if ($provision_url != "NOTDONE") {
                        return array("link" => urldecode($provision_url), "msg" => "$name has been successfully created.");
                    } else {
                        return array("msg" => "Fail to create new provision. Please try later.");
                    }
                } else {
                    return array("msg" => "Fail to create new provision. Please try later.");
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

function get_Entity_Dtl($id)
{

    global $pdo;
    global $base_url;

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$id]);
    $res_core = $pdoQuery->fetch();
    if ($res_core["ctype"] == 1 || $res_core["ctype"] == 2) {
        $res_core["outsourcedList"] = get_outsourcePartner($res_core["outsourcedId"]);
    }

    return $res_core;
}

function getChannelDetails()
{

    global $pdo;
    global $base_url;

    $pdo = pdo_connect();

    $customerType = $_SESSION["user"]["customerType"];
    $selectedId = isset($_SESSION["selected"]["eid"]) ? $_SESSION["selected"]["eid"] : '';
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
    $pdoQuery = $pdo->prepare("SELECT eid,companyName,regNo,referenceNo,firstName,lastName,emailId,phoneNo,customerNo,reportserver,skulist,entityId,channelId,subchannelId,outsourcedId from " . $GLOBALS['PREFIX'] . "agent.channel where $wh");
    $pdoQuery->execute();
    $res = $pdoQuery->fetch();

    return $res;
}

function getSelectedEditserverChannel($cid)
{

    global $pdo;
    global $base_url;

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT eid,reportserver,skulist from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $roleItems = explode(",", $res_sku['reportserver']);

    $pdoQuery = $pdo->prepare("SELECT serverid,servername from " . $GLOBALS['PREFIX'] . "install.Servers");
    $pdoQuery->execute();
    $resSKU = $pdoQuery->fetchAll();

    $str = '';

    if (safe_count($resSKU) > 0) {

        $str = '<h2>Report Server</h2>';

        foreach ($resSKU as $value) {
            if (in_array($value['serverid'], $roleItems)) {
                $str .= '<div class="form-group">
                            <div class="checkbox"><label><input type="checkbox" name="server[]" value="' . $value['serverid'] . '" checked>' . $value['servername'] . '</label>
                                    <a href="javascript:;" class="remove"><i class="material-icons icon-ic_block_24px"></i></a>
                            </div>
                    </div>';
            } else {
                $str .= '<div class="form-group">
                            <div class="checkbox"><label><input type="checkbox" name="server[]" value="' . $value['serverid'] . '" >' . $value['servername'] . '</label>
                                    <a href="javascript:;" class="remove"><i class="material-icons icon-ic_block_24px"></i></a>
                            </div>
                    </div>';
            }
        }
    }
    return $str;
}

function resendMail($cid)
{

    global $pdo;
    global $base_url;

    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT eid,firstName,emailId from " . $GLOBALS['PREFIX'] . "agent.channel where eid=? limit 1");
    $pdoQuery->execute([$cid]);
    $res_sku = $pdoQuery->fetch();

    $userName = $res_sku['firstName'];
    $userEmail = $res_sku['emailId'];

    $pdoQuery = $pdo->prepare("SELECT userid,userKey from " . $GLOBALS['PREFIX'] . "core.Users where user_email=? limit 1");
    $pdoQuery->execute([$userEmail]);
    $res_core = $pdoQuery->fetch();
    $resetId = $res_core['userKey'];

    $res = sendTrialCust($userName, $userEmail, $resetId);
    if ($res == 1) {
        return 'Mail has been sent successfully.';
    } else {
        return 'Error to send mail. Please try again.';
    }
}

function getUserNextLevel()
{
    try {
        global $pdo;
        $pdo = pdo_connect();

        $username = $_SESSION["user"]["username"];
        $cType = $_SESSION["user"]["customerType"];
        $entityId = $_SESSION["user"]["entityId"];
        $channelId = $_SESSION["user"]["channelId"];
        $subchannelId = $_SESSION["user"]["subchannelId"];
        $uid = $_SESSION["user"]["userid"];
        $eid = $_SESSION["user"]["cId"];
        $entityDtls = get_Entity_Dtl($eid);
        $sql = '';
        $sql1 = '';
        $str = '';
        $str1 = '';

        $user_pre = getUserChannelRights($uid);

        $entity_id = str_explode($user_pre['entity_id']);
        $chnl_id = str_explode($user_pre['channel_id']);
        $subch_id = str_explode($user_pre['subch_id']);
        $cust_id = str_explode($user_pre['customer_id']);

        if ($cType == 0) {
            $pdoQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 1 AND eid in (" . $entity_id . ")");
        } else if ($cType == 1) {
            $pdoQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 2 AND entityId in(" . $eid . ") AND eid in (" . $chnl_id . ")");
            $pdoCustQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 5 AND entityId in(" . $eid . ") and eid in (" . $cust_id . ") and channelId=0 and subchannelId=0");
        } else if ($cType == 2) {
            $pdoQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 3 AND channelId in(" . $eid . ") AND eid in (" . $subch_id . ")");
            $pdoCustQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 5 AND channelId in(" . $eid . ") and eid in (" . $cust_id . ") and subchannelId=0");
        } else if ($cType == 3) {
            $pdoCustQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 5 AND subchannelId in(" . $eid . ") and eid in (" . $cust_id . ")");
        } else if ($cType == 5) {
            $pdoQuery = $pdo->prepare("SELECT customer as eid, customer as companyName from " . $GLOBALS['PREFIX'] . "core.Customers where username = '" . $username . "' group by customer");
        }
        $pdoQuery->execute();
        $sql_res = $pdoQuery->fetchAll();
        if (safe_count($sql_res) > 0) {
            $str .= "<option value='all'>All</option>";
            foreach ($sql_res as $value) {
                $str .= "<option value='" . $value['eid'] . "'>" . $value['companyName'] . "</option>";
            }
        } else {
            $str .= "<option value='0'>Not Available</option>";
        }

        $pdoCustQuery->execute();
        $sql_cust_res = $pdoCustQuery->fetchAll();
        if (safe_count($sql_cust_res) > 0) {
            $str1 .= "<option value='all'>All</option>";
            foreach ($sql_cust_res as $value) {
                $str1 .= "<option value='" . $value['eid'] . "'>" . $entityDtls['companyName'] . " : " . $value['companyName'] . "</option>";
            }
        } else {
            $str1 .= "<option value='0'>Not Available</option>";
        }

        return $str . '##' . $str1;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function str_explode($ids)
{

    $wordChunks = explode(",", $ids);
    $str = '';
    for ($i = 0; $i < safe_count($wordChunks); $i++) {
        $str .= $wordChunks[$i] . ',';
    }
    $string = rtrim($str, ',');
    return $string;
}

function getUserChannelList()
{
    try {
        global $pdo;
        $pdo = pdo_connect();
        $entityIds = '';
        $cType = url::requestToAny('ctype');

        $userid = $_SESSION["user"]["userid"];
        $username = $_SESSION["user"]["username"];
        $ch_id = url::requestToAny('selectedValues');
        $selVal = explode(',', $ch_id);
        $ch_ids = '';
        $str = '';
        $str1 = '';
        foreach ($selVal as $value) {
            $ch_ids .= $value . ',';
        }
        $ch_ids = rtrim($ch_ids, ',');

        $user_pre = getUserChannelRights($userid);
        $entityDtls = get_Entity_Dtl($uid);

        $entity_id = str_explode($user_pre['entity_id']);
        $chnl_id = str_explode($user_pre['channel_id']);
        $subch_id = str_explode($user_pre['subch_id']);
        $cust_id = str_explode($user_pre['customer_id']);

        if ($cType == 0) {
            $pdoQuery = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE cType = 1 AND eid in ($ch_ids)");
        } else if ($cType == 1) {
            $pdoQuery = $pdo->prepare("SELECT C.companyName,C.eid,GROUP_CONCAT(M.companyName,' : ',C.companyName) displayName FROM " . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "agent.channel M WHERE C.cType = 2 AND C.entityId in(" . $ch_ids . ") AND C.eid in (" . $chnl_id . ") and C.entityId=M.eid  group by C.eid");
            $pdoCustQuery = $pdo->prepare("SELECT C.companyName,C.eid,GROUP_CONCAT(M.companyName,' : ',C.companyName) displayName FROM " . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "agent.channel M WHERE C.cType = 5 AND C.entityId in(" . $ch_ids . ") and C.channelId = 0 and C.subchannelId = 0 AND C.eid in (" . $cust_id . ") and C.entityId=M.eid   group by C.eid");
        } else if ($cType == 2) {
            $pdoQuery = $pdo->prepare("SELECT C.companyName,C.eid,GROUP_CONCAT(M.companyName,' : ',C.companyName) displayName FROM " . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "agent.channel M WHERE C.cType = 3 AND C.channelId in(" . $ch_ids . ") AND C.eid in (" . $subch_id . ") and C.channelId=M.eid  group by C.eid");

            $pdoCustQuery = $pdo->prepare("SELECT C.companyName,C.eid,GROUP_CONCAT(M.companyName,' : ',C.companyName) displayName FROM " . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "agent.channel M WHERE C.cType = 5 AND C.channelId in(" . $ch_ids . ") and C.subchannelId = 0 AND C.eid in (" . $cust_id . ") and C.channelId=M.eid  and C.channelId=M.eid   group by C.eid");
        } else if ($cType == 3) {
            $pdoCustQuery = $pdo->prepare("SELECT C.companyName,C.eid,GROUP_CONCAT(M.companyName,' : ',C.companyName) displayName FROM " . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "agent.channel M WHERE C.cType = 5 AND C.subchannelId in(" . $ch_ids . ") AND C.eid in (" . $cust_id . ") and C.subchannelId=M.eid  group by C.eid");
        }
        $pdoQuery->execute();
        $sql_res = $pdoQuery->fetchAll();
        if (safe_count($sql_res) > 0) {
            $str .= "<option value='all'>All</option>";
            foreach ($sql_res as $value) {
                $str .= "<option value='" . $value['eid'] . "'>" . $value['displayName'] . "</option>";
            }
        } else {
            $str .= '<option value="0">Not Available</option>';
        }
        $pdoCustQuery->execute();
        $sql_cust_res = $pdoCustQuery->fetchAll();
        if (safe_count($sql_cust_res) > 0) {
            $str1 .= "<option value='all'>All</option>";
            foreach ($sql_cust_res as $value) {
                $str1 .= "<option value='" . $value['eid'] . "' data-foo='" . $value['eid'] . "'>" . $value['displayName'] . "</option>";
            }
            $str1 .= '@@1';
        } else {
            $str1 .= '<option value="0">Not Available</option>@@0';
        }

        return $str . '##' . $str1;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getCustomersLevel()
{
    try {
        global $pdo;
        $pdo = pdo_connect();
        $entityIds = '';
        $entityIds = url::requestToAny('selectedValues');

        $sql = '';
        $str = '';
        $pdoQuery = $pdo->prepare("SELECT eid,companyName FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE ctype = 5 AND subchannelId in (" . $entityIds . ")");
        if ($sql != "") {
            $pdoQuery->execute();
            $sql_res = $pdoQuery->fetchAll();
            if (safe_count($sql_res) > 0) {
                $str .= "<option value='all'>All</option>";
                foreach ($sql_res as $value) {
                    $str .= "<option value='" . $value['eid'] . "'>" . $value['companyName'] . "</option>";
                }
            } else {
                $str .= '<option value="0">Not Available</option>';
            }
        } else {
            $str .= '<option value="0">Not Available</option>';
        }

        return $str;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getSelSiteList($cid)
{

    global $pdo;

    $pdoQuery = $pdo->prepare("SELECT C.companyName,C.eid from " . $GLOBALS['PREFIX'] . "agent.channel C  where C.eid=?");
    $pdoQuery->execute([$cid]);
    $chnl_res = $pdoQuery->fetch();

    $compName = preg_replace('/\s+/', '_', $chnl_res['companyName']);
    $pdoQuery = $pdo->prepare("SELECT C.customer from " . $GLOBALS['PREFIX'] . "core.Users U," . $GLOBALS['PREFIX'] . "core.Customers C where U.username=C.username and U.username=? group by C.customer");
    $pdoQuery->execute([$compName]);
    $site_res = $pdoQuery->fetchAll();

    $str = '';

    foreach ($site_res as $value) {

        $str .= "<option value='" . $value['customer'] . "'>" . $value['customer'] . "</option>";
    }
    $siteList = $str;

    return $siteList;
}

function get_year_months($days)
{

    if ($days >= 365) {
        $years = ($days / 365);
        $years = floor($years);
        $i = $years;
        if ($i == 1) {
            $i = $years . ' year';
        } else {
            $i = $years . ' years';
        }
    } else {
        $month = ($days % 365) / 30;
        $month = floor($month);
        $i = $month;
        if ($i == 1) {
            $i = $month . ' month';
        } else {
            $i = $month . ' months';
        }
    }
    return $i;
}

function get_fetchDownloadUrl()
{
    global $base_url;
    $str = '';
    $pdo = pdo_connect();
    $custId = url::requestToAny('siteId');
    $pdoQuery = $pdo->prepare("SELECT downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE id=? limit 1");
    $pdoQuery->execute()[$custId];
    $download_res = $pdoQuery->fetch();
    if (safe_count($download_res) > 0) {
        $str = $base_url . 'eula.php?id=' . $download_res['downloadId'];
    } else {
        $str = '';
    }
    return $str;
}

function validateLicenseKey($licenseKey)
{

    global $base_url;
    $str = '';
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT customerNum,orderNum,orderDate FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE subscriptionKey=? limit 1");
    $pdoQuery->execute([$licenseKey]);
    $download_res = $pdoQuery->fetch();
    if (safe_count($download_res) > 0) {
        $customerNum = $download_res['customerNum'];
        $orderNum = $download_res['orderNum'];
        if ($customerNum != '' && $orderNum != '') {
            $str = 0;
        } else if ($customerNum == '' && $orderNum == '') {
            $str = getEntitlementSKU('', $licenseKey);
        }
    } else {
        $str = 2;
    }
    return $str;
}

function getEntitlementSKU($custid, $licenseKey)
{

    global $base_url;
    $str = '';
    $pdo = pdo_connect();

    if ($licenseKey != '') {
        $pdoQuery = $pdo->prepare("SELECT customerNum,orderNum,orderDate,SKUNum,siteName FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE subscriptionKey=? limit 1");
        $pdoQuery->execute([$licenseKey]);
        $download_res = $pdoQuery->fetch();
        if (safe_count($download_res) > 0) {
            $customerNum = $download_res['customerNum'];
            $orderNum = $download_res['orderNum'];
            $SKUNum = $download_res['SKUNum'];
        }
    } else if ($custid != 0 || $custid != '') {
        $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where siteName IN (SELECT siteName FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE id=?) group by SKUNum");
        $pdoQuery->execute([$custid]);
        $download_res = $pdoQuery->fetchAll();

        if (safe_count($download_res) > 0) {
            foreach ($download_res as $key => $val) {
                $customerNum = $val['customerNum'];
                $orderNum = $val['orderNum'];
                $SKUNum = $val['SKUNum'];
                $skuDetls = getNoofPcsBySku($SKUNum);
                $skuDesc = $skuDetls[3];
                $skuId = $skuDetls[6];
                $str .= '<option value="' . $skuId . '" selected>' . $skuDesc . '</option>';
            }
        } else {
            $str = '<option value="" selected>Not Available</option>';
        }
    }

    return $str;
}

function getSignUpDtl($vcode)
{

    global $base_url;
    $str = '';
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "agent.signup where vCode=?");
    $pdoQuery->execute([$vcode]);
    $signup_res = $pdoQuery->fetch();
    if (safe_count($signup_res) > 0) {

        $name = $signup_res['firstName'];
        $lastName = $signup_res['lastName'];
        $companyname = $signup_res['companyname'];
        $email = $signup_res['email'];
        $phoneid = $signup_res['phoneno'];
        $customerType = $signup_res['customerType'];
        $msg = array("msg" => "success", "name" => $name, "lastName" => $lastName, "companyname" => $companyname, "email" => $email, "phoneno" => $phoneid, "customerType" => $customerType);
    } else {
        $msg = array("msg" => "invalid");
    }
    return $msg;
}

function userSignUp($firstname, $lastname, $phoneId, $companyName, $emailId, $customerType)
{

    global $base_url;
    $str = '';
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT firstName,lastName,phoneno from " . $GLOBALS['PREFIX'] . "agent.signup where email=? limit 1");
    $pdoQuery->execute([$emailId]);
    $res = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email=? limit 1");
    $pdoQuery->execute([$emailId]);
    $res_user = $pdoQuery->fetch();

    $pdoQuery = $pdo->prepare("SELECT eid from " . $GLOBALS['PREFIX'] . "agent.channel C where (C.emailId =? or C.companyName=?)  limit 1");
    $pdoQuery->execute([$emailId, $companyName]);
    $res_channel = $pdoQuery->fetch();

    if (safe_count($res) == 0 && safe_count($res_user) == 0 && safe_count($res_channel) == 0) {
        $time = time();
        $vcode = getPasswordId1();

        $channelInsertSql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "agent.signup set firstName=?,lastName=?,phoneno=?,email=?,companyname=?,vCode=?,createdDate =?");
        $channelInsertSql->execute([$firstname, $lastname, $phoneId, $emailId, $companyName, $vcode, $time]);
        $signup_result = $pdo->lastInsertId();

        if ($signup_result) {

            $ret = sendSignUpUserEmail($firstname, $emailId, $vcode);
            if ($ret == 1) {
                return 'Hi ' . $firstname . ', You are successfully signed up. Please access your email for further instructions.';
            } else {
                return 'Hi ' . $firstname . ', You are successfully signed up, fail to send mail.';
            }
        } else {
            return 'Fail to sign up. Please try later.';
        }
    } else {
        return 'Email id or company name already registered.';
    }
}

function sendSignUpUserEmail($userName, $userEmail, $passid)
{
    global $base_url;
    $to = $userEmail;
    $toName = $userName;

    $subject = "Nanoheal new user authentication";

    $resetLink = $base_url . 'pass_reset.php?vid=' . $passid . '&type=signup';

    $body = "
                <html>
                <body>
                        <div style='color:#0000CC; font-family:Courier New, Courier, monospace; font-size:13px;'>
                                Hi $userName ,
                                <br /><br />
                                Please <a href='" . $resetLink . "' style='color:#CC0000'>click here</a> to set your new password and log into the Nanoheal portal.<br /><br />

                                Note: This is a system generated email. Please do not reply to this email.<br /><br />

                                Thanks, <br />
                                Nanoheal support team
                        </div>
                </body>
                </html>
                ";

    $fromEmail = 'noreply@nanoheal.com';
    $fromName = 'Support';

    $headers = '';
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . $fromName . ' <' . $fromEmail . '>' . "\r\n";
//    if (!mail($to, $subject, $body, $headers)) {
    // send from visualisationService
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $to,
      'subject' => $subject,
      'text' =>'',
      'html' => $body,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
  if (!CURL::sendDataCurl($url, $arrayPost)) {
  return 0;
    } else {
        return 1;
    }
}

function get_confirmTrial($skuVal, $compId, $licenseKey)
{
    global $pdo;
    $pdo = pdo_connect();

    if ($skuVal != '') {
        $pdoQuery = $pdo->prepare("SELECT payment_mode,skuRef,skuName FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster where id =? limit 1");
        $pdoQuery->execute([$skuVal]);
        $res = $pdoQuery->fetch();

        $payment = $res["payment_mode"];
        if ($payment == "Trial") {
            $skuNum = $res["skuRef"];
            $pdoQuery = $pdo->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder where compId =? and SKUNum =? limit 1");
            $pdoQuery->execute([$compId, $skuNum]);
            $confirm_res = $pdoQuery->fetch();
            if (safe_count($confirm_res) > 0) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    } else if ($licenseKey != '') {
        return 0;
    }
}

function get_confirmEntitleMentTrial($skuVal, $compId)
{
    global $pdo;
    $pdo = pdo_connect();
    $pdoQuery = $pdo->prepare("SELECT count(C.id) cnt,C.SKUNum,M.payment_mode FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.customerOrder S," . $GLOBALS['PREFIX'] . "agent.skuMaster M where C.id =?  and C.SKUNum=S.SKUNum and S.SKUNum=M.skuRef group by C.SKUNum");
    $pdoQuery->execute([$skuVal]);
    $confirm_res = $pdoQuery->fetch();
    $payment_mode = $confirm_res["payment_mode"];
    $count = $confirm_res["cnt"];
    if ($count > 1 && $payment_mode == "Trial") {
        return 1;
    } else {
        return 0;
    }
}

function subscriptionKeyGen()
{
    global $serverCode;
    $key = md5(microtime());
    $new_key = '';
    $keyVal = '';
    for ($i = 1; $i <= 21; $i++) {
        $new_key .= $key[$i];
        $keyVal .= $key[$i];
    }

    return array("0" => strtoupper($serverCode . $keyVal), "1" => strtoupper($serverCode . $keyVal));
}

function getEntitleUrl($sid, $id)
{

    if ($sid != 0 || $sid != '0') {
        $res = get_entitlement_DownloadURL($sid);
    } else {
        $res = get_entitlement_DownloadURL_id($id);
    }

    return $res;
}

function get_entitlement_DownloadURL($sid)
{
    global $pdo;
    global $base_url;
    $pdo = pdo_connect();
    $pdoQuery = $pdo->prepare("SELECT downloadId,downloadStatus FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE sid =? limit 1");
    $pdoQuery->execute([$sid]);
    $url_res = $pdoQuery->fetch();
    if (safe_count($url_res) > 0) {
        $downloadStatus = $url_res["downloadStatus"];
        if ($downloadStatus != 'EXE') {
            $downloadUrl = $base_url . 'eula.php?sid=' . $url_res["downloadId"];
        } else {
            $downloadUrl = 'Client already installed. Please Revoke the order to get URL.';
        }
        return $downloadUrl;
    } else {
        return "Url does not exist";
    }
}

function get_entitlement_DownloadURL_id($id)
{
    global $pdo;
    global $base_url;
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT C.downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C WHERE C.id =? limit 1");
    $pdoQuery->execute([$id]);
    $url_res = $pdoQuery->fetch();
    if (safe_count($url_res) > 0) {
        $downloadUrl = $base_url . 'eula.php?id=' . $url_res["downloadId"];
        return $downloadUrl;
    } else {
        return "Url does not exist";
    }
}

function getUserChannelRights($uid)
{

    global $pdo;
    global $base_url;
    $pdo = pdo_connect();

    $pdoQuery = $pdo->prepare("SELECT ch_id,entity_id,channel_id,subch_id,customer_id,user_priv from " . $GLOBALS['PREFIX'] . "core.Users where userid =?");
    $pdoQuery->execute([$uid]);
    $url_res = $pdoQuery->fetch();
    if (safe_count($url_res) > 0) {
        return $url_res;
    } else {
        return '';
    }
}

function regenerateOTP()
{
    global $timer;
    $pdo = pdo_connect();
    $username = url::requestToAny('email');

    $otp = rand(100000, 999999);

    $time = time() + $timer;
    sendMailOtp($otp, $username);
    $sql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set otp=?,otp_expiretime=? where user_email=?");
    $sql->execute([$otp, $time, $username]);
    $sqlRes = $pdo->lastInsertId();
}
