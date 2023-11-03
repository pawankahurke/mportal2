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
//file_put_contents($absDocRoot. 'notification/post.txt',print_r($_POST,1));

nhRole::dieIfnoRoles(['compliance']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'getTopNotification') { //roles: compliance
  getTopNotification();
} else if (url::postToText('function') === 'get_compliance') { //roles: compliance
  get_compliance();
} else if (url::postToText('function') === 'get_complianceDtl') { //roles: compliance
  get_complianceDtl();
} else if (url::postToText('function') === 'getNotes') { //roles: compliance
  getNotes();
} else if (url::postToText('function') === 'updateNote') { //roles: compliance
  updateNote();
} else if (url::postToText('function') === 'get_notificationSoln') { //roles: compliance
  get_notificationSoln();
} else if (url::getToText('function') === 'get_notificationsEvents') { //roles: compliance
  get_notificationsEvents();
} else if (url::postToText('function') === 'get_notificationSolnIntre') { //roles: compliance
  get_notificationSolnIntre();
} else if (url::postToText('function') === 'updateNocStatus') { //roles: compliance
  updateNocStatus();
} else if (url::postToText('function') === 'get_notificationSolnDtl') { //roles: compliance
  get_notificationSolnDtl();
} else if (url::postToText('function') === 'AddRemoteJobs') { //roles: compliance
  AddRemoteJobsNew();
} else if (url::postToText('function') === 'AddAndroidJobs') { //roles: compliance
  AddAndroidJobs_func();
} else if (url::postToText('function') === 'notify_getprofile') { //roles: compliance
  notify_getprofile();
} else if (url::postToText('function') === 'updateSoln') { //roles: compliance
  updateSoln();
}

