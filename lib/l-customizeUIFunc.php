<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-rcmd.php';
global $base_path;

include_once '../lib/l-setTimeZone.php';

nhRole::dieIfnoRoles(['branding']); // roles: branding.

//Replace $routes['post'] with if else
if (url::postToText('function') === 'uploadHeaderLogo') { // roles: branding
    uploadHeaderLogo();
} else if (url::postToText('function') === 'uploadShortcutIcon') { // roles: branding
    uploadShortcutIcon();
} else if (url::postToText('function') === 'uploadBGImage') { // roles: branding
    uploadBGImage();
} else if (url::postToText('function') === 'uploadLandingBGImage') { // roles: branding
    uploadLandingBGImage();
} else if (url::postToText('function') === 'uploadBrandingImage') { // roles: branding
    uploadBrandingImage();
} else if (url::postToText('function') === 'saveUpdateConfiguration') { // roles: branding
    saveUpdateConfiguration();
} else if (url::postToText('function') === 'saveClientBrandingImages') { // roles: branding
    saveClientBrandingImages();
} else if (url::postToText('function') === 'saveClientLandingPageInfo') { // roles: branding
    saveClientLandingPageInfo();
} else if (url::postToText('function') === 'createDefaultClientBranding') { // roles: branding
    createDefaultClientBranding();
} else if (url::postToText('function') === 'sendTestUrlEmail') { // roles: branding
    sendTestUrlEmail();
} else if (url::postToText('function') === 'save_EmailTemplateInfo') { // roles: branding
    save_EmailTemplateInfo();
} else if (url::postToText('function') === 'set_BrandingConfigName') { // roles: branding
    set_BrandingConfigName();
} else if (url::postToText('function') === 'updateClientBrandingUrl') { // roles: branding
    updateClientBrandingUrl();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'get_CustomizeConfDetails') { // roles: branding
    get_CustomizeConfDetails();
}


function removeBG()
{
    $file = url::postToText('filename');
    if (!unlink("../admin/temp/" . $file)) {
        echo ("Error deleting $file");
    } else {
        echo ("Deleted $file");
    }
}

function uploadHeaderLogo()
{

    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    if ($_FILES['file']['error'] != 0) {
        $response = array('status' => 'error', 'message' => 'Error uploading file. Try again!');
    } else if (!strpos($_FILES['file']['type'], 'png')) {
        $response = array('status' => 'error', 'message' => 'Invalid file format!');
    } else {
        $logoName = 'logo_' . $cid . '_' . $pid . time() . '.png';
        $file_ext = explode('.', $logoName);
        if ($file_ext[1] != 'png') {
            $response = array('status' => 'error', 'message' => 'Invalid file Bypassed!');
        } else {
            if (!is_dir('../admin/temp')) {
                mkdir('../admin/temp');
            }
            unlink('../admin/temp/' . $_SESSION['logoname']);
            move_uploaded_file($_FILES['file']['tmp_name'], '../admin/temp/' . $_FILES['file']['name']);

            $_SESSION['logoname'] = $logoName;

            rename('../admin/temp/' . $_FILES['file']['name'], '../admin/temp/' . $logoName);
            $response = array('status' => 'success', 'message' => 'Logo uploaded successfully', 'logo_filename' => $logoName);
        }
    }
    ob_clean();
    $auditRes = create_auditLog('Branding', 'Header Logo Modification', 'Success');
    echo json_encode($response);
}

