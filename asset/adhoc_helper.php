<?php


global $base_path;
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../vendor/autoload.php';
require_once '../lib/l-db.php';
require_once '../include/common_functions.php';
require_once '../lib/l-util.php';
require_once '../lib/l-dashboard.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-setTimeZone.php';



function filterOperationsDetails($id, $userId = null)
{
    $id = (int) $id;

    $sql = 'SELECT
                a.id, a.name, a.created_at,
                ao.filter_name, ao.filter_value,
                ao.filter_operation,
                ao.source_fields as source_fields
           FROM `' . $GLOBALS['PREFIX'] . 'agent`.`adhoc_query_filters` a
           LEFT JOIN `' . $GLOBALS['PREFIX'] . 'agent`.`adhoc_query_filter_opearations` ao ON (a.id=ao.adhoc_query_filter_id)
           where a.id=?';
    $bindings = [$id];

    if (!is_null($userId))
        $sql .= ' AND a.user_id=?';
    if (!is_null($userId))
        $bindings[] = $userId;

    $pdo = NanoDB::connect();
    $dbo = $pdo->prepare($sql);
    $dbo->execute($bindings);
    $result = $dbo->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

function filtereventOperationsDetails($id, $userId = null)
{
    $id = (int) $id;

    $sql = 'SELECT
                a.dartno,a.id, a.name, a.created_at,
                ao.filter_name, ao.filter_value,
                ao.filter_operation,
                ao.source_fields as source_fields
           FROM `' . $GLOBALS['PREFIX'] . 'agent`.`adhoc_event_filters` a
           LEFT JOIN `' . $GLOBALS['PREFIX'] . 'agent`.`adhoc_event_filter_opearations` ao ON (a.id=ao.adhoc_event_filter_id)
           where a.id=?';
    $bindings = [$id];

    if (!is_null($userId))
        $sql .= ' AND a.user_id=?';
    if (!is_null($userId))
        $bindings[] = $userId;

    $pdo = NanoDB::connect();
    $dbo = $pdo->prepare($sql);
    $dbo->execute($bindings);
    $result = $dbo->fetchAll(PDO::FETCH_ASSOC);
    return $result;
}

function all($userId)
{
    $finalArray = array();
    $sql = "SELECT a.id, a.name, a.created_at,
            GROUP_CONCAT(ao.filter_name) as filter_name, GROUP_CONCAT(ao.filter_value) as filter_value,
            GROUP_CONCAT(ao.filter_operation) as filter_operation,
            ao.source_fields as source_fields
            FROM `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` a
            LEFT JOIN `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filter_opearations` ao ON (a.id=ao.adhoc_query_filter_id)
            WHERE a.user_id=?
            GROUP BY a.id ORDER BY a.id DESC";

    $pdo = NanoDB::connect();
    $dbo = $pdo->prepare($sql);
    $dbo->execute([$userId]);
    $result = $dbo->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $key => $val) {
        $val['type'] = 'Asset';
        array_push($finalArray, $val);
    }


    $sql2 = "SELECT a.dartno,a.id, a.name, a.created_at,
            GROUP_CONCAT(ao.filter_name) as filter_name, GROUP_CONCAT(ao.filter_value) as filter_value,
            GROUP_CONCAT(ao.filter_operation) as filter_operation,
            ao.source_fields as source_fields
            FROM `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` a
            LEFT JOIN `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filter_opearations` ao ON (a.id=ao.adhoc_event_filter_id)
            WHERE a.user_id=?
            GROUP BY a.id ORDER BY a.id DESC";

    $pdo = NanoDB::connect();
    $dbo2 = $pdo->prepare($sql2);
    $dbo2->execute([$userId]);
    $result2 = $dbo2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result2 as $key => $val) {
        $val['type'] = 'Event';
        array_push($finalArray, $val);
    }
    return $finalArray;
}

function dataTableList()
{
    if (!isset($_SESSION['user']['dashboardLogin'])) {
        exit(json_encode(array('data' => [])));
    }

    global $base_url;
    $result = all($_SESSION['user']['dashboardLogin']);
    $return = [];
    foreach ($result as $eachRows) {
        $type = $eachRows['type'];
        if ($type == 'Asset') {
            $export = '<a style="color: #ba54f5;" onclick="export_Asset(\'' . $eachRows['id'] . '\')" id="export_Asset">Execute</a>';
        } else {
            $export = '<a style="color: #ba54f5;" onclick="export_Event(\'' . $eachRows['id'] . '\')" id="export_Event">Execute</a>';
        }
        $time = strtotime($eachRows['created_at']);

        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $actualtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $time, "m/d/Y h:i A");
        } else {
            $actualtime = date("m/d/Y h:i A", $time);
        }
        $return[] = [
            'id' => $eachRows['id'],
            'name' => $eachRows['name'],
            'source' => strlen($eachRows['source_fields']) > 30 ? substr($eachRows['source_fields'], 0, 30) . '...' : $eachRows['source_fields'],
            'created' => $actualtime,
            'type' => $eachRows['type'],
            'export' => $export,
        ];
    }

    $return = array('data' => $return);
    echo json_encode($return);
}

function createFilter()
{
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!isset($_SESSION['user']['dashboardLogin'])) {
            exit(json_encode(['success' => false, 'message' => 'User not found']));
        }

        $mpriv = checkModulePrivilege('ad-hoc', 2);
        if (!$mpriv) {
            exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
        }

        $pdo = NanoDB::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();
        $totalOpt = 2;
        $optsCompleted = 0;
        $userId = $_SESSION['user']['dashboardLogin'];

        $sql = "INSERT INTO `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` (`user_id` ,`name`) VALUES (?, ?)";
        $dbo = $pdo->prepare($sql);

        try {
            $dbo->execute([$userId, url::postToText('fname')]);
        } catch (\Exception $e) {
            $pdo->rollback();
            if (stripos($e->getMessage(), 'duplicate')) {
                exit(json_encode(['success' => false, 'message' => 'The name alredy exists, choose a different name']));
            }
        }

        $lastInsertId = $pdo->lastInsertId();

        if (is_numeric($lastInsertId) && intval($lastInsertId) > 0)
            $optsCompleted++;

        $c = 0;
        $sourceField = strip_tags($_POST['source-field']);
        $insertSql = "INSERT INTO `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filter_opearations` (`adhoc_query_filter_id`, `filter_name`, `filter_operation`, `filter_value`, `source_fields`) VALUES ";
        $bindings = [];
        $insertValueArray = [];
        $hasValues = false;

        foreach (url::postToAny('filter-name') as $eachFilterName) {
            $eachFilterName = strip_tags($eachFilterName);
            $eachFilterOperator = isset($_POST['filter-operator'][$c]) ? strip_tags($_POST['filter-operator'][$c]) : null;
            $eachFilterValue = isset($_POST['filter-value'][$c]) ? strip_tags($_POST['filter-value'][$c]) : null;

            if (!is_null($eachFilterName) && !is_null($eachFilterOperator) && !is_null($eachFilterValue)) {
                $hasValues = true;

                $bindings[] = (int) $lastInsertId;
                $bindings[] = $eachFilterName;
                $bindings[] = $eachFilterOperator;
                $bindings[] = $eachFilterValue;
                $bindings[] = $sourceField;

                $insertValueArray[$c] = "(?, ?, ?, ?, ?)";
            }

            $c++;
        }
        if ($hasValues) {
            $insertSql .= implode(",", $insertValueArray);
            $dbo = $pdo->prepare($insertSql);

            try {
                $dbo->execute($bindings);
                $optsCompleted++;
            } catch (\Exception $e) {
                $pdo->rollback();
                exit(json_encode(['success' => false, 'message' => 'The name alredy exists, choose a different name']));
            }
        }

        if ($optsCompleted >= $totalOpt) {
            $pdo->commit();
        }

        $auditdata = ['Created Asset Filter'];
        auditInformation($auditdata);
        exit(json_encode(['success' => true, 'message' => 'Successfully added a new filter']));
    }
}

