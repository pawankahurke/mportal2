<?PHP

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";


function ELPROV_GetCustomers($db, $loggedEid)
{
    echo $loggedEid;
    global $elastic_url;
    $url = $elastic_url . "customerorder/_search?pretty";
    $params = '{
                "query": {
                    "term": {
                        compId: ' . $loggedEid . '
                    }
                }
            }';
    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}

function ELPROV_GetCustomersDevices($customerNum, $orderNum)
{
    global $elastic_url;
    $url = $elastic_url . "servicerequest/_search?pretty";
    $query = "(customerNum:' . $customerNum . ') AND (orderNum:' . $orderNum . ')";
    $params = '{
                "query": {
                    "query_string": {
                        "query": "(customerNum:' . $customerNum . ') AND (orderNum:' . $orderNum . ') AND (revokeStatus:I)",
                        "default_operator": "AND"
                    }
                }
            }';
    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}


function ELPROV_POST_Curl($url, $pdata)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($pdata)
        )
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_exec($ch);
    $result = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);
    logElasticOperations($curl_errno, $result);
    return TRUE;
}

function ELPROV_PUT_Curl($url, $params)
{
    global $elastic_url;
    $url = $elastic_url . "servicerequest/" . $id . "?pretty";

    $headers = array();
    $headers[] = "Content-Type: application/x-www-form-urlencoded";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
}

function ELPROV_DELETE_Curl()
{
}

function logElasticOperations($errorCode, $curlReponse)
{
    logs::log($errorCode, $curlReponse);
    return TRUE;
}

