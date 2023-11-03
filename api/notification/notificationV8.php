<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-sUserToken');

function getSolutions($data)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $isSort = true;

    $limit = '';
    $scopeVal = isset($data['scopeVal']) ? $data['scopeVal'] : $data['scopvalue'];
    $sitename = isset($data['sitename']) ? $data['sitename'] : $data['passlevel'];
    if ($scopeVal === "") {
        $response = array("status" => "failed", "message" => 'scopeVal parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($sitename === "") {
        $response = array("status" => "failed", "message" => 'sitename parameter not passed.');
        response(json_encode($response), 404);
    }

    $OS_txt = getMachineOS_func($scopeVal);
    $OS_arr = explode("####", $OS_txt);
    $OS = $OS_arr[0];
    $OSType = "windows";
    if ($OSType === 'windows') {

        $OSName = "Windows";
        $OSDB = "Windows";
        $OSSUB = 'NA';
        $stype = "Service Tag";

        if ($stype === 'Service Tag' || $stype === 'Host Name' || $stype === 'ServiceTag') {
            if ((strpos($OS, 'Windows 10') !== false) || (strpos($OS, 'windows 10') !== false) || (strpos($OS, 'windows10') !== false) || (strpos($OS, 'Windows10') !== false)) {
                $OSSUB = "10";
            } else if ((strpos($OS, 'Windows 8.1') !== false) || (strpos($OS, 'windows 8.1') !== false) || (strpos($OS, 'windows8.1') !== false) || (strpos($OS, 'Windows8.1') !== false)) {
                $OSSUB = "8";
            } else if ((strpos($OS, 'Windows 8') !== false) || (strpos($OS, 'windows 8') !== false) || (strpos($OS, 'Windows8') !== false) || (strpos($OS, 'windows8') !== false)) {
                $OSSUB = "8";
            } else if ((strpos($OS, 'Windows 7') !== false) || (strpos($OS, 'windows 7') !== false) || (strpos($OS, 'Windows7') !== false) || (strpos($OS, 'windows7') !== false)) {
                $OSSUB = "7";
            } else if ((strpos($OS, 'Windows Vista') !== false) || (strpos($OS, 'WindowsVista') !== false)) {
                $OSSUB = "vista";
            } else {
                $OSSUB = "xp";
            }
        } else {
            $OSSUB = 'NA';
        }
    } else if ($OSType === 'android') {
        $OSName = "Android";
        $OSDB = "Android";
    } else if (OSType === 'mac') {
        $OSName = "Mac";
        $OSDB = "Mac";
    } else if (OSType === 'linux') {
        $OSName = "Linux";
        $OSDB = "Linux";
    } else {
        $OSName = "unknow";
        $OSDB = "7";
    }

    $os_txt = $OSDB;
    $pageId = 1;

    $menuitem = url::issetInRequest('menuitem') ? url::requestToText('menuitem') : '';
    if ($os_txt === "Windows") {
    } else if ($os_txt === "Android") {
    } else if ($os_txt === "Mac") {
    } else if ($os_txt === "Linux") {
    } else if ($os_txt === "RLinux") {
    }

    $colname = "OS";
    $sortObject = new stdClass();
    $sortObj = new stdClass();
    $Cname = "menuItem";
    $sortObj->$Cname = $menuitem;

    if ($pageId == '1' || $pageId == 1) {
        if ($os_txt === "Android") {
            $sortObject->$colname = $os_txt;
            $sortObject->$colname = "L3";
        } else {
            $sortObject->$colname = $OSDB;
            $colname = "type";
            $sortObject->$colname = "L3";
        }
    } else {
        $sortObject->$colname = $pageId;
    }

    $sort = $sortObject;
    $mgroupuniq = GetMachineUniqId($scopeVal, $sitename, $db);
    $parent_mgroupuniq = GetSiteUniqId($sitename, $db);

    if ($mgroupuniq === "" || $parent_mgroupuniq === "") {
        $response = array("status" => "failed", "message" => 'Machine details not available.');
        response(json_encode($response), 404);
    }

    $parseobjArr = array();
    $parseobjArr["name"] = "S00304_BaseProfiles";
    $parseobjArr["dart"] = 304;
    $parseobjArr["group"] = $mgroupuniq;

    $newarray = array();
    array_push($newarray, $parseobjArr);
    $response = NH_Config_API_GET($newarray);
    $resObj = safe_json_decode(trim($response));
    if (isset($resObj->value) && !empty(trim($resObj->value))) {
        $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
        $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

        if ($isSort) {
            $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
        } else {
            $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
        }

        response($arr_json, 200);
    } else {
        if ($parseobjArr_parent != $mgroupuniq) {
            $parseobjArr_parent = array();
            $parseobjArr_parent["name"] = "S00304_BaseProfiles";
            $parseobjArr_parent["dart"] = 304;
            $parseobjArr_parent["group"] = $parent_mgroupuniq;
            $newarray = array();
            array_push($newarray, $parseobjArr_parent);
            $response1 = NH_Config_API_GET($newarray);

            $resObj_parent = safe_json_decode(trim($response1));

            if (isset($resObj_parent->value)) {
                $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                if ($isSort) {
                    $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                } else {
                    $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                }

                response($arr_json, 200);
            } else {
                $response = array("status" => "failed", "message" => 'getBaseProfileDetails value not found/set (4)');
                response(json_encode($response), 404);
            }
        } else {
            $response = array("status" => "failed", "message" => 'getBaseProfileDetails value not found/set (5)');
            response(json_encode($response), 404);
        }
    }
}

