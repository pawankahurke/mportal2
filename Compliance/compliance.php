<?php
/*  ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);   */
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';
require_once '../lib/l-elastic.php';
require_once '../lib/l-util.php';
require_once '../lib/l-dashboard.php';
require_once '../include/common_functions.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

if (!isset($_SESSION)) {
}

if (!isset($_SESSION['user']['dashboardLogin'])) {
  header("location:../index.php");
}

// $permission = checkModulePrivilege('compliance', 2);
// if (!$permission) exit('Permission denied');

$routes = [
  'get_categories_and_items' =>  'getCategoriesAndItems',
  'get_compliance_filters' => 'dispatchComplianceFilter',
  'get_compliance_details' => 'dispatchComplianceDetails',
  'reset_compliance_item' => 'resetComplianceItem'
];

if (url::issetInRequest('function')) {
  if (array_key_exists(url::requestToAny('function'), $routes)) {
    $function = $routes[url::requestToAny('function')];
    call_user_func($function);
  }
}

function resetComplianceItemold()
{
  $permission = checkModulePrivilege('compliance_reset', 2);
  if (!$permission) exit('Permission denied');

  $name = isset($_POST['data']['compliance_name']) ? $_POST['data']['compliance_name'] : '';
  $machine = isset($_POST['data']['machine']) ? $_POST['data']['machine'] : '';
  $site = isset($_POST['data']['site']) ? $_POST['data']['site'] : '';
  $item = isset($_POST['data']['item']) ? $_POST['data']['item'] : '';
  $category = isset($_POST['data']['category']) ? $_POST['data']['category'] : '';
  $datetime = isset($_POST['data']['datetime']) ? $_POST['data']['datetime'] : '';

  if (empty($name)) {
    exit(json_encode(['success' => false, 'message' => 'compliance name not found']));
  }

  if (empty($machine)) {
    exit(json_encode(['success' => false, 'message' => 'machine name not found']));
  }

  if (empty($site)) {
    exit(json_encode(['success' => false, 'message' => 'site name not found']));
  }

  if (empty($item)) {
    exit(json_encode(['success' => false, 'message' => 'item not found']));
  }

  if (empty($category)) {
    exit(json_encode(['success' => false, 'message' => 'category not found']));
  }

  if (empty($datetime)) {
    exit(json_encode(['success' => false, 'message' => 'datetime not found']));
  }

  $items = ["Security", "Maintenance", "Resource", "Events", "Availability"];
  $categories = ["Ok", "Warning", "Alert"];
  $name = urldecode($name);

  if (!in_array($item, $items)) {
    exit(json_encode(['success' => false, 'message' => 'Unable to reset']));
  }

  if (!in_array($category, $categories)) {
    exit(json_encode(['success' => false, 'message' => 'Unable to reset']));
  }


  global $elastic_url;
  $indexName = 'compliance';
  $url = $elastic_url . $indexName . '/_update_by_query?pretty';

  $query = '{
                "query": {
                  "bool": {
                    "must": [
                      {
                        "match": {
                          "nocName.keyword": "' . $name . '"
                        }
                      },
                      {
                        "match": {
                          "nhtype.keyword": "Compliance"
                        }
                      },
                      {
                        "match": {
                          "machine.keyword": "' . $machine . '"
                        }
                      },
                      {
                        "match": {
                          "site.keyword": "' . $site . '"
                        }
                      },
                      {
                        "match": {
                          "itemtype.keyword": "' . $item . '"
                        }
                      },
                      {
                        "match": {
                          "category.keyword": "' . $category . '"
                        }
                      },
                      {
                        "match": {
                          "reset.keyword": "0"
                        }
                      }
                    ]
                  }
                },
                "script": {
		  "inline": "ctx._source.reset=\'1\'"
                }
              }';

  global $elastic_username;
  global $elastic_password;

  $headers = array(
    "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
  );
  $headers[] = "Content-Type: application/json";
  $headers[] = "Authorization: Basic " .  base64_encode($elastic_username . ":" . $elastic_password);

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $result = curl_exec($ch);
  $errorno = curl_errno($ch);

  if ($errorno) {
    exit(json_encode(['success' => false, 'message' => 'Unable to reset, Something went wrong']));
  }

  $response = safe_json_decode($result, true);

  if (isset($response['total'])) {
    exit(json_encode(['success' => true, 'message' => 'Successfully reset compliance data']));
  }

  exit(json_encode(['success' => false, 'message' => 'Unable to reset']));
}


