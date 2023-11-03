<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
function getDataFromElastic($source, $cond)
{

    $condition = '';

    foreach ($cond as $key => $value) {
        $condition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
    }
    $where = rtrim($condition, ',');
    $result = getCurlData($source, $where);
    return $result;
}

function getCurlData($source, $con)
{

    global $elastic_url;
    $url = $elastic_url . "scoredeventdtl/_search?pretty&size=1";

    $params = '{
        "_source": [' . $source . '],
        "query": {
            "bool": {
                "must" : [
                    ' . $con . '
                ]
            }
        },
        "sort": { "servertime": { "order": "desc" }}
    }';
    $curlResp = COMM_getDataUsingCurl($params, $url);
    $result = FORMAT_CurlData($curlResp);
    return $result;
}

function FORMAT_CurlData($result)
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

function COMM_getDataUsingCurl($params, $url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $resData = curl_exec($curl);
    curl_close($curl);

    return $resData;
}

function getScorecardAssetResponse($machineid, $dataid)
{
    global $elastic_url;
    $url = $elastic_url . "assetdatalatest_1/_search?pretty&size=10000";

    $params = '{
                "_source": ["_id", ' . $dataid . '],
                "query": {
                    "bool": {
                        "filter": {
                            "terms": { "_id": ["' . $machineid . '"] }
                        }
                    }
                }
            }';
    $curlResp = COMM_getDataUsingCurl($params, $url);
    $result = FORMAT_CurlData($curlResp);

    return $result[0][$dataid];
}

function getScorecardEventResponse($scrip, $cust, $host, $scoretext, $repdate)
{
    $source = '"cust", "host", "text1", "svalue", "scoretext", "servertime", "scrip"';
    $cond = array('scrip' => $scrip, 'cust' => $cust, 'host' => $host, 'scoretext' => $scoretext, 'serverdate' => $repdate);
    $dataRes = getDataFromElastic($source, $cond);
    return $dataRes[0];
}

function get_machineAssetDetails($cust, $host)
{
    global $pdo;
    global $elastic_url;
    $url = $elastic_url . "assetdatalatest_1/_search?pretty&size=10000";

    $sql = $pdo->prepare("select GROUP_CONCAT('\"', dataid, '\"') AS assetdatalist from " . $GLOBALS['PREFIX'] . "asset.DataName where name in ('Operating System','MAC address','IP address')");
    $sql->execute();
    $res = $sql->fetch();
    $datalist = $res['assetdatalist'];

    $sqlMachine = $pdo->prepare("select machineid from " . $GLOBALS['PREFIX'] . "asset.Machine M where M.host = ? and M.cust = ? order by M.slatest limit 1;");
    $sqlMachine->execute([$host, $cust]);
    $resMachine = $sqlMachine->fetch();
    $machineid = $resMachine['machineid'];

    $params = '{
                "_source": ["_id", ' . $datalist . '],
                "query": {
                    "bool": {
                        "filter": {
                            "terms": { "_id": ["' . $machineid . '"] }
                        }
                    }
                }
            }';
    $curlResp = COMM_getDataUsingCurl($params, $url);
    $result = FORMAT_CurlData($curlResp);

    $dataid = str_replace('"', '', explode(',', $datalist));
    foreach ($dataid as $value) {
        $ord = $value . '_1';
        $assetData[] = $result[0][$value][$ord];
    }
    return $assetData;
}

function EL_deleteCurrentIndexRecords($serverdate)
{
    global $elastic_url;
    $url = $elastic_url . "scorecarddetails/_delete_by_query?pretty";

    $params = '{
                "query": {
                    "bool" : {
                       "must" : [
                            { "match" : { "serverdate" : "' . $serverdate . '" } }
                       ]
                    }
                }
            }';
    pushBulkData($params, $url);
}

