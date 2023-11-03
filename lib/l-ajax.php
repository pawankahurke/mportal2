<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashElastic.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';
include_once '../lib/l-group.php';
include_once '../lib/l-entitlement.php';
include_once '../lib/l-resolution.php';
include_once '../lib/l-resolution_new_EL.php';
include_once '../lib/l-resolutionEL.php';
include_once '../lib/l-admin.php';
include_once '../lib/l-logAudit.php';
include_once '../lib/l-profilewizard.php';
include_once '../lib/l-rightPane.php';
include_once '../lib/l-deploy.php';
include_once '../lib/l-formatGrid.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-export.php';
include_once '../lib/l-mobilityReport.php';
include_once '../lib/l-sqlitedb.php';
include_once '../lib/l-msp.php';
include_once '../lib/l-setTimeZone.php';

function AJAX_GetHomePageData()
{
    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    global $API_enable_RESOL;
    switch ($searchType) {
        case 'Sites':
            $userCount = count(USER_SitesUsers($key, $db, $dataScope, ''));
            $deviceCount = count(DASH_GetMachinesSites($key, $db, $dataScope));
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $userCount = '1';
            $deviceCount = safe_count($machines);

            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $parentSite = DASH_GetMachSite($key, $db, $censusId);
            $userCount = '1';
            $deviceCount = 1;

            break;
        default:
            break;
    }

    $returnData['deviceCount'] = $deviceCount;
    $returnData['userCount'] = $userCount;

    echo json_encode($returnData);
}

function AJAX_GetComplianceItems()
{
    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 1);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetInteger('status', 0);
    $detail = 0;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            $complianceItems = UTIL_FormatCompListData($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            $complianceItems = UTIL_FormatCompListData($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status, $detail);
            $complianceItems = UTIL_FormatCompListData($complianceData, false);
            break;
        default:
            break;
    }
    echo $complianceItems;
}

function AJAX_GetComplianceFilterItems()
{
    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetString('filteritem', 1);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetString('filterstatus', 0);
    $detail = UTIL_GetInteger('detail', 0);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceFilterDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            $complianceItems = UTIL_FormatCompListData($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceFilterDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            $complianceItems = UTIL_FormatCompListData($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceFilterDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status, $detail);
            $complianceItems = UTIL_FormatCompListData($complianceData, false);
            break;
        default:
            break;
    }
    echo $complianceItems;
}

function AJAX_GetMachComplianceItems()
{
    $db = pdo_connect();
    $key = '';

    $censusId = UTIL_GetInteger('censusId', 0);
    $itemtype = UTIL_GetInteger('itemtype', 1);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetInteger('status', -1);
    $detail = 0;

    $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status, $detail);
    $complianceItems = UTIL_FormatCompListData($complianceData, true);

    echo $complianceItems;
}

function AJAX_GetComplianceDetails()
{
    $dbusage = $_SESSION["user"]["usage"];
    $db = pdo_connect();
    $key = '';
    global $API_enable_comp;

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 0);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetInteger('status', 0);
    $draw = UTIL_GetInteger('draw', 1);
    $detail = 1;
    $dbusage = $_SESSION["user"]["usage"];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            if ($dbusage == 1 && $API_enable_comp == 1) {
                $complianceDetails = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            } else {
                $complianceData = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
                $complianceDetails = UTIL_FormatCompDetailData($complianceData, $draw);
            }
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            if ($dbusage == 1 && $API_enable_comp == 1) {
                $complianceDetails = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            } else {
                $complianceData = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
                $complianceDetails = UTIL_FormatCompDetailData($complianceData, $draw);
            }
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            if ($dbusage == 1 && $API_enable_comp == 1) {
                $complianceDetails = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
            } else {
                $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
                $complianceDetails = UTIL_FormatCompDetailData($complianceData, $draw);
            }
            break;
        default:
            break;
    }

    echo json_encode($complianceDetails);
}

function AJAX_GetComplianceFilterDetails()
{
    $db = pdo_connect();
    $key = '';
    global $API_enable_comp;

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('filteritem', 0);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetString('filterstatus', 0);
    $draw = UTIL_GetInteger('draw', 1);
    $detail = 1;
    $dbusage = $_SESSION["user"]["usage"];
    $FilterDetail = 'FilterDetail';
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            if ($dbusage == 1 && $API_enable_comp == 1) {
                $complianceDetails = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            } else {
                $complianceData = DASH_GetComplainceFilterDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
                $complianceDetails = UTIL_FormatCompDetailData($complianceData, $draw);
            }
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            if ($dbusage == 1 && $API_enable_comp == 1) {
                $complianceDetails = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            } else {
                $complianceData = DASH_GetComplainceFilterDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
                $complianceDetails = UTIL_FormatCompDetailData($complianceData, $draw);
            }
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            if ($dbusage == 1 && $API_enable_comp == 1) {
                $complianceDetails = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
            } else {
                $complianceData = DASH_GetComplainceFilterDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
                $complianceDetails = UTIL_FormatCompDetailData($complianceData, $draw);
            }
            break;
        default:
            break;
    }

    echo json_encode($complianceDetails);
}

function AJAX_GetAllComplianceDetail()
{
    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 1);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetInteger('status', 0);
    $detail = 1;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $machineLastRprt = DASH_GetMachLastRprtSites($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            $machWiseCompCount = UTIL_CountAllMachComplainceStat($complianceData, $machineLastRprt);
            $complianceDetails = UTIL_FormatAllCompDetailData($machWiseCompCount);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machineLastRprt = DASH_GetGroupsMachLastRprt($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            $machWiseCompCount = UTIL_CountAllMachComplainceStat($complianceData, $machineLastRprt);
            $complianceDetails = UTIL_FormatAllCompDetailData($machWiseCompCount);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $machineLastRprt = DASH_GetMachLastRprt($key, $db, $censusId);
            $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
            $machWiseCompCount = UTIL_CountAllMachComplainceStat($complianceData, $machineLastRprt);
            $complianceDetails = UTIL_FormatAllCompDetailData($machWiseCompCount);
            break;
        default:
            break;
    }

    echo json_encode($complianceDetails);
}

function AJAX_GetSecurityGridData()
{

    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 9);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetInteger('status', 3);
    $detail = 1;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            $serialNoMap = DASH_HostNSerialNoMapSite($key, $db, $dataScope);
            $securityData = UTIL_FormatSecurityData($db, $complianceData, $serialNoMap);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            $serialNoMap = DASH_HostNSerialNoMapGrp($key, $db, $machines);
            $securityData = UTIL_FormatSecurityData($db, $complianceData, $serialNoMap);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
            $serialNoMap = DASH_HostNSerialNoMapMach($key, $db, $censusId);
            $securityData = UTIL_FormatSecurityData($db, $complianceData, $serialNoMap);
            break;
        default:
            break;
    }

    echo json_encode($securityData);
}

function AJAX_GetHealthGridData()
{

    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 9);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetInteger('status', 3);
    $detail = 1;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            $serialNoMap = DASH_HostNSerialNoMapSite($key, $db, $dataScope);
            $healthData = UTIL_FormatHealthData($db, $complianceData, $serialNoMap);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            $serialNoMap = DASH_HostNSerialNoMapGrp($key, $db, $machines);
            $healthData = UTIL_FormatHealthData($db, $complianceData, $serialNoMap);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
            $serialNoMap = DASH_HostNSerialNoMapMach($key, $db, $censusId);
            $healthData = UTIL_FormatHealthData($db, $complianceData, $serialNoMap);
            break;
        default:
            break;
    }

    echo json_encode($healthData);
}

function AJAX_GetSummaryRprtData()
{

    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $reportDate = UTIL_GetInteger('reportDate', 8);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $reportdurtn = UTIL_GetReportDuration($reportDate);
    $names = array('Hard Disk Failure', 'Processor Failure', 'CPU Failure', 'RAM Failure', 'Battery Failure', 'Firewall Disabled', 'Multiple Antivirus Installed', 'No Anti-Spyware Installed', 'Anti Virus not up-to-date', 'Device Ram less than 10%', 'Device Battery less than 10%', 'Device Disk space less than 20%');
    $itemIds = UTIL_GetItemId($db, 'EventItems', $names);
    switch ($searchType) {
        case 'Sites':
            $summaryData = DASH_GetSummaryRprtSite($key, $db, $dataScope, $itemIds, $reportdurtn);
            $summaryDetails = UTIL_FormatSummaryData($summaryData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $summaryData = DASH_GetGetSummaryRprtGrp($key, $db, $machines, $itemIds, $reportdurtn);
            $summaryDetails = UTIL_FormatSummaryData($summaryData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $summaryData = DASH_GetSummaryRprtMach($key, $db, $censusId, $itemIds, $reportdurtn);
            $summaryDetails = UTIL_FormatSummaryData($summaryData);
            break;
        default:
            break;
    }
    echo json_encode($summaryDetails);
}

function AJAX_GetSoftwareRprtData()
{

    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $reportDate = UTIL_GetInteger($reportDate, 8);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $censusIds = DASH_GetMachinesSites($key, $db, $dataScope);
            $softwareDetails = RPRT_SoftwareData($key, $db, $censusIds);
            break;
        case 'Groups':
            $censusIds = DASH_GetGroupsMachines($key, $db, $dataScope);
            $softwareDetails = RPRT_SoftwareData($key, $db, $censusIds);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $softwareDetails = RPRT_SoftwareData($key, $db, $censusId);
            break;
        default:
            break;
    }

    $softdata = RPRT_SoftWare_GridData($db, $softwareDetails);
    echo json_encode($softdata);
}

function AJAX_Get_RightPane()
{
    $pageValue = url::postToAny('page');
    $userName = $_SESSION['user']['username'];
    $customerType = $_SESSION['user']['customerType'];
    $pdo = pdo_connect();
    $json = [];
    $gatewayStatus = null;
    $user = $userName;
    $json['userType'] = $customerType;

    $views = array('Sites', 'Groups');

    $_SESSION['user']['group_list'] = null;
    
    $searchType = '';
    if(isset($_SESSION['searchType'])){
        $searchType = $_SESSION['searchType'];
    }
    
    $selectedSiteName = '';
    if(isset($_SESSION['searchValue'])){
        $selectedSiteName = $_SESSION['searchValue'];
    }

    if ($searchType == 'ServiceTag') {
        $selectedSiteName = $_SESSION['rparentName'];
    }
    foreach ($views as $viewvalue) {
        if ($viewvalue == 'Sites') {
            $sitesql = $pdo->prepare("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? order by lower(customer)");
            $sitesql->execute([$user]);
            $siteres = $sitesql->fetchAll(PDO::FETCH_ASSOC);
            if ($pageValue == 'Home Page') {
                array_unshift($siteres, array("name" => "All"));
            }
            foreach ($siteres as $key => $val) {
                $site = utf8_encode($val['name']);
                $site1 = $site . ' ';
                $json['views'][$viewvalue][$site1]['id'] = utf8_encode($site) . "_" . $viewvalue . '@@@' . $gatewayStatus;
            }
        } else if ($viewvalue == 'Groups') {
            $json['views'][$viewvalue] = [];
            $json['views'][$viewvalue] = [];

            $a = 0;
            $gatewayStatus = 0;
            $stylesel = $pdo->query('select * from group_styles');
            $styleres = $stylesel->fetchAll();
            if ($pageValue == 'Home Page') {
                array_unshift($styleres, array("style_name" => "All", "style_number" => 1, "id" => 0));
            }
            foreach ($styleres as $style) {
                $stylename = $style['style_name'];
                $temp = [];
                $stylename = $stylename . ' ';
                $json['views'][$viewvalue][$stylename]['id'] = utf8_encode($style['style_number']) . "_" . $viewvalue;
            }
        }
    }
    echo json_encode($json);
}

function AJAX_Update_Session()
{
    $updateHome = url::postToStringAz09('updateHome');

    if ($updateHome) {
        $_SESSION['user']['loggedUType'] = '';
    }

    $searchType = UTIL_GetString('searchType', '');
    switch ($searchType) {
        case 'Sites':
            Pane_UpdateSession();
            break;
        case 'Groups':
            Pane_UpdateSession();
            break;
        case 'ServiceTag':
            Pane_UpdateSession();
            break;
        default:
            break;
    }
}

function AJAX_GetComplianceTrend()
{

    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 5);
    $itemId = UTIL_GetString('itemid', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceTrendSite($key, $db, $dataScope, $itemtype, true, $itemId);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceTrendGrp($key, $db, $machines, $itemtype, true, $itemId);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceTrendMach($key, $db, $censusId, $itemtype, true, $itemId);
            break;
        default:
            break;
    }
    $dateGraphLabel = UTIL_GetdateLabelForMonth();
    $returnData['complianceTrend'] = $complianceData['graphData'];
    $returnData['graphDataLabel'] = $dateGraphLabel;

    echo json_encode($returnData);
}