function resetComplianceItem()
{
  $permission = checkModulePrivilege('compliance_reset', 2);
  if (!$permission) exit('Permission denied');

  $name = isset($_POST['data']['compliance_name']) ? $_POST['data']['compliance_name'] : '';
  $machine = isset($_POST['data']['machine']) ? $_POST['data']['machine'] : '';
  $site = isset($_POST['data']['site']) ? $_POST['data']['site'] : '';
  $item = isset($_POST['data']['item']) ? $_POST['data']['item'] : '';
  $category = isset($_POST['data']['category']) ? $_POST['data']['category'] : '';
  $datetime = isset($_POST['data']['datetime']) ? $_POST['data']['datetime'] : '';

  if (empty($name)) {
    exit(json_encode(['success' => false, 'message' => 'compliance name not found']));
  }

  if (empty($machine)) {
    exit(json_encode(['success' => false, 'message' => 'machine name not found']));
  }

  if (empty($site)) {
    exit(json_encode(['success' => false, 'message' => 'site name not found']));
  }

  if (empty($item)) {
    exit(json_encode(['success' => false, 'message' => 'item not found']));
  }

  if (empty($category)) {
    exit(json_encode(['success' => false, 'message' => 'category not found']));
  }

  if (empty($datetime)) {
    exit(json_encode(['success' => false, 'message' => 'datetime not found']));
  }

  $items = ["Security", "Maintenance", "Resource", "Events", "Availability"];
  $categories = ["Ok", "Warning", "Alert"];
  $name = urldecode($name);

  if (!in_array($item, $items)) {
    exit(json_encode(['success' => false, 'message' => 'Unable to reset']));
  }

  if (!in_array($category, $categories)) {
    exit(json_encode(['success' => false, 'message' => 'Unable to reset']));
  }

  $categ = getCategoryType([$category]);
  $ftype = getFilterType([$item]);
  $dt = date('Y-m-d', strtotime($datetime));


  $pdo = pdo_connect();
  $selConsole = $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Console where machine = ? and site = ? and status = ? and ntype = ? and date(FROM_UNIXTIME(this_run)) = ? and name = ?');
  $selConsole->execute([$machine, $site, $categ[0], $ftype[0], $dt, urldecode($name)]);
  $consoleres = $selConsole->fetchAll(PDO::FETCH_ASSOC);
  foreach ($consoleres as $cons) {

    $consoleCheck = $pdo->prepare('update  ' . $GLOBALS['PREFIX'] . 'event.Console set activeStatus = 0 where id = ?');
    $consoleCheck->execute([$cons['id']]);
  }



  exit(json_encode(['success' => true, 'message' => 'Successfully reset compliance data']));
}





