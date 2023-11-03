<?php






include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';

function PTS_GetAllCustomers($key, $pdo, $ch_id)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select customerNum, orderNum, compId, processId, coustomerFirstName, coustomerLastName, emailId, "
            . "downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder GROUP BY customerNum ORDER BY id DESC");
        $sql->execute();
        $res = $sql->fetchAll();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetEntityOrder($key, $pdo, $ch_id)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.orderDetails D where D.chnl_id in (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where (eid=? or entityId=?)) order by id desc");
        $sql->execute([$ch_id, $ch_id]);
        $res = $sql->fetchAll();
        return $res;
    } else {
        echo "API Key is expired";
    }
}


function PTS_GetCustomerOrders($key, $pdo, $customerNumber)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select C.siteName, C.id, C.customerNum, C.orderNum, C.compId, C.processId, C.payRefNum,C.contractEndDate,C.orderDate,C.SKUNum, count(S.sid) installedCnt from "
            . "agent.customerOrder C left join " . $GLOBALS['PREFIX'] . "agent.serviceRequest S ON C.customerNum=S.customerNum and "
            . "C.orderNum=S.orderNum and S.revokeStatus='I' where C.customerNum = ? group by C.customerNum,C.orderNum");
        $sql->execute([$customerNumber]);
        $res = $sql->fetchAll();

        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "API Key is expired";
    }
}


function PTS_GetSitesDevices($key, $pdo = null, $custId, $processId, $custNumber, $ordNumber)
{
    $res = NanoDB::find_many("select C.siteName,C.id,C.customerNum,C.orderNum,C.compId,C.processId,C.orderDate,C.contractEndDate,C.SKUDesc,S.sid,S.serviceTag,S.installationDate,S.uninstallDate, S.machineManufacture, S.machineModelNum, "
        . "S.downloadStatus,S.revokeStatus,S.orderStatus,S.sid,ce.id as censusid from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.serviceRequest S,core.Census ce "
        . "where C.customerNum=S.customerNum and C.orderNum=S.orderNum and C.customerNum=? and "
        . "C.orderNum=? and C.compId=? and C.processId=? and S.revokeStatus='I' and ce.host=S.serviceTag group by S.serviceTag", null, [$custNumber, $ordNumber, $custId, $processId]);

    // $sql->execute([$custNumber, $ordNumber, $custId, $processId]);
    // $res = $sql->fetchAll();
    if ($res && safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}


function PTS_GetSKUForCountry($key, $pdo, $countryCode, $skuRef, $skuListType)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        switch ($skuListType) {
            case 1:
                $sql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE ccode = '" . $countryCode . "' AND skuType = 3");
                break;
            case 2:
                $sql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE id IN (SELECT renewSku FROM "
                    . "" . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuRef = '" . $skuRef . "')");
                break;
            case 3:
                $sql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE find_in_set(id, (SELECT Distinct(upgrdSku) FROM "
                    . "" . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuRef = '" . $skuRef . "'))");
                break;
            default:
                $sql = "";
                break;
        }
        $sql->execute();
        $res = $sql->fetchAll();
        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetSKUForCountryWithOS($key, $pdo, $countryCode, $skuRef, $skuListType, $osType)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        switch ($skuListType) {
            case 1:
                $sql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE ccode = '" . $countryCode . "' AND skuType = 3 AND (osType = '" . $osType . "' OR osType = 'All')");
                break;
            default:
                $sql = "";
                break;
        }
        $sql->execute();
        $res = $sql->fetchAll();
        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetProvisionSKU($key, $pdo)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $entityId = $_SESSION["user"]["entityId"];
        $channelId = $_SESSION["user"]["channelId"];

        $sql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuType = 3 and entityId = '" . $entityId . "' and chId = " . $channelId);
        $sql->execute();
        $res = $sql->fetchAll();
        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "API Key is expired";
    }
}


function PTS_GetSKUFeatures($key, $pdo)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select skuId,skuName,skuDescription FROM " . $GLOBALS['PREFIX'] . "agent.skuFeatures");
        $sql->execute();
        $res = $sql->fetchAll();
        if (safe_count($res)) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetRenewPlanDetails($pdo, $osType, $custNum, $orderNum, $skuarray)
{

    $sql = $pdo->prepare("select SKUNum,contractEndDate from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum='" . $custNum . "' and orderNum= '" . $orderNum . "' ");
    $sql->execute();
    $sqlRes = $sql->fetch();

    $currentDate = date('d-m-Y');
    $validDate = $sqlRes['contractEndDate'];
    $diff = abs($validDate - strtotime($currentDate));
    $days = floor($diff / (60 * 60 * 24));

    if ($days < 45) {
        $skuRef = $sqlRes['SKUNum'];
        $renewSql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE find_in_set(id ,(SELECT Distinct(renewSku) " .
            "FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuRef = ?))");
        $renewSql->execute([$skuRef]);
        $renewRes = $renewSql->fetchAll();
    }

    $skuDetails = $pdo->prepare("select skuId,skuName,skuDescription,skuGroup FROM " . $GLOBALS['PREFIX'] . "agent.skuFeatures");
    $skuDetails->execute();
    $skuRes = $skuDetails->fetchAll();
    $resultArray = array();
    $features = array();

    foreach ($skuRes as $key => $value) {
        $features[$value['skuGroup']][] = $value['skuDescription'];
    }

    $planSql = $pdo->prepare("select m.id as id,planName,planDetail,skuPrice,m.skuRef,m.skuName FROM " . $GLOBALS['PREFIX'] . "agent.skuPlanDetails s, " .
        "" . $GLOBALS['PREFIX'] . "agent.skuMaster m WHERE s.skuRef = m.skuRef AND osType=? group by m.skuRef order by s.planId");
    $planSql->execute([$osType]);
    $planResult = $planSql->fetchAll();
    foreach ($planResult as $keys => $val) {
        $column[$keys][] = $val['planName'];

        $planName = str_replace(" ", "", $val['planName']);
        \Stripe\Stripe::setApiKey("sk_test_pfo7bAfdfSSkasZkBysq1gf7");
        if (($planName == 'AnnualUnlimited') && (!empty($renewRes))) {
            $skuNum = $renewRes[0]['skuRef'];
            $val['skuRef'] = $skuNum;
        } else {
            $skuNum = $val['skuRef'];
        }

        $skuPrice = get_price($skuNum);
        $Price = $skuPrice == '.0' ? '0' : $skuPrice;
        $price[$planName][] = $Price;
        $price[$planName][] = $val['skuRef'];
        $price[$planName][] = $val['id'];
        foreach (safe_json_decode($val['planDetail']) as $sku => $vals) {
            $resultArray[$planName][$sku] = $vals;
        }
    }
    $result = array('features' => $features, 'rows' => $resultArray, 'price' => $price, 'status' => "SUCCESS");
    return $result;
}

function PTS_GetPlanDetails($pdo, $osType, $skuarray)
{

    $skuDetails = $pdo->prepare("select skuId,skuName,skuDescription FROM " . $GLOBALS['PREFIX'] . "agent.skuFeatures");
    $skuDetails->execute();
    $skuRes = $skuDetails->fetchAll();
    $resultArray = array();

    foreach ($skuRes as $key => $value) {
        $resultArray[$value['skuName']][] = $value['skuDescription'];
    }

    $planSql = $pdo->prepare("select m.id as id,m.planId,planName,planDetail,skuPrice,skuRef FROM " . $GLOBALS['PREFIX'] . "agent.skuPlanDetails s, " .
        "" . $GLOBALS['PREFIX'] . "agent.skuMaster m WHERE s.planId = m.planId AND os=? group by planId");
    $planSql->execute([$osType]);
    $planResult = $planSql->fetchAll();
    $column[0][] = '&nbsp;';

    foreach ($planResult as $keys => $val) {
        $column[++$keys][] = $val['planName'];

        $planName = str_replace(" ", "", $val['planName']);
        \Stripe\Stripe::setApiKey("sk_test_pfo7bAfdfSSkasZkBysq1gf7");
        $skuNum = $val['skuRef'];

        $skuPrice = get_price($skuNum);
        $Price = $skuPrice == '.0' ? '0' : $skuPrice;
        $price[$planName][] = $Price;
        $price[$planName][] = $val['skuRef'];
        $price[$planName][] = $val['id'];

        foreach (safe_json_decode($val['planDetail']) as $sku => $vals) {

            if ($vals == 0 || $vals == 1) {
                if (in_array($val['skuRef'], $skuarray)) {
                    $resultArray[$sku][] = 1;
                } else {
                    $resultArray[$sku][] = $vals;
                }
            } else {
                $resultArray[$sku][] = $vals;
            }
        }
        $tempPlanId = $val['planId'];
    }
    $result = array('column' => $column, 'rows' => $resultArray, 'price' => $price, 'status' => "SUCCESS");
    return $result;
}

function PTS_getSKURef($pdo, $cust, $ord, $skuref)
{

    $sql = $pdo->prepare("select customerNum,orderNum,SKUNum,oldorderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=? and orderNum = ?");
    $sql->execute([$cust, $ord]);
    $res = $sql->fetch();

    $ord = !empty($res['oldorderNum']) ? $res['oldorderNum'] : $res['orderNum'];

    if (safe_count($res) > 0 && !empty($res['oldorderNum'])) {
        array_push($skuref, $res['SKUNum']);
        return PTS_getSKURef($pdo, $res['customerNum'], $ord, $skuref);
    } else {
        $sql1 = $pdo->prepare("select customerNum,orderNum,SKUNum,oldorderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=? and orderNum= ?");
        $sql1->execute([$cust, $ord]);
        $res1 = $sql1->fetch();
        if (safe_count($res1) > 0) {
            array_push($skuref, $res['SKUNum']);
        }
        return $skuref;
    }
}


function PTS_CreateNewProvision($key, $pdo, $cId, $customerCountry, $SKU, $SKUNoOfPc, $customerNumber, $orderNumber, $customerFirstName, $customerLastName, $customerEmailId, $customerPhone, $orderDate, $osType, $ServiceDate)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        global $base_url;
        global $payment_url;
        $valLic = 5;
        $agentEmail = $_SESSION['user']['adminEmail'];
        $agentLogEmail = $_SESSION['user']['adminEmail'];

        $custResult = PTS_CheckCustomerDetails($customerEmailId, $pdo);

        if (safe_count($custResult) > 0) {
            $customerNum = $custResult['customerNum'];
        } else {
            $insertSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerDetails SET customerNum = ?,custFirstName = ?," .
                "custLastName = ?,custLogin = ? ");
            $insertSql->execute([$customerNumber, $customerFirstName, $customerLastName, $customerEmailId]);
            $sqlRes = $pdo->lastInsertId();
        }

        $isCustomerExist = PTS_IsNumberExist($key, $pdo, "customerNum", $customerNumber);
        if (safe_count($isCustomerExist) > 0) {
            return array("status" => "FAILED", "message" => "Support/Contract No. already exist, please enter another Support/Contract No.");
        }

        $isOrderExist = PTS_IsNumberExist($key, $pdo, "orderNum", $orderNumber);
        if (safe_count($isOrderExist) > 0) {
            return array("status" => "FAILED", "message" => "Work order No already exist, please enter another Work order No");
        }

        $proccess = PTS_GetProcessForCompany($key, $pdo, $cId);
        $pId = $proccess['pId'];

        $finStatusMsg = '';

        if ($cId == '' || $pId == '') {
            $finStatusMsg = "%%LOGINPROB%%";
            return array("status" => "FAILED", "message" => "Some error occurred, please try again later", "error" => "CID/PID is empty");
        }
        $custDtl = PTS_GetCompany_Details($key, $pdo, $cId);
        $channelId = $custDtl['channelId'];
        if ($custDtl == '') {
            return array("status" => "FAILED", "message" => "Server Not Found");
        } else {
            $reportserver = $custDtl["reportserver"];
        }

        $paymentId = PTS_GetPaymentId($pdo);
        $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
        PTS_SetProcessDetailSession($proccessDetail, $osType);
        $i = 0;
        foreach ($SKUNoOfPc as $skuid => $noofpc) {
            $skuDetls = PTS_GetSKUDetails($key, $pdo, "id", $skuid);
            if ($i > 0) {
                $orderNumber = PTS_GetAutoCustomerNumber($pdo);
            }
            if ($noofpc <= 0 || $noofpc == "0") {
                $noofpc = $skuDetls['licenseCnt'];
            }

            $timeZoneVal = date_default_timezone_get();
            date_default_timezone_set('America/Mexico_City');
            $finStatusMsg = PTS_InsertProvision($key, $pdo, $cId, $pId, $customerNumber, $orderNumber, $customerEmailId, $customerFirstName, $customerLastName, $reportserver, $provCode, $customerCountry, $fileString, $orderDate, $skuDetls, $proccessDetail, $paymentId, $noofpc, $osType, $ServiceDate);
            $i++;

            date_default_timezone_set($timeZoneVal);
        }

        return $finStatusMsg;
    } else {
        echo "API Key is expired";
    }
}

