<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'include/common_functions.php';
header("Access-Control-Allow-Origin: *");

$conn = db_connect();
db_change($GLOBALS['PREFIX'] . "agent", $conn);

$siteRegCode = url::issetInRequest('siteRegCode') ? str_replace("'", "", url::requestToAny('siteRegCode')) : '';
$serviceTag = url::issetInRequest('servicetag') ? str_replace("'", "", url::requestToAny('servicetag')) : '';
$macaddress = url::issetInRequest('servicetag') ? str_replace("'", "", url::requestToAny('macaddress')) : '';
$streamUrl = url::issetInRequest('streamUrl') ? str_replace("'", "", url::requestToAny('streamUrl')) : '';
if ($streamUrl != '') {
	//$serverName = 'Kyndryl';
	$sql_ser = "select cid from  " . $GLOBALS['PREFIX'] . "install.Servers where streamingurl='" . $streamUrl . "'";
	$res_ser = find_one($sql_ser, $conn);
	$cid = $res_ser['cid'];
	echo $cid;
} else if ($siteRegCode != '') {
	$sql_ser = "select count(distinct serviceTag) pcCount from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where siteRegCode='" . $siteRegCode . "'";
	$res_ser = find_one($sql_ser, $conn);
	$pcCount = $res_ser['pcCount'];

	/*	if ( $pcCount > 0) {
		echo "The query worked.";
		}
			*/
	$sql_sertag = "select count(distinct macAddress) as pcCount from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where siteRegCode='" . $siteRegCode . "'";
	$res_sertag = find_one($sql_sertag, $conn);
	$insCnt =  $res_sertag['pcCount'];

	/*	if ( $insCnt > 0) {
		echo "2nd query worked.";
		}
		
			*/
	if ($insCnt > $pcCount) {
		$pcCount = $insCnt;
	}

	$sql_ser = "select skuids from  " . $GLOBALS['PREFIX'] . "install.Sites where regCode='" . $siteRegCode . "'";
	$res_ser = find_one($sql_ser, $conn);
	$skuIds = $res_ser['skuids'];

	$sql_ser = "select quantity from  " . $GLOBALS['PREFIX'] . "install.skuOfferings where sid= '" . $skuIds . "'";
	$res_ser = find_one($sql_ser, $conn);
	$maxCount = $res_ser['quantity'];

	$sql_sertag = "select count(distinct macAddress) as reInstall from  " . $GLOBALS['PREFIX'] . "agent.serviceRequest where serviceTag='" . $serviceTag . "' and siteRegCode='" . $siteRegCode . "'";
	$res_sertag = find_one($sql_sertag, $conn);
	$isReinstall =  $res_sertag['reInstall'];

	if (($maxCount >= $pcCount) || ($maxCount == 0) || ($isReinstall != 0)) {
		echo "Proceed with the installation.";
		//echo "You have bought $maxCount licences and consumed all of them.";
	} else {
		echo "All the licences have been used.";
		//echo "You have consumed '" . $pcCount . "' out of $maxCount licences.";
	}
} else {

	echo 'Error to download setup file';
}