function dispatchComplianceDetails()
{
  if ($_SERVER['REQUEST_METHOD'] != 'POST')
    return;

  if (!url::issetInPost('name')) {
    exit(json_encode(['success' => false, 'data' => 'compliance name not found']));
  }
  $nofStatus = url::issetInPost('status') ? url::postToAny('status') : '';
  $nocName = trim(urldecode(url::postToAny('name')));
  $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
  $curPage = url::postToInt('nextPage') - 1;

  $limitStart = $limitCount * $curPage;

  $limitEnd = $limitStart + $limitCount;

  $user = $_SESSION['user']['username'];
  $searchType = $_SESSION['searchType'];
  $searchVal = $_SESSION['searchValue'];
  $pdo = NanoDB::connect();
  $con = '';
  $name = urldecode(url::postToAny('name'));
  $items = explode(",", urldecode(url::postToAny('compliance_items')));
  $categories = explode(",", urldecode(url::postToAny('compliance_categories')));
  $allowedItems = ["Security", "Maintenance", "Resource", "Events", "Availability"];
  $allowedCategory = ["Ok", "Warning", "Alert"];

  $validateFail = false;
  foreach ($items as $eachItemsNames) {
    if (!in_array($eachItemsNames, $allowedItems)) {
      $validateFail = true;
      break;
    }
  }

  foreach ($categories as $eachCategoryName) {
    if (!in_array($eachCategoryName, $allowedCategory)) {

      $validateFail = true;
      break;
    }
  }

  if ($validateFail) {
    exit(json_encode(["data" => '']));
  }

  if ($searchType == 'Sites') {
    $where = ' site ="' . str_replace('/', '', $searchVal) . '"';
  } else if ($searchType == 'ServiceTag') {
    $where = ' machine = "' . str_replace('/', '', $searchVal) . '"';
  } else {
    $dataScope = UTIL_GetSiteScope_PDO($pdo, str_replace('/', '', $searchVal), $searchType);
    $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
    $whereArray = [];
    foreach ($machines as $row) {
      $whereArray[] = '"' . $row . '"';
    }


    $where = ' machine IN (' . implode(",", $whereArray) . ')';
  }

  $filterItems = getFilterType($items);
  $filterCategory = getCategoryType($categories);

  if (safe_sizeof($filterItems) > 0) {
    $where =  $where . ' and ntype IN ( ' . implode(',', $filterItems) . ')';
  }
  if (safe_sizeof($filterCategory) > 0) {
    $where =  $where . ' and status IN ( ' . implode(',', $filterCategory) . ')';
  }

  $filters = [];
  $return = getComplianceDetailsMysql($where, $name);
  $data = $return[0];
  $totCount = $return[1];


  $auditRes = create_auditLog('Compliance', 'View', 'Success');
  if (safe_sizeof($data) == 0) {
    echo      json_encode(['success' => false, 'data' => 'Data Not Found']);
  } else {
    $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
    $dataArr['data'] =    $data; //formatComplianceDetails($groupedData);
    echo json_encode($dataArr);
  }
  //  exit(json_encode($rsponseArray));
}


