<?php

ini_set("session.cookie_httponly", '1');
ini_set('session.cookie_secure', '1');
ini_set('memory_limit', '10G');
ini_set('html_errors', 'off');

include_once "src/utils/utils.php";


session_start();

// http://xdebug.org/docs/code_coverage#xdebug_start_code_coverage
// xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);


$_SERVER['HTTPS'] = true;
$GLOBALS['HTTPS'] = true;

if (isset($GLOBALS['HTTP_SERVER_VARS'])) {
    $GLOBALS['HTTP_SERVER_VARS']['HTTPS'] = true;
}

if (file_exists("/etc/nanoheal-cloud/env.json")) {
    // @todo add keys to work with urls like this: "dp-3-23-dashboard.default.svc.cluster.local"
    $file = file_get_contents("/etc/nanoheal-cloud/env.json");
    $file = safe_json_decode($file);

    // virtualHost
    foreach ($file as $value) {
        if (
            $_SERVER['HTTP_HOST'] === $value->host ||
            $_SERVER['HTTP_HOST'] === $value->local
        ) {
            foreach ($value->env as $keyEl => $valueEl) {
                putenv("$keyEl=$valueEl");
            }
            break;
        }
    }
}

error_reporting(E_ALL);
if (getenv('PHP_ERROR_REPORTING') === 'WARNING') {
    error_reporting(E_ERROR & ~E_NOTICE);
}
if (getenv('PHP_ERROR_REPORTING') === 'ALL') {
    error_reporting(E_ALL);
}
if (getenv('PHP_ERROR_REPORTING') === 'ERROR') {
    error_reporting(E_ERROR);
}
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);

// header("Cache-Control: no cache");
// session_cache_limiter("private_no_expire");
// print_r($_SESSION);
//phpinfo();
global $base_url;
global $db_host;
global $db_port;
global $db_user;
global $db_password;
global $base_path;
global $mum_tab;
global $configServer;
global $redis_url;
global $redis_port;
global $redis_pwd;
global $reportingurl;
global $wsurl;
global $signupPassUrl;
global $download_ClientUrl;
global $purchaseUrl;
global $aviraEnabled;
global $suitcrm_service_url;
global $suitecrm_username;
global $suitecrm_password;
global $mauticURL;
global $mauticKEY;
global $privacyURL;
global $termsCond;
global $ClientUIDefaultLocation;
global $ClientUIFTPLocation;
global $apiCall_url;
global $elastic_url;
global $matic_segmentId;
global $CRMEN;
global $crmAccountId;
global $crmAccountName;
global $trialDays;
global $TS_restricted;
global $crmCompucom;
global $API_enable;
global $NH_API_URL;
global $kibana_url;
global $kibana_username;
global $kibana_password;
global $elast_alert;
global $elast_watch;
global $elast_path;
global $nhorg_server;
global $nhorg_port;
global $nhorg_username;
global $nhorg_password;
global $elastic_username;
global $elastic_password;
global $dbnFileName;
global $dbnAbsolutePath;
global $serverProtocol;
global $licenseurl;
global $licenseapiurl;
global $HTTP_HOST;
global $ftp_server;
global $ftp_user_name;
global $ftp_user_pass;
global $securityArray;
global $globalretry;
global $timer;
global $entireArray;
$entireArray = array();
global $laravelauth_url;

// Created this variables for audit purpose
global $logAudit; // Audit is required or not
global $auditStore; // Where to store audit details either in mysql (1) or elastic (0)
global $licensePostapiUrl;
global $cubeUrl;
global $cubePort;
global $ssosamlapiurl;
//Customer Specific Settings
global $supportMail;
global $mailCustName;
global $customerContact;
global $dashboardDownload;
global $troubleshooterMode;
$cubeUrl = "https://cubejs.nanoheal.com";

$file_path = $_SERVER['DOCUMENT_ROOT'] . "/Dashboard/";
$serverProtocol = /*getenv('DASHBOARD_serverProtocol') ?:*/ 'https://';
$pageURL = $serverProtocol;
$base_url = $pageURL . $_SERVER["HTTP_HOST"] . "/Dashboard/";
$rootpath = $_SERVER['DOCUMENT_ROOT'];
$base_path = $rootpath . "/Dashboard/";
$laravelauth_url = $pageURL . $_SERVER["HTTP_HOST"] . "/Dashboard/nanohealauth/";

$absDocRoot = $_SERVER['DOCUMENT_ROOT'] . "/Dashboard/";

$logAudit = 1; //Audit enabled
$auditStore = 1; // Audit will be stored in mysql

/* $db_host            = "54.87.231.72"; */
$db_host = getenv('DB_HOST') ?: "10.0.6.65";
$db_port = "3306";
$db_user = getenv('DB_DATABASE') ?: "weblog";
$db_password = getenv('DB_PASSWORD') ?: "b6Q4qT17xyfYJS9CJP2019#";