function updateFilter()
{
    $id = url::postToText('selectedValue');
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {

        if (!isset($_SESSION['user']['dashboardLogin'])) {
            exit(json_encode(['success' => false, 'message' => 'User not found']));
        }

        $mpriv = checkModulePrivilege('ad-hoc', 2);
        if (!$mpriv) {
            exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
        }

        $pdo = NanoDB::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();
        $totalOpt = 2;
        $optsCompleted = 0;
        $userId = $_SESSION['user']['dashboardLogin'];


        $sql1 = $pdo->prepare("Select * FROM `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` WHERE id = ? ");
        $sql1->execute([$id]);
        $res1 = $sql1->fetch();
        $newname = url::postToText('fnameedit');
        if ($res1) {
            $sql2 = "update `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` set name=? where id=? and user_id=?";
            $dbo2 = $pdo->prepare($sql2);
            $res = $dbo2->execute([$newname, $id, $userId]);
            $lastInsertId = $pdo->lastInsertId();
        } else {
            $sql2 = "Insert into  `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` (name,id,user_id) VALUES (?,?,?)";
            $dbo2 = $pdo->prepare($sql2);
            $dbo2->execute([$newname, $id, $userId]);
            $lastInsertId = $pdo->lastInsertId();
        }

        $sql = $pdo->prepare("DELETE FROM `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filter_opearations` WHERE adhoc_query_filter_id = ? ");
        $sql->execute([$id]);
        $res = $pdo->lastInsertId();

        if (is_numeric($lastInsertId) && intval($lastInsertId) > 0)
            $optsCompleted++;

        $c = 0;
        $sourceField = strip_tags($_POST['source-fieldedit']);
        $insertSql = "INSERT INTO `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filter_opearations` (`adhoc_query_filter_id`, `filter_name`, `filter_operation`, `filter_value`, `source_fields`) VALUES ";
        $bindings = [];
        $insertValueArray = [];
        $hasValues = false;

        foreach (url::postToAny('filter-nameedit') as $eachFilterName) {
            $eachFilterName = strip_tags($eachFilterName);
            $eachFilterOperator = isset($_POST['filter-operatoredit'][$c]) ? strip_tags($_POST['filter-operatoredit'][$c]) : null;
            $eachFilterValue = isset($_POST['filter-valueedit'][$c]) ? strip_tags($_POST['filter-valueedit'][$c]) : null;

            if (!is_null($eachFilterName) && !is_null($eachFilterOperator) && !is_null($eachFilterValue)) {
                $hasValues = true;

                $bindings[] = (int) $id;
                $bindings[] = $eachFilterName;
                $bindings[] = $eachFilterOperator;
                $bindings[] = $eachFilterValue;
                $bindings[] = $sourceField;

                $insertValueArray[$c] = "(?, ?, ?, ?, ?)";
            }

            $c++;
        }
        if ($hasValues) {
            $insertSql .= implode(",", $insertValueArray);
            $dbo = $pdo->prepare($insertSql);
            $res = $dbo->execute($bindings);
            $pdo->commit();
            $optsCompleted++;
        }


        if ($optsCompleted >= $totalOpt) {
            $pdo->commit();
        }

        $auditdata = ['Updated Asset Filter'];
        auditInformation($auditdata);
        exit(json_encode(['success' => true, 'message' => 'Successfully added a new filter']));
    }
}

function geteventDartList()
{
    $pdo = NanoDB::connect();
    $sql = "SELECT DISTINCT DartNo from " . $GLOBALS['PREFIX'] . "agent.event_filters order by DartNo  + 0 ASC";
    $dbo = $pdo->prepare($sql);
    $dbo->execute();
    $result = $dbo->fetchAll(PDO::FETCH_ASSOC);
    $scripList = "";
    foreach ($result as $key => $value) {
        $dartVal = $value['DartNo'];
        if ($dartVal != '' && $dartVal != '-') {
            $scripList .= "<option value='" . $value['DartNo'] . "'>" . $value['DartNo'] . "</option>";
        }
    }
    echo $scripList;
}

function getEvent_List()
{
    $dart = url::getToText('selectedDart');
    $pdo = NanoDB::connect();
    $arr = explode(',', $dart);

    $in = str_repeat('?,', safe_count($arr) - 1) . '?';
    $sql = "SELECT filter_name from " . $GLOBALS['PREFIX'] . "agent.event_filters where DartNo IN ($in)";
    $stm = $pdo->prepare($sql);
    $stm->execute($arr);
    $data = $stm->fetchAll(PDO::FETCH_ASSOC);

    $scripList = "";
    foreach ($data as $key => $value) {
        $dartVal = $value['filter_name'];
        if ($dartVal != '' && $dartVal != '-') {
            $scripList .= "<option value='" . $value['filter_name'] . "'>" . $value['filter_name'] . "</option>";
        }
    }
    echo $scripList;
}

function check_EventTitle()
{
    $dart = url::getToText('selectedDart');
    $pdo = NanoDB::connect();

    $sql = "SELECT filter_name from " . $GLOBALS['PREFIX'] . "agent.event_filters where DartNo = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([$dart]);
    $datares = $stm->fetch(PDO::FETCH_ASSOC);
    $data = $datares['filter_name'];

    echo $data;
}

function createEventFilter()
{

    $pdo = NanoDB::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    $name = url::postToText('fname2');
    $dart = url::postToText('dart_no');
    $sourcefileds = url::postToText('source-field2');
    if ($sourcefileds == '' || $sourcefileds == 'undefined' || $sourcefileds == ' ') {
        $sourcefileds = '-';
    }
    $userId = $_SESSION['user']['dashboardLogin'];
    $totalOpt = 2;
    $optsCompleted = 0;

    $mpriv = checkModulePrivilege('ad-hoc', 2);
    if (!$mpriv) {
        exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
    }

    $dbo = $pdo->prepare("Insert into `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` (`user_id` ,`name`,`dartno`) VALUES (?, ? ,?)");

    try {
        $dbo->execute([$userId, $name, $dart]);
    } catch (\Exception $e) {
        $pdo->rollback();
        if (stripos($e->getMessage(), 'duplicate')) {
            exit(json_encode(['success' => false, 'message' => 'The name alredy exists, choose a different name']));
        }
    }

    $lastInsertId = $pdo->lastInsertId();

    if (is_numeric($lastInsertId) && intval($lastInsertId) > 0)
        $optsCompleted++;

    $insertSql = "INSERT INTO `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filter_opearations` (`adhoc_event_filter_id`, `filter_name`, `filter_operation`, `filter_value`, `source_fields` ,`dartno`) VALUES ";
    $bindings = [];
    $insertValueArray = [];
    $hasValues = false;
    $c = 0;

    foreach (url::postToAny('filter-name2') as $eachFilterName) {
        $eachFilterName = strip_tags($eachFilterName);
        $eachFilterOperator = isset($_POST['filter-operator2'][$c]) ? strip_tags($_POST['filter-operator2'][$c]) : null;
        $eachFilterValue = isset($_POST['filter-value2'][$c]) ? strip_tags($_POST['filter-value2'][$c]) : null;

        if (!is_null($eachFilterName) && !is_null($eachFilterOperator) && !is_null($eachFilterValue)) {
            $hasValues = true;

            $bindings[] = (int) $lastInsertId;
            $bindings[] = $eachFilterName;
            $bindings[] = $eachFilterOperator;
            $bindings[] = $eachFilterValue;
            $bindings[] = $sourcefileds;
            $bindings[] = $dart;
            $insertValueArray[$c] = "(?, ?, ?, ?, ?, ?)";
        }
        $c++;
    }

    if ($hasValues) {
        $insertSql .= implode(",", $insertValueArray);
        $dbo = $pdo->prepare($insertSql);
        $optsCompleted++;
        try {
            $dbo->execute($bindings);
        } catch (\Exception $e) {
            $pdo->rollback();
            exit(json_encode(['success' => false, 'message' => 'The name alredy exists, choose a different name']));
        }
    }

    if ($optsCompleted >= $totalOpt) {
        $pdo->commit();
    }

    $auditdata = ['Created Event Filter'];
    auditInformation($auditdata);
    exit(json_encode(['success' => true, 'message' => 'Successfully added a new filter']));
}

