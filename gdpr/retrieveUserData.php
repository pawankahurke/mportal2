<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';

$username = url::requestToAny('username');
$retrieveType = url::requestToAny('type');

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'core', $db);

if ($retrieveType == 'user') {

    $sql_user = "select user_email, firstName, lastName, timezone from " . $GLOBALS['PREFIX'] . "core.Users where username = '$username'";
    $res_user = find_one($sql_user, $db);

    if (safe_count($res_user) > 0) {
        $useremail = $res_user['user_email'];
        $userfName = $res_user['firstName'];
        $userLName = $res_user['lastName'];
        $timeZone = (is_null($res_user['timezone']) || empty($res_user['timezone'])) ? 'Not available' : $res_user['timezone'];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=userdata.csv');

        ob_clean();
        $output = fopen('php://output', 'w');

        fputcsv($output, array('User Name', 'Email Id', 'First Name', 'Last Name', 'Timezone'));

        fputcsv($output, array($username, $useremail, $userfName, $userLName, $timeZone));
        fclose($output);
    }
}