function PTS_InsertProvision($key, $pdo, $cId, $pId, $customerNumber, $orderNumber, $customerEmailId, $customerFirstName, $customerLastName, $reportserver, $provCode, $customerCountry, $fileString, $orderDate, $skuDetls, $proccessDetail, $paymentId, $noOfPc, $osType, $ServiceDate)
{
    global $payment_url;
    global $base_url;
    $remoteSession = "";
    $provCode = "01";
    $currentDate = time();
    $agentLogEmail = $_SESSION['user']['adminEmail'];
    $noOfDays = $skuDetls['noOfDays'];
    $skuDesc = $skuDetls['description'];
    $skuname = $skuDetls['skuName'];
    $skuRef = $skuDetls['skuRef'];
    $trial = $skuDetls['trial'];
    $renewDays = $skuDetls['renewDays'];
    $osType = $osType;

    $dateOfOrder = strtotime($orderDate);
    $dateOfService = strtotime($ServiceDate);
    $contractEDate = date('m/d/Y', strtotime($orderDate . '+' . $noOfDays . ' day'));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($orderDate)) . " +$noOfDays day");

    $refCustomerNum = PTS_GetAutoCustomerNumber($pdo);
    $refOrderNum = PTS_GetAutoCustomerNumber($pdo);

    $sitename = PTS_GetSiteNameForProcess($key, $pdo, $pId, $skuRef);
    if ($sitename == '') {
        return array("status" => "FAILED", "message" => "Site Code not found");
    }

    $variation = $proccessDetail['variation'];
    $locale = $proccessDetail['locale'];
    $downloadPath = $proccessDetail['downloaderPath'];
    $sendEmail = $proccessDetail['sendMail'];
    $backUp = $proccessDetail['backupCheck'];
    $respectiveDB = $proccessDetail['DbIp'];

    $fileString = PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNumber, $orderNumber, $customerEmailId, $contractEDate, $agentLogEmail, $reportserver, $trial, '', $renewDays);
    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = PTS_GetDownloadId($pdo);
        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $orderNumber . "', "
            . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . safe_addslashes($customerFirstName) . "', "
            . "coustomerLastName = '" . safe_addslashes($customerLastName) . "' , coustomerCountry = '" . trim($customerCountry) . "', "
            . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
            . "orderDate = '" . $dateOfOrder . "', ScheduleDate = '" . $dateOfService . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '0', "
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', "
            . "noOfPc = '" . trim($noOfPc) . "', oldorderNum ='', provCode = '" . $provCode . "', "
            . "remoteSessionURL = '" . $remoteSession . "',agentId = '" . $agentLogEmail . "',processId= '$pId', "
            . "compId='$cId',downloadId='" . $downloadId . "',paymentId='" . $paymentId . "',createdDate='" . time() . "',siteName='" . $sitename . "', "
            . "advSub='1',licenseKey = '0',nhOrderKey='0'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();


        if ($result > 0) {

            if ($trial == 1) {
                $dUrl = $base_url . 'eula.php?id=' . $downloadId;
                return array("status" => "SUCCESS", "message" => "Provision created successfully", "trial" => 1, "link" => $dUrl);
            } else {
                $dUrl = $base_url . 'eula.php?id=' . $downloadId;
                return array("status" => "SUCCESS", "message" => "Provision created successfully", "link" => $dUrl);
            }

            $finStatusMsg = $dUrl . '--' . $valLic;
        } else {

            return array("status" => "FAILED", "message" => "Provision not done, please try again later");
        }
    } else {
        $finStatusMsg = "NOTDONE--" . $valLic;
    }
}

function PTS_InsertProvisionSites($key, $pdo, $ch_id, $channelId, $siteName, $restrict)
{

    try {

        $serl_cust = $pdo->prepare("select id FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE customer= ? LIMIT 1");
        $serl_cust->execute([$siteName]);
        $serl_res = $serl_cust->fetch();

        if (safe_count($serl_res) > 0) {
        } else {
            if ($restrict == '0' || $restrict == 0) {
                $sqlEntity = $pdo->prepare("select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=? and U.user_priv=0");
                $sqlEntity->execute([$channelId]);
                $ent_res = $sqlEntity->fetchAll();
                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {

                        $addEnSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            } else {
                $sqlEntity = " select U.username,U.userid,U.entity_id,U.channel_id,U.subch_id,U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U "
                    . "where U.ch_id IN (select eid from " . $GLOBALS['PREFIX'] . "agent.channel where companyName = 'NH_MSP')";
                $sqlEntity->execute();
                $ent_res = $sqlEntity->fetchAll();
                if (safe_count($ent_res) > 0) {

                    foreach ($ent_res as $entValue) {

                        $addEnSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $entValue['username'] . "', '" . $siteName . "', 0, 0)");
                        $addEnSite->execute();
                        $result1 = $pdo->lastInsertId();
                    }
                }
            }
        }
        $sqlCustomer = $pdo->prepare("select U.username, U.userid, U.entity_id, U.channel_id, U.subch_id, U.customer_id from " . $GLOBALS['PREFIX'] . "core.Users U where U.ch_id=?");
        $sqlCustomer->execute([$ch_id]);
        $custmomer_res = $sqlCustomer->fetchAll();
        if (safe_count($custmomer_res) > 0) {

            foreach ($custmomer_res as $user) {

                $addCustSite = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, sitefilter, owner) VALUES ('" . $user['username'] . "', '" . $siteName . "', 0, 0)");
                $addCustSite->execute();
                $custresult = $pdo->lastInsertId();
            }
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNumber, $customerOrder, $customerEmailId, $contractEDate, $provCode, $serverId, $trial, $degrdSku, $renew)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        global $base_url;
        global $file_path;
        $INI_FILEPATH = 'customer/ini/';
        $INI_FILENAME = 'NanoHeal';
        $serverDetail = PTS_GetServerDetails($key, $pdo, $serverId);

        if (safe_count($serverDetail) > 0) {
            $url1 = explode("https://", $serverDetail["url"]);
            if ($serverDetail['advanced'] == 1) {
                $asseturl = explode("https://", $serverDetail["asseturl"]);
                $configurl = explode("https://", $serverDetail["configurl"]);
            } elseif ($serverDetail['advanced'] == 0) {
                $asseturl = explode("https://", $serverDetail["url"]);
                $configurl = explode("https://", $serverDetail["url"]);
            }
            $url2 = explode("http://", $serverDetail["url"]);
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

            $username = $serverDetail["username"];
            $password = $serverDetail["password"];
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
    } else {
        echo "API Key is expired";
    }
}

function PTS_SetProcessDetailSession($process_detail, $os)
{

    global $windowsChatUrl;
    global $androidChatUrl;
    $osType = strtolower($os);

    $_SESSION['lob']['phoneNumber'] = $process_detail['phoneNo'];
    if ($osType == 'windows') {
        $_SESSION['lob']['chatLink'] = $windowsChatUrl;
    } else {
        $_SESSION['lob']['chatLink'] = $androidChatUrl;
    }
    $_SESSION['lob']['ReplyEmailId'] = $process_detail['replyEmailId'];
    $_SESSION['lob']['serviceLink'] = $process_detail['serviceLink'];
    $_SESSION['lob']['privacyLink'] = $process_detail['privacyLink'];
    $_SESSION['lob']['variation'] = $process_detail['variation'];
    $_SESSION['lob']['locale'] = $process_detail['locale'];
    $_SESSION['lob']['SWLangCode'] = $process_detail['SWLangCode'];
    $_SESSION['lob']['folnDarts'] = $process_detail['folnDarts'];
    $_SESSION['lob']['FtpConfUrl'] = $process_detail['FtpConfUrl'];
    $_SESSION['lob']['WsServerUrl'] = $process_detail['WsServerUrl'];
    $_SESSION['lob']['DeployPath32'] = $process_detail['setupName32'];
    $_SESSION['lob']['DeployPath64'] = $process_detail['setupName64'];
    $_SESSION['lob']['defaultProfile'] = $process_detail['DPName'];
    return TRUE;
}

function PTS_GetCompany_Details($key, $pdo, $eid)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid=? LIMIT 1");
        $sql->execute([$eid]);
        $res = $sql->fetch();
        if (safe_count($res) > 0) {
            return $res;
        } else {
            return '';
        }
    } else {
        echo "API Key is expired";
    }
}

function formatChatLink($ChatLinkUrl, $Fname, $Lname, $Email)
{
    if ($ChatLinkUrl != '') {
        $Ftext = "fieldname_2=";
        $Ltext = "fieldname_3=";
        $Etext1 = "fieldname_4=";
        $Etext2 = "fieldname_6=";

        $ChatLink = str_replace($Ftext, $Ftext . $Fname, $ChatLinkUrl);
        $ChatLink = str_replace($Ltext, $Ltext . $Lname, $ChatLink);
        $ChatLink = str_replace($Etext1, $Etext1 . $Email, $ChatLink);
        $ChatLink = str_replace($Etext2, $Etext2 . $Email, $ChatLink);
    } else {
        $ChatLink = '';
    }
    return $ChatLink;
}

function PTS_GetCompany_DetailsByColumn($key, $pdo, $columnname, $columnvalue)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE " . $columnname . "='" . $columnvalue . "' LIMIT 1");
        $sql->execute();
        $res = $sql->fetch();
        if (safe_count($res) > 0) {
            return $res;
        } else {
            return '';
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetProcessForCompany($key, $pdo, $compId)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select pId,cId,processName FROM " . $GLOBALS['PREFIX'] . "agent.processMaster WHERE cId=? ORDER BY pId DESC LIMIT 1");
        $sql->execute([$compId]);
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetProcessDetails($key, $pdo, $pId)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.processMaster WHERE pId=? LIMIT 1");
        $sql->execute([$pId]);
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}


function PTS_GetServerDetails($key, $pdo, $serverId)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql_reg = $pdo->prepare("select url, advanced, asseturl, configurl, username, password "
            . "FROM " . $GLOBALS['PREFIX'] . "install.Servers WHERE serverid=? LIMIT 1");
        $sql_reg->execute([$serverId]);
        $res_reg = $sql_reg->fetch();
        return $res_reg;
    } else {
        echo "API Key is expired";
    }
}


