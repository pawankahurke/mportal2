<?php


function RESOL_GetData_new_EL($db, $evnttype)
{

    $con = $evnttype;
    $eventCondData = createCondition($con, $db);
    $columnArrStr = $eventCondData['columnArr'];
    $tagfilter = $eventCondData['tagfilter'];
    $sitefilter = $eventCondData['sitefilter'];
    $fromDate = $eventCondData['fromdate'];
    $toDate = $eventCondData['todate'];
    $macFilter = $eventCondData['macfilter'];
    $elasticurl = $eventCondData['eurl'] . '/_search?pretty';

    $result = RESOLEL_getData_aggregate_new_EL($columnArrStr, $tagfilter, $sitefilter, $fromDate, $toDate, $elasticurl, $macFilter, 'description');
    return $result;
}

function RESOL_GetDetail_new_EL($db, $namelist, $eventType)
{

    $con = $eventType;
    $eventCondData = createCondition($con, $db);
    $columnArrStr = $eventCondData['columnArr'];
    $tagfilter = $eventCondData['tagfilter'];
    $sitefilter = $eventCondData['sitefilter'];
    $fromDate = $eventCondData['fromdate'];
    $toDate = $eventCondData['todate'];
    $macFilter = $eventCondData['macfilter'];
    $elasticurl = $eventCondData['eurl'] . '/_search?pretty';

    $result = RESOLEL_getData_new_EL($columnArrStr, $tagfilter, $sitefilter, $fromDate, $toDate, $elasticurl, $namelist, $macFilter);
    return $result;
}

function createCondition($cond)
{
    global $elastic_url;
    $db = db_connect();

    $fromDate = time() - (15 * 24 * 60 * 60);
    $toDate = time();

    $columnArr = '';
    $machinefilter = '';

    $fromDateIndex = date('Y-m-d', $fromDate);
    $toDateIndex = date('Y-m-d', $toDate);
    $indexName = createEventIndex($fromDateIndex, $toDateIndex);

    $url = $elastic_url . "$indexName";

    $columnArrStr = '"idx","description","machine","text1","servertime","customer","clientversion","clientsize","uuid"';

    $searchType = $_SESSION['searchType'];
    $sitename = $_SESSION['searchValue'];
    $machinename = $_SESSION['searchValue'];

    if ($_SESSION['searchValue'] == 'All') {
        $key = "";
        $dataScope = UTIL_GetSiteScope($db, $_SESSION['searchValue'], $searchType);
        foreach ($dataScope as $key => $value) {
            $sitefilter .= '{"term": {"customer": "' . $value . '"}},';
        }
        $sitefilter = rtrim($sitefilter, ',');
    } else {
        if ($searchType == 'Sites') {
            $sitefilter = '{"term": {"customer": "' . $sitename . '"}}';
        } else if ($searchType == 'ServiceTag') {
            $sql = "select cust from " . $GLOBALS['PREFIX'] . "asset.Machine where host='$machinename' limit 1";
            $sqlRes = find_one($sql, $db);
            $site = $sqlRes['cust'];

            $sitefilter = '';
            $machinefilter = '{ "term": { "machine": "' . $machinename . '" }},{ "term": { "customer": "' . $site . '" }}';
        } else {
            $dataScope = UTIL_GetSiteScope($db, $_SESSION['searchValue'], $searchType);
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);

            foreach ($machines as $key => $value) {
                $sitefilter .= '{"term": {"machine": "' . $value . '"}},';
            }
            $sitefilter = rtrim($sitefilter, ',');
        }
    }

    $tagfilter = '{ "term" : {"evntType" : "' . $cond . '"}}';

    $returnArr['columnArr'] = $columnArrStr;
    $returnArr['sitefilter'] = $sitefilter;
    $returnArr['tagfilter'] = $tagfilter;
    $returnArr['eurl'] = $url;
    $returnArr['fromdate'] = $fromDate;
    $returnArr['todate'] = $toDate;
    $returnArr['macfilter'] = $machinefilter;

    return $returnArr;
}