//Replace $routes['get'] with if else
if (url::getToText('function') === 'exportComplianceselected') { //roles: compliance
  exportComplianceselected();
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

function get_compliance()
{

  $nidOut = '';
  $user = $_SESSION['user']['username'];
  $searchType = $_SESSION['searchType'];
  $searchVal = $_SESSION['searchValue'];
  $item = url::issetInPost('item') ? url::postToAny('item') : '';
  $category = url::issetInPost('category') ? url::postToAny('category') : '';


  $notify_res = Notify_getNotificationsMysql($user, $searchType, $searchVal, $item, $category);
  echo $notify_res;
}


function getFilterType($items)
{
  $typearray = array();
  if(!empty($items)){
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
  }
  return $typearray;
}

function getCategoryType($items)
{
  $typearray = array();
  if(!empty($items)){
    foreach ($items as $item) {
      if ($item == 'Ok') {
        array_push($typearray, 1);
      }
      if ($item == 'Warning') {
        array_push($typearray, 2);
      }
      if ($item == 'Alert') {
        array_push($typearray, 3);
      }
    }
  }

  return $typearray;
}

function Notify_getNotificationsMysql($user, $searchType, $searchVal, $item, $category)
{
  $pdo = pdo_connect();
  if ($searchType == 'Sites') {
    $where = "c.site = '" . str_replace('/', '', $searchVal) . "' and c.machine IN (select host from " . $GLOBALS['PREFIX'] . "core.Census where site = '" . str_replace('/', '', $searchVal) . "')";
  } else if ($searchType == 'ServiceTag') {
    $where = "c.machine = '" . str_replace('/', '', $searchVal) . "'";
  } else {
    $dataScope = UTIL_GetSiteScope_PDO($pdo, str_replace('/', '', $searchVal), $searchType);
    $machines = DASH_GetGroupsMachines_PDO('', null, $dataScope);
    $whereArray = [];
    foreach ($machines as $row) {
      $whereArray[] = '"' . $row . '"';
    }
    $where = ' c.machine IN (' . implode(",", $whereArray) . ')';
  }

  $filterItems = getFilterType($item);
  $filterCategory = getCategoryType($category);

  if ($item != '') {
    $where =  $where . ' and c.ntype IN ( ' . implode(',', $filterItems) . ')';
  }

  if ($category != '') {
    $where =  $where . ' and c.status IN ( ' . implode(',', $filterCategory) . ')';
  }

  $nidOut = '';
  $q = "select distinct n.name from  " . $GLOBALS['PREFIX'] . "event.Notifications as n left join  event.Console as c on c.nid = n.id
   where
    " . $where . " and c.status != 4 and c.activeStatus = 1 LIMIT 1000  ";

  logs::log("Notify_getNotificationsMysql:" . $q);
  $consoleSql = $pdo->prepare($q);
  $consoleSql->execute();
  $data = $consoleSql->fetchAll(PDO::FETCH_ASSOC);

  $recordList = '';
  $i = 0;
  foreach ($data as $notify) {
    $name = $notify['name'];

    $onclickval = "complianceDtl_datatable('','','" . $name . "', this)";
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
  return $recordList . '##' . $nidOut;
}

function Notify_getNotifications($user, $searchType, $searchVal)
{
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
  $res = safe_json_decode($result, TRUE);
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

function get_complianceDtl()
{
  $pdo = pdo_connect();
  $name = url::requestToText('name');
  $category = url::issetInPost('category') ? url::postToAny('category') : '';
  $item = url::issetInPost('item') ? url::postToAny('item') : '';

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
    if ($orderVal != 'reset') {
      $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
    } else {
      $orderStr = 'ORDER BY servertime DESC';
    }
  } else {
    $orderStr = 'ORDER BY servertime DESC';
  }

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
    if (strtolower($notifSearch) == 'Availability') {
      $ntype = 1;
    } else if (strtolower($notifSearch) == 'Security') {
      $ntype = 2;
    } else if (strtolower($notifSearch) == 'Resource') {
      $ntype = 3;
    } else if (strtolower($notifSearch) == 'Maintenance') {
      $ntype = 4;
    } else if (strtolower($notifSearch) == 'Events') {
      $ntype = 5;
    }

    if (strtolower($notifSearch) == 'Ok') {
      $cat = 1;
    }
    if (strtolower($notifSearch) == 'Warning') {
      $cat = 2;
    }
    if (strtolower($notifSearch) == 'Alert') {
      $cat = 3;
    }
    if ($ntype != '' or $cat != '') {
      $whereSearch = " (machine LIKE '%" . $notifSearch . "%'
            OR ntype = $ntype OR status = $cat) and";
    } else {
      $whereSearch = " (machine LIKE '%" . $notifSearch . "%'
                OR ntype  LIKE '%" . $notifSearch . "%' OR status  LIKE '%" . $notifSearch . "%' ) and";
    }
  } else {
    $whereSearch = '';
  }

  $filterItems = getFilterType($item);
  $filterCategory = getCategoryType($category);

  if ($item != '') {
    $where =  $where . ' and ntype IN ( ' . implode(',', $filterItems) . ')';
  }

  if ($category != '') {
    $where =  $where . ' and status IN ( ' . implode(',', $filterCategory) . ')';
  }
  $return = getComplianceDetailsMysql($where, $name, $whereSearch, $limitStr, $orderStr);
  $data = $return[0];
  $totCount = $return[1];


  create_auditLog('Compliance', 'View', 'Success');
  if (safe_sizeof($data) == 0) {
    echo      json_encode(['success' => false, 'data' => 'Data Not Found']);
  } else {
    $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
    $dataArr['html'] =    $data; //formatComplianceDetails($groupedData);
    echo json_encode($dataArr);
  }
}

