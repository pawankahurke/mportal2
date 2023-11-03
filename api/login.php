<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-UserToken');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once "Rest.inc.php";

if (!function_exists('apache_request_headers')) {
    function apache_request_headers()
    {
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($key, 5)))));
                $out[$key] = $value;
            } else {
                $out[$key] = $value;
            }
        }
        return $out;
    }
}

function readJWT($param)
{

    global $HFN_DECRYPT_JWT_KEY;

    $key = $HFN_DECRYPT_JWT_KEY;

    $token = "";
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $authorize = sscanf($authHeader, 'Bearer %s');
        $token = $authorize[0];

        if (!empty($token)) {

            $json = decode($token, $key);
            $jsonobj = safe_json_decode($json, true);
            $data = $jsonobj[$param];
            return $data;
        } else {
            return 'ERROR';
        }
    } else if (apache_request_headers()["Authorization"]) {
        $authHeader = apache_request_headers()["Authorization"];
        $authorize = sscanf($authHeader, 'Bearer %s');
        $token = $authorize[0];
        if (!empty($token)) {
            $json = decode($token, $key);
            $jsonobj = safe_json_decode($json, true);
            $data = $jsonobj[$param];
            return $data;
        } else {
            return 'ERROR';
        }
    } else {
        return 'EMPTY';
    }
}

function generateJWT($data)
{

    global $HFN_ENCRYPT_JWT_KEY;
    $header = '{"alg":"HS256","typ":"JWT"}';

    $payload = json_encode($data);

    $key = $HFN_ENCRYPT_JWT_KEY;
    $token = encode($header, $payload, $key);
    return $token;
}

function generateLoginJWT($data)
{

    global $HFN_DECRYPT_JWT_KEY;
    $header = '{"alg":"HS256","typ":"JWT"}';

    $payload = json_encode($data);

    $key = $HFN_DECRYPT_JWT_KEY;
    $token = encode($header, $payload, $key);
    return $token;
}

function validateuser()
{
    global $HFN_ENCRYPT_JWT_KEY;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $email = readJWT("username");
    $password = readJWT("password");

    $encryptedpwd = md5($password);
    $loginSql = "select userid,ch_id,username,user_email,user_phone_no,role_id,priv_admin from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=lower('$email')) and password='$encryptedpwd' and priv_api='1' limit 1;";
    if (!empty($email) and !empty($password)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result = find_one($loginSql, $db);
            if (safe_count($result) > 0) {

                $data = time() . $email . "-" . base64_decode($password);

                $token = hash('sha256', $_SERVER["SERVER_NAME"] . $data);

                $update_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET token='" . $token . "' , tokenGenerationTime=UNIX_TIMESTAMP(NOW()) , tokenLastUsed=UNIX_TIMESTAMP(NOW()) where userid='" . $result['userid'] . "';";
                $res = redcommand($update_sql, $db);

                if (!$res) {
                    $error = array('status' => "Failed", "msg" => "Could not validate user");
                    response(json($error), 400);
                } else {
                    $expire = time() + 15 * 60;
                    $issuedAt = time();
                    $session_id = $HFN_ENCRYPT_JWT_KEY;
                    $jwt = [
                        'iss' => "nanoheal.com", "company" => "HP",
                        'data' => [
                            "userId" => $result['userid'],
                            "userName" => $result['username'],
                            "usertoken" => $token,
                        ],
                    ];
                    $set_header_data = generateJWT($jwt);
                    $success = array('status' => "Success", "AuthToken" => $token, "Authorization" => $set_header_data);
                    jwt_response($set_header_data, json($success), 200);
                }
            } else {
                $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
                response(json($error), 401);
            }
        }
    } else {
        $error = array('status' => "Failed", "msg" => "Email Id or Password not found");
        response(json($error), 400);
    }
}

function validateCRMDetails($input)
{

    global $HFN_ENCRYPT_JWT_KEY;
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $email = $input["username"];
    $password = $input["userpass"];

    $encryptedpwd = md5($password);
    $loginSql = "select userid,ch_id,username,user_email,user_phone_no,role_id,priv_admin,token,user_email from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=lower('$email')) and password='$encryptedpwd' and priv_api='1' limit 1";

    if (!empty($email) and !empty($password)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result = find_one($loginSql, $db);
            if (safe_count($result) > 0) {

                $userid = $result['user_email'];
                $jwt = [
                    "username" => $userid,
                    "password" => $password,
                ];
                $set_header_data = generateLoginJWT($jwt);
                $success = array('status' => "Success", "Authorization" => $set_header_data);
                response(json($success), 200);
            } else {
                $error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
                response(json($error), 401);
            }
        }
    } else {
        $error = array('status' => "Failed", "msg" => "Email Id or Password not found");
        response(json($error), 400);
    }
}

