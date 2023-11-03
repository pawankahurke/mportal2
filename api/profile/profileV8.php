<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-sUserToken');

//error_reporting(-1);
//ini_set('display_errors', 'On');

function getMachinewise($data)
{
    // @warn $username used in sql query without filtration (SQL Injection Issues )
    $username = $data["username"];
    $pdo = NanoDB::connect();
    //Manika fix after 14-06-2021 start
    $dartNo = '';
    $assetname = '';
    if ($_SERVER['HTTP_HOST'] == 'fix-it.nanoheal.com' || $_SERVER['HTTP_HOST'] == 'pov.nanoheal.com' || $_SERVER['HTTP_HOST'] == "dp-3-23-dashboard.default.svc.cluster.local") {
        $dartNo = 56;
        $assetname = "value->>'$.useraccountname'";
    } else {
        $dartNo = 21;
        $assetname = "value->>'$.username'";
    }

    $q = "select max(from_unixtime(A.slatest,'%Y-%m-%d')) as slatest,
    M.host as 'machine',
    M.cust as cust, C.host,
    1 as count from " . $GLOBALS['PREFIX'] . "asset.AssetData as A
    join " . $GLOBALS['PREFIX'] . "asset.Machine as M on A.machineid = M.machineid
    join " . $GLOBALS['PREFIX'] . "core.Census as C on C.host = M.host
    where A.dataid = $dartNo and $assetname  = '" . $username . "'
    group by M.Cust, A.machineid";
    //log_error($q);

    $stmt = $pdo->query($q);
    //Manika fix after 14-06-2021 end
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $machineArr = array();
    foreach ($rows as $key => $value) {
        array_push($machineArr, $value['machine']);
    }
    response(json_encode($machineArr), 200);
}

function getjobstatus($data)
{
    $jobid = $data['jobid'];
    $pdo = NanoDB::connect();
    $stmt = $pdo->query("select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where JobType = 'Push Solution API' and AID = '" . $jobid . "'");
    $rows = $stmt->fetch(PDO::FETCH_ASSOC);
    $status = $rows['JobStatus'];
    if ($status == '2' || $status == 2) {
        $status = "Completed";
    } else if ($status == '0' || $status == 0) {
        $status = "Pending";
    } else if ($status == '3' || $status == 3) {
        $status = "Failed";
    } else {
        $status = "Failed";
    }
    $resultStatus = array("status" => $status);
    response(json_encode($resultStatus), 200);
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
        'Content-Type: application/json',
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
                    if ($itemObject->$filterKey == $filterVal) {
                        $isSortComplete_cnt = $isSortComplete_cnt + 1;
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

    $redis = RedisLink::connect();


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

    $db = NanoDB::connect();

    $serviceTag = isset($data['servicetag']) ? $data['servicetag'] : "";
    $profileVarVal = isset($data['varvalue']) ? $data['varvalue'] : "";
    $agentName = '';
    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'servicetag parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($profileVarVal === "") {
        $response = array("status" => "failed", "message" => 'varvalue parameter not passed.');
        response(json_encode($response), 404);
    }

    $res = N_pushJobSolutions($db, $serviceTag, $profileVarVal, $agentName);
}

function pushSolutionItems($data)
{
    $db = NanoDB::connect();

    $serviceTag = isset($data['servicetag']) ? $data['servicetag'] : "";
    $shortDesc = isset($data['solutionVar']) ? $data['solutionVar'] : "";
    $username = isset($data['username']) ? $data['username'] : "";

    $agentName = 'nh';

    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'servicetag parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($shortDesc === "") {
        $response = array("status" => "failed", "message" => 'solutionVar parameter not passed.');
        response(json_encode($response), 404);
    }

    $res_push = N_pushJobSolutions($db, $serviceTag, $shortDesc, $username);
    // $status = 0;
    // $statusMsg = "Failed to push solution!";
    //     if ($res_push['status'] === 'success') {
    //         $status = "success";
    //         $statusMsg = "Task pushed to action Queue";
    //     }

    if (!isset($res_push['jobid']) || !$res_push['jobid']) {
        response(json($res_push), 200);
        return;
    }
    $res = array("jobid" => $res_push['jobid']);
    response(json($res), 200);
}

