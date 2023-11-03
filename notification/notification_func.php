<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-elastic.php';
include_once '../lib/l-profileAPI.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
require_once '../lib/l-db.php';
require_once '../lib/l-util.php';
require_once '../lib/l-dashboard.php';
include_once '../lib/l-setTimeZone.php';
//file_put_contents($absDocRoot. 'notification/post.txt',print_r($_POST,1));

nhRole::dieIfnoRoles(['notification']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'getTopNotification') { //roles: notification
    getTopNotification();
} else if (url::postToText('function') === 'get_notifications') { //roles: notification
    get_notifications();
} else if (url::postToText('function') === 'get_notificationDtl') { //roles: notification
    get_notificationDtl();
} else if (url::postToText('function') === 'getNotes') { //roles: notification
    getNotes();
} else if (url::postToText('function') === 'updateNote') { //roles: notification
    updateNote();
} else if (url::postToText('function') === 'get_notificationSoln') { //roles: notification
    get_notificationSoln();
} else if (url::postToText('function') === 'get_notificationsEvents') { //roles: notification
    get_notificationsEvents();
} else if (url::postToText('function') === 'get_notificationSolnIntre') { //roles: notification
    get_notificationSolnIntre();
} else if (url::postToText('function') === 'updateNocStatus') { //roles: notification
    updateNocStatus();
} else if (url::postToText('function') === 'get_notificationSolnDtl') { //roles: notification
    get_notificationSolnDtl();
} else if (url::postToText('function') === 'AddRemoteJobs') { //roles: notification
    Add_RemoteJobs();
} else if (url::postToText('function') === 'AddAndroidJobs') { //roles: notification
    Add_AndroidJobs();
} else if (url::postToText('function') === 'notify_getprofile') { //roles: notification
    notify_getprofile();
} else if (url::postToText('function') === 'updateSoln') { //roles: notification
    updateSoln();
} else if (url::postToText('function') === 'updateNotifName') { //roles: notification
    updateNotifName();
} else if (url::postToText('function') === 'showNearbyEvents') { //roles: notification
    showNearbyEvents();
} else if (url::postToText('function') === 'showEventDetails') { //roles: notification
    showEventDetails();
} else if ($_POST['function'] === 'updateEventTime') { //roles: notification
    updateEventTime();
} else if ($_POST['function'] === 'checkEventSession') { //roles: notification
    checkEventSession();
}

//Replace $routes['get'] with if else
if (url::getToText('function') === 'exportNotificationselected') { //roles: notification
    exportNotificationselected();
}

function updateNotifName()
{
    $nocName = url::requestToText('name');
    $_SESSION['selectedNotif'] = $nocName;
    echo $_SESSION['selectedNotif'];
}

function checkElasticValue()
{
    $pdo = pdo_connect();
    $sql = $pdo->prepare("SELECT value FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE NAME = 'elast_config'");
    $sql->execute();
    $res = $sql->fetch();
    $res = $res['value'];
    if ($res == "1") {
        $result = "1";
    } else {
        $result = "0";
    }
    return $result;
}

function getDefaultESHeaders()
{
    global $elastic_username;
    global $elastic_password;

    $headers = array();
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

    return $headers;
}

function get_notifications()
{
    $user = $_SESSION['user']['username'];
    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];
    $priority = url::issetInPost('priority') ? url::postToAny('priority') : '';
    $ntype = url::issetInPost('ntype') ? url::postToAny('ntype') : '';

    if($ntype == ''){
        $ntype = [];
    }

    $notify_res = Notify_getNotificationsMysql($user, $searchType, $searchVal, $priority, $ntype);
    echo $notify_res;
}

function Notify_getNotificationsMysql($user, $searchType, $searchVal, $priority, $ntype)
{

    $pdo = pdo_connect();

    if ($searchType == 'Sites') {
        $where = "C.site = '" . str_replace('/', '', $searchVal) . "'";
        // $where = "site = '" . str_replace('/', '', $searchVal) . "' and machine IN (select host from core.Census where site = '" . str_replace('/', '', $searchVal) . "')";
    } else if ($searchType == 'ServiceTag') {
        $where = "C.machine = '" . str_replace('/', '', $searchVal) . "'";
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, str_replace('/', '', $searchVal), $searchType);
        $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
        $whereArray = [];
        foreach ($machines as $row) {
            $whereArray[] = '"' . $row . '"';
        }

        $where = ' C.machine IN (' . implode(",", $whereArray) . ')';
    }

    if ($priority == '') {
        $priorityCond = '';
    } else {
        $priorityCond = ' and C.priority IN (' . implode(',', $priority) . ')';
    }

    $filterItems = getFilterType($ntype);

    if ($ntype == '' || empty($ntype)) {
        $nTypeCond = '';
    } else {
        $nTypeCond = ' and C.ntype IN (' . implode(',', $filterItems) . ')';
    }

    $nidOut = '';

    $q = "select * from " . $GLOBALS['PREFIX'] . "event.Notifications N join " . $GLOBALS['PREFIX'] . "event.Console C on N.name = C.name
    and N.id = C.nid where  " . $where . "  and C.status = 4 $priorityCond $nTypeCond group by N.name";

    logs::log(__FILE__, __LINE__, "Notify_getNotificationsMysql:" . $q, 0);

    $data = NanoDB::find_many($q, null, []);

    $recordList = '';
    $i = 0;
    foreach ($data as $notify) {
        $name = $notify['name'];

        $nID = $notify['nid'];
        $onclickval = "notificationDtl_datatable('','" . $name . "', this)";
        if ($i == 0) {
            if (isset($_SESSION['backtoNotify'])) {
                unset($_SESSION['backtoNotify']);
                $nidOut = $_SESSION['selectednid'];
            } else {
                $nidOut = $name;
            }
            $recordList .= '<div class="notif-padding active" onclick="' . $onclickval . '">' . $name . '</div>';
        } else {
            $recordList .= '<div class="notif-padding" onclick="' . $onclickval . '">' . $name . '</div>';
        }
        $i++;
    }
    $_SESSION['firstNid'] = $nidOut;
    $_SESSION['selectednid'] = $nidOut;
    $_SESSION['notifyNid'] = $nID;
    return $recordList . '##' . $nidOut;
}

