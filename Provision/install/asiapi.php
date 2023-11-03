<?php

/*
 * History
 * ^^^^^^^
 * 26-08-2019   JHN     File created for licesing API implementation.
 */
require_once(getcwd() . '/../../config.php');


include_once('../lib/l-cnst.php');
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-slct.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-user.php');
include_once('../lib/l-head.php');
include_once('header.php');
include_once('../lib/l-errs.php');

function updateBrandingUrl($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $brandingurl = $reqData['brandingurl'];
    $sitename = $reqData['sitename'];
    // insert into Sites table [1]
    $sql = "UPDATE Sites SET brandingurl = '$brandingurl' where sitename='$sitename'";
    $res = redcommand($sql, $db);

    if ($res) {
        $retres = ['code' => 200, 'status' => 1, 'msg' => 'Branding url has been updated successfully.'];
    } else {
        $retres = ['code' => 200, 'status' => 0, 'msg' => 'Failed to update branding url.'];
    }
    echo json_encode($retres);
}

function createInstallSite($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $sitename = addcslashes($reqData['sitename'], "'");
    $email_id = $reqData['emailid'];
    $serverid = $reqData['serverid'];
    $sku_item = $reqData['skuitem'];
    $startup  = $reqData['startup'];
    $followon = $reqData['followon'];
    $delay    = $reqData['delay'];

    $now = time();

    $sql = "SELECT installuserid, installuser FROM Users WHERE email = '$email_id' limit 1";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $server = mysqli_fetch_assoc($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    $instusrid = $server['installuserid'];
    $delayInMins = ($delay * 1440);
    $regcode = gen_regcode($sitename, 0);
    // insert into Sites table [1]
    $sitesql = "insert into Sites (sitename, installuserid, email, serverid, skuids, startupid, followonid, delay, regcode) values "
        . "('$sitename', $instusrid, '$email_id', $serverid, '$sku_item', '$startup', '$followon', $delayInMins, '$regcode')";
    $siteres = redcommand($sitesql, $db);
    TokenChecker::calcSites($sitename, 'sitename');

    if ($siteres) {
        $ins_siteid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $sesql = "insert into Siteemail set siteid=$ins_siteid, installuserid=$instusrid, userid=$instusrid, email='$email_id', createdtime='$now'";
        redcommand($sesql, $db);

        $retres = ['code' => 200, 'status' => 1, 'msg' => 'Site created successfully!'];
    } else {
        $retres = ['code' => 200, 'status' => 0, 'msg' => 'Site creation failed!'];
    }
    echo json_encode($retres);
}

function getInstallSiteInfo($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $sitenameorid = $reqData['sitenameorid'];

    $siteSql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where sitename='$sitenameorid' or siteid = '$sitenameorid'";
    $siteRes = redcommand($siteSql, $db);
    if ($siteRes) {
        if (mysqli_num_rows($siteRes) > 0) {
            $siteData = mysqli_fetch_assoc($siteRes);
        }
        ((mysqli_free_result($siteRes) || (is_object($siteRes) && (get_class($siteRes) == "mysqli_result"))) ? true : false);
    }
    echo json_encode($siteData);
}

function getSiteEmailInfo($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    if (isset($reqData['userid'])) {
        $userid = $reqData['userid'];
        $sitesql = "SELECT * FROM Siteemail WHERE installuserid = $userid;";
    } else if (isset($reqData['siteid']) && isset($reqData['emailid'])) {
        $siteid = $reqData['siteid'];
        $emailid = $reqData['emailid'];
        $sitesql = "SELECT * FROM Siteemail WHERE siteid = '$siteid' and email = '$emailid';";
    }
    $siteres = redcommand($sitesql, $db);

    if ($siteres) {
        if (mysqli_num_rows($siteres) > 0) {
            $sitedata = mysqli_fetch_assoc($siteres);
        }
        ((mysqli_free_result($siteres) || (is_object($siteres) && (get_class($siteres) == "mysqli_result"))) ? true : false);
    }
    echo json_encode($sitedata);
}

function insertSiteEmailData($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $new_email_cnt = 0;
    $dup_email_cnt = 0;
    $now = time();

    $emailList = $reqData['emaillist'];
    $userid = $reqData['userid'];
    $siteid = $reqData['siteid'];
    $sbuserid = $reqData['sbuserid'];

    foreach ($emailList as $key => $value) {
        $sql = "select * from Siteemail where email = '$value' and installuserid = $userid and siteid = $siteid";
        $res = redcommand($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res) > 0) {
                $dup_email_cnt++;
            } else {
                $sesql = "insert into Siteemail set siteid = $siteid, installuserid = $userid, userid = $sbuserid, "
                    . "email = '$value', createdtime='$now'";
                redcommand($sesql, $db);

                $new_email_cnt++;
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    if ($new_email_cnt > 0) {
        $msg = $new_email_cnt . " new email(s) has been added!.";
    } else {
        $msg = "No new emails to add";
    }
    $retres = ['code' => 200, 'status' => 1, 'msg' => $msg];

    echo json_encode($retres);
}

function updateSiteEmailData($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $now = time();
    $siteemailid = $reqData['siteemailid'];
    $sql = "UPDATE Siteemail SET sent = $now WHERE siteemailid = $siteemailid";
    $res = redcommand($sql, $db); // SE fix skip 
    if ($res) {
        echo 1;
    } else {
        echo 0;
    }
}

function createInstallUser($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $emailid = $reqData['emailid'];
    $password = $reqData['password'];
    $firstname = $reqData['fname'];
    $lastname = $reqData['lname'];
    $sb_userid = $reqData['sbuserid'];
    $serverid = $reqData['serverid'];
    $skuitem = $reqData['skuitem'];
    $startup = $reqData['startup'];
    $followon = $reqData['followon'];
    $delay = $reqData['delay'];

    $checkSql = "SELECT * FROM Users WHERE installemailid = '$emailid' limit 1";
    $checkRes = redcommand($checkSql, $db);
    if ($checkRes) {
        if (mysqli_num_rows($checkRes) > 0) {
            $retres = ['code' => 200, 'status' => 0, 'msg' => 'Install user already exists!'];
        } else {
            $compname = $firstname . '_' . $lastname;
            $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "install.Users (installuser, password, installemailid, "
                . "firstname, lastname, companyname, sbuserid, priv_admin, serverid, "
                . "skuids, startupid, followonid, delay, email) VALUES ('$compname', '$password', "
                . "'$emailid', '$firstname', '$lastname', '$compname', $sb_userid, 1, $serverid, "
                . "'$skuitem', '$startup', '$followon', $delay, '$emailid')";
            $res = redcommand($sql, $db);
            if ($res) {
                $retres = ['code' => 200, 'status' => 1, 'msg' => 'Install user created successfully!'];
            } else {
                $retres = ['code' => 200, 'status' => 0, 'msg' => 'Install user creation failed!'];
            }
        }
        ((mysqli_free_result($checkRes) || (is_object($checkRes) && (get_class($checkRes) == "mysqli_result"))) ? true : false);
    }
    echo json_encode($retres);
}

function getSkuListInfo()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $skuSql = "SELECT * FROM skuOfferings";
    $skuRes = redcommand($skuSql, $db);
    if ($skuRes) {
        if (mysqli_num_rows($skuRes) > 0) {
            while ($row = mysqli_fetch_assoc($skuRes)) {
                $skudata[] = $row;
            }
        }
        ((mysqli_free_result($skuRes) || (is_object($skuRes) && (get_class($skuRes) == "mysqli_result"))) ? true : false);
    }
    echo json_encode($skudata);
}

function getLicenseDetails($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $sitename = $reqData['sitename'];
    $maxinstall = 0;

    $sitesql = "select siteid, skuids, regcode, installuserid, urldownload, wsurl from " . $GLOBALS['PREFIX'] . "install.Sites where sitename = ? limit 1";
    $sitedata = NanoDB::find_one($sitesql, null, [$sitename]);
    if ($sitedata) {
        //     if (mysqli_num_rows($siteres) > 0) {
        //         $sitedata = mysqli_fetch_assoc($siteres);
        $siteid = $sitedata['siteid'];
        $skuid = $sitedata['skuids'];
        $regcode = $sitedata['regcode'];
        $installuserid = $sitedata['installuserid'];
        $urldownload = $sitedata['urldownload'];
        $wsurl = $sitedata['wsurl'];
        //     }
        //     ((mysqli_free_result($siteres) || (is_object($siteres) && (get_class($siteres) == "mysqli_result"))) ? true : false);
    }

    if ($wsurl == '') {
        $usersql = "select wsurl from Users where installuserid = $installuserid limit 1";
        $userres = redcommand($usersql, $db);
        if ($userres) {
            if (mysqli_num_rows($userres) > 0) {
                $userdata = mysqli_fetch_assoc($userres);
                $wsurl = $userdata['wsurl'];
            }
            ((mysqli_free_result($userres) || (is_object($userres) && (get_class($userres) == "mysqli_result"))) ? true : false);
        }
    }

    $apisql = "select domainurl from apiConfig limit 1";
    $apires = redcommand($apisql, $db);
    if ($apires) {
        if (mysqli_num_rows($apires) > 0) {
            $apidata = mysqli_fetch_assoc($apires);
            $domainurl = $apidata['domainurl'];
        }
        ((mysqli_free_result($apires) || (is_object($apires) && (get_class($apires) == "mysqli_result"))) ? true : false);
    }

    $skusql = "select name, quantity from skuOfferings where sid = $skuid limit 1";
    $skures = redcommand($skusql, $db);
    if ($skures) {
        if (mysqli_num_rows($skures) > 0) {
            $skudata = mysqli_fetch_assoc($skures);
            $skuname = $skudata['name'];
            $maxinstall = $skudata['quantity'];
        }
        ((mysqli_free_result($skures) || (is_object($skures) && (get_class($skures) == "mysqli_result"))) ? true : false);
    }

    $semailsql = "select siteemailid, numinstalls from Siteemail where siteid = $siteid limit 1";
    $semailres = redcommand($semailsql, $db);
    if ($semailres) {
        if (mysqli_num_rows($semailres) > 0) {
            $semaildata = mysqli_fetch_assoc($semailres);
            $siteemailid = $semaildata['siteemailid'];
            $numinstalls = $semaildata['numinstalls'];
        }
        (((is_object($skures) && (get_class($skures) == "mysqli_result"))) ? true : false);
    }

    if ($urldownload != '') {
        $downloadurl = explode('/', $urldownload);
        $domainurl = $downloadurl[0] . '//' . $downloadurl[2] . '/';
    }
    $serverProtocol  = 'https://';
    $servername = $_SERVER['HTTP_HOST'];
    $domainurl = $serverProtocol . $servername . "/";
    $retdata['skuname'] = $skuname;
    $retdata['maxinstall'] = $maxinstall;
    $retdata['numinstall'] = $numinstalls;
    $retdata['regcode'] = $regcode;
    $retdata['siteemailid'] = $siteemailid;
    $retdata['wsurl'] = $wsurl;
    $retdata['domainurl'] = $domainurl;

    echo json_encode($retdata);
    logs::log("Answer:", $retdata);
}

/* Generic functions :: START */

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

function getMultiTenantInfo($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $appid = $reqData['applicationid'];

    $tenantsql = "select * from Tenant where fusionauthappid = '$appid' limit 1";
    $tenantres = redcommand($tenantsql, $db);
    if ($tenantres) {
        if (mysqli_num_rows($tenantres) > 0) {
            $tenantdata = mysqli_fetch_assoc($tenantres);
            $multiTenantInfo = $tenantdata['multitenantinfo'];

            $tenantInfo = safe_json_decode($multiTenantInfo, TRUE);

            echo json_encode($tenantInfo['mysql']);
        }
        ((mysqli_free_result($tenantres) || (is_object($tenantres) && (get_class($tenantres) == "mysqli_result"))) ? true : false);
    }
}

function getClientDownloadInfo($reqData)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'install', $db);

    $regcode = $reqData['regcode'];
    // Query 1
    $siteSql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where regcode = '$regcode'";
    $siteRes = redcommand($siteSql, $db);
    if ($siteRes) {
        if (mysqli_num_rows($siteRes) > 0) {
            $siteData = mysqli_fetch_assoc($siteRes);
        }
        ((mysqli_free_result($siteRes) || (is_object($siteRes) && (get_class($siteRes) == "mysqli_result"))) ? true : false);
    }

    // Query 2
    $serverSql = "select * from " . $GLOBALS['PREFIX'] . "install.Servers where serverid = '" . $siteData['serverid'] . "'";
    $serverRes = redcommand($serverSql, $db);
    if ($serverRes) {
        if (mysqli_num_rows($serverRes) > 0) {
            $serverData = mysqli_fetch_assoc($serverRes);
        }
        ((mysqli_free_result($serverRes) || (is_object($serverRes) && (get_class($serverRes) == "mysqli_result"))) ? true : false);
    }

    //Query 3
    $custSql = "select cid, customer_name from " . $GLOBALS['PREFIX'] . "install.Customers where tenant_id = " . $siteData['installuserid'] . " limit 1";
    $custRes = redcommand($custSql, $db);
    if ($custRes) {
        if (mysqli_num_rows($custRes) > 0) {
            $customerData = mysqli_fetch_assoc($custRes);
        }
        ((mysqli_free_result($custRes) || (is_object($custRes) && (get_class($custRes) == "mysqli_result"))) ? true : false);
    }

    //Query 4
    $apiSql = "select domainurl from " . $GLOBALS['PREFIX'] . "install.apiConfig order by id limit 1";
    $apiRes = redcommand($apiSql, $db);
    if ($apiRes) {
        if (mysqli_num_rows($apiRes) > 0) {
            $apiData = mysqli_fetch_assoc($apiRes);
        }
        ((mysqli_free_result($apiRes) || (is_object($apiRes) && (get_class($apiRes) == "mysqli_result"))) ? true : false);
    }

    $finalData = array_merge(['site' => $siteData], ['server' => $serverData], ['customer' => $customerData], ['api' => $apiData]);

    echo json_encode($finalData);
}

