<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1); 

require_once(getcwd() . '/../../config.php');

include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-dberr.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
//include ( 'header.php' );
include('../lib/l-errs.php');
include('../lib/l-svbt.php');
include('../lib/l-config.php');


global $global_cid;
$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$action = get_argument('action', 0, 'add');
//print_r($_REQUEST);exit;
$params =  get_argument('params', 0, '');
$authuser = 'hfn'; //install_login($db);
//$authuserdata = install_user($authuser, $db);
$sql = "select * from Users where\n";
$sql .= " installuser='$authuser'";
$res = command($sql, $db);
if ($res) {
    if (mysqli_num_rows($res) == 1) {
        $authuserdata = mysqli_fetch_array($res);
        mysqli_free_result($res);
    }
}
//echo  $authuserdata['installuserid'];
// !!
$authid = $authuserdata['installuserid'];
$auth_admin = @($authuserdata['priv_admin']) ? 1 : 0;
$auth_servers = @($authuserdata['priv_servers']) ? 1 : 0;
$auth_email = @($authuserdata['priv_email']) ? 1 : 0;
$id = get_argument('id', 0, 0);

logs::log("GET: " . $action, $params);

switch ($action) {
    case 'getCustomer':
        get_configuredCustomerApi($authid, $db);
        break;
    case 'getSitesList':
        get_SiteListApi($db, $params);
        break;
    case 'getSiteList':
        get_SiteListIdApi($id, $db);
        break;

    case 'getSubscriptionList':
        get_SubscriptionList($db);
        break;
    case 'insertSite':
        insert_siteApi($authuser, $authid, $id, $db);
        break;
    case 'updateSite':
        update_siteApi($id, $authuser, $db);
        break;

    case 'listservers':
        get_serversApi($authid, $db);
        break;
    case 'listsku':
        $custId = get_argument('custId', 0, 0);
        get_configuredSkuApi($custId, $db);
        break;
}

function install_user($name, $db)
{
    $row = array();
    $sql = "select * from Users where\n";
    $sql .= " installuser='$name'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
            mysqli_free_result($res);
        }
    }
    return $row;
}

/**
 * @warn SQL Injection Issues
 */
function get_SiteListApi($db, $params)
{
    $params = safe_json_decode($params, true);
    $limitCount = (intval($params['limitCount']) == 0) ? 10 : intval($params['limitCount']);
    $curPage = intval($params['nextPage']) - 1;
    $orderVal = $params['order'] === 'skuname' ? 'skuOfferings.name' : $params['order'];
    $sortVal = $params['sort'];

    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    $notifSearch = trim($params['notifSearch']);
    if ($notifSearch != '') {
        /*$whereSearch = " and (S.sitename like '%$notifSearch%'
		OR U.installuser like '%$notifSearch%'
		OR SE.createdtime like '%$notifSearch%'
		OR C.customer_name like '%$notifSearch%'
		OR SK.name like '%$notifSearch%') "; */

        $whereSearch = " and (Sites.sitename like '%$notifSearch%'
		OR Sites.username like '%$notifSearch%'
		OR Sites.firstcontact like '%$notifSearch%'
		OR Customers.customer_name like '%$notifSearch%'
		OR skuOfferings.name like '%$notifSearch%') ";
    } else {
        $whereSearch = '';
    }

    if ($orderVal != '') {
        $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
    } else {
        $orderStr = 'order by Sites.sitename asc';
    }
    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }

    $sitesList = [];
    $sql = "select  Sites.siteid,Sites.sitename,Sites.username,Sites.firstcontact,Sites.lastcontact,Customers.cid,Customers.customer_name,skuOfferings.name as skuname
	    from " . $GLOBALS['PREFIX'] . "install.Sites," . $GLOBALS['PREFIX'] . "install.Customers," . $GLOBALS['PREFIX'] . "install.skuOfferings where Customers.cid = Sites.cid and Sites.skuids = skuOfferings.sid $whereSearch $orderStr $limitStr";
    logs::log(__FILE__, __LINE__, $sql, 0);
    //$sql = "select S.siteid, S.sitename, U.installuser, SE.createdtime, C.customer_name, SK.name as skuname from " . $GLOBALS['PREFIX'] . "install.Sites S, Customers C, skuOfferings SK, Siteemail SE, Users U
    //where C.cid = S.cid and S.skuids = SK.sid and SE.email = S.email and SE.siteid = S.siteid and S.installuserid = U.installuserid $whereSearch $orderStr $limitStr";
    $res = command($sql, $db);
    $sql2 = "select  count(*) as count from " . $GLOBALS['PREFIX'] . "install.Sites," . $GLOBALS['PREFIX'] . "install.Customers," . $GLOBALS['PREFIX'] . "install.skuOfferings where Customers.cid = Sites.cid and Sites.skuids = skuOfferings.sid $whereSearch $orderStr";
    //$sql2 = "select count(*) as count from " . $GLOBALS['PREFIX'] . "install.Sites S, Customers C, skuOfferings SK, Siteemail SE, Users U where C.cid = S.cid and S.skuids = SK.sid and SE.email = S.email and SE.siteid = S.siteid and S.installuserid = U.installuserid $whereSearch $orderStr";
    $res2 = command($sql2, $db);
    $row2 = mysqli_fetch_assoc($res2);
    logs::log(__FILE__, __LINE__, $sql2, 0);
    while ($row = mysqli_fetch_assoc($res)) {
        $sitesList['data'][] = $row;
        $sitesList['totCount'] = $row2['count'];
    }

    echo json_encode($sitesList);
}