function ELPROV_POST_Reseller($eid, $db)
{

    global $elastic_url;
    if ($eid != '') {
        $sql = "select * from " . $GLOBALS['PREFIX'] . "agent.channel P where P.eid = '$eid'";
        $res = find_one($sql, $db);
        if (safe_count($res) > 0) {
            $id = array("index" => array("_id" => $res['eid']));
            $data = array(
                'eid' => $res['eid'],
                'crmId' => $res['crmId'],
                'entityId' => $res['entityId'],
                'channelId' => $res['channelId'],
                'subchannelId' => $res['subchannelId'],
                'outsourcedId' => $res['outsourcedId'],
                'companyName' => utf8_encode($res['companyName']),
                'regNo' => $res['regNo'],
                'referenceNo' => $res['referenceNo'],
                'firstName' => $res['firstName'],
                'lastName' => $res['lastName'],
                'emailId' => $res['emailId'],
                'phoneNo' => $res['phoneNo'],
                'address' => utf8_encode($res['address']),
                'city' => utf8_encode($res['city']),
                'zipCode' => utf8_encode($res['zipCode']),
                'country' => utf8_encode($res['country']),
                'province' => $res['province'],
                'website' => utf8_encode($res['website']),
                'ctype' => $res['ctype'],
                'businessLevel' => $res['businessLevel'],
                'entyHirearchy' => $res['entyHirearchy'],
                'customerNo' => $res['customerNo'],
                'ordergen' => $res['ordergen'],
                'skulist' => $res['skulist'],
                'reportserver' => $res['reportserver'],
                'addcustomer' => $res['addcustomer'],
                'loginUsing' => $res['loginUsing'],
                'createdtime' => $res['createdtime'],
                'logo' => $res['logo'],
                'iconLogo' => $res['iconLogo'],
                'restricted' => $res['restricted'],
                'status' => $res['status'],
                'trialEnabled' => $res['trialEnabled'],
                'trialStartDate' => $res['trialStartDate'],
                'trialEndDate' => $res['trialEndDate'],
                'showTrialBox' => $res['showTrialBox'],
                'clientlogo' => $res['clientlogo'],
                'crmType' => $res['crmType'],
                'crmIP' => utf8_encode($res['crmIP']),
                'crmKey' => utf8_encode($res['crmKey']),
                'crmUsername' => utf8_encode($res['crmUsername']),
                'crmPassword' => utf8_encode($res['crmPassword']),
                'sitelist' => utf8_encode($res['sitelist'])
            );

            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "channel/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function ELPROV_POST_ProsMaster($pid, $db)
{

    global $elastic_url;
    if ($pid != '') {

        $sql = "select * from " . $GLOBALS['PREFIX'] . "agent.processMaster P where P.pId = '$pid'";
        $res = find_one($sql, $db);
        if (safe_count($res) > 0) {
            $id = array("index" => array("_id" => $res['pId']));
            $data = array(
                'pId' => $res['pId'],
                'swId' => $res['swId'],
                'cId' => $res['cId'],
                'processName' => utf8_encode($res['processName']),
                'DPName' => $res['DPName'],
                'showUpgradeClient' => $res['showUpgradeClient'],
                'description' => $res['description'],
                'siteCode' => $res['siteCode'],
                'metaSiteName' => $res['metaSiteName'],
                'createdDate' => $res['createdDate'],
                'DbIp' => $res['DbIp'],
                'serverId' => $res['serverId'],
                'siteCreate' => $res['siteCreate'],
                'dateCheck' => $res['dateCheck'],
                'backupCheck' => $res['backupCheck'],
                'sendMail' => $res['sendMail'],
                'serverUrl' => $res['serverUrl'],
                'phoneNo' => $res['phoneNo'],
                'website' => utf8_encode($res['website']),
                'replyEmailId' => $res['replyEmailId'],
                'chatLink' => $res['chatLink'],
                'serviceLink' => $res['serviceLink'],
                'privacyLink' => $res['privacyLink'],
                'variation' => $res['variation'],
                'skulist' => $res['skulist'],
                'locale' => utf8_encode($res['locale']),
                'videoUrl' => utf8_encode($res['videoUrl']),
                'fromName' => $res['fromName'],
                'SWLangCode' => $res['SWLangCode'],
                'crmId' => $res['crmId'],
                'profileExt' => $res['profileExt'],
                'DbPassword' => $res['DbPassword'],
                'subjectLine' => utf8_encode($res['subjectLine']),
                'welComeMail' => utf8_encode($res['welComeMail']),
                'deployPath32' => utf8_encode($res['deployPath32']),
                'deployPath64' => utf8_encode($res['deployPath64']),
                'setupName32' => utf8_encode($res['setupName32']),
                'setupName64' => utf8_encode($res['setupName64']),
                'androidsetup' => utf8_encode($res['androidsetup']),
                'macsetup' => utf8_encode($res['macsetup']),
                'linuxsetup' => utf8_encode($res['linuxsetup']),
                'downloaderPath' => utf8_encode($res['downloaderPath']),
                'logoName' => utf8_encode($res['logoName']),
                'folnDarts' => $res['folnDarts'],
                'awsBuktName' => utf8_encode($res['awsBuktName']),
                'awsKey' => utf8_encode($res['awsKey']),
                'awsSecret' => utf8_encode($res['awsSecret']),
                'downType' => utf8_encode($res['downType']),
                'downlrName' => $res['downlrName'],
                'profileName' => $res['profileName'],
                'andProfileName' => $res['andProfileName'],
                'iosProfileName' => $res['iosProfileName'],
                'lnxProfileName' => $res['lnxProfileName'],
                'macProfileName' => $res['macProfileName'],
                'FtpConfUrl' => utf8_encode($res['FtpConfUrl']),
                'WsServerUrl' => utf8_encode($res['WsServerUrl']),
                'termsConditionUrl' => utf8_encode($res['termsConditionUrl']),
                'status' => $res['status']
            );

            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "processmaster/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function ELPROV_CURL_PushData($url, $pdata)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($pdata)
        )
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_exec($ch);
    echo $result = curl_exec($ch);
    echo $curl_errno = curl_errno($ch);
    curl_close($ch);

    return TRUE;
}

function ELPROV_POST_CustomerOrder($custId, $db)
{

    global $elastic_url;
    if ($custId != '') {

        $sel_cust = "select * from " . $GLOBALS['PREFIX'] . "agent.customerOrder where id =$custId";
        $cust_res = find_one($sel_cust, $db);

        if (safe_count($cust_res) > 0) {
            $id = array("index" => array("_id" => $cust_res['id']));
            $data = array(
                'id' => $cust_res['id'],
                'customerNum' => $cust_res['customerNum'],
                'orderNum' => $cust_res['orderNum'],
                'refCustomerNum' => $cust_res['refCustomerNum'],
                'refOrderNum' => $cust_res['refOrderNum'],
                'coustomerFirstName' => $cust_res['coustomerFirstName'],
                'coustomerLastName' => $cust_res['coustomerLastName'],
                'coustomerCountry' => $cust_res['coustomerCountry'],
                'emailId' => $cust_res['emailId'],
                'SKUNum' => $cust_res['SKUNum'],
                'SKUDesc' => $cust_res['SKUDesc'],
                'orderDate' => $cust_res['orderDate'],
                'contractEndDate' => $cust_res['contractEndDate'],
                'backupCapacity' => $cust_res['backupCapacity'],
                'sessionid' => $cust_res['sessionid'],
                'validity' => $cust_res['validity'],
                'noOfPc' => $cust_res['noOfPc'],
                'entryLog' => $cust_res['entryLog'],
                'BackUpStatus' => $cust_res['BackUpStatus'],
                'lineOfBusiness' => $cust_res['lineOfBusiness'],
                'oldorderNum' => $cust_res['oldorderNum'],
                'backupOrderNum' => $cust_res['backupOrderNum'],
                'backupOrderDate' => $cust_res['backupOrderDate'],
                'thirtydayMail' => $cust_res['thirtydayMail'],
                'seventhdayMail' => $cust_res['seventhdayMail'],
                'lastdayMail' => $cust_res['lastdayMail'],
                'backupSKU' => $cust_res['backupSKU'],
                'provCode' => $cust_res['provCode'],
                'upgdorderNum' => $cust_res['upgdorderNum'],
                'remoteSessionURL' => $cust_res['remoteSessionURL'],
                'agentId' => $cust_res['agentId'],
                'siteName' => $cust_res['siteName'],
                'processId' => $cust_res['processId'],
                'compId' => $cust_res['compId'],
                'downloadId' => $cust_res['downloadId'],
                'mappCCNum' => $cust_res['mappCCNum'],
                'payRefNum' => $cust_res['payRefNum'],
                'prExtDate' => $cust_res['prExtDate'],
                'prExtStatus' => $cust_res['prExtStatus'],
                'createdDate' => $cust_res['createdDate'],
                'subscriptionKey' => $cust_res['subscriptionKey'],
                'nhOrderKey' => $cust_res['nhOrderKey'],
                'licenseKey' => $cust_res['licenseKey'],
                'advSub' => $cust_res['advSub'],
                'aviraModules' => $cust_res['aviraModules'],
                'aviraVrnUpdt' => $cust_res['aviraVrnUpdt']
            );

            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "customerorder/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function ELPROV_POST_UserData($userId, $db)
{

    global $elastic_url;
    if ($userId != '') {

        $sel_user = "select * from " . $GLOBALS['PREFIX'] . "core.Users where userid = $userId";
        $user_res = find_one($sel_user, $db);

        if (safe_count($user_res) > 0) {
            $id = array("index" => array("_id" => $user_res['userid']));
            $data = array(
                'userid' => $user_res['userid'],
                'username' => $user_res['username'],
                'password' => $user_res['password'],
                'notify_mail' => $user_res['notify_mail'],
                'report_mail' => $user_res['report_mail'],
                'priv_admin' => $user_res['priv_admin'],
                'priv_notify' => $user_res['priv_notify'],
                'priv_report' => $user_res['priv_report'],
                'priv_areport' => $user_res['priv_areport'],
                'priv_search' => $user_res['priv_search'],
                'priv_aquery' => $user_res['priv_aquery'],
                'priv_downloads' => $user_res['priv_downloads'],
                'priv_updates' => $user_res['priv_updates'],
                'priv_config' => $user_res['priv_config'],
                'priv_asset' => $user_res['priv_asset'],
                'priv_debug' => $user_res['priv_debug'],
                'priv_restrict' => $user_res['priv_restrict'],
                'priv_provis' => $user_res['priv_provis'],
                'priv_audit' => $user_res['priv_audit'],
                'priv_csrv' => $user_res['priv_csrv'],
                'filtersites' => $user_res['filtersites'],
                'logo_file' => $user_res['logo_file'],
                'logo_x' => $user_res['logo_x'],
                'logo_y' => $user_res['logo_y'],
                'footer_left' => $user_res['footer_left'],
                'footer_right' => $user_res['footer_right'],
                'revusers' => $user_res['revusers'],
                'cksum' => $user_res['cksum'],
                'asset_report_sender' => $user_res['asset_report_sender'],
                'disable_cache' => $user_res['disable_cache'],
                'event_notify_sender' => $user_res['event_notify_sender'],
                'event_report_sender' => $user_res['event_report_sender'],
                'jpeg_quality' => $user_res['jpeg_quality'],
                'meter_report_sender' => $user_res['meter_report_sender'],
                'rept_css' => $user_res['rept_css'],
                'ch_id' => $user_res['ch_id'],
                'entity_id' => $user_res['entity_id'],
                'channel_id' => $user_res['channel_id'],
                'subch_id' => $user_res['subch_id'],
                'customer_id' => $user_res['customer_id'],
                'role_id' => $user_res['role_id'],
                'user_priv' => $user_res['user_priv'],
                'passwordDate' => $user_res['passwordDate'],
                'userKey' => $user_res['userKey'],
                'userStatus' => $user_res['userStatus'],
                'loginStatus' => $user_res['loginStatus'],
                'loginDate' => $user_res['loginDate'],
                'userSession' => $user_res['userSession'],
                'passwordHistory' => $user_res['passwordHistory'],
                'privacy_email' => $user_res['privacy_email'],
                'privacy_phone' => $user_res['privacy_phone'],
                'imgPath' => $user_res['imgPath'],
                'token' => $user_res['token'],
                'tokenGenerationTime' => $user_res['tokenGenerationTime'],
                'tokenLastUsed' => $user_res['tokenLastUsed'],
                'priv_api' => $user_res['priv_api'],
                'cssconfig' => $user_res['cssconfig'],
                'timezone' => $user_res['timezone'],
                'user_email' => $user_res['user_email'],
                'user_phone_no' => $user_res['user_phone_no'],
                'firstName' => $user_res['firstName'],
                'lastName' => $user_res['lastName'],
                'clogo' => $user_res['clogo'],
                'original_role_id' => $user_res['original_role_id']
            );

            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "users/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function ELPROV_POST_OrderData($orderId, $db)
{

    global $elastic_url;
    if ($orderId != '') {

        $sel_order = "select * from " . $GLOBALS['PREFIX'] . "agent.orderDetails where id=$orderId";
        $order_res = find_one($sel_order, $db);

        if (safe_count($order_res) > 0) {
            $id = array("index" => array("_id" => $order_res['id']));
            $data = array(
                'id' => $order_res['id'],
                'chnl_id' => $order_res['chnl_id'],
                'orderNum' => $order_res['orderNum'],
                'skuNum' => $order_res['skuNum'],
                'skuDesc' => $order_res['skuDesc'],
                'licenseCnt' => $order_res['licenseCnt'],
                'installCnt' => $order_res['installCnt'],
                'purchaseDate' => $order_res['purchaseDate'],
                'orderDate' => $order_res['orderDate'],
                'contractEndDate' => $order_res['contractEndDate'],
                'noofDays' => $order_res['noofDays'],
                'payRefNum' => $order_res['payRefNum'],
                'transRefNum' => $order_res['transRefNum'],
                'trial' => $order_res['trial'],
                'nh_lic' => $order_res['nh_lic'],
                'amount' => $order_res['amount'],
                'aviraOtc' => $order_res['aviraOtc'],
                'crmOrderId' => $order_res['crmOrderId'],
                'status' => $order_res['status']
            );
            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "orderdetails/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function ELPROV_POST_ServiceReqData($sId, $db)
{

    global $elastic_url;
    if ($sId != '') {

        $sel_service = "select * from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where sid =$sId";
        $service_res = find_one($sel_service);

        if (safe_count($service_res) > 0) {
            $id = array("index" => array("_id" => $service_res['sid']));
            $data = array(
                'sid' => $service_res['sid'],
                'customerNum' => $service_res['customerNum'],
                'orderNum' => $service_res['orderNum'],
                'sessionid' => $service_res['sessionid'],
                'serviceTag' => $service_res['serviceTag'],
                'installationDate' => $service_res['installationDate'],
                'uninstallDate' => $service_res['uninstallDate'],
                'iniValues' => $service_res['iniValues'],
                'agentPhoneId' => $service_res['agentPhoneId'],
                'createdTime' => $service_res['createdTime'],
                'backupCapacity' => $service_res['backupCapacity'],
                'downloadStatus' => $service_res['downloadStatus'],
                'oldServiceTag' => $service_res['oldServiceTag'],
                'revokeStatus' => $service_res['revokeStatus'],
                'machineManufacture' => $service_res['machineManufacture'],
                'machineModelNum' => $service_res['machineModelNum'],
                'pcNo' => $service_res['pcNo'],
                'machineName' => $service_res['machineName'],
                'machineOS' => $service_res['machineOS'],
                'clientVersion' => $service_res['clientVersion'],
                'oldVersion' => $service_res['oldVersion'],
                'assetStatus' => $service_res['assetStatus'],
                'uninsdormatStatus' => $service_res['uninsdormatStatus'],
                'uninsdormatDate' => $service_res['uninsdormatDate'],
                'downloadId' => $service_res['downloadId'],
                'macAddress' => $service_res['macAddress'],
                'siteName' => $service_res['siteName'],
                'processId' => $service_res['processId'],
                'compId' => $service_res['compId'],
                'subscriptionKey' => $service_res['subscriptionKey'],
                'licenseKey' => $service_res['licenseKey'],
                'MobileID' => $service_res['MobileID'],
                'MobileType' => $service_res['MobileType'],
                'orderStatus' => $service_res['orderStatus'],
                'aviraId' => $service_res['aviraId']
            );

            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "servicerequest/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function ELPROV_POST_CustomerData($custId, $db)
{

    global $elastic_url;
    if ($custId != '') {

        $sel_cust = "select * from " . $GLOBALS['PREFIX'] . "core.Customers where id = $custId";
        $cust_res = find_one($sel_cust, $db);

        if (safe_count($cust_res) > 0) {
            $id = array("index" => array("_id" => $cust_res['id']));
            $data = array(
                'id' => $cust_res['id'],
                'username' => $cust_res['username'],
                'customer' => $cust_res['customer'],
                'sitefilter' => $cust_res['sitefilter'],
                'owner' => $cust_res['owner'],
                'notify_sender' => $cust_res['notify_sender']
            );
            $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($data)) . PHP_EOL;

            $url = $elastic_url . "customers/post/_bulk";

            pushBulkData($fdata, $url);
        }
    }
}

function pushBulkData($pdata, $url)
{


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($pdata)
        )
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_exec($ch);
    curl_close($ch);
    return TRUE;
}

function PROVEL_GetData($tableName, $cond, $source)
{

    $condition = '';

    foreach ($cond as $key => $value) {
        $condition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
    }
    $where = rtrim($condition, ',');
    PROVEL_GetDetails($where, $url, $source);
}

function PROVEL_GetDetails($con, $tableName, $source)
{

    global $elastic_url;

    $url = $elastic_url . $tableName . "/_search?pretty";

    $params = '{
            "_source": [ ' . $source . '],
            "query": {
            "bool": {
              "must" : [ 
                   "' . $con . '"
              ]
            }
           } 
          }
    }';

    $result = PROVEL_GetDataFromElastic($url, $params);
    $res = FORMAT_ElData($result);
    return $res;
}

function PROVEL_GetDataFromElastic($url, $params)
{

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $res = curl_exec($curl);
    curl_close($curl);
    return $res;
}

function FORMAT_ElData($result)
{

    $curlArray = safe_json_decode($curlResponse, TRUE);
    if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 1) {
        $loopsArray = $curlArray['hits']['hits'];
        foreach ($loopsArray as $key => $value) {
            $data[$key] = $value['_source'];
        }
    } else if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
        $data = $curlArray['hits']['hits'][0]['_source'];
    }
    return $data;
}

function ELPROV_getOrderDetails()
{
}


function getElasticDataProMono($indexName)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_search?pretty&size=10000";

    $params = '{
                "_source": []
            }';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $res = curl_exec($curl);
    $elData = safe_json_decode($res, TRUE);
    $totalEL_size = $elData['hits']['total'];
    $elDataStore = array();
    for ($i = 0; $i < $totalEL_size; $i++) {
        $array = array(
            $elData['hits']['hits'][$i]['_source']['prodname'],
            $elData['hits']['hits'][$i]['_source']['global'] == 1 ? 'Yes' : 'No',
            $elData['hits']['hits'][$i]['_source']['defaultenable'] == 1 ? 'Yes' : 'No',
            $elData['hits']['hits'][$i]['_source']['defaultmonitor'] == 1 ? 'Yes' : 'No',
            gmdate('m/d/Y H:i', $elData['hits']['hits'][$i]['_source']['created']),
            gmdate('m/d/Y H:i', $elData['hits']['hits'][$i]['_source']['modified']),
            $elData['hits']['hits'][$i]['_source']['productid']
        );
        array_push($elDataStore, $array);
    }
    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }
    curl_close($curl);
    return $elDataStore;
}


function provisionMetering_UpdateElastic($EL_proMeter_UpdtData, $editid, $indexname)
{

    global $elastic_url;

    $url = $elastic_url . $indexname . "/post/" . $editid . "/_update?pretty";

    foreach ($EL_proMeter_UpdtData as $key => $value) {
        $updateVal .= '"' . $key . '": "' . $value . '",';
    }
    $update = rtrim($updateVal, ',');

    $params = '{
        "doc" : { ' . $update . ' }
    }';

    pushBulkData($params, $url);
}


function EL_DeleteIndexRowData($indexName, $id)
{
    if ($id !== "") {
        global $elastic_url;
        $url = $elastic_url . $indexName . "/post/" . $id . "?pretty";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return TRUE;
    }
}


function getElasticDataProMonoMeter($indexName)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_search?pretty&size=10000";

    $params = '{
                "_source": []
            }';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $res = curl_exec($curl);
    $elData = safe_json_decode($res, TRUE);
    $totalEL_size = $elData['hits']['total'];
    $elDataStore = array();
    for ($i = 0; $i < $totalEL_size; $i++) {
        $array = array(
            $elData['hits']['hits'][$i]['_source']['name'],
            $elData['hits']['hits'][$i]['_source']['type'],
            $elData['hits']['hits'][$i]['_source']['username'],
            gmdate('m/d/Y H:i', $elData['hits']['hits'][$i]['_source']['created']),
            gmdate('m/d/Y H:i', $elData['hits']['hits'][$i]['_source']['expires']),
            $elData['hits']['hits'][$i]['_source']['id'],
            $elData['hits']['hits'][$i]['_source']['reporttype'],
            $elData['hits']['hits'][$i]['_source']['usetime'],
            $elData['hits']['hits'][$i]['_source']['totalby']
        );
        array_push($elDataStore, $array);
    }
    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }
    curl_close($curl);
    return $elDataStore;
}


function EL_FetchRowRecordBasedIds($indexName, $id)
{
    if ($id !== '') {
        global $elastic_url;
        $url = $elastic_url . $indexName . "/post/" . $id . "/_source?pretty";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}



function ELPROV_TermSearch($indexName, $columnName, $columnValue)
{
    try {
        global $elastic_url;
        $url = $elastic_url . $indexName . "/_search";
        $postData = '{"query":{"term":{"' . $columnName . '":"' . $columnValue . '"}}}';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            )
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $response = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);

        if ($curl_errno) {
            return false;
        }

        return $response;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }

    return false;
}

function getElasticData($indexName)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_search?pretty&size=10000";

    $params = '{
                "_source": []
            }';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $res = curl_exec($curl);

    if (curl_errno($curl)) {
        echo 'Error:' . curl_error($curl);
    }
    curl_close($curl);
    return $res;
}
