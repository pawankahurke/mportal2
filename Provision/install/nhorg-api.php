<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  05-Mar-19   JHN     File Created for server API.
 * 
 * 
 */

//error_reporting(-1);
//ini_set('display_errors', 'On');

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once('../lib/l-cnst.php');
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-dberr.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-slct.php');
include_once('../lib/l-user.php');
include_once('../lib/l-head.php');
include_once('../lib/l-errs.php');

include_once('../lib/l-svbt.php');

$content = file_get_contents('php://input');
$data = safe_json_decode($content, true);

switch ($data['function']) { // roles: user, adduser

    case 'registeruser':
        nhRole::dieIfnoRoles(['user', 'adduser']); // roles: user, adduser
        $emailid = $data['emailid'];

        /*$authResp = SVBT_validateServiceBotAccess();
        $authRespData = safe_json_decode($authResp, true);
        $sb_token = $authRespData['token'];

        $userResp = createUser($sb_token, $emailid);
        $userRespData = safe_json_decode($userResp, true);
        $userRegisterUrl = $userRespData['api'];

        $password = 'nanoheal@123';
        $registerResp = registerUser($sb_token, $userRegisterUrl, $emailid, $password);
        $registerRespData = safe_json_decode($registerResp, true);*/

        //if ($registerRespData['message'] === 'successful signup') {
        //echo 'Service Bot User has been created successfully!' . PHP_EOL;
        $password = 'nanoheal@123';
        $orguserid = createORGUser($emailid, $password);
        if ($orguserid) {
            echo 'ORG User with id ' . $orguserid . ' has been created successfully!';
        }
        //}
        break;

    default:
        break;
}

function createUser($sb_token, $emailid)
{

    nhRole::dieIfnoRoles(['user', 'adduser']); // roles: user, adduser
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

function registerUser($sb_token, $userRegisterUrl, $emailid, $password)
{

    nhRole::dieIfnoRoles(['user', 'adduser']); // roles: user, adduser
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

function createORGUser($emailid, $password)
{
    nhRole::dieIfnoRoles(['user', 'adduser']); // roles: user, adduser
    $db = db_code('db_ins');
    if ($db) {
        // Create entry in install.Users table
        $installuser = explode('@', $emailid)[0];

        $userSql = "insert into Users set installuser = '$installuser', password = '" . md5($password) . "', "
            . "email = '$emailid', priv_servers = 1, priv_email = 1";
        $userRes = command($userSql, $db);
        if (affected($userRes, $db)) {
            $num = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        }
    }
    return $num;
}
