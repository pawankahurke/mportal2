<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once("JWT.php");
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

function basicauth_validate($authtoken, $db)
{

    try {

        $key = $authtoken;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            $authorize = sscanf($authHeader, 'Bearer %s');

            if (!empty($authorize[0])) {

                $json = decode($authorize[0], $key);
                $jsonobj = safe_json_decode($json, true);
                $sesskey = $jsonobj['data']['usertoken'];
                $userid = $jsonobj['data']['userId'];
                $res = validateLogin($userid, $sesskey, $db);
                return $res;
            } else {

                $jsondata = array("status" => "error", "msg" => "Invalid Authorization");
                response(json($jsondata), 400);
            }
        } else if (apache_request_headers()["Authorization"]) {
            $authHeader = apache_request_headers()["Authorization"];
            $authorize = sscanf($authHeader, 'Bearer %s');

            if (!empty($authorize[0])) {

                $json = decode($authorize[0], $key);
                $jsonobj = safe_json_decode($json, true);
                $sesskey = $jsonobj['data']['usertoken'];
                $userid = $jsonobj['data']['userId'];
                $res = validateLogin($userid, $sesskey, $db);
                return $res;
            } else {

                $jsondata = array("status" => "error", "msg" => "Invalid Authorization");
                response(json($jsondata), 400);
            }
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        $jsondata = array("status" => "error", "msg" => "Some error occured! Please try again");
        response(json($jsondata), 400);
    }
}

function validateLogin($userid, $authtoken, $db)
{

    try {

        $sql_usr = "select * from " . $GLOBALS['PREFIX'] . "core.Users U where U.userid='$userid' and token='$authtoken' limit 1";

        $res_usr = find_one($sql_usr, $db);
        if (safe_count($res_usr) > 0) {
            $curdt = time();
            $mins = round(($curdt - $res_usr['tokenLastUsed']) / 60);
            if ($mins < 16) {
                $update_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET tokenLastUsed='$curdt' where userid='$userid'";
                $res = redcommand($update_sql, $db);
                return 'Success';
            } else {
                $jsondata = array("status" => "error", "msg" => "Auth token expired. Please relogin");
                response(json($jsondata), 400);
            }
        } else {

            $jsondata = array("status" => "error", "msg" => "Invalid Authorization");
            response(json($jsondata), 400);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}
