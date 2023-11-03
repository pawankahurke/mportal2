<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-dbConnect.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/include/common_functions.php';
include_once 'lib/l-crmdetls.php';

global $CRMEN;

$sessionid = url::issetInRequest('sessionid') ? url::requestToAny('sessionid') : '';
$confid = url::issetInRequest('confid') ? url::requestToAny('confid') : '';
$serviceTag = url::issetInRequest('servicetag') ? url::requestToAny('servicetag') : '';
$act = url::issetInRequest('act') ? url::requestToAny('act') : '';
$machineManufacture = url::issetInRequest('machineManufacture') ? str_replace("'", "", url::requestToAny('machineManufacture')) : '';
$machineModelNum = url::issetInRequest('machineModelNum') ? str_replace("'", "", url::requestToAny('machineModelNum')) : '';
$machineName = url::issetInRequest('machinename') ? str_replace("'", "", url::requestToAny('machinename')) : '';
$os = url::issetInRequest('os') ? str_replace("'", "", url::requestToAny('os')) : '';
$customerno = url::issetInRequest('customerno') ? str_replace("'", "", url::requestToAny('customerno')) : '';
$orderno = url::issetInRequest('orderno') ? str_replace("'", "", url::requestToAny('orderno')) : '';
$macaddress = url::issetInRequest('macAddress') ? str_replace("'", "", url::requestToAny('macAddress')) : '';
$ftpUrl = url::issetInRequest('url') ? str_replace("'", "", url::requestToAny('url')) : '';

$sessionid = str_replace("'", "", $sessionid);
$serviceTag = str_replace("'", "", $serviceTag);
$act = str_replace("'", "", $act);

if ($sessionid != '' && ($act == 'D' || $act == 'download' || $act == 'R')) {
    $returnStatus = addServiceRequest($sessionid, $serviceTag, $act, $macaddress);
    echo $returnStatus;
} else if ($sessionid != '' && $act == 'del') {

    $returnStatus = updateServiceRequest($sessionid, $serviceTag, $machineManufacture, $machineModelNum, $machineName, $os, $macaddress);
    echo $returnStatus;
} else if ($confid != '' && $act == 'swd') {

    $returnStatus = getsoftDisValue($confid, $serviceTag);
    echo $returnStatus;
} else if ($customerno != '' && $orderno != '' && ($act == 'D' || $act == 'download' || $act == 'R')) {

    $returnStatus = addiosServiceRequest($customerno, $orderno, $serviceTag, $act);
    echo $returnStatus;
} else if ($customerno != '' && $orderno != '' && $act == 'del') {

    $returnStatus = updateIosServiceRequest($customerno, $orderno, $serviceTag, $machineManufacture, $machineModelNum, $machineName, $os);
    echo $returnStatus;
} else if ($act == 'FTP' && $ftpUrl != '') {

    $returnStatus = getFTPdetails($ftpUrl);
    echo $returnStatus;
}