function GetMachineUniqId($scopeVal, $sitename, $db)
{
    try {
        $sql = "SELECT censusuniq from " . $GLOBALS['PREFIX'] . "core.Census C WHERE C.HOST = '" . $scopeVal . "' AND C.site = '" . $sitename . "' ORDER BY Id desc LIMIT 1";
        $res = find_one($sql, $db);
        $mgroupuniq = "";
        if (safe_count($res) > 0) {
            $mgroupuniq = $res['censusuniq'];
        }
        return $mgroupuniq;
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
}

function GetSiteUniqId($sitename, $db)
{
    try {
        $sql = "select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where NAME= '" . $sitename . "'";
        $res = find_one($sql, $db);
        $parent_mgroupuniq = "";
        if (safe_count($res) > 0) {
            $parent_mgroupuniq = $res['mgroupuniq'];
        }
        return $parent_mgroupuniq;
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
}

function MAKE_CURL_CALL($variablesarray, $method = "get")
{
    global $apiCall_url;
    $url = $apiCall_url . '?method=' . $method;
    $varList = array();

    array_push($varList, $variablesarray);

    $username = 'admin';
    $password = 'nanoheal@123';
    $json_array = json_encode($varList);
    $data_string = '{"jsondata":' . $json_array . '}';
    $header = array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        return $result;
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return "Exception : " . $ex;
    }
}

function createJSONFormat($tableName, $input_string, $colName, $separator, $sub_separator, $sort, $limit = 0)
{
    $input_string = explode($separator, $input_string);
    $json_data = [];

    foreach ($input_string as $key => $value) {
        $row_val = explode($sub_separator, $value, safe_count($colName));
        $exists = false;
        $itemObject = new stdClass();

        foreach ($row_val as $k => $v) {
            $name = $colName[$k];
            $itemObject->$name = trim($v);
        }

        if ($sort == 0) {
            $json_data[$key] = $itemObject;
        } else {

            $isSortComplete_cnt = 0;
            foreach ($sort as $filterKey => $filterVal) {
                if (strpos($filterVal, "!") !== false) {
                    $filterVal = str_replace("!", "", $filterVal);
                    if ($itemObject->$filterKey != $filterVal) {
                        $isSortComplete_cnt = $isSortComplete_cnt + 1;
                    }
                } else {
                    if ($filterKey == 'profile') {
                        if (stripos($itemObject->$filterKey, $filterVal) !== false) {
                            $isSortComplete_cnt = $isSortComplete_cnt + 1;
                        }
                    } else {
                        if ($itemObject->$filterKey == $filterVal) {
                            $isSortComplete_cnt = $isSortComplete_cnt + 1;
                        }
                    }
                }
            }

            if (safe_count($sort) == $isSortComplete_cnt) {
                $json_data[$key] = $itemObject;
            }
        }
    }
    if ((int) $limit > 0) {
        $json_data = array_splice($json_data, 0, $limit);
    }
    return json_encode(array($tableName => $json_data), JSON_PRETTY_PRINT);
}

function getMachineOS_func($searchValue)
{

    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);

    $searchtype = "Service Tag";
    $cType = "NA";
    $machinelist = "";
    $searchManual = "";
    if ($searchtype == 'Service Tag' || $searchtype == 'ServiceTag' || $searchtype == 'Host Name') {
        $selectiontype = 'Machine : ' . $searchValue;
        $Redisres = $redis->lrange($searchValue, 0, -1);
        if (safe_count($Redisres) > 0) {
            $OperatingSystem = $Redisres[4];
            $ServiceTag = $searchValue;
            $versionNo = $Redisres[3];
            if (trim($OperatingSystem) === '') {
                $OperatingSystem = "NULL";
            }
            if ($machinelist === '') {
                $machinelist = $ServiceTag . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
            } else {
                $machinelist .= '~~~~' . $ServiceTag . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
            }
        } else {
            if ($searchManual === '') {
                $machinelist = "'" . $ServiceTag . "'";
            } else {
                $machinelist .= ",'" . $ServiceTag . "'";
            }
        }
    }
    return $OperatingSystem . '####' . $machinelist . '####' . $cType . '####' . $selectiontype . '####' . $searchtype;
}

