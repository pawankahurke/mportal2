<?php

function getAssetDataByQry($qryid, $machineid, $from, $size, $db)
{

    $res = __getAssetELQry($qryid, $machineid, $from, $size, $db);

    $status     = $res['status'];
    if ($status == 'Success') {

        $assetQry   = $res['elqry'];
        $selfields  = $res['selfields'];
        $finalResult = parseAssetData($assetQry, $selfields);

        return $finalResult;
    } else {
        return json_encode($res);
    }
}

function parseAssetData($assetQry, $displayFieldsColumn)
{

    $assetData = getAllAssets($assetQry);

    $response = safe_json_decode($assetData, true);

    $result = null;
    $i = 0;
    $tempArray = [];

    $groupedData = [];
    $tempGoupedData = [];
    $newDNA = [];
    $maxOrd = array();

    foreach ($displayFieldsColumn as $key => $value) {
        if ($key == 'Machine Name' || $key == 'Site Name' || $key == 'Host') {
            $newDNA[] = $value;
        }
    }
    $array = [];
    foreach ($response['hits']['hits'] as $key => $val) {

        $machineId = $val['_id'];
        $result = $val['_source'];
        $keys = safe_array_keys($result);
        foreach ($keys as $value1) {
            $res = $result[$value1];
            ksort($res);
            end($res);
            $fordVal = key($res);
            $maxOrd[] = $fordVal;
        }

        foreach ($result as $dataid => $dataValue) {
            ksort($dataValue);
            foreach ($dataValue as $key1 => $value) {
                $ordinal[$key1] = $value;
            }
            $tempArray[$dataid] = $ordinal;
            $dataidList[] = $dataid;
            $ordinal = array();
        }


        $dataidListNew = [];
        foreach (array_unique($dataidList) as $key2 => $value) {
            $dataidListNew[] = $value;
        }

        $arrDataRes = ordinalLoopFunction_new($tempArray, $maxOrd, $dataidListNew, $newDNA);

        foreach ($arrDataRes as $key3 => $value) {
            $machineIdNew = $machineId . '_' . ($key3 + 1);
            $array[$machineIdNew] = $value;
        }
    }

    return json_encode($array);
}

function ordinalLoopFunction_new($OrdArray, $maxOrdinal, $dataidList, $newDNA)
{

    $newArr = [];
    $marOrd = max($maxOrdinal);

    for ($var = 1; $var <= $marOrd; $var++) {
        foreach ($OrdArray as $key => $value) {
            $fvar = $var;
            if (in_array($key, $newDNA)) {
                if ($value[$fvar] == '') {
                    $fvar = 1;
                }
            }
            $newArr[$var][] = $value[$fvar];
        }
    }

    foreach ($newArr as $key => $value) {
        $finalArr = [];
        $i = 1;
        foreach ($value as $nkey => $nval) {

            $data = ($nval != '') ? $nval : '-';
            $finalArr[$dataidList[$nkey]][$key] = $data;
            $i++;
        }
        $returnArr[] = $finalArr;
    }
    return $returnArr;
}

function getAllAssets($params)
{

    global $elastic_url;
    $url = $elastic_url . "assetdatalatest/_search?pretty&filter_path=took,hits.hits._id,hits.hits._score,hits.hits._source";

    try {
        $headers = array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        );
        $headers[] = "Content-Type: application/json";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
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

function __getAssetELQry($qryid, $machineid, $from, $size, $db)
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
            $selectfields = implode(',', $datafields['dataid']);
            $dataNames = $datafields['dataname'];

            $assetQry = __buildAssetELQry($searchStr, $selectfields, $machineid, $from, $size, $db);

            $finalQry = array("status" => "Success", "elqry" => $assetQry, "selfields" => $dataNames);
        } else {
            $finalQry = array("status" => "Error", "msg" => "Search string empty");
        }
    } else {
        $finalQry = array("status" => "Error", "msg" => "Invalid search id");
    }

    return $finalQry;
}

function __buildAssetELQry($queryStr, $selectfields, $machineid, $from, $size, $db)
{

    $mustStr     = '';
    $mustNotArrr = '';
    $filterCond  = '';

    if ($size == '' || $size == 0) {
        $size = 5;
    }

    if ($from == '') {
        $from = 0;
    }

    $queryExplArr = explode("\n", $queryStr);
    $conditionOR = true;

    foreach ($queryExplArr as $value) {
        $queryBrk = trim($value);
        if ($queryBrk == 'OR') {
            $conditionOR = false;
        }
        if ($queryBrk != 'OR') {
            $retVal = __buildQryStr($queryBrk, $db);
            $mustVal = trim($retVal['must']);
            $mustNotVal = trim($retVal['mustNot']);
            if ($mustVal != '') {
                $mustStr .= $retVal['must'] . ',';
            }
            if ($mustNotVal != '') {
                $mustNotArrr .= $retVal['mustNot'] . ',';
            }
        }
    }

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
    if ($conditionOR == false) {
        $contionType = 'should';
    }

    if ($machineid != '') {

        $filterCond = ',
            "filter": {
            "terms": {
              "_id": [' . $machineid . '] 
            }
          }';
    }

    $finalQry = ' {
        "_source": [
          ' . $selectfields . '
        ],
        "sort" : [
           { "_id" : {"order" : "asc"} }
        ],
        "from" : ' . $from . ', 
        "size" : ' . $size . ',
        "query": {
          "bool": {
            "' . $contionType . '": [' . $finalCondt . ']' . $filterCond . '
          }
        }
   }';

    return $finalQry;
}

