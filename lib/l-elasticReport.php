<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-elastic.php';
include_once '../lib/l-elasticReport.php';
include_once '../lib/l-assetnew.php';

function ELRPT_GetAllViewReports($db)
{
    $customerType = $_SESSION['user']['customerType'];
    if ($customerType == 0) {
        $sql = "select id, name, status, created, username from " . $GLOBALS['PREFIX'] . "report.ManagedReport where status =1";
    } else {
        $user_id = $_SESSION['user']['adminid'];
        $sibling_users = fetch_all_users_siblings($user_id, $db);
        $sql = "select id, name, `status`, created, username from " . $GLOBALS['PREFIX'] . "report.ManagedReport where (( userid = $user_id or (userid in (" . implode(",", $sibling_users) . ") and global = 1 )) or envglobal = 1) and status = 1";
    }
    $res = find_many($sql, $db);
    return $res;
}

function fetch_all_users_siblings($owner, $db)
{
    $sql = "SELECT ch_id FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = $owner";
    $result = find_one($sql, $db);
    $sql = "SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE ch_id =" . $result['ch_id'];
    $result = find_many($sql, $db);
    foreach ($result as $value) {
        $return[] = $value['userid'];
    }
    return $return;
}

function ELRPT_GetSectionData($db, $repId)
{
    $sql = "SELECT name,global,include,username,created,infportal,emaillist,userid,type FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id ='$repId' ";
    $report = find_one($sql, $db);

    $reportData['name'] = $report['name'];
    $reportData['global'] = $report['global'];
    $reportData['include'] = $report['include'];
    $reportData['username'] = $report['username'];
    $reportData['created'] = $report['created'];
    $reportData['infPortal'] = $report['infportal'];
    $reportData['emailList'] = $report['emaillist'];
    $reportData['userid'] = $report['userid'];
    $reportData['reportType'] = $report['type'];

    $sqlSec = "SELECT sectionid, chartType, chartEnabled, gridEnabled FROM " . $GLOBALS['PREFIX'] . "report.ManagedReportMap WHERE reportid = $repId";
    $section = find_many($sqlSec, $db);

    foreach ($section as $value) {
        $secSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection WHERE id =" . $value['sectionid'];
        $res = find_one($secSql, $db);

        $sectionData = [];
        if (empty($res)) {
            $sectionData['sectionName'] = '';
            $sectionData['subHeaders'] = '';
            $sectionData['chartType'] = '';
            $sectionData['secType'] = '';
        } else {
            $sectionData['sectionName'] = $res['name'];
            $sectionData['subHeaders'] = $res['subheaders'];
            $sectionData['gridEnalbed'] = $value['gridEnabled'];
            $sectionData['chartEnalbed'] = $value['chartEnabled'];
            $sectionData['chartType'] = $value['chartType'];
            $sectionData['secType'] = $res['sectiontype'];
            $sectionData['secId'] = $res['id'];
            $eid = $_SESSION['user']['cId'];
        }
        $sqlSubSec = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE sectionid =" . $value['sectionid'];
        $subSec = find_many($sqlSubSec, $db);
        foreach ($subSec as $value1) {
            $sectionData['subSectionData'][] = array($value1['name'], $value1['filtertype'], $value1['filterid'], $value1['groupVal'], $value1['reportduration'], $value1['updatetype'], $value1['updatesize'], $value1['mnth'], $value1['year'], $value1['ostype']);
        }
        $reportData['sectionData'][] = $sectionData;
    }

    return $reportData;
}

function ELRPT_GetEventSectionDetails($db, $sectionId)
{
    $temptime1 = "";
    $temptime2 = "";
    global $API_enable_Event;

    $sql = "SELECT M.filterid, S.eventtag, S.name, M.mnth, M.reportduration,M.text FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection M LEFT JOIN event.SavedSearches S ON M.filterid=S.id WHERE M.sectionid='$sectionId'";
    $tagRes = find_many($sql, $db);



    foreach ($tagRes as $tag) {
        $latest = FALSE;
        $mnth = $tag['mnth'];
        $reportduration = $tag['reportduration'];
        if ($reportduration == 7) {
            $temptime1 = time();
            $temptime2 = $temptime1 - 7 * 24 * 60 * 60;
            $temptime1 = $temptime1 . '000';
            $temptime2 = $temptime2 . '000';
        } else if ($mnth !== "0" && $reportduration == 0) {
            $time = explode("--", $mnth);
            $temptime1 = $time[1] . '000';
            $temptime2 = $time[0] . '000';
        } else if ($mnth == "0" && $reportduration == 0) {
            $latest = TRUE;
        }
        $src = $tag['text'] == '' ? 'text1' : $tag['text'];
        $source = '["' . $src . '",';
    }
    $source .= '"machine", "customer", "description", "servertime", "Tags", "entered", "enteredDate", "scrip"]';

    $eventTag = ELRPT_GetEventTagString($tagRes);
    $customerString = ELRPT_GetCustomersString($db);
    $eventsitesdata = [];

    if ($latest) {
        $searchType = $_SESSION['searchType'];
        $searchValue = $_SESSION['searchValue'];
        $filterString = "";

        if ($searchType == "Sites" && $searchValue == "All") {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
            foreach ($dataScope as $row) {
                $filterString .= '{"match": {"customer": "' . $row . '"}},';
            }
            $filterString = rtrim($filterString, ',');
        } else if ($searchType == "Sites") {
            $filterString .= '{"match": {"customer": "' . $searchValue . '"}}';
        } else if ($searchType == "ServiceTag") {
            $filterString .= '{"match": {"machine": "' . $searchValue . '"}}';
        } else {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            foreach ($machines as $val) {
                $filterString .= '{"match": {"machine": "' . $val . '"}},';
            }
            $filterString = rtrim($filterString, ',');
        }
        if ($filterString != '') {

            $params = '{
                    "query": {
                              "bool": {
                                "must": [
                                  {
                                    "bool": {
                                      "minimum_should_match": 1,
                                      "should": [' . $filterString . ']
                                    }
                                  },
                                  {
                                    "bool": {
                                      "minimum_should_match": 1,
                                      "should": [' . $eventTag[0] . ']
                                    }
                                  }
                                ]
                              }
                            },
                    "aggs": {
                        "top_tags": {
                            "terms": {
                                "field": "machine.keyword",
                                "size": 9999
                            },
                            "aggs": {
                                "top_machine_hits": {
                                    "top_hits": {
                                        "sort": [
                                            {
                                                "servertime": {
                                                    "order": "desc"
                                                }
                                            }
                                        ],
                                        "_source": {
                                            "includes": ' . $source . '
                                        },
                                        "size" : 1
                                    }
                                }
                            }
                        }
                    }
                }';

            if ($API_enable_Event == 1) {
                if (($temptime1 == '' || $temptime1 == '000') || ($temptime2 == '' || $temptime2 == '000')) {
                    $from = date(strtotime("-15 days"));
                    $now = time();

                    $temptime1 = date('Y-m-d', $from);
                    $temptime2 = date('Y-m-d', $now);
                }
                $indexName = createEventIndex($temptime1, $temptime2);
                $tempRes = EL_GetCurl($indexName, $params);
            } else {
                $tempRes = EL_GetCurl("event", $params);
            }
            $curlArray = safe_json_decode($tempRes, TRUE);
            $tempArray = $curlArray['aggregations']['top_tags']['buckets'];
            foreach ($tempArray as $key => $value) {
                $innerArray = $value['top_machine_hits']['hits']['hits'];
                foreach ($innerArray as $key1 => $value1) {
                    $eventsitesdata[$key] = $value1['_source'];
                }
            }
        }
    } else {
        if (($temptime1 == '' || $temptime1 == '000') || ($temptime2 == '' || $temptime2 == '000')) {
            $from = date(strtotime("-15 days"));
            $now = time();
        }
        if ($API_enable_Event == 1) {
            $temptime2 = date('Y-m-d', $from);
            $temptime1 = date('Y-m-d', $now);

            $indexName = createEventIndex($temptime1, $temptime2);
            $temptime2 = $from;
            $temptime1 = $now;
        } else {
            if ($from == '' || $now == '') {
            } else {
                $temptime2 = $from;
                $temptime1 = $now;
            }
        }
        if ($customerString != '') {
            $params = '{
                    "_source": ' . $source . ',
                    "query": {
                      "bool": {
                        "must": [
                          {
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [' . $customerString . ']
                            }
                          },
                          {
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [' . $eventTag[0] . ']
                            }
                          },
                          {
                            "range": {
                              "entered": {
                                "gte": ' . $temptime2 . ',
                                "lte": ' . $temptime1 . '
                              }
                            }
                          }
                        ]
                      }
                    },
                   "aggregations": {
                  "id1_count": {

                              "terms": { "field": "machine", "size": 9999},
                                    "aggregations": {
                                              "top_sales_hits": {
                                                  "top_hits": {
                    "sort": [
                                                          {
                                                              "_id": {
                                                                  "order": "desc"
                                                              }
                                                          }
                                                      ],
                                                      "_source": {
                                                          "includes": ' . $source . '
                                                      },
                                                      "size" : 1
                                                  }

                                                  }
                                            }
                                }
                        }
                      }
                  }';

            $params1 = '{
                    "_source": ' . $source . ',
                    "query": {
                      "bool": {
                        "must": [
                          {
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [' . $customerString . ']
                            }
                          },
                          {
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [' . $eventTag[0] . ']
                            }
                          },
                          {
                            "range": {
                              "entered": {
                                "gte": ' . $temptime2 . ',
                                "lte": ' . $temptime1 . '
                              }
                            }
                          }
                    ]
                      }
                    },
                   "aggs": {
                  "id1_count": {

                              "terms": { "field": "machine.keyword", "size": 10000},
                                    "aggs": {
                                              "top_sales_hits": {
                                                  "top_hits": {
                    "sort": [
                                                          {
                                                              "_id": {
                                                                  "order": "desc"
                    }
                                                          }
                                                      ],
                                                      "_source": {
                                                          "includes": ' . $source . '
                                                      },
                                                      "size" : 1
                                                  }

                                                  }
                                            }
                                }
                        }
                      }
                  }';

            if ($API_enable_Event == 1) {
                $temptime1 = date('Y-m-d', $from);
                $temptime2 = date('Y-m-d', $now);
                $indexName = createEventIndex($temptime1, $temptime2);

                $tempRes = EL_GetCurl($indexName, $params);
                $eventsitesdata = EL_FormatCurldata_aggr($tempRes);
            } else {
                $tempRes = EL_GetCurl("event", $params1);
                $eventsitesdata = EL_FormatCurldata_aggr($tempRes);
            }
        }
    }
    return array('tags' => $eventTag[1], "data" => $eventsitesdata, "tagArray" => $eventTag[2], "count" => safe_count($eventsitesdata));
}