function updateEventFilter()
{
    $id = url::postToText('selectedValue');
    $pdo = NanoDB::connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->beginTransaction();
    $name = url::postToText('fname2edit');
    $dart = url::postToText('dart_no');
    $sourcefileds = url::postToText('source-field2edit');
    if ($sourcefileds == '' || $sourcefileds == 'undefined' || $sourcefileds == ' ') {
        $sourcefileds = '-';
    }
    $userId = $_SESSION['user']['dashboardLogin'];
    $totalOpt = 2;
    $optsCompleted = 0;

    $mpriv = checkModulePrivilege('ad-hoc', 2);
    if (!$mpriv) {
        exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
    }

    $sql1 = $pdo->prepare("Select * FROM `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` WHERE id = ? ");
    $sql1->execute([$id]);
    $res1 = $sql1->fetch();
    $newname = url::postToText('fname2edit');
    if ($res1) {
        $sql2 = "update `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` set name=? where id=? and user_id=?";
        $dbo2 = $pdo->prepare($sql2);
        $res = $dbo2->execute([$newname, $id, $userId]);
        $lastInsertId = $pdo->lastInsertId();
    } else {
        $sql2 = "Insert into  `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` (name,id,user_id) VALUES (?,?,?)";
        $dbo2 = $pdo->prepare($sql2);
        $dbo2->execute([$newname, $id, $userId]);
        $lastInsertId = $pdo->lastInsertId();
    }

    $sql = $pdo->prepare("DELETE FROM `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filter_opearations` WHERE adhoc_event_filter_id = ? ");
    $sql->execute([$id]);
    $res = $pdo->lastInsertId();
    if (is_numeric($lastInsertId) && intval($lastInsertId) > 0)
        $optsCompleted++;


    $insertSql = "INSERT INTO `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filter_opearations` (`adhoc_event_filter_id`, `filter_name`, `filter_operation`, `filter_value`, `source_fields` ,`dartno`) VALUES ";
    $bindings = [];
    $insertValueArray = [];
    $hasValues = false;
    $c = 0;
    foreach (url::postToAny('filter-name2edit') as $eachFilterName) {
        $eachFilterName = strip_tags($eachFilterName);
        $eachFilterOperator = isset($_POST['filter-operator2edit'][$c]) ? strip_tags($_POST['filter-operator2edit'][$c]) : null;
        $eachFilterValue = isset($_POST['filter-value2edit'][$c]) ? strip_tags($_POST['filter-value2edit'][$c]) : null;
        if (!is_null($eachFilterName) && !is_null($eachFilterOperator) && !is_null($eachFilterValue)) {
            $hasValues = true;

            $bindings[] = (int) $id;
            $bindings[] = $eachFilterName;
            $bindings[] = $eachFilterOperator;
            $bindings[] = $eachFilterValue;
            $bindings[] = $sourcefileds;
            $bindings[] = $dart;
            $insertValueArray[$c] = "(?, ?, ?, ?, ?, ?)";
        }
        $c++;
    }

    if ($hasValues) {
        $insertSql .= implode(",", $insertValueArray);
        $dbo = $pdo->prepare($insertSql);
        $res = $dbo->execute($bindings);
        $pdo->commit();
        $optsCompleted++;
    }

    if ($optsCompleted >= $totalOpt) {
        $pdo->commit();
    }

    $auditdata = ['Updated Event Filter'];
    auditInformation($auditdata);
    exit(json_encode(['success' => true, 'message' => 'Successfully added a new filter']));
}

function exportAssetBackups()
{
    $pdo = NanoDB::connect();
    $searchType = $_SESSION['searchType'];
    $searchVal = 'JSFB_Desktop';
    $likeField = url::issetInGet('filter') && !url::isEmptyInGet('filter') ? url::getToText('filter') : false;
    $source = [];
    $types = '';

    $mpriv = checkModulePrivilege('ad-hoc', 2);
    if (!$mpriv) {
        exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
    }

    $createWildcardSegment = function ($fieldName, $value) {
        if ($value != '*') {
            $likeQuery = '{
          "wildcard": {
                  "' . $fieldName . '": {
              "value" : "*' . $value . '*"
            }
          }
        }';
        } else {
            $likeQuery = '{
                "wildcard": {
                  "' . $fieldName . '": {
                    "value" : "' . $value . '"
                  }
                }
              }';
        }
        return $likeQuery;
    };

    $createMatchSegment = function ($fieldName, $value) {
        $query = '{
          "match": {
            "' . $fieldName . '.keyword": "' . $value . '"
          }
        }';

        return $query;
    };

    $createMatchPhrase = function ($fieldName, $value, $searchVal) {
        $query = '{
          "match_phrase": {
            "' . $fieldName . '": "' . $value . '"
          }
        },{
				"match": {
					"site": "' . $searchVal . '"
				}
}';
        return $query;
    };

    if (url::issetInGet('type') || !url::isEmptyInGet('type')) {
        $type = url::getToText('type');

        if (strpos($type, ",")) {
            $types = '';
            $typesAr = [];
            $types = $source = explode(",", $type);

            foreach ($types as $eachTypes) {
                $typesAr[] = '{"exists": {"field": "' . $eachTypes . '"}}';
            }

            $types = implode(",", $typesAr);
        } else {
            $source = [$type];
            $types = '{"exists": {"field": "' . $type . '"}}';
        }
    }


    $cQ = function ($sourceC, $types, $where, $mustNot = null) {

        $query = '{
            "_source" : [' . implode(",", $sourceC) . '],
            "query": {
              "bool": {
	      "must": [' . $types . ',
	      ' . $where . ']';

        if (!is_null($mustNot))
            $query .= ',"must_not": [' . $mustNot . ']';
        if (!is_null($matchPhraseArray))
            $query .= ',"must": ' . $matchPhraseArray . '';
        $query .= '}
            },
            "sort" : [
                    { "TimeStamp" : {"order" : "desc"}}
            ]
          }';

        return $query;
    };


    $isGroup = false;

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
        $isGroup = true;
    }

    $mustNot = null;

    if (url::issetInGet('fid') && url::isNumericInGet('fid')) {
        $userId = $_SESSION['user']['dashboardLogin'];
        $details = filterOperationsDetails(url::getToText('fid'), $userId);
        $mustArray = $mustNotArray = $containsArray = [];

        if (isset($details[0]['source_fields'])) {
            $source = strpos($details[0]['source_fields'], ",") ? explode(",", $details[0]['source_fields']) : [$details[0]['source_fields']];
        }

        foreach ($details as $eachDetails) {
            if (is_numeric($eachDetails['filter_operation'])) {
                $filterOperation = intval($eachDetails['filter_operation']);
                if ($filterOperation == 1) {
                    $mustArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 2) {
                    $mustNotArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 3) {
                    $containsArray[] = $createWildcardSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 4) {
                    $matchPhraseArray[] = $createMatchPhrase($eachDetails['filter_name'], $eachDetails['filter_value'], $searchVal);
                }
            }
        }

        if (safe_sizeof($mustNotArray) > 0)
            $mustNot = implode(",", $mustNotArray);
        if (safe_sizeof($matchPhraseArray) > 0) {
            $matchphrase = empty($types) ? '' : ',';
            $matchphrase .= implode(",", $matchPhraseArray);
            $types .= $matchphrase;
        }
        if (safe_sizeof($mustArray) > 0) {
            $must = empty($types) ? '' : ',';
            $must .= implode(",", $mustArray);
            $types .= $must;
        }

        if (safe_sizeof($containsArray) > 0) {
            $contains = empty($types) ? '' : ',';
            $contains .= implode(",", $containsArray);
            $types .= $contains;
        }
    } else if ($likeField) {
        $likeQuery = empty($types) ? '' : ',';
        $likeQuery .= '{
          "wildcard": {
            "installedprogramswithversions.installedsoftwarenames": {
              "value" : "*' . $likeField . '*"
            }
          }
        }';

        $types .= $likeQuery;
    }

    if (url::issetInGet('fid') || url::isNumericInGet('fid')) {
        $initsource = $source;
        $source = array_merge($source, ["machine", "site", "TimeStamp"]);
    }
    $sourceC = [];

    foreach ($source as $eachSource) {
        $sourceC[] = '"' . $eachSource . '"';
    }
    $query = $cQ($sourceC, $types, $where, $mustNot);
    $exportAssoc = [];

    global $elastic_url;
    global $elastic_username;
    global $elastic_password;
    $searchname = strtolower($searchVal);
    $indexName = 'assets_' . $searchname . '*/_search';
    $url = $elastic_url . $indexName;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?scroll=10m&size=200000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    $headers = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader()
    );
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);
    $result = curl_exec($ch);

    if ($errorno) {
        exit($errorno);
    }

    $result = safe_json_decode($result, true);
    $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
    $scroll_id = $result['_scroll_id'];
    $scroll_size = $result['hits']['total']['value'];

    $timeStamps = [];
    $finalTimestamps = [];
    $latestTimestamp = [];
    if (!$isGroup) {
        foreach ($hits as $key => $val) {
            $time = $val['TimeStamp'];
            $machine = $val['machine'];
            $timeStamps[$machine] = $time;
        }

        $timest = array_unique($timeStamps);
        foreach ($timest as $vals) {
            array_push($latestTimestamp, $vals);
        }

        $searchname = strtolower($searchVal);
        $indexName = 'assets_' . $searchname . '*/_search/scroll';

        if ($timest) {
            foreach ($timest as $vals) {
                array_push($latestTimestamp, $vals);
            }
            $indexName = 'assets_' . $searchname . '*/_search';
            $url = $elastic_url . $indexName;
            $latestTimestamp = implode(',', $latestTimestamp);
            $where .= ',{"terms" : {"TimeStamp" : [' . $latestTimestamp . ']}}';
            $query = $cQ($sourceC, $types, $where);
            curl_setopt($ch, CURLOPT_URL, $url . '?scroll=10m&size=200000');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            $headers = array(
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
            );
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $errorno = curl_errno($ch);
            $result = curl_exec($ch);
            if ($errorno) {
                exit($errorno);
            }

            $result = safe_json_decode($result, true);
            $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;

            if ($hits) {
                foreach ($hits as $key => $eachHits) {
                    foreach ($eachHits as $inKey => $inEachHits) {
                        $masterKey = $eachHits['machine'];
                        if (is_array($inEachHits)) {
                            foreach ($initsource as $sourceval) {
                                $sourcekey = explode('.', $sourceval)[1];
                                $exportAssoc[$key][$sourcekey] = empty($inEachHits[$sourcekey]) ? 'NA' : $inEachHits[$sourcekey];
                            }
                        } else {
                            $exportAssoc[$key][$inKey] = ($inKey == 'TimeStamp') ? date('Y-m-d H:i:s', $inEachHits) : $inEachHits;
                        }
                    }
                }
            }
        }
    } else {
        foreach ($hits as $key => $val) {
            $time = $val['TimeStamp'];
            $machine = $val['machine'];
            $timeStamps[$machine] = $time;
        }
        $timest = array_unique($timeStamps);

        foreach ($timest as $vals) {
            array_push($latestTimestamp, $vals);
        }
        $indexName = 'assets*/_search';
        $url = $elastic_url . $indexName;
        $latestTimestamp = implode(',', $latestTimestamp);
        $where .= ',{"terms" : {"TimeStamp" : [' . $latestTimestamp . ']}}';
        $query = $cQ($sourceC, $types, $where);
        curl_setopt($ch, CURLOPT_URL, $url . '?scroll=10m&size=200000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $errorno = curl_errno($ch);
        $result = curl_exec($ch);
        if ($errorno) {
            exit($errorno);
        }

        $result = safe_json_decode($result, true);
        $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
        if ($hits) {
            foreach ($hits as $key => $eachHits) {
                foreach ($eachHits as $inKey => $inEachHits) {
                    $masterKey = $eachHits['machine'];
                    if (is_array($inEachHits)) {
                        foreach ($initsource as $sourceval) {
                            $sourcekey = explode('.', $sourceval)[1];
                            $exportAssoc[$key][$sourcekey] = empty($inEachHits[$sourcekey]) ? 'NA' : $inEachHits[$sourcekey];
                        }
                    } else {
                        $exportAssoc[$key][$inKey] = ($inKey == 'TimeStamp') ? date('Y-m-d H:i:s', $inEachHits) : $inEachHits;
                    }
                }
            }
        }
    }
    $url = $elastic_url;
    $query = '{
            "scroll_id" : "' . $scroll_id . '"
        }';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '/_search/scroll');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    $headers = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
    );
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);
    $result = curl_exec($ch);
    if ($errorno) {
        exit($errorno);
    }
    curl_close($ch);
    $exportAssoc = array_unique($exportAssoc, SORT_REGULAR);
    exportAssetDetails($exportAssoc, 'Asset');
}