function getComplianceDetailsMysql($where, $name)
{
  $pdo = pdo_connect();
  $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
  $curPage = url::postToInt('nextPage') - 1;

  $limitStart = $limitCount * $curPage;

  $limitEnd = $limitStart + $limitCount;
  $q =  "select machine,site,ntype,status,SUM(count) as count,servertime, FROM_UNIXTIME(servertime, '%d-%m-%Y') as ndate from  " . $GLOBALS['PREFIX'] . "event.Console where name = '$name' and status != 4 and activeStatus = 1 and " . $where . "  group by ndate, machine, count  ORDER BY servertime DESC LIMIT $limitStart,$limitEnd";
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
    $retData[$i][] =   $res['count'];
    $retData[$i][] =  '<a href="javascript:void(0)" class="resetComplianceGroupHand">Reset</a>';
    $i++;
  }
  // echo '<pre>'.print_r($retData,1).'</pre>';

  $q3 = " select    FROM_UNIXTIME(servertime, '%d-%m-%Y')  as ndate from  " . $GLOBALS['PREFIX'] . "event.Console where name = '$name' and status != 4 and activeStatus = 1 and " . $where . "  group by ndate, machine, count  ORDER BY servertime DESC ";

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
function getCategoryType($items)
{
  $typearray = array();
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

  return $typearray;
}
function getComplianceFiltersMysql($items, $categories)
{
  $user = $_SESSION['user']['username'];
  $searchType = $_SESSION['searchType'];
  $searchVal = $_SESSION['searchValue'];
  $pdo = pdo_connect();
  $con = '';
  $allowedItems = ["Security", "Maintenance", "Resource", "Events", "Availability"];
  $allowedCategory = ["Ok", "Warning", "Alert"];
  $where = '';
  $validateFail = false;

  if (empty($items)) {
    $responseArray = [
      'success' => false,
      'message' => "Please select atleast one compliance item to view compliance data"
    ];
    return ($responseArray);
  }

  if (empty($categories)) {
    $responseArray = [
      'success' => false,
      'message' => "Please select atleast one compliance category to view compliance data"
    ];
    return ($responseArray);
  }

  foreach ($items as $eachItemsNames) {
    if (!in_array($eachItemsNames, $allowedItems)) {
      $validateFail = true;
      break;
    }
  }

  foreach ($categories as $eachCategoryName) {
    if (!in_array($eachCategoryName, $allowedCategory)) {
      $validateFail = true;
      break;
    }
  }

  if ($validateFail) {
    $responseArray = [
      'success' => false,
      'message' => "No record found"
    ];

    return ($responseArray);
  }

  if ($searchType == 'Sites') {
    $where = 'site = "' . str_replace('/', '', $searchVal) . '"';
  } else if ($searchType == 'ServiceTag') {
    $where = 'machine  = "' . str_replace('/', '', $searchVal) . '"';
  } else {
    $dataScope = UTIL_GetSiteScope_PDO($pdo, str_replace('/', '', $searchVal), $searchType);
    $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
    $whereArray = [];

    foreach ($machines as $row) {
      $whereArray[] = '"' . $row . '"';
    }

    $where = 'machine IN (' . implode(",", $whereArray) . ')';
  }

  $filterItems = getFilterType($items);
  $filterCategory = getCategoryType($categories);

  if (empty($where)) {
    $responseArray = [
      'success' => false,
      'message' => "Filters not available for the selection"
    ];

    return ($responseArray);
  }
  if (safe_sizeof($filterItems) > 0) {
    $where =  $where . ' and ntype IN ( ' . implode(',', $filterItems) . ')';
  }
  if (safe_sizeof($filterCategory) > 0) {
    $where =  $where . ' and status IN ( ' . implode(',', $filterCategory) . ')';
  }

  $filters = [];
  $responseArray = [
    'success' => false,
    'message' => "No data available"
  ];
  if (isset($items) && isset($categories)) {

    $consoleCheck =  $pdo->prepare('select * from  ' . $GLOBALS['PREFIX'] . 'event.Notifications where id IN (select nid from  ' . $GLOBALS['PREFIX'] . 'event.Console where ' . $where . ' and status != 4 and activeStatus = 1 )');
    $consoleCheck->execute();
    // print_r($consoleCheck);exit;
    $filters = $consoleCheck->fetchAll(PDO::FETCH_ASSOC);
  }

  if ($filters) {
    $responseArray = [
      'success' => true,
      'data' => $filters
    ];
  }
  // print_R($responseArray);exit;
  return ($responseArray);
}





function exportComplianceDetails($data)
{
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->setActiveSheetIndex(0);
  $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
  $objPHPExcel->getActiveSheet()->setCellValue('A1', "Machine Name");
  $objPHPExcel->getActiveSheet()->setCellValue('B1', "Site Name");
  $objPHPExcel->getActiveSheet()->setCellValue('C1', "Item");
  $objPHPExcel->getActiveSheet()->setCellValue('D1', "Category");
  $objPHPExcel->getActiveSheet()->setCellValue('E1', "Date");
  $objPHPExcel->getActiveSheet()->setCellValue('F1', "Count");
  $objPHPExcel->getActiveSheet()->setTitle("Compliance Details");

  if (safe_count($data) > 0) {
    $index = 2;
    foreach ($data as $each) {
      $machine = isset($each[0]) ? $each[0] : '';
      $site   = isset($each[1]) ? $each[1] : '';
      $item = isset($each[2]) ? $each[2] : '';
      $category = isset($each[3]) ? $each[3] : '';
      $date   = isset($each[4]) ? $each[4] : '';
      $count = isset($each[5]) ? $each[5] : '';;

      $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $machine);
      $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $site);
      $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $item);
      $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $category);
      $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $date);
      $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $count);
      $index++;
    }
  } else {
    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'No data available');
  }

  $objPHPExcel->setActiveSheetIndex(0);
  header('Content-Type: application/vnd.ms-excel');
  header('Content-Disposition: attachment;filename="compliance_details.xls"');
  header('Cache-Control: max-age=0');
  $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
  ob_end_clean();
  $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
  $auditRes = create_auditLog('Compliance', 'Export', 'Success', NULL, $gpname);

  $objWriter->save('php://output');
}

