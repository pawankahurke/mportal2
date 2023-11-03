<?php

global $elastic_url;

function getDataFromEL($tableName, $source, $cond)
{

    $condition = '';

    foreach ($cond as $key => $value) {
        $condition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
    }
    $where = rtrim($condition, ',');
    $result = getData($tableName, $source, $where);
    return $result;
}

function getData($tableName, $source, $con)
{

    global $elastic_url;
    $url = $elastic_url . $tableName . "/_search?pretty&size=10000";

    $params = '{
        "_source": [' . $source . '],
        "query": {
             "bool": {
                 "must" : [
                      ' . $con . '
                 ]
             }
         }
     }';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $res = curl_exec($curl);
    curl_close($curl);

    $result = FORMAT_Data($res);
    return $result;
}

function FORMAT_Data($result)
{
    $curlArray = safe_json_decode($result, TRUE);

    if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 1) {
        $loopsArray = $curlArray['hits']['hits'];
        foreach ($loopsArray as $key => $value) {
            $data[$key] = $value['_source'];
        }
    } else if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
        $data[0] = $curlArray['hits']['hits'][0]['_source'];
    } else {
        $data = array();
    }
    return $data;
}

function getComplianceTwentyFourHourTrend($sitename, $userid, $last24hr, $lasthr, $itemtype, $machine)
{

    global $elastic_url;

    $from = date(strtotime("-24 hour"));
    $now = time();

    $fromDate = date('Y-m-d', $from);
    $toDate = date('Y-m-d', $now);

    $indexName = createComplaincInd($fromDate, $toDate);

    $url = $elastic_url . $indexName . "/_search?pretty&size=10000";

    if ($machine != '') {
        $term = "," . $machine;
    }
    for ($i = 1, $j = 0; $i <= 3; $i++, $j++) {
        $status = $i;
        $query = '{
            "query" : {
               "constant_score" : { 
                  "filter" : {
                     "bool" : {
                       "must":[  
                            {  
                               "bool":{  
                                    "minimum_should_match":1,
                                    "should":[' . $sitename . ']
                                }
                            },
                            {"term": {"userid": "' . $userid . '"}},
                            {"term": {"itemtype": "' . $itemtype . '"}}
                                ' . $term . ',
                              {"term": {"status": "' . $status . '"}}       
                        ],"filter": [ { "range": { "servertime": { "gte": "' . $last24hr . '" , "lte": "' . $now . '" }}} ]
                    }
                  }
               }
            }
         }';

        $result = curlCommonFunction($url, $query);
        $data = safe_json_decode($result, TRUE);
        $total = $data['hits']['total'];

        $hits = $data['hits']['hits'];
        foreach ($hits as $val) {
            $host = $val['_source']['host'];
            $temp[$host] = $val['_source'];
        }
        $res[$j]['count'] = safe_count($temp);
        $res[$j]['status'] = $status;
        $res[$j]['itemtype'] = $itemtype;
    }
    return $res;
}

function getComplianceLastHourTrend($sitename, $userid, $servertime, $itemtype, $machine)
{

    global $elastic_url;
    $tableName = date('Y-m-d', time());

    $url = $elastic_url . "compliancesummary_" . $tableName . "/_search";

    if ($machine != '') {
        $term = "," . $machine;
    }

    for ($i = 1, $j = 0; $i <= 3; $i++, $j++) {
        $status = $i;
        $query = '{
            "query" : {
               "constant_score" : { 
                  "filter" : {
                     "bool" : {
                       "must":[  
                            {  
                               "bool":{  
                                    "minimum_should_match":1,
                                    "should":[' . $sitename . ']
                                }
                            },
                            {"term": {"userid": "' . $userid . '"}},
                            {"term": {"itemtype": "' . $itemtype . '"}}
                                ' . $term . ',
                              {"term": {"status": "' . $status . '"}}       
                        ],"filter": [ { "range": { "servertime": { "gte": "' . $servertime . '"}}} ]
                    }
                  }
               }
            }
         }';
        $result = curlCommonFunction($url, $query);
        $data = safe_json_decode($result, TRUE);
        $total = $data['hits']['total'];
        $res[$j]['count'] = $total;
        $res[$j]['status'] = $status;
        $res[$j]['itemtype'] = $itemtype;
    }

    return $res;
}

function format_output($res)
{
    $result = safe_json_decode($res, TRUE);
    if (isset($result['hits']['total']) && $result['hits']['total'] > 1) {
        $loopsArray = $result['hits']['hits'];
        foreach ($loopsArray as $key => $value) {
            $data[$key] = $value['_source'];
        }
    } else if (isset($result['hits']['total']) && $result['hits']['total'] > 0) {
        $data = $result['hits']['hits'][0]['_source'];
    } else {
        $data = array();
    }

    return $data;
}