function pushSolutionsNew($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $serviceTag = isset($data['servicetag']) ? $data['servicetag'] : "";
    $profileVarVal = isset($data['varvalue']) ? $data['varvalue'] : "";
    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'servicetag parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($profileVarVal === "") {
        $response = array("status" => "failed", "message" => 'varvalue parameter not passed.');
        response(json_encode($response), 404);
    }

    $res = N_pushJobSolutions($db, $serviceTag, $profileVarVal);
    echo json_encode($res);
}

function N_pushJobSolutions($db, $serviceTag, $profileVarValue)
{
    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);

    $Redisres = $redis->lrange("$serviceTag", 0, -1);
    $OperatingSystem = '';

    $bid = 0;
    $currTime = time();
    $DartNumber = '286';
    $agentName = 'admin@nanoheal.com';
    $agentUniqId = '10001';
    $notifyId = 0;
    $Type = 'Push Solution API';

    if (safe_count($Redisres) > 0) {

        $OperatingSystem = $Redisres[4];
        $CustNum = $Redisres[1];
        $OrderNum = $Redisres[2];

        $varConfg = "VarName=S00286ProfileName;VarType=2;VarVal=" . $profileVarValue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
        $dartConfg = $varConfg;

        $Selectiontype = 'Machine : ' . $serviceTag;
        $machineOs = $OperatingSystem;

        // $res = 1;

        // if (safe_count($res) > 0) {
        $profileName = "";

        $sqlAudit[] = '(' . $bid . ',"' . $CustNum . '","' . $OrderNum . '","' . $serviceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $notifyId . '","' . $Type . '","' . $machineOs . '","' . $profileName . '","' . $dartConfg . '")';

        $resID = Notify_insertJobsIntoAudit($db, $sqlAudit);
        $redis->select(1);
        $redis->rpush($serviceTag . ":" . $resID, $serviceTag, $resID, $dartConfg, $notifyId, $agentUniqId, $DartNumber);
        $redis->select(0);

        $trigRes = TriggerJobAPI($serviceTag);
        if ($trigRes) {
            return array("status" => "success", "result" => " pushed successfully!");
        } else {
            return array("status" => "failed", "result" => "Failed to push solution!");
        }
        // } else {
        //     return array("status" => "failed", "result" => "Profile not available!");
        // }
    } else {
        $result = array("scopvalue" => $serviceTag, "machine Status" => "error", "msg" => "Machine is not available/Not installed.");
        return array("status" => "failed", "result" => $result);
    }
}

function Notify_insertJobsIntoAudit($db, $sql)
{
    db_change($GLOBALS['PREFIX'] . "communication", $db);

    $sqlQry = "INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence) VALUES " . implode(',', $sql);
    redcommand($sqlQry, $db);

    $FirstInsertId = mysqli_insert_id($db);
    return $FirstInsertId;
}

function getProfileTiles($data)
{
    getSolutions($data);
}