function Notify_getNotifications($user, $searchType, $searchVal)
{
    $recordList = '';
    $user = $_SESSION['user']['username'];
    $rparentName = $_SESSION['rparentName'];
    $fromdate = strtotime('-14 days');
    $todate = time();
    $indexName = 'notification';
    $pdo = pdo_connect();
    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $con = '';
    if ($searchType == 'Sites') {
        $con = '{
                        "term": {
                          "site.keyword": "' . $searchVal . '"
                        }
                      }';
        $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
    } else if ($searchType == 'ServiceTag') {
        if (isset($_SESSION['passlevel']) && $_SESSION['passlevel'] == 'Groups') {
            $con = '{
                        "term": {
                          "machine.keyword": "' . $searchVal . '"
                        }
                      }';

            $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
        } else {
            $con = ' {
                        "term": {
                          "site.keyword": "' . $rparentName . '"
                        }
                      },
                        {
                        "term": {
                          "machine.keyword": "' . $searchVal . '"
                        }
                      }';
            $con1 = '"bool": {
                          "minimum_should_match": 2,
                          "should": [' . $con . ']';
        }
    } else {
        $dataScope = UTIL_GetSiteScope($pdo, $searchVal, $searchType);

        $machines = DASH_GetGroupsMachines('', $pdo, $dataScope);

        $sitefilter = '';
        foreach ($machines as $row) {
            $sitefilter .= '{ "term": { "machine.keyword": "' . $row . '" }},';
        }
        $con = rtrim($sitefilter, ',');
        $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
    }

    $query = '{
                "query": {
                  "bool": {
                    "must": [
                    {
                        ' . $con1 . '
                        }
                      },
                      {
                        "match": {
                          "nhtype.keyword": "Notification"
                        }
                      }

                    ]
                  }
                },
                "aggs": {
                  "id1_count": {
                    "terms": {
                      "field": "' . $field . '",
                      "size" : 10000
                    }
                  }
                }
              }';

    $requestHeader = getDefaultESHeaders();
    $result = EL_GetCurl($indexName, $query, $requestHeader);
    $res = safe_json_decode($result, true);
    $aggr = $res["aggregations"]['id1_count']['buckets'];
    $i = 0;
    foreach ($aggr as $key => $val) {
        $name = $val['key'];
        $cnt = $val['doc_count'];

        if ($i == 0) {
            if (isset($_SESSION['backtoNotify'])) {
                unset($_SESSION['backtoNotify']);
                $nidOut = $_SESSION['selectednid'];
            } else {
                $nidOut = $name;
            }
        }

        $onclickval = "notificationDtl_datatable('" . $name . "')";
        if ($i == 0) {
            $recordList .= '<li onclick="' . $onclickval . '" class="' . $name . ' active" ><a href="javascript:void(0)" id="' . $name . '" title="' . $name . '">' . $name . '</a></li>';
        } else {
            $recordList .= '<li onclick="' . $onclickval . '" class="' . $name . '"><a href="javascript:void(0)" id="' . $name . '" title="' . $name . '">' . $name . '</a></li>';
        }
        $i++;
    }
    $_SESSION['firstNid'] = $nidOut;
    $_SESSION['selectednid'] = $nidOut;
    return $recordList . '##' . $nidOut;
}

function getFilterType($items)
{
    $typearray = array();
    foreach ($items as $item) {
        if ($item == 'Availability') {
            array_push($typearray, 1);
        }
        if ($item == 'Security') {
            array_push($typearray, 2);
        }
        if ($item == 'Resource') {
            array_push($typearray, 3);
        }
        if ($item == 'Maintenance') {
            array_push($typearray, 4);
        }
        if ($item == 'Events') {
            array_push($typearray, 5);
        }
    }
    return $typearray;
}