function formatComplianceDetails($detailsArray)
{
  $formattedArray  = [];
  foreach ($detailsArray as $machineName => $eachMachines) {
    foreach ($eachMachines as $eachItemName => $eachItems) {
      foreach ($eachItems as $categoryName => $eachcategories) {
        $data = [];
        $data[] = isset($eachcategories[0]['machine']) ? $eachcategories[0]['machine'] : '';
        $data[] = isset($eachcategories[0]['site']) ? $eachcategories[0]['site'] : '';
        $data[] = isset($eachcategories[0]['itemtype']) ? $eachcategories[0]['itemtype'] : '';
        $data[] = isset($eachcategories[0]['category']) ? $eachcategories[0]['category'] : '';
        $data[] = isset($eachcategories[0]['@timestamp']) ? $eachcategories[0]['@timestamp'] : '';
        $data[] = isset($eachcategories) ? safe_sizeof($eachcategories) : '0';
        if (checkModulePrivilege('compliance_filter', 2)) $data[] = '<a href="javascript:void(0)" class="resetComplianceGroupHand">Reset</a>';
        $formattedArray[] = $data;
      }
    }
  }

  return $formattedArray;
}

function dispatchComplianceFilter()
{
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $items = url::isEmptyInPost('compliance_items') ? [] : explode(",", urldecode(url::postToAny('compliance_items')));
    $categories = url::isEmptyInPost('compliance_categories') ? [] : explode(",", urldecode(url::postToAny('compliance_categories')));

    exit(json_encode(getComplianceFiltersMysql($items, $categories)));
  }
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

function getComplianceFilters($items, $categories)
{
  $user = $_SESSION['user']['username'];
  $searchType = $_SESSION['searchType'];
  $searchVal = $_SESSION['searchValue'];
  $user = $_SESSION['user']['username'];
  $pdo = NanoDB::connect();
  $con = '';
  $allowedItems = ["Security", "Maintenance", "Resource", "Events", "Availability"];
  $allowedCategory = ["Ok", "Warning", "Alert"];
  $where = '';
  $validateFail = false;

  if (empty($items)) {
    $responseArray = [
      'success' => false,
      'message' => "Please select atleast one compliance item to view compliance data"
    ];
    return ($responseArray);
  }

  if (empty($categories)) {
    $responseArray = [
      'success' => false,
      'message' => "Please select atleast one compliance category to view compliance data"
    ];
    return ($responseArray);
  }

  foreach ($items as $eachItemsNames) {
    if (!in_array($eachItemsNames, $allowedItems)) {
      $validateFail = true;
      break;
    }
  }

  foreach ($categories as $eachCategoryName) {
    if (!in_array($eachCategoryName, $allowedCategory)) {
      $validateFail = true;
      break;
    }
  }

  if ($validateFail) {
    $responseArray = [
      'success' => false,
      'message' => "No record found"
    ];

    return ($responseArray);
  }

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
    }

    $where = '{"terms" : {"machine.keyword" : [' . implode(",", $whereArray) . ']}}';
  }

  if (empty($where)) {
    $responseArray = [
      'success' => false,
      'message' => "Filters not available for the selection"
    ];

    return ($responseArray);
  }
  $res = checkElasticValue();
  if ($res == '0') {
    $whereArray = [];
    foreach ($items as $eachItems) {
      $whereArray[] = '"' . $eachItems . '"';
    }

    if (safe_sizeof($whereArray) > 0)
      $where .= ',{"match" : {"itemtype.keyword" : [' . implode(",", $whereArray) . ']}}';

    $whereArray = [];
    foreach ($categories as $eachCategories) {
      $whereArray[] = '"' . $eachCategories . '"';
    }

    if (safe_sizeof($whereArray) > 0)
      $where .= ',{"match" : {"category.keyword" : [' . implode(",", $whereArray) . ']}}';
  }

  $filters = [];
  $responseArray = [
    'success' => false,
    'message' => "No data available"
  ];
  if (isset($items) && isset($categories)) {
    $filters = getComplianceFilterData($where, $res);
  }

  if ($filters) {
    $responseArray = [
      'success' => true,
      'data' => $filters
    ];
  }

  return ($responseArray);
}

