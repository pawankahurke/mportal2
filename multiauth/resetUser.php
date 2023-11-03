<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
$db = pdo_connect();
$userId = $_GET['userid'];
		$udstmt = $db->prepare("update ".$GLOBALS['PREFIX']."core.Users set google_secret_code = NULL where userid = ?");
		$udstmt->execute([$userId]);

echo json_encode(array("status"=>"success","msg"=>"MFA resetted successfully"));