function get_notificationDtl()
{
    $pdo =   NanoDB::connect();
    $nocName = url::requestToText('name');
    //$nofStatus = url::issetInPost('status') ? url::requestToStringAz09('status') : '';
    // $priority = url::issetInPost('priority') ? url::requestToStringAz09('priority') : '';
    // $ntype = url::issetInPost('ntype') ? url::requestToStringAz09('ntype') : '';

    $nofStatus = url::issetInPost('status') ? url::postToAny('status') : '';
    $priority = url::issetInPost('priority') ? url::postToAny('priority') : '';
    $ntype = url::issetInPost('ntype') ? url::postToAny('ntype') : '';

    if($ntype == ''){
        $ntype = [];
    }

    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        if ($orderVal != 'note') {
            $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
        } else {
            $orderStr = 'order by this_run desc';
        }
    } else {
        // $orderStr = 'order by machine desc,ndate desc,count desc,nocStatus desc';
        $orderStr = 'order by this_run desc';
    }

    $limitStart = $limitCount * $curPage;

    $limitEnd = $limitStart + $limitCount;


    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];


    if ($searchType == 'Sites') {
        $where = ' site  = "' . str_replace('/', '', $searchVal) . '"';
    } else if ($searchType == 'ServiceTag') {
        $where = "machine = '" . str_replace('/', '', $searchVal) . "'";
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, str_replace('/', '', $searchVal), $searchType);
        $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
        $whereArray = [];
        foreach ($machines as $row) {
            $whereArray[] = '"' . $row . '"';
        }

        $where = ' machine IN (' . implode(",", $whereArray) . ')';
    }
    $notifSearch = url::postToText('notifSearch');
    if ($notifSearch != '') {
        if (strtolower($notifSearch) == 'new') {
            $whereSearch = " and  (machine LIKE '%" . $notifSearch . "%'
            OR nocStatus  IS NULL)";
        } else {
            $whereSearch = " and  (machine LIKE '%" . $notifSearch . "%'
            OR nocStatus  LIKE '%" . $notifSearch . "%')";
        }
    } else {
        $whereSearch = '';
    }

    if ($priority != '') {
        $priorityStr = 'and priority IN (' . implode(',', $priority) . ')';
    } else {
        $priorityStr = '';
    }

    $filterItems = getFilterType($ntype);

    if ($ntype != '' && !empty($ntype)) {
        $nTypeStr = 'and ntype IN (' . implode(',', $filterItems) . ')';
    } else {
        $nTypeStr = '';
    }

    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }
    //Check Nid
    // $qGet =  "select id from event.Notifications where name = '".$nocName."'";
    // print_r($qGet);exit;
    // echo $nocName;exit;
    $nIDdata  = NanoDB::find_one("select id from " . $GLOBALS['PREFIX'] . "event.Notifications where name = ?", [$nocName]);
    $nId = $nIDdata['id'] ?: null;
    $statusArr = array();
    $data = [];
    if ($nofStatus != '') {
        $str = '';
        foreach ($nofStatus as $key => $val) {
            if ($val != 'New') {
                array_push($statusArr, $val);
            }
            if ($val == 'New') {
                $str = ' or nocStatus is NULL';
            }
        }
        
        if (safe_count($statusArr) > 0) {
            $in = str_repeat('?,', safe_count($statusArr) - 1) . '?';

            $q2 = "select machine,name, site,id,nocStatus, comment as note,count(machine) as count,  date(FROM_UNIXTIME(this_run)) as ndate, this_run ,actionNote
            from " . $GLOBALS['PREFIX'] . "event.Console where name = ? and nid = ? and status = 4  and nocStatus in ($in) $str
            $priorityStr $nTypeStr
            and " . $where . "  $whereSearch group by ndate, machine  $orderStr $limitStr";
            logs::log(__FILE__, __LINE__, "SQL " . __FILE__ . ":" . __LINE__ . ": $q2 ");
            $data = NanoDB::find_many($q2, array_merge([$nocName, $nId], $statusArr));
        } else {
            $q2 = "select machine,name, site,id,nocStatus, comment as note,count(machine) as count,  date(FROM_UNIXTIME(this_run)) as ndate ,this_run ,actionNote
            from " . $GLOBALS['PREFIX'] . "event.Console where name = ? and nid = ? and status = 4  and nocStatus is NULL
            $priorityStr $nTypeStr
            and " . $where . "  $whereSearch  group by ndate, machine  $orderStr $limitStr";
            logs::log(__FILE__, __LINE__, "SQL " . __FILE__ . ":" . __LINE__ . ": $q2 ");
            $data = NanoDB::find_many($q2, [$nocName, $nId]);
        }
    } else {
        $q2 = "select machine,name, site,id,nocStatus, comment as note,count(machine) as count,  date(FROM_UNIXTIME(this_run)) as ndate, this_run ,actionNote
        from " . $GLOBALS['PREFIX'] . "event.Console where name = ? and  nid = ? and status  = 4
        and " . $where . "  $whereSearch group by ndate, machine  $orderStr $limitStr";

        logs::log(__FILE__, __LINE__, "SQL " . __FILE__ . ":" . __LINE__ . ": $q2 ");
        $data = NanoDB::find_many($q2, [$nocName, $nId]);
    }
    //GROUP BY machine, servertime, name

    $q3 = "select count(*) as count from (select   FROM_UNIXTIME(this_run, '%d-%m-%Y') as ndate
        from " . $GLOBALS['PREFIX'] . "event.Console where name = ? and  nid = ? and status  = 4
        and " . $where . "  $whereSearch group by ndate, machine  ) as a";
    logs::log(__FILE__, __LINE__, "SQL4 " . __FILE__ . ":" . __LINE__ . ": $q3 ");

    $totCount = NanoDB::find_one($q3, [$nocName, $nId]);
    $totCount = $totCount['count'];

    if (safe_sizeof($data) == 0) {
        $dataArr['largeDataPaginationHtml'] = '';
        $dataArr['html'] = '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
        $dataArr['html'] = Format_notificationDataMysql($data, $nofStatus);
        echo json_encode($dataArr);
    }
}

function Format_notificationDataMysql($result, $notifStatus)
{
    $recordList = [];
    $i = 0;
    foreach ($result as $lastMachine) {
        $id = $lastMachine['id'];
        $cust = $lastMachine['site'];
        $machine = $lastMachine['machine'];
        $name = $lastMachine['name'];
        $last_run = $lastMachine['this_run'];
        $tid = $id . "~~" . $name . "~~" . $cust . "~~" . $machine . "~~" . $lastMachine['this_run'];
        $allStatuses = array_column($lastMachine, 'nocStatus');

        if (isset($lastMachine['nocStatus'])) {
            $status = $lastMachine['nocStatus'];
        } else if (isset($lastMachine['nocStatus'])) {
            $status = $lastMachine['nocStatus'];
        } else {
            $status = false;
        }

        if (in_array('default', $allStatuses)) {
            $status = '<p style="color:red;">New</p>';
            $statCheck = "New";
        } else if ($status && $status == 'Completed') {
            $status = '<p style="color:green;">Completed</p>';
            $statCheck = "Completed";
        } else if ($status && $status == 'Fixed') {
            $status = '<p style="color:orange;">Pending</p>';
            $statCheck = "Pending";
        } else if ($status && $status != 'Completed' && $status != 'default' && $status != null) {
            $status = '<p style="color:#FF8C00;">Actioned</p>';
            $statCheck = "Actioned";
        } else {
            $status = '<p style="color:red;">New</p>';
            $statCheck = "New";
        }

        if ($statCheck == 'Actioned' || $statCheck == 'Completed') {
            $recordList[$i][] = '<div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input notifychk" id="' . $id . '" name="checkNoc" value="' . $tid . '" onclick="uniqueCheckBox(\'' . $statCheck . '\');" type="checkbox">
                            </label>
                        </div>';
        } else {
            $recordList[$i][] = '<div class="form-check">
                            <label class="form-check-label">
                                <input class="form-check-input notifychk" id="' . $id . '" name="checkNoc" value="' . $tid . '" onclick="uniqueCheckBox(\'' . $statCheck . '\');" type="checkbox">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>';
        }

        $recordList[$i][] = $lastMachine['machine'];

        $recordList[$i][] = $lastMachine['ndate'];

        $recordList[$i][] = $lastMachine['count'];

        $recordList[$i][] = $status;

        $addNotes = "addNotes('" . $name . "','" . $cust . "','" . $machine . "',$last_run)";
        if ($lastMachine['actionNote'] == '') {
            $recordList[$i][] = '<p onclick="' . $addNotes . '" style="cursor: pointer;text-decoration: none;color:#0096D6;" title="Add Note">Add</p>';
        } else {
            $recordList[$i][] = '<p onclick="' . $addNotes . '" style="cursor: pointer;text-decoration: none;color:#0096D6;" title="View/Edit Note">View/Edit</p>';
        }

        // $recordList[$i][] = (trim($lastMachine['actionNote']) == '')? '-': trim($lastMachine['note']);
        $recordList[$i][] = $id;
        $i++;
    }
    return $recordList;
}