function getCategoriesAndItems()
{
  $nidOut = '';
  $user = $_SESSION['user']['username'];
  $searchType = $_SESSION['searchType'];
  $searchVal = $_SESSION['searchValue'];

  $user = $_SESSION['user']['username'];
  $fromdate = strtotime('-14 days');
  $todate = time();
  $indexName = 'notification';
  $pdo = NanoDB::connect();

  $con = '';
  if ($searchType == 'Sites') {
    $where = '{
                    "term": {
                      "site.keyword": "' . $searchVal . '"
                    }
                }';
  } else if ($searchType == 'ServiceTag') {
    $where = ' {
                        "term": {
                          "machine.keyword": "' . $searchVal . '"
                        }
                    }';
  } else {
    $dataScope = UTIL_GetSiteScope($pdo, $searchVal, $searchType);
    $machines = DASH_GetGroupsMachines('', $pdo, $dataScope);
    $where = '';

    foreach ($machines as $row) {
      $where .= '{ "term": { "machine.keyword": "' . $row . '" }},';
    }

    $where = rtrim($where, ',');
  }

  $categories = getComplianceCategory($where);
  $items = getComplianceItems($where);

  $rsponseArray = ['success' => false];

  if ($categories && $items) {
    $rsponseArray = [
      'success' => true,
      'data' => [
        'categories' => $categories,
        'items' => $items
      ]
    ];
  }

  exit(json_encode($rsponseArray));
}


function EL_post($data, $indexName, $indexType = false, $size = '10000')
{
  global $elastic_url;
  global $elastic_username;
  global $elastic_password;

  $url = $elastic_url . $indexName;
  if ($indexType)  $url .= "/" . $indexType;
  $url .= "/_search?size=" . $size . "&pretty";

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

  $headers = array(
    "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
  );
  $headers[] = "Content-Type: application/json";
  $headers[] = "Authorization: Basic " .  base64_encode($elastic_username . ":" . $elastic_password);

  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $errorno = curl_errno($ch);

  $result = curl_exec($ch);
  if ($errorno) {
    logElasticError($errorno, $result);
    return array();
    exit();
  }

  curl_close($ch);

  return $result;
}


function getValueByKeyInArray($key, $array)
{
  $returnArray = [];
  foreach ($array as $eachArray) {
    if (array_key_exists($key, $eachArray)) {
      $returnArray[] = $eachArray[$key];
    }
  }

  return $returnArray;
}



