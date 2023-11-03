<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/vendors/csrf-magic.php';
csrf_check_custom();
include_once 'dashboardSiteFunction.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-crmdetls.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-dbConnect.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-crmdetls.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-mail.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-login.php';



function CreateLoginInfo($db)
{
    $user_name = url::issetInRequest('username') ? url::requestToAny('username') : '';
    $login_time = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    $sessionid = session_id();

    $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.login_info (username, login_time, sessionid, ip) VALUES (?,?,?,?)");
    $sql->execute([$user_name, $login_time, $sessionid, $ip]);
    $res = $db->lastInsertId();
    return $res;
}

function unsetUserloginfo()
{
}

function encryptuserpwd($pwd)
{
    $encryptedpwd = "";
    try {
        $encrypt_method = "AES-256-CBC";
        $secret_key = 'jfsibm';
        $secret_iv = 'jfsibm@nanoheal.com';

        $key = hash('sha256', $secret_key);

        $iv = substr(hash('sha256', $secret_iv), 0, 16);

        $output = openssl_encrypt($pwd, $encrypt_method, $key, 0, $iv);
        $encryptedpwd = base64_encode($output);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $encryptedpwd;
}



function CheckSSOAuthentication($db, $username, $pwd, $type, $timeZone, $signInType, $otp)
{
    global $base_url;
    global $aviraEnabled;
    global $ptsProvModel;
    // try {

    $userData = nhUser::getUserInformation($username, $db);

    // $userVal = explode("##", $ustatus);
    $lstmt = $db->prepare("select userid, username, user_email, access_token, id_token from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ?");
    $lstmt->execute([$username]);
    $lstmtres = $lstmt->fetch(PDO::FETCH_ASSOC);
    // $err = $lstmt->errorInfo();
    // if ($err) {
    //     logs::log(__FILE__, __LINE__, json_encode($err), 0);
    //     print_r($err);
    //     die();
    // }
    //echo json_encode($lstmtres);
    if (isset($lstmtres['username']) && $lstmtres['username'] != '') {
        $l_rdata = ['status' => 'success', 'result' => $lstmtres['userid']];
    } else {
        $l_rdata = [];
    }
    $response = (object) $l_rdata;

    if ($response->status == "success") {
        $token_id = $lstmtres['id_token'];
        if ($userData['mfaEnabled'] == '1' && $userData['securityType'] == 'emailotp') {
            //            otp generation and validation starts
            if ($otp == '' && $userData['otp_blocked'] != '1') {
                $otpres = generateOtp($username, $otp);
                $_SESSION['user']['pwd'] = $pwd;
                $_SESSION['user']['adminEmail'] = $username;
                $_SESSION['loginCount'] = 1;
                $tokenSession['user']['pwd'] = $pwd;
                $tokenSession['user']['adminEmail'] = $username;
                $tokenSession['loginCount'] = 1;

                if ($otp == '' && $otpres == 0) {
                    return "<label class='help-block' style='color:red;'>Enter OTP.</label>";
                }
            } else {
                if ($userData['otp_blocked'] == 1 && $userData['otp_blocktime'] > time()) {
                    $min = floor(($userData['otp_blocktime'] - time()) / 60);
                    return array("msg" => "<label class='help-block' style='color:red;'>Your account has been blocked. Please try after <span id='blocktime'>$min:00</span> minutes</label>", "type" => 1);
                } else if ($userData['otp_blocktime'] < time() && $otp == '') {
                    $_SESSION['user']['pwd'] = $pwd;
                    $_SESSION['user']['adminEmail'] = $username;
                    $_SESSION['loginCount'] = 1;
                    $tokenSession['user']['pwd'] = $pwd;
                    $tokenSession['user']['adminEmail'] = $username;
                    $tokenSession['loginCount'] = 1;
                    unblockAccount($db, $username);
                    $otpres = generateOtp($username, $otp);
                    if ($otp == '' && $otpres == 0) {
                        return "<label class='help-block' style='color:red;'>Enter OTP.</label>";
                    }
                } else {
                    $_SESSION['user']['pwd'] = $pwd;
                    $_SESSION['user']['adminEmail'] = $username;
                    $_SESSION['loginCount'] = 1;
                    $tokenSession['user']['pwd'] = $pwd;
                    $tokenSession['user']['adminEmail'] = $username;
                    $tokenSession['loginCount'] = 1;
                    $otpres = validateOtp($db, $otp, $username);
                    if (is_string($otpres) || is_array($otpres)) {
                        return $otpres;
                    }
                }
            }
        }

        $pdo = $db->prepare("select loginStatus,userSession from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
        $pdo->execute([$response->result]);
        $loginStatus = $pdo->fetch(PDO::FETCH_ASSOC);

        if ($loginStatus) {
            $logLoginAudit = false;
            $_SESSION['login_success_count'] = (isset($_SESSION['login_success_count']) && is_numeric($_SESSION['login_success_count']) && intval($_SESSION['login_success_count']) == 1) ? 2 : 1;
            $tokenSession['login_success_count'] = (isset($tokenSession['login_success_count']) && is_numeric($tokenSession['login_success_count']) && intval($tokenSession['login_success_count']) == 1) ? 2 : 1;
            $userSessionKey = sha1(uniqid() . time() . rand(9999, 999999)) . uniqid();
            $userSessionKey = substr($userSessionKey, 0, 49);

            if (is_numeric($loginStatus['loginStatus']) && intval($loginStatus['loginStatus']) == 1) {
                //if ($tokenSession['login_success_count'] == 1 && $otp == '') {
                // if ($_SESSION['login_success_count'] == 1 && $otp == '') {
                //     return array("msg" => "<label class='help-block' style='color:#ec250d;'>User is already logged in, login once more to continue</label>");
                // }

                //if ($tokenSession['login_success_count'] > 1) {
                if ($_SESSION['login_success_count'] > 1) {
                    $logLoginAudit = true;
                    unset($tokenSession['login_success_count']);
                    $pdo = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users set userSession='" . $userSessionKey . "' where userid='" . $response->result . "'");
                    $pdo->execute();
                }
            } else {
                $logLoginAudit = true;
                //unset($tokenSession['login_success_count']);
                unset($_SESSION['login_success_count']);
                $pdo = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users set loginStatus='1',userSession='" . $userSessionKey . "' where userid='" . $response->result . "'");
                $pdo->execute();
            }

            if ($logLoginAudit) {
                login_audit($db, $username, '', $timeZone, 'Success');

                // Create audit log for user login event
                create_auditLog('User', 'Login', 'Success');
            }

            $tokenSession['usertokenSession_key'] = $userSessionKey;
        }

        $res_userid = $response->result;
        //$user_details = DashboardAPI("GET", "user/details/" . $res_userid);

        $udstmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
        $udstmt->execute([$res_userid]);
        $udstmtres = $udstmt->fetch(PDO::FETCH_ASSOC);

        $ud_rdata = ['result' => (object) $udstmtres];
        $user_details = (object) $ud_rdata;

        $superUserName = $user_details->result->username;
        $fname = $user_details->result->firstName;
        $admin_id = $user_details->result->userid;
        $admin_email = $user_details->result->user_email;
        $role_id = $user_details->result->role_id;
        $entity_id = $user_details->result->entity_id;
        $parent_id = $user_details->result->parent_id;
        $user_timeZone = $user_details->result->timezone;

        // Get Role Name
        $coreRole = "select id, name from " . $GLOBALS['PREFIX'] . "core.Options where id = ?";
        $corepdo = $db->prepare($coreRole);
        $corepdo->execute([$role_id]);
        $coreRoleName = $corepdo->fetch(PDO::FETCH_ASSOC);

        // Get Elastic namespace
        $nmstmt = $db->prepare('select id, name, value from ' . $GLOBALS['PREFIX'] . 'core.Options where name in (?, ?)');
        $nmstmt->execute(['kibana_namespace', 'elast_config']);
        $optionsData = $nmstmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($optionsData as $key => $value) {
            if ($value['name'] == 'kibana_namespace') {
                $_SESSION['knamespace'] = $value['value'];
                $tokenSession['knamespace'] = $value['value'];
            } else if ($value['name'] == 'elast_config') {
                $_SESSION['knamespace'] = $value['value'];
                $tokenSession['elastconfig'] = $value['value'];
            }
        }

        $_SESSION["user"]["dashboardLogin"] = 1;
        $_SESSION["user"]["sso"] = true;
        $_SESSION["ssologout"] = 1;
        $_SESSION["user"]["mulverify"] = true;
        $_SESSION["user"]["logged_username"] = $superUserName;
        $_SESSION["user"]["fname"] = $fname;
        $_SESSION["user"]["adminEmail"] = $admin_email;
        $_SESSION['user']['username'] = $superUserName;
        $_SESSION['user']['customerType'] = 5; // hard-wired since all are customers
        $_SESSION['user']['cId'] = 0;
        $_SESSION["user"]["cemail"] = $admin_email;
        $_SESSION['user']['busslevel'] = 'Commercial'; // hard-wired since enterprise flow is considered
        $_SESSION['user']['userid'] = $admin_id;
        $_SESSION['user']['serverid'] = $userData['subch_id'];
        $_SESSION['user']['entityid'] = $entity_id;
        $_SESSION['user']['parentid'] = $parent_id;
        $_SESSION['user']['rolename'] = $coreRoleName['name'];
        $_SESSION['user']['role_id'] = $role_id;
        $_SESSION['user']['usertimezone'] = $user_timeZone;
        $tokenSession["user"]["dashboardLogin"] = 1;
        $tokenSession["user"]["sso"] = true;
        $tokenSession["ssologout"] = 1;
        $tokenSession["user"]["mulverify"] = true;
        $tokenSession["user"]["logged_username"] = $superUserName;
        $tokenSession["user"]["fname"] = $fname;
        $tokenSession["user"]["adminEmail"] = $admin_email;
        $tokenSession['user']['username'] = $superUserName;
        $tokenSession['user']['customerType'] = 5; // hard-wired since all are customers
        $tokenSession['user']['cId'] = 0;
        $tokenSession["user"]["cemail"] = $admin_email;
        $tokenSession['user']['busslevel'] = 'Commercial'; // hard-wired since enterprise flow is considered
        $tokenSession['user']['userid'] = $admin_id;
        $tokenSession['user']['serverid'] = $userData['subch_id'];
        $tokenSession['user']['entityid'] = $entity_id;
        $tokenSession['user']['parentid'] = $parent_id;
        $tokenSession['user']['rolename'] = $coreRoleName['name'];
        $tokenSession['user']['role_id'] = $role_id;
        $tokenSession['user']['usertimezone'] = $user_timeZone;
        //echo 'SSO => ' . $tokenSession["user"]["sso"]; die();
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
            }
            if ($val['name'] == 'advance_backup') {
                $adv_backup_val = $val['value'];
            }
            if ($val['name'] == 'avira_inst') {
                $avira_inst_val = $val['value'];
            }
            // if ($val['name'] == 'redis_enable') {
            //     $redis_enable = $val['value'];
            // }
            if ($val['name'] == 'localization') {
                $localization = $val['value'];
            }
            if ($val['name'] == 'dbUsage') {
                $dbUsage = $val['value'];
            }
        }

        if ($adv_asset_val == 1) {
            $_SESSION['machineTableName'] = 'MachineLatest';
            $_SESSION['assetTableName'] = 'AssetDataLatestTest';
            $tokenSession['machineTableName'] = 'MachineLatest';
            $tokenSession['assetTableName'] = 'AssetDataLatestTest';
        } else {
            $_SESSION['machineTableName'] = 'Machine';
            $_SESSION['assetTableName'] = 'AssetDataLatest';
            $tokenSession['machineTableName'] = 'Machine';
            $tokenSession['assetTableName'] = 'AssetDataLatest';
        }

        $_SESSION["user"]["Advance_Backup"] = $adv_backup_val;
        $_SESSION["user"]["Avira_Inst"] = $aviraEnabled;
        // $_SESSION["user"]["redis"] = $redis_enable;
        $_SESSION["user"]["localization"] = $localization;
        $_SESSION["user"]["usage"] = $dbUsage;
        $_SESSION["user"]["ptsEnabled"] = $ptsProvModel;
        $tokenSession["user"]["Advance_Backup"] = $adv_backup_val;
        $tokenSession["user"]["Avira_Inst"] = $aviraEnabled;
        // $tokenSession["user"]["redis"] = $redis_enable;
        $tokenSession["user"]["localization"] = $localization;
        $tokenSession["user"]["usage"] = $dbUsage;
        $tokenSession["user"]["ptsEnabled"] = $ptsProvModel;

        //site list
        $agent_sites = nhUser::getAdminSites_PDO($admin_id, $db);

        //role to session
        $userRoles = getUserRolesUsingJson_PDO($role_id, $db);
        $_SESSION["user"]["roleValue"] = $userRoles;
        $tokenSession["user"]["roleValue"] = $userRoles;

        $sitelist = nhUser::get_sitelist_PDO($agent_sites);
        reset($sitelist); // make sure array pointer is at first element
        $_SESSION['searchValue'] = key($sitelist);
        $_SESSION["user"]["user_sites"] = $agent_sites;
        $_SESSION["user"]["site_list"] = $sitelist;
        $tokenSession['searchValue'] = key($sitelist);
        $tokenSession["user"]["user_sites"] = $agent_sites;
        $tokenSession["user"]["site_list"] = $sitelist;

        $_SESSION['searchType'] = 'Sites';
        $_SESSION['passlevel'] = 'Sites';
        $_SESSION['rparentName'] = $_SESSION['searchValue'];
        $tokenSession['searchType'] = 'Sites';
        $tokenSession['passlevel'] = 'Sites';
        $tokenSession['rparentName'] = $tokenSession['searchValue'];

        $length = 32;
        $_SESSION['token'] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
        //$tokenSession['token'] = substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
        // Create audit log for user login event
        //create_auditLog('User','Login','Success');
        // get token and update the user information
        $tokenData = explode('.', $token_id);
        //print_r($tokenData); die();
        $payloadData = safe_json_decode(base64_decode($tokenData[1]), true);
        // $userPayloadData = json_encode(array_merge($payloadData, $tokenSession));
        $userPayloadData = json_encode($tokenSession);
        $userTokenData = $tokenData[0] . '.' . base64_encode($userPayloadData) . '.' . $tokenData[2];

        $userKey = uniqid();

        $tokenidstmt = $db->prepare('update ' . $GLOBALS['PREFIX'] . 'core.Users set id_token = ?, userKey = ? where user_email = ?');
        $tokenidstmt->execute([$userTokenData, $userKey, $username]);

        //setcookie('x-enc-data', base64_encode($username), time() + (86400 * 30), "/");
        //setcookie('nh-userkey', $userKey, time() + (86400 * 30), "/");

        $stmt = $db->prepare('select * from ' . $GLOBALS['PREFIX'] . 'core.Customers where username=? limit 1');
        $stmt->execute([$superUserName]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($signInType == '') {
            $configstatus = false;
            foreach ($res as $key => $value) {
                $customerName = $value['customer'];
                $customerId = $value['id'];
                $showBrandingVal = $customerName . '_' . $customerId;
                $_SESSION['showBrandingInit'] = $showBrandingVal;
                $tokenSession['showBrandingInit'] = $showBrandingVal;
                if (isset($value['confstatus']) && $value['confstatus'] === '1') {
                    $configstatus = true;
                }
            }
            if ($res) {
                header("location:" . $base_url . "home/index.php");
            } else {
                header("location:" . $base_url . "home/home.php");
            }
        } else {
            header("location:" . $base_url . "signupPage/sliderindex.php");
        }
    } else {
        return array("msg" => "<label class='help-block' style='color:#ec250d;'>Invalid email id or password.</label>");
    }
    // } catch (Exception $exc) {  logs::log(__FILE__, __LINE__, $exc, 0);
    //     logs::log(__FILE__, __LINE__, json_encode($exc), 0);
    //     return $exc;
    // }
}

function create_auditLog($module = null, $action = null, $status = 'Success', $ref = null, $refName = null)
{

    global $logAudit;
    global $auditStore;
    if ($logAudit == 1) {
        if (isset($_SESSION["user"]["userid"]) && isset($_SESSION['user']['username'])) {

            $username = $_SESSION['user']['username'];
            $userid = $_SESSION["user"]["userid"];
            $useremail = $_SESSION["user"]["adminEmail"];
            $url = getCurrentRequestURL();
            $time = time();
            $ip = $_SERVER['REMOTE_ADDR'];
            $browser = getBrowser();
            if ($browser['name'] !== 'Unknown') {
                $agentDet = $browser['name'] . ' - ' . $browser['version'] . ', ' . $browser['platform'];
            } else {
                $agentDet = $browser['userAgent'];
            }
            $userAgent = $agentDet;
            if (isset($ref)) {
                $reference = json_encode($ref);
            } else {
                $reference = null;
            }

            if (isset($refName)) {
                $referName = $refName;
            } else {
                $referName = null;
            }

            if ($auditStore == 1) {
                include_once '../lib/l-db.php';
                $db = pdo_connect();
                $dt = gmdate('Y-m-d H:i:s');
                $auditRes = auditStore_mysql($db, [$module, $action, $username, $userid, $useremail, $url, null, $ip, $userAgent, $status, $reference, $referName, $dt]);
                return $auditRes;
            }
        }
    }
}

function create_withoutsession_auditLog($module, $email, $action = null, $status = 'Success')
{

    global $logAudit;
    global $auditStore;
    if ($logAudit == 1) {
        include_once 'lib/l-db.php';
        $db = pdo_connect();
        $userSql = "SELECT userid, username,firstName,lastName FROM " . $GLOBALS['PREFIX'] . "core.Users where user_email=?";
        $pdo = $db->prepare($userSql);
        $pdo->execute([$email]);
        $userRes = $pdo->fetch(PDO::FETCH_ASSOC);

        $username = $userRes['username'];
        $userid = $userRes['userid'];
        $useremail = $email;
        $url = getCurrentRequestURL();
        $time = time();
        $ip = $_SERVER['REMOTE_ADDR'];
        $browser = getBrowser();
        if ($browser['name'] !== 'Unknown') {
            $agentDet = $browser['name'] . ' - ' . $browser['version'] . ', ' . $browser['platform'];
        } else {
            $agentDet = $browser['userAgent'];
        }
        $userAgent = $agentDet;
        if ($auditStore == 1) {
            $dt = gmdate('Y-m-d H:i:s');
            $auditRes = auditStore_mysql($db, [$module, $action, $username, $userid, $useremail, $url, null, $ip, $userAgent, $status, null, null, $dt]);
            return $auditRes;
        }
    }
}

function auditStore_mysql($db, $logDet = array())
{

    $sqlAudit = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "core.AuditLog (module,action,username,userid,useremail,url,method,ip,agent,status,rawReference,refName,created) values(?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $sqlRes = $sqlAudit->execute($logDet);
    return $sqlRes;
}

function getCurrentRequestURL()
{

    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $url = "https://";
    } else {
        $url = "http://";
    }

    $url .= $_SERVER['HTTP_HOST'];

    $url .= $_SERVER['REQUEST_URI'];

    return $url;
}

