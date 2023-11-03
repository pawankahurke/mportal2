<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/vendors/csrf-magic.php';
csrf_check_custom();
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-dbConnect.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-logAudit.php';

include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/include/dashboardSiteFunction.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-crmdetls.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-mail.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-util.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-user.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-db.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/include/common_functions.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-dashboard.php';



// condition with basename needs for check "its a root call file?"
if (url::issetInRequest('function') && basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    $function = url::requestToText('function');

    if (function_exists($function)) {
        $function();
    } else {
        die("function do not exists");
    }
}

/**
 * Used in login form in managment portal dashboard and also used as API endpoint in visualisationservice
 * @see https://gitlab.nanoheal.com/sourcecode/visualisationservice/-/blob/master/packages/api-graphql/src/services/mportalApi.service.ts
 */
function validateUserDetails()
{
    if (isset($_SERVER["HTTP_AUTHORIZATION"])) {
        $auth = $_SERVER["HTTP_AUTHORIZATION"];
        $auth_array = explode(" ", $auth);
        $un_pw = explode(":", base64_decode($auth_array[1]));
        $un = $un_pw[0];
        $pw = $un_pw[1];
    }

    $emailid = $un; //url::issetInPost('email') ? strtolower(url::postToText('email')): '';
    $passwrd = $pw; //url::issetInPost('password') ? url::postToText('password') : '';
    $timeZone = url::issetInPost('timezone') ? url::postToText('timezone') : '';
    $otp = url::postToText('opt_code');

    $authentication = CheckAuthentication(NanoDB::connect(), $emailid, $passwrd, '', $timeZone, '', $otp);

    echo json_encode($authentication);

    exit;
}

function prepareAuthResponse($response, $userData)
{
    $authExpiresIn = 60 * 60;
    $jwt = JWT::getJWT(['id' => $userData['userid']], getenv('APP_SECRET_KEY'), $authExpiresIn);
    setcookie('Authorization', $jwt, time() + $authExpiresIn, '/', "", false, true);
    return array_merge($response, ['token' => $jwt]);
}

/**
 * Used in login form in managment portal dashboard and also used as API endpoint in visualisationservice
 * @see https://gitlab.nanoheal.com/sourcecode/visualisationservice/-/blob/master/packages/api-graphql/src/services/mportalApi.service.ts
 */
