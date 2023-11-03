<?php


require_once("../include/common_functions.php");
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-dashElastic.php';
include_once '../lib/l-setTimeZone.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';

global $db;
$db = db_connect();
$function = '';

if (url::issetInRequest('function')) {
    $function = url::requestToText('function');
    $function();
}

function sitegridlist()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "asset", $db);
    $draw = url::requestToAny('draw');
    $gridType = UTIL_GetString('gridType', '');
    $searchVal = url::requestToAny('search')['value'];
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $limit = "LIMIT 0,18446744073709551615";
    $key = '';
    $where = '';

    $orderval = url::requestToAny('order')[0]['column'];

    if ($orderval != '') {
        $orderColoumn = url::requestToAny('columns')[$orderval]['data'];
        $ordertype = url::requestToAny('order')[0]['dir'];
        $orderValues = "$where order by $orderColoumn $ordertype";
    }

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    switch ($searchType) {
        case 'Sites':
            $Oscount = DASH_GetSiteOsDetails($key, $db, $gridType, $dataScope, $searchVal, $orderValues, '');
            $OsDetails = DASH_GetSiteOsDetails($key, $db, $gridType, $dataScope, $searchVal, $orderValues, $limit);
            break;
        case 'Groups':
            $machineLastRprt = DASH_GetGroupsMachines($key, $db, $dataScope);

            $Oscount = DASH_GetGrpOsDetail($key, $db, $gridType, $machineLastRprt, $searchVal, $orderValues, '');
            $OsDetails = DASH_GetGrpOsDetail($key, $db, $gridType, $machineLastRprt, $searchVal, $orderValues, $limit);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $Oscount = DASH_GetMachineOsDetail($key, $db, $gridType, $censusId, $searchValue, $orderValues, '');
            $OsDetails = DASH_GetMachineOsDetail($key, $db, $gridType, $censusId, $searchValue, $orderValues, $limit);
            break;
        default:
            break;
    }

    $totalRecords = safe_count($Oscount);
    if ($totalRecords > 0) {
        foreach ($OsDetails as $key => $row) {

            $slatest1 = ($row['last'] == '-') ? '-' : date('m/d/Y H:i:s', $row['last']);
            $clatest1 = ($row['born'] == '-') ? '-' : date('m/d/Y H:i:s', $row['born']);
            $opsystem1 = $row['value1'];

            $DT_RowId = $row['site'] . '##' . $row['host'];
            $id = $row['id'];
            $host = $row['host'];
            $value1 = '<p class="ellipsis" title="' . $opsystem1 . '">' . $opsystem1 . '</p>';
            $clatest = '<p class="ellipsis" title="' . $clatest1 . '">' . $clatest1 . '</p>';
            $slatest = '<p class="ellipsis" title="' . $slatest1 . '">' . $slatest1 . '</p>';
            $cust = $row['site'];


            $recordList[] = array($host, $value1, $clatest, $slatest, $DT_RowId, $id, $cust);
        }
    } else {
        $recordList = [];
    }

    $jsonData = $recordList;

    echo json_encode($jsonData);
}

function sitespiechartdata()
{
    $db = db_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $machineTableName = $_SESSION['machineTableName'];
    $asssetDataTableName = $_SESSION['assetTableName'];
    $menuLevel = $_SESSION['menuLevelOne'];
    $username = $_SESSION['user']['username'];
    $windows = 0;
    $android = 0;
    $linux = 0;
    $sunos = 0;
    $macos = 0;
    $others = 0;
    $iOS = 0;
    $gridType = 'chart';
    $searchVal = '';

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    switch ($searchType) {
        case 'Sites':
            $OsDetails = DASH_GetSiteOsDetails($key, $db, $gridType, $dataScope, $searchVal, '', '');
            break;
        case 'Groups':
            $machineLastRprt = DASH_GetGroupsMachines($key, $db, $dataScope);
            $OsDetails = DASH_GetGrpOsDetail($key, $db, $gridType, $machineLastRprt, $searchVal);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $OsDetails = DASH_GetMachineOsDetail($key, $db, $gridType, $censusId, $searchValue);
            break;
        default:
            break;
    }
    foreach ($OsDetails as $key => $value) {
        if (strpos($value['value1'], 'Windows') !== false) {
            $windows++;
        }
        if (strpos($value['value1'], 'Android') !== false) {
            $android++;
        }
        if (strpos($value['value1'], 'Linux') !== false) {
            $linux++;
        }
        if (strpos($value['value1'], 'MAC') !== false) {
            $macos++;
        }
        if (strpos($value['value1'], 'iOS') !== false) {
            $iOS++;
        }
        if ($value['value1'] == '' || $value['value1'] == '-') {
            $others++;
        }
    }

    $responce['windows'] = $windows;
    $responce['android'] = $android;
    $responce['linux'] = $linux;
    $responce['mac'] = $macos;
    $responce['iOS'] = $iOS;
    $responce['other'] = $others;

    echo json_encode($responce);
}