function PTS_GetDownloadId($pdo)
{

    try {
        $downloadId = PTS_GetRandomCode();

        $sql_Coust = $pdo->prepare("select id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE downloadId=?");
        $sql_Coust->execute([$downloadId]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return PTS_GetDownloadId($pdo);
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}


function PTS_GetPaymentId($pdo)
{

    try {
        $paymentId = PTS_GetRandomCode();

        $sql_Coust = $pdo->prepare("select id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE paymentId=?");
        $sql_Coust->execute([$paymentId]);
        $res_Coust = $sql_Coust->fetch();
        $count = safe_count($res_Coust);
        if ($count > 0) {
            return PTS_GetPaymentId($pdo);
        }
        return $paymentId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}


function PTS_GetRandomCode()
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

function PTS_GetAutoCustomerNumber($pdo)
{
    $custnum = rand(1000000, 9999999999);
    $sql = $pdo->prepare("select id,customerNum,orderNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum=? OR "
        . "orderNum=? OR refCustomerNum =? OR refOrderNum =? OR oldorderNum =?");
    $sql->execute([$custnum, $custnum, $custnum, $custnum, $custnum]);
    $res = $sql->fetch();

    $count = safe_count($res);
    if ($count > 0) {
        PTS_GetAutoCustomerNumber($pdo);
    } else {
        return $custnum;
    }
}

function PTS_GetSKUDetails($key, $pdo, $columnName, $whereValue)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select id, noOfDays, licenseCnt, description, skuName, skuRef, trial, tax, renewDays,provCode FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE " . $columnName . " ='" . $whereValue . "' LIMIT 1");
        $sql->execute();
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetSkusDetails($key, $pdo, $columnName, $whereValue, $skuType)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select id, noOfDays, licenseCnt, description, skuName, skuRef, trial, tax FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE " . $columnName . " =? and skuType =?"
            . "LIMIT 1");
        $sql->execute([$whereValue, $skuType]);
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetSiteNameForProcess($key, $pdo, $pId, $SKU)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select R.sitename sitename,F.skuRef value from " . $GLOBALS['PREFIX'] . "agent.skuMaster F, " . $GLOBALS['PREFIX'] . "agent.custSkuMaster C, " . $GLOBALS['PREFIX'] . "agent.RegCode R where "
            . "F.skuRef='" . trim($SKU) . "' and F.id = C.skuId and C.siteId = R.id and C.pId = ? limit 1");
        $sql->execute([$pId]);
        $res = $sql->fetch();
        if (safe_count($res) > 0) {
            return $res['sitename'];
        } else {
            return '';
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetOrdersPayementStatus($key, $customerNumber, $orderNumber, $paymentRefNum)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        if ($paymentRefNum == NULL || $paymentRefNum == '') {
            return 'Pending';
        } else {
            return 'Done';
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_PushOrder($custNo, $sessionid)
{
    global $db_user;
    global $base_url;
    $dbIp = '104.154.53.224:4791';
    try {
        if ($dbIp != '') {
            $pdo = pdo_connect();
            if (!$pdo) {
                return 0;
            } else {
                $url = rtrim($base_url, '/');
                $queryString = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.orderDetails (customerNo, sessionId, reportUrl) VALUES (?, ?, ?)");
                $queryString->execute([$custNo, $sessionid, $url]);
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

function PTS_GetCustomerDetails($key, $pdo, $compId, $proccessId, $custNumber, $orderNumber)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select id, customerNum, orderNum, coustomerFirstName, coustomerLastName, emailId, SKUDesc,SKUNum, orderDate, FROM_UNIXTIME(orderDate, '%Y-%m-%d') orderDate1,contractEndDate, "
            . "agentId, downloadId, paymentId, oldorderNum, noOfPc, payRefNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum='$custNumber' AND "
            . "orderNum=? AND compId = ? AND processId =? LIMIT 1");
        $sql->execute([$orderNumber, $compId, $proccessId]);
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetOrderDetails($key, $pdo, $id)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select customerNum, orderNum, coustomerFirstName, coustomerLastName, coustomerCountry, emailId, SKUNum, "
            . "SKUDesc, FROM_UNIXTIME(orderDate, '%Y-%m-%d') orderDate, contractEndDate, SKUNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE id=? LIMIT 1");
        $sql->execute([$id]);
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetOrderDetailsByNumber($key, $pdo, $custNumber, $orderNumber)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select customerNum, orderNum, coustomerFirstName, coustomerLastName,compId,processId, coustomerCountry, emailId, SKUNum, sessionIni, siteName, "
            . "SKUDesc, FROM_UNIXTIME(orderDate, '%Y-%m-%d') orderDate, FROM_UNIXTIME(contractEndDate, '%Y-%m-%d') contractEndDate, createdDate, SKUNum, downloadId, noOfPc, paymentId, payRefNum FROM "
            . "" . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum=? AND orderNum=? LIMIT 1");
        $sql->execute([$custNumber, $orderNumber]);
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function RevokeUrlByServiceTag($key, $pdo, $customerNumber, $customerOrder)
{
    $res = array();
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $cust_sql = $pdo->prepare("select id,customerNum,orderNum,downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE orderNum = '" . $customerOrder . "' AND customerNum = '" . $customerNumber . "' ORDER BY id desc limit 1");
        $cust_sql->execute();
        $cust_res = $cust_sql->fetch();

        $tag_sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE orderNum = '" . $customerOrder . "' AND customerNum = '" . $customerNumber . "' ORDER BY sid desc limit 1");
        $tag_sql->execute();
        $tag_res = $tag_sql->fetch();

        if ($tag_res) {
            $servTag = $tag_res['serviceTag'];
            $downloadIdOld = $cust_res['downloadId'];
            $pId = $tag_res['processId'];
            $cId = $tag_res['compId'];
            $agentPhoneId = $_SESSION["user"]["username"];
            $finStatusMsg = '';

            if ($cId == '' || $pId == '') {
                $finStatusMsg = "%%LOGINPROB%%";
                return $finStatusMsg;
            }

            $sql_prod = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = ?");
            $sql_prod->execute([$pId]);
            $res_prod = $sql_prod->fetch();

            $_SESSION['lob']['phoneNumber'] = $res_prod['phoneNo'];
            $_SESSION['lob']['chatLink'] = $res_prod['chatLink'];
            $_SESSION['lob']['ReplyEmailId'] = $res_prod['replyEmailId'];
            $_SESSION['lob']['serviceLink'] = $res_prod['serviceLink'];
            $_SESSION['lob']['privacyLink'] = $res_prod['privacyLink'];
            $_SESSION['lob']['variation'] = $res_prod['variation'];
            $_SESSION['lob']['locale'] = $res_prod['locale'];
            $_SESSION['lob']['SWLangCode'] = $res_prod['SWLangCode'];
            $_SESSION['lob']['SWLangCode'] = $res_prod['SWLangCode'];
            $_SESSION['lob']['folnDarts'] = $res_prod['folnDarts'];
            $_SESSION['lob']['FtpConfUrl'] = $res_prod['FtpConfUrl'];
            $_SESSION['lob']['WsServerUrl'] = $res_prod['WsServerUrl'];

            $downloadPath = $res_prod['downloaderPath'];
            $currentDate = time();

            $update_sql = $pdo->prepare("UPDATE  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set revokeStatus='R' where orderNum = '" . $customerOrder . "' and customerNum = '" . $customerNumber . "' AND serviceTag = '" . $servTag . "' ");
            $update_sql->execute();
            $result_update = $pdo->lastInsertId();

            if ($result_update > 0) {

                $sessionid = md5(mt_rand());
                $downlId = PTS_GetDownloadId();
                $sql_seriveTag = $pdo->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "agent.serviceRequest set customerNum = '" . $customerNumber . "', orderNum = '" . $customerOrder . "', serviceTag = '" . $servTag . "', sessionid = '" . $sessionid . "', agentPhoneId = '" . $agentPhoneId . "', uninstallDate = '" . $tag_res['uninstallDate'] . "', iniValues = '" . mysqli_real_escape_string($tag_res['iniValues']) . "', machineManufacture = '" . $tag_res['machineManufacture'] . "', machineModelNum = '" . $tag_res['machineModelNum'] . "', createdTime = '" . $currentDate . "', backupCapacity = '" . $tag_res['backupCapacity'] . "', downloadStatus  ='G', oldServiceTag = '" . $servTag . "', revokeStatus = 'I', pcNo = '" . $tag_res['pcNo'] . "',downloadId='" . $downlId . "',processId=?, compId = ?");
                $sql_seriveTag->execute([$pId, $cId]);
                $result_service = $pdo->lastInsertId();
                if ($result_service > 0) {
                    $res['downloadId'] = $downloadIdOld;
                }
            }
        }
        return $res;
    } else {
        return $res;
    }
}

function PTS_GetAllOrdersForCustomer($key, $pdo, $custNumber)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select customerNum, orderNum, coustomerFirstName, coustomerLastName,compId,processId, coustomerCountry, emailId, SKUNum, sessionIni, siteName, "
            . "SKUDesc, FROM_UNIXTIME(orderDate, '%Y-%m-%d') orderDate, FROM_UNIXTIME(contractEndDate, '%Y-%m-%d') contractEndDate, createdDate, SKUNum, downloadId, noOfPc, paymentId, payRefNum FROM "
            . "" . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum=? ");
        $sql->execute([$custNumber]);
        $res = $sql->fetchAll();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_IsNumberExist($key, $pdo, $columnName, $number)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select id FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE " . $columnName . " = '" . $number . "' LIMIT 1");
        $sql->execute();
        $res = $sql->fetch();
        return $res;
    } else {
        echo "API Key is expired";
    }
}

function PTS_GetOrderHistory($key, $pdo, $compId, $proccessId, $custNumber, $orderNumber, $historyArray)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {

        $sql = $pdo->prepare("select id, customerNum, orderNum, coustomerFirstName, coustomerLastName, emailId, SKUDesc, FROM_UNIXTIME(orderDate, '%Y-%m-%d') orderDate, contractEndDate, "
            . "agentId, downloadId, paymentId, oldorderNum, noOfPc, payRefNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum='$custNumber' AND "
            . "orderNum=? AND compId = ? AND processId = ? ORDER BY ID DESC LIMIT 1");
        $sql->execute([$orderNumber, $compId, $proccessId]);
        $res = $sql->fetch();
        if (safe_count($res) > 0) {
            $historyArray[] = $res;
        }

        if ($res['oldorderNum'] != 0 && $res['oldorderNum'] != '' && $res['oldorderNum'] != null) {
            return PTS_GetOrderHistory($key, $pdo, $compId, $proccessId, $custNumber, $res['oldorderNum'], $historyArray);
        } else {
            return $historyArray;
        }
        return $historyArray;
    } else {
        echo "API Key is expired";
    }
}

function PTS_RenewProvision($key, $pdo, $cId, $customerNumber, $oldOrderNumber, $oldSkuRef, $newOrderNumber, $newSkuRef)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        global $base_url;
        global $payment_url;

        $isOrderExist = PTS_IsNumberExist($key, $pdo, "orderNum", $newOrderNumber);
        if (safe_count($isOrderExist) > 0) {
            return array("status" => "FAILED", "message" => "Order Number already exist, please enter another order number");
        }

        $oldOrderDetails = PTS_GetOrderDetailsByNumber($key, $pdo, $customerNumber, $oldOrderNumber);
        $customerCountry = $oldOrderDetails['coustomerCountry'];
        $customerFirstName = $oldOrderDetails['coustomerFirstName'];
        $customerLastName = $oldOrderDetails['coustomerLastName'];
        $customerEmailId = $oldOrderDetails['emailId'];
        $oldSkuNum = $oldOrderDetails['SKUNum'];
        $customerPhone = '';
        $orderDate = $oldOrderDetails['orderDate'];
        $oldContractEdate = $oldOrderDetails['contractEndDate'];
        $osType = $oldOrderDetails['osType'];

        $valLic = 5;
        $agentEmail = $_SESSION['user']['adminEmail'];
        $agentLogEmail = $_SESSION['user']['adminEmail'];

        $proccess = PTS_GetProcessForCompany($key, $pdo, $cId);
        $pId = $proccess['pId'];

        $currentDate = time();
        $remoteSession = '';
        $provCode = '01';
        $finStatusMsg = '';

        $refCustomerNum = PTS_GetAutoCustomerNumber($pdo);
        $refOrderNum = PTS_GetAutoCustomerNumber($pdo);

        if ($cId == '' || $pId == '') {
            $finStatusMsg = "%%LOGINPROB%%";
            return $finStatusMsg;
        }
        $custDtl = PTS_GetCompany_Details($key, $pdo, $cId);
        if ($custDtl == '') {
            return array("status" => "FAILED", "message" => "Server Not Found");
        } else {
            $reportserver = $custDtl["reportserver"];
        }
        $oldskuDetls = PTS_GetSKUDetails($key, $pdo, "skuRef", $oldSkuNum);
        $skuDetls = PTS_GetSKUDetails($key, $pdo, "skuRef", $newSkuRef);
        $noOfDays = $skuDetls['noOfDays'];
        $noOfPc = $skuDetls['licenseCnt'];
        $skuDesc = $skuDetls['description'];
        $skuname = $skuDetls['skuName'];
        $skuRef = $skuDetls['skuRef'];
        $renewDays = $skuDetls['renewDays'];

        $dateOfOrder = time();
        $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
        $contractEnd = strtotime(date("Y-m-d H:i:s", $oldContractEdate) . " +$noOfDays day");

        $noOfDaysSeconds = $noOfDays * 24 * 60 * 60;
        $contractEnd = strtotime(date("Y-m-d H:i:s", $oldContractEdate));
        $contractEndStamp = strtotime($oldContractEdate);
        $contractEndLast = $noOfDaysSeconds + $contractEndStamp;

        $dateOfOrder = time();
        $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
        $noOfDaysSeconds = $noOfDays * 24 * 60 * 60;
        $contractEnd = strtotime(date("Y-m-d H:i:s", $oldContractEdate));
        if ($oldskuDetls['trial'] == 1 || $oldskuDetls['trial'] == '1') {
            $contractEndLast = $dateOfOrder + $noOfDaysSeconds;
        } else {
            $contractEndStamp = strtotime($oldContractEdate);
            $contractEndLast = $noOfDaysSeconds + $contractEndStamp;
        }

        $sitename = PTS_GetSiteNameForProcess($key, $pdo, $pId, $newSkuRef);
        if ($sitename == '') {
            return array("status" => "FAILED", "message" => "Renew is not available");
        }

        $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
        PTS_SetProcessDetailSession($proccessDetail, $osType);

        $variation = $proccessDetail['variation'];
        $locale = $proccessDetail['locale'];
        $downloadPath = $proccessDetail['downloaderPath'];
        $sendEmail = $proccessDetail['sendMail'];
        $backUp = $proccessDetail['backupCheck'];
        $respectiveDB = $proccessDetail['DbIp'];

        $fileString = PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNumber, $orderNumber, $customerEmailId, $contractEDate, $provCode, $reportserver, '0', '', $renewDays);
        if ($fileString != 'FAIL' || $fileString != '') {

            $downloadId = PTS_GetDownloadId($pdo);
            $paymentId = PTS_GetPaymentId($pdo);
            $sessionid = md5(mt_rand());

            $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $newOrderNumber . "', "
                . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', "
                . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', "
                . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
                . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEndLast . "', backupCapacity = '0', "
                . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', "
                . "noOfPc = '" . trim($noOfPc) . "', oldorderNum ='" . $oldOrderNumber . "', provCode = '" . $provCode . "', "
                . "remoteSessionURL = '" . $remoteSession . "',agentId = '" . $agentLogEmail . "',processId= '$pId', "
                . "compId='$cId',downloadId='" . $downloadId . "',paymentId='" . $paymentId . "',createdDate='" . time() . "',siteName='" . $sitename . "', "
                . "advSub='1',licenseKey = '0',nhOrderKey='0',gateWayStatus='0', osType='" . $osType . "'");
            $sql_ser->execute();
            $result = $pdo->lastInsertId();

            $dUrl = $payment_url . "index.php?id=" . $paymentId;
            if ($result > 0) {
                return array("status" => "SUCCESS", "message" => "Provision renewed successfully", "link" => $dUrl);
                $finStatusMsg = $dUrl . '--' . $valLic;
            } else {
                return array("status" => "FAILED", "message" => "Provision renew not done, please try again later");
            }
        } else {
            $finStatusMsg = "NOTDONE--" . $valLic;
        }

        return $finStatusMsg;
    } else {
        echo "API Key is expired";
    }
}