function putEventDetailToCSV($hits, $initsource, $fileExits = true)
{
    $exportAssoc = [];
    $titleArray = array();
    $dataArray = array();
    $finalArray = array();
    global $totalHits;

    if ($fileExits) {
        $asset_file_data = file_get_contents('event_export_data.json');
        $finalArray = (array) safe_json_decode($asset_file_data);
    } else {
        unlink('event_export_data.json');
    }

    foreach ($hits as $key => $eachHits) {
        foreach ($eachHits as $inKey => $inEachHits) {
            if (is_array($inEachHits)) {
                foreach ($initsource as $sourceval) {
                    $sourcekey = explode('.', $sourceval)[1];
                    if ($inEachHits[$sourcekey] == '0' || $inEachHits[$sourcekey] == 0) {
                        $exportAssoc[$key][$sourcekey] = $inEachHits[$sourcekey];
                    } else if ($inEachHits[$sourcekey] == '') {
                        $exportAssoc[$key][$sourcekey] = 'NA';
                    } else {
                        $exportAssoc[$key][$sourcekey] = $inEachHits[$sourcekey];
                    }
                }
            } else {
                $exportAssoc[$key][$inKey] = ($inKey == 'ctime') ? date('Y-m-d H:i:s', $inEachHits) : $inEachHits;
            }
        }
    }


    $columnArray = safe_array_keys($exportAssoc[safe_array_keys($exportAssoc)[0]]);
    foreach ($columnArray as $key => $eachColumns) {
        if ($eachColumns != '') {
            array_push($titleArray, $eachColumns);
        }
    }
    $i = 1;
    foreach ($exportAssoc as $key => $eachAssoc) {
        array_push($dataArray, $eachAssoc);
    }
    foreach ($exportAssoc as $key => $val) {
        $eachDataArr = array();
        foreach ($titleArray as $tval) {
            if (in_array($tval, safe_array_keys($val))) {
                $eachDataArr[$tval] = $val[$tval];
            } else {
                $eachDataArr[$tval] = 'NA';
            }
        }
        $finalArray[] = $eachDataArr;
    }
    $finalArray = array_unique($finalArray, SORT_REGULAR);
    file_put_contents('event_export_data.json', json_encode($finalArray));
}

function putAssestDetailtToCSV($hits, $initsource, $fileExits = true)
{
    $exportAssoc = [];
    $titleArray = array();
    $dataArray = array();
    $finalArray = array();
    global $totalHits;

    if ($fileExits) {
        $asset_file_data = file_get_contents('asset_export_data.json');
        $finalArray = (array) safe_json_decode($asset_file_data);
    } else {
        unlink('asset_export_data.json');
    }

    foreach ($hits as $key => $eachHits) {
        $totalHits += 1;
        foreach ($eachHits as $inKey => $inEachHits) {
            if (is_array($inEachHits)) {
                foreach ($initsource as $sourceval) {
                    $sourcekey = explode('.', $sourceval)[1];
                    $exportAssoc[$key][$sourcekey] = empty($inEachHits[$sourcekey]) ? 'NA' : $inEachHits[$sourcekey];
                }
            } else {
                $exportAssoc[$key][$inKey] = ($inKey == 'TimeStamp') ? date('Y-m-d H:i:s', $inEachHits) : $inEachHits;
            }
        }
    }


    $columnArray = safe_array_keys($exportAssoc[safe_array_keys($exportAssoc)[0]]);
    foreach ($columnArray as $key => $eachColumns) {
        if ($eachColumns != '') {
            array_push($titleArray, $eachColumns);
        }
    }
    $i = 1;
    foreach ($exportAssoc as $key => $eachAssoc) {
        array_push($dataArray, $eachAssoc);
    }
    foreach ($exportAssoc as $key => $val) {
        $eachDataArr = array();
        foreach ($titleArray as $tval) {
            if (in_array($tval, safe_array_keys($val))) {
                $eachDataArr[$tval] = $val[$tval];
            } else {
                $eachDataArr[$tval] = 'NA';
            }
        }
        $finalArray[] = $eachDataArr;
    }
    $finalArray = array_unique($finalArray, SORT_REGULAR);
    file_put_contents('asset_export_data.json', json_encode($finalArray));
}

