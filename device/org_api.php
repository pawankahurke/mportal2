<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-quer.php';

include_once '../lib/l-svbt.php';
require_once '../include/common_functions.php';

include_once 'org_api_func.php';
include_once 'emailtemplate.php';

nhRole::dieIfnoRoles(['licensedetails']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'create_OrgInsSite') { //roles: licensedetails
    create_OrgInsSite();
} else if (url::postToText('function') === 'update_SiteEmailData') { //roles: licensedetails
    update_SiteEmailData();
} else if (url::postToText('function') === 'create_InstallUser') { //roles: licensedetails
    create_InstallUser();
} else if (url::postToText('function') === 'send_DownloadLinkMail') { //roles: licensedetails
    send_DownloadLinkMail();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'get_LicenseDetails') { //roles: licensedetails
    get_LicenseDetails();
} else if (url::postToText('function') === 'get_SkuList') { //roles: licensedetails
    get_SkuList();
} else if (url::postToText('function') === 'get_SkuId') { //roles: licensedetails
    get_SkuId();
} else if (url::postToText('function') === 'addNewSkuList') { //roles: licensedetails
    addNewSkuList();
} else if (url::postToText('function') === 'addLicenseDetails') { //roles: licensedetails
    addLicenseDetails();
} else if (url::postToText('function') === 'showLicenseDetails') { //roles: licensedetails
    showLicenseDetails();
} else if (url::postToText('function') === 'updateLicenseCount') { //roles: licensedetails
    updateLicenseCount();
}


function create_OrgInsSite()
{

    $sitename = url::postToText('sitename');
    $insusrid = $_SESSION['user']['userid'];
    $email_id = $_SESSION["user"]["adminEmail"];
    $serverid = $_SESSION['user']['serverid'];
    $sku_item = url::postToText('skuid');
    $startup = url::postToText('startup');
    $followon = url::postToText('followon');
    $delay = url::postToText('delay');

    if (preg_match('/^[a-zA-Z0-9_]*$/', $sitename)) {
        $retInfo = createOrgInsSiteFunc($sitename, $insusrid, $email_id, $serverid, $sku_item, $startup, $followon, $delay);
    } else {
        $retInfo = ["status" => 0, "msg" => "The site name must contain only alphanumeric values", "val" => 0];
    }

    echo json_encode($retInfo);
}

function getSiteEmailData()
{
    $siteEmailData = getSiteEmailDataFunc();

    echo json_encode($siteEmailData);
}

function update_SiteEmailData()
{
    $emailList = url::postToAny('email_list');
    $sitename = html_entity_decode(url::postToText('sitename'));
    $result = updateSiteEmailDataFunc($emailList, $sitename);

    echo json_encode($result);
}

function send_DownloadLinkMail()
{
    $siteid = url::postToAny('siteid');
    $emailList = url::postToAny('emailList');
    $result = sendDownloadLinkMailFunc($siteid, $emailList);

    echo json_encode($result);
}

function create_InstallUser()
{
    $firstname = url::postToText('fname');
    $lastname = url::postToText('lname');
    $emailid = url::postToText('emailid');
    $roleType = url::postToText('role');
    $siteList = url::postToAny('sitelist');

    $result = createInstallUserFunc($firstname, $lastname, $emailid, $roleType, $siteList);
    echo json_encode($result);
}

function get_SkuList()
{
    $result = getSkuListFunc();
    echo json_encode($result);
}

function get_SkuId()
{
    $skuName = url::postToText('SKU');
    $result = getSkuId($skuName);
    echo json_encode($result);
}

function get_LicenseDetails()
{
    $sitename = url::postToText('sitename');
    $result = getLicenseDetailsFunc($sitename);

    echo json_encode($result);
}

function addNewSkuList()
{
    $skuname = url::postToText('skuname');
    $skuDesc = url::postToText('skuDesc');
    $skuCat = url::postToText('skuCat');
    $skuBillType = url::postToText('skuBillType');
    $skuQty = url::postToText('skuQty');
    $skuAmt = url::postToText('skuAmt');
    $skuTrial = url::postToText('skuTrial');
    $skuBillCycle = url::postToText('skuBillCycle');
    $result = addNewSkuListFunc($skuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle);
    echo $result;
}

function addLicenseDetails()
{
    $skuname = url::postToText('skuname');
    $skuDesc = url::postToText('skuDesc');
    $skuCat = url::postToText('skuCat');
    $skuBillType = url::postToText('skuBillType');
    $skuQty = url::postToText('skuQty');
    $skuAmt = url::postToText('skuAmt');
    $skuTrial = url::postToText('skuTrial');
    $skuBillCycle = url::postToText('skuBillCycle');
    $licenseKey = url::postToAny('licenseKey');
    $result = addNewLicenseListFunc($skuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle, $licenseKey);
}

function showLicenseDetails()
{
    $skuname = url::postToText('checkKey');
    $result = showLicenseDetailsFunc($skuname);
    echo json_encode($result);
}

function updateLicenseCount()
{
    $oldskuname = url::postToText('oldSkuName');
    $newskuname = url::postToText('newSKUName');
    $skuDesc = url::postToText('newSKUDesc');
    $skuCat = url::postToText('newSKUCat');
    $skuBillType = url::postToText('newSKUBType');
    $skuQty = url::postToText('newSKUQty');
    $skuAmt = url::postToText('newSKUAmt');
    $skuTrial = url::postToText('newSKUTPrd');
    $skuBillCycle = url::postToText('newSKUBCycle');
    $result = updateLicenseCountFunc($oldskuname, $newskuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle);
    echo $result;
}