function get_SubscriptionList($db)
{
    $sitesList = [];
    $sql = "select S.siteid, S.sitename, U.installuser, SE.createdtime, SK.name as skuname,
SK.description as description,SK.category as category,SK.quantity as total,SK.amount as used,SK.trialperiod as period ,
SK.billingtype as billingtype,SK.trialperiod as trialperiod,SK.billingcycle as billingcycle,SK.sid as licenseid
from " . $GLOBALS['PREFIX'] . "install.Sites S, skuOfferings SK, Siteemail SE, Users U where S.skuids = SK.sid
and SE.email = S.email and SE.siteid = S.siteid and S.installuserid = U.installuserid order by SE.siteemailid";
    $res = command($sql, $db);
    while ($row = mysqli_fetch_assoc($res)) {
        $sitesList[] = $row;
    }

    echo json_encode($sitesList);
}

function __encrypt($string)
{

    $pubKey = '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA4FnCjLL6vuJr1miqS54Y
UhpziZTLQkROs3lylUG0HgQW6bzXyoSG/6U2MZwmmSSfB9dZSSOPuLEsErU3jFR5
UxNHJBQ+t29/kDDsPqLcD1aiXNlPGm4nySERsKk6UP4ixUXJ4hYo1qZoJh9D3BCB
g/hJPEIXXKzu33UAuTwbHowhGW1dN6gHqhqFQ0ukGrZ4imGt1k7jk9LqOc27WnFN
3T0X5Lq3l7lp0s0WwpJKBrFdjIGnjdgDOw9YjXHbpeRmYCZmag/gjm5P1t7v/MSh
9AxVshQ9wZazCJRFI1MqzRQ1pYsU+sInEqiIZh8ktSDKHAtMHGVH7WswKez6DxO4
qWjXeLZcWagb57Ag3pJsS7+cy9Chq5crATlDg5urbYvN9hz/AMGDRKg6YOO9pyFH
frPRG1dGLwt04HV1NZG31tQM3ghmoaLBlwjgHf0Qk+zu5kHhvXD2Nflv3KFJ0mCh
vR6jhp8JIEI/O7KVnfGk/IoMitqkTLmHWQJQvqTD+ngoVGRgyDjtxvTVrnX2NeZ/
5GRWmKVVxxDO1FeJwQTiMjOLXP9Aw5LQq7jUCjrGNL37fwP0TdsftOJSp70m72e3
FsYYhsiTTeQ74K7TgwDl0tnhaZnrw5Iii6TQ7S0GatUuOAIpheJkX+bD5XWWxR8a
C2kMYCAcmgQ4kbnUMrGjx1ECAwEAAQ==
-----END PUBLIC KEY-----';

    // Encrypt the data to $encrypted using the public key
    openssl_public_encrypt($string, $encrypted, $pubKey);

    $enkey = base64_encode($encrypted);
    return $enkey;
}

function get_SiteListIdApi($id, $db)
{
    // echo "Get sites List";
    $sitesList = [];
    $sql = "select  Sites.siteid,Sites.sitename,Sites.username,Sites.firstcontact,Sites.lastcontact,Customers.cid,Customers.customer_name  from " . $GLOBALS['PREFIX'] . "install.Sites,Customers where Sites.cid = Customers.cid and Sites.siteid = " . $id;
    $res = command($sql, $db);
    while ($row = mysqli_fetch_assoc($res)) {

        $sitesList[] = $row;
    }

    echo json_encode($sitesList);
}

function get_serversApi($installuserid, $db)
{
    $servers = [];
    $sql = "SELECT * FROM Servers ORDER BY servername"; //WHERE  global = 1 OR installuserid = $installuserid

    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $servers[$row['serverid']] = $row['servername'];
            }
        }
        mysqli_free_result($res);
    }
    echo json_encode($servers);
}

