<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';

nhRole::dieIfnoRoles(['ticketingwiz']); // roles: ticketingwiz

//Replace $routes['post'] with if else
if (url::postToText('function') === 'configureCRMDetails') { // roles: ticketingwiz
    configureCRMDetails();
} else if (url::postToText('function') === 'configureJsonPayload') { //roles: ticketingwiz
    configureJsonPayload();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'getTicketEventDetails') { //roles: ticketingwiz
    getTicketEventDetails();
} else if (url::postToText('function') === 'getTicketSiteDetails') { //roles: ticketingwiz
    getTicketSiteDetails();
} else if (url::postToText('function') === 'getConfiguredCrmData') { //roles: ticketingwiz
    getConfiguredCrmData();
} else if (url::postToText('function') === 'getPayloadData') { //roles: ticketingwiz
    getPayloadData();
}




function getEnabledTicketTypes($pdo, $siteName)
{

    $stmt = $pdo->prepare("select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where tcktcreation = ? and siteName = ? ");
    $stmt->execute(['enabled', $siteName]);
    $crmdata = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($crmdata) {
        $crmdetail = [];
        if ($crmdata['autoheal'] == '1') {
            $crmdetail[] = '1';
        }
        if ($crmdata['notification'] == '1') {
            $crmdetail[] = '2';
        }
        if ($crmdata['selfhelp'] == '1') {
            $crmdetail[] = '3';
        }
        if ($crmdata['schedule'] == '1') {
            $crmdetail[] = '4';
        }
    } else {
        $crmdetail[] = '0';
    }
    return $crmdetail;
}

function getTicketEventDetails()
{
    $pdo = pdo_connect();
    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $limitStart = $limitCount * $curPage;

    $limitEnd = $limitStart + $limitCount;
    $searchValue = $_SESSION['searchValue'];

    $siteName = $searchValue;

    $ticketTypes = getEnabledTicketTypes($pdo, $siteName);

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        if ($orderVal != 'action') {
            $orderStr = 'ORDER BY ' . $orderVal . ' ' . $sortVal;
        } else {
            $orderStr = 'ORDER BY teid DESC';
        }
    } else {
        $orderStr = 'ORDER BY teid DESC';
    }

    $notifSearch = url::postToText('notifSearch');

    if ($notifSearch != '') {
        $whereSearch = " and  (ticketSub LIKE '%" . $notifSearch . "%'
         OR machineName LIKE '%" . $notifSearch . "%'
         OR ticketId LIKE '%" . $notifSearch . "%'
         OR status LIKE '%" . $notifSearch . "%'
         OR status LIKE '%" . $notifSearch . "%')";
    } else {
        $whereSearch = '';
    }

    // $eventDate = date('Y-m-d', strtotime('-14 days'));
    $eventDate = strtotime('-14 days');
    $ticket_in = str_repeat('?,', safe_count($ticketTypes) - 1) . '?';
    // $sqlevent = "select * from  ".$GLOBALS['PREFIX']."event.ticketEvents where siteName =  ? and eventDateTime >  ?  and ticketType IN ($ticket_in) ORDER BY teid DESC   LIMIT $limitStart,$limitEnd";
    $sqlevent = "select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where siteName =  ? and ticketType IN ($ticket_in) $whereSearch $orderStr  LIMIT $limitStart,$limitEnd";

    // $params = array_merge([$siteName, $eventDate], $ticketTypes);
    $params = array_merge([$siteName], $ticketTypes);
    $ticketdata = NanoDB::find_many($sqlevent, null, $params);
    // echo '<pre>ff'.print_r($ticketdata,1).'</pre>';
    $eventTypeArr = ['1' => 'Autoheal', '2' => 'Notification', '3' => 'Selfhelp', '4' => 'Schedule'];

    if ($ticketdata) {
        foreach ($ticketdata as $key => $value) {
            $teid = $value['teid'];
            $notifName = $value['ticketSub'];
            $eventDate = $value['eventDateTime'];
            $cronTime = ($value['crontime'] != '') ? date("Y-m-d h:i:s", $value['crontime']) : '-';
            $machineName = $value['machineName'];
            $ticketId = $value['ticketId'];
            $status = ($value['ticketType'] == 2) ? 'Open' : 'Closed';
            $eventType = $eventTypeArr[$value['ticketType']];

            $ticketDetails[] = array($notifName, $eventDate, $cronTime, $machineName, $ticketId, $status, $eventType, $teid);
        }
    } else {
        $ticketDetails = [];
    }
    // $sqlevent = "select count(*) from event.ticketEvents where siteName = ? and eventDateTime > ?  and ticketType IN ($ticket_in) ";
    $sqlevent = "select count(*) from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where siteName = ? and ticketType IN ($ticket_in) $whereSearch ";
    $ticketstmt = $pdo->prepare($sqlevent);
    $ticketstmt->execute($params);
    //  print_r($params);
    $totCount =  $ticketstmt->fetchColumn();
    $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
    $dataArr['html'] =    $ticketDetails;
    echo json_encode($dataArr);
}

