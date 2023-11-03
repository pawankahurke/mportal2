<?php




function PTS_SB_GetProvisionSKU($key, $pdo)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $email = 'admin@nanoheal.com';
        $passw = 'nanoheal@123';
        $res = validateServiceBotAccess($email, $passw);

        if ($res) {

            $channelID = $_SESSION['user']['cId'];

            $channelSql = $pdo->prepare("select emailId, skuList from " . $GLOBALS['PREFIX'] . "agent.channel where eid= ?");
            $channelSql->execute([$channelID]);
            $channelRes =  $channelSql->find();

            $skuListToShow = $channelRes['skuList'];

            $skuRes = getServiceBotData($skuListToShow);
            if (safe_count($skuRes)) {
                return $skuRes;
            } else {
                return array();
            }
        } else {
            echo 'Invalid Access Token';
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_SB_GetOrderProvisionSKU($key, $pdo)
{
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {
        $email = 'admin@nanoheal.com';
        $passw = 'nanoheal@123';
        $res = validateServiceBotAccess($email, $passw);

        if ($res) {

            $channelID = $_SESSION['user']['cId'];

            $channelSql = $pdo->prepare("select emailId, skuList from " . $GLOBALS['PREFIX'] . "agent.channel where eid= ?");
            $channelSql->execute([$channelID]);
            $channelRes =  $channelSql->find();

            $skuListToShow = $channelRes['skuList'];

            $skuRes = getServiceBotOrderData($skuListToShow);
            if (safe_count($skuRes)) {
                return $skuRes;
            } else {
                return array();
            }
        } else {
            echo 'Invalid Access Token';
        }
    } else {
        echo "API Key is expired";
    }
}

function PTS_SB_CreateNewProvision($key, $pdo, $SKU, $customerNumber, $orderNumber, $customerFirstName, $customerLastName, $customerEmailId, $customerPhone, $orderDate, $cId)
{
    global $NH_API_URL;
    global $base_url;
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {

        $isCustomerExist = PTS_IsNumberExist($key, $pdo, "customerNum", $customerNumber);
        if (safe_count($isCustomerExist) > 0) {
            return array("status" => "FAILED", "message" => "Support/Contract No. already exist, please enter another Support/Contract No.");
        }

        $isOrderExist = PTS_IsNumberExist($key, $pdo, "orderNum", $orderNumber);
        if (safe_count($isOrderExist) > 0) {
            return array("status" => "FAILED", "message" => "Work order No already exist, please enter another Work order No");
        }

        $email = 'admin@nanoheal.com';
        $passw = 'nanoheal@123';
        $res = validateServiceBotAccess($email, $passw);

        $url = $NH_API_URL . 'order_key/create';
        $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $_SESSION['cb_access_token']);

        $skuid = explode('####', $SKU);

        $data['data']['sku'] = $skuid[0];
        $data['data']['customer_number'] = $customerNumber;
        $data['data']['order_number'] = $orderNumber;
        $data['data']['first_name'] = $customerFirstName;
        $data['data']['last_name'] = $customerLastName;
        $data['data']['email'] = $customerEmailId;
        $data['data']['phone_number'] = $customerPhone;
        $data['data']['date_of_order'] = strtotime($orderDate);
        $data['data']['eid'] = $cId;

        $orderData = commonServiceBotCurlCall($url, $headers, $data, $method = true);

        if ($orderData['status'] == 'success') {
            $orderUrl = $orderData['result']['url'];
            $orderKey = $orderData['result']['key'];

            $dwnldSql = $pdo->prepare("select downloadId from " . $GLOBALS['PREFIX'] . "agent.customerOrder where subscriptionKey=? order by id desc limit 1");
            $dwnldSql->execute([$orderKey]);
            $dwnldRes =  $dwnldSql->find();

            $orderLink = $base_url . "eula.php?id=" . $dwnldRes['downloadId'];
            $orderArray = array("status" => "SUCCESS", "message" => "Order key generated successfully", "link" => $orderLink);

            $subject = "Order Link Generation";
            $body = "Dear $customerFirstName, " . PHP_EOL . PHP_EOL .
                "Your Order has been created successfully." . PHP_EOL . PHP_EOL .
                "Your Download Link : " . $orderLink . PHP_EOL . PHP_EOL .
                "Regards, " . PHP_EOL . " Nanoheal Team";
            CURR_sendMailFuncCommon($subject, $body, $customerEmailId);
        } else {
            $orderArray = array("status" => "FAILED", "message" => "Generating Order key failed");
        }
        return $orderArray;
    } else {
        echo "API Key is expired";
    }
}

