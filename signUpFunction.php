<?php

/*
 * Name    Date        Details
 * MANIKA  SEP 2021    Initial implementation for signup redirection
 */

// ini_set('display_errors', 'On');
// error_reporting(-1);
include_once "config.php";


if (!nhUser::isAllowSignUp()) {
    die('Sign up is not allowed');
}

include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once 'lib/l-user.php';
include_once 'include/common_functions.php';


if (url::postToText('function') === 'SignupNewUser') {  // has checks
    SignupNewUser();
}

function SignupNewUser()
{
    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed');
    }
    global $dash_tanentId;
    global $dash_deployId;
    global $reportingurl;
    global $dash_client_32;
    global $dash_client_64;
    global $base_url;

    $fname = url::postToText('firstname');
    $lname = url::postToText('lastname');
    $email = trim(strtolower(url::postToText('newEmail')));

    $pdo = NanoDB::connect();

    $timeZone = '';

    if ($email != '') {
        $loginType = '';

        $logininfo = array('email' => $email, 'timezone' => $timeZone, 'authtype' => $loginType);
        $_SESSION["userloginfo"] = $logininfo;
        $uname = preg_replace('/\s+/', '_', $fname);
        $username = $uname . $lname;
        // $username = explode('@', $email)[0];
        $access_token = '-';
        $authid_token = '-';

        $role_id = nhRole::$TYPE_RestrictedRole; // default for ad users
        $pwdExpiryDate = strtotime("+1 year");
        $userKey = uniqid();

        // check if user exists
        $userChkStmt = $pdo->prepare('select userid, username from ' . $GLOBALS['PREFIX'] . 'core.Users where user_email = ?');
        $userChkStmt->execute([$email]);
        $userChkData = $userChkStmt->fetch(PDO::FETCH_ASSOC);

        $domainName = explode('.', $_SERVER["HTTP_HOST"]);
        $domainName = $domainName[0];

        //Check if the tenant id(cId) and Server Id exists
        $siteChkStmt = $pdo->prepare('select serverid, cId from ' . $GLOBALS['PREFIX'] . 'install.Servers where servername = ? and serverid = ? and cId = ?');
        $siteChkStmt->execute([$domainName, $dash_tanentId, $dash_deployId]);
        $siteChkData = $siteChkStmt->fetch(PDO::FETCH_ASSOC);

        if (!$siteChkData) {
            $streamingurl = 'https://' . $_SERVER["HTTP_HOST"] . '/Dashboard';
            $servername = 'https://' . $_SERVER["HTTP_HOST"] . '/Dashboard/api/license/';

            $ins_server_stmt = $pdo->prepare('INSERT INTO ' . $GLOBALS['PREFIX'] . 'install.Servers (servername, installuserid, serverurl,'
                . 'global, notifyemail, reportemail, url, streamingurl, client_32_name, '
                . 'client_64_name, client_android_name, client_mac_name, client_ios_name, '
                . 'client_linux_name, cId) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');

            $params = array_merge([
                $domainName, 1, $servername, 1, '', '', $reportingurl,
                $streamingurl, $dash_client_32, $dash_client_64, '', '', '', '', $dash_deployId
            ]);
            $ins_server_stmt->execute($params);
            $deployID = inserted_id($pdo);
        } else {
            $deployID = $dash_deployId;
        }

        if (!$userChkData) {
            $ins_user_stmt = $pdo->prepare('insert into ' . $GLOBALS['PREFIX'] . 'core.Users (firstName,lastName, '
                . 'username, password, user_email, role_id, parent_id, userStatus, '
                . 'passwordDate, access_token, id_token, userKey, userType, clogo, cId, deployId) '
                . 'values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $ins_user_res = $ins_user_stmt->execute([
                $fname, $lname, $username, '-', $email,
                $role_id, 1, 0, $pwdExpiryDate, $access_token, $authid_token, $userKey,
                'Other', 'New User', $dash_tanentId, $deployID
            ]);

            if ($ins_user_res) {
              $resetId = $userKey;
              $mailType = 10;
              $entityId = ''; //$_SESSION["user"]["entityId"];
              $language = 'en';

              //select template message welcome
              $db = NanoDB::connect();
              $template = $db->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate where tempFor = 'newWelcome'");
              $template->execute();
              $templateRes = $template->fetchAll();
              $messageTemplate = $templateRes[0]['mailTemplate'];
              $base_img_url = getenv('VISUALISATION_SERVICE_API_URL').'/dashboard-customization/email/';
              $messageTemplate = str_replace('{{host_dir_image}}', $base_img_url, $messageTemplate);
              $messageTemplate = str_replace('"#"', $base_url, $messageTemplate);
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
              $messageTemplate = str_replace('{{company_address}}', $company_address, $messageTemplate);
              $messageTemplate = str_replace('{{font_url}}', $base_url, $messageTemplate);


              //send welcome message
              $arrayPost = array(
                  'from' => getenv('SMTP_USER_LOGIN'),
                  'to' => $email,
                  'subject' => $templateRes[0]['subjectline'],
                  'text' =>'',
                  'html' => $messageTemplate,
                  'token' => getenv('APP_SECRET_KEY'),
                );
              $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
              CURL::sendDataCurl($url, $arrayPost);

              //send message reset link
              $template = $db->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate where tempFor = 'newResetPassword'");
              $template->execute();
              $templateRes = $template->fetchAll();
              $messageTemplate = $templateRes[0]['mailTemplate'];
              $resetLink = $base_url . 'reset-password.php?vid=' . $resetId;
              $messageTemplate = str_replace('{{host_dir_image}}', $base_img_url, $messageTemplate);

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
              $messageTemplate = str_replace('{{playsholder}}', $username, $messageTemplate);
              $messageTemplate = str_replace('"#"', $resetLink, $messageTemplate);

              $arrayPost = array(
                'from' => getenv('SMTP_USER_LOGIN'),
                'to' => $email,
                'subject' => $templateRes[0]['subjectline'],
                'text' =>'',
                'html' => $messageTemplate,
                'token' => getenv('APP_SECRET_KEY'),
              );
              CURL::sendDataCurl($url, $arrayPost);

//              $res = User_SendEmail_PDO($pdo, $username, $email, $resetId, $mailType, $entityId, $language);

                if (getenv('ENV_TYPE') == 'dev'){
                  echo "<a href='".$resetLink."' class='user-access-link'>".$resetLink."</a>";
                }else{
//                  echo "User Inserted.Please check the register email to setup a password";
                  echo "success";
                }
            } else {
                echo "Error while inserting user";
            }
        } else {
            echo "User already exists";
        }
    }
}
