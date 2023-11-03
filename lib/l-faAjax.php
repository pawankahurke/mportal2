<?php

// error_reporting(-1);
// ini_set('display_errors', 'On');
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
require_once '../include/common_functions.php';


nhRole::dieIfnoRoles(['user', 'edituser', 'adduser']); // roles: user , edituser, adduser

//Replace $routes['get'] with if else
if (url::postToText('function') === 'FAAJX_approveUser') { // roles: user , edituser, adduser
    approveUser();
} else if (url::postToText('function') === 'FAAJX_rejectUser') { // roles: user , edituser, adduser
    rejectUser();
} else if (url::postToText('function') === 'FAAJX_saveUserPermission') { // roles: user , edituser, adduser
    saveUserPermission();
}

if (url::getToText('function') === 'FAAJX_getLockedUsers') { // roles: user , edituser, adduser
    getLockedUsers();
}

function getLockedUsers()
{
    $pdo = pdo_connect();

    $stmt = $pdo->prepare('select username, firstName, lastName, user_email, userStatus '
        . 'from ' . $GLOBALS['PREFIX'] . 'core.Users where username NOT IN (select username from ' . $GLOBALS['PREFIX'] . 'core.Customers);');
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);
}

function updateUserFunction($columnName, $columnValue, $pdo, $type)
{
    if ($type == 'approve') {
        $userStatus = '1';
        $mailType = 'unlock';
    } else {
        $userStatus = '0';
        $mailType = 'lock';
    }
    $userstmt = $pdo->prepare('select username, user_email from ' . $GLOBALS['PREFIX'] . 'core.Users where ' . $columnName . ' = ?');
    $userstmt->execute([$columnValue]);
    $userdata = $userstmt->fetch(PDO::FETCH_ASSOC);

    $username = $userdata['username'];
    $user_email = $userdata['user_email'];

    $stmt = $pdo->prepare('update ' . $GLOBALS['PREFIX'] . 'core.Users set userStatus = ?,clogo = "" where ' . $columnName . ' = ?');
    $data = $stmt->execute([$userStatus, $columnValue]);

    if ($columnName == 'user_email') {
        $pwd = 'set';
    } else {
        $pwd = 'notset';
    }

    if ($data) {
        sendApprovalStatusMail($mailType, $username, $user_email, $userkey, $pwd);
        return 1;
    } else {
        return 0;
    }
}

function approveUser()
{
    $pdo = pdo_connect();
    $type = "approve";
    $userkey = htmlspecialchars(url::postToText('userkey'));
    $userEmail = url::postToText('userEmail');

    //Check if user has password created or not
    if ($userkey == '') {
        $response = updateUserFunction('user_email', $userEmail, $pdo, $type);
    } else {
        $response = updateUserFunction('userKey', $userkey, $pdo, $type);
    }

    if ($response) {
        echo "success";
    } else {
        echo "failed";
    }
}

function rejectUser()
{
    $pdo = pdo_connect();
    $type = 'reject';
    $userkey = htmlspecialchars(url::postToText('userkey'));
    $userEmail = url::postToText('userEmail');

    //Check if user has password created or not
    if ($userkey == '') {
        $response = updateUserFunction('user_email', $userEmail, $pdo, $type);
    } else {
        $response = updateUserFunction('userKey', $userkey, $pdo, $type);
    }

    if ($response) {
        echo "success";
    } else {
        echo "failed";
    }
}

function saveUserPermission()
{
    $pdo = pdo_connect();

    $userkey = htmlspecialchars(url::postToText('userkey'));
    $userRole = htmlspecialchars(url::postToText('userrole'));
    $userEmail = url::postToText('userEmail');
    $userSite = url::postToAny('usersite');

    if ($userkey == '') {
        $stmt = $pdo->prepare('select userid, username from ' . $GLOBALS['PREFIX'] . 'core.Users where user_email = ? limit 1');
        $stmt->execute([$userEmail]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->prepare('select userid, username from ' . $GLOBALS['PREFIX'] . 'core.Users where userKey = ? limit 1');
        $stmt->execute([$userkey]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($data) {
        $userid = $data['userid'];
        $username = $data['username'];

        // update role value
        $updt_stmt = $pdo->prepare('update ' . $GLOBALS['PREFIX'] . 'core.Users set role_id = ? , clogo = "" where userid = ?');
        $updt_stmt->execute([$userRole, $userid]);

        // Make Site Entries
        if (safe_count($userSite) > 0) {
            $delstmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'core.Customers where username = ?');
            $delstmt->execute([$username]);

            foreach ($userSite as $value) {
                $instmt = $pdo->prepare('insert into ' . $GLOBALS['PREFIX'] . 'core.Customers (username, customer) values (?, ?)');
                $instmt->execute([$username, $value]);
            }
        }
        echo 'success';
    } else {
        echo 'failed';
    }
}

function sendApprovalStatusMail($statType, $username, $useremail, $userkey, $pwd)
{
    global $base_url;
    if ($statType == 'unlock') {
        if ($pwd == 'set') {
            // $setpasslink = $base_url . 'reset-password.php?vid=' . $userkey;
            $body = 'Dear User' . PHP_EOL . PHP_EOL . 'Your Signup request has been approved by '
                . 'Nanoheal Admin. Please login to your account to get started' . PHP_EOL . PHP_EOL
                . 'Thanks & Regards' . PHP_EOL . 'Nanoheal Admin';
        } else {
            $setpasslink = $base_url . 'reset-password.php?vid=' . $userkey;
            $body = 'Dear User' . PHP_EOL . PHP_EOL . 'Your Signup request has been approved by '
                . 'Nanoheal Admin. Please use the below link to set Nanoheal portal password.'
                . PHP_EOL . PHP_EOL . 'Set Password Link : ' . $setpasslink . PHP_EOL . PHP_EOL
                . 'Thanks & Regards' . PHP_EOL . 'Nanoheal Admin';
        }
    } else if ($statType == 'lock') {
        $body = 'Dear User' . PHP_EOL . PHP_EOL . 'Your Nanoheal portal account has been de-activated. '
            . 'Please contact Nanoheal portal administrator to activate your account.' . PHP_EOL
            . PHP_EOL . 'Thanks & Regards' . PHP_EOL . 'Nanoheal Admin';
    }
    $subject = 'User Approval Status';
    $fromEmail = "noreply@nanoheal.com";
    $fromName = 'Nanoheal';

    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $useremail,
      'subject' => $subject,
      'text' =>'',
      'html' => $body,
      'token' => getenv('APP_SECRET_KEY'),
    );

    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";

    if (CURL::sendDataCurl($url, $arrayPost)) {
        return 1;
    } else {
        return 0;
    }
}