function uploadShortcutIcon()
{

    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    if ($_FILES['file']['error'] != 0) {
        $response = array('status' => 'error', 'message' => 'Error uploading file. Try again!');
    } else if (!strpos($_FILES['file']['type'], 'ico')) {
        $response = array('status' => 'error', 'message' => 'Invalid file format!');
    } else {
        $shortcutname = 'favicon_' . $cid . '_' . $pid . '.ico';
        $file_ext = explode('.', $shortcutname);
        if ($file_ext[1] != 'ico') {
            $response = array('status' => 'error', 'message' => 'Invalid file Bypassed!');
        } else {
            if (!is_dir('../admin/temp')) {
                mkdir('../admin/temp');
            }
            unlink('../admin/temp/' . $_SESSION['shortcutname']);
            move_uploaded_file($_FILES['file']['tmp_name'], '../admin/temp/' . $_FILES['file']['name']);

            $_SESSION['shortcutname'] = $shortcutname;

            rename('../admin/temp/' . $_FILES['file']['name'], '../admin/temp/' . $shortcutname);
            $response = array('status' => 'success', 'message' => 'Shortcut Icon uploaded successfully', 'shortcut_filename' => $shortcutname);
        }
    }
    ob_clean();
    $auditRes = create_auditLog('Branding', 'Shortcut Icon Modification', 'Success');

    echo json_encode($response);
}

function uploadBGImage()
{
    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    if ($_FILES['file']['error'] != 0) {
        $response = array('status' => 'error', 'message' => 'Error uploading file. Try again!');
    } else if (!strpos($_FILES['file']['type'], 'png')) {
        $response = array('status' => 'error', 'message' => 'Invalid file format!');
    } else {
        $bgimageName = 'bgimage_' . $cid . '_' . $pid . '.png';
        $file_ext = explode('.', $bgimageName);
        if ($file_ext[1] != 'png') {
            $response = array('status' => 'error', 'message' => 'Invalid file Bypassed.');
        } else {
            if (!is_dir('../admin/temp')) {
                mkdir('../admin/temp');
            }
            unlink('../admin/temp/' . $_SESSION['bgimage']);
            move_uploaded_file($_FILES['file']['tmp_name'], '../admin/temp/' . $_FILES['file']['name']);

            $_SESSION['bgimage'] = $bgimageName;

            rename('../admin/temp/' . $_FILES['file']['name'], '../admin/temp/' . $bgimageName);
            $response = array('status' => 'success', 'message' => 'Background Image uploaded successfully!', 'bg_filename' => $bgimageName);
        }
    }
    ob_clean();
    $auditRes = create_auditLog('Branding', 'Background Image Modification', 'Success');
    echo json_encode($response);
}

