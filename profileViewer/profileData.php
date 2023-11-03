<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once("../include/common_functions.php");

include_once $absDocRoot . 'lib/l-db.php';
include_once $absDocRoot . 'lib/l-sql.php';
include_once $absDocRoot . 'lib/l-gsql.php';
include_once $absDocRoot . 'lib/l-rcmd.php';
include_once $absDocRoot . 'lib/l-util.php';
include_once $absDocRoot . 'lib/l-dashboard.php';
include_once $absDocRoot . 'lib/l-msp.php';
require_once $absDocRoot . 'lib/l-filterData.php';


global $db;
$db = db_connect();
nhRole::dieIfnoRoles(['profilewizard']);


if (url::postToText('function') === 'profiledata') { //roles: profilewizard
    profiledata();
}
if (url::postToText('function') === 'profiledataedit') { //roles: profilewizard
    profiledataedit();
}
if (url::postToText('function') === 'proimage') { //roles: profilewizard
    proimage();
}
if (url::postToText('function') === 'fileupload') { //roles: profilewizard
    fileupload();
}
if (url::postToText('function') === 'profiledataeditlogoText') { //roles: profilewizard
    profiledataeditlogoText();
}
if (url::postToText('function') === 'profiledataedituser') { //roles: profilewizard
    profiledataedituser();
}
if (url::postToText('function') === 'uploadProfileDatawithimg') { //roles: profilewizard
    uploadProfileDatawithimg();
}



function profiledata()
{
    $db = pdo_connect();
    $userid = strip_tags($_SESSION['user']['userid']);

    $proSqlRes = DASH_GetLoggedUserDetails('', $userid, $db);


    $userid = $proSqlRes['userid'];
    $chid = $proSqlRes['ch_id'];
    $uname = $proSqlRes['username'];
    $fname = $proSqlRes['firstName'];
    $lname = $proSqlRes['lastName'];
    $uemail = $proSqlRes['user_email'];
    $uphNo = $proSqlRes['user_phone_no'];
    $imgPath = $proSqlRes['imgPath'];
    $timezone = $proSqlRes['timezone'];
    $customerType = url::postToText('custtype');
    switch ($customerType) {
        case "0":
            $custType = "Admin";
            break;
        case "1":
            $custType = "Entity";
            break;
        case "2":
            $custType = "Channel";
            break;
        case "3":
            $custType = "Sub Channel";
            break;
        case "4":
            $custType = "Outsourced";
            break;
        case "5":
            $custType = "Customer";
            break;
        default:
            echo "";
    }

    $uroleId = $proSqlRes['role_id'];
    $rolSqlRes = DASH_GetLoggedUserType('', $uroleId, $db);
    $roleName = $rolSqlRes['displayName'];
    $record = array("uemail" => $uemail, "userid" => $userid, "role" => $roleName, "phno" => $uphNo, "cust" => $custType, "fname" => $fname, "lname" => $lname, "timezone" => "", "imgpath" => $imgPath);

    echo json_encode($record);
    exit;
}


function DASH_GetLoggedUserDetails($key, $userid, $db)
{

    $stmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
    $stmt->execute([$userid]);
    $proRes = $stmt->fetch(PDO::FETCH_ASSOC);

    return $proRes;
}
function DASH_GetLoggedUserType($key, $uroleId, $db)
{
    $stmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.RoleMapping where assignedRole = ?");
    $stmt->execute([$uroleId]);
    $rolRes = $stmt->fetch(PDO::FETCH_ASSOC);
    return $rolRes;
}

function DASH_EditProfile2($key, $fstname, $lstname, $phnum, $userid, $timeZone, $db)
{
    $updatesql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET firstName=?, lastName=?, user_phone_no=?, timezone=? where userid=?";
    $stmt = $db->prepare($updatesql);
    $updateres = $stmt->execute([$fstname, $lstname, $phnum, $timeZone, $userid]);

    return $updateres;
}