function get_configuredCustomerApi($installuserid, $db)
{
    global $global_cid;
    $couter = 0;
    $offerings = [];
    $offerSql = "SELECT * FROM Customers order by customer_name"; //where tenant_id = '$installuserid'
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings[$row['cid']] = $row['customer_name'];
                if ($couter === 0) {
                    $global_cid = $row['cid'];
                }
                $couter++;
            }
        }
        mysqli_free_result($offerRes);
    }
    echo json_encode($offerings);
}

function get_configuredSkuApi($selectedCustId, $db)
{
    global $global_cid;
    $skulist = [];
    $offerings = [];

    if ($selectedCustId == '') {
        $selectedCustId = $global_cid;
    }

    $sql = "SELECT sku_list FROM Customers WHERE cid = '$selectedCustId'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $skulist[] = $row['sku_list'];
            }
        }
        mysqli_free_result($res);
    }


    $offerids = implode(',', $skulist);
    $offerSql = "SELECT * FROM skuOfferings where sid IN ($offerids)";
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings[$row['sid']] = $row['name'];
            }
        }
        mysqli_free_result($offerRes);
    }

    echo json_encode($offerings);
}

function insert_siteApi($authuser, $userid, $id, $db, $regen = 0)
{
    $sql_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $sitename = trim(get_argument('sitename', 1, ''));
    $domain = trim(get_argument('domain', 1, ''));
    $username = trim(get_argument('username', 1, ''));
    $password = trim(get_argument('password', 0, ''));
    $confirm_pwd = trim(get_argument('confirmpassword', 0, ''));
    $email = trim(get_argument('email', 1, ''));
    $serverid = intval(get_argument('serverid', 1, 0));
    $cid = intval(get_argument('custlist', 1, 0));
    $proxy = trim(get_argument('proxy', 0, ''));
    $startupid = trim(get_argument('startupid', 1, 'All'));
    $followonid = trim(get_argument('followonid', 1, 'All'));
    $delay_days = intval(get_argument('delay_days', 0, 0));
    $delay_hrs = intval(get_argument('delay_hrs', 0, 0));
    $delay_mins = intval(get_argument('delay_mins', 0, 0));
    $delay_on = intval(get_argument('delay_on', 0, 0));
    $deployPath32 = trim(get_argument('deploypath32', 0, ''));
    $deployPath64 = trim(get_argument('deploypath64', 0, ''));
    $fcmUrl = trim(get_argument('fcmUrl', 0, ''));
    $emailbounce = trim(get_argument('emailbounce', 0, ''));
    $urldownload = trim(get_argument('urldownload', 0, ''));
    $messagetext = trim(get_argument('messagetext', 1, ''));
    $emailsubject = trim(get_argument('emailsubject', 1, ''));
    $emailsender = trim(get_argument('emailsender', 1, ''));
    $emailxheaders = trim(get_argument('emailxheaders', 1, ''));
    $uninstall = ($followonid == 'Uninstall') ? 1 : 0;
    $skuids = intval(get_argument('skulist', 1, 0));

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }

    // check for blank site name
    if (!strlen($sitename)) {
        $msg = "Site name cannot be blank.";
    } else if (strpos($sitename, " ") !== false) {
        $msg = "Site name cannot contain space";
    }

    // check for password
    if ($msg == '') {
        if (strlen($password) || strlen($confirm_pwd)) {
            $response = check_pwd_change($db, $username, 0, '', $password, $confirm_pwd);
            if ($response == "success")
                $sql_pwd = " password='" . md5($password) . "',\n";
            elseif (strlen(trim($response)))
                $msg = $response;
            else
                $msg = "There was a problem with this update. Please try again.";
        } else { // use default sitepassword if it matches the (non-blank) siteusername.
            if (strlen($username)) {
                $userdata = get_user_data($userid, $db);
                $siteusername = $userdata['siteusername'];
                $sitepassword = $userdata['sitepassword'];
                if ($username == $siteusername) {
                    $password = $userdata['password'];
                    $sql_pwd = " password='$password',\n";
                }
            }
        }
    }

    // upload enhancement start

    $client32Upload = $client64Upload = $clientApkUpload = $clientMacUpload = $clientIosUpload = $clientLinuxUpload = false;
    $client32Name = 'executable_client_32';
    $client64Name = 'executable_client_64';
    $clientApkName = 'executable_client_apk';
    $clientMacName = 'executable_client_mac';
    $clientIosName = 'executable_client_ios';
    $clientLinuxName = 'executable_client_linux';

    if (isset($_FILES[$client32Name]) && isset($_FILES[$client32Name]['name']) && !empty($_FILES[$client32Name]['name'])) {
        if (!isset($_FILES[$client32Name]['error']) || $_FILES[$client32Name]['error'] != 0) {
            $msg = "Client 32 bit upload error";
        }
        $client32Upload = true;
        $client32FileName = $_FILES[$client32Name]['name'];
    }

    if (isset($_FILES[$client64Name]) && isset($_FILES[$client64Name]['name']) && !empty($_FILES[$client64Name]['name'])) {
        if (!isset($_FILES[$client64Name]['error']) || $_FILES[$client64Name]['error'] != 0) {
            $msg = "Client 64 bit upload error";
        }
        $client64Upload = true;
        $client64FileName = $_FILES[$client64Name]['name'];
    }

    if (isset($_FILES[$clientApkName]) && isset($_FILES[$clientApkName]['name']) && !empty($_FILES[$clientApkName]['name'])) {
        if (!isset($_FILES[$clientApkName]['error']) || $_FILES[$clientApkName]['error'] != 0) {
            $msg = "Android Client upload error";
        }

        $clientApkUpload = true;
        $clientApkFileName = $_FILES[$clientApkName]['name'];
    }

    if (isset($_FILES[$clientMacName]) && isset($_FILES[$clientMacName]['name']) && !empty($_FILES[$clientMacName]['name'])) {
        if (!isset($_FILES[$clientMacName]['error']) || $_FILES[$clientMacName]['error'] != 0) {
            $msg = "Mac Client upload error";
        }

        $clientMacUpload = true;
        $clientMacFileName = $_FILES[$clientMacName]['name'];
    }
    if (isset($_FILES[$clientIosName]) && isset($_FILES[$clientIosName]['name']) && !empty($_FILES[$clientIosName]['name'])) {
        if (!isset($_FILES[$clientIosName]['error']) || $_FILES[$clientIosName]['error'] != 0) {
            $msg = "Ios Client upload error";
        }

        $clientIosUpload = true;
        $clientIosFileName = $_FILES[$clientIosName]['name'];
    }

    if (isset($_FILES[$clientLinuxName]) && isset($_FILES[$clientLinuxName]['name']) && !empty($_FILES[$clientLinuxName]['name'])) {
        if (!isset($_FILES[$clientLinuxName]['error']) || $_FILES[$clientLinuxName]['error'] != 0) {
            $msg = "Linux Client upload error";
        }

        $clientLinuxUpload = true;
        $clientLinuxFileName = $_FILES[$clientLinuxName]['name'];
    }

    if ($client32Upload) {
        $upoadFtpData = uploadWithFtp($client32Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($client64Upload) {
        $upoadFtpData = uploadWithFtp($client64Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientApkUpload) {
        $upoadFtpData = uploadWithFtp($clientApkName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientMacUpload) {
        $upoadFtpData = uploadWithFtp($clientMacName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientIosUpload) {
        $upoadFtpData = uploadWithFtp($clientIosName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientLinuxUpload) {
        $upoadFtpData = uploadWithFtp($clientLinuxName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($msg == '') {
        //calculate delay
        $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;

        /// generate reg code
        $regcode = gen_regcode($sitename, $regen);

        $ssl = (isset($_SERVER['HTTPS'])) ? 1 : 0;
        $host = $_SERVER['HTTP_HOST'];
        $http = ($ssl) ? 'https' : 'http';

        $defBrandingUrl = $http . '://' . $host . 'Dashboard/Provision/install/cust_Default_Branding/cust_Default_Branding.zip';
        $urldownload = $http . '://' . $host . '/Provision/download/';
        // insert into Sites table [1]
        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "install.Sites SET\n";
        $sql .= " sitename='$sitename',\n";
        $sql .= " domain='$domain',\n";
        $sql .= " installuserid='$userid',\n";
        $sql .= " username='$username',\n";
        if (strlen(trim($sql_pwd)))
            $sql .= $sql_pwd;
        $sql .= " email='$email',\n";
        $sql .= " skuids=$skuids,\n";
        $sql .= " serverid=$serverid,\n";
        $sql .= " cid=$cid,\n";
        $sql .= " proxy='$proxy',\n";
        $sql .= " startupid='$startupid',\n";
        $sql .= " followonid='$followonid',\n";
        $sql .= " uninstall='$uninstall',\n";
        $sql .= " delay=$delay,\n";
        $sql .= " delayon=$delay_on,\n";
        $sql .= " deploypath32='$deployPath32',\n";
        $sql .= " deploypath64='$deployPath64',\n";
        $sql .= " fcmUrl='$fcmUrl',\n";
        $sql .= " emailbounce='$emailbounce',\n";
        $sql .= " urldownload='$urldownload',\n";
        $sql .= " messagetext='$messagetext',\n";
        $sql .= " emailsubject='$emailsubject',\n";
        $sql .= " emailsender='$emailsender',\n";
        $sql .= " emailxheaders='$emailxheaders',\n";
        $sql .= " regcode='$regcode',\n";
        $sql .= " firstcontact=" . time() . ",\n";
        $sql .= " brandingurl='$defBrandingUrl'\n";

        if ($client32Upload) {
            $sql .= ", client_32_name='$client32FileName'\n";
        }

        if ($client64Upload) {
            $sql .= ", client_64_name='$client64FileName'\n";
        }

        if ($clientApkUpload) {
            $sql .= ", client_android_name='$clientApkFileName'\n";
        }

        if ($clientMacUpload) {
            $sql .= ", client_mac_name='$clientMacFileName'\n";
        }

        if ($clientIosUpload) {
            $sql .= ", client_ios_name='$clientIosFileName'\n";
        }

        if ($clientLinuxUpload) {
            $sql .= ", client_linux_name='$clientLinuxFileName'\n";
        }

        $res = redcommand($sql, $db);
        TokenChecker::calcSites($sitename, 'sitename');

        $sitename = stripslashes($sitename);

        if (!$res) {
            $problem = 1;
            $sql_error = mysqli_error($db);
            $sql_errno = mysqli_errno($db);
            echo $sql_error;
            // check for duplicate site name or regcode
            $key_index1 = get_key_index('install', 'Sites', 'uniq', $db);
            $key_index2 = get_key_index('install', 'Sites', 'uniq2', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                // check for duplicate site name
                if (preg_match("/\b$key_index1\b/", $sql_error)) {
                    $xtra_msg = "The site name <b>$sitename</b> is a duplicate
                                    of an existing site name.";
                }
                // check for duplicate regcode
                elseif (preg_match("/\b$key_index2\b/", $sql_error)) {
                    // try again, with regen set to 1
                    insert_site($authuser, $userid, $id, $db, 1);
                }
            }
        }


        if ($problem) {
            $msg = "Unable to add site <b>$sitename</b>. $xtra_msg";
        } else {
            // Creating default site email entry for current user
            $message = "New site <b>$sitename</b> added.";
            $siteid = mysqli_insert_id($db);
            $siteemailid = insertDefaultSiteEmailEntry($userid, $siteid, $db);
            if ($siteemailid != 'EXIST') {
                $message .= "<br/>You have entered <b>1</b> new email $email for site <b>$sitename</b>.";
            }

            $userdata = get_user_data($userid, $db);
            $installuser = $userdata['installuser'];
            /* $asicon = SVBT_coreServerConnect($installuser, $db);
              if($asicon['status'] == 1) {
              SVBT_createASICoreCustomers($sitename, $email, $db, $asicon['dbcon']);
              } */

            $apisql = "select serverurl from Servers where serverid = '$serverid'";
            $apires = command($apisql, $db);

            if (mysqli_num_rows($apires) > 0) {
                $data = mysqli_fetch_array($apires);
                $apiurl = $data['serverurl'];
            }

            $cdata['function'] = 'createcustomer';
            $cdata['data']['sitename'] = $sitename;
            $cdata['data']['emailid'] = $email;
            MAKE_CURL_CALL($apiurl, $cdata);

            $sdata['function'] = 'createsite';
            $sdata['data']['sitename'] = $sitename;
            $sdata['data']['domain'] = $domain;
            $sdata['data']['userid'] = $userid;
            $sdata['data']['username'] = $username;
            $sdata['data']['password'] = $password;
            $sdata['data']['email'] = $email;
            $sdata['data']['serverid'] = $serverid;
            $sdata['data']['proxy'] = $proxy;
            $sdata['data']['startupid'] = $startupid;
            $sdata['data']['followonid'] = $followonid;
            $sdata['data']['uninstall'] = $uninstall;
            $sdata['data']['delay'] = $delay;
            $sdata['data']['delayon'] = $delay_on;
            $sdata['data']['deploypath32'] = $deployPath32;
            $sdata['data']['deploypath64'] = $deployPath64;
            $sdata['data']['emailbounce'] = $emailbounce;
            $sdata['data']['urldownload'] = $urldownload;
            $sdata['data']['messagetext'] = $messagetext;
            $sdata['data']['emailsubject'] = $emailsubject;
            $sdata['data']['emailsender'] = $emailsender;
            $sdata['data']['emailxheaders'] = $emailxheaders;
            $sdata['data']['regcode'] = $regcode;
            $sdata['data']['brandingurl'] = $defBrandingUrl;
            MAKE_CURL_CALL($apiurl, $sdata);

            $log = "install: Site '$sitename' added by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    }

    echo json_encode(array("message" => $msg));
}

function gen_regcode($sitename, $regen = 0)
{
    $step1 = md5($sitename);
    $step2 = hexdec(substr($step1, 0, 8));
    $positive = hexdec("7FFFFFFF");
    $step3 = $step2 & $positive;
    $step4 = $step3 % 1000000000;
    $servergen = hexdec("FFFFFFF0");
    $step5 = $step4 & $servergen;

    if ($regen) { // came back b/c previously generated reg code was a dup of existing.
        $log = "install: The regcode generated for site '$sitename' is a duplicate" .
            " of existing; generating new regcode.";
        logs::log(__FILE__, __LINE__, $log, 0);

        // seed with microseconds
        srand(make_seed());
        $rand = rand(1, 99);
        $incr = $rand * 16;   // use 16, not 1, since lowest 4 bits are reserved
        $step5 += $incr;
        $step5 = $step5 % 1000000000;  // in case it got too long
    }

    // pad number if less than 9 digits
    $number = sprintf("%09d", $step5);

    // get first 9 digits
    $dig1 = substr($number, 0, 1);
    $dig2 = substr($number, 1, 1);
    $dig3 = substr($number, 2, 1);
    $dig4 = substr($number, 3, 1);
    $dig5 = substr($number, 4, 1);
    $dig6 = substr($number, 5, 1);
    $dig7 = substr($number, 6, 1);
    $dig8 = substr($number, 7, 1);
    $dig9 = substr($number, 8, 1);

    // generate check digit
    $intermediate = ($dig1 * 10) + ($dig2 * 9) + ($dig3 * 8) + ($dig4 * 7) + ($dig5 * 6) + ($dig6 * 5) + ($dig7 * 4) + ($dig8 * 3) + ($dig9 * 2);
    $remainder = $intermediate % 11;
    $checkdig = (11 - $remainder) % 11;
    if ($checkdig == 10)
        $checkdig = 'X';

    $regcode = $dig1 . $dig2 . $dig3 . $dig4 . $dig5 . $dig6 . $dig7 . $dig8 . $dig9 . $checkdig;

    return $regcode;
}

function get_user_data($id, $db)
{
    $userdata = array();
    $sql = "SELECT * FROM Users WHERE installuserid = $id";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $userdata['installuserid'] = $row['installuserid'];
                $userdata['installuser'] = $row['installuser'];
                $userdata['password'] = $row['password'];
                $userdata['priv_servers'] = $row['priv_servers'];
                $userdata['priv_email'] = $row['priv_email'];
                $userdata['priv_admin'] = $row['priv_admin'];
                $userdata['siteusername'] = $row['siteusername'];
                $userdata['sitepassword'] = $row['sitepassword'];
                $userdata['email'] = htmlentities($row['email']);
                $userdata['serverid'] = $row['serverid'];
                $userdata['skuids'] = $row['skuids'];
                $userdata['proxy'] = $row['proxy'];
                $userdata['startupid'] = $row['startupid'];
                $userdata['followonid'] = $row['followonid'];
                $userdata['delay'] = $row['delay'];
                $userdata['messagetext'] = $row['messagetext'];
                $userdata['emailsubject'] = $row['emailsubject'];
                $userdata['emailsender'] = htmlentities($row['emailsender']);
                $userdata['emailxheaders'] = htmlentities($row['emailxheaders']);
                $userdata['emailbounce'] = htmlentities($row['emailbounce']);
                $userdata['urldownload'] = $row['urldownload'];
            }
        }
        mysqli_free_result($res);
    }
    return $userdata;
}

function insertDefaultSiteEmailEntry($userid, $siteid, $db)
{

    $userSql = "select installuser, installemailid from Users where installuserid = $userid limit 1";
    $userRes = command($userSql, $db);
    $userData = mysqli_fetch_array($userRes);
    $useremail = $userData['installemailid'];

    $sql = "select * from Siteemail where email = '$useremail' and siteid = $siteid and installuserid = $userid limit 1";
    $res = command($sql, $db);

    if (mysqli_num_rows($res) > 0) {
        $retval = 'EXIST';
    } else {
        $now = time();
        $sql = "INSERT INTO Siteemail SET";
        $sql .= " siteid=$siteid,\n";
        $sql .= " installuserid=$userid,\n";
        $sql .= " email='$useremail',\n";
        $sql .= " createdtime='$now'\n";
        $res = redcommand($sql, $db);

        $siteemailid = mysqli_insert_id($db);
        $retval = $siteemailid;
    }
    return $retval;
}

function update_siteAPI($id, $authuser, $db)
{
    $sql_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $sitename = trim(get_argument('sitename', 1, ''));
    $domain = trim(get_argument('domain', 1, ''));
    $username = trim(get_argument('username', 1, ''));
    $password = trim(get_argument('password', 0, ''));
    $confirm_pwd = trim(get_argument('confirmpassword', 0, ''));
    $email = trim(get_argument('email', 1, ''));
    $serverid = intval(get_argument('serverid', 1, 0));
    $skuids = intval(get_argument('skulist', 1, 0));
    $cid = intval(get_argument('custlist', 1, 0));
    $proxy = trim(get_argument('proxy', 0, ''));
    $startupid = trim(get_argument('startupid', 1, 'All'));
    $followonid = trim(get_argument('followonid', 1, 'All'));
    $delay_days = intval(get_argument('delay_days', 0, 0));
    $delay_hrs = intval(get_argument('delay_hrs', 0, 0));
    $delay_mins = intval(get_argument('delay_mins', 0, 0));
    $regcode = trim(get_argument('regcode', 0, ''));
    $deployPath32 = trim(get_argument('deploypath32', 0, ''));
    $deployPath64 = trim(get_argument('deploypath64', 0, ''));
    $fcmUrl = trim(get_argument('fcmUrl', 0, ''));
    $emailbounce = trim(get_argument('emailbounce', 0, ''));
    $urldownload = trim(get_argument('urldownload', 0, ''));
    $messagetext = trim(get_argument('messagetext', 1, ''));
    $emailsubject = trim(get_argument('emailsubject', 1, ''));
    $emailsender = trim(get_argument('emailsender', 1, ''));
    $emailxheaders = trim(get_argument('emailxheaders', 1, ''));

    $uninstall = ($followonid == 'Uninstall') ? 1 : 0;

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }

    // check for blank site name
    if (!strlen($sitename)) {
        $msg = "Site name cannot be blank.";
    }

    /* Check for case-sensitive only change to the site name */
    $sql = "SELECT sitename FROM Sites WHERE siteid=$id";
    $thisSite = find_site($id, $db);
    if ($thisSite) {
        if ((strcmp($thisSite['sitename'], $sitename) != 0) &&
            (strcasecmp($thisSite['sitename'], $sitename) == 0)
        ) {
            $msg = "Cannot adjust the case-sensitivity of a site.";
        }
    }

    // check for password
    if ($msg == '') {
        if (strlen($password) || strlen($confirm_pwd)) {
            $response = check_pwd_change($db, $username, 0, '', $password, $confirm_pwd);
            if ($response == "success")
                $sql_pwd = " password='" . md5($password) . "',\n";
            elseif (strlen(trim($response)))
                $msg = $response;
            else
                $msg = "There was a problem with this update. Please try again.";
        }
    }

    // upload enhancement start

    $client32Upload = $client64Upload = false;
    $client32Name = 'executable_client_32';
    $client64Name = 'executable_client_64';
    $clientApkName = 'executable_client_apk';
    $clientMacName = 'executable_client_mac';
    $clientIosName = 'executable_client_ios';
    $clientLinuxName = 'executable_client_linux';

    if (isset($_FILES[$client32Name]) && isset($_FILES[$client32Name]['name']) && !empty($_FILES[$client32Name]['name'])) {
        if (!isset($_FILES[$client32Name]['error']) || $_FILES[$client32Name]['error'] != 0) {
            $msg = "Client 32 bit upload error";
        }
        $client32Upload = true;
        $client32FileName = $_FILES[$client32Name]['name'];
    }

    if (isset($_FILES[$client64Name]) && isset($_FILES[$client64Name]['name']) && !empty($_FILES[$client64Name]['name'])) {
        if (!isset($_FILES[$client64Name]['error']) || $_FILES[$client64Name]['error'] != 0) {
            $msg = "Client 64 bit upload error";
        }
        $client64Upload = true;
        $client64FileName = $_FILES[$client64Name]['name'];
    }

    if (isset($_FILES[$clientApkName]) && isset($_FILES[$clientApkName]['name']) && !empty($_FILES[$clientApkName]['name'])) {
        if (!isset($_FILES[$clientApkName]['error']) || $_FILES[$clientApkName]['error'] != 0) {
            $msg = "Android Client upload error";
        }
        $clientApkUpload = true;
        $clientApkFileName = $_FILES[$clientApkName]['name'];
    }

    if (isset($_FILES[$clientMacName]) && isset($_FILES[$clientMacName]['name']) && !empty($_FILES[$clientMacName]['name'])) {
        if (!isset($_FILES[$clientMacName]['error']) || $_FILES[$clientMacName]['error'] != 0) {
            $msg = "Mac Client upload error";
        }

        $clientMacUpload = true;
        $clientMacFileName = $_FILES[$clientMacName]['name'];
    }
    if (isset($_FILES[$clientIosName]) && isset($_FILES[$clientIosName]['name']) && !empty($_FILES[$clientIosName]['name'])) {
        if (!isset($_FILES[$clientIosName]['error']) || $_FILES[$clientIosName]['error'] != 0) {
            $msg = "Ios Client upload error";
        }

        $clientIosUpload = true;
        $clientIosFileName = $_FILES[$clientIosName]['name'];
    }

    if (isset($_FILES[$clientLinuxName]) && isset($_FILES[$clientLinuxName]['name']) && !empty($_FILES[$clientLinuxName]['name'])) {
        if (!isset($_FILES[$clientLinuxName]['error']) || $_FILES[$clientLinuxName]['error'] != 0) {
            $msg = "Linux Client upload error";
        }

        $clientLinuxUpload = true;
        $clientLinuxFileName = $_FILES[$clientLinuxName]['name'];
    }

    if ($client32Upload) {
        $upoadFtpData = uploadWithFtp($client32Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($client64Upload) {
        $upoadFtpData = uploadWithFtp($client64Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientApkUpload) {
        $upoadFtpData = uploadWithFtp($clientApkName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientMacUpload) {
        $upoadFtpData = uploadWithFtp($clientMacName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientIosUpload) {
        $upoadFtpData = uploadWithFtp($clientIosName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientLinuxUpload) {
        $upoadFtpData = uploadWithFtp($clientLinuxName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    $ssl = (isset($_SERVER['HTTPS'])) ? 1 : 0;
    $host = $_SERVER['HTTP_HOST'];
    $http = ($ssl) ? 'https' : 'http';

    if ($msg == '') {
        // calculate delay
        $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;

        $urldownload = $http . '://' . $host . '/Provision/download/';

        // insert into Sites table [1]
        // update Sites table
        $sql = "update Sites set";
        $sql .= " sitename='$sitename',\n";
        $sql .= " domain='$domain',\n";
        $sql .= " username='$username',\n";
        if (strlen(trim($sql_pwd)))
            $sql .= $sql_pwd;
        $sql .= " email='$email',\n";
        $sql .= " serverid=$serverid,\n";
        $sql .= " skuids=$skuids,\n";
        $sql .= " cid=$cid,\n";
        $sql .= " proxy='$proxy',\n";
        $sql .= " startupid='$startupid',\n";
        $sql .= " followonid='$followonid',\n";
        $sql .= " uninstall='$uninstall',\n";
        $sql .= " delay=$delay,\n";
        $sql .= " deploypath32='$deployPath32',\n";
        $sql .= " deploypath64='$deployPath64',\n";
        $sql .= " fcmUrl='$fcmUrl',\n";
        $sql .= " emailbounce='$emailbounce',\n";
        $sql .= " urldownload='$urldownload',\n";
        $sql .= " messagetext='$messagetext',\n";
        $sql .= " emailsubject='$emailsubject',\n";
        $sql .= " emailsender='$emailsender',\n";
        $sql .= " emailxheaders='$emailxheaders'\n";
        $sql .= " lastcontact=" . time() . "\n";
        if ($client32Upload) {
            $sql .= ", client_32_name='$client32FileName'\n";
        }

        if ($client64Upload) {
            $sql .= ", client_64_name='$client64FileName'\n";
        }

        if ($clientApkUpload) {
            $sql .= ", client_android_name='$clientApkFileName'\n";
        }

        if ($clientMacUpload) {
            $sql .= ", client_mac_name='$clientMacFileName'\n";
        }

        if ($clientIosUpload) {
            $sql .= ", client_ios_name='$clientIosFileName'\n";
        }

        if ($clientLinuxUpload) {
            $sql .= ", client_linux_name='$clientLinuxFileName'\n";
        }

        $sql .= " where siteid = $id";
        $res = redcommand($sql, $db);

        $sitename = stripslashes($sitename);
        TokenChecker::calcSites($sitename, 'sitename');

        if (!$res) {
            $problem = 1;
            $sql_error = mysqli_error($db);
            $sql_errno = mysqli_errno($db);

            // check for duplicate site name
            $key_index = get_key_index('install', 'Sites', 'uniq', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The site name <b>$sitename</b> is a duplicate
                                 of an existing site name.";
                }
            }
        }

        if ($problem) {
            $msg = "Unable to update site <b>$sitename</b>. $xtra_msg";
        } else {
            $message = "Site <b>$sitename</b> updated.";
            $log = "install: Site '$sitename' updated by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);

            // Make sure clients haven't already gotten old settings
            if (mysqli_affected_rows($db)) {
                // update numedits
                // insert into Sites table [1]
                $sql = "update Sites set";
                $sql .= " numedits= numedits + 1\n";
                $sql .= " where siteid = $id";
                $res = redcommand($sql, $db);
                TokenChecker::calcSites($id);

                $numconnects = get_numconnects($id, $db);
                if ($numconnects) {
                    $are = ($numconnects > 1) ? 'are' : 'is';
                    $clients = ($numconnects > 1) ? 'clients' : 'client';
                    $These = ($numconnects > 1) ? 'These' : 'This';
                    $message .= "<br><br>Note that there $are already $numconnects" .
                        " $clients using the data you changed." .
                        " $These $clients will not be updated with the " .
                        " changes you made until the next time a" .
                        " client is installed at site <b>$sitename</b>.";
                }
            }
        }
    }

    echo json_encode(array("message" => $msg));
}