function eventlistData()
{
    global $API_enable_Event;
    $key = '';
    $db = db_connect();
    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $machine_name = url::requestToAny('host');
    $site_name = url::requestToAny('cust');

    if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
        $eventgriddata = DASH_GetMachineEventList_EL($key, $time1, $time2, $machine_name, $site_name, $db);
    } else {
        $eventgriddata = DASH_GetMachineEventList_OLD($key, $time1, $time2, $machine_name, $site_name, $db);
    }

    $totalrecords = safe_count($eventgriddata);

    if ($totalrecords > 0) {

        foreach ($eventgriddata as $key => $row) {

            $recordList[] = array(
                '<p class="ellipsis" title="' . $row['machine'] . '">' . $row['machine'] . '</p>',
                '<p class="ellipsis" title="' . safe_addslashes(utf8_encode($row['description'])) . '">' . safe_addslashes(utf8_encode($row['description'])) . '</p>',
                '<p class="ellipsis" title="' . strip_tags(wordwrap(safe_addslashes(utf8_encode($row['text1'])), 50, PHP_EOL)) . '">' . strip_tags(wordwrap(safe_addslashes(utf8_encode($row['text1'])), 50, PHP_EOL)) . '</p>',
                '<p class="ellipsis" title="' . $row['scrip'] . '">' . $row['scrip'] . '</p>',
                '<p class="ellipsis" title="' . date("m-d-Y H:i:s", $row['entered']) . '">' . date("m-d-Y H:i:s", $row['entered']) . '</p>',
                '<p class="ellipsis" title="' . date("m-d-Y H:i:s", $row['servertime']) . '">' . date("m-d-Y H:i:s", $row['servertime']) . '</p>',
                $row['idx']
            );
        }
    } else {
        $recordList = array();
    }

    echo json_encode($recordList);
}