function N_pushJobSolutions($db, $serviceTag, $profileVarValue, $agentNameIn)
{
    $redis = RedisLink::connect(true);

    $Redisres = $redis->lrange("$serviceTag", 0, -1);

    $OperatingSystem = '';

    $bid = 0;
    $currTime = time();
    $DartNumber = '286';
    $agentName = $agentNameIn;
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

        $sqlAudit[] = $bid . ',' . $CustNum . ',' . $OrderNum . ',' . $serviceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $notifyId . ',' . $Type . ',' . $machineOs . ',' . $profileName . ',' . $dartConfg;
        $resID = Notify_insertJobsIntoAudit($db, $sqlAudit); // Insert Into Audit

        $redis->select(1); // Select Jobs Redis Memory
        $redis->rpush($serviceTag . ":" . $resID, $serviceTag, $resID, $dartConfg, $notifyId, $agentUniqId, $DartNumber);
        $redis->select(0);

        $trigRes = TriggerJobAPI($serviceTag);
        if ($trigRes) {
            return array("status" => "success", "result" => " pushed successfully!", "jobid" => $resID);
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
    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($sql as $key => $val) {
        $vals = explode(',', $val);
        $sqlQry->execute($vals);
    }
    $lastInsertId = $db->lastInsertId();
    return $lastInsertId;
}

function TriggerJobAPI($servicetag)
{
    $db = NanoDB::connect();
    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'dashboard_config' limit 1");
    $sql->execute();
    $set = $sql->fetch();
    $confdata = safe_json_decode($set['value'], true);
    $wsurl = $confdata['wsurl'];
    $nodeJobUrl = get_nodeJobUrl_v6($wsurl);

    $curl = curl_init();

    $appkey = getenv('API_AppKey') ?: "GfmdPDkAsS8R3xm8RRnpSwXHxkfYVj6Ho";
    $seckey = getenv('API_SecKey') ?: "36JydVSbpjqeMA7abgjZVJ2vBHW25npT";

    curl_setopt_array($curl, array(
        CURLOPT_URL => $nodeJobUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "",
        CURLOPT_HTTPHEADER => array(
            "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
            "AuthenticationKey:" . $appkey,
            "SecretKey:" . $seckey,
            "ServiceTag:" . $servicetag,
            "cache-control: no-cache",
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return $err;
    } else {
        return 1;
    }
}

function get_nodeJobUrl_v6($wsurl)
{

    $https_replaced = str_replace("https://", "", $wsurl);
    $http_replaced = str_replace("http://", "", $https_replaced);
    $wss_replaced = str_replace("wss://", "", $http_replaced);
    $hfnws_replaced = str_replace("hfnws", "triggerjob", $wss_replaced);
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "https://";

    $url_replaced = $pageURL . $hfnws_replaced;

    return $url_replaced;
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

    $from = time() - (30 * 24 * 60 * 60); // for showing -30 Days DATA from Today's Date
    $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
        . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$machinename' and JobType IN " //AND CustomerNO in ($custList) and OrderNO in ($ordList)
        . "('Interactive','Push Solution API') and JobStatus != '7' and JobCreatedTime >= '$from' ORDER BY AID desc"; //and AgentUniqId='$user_email'

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

    $machinename = isset($data['machine']) ? $data['machine'] : "";
    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    $onlineOffline = 'Offline';


    $redis = RedisLink::connect();
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

    if ($machinename !== "" && $passlevel != "") {
        $resultinfo = getElasticBasicInfo($machinename, $passlevel);

        if (safe_count($resultinfo) > 0) {

            if ($resultinfo['status'] == 'fail') {
                response(json_encode($resultinfo), 404);
            } else {
                response(json_encode($resultinfo), 200);
            }
        } else {

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
                    response(json_encode($assetarr), 200);
                } else {
                    $resultinfo = getElasticBasicInfo($machinename, $passlevel);

                    if ($resultinfo['status'] == 'fail') {
                        response(json_encode($resultinfo), 404);
                    } else {
                        response(json_encode($resultinfo), 200);
                    }
                }
            }
        }
    } else {
        $response = array("status" => "failed", "message" => 'machine or site parameter not passed.');
        response(json_encode($response), 404);
    }
}

function getElasticBasicInfo($machinename, $passlevel)
{
    logs::log(__FILE__, __LINE__, "Error:CodeRemoved");
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
    $sqlq = 'select S.siteName, S.serviceTag from ' . $GLOBALS['PREFIX'] . 'agent.serviceRequest S where S.downloadStatus = "EXE" group by S.serviceTag';
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

        $redis = RedisLink::connect();
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
    getProfileSolutions($data);
}

function getProfileItems($data)
{
    getTilesSolutions($data);
}

function get_V6_profile_format($input)
{

    $obj = safe_json_decode($input, true);

    foreach ($obj as $key => $value) {
        if ($key === 'Main') {
            $loop_1 = $value;
        }
    }

    $i = 0;
    $in_arr_1 = [];
    foreach ($loop_1 as $key => $value) {
        $profile = isset($value['profile']) ? $value['profile'] : "";
        $dart = isset($value['dart']) ? $value['dart'] : "";
        $variable = isset($value['variable']) ? $value['variable'] : "";
        $shortDesc = isset($value['shortDesc']) ? $value['shortDesc'] : "";
        $description = isset($value['description']) ? $value['description'] : "";
        $page = isset($value['page']) ? $value['page'] : "";
        $menuItem = isset($value['menuItem']) ? $value['menuItem'] : "";
        $clickfun = isset($value['clickfun']) ? $value['clickfun'] : "clickl1level";
        $parentId = $key;

        $in_arr_1[$i]['parentId'] = $parentId;
        $in_arr_1[$i]['profile'] = $profile;
        $in_arr_1[$i]['dart'] = $dart;
        $in_arr_1[$i]['variable'] = $variable;
        $in_arr_1[$i]['shortDesc'] = $shortDesc;
        $in_arr_1[$i]['description'] = $description;
        $in_arr_1[$i]['page'] = $page;
        $in_arr_1[$i]['menuItem'] = $menuItem;
        $in_arr_1[$i]['clickfun'] = $clickfun;
        $i++;
    }
    $res['data'] = [];
    if (safe_count($in_arr_1) > 0) {
        $res['data'] = $in_arr_1;
        $res['backParentId'] = "2";
        $res['profile'] = "Troubleshooters";
        $res['tiledesc'] = "Troubleshooters##You can fix many common issues easily with these powerful troubleshooting tools.\r\nChoose a category on the left, and then  Select the fix that best matches the symptoms of the problem";
    }
    return $result = json_encode($res);
}

function getProfileSolutions($data)
{
    $level = isset($data["level"]) ? strip_tags($data["level"]) : 'main';

    $profile = '';

    $recordList = [];

    $serviceTag = isset($data['servicetag']) ? $data['servicetag'] : $data['scopvalue'];

    $os = isset($data["os"]) ? strip_tags($data["os"]) : 'Windows';
    $ossub = isset($data["ossub"]) ? strip_tags($data["ossub"]) : 'NA';
    $pageId = isset($data["pageId"]) ? strip_tags($data["pageId"]) : '1';

    $menuitem = isset($data["menuitem"]) ? strip_tags($data["menuitem"]) : '';

    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'servicetag parameter not passed.');
        response(json_encode($response), 200);
    }

    if ($os === "Windows") {
        $profile = 'event.' . $_SESSION["user"]["profileName"];
    } else if ($os === "Android") {
    } else if ($os === "Mac") {
    } else if ($os === "Linux") {
    } else if ($os === "RLinux") {
    }

    $wh = '';
    $sortObject = new stdClass();
    $colname = "page";
    $sortobject = new stdClass();
    $cname = "parentId";
    $sortObj = new stdClass();
    $Cname = "menuItem";
    $sortObj->$Cname = $menuitem;

    if ($pageId == '1' || $pageId == 1) {
        if ($os === "Android") {
            $sortObject->$colname = "1";
            $sortobject->$cname = "1";
        } else {
            $sortObject->$colname = "2";
            $sortobject->$cname = "1";
        }
    } else {
        $sortObject->$colname = $pageId;
        $sortobject->$cname = $pageId;
    }
    if ($ossub !== 'NA') {
        $wh .= "and (OS like '%$ossub%' or OS = 'common')";
    }

    $Res = get_mgroupuniqueid($serviceTag);

    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    $main_arr = $arr_response["Main"];

    $apiresponse1 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, "limit" => 1, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse1 = safe_json_decode(json_encode($apiresponse1), true);
    $mainarr = $arrresponse1["Main"];

    $apiresponse2 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, $sortObj, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse2 = safe_json_decode(json_encode($apiresponse2), true);
    $mainarr2 = $arrresponse2["Main"];

    $all_api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $all_arr_response = safe_json_decode(json_encode($all_api_response), true);
    $allProfile_arr = $all_arr_response["Main"];

    $new_mid = 0;
    $new_page = 0;
    unset($_SESSION['profileKeys']);
    unset($_SESSION['new_mid']);
    unset($_SESSION['$new_page']);
    $profileKeys = [];
    $page_L2 = [];
    $parent_L2 = [];

    foreach ($allProfile_arr as $key => $val) {

        $empty = $val['Enable/Disable'];
        if ($empty != '') {
            $mid = $allProfile_arr[$key]['mid'];
            $new_mid = (int) $mid + 1;
            $profileKeys = safe_array_keys($allProfile_arr[$key]);
        }
    }
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $profileKeys);
    foreach ($allProfile_arr as $key => $val) {
        $type = $val['type'];

        $page = $allProfile_arr[$key]['page'];
        array_push($page_L2, (int) $page);
        $page_num = max($page_L2);

        $parentId = $allProfile_arr[$key]['parentId'];
        array_push($parent_L2, (int) $parentId);
        $parentId_num = max($parent_L2);
    }
    if ($parentId_num > $page_num) {
        $new_page = (int) $parentId_num + 1;
    } else {
        $new_page = (int) $page_num + 1;
    }

    $_SESSION["new_mid"] = $new_mid;
    $_SESSION["profileKeys"] = implode("***", $string);
    $_SESSION["new_page"] = $new_page;
    if ($menuitem == '') {
        $qry = $mainarr;
    } else {
        $qry = $mainarr2;
    }
    $backParentId = $qry;
    $tileDesc = '';
    $finStr = '';
    $count_res = safe_count($main_arr);
    $profilearr = [];

    if ($count_res > 0) {
        foreach ($main_arr as $key => $val) {
            $menuItem = $main_arr[$key]['menuItem'];
            $type = $main_arr[$key]['type'];
            $parentId = $main_arr[$key]['parentId'];
            $profile = $main_arr[$key]['profile'];
            $dart = $main_arr[$key]['dart'];
            $variable = $main_arr[$key]['varValue'];
            $shortDesc = $main_arr[$key]['shortDesc'];
            $description = $main_arr[$key]['tileDesc'];
            $page = $main_arr[$key]['page'];
            $status = $main_arr[$key]['Enable/Disable'];
            $mid = $main_arr[$key]['mid'];
            $Os = $main_arr[$key]['OS'];
            if ($status == '1' || $status == 1 || $status == '3' || $status == 3) {
                $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "'";
                if ($type == 'L1') {
                    $tileDesc = $profile . '##' . $description;
                } elseif ($type == 'L2') {
                    $finStr .= '<li><a href="javascript:;" title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '</a></li>';
                    $recordList[] = array("parentId" => $parentId, "profile" => safe_addslashes($profile), "dart" => $dart, "variable" => $variable, "shortDesc" => $shortDesc, "description" => urlencode($description), "page" => $page, "menuItem" => $menuItem, "clickfun" => "clickl1level");
                } elseif ($type == 'L3') {
                    if (!in_array($profile, $profilearr)) {
                        array_push($profilearr, $profile);
                        $finStr .= '<li><a href="javascript:;" title="' . $profile . '" onclick="clickl3level(' . $param . ');">' . $profile . '</a></li>';
                        $recordList[] = array("parentId" => $parentId, "profile" => safe_addslashes($profile), "dart" => $dart, "variable" => $variable, "shortDesc" => $shortDesc, "description" => urlencode($description), "page" => $page, "menuItem" => $menuItem, "clickfun" => "clickl3level");
                    }
                }
            }
        }
    }
    $jsonData = array("data" => $recordList, "backParentId" => $backParentId, "profile" => $profile, "tiledesc" => safe_addslashes($tileDesc));
    response(json_encode($jsonData), 200);
}