function getBrowser()
{
    $u_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version = "";

    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    } elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    } elseif (preg_match('/Firefox/i', $u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    } elseif (preg_match('/Edge/i', $u_agent)) {
        $bname = 'Edge';
        $ub = "Edge";
    } elseif (preg_match('/Chrome/i', $u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    } elseif (preg_match('/Safari/i', $u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    } elseif (preg_match('/Opera/i', $u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    } elseif (preg_match('/Netscape/i', $u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    } elseif (preg_match('/Trident/i', $u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "Internet Explorer";
        if (strpos($u_agent, 'rv:') > 50) {
            $vpos = strpos($u_agent, 'rv:') + 3;
            $version = $u_agent[$vpos] . $u_agent[$vpos + 1];
        }
    }

    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
    }

    $i = safe_count($matches['browser']);
    if ($i != 1) {
        if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
            $version = $matches['version'][0];
        } else {
            $version = $matches['version'][1];
        }
    } else {
        $version = $matches['version'][0];
    }
    if (preg_match('/Trident/i', $u_agent)) {
    }

    if ($version == null || $version == "") {
        $version = "-";
    }

    return array(
        'userAgent' => $u_agent,
        'name' => $bname,
        'version' => $version,
        'platform' => $platform,
        'pattern' => $pattern,
    );
}


function getComanyDtl($cid, $pid, $db)
{
    $sql = $db->prepare("select C.cId,P.pId,P.profileName,P.andProfileName,P.macProfileName,P.iosProfileName,P.lnxProfileName,P.logoName,P.processName,C.companyName,C.customerType,C.customerNo,C.showCustNo,C.showOrdNo,C.addCust addCustomer from " . $GLOBALS['PREFIX'] . "agent.customerMaster C," . $GLOBALS['PREFIX'] . "agent.processMaster P where C.cId = P.cId and P.pId =? and C.cId=? limit 1");
    $sql->execute([$pid, $cid]);
    $resultPro = $sql->fetch();

    return $resultPro;
}

function getChannelDtl($cid, $db)
{
    $sql = $db->prepare("select C.eid,C.entityId,C.channelId,C.subchannelId,C.outsourcedId,C.companyName,C.firstName,C.lastName,C.emailId,C.phoneNo,C.businessLevel,C.ordergen,C.skulist,C.reportserver,C.addcustomer,C.logo,C.ctype,C.customerNo,C.entyHirearchy,C.trialEnabled,C.trialEndDate,C.showTrialBox,P.pId,P.profileName,P.andProfileName,P.macProfileName,P.iosProfileName,P.lnxProfileName,P.logoName,P.processName,P.downloaderPath,C.status from " . $GLOBALS['PREFIX'] . "agent.channel C," . $GLOBALS['PREFIX'] . "agent.processMaster P where C.eid=? and C.eid=P.cId limit 1");
    $sql->execute([$cid]);
    $resultPro = $sql->fetch();

    return $resultPro;
}

function getServicePrDtl($swId, $pid, $db)
{

    $sql = $db->prepare("select P.pId,P.profileName,P.andProfileName,P.macProfileName,P.iosProfileName,P.lnxProfileName,P.logoName,P.processName,C.companyName,C.customerType,C.custmerNo as showCustNo,C.orderNo as showOrdNo,C.licenceCnt,C.payment,C.addCustomer,C.skuList,C.noOfCustomer,C.sessionId from " . $GLOBALS['PREFIX'] . "agent.serviceMaster C,processMaster P where C.sId = P.swId and P.pId =? and C.sId=? limit 1");
    $sql->execute([$pid, $swId]);
    $resultPro = $sql->fetch();
    return $resultPro;
}

function getAdminSites($adminid, $db)
{

    $siteSql = $db->prepare("select C.customer from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username = U.username and U.userid = ?  group by C.customer");
    $siteSql->execute([$adminid]);
    $resultSite = $siteSql->fetchAll();
    $agent_sites = array();
    foreach ($resultSite as $row) {
        $agent_sites[] = "'" . $row['customer'] . "'";
    }
    $sitesCount = safe_count($agent_sites);
    $returnDate = array($sitesCount, $agent_sites);
    return $returnDate;
}

function getUserRolesUsingJson($role_id, $db)
{

    $rolesql = $db->prepare("SELECT name,value,type FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE  id=? and type=10 limit 1");
    $rolesql->execute([$role_id]);
    $role = $rolesql->fetch();
    $roleVal = $role['value'];
    $roleItemsArray = safe_json_decode($roleVal, true);

    $roleItemsArray['user']['roleValue']['services'] = $roleItemsArray['user']['roleValue']['service'];
    $roleList = $roleItemsArray;
    return $roleList;
}

function checkTrialExpired($role_id, $ch_id, $db)
{
    $now = time();

    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = ? LIMIT 1");
    $sql->execute([$ch_id]);
    $res = $sql->fetch();

    $trialEnabled = $res['trialEnabled'];
    $trialStartDate = $res['trialStartDate'];
    $trialEndDate = $res['trialEndDate'];

    if ($trialEnabled == '0' || $trialEnabled == '2' || $trialEnabled == '3') {
        $return = $role_id;
    } else if ($trialEnabled == '1' && ($trialEndDate <= $now)) {

        $rolesql = $db->prepare("SELECT O.id, O.value FROM " . $GLOBALS['PREFIX'] . "core.Options O, " . $GLOBALS['PREFIX'] . "core.RoleMapping R WHERE O.id=R.assignedRole AND R.statusVal = 2");
        $rolesql->execute();
        $role = $rolesql->fetch();
        $return = $role['id'];

        $updateStatusSql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.channel CH SET CH.trialEnabled = '2' WHERE CH.eid = ?");
        $updateStatusSql->execute([$ch_id]);

        $updateRoleSql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users U SET U.role_id = ? WHERE U.ch_id = ?");
        $updateRoleSql->execute([$return, $ch_id]);
    } else {
        $return = $role_id;
    }
    return $return;
}

function getUserStatus($userId, $db)
{

    $umsg = 0;
    $lmsg = 0;
    $pmsg = 0;
    $currentTimestamp = time();
    $agentsql = $db->prepare("select userid,userStatus,loginStatus,passwordDate from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email) = ? or user_phone_no= ?)  limit 1");
    $agentsql->execute(["lower('$userId')", $userId]);
    $resultStatus = $agentsql->fetch();
    if (safe_count($resultStatus) > 0) {
        $userstatus = $resultStatus['userStatus'];
        $loginStatus = $resultStatus['loginStatus'];
        $passwordDate = $resultStatus['passwordDate'];
        if ($userstatus == 0) {
            $umsg = 0;
        } else {
            $umsg = 1;
        }
        if ($loginStatus == 1) {
            $lmsg = 0;
        } else {
            $lmsg = 1;
        }

        $numDays = ($passwordDate - $currentTimestamp) / 24 / 60 / 60;

        if ($numDays <= 0) {
            $pmsg = 0;
        } else {
            $pmsg = 1;
        }
    } else {
        $umsg = 3;
        $lmsg = 3;
        $pmsg = 3;
    }
    return $umsg . '##' . $lmsg . '##' . $pmsg;
}

function updateUserLoginStatus($userSession, $username, $db)
{
    global $CRMEN;
    $unixdate = time();

    $logintime = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set userSession=?,loginStatus='1',loginDate = ? where (user_email=? or user_phone_no = ?)");
    $logintime->execute([$userSession, $unixdate, $username, $username]);
    $res_login = $db->lastInsertId();
    if ($res_login) {
        if ($CRMEN == 1) {
            getCRMContactDtl($username, $unixdate, $db);
        }
    }
}

function getCRMContactDtl($emailid, $lastlogindt, $db)
{

    $checkPwdSql = $db->prepare("select emailId,chId,crmAcctType,mauticSegid,mauticId,crmUserId,crmLeadId from " . $GLOBALS['PREFIX'] . "agent.contactDetails where emailId=? order by id desc limit 1");
    $checkPwdSql->execute([$emailid]);
    $checkPwdRes = $checkPwdSql->fetch();
    if (safe_count($checkPwdRes) > 0) {
        $crmCntId = $checkPwdRes['crmUserId'];
        $mauticId = $checkPwdRes['mauticId'];
        $crmLeadId = $checkPwdRes['crmLeadId'];
        if ($crmCntId != '') {
            RSLR_updateLeadLastlogin($crmLeadId, $lastlogindt, $mauticId);
        }
    }
}

function userInvalidCheck($username, $db)
{

    $msg = '';
    $userSql = $db->prepare("select id,userStatus,loginStatus from " . $GLOBALS['PREFIX'] . "agent.Agent where email = ?");
    $userSql->execute([$username]);
    $resuserSql = $userSql->fetch(PDO::FETCH_ASSOC);

    if (safe_count($resuserSql) <= 0) {
        $msg = "<lable class='help-block'>Invalid Username.</lable>";
    } else {

        $userSql = $db->prepare("update Agent set passwordThreshold = passwordThreshold + 1 where email = ? limit 1");
        $userSql->execute([$username]);
        $res = $db->lastInsertId();

        $getthresold = $db->prepare("select passwordThreshold from " . $GLOBALS['PREFIX'] . "agent.Agent where  email = ? limit 1");
        $getthresold->execute([$username]);
        $rest = $getthresold->fetch(PDO::FETCH_ASSOC);
        $thresoldcount = $rest['passwordThreshold'];
        $msg = "";
        if ($thresoldcount == 1) {

            $msg = "<lable class='help-block'>Invalid Password, remaining 4 attempts.</lable>";
        } else if ($thresoldcount == 4) {

            $msg = "<lable class='help-block'>Invalid Password, Last attempt, Account will be Locked.</lable>";
        } else if ($thresoldcount == 5) {
            $lockuser = $db->prepare("update Agent set passwordThreshold = 0,userStatus = 0 where  email = ? limit 1");
            $lockuser->execute([$username]);

            sendResetPasswordLink($db, $username);
            $msg = "<lable class='help-block'>Account is locked and an email has been sent your email id to unlock your account.</lable>";
        }
    }
    return $msg;
}

function overAllVersionUpdate($userSites, $db)
{

    $siteString = $userSites;
    $in = str_repeat('?,', safe_count($siteString) - 1) . '?';
    $sql = $db->prepare("select lastversion from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename in ($in) group by lastversion order by lastversion desc");
    $sql->execute([$siteString]);
    $resultUpdate = $sql->fetchAll();

    $versions = "";
    foreach ($resultUpdate as $row) {

        if ($row['lastversion'] != '') {
            $lastVersion = $row['lastversion'];
            $params = array_merge([$lastVersion], $siteString);
            $sql = $db->prepare("select count(*) as count from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where lastversion = ? and sitename in($in)");
            $sql->execute($params);
            $result = $sql->fetch(PDO::FETCH_ASSOC);

            $versions .= "['" . $row['lastversion'] . "'," . $result['count'] . "],";
        }
    }
    return "[" . rtrim($versions, ',') . "]";
}

function sendResetPasswordLink($db, $user_email)
{
    $user_email = strtolower($user_email);
    $resetSql = "select userid,role_id,username,user_email from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=? or user_phone_no = ?) limit 1";
    $bindings = array($user_email, $user_email);
    $pdo = $db->prepare($resetSql);
    $pdo->execute($bindings);
    $resetRes = $pdo->fetch(PDO::FETCH_ASSOC);
    $currentTimestamp = time();
    if ($resetRes && safe_count($resetRes) > 0) {
        $passId = getDownloadId();
        $sql_change = "update " . $GLOBALS['PREFIX'] . "core.Users set userKey=? where (user_email=? or user_phone_no=?)";
        $bindings = array($passId, $user_email, $user_email);
        $pdo = $db->prepare($sql_change);
        $updRes = $pdo->execute($bindings);

        $mailStus = resetPasswordMail($resetRes['first_name'], $user_email, $passId);

        if ($mailStus == 1) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 2;
    }
}

function resetPasswordMail($name, $email, $id)
{

    global $base_url;
    global $supportMail;
    global $mailCustName;
    global $customerContact;
    $resetLink = $base_url . 'reset-password.php?vid=' . $id;

    $customerDisplayName = ($mailCustName != "") ? $mailCustName : 'Nanoheal';

    $subject = "$customerDisplayName Password Reset";
    $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="format-detection" content="telephone=no"/>
    <title>V2 Welcome to ' . $customerDisplayName . '</title>
    <link href="../assets/css/family=RobotoOpenSans.css" rel="stylesheet" type="text/css">
    <style type="text/css">

        /* Resets: see reset.css for details */
        .ReadMsgBody { width: 100%; background-color: #ffffff;}
        .ExternalClass {width: 100%; background-color: #ffffff;}
        .ExternalClass, .ExternalClass p, .ExternalClass span,
        .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
        #outlook a{ padding:0;}
        body{width: 100%; height: 100%; background-color: #ffffff; margin:0; padding:0;}
        body{ -webkit-text-size-adjust:none; -ms-text-size-adjust:none; }
        html{width:100%;}
        table {mso-table-lspace:0pt; mso-table-rspace:0pt; border-spacing:0;}
        table td {border-collapse:collapse;}
        table p{margin:0;}
        br, strong br, b br, em br, i br { line-height:100%; }
        div, p, a, li, td { -webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
        h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
        span a { text-decoration: none !important;}
        a{ text-decoration: none !important; }
        img{height: auto !important; line-height: 100%; outline: none; text-decoration: none;  -ms-interpolation-mode:bicubic;}
        .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited,
        .yshortcuts a:hover, .yshortcuts a span { text-decoration: none !important; border-bottom: none !important;}
        /*mailChimp class*/
        .default-edit-image{
                    height:20px;
        }
        ul{padding-left:10px; margin:0;}
        .tpl-repeatblock {
                    padding: 0px !important;
        border: 1px dotted rgba(0,0,0,0.2);
        }
        .tpl-content{
                    padding:0px !important;
        }
        @media only screen and (max-width:800px){
                    table[style*="max-width:800px"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
        table[style*="max-width:800px"] img{width:100% !important; height:auto !important; max-width:100% !important;}
        }
        @media only screen and (max-width: 640px){
                    /* mobile setting */
                    table[class="container"]{width:100%!important; max-width:100%!important; min-width:100%!important;
        padding-left:20px!important; padding-right:20px!important; text-align: center!important; clear: both;}
        td[class="container"]{width:100%!important; padding-left:20px!important; padding-right:20px!important; clear: both;}
        table[class="full-width"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
        table[class="full-width-center"] {width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        table[class="force-240-center"]{width:240px !important; clear: both; margin:0 auto; float:none;}
        table[class="auto-center"] {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"]{width: auto!important; max-width:75%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        table[class="col-3"],table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important;}
        table[class="col-2"]{width:47.3%!important; max-width:100%!important;}
        *[class="full-block"]{width:100% !important; display:block !important; clear: both; padding-top:10px; padding-bottom:10px;}
        /* image */
        td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important;}
        /* helper */
        table[class="space-w-20"]{width:3.57%!important; max-width:20px!important; min-width:3.5% !important;}
        table[class="space-w-20"] td:first-child{width:3.5%!important; max-width:20px!important; min-width:3.5% !important;}
        table[class="space-w-25"]{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
        table[class="space-w-25"] td:first-child{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
        table[class="space-w-30"] td:first-child{width:5.35%!important; max-width:30px!important; min-width:5.35% !important;}
        table[class="fix-w-20"]{width:20px!important; max-width:20px!important; min-width:20px!important;}
        table[class="fix-w-20"] td:first-child{width:20px!important; max-width:20px!important; min-width:20px !important;}
        *[class="h-10"]{display:block !important;  height:10px !important;}
        *[class="h-20"]{display:block !important;  height:20px !important;}
        *[class="h-30"]{display:block !important; height:30px !important;}
        *[class="h-40"]{display:block !important;  height:40px !important;}
        *[class="remove-640"]{display:none !important;}
        *[class="text-left"]{text-align:left !important;}
        *[class="clear-pad"]{padding:0 !important;}
        }
        @media only screen and (max-width: 479px){
                    /* mobile setting */
                    table[class="container"]{width:100%!important; max-width:100%!important; min-width:124px!important;
        padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
        td[class="container"]{width:100%!important; padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
        table[class="full-width"],table[class="full-width-479"]{width:100%!important; max-width:100%!important; min-width:124px!important; clear: both;}
        table[class="full-width-center"] {width: 100%!important; max-width:100%!important; min-width:124px!important; text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"]{width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        table[class="col-3"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
        table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important; }
        table[class="col-2"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
        *[class="full-block-479"]{display:block !important; width:100% !important; clear: both; padding-top:10px; padding-bottom:10px; }
        /* image */
        td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:124px !important;}
        td[class="image-min-80"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:80px !important;}
        td[class="image-min-100"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:100px !important;}
        /* halper */
        table[class="space-w-20"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-20"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-25"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-25"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-30"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-30"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
        *[class="remove-479"]{display:none !important;}
        table[width="595"]{width:100% !important;}
        img{max-width:280px !important;}
        .resize-font, .resize-font *{
                        font-size: 37px !important;
          line-height: 48px !important;
        }
        }

        a:active{color:initial !important;} a:visited{color:initial !important;}
        td ul{list-style: initial; margin:0; padding-left:20px;}

            @media only screen and (max-width: 640px){ .image-100-percent{ width:100%!important; height: auto !important; max-width: 100% !important; min-width: 124px !important;}}body{background-color:#efefef;} .default-edit-image{height:20px;} tr.tpl-repeatblock , tr.tpl-repeatblock > td{ display:block !important;} .tpl-repeatblock {padding: 0px !important;border: 1px dotted rgba(0,0,0,0.2);} table[width="595"]{width:100% !important;}a img{ border: 0 !important;}
    a:active{color:initial !important;} a:visited{color:initial !important;}
    .tpl-content{padding:0 !important;}
    </style>
    <!--[if gte mso 15]>
    <style type="text/css">
                        a{text-decoration: none !important;}
    body { font-size: 0; line-height: 0; }
    tr { font-size:1px; mso-line-height-alt:0; mso-margin-top-alt:1px; }
    table { font-size:1px; line-height:0; mso-margin-top-alt:1px; }
    body,table,td,span,a{font-family: Arial, Helvetica, sans-serif !important;}
    a img{ border: 0 !important;}
    </style>
    <![endif]-->
    <!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
    </head>
    <body  style="font-size:12px; width:100%; height:100%;">
    <table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#efefef; width:800px; max-width: 800px; margin: 0 auto; outline: 1px solid #efefef; box-shadow: 0px 0px 5px #E0E0E0;"><!--START LAYOUT-2 (LOGO/CONTENT AND BUTTON) --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;" class="full-width"><!-- start space --><tbody><tr><td valign="top" height="12" style="height: 12px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start header image --><tbody><tr dup="0"><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="top" width="175" style="width: 175px;">
                                        <a href="#" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                          <img src="' . $base_url . '/assets/img/logo.png" width="175" style="max-width: 240px; display: block !important; width: 175px; height: auto;" alt="set3-image-icon.png" border="0" hspace="0" vspace="0" height="auto"></a>
                                      </td>
                                    </tr><!-- start space --><tr><td valign="top" height="7" style="height: 7px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                      </tr><!-- end space --></tbody></table></td>
                              </tr><!-- end header image --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                              </tr><!-- end space --><!-- start content --><tr><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr dup="0"><td valign="top" align="center">
                                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><!-- start content --><!-- end content --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                          </tr><!-- end space --></tbody></table></td>
                                    </tr><!-- start button --><tr><td valign="top">
                                        <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr>

                                          </tr></tbody></table></td>
                                    </tr><!-- end button --></tbody></table></td>
                              </tr><!-- end content --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!--END LAYOUT-2 (LOGO/CONTENT AND BUTTON) --></tbody><!--START LAYOUT-2 (LOGO/CONTENT AND BUTTON) --><tbody><tr><td align="center" valign="top" class="container" style="background:url(http://mailbuild.rookiewebstudio.com/customers/QMmiPwSJ/user_upload/20161103175027_Untitled_design.jpg) no-repeat top center/cover; background-color: #ffffff; background-size: cover; background-position: 50% 0%, 50% 50%; background-repeat: no-repeat;" background="http://mailbuild.rookiewebstudio.com/customers/QMmiPwSJ/user_upload/20161103175027_Untitled_design.jpg" width="100%" height="100%">
                <!-- start container -->
                <!--[if gte mso 9]>				<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:800px; height:483px; background-repeat:no-repeat;">				<v:fill type="frame" color="#ffffff" src="http://mailbuild.rookiewebstudio.com/customers/QMmiPwSJ/user_upload/20161103175027_Untitled_design.jpg" ></v:fill>				<v:textbox style="mso-fit-text-to-shape:true; v-padding-auto:true;" inset="0,0,0,0" >				<![endif]--><table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; padding-left: 20px; padding-right: 20px; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;" class="full-width"><!-- start space --><tbody><tr><td valign="top" height="50" style="height: 50px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start header image --><tbody><tr dup="0"><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="top" width="100" style="width: 100px;">
                                        <a href="#" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                          <img src="' . $base_url . '/vendors/images/20161103171453_Picture1.png" width="100" style="max-width: 240px; height: auto; display: block;" alt="set3-image-icon.png" border="0" hspace="0" vspace="0" height="auto"></a>
                                      </td>
                                    </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                      </tr><!-- end space --></tbody></table></td>
                              </tr><!-- end header image --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                              </tr><!-- end space --><!-- start content --><tr><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr dup="0"><td valign="top" align="center">
                                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr dup="0"><td valign="top">
                                              <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" style="font-size: 14px; color: rgb(255, 255, 255); font-weight: normal; text-align: center; font-family: Roboto, Arial, Helvetica, sans-serif; word-break: break-word; line-height: 22px;"><br></td>
                                                </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                                </tr><!-- end space --><tr><td align="center" style="font-size: 28px; color: rgb(255, 255, 255); font-weight: normal; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; word-break: break-word; line-height: 36px;"><span style="font-family: "trebuchet ms", geneva, arial !important; font-size: 40px; word-break: break-word; line-height: 48px;"></span><br><br><span style="font-family: helvetica, "arial sans-serif" !important; font-size: 21px; word-break: break-word; line-height: 29px;">You\'re one step away from all the goodness.</span><br><span style="font-family: helvetica, "arial sans-serif" !important; font-size: 21px; word-break: break-word; line-height: 29px;"><span style="color: rgb(230, 227, 227); font-size: inherit; line-height: 29px;">Set your password and get rolling</span>.</span><br></td>
                                                </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                                </tr><!-- end space --></tbody></table></td>
                                          </tr><!-- start content --><!-- end content --><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                          </tr><!-- end space --></tbody></table></td>
                                    </tr><!-- start button --><tr><td valign="top">
                                        <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" align="center" style="padding:5px;" class="full-block-479" dup="0">
                                              <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" style="border-radius: 24px; background-color: #52d2a9;">
                                                    <table width="auto" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td width="auto" align="center" valign="middle" height="42" style="min-width: 80px; font-size: 14px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: rgb(255, 255, 255); font-weight: normal; padding-left: 25px; padding-right: 25px; background-clip: padding-box; word-break: break-word; line-height: 22px;"><span style="font-size: 14px; line-height: 22px;"><a href="' . $resetLink . '" style="font-size: inherit; border-style: none; text-decoration: none !important;" border="0">RESET PASSWORD</a><br></span></td>
                                                      </tr></tbody></table></td>
                                                </tr></tbody></table></td></tr></tbody></table></td>
                                    </tr><!-- end button --></tbody></table></td>
                              </tr><!-- end content --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="77" style="height: 77px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!--[if gte mso 9]>				</v:textbox>				</v:rect>				<![endif]--><!-- end container --></td>
            </tr><!--END LAYOUT-2 (LOGO/CONTENT AND BUTTON) --></tbody><!-- START LAYOUT-13 ( FULL-IMAGE / TEXT ) --><tbody><tr><td valign="top" align="center" style="background-color:#ffffff;" class="container">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" align="center">
                      <table width="560" border="0" cellspacing="0" cellpadding="0" align="center" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top" align="center">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start image --><tbody><!-- end image --><!-- start title --><!-- end title --><!-- start description --><!-- end description --><!-- start content --><!-- end content --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!-- END LAYOUT-13 ( FULL-IMAGE / TEXT ) --></tbody><!-- START LAYOUT-5 ( CONTENT SOCIAL ) --><tbody><tr><td valign="top" align="center" class="container" style="background-color: #ffffff;">
              <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" align="center">
                    <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="30" style="height: 30px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                      </tr><!-- end space --><tr dup="0"><td valign="top" align="center">
                          <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" style="font-size: 14px; color: rgb(136, 136, 136); font-weight: normal; text-align: center; font-family: "Open Sans", Arial, Helvetica, sans-serif; word-break: break-word; line-height: 22px;"><span style="font-size: inherit; line-height: 22px;">If you have any questions, please reply to our mail.<br></span></td>
                            </tr><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                            </tr><!-- end space --></tbody></table></td>
                      </tr><tr dup="0"><td valign="top">
                          <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="middle">
                                <table width="auto" align="center" border="0" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="middle" style="padding-left: 3px; padding-right: 3px; width: 25px;" width="25">
                                      <a href="#" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                        <img src="' . $base_url . '/vendors/images/set13-social-facebook-gray.png" width="25" alt="https://www.facebook.com/' . $customerDisplayName . '" style="max-width: 25px; display: block !important; width: 25px; height: auto;" height="auto"></a>
                                    </td>
                                    <td align="center" valign="middle" style="padding-left: 3px; padding-right: 3px; width: 25px;" width="25">
                                      <a href="#" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                        <img src="' . $base_url . '/vendors/images/set13-social-twitter-gray.png" width="25" alt="https://twitter.com/' . $customerDisplayName . '" style="max-width: 25px; display: block !important; width: 25px; height: auto;" height="auto"></a>
                                    </td></tr></tbody></table></td>
                            </tr><!-- start space --><tr><td valign="top" height="8" style="height: 8px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                            </tr><!-- end space --></tbody></table></td>
                      </tr></tbody></table></td>
                </tr><!-- start space --><tr><td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                </tr><!-- end space --></tbody></table></td>
          </tr><!-- END LAYOUT-5 ( CONTENT SOCIAL ) --></tbody><!--START LAYOUT-16 ( UNSUBSCRIBE ) --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr dup="0"><td valign="top">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start rights reserved --><tbody><tr><td align="center" style="font-size: 13px; color: rgb(51, 51, 51); font-weight: normal; text-align: center; font-family: Roboto, "Open Sans", Arail, Tahoma, Helvetica, Arial, sans-serif; word-break: break-word; line-height: 21px;"><span style="color: rgb(153, 153, 153); font-size: 13px; line-height: 21px;"><br>Copyright &copy; ' . date('Y', time()) . ' ' . $customerDisplayName . '.&nbsp;All rights reserved.</span></td>
                              </tr><!-- end rights reserved --><!-- start space --><tr><td valign="top" height="8" style="height: 8px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                </tr><!-- end space --></tbody></table></td>
                        </tr><tr dup="0"><td valign="top">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" style="font-size: 14px; color: rgb(82, 210, 169); font-weight: normal; text-align: center; font-family: Roboto, "Open Sans", Arail, Tahoma, Helvetica, Arial, sans-serif; word-break: break-word; line-height: 22px;">
                                <span style="text-decoration: none; color: rgb(82, 210, 169); font-size: inherit; line-height: 22px;">
                                </span>
                              </td>
                            </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                </tr><!-- end space --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                      </tr><!-- end space --></tbody></table></td>
                </tr></tbody></table><!-- end container --></td>
          </tr><!--END LAYOUT-16 ( UNSUBSCRIBE ) --></tbody><!--START LAYOUT-9 ( CONTENT / BUTTON )  --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start content --><tbody><!-- end content --><!-- start content --><!-- end content --><!-- start button --><tr><td valign="top" align="center">
                                  <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr>
                                    </tr></tbody></table></td>
                              </tr><!-- end button --></tbody></table></td>
                        </tr><!-- end content --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!--END LAYOUT-9 ( CONTENT / BUTTON ) --></tbody><!--START LAYOUT-9 ( CONTENT / BUTTON )  --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start content --><tbody><!-- end content --><!-- start content --><!-- end content --><!-- start button --><tr><td valign="top" align="center">
                                  <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr>
                                    </tr></tbody></table></td>
                              </tr><!-- end button --></tbody></table></td>
                        </tr><!-- end content --><!-- start space --><tr><td valign="top" height="2" style="height: 2px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!--END LAYOUT-9 ( CONTENT / BUTTON ) --></tbody><!--START LAYOUT-05 ( HEADING )  --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="3" style="height: 3px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><!-- start content --><!-- end content --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!--END LAYOUT-05 ( HEADING ) --></tbody><!--START LAYOUT-14 ( FOOTER LOGO )  --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start image --><tbody><tr><td valign="top" align="center" width="136" style="width: 136px;">
                                  <img src="' . $base_url . '/assets/img/logo.png" width="136" alt="set3-iogo-footer" style="max-width: 240px; display: block !important; width: 136px; height: auto;" border="0" hspace="0" vspace="0" height="auto"></td>
                              </tr><!-- end image--></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="30" style="height: 30px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!--END LAYOUT-14 ( FOOTER LOGO ) --></tbody></table></body>
    </html>';
    $fromEmailId = ($supportMail != '') ? $supportMail : "noreply@nanoheal.com";

    $fromName = 'Nanoheal';
    $toName = explode('@', $email)[0];
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $email,
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

function getDownloadId()
{

    try {

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
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function resetPassword($db, $userid, $pwd)
{

    $updSql = $db->prepare("UPDATE Agent SET password = ?,passChange='0',resetsession='' where resetsession=?");
    $updSql->execute([$pwd, $userid]);
    $resetRes = $db->lastInsertId();
    if ($resetRes) {
        return 1;
    } else {
        return 0;
    }
}

function IsLoggerIn()
{
    if ($_SESSION["user"]["username"] != "") {
        return $_SESSION["user"]["username"];
    } else {
        header("location:../index.php");
    }
}

function GetMachineProfile($db, $id)
{

    $sql1 = $db->prepare("select group_query.group_name,all_query.value,group_query.cnt,all_query.ordinal from (select dn.name,pd.value,pd.ordinal
				from " . $GLOBALS['PREFIX'] . "asset.DataName dn," . $GLOBALS['PREFIX'] . "dashboard.ProfileDisplay pd
				where pd.dataid=dn.dataid and pd.censusid=? and
				pd.userid=(select userid from " . $GLOBALS['PREFIX'] . "core.Users where username='admin'))all_query,
				(select dn.name group_name,count(*) cnt from " . $GLOBALS['PREFIX'] . "asset.DataName dn," . $GLOBALS['PREFIX'] . "dashboard.ProfileDisplay pd
				where pd.dataid=dn.dataid  and pd.censusid=? and pd.userid=1 group by name having count(*) >1)group_query
				where group_query.group_name=all_query.name order by ordinal ");
    $sql1->execute([$id, $id]);

    $sql2 = $db->prepare("select dn.name,pd.value,0,pd.ordinal from " . $GLOBALS['PREFIX'] . "asset.DataName dn," . $GLOBALS['PREFIX'] . "dashboard.ProfileDisplay pd
				where pd.dataid=dn.dataid and pd.censusid=? and pd.userid=(select userid from " . $GLOBALS['PREFIX'] . "core.Users where username='admin')
				group by name having count(*) = 1 ");
    $sql2->execute([$id]);
    $result2 = find_many($sql2, $db);
    $result1 = $sql2->fetchAll();
    $index = 0;
    $str = array();
    foreach ($result2 as $key => $arr) {
        $str[$index]['name'] = $arr['name'];
        $str[$index]['value'] = $arr['value'];
        $index++;
    }
    $result1 = $sql1->fetchAll();
    foreach ($result1 as $key => $arr) {
        $str[$index]['name'] = $arr['group_name'];
        $str[$index]['value'] = $arr['value'];
        $index++;
    }
    $display = "<table height='100%' width='100%' style='border-collapse:collapse; font-family: Calibri, Verdana, Arial, Helvetica, sans-serif; font-size:12px; overflow:auto;'  >";
    $display .= "<tr style='border-bottom: 1px solid #BFBFBF; background-color:#535353; color:#fff000; '  >";
    $display .= "<td width='25%' align='right' height='26px' style='padding-left:15px'> Name </td>";
    $display .= "<td width='25%' align='left' height='26px' style='padding-left:25px'> Value </td>";
    $display .= "<td width='25%' align='right' height='26px' style='padding-left:15px'> Name </td>";
    $display .= "<td width='25%' align='left' height='26px' style='padding-left:25px'> Value </td>";
    $display .= "</tr>";

    $half = safe_count($str) / 2;
    for ($i = 0; $i < $half; $i++) {
        $display .= "<tr style='background-color:#F5F5F5; '>";
        $display .= "<td height='26px' align='right' style='padding-left:15px' width='25%'>" . $str[$i]['name'] . "</td>";
        $display .= "<td height='26px' align='left' style='padding-left:25px' width='25%'>" . $str[$i]['value'] . "</td>";
        $display .= "<td height='26px' align='right' style='padding-left:15px' width='25%'>" . $str[$half + $i]['name'] . "</td>";
        $display .= "<td height='26px' align='left' style='padding-left:25px' width='25%'>" . $str[$half + $i]['value'] . "</td>";
        $display .= "</tr>";
    }
    $display .= "<tr>";
    $display .= "<td colspan='4' style='background-color:#f5f5f5;'>&nbsp;</td>";
    $display .= "</tr>";
    $display .= "</table>";

    echo $display;
}

function GetMachineidAsset($db, $host)
{
    $sql = $db->prepare("select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine where host= ?");
    $sql->execute([$host]);
    $res = $sql->fetch();
    return $res['machineid'];
}

function GetSiteidAsset($db, $customer, $username)
{
    $sql = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Customers where username= ? and customer = ? ");
    $sql->execute([$username, $customer]);
    $res = $sql->fetch();
    return $res['id'];
}

function rsmidnight($tdate)
{
    $hour = (60 * 60);
    $tday = getdate($tdate);
    $delta = $tday['seconds'] + (60 * ($tday['minutes'] + (60 * $tday['hours'])));
    $ydate = ($delta <= 0) ? $tdate : $tdate - $delta;
    return rscorrect_hour($ydate, 0);
}

function rscorrect_hour($when, $hour)
{
    $tm = getdate($when);
    $hh = $tm['hours'];
    if ($hh != $hour) {
        $temp = $when;
        if ((($hh + 1) % 24) == $hour) {
            $temp = $when + 3600;
        }
        if ((($hh + 23) % 24) == $hour) {
            $temp = $when - 3600;
        }
        if ($temp != $when) {
            $tm = getdate($temp);
            $hh = $tm['hours'];
            if ($hh == $hour) {
                $when = $temp;
            }
        }
    }
    return $when;
}

function rspast_options($midn, $days)
{
    reset($days);
    foreach ($days as $key => $day) {
        $time = rsdate_code($midn, $day);
        $text = date('D m/d', $time) . " ($day days)";
        $opts[$day] = $text;
    }
    return $opts;
}

function rsdate_code($when, $d)
{
    if ($d > 1) {
        $when = rsdays_ago($when, $d - 1);
    }
    return $when;
}

function rsdays_ago($time, $days)
{
    $date = getdate($time);
    $hour = $date['hours'];
    $when = $time - ($days * 86400);
    return rscorrect_hour($when, $hour);
}

function rsrestrict_time($srch_lastrun, $midn, $field)
{
    $valu = $srch_lastrun;
    if ($valu > 0) {
        $time = rsdate_code($midn, $valu);
        return "R.$field > $time";
    }
}

function mumSearchPatchOptions($db)
{

    $sql = $db->prepare("select patchid, name from " . $GLOBALS['PREFIX'] . "softinst.Patches order by name");
    $sql->execute();
    $res = $sql->fetchAll();
    return $res;
}

function mumSearchMachOptions($site, $db)
{
    $siteArr = array();
    $siteArr = explode(',', $site);
    $in = str_repeat('?,', safe_count($siteArr) - 1) . '?';
    $sql = $db->prepare("select machineid, host from " . $GLOBALS['PREFIX'] . "asset.Machine where cust in($in) order by host");
    $sql->execute($siteArr);
    $macRes = $sql->fetchAll();
    return $macRes;
}

function refuture_options()
{
    $m = 60;
    $h = $m * 60;
    $d = $h * 24;
    return array(
        $h * 1 => '1 hour',
        $h * 2 => '2 hours',
        $h * 3 => '3 hours',
        $h * 4 => '4 hours',
        $h * 5 => '5 hours',
        $h * 6 => '6 hours',
        $h * 7 => '7 hours',
        $h * 8 => '8 hours',
        $h * 9 => '9 hours',
        $h * 10 => '10 hours',
        $h * 11 => '11 hours',
        $h * 12 => '12 hours',
        $h * 13 => '13 hours',
        $h * 14 => '14 hours',
        $h * 15 => '15 hours',
        $h * 16 => '16 hours',
        $h * 17 => '17 hours',
        $h * 18 => '18 hours',
        $h * 19 => '19 hours',
        $h * 20 => '20 hours',
        $h * 21 => '21 hours',
        $h * 22 => '22 hours',
        $h * 23 => '23 hours',
        $d => '1 day',
        $d * 2 => '2 days',
        $d * 3 => '3 days',
        $d * 4 => '4 days',
        $d * 5 => '5 days',
        $d * 6 => '6 days',
        $d * 7 => '1 week',
        $d * 14 => '2 weeks',
    );
}

function resecs_options()
{
    $out = array();
    $set = refuture_options();

    reset($set);
    foreach ($set as $secs => $txt) {
        $out[$secs] = $txt;
    }
    return $out;
}

function getSiteNameByMachine($searchValue)
{
    $searchValueArr = array();
    $searchValueArr = explode(',', $searchValue);
    $db = pdo_connect();

    $in = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
    $machineQry = $db->prepare("select C.site, C.host as host
                       from " . $GLOBALS['PREFIX'] . "core.Census as C left join " . $GLOBALS['PREFIX'] . "core.Revisions as R on R.censusid = C.id
                       left join " . $GLOBALS['PREFIX'] . "agent.serviceRequest as S on S.serviceTag = C.host
                       left join " . $GLOBALS['PREFIX'] . "agent.customerOrder as CO on CO.orderNum = S.orderNum
                       where C.host IN($in) order by C.id desc limit 1");
    $machineQry->execute($searchValueArr);
    $machineRes = $machineQry->fetch();

    return $machineRes;
}

function getSectionOptions($userName, $db)
{
    $secSql = $db->prepare("select sectionname, sectionuniq from " . $GLOBALS['PREFIX'] . "report.Section where username = ? order by sectionnam");
    $secSql->execute([$userName]);
    $secRes = $secSql->fetchAll();
    return $secRes;
}

function getScheduleOptions($userName, $db)
{
    $schSql = $db->prepare("select name, scheduniq from " . $GLOBALS['PREFIX'] . "schedule.Schedules where username=? order by name");
    $schSql->execute([$userName]);
    $schRes = $schSql->fetchAll();
    return $schRes;
}

function convert_time_zone($timeFromDatabase_time)
{
    $userTime = new DateTime($timeFromDatabase_time, new DateTimeZone('America/Mexico_City'));
    $userTime->setTimezone(new DateTimeZone($_SESSION['timeZone']));
    return $userTime->format('Y-m-d H:i:s');
}

function getMachineOs($host, $site)
{
    $db = pdo_connect();
    $sql = $db->prepare("select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName where name = 'Operating System'");
    $sql->execute();
    $dnres = $sql->fetch();
    $dataid = $dnres['dataid'];

    $sql = $db->prepare("select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine where host = ? and cust = ?");
    $sql->execute([$host, $site]);
    $mres = $sql->fetch();
    $machineid = $mres['machineid'];
    if ($dataid != '' && $machineid != '') {

        $sql = $db->prepare("select value from " . $GLOBALS['PREFIX'] . "asset.AssetData where dataid = ? and machineid = ?");
        $sql->execute([$dataid, $machineid]);
        $adres = $sql->fetch();
        $machineOs = explode(' ', $adres['value']);
        return $machineOs[0];
    } else {
        return;
    }
}

function getDartData($query)
{

    $db = pdo_connect();
    $res = $query->fetch();
    $darts = explode(',', $res['dartno']);
    return $darts;
}

function logout($db, $keepPrimaryLoggedIn = false)
{
    $username = $_SESSION["user"]["adminEmail"];
    $status = false;

    if (!$keepPrimaryLoggedIn) {
        $dbo = NanoDB::connect();
        $logintime = $dbo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set loginStatus='0' where user_email=?");
        $logintime->execute([$username]);
    }

    $timezone = $_SESSION['userTimeZone'];

    try {
        if (!function_exists('login_audit')) {
            require_once dirname(__FILE__) . '/../lib/l-logAudit.php';
        }

        login_audit($db, $username, 'logout', $timezone, 'Success');
        create_auditLog('User', 'Logout', 'Success');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }

    try {
        $_SESSION["user"]["username"] = "";
        unset($_SESSION["user"]["username"]);
        $_SESSION['user'] = array();
        $status = true;
        setcookie('PHPSESSID', null, -1, '/');
        setcookie('usertoken', null, -1, '/');
        setcookie('sso', null, -1, '/');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }

    return $status;
}

function get_TrialInformation($db, $cId)
{
    $sql = $db->prepare("SELECT showTrialBox, trialEnabled, trialStartDate FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = ? LIMIT 1");
    $sql->execute([$cId]);
    $res = $sql->fetch();

    if ($res['trialEnabled'] == '0' && $res['trialStartDate'] == 0) {
        $siteExistSql = $db->prepare("SELECT C.customer FROM " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U WHERE U.username=C.username AND U.ch_id = ?");
        $siteExistSql->execute([$cId]);
        $siteExistRes = $siteExistSql->fetchAll();

        if (safe_count($siteExistRes) == 0) {
            if ($res['showTrialBox'] == '0') {
                return 1;
            } else if ($res['showTrialBox'] == '1') {
                return 2;
            }
        } else {
            return 3;
        }
    } else {
        return 3;
    }
}

function getTrimmedCompanyName($companyName)
{
    if (preg_match("/^[-a-zA-Z0-9_]*$/", $companyName)) {
        $split = explode('_', $companyName);
        $num = $split[1];
        if (preg_match("/^[0-9]*$/", $num)) {
            return $split[0];
        } else {
            return $companyName;
        }
    } else {

        return $companyName;
    }
}

function getTrialDownloadUrl($db, $cId)
{
    global $base_url;
    $sql = $db->prepare("SELECT downloadId FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder C, " . $GLOBALS['PREFIX'] . "agent.skuMaster S where C.compId=? AND C.SKUNum=S.skuRef AND S.trial='1' LIMIT 1");
    $sql->execute([$cId]);
    $res = $sql->fetch();
    if (safe_count($res) > 0) {
        $downloadId = $res['downloadId'];
        $url = $base_url . 'eula.php?id=' . $downloadId;
    } else {
        $url = '';
    }
    return $url;
}

function getSiteDetails($machineName, $customerNum, $orderNum, $db)
{

    $sql = $db->prepare("select C.site as siteName, C.id, C.host from " . $GLOBALS['PREFIX'] . "agent.customerOrder CO, " . $GLOBALS['PREFIX'] . "core.Census C where CO.customerNum = ? and CO.orderNum = ? and C.host = ? order by C.id desc limit 1");
    $sql->execute([$customerNum, $orderNum, $machineName]);
    $res = $sql->fetch();

    return $res;
}

function checkModulePrivilege($roleName, $requiredVal)
{
    return nhRole::checkModulePrivilege($roleName, $requiredVal);
}

function validateMimeAndExtension($fileName, $allowedMime, $allowedExtensions, $isImage = false)
{

    if (!in_array($_FILES[$fileName]['type'], $allowedMime)) {
        return false;
    }

    $fileInfo = pathinfo($_FILES[$fileName]['name']);

    if (!is_array($fileInfo) || !array_key_exists("extension", $fileInfo) || !in_array($fileInfo['extension'], $allowedExtensions)) {
        return false;
    }

    $tempName = $_FILES[$fileName]['tmp_name'];
    $mimeType = mime_content_type($tempName);

    if (!in_array($mimeType, $allowedMime)) {
        return false;
    }

    $finfoCheck = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES[$fileName]['tmp_name']);

    if (!in_array($finfoCheck, $allowedMime)) {
        return false;
    }

    if ($isImage) {
        $sizeArray = getimagesize($_FILES[$fileName]['tmp_name']);

        if (!isset($sizeArray) || !is_array($sizeArray) || !array_key_exists("mime", $sizeArray) || !in_array($sizeArray["mime"], $allowedMime)) {
            return false;
        }
    }

    return true;
}


function getUserRolesUsingJson_PDO($role_id, $db)
{
    $rolesql = "SELECT name,value,type FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE id=? and type=10 limit 1";
    $pdo = $db->prepare($rolesql);
    $pdo->execute([$role_id]);
    $role = $pdo->fetch(PDO::FETCH_ASSOC);

    if ($role) {
        $roleVal = $role['value'];
        logs::log(__FILE__, __LINE__, $roleVal, 0);
        $roleItemsArray = safe_json_decode($roleVal, true);

        // $roleItemsArray['user']['roleValue']['services'] = $roleItemsArray['user']['roleValue']['service'];
        return $roleItemsArray;
    }

    return false;
}

function checkVisualisationAccess($pdo, $dID)
{

    $userid = $_SESSION['user']['userid'];
    $sql = $pdo->prepare("Select uid from " . $GLOBALS['PREFIX'] . "agent.dashboard where dashboardId=?");
    $sql->execute([$dID]);
    $sqlres = $sql->fetch();

    if ($sqlres && $sqlres['uid'] == $userid) {
        return true;
    } else {
        return false;
    }
}

function getChildDetails($userid, $colName)
{
    $pdo = pdo_connect();

    $sql = $pdo->prepare("select userid, user_email, parent_id,username from " . $GLOBALS['PREFIX'] . "core.Users where parent_id = ?");
    $sql->execute([$userid]);
    $res = $sql->fetchAll();

    $uCount = safe_count($res);
    $new = [];

    if ($uCount > 0) {
        foreach ($res as $value) {
            $new[] = $value[$colName];
            $chUserList = getChildDetails($value['userid'], $colName);
            foreach ($chUserList as $chval) {
                $new[] = $chval;
            }
        }
    } else {
        $new = $res;
    }

    return $new;
}

function getParent($userid)
{
    $pdo = NanoDB::connect();
    $sql = $pdo->prepare("select parent_id from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
    $sql->execute([$userid]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    $parents = $result['parent_id'];

    return (safe_sizeof($parents) > 0 ? ($parents) : false);
}

function getParents($userid, &$parents = [])
{
    $pdo = NanoDB::connect();
    $sql = $pdo->prepare("select parent_id from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
    $sql->execute([$userid]);
    $result = $sql->fetch();

    if ($result && is_numeric($result['parent_id']) && intval($result['parent_id']) > 0) {
        if (!in_array($result['parent_id'], $parents)) {
            $parents[] = $result['parent_id'];
            getParents($result['parent_id'], $parents);
        }
    }
    return (safe_sizeof($parents) > 0 ? ($parents) : false);
}


function sendOtpMail($otp, $email, $db)
{

    $subject = "Nanoheal - Login OTP";
    $body = "Hi, <br/> Please find your OTP : $otp";
    $fromEmailId = "noreply@nanoheal.com";

    $headers = "";
    $headers .= "Organization: Sender Organization\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    $headers .= "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=iso-8859-1\r\n";

    $headers .= 'From:' . $fromEmailId . "\r\n";
    //    mail($email, $subject, $body, $headers);
    // send from visualisationService
    $arrayPost = array(
        'from' => getenv('SMTP_USER_LOGIN'),
        'to' => $email,
        'subject' => $subject,
        'text' => '',
        'html' => $body,
        'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
    CURL::sendDataCurl($url, $arrayPost);
}



function sendMailOtp($otp, $email)
{

    global $base_url;

    $subject = "Login OTP";

    $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta name="format-detection" content="telephone=no"/>
    <title>V2 Welcome to Selfheal</title>
    <link href="../assets/css/family=RobotoOpenSans.css" rel="stylesheet" type="text/css">
    <style type="text/css">

        /* Resets: see reset.css for details */
        .ReadMsgBody { width: 100%; background-color: #ffffff;}
        .ExternalClass {width: 100%; background-color: #ffffff;}
        .ExternalClass, .ExternalClass p, .ExternalClass span,
        .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height:100%;}
        #outlook a{ padding:0;}
        body{width: 100%; height: 100%; background-color: #ffffff; margin:0; padding:0;}
        body{ -webkit-text-size-adjust:none; -ms-text-size-adjust:none; }
        html{width:100%;}
        table {mso-table-lspace:0pt; mso-table-rspace:0pt; border-spacing:0;}
        table td {border-collapse:collapse;}
        table p{margin:0;}
        br, strong br, b br, em br, i br { line-height:100%; }
        div, p, a, li, td { -webkit-text-size-adjust:none; -ms-text-size-adjust:none;}
        h1, h2, h3, h4, h5, h6 { line-height: 100% !important; -webkit-font-smoothing: antialiased; }
        span a { text-decoration: none !important;}
        a{ text-decoration: none !important; }
        img{height: auto !important; line-height: 100%; outline: none; text-decoration: none;  -ms-interpolation-mode:bicubic;}
        .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited,
        .yshortcuts a:hover, .yshortcuts a span { text-decoration: none !important; border-bottom: none !important;}
        /*mailChimp class*/
        .default-edit-image{
                    height:20px;
        }
        ul{padding-left:10px; margin:0;}
        .tpl-repeatblock {
                    padding: 0px !important;
        border: 1px dotted rgba(0,0,0,0.2);
        }
        .tpl-content{
                    padding:0px !important;
        }
        @media only screen and (max-width:800px){
                    table[style*="max-width:800px"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
        table[style*="max-width:800px"] img{width:100% !important; height:auto !important; max-width:100% !important;}
        }
        @media only screen and (max-width: 640px){
                    /* mobile setting */
                    table[class="container"]{width:100%!important; max-width:100%!important; min-width:100%!important;
        padding-left:20px!important; padding-right:20px!important; text-align: center!important; clear: both;}
        td[class="container"]{width:100%!important; padding-left:20px!important; padding-right:20px!important; clear: both;}
        table[class="full-width"]{width:100%!important; max-width:100%!important; min-width:100%!important; clear: both;}
        table[class="full-width-center"] {width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        table[class="force-240-center"]{width:240px !important; clear: both; margin:0 auto; float:none;}
        table[class="auto-center"] {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"]{width: auto!important; max-width:75%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        table[class="col-3"],table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important;}
        table[class="col-2"]{width:47.3%!important; max-width:100%!important;}
        *[class="full-block"]{width:100% !important; display:block !important; clear: both; padding-top:10px; padding-bottom:10px;}
        /* image */
        td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important;}
        /* helper */
        table[class="space-w-20"]{width:3.57%!important; max-width:20px!important; min-width:3.5% !important;}
        table[class="space-w-20"] td:first-child{width:3.5%!important; max-width:20px!important; min-width:3.5% !important;}
        table[class="space-w-25"]{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
        table[class="space-w-25"] td:first-child{width:4.45%!important; max-width:25px!important; min-width:4.45% !important;}
        table[class="space-w-30"] td:first-child{width:5.35%!important; max-width:30px!important; min-width:5.35% !important;}
        table[class="fix-w-20"]{width:20px!important; max-width:20px!important; min-width:20px!important;}
        table[class="fix-w-20"] td:first-child{width:20px!important; max-width:20px!important; min-width:20px !important;}
        *[class="h-10"]{display:block !important;  height:10px !important;}
        *[class="h-20"]{display:block !important;  height:20px !important;}
        *[class="h-30"]{display:block !important; height:30px !important;}
        *[class="h-40"]{display:block !important;  height:40px !important;}
        *[class="remove-640"]{display:none !important;}
        *[class="text-left"]{text-align:left !important;}
        *[class="clear-pad"]{padding:0 !important;}
        }
        @media only screen and (max-width: 479px){
                    /* mobile setting */
                    table[class="container"]{width:100%!important; max-width:100%!important; min-width:124px!important;
        padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
        td[class="container"]{width:100%!important; padding-left:15px!important; padding-right:15px!important; text-align: center!important; clear: both;}
        table[class="full-width"],table[class="full-width-479"]{width:100%!important; max-width:100%!important; min-width:124px!important; clear: both;}
        table[class="full-width-center"] {width: 100%!important; max-width:100%!important; min-width:124px!important; text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"]{width: 100%!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        *[class="auto-center-all"] * {width: auto!important; max-width:100%!important;  text-align: center!important; clear: both; margin:0 auto; float:none;}
        table[class="col-3"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
        table[class="col-3-not-full"]{width:30.35%!important; max-width:100%!important; }
        table[class="col-2"]{width:100%!important; max-width:100%!important; text-align: center!important; clear: both;}
        *[class="full-block-479"]{display:block !important; width:100% !important; clear: both; padding-top:10px; padding-bottom:10px; }
        /* image */
        td[class="image-full-width"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:124px !important;}
        td[class="image-min-80"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:80px !important;}
        td[class="image-min-100"] img{width:100% !important; height:auto !important; max-width:100% !important; min-width:100px !important;}
        /* halper */
        table[class="space-w-20"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-20"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-25"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-25"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-30"]{width:100%!important; max-width:100%!important; min-width:100% !important;}
        table[class="space-w-30"] td:first-child{width:100%!important; max-width:100%!important; min-width:100% !important;}
        *[class="remove-479"]{display:none !important;}
        table[width="595"]{width:100% !important;}
        img{max-width:280px !important;}
        .resize-font, .resize-font *{
                        font-size: 37px !important;
          line-height: 48px !important;
        }
        }

        a:active{color:initial !important;} a:visited{color:initial !important;}
        td ul{list-style: initial; margin:0; padding-left:20px;}

            @media only screen and (max-width: 640px){ .image-100-percent{ width:100%!important; height: auto !important; max-width: 100% !important; min-width: 124px !important;}}body{background-color:#efefef;} .default-edit-image{height:20px;} tr.tpl-repeatblock , tr.tpl-repeatblock > td{ display:block !important;} .tpl-repeatblock {padding: 0px !important;border: 1px dotted rgba(0,0,0,0.2);} table[width="595"]{width:100% !important;}a img{ border: 0 !important;}
    a:active{color:initial !important;} a:visited{color:initial !important;}
    .tpl-content{padding:0 !important;}
    </style>
    <!--[if gte mso 15]>
    <style type="text/css">
                        a{text-decoration: none !important;}
    body { font-size: 0; line-height: 0; }
    tr { font-size:1px; mso-line-height-alt:0; mso-margin-top-alt:1px; }
    table { font-size:1px; line-height:0; mso-margin-top-alt:1px; }
    body,table,td,span,a{font-family: Arial, Helvetica, sans-serif !important;}
    a img{ border: 0 !important;}
    </style>
    <![endif]-->
    <!--[if gte mso 9]><xml><o:OfficeDocumentSettings><o:AllowPNG/><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml><![endif]-->
    </head>
    <body  style="font-size:12px; width:100%; height:100%;">
    <table id="mainStructure" width="800" class="full-width" align="center" border="0" cellspacing="0" cellpadding="0" style="background-color:#efefef; width:800px; max-width: 800px; margin: 0 auto; outline: 1px solid #efefef; box-shadow: 0px 0px 5px #E0E0E0;"><!--START LAYOUT-2 (LOGO/CONTENT AND BUTTON) --><tbody><tr><td align="center" valign="top" class="container" style="background-color: #ffffff;">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;" class="full-width"><!-- start space --><tbody><tr><td valign="top" height="12" style="height: 12px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start header image --><tbody><tr dup="0"><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="top" width="175" style="width: 175px;">
                                        <a href="#" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                          <img src="' . $base_url . '/vendors/images/20161103171845_nanoheal_logo.png" width="175" style="max-width: 240px; display: block !important; width: 175px; height: auto;" alt="set3-image-icon.png" border="0" hspace="0" vspace="0" height="auto"></a>
                                      </td>
                                    </tr><!-- start space --><tr><td valign="top" height="7" style="height: 7px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                      </tr><!-- end space --></tbody></table></td>
                              </tr><!-- end header image --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                              </tr><!-- end space --><!-- start content --><tr><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr dup="0"><td valign="top" align="center">
                                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><!-- start content --><!-- end content --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                          </tr><!-- end space --></tbody></table></td>
                                    </tr><!-- start button --><tr><td valign="top">
                                        <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr>

                                          </tr></tbody></table></td>
                                    </tr><!-- end button --></tbody></table></td>
                              </tr><!-- end content --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!--END LAYOUT-2 (LOGO/CONTENT AND BUTTON) --></tbody><!--START LAYOUT-2 (LOGO/CONTENT AND BUTTON) --><tbody><tr><td align="center" valign="top" class="container" style="background:url(http://mailbuild.rookiewebstudio.com/customers/QMmiPwSJ/user_upload/20161103175027_Untitled_design.jpg) no-repeat top center/cover; background-color: #ffffff; background-size: cover; background-position: 50% 0%, 50% 50%; background-repeat: no-repeat;" background="http://mailbuild.rookiewebstudio.com/customers/QMmiPwSJ/user_upload/20161103175027_Untitled_design.jpg" width="100%" height="100%">
                <!-- start container -->
                <!--[if gte mso 9]>				<v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:800px; height:483px; background-repeat:no-repeat;">				<v:fill type="frame" color="#ffffff" src="http://mailbuild.rookiewebstudio.com/customers/QMmiPwSJ/user_upload/20161103175027_Untitled_design.jpg" ></v:fill>				<v:textbox style="mso-fit-text-to-shape:true; v-padding-auto:true;" inset="0,0,0,0" >				<![endif]--><table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; padding-left: 20px; padding-right: 20px; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top">
                      <table width="560" align="center" border="0" cellspacing="0" cellpadding="0" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;" class="full-width"><!-- start space --><tbody><tr><td valign="top" height="50" style="height: 50px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top">
                            <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start header image --><tbody><tr dup="0"><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" valign="top" width="100" style="width: 100px;">
                                        <a href="#" style="text-decoration: none !important; font-size: inherit; border-style: none;" border="0">
                                          <img src="' . $base_url . '/vendors/images/20161103171453_Picture1.png" width="100" style="max-width: 240px; height: auto; display: block;" alt="set3-image-icon.png" border="0" hspace="0" vspace="0" height="auto"></a>
                                      </td>
                                    </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                      </tr><!-- end space --></tbody></table></td>
                              </tr><!-- end header image --><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                              </tr><!-- end space --><!-- start content --><tr><td valign="top">
                                  <table width="100%" align="center" border="0" cellspacing="0" cellpadding="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr dup="0"><td valign="top" align="center">
                                        <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr dup="0"><td valign="top">
                                              <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td align="center" style="font-size: 14px; color: rgb(255, 255, 255); font-weight: normal; text-align: center; font-family: Roboto, Arial, Helvetica, sans-serif; word-break: break-word; line-height: 22px;"><br></td>
                                                </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                                </tr><!-- end space --><tr><td align="center" style="font-size: 28px; color: rgb(255, 255, 255); font-weight: normal; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; word-break: break-word; line-height: 36px;"><span style="font-family: helvetica, "arial sans-serif" !important; font-size: 21px; word-break: break-word; line-height: 29px;"></span><br><span style="font-family: helvetica, "arial sans-serif" !important; font-size: 21px; word-break: break-word; line-height: 29px;"><span style="color: rgb(230, 227, 227); font-size: inherit; line-height: 29px;">Your Dashboard Login OTP</span>.</span><br></td>
                                                </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                                </tr><!-- end space --></tbody></table></td>
                                          </tr><!-- start content --><!-- end content --><!-- start space --><tr><td valign="top" height="20" style="height: 20px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                                          </tr><!-- end space --></tbody></table></td>
                                    </tr><!-- start button --><tr><td valign="top">
                                        <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" align="center" style="padding:5px;" class="full-block-479" dup="0">
                                              <table width="auto" border="0" align="center" cellpadding="0" cellspacing="0" style="margin:0 auto;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" style="border-radius: 24px; background-color: #52d2a9;">
                                                    <table width="auto" align="center" border="0" cellpadding="0" cellspacing="0" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td width="auto" align="center" valign="middle" height="42" style="min-width: 80px; font-size: 14px; font-family: Roboto, Arial, Helvetica, sans-serif; text-align: center; color: rgb(255, 255, 255); font-weight: normal; padding-left: 25px; padding-right: 25px; background-clip: padding-box; word-break: break-word; line-height: 22px;"><span style="font-size: 14px; line-height: 22px;">' . $otp . '<br></span></td>
                                                      </tr></tbody></table></td>
                                                </tr></tbody></table></td></tr></tbody></table></td>
                                    </tr><!-- end button --></tbody></table></td>
                              </tr><!-- end content --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="77" style="height: 77px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!--[if gte mso 9]>				</v:textbox>				</v:rect>				<![endif]--><!-- end container --></td>
            </tr><!--END LAYOUT-2 (LOGO/CONTENT AND BUTTON) --></tbody><!-- START LAYOUT-13 ( FULL-IMAGE / TEXT ) --><tbody><tr><td valign="top" align="center" style="background-color:#ffffff;" class="container">
                <!-- start container -->
                <table width="600" align="center" border="0" cellspacing="0" cellpadding="0" class="container" style="min-width: 600px; margin: 0px auto; padding-left: 20px; padding-right: 20px; background-color: #ffffff; width: 600px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><tbody><tr><td valign="top" align="center">
                      <table width="560" border="0" cellspacing="0" cellpadding="0" align="center" class="full-width" style="margin: 0px auto; width: 560px;mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start space --><tbody><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --><tr><td valign="top" align="center">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center" style="mso-table-lspace:0pt; mso-table-rspace:0pt;"><!-- start image --><tbody><!-- end image --><!-- start title --><!-- end title --><!-- start description --><!-- end description --><!-- start content --><!-- end content --></tbody></table></td>
                        </tr><!-- start space --><tr><td valign="top" height="1" style="height: 1px; font-size: 0px; line-height: 0; border-collapse: collapse;">&nbsp;</td>
                        </tr><!-- end space --></tbody></table></td>
                  </tr></tbody></table><!-- end container --></td>
            </tr><!-- END LAYOUT-13 ( FULL-IMAGE / TEXT ) --></tbody><!-- START LAYOUT-5 ( CONTENT SOCIAL ) --><tbody><tr><td valign="top" align="center" class="container" style="background-color: #ffffff;">
              </table></body>
    </html>';

    $fromMail = 'noreply@nanoheal.com';
    $fromName = 'Nanoheal';
    $emailName = explode('@', $email)[0];

    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $email,
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

function group_by($key, $array)
{
    //  echo '<pre>'.print_r($array,1).'</pre>';
    $result = array();

    foreach ($array as $val) {
        //  if (array_key_exists($key, $val)) {
        $result[$val[$key]][] = $val;
        //   } else {
        //       $result[""][] = $val;
        //   }
    }

    //    echo '<pre>' . print_r($result, 1) . '</pre>';
    return $result;
}

function triggerModulesBasedOnType($routes = array())
{
    return;
    if (url::getToText('function') === 'uploadProfileDatawithimg') {
        call_user_func('uploadProfileDatawithimg');
    }
    if (url::postToText('function') == 'profiledata') {
        call_user_func('profiledata');
    }
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'POST':
            if (url::issetInPost('function')) {
                if (hash_equals($_SESSION['token'], $_COOKIE['usertoken'])) {
                    if (array_key_exists(url::postToText('function'), $routes['post'])) {
                        $function = $routes['post'][url::postToText('function')];
                        call_user_func($function);
                    } else {
                        $resp = ['status' => 'failed', 'msg' => 'Invalid Request!'];
                    }
                } else {
                    $resp = ['status' => 'failed', 'msg' => 'Invalid Token!'];
                }
            }
            break;
        case 'GET':
            if (url::issetInGet('function')) {
                if (hash_equals($_SESSION['token'], $_COOKIE['usertoken'])) {
                    if (array_key_exists(url::getToAny('function'), $routes['get'])) {
                        $function = $routes['get'][url::getToAny('function')];
                        call_user_func($function);
                    } else {
                        $resp = ['status' => 'failed', 'msg' => 'Invalid Request!'];
                    }
                } else {
                    $resp = ['status' => 'failed', 'msg' => 'Invalid Token!'];
                }
            }
            break;
        case 'PUT':
            break;
        case 'DELETE':
            if (url::issetInRequest('function')) {
                if (hash_equals($_SESSION['token'], $_COOKIE['usertoken'])) {
                    if (array_key_exists(url::requestToAny('function'), $routes['delete'])) {
                        $function = $routes['delete'][url::requestToAny('function')];
                        call_user_func($function);
                    } else {
                        $resp = ['status' => 'failed', 'msg' => 'Invalid Request!'];
                    }
                } else {
                    $resp = ['status' => 'failed', 'msg' => 'Invalid Token!'];
                }
            }
            break;

        default:
            $resp = ['status' => 'failed', 'msg' => 'Invalid Request!'];
            break;
    }
    print_r($resp);
}

function auditInformation($data)
{
    return true;
}

function GetTimeZone()
{
    $timezones = array(
        'Pacific/Midway' => "(GMT-11:00) Midway Island",
        'US/Samoa' => "(GMT-11:00) Samoa",
        'US/Hawaii' => "(GMT-10:00) Hawaii",
        'US/Alaska' => "(GMT-09:00) Alaska",
        'US/Pacific' => "(GMT-08:00) Pacific Time (US &amp; Canada)",
        'America/Tijuana' => "(GMT-08:00) Tijuana",
        'US/Arizona' => "(GMT-07:00) Arizona",
        'US/Mountain' => "(GMT-07:00) Mountain Time (US &amp; Canada)",
        'America/Chihuahua' => "(GMT-07:00) Chihuahua",
        'America/Mazatlan' => "(GMT-07:00) Mazatlan",
        'America/Mexico_City' => "(GMT-06:00) Mexico City",
        'America/Monterrey' => "(GMT-06:00) Monterrey",
        'Canada/Saskatchewan' => "(GMT-06:00) Saskatchewan",
        'US/Central' => "(GMT-06:00) Central Time (US &amp; Canada)",
        'US/Eastern' => "(GMT-05:00) Eastern Time (US &amp; Canada)",
        'US/East-Indiana' => "(GMT-05:00) Indiana (East)",
        'America/Bogota' => "(GMT-05:00) Bogota",
        'America/Lima' => "(GMT-05:00) Lima",
        'America/Caracas' => "(GMT-04:30) Caracas",
        'Canada/Atlantic' => "(GMT-04:00) Atlantic Time (Canada)",
        'America/La_Paz' => "(GMT-04:00) La Paz",
        'America/Santiago' => "(GMT-04:00) Santiago",
        'Canada/Newfoundland' => "(GMT-03:30) Newfoundland",
        'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
        'Greenland' => "(GMT-03:00) Greenland",
        'Atlantic/Stanley' => "(GMT-02:00) Stanley",
        'Atlantic/Azores' => "(GMT-01:00) Azores",
        'Atlantic/Cape_Verde' => "(GMT-01:00) Cape Verde Is.",
        'Africa/Casablanca' => "(GMT) Casablanca",
        'Europe/Dublin' => "(GMT) Dublin",
        'Europe/Lisbon' => "(GMT) Lisbon",
        'Europe/London' => "(GMT) London",
        'Africa/Monrovia' => "(GMT) Monrovia",
        'Europe/Amsterdam' => "(GMT+01:00) Amsterdam",
        'Europe/Belgrade' => "(GMT+01:00) Belgrade",
        'Europe/Berlin' => "(GMT+01:00) Berlin",
        'Europe/Bratislava' => "(GMT+01:00) Bratislava",
        'Europe/Brussels' => "(GMT+01:00) Brussels",
        'Europe/Budapest' => "(GMT+01:00) Budapest",
        'Europe/Copenhagen' => "(GMT+01:00) Copenhagen",
        'Europe/Ljubljana' => "(GMT+01:00) Ljubljana",
        'Europe/Madrid' => "(GMT+01:00) Madrid",
        'Europe/Paris' => "(GMT+01:00) Paris",
        'Europe/Prague' => "(GMT+01:00) Prague",
        'Europe/Rome' => "(GMT+01:00) Rome",
        'Europe/Sarajevo' => "(GMT+01:00) Sarajevo",
        'Europe/Skopje' => "(GMT+01:00) Skopje",
        'Europe/Stockholm' => "(GMT+01:00) Stockholm",
        'Europe/Vienna' => "(GMT+01:00) Vienna",
        'Europe/Warsaw' => "(GMT+01:00) Warsaw",
        'Europe/Zagreb' => "(GMT+01:00) Zagreb",
        'Europe/Athens' => "(GMT+02:00) Athens",
        'Europe/Bucharest' => "(GMT+02:00) Bucharest",
        'Africa/Cairo' => "(GMT+02:00) Cairo",
        'Africa/Harare' => "(GMT+02:00) Harare",
        'Europe/Helsinki' => "(GMT+02:00) Helsinki",
        'Europe/Istanbul' => "(GMT+02:00) Istanbul",
        'Asia/Jerusalem' => "(GMT+02:00) Jerusalem",
        'Europe/Kiev' => "(GMT+02:00) Kyiv",
        'Europe/Minsk' => "(GMT+02:00) Minsk",
        'Europe/Riga' => "(GMT+02:00) Riga",
        'Europe/Sofia' => "(GMT+02:00) Sofia",
        'Europe/Tallinn' => "(GMT+02:00) Tallinn",
        'Europe/Vilnius' => "(GMT+02:00) Vilnius",
        'Asia/Baghdad' => "(GMT+03:00) Baghdad",
        'Asia/Kuwait' => "(GMT+03:00) Kuwait",
        'Africa/Nairobi' => "(GMT+03:00) Nairobi",
        'Asia/Riyadh' => "(GMT+03:00) Riyadh",
        'Europe/Moscow' => "(GMT+03:00) Moscow",
        'Asia/Tehran' => "(GMT+03:30) Tehran",
        'Asia/Baku' => "(GMT+04:00) Baku",
        'Europe/Volgograd' => "(GMT+04:00) Volgograd",
        'Asia/Muscat' => "(GMT+04:00) Muscat",
        'Asia/Tbilisi' => "(GMT+04:00) Tbilisi",
        'Asia/Yerevan' => "(GMT+04:00) Yerevan",
        'Asia/Kabul' => "(GMT+04:30) Kabul",
        'Asia/Karachi' => "(GMT+05:00) Karachi",
        'Asia/Tashkent' => "(GMT+05:00) Tashkent",
        'Asia/Kolkata' => "(GMT+05:30) Kolkata",
        'Asia/Kathmandu' => "(GMT+05:45) Kathmandu",
        'Asia/Yekaterinburg' => "(GMT+06:00) Ekaterinburg",
        'Asia/Almaty' => "(GMT+06:00) Almaty",
        'Asia/Dhaka' => "(GMT+06:00) Dhaka",
        'Asia/Novosibirsk' => "(GMT+07:00) Novosibirsk",
        'Asia/Bangkok' => "(GMT+07:00) Bangkok",
        'Asia/Jakarta' => "(GMT+07:00) Jakarta",
        'Asia/Krasnoyarsk' => "(GMT+08:00) Krasnoyarsk",
        'Asia/Chongqing' => "(GMT+08:00) Chongqing",
        'Asia/Hong_Kong' => "(GMT+08:00) Hong Kong",
        'Asia/Kuala_Lumpur' => "(GMT+08:00) Kuala Lumpur",
        'Australia/Perth' => "(GMT+08:00) Perth",
        'Asia/Singapore' => "(GMT+08:00) Singapore",
        'Asia/Taipei' => "(GMT+08:00) Taipei",
        'Asia/Ulaanbaatar' => "(GMT+08:00) Ulaan Bataar",
        'Asia/Urumqi' => "(GMT+08:00) Urumqi",
        'Asia/Irkutsk' => "(GMT+09:00) Irkutsk",
        'Asia/Seoul' => "(GMT+09:00) Seoul",
        'Asia/Tokyo' => "(GMT+09:00) Tokyo",
        'Australia/Adelaide' => "(GMT+09:30) Adelaide",
        'Australia/Darwin' => "(GMT+09:30) Darwin",
        'Asia/Yakutsk' => "(GMT+10:00) Yakutsk",
        'Australia/Brisbane' => "(GMT+10:00) Brisbane",
        'Australia/Canberra' => "(GMT+10:00) Canberra",
        'Pacific/Guam' => "(GMT+10:00) Guam",
        'Australia/Hobart' => "(GMT+10:00) Hobart",
        'Australia/Melbourne' => "(GMT+10:00) Melbourne",
        'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
        'Australia/Sydney' => "(GMT+10:00) Sydney",
        'Asia/Vladivostok' => "(GMT+11:00) Vladivostok",
        'Asia/Magadan' => "(GMT+12:00) Magadan",
        'Pacific/Auckland' => "(GMT+12:00) Auckland",
        'Pacific/Fiji' => "(GMT+12:00) Fiji",
    );
    return $timezones;
}

function checkUserSiteAccess($username, $parentName)
{
    $db = pdo_connect();

    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Customers where username = ? and customer = ? ");
    $sql->execute([$username, $parentName]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    if ($res) {
        return true;
    } else {
        return false;
    }
}

function checkUserGroupAccess($username, $parentName)
{
    $db = pdo_connect();

    $sql = $db->prepare("SELECT mg.name FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,core.GroupMappings gm WHERE mg.style=? and mg.mgroupid = gm.groupid and gm.username = ? ");
    $sql->execute([$parentName, $username]);
    $res = $sql->fetchAll();

    if ($res) {
        return true;
    } else {
        return false;
    }
}

function checkGroupAccess($mId, $type)
{
    $pdo = pdo_connect();
    $userName = $_SESSION['user']['username'];
    if ($type == 'grpname') {
        $grpId = strip_tags($mId);

        $sql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE lower(name) = ?");
        $sql->execute([strtolower($grpId)]);
        $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    } else {
        $grpId = strip_tags($mId);

        $sql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE mgroupid = ?");
        $sql->execute([strtolower($grpId)]);
        $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    }
  if ($sqlRes['username'] == $userName) {
    return true;
  } else {
    $sql2 = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.GroupMappings WHERE username = ? AND groupid = ?");
    $sql2->execute([$userName, $grpId]);
    $checkName = $sql2->fetch();
    if ($userName === $checkName['username']) {
      return true;
    } else {
      return false;
    }
  }
}


function largeDataPagination($total_records = "", $total_records_per_page = 10, $limitEnd = '', $curPage = 0, $nocName = '')
{
    //   $total_records_per_page = 5;
    //    echo $total_records . ', ' . $total_records_per_page . ', ' . $limitEnd . ', ' . $curPage . ', ' . $nocName;
    $curPage = $curPage + 1;
    // echo "Total Records::".$total_records.PHP_EOL;
    // echo "Total Records per page::".$total_records_per_page.PHP_EOL;
    $total_no_of_pages = ceil($total_records / $total_records_per_page);
    // print_r($total_no_of_pages);exit;
    $second_last = $total_no_of_pages - 1;
    $return = '<div id="notifyDtl_wrapper" class="dataTables_wrapper dt-bootstrap4">
        <div class="bottom">
            <div class="dataTables_length text-left" id="notifyDtl_length" style="display: flex"><label>Show <select id="notifyDtl_lengthSel" name="notifyDtl_length" class="custom-select custom-select-sm form-control form-control-sm">';
    $options = array(10, 25, 50, 100);
    foreach ($options as $opt) {
        $selected = ($opt == $total_records_per_page) ? ' selected ' : '';
        $return .= ' <option ' . $selected . ' value="' . $opt . '">' . $opt . '</option>';
    }

    $startRange = ($total_records_per_page * $curPage) - ($total_records_per_page - 1);
    $finishRange = ($total_records_per_page * $curPage < $total_records) ? $total_records_per_page * $curPage : $total_records;
    $return .= '</select> entries</label><div id="rangeValues" style="margin-left: 40px; font-size: initial;">' . $startRange . '-' . $finishRange . ' of ' . $total_records .'</div> </div>';

    $return .= '<div class="dataTables_paginate paging_full_numbers " id="notifyDtl_paginate">';
    $return .= '<ul class="pagination">';

    $disabled = '';
    if ($curPage <= 1) {
        $disabled = ' disabled ';
    }

    $return .= '<li class="paginate_button page-item first ' . $disabled . '" id="notifyDtl_first"><a href="#" data-pgno="1" data-dt-idx="0"  data-name="' . $nocName . '" class="page-link">First</a></li>';

    $disabled = '';
    if ($curPage <= 1) {
        $disabled = ' disabled ';
    }
    $return .= '<li class="paginate_button page-item previous ' . $disabled . '" id="notifyDtl_previous"><a data-pgno="' . ($curPage - 1) . '" data-name="' . $nocName . '" href="#"  class="page-link">Previous</a></li>';
    // echo "Total no of pages:::".$total_no_of_pages.PHP_EOL;
    // echo "Total records per page:::".$total_records_per_page.PHP_EOL;exit;
    if ($total_no_of_pages <= $total_records_per_page) {
        for ($counter = 1; $counter <= $total_no_of_pages; $counter++) {
            //  echo $counter;
            if ($counter == $curPage) {
                $return .= '<li class="paginate_button page-item active"><a href="#"  class="page-link" data-pgno="' . $counter . '" data-name="' . $nocName . '">' . $counter . '</a></li>';
            } else {
                $return .= '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' . $counter . '" data-name="' . $nocName . '">' . $counter . '</a></li>';
            }
        }
    } elseif ($total_no_of_pages > $total_records_per_page) {
        if ($curPage <= 8) {
            // echo "gg";
            for ($counter = 1; $counter <= 8; $counter++) {
                if ($counter == $curPage) {
                    $return .= '<li class="paginate_button page-item active"><a href="#"  class="page-link" data-name="' . $nocName . '" data-name="' . $nocName . '">' . $counter . '</a></li>';
                } else {
                    $return .= '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' . $counter . '" data-name="' . $nocName . '">' . $counter . '</a></li>';
                }
            }
            /*             $return  .=  "<li><a>...</a></li>";
        $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' .  $second_last . '">' .  $second_last . '</a></li>';
        $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' . $total_no_of_pages . '">' . $total_no_of_pages . '</a></li>'; */
        } elseif ($curPage > 8 && $curPage < $total_no_of_pages - 8) {
            // echo "this";
            /*             $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="1">1</a></li>';
            $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="2">2</a></li>';
            $return  .=  "<li><a>...</a></li>"; */
            for ($counter = $curPage - 4; $counter <= $curPage + 4; $counter++) {
                if ($counter == $curPage) {
                    $return .= '<li class="paginate_button page-item active"><a href="#"  class="page-link" data-name="' . $nocName . '">' . $counter . '</a></li>';
                } else {
                    $return .= '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' . $counter . '" data-name="' . $nocName . '" data-name="' . $nocName . '">' . $counter . '</a></li>';
                }
            }
            /*             $return  .=  "<li><a>...</a></li>";
        $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' .  $second_last . '">' .  $second_last . '</a></li>';
        $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' . $total_no_of_pages . '">' . $total_no_of_pages . '</a></li>'; */
        } else {
            /*             $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="1">1</a></li>';
            $return  .=  '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="2">2</a></li>';
            $return  .=  "<li><a>...</a></li>"; */
            for ($counter = $total_no_of_pages - 8; $counter <= $total_no_of_pages; $counter++) {
                // echo "counter::".$counter.PHP_EOL;
                // echo "total no of pages::".$total_no_of_pages.PHP_EOL;
                if ($counter == $curPage) {
                    $return .= '<li class="paginate_button page-item active"><a href="#"  class="page-link" data-name="' . $nocName . '">' . $counter . '</a></li>';
                } else {
                    $return .= '<li class="paginate_button page-item  "><a href="#"  class="page-link" data-pgno="' . $counter . '" data-name="' . $nocName . '">' . $counter . '</a></li>';
                }
            }
        }
    }

    $disabled = '';
    if ($curPage >= $total_no_of_pages) {
        $disabled = ' disabled ';
    }
    $return .= '<li class="paginate_button page-item next ' . $disabled . ' " id="notifyDtl_next"><a href="#" data-dt-idx="3"  class="page-link" data-pgno="' . ($curPage + 1) . '"  data-name="' . $nocName . '">Next</a></li>';

    if ($curPage >= $total_no_of_pages) {
        $disabled = ' disabled ';
    }
    $return .= '<li class="paginate_button page-item last  ' . $disabled . '" id="notifyDtl_last"><a href="#" data-dt-idx="4"  class="page-link" data-pgno="' . $total_no_of_pages . '" data-name="' . $nocName . '">Last</a></li>';
    $return .= '</ul></div>

        </div>
    </div>';
    return $return;
}
