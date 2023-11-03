<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
require_once '../include/common_functions.php';


if (!isset($_SESSION['user']['username'])) {
    $return = array('status' => true, 'message' => 'loggedout');
    echo json_encode($return);
    return;
}

$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) {
    exit(json_encode(array('status' => false, 'message' => 'Permission denied')));
}


$db = pdo_connect();
$userId = $_SESSION['user']['userid'];
$limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
$curPage = url::postToInt('nextPage') - 1;

$orderVal = url::postToStringAz09('order');
$sortVal = url::postToStringAz09('sort');

if ($orderVal != '') {
    $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
} else {
    // $orderStr = 'order by machine desc,ndate desc,count desc,nocStatus desc';
    $orderStr = ' order by d.id desc ';
}


$limitStart = $limitCount * $curPage;

$limitEnd = $limitStart + $limitCount;
$notifSearch = url::postToText('notifSearch');
if ($notifSearch != '') {
    $whereSearch = "and (d.name LIKE '%" . $notifSearch . "%'
            OR d.ntype LIKE '%" . $notifSearch . "%'
            OR d.created LIKE '%" . $notifSearch . "%'
            OR d.modified LIKE '%" . $notifSearch . "%'
            OR d.enabled LIKE '%" . $notifSearch . "%'
            OR d.group_include LIKE '%" . $notifSearch . "%'
            )";
} else {
    $whereSearch = '';
}
$username = $_SESSION['user']['username'];
$sitelist = $_SESSION["user"]["site_list"];
$whereor = ' where (';
$tempor = '';
$i = 0;
if (safe_sizeof($sitelist) > 0) {

    $sitelist = array_values($sitelist);

    for ($i = 0; $i < safe_sizeof($sitelist); $i++) {
        if ($i == safe_sizeof($sitelist) - 1) {
            $tempor =  $tempor . " d.group_include like '%$sitelist[$i]%'" . " OR " . " d.group_include in( 'All', '') ";
        } else {
            $tempor =  $tempor . " d.group_include like '%$sitelist[$i]%'" . " OR ";
        }
    }
}
if ($tempor !== '') {
    $whereor = $whereor . $tempor . " )";
} else {
    $whereor = $whereor . " d.group_include in( 'All', '') ";
}

if ($limitStart > 0) {
    $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
} else {
    $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
}
$Sql = "SELECT d.id, d.name, d.ntype, d.created, d.modified, d.enabled, d.group_include FROM  " . $GLOBALS['PREFIX'] . "event.Notifications as d $whereor $whereSearch $orderStr $limitStr";
$data = NanoDB::find_many($Sql);

$Sql = "SELECT d.id, d.name, d.ntype, d.created, d.modified, d.enabled, d.group_include FROM  " . $GLOBALS['PREFIX'] . "event.Notifications as d $whereor $whereSearch $orderStr";
$totCount = safe_count(NanoDB::find_many($Sql));

if (safe_sizeof($data) == 0) {
    $dataArr['largeDataPaginationHtml'] =  '';
    $dataArr['html'] =   '';
    echo json_encode($dataArr);
} else {
    $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
    $dataArr['html'] =    Format_watcherDataMysql($data);
    echo json_encode($dataArr);

    //}
}

function Format_watcherDataMysql($data)
{
    $recordList = [];
    $i = 0;
    foreach ($data as $eachConfigs) {
        $alertid = isset($eachConfigs['id']) ? $eachConfigs['id'] : "-";
        $alertname = isset($eachConfigs['name']) ? $eachConfigs['name'] : "-";
        $site = isset($eachConfigs['group_include']) ? $eachConfigs['group_include'] : "All";
        $alerttype = isset($eachConfigs['ntype']) ? $eachConfigs['ntype'] : "-";
        $watcherId = isset($eachConfigs['watcherId']) ? $eachConfigs['watcherId'] : "-";
        $createdtime = isset($eachConfigs['created']) ? date("m/d/Y H:i:s", $eachConfigs['created']) : "-";
        $modifiedtime = isset($eachConfigs['modified']) && $eachConfigs['modified'] != 0 ? date("m/d/Y H:i:s", $eachConfigs['modified']) : "-";
        $alertstatus = isset($eachConfigs['enabled']) ? $eachConfigs['enabled'] : "-";
        $alertstatusVal = "-";
        if ($alertstatus === "1") {
            $alertstatusVal = "Enabled";
        } else if ($alertstatus === "0") {
            $alertstatusVal = "Disabled";
        }

        $alerttype = getAlertType($alerttype);
        $recordList[$i][] = $alertname;
        $recordList[$i][] = $site;
        $recordList[$i][] = $alerttype;
        $recordList[$i][] = $createdtime;
        $recordList[$i][] = $modifiedtime;
        $recordList[$i][] = $alertstatusVal;
        $recordList[$i][] = $alertid;
        $recordList[$i][] = $watcherId;
        $i++;
    }
    return $recordList;
}

function getAlertType($alerttype)
{
    $alerttxt = '';
    if ($alerttype == 1) {
        $alerttxt = 'Availability';
    } else if ($alerttype == 2) {
        $alerttxt = 'Security';
    } else if ($alerttype == 3) {
        $alerttxt = 'Resource';
    } else if ($alerttype == 4) {
        $alerttxt = 'Maintenance';
    } else {
        $alerttxt = 'Events of Interest';
    }

    return $alerttxt;
}

$auditRes = create_auditLog('View Alert configuration', 'View', 'Success');

// $return = array('data' => $files);
// echo json_encode($return);