function getTilesSolutions($data)
{
    $level = isset($data["level"]) ? strip_tags($data["level"]) : 'main';

    $profile = '';

    $recordList = [];

    $serviceTag = isset($data['servicetag']) ? $data['servicetag'] : $data['scopvalue'];

    $os = isset($data["os"]) ? strip_tags($data["os"]) : 'Windows';
    $ossub = isset($data["ossub"]) ? strip_tags($data["ossub"]) : 'NA';
    $pageId = '2';

    $menuitem = isset($data["menuitem"]) ? strip_tags($data["menuitem"]) : '';
    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'servicetag parameter not passed.');
        response(json_encode($response), 200);
    }

    if ($os === "Windows") {
        $profile = 'event.' . $_SESSION["user"]["profileName"];
    } else if ($os === "Android") {
    } else if ($os === "Mac") {
    } else if ($os === "Linux") {
    } else if ($os === "RLinux") {
    }

    $wh = '';
    $sortObject = new stdClass();
    $colname = "page";
    $sortobject = new stdClass();
    $cname = "parentId";
    $sortObj = new stdClass();
    $Cname = "menuItem";
    $sortObj->$Cname = $menuitem;

    if ($pageId == '1' || $pageId == 1) {
        if ($os === "Android") {
            $sortObject->$colname = "1";
            $sortobject->$cname = "1";
        } else {
            $sortObject->$colname = "2";
            $sortobject->$cname = "1";
        }
    } else {
    }
    if ($ossub !== 'NA') {
        $wh .= "and (OS like '%$ossub%' or OS = 'common')";
    }

    $Res = get_mgroupuniqueid($serviceTag);
    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    $main_arr = $arr_response["Main"];

    $apiresponse1 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, "limit" => 1, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse1 = safe_json_decode(json_encode($apiresponse1), true);
    $mainarr = $arrresponse1["Main"];

    $apiresponse2 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, $sortObj, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse2 = safe_json_decode(json_encode($apiresponse2), true);
    $mainarr2 = $arrresponse2["Main"];

    $all_api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $all_arr_response = safe_json_decode(json_encode($all_api_response), true);
    $allProfile_arr = $all_arr_response["Main"];

    $new_mid = 0;
    $new_page = 0;
    unset($_SESSION['profileKeys']);
    unset($_SESSION['new_mid']);
    unset($_SESSION['$new_page']);
    $profileKeys = [];
    $page_L2 = [];
    $parent_L2 = [];

    foreach ($allProfile_arr as $key => $val) {

        $empty = $val['Enable/Disable'];
        if ($empty != '') {
            $mid = $allProfile_arr[$key]['mid'];
            $new_mid = (int) $mid + 1;
            $profileKeys = safe_array_keys($allProfile_arr[$key]);
        }
    }
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $profileKeys);

    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $profileKeys);
    foreach ($allProfile_arr as $key => $val) {
        $type = $val['type'];

        $page = $allProfile_arr[$key]['page'];
        array_push($page_L2, (int) $page);
        $page_num = max($page_L2);

        $parentId = $allProfile_arr[$key]['parentId'];
        array_push($parent_L2, (int) $parentId);
        $parentId_num = max($parent_L2);
    }
    if ($parentId_num > $page_num) {
        $new_page = (int) $parentId_num + 1;
    } else {
        $new_page = (int) $page_num + 1;
    }

    $_SESSION["new_mid"] = $new_mid;
    $_SESSION["profileKeys"] = implode("***", $string);
    $_SESSION["new_page"] = $new_page;
    if ($menuitem == '') {
        $qry = $mainarr;
    } else {
        $qry = $mainarr2;
    }
    $backParentId = $qry;
    $tileDesc = '';
    $finStr = '';
    $count_res = safe_count($main_arr);
    $profilearr = [];
    if ($count_res > 0) {
        foreach ($main_arr as $key => $val) {
            $menuItem = $main_arr[$key]['menuItem'];
            $type = $main_arr[$key]['type'];
            $parentId = $main_arr[$key]['parentId'];
            $profile = $main_arr[$key]['profile'];
            $dart = $main_arr[$key]['dart'];
            $variable = $main_arr[$key]['varValue'];
            $shortDesc = $main_arr[$key]['shortDesc'];
            $description = $main_arr[$key]['tileDesc'];
            $page = $main_arr[$key]['page'];
            $status = $main_arr[$key]['Enable/Disable'];
            $mid = $main_arr[$key]['mid'];
            $Os = $main_arr[$key]['OS'];

            if ($status == '1' || $status == 1 || $status == '3' || $status == 3) {
                if ($type == 'L1') {
                    $tileDesc = $profile . '##' . $description;
                } elseif ($type == 'L2') {
                } elseif ($type == 'L3') {
                    if (!in_array($profile, $profilearr)) {
                        array_push($profilearr, $profile);
                        $recordList[] = array("profile" => safe_addslashes($profile), "solutionVar" => $variable);
                    }
                }
            }
        }
    }

    $jsonData = array("profiles" => $recordList);
    response(json_encode($jsonData), 200);
}