function exportAsset($searchType, $searchVal, $aid, $fid, $queryname, $filter = '')
{
    $pdo = NanoDB::connect();
    $likeField = isset($filter) && !empty($filter) ? strip_tags($filter) : false;
    $source = [];
    $types = '';



    $createWildcardSegment = function ($fieldName, $value) {
        if ($value != '*') {
            $likeQuery = '{
          "wildcard": {
                  "' . $fieldName . '": {
              "value" : "*' . $value . '*"
            }
          }
        }';
        } else {
            $likeQuery = '{
                "wildcard": {
                  "' . $fieldName . '": {
                    "value" : "' . $value . '"
                  }
                }
              }';
        }
        return $likeQuery;
    };

    $createMatchSegment = function ($fieldName, $value) {
        $query = '{
          "match": {
            "' . $fieldName . '.keyword": "' . $value . '"
          }
        }';

        return $query;
    };

    $createMatchPhrase = function ($fieldName, $value, $searchVal) {
        $query = '{
          "match_phrase": {
            "' . $fieldName . '": "' . $value . '"
          }
        },{
				"match": {
					"site": "' . $searchVal . '"
				}
}';
        return $query;
    };

    if (url::issetInGet('type') || !url::isEmptyInGet('type')) {
        $type = url::getToText('type');

        if (strpos($type, ",")) {
            $types = '';
            $typesAr = [];
            $types = $source = explode(",", $type);

            foreach ($types as $eachTypes) {
                $typesAr[] = '{"exists": {"field": "' . $eachTypes . '"}}';
            }

            $types = implode(",", $typesAr);
        } else {
            $source = [$type];
            $types = '{"exists": {"field": "' . $type . '"}}';
        }
    }


    $cQ = function ($sourceC, $types, $where, $mustNot = null) {

        $query = '{
            "_source" : [' . implode(",", $sourceC) . '],
            "query": {
              "bool": {
	      "must": [' . $types . ',
	      ' . $where . ']';

        if (!$types || $types == '') {
            $query = '{
            "_source" : [' . implode(",", $sourceC) . '],
            "query": {
              "bool": {
	      "must": [' . $where . ']';
        }


        if (!is_null($mustNot))
            $query .= ',"must_not": [' . $mustNot . ']';
        if (!is_null($matchPhraseArray))
            $query .= ',"must": ' . $matchPhraseArray . '';
        $query .= '}
            },
            "sort" : [
                    { "TimeStamp" : {"order" : "desc"}}
            ]
          }';

        return $query;
    };


    $isGroup = false;

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
        $isGroup = true;
    }

    $mustNot = null;

    if (isset($fid) && is_numeric($fid)) {
        $userId = $_SESSION['user']['dashboardLogin'];
        $details = filterOperationsDetails(strip_tags($fid), $userId);
        $mustArray = $mustNotArray = $containsArray = [];

        if (isset($details[0]['source_fields'])) {
            $source = strpos($details[0]['source_fields'], ",") ? explode(",", $details[0]['source_fields']) : [$details[0]['source_fields']];
        }

        foreach ($details as $eachDetails) {
            if (is_numeric($eachDetails['filter_operation'])) {
                $filterOperation = intval($eachDetails['filter_operation']);
                if ($filterOperation == 1) {
                    $mustArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 2) {
                    $mustNotArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 3) {
                    $containsArray[] = $createWildcardSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 4) {
                    $matchPhraseArray[] = $createMatchPhrase($eachDetails['filter_name'], $eachDetails['filter_value'], $searchVal);
                }
            }
        }

        if (safe_sizeof($mustNotArray) > 0)
            $mustNot = implode(",", $mustNotArray);
        if (safe_sizeof($matchPhraseArray) > 0) {
            $matchphrase = empty($types) ? '' : ',';
            $matchphrase .= implode(",", $matchPhraseArray);
            $types .= $matchphrase;
        }
        if (safe_sizeof($mustArray) > 0) {
            $must = empty($types) ? '' : ',';
            $must .= implode(",", $mustArray);
            $types .= $must;
        }

        if (safe_sizeof($containsArray) > 0) {
            $contains = empty($types) ? '' : ',';
            $contains .= implode(",", $containsArray);
            $types .= $contains;
        }
    } else if ($likeField) {
        $likeQuery = empty($types) ? '' : ',';
        $likeQuery .= '{
          "wildcard": {
            "installedprogramswithversions.installedsoftwarenames": {
              "value" : "*' . $likeField . '*"
            }
          }
        }';

        $types .= $likeQuery;
    }

    if (isset($fid) || is_numeric($fid)) {
        $initsource = $source;
        $source = array_merge($source, ["machine", "site", "TimeStamp"]);
    }
    $sourceC = [];

    foreach ($source as $eachSource) {
        $sourceC[] = '"' . $eachSource . '"';
    }
    $query = $cQ($sourceC, $types, $where, $mustNot);
    $exportAssoc = [];

    global $elastic_url;
    global $elastic_username;
    global $elastic_password;
    $searchname = strtolower($searchVal);
    $indexName = 'assets_' . $searchname . '*/_search';
    $url = $elastic_url . $indexName;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?scroll=10m&size=10000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    $headers = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
    );
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);
    $result = curl_exec($ch);

    if ($errorno) {
        exit($errorno);
    }

    $result = safe_json_decode($result, true);
    $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
    $scroll_id = isset($result['_scroll_id']) ? $result['_scroll_id'] : null;
    $scroll_size = $result['hits']['total']['value'];


    $timeStamps = [];
    $finalTimestamps = [];
    $latestTimestamp = [];
    $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;


    $totalHits = 0;
    putAssestDetailtToCSV($hits, $initsource, false);

    $i = 1;
    while ($scroll_id) {
        $query = '{
            "scroll" : "1m",
            "scroll_id" : "' . $scroll_id . '"
        }';

        $ch = curl_init();
        $elURL = $elastic_url . '_search/scroll';
        curl_setopt($ch, CURLOPT_URL, $elURL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Basic " . base64_encode("writer:ut@AZ$5Ra?JA9!mwz");

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $errorno = curl_errno($ch);
        $result = curl_exec($ch);

        if ($errorno) {
            exit($errorno);
        }
        $result = safe_json_decode($result, true);
        $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : array();
        if (safe_count($hits) < 1) {
            break;
        }
        $scroll_id = isset($result['_scroll_id']) && $result['_scroll_id'] ? $result['_scroll_id'] : null;
        putAssestDetailtToCSV($hits, $initsource);
        $i++;
    }


    $filename = 'Asset_' . $queryname . '_' . time() . '.csv';

    $fp = fopen($filename, 'wb');

    $finalArray = file_get_contents('asset_export_data.json');
    $finalArray = safe_json_decode($finalArray);
    $finalArray = (array) $finalArray;


    $columnName = safe_array_keys((array) $finalArray[0]);

    fputcsv($fp, $columnName, ',');

    foreach ($finalArray as $line) {
        $line = (array) $line;
        $item = array();
        foreach ($columnName as $col) {
            $item[] = isset($line[$col]) ? $line[$col] : "N/A";
        }
        fputcsv($fp, array_values($item), ',');
    }

    $status = 'Completed';
    $now = time();
    $queryInsert = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "agent.adhocInfoPortal set endTime = ?, fileName = ?, status = ?,cronstatus = ? where id = ?");
    $queryInsert->execute([$now, $filename, $status, 2, $aid]);

    fclose($fp);
    return $filename;
}