function ELRPT_GetSummaryEventSectionDetails($db, $filterId, $subSectionId, $mnth)
{
    $sql = 'select S.eventtag, S.name from ' . $GLOBALS['PREFIX'] . 'event.SavedSearches S WHERE S.id=' . $filterId;
    $tagRes = find_many($sql, $db);

    $source = '["machine", "customer", "description", "text1", "servertime", "Tags", "entered", "enteredDate", "scrip"]';

    $eventTag = ELRPT_GetEventTagString($tagRes);
    $customerString = ELRPT_GetCustomersString($db);


    if ($mnth == 'latest') {
        $searchType = $_SESSION['searchType'];
        $searchValue = $_SESSION['searchValue'];
        $filterString = "";

        if ($searchType == "Sites" && $searchValue == "All") {
        } else if ($searchType == "Sites") {
            $filterString .= '{"match": {"customer": "' . $searchValue . '"}}';
        } else if ($searchType == "ServiceTag") {
            $filterString .= '{"match": {"machine": "' . $searchValue . '"}}';
        }
        $params = '{
                    "query": {
                              "bool": {
                                "must": [
                                  {
                                    "bool": {
                                      "minimum_should_match": 1,
                                      "should": [' . $filterString . ']
                                    }
                                  },
                                  {
                                    "bool": {
                                      "minimum_should_match": 1,
                                      "should": [' . $eventTag[0] . ']
                                    }
                                  }
                                ]
                              }
                            },
                    "aggs": {
                        "top_tags": {
                            "terms": {
                                "field": "machine.keyword",
                                "size": 100
                            },
                            "aggs": {
                                "top_machine_hits": {
                                    "top_hits": {
                                        "sort": [
                                            {
                                                "servertime": {
                                                    "order": "desc"
                                                }
                                            }
                                        ],
                                        "_source": {
                                            "includes": [ "machine", "customer", "description", "text1", "servertime", "Tags", "entered", "enteredDate", "scrip"]
                                        },
                                        "size" : 1
                                    }
                                }
                            }
                        }
                    }
                }';
        $tempRes = EL_GetCurl("event", $params);
        $curlArray = safe_json_decode($tempRes, TRUE);
        $tempArray = $curlArray['aggregations']['top_tags']['buckets'];
        $eventsitesdata = [];
        foreach ($tempArray as $key => $value) {
            $innerArray = $value['top_machine_hits']['hits'];
            foreach ($innerArray as $key1 => $value1) {
                array_push($eventsitesdata, $value1['_source']);
            }
        }
    } else {
        $temptime1 = time();
        $temptime2 = $temptime1 - $mnth * 24 * 60 * 60;
        $temptime1 = $temptime1 . '000';
        $temptime2 = $temptime2 . '000';



        $params = '{
                        "_source": ' . $source . ',
                        "query": {
                          "bool": {
                            "must": [
                              {
                                "bool": {
                                  "minimum_should_match": 1,
                                  "should": [' . $customerString . ']
                                }
                              },
                              {
                                "bool": {
                                  "minimum_should_match": 1,
                                  "should": [' . $eventTag[0] . ']
                                }
                              }
                            ]
                          }
                        },
                        "sort": [
                            { "enteredDate":   { "order": "asc" }}
                        ]
                      }';
        $tempRes = EL_GetCurl("event", $params);
        $eventsitesdata = EL_FormatCurldata($tempRes);
    }

    return array('tags' => $eventTag[1], "data" => $eventsitesdata, "tagArray" => $eventTag[2]);
}