function profiledataedit()
{
    $db = pdo_connect();
    $custType = $_SESSION['user']['customerType'];
    $username = $_SESSION['user']['username'];
    $userEmail = $_SESSION['user']['adminEmail'];
    $channel_id = $_SESSION['user']['channelId'];
    $entity_id = $_SESSION['user']['entityId'];
    $eid = $_SESSION["user"]["cId"];
    $usertext = url::issetInRequest('text') ? url::requestToText('text') : '';

    PROFILE_AlterTable($db, "agent", "channel", "clientlogo", "VARCHAR(250)", "default");
    $res = MSP_GetEntityDetail($db, $eid);
    $clientlogo = $res['clientlogo'];

    if ($clientlogo == 'default') {
        $companyname = $res['companyName'];
        $UIDirectory = CUST_CreateClient_UIDirectory($companyname);
        $updatePath = MSP_Update_UIDirectoryPath($db, $eid, $UIDirectory);
        $clientlogo = $UIDirectory;
    }

    $sql = "select cssconfig from " . $GLOBALS['PREFIX'] . "core.Users where username=? and user_email=? limit 1";
    $pdo = $dbo->prepare($sql);
    $pdo->execute([$username, $userEmail]);
    $sqlresult = $pdo->fetch(PDO::FETCH_ASSOC);
    $custcssconfig = explode(',', $sqlresult['cssconfig']);



    $sqlchannel = "select U.cssconfig from " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "agent.channel C where U.ch_id = C.eid and U.user_email = C.emailId and C.eid=? limit 1";
    $pdo = $dbo->prepare($sqlchannel);
    $pdo->execute([$channel_id]);
    $sqlchannelres = $pdo->fetch(PDO::FETCH_ASSOC);
    $resellercssconfg = explode(',', $sqlchannelres['cssconfig']);


    $sqlentity = "select U.cssconfig from " . $GLOBALS['PREFIX'] . "core.Users U, " . $GLOBALS['PREFIX'] . "agent.channel C where U.ch_id = C.eid and U.user_email = C.emailId and C.eid=? limit 1";
    $pdo = $dbo->prepare($sqlentity);
    $pdo->execute([$entity_id]);
    $sqlentityres = $pdo->fetch(PDO::FETCH_ASSOC);
    $entitycssconfg = explode(',', $sqlentityres['cssconfig']);

    if (is_array($_FILES)) {
        $userclientlogo = $_FILES['upload_clientlogo']['name'];
        $targetDir = "../vendors/images";
        $userlogo = $_FILES['upload_logo']['name'];
        $userfooterlogo = $_FILES['upload_footerlogo']['name'];
        $upload_clientlogo_type = $_FILES["upload_clientlogo"]["type"];
        $upload_clientlogo_size = $_FILES["upload_clientlogo"]["size"];

        $upload_logo_type = $_FILES["upload_logo"]["type"];
        $upload_logo_size = $_FILES["upload_logo"]["size"];

        $upload_footerlogo_type = $_FILES["upload_footerlogo"]["type"];
        $upload_footerlogo_size = $_FILES["upload_footerlogo"]["size"];

        $maxsize = 2 * 1024 * 1024;

        if (($upload_clientlogo_size > $maxsize) || ($upload_logo_size > $maxsize) || ($upload_footerlogo_size > $maxsize)) {
            echo 'sizeError';
            return false;
        }

        if ($userclientlogo != '') {

            if (!validateMimeAndExtension('upload_clientlogo', array('image/png', 'image/jpeg'), array('png', 'jpg'), true)) {
                echo 'invalidFile';
                return false;
            }

            $userclientlogo_ext = pathinfo($userclientlogo, PATHINFO_EXTENSION);
            if (!array_key_exists($userclientlogo_ext, $allowed_ext)) {
                echo 'invalidFile';
                return false;
            }

            if (strpos($userclientlogo, 'alert') !== false) {
                echo 'invalidFile';
                return false;
            }
        }


        if ($userlogo != '') {
            if (!validateMimeAndExtension('upload_logo', array('image/png', 'image/jpeg'), array('png', 'jpg'), true)) {
                echo 'invalidFile';
                return false;
            }
            $userlogo_ext = pathinfo($userlogo, PATHINFO_EXTENSION);

            if (!array_key_exists($userlogo_ext, $allowed_ext)) {
                echo 'invalidFile';
                return false;
            }

            if (strpos($userlogo, 'alert') !== false) {
                echo 'invalidFile';
                return false;
            }
        }

        if ($userfooterlogo != '') {
            if (!validateMimeAndExtension('upload_footerlogo', array('image/png', 'image/jpeg'), array('png', 'jpg'), true)) {
                echo 'invalidFile';
                return false;
            }
            $userfooterlogo_ext = pathinfo($userfooterlogo, PATHINFO_EXTENSION);
            if (!array_key_exists($userfooterlogo_ext, $allowed_ext)) {
                echo 'invalidFile';
                return false;
            }

            if (strpos($userfooterlogo, 'alert') !== false) {
                echo 'invalidFile';
                return false;
            }
        }

        if (strpos($clientlogo, '.png') !== false || strpos($clientlogo, '.jpg') !== false || strpos($clientlogo, '.jpeg') !== false) {
            $array = explode('/', $clientlogo);
            array_pop($array);
            $path = implode('/', $array);
            $path = $path . "/" . $userclientlogo;
        } else {
            $path = "$clientlogo/" . $userclientlogo;
        }

        if (is_uploaded_file($_FILES['upload_logo']['tmp_name']) || is_uploaded_file($_FILES['upload_footerlogo']['tmp_name']) || is_uploaded_file($_FILES['upload_clientlogo']['tmp_name'])) {
            move_uploaded_file($_FILES['upload_logo']['tmp_name'], "$targetDir/" . $userlogo);
            move_uploaded_file($_FILES['upload_footerlogo']['tmp_name'], "$targetDir/" . $userfooterlogo);
            move_uploaded_file($_FILES['upload_clientlogo']['tmp_name'], $path);
            chmod($path, 0777);
        }

        if ($userclientlogo != '') {
            $sql = "update " . $GLOBALS['PREFIX'] . "agent.channel set clientlogo=? where eid=?";
            $pdo = $dbo->prepare($sql);
            $result = $pdo->execute([$path, $eid]);
        }

        if ($usertext != '') {
            uploadprofiletext($usertext, $db);
        }

        if ($custType == 5) {
            if ($custcssconfig[0] != 'default' && $custcssconfig[0] != '') {

                $myfile = fopen("../vendors/styles/$custcssconfig[0].css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/$custcssconfig[0].css"));
            } else if ($resellercssconfg[0] != 'default' && $resellercssconfg[0] != '') {

                $myfile = fopen("../vendors/styles/$resellercssconfg[0].css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/$resellercssconfg[0].css"));
            } else if ($entitycssconfg[0] != 'default' && $entitycssconfg[0] != '') {

                $myfile = fopen("../vendors/styles/$entitycssconfg[0].css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/$entitycssconfg[0].css"));
            } else {
                $myfile = fopen("../vendors/styles/config.css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/config.css"));
            }
        } else if ($custType == 2) {

            if ($custcssconfig[0] != 'default' && $custcssconfig[0] != '') {

                $myfile = fopen("../vendors/styles/$custcssconfig[0].css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/$custcssconfig[0].css"));
            } else if ($entitycssconfg[0] != 'default' && $entitycssconfg[0] != '') {

                $myfile = fopen("../vendors/styles/$entitycssconfg[0].css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/$entitycssconfg[0].css"));
            } else {

                $myfile = fopen("../vendors/styles/config.css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/config.css"));
            }
        } else if ($custType == 1) {

            if ($custcssconfig[0] != 'default' && $custcssconfig[0] != '') {

                $myfile = fopen("../vendors/styles/$custcssconfig[0].css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/$custcssconfig[0].css"));
            } else {

                $myfile = fopen("../vendors/styles/config.css", "r");
                $templ = fread($myfile, filesize("../vendors/styles/config.css"));
            }
        } else {
            $myfile = fopen("../vendors/styles/config.css", "r");
            $templ = fread($myfile, filesize("../vendors/styles/config.css"));
        }

        $searchfor = '.main-logo';
        $pattern = preg_quote($searchfor, '/');
        $pattern = "/^.*$pattern.*\$/m";

        if (preg_match_all($pattern, $templ, $matches)) {
            $logochange = implode("\n", $matches[0]) . PHP_EOL;
        }

        $searchfor1 = '.f-logo';
        $pattern1 = preg_quote($searchfor1, '/');
        $pattern1 = "/^.*$pattern1.*\$/m";

        if (preg_match_all($pattern1, $templ, $matches1)) {
            $logochange1 = implode("\n", $matches1[0]) . PHP_EOL;
        }

        if ($userlogo != '') {
            $usericon = $logochange;
            $usericon1 = explode("(", $usericon);
            $usericon12 = explode(".", $usericon1[1]);
            $iconuserimages = ".." . $usericon12[2] . ".png";
            $dirname = "../images/";
            $images = $dirname . $userlogo;
        } else {
            $file = $logochange;
            $arr = explode("(", $file);
            $arr11 = explode(".", $arr[1]);
            $images = ".." . $arr11[2] . ".png";
        }

        if ($userfooterlogo != '') {
            $icon = $logochange1;
            $icon1 = explode("(", $icon);
            $icon12 = explode(".", $icon1[1]);
            $iconfooterimages = ".." . $icon12[2] . ".png";
            $dirname1 = "../images/";
            $images1 = $dirname1 . $userfooterlogo;
        } else {
            $file1 = $logochange1;
            $arr1 = explode("(", $file1);
            $arr12 = explode(".", $arr1[1]);
            $images1 = ".." . $arr12[2] . ".png";
        }

        $logocH = str_replace($iconuserimages, $images, $logochange);
        $logocH1 = str_replace($iconfooterimages, $images1, $logochange1);
        $temp2 = str_replace("$logochange", "$logocH", $templ);
        $temp21 = str_replace("$logochange1", "$logocH1", $temp2);

        fclose($myfile);

        $logged_user = $_SESSION['user']['username'];
        $filemain = fopen("../vendors/styles/config_$logged_user.css", "w");
        chmod("../vendors/styles/config_$logged_user.css", 0777);
        fwrite($filemain, $temp21);
        fclose($filemain);

        echo 1;
    } else {
        echo 0;
    }
}