function EL_updateScoreDetails($compId, $procId, $cust, $host, $scoreId, $scoreVal)
{
    global $pdo;
    global $elastic_url;
    $url = $elastic_url . "scorecarddetails/post/_bulk";

    $serverdate = date('Y-m-d', time());
    $servertime = strtotime($serverdate);
    $result = get_machineAssetDetails($cust, $host);
    $macaddress = $result[1];
    $ipaddress = $result[0];
    $machineos = $result[2];

    $maxSql = $pdo->prepare("select scorevariableId, scoremax from " . $GLOBALS['PREFIX'] . "scorecard.scorecardsummary where id =?");
    $maxSql->execute([$scoreId]);
    $maxRes = $maxSql->fetch();
    $maxscore = $maxRes['scoremax'];

    $uniq_id_val = $scoreId . '_' . $cust . '_' . $host . '_' . $serverdate;
    $uniq_id = md5($uniq_id_val);

    $id = array("index" => array("_id" => $uniq_id));
    $insData = array(
        'companyid' => $compId, 'processid' => $procId, 'scoreid' => $scoreId, 'sitename' => $cust, 'machine' => $host,
        'serverdate' => $serverdate, 'servertime' => $servertime, 'scorevalue' => 1, 'score' => $scoreVal, 'maxscore' => $maxscore,
        'macaddress' => $macaddress, 'machineOs' => $machineos, 'ipaddress' => $ipaddress
    );

    $fdata = str_replace(array('[', ']'), '', json_encode($id)) . PHP_EOL . str_replace(array('[', ']'), '', json_encode($insData)) . PHP_EOL;

    pushBulkData($fdata, $url);
}

function pushBulkData($pdata, $url)
{

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($pdata)
        )
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $pdata);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    $res = curl_exec($ch);
    echo '<pre>';
    if ($res) {
        echo 'Result : ' . $res;
    }
    curl_close($ch);

    return TRUE;
}



function EL_SCORE_GetTrendGraphData($searchType, $searchValue, $eid)
{
    global $pdo;
    global $elastic_url;
    $url = $elastic_url . "scorecarddetails/_search?pretty&size=10000";

    $mCount = '';
    $servertime = '';
    $scoreavg = 0;
    $scoreArr = array();
    $mscoreArr = array();
    $mcountArr = array();
    $machineArr = array();
    $dateArr = array();
    $data = array();
    $paramScoreArr = array();
    $paramMaxScoreArr = array();
    $paramScoreDetails = array();
    $weekDateArr = array();

    if ($searchType == 'Sites') {
        $searchCondition = '{ "match" : { "sitename" : "' . $searchValue . '" } }';
    } else if ($searchType == 'ServiceTag') {
        $searchCondition = '{ "term" : { "machine.keyword" : "' . $searchValue . '" } }';
    } else if ($searchType == 'Groups') {
        $key = '';
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $machines = DASH_GetGroupsMachines($key, $db, $dataScope);

        $searchCondition = '{ "match" : { "machine" : "' . $machines . '" } }';
    }
    $params = '{
                "_source": [],
                "query": {
                    "bool" : {
                       "must" : [' . $searchCondition . ']
                   }
                },
                "sort" : [{ "serverdate" : "desc" }]
            }';
    $curlResp = COMM_getDataUsingCurl($params, $url);
    $result = FORMAT_CurlData($curlResp);

    foreach ($result as $key => $value) {

        if ($value['score'] > 0) {
            $scoreArr[$value['serverdate']][$value['scoreid']][] = $value['score'];
            $mscoreArr[$value['serverdate']][$value['scoreid']][] = $value['maxscore'];
        }
        $mcountArr[$value['serverdate']][] = $value['machine'];
        $machineArr[] = $value['machine'];
        $dateArr[] = $value['serverdate'];
        $siteArr[] = $value['sitename'];
    }
    $dayscount = count(array_unique($dateArr));

    foreach ($scoreArr as $key => $valueNew) {
        $mCount = count(array_unique($mcountArr[$key]));
        foreach ($valueNew as $key1 => $valueNew1) {
            $paramScore = ceil(array_sum($valueNew1) / $mCount);
            $paramMaxScore = ceil(array_sum($mscoreArr[$key][$key1]) / safe_count($mscoreArr[$key][$key1]));
            $paramScoreArr[$key][] = $paramScore;
            $paramMaxScoreArr[$key][] = $paramMaxScore;
            $paramScoreDetails[$key][] = $key1;
        }
    }

    $pdo = pdo_connect();
    foreach ($paramScoreArr as $key => $valueF) {
        $servertime = strtotime($key);

        $scoreavg = array_sum($valueF);
        $maxscore = array_sum($paramMaxScoreArr[$key]);

        $sitename = array_unique($siteArr)[0];

        $dataids = implode(',', $paramScoreDetails[$key]);

        $noScoreSql = $pdo->prepare("select sc.id, sum(sc.scoremax) pscoremax from " . $GLOBALS['PREFIX'] . "scorecard.scorecardsummary sc, " . $GLOBALS['PREFIX'] . "scorecard.scorevariables sv "
            . "where siteConfigured = ? and sc.scorevariableId = sv.id and sc.id not in (" . $dataids . ");");
        $noScoreSql->execute([$sitename]);
        $noScoreRes = $noScoreSql->fetch();

        $maxscoreAll = $maxscore + $noScoreRes['pscoremax'];

        $data[] = array(
            'servertime' => $servertime, 'scoreavg' => $scoreavg, 'maxscore' => $maxscoreAll,
            'dayscount' => $dayscount, 'serverdate' => $key, 'sitename' => $sitename
        );
    }

    return $data;
}