$configServer = getenv('DASHBOARD_configServer') ?: "https://" . $_SERVER["HTTP_HOST"] . "/Dashboard/";
$elastic_url = "";
$elastic_username = '';
$elastic_password = '';

$licenseurl = getenv('DASHBOARD_licenseurl') ?: "https://uatlicense.nanoheal.com/Provision/";
$licenseapiurl = $licenseurl . "install/asiapi.php";

$licensePostapiUrl = getenv('DASHBOARD_PostapiUrl') ?: "https://uatlicense.nanoheal.com/Provision/api/apilicense.php";
$dashlicenseapiurl = $pageURL . $_SERVER["HTTP_HOST"] . "/Dashboard/Provision/install/asiapi.php";
$dashlicensePostapiUrl = $pageURL . $_SERVER["HTTP_HOST"] . "/Dashboard/Provision/api/apilicense.php";


$redis_url = getenv('DASHBOARD_redis_url') ?: '127.0.0.1';
$redis_port = getenv('DASHBOARD_redis_port') ?: getenv('REDIS_PORT') ?: '6379';
$redis_pwd = getenv('DASHBOARD_redis_pwd') ?: getenv('REDIS_PASSWORD') ?: "b7Q5qJ23xyfYJS0CSR2020#";

$wsurl = getenv('DASHBOARD_wsurl') ?: 'wss://127.0.0.1:6379';

$reportingurl = getenv('DASHBOARD_reportingurl') ?: "https://" . $_SERVER["HTTP_HOST"] . "/main/rpc/rpc.php";

$trialDays = 365;
$main_dash_username = 'admin';
$main_dash_password = 'nanoheal@123';

$aviraEnabled = 0;

$suitcrm_service_url = 'https://crm.msp.nanoheal.com/spicecrm_uat/service/v4_1/rest.php';
$suitecrm_username = 'admin';
$suitecrm_password = 'admin@123#';

$mauticURL = 'https://campaign.msp.nanoheal.com/campaign_service_uat/services/';
$mauticKEY = 'ddd799a60673d28f0b711213fdebb098';
$matic_segmentId = 24;

$privacyURL = 'https://nanoheal.com/privacy-policy/';
$termsCond = 'https://nanoheal.com/terms-conditions/';

$ClientUIDefaultLocation = $rootpath . "/config/Default/";
$ClientUIFTPLocation = $rootpath . "/config/Customer/";

$signupPassUrl = "https://localhost/email-verifier/";

$CRMEN = 0;
$crmAccountId = '3dbef92a-ab3a-fa46-3d97-5a813faecd4e';
$crmAccountName = '_Nanoheal Web Site Submissions UAT';

// RSA Encryption/Decryption Key
$iv = 'a1a2a3a4a5a6a7a8b1b2b3b4b5b6b7b8';
$key = 'c1c2c3c4c5c6c7c8d1d2d3d4d5d6d7d8c1c2c3c4c5c6c7c8d1d2d3d4d5d6d7d8';

$TS_restricted = 1; // troubleshooter restriction only at machine level
$crmCompucom = 1; // compucom variable for enabling and disabling UI and Functionality of ITSM
$API_enable = 1; //API enabling

//API url
$NH_API_URL = 'https://localhost/v1/public/index.php/';

$kibana_url = '';
$kibana_ip_url = '';
$kibana_username = '';
$kibana_password = '';

$apiCall_url = getenv('DASHBOARD_apiCall_url') ?: "https://" . $_SERVER["HTTP_HOST"] . "/main/acct/NH-Config_API.php";
//$apiurl = getenv('DASHBOARD_apiurl') ?: "https://" . $_SERVER["HTTP_HOST"] . "/Dashboard/api/index.php";
//new apiurl
$apiurl = "http://localhost:85/Dashboard/api/index.php"; //if we remove the port no. in this line, then data in manage devices page will not display

//Notification Configuration
$elast_alert = 'https://localhost/Dashboard/elast/elast_alert.php';
$elast_watch = 'https://localhost/Dashboard/elast/elastwatcher.php';
$elast_path = '/opt/elastalert/rules';

// OTP global variables
$securityArray = ["emailotp" => 'Email OTP', "MFA" => 'MFA', "none" => 'None'];
$globalretry = 3;
$timer = 120;

$entireArray["kibanaurl"] = $kibana_url;
$entireArray["kibanaipurl"] = $kibana_ip_url;
$entireArray["kibanausername"] = $kibana_username;
$entireArray["kibanapass"] = $kibana_password;
$entireArray["elasticurl"] = $elastic_url;
$entireArray["elasticusername"] = $elastic_username;
$entireArray["elasticpass"] = $elastic_password;
$entireArray["securityarr"] = $securityArray;
$entireArray["globalretry"] = $globalretry;
$entireArray["timer"] = $timer;