function eventlistallData()
{
    global $API_enable_Event;
    if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
        eventlistallData_EL();
    } else {
        $key = '';
        $db = db_connect();
        $siteName = url::requestToAny('cust');
        $machine = url::requestToAny('host');
        if ($siteName != '' && $machine != '') {
            $searchValue = $machine;
            $searchType = 'ServiceTag';
        } else {
            $searchValue = $_SESSION['searchValue'];
            $searchType = $_SESSION['searchType'];
        }

        $time1 = time();
        $time2 = strtotime("-24 hours", $time1);
        $filterValue = (url::requestToAny('filter') != '') ? url::requestToAny('filter') : '';
        $dartnoValue = (url::requestToAny('dartno') != '') ? url::requestToAny('dartno') : '';

        $exec = (url::requestToAny('exec') != '') ? url::requestToAny('exec') : '';
        $title = (url::requestToAny('title') != '') ? url::requestToAny('title') : '';
        $text = (url::requestToAny('text') != '') ? url::requestToAny('text') : '';
        $idval = (url::requestToAny('idval') != '') ? url::requestToAny('idval') : '';

        $time1 = (url::requestToAny('enddate') != '') ? strtotime(url::requestToAny('enddate') . '23:59:59') : time();
        $time2 = (url::requestToAny('startdate') != '') ? strtotime(url::requestToAny('startdate') . '00:00:00') : strtotime("-24 hours", $time1);

        $_SESSION['AdvEventFilterTime1'] = $time1;
        $_SESSION['AdvEventFilterTime2'] = $time2;

        $_SESSION['AdvEventFilter'] = array('Tags' => $filterValue, 'scrip' => $dartnoValue, 'executable' => $exec, 'windowtitle' => $title, 'text1' => $text, 'id' => $idval);

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

        switch ($searchType) {
            case 'Sites':
                if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
                    $gridlist = DASH_GetSitesEventList_EL($key, $db, $dataScope, $time1, $time2);
                } else {
                    $gridlist = DASH_GetSitesEventList_OLD($key, $db, $dataScope, $time1, $time2);
                }
                break;
            case 'Groups':
                $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
                $gridlist = DASH_GetGroupEventList($key, $time1, $time2, $machines, $db);
                break;
            case 'ServiceTag':
                $site_name = $_SESSION['rparentName'];
                $machine_name = $_SESSION['searchValue'];
                if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
                    $gridlist = DASH_GetMachineEventList_EL($key, $time1, $time2, $machine_name, $site_name, $db);
                } else {
                    $gridlist = DASH_GetMachineEventList_OLD($key, $time1, $time2, $machine_name, $site_name, $db);
                }

                break;
            default:
                break;
        }
        $totalrecords = safe_count($gridlist);

        if ($totalrecords > 0) {
            foreach ($gridlist as $key => $row) {

                $device = '<p class="ellipsis" title="' . $row['machine'] . '">' . $row['machine'] . '</p>';
                $desc = '<p class="ellipsis" title="' . safe_addslashes(utf8_encode($row['description'])) . '">' . safe_addslashes(utf8_encode($row['description'])) . '</p>';
                $eventInfo = '<p class="ellipsis" title="' . strip_tags(wordwrap(utf8_encode(str_replace('"', '', $row['text1'])), 50, PHP_EOL)) . '">' . strip_tags(wordwrap(utf8_encode(str_replace('"', '', $row['text1'])), 50, PHP_EOL)) . '</p>';
                $scrip = '<p class="ellipsis" title="' . $row['scrip'] . '">' . $row['scrip'] . '</p>';
                $clientTime = '<p class="ellipsis" title="' . date("m/d/Y H:i:s", $row['entered']) . '">' . date("m/d/Y H:i:s", $row['entered']) . '</p>';
                $serverTime = '<p class="ellipsis" title="' . date("m/d/Y H:i:s", $row['servertime']) . '">' . date("m/d/Y H:i:s", $row['servertime']) . '</p>';
                $idx = $row['idx'];

                $recordList[] = array('device' => $device, 'desc' => $desc, 'eventInfo' => $eventInfo, 'scrip' => $scrip, 'clientTime' => $clientTime, 'serverTime' => $serverTime);
            }
        } else {
            $recordList = array();
        }

        echo json_encode($recordList);
    }
}



