<?php

function EL_GetCurlWithScroll($indexName, $params, $requestHeaders = [], $scroll_id = null)
{
    global $elastic_url;

    if ($scroll_id) {
        $params = '{
            "scroll" : "1m",
            "scroll_id" : "' . $scroll_id . '"
        }';
        $url = $elastic_url . '_search/scroll';
    } else {
        $url = $elastic_url . $indexName.'/_search?scroll=1m&size=10000';
    }
    $result = EL_MakeCurl($url, $params, $requestHeaders);
    return $result;
}

function EL_GetCurl($indexName, $params, $requestHeaders = [])
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_search?size=10000&pretty";

    $result = EL_MakeCurl($url, $params, $requestHeaders);
    if ($errorno) {
        logElasticError($errorno, $result);
        return array();
        exit();
    }
        return $result;
}

function EL_GetCurlWithLimit($indexName, $params, $requestHeaders = [])
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_search?pretty";

    if (isset($requestHeaders) && is_array($requestHeaders) && safe_sizeof($requestHeaders) > 0) {
        $headers = $requestHeaders;
    } else {
        $headers = [];
    }

    $result = EL_MakeCurl($url, $params, $requestHeaders);
    if ($errorno) {
        logElasticError($errorno, $result);
        return array();
        exit();
    }
        return $result;
}

function EL_GetCurlRecordsCount($indexName, $params)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_count";

    $result = EL_MakeCurl($url, $params);
    if ($errorno) {
        logElasticError($errorno, $result);
        return array();
        exit();
    }
    curl_close($ch);
    return $result;
}


function EL_PushData($indexName, $params, $id)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/post/_bulk";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params))
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_exec($ch);
    $result = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    curl_close($ch);
    return TRUE;

}

function EL_MakeCurl($url, $params, $requestHeaders = [])
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    $headers = array();

    if (isset($requestHeaders) && is_array($requestHeaders) && safe_sizeof($requestHeaders) > 0) {
        $headers = $requestHeaders;
    } else {
        $headers = [];
        $headers[] = "Content-Type: application/json";
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);

    $result = curl_exec($ch);
    if ($errorno) {
        logElasticError($errorno, $result);
        return array();
        exit();
    }
    curl_close($ch);
    return $result;
}

function EL_GetRowsCount($indexName)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_count";

    $params = '';
    $count = 0;

    $result = EL_MakeCurl($url, $params);
    $isValidJson = EL_IsValidJson($result);
    if ($isValidJson) {
        $curlArray = safe_json_decode($result, TRUE);
        if (isset($curlArray['count']) && $curlArray['count'] > 0) {
            $count = $curlArray['count'];
        }
    }
    return $count;
}


function EL_ScrollData($indexName, $params, $count)
{
    global $elastic_url;
    $url = $elastic_url . $indexName;

    $result = EL_MakeCurl($url, $params);
    if ($errorno) {
        logElasticError($errorno, $result);
        return array();
        exit();
    }
    curl_close($ch);
    return $result;
}

function EL_GetScrollId($indexUrl, $params)
{
    $url = $indexUrl . "_search?scroll=2m";
    $result = EL_MakeCurl($url, $params);
    $isValidJson = EL_IsValidJson($result);
    if ($isValidJson) {
        $curlArray = safe_json_decode($result, TRUE);
        if (isset($curlArray['_scroll_id'])) {
            $count = $curlArray['_scroll_id'];
        }
    }

    return $result;
}

function EL_FormatCurldata_new($curlResponse)
{
    $isValidJson = EL_IsValidJson($curlResponse);
    $result = [];
    $total = 0;
    if ($isValidJson) {
        $curlArray = safe_json_decode($curlResponse, TRUE);
        if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
            $total = $curlArray['hits']['total'];
            $loopsArray = $curlArray['hits']['hits'];
            foreach ($loopsArray as $key => $value) {
                $result[$key] = $value['_source'];
                $result[$key]['machineid'] = $value['_id'];
            }
        }
    }
    return array("result" => $result, "total" => $total);
}


function EL_FormatCurldata($curlResponse)
{
    $isValidJson = EL_IsValidJson($curlResponse);
    $result = [];
    if ($isValidJson) {
        $curlArray = safe_json_decode($curlResponse, TRUE);
        if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
            $loopsArray = $curlArray['hits']['hits'];
            foreach ($loopsArray as $key => $value) {
                $result[$key] = $value['_source'];
                $result[$key]['machineid'] = $value['_id'];
            }
        }
    }
    return $result;
}


function EL_IncreaseResultWindow($indexName, $size)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/_settings";

    $params = '{
                "index": {
                  "max_result_window": ' . $size . '
                    }
              }';


    $result = EL_MakeCurl($url, $params);
    return safe_json_decode($result, TRUE);
}

function EL_DeleteIndex($indexName)
{
    global $elastic_url;
    $url = $elastic_url . $indexName . "/?pretty";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return TRUE;
}

function EL_DeleteIndexRow($indexName, $id)
{
    if ($id !== "") {
        global $elastic_url;
        $url = $elastic_url . $indexName . "/doc/" . $id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return TRUE;
    }
}

function EL_IsValidJson($string)
{
    $result = safe_json_decode($string);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = TRUE;             break;
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
        case JSON_ERROR_UTF8:                   $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_RECURSION:              $error = 'One or more recursive references in the value to be encoded.';
            break;
        case JSON_ERROR_INF_OR_NAN:             $error = 'One or more NAN or INF values in the value to be encoded.';
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

function logElasticError($errorNo, $string)
{
    $dt = date('Y-m-d H:i:s (T)');
    $fp = fopen('elasticCalls.log', 'a+');
    fwrite($fp, $dt . PHP_EOL);
    fwrite($fp, "ERROR CODE=" . $errorNo . PHP_EOL);
    fwrite($fp, "RESPONSE=" . $string . PHP_EOL . PHP_EOL);
    fclose($fp);
    return TRUE;
}

function EL_FormatCurldata_aggr($res)
{

    $isValidJson = EL_IsValidJson($res);
    $result = [];
    $total = 0;
    if ($isValidJson) {
        $curlArray = safe_json_decode($res, TRUE);
        if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
            $loopsArray = $curlArray['aggregations']['id1_count']['buckets'];
            foreach ($loopsArray as $key => $value) {
                $result[$key] = $value['top_sales_hits']['hits']['hits'][0]['_source'];
                $result[$key]['machineid'] = $value['top_sales_hits']['hits']['hits']['_id'];
            }
        }
    }
    return $result;
}

function updateByIndex($params, $indexName, $requestHeaders = [])
{

    global $elastic_url;
    $url = $elastic_url . $indexName . "/_update_by_query?pretty";

    if (isset($requestHeaders) && is_array($requestHeaders) && safe_sizeof($requestHeaders) > 0) {
        $headers = $requestHeaders;
    } else {
        $headers = [];
        $headers[] = "Content-Type: application/json";
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    $res = curl_exec($curl);
    curl_close($curl);
    return $res;

}

function postELcall($indexName, $query, $requestHeaders = [])
{

    global $elastic_url;
    $url = $elastic_url . $indexName . "/_search?pretty";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

    if (isset($requestHeaders) && is_array($requestHeaders) && safe_sizeof($requestHeaders) > 0) {
        $headers = $requestHeaders;
    } else {
        $headers = [];
        $headers[] = "Content-Type: application/json";
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);

    $result = curl_exec($ch);
    return $result;
}
