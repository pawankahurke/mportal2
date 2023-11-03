<?php
ini_set('session.cookie_httponly', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_secure', '1');
include_once "config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'include/common_functions.php';

$db     = db_connect();
db_change($GLOBALS['PREFIX'] . "agent", $db);

$keepPrimaryLoggedIn = false;

if (isset($_SESSION)) {
    if (isset($_SESSION['logout_user_' . $_SESSION['user']['userid']]) && $_SESSION['logout_user_' . $_SESSION['user']['userid']]) {
        $keepPrimaryLoggedIn = true;
        unset($_SESSION['logout_user_' . $_SESSION['user']['userid']]);
    }
}

$loggedOut = logout($db, $keepPrimaryLoggedIn);

if ($loggedOut && !url::issetInPost('no-redirect')) {
    setcookie('PHPSESSID', null, -1, '/');
    setcookie('usertoken', null, -1, '/');
    setcookie('sso', null, -1, '/');
    //for visualization service logout request
    if ($_POST['allow']) {
        logs::log(__FILE__, __LINE__, 'if v ife', 0);
        header("Content-type: application/json; charset=utf-8");
        echo json_encode(['success' => true]);
        exit();
    }

    header("Clear-Site-Data: \"*\"");
    header("location:index.php");
    exit();
}