function saveUpdateConfiguration()
{

    $db = pdo_connect();

    try {
        $hdrColor = url::postToText('hdrcolor');
        $ftrColor = url::postToText('ftrcolor');
        $btnColor = url::postToText('btncolor');

        $welcomeMsg = url::postToText('welcomemsg');
        $termsLink = url::postToText('termslink');
        $supportphone = url::postToText('supportphone');
        $chaturl = url::postToText('chaturl');

        $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
        $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

        $jsonData['folderPath'] = '../../Branding/cust_' . $cid . '_' . $pid;
        $jsonData['nameToUse'] = 'cust_' . $cid . '_' . $pid;
        $jsonData['logoPath'] = '../../Branding/cust_' . $cid . '_' . $pid . '/images/logo.png';
        $jsonData['shortccutPath'] = '../../Branding/cust_' . $cid . '_' . $pid . '/images/shortcuticon.ico';
        $jsonData['bgimagePath'] = '../../Branding/cust_' . $cid . '_' . $pid . '/images/bg-img.png';

        mkdir('../../Branding');

        $folderToCreate = "../../Branding/cust_" . $cid . "_" . $pid;
        mkdir($folderToCreate);
        recursive_copy('../admin/config', $folderToCreate . '/config');
        recursive_copy('../admin/images', $folderToCreate . '/images');

        $fp = fopen($folderToCreate . '/config/color-template.css', 'a');
        if ($hdrColor != '') {
            fwrite($fp, PHP_EOL . PHP_EOL);
            fwrite($fp, '.header-wrap { background-color: ' . $hdrColor . '; }');
            $jsonData['headerColor'] = $hdrColor;
        }

        if ($ftrColor != '') {
            fwrite($fp, PHP_EOL . PHP_EOL);
            fwrite($fp, '.footer-left { background-color: ' . $ftrColor . '; } ');
            fwrite($fp, '.footer-right { background-color: ' . $ftrColor . '; }');
            $jsonData['footerColor'] = $ftrColor;
        }

        if ($btnColor != '') {
            fwrite($fp, PHP_EOL . PHP_EOL);
            fwrite($fp, '.welcome-page .btn-color-font-bg { background-color: ' . $btnColor . '; } ');
            fwrite($fp, '.btn-color-font-bg { background-color: ' . $btnColor . '; } ');
            $jsonData['buttonColor'] = $btnColor;
        }
        fclose($fp);

        $confile = fopen($folderToCreate . '/config/config.js', 'a');
        if ($welcomeMsg != '') {
            fwrite($confile, PHP_EOL . PHP_EOL);
            fwrite($confile, 'ConfigObj.installer.textChanges.ins_home_title = "' . $welcomeMsg . '";');
            $jsonData['welcomeMsg'] = $welcomeMsg;
        }
        if ($termsLink != '') {
            fwrite($confile, PHP_EOL . PHP_EOL);
            $termsAgreeText = "I agree to the <span><a href='" . $termsLink . "' target='_blank' style='text-decoration: underline;'>Terms and Conditions</a></span> associated with this product";
            fwrite($confile, 'ConfigObj.installer.textChanges.termsAgreeText = "' . $termsAgreeText . '";');
            $jsonData['termsLink'] = $termsLink;
        }
        if ($supportphone != '') {
            fwrite($confile, PHP_EOL . PHP_EOL);
            fwrite($confile, 'ConfigObj.installer.textChanges.supportPhoneNo = "' . $supportphone . '";');
            $jsonData['supportphone'] = $supportphone;
        }
        if ($chaturl != '') {
            fwrite($confile, PHP_EOL . PHP_EOL);
            fwrite($confile, 'ConfigObj.installer.textChanges.chatUrl = "' . $chaturl . '";');
            $jsonData['chatUrl'] = $chaturl;
        }
        fclose($confile);

        if ($_SESSION['logoname'] != '') {
            rename('../admin/temp/' . $_SESSION['logoname'], '../admin/temp/logo.png');
            copy('../admin/temp/logo.png', $folderToCreate . '/images/logo.png');
        }

        if ($_SESSION['bgimage'] != '') {
            rename('../admin/temp/' . $_SESSION['bgimage'], '../admin/temp/bg-img.png');
            copy('../admin/temp/bg-img.png', $folderToCreate . '/images/bg-img.png');
        }

        $now = time();

        $updateCustomizeSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Customers set confstatus = '1', lastmodified = ? where customer = ?");
        $updateCustomizeSql->execute([$now, $cid]);
        $db->lastInsertId();

        $jsonfile = fopen($folderToCreate . '/cust_' . $cid . '_' . $pid . '.json', 'w');
        fwrite($jsonfile, json_encode($jsonData));
        fclose($jsonfile);

        $auditRes = create_auditLog('Branding', 'Modifictaion', 'Success', $_POST);

        $returnData = 'success';
        echo $returnData;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        $auditRes = create_auditLog('Branding', 'Modifictaion', 'Failed', $_POST);

        echo $exc->getTraceAsString();
    }
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
    unlink($folderToZip . '/cust_' . $cid . '_' . $pid . '.zip');
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


function uploadBrandingImage()
{

    $brandingVal = url::issetInPost('function') ? url::postToText('function') : 'branding1';
    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    if ($_FILES['file']['error'] != 0) {
        $response = array('status' => 'error', 'message' => 'Error uploading file. Try again!');
    } else if (!strpos($_FILES['file']['type'], 'png')) {
        $response = array('status' => 'error', 'message' => 'Invalid file format!');
    } else {
        $branding_imageName = $brandingVal . '_' . $cid . '_' . $pid . '.png';
        $file_ext = explode('.', $branding_imageName);
        if ($file_ext[1] != 'png') {
            $response = array('status' => 'error', 'message' => 'Invalid file Bypassed!');
        } else {
            if (!is_dir('../admin/temp')) {
                mkdir('../admin/temp');
            }
            move_uploaded_file($_FILES['file']['tmp_name'], '../admin/temp/' . $_FILES['file']['name']);

            unlink('../admin/temp/' . $_SESSION[$brandingVal . 'image']);

            $_SESSION[$brandingVal . 'image'] = $branding_imageName;

            rename('../admin/temp/' . $_FILES['file']['name'], '../admin/temp/' . $branding_imageName);
            $response = array('status' => 'success', 'message' => 'Branding Image uploaded successfully!', 'filename' => $branding_imageName);
        }
    }
    ob_clean();
    echo json_encode($response);
}

function saveClientBrandingImages()
{
    $db = pdo_connect();

    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    $rotationSpeed = url::postToText('rotspeed');
    $url1 = url::postToText('URLVal1');
    $url2 = url::postToText('URLVal2');
    $url3 = url::postToText('URLVal3');
    $url4 = url::postToText('URLVal4');
    $url5 = url::postToText('URLVal5');

    $txt1 = url::postToText('TextVal1');
    $txt2 = url::postToText('TextVal2');
    $txt3 = url::postToText('TextVal3');
    $txt4 = url::postToText('TextVal4');
    $txt5 = url::postToText('TextVal5');

    try {
        $folderToCreate = "../../Branding/cust_" . $cid . "_" . $pid;
        mkdir($folderToCreate);


        if ($_SESSION['branding1image'] != '') {
            rename('../admin/temp/' . $_SESSION['branding1image'], '../admin/temp/group-1.png');
            copy('../admin/temp/group-1.png', $folderToCreate . '/images/group-1.png');
        }
        if ($_SESSION['branding2image'] != '') {
            rename('../admin/temp/' . $_SESSION['branding2image'], '../admin/temp/group-2.png');
            copy('../admin/temp/group-2.png', $folderToCreate . '/images/group-2.png');
        }
        if ($_SESSION['branding3image'] != '') {
            rename('../admin/temp/' . $_SESSION['branding3image'], '../admin/temp/group-3.png');
            copy('../admin/temp/group-3.png', $folderToCreate . '/images/group-3.png');
        }
        if ($_SESSION['branding4image'] != '') {
            rename('../admin/temp/' . $_SESSION['branding4image'], '../admin/temp/group-4.png');
            copy('../admin/temp/group-4.png', $folderToCreate . '/images/group-4.png');
        }
        if ($_SESSION['branding5image'] != '') {
            rename('../admin/temp/' . $_SESSION['branding5image'], '../admin/temp/group-5.png');
            copy('../admin/temp/group-5.png', $folderToCreate . '/images/group-5.png');
        }

        $cfname = 'cust_' . $cid . '_' . $pid;

        $customerInfo = file_get_contents('../../Branding/' . $cfname . '/' . $cfname . '.json', 'r');
        $customerData = safe_json_decode($customerInfo, true);

        $customerData['rotationSpeed'] = $rotationSpeed;
        $customerData['url1'] = $url1;
        $customerData['url2'] = $url2;
        $customerData['url3'] = $url3;
        $customerData['url4'] = $url4;
        $customerData['url5'] = $url5;
        $customerData['txt1'] = $txt1;
        $customerData['txt2'] = $txt2;
        $customerData['txt3'] = $txt3;
        $customerData['txt4'] = $txt4;
        $customerData['txt5'] = $txt5;
        $jsonfile = fopen($folderToCreate . '/cust_' . $cid . '_' . $pid . '.json', 'w');
        fwrite($jsonfile, json_encode($customerData));
        fclose($jsonfile);

        $now = time();

        $updateCustomizeSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Customers set confstatus = '1', lastmodified = ? where customer = ?");
        $updateCustomizeSql->execute([$now, $cid]);
        $res = $updateCustomizeSql->rowCount();
        if ($res) {
            $auditRes = create_auditLog('Branding', 'Branding Images Modification', 'Success');
            $returnData = 'success';
        } else {
            $auditRes = create_auditLog('Branding', 'Branding Images Modification', 'Failed');
            $returnData = 'error';
        }
        echo $returnData;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        echo $exc->getTraceAsString();
    }
}

function get_CustomizeConfDetails()
{
    $db = pdo_connect();

    $draw = 1;
    $totalCount = 0;
    $recordList = [];

    $logged_username = $_SESSION["user"]["logged_username"];


    $customizeSql = $db->prepare("select c.*, u.user_email from " . $GLOBALS['PREFIX'] . "core.Customers c, " . $GLOBALS['PREFIX'] . "core.Users u where c.username = ? and c.username = u.username and c.customer != '' group by customer");
    $customizeSql->execute([$logged_username,]);
    $customizeRes = $customizeSql->fetchAll();

    if (safe_count($customizeRes) > 0) {
        $totalCount = safe_count($customizeRes);
        $recordList = formatCustomizeConfDetails($customizeRes);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);

    $auditRes = create_auditLog('Branding', 'View', 'Success');

    ob_clean();
    echo json_encode($jsonData);
}

function MSP_CreatPTag($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}

function MSP_CreatPTagEdit($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}

function formatCustomizeConfDetails($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {

        $companyName = MSP_CreatPTag($value['customer']);
        $emailId = MSP_CreatPTag($value['user_email']);
        $filenameVal = 'cust_' . $value['customer'] . '_' . $value['id'] . '.zip';
        $filename = MSP_CreatPTag($filenameVal);

        if ($value['confstatus'] == '0') {
            $clientuiConf = MSP_CreatPTag('No');
        } else {
            $clientuiConf = MSP_CreatPTag('Yes');
        }

        if ($value['lastmodified'] > 0) {

            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $time = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['lastmodified'], "m/d/Y h:i A");
            } else {
                $time = date("m/d/Y h:i A", $value['lastmodified']);
            }
            $lastModified = MSP_CreatPTagEdit($time);
        } else {
            $lastModified = MSP_CreatPTagEdit("");
        }

        $rowId = $value['customer'] . '_' . $value['id'];

        $array[] = array(
            "DT_RowId" => $rowId,
            'customername' => $companyName,
            'emailid' => $emailId,
            'filename' => $filename,
            'clientuiconf' => $clientuiConf,
            'lastmodified' => $lastModified
        );
    }
    return $array;
}

