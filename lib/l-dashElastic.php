<?php



function ELPROV_GetSitesEvents($db, $time1, $time2, $sites) {
    global $elastic_url;
    $url = $elastic_url."/event/_search?pretty&size=10000";
     
    $params = '{
                "query": {
                  "bool": { 
                    "must": [
                      { "match": { "customer":"'.$sites.'"}}
                    ],
                    "filter": [ 
                      { "range": { "servertime": { "gte": "'.$time2.'", "lte" : "'.$time1.'" }}} 
                    ]
                      }
                        }
              }';

    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}

function ELPROV_GetMachineEvents($db, $time1, $time2, $machine, $sitename) {
    global $elastic_url;
    $url = $elastic_url."/event/_search?pretty&size=1000";
    $params = '{
                "query": {
                  "bool": { 
                    "must": [
                      { "match": { "machine":"'.$machine.'"}},
                      { "match": { "customer": "'.$sitename.'" }}
                    ],
                    "filter": [ 
                      { "range": { "servertime": { "gte": "'.$time2.'", "lte" : "'.$time1.'" }}} 
                    ]
                      }
                        }
              }';
    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}

function ELPROV_GetAllSitesEvents($db, $time1, $time2, $machine, $sitename) {
    global $elastic_url;
    $url = $elastic_url."/event/_search?pretty&size=1000";
    $params = '{
                "query": {
                  "filtered": {
                    "query": {
                      "query_string": {
                        "query": "(machine:' . $machine . ') AND (customer:' . $sitename . ')"
                      }
                    },
                    "filter": {
                      "range": {
                        "servertime": {
                          "gt": "'.$time2.'",
                          "lte": "'.$time1.'"
                        }
                      }
                    }
                  }
                }
              }';
    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}

function ELPROV_GetFilterSitesEvents($db, $time1, $time2, $sites, $dartNo, $savedSearch) {
    global $elastic_url;
    
    $url = $elastic_url."/event/_search?pretty&size=10000";
    if (is_array($sites)) {
        foreach ($sites as $site) {
            $siteList .= $site . ',';
        }
        $lableDisply = rtrim($siteList, ',');
    } else {

        $lableDisply = $sites;
    }
    
    $params = ELPROV_GetFilterString($time1, $time2, $dartNo, $savedSearch, $lableDisply, "Sites");
   
    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}

function ELPROV_GetFilterMachineEvents($db, $time1, $time2, $machines, $sitename, $dartNo, $savedSearch) {
    global $elastic_url;
    $url = $elastic_url."/event/_search?pretty&size=1000";
    if (is_array($machines)) {
        foreach ($machines as $site) {
            $machineList .= "'" . $machine . "',";
        }
        $lableDisply = rtrim($machineList, ',');
    } else {

        $lableDisply = $machines;
    }
    $params = ELPROV_GetFilterString($time1, $time2, $dartNo, $savedSearch, $lableDisply, "ServiceTag");
    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}

function ELPROV_GetFilterString($time1, $time2, $dartNo, $savedSearch, $lableDisply, $searchType) {
   
    if ($searchType == "Sites") {
        $subParam1 = '{ "match": { "customer":"' . $lableDisply . '"}},';
    } else if ($searchType == "ServiceTag") {
        $subParam1 = '{ "match": { "machine":"' . $lableDisply . '"}},';
    }

    if ($dartNo !== "" && $savedSearch !== "undefined") {
        $subParam2 = '{ "match": { "scrip":"' . $dartNo . '"}},{ "match": { "Tags":"*' . $savedSearch . '*"}}';
    }else if ($savedSearch !== "undefined") {
        $subParam2 = '{ "match": { "Tags":"*' . $savedSearch . '*"}}';
    } else if ($dartNo !== "") {
        $subParam2 = '{ "match": { "scrip":"' . $dartNo . '"}}';
    }else{
        $subParam1 = rtrim($subParam1, ',');
    }
    
    $params = '{
                "query": {
                  "bool": { 
                    "must": [
                      ' . $subParam1 . '' . $subParam2 . '
                    ],
                    "filter": [ 
                      { "range": { "servertime": { "gte": "' . $time1 . '", "lte" : "' . $time2 . '" }}} 
                    ]
                      }
                        }
              }';
    return $params;
}

function EL_DASH_GetSummaryRprt($db, $machineArray, $itemId, $reportdurtn){
    global $elastic_url;
    $url = $elastic_url."/compliancesummary";
    
    $summaryDetails = [];
    $itemIds = [];
    $censusIds = '';
    
    if (is_array($machineArray)) {
        foreach ($machineArray as $key => $value) {
            $censusIds .= $key.' ';
        }
    }else{
        $censusIds = $machineArray;
    }
    
    foreach ($itemId as $value) {
        $itemIds[] = $value['eventitemid'];
    }
    $itemIds = implode(" ", $itemIds);
    
    echo $params = '{
                "query": {
                  "bool": {
                    "must": [
                      { "match": { "censusid": "'.$censusIds.'"}},
                      { "match": { "itemid": "'.$itemIds.'"}}
                    ],
                    "filter": [ 
                      { "range": { "servertime": { "gte": "' . $reportdurtn[0] . '", "lte" : "' . $reportdurtn[1] . '" }}} 
                    ]
                  }
                }
              }';
    
    $tempRes = EL_GetCurl("compliancesummary", $params);
    $res = ELPROV_FORMAT_Curldata($tempRes);
    return $res;
}


?>