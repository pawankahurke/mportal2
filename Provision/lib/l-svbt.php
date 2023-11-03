<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  05-Mar-19   JHN     File Created. Contains Service Bot related functions.
  05-Mar-19   JHN     Subscription creation in service bot funtion added.
 * 
 */

include_once 'l-config.php';

function SVBT_validateServiceBotAccess()
{

    global $sb_api_url;

    $username = 'admin@nanoheal.com';
    $password = 'nanoheal@123$';

    try {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $sb_api_url . "auth/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"email\" : \"$username\",\"password\" : \"$password\"}",
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"]
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $res = "cURL Error #:" . $err;
        } else {
            $res = $response;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        $res = $exc->getTraceAsString();
    }
    return $res;
}

function SVBT_checkMaxInstallCount($regCode, $siteName, $db)
{

    $siteData = getSiteInformation($regCode, $siteName, $db);
    $doInstall = true;

    if ($siteData) {
        $siteid = $siteData['siteid'];
        $installuserid = $siteData['installuserid'];

        $maxSql = "select * from Siteemail where siteid = $siteid and installuserid = $installuserid";
        logs::log("SVBT_checkMaxInstallCount: " . "MaxSql : " . $maxSql . PHP_EOL);
        $maxRes = find_one($maxSql, $db);

        $maxCount = $maxRes['maxinstall'];
        $installedCount = $maxRes['numinstalls'];

        $subscrpid = $maxRes['subscriptionid'];
        $userid = $maxRes['userid'];

        if ($maxCount != 0) {
            if ($installedCount < $maxCount) {
                $doInstall = true;
                logs::log("SVBT_checkMaxInstallCount: " .  "doInstall : TRUE" . PHP_EOL);
            } else {
                // Rollback Installation count exceeds
                $doInstall = false;
                logs::log("SVBT_checkMaxInstallCount: " .   "doInstall : FALSE" . PHP_EOL);
            }
        }

        $doSubscrp = false;
        logs::log("SVBT_checkMaxInstallCount: " . "doSubscrp Value : $subscrpid --- $userid" . PHP_EOL);
        if ($subscrpid == 0 && $userid == 0) {
            logs::log("SVBT_checkMaxInstallCount: " .   "doSubscrp : TRUE");
            $doSubscrp = true;
        }
    }
    $checkPost = ["doInstall" => $doInstall, "doSubscrp" => $doSubscrp];

    return $checkPost;
}