function uploadLandingBGImage()
{

    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    if ($_FILES['file']['error'] != 0) {
        $response = array('status' => 'error', 'message' => 'Error uploading file. Try again!');
    } else if (!strpos($_FILES['file']['type'], 'png')) {
        $response = array('status' => 'error', 'message' => 'Invalid file format!');
    } else {
        $lpbgimageName = 'lpbgimage_' . $cid . '_' . $pid . '.png';
        $file_ext = explode('.', $lpbgimageName);
        if ($file_ext[1] != 'png') {
            $response = array('status' => 'error', 'message' => 'Invalid file Bypassed!');
        } else {
            if (!is_dir('../admin/temp')) {
                mkdir('../admin/temp');
            }
            unlink('../admin/temp/' . $_SESSION['lpbgimage']);
            move_uploaded_file($_FILES['file']['tmp_name'], '../admin/temp/' . $_FILES['file']['name']);

            $_SESSION['lpbgimage'] = $lpbgimageName;

            rename('../admin/temp/' . $_FILES['file']['name'], '../admin/temp/' . $lpbgimageName);
            $response = array('status' => 'success', 'message' => 'Background Image uploaded successfully!', 'lp_bg_filename' => $lpbgimageName);
        }
    }
    ob_clean();

    $auditRes = create_auditLog('Branding', 'Landing Background Images Modification', 'Success');
    echo json_encode($response);
}