function PTS_UpgradeProvision($key, $pdo, $cId, $customerNumber, $oldOrderNumber, $oldSkuRef, $newOrderNumber, $newSkuRef)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        global $base_url;
        global $payment_url;

        $isOrderExist = PTS_IsNumberExist($key, $pdo, "orderNum", $newOrderNumber);
        if (safe_count($isOrderExist) > 0) {
            return array("status" => "FAILED", "message" => "Order Number already exist, please enter another order number");
        }

        $oldOrderDetails = PTS_GetOrderDetailsByNumber($key, $pdo, $customerNumber, $oldOrderNumber);
        $customerCountry = $oldOrderDetails['coustomerCountry'];
        $customerFirstName = $oldOrderDetails['coustomerFirstName'];
        $customerLastName = $oldOrderDetails['coustomerLastName'];
        $customerEmailId = $oldOrderDetails['emailId'];
        $oldSkuNum = $oldOrderDetails['SKUNum'];
        $osType = $oldOrderDetails['osType'];
        $customerPhone = '';
        $orderDate = $oldOrderDetails['orderDate'];
        $oldContractEdate = $oldOrderDetails['contractEndDate'];

        $valLic = 5;
        $agentEmail = $_SESSION['user']['adminEmail'];
        $agentLogEmail = $_SESSION['user']['adminEmail'];

        $proccess = PTS_GetProcessForCompany($key, $pdo, $cId);
        $pId = $proccess['pId'];

        $currentDate = time();
        $remoteSession = '';
        $provCode = '01';
        $finStatusMsg = '';

        $refCustomerNum = PTS_GetAutoCustomerNumber($pdo);
        $refOrderNum = PTS_GetAutoCustomerNumber($pdo);

        if ($cId == '' || $pId == '') {
            $finStatusMsg = "%%LOGINPROB%%";
            return $finStatusMsg;
        }
        $custDtl = PTS_GetCompany_Details($key, $pdo, $cId);
        if ($custDtl == '') {
            return array("status" => "FAILED", "message" => "Server Not Found");
        } else {
            $reportserver = $custDtl["reportserver"];
        }

        $oldskuDetls = PTS_GetSKUDetails($key, $pdo, "skuRef", $oldSkuNum);
        $skuDetls = PTS_GetSKUDetails($key, $pdo, "skuRef", $newSkuRef);
        $noOfDays = $skuDetls['noOfDays'];
        $noOfPc = $skuDetls['licenseCnt'];
        $skuDesc = $skuDetls['description'];
        $skuname = $skuDetls['skuName'];
        $skuRef = $skuDetls['skuRef'];
        $renewDays = $skuDetls['renewDays'];

        $dateOfOrder = time();
        $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
        $noOfDaysSeconds = $noOfDays * 24 * 60 * 60;
        $contractEnd = strtotime(date("Y-m-d H:i:s", $oldContractEdate));
        if ($oldskuDetls['trial'] == 1 || $oldskuDetls['trial'] == '1') {
            $contractEndLast = $dateOfOrder + $noOfDaysSeconds;
        } else {
            $contractEndStamp = strtotime($oldContractEdate);
            $contractEndLast = $noOfDaysSeconds + $contractEndStamp;
        }


        $sitename = PTS_GetSiteNameForProcess($key, $pdo, $pId, $newSkuRef);
        if ($sitename == '') {
            return array("status" => "FAILED", "message" => "Upgrade is not available from Renew plan");
        }

        $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
        PTS_SetProcessDetailSession($proccessDetail, $osType);

        $variation = $proccessDetail['variation'];
        $locale = $proccessDetail['locale'];
        $downloadPath = $proccessDetail['downloaderPath'];
        $sendEmail = $proccessDetail['sendMail'];
        $backUp = $proccessDetail['backupCheck'];
        $respectiveDB = $proccessDetail['DbIp'];

        $fileString = PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNumber, $orderNumber, $customerEmailId, $contractEDate, $provCode, $reportserver, '0', '', $renewDays);
        if ($fileString != 'FAIL' || $fileString != '') {

            $downloadId = PTS_GetDownloadId($pdo);
            $paymentId = PTS_GetPaymentId($pdo);
            $sessionid = md5(mt_rand());

            $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $newOrderNumber . "', "
                . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFirstName . "', "
                . "coustomerLastName = '" . $customerLastName . "' , coustomerCountry = '" . trim($customerCountry) . "', "
                . "emailId= '" . trim($customerEmailId) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
                . "orderDate = '" . $dateOfOrder . "', contractEndDate = '" . $contractEndLast . "', backupCapacity = '0', "
                . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', "
                . "noOfPc = '" . trim($noOfPc) . "', oldorderNum ='" . $oldOrderNumber . "', provCode = '" . $provCode . "', "
                . "remoteSessionURL = '" . $remoteSession . "',agentId = '" . $agentLogEmail . "',processId= '$pId', "
                . "compId='$cId',downloadId='" . $downloadId . "',paymentId='" . $paymentId . "',createdDate='" . time() . "',siteName='" . $sitename . "', "
                . "advSub='1',licenseKey = '0',nhOrderKey='0',gateWayStatus='0', osType='" . $osType . "'");
            $sql_ser->execute();
            $result = $pdo->lastInsertId();

            $dUrl = $payment_url . "index.php?id=" . $paymentId;
            if ($result > 0) {
                return array("status" => "SUCCESS", "message" => "Provision renewed successfully", "link" => $dUrl);
                $finStatusMsg = $dUrl . '--' . $valLic;
            } else {
                return array("status" => "FAILED", "message" => "Provision renew not done, please try again later");
            }
        } else {
            $finStatusMsg = "NOTDONE--" . $valLic;
        }

        return $finStatusMsg;
    } else {
        echo "API Key is expired";
    }
}


function PTS_SendPaymentMail($key, $pdo, $customerNumber, $orderNumber, $custEmail)
{
    global $base_url;
    global $payment_url;

    $orderDetails = PTS_GetOrderDetailsByNumber($key, $pdo, $customerNumber, $orderNumber);
    $skuNum = $orderDetails['SKUNum'];
    $payementStatus = 'Done';
    $toName = $orderDetails['coustomerFirstName'];
    $toEmail = isset($orderDetails['emailId']) ? $orderDetails['emailId'] : $custEmail;
    $skuStatus = PTS_GetSKUDetails('', $pdo, 'skuRef', $skuNum);

    if ($skuStatus['trial'] == 1 || $skuStatus['trial'] == '1') {

        $subject = "Client Download Link";
        $downloadId = $orderDetails['downloadId'];
        $url = $base_url . "eula.php?id=" . $downloadId;
        $body = 'Hi ' . $toName . ', <br/><br/>Please <a href="' . $url . '">Click Here</a> to download client.<br/><br/> Regards,<br/>Nanoheal Team';

        $mailStatus = PTS_SendMail("Nanoheal", getenv('SMTP_USER_LOGIN'), $toName, $toEmail, $subject, $body);
        return $mailStatus;
    } else {

        if (trim($payementStatus) == 'Done') {
            $subject = "Client Download Link";
            $downloadId = $orderDetails['downloadId'];
            $url = $base_url . "eula.php?id=" . $downloadId;
            $body = 'Hi ' . $toName . ', <br/><br/>Please <a href="' . $url . '">Click Here</a> to download client.<br/><br/> Regards,<br/>Nanoheal Team';
        } else {
            $subject = "Pending Payment Link";
            $paymentId = $orderDetails['paymentId'];
            $url = $payment_url . "index.php?id=" . $paymentId;
            $body = 'Hi ' . $toName . ', <br/><br/>Please <a href="' . $url . '">Click Here</a> to process further.<br/><br/> Regards,<br/>Nanoheal Team';
        }
        $mailStatus = PTS_SendMail("Nanoheal", getenv('SMTP_USER_LOGIN'), $toName, $toEmail, $subject, $body);
        return $mailStatus;
    }
}


function PTS_GetEmailBody($subject, $toName, $link)
{
    $body = 'Hi ' . $toName . ', Please <a href="' . $link . '">Click Here</a> to process further.';
    return $body;
}


function PTS_SendMail($fromName, $fromEmail, $toName, $toEmail, $subject, $body)
{
    $headers = '';
    $headers .= 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    $headers .= 'From: ' . '<' . $fromEmail . '>' . "\r\n";
    $headers .= 'Reply-To: ' . ' <' . $fromEmail . '>' . "\r\n";
    //    $mail = mail($toEmail, $subject, $body, $headers);


    //    if (!$mail) {

    // send from visualisationService
    $arrayPost = array(
        'from' => getenv('SMTP_USER_LOGIN'),
        'to' => $toEmail,
        'subject' => $subject,
        'text' => '',
        'html' => $body,
        'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
    $result = CURL::sendDataCurl($url, $arrayPost);

    if (!$result) {
        return 0;
    } else {
        return 1;
    }
}

function PTS_CalculateTax($tax, $price, $currency)
{
    if ($tax != 0 || $tax != '0' || $tax != NULL) {

        $taxSub = round(($price * ($tax / 100)), 3);
        $taxExp = explode(".", $taxSub);

        if (strlen($taxExp[1]) > 2 && $currency == 'GBP') {
            $taxSub1 = (float)($taxExp[0] . '.' . substr($taxExp[1], 0, -1));
        } else if (strlen($taxExp[1]) > 2 && $currency == 'EUR') {
            $taxSub1 = (float)(round($taxSub, 2));
        } else {
            $taxSub1 = $taxSub;
        }
    } else {
        $taxSub1 = 0;
    }
    return $taxSub1;
}

function PTS_GETOrderStatus($key, $pdo, $custNumber, $orderNumber)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select BackUpStatus,contractEndDate from " . $GLOBALS['PREFIX'] . "agent.customerOrder where customerNum=? and orderNum=?;");
        $sql->execute([$custNumber, $orderNumber]);
        $res = $sql->fetchAll();
        return $res;
    } else {
        echo "records not found";
    }
}

function PTS_GETInstallStatus($key, $pdo, $custNumber, $orderNumber)
{
    $isKeyValid = DASH_ValidateKey($key);
    $empty = array();
    if ($isKeyValid) {
        $sql = $pdo->prepare("select S.uninsdormatStatus,S.downloadStatus,S.revokeStatus from " . $GLOBALS['PREFIX'] . "agent.serviceRequest S where S.customerNum = ? and S.orderNum = ? order by sid desc limit 1");
        $sql->execute([$custNumber, $orderNumber]);
        $res = $sql->fetchAll();
        if (safe_sizeof($res) > 0) {
            return $res;
        } else {
            return $empty;
        }
    } else {
        return $empty;
    }
}


function PTS_CreateCustomerHistory($key, $pdo, $customerNumber, $orderNumber, $noofpc, $historyType)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $orderDetails = PTS_GetOrderDetailsByNumber($key, $pdo, $customerNumber, $orderNumber);
        $customerFirstName = $orderDetails['coustomerFirstName'];
        $customerEmailId = $orderDetails['emailId'];
        $orderDate = $orderDetails['orderDate'];
        $contractEdate = $orderDetails['contractEndDate'];
        $createdDate = $orderDetails['createdDate'];
        $SKUNum = $orderDetails['SKUNum'];
        $agentLogEmail = $_SESSION['user']['adminEmail'];

        $paymentId = PTS_GetPaymentId($pdo);
        $currentDate = time();

        $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.`customerHistory` (`customerNum`, `orderNum`, `emailId`, `SKUNum`, `orderDate`, `contractEndDate`,
            `noOfPc`, `agentId`, `paymentId`, `payRefNum`, `prExtDate`, `prExtStatus`, `createdDate`, `transationType`,
            `transactionDate`, `historyType`, `historyDate`) VALUES (?,?,?,?,?,?,?,?,?, NULL, NULL, NULL,?,NULL, NULL,?,?)");
        $sql->execute([$customerNumber, $orderNumber, $customerEmailId, $SKUNum, $orderDate, $contractEdate, $noofpc, $agentLogEmail, $paymentId, $createdDate, $historyType, $currentDate]);
        $result = $pdo->lastInsertId();
        return $paymentId;
    } else {
        echo "API Key is expired";
    }
}


function PTS_CreateTrialProvision($pdo, $customerEmail, $customerFName, $companyName, $password, $osType, $custNum)
{
    $isKeyValid = DASH_ValidateKey($key);
    global $base_url;
    if ($isKeyValid) {
        $osType1 = "1' and osType='" . $osType;
        $skuDetls = PTS_GetSKUDetails($key, $pdo, "trial", $osType1);
        $noOfDays = $skuDetls['noOfDays'];
        $noOfPc = $skuDetls['licenseCnt'];
        $skuDesc = $skuDetls['description'];
        $skuname = $skuDetls['skuName'];
        $skuRef = $skuDetls['skuRef'];
        $renewDays = $skuDetls['renewDays'];
        $provCode = $skuDetls['provCode'];
        $customerLName = '';

        $remoteSession = "";
        $currentDate = time();
        $orderDate = time();
        $dateOfOrder = strtotime($orderDate);
        $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
        $contractEnd = strtotime(date("Y-m-d H:i:s", $orderDate) . " +$noOfDays day");

        $customerDtls = PTS_GetCompany_DetailsByColumn($key, $pdo, "companyName", $companyName);
        $cId = $customerDtls['eid'];
        $reportserver = $customerDtls['reportserver'];
        $proccess = PTS_GetProcessForCompany($key, $pdo, $cId);
        $pId = $proccess['pId'];

        if ($custNum == '') {
            $customerNumber = PTS_GetAutoCustomerNumber($pdo);
        } else {
            $customerNumber = $custNum;
        }
        $orderNumber = PTS_GetAutoCustomerNumber($pdo);
        $refCustomerNum = PTS_GetAutoCustomerNumber($pdo);
        $refOrderNum = PTS_GetAutoCustomerNumber($pdo);

        $sitename = PTS_GetSiteNameForProcess($key, $pdo, $pId, $skuRef);
        if ($sitename == '') {
            return array("status" => "FAILED", "message" => "Site Code not found");
        }

        $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
        $variation = $proccessDetail['variation'];
        $locale = $proccessDetail['locale'];
        $downloadPath = $proccessDetail['downloaderPath'];
        $sendEmail = $proccessDetail['sendMail'];
        $backUp = $proccessDetail['backupCheck'];
        $respectiveDB = $proccessDetail['DbIp'];

        PTS_SetProcessDetailSession($proccessDetail, $osType);

        $agentLogEmail = $customerEmail;
        $emailRes = validateEmailId($pdo, $customerEmail, $skuRef);

        if (safe_count($emailRes) > 0) {
            $sessionid = $emailRes['sessionid'];
            $downloadId = $emailRes['downloadId'];
            $custFirstName = $emailRes['custFirstName'];
            $custLastName = $emailRes['custLastName'];
            $password = $emailRes['custPassword'];
            $license = $emailRes['noOfPc'];
            $planName = $emailRes['SKUDesc'];
            $customerNum = $emailRes['customerNum'];
            if ($password == '') {
                return array(
                    "status" => "SUCCESS", "customerNumber" => $customerNum, "trailSessionId" => $sessionid, "licenseSessionId" => $sessionid, "downloadUrl" => $base_url . 'eula.php?id=' . $downloadId,
                    "emailId" => $customerEmail, "customerFirstName" => $custFirstName, "customerLastName" => $custLastName, "licenseCount" => $license, "password" => $password, "Plan" => $planName, "register" => 0, "directInstal" => 1
                );
            } else {
                return array(
                    "status" => "SUCCESS", "customerNumber" => $customerNum, "trailSessionId" => $sessionid, "licenseSessionId" => $sessionid, "downloadUrl" => $base_url . 'eula.php?id=' . $downloadId,
                    "emailId" => $customerEmail, "customerFirstName" => $custFirstName, "customerLastName" => $custLastName, "licenseCount" => $license, "password" => $password, "Plan" => $planName, "register" => 1, "directInstal" => 1
                );
            }
        } else {
            $custResult = PTS_CheckCustomerDetails($customerEmail, $pdo);
            if (safe_count($custResult) > 0) {
                $customerNum = $custResult['customerNum'];
                $customerFName = $custResult['custFirstName'];
                $customerLName = $custResult['custLastName'];
                $password = $custResult['custPassword'];
            } else {
                $insertSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerDetails SET customerNum = ?,custFirstName = ?," .
                    "custLastName = '',custLogin = ?");
                $insertSql->execute([$customerNumber, $customerFName, $customerEmail]);
                $sqlRes = $pdo->lastInsertId();
            }

            $fileString = PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNumber, $orderNumber, $customerEmail, $contractEDate, $provCode, $reportserver, '1', '', $renewDays);
            if ($fileString != 'FAIL' || $fileString != '') {

                $downloadId = PTS_GetDownloadId($pdo);
                $sessionid = md5(mt_rand());

                $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNumber . "', orderNum = '" . $orderNumber . "', "
                    . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFName . "', "
                    . "coustomerLastName = '$customerLName' , coustomerCountry = '', "
                    . "emailId= '" . trim($customerEmail) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
                    . "orderDate = '" . $orderDate . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '0', "
                    . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', "
                    . "noOfPc = '" . trim($noOfPc) . "', oldorderNum ='', provCode = '" . $provCode . "', "
                    . "remoteSessionURL = '" . $remoteSession . "',agentId = '',processId= '$pId', "
                    . "compId='$cId',downloadId='" . $downloadId . "',createdDate='" . time() . "',siteName='" . $sitename . "', "
                    . "advSub='0',licenseKey = '0',nhOrderKey='0',gateWayStatus='0',osType = ?");
                $sql_ser->execute([$osType]);
                $result = $pdo->lastInsertId();

                if ($result > 0) {
                    $insertTrialPasswords = PTS_InsertTrialPasswords($pdo, $customerNumber, $customerEmail, $customerName, $password);
                    if ($password == '') {
                        return array(
                            "status" => "SUCCESS", "customerNumber" => $customerNumber, "trailSessionId" => $sessionid, "licenseSessionId" => '', "downloadUrl" => $base_url . 'eula.php?id=' . $downloadId, "emailId" => $customerEmail,
                            "customerFirstName" => $customerName, "customerLastName" => '', "licenseCount" => 0, "Plan" => $skuDesc, "password" => $password, "message" => "Trial Created Successfully", "register" => 0, "directInstal" => 0
                        );
                    } else {
                        return array(
                            "status" => "SUCCESS", "customerNumber" => $customerNumber, "trailSessionId" => $sessionid, "licenseSessionId" => '', "downloadUrl" => $base_url . 'eula.php?id=' . $downloadId, "emailId" => $customerEmail,
                            "customerFirstName" => $customerName, "customerLastName" => '', "licenseCount" => 0, "Plan" => $skuDesc, "password" => $password, "message" => "Trial Created Successfully", "register" => 1, "directInstal" => 0
                        );
                    }
                } else {
                    return array("status" => "ERROR", "sessionid" => "", "message" => "Some error occurred, please try later");
                }
            } else {
                return array("status" => "ERROR", "sessionid" => "", "message" => "Some error occurred, please try later");
            }
        }
    }
}