function validateServiceBotAccess($emailid, $password)
{
    global $NH_API_URL;
    $data['email'] = $emailid;
    $data['password'] = $password;
    $url = $NH_API_URL . 'login';
    $headers = array('Content-Type: application/json');

    $accessData = commonServiceBotCurlCall($url, $headers, $data, $method = true);

    if ($accessData['status'] == 'success') {
        $_SESSION['cb_access_token'] = $accessData['result']['access_token'];
        $_SESSION['cb_token_type'] = $accessData['result']['token_type'];
        $_SESSION['cb_expires_in'] = $accessData['result']['expires_in'];
        $retval = true;
    } else {
        $retval = false;
    }
    return $retval;
}

function getServiceBotOrderData($skuListToShow)
{
    global $NH_API_URL;
    $url = $NH_API_URL . 'service_template/options';

    $email = 'admin@nanoheal.com';
    $passw = 'nanoheal@123';
    $res = validateServiceBotAccess($email, $passw);

    $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $_SESSION['cb_access_token']);

    $jsondata = '{"sku_list":[' . $skuListToShow . ']}';

    $skuData = commonServiceBotCurlCallManual($url, $headers, $jsondata, $method = true);

    if ($skuData['status'] == 'success') {
        foreach ($skuData['result'] as $key => $value) {

            $skuType = $value['type'];
            $subType = $value['subscription-type'];
            $finalRelSku = $value['related-sku'];
            if ($subType != 'Server') {
                $skuArray[$key]['skuRef'] = $value['id'] . '####' . $skuType . '####' . $subType . '####' . $finalRelSku;
                $skuArray[$key]['name'] = $value['name'];
                $skuArray[$key]['description'] = $value['name'];
            }
        }
    } else {
        $skuArray = '';
    }
    return $skuArray;
}

function getServiceBotData($skuListToShow)
{
    global $NH_API_URL;
    $url = $NH_API_URL . 'service_template/options';

    $email = 'admin@nanoheal.com';
    $passw = 'nanoheal@123';
    $res = validateServiceBotAccess($email, $passw);

    $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $_SESSION['cb_access_token']);
    $jsondata = '{"sku_list":[' . $skuListToShow . ']}';

    $skuData = commonServiceBotCurlCallManual($url, $headers, $jsondata, $method = true);
    $eid = $_SESSION['user']['cd_eid'];
    $tenantDtl = getTenantDetl($eid);
    $tenantStatus = $tenantDtl['serverActivation'];
    $i = 0;
    if ($skuData['status'] == 'success') {
        foreach ($skuData['result'] as $key => $value) {

            $skuType = $value['type'];
            $subType = trim($value['subscription-type']);
            $finalRelSku = $value['related-sku'];

            if ($tenantStatus == '1' && $subType != 'Server') {

                $skuArray[$i]['skuRef'] = $value['id'] . '####' . $skuType . '####' . $subType . '####' . $finalRelSku;
                $skuArray[$i]['name'] = $value['name'];
                $skuArray[$i]['description'] = $value['name'];
                $i++;
            }
            if ($tenantStatus == '0' && $subType == 'Server') {
                $skuArray[$i]['skuRef'] = $value['id'] . '####' . $skuType . '####' . $subType . '####' . $finalRelSku;
                $skuArray[$i]['name'] = $value['name'];
                $skuArray[$i]['description'] = $value['name'];
                $i++;
            }
        }
    } else {
        $skuArray = '';
    }
    return $skuArray;
}