function addServiceRequest($sessionid, $serviceTag, $act, $macaddress)
{
    global $CRMEN;

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);

    $pdo = pdo_connect();

    if ($serviceTag != '') {
        $currentDate = time();

        $sql_cust = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where sessionid = ? limit 1");
        $sql_cust->execute([$sessionid]);
        $res_cust = $sql_cust->fetch(PDO::FETCH_ASSOC);

        if ($res_cust) {
            $sql_serReq = $pdo->prepare("select S.customerNum customerNum,S.orderNum orderNum,C.sessionIni,"
                . "C.remoteSessionURL,C.validity,C.backupCapacity,C.noOfPc,C.processId,C.compId,C.siteName,"
                . "C.advSub from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S, " . $GLOBALS['PREFIX'] . "agent.customerOrder C where S.sessionid=? and (S.downloadStatus = 'D' "
                . "or S.downloadStatus = 'G') and S.customerNum = C.customerNum and S.orderNum=C.orderNum limit 1");
            $sql_serReq->execute([$sessionid]);
            $res_cust = $sql_serReq->fetch(PDO::FETCH_ASSOC);
        }

        $customerNum = $res_cust['customerNum'];
        $orderNum = $res_cust['orderNum'];
        $pId = $res_cust['processId'];
        $cId = $res_cust['compId'];
        $siteName = $res_cust['siteName'];
        $advSub = $res_cust['advSub'];

        if ($advSub == '0' || $advSub == 0) {

            $sql_ser = "select count(sid) as pcCount from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNum . "' and orderNum='" . $orderNum . "' and processId='$pId' limit 1";
            $res_ser = find_one($sql_ser, $conn);
            $pcCount = $res_ser['pcCount'];

            $sql_sertag = "select * from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNum . "' and orderNum='" . $orderNum . "' and serviceTag='" . $serviceTag . "' and processId='$pId' order by sid desc limit 1";
            $res_sertag = find_one($sql_sertag, $conn);
            $downloadStatus = $res_sertag['downloadStatus'];
            if ((safe_count($res_cust) > 0) && (safe_count($res_sertag) == 0)) {

                $cust_ini = $res_cust['sessionIni'];
                $rSessionUrl = $res_cust['remoteSessionURL'];
                $validity = intval($res_cust['validity']);
                $backUpCapacity = $res_cust['backupCapacity'];
                $uninstallDb = $res_cust['contractEndDate'];
                $noOfPc = $res_cust['noOfPc'];
                $agentEmailId = $res_cust['agentId'];

                $sql_prod = "select * from processMaster P where P.pId = '$pId'";
                $res_prod = find_one($sql_prod, $conn);
                $respectiveDB = $res_prod['DbIp'];

                if (($noOfPc >= $pcCount) || ($noOfPc == 0)) {

                    $sessionid1 = md5(mt_rand());

                    $appendString = "RemoteSessionURL=" . $rSessionUrl;
                    $fcust_ini = $cust_ini;
                    if (($noOfPc == 0 || (safe_count($res_sertag) == 0)) && ($act == 'D' || $act == 'download')) {
                        $pcCount = $pcCount + 1;
                        $downloadId = INI_ServiceDownloadId();
                        $sql_seriveTag = "insert into " . $GLOBALS['PREFIX'] . "agent.serviceRequest set customerNum = '" . $customerNum . "' , orderNum = '" . $orderNum . "' , sessionid = '" . $sessionid1 . "' ,installationDate='" . $currentDate . "',uninstallDate='" . $uninstallDb . "',iniValues = '" . $fcust_ini . "', createdTime = '" . $currentDate . "', backupCapacity = '" . $backUpCapacity . "', downloadStatus  ='D' , revokeStatus = 'I', pcNo='$pcCount',serviceTag='$serviceTag',agentPhoneId='" . $agentEmailId . "',processId='$pId',compId = '$cId',siteName='" . $siteName . "',downloadId='" . $downloadId . "',macAddress='" . $macaddress . "'";
                        $result_service = redcommand($sql_seriveTag, $conn);
                        if ($result_service > 0) {

                            $db = db_connect();
                            db_change($GLOBALS['PREFIX'] . "agent", $conn);
                            if ($CRMEN == 1) {
                            }
                            return $fcust_ini;
                        }
                    } else {
                        return $fcust_ini;
                    }
                } else {

                    return "no of installation exceeded.";
                }
            } elseif (safe_count($res_cust) > 0 && safe_count($res_sertag) > 0) {
                if ($downloadStatus != 'EXE') {
                    return $res_sertag['iniValues'];
                } else {
                    $msg = 'notfound';
                    return $msg;
                }
            } else {
                $msg = 'notfound';
                return $msg;
            }
        } elseif ($advSub == 1 || $advSub == '1') {

            $processId = $res_cust['processId'];
            $compId = $res_cust['compId'];
            $cust_ini = $res_cust['sessionIni'];

            $sql_ch = "select C.eid,C.entityId,C.channelId from " . $GLOBALS['PREFIX'] . "agent.channel C, " . $GLOBALS['PREFIX'] . "agent.channel H where C.eid='$compId' and C.channelId=H.eid and H.ctype=2";
            $res_ch = find_one($sql_ch, $conn);
            if (safe_count($res_ch) > 0) {

                $chnlid = $res_ch['channelId'];
                $dt = time();
                $sql_sub = "select id,chnl_id,skuNum,skuDesc,licenseCnt,installCnt,purchaseDate,contractEndDate,trial from " . $GLOBALS['PREFIX'] . "agent.orderDetails where chnl_id='$compId' and contractEndDate >='$dt' and status=1 and  licenseCnt > installCnt limit 1";
                $res_sub = find_one($sql_sub, $conn);
                if (safe_count($res_sub) > 0) {

                    $contractEndDate = $res_sub['contractEndDate'];
                    $licOrderId = $res_sub['id'];
                    $trialState = $res_sub['trial'];

                    $sql_sertag = "select sid,customerNum,orderNum,serviceTag,downloadStatus,uninstallDate,iniValues from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNum . "' and orderNum='" . $orderNum . "' and serviceTag='" . $serviceTag . "' and processId='$processId' order by sid desc limit 1";
                    $res_sertag = find_one($sql_sertag, $conn);
                    $insCnt = safe_count($res_sertag);
                    $downloadStatus = $res_sertag['downloadStatus'];
                    $uninstallDate = $res_sertag['uninstallDate'];
                    $seriniValues = $res_sertag['iniValues'];

                    if ($contractEndDate > time() && $insCnt == 0) {

                        $sql_ser = "select count(sid) insCnt from (select sid from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.compId in($compId) and revokeStatus = 'I' group by S.customerNum,S.orderNum,S.serviceTag) as X";
                        $res_ser = find_one($sql_ser, $conn);
                        $tinsCnt = $res_ser['insCnt'];

                        $sql_ordtl = "select sum(licenseCnt) total from orderDetails where chnl_id='$compId'";
                        $res_ordtl = find_one($sql_ordtl, $conn);
                        $totalCnt = $res_ordtl['total'];
                        if ($totalCnt > $tinsCnt) {

                            $sessionid1 = md5(mt_rand());

                            $uninstallDate = Date("m/d/Y", $contractEndDate);
                            $uninstallDb = "UniDays=" . $uninstallDate;

                            $tpos = strrpos($cust_ini, "UniDays", 0);
                            $cust_ini = str_replace("UniDays=", $uninstallDb, $cust_ini);

                            $appendString = "\nRemoteSessionURL=" . $rSessionUrl;
                            $fcust_ini = $cust_ini . $appendString;

                            $pcCount = $pcCount + 1;
                            $downloadId = INI_ServiceDownloadId();
                            $sql_seriveTag = "insert into " . $GLOBALS['PREFIX'] . "agent.serviceRequest set customerNum = '" . $customerNum . "' , orderNum = '" . $orderNum . "' , sessionid = '" . $sessionid1 . "' ,installationDate='" . $currentDate . "',uninstallDate='" . $contractEndDate . "',iniValues = '" . $fcust_ini . "', createdTime = '" . $currentDate . "', backupCapacity = '" . $backUpCapacity . "', downloadStatus  ='D' , revokeStatus = 'I', pcNo='$pcCount',serviceTag='$serviceTag',agentPhoneId='" . $agentEmailId . "',processId='$pId',compId = '$cId',siteName='" . $siteName . "',downloadId='" . $downloadId . "',macAddress='" . $macaddress . "',orderStatus='Active'";
                            $result_service = redcommand($sql_seriveTag, $conn);
                            if ($result_service > 0) {
                                $sid = mysql_insert_id();
                                if ($CRMEN == 1) {
                                    updateCRMInsCnt($cId, $trialState, $conn);
                                }
                                $licupd = "update orderDetails SET installCnt = installCnt+1 where id='$licOrderId'";
                                $result_licupd = redcommand($licupd, $conn);

                                return $fcust_ini;
                            } else {
                                $msg = 'notfound';
                                return $msg;
                            }
                        } else {

                            echo "No of installation exceeded.";
                        }
                    } elseif ($insCnt > 0) {

                        if ($uninstallDate > time() && $downloadStatus == 'D') {
                            return $seriniValues;
                        } else if ($uninstallDate > time() && $downloadStatus == 'EXE') {

                            $seriniValues = revokeOrder($sessionid, $serviceTag, $macaddress, $customerNum, $orderNum, $pId);
                            return $seriniValues;
                        } else {
                            return 'This contract has been Expired/Cancelled for this device.';
                        }
                    } else {
                        return "No valid contract.";
                    }
                } else {
                    echo "No of installation exceeded.";
                }
            }
        } else {
            echo 'notfound';
        }
    }
}

