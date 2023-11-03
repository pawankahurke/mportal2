<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
global $asiDBip;
global $asiDBport;
global $asiDBusername;
global $asiDBpassword;

global $sb_api_url;
global $stripe_api_url;

global $ftp_server;
global $ftp_username;
global $ftp_userpass;
global $ftp_downloadpath;

$asiDBip = getenv('DB_HOST') ?: '10.240.0.28';
$asiDBport = '3306';
$asiDBusername = getenv('DB_USERNAME') ?: 'weblog';
$asiDBpassword = getenv('DB_PASSWORD') ?: 'MbvK1AtPXN5aMnyK5#D';

$sb_api_url = "https://servicebot11.nanoheal.com/api/v1/";
$stripe_api_url = "https://api.stripe.com/v1/";

$ftp_server = getenv('DASHBOARD_licenseurl') ?: 'localhost';
$ftp_username = 'nanoheal';
$ftp_userpass = 'DzN7r]59r4R}BO$bG3Z#';
$ftp_downloadpath = 'setups/live';