function machineHistory($data)
{

    $machinename = isset($data['machine']) ? $data['machine'] : "";

    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "communication", $db);

    $proactiveRes = [];

    $from = time() - (30 * 24 * 60 * 60);
    $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
        . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$machinename' and JobType IN " . "('Interactive') and JobStatus != '7' and JobCreatedTime >= '$from' ORDER BY AID desc";
    $proactiveRes = find_many($proactiveSql, $db);

    if (safe_count($proactiveRes) > 0) {
        foreach ($proactiveRes as $key => $row) {
            $solutionPushed = $row['ProfileName'];
            $auditId = $row['AID'];
            $eventListN = $row['DartExecutionProof'];
            $state = $row['JobStatus'];

            $machineOS = strtolower($row['MachineOs']);

            if (strpos($machineOS, "windows") !== false || strpos($machineOS, "os x") !== false || strpos($machineOS, "linux") !== false) {
                $strN = 'AuditDetailStatusFn(1,' . $auditId . ',\'' . $eventListN . '\')';
                switch ($state) {
                    case "2":
                        $proof = 'Completed';
                        break;
                    case "3":
                        $proof = 'Failed';
                        break;
                    case "0":
                        $proof = 'Pending';
                        break;
                    default:
                        $proof = 'Pending';
                        break;
                }
            } else if (strpos($machineOS, "ios") !== false || strpos($machineOS, "android") !== false) {
                $strN = 'AuditDetailStatusFn(1,' . $auditId . ',\'' . $eventListN . '\')';
                switch ($state) {
                    case "2":
                        $proof = 'Completed';
                        break;
                    case "3":
                        $proof = 'Failed';
                        break;
                    case "0":
                        $proof = 'Pending';
                        break;
                    default:
                        $proof = 'Pending';
                        break;
                }
            } else {
                $strN = 'AuditDetailStatusFn(0,' . $auditId . ',\'' . $eventListN . '\')';
                $proof = 'Pending';
            }

            $agentName = UTIL_GetTrimmedSiteName($row['AgentName']);
            $recordList[] = array(
                "AID" => '' . $row["AID"] . '',
                "MachineTag" => $row['MachineTag'],
                "AgentName" => $agentName,
                "ProfileName" => urldecode($solutionPushed),
                "JobCreatedTime" => '' . date("m/d/Y h:i A", $row['JobCreatedTime']) . '',
                "JobStatus" => '' . $proof . '',
            );
        }
    } else {
        $recordList = array();
    }
    response(json_encode($recordList), 200);
}

function UserAuthenticate($data)
{

    global $base_url;

    $machinename = isset($data['machine']) ? $data['machine'] : "";
    $DsnUsername = isset($data['DsnUsername']) ? $data['DsnUsername'] : "";
    $Dsnpassword = isset($data['Dsnpassword']) ? $data['Dsnpassword'] : "";

    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    if ($DsnUsername === "") {
        $response = array("status" => "failed", "message" => 'DSN username parameter not passed.');
        response(json_encode($response), 404);
    }

    if ($Dsnpassword === "") {
        $response = array("status" => "failed", "message" => 'DSN Password not passed.');
        response(json_encode($response), 404);
    }

    $db = db_connect();

    $db = db_connect();
    $Dsnpassword = md5($Dsnpassword);
    $sqlq = "select user_email,password from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=lower('$DsnUsername')) and password='$Dsnpassword'";

    $row = find_one($sqlq, $db);
    $count = safe_count($row);
    if ($count > 0) {
        $Rand = generateRandomString($length = 8);
        $Ukey = ValidateUserKey($Rand);

        if ($Ukey > 0) {
            $Rand = generateRandomString($length = 8);
        } else {
            $res = UpdateUserToken($Rand, $DsnUsername, $Dsnpassword);
            if ($res == "success") {
                $responseData = $base_url . "snowlogin.php?SnowUserKey=" . $Rand . "&machine=" . $machinename;
                $resArr = array("status" => "success", "message" => $responseData);
            } else {
                $responseData = "Invalid Usename or Password";
                $resArr = array("status" => "failed", "message" => $responseData);
            }
            response(json_encode($resArr), 200);
        }
    } else {
        $responseData = "Invalid Usename or Password";
        $resArr = array("status" => "failed", "message" => $responseData);
        response(json_encode($resArr), 200);
    }
}

function generateRandomString($length = 8)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function ValidateUserKey($Rand)
{
    $db = db_connect();
    $sqlq = "select userKey from " . $GLOBALS['PREFIX'] . "core.Users where userKey='$Rand'";
    $row = find_one($sqlq, $db);
    $count = safe_count($row);
    return $count;
}

function UpdateUserToken($Rand, $DsnUsername, $Dsnpassword)
{
    $db = db_connect();
    $sqlq = "update " . $GLOBALS['PREFIX'] . "core.Users set userKey='$Rand' where (lower(user_email)=lower('$DsnUsername')) and password='$Dsnpassword'";

    $sql_Res = redcommand($sqlq, $db);
    if ($sql_Res) {
        $res = "success";
    } else {
        $res = "failed";
    }
    return $res;
}