function EL_getScoreReportDetails($searchType, $searchValue, $serverdate)
{
    global $elastic_url;
    $url = $elastic_url . "scorecarddetails/_search?pretty&size=10000";
    $pdo = pdo_connect();

    $userid = $_SESSION['user']['userid'];
    $searchCondition = '';
    if ($searchType == 'Sites') {
        $searchCondition = '{ "match" : { "sitename" : "' . $searchValue . '" } },
                            { "match" : { "serverdate" : "' . $serverdate . '" } }';
        $sitename = explode('__', $searchValue)[0];
        $siteConfName = $searchValue;
    } else if ($searchType == 'ServiceTag') {
        $searchCondition = '{ "term" : { "machine.keyword" : "' . $searchValue . '" } },
                            { "match" : { "serverdate" : "' . $serverdate . '" } }';
        $sitename = explode('__', $_SESSION['rparentName'])[0];
        $siteConfName = $_SESSION['rparentName'];
    }

    $params = '{
                "_source": [],
                "query": {
                    "bool" : {
                       "must" : [' . $searchCondition . ']
                    }
                }
            }';
    $curlResp = COMM_getDataUsingCurl($params, $url);
    $result = FORMAT_CurlData($curlResp);

    if (safe_count($result) > 0) {
        $scoreidArr = array();
        foreach ($result as $value) {
            $scoreidArr[$value['scoreid']][] = $value;
        }
        $noScore = "";
        foreach ($scoreidArr as $key1 => $scoreData) {
            $noScore .= $key1 . ',';
            $paramSql = $pdo->prepare("select sc.id, sv.scorevariableName from " . $GLOBALS['PREFIX'] . "scorecard.scorevariables sv, " . $GLOBALS['PREFIX'] . "scorecard.scorecardsummary sc where sc.scorevariableId = sv.id and sc.id = ?;");
            $paramSql->execute([$key1]);
            $paramRes = $paramSql->fetch();
            $paramName = $paramRes['scorevariableName'];
            $paramScore = 0;
            $paramTotal = 0;
            $scoreid = $key1;
            foreach ($scoreData as $key2 => $value2) {
                $paramScore += $value2['score'];
                $paramTotal = $value2['maxscore'];
                $machineArr[] = $value2['machine'];
                $severtime = $value2['servertime'];
            }
            $macCount = count(array_unique($machineArr));

            $scoreDetailsData[] = array(
                'scoreid' => $scoreid, 'paramName' => $paramName, 'paramScore' => $paramScore,
                'paramTotal' => $paramTotal, 'cnt' => $macCount, 'sitename' => $sitename, 'servertime' => $severtime
            );
        }
        $noScoreVal = rtrim($noScore, ',');
        $scoreCond = " and sc.id not in ($noScoreVal)";
    } else {
        $scoreCond = '';
        $scoreDetailsData = array();
    }

    $noScoreSql = $pdo->prepare("select sc.id, sv.scorevariableName, sc.scoremax from " . $GLOBALS['PREFIX'] . "scorecard.scorecardsummary sc, " . $GLOBALS['PREFIX'] . "scorecard.scorevariables sv where siteConfigured = ? and sc.scorevariableId = sv.id " . $scoreCond . ";");
    $noScoreSql->execute([$siteConfName]);
    $noScoreRes = $noScoreSql->fetchAll();
    foreach ($noScoreRes as $value) {
        $servertime = '';
        $scoreDetailsData[] = array(
            'scoreid' => $value['id'], 'paramName' => $value['scorevariableName'], 'paramScore' => 0,
            'paramTotal' => $value['scoremax'], 'cnt' => $macCount, 'sitename' => $sitename, 'servertime' => $servertime
        );
    }

    return $scoreDetailsData;
}