function getSolutions($data)
{

    $os = isset($data['os']) ? $data['os'] : "Windows";
    $pageId = isset($data['pageId']) ? $data['pageId'] : "";
    $ossub = isset($data['ossub']) ? $data['ossub'] : "";
    $menuitem = isset($data['menuitem']) ? $data['menuitem'] : "";

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    if ($os === "Windows") {
        $profile = 'profile';
    } else if ($os === "Android") {
        $profile = 'profile';
    } else if ($os === "Mac") {
        $profile = 'profile';
    } else if ($os === "Linux") {
        $profile = 'profile';
    }

    $wh = '';

    if ($pageId == '1' || $pageId == 1) {
        if ($os === "Android") {
            $wh = "page = '1'";
        } else {
            $wh = "page = '2'";
        }
    } else {
        $wh = "page = '$pageId'";
    }

    if ($ossub !== 'NA') {
        $wh .= "and (OS like '%$ossub%' or OS = 'common')";
    }

    $sqlQuery = "select mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,tileDesc,"
        . "iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart from $profile where $wh and follow !='CORE/' group by shortDesc "
        . "UNION select mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,tileDesc,"
        . "iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart  from $profile  where $wh and follow !='CORE/' "
        . "group by shortDesc order by mid";

    $sqlRes = find_many($sqlQuery, $db);

    if ($menuitem == '') {
        $qry = "select page from $profile where  parentId = '$pageId' limit 1";
    } else {
        $qry = "select page from $profile where  parentId = '$pageId' and menuItem = '$menuitem' limit 1";
    }

    $qryres = find_one($qry, $db);
    $backParentId = $qryres['page'];
    $tileDesc = '';
    $cnt = safe_count($sqlRes);
    $finStr = '';
    for ($i = 0; $i < $cnt; $i++) {

        $menuItem = $sqlRes[$i]['menuItem'];
        $type = $sqlRes[$i]['type'];
        $parentId = $sqlRes[$i]['parentId'];
        $profile = $sqlRes[$i]['profile'];
        $dart = $sqlRes[$i]['dart'];
        $variable = $sqlRes[$i]['variable'];
        $shortDesc = $sqlRes[$i]['shortDesc'];
        $description = $sqlRes[$i]['tileDesc'];
        $page = $sqlRes[$i]['page'];

        $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "'";
        if ($type == 'L1') {
            $tileDesc = $profile . '##' . $description;
        } elseif ($type == 'L2') {
            $finStr .= '<li><a href="javascript:;" title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '</a></li>';
            $recordList[] = array("parentId" => $parentId, "profile" => safe_addslashes($profile), "dart" => $dart, "variable" => $variable, "shortDesc" => $shortDesc, "description" => urlencode($description), "page" => $page, "menuItem" => $menuItem, "clickfun" => "clickl1level");
        } elseif ($type == 'L3') {
            $finStr .= '<li><a href="javascript:;" title="' . $profile . '" onclick="clickl3level(' . $param . ');">' . $profile . '</a></li>';
            $recordList[] = array("parentId" => $parentId, "profile" => safe_addslashes($profile), "dart" => $dart, "variable" => $variable, "shortDesc" => $shortDesc, "description" => urlencode($description), "page" => $page, "menuItem" => $menuItem, "clickfun" => "clickl3level");
        }
    }
    $jsonData = array("data" => $recordList, "backParentId" => $backParentId, "profile" => $profile, "tiledesc" => safe_addslashes($tileDesc));
    echo json_encode($jsonData);
}

