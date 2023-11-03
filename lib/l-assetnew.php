<?php

include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/config.php';
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-db.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-sql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-gsql.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib/l-rcmd.php';



function __getAssetELQry($qryid, $machineid, $indexname, $from, $size, $export, $db)
{

    $searchStr = '';
    $displayfields = '';


    $sql = "select id,searchstring,displayfields from " . $GLOBALS['PREFIX'] . "asset.AssetSearches S where S.id='$qryid'";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {

        $searchStr = trim($res['searchstring']);
        $displayfields = $res['displayfields'] . 'Machine Name:Site Name:';

        if ($searchStr != '') {
            $datafields = __buildSelFields($displayfields, $db);
            $selectfields = implode(',', $datafields['dispfields']);
            $dataNames = $datafields['dataname'];
            $filterfields = $datafields['selfields'];
            $sliced_array = array();
            if ($export == 0) {
                $assetQry = __buildAssetELQry($searchStr, $selectfields, $datafields['selFieldVal'], $machineid, $dataNames, $from, $size, $indexname, $db);
            } else {
            }
            $finalQry = array("status" => "Success", "msg" => $assetQry);
        } else {
            $datafields = __buildSelFields($displayfields, $db);
            $selectfields = implode(',', $datafields['dispfields']);
            $dataNames = $datafields['dataname'];
            $filterfields = $datafields['selfields'];
            $assetQry = __matchAll($selectfields, $datafields['selFieldVal'], $machineid, $from, $size);
            if ($export == 0) {

                $resultdata = __parseResult($assetQry['finalQry']);
                $finalQry = array("status" => "Success", "result" => $resultdata['data'], "total" => $resultdata['total']);
            } else {
                $assetQry = $assetQry['finalQry'];
                $finalQry = array("status" => "Success", "result" => $assetQry);
            }
        }
    } else {
        $finalQry = array("status" => "Error", "msg" => "Invalid search id");
    }

    return $finalQry;
}


function __getFilerIndexExport($from, $size)
{

    $qry = '{
	"from": ' . $from . ',
	"size": ' . $size . ',
	"query": {
		"bool": {
			"must": [{
				"match_all": {}
			}]
		}
	}
    }';

    return $qry;
}


function __getFilerIndexData($indexname, $from, $size)
{


    $qry = '{
	"from": ' . $from . ',
	"size": ' . $size . ',
	"query": {
		"bool": {
			"must": [{
				"match_all": {}
			}]
		}
	}
    }';

    sleep(1);
    $assetData = getAllAssets_1($qry, $indexname);

    $response = safe_json_decode($assetData, true);
    $finalArr = [];
    $total = $response['hits']['total'];
    foreach ($response['hits']['hits'] as $key => $val) {

        $result = $val['_source'];
        $finalArr[] = $result;
    }
    return array("result" => $finalArr, "total" => $total);
}



function __parseResult($qry)
{

    $indexname = 'assetdata';

    $assetData = getAllAssets_1($qry, $indexname);


    $response = safe_json_decode($assetData, true);
    $finalArr = [];
    $total = $response['hits']['total'];
    foreach ($response['hits']['hits'] as $key => $val) {

        $result = $val['_source'];
        $finalArr[] = $result;
    }
    return array("data" => $finalArr, "total" => $total);
}