function get_notificationSoln()
{

    $name = url::requestToText('nid');
    $pdo = pdo_connect();

    $sql = $pdo->prepare("SELECT  mid,notifyName,solution FROM " . $GLOBALS['PREFIX'] . "dashboard.notifySol WHERE notifyName=?");
    $sql->execute([$name]);
    $res = $sql->fetchAll();
    $str = '';

    if (safe_count($res) > 0) {
        foreach ($res as $value) {

            $id = $value['mid'];
            $desc = $value['solution'];
            $name = explode('##', $desc)[2];

            $str .= '<div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="profilename" value="' . $desc . '">
                            <span class="form-check-sign"></span>
                            ' . $name . '
                        </label>
                    </div>';
        }
    } else {
        $str = '<div style="font-size: 10px;">
            No Suggested fix
           </div>';
    }
    $auditRes = create_auditLog('Notification', 'Action Taken', 'Success', null, $name);

    echo $str;
}

function get_notificationsEvents()
{
    $pdo = pdo_connect();

    $notifList = url::postToArrayWithException('notifdetlist', '~');
    $idArr = array(-1);

    foreach ($notifList as $k => $v) {
        if ($v != '') {
            $notifList = explode("~~", $v);

            $NotifName = $notifList[1];
            $SiteName = $notifList[2];
            $MachineName = $notifList[3];
            $time = (int) $notifList[4];

            // $params = array_merge([$SiteName, $MachineName, $NotifName]); 
            $notifyData = NanoDB::find_many('select id from ' . $GLOBALS['PREFIX'] . 'event.Console where site=? and machine=? and name=? and status = 4 and FROM_UNIXTIME(this_run, "%d-%m-%Y")=FROM_UNIXTIME(' . $time . ', "%d-%m-%Y")', null, [
                $SiteName, $MachineName, $NotifName
            ]);
            foreach ($notifyData as $key => $val) {
                $idArr[] = $val['id'];
            }
        }
    }

    $notifyData = NanoDB::find_many('select id,nid,machine,servertime,this_run,count,nocStatus,name,site,'
        . 'actionNote as note, event_list, eventDetails from ' . $GLOBALS['PREFIX'] . 'event.Console where id IN (' . implode(",", $idArr) . ')', null, []);

    foreach ($notifyData as $value) {
        $_SESSION['eventDetails'] = $value['eventDetails'];

        $eventDetailsObj = safe_json_decode($value['eventDetails'], true);

        // $eventData = trim(stripslashes($value['event_list']), '"');
        // // $eventData = explode(',',$eventData);
        // $eventData = safe_json_decode($eventData, true);
        // $eventList = $eventData[0]; // safe_json_decode($eventData[0], TRUE);
        // $eventID = $eventData[1]; //safe_json_decode($eventData[1], TRUE);
        // $eventID = explode(':', $eventID[0]);
        // $eID = $eventID[1];
        // foreach ($eventList as $eventVal) {
        $machineName = '<p title="' . $value['machine'] . '">' . $value['machine'] . '</p>';
        $clientTime = '<span class="date-overflow" title=\'' . date("Y/m/d h:i A", $value['this_run']) . '\'>' . date("Y/m/d h:i A", $value['this_run']) . '</span>';
        $serverTime = '<span class="date-overflow" title=\'' . date("Y/m/d h:i A", $value['servertime']) . '\'>' . date("Y/m/d h:i A", $value['servertime']) . '</span>';
        $eventDetails = '<img data-qa="get_notificationsEvents_showNearbyEvents" class="mr-2 cursorPointer" title="Show Nearby Events" onclick="showNearbyEvents(\'' . $value['machine'] . '\')" src="../assets/img/analyse.png" style="width: 22px;border-radius: 0;float:right;opacity: 0.5;">
                                <img  data-qa="get_notificationsEvents_showMoreDetails" class="mr-1 cursorPointer" title="More Details" onclick=\'showMoreDetails(' . json_encode($eventDetailsObj) . ')\' src="../assets/img/list.png" style="width: 18px;border-radius: 0;float:right;opacity: 0.5;">';

        $recordList[] = array($machineName, $clientTime, $serverTime, $eventDetails);
        // }
    }
    //Create audit log for notification events view
    create_auditLog('Notification', 'Viewed Notification Details', 'Success');
    echo json_encode($recordList);
}

function exportNotificationselected()
{
    $eventTime = url::requestToText('eventDt');
    $nocName = url::requestToText('name');
    $date = date('Y-m-d', strtotime('-14 days'));
    $todate = time();
    $fromdate = strtotime($date);

    $result = getNotificationDataExportMysql($nocName, $fromdate, $todate);

    $index = 2;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Notification');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Device');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Date');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Count');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Status');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Notes');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Solution');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Username');
    $key = '';
    $notifName = '';
    if (safe_count($result) > 0) {
        foreach ($result as $key => $val) {
            $val = (array) $val;
            $notifName = $val['name'];
            $servertime = $val['servertime'];
            $statusVal = $val['nocStatus'];
            $machineName = $val['machine'];
            $count = $val['count'];
            $notes = ($val['actionNote'] == 'default') ? '-' : $val['actionNote'];
            $solution = ($val['solution'] == 'default' || $val['solution'] == '') ? '-' : $val['solution'];
            $username = ($val['username'] == 'default') ? '-' : $val['username'];

            if ($statusVal == 'Completed') {
                $statusStr = "Completed";
            } else if ($statusVal != 'Completed' && $statusVal != 'default' && $statusVal != '' && $statusVal != null) {
                $statusStr = "Actioned";
            } else if ($statusVal == '') {
                $statusStr = "New";
            }

            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $notifName . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '="' . $machineName . '"');

            $tempservertime = $servertime;
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, PHPExcel_Shared_Date::PHPToExcel($tempservertime));
            $objPHPExcel->getActiveSheet()->getStyle('C' . $index)->getNumberFormat()->setFormatCode("mm-dd-yyyy h:mm:ss ");
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $count . '');
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $statusStr . '');
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, '' . $notes . '');
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, '' . $solution . '');
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, '' . $username . '');

            $index++;
        }
    } else {
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available');
    }

    if ($nocName == '') {
        $fn = "All's__Notications.xls";
    } else {
        $fn = $nocName . "'s__Notications.xls";
    }
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : null;
    $auditRes = create_auditLog('Notification', 'Export', 'Success', null, $gpname);
    $objWriter->save('php://output');
}