function ELRPT_GetEventTagString($sectionTagData)
{

    $tagfilter = "";
    $tagString = "";
    $tags = [];
    foreach ($sectionTagData as $tag) {
        if ($tag['eventtag'] != "" || $tag['eventtag'] != NULL) {
            $tagString .= $tag['eventtag'] . ",";
            $tagfilter .= '{"match": {"Tags": "' . $tag['eventtag'] . '"}},';
            $tags[$tag['eventtag']] .= $tag['name'];
        }
    }
    return array(rtrim($tagfilter, ','), rtrim($tagString, ','), $tags);
}

function ELRPT_GetCustomersString($db)
{
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $filterString = "";
    global $API_enable_Event;

    if ($searchType == "Sites" && $searchValue == "All") {
        $user = $_SESSION['user']['username'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
        foreach ($dataScope as $row) {
            $filterString .= '{"match": {"customer": "' . $row . '"}},';
        }
    } else if ($searchType == "Sites") {
        $filterString .= '{"match": {"customer": "' . $searchValue . '"}}';
    } else if ($searchType == "ServiceTag") {
        $filterString .= '{"match": {"machine": "' . $searchValue . '"}}';
    } else {
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
        if ($API_enable_Event == 1) {
            foreach ($machines as $val) {
                $filterString .= '{"term": {"machine": "' . $val . '"}},';
            }
        } else {
            foreach ($machines as $val) {
                $filterString .= '{"term": {"machine.keyword": "' . $val . '"}},';
            }
        }

        $filterString = rtrim($filterString, ',');
    }

    return rtrim($filterString, ",");
}

function ELRPT_GetAssetSectionDetails($db, $sectionid, $reptid)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $reportData = getReportData($reptid, $sectionid, $db, 2);
    $userid = $reportData['userid'];
    $jsonData = report_cron($reptid, $reportData, $userid, $db, 2);
    return $jsonData;
}

function ELRPT_GetSavedSearch($db, $filterId)
{
    $sql = "SELECT eventtag, dartnum FROM " . $GLOBALS['PREFIX'] . "event.SavedSearches WHERE id='' LIMIT 1";
    $res = find_one($sql, $db);

    if ($res['eventtag'] == "") {
        return $res['eventtag'];
    } else {
        return $res['dartnum'];
    }
}

function ELRPT_GetNotifData($db, $sectionData)
{
    $sectionId = UTIL_GetInteger('sectionId', 0);
    $statusArray = array(0 => "Pending", 1 => "Fixed");

    $sectionSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE sectionid= '$sectionId' LIMIT 1";
    $sectionRes = find_one($sectionSql, $db);


    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $userid = $_SESSION["user"]["userid"];
    $machGrpList = fetch_mach_grps($searchValue, $userid, $db);
    $mnth = $sectionRes['mnth'];
    $graph = [];

    if ($machGrpList != '') {
        $machines = fetch_machines_list($machGrpList, $db);
    }



    $censusIds = '"' . implode('","', $machines['censusid']) . '"';
    $nids = $sectionRes['filterid'];
    $groupcate = explode("###", $sectionRes['groupVal']);
    $group = $groupcate[0];
    $category = $groupcate[1];

    if ($mnth == "7" || $mnth == "30" || $mnth == "60") {
        $startDate = strtotime("-" . $mnth . " day");
        $endDate = time();
    } else {
        $interval = explode("--", $sectionRes['mnth']);
        $startDate = $interval[0];
        $endDate = $interval[1];
    }

    $notifSql = 'SELECT * FROM  ' . $GLOBALS['PREFIX'] . 'event.tempGraphSummary WHERE machine IN (' . $machines['host'][0] . ') AND servertime BETWEEN ' . $startDate . ' AND ' . $endDate . ' ';
    $notifRes = find_many($notifSql, $db);
    $res2 = [];
    $i = 0;
    foreach ($notifRes as $key => $val) {
        $prio = $val['priority'];
        $type = 'Minor';

        if ($prio == 1 || $prio == "1") {
            $type = 'Critical';
        } else if ($prio == 2 || $prio == "2" || $prio == 3 || $prio == "3") {
            $type = 'Major';
        }

        $res2[$i]['machine'] = $val['machine'];
        $res2[$i]['sitename'] = UTIL_GetTrimmedGroupName($val['sitename']);
        $res2[$i]['nocname'] = $val['nocname'];
        $res2[$i]['date'] = $val['nocname'];
        $res2[$i]['status'] = $statusArray[$val['actionStatus']];
        $res2[$i]['type'] = $type;
        $i++;
    }
    if ($category == "host") {
        if ($group == "site") {
            $group = "sitename";
        }
        $graph = ELRPT_FormatGraphData($res2, $group);
    }

    $return['details'] = $res2;
    $return['graph'] = $graph;
    return $return;
}

function ELRPT_GetMUMData($db, $sectionData)
{
    $sectionId = UTIL_GetInteger('sectionId', 0);
    global $elastic_url;

    $sectionSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE sectionid= '$sectionId' LIMIT 1";
    $sectionRes = find_one($sectionSql, $db);

    $url = $elastic_url . "patchesstatussummary/_search?pretty";
    $typeArray = array(1 => "Update", 2 => "Service Pack", 3 => "Roll Up", 4 => "Security", 5 => "Critical");
    $statusArray = array(8 => "Installed", 10 => "Downloaded", 11 => "Detected", 15 => "Superseded", 16 => "Waiting");
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $userid = $_SESSION["user"]["userid"];
    $machGrpList = fetch_mach_grps($searchValue, $userid, $db);
    $mnth = $sectionRes['mnth'];
    $graph = [];

    if ($machGrpList != '') {
        $machines = fetch_machines_list($machGrpList, $db);
    }

    $censusIds = '"' . implode('","', $machines['censusid']) . '"';
    $patchIds = $sectionRes['filterid'];
    $groupcate = explode("###", $sectionRes['groupVal']);
    $group = $groupcate[0];
    $category = $groupcate[1];

    if ($mnth == "7" || $mnth == "30" || $mnth == "60") {
        $startDate = strtotime("-" . $mnth . " day") . '000';
        $endDate = time() . '000';
    } else {
        $interval = explode("--", $sectionRes['mnth']);
        $startDate = $interval[0] . '000';
        $endDate = $interval[1] . '000';
    }

    $params = '{
                "_source": [ "censusid", "detected", "status", "patchid", "type", "site", "platform", "patchname"],
                "query": {
                "bool": {
                  "must" : [
                    { "terms": { "censusid" : [' . $censusIds . '] } }
                  ],
                  "must" : [
                    { "terms": { "patchid" : [' . $patchIds . '] } }
                  ]
                },
                {
                    "range": {
                      "detected": {
                        "gte": ' . $startDate . ',
                        "lte": ' . $endDate . '
                      }
                    }
                  }
               }
              }
        }';
    $tempRes = ELPROV_GET_Curl($url, "");
    $res = EL_FormatCurldata($tempRes);
    $i = 0;
    $res2 = [];
    foreach ($res as $key => $val) {
        $res2[$i]['host'] = $machines['machineNames'][$val['censusid']];
        $res2[$i]['detected'] = date("Y-m-d", $val['detected']);
        $res2[$i]['patchid'] = $val['patchid'];
        $res2[$i]['status'] = $statusArray[$val['status']];
        $res2[$i]['type'] = $typeArray[$val['type']];
        $res2[$i]['site'] = UTIL_GetTrimmedGroupName($val['site']);
        $res2[$i]['patchname'] = $val['patchname'];
        $res2[$i]['platform'] = $val['platform'];
        $i++;
    }
    if ($category == "host") {
        $graph = ELRPT_FormatGraphData($res2, $group);
    }

    $return['type'] = 'mum';
    $return['details'] = $res2;
    $return['graph'] = $graph;
    return $return;
}

