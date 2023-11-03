<?php


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
            $dataNames    = $datafields['dataname'];
            $filterfields = $datafields['selfields'];
            $sliced_array = array();
            $assetQry = __buildAssetELQry($searchStr, $selectfields, $filterfields, $machineid, $dataNames, $from, $size, $db);


            $finalQry = array("status" => "Success", "msg" => $assetQry);
        } else {

            $datafields = __buildSelFields($displayfields, $db);
            $selectfields = implode(',', $datafields['dataid']);
            $dataNames    = $datafields['dataname'];
            $filterfields = $datafields['selfields'];
            $assetQry = __matchAll($selectfields, $machineid, $from, $size);

            $result = parseAssetData($assetQry['finalQry'], $dataNames, $assetQry['filterData'], "should");
            $finalQry = array("status" => "Success", "msg" => $result);
        }
    } else {
        $finalQry = array("status" => "Error", "msg" => "Invalid search id");
    }
    return $finalQry;
}

function __matchAll($selectfields, $machineid, $from, $size)
{

    $filterList = array();
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
            "must": [
              {
                "match_all": {}
              }
            ]' . $filterCond . '
          }
        }
   }';

    return array("finalQry" => $finalQry, "filterData" => $filterList);
}


function __buildAssetELQry($queryStr, $selectfields, $filterfields, $machineid, $displayFieldsColumn, $from, $size, $db)
{

    $mustStr     = '';
    $mustNotArrr = '';
    $filterCond  = '';

    $resultArr   = array();
    $resultData  = [];
    if ($size == '' || $size == 0) {
        $size = 5;
    }

    if ($from == '') {
        $from = 0;
    }

    $queryExplArr = explode("\n", $queryStr);
    $conditionOR = true;

    foreach ($queryExplArr as $value) {
        $filterList  = array();
        $queryBrk = trim($value);


        if ($queryBrk != 'OR') {
            $retVal = __buildQryStr($queryBrk, $filterfields, $db);
            $mustVal = trim($retVal['must']);
            $mustNotVal = trim($retVal['mustNot']);
            $filterCrit = $retVal['filterData'];
            if ($mustVal != '') {
                $mustStr = $retVal['must'] . ',';
            }

            if ($mustNotVal != '') {
                $mustNotArrr = $retVal['mustNot'] . ',';
            }

            if (safe_count($filterCrit) > 0) {
                array_push($filterList, $filterCrit);
            }

            $assetQry   = __buildQrySection($mustStr, $mustNotArrr, $selectfields, $machineid, $from, $size);
            $resultData = parseAssetData($assetQry['finalQry'], $displayFieldsColumn, $filterCrit, $assetQry['condType']);

            if (!empty($resultData)) {
                array_push($resultArr, $resultData);
            }
        }
    }

    return $resultArr;
}

function __buildQrySection($mustStr, $mustNotArrr, $selectfields, $machineid, $from, $size)
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

    if ($finalNotCondt != '') {
        $contionType = 'must';
    } else {
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
    return array("finalQry" => $finalQry, "condType" => $contionType);
}

function __buildQryStr($queryStr, $filterfields, $db)
{

    $includeArr = array("contains", "equal to", "begins with", "ends with");
    $excludeArr = array("does not contain", "not equal to");
    $filterArr = array("less than", "greater than", "less than or equal to", "greater than or equal to");


    $findme = 'AND';
    $pos = strpos($queryStr, $findme);


    $includeDataid = array();
    $includeVal = array();
    $filterList = array();

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

                array_push($includeDataid, $dataId);
                array_push($includeVal, $condtVal);
            } elseif (in_array($condition, $excludeArr)) {
                array_push($excludeDataid, $dataId);
                array_push($excludeVal, $condtVal);
            }
        }


        $must    = __buildWhQry($includeDataid, $includeVal, "must");
        $mustNot = __buildWhQry($excludeDataid, $excludeVal, "must_not");
        $retVal  = array("must" => $must, "mustNot" => $mustNot, "filterData" => $filterList);

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
            array_push($includeDataid, $dataId);
            array_push($includeVal, $condtVal);
        } elseif (in_array($condition, $excludeArr)) {

            array_push($excludeDataid, $dataId);
            array_push($excludeVal, $condtVal);
        }


        $must = __buildWhQry($includeDataid, $includeVal, "must");
        $mustNot = __buildWhQry($excludeDataid, $excludeVal, "must_not");
        $retVal = array("must" => $must, "mustNot" => $mustNot, "filterData" => $filterList);
        return $retVal;
    }
}

function __buildWhQry($dataid, $queryVal, $type)
{
    $dataVal = '';
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

        $searchVal = '\"*' . trim($wh) . '*\"';
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

    $fields  = array();
    $list    = array();
    $dataids = array();

    if ($selfields) {

        $selfields = str_replace(":", "','", substr($selfields, 1, -1));
        $query = "SELECT dataid, clientname, groups,name FROM " . $GLOBALS['PREFIX'] . "asset.DataName WHERE name IN ('$selfields') group by dataid order by ordinal";
        $res = mysqli_query($db, $query);
        if ($res) {
            $value = '_id';
            $fields[] = '"' . $value . '"';
            while ($row = mysqli_fetch_assoc($res)) {
                $fields[] = '"' . $row['dataid'] . '"';
                $dataids[]  = $row['dataid'];
                $list[$row['clientname']] = $row['dataid'];
            }
        } else {
            $value      = '*';
            $fields[]   = '"' . $value . '"';
        }
    } else {

        $value      = '*';
        $fields[]   = '"' . $value . '"';
    }

    return array("dataname" => $list, "dataid" => $fields, "selfields" => $dataids);
}