$HTTP_HOST = $_SERVER["HTTP_HOST"];

$dbnFileName = 'core.dbn';
$dbnAbsolutePath = '/dbn/' . $dbnFileName;

$ftp_server = 'licensing-svc';
$ftp_user_name = 'nanoheal';
$ftp_user_pass = 'DzN7r]59r4R}BO$bG3Z#';

$supportMail = '';
$custMailName = '';
$customerContact = '';
$dashboardDownload = ''; // | values can be 'YES' or ''[default] |
$troubleshooterMode = 'On'; // values can be 'Off' or 'On' [default] |

$ssosamlapiurl = getenv('DASHBOARD_ssosamlapiurl');
// $ssosamlapiurl = 'https://sso.vd11.nanoheal.work';

/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

/// change cube js and visualization url here
//$cubejs = 'cubejs.nanoheal.com';

global $dash_tanentId;
global $dash_deployId;

global $dash_domainName;
global $dash_brandingUrl;
global $dash_downloadUrl;
global $dash_emailSubject;
global $dash_emailSender;
global $dash_emailBounce;
global $dash_emailHeaders;
global $dash_msgTxt;
global $dash_delay;
global $dash_startupid;
global $dash_followonid;

global $dash_delayon;
global $dash_uninstall;
global $dash_deploypath32;
global $dash_deploypath64;
global $dash_fcmUrl;

global $dash_client_32;
global $dash_client_64;
global $dash_client_android;
global $dash_client_mac;
global $dash_client_ios;
global $dash_client_linux;
global $dash_proxy;

//New Site Addition Variables
$dash_tanentId = getenv('TENANT_ID') ?: 3;
$dash_deployId = getenv('DEPLOYMENT_ID') ?: 62;
$dash_domainName = getenv('DASHBOARD_DOMAIN_NAME') ?: 'admin@nanoheal.com';
$dash_brandingUrl = getenv('DASHBOARD_BRANDING_URL') ?: 'cust_Default_Branding.zip';
$dash_downloadUrl = getenv('DASHBOARD_DOWNLOAD_URL') ?: "https://" . $_SERVER["HTTP_HOST"] . "/Dashboard/install/download/";
$dash_emailSubject = getenv('DASHBOARD_EMAIL_SUBJECT') ?: 'Nanoheal download URL';
$dash_emailSender = getenv('DASHBOARD_EMAIL_SENDER') ?: getenv('SMTP_USER_LOGIN') ?: 'support@nanoheal.com';
$dash_emailBounce = getenv('DASHBOARD_EMAILBOUNCE') ?: '';
$dash_emailHeaders = getenv('DASHBOARD_EMAILHEADERS') ?: '';
$dash_msgTxt = getenv('DASHBOARD_MSGTXT') ?: '%responseurl%';
$dash_delay = getenv('DASHBOARD_DELAY') ?: 2628000;
$dash_startupid = getenv('DASHBOARD_STARTUP_ID') ?: 'All';
$dash_followonid = getenv('DASHBOARD_FOLLOWON_ID') ?: 'All';

$dash_delayon = getenv('DASHBOARD_DELAY_ON') ?: 0;
$dash_uninstall = getenv('DASHBOARD_UNINSTALL') ?: 0;
$dash_deploypath32 = getenv('DASHBOARD_DEPLOY32') ?: '';
$dash_deploypath64 = getenv('DASHBOARD_DEPLOY64') ?: '';
$dash_fcmUrl = getenv('DASHBOARD_FCMURL') ?: '';
$dash_proxy = getenv('DASHBOARD_PROXY') ?: '';

$dash_client_32 = getenv('DASHBOARD_CLIENT_32') ?: 'NanohealClient-Setup-32Bit-V0062.exe';
$dash_client_64 = getenv('DASHBOARD_CLIENT_64') ?: 'NanohealClient-Setup-64Bit-V0089.exe';
$dash_client_android = getenv('DASHBOARD_CLIENT_ANDROID') ?: '';
$dash_client_mac = getenv('DASHBOARD_CLIENT_MAC') ?: '';
$dash_client_ios = getenv('DASHBOARD_CLIENT_IOS') ?: '';
$dash_client_linux = getenv('DASHBOARD_CLIENT_LINUX') ?: '';

$GLOBALS['PREFIX'] = getenv('DB_PREFIX') ? getenv('DB_PREFIX') : '';
$GLOBALS['APIGATEWAY_AUTH_KEY'] = getenv('APIGATEWAY_AUTH_KEY') ? getenv('APIGATEWAY_AUTH_KEY') : '';

whitelist::dieIfRequestIsWrong();
whitelist::checkRoute();

include_once 'configFromDB.php';