function parseAssetData($assetQry, $displayFieldsColumn, $filterFld, $condType, $uniloop)
{
    $assetData = getAllAssets($assetQry);

    $response = safe_json_decode($assetData, true);

    $result = null;
    $finalArr = [];
    if ($condType == 'must_not') {

        $lastMachineName = '';
        $tempArr = [];
        $tempArr1 = [];
        $tempNotArr = [];
        $totalCnt = safe_count($response['hits']['hits']);
        $loop = 0;
        foreach ($response['hits']['hits'] as $key => $val) {

            $machineId = $val['_id'];
            $result = $val['_source'];

            $machineName = $result['machinename'];
            if (($lastMachineName != '' && $lastMachineName != $machineName) || ($lastMachineName != '' && $totalCnt == $loop + 1)) {

                $uniqCnt = count(array_unique($tempArr1));
                $filterCnt = safe_count($filterFld);
                $totlCnt = safe_count($tempNotArr);
                if ($totlCnt == 0) {
                    $finalArr = pushValues($finalArr, $tempArr);
                }
                $lastMachineName = $machineName;
                $tempArr    = [];
                $tempArr1   = [];
                $tempNotArr = [];
            } else if ($lastMachineName == '' || $lastMachineName == $machineName) {
                $lastMachineName = $machineName;
            }
            $statusVal = checkFiltercase_not($filterFld, $result);

            if ($statusVal['status']) {
                $tempArr[] = $result;
                $tempArr1[] = $statusVal['tempArr'];
                $tempNotArr[] = $statusVal['tempArr'];
            } else {
                $tempArr[] = $result;
                $tempArr1[] = $statusVal['tempArr'];
            }
            $loop++;
        }
    } else {

        if ($uniloop == 0) {
            foreach ($response['hits']['hits'] as $key => $val) {

                $machineId = $val['_id'];
                $result = $val['_source'];

                $status = checkFiltercase($filterFld, $result);
                if ($status) {
                    $finalArr[] = $result;
                }
            }
        } else if ($uniloop == 1) {

            $lastMachineName = '';
            $tempArr = [];
            $tempArr1 = [];
            $totalCnt = safe_count($response['hits']['hits']);
            $loop = 0;
            foreach ($response['hits']['hits'] as $key => $val) {

                $machineId = $val['_id'];
                $result = $val['_source'];

                $machineName = $result['machinename'];
                if (($lastMachineName != '' && $lastMachineName != $machineName) || ($lastMachineName != '' && $totalCnt == $loop + 1)) {

                    $uniqCnt = count(array_unique($tempArr1));
                    $filterCnt = safe_count($filterFld);
                    if ($uniqCnt == $filterCnt) {
                        $finalArr = pushValues($finalArr, $tempArr);
                    }
                    $lastMachineName = $machineName;
                    $tempArr = [];
                    $tempArr1 = [];
                } else if ($lastMachineName == '' || $lastMachineName == $machineName) {
                    $lastMachineName = $machineName;
                }
                $statusVal = checkFiltercase_1($filterFld, $result);

                if ($statusVal['status']) {

                    $tempArr[] = $result;
                    $tempArr1[] = $statusVal['tempArr'];
                }

                $loop++;
            }
        }
    }

    return $finalArr;
}


function pushValues($finalArr, $tempArr)
{


    foreach ($tempArr as $value) {

        $finalArr[] = $value;
    }

    return $finalArr;
}


function checkFiltercase($filetrFld, $result)
{

    $status = true;
    foreach ($filetrFld as $key => $value) {

        $dataid = $value;
        $cond1 = trim($key);

        $matchExpl = explode("##", $cond1);
        $condVal = trim($matchExpl[0]);
        $matchVal = trim($matchExpl[1]);

        $assetVal = $result[$dataid];

        if ($matchVal == 'exact') {
            if (trim($assetVal) != $condVal) {

                $status = false;
                return $status;
            }
        } else {
            if (stripos(trim($assetVal), $condVal) === false) {
                $status = false;
                return $status;
            }
        }
    }
    return $status;
}


function checkFiltercase_1($filetrFld, $result)
{
    $tempArr1 = [];
    $status = false;
    foreach ($filetrFld as $key => $value) {

        $dataid = $value;
        $cond1 = trim($key);

        $matchExpl = explode("##", $cond1);
        $condVal = trim($matchExpl[0]);
        $matchVal = trim($matchExpl[1]);

        $assetVal = $result[$dataid];

        if ($matchVal == 'exact') {
            if (trim($assetVal) == $condVal) {
                $status = true;
                return array("status" => $status, "tempArr" => $condVal);
            }
        } else {
            if (stripos(trim($assetVal), $condVal) !== false) {
                $status = true;
                return array("status" => $status, "tempArr" => $condVal);
            }
        }
    }
    return array("status" => $status, "tempArr" => '');
}


function checkFiltercase_not($filetrFld, $result)
{

    $tempArr1 = [];
    $status = false;
    foreach ($filetrFld as $key => $value) {

        $dataid = $value;
        $cond1 = trim($key);

        $matchExpl = explode("##", $cond1);
        $condVal = trim($matchExpl[0]);
        $matchVal = trim($matchExpl[1]);

        $assetVal = $result[$dataid];

        if ($matchVal == 'exact') {
            if (trim($assetVal) == $condVal) {
                $status = true;
                return array("status" => $status, "tempArr" => $condVal);
            }
        } else {
            if (stripos(trim($assetVal), $condVal) !== false) {
                $status = true;
                return array("status" => $status, "tempArr" => $condVal);
            }
        }
    }
    return array("status" => $status, "tempArr" => 'match');
}


