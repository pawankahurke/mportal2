<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/config.php';
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-rocketChat.php';
include_once 'l-mail.php';
include_once 'class.phpmailer.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/include/common_functions.php';

function USER_Users($key, $db, $ch_id)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        db_change($GLOBALS['PREFIX'] . "core", $db);
        $user_sql = "select userid,user_email, username, firstName from " . $GLOBALS['PREFIX'] . "core.Users C where C.ch_id='$ch_id' and (username != 'hfn' and username != 'admin')";
        $user_res = find_many($user_sql, $db);
        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_SitesUsers($key, $db, $site, $whereClause)
{
    $key = DASH_ValidateKey($key);

    $ch_id = $_SESSION["user"]["cId"];

    $level = url::requestToAny('type');
    if ($key) {
        db_change($GLOBALS['PREFIX'] . "core", $db);
        if ($level == 'all') {
            $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users";
        } else {
            $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users U WHERE ch_id='$ch_id'";
        }
        $user_res = find_many($user_sql, $db);

        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_GetSiteSql($key, $db, $ctype, $ch_id, $site, $whereClause)
{
    $user_sql = '';
    $sitelist = '';
    if (is_array($site)) {
        $sitelist = '';
        foreach ($site as $value) {
            $sitelist .= "'" . $value . "',";
        }
        $sitelist = rtrim($sitelist, ',');
    } else {
        $sitelist = "'" . $site . "'";
    }

    if ($ctype == 0 || $ctype == '0') {
        $user_sql = "SELECT U.* FROM " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "core.Customers C WHERE U.username=C.username AND C.customer IN ($sitelist) $whereClause ";
    } else if ($ctype == 1 || $ctype == '1') {
        $res_ch_ids = USER_GetNextLevelEntity($key, $db, "entityId", $ch_id);
        $user_sql = "SELECT U.* FROM " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "core.Customers C WHERE U.ch_id in
                            ($res_ch_ids,$ch_id) AND U.username = C.username AND C.customer IN ($sitelist) $whereClause  ";
    } else if ($ctype == 1 || $ctype == '2') {
        $res_ch_ids = USER_GetNextLevelEntity($key, $db, "channelId", $ch_id);
        $user_sql = "SELECT U.* FROM " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "core.Customers C WHERE U.ch_id in
                            ($res_ch_ids,$ch_id) AND U.username = C.username AND C.customer IN ($sitelist) $whereClause  ";
    } else if ($ctype == 1 || $ctype == '3') {
        $res_ch_ids = USER_GetNextLevelEntity($key, $db, "subchannelId", $ch_id);
        $user_sql = "SELECT U.* FROM " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "core.Customers C WHERE U.ch_id in
                            ($res_ch_ids,$ch_id) AND U.username = C.username AND C.customer IN ($sitelist) $whereClause  ";
    } else {
        $user_sql = "SELECT U.* FROM " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "core.Customers C WHERE U.ch_id in
                            ($ch_id) AND U.username = C.username AND C.customer IN ($sitelist) $whereClause  ";
    }
    return $user_sql;
}

function event_Items($key, $pdo, $whereClause, $itemtype)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        if ($whereClause == "") {
            $whereClause = " GROUP BY  E.name";
        }
        $user_sql = "select * from " . $GLOBALS['PREFIX'] . "dashboard.EventItems E where itemtype = ? $whereClause ";
        $stmt = $pdo->prepare($user_sql);
        $stmt->execute([$itemtype]);
        $user_res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($user_res)) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        $msg = "Your key has been expired";
        print_data($msg);
    }
}

function GetEventitemsSearchid_new($pdo)
{
    $stmt = $pdo->prepare("select id, name from " . $GLOBALS['PREFIX'] . "event.SavedSearches order by name");
    $stmt->execute();
    $user_res = $stmt->fetchAll();
    if (safe_count($user_res) > 0) {
        return $user_res;
    } else {
        return array();
    }
}