function PTS_CreateProvisionOfSugarCRM($pdo, $customerNum, $skuNum, $customerFName, $customerLName)
{

    global $base_url;
    global $payment_url;

    $skuDetls = PTS_GetSKUDetails("", $pdo, "skuRef", $skuNum);
    $noOfDays = $skuDetls['noOfDays'];
    $noOfPc = $skuDetls['licenseCnt'];
    $skuDesc = $skuDetls['description'];
    $skuname = $skuDetls['skuName'];
    $skuRef = $skuDetls['skuRef'];
    $renewDays = $skuDetls['renewDays'];

    $remoteSession = "";
    $provCode = "01";
    $currentDate = time();
    $orderDate = time();
    $dateOfOrder = strtotime($orderDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(date("Y-m-d H:i:s", strtotime($orderDate)) . " +$noOfDays day");

    $customerDtls = PTS_GetCompany_DetailsByColumn($key, $pdo, "companyName", "PTS_Customer1");
    $cId = $customerDtls['eid'];
    $reportserver = $customerDtls['reportserver'];
    $proccess = PTS_GetProcessForCompany($key, $pdo, $cId);
    $pId = $proccess['pId'];

    $orderNumber = PTS_GetAutoCustomerNumber($pdo);
    $refCustomerNum = PTS_GetAutoCustomerNumber($pdo);
    $refOrderNum = PTS_GetAutoCustomerNumber($pdo);

    $sitename = PTS_GetSiteNameForProcess($key, $pdo, $pId, $skuRef);
    if ($sitename == '') {
        return array("status" => "FAILED", "message" => "Site Code not found");
    }

    $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
    $variation = $proccessDetail['variation'];
    $locale = $proccessDetail['locale'];
    $downloadPath = $proccessDetail['downloaderPath'];
    $sendEmail = $proccessDetail['sendMail'];
    $backUp = $proccessDetail['backupCheck'];
    $respectiveDB = $proccessDetail['DbIp'];

    $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
    PTS_SetProcessDetailSession($proccessDetail, '');

    $fileString = PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNum, $orderNumber, $customerEmail, $contractEDate, $agentLogEmail, $reportserver, '0', '', $renewDays);
    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = PTS_GetDownloadId($pdo);
        $paymentId = PTS_GetPaymentId($pdo);
        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNum . "', orderNum = '" . $orderNumber . "', "
            . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFName . "', "
            . "coustomerLastName = '" . $customerLName . "' , coustomerCountry = '', "
            . "emailId= '" . trim($customerEmail) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
            . "orderDate = '" . $orderDate . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '0', "
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', "
            . "noOfPc = '" . trim($noOfPc) . "', oldorderNum ='', provCode = '" . $provCode . "', "
            . "remoteSessionURL = '" . $remoteSession . "',agentId = '',processId= '$pId', "
            . "compId='$cId',downloadId='" . $downloadId . "',paymentId='" . $paymentId . "',createdDate='" . time() . "',siteName='" . $sitename . "', "
            . "advSub='0',licenseKey = '0',nhOrderKey='0',gateWayStatus='0'");
        $sql_ser->execute();
        $result = $pdo->lastInsertId();

        if ($result > 0) {
            $paymentUrl = $payment_url . "index.php?id=" . $paymentId;
            $downloadUrl = $base_url . "eula.php?id=" . $downloadId;
            return array("status" => "SUCCESS", "sessionid" => $sessionid, "downloadId" => $downloadId, "paymentId" => $paymentId, "downloadUrl" => $downloadUrl, "paymentUrl" => $paymentUrl, "message" => "Provision Created Successfully");
        } else {
            return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
        }
    } else {
        return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
    }
}


function PTS_CreateProvisionForSKU($key, $pdo, $customerNum, $oldOrderNum, $customerFName, $customerLName, $customerEmail, $skuNum, $count, $osType)
{

    global $base_url;
    global $payment_url;

    $osType1 = "$skuNum' and osType='" . $osType;
    $skuDetls = PTS_GetSKUDetails("", $pdo, "skuRef", $osType1);
    $noOfDays = $skuDetls['noOfDays'];
    $skuDesc = $skuDetls['description'];
    $skuname = $skuDetls['skuName'];
    $skuRef = $skuDetls['skuRef'];
    $renewDays = $skuDetls['renewDays'];
    $count = ($count == 0) ? 1 : $count;

    $remoteSession = "";
    $provCode = "01";
    $currentDate = time();
    $orderDate = time();
    $dateOfOrder = strtotime($orderDate);
    $contractEDate = Date("m/d/Y", strtotime("+" . $noOfDays . " days"));
    $contractEnd = strtotime(Date("m/d/Y", strtotime("+" . $noOfDays . " days")));

    $customerDtls = PTS_GetCompany_DetailsByColumn($key, $pdo, "companyName", "PTS_Customer1");
    $cId = $customerDtls['eid'];
    $reportserver = $customerDtls['reportserver'];
    $proccess = PTS_GetProcessForCompany($key, $pdo, $cId);
    $pId = $proccess['pId'];

    $orderNumber = PTS_GetAutoCustomerNumber($pdo);
    $refCustomerNum = PTS_GetAutoCustomerNumber($pdo);
    $refOrderNum = PTS_GetAutoCustomerNumber($pdo);

    $sitename = PTS_GetSiteNameForProcess($key, $pdo, $pId, $skuRef);
    if ($sitename == '') {
        return array("status" => "FAILED", "message" => "Site Code not found");
    }

    $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
    $variation = $proccessDetail['variation'];
    $locale = $proccessDetail['locale'];
    $downloadPath = $proccessDetail['downloaderPath'];
    $sendEmail = $proccessDetail['sendMail'];
    $backUp = $proccessDetail['backupCheck'];
    $respectiveDB = $proccessDetail['DbIp'];

    $proccessDetail = PTS_GetProcessDetails($key, $pdo, $pId);
    PTS_SetProcessDetailSession($proccessDetail, $osType);

    $fileString = PTS_Create_INI_Parameters($key, $pdo, $sitename, $currentDate, $customerNum, $orderNumber, $customerEmail, $contractEDate, $agentLogEmail, $reportserver, '0', '', $renewDays);
    if ($fileString != 'FAIL' || $fileString != '') {

        $downloadId = PTS_GetDownloadId($pdo);
        $paymentId = PTS_GetPaymentId($pdo);
        $sessionid = md5(mt_rand());

        $sql_ser = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerOrder set customerNum = '" . $customerNum . "', orderNum = '" . $orderNumber . "', "
            . "refCustomerNum = '" . $refCustomerNum . "',refOrderNum = '" . $refOrderNum . "', coustomerFirstName = '" . $customerFName . "', "
            . "coustomerLastName = '" . $customerLName . "' , coustomerCountry = '', "
            . "emailId= '" . trim($customerEmail) . "',SKUNum = '" . trim($skuRef) . "', SKUDesc = '" . $skuDesc . "', "
            . "orderDate = '" . $orderDate . "', contractEndDate = '" . $contractEnd . "', backupCapacity = '0', "
            . "sessionid = '" . $sessionid . "', sessionIni = '" . mysqli_real_escape_string($fileString) . "', validity = '" . trim($noOfDays) . "', "
            . "noOfPc = '" . trim($count) . "', oldorderNum ='" . $oldOrderNum . "', provCode = '" . $provCode . "', "
            . "remoteSessionURL = '" . $remoteSession . "',agentId = '" . $customerEmail . "',processId= '$pId', "
            . "compId='$cId',downloadId='" . $downloadId . "',paymentId='" . $paymentId . "',createdDate='" . time() . "',siteName='" . $sitename . "', "
            . "advSub='0',licenseKey = '0',nhOrderKey='0',gateWayStatus='0',osType = ?");
        $sql_ser->execute([$osType]);
        $result = $pdo->lastInsertId();

        if ($result > 0) {
            $paymentUrl = $payment_url . "index.php?id=" . $paymentId;
            $downloadUrl = $base_url . "eula.php?id=" . $downloadId;
            return array("status" => "SUCCESS", "sessionid" => $sessionid, "downloadId" => $downloadId, "paymentId" => $paymentId, "downloadUrl" => $downloadUrl, "paymentUrl" => $paymentUrl, "ordernum" => $orderNumber, "message" => "Provision Created Successfully");
        } else {
            return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
        }
    } else {
        return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
    }
}

function PTS_GetDeviceDetails($pdo, $deviceSid, $resCust, $pid)
{

    $customerNum = $resCust['customerNum'];
    $orderNum = $resCust['orderNum'];
    $contEndDate = $resCust['contractEndDate'];
    $sessionIni = $resCust['sessionIni'];
    $siteName = $resCust['siteName'];
    $compId = $resCust['compId'];
    $cust_sessionId = $resCust['sessionid'];
    $devicelist = $deviceSid[1];

    $sqlAudit = array();

    foreach ($devicelist as $value) {
        $session_id = $value['sessionId'];
        $servicetag = $value['serviceTag'];

        $sql = $pdo->prepare("select customerNum, serviceTag, machineManufacture, machineModelNum, macAddress, machineOS, MobileID  "
            . "FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE sessionid = ? and serviceTag = ? and processId = ? and "
            . "downloadStatus='EXE' and revokeStatus='I' order by sid desc LIMIT 1");
        $sql->execute([$session_id, $servicetag, $pid]);
        $res = $sql->fetch();

        $nodlist = array();
        if (safe_count($res)) {
            $array = $res;
            $sessionId = md5(mt_rand());
            $installDt = time();
            $serviceTag = $res['serviceTag'];
            $machineOS = $res['machineOS'];
            $machineManufacture = $res['machineManufacture'];
            $machineModelNum = $res['machineModelNum'];
            $macAddress = $res['macAddress'];
            $mobileId = $res['MobileID'];
            $doNlIdf = '';
            $appendString = "\nRemoteSessionURL=";
            $fcust_ini = $sessionIni . $appendString;

            $serviceTagExists = PTS_serviceTagExists($pdo, $customerNum, $orderNum, $serviceTag);
            if (safe_count($serviceTagExists) == 0) {
                $sqlAudit[] = '("' . $customerNum . '","' . $orderNum . '","' . $sessionId . '","' . $serviceTag . '","' . $installDt . '","' . $contEndDate . '","' . $fcust_ini . '","I","D","Assigned","' . $machineManufacture . '","' . $machineModelNum . '","' . $machineOS . '","' . $mobileId . '","' . $doNlIdf . '","' . $macAddress . '","' . $pid . '","' . $compId . '","' . $siteName . '")';
            }

            $serval = array('sessionId' => $cust_sessionId, 'serviceTag' => $serviceTag, 'mobileId' => $mobileId);
            array_push($nodlist, $serval);
        }
    }

    if (!empty($sqlAudit)) {
        $sqlQry = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest (customerNum,orderNum,sessionid,serviceTag,installationDate,uninstallDate,iniValues,revokeStatus,downloadStatus,orderStatus,"
            . "machineManufacture,machineModelNum,machineOS,MobileID,downloadId,macAddress,processId,compId,siteName) VALUES " . implode(',', $sqlAudit));
        $sqlQry->execute();
        $result = $pdo->lastInsertId();
        if ($result) {
            return array("status" => "SUCCESS", "sessionid" => $nodlist);
        } else {
            return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
        }
    } else if (!empty($nodlist)) {
        return array("status" => "SUCCESS", "sessionid" => $nodlist);
    } else {
        return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
    }
}

