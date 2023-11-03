<?php

function addNewSkuListFunc($skuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle)
{

    $rdata['function'] = 'addNewSku';
    $rdata['data']['skuname'] = $skuname;
    $rdata['data']['skuDesc'] = $skuDesc;
    $rdata['data']['skuCat'] = $skuCat;
    $rdata['data']['skuBillType'] = $skuBillType;
    $rdata['data']['skuQty'] = $skuQty;
    $rdata['data']['skuAmt'] = $skuAmt;
    $rdata['data']['skuTrial'] = $skuTrial;
    $rdata['data']['skuBillCycle'] = $skuBillCycle;

    $scresdata =  MAKE_CURL_CALL($rdata);

    return $scresdata;
}

function showLicenseDetailsFunc($skuname)
{
    $rdata['function'] = 'showLicenseDetails';
    $rdata['data']['skuname'] = $skuname;

    $scresdata =  MAKE_CURL_CALL($rdata);
    return $scresdata;
}

function updateLicenseCountFunc($oldskuname, $newskuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle)
{

    $rdata['function'] = 'updateLicenseDetails';
    $rdata['data']['oldskuname'] = $oldskuname;
    $rdata['data']['skuname'] = $newskuname;
    $rdata['data']['skuDesc'] = $skuDesc;
    $rdata['data']['skuCat'] = $skuCat;
    $rdata['data']['skuBillType'] = $skuBillType;
    $rdata['data']['skuQty'] = $skuQty;
    $rdata['data']['skuAmt'] = $skuAmt;
    $rdata['data']['skuTrial'] = $skuTrial;
    $rdata['data']['skuBillCycle'] = $skuBillCycle;

    $scresdata =  MAKE_CURL_CALL($rdata);
    return $scresdata;
}

function addNewLicenseListFunc($skuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle, $licenseKey)
{
    $pdo = pdo_connect();
    $serverSql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "install.skuOfferings where customfields=?");
    $serverSql->execute([$licenseKey]);
    $serverRes = $serverSql->fetch(PDO::FETCH_ASSOC);

    if (!$serverRes) {
        // skuOfferings insert
        $ins_user_stmt = $pdo->prepare('INSERT INTO ' . $GLOBALS['PREFIX'] . 'install.skuOfferings (`name`, `description`, `published`, `category`, `billingtype`, `quantity`, `amount`, `trialperiod`, `billingcycle`, `customfields`)'
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $ins_user_stmt->execute([$skuname, $skuDesc, 0, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle, $licenseKey]);
    }
}

function createOrgInsSiteFunc($sitename, $insusrid, $email_id, $serverid, $sku_item, $startup, $followon, $delay)
{
    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];
    $userid = $_SESSION['user']['userid'];

    $serverSql = $pdo->prepare("select distinct subch_id as serverid from " . $GLOBALS['PREFIX'] . "core.Users where subch_id != 0 limit 1");
    $serverSql->execute();
    $serverRes = $serverSql->fetch(PDO::FETCH_ASSOC);

    $rdata['function'] = 'createinstallsite';
    $rdata['data']['sitename'] = $sitename;
    $rdata['data']['emailid'] = $email_id;
    $rdata['data']['serverid'] = $serverid;
    $rdata['data']['skuitem'] = $sku_item;
    $rdata['data']['startup'] = $startup;
    $rdata['data']['followon'] = $followon;
    $rdata['data']['delay'] = $delay;
    $rdata['data']['username'] = $username;
    $rdata['data']['serverid'] = $serverRes['serverid'];

    $scresdata =  MAKE_CURL_CALL($rdata);
    if ($scresdata['status']) {
        $custSql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) values (?, ?)");
        $custSql->execute([$username, $sitename]);

        $parents = getParents($userid);
        $user_in = str_repeat('?,', safe_count($parents) - 1) . '?';
        $userSql = $pdo->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid in ($user_in)");
        $userSql->execute([$parents]);
        $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);

        foreach ($userRes as $value) {
            $pusername = $value['username'];
            $custSql = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) values (?, ?)");
            $custSql->execute([$pusername, $sitename]);
        }

        $userCustSql = $pdo->prepare("select id, username from " . $GLOBALS['PREFIX'] . "core.Customers where username = ? and customer = ?");
        $userCustSql->execute([$username, $sitename]);
        $userCustRes = $userCustSql->fetch(PDO::FETCH_ASSOC);
        $siteid = $userCustRes['id'];
        $folderToCreate = "../../Branding/cust_" . $sitename . "_" . $siteid;
        try {
            mkdir($folderToCreate);
            recursive_copy('../admin/config', $folderToCreate . '/config');
            recursive_copy('../admin/images', $folderToCreate . '/images');

            createZipArchive($folderToCreate, $sitename, $siteid);

            // updateBrandingUrlFunc($sitename, $siteid);
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
        }

        $status = 1;
        $msg = "Site created successfully";
        $val = '';

        $retInfo = ["status" => $status, "msg" => $msg, "val" => $val];
    } else {
        $status = 0;
        $msg = "Site creation failed";
        $val = '';
        $retInfo = ["status" => $status, "msg" => $msg, "val" => $val];
    }
    return $retInfo;
}