function USER_GetAllUsers($key, $db, $ch_id, $whereClause)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $selectedType = $_SESSION['searchType'];
        $selectedItem = $_SESSION['searchValue'];

        db_change($GLOBALS['PREFIX'] . "core", $db);
        $ctype = $_SESSION["user"]["customerType"];

        if ($selectedItem == 'All' && $selectedType == "Sites") {
            if ($ctype == 0 || $ctype == '0') {
                $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users $whereClause";
            } else if ($ctype == 1 || $ctype == '1') {
                $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE (username != 'hfn' and username != 'admin') $whereClause";
            } else {
                $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE ch_id = '$ch_id' and (username != 'hfn' and username != 'admin') $whereClause";
            }
        } else if ($selectedItem != 'All' && $selectedType == "Sites") {
            $user = $_SESSION['user']['username'];
            $site = $selectedItem;
            $user_sql = "select U.* from " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "agent.customerOrder C where U.ch_id=C.compId AND C.siteName "
                . "= '$site' group by U.userid $whereClause";
        }
        $user_res = find_many($user_sql, $db);
        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_GetEntityUsers($key, $db, $ch_id)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $wh = '';
        $status = url::requestToAny('status');
        if ($status != '') {
            if ($status == 1 || $status == '1') {
                $wh = ' and userStatus = "1" and password != ""';
            } else if ($status == 0 || $status == '0') {
                $wh = ' and userStatus = "0" and password != ""';
            } else if ($status == 2 || $status == '2') {
                $wh = ' and userStatus = "1" and password = ""';
            }
        }
        db_change($GLOBALS['PREFIX'] . "core", $db);
        $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE ch_id = '$ch_id' and (username != 'hfn' and username != 'admin') $wh";
        $user_res = find_many($user_sql, $db);
        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_GetUserDetail($key, $pdo, $userid)
{
    $pdo = pdo_connect();
    $key = DASH_ValidateKey($key);

    if ($key) {
        $user_sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users C where C.userid=? limit 1");
        $user_sql->execute([$userid]);
        $user_res = $user_sql->fetch(PDO::FETCH_ASSOC);
        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_AddUser($key, $db, $user_dtls, $randomPass = '')
{
    $cksum = strip_tags($user_dtls["cksum"]);
    $name = strip_tags($user_dtls["userName"]);
    $lastName = strip_tags($user_dtls["lastname"]);
    $userName = strip_tags($user_dtls["username"] . $user_dtls["lastname"]);
    $userKey = strip_tags($user_dtls["resetid"]);
    $eid = strip_tags($user_dtls["eid"]);
    $userEmail = strip_tags($user_dtls["userEmail"]);
    $userName = preg_replace("/[^a-zA-Z]+/", "", $userName);
    $userType = 1;
    $userid = (int) $user_dtls["userid"];
    $roleName = strip_tags($user_dtls["agentRoleName"]);
    $server = $_SERVER['HTTP_HOST'];
    $timeZone = strip_tags($user_dtls["timezone"]);

    $loggedUserDtls = USER_GetUserDetail($key, $db, $userid);
    $entityId = $loggedUserDtls["entity_id"];
    $channelId = $loggedUserDtls["channel_id"];
    $customerId = $loggedUserDtls["customer_id"];
    $subchannelId = $loggedUserDtls["subch_id"];

    $roleId = $user_dtls["userrole"];
    $sectype = $user_dtls["securityOpt"];
    $mfa = $sectype == '' ? '0' : '1';
    $mfa = $sectype == 'MFA' ? '1' : '0';
    $mfa = $sectype == 'none' ? '0' : '1';

    $priv_admin = 0;
    if ($roleName == 'AdminRole') {
        $priv_admin = 1;
    }

    $entityIdVal = ($entityId + 1);

    try {

        $user_exist_sql = $db->prepare("select userid,username from " . $GLOBALS['PREFIX'] . "core.Users where username=? or user_email=? limit 1");
        $user_exist_sql->execute([$userName, $userEmail]);
        $user_exist_res = $user_exist_sql->fetch(PDO::FETCH_ASSOC);

        if ($user_exist_res) {
            return "DUPLICATE";
        } else {
          if ($randomPass){
            $newUserPassword = password_hash($randomPass, PASSWORD_DEFAULT);
          }else{
            $newUserPassword = '';
          }

          $user_sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Users
            (
              ch_id,entity_id,
              channel_id,subch_id,
              customer_id,
              role_id,
              original_role_id,
              username,
              firstName,
              lastName,
               password,
              user_email,
              user_phone_no,
              user_priv,
               notify_mail,
               report_mail,
               priv_admin,
               priv_notify,
               priv_report,
               priv_areport,
               priv_search,
               priv_aquery,
               priv_downloads,
               priv_updates,
               priv_config,
               priv_asset,
               priv_debug,
               priv_restrict,
               priv_provis,
               priv_audit,
               priv_csrv,
               filtersites,
               logo_file,
               logo_x,
               logo_y,
               footer_left,
               footer_right,
               revusers,
               cksum,
               asset_report_sender,
               disable_cache,
               event_notify_sender,
               event_report_sender,
               jpeg_quality,
               meter_report_sender,
               rept_css,
              userKey,
               parent_id,
              securityType,
              mfaEnabled,
              timezone
              ) VALUES (
                ?,?,?,?,?,?,?,?,?,?,?,?,?,? ,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
              )");
            $user_res = $user_sql->execute([
                $eid,
                $entityIdVal,
                $channelId,
                $subchannelId,
                $customerId,
                $roleId,
                $roleId,
                $userName,
                $name,
                $lastName,
                $newUserPassword,
                $userEmail,
                '',
                $userType,
                '',
                '',
                $priv_admin,
                1,
                1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 1, 0, 0, 0, '', '',
                '', '', '', 0, $cksum, '', 0, '', '', 95, '', '', $userKey,
                $userid,
                $sectype,
                $mfa,
                $timeZone,
            ]);
            $insertId = $db->lastInsertId();

            create_auditLog('User', 'Create', 'Success', $user_dtls);
            return $insertId;
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        create_auditLog('User', 'Create', 'Failed', $user_dtls);
    }
}

function USER_Role_Name($role_id, $pdo)
{

    try {

        $role_sql = $pdo->prepare("SELECT assignedRole,displayName FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE assignedRole=? LIMIT 1");
        $role_sql->execute([$role_id]);
        $role_res = $role_sql->fetch(PDO::FETCH_ASSOC);

        if ($role_res && safe_count($role_res) > 0) {
            return $role_res['displayName'];
        } else {
            return "-";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_Role_Id($roleStatus, $db)
{
    try {
        $role_sql = "SELECT assignedRole,displayName FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE statusVal='$roleStatus' LIMIT 1";
        $role_res = find_one($role_sql, $db);
        if (safe_count($role_res) > 0) {
            return $role_res['assignedRole'];
        } else {
            return "";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_Entity_Role_Name($ch_id, $db)
{
    try {
        $role_sql = "SELECT eid,ctype FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid='$ch_id'";
        $role_res = find_one($role_sql, $db);
        if (safe_count($role_res) > 0) {
            return $role_res['ctype'];
        } else {
            return "6";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_Status($userStatus, $pwd)
{
    try {
        $status = '';
        if ($userStatus == '0' && $pwd != '') {
            $status = 'Disabled';
        } else if ($userStatus == '1' && $pwd != '') {
            $status = 'Active';
        } else if ($userStatus == '1' && $pwd == '') {
            $status = 'In Active';
        } else if ($userStatus == '0' && $pwd == '') {
            $status = 'In Active';
        }
        return $status;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_RoleNameList($db)
{
    try {
        $role_sql = "SELECT id,name,value,type FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE type=10 AND name IN ('user_superadmin','user_admin','user_engineer','user_operator')";
        $role_res = find_many($role_sql, $db);
        if (safe_count($role_res) > 0) {
            return $role_res;
        } else {
            return array();
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_InsertSite($db, $username, $sitename, $lastName)
{
    try {
        $userName = $username . $lastName;
        $userName = preg_replace("/[^a-zA-Z]+/", "", $userName);

        $site_exist_sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username = '" . $userName . " ' AND customer = '$sitename' LIMIT 1";
        $site_exist_res = find_one($site_exist_sql, $db);
        if (safe_count($site_exist_res) > 0) {
            return true;
        } else {
            $ins_customer = "insert into " . $GLOBALS['PREFIX'] . "core.Customers set username = '" . $userName . " ', customer = '" . $sitename . "', sitefilter = '0', owner = '0'";
            $cust_result = redcommand($ins_customer, $db);
            return true;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_InsertCheckSum($pdo, $username, $chksum)
{
    try {

        $cksum_exist_sql = $pdo->prepare("SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users_cksum WHERE username =? AND level=? AND cksum=? LIMIT 1");
        $cksum_exist_sql->execute([$username, 1, $chksum]);
        $cksum_exist_res = $cksum_exist_sql->fetchAll();

        if (safe_count($cksum_exist_res) > 0) {
            return true;
        } else {

            $sql_usrck = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users_cksum (username,level,cksum) values (?,?,?)");
            $sql_usrck->execute([$username, 1, $chksum]);

            return true;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_GetSiteName($db, $custOrdId)
{
    try {
        $select_site = "select siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder where id in ($custOrdId) and siteName != ''";
        $res_site = find_one($select_site, $db);
        if (safe_count($res_site) > 0) {
            return $res_site;
        } else {
            return 0;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_GetSiteWithCompId($pdo, $custOrdId)
{
    $sites = [];
    try {
        $select_site = $pdo->prepare("select siteName from " . $GLOBALS['PREFIX'] . "agent.customerOrder where compId in (?) and siteName != ?");
        $select_site->execute([$custOrdId, '']);
        $res_site = $select_site->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res_site) > 0) {
            foreach ($res_site as $key => $val) {
                $sites[] = $val['siteName'];
            }
        } else {
            return 0;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }

    return $sites;
}

function USER_GetSiteWithUsername($pdo, $username)
{
    $sites = [];

    try {
        $select_site = $pdo->prepare("SELECT customer FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username = ? GROUP BY customer");
        $select_site->execute([$username]);
        $res_site = $select_site->fetchAll(PDO::FETCH_ASSOC);

        if (safe_count($res_site) > 0) {
            foreach ($res_site as $key => $val) {
                $sites[] = $val['customer'];
            }
        } else {
            return 0;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }

    return $sites;
}

function User_SendEmail($db, $userName, $userEmail, $passid, $mailType, $enid, $language)
{

    global $base_url;
    $resetLink = $base_url . 'reset-password.php?vid=' . $passid;
    db_change($GLOBALS['PREFIX'] . "agent", $db);

    $key = '';
    $fromEmail = getenv('SMTP_USER_LOGIN');
    if ($language == "undefined" || $language == '') {
        $language = "en";
    }

    $cntry = 'USA';
    $select_template = "select * from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype='$mailType' and country='$cntry'  and language='$language' limit 1";
    $res_template = find_one($select_template, $db);

    $subject = $res_template['subjectline'];
    $message = $res_template['mailTemplate'];

    $NHimage = $base_url . 'vendors/images/20161103171845_nanoheal_logo.png';
    $NHFinalimage = $base_url . 'vendors/images/20161027170825_nanoheal_logo_final.png';
    $Picture1 = $base_url . 'vendors/images/20161103171453_Picture1.png';
    $facebookImg = $base_url . 'vendors/images/set13-social-facebook-gray.png';
    $twitterImg = $base_url . 'vendors/images/set13-social-twitter-gray.png';
    $forgotpassword = $base_url . 'forgot-password.php';

    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('NANOHEAL_FINAL', $NHFinalimage, $message);
    $message = str_replace('PICTURE1', $Picture1, $message);
    $message = str_replace('FACEBOOK_SOCIAL', $facebookImg, $message);
    $message = str_replace('PASSURL', $resetLink, $message);
    $message = str_replace('TWITTER_SOCIAL', $twitterImg, $message);
    $message = str_replace('FORGOTPASSWORD', $forgotpassword, $message);

    if (!send_mail($userEmail, $subject, $message, $fromEmail)) {
        return 0;
    } else {
        return 1;
    }
}

function USER_Rights($key, $db, $userid)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $usr_sql = "SELECT ch_id,entity_id,channel_id,subch_id,customer_id,user_priv FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = '$userid'";
        $usr_res = find_one($usr_sql, $db);
        if (safe_count($usr_res) > 0) {
            return $usr_res;
        } else {
            return '';
        }
    } else {
        echo "Your key is expired";
    }
}

function USER_UserRoleWithCtype($key, $db, $ctype)
{

    global $db;

    $key = DASH_ValidateKey($key);
    if ($key) {
        $roleId = 0;
        try {
            if ($ctype == "2" || $ctype == 2) {
                $sql_core = "select assignedRole as id from " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE statusVal = 0 limit 1";
            } else if ($ctype == "5" || $ctype == 5) {
                $sql_core = "select assignedRole as id from " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE statusVal = 0 limit 1";
            } else if ($ctype == "4" || $ctype == 4) {
                $sql_core = "select id,name from " . $GLOBALS['PREFIX'] . "core.Options where name='user_admin' limit 1";
            }

            $res_core = find_one($sql_core, $db);
            if (safe_count($res_core) > 0) {
                $roleId = $res_core['id'];
            } else {
                $roleId = 0;
            }
            return $roleId;
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    } else {
        echo "Your key is expired";
    }
}

function USER_DownloadId($key, $db)
{

    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $downloadId = USER_PasswordId('');

            $sql_Coust = "select id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where downloadId='$downloadId'";
            $res_Coust = find_one($sql_Coust, $db);
            $count = safe_count($res_Coust);
            if ($count > 0) {
                return USER_DownloadId('', $db);
            }
        } else {
            echo "Your key is expired";
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_PasswordId($key)
{

    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $character_set_array = array();
            $character_set_array[] = array('count' => 40, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789');
            $temp_array = array();
            foreach ($character_set_array as $character_set) {
                for ($i = 0; $i < $character_set['count']; $i++) {
                    $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
                }
            }
            shuffle($temp_array);
            $randomNo = implode('', $temp_array);
            return $randomNo;
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_IsLoggedUser($key, $userid)
{
    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $loggedUserId = $_SESSION["user"]["userid"];
            $result = getChildDetails($loggedUserId, "userid");
            if (in_array($userid, $result)) {
                $result = "Match found";
                return 1;
            } else {
                $result = "Match not found";
                return 0;
            }
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_DeleteUser($key, $pdo, $userid)
{
    try {
        $key = DASH_ValidateKey($key);

        if ($key) {
            $userid = (int) $userid;
            $query = $pdo->prepare("select firstName,username,user_email FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid=? limit 1");
            $query->execute([$userid]);
            $result = $query->fetch(PDO::FETCH_ASSOC);

            $del_sql = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = ?");
            $del_sql->execute([$userid]);
            $del_res = $del_sql->rowCount();

            if ($del_res) {
                create_auditLog('User', 'Deletion', 'Success', $result);

                USER_DeletionMail($pdo, $result['username'], $result['user_email']);
                return true;
            } else {
                create_auditLog('User', 'Deletion', 'Failed', $result);
                return false;
            }
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_DisableUser($key, $db, $userid)
{
    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $dis_sql = "SELECT userstatus, password FROM " . $GLOBALS['PREFIX'] . "core.Users where userid = '$userid' LIMIT 1";
            $dis_res = find_one($dis_sql, $db);
            $currentStatus = $dis_res['userstatus'];
            $currentPwd = $dis_res['password'];
            if ($currentStatus == 0 && $currentPwd != '') {
                return 2;
            } else if ($currentStatus == 1 && $currentPwd == '') {
                return 3;
            } else {
                $del_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET userstatus = '0' WHERE userid in ($userid)";
                $del_res = redcommand($del_sql, $db);
                if ($del_res) {
                    return 1;
                } else {
                    return 0;
                }
            }
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_EnableUser($key, $db, $userid)
{
    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $dis_sql = "SELECT userstatus, password FROM " . $GLOBALS['PREFIX'] . "core.Users where userid = '$userid' LIMIT 1";
            $dis_res = find_one($dis_sql, $db);
            $currentStatus = $dis_res['userstatus'];
            $currentPwd = $dis_res['password'];
            if ($currentStatus == 1 && $currentPwd != '') {
                return 2;
            } else if ($currentStatus == 1 && $currentPwd == '') {
                return 3;
            } else {
                $del_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET userstatus = '1' WHERE userid in ($userid)";
                $del_res = redcommand($del_sql, $db);
                if ($del_res) {
                    return 1;
                } else {
                    return 0;
                }
            }
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_DeleteSite($key, $pdo, $userid)
{
    try {
        $key = DASH_ValidateKey($key);
        if ($key) {

            $del_sql = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username in (SELECT username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid =?)");
            $del_sql->execute([$userid]);
            $del_res = $del_sql->rowCount();

            if ($del_res) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_IsExist($key, $pdo, $email)
{
    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $pdo = NanoDB::connect();
            $sql = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE user_email = ? LIMIT 1");
            $sql->execute([$email]);
            $res = $sql->fetch(PDO::FETCH_ASSOC);

            if (safe_count($res) > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            echo "Your key is expired";
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_SendMail($username, $toemailId, $fromEmailId, $message, $subject)
{
    $headers = "";
    $headers .= "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";

    if (mail($toemailId, $subject, $message, $headers)) {
        return 1;
    } else {
        return 0;
    }
}

function USER_EditUser($key, $db, $user_dtls)
{
    $userid = $user_dtls["selUid"];
    $name = $user_dtls["userName"];
    $lastName = $user_dtls["lastname"];
    $ch_id = $user_dtls["ch_id"];
    $wh = '';
    if ($ch_id != '') {
        $wh = " ch_id = '$ch_id', ";
    }

    try {
        $update_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users set $wh firstName = '$name', lastName = '$lastName' WHERE userid = '$userid'";
        $update_res = redcommand($update_sql, $db);
        return $update_res;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function USER_ModifyUser($key, $db, $ch_id, $userid, $firstname, $lastname)
{
    $wh = '';
    if ($ch_id != '') {
        $wh = " ch_id = '$ch_id', ";
    }

    try {
        $update_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users set $wh firstName = '$firstname', lastName = '$lastname' WHERE userid = '$userid'";
        $update_res = redcommand($update_sql, $db);
        if ($update_res) {
            return array("status" => "success", "message" => "User updated successfully");
        } else {
            return array("status" => "failed", "message" => "Some error occurred while updating user details");
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function USER_EditNewUser($key, $pdo, $ch_id, $userid, $name, $lastName, $role_id, $Site_name, $sectype, $timezone)
{
    try {
        $query = "select firstName,user_email,username,role_id,lastName FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = ? limit 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([safe_addslashes($userid)]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $siteName = explode(',', $Site_name);
        $userName = $result['username'];

        $stmt1 = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username = ?");
        $stmt1->execute([safe_addslashes($userName)]);
        $siteList = "";
        $stmt2 = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Customers(username,customer) VALUES (?,?)");
        foreach ($siteName as $key => $val) {
            $site = UTIL_GetTrimmedGroupName($val);
            $siteList .= "$site, ";
            $stmt2->execute([$userName, $val]);
        }
        $siteList = rtrim($siteList, ',');
        $mfa = $sectype == 'none' ? '0' : '1';
        $mfa = $sectype == '' ? '0' : '1';
        $mfa = $sectype == 'MFA' ? '1' : '0';
        $update_sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users set firstName = ?, lastName = ?, role_id = ?,mfaEnabled=?,securityType=?,timezone=? WHERE userid = ?";
        $stmt3 = $pdo->prepare($update_sql);
        $update_res = $stmt3->execute([safe_addslashes($name), safe_addslashes($lastName), safe_addslashes($role_id), $mfa, $sectype, $timezone, $userid]);
        $user_dtls = array("name" => $name, "lastName" => $lastName, "roleId" => $role_id, "mfa" => $mfa, "SecType" => $sectype, "userId" => $userid);
        create_auditLog('User', 'Modification', 'Success', $user_dtls);

        USER_SendRoleChangeEmail($pdo, $result['username'], $result['user_email'], $result['firstName'], $result['lastName'], $result['role_id'], $name, $lastName, $role_id, $siteList);

        return $update_res;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $user_dtls = array("name" => $name, "lastName" => $lastName, "roleId" => $role_id, "mfa" => $mfa, "SecType" => $sectype, "userId" => $userid);
        create_auditLog('User', 'Modification', 'Failed', $user_dtls);
    }
}

function USER_GetLoggedUserSite($key, $pdo, $userid)
{
    try {

        $sql = $pdo->prepare("select C.customer, C.username from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username=U.username and U.userid=?");
        $sql->execute([$userid]);
        $res = $sql->fetchAll();
        return $res;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function USER_GetNextLevelEntity($key, $db, $columnName, $eid)
{
    try {
        $sql = "select GROUP_CONCAT(eid) as eid from " . $GLOBALS['PREFIX'] . "agent.channel where $columnName = $eid AND (ctype = 2 OR ctype = 5) LIMIT 1";
        $res = find_one($sql, $db);
        return $res['eid'];
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function USER_GetUserEmailDetails($key, $db, $useremail)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $user_sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users C WHERE C.user_email='$useremail' LIMIT 1";
        $user_res = find_one($user_sql, $db);
        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_AllUsersForEntity($key, $db, $chId, $wh)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $user_sql = "select userid, ch_id, username from " . $GLOBALS['PREFIX'] . "core.Users C where C.ch_id='$chId' $wh";
        $user_res = find_one($user_sql, $db);
        if (safe_count($user_res) > 0) {
            return $user_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function USER_SendUserEmails($db, $username, $toemailId, $fromEmailId, $mailType, $url, $language)
{
    global $base_url;
    //get body massage
    $db = NanoDB::connect();
    $template = $db->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate where tempFor = 'newResetPassword'");
    $template->execute();
    $templateRes = $template->fetchAll();
    $messageTemplate = $templateRes[0]['mailTemplate'];
    $base_img_url = getenv('VISUALISATION_SERVICE_API_URL') . '/dashboard-customization/email/';
    $resetLink = $url;
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
    //  $resultGetParams = USER_SendPasswordResetEmail($db, $username, $toemailId, $fromEmailId, $mailType, $url, $language);
    //set params for send
    $arrayPost = array(
        'from' => 'support@nanoheal.com',
        'to' => $toemailId,
        'subject' => $templateRes[0]['subjectline'],
        'text' => '',
        'html' => $messageTemplate,
        'token' => getenv('APP_SECRET_KEY'),
    );
//      $url = "https://lsf0li.nanoheal.work/visualization/api/mailer/sendmassage";
//      $url = 'http://localhost:5000/visualization/api/mailer/sendmassage';
    $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
    $result = CURL::sendDataCurl($url, $arrayPost);
    return $result;


    //    switch ($mailType) {
    //        case 10:
    ////            $result = USER_SendPasswordResetEmail($db, $username, $toemailId, $fromEmailId, $mailType, $url, $language);
    //            $resultGetParams = USER_SendPasswordResetEmail($db, $username, $toemailId, $fromEmailId, $mailType, $url, $language);
    //        $result = CURL::sendDataCurl($url, $arrayPost);
    //            break;
    //        case 9:
    ////            $result = USER_SendPasswordResetEmail($db, $username, $toemailId, $fromEmailId, $mailType, $url, $language);
    //        $result = CURL::sendDataCurl($url, $arrayPost);
    //            break;
    //        default:
    //            break;
    //    }

}

//function USER_SendPasswordResetEmail($db, $uname, $toemailId, $fromEmailId, $mailType, $resetLink, $language)
//{
//    global $base_url;
//    global $supportMail;
//    global $mailCustName;
//    global $customerContact;
//
//    if ($language == "undefined" || $language == '') {
//        $language = "en";
//    }
//    $select_template = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype='$mailType' and language = '$language' LIMIT 1";
//    $res_template = find_one($select_template, $db);
//    $message = $res_template['mailTemplate'];
//    $subject = str_replace('Nanoheal', $mailCustName, $res_template['subjectline']);
//
//    $fromEmail = ($supportMail != '') ? $supportMail : "noreply@nanoheal.com";
//    $customerPhDisp = ($customerContact != '') ? $customerContact : '1 (855) 436-4621';
//
//    $DashboardLogo = $base_url . '/assets/img/logo-' . $mailCustName . '.png';
//    $NHimage = $base_url . 'vendors/images/20161103171845_nanoheal_logo.png';
//    $NHFinalimage = $base_url . 'vendors/images/20161027170825_nanoheal_logo_final.png';
//    $Picture1 = $base_url . 'vendors/images/20161103171453_Picture1.png';
//    $facebookImg = $base_url . 'vendors/images/set13-social-facebook-gray.png';
//    $twitterImg = $base_url . 'vendors/images/set13-social-twitter-gray.png';
//    $forgotpassword = $base_url . 'forgot-password.php';
//
//    $message = str_replace('DASHBOARDLOGO', $DashboardLogo, $message);
//    $message = str_replace('DASHBOARDNAME', $mailCustName, $message);
//    $message = str_replace('CUSTOMERCONTACTNO', $customerPhDisp, $message);
//    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
//    $message = str_replace('NANANOHEAL_FINAL', $NHFinalimage, $message);
//    $message = str_replace('PICTURE1', $Picture1, $message);
//    $message = str_replace('FACEBOOK_SOCIAL', $facebookImg, $message);
//    $message = str_replace('PASSURL', $resetLink, $message);
//    $message = str_replace('TWITTER_SOCIAL', $twitterImg, $message);
//    $message = str_replace('FORGOTPASSWORD', $forgotpassword, $message);
//
//    $fromName = 'Nanoheal';
//    $toName = explode('@', $toemailId)[0];
//    //send message with CURL::sendDataCurl
//    if ($mailResponse == 2) {
//        return 1;
//    } else {
//        return 0;
//    }
//}

function USER_SendPasswordResetEmail($db, $uname, $toemailId, $fromEmailId, $mailType, $resetLink, $language)
{
    global $base_url;
    global $supportMail;
    global $mailCustName;
    global $customerContact;


    $base_img_url = getenv('VISUALISATION_SERVICE_API_URL') . '/dashboard-customization/';

    if ($language == "undefined" || $language == '') {
        $language = "en";
    }
    $select_template = "SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype='$mailType' and language = '$language' LIMIT 1";
    $res_template = find_one($select_template, $db);
    $message = $res_template['mailTemplate'];
    $subject = str_replace('Nanoheal', $mailCustName, $res_template['subjectline']);

    $fromEmail = ($supportMail != '') ? $supportMail : "noreply@nanoheal.com";
    $customerPhDisp = ($customerContact != '') ? $customerContact : '1 (855) 436-4621';

    //  $DashboardLogo = $base_url . '/assets/img/logo-' . $mailCustName . '.png';
    //  $NHimage = $base_url . 'vendors/images/20161103171845_nanoheal_logo.png';
    //  $NHFinalimage = $base_url . 'vendors/images/20161027170825_nanoheal_logo_final.png';
    //  $Picture1 = $base_url . 'vendors/images/20161103171453_Picture1.png';
    //  $facebookImg = $base_url . 'vendors/images/set13-social-facebook-gray.png';
    //  $twitterImg = $base_url . 'vendors/images/set13-social-twitter-gray.png';
    $DashboardLogo = $base_img_url . $mailCustName . '.png';
    $NHimage = $base_img_url . '20161103171845_nanoheal_logo.png';
    $NHFinalimage = $base_img_url . '20161027170825_nanoheal_logo_final.png';
    $Picture1 = $base_img_url . '20161103171453_Picture1.png';
    $facebookImg = $base_img_url . 'set13-social-facebook-gray.png';
    $twitterImg = $base_img_url . 'set13-social-twitter-gray.png';
    $forgotpassword = $base_url . 'forgot-password.php';

    $message = str_replace('DASHBOARDLOGO', $DashboardLogo, $message);
    $message = str_replace('DASHBOARDNAME', $mailCustName, $message);
    $message = str_replace('CUSTOMERCONTACTNO', $customerPhDisp, $message);
    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('NANANOHEAL_FINAL', $NHFinalimage, $message);
    $message = str_replace('PICTURE1', $Picture1, $message);
    $message = str_replace('FACEBOOK_SOCIAL', $facebookImg, $message);
    $message = str_replace('PASSURL', $resetLink, $message);
    $message = str_replace('TWITTER_SOCIAL', $twitterImg, $message);
    $message = str_replace('FORGOTPASSWORD', $forgotpassword, $message);

    $fromName = 'Nanoheal';
    $toName = explode('@', $toemailId)[0];


    return [
        'message' => $message,
        'toEmail' => $toemailId,
        'subject' => $subject
    ];
}

function USER_PasswordKey()
{

    try {

        $character_set_array = array();
        $character_set_array[] = array('count' => 6, 'characters' => '0123456789');
        $character_set_array[] = array('count' => 2, 'characters' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklkmnopqrstuvwxyz');
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

function USER_UserKey($db)
{
    try {
        $userkey = USER_PasswordKey();
        $sql = "SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userkey= ? LIMIT 1";

        $res = NanoDB::find_many($sql, [$userkey]);
        if (safe_count($res) > 0) {
            return USER_PasswordKey($db);
        }
        return $userkey;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_UpdateUserKey($db, $userid, $userkey)
{

    try {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET userkey = '$userkey' WHERE userid = $userid";
        $res = redcommand($sql, $db);
        return $res;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_GetAllRoles($db)
{
    $sql = "SELECT assignedRole,displayName FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE global=1";
    $pdo = $db->prepare($sql);
    $pdo->execute();
    $res = $pdo->fetchAll(PDO::FETCH_ASSOC);
    return $res;
}

function USER_GetAdminRole($db)
{
    $sql = "SELECT assignedRole FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE displayName='AdminRole'";
    $pdo = $db->prepare($sql);
    $pdo->execute();
    $res = $pdo->fetch(PDO::FETCH_ASSOC);
    return $res;
}

function USER_GetSelectedUserSites($pdo, $userid)
{
    $sql = $pdo->prepare("SELECT customer FROM " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U WHERE C.username=U.username AND U.userid=?");
    $sql->execute([$userid]);
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($res) > 0) {
        return $res;
    } else {
        return [];
    }
    return $res;
}

function USER_SendRoleChangeEmail($pdo, $username, $toemailId, $oldFname, $oldLname, $oldRole, $fname, $lname, $role, $site)
{

    global $base_url;
    global $configServer;
    $string = "";
    if (strcasecmp($oldFname, $fname) != '0') {
        $string .= 'First name is changed from ' . $oldFname . ' to ' . $fname . '<br />';
    }

    if (strcasecmp($oldLname, $lname) != '0') {
        $string .= 'Last name is changed from ' . $oldLname . ' to ' . $lname . '<br />';
    }

    if ($oldRole != $role) {
        $stmt = $pdo->prepare("select assignedRole,displayName from " . $GLOBALS['PREFIX'] . "core.RoleMapping where assignedRole in(?,?)");
        $stmt->execute([$oldRole, $role]);
        $sqlRes = $stmt->fetchAll();

        foreach ($sqlRes as $val) {
            $roleId[$val['assignedRole']] = $val['displayName'];
        }
        $string .= "Role is changed from $roleId[$oldRole] to $roleId[$role]<br />";
    }

    $string .= "Following sites are assigned $site";
    $fromEmail = getenv('SMTP_USER_LOGIN');

    $stmt = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype='12' and language = 'en' LIMIT 1");
    $stmt->execute();
    $res_template = $stmt->fetch();
    $message = $res_template['mailTemplate'];
    $subject = $res_template['subjectline'];
    $NHimage = $base_url . '/vendors/images/20161027170825_nanoheal_logo_final.png';

    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('USER_NAME', $username, $message);
    $message = str_replace('SERVER_NAME', $configServer, $message);
    $message = str_replace('STRING_MESSAGE', $string, $message);

    if (!send_mail($toemailId, $subject, $message, $fromEmail)) {
        return 0;
    } else {
        return 1;
    }
}

function USER_DeletionMail($pdo, $username, $toemailId)
{

    global $base_url;
    global $configServer;

    $string = "You're access to server " . $configServer . " is removed<br />";
    $fromEmail = getenv('SMTP_USER_LOGIN');

    $stmt = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.emailTemplate WHERE ctype='13' and language = 'en' LIMIT 1");
    $stmt->execute();
    $res_template = $stmt->fetch(PDO::FETCH_ASSOC);

    $message = $res_template['mailTemplate'];
    $subject = $res_template['subjectline'];
    $NHimage = $base_url . '/vendors/images/20161027170825_nanoheal_logo_final.png';

    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('USER_NAME', $username, $message);
    $message = str_replace('STRING_MESSAGE', $string, $message);

    if (!send_mail($toemailId, $subject, $message, $fromEmail)) {
        return 0;
    } else {
        return 1;
    }
}

function USER_SitesUsers_PDO($key, $pdo, $site, $whereClause, $type = '')
{
    // echo (__FILE__ . ":" . __FUNCTION__ . ":" . __LINE__ . "\n");
    $returnData = array();

    $level = url::postToText('type');
    $loggedUserId = $_SESSION["user"]["userid"];

    // var_dump($childUsers);
    $user_sql = null;
    $user_sql2 = null;

    if ($level === 'all') {
        $user_sql = NanoDB::connect()->prepare("SELECT userid, firstName, lastName, user_email, role_id, userStatus, password, userType FROM " . $GLOBALS['PREFIX'] . "core.Users" . $whereClause);
        $user_sql->execute();

        $user_sql2 = NanoDB::connect()->prepare("SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM " . $GLOBALS['PREFIX'] . "core.Users");
        $user_sql2->execute();
    } else {
        // @warn: can have a bug.
        $childUsers = getChildDetails($loggedUserId, "userid");
        $arr = $childUsers;
        $in = str_repeat('?,', safe_count($arr) - 1) . '?';
        if ($type == 'export') {
            // $sql = "SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM ".$GLOBALS['PREFIX']."core.Users WHERE userid IN ($in) $whereClause";
            $user_sql = NanoDB::connect()->prepare("SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($in)");
            $user_sql->execute($arr);

            // $sql2 = "SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM ".$GLOBALS['PREFIX']."core.Users WHERE userid IN ($in)";
            $user_sql2 = NanoDB::connect()->prepare("SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($in)");
            $user_sql2->execute($arr);
        } else {
            // $sql = "SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM ".$GLOBALS['PREFIX']."core.Users WHERE userid IN ($in) $whereClause";
            $user_sql = NanoDB::connect()->prepare("SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($in) $whereClause");
            $user_sql->execute($arr);

            // $sql2 = "SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM ".$GLOBALS['PREFIX']."core.Users WHERE userid IN ($in)";
            $user_sql2 = NanoDB::connect()->prepare("SELECT userid,firstName,lastName,user_email,role_id,userStatus,password,userType FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($in)");
            $user_sql2->execute($arr);
        }
    }
    $user_res = $user_sql->fetchAll(PDO::FETCH_ASSOC);
    $totCount = safe_count($user_sql2->fetchAll(PDO::FETCH_ASSOC));
    if (safe_count($user_res) > 0) {
        $returnData['data'] = $user_res;
        $returnData['totCount'] = $totCount;
        return $returnData;
    } else {
        return array();
    }
}

function USER_DownloadId_PDO($key, $pdo)
{

    try {
        $key = DASH_ValidateKey($key);
        if ($key) {
            $downloadId = USER_PasswordId('');

            $sql_Coust = $pdo->prepare("select id,customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where downloadId=?");
            $sql_Coust->execute([$downloadId]);
            $res_Coust = $sql_Coust->fetchAll();

            $count = safe_count($res_Coust);
            if ($count > 0) {
                return USER_DownloadId_PDO('', $pdo);
            }
        } else {
            echo "Your key is expired";
        }
        return $downloadId;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function USER_InsertSite_PDO($pdo, $username, $sitename, $lastName)
{
    try {
        $userName = $username . $lastName;
        $loggedusername = $_SESSION['user']['logged_username'];
        $userName = preg_replace("/[^a-zA-Z]+/", "", $userName);
        $site_exist_sql = $pdo->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username =? AND customer =? LIMIT 1");
        $site_exist_sql->execute([$userName, $sitename]);
        $site_exist_res = $site_exist_sql->fetchAll();

        $confstatus_sql = $pdo->prepare("SELECT confstatus FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username =? AND customer =? LIMIT 1");
        $confstatus_sql->execute([$loggedusername, $sitename]);
        $confstatus_res = $confstatus_sql->fetch();
        $confstatus = $confstatus_res['confstatus'];

        if (!$confstatus) {
            $confstatus = 0;
        }

        if (safe_count($site_exist_res) > 0) {
            return true;
        } else {
            $params = array_merge([$userName, $sitename, 0, 0, $confstatus]);
            $ins_customer = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username,customer,sitefilter,owner,confstatus) values (?,?,?,?,?)");
            $ins_customer->execute($params);
            return true;
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function User_SendEmail_PDO($pdo, $userName, $userEmail, $passid, $mailType, $enid, $language)
{
    global $base_url;
    global $supportMail;
    global $mailCustName;
    global $customerContact;
    $resetLink = $base_url . 'reset-password.php?vid=' . $passid;
    $key = '';
    $fromEmail = ($supportMail != '') ? $supportMail : "noreply@nanoheal.com";

    if ($language == "undefined" || $language == '') {
        $language = "en";
    }

    $cntry = 'USA';
    $select_template = $pdo->prepare("select subjectline,mailTemplate from " . $GLOBALS['PREFIX'] . "agent.emailTemplate where ctype=? and country=? and language=? limit 1");
    $select_template->execute([$mailType, $cntry, $language]);
    $res_template = $select_template->fetch();

    $subject = str_replace('Nanoheal', $mailCustName, $res_template['subjectline']);
    $message = $res_template['mailTemplate'];
    $DashboardLogo = $base_url . '/assets/img/logo-' . $mailCustName . '.png';
    $NHimage = $base_url . '/vendors/images/20161103171845_nanoheal_logo.png';
    $NHFinalimage = $base_url . '/vendors/images/20161027170825_nanoheal_logo_final.png';
    $Picture1 = $base_url . '/vendors/images/20161103171453_Picture1.png';
    $facebookImg = $base_url . '/vendors/images/set13-social-facebook-gray.png';
    $twitterImg = $base_url . '/vendors/images/set13-social-twitter-gray.png';
    $forgotpassword = $base_url . 'forgot-password.php';
    $customerPhDisp = ($customerContact != '') ? $customerContact : '1 (855) 436-4621';

    $message = str_replace('DASHBOARDLOGO', $DashboardLogo, $message);
    $message = str_replace('DASHBOARDNAME', $mailCustName, $message);
    $message = str_replace('CUSTOMERCONTACTNO', $customerPhDisp, $message);
    $message = str_replace('NANOHEAL_LOGO', $NHimage, $message);
    $message = str_replace('NANOHEAL_FINAL', $NHFinalimage, $message);
    $message = str_replace('PICTURE1', $Picture1, $message);
    $message = str_replace('FACEBOOK_SOCIAL', $facebookImg, $message);
    $message = str_replace('PASSURL', $resetLink, $message);
    $message = str_replace('TWITTER_SOCIAL', $twitterImg, $message);
    $message = str_replace('FORGOTPASSWORD', $forgotpassword, $message);

//    if (getenv('ENV_TYPE') == 'dev') {
//        return ['resetLink' => $resetLink];
//    }

    $fromName = 'Nanoheal';

    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $userEmail,
      'subject' => $subject,
      'text' =>'',
      'html' => $message,
      'token' => getenv('APP_SECRET_KEY'),
    );

    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
    if (CURL::sendDataCurl($url, $arrayPost)) {
        return 1;
    } else {
        return 0;
    }
}

function USER_UserRoleWithCtype_PDO($key, $pdo, $ctype)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $roleId = 0;
        try {
            if ($ctype == "2" || $ctype == 2) {
                $sql_core = $pdo->prepare("select assignedRole as id from " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE statusVal = ? limit 1");
                $sql_core->execute([0]);
            } else if ($ctype == "5" || $ctype == 5) {
                $sql_core = $pdo->prepare("select assignedRole as id from " . $GLOBALS['PREFIX'] . "core.RoleMapping WHERE statusVal = ? limit 1");
                $sql_core->execute([0]);
            } else if ($ctype == "4" || $ctype == 4) {
                $sql_core = $pdo->prepare("select id,name from " . $GLOBALS['PREFIX'] . "core.Options where name=? limit 1");
                $sql_core->execute(['user_admin']);
            }

            $res_core = $sql_core->fetchAll(PDO::FETCH_ASSOC);
            if (safe_count($res_core) > 0) {
                $roleId = $res_core['id'];
            } else {
                $roleId = 0;
            }
            return $roleId;
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }
    } else {
        echo "Your key is expired";
    }
}

function sendMailViaSMTP($pdo, $emailto, $subject, $mailcontent, $altbody = '')
{

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "install.mailConfig");
    $sql->execute();
    $sqlres = $sql->fetch();

    $host = $sqlres['host'];
    $port = $sqlres['port'];
    $username = $sqlres['username'];
    $password = $sqlres['password'];
    $from = $sqlres['fromemail'];

    //    $smtpAuth = true;
    //    if ($username == '' && $password == '') {
    //        $smtpAuth = false;
    //    }
    //
    //    $mail = new PHPMailer;
    //    $mail->isSMTP();
    //
    //    $mail->SMTPDebug = 0;
    //    $mail->Host = $host;
    //    $mail->Port = $port;
    //    $mail->SMTPAuth = $smtpAuth;
    //    $mail->Username = $username;
    //    $mail->Password = $password;
    //
    //    $mail->setFrom($from, 'No Reply');
    //    $mail->addAddress($emailto, $emailto);
    //    $mail->isHTML(true);
    //    $mail->Subject = $subject;
    //    $mail->msgHTML($mailcontent);
    //    $mail->AltBody = $altbody;

    // send from visualisationService
    $arrayPost = array(
        'from' => getenv('SMTP_USER_LOGIN'),
        'to' => $emailto,
        'subject' => $subject,
        'text' => '',
        'html' => $mailcontent,
        'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";

    //  if (!$mail->send()) {
    if (!CURL::sendDataCurl($url, $arrayPost)) {
        return 0;
    } else {
        return 1;
    }
}
