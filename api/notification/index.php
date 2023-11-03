<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-sUserToken');
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();


include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-db.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-sql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-gsql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-rcmd.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-util.php';


include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/api/JWT.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/api/auth.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/api/login.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/api/notificationV8/notificationV8.php';

include_once 'notificationV8.php';


$inputJSON = file_get_contents('php://input');
$input = safe_json_decode($inputJSON, TRUE);


if (($input == '') && (!url::issetInRequest('rquest'))) {
    foreach ($_REQUEST as $key => $value) {
        $input[$key] = $value;
    }
    $function = (trim($input['scope']));
} else {
    if (url::issetInRequest('rquest')) {
        $function = (trim(str_replace("/", "", url::requestToAny('rquest'))));
    } else {
        $function = (trim($input['scope']));
    }
}

if ($function == '') {
    $jsondata = '[{"status":"error", "msg":"No method has been selected."}]';
    echo $jsondata;
} else {
    if ($function != 'validateuser') {

        $scope_range = [
            'getProfileTiles', 'getSolutions', 'pushSolutionsNew', 'validateCRMDetails', 'machineHistory', 'UserAuthenticate', 'machineOnlineStatus', 'machineBasicInfo', 'machineAssetID', 'actionInteractive', 'getMachineDetails', 'getMachinesList', 'getNotificationSummary', 'actionNotifications', 'getProfileData'
        ];

        if (!in_array($function, $scope_range)) {
            $jsondata = array("status" => "error", "msg" => "Invalid scope. (1) for function $function");
            response(json_encode($jsondata), 200);
        } else {
            if ($function == 'validateCRMDetails') {
                $datares = $function($input);
                echo $datares;
            } else {
                global $HFN_ENCRYPT_JWT_KEY;
                $auth_key = $HFN_ENCRYPT_JWT_KEY;
                $db = db_connect();
                db_change($GLOBALS['PREFIX'] . 'core', $db);
                $validate = basicauth_validate($auth_key, $db);
                if ($validate == 'Success') {
                    $datares = $function($input);
                    echo $datares;
                } else {
                    echo $validate;
                }
            }
        }
    } else {
        $datares = $function($input);
        echo $datares;
    }
}
