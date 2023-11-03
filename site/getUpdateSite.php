<?php
use function GuzzleHttp\Psr7\str;

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
require_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
require_once '../lib/l-sql.php';
require_once '../lib/l-gsql.php';
require_once '../lib/l-rcmd.php';
require_once '../lib/l-util.php';
global $licensePostapiUrl;
$pdo = pdo_connect(); $id = url::getToAny('id');
$siteList = CURL::getContentByURL($licensePostapiUrl."?action=getSiteList&id=".$id);
echo json_encode($siteList);