function getNotificationDetails($nocName, $sitename, $machine, $eventTime, $fromdate, $todate)
{

    $user = $_SESSION['user']['username'];
    $indexName = 'notification';
    $machines = is_array($machine) ? $machine : [$machine];
    $machinesNew = [];

    foreach ($machines as $eachMachines) {
        $machinesNew[] = '"' . $eachMachines . '"';
    }
    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $query = '{
        "_source": {
          "includes": [
            "machine",
            "site",
            "ctime",
            "nocName",
            "text2",
            "text3",
            "text1",
            "text4"
          ]
        },
        "query": {
          "bool": {
            "must": [
              {
                "match": {
                  "nhtype.keyword": "Notification"
                }
              },
              {
                "match": {
                  "site.keyword": "mokshasite"
                }
              },
              {
                "match": {
                  "' . $field . '": "Firewall_Enable"
                }
              }
            ],
            "filter": [
              {
                "range": {
                  "ctime": {
                    "gte": "' . $fromdate . '",
                    "lte": "' . $todate . '"
                  }
                }
              },
              {
                "terms": {
                  "machine.keyword": [' . implode(',', $machinesNew) . ']
                }
              }
            ]
          }
        }
      }';

    $requestHeader = getDefaultESHeaders();
    $result = EL_GetCurl($indexName, $query, $requestHeader);

    $res = safe_json_decode($result, true);
    $resArray = $res['hits']['hits'];

    return $resArray;
}

function get_notificationSolnIntre()
{
    $nid = url::requestToText('name');
    $_SESSION['selectednid'] = $nid;
    $_SESSION['notifyWindow'] = 1;
    $_SESSION['fromwindow'] = 'Notify';
    $_SESSION['notifyselArr'] = url::requestToAny('sel');
}

function getNotesold()
{
    $sitename = url::requestToText('site');
    $machine = url::requestToText('machine');
    $eventTime = url::requestToText('eventDt');
    $nocName = url::requestToText('name');
    $fromdate = $eventTime;
    $todate = $fromdate + 86400;
    $user = $_SESSION['user']['username'];
    $indexName = 'notification';
    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $query = '{
                "size" : 1,
                      "_source": {
                              "includes": ["actionNote", "site", "nocName"]
                      },
                      "query": {
                            "bool": {

                                    "must": [{
                                        "match": {
                                            "nhtype.keyword": "Notification"
                                    }
                            },
                            {
                                    "match": {
                                            "site.keyword": "' . $sitename . '"
                                    }
                            },
                            {
                                    "match": {
						"machine.keyword": "' . $machine . '"
					}
				},
				{
					"match": {
						"' . $field . '": "' . $nocName . '"
					}
				}
			]
		}
	}
}';

    $requestHeader = getDefaultESHeaders();
    $result = EL_GetCurlWithLimit($indexName, $query, $requestHeader);
    $res = safe_json_decode($result, true);
    $resArray = $res['hits']['hits'][0]['_source'];
    if ($resArray['actionNote'] == 'default') {
        echo "add";
    } else {
        echo $resArray['actionNote'];
    }
}

function updateNoteold()
{
    $sitename = url::requestToText('site');
    $machine = url::requestToText('machine');
    $eventTime = url::requestToText('eventDt');
    $nocName = url::requestToText('name');
    $note = url::requestToText('note');

    $date = date('Y-m-d 00:00:01', strtotime($eventTime));
    $fromdate = strtotime($date);
    $todate = strtotime(date('Y-m-d 23:59:00', strtotime($eventTime)));

    $indexName = 'notification';
    $user = $_SESSION['user']['username'];
    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $query = '{
            "query": {
                "bool": {

                "must": [
                    {
                      "match": {
                        "' . $field . '": "' . $nocName . '"
                      }
                    },
                    {
                        "match": {
                            "machine.keyword": "' . $machine . '"
                        }
                    }
                ]
              }
            },
            "script": {
              "inline": "ctx._source.actionNote= \"' . $note . '\";"
            }
          }';

    $requestHeader = getDefaultESHeaders();
    updateByIndex($query, $indexName, $requestHeader);

    echo "success";
}

function getNotes()
{
    $sitename = url::requestToText('site');
    $machine = url::requestToText('machine');
    $eventTime = url::requestToText('eventDt');
    $nocName = url::requestToText('name');
    $pdo = pdo_connect();
    $consoleCheck = $pdo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'event.Console where machine = ? and site = ? and this_run = ? and name = ?');
    $consoleCheck->execute([$machine, $sitename, $eventTime, $nocName]);
    $consoleRes = $consoleCheck->fetch(PDO::FETCH_ASSOC);
    if ($consoleRes['actionNote'] == '') {
        echo "add";
    } else {
        echo $consoleRes['actionNote'];
    }
}

function updateNote()
{

    $pdo = pdo_connect();
    $sitename = url::requestToText('site');
    $machine = url::requestToText('machine');
    $eventTime = url::requestToText('eventDt');
    $nocName = url::requestToText('name');
    $note = url::requestToText('note');
    // $dt = strtotime($eventTime);

    $nSql = $pdo->prepare("select id from " . $GLOBALS['PREFIX'] . "event.Notifications where name = '" . $nocName . "'");
    $nSql->execute();
    $nIDdata = $nSql->fetch(PDO::FETCH_ASSOC);
    $nId = $nIDdata['id'];

    $params = array_merge([$note, $machine, $sitename, $eventTime, $nocName, $nId]);
    $consoleCheck = $pdo->prepare('update ' . $GLOBALS['PREFIX'] . 'event.Console set actionNote = ? where machine = ? and site = ? and this_run = ? and name = ? and nid = ?');
    $consoleCheck->execute($params);
    echo "success";
}

function get_notificationSolnDtl()
{
    $db = db_connect();

    $_SESSION['notifyselArr'] = url::requestToText('notifyArr');
    $_SESSION['notifyWindow'] = 1;

    echo "success";
}

function Notify_getNotifyProfilesDtl($db, $nid, $profId)
{

    $sql = NanoDB::connect()->prepare("select nid,name,id,dartconfig,description,tileDesc,dartnum from " . $GLOBALS['PREFIX'] . "event.NotificationProfile where nid=? and id=? and enabled=? limit ?");
    $sql->execute([$nid, $profId, 1, 1]);
    $res = $sql->fetch();

    return $res;
}