function AJAX_GetComplianceCalendarMonthGraph()
{

    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceCalendarMonthGraphSite($key, $db, $dataScope);
            $makeCompMonthGraph = UTIL_FormatComplianceCalendarMonthGraph($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceCalendarMonthGraphGroup($key, $db, $machines);
            $makeCompMonthGraph = UTIL_FormatComplianceCalendarMonthGraph($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceCalendarMonthGraphMachine($key, $db, $censusId);
            $makeCompMonthGraph = UTIL_FormatComplianceCalendarMonthGraph($complianceData);
            break;
        default:
            break;
    }

    echo json_encode($makeCompMonthGraph);
}

function AJAX_GetComplianceCalendarWeekGraph()
{

    $db = pdo_connect();
    $key = '';
    $now = time();
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $timestamp = UTIL_GetInteger('tstamp', $now);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceCalendarWeekGraphSite($key, $db, $dataScope, $timestamp);
            $makeCompWeekGraph = UTIL_FormatComplianceCalendarWeekGraph($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceCalendarWeekGraphGroup($key, $db, $machines, $timestamp);
            $makeCompWeekGraph = UTIL_FormatComplianceCalendarWeekGraph($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceCalendarWeekGraphMachine($key, $db, $censusId, $timestamp);
            $makeCompWeekGraph = UTIL_FormatComplianceCalendarWeekGraph($complianceData);
            break;
        default:
            break;
    }

    echo json_encode($makeCompWeekGraph);
}

function AJAX_GetComplianceCalendarDailyGraph()
{

    $db = pdo_connect();
    $key = '';
    $now = time();
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $timestamp = UTIL_GetInteger('tstamp', $now);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceCalendarDailyGraphSite($key, $db, $dataScope, $timestamp);
            $makeCompDailyGraph = UTIL_FormatComplianceCalendarDailyGraph($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceCalendarDailyGraphGroup($key, $db, $machines, $timestamp);
            $makeCompDailyGraph = UTIL_FormatComplianceCalendarDailyGraph($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceCalendarDailyGraphMachine($key, $db, $censusId, $timestamp);
            $makeCompDailyGraph = UTIL_FormatComplianceCalendarDailyGraph($complianceData);
            break;
        default:
            break;
    }

    echo json_encode($makeCompDailyGraph);
}

function AJAX_GetComplianceLeftItems()
{
    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $detail = 0;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceLeftDetailsSite($key, $db, $dataScope, $detail);
            $complianceItems = UTIL_FormatCompLeftListData($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceLeftDetailsGrp($key, $db, $machines, $detail);
            $complianceItems = UTIL_FormatCompLeftListData($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceLeftDetailsMach($key, $db, $censusId, $detail);
            $complianceItems = UTIL_FormatCompLeftListData($complianceData, false);
            break;
        default:
            break;
    }
    echo json_encode($complianceItems);
}

function AJAX_GetComplianceRightItems()
{
    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $detail = 1;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceRightDetailsSite($key, $db, $dataScope, $detail);
            $complianceItems = UTIL_FormatCompRightListData($complianceData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceRightDetailsSite($key, $db, $machines, $detail);
            $complianceItems = UTIL_FormatCompRightListData($complianceData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceRightDetailsSite($key, $db, $censusId, $detail);
            $complianceItems = UTIL_FormatCompRightListData($complianceData, false);
            break;
        default:
            break;
    }
    echo json_encode($complianceItems);
}

function AJAX_ResetComplianceItems()
{

    $db = pdo_connect();
    $key = '';

    $censusId = UTIL_GetString('censusId', '');
    $itemType = UTIL_GetString('itemType', '');
    $itemId = UTIL_GetString('itemId', '');

    $res = DASH_ResetComplianceItem($key, $db, $censusId, $itemType, $itemId);

    if ($res == 1) {
        echo 'done';
    } else {
        echo 'fail';
    }
}

function AJAX_GetAlertNWarn()
{

    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 1);
    $day = UTIL_GetInteger('day', 14);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetAlertNWarnCompSite($key, $db, $itemtype, $day, $dataScope);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetAlertNWarnCompGrp($key, $db, $itemtype, $day, $machines);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetAlertNWarnCompMach($key, $db, $itemtype, $day, $censusId);
            break;
        default:
            break;
    }
    $returnData['activeAlert'] = $complianceData['warningcount'];
    $returnData['activeWarning'] = $complianceData['alertcount'];

    echo json_encode($returnData);
}

function AJAX_GetDeviceAssetInfo()
{
    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $gridType = UTIL_GetString('gridType', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $request_data = [];
    if ($gridType == 'software') {
        $softwareNames = UTIL_GetString('softwareNames', '');
        if ($softwareNames != '') {
            $request_data['softwareNames'] = explode(',', $softwareNames);
        }
    }
    $draw = url::requestToText('draw');
    $start = UTIL_GetInteger('start', 0);
    $length = UTIL_GetInteger('length', 0);

    switch ($searchType) {
        case 'Sites':
            $jsonRecordsArray = AJAX_GetSiteAssetInfo($key, $db, $gridType, $dataScope, $request_data);
            break;
        case 'Groups':
            $jsonRecordsArray = AJAX_GetGroupAssetInfo($key, $db, $gridType, $dataScope, $request_data);
            break;
        case 'ServiceTag':
            $jsonRecordsArray = AJAX_GetMachineAssetInfo($key, $db, $gridType, $request_data);
            break;
        default:
            break;
    }

    if ($length != 0) {
        $recordList = array_slice($jsonRecordsArray, $start++, $length++, true);
    } else if (safe_count($jsonRecordsArray) > 0) {
        $recordList = array_slice($jsonRecordsArray, 0, safe_count($jsonRecordsArray), true);
    } else {
        $recordList = array();
    }

    echo json_encode($recordList);
}

function AJAX_GetDeviceAssetInfo_RESTAPI($searchType, $searchValue, $gridType)
{
    $db = pdo_connect();
    $key = '';
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $jsonRecordsArray = AJAX_GetSiteAssetInfo($key, $db, $gridType, $dataScope);
            break;
        case 'Groups':
            $jsonRecordsArray = AJAX_GetGroupAssetInfo($key, $db, $gridType, $dataScope);
            break;
        case 'ServiceTag':
            $jsonRecordsArray = AJAX_GetMachineAssetInfo($key, $db, $gridType);
            break;
        default:
            break;
    }

    echo json_encode($jsonRecordsArray);
}

function AJAX_GetSiteAssetInfo($key, $db, $gridType, $dataScope, $request_data)
{
    $types = explode('|', $gridType);
    $gridType = $types[0];
    switch ($gridType) {
        case 'basic':
            $restrict = array('User Name', 'Chassis Type', 'Chassis Manufacturer', 'Operating System');
            $assetData = DASH_GetBasicInfoSite($key, $db, $dataScope, $restrict);
            $jsonRecordsArray = UTIL_CreateAssetInfoJson($assetData);
            break;
        case 'software':
            $jsonRecordsArray = [];
            $assetData = [];
            $graphData = [];

            if (safe_count($request_data['softwareNames']) > 0) {
                $assetData = DASH_GetSoftInfoSite($key, $db, $dataScope, $request_data['softwareNames']);
                if (!$request_data['isexport']) {
                    $jsonRecordsArray['griddata'] = json_encode($assetData);
                    $graphData = DASH_GetSoftInfoSiteGraphData($key, $db, $dataScope, $request_data['softwareNames']);
                    $jsonRecordsArray['graphdata'] = json_encode($graphData['data'], JSON_NUMERIC_CHECK);
                    $jsonRecordsArray['drilldowndata'] = json_encode($graphData['drilldowndata']);
                } else {
                    $jsonRecordsArray = $assetData;
                }
            } else {
                $jsonRecordsArray['griddata'] = json_encode($assetData);
                $jsonRecordsArray['graphdata'] = json_encode($graphData['data'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['drilldowndata'] = json_encode($graphData['drilldowndata'], JSON_NUMERIC_CHECK);
            }
            break;
        case 'patch':
            $assetData = DASH_GetPatchInfoSite($key, $db, $dataScope);
            $jsonRecordsArray = UTIL_CreatePatchInfoJson($assetData);
            break;
        case 'resource':
            $assetData = DASH_GetResourceInfoSite($key, $db, $dataScope);
            $jsonRecordsArray = UTIL_CreateResourceInfoJson($assetData);
            break;
        case 'network':
            $assetData = DASH_GetNetworkInfoSite($key, $db, $dataScope);
            $jsonRecordsArray = UTIL_CreateNetworkInfoJson($assetData);
            break;
        case 'system':
            $jsonRecordsArray = [];
            $assetData = DASH_GetSystemInfoSite($key, $db, $dataScope);
            if ($request_data['isexport']) {
                $subtype = $types[1];
                switch ($subtype) {
                    case 'operatingSystem':
                        $jsonRecordsArray = $assetData['griddata']['operatingSystemGrid'];
                        break;
                    case 'chassisManufacturer':
                        $jsonRecordsArray = $assetData['griddata']['chassisManufacturerGrid'];
                        break;
                    case 'chassisType':
                        $jsonRecordsArray = $assetData['griddata']['chassisTypeGrid'];
                        break;
                    case 'processor':
                        $jsonRecordsArray = $assetData['griddata']['processorGrid'];
                        break;
                }
            } else {
                $jsonRecordsArray['graphdata']['chassisManufacturer'] = json_encode($assetData['graphdata']['Chassis Manufacturer'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['chassisManufacturer'] = json_encode($assetData['griddata']['chassisManufacturerGrid']);
                $jsonRecordsArray['graphdata']['operatingSystem'] = json_encode($assetData['graphdata']['Operating System'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['operatingSystem'] = json_encode($assetData['griddata']['operatingSystemGrid']);
                $jsonRecordsArray['graphdata']['chassisType'] = json_encode($assetData['graphdata']['Chassis Type'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['chassisType'] = json_encode($assetData['griddata']['chassisTypeGrid']);
                $jsonRecordsArray['graphdata']['processorVersion'] = json_encode($assetData['graphdata']['Processor Version'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['processorVersion'] = json_encode($assetData['griddata']['processorGrid']);
            }

            break;
        default:
            break;
    }
    return $jsonRecordsArray;
}

function AJAX_GetGroupAssetInfo($key, $db, $gridType, $dataScope, $request_data)
{
    $types = explode('|', $gridType);
    $gridType = $types[0];
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    switch ($gridType) {
        case 'basic':
            $restrict = array('User Name', 'Chassis Type', 'Chassis Manufacturer', 'Operating System');
            $assetData = DASH_GetBasicInfoGroup($key, $db, $machines, $restrict);
            $jsonRecordsArray = UTIL_CreateAssetInfoJson($assetData);
            break;
        case 'software':
            $jsonRecordsArray = [];
            $assetData = [];
            $graphData = [];

            if (safe_count($request_data['softwareNames']) > 0) {
                $assetData = DASH_GetSoftInfoGroup($key, $db, $machines, $request_data['softwareNames']);
                if (!$request_data['isexport']) {
                    $jsonRecordsArray['griddata'] = json_encode($assetData);
                    $graphData = DASH_GetSoftInfoGroupGraphData($key, $db, $machines, $request_data['softwareNames']);
                    $jsonRecordsArray['graphdata'] = json_encode($graphData['data'], JSON_NUMERIC_CHECK);
                    $jsonRecordsArray['drilldowndata'] = json_encode($graphData['drilldowndata'], JSON_NUMERIC_CHECK);
                } else {
                    $jsonRecordsArray = $assetData;
                }
            } else {
                $jsonRecordsArray['griddata'] = json_encode($assetData);
                $jsonRecordsArray['graphdata'] = json_encode($graphData['data'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['drilldowndata'] = json_encode($graphData['drilldowndata'], JSON_NUMERIC_CHECK);
            }
            break;
        case 'patch':
            $restrict = array('User Name', 'Description Name', 'Installed On', 'KB ID');
            $assetData = DASH_GetPatchInfoGroup($key, $db, $machines);
            $jsonRecordsArray = UTIL_CreatePatchInfoJson($assetData);
            break;
        case 'resource':
            $assetData = DASH_GetResourceInfoGroup($key, $db, $machines);
            $jsonRecordsArray = UTIL_CreateResourceInfoJson($assetData);
            break;
        case 'network':
            $restrict = array('User Name', 'Domain', 'IP address', 'MAC address');
            $assetData = DASH_GetNetworkInfoGroup($key, $db, $machines);
            $jsonRecordsArray = UTIL_CreateNetworkInfoJson($assetData);
            break;
        case 'system':
            $jsonRecordsArray = [];
            $assetData = DASH_GetSystemInfoGroup($key, $db, $machines);
            if ($request_data['isexport']) {
                $subtype = $types[1];
                switch ($subtype) {
                    case 'operatingSystem':
                        $jsonRecordsArray = $assetData['griddata']['operatingSystemGrid'];
                        break;
                    case 'chassisManufacturer':
                        $jsonRecordsArray = $assetData['griddata']['chassisManufacturerGrid'];
                        break;
                    case 'chassisType':
                        $jsonRecordsArray = $assetData['griddata']['chassisTypeGrid'];
                        break;
                    case 'processor':
                        $jsonRecordsArray = $assetData['griddata']['processorGrid'];
                        break;
                }
            } else {
                $jsonRecordsArray['graphdata']['chassisManufacturer'] = json_encode($assetData['graphdata']['Chassis Manufacturer'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['chassisManufacturer'] = json_encode($assetData['griddata']['chassisManufacturerGrid']);
                $jsonRecordsArray['graphdata']['operatingSystem'] = json_encode($assetData['graphdata']['Operating System'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['operatingSystem'] = json_encode($assetData['griddata']['operatingSystemGrid']);
                $jsonRecordsArray['graphdata']['chassisType'] = json_encode($assetData['graphdata']['Chassis Type'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['chassisType'] = json_encode($assetData['griddata']['chassisTypeGrid']);
                $jsonRecordsArray['graphdata']['processorVersion'] = json_encode($assetData['graphdata']['Processor Version'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['processorVersion'] = json_encode($assetData['griddata']['processorGrid']);
            }
            break;
        default:
            break;
    }
    return $jsonRecordsArray;
}

function AJAX_GetMachineAssetInfo($key, $db, $gridType, $request_data)
{
    $types = explode('|', $gridType);
    $gridType = $types[0];
    $censusId = $_SESSION['rcensusId'];
    switch ($gridType) {
        case 'basic':
            $restrict = array('User Name', 'Chassis Type', 'Chassis Manufacturer', 'Operating System');
            $assetData = DASH_GetBasicInfoMach($key, $db, $censusId, $restrict);
            $jsonRecordsArray = UTIL_CreateAssetInfoJson($assetData);
            break;
        case 'software':
            $jsonRecordsArray = [];
            $assetData = [];
            $graphData = [];

            if (safe_count($request_data['softwareNames']) > 0) {
                $assetData = DASH_GetSoftInfoMach($key, $db, $censusId, $request_data['softwareNames']);
                if (!$request_data['isexport']) {
                    $jsonRecordsArray['griddata'] = json_encode($assetData);
                    $graphData = DASH_GetSoftInfoMachGraphData($key, $db, $censusId, $request_data['softwareNames']);
                    $jsonRecordsArray['graphdata'] = json_encode($graphData['data'], JSON_NUMERIC_CHECK);
                    $jsonRecordsArray['drilldowndata'] = json_encode($graphData['drilldowndata'], JSON_NUMERIC_CHECK);
                } else {
                    $jsonRecordsArray = $assetData;
                }
            } else {
                $jsonRecordsArray['griddata'] = json_encode($assetData);
                $jsonRecordsArray['graphdata'] = json_encode($graphData['data'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['drilldowndata'] = json_encode($graphData['drilldowndata'], JSON_NUMERIC_CHECK);
            }
            break;
        case 'patch':
            $assetData = DASH_GetPatchInfoMach($key, $db, $censusId);
            $jsonRecordsArray = UTIL_CreatePatchInfoJson($assetData);
            break;
        case 'resource':
            $assetData = DASH_GetResourceInfoMach($key, $db, $censusId);
            $jsonRecordsArray = UTIL_CreateResourceInfoJson($assetData);
            break;
        case 'network':
            $restrict = array('User Name', 'Domain', 'IP address', 'MAC address');
            $assetData = DASH_GetNetworkInfoMach($key, $db, $censusId);
            $jsonRecordsArray = UTIL_CreateNetworkInfoJson($assetData);
            break;
        case 'system':
            $jsonRecordsArray = [];
            $assetData = DASH_GetSystemInfoMach($key, $db, $censusId);
            if ($request_data['isexport']) {
                $subtype = $types[1];
                switch ($subtype) {
                    case 'operatingSystem':
                        $jsonRecordsArray = $assetData['griddata']['operatingSystemGrid'];
                        break;
                    case 'chassisManufacturer':
                        $jsonRecordsArray = $assetData['griddata']['chassisManufacturerGrid'];
                        break;
                    case 'chassisType':
                        $jsonRecordsArray = $assetData['griddata']['chassisTypeGrid'];
                        break;
                    case 'processor':
                        $jsonRecordsArray = $assetData['griddata']['processorGrid'];
                        break;
                }
            } else {
                $jsonRecordsArray['graphdata']['chassisManufacturer'] = json_encode($assetData['graphdata']['Chassis Manufacturer'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['chassisManufacturer'] = json_encode($assetData['griddata']['chassisManufacturerGrid']);
                $jsonRecordsArray['graphdata']['operatingSystem'] = json_encode($assetData['graphdata']['Operating System'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['operatingSystem'] = json_encode($assetData['griddata']['operatingSystemGrid']);
                $jsonRecordsArray['graphdata']['chassisType'] = json_encode($assetData['graphdata']['Chassis Type'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['chassisType'] = json_encode($assetData['griddata']['chassisTypeGrid']);
                $jsonRecordsArray['graphdata']['processorVersion'] = json_encode($assetData['graphdata']['Processor Version'], JSON_NUMERIC_CHECK);
                $jsonRecordsArray['griddata']['processorVersion'] = json_encode($assetData['griddata']['processorGrid']);
            }
            break;
        default:
            break;
    }

    return $jsonRecordsArray;
}

function AJAX_ExportDeviceAssetInfo()
{

    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $gridType = UTIL_GetString('gridType', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $headerArray = array(
        "basic" => array("A" => "Machine", "B" => "User Name", "C" => "Chassis Type", "D" => "Manufacturer", "E" => "Operating System"),
        "software" => array("A" => "Machine", "B" => "User Name", "C" => "Software Name", "D" => "Installed On", "E" => "Version"),
        "patch" => array("A" => "Machine", "B" => "User Name", "C" => "Patch Name", "D" => "Installed On", "E" => "KB Id"),
        "resource" => array("A" => "Machine", "B" => "Drive", "C" => "Total Size(GB)", "D" => "Used Space(GB)", "E" => "Free Space(GB)"),
        "network" => array("A" => "Machine", "B" => "User Name", "C" => "Domain", "D" => "IP Address", "E" => "Mac Address"),
        "system|operatingSystem" => array("A" => "Machine", "B" => "User Name", "C" => "Operating System", "D" => "OS Version Number", "E" => "Service Pack"),
        "system|chassisManufacturer" => array("A" => 'Machine', "B" => 'User Name', "C" => 'Chassis Manufacturer', "D" => 'Chassis Serial Number', "E" => 'System Product'),
        "system|processor" => array("A" => 'Machine', "B" => 'User Name', "C" => 'Processor Manufacturer', "D" => 'Processor Version', "E" => 'Processor Type'),
        "system|chassisType" => array("A" => 'Machine', "B" => 'User Name', "C" => 'Chassis Type', "D" => 'Chassis Serial Number', "E" => 'System Product'),
    );
    $request['isexport'] = 1;
    if ($gridType == 'software') {
        $softwareNames = UTIL_GetString('softwareNames', '');
        $request['softwareNames'] = explode(',', $softwareNames);
    }
    switch ($searchType) {
        case 'Sites':
            $assetData = AJAX_GetSiteAssetInfo($key, $db, $gridType, $dataScope, $request);
            break;
        case 'Groups':
            $assetData = AJAX_GetGroupAssetInfo($key, $db, $gridType, $dataScope, $request);
            break;
        case 'ServiceTag':
            $assetData = AJAX_GetMachineAssetInfo($key, $db, $gridType, $request);
            break;
        default:
            break;
    }
    $objPHPExcel = AJAX_CreateAssetExcelObject($headerArray[$gridType], $gridType);
    $result = AJAX_CreatDownloadAssetExcel($gridType, $assetData, $objPHPExcel);
}

function AJAX_CreatDownloadAssetExcel($gridType, $assetData, $objPHPExcel)
{
    $fp = fopen('php://output', 'a');
    if (safe_count($assetData) > 0) {
        AJAX_GetBasicInfoExcel($assetData, $gridType, $fp);
    } else {
        $tempArray = array('No Data Available');
        fputcsv($fp, $tempArray);
    }
}

function AJAX_CreateAssetExcelObject($headerArray, $gridType)
{
    $header = array();
    try {
        foreach ($headerArray as $key => $value) {
            array_push($header, $value);
        }
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename=' . $gridType . '.csv;');
        header('Content-Transfer-Encoding: binary');
        $fpo = fopen('php://output', 'w');
        $cvsHeadings = $header;
        fputcsv($fpo, $cvsHeadings);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}

function AJAX_GetBasicInfoExcel($assetData, $gridType, $fp)
{
    try {
        $tempArray = array();
        if ($gridType == 'software') {
            $objPHPExcel = AJAX_GetSoftwareInfoExcel($assetData, $gridType, $fp);
        } else if ($gridType == 'patch') {
            $objPHPExcel = AJAX_GetPatchInfoExcel($assetData, $gridType, $fp);
        } else {
            foreach ($assetData as $key => $value) {

                $values = strip_tags($value[0]);
                $value1 = strip_tags($value[1]);
                $value2 = strip_tags($value[2]);
                $value3 = strip_tags($value[3]);
                $value4 = strip_tags($value[4]);
                $tempArray = array($values, $value1, $value2, $value3, $value4);
                fputcsv($fp, $tempArray);
            }
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function AJAX_GetSoftwareInfoExcel($assetData, $gridType, $fp)
{
    try {
        $tempArray = array();
        foreach ($assetData as $key => $value) {
            $values = strip_tags($value[0]);
            $value1 = strip_tags($value[1]);
            $value2 = strip_tags($value[2]);
            $date = date(strtotime('m/d/Y', strip_tags($value[3])));
            $value4 = strip_tags($value[4]);
            $tempArray = array($values, $value1, $value2, $date, $value4);
            fputcsv($fp, $tempArray);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function AJAX_GetPatchInfoExcel($assetData, $gridType, $objPHPExcel)
{
    try {
        $tempArray = array();
        foreach ($assetData as $key => $value) {
            $values = strip_tags($value[0]);
            $value1 = strip_tags($value[1]);
            $value2 = strip_tags($value[2]);
            $value3 = $value[3] == 'NA' ? 'NA' : strip_tags($value[3]);
            $value4 = strip_tags($value[4]);
            $tempArray = array($values, $value1, $value2, $value3, $value4);
            fputcsv($fp, $tempArray);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function sessionTimeOut()
{

    $_SESSION["user"]["username"] = "";
    unset($_SESSION["user"]["username"]);
    setcookie('PHPSESSID', null, -1, '/');
    setcookie('usertoken', null, -1, '/');
    setcookie('sso', null, -1, '/');
    session_destroy();
}

function AJAX_GetComplianceHomeItems()
{

    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetInteger('itemtype', 1);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceItems = DASH_GetComplianceHomeItems($key, $db, $itemtype);
            foreach ($complianceItems as $key => $value) {
                $returnData['compDeviation'][] = DASH_GetComplainceTrendSite($key, $db, $dataScope, $itemtype, false, $value);
                $returnData['itemNames'][] = $key;
                $returnData['itemId'][] = $value;
            }
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceItems = DASH_GetComplianceHomeItems($key, $db, $itemtype);
            foreach ($complianceItems as $key => $value) {
                $returnData['compDeviation'][] = DASH_GetComplainceTrendGrp($key, $db, $machines, $itemtype, false, $value);
                $returnData['itemNames'][] = $key;
                $returnData['itemId'][] = $value;
            }
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceItems = DASH_GetComplianceHomeItems($key, $db, $itemtype);
            foreach ($complianceItems as $key => $value) {
                $returnData['compDeviation'][] = DASH_GetComplainceTrendMach($key, $db, $censusId, $itemtype, false, $value);
                $returnData['itemNames'][] = $key;
                $returnData['itemId'][] = $value;
            }
            break;
        default:
            break;
    }
    echo json_encode($returnData);
}

function AJAX_GetNotifyGridData()
{
    $db = pdo_connect();
    $search = strip_tags(url::requestToAny('search')['value']);
    $authuser = $_SESSION['user']['username'];
    $append_search = "";

    $res = ADMN_GetNotifyGridData('1', $db, $search, $authuser, '');
    FORMAT_ADMN_NotifyGridData($res);
}

function AJAX_GetNotifAddFields()
{
    $type = url::requestToText('type');
    $id = url::requestToText('selected');

    $userName = $_SESSION['user']['username'];
    $channelId = $_SESSION['user']['cId'];
    $grpCategory = 'Wiz_SCOP_MC';
    if ($type == "add") {
        $recordListevent = ADMN_GetSavedSearchList($type, $id);
        $incexcGroup = ADMN_GetIncExcGroups($userName, $channelId, $grpCategory, $id, $type);
        $recordList = array("eventfilterlist" => $recordListevent, "incgroup" => $incexcGroup, "excgroup" => $incexcGroup);
    } else if ($type == "edit" || $type == "copy") {
        $recordListevent = ADMN_GetSavedSearchList($type, $id);
        $incexcGroup = ADMN_GetIncExcGroups($userName, $channelId, $grpCategory, $id, $type);
        $inexmachine = explode('@@@@', $incexcGroup);
        $recordList = array("eventfilterlist" => $recordListevent, "incgroup" => $inexmachine[0], "excgroup" => $inexmachine[1]);
    }

    echo json_encode($recordList);
}

function AJAX_SubmitNotifyFilter()
{
    $pdo = pdo_connect();
    $key = '1';
    $validate = url::requestToText('val');
    $notifyname = url::requestToText('name');
    $username = $_SESSION['user']['username'];
    $type = url::requestToText('type');
    $sel = url::requestToText('sel');
    if ($validate == "1") {
        ADMN_ValidateNotifyName($key, $pdo, $username, $notifyname, $type, $sel);
    } else {
        $res = ADMN_SubmitNotifyData($key, $pdo, $username, $type, $sel);
        if ($res) {
            echo "Updated successfully";
        }
    }
}

function AJAX_ADMN_NotifyL3Profiles()
{
    $db = pdo_connect();
    $key = '';
    $id = url::requestToText('id');
    $res = ADMN_NotifyL3Profiles($key, $db, $id);
    echo json_encode($res);
}

function AJAX_ADMN_NotyUpdateSolution()
{
    $db = pdo_connect();
    $key = '';
    ADMN_NotyUpdateSolution($key, $db);
}

function AJAX_ADMN_GetNotifDetails()
{
    $key = "";
    $db = pdo_connect();
    $id = url::requestToText('id');
    $detailsRes = ADMN_GetNotificationDetails($key, $db, $id);
    echo json_encode($detailsRes);
}

function AJAX_ADMN_DeleteNotify()
{
    $db = pdo_connect();
    $key = '1';
    $id = url::requestToText('id');

    $res = ADMN_DeleteNotify($key, $db, $id);
    if ($res) {
        echo "Deleted successfully";
    }
}

function AJAX_GetEventfilterGridData()
{
    $db = pdo_connect();
    $search = strip_tags(url::requestToAny('search')['value']);
    $authuser = $_SESSION['user']['username'];
    $append_search = "";
    $where = '';
    $fromDate = '';

    if ($search != "") {
        $append_search = " and LOWER(S.name) like '%$search%'";
    } else {
        $append_search = "";
    }
    $filter = url::requestToText('search');

    if ($filter == 1) {

        $evntScope = url::requestToText('evntScope');
        $eventname = url::requestToText('eventname');
        $evntowner = url::requestToText('evntowner');

        $devhr = 0;
        $devminute = 0;
        $devsec = 0;
        $crtedmnth = url::requestToText('crtedmnth');
        $crtdday = url::requestToText('crtdday');
        $crtdyear = url::requestToText('crtdyear');

        if ($evntScope != '') {
            $where = "and S.global = $evntScope";
        }
        if ($eventname != '') {
            $where = "and S.name = '$eventname'";
        }

        if ($evntowner != '') {
            $where = "and S.id = '$evntowner'";
        }

        if ($crtdyear != '') {
            $fromDate = mktime($devhr, $devminute, $devsec, $crtedmnth, $crtdday, $crtdyear);
            $where = "and created >= $fromDate";
        }
    }

    $res = ADMN_GetEventfilterGridData('1', $db, $append_search, $authuser, $where, $fromDate);
    $result = FORMAT_ADMN_GetEventfilterGridData($res);

    echo json_encode($result);
}

function AJAX_SubmitFilter()
{
    $db = pdo_connect();

    $validation = UTIL_GetInteger('val', '');
    $username = $_SESSION['user']['username'];
    $name = UTIL_GetString('name', '');
    $filter = UTIL_GetString('filter', '');
    $decode_filter = mysqli_real_escape_string($filter);
    $global = UTIL_GetInteger('global', 0);
    $md5filter = md5($name . $filter);

    if ($validation == "1") {
        ADMN_EventValidateName('1', $db, $name, $username);
    } else {
        ADMN_EventSubmitData('1', $db, $name, $decode_filter, $username, $md5filter, $global);
    }
}

function AJAX_UpdateFilter()
{
    $db = pdo_connect();
    $key = '';

    $validation = UTIL_GetInteger('val', '');
    $username = $_SESSION['user']['username'];
    $name = UTIL_GetString('name', '');
    $filter = UTIL_GetString('filter', '');
    $decode_filter = mysql_real_escape_string($filter);
    $editid = UTIL_GetInteger('editid', '');
    $global = UTIL_GetInteger('global', 0);

    ADMN_EventUpdateData('1', $db, $name, $decode_filter, $username, $editid, $global);
}

function AJAX_deleteFilter()
{
    $db = pdo_connect();
    $deleteid = UTIL_GetInteger('deleteid', '');

    $deleteddata = ADMN_DeleteData('1', $db, $deleteid);
    echo $deleteddata;
}

function AJAX_copyFilter()
{
    $db = pdo_connect();

    $key = "";
    $validation = UTIL_GetInteger('val', '');
    $username = $_SESSION['user']['username'];
    $name = UTIL_GetString('name', '');
    $filter = UTIL_GetString('filter', '');
    $md5filter = md5($name . $filter);
    $copyid = UTIL_GetInteger('copyid', '');
    $global = UTIL_GetInteger('global', 0);

    if ($validation == "1") {
        ADMN_EventValidateName($key, $db, $name, $username);
    } else {
        ADMN_CopyData('1', $db, $name, $filter, $username, $copyid, $md5filter, $global);
    }
}

function AJAX_GetDeploymentLeftList()
{
    $db = pdo_connect();
    $key = '1';
    $site = UTIL_GetString('site', '');
    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];
    } else if ($searchType == "Sites") {
        $site = $_SESSION["searchValue"];
        $host = $_SESSION["searchValue"];
    }
    $res = DEPL_GetLeftGridData($key, $db, $searchType, $site, $host);
    FORMAT_DEPL_LeftGridData($res, $db);
}

function AJAX_DEPL_AddSubnetId()
{

    $db = pdo_connect();
    $res = checkModulePrivilege('addsubnet', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    $key = '1';

    $subnetip = url::requestToText('subip');

    $username = url::requestToText('username');
    $password = url::requestToText('password');
    $domain = url::requestToText('domain');

    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];
    } else if ($searchType == "Sites") {
        $site = $_SESSION["searchValue"];
        $host = $_SESSION["searchValue"];
    }

    $validate = DEPL_ValidateSubnetIp($key, $db, $subnetip, $site);

    $validateImp = DEPL_CheckImpersonation($key, $site, $host, $db);

    if ($validate == 0) {

        DEPL_AddSubnetId($key, $db, $subnetip, $site, '');

        if ($validateImp == "no impersonation") {
            if ($searchType == "ServiceTag") {
                $res = DEPL_AddImpersonation($key, $username, $password, $domain, $site, $host, $db);
            }
        } else if ($validateImp == "impersonation exist") {
            $res = DEPL_UpdateImpersonationCreds($key, $site, $host, $db, $username, $password, $domain);
        }
    } else {
        echo "1";
    }
}

function AJAX_getImporsonationDetails()
{
    $db = pdo_connect();
    $key = '';
    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];

        $res = DEPL_GetImpersonationCreds($key, $site, $host, $db);
        $res['level'] = "machine";
    } else if ($searchType == "Sites") {
        $res['level'] = "machine";
    }
    echo json_encode($res);
}

function AJAX_UpdateImpersonationCreds()
{
    $db = pdo_connect();

    $res = checkModulePrivilege('modifysubnet', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    $key = '';

    $username = url::requestToText('username');
    $pwd = url::requestToText('pwd');
    $domain = url::requestToText('domain');

    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];

        $res = DEPL_UpdateImpersonationCreds($key, $site, $host, $db, $username, $pwd, $domain);
    } else if ($searchType == "Sites") {
        $res = "not valid";
    }
    echo $res;
}

function AJAX_GetDeploymentRightList()
{
    $db = pdo_connect();
    $key = '1';
    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];
    } else if ($searchType == "Sites") {
        $site = $_SESSION["searchValue"];
        $host = $_SESSION["searchValue"];
    }

    $submask = UTIL_GetString('submask', '');

    $res = DEPL_GetRightGridData($key, $db, $submask, $site, $host);
    if (!is_array($res)) {
        $result['status'] = $res;
        echo json_encode($result);
    } elseif (safe_count($res) == 0) {
        $result['status'] = 'not scanned';
        echo json_encode($result);
    } else {
        FORMAT_DEPL_RightGridData($res);
    }
}

function AJAX_DEPL_CheckSubnetVlues()
{
    $db = pdo_connect();
    $key = '1';
    $submask = UTIL_GetString('submask', '');
    $site = UTIL_GetString('site', '');
    $host = UTIL_GetString('host', '');

    $res = DEPL_CheckRightGridData($key, $db, $submask, $site, $host);

    if (safe_count($res) == 0) {
        $result['status'] = 'Data Not Found';
        echo $result['status'];
    } elseif (safe_count($res) > 0) {
        $result['status'] = 'Data Found';
        echo $result['status'];
    }
}

function AJAX_DEPL_GetExportDetails()
{

    $key = "";
    $db = pdo_connect();

    $res = checkModulePrivilege('exportsubnet', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    $submask = url::requestToText('subnetmask');
    $level = $_SESSION['passlevel'];
    $searchvalue = $_SESSION['searchValue'];
    $scopeType = $_SESSION['searchType'];
    if ($scopeType == 'ServiceTag') {
        $scope = $_SESSION['rparentName'];
    }
    DEPL_GetExportDetails($key, $scopeType, $searchvalue, $submask, $db);
}

function AJAX_GetMachOnlineStatus()
{

    $db = pdo_connect();
    $key = '';

    $searchValue = UTIL_GetString('host', '');
    $hostname = array($searchValue);

    $remote = DASH_GetAllMachineStatus($key, $db, $hostname);

    $status = $remote[$searchValue][0];
    echo json_encode($status);
}

function AJAX_DEPL_CheckScanJob()
{

    $key = "";
    $db = pdo_connect();
    $site = url::requestToText('site');
    $host = url::requestToText('host');
    $submask = url::requestToText('submask');
    $hostname = array($host);
    $remote = DASH_GetAllMachineStatus($key, $db, $hostname);
    $status = $remote[$host][0];
    if ($status == 'Offline' || $status == '' || $status == 'offline') {
        echo $status;
    } else {
        $check = DEPL_CheckScanJob($key, $db, $site, $host, $submask);
        if ($check == "scan error") {
            DEPL_UpdateScanJob($key, $db, $site, $host, $submask);
        } else if ($check == "no scan") {
            $check = DEPL_InsertScanJob($key, $db, $site, $host, $submask);
        }
        echo $check;
    }
}

function AJAX_DEPL_DeployJob()
{
    $key = "";
    $db = pdo_connect();
    $site = url::requestToText('site');
    $host = url::requestToText('host');
    $submask = url::requestToText('submask');
    $ip = url::requestToText('ip');

    $username = url::requestToText('username');
    $password = url::requestToText('password');
    $domain = url::requestToText('domain');

    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];
    } else if ($searchType == "Sites") {
        $site = $_SESSION["searchValue"];
        $host = $_SESSION["searchValue"];
    }

    $hostname = array($host);
    $remote = DASH_GetAllMachineStatus($key, $db, $hostname);
    $status = $remote[$host][0];
    if ($status == 'Offline' || $status == '' || $status == 'offline') {
        echo $status;
    } else {
        $imp = DEPL_CheckImpersonation($key, $site, $host, $db);
        if ($imp == 'no impersonation') {
            DEPL_AddImpersonation($key, $username, $password, $domain, $site, $host, $db);
        } else if ($imp == "impersonation exist") {
            DEPL_UpdateImpersonationCreds($key, $site, $host, $db, $username, $password, $domain);
        }
        echo "added";
    }
}

function AJAX_DEPL_DeployJobConfirm()
{
    $key = "";
    $db = pdo_connect();

    $res = checkModulePrivilege('deploysubnet', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }

    $site = url::requestToText('site');
    $host = url::requestToText('host');
    $submask = url::requestToText('submask');
    $ip = url::requestToText('ip');

    $username = url::requestToText('username');
    $password = url::requestToText('password');
    $domain = url::requestToText('domain');

    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];
    if ($searchType == "ServiceTag") {
        $site = $_SESSION["rparentName"];
        $host = $_SESSION["searchValue"];
    } else if ($searchType == "Sites") {
        $site = $_SESSION["searchValue"];
        $host = $_SESSION["searchValue"];
    }

    $hostname = array($host);
    $remote = DASH_GetAllMachineStatus($key, $db, $hostname);
    $status = $remote[$host][0];
    if ($status == 'Offline' || $status == '' || $status == 'offline') {
        echo $status;
    } else {
        $check = DEPL_UpdateDeployJob($key, $db, $site, $ip, $submask);
        echo 'pushed';
    }
}

function AJAX_ImpersonationCreds()
{

    $key = "";
    $db = pdo_connect();
    $username = UTIL_GetString('username', '');
    $password = UTIL_GetString('password', '');
    $domain = UTIL_GetString('domain', '');
    $host = UTIL_GetString('host', '');
    $site = UTIL_GetString('site', '');
    $hostname = array($host);

    $imp = DEPL_AddImpersonation($key, $username, $password, $domain, $site, $host, $db);
    echo $imp;
}

function AJAX_GetImpersonationCreds()
{

    $key = "";
    $db = pdo_connect();

    $host = url::requestToText('host');
    $site = url::requestToText('site');

    $imp = DEPL_GetImpersonationCreds($key, $site, $host, $db);
    echo json_encode($imp);
}

function AJAX_GetAssetQueryGridData()
{
    $key = "";
    $db = pdo_connect();
    $username = $_SESSION['user']['username'];
    $res = ADMN_AssetQueryGridData($key, $db, $username, '', '');
    FORMAT_ADMN_AssetQueryGridData($res);
}

function AJAX_ADMN_DeleteAssetQuery()
{
    $db = pdo_connect();
    $key = '1';
    $id = UTIL_GetInteger('id', '');

    $res = ADMN_DeleteAssetQuery($key, $db, $id);
    if ($res) {
        echo "Deleted successfully";
    }
}

function AJAX_RunAssetQuery()
{
    $db = pdo_connect();
    $id = UTIL_GetInteger('id', '');
    $name = UTIL_GetString('auth', '');
    $searchType = $_SESSION["searchType"];
    $searchVal = $_SESSION["searchValue"];
    $parent = $_SESSION["rparentName"];

    $result = ADMN_RunAssetQuery($db, $id, $name, $searchType, $searchVal, $parent);
    if ($result) {
        echo "success";
    }
}

function Ajax_GetProfileList()
{
    $db = pdo_connect();
    $key = '1';
    $customerId = $_SESSION['user']['cId'];
    $custType = $_SESSION['user']['customerType'];
    $channel_id = $_SESSION['user']['channelId'];
    $entity_id = $_SESSION['user']['entityId'];
    PRFL_GetProfileList($db, $key, $customerId, $custType, $channel_id, $entity_id);
}

function Ajax_EditProfile()
{
    $db = pdo_connect();
    $key = '1';
    $menuitem = UTIL_GetString('menuitem', '');
    $dartitem = UTIL_GetInteger('dartitem', '');
    $image = UTIL_GetString('image', '');
    $op_sys = UTIL_GetString('op_sys', '');
    $profile = UTIL_GetString('profile', '');
    $varvalue = UTIL_GetString('varvalue', '');
    $description = UTIL_GetString('description', '');
    $follow = UTIL_GetString('follow', '');
    $sequence = UTIL_GetString('sequence', '');
    $auth = UTIL_GetString('auth', '');
    $mid = UTIL_GetInteger('mid', '');
    $type = UTIL_GetString('type', '');
    $page = UTIL_GetInteger('page', '');

    $result = PRFL_EditProfile($db, $key, $menuitem, $dartitem, $image, $op_sys, $profile, $varvalue, $description, $follow, $sequence, $auth, $mid);

    if ($result) {
        $recordlist = array('msg' => 'valid');
    } else {
        $recordlist = array('msg' => 'Invalid');
    }
    echo json_encode($recordlist);
}

function AJAX_RESOL_ServicesListData()
{
    $key = "1";
    $db = pdo_connect();
    $res = RESOL_Get_ServicesListData($key, $db);
    FORMAT_RESOL_ServicesListData($res);
}

function AJAX_RESOL_ServicesGridData()
{
    $key = "";
    $db = pdo_connect();
    $username = $_SESSION['user']['username'];
    $ProfileName = UTIL_GetString('ProfileName', '');
    $res = RESOL_Get_ServicesGridData($key, $db, $username, $ProfileName);
    $searchtype = $_SESSION["searchType"];
    FORMAT_RESOL_ServicesGridData($res, $searchtype);
}

function AJAX_RESOL_NHConfigListData()
{
    $key = "1";
    $db = pdo_connect();
    $res = RESOL_Get_NHConfigListData($key, $db);
    FORMAT_RESOL_NHConfigListData($res);
}

function AJAX_RESOL_NHConfigGridData()
{
    $key = "1";
    $db = pdo_connect();
    $username = $_SESSION['user']['username'];
    $ProfileName = UTIL_GetString('ProfileName', '');
    $searchtype = $_SESSION["searchType"];
    $searchval = $_SESSION["searchValue"];
    $NHGridData = RESOL_Get_NHConfigGridData($key, $db, $username, $ProfileName, $searchtype, $searchval);
    echo json_encode($NHGridData);
}

function AJAX_RESOL_GetNHConfigExportDetails()
{
    $key = "1";
    $db = pdo_connect();
    $username = $_SESSION['user']['username'];
    $ProfileName = UTIL_GetString('ProfileName', '');
    $searchtype = $_SESSION["searchType"];
    $searchval = $_SESSION["searchValue"];
    $NHGridData = RESOL_Get_NHConfigGridData($key, $db, $username, $ProfileName, $searchtype, $searchval);
    $NHProfileDetails = "";
    EXPORT_NHConfigGridData($NHGridData, $NHProfileDetails, $ProfileName);
}

function AJAX_RESOL_AviraSchedulerData()
{
    $key = "";
    $db = pdo_connect();
    $res = RESOL_Get_AviraSchedulerData($key, $db);
    $searchtype = $_SESSION["searchType"];
    FORMAT_RESOL_AviraSchedulerData($res, $searchtype);
}

function AJAX_RESOL_GetServicesExportDetails()
{
    $key = "1";
    $db = pdo_connect();
    $ProfileName = UTIL_GetString('ProfileName', '');
    $username = $_SESSION['user']['username'];
    $data = RESOL_Get_ServicesGridData($key, $db, $username, $ProfileName);
    $searchtype = $_SESSION["searchType"];
    EXPORT_ServicesGridData($data, $ProfileName, $username, $searchtype);
}

function AJAX_mobility_report()
{

    $db = pdo_connect();

    $tag = $_SESSION['searchType'];
    $fromDate = strtotime(url::requestToText('startDate'));
    $toDate = strtotime(url::requestToAny('endDate'));
    $site = url::requestToText('site');
    $machine = url::requestToText('machine');
    $level = url::requestToText('level');
    $reportType = UTIL_GetString('reportType', '');
    $functionCall = url::requestToText('functionToCall');

    $rowToDisplay = url::requestToText('rowToDisplay');
    $rowToDisplay = implode(',', $rowToDisplay);

    if ($tag == 'Sites') {
        $machine = '';
    }

    if ($site == 'All') {
        $site = UTIL_GetSiteScope($db, $site, $tag);
    }

    if ($functionCall == 'getMapdetails') {
        $result = getMapdetails($fromDate, $toDate, $machine, $db);
    } else if ($functionCall == 'rowReport') {
        $result = rowReport($site, $machine, $fromDate, $toDate, $reportType, $rowToDisplay, $db);
    } else {
        $result = $functionCall($site, $machine, $fromDate, $toDate, $db);
    }

    echo json_encode($result);
}

function Ajax_GetProfileDetails()
{

    $key = "";
    $db = pdo_connect();
    $profile = UTIL_GetString('profile', '');
    $mid = UTIL_GetInteger('mid', '');
    $recordList = [];

    $result = PRFL_GetProfileDetails($db, $key, $mid, $profile);
    $resulttype = $result['type'];
    $resultpage = $result['page'];
    $resultparentid = $result['parentId'];
    $resultsequence = $result['sequence'];

    if ($resulttype == 'L2' || $resulttype == 'L3') {
        $parentname = PRFL_GetParentTileName($db, $key, $resultpage);
        $sequencelist = PRFL_GetSequencelist($key, $db, $resultsequence);

        foreach ($sequencelist as $value) {
            $list .= "<option value='" . $value['id'] . "&nbsp;:&nbsp;" . $value['Description'] . "' >" . $value['id'] . "&nbsp;:&nbsp;" . $value['Description'] . "</option>";
            $editlist .= "<option value='" . $value['id'] . "' title='" . $value['Description'] . "'>" . $value['Description'] . "</option>";
        }
    }

    if ($result) {

        $menuitem = $result['menuItem'];
        $profilename = $result['profile'];
        $dart = $result['dart'];
        $varvalue = $result['varValue'];
        $image = $result['image'];
        $titleDescription = $result['tileDesc'];
        $operatinfsystem = $result['OS'];
        $follow = $result['follow'];
        $sequencelist = $result['sequence'];
        $authflag = $result['authFalg'];
        $type = $result['type'];
        $page = $result['page'];
        $parenttilename = $parentname['profile'];
        $sequence = explode(",", $sequencelist);

        $recordList = array(
            'Menuitem' => $menuitem, 'Profile' => $profilename, 'Dart' => $dart, 'varValue' => $varvalue,
            'Image' => $image, 'Description' => $titleDescription, 'Operatingsystem' => $operatinfsystem,
            'Follow' => $follow, "Sequence" => $list, "authentication" => $authflag, "Type" => $type,
            "Page" => $page, "parenttilename" => $parenttilename, "sequenceedit" => $editlist,
        );
    } else {
        $recordList = array('', '', '', '', '', '', '', '', '', '', '', '');
    }

    echo json_encode($recordList);
}

function Ajax_GetAddProfile()
{

    $key = "";
    $db = pdo_connect();
    $profile = UTIL_GetString('profile', '');
    $mid = UTIL_GetInteger('mid', '');
    $page = [];
    $recordList = [];

    $result = PRFL_GetProfileDetails($db, $key, $mid, $profile);

    $getpage = PRFL_GetProfilePageDetails($db, $key, $result['page']);

    $len = safe_count($getpage);
    foreach ($getpage as $key => $value) {
        $page[] = $value['page'] . '&nbsp;&nbsp;(' . $value['profile'] . ')';
    }

    $pagelist = "<option value='1'>New Profile</option>";
    for ($i = 0; $i < $len; $i++) {
        $pagelistl2 .= "<option value='" . $page[$i] . "' >$page[$i]</option>";
    }

    if ($result) {

        $type = $result['type'];
        $page = $result['page'];
        $sequencelist = $result['sequence'];
        $sequence = explode(",", $sequencelist);

        $leng = safe_count($sequence);
        for ($i = 0; $i < $leng; $i++) {
            $sequenceoption .= "<option value='" . $sequence[$i] . "' >$sequence[$i]</option>";
        }

        $sql = $db->prepare("select page,type from " . $GLOBALS['PREFIX'] . "event.profile order by cast(page as unsigned) desc limit 1");
        $sql->execute();
        $sqlresult = $sql->fetchAll(PDO::FETCH_ASSOC);

        $page_new = $sqlresult['page'] + 1;

        $pahelist = array($page, $page_new);

        $pagedrop = "<option value='" . $pahelist[0] . "'> $pahelist[0] </option>";
        $pagedropL1 = "<option value='" . $pahelist[1] . "'>New Profile</option>";

        $recordList = array("Sequence" => $sequenceoption, 'pagelist' => $pagelist, 'pagelistl2' => $pagelistl2, 'page' => $pagedropL1, 'pagedropL1' => $pagedrop, 'profilepage' => $page);
    }

    echo json_encode($recordList);
}

function Ajax_GetAddProfileValue()
{

    $key = "";
    $db = pdo_connect();
    $menuitem = UTIL_GetString('addmenu', '');
    $dartitem = UTIL_GetInteger('adddart', 'null');
    $image = UTIL_GetString('addimage', '');
    $op_sys = UTIL_GetString('addos', '');
    $profile = UTIL_GetString('addprofile', '');
    $varvalue = UTIL_GetString('addvarvalue', 'null');
    $description = UTIL_GetString('adddecription', '');
    $follow = UTIL_GetString('addfollow', '');
    $sequence = UTIL_GetString('addsequence', '');
    $auth = UTIL_GetString('addauthentication', '');
    $page = UTIL_GetInteger('addpage', '');
    $parentid = UTIL_GetInteger('addpagelist', '');
    $type = UTIL_GetString('addtype', '');
    $mid = UTIL_GetInteger('mid', '');
    $variable = UTIL_GetString('variable', '');

    if ($type == 'L3') {
        $parentid = $mid;
    }

    $resultvalid = PRFL_GetValidateProfile($db, $key, $mid, $page, $menuitem, $type, $profile);
    $resultmid = $resultvalid['mid'];
    if ($resultmid == '') {

        $result = PRFL_GetAddProfilesubmit($key, $db, $menuitem, $dartitem, $image, $op_sys, $profile, $varvalue, $description, $follow, $sequence, $auth, $page, $parentid, $type, $variable);

        if ($result) {
            $recordList = array('msg' => 'valid');
        } else {
            $recordList = array('msg' => 'Invalid');
        }
    } else {
        $recordList = array('msg' => 'Invalid');
    }
    echo json_encode($recordList);
}

function Ajax_DeleteProfile()
{

    $key = "";
    $db = pdo_connect();
    $mid = UTIL_GetInteger('mid', '');
    $customerId = $_SESSION['user']['cId'];
    $recordList = [];

    $result = PRFL_GetDeleteProfile($key, $db, $mid, $customerId);

    if ($result) {
        $recordList = array("msg" => 'valid');
    } else {
        $recordList = array("msg" => 'Invalid');
    }

    echo json_encode($recordList);
}

function Ajax_SequenceNumber()
{

    $key = '';
    $db = pdo_connect();
    $sid = UTIL_GetString('sid', '');
    $sidexplode = explode(",", $sid);
    $id = '';
    $result = PRFL_GetSequenceList($key, $db, $id);

    foreach ($result as $key => $value) {

        $id = $value['id'];
        $version = $value['Version'];
        $dart = $value['DART'];
        $description = $value['Description'];

        if (in_array($id, $sidexplode)) {
            $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" onclick="uniqueCheckBox();" class="sequence_check" name="' . $id . '" id="' . $id . '" checked><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
        } else {
            $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" onclick="uniqueCheckBox();" class="sequence_check" name="' . $id . '" id="' . $id . '"><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
        }

        $descript = "<a href='#' onclick = 'sequenceDetails(" . $id . ");' style='color:green;'> " . $description . " </a>";

        $recordList[] = array($checkBox, $version, $dart, $descript);
    }

    echo json_encode($recordList);
}

function Ajax_SequenceDetails()
{
    $key = '';
    $db = pdo_connect();
    $cid = UTIL_GetInteger('cid', '');

    $result = PRFL_GetSequenceDetails($key, $db, $cid);

    foreach ($result as $key => $value) {

        $variable = $value['Variable'];
        $varvalue = $value['VarValue'];

        $recordList[] = array($variable, $varvalue);
    }

    echo json_encode($recordList);
}

function AJAX_GetSummaryRprtExprtData($rptDate)
{

    $db = pdo_connect();
    $key = '';

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $rptDate = UTIL_GetInteger('reportDate', 8);

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $reportdurtn = UTIL_GetReportDuration($rptDate);
    $names = array('Hard Disk Failure', 'Processor Failure', 'CPU Failure', 'RAM Failure', 'Battery Failure', 'Firewall Disabled', 'Multiple Antivirus Installed', 'No Anti-Spyware Installed', 'Anti Virus not up-to-date', 'Device Ram less than 10%', 'Device Battery less than 10%', 'Device Disk space less than 20%');
    $itemIds = UTIL_GetItemId($db, 'EventItems', $names);
    switch ($searchType) {
        case 'Sites':
            $summaryData = DASH_GetSummaryRprtSite($key, $db, $dataScope, $itemIds, $reportdurtn);
            $summaryDetails = UTIL_FormatSummaryExprtData($summaryData);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $summaryData = DASH_GetGetSummaryRprtGrp($key, $db, $machines, $itemIds, $reportdurtn);
            $summaryDetails = UTIL_FormatSummaryExprtData($summaryData);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $summaryData = DASH_GetSummaryRprtMach($key, $db, $censusId, $itemIds, $reportdurtn);
            $summaryDetails = UTIL_FormatSummaryExprtData($summaryData);
            break;
        default:
            break;
    }
    EXPORT_SummaryExportList($summaryDetails);
}

function AJAX_GetCapacityReportData()
{
    global $db;
    $db = pdo_connect();
    $key = '';
    $rparentname = $_SESSION['rparentName'];
    $passLevel = $_SESSION['passlevel'];
    $user = $_SESSION['user']['username'];
    $sites = DASH_GetSites($key, $db, $user);
    foreach ($sites as $value) {
        $siteVal .= "'" . $value . "',";
    }
    $siteVal = rtrim($siteVal, ',');

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    if ($passLevel == 'Sites') {
        $siteVal = "'" . $rparentname . "'";
    } else {
        $siteVal = $siteVal;
    }

    $capResult = DASH_GetAllCapacityData($searchType, $dataScope, $siteVal);
    $recordList = FORMAT_CreateCapacityGridArray($capResult);

    echo json_encode($recordList);
}

function AJAX_GetAdhocReportData()
{

    global $db;
    $db = pdo_connect();
    $key = '';
    $rparentname = $_SESSION['rparentName'];
    $passLevel = $_SESSION['passlevel'];
    $user = $_SESSION['user']['username'];
    $sites = DASH_GetSites($key, $db, $user);
    foreach ($sites as $value) {
        $siteVal .= "'" . $value . "',";
    }
    $siteVal = rtrim($siteVal, ',');

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    if ($passLevel == 'Sites') {
        $siteVal = "'" . $rparentname . "'";
    } else {
        $siteVal = $siteVal;
    }

    $capResult = DASH_GetAllAdocQueryData($searchType, $dataScope, $siteVal);

    echo json_encode($capResult);
}

function AJAX_GetSelect_Data()
{
    $eventType = url::issetInRequest('eventType') ? url::requestToText('eventType') : "";
    global $db;
    $db = pdo_connect();
    $result = DASH_GetEventData($eventType, $db);
    echo $result;
}

function AJAX_GetSelectedEdit()
{
    $selectedDataId = url::issetInRequest('selectedDataId') ? url::requestToText('selectedDataId') : "";
    global $db;
    $db = pdo_connect();
    $result = DASH_GetEvent_EditData($selectedDataId, $db);
    echo $result;
}

function AJAX_GetSelectedDel()
{
    $selectedDataId = url::issetInRequest('selectedDataId') ? url::requestToText('selectedDataId') : "";
    global $db;
    $db = pdo_connect();
    $result = DASH_GetEvent_DelData($selectedDataId, $db);
    echo $result;
}

function AJAX_GetSelectedView()
{
    $selectedDataId = url::issetInRequest('selectedDataId') ? url::requestToText('selectedDataId') : "";
    global $db;
    $db = pdo_connect();
    $result = DASH_GetEvent_ViewData($selectedDataId, $db);
    echo json_encode($result);
}

function AJAX_add_adhocQueries()
{
    global $db;
    $db = pdo_connect();
    $data['userName'] = $_SESSION['user']['logged_username'];
    $data['searchType'] = $_SESSION['searchType'];
    $data['rparentName'] = $_SESSION['rparentName'];
    $data['searchValue'] = $_SESSION['searchValue'];
    $data['report_type'] = url::issetInRequest('report_type') ? url::requestToText('report_type') : "";
    $data['report_name'] = url::issetInRequest('report_name') ? url::requestToText('report_name') : "";
    $data['dart_num'] = url::issetInRequest('dart_num') ? url::requestToText('dart_num') : "";
    $data['scrp_num'] = url::issetInRequest('scrp_num') ? url::requestToText('scrp_num') : "";
    $data['report_duration'] = url::issetInRequest('report_duration') ? url::requestToText('report_duration') : "";
    $data['report_email'] = url::issetInRequest('report_email') ? url::requestToText('report_email') : "";
    $data['assSearch_num'] = url::issetInRequest('assSearch_num') ? url::requestToText('assSearch_num') : "";
    $data['dayshour'] = url::issetInRequest('dayshour') ? url::requestToText('dayshour') : "";
    $data['days_min'] = url::issetInRequest('days_min') ? url::requestToText('days_min') : "";
    $data['weekday'] = url::issetInRequest('weekday') ? url::requestToText('weekday') : "";
    $data['week_hour'] = url::issetInRequest('week_hour') ? url::requestToText('week_hour') : "";
    $data['week_min'] = url::issetInRequest('week_min') ? url::requestToText('week_min') : "";
    $data['month_day'] = url::issetInRequest('month_day') ? url::requestToText('month_day') : "";
    $data['month_hour'] = url::issetInRequest('month_hour') ? url::requestToText('month_hour') : "";
    $data['month_min'] = url::issetInRequest('month_min') ? url::requestToText('month_min') : "";

    $result = DASH_addQueryData($data, $db);
    echo $result;
}

function AJAX_Edit_adhocQueries()
{
    global $db;
    $db = pdo_connect();
    $data['userName'] = $_SESSION['user']['logged_username'];
    $data['searchType'] = $_SESSION['searchType'];
    $data['rparentName'] = $_SESSION['rparentName'];
    $data['searchValue'] = $_SESSION['searchValue'];
    $data['report_type'] = url::issetInRequest('report_type') ? url::requestToText('report_type') : "";
    $data['report_name'] = url::issetInRequest('report_name') ? url::requestToText('report_name') : "";
    $data['dart_num'] = url::issetInRequest('dart_num') ? url::requestToText('dart_num') : "";
    $data['scrp_num'] = url::issetInRequest('scrp_num') ? url::requestToText('scrp_num') : "";
    $data['report_duration'] = url::issetInRequest('report_duration') ? url::requestToText('report_duration') : "";
    $data['report_email'] = url::issetInRequest('report_email') ? url::requestToText('report_email') : "";
    $data['assSearch_num'] = url::issetInRequest('assSearch_num') ? url::requestToText('assSearch_num') : "";
    $data['editReportID'] = url::issetInRequest('editReportID') ? url::requestToText('editReportID') : "";
    $data['Editreport_Status'] = url::issetInRequest('Editreport_Status') ? url::requestToText('Editreport_Status') : "";
    $data['dayshour'] = url::issetInRequest('dayshour') ? url::requestToText('dayshour') : "";
    $data['days_min'] = url::issetInRequest('days_min') ? url::requestToText('days_min') : "";
    $data['weekday'] = url::issetInRequest('weekday') ? url::requestToText('weekday') : "";
    $data['week_hour'] = url::issetInRequest('week_hour') ? url::requestToText('week_hour') : "";
    $data['week_min'] = url::issetInRequest('week_min') ? url::requestToText('week_min') : "";
    $data['month_day'] = url::issetInRequest('month_day') ? url::requestToText('month_day') : "";
    $data['month_hour'] = url::issetInRequest('month_hour') ? url::requestToText('month_hour') : "";
    $data['month_min'] = url::issetInRequest('month_min') ? url::requestToText('month_min') : "";

    $result = DASH_editQueryData($data, $db);
    echo $result;
}

function AJAX_GetCapacityReportDataExport()
{
    $objPHPExcel = EXPORT_GetCapacityReportDataExport();
    $fn = "Capacityreport.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function AJAX_ProfileExcelDownload()
{

    $key = '';
    $db = pdo_connect();
    $mid = UTIL_GetString('selected', '');

    $sqlres = PRFL_GetProfileExportList($key, $db, $mid);
    db_change($GLOBALS['PREFIX'] . 'event', $db);

    if ($sqlres) {
        $db = new MyDB();
        if (!$db) {
        } else {
            chmod("../Querydb/profile_'" . time() . "'.db", 0777);

            $sql = "
                CREATE TABLE main (
                mid         INT PRIMARY KEY NOT NULL,
                menuItem    TEXT,
                type        TEXT,
                parentId    TEXT,
                profile     TEXT,
                dart	    TEXT,
                variable    TEXT,
                varValue    TEXT,
                shortDesc   TEXT,
                description TEXT,
                image       TEXT,
                tileSize    TEXT,
                tileDesc    TEXT,
                iconPos     TEXT,
                OS          TEXT,
                page        TEXT,
                lang        TEXT,
                status      TEXT,
                themeColo   TEXT,
                themeFont   TEXT,
                theme       TEXT,
                follow      TEXT,
                addon	    TEXT,
                addonDart   TEXT,
                authFalg    TEXT,
                usageType   TEXT,
                sequence    TEXT,
                isRD        TEXT,
                scheduleTime TEXT);";

            $sql1 = "
                CREATE TABLE ConfigurationMaster
                (Id               INT PRIMARY KEY NOT NULL,
                Vesrion           TEXT,
                DART              TEXT,
                Description       TEXT);";

            $sql2 = "
                    CREATE TABLE ConfigurationDetails
                    (Id           INT PRIMARY KEY NOT NULL,
                    cid           NUMERIC,
                    Variable      TEXT,
                    VarType       TEXT,
                    VarValue      TEXT,
                    wn_id         TEXT,
                    def           NUMERIC,
                    scop          NUMERIC,
                    pwsc          NUMERIC,
                    descval       TEXT);";

            $sql3 = "
                    CREATE TABLE ServiceLog_Master
                    (mid             INT PRIMARY KEY NOT NULL,
                    dartNo           INT,
                    tileName         TEXT,
                    varValues        TEXT,
                    successDesc      TEXT,
                    terminateDesc    TEXT,
                    Type             TEXT);";

            $sql4 = "
                    CREATE TABLE Status_Master
                    (sm_id           INT PRIMARY KEY NOT NULL,
                    dart             NUMERIC,
                    statusName       TEXT,
                    isEnabled        TEXT);";

            $sql5 = "
                    CREATE TABLE Status_Details
                    (sd_id           INT PRIMARY KEY NOT NULL,
                    page             TEXT,
                    profile          TEXT,
                    varValues        TEXT,
                    variable         TEXT,
                    dartfrom         TEXT,
                    darttoExecute    TEXT,
                    description      TEXT,
                    logicType        TEXT,
                    logicPara        TEXT,
                    dispBtn          TEXT,
                    url              TEXT,
                    status           INTEGER,
                    title            TEXT,
                    parent           TEXT,
                    UISection        TEXT,
                    GUIType          TEXT,
                    addCss           TEXT,
                    functiontoCall   TEXT,
                    ImageFileName    TEXT,
                    usageType        TEXT);";

            $ret = $db->exec($sql);
            $ret1 = $db->exec($sql1);
            $ret2 = $db->exec($sql2);
            $ret3 = $db->exec($sql3);
            $ret4 = $db->exec($sql4);
            $ret5 = $db->exec($sql5);
            if (!$ret) {
                echo $db->lastErrorMsg();
            } else {

                foreach ($sqlres as $key => $value) {
                    $return = '';
                    $return .= '(';
                    $return .= '"' . $value['mid'] . '", ';
                    $return .= '"' . $value['menuItem'] . '", ';
                    $return .= '"' . $value['type'] . '", ';
                    $return .= '"' . $value['parentId'] . '", ';
                    $return .= '"' . $value['profile'] . '", ';
                    $return .= '"' . $value['dart'] . '", ';
                    $return .= '"' . $value['variable'] . '", ';
                    $return .= '"' . $value['varValue'] . '", ';
                    $return .= '"' . $value['shortDesc'] . '", ';
                    $return .= '"' . $value['description'] . '", ';
                    $return .= '"' . $value['image'] . '", ';
                    $return .= '"' . $value['tileSize'] . '", ';
                    $return .= '"' . $value['tileDesc'] . '", ';
                    $return .= '"' . $value['iconPos'] . '", ';
                    $return .= '"' . $value['OS'] . '", ';
                    $return .= '"' . $value['page'] . '", ';
                    $return .= '"' . $value['lang'] . '", ';
                    $return .= '"' . $value['status'] . '", ';
                    $return .= '"' . $value['themeColo'] . '", ';
                    $return .= '"' . $value['themeFont'] . '", ';
                    $return .= '"' . $value['theme'] . '", ';
                    $return .= '"' . $value['follow'] . '", ';
                    $return .= '"' . $value['addon'] . '", ';
                    $return .= '"' . $value['addonDart'] . '", ';
                    $return .= '"' . $value['authFalg'] . '", ';
                    $return .= '"' . $value['usageType'] . '", ';
                    $return .= '"' . $value['sequence'] . '",';
                    $return .= '"' . '1' . '",';
                    $return .= '"' . 'null' . '"';
                    $return .= "),";

                    if ($value['type'] == 'L3') {
                        $seq .= "," . $value['sequence'] . ",";
                    }
                    $seq = ltrim($seq, ",");
                    $seq = rtrim($seq, ",");
                }
                $return = rtrim($return, ",");
                $sql1 = "INSERT INTO main (mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,"
                    . "tileDesc,iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart,authFalg,usageType,sequence,"
                    . "isRD,scheduleTime) VALUES" . $return;

                $seqQuery = PRFL_GetSequenceQuery($key, $db, $seq);
                $seqQueryres = explode("#", $seqQuery);
                $sql2 = "INSERT INTO ConfigurationMaster (Id, Vesrion,DART,Description) VALUES" . $seqQueryres[0];

                $seqDetailQuery = PRFL_GetSequenceDetailQuery($key, $db, $seqQueryres[1]);
                $sql3 = "INSERT INTO ConfigurationDetails (Id, cid, Variable, VarType,VarValue, wn_id, def, scop, pwsc, descval) VALUES" . $seqDetailQuery;

                $servicelogQuery = PRFL_GetServiceLogMasterQuery($key, $db);
                $sql4 = "INSERT INTO ServiceLog_Master (mid, dartNo, tileName, varValues, successDesc, terminateDesc, Type) VALUES" . $servicelogQuery;

                $statusmasterQuery = PRFL_GetStatusMasterQuery($key, $db);
                $sql5 = "INSERT INTO Status_Master (sm_id, dart, statusName, isEnabled) VALUES" . $statusmasterQuery;

                $statusdetailQuery = PRFL_GetStatusDetailQuery($key, $db);
                $sql6 = "INSERT INTO Status_Details (sd_id, page, profile, varValues, variable, dartfrom, dartToExecute, description, logicType, "
                    . "logicPara, dispBtn, url, status, title, parent, UISection, GUIType, addCss, functionToCall, ImageFileName, usageType) "
                    . "VALUES" . $statusdetailQuery;

                $ret = $db->exec($sql1);
                $ret1 = $db->exec($sql2);
                $ret2 = $db->exec($sql3);
                $ret3 = $db->exec($sql4);
                $ret4 = $db->exec($sql5);
                $ret5 = $db->exec($sql6);
                if (!$ret) {
                } else {

                    $name = "profile_'" . time() . "'.db";
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/force-download');
                    header("Content-Disposition: attachment; filename=\"" . basename($name) . "\";");
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($name));
                    ob_clean();
                    flush();
                    readfile("../Querydb/" . $name);
                    exit;
                }
            }
            $db->close();
        }
    }
}

function AJAX_UpdateProfileMap()
{
    $db = pdo_connect();
    $key = '';
    $recordlist = [];

    $customerId = $_SESSION['user']['cId'];
    $values = url::requestToText('selected');

    $result = PRFL_UpdateProfileMap($key, $db, $customerId, $values);

    if ($result == 1 || $result == '1') {
        $recordlist = array('msg' => 'success');
    } else {
        $recordlist = array('msg' => 'failed');
    }

    echo json_encode($recordlist);
}

function AJAX_UpdateTimeZone()
{

    $timeZone = UTIL_GetString('timeZone', '');
    $_SESSION['userTimeZone'] = $timeZone;
}

function AJAX_AddConfiguration()
{
    $db = pdo_connect();
    $key = '';

    $version = UTIL_GetString('version', '');
    $dart = UTIL_GetInteger('dart', '');
    $description = UTIL_GetString('decription', '');

    $result = PRFL_GetConfigurationSubmit($db, $key, $version, $dart, $description);

    if ($result) {
        $jsonlist = array('msg' => 'success');
    } else {
        $jsonlist = array('msg' => 'failed');
    }

    echo json_encode($jsonlist);
}

function AJAX_AddConfigDetail()
{
    $db = pdo_connect();
    $key = '';

    $cid = url::requestToText('cid');
    $varibale = url::requestToText('variable');
    $vartype = url::requestToText('vartype');
    $varvalue = url::requestToText('varvalue');
    $scope = url::requestToText('scope');
    $desval = url::requestToText('desval');

    $result = PRFL_GetConfigurationDetailSubmit($db, $key, $cid, $varibale, $vartype, $varvalue, $scope, $desval);

    if ($result) {
        $recordlist = array("msg" => 'success');
    } else {
        $recordlist = array("msg" => 'failed');
    }

    echo json_encode($recordlist);
}

function AJAX_AddServiceLogMaster()
{
    $db = pdo_connect();
    $key = '';

    $tilename = url::requestToText('tilename');
    $sucssdesc = url::requestToText('Sucssdesc');
    $termdesc = url::requestToText('termdesc');
    $dart = url::requestToText('dart');
    $type = 'Tile';
    $varValues = url::requestToText('varvalue');

    $result = PRFL_GetServiceLogMasterValues($key, $db, $tilename, $sucssdesc, $termdesc, $dart, $type, $varValues);
}

function AJAX_ConfigMasterDetail()
{
    $db = pdo_connect();
    $key = '';

    $version = UTIL_GetString('version', '');
    $dart = UTIL_GetInteger('dart', '');
    $description = UTIL_GetString('decription', '');

    $result = PRFL_GetConfigurationMasterValues($db, $key, $version, $dart, $description);

    if ($result) {
        $id = $result['id'];
    }

    $jsonData = array('id' => $id, 'dart' => $dart);

    echo json_encode($jsonData);
}

function AJAX_Configurationedit()
{
    $db = pdo_connect();
    $key = '';
    $id = '';

    $result = PRFL_GetSequenceList($key, $db, $id);

    if ($result) {
        foreach ($result as $key => $value) {
            $id = $value['id'];
            $version = $value['Version'];
            $dart = $value['DART'];
            $description = $value['Description'];
            $checkBox = '<div class="checkbox inline"><label><input type="radio" class="configurationcheck" name="' . $id . '" id="' . $id . '"></label></div>';
            $recordlist[] = array($checkBox, $version, $dart, $description);
        }
    } else {
        $recordlist = array();
    }
    echo json_encode($recordlist);
}

function AJAX_UpdateConfigurationMaster()
{
    $db = pdo_connect();
    $key = '';
    $id = UTIL_GetInteger('id', '');

    $result = PRFL_GetSequenceList($key, $db, $id);

    if ($result) {
        foreach ($result as $key => $value) {
            $version = $value['Version'];
            $dart = $value['DART'];
            $description = $value['Description'];

            $recordlist[] = array("id" => $id, "version" => $version, "dart" => $dart, "description" => $description);
        }
    } else {
        $recordlist = array();
    }

    echo json_encode($recordlist);
}

function AJAX_UpdateConfigMaster()
{
    $db = pdo_connect();
    $key = '';

    $id = url::requestToText('id');
    $version = url::requestToText('version');
    $dart = url::requestToText('dart');
    $descrip = url::requestToText('descrip');

    $result = PRFL_UpdateConfigMaster($key, $db, $id, $version, $dart, $descrip);

    if ($result) {
        $jsondata = array('msg' => 'success', 'dart' => $dart);
    } else {
        $jsondata = array('msg' => 'failed');
    }

    echo json_encode($jsondata);
}

function AJAX_GetConfigDetail()
{
    $db = pdo_connect();
    $key = '';
    $id = url::requestToText('id');

    $result = PRFL_GetSequenceDetails($key, $db, $id);
    if ($result) {
        $varibale .= "<option value='AddNew' style='color:#48b2e4;'>Add New Variable</option>";
        foreach ($result as $key => $value) {
            $varibale .= "<option value='" . $value['Id'] . "'>" . $value['Variable'] . "</option>";
        }
    }

    echo json_encode($varibale);
}

function AJAX_ResetDeployScan()
{
    $db = pdo_connect();
    $key = '';
    $site = UTIL_GetString('site', '');
    $subnetMask = UTIL_GetString('subnetmask', '');

    $scanStatus = DEPL_ResetDeployScan($key, $db, $subnetMask, $site);
    echo $scanStatus;
}

function AJAX_GetAvailStatus()
{
    $db = pdo_connect();
    $key = '';
    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $machines = DASH_GetMachinesSites($key, $db, $dataScope);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            break;
        case 'ServiceTag':
            $machines = array("1");
            break;
        default:
            break;
    }

    if (safe_count($machines) > 0) {
        echo "found";
    } else {
        echo "notfound";
    }
}

function AJAX_GetComplianceExportDetails()
{
    $db = pdo_connect();
    $key = '';

    $searchType = UTIL_GetString('searchType', '');
    $searchValue = UTIL_GetString('searchValue', '');
    $itemtype = UTIL_GetString('itemtype', 0);
    $itemid = UTIL_GetInteger('itemid', 0);
    $status = UTIL_GetString('status', 0);
    $draw = UTIL_GetInteger('draw', 1);
    $detail = 1;
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $complianceData = DASH_GetComplainceFilterDetailsSite($key, $db, $dataScope, $itemtype, $itemid, $status, $detail);
            $complianceDetails = EXPORT_CompDetailData($complianceData, $draw, $itemtype, $status);
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            $complianceData = DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail);
            $complianceDetails = EXPORT_CompDetailData($complianceData, $draw, $itemtype, $status);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $complianceData = DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status);
            $complianceDetails = EXPORT_CompDetailData($complianceData, $draw, $itemtype, $status);
            break;
        default:
            break;
    }

    echo json_encode($complianceDetails);
}

function AJAX_EditConfigFieldShow()
{
    $db = pdo_connect();
    $key = '';
    $id = url::requestToText('id');

    $result = PRFL_GetSequenceDetailslist($key, $db, $id);
    if ($result) {
        $detailid = $result['Id'];
        $vartype = $result['VarType'];
        $varvalue = $result['VarValue'];
        $descval = $result['descval'];

        $servicelogvarValue = PRFL_GetvarValues($key, $db, $varvalue);
        $tilename = $servicelogvarValue['tilename'];
        $successDesc = $servicelogvarValue['successDesc'];
        $terminate = $servicelogvarValue['terminateDesc'];
        $mid = $servicelogvarValue['mid'];
    }

    $recordlist = array(
        'id' => $detailid, 'vartype' => $vartype, 'varvalue' => $varvalue, 'descval' => $descval, 'tilename' => $tilename,
        'success' => $successDesc, 'terminate' => $terminate, 'mid' => $mid,
    );
    echo json_encode($recordlist);
}

function AJAX_ConfigDetailUpdate()
{
    $db = pdo_connect();
    $key = '';
    $id = url::requestToText('id');
    $vartype = url::requestToText('vartype');
    $descval = url::requestToText('descval');
    $varvalue = url::requestToText('varvalue');

    $result = PRFL_GetUpdateSequence($key, $db, $id, $vartype, $descval, $varvalue);

    if ($result) {
        $jsondata = array('msg' => 'success');
    } else {
        $jsondata = array('msg' => 'failed');
    }

    echo json_encode($jsondata);
}

function AJAX_GetSQLiteDBUpload()
{
    $db = pdo_connect();
    $key = '';
    $recordlist = [];

    $customerId = $_SESSION['user']['cId'];
    $values = url::requestToText('selected');

    $sqllite = AJAX_GetProfileDBUpload($key, $db, $values, $customerId);
    if ($sqllite) {
        echo 1;
    } else {
        echo 0;
    }
}

function AJAX_GetProfileDBUpload($key, $db, $mid, $eid)
{

    $res = MSP_GetEntityDetail($db, $eid);
    $clientlogo = $res['clientlogo'];

    if ($clientlogo == 'default') {

        $companyname = $res['companyName'];
        $UIDirectory = CUST_CreateClient_UIDirectory($companyname);
        $updatePath = MSP_Update_UIDirectoryPath($db, $eid, $UIDirectory);
        $path = $UIDirectory;
    } else {
        if (strpos($clientlogo, '.png') !== false || strpos($clientlogo, '.jpg') !== false || strpos($clientlogo, '.jpeg') !== false) {
            $array = explode('/', $clientlogo);
            array_pop($array);
            $path = implode('/', $array);
        } else {
            $path = $clientlogo;
        }
    }

    $sqlres = PRFL_GetProfileExportList($key, $db, $mid);
    db_change($GLOBALS['PREFIX'] . 'event', $db);

    if ($sqlres) {
        $db = new MyDBUpload($path);

        if (!$db) {
        } else {
            chmod($path . "/profile_" . time() . ".db", 0777);

            $sql = "
                CREATE TABLE main (
                mid         INT PRIMARY KEY NOT NULL,
                menuItem    TEXT,
                type        TEXT,
                parentId    TEXT,
                profile     TEXT,
                dart	    TEXT,
                variable    TEXT,
                varValue    TEXT,
                shortDesc   TEXT,
                description TEXT,
                image       TEXT,
                tileSize    TEXT,
                tileDesc    TEXT,
                iconPos     TEXT,
                OS          TEXT,
                page        TEXT,
                lang        TEXT,
                status      TEXT,
                themeColo   TEXT,
                themeFont   TEXT,
                theme       TEXT,
                follow      TEXT,
                addon	    TEXT,
                addonDart   TEXT,
                authFalg    TEXT,
                usageType   TEXT,
                sequence    TEXT,
                isRD        TEXT,
                scheduleTime TEXT);";

            $sql1 = "
                CREATE TABLE ConfigurationMaster
                (Id               INT PRIMARY KEY NOT NULL,
                Vesrion           TEXT,
                DART              TEXT,
                Description       TEXT);";

            $sql2 = "
                    CREATE TABLE ConfigurationDetails
                    (Id           INT PRIMARY KEY NOT NULL,
                    cid           NUMERIC,
                    Variable      TEXT,
                    VarType       TEXT,
                    VarValue      TEXT,
                    wn_id         TEXT,
                    def           NUMERIC,
                    scop          NUMERIC,
                    pwsc          NUMERIC,
                    descval       TEXT);";

            $sql3 = "
                    CREATE TABLE ServiceLog_Master
                    (mid             INT PRIMARY KEY NOT NULL,
                    dartNo           INT,
                    tileName         TEXT,
                    varValues        TEXT,
                    successDesc      TEXT,
                    terminateDesc    TEXT,
                    Type             TEXT);";

            $sql4 = "
                    CREATE TABLE Status_Master
                    (sm_id           INT PRIMARY KEY NOT NULL,
                    dart             NUMERIC,
                    statusName       TEXT,
                    isEnabled        TEXT);";

            $sql5 = "
                    CREATE TABLE Status_Details
                    (sd_id           INT PRIMARY KEY NOT NULL,
                    page             TEXT,
                    profile          TEXT,
                    varValues        TEXT,
                    variable         TEXT,
                    dartfrom         TEXT,
                    darttoExecute    TEXT,
                    description      TEXT,
                    logicType        TEXT,
                    logicPara        TEXT,
                    dispBtn          TEXT,
                    url              TEXT,
                    status           INTEGER,
                    title            TEXT,
                    parent           TEXT,
                    UISection        TEXT,
                    GUIType          TEXT,
                    addCss           TEXT,
                    functiontoCall   TEXT,
                    ImageFileName    TEXT,
                    usageType        TEXT);";

            $ret = $db->exec($sql);
            $ret1 = $db->exec($sql1);
            $ret2 = $db->exec($sql2);
            $ret3 = $db->exec($sql3);
            $ret4 = $db->exec($sql4);
            $ret5 = $db->exec($sql5);
            if (!$ret) {
                echo $db->lastErrorMsg();
            } else {

                foreach ($sqlres as $key => $value) {
                    $return = '';
                    $return .= '(';
                    $return .= '"' . $value['mid'] . '", ';
                    $return .= '"' . $value['menuItem'] . '", ';
                    $return .= '"' . $value['type'] . '", ';
                    $return .= '"' . $value['parentId'] . '", ';
                    $return .= '"' . $value['profile'] . '", ';
                    $return .= '"' . $value['dart'] . '", ';
                    $return .= '"' . $value['variable'] . '", ';
                    $return .= '"' . $value['varValue'] . '", ';
                    $return .= '"' . $value['shortDesc'] . '", ';
                    $return .= '"' . $value['description'] . '", ';
                    $return .= '"' . $value['image'] . '", ';
                    $return .= '"' . $value['tileSize'] . '", ';
                    $return .= '"' . $value['tileDesc'] . '", ';
                    $return .= '"' . $value['iconPos'] . '", ';
                    $return .= '"' . $value['OS'] . '", ';
                    $return .= '"' . $value['page'] . '", ';
                    $return .= '"' . $value['lang'] . '", ';
                    $return .= '"' . $value['status'] . '", ';
                    $return .= '"' . $value['themeColo'] . '", ';
                    $return .= '"' . $value['themeFont'] . '", ';
                    $return .= '"' . $value['theme'] . '", ';
                    $return .= '"' . $value['follow'] . '", ';
                    $return .= '"' . $value['addon'] . '", ';
                    $return .= '"' . $value['addonDart'] . '", ';
                    $return .= '"' . $value['authFalg'] . '", ';
                    $return .= '"' . $value['usageType'] . '", ';
                    $return .= '"' . $value['sequence'] . '",';
                    $return .= '"' . '1' . '",';
                    $return .= '"' . 'null' . '"';
                    $return .= "),";

                    if ($value['type'] == 'L3') {
                        $seq .= "," . $value['sequence'] . ",";
                    }
                    $seq = ltrim($seq, ",");
                    $seq = rtrim($seq, ",");
                }
                $return = rtrim($return, ",");
                $sql1 = "INSERT INTO main (mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,"
                    . "tileDesc,iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart,authFalg,usageType,sequence,"
                    . "isRD,scheduleTime) VALUES" . $return;

                $seqQuery = PRFL_GetSequenceQuery($key, $db, $seq);
                $seqQueryres = explode("#", $seqQuery);
                $sql2 = "INSERT INTO ConfigurationMaster (Id, Vesrion,DART,Description) VALUES" . $seqQueryres[0];

                $seqDetailQuery = PRFL_GetSequenceDetailQuery($key, $db, $seqQueryres[1]);
                $sql3 = "INSERT INTO ConfigurationDetails (Id, cid, Variable, VarType,VarValue, wn_id, def, scop, pwsc, descval) VALUES" . $seqDetailQuery;

                $servicelogQuery = PRFL_GetServiceLogMasterQuery($key, $db);
                $sql4 = "INSERT INTO ServiceLog_Master (mid, dartNo, tileName, varValues, successDesc, terminateDesc, Type) VALUES" . $servicelogQuery;

                $statusmasterQuery = PRFL_GetStatusMasterQuery($key, $db);
                $sql5 = "INSERT INTO Status_Master (sm_id, dart, statusName, isEnabled) VALUES" . $statusmasterQuery;

                $statusdetailQuery = PRFL_GetStatusDetailQuery($key, $db);
                $sql6 = "INSERT INTO Status_Details (sd_id, page, profile, varValues, variable, dartfrom, dartToExecute, description, logicType, "
                    . "logicPara, dispBtn, url, status, title, parent, UISection, GUIType, addCss, functionToCall, ImageFileName, usageType) "
                    . "VALUES" . $statusdetailQuery;

                $ret = $db->exec($sql1);
                $ret1 = $db->exec($sql2);
                $ret2 = $db->exec($sql3);
                $ret3 = $db->exec($sql4);
                $ret4 = $db->exec($sql5);
                $ret5 = $db->exec($sql6);
                if (!$ret) {
                } else {

                    $name = "/profile_'" . time() . "'.db";
                    readfile($path . $name);
                    return $res;
                }
            }
            $db->close();
        }
    }
}

function AJAX_GetTrimmedSiteName($siteNameString)
{
    if (strpos($siteNameString, '__') !== false) {
        $siteNameArray = explode("__", $siteNameString);
        $siteName = utf8_encode($siteNameArray[0]);
        return $siteName;
    } else {
        return utf8_encode($siteNameString);
    }
}

function AJAX_UpdateServiceLogMstr()
{
    $tilename = url::requestToText('tilename');
    $successdesc = url::requestToText('Sucssdesc');
    $terminatedesc = url::requestToText('termdesc');
    $mid = url::requestToText('editmid');

    $result = PRFL_GetserviceLogUpdate($key, $db, $mid, $tilename, $successdesc, $terminatedesc);

    if ($result) {
        $jsondata = array("msg" => "success");
    } else {
        $jsondata = array("msg" => "failed");
    }

    echo json_encode($jsondata);
}

function AJAX_CheckDeployScan()
{
    $db = pdo_connect();
    $key = '';
    $site = UTIL_GetString('site', '');
    $subnetMask = UTIL_GetString('subnetmask', '');

    $scanStatus = DEPL_CheckResetStatus($key, $db, $subnetMask, $site);
    echo $scanStatus;
}

function Ajax_DeploymentAuditGrid()
{
    $db = pdo_connect();
    $key = '';
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $passLevel = $_SESSION['passlevel'];
    $whereString = '';

    if ($searchType == 'ServiceTag' || $searchType == 'Service Tag' || $searchType == 'Host Name') {
        if ($passLevel == 'Sites' || $passLevel == 'Site') {
            $whereString = $_SESSION['rparentName'];
        } else if ($passLevel == 'Groups' || $passLevel == 'Group') {
            $censusId = $_SESSION['rcensusId'];
            $whereString = DEPL_GetSiteNameUsingCensusId($db, $censusId);
        }
    } else if ($searchType == 'Sites' || $searchType == 'Site') {
        $whereString = $searchValue;
    } else if ($searchType == 'Groups') {
        $whereString = '';
    }

    $dep_Audit = DEPL_DeployAudit($db, $key, $whereString);
    FORMAT_DEPL_DeployAudit($dep_Audit);
}

function Ajax_DeploymentAuditDetailsGrid()
{
    $db = pdo_connect();
    $key = '';
    $idx = url::requestToText('idx');

    $dep_audit_Details = DEPL_DeployAuditDetails($db, $key, $idx);
    FORMAT_DEPL_DeployAuditDetails($dep_audit_Details);
}

function Ajax_DeploymentAuditDetailsExcel()
{
    $db = pdo_connect();
    $key = '';
    $idx = url::requestToText('idx');

    $result_Excel = DEPL_DeployAuditDetails($db, $key, $idx);
    Download_DEPL_DeployAuditDetails($result_Excel);
}

function Download_DEPL_DeployAuditDetails($result_Excel)
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Text2');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Text3');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Text4');

    if (safe_count($result_Excel) > 0) {
        $index = 2;
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $result_Excel['text2'] . '');
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $result_Excel['text3'] . '');
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $result_Excel['text4'] . '');
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available in table');
    }

    $fn = "filename.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function Ajax_Deploy_AuditExcel()
{
    $db = pdo_connect();
    $key = '';
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $passLevel = $_SESSION['passlevel'];
    $whereString = '';

    if ($searchType == 'ServiceTag' || $searchType == 'Service Tag' || $searchType == 'Host Name') {
        if ($passLevel == 'Sites' || $passLevel == 'Site') {
            $whereString = $_SESSION['rparentName'];
        } else if ($passLevel == 'Groups' || $passLevel == 'Group') {
            $censusId = $_SESSION['rcensusId'];
            $whereString = DEPL_GetSiteNameUsingCensusId($db, $censusId);
        }
    } else if ($searchType == 'Sites' || $searchType == 'Site') {
        $whereString = $searchValue;
    } else if ($searchType == 'Groups') {
        $whereString = 'Data not available for group level';
    }

    $dep_AuditDetails = DEPL_DeployDetails($db, $key, $whereString);
    Download_DEPL_DeployDetails($dep_AuditDetails);
}

function Download_DEPL_DeployDetails($dep_AuditDetails)
{
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Site');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Machine');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Time');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Text1');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Text2');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Text3');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Text4');

    $res = Ajax_Create_AuditDetailExcel($dep_AuditDetails, $objPHPExcel);
    return $res;
}

function Ajax_Create_AuditDetailExcel($dep_AuditDetails, $objPHPExcel)
{
    Ajax_Get_DeployAuditData($dep_AuditDetails, $objPHPExcel);

    $fn = "Deploy_AuditDetails.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function Ajax_Get_DeployAuditData($dep_AuditDetails, $objPHPExcel)
{
    if (safe_count($dep_AuditDetails) > 0) {
        $index = 2;
        if (is_array($dep_AuditDetails)) {
            foreach ($dep_AuditDetails as $key => $value) {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . AJAX_GetTrimmedSiteName($value['site'] . ''));
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $value['machine'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $value['time'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $value['text1'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $value['text2'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, '' . $value['text3'] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, '' . $value['text4'] . '');
                $index++;
            }
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available in table');
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available in table');
    }
    return $objPHPExcel;
}

function AJAX_GetEventFilterDtal()
{
    $key = '';
    $db = pdo_connect();
    $eid = UTIL_GetInteger('eid', 0);

    $result = ADMIN_GetEventFilterDetails($db, $key, $eid);

    if ($result) {

        $created = date('m/d/Y H:i A', $result['created']);
        $searchstring = $result['searchstring'];

        $recordlist = array('created' => $created, 'searchstring' => $searchstring);
    } else {
        $recordlist = array('created' => $created, 'searchstring' => $searchstring);
    }

    echo json_encode($recordlist);
}

function AJAX_GetEventRightGridData()
{
    $key = '';
    $db = pdo_connect();
    $eid = UTIL_GetInteger('eid', 0);
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentName = $_SESSION['rparentName'];
    $condition = '';

    if ($searchType == 'Sites') {
        $siteName = $searchValue;
    } elseif ($searchType == 'Groups') {
        $siteName = $searchValue;
    } else {
        $siteName = $rparentName . ":" . $searchValue;
    }
    $condition = " and sitename ='$siteName' ";

    $result = ADMIN_GetEventFilterCronResult($db, $key, $eid, $condition);

    if ($result) {

        foreach ($result as $key => $value) {

            if ($value['fileName'] != '') {
                $download = "<a href='#' onclick='Downloadxls(\"" . $value['fileName'] . "\")'><i class='material-icons icon-ic_file_download_24px'></i></a>";
            } else {
                $download = '';
            }

            if ($value['startTime']) {
                $startTime = date('m/d/Y h:i:s', $value['startTime']);
            } else if ($result['startTime'] == '0') {
                $startTime = '';
            } else {
                $startTime = '-';
            }

            $status = $value['status'];
            $recordlist[] = array($status, $startTime, $download);
        }
    } else {

        $recordlist = array();
    }

    echo json_encode($recordlist);
}

function AJAX_GetEventRunSubmit()
{
    $db = pdo_connect();
    $id = UTIL_GetInteger('eid', '');
    $username = $_SESSION['user']['username'];
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $rParent = $_SESSION['rparentName'];

    $result = ADMN_RunEventFilterQuery($db, $id, $username, $searchType, $searchValue, $rParent);
    if ($result) {
        echo "success";
    }
}

function AJAX_Get_SelectedSitesMachines()
{
    $username = $_SESSION['user']['username'];
    $parentName = UTIL_GetString('parentName', '');
    $type = url::requestToText('type');

    if ($type == 'Groups') {
        $checkAccess = checkUserGroupAccess($username, $parentName);
    } else {
        $checkAccess = checkUserSiteAccess($username, $parentName);
    }

    if ($checkAccess) {
        $type = UTIL_GetString('type', '');
        $limit = UTIL_GetInteger('limit', '');
        $machines = Pane_GetSelectedSitesMachines(NanoDB::connect(), $parentName, $type, $username, $limit);
        echo json_encode($machines);
    } else {
        $response = array("msg" => 'Permission denied');
        echo json_encode($response);
        // exit();
    }
}

function AJAX_GetCensusData()
{

    $db = pdo_connect();
    $username = $_SESSION['user']['username'];
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $draw = url::requestToText('draw');
    $orderval = strip_tags(url::requestToAny('order')[0]['column']);
    $orderValues = '';
    if ($orderval != '') {
        // $orderColoumn = strip_tags(url::requestToAny('columns')[$orderval]['data']);
        $ordertype = strip_tags(url::requestToAny('order')[0]['dir']);
        $orderValues = " order by customer $ordertype";
    }
    $searchVal = strtolower(url::requestToAny('search')['value']);
    if ($searchVal != '') {
        $where = "  and lower(customer) like '%" . $searchVal . "%'";
    } else {
        $where = '';
    }

    $result = ADMN_GetCensusData($db, $username, $searchType, $searchValue, $orderValues, $where);
    $totalRecords = safe_count($result);

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $result);
    echo json_encode($jsonData);
}

function AJAX_GetMachineList()
{
    $pdo = pdo_connect();
    $limitCount = (url::postToInt('limitCount') === 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        if ($orderVal != 'action') {
            $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
        } else {
            $orderStr = 'order by born desc';
        }
    } else {
        $orderStr = 'order by born desc';
    }

    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;
    $searchVal = $_POST['searchValue'] ?? $_SESSION['searchValue'];

    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }
    $siteName = $searchVal;

    $notifSearch = url::postToText('notifSearch');

    if ($notifSearch != '') {
        $whereSearch = " and  (C.host LIKE UPPER('%" . $notifSearch . "%')
            OR os LIKE '%" . strtolower($notifSearch) . "%')";
    } else {
        $whereSearch = '';
    }

    $sql1 = "SELECT DISTINCT(C.host) as host,C.born as born, C.last as last,id,os, clientversion FROM " . $GLOBALS['PREFIX'] . "core.Census C WHERE site = ? $whereSearch $orderStr $limitStr";

    $stmt_mach = $pdo->prepare($sql1);
    $stmt_mach->execute([$siteName]);
    $machineRes = $stmt_mach->fetchAll(PDO::FETCH_ASSOC);

    $res = NanoDB::find_one("SELECT COUNT(DISTINCT C.host) as  totCount FROM " . $GLOBALS['PREFIX'] . "core.Census C WHERE site = ? $whereSearch ", [$siteName]);
    $totCount = $res['totCount'];

    if ($_POST['searchValue']) {
        echo json_encode($machineRes);
        exit;
    }
    if (empty($machineRes)) {
        $dataArr['largeDataPaginationHtml'] = '';
        $dataArr['html'] = '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
        $dataArr['html'] = Format_CensusDataMysql($machineRes);
        echo json_encode($dataArr);
    }
}

function Format_CensusDataMysql($machineRes)
{
    foreach ($machineRes as $key => $val) {

        $host = $val['host'];
        $os = $val['os'];
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $borntime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['born'], "m/d/Y g:i:s A");
            $lasttime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['last'], "m/d/Y g:i:s A");
        } else {
            $borntime = date("m/d/Y g:i:s A", $val['born']);
            $lasttime = date("m/d/Y g:i:s A", $val['last']);
        }

        $born = ($val['born'] == 0) ? '-' : $borntime;
        $last = ($val['last'] == 0) ? '-' : $lasttime;
        $id = $val['id'];
        if ($os == '') {
            $os = '-';
        }

        $clientversion = $val['clientversion'];
        if (!$clientversion  || empty($clientversion)) {
            $clientversion = '-';
        }

        $action = "<p class='ellipsis' title='Expunge'><a href='javascript:'"
            . "onclick='removeMachin(&quot;$id&quot;,&quot;$host&quot;);' style='' >Expunge"
            . "<span id='loader_$id' style='display:none;'>"
            . "<img class='' alt='loader...' src='../assets/img/loader-sm.gif'>"
            . "</span></a></p>";
        $machineList[] = array($host, $os, $born, $last, $clientversion, $action, $id);
    }
    return $machineList;
}

function AJAX_censusExport()
{

    $db = pdo_connect();
    $column = UTIL_GetString('colName', '');
    $condition = UTIL_GetString('condition', '');
    $id = UTIL_GetString('cid', '');

    $column = (is_null($column)) ? '' : $column;

    $condition = rtrim($condition, ',');

    $exportReport = ADMIN_GetCensusExport($db, $column, $condition, $id);

    echo json_encode($exportReport);
}

function AJAX_GETManageJobsData()
{
    $key = 1;
    $db = pdo_connect();
    $type = UTIL_GetInteger('type', 1);
    $start = url::requestToText('start');
    $length = url::requestToText('length');
    $limit = " limit $start , $length";
    $searchVal = strip_tags(url::requestToAny('search')['value']);
    $draw = url::requestToText('draw');
    $orderval = strip_tags(url::requestToAny('order')[0]['column']);

    if ($orderval != '') {
        $orderColoumn = strip_tags(url::requestToAny('columns')[$orderval]['data']);
        $ordertype = strip_tags(url::requestToAny('order')[0]['dir']);
        $orderValues = "order by $orderColoumn $ordertype";
    }

    $data = RESOL_GETManageJobsData($key, $db, $type, $orderValues, $searchVal, $limit);
    $count = count(RESOL_GETManageJobsData($key, $db, $type, '', $searchVal, ''));
    $formatTable = FORMAT_GETManageJobsData($type, $data, $count, $draw);
    echo json_encode($formatTable);
}

function AJAX_census_delete()
{

    $sId = UTIL_GetString('mId', '');

    ADMN_Census_Delete($sId);
    $array = array("status" => "success", "msg" => "Machine deletion successful");
    echo json_encode($array);
}

function AJAX_expunge_func()
{
    $event = url::requestToText('event');
    $asset = url::requestToText('asset');
    $patch = url::requestToText('patch');

    $mId = UTIL_GetInteger('mId', '');

    $index = '';
    if ($event == 1 && $asset == 1 && $patch == 1) {
        $index = 'assets*,events*,patches*';
    }

    if (!$index) {
        if ($asset == 1) {
            $index .= 'assets*,';
        }
        if ($event == 1) {
            $index .= 'events*,';
        }
        if ($patch == 1) {
            $index .= 'patches*,';
        }
    }
    $findex = rtrim($index, ',');
    if ($findex == '') {
        $index = false;
    }
    
    ADMN_Census_Expunge($mId,$index);

    ADMN_Redis_Expunge($mId);

    $auditRes = create_auditLog('Device', 'Expunge', 'Success');

    $array = array("status" => "success", "msg" => "Expunge Successful");
    echo json_encode($array);
}

function AJAX_GetGCMIDFn()
{
    $key = 1;
    $db = pdo_connect();
    $id = UTIL_GetInteger('id', 0);
    $data = RESOL_GetGCMIDFn($key, $db, $id);
    echo json_encode($data);
}

function AJAX_SubmitGCMIDFn()
{
    $key = 1;
    $db = pdo_connect();
    $id = UTIL_GetInteger('id', 0);
    $gcmid = UTIL_GetString('gcmid', '');
    $data = RESOL_SubmitGCMIDFn($key, $db, $id, $gcmid);
    echo $data;
}

function AJAX_DeleteJobsFromAuditFn()
{
    $key = 1;
    $db = pdo_connect();
    $ids = UTIL_GetString('ids', '');
    $data = RESOL_DeleteJobsFromAuditFn($key, $db, rtrim($ids, ','));
    echo $data;
}

function AJAX_GCMDetailsFn()
{
    $key = 1;
    $db = pdo_connect();
    $type = UTIL_GetInteger('type', 1);
    $searchVal = strip_tags(url::requestToAny('search')['value']);

    $data = RESOL_GETManageJobsData($key, $db, $type, '', $searchVal, '');
    EXPORT_GCMDetailsFn($data);
}

function AJAX_getAssetDetails()
{

    $db = pdo_connect();
    $id = url::requestToText('id');

    $sql = $db->prepare("SELECT name,searchstring,displayfields FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches WHERE id= ?");
    $sql->execute([$id]);
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($sqlRes) > 0) {
        $result = array();
        $result['name'] = $sqlRes['name'];
        $result['condition'] = $sqlRes['searchstring'];
        $fields = trim($sqlRes['displayfields'], ':');
        $fields = str_replace(':', '<br />', $fields);

        echo json_encode(array('name' => $sqlRes['name'], 'condition' => $sqlRes['searchstring'], 'fields' => $fields));
    }
}

function AJAX_GetSkuDetails()
{

    $db = pdo_connect();
    $draw = url::requestToText('draw');

    $data = ADMN_GetSkuList($db);
    $totalRecords = safe_count($data);

    if (safe_count($data) > 0) {
        foreach ($data as $key => $val) {
            $id = $val['id'];
            $skuName = $val['skuName'];
            $desc = $val['description'];

            $skuList[] = array(
                "DT_RowId" => $id,
                "skuName" => '<p class="ellipsis" title="' . $skuName . '">' . $skuName . '</p>',
                "description" => '<p class="ellipsis" title="' . $desc . '">' . $desc . '</p>',
            );
        }
    } else {
        $skuList = [];
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $skuList);
    echo json_encode($jsonData);
}

function AJAX_createSku()
{

    $db = pdo_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);

    $skuName = UTIL_GetString('skuName', "");
    $skuRef = UTIL_GetString('skuRef', "");
    $skuDesc = UTIL_GetString('skuDesc', "");
    $skuType = UTIL_GetInteger('skuType', 0);
    $skuReminder = UTIL_GetString('reminder', "");
    $licenseCount = UTIL_GetInteger('licenseCount', 0);
    $skuPrice = UTIL_GetString('price', "");
    $platformPrice = UTIL_GetString('platformPrice', "");
    $validity = UTIL_GetString('validity', "");
    $lang = UTIL_GetString('language', "");

    $result = ADMN_createSku($db, $skuName, $skuRef, $skuDesc, $skuPrice, $skuReminder, $skuType, $licenseCount, $lang, $validity, $platformPrice);

    echo json_encode($result);
}

function AJAX_editSkuDetails()
{

    $db = pdo_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);
    $id = UTIL_GetInteger('id', 0);

    $result = ADMN_GetSkuDetails($db, $id);
    echo json_encode($result);
}

function AJAX_updateSku()
{

    $db = pdo_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);

    $skuName = UTIL_GetString('skuName', "");
    $skuRef = UTIL_GetString('skuRef', "");
    $skuDesc = UTIL_GetString('skuDesc', "");
    $skuType = UTIL_GetInteger('skuType', 0);
    $skuReminder = UTIL_GetString('reminder', "");
    $licenseCount = UTIL_GetInteger('licenseCount', 0);
    $skuPrice = UTIL_GetString('price', "");
    $platformPrice = UTIL_GetString('platformPrice', "");
    $validity = UTIL_GetString('validity', "");
    $lang = UTIL_GetString('language', "");
    $id = UTIL_GetInteger('id', 0);

    $result = ADMN_updateSku($db, $skuName, $skuRef, $skuDesc, $skuPrice, $skuReminder, $skuType, $licenseCount, $lang, $validity, $platformPrice);

    echo json_encode($result);
}

function AJAX_skuDetailsExport()
{

    $db = pdo_connect();
    db_change($GLOBALS['PREFIX'] . 'agent', $db);

    $result = ADMN_GetSkuList($db);
    EXPORT_skuList($result);
}

function AJAX_get_LoginDetails()
{

    $db = pdo_connect();
    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;
    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;
    $notifSearch = url::postToText('notifSearch');

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        if ($orderVal != 'note') {
            $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
        } else {
            $orderStr = 'order by loginTime desc';
        }
    } else {
        // $orderStr = 'order by machine desc,ndate desc,count desc,nocStatus desc';
        $orderStr = 'order by loginTime desc';
    }

    $result = LOG_getLoginDetails($db, $limitCount, $curPage, $limitStart, $limitEnd, $notifSearch, $orderStr);
    // echo json_encode($result);
}

function AJAX_exportLoginDetails()
{

    $db = pdo_connect();

    LOG_exportLoginDetails($db);
}

function AJAX_get_LoginRangeDetails()
{

    $db = pdo_connect();
    $fromDate = UTIL_GetString('from', '');

    $toDate = UTIL_GetString('to', '');

    $level = UTIL_GetString('level', '');

    $result = LOG_getLoginRangeDetails($db, $fromDate, $toDate, $level);
    echo json_encode($result);
}

function AJAX_export_LoginRangeDetails()
{

    $db = pdo_connect();
    $fromDate = UTIL_GetString('from', '');
    $toDate = UTIL_GetString('to', '');
    $level = UTIL_GetString('level', '');
    $leveltype = UTIL_GetString('leveltype', '');
    $sublistval = UTIL_GetString('sublist', '');

    LOG_ExportLogDetails($db, $fromDate, $toDate, $level, $leveltype, $sublistval);
}

function AJAX_SearchMachineDetail()
{

    $pdo = pdo_connect();
    $host = UTIL_GetString('searchText', "");
    $machines = Pane_GetSelectedMachines($pdo, $host);
    echo $machines;
}

function ADMN_NotyEnabelDisable()
{
    $db = pdo_connect();
    $id = url::requestToText('id');
    $targetChk = url::requestToText('targetId');
    if ($targetChk == 'Enabled') {
        $sql = $db->prepare("UPDATE  " . $GLOBALS['PREFIX'] . "event.Notifications SET enabled = 0 where id = ?");
        $sql->execute([$id]);
    } else {
        $sql = $db->prepare("UPDATE  " . $GLOBALS['PREFIX'] . "event.Notifications SET enabled = 1 where id = ?");
        $sql->execute([$id]);
    }
    $res = $db->lastInsertId();
    if ($res) {
        echo "success";
    } else {
        echo "failed";
    }
}

function AJAX_Audit_GridData()
{
    $db = pdo_connect();
    $user = $_SESSION['user']['username'];

    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $limitStart = $limitCount * $curPage;

    $limitEnd = $limitStart + $limitCount;
    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
    } else {
        $orderStr = 'order by time desc';
    }

    $siteArrListArr = array();
    $user_id = $_SESSION['user']['userid'];
    $loggedUserName = $_SESSION['user']['username'];

    $usrArr = getChildDetails($user_id, 'username');

    $usrArrData = array_merge([$loggedUserName], $usrArr);
    $grouplist = $_SESSION['user']['group_list'];
    $siteAccessList = $_SESSION['user']['site_list'];
    foreach ($grouplist as $key => $val) {
        array_push($siteAccessList, $key);
    }
    foreach ($siteAccessList as $value) {
        array_push($siteArrListArr, $value);
    }
    $in = str_repeat('?,', safe_count($siteArrListArr) - 1) . '?';
    $in2 = str_repeat('?,', safe_count($usrArrData) - 1) . '?';

    $last24hrs = strtotime('-30 Days');
    // $last24hrs = strtotime('-6 month');

    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where site IN ($in) and user in ($in2) and time >= ? $orderStr $limitStr");
    $params = array_merge($siteArrListArr, $usrArrData, [$last24hrs]);
    $sql->execute($params);
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.Audit where site IN ($in) and user in ($in2) and time >= ? $orderStr $limitStr");
    $sql2->execute($params);
    $totCount = safe_count($sql2->fetchAll(PDO::FETCH_ASSOC));

    if (safe_sizeof($res) == 0) {
        $dataArr['largeDataPaginationHtml'] = '';
        $dataArr['html'] = '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
        $dataArr['html'] = FORMAT_ADMN_AuditGrid($res, $db);
        echo json_encode($dataArr);
    }
}

function AJAX_Export_DartAudit()
{
    $res = checkModulePrivilege('dartexport', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }
    $conn = pdo_connect();
    $user = $_SESSION['user']['username'];
    $fromDate = UTIL_GetString('from', '');
    $toDate = UTIL_GetString('to', '');
    $level = UTIL_GetString('type', '');
    $sublist = UTIL_GetString('sublist', '');

    ADMN_ExportAudit($conn, $fromDate, $toDate, $level, $sublist);
}

function AJAX_GetExcelSheetObject($headers, $width)
{
    try {
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        foreach ($headers as $key => $value) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($key)->setWidth($width);
            $objPHPExcel->getActiveSheet()->setCellValue($key . '1', $value);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}

function AJAX_CreateDartAuditExcelSheet($objPHPExcel, $resultArray)
{
    try {
        $index = 2;
        foreach ($resultArray as $key => $value) {
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['time'], "m/d/Y h:i A");
            } else {
                $userLoggedTime = date("m/d/Y h:i A", $value['time']);
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $userLoggedTime);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['user']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, strip_tags($value['detail']));
            $index++;
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function Ajax_audit_Data()
{
    $res = checkModulePrivilege('dartdetailview', 2);
    if (!$res) {
        echo 'Permission denied';
        exit();
    }
    $db = pdo_connect();
    $auditData = [];
    $id = url::requestToText('auditId');
    $sql = $db->prepare("SELECT time,user,detail FROM " . $GLOBALS['PREFIX'] . "audit.Audit where auditid = ?");
    $sql->execute([$id]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);
    if ($res) {
        $time = date('m/d/y h:i A', $res['time']);
        $user = $res['user'];
        $detail = $res['detail'];
        $auditData = array("time" => $time, "user" => $user, "detail" => $detail);
    }
    if (safe_count($res)) {
        echo json_encode($auditData);
    } else {
        echo 'failed';
    }
}

function AJAX_exportsite()
{
    $db = pdo_connect();
    $searchType = $_SESSION['searchType'];
    if ($searchType == 'Sites') {
        $siteName = $_SESSION['searchValue'];
        $res = ADMN_GetMachineData_export($db, $siteName);
        return $res;
    } else if ($searchType == 'Groups') {
        $searchVal = $_SESSION['searchValue'];
        $groupname = $_SESSION['rparentName'];
        $res = ADMN_GetGroupMachineData_export($db, $searchVal, $groupname);
        return $res;
    }
}

function returnSearchType()
{
    if (!isset($_SESSION)) {
    }

    $searchType = isset($_SESSION["searchType"]) ? $_SESSION["searchType"] : '';

    exit(trim($searchType));
}

function AJAX_GetGroupMachineList()
{
    $pdo = pdo_connect();

    $searchVal = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $groupname = $_SESSION['rparentName'];
    create_auditLog('Devices', 'View', 'Success');

    if ($searchType == 'Sites') {
        $res = ADMN_GetMachineData($pdo, $searchVal, '', '');
    } else if ($searchType == 'Groups') {
        $res = ADMN_GetMachinesAndGroups($pdo, $searchVal, $groupname);
    }

    echo json_encode($res);
}

function AJAX_GetGroupInfo()
{
    $pdo = pdo_connect();

    $searchVal = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $groupname = $_SESSION['rparentName'];

    $res = ADMN_GetGroupsInfo($pdo, $searchVal, $groupname);

    echo json_encode($res);
}

function Ajax_DeploymentSubnetDetails()
{
    $db = pdo_connect();
    $key = '';
    $selectedMask = url::requestToText('selectedmassk');
    $selectedSite = url::requestToText('selectedSite');

    $dep_audit_Details = DEPL_DeployDeleteSubnet($db, $key, $selectedMask, $selectedSite);

    echo $dep_audit_Details;
}

function AJAX_get_UserDetails()
{

    $db = pdo_connect();

    $res = getUSerDetailList($db);
    echo $res;
}

function AJAX_get_CustomerDetails()
{

    $db = pdo_connect();

    $res = getDetailCustomerList($db);
    echo $res;
}

function AJAX_get_UserDartDetails()
{

    $db = pdo_connect();

    $res = getUSerDartDetailList($db);
    echo $res;
}