function machineOnlineStatus($data)
{
    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $machinename = isset($data['machine']) ? $data['machine'] : "";

    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    $onlineOffline = 'Offline';
    $machineOs = '';
    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);
    $redis->select(0);
    $nodemahne = strtoupper($machinename);
    $Redisres = $redis->lrange("$nodemahne", 0, -1);

    if (safe_count($Redisres) > 0) {

        $onlineOffline = $Redisres[5];
        $machineOs = $Redisres[4];
    } else {
        $onlineOffline = 'Client not installed on device';
    }
    $resArr = array("machinename" => $machinename, "machinestatus" => $onlineOffline);
    response(json_encode($resArr), 200);
}

function machineBasicInfo($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "asset", $db);

    $machinename = isset($data['machine']) ? $data['machine'] : "";
    $passlevel = isset($data['passlevel']) ? $data['passlevel'] : "";

    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    $wh = '';
    $assetarr = array(
        "username" => "-", "host" => "-", "domain" => "-", "sitename" => "-", "machinemname" => "-", "timezone" => "-", "processmanf" => "-", "proceetype" => "-", "processExtrclock" => "-", "processMaxspeed" => "-",
        "biosvendor" => "-", "os" => "-", "osversion" => "-", "ntproducttype" => "-", "systemManuf" => "-", "systemProduct" => "-", "systemSerial" => "-", "chassistype" => "-", "ramsize" => "-", "ipaddress" => "-"
    );

    if ($passlevel != '') {
        $wh = ' and m.cust="' . $passlevel . '"';
    }
    $sqlMQuery = "select m.machineid,m.host,m.cust from " . $GLOBALS['PREFIX'] . "asset.Machine m where m.host='$machinename' $wh order by m.machineid desc limit 1";
    $sqlMRes = find_one($sqlMQuery, $db);
    if (safe_count($sqlMRes) > 0) {

        $mid = $sqlMRes['machineid'];

        $sqlQuery = "select D.dataid,D.name from " . $GLOBALS['PREFIX'] . "asset.DataName D where "
            . "D.name in ('User Name','Host','Domain','Site Name','Machine Name','Time Zone','Processor Manufacturer','Processor Type','Processor External Clock in MHz','Processor Max Speed in MHz','BIOS Vendor','Operating System','OS Version Number','NT Product Type','System Manufacturer','System Product','System Serial Number','Chassis Type','Array Range Size','IP address')";

        $sqlRes = find_many($sqlQuery, $db);
        if (safe_count($sqlRes) > 0) {
            $assetarr["status"] = "success";

            foreach ($sqlRes as $value) {

                $did = $value['dataid'];
                $dataname = $value['name'];

                $sqlAQuery = "select * from " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest L where L.machineid='$mid' and L.dataid='$did' ORDER BY ordinal desc limit 1";
                $sqlARes = find_one($sqlAQuery, $db);

                if ($dataname == 'User Name') {
                    $assetarr["username"] = $sqlARes['value'];
                }

                if ($dataname == 'Host') {
                    $assetarr["host"] = $sqlARes['value'];
                }

                if ($dataname == 'Domain') {
                    $assetarr["domain"] = $sqlARes['value'];
                }

                if ($dataname == 'Site Name') {

                    $siteName = explode("__", $sqlARes['value']);
                    $assetarr["sitename"] = $siteName[0];
                }

                if ($dataname == 'Machine Name') {
                    $assetarr["machinemname"] = $sqlARes['value'];
                }

                if ($dataname == 'Time Zone') {
                    $assetarr["timezone"] = $sqlARes['value'];
                }

                if ($dataname == 'Processor Manufacturer') {
                    $assetarr["processmanf"] = $sqlARes['value'];
                }

                if ($dataname == 'Processor Type') {
                    $assetarr["proceetype"] = $sqlARes['value'];
                }

                if ($dataname == 'Processor External Clock in MHz') {
                    $assetarr["processExtrclock"] = $sqlARes['value'];
                }

                if ($dataname == 'Processor Max Speed in MHz') {
                    $assetarr["processMaxspeed"] = $sqlARes['value'];
                }

                if ($dataname == 'BIOS Vendor') {
                    $assetarr["biosvendor"] = $sqlARes['value'];
                }

                if ($dataname == 'Operating System') {
                    $assetarr["os"] = $sqlARes['value'];
                }

                if ($dataname == 'OS Version Number') {
                    $assetarr["osversion"] = $sqlARes['value'];
                }

                if ($dataname == 'NT Product Type') {
                    $assetarr["ntproducttype"] = $sqlARes['value'];
                }

                if ($dataname == 'System Manufacturer') {
                    $assetarr["systemManuf"] = $sqlARes['value'];
                }

                if ($dataname == 'System Product') {
                    $assetarr["systemProduct"] = $sqlARes['value'];
                }

                if ($dataname == 'System Serial Number') {
                    $assetarr["systemSerial"] = $sqlARes['value'];
                }

                if ($dataname == 'Chassis Type') {
                    $assetarr["chassistype"] = $sqlARes['value'];
                }

                if ($dataname == 'Array Range Size') {
                    $assetarr["ramsize"] = $sqlARes['value'];
                }

                if ($dataname == 'IP address') {
                    $assetarr["ipaddress"] = $sqlARes['value'];
                }
            }
        } else {
            $assetarr["status"] = "fail";
        }
        response(json_encode($assetarr), 200);
    } else {
        $assetarr["status"] = "fail";
        response(json_encode($assetarr), 404);
    }
}