function eventlistallData_EL()
{
    $draw = url::issetInRequest('draw') ? url::requestToAny('draw') : 1;
    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length = url::issetInRequest('length') ? url::requestToAny('length') : 2000;

    $key = '';
    $db = db_connect();
    $siteName = url::requestToAny('cust');
    $machine = url::requestToAny('host');
    if ($siteName != '' && $machine != '') {
        $searchValue = $machine;
        $searchType = 'ServiceTag';
    } else {
        $searchValue = $_SESSION['searchValue'];
        $searchType = $_SESSION['searchType'];
    }
    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $filterValue = (url::requestToAny('filter') != '') ? url::requestToAny('filter') : '';
    $dartnoValue = (url::requestToAny('dartno') != '') ? url::requestToAny('dartno') : '';

    $exec = (url::requestToAny('exec') != '') ? url::requestToAny('exec') : '';
    $title = (url::requestToAny('title') != '') ? url::requestToAny('title') : '';
    $text1 = (url::requestToAny('text1') != '') ? url::requestToAny('text1') : '';
    $text2 = (url::requestToAny('text2') != '') ? url::requestToAny('text2') : '';
    $text3 = (url::requestToAny('text3') != '') ? url::requestToAny('text3') : '';
    $text4 = (url::requestToAny('text4') != '') ? url::requestToAny('text4') : '';
    $idval = (url::requestToAny('idval') != '') ? url::requestToAny('idval') : '';

    $time1 = (url::requestToAny('enddate') != '') ? strtotime(url::requestToAny('enddate') . '23:59:59') : time();
    $time2 = (url::requestToAny('startdate') != '') ? strtotime(url::requestToAny('startdate') . '00:00:00') : strtotime("-24 hours", $time1);

    $_SESSION['AdvEventFilterTime1'] = $time1;
    $_SESSION['AdvEventFilterTime2'] = $time2;

    $_SESSION['AdvEventFilter'] = array('Tags' => $filterValue, 'scrip' => $dartnoValue, 'executable' => $exec, 'windowtitle' => $title, 'text1' => $text, 'text2' => $text2, 'text3' => $text3, 'text4' => $text4, 'id' => $idval);

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            if (is_array($dataScope)) {
                foreach ($dataScope as $row) {
                    $sitefilter .= '{"term": {"site.keyword": "' . $row . '"}},';
                }
                $sitefilter = rtrim($sitefilter, ',');
            } else {
                $sitefilter = '{"term": {"site.keyword": "' . $dataScope . '"}}';
            }
            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);

            foreach ($machines as $row) {
                $sitefilter .= '{ "term": { "machine.keyword": "' . $row . '" }},';
            }
            $sitefilter = rtrim($sitefilter, ',');

            break;
        case 'ServiceTag':
            if ($_SESSION['passlevel'] == 'Groups') {
                $sitefilter .= '{ "term": { "machine.keyword": "' . $dataScope . '" }}';
            } else {
                $censusId = $_SESSION['rcensusId'];

                $site = $_SESSION['rparentName'];
                $sitefilter .= '{ "term": { "machine.keyword": "' . $dataScope . '" }},
                                { "term": { "site.keyword": "' . $site . '" }}';
            }
            break;
        default:
            break;
    }
    if ($sitefilter != '') {
        $advQueryString = '';
        $queryString = '';
        foreach ($_SESSION['AdvEventFilter'] as $key => $value) {
            if ($value != '') {
                $queryString .= '{ "term": {"' . $key . '.keyword' . '": "' . $value . '" }},';
            }
        }
        $queryStringCond = rtrim($queryString, ',');
        if ($queryStringCond != '') {
            $advQueryString = '"minimum_should_match": 1,
                        "should": [
                            ' . $queryStringCond . '
                        ],';
        }

        $fromDate   = date('Y.m.d', $time2);
        $toDate     = date('Y.m.d', $time1);
        $indexName = createEventIndex($fromDate, $toDate);

        if ($searchType == 'Sites' || $searchType == 'Groups') {
            $params = '{
              "from" : ' . $start . ', "size" : ' . $length . ',
                "query": {
                  "bool": {
                    "must": [
                      {
                        "bool": {
                          "minimum_should_match": 1,
                          "should": [' . $sitefilter . ']
                        }
                      },' . $queryString . '
                      {
                        "range": {
                          "ctime": {
                            "gte": ' . $time2 . ',
                            "lte": ' . $time1 . '
                          }
                        }
                      }
                    ]
                  }
                },
                "sort" : [{ "_id" : "desc" }]
          }';
        } else {

            $params = '{
              "from" : ' . $start . ', "size" : ' . $length . ',
                "query": {
                  "bool": {
                        ' . $advQueryString . '
                    "must": [
                            ' . $sitefilter . '
                        ],
                        "filter": [
                            { "range": { "ctime": { "gte": ' . $time2 . ', "lte": ' . $time1 . ' } } }
                    ]
                  }
                }
          }';
        }

        global $elastic_username;
        global $elastic_password;

        $requestHeaders = [
            "Content-Type: application/json",
            "Authorization: Basic " .  base64_encode($elastic_username . ":" . $elastic_password)
        ];

        $indexName = 'events*';
        $tempRes = EL_GetCurlWithLimit($indexName, $params, $requestHeaders);
        $resultData = EL_FormatCurldata_new($tempRes);
        $totalCount = $resultData['total'];
        $eventsitesdata = $resultData['result'];

        $idx = 1;
        if ($totalCount > 0) {
            if (url::issetInGet('export')) {
                exportEventList($eventsitesdata);
            } else {
                foreach ($eventsitesdata as $key => $row) {
                    $device = '<p class="ellipsis event_info_node" data-event-info=\'' . (json_encode($row, JSON_HEX_APOS)) . '\' title="' . $row['machine'] . '">' . $row['machine'] . '</p>';
                    $desc = '<p class="ellipsis" title="' . safe_addslashes(utf8_encode($row['desc'])) . '">' . safe_addslashes(utf8_encode($row['desc'])) . '</p>';
                    $scrip = '<p class="ellipsis" title="' . $row['scrip'] . '">' . $row['scrip'] . '</p>';
                    $clientTime = '<p class="ellipsis" title="' . date("m/d/Y H:i:s", $row['ctime']) . '">' . date("m/d/Y H:i:s", $row['ctime']) . '</p>';
                    $serverTime = isset($row['hrtime']) ? '<p class="ellipsis" title="' . date("m/d/Y H:i:s", strtotime($row['@timestamp'])) . '">' . date("m/d/Y H:i:s", strtotime($row['@timestamp'])) . '</p>' : '';
                    $recordList[] = array("id" => $idx, 'device' => $device, 'desc' => $desc, 'eventInfo' => $eventInfo, 'scrip' => $scrip, 'clientTime' => $clientTime, 'serverTime' => $serverTime);
                    $idx++;
                }
            }
        } else {
            if (url::issetInGet('export')) {
                exportEventList($eventsitesdata);
            } else {
                $recordList = array();
            }
        }
    } else {
        if (url::issetInGet('export')) {
            $eventsitesdata = [];
            exportEventList($eventsitesdata);
        } else {
            $recordList = array();
        }
    }
    $val = $totalCount['value'];
    if ($val === 0) {
        $totalCount = array();
        $recordList = array();
    }
    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    echo json_encode($jsonData);
}