function saveClientLandingPageInfo()
{
    $db = pdo_connect();

    $lpwcmsgtitle = url::postToText('lpwcmsgtitle');
    $lpwcmsg = url::postToText('lpwcmsg');

    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    $sdata['function'] = 'getinstallsiteinfo';
    $sdata['data']['sitenameorid'] = $cid;
    $siteData = MAKE_CURL_CALL($sdata);

    $regcode = $siteData['regcode'];
    $siteid = $siteData['siteid'];
    $email = $siteData['email'];
    $siteemailid = get_emails($siteid, $email);

    $stmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'dashboard_config' limit 1");
    $stmt->execute();
    $confres = $stmt->fetch();
    $confdata = safe_json_decode($confres['value'], true);
    $licenseurl = $confdata['licenseurl'];

    $url = $licenseurl . "install/d.php";
    $responseurl = "$url?r=$regcode&e=$siteemailid";

    $folderToCreate = "../../Branding/cust_" . $cid . "_" . $pid;

    if (!is_dir($folderToCreate)) {
        mkdir($folderToCreate);
    }

    if ($_SESSION['lpbgimage'] != '') {
        rename('../admin/temp/' . $_SESSION['lpbgimage'], '../admin/temp/bg-img.png');
        copy('../admin/temp/bg-img.png', $folderToCreate . '/images/bg-img.png');
    }

    $cfname = 'cust_' . $cid . '_' . $pid;

    $customerInfo = file_get_contents('../../Branding/' . $cfname . '/' . $cfname . '.json', 'r');
    $customerData = safe_json_decode($customerInfo, true);

    $customerData['landingWelcomeTitle'] = $lpwcmsgtitle;
    $customerData['landingWelcomeMsg'] = $lpwcmsg;
    $customerData['landingBgImagePath'] = '../../Branding/cust_' . $cid . '_' . $pid . '/images/bg-img.png';

    $jsonfile = fopen($folderToCreate . '/cust_' . $cid . '_' . $pid . '.json', 'w');
    fwrite($jsonfile, json_encode($customerData));
    fclose($jsonfile);

    $now = time();

    $updateCustomizeSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Customers set confstatus = '1', lastmodified = ? where customer = ?");
    $updateCustomizeSql->execute([$now, $cid]);
    $updateCustomizeRes = $db->lastInsertId();

    ob_clean();
    if ($updateCustomizeRes) {

        $auditRes = create_auditLog('Branding', 'Landing Page Modification', 'Success');

        echo 'Landing Update Success###' . urlencode($responseurl);
    } else {

        $auditRes = create_auditLog('Branding', 'Landing Page Modification', 'Failed');

        echo 'Landing Update Failed###' . urlencode($responseurl);
    }
}