function getAllAssets($params)
{

    global $elastic_url;
    $url = $elastic_url . "assetdata/_search?pretty&filter_path=took,hits.hits._id,hits.hits._score,hits.hits._source";

    try {
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}




function getAllAssets_scrollid($scrollid)
{

    global $elastic_url;
    $params = '{"scroll" : "1m", "scroll_id" : "' . $scrollid . '"}';
    $url = $elastic_url . "_search/scroll";

    try {
        $headers = array();
        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}


function getAllAssets_scroll($params, $indexname)
{

    global $elastic_url;
    $url = $elastic_url . $indexname . "/_search?scroll=5m";

    try {
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function deleteScrollId($scrollid)
{

    global $elastic_url;
    $params = '{"scroll_id" : "' . $scrollid . '"}';
    $url = $elastic_url . "_search/scroll";

    try {
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}
function getAllAssets_1($params, $indexname)
{

    global $elastic_url;
    $url = $elastic_url . $indexname . "/_search";

    try {
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function pushAssetData($params, $indexname)
{
    global $elastic_url;

    $bulkString = '';

    foreach ($params as $doc) {
        $generated = substr(str_shuffle("4567890abcdefghiABCDEFGHIJKLMNOPQRSTjklmnopqrstuvwxyzUVWXYZ123-_"), mt_rand(0, 50), 20);
        $var = '{ "index" : { "_index" : "' . $indexname . '", "_type" : "machinedata","_id": "' . $generated . '"}}';
        $bulkString .= "$var\n";
        $bulkString .= json_encode($doc) . "\n";
    }

    $url = $elastic_url . $indexname . "/machinedata/_bulk";

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
                'Content-Type: application/json'
            )
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bulkString);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        curl_exec($ch);
        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function __matchAll($selectfields, $selFieldVal, $filterCond, $from, $size)
{

    $filterList = array();

    $mustCrit = __getmustCrit($selFieldVal);




    $finalQry = ' {
        "_source": [
          ' . $selectfields . '
        ],
        "sort" : [
           { "machineid" : {"order" : "asc"} }
        ],
        "from" : ' . $from . ', 
        "size" : ' . $size . ',
        "query": {
          "bool": {
            ' .
        $mustCrit . $filterCond . '
          }
        }
   }';
    return array("finalQry" => $finalQry, "filterData" => $filterList);
}

function __getmustCrit($selectfields)
{
    $wh2 = "";
    foreach ($selectfields as $value) {
        $wh2 .= ' {
          "exists": {
            "field": "' . $value . '"
          }
        },';
    }
    $whr1 = rtrim($wh2, ',');
    $qryStr = '"must":[
                    ' . $whr1 . '
                ]
              ';
    return $qryStr;
}

function __buildAssetELQry($queryStr, $selectfields, $filterfields, $machineid, $displayFieldsColumn, $from, $size, $indexname, $db)
{

    $mustStr = '';
    $mustNotArrr = '';

    $resultData = [];
    if ($size == '' || $size == 0) {
        $size = 5;
    }

    if ($from == '') {
        $from = 0;
    }

    $queryExplArr = explode("\n", $queryStr);
    $conditionOR = true;

    foreach ($queryExplArr as $value) {
        $filterList = array();
        $queryBrk = trim($value);

        if ($queryBrk != 'OR') {

            $retVal = __buildQryStr($queryBrk, $filterfields, $db);
            $mustVal = trim($retVal['must']);
            $mustNotVal = trim($retVal['mustNot']);
            $filterCrit = $retVal['filterData'];
            $uniloop = $retVal['uniloop'];

            if ($mustVal != '') {
                $mustStr = $retVal['must'] . ',';
            }

            if ($mustNotVal != '') {
                $mustNotArrr = $retVal['mustNot'] . ',';
            }

            if (safe_count($filterCrit) > 0) {
                array_push($filterList, $filterCrit);
            }

            $assetQry = __buildQrySection($mustStr, $mustNotArrr, $selectfields, $machineid, $from, $size);
            $resultData = parseAssetData($assetQry['finalQry'], $displayFieldsColumn, $filterCrit, $assetQry['condType'], $uniloop);
            pushAssetData($resultData, $indexname);
        }
    }

    return $indexname;
}

function __buildQrySection($mustStr, $mustNotArrr, $selectfields, $filterCond, $from, $size)
{


    $finalCondt = '';

    $finalMustCondt = rtrim($mustStr, ',');
    $finalNotCondt = rtrim($mustNotArrr, ',');
    $finalMustCondt = trim($finalMustCondt);
    $finalNotCondt = trim($finalNotCondt);

    if ($finalMustCondt != '' && $finalNotCondt != '') {
        $finalCondt .= $finalMustCondt . ',' . $finalNotCondt;
    } elseif ($finalNotCondt != '' && $finalMustCondt == '') {
        $finalCondt .= $finalNotCondt;
    } elseif ($finalNotCondt == '' && $finalMustCondt != '') {
        $finalCondt .= $finalMustCondt;
    }
    $contionType = 'must';
    if ($finalNotCondt != '') {
        $contionType = 'must_not';
    } else {
        $contionType = 'should';
    }





    $finalQry = ' {
        "_source": [
          ' . $selectfields . '
        ],
        "from" : ' . $from . ', 
        "size" : ' . $size . ',
        "sort" : [
           { "machineid" : {"order" : "asc"} }
        ],
        "query": {
          "bool": {
            "' . $contionType . '": ' . $finalCondt . $filterCond . '
          }
        }
   }';
    return array("finalQry" => $finalQry, "condType" => $contionType);
}

function __buildQryStr($queryStr, $filterfields, $db)
{

    $includeArr = array("contains", "equal to", "begins with", "ends with");
    $excludeArr = array("does not contain", "not equal to");
    $filterArr = array("less than", "greater than", "less than or equal to", "greater than or equal to");


    $findme = 'AND';
    $pos = strpos($queryStr, $findme);


    $includeDataid = [];
    $includeVal = [];
    $filterList = [];

    $excludeDataid = array();
    $excludeVal = array();

    if ($pos !== false) {

        $condArr = explode($findme, $queryStr);
        $condCnt = safe_count($condArr);
        foreach ($condArr as $criteria) {

            $dataVal = __searchCondition($criteria, $db);
            $dataId = trim($dataVal["dataid"]);
            $condition = trim($dataVal["condition"]);

            $condtVal = __getMatchString($criteria);

            if (in_array($condition, $includeArr)) {


                if (in_array($dataId, $filterfields)) {

                    $subVal = 'like';
                    if ($condition == 'equal to') {
                        $subVal = 'exact';
                    }

                    $condtVal1 = $condtVal . '##' . $subVal;
                    $v = array($dataId => $condtVal1);
                    $filterList[$condtVal1] = $dataId;
                }

                $includeDataid[] = $dataId;
                $includeVal[] = $condtVal . '##' . $dataId;
            } elseif (in_array($condition, $excludeArr)) {

                if (in_array($dataId, $filterfields)) {

                    $subVal = 'like';
                    if ($condition == 'not equal to') {
                        $subVal = 'exact';
                    }

                    $condtVal1 = $condtVal . '##' . $subVal;
                    $v = array($dataId => $condtVal1);
                    $filterList[$condtVal1] = $dataId;
                }
                $excludeDataid[] = $dataId;
                $excludeVal[] = $condtVal . '##' . $dataId;
            }
        }

        $must = __buildWhQry($includeDataid, $includeVal, "must");
        $mustNot = __buildWhQry($excludeDataid, $excludeVal, "must_not");

        $retVal = array("must" => $must['qury'], "mustNot" => $mustNot['qury'], "filterData" => $filterList, "uniloop" => $must['uniloop']);

        return $retVal;
    } else {

        $dataVal = __searchCondition($queryStr, $db);
        $dataId = $dataVal["dataid"];
        $condition = $dataVal["condition"];

        $condtVal = __getMatchString($queryStr);
        if (in_array($condition, $includeArr)) {

            if (in_array($dataId, $filterfields)) {
                $subVal = 'like';
                if ($condition == 'equal to') {
                    $subVal = 'exact';
                }
                $condtVal1 = $condtVal . '##' . $subVal;
                $v = array($dataId => $condtVal1);
                $filterList[$condtVal1] = $dataId;
            }

            $includeDataid[] = $dataId;
            $includeVal[] = $condtVal . '##' . $dataId;
        } elseif (in_array($condition, $excludeArr)) {

            if (in_array($dataId, $filterfields)) {

                $subVal = 'like';
                if ($condition == 'not equal to') {
                    $subVal = 'exact';
                }

                $condtVal1 = $condtVal . '##' . $subVal;
                $v = array($dataId => $condtVal1);
                $filterList[$condtVal1] = $dataId;
            }

            $excludeDataid[] = $dataId;
            $excludeVal[] = $condtVal . '##' . $dataId;
        }


        $must    = __buildWhQry($includeDataid, $includeVal, "must");
        $mustNot = __buildWhQry($excludeDataid, $excludeVal, "must_not");
        $retVal = array("must" => $must['qury'], "mustNot" => $mustNot['qury'], "filterData" => $filterList, "uniloop" => $must['uniloop']);
        return $retVal;
    }
}

function __buildWhQry($dataid, $queryVal, $type)
{
    $dataVal = '';
    $qryStr = '';
    $dataidCnt = safe_count($dataid);
    $mustnot = [];
    $filterCnt  = safe_count($dataid);
    $filteruniq = array_unique($dataid);

    $cnt = safe_count($queryVal);
    $j = 0;
    $wh1 = '';
    $wh2 = '';
    foreach ($queryVal as $wh) {
        $matchExpl = explode("##", $wh);
        $matchVal = trim($matchExpl[0]);
        $dataname = trim($matchExpl[1]);
        $searchVal = '\"*' . trim($matchVal) . '*\"';

        $wh1 .= ' {
          "match": {
            "' . $dataname . '": {
              "query": "' . $searchVal . '",
              "operator": "and",
              "fuzziness": "AUTO"
            }
          }
        },';

        if (!in_array($dataname, $mustnot)) {
            $wh2 .= ' {
              "exists": {
                "field": "' . $dataname . '"
              }
            },';
        }
        $mustnot[] = $dataname;
        $j++;
    }
    if ($type == 'must_not') {
        $whr = '';
    } else {
        $whr = rtrim($wh1, ',');
    }
    $whr1 = rtrim($wh2, ',');
    if ($dataidCnt > 0 && $cnt > 0) {
        $qryStr = '
                [
                  ' . $whr . '
                ],
                "must":[
                    ' . $whr1 . '
                ]
              
            
            ';
    }

    $uniqLoop = 0;
    if ($filterCnt != safe_count($filteruniq)) {
        $uniqLoop = 1;
    }
    return array("qury" => $qryStr, "uniloop" => $uniqLoop);
}