function curlCommonFunction($url, $query)
{

    try {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($query)
            )
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $result;
}

function createComplaincInd($fromDate, $toDate)
{

    $indexName = '';
    $date = $fromDate;
    $end_date = $toDate;
    while (strtotime($date) <= strtotime($end_date)) {

        $indexName .= 'compliancesummary_' . $date . ',';

        $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
    }
    return rtrim($indexName, ',');
}

function getComplianceTrend($site, $userId, $itemType, $machine)
{

    global $elastic_url;

    $from = date(strtotime("-14 days"));
    $now = time();

    $fromDate = date('Y-m-d', $from);
    $toDate = date('Y-m-d', $now);
    $date = $fromDate;
    $result = array();
    while (strtotime($date) <= strtotime($toDate)) {
        $fromDate = $date;
        $date = date("Y-m-d", strtotime("+1 day", strtotime($fromDate)));

        $indexName = createComplaincInd($fromDate, $date);
        $url = $elastic_url . $indexName . "/_search?pretty&size=10000";
        $result[$fromDate] = getComplianceDataBasedOnDate_new($site, $userId, $itemType, $fromDate, $date, $url, $machine);
    }

    $i = 0;
    foreach ($result as $key => $val) {
        foreach ($val as $vals) {
            $res[$i]['day'] = explode('-', $key)[2];;
            $res[$i]['count'] = $vals['count'];
            $res[$i]['itemtype'] = $vals['itemtype'];
            $res[$i]['status'] = $vals['status'];
            $i++;
        }
    }
    return $res;
}

function getComplianceDataBasedOnDate($site, $userId, $itemType, $fromDate, $date, $url, $machine)
{

    $servertime = strtotime($fromDate);
    $nowservertime = strtotime($date);
    if ($machine != '') {
        $term = "," . $machine;
    }

    $query = '{
        "query" : {
           "constant_score" : { 
              "filter" : {
                 "bool" : {
                   "should" : [
                      { "terms" : {"status" : ["1", "2", "3"]}}
                   ],
                    "must":[  
                        {  
                           "bool":{  
                                "minimum_should_match":1,
                                "should":[' . $site . ']
                            }
                        },
                        {"term": {"userid": "' . $userId . '"}},
                        {"term": {"itemtype": "' . $itemType . '"}}
                            ' . $term . '
                    ]
                }
              }
           }
        }
     }';
    $result = curlCommonFunction($url, $query);
    $response = format_output($result);
    return $response;
}

function getComplianceDataBasedOnDate_new($site, $userId, $itemType, $fromDate, $date, $url, $machine)
{

    $servertime = strtotime($fromDate);
    $nowservertime = strtotime($date);
    $res = array();


    if ($machine != '') {
        $term = "," . $machine;
    }
    for ($i = 1, $j = 0; $i <= 3; $i++, $j++) {
        $status = $i;
        $query = '{
            "query" : {
               "constant_score" : { 
                  "filter" : {
                     "bool" : {
                       "must":[  
                            {  
                               "bool":{  
                                    "minimum_should_match":1,
                                    "should":[' . $site . ']
                                }
                            },
                            {"term": {"userid": "' . $userId . '"}},
                            {"term": {"itemtype": "' . $itemType . '"}}
                                ' . $term . ',
                              {"term": {"status": "' . $status . '"}},
                               {"term": {"serverdate": "' . $fromDate . '"}}    
                        ]
                    }
                  }
               }
            }
         }';
        $result = curlCommonFunction($url, $query);
        $data = safe_json_decode($result, TRUE);
        $total = $data['hits']['total'];
        $res[$j]['count'] = $total;
        $res[$j]['status'] = $status;
        $res[$j]['itemtype'] = $itemType;
    }
    return $res;
}

function resetCompliance($id)
{

    global $elastic_url;

    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $Startdate = date('Y-m-d', $time2);
    $Enddate = date('Y-m-d', $time1);

    $CompIndex = createComplianceIndex($Startdate, $Enddate);
    $url = $elastic_url . $CompIndex . "/_delete_by_query?pretty";

    $params = '{
                "query": {
                    "bool" : {
                       "must" : [
                            { "match" : { "csid" : "' . $id . '" } }
                       ]
                    }
                }
            }';
    $result = curlCommonFunction($url, $params);
    return true;
}