function getComplianceDetailsMysql($where, $name, $whereSearch, $limitStr, $orderStr)
{
  $pdo = pdo_connect();

  $q =  "select distinct machine,site,ntype,status,count(machine) as count,servertime, FROM_UNIXTIME(servertime, '%d-%m-%Y') as ndate from  " . $GLOBALS['PREFIX'] . "event.Console where $whereSearch  name = '$name' and status != 4 and activeStatus = 1 and " . $where . "  group by machine $orderStr $limitStr";
  $consoleSql = $pdo->prepare($q);
  $consoleSql->execute();
  $responseArray = $consoleSql->fetchAll(PDO::FETCH_ASSOC);
  $responseArray = get_replaceTypeItems($responseArray);
  $responseArray = get_replaceCategoryItems($responseArray);
  $i = 0;
  foreach ($responseArray as $res) {
    $retData[$i][] = $res['machine'];
    $retData[$i][] = $res['site'];
    $retData[$i][] = $res['itemtype'];
    $retData[$i][] = $res['category'];
    $retData[$i][] = $res['ndate'];
    $retData[$i][] = $res['count'];
    $retData[$i][] =  '<a href="javascript:void(0)" class="resetComplianceGroupHand">Reset</a>';
    $i++;
  }
  // echo '<pre>'.print_r($retData,1).'</pre>';

  $q3 = " select  distinct machine,site,ntype,status,count(machine) as count,servertime, FROM_UNIXTIME(servertime, '%d-%m-%Y') as ndate from  " . $GLOBALS['PREFIX'] . "event.Console where $whereSearch name = '$name' and status != 4 and activeStatus = 1 and " . $where . "  group by  machine  $orderStr ";

  $countSql = $pdo->prepare($q3);
  $countSql->execute();
  $totCount = safe_count($countSql->fetchAll(PDO::FETCH_ASSOC));
  return [$retData, $totCount];
}


function get_replaceTypeItems($resp)
{
  $index = 0;
  $newArray = $resp;
  foreach ($resp as $res) {
    if ($res['ntype'] == 1) {
      $newArray[$index]['itemtype'] = 'Availability';
    }
    if ($res['ntype'] == 2) {
      $newArray[$index]['itemtype'] = 'Security';
    }
    if ($res['ntype'] == 3) {
      $newArray[$index]['itemtype'] = 'Resource';
    }
    if ($res['ntype'] == 4) {
      $newArray[$index]['itemtype'] = 'Maintenance';
    }
    if ($res['ntype'] == 5) {
      $newArray[$index]['itemtype'] = 'Events';
    }
    $index++;
  }

  return $newArray;
}

function get_replaceCategoryItems($resp)
{
  $index = 0;
  $newArray = $resp;
  foreach ($resp as $res) {
    if ($res['status'] == 1) {
      $newArray[$index]['category'] = 'Ok';
    }
    if ($res['status'] == 2) {
      $newArray[$index]['category'] = 'Warning';
    }
    if ($res['status'] == 3) {
      $newArray[$index]['category'] = 'Alert';
    }
    $index++;
  }

  return $newArray;
}

function Format_notificationDataMysql($result, $notifStatus)
{
  $recordList = [];

  $i = 0;
  foreach ($result as $lastMachine) {
    $id = $lastMachine['id'];
    $tid = $id . "~~" . $name . "~~" . $cust . "~~" . $machine . "~~" . $lastMachine['this_run'];
    $allStatuses = array_column($eachNotificationData, 'nocStatus');

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
    } else if ($status && $status != 'Completed' && $status != 'default' && $status != NULL) {
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

    $recordList[$i][] =  $lastMachine['ndate'];

    $recordList[$i][] =  $lastMachine['count'];



    $recordList[$i][] = $status;

    $addNotes =   "addNotes('" . $name . "','" . $cust . "','" . $machine . "','" . $lastMachine['this_run'] . "')";

    if ($lastMachine['note'] == '') {
      $recordList[$i][] = '<p onclick="' . $addNotes . '" style="cursor: pointer;text-decoration: none;color:#0096D6;" title="Add Note">Add</p>';
    } else {
      $recordList[$i][] = '<p onclick="' . $addNotes . '" style="cursor: pointer;text-decoration: none;color:#0096D6;" title="View/Edit Note">View/Edit</p>';
    }


    $recordList[$i][] = (trim($lastMachine['note']) == '') ? '-' : trim($lastMachine['note']);
    $i++;
  }
  return $recordList;
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

  $notifySql = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Console where name = "' . $nocName . '" and status != 4 and activeStatus = 1 and ' . $where);

  $notifySql->execute();
  $data = $notifySql->fetchAll(PDO::FETCH_ASSOC);
  $jsonData = array();
  if (safe_sizeof($data) == 0) {
    echo json_encode($jsonData);
  } else {
    $jsonData = putFormatedDataToFileMysql($data);
    echo json_encode($jsonData);
  }
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
