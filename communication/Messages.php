<?php

$myfile = fopen("log1.txt", "a");

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();

$ServiceTag = url::issetInRequest('serviceTag') ? url::requestToAny('serviceTag') : '';
$BID = url::issetInRequest('AID') ? url::requestToAny('AID') : '';
$status = url::issetInRequest('status') ? url::requestToAny('status') : '';

$db = new mysqli($db_host, $db_user, $db_password, 'communication', $db_port);
echo '<br>';
echo $db_user;
echo '<br>';
echo $db_password;
echo '<br>';
echo $db_user;
echo '<br>';
echo $db_port;
exit;
if ($db->connect_errno > 0) {
    echo "1";
    die('Unable to connect to database [' . $db->connect_error . ']');
}

$db->select_db("communication");
$current_timestamp = time();
$sql = "UPDATE ".$GLOBALS['PREFIX']."communication.Audit set JobStatus = $status, clientExecutedTime = $current_timestamp WHERE MachineTag ='$ServiceTag' and BID = $BID";
fwrite($myfile, PHP_EOL);
fwrite($myfile, $sql);
if (!$result = $db->query($sql)) {
    echo "1";
    die('There was an error running the query [' . $db->error . ']');
} else {
    echo "0";
}
?>
