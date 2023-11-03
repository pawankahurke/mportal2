<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-db.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-sql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-gsql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-rcmd.php';

function createCoreUser($pdata)
{
    $pdo = pdo_connect();

    if (!nhUser::isAllowSignUp()) {
        die('Sign up is not allowed in ' . __FUNCTION__);
    }
    $username   = $pdata['username'];
    $password   = $pdata['password'];
    $skuids     = $pdata['skuids'];
    $serverid   = $pdata['serverid'];
    $emailid    = $pdata['emailid'];
    $ftname     = $pdata['fname'];
    $ltname     = $pdata['lname'];
    $userid     = $pdata['userid'];

    $stmt = $pdo->prepare("select count(userid) usercnt from " . $GLOBALS['PREFIX'] . "core.Users where username = ? limit 1");
    $stmt->execute([$username]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($res['usercnt'] > 0) {
        $ustmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set entity_id = ?, channel_id = ?, subch_id = ?, user_email = ?, "
            . "firstName = ?, lastName = ? where username = ?");
        $ures = $ustmt->execute([1, $skuids, $serverid, $emailid, $ftname, $ltname, $username]);
        if ($ures) {
            $retres = ['code' => 200, 'status' => 'success', 'msg' => 'User details updated successfully.'];
        } else {
            $retres = ['code' => 200, 'status' => 'failed', 'msg' => 'Failed to update user details.'];
        }
    } else {
        $ostmt = $pdo->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Options where name = ? and type = ? limit 1");
        $ostmt->execute(['AdminRole', 10]);
        $ores = $ostmt->fetch(PDO::FETCH_ASSOC);
        $roleid = $ores['id'];

        $pwdate = time() + (365 * 24 * 60 * 60);
        $istmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Users (username, password, priv_admin, entity_id, channel_id, subch_id, "
            . "user_email, firstName, lastName, customer_id, passwordDate, role_id, revusers) values (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $ires = $istmt->execute([$username, $password, 1, 1, $skuids, $serverid, $emailid, $ftname, $ltname, $userid, $pwdate, $roleid, 1]);
        if ($ires) {
            $retres = ['code' => 200, 'status' => 'success', 'msg' => 'User has been created successfully.'];
        } else {
            $retres = ['code' => 200, 'status' => 'failed', 'msg' => 'Failed to create user.'];
        }
    }
    echo json_encode($retres);
}

function createCoreCustomer($pdata)
{
    $pdo = pdo_connect();

    $emailid = $pdata['emailid'];
    $sitename = $pdata['sitename'];

    $stmt = $pdo->prepare("select userid, username from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ? limit 1");
    $stmt->execute([$emailid]);
    $userres = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $userres['username'];

    $cstmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) values (?, ?)");
    $cres = $cstmt->execute([$username, $sitename]);
    if ($cres) {
        $retres = ['code' => 200, 'status' => 'success', 'msg' => 'Site has been attached to the user successfully.'];
    } else {
        $retres = ['code' => 200, 'status' => 'failed', 'msg' => 'Failed to attach site with the user.'];
    }
    echo json_encode($retres);
}

function createCoreInstallSites($pdata)
{
    $pdo = pdo_connect();

    $sitename = $pdata['sitename'];
    $domain = $pdata['domain'];
    $userid = $pdata['userid'];
    $username = $pdata['username'];
    $password = $pdata['password'];
    $email = $pdata['email'];
    $serverid = $pdata['serverid'];
    $proxy = $pdata['proxy'];
    $startupid = $pdata['startupid'];
    $followonid = $pdata['followonid'];
    $uninstall = $pdata['uninstall'];
    $delay = $pdata['delay'];
    $delayon = $pdata['delayon'];
    $deployPath32 = $pdata['deploypath32'];
    $deployPath64 = $pdata['deploypath64'];
    $emailbounce = $pdata['emailbounce'];
    $urldownload = $pdata['urldownload'];
    $messagetext = $pdata['messagetext'];
    $emailsubject = $pdata['emailsubject'];
    $emailsender = $pdata['emailsender'];
    $emailxheaders = $pdata['emailxheaders'];
    $regcode = $pdata['regcode'];
    $brandingUrl = $pdata['brandingurl'];
    // insert into Sites table [1]
    $stmt = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "install.Sites SET sitename=?, domain=?, installuserid=?, username=?, email=?, "
        . "serverid=?, proxy=?, startupid=?, followonid=?, uninstall=?, delay=?, delayon=?, deploypath32=?, "
        . "deploypath64=?, emailbounce=?, urldownload=?, messagetext=?, emailsubject=?, emailsender=?, "
        . "emailxheaders=?, regcode=?, brandingurl=?");
    $stmt->execute([
        $sitename, $domain, $userid, $username, $email, $serverid, $proxy, $startupid, $followonid,
        $uninstall, $delay, $delayon, $deployPath32, $deployPath64, $emailbounce, $urldownload, $messagetext,
        $emailsubject, $emailsender, $emailxheaders, $regcode, $brandingUrl
    ]);
    $siteid = $pdo->lastInsertId();
    TokenChecker::calcSites($sitename, 'sitename');

    $now = time();
    $sestmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "install.Siteemail (siteid, installuserid, userid, subscriptionid, "
        . "email, createdtime, sent, response, numresponses, installed, numinstalls, maxinstall) values "
        . "(?,?,?,?,?,?,?,?,?,?,?,?)");
    $seres = $sestmt->execute([$siteid, $userid, 0, 0, $email, $now, 0, 0, 0, 0, 0, 0]);

    $siteusername = ($username == '') ? 'admin' : $username;
    $ccstmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer, lastmodified) values (?,?,?)");
    $ccstmt->execute([$siteusername, $sitename, time()]);

    if ($seres) {
        $retres = ['code' => 200, 'status' => 'success', 'msg' => 'ASI Site entry success.'];
    } else {
        $retres = ['code' => 200, 'status' => 'failed', 'msg' => 'ASI Site entry failed.'];
    }
    echo json_encode($retres);
}



$data = file_get_contents('php://input');
$pdata = safe_json_decode($data, true);

$function = $pdata['function'];
$contdata = $pdata['data'];

switch ($function) {
    case 'createuser':
        createCoreUser($contdata);
        break;
    case 'createcustomer':
        createCoreCustomer($contdata);
        break;
    case 'createsite':
        createCoreInstallSites($contdata);
        break;
    default:
        break;
}