function __searchCondition($criteria, $db)
{



    $condArr = array("contains", "does not contain", "equal to", "not equal to", "begins with", "ends with", "less than", "greater than", "less than or equal to", "greater than or equal to");

    foreach ($condArr as $value) {

        $foundVal = strpos($criteria, $value);
        if ($foundVal !== false) {

            $dataNameArr = explode($value, $criteria);
            $dataname = $dataNameArr[0];
            $dataname = trim(str_replace("(", "", $dataname));
            $result = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '', $dataname));
            $result = str_replace('-', '', $result);
            $conArr = array("dataid" => $result, "condition" => $value);

            return $conArr;
        } else {
        }
    }
}

function __getDataId($dataname, $db)
{

    $dataid = 0;
    $sql = "select D.dataid from " . $GLOBALS['PREFIX'] . "asset.DataName D where D.name='$dataname'";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        $dataid = $res['dataid'];
    }
    return $dataid;
}

function __getMatchString($str)
{

    $searchVal = '';
    if (preg_match("/'([^']+)'/", $str, $m)) {
        $searchVal = $m[1];
    }
    return $searchVal;
}

function __buildSelFields($selfields, $db)
{

    $fields = array();
    $list = array();
    $dataids = array();
    $selFields = array();
    $selFieldVal = array();

    if ($selfields) {

        $orderfields = $selfields;
        $selfields = str_replace(":", "','", substr($selfields, 1, -1));

        $orderfields = str_replace(":", ",", substr($orderfields, 1, -1));
        $orderfields = "'" . $orderfields . "'";


        $query = "SELECT dataid, clientname, groups,name FROM " . $GLOBALS['PREFIX'] . "asset.DataName WHERE name IN ('$selfields') group by dataid order by find_in_set(name, $orderfields)";
        $res = mysql_query($query, $db);
        if ($res) {
            $value = '_id';
            $machineid = 'machineid';
            $fields[] = '"' . $value . '"';
            $selFields[] = '"' . $machineid . '"';
            while ($row = mysqli_fetch_assoc($res)) {
                $fields[] = '"' . $row['dataid'] . '"';
                $dataids[] = $row['dataid'];
                $list[$row['clientname']] = $row['dataid'];

                $result = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '', $row['name']));
                $result = str_replace('-', '', $result);
                $selFields[] = '"' . trim($result) . '"';
                $selFieldVal[] = trim($result);
            }
        } else {
            $value = '*';
            $fields[] = '"' . $value . '"';
            $selFields[] = '"' . $value . '"';
            $selFieldVal[] = $value;
        }
    } else {

        $value = '*';
        $fields[] = '"' . $value . '"';
        $selFields[] = '"' . $value . '"';
        $selFieldVal[] = $value;
    }

    return array("dataname" => $list, "dataid" => $fields, "selfields" => $dataids, "dispfields" => $selFields, "selFieldVal" => $selFieldVal);
}