function CheckAuthentication($db, $username, $pwd, $type, $timeZone, $signInType, $otp)
{
    global $base_url;

    $userVal = nhUser::getUserStatus_PDO($username);
    $userData = nhUser::getUserInformation($username, $db);
    $userstatus = $userVal[0];
    $PasswordStatus = $userVal[2];

    $return = CheckAuthenticationValidate($userstatus, $PasswordStatus);
    if (is_array($return)) {
        return $return;
    }

    $lstmtres = NanoDB::find_one("select userid, username, user_email, password, userType, userKey, login_attempts, firstName, lastName, passwordHistory
    from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ? ", [$username]);

    $bCryptPwd = $lstmtres['password'];
    $passwordHistory = $lstmtres['passwordHistory'];
    $user_Key = $lstmtres['userKey'];
    $login_attempts = (int)$lstmtres['login_attempts'];
    $userType = $lstmtres['userType'];

    // Fix to change md5 Pwds to bCrypt Pwds. if pwd = md5, then password is md5. so create bcrypt password, insert and then go to test password_verify for bcrypt.
    if ($bCryptPwd === md5($pwd)) {
        $bCryptPwd = md5TobCryptPasswordFix($db, $pwd, $passwordHistory, $username);
    }

    if ((password_verify($pwd, $bCryptPwd)) && $login_attempts < nhUser::$max_login_attempts  /*&& !is_null($login_attempts)*/) {
        $loginStatus = 'success';
        login_audit($db, $username, 'login', $timeZone, 'Success');
        $upd = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set loginStatus = '0', login_attempts=0 where user_email = ? ");
        $upd->execute([$username]);
    } else if ($login_attempts >= 5 || is_null($login_attempts)) {
        if (!is_null($login_attempts)) {
            nhUser::sendEmail(
                $username,
                "Your Nanoheal Account Needs Attention",
                file_get_contents('../emails/user-blocked.html'),
                array(
                    "[USERNAME]" => $lstmtres['firstName'] . " " . $lstmtres['lastName'],
                    "[visualisation_api_url]" => getenv("VISUALISATION_SERVICE_API_URL"),
                )
            );
            nhUser::blockUserLogin($username);
            $_POST['sel_userId'] = $lstmtres['userid'];
            $_POST['mailType'] = 9;
            $sel_userId = UTIL_GetString('sel_userId', '');
            $language = UTIL_GetString('language', '');
            // $userDetails = USER_GetUserDetail('', $db, $sel_userId);
            error_log(json_encode($lstmtres), 0);
            $conn = db_connect();
            $username = $lstmtres['username'];
            $toemailId = strtolower($lstmtres['user_email']);
            $userkey = USER_UserKey($conn);
            error_log("userkey - $userkey, ---" . $lstmtres['userid'] . "----", 0);
            USER_UpdateUserKey($conn, $lstmtres['userid'], $userkey);
            $url = $base_url . 'reset-password.php?vid=' . $userkey;
            $fromEmailId = getenv('SMTP_USER_LOGIN');
            $mailType = UTIL_GetInteger('mailType', $_POST['mailType']);

            //send message Attention
            //            $db = NanoDB::connect();
            //            $template = $db->prepare("select * FROM agent.emailTemplate where tempFor = 'newAttention'");
            //            $template->execute();
            //            $templateRes = $template->fetchAll();
            //            $messageTemplate = $templateRes[0]['mailTemplate'];


            $templateRes = NanoDB::find_one("select * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate where tempFor = 'newAttention'");
            $messageTemplate = $templateRes['mailTemplate'];

            global $base_url;
            $base_img_url = getenv('VISUALISATION_SERVICE_API_URL') . '/dashboard-customization/email/';
            $messageTemplate = str_replace('{{host_dir_image}}', $base_img_url, $messageTemplate);
            $messageTemplate = str_replace('{{playsholder}}', $username, $messageTemplate);

            $company_phone = Options::getOption('company_phone');
            $company_phone = $company_phone['value'];
            $company_email = Options::getOption('company_email');
            $company_email = $company_email['value'];
            $company_site = Options::getOption('company_site');
            $company_site = $company_site['value'];
            $company_address = Options::getOption('company_address');
            $company_address = $company_address['value'];
            $messageTemplate = str_replace('{{host_dir_image}}', $base_img_url, $messageTemplate);
            $messageTemplate = str_replace('{{company_phone}}', $company_phone, $messageTemplate);
            $messageTemplate = str_replace('{{company_email}}', $company_email, $messageTemplate);
            $messageTemplate = str_replace('{{company_site}}', $company_site, $messageTemplate);
            $messageTemplate = str_replace('{{font_url}}', $base_url, $messageTemplate);
            $messageTemplate = str_replace('{{company_address}}', $company_address, $messageTemplate);
            $messageTemplate = str_replace('"#"', $base_url, $messageTemplate);

            $arrayPost = array(
                'from' => getenv('SMTP_USER_LOGIN'),
                'to' => $toemailId,
                'subject' => $templateRes['subjectline'],
                'text' => '',
                'html' => $messageTemplate,
                'token' => getenv('APP_SECRET_KEY'),
            );
            $url_message = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
            CURL::sendDataCurl($url_message, $arrayPost);


            USER_SendUserEmails($conn, $username, $toemailId, $fromEmailId, $mailType, $url, $language);
            // print_data($result);
        }
        return array("textMessage" => "Account has been blocked", "msg" => "<label class='help-block' style='color:red;'>Your account has been blocked. <br>Please, check your email.</label>");
    } else {
        $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET login_attempts=? where user_email = ? ;");
        $sql->execute([$login_attempts + 1, $username]);

        login_audit($db, $username, 'login', $timeZone, 'Failed');
        return array("_login_attempts" => $login_attempts, "textMessage" => "Invalid email id or password..", "msg" => "<label class='help-block' style='color:#ec250d;'>Invalid email id or password..</label>");
    }

    if ($loginStatus == 'success' && ($userData['securityType'] == 'none' || $userData['securityType'] == '')) {

        $uptstmt = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users set apipass = ? where user_email = ?");
        $uptstmt->execute([sha1(sha1($pwd, true)), $username]);


        if ($userType != 'Other') {
            $uptstmt = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users set userType = ? where user_email = ?");
            $uptstmt->execute(['Nanoheal', $username]);
            $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Customers where username=? limit 1";
            $pdo = $db->prepare($sql);
            $pdo->execute([$superUserName]);
            $res = $pdo->fetchAll(PDO::FETCH_ASSOC);
            $configstatus = false;
            foreach ($res as $key => $value) {
                $customerName = $value['customer'];
                $customerId = $value['id'];
                $showBrandingVal = $customerName . '_' . $customerId;
                $_SESSION['showBrandingInit'] = $showBrandingVal;
                if (
                    isset($value['confstatus']) && $value['confstatus'] === '1'
                ) {
                    $configstatus = true;
                }
            }
            setDashSession($db, $username);

            if (empty($_SESSION["user"]["site_list"])) {
                $_SESSION['user']['loggedUType'] = 'Other';
            }
            if (empty($_SESSION["user"]["user_sites"])) {
                $_SESSION['user']['loggedUType'] = 'Other';
            }

            return prepareAuthResponse(["msg" => "LOGGED", 'url' => $base_url . "home", 'phpSessionId' => session_id()], $userData);
        } else {
            setDashSession($db, $username);
            if ($_SESSION['user']['newUserCheck'] == 'New User') {
                $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET userStatus='0',userKey=? where user_email=?";
                $pdo = $db->prepare($updSql);
                $resetRes = $pdo->execute([$user_Key, $username]);

                return prepareAuthResponse(["msg" => "LOGGED", 'url' => $base_url . "home"], $userData);
            } else {
                if (empty($_SESSION["user"]["site_list"])) {
                    return prepareAuthResponse(["msg" => "LOGGED", 'url' => $base_url . "homepage"], $userData);
                } else {
                    return prepareAuthResponse(["msg" => "LOGGED", 'url' => $base_url . "home"], $userData);
                }
            }
        }
    }
    if ($loginStatus == 'success' && $userData['securityType'] == 'emailotp' && ($userType == 'Other' || $userType == null)) {
        return CheckAuthenticationOtpCheck($db, $otp, $userData, $username, $pwd);
    }
    if ($loginStatus == 'success' && $userData['securityType'] == 'MFA' && $userData['mfaEnabled'] == '1' && $userType == 'Other') {
        if ($_SESSION['user']['newUserCheck'] == 'New User') {
            $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET userStatus='0',userKey=? where user_email=?";
            $pdo = $db->prepare($updSql);
            $pdo->execute([$user_Key, $username]);
            return prepareAuthResponse(["msg" => "LOGGED", 'url' => $base_url . "home"], $userData);
        } else {
            return prepareAuthResponse(["msg" => "LOGGED", 'url' => $base_url . "homepage"], $userData);
        }
    }
}

function md5TobCryptPasswordFix($db, $pwd, $updatedPwdHist, $username)
{
    $timestamp = time();
    $timestamp = strtotime('+90 day', $timestamp);

    $bcryptPass = password_hash($pwd, PASSWORD_DEFAULT);
    $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET password=?,passwordDate=?,userStatus='1',passwordHistory=?, userKey='' where user_email=?";
    $bindings = array($bcryptPass, $timestamp, $updatedPwdHist, $username);
    $pdo = NanoDB::connect()->prepare($updSql);
    $pdo->execute($bindings);
    return $bcryptPass;
}
function CheckAuthenticationValidate($userstatus, $PasswordStatus)
{
    if ($userstatus == 0) {
        return array("file" => __FILE__, "msg" => "<label class='help-block' style='color:#ec250d;'>Account is locked, Please click on forgot/reset password to unlock your account.</label>");
    }

    if ($PasswordStatus == 0) {
        return array("file" => __FILE__, "msg" => "<label class='help-block' style='color:#ec250d;'>Password has been expired for this login.</label>");
    } else if ($PasswordStatus == 2) {
        return array("file" => __FILE__, "msg" => "<label class='help-block' style='color:#ec250d;'>Login password has not been set for this user</label>");
    }
    if ($userstatus == 3) {
        return array("file" => __FILE__, "msg" => "<label class='help-block' style='color:#ec250d;'>Invalid email id or password...</label>");
    }
    return true;
}
function CheckAuthenticationOtpCheck($db, $otp, $userData, $username, $pwd)
{
    $otpBlockCount = getenv('otpBlockCount') ?: 20;
    if ($otp == '' && $userData['otp_resend_count'] >= $otpBlockCount && $userData['otp_blocked'] != '1') {
        $block_time = time() + 1800;
        $updateSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set otp_blocked='1',otp_blocktime='$block_time' where user_email=?");
        $updateSql->execute([$username]);
        $userData['otp_blocktime'] = $block_time;
        $userData['otp_blocked'] = '1';
    }

    if ($otp == '' && $userData['otp_resend_expiretime'] > time()) {

        $block_time = floor(($userData['otp_resend_expiretime'] - time()));
        return array("msg" => "OPT not sent, wait $block_time sec.", "type" => 1);
    } else if ($otp == '' && $userData['otp_blocked'] != '1') {
        $otpres = generateOtp($username, $otp);
        $_SESSION['user']['pwd'] = $pwd;
        $_SESSION['user']['adminEmail'] = $username;
        $_SESSION['loginCount'] = 1;
        if ($otp == '' && $otpres == 0) {
            // return array("msg" => "<label class='help-block' style='color:red;'>Enter OTP.</label>", "type" => 1);
            return array("msg" => "OPT_SENDED", "type" => 1);
        }
    } else {
        if ($userData['otp_blocked'] == 1 && $userData['otp_blocktime'] > time()) {
            $min = floor(($userData['otp_blocktime'] - time()) / 60);
            return array("msg" => "<label class='help-block' style='color:red;'>Your account has been blocked. Please try after <span id='blocktime'>$min:00</span> minutes</label>", "type" => 1);
        } else if ($userData['otp_blocktime'] < time() && $otp == '') {
            $_SESSION['user']['pwd'] = $pwd;
            $_SESSION['user']['adminEmail'] = $username;
            $_SESSION['loginCount'] = 1;
            unblockAccount($db, $username);
            $otpres = generateOtp($username, $otp);
            if ($otp == '' && $otpres == 0) {
                // return array("msg" => "<label class='help-block' style='color:red;'>Enter OTP.</label>", "type" => 1);
                return array("msg" => "OPT_SENDED", "type" => 1);
            }
        } else {
            $_SESSION['user']['pwd'] = $pwd;
            $_SESSION['user']['adminEmail'] = $username;
            $_SESSION['loginCount'] = 1;
            $otpres = validateOtp($db, $otp, $username);
            if (is_string($otpres) || is_array($otpres)) {
                return $otpres;
            }
        }
    }
}

function setDashSession($db, $useremail)
{
    session_regenerate_id(true);
    $_SESSION["cType"] = "COMMON"; // Types IBM, COMMON
    $_SESSION["cNode"] = "NODEJS";

    $udstmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ?");
    $udstmt->execute([$useremail]);
    $user_details = $udstmt->fetch(PDO::FETCH_ASSOC);
    $role_id = $user_details['role_id'];
    $coreRole = "select id, name from " . $GLOBALS['PREFIX'] . "core.Options where id = ?";
    $corepdo = $db->prepare($coreRole);
    $corepdo->execute([$role_id]);
    $coreRoleName = $corepdo->fetch(PDO::FETCH_ASSOC);
    $_SESSION["user"]["dashboardLogin"] = 1;
    $_SESSION["user"]["sso"] = false;
    $_SESSION["ssologout"] = 0;
    $_SESSION["user"]['newUserCheck'] = $user_details['clogo'];
    $_SESSION["user123432"]["logged_username"] = $user_details['username'];
    $_SESSION["user"]["fname"] = $user_details['firstName'];
    $_SESSION["user"]["adminEmail"] = $user_details['user_email'];
    $_SESSION['user']['username'] = $user_details['username'];
    $_SESSION['user']['customerType'] = 5;
    $_SESSION['user']['cId'] = 0;
    $_SESSION["user"]["cemail"] = $user_details['user_email'];
    $_SESSION['user']['busslevel'] = 'Commercial';
    $_SESSION['user']['userid'] = $user_details['userid'];
    $_SESSION['user']['serverid'] = $user_details['subch_id'];
    $_SESSION['user']['entityid'] = $user_details['entity_id'];
    $_SESSION['user']['parentid'] = $user_details['parent_id'];
    $_SESSION['user']['rolename'] = $coreRoleName['name'];
    $_SESSION['user']['role_id'] = $user_details['role_id'];
    $_SESSION['user']['usertimezone'] = $user_details['timezone'];
    $coreOptionsSql = "select name,value from " . $GLOBALS['PREFIX'] . "core.Options where name in ('advanced_asset','advance_backup','avira_inst','localization','dbUsage')";
    $pdo = $db->prepare($coreOptionsSql);
    $pdo->execute();
    $coreOptionsSqlRes = $pdo->fetchAll(PDO::FETCH_ASSOC);
    $dbUsage = 0;
    $adv_asset_val = 0;
    $adv_backup_val = 0;
    // $avira_inst_val = 0;
    // $redis_enable = 0;
    $localization = 0;
    foreach ($coreOptionsSqlRes as $key => $val) {
        if ($val['name'] == 'advanced_asset') {
            $adv_asset_val = $val['value'];
        } elseif ($val['name'] == 'advance_backup') {
            $adv_backup_val = $val['value'];
            // } elseif ($val['name'] == 'avira_inst') {
            //     $avira_inst_val = $val['value'];
            // } elseif ($val['name'] == 'redis_enable') {
            //     $redis_enable = $val['value'];
        } elseif ($val['name'] == 'localization') {
            $localization = $val['value'];
        } elseif ($val['name'] == 'dbUsage') {
            $dbUsage = $val['value'];
        }
    }
    if ($adv_asset_val == 1) {
        $_SESSION['machineTableName'] = 'MachineLatest';
        $_SESSION['assetTableName'] = 'AssetDataLatestTest';
    } else {
        $_SESSION['machineTableName'] = 'Machine';
        $_SESSION['assetTableName'] = 'AssetDataLatest';
    }
    $_SESSION["user"]["Advance_Backup"] = $adv_backup_val;
    // $_SESSION["user"]["Avira_Inst"] = $aviraEnabled;
    // $_SESSION["user"]["redis"] = $redis_enable;
    $_SESSION["user"]["localization"] = $localization;
    $_SESSION["user"]["usage"] = $dbUsage;
    // $_SESSION["user"]["ptsEnabled"] = $ptsProvModel;
    $agent_sites = nhUser::getAdminSites_PDO($user_details['userid'], $db);

    $userRoles = getUserRolesUsingJson_PDO($role_id, $db);
    $_SESSION["user"]["roleValue"] = $userRoles;
    $sitelist = nhUser::get_sitelist_PDO($agent_sites);
    reset($sitelist);
    $_SESSION['searchValue'] = key($sitelist);
    $_SESSION["user"]["user_sites"] = $agent_sites;
    $_SESSION["user"]["site_list"] = $sitelist;
    $_SESSION['searchType'] = 'Sites';
    $_SESSION['passlevel'] = 'Sites';
    $_SESSION['rparentName'] = $_SESSION['searchValue'];
    $length = 32;
    $_SESSION['token'] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
}


function checkSingleSignOnStat()
{
    $pdo = pdo_connect();

    $domainName = $_SERVER['HTTP_HOST'];
    $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.singlesignon where domain_name = ?");
    $stmt->execute([$domainName]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $response = ['code' => 200, 'msg' => 'SSO_SET', 'data' => $data['sso_type']];
    } else {
        $response = ['code' => 200, 'msg' => 'SSO_NOTSET', 'data' => ''];
    }
    echo json_encode($response);
    die();
}

function validateOtp($db, $otp, $username)
{
    $max_retries = 20;
    $sql = $db->prepare("select otp,otp_expiretime,otp_retry,otp_blocked,otp_blocktime from " . $GLOBALS['PREFIX'] . "core.Users where user_email=?");
    $sql->execute([$username]);
    $sqlres = $sql->fetch();
    $time = time();
    $timeZone = url::issetInPost('timezone') ? url::postToText('timezone') : '';
    if ($sqlres['otp']) {
        $expire = $sqlres['otp_expiretime'];
        $retry = $sqlres['otp_retry'];
        $blocked = $sqlres['otp_blocked'];
        $otpstored = $sqlres['otp'];
        $blocktime = $sqlres['otp_blocktime'];
        // return $expire.'--'.$time.'--'.$retry.'--'.$otp.'--'.$otpstored.'--'.$blocked;exit;

        if ($expire > $time && $retry < $max_retries && $blocked != 1) {

            if ($otp == $otpstored) {
                unblockAccount($db, $username);
                setDashSession($db, $username);
                login_audit($db, $username, 'otp', $timeZone, 'Success');
                return array("msg" => "LOGGED");
            } else {
                $newretry = $retry + 1;
                $whr = '';
                $time1 = time() + 1800;
                // if ($retry == 2) {
                //     $whr = ",otp_blocked=1,otp_blocktime='$time1'";
                // }

                $updateSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set otp_retry= ? $whr where user_email=?");
                $updateSql->execute([$newretry, $username]);

                login_audit($db, $username, 'otp', $timeZone, 'Failed');

                return array("msg" => "<label class='help-block' style='color:red;'>OTP entered is incorrect. Please click on Resend to send fresh OTP</label>", "time" => $expire);
            }
        } else if ($blocked == 1) {
            $min = floor(($res1['otp_blocktime'] - time()) / 60);
            return array("msg" => "<label class='help-block' style='color:red;'>Your account has been blocked. Please try after <span id='blocktime'>$min:00</span> minutes</label>", "type" => 1);
        } else if ($expire < $time) {
            $newretry = $retry + 1;
            $whr = '';
            $time1 = time() + 1800;
            $tm = $max_retries - 1;
            if ($retry >= $tm) {
                $whr = ",otp_blocked='1',otp_blocktime='$time1'";
            }

            $updateSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set otp_retry= ? $whr where user_email=?");
            $updateSql->execute([$newretry, $username]);

            return array("msg" => "<label class='help-block' style='color:red;'>Your OTP has expired. Please click on Resend to send fresh OTP</label>");
        }
    }
}
function unblockAccount($db, $username)
{

//    nhRole::dieIfnoRoles(['user', 'edituser']); // roles: user, edituser
    $updateSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set otp_blocktime=0,otp_blocked='0',otp_retry=0,otp_resend_count=0,otp_resend_expiretime=0 where user_email=?");
    $updateSql->execute([$username]);
}


function generateOtp($emailId, $otp)
{
  ;
//    nhRole::dieIfnoRoles(['user', 'edituser']); // roles: user, edituser
    $db = pdo_connect();

    $pdo = $db->prepare("Select mfaEnabled, otp_resend_count, securityType from " . $GLOBALS['PREFIX'] . "core.Users where user_email=?");
    $pdo->execute([$emailId]);
    $sqlRes = $pdo->fetch(PDO::FETCH_ASSOC);
//    if ($sqlRes['mfaEnabled']) {
//        if ($sqlRes['mfaEnabled'] == 1 || $sqlRes['mfaEnabled'] == '') {
          if ($sqlRes['securityType'] == 'emailotp' ) {
            $otp = rand(100000, 999999);
            $otpTimeMinutes = getenv('otpTimeMinutes') ?: 30;
            $otpTime = $otpTimeMinutes * 60;
            $time = time() + $otpTime;
            global $base_url;
//            $body = file_get_contents('../emails/opt-pwd.html');
//            $body = str_replace('[OTP]', $otp, $body);
//            $body = str_replace('[visualisation_api_url]', getenv("VISUALISATION_SERVICE_API_URL"), $body);

            $db = NanoDB::connect();
            $template = $db->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate where tempFor = 'newTimePassword'");
            $template->execute();
            $templateRes = $template->fetchAll();
            $messageTemplate = $templateRes[0]['mailTemplate'];
            $base_img_url = getenv('VISUALISATION_SERVICE_API_URL').'/dashboard-customization/email/';
            $company_phone = Options::getOption('company_phone');
            $company_phone = $company_phone['value'];
            $company_email = Options::getOption('company_email');
            $company_email = $company_email['value'];
            $company_site = Options::getOption('company_site');
            $company_site = $company_site['value'];
            $company_address = Options::getOption('company_address');
            $company_address = $company_address['value'];

            $messageTemplate = str_replace('{{host_dir_image}}', $base_img_url, $messageTemplate);
            $messageTemplate = str_replace('{{company_phone}}', $company_phone, $messageTemplate);
            $messageTemplate = str_replace('{{company_email}}', $company_email, $messageTemplate);
            $messageTemplate = str_replace('{{company_site}}', $company_site, $messageTemplate);
            $messageTemplate = str_replace('{{company_address}}', $company_address, $messageTemplate);
            $messageTemplate = str_replace('{{font_url}}', $base_url, $messageTemplate);
            $messageTemplate = str_replace('{{playsholder}}', $emailId, $messageTemplate);
            $messageTemplate = str_replace('089067', $otp, $messageTemplate);
            $otpTimeMessage = "OTP is only valid for ".$otpTimeMinutes." minutes";
            $messageTemplate = str_replace('OTP is only valid for 30 minutes', $otpTimeMessage, $messageTemplate);

            $resend_counter = $sqlRes['otp_resend_count'] + 1;
            $resend_expire = time() + 60;

          //set params for send
          $arrayPost = array(
            'from' => getenv('SMTP_USER_LOGIN'),
            'to' => $emailId,
            'subject' => $templateRes[0]['subjectline'],
            'text' => '',
            'html' => $messageTemplate,
            'token' => getenv('APP_SECRET_KEY'),
          );

          $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
          CURL::sendDataCurl($url, $arrayPost);

//            nhUser::sendEmail(
//                $emailId,
//                "Nanoheal - Login OTP",
//                file_get_contents('../emails/opt-pwd.html'),
//                array(
//                    "[OTP]" => $otp,
//                    "[visualisation_api_url]" => getenv("VISUALISATION_SERVICE_API_URL"),
//                )
//            );
            $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set otp=?, otp_expiretime=?, otp_resend_count=?, otp_resend_expiretime=? where user_email=?");
            $sql->execute([$otp, $time, $resend_counter, $resend_expire, $emailId]);

            return 0;
        } else {
            return 1;
        }
}


function processSingleSignOn()
{
    $pdo = pdo_connect();
    global $base_url;
    global $ssosamlapiurl;
    $domainName = $_SERVER['HTTP_HOST'];
    $ssoType = url::requestToStringAz09('sso_type');

    if (!isset($_SESSION['nonce'])) {
        $_SESSION['nonce'] = md5(time()) . md5(rand()) . md5(uniqid());
    }
    $gtstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.singlesignon where domain_name = ? and sso_type = ?");
    $gtstmt->execute([$domainName, $ssoType]);
    $gtdata = $gtstmt->fetch(PDO::FETCH_ASSOC);
    if ($gtdata) {

        if ($ssoType == 'OAUTH') {
            //scope parser
            $scope = $gtdata['scope'];
            $scopeDelimiter = "";
            $scopeData = explode(',', $scope);
            if (safe_count($scopeData) > 1) {
                $scopeDelimiter = ',';
            } else {
                $scopeData = explode(" ", $scope);
                $scopeDelimiter = " ";
            }

            $reqData['authorizeUrl'] = $gtdata['authorize_url'];
            $reqData['accessUrl'] = $gtdata['access_url'];
            $reqData['oauthVersion'] = $gtdata['oauth_version'];
            $reqData['key'] = $gtdata['client_id'];
            $reqData['nonce'] = $_SESSION['nonce'];
            $reqData['secret'] = $gtdata['client_secret'];
            $reqData['scope'] = array_values($scopeData);
            $reqData['scopeDelimiter'] = $scopeDelimiter;
            $reqData['baseRedirectUrl'] = $base_url . 'signinCheck.php';
            $reqData['resourceUrl'] = $gtdata['resource_url'];

            $resp = SSO::getCurlResponse('POST', '/api/oauth/login/provider/details', $reqData);
            $resp['reurl'] = $ssosamlapiurl;
          logs::log('IGOR_DOMAIN', $resp);
        } else if ($ssoType == 'SAML') {
            $reqData['idpMeta'] = $gtdata['idp_metadata'];
            $reqData['spEntityId'] = $gtdata['sp_entity_id'];
            $reqData['nonce'] = $_SESSION['nonce'];
            $reqData['acsUrl'] = $gtdata['acs_url'];
            $reqData['baseRedirectUrl'] = $base_url . 'signinCheck.php';

            $resp = SSO::getCurlResponse("POST", '/api/saml/login/context', $reqData);
        }

        echo json_encode($resp);
    }
}