function PTS_MakeNewServiceTag($pdo, $customerDetails)
{

    $i = 0;
    $insertRes = [];
    $custNo = $customerDetails[0]['customerId'];
    $ordNo = $customerDetails[0]['newOrderId'];
    $pid = $customerDetails[0]['processId'];

    $resCust = PTS_GetNewOrderDtl($pdo, $custNo, $ordNo, $pid);

    if (safe_count($resCust) > 0) {


        $deviceDtls = PTS_GetDeviceDetails($pdo, $customerDetails, $resCust, $pid);

        return $deviceDtls;
    }
}



function PTS_GetDeviceDetailsArray($pdo, $deviceDtls, $customerDetails)
{
    $newDeviceDtls['compId'] = $customerDetails['compId'];
    $newDeviceDtls['processId'] = $customerDetails['processId'];
    $newDeviceDtls['customerNum'] = $customerDetails['customerNum'];
    $newDeviceDtls['orderNumber'] = $customerDetails['orderNum'];
    $newDeviceDtls['serviceTag'] = $deviceDtls['serviceTag'];
    $newDeviceDtls['sessionIni'] = $customerDetails['sessionIni'];
    $newDeviceDtls['siteName'] = $customerDetails['siteName'];
    $newDeviceDtls['machineManufacture'] = $deviceDtls['machineManufacture'];
    $newDeviceDtls['machineModelNum'] = $deviceDtls['machineModelNum'];
    $newDeviceDtls['macAddress'] = $deviceDtls['macAddress'];
    $newDeviceDtls['uninstallDate'] = $customerDetails['contractEndDate'];
    $newDeviceDtls['machineOS'] = $deviceDtls['machineOS'];
    $newDeviceDtls['clientVersion'] = "";
    $newDeviceDtls['downloadId'] = $customerDetails['downloadId'];
    $newDeviceDtls['sessionId'] = md5(mt_rand());
    $newDeviceDtls['installDate'] = time();
    return $newDeviceDtls;
}

function PTS_GetNewOrderDtl($pdo, $custNo, $ordNo, $pid)
{
    $res = array();
    $sql = $pdo->prepare("select C.customerNum,C.orderNum,C.contractEndDate,C.sessionIni,C.siteName,C.compId,C.sessionid from " . $GLOBALS['PREFIX'] . "agent.customerOrder C where C.customerNum=? and C.orderNum= and C.processId=? limit 1");
    $sql->execute([$custNo, $ordNo, $pid]);
    $sqlRes = $sql->fetch();
    if (safe_count($sqlRes) > 0) {
        return $sqlRes;
    } else {
        return $res;
    }
}



function PTS_InsertServiceTags($pdo, $newDeviceDtls)
{
    $installDate = trim($newDeviceDtls['installDate']);
    $cId = trim($newDeviceDtls['compId']);
    $pId = trim($newDeviceDtls['processId']);
    $customerNum = trim($newDeviceDtls['customerNum']);
    $orderNumber = trim($newDeviceDtls['orderNumber']);
    $serviceTag = trim($newDeviceDtls['serviceTag']);
    $sessionIni = trim($newDeviceDtls['sessionIni']);
    $siteName = trim($newDeviceDtls['siteName']);
    $manufacturer = trim($newDeviceDtls['machineManufacture']);
    $modelNum = trim($newDeviceDtls['machineModelNum']);
    $macAddress = trim($newDeviceDtls['macAddress']);
    $uninstallDate = trim($newDeviceDtls['uninstallDate']);
    $machineOS = trim($newDeviceDtls['machineOS']);
    $clientVersion = trim($newDeviceDtls['clientVersion']);
    $downloadId = trim($newDeviceDtls['downloadId']);
    $sessionid = trim($newDeviceDtls['sessionId']);

    $insertSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest SET customerNum = '" . $customerNum . "', orderNum = '" . $orderNumber . "', "
        . "sessionid = '" . $sessionid . "', serviceTag = '" . $serviceTag . "', installationDate= '" . $installDate . "', "
        . "uninstallDate = '" . $uninstallDate . "', iniValues = '" . $sessionIni . "', agentPhoneId= '', revokeStatus = 'I', "
        . "machineManufacture= '" . $manufacturer . "', machineModelNum = '" . $modelNum . "', pcNo = '1', machineName = '', "
        . "machineOS = '" . $machineOS . "', clientVersion = '" . $clientVersion . "', oldVersion= '', assetStatus = '0', "
        . "uninsdormatStatus = '', uninsdormatDate = '0', downloadId = '" . $downloadId . "', macAddress = '" . $macAddress . "', "
        . "processId = '" . $pId . "', compId= '" . $cId . "', siteName = '" . $siteName . "', subscriptionKey = '', licenseKey = '', "
        . "orderStatus = 'Renew'");
    $insertSql->execute();
    return $pdo->lastInsertId();
}

function PTS_CheckValidPlanForCust($pdo, $customerNum, $orderNum)
{
    $sql = $pdo->prepare("select customerNum,orderNum,coustomerFirstName,emailId,SKUNum,planId,trial,contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c, " .
        "" . $GLOBALS['PREFIX'] . "agent.skuMaster s WHERE s.skuRef = c.SKUNum and customerNum=? and orderNum=?  group by orderNum");
    $sql->execute([$customerNum, $orderNum]);
    $sqlRes = $sql->fetch();
    $planId = $sqlRes['planId'];
    $skuNum = $sqlRes['SKUNum'];

    $skuName = $pdo->prepare("select planName FROM " . $GLOBALS['PREFIX'] . "agent.skuPlanDetails WHERE planId = ?");
    $skuName->execute([$planId]);
    $skuNameRes = $skuName->fetch();

    $planSql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE "
        . "find_in_set(id ,(SELECT Distinct(upgrdSku) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuRef = ?))");
    $planSql->execute([$skuNum]);
    $planName = $planSql->fetchAll();

    if (empty($planName)) {
        $renewSql = $pdo->prepare("select skuRef, description FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE "
            . "find_in_set(id ,(SELECT Distinct(renewSku) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuRef = ?)) ");
        $renewSql->execute([$skuNum]);
        $planName = $renewSql->fetchAll();
    }

    return array("planName" => $skuNameRes['planName'], "includePlan" => $planName, "skuNum" => $sqlRes['SKUNum'], "status" => "SUCCESS");
}

function decryptPostData($data)
{

    global $iv;
    global $key;

    $formattedData = array();
    $ct = $data;

    $ivBytes = hex2bin($iv);
    $keyBytes = hex2bin($key);
    $ctBytes = base64_decode($ct);

    $decrypt = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $keyBytes, $ctBytes, MCRYPT_MODE_CBC, $ivBytes));

    $explodeData = explode('&', $decrypt);
    foreach ($explodeData as $key => $val) {
        $temp = explode('=', $val);
        $formattedData[$temp[0]] = $temp[1];
    }
    return $formattedData;
}

function payOrder($details)
{
    $pdo = pdo_connect();

    $decryptedData = decryptPostData($details);
    \Stripe\Stripe::setApiKey("sk_test_pfo7bAfdfSSkasZkBysq1gf7");
    $paymentId = $decryptedData['payId'];
    $cardNum = $decryptedData['cardNum'];
    $expMonth = $decryptedData['month'];
    $expYear = $decryptedData['year'];
    $cvc = $decryptedData['ccv'];
    $email = $decryptedData['email'];
    $name = $decryptedData['name'];
    $country = $decryptedData['country'];
    $postal = $decryptedData['areacode'];
    $price = $decryptedData['cost'];
    $custNum = $decryptedData['custId'];
    $orderNum = $decryptedData['orderId'];
    $pcCount = $decryptedData['count'];
    $amount = $decryptedData['amount'];
    $tax = $decryptedData['tax'];
    $addSku = $decryptedData['addsku'];
    $skuNum = $decryptedData['skuNum'];

    $token = get_token($cardNum, $expMonth, $expYear, $cvc);
    $custSql = $pdo->prepare("select cust_id FROM " . $GLOBALS['PREFIX'] . "agent.stripeCustomer WHERE emailId = ?");
    $custSql->execute([$email]);
    $custRes = $custSql->fetch();

    if ($custRes) {
        $custId = $custRes['cust_id'];
        $getCust = getCustomer($token, $custId);
        $cardId = $getCust['card'];
    } else {
        $createCust = createCustomer($token, $email);
        $custId = $createCust['cust_id'];
        $cardId = $getCust['card'];
        $saveCustSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.stripeCustomer(emailId,cust_id) VALUES(?,?)");
        $saveCustSql->execute([$email, $custId]);
        $saveRes = $pdo->lastInsertId();
    }
    if ($custId) {

        $product['SKUNum'] = $skuNum;

        $createOrd = createOrder($custId, $product, $country, $postal, $email, $name, $amount);
    }
    if (!is_array($createOrd)) {
        $payRef = pay($createOrd, $cardId, $custId);
    } else {
        return $returnArray = array('msg' => 'failed', 'status' => $createOrd['message']);
    }

    $currentTime = time();
    $paymentSql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET payRefNum = ?,mappCCNum = ?," .
        "transactionDate = ?,transationType = 'void',paidAmount = ? WHERE paymentId= ?");
    $paymentSql->execute([$payRef, $cardId, $currentTime, $amount, $paymentId]);
    $paymentRes = redcommand($paymentSql, $pdo);

    $updateHostorySql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerHistory SET payRefNum =?,transationType='void',transactionDate=? " .
        "WHERE paymentId = ?");
    $updateHostorySql->execute([$payRef, $currentTime, $paymentId]);
    $paymentUpdateRes = redcommand($updateHostorySql, $pdo);

    $insertSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.paymentDetails SET customerNum = ?,orderNum = ?,orderRef = ?," .
        "transactionRef=?,transactionDate=?,amount=?,status='success'");
    $insertSql->execute([$custNum, $orderNum, $createOrd, $payRef, $currentTime, $amount]);
    $insertRes = $pdo->lastInsertId();

    if (strlen($paymentRes) > 0) {
        $returnArray = array('msg' => 'success', 'payId' => $payRef, 'price' => $amount, 'email' => $email);
    } else {
        $returnArray = array('msg' => 'failed', 'payId' => '', 'price' => 0, 'email' => $email);
    }

    return $returnArray;
}