function updateCRMInsCnt($cid, $trialState, $conn)
{

    $sql_insCnt = "select id,emailId,chId,crmUserId,crmLeadId,mauticId,downloadCnt,installCnt,trialInstCnt from " . $GLOBALS['PREFIX'] . "agent.contactDetails where chId='$cid' limit 1";
    $res_DnlCnt = find_one($sql_insCnt, $conn);
    if (safe_count($res_DnlCnt) > 0) {
        $inlCnt = $res_DnlCnt['installCnt'];
        $trialInstCnt = $res_DnlCnt['trialInstCnt'];
        $mauticId = $res_DnlCnt['mauticId'];
        $cnid = $res_DnlCnt['id'];
        $crmCntId = $res_DnlCnt['crmUserId'];
        if ($trialState == 1) {
            $trialInstCnt = $trialInstCnt + 1;
        } else {
            $inlCnt = $inlCnt + 1;
        }

        $dnlcnt_sql = "update " . $GLOBALS['PREFIX'] . "agent.contactDetails set installCnt='$inlCnt',trialInstCnt='$trialInstCnt' where id='$cnid'";
        $res_login = redcommand($dnlcnt_sql, $conn);
        if ($trialState == 1) {
            RSLR_updateTrialInstlCnt($crmCntId, $trialInstCnt, $mauticId);
        } else {
            RSLR_updateInstlCnt($crmCntId, $inlCnt, $mauticId);
        }
    }
}

