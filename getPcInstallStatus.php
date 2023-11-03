<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'include/common_functions.php';

$conn = db_connect();
db_change($GLOBALS['PREFIX'] . "agent", $conn);

$sessionid = url::requestToAny('sessionid');

$sessionid = str_replace("'", "", $sessionid);

if ($sessionid != '') {

	$sql_cust = "select * from customerOrder where sessionid='" . $sessionid . "' limit 1";
	$res_cust = find_one($sql_cust, $conn);


	$customerNo = $res_cust['customerNum'];
	$orderNo    = $res_cust['orderNum'];
	$processId = $res_cust['processId'];
	if ($customerNo != '' && $orderNo != '') {

		$sql_ser = "select count(sid) pcCount,customerNum,orderNum,serviceTag,downloadStatus from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum='" . $customerNo . "' and orderNum='" . $orderNo . "' and revokeStatus='I' and processId='$processId' limit 1";
		$res_ser = find_one($sql_ser, $conn);
		$pcCount = $res_ser['pcCount'];
		$noOfPc = $res_cust['noOfPc'];
		$contractDate = $res_cust['contractEndDate'];

		if (($noOfPc > $pcCount) || ($noOfPc == 0)) {

			if ($contractDate > time()) {
				$remainCnt = $noOfPc - $pcCount;
				echo $remainCnt;
			} else {
				echo 0;
			}
		} else {
			echo 0;
		}
	} else {
		echo 0;
	}
} else {

	echo 0;
}