function PTS_SB_getCustomerDetailsFunc($key)
{
    global $NH_API_URL;
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {

        $email = 'admin@nanoheal.com';
        $passw = 'nanoheal@123';
        $res = validateServiceBotAccess($email, $passw);

        $channelID = $cId = $_SESSION['user']['cId'];

        $url = $NH_API_URL . 'tenant/customers/' . $channelID;
        $headers = array('Authorization: Bearer ' . $_SESSION['cb_access_token']);
        $data = '';
        $custData = commonServiceBotCurlCall($url, $headers, $data, $method = false);

        if ($custData['status'] == 'success') {
            foreach ($custData['result'] as $key => $value) {
                $companyName[$key]['compid'] = $value['eid'];
                $companyName[$key]['companyName'] = $value['companyName'];
            }
        } else {
            $companyName = '';
        }
        return $companyName;
    } else {
        echo "API Key is expired";
    }
}

function PTS_SB_submitCommercialDataFunc($key, $skuid, $custid, $subType)
{
    global $NH_API_URL;
    global $base_url;
    $isKeyValid = DASH_ValidateKey($key);
    if ($isKeyValid) {

        $email = 'admin@nanoheal.com';
        $passw = 'nanoheal@123';
        $res = validateServiceBotAccess($email, $passw);

        $url = $NH_API_URL . 'generate/order_key';
        $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $_SESSION['cb_access_token']);
        $data['data']['sku'] = $skuid;
        $data['data']['eid'] = $custid;
        $data['data']['download_url'] = $base_url . "eula.php";
        $commercialData = commonServiceBotCurlCall($url, $headers, $data, $method = true);

        if ($commercialData['status'] == 'success') {

            $commercialDetail['site_act_key'] = $commercialData['result'];
            $_SESSION['user']['licenseKey'] = $commercialData['result'];
            if ($subType === 'Server') {
                $subject = "Tenant Activation key";
                $body = "Dear User," . PHP_EOL . PHP_EOL .
                    "Your Tenant Activation key is : " . $commercialData['result'] . PHP_EOL . PHP_EOL .
                    "Regards, " . PHP_EOL . " Nanoheal";
            } else if ($subType === 'Device') {
                $subject = "Site Activation key";
                $body = "Dear User, " . PHP_EOL . PHP_EOL .
                    "Your Site Activation key is : " . $commercialData['result'] . PHP_EOL . PHP_EOL .
                    "Regards, " . PHP_EOL . " Nanoheal";
            }
            CURR_sendMailFuncCommon($subject, $body, '');
        } else {
            $commercialDetail['site_act_key'] = '';
        }
        return $commercialDetail;
    } else {
        echo "API Key is expired";
    }
}

function PTS_SB_attachSiteKey($key, $siteName, $sitekey)
{
    global $NH_API_URL;
    $isKeyValid = DASH_ValidateKey($key);

    $pdo = db_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $pdo);

    if ($isKeyValid) {
        $email = 'admin@nanoheal.com';
        $passw = 'nanoheal@123';
        $res = validateServiceBotAccess($email, $passw);

        $url = $NH_API_URL . 'assign_site/to_servicebot/by/licensey_key';
        $headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $_SESSION['cb_access_token']);
        $data['site_name'] = $siteName;
        $data['license_key'] = $sitekey;


        $siteKeyAssignRes = commonServiceBotCurlCall($url, $headers, $data, $method = true);

        if ($siteKeyAssignRes['status'] == 'success') {
            if ($siteKeyAssignRes['result']) {
                $_SESSION['user']['licenseKey'] = $sitekey;
                if (isset($siteKeyAssignRes['result']['url'])) {
                    $siteAssignRes['status'] = 'success';
                    $siteAssignRes['msg'] = 'DURL';
                    $siteAssignRes['url'] = $siteKeyAssignRes['result']['url'];
                    $siteAssignRes['key'] = $siteKeyAssignRes['result']['key'];
                    $siteAssignRes['sid'] = $siteKeyAssignRes['result']['session_id'];

                    $keySql = $pdo->prepare("select chnl_id from " . $GLOBALS['PREFIX'] . "agent.orderDetails where licenseKey = ? order by id desc limit 1");
                    $keySql->execute([$sitekey]);
                    $keyRes =  $keySql->find();
                    $url = $NH_API_URL . 'download_id/by/site_name/comp_id';
                    $data['comp_id'] = $keyRes['chnl_id'];
                    $data['site_name'] = $siteName;
                    $getDownloadId = commonServiceBotCurlCall($url, $headers, $data, $method = true);

                    if ($getDownloadId['status'] == 'success') {
                        $siteAssignRes['downloadID'] = $getDownloadId['result'];
                    } else {
                        $siteAssignRes['downloadID'] = '';
                    }
                } else {
                    $siteAssignRes['status'] = 'success';
                    $siteAssignRes['msg'] = 'Successfully attached key with the site';
                }
            } else {
                $siteAssignRes['status'] = 'error';
                $siteAssignRes['msg'] = 'Failed to attach the key with site';
            }
        } else {
            $siteAssignRes['status'] = 'error';
            $siteAssignRes['msg'] = 'Some error occured! Please try again.';
        }
        return $siteAssignRes;
    } else {
        echo "API Key is expired";
    }
}