function updateNocStatus()
{
    $name = url::postToTextWithException('name', '~');
    $machineDet = strip_tags(rtrim(url::postToTextWithException('machineDet', '~'), '~~~~'));

    if (!url::issetInRequest('sugg')) {
        $status = 'Actioned';
    } else {
        $status = 'Completed';
    }

    $res = updateStatusMysql($machineDet, $name, $status);
    if ($res) {
        echo "Done";
    } else {
        echo "Fail";
    }
}

function updateStatusMysql($value, $nocName, $status)
{
    $pdo = pdo_connect();
    // $user = $_SESSION['user']['username'];
    $temp = explode('~~~~', $value);
    foreach ($temp as $key => $val) {
        $values = explode('~~', $val);
        $cid = $values[0];
        // $machine = $values[2];
        // $eventTime = ltrim(rtrim($values[6], '"'), '"');
        // $date = date('Y-m-d H:i:s', $eventTime);
        // $fromdate = strtotime($date);
        // $todate = $fromdate + 86400;
        $sol = $values[7];
        // $dt = strtotime($date);
        $consoleCheck = $pdo->prepare('update ' . $GLOBALS['PREFIX'] . 'event.Console set nocStatus = ?,solutionPush = ? where id = ?');
        $consoleReslt = $consoleCheck->execute([$status, $sol, $cid]);
        if ($consoleReslt) {
            return 1;
        } else {
            return 0;
        }
    }
}

function updateStatus($value, $nocName, $status)
{

    $indexName = 'notification';
    $user = $_SESSION['user']['username'];
    $temp = explode('~~~~', $value);
    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    foreach ($temp as $key => $val) {
        $values = explode('~~', $val);
        $machine = $values[2];
        $eventTime = ltrim(rtrim($values[6], '"'), '"');
        // $date = date('Y-m-d H:i:s', $eventTime);
        // $fromdate = strtotime($date);
        // $todate = $fromdate + 86400;
        $sol = $values[7];

        $fromdate = strtotime(date('Y-m-d', $eventTime) . ' 00:00:01');
        // $todate = strtotime(date('Y-m-d', $eventTime) . ' 23:59:59');

        $query = '{
            "query": {
                "bool": {

                "must": [
                    {
                      "match": {
                        "' . $field . '": "' . $nocName . '"
                      }
                    },
                    {
                        "match": {
                            "machine.keyword": "' . $machine . '"
                        }
                    }
                ]
              }
            },
            "script": {
              "inline": "ctx._source.Status= \"' . $status . '\";ctx._source.solution= \"' . $sol . '\";ctx._source.username= \"' . $user . '\";"
            }
          }';

        $requestHeader = getDefaultESHeaders();
        $res = updateByIndex($query, $indexName, $requestHeader);
    }

    return true;
}

function getTopNotification()
{

    $user = $_SESSION['user']['username'];
    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];
    $pdo = NanoDB::connect();

    if ($searchType == 'Sites') {
        $where = '{"terms" : {"site.keyword" : ["' . $searchVal . '"]}}';
    } else if ($searchType == 'ServiceTag') {
        $where = '{"terms" : {"machine.keyword" : ["' . $searchVal . '"]}}';
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, $searchVal, $searchType);
        $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
        $whereArray = [];
        foreach ($machines as $row) {
            $whereArray[] = '"' . $row . '"';
            $where = '{"terms" : {"machine.keyword" : [' . implode(",", $whereArray) . ']}}';
        }

        $where = implode(",", $whereArray);
    }
    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $indexName = 'notification';
    $query = '{
            "size": 0,
            "query": {
              "bool": {
                "must": [
                  {
                    "match": {
                      "nhtype.keyword": "Notification"
                    }
                  }
                ],
                    "filter" : [
                    ' . $where . '
                    ]
              }
            },
            "aggs": {
              "unique_notification_name": {
                "terms": {
                  "field": "' . $field . '"
                }
              }
            },
            "sort": {
              "ctime": {
                "order": "desc"
              }
            }
          }';

    $requestHeader = getDefaultESHeaders();
    $result = postELcall($indexName, $query, $requestHeader);
    $res = safe_json_decode($result, true);
    $data = isset($res['aggregations']['unique_notification_name']['buckets']) ? $res['aggregations']['unique_notification_name']['buckets'] : [];
    $data = safe_sizeof($data) > 0 ? array_column($data, 'key') : [];

    $list = '';
    if (safe_sizeof($data) == 0) {
        $list .= '<li class="nav-link">
                                <span>No alert found</span>
                </li>';
    } else {
        foreach ($data as $key => $val) {
            $list .= '<li class="nav-link">
                                <a href="#" class="nav-item dropdown-item">New Notification : ' . $val . ' reported</a>
                    </li>';
        }
    }

    echo $list;
}

function notify_getprofile()
{


    $name = url::requestToText('name');
    $sortObject = new stdClass();
    $level = 'type';
    $sortObject->$level = "L3";

    $Res = getmgroupuniqueid();

    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    $main_arr = $arr_response["Main"];
    $sqlRes = NanoDB::find_one("SELECT mid,solution FROM " . $GLOBALS['PREFIX'] . "dashboard.notifySol where notifyName=? order by id desc limit ?", null, [$name, 1]);

    $options = "";
    if (safe_count($main_arr) > 0) {
        foreach ($main_arr as $key => $val) {
            $id = $val['mid'];
            $dart = $val['dart'];
            $variable = $val['varValue'];
            $shortDesc = $val['shortDesc'];
            $profileName = $val['profile'];
            $os = $val['OS'];

            $value = $dart . '##' . $variable . '##' . $shortDesc . '##' . $profileName . '##' . $os;

            //            if ($sqlRes['mid'] == $id) {
            $serchSelected = strpos($sqlRes['solution'], $profileName);
            if ($serchSelected) {
                $options .= "<option value='" . $value . "' id='" . $id . "' selected>$profileName</option>";
            } else {
                $options .= "<option value='" . $value . "' id='" . $id . "'>$profileName</option>";
            }
        }
    } else {
        $options .= "<option value='' id=''>No data available</option>";
    }
    create_auditLog('Notification', 'Update Solution', 'Success', null, $name);

    echo $options;
}

