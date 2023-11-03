<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-authenticate.php';
include_once '../lib/l-crmdetls.php';



if (url::issetInRequest('function')) { // roles: user, edituser
  nhRole::dieIfnoRoles(['user', 'edituser']); // roles: user, edituser
  $function = url::requestToText('function'); // roles: user, edituser
  $function();
}



function login_sendresetPassLink()
{
    $db      = db_connect();

    $user_email  = url::issetInRequest('userid') ? url::requestToAny('userid') : '';
    $returnVal = Login_resetPasswordLink($db, $user_email);
    echo $returnVal;
}


function login_updatePassLink()
{
    $db      = db_connect();

    $userKey  = url::issetInRequest('userKey') ? url::requestToAny('userKey') : '';
    $user_pass  = url::issetInRequest('pwd') ? url::requestToAny('pwd') : '';
    $returnVal = Login_resetPasswordLink($db, $userKey, $user_pass);
    echo $returnVal;
}

function login_checkUserKey()
{
    $db      = db_connect();

    $resetSession  = url::issetInRequest('resetSession') ? url::requestToAny('resetSession') : '';
    $returnVal = Login_checkvid($db, $resetSession);
    echo $returnVal;
}


function login_resetUserPass()
{
    $db      = db_connect();

    $userid  = url::issetInRequest('resetSession') ? url::requestToAny('resetSession') : '';
    $pwd     = url::issetInRequest('password') ? url::requestToAny('password') : '';

    $returnVal = Login_resetPassword($db, $userid, $pwd);
    echo $returnVal;
}