function ELRPT_FormatGraphData($rows, $column)
{
    $tempGoupedData = [];
    foreach ($rows as $key => $value) {
        $columnVal = $value[$column];

        if (array_key_exists($columnVal, $tempGoupedData)) {
            $tempGoupedData[$columnVal]++;
        } else {
            $tempGoupedData[$columnVal] = 1;
        }
    }
    return $tempGoupedData;
}

function getMumData()
{
    $reptid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';
    $sectionid = url::issetInRequest('sectionid') ? url::requestToAny('sectionid') : '';

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $reportData = getReportData($reptid, $sectionid, $db, 3);
    $userid = $reportData['userid'];
    $jsonData = report_cron($reptid, $reportData, $userid, $db, 3);

    echo json_encode($jsonData);
}

function getAssetData()
{
    $reptid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';
    $sectionid = url::issetInRequest('sectionid') ? url::requestToAny('sectionid') : '';

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $reportData = getReportData($reptid, $sectionid, $db, 2);
    $userid = $reportData['userid'];
    $jsonData = report_cron($reptid, $reportData, $userid, $db, 2);
    echo json_encode($jsonData);
}

function getReportData($id, $sectionid, $db, $sectionType)
{

    $sql = "SELECT name,global,include,username,created,infportal,emaillist,userid,type FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id =$id ";
    $report = find_one($sql, $db);
    $reportData['name'] = $report['name'];
    $reportData['global'] = $report['global'];
    $reportData['include'] = $report['include'];
    $reportData['username'] = $report['username'];
    $reportData['created'] = $report['created'];
    $reportData['infPortal'] = $report['infportal'];
    $reportData['emailList'] = $report['emaillist'];
    $reportData['userid'] = $report['userid'];
    $reportData['reportType'] = $report['type'];
    $sqlShed = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.Schedule WHERE reportid = $id";
    $schedData = find_one($sqlShed, $db);
    $reportData['schedData'] = array($schedData['schedtype'], $schedData['mnthday'], $schedData['weekday'], $schedData['hour'], $schedData['min']);
    $sqlSec = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedReportMap mr, " . $GLOBALS['PREFIX'] . "report.ManagedSection ms WHERE reportid = $id and mr.sectionid = ms.id and ms.sectiontype = $sectionType AND sectionid = $sectionid";
    $section = find_many($sqlSec, $db);

    foreach ($section as $value) {
        $secSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection WHERE id =" . $value['sectionid'];
        $res = find_one($secSql, $db);

        $sectionData = [];
        if (empty($res)) {
            $sectionData['sectionName'] = '';
            $sectionData['subHeaders'] = '';
            $sectionData['chartType'] = '';
            $sectionData['secType'] = '';
        } else {
            $sectionData['sectionName'] = $res['name'];
            $sectionData['subHeaders'] = $res['subheaders'];
            $sectionData['chartType'] = $res['charttype'];
            $sectionData['secType'] = $res['sectiontype'];
            $sectionData['secId'] = $res['id'];
            $eid = $_SESSION['user']['cId'];
        }
        $sqlSubSec = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE sectionid =" . $value['sectionid'];
        $subSec = find_many($sqlSubSec, $db);
        foreach ($subSec as $value1) {
            $sectionData['subSectionData'][] = array($value1['name'], $value1['filtertype'], $value1['filterid'], $value1['groupVal'], $value1['reportduration'], $value1['updatetype'], $value1['updatesize'], $value1['mnth'], $value1['year'], $value1['ostype']);
        }
        $reportData['sectionData'][] = $sectionData;
    }

    return $reportData;
}

function get_size_inbytes($updateSize)
{
    switch ($updateSize) {
        case '2':
            $sizeComp = " size > 0 and size <= 51200 ";
            break;
        case '3':
            $sizeComp = " size > 51200 and size <= 1048576 ";
            break;
        case '4':
            $sizeComp = " size > 1048576 and size <= 5242880 ";
            break;
        case '5':
            $sizeComp = " size > 5242880 ";
            break;
        default:
            $sizeComp = '';
            break;
    }
    return $sizeComp;
}

function get_other_crit($subSecData)
{
    $updateType = $subSecData[5];
    $updateSize = $subSecData[6];
    $mnth = $subSecData[7];
    $year = $subSecData[8];
    $osType = $subSecData[9];
    $where = '';
    if ($mnth == 0) {
        $where .= " date BETWEEN '" . strtotime("1 January $year") . "' AND '" . strtotime("31 December $year") . "'";
    } else {
        $to_date = date("Y-m-t", strtotime("1 $mnth $year"));
        $where .= " date BETWEEN '" . strtotime("1 $mnth $year") . "' AND '" . strtotime($to_date) . "'";
    }

    $where .= " and type in ($updateType) ";

    if ($updateSize != 0 && $updateSize != 1) {
        $sizeInBytes = get_size_inbytes($updateSize);
        $where .= " and $sizeInBytes";
    }

    return $where;
}

function prepare_sql_mum($subSecData)
{
    $patchId = $subSecData[2];
    $where .= get_other_crit($subSecData);
    $where .= " and patchid in ($patchId)";
    $sql = "select patchid,name,type from " . $GLOBALS['PREFIX'] . "softinst.Patches where $where";
    return $sql;
}

function fetch_mum_data($subSecData, $machines, $db)
{
    $summaryName = $subSecData[0];
    $summaryHdrs = $subSecData[3];
    $res2 = [];
    $statusArray = array(8 => "Installed", 10 => "Downloaded", 11 => "Detected", 15 => "Superseded", 16 => "Waiting");
    $typeArray = array(1 => "Update", 2 => "Service Pack", 3 => "Roll Up", 4 => "Security", 5 => "Critical");
    db_change($GLOBALS['PREFIX'] . 'softinst', $db);
    $sql = prepare_sql_mum($subSecData);
    $patchesDetails = find_many($sql, $db);

    foreach ($patchesDetails as $key => $val1) {
        $patchid .= $val1['patchid'] . ",";
        $patchName[$val1['patchid']] = $val1['name'];
        $patchType[$val1['patchid']] = $typeArray[$val1['type']];
        $allPatches[] = $val1['patchid'];
    }

    $tempres = get_MUMData($machines, $allPatches, $summaryHdrs, $typeArray);
    $i = 0;
    $statusCount = [];
    $statusCountGraph = [];

    foreach ($tempres as $key => $val) {
        $res2[$i]['host'] = $machines['machineNames'][$val['censusid']];
        $res2[$i]['detected'] = date("Y-m-d", $val['detected']);
        $res2[$i]['patchid'] = $val['patchid'];
        $res2[$i]['status'] = $statusArray[$val['status']];


        if (array_key_exists($val['status'], $statusCount)) {
            $statusCount[$val['status']]['name'] = $statusArray[$val['status']];
            $statusCount[$val['status']]['count']++;
        } else {
            $statusCount[$val['status']]['name'] = $statusArray[$val['status']];
            $statusCount[$val['status']]['count'] = 1;
        }
        $i++;
    }

    foreach ($statusCount as $key => $value) {
        $statusCountGraph[] = $value;
    }

    $return['type'] = 'mum';
    $return['details'] = $res2;
    $return['groupedData'] = $statusCountGraph;
    $return['patchname'] = $patchName;
    $return['patchtype'] = $patchType;
    $return['name'] = $summaryName;
    return $return;
}