function profiledataedituser()
{
    $db = pdo_connect();
    $fstname = url::requestToText('firstname');
    $lstname = url::requestToText('lastname');
    $phnum = url::requestToText('phone_no');
    $userid = strip_tags($_SESSION['user']['userid']);
    $timeZone = url::requestToText('time_zone');
    $_SESSION['userTimeZone'] = $timeZone;

    $editprofile = DASH_EditProfile2('', $fstname, $lstname, $phnum, $userid, $timeZone, $db);

    if ($editprofile) {
        echo 1;
    } else {
        echo 0;
    }
}

function profiledataeditlogoText()
{
    $db = pdo_connect();
    $fstname = url::requestToText('firstname');
    $lstname = url::requestToText('lastname');
    $phnum = url::requestToText('phone_no');
    $uplodtext = url::requestToText('text');
    $userid = $_SESSION['user']['userid'];

    uploadprofiletext($uplodtext, $db);
    $editprofile = DASH_EditProfile2('', $fstname, $lstname, $phnum, $userid, $timeZone, $db);

    if ($editprofile) {
        echo 1;
    } else {
        echo 0;
    }
}



function fileupload()
{
    $logged_user = $_SESSION['user']['username'];
    $uid = $_SESSION['user']['adminid'];

    if (url::issetInPost('imagebase64')) {
        $data = url::postToText('imagebase64');

        list($type, $data) = explode(';', $data);
        list(, $data) = explode(',', $data);

        $data = base64_decode($data);
        $d = mt_rand(4, 30);

        $utc = time();
        $target_dir = "../profileViewer/profileImage";
        $image = $target_dir . "/" . $logged_user . "_" . $utc . ".png";

        file_put_contents($image, $data);

        $db = pdo_connect();
        $uploadimage = DASH_GetUploadImage('', $image, $uid, $db);

        if ($uploadimage) {
            $temp = 1;
            $src = $image;
        } else {
            $temp = 0;
            $src = "";
        }

        $sendData = array("res" => $temp, "src" => $src);
        echo json_encode($sendData);
    }
}