function getmgroupuniqueid()
{

    $pdo = pdo_connect();
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $rparentValue = $_SESSION['rparentName'];

    if ($searchType == 'Sites') {

        $sql = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$searchValue]);
        $sqlRes = $sql->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sqlRes['mgroupuniq'];
    } else if ($searchType == 'Groups') {
        $sql = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=? and style= ?");
        $sql->execute([$searchValue, $rparentValue]);
        $sqlRes = $sql->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sqlRes['mgroupuniq'];
    } else {

        $sql = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
        $sql->execute(["%$searchValue%"]);
        $sqlRes = $sql->fetch();

        $sql1 = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql1->execute([$rparentValue]);
        $sql1Res = $sql1->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sql1Res['mgroupuniq'];
    }

    return array("mgroupuniq" => $mgroupid, "parentmgroupid" => $mgroupidParent);
}

function updateSoln()
{

    //    $val = url::requestToText('val');
    $val = url::arrayToString('val');
    $name = url::requestToText('name');
    $mid = url::requestToText('mid');
    $machineName = $_SESSION['searchValue'];
    $siteName = $_SESSION['rparentName'];
    $time = time();
    $pdo = pdo_connect();

    $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "dashboard.notifySol (mid,machineName,siteName,notifyName,solution,time) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE mid=?,solution=?,time=?");
    $sql->execute([$mid, $machineName, $siteName, $name, $val, $time, $mid, $val, $time]);
    $res = $pdo->lastInsertId();

    if ($res) {
        echo "success";
    } else {
        echo "failed";
    }
}

function getNotificationExport($nocName, $fromdate, $todate)
{

    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];
    $rparentName = $_SESSION['rparentName'];
    $indexName = 'notification';
    $pdo = pdo_connect();

    $con = '';
    if ($searchType == 'Sites') {
        $con = '{
                        "term": {
                          "site.keyword": "' . $searchVal . '"
}
                      }';
        $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
    } else if ($searchType == 'ServiceTag') {
        $con = ' {
                        "term": {
                          "site.keyword": "' . $rparentName . '"
                        }
                      },{
                        "term": {
                          "machine.keyword": "' . $searchVal . '"
                        }
                      }';
        $con1 = '"bool": {
                          "minimum_should_match": 2,
                          "should": [' . $con . ']';
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, $_SESSION['searchValue'], $_SESSION['searchType']);
        $machines = DASH_GetGroupsMachines_PDO($key, $pdo, $dataScope);
        $sitefilter = '';
        foreach ($machines as $row) {
            $sitefilter .= '{ "term": { "machine.keyword": "' . $row . '" }},';
        }
        $con = rtrim($sitefilter, ',');
        $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
    }

    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $query = '{
                "query": {
                  "bool": {

                    "must": [
                        {
                        ' . $con1 . '
                        }
                      },
                      {
                        "match": {
                          "nhtype.keyword": "Notification"
                        }
                      },
                        {
                            "match": {
                              "' . $field . '": "' . $nocName . '"
                            }
                        }
                    ]
                  }
                },
                "sort" : {
                    "ctime": {"order": "desc"}
                }
              }';

    $requestHeader = getDefaultESHeaders();
    $result = EL_GetCurl($indexName, $query, $requestHeader);

    $res = safe_json_decode($result, true);
    $resArray = $res['hits']['hits'];

    $temp = array();
    foreach ($resArray as $key => $val) {
        $date = date('m/d/Y', $val['_source']['ctime']);
        $machine = $val['_source']['machine'];
        $text = $machine . $date;
        $temp[$text]['name'] = $val['_source']['nocName'];
        $temp[$text]['site'] = $val['_source']['site'];
        $temp[$text]['ctime'] = $val['_source']['ctime'];
        $temp[$text]['machine'] = $val['_source']['machine'];
        $temp[$text]['count'] += 1;
        $temp[$text]['Status'] = $val['_source']['Status'];
        $temp[$text]['actionNote'] = $val['_source']['actionNote'];
        $temp[$text]['cver'] = $val['_source']['cver'];
        $temp[$text]['id'] = $val['_source']['id'];
        $temp[$text]['scrip'] = $val['_source']['scrip'];
        $temp[$text]['solution'] = isset($val['_source']['solution']) ? $val['_source']['solution'] : '-';
        $temp[$text]['username'] = isset($val['_source']['username']) ? $val['_source']['username'] : '-';
    }
    return $temp;
}

function putFormatedDataToFile($resArray, $fileExits = true)
{
    $temp = array();
    foreach ($resArray as $key => $val) {
        $date = date('m/d/Y', $val['_source']['ctime']);
        $machine = $val['_source']['machine'];
        $text = $machine . $date;
        $temp[$text]['name'] = $val['_source']['nocName'];
        $temp[$text]['site'] = $val['_source']['site'];
        $temp[$text]['ctime'] = $val['_source']['ctime'];
        $temp[$text]['machine'] = $val['_source']['machine'];
        $temp[$text]['count'] += 1;
        $temp[$text]['Status'] = $val['_source']['Status'];
        $temp[$text]['actionNote'] = $val['_source']['actionNote'];
        $temp[$text]['cver'] = $val['_source']['cver'];
        $temp[$text]['id'] = $val['_source']['id'];
        $temp[$text]['scrip'] = $val['_source']['scrip'];
        $temp[$text]['solution'] = isset($val['_source']['solution']) ? $val['_source']['solution'] : '-';
        $temp[$text]['username'] = isset($val['_source']['username']) ? $val['_source']['username'] : '-';
    }

    if ($fileExits) {
        $inp = file_get_contents('rj_array.json');
        $tempArray = safe_json_decode($inp);
        array_push($tempArray, $temp);
    } else {
        unlink('rj_array.json');
        $tempArray = $temp;
    }
    file_put_contents('rj_array.json', json_encode($tempArray));
}

