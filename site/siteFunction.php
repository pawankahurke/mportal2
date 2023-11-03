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
require_once '../include/common_functions.php';

global $licensePostapiUrl;

$draw = 1;
$recordList = array();

$limitCount = (url::requestToInt('limitCount') == 0) ? 10 : url::requestToInt('limitCount');
$nextPage = url::postToInt('nextPage');
$notifSearch = url::requestToStringAz09('notifSearch');
$order = url::requestToStringAz09('order');
$sort = url::requestToStringAz09('sort');
$curPage = url::postToInt('nextPage') - 1;
$params = array(
    "limitCount" => $limitCount,
    "nextPage" => $nextPage,
    "notifSearch" => $notifSearch,
    "order" => $order,
    "sort" => $sort
);
$params = json_encode($params);
// $params=json_encode($params);
$sites = safe_json_decode(getSitesList($params), true);


$limitStart = $limitCount * $curPage;
$limitEnd = $limitStart + $limitCount;
$data = $sites['data'];
$totCount = $sites['totCount'];
// echo $totCount."$$$$$====>".$limitCount."$$$$$$===>".$curPage;exit;
if (safe_sizeof($data) == 0) {
    $dataArr['largeDataPaginationHtml'] =  '';
    $dataArr['html'] =   '';
    echo json_encode($dataArr);
} else {
    $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, '');
    $dataArr['html'] =  FORMATSitesData($data);
    echo json_encode($dataArr);
}



function FORMATSitesData($data)
{
    nhRole::dieIfnoRoles(['site']);

    $i = 0;
    foreach ($data as $site) {

        $user = !empty($site['username']) ? $site['username'] : '-';

        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $create = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $site['firstcontact'], "Y/m/d");
        } else {
            $create = date("Y/m/d", $site['firstcontact']);
        }
        $recordList[$i][] = $site['sitename'];
        $recordList[$i][] = $user;
        $recordList[$i][] = $create;
        $recordList[$i][] = $site['customer_name'];
        $recordList[$i][] = $site['skuname'];
        $recordList[$i][] = $site['siteid'];
        $i++;
    }
    return $recordList;
}

function getSitesList($params)
{
    nhRole::dieIfnoRoles(['site']);

    global $dashlicensePostapiUrl;
    // send query to file: Provision/api/apilicense.php (function get_SiteListApi)
    $siteList = CURL::getContentByURL($dashlicensePostapiUrl . "?action=getSitesList&params=" . $params);
    // var_dump($siteList);
    return $siteList;
}