function getComplianceCategory($where)
{
  $query = '{
        "query": {
          "bool": {
            "must": [
              {
                "bool": {
                  "minimum_should_match": 1,
                  "should": [
                    ' . $where . '
                  ]
                }
              },
              {
                "match": {
                  "nhtype.keyword": "Compliance"
                }
              }
            ]
          }
        },
        "aggs": {
          "myaggs": {
            "terms": {
              "field": "machine.keyword"
            }
          }
        }
      }';


  $jsonResponse = EL_post($query, 'compliance');
  $responseArray = safe_json_decode($jsonResponse, true);

  if (array_key_exists('error', $responseArray)) {
    return false;
  }

  $aggregations = isset($responseArray['aggregations']['myaggs']['buckets']) ? $responseArray['aggregations']['myaggs']['buckets'] : false;
  $data = getValueByKeyInArray('key', $aggregations);

  return $data;
}


function getComplianceItems($where)
{
  $query = '{
        "query": {
          "bool": {
            "must": [
              {
                "bool": {
                  "minimum_should_match": 1,
                  "should": [
                    ' . $where . '
                  ]
                }
              },
              {
                "match": {
                  "nhtype.keyword": "Compliance"
                }
              }
            ]
          }
        },
        "aggs": {
          "myaggs": {
            "terms": {
              "field": "machine.keyword"
            }
          }
        }
      }';


  $jsonResponse = EL_post($query, 'compliance');
  $responseArray = safe_json_decode($jsonResponse, true);

  if (array_key_exists('error', $responseArray)) {
    return false;
  }

  $aggregations = isset($responseArray['aggregations']['myaggs']['buckets']) ? $responseArray['aggregations']['myaggs']['buckets'] : false;
  $data = getValueByKeyInArray('key', $aggregations);

  return $data;
}

function getComplianceFilterData($where, $res)
{
  if ($res == '0') {
    $must = '[
{
                    "match": {
                        "nhtype.keyword": "compliance"
                    }
                  },
                  {
                    "match": {
                      "reset.keyword": "0"
                    }
                  }
                  ],
                  "filter" : [
                  ' . $where . '
                  ]';
  } else {
    $must = '[
                {
                    "match": {
                        "reset.keyword": "0"
              }
            },
                ' . $where . '
            ]';
  }



  $query = '{
    "query": {
        "bool": {
            "must": ' . $must . '
        }
    },
            "aggs": {
              "myaggs": {
                "terms": {
                "field": "nocName.keyword",
                "size": 10000
                }
              }
            },
            "size" : 0
          }';

  $jsonResponse = EL_post($query, 'compliance');
  $responseArray = safe_json_decode($jsonResponse, true);

  if (array_key_exists('error', $responseArray)) {
    return false;
  }

  $aggregations = isset($responseArray['aggregations']['myaggs']['buckets']) ? $responseArray['aggregations']['myaggs']['buckets'] : false;
  $data = getValueByKeyInArray('key', $aggregations);
  asort($data);

  return array_values($data);
}

function getComplianceDetails($where, $name)
{
  $res = checkElasticValue();
  $fromdate = strtotime('-14 days');
  $todate = time();
  if ($res == '0') {
    $must = '[
                  {
                    "match" : {
            "nocName.keyword" : "' . $name . '"
                    }
                  },
                  {
                    "match": {
                        "nhtype.keyword": "Compliance"
                    }
                  },
                  {
                    "match": {
                      "reset.keyword": "0"
                    }
                  }
                  ],
                  "filter" : [
            ' . $where . '
                  ]
              }
      }';
    $field = "machine.keyword";
  } else {
    $must = '[
                {
                    "match": {
                        "reset.keyword": "0"
                    }
            },
                {
                	"match" :{"nocName.keyword" : "' . $name . '"}
                },
                ' . $where . '
            ]';
    $field = "itemtype.keyword";
  }

  $query = '{
    "query": {
        "bool": {
            "must": ' . $must . '
        }
    },
            "aggs": {
        "myaggs": {
                "terms": {
                "field": "' . $field . '",
                "size": 10000
                }
              }
            }
          }';

  $jsonResponse = EL_post($query, 'compliance');
  $responseArray = safe_json_decode($jsonResponse, true);
  if (array_key_exists('error', $responseArray)) {
    return false;
  }

  return $responseArray;
}