function checkMacAddress($sessionid, $serviceTag, $macAddress, $customerNo, $orderNo, $processId)
{

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);

    $macAddress1 = str_replace("'", "", $macAddress);

    $mystring = $macAddress1;
    $findme = ',';
    $pos = strpos($mystring, $findme);
    if ($pos === false) {
        $sql_sertag = "select sid,customerNum,orderNum from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNo . "' and orderNum='" . $orderNo . "' and serviceTag='" . $serviceTag . "' and processId='$processId' and macAddress like '%$macAddress1%' order by sid desc limit 1";
        $res_sertag = find_one($sql_sertag, $conn);
        if (safe_count($res_sertag) > 0) {
            return 1;
        } else {
            return 0;
        }
    } else {

        $macAdd = explode(",", $macAddress1);
        foreach ($macAdd as $macId) {

            $sql_sertag = "select sid,customerNum,orderNum from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNo . "' and orderNum='" . $orderNo . "' and serviceTag='" . $serviceTag . "' and processId='$processId' and macAddress like '%$macId%' order by sid desc limit 1";
            $res_sertag = find_one($sql_sertag, $conn);

            if (safe_count($res_sertag) > 0) {
                return 1;
            }
        }
        return 0;
    }
}

function revokeOrder($sessionid, $serviceTag, $macAddress, $customerNum, $orderNum, $pId)
{

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);

    $res = checkMacAddress($sessionid, $serviceTag, $macAddress, $customerNum, $orderNum, $pId);
    if ($res == 1) {

        $sql_sertag = "select * from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNum . "' and orderNum='" . $orderNum . "' and serviceTag='" . $serviceTag . "' and processId='$pId' order by sid desc limit 1";
        $res_sertag = find_one($sql_sertag, $conn);

        if (safe_count($res_sertag) > 0) {

            $sid = $res_sertag['sid'];
            $custNum = $res_sertag['customerNum'];
            $ordNum = $res_sertag['orderNum'];
            $serviceTag = $res_sertag['serviceTag'];
            $uninstallDate = $res_sertag['uninstallDate'];
            $iniValues = $res_sertag['iniValues'];
            $agentPhoneId = $res_sertag['agentPhoneId'];
            $backupCapacity = $res_sertag['backupCapacity'];
            $pcNo = $res_sertag['pcNo'];
            $macAddress1 = $res_sertag['macAddress'];
            $compId = $res_sertag['compId'];
            $siteName = $res_sertag['siteName'];

            $sessionid1 = md5(mt_rand());
            $currentDate = time();

            $macAddress1 = str_replace("'", "", $macAddress1);
            $macAddress1 = str_replace('"', "", $macAddress1);

            $downloadId = INI_ServiceDownloadId();
            $sql_seriveTag = "insert into  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set customerNum = '" . $custNum . "' , orderNum = '" . $ordNum . "' , sessionid = '" . $sessionid1 . "' ,installationDate='" . $currentDate . "',uninstallDate='" . $uninstallDate . "',iniValues = '" . mysql_real_escape_string($iniValues) . "', createdTime = '" . $currentDate . "', backupCapacity = '" . $backupCapacity . "', downloadStatus  ='D' , revokeStatus = 'I', pcNo='$pcNo',serviceTag='$serviceTag',agentPhoneId='" . $agentPhoneId . "',processId='$pId',compId = '$compId',macAddress='$macAddress1',oldServiceTag='$serviceTag',siteName='" . $siteName . "',orderStatus='Active',downloadId='" . $downloadId . "'";
            $result_service = redcommand($sql_seriveTag, $conn);

            if ($result_service) {
                $srid = mysqli_insert_id($conn);
                pushSR($srid);
                $sql_ser = "update  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set revokeStatus ='R'  where serviceTag='" . $serviceTag . "' and processId='$pId' and sid='$sid'";
                $result = redcommand($sql_ser, $conn);
                pushSR($sid);
                return $iniValues;
            }
        } else {
            return 'notfound';
        }
    }
}