function exportEventList($data)
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $gname = $_SESSION['rparentName'];


    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    if ($searchType === 'Sites') {
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Site Name');
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Group Name');
    }
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Machine');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Description');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Scrip');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Client time');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Server time');


    $index = 2;

    if (safe_sizeof($data) > 0) {
        foreach ($data as $eachData) {
            if ($searchType === 'Sites') {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $searchValue);
            } else {
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $gname);
            }
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $eachData['machine']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $eachData['desc']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $eachData['scrip']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, date("m/d/Y H:i:s", $eachData['ctime']));
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, date("m/d/Y H:i:s", strtotime($eachData['@timestamp'])));
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    $fn = "event_information.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}


function Exportdeviceslist()
{
    $db = db_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $searchVal = '';
    $gridType = url::requestToAny('gridtype');
    $index = 2;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Machine Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Operating System');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Born Date');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Last Reported');

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    switch ($searchType) {
        case 'Sites':
            $OsDetails = DASH_GetSiteOsDetails($key, $db, $gridType, $dataScope, $searchVal, '', '');
            break;
        case 'Groups':
            $machineLastRprt = DASH_GetGroupsMachines($key, $db, $dataScope);
            $OsDetails = DASH_GetGrpOsDetail($key, $db, $gridType, $machineLastRprt, $searchVal);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $OsDetails = DASH_GetMachineOsDetail($key, $db, $gridType, $censusId, $searchVal);
            break;
        default:
            break;
    }

    if ($OsDetails) {
        foreach ($OsDetails as $key => $value) {
            $slatest = date('m/d/Y H:i:s', $value['last']);
            $clatest = date('m/d/Y H:i:s', $value['born']);
            $operating = $value['value1'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['host']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $operating);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $clatest);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $slatest);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
    $fn = "DevicesList.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}