function getAssetGraphData($jsonData)
{
    $groupedData = $jsonData[0]['groupedData'];
    $tempArray = [];
    foreach ($groupedData as $key => $value) {
        $tempArray[$value['name']] = intval($value['count']);
    }
    return $tempArray;
}

function get_MUMData($machines, $patchesDetails, $statusString, $typeArray)
{

    $statusArray = explode(',', $statusString);
    global $elastic_url;
    $url = $elastic_url . "patchesstatussummary/_search?pretty";

    $censusIds = '"' . implode('","', $machines['censusid']) . '"';
    $status = '"' . implode('","', $statusArray) . '"';
    $patchIds = '"' . implode('","', $patchesDetails) . '"';

    $params = '{
                "_source": [ "censusid", "detected", "status", "patchid", "type"],
                "query": {
                "bool": {
                  "must" : [
                    { "terms": { "censusid" : [' . $censusIds . '] } }
                  ],
                  "must" : [
                    { "terms": { "status" : [' . $status . '] } }
                  ],
                  "must" : [
                    { "terms": { "patchid" : [' . $patchIds . '] } }
                  ]
                }
               }
              }
        }';

    $tempRes = ELPROV_GET_Curl($url, $params);
    $res = EL_FormatCurldata($tempRes);
    return $res;
}

function process_sections($secName, $subSections, $groupby, $chartType, $machines, $db, $reportType)
{
    global $API_enable;
    if ($reportType == 2) {
        if ($API_enable == 0) {
            $result = fetch_asset_data($subSections[0], TRUE, $machines);
        } else {
            $result = fetchAssetDataNew($subSections[0], TRUE, $machines);
        }
    } else if ($reportType == 3) {
        $result[0] = fetch_mum_data($subSections[0], $machines, $db);
    }

    return $result;
}

function prepare_sections($reportData, $machines, $machGrpList, $db, $reportType)
{
    $i = 0;
    $data = [];
    end($reportData['sectionData']);
    $lastIndex = key($reportData['sectionData']);
    reset($reportData['sectionData']);

    foreach ($reportData['sectionData'] as $key => $value) {
        $resData = process_sections($value['sectionName'], $value['subSectionData'], $value['subHeaders'], $value['chartType'], $machines, $db, $reportType);
    }

    return $resData;
}

function fetch_mach_grps($machGrpsNms, $userid, $db)
{

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $parentValue = $_SESSION['rparentName'];
    $user = $_SESSION['user']['logged_username'];

    if (stripos($machGrpsNms, 'All') !== FALSE) {
        $sql = "select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = $userid";
        $result = find_many($sql, $db);
        $names = [];
        foreach ($result as $val) {
            $names[] = "'" . $val['customer'] . "'";
        }
    } else {
        $names = explode(",", $machGrpsNms);
        foreach ($names as $value) {
            $list .= "'" . $value . "',";
        }
        $sql = "select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = $userid and C.customer in (" . rtrim($list, ",") . ")";
        $result = find_many($sql, $db);
        $names = [];
        foreach ($result as $val) {
            $names[] = "'" . $val['customer'] . "'";
        }
    }

    $machGrpsUniqs = '';

    if ($searchType == 'ServiceTag') {
        $siteNameSql = "select cust from " . $GLOBALS['PREFIX'] . "asset.Machine where host='$searchValue' order by machineid desc limit 1";
        $siteRes = find_one($siteNameSql, $db);
        $stagGroupVal = $siteRes['cust'] . ':' . $searchValue;

        $sql = "select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ('$stagGroupVal')";
    } else if ($searchType == 'Groups') {

        $sql = "select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid ='$searchValue' ";
    } else {
        $sql = "select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in (" . implode(",", $names) . ")";
    }
    $res = find_many($sql, $db);

    foreach ($res as $key => $val) {
        $machGrpsUniqs .= "'" . $val['mgroupuniq'] . "',";
    }

    return rtrim($machGrpsUniqs, ",");
}

function fetch_machines_list($machGrps, $db)
{
    $return['mid'] = [];
    $return['host'] = [];
    $return['censusid'] = [];
    $return['machineNames'] = [];
    $host = '';
    $sql = "select distinct C.host,C.id,C.site from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M "
        . "join " . $GLOBALS['PREFIX'] . "core.Census as C on C.censusuniq = M.censusuniq "
        . "where M.mgroupuniq in  ($machGrps)";

    $res = find_many($sql, $db);

    foreach ($res as $key => $val) {
        $host .= "'" . $val['host'] . "',";
        $return['censusid'][] = $val['id'];
        $return[$val['host']] = $val['site'];
        $return['machineNames'][$val['id']] = $val['host'];
        $sitename .= "'" . $val['site'] . "',";
    }

    $sql = "select machineid,host from " . $GLOBALS['PREFIX'] . "asset.Machine where host in (" . rtrim($host, ",") . ") and cust in (" . rtrim($sitename, ",") . ")";
    $res = find_many($sql, $db);
    foreach ($res as $key => $val) {
        $return['mid'][] = $val['machineid'];
    }
    $return['host'][0] = rtrim($host, ",");
    return $return;
}

function report_cron($id, $reportData, $userid, $db, $reportType)
{

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $userid = $_SESSION["user"]["userid"];
    $finalRes = array();

    $machGrpList = fetch_mach_grps($searchValue, $userid, $db);

    if ($machGrpList != '') {

        $machines = fetch_machines_list($machGrpList, $db);
        if (safe_count($machines['mid']) > 0) {

            $finalRes = prepare_sections($reportData, $machines, $machGrpList, $db, $reportType);
        }
    }
    return $finalRes;
}

function fetch_asset_data($subSecData, $single, $machines)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    $filterId = $subSecData[2];

    $res = get_DisplayFields($db, $filterId);
    $fields = $res['displayfields'] . ":Machine Name:Site Name:";
    $terms = get_SearchTerms($db, $filterId);
    if (safe_count($terms) > 1) {
        $return = getMultipleBlockAssets($db, $subSecData, $terms, $fields, $machines);
    } else {
        $return = getSingleBlockAssets($db, $subSecData, $terms[1], $fields, $machines);
    }
    return $return;
}

function fetch_asset_data_new($subSecData, $single, $machines)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    $filterId = $subSecData[2];

    $res = get_DisplayFields($db, $filterId);
    $fields = $res['displayfields'] . ":Machine Name:Site Name:";
    $terms = get_SearchTerms($db, $filterId);

    $compareDataid = $terms[0]['dataid'];
    $filterName = $subSectionData['subSectionData'][0];

    foreach ($machines['mid'] as $val) {
        $machineids .= '"' . $val . '",';
    }
    $machineids = rtrim($machineids, ',');
    $machineid = ' ,"filter": {
        "terms": {
          "machineid": [' . $machineids . ']
        }
    }';
    $return = getAssetDataByQry($filterId, $machineid, 0, 100, $db);

    $res = safe_json_decode($return, TRUE);
    if (safe_count($terms) > 1) {
        $displayFields = asset_display_criteria($db, $fields, $terms, $machines['mid']);
        $return = getAssetResponseArray_new1($displayFields, $res, $fields, $filterName, $terms, $compareDataid);
    } else {
        $displayFields = asset_display_criteria($db, $fields, $terms[1], $machines['mid']);
        $return = getAssetResponseArray_new1($displayFields, $res, $fields, $filterName, $terms[1], $compareDataid);
    }

    return $return;
}

