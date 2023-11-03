<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
require_once '../lib/l-sql.php';
require_once '../lib/l-gsql.php';
require_once '../lib/l-rcmd.php';
require_once '../lib/l-util.php';
global $licensePostapiUrl;
$pdo = pdo_connect();
$option  = '';
$custId = url::getToText('custId');
$subscriptions = CURL::getContentByURL($licensePostapiUrl . "?action=listsku&custId=" . $custId);
$subscriptionList = safe_json_decode($subscriptions, true);

foreach ($subscriptionList as $key => $value) {

    $option .= "<option value='" . $key . "'>" . safe_addslashes(utf8_encode($value)) . "</option>";
}

echo $option;