function eventexportlist()
{
    global $API_enable_Event;
    $key = '';
    $db = db_connect();
    $time1 = time();
    $time2 = $time1 - (24 * 60 * 60);
    $machine_name = url::requestToAny('host');
    $site_name = url::requestToAny('cust');
    $dbUsage = $_SESSION["user"]["usage"];

    $index = 2;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Device');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Description');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Event Information');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Scrip No.');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Client Time');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Server Time');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'text1');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'text2');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'text3');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'text4');

    if ($dbUsage == 1) {
        $eventgriddata = DASH_GetMachineEventList_EL($key, $time1, $time2, $machine_name, $site_name, $db);
        die();
    } else {
        $eventgriddata = DASH_GetMachineEventList_OLD($key, $time1, $time2, $machine_name, $site_name, $db);
    }

    if ($eventgriddata) {
        foreach ($eventgriddata as $key => $value) {

            $description = safe_addslashes(utf8_encode($value['description']));
            $text1 = strip_tags(wordwrap(safe_addslashes(utf8_encode($value['text1'])), 50, PHP_EOL));
            $clienttime = date("m/d/Y H:i:s", $value['entered']);
            $servertime = date("m/d/Y H:i:s", $value['servertime']);
            $machines = $value['machine'];
            $scrip = $value['scrip'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $machines);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $description);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $text1);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $scrip);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $clienttime);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $servertime);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $value['text1']);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, $value['text2']);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, $value['text3']);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $value['text3']);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
    $fn = "EventList.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}



function eventDetailPopup()
{
    $key = '';
    $db = db_connect();
    $eid = url::requestToAny('eid');
    $listDetails = DASH_GetEventDetailList($key, $eid, $db);

    $temp_str = $listDetails["text4"];
    $text4 = str_replace("<title>Local Machines</title>", " ", $temp_str);
    $site = UTIL_GetTrimmedGroupName($listDetails['customer']);
    $machine = $listDetails['machine'];
    $clientversion = $listDetails['clientversion'];
    $cltime = date('m:d:Y H:i:s', $listDetails['entered']);
    $setime = date('m:d:Y H:i:s', $listDetails['servertime']);
    $uuid = $listDetails['uuid'];
    $uname = $listDetails['username'];
    $priority = $listDetails['priority'];
    $desc = $listDetails['description'];
    $scripno = $listDetails['scrip'];
    $type = $listDetails['type'];
    $exec = $listDetails['executable'];
    $version = $listDetails['version'];
    $size = $listDetails['size'];
    $uid = $listDetails['id'];
    $string1 = $listDetails['string1'];
    $string2 = $listDetails['string2'];
    $clsize = $listDetails['clientsize'];
    $path = $listDetails['path'];
    $text1 = $listDetails['text1'];
    $text2 = $listDetails['text2'];
    $text3 = $listDetails['text3'];
    $text4 = $listDetails['text4'];

    $text4 = str_replace("config.cgi", "javascript:;", $text4);

    $data = array(
        "site" => '' . $site . '', "machine" => '' . $machine . '', "clientversion" => '' . $clientversion . '', "cltime" => '' . $cltime . '',
        "setime" => '' . $setime . '', "uuid" => '' . $uuid . '', "uname" => '' . $uname . '', "priority" => '' . $priority . '', "desc" => '' . $desc . '',
        "scripno" => '' . $scripno . '', "type" => '' . $type . '', "exec" => '' . $exec . '', "version" => '' . $version . '', "size" => '' . $size . '',
        "uid" => '' . $uid . '', "string1" => '' . $string1 . '', "string2" => '' . $string2 . '', "clsize" => '' . $clsize . '', "path" => '' . $path . '',
        "text1" => '<p class="ellipsis">' . $text1 . '</p>', "text2" => '<p class="ellipsis">' . $text2 . '</p>',
        "text3" => '<p class="ellipsis">' . $text3 . '</p>', "text4" => '<p class="ellipsis">' . $text4 . '</p>'
    );

    echo json_encode($data);
}