function fetchAssetDataNew($subSecData, $single, $machines)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    $from = 0;
    $size = 10000;
    $export = 0;
    $filterId = $subSecData[2];
    $indexname = '';

    $res = get_DisplayFields($db, $filterId);
    $fields = $res['displayfields'] . ":Machine Name:Site Name:";
    $searchString = $res['searchstring'];
    $terms = get_SearchTerms($db, $filterId);

    $compareDataid = $terms[0]['dataid'];
    $filterName = $subSectionData['subSectionData'][0];

    if ($searchString != '') {
        $time = time();
        $indexname = 'asset_' . $filterId . '_' . $time;
        $tempArray = array();
        $machineCount = safe_count($machines['mid']);
        if ($machineCount > 100) {
            $k = floor($machineCount / 100) + 1;
            for ($i = 0; $i < $k; $i++) {
                $machine = array();
                $start = $i * 100;
                $end = 100;
                $machine = array_slice($machines['mid'], $start, $end);
                $machineid = '';
                foreach ($machine as $val) {
                    $machineid .= '"' . $val . '",';
                }
                $machineids = rtrim($machineid, ',');
                $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineids . ']}}';

                $return = __getAssetELQry($filterId, $machineid, $indexname, $from, $size, $export, $db);
            }
            $res = $tempArray;
        } else {
            $machineid = '';
            foreach ($machines['mid'] as $key => $val) {
                $machineid .= '"' . $val . '",';
            }
            $machineids = rtrim($machineid, ',');
            $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineids . ']}}';
            $return = __getAssetELQry($filterId, $machineid, $indexname, $from, $size, $export, $db);

            $res1 = safe_json_decode($return, TRUE);
            array_push($tempArray, $res1);
            $res = $tempArray;
        }
        updateWindowSize($indexname);
    } else {
        $indexname = 'assetdata';
    }

    if (!empty($machines['mid'])) {
        foreach ($machines['mid'] as $val) {
            $machineid .= '"' . $val . '",';
        }
        $machineid = rtrim($machineid, ',');
        if ($searchType == 'Sites' && $searchValue != 'All') {
            $machineid = ' ,"filter": {"term": {"sitename.keyword": "' . $searchValue . '"}}';
        } else if ($searchType == 'Sites' && $searchValue == 'All') {
            $sql = "select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = $userid";
            $result = find_many($sql, $db);
            $names = '';
            foreach ($result as $val) {
                $names .= '"' . $val['customer'] . '",';
            }
            $names = rtrim($names, ',');
            $machineid = ' ,"filter": {"terms": {"sitename.keyword": [' . $names . ']}}';
        } else if ($searchType == 'ServiceTag') {
            $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineid . ']}}';
        } else {
            $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineid . ']}}';
        }
        if ($indexname == 'assetdata') {
            $indexname = 'assetdata';
            $result = __getAssetELQry($filterId, $machineid, $indexname, $start, $end, $export, $db);
        } else {
            $result = __getFilerIndexData($indexname, $from, $size);
        }
        if (safe_count($terms) > 1) {
            $displayFields = asset_display_criteria($db, $fields, $terms, $machines['mid']);
            $return = getAssetResponseArray_new1($displayFields, $result['result'], $fields, $filterName, $terms, $compareDataid);
        } else {
            $displayFields = asset_display_criteria($db, $fields, $terms[1], $machines['mid']);
            $return = getAssetResponseArray_new1($displayFields, $result['result'], $fields, $filterName, $terms[1], $compareDataid);
        }
        return $return;
    } else {
    }
}

function getAssetResponseArray_new1($displayFields, $res, $fields, $filterName, $terms, $compareDataid)
{
    $search = $displayFields['criteria'];
    unset($displayFields['criteria']);
    $return[0]['details'] = $displayFields;
    $return[0]['details']['rows'] = $res;
    $return[0]['details']['total'] = safe_count($res);
    $return[0]['details']['pages'] = 0;
    $return[0]['details']['block'] = 1;
    $return[0]['details']['search'] = $search;
    $return[0]['details']['fields'] = $fields;
    $return[0]['details']['dataId'] = $compareDataid;
    $return[0]['details']['showGraph'] = safe_count($terms);
    $return[0]['groupedData']['count'] = safe_count($res);
    $return[0]['groupedData']['name'] = $filterName;
    $return[0]['type'] = 'asset';
    $return['graph'] = $res;
    return $return;
}

function getMultipleBlockAssets($db, $subSecData, $terms, $fields, $machines)
{
    global $elastic_url;
    $url = $elastic_url . "assetdatalatest/_search?pretty&size=10000";

    $filterName = $subSecData[0];

    foreach ($terms as $key => $term) {
        $displayFields = asset_display_criteria($db, $fields, $term, $machines['mid']);
        $columns = get_AssetDisplayFields($displayFields['columns']);
        $params = getParameterValue($term, $columns, $machines);
        $tempRes = ELPROV_GET_Curl($url, $params);
        $tempRes2 = EL_FormatCurldata($tempRes);
        if (safe_count($tempRes2) > 0) {
            $compareTerms = $term;
            $compareDataid = $term[0]['dataid'];
            break;
        }
    }
    $res = formatAssetResult($tempRes2, $compareTerms, $displayFields['columns'], $compareDataid);
    $return = getAssetResponseArray($displayFields, $res, $fields, $filterName, $terms, $compareDataid);
    return $return;
}

function getSingleBlockAssets($db, $subSecData, $terms, $fields, $machines)
{
    global $elastic_url;
    $url = $elastic_url . "assetdatalatest_1/_search?pretty&size=10000";

    $filterName = $subSecData[0];
    $compareDataid = $terms[0]['dataid'];
    $displayFields = asset_display_criteria($db, $fields, $terms, $machines['mid']);

    if ($compareDataid != "") {
        $displayFieldsColumn = isCompareDataIdExist($displayFields['columns'], $compareDataid);
    } else {
        $compareDataid = $displayFields['columns'][$filterName];
        $displayFieldsColumn = $displayFields['columns'];
    }

    $columns = get_AssetDisplayFields($displayFields['columns']);
    $params = getParameterValue($terms, $columns, $machines);
    $tempRes = ELPROV_GET_Curl($url, $params);

    $tempRes2 = EL_FormatCurldata($tempRes);
    $res = formatAssetResultTest($tempRes2, $terms, $displayFields['columns'], $compareDataid);
    $return = getAssetResponseArray($displayFields, $res, $fields, $filterName, $terms, $compareDataid);
    return $return;
}