function save_EmailTemplateInfo()
{
    global $base_url;
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $base_path = $pageURL . $_SERVER["HTTP_HOST"];

    $db = pdo_connect();

    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];

    $emailsub = url::postToText('emailsub');
    $emailtitle = '';
    $emailbody = url::postToAny('emailbody');



    $folderToCreate = "../../Branding/cust_" . $cid . "_" . $pid;

    if (!is_dir($folderToCreate)) {
        mkdir($folderToCreate);
    }

    $emailContent = str_replace('../assets/img/boxTop.png', $base_url . 'assets/img/boxTop.png', $emailContent);
    $emailContent = str_replace($folderToCreate . '/images/logo.png', $base_path . '/Branding/cust_' . $cid . "_" . $pid . '/images/logo.png', $emailContent);

    $cfname = 'cust_' . $cid . '_' . $pid;
    $customerInfo = file_get_contents('../../Branding/' . $cfname . '/' . $cfname . '.json', 'r');
    $customerData = safe_json_decode($customerInfo, true);

    $customerData['emailSubject'] = $emailsub;
    $customerData['emailBody'] = $emailbody;


    $jsonfile = fopen($folderToCreate . '/cust_' . $cid . '_' . $pid . '.json', 'w');
    fwrite($jsonfile, json_encode($customerData));
    fclose($jsonfile);

    $now = time();

    $updateCustomizeSql = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Customers set confstatus = '1', lastmodified = ?, emailsubject=?,
            emailtitle=?, emailbody=? where customer = ?");
    $updateCustomizeSql->execute([$now, $emailsub, $emailtitle, $emailbody, $cid]);
    $updateCustomizeRes = $db->lastInsertId();

    $res = createZipArchive($folderToCreate, $cid, $pid);

    uploadBrandingRemoteFTP($folderToCreate, $cid, $pid);

    if ($res) {
        $_SESSION['zipname'] = $folderToCreate;
    }

    ob_clean();
    if ($updateCustomizeRes) {
        $auditRes = create_auditLog('Branding', 'Email Template Modification', 'Success');
        echo 'Email Template Update Success';
    } else {
        $auditRes = create_auditLog('Branding', 'Email Template Modification', 'Failed');
        echo 'Email Template Update Failed';
    }
}