function __buildQryStr($queryStr, $db)
{



    $includeArr = array("contains", "equal to", "begins with", "ends with");
    $excludeArr = array("does not contain", "not equal to");
    $filterArr = array("less than", "greater than", "less than or equal to", "greater than or equal to");


    $findme = 'AND';
    $pos = strpos($queryStr, $findme);


    $includeDataid = array();
    $includeVal = array();

    $excludeDataid = array();
    $excludeVal = array();

    if ($pos !== false) {

        $condArr = explode($findme, $queryStr);
        $condCnt = safe_count($condArr);
        foreach ($condArr as $criteria) {

            $dataVal = __searchCondition($criteria, $db);
            $dataId = $dataVal["dataid"];
            $condition = $dataVal["condition"];

            $condtVal = __getMatchString($criteria);

            if (in_array($condition, $includeArr)) {
                array_push($includeDataid, $dataId);
                array_push($includeVal, $condtVal);
            } elseif (in_array($condition, $excludeArr)) {
                array_push($excludeDataid, $dataId);
                array_push($excludeVal, $condtVal);
            }
        }

        $must = __buildWhQry($includeDataid, $includeVal, "must");
        $mustNot = __buildWhQry($excludeDataid, $excludeVal, "must_not");
        $retVal = array("must" => $must, "mustNot" => $mustNot);

        return $retVal;
    } else {

        $dataVal = __searchCondition($queryStr, $db);
        $dataId = $dataVal["dataid"];
        $condition = $dataVal["condition"];

        $condtVal = __getMatchString($queryStr);
        if (in_array($condition, $includeArr)) {
            array_push($includeDataid, $dataId);
            array_push($includeVal, $condtVal);
        } elseif (in_array($condition, $excludeArr)) {

            array_push($excludeDataid, $dataId);
            array_push($excludeVal, $condtVal);
        }

        $must = __buildWhQry($includeDataid, $includeVal, "must");
        $mustNot = __buildWhQry($excludeDataid, $excludeVal, "must_not");
        $retVal = array("must" => $must, "mustNot" => $mustNot);
        return $retVal;
    }
}

function __buildWhQry($dataid, $queryVal, $type)
{

    $qryStr = '';
    $dataidCnt = safe_count($dataid);
    foreach ($dataid as $ids) {
        $dataVal .= '"full_' . $ids . '",';
    }
    $fields = rtrim($dataVal, ',');

    $cnt = safe_count($queryVal);
    $j = 0;
    $wh1 = '';
    foreach ($queryVal as $wh) {

        $searchVal = '\"*' . $wh . '*\"';
        if ($j == $cnt - 1) {
            $wh1 .= $searchVal;
        } else {
            $wh1 .= $searchVal . ' AND ';
        }
        $j++;
    }

    if ($dataidCnt > 0 && $cnt > 0) {
        $qryStr = '{
              "bool": {
                "' . $type . '": [
                  {
                    "query_string" : {
                      "query" : "' . $wh1 . '",
                      "type":   "phrase_prefix",
                      "fields" : [' . $fields . ']
                    }
                  }
                ]
              }
            }
            ';
    }

    return $qryStr;
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
            $dataid = __getDataId($dataname, $db);
            $conArr = array("dataid" => $dataid, "condition" => $value);

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
    if ($selfields) {

        $selfields = str_replace(":", "','", substr($selfields, 1, -1));
        $query = "SELECT dataid, clientname, groups,name FROM " . $GLOBALS['PREFIX'] . "asset.DataName WHERE name IN ('$selfields') order by ordinal";
        $res = mysql_query($query, $db);
        if ($res) {
            $value = '_id';
            $fields[] = '"' . $value . '"';
            while ($row = mysqli_fetch_assoc($res)) {
                $fields[] = '"' . $row['dataid'] . '"';
                $list[$row['clientname']] = $row['dataid'];
            }
        } else {
            $value = '*';
            $fields[] = '"' . $value . '"';
        }
    } else {

        $value = '*';
        $fields[] = '"' . $value . '"';
    }

    return array("dataname" => $list, "dataid" => $fields);
}