// If $cSiteName is empty, then the server is hosted by NH. Else it is self hosted
function SVBT_checkMaxInstallCountNew(
    $siteID,
    $siteEmailId,
    $cSiteName,
    $machineOS,
    $serviceTag,
    $macAddress,
    $db
) {
    $maxSql = "select name, quantity from skuOfferings so where so.sid IN (select skuids from " . $GLOBALS['PREFIX'] . "install.Sites where sitename='$cSiteName') limit 1;";
    $maxRes = find_one($maxSql, $db);
    $maxInstall = $maxRes['quantity'];
    //UNLIMITED Licence. So return OK.
    if ($maxInstall == 0)
        return 'OK';

    $numinstSql = "select   numinstalls from Siteemail where siteemailid = '$siteEmailId'";
    $result = find_one($numinstSql, $db);
    $numInstall = intval($result['numinstalls']);
    //Licence Exceeded. So return EXCEEDED. DO NOT ALLOW ACTIVATION.
    if ($numInstall >= $maxInstall) {
        return 'EXCEEDED';
    } else {
        return 'OK';
    }
}
/* Checks the RPC Data and inserts/updates agent.serviceRequest table */
function SVBT_checkFirstInstall(
    $siteID,
    $cid,
    $siteEmailId,
    $cSiteName,
    $machineOS,
    $serviceTag,
    $macAddress,
    $db = null
) {
    $curTime = time();
    // IF SERVICETAG "AND" ALL MAC ADDRESSES MATCH THEN OLD LICENCE UPDATED
    // IF THEY DONT MATCH THEN THE DEVICE IS ADDED AS NEW LICENCE
    // SO IF ANY HARDWARE CHANGE (CHANGES MACADDRESS) OR HOSTNAME(SERVICETAG) IS CHANGED, THEN IT WILL BE A NEW INSTALLATION
    $macAddressQuer = SVBT_filterMacAdd($macAddress);
    $sql = "SELECT  revokeStatus  from " . $GLOBALS['PREFIX'] . "agent.serviceRequest 
        where siteRegCode = ?
        AND  machineOS = ?
        AND (serviceTag = ? " . $macAddressQuer . ") 
        ORDER BY sid DESC 
        LIMIT 0,1";
    $result = NanoDB::find_one($sql, null, [$siteID, $machineOS, $serviceTag]);

    if (empty($result)) {
        $servReqInsSql = "INSERT INTO " . $GLOBALS['PREFIX'] . "agent.serviceRequest
         (sessionid, createdTime, siteRegCode, installationDate,  serviceTag, machineOS, macAddress, downloadStatus, revokeStatus,customerNum)
         VALUES ('" . md5($curTime) . "', 
         '$curTime', 
         '$siteID', 
          '$curTime', '$serviceTag', '$machineOS', '$macAddress', 'EXE', 'I','$cid')";
        // command($servReqInsSql, $db);
        NanoDB::query($servReqInsSql);

        $numinstSql = "select installuserid from " . $GLOBALS['PREFIX'] . "install.Siteemail where siteemailid = ?";
        $result = NanoDB::find_one($numinstSql, null, [$siteEmailId]);
        $installuserid = intval($result['installuserid']);
        $sql = "update " . $GLOBALS['PREFIX'] . "install.Siteemail set installed = '$curTime', " .
            "numinstalls = numinstalls + 1 where " .
            "installuserid = '$installuserid' and " .
            "siteemailid = '$siteEmailId'";
        // command($sql, $db);
        NanoDB::query($sql);

        return 'FIRST';
    }
    if ($result['revokeStatus'] === 'I') {
        echo  $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.serviceRequest set revokeStatus = 'R'
         where siteRegCode = '$siteID' AND  serviceTag = '$serviceTag' AND machineOS = '$machineOS' AND macAddress = '$macAddress'";

        // command($updateSql, $db);
        NanoDB::query($updateSql);
    }
    return 'SUBSEQUENT';
}
// Get only the validated MACAddress from the comma seperated list and create the query.
function SVBT_filterMacAdd($macAddress)
{
    $r = '';
    $a = explode(',', $macAddress);
    foreach ($a as $b) {
        if (filter_var($b, FILTER_VALIDATE_MAC)) {
            $r .=  " AND  macAddress LIKE '%$b%' ";
        }
    }
    return $r;
}
function SVBT_createUser($sb_token, $emailid)
{

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://servicebot.nanoheal.com/api/v1/users/invite",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{"email" : "' . $emailid . '"}',
        CURLOPT_HTTPHEADER => ["Authorization: JWT " . $sb_token, "Content-Type: application/json"]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $response = "cURL Error #:" . $err;
    }
    return $response;
}

function SVBT_registerUser($sb_token, $userRegisterUrl, $emailid, $password)
{

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $userRegisterUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{"email":"' . $emailid . '", "password":"' . $password . '"}',
        CURLOPT_HTTPHEADER => [
            "Authorization: JWT " . $sb_token,
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $response = "cURL Error #:" . $err;
    }
    return $response;
}

function SVBT_generateStripeToken()
{
    global $stripe_api_url;
    $stripe_secret_key = 'sk_test_pfo7bAfdfSSkasZkBysq1gf7';

    try {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $stripe_api_url . "tokens",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "card%5Bnumber%5D=4242424242424242&card%5Bexp_month%5D=09&card%5Bexp_year%5D=2022&card%5Bcvc%5D=356&card%5Bname%5D=Hello%20Test",
            CURLOPT_HTTPHEADER => ["Authorization: Bearer " . $stripe_secret_key, "Content-Type: application/x-www-form-urlencoded"]
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $res = "cURL Error #:" . $err;
        } else {
            $res = $response;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        $res = $exc->getTraceAsString();
    }
    return $res;
}

function SVBT_getServiceInstanceInformation($sb_token, $skuid)
{
    global $sb_api_url;

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $sb_api_url . "service-templates/" . $skuid,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: JWT " . $sb_token,
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        $data['status'] = 'error';
        $data['msg'] = $err;
    } else {
        //echo $response;
        $serviceInstData = safe_json_decode($response, true);
        if (array_key_exists('references', $serviceInstData)) {
            foreach ($serviceInstData as $key => $refvalue) {
                if ($key === 'references') {
                    foreach ($refvalue['service_template_properties'] as $value) {
                        $data['service_instance_properties'][] = $value;
                    }
                }
            }
        }
    }
    return json_encode($data);
}