function addiosServiceRequest($customerNo, $orderNo, $serviceTag, $act)
{

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);
    if ($serviceTag != '') {
        $currentDate = time();

        $sql_cust = "select * from customerOrder where customerNum='$customerNo' and  orderNum = '$orderNo' limit 1";
        $res_cust = find_one($sql_cust, $conn);

        if (safe_count($res_cust) == 0) {

            $sql_serReq = "select S.customerNum customerNum,S.orderNum orderNum,C.sessionIni,C.remoteSessionURL,C.validity,C.backupCapacity,C.noOfPc,C.processId,C.compId,C.siteName from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest S,customerOrder C where C.customerNum='$customerNo' and C.orderNum = '$orderNo' and (S.downloadStatus = 'D' or S.downloadStatus = 'G') and S.customerNum = C.customerNum and S.orderNum=C.orderNum  limit 1";
            $res_cust = find_one($sql_serReq, $conn);
        }

        $customerNum = $res_cust['customerNum'];
        $orderNum = $res_cust['orderNum'];
        $pId = $res_cust['processId'];
        $cId = $res_cust['compId'];
        $siteName = $res_cust['siteName'];

        $sql_ser = "select count(sid) as pcCount from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNum . "' and orderNum='" . $orderNum . "' and processId='$pId' limit 1";
        $res_ser = find_one($sql_ser, $conn);
        $pcCount = $res_ser['pcCount'];

        $sql_sertag = "select * from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNum . "' and orderNum='" . $orderNum . "' and serviceTag='" . $serviceTag . "' and processId='$pId' order by sid desc limit 1";
        $res_sertag = find_one($sql_sertag, $conn);
        $downloadStatus = $res_sertag['downloadStatus'];
        if ((safe_count($res_cust) > 0) && (safe_count($res_sertag) == 0)) {

            $cust_ini = $res_cust['sessionIni'];
            $rSessionUrl = $res_cust['remoteSessionURL'];
            $validity = intval($res_cust['validity']);
            $backUpCapacity = $res_cust['backupCapacity'];
            $uninstallDb = $res_cust['contractEndDate'];
            $noOfPc = $res_cust['noOfPc'];
            $agentEmailId = $res_cust['agentId'];

            $sql_prod = "select * from processMaster P where P.pId = '$pId' ";
            $res_prod = find_one($sql_prod, $conn);
            $respectiveDB = $res_prod['DbIp'];

            if (($noOfPc >= $pcCount) || ($noOfPc == 0)) {

                $sessionid1 = md5(mt_rand());

                $appendString = "\nRemoteSessionURL=" . $rSessionUrl;
                $fcust_ini = $cust_ini . $appendString;
                if (($noOfPc == 0 || (safe_count($res_sertag) == 0)) && ($act == 'D' || $act == 'download')) {
                    $pcCount = $pcCount + 1;
                    $downloadId = INI_ServiceDownloadId();
                    $sql_seriveTag = "insert into  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set customerNum = '" . $customerNum . "' , orderNum = '" . $orderNum . "' , sessionid = '" . $sessionid1 . "' ,installationDate='" . $currentDate . "',uninstallDate='" . $uninstallDb . "',iniValues = '" . $fcust_ini . "', createdTime = '" . $currentDate . "', backupCapacity = '" . $backUpCapacity . "', downloadStatus  ='D' , revokeStatus = 'I', pcNo='$pcCount',serviceTag='$serviceTag',agentPhoneId='" . $agentEmailId . "',processId='$pId',compId = '$cId',siteName='" . $siteName . "',downloadId='" . $downloadId . "'";
                    $result_service = redcommand($sql_seriveTag, $conn);
                    if ($result_service > 0) {

                        $db = db_connect();
                        db_change($GLOBALS['PREFIX'] . "agent", $conn);

                        return $fcust_ini;
                    }
                } else {
                    return $fcust_ini;
                }
            } else {

                return "no of installation exceeded.";
            }
        } elseif (safe_count($res_cust) > 0 && safe_count($res_sertag) > 0) {
            if ($downloadStatus != 'EXE') {
                return $res_sertag['iniValues'];
            } else {
                $msg = 'notfound';
                return $msg;
            }
        } else {
            $msg = 'notfound';
            return $msg;
        }
    } else {
        echo 'notfound';
    }
}

