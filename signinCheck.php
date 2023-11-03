<?php

/*
 * Name Date        Details
 * JHN  Feb 2021    Initial implementation for SSO redirection
 */

//ini_set('display_errors', 'On');
//error_reporting(-1);
include_once "config.php";
$sessionNonce = trim($_SESSION['nonce']);
$retNonce = url::getToText('nonce');
$email = trim(strtolower(url::getToText('email')));
if (!isset($_GET) || $sessionNonce == '' || $retNonce == '' || $sessionNonce != $retNonce || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    if (!getenv('SSO_NONCE_NO_CHECK')) {
        // If we have SSO_NONCE_NO_CHECK in env then we in test mode
        // We allow do not check NONCE to simplify cypress test code

        // Remove cookie for fix nonce: PHPSESSID, usertoken, sso
        setcookie('PHPSESSID', null, -1, '/');
        setcookie('usertoken', null, -1, '/');
        setcookie('sso', null, -1, '/');
        header("location: " . $base_url . "index.php?msg=nonce+has+errors&startSSOauth=1&sessionNonce=$sessionNonce&retNonce=$retNonce");
        exit;
    }
}
// $_SESSION['nonce'] = '';
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'lib/l-logAudit.php';
include_once 'include/common_functions.php';


$invalid = '';
$pdo = NanoDB::connect();

if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET)) {

    $email = trim(strtolower(url::getToAny('email')));
    $pwd = '';
    $timeZone = '';

    if ($email != '') {
        $loginType = '';
        $signInType = '';
        $logininfo = array('email' => $email, 'timezone' => $timeZone, 'authtype' => $loginType);
        $_SESSION["userloginfo"] = $logininfo;

        $username = explode('@', $email)[0];
        $access_token = '-';
        $authid_token = '-';

        $role_id = nhRole::$TYPE_RestrictedRole; // default for ad users
        $pwdExpiryDate = strtotime("+1 year");
        $userKey = uniqid();
        //setcookie('nh-userkey', $userKey, time() + (86400 * 30), "/");

        // check if user exists
        $userChkStmt = $pdo->prepare('select userid, username from ' . $GLOBALS['PREFIX'] . 'core.Users where user_email = ?');
        $userChkStmt->execute([$email]);
        $userChkData = $userChkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$userChkData) {
            $ins_user_stmt = $pdo->prepare('insert into ' . $GLOBALS['PREFIX'] . 'core.Users (firstName, '
                . 'username, password, user_email, role_id, parent_id, userStatus, '
                . 'passwordDate, access_token, id_token, userKey, userType) '
                . 'values (?,?,?,?,?,?,?,?,?,?,?,?)');
            $ins_user_res = $ins_user_stmt->execute([
                $username, $username, '-', $email,
                $role_id, 1, 0, $pwdExpiryDate, $access_token, $authid_token, $userKey, 'SSO',
            ]);
            // $err = $ins_user_stmt->errorInfo();
            // if ($err) {
            //     logs::log(__FILE__, __LINE__, json_encode($err), 0);
            //     print_r($err);
            //     die();
            // }
            // sendApprovalMail(); // Send Approval Mail to Admin Users
        }

        $authentication = CheckSSOAuthentication($pdo, $email, $pwd, $loginType, $timeZone, $signInType, '');
        login_audit($db, $username, 'SSO', time(), 'Success');
    }
}

?>

<html>
<script type="text/javascript">
    location.replace("<?php echo $base_url . "home/" ?>");
</script>

</html>