function proimage()
{
    exit('ghf');
    $db = pdo_connect();
    $userid = url::requestToText('userid');
    $imagepath = DASH_GetProfileImagePath('', $userid, $db);

    $record = array("img" => $imagepath);
    echo json_encode($record);
}

function uploadprofiletext($uplodtext, $db)
{
    $username = $_SESSION['user']['username'];
    $userEmail = $_SESSION['user']['adminEmail'];
    $uplodtext = strip_tags($uplodtext);

    $sql = "select cssconfig from " . $GLOBALS['PREFIX'] . "core.Users where username=? and user_email=? limit 1";
    $pdo = $db->prepare($sql);
    $pdo->execute([$username, $userEmail]);
    $sqlresult = $pdo->fetch(PDO::FETCH_ASSOC);

    $usercssconfig = explode(',', $sqlresult['cssconfig']);
    $sqltext = "update " . $GLOBALS['PREFIX'] . "core.Users SET cssconfig=? where username=? and user_email=?";
    $pdo = $db->prepare($sqltext);
    $pdo->execute(["config_" . $username . "," . $uplodtext, $username, $userEmail]);
}

function PROFILE_AlterTable($db, $dbName, $tableName, $columnName, $dataType, $defaultValue)
{

    if (!empty($dbName) && !FILTERDATA_validateAlphaNumeric($dbName)) {
        return false;
    }

    if (!empty($tableName) && !FILTERDATA_validateAlphaNumeric($tableName)) {
        return false;
    }
    if (!empty($columnName) && !FILTERDATA_validateAlphaNumeric($columnName)) {
        return false;
    }

    $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?";
    $pdo = $db->prepare($sql);
    $pdo->execute([$dbName, $tableName, $columnName]);
    $result = $pdo->fetch(PDO::FETCH_ASSOC);

    if (safe_count($result) > 0) {
        return TRUE;
    } else {
        $alertSql = "ALTER TABLE $dbName.$tableName ADD COLUMN $columnName $dataType NULL DEFAULT '$defaultValue'";
        $alterRes = redcommand($alertSql, $db);
        return TRUE;
    }
}

function uploadProfileDatawithimg()
{
    $fstname = url::requestToText('firstname');
    $lstname = url::requestToText('lastname');
    $phnum = url::requestToText('phone_no');
    $userid = strip_tags($_SESSION['user']['userid']);
    $timeZone = url::requestToText('timezone');
    $_SESSION['userTimeZone'] = $timeZone;
    $logged_user = $_SESSION['user']['username'];
    $uid = $_SESSION['user']['adminid'];
    $temp = 0;
    $src = "";

    if (isset($_SESSION['user']['username']) && $_SESSION['user']['username'] != '') {

        $db = pdo_connect();

        $editprofile = DASH_EditProfile2('', $fstname, $lstname, $phnum, $userid, $timeZone, $db);

        if ($editprofile) {
            $temp = 1;
            $src = "";
        } else {
            $temp = 0;
            $src = "";
        }
    }

    $sendData = array("res" => $temp, "src" => $src);

    echo json_encode($sendData);
    exit;
}