function get_eventDeetailpopup()
{

    $db = db_connect();
    $draw = url::requestToAny('draw');
    $idx = url::requestToAny('idx');

    $sqltotal = "select count(idx) as count from  " . $GLOBALS['PREFIX'] . "event.Events where idx = $idx";
    $resulttotal = find_one($sqltotal, $db);
    $totalRecords = $resulttotal['count'];

    $insert = "select entered,servertime, replace(replace( replace(text1, '', ' '), '', ' '), '', ' ') as text1 from  " . $GLOBALS['PREFIX'] . "event.Events where idx = $idx limit 1";
    $result = find_one($insert, $db);

    $recordList[] = array('clienttime' => '' . date("m/d/Y H:i:s", $result['entered']) . '', 'servertime' => '' . date("m/d/Y H:i:s", $result['servertime']) . '', 'text' => $result['text1']);

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $recordList);
    echo json_encode($jsonData);
}



function exportExcelallData()
{
    global $API_enable_Event;
    $key = '';
    $db = db_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $time1 = ($_SESSION['AdvEventFilterTime1'] != '') ? $_SESSION['AdvEventFilterTime1'] : time();
    $time2 = ($_SESSION['AdvEventFilterTime2'] != '') ? $_SESSION['AdvEventFilterTime2'] : strtotime("-24 hours", $time1);

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    switch ($searchType) {
        case 'Sites':
            if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
                $gridlist = DASH_GetSitesEventList_EL($key, $db, $dataScope, $time1, $time2);
            } else {
                $gridlist = DASH_GetSitesEventList_OLD($key, $db, $dataScope, $time1, $time2);
            }

            break;
        case 'Groups':
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);

            if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
                $gridlist = DASH_GetGroupEventList_EL($key, $db, $machines, $time1, $time2);
            } else {
                $gridlist = DASH_GetGroupEventList($key, $time1, $time2, $machines, $db);
            }
            break;
        case 'ServiceTag':
            $site_name = $_SESSION['rparentName'];
            $machine_name = $_SESSION['searchValue'];
            if ((isset($_SESSION['user']['usage']) && $_SESSION['user']['usage'] == 1)) {
                $gridlist = DASH_GetMachineEventList_EL($key, $time1, $time2, $machine_name, $site_name, $db);
            } else {
                $gridlist = DASH_GetMachineEventList_OLD($key, $time1, $time2, $machine_name, $site_name, $db);
            }

            break;
        default:
            break;
    }
}

function get_assetfilterportal()
{

    $key = '';
    $db = db_connect();
    $siteType = UTIL_GetString('searchType', '');
    $site = UTIL_GetString('searchValue', '');
    $auth = UTIL_GetString('username', '');
    $qid = UTIL_GetInteger('qid', 9);
    $time = time();
    $offset = 0;
    $totalMachine = 0;

    $assetresult = DASH_GetAssetReportResult($key, $db, $qid, $site, $siteType, $auth, $time, $offset, $totalMachine);

    if ($assetresult) {
        echo 'Report will be published on information portal shortly';
    }
}

function get_machinereportlist()
{
    $key = '';
    $db = db_connect();
    $id = UTIL_GetInteger('id', '');
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentName = $_SESSION['rparentName'];

    if ($searchType == 'Sites') {
        $siteName = $searchValue;
    } elseif ($searchType == 'Groups') {
        $siteName = $searchValue;
    } else {
        $siteName = $rparentName . ":" . $searchValue;
    }

    $sql = "SELECT id,startTime,fileName,status FROM " . $GLOBALS['PREFIX'] . "asset.AssetQueryResult where " .
        "qid = $id  and sitename = '$siteName' order by startTime desc";

    $sqlresult = find_many($sql, $db);
    $recordList = [];
    if ($sqlresult) {
        foreach ($sqlresult as $key => $row) {

            if ($row['fileName'] && $row['status'] == 'Completed') {
                $download = "<a href='#' onclick='Downloadxls(\"" . $row['fileName'] . "\")'><i class='material-icons icon-ic_file_download_24px'></i></a>";
            } else {
                $download = '';
            }

            if ($row['startTime']) {
                $startTime = date('m/d/Y h:i:s', $row['startTime']);
            } else if ($row['startTime'] == '0') {
                $startTime = '';
            } else {
                $startTime = '0';
            }




            $recordList[] = array(
                '<p class="ellipsis" title="' . $row['status'] . '">' . $row['status'] . '</p>',
                '<p class="ellipsis" title="' . $startTime . '">' . $startTime . '</p>',
                '<p class="ellipsis">' . $download . '</p>'
            );
        }
    } else {
        $recordList = array();
    }
    echo json_encode($recordList);
}