function machineAssetID($data)
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "asset", $db);

    $machinename = isset($data['machine']) ? $data['machine'] : "";

    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    $sql = "select WS_ticketId from " . $GLOBALS['PREFIX'] . "asset.cmdbAssetDetails A where A.machineName='$machinename' order by A.id desc limit 1";

    $sqlRes = find_one($sql, $db);
    $count = safe_count($sqlRes);
    if ($count > 0) {
        $sys_id = $sqlRes['WS_ticketId'];
        $response = array("status" => "success", "message" => "Success", "sys_id" => $sys_id);
        response(json_encode($response), 200);
    } else {
        $sys_id = "Record Not found";
        $response = array("status" => "failed", "message" => "Record Not found");
        response(json_encode($response), 404);
    }
}

function actionInteractive($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $serviceTag = isset($data['servicetag']) ? $data['servicetag'] : "";
    $profileVarVal = isset($data['varvalue']) ? $data['varvalue'] : "";
    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'servicetag parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($profileVarVal === "") {
        $response = array("status" => "failed", "message" => 'varvalue parameter not passed.');
        response(json_encode($response), 404);
    }

    $res = N_pushJobSolutions($db, $serviceTag, $profileVarVal);
    echo json_encode($res);
}

function getMachineDetails($data)
{

    $searchVal = isset($data['scopvalue']) ? $data['scopvalue'] : "";
    $agentEmail = isset($data['AgentEmail']) ? $data['AgentEmail'] : "";

    if ($searchVal === "") {
        $response = array("status" => "failed", "message" => 'scopvalue parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($agentEmail === "") {
        $response = array("status" => "failed", "message" => 'agentemail parameter not passed.');
        response(json_encode($response), 404);
    }

    $machineList = array();
    $db = db_connect();
    $sqlq = "select username,user_email,password from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=lower('$agentEmail'))";
    $sql_Res = find_one($sqlq, $db);
    if (safe_count($sql_Res) > 0) {
        $username = $sql_Res['username'];

        $sqlCust = "select id,host,site from " . $GLOBALS['PREFIX'] . "core.Census where site in (select customer from " . $GLOBALS['PREFIX'] . "core.Customers where username='$username') and host ='$searchVal'";
        $sqlRes = find_many($sqlCust, $db);
        if (safe_count($sqlRes) > 0) {
            foreach ($sqlRes as $value) {
                $site = $value['site'];
                $host = $value['host'];
                $machineList[] = array("host" => $host, "site" => $site, "displaySitename" => $site);
            }
            $retVal = array("status" => "success", "msg" => $machineList);
            response(json_encode($retVal), 200);
        } else {

            $retVal = array("status" => "error", "msg" => "Machine not in Agent scope.");
            response(json_encode($retVal), 404);
        }
    } else {
        $retVal = array("status" => "error", "msg" => "Agent details not available.");
        response(json_encode($retVal), 404);
    }
}

function getMachinesList()
{
    $db = db_connect();
    $sqlq = 'select S.siteName, S.serviceTag from ' . $GLOBALS['PREFIX'] . 'agent.serviceRequest S  group by S.serviceTag';
    $sql_Res = find_many($sqlq, $db);

    if (safe_count($sql_Res) > 0) {
        response(json_encode($sql_Res), 200);
    } else {
        $res = "{}";
        response(json_encode($res), 404);
    }
}

function getNotificationSummary($data)
{
    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $passlevel = isset($data['passlevel']) ? $data['passlevel'] : "";
    $searchVal = isset($data['scopvalue']) ? $data['scopvalue'] : "";

    if ($searchVal === "") {
        $response = array("status" => "failed", "message" => 'scopvalue parameter not passed.');
        response(json_encode($response), 404);
    }

    $db = db_connect();

    $machineRes = NTFI_getMachineDtl($db, $searchVal);
    if (safe_count($machineRes) > 0) {
        $onlineOffline = 'Offline';

        $redis = new Redis();
        $redis->connect($redis_url, $redis_port);
        $redis->auth($redis_pwd);
        $redis->select(0);
        $Redisres = $redis->lrange("$searchVal", 0, -1);

        if (safe_count($Redisres) > 0) {

            $onlineOffline = $Redisres[5];
        } else {
            $onlineOffline = 'Not Found';
        }

        if ($passlevel != '') {
            $sitename = $passlevel;
        } else {
            $sitename = $machineRes['site'];
        }

        $criticalCnt = 0;
        $majorCnt = 0;
        $minorCnt = 0;

        $notify_res = NTFI_New_getNotificationSummary($db, $searchVal, $sitename);

        $criticalList = [];
        $majorList = [];
        $minorList = [];
        if (safe_count($notify_res) > 0) {
            foreach ($notify_res as $key => $value1) {

                $priority = $value1['priority'];
                $cnt = $value1['cnt'];
                if ($priority == 1 || $priority == 2) {
                    $criticalCnt = $criticalCnt + 1;
                }
                if ($priority == 3 || $priority == 4) {
                    $majorCnt = $majorCnt + 1;
                }
                if ($priority >= 5) {
                    $minorCnt = $minorCnt + 1;
                }
            }
        }

        $notify_dtlres = Notify_getNotificationSummaryDtl_new($db, $searchVal, $sitename);
        $retVal = array("status" => "1", "machinename" => $searchVal, "machinestatus" => $onlineOffline, "criticalcnt" => $criticalCnt, "majorcnt" => $majorCnt, "minorcnt" => $minorCnt, $notify_dtlres);
    } else {

        $minorList[] = array("status" => "No data found");
        $majorList[] = array("status" => "No data found");
        $criticalList[] = array("status" => "No data found");

        $notify_dtlres = array("critical" => $criticalList, "major" => $majorList, "minor" => $minorList);

        $retVal = array("status" => "0", "machinename" => $searchVal, "machinestatus" => 'Nanoheal not installed on device', "criticalcnt" => 0, "majorcnt" => 0, "minorcnt" => 0, $notify_dtlres);
    }

    response(json_encode($retVal), 200);
}

function NTFI_getMachineDtl($db, $searchVal)
{

    try {

        $query = "select C.site,C.host from " . $GLOBALS['PREFIX'] . "core.Census C where C.host = '$searchVal' order by C.last desc limit 1";
        $censusdtl = find_one($query, $db);
        if (safe_count($censusdtl) > 0) {
            return $censusdtl;
        } else {
            return array();
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function NTFI_New_getNotificationSummary($db, $searchVal, $sitename)
{

    $from = date('Y-m-d', strtotime('-15 days'));
    $today = time();
    $preTime = strtotime($from);

    $query = "select priority,count(tid) cnt from (select tid, servertime, nocName, status, nid, machine,priority,serverdate from (select tid, servertime, nocName, nocStatus as status, nid,  machine,priority,FROM_UNIXTIME(servertime, '%Y-%m-%d') serverdate from  " . $GLOBALS['PREFIX'] . "event.tempNotification where username='admin' and servertime between $preTime and $today and machine = '$searchVal' and site='$sitename'   ORDER BY tid desc) as K   group by nid,serverdate) as J group by priority,nid ";
    $notfCnt = find_many($query, $db);
    if (safe_count($notfCnt) > 0) {
        return $notfCnt;
    } else {
        return array();
    }
}

function Notify_getNotificationSummaryDtl_new($db, $searchVal, $sitename)
{

    $stat = " and T.nocStatus IS NULL";
    $sqlWh = '';
    $machines = "'" . implode("','", $data) . "'";
    $sqlWh .= "and machine in ($machines)";

    $key = 1;
    if ($key) {

        $from = date('Y-m-d', strtotime('-15 days'));
        $today = time();
        $preTime = strtotime($from);

        $criticalCnt = 0;
        $majorCnt = 0;
        $minorCnt = 0;

        $criticalList = [];
        $majorList = [];
        $minorList = [];

        $sql_query = "select  nocName, nid, priority,count(tid) Cnt1 from (select tid, servertime, nocName, status, nid,  machine,priority,serverdate,count(tid) Cnt from (select tid, servertime, nocName, nocStatus as status, nid,  machine,priority,FROM_UNIXTIME(servertime, '%Y-%m-%d') serverdate from  " . $GLOBALS['PREFIX'] . "event.tempNotification where username='admin' and servertime between $preTime and $today and machine = '$searchVal' and site='$sitename'   ORDER BY tid desc) as K  group by nid,serverdate order by nid,serverdate,priority) as J group by nid";
        $notf_Cnt = find_many($sql_query, $db);
        if (safe_count($notf_Cnt) > 0) {
            foreach ($notf_Cnt as $key => $value) {

                $nid1 = $value['nid'];
                $nocName1 = $value['nocName'];
                $eventCount1 = $value['Cnt1'];
                $priority1 = $value['priority'];

                if ($priority1 == 1 || $priority1 == 2) {
                    $criticalCnt = $criticalCnt + $eventCount1;
                }

                if ($priority1 == 3 || $priority1 == 4) {
                    $majorCnt = $majorCnt + $eventCount1;
                }

                if ($priority1 >= 5) {
                    $minorCnt = $minorCnt + $eventCount1;
                }

                $nidDtl = Notify_getNotifiySNDtls($key, $db, $nid1, $searchVal, $sitename);
                if (safe_count($nidDtl) > 0) {
                    $list = [];
                    foreach ($nidDtl as $key => $value1) {

                        $tid = $value1['tid'];
                        $servertime = $value1['servertime'];
                        $nocName = $value1['nocName'];
                        $statusVal = $value1['status'];
                        $nid = $value1['nid'];
                        $machine = $value1['machine'];
                        $priority = $value1['priority'];
                        $notifyDt = $value1['serverdate'];
                        $machineOS = $value1['machineOs'];
                        $consoleId = $value1['consoleId'];
                        $scrip = $value1['scrip'];
                        $eventIdx = $value1['eventIdx'];
                        $eventCount = $value1['Cnt'];
                        $site = $value1['site'];
                        $version = $value1['clientversion'];
                        $dartExeStat = '';

                        if ($statusVal == '') {
                            $status = 'New';
                        } else if ($statusVal == 'Fixed' && $dartExeStat != '') {
                            $status = 'Completed';
                        } else {
                            $status = 'Actioned';
                        }

                        if ($machineOS == '') {
                            $machineOS = 'NULL';
                        }

                        $list[] = array("device" => $machine, "eventdate" => $servertime, "eventcount" => $eventCount, "status" => $status, "os" => $machineOS, "tempid" => $tid, "notifydate" => $notifyDt, "consoleid" => $consoleId, "dartno" => $scrip, "eventIdx" => $eventIdx, "sitename" => $site, "clientversion" => $version);
                    }
                }

                if ($priority1 == 1 || $priority1 == 2) {
                    $criticalList[] = array("nid" => $nid1, "nocname" => $nocName1, "count" => $eventCount1, "details" => $list);
                }

                if ($priority1 == 3 || $priority1 == 4) {
                    $majorList[] = array("nid" => $nid1, "nocname" => $nocName1, "count" => $eventCount1, "details" => $list);
                }

                if ($priority1 >= 5) {
                    $minorList[] = array("nid" => $nid1, "nocname" => $nocName1, "count" => $eventCount1, "details" => $list);
                }
            }

            if (empty($minorList)) {
                $minorList[] = array("status" => "No data found");
            }

            if (empty($majorList)) {
                $majorList[] = array("status" => "No data found");
            }

            if (empty($criticalList)) {
                $criticalList[] = array("status" => "No data found");
            }

            return array("critical" => $criticalList, "major" => $majorList, "minor" => $minorList);
        } else {

            $minorList[] = array("status" => "No data found");
            $majorList[] = array("status" => "No data found");
            $criticalList[] = array("status" => "No data found");

            return array("critical" => $criticalList, "major" => $majorList, "minor" => $minorList);
        }
    } else {
        echo "Your key has been expired";
    }
}

function actionNotifications($data)
{
    getSolutions($data);
}

function getProfileData($data)
{
    getSolutions($data);
}