function recursive_copy($src, $dst)
{
    $dir = opendir($src);
    mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recursive_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

function createZipArchive($folderToZip, $cid, $pid)
{

    $rootPath = realpath($folderToZip);

    $zip = new ZipArchive();
    $zip->open($folderToZip . '/cust_' . $cid . '_' . $pid . '.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);


    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    return 1;
}

function createCoreCustomersFunc($compname, $db)
{
    $sql = "select username from " . $GLOBALS['PREFIX'] . "core.Users where priv_admin = 1";
    $res = find_many($sql, $db);
    $adminCust = '';
    $default = "('$compname', '$compname'),";
    foreach ($res as $value) {
        $username = $value['username'];
        $adminCust .= "('$username', '$compname'),";
    }

    $queryVal = $default . '' . $adminCust;
    $queryVal = rtrim($queryVal, ',');
    $inssql = "insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) values $queryVal";
    $insres = redcommand($inssql, $db);
    if (!$insres) {
        logs::log("Failed to create Core Customer entry");
    }
}

function getCoreUserInfo($userEmail)
{
    $coreSql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where user_email =  ?";
    $coreRes = NanoDB::find_one($coreSql, null, [$userEmail]);
    return $coreRes;
}

function getLicenseSiteInfo($sitename)
{
    $udata['function'] = 'getinstallsiteinfo';
    $udata['data']['sitenameorid'] = $sitename;
    $siteData = MAKE_CURL_CALL($udata);

    if (safe_count($siteData) > 0) {
        $userData['instuserid'] = isset($siteData['installuserid']) ? $siteData['installuserid'] : '';
        $userData['sbuserid'] = isset($siteData['sbuserid']) ? $siteData['sbuserid'] : '';
        $userData['siteid'] = isset($siteData['siteid']) ? $siteData['siteid'] : '';
    } else {
        return 0;
    }

    return $userData;
}

function getSiteEmailDataFunc()
{

    $emailsdata = [];
    $userEmail = $_SESSION["user"]["adminEmail"];
    $coreUserData = getCoreUserInfo($userEmail);
    $orguserid = $coreUserData['customer_id'];

    $sdata['function'] = 'getsiteemailinfo';
    $sdata['data']['userid'] = $orguserid;
    $siteData = MAKE_CURL_CALL($sdata);

    if ($siteData) {
        foreach ($siteData as $key => $value) {
            $id = $value['siteemailid'];
            $emailsdata[$id] = htmlentities($value['email']);
        }
        $retInfo = ["status" => 1, "msg" => "Success", "data" => $emailsdata];
    } else {
        $retInfo = ["status" => 1, "msg" => "Failed", "data" => ""];
    }
    return $retInfo;
}

function updateSiteEmailDataFunc($emailList, $sitename)
{

    $instUserData = getLicenseSiteInfo($sitename);

    $siteid = $instUserData['siteid'];
    $ins_userid = $instUserData['instuserid'];
    $sb_userid = ($instUserData['sbuserid'] != NULL) ? $instUserData['sbuserid'] : $ins_userid;

    if (!$instUserData) {
        $retInfo = ["status" => 0, "msg" => "Site doesn't exist in the license server!", "data" => ""];
        return $retInfo;
    }

    $sedata['function'] = 'insertsiteemaildata';
    $sedata['data']['emaillist'] = $emailList;
    $sedata['data']['userid'] = $ins_userid;
    $sedata['data']['siteid'] = $siteid;
    $sedata['data']['sbuserid'] = $sb_userid;
    $siteData = MAKE_CURL_CALL($sedata);

    $retInfo = ["status" => $siteData['status'], "msg" => $siteData['msg'], "data" => $siteid];
    return $retInfo;
}

function sendDownloadLinkMailFunc($siteid, $emailList)
{

    global $emailTemplate;

    $emailData = explode(PHP_EOL, $emailList);

    $udata['function'] = 'getinstallsiteinfo';
    $udata['data']['sitenameorid'] = $siteid;
    $sitedata = MAKE_CURL_CALL($udata);

    $customerSite = $_SESSION['searchValue'];
    $emailSql = "select emailsubject, emailtitle, emailbody from " . $GLOBALS['PREFIX'] . "core.Customers where customer=? limit 1";
    $emailRes = NanoDB::find_one($emailSql, null, [$customerSite]);

    $emailsubject = $emailRes['emailsubject'];
    $emailtitle = $emailRes['emailtitle'];
    $emailbody = $emailRes['emailbody'];

    $regcode = $sitedata['regcode'];
    $emailbounce = $sitedata['emailbounce'];
    $emailsender = $sitedata['emailsender'];
    $emailxheaders = $sitedata['emailxheaders'];

    $subject = $emailsubject;
    $headers = "From: $emailsender\n";
    $headers .= "Reply-To: $emailsender\n";
    $headers .= "Errors-To: $emailbounce\n";
    if (strlen($emailxheaders)) {
        $headers .= "$emailxheaders";
    }

    $mime_head = "MIME-Version: 1.0\n";
    $mime_head .= "Content-Type: text/html;charset=UTF-8\n";



    $licnsdata['function'] = 'getlicensedetails';
    $licnsdata['data']['sitename'] = $sitedata['sitename'];
    $licensedetails = MAKE_CURL_CALL($licnsdata);
    $licenseurl = $licensedetails['domainurl'];

    $url = $licenseurl . 'Provision/install/d.php';
    foreach ($emailData as $value) {

        $emailContent = str_replace('EMAILTITLE', $emailtitle, $emailTemplate);
        $emailContent = str_replace('EMAILBODY', $emailbody, $emailContent);

        $email = $value;
        $siteemailid = get_emails($siteid, $email);

        $responseurl = "$url?r=$regcode&e=$siteemailid";

        $emailContent = str_replace('DOWNLOADBTNURL', $responseurl, $emailContent);
        $emailContent = str_replace('DOWNLOADURL', $responseurl, $emailContent);

        $fromMail = 'noreply@nanoheal.com';
        $fromName = 'Nanoheal NoReply';
        $emailName = explode('@', $email)[0];
        $arrayPost = array(
            'from' => getenv('SMTP_USER_LOGIN'),
            'to' => $email,
            'subject' => $subject,
            'text' => '',
            'html' => $emailContent,
            'token' => getenv('APP_SECRET_KEY'),
        );

        $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";

        if (CURL::sendDataCurl($url, $arrayPost)) {
            $sedata['function'] = 'updatesiteemaildata';
            $sedata['data']['siteemailid'] = $siteemailid;
            $seres = MAKE_CURL_CALL($sedata);

            if ($seres) {
                $status = 1;
                $msg = 'Site email data update success!';
                $data = '';
            } else {
                $status = 0;
                $msg = 'Failed to update Site email data!';
                $data = '';
            }
        } else {
            $status = 0;
            $msg = 'Failed to send mail';
            $data = '';
        }
    }
    $retInfo = ["status" => $status, "msg" => $msg, "data" => $data];
    return $retInfo;
}

function get_emails($siteid, $emailid)
{

    $sedata['function'] = 'getsiteemailinfo';
    $sedata['data']['siteid'] = $siteid;
    $sedata['data']['emailid'] = $emailid;

    $emaildata = MAKE_CURL_CALL($sedata);

    $siteEmailId = $emaildata['siteemailid'];
    return $siteEmailId;
}

function createInstallUserFunc($firstname, $lastname, $emailid, $roleType, $siteList)
{
    $adminEmail = $_SESSION['user']['adminEmail'];
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Users where user_email = ? limit 1";
    $res = NanoDB::find_one($sql, null, [$adminEmail]);

    $sb_userid = '213';
    $serverid = $res['subch_id'];
    $skuitem = $res['channel_id'];
    $startup = 'All';
    $followon = 'All';
    $delay = 86400;

    $srdata['function'] = 'createinstalluser';
    $srdata['data']['emailid'] = $emailid;
    $srdata['data']['password'] = md5('nanoheal@123');
    $srdata['data']['fname'] = $firstname;
    $srdata['data']['lname'] = $lastname;
    $srdata['data']['sbuserid'] = $sb_userid;
    $srdata['data']['serverid'] = $serverid;
    $srdata['data']['skuitem'] = $skuitem;
    $srdata['data']['startup'] = $startup;
    $srdata['data']['followon'] = $followon;
    $srdata['data']['delay'] = $delay;
    $emaildata = MAKE_CURL_CALL($srdata);

    $retInfo = ["status" => $emaildata['status'], "msg" => $emaildata['msg']];
    return $retInfo;
}




function make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}


function getSkuListFunc()
{
    $data = "";

    $skudata['function'] = 'getskulist';
    $skuofferingsdata = MAKE_CURL_CALL($skudata);

    if (safe_count($skuofferingsdata) > 0) {
        foreach ($skuofferingsdata as $value) {
            $offerid = $value['sid'];
            $offername = $value['name'];
            $noOfDays = $value['trialperiod'];
            $data .= "<option value='$offerid###$noOfDays'>$offername</option>";
        }
        $msg = "Offerings found";
        $status = 1;
    } else {
        $msg = "No Offerings found!";
        $status = 0;
    }
    $retInfo = ["status" => $status, "msg" => $msg, "data" => $data];
    return $retInfo;
}

function getSkuId($skuName)
{
    $skuArr = [];
    $skudata['function'] = 'getskulist';
    $skuofferingsdata = MAKE_CURL_CALL($skudata);
    foreach ($skuofferingsdata as $value) {
        if ($value['name'] == $skuName) {
            $skuArr['sid'] = $value['sid'];
        }
    }

    return $skuArr;
}



function getLicenseDetailsFunc($sitename)
{
    global $dashboardDownload;
    global $base_url;
    $pdo = pdo_connect();
    $data = [];

    $licnsdata['function'] = 'getlicensedetails';
    $licnsdata['data']['sitename'] = $sitename;
    $licensedetails = MAKE_CURL_CALL($licnsdata);
    // var_dump($licensedetails);

    $data['skuname'] = $licensedetails['skuname'];
    $data['maxinstall'] = $licensedetails['maxinstall'];
    $data['numofinstall'] = isset($licensedetails['numinstall']) ? $licensedetails['numinstall'] : 0;
    $data['regcode'] = $licensedetails['regcode'];
    $data['siteemailid'] = $licensedetails['siteemailid'];
    $data['licenseurl'] = $licensedetails['domainurl'];
    $data['wsurl'] = $licensedetails['wsurl'];
    $data['isDownViaDash'] = 'NO';
    if ($dashboardDownload == 'YES') {
        $data['licenseurl'] = $base_url;
        $data['isDownViaDash'] = 'YES';
    }

    $licenseUrl = $licensedetails['domainurl'];
    $wsurlAbs = $licensedetails['wsurl'];
    $wsurltemp = explode('//', $wsurlAbs);
    $wsurl = $wsurltemp[1];
    $opt_sql = "update " . $GLOBALS['PREFIX'] . "core.Options set value = ? where name = ?";
    NanoDB::query($opt_sql,  ['{"licenseurl": "' . $licenseUrl . '", "wsurl": "' . $wsurl . '"}', 'dashboard_config']);

    $retInfo = ["status" => 1, "msg" => 'License details', "data" => $data];
    return $retInfo;
}

function MAKE_CURL_CALL($data)
{
    global $dashlicenseapiurl;
    $data_string = json_encode($data);
    $header = array(
        'Content-Type: application/json',
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),
        'PHPSESSID: ' . session_id(),

    );

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_URL, $dashlicenseapiurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($ch);
        // var_dump($result);

        $presdata = safe_json_decode($result, true);
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
    return $presdata;
}