function uploadBrandingRemoteFTP($folderToCreate, $cid, $pid)
{
    global $ftp_server;
    global $ftp_user_name;
    global $ftp_user_pass;
    $ftp_brand_path = '/home/nanoheal/setups/branding';


    $login_result = ftp_login($ftp_conn, $ftp_user_name, $ftp_user_pass);

    $upldstatus = '';
    if ((!$ftp_conn) || (!$login_result)) {
        $upldstatus = 'login failed';
    }

    if ($upldstatus == '') {

        if (!ftp_chdir($ftp_conn, $ftp_brand_path)) {
            ftp_mkdir($ftp_conn, $ftp_brand_path);
        }

        $source_file = $folderToCreate . '/cust_' . $cid . '_' . $pid . '.zip';
        $destin_file = $ftp_brand_path . '/cust_' . $cid . '_' . $pid . '.zip';

        $upload = ftp_put($ftp_conn, $destin_file, $source_file, FTP_BINARY);
        if (!$upload) {
            $upldstatus = 'saving file failed';
        } else {
            ftp_chmod($ftp_conn, 0777, $destin_file);
            $upldstatus = 'success';
        }
    }

    ftp_close($ftp_conn);

    return $upldstatus;
}

function sendTestUrlEmail()
{
    global $base_url;
    $db = pdo_connect();

    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    $base_path = $pageURL . $_SERVER["HTTP_HOST"];
    $mailTo = url::postToText('emailTo');
    $cid = url::issetInPost('custcid') ? url::postToText('custcid') : $_SESSION["user"]["cId"];
    $pid = url::issetInPost('custpid') ? url::postToText('custpid') : $_SESSION["user"]["pid"];
    $emailsub = url::postToText('emailsub');
    $emailContent = urldecode(url::postToAny('emailContent'));

    $sdata['function'] = 'getinstallsiteinfo';
    $sdata['data']['sitenameorid'] = $cid;
    $siteData = MAKE_CURL_CALL($sdata);

    $regcode = $siteData['regcode'];
    $siteid = $siteData['siteid'];
    $email = $siteData['email'];
    $siteemailid = get_emails($siteid, $email);

    $folderToCreate = "../../Branding/cust_" . $cid . "_" . $pid;
    if (!is_dir($folderToCreate)) {
        mkdir($folderToCreate);
    }

    $stmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'dashboard_config' limit 1");
    $stmt->execute();
    $confres = $stmt->fetch();

    $confdata = safe_json_decode($confres['value'], true);
    $licenseurl = $confdata['licenseurl'];

    $url = $licenseurl . "install/d.php";
    $responseurl = "$url?r=$regcode&e=$siteemailid";

    $emailContent = str_replace('../assets/img/boxTop.png', $base_url . 'assets/img/boxTop.png', $emailContent);
    $emailContent = str_replace($folderToCreate . '/images/logo.png', $base_path . '/Branding/cust_' . $cid . "_" . $pid . '/images/logo.png', $emailContent);
    $emailContent = str_replace('%url%', $responseurl, $emailContent);

    $emailfile = fopen($folderToCreate . '/cust_mail_' . $cid . '_' . $pid . '.html', 'w');
    fwrite($emailfile, $emailContent);
    fclose($emailfile);

    $cfname = 'cust_' . $cid . '_' . $pid;
    $customerInfo = file_get_contents('../../Branding/' . $cfname . '/' . $cfname . '.json', 'r');
    $customerData = safe_json_decode($customerInfo, true);

    $subject = isset($customerData['emailSubject']) ? $customerData['emailSubject'] : $emailsub;

    $mail_file_name = 'cust_mail_' . $cid . '_' . $pid;
    $mailContentData = file_get_contents('../../Branding/' . $cfname . '/' . $mail_file_name . '.html', 'r');


    $message = $mailContentData;

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    $headers .= 'From: noreply@nanoheal.com>' . "\r\n";

    //    $res = mail($mailTo, $subject, $message, $headers);
    // send from visualisationService
    $arrayPost = array(
        'from' => getenv('SMTP_USER_LOGIN'),
        'to' => $mailTo,
        'subject' => $subject,
        'text' => '',
        'html' => $message,
        'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL') . "/mailer/sendmassage";
    $result = CURL::sendDataCurl($url, $arrayPost);

    ob_clean();
    if ($result) {
        echo 'Mailed Successfully';
    } else {
        echo 'Mail Failed';
    }
}

