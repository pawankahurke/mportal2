<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

global $sb_api_url;
global $stripe_api_url;
// $sb_api_url = "https://servicebot.nanoheal.com/api/v1/";
$stripe_api_url = "https://api.stripe.com/v1/";

function SVBT_validateServiceBotAccess()
{
    global $sb_api_url;

    $username = 'admin@nanoheal.com';
    $password = 'nanoheal@123$';

    try {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $sb_api_url . "auth/token",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\"email\" : \"$username\",\"password\" : \"$password\"}",
            CURLOPT_HTTPHEADER => [
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(), "Content-Type: application/json"
            ]
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
        $emailid = $siteData['email'];

        $maxSql = "select * from Siteemail where email = '$emailid' and siteid = $siteid"
            . " and installuserid = $installuserid";
        $maxRes = find_one($maxSql, $db);

        $maxCount = $maxRes['maxinstall'];
        $installedCount = $maxRes['numinstalls'];

        if ($maxCount != 0) {
            if ($installedCount < $maxCount) {
                $doInstall = true;
            } else {
                $doInstall = false;
            }
        }
    }
    return $doInstall;
}

function SVBT_createUser($sb_token, $emailid)
{

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://servicebot.nanoheal.com/api/v1/users/invite",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_POSTFIELDS => '{"email" : "' . $emailid . '"}',
        CURLOPT_HTTPHEADER => [
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(), "Authorization: JWT " . $sb_token, "Content-Type: application/json"
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

function SVBT_registerUser($sb_token, $userRegisterUrl, $emailid, $password)
{

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $userRegisterUrl,
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => '{"email":"' . $emailid . '", "password":"' . $password . '"}',
        CURLOPT_HTTPHEADER => [
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
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
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_TIMEOUT => 30,
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
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
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
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
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
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
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
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_FOLLOWLOCATION => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => [
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
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

function SVBT_coreServerConnect($installuser, $db)
{

    $sql = "select installuserid from Users where installuser = '$installuser'";
    $res = command($sql, $db);

    if (mysqli_num_rows($res) > 0) {
        $data = mysql_fetch_array($res);
        $installuserid = $data['installuserid'];
    }

    $serversql = "select * from Servers where installuserid = $installuserid";
    $serverres = command($serversql, $db);

    if (mysqli_num_rows($serverres) > 0) {
        $serverdata = mysql_fetch_array($serverres);

        $asi_urldata  = explode(':', $serverdata['url'])[1];
        $asi_username = $serverdata['username'];
        $asi_password = $serverdata['password'];

        $asi_urlinfo  = explode('/', $asi_urldata);
        $asi_url = $asi_urlinfo[2];
        $asi_port = 4791;
        $asi_con = mysqli_connect($asi_url, $asi_username, $asi_password, $GLOBALS['PREFIX'] . 'core', $asi_port);

        if ($asi_con) {
            $ret = ["status" => 1, "dbcon" => $asi_con];
        } else {
            $ret = ["status" => 0];
        }
    }
    return $ret;
}

function SVBT_createASICoreUser($installuser, $db, $asicon)
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }

    $getsql = "select installuserid, password from Users where installuser = '$installuser'";
    $getres = command($getsql, $db);

    if (mysqli_num_rows($getres) > 0) {
        $data = mysql_fetch_array($getres);
        $installuserid = $data['installuserid'];
        $installuserpassword = $data['password'];
    }

    $sitesql = "select sitename from " . $GLOBALS['PREFIX'] . "install.Sites where installuserid = $installuserid";
    $siteres = command($sitesql, $db);

    if (mysqli_num_rows($siteres) > 0) {
        while ($row = mysqli_fetch_assoc($siteres)) {
            $sitenamelist .= $row['sitename'] . ',';
        }
    }
    $sitenamelist = rtrim($sitenamelist, ',');

    $checkSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users where username = '$installuser' limit 1";
    $checkRes = mysqli_query($asicon, $checkSql);

    if (mysqli_num_rows($checkRes) === 0) {

        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users (username, password, priv_admin) VALUES "
            . "('$installuser', '$installuserpassword', 1)";
        $res = mysqli_query($asicon, $sql);

        if ($res) {
            $coreuserid = mysqli_insert_id($asicon);
            $sitearrlist = explode(',', $sitenamelist);
            foreach ($sitearrlist as $value) {
                $custsql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) VALUES "
                    . "('$installuser', '$value')";
                mysqli_query($asicon, $custsql);
            }
        }
        echo 'Created ASI server core user with userid# ' . $coreuserid;
    } else {
        $sitearrlist = explode(',', $sitenamelist);
        foreach ($sitearrlist as $value) {

            $dlCustSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Customers where username = '$installuser' "
                . "and customer = '$value'";
            $dlCustRes = mysqli_query($asicon, $dlCustSql);

            if (mysqli_num_rows($dlCustRes) === 0) {
                $custsql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) VALUES "
                    . "('$installuser', '$value')";
                mysqli_query($asicon, $custsql);
            }
        }
        echo "ASI Core user has been updated successfully!" . PHP_EOL;
    }
    mysqli_close($asicon);
}