function get_deleteportalreport()
{
    $key = '';
    $db = db_connect();
    $ids = url::requestToAny('id');
    $id = $ids;
    if (safe_count($id > 1)) {

        $sql_total = "delete from " . $GLOBALS['PREFIX'] . "asset.AssetQueryResult where id in ($id)";
        $result = redcommand($sql_total, $db);
    } else {

        $sql_total = "update " . $GLOBALS['PREFIX'] . "asset.AssetQueryResult set global = 1 where id = $id[0]";
        $result = redcommand($sql_total, $db);
    }

    echo json_encode($result);
}



function get_machineremote()
{
    $key = '';
    $db = db_connect();
    $hostname = [];
    $host = url::requestToAny('hostname');
    $hostname = array($host);
    $remote = DASH_GetAllMachineStatus($key, $db, $hostname);
    $status = $remote[$host][0];

    echo json_encode($status);
}

function get_searchmachines()
{
    $key = '';
    $db = db_connect();
    $username = url::requestToAny('username');
    $remote = Pane_GetSearchMenu($key, $username);
}




function filteredEventData()
{
    $key = '';
    $db = db_connect();

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $temptime1 = UTIL_GetString('StartTime', "");

    $temptime2 = UTIL_GetString('EndTime', "");

    $time1 = ($temptime1 !== "") ? strtotime($temptime1) : time();
    $time2 = ($temptime1 !== "") ? strtotime($temptime2) : time() - (15 * 24 * 60 * 60);

    $dartNo = UTIL_GetString('DartNumber', '');
    $savedSearch = UTIL_GetString('saved_searches', '');
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    switch ($searchType) {
        case 'Sites':
            $gridlist = ELPROV_GetFilterSitesEvents($db, $time1, $time2, $dataScope, $dartNo, $savedSearch);
            break;
        case 'ServiceTag':
            $site_name = $_SESSION['rparentName'];
            $machine_name = $_SESSION['searchValue'];
            $gridlist = ELPROV_GetFilterMachineEvents($db, $time1, $time2, $machine_name, $site_name, $dartNo, $savedSearch);
            break;
        default:
            break;
    }
    $totalrecords = safe_count($gridlist);

    if ($totalrecords > 0) {
        foreach ($gridlist as $key => $row) {
            $recordList[] = array(
                '<p class="ellipsis" title="' . $row['machine'] . '">' . $row['machine'] . '</p>',
                '<p class="ellipsis" title="' . safe_addslashes(utf8_encode($row['description'])) . '">' . safe_addslashes(utf8_encode($row['description'])) . '</p>',
                '<p class="ellipsis" title="' . strip_tags(wordwrap(safe_addslashes(utf8_encode($row['text1'])), 50, PHP_EOL)) . '">' . strip_tags(wordwrap(safe_addslashes(utf8_encode($row['text1'])), 50, PHP_EOL)) . '</p>',
                '<p class="ellipsis" title="' . date("m/d/Y H:i:s", $row['entered']) . '">' . date("m/d/Y H:i:s", $row['entered']) . '</p>',
                '<p class="ellipsis" title="' . date("m/d/Y H:i:s", $row['servertime']) . '">' . date("m/d/Y H:i:s", $row['servertime']) . '</p>',
                $row['idx']
            );
        }
    } else {
        $recordList = array();
    }

    echo json_encode($recordList);
}

function getSavedSearches()
{
    $db = db_connect();
    $str = '<option value="0">Please select saved search</option>';
    $savedSearchArr = DASH_GetEventFilerList("", $db, 'admin');

    foreach ($savedSearchArr as $key => $value) {
        $str .= '<option value="' . $value['id'] . '--' . $value['eventtag'] . '">' . $value['name'] . '</option>';
    }
    echo $str;
}