function deviceinformation($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    if (!empty($hostname) and !empty($sitename)) {

        $app_sql = "select d.dataid,m.machineid,d.name,a.value from " . $GLOBALS['PREFIX'] . "asset.DataName d ," . $GLOBALS['PREFIX'] . "asset.AssetData a," . $GLOBALS['PREFIX'] . "asset.Machine m where " .
            " d.name in ('Machine Name','System Product','System Manufacturer','Serial number','Language','OS Version Number','Battery Capacity','MAC address','Bluetooth MAC address','Wi-Fi MAC address','IMEI NO','Number of cores') " .
            " and d.dataid=a.dataid and m.machineid=a.machineid and m.host='" . $hostname . "' and m.cust='" . $sitename . "';";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find device information for mentioned host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function getCustomerEmail($input)
{
    $custno = $input['custno'];
    $reult_array = [];

    if (!empty($custno)) {
        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . 'core', $db);
        $sql = "SELECT emailId, sessionid FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c WHERE c.customerNum='$custno' LIMIT 1";
        $result = find_one($sql, $db);

        if (safe_count($result) > 0) {
            $reult_array = array('status' => "success", "email" => $result['emailId']);
        } else {
            $reult_array = array('status' => "failed", "msg" => "Customer number not valid");
        }
    } else {
        $reult_array = array('status' => "failed", "msg" => "Customer number not found");
    }
    return json($reult_array);
}

function get_customer_email_old($input)
{
    if ($this->get_request_method() != "POST") {
        $this->response('', 400);
    }

    $custno = $this->readJWT("custno");

    $loginSql = "SELECT emailId,sessionid FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder c where c.customerNum='$custno' limit 1;";

    if (!empty($custno)) {
        $sql = mysql_query($loginSql, $this->db);
        if (mysqli_num_rows($sql) > 0) {
            $result = mysql_fetch_array($sql, MYSQL_ASSOC);

            $success = array('status' => "Success", "Registered email Id " => $result['emailId']);
            $jwt = [
                'iss' => "nanoheal.com", "company" => "HP",
                'data' => [
                    "email" => $result['emailId'],
                    "secretkey" => $result['sessionid'],
                ],
            ];

            $set_header_data = $this->generateJWT($jwt);
            $this->jwt_response($set_header_data, $this->json($success), 200);
        } else {
            $error = array('status' => "Failed", "msg" => "Invalid customer number");
            $this->response($this->json($error), 400);
        }
    } else {
        $error = array('status' => "Failed", "msg" => "Customer number not found or Invalid key");

        $this->response($this->json($error), 400);
    }
}

function installed_program($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];

    if (!empty($hostname) and !empty($sitename)) {
        $app_sql = "select * from " . $GLOBALS['PREFIX'] . "asset.AssetData a where a.dataid IN (select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName where name = 'Installed Program') " .
            " and a.machineid = (select m.machineid from " . $GLOBALS['PREFIX'] . "asset.Machine m where " . " m.cust='" . $sitename . "' and m.host='" . $hostname . "'  " .
            " order by m.machineid desc limit 1) ;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find installed program list for mentioned host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function app_usage_details($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "SELECT d.*,d.exename As app,COUNT(d.exename) AS times, " .
            " FROM_UNIXTIME(sum(d.timespentonapp),'%H hr: %i min: %s sec') as duration, " .
            " FROM_UNIXTIME(d.clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat  FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail d " .
            " where d.siteName='" . $sitename . "' and d.machine='" . $hostname . "' " .
            " and d.datatype='1' " .
            $time_where .
            " GROUP BY d.exename having times>0 order by times desc;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find application usage records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function call_details($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "SELECT d.*,d.PhoneNo As number,d.typeOfCall as calltype,FROM_UNIXTIME(d.timespoke,'%H hr: %i min: %s sec') as duration, " .
            " FROM_UNIXTIME(d.clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat  FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail d  " .
            " where d.siteName='" . $sitename . "' and d.machine='" . $hostname . "' " .
            $time_where .
            " order by dateformat asc;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find call usage records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function web_usage_details($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "SELECT b.*,b.trackurl as url, " .
            " FROM_UNIXTIME(sum(b.timespentonurl),'%H hr: %i min: %s sec') as duration,COUNT(*) AS times, " .
            " FROM_UNIXTIME(b.clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat " .
            " from " . $GLOBALS['PREFIX'] . "mdm.BrowserDataAnalysis b  " .
            " where b.siteName='" . $sitename . "' and b.machine='" . $hostname . "' " .
            $time_where .
            " group by b.trackurl order by dateformat asc;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find web usage records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function mobile_data_details($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "Select d.*,d.machine,d.exename,sum(d.mobiledatausage) as mobileusage_mb,d.clientTime, " .
            "count(d.exename) as times , " .
            "FROM_UNIXTIME(d.clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat " .
            "from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail d where  " .
            "d.datatype='2'  " .
            " " . $time_where . " and " .
            " d.siteName='" . $sitename . "' and d.machine='" . $hostname . "' " . " group by d.exename order by mobileusage_mb desc; ";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find mobile usage records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function wifi_data_details($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "Select d.*,d.machine,d.exename,sum(d.wifidatausage) as wifiusage_mb,d.clientTime, " .
            "count(d.exename) as times , " .
            "FROM_UNIXTIME(d.clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat " .
            "from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail d where  " .
            "d.datatype='2'  " .
            " " . $time_where . " and " .
            " d.siteName='" . $sitename . "' and d.machine='" . $hostname . "' " . " group by d.exename order by wifiusage_mb desc; ";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find wifi usage records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function network_data_details($input)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "Select * from " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail " .
            " where siteName='" . $sitename . "' and machine='" . $hostname . "' " .
            " " . $time_where . " ;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find network data details records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function battery_usage_details($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);
    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "Select d.*,d.exename,d.batterydatausage, " .
            "count(d.exename) as times , " .
            "FROM_UNIXTIME(d.clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat, " .
            "SUM(d.batterydatausage) as totalusage, " .
            "CAST(SUM(d.batterydatausage) AS DECIMAL) / count(d.exename) as percentage " .
            " from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail d where " .
            " d.datatype='3' and " .
            " d.batterydatausage<>0  " .
            " " . $time_where . " and " .
            " d.siteName='" . $sitename . "' and d.machine='" . $hostname . "' " . " group by d.exename order by totalusage desc;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find battery usage records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function location_tracking($input)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    $hostname = $input['host'];
    $sitename = $input['site'];
    $startDate = $input["startDate"];
    $endDate = $input["endDate"];
    $startDate = strtotime($startDate);
    $endDate = strtotime($endDate);

    if (!empty($hostname) and !empty($sitename)) {
        if ($startDate != '' && $endDate != '') {
            $time_where = ' and clientTime >=' . $startDate . ' and clientTime <=' . $endDate;
        }

        $app_sql = "Select *, " .
            " FROM_UNIXTIME(clientTime,'%a %b %d %H:%i:%s UTC %Y') as dateformat " .
            " from " . $GLOBALS['PREFIX'] . "mdm.LocationDetail where " .
            " siteName='" . $sitename . "' and machine='" . $hostname . "'  " . $time_where . " " .
            " order by id asc limit 100;";

        $res = find_many($app_sql, $db);
        if ($res) {
            response(json($res), 200);
        } else {
            $error = array('status' => "error", "msg" => "Couldnot find location tracking records for mentioned dates or Invalid host or site");
            response(json($error), 400);
        }
    } else {
        $error = array('status' => "failed", "msg" => "Host name parameter not passed or site name parameter not passed");
        response(json($error), 400);
    }
}

function json($data)
{
    if (is_array($data)) {
        return json_encode($data);
    }
}

function getRightPaneUsingEmail($input)
{
    $apikey = $_SERVER['HTTP_X_API_KEY'];

    $email = trim($input['email']);
    $result_array = [];
    $siteArray = [];
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result_array = array("status" => "failed", "msg" => "Invalid email address");
    } else {
        $sql = "SELECT customerNum, orderNum, coustomerFirstName, coustomerLastName, siteName FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder "
            . "WHERE emailId = '$email' AND siteName IS NOT NULL";
        $result = find_many($sql, $db);
        if (safe_count($result) > 0) {
            foreach ($result as $value) {
                array_push($siteArray, $value['siteName']);
            }

            $result_array = getRightPaneForEmail($apikey, $siteArray, $db);
        } else {
            $result_array = array("status" => "failed", "msg" => "No records found");
        }
    }
    return json($result_array);
}

function getRightPaneForEmail($key, $siteArray, $db)
{
    $json = [];
    foreach ($siteArray as $site) {
        $machines = DASH_GetMachinesSites($key, $db, $site);
        $machineStatus = DASH_GetAllMachineStatusNOs($key, $db, $machines);
        $temp = [];
        foreach ($machines as $id => $host) {
            $temp[$host]['status'] = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
            $temp[$host]['os'] = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
            $temp[$host]['censusId'] = $id;

            $json['Sites']['All']['machines'][$host]['status'] = $machineStatus[$host][0];
            $json['Sites']['All']['machines'][$host]['os'] = $machineStatus[$host][1];
            $json['Sites']['All']['machines'][$host]['censusId'] = $id;
        }
        $json['Sites'][$site]['machines'] = $temp;
    }
    return $json;
}