function SVBT_getUserInformation($sb_token, $emailval)
{
    global $sb_api_url;
    try {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $sb_api_url . "users/search?key=email&value=" . $emailval,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Authorization: JWT " . $sb_token,
                "Content-Type: application/json"
            ]
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $response = ["status" => 'error', "errmsg" => $err];
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        echo $exc->getTraceAsString();
    }
    return $response;
}



/*
 *  common functions : start
 */

function getSiteInformation($regcode, $sitename, $db)
{
    $getSiteSql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$regcode' and sitename = '$sitename' limit 1";
    $getSiteRes = find_one($getSiteSql, $db);

    if (safe_count($getSiteRes) > 0) {
        return $getSiteRes;
    } else {
        return 0;
    }
}

function SVBT_getSiteEmailInformation($regcode, $sitename, $db)
{
    $siteData = getSiteInformation($regcode, $sitename, $db);
    if ($siteData) {
        $siteid = $siteData['siteid'];
        $installuserid = $siteData['installuserid'];

        $semailSql = "select * from Siteemail where siteid = $siteid and installuserid = $installuserid";
        $semailRes = find_one($semailSql, $db);

        return $semailRes;
    }
}

/*
 *  common functions : end
 */

// API to retrieve the offerings information
function SVBT_getPublicServiceTemplates($sb_token)
{
    global $sb_api_url;

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $sb_api_url . "service-templates/public",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "Authorization: JWT " . $sb_token,
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $response = ["status" => "failed", "message" => $err];
    }
    return $response;
}

function SVBT_getServiceTemplatesById($sb_token, $skuid)
{
    global $sb_api_url;

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $sb_api_url . "service-templates/" . $skuid,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Authorization: JWT " . $sb_token,
            "Content-Type: application/json"
        ]
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        $response = ["status" => "failed", "message" => $err];
    }
    return $response;
}


function SVBT_createASICoreUser_Dev($installuser, $serverid, $db)
{

    $getsql = "select * from Users where installuser = '$installuser'";
    $getres = command($getsql, $db);

    if (mysqli_num_rows($getres) > 0) {
        $data = mysqli_fetch_array($getres);
        $installemailid = $data['installemailid'];
        $installuserid = $data['installuserid'];
        $installuserpassword = $data['password'];
        $installskuids = $data['skuids'];
        $firstName = $data['firstname'];
        $lastName = $data['lastname'];
        //$userCompanyName = $data['companyname'];
        //$serverid = $data['serverid'];
    }

    $apisql = "select serverurl from Servers where serverid = '$serverid'";
    $apires = command($apisql, $db);

    if (mysqli_num_rows($apires) > 0) {
        $data = mysqli_fetch_array($apires);
        $apiurl = $data['serverurl'];
    }
    // curl request data
    $data['function'] = 'createuser';
    $data['data']['username'] = $installuser;
    $data['data']['password'] = $installuserpassword;
    $data['data']['skuids'] = $installskuids;
    $data['data']['serverid'] = $serverid;
    $data['data']['emailid'] = $installemailid;
    $data['data']['fname'] = $firstName;
    $data['data']['lname'] = $lastName;
    $data['data']['userid'] = $installuserid;

    MAKE_CURL_CALL($apiurl, $data);
}

/*function SVBT_createASICoreCustomers($sitename, $email, $db, $asicon) {
    $sql = "select userid, username from core.Users where user_email = '$email' limit 1";
    $res = mysqli_query($asicon, $sql);
    
    if (mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $custSql = "insert into core.Customers (username, customer) values ('".$row['username']."', '$sitename')";
            $custRes = mysqli_query($asicon, $custSql);
        }
    }
}*/

function MAKE_CURL_CALL($url, $data)
{

    $data_string = json_encode($data);

    $header = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url . 'licenseapi.php');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            echo 'ASI Server Info : <b>' . curl_errno($ch) . ' | ' . $error_msg . '</b>' . PHP_EOL;
        } else {
            $presdata = safe_json_decode($result, TRUE);
            echo 'ASI Server Info : <b>' . $presdata['msg'] . '</b>' . PHP_EOL;
        }
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
}