function exportEvent($searchType, $searchVal, $fid, $queryname, $from, $to, $filter = '')
{
    $pdo = NanoDB::connect();
    $likeField = isset($filter) && !empty($filter) ? strip_tags($filter) : false;
    $source = [];
    $types = '';



    $sql = "SELECT * from " . $GLOBALS['PREFIX'] . "agent.adhoc_event_filter_opearations where adhoc_event_filter_id = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([$fid]);
    $data = $stm->fetch(PDO::FETCH_ASSOC);
    $filter_name = $data['filter_name'];

    $createWildcardSegment = function ($fieldName, $value) {
        if ($value != '*') {
            $likeQuery = '{
                "wildcard": {
                  "' . $fieldName . '": {
                    "value" : "*' . $value . '*"
                  }
                }
              }';
        } else {
            $likeQuery = '{
                "wildcard": {
                  "' . $fieldName . '": {
                    "value" : "' . $value . '"
                  }
                }
              }';
        }
        return $likeQuery;
    };

    $createMatchSegment = function ($fieldName, $value) {
        $query = '{
          "match": {
            "' . $fieldName . '.keyword": "' . $value . '"
          }
        }';

        return $query;
    };

    $createMatchPhrase = function ($fieldName, $value, $searchVal) {
        $query = '{
          "match_phrase": {
            "' . $fieldName . '": "' . $value . '"
          }
        },{
				"match": {
					"site": "' . $searchVal . '"
				}
}';
        return $query;
    };

    if (url::issetInGet('type') || !url::isEmptyInGet('type')) {
        $type = url::getToText('type');

        if (strpos($type, ",")) {
            $types = '';
            $typesAr = [];
            $types = $source = explode(",", $type);

            foreach ($types as $eachTypes) {
                $typesAr[] = '{"exists": {"field": "' . $eachTypes . '"}}';
            }

            $types = implode(",", $typesAr);
        } else {
            $source = [$type];
            $types = '{"exists": {"field": "' . $type . '"}}';
        }
    }

    $cQ = function ($sourceC, $types, $where, $mustNot = null) {

        $query = '{
            "_source" : [' . implode(",", $sourceC) . '],
            "query": {
              "bool": {
	      "must": [' . $types . ',
	      ' . $where . ']';

        if (!is_null($mustNot))
            $query .= ',"must_not": [' . $mustNot . ']';
        if (!is_null($matchPhraseArray))
            $query .= ',"must": ' . $matchPhraseArray . '';
        $query .= '}
            },
            "sort" : [
                    { "ctime" : {"order" : "desc"}}
            ]
          }';

        return $query;
    };

    $isGroup = false;

    if ($searchType == 'Sites') {
        $where = '{"terms" : {"site.keyword" : ["' . $searchVal . '"]}}';
        $checkLevel = $searchVal;
        $LevelVal = "site";
    } else if ($searchType == 'ServiceTag') {
        $where = '{"terms" : {"machine.keyword" : ["' . $searchVal . '"]}}';
        $checkLevel = $searchVal;
        $LevelVal = "machine";
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, $searchVal, $searchType);
        $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
        $whereArray = [];
        foreach ($machines as $row) {
            $whereArray[] = '"' . $row . '"';
        }
        $checkLevel = implode(",", $whereArray);
        $LevelVal = "machine";
        $where = '{"terms" : {"machine.keyword" : [' . implode(",", $whereArray) . ']}}';
        $isGroup = true;
    }
    $mustNot = null;

    if (url::issetInGet('fid') && url::isNumericInGet('fid')) {
        $userId = $_SESSION['user']['dashboardLogin'];
        $details = filtereventOperationsDetails(url::getToText('fid'), $userId);
        $mustArray = $mustNotArray = $containsArray = [];
        if (isset($details[0]['source_fields'])) {
            $source = strpos($details[0]['source_fields'], ",") ? explode(",", $details[0]['source_fields']) : [$details[0]['source_fields']];
        }

        foreach ($details as $eachDetails) {
            if (is_numeric($eachDetails['filter_operation'])) {
                $filterOperation = intval($eachDetails['filter_operation']);
                if ($filterOperation == 1) {
                    $mustArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                    $mustArray[] = $createMatchSegment("scrip", $eachDetails['dartno']);
                    $mustArray[] = $createMatchSegment($LevelVal, $checkLevel);
                } else if ($filterOperation == 2) {
                    $mustNotArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 3) {
                    $containsArray[] = $createWildcardSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 4) {
                    $matchPhraseArray[] = $createMatchPhrase($eachDetails['filter_name'], $eachDetails['filter_value'], $searchVal);
                }
            } else {
                $mustArray[] = $createMatchSegment("scrip", $eachDetails['dartno']);
            }
        }
        if (safe_sizeof($mustNotArray) > 0)
            $mustNot = implode(",", $mustNotArray);
        if (safe_sizeof($mustArray) > 0) {
            $must = empty($types) ? '' : ',';
            $must .= implode(",", $mustArray);
            $types .= $must;
        }
        if (safe_sizeof($matchPhraseArray) > 0) {
            $matchphrase = empty($types) ? '' : ',';
            $matchphrase .= implode(",", $matchPhraseArray);
            $types .= $matchphrase;
        }
        if (safe_sizeof($containsArray) > 0) {
            $contains = empty($types) ? '' : ',';
            $contains .= implode(",", $containsArray);
            $types .= $contains;
        }
    }

    if (url::issetInGet('fid') || url::isNumericInGet('fid')) {
        $initsource = $source;
        $source = array_merge($source, ["machine", "site", "ctime"]);
    }
    $sourceC = [];

    foreach ($source as $eachSource) {
        $sourceC[] = '"' . $eachSource . '"';
    }

    $where .= ',{
					"range": {
						"ctime": {
							"gte": "' . $from . '",
							"lte": "' . $to . '"
            }
					}
				}';

    $query = $cQ($sourceC, $types, $where, $mustNot);
    $exportAssoc = [];

    global $elastic_url;
    global $elastic_username;
    global $elastic_password;

    $url = $elastic_url . 'events*/_search';
    $size = $isGroup ? 10000 : 1;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?scroll=10m&size=10000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    $headers = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
    );
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);
    $result = curl_exec($ch);

    if ($errorno) {
        exit($errorno);
    }

    $result = safe_json_decode($result, true);
    $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
    $scroll_id = isset($result['_scroll_id']) ? $result['_scroll_id'] : null;
    $timeStamps = [];
    $latestTimestamp = [];

    putEventDetailToCSV($hits, $initsource, false);

    $i = 1;
    while ($scroll_id) {
        $query = '{
            "scroll" : "1m",
            "scroll_id" : "' . $scroll_id . '"
        }';

        $ch = curl_init();
        $elURL = $elastic_url . '_search/scroll';
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $elURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $errorno = curl_errno($ch);
        $result = curl_exec($ch);

        if ($errorno) {
            exit($errorno);
        }
        $result = safe_json_decode($result, true);
        $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : array();
        if (safe_count($hits) < 1) {
            break;
        }
        $scroll_id = isset($result['_scroll_id']) && $result['_scroll_id'] ? $result['_scroll_id'] : null;
        putEventDetailToCSV($hits, $initsource);
        $i++;
    }


    $filename = 'Event_' . $queryname . '_' . time() . '.csv';
    $fp = fopen($filename, 'wb');

    $finalArray = file_get_contents('event_export_data.json');
    $finalArray = safe_json_decode($finalArray);
    $finalArray = (array) $finalArray;

    $columnName = safe_array_keys((array) $finalArray[0]);

    fputcsv($fp, $columnName, ',');

    foreach ($finalArray as $line) {
        $line = (array) $line;
        $item = array();
        foreach ($columnName as $col) {
            $item[] = isset($line[$col]) ? $line[$col] : "N/A";
        }
        fputcsv($fp, array_values($item), ',');
    }
    fclose($fp);

    return $filename;
}