function get_emails($siteid, $email)
{

    $sedata['function'] = 'getsiteemailinfo';
    $sedata['data']['siteid'] = $siteid;
    $sedata['data']['emailid'] = $email;

    $emaildata = MAKE_CURL_CALL($sedata);

    $siteEmailId = $emaildata['siteemailid'];

    return $siteEmailId;
}

function createDefaultClientBranding()
{

    $custData = url::postToText('selected');
    $data = explode('_', $custData);
    $cid = $data[0];
    $pid = $data[1];

    $cfname = 'cust_' . $cid . '_' . $pid;
    $setDefBrandingPath = "../../Branding/cust_" . $cid . "_" . $pid;
    recursive_copy('../admin/config', $setDefBrandingPath . '/config');
    recursive_copy('../admin/images', $setDefBrandingPath . '/images');

    $jsonfile = fopen('../../Branding/' . $cfname . '/' . $cfname . '.json', 'w');
    fwrite($jsonfile, '');
    fclose($jsonfile);

    $files = glob('../admin/temp/*');
    foreach ($files as $file) {
        if (is_file($file))
            unlink($file);
    }

    $returnData = 'success';
    echo $returnData;
}

function MAKE_CURL_CALL($data)
{
    global $licenseapiurl;

    $data_string = json_encode($data);

    $header = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $licenseapiurl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $presdata = safe_json_decode($result, true);
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
    return $presdata;
}

function set_BrandingConfigName()
{
    $brandingConfigName = url::issetInPost('brandingconfval') ? url::postToText('brandingconfval') : '';

    $_SESSION['brandingconfval'] = $brandingConfigName;

    echo 'config updated';
}

function updateClientBrandingUrl()
{

    $sitename = url::issetInPost('sitename') ? url::postToText('sitename') : '';
    $siteid   = url::issetInPost('siteid') ? url::postToText('siteid') : '';

    $name = 'cust_' . $sitename . '_' . $siteid;
    $brandingurl = $name . '.zip';

    $rdata['function'] = 'updatebrandingurl';
    $rdata['data']['brandingurl'] = $brandingurl;
    $rdata['data']['sitename'] = $sitename;

    $upures = MAKE_CURL_CALL($rdata);
    return $upures;
}
