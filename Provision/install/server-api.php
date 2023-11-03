<?php

include_once 'config.php';
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';

error_reporting(-1);
ini_set('display_errors', 'On');

if (url::issetInRequest('function')) { // roles: user, adduser
    nhRole::dieIfnoRoles(['user', 'adduser']); // roles: user, adduser
    $function = url::requestToAny('function'); // roles: user, adduser
    $function();
}

function createCoreUser()
{
    nhRole::dieIfnoRoles(['user', 'adduser']); // roles: user, adduser

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "core", $db);

    $username = url::requestToAny('username');

    $checkuserSql = "select * from Users where username = '$username'";
    $checkuserRes = find_one($checkuserSql, $db);

    if (safe_count($checkuserRes) > 0) {
        echo 'User already Exists';
    } else {
        echo 'asdfasdf';
    }
}