function RESOLEL_getData_aggregate_new_EL($columnArrStr, $tagfilter, $sitefilter, $fromDate, $toDate, $elasticurl, $macfilter, $colName)
{

    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length =  10000;

    if ($sitefilter != '') {
        $cond = '"minimum_should_match": 1,
                            "should" : [
                                ' . $sitefilter . '
                            ],
                             "must": [
                                ' . $tagfilter . '
                            ],';
    } else {
        $cond = '"must": [
                        ' . $macfilter . ',
                        ' . $tagfilter . '
                    ],';
    }

    $params = '{
                   "from" : ' . $start . ', "size" : ' . $length . ',
                    "_source": [' . $columnArrStr . '],
                    "query": {
                      "bool": {
                            ' . $cond . '
                            "filter": [
                                { "range": { "servertime": { "gte": ' . $fromDate . ', "lte": ' . $toDate . ' } } }
                        ]
                      }
                    },"aggs": {
		"id1_count": {
			"terms": {
				"field": "description"
			},
			"aggs": {
				"id2_count": {
					"terms": {
						"field": "machine"
					},
					"aggs": {
						"top_sales_hits": {
							"top_hits": {
								"sort": [{
									"idx": {
										"order": "desc"
									}
								}],
								"_source": {
									"includes": [
										"customer",
										"scrip",
										"machine",
										"evntType",
                                                                                "description"
									]
								},
                                                                "size": 1
							}
						}
					}
				}
			}
		}
	}
                  }';
    $params1 = '{
                   "from" : ' . $start . ', "size" : ' . $length . ',
                    "_source": [' . $columnArrStr . '],
                    "query": {
                      "bool": {
                            "minimum_should_match": 1,
                            "should" : [
                                ' . $sitefilter . '
                            ],
                        "must": [
                                ' . $tagfilter . '
                            ],
                            "filter": [
                                { "range": { "servertime": { "gte": ' . $fromDate . ', "lte": ' . $toDate . ' } } }
                        ]
                      }
                    }
                  }';

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $elasticurl);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $res = curl_exec($curl);
    curl_close($curl);
    $result = FORMAT_RESOL_Aggre_Data_new_EL($res, $colName);

    return $result;
}

function RESOLEL_getData_new_EL($columnArrStr, $tagfilter, $sitefilter, $fromDate, $toDate, $elasticurl, $namelist, $macfilter)
{

    $nameCond = '{"term": {"description": "' . $namelist . '"}}';
    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length = url::issetInRequest('length') ? url::requestToAny('length') : 10000;

    if ($sitefilter != '') {
        $cond = '"minimum_should_match": 1,
                            "should" : [
                                ' . $sitefilter . '
                            ],
                        "must": [
                                ' . $tagfilter . ',
                                ' . $nameCond . '
                            ],';
    } else {
        $cond = '"must": [
                        ' . $macfilter . ',
                        ' . $nameCond . ',
                        ' . $tagfilter . '
                    ],';
    }

    $params = '{
                     "from" : ' . $start . ', "size" : ' . $length . ',
                    "_source": [' . $columnArrStr . '],
                    "query": {
                      "bool": {
                            ' . $cond . '
                            "filter": [
                                { "range": { "servertime": { "gte": ' . $fromDate . ', "lte": ' . $toDate . ' } } }
                        ]
                      }
                    }
                  }';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $elasticurl);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $res = curl_exec($curl);
    curl_close($curl);
    $result = FORMAT_RESOL_Data_new_EL($res);
    return $result;
}

function FORMAT_RESOL_Aggre_Data_new_EL($result, $colName)
{
    $curlArray = safe_json_decode($result, TRUE);
    $data = array();

    if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 1) {
        $loopsArray = $curlArray['hits']['hits'];

        foreach ($loopsArray as $key => $value) {
            $data[$key] = $value['_source'];
        }

        $loopsArray = $curlArray['aggregations']['buckets'];
        foreach ($loopsArray as $key => $value) {
            $data[$key][$colName] = $value['key'];
        }

        $temp = array();
        foreach ($data as $val) {
            if (!in_array($val['description'], $temp)) {
                array_push($temp, $val['description']);
            }
        }
        $res = array("total" => count($temp), "data" => $data);
    } else if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
        $data[0][$colName] = $curlArray['aggregations']['id1_count']['buckets'][0]['key'];
        $res = array("total" => 1, "data" => $data);
    } else {
        $res = array("total" => 0, "data" => $data);
    }

    return $res;
}

function FORMAT_RESOL_Data_new_EL($result)
{
    $curlArray = safe_json_decode($result, TRUE);

    if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 1) {
        $loopsArray = $curlArray['hits']['hits'];
        $total = $curlArray['hits']['total'];
        foreach ($loopsArray as $key => $value) {
            $data[$key] = $value['_source'];
        }
    } else if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
        $data[0] = $curlArray['hits']['hits'][0]['_source'];
        $total = $curlArray['hits']['total'];
    } else {
        $data = array();
    }
    return array("data" => $data, "total" => $total);
}
