<?php

function getAssetDataByQry($qryid, $machineid, $from, $size, $db)
{

    $res = __getAssetELQry($qryid, $machineid, $from, $size, $db);

    $status = $res['status'];
    if ($status == 'Success') {


        $datlist = json_encode($res['msg']);
        $datlist = str_replace("},{", ",", $datlist);
        $jsonData = trim($datlist, '[]');

        return $jsonData;
    } else {
        return json_encode($res);
    }
}

function parseAssetData($assetQry, $displayFieldsColumn, $filterFld, $condType)
{


    $assetData = getAllAssets($assetQry);

    $response = safe_json_decode($assetData, true);

    $result = null;
    $finalArr = [];
    foreach ($response['hits']['hits'] as $key => $val) {
        $maxOrd = array();
        $machineId = $val['_id'];
        $result = $val['_source'];
        $keys = safe_array_keys($result);
        foreach ($keys as $value1) {
            $res = $result[$value1];
            $fordVal = safe_count($res);
            $maxOrd[] = $fordVal;
        }

        $maxOrdVal = max($maxOrd);
        $conCheck = checkFiltercase($filterFld, $result);
        if ($conCheck) {

            for ($index = 1; $index <= $maxOrdVal; $index++) {
                $rowdata = array();
                $rowdata1 = array();
                foreach ($result as $dataid => $value) {

                    if (isset($value[$index])) {
                        $val = $value[$index];
                    } else {
                        if (safe_count($value) == 1) {
                            if (isset($value[1])) {
                                $val = $value[1];
                            } else {
                                $val = '-';
                            }
                        } else {
                            $val = '-';
                        }
                    }

                    $rowdata[$dataid][$index] = $val;
                    $rowdata1[$dataid] = $val;
                }
                $retVal = __filterAssetData($rowdata, $index, $filterFld);
                if ($retVal) {

                    $machineIdNew = $machineId . '_' . $index;
                    $finalArr[$machineIdNew] = $rowdata1;
                }
            }
        }
    }

    return $finalArr;
}


function __filterAssetData($data, $keyVal, $filetrFld)
{

    $ret = false;
    if (safe_count($filetrFld) > 0) {
        $i = 1;
        $lastdataid = 0;
        foreach ($filetrFld as $key => $value) {

            $dataid = $value;
            $condValue = $key;
            $ret = __filterloop($data, $condValue, $dataid, $keyVal, $i, $filetrFld);
            $nextKey = __getNextKey($filetrFld, $i);
            if ($ret == false) {
                if ($nextKey != $dataid) {
                    return $ret;
                }
            } else {
                if ($nextKey == $dataid) {
                    return $ret;
                }
            }

            $i++;
        }
        return $ret;
    } else {
        $ret = true;
        return $ret;
    }
}

function __filterloop($data, $cond, $dataid, $keyVal, $i, $filetrFld)
{

    $matchExpl = explode("##", $cond);
    $condVal = trim($matchExpl[0]);
    $matchVal = trim($matchExpl[1]);

    if ($matchVal == 'exact') {
        if (trim($data[$dataid][$keyVal]) == $condVal) {
            $flag = true;
        } else {
            $flag = false;
        }
    } else {
        if (stripos(trim($data[$dataid][$keyVal]), $condVal) !== false) {
            $flag = true;
        } else {
            $flag = false;
        }
    }
    return $flag;
}

function __getNextKey($value, $i)
{
    $arrySlice = array_slice($value, $i, $i + 1);
    foreach ($arrySlice as $key => $value1) {
        return $value1;
    }
}


function __checkDataids($filetrFld)
{

    $lastDataid = [];
    $ret = false;
    if (safe_count($filetrFld) > 0) {

        foreach ($filetrFld as $key => $value) {

            if (in_array($value, $lastDataid)) {
                return true;
            } else {
                $lastDataid[] = $value;
            }
        }
    }
    return $ret;
}

function checkFiltercase($filetrFld, $data)
{

    $dataidDup = __checkDataids($filetrFld);
    if ($dataidDup) {

        $ret = false;
        $excnt = 0;
        $cnt = safe_count($filetrFld);
        $existArr = [];
        if (safe_count($filetrFld) > 0) {

            foreach ($filetrFld as $key => $value) {

                $dataid = $value;
                $cond1 = trim($key);

                $matchExpl = explode("##", $cond1);
                $condVal = trim($matchExpl[0]);
                $matchVal = trim($matchExpl[1]);

                $filterArr = $data[$dataid];
                foreach ($filterArr as $value) {
                    if ($matchVal == 'exact') {
                        if (trim($value) == $condVal) {
                            $existArr[] = $condVal;
                        }
                    } else {

                        if (stripos(trim($value), $condVal) !== false) {
                            $existArr[] = $condVal;
                        }
                    }
                }
            }

            $excnt = count(array_unique($existArr));
            if ($cnt == $excnt) {
                $ret = true;
            } else {
                $ret = false;
            }
            return $ret;
        } else {
            $ret = true;
            return $ret;
        }
    } else {
        $ret = true;
        return $ret;
    }
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