function PTS_SB_getAllOrders($key, $ch_id)
{
    global $NH_API_URL;

    $email = 'admin@nanoheal.com';
    $passw = 'nanoheal@123';
    $res = validateServiceBotAccess($email, $passw);

    $url = $NH_API_URL . 'order_details/by/channel_id/' . $ch_id;
    $headers = array('Authorization: Bearer ' . $_SESSION['cb_access_token']);
    $orderData = commonServiceBotCurlCall($url, $headers, $data, $method = false);

    if ($orderData['status'] == 'success') {
        $orderResult = $orderData['result'];
    } else {
        $orderResult = array();
    }
    return $orderResult;
}

function commonServiceBotCurlCall($url, $headers, $data, $reqType)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $reqType);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($reqType) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);

    return safe_json_decode($result, true);
}

function commonServiceBotCurlCallManual($url, $headers, $jsondata, $reqType)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, $reqType);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($reqType) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
    }
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);

    return safe_json_decode($result, true);
}

function activateTenant($eid, $licKey, $pdo)
{

    $sql = $pdo->prepare("select D.id,D.chnl_id,D.orderNum from " . $GLOBALS['PREFIX'] . "agent.orderDetails D where D.chnl_id = ? and D.licenseKey = ?");
    $sql->execute([$eid, $licKey]);
    $res =  $sql->find();
    if (safe_count($res)) {

        $sql_chnl = $pdo->prepare("select eid,serverActivation from " . $GLOBALS['PREFIX'] . "agent.channel D where D.eid = ?");
        $sql_chnl->execute([$eid]);
        $res_chnl =  $sql->find();
        if (safe_count($res_chnl) > 0) {

            if ($res_chnl['serverActivation'] == '0') {
                $update_sql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.channel set serverActivation='1' where eid = ?");
                $update_sql->execute([$eid]);
                $result_update = $pdo->lastInsertId();
                if ($result_update) {
                    return 'Tenant activation has been completed';
                }
            } else {
                return 'Tenant activation alredy completed';
            }
        }
    } else {
        return 'Invalid key';
    }
}

function getTenantDetl($eid)
{
    $pdo = db_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $pdo);
    $sql = $pdo->prepare("select eid,companyName,firstName,lastName,serverActivation from " . $GLOBALS['PREFIX'] . "agent.channel C where C.eid=?");
    $sql->execute([$eid]);
    $res =  $sql->find();
    if (safe_count($res)) {
        return $res;
    }
}

function CURR_sendMailFuncCommon($subject, $bodyData, $provEmailId)
{

    $fp = fopen('maillog.log', 'a');
    fwrite($fp, 'Inside Send Mail Function ' . PHP_EOL);
    $to = $_SESSION["user"]["adminEmail"];
    $from = getenv('SMTP_USER_LOGIN');
    $first_name = $_SESSION["user"]["fname"];
    $message = $bodyData;

    $headers = "From:" . $from;

    if ($provEmailId == '') {
        $toList = $to;
    } else {
        $toList = $provEmailId . ', ' . $to;
    }
//    $res = mail($toList, $subject, $message, $headers);

    // send from visualisationService
    $arrayPost = array(
      'from' => $from,
      'to' => $toList,
      'subject' => $subject,
      'text' =>'',
      'html' => $message,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
    $res = CURL::sendDataCurl($url, $arrayPost);


  if ($res) {
        fwrite($fp, 'Send Mail Function Success' . PHP_EOL);
    } else {
        fwrite($fp, 'Send Mail Function Failed' . PHP_EOL);
    }
    fclose($fp);
}