function PTS_GetSKU_BySKUType($key, $pdo, $countryCode, $companyName, $skuType)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $sql = $pdo->prepare("select S.skuRef, S.description, S.skuName FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster S, " . $GLOBALS['PREFIX'] . "agent.channel C WHERE S.chId=C.eid AND C.companyName=? AND
					S.skuType=? AND ccode=?");
        $sql->execute([$companyName, $skuType, $countryCode]);
        $res = $sql->fetchAll();

        if (safe_count($res) > 0) {
            return $res;
        } else {
            return array();
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_getDownloadUrl($custId, $orderId, $pdo)
{
    global $base_url;

    $deviceSql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE customerNum=? AND orderNum=? ");
    $deviceSql->execute([$custId, $orderId]);
    $deviceRes = $deviceSql->fetchAll();

    if (!$deviceRes) {
        $custSql = $pdo->prepare("select customerNum,orderNum,coustomerFirstName,coustomerLastName,emailId,SKUNum,trial,downloadId " .
            "FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c, " . $GLOBALS['PREFIX'] . "agent.skuMaster s WHERE s.skuRef = c.SKUNum AND customerNum=? limit 1");
        $custSql->execute([$custId]);
        $custRes = $custSql->fetch();

        if ($custRes['trial'] == 1) {
            $downloadId = $custRes['downloadId'];
            $downloadUrl = $base_url . "eula.php?id=" . $downloadId;;
        } else {
            $customerNum = $custRes['customerNum'];
            $oldOrderNum = $custRes['orderNum'];
            $customerFName = $custRes['coustomerFirstName'];
            $customerLName = $custRes['coustomerLastName'];
            $custEmailId = $custRes['emailId'];
            $skuRef = $custRes['SKUNum'];
            $count = 1;

            $return = PTS_CreateProvisionForSKU("", $pdo, $customerNum, $oldOrderNum, $customerFName, $customerLName, $custEmailId, $skuRef, $count);
            $downloadUrl = $return['downloadUrl'];
        }

        return $downloadUrl;
    }
}

function PTS_createCustomer($customerName, $customerEmail, $password, $pdo)
{
    $pwd = md5($password);

    $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerLogin SET customerName = ?,email=?,password=?");
    $sql->execute([$customerName, $customerEmail, $pwd]);
    $sqlRes = $pdo->lastInsertId();
    if ($sqlRes) {
        return $sqlRes;
    }
}

function PTS_loginCustomer($customerEmail, $password, $pdo)
{

    $sql = $pdo->prepare("select email,password FROM " . $GLOBALS['PREFIX'] . "agent.customerLogin WHERE email =?");
    $sql->execute([$customerEmail]);
    $sqlRes = $sql->fetch();
}

function PTS_resetPassword($customerEmail, $pdo)
{
}

function PTS_getCustomerNumber($email, $pdo)
{

    $sql = $pdo->prepare("select DISTINCT(customerNum) as custNum FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE emailId= ?");
    $sql->execute([$email]);
    $sqlRes = $sql->fetch();

    if ($sqlRes) {
        $returnArray = array('msg' => 'success', 'customerNum' => $sqlRes['custNum']);
    } else {
        $returnArray = array('msg' => 'failed');
    }
    return $returnArray;
}

function PTS_GetCustomerDetailsByNumber($pdo, $customerNum, $orderNum)
{

    $sql = $pdo->prepare("select customerNum, orderNum, coustomerFirstName, coustomerLastName,compId,processId,sessionid,oldorderNum, " .
        "coustomerCountry, emailId, SKUNum, sessionIni, siteName,SKUDesc, FROM_UNIXTIME(orderDate, '%m/%d/%Y') orderDate, " .
        "FROM_UNIXTIME(contractEndDate, '%m/%d/%Y') contractEndDate, createdDate, SKUNum, downloadId, noOfPc, paymentId, payRefNum,planId FROM " .
        "" . $GLOBALS['PREFIX'] . "agent.customerOrder c," . $GLOBALS['PREFIX'] . "agent.skuMaster s WHERE c.SKUNum=s.skuRef AND c.customerNum=? AND orderNum=? limit 1");
    $sql->execute([$customerNum, $orderNum]);
    $sqlRes = $sql->fetch();

    if ($sqlRes) {
        return $sqlRes;
    } else {
        return "No records found";
    }
}

function PTS_GetSitesDevicesForSku($pdo, $compId, $processId, $customerNum, $osType)
{

    $where = '';
    if ($osType == 'windows') {
        $where = " AND S.machineOS like '%windows%'";
    } else if ($osType == 'android') {
        $where = " AND S.machineOS like '%android%'";
    }

    $sql = $pdo->prepare("select * from (select C.siteName,C.id,C.customerNum,C.orderNum,C.compId,C.processId,C.orderDate,C.contractEndDate,C.SKUDesc,S.sid,S.serviceTag,S.installationDate,S.uninstallDate, S.machineManufacture, S.machineModelNum, "
        . "S.downloadStatus,S.revokeStatus,S.orderStatus,S.sessionid,S.machineOS from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.serviceRequest S "
        . "where C.customerNum=S.customerNum and C.customerNum=? and"
        . " C.compId=? and C.processId= ? and S.revokeStatus='I' AND S.downloadStatus='EXE' " . $where . " order by sid desc) as k group by serviceTag");
    $sql->execute([$customerNum, $compId, $processId]);
    $res = $sql->fetchAll();
    if (safe_count($res)) {
        return $res;
    } else {
        return "No records found";
    }
}

function PTS_GetPlanName($planId, $pdo)
{

    $sql = $pdo->prepare("select planName FROM " . $GLOBALS['PREFIX'] . "agent.skuPlanDetails WHERE planId = ? ");
    $sql->execute([$planId]);
    $res = $sql->fetch();

    if (safe_count($res)) {
        return $res;
    } else {
        return "No records found";
    }
}

function PTS_getUnusedLicenseCount($customerId, $orderId, $pdo, $osType)
{

    $count = 0;
    $where = '';
    if ($osType == 'windows') {
        $where = " AND machineOs like '%windows%'";
    } else if ($osType == 'android') {
        $where = " AND machineOs = 'Android'";
    }
    $sql = $pdo->prepare("select S.serviceTag from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.serviceRequest S "
        . "where C.customerNum=S.customerNum and C.orderNum=S.orderNum and C.customerNum=? and"
        . " C.orderNum=? and S.revokeStatus='I' " . $where . " group by S.serviceTag");
    $sql->execute([$customerId, $orderId]);
    $res = $sql->fetchAll();

    $count = safe_count($res);
    return $count;
}

function PTS_updateOrderForSKU($key, $pdo, $customerNum, $orderNum, $customerFName, $customerLName, $custEmailId, $skuRef, $count)
{

    $orderSql = $pdo->prepare("select noOfPc,downloadId,paymentId,emailId,orderDate,contractEndDate FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum = ? AND orderNum = ?");
    $orderSql->execute([$customerNum, $orderNum]);
    $custDetails = $orderSql->fetch();

    $paymentId = PTS_GetPaymentId($pdo);
    $downloadId = $custDetails['downloadId'];
    $emailId = $custDetails['emailId'];
    $orderDate = $custDetails['orderDate'];
    $contractEndDate = $custDetails['contractEndDate'];
    $pcCount = $custDetails['noOfPc'];
    $newCount = $pcCount + $count;

    $updateOrderSql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET noOfPc=?,paymentId=? WHERE customerNum =? AND orderNum=?");
    $updateOrderSql->execute([$newCount, $paymentId, $customerNum, $orderNum]);
    $updateRes = redcommand($updateOrderSql, $pdo);

    $customerHistorySql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerHistory (customerNum,orderNum,emailId,SKUNum,orderDate,contractEndDate,noOfPc,paymentId) " .
        "VALUES(?,?,?,?,?,?,?,?)");
    $customerHistorySql->execute([$customerNum, $orderNum, $emailId, $skuRef, $orderDate, $contractEndDate, $count, $paymentId]);
    $updateHistoryRes = $pdo->lastInsertId();

    if ($updateHistoryRes) {
        return array("status" => "SUCCESS", "paymentId" => $paymentId, "ordernum" => $orderNum, "message" => "Provision updated Successfully");
    } else {
        return array("status" => "FAILED", "sessionid" => "", "message" => "Some error occurred, please try later");
    }
}

function updateDetails($fname, $lname, $email, $custNum, $pdo)
{

    $sql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET coustomerFirstName=?, coustomerLastName=?,emailId=? WHERE " .
        "customerNum=?");
    $sql->execute([$fname, $lname, $email, $custNum]);
    $sqlRes = redcommand($sql, $pdo);

    if ($sqlRes) {
        return array("status" => "SUCCESS", "message" => "Details updated Successfully");
    } else {
        return array("status" => "FAILED", "message" => "Some error occurred, please try later");
    }
}

function validateEmailId($pdo, $emailId, $skuRef)
{

    $skuRef = trim($skuRef);
    $sql = $pdo->prepare("select C.sessionid,C.downloadId,C.id,D.custFirstName,D.custLastName,D.custPassword,C.noOfPc,C.customerNum,C.SKUDesc " .
        "from " . $GLOBALS['PREFIX'] . "agent.customerOrder C," . $GLOBALS['PREFIX'] . "agent.customerDetails D where C.emailId=D.custLogin AND C.emailId = ? and SKUNum = ?  order by id desc limit 1");
    $sql->execute([$emailId, $skuRef]);
    $result = $sql->fetch();

    return $result;
}

function PTS_InsertTrialPasswords($pdo, $customerNumber, $customerEmail, $customerName, $password)
{
    $createdDate = time();
    $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.`customerDetails` (`customerNum`, `custName`, `custLogin`, `custPassword`, `createdDate`) VALUES (?,?,?,?,?)");
    $sql->execute([$customerNumber, $customerName, $customerEmail, $password, $createdDate]);
    $res = $pdo->lastInsertId();
    return TRUE;
}

function PTS_GetCustomerInfoByEmail($pdo, $email, $companyName, $osType, $serviceTag, $macAddress)
{

    $testSql = $pdo->prepare("select custPassword FROM " . $GLOBALS['PREFIX'] . "agent.customerDetails WHERE custLogin = ? ");
    $testSql->execute([$email]);
    $testRes = $testSql->fetch();
    if (safe_count($testRes) > 0) {
        $custPassword = $testRes['custPassword'];
    } else {
        $custPassword = '';
    }

    $sql = $pdo->prepare("select cd.customerNum,custFirstName,custLastName,orderNum,custPassword,SKUDesc,orderDate,sessionid,noOfPc,orderNum" .
        "contractEndDate,paymentId FROM " . $GLOBALS['PREFIX'] . "agent.customerDetails cd," . $GLOBALS['PREFIX'] . "agent.customerOrder co WHERE cd.custLogin = ? " .
        "AND cd.customerNum = co.customerNum AND co.osType =? AND payRefNum IS NOT NULL order by id desc limit 1");
    $sql->execute([$email, $osType]);

    $sqlRes = $sql->fetch();

    if (safe_count($sqlRes) > 0) {
        $custNum = $sqlRes['customerNum'];
        $orderNum = $sqlRes['orderNum'];
        $custFirstName = $sqlRes['custFirstName'];
        $custLastName = $sqlRes['custLastName'];
        $orderDate = $sqlRes['orderDate'];
        $OrderDateFormatted = date("%m/%d/%Y", $sqlRes['orderDate']);
        $endDate = $sqlRes['contractEndDate'];
        $contractEndDate = date("%m/%d/%Y", $sqlRes['contractEndDate']);
        $plan = $sqlRes['SKUDesc'];
        if (empty($sqlRes['paymentId'])) {
            $licenseCount = 0;
            $trailSessionId = $sqlRes['sessionid'];
            $licenseSessionId = '';
        } else {
            $countSql = $pdo->prepare("select count(DISTINCT serviceTag) as count FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE customerNum = ? AND " .
                "orderNum = ? AND revokeStatus = 'I' AND serviceTag = ? AND macAddress = ? ");
            $countSql->execute([$custNum, $orderNum, $serviceTag, $macAddress]);
            $countRes = $countSql->fetch();

            $directInstall = $countRes['count'] == 0 ? 0 : 1;
            $licenseCount = $countRes['count'] == 0 ? $sqlRes['noOfPc'] : abs($countRes['count'] - $sqlRes['noOfPc']);

            $trailSql = $pdo->prepare("select sessionid FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum = ? AND paymentId IS NULL ");
            $trailSql->execute([$custNum]);
            $trailRes = $trailSql->fetch();

            if ($trailRes['sessionid'] == '') {
                $trailId = PTS_CreateTrialProvision($pdo, $email, '', $companyName, '', $osType, $custNum);
            } else {
                $trailId['trailSessionId'] = $trailRes['sessionid'];
            }
            $trailSessionId = $trailId['trailSessionId'];
            $licenseSessionId = $sqlRes['sessionid'];
        }

        if ($custPassword == '' || $custPassword == 0) {
            $result = array(
                "status" => "SUCCESS", "customerNumber" => $custNum, "customerFirstName" => $custFirstName, "customerLastName" => $custLastName, "emailId" => $email, "password" => $custPassword,
                "Plan" => $plan, "trailSessionId" => $trailSessionId, "licenseSessionId" => $licenseSessionId, "licenseCount" => $licenseCount, "register" => 0, "directInstal" => $directInstall
            );
        } else {
            $result = array(
                "status" => "SUCCESS", "customerNumber" => $custNum, "customerFirstName" => $custFirstName, "customerLastName" => $custLastName, "emailId" => $email, "password" => $custPassword,
                "Plan" => $plan, "trailSessionId" => $trailSessionId, "licenseSessionId" => $licenseSessionId, "licenseCount" => $licenseCount, "register" => 1, "directInstal" => $directInstall
            );
        }
    } else {

        $result = PTS_CreateTrialProvision($pdo, $email, '', $companyName, '', $osType, '');
    }
    return $result;
}

function PTS_GetMachineByEmail($pdo, $customerEmail)
{

    $machineSql = $pdo->prepare("select DISTINCT(serviceTag) as serviceTag,siteName,machineOS,sid FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest s," . $GLOBALS['PREFIX'] . "agent.customerDetails c WHERE s.customerNum = c.customerNum AND " .
        "c.custLogin = ? AND s.downloadStatus='EXE' AND s.revokeStatus = 'I' order by sid desc");
    $machineSql->execute([$customerEmail]);
    $machineRes = $machineSql->fetchAll();

    if (safe_count($machineRes) > 0) {
        $machineArray = array();
        foreach ($machineRes as $key => $val) {
            $machineArray[$key]['serviceTag'] = $val['serviceTag'];
            $machineArray[$key]['siteName'] = $val['siteName'];
            $machineArray[$key]['OS'] = $val['machineOS'];
        }
        $result = array("status" => "SUCCESS", "data" => $machineArray);
    } else {
        $result = array("status" => "FAILED", "data" => "No machines linked to this id");
    }
    return $result;
}

function PTS_RegisterUser($pdo, $fName, $lName, $email, $password)
{


    $time = time();
    global $companyName;

    $sql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerDetails set custFirstName = ? , custLastName = ? , custPassword = ?, createdDate = ? WHERE custLogin = ? ");
    $sql->execute([$fName, $lName, $password, $time, $email]);
    $sqlRes = $pdo->lastInsertId();

    $sql1 = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder set coustomerFirstName = ?,coustomerLastName = ? where emailId = ? ");
    $sql1->execute([$fName, $lName, $email]);
    $sql1Res = $pdo->lastInsertId();

    if ($sql1Res) {
        $result = array("status" => "SUCCESS", "message" => "User registration successful");
    } else {
        $result = array("status" => "FAILED", "message" => "User email doesnot exists");
    }
    return $result;
}

function PTS_resetPasword($pdo, $email, $password)
{



    $sql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerDetails set custPassword = ? WHERE custLogin =? ");
    $sql->execute([$password, $email]);
    $sqlRes = redcommand($sql, $pdo);

    if (!empty($sqlRes)) {
        $result = array("status" => "SUCCESS", "message" => "Password reset successful");
    } else {
        $result = array("status" => "FAILED", "message" => "Password couldnot be reset. Try again");
    }
    return $result;
}

function PTS_IsTrialTaken($pdo, $customerEmailId, $skuRef)
{
    $ret = array();
    $skuRef = trim($skuRef);
    $sql_Coust = $pdo->prepare("select C.sessionid,C.downloadId,C.id from " . $GLOBALS['PREFIX'] . "agent.customerOrder C where C.emailId = ? and SKUNum =?  order by id desc limit 1");
    $sql_Coust->execute([$emailId, $skuRef]);
    $rows = $sql_Coust->fetchAll();
    if ($rows > 0) {
        return $rows;
    } else {
        return $ret;
    }
}

function PTS_userRegistration($pdo, $firstName, $lastName, $emailId, $url)
{

    $time = time();
    $vid = PTS_GetRandomCode();

    $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.customerDetails SET custFirstName= ?,custLastName=?," .
        "custLogin=?,createdDate=?,vid=? ");
    $sql->execute([$firstName, $lastName, $emailId, $time, $vid]);
    $sqlres = $pdo->lastInsertId();

    $email = PTS_resetPasswordMail($firstName, $lastName, $emailId, $vid, $url);

    if ($email) {
        $result = array("status" => "SUCCESS");
    } else {
        $result = array("status" => "FAILED");
    }
    return $result;
}

