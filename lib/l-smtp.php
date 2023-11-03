<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-mail.php';
include_once 'class.phpmailer.php';
include_once '../lib/l-setTimeZone.php';


nhRole::dieIfnoRoles(['cofigsmtp']); // roles: cofigsmtp

//Replace $routes['post'] with if else
if (url::postToText('function') === 'SMTP_add_Config') { // roles: cofigsmtp
    SMTP_add_Config();
} else if (url::postToText('function') === 'SMTP_send_Mail') { // roles: cofigsmtp
    SMTP_send_Mail();
} else if (url::postToText('function') === 'SMTP_get_Details') { // roles: cofigsmtp
    SMTP_get_Details();
} else if (url::postToText('function') === 'SMTP_edit_Config') { // roles: cofigsmtp
    SMTP_edit_Config();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'SMTP_getTable_Details') { // roles: cofigsmtp
    SMTP_getTable_Details();
}




function SMTP_getTable_Details()
{

    $db = pdo_connect();
    $recordList = array();


    $sql = $db->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "install.mailConfig");
    $sql->execute();
    $sqlRes = $sql->fetchAll();

    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $key => $value) {
            $id = $value['id'];
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $time = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['createdtime'], "m/d/Y h:i A");
                $modifiedtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['lastmodified'], "m/d/Y h:i A");
            } else {
                $time = date("m/d/Y h:i A", $value['createdtime']);
                $modifiedtime = date("m/d/Y h:i A", $value['lastmodified']);
            }
            $name = '<p class="ellipsis" id="detailaudit" title="' . strip_tags($value['name']) . '">' . strip_tags($value['name']) . '</p>';
            $time = '<p class="ellipsis" title="' . $time . '">' . $time . '</p>';
            $modifiedtime = '<p class="ellipsis" title="' . $modifiedtime . '">' . $modifiedtime . '</p>';

            $recordList[] = array($name, $time, $modifiedtime, $id);
        }
        $auditRes = create_auditLog('SMTP', 'View', 'Success');
    }
    echo json_encode($recordList);
}


function SMTP_add_Config()
{

    $res = checkModulePrivilege('addsmtp', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    $db = pdo_connect();
    $name = url::requestToText('name');
    $host = url::requestToText('host');
    $port = url::requestToText('port');
    $username = url::requestToText('username');
    $pwd = url::requestToText('pwd');
    $security = url::requestToText('security');
    $time = time();
    $from = url::requestToText('from');
    $user = $_SESSION['user']['logged_username'];


    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "install.mailConfig");
    $sql->execute();
    $sqlres = $sql->fetchAll();

    if (safe_count($sqlres) == 0) {
        $sql1 = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "install.mailConfig (name,host,port,username,password,security,createdtime,fromemail,createduser) VALUES (?,?,?,?,?,?,?,?,?)");
        $sql1->execute([$name, $host, $port, $username, $pwd, $security, $time, $from, $user]);
        $sqlRes = $db->lastInsertId();
    } else {
        $sql1 =  $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "install.mailConfig set name=?,host=?,port=?,username=?,password=?,security=?,fromemail=?,lastmodified=?,modifieduser=?");
        $sql1->execute([$name, $host, $port, $username, $pwd, $security, $from, $time, $user]);
        $sqlRes = $sql1->rowCount();
    }


    if ($sqlRes) {
        $auditRes = create_auditLog('SMTP', 'Create', 'Success', $_REQUEST);
        echo "success";
    } else {
        $auditRes = create_auditLog('SMTP', 'Create', 'Failed', $_REQUEST);
        echo "fail";
    }
}

function SMTP_get_Details()
{

    $db = pdo_connect();
    $id = url::requestToText('id');

    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "install.mailConfig where id=?");
    $sql->execute([$id]);
    $sqlres = $sql->fetch();

    $data = array();
    if (safe_count($sqlres) > 0) {
        $data = array(
            "status" => "success", "name" => $sqlres['name'], "host" => $sqlres['host'], "port" => $sqlres['port'],
            "username" => $sqlres['username'], "pwd" => $sqlres['password'], "from" => $sqlres['fromemail'], "security" => $sqlres['security']
        );
    } else {
        $data = array("status" => "fail");
    }
    echo json_encode($data);
}

function SMTP_edit_Config()
{

    $db = pdo_connect();
    $name = url::requestToText('name');
    $host = url::requestToText('host');
    $port = url::requestToText('port');
    $username = url::requestToText('username');
    $pwd = url::requestToText('pwd');
    $security = url::requestToText('security');
    $time = time();
    $from = url::requestToText('from');
    $user = $_SESSION['user']['logged_username'];



    $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "install.mailConfig set name=?,host=?,port=?,username=?,password=?,
                security=?,lastmodified=?,fromemail=?,modifieduser=?");
    $sql->execute([$name, $host, $port, $username, $pwd, $security, $time, $from, $user]);
    $sqlRes = $sql->rowCount();

    if ($sqlRes) {
        $auditRes = create_auditLog('SMTP', 'Modification', 'Success', $_REQUEST);
        echo "success";
    } else {
        $auditRes = create_auditLog('SMTP', 'Modification', 'Failed', $_REQUEST);
        echo "fail";
    }
}

function SMTP_send_Mail()
{
    $res = checkModulePrivilege('testsmtp', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }



    $to = url::postToText('name');
    $from = 'noreply@nanoheal.com';
    $fromName = 'Nanoheal';
    $subject = 'SMTP test mail';
    $toName = explode('@', $to)[0];
    $mailContent = 'Dear ' . $toName . '<br/><br/>This is a test mail. <br/><br/> Thanks<br/>Nanoheal';
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $to,
      'subject' => $subject,
      'text' =>'',
      'html' => $mailContent,
      'token' => getenv('APP_SECRET_KEY'),
    );

    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";

    if (CURL::sendDataCurl($url, $arrayPost)) {
        echo 'success';
    } else {
        echo 'Failed';
    }
}
