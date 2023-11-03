<?php

$_POST["allow"] = true; // disable csfr
$header = explode(' ', apache_request_headers()['Authorization']);
$keyType = $header[0];
$keyValue = $header[1];

if($keyValue != getenv('APIGATEWAY_AUTH_KEY') || $keyType != 'apiKey'){ 
  http_response_code(401);
  exit;
}

$function = explode('/', $_GET['rquest']);

$entrie = $function[count($function) - 2];
$function = end($function);


$_REQUEST['rquest'] = str_replace('Dashboard/api/'.$entrie, '', $_GET['rquest']);

if($entrie == 'profile'){
  include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/api/profile/common.php";
  include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/api/profile/index.php";
  include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/api/profile/profileV8.php";
} else if($entrie == 'notification'){
  include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/api/notification/index.php";
  include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/api/notification/notificationV8.php";
} else if($entrie == 'license'){
  include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/api/license/licenseapi.php";
}
