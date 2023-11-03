<?php

function Login_resetPasswordLink($db, $user_email)
{
    $resetSql = "select userid,role_id,username,user_email,firstName from " . $GLOBALS['PREFIX'] . "core.Users where (user_email='$user_email' or user_phone_no = '$user_email') limit 1";
    $resetRes = find_one($resetSql, $db);
    $currentTimestamp = time();
    if (safe_count($resetRes) > 0) {

        $passId = Login_downloadId();
        $sql_change = "update " . $GLOBALS['PREFIX'] . "core.Users set userKey='" . $passId . "' where (user_email='$user_email' or user_phone_no = '$user_email')";
        $updRes = redcommand($sql_change, $db);
        $mailStus = Login_resetPasswordMail($resetRes['firstName'], $user_email, $passId, $db);
        if ($mailStus == 1) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 2;
    }
}

function Login_downloadId()
{

    try {

        $character_set_array = array();
        $character_set_array[] = array('count' => 40, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@$%&()__0123456789');
        $temp_array = array();
        foreach ($character_set_array as $character_set) {
            for ($i = 0; $i < $character_set['count']; $i++) {
                $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
            }
        }
        shuffle($temp_array);
        $randomNo = implode('', $temp_array);
        return $randomNo;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function Login_resetPasswordMail($userName, $userEmail, $passid, $db)
{

    global $base_url;
    $resetLink = $base_url . 'reset-password.php?vid=' . $passid;
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $select_template = "select * from emailTemplate where ctype='9' limit 1";
    $res_template = find_one($select_template, $db);

    $subject = "Welcome to Proactive Intelligence!";
    $message = $res_template['mailTemplate'];

    $NHimage = $base_url . '/vendors/images/20130309_RS_017P8278.jpg';
    $HPimage = $base_url . '/vendors/images/hp.gif';
    $ximage = $base_url . '/vendors/images/x.gif';

    $message = str_replace('USERNAME', $userName, $message);
    $message = str_replace('NHIMG', $NHimage, $message);
    $message = str_replace('HPIMG', $HPimage, $message);
    $message = str_replace('XGIF', $ximage, $message);
    $message = str_replace('PASSURL', $resetLink, $message);

    $fromEmailId = 'Proactive Services Support Team <proactiveintelligence@hp.com>';

    $headers .= "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";
//    if (!mail($userEmail, $subject, $message, $headers)) {
    // send from visualisationService
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $userEmail,
      'subject' => $subject,
      'text' =>'',
      'html' => $message,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
  if (!CURL::sendDataCurl($url, $arrayPost)) {
  return 0;
    } else {
        return 1;
    }
}

function Login_resetPassword($db, $resetsession, $pwd)
{

    global $CRMEN;
    db_change($GLOBALS['PREFIX'] . "core", $db);
    $mdPass = md5($pwd);
    $timestamp = time();
    $timestamp1 = strtotime('+90 day', $timestamp);
    $checkPwdSql = "SELECT user_email, password, passwordHistory,username,firstName,lastName, cksum FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userKey='$resetsession' limit 1";
    $checkPwdRes = find_one($checkPwdSql, $db);
    $currentPwd = $checkPwdRes["password"];
    $pwdHistory = $checkPwdRes["passwordHistory"];
    $pwdHistArray = explode(',', $pwdHistory);
    $username = $checkPwdRes['username'];
    $user_email = $checkPwdRes['user_email'];
    $fname = $checkPwdRes['firstName'];
    $lname = $checkPwdRes['lastName'];

    if (safe_count($pwdHistArray) > 4) {
        unset($pwdHistArray[0]);
        $pwdHistory = implode(",", $pwdHistArray);
    }

    if ($currentPwd == $mdPass) {
        return 2;
    } else if (in_array($mdPass, $pwdHistArray)) {
        return 2;
    }

    if ($fname != '') {
        if (preg_match("/$fname/i", $pwd)) {
            return 4;
        }
    }
    if ($lname != '') {
        if ((strlen($lname) > 3) && preg_match("/$lname/i", $pwd)) {
            return 5;
        }
    }
    if ($pwdHistory != "") {
        $updatedPwdHist = $pwdHistory . ',' . $mdPass;
    } else {
        $updatedPwdHist = $mdPass;
    }
    $updSql = "UPDATE Users SET password = '" . $mdPass . "',passwordDate='$timestamp1',userStatus='1',passwordHistory = '$updatedPwdHist' where userKey='$resetsession'";
    $resetRes = redcommand($updSql, $db);
    if ($resetRes) {
        if ($CRMEN == 1) {
            getCRMContactDtl($user_email, $db);
        }
    }
    $cksum = isset($checkPwdRes["cksum"]) ? $checkPwdRes["cksum"] : '0';
    return '1##' . $cksum;
}

function getCRMContactDtl($emailid, $db)
{

    $checkPwdSql = "select emailId,chId,crmAcctType,mauticSegid,mauticId,crmUserId from " . $GLOBALS['PREFIX'] . "agent.contactDetails where emailId='$emailid' order by id desc limit 1";
    $checkPwdRes = find_one($checkPwdSql, $db);
    if (safe_count($checkPwdRes) > 0) {
        $crmCntId = $checkPwdRes['crmUserId'];
        $mauticId = $checkPwdRes['mauticId'];
        if ($crmCntId != '') {
            RSLR_pushUpdateContactCRM($crmCntId, $mauticId);
        }
    }
}

function Login_checkvid($db, $resetSession)
{

    db_change($GLOBALS['PREFIX'] . "core", $db);
    $msg = '';
    $resetSql = "select userid,passwordDate,username,user_email from " . $GLOBALS['PREFIX'] . "core.Users where userKey='$resetSession' limit 1";
    $resetRes = find_one($resetSql, $db);
    $count = safe_count($resetRes);
    if ($count > 0) {

        return 'DONE##' . $resetRes['user_email'];
    } else {
        return 'NOTDONE##' . 'Invalid case';
    }
}