function getTicketSiteDetails()
{
    $siteData = $_SESSION["user"]["site_list"];

    $siteDetails = '<option value="">-- select a customer --</option>';
    foreach ($siteData as $key => $value) {
        $siteDetails .= '<option value="' . $key . '">' . $value . '</option>';
    }

    echo $siteDetails;
}

function getConfiguredCrmData()
{
    $pdo = pdo_connect();

    $siteList = $_SESSION["user"]["site_list"];
    $siteName = url::postToText('sitename');

    if (isset($siteList[$siteName])) {

        $stmt = $pdo->prepare("select crmUrl, crmUsername, crmPassword, jsonData, jsonCloseData, tcktcreation, "
            . "autoheal, selfhelp, schedule, notification from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteName = ? limit 1");
        $stmt->execute([$siteName]);
        $crmdata = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode($crmdata);
    } else {
        echo json_encode('Permission Denied, ' . $siteName . ' not in ' . json_encode($siteList));
    }
}

function configureCRMDetails()
{
    $pdo = pdo_connect();

    $crmtype = url::postToText('crmtype');
    $customer = url::postToText('customer');
    $crmurl = url::postToText('crmurl');
    $crmusername = url::postToText('crmusername');
    $crmpassword = url::postToText('crmpassword');
    $tickEnable = url::postToText('tickEnable');
    $tickAutoheal = url::postToText('tickAutoheal');
    $tickSelfhelp = url::postToText('tickSelfhelp');
    $tickSchedule = url::postToText('tickSchedule');
    $tickNotification = url::postToText('tickNotification');

    $stmt = $pdo->prepare("select count(id) as crmcnt from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteName = ?");
    $stmt->execute([$customer]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    $tickEnableData = ($tickEnable == '1') ? 'enabled' : 'disabled';

    if ($data['crmcnt'] > 0) {
        $siteList = $_SESSION["user"]["site_list"];
        if (in_array($customer, $siteList)) {
            $updtsql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set crmType = ?, crmUrl = ?, crmUsername = ?, crmPassword = ?, "
                . "tcktcreation = ?, autoheal = ?, selfhelp = ?, schedule = ?, notification = ? where siteName = ?";
            $updtstmt = $pdo->prepare($updtsql);
            $params = array_merge([
                $crmtype, $crmurl, $crmusername, $crmpassword, $tickEnableData, $tickAutoheal, $tickSelfhelp,
                $tickSchedule, $tickNotification, $customer
            ]);
            $updtres = $updtstmt->execute($params);
            if ($updtres) {
                $rdata = ['type' => 'update', 'rmsg' => 'success'];
            } else {
                $rdata = ['type' => 'update', 'rmsg' => 'failed'];
            }
        } else {
            $rdata = ['type' => 'update', 'rmsg' => 'noaccess'];
        }
    } else {
        $siteList = $_SESSION["user"]["site_list"];
        if (in_array($customer, $siteList)) {
            $instsql = "insert into  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure (siteName, crmType, crmUrl, crmUsername, crmPassword, "
                . "tcktcreation, autoheal, selfhelp, schedule, notification) values (?,?,?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($instsql);
            $params = array_merge([
                $customer, $crmtype, $crmurl, $crmusername, $crmpassword, $tickEnableData, $tickAutoheal,
                $tickSelfhelp, $tickSchedule, $tickNotification
            ]);
            $stmt->execute($params);

            $instid = $pdo->lastInsertId();

            if ($instid) {
                $rdata = ['type' => 'insert', 'rmsg' => 'success'];
            } else {
                $rdata = ['type' => 'insert', 'rmsg' => 'failed'];
            }
        } else {
            $rdata = ['type' => 'insert', 'rmsg' => 'noaccess'];
        }
    }

    echo json_encode($rdata);
}

function configureJsonPayload()
{
    $pdo = pdo_connect();

    $customer = url::postToAny('customer');
    $createjson = url::postToAny('createjson');
    $closedjson = url::postToAny('closedjson');

    $updtsql = "update  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure set jsonData = ?, jsonCloseData = ? where siteName = ?";
    $updtstmt = $pdo->prepare($updtsql);
    $updtres = $updtstmt->execute([$createjson, $closedjson, $customer]);

    if ($updtres) {
        $rdata = ['type' => 'json_update', 'rmsg' => 'success'];
    } else {
        $rdata = ['type' => 'json_update', 'rmsg' => 'failed'];
    }

    echo json_encode($rdata);
}


function getPayloadData()
{
    $pdo = pdo_connect();

    $type = url::getToAny('type');
    $siteName = $_SESSION['searchValue'];

    $stmt = $pdo->prepare("select jsonData, jsonCloseData from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure where siteName = ?");
    $stmt->execute([$siteName]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $rdata = ['rmsg' => 'success', 'data' => $data];
    } else {
        $rdata = ['rmsg' => 'failed', 'data' => 'No Configuration Found'];
    }

    echo json_encode($rdata);
}