function exportEventBKP()
{
    $fid = url::getToText('fid');
    $pdo = NanoDB::connect();
    $searchType = $_SESSION['searchType'];
    $from = strtotime(url::getToText('from'));
    $to = strtotime(url::getToText('to'));
    $searchVal = $_SESSION['searchValue'];
    $likeField = url::issetInGet('filter') && !url::isEmptyInGet('filter') ? url::getToText('filter') : false;
    $source = [];
    $types = '';

    $mpriv = checkModulePrivilege('ad-hoc', 2);
    if (!$mpriv) {
        exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
    }

    $sql = "SELECT * from " . $GLOBALS['PREFIX'] . "agent.adhoc_event_filter_opearations where adhoc_event_filter_id = ?";
    $stm = $pdo->prepare($sql);
    $stm->execute([$fid]);
    $data = $stm->fetch(PDO::FETCH_ASSOC);
    $filter_name = $data['filter_name'];

    $createWildcardSegment = function ($fieldName, $value) {
        if ($value != '*') {
            $likeQuery = '{
                "wildcard": {
                  "' . $fieldName . '": {
                    "value" : "*' . $value . '*"
                  }
                }
              }';
        } else {
            $likeQuery = '{
                "wildcard": {
                  "' . $fieldName . '": {
                    "value" : "' . $value . '"
                  }
                }
              }';
        }
        return $likeQuery;
    };

    $createMatchSegment = function ($fieldName, $value) {
        $query = '{
          "match": {
            "' . $fieldName . '.keyword": "' . $value . '"
          }
        }';

        return $query;
    };

    $createMatchPhrase = function ($fieldName, $value, $searchVal) {
        $query = '{
          "match_phrase": {
            "' . $fieldName . '": "' . $value . '"
          }
        },{
				"match": {
					"site": "' . $searchVal . '"
				}
}';
        return $query;
    };

    if (url::issetInGet('type') || !url::isEmptyInGet('type')) {
        $type = url::getToText('type');

        if (strpos($type, ",")) {
            $types = '';
            $typesAr = [];
            $types = $source = explode(",", $type);

            foreach ($types as $eachTypes) {
                $typesAr[] = '{"exists": {"field": "' . $eachTypes . '"}}';
            }

            $types = implode(",", $typesAr);
        } else {
            $source = [$type];
            $types = '{"exists": {"field": "' . $type . '"}}';
        }
    }

    $cQ = function ($sourceC, $types, $where, $mustNot = null) {

        $query = '{
            "_source" : [' . implode(",", $sourceC) . '],
            "query": {
              "bool": {
	      "must": [' . $types . ',
	      ' . $where . ']';

        if (!is_null($mustNot))
            $query .= ',"must_not": [' . $mustNot . ']';
        if (!is_null($matchPhraseArray))
            $query .= ',"must": ' . $matchPhraseArray . '';
        $query .= '}
            },
            "sort" : [
                    { "ctime" : {"order" : "desc"}}
            ]
          }';

        return $query;
    };

    $isGroup = false;

    if ($searchType == 'Sites') {
        $where = '{"terms" : {"site.keyword" : ["' . $searchVal . '"]}}';
        $checkLevel = $searchVal;
        $LevelVal = "site";
    } else if ($searchType == 'ServiceTag') {
        $where = '{"terms" : {"machine.keyword" : ["' . $searchVal . '"]}}';
        $checkLevel = $searchVal;
        $LevelVal = "machine";
    } else {
        $dataScope = UTIL_GetSiteScope_PDO($pdo, $searchVal, $searchType);
        $machines = DASH_GetGroupsMachines_PDO('', $pdo, $dataScope);
        $whereArray = [];
        foreach ($machines as $row) {
            $whereArray[] = '"' . $row . '"';
        }
        $checkLevel = implode(",", $whereArray);
        $LevelVal = "machine";
        $where = '{"terms" : {"machine.keyword" : [' . implode(",", $whereArray) . ']}}';
        $isGroup = true;
    }
    $mustNot = null;

    if (url::issetInGet('fid') && url::isNumericInGet('fid')) {
        $userId = $_SESSION['user']['dashboardLogin'];
        $details = filtereventOperationsDetails(url::getToText('fid'), $userId);
        $mustArray = $mustNotArray = $containsArray = [];
        if (isset($details[0]['source_fields'])) {
            $source = strpos($details[0]['source_fields'], ",") ? explode(",", $details[0]['source_fields']) : [$details[0]['source_fields']];
        }

        foreach ($details as $eachDetails) {
            if (is_numeric($eachDetails['filter_operation'])) {
                $filterOperation = intval($eachDetails['filter_operation']);
                if ($filterOperation == 1) {
                    $mustArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                    $mustArray[] = $createMatchSegment("scrip", $eachDetails['dartno']);
                    $mustArray[] = $createMatchSegment($LevelVal, $checkLevel);
                } else if ($filterOperation == 2) {
                    $mustNotArray[] = $createMatchSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 3) {
                    $containsArray[] = $createWildcardSegment($eachDetails['filter_name'], $eachDetails['filter_value']);
                } else if ($filterOperation == 4) {
                    $matchPhraseArray[] = $createMatchPhrase($eachDetails['filter_name'], $eachDetails['filter_value'], $searchVal);
                }
            } else {
                $mustArray[] = $createMatchSegment("scrip", $eachDetails['dartno']);
            }
        }
        if (safe_sizeof($mustNotArray) > 0)
            $mustNot = implode(",", $mustNotArray);
        if (safe_sizeof($mustArray) > 0) {
            $must = empty($types) ? '' : ',';
            $must .= implode(",", $mustArray);
            $types .= $must;
        }
        if (safe_sizeof($matchPhraseArray) > 0) {
            $matchphrase = empty($types) ? '' : ',';
            $matchphrase .= implode(",", $matchPhraseArray);
            $types .= $matchphrase;
        }
        if (safe_sizeof($containsArray) > 0) {
            $contains = empty($types) ? '' : ',';
            $contains .= implode(",", $containsArray);
            $types .= $contains;
        }
    }

    if (url::issetInGet('fid') || url::isNumericInGet('fid')) {
        $initsource = $source;
        $source = array_merge($source, ["machine", "site", "ctime"]);
    }
    $sourceC = [];

    foreach ($source as $eachSource) {
        $sourceC[] = '"' . $eachSource . '"';
    }
    $query = $cQ($sourceC, $types, $where, $mustNot);
    $exportAssoc = [];

    global $elastic_url;
    global $elastic_username;
    global $elastic_password;

    $url = $elastic_url . 'events*/_search';
    $size = $isGroup ? 10000 : 1;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url . '?size=10000');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    $headers = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
    );
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);
    $result = curl_exec($ch);

    if ($errorno) {
        exit($errorno);
    }

    $result = safe_json_decode($result, true);
    $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
    $timeStamps = [];
    $latestTimestamp = [];

    if (!$isGroup) {

        foreach ($hits as $key => $val) {
            $time = $val['ctime'];
            $machine = $val['machine'];
            $timeStamps[$machine] = $time;
        }


        $where .= ',{
					"range": {
						"ctime": {
							"gte": "' . $from . '",
							"lte": "' . $to . '"
            }
					}
				}';
        $query = $cQ($sourceC, $types, $where);
        curl_setopt($ch, CURLOPT_URL, $url . '?size=10000');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $errorno = curl_errno($ch);
        $result = curl_exec($ch);
        if ($errorno) {
            exit($errorno);
        }
        $result = safe_json_decode($result, true);
        $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
        if ($hits) {
            foreach ($hits as $key => $eachHits) {
                foreach ($eachHits as $inKey => $inEachHits) {
                    if (is_array($inEachHits)) {
                        foreach ($initsource as $sourceval) {
                            $sourcekey = explode('.', $sourceval)[1];
                            if ($inEachHits[$sourcekey] == '0' || $inEachHits[$sourcekey] == 0) {
                                $exportAssoc[$key][$sourcekey] = $inEachHits[$sourcekey];
                            } else if ($inEachHits[$sourcekey] == '') {
                                $exportAssoc[$key][$sourcekey] = 'NA';
                            } else {
                                $exportAssoc[$key][$sourcekey] = $inEachHits[$sourcekey];
                            }
                        }
                    } else {
                        $exportAssoc[$key][$inKey] = ($inKey == 'ctime') ? date('Y-m-d H:i:s', $inEachHits) : $inEachHits;
                    }
                }
            }
        }
    } else {

        foreach ($hits as $key => $val) {
            $time = $val['ctime'];
            $machine = $val['machine'];
            $timeStamps[$machine] = $time;
        }
        $timest = $timeStamps;

        if ($timest) {
            foreach ($timest as $vals) {
                array_push($latestTimestamp, $vals);
            }
            $latestTimestamp = implode(',', $latestTimestamp);
            $where .= ',{
					"range": {
						"ctime": {
							"gte": "' . $from . '",
							"lte": "' . $to . '"
						}
					}
				}';
            $query = $cQ($sourceC, $types, $where);
            curl_setopt($ch, CURLOPT_URL, $url . '?size=10000');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            $headers = array(
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
            );
            $headers[] = "Content-Type: application/json";
            $headers[] = "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $errorno = curl_errno($ch);
            $result = curl_exec($ch);
            if ($errorno) {
                exit($errorno);
            }

            $result = safe_json_decode($result, true);
            $hits = isset($result['hits']['hits']) ? array_column($result['hits']['hits'], '_source') : false;
            if ($hits) {
                foreach ($hits as $key => $eachHits) {
                    foreach ($eachHits as $inKey => $inEachHits) {
                        $masterKey = $eachHits['machine'];
                        if (is_array($inEachHits)) {
                            foreach ($initsource as $sourceval) {
                                $sourcekey = explode('.', $sourceval)[1];
                                if ($inEachHits[$sourcekey] == '0' || $inEachHits[$sourcekey] == 0) {
                                    $exportAssoc[$key][$sourcekey] = $inEachHits[$sourcekey];
                                } else if ($inEachHits[$sourcekey] == '') {
                                    $exportAssoc[$key][$sourcekey] = 'NA';
                                } else {
                                    $exportAssoc[$key][$sourcekey] = $inEachHits[$sourcekey];
                                }
                            }
                        } else {
                            $exportAssoc[$key][$inKey] = ($inKey == 'ctime') ? date('Y-m-d H:i:s', $inEachHits) : $inEachHits;
                        }
                    }
                }
            }
        }
    }
    curl_close($ch);
    exportAssetDetails($exportAssoc, 'Event');
}

function exportAssetDetails($exportAssoc, $type)
{
    $titleArray = array();
    $dataArray = array();
    $finalArray = array();
    $sfinalArray = array();
    if ($type == 'Asset') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Assets.csv"');
    } else {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="Events.csv"');
    }

    $FinalArray = array();
    if (safe_sizeof($exportAssoc)) {
        $columnArray = safe_array_keys($exportAssoc[safe_array_keys($exportAssoc)[0]]);
        foreach ($columnArray as $key => $eachColumns) {
            if ($eachColumns != '') {
                array_push($titleArray, $eachColumns);
            }
        }
        $finalArray[0] = $titleArray;
        $i = 1;
        foreach ($exportAssoc as $key => $eachAssoc) {
            array_push($dataArray, $eachAssoc);
        }
        foreach ($exportAssoc as $key => $val) {
            foreach ($titleArray as $tval) {
                if (in_array($tval, safe_array_keys($val))) {
                    $eachDataArr[$tval] = $val[$tval];
                } else {
                    $eachDataArr[$tval] = 'NA';
                }
            }
            $finalArray[] = $eachDataArr;
        }

        $fp = fopen('php://output', 'wb');
        foreach ($finalArray as $line) {
            fputcsv($fp, $line, ',');
        }
        fclose($fp);
    } else {
        ob_clean();
        $fp = fopen('php://output', 'wb');
        $finalArray = array('No data found');
        foreach ($finalArray as $line) {
            fputcsv($fp, $finalArray);
        }
        fclose($fp);
    }
}

function deleteFilter()
{
    $id = url::requestToText('id');
    $db = pdo_connect();

    $mpriv = checkModulePrivilege('ad-hoc', 2);
    if (!$mpriv) {
        exit(json_encode(['success' => false, 'message' => 'Access Denied!']));
    }

    $sql1 = $db->prepare("delete from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` where id = ?");
    $sql1->execute([$id]);
    $res1 = $db->lastInsertId();

    $sql2 = $db->prepare("delete from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filter_opearations` where adhoc_query_filter_id = ?");
    $sql2->execute([$id]);
    $res2 = $db->lastInsertId();

    $sql3 = $db->prepare("delete from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` where id = ?");
    $sql3->execute([$id]);
    $res3 = $db->lastInsertId();

    $sql4 = $db->prepare("delete from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filter_opearations` where adhoc_event_filter_id = ?");
    $sql4->execute([$id]);
    $res4 = $db->lastInsertId();

    $auditdata = ['Deleted Filter'];
    auditInformation($auditdata);

    echo "success";
}

function fetchAssetfilterData()
{
    $db = pdo_connect();
    $filtersql = $db->prepare("SELECT filter_name FROM " . $GLOBALS['PREFIX'] . "asset.filter ORDER BY filter_name asc");
    $filtersql->execute();
    $filterres = $filtersql->fetchAll(PDO::FETCH_ASSOC);
    return $filterres;
}