function updateServiceRequest($sessionid, $servicetag, $machineManufacture, $machineModelNum, $machineName, $os, $macaddress)
{

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);

    $pdo = pdo_connect();

    $currentDate = time();

    $sql_cust = $pdo->prepare("select * from customerOrder where sessionid = ? limit 1");
    $sql_cust->execute([$sessionid]);
    $res_cust = $sql_cust->fetch(PDO::FETCH_ASSOC);

    if ($res_cust) {

        $sql_serReq = $pdo->prepare("select S.customerNum customerNum,S.orderNum orderNum,C.sessionIni,"
            . "C.remoteSessionURL,C.validity,C.backupCapacity,C.noOfPc,C.processId from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S, "
            . "agent.customerOrder C where S.sessionid = ? and (S.downloadStatus = 'D' or S.downloadStatus = 'G') "
            . "and S.customerNum = C.customerNum and S.orderNum=C.orderNum limit 1");
        $sql_serReq->execute([$sessionid]);
        $res_cust = $sql_serReq->fetch(PDO::FETCH_ASSOC);
    }

    $pId = $res_cust['processId'];

    $sql_ser = $pdo->prepare("update  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set downloadStatus = ?, installationDate = ?, machineManufacture = ?, "
        . "machineModelNum = ?, machineOS = ? where serviceTag = ? and (downloadStatus = ? or downloadStatus = ?) and "
        . "processId = ?");
    $result = $sql_ser->execute(['EXE', $currentDate, $machineManufacture, $machineModelNum, $os, $servicetag, 'D', 'G', $pId]);
    if ($result > 0) {
        return $result;
    } else {
        return 0;
    }
}

function updateIosServiceRequest($customerNo, $orderNo, $servicetag, $machineManufacture, $machineModelNum, $machineName, $os)
{

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);

    $currentDate = time();

    $sql_cust = "select * from customerOrder where customerNum='$customerNo' and  orderNum = '$orderNo' limit 1";
    $res_cust = find_one($sql_cust, $conn);

    if (safe_count($res_cust) == 0) {

        $sql_serReq = "select S.customerNum customerNum,S.orderNum orderNum,C.sessionIni,C.remoteSessionURL,C.validity,C.backupCapacity,C.noOfPc,C.processId from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest S,customerOrder C where S.sessionid='" . $sessionid . "' and (S.downloadStatus = 'D' or S.downloadStatus = 'G') and S.customerNum = C.customerNum and S.orderNum=C.orderNum  limit 1";
        $res_cust = find_one($sql_serReq, $conn);
    }

    $pId = $res_cust['processId'];
    $customerNum = $res_cust['customerNum'];
    $orderNum = $res_cust['orderNum'];

    $sql_ser = "update  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set downloadStatus ='EXE', installationDate='" . $currentDate . "', machineManufacture = '" . $machineManufacture . "' , machineModelNum = '" . $machineModelNum . "', machineOS='" . $os . "'  where serviceTag='" . $servicetag . "' and (downloadStatus = 'D' or downloadStatus = 'G') and processId='$pId'";
    $result = redcommand($sql_ser, $conn);
    if ($result > 0) {

        db_change($GLOBALS['PREFIX'] . "node", $conn);

        $sql_machine = "update tempMachine set machineOs='" . $os . "',machineManufacture='" . $machineManufacture . "',customerNum='" . $customerNum . "',orderNum='" . $orderNum . "'  where host='" . $servicetag . "'";
        $result_machine = redcommand($sql_machine, $conn);

        db_change($GLOBALS['PREFIX'] . "agent", $conn);

        return $result;
    } else {
        return 0;
    }
}