function getAssetResponseArray($displayFields, $res, $fields, $filterName, $terms, $compareDataid)
{
    $search = $displayFields['criteria'];
    unset($displayFields['criteria']);
    $return[0]['details'] = $displayFields;
    $return[0]['details']['rows'] = $res[0];
    $return[0]['details']['total'] = safe_count($res[0]);
    $return[0]['details']['pages'] = 0;
    $return[0]['details']['block'] = 1;
    $return[0]['details']['search'] = $search;
    $return[0]['details']['fields'] = $fields;
    $return[0]['details']['dataId'] = $compareDataid;
    $return[0]['details']['showGraph'] = safe_count($terms);
    $return[0]['groupedData']['count'] = safe_count($res[0]);
    $return[0]['groupedData']['name'] = $filterName;
    $return[0]['type'] = 'asset';
    $return['graph'] = $res[1];
    return $return;
}

function formatAssetResult($assets, $terms, $displayFieldsColumn, $compareDataid)
{
    $tempArray = [];
    $array = [];
    $groupedData = [];
    $tempGoupedData = [];

    foreach ($assets as $key => $value) {
        $machineId = $value['machineid'];
        unset($value['machineid']);
        foreach ($value as $dataid => $dataValue) {

            $dataidArray = explode('_', key($dataValue));
            $tempArray[$dataid] = array($dataidArray[1] => $dataValue[key($dataValue)]);

            if ($dataid == $compareDataid) {
                $graphLable = $dataValue[key($dataValue)];
                if (array_key_exists($graphLable, $tempGoupedData)) {
                    $tempGoupedData[$graphLable]++;
                } else {
                    $tempGoupedData[$graphLable] = 1;
                }
            }
        }
        $array[$machineId] = $tempArray;
    }
    return array($array, $tempGoupedData);
}



function formatAssetResultNew($assets, $terms, $displayFieldsColumn, $compareDataid)
{
    $tempArray = [];
    $array = [];
    $groupedData = [];
    $tempGoupedData = [];
    foreach ($assets as $key => $value) {
        $machineId = $value['machineid'];
        unset($value['machineid']);
        foreach ($value as $dataid => $dataValue) {
            foreach ($dataValue as $key => $value) {
                $ordVal = explode('_', $key)[1];
                $ordinal[$ordVal] = $value;
            }
            ksort($ordinal);
            $tempArray[$dataid] = $ordinal;

            if ($dataid == $compareDataid) {
                $graphLable = $dataValue[key($dataValue)];
                if (array_key_exists($graphLable, $tempGoupedData)) {
                    $tempGoupedData[$graphLable]++;
                } else {
                    $tempGoupedData[$graphLable] = 1;
                }
            }
            $ordinal = '';
        }
        $array[$machineId] = $tempArray;
    }
    return array($array, $tempGoupedData);
}

function ordinalLoopFunction($OrdArray, $maxOrdinal, $dataidList, $newDNA)
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
        foreach ($value as $nkey => $nval) {
            $data = ($nval != '') ? $nval : '-';
            $finalArr[$dataidList[$nkey]][$key] = $data;
        }
        $returnArr[] = $finalArr;
    }
    return $returnArr;
}

function formatAssetResultTest($assets, $terms, $displayFieldsColumn, $compareDataid)
{
    $tempArray = [];
    $array = [];
    $groupedData = [];
    $tempGoupedData = [];
    $newDNA = [];
    foreach ($displayFieldsColumn as $key => $value) {
        if ($key == 'Machine Name' || $key == 'Site Name' || $key == 'Host') {
            $newDNA[] = $value;
        }
    }

    foreach ($assets as $key => $value) {
        $machineId = $value['machineid'];
        unset($value['machineid']);
        $maxOrd = array();
        foreach ($value as $dataid => $dataValue) {
            $fOrdn = '';
            foreach ($dataValue as $key => $value) {
                $ordVal = explode('_', $key)[1];
                $ordinal[$ordVal] = $value;
                $fOrdn[] = $ordVal;
                $fordVal = max($fOrdn);
            }
            $maxOrd[] = $fordVal;
            ksort($ordinal);
            $tempArray[$dataid] = $ordinal;
            $dataidList[] = $dataid;
            $ordinal = '';
            if ($dataid == $compareDataid) {
                $graphLable = $dataValue[key($dataValue)];
                if (array_key_exists($graphLable, $tempGoupedData)) {
                    $tempGoupedData[$graphLable]++;
                } else {
                    $tempGoupedData[$graphLable] = 1;
                }
            }
        }
        $dataidListNew = [];
        foreach (array_unique($dataidList) as $key => $value) {
            $dataidListNew[] = $value;
        }
        $arrDataRes = ordinalLoopFunction($tempArray, $maxOrd, $dataidListNew, $newDNA);
        foreach ($arrDataRes as $key => $value) {
            $machineIdNew = $machineId . '_' . ($key + 1);
            $array[$machineIdNew] = $value;
        }
    }
    return array($array, $tempGoupedData);
}

function getParameterValue($terms, $columns, $machines)
{
    $tempString = "";
    $machineIds = '"' . implode('","', $machines['mid']) . '"';
    if (safe_count($terms) > 0) {
        foreach ($terms as $key => $value) {
            $tempString .= '{
                        "multi_match" : {
                          "query":      "' . $value['value'] . '",
                          "fields":     [ "' . $value['dataid'] . '.' . $value['dataid'] . '_*"],
                          "minimum_should_match": "25%"
                        }
                      },';
        }
        $tempString = rtrim($tempString, ',');
        $params = '{
               "_source": ["_id", ' . $columns . '],
               "query": {
                 "bool": {
                   "must": [
                     ' . $tempString . '
                   ],
                 "filter": {
                    "terms": { "_id": [' . $machineIds . '] }
                  }
                 }
               }
             }';
    } else {
        $params = '{
               "_source": ["_id", ' . $columns . '],
               "query": {
                 "bool": {
                 "filter": {
                    "terms": { "_id": [' . $machineIds . '] }
                  }
                 }
               }
             }';
    }

    return $params;
}

function getParameterValueAstHist($columns, $machineid)
{

    $params = '{
           "_source": ["machineid", ' . $columns . '],
           "query": {
             "bool": {
             "filter": {
                "terms": { "machineid": [' . $machineid . '] }
              }
             }
           }
         }';

    return $params;
}

function get_AssetDisplayFields($displayArray)
{
    foreach ($displayArray as $key => $value) {
        $columnsArray[] = $value;
    }
    $columns = '"' . implode('","', $columnsArray) . '"';
    return $columns;
}

function get_DisplayFields($db, $filterId)
{
    $sql = "SELECT displayfields,searchstring FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches WHERE id = $filterId LIMIT 1";
    $res = find_one($sql, $db);
    return $res;
}

function get_SearchTerms($db, $qid)
{
    $terms = array();
    $sql = "SELECT d.dataid, c.comparison, c.value, c.block, d.groups, d.ordinal FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearchCriteria AS c INNER JOIN " . $GLOBALS['PREFIX'] . "asset.DataName AS d ON d.name = c.fieldname WHERE c.assetsearchid = $qid";
    $res = find_many($sql, $db);
    foreach ($res as $key => $value) {
        $terms[$value['block']][] = $value;
    }

    return $terms;
}

function isCompareDataIdExist($displayFieldsColumns, $dataid)
{
    $tempArray = array_values($displayFieldsColumns);

    if (in_array($dataid, $tempArray)) {
    } else {
        $db = db_connect();
        $query = "SELECT dataid, name FROM " . $GLOBALS['PREFIX'] . "asset.DataName WHERE dataid = '$dataid' LIMIT 1";
        $res = find_one($query, $db);
        $displayFieldsColumns[$res['name']] = $res['dataid'];
    }
    return $displayFieldsColumns;
}

