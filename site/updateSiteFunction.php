<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once $absDocRoot . 'lib/l-db.php';
include_once $absDocRoot . 'lib/l-dbConnect.php';
require_once $absDocRoot . 'lib/l-sql.php';
require_once $absDocRoot . 'lib/l-gsql.php';
require_once $absDocRoot . 'lib/l-rcmd.php';
require_once $absDocRoot . 'lib/l-util.php';
require_once $absDocRoot . 'lib/l-setTimeZone.php';

global $licensePostapiUrl;
$pdo = pdo_connect();
$draw = 1;
$recordList = array();
$sites = safe_json_decode(getSubscriptionList(), true);
$finalarr = array();

foreach ($sites as $key => $item) {
    $finalarr[$item['skuname']][$key] = $item;
}
foreach ($finalarr as $k => $site) {
    $siteArr = array();
    if (safe_count($site) > 1) {
        foreach ($site as $key => $item) {
            array_push($siteArr, $item['sitename']);
            $sitesVal = implode(',', $siteArr);
            $skuName = $item['skuname'];
            $trail = $item['trialperiod'];
            $total = $item['total'];
            $used = $item['used'];
            $licenseid = $item['licenseid'];
            $dist = 1; //$site['distribution'];
        }
    } else {
        foreach ($site as $key => $item) {
            $sitesVal = $item['sitename'];
            $skuName = $item['skuname'];
            $trail = $item['trialperiod'];
            $total = $item['total'];
            $used = $item['used'];
            $dist = 1; //$site['distribution'];
            $licenseid = $item['licenseid'];
        }
    }
    // console.log($siteArr."siteArr");
    $Sql = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."install.skuOfferings where name=?");
    $Sql->execute([$skuName]);
    $Res = $Sql->fetch(PDO::FETCH_ASSOC);
    $license = $Res['customfields'];
    $user = !empty($site['installuser']) ? $site['installuser'] : '-';
    $unused = $total - $used;
    $siteName = $site['sitename'];
    if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
        $create = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $site['firstcontact'], "Y/m/d");
    } else {
        $create = date("Y/m/d", $site['firstcontact']);
    }
    $site['distribution'] = 1;
    $recordList[] = array(
        "DT_RowId" => $licenseid,
        "license" => '<p style="width:10px" id="' . $license . '" class="ellipsis" title="' . $license . '">' . $license . '</p>',
        "expiry" => '<p id="' . $trail . '" class="ellipsis" title="' . $trail . '">' . $trail . '</p>',
        "attached" => '<p id="' . $sitesVal . '" class="ellipsis" title="' . $sitesVal . '">' . $sitesVal . '</p>',
        "total" => '<p id="' . $total . '" class="ellipsis" title="' . $total . '">' . $total . '</p>',
        "unused" => '<p id="' . $unused  . '" class="ellipsis" title="' . $unused  . '">' . $unused  . '</p>',
        "distribution" => '<p id="' . $dist . '" class="ellipsis" title="' . $dist . '">' . $dist . '</p>'
    );
}
$jsonData = array("draw" => $draw, "recordsTotal" => 2, "recordsFiltered" => 2, "data" => $recordList);
echo json_encode($jsonData);
exit;
function getSubscriptionList()
{
    global $licensePostapiUrl;
    $siteList = CURL::getContentByURL($licensePostapiUrl . "?action=getSubscriptionList");
    return $siteList;
}