function fetchEventfilterData()
{
    $db = pdo_connect();
    $filtersql = $db->prepare("SELECT  filter_name FROM " . $GLOBALS['PREFIX'] . "agent.event_filters ORDER BY filter_name asc");
    $filtersql->execute();
    $filterres = $filtersql->fetchAll(PDO::FETCH_ASSOC);
    return $filterres;
}

function fetchdartfilterData()
{
    $db = pdo_connect();
    $filtersql = $db->prepare("SELECT dartno FROM " . $GLOBALS['PREFIX'] . "agent.event_filters group by dartno");
    $filtersql->execute();
    $filterres = $filtersql->fetchAll(PDO::FETCH_ASSOC);
    return $filterres;
}

function assetOperators()
{
    $filterarray = array();
    $filterarray['1'] = 'Equal';
    $filterarray['2'] = 'Not Equal';
    $filterarray['3'] = 'Contains';
    $filterarray['4'] = 'Match Phrase';

    return $filterarray;
}

function fetch_FilterDetails()
{
    $id = url::getToText('id');
    $type = url::getToText('type');
    $db = pdo_connect();
    $sql = $db->prepare("select * from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filters` where id = ?");
    $sql->execute([$id]);
    $res = $sql->fetch();
    $userid = $res['user_id'];
    $name = $res['name'];

    $filterarray = array();
    $sql2 = $db->prepare("select * from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_query_filter_opearations` where adhoc_query_filter_id = ?");
    $sql2->execute([$id]);
    $res2 = $sql2->fetchAll();
    $c = 0;

    foreach ($res2 as $key => $val) {
        $sourcefields = $val['source_fields'];
        $sourcefields = explode(',', $sourcefields);
        $filtername[$c] = $val['filter_name'];
        $filteroperation[$c] = $val['filter_operation'];
        $filterval[$c] = $val['filter_value'];
        $filterscount = safe_count($filterarray['filtername']);

        $filterarray['name'] = $name;
        $filterarray['sourcefields'] = $sourcefields;

        $OperatorData = assetOperators();
        foreach ($OperatorData as $key => $val) {
            if (in_array($key, $filteroperation)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $str2 .= "<option $selected value=" . $key . ">" . $val . "</option>";
        }

        $filterres = fetchAssetfilterData();
        foreach ($filterres as $vals) {
            if (in_array($vals['filter_name'], $filtername)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $str1 .= "<option data-tokens=\"" . $vals['filter_name'] . "\" $selected value=\"" . $vals['filter_name'] . "\">" . $vals['filter_name'] . "</option>";
        }
        $html .= '<div class="row edit-filter-rows">
                    <div class="col-sm-4">
                        <label>
                            Filter name
                        </label>
                        <select data-live-search="true" data-required="true" data-label="Filter name" class="selectpicker" data-style="btn btn-info" title="Select Filter Name" data-size="3" name="filter-nameedit[]">
                                ' . $str1 . '
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label>
                             Operator
                        </label>
                        <select  data-required="true" data-label="Operator" class="selectpicker" data-style="btn btn-info" title="Select Asset Filter" data-size="3" id="assetOperatoredit" name="filter-operatoredit[]">
                                ' . $str2 . '
                        </select>
                    </div>
                   <div class="col-sm-3">
                       <label>
                            Value
                        </label>
                        <input data-required="true" data-label="Value" value="' . $filterval[$c] . '" class="form-control" type="text" name="filter-valueedit[]"/>
                    </div>
                    <div class="col-sm-1">
                            <i id="add-more-filteredit" class="dt-icons-l tim-icons icon-simple-add r-ic-plain"></i>
                            <i class="dt-icons-l tim-icons icon-simple-remove r-ic-plain" style="display:none" onclick="removeFilterGridedit($(this))"></i>
                    </div>
                    <input style="display:none" type="submit" class="button" value="create"/>
                    </div>';

        $c++;
    }

    $filterres = fetchAssetfilterData();
    $sourceValues = $filterarray['sourcefields'];

    foreach ($filterres as $vals) {
        if (in_array($vals['filter_name'], $sourceValues)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $str1 .= "<option data-tokens=\"" . $vals['filter_name'] . "\" $selected value=\"" . $vals['filter_name'] . "\">" . $vals['filter_name'] . "</option>";
    }
    $filterarray['asset_options'] = $str1;
    $filterarray['filter_options'] = $html;
    echo json_encode($filterarray);
}

function fetch_eventDetails()
{
    $id = url::getToText('id');
    $type = url::getToText('type');
    $db = pdo_connect();
    $sql = $db->prepare("select * from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filters` where id = ?");
    $sql->execute([$id]);
    $res = $sql->fetch();

    $userid = $res['user_id'];
    $name = $res['name'];

    $filterarray = array();
    $sql2 = $db->prepare("select * from `" . $GLOBALS['PREFIX'] . "agent`.`adhoc_event_filter_opearations` where adhoc_event_filter_id = ?");
    $sql2->execute([$id]);
    $res2 = $sql2->fetchAll();
    $c = 0;
    foreach ($res2 as $key => $val) {
        $sourcefields = $val['source_fields'];
        $sourcefields = explode(',', $sourcefields);
        $filtername[$c] = $val['filter_name'];
        $filteroperation[$c] = $val['filter_operation'];
        $filterval[$c] = $val['filter_value'];
        $filterscount = safe_count($filterarray['filtername']);

        $filterarray['name'] = $name;
        $filterarray['sourcefields'] = $sourcefields;
        $filterarray['dartno'] = $val['dartno'];

        $OperatorData = assetOperators();
        foreach ($OperatorData as $key => $val) {
            if (in_array($key, $filteroperation)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $str2 .= "<option $selected value=" . $key . ">" . $val . "</option>";
        }

        $filterres = fetchEventfilterData();
        foreach ($filterres as $vals) {
            if (in_array($vals['filter_name'], $filtername)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $str1 .= "<option data-tokens=\"" . $vals['filter_name'] . "\" $selected value=\"" . $vals['filter_name'] . "\">" . $vals['filter_name'] . "</option>";
        }
        $html .= '<div class="row edit-filter2-rows">
                    <div class="col-sm-4">
                        <label>
                            Filter name
                        </label>
                        <select data-live-search="true" data-required="true" data-label="Filter name" class="selectpicker" data-style="btn btn-info" title="Select Filter Name" data-size="3" name="filter-name2edit[]">
                                ' . $str1 . '
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <label>
                             Operator
                        </label>
                        <select  data-required="true" data-label="Operator" class="selectpicker" data-style="btn btn-info" title="Select Asset Filter" data-size="3" id="assetOperatoredit" name="filter-operator2edit[]">
                                ' . $str2 . '
                        </select>
                    </div>
                   <div class="col-sm-3">
                       <label>
                            Value
                        </label>
                        <input data-required="true" data-label="Value" value="' . $filterval[$c] . '" class="form-control" type="text" name="filter-value2edit[]"/>
                    </div>
                    <div class="col-sm-1">
                            <i id="add-more-filter2edit" class="dt-icons-l tim-icons icon-simple-add r-ic-plain"></i>
                            <i class="dt-icons-l tim-icons icon-simple-remove r-ic-plain" style="display:none" onclick="removeFilterGridedit($(this))"></i>
                    </div>
                    <input style="display:none" type="submit" class="button" value="create"/>
                    </div>';

        $c++;
    }

    $filterres = fetchEventfilterData();
    $sourceValues = $filterarray['sourcefields'];

    foreach ($filterres as $vals) {
        if (in_array($vals['filter_name'], $sourceValues)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $str1 .= "<option data-tokens=\"" . $vals['filter_name'] . "\" $selected value=\"" . $vals['filter_name'] . "\">" . $vals['filter_name'] . "</option>";
    }

    $dartSelected = array($filterarray['dartno']);
    $result = fetchdartfilterData();

    foreach ($result as $vals) {
        if (in_array($vals['dartno'], $dartSelected)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $str3 .= "<option data-tokens=\"" . $vals['dartno'] . "\" $selected value=\"" . $vals['dartno'] . "\">" . $vals['dartno'] . "</option>";
    }
    $filterarray['asset_options'] = $str1;
    $filterarray['filter_options'] = $html;
    $filterarray['dart_list'] = $str3;
    echo json_encode($filterarray);
}



function cron_adhoc_query($pdo, $aid, $adhocDetails)
{

    $searchType = $adhocDetails['searchtype'];
    $searchValue = $adhocDetails['searchvalue'];
    $qid = $adhocDetails['qid'];
    $queryname = $adhocDetails['queryname'];

    if ($adhocDetails['adhoctype'] == 'asset') {
        $res = exportAsset($searchType, $searchValue, $aid, $qid, $queryname);
    } else {
        $from = $adhocDetails['datafrom'];
        $to = $adhocDetails['datato'];
        $res = exportEvent($searchType, $searchValue, $qid, $queryname, $from, $to);
    }
    $data['filename'] = $res;

    return $data;
}