function ELRPT_GetAssetSummarySectionDetails($db, $sectionId, $filterId)
{
    $res = get_DisplayFields($db, $filterId);
    $fields = $res['displayfields'] . ":Machine Name:Site Name:";
    $terms = get_SearchTerms($db, $filterId);

    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $userid = $_SESSION["user"]["userid"];


    $machGrpList = fetch_mach_grps($searchValue, $userid, $db);

    if ($machGrpList != '') {

        $machines = fetch_machines_list($machGrpList, $db);
    }

    if (safe_count($terms) > 1) {
        $return = getMultipleBlockAssets($db, array(), $terms, $fields, $machines);
    } else {
        $return = getSingleBlockAssets($db, array(), $terms[1], $fields, $machines);
    }

    return $return[0]['details']['rows'];
}



function getCurlRecordsCount($elastic_index_url, $query)
{

    $url = $elastic_index_url . "/_count";

    $result = commonCurlResult($url, $query);

    return $result;
}

function commonCurlResult($url, $query)
{
    try {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
                'Content-Type: application/json',
                'Content-Length: ' . strlen($query)
            )
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);

        if ($curl_errno) {
            __logElasticError($curl_errno, $result);
            return array();
        }

        curl_close($ch);
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        return $exc;
    }
    return $result;
}

function __logElasticError($errorNo, $string)
{
    logs::log($errorNo, $string);
    return TRUE;
}

function __getObjectExcel($objPHPExcel, $sheetIndex)
{

    $sheetNo = $sheetIndex + 1;
    $objPHPExcel->createSheet($sheetIndex);
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Sl.no');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Scrip');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Customer');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Machine');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Description');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Details');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Server Time');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Client Time');
    $objPHPExcel->getActiveSheet()->setTitle("Event Report Details - $sheetNo");
    return $objPHPExcel;
}

function __getObjectExcelnew()
{

    $header = array("Scrip", "Customer", "Machine", "Description", "Text1", "Text2", "Text3", "Text4", "Client Time", "Server Time", "Client Version");
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=Dynamic_Event_Report.csv;');
    header('Content-Transfer-Encoding: binary');

    $fpo = fopen('php://output', 'w');

    $cvsHeadings = $header;
    fputcsv($fpo, $cvsHeadings);
}

function writeEventExcelfile($elastic_url, $totalCount, $query)
{

    $index = 2;
    $j = 0;
    $sheetIndex = 0;
    $activeSheet2 = FALSE;
    __getObjectExcelnew();
    if ($totalCount > 2000) {

        $k = floor($totalCount / 2000) + 1;
        for ($i = 0; $i < $k; $i++) {

            $start = $i * 2000;
            $length = 2000;

            $params = '{"from" : ' . $start . ', "size" : ' . $length . ',' . $query . '}';

            if ($start >= 1048000) {
                if ($activeSheet2 == FALSE) {

                    __getObjectExcelnew();
                    $activeSheet2 = TRUE;
                }

                $inx = $j * $length + 2;
                __loopexportdatanew($elastic_url, $params);
                $j++;
            } else {
                $inx = $start + 2;
                __loopexportdatanew($elastic_url, $params);
            }
        }
    } else if ($totalCount <= 2000) {

        $start = 0;
        $length = $totalCount;

        $params = '{"from" : ' . $start . ', "size" : ' . $length . ',' . $query . '}';

        $inx = $start + 2;
        __loopexportdatanew($elastic_url, $params);
    }
}

function __loopexportdata($elastic_url, $query, $objPHPExcel, $index)
{
    $tempRes = __getCurlWithLimit($elastic_url, $query);
    $resultData = __formatJsondata($tempRes);
    $totalCount = $resultData['total'];
    $gridlist = $resultData['result'];

    if ($totalCount > 0) {
        foreach ($gridlist as $key => $value) {

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . ($index - 1) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $value['scrip'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . UTIL_GetTrimmedGroupName($value['customer']) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $value['machine'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . safe_addslashes(utf8_encode($value['description'])) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, '' . safe_addslashes(utf8_encode($value['text1'])) . '');
            $servertime = $value['servertime'];
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, PHPExcel_Shared_Date::PHPToExcel($servertime));
            $objPHPExcel->getActiveSheet()->getStyle('G' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");
            $clienttime = $value['entered'];
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, PHPExcel_Shared_Date::PHPToExcel($clienttime));
            $objPHPExcel->getActiveSheet()->getStyle('H' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    return $objPHPExcel;
}

function __loopexportdatanew($elastic_url, $query)
{
    $fp = fopen('php://output', 'a');
    $tempRes = __getCurlWithLimit($elastic_url, $query);
    $resultData = __formatJsondata($tempRes);
    $totalCount = $resultData['total'];
    $gridlist = $resultData['result'];
    $tempArray = array();

    if ($totalCount > 0) {
        foreach ($gridlist as $key => $value) {
            $tempArray = array();
            $scrip =  $value['scrip'];
            $customer = UTIL_GetTrimmedGroupName($value['customer']);
            $machine = $value['machine'];
            $description = safe_addslashes(utf8_encode($value['description']));
            $text1 = safe_addslashes(utf8_encode($value['text1']));
            $text2 = safe_addslashes(utf8_encode($value['text2']));
            $text3 = safe_addslashes(utf8_encode($value['text3']));
            $text4 = safe_addslashes(utf8_encode($value['text4']));
            $clienttime = date("m/d/Y H:i:s", $value['entered']);
            $servertime = date("m/d/Y H:i:s", strtotime($value['serverDate']));
            $clientversion = $value['clientversion'];

            $tempArray = array($scrip, $customer, $machine, $description, $text1, $text2, $text3, $text4, $clienttime, $servertime, $clientversion);

            fputcsv($fp, $tempArray);
        }
    } else {
        $tempArray = array('No Data Available');
        fputcsv($fp, $tempArray);
    }

    return $objPHPExcel;
}

function __getCurlWithLimit($elastic_url, $query)
{

    $url = $elastic_url . "_search?pretty";
    $result = commonCurlResult($url, $query);
    return $result;
}

function __isValidJson($string)
{

    $result = safe_json_decode($string);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = TRUE;
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error) {
        return TRUE;
    } else {
        logElasticError("FALSE", $error);
        return FALSE;
    }
}

function __formatJsondata($jsonResponse)
{

    $isValidJson = __isValidJson($jsonResponse);
    $result = [];
    $total = 0;
    if ($isValidJson) {
        $curlArray = safe_json_decode($jsonResponse, TRUE);
        if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
            $total = $curlArray['hits']['total'];
            $loopsArray = $curlArray['hits']['hits'];
            foreach ($loopsArray as $key => $value) {
                $result[$key] = $value['_source'];
            }
        }
    }
    return array("result" => $result, "total" => $total);
}

function __generateExcelFile($objPHPExcel)
{

    $file_name = "Dynamic_Event_Report.xlsx";
    $objPHPExcel->setActiveSheetIndex(0);

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=\"$file_name\"");
    header("Cache-Control: max-age=0");

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");

    ob_clean();
    $objWriter->save("php://output");
}