function PTS_resetPasswordMail($firstName, $lastName, $emailId, $vid, $url)
{


    $subject = "Set password link";
    $url1 = $url . "?id=" . $vid;
    $body = 'Hi ' . $firstName . ', Please use <a href="' . $url1 . '">Click Here</a> to set password.';

    $mailStatus = PTS_SendMail("Nanoheal", getenv('SMTP_USER_LOGIN'), $firstName, $emailId, $subject, $body);
    return $mailStatus;
}

function PTS_setUserPassword($pdo, $password, $vid)
{

    $setSql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerDetails SET custPassword=? WHERE vid = ?");
    $setSql->execute([$password, $vid]);
    $sqlRes = redcommand($setSql, $pdo);

    if ($sqlRes) {
        $removeVidSql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerDetails SET vid = '' WHERE vid=? ");
        $removeVidSql->execute([$vid]);
        $updateRes = redcommand($removeVidSql, $pdo);

        $result = array("status" => "SUCCESS");
    } else {
        $result = array("status" => "FAILED");
    }
    return $result;
}

function PTS_custDetailsForWeb($pdo, $email)
{

    $custsql = $pdo->prepare("select custFirstName,custLastName,custLogin FROM " . $GLOBALS['PREFIX'] . "agent.customerDetails WHERE custLogin = ? ");
    $custsql->execute([$email]);
    $custRes = $custsql->fetch();

    if (safe_count($custRes) > 0) {
        $fName = $custRes['custFirstName'];
        $lName = $custRes['custLastName'];
        $email = $custRes['custLogin'];

        $result = array("status" => "SUCCESS", "customerFirstName" => $fName, "customerLastName" => $lName, "customerEmail" => $email);
    } else {
        $result = array("status" => "FAILED", "msg" => "customer doesnot exists");
    }
    return $result;
}

function PTS_CheckCustomerDetails($customerEmail, $pdo)
{

    $custDetailsSql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.customerDetails WHERE custLogin = ? ");
    $custDetailsSql->execute([$customerEmail]);
    $custRes = $custDetailsSql->fetch();

    return $custRes;
}

function PTS_getAdditionalSKUDetails($pdo)
{

    $skuSql = $pdo->prepare("select skuRef,skuPrice FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE skuType = 4 ");
    $skuSql->execute();
    $skuRes = $skuSql->fetch();
    return $skuRes;
}

function PTS_customerSalesDetails($pdo)
{


    $time = time();

    $skuSql = $pdo->prepare("select skuRef FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster s WHERE s.trial = 1");
    $skuSql->execute();
    $skuRes = $skuSql->fetchAll();

    foreach ($skuRes as $key => $val) {
        $sku .= "'" . $val['skuRef'] . "',";
    }

    $skuRef = trim($sku, ',');

    $sql = $pdo->prepare("select DISTINCT(serviceTag) as serviceTag,c.customerNum,c.orderNum,coustomerFirstName," .
        "FROM_UNIXTIME(orderDate,'%Y-%m-%d') as orderDate,c.SKUNum,s.chatroomid,FROM_UNIXTIME(contractEndDate,'%Y-%m-%d') as contractEndDate, " .
        "s.machineOS,s.machineManufacture,s.machineManufacture " .
        "FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c," . $GLOBALS['PREFIX'] . "agent.serviceRequest s WHERE s.customerNum = c.customerNum AND s.orderNum=c.orderNum AND " .
        "s.revokeStatus = 'I' AND s.downloadStatus = 'EXE' AND s.processId = '4' AND c.compId='5' AND " .
        "s.chatroomid is NOT NULL AND (c.SKUNum IN ($skuRef) ) order by c.orderDate desc ");
    $sql->execute();

    $res = $sql->fetchAll();
    if (safe_count($res) > 0) {

        foreach ($res as $value) {

            $custNum = $value['customerNum'];
            $custName = $value['coustomerFirstName'];
            $orderDate = $value['orderDate'];
            $serviceTag = $value['serviceTag'];
            $installStatus = 'Installed';
            $chatId = $value['chatroomid'];
            $os = $value['machineOS'];
            $manuf = $value['machineManufacture'];
            $orderEndDate = $value['contractEndDate'];
            $orderNum = $value['orderNum'];

            $result[] = array(
                "customerName" => $custName,
                "customerNumber" => $custNum,
                "date" => $orderDate,
                "servicetag" => $serviceTag,
                "status" => $installStatus,
                "chatroom" => $chatId,
                "orderNumber" => $orderNum,
                "enDate" => $orderEndDate,
                "machineOs" => $os,
                "manufacturer" => $manuf,
            );
        }
    } else {
        $result = [];
    }

    return $result;
}

function PTS_getCustomerByChatId($pdo, $chatId)
{

    $skuArray = array('sku_AqZtnF49kysSHu', 'sku_BY80gJa7TgU0lq');

    $sql = $pdo->prepare("select c.customerNum,c.orderNum,serviceTag,coustomerFirstName,coustomerLastName,emailId,SKUNum," .
        "FROM_UNIXTIME(orderDate,'%Y-%m-%d') as orderDate,FROM_UNIXTIME(contractEndDate,'%Y-%m-%d') as contractEndDate " .
        "FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c," . $GLOBALS['PREFIX'] . "agent.serviceRequest s WHERE s.chatroomid = ? and s.customerNum=c.customerNum order by sid desc limit 1; ");
    $sql->execute([$chatId]);
    $sqlRes = $sql->fetch();

    if (safe_count($sqlRes) > 0) {

        if (in_array($sqlRes['SKUNum'], $skuArray)) {
            $trail = 'sales';
        } else {
            $trail = 'support';
        }

        $result[] = array(
            "customerNumber" => $sqlRes['customerNum'], "orderNumber" => $sqlRes['orderNum'], "serviceTag" => $sqlRes['serviceTag'],
            "firstName" => $sqlRes['coustomerFirstName'], "lastName" => $sqlRes['coustomerLastName'], "emailId" => $sqlRes['emailId'], "orderDate" => $sqlRes['orderDate'],
            "endDate" => $sqlRes['contractEndDate'], "redirectTo" => $trail
        );
    } else {
        $result = [];
    }
    return $result;
}

function PTS_serviceTagExists($pdo, $customerNum, $orderNum, $serviceTag)
{

    $sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE customerNum = ? AND orderNum = ? " .
        "AND serviceTag = ? AND revokeStatus = 'I' AND downloadStatus = 'D'");
    $sql->execute([$customerNum, $orderNum, $serviceTag]);
    $sqlRes = $sql->fetchAll();

    return $sqlRes;
}

function PTS_verifyPassword($pdo, $emailId, $password)
{

    $sql = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.customerDetails WHERE custLogin = ? AND custPassword = ? ");
    $sql->execute([$emailId, $password]);
    $sqlRes = $sql->fetch();

    if (safe_count($sqlRes) > 0) {
        $result = array("status" => "SUCCESS", "message" => "Login successful");
    } else {
        $result = array("status" => "FAILED", "message" => "Wrong password");
    }
    return $result;
}

function PTS_getPasswordResetLink($pdo, $emailId, $url)
{

    $time = time();
    $vid = PTS_GetRandomCode();

    $sql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerDetails SET vid= ?,createdDate=? WHERE " .
        "custLogin=?");
    $sql->execute([$vid, $time, $emailId]);
    $sqlRes = redcommand($sql, $pdo);

    $email = PTS_resetPasswordMail('', '', $emailId, $vid, $url);

    if ($email) {
        $result = array("status" => "SUCCESS");
    } else {
        $result = array("status" => "FAILED");
    }
    return $result;
}

function PTS_getDetailsByEmail($pdo, $emailId)
{
    $sql = $pdo->prepare("select c.customerNum,orderNum,osType FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c," . $GLOBALS['PREFIX'] . "agent.customerDetails d WHERE " .
        "c.customerNum = d.customerNum AND d.custLogin = ? order by id desc limit 1");
    $sql->execute([$emailId]);
    $sqlRes = $sql->fetch();

    if (safe_count($sqlRes) > 0) {
        return $sqlRes;
    }
}

function PTS_getMyAccountDetails($pdo, $emailId)
{

    $custDetails = PTS_getDetailsByEmail($pdo, $emailId);

    if (safe_count($custDetails) > 0) {
        $customerId = $custDetails['customerNum'];
        $orderId = $custDetails['orderNum'];
        $os = $custDetails['osType'];
    }


    $machineDetails = PTS_getAllDevices($customerId, $orderId, $os, $pdo);
    $custDetails = PTS_GetCustomerDetailsByNumber($pdo, $customerId, $orderId);
    $licenseCount = PTS_getUnusedLicenseCount($customerId, $orderId, $pdo, $osType);
    $orderDetails = PTS_getOrderDetail($customerId, $pdo);
    $downloadUrl = PTS_getTrailId($customerId, $pdo);
    $machineOs = PTS_getMachineOsCount($customerId, $pdo);

    $result = array();

    $result['personalDetails']['customerFirstName'] = $custDetails['coustomerFirstName'];
    $result['personalDetails']['customerLastName'] = $custDetails['coustomerLastName'];
    $result['personalDetails']['customerEmail'] = $custDetails['emailId'];
    $result['personalDetails']['customerNum'] = $custDetails['customerNum'];
    $result['personalDetails']['orderNum'] = $custDetails['orderNum'];
    $result['personalDetails']['SKUDesc'] = $custDetails['SKUDesc'];
    $result['personalDetails']['contractEndDate'] = $custDetails['contractEndDate'];
    if ($custDetails['payRefNum'] == '' || is_null($custDetails['payRefNum'])) {
        $result['personalDetails']['noOfPC'] = 0;
    } else {
        $result['personalDetails']['noOfPC'] = $custDetails['noOfPc'];
    }

    $result['orderDetails'] = $orderDetails;
    $result['manageDevice'] = $machineDetails;

    return $result;
}

function PTS_getOrderDetail($customerId, $pdo)
{

    $sql = $pdo->prepare("select customerNum,orderNum,FROM_UNIXTIME(orderDate, '%m/%d/%Y') as orderDate,FROM_UNIXTIME(contractEndDate, '%m/%d/%Y') as endDate," .
        "payRefNum,noOfPc FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum=?");
    $sql->execute([$customerId]);
    $sqlRes = $sql->fetchAll();
    return $sqlRes;
}

function PTS_getTrailId($customerId, $pdo)
{
    global $base_url;

    $sql = $pdo->prepare("select downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE customerNum = ? AND paymentId IS NULL ");
    $sql->execute([$customerId]);
    $sqlRes = $sql->fetch();

    $downloadId = $base_url . 'eula.php?id=' . $sqlRes['downloadId'];

    return $downloadId;
}

function PTS_getMachineOsCount($customerId, $pdo)
{
    $androidSql = $pdo->prepare("select count(DISTINCT serviceTag) as count FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest where machineOs='Android' and customerNum=? ");
    $androidSql->execute([$customerId]);
    $androidCount = $androidSql->fetch();

    $windowsSql = $pdo->prepare("select count(DISTINCT serviceTag) as count FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest where machineOs like '%windows%' and customerNum=? ");
    $windowsSql->execute([$customerId]);
    $windowsCount = $windowsSql->fetch();

    return array("windows" => $windowsCount['count'], "android" => $androidCount['count']);
}

function PTS_getAllDevices($customerId, $orderId, $os, $pdo)
{
    try {
        $customerDetails = PTS_GetCustomerDetailsByNumber($pdo, $customerId, $orderId);
        $compId = $customerDetails['compId'];
        $processId = $customerDetails['processId'];

        $windowsDeviceDetails = PTS_GetSitesDevicesForSku($pdo, $compId, $processId, $customerNum, 'windows');
        $androidDeviceDetails = PTS_GetSitesDevicesForSku($pdo, $compId, $processId, $customerNum, 'android');
        if (safe_count($windowsDeviceDetails) > 0) {
            $i = 0;
            foreach ($windowsDeviceDetails as $device) {
                $winresult[$i]['sid'] = $device['sid'];
                $winresult[$i]['serviceTag'] = $device['serviceTag'];
                $winresult[$i]['SKUDesc'] = $device['SKUDesc'];
                $winresult[$i]['startDate'] = date("m/d/Y", $device['installationDate']);
                $winresult[$i]['endDate'] = date("m/d/Y", $device['uninstallDate']);
                $winresult[$i]['sessionid'] = $device['sessionid'];
                $winresult[$i]['os'] = $device['machineOS'];
                $winresult[$i]['orderStatus'] = $device['orderStatus'];
                $i++;
            }
        } else {
            $winresult = [];
        }

        if (safe_count($androidDeviceDetails) > 0) {
            $j = 0;
            foreach ($androidDeviceDetails as $device) {
                $androidresult[$j]['sid'] = $device['sid'];
                $androidresult[$j]['serviceTag'] = $device['serviceTag'];
                $androidresult[$j]['SKUDesc'] = $device['SKUDesc'];
                $androidresult[$j]['startDate'] = date("D-M-Y", $device['installationDate']);
                $androidresult[$j]['endDate'] = date("d-M-Y", $device['uninstallDate']);
                $androidresult[$j]['sessionid'] = $device['sessionid'];
                $androidresult[$j]['os'] = $device['machineOS'];
                $androidresult[$j]['orderStatus'] = $device['orderStatus'];
                $j++;
            }
        } else {
            $androidresult = [];
        }

        $result = array("windows" => $winresult, "android" => $androidresult);
        return $result;
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function PTS_GetCustomerNumberByEmail($pdo, $email)
{
    $sql = $pdo->prepare("select customerNum,osType FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE emailId=? GROUP BY customerNum");
    $sql->execute([$email]);
    $sqlRes = $sql->fetchAll();

    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $key => $val) {
            $os = strtolower($val['osType']);
            $result[$os] = $val['customerNum'];
        }
    }
    return $result;
}