function actionInteractive($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $serviceTag = isset($data['machine']) ? $data['machine'] : "";
    $shortDesc = isset($data['variable']) ? $data['variable'] : "";
    $AgentEmail = isset($data['AgentEmail']) ? $data['AgentEmail'] : "";

    $agentName = '';

    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($shortDesc === "") {
        $response = array("status" => "failed", "message" => 'variable parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($AgentEmail === "") {
        $response = array("status" => "failed", "message" => 'AgentEmail parameter not passed.');
        response(json_encode($response), 404);
    }

    $sqlq = "select username,user_email from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=lower('$AgentEmail'))";
    $sql_Res = find_one($sqlq, $db);
    if (safe_count($sql_Res) > 0) {
        $agentName = $sql_Res['username'];
    }

    $res_push = N_pushJobSolutions($db, $serviceTag, $shortDesc, $agentName);
    $status = 0;
    $statusMsg = "failed";
    if ($res_push['status'] === 'success') {
        $status = 1;
        $statusMsg = "Task pushed to action Queue";
    }
    $res = array("serviceTagSupported" => $serviceTag, "serviceTagNotSupported" => "", "bid" => "", "progServiceTag" => "", "searchtype" => "", "onlineOffline" => "", "notifyRes" => "", "status" => $status, "message" => $statusMsg);
    echo json_encode($res);
}

function pushProfileSolution($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $serviceTag = isset($data['machine']) ? $data['machine'] : "";
    $shortDesc = isset($data['variable']) ? $data['variable'] : "";
    $AgentEmail = isset($data['AgentEmail']) ? $data['AgentEmail'] : "";

    $agentName = '';

    if ($serviceTag === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($shortDesc === "") {
        $response = array("status" => "failed", "message" => 'shortDesc parameter not passed.');
        response(json_encode($response), 404);
    }
    if ($AgentEmail === "") {
        $response = array("status" => "failed", "message" => 'AgentEmail parameter not passed.');
        response(json_encode($response), 404);
    }

    $sqlq = "select username,user_email from " . $GLOBALS['PREFIX'] . "core.Users where (lower(user_email)=lower('$AgentEmail'))";
    $sql_Res = find_one($sqlq, $db);
    if (safe_count($sql_Res) > 0) {
        $agentName = $sql_Res['username'];
    }

    $res_push = N_pushJobSolutions($db, $serviceTag, $shortDesc, $agentName);
    $status = 0;
    $statusMsg = "failed";
    if ($res_push['status'] === 'success') {
        $status = 1;
        $statusMsg = "Task pushed to action Queue";
    }
    $res = array("serviceTagSupported" => $serviceTag, "serviceTagNotSupported" => "", "bid" => "", "progServiceTag" => "", "searchtype" => "", "onlineOffline" => "", "notifyRes" => "", "status" => $status, "message" => $statusMsg);
    echo json_encode($res);
}

function get_mgroupuniqueid($searchValue)
{
    $db = NanoDB::connect();

    $sql_site = $db->prepare("select site from " . $GLOBALS['PREFIX'] . "core.Census c where c.host = ? order by id desc limit 1");
    $sql_site->execute([$searchValue]);
    $sql1Res_site = $sql_site->fetch();

    $rparentValue = $sql1Res_site['site'];

    $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like '%$rparentValue:$searchValue%' order by mgroupid desc limit 1");
    $sql->execute();
    $sqlRes = $sql->fetch();

    $sql1 = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
    $sql1->execute([$rparentValue]);
    $sql1Res = $sql1->fetch();

    $mgroupid = $sqlRes['mgroupuniq'];
    $mgroupidParent = $sql1Res['mgroupuniq'];

    return array("mgroupuniq" => $mgroupid, "parentmgroupid" => $mgroupidParent);
}

function exportmachineHistory($data)
{

    $machinename = isset($data['machine']) ? $data['machine'] : "";

    if ($machinename === "") {
        $response = array("status" => "failed", "message" => 'machine parameter not passed.');
        response(json_encode($response), 404);
    }

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "communication", $db);

    $proactiveRes = [];

    require './vendor/autoload.php';

    $index = 2;
    $slno = 1;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'SL No');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Machine Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Agent Name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Created Time');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Profile Name');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Status');

    $from = time() - (30 * 24 * 60 * 60); // for showing -30 Days DATA from Today's Date
    $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
        . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$machinename' and JobType IN " //AND CustomerNO in ($custList) and OrderNO in ($ordList)
        . "('Interactive','Push Solution API') and JobStatus != '7' and JobCreatedTime >= '$from' order by JobCreatedTime desc"; //and AgentUniqId='$user_email'

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

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $slno . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $machinename . '');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $agentName . '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . date("m/d/Y h:i A", $row['JobCreatedTime']) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $solutionPushed . '');
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, '' . $proof . '');
            $index++;
            $slno++;
        }
    } else {
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available');
    }

    $fn = $machinename . "'s__ActionHistory.xls";
    $objPHPExcel->setActiveSheetIndex(0);

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');
    $writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    $writer->save('php://output');
}

function getTicketInfo($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $ticketNo = isset($data['ticketNo']) ? $data['ticketNo'] : "";
    if ($ticketNo === "") {
        $response = array("status" => "failed", "message" => 'Tikect number parameter not passed.');
        response(json_encode($response), 404);
    }

    if ($ticketNo === 'INC0000019') {

        $res = array("status" => "success", "machine" => "HFNL100038", "site" => "SelfhealNEXTERA__201900017");
        response(json_encode($res), 200);
    }
}