function getNotificationDataExport($nocName, $fromdate, $todate)
{

    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];
    $rparentName = $_SESSION['rparentName'];
    $indexName = 'notification';
    $pdo = pdo_connect();

    $con = '';
    if ($searchType == 'Sites') {
        $con = '{
                        "term": {
                          "site.keyword": "' . $searchVal . '"
}
                      }';
        $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
    } else if ($searchType == 'ServiceTag') {
        $con = ' {
                        "term": {
                          "site.keyword": "' . $rparentName . '"
                        }
                      },{
                        "term": {
                          "machine.keyword": "' . $searchVal . '"
                        }
                      }';
        $con1 = '"bool": {
                          "minimum_should_match": 2,
                          "should": [' . $con . ']';
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, $_SESSION['searchValue'], $_SESSION['searchType']);
        $machines = DASH_GetGroupsMachines_PDO($key, $pdo, $dataScope);
        $sitefilter = '';
        foreach ($machines as $row) {
            $sitefilter .= '{ "term": { "machine.keyword": "' . $row . '" }},';
        }
        $con = rtrim($sitefilter, ',');
        $con1 = '"bool": {
                          "minimum_should_match": 1,
                          "should": [' . $con . ']';
    }

    $result = checkElasticValue();
    if ($result == '1') {
        $field = "nocName.keyword";
    } else {
        $field = "NotificationName.keyword";
    }
    $query = '{
                "query": {
                  "bool": {

                    "must": [
                        {
                        ' . $con1 . '
                        }
                      },
                      {
                        "match": {
                          "nhtype.keyword": "Notification"
                        }
                      },
                        {
                            "match": {
                              "' . $field . '": "' . $nocName . '"
                            }
                        }
                    ]
                  }
                },
                "sort" : {
                    "ctime": {"order": "desc"}
                }
              }';

    $requestHeader = getDefaultESHeaders();
    try {
        $result = EL_GetCurlWithScroll($indexName, $query, $requestHeader);

        $result = safe_json_decode($result, true);
        $resArray = $result['hits']['hits'];

        putFormatedDataToFile($resArray, false);
        $scroll_id = isset($result['_scroll_id']) ? $result['_scroll_id'] : null;

        while ($scroll_id) {
            $result = EL_GetCurlWithScroll($indexName, $query, $requestHeader, $scroll_id);
            $result = safe_json_decode($result, true);
            $resArray = $result['hits']['hits'];
            putFormatedDataToFile($resArray);
            $scroll_id = isset($result['_scroll_id']) ? $result['_scroll_id'] : null;
        }
        return true;
    } catch (\Exception $exception) {
        logs::log(__FILE__, __LINE__, $exception->getMessage(), 3, '/var/www/html/Dashboard_V8/notification/notification.log');
        return false;
    }
}

function getNotificationDataExportMysql($nocName, $fromdate, $todate)
{

    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];
    $rparentName = $_SESSION['rparentName'];
    $pdo = pdo_connect();
    $con = '';
    if ($searchType == 'Sites') {
        $where = ' site = "' . str_replace('/', '', $searchVal) . '"';
    } else if ($searchType == 'ServiceTag') {
        $where = ' machine = "' . str_replace('/', '', $searchVal) . '"';
    }

    $notifySql = $pdo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'event.Console where name = "' . $nocName . '" and status = 4 and ' . $where);
    $notifySql->execute();
    $data = $notifySql->fetchAll(PDO::FETCH_ASSOC);

    return $data;
    // $jsonData = array();
    // if (safe_sizeof($data) == 0) {
    //     echo json_encode($jsonData);
    // } else {
    //     $jsonData = putFormatedDataToFileMysql($data);
    //     echo json_encode($jsonData);
    // }

}

function putFormatedDataToFileMysql($resArray, $fileExits = true)
{
    $temp = array();
    foreach ($resArray as $key => $val) {

        $date = date('m/d/Y', $val['this_run']);
        $machine = $val['machine'];
        $text = $machine . $date;
        $temp[$text]['name'] = $val['name'];
        $temp[$text]['site'] = $val['site'];
        $temp[$text]['ctime'] = $val['this_run'];
        $temp[$text]['machine'] = $val['machine'];
        $temp[$text]['count'] += 1;
        $temp[$text]['status'] = $val['nocStatus'];
        $temp[$text]['actionNote'] = $val['actionNote'];
        $temp[$text]['cver'] = '';
        $temp[$text]['id'] = $val['id'];
        $temp[$text]['scrip'] = '';
        $temp[$text]['solution'] = isset($val['solutionPush']) ? $val['solutionPush'] : '-';
        $temp[$text]['username'] = isset($val['username']) ? $val['username'] : '-';
    }
    return $temp;

    if ($fileExits) {
        unlink('rj_array.json');
        $inp = file_get_contents('rj_array.json');
        $tempArray = safe_json_decode($inp);
    } else {
        unlink('rj_array.json');
        $tempArray = $temp;
    }
    file_put_contents('rj_array.json', json_encode($temp));
}

/**
 * This will show all the events from event.Events table based on the 
 * Device selection and the daterange selected.
 */
function showNearbyEvents()
{
    $machineName = url::postToText('machine');
    $startDate = url::postToInt('startDate');
    $endDate = url::postToInt('endDate');

    $notifyData = NanoDB::find_many(
        'select * from ' . $GLOBALS['PREFIX'] . 'event.Events where machine = ? and servertime >= ? and servertime <= ?  LIMIT 1000',
        null,
        [$machineName, $startDate, $endDate]
    );

    $recordList = [];

    foreach ($notifyData as $value) {

        $machineName = '<p title="' . $value['machine'] . '">' . $value['machine'] . '</p>';
        $clientTime = '<span class="date-overflow" title=\'' . date("Y/m/d h:i A", $value['ctime']) . '\'>' . date("Y/m/d h:i A", $value['ctime']) . '</span>';
        $serverTime = '<span class="date-overflow" title=\'' . date("Y/m/d h:i A", $value['servertime']) . '\'>' . date("Y/m/d h:i A", $value['servertime']) . '</span>';
        $dart = '<p title="' . $value['scrip'] . '">' . $value['scrip'] . '</p>';
        $dartDesc = '<p title="' . $value['description'] . '">' . $value['description'] . '</p>';
        $eventDetails = '<img class="mr-1 cursorPointer"   data-qa="showNearbyEvents_showMoreDetails"  title="More Details" onclick=\'showMoreDetails(' . json_encode(safe_json_decode($value)) . ')\' src="../assets/img/list.png" style="width: 18px;border-radius: 0;float:right;opacity: 0.5;">';

        $recordList[] = array($clientTime, $serverTime, $dart, $dartDesc, $eventDetails);
    }
    echo json_encode($recordList);
}
