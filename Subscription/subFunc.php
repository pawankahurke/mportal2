<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
require_once '../lib/l-db.php';

nhRole::dieIfnoRoles(['licensedetails']); // roles: licensedetails
if (url::postToText('function') === 'getLicenseList') { // roles: licensedetails
    getLicenseList();
} else if (url::postToText('function') === 'updateLicenseDetails') { // roles: licensedetails
    updateLicenseDetails();
}

function getLicenseList()
{
    $pdo = pdo_connect();
    $Sql = $pdo->prepare("select distinct customfields,name from " . $GLOBALS['PREFIX'] . "install.skuOfferings");
    $Sql->execute();
    $Res = $Sql->fetchAll(PDO::FETCH_ASSOC);
    $str1 = "<option value='0' selected>Please select License</option>";

    foreach ($Res as $value) {
        $str .= "<option value='" . $value['name'] . "'>" . $value['customfields'] . "</option>";
    }
    $skuList = $str1 . $str;

    print_r($skuList);
}

function updateLicenseDetails()
{
    $pdo = pdo_connect();
    $oldSkuName = url::postToText('oldSkuName');
    $skuname = url::postToText('skuname');
    $skuDesc = url::postToText('skuDesc');
    $skuCat = url::postToText('skuCat');
    $skuBillType = url::postToText('skuBillType');
    $skuQty = url::postToText('skuQty');
    $skuAmt = url::postToText('skuAmt');
    $skuTrial = url::postToText('skuTrial');
    $skuBillCycle = url::postToText('skuBillCycle');
    $licenseKey  = url::postToText('licenseKey');

    // skuOfferings insert
    $sqlupdate = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "install.skuOfferings set name = ?,description= ?,category= ?,billingtype= ?,
    quantity= ?,amount= ?,trialperiod= ?,billingcycle= ?,customfields =? where name = ?");
    $params = array_merge([$skuname, $skuDesc, $skuCat, $skuBillType, $skuQty, $skuAmt, $skuTrial, $skuBillCycle, $licenseKey, $oldSkuName]);
    $result = $sqlupdate->execute($params);
    print_R($result);
    exit;
}