/* Generic functions :: END */

/* Main program */

$rdata = file_get_contents('php://input');
$pdata = safe_json_decode($rdata, true);

$reqApi = $pdata['function']; // roles: site 
$reqData = $pdata['data'];

logs::log("Api call:", [$reqApi, nhRole::getMyRoles()]);
switch ($reqApi) {
    case 'updatebrandingurl':
        nhRole::dieIfnoRoles(['site']); // roles: site 
        updateBrandingUrl($reqData);
        break;
    case 'createinstallsite':
        nhRole::dieIfnoRoles(['site']); // roles: site 
        createInstallSite($reqData);
        break;
    case 'getinstallsiteinfo':
        nhRole::dieIfnoRoles(['site']); // roles: site 
        getInstallSiteInfo($reqData);
        break;
    case 'getsiteemailinfo':
        nhRole::dieIfnoRoles(['site']); // roles: site         
        getSiteEmailInfo($reqData);
        break;
    case 'insertsiteemaildata':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        insertSiteEmailData($reqData);
        break;
    case 'updatesiteemaildata':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        updateSiteEmailData($reqData);
        break;
    case 'createinstalluser':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        createInstallUser($reqData);
        break;
    case 'getskulist':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        getSkuListInfo();
        break;
    case 'getlicensedetails':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        getLicenseDetails($reqData);
        break;
    case 'getmultitenantinfo':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        getMultiTenantInfo($reqData);
        break;
    case 'getclientdownloadinfo':
        nhRole::dieIfnoRoles(['site']); // roles: site  
        getClientDownloadInfo($reqData);
        break;
    default:
        echo json_encode(['code' => 400, 'status' => 0, 'msg' => 'API function not found!']);
        break;
}