function getsoftDisValue($confid, $serviceTag)
{

    // $conn = db_connect();
    // db_change($GLOBALS['PREFIX'] . "softinst", $conn);

    $retVal = getSoftDistData($confid);
    return $retVal;
}

function getSoftDistData($confid)
{
    $redis = RedisLink::connect();
    $package = $redis->HGET('SWDConf', $confid);
    return $package;
}


function INI_ServiceDownloadId()
{

    try {

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "agent", $db);

        $downloadId = INI_PasswordId();

        $sql_Coust = "select sid,customerNum,orderNum from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where downloadId='$downloadId'";
        $res_Coust = find_one($sql_Coust, $db);
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return INI_ServiceDownloadId();
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function INI_PasswordId()
{

    try {

        $character_set_array = array();
        $character_set_array[] = array('count' => 40, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');

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

function getLicenseKey($custnum, $ordNum)
{

    try {

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "agent", $db);

        $downloadId = 'Not Found';

        $sql_Coust = "select licenseKey,customerNum,orderNum from customerOrder where customerNum='$custnum' and orderNum='$ordNum' order by id desc limit 1";
        $res_Coust = find_one($sql_Coust, $db);
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return $res_Coust['licenseKey'];
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function addAviraIns($aviraDtl)
{

    try {

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "agent", $db);
        $sid = $aviraDtl['sid'];
        $sql_avira = "insert into aviraDetails set sid='" . $aviraDtl['sid'] . "',identifier='" . $aviraDtl['identifier'] . "',licenseSerial = '" . $aviraDtl['licenseSerial'] . "' , licenseExpiration = '" . $aviraDtl['licenseExpiration'] . "' , licenseType = '" . $aviraDtl['licenseType'] . "'"
            . " ,userCount='" . $aviraDtl['userCount'] . "',productName='" . $aviraDtl['productName'] . "',engineDate = '" . $aviraDtl['engineDate'] . "', "
            . "engineVersion = '" . $aviraDtl['engineVersion'] . "', productDate = '" . $aviraDtl['productDate'] . "', productVersion  ='" . $aviraDtl['productVersion'] . "' , "
            . "vdfDate = '" . $aviraDtl['vdfDate'] . "', vdfVersion='" . $aviraDtl['vdfVersion'] . "',webcatVersion='" . $aviraDtl['webcatVersion'] . "',productLanguage='" . $aviraDtl['productLanguage'] . "',"
            . "productId='" . $aviraDtl['productId'] . "',installPath = '" . mysqli_real_escape_string($db, $aviraDtl['installPath']) . "',lastUpdateDate='" . $aviraDtl['lastUpdateDate'] . "',updateRequiredState='" . $aviraDtl['updateRequiredState'] . "'";
        $result_avira = redcommand($sql_avira, $db);
        if ($result_avira) {
            $insid = mysqli_insert_id($db);
            $sql_ser = "update  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set aviraId ='$insid' where sid='$sid'";
            redcommand($sql_ser, $db);
            return 1;
        } else {
            return 0;
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function getSid($custnum, $ordnum, $servicetag)
{

    try {

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "agent", $db);

        $downloadId = '0';

        $sql_Coust = "select sid,customerNum,orderNum,serviceTag from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where customerNum = '$custnum' and orderNum='$ordnum' and serviceTag='$servicetag' order by sid desc limit 1";
        $res_Coust = find_one($sql_Coust, $db);
        $count = safe_count($res_Coust);
        if ($count > 0) {

            return $res_Coust['sid'];
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function getFTPdetails($ftpUrl)
{

    $conn = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $conn);

    $sql_serReq = "select * from ftpDetails where base_url='$ftpUrl' limit 1";
    $res_cust = find_one($sql_serReq, $conn);

    if (safe_count($res_cust) > 0) {
        $retMsg = $res_cust['combination'];
    } else {
        $retMsg = 'NOTFOUND';
    }
    return $retMsg;
}

function pushSR($sid)
{
    logs::trace(1, "CodeRemoved");
}

function pushSRData($pdata, $id)
{
    logs::trace(1, "CodeRemoved");
}
