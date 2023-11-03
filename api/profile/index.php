<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-sUserToken');
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
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
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/include/NH-Config_API.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-vars.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/api/profile/profileV8.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-profileAPI.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/api/profile/common.php';


$inputJSON = file_get_contents('php://input');
$input = safe_json_decode($inputJSON, TRUE);

logs::log("inputJSON", [$inputJSON]);

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

if (!is_array($input)) {
    $input = [];
    // die("Invalid Request, can not parse json `$input` to object");
}

switch ($function) {
    case 'getMachinewise':
        $datares = $function($input);
        echo $datares;
    case 'getProfileItems':
        $datares = $function($input);
        echo $datares;
    case 'pushSolutionItems':
        $datares = $function($input);
        echo $datares;
    case 'getjobstatus':
        $datares = $function($input);
        echo $datares;
    default:
        break;
}
