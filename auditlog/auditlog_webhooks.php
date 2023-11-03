<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-db.php';

$db = pdo_connect();
$auditresult = array();
$auditresult = get_allAuditLog($db,'');

echo json_encode($auditresult);

function get_allAuditLog($db,$filter){
	
	$sql2 = $db->prepare("SELECT audit_id,module,action,username,useremail,status,if(refName IS NULL,'NA',refName) AS reference_name,created as logtime FROM ".$GLOBALS['PREFIX']."core.AuditLog ".$filter." order by created asc ");
        $sql2->execute();
    $result = $sql2->fetchAll(PDO::FETCH_ASSOC);

    return $result;
	
	
}









?>