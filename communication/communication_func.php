<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';
require_once '../include/common_functions.php';
include_once '../lib/l-profileAPI.php';
include_once '../lib/l-elastic.php';
include_once '../communication/common_communication.php';

function AddAndroidJobs_func()
{
    $db = NanoDB::connect();

    global $redis_url;
    global $redis_port;
    global $redis_pwd;
    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);

    $agentName = $_SESSION["user"]["logged_username"];
    $agentUniqId = $_SESSION["user"]["adminEmail"];

    if (is_null($agentUniqId) || $agentUniqId === '') {
        $agentUniqId = $_SESSION["user"]["adminid"];
    }

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];

    $SelectedOS = url::requestToText('OS');
    $Type = url::requestToText('Jobtype');
    $ShortDesc = url::requestToText('shortDesc');
    $ProfileName = url::requestToText('ProfileName');
    $DartNumber = url::requestToText('Dart');
    $Variable = url::requestToText('variable');
    $GroupName = url::requestToText('GroupName');

    $ProgServiceTag = '';
    $onlineOffline = 'Offline';

    $bid = 0;
    $idx = 0;

    $sqlAudit = array();
    $currTime = time();

    $ServiceTagNotSupported = '';
    $ServiceTagSupported = '';
    $Selectiontype = '';
    $DartCommon = '';
    $livetime = '';
    $status = 2;

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $Selectiontype = 'Machine : ' . $searchValue;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $Selectiontype = 'Site : ' . $searchValue;
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $Selectiontype = 'Group : ' . $GroupName;
    }
    $SelectedOS = strtolower($SelectedOS);
    if ($SelectedOS === 'android' && $Type === 'Interactive') {
        $Profile = $_SESSION["user"]["andprofileName"];
    } else if ($SelectedOS === 'android' && $Type === 'Software Distribution') {
        $qry = $db->prepare("select packageDesc, packageName, configDetail, sourceType, distributionConfigDetail from " . $GLOBALS['PREFIX'] . "softinst.Packages where id =?");
        $qry->execute([$DartNumber]);
        $res = $qry->fetch();
        $packageDesc = $res['packageDesc'];
        $packageName = $res['packageName'];
        $sourceType = $res['sourceType'];
        $distributionConfigDetail = $res['distributionConfigDetail'];

        $androidtype = 0;

        if (trim($sourceType) == '4') {
            $androidtype = 2;
        } else {
            $androidtype = 1;
        }

        $batchinsert = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Jobid (agentUniqId, packageName, SelectionType, createdtime) VALUES (?,?,?,?)");
        $batchinsert->execute([$agentUniqId, $packageName, $Selectiontype, $currTime]);
        $bid = $db->lastInsertId();

        $ProfileName = "SWD : " . $packageDesc;
    }

    if ($Type === 'Interactive' || $Type === 'Notification') {

        $sqlQry = $db->prepare("select varValue, OS from $table where shortDesc = ? limit ?");
        $sqlQry->execute([$ShortDesc, 1]);
        $resQry = $sqlQry->fetchAll();

        foreach ($resQry as $key => $value) {
            $varvalue = $value['varValue'];

            if (intval($DartNumber) === 43) {
                $DartCommon = "VarName=S00043SilentUninstall;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=43;VarScope=1;";
                $commonProfile = 1;
            } else if (intval($DartNumber) === 256) {
                $DartCommon = "VarName=" . $Variable . ";VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=256;VarScope=1;";
                $commonProfile = 1;
            } else if (intval($DartNumber) === 177) {
                $DartCommon = "VarName=Scrip177RunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=177;VarScope=1;";
                $commonProfile = 1;
            } else if (intval($DartNumber) === 148) {
                $DartCommon = "RCNF=1;VarName=Scrip148RunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=148;VarScope=1;";
                $commonProfile = 1;
            } else if (intval($DartNumber) === 286) {
                $DartCommon = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                $commonProfile = 1;
            }
        }
    } else if ($Type === 'Software Distribution') {
        $commonProfile = 1;
        if ($SelectedOS === 'windows') {
            $DartCommon = "RCNF=1;VarName=S00288IndividualPatches;VarType=2;VarVal=" . "SWD-" . $DartNumber . ";Action=SET;DartNum=288;VarScope=1;#;NextConf;#RCNF=1;VarName=S00288RunNowButtonnew;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=288;VarScope=1;";
            $DartNumber = 288;
        } else {
            if (stripos($distributionConfigDetail, "32Link") !== false) {
                $DartCommon = "RCNF=1;VarName=S00288PatchesAvailableNew5;VarType=2;VarVal=" . $distributionConfigDetail . ";Action=SET;DartNum=288;VarScope=1;#;NextConf;#RCNF=1;VarName=S00288IndividualPatches;VarType=2;VarVal=1," . $packageName . ";Action=SET;DartNum=288;VarScope=1;#;NextConf;#RCNF=1;VarName=S00288RunNowButtonnew;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=288;VarScope=1;";
                $DartNumber = 288;
            } else {
                $DartCommon = "RCNF=1;VarName=S00415ServerIp;VarType=2;VarVal=" . $distributionConfigDetail . ";Action=SET;DartNum=415;VarScope=1;#;NextConf;#RCNF=1;VarName=S00415RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=415;VarScope=1;";
                $DartNumber = 415;
            }
        }
    } else if ($Type === 'Message') {

        $batchinsert = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Jobid (agentUniqId, packageName, SelectionType, createdtime) VALUES (?,?,?,?)");
        $batchinsert->execute([$agentUniqId, $packageName, $Selectiontype, $currTime]);
        $bid = $db->lastInsertId();
        $ProfileName = "MSG : " . $ProfileName;

        $id = url::requestToAny('variable');

        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.MessageDetails WHERE id=?");
        $sql->execute([$id]);
        $sqlRes = $sql->fetch();

        $message = $sqlRes['message'];
        $url = $sqlRes['url'];
        $title = $sqlRes['name'];
        $button1 = $sqlRes['button1'];
        $button2 = $sqlRes['button2'];
        $time = $sqlRes['time'];
        $frequency = $sqlRes['frequency'];
        $livetime = $sqlRes['livetime'];

        $DartCommon = "$bid###SEND_NOTIFICATION:$title@@@$message@@@$url@@@$button1@@@$button2@@@$time@@@$frequency@@@$livetime";
        $DartNumber = 00;
        $status = 0;
    } else if ($Type === 'ClearMessage') {

        $profilename = 'MSG : ' . $DartNumber;

        $aidsql = $db->prepare("SELECT GROUP_CONCAT(AID SEPARATOR  ',') as AID FROM " . $GLOBALS['PREFIX'] . "communication.Audit WHERE ProfileName=? and JobStatus=?");
        $aidsql->execute([$profilename, 0]);
        $aidRes = $aidsql->fetch();
        $Aid = $aidRes['AID'];
        if ($Aid != '') {

            $aid = explode(',', $Aid);
            $in = str_repeat('?,', safe_count($aid) - 1) . '?';
            $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "communication.Audit set JobStatus=20 WHERE AID IN ($in)");
            $sql->execute($aid);
            $ssqlRes = $db->lastInsertId();
        }

        $batchinsert = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Jobid (agentUniqId, packageName, SelectionType, createdtime) VALUES (?,?,?,?)");
        $batchinsert->execute([$agentUniqId, $packageName, $Selectiontype, $currTime]);
        $bid = $db->lastInsertId();

        $ProfileName = "MSG : " . $ProfileName;
        $DartCommon = url::requestToText('variable');
        $status = 20;
        $DartNumber = 00;
        $status = 0;
    }

    $DeviceKey = "";
    $DeviceName = "";

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

        $Redisres = $redis->lrange("$searchValue", 0, -1);
        if (safe_count($Redisres) > 0) {

            $CustomerNumber = $Redisres[1];
            $OrderNumber = $Redisres[2];
            $OperatingSystem = $Redisres[4];
            $APIKEY = $Redisres[6];
            $onlineOffline = "Offline";
            $profileConfig = $DartCommon;

            if (stripos($OperatingSystem, $SelectedOS) !== false) {
                $sqlAudit[] = [$bid, $CustomerNumber, $OrderNumber, $searchValue, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig, $status];
                if ($DeviceKey === "") {
                    $DeviceKey = $APIKEY;
                    $DeviceName = $searchValue;
                } else {
                    $DeviceKey .= "," . $APIKEY;
                    $DeviceName .= "," . $searchValue;
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $searchValue;
                } else {
                    $ServiceTagNotSupported .= '~~' . $ServiceTag;
                }
            }
        } else {
            if ($ServiceTagNotSupported === '') {
                $ServiceTagNotSupported = $ServiceTag;
            } else {
                $ServiceTagNotSupported .= '~~' . $ServiceTag;
            }
        }
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $onlineOffline = "Offline";
        $profileConfig = $DartCommon;

        $siteScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        $sitemachines = DASH_GetMachinesSites($key, $db, $siteScope);
        foreach ($sitemachines as $key => $value) {
            $Redisres = $redis->lrange("$sitemachines[$key]", 0, -1);
            if (safe_count($Redisres) > 0) {

                $CustomerNumber = $Redisres[1];
                $OrderNumber = $Redisres[2];
                $OperatingSystem = $Redisres[4];
                $ServiceTag = $Redisres[0];
                $APIKEY = $Redisres[6];
                $profileConfig = '';

                if (stripos($OperatingSystem, $SelectedOS) !== false) {
                    $profileConfig = $DartCommon;
                    if ($profileConfig !== '') {
                        $sqlAudit[] = [$bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig, $status];
                        if ($DeviceKey === "") {
                            $DeviceKey = $APIKEY;
                            $DeviceName = $ServiceTag;
                        } else {
                            $DeviceKey .= "," . $APIKEY;
                            $DeviceName .= "," . $ServiceTag;
                        }
                        if (safe_count($sqlAudit) === 1000) {
                            $resID = InsertMobileJobsIntoAudit($sqlAudit);
                            for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                $val = explode(',', $sqlAudit[$x]);
                                if ($ServiceTagSupported === '') {
                                    $ServiceTagSupported = trim($val[3], '"');
                                } else {
                                    $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                }
                                $resID++;
                            }

                            FCMSendMessage_func($DartCommon, $DeviceKey, $DeviceName, $livetime);
                            unset($sqlAudit);
                            $sqlAudit = array();
                            $DeviceKey = "";
                            $DeviceName = "";
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $sitemachines[$key];
                        } else {
                            $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $sitemachines[$key];
                    } else {
                        $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                    }
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $sitemachines[$key];
                } else {
                    $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                }
            }
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $onlineOffline = "Offline";
        $profileConfig = $DartCommon;

        $groupScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        $groupmachines = DASH_GetGroupsMachines($key, $db, $groupScope);
        foreach ($groupmachines as $key => $value) {
            $Redisres = $redis->lrange("$groupmachines[$key]", 0, -1);
            if (safe_count($Redisres) > 0) {
                $CustomerNumber = $Redisres[1];
                $OrderNumber = $Redisres[2];
                $OperatingSystem = $Redisres[4];
                $ServiceTag = $Redisres[0];
                $APIKEY = $Redisres[6];
                $profileConfig = '';

                if (stripos($OperatingSystem, $SelectedOS) !== false) {
                    $profileConfig = $DartCommon;
                    if ($profileConfig !== '') {
                        $sqlAuditQuer[] = [$bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig, $status];
                        $sqlAudit[] = [$bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig, $status];
                        if ($DeviceKey === "") {
                            $DeviceKey = $APIKEY;
                            $DeviceName = $ServiceTag;
                        } else {
                            $DeviceKey .= "," . $APIKEY;
                            $DeviceName .= "," . $ServiceTag;
                        }
                        if (safe_count($sqlAudit) === 1000) {
                            $resID = InsertMobileJobsIntoAudit($sqlAuditQuer);
                            for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                $val = explode(',', $sqlAudit[$x]);
                                if ($ServiceTagSupported === '') {
                                    $ServiceTagSupported = trim($val[3], '"');
                                } else {
                                    $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                }
                                $resID++;
                            }

                            FCMSendMessage_func($DartCommon, $DeviceKey, $DeviceName, $livetime);
                            unset($sqlAudit);
                            $sqlAudit = array();
                            $DeviceKey = "";
                            $DeviceName = "";
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $sitemachines[$key];
                        } else {
                            $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $sitemachines[$key];
                    } else {
                        $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                    }
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $sitemachines[$key];
                } else {
                    $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                }
            }
        }
    }
    if (safe_count($sqlAudit) !== 0) {
        $resID = InsertMobileJobsIntoAudit($sqlAudit);
        for ($x = 0; $x < safe_count($sqlAudit); $x++) {
            $val = explode(',', $sqlAudit[$x]);
            if ($ServiceTagSupported === '') {
                $ServiceTagSupported = "'" . trim($val[3], '"') . "'";
            } else {
                $ServiceTagSupported .= '~~' . "'" . trim($val[3], '"') . "'";
            }
        }

        FCMSendMessage_func($DartCommon, $DeviceKey, $DeviceName, $livetime);
    }
    $GroupName = !empty($GroupName) ? $GroupName : $searchValue;
    $action = "Execute - " . $Variable . "( DART-" . $DartNumber . ")";
    create_auditLog('Troubleshooter', $action, 'Success', $_REQUEST, $GroupName);
    $redis->close();
    echo $ServiceTagSupported . '##' . $ServiceTagNotSupported . '##' . $bid . '##' . $ProgServiceTag . '##' . $searchtype . '##' . $onlineOffline;
}

function FCMSendMessage_func($msg, $DeviceID, $DeviceName, $timetolive)
{
    $currTime = time();

    define("GOOGLE_API_KEY", "AIzaSyBNv7Qdf1EYuvy7-7f4lAdgRZH2hqhsprU");
    $url = 'https://fcm.googleapis.com/fcm/send';

    if ($timetolive == '') {
        $msg = $currTime . "###" . $msg;
        $message = array("price" => "$msg");
        $FCMID = explode(",", $DeviceID);
        $fields = array(
            'registration_ids' => $FCMID,
            'data' => $message,
        );
    } else {
        $message = array("price" => "$msg");
        $FCMID = explode(",", $DeviceID);
        $time = $timetolive * 60 * 60;
        $fields = array(
            'registration_ids' => $FCMID,
            'data' => $message,
            'time_to_live' => $time,
        );
    }

    $headers = array(
        'Authorization: key=' . GOOGLE_API_KEY,
        'Content-Type: application/json',
    );

    $splittedName = explode(",", $DeviceName);
    $machine = array(
        'ServiceTag' => $splittedName,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);

    if ($result === false) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);

    $remove_ids = array();

    $jsonArray = safe_json_decode($result);
    if (!empty($jsonArray->results)) {
        for ($i = 0; $i < safe_count($jsonArray->results); $i++) {
            if (isset($jsonArray->results[$i]->error)) {
                $remove_ids[$i] = $machine["ServiceTag"][$i] . " : " . $jsonArray->results[$i]->error . "\r\n";
            }
        }
    }
    $SuccessCount = $jsonArray->success;
    $FailureCount = $jsonArray->failure;
    $ReturnString = "Success : " . strval($SuccessCount) . ", Failed : " . strval($FailureCount);

    if (!empty($remove_ids)) {
        $remove_ids_string = implode(' ', $remove_ids);
        echo $ReturnString . "\r\n[Failed Log]\r\n " . $remove_ids_string . "\r\n";
    } else {
        echo $ReturnString . "\r\n";
    }
}

function InsertMobileJobsIntoAudit($sql)
{
    $pdo = pdo_connect();
    foreach ($sql as $key => $value) {
        $sqlQry = $pdo->prepare("INSERT ignore INTO " . $GLOBALS['PREFIX'] . "communication.Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName,
 ProfileSequence, JobStatus) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $sqlQry->execute($value);
        $FirstInsertId = $pdo->lastInsertId();
    }
    return $FirstInsertId;
}


function get_OS($type, $name)
{
    $result = null;
    $sql = "";
    if ($type === "Sites") {
        $sql = "SELECT DISTINCT os FROM " . $GLOBALS['PREFIX'] . "core.Census where site = ?";
        $result = NanoDB::find_many($sql, null, [$name]);
    }

    if ($type === "Groups") {
        $sql = "SELECT  DISTINCT Census.os as os FROM  " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap, " . $GLOBALS['PREFIX'] . "core.MachineGroups
      WHERE
      Census.censusuniq = MachineGroupMap.censusuniq
      AND
      MachineGroups.mgroupuniq=MachineGroupMap.mgroupuniq
      AND
      MachineGroups.style <> 1
      AND MachineGroups.name = ?";
        $result = NanoDB::find_many($sql, null, [$name]);
    }

    if ($type === "ServiceTag") {
        $sql = "SELECT DISTINCT os FROM " . $GLOBALS['PREFIX'] . "core.Census where host = ?";
        $result = NanoDB::find_many($sql, null, [$name]);

        if (!$result || !$result[0]['os']) {
            $sql = "SELECT  value->>'$.operatingsystem' as os from " . $GLOBALS['PREFIX'] . "asset.AssetDataDaily add2 where machineid in
             (SELECT machineid FROM " . $GLOBALS['PREFIX'] . "asset.Machine m WHERE host = ? )
             and dataid = 16 limit 1";
            $result = NanoDB::find_many($sql, null, [$name]);
        }
    }

    $array = [];
    foreach ($result as $value) {
        $osName = $value["os"];

        if (preg_match("/^windows/i", $osName) === 1) {
            array_push($array, "windows");
        }

        if (preg_match("/^android/i", $osName) === 1) {
            array_push($array, "android");
        }

        if (preg_match("/^mac/i", $osName) === 1) {
            array_push($array, "mac");
        }

        if (preg_match("/^linux/i", $osName) === 1) {
            array_push($array, "linux");
        }

        if (preg_match("/^ios/i", $osName) === 1) {
            array_push($array, "ios");
        }

        if (preg_match("/^readynet router/i", $osName) === 1) {
            array_push($array, "readynet router");
        }
    }

    $os = array_unique($array);

    return $os;
}


function AddRemoteJobs_func($os = null)
{
    $db = NanoDB::connect();

    $SelectedOS = ($os) ? $os : url::requestToText('OS');
    $name = url::requestToStringAz09('name');
    $type = url::requestToStringAz09('type');

    $searchValue = $name;
    $searchtype =  $type;

    if (($searchtype == 'Sites' || $searchtype == 'Groups') && url::requestToText('ProfileName') === 'Uninstall Tech Services') {
        // @todo fix it.
        echo "NotAvaiableForSite";
        exit;
    }

    if ($SelectedOS === "unknow") {
        $arrayOS = get_OS($type, $name);
        foreach ($arrayOS as $os) {
            AddRemoteJobs_func($os);
        }
        exit;
    }

    $redis = RedisLink::connect(true);

    $agentName =  "admin"; // $_SESSION["user"]["logged_username"]
    $agentUniqId =  $_SESSION["user"]["adminEmail"];

    if (is_null($agentUniqId) || $agentUniqId === '') {
        $agentUniqId = $_SESSION["user"]["adminid"];
    }

    $Type = url::requestToText('Jobtype');
    // $ShortDesc = url::requestToText('shortDesc');
    $ProfileName = url::rawPost('ProfileName');
    $DartNumber = url::requestToText('Dart');
    $Variable = url::requestToText('variable');
    $NotificationWindow = url::requestToText('NotificationWindow');
    $GroupName = url::requestToText('GroupName');
    $Notification = strip_tags(trim($_SESSION['notifyselArr']));

    $ProgServiceTag = '';
    $onlineOffline = 'Offline';

    if (intVal($NotificationWindow) === 1) {
        $Type = 'Notification';
    }


    $bid = 0;
    $idx = 0;

    $sqlAudit = array();
    $currTime = time();

    $Dartxp = '';
    $Dartvista = '';
    $Dart7 = '';
    $Dart8 = '';
    $Dart10 = '';
    $DartCommon = '';
    $commonProfile = 0;

    $ServiceTagNotSupported = '';
    $ServiceTagSupported = '';
    $Selectiontype = '';
    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $Selectiontype = 'Machine : ' . $searchValue;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $Selectiontype = 'Site : ' . $searchValue;
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $Selectiontype = 'Group : ' . $GroupName;
    }

    if ($Type === 'Software Distribution') {

        $qry = $db->prepare("select packageDesc, packageName, configDetail, sourceType from " . $GLOBALS['PREFIX'] . "softinst.Packages where id =?");
        $qry->execute([$DartNumber]);
        $res = $qry->fetch();

        $packageDesc = $res['packageDesc'];
        $packageName = $res['packageName'];


        $batchinsert = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Jobid (agentUniqId, packageName, SelectionType, createdtime) VALUES (?,?,?,?)");
        $batchinsert->execute([$agentUniqId, $packageName, $Selectiontype, $currTime]);
        $bid = $db->lastInsertId();

        $ProfileName = "SWD : " . $packageDesc;
    }

    if ($Type === 'Interactive' || $Type === 'Notification') {

        $varvalue = url::requestToText('variable');
        $OS = url::requestToText('ProfileOS');

        if (intval($DartNumber) === 43) {
            $DartCommon = "VarName=S00043SilentUninstall;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=43;VarScope=1;";
            $commonProfile = 1;
        } else if (intval($DartNumber) === 256) {
            $DartCommon = "VarName=" . $Variable . ";VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=256;VarScope=1;";
            $commonProfile = 1;
        } else if (intval($DartNumber) === 286) {
            if ($OS == 'common') {
                $DartCommon = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                $commonProfile = 1;
            } else {
                if (stripos($OS, 'xp') !== false) {
                    $Dartxp = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, 'vista') !== false) {
                    $Dartvista = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, '7') !== false) {
                    $Dart7 = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, '8') !== false) {
                    $Dart8 = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, '10') !== false) {
                    $Dart10 = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
            }
        }
    } else if (intval($DartNumber) === 275) {
        $commonProfile = 1;
        $DartCommon = "VarName=constS00275WmiMethod;VarType=2;VarVal=" . $Variable . ";Action=SET;DartNum=275;VarScope=1;#;NextConf;#VarName=S00286ProfileName;VarType=2;VarVal=WMI;Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
    } else if ($Type === 'Software Distribution') {
        $commonProfile = 1;
        if ($SelectedOS === 'windows' || $SelectedOS === 'os x' || $SelectedOS === 'linux') {
            $DartCommon = "VarName=S00288IndividualPatches;VarType=2;VarVal=" . "SWD-" . $DartNumber . ";Action=SET;DartNum=288;VarScope=1;#;NextConf;#VarName=S00288RunNowButtonnew;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=288;VarScope=1;";
            $DartNumber = 288;
        }
    } else if ($Type === 'Custom Profile') {
        $commonProfile = 1;
        $DartCommon = "VarName=S00286RunTimeConfig;VarType=2;VarVal=" . $ProfileName . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
    }

    if (intVal($NotificationWindow) !== 1) {
        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $ServiceTag = $searchValue;
            $ProgServiceTag = $ServiceTag;
            $Redisres = $redis->lrange("$searchValue", 0, -1);
            if (safe_count($Redisres) > 0) {
                $CustomerNumber = (trim($Redisres[1]) != '') ? trim($Redisres[1]) : '0';
                $OrderNumber = (trim($Redisres[2]) != '') ? trim($Redisres[2]) : '0';
                $OperatingSystem = $Redisres[4];
                $onlineOffline = $Redisres[5];
                $profileConfig = '';

                if (stripos($OperatingSystem, $SelectedOS) !== false) {
                    if ($commonProfile === 1) {
                        $profileConfig = $DartCommon;
                    } else {
                        if (stripos($OperatingSystem, '8') !== false) {
                            $profileConfig = $Dart8;
                        } else if (stripos($OperatingSystem, '7') !== false) {
                            $profileConfig = $Dart7;
                        } else if (stripos($OperatingSystem, '10') !== false) {
                            $profileConfig = $Dart10;
                        } else if (stripos($OperatingSystem, 'vista') !== false) {
                            $profileConfig = $Dartvista;
                        } else if (stripos($OperatingSystem, 'xp') !== false) {
                            $profileConfig = $Dartxp;
                        } else {
                            $profileConfig = $DartCommon;
                        }
                    }
                    if ($Type === 'Custom Profile') {
                        $ProfileName = explode('DartNo=269', $ProfileName)[0];
                    }

                    if ($profileConfig !== '') {
                        $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $idx . '","' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                        $sqlAuditArr = [ $bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig ];

                        $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $idx . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $ServiceTag;
                        } else {
                            $ServiceTagNotSupported .= '~~' . $ServiceTag;
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $ServiceTag;
                    } else {
                        $ServiceTagNotSupported .= '~~' . $ServiceTag;
                    }
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $ServiceTag;
                } else {
                    $ServiceTagNotSupported .= '~~' . $ServiceTag;
                }
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            $siteScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $sitemachines = DASH_GetMachinesSites(null/* not used */, $db, $siteScope);
            foreach ($sitemachines as $key => $value) {

                $Redisres = $redis->lrange("$sitemachines[$key]", 0, -1);
                if (safe_count($Redisres) > 0) {
                    $CustomerNumber = $Redisres[1];
                    $OrderNumber = $Redisres[2];
                    $OperatingSystem = $Redisres[4];
                    $ServiceTag = $sitemachines[$key];
                    $profileConfig = '';

                    if (stripos($OperatingSystem, $SelectedOS) !== false) {
                        if ($commonProfile === 1) {
                            $profileConfig = $DartCommon;
                        } else {
                            if (stripos($OperatingSystem, '8') !== false) {
                                $profileConfig = $Dart8;
                            } else if (stripos($OperatingSystem, '7') !== false) {
                                $profileConfig = $Dart7;
                            } else if (stripos($OperatingSystem, '10') !== false) {
                                $profileConfig = $Dart10;
                            } else if (stripos($OperatingSystem, 'vista') !== false) {
                                $profileConfig = $Dartvista;
                            } else if (stripos($OperatingSystem, 'xp') !== false) {
                                $profileConfig = $Dartxp;
                            } else {
                                $profileConfig = $DartCommon;
                            }
                        }
                        if ($profileConfig !== '') {
                            $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $idx . '","' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                            $sqlAuditArr = [ $bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig ];

                            $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $idx . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                            if (safe_count($sqlAudit) === 1000) {
                                $redis->select(1);
                                $resID = InsertJobsIntoAudit($sqlAuditQuer);

                                $swdData = array();
                                $swdData["id"] = "$resID";
                                $swdData["bid"] = "$bid";
                                $swdData["indexname"] = "swd_" . date("Y_m");
                                $swdData["logtype"] = "swdcreate";
                                $swdData["customerno"] = $resData[1];
                                $swdData["orderno"] = $resData[2];
                                $swdData["machine"] = $resData[3];
                                $swdData["jobcreatedtime"] = $resData[4];
                                $swdData["selectiontype"] = $resData[5];
                                $swdData["dart"] = $resData[6];
                                $swdData["agentname"] = $resData[7];
                                $swdData["AgentUniqId"] = $resData[8];
                                $swdData["IDX"] = "0";
                                $swdData["JobType"] = $resData[10];
                                $swdData["MachineOs"] = $resData[11];
                                $swdData["ProfileName"] = $resData[12];
                                $swdData["ProfileSequence"] = $resData[13];
                                $swdData["ClientTimeZone"] = "";
                                $swdData["ClientExecutedTime"] = "";
                                $swdData["JobStatus"] = "0";
                                $swdData["DartExecutionProof"] = "";

                                for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                    $val = explode(',', $sqlAudit[$x]);
                                    $JobMsg = $val[13];
                                    $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
                                    if ($ServiceTagSupported === '') {
                                        $ServiceTagSupported = trim($val[3], '"');
                                    } else {
                                        $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                    }
                                    $resID++;
                                }

                                $redis->select(0);
                                unset($sqlAudit);
                                $sqlAudit = array();
                            }
                        } else {
                            if ($ServiceTagNotSupported === '') {
                                $ServiceTagNotSupported = $sitemachines[$key];
                            } else {
                                $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                            }
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $sitemachines[$key];
                        } else {
                            $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $sitemachines[$key];
                    } else {
                        $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                    }
                }
            }
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {

            $groupScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $groupmachines = DASH_GetGroupsMachines(null/* not used */, $db, $groupScope);

            foreach ($groupmachines as $key => $value) {

                $Redisres = $redis->lrange("$groupmachines[$key]", 0, -1);
                if (safe_count($Redisres) > 0) {
                    $CustomerNumber = $Redisres[1];
                    $OrderNumber = $Redisres[2];
                    $OperatingSystem = $Redisres[4];
                    $ServiceTag = $groupmachines[$key];
                    $profileConfig = '';

                    if (stripos($OperatingSystem, $SelectedOS) !== false) {
                        if ($commonProfile === 1) {
                            $profileConfig = $DartCommon;
                        } else {
                            if (stripos($OperatingSystem, '8') !== false) {
                                $profileConfig = $Dart8;
                            } else if (stripos($OperatingSystem, '7') !== false) {
                                $profileConfig = $Dart7;
                            } else if (stripos($OperatingSystem, '10') !== false) {
                                $profileConfig = $Dart10;
                            } else if (stripos($OperatingSystem, 'vista') !== false) {
                                $profileConfig = $Dartvista;
                            } else if (stripos($OperatingSystem, 'xp') !== false) {
                                $profileConfig = $Dartxp;
                            } else {
                                $profileConfig = $DartCommon;
                            }
                        }
                        if ($profileConfig !== '') {
                            $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $idx . '","' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                            $sqlAuditArr = [ $bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, $idx, $Type, $OperatingSystem, $ProfileName, $profileConfig ];

                            $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $idx . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                            if (safe_count($sqlAudit) === 1000) {
                                $redis->select(1);
                                $resID = InsertJobsIntoAudit($sqlAuditQuer);

                                $swdData = array();
                                $swdData["id"] = "$resID";
                                $swdData["bid"] = "$bid";
                                $swdData["indexname"] = "swd_" . date("Y_m");
                                $swdData["logtype"] = "swdcreate";
                                $swdData["customerno"] = $resData[1];
                                $swdData["orderno"] = $resData[2];
                                $swdData["machine"] = $resData[3];
                                $swdData["jobcreatedtime"] = $resData[4];
                                $swdData["selectiontype"] = $resData[5];
                                $swdData["dart"] = $resData[6];
                                $swdData["agentname"] = $resData[7];
                                $swdData["AgentUniqId"] = $resData[8];
                                $swdData["IDX"] = "0";
                                $swdData["JobType"] = $resData[10];
                                $swdData["MachineOs"] = $resData[11];
                                $swdData["ProfileName"] = $resData[12];
                                $swdData["ProfileSequence"] = $resData[13];
                                $swdData["ClientTimeZone"] = "";
                                $swdData["ClientExecutedTime"] = "";
                                $swdData["JobStatus"] = "0";
                                $swdData["DartExecutionProof"] = "";

                                for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                    $val = explode(',', $sqlAudit[$x]);
                                    $JobMsg = $val[13];
                                    $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
                                    if ($ServiceTagSupported === '') {
                                        $ServiceTagSupported = trim($val[3], '"');
                                    } else {
                                        $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                    }
                                    $resID++;
                                }

                                $redis->select(0);
                                unset($sqlAudit);
                                $sqlAudit = array();
                            }
                        } else {
                            if ($ServiceTagNotSupported === '') {
                                $ServiceTagNotSupported = $groupmachines[$key];
                            } else {
                                $ServiceTagNotSupported .= '~~' . $groupmachines[$key];
                            }
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $groupmachines[$key];
                        } else {
                            $ServiceTagNotSupported .= '~~' . $groupmachines[$key];
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $groupmachines[$key];
                    } else {
                        $ServiceTagNotSupported .= '~~' . $groupmachines[$key];
                    }
                }
            }
        }
    } else {
        $Notification = rtrim($Notification, "~");
        $Jobs = explode('~~~~', $Notification);
        $sql = array();
        // $Status = "Fixed";
        for ($i = 0; $i < safe_count($Jobs); $i++) {
            if ($Jobs[$i] === '') {
                continue;
            }
            $Job = explode('~~', $Jobs[$i]);

            $Nid = $Job[0];
            $notiName = $Job[1];
            $Site = $Job[2];
            $ServiceTag = $Job[3];
            // $clientVersion = $Job[4];
            $dartNo = $Job[5];
            $eventTime = $Job[6];
            $Cid = $Job[5];
            // $tid = $Job[8];
            $idx = $Job[6];

            $Redisres = $redis->lrange("$ServiceTag", 0, -1);
            if (safe_count($Redisres) > 0) {
                $CustomerNumber = $Redisres[1];
                $OrderNumber = $Redisres[2];
                $OperatingSystem = $Redisres[4];

                if (safe_count($Jobs) === 1) {
                    $ProgServiceTag = $ServiceTag;
                    $onlineOffline = $Redisres[5];
                }

                $profileConfig = '';

                if (stripos($OperatingSystem, $SelectedOS) !== false) {
                    if ($commonProfile === 1) {
                        $profileConfig = $DartCommon;
                    } else {
                        if (stripos($OS, '8') !== false) {
                            $profileConfig = $Dart8;
                        } else if (stripos($OS, '7') !== false) {
                            $profileConfig = $Dart7;
                        } else if (stripos($OS, '10') !== false) {
                            $profileConfig = $Dart10;
                        } else if (stripos($OS, 'vista') !== false) {
                            $profileConfig = $Dartvista;
                        } else if (stripos($OS, 'xp') !== false) {
                            $profileConfig = $Dartxp;
                        }
                    }
                    if ($profileConfig !== '') {
                        $Selectiontype = 'Machine : ' . $ServiceTag;
                        $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '",0,"' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                        $sqlAuditArr = [ $bid, $CustomerNumber, $OrderNumber, $ServiceTag, $currTime, $Selectiontype, $DartNumber, $agentName, $agentUniqId, 0, $Type, $OperatingSystem, $ProfileName, $profileConfig ];
                        $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',0,' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                        $sql[] = $notiName . ',' . $Site . ',' . $ServiceTag . ',Completed,' . $dartNo . ',' . $eventTime . ',' . $ProfileName;
                        if (safe_count($sqlAudit) === 1000) {
                            $redis->select(1);
                            $resID = InsertJobsIntoAudit($sqlAuditQuer);
                            InsertJobsIntoTicketEvents($resID, $ServiceTag, $Site, $Nid, $Cid, $db);
                            for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                $val = explode(',', $sqlAudit[$x]);
                                $JobMsg = $val[13];
                                $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
                                if ($ServiceTagSupported === '') {
                                    $ServiceTagSupported = trim($val[3], '"');
                                } else {
                                    $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                }
                                $resID++;
                            }

                            $redis->select(0);
                            unset($sqlAudit);
                            $sqlAudit = array();
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $ServiceTag;
                        } else {
                            $ServiceTagNotSupported .= '~~' . $ServiceTag;
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $ServiceTag;
                    } else {
                        $ServiceTagNotSupported .= '~~' . $ServiceTag;
                    }
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $ServiceTag;
                } else {
                    $ServiceTagNotSupported .= '~~' . $ServiceTag;
                }
            }
        }
    }

    $redis->select(1);
    $duplicateJob = 0;
    if (safe_count($sqlAudit) !== 0) {
        foreach ($sqlAuditQuer as $key => $val) {
            $auditCheck = validateJobsIntoAudit($sqlAuditQuer[$key]);
            if ($auditCheck) {
                $duplicateJob++;
            } else {
                $resID = InsertJobsIntoAuditLatest($sqlAuditArr);

                $expData = $sqlAuditArr;
                $JobMsg = $expData[13];
                $redis->rpush(trim($expData[3], '"') . ":" . $resID, trim($expData[3], '"'), $resID, trim($JobMsg, '")'), trim($expData[9], '"'), trim($expData[8], '"'), $DartNumber);
                if ($ServiceTagSupported == '') {
                    $ServiceTagSupported = trim($expData[3], '"');
                } else {
                    $ServiceTagSupported .= '~~' . trim($expData[3], '"');
                }

                $resInsAudit = $sqlAudit[$key];
                $resDattrimed = trim(trim($resInsAudit, "("), ")");
                $repldVal = str_replace('"', "", $resDattrimed);
                $resData = explode(",", $repldVal);
                $swdData = array();
                $swdData["id"] = "$resID";
                $swdData["bid"] = "$resData[0]";
                $swdData["indexname"] = "swd_" . date("Y_m");
                $swdData["logtype"] = "swdcreate";
                $swdData["customerno"] = $resData[1];
                $swdData["orderno"] = $resData[2];
                $swdData["machine"] = $resData[3];
                $swdData["jobcreatedtime"] = $resData[4];
                $swdData["selectiontype"] = $resData[5];
                $swdData["dart"] = $resData[6];
                $swdData["agentname"] = $resData[7];
                $swdData["AgentUniqId"] = $resData[8];
                $swdData["IDX"] = "0";
                $swdData["JobType"] = $resData[10];
                $swdData["MachineOs"] = $resData[11];
                $swdData["ProfileName"] = $resData[12];
                $swdData["ProfileSequence"] = $resData[13];
                $swdData["ClientTimeZone"] = "";
                $swdData["ClientExecutedTime"] = "";
                $swdData["JobStatus"] = "0";
                $swdData["DartExecutionProof"] = "";
            }
        }
    }
    $redis->close();
    $GroupName = !empty($GroupName) ? $GroupName : $searchValue;
    $action = "Execute - " . $Variable . "( DART-" . $DartNumber . ")";
    create_auditLog('Troubleshooter', $action, 'Success', $_REQUEST, $GroupName);

    $notifyRes = '';
    if (intVal($NotificationWindow) === 1) {
        $notifyRes = updateNocStatus($sql);
    }
    if ($duplicateJob > 0) {
        echo $ServiceTagSupported . '##' . $ServiceTagNotSupported . '##' . $bid . '##' . $ProgServiceTag . '##' . $searchtype . '##' . $onlineOffline . '##Duplicates';
    } else {
        echo $ServiceTagSupported . '##' . $ServiceTagNotSupported . '##' . $bid . '##' . $ProgServiceTag . '##' . $searchtype . '##' . $onlineOffline . '##' . $notifyRes;
    }
    // @todo replace answer to json
}

function AddRemoteJobsNew_func()
{
    include_once '../software_distribution/software_distribution.php';

    $db = pdo_connect();

    global $redis_url;
    global $redis_port;
    global $redis_pwd;
    global $TS_restricted;

    $redis = RedisLink::connect(true);

    $redis->select(0);

    $agentName = $_SESSION["user"]["logged_username"];
    $agentUniqId = $_SESSION["user"]["adminEmail"];

    if (is_null($agentUniqId) || $agentUniqId === '') {
        $agentUniqId = $_SESSION["user"]["adminid"];
    }

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];

    $SelectedOS = url::requestToText('OS');
    $Type = url::requestToText('Jobtype');
    $ShortDesc = url::requestToText('shortDesc');
    $ProfileName = url::requestToText('ProfileName');
    $DartNumber = url::requestToText('Dart');
    $Variable = url::requestToText('variable');
    $NotificationWindow = url::requestToInt('NotificationWindow');
    $GroupName = url::requestToText('GroupName');
    $Notification = strip_tags(trim($_SESSION['notifyselArr']));

    $ProgServiceTag = '';
    $onlineOffline = 'Offline';

    if ($NotificationWindow === 1) {
        $Type = 'Notification';
    }

    if (($searchtype == 'Sites' || $searchtype == 'Groups') && url::requestToText('ProfileName') === 'Uninstall Tech Services') {
        echo "NotAvaiableForSite";
        exit;
    }

    $sqlQry = '';

    $bid = 0;
    $idx = 0;

    $sqlAudit = array();
    $currTime = time();

    $Dartxp = '';
    $Dartvista = '';
    $Dart7 = '';
    $Dart8 = '';
    $Dart10 = '';
    $DartCommon = '';
    $commonProfile = 0;

    $ServiceTagNotSupported = '';
    $ServiceTagSupported = '';
    $Selectiontype = '';
    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
        $Selectiontype = 'Machine : ' . $searchValue;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $Selectiontype = 'Site : ' . $searchValue;
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $Selectiontype = 'Group : ' . $GroupName;
    }

    if ($SelectedOS === 'windows' && $Type === 'Interactive') {
        $Profile = $_SESSION["user"]["profileName"];
    } else if ($SelectedOS === 'os x' && $Type === 'Interactive') {
        $Profile = $_SESSION["user"]["macprofileName"];
    } else if ($SelectedOS === 'windows' && $Type === 'Notification') {
        $Profile = $_SESSION["user"]["profileName"];
    } else if ($Type === 'Software Distribution') {

        $packageType = url::issetInRequest('package-type') && in_array(url::requestToAny('package-type'), ['execute', 'distribute']) ? url::requestToAny('package-type') : null;
        $details = requestDetails($DartNumber, null, $_SESSION['user']['dashboardLogin']);
        $configData = requestConfiguration($DartNumber, $packageType, $_SESSION['user']['dashboardLogin']);

        if (!$details && !$configData) {
            exit('Failed');
        }

        $packageName = $details['name'];
        $packageDesc = $details['name'];
        $sourceType = '2';
        $configDetail = $configData;

        $androidtype = 0;

        if (trim($sourceType) == '4') {
            $androidtype = 2;
        } else {
            $androidtype = 1;
        }

        $batchinsert = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Jobid (agentUniqId, packageName, SelectionType, createdtime) VALUES (?,?,?,?)");
        $batchinsert->execute([$agentUniqId, $packageName, $Selectiontype, $currTime]);
        $bid = $db->lastInsertId();

        $ProfileName = "SWD : " . $packageDesc;
    } else if ($SelectedOS === 'os x') {
        $Profile = $_SESSION["user"]["macprofileName"];
    } else if ($SelectedOS === 'linux') {
        $Profile = $_SESSION["user"]["lnxprofileName"];
    } else if ($SelectedOS === "RLinux") {
        $Profile = $_SESSION["user"]["readynetProfileName"];
    }

    if ($Type === 'Interactive' || $Type === 'Notification') {

        $varvalue = url::requestToText('variable');
        $OS = url::requestToText('ProfileOS');

        if (intval($DartNumber) === 43) {
            $DartCommon = "VarName=S00043SilentUninstall;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=43;VarScope=1;";
            $commonProfile = 1;
        } else if (intval($DartNumber) === 256) {
            $DartCommon = "VarName=" . $Variable . ";VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=256;VarScope=1;";
            $commonProfile = 1;
        } else if (intval($DartNumber) === 286) {
            if ($OS == 'common') {
                $DartCommon = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                $commonProfile = 1;
            } else {
                if (stripos($OS, 'xp') !== false) {
                    $Dartxp = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, 'vista') !== false) {
                    $Dartvista = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, '7') !== false) {
                    $Dart7 = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, '8') !== false) {
                    $Dart8 = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
                if (stripos($OS, '10') !== false) {
                    $Dart10 = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                }
            }
        }
    } else if (intval($DartNumber) === 275) {
        $commonProfile = 1;
        $DartCommon = "VarName=constS00275WmiMethod;VarType=2;VarVal=" . $Variable . ";Action=SET;DartNum=275;VarScope=1;#;NextConf;#VarName=S00286ProfileName;VarType=2;VarVal=WMI;Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
    } else if ($Type === 'Software Distribution') {
        $commonProfile = 1;
        if ($SelectedOS === 'windows' || $SelectedOS === 'os x' || $SelectedOS === 'linux') {
            $DartCommon = "VarName=S00288IndividualPatches;VarType=2;VarVal=" . "SWD-" . $DartNumber . ";Action=SET;DartNum=288;VarScope=1;#;NextConf;#VarName=S00288RunNowButtonnew;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=288;VarScope=1;";
            $DartNumber = 288;
        }
    } else if ($Type === 'Custom Profile') {
        $commonProfile = 1;
        $DartCommon = "VarName=S00286RunTimeConfig;VarType=2;VarVal=" . $ProfileName . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
    }

    if (intVal($NotificationWindow) !== 1) {
        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $ServiceTag = $searchValue;
            $ProgServiceTag = $ServiceTag;
            $Redisres = $redis->lrange("$searchValue", 0, -1);
            if (safe_count($Redisres) > 0) {
                $CustomerNumber = $Redisres[1];
                $OrderNumber = $Redisres[2];
                $OperatingSystem = $Redisres[4];
                $onlineOffline = $Redisres[5];
                $profileConfig = '';

                if (stripos($OperatingSystem, $SelectedOS) !== false) {
                    if ($commonProfile === 1) {
                        $profileConfig = $DartCommon;
                    } else {
                        if (stripos($OperatingSystem, '8') !== false) {
                            $profileConfig = $Dart8;
                        } else if (stripos($OperatingSystem, '7') !== false) {
                            $profileConfig = $Dart7;
                        } else if (stripos($OperatingSystem, '10') !== false) {
                            $profileConfig = $Dart10;
                        } else if (stripos($OperatingSystem, 'vista') !== false) {
                            $profileConfig = $Dartvista;
                        } else if (stripos($OperatingSystem, 'xp') !== false) {
                            $profileConfig = $Dartxp;
                        }
                    }
                    if ($Type === 'Custom Profile') {
                        $ProfileName = explode('DartNo=269', $ProfileName)[0];
                    }

                    if ($profileConfig !== '') {
                        $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $idx . '","' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                        $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $idx . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $ServiceTag;
                        } else {
                            $ServiceTagNotSupported .= '~~' . $ServiceTag;
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $ServiceTag;
                    } else {
                        $ServiceTagNotSupported .= '~~' . $ServiceTag;
                    }
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $ServiceTag;
                } else {
                    $ServiceTagNotSupported .= '~~' . $ServiceTag;
                }
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            $siteScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $sitemachines = DASH_GetMachinesSites($key, $db, $siteScope);

            foreach ($sitemachines as $key => $value) {
                $Redisres = $redis->lrange("$sitemachines[$key]", 0, -1);
                if (safe_count($Redisres) > 0) {
                    $CustomerNumber = $Redisres[1];
                    $OrderNumber = $Redisres[2];
                    $OperatingSystem = $Redisres[4];
                    $ServiceTag = $sitemachines[$key];
                    $profileConfig = '';

                    if (stripos($OperatingSystem, $SelectedOS) !== false) {
                        if ($commonProfile === 1) {
                            $profileConfig = $DartCommon;
                        } else {
                            if (stripos($OperatingSystem, '8') !== false) {
                                $profileConfig = $Dart8;
                            } else if (stripos($OperatingSystem, '7') !== false) {
                                $profileConfig = $Dart7;
                            } else if (stripos($OperatingSystem, '10') !== false) {
                                $profileConfig = $Dart10;
                            } else if (stripos($OperatingSystem, 'vista') !== false) {
                                $profileConfig = $Dartvista;
                            } else if (stripos($OperatingSystem, 'xp') !== false) {
                                $profileConfig = $Dartxp;
                            }
                        }
                        if ($profileConfig !== '') {
                            $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $idx . '","' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                            $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $idx . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                            if (safe_count($sqlAudit) === 1000) {
                                $redis->select(1);
                                $resID = InsertJobsIntoAudit($sqlAuditQuer);

                                $swdData = array();
                                $swdData["id"] = "$resID";
                                $swdData["bid"] = "$bid";
                                $swdData["indexname"] = "swd_" . date("Y_m");
                                $swdData["logtype"] = "swdcreate";
                                $swdData["customerno"] = $resData[1];
                                $swdData["orderno"] = $resData[2];
                                $swdData["machine"] = $resData[3];
                                $swdData["jobcreatedtime"] = $resData[4];
                                $swdData["selectiontype"] = $resData[5];
                                $swdData["dart"] = $resData[6];
                                $swdData["agentname"] = $resData[7];
                                $swdData["AgentUniqId"] = $resData[8];
                                $swdData["IDX"] = "0";
                                $swdData["JobType"] = $resData[10];
                                $swdData["MachineOs"] = $resData[11];
                                $swdData["ProfileName"] = $resData[12];
                                $swdData["ProfileSequence"] = $resData[13];
                                $swdData["ClientTimeZone"] = "";
                                $swdData["ClientExecutedTime"] = "";
                                $swdData["JobStatus"] = "0";
                                $swdData["DartExecutionProof"] = "";

                                for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                    $val = explode(',', $sqlAudit[$x]);
                                    $JobMsg = $val[13];
                                    $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
                                    if ($ServiceTagSupported === '') {
                                        $ServiceTagSupported = trim($val[3], '"');
                                    } else {
                                        $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                    }
                                    $resID++;
                                }

                                $redis->select(0);
                                unset($sqlAudit);
                                $sqlAudit = array();
                            }
                        } else {
                            if ($ServiceTagNotSupported === '') {
                                $ServiceTagNotSupported = $sitemachines[$key];
                            } else {
                                $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                            }
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $sitemachines[$key];
                        } else {
                            $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $sitemachines[$key];
                    } else {
                        $ServiceTagNotSupported .= '~~' . $sitemachines[$key];
                    }
                }
            }
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {

            $groupScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $groupmachines = DASH_GetGroupsMachines($key, $db, $groupScope);

            foreach ($groupmachines as $key => $value) {

                $Redisres = $redis->lrange("$groupmachines[$key]", 0, -1);
                if (safe_count($Redisres) > 0) {
                    $CustomerNumber = $Redisres[1];
                    $OrderNumber = $Redisres[2];
                    $OperatingSystem = $Redisres[4];
                    $ServiceTag = $groupmachines[$key];
                    $profileConfig = '';

                    if (stripos($OperatingSystem, $SelectedOS) !== false) {
                        if ($commonProfile === 1) {
                            $profileConfig = $DartCommon;
                        } else {
                            if (stripos($OperatingSystem, '8') !== false) {
                                $profileConfig = $Dart8;
                            } else if (stripos($OperatingSystem, '7') !== false) {
                                $profileConfig = $Dart7;
                            } else if (stripos($OperatingSystem, '10') !== false) {
                                $profileConfig = $Dart10;
                            } else if (stripos($OperatingSystem, 'vista') !== false) {
                                $profileConfig = $Dartvista;
                            } else if (stripos($OperatingSystem, 'xp') !== false) {
                                $profileConfig = $Dartxp;
                            }
                        }
                        if ($profileConfig !== '') {
                            $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $idx . '","' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                            $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $idx . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                            if (safe_count($sqlAudit) === 1000) {
                                $redis->select(1);
                                $resID = InsertJobsIntoAudit($sqlAuditQuer);

                                $swdData = array();
                                $swdData["id"] = "$resID";
                                $swdData["bid"] = "$bid";
                                $swdData["indexname"] = "swd_" . date("Y_m");
                                $swdData["logtype"] = "swdcreate";
                                $swdData["customerno"] = $resData[1];
                                $swdData["orderno"] = $resData[2];
                                $swdData["machine"] = $resData[3];
                                $swdData["jobcreatedtime"] = $resData[4];
                                $swdData["selectiontype"] = $resData[5];
                                $swdData["dart"] = $resData[6];
                                $swdData["agentname"] = $resData[7];
                                $swdData["AgentUniqId"] = $resData[8];
                                $swdData["IDX"] = "0";
                                $swdData["JobType"] = $resData[10];
                                $swdData["MachineOs"] = $resData[11];
                                $swdData["ProfileName"] = $resData[12];
                                $swdData["ProfileSequence"] = $resData[13];
                                $swdData["ClientTimeZone"] = "";
                                $swdData["ClientExecutedTime"] = "";
                                $swdData["JobStatus"] = "0";
                                $swdData["DartExecutionProof"] = "";

                                for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                    $val = explode(',', $sqlAudit[$x]);
                                    $JobMsg = $val[13];
                                    $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
                                    if ($ServiceTagSupported === '') {
                                        $ServiceTagSupported = trim($val[3], '"');
                                    } else {
                                        $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                    }
                                    $resID++;
                                }

                                $redis->select(0);
                                unset($sqlAudit);
                                $sqlAudit = array();
                            }
                        } else {
                            if ($ServiceTagNotSupported === '') {
                                $ServiceTagNotSupported = $groupmachines[$key];
                            } else {
                                $ServiceTagNotSupported .= '~~' . $groupmachines[$key];
                            }
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $groupmachines[$key];
                        } else {
                            $ServiceTagNotSupported .= '~~' . $groupmachines[$key];
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $groupmachines[$key];
                    } else {
                        $ServiceTagNotSupported .= '~~' . $groupmachines[$key];
                    }
                }
            }
        }
    } else {
        $Notification = rtrim($Notification, "~");
        $Jobs = explode('~~~~', $Notification);

        $sql = array();
        $Status = "Fixed";
        for ($i = 0; $i < safe_count($Jobs); $i++) {
            if ($Jobs[$i] === '') {
                continue;
            }
            $Job = explode('~~', $Jobs[$i]);

            $Nid = $Job[0];
            $notiName = $Job[1];
            $Site = $Job[2];
            $ServiceTag = $Job[3];
            $clientVersion = $Job[4];
            $dartNo = $Job[5];
            $eventTime = $Job[6];
            $Cid = $Job[5];
            $tid = $Job[8];
            $idx = $Job[6];

            $Redisres = $redis->lrange("$ServiceTag", 0, -1);

            if (safe_count($Redisres) > 0) {
                $CustomerNumber = $Redisres[1];
                $OrderNumber = $Redisres[2];
                $OperatingSystem = $Redisres[4];

                if (safe_count($Jobs) === 1) {
                    $ProgServiceTag = $ServiceTag;
                    $onlineOffline = $Redisres[5];
                }

                $profileConfig = '';

                if (stripos($OperatingSystem, $SelectedOS) !== false) {
                    if ($commonProfile === 1) {
                        $profileConfig = $DartCommon;
                    } else {
                        if (stripos($OS, '8') !== false) {
                            $profileConfig = $Dart8;
                        } else if (stripos($OS, '7') !== false) {
                            $profileConfig = $Dart7;
                        } else if (stripos($OS, '10') !== false) {
                            $profileConfig = $Dart10;
                        } else if (stripos($OS, 'vista') !== false) {
                            $profileConfig = $Dartvista;
                        } else if (stripos($OS, 'xp') !== false) {
                            $profileConfig = $Dartxp;
                        }
                    }

                    if ($profileConfig !== '') {
                        $Selectiontype = 'Machine : ' . $ServiceTag;
                        $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '",0,"' . $Type . '","' . $OperatingSystem . '","' . $ProfileName . '","' . $profileConfig . '")';
                        $sqlAuditQuer[] = $bid . ',' . $CustomerNumber . ',' . $OrderNumber . ',' . $ServiceTag . ',' . $currTime . ',' . $Selectiontype . ',' . $DartNumber . ',' . $agentName . ',' . $agentUniqId . ',' . $Nid . ',' . $Type . ',' . $OperatingSystem . ',' . $ProfileName . ',' . $profileConfig;
                        $sql[] = $notiName . ',' . $Site . ',' . $ServiceTag . ',Completed,' . $dartNo . ',' . $eventTime . ',' . $ProfileName;
                        if (safe_count($sqlAudit) === 1000) {
                            $redis->select(1);
                            $resID = InsertJobsIntoAudit($sqlAuditQuer);
                            InsertJobsIntoTicketEvents($resID, $ServiceTag, $Site, $Nid, $Cid, $db);

                            for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                                $val = explode(',', $sqlAudit[$x]);
                                $JobMsg = $val[13];
                                $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
                                if ($ServiceTagSupported === '') {
                                    $ServiceTagSupported = trim($val[3], '"');
                                } else {
                                    $ServiceTagSupported .= '~~' . trim($val[3], '"');
                                }
                                $resID++;
                            }

                            $redis->select(0);
                            unset($sqlAudit);
                            $sqlAudit = array();
                        }
                    } else {
                        if ($ServiceTagNotSupported === '') {
                            $ServiceTagNotSupported = $ServiceTag;
                        } else {
                            $ServiceTagNotSupported .= '~~' . $ServiceTag;
                        }
                    }
                } else {
                    if ($ServiceTagNotSupported === '') {
                        $ServiceTagNotSupported = $ServiceTag;
                    } else {
                        $ServiceTagNotSupported .= '~~' . $ServiceTag;
                    }
                }
            } else {
                if ($ServiceTagNotSupported === '') {
                    $ServiceTagNotSupported = $ServiceTag;
                } else {
                    $ServiceTagNotSupported .= '~~' . $ServiceTag;
                }
            }
        }
    }

    $redis->select(1);
    if (safe_count($sqlAudit) !== 0) {
        $resID = InsertJobsIntoAudit($sqlAuditQuer);

        $resInsAudit = $sqlAudit[0];
        $resDattrimed = trim(trim($resInsAudit, "("), ")");
        $repldVal = str_replace('"', "", $resDattrimed);
        $resData = explode(",", $repldVal);
        $swdData = array();
        $swdData["id"] = "$resID";
        $swdData["bid"] = "$resData[0]";
        $swdData["indexname"] = "swd_" . date("Y_m");
        $swdData["logtype"] = "swdcreate";
        $swdData["customerno"] = $resData[1];
        $swdData["orderno"] = $resData[2];
        $swdData["machine"] = $resData[3];
        $swdData["jobcreatedtime"] = $resData[4];
        $swdData["selectiontype"] = $resData[5];
        $swdData["dart"] = $resData[6];
        $swdData["agentname"] = $resData[7];
        $swdData["AgentUniqId"] = $resData[8];
        $swdData["IDX"] = "0";
        $swdData["JobType"] = $resData[10];
        $swdData["MachineOs"] = $resData[11];
        $swdData["ProfileName"] = $resData[12];
        $swdData["ProfileSequence"] = $resData[13];
        $swdData["ClientTimeZone"] = "";
        $swdData["ClientExecutedTime"] = "";
        $swdData["JobStatus"] = "0";
        $swdData["DartExecutionProof"] = "";

        for ($x = 0; $x < safe_count($sqlAudit); $x++) {
            $val = explode(',', $sqlAudit[$x]);
            $JobMsg = $val[13];
            $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($JobMsg, '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
            if ($ServiceTagSupported === '') {
                $ServiceTagSupported = trim($val[3], '"');
            } else {
                $ServiceTagSupported .= '~~' . trim($val[3], '"');
            }
            $resID++;
        }
    }
    $redis->close();

    $notifyRes = '';
    if (intVal($NotificationWindow) === 1) {
        $notifyRes = updateNocStatus($sql);
    }
    echo $ServiceTagSupported . '##' . $ServiceTagNotSupported . '##' . $bid . '##' . $ProgServiceTag . '##' . $searchtype . '##' . $onlineOffline . '##' . $notifyRes;
}

function updateNocStatus($sql)
{

    $indexName = 'notification';
    $user = $_SESSION['user']['username'];

    foreach ($sql as $key => $val) {

        $values = explode(',', $val);
        $nocName = $values[0];
        $machine = $values[2];
        $eventTime = $values[5];
        $date = date('Y-m-d H:i:s', $eventTime);
        $fromdate = strtotime($date);
        $todate = $fromdate + 86400;
        $status = $values[3];
        $sol = $values[6];
    }

    $query = '{
            "query": {
                "bool": {

                "must": [
                    {
                      "match": {
                        "NotificationName.keyword": "' . $nocName . '"
                      }
                    },
                    {
                        "match": {
                            "machine.keyword": "' . $machine . '"
                        }
                    }
                ],
                "filter": [{
                    "range": {
                        "ctime": {
                            "gte": "' . $fromdate . '",
                            "lte": "' . $todate . '"
                        }
                    }
                }]
              }
            },
            "script": {
              "inline": "ctx._source.Status= \"' . $status . '\";ctx._source.solution= \"' . $sol . '\";ctx._source.username= \"' . $user . '\";"
            }
          }';

    updateByIndex($query, $indexName);
    return "success";
}

function validateJobsIntoAudit($sqlstr)
{
    $sqldata = explode(',', $sqlstr);
    $MachineTag = $sqldata[3];
    $ProfileName = $sqldata[12];

    $pdo = pdo_connect();
    $stmt = $pdo->prepare('SELECT * FROM ' . $GLOBALS['PREFIX'] . 'communication.Audit WHERE MachineTag = ? and ProfileName = ? and JobStatus = ?');
    $stmt->execute([$MachineTag, $ProfileName, 0]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        return 1;
    } else {
        return 0;
    }
}

function InsertJobsIntoAudit($sql)
{
    $db = NanoDB::connect();
    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($sql as $key => $val) {
        $vals = explode(',', $val);
        $sqlQry->execute($vals);
    }
    $lastInsertId = $db->lastInsertId();
    return $lastInsertId;
}

function InsertJobsIntoAuditLatest($sql)
{
    $db = NanoDB::connect();
    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $vals = $sql;
    $sqlQry->execute($vals);
    $lastInsertId = $db->lastInsertId();

    return $lastInsertId;
}

function InsertJobsIntoTicketEvents($resID, $ServiceTag, $Site, $Nid, $Cid, $db)
{

    $sql = $db->prepare("update  " . $GLOBALS['PREFIX'] . "event.ticketEvents set audit_Id=? where siteName=? and machineName=? and consoleId=? and nid=?");
    $sql->execute([$resID, $Site, $ServiceTag, $Cid, $Nid]);
    $rest = $db->lastInsertId();
}

function getMachineOS_funcCall()
{
    // global $redis_url;
    // global $redis_port;
    // global $redis_pwd;
    $machines = null;

    try {
        $redis = RedisLink::connect();
        // $redis->connect($redis_url, $redis_port);
        // $redis->auth($redis_pwd);
        // $redis->select(0);

        // $db = NanoDB::connect();
        $key = '';

        $searchtype = url::postToText('searchType', $_SESSION["searchType"]);
        $searchValue = url::postToText('searchValue', $_SESSION["searchValue"]);
        $cType = $_SESSION["cType"];
        $machinelist = "";
        $searchManual = "";
        if ($searchtype == 'Service Tag' || $searchtype == 'ServiceTag' || $searchtype == 'Host Name') {
            $selectiontype = 'Machine : ' . $searchValue;
            $Redisres = $redis->lrange($searchValue, 0, -1); // get all elements in a list
            if (safe_count($Redisres) > 0) {
                $OperatingSystem = $Redisres[4];
                $versionNo = $Redisres[3];
                if (trim($OperatingSystem) === '') {
                    $OperatingSystem = "NULL";
                }
                if ($machinelist === '') {
                    $machinelist = $searchValue . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
                } else {
                    $machinelist .= '~~~~' . $searchValue . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
                }
            } else {
                if ($searchManual === '') {
                    $machinelist = "'" . $searchValue . "'";
                } else {
                    $machinelist .= ",'" . $searchValue . "'";
                }
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            if ($searchValue == 'All') {
                $selectiontype = 'Site : ' . $searchValue;
            } else {
                $selectiontype = 'Site : ' . $searchValue;
                $siteScope = UTIL_GetSiteScope(NanoDB::connect(), $searchValue, $searchtype);
                $machines = DASH_GetMachinesSites($key,  NanoDB::connect(), $siteScope);
                foreach ($machines as $key => $value) {
                    $Redisres = $redis->lrange("$machines[$key]", 0, -1);
                    if (safe_count($Redisres) > 0) {
                        $OperatingSystem = $Redisres[4];
                        $ServiceTag = $machines[$key];
                        $versionNo = $Redisres[3];
                        if (trim($OperatingSystem) === '') {
                            $OperatingSystem = "NULL";
                        }
                        if ($machinelist === '') {
                            $machinelist = $ServiceTag . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
                        } else {
                            $machinelist .= '~~~~' . $ServiceTag . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
                        }
                    }
                }
            }
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == 'All') {
                $selectiontype = 'Group : ' . $searchValue;
            } else {
                $selectiontype = 'Group : ' . $searchValue;
                $groupScope = UTIL_GetSiteScope(NanoDB::connect(), $searchValue, $searchtype);
                $machines = DASH_GetGroupsMachines($key,  NanoDB::connect(), $groupScope);

                foreach ($machines as $key => $value) {
                    $Redisres = $redis->lrange("$machines[$key]", 0, -1);
                    if (safe_count($Redisres) > 0) {
                        $OperatingSystem = $Redisres[4];
                        $ServiceTag = $machines[$key];
                        $versionNo = $Redisres[3];
                        if (trim($OperatingSystem) === '') {
                            $OperatingSystem = "NULL";
                        }
                        if ($machinelist === '') {
                            $machinelist = $ServiceTag . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
                        } else {
                            $machinelist .= '~~~~' . $ServiceTag . '~~' . 'Group' . '~~' . $versionNo . '~~' . $OperatingSystem;
                        }
                    }
                }
            }
        }
        return json_encode([
            "OperatingSystem" => $OperatingSystem ?: "", // use
            "machinelist" => $machinelist, // not used
            "cType" => $cType, // not used
            "selectiontype" => $selectiontype ?: "", // use
            "searchtype" => $searchtype, // not used

            // debug
            "_searchValue" => $searchValue,
            "_machines" => $machines,
        ]);
        // $OperatingSystem . '####' . $machinelist . '####' . $cType . '####' . $selectiontype . '####' . $searchtype;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        return json_encode(["error" => $exc]);
    }
}

function get_all_search_list($searchType)
{

    $username = $_SESSION['user']['username'];
    $db = pdo_connect();
    $sites = "";
    $hosts = "";
    if ($searchType == 'Sites' || $searchType == 'Site') {

        $sql = $db->prepare("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=?");
        $sql->execute([$username]);
        $sqlRes = $sql->fetchAll();

        foreach ($sqlRes as $key => $value) {
            $sites .= "'" . safe_addslashes($value['name']) . "',";
        }
        $searchVal = rtrim($sites, ',');
    } else if ($searchType == 'Groups' || $searchType == 'Group') {

        $wh = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where (username = ? or global=1) and style=2";

        $sql = $db->prepare("select DISTINCT C.host from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq and GP.mgroupuniq = M.mgroupuniq and M.name IN ($wh) group by C.host");
        $sql->execute([$username]);
        $resultSite = $sql->fetchAll();

        foreach ($resultSite as $row) {
            $hosts .= "'" . $row['host'] . "',";
        }
        $searchVal = rtrim($hosts, ',');
    } else {
        $groupid = explode(',', getViewId($db));

        $in = str_repeat('?,', safe_count($groupid) - 1) . '?';
        $sql = $db->prepare("select c.host as host from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,core.MachineGroupMap mgm,core.Census c where mg.mgroupuniq=mgm.mgroupuniq and mgm.censusuniq=c.censusuniq and mg.mgroupid in ($in) and c.host != '0' group by c.host");
        $sql->execute([$groupid]);
        $res = $sql->fetchAll();

        foreach ($res as $row) {
            $hosts .= "'" . $row['host'] . "',";
        }
        $searchVal = rtrim($hosts, ',');
    }
    return $searchVal;
}

function getOsVarValues_func()
{
    $db = pdo_connect();

    $shortDesc = url::requestToText('shortDesc');
    $cNode = $_SESSION["cNode"];
    $profile = $_SESSION["user"]["profileName"];

    $table = $GLOBALS['PREFIX'] . 'event.' . $profile;
    $sqlQry = $db->prepare("select varValue,OS from $table where shortDesc =?");
    $sqlQry->execute([$shortDesc]);
    $resQry = $sqlQry->fetchAll();

    $varvaluexp = '';
    $varvaluevista = '';
    $varvalue7 = '';
    $varvalue8 = '';
    $varvalue10 = '';
    $varvaluecommon = '';
    $NewLineDl = '#;NewLine;#';
    $commonProfile = 0;

    foreach ($resQry as $key => $value) {

        $varvalue = $value['varValue'];
        $OS = $value['OS'];
        $sequence = $values['sequence'];

        if ($OS == 'common') {
            if ($cNode === 'NODEJS') {
                $varvaluecommon = $varvalue;
            } else if ($cNode === 'NODEPHP') {
                $varvaluecommon = $varvalue . $NewLineDl . createSequence($sequence);
            }
            $commonProfile = 1;
        } else {
            if (strpos($OS, 'xp') !== false) {
                if ($cNode === 'NODEJS') {
                    $varvaluexp = $varvalue;
                } else if ($cNode === 'NODEPHP') {
                    $varvaluexp = $varvalue . $NewLineDl . createSequence($sequence);
                }
            }
            if (strpos($OS, 'vista') !== false) {
                if ($cNode === 'NODEJS') {
                    $varvaluevista = $varvalue;
                } else if ($cNode === 'NODEPHP') {
                    $varvaluevista = $varvalue . $NewLineDl . createSequence($sequence);
                }
            }
            if (strpos($OS, '7') !== false) {
                if ($cNode === 'NODEJS') {
                    $varvalue7 = $varvalue;
                } else if ($cNode === 'NODEPHP') {
                    $varvalue7 = $varvalue . $NewLineDl . createSequence($sequence);
                }
            }
            if (strpos($OS, '8') !== false) {
                if ($cNode === 'NODEJS') {
                    $varvalue8 = $varvalue;
                } else if ($cNode === 'NODEPHP') {
                    $varvalue8 = $varvalue . $NewLineDl . createSequence($sequence);
                }
            }
            if (strpos($OS, '10') !== false) {
                if ($cNode === 'NODEJS') {
                    $varvalue10 = $varvalue;
                } else if ($cNode === 'NODEPHP') {
                    $varvalue10 = $varvalue . $NewLineDl . createSequence($sequence);
                }
            }
        }
    }
    echo $varvaluexp . '##' . $varvaluevista . '##' . $varvalue7 . '##' . $varvalue8 . '##' . $varvalue10 . '##' . $varvaluecommon . '##' . $commonProfile;
}

function createSequence($sequence)
{

    $db = pdo_connect();

    $sqlDetails = $db->prepare("select CM.DART,CM.Description,CD.Variable,CD.VarType,CD.VarValue from ConfigurationMaster CM, ConfigurationDetails CD  where CM.id IN  (282,?)  and CM.id=CD.cid order by FIELD(CD.cid,?)");
    $sqlDetails->execute([$sequence]);
    $resDetails = $sqlDetails->fetchAll();

    $glDartNo = "";
    $confStr = "";
    $NewLineDl = '#;NewLine;#';
    foreach ($resDetails as $value) {

        if ($glDartNo == $value['DART']) {
            $confStr .= '&&&' . $NewLineDl;
        } elseif ($glDartNo != '') {
            $confStr .= '$$$' . $NewLineDl;
        }

        $VarValue = preg_replace("/(\r\n|\n|\r|\t)/i", $NewLineDl, $value['VarValue']);

        if ($glDartNo == '' || $glDartNo != $value['DART']) {

            $confStr .= 'DartNo=' . $value['DART'] . '(' . $value['Description'] . ')' . $NewLineDl;
        }

        $confStr .= 'VarName=' . $value['Variable'] . $NewLineDl;
        $confStr .= 'VarType=' . $value['VarType'] . $NewLineDl;
        if ($i == $j + 1) {
            $confStr .= 'VarVal=' . $VarValue;
        } else {
            $confStr .= 'VarVal=' . $VarValue . $NewLineDl;
        }

        $glDartNo = $value['DART'];
        $j++;
    }
    return trim($confStr);
}

function getAndroidVarValues_func()
{
    $db = pdo_connect();

    $shortDesc = url::requestToText('shortDesc');

    $profile = strip_tags($_SESSION["user"]["andprofileName"]);

    $table = $GLOBALS['PREFIX'] . 'event.' . $profile;
    $sqlQry = $db->prepare("select varValue from $table where shortDesc =?");
    $sqlQry->execute([$shortDesc]);
    $resQry = $sqlQry->fetch();

    echo $resQry['varValue'];
}

function getMacVarValues_func()
{
    $db = pdo_connect();

    $shortDesc = url::requestToText('shortDesc');

    $profile = $_SESSION["user"]["macprofileName"];

    $table = $GLOBALS['PREFIX'] . 'event.' . $profile;
    $sqlQry = $db->prepare("select varValue from $table where shortDesc =?");
    $sqlQry->execute([$shortDesc]);
    $resQry = $sqlQry->fetch();

    echo $resQry['varValue'];
}

function updateNocStatusGrp($sql)
{

    $db = pdo_connect();
    $msg = 'success';
    $agentId = $_SESSION["user"]["adminEmail"];
    $unixtime = time();

    $sqlQry = $db->prepare("INSERT INTO  " . $GLOBALS['PREFIX'] . "event.NotificationStatus (nid, sitename, machine, status, timeExecuted, dartnum, consoleId, eventTime, agentId, eventIdx,solutionPush)
		VALUES (?,?,?,?,?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status =values(status),timeExecuted = ?,dartnum =values(dartnum),eventTime =values(eventTime),agentId =?");
    foreach ($sql as $key => $val) {
        $params = array_merge($sql, [$unixtime, $agentId]);
        $sqlQry->execute($params);
    }
    $resQry = $db->lastInsertId();

    if ($resQry) {
        $msg = 'success';
    } else {
        $msg = 'error';
    }
    return $msg;
}

function doActionOnNotification_func()
{

    $userName = $_SESSION["user"]["logged_username"];
    $agentId = $_SESSION["user"]["adminEmail"];
    $cNode = $_SESSION["cNode"];

    $JobRow = url::issetInRequest('jobRow') ? url::requestToText('jobRow') : '';

    $Jobs = explode('~~~~', $JobRow);

    $bid = 0;
    $type = 'Notification';
    $status = "Fixed";
    $sqlSchedule = array();
    $sql = array();
    $currTime = time();

    $str = "";
    for ($i = 0; $i < safe_count($Jobs); $i++) {
        $Job = explode('~~', $Jobs[$i]);

        $sitename = $Job[0];
        $serviceTag = $Job[1];
        $versionNo = $Job[2];
        $dartConfg = $Job[3];
        $profileName = $Job[4];
        $nid = $Job[5];
        $consoleId = $Job[6];
        $eventIdx = $Job[7];
        $eventTime = $Job[8];
        $notifyId = $Job[9];
        $dartnum = $Job[10];

        $sql[] = '("' . $nid . '", "' . $sitename . '","' . $serviceTag . '","' . $status . '","' . time() . '",' . $dartnum . ',"' . $consoleId . '","' . $eventTime . '","' . $agentId . '","' . $eventIdx . '","' . $profileName . '")';
        if ($cNode === 'NODEJS') {
            $sqlSchedule[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $currTime . ',"' . urlencode($dartConfg) . '","' . urldecode($profileName) . '","' . $agentId . '","' . $notifyId . '","' . $versionNo . '","' . $nid . '")';
            $sqlScheduleQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
        } else if ($cNode === 'NODEPHP') {
            $sqlAudit[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $currTime . ',"' . urlencode($dartConfg) . '","' . urldecode($profileName) . '","' . $agentId . '","' . $notifyId . '","' . $versionNo . '","' . $nid . '")';
            $sqlAuditQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
            $str .= 'dboard---' . $serviceTag . '---' . $sitename . '---' . $serviceTag . '---' . 'any##';
        }
    }

    updateNocStatusGrp($sql);
    updateMETickets($JobRow, $userName);

    if ($cNode === 'NODEJS') {
        $scheduleStatus = insIntoSchedule($sqlScheduleQuer);
        return $scheduleStatus;
    } else if ($cNode === 'NODEPHP') {
        $auditStatus = insIntoAudit($sqlAuditQuer);
        return $str;
    }
}

function doActionOnInteractive_func()
{

    $userName = $_SESSION["user"]["logged_username"];
    $agentId = $_SESSION["user"]["adminEmail"];

    $cNode = $_SESSION["cNode"];

    $JobRow = url::issetInRequest('jobRow') ? url::requestToText('jobRow') : '';

    $Jobs = explode('~~~~', $JobRow);

    $bid = 0;
    $nid = 0;
    $notifyId = 0;
    $type = 'Interactive';
    $sqlSchedule = array();
    $str = "";
    $currTime = time();
    for ($i = 0; $i < safe_count($Jobs); $i++) {
        $Job = explode('~~', $Jobs[$i]);
        $sitename = $Job[0];
        $serviceTag = $Job[1];
        $versionNo = $Job[2];
        $dartConfg = $Job[3];
        $profileName = $Job[4];

        if ($cNode === 'NODEJS') {
            $sqlSchedule[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $currTime . ',"' . urlencode($dartConfg) . '","' . urldecode($profileName) . '","' . $agentId . '",' . $notifyId . ',"' . $versionNo . '","' . $nid . '")';
            $sqlScheduleQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
        } else if ($cNode === 'NODEPHP') {
            $sqlAudit[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $currTime . ',"' . urlencode($dartConfg) . '","' . urldecode($profileName) . '","' . $agentId . '","' . $notifyId . '","' . $versionNo . '","' . $nid . '")';
            $sqlAuditQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
            $str .= 'dboard---' . $serviceTag . '---' . $sitename . '---' . $serviceTag . '---' . 'any##';
        }
    }

    if ($cNode === 'NODEJS') {
        $scheduleStatus = insIntoSchedule($sqlScheduleQuer);
        return $scheduleStatus;
    } else if ($cNode === 'NODEPHP') {
        $auditStatus = insIntoAudit($sqlAuditQuer);
        return $str;
    }
}

function doActionOnInteractiveMac_func()
{

    $userName = $_SESSION["user"]["logged_username"];
    $agentId = $_SESSION["user"]["adminEmail"];

    $JobRow = url::issetInRequest('jobRow') ? url::requestToText('jobRow') : '';

    $Jobs = explode('~~~~', $JobRow);

    $bid = 0;
    $nid = 0;
    $notifyId = 0;
    $type = 'Interactive';
    $sqlSchedule = array();

    for ($i = 0; $i < safe_count($Jobs); $i++) {
        $Job = explode('~~', $Jobs[$i]);
        $sitename = $Job[0];
        $machine = $Job[1];
        $versionNo = $Job[2];
        $dartConfg = $Job[3];
        $profileName = $Job[4];

        $sqlSchedule[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $machine . '","' . $type . '",' . time() . ',"' . urlencode($dartConfg) . '","' . urldecode($profileName) . '","' . $agentId . '",' . $notifyId . ',"' . $versionNo . '","' . $nid . '")';
    }

    $scheduleStatus = insIntoSchedule($sqlSchedule);
    return $scheduleStatus;
}

function doActionOnInteractiveAndroid_func()
{

    $userName = $_SESSION["user"]["logged_username"];
    $agentId = $_SESSION["user"]["adminEmail"];

    $JobRow = url::issetInRequest('jobRow') ? url::requestToText('jobRow') : '';
    $DJobRow = urldecode($JobRow);

    $Jobs = explode('~~~~', $DJobRow);

    $bid = 0;
    $nid = 0;
    $notifyId = 0;
    $type = 'Interactive';
    $sqlSchedule = array();

    for ($i = 0; $i < safe_count($Jobs); $i++) {
        $Job = explode('~~', $Jobs[$i]);
        $sitename = $Job[0];
        $machine = $Job[1];
        $versionNo = $Job[2];
        $dartConfg = $Job[3];
        $profileName = $Job[4];

        $sqlSchedule[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $machine . '","' . $type . '",' . time() . ',"' . $dartConfg . '","' . $profileName . '","' . $agentId . '",' . $notifyId . ',"' . $versionNo . '","' . $nid . '")';
    }

    $scheduleStatus = insIntoSchedule($sqlSchedule);
    return $scheduleStatus;
}

function getScheduledata_func()
{
    $db = pdo_connect();
    $schdQry = $db->prepare("select *,M.socketSession socketSession from " . $GLOBALS['PREFIX'] . "node.schedule S,machine M where S.servicetag=M.host and M.status='online' and S.machineOs = 'Android' and S.scheduleType IS NULL");
    $schdQry->execute();
    $res = $schdQry->fetchAll();
    $str = '';

    foreach ($res as $value) {

        $host = $value['servicetag'];

        $sitename = $value['siteName'];
        $osName = $value['machineOs'];
        $varValues = $value['varValues'];
        $sid = $value['sid'];
        $socketSession = $value['socketSession'];

        $str .= 'dboard---' . $host . '---' . $sitename . '---' . $socketSession . '---' . $sid . '---' . $varValues . '---' . $osName . '##';
    }

    return $str;
}

function getTempAuditdata_func()
{
    $db = pdo_connect();

    $OS = url::requestToAny('os');

    $tempauditQry = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "node.tempaudit T,tempMachine M where T.servicetag=M.host and M.status='online' and M.machineOs like ?");
    $tempauditQry->execute(["%$OS%"]);
    $res = $tempauditQry->fetchAll();
    $str = '';

    foreach ($res as $value) {
        $host = $value['servicetag'];
        $sitename = $value['siteName'];
        $osName = $value['machineOs'];

        $machineQry = $db->prepare("select socketSession from " . $GLOBALS['PREFIX'] . "node.machine where host=? order by id DESC limit ?");
        $machineQry->execute([$host, 1]);
        $machineRes = $machineQry->fetch();
        $socketSession = $machineRes['socketSession'];
        if ($socketSession !== null || $socketSession !== '' || $socketSession !== 'NA') {
            $str .= 'dboard---' . $host . '---' . $sitename . '---' . $socketSession . '---' . $osName . '##';
        }
    }
    return $str;
}

function updateAuditData_func()
{
    $machineList = url::issetInRequest('machineList') ? url::requestToText('machineList') : '';
    $db = pdo_connect();

    $search = explode(',', $machineList);
    $in = str_repeat('?,', safe_count($arr) - 1) . '?';
    $tempauditQry = $db->prepare("update `schedule` set scheduleType = '1' where sid IN ($in)");
    $tempauditQry->execute($search);
    $res = $tempauditQry->fetchAll();
    return $res;
}

function getCancelMachineDet_info()
{
    $db = pdo_connect();
    $machine = url::issetInRequest('machineName') ? url::requestToText('machineName') : '';

    $getNdSql = $db->prepare("select site,host machine,machine censusuniq,status,socketSession from " . $GLOBALS['PREFIX'] . "node.machine where host = ? order by id DESC limit ?");
    $getNdSql->execute([$machine, 1]);
    $getNd_res = $getNdSql->fetch();

    $emitStr = $getNd_res['socketSession'] . '---' . $getNd_res['censusuniq'];
    return $emitStr;
}

function getmachinelistSD_info()
{
    $db = pdo_connect();

    $dartid = url::issetInRequest('dartConfg') ? url::requestToText('dartConfg') : '';
    $appMach = url::issetInRequest('appMachines') ? url::requestToText('appMachines') : '';

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $cNode = $_SESSION["cNode"];

    $selectiontype = "";
    if ($appMach === '') {
        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $sqlQry = $db->prepare("select machine, site, host, status, machineOs from tempMachine where host =?");
            $sqlQry->execute([$searchValue]);
            $selectiontype = 'Machine : ' . $searchValue;
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            if ($searchValue == 'All') {
                $searchVal = get_all_search_list($searchtype);
                $search = explode(',', $searchVal);
                $in = str_repeat('?,', safe_count($search) - 1) . '?';
                $sqlQry = $db->prepare("select machine, site, host, status, machineOs from tempMachine where site IN ($in)");
                $sqlQry->execute($search);
                $selectiontype = 'Site : ' . $searchVal;
            } else {

                $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where site =?");
                $sqlQry->execute([$searchValue]);
                $selectiontype = 'Site : ' . $searchValue;
            }
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == 'All') {
                $searchVal = get_all_search_list($searchtype);

                $search = explode(',', $searchVal);
                $in = str_repeat('?,', safe_count($search) - 1) . '?';
                $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where site IN ($in)");
                $sqlQry->execute($search);
                $selectiontype = 'Group : ' . $GroupName;
            } else {
                $machines = get_all_search_list($searchtype);
                $search = explode(',', $machines);
                $in = str_repeat('?,', safe_count($search) - 1) . '?';
                $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where host IN ($in)");
                $sqlQry->execute($search);
                $selectiontype = 'Group : ' . $GroupName;
            }
        }
    } else {
        $search = explode(',', $appMach);
        $in = str_repeat('?,', safe_count($search) - 1) . '?';
        $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where host IN ($in)");
        $sqlQry->execute($search);
        $selectiontype = 3;
    }

    $resQry = $sqlQry->fetchAll();
    $selectedmachinecount = safe_count($resQry);

    if ($selectedmachinecount == 0) {
        echo '0##0##0##0';
        exit;
    }

    $userName = $_SESSION["user"]["logged_username"];
    $agentId = $_SESSION["user"]["adminEmail"];

    $sqlSchedule = array();
    $type = 'SD';
    $nid = '';
    $notifyId = '';
    $versionNo = '';
    $machinelist = '';
    $str = '';

    $qry = $db->prepare("select packageDesc, packageName, configDetail, sourceType from " . $GLOBALS['PREFIX'] . "softinst.Packages where id =?");
    $qry->execute([$dartid]);
    $res = $qry->fetch();

    $packageDesc = $res['packageDesc'];
    $packageName = $res['packageName'];
    $configDetail = $res['configDetail'];
    $sourceType = $res['sourceType'];

    $androidtype = 0;

    if (trim($sourceType) == '4') {
        $androidtype = 2;
    } else {
        $androidtype = 1;
    }

    $batchinsert = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "node.scheduleHistory (agent, packageName, type, selectiontype, time) VALUES (?,?,?,?,?)");
    $batchinsert->execute([$userName, $packageName, $type, $selectiontype, time()]);
    $bid = $db->lastInsertId();

    $timenow = time();

    foreach ($resQry as $value) {
        $serviceTag = $value['host'];
        $sitename = $value['site'];
        $machinelist .= $value['host'] . '~~';
        $dartConfg = "";

        if (stripos($value['machineOs'], 'android') !== false) {
            if ($androidtype == 1) {
                $dartConfg = "ScripNo=415&S00415ServerIp=" . $configDetail . "&S00415ServerIp_GroupSetting=CID&S00415RunNowButton=Execute&S00415RunNowButton_GroupSetting=CID@@@@empty";
            } else {
                $dartConfg = "ScripNo=415&S00415AppInstallFromPlayStore=" . $packageName . "&S00415AppInstallFromPlayStore_GroupSetting=CID&S00415RunNowButton=Execute&S00415RunNowButton_GroupSetting=CID@@@@empty";
            }
        } else if (stripos($value['machineOs'], 'mac') !== false) {
            $dartConfg = "";
        } else if (stripos($value['machineOs'], 'linux') !== false) {
            $dartConfg = "";
        } else {
            $dartConfg = "VarName=S00288IndividualPatches;VarType=2;VarVal=" . "SWD-" . $dartid . ";Action=SET;DartNum=288;VarScope=1;#;NextConf;#VarName=S00288RunNowButtonnew;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=288;VarScope=1;#;NextConf;##;NextConf;#End";
        }
        $profileName = "SWD : " . $packageDesc;
        $currTime = date('U');
        if ($cNode === 'NODEJS') {
            $sqlSchedule[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $timenow . ',"' . $dartConfg . '","' . $profileName . '","' . $agentId . '","' . $notifyId . '","' . $versionNo . '","' . $nid . '")';
            $sqlScheduleQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
        } else if ($cNode === 'NODEPHP') {
            $sqlAudit[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $timenow . ',"' . urlencode($dartConfg) . '","' . urldecode($profileName) . '","' . $agentId . '","' . $notifyId . '","' . $versionNo . '","' . $nid . '")';
            $sqlAuditQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
            $str .= 'dboard---' . $serviceTag . '---' . $sitename . '---' . $serviceTag . '---' . 'any##';
        }
    }

    if ($cNode === 'NODEJS') {
        $resStatus = insIntoSchedule($sqlScheduleQuer);
    } else if ($cNode === 'NODEPHP') {
        $resStatus = insIntoAudit($sqlAuditQuer);
    }
    $OS = $resQry[0]['machineOs'];

    return $selectedmachinecount . '@@' . $machinelist . '@@' . $resStatus . '@@' . $bid . '@@' . $OS . '@@' . $str;
}

function insIntoSchedule($sql)
{
    $db = pdo_connect();

    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "node.schedule (bid, siteName, username, servicetag, type, scheduleTime, varValues, profileName, userId, idx, version, nid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($sql as $key => $val) {
        $sqlQry->execute($val);
    }
    $resQry = $db->lastInsertId();
    return $resQry;
}

function insIntoAudit($sql)
{
    $db = pdo_connect();

    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "node.audit (bid, siteName, username, servicetag, type, createdtime, varValues, profileName, userId, idx, version, nid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    foreach ($sqlQry as $key => $val) {
        $sqlQry->execute($val);
    }
    $resQry = $db->lastInsertId();
    return $resQry;
}

function insIntoPackageHistory($sql)
{
    $db = pdo_connect();

    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PackageHistory (oid,pid,qid,site,host,serverTime,pushType,pushName,`status`) VALUES(?,?,?,?,?,?,?,?,?)");
    foreach ($sql as $key => $val) {
        $sqlQry->execute($val);
    }
    $resQry = $db->lastInsertId();
    return $resQry;
}


function profileData_info()
{

    $level = url::requestToText('level');
    $checklevel = url::requestToText('search');
    $_SESSION['passlevel'] = $checklevel;
    // $db = pdo_connect();
    global $TS_restricted;
    $searchType = $_SESSION["searchType"];

    $os = url::issetInRequest('os') ? url::requestToText('os') : '';
    $ossub = url::issetInRequest('ossub') ? url::requestToText('ossub') : '';
    $pageId = url::issetInRequest('pageId') ? url::requestToText('pageId') : '';

    $menuitem = url::issetInRequest('menuitem') ? url::requestToText('menuitem') : '';
    if ($os === "Windows") {
        $profile = $GLOBALS['PREFIX'] . 'event.' . $_SESSION["user"]["profileName"];
    } else if ($os === "Android") {
        $profile = $GLOBALS['PREFIX'] . 'event.' . $_SESSION["user"]["andprofileName"];
    } else if ($os === "Mac") {
        $profile = $GLOBALS['PREFIX'] . 'event.' . $_SESSION["user"]["macprofileName"];
    } else if ($os === "Linux") {
        $profile = $GLOBALS['PREFIX'] . 'event.' . $_SESSION["user"]["lnxprofileName"];
    } else if ($os === "RLinux") {
        $profile = $GLOBALS['PREFIX'] . 'event.' . $_SESSION["user"]["readynetProfileName"];
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
    if ($level == 'notify') {
        $Res = notificationwin();
    } else {
        $Res = getmgroupuniqueid();
    }

    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array(
        "function" => "getBaseProfileDetails",
        "sort" => $sortObject,
        "mgroupuniq" => $Res['mgroupuniq'],
        "parent_mgroupuniq" => $Res['parentmgroupid'],
    ));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    $main_arr = $arr_response["Main"];
    if (safe_count($main_arr) <= 0) {
        $msg = '<p data-qa="Troubleshooter-empty-result" qa-error="' . __FILE__ . ":" . __LINE__ . '" >Attach a profile to view the Troubleshooter tiles</p>';
        return $msg;
    }

    // if ($count_res > 0 && $TS_restricted == 1 && $searchType != 'ServiceTag' && $searchType != 'Groups') {
    //     $msg = 'Please select machine to see list of Troubleshooters.';
    //     return '<ul class="nav" style="list-style-type: none;" id="toolboxList">' . $msg . '</ul>';
    // }

    $apiresponse1 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, "limit" => 1, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse1 = safe_json_decode(json_encode($apiresponse1), true);
    $mainarr = $arrresponse1["Main"];

    $apiresponse2 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, $sortObj, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse2 = safe_json_decode(json_encode($apiresponse2), true);
    $mainarr2 = $arrresponse2["Main"];

    $all_api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $all_arr_response = safe_json_decode(json_encode($all_api_response), true);
    $allProfile_arr = $all_arr_response["Main"];
    $auditRes = create_auditLog('Troubleshooter', 'View', 'Success', $_REQUEST);

    $new_mid = 0;
    $new_page = 0;
    unset($_SESSION['profileKeys']);
    unset($_SESSION['new_mid']);
    unset($_SESSION['$new_page']);
    $profileKeys = [];
    $page_L2 = [];
    $parent_L2 = [];
    $parent_ID = 0;
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

    if ($count_res > 0) {
        foreach ($main_arr as $key => $val) {
            $menuItem = $main_arr[$key]['menuItem'];
            $type = $main_arr[$key]['type'];
            $parentId = $main_arr[$key]['parentId'];
            $profile = $main_arr[$key]['profile'];
            $dart = $main_arr[$key]['dart'];
            $variable = $main_arr[$key]['variable'];
            $shortDesc = $main_arr[$key]['shortDesc'];
            $description = $main_arr[$key]['tileDesc'];
            $page = $main_arr[$key]['page'];
            $status = $main_arr[$key]['Enable/Disable'];
            $mid = $main_arr[$key]['mid'];
            $Os = $main_arr[$key]['OS'];

            $json = "'" . implode("*****", array_map(
                function ($v, $k) {
                    return sprintf("%s#:#%s", $k, $v);
                },
                $main_arr[$key],
                safe_array_keys($main_arr[$key])
            )) . "'";

            if ($level == 'main' || $level == 'edit' || $level == 'notify') {
                if ($status == '1' || $status == 1 || $status == '3' || $status == 3) {
                    $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "','" . $mid . "','" . $level . "','" . $Os . "'";
                    if ($type == 'L1') {
                        $tileDesc = $profile . '##' . $description;
                        $finStr .= '<li><input type="hidden" value="' . $mid . '" class="hidden_mid"><a   href="javascript:;"  title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '<i  style="display:none"  class="tim-icons icon-pencil troubIcon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');">' . '</i></a></li>';
                    } elseif ($type == 'L2') {
                        $finStr .= '<li><input type="hidden" value="' . $mid . '" class="hidden_mid"><a  href="javascript:;"  title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '<i style="display:none" class="tim-icons icon-pencil troubIcon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');">' . '</i></a></li>';
                    } elseif ($type == 'L3') {
                        $finStr .= '<li><input type="hidden" value="' . $mid . '" class="hidden_mid"><a  href="javascript:;" title="' . $profile . '" onclick="clickl3level(' . $param . ');">' . $profile . '<i style="display:none"  class="tim-icons icon-pencil troubIcon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');">' . '</i></a></li>';
                    }
                }
            } else {
                if ($status == '1' || $status == 1 || $status == '3' || $status == 3) {
                    $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "','" . $mid . "','" . $level . "'";
                    if ($type == 'L1') {
                        $tileDesc = $profile . '##' . $description;
                        $finStr .= '<li><input type="hidden" value="' . $mid . '" class="hidden_mid"><input type="hidden" value="' . $status . '" class="hidden_status"><a   href="javascript:;"  title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '<i  style="display:none"  class="tim-icons icon-pencil troubIcon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');">' . '</i></a></li>';
                    } elseif ($type == 'L2') {
                        $finStr .= '<li><input type="hidden" value="' . $mid . '" class="hidden_mid"><input type="hidden" value="' . $status . '" class="hidden_status"><a  href="javascript:;"  title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '<i style="display:none" class="tim-icons icon-pencil troubIcon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');">' . '</i></a></li>';
                    } elseif ($type == 'L3') {
                        $finStr .= '<li><input type="hidden" value="' . $mid . '" class="hidden_mid"><a  href="javascript:;" title="' . $profile . '" onclick="clickl3level(' . $param . ');">' . $profile . '<i style="display:none"  class="tim-icons icon-pencil troubIcon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');">' . '</i></a></li>';
                    }
                }
            }
        }
    } else {
        $msg = '<p data-qa="Troubleshooter-empty-result"  qa-error="' . __FILE__ . ":" . __LINE__ . '"  >Attach a profile to view the Troubleshooter tiles</p>';
        return $msg;
    }
    unset($_SESSION['fromwindow']);
    if ($level == 'notify') {
        return '<ul class="nav" style="list-style-type: none;" id="toolboxList">' . $finStr . '</ul>##' . $backParentId . '##' . $profile . '##' . safe_addslashes($tileDesc);
    } else {
        if ($TS_restricted == 1 && $searchType != 'ServiceTag' && isset($_SESSION['fromwindow'])) {
            return '<div  id="clickList" >' . $finStr . '</div>##' . $backParentId . '##' . $profile . '##' . safe_addslashes($tileDesc);
            // } else if ($TS_restricted == 1 && $searchType != 'ServiceTag' && $searchType != 'Groups') {
            //     $msg = 'Please select machine to see list of Troubleshooters.';
            //     return '<ul class="nav" style="list-style-type: none;" id="toolboxList">' . $msg . '</ul>';
        } else {
            return '<ul class="nav" style="list-style-type: none;" id="toolboxList">' . $finStr . '</ul>##' . $backParentId . '##' . $profile . '##' . safe_addslashes($tileDesc);
        }
    }
}

function advprofileData_info()
{
    $db = pdo_connect();

    $os = url::issetInRequest('os') ? url::requestToText('os') : '';
    $ossub = url::issetInRequest('ossub') ? url::requestToText('ossub') : '';
    $pageId = url::issetInRequest('pageId') ? url::requestToText('pageId') : '';

    $menuitem = url::issetInRequest('menuitem') ? url::requestToText('menuitem') : '';

    if ($os === "Windows") {
        $profile = $_SESSION["user"]["profileName"];
    } else if ($os === "Android") {
        $profile = $_SESSION["user"]["andprofileName"];
    } else if ($os === "Mac") {
        $profile = $_SESSION["user"]["macprofileName"];
    } else if ($os === "Linux") {
        $profile = $_SESSION["user"]["lnxprofileName"];
    } else if ($os === "RLinux") {
        $profile = $_SESSION["user"]["readynetProfileName"];
    }

    $table = $GLOBALS['PREFIX'] . 'event.' . $profile;
    $wh = '';
    $arr = array();
    if ($pageId == '1' || $pageId == 1) {
        if ($os === "Android") {
            $wh = "page = ?";
            $arr[] = 1;
        } else {
            $wh = "page = ?";
            $arr[] = 2;
        }
    } else {
        $wh = "page = ?";
        $arr[] = $pageId;
    }

    if ($ossub !== 'NA') {
        $like = "%$ossub%";
        $wh .= " and (OS like ? or OS = 'common')";
        $arr[] = $ossub;
    }

    $sqlQuery = $db->prepare("select mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,tileDesc,"
        . "iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart from $table where $wh and menuItem !='Advanced Troubleshooting' and follow !='CORE/' group by shortDesc "
        . "UNION select mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,tileDesc,"
        . "iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart  from $table  where $wh and menuItem !='Advanced Troubleshooting' and follow !='CORE/' "
        . "group by shortDesc order by mid");

    $params = array_merge($arr, $arr);
    $sqlQuery->execute($params);
    $sqlRes = $sqlQuery->fetchAll();

    $profile_slash = safe_addslashes($profile);

    if ($menuitem == '') {
        $qry = $db->prepare("select page from $profile where  parentId = ? limit ?");
        $qry->execute([$pageId, 1]);
    } else {
        $qry = $db->prepare("select page from $profile where  parentId = ? and menuItem = ? limit ?");
        $qry->execute([$pageId, $menuitem, 1]);
    }

    $qryres = $sql->fetch();
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

        $profileId = preg_replace('/\s+/', '_', $profile);

        $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "','" . $profileId . "'";
        if ($type == 'L1') {
            $tileDesc = $profile . '##' . $description;
        } elseif ($type == 'L2') {
            $finStr .= '<li><a href="javascript:;" title="' . $profile . '" onclick="clickl1level(' . $param . ');">' . $profile . '<i class="tim-icons icon-pencil troubIcon"></i></a></li>';
        } elseif ($type == 'L3') {
            $finStr .= '<li><a href="javascript:;" title="' . $profile . '" onclick="clickl3level(' . $param . ');">' . $profile . '<i class="tim-icons icon-pencil troubIcon"></i></a></li>';
        }
    }
    return '<ul id="advtoolboxList">' . $finStr . '</ul>##' . $backParentId . '##' . $profile . '##' . safe_addslashes($tileDesc);
}

function profileDataList_info()
{
    $level = url::requestToText('level');
    $db = pdo_connect();
    global $TS_restricted;
    $searchType = $_SESSION["searchType"];

    $os = url::issetInRequest('os') ? url::requestToText('os') : '';
    $ossub = url::issetInRequest('ossub') ? url::requestToText('ossub') : '';
    $pageId = url::issetInRequest('pageId') ? url::requestToText('pageId') : '';
    $searchprofile = url::issetInRequest('searchProfile') ? url::requestToAny('searchProfile') : '';

    $menuitem = url::issetInRequest('menuitem') ? url::requestToText('menuitem') : '';
    if ($os === "Windows") {
        $profile = $_SESSION["user"]["profileName"];
    } else if ($os === "Android") {
        $profile = $_SESSION["user"]["andprofileName"];
    } else if ($os === "Mac") {
        $profile = $_SESSION["user"]["macprofileName"];
    } else if ($os === "Linux") {
        $profile = $_SESSION["user"]["lnxprofileName"];
    } else if ($os === "RLinux") {
        $profile = $_SESSION["user"]["readynetProfileName"];
    }

    $table = $GLOBALS['PREFIX'] . 'event.' . $profile;
    $wh = '';
    $sortObject = new stdClass();
    $colname = "page";
    $sortobject = new stdClass();
    $cname = "parentId";
    $sortObj = new stdClass();
    $Cname = "menuItem";
    $sortObj->$Cname = $menuitem;
    $sort = new stdClass();
    $col = "profile";

    if ($pageId == '1' || $pageId == 1) {
        if ($os === "Android") {
            $sortObject->$colname = "1";
            $sortobject->$cname = "1";
        } else {
            $sortObject->$colname = "2";
            $sortobject->$cname = "1";
        }
    } else {
        if ($searchprofile != '') {
            $sort->$col = $searchprofile;
        } else {
            $sortObject->$colname = $pageId;
            $sortobject->$cname = $pageId;
        }
    }
    if ($ossub !== 'NA') {
        if (($os == $ossub) || ($os == 'common')) {
            $wh = $os;
        }
    }

    if ($level == 'notify') {
        $Res = notificationwin();
    } else {
        $Res = getmgroupuniqueid();
    }
    if ($searchprofile == '') {
        $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
        $arr_response = safe_json_decode(json_encode($api_response), true);
        $main_arr = $arr_response["Main"];
        // $dynamic_config = $arr_response["dynamiConfig"];
    } else {
        $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sort, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
        $arr_response = safe_json_decode(json_encode($api_response), true);
        $main_arr = $arr_response["Main"];
        // $dynamic_config = $arr_response["dynamiConfig"];
    }
    $apiresponse1 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, "limit" => 1, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse1 = safe_json_decode(json_encode($apiresponse1), true);
    $mainarr = $arrresponse1["Main"];

    $apiresponse2 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortobject, $sortObj, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arrresponse2 = safe_json_decode(json_encode($apiresponse2), true);
    $mainarr2 = $arrresponse2["Main"];

    if ($menuitem == '') {
        $qry = $mainarr;
    } else {
        $qry = $mainarr2;
    }
    $profilearr = [];
    $backParentId = $qry;
    $tileDesc = '';
    $finStr = '';
    $count_res = safe_count($main_arr);
    $i = 0;
    if ($count_res > 0) {
        foreach ($main_arr as $key => $val) {
            $i++;
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
            $dynamic = $main_arr[$key]['dynamic'];
            if ($dynamic == 1) {
                $sequence = $dart;
            } else {
                $sequence = '';
            }
            $json = "'" . implode("*****", array_map(
                function ($v, $k) {
                    return sprintf("%s#:#%s", $k, $v);
                },
                $main_arr[$key],
                safe_array_keys($main_arr[$key])
            )) . "'";
            if ($level == 'main') {
                if ($status == '1' || $status == 1 || $status == '3' || $status == 3) {
                    $divId = "fixlist" . $i;
                    $listId = "morelist" . $parentId;
                    $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "','" . $divId . "','" . $listId . "','" . $dynamic . "','" . $sequence . "','" . $Os . "','" . $level . "'";
                    if ($type == 'L1') {
                        $tileDesc = $profile . '##' . $description;
                    } elseif ($type == 'L2') {
                        $finStr .= '<li>
                        <input type="hidden" value="' . $mid . '" class="hidden_mid">
                        <a data-bs-toggle="collapse" href="javascript:;"  onclick="clickl12level(' . $param . ');">
                            <span class="resetTroub">
                                <i class="tim-icons icon-link-72 green"></i>
                                    <h5>' . $profile . '</h5>
                                    <p class="txt"  data-qa="communication_func_1_description">' . $description . '</p>
                            </span>
                            <i style="display:none" class="tim-icons icon-pencil troubIconicon" onclick="onEditTileClick(' . $json . ');"></i>
                        </a>
                        <ul id="' . $listId . '" data-qa="profileDataList_info::morelist"></ul>
                    </li>';
                    } elseif ($type == 'L3') {
                        if (!in_array($profile, $profilearr)) {
                            array_push($profilearr, $profile);
                            $finStr .= '<li>
                        <input type="hidden" value="' . $mid . '" class="hidden_mid">
                        <a data-bs-toggle="collapse" href="" style="cursor:default;" onclick="clickcheck(this,' . $mid . ',' . $status . ')" >
                            <span class="resetTroub">
                                <i class="tim-icons icon-link-72 green"></i>
                                <p class="rightBtn"><i style="display:none" class="tim-icons icon-pencil troubIconicon" onclick="onEditTileClick(' . $json . ');"></i></p>
                                    <h5>' . $profile . '</h5>
                                        <p class="txt"  data-qa="communication_func_2_description" >' . $description . '</p>
                                            <p class="rightBtn">
                                        <button data-level="' . $level . '" type="button" id="trlbtn" class="swal2-confirm btn btn-success btn-sm rightBtn" onclick="clickl3level(' . $param . ');">Run the Troubleshooter</button>

                                        </p>
                            </span>
                        </a>
                    </li>';
                        }
                    }
                }
            } else {
                $divId = "fixlist" . $i;
                $listId = "morelist" . $parentId;
                $array = [];
                array_push($array, $mid);
                array_push($array, $status);
                $val = "'" . implode('**', $array) . "'";
                if ($status == '1' || $status == 1 || $status == '3' || $status == 3) {

                    $param = "this,'" . $parentId . "','" . safe_addslashes($profile) . "','" . $dart . "','" . $variable . "','" . $shortDesc . "','" . urlencode($description) . "','" . $page . "','" . $menuItem . "','" . $divId . "','" . $listId . "','" . $dynamic . "','" . $sequence . "','" . $Os . "'";
                    if ($type == 'L1') {
                        $tileDesc = $profile . '##' . $description;
                    } elseif ($type == 'L2') {
                        $finStr .= '<li>
                            <input type="hidden" value="' . $mid . '" class="hidden_mid">
                            <input type="hidden" value="' . $status . '" class="hidden_status">
                            <a data-bs-toggle="collapse" href="javascript:;"  onclick="clickl12level(' . $param . ');">
                                <span class="resetTroub">
                                    <i class="tim-icons icon-link-72 green"></i>
                                        <h5>' . $profile . '</h5>
                                        <p class="txt"  data-qa="communication_func_3_description" >' . $description . '</p>
                                </span>
                                <i style="display:none" class="tim-icons icon-pencil troubIconicon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');"></i>
                            </a>
                            <ul id="' . $listId . '" data-qa="profileDataList_info::morelist"></ul>
                        </li>';
                    } elseif ($type == 'L3') {
                        if (!in_array($profile, $profilearr)) {
                            array_push($profilearr, $profile);
                            $finStr .= '<li>
                            <input type="hidden" value="' . $mid . '" class="hidden_mid">
                            <input type="hidden" value=' . $val . ' class="hidden_status">
                            <input type="hidden" value=' . $json . ' id="json_value">
                            <a data-bs-toggle="collapse" href="" style="cursor:default;" onclick="clickcheck(this,' . $mid . ',' . $val . ')" >
                                <span class="resetTroub">
                                    <i class="tim-icons icon-link-72 green"></i>
                                    <p class="rightBtn"><i style="display:none" class="tim-icons icon-pencil troubIconicon" onclick="onEditTileClick(' . $json . ',\'' . $level . '\');"></i></p>
                                        <h5>' . $profile . '</h5>
                                            <p class="txt"  data-qa="communication_func_4_description" >' . $description . '</p>
                                                <p class="rightBtn">
                                                <button
                                                type="button"
                                                data-level="' . $level . '"
                                                 id="trlbtn"
                                                 class="swal2-confirm btn btn-success btn-sm rightBtn"
                                                 onclick="clickl3level(' . $param . ');">
                                                 Run the Troubleshooter
                                                 </button>
                                            </p>
                                </span>
                            </a>
                        </li>';
                        }
                    }
                }
            }
        }
    }
    return '' . $finStr . '##' . $backParentId . '##' . $profile . '##' . safe_addslashes($tileDesc);
}

function getProfileName_func()
{
    $db = pdo_connect();

    $vValue = url::issetInRequest('varValue') ? url::requestToText('varValue') : '';
    $profileName = $_SESSION['user']['profileName'];

    $statusQry = $db->prepare("select profile,tileDesc from $table where varValue = ? limit ?");
    $statusQry->execute([$vValue, 1]);
    $statusres = $statusQry->fetch();
    echo $statusres['profile'] . '####' . nl2br($statusres['tileDesc']);
}

function configDartSync($gval)
{

    $cType = $_SESSION["cType"];

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];

    // $selectiontype = "";

    $db = pdo_connect();

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

        $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where host =?");
        $sqlQry->execute([$searchValue]);
        // $selectiontype = 'Machine : ' . $searchValue;
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        if ($searchtype == 'All') {
            $searchVal = get_all_search_list($searchtype);
            $search = explode(',', $searchVal);
            $in = str_repeat('?,', safe_count($search) - 1) . '?';
            $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where site IN ($in)");
            $sqlQry->execute($search);

            // $selectiontype = 'Site : ' . $searchVal;
        } else {
            $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where site =?");
            $sqlQry->execute([$searchValue]);
            // $selectiontype = 'Site : ' . $searchValue;
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        if ($searchtype == 'All') {
            $searchVal = get_all_search_list($searchtype);

            $search = explode(',', $searchVal);
            $in = str_repeat('?,', safe_count($search) - 1) . '?';
            $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where host IN ($in)");
            $sqlQry->execute($search);

            // $selectiontype = 'Group : ' . $GroupName;
        } else {
            $machines = get_all_search_list($searchValue);
            $search = explode(',', $machines);
            $in = str_repeat('?,', safe_count($search) - 1) . '?';
            $sqlQry = $db->prepare("select machine, site, host, status, machineOs from " . $GLOBALS['PREFIX'] . "node.tempMachine where host IN ($in)");
            $sqlQry->execute($search);
            // $selectiontype = 'Group : ' . $GroupName;
        }
    }

    $resQry = $sqlQry->fetchAll();
    $selectedmachinecount = safe_count($resQry);

    if ($selectedmachinecount == 0) {
        return '0##0##0';
    } else {
        $OS = $resQry[0]['machineOs'];
        if (stripos($OS, 'windows') !== false) {
            if ($cType == "IBM") {
                winDartConfSyncIBM($gval, $searchtype, $resQry);
                return 'windows';
            } else if ($cType == "COMMON") {
                $res = winDartConfSyncCOMMON($gval, $searchtype, $resQry);
                $cNode = $_SESSION["cNode"];
                if ($cNode === 'NODEJS') {
                    return 'windows';
                } else if ($cNode === 'NODEPHP') {
                    return $res;
                }
            }
        } else if (stripos($OS, 'android') !== false) {
            androidDartConfSync($gval, $searchtype, $resQry);
            return 'android';
        } else if (stripos($OS, 'mac') !== false) {
            macDartConfSyncCOMMON($gval, $searchtype, $resQry);
            return 'mac';
        }
    }
}

function androidDartConfSync($gval, $searchtype, $resQry)
{

    $val = explode("&ScripNo=", $gval);
    $CID = 'empty';

    $scripval = explode("&", $val[1]);
    $dart = $scripval[0];
    $varvalue = "ScripNo=" . $dart;

    $db = pdo_connect();

    $var = "%$dart%";
    $sql = $db->prepare("SELECT Variable FROM " . $GLOBALS['PREFIX'] . "node.AndVariables WHERE Variable like ? and VarType = ?");
    $sql->execute([$var, 2]);
    $set = $sql->fetchAll();

    foreach ($set as $key => $row) {
        $varvalue .= "&" . $row['Variable'] . "=" . "&" . $row['Variable'] . "_GroupSetting=CID";
    }

    for ($x = 1; $x < safe_count($scripval); $x++) {

        if (strpos($scripval[$x], 'button=') === false) {

            $temp = explode("=", $scripval[$x]);

            $val1 = $temp[0];
            $val2 = $scripval[$x];

            if (strpos($val1, '_GroupExecute') !== false) {
                $val1 = str_replace("_GroupExecute", "", $val1);
            }

            if (strpos($val2, '_GroupExecute') !== false) {
                $val2 = str_replace("_GroupExecute", "", $val2);
            }

            if (strpos($val2, 'Execute for group') !== false) {
                $val2 = str_replace("Execute for group", "Execute", $val2);
            }

            if (strpos($val2, '_confirmation') !== false) {
                $varvalue .= "&" . $val2;
            } else {
                $varvalue .= "&" . $val2 . "&" . $val1 . "_GroupSetting=CID";
            }
        }
    }
    if (strpos($varvalue, '_GroupExecute_GroupSetting') !== false) {
        $varvalue = str_replace("_GroupExecute_GroupSetting", "_GroupSetting", $varvalue);
    }

    $varvalueencoded = $varvalue . '@@@@' . $CID;
    $resu = syncDataUsingNode($varvalueencoded, $resQry, $dart);
    return $resu;
}

function winDartConfSyncCOMMON($gval, $searchtype, $resQry)
{

    $db = pdo_connect();
    $val = explode("&ScripNo=", $gval);
    $scripval = explode("&", $val[1]);
    $dart = $scripval[0];
    $changeVal = $val[1];
    $cNode = $_SESSION["cNode"];

    $sql = $db->prepare("SELECT name,itype FROM " . $GLOBALS['PREFIX'] . "node.WinVariables WHERE scop =?");
    $sql->execute([$dart]);
    $set = $sql->fetchAll();
    $res = $set;

    foreach ($res as $key => $row) {
        if (strpos($changeVal, $row['name']) !== false) {
            $oldVal = "&" . $row['name'];
            $newVal = "||@@||" . $row['name'];
            $changeVal = str_replace($oldVal, $newVal, $changeVal);
        }
    }

    if (strpos($changeVal, '&button') !== false) {
        $oldVal = '&button';
        $newVal = "||@@||" . 'button';
        $changeVal = str_replace($oldVal, $newVal, $changeVal);
    }

    $scripVal = explode("||@@||", $changeVal);
    $varvalue = "";

    $execute = "";
    $executeType = "";

    $delimeter = "#;NextConf;#";

    if ($searchtype == 'ServiceTag' || $searchtype === 'Service Tag' || $searchtype === 'Host Name') {
        $executeType = 1;
    } else {
        $executeType = 2;
    }

    $res1 = $set;
    foreach ($res1 as $key => $row) {
        if ($row['itype'] == '3') {
            $dartname = $row['name'];
            $strType = '3';
            if ($varvalue == "") {
                $varvalue = 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=FALSE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
            } else {
                $varvalue .= $delimeter . 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=FALSE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
            }
        }
    }

    foreach ($scripVal as $value) {

        if (strpos($value, '=') !== false) {

            if (strpos($value, '=on') !== false) {

                $scripname = explode("=", $value);
                $dartname = $scripname[0];
                $strType = '3';

                if ($varvalue == "") {
                    $varvalue = 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=TRUE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                } else {
                    $varvalue .= $delimeter . 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=TRUE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                }
            } else {

                $scripname = explode("=", $value);

                if ($scripname[0] === $dart) {
                    continue;
                }

                $dartname = $scripname[0];
                $dartvalue = "";
                $dartvalue .= $scripname[1];

                for ($x = 2; $x < safe_count($scripname); $x++) {
                    $dartvalue .= ("=" . $scripname[$x]);
                }

                if (strpos($dartname, 'button') !== false) {
                } else {
                    if (strpos($dartname, 'GroupExecute') !== false) {

                        $exeval = explode("_", $dartname);
                        $execute = $exeval[0];
                    } else {
                        $res = $set;
                        foreach ($res as $key => $row) {
                            if ($row['name'] === $dartname) {

                                $strType = '';

                                if ($row['itype'] === '0') {
                                    $strType = '0';
                                } else if ($row['itype'] === '2') {
                                    if ($dartname === "ConfigurationPassword" || $dartname === "SimpleConfigPassword") {
                                        if ($dartvalue === '000000') {
                                            continue;
                                        } else {
                                            $strType = '4';
                                        }
                                    } else {
                                        $strType = '2';
                                    }
                                }

                                if ($varvalue == "") {
                                    if ($cNode === 'NODEJS') {
                                    } else if ($cNode === 'NODEPHP') {
                                        $NewLineDl = '#;NewLine;#';
                                        $dartvalue = preg_replace("/(\r\n|\n|\r|\t)/i", $NewLineDl, $dartvalue);
                                    }
                                    $varvalue = 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=" . $dartvalue . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                                } else {
                                    if ($cNode === 'NODEJS') {
                                    } else if ($cNode === 'NODEPHP') {
                                        $NewLineDl = '#;NewLine;#';
                                        $dartvalue = preg_replace("/(\r\n|\n|\r|\t)/i", $NewLineDl, $dartvalue);
                                    }
                                    $varvalue .= $delimeter . 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=" . $dartvalue . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if ($execute == "") {
        if ($cNode === 'NODEJS') {
            $varvalue .= $delimeter . $delimeter . "End";
        } else if ($cNode === 'NODEPHP') {
        }
    } else {
        if ($cNode === 'NODEJS') {
            $varvalue .= $delimeter . 'VarName=' . trim($execute) . ";VarType=7;VarVal=Execute;Action=RUN" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";" . $delimeter . $delimeter . "End";
        } else if ($cNode === 'NODEPHP') {
            $varvalue .= $delimeter . 'VarName=' . trim($execute) . ";VarType=7;VarVal=Execute;Action=RUN" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
        }
    }
    $varvalueencoded = urlencode($varvalue);
    $resu = syncDataUsingNode($varvalueencoded, $resQry, $dart);
    return $resu;
}

function macDartConfSyncCOMMON($gval, $searchtype, $resQry)
{

    $db = pdo_connect();
    $val = explode("&ScripNo=", $gval);
    $scripval = explode("&", $val[1]);
    $dart = $scripval[0];
    $changeVal = $val[1];

    $sql = $db->prepare("SELECT name,itype FROM " . $GLOBALS['PREFIX'] . "node.MacVariables WHERE scop =?");
    $sql->execute([$dart]);
    $set = $sql->fetchAll();
    $res = $set;

    foreach ($res as $key => $row) {
        if (strpos($changeVal, $row['name']) !== false) {
            $oldVal = "&" . $row['name'];
            $newVal = "||@@||" . $row['name'];
            $changeVal = str_replace($oldVal, $newVal, $changeVal);
        }
    }

    if (strpos($changeVal, '&button') !== false) {
        $oldVal = '&button';
        $newVal = "||@@||" . 'button';
        $changeVal = str_replace($oldVal, $newVal, $changeVal);
    }

    $scripVal = explode("||@@||", $changeVal);
    $varvalue = "";

    $execute = "";
    $executeType = "";

    $delimeter = "#;NextConf;#";

    if ($searchtype == 'ServiceTag' || $searchtype === 'Service Tag' || $searchtype === 'Host Name') {
        $executeType = 1;
    } else {
        $executeType = 2;
    }

    foreach ($res as $key => $row) {
        if ($row['itype'] == '3') {
            $dartname = $row['name'];
            $strType = '3';
            if ($varvalue == "") {
                $varvalue = 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=FALSE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
            } else {
                $varvalue .= $delimeter . 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=FALSE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
            }
        }
    }

    foreach ($scripVal as $value) {

        if (strpos($value, '=') !== false) {

            if (strpos($value, '=on') !== false) {

                $scripname = explode("=", $value);
                $dartname = $scripname[0];
                $strType = '3';

                if ($varvalue == "") {
                    $varvalue = 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=TRUE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                } else {
                    $varvalue .= $delimeter . 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=TRUE" . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                }
            } else {

                $scripname = explode("=", $value);

                if ($scripname[0] === $dart) {
                    continue;
                }

                $dartname = $scripname[0];
                $dartvalue = "";
                $dartvalue .= $scripname[1];

                for ($x = 2; $x < safe_count($scripname); $x++) {
                    $dartvalue .= ("=" . $scripname[$x]);
                }

                if (strpos($dartname, 'button') !== false) {
                } else {
                    if (strpos($dartname, 'GroupExecute') !== false) {

                        $exeval = explode("_", $dartname);
                        $execute = $exeval[0];
                    } else {
                        $res = $set;
                        foreach ($res as $key => $row) {
                            if ($row['name'] === $dartname) {

                                $strType = '';

                                if ($row['itype'] === '0') {
                                    $strType = '0';
                                } else if ($row['itype'] === '2') {
                                    if ($dartname === "ConfigurationPassword" || $dartname === "SimpleConfigPassword") {
                                        if ($dartvalue === '000000') {
                                            continue;
                                        } else {
                                            $strType = '4';
                                        }
                                    } else {
                                        $strType = '2';
                                    }
                                }

                                if ($varvalue == "") {
                                    $varvalue = 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=" . $dartvalue . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                                } else {
                                    $varvalue .= $delimeter . 'VarName=' . trim($dartname) . ";VarType=" . $strType . ";VarVal=" . $dartvalue . ";Action=SET" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";";
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if ($execute == "") {
        $varvalue .= $delimeter . $delimeter . "End";
    } else {
        $varvalue .= $delimeter . 'VarName=' . trim($execute) . ";VarType=7;VarVal=Execute;Action=RUN" . ";DartNum=" . $dart . ";VarScope=" . $executeType . ";" . $delimeter . $delimeter . "End";
    }
    $varvalueencoded = urlencode($varvalue);
    $resu = syncDataUsingNode($varvalueencoded, $resQry, $dart);
    return $resu;
}

function winDartConfSyncIBM($gvals, $searchtype, $resQry)
{

    $db = pdo_connect();

    $gval = str_replace("&ConfirmText=yes&Continue=Continue", "", $gvals);
    $val = explode("&ScripNo=", $gval);

    $changeVal = $val[1];

    $scripval = explode("&", $val[1]);
    $dart = $scripval[0];

    $sql = $db->prepare("SELECT name,itype FROM " . $GLOBALS['PREFIX'] . "node.WinVariables WHERE scop =?");
    $sql->execute([$dart]);
    $set = $sql->fetchAll();

    $res = $set;

    foreach ($res as $key => $row) {
        if (strpos($changeVal, $row['name']) !== false) {
            $oldVal = "&" . $row['name'];
            $newVal = "||@@||" . $row['name'];
            $changeVal = str_replace($oldVal, $newVal, $changeVal);
        }
    }
    if (strpos($changeVal, '&button') !== false) {
        $oldVal = '&button';
        $newVal = "||@@||" . 'button';
        $changeVal = str_replace($oldVal, $newVal, $changeVal);
    }

    $scripvals = explode("||@@||", $changeVal);
    $varvalue = "";

    $execute = "";

    $res1 = $set;
    foreach ($res1 as $key => $row) {
        if ($row['itype'] == '3') {
            $dartname = $row['name'];
            $dartname = str_replace("_", "$", $dartname);
            if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
                if ($varvalue == "") {
                    $varvalue = $dartname . "_" . $dart . "_BOOLEAN_SET_FALSE_MachineLevel";
                } else {
                    $varvalue .= "##" . $dartname . "_" . $dart . "_BOOLEAN_SET_FALSE_MachineLevel";
                }
            } else {
                if ($varvalue == "") {
                    $varvalue = $dartname . "_" . $dart . "_BOOLEAN_SET_FALSE";
                } else {
                    $varvalue .= "##" . $dartname . "_" . $dart . "_BOOLEAN_SET_FALSE";
                }
            }
        }
    }

    foreach ($scripvals as $value) {
        if (strpos($value, '=') !== false) {
            if (strpos($value, '=on') !== false) {
                $scripname = explode("=", $value);
                $dartname = $scripname[0];
                $dartname = str_replace("_", "$", $dartname);

                if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
                    if ($varvalue == "") {
                        $varvalue = $dartname . "_" . $dart . "_BOOLEAN_SET_TRUE_MachineLevel";
                    } else {
                        $varvalue .= "##" . $dartname . "_" . $dart . "_BOOLEAN_SET_TRUE_MachineLevel";
                    }
                } else {
                    if ($varvalue == "") {
                        $varvalue = $dartname . "_" . $dart . "_BOOLEAN_SET_TRUE";
                    } else {
                        $varvalue .= "##" . $dartname . "_" . $dart . "_BOOLEAN_SET_TRUE";
                    }
                }
            } else {
                $scripname = explode("=", $value);

                $dartname = $scripname[0];
                $dartvalue = $scripname[1];
                if ($dartvalue == "") {
                    $dartvalue = " ";
                }

                if ($scripname[0] === $dart) {
                    continue;
                }

                $dartname = $scripname[0];
                $dartvalue = "";

                $dartvalue .= $scripname[1];

                for ($x = 2; $x < safe_count($scripname); $x++) {
                    $dartvalue .= ("=" . $scripname[$x]);
                }

                $dartname = str_replace("_", "$", $dartname);
                $dartvalue = str_replace("_", "|!&@%=$*#!|", $dartvalue);

                if (strpos($dartname, 'button') === false) {
                    if (strpos($dartname, 'GroupExecute') !== false) {
                        $exeval = explode("$", $dartname);
                        $execute = $exeval[0];
                    } else {
                        $res = $set;
                        foreach ($res as $key => $row) {
                            if ($row['name'] == $dartname) {
                                $strType = '';
                                if ($row['itype'] == '0') {
                                    $strType = '_INTEGER_SET_';
                                } else if ($row['itype'] == '2') {
                                    if ($dartname === "ConfigurationPassword" || $dartname === "SimpleConfigPassword") {
                                        if ($dartvalue === '000000') {
                                            continue;
                                        } else {
                                            $strType = '_PASSWORD_SET_';
                                        }
                                    } else {
                                        $strType = '_STRING_SET_';
                                    }
                                }
                                if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
                                    if ($varvalue == "") {
                                        $varvalue = $dartname . "_" . $dart . $strType . $dartvalue . "_MachineLevel";
                                    } else {
                                        $varvalue .= "##" . $dartname . "_" . $dart . $strType . $dartvalue . "_MachineLevel";
                                    }
                                } else {
                                    if ($varvalue == "") {
                                        $varvalue = $dartname . "_" . $dart . $strType . $dartvalue;
                                    } else {
                                        $varvalue .= "##" . $dartname . "_" . $dart . $strType . $dartvalue;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if ($execute == "") {
        $varvalue .= "##End";
    } else {
        $varvalue .= "##" . $execute . "_" . $dart . "_RUN_SET_Execute##End";
    }
    $varvalueencoded = urlencode($varvalue);
    $resu = syncDataUsingNode($varvalueencoded, $resQry, $dart);
    return $resu;
}

function syncDataUsingNode($dartid, $resQry, $profileName)
{

    $cNode = $_SESSION["cNode"];

    $db = pdo_connect();

    $userName = $_SESSION["user"]["logged_username"];
    $agentId = $_SESSION["user"]["adminEmail"];
    $profileName = "Dart : " . $profileName . " - Configuration";

    $sqlSchedule = array();
    $sqlAudit = array();
    $type = 'SYNC';
    $nid = '';
    $notifyId = '';
    $versionNo = '';
    $machinelist = '';
    $currTime = time();
    $str = '';

    $sqlQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "node.schedule (bid, siteName, username, servicetag, type, scheduleTime, varValues, userId, idx, version, nid, profileName) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

    foreach ($resQry as $value) {
        $serviceTag = safe_addslashes($value['host']);
        $sitename = safe_addslashes($value['site']);
        $machinelist .= safe_addslashes($value['host']) . '~~';
        $dartConfg = "";
        $bid = "0";
        if ($value['machineOs'] == 'Android') {
            $dartConfg = $dartid;
        } else if ($value['machineOs'] == 'Mac') {
            $dartConfg = $dartid;
        } else if ($value['machineOs'] == 'Linux') {
            $dartConfg = "";
        } else {
            $dartConfg = $dartid;
        }

        if ($cNode === 'NODEJS') {
            $sqlQry->execute([$bid, $sitename, $userName, $serviceTag, $type, $currTime, $dartConfg, $agentId, $notifyId, $versionNo, $nid, $profileName]);
        } else if ($cNode === 'NODEPHP') {
            $sqlAudit[] = '(' . $bid . ',"' . $sitename . '","' . $userName . '","' . $serviceTag . '","' . $type . '",' . $currTime . ',"' . $dartConfg . '","' . $profileName . '","' . $agentId . '","' . $notifyId . '","' . $versionNo . '","' . $nid . '")';
            $sqlAuditQuer[] = $bid . ',' . $sitename . ',' . $userName . ',' . $serviceTag . ',' . $type . ',' . $currTime . ',' . urlencode($dartConfg) . ',' . urldecode($profileName) . ',' . $agentId . ',' . $notifyId . ',' . $versionNo . ',' . $nid;
            $str .= 'dboard---' . $serviceTag . '---' . $sitename . '---' . $serviceTag . '---' . 'any##';
        }
    }

    if ($cNode === 'NODEJS') {
        $res = $db->lastInsertId();
        return $res;
    } else if ($cNode === 'NODEPHP') {
        $auditStatus = insIntoAudit($sqlAuditQuer);
        return $str;
    }
}

function cancelPendingJobs_func()
{
    $db = pdo_connect();

    $sqlQry = $db->prepare("select machine,siteName,username,servicetag,varValues,profileName,scheduleTime from " . $GLOBALS['PREFIX'] . "node.schedule where bid =?");
    $bid = intval($bid);
    $sqlQry->execute([$bid]);
    $res = $sqlQry->fetchAll();

    $rmQrySchedule = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "node.schedule where bid =?");
    $rmQrySchedule->execute([$bid]);

    $rmQryTempAudit = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "node.tempaudit where bid =?");
    $rmQryTempAudit->execute([$bid]);

    $sqlaudit = array();
    $userId = $_SESSION["user"]["adminEmail"];

    $insQry = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "node.audit (`bid`, `machine`, `servicetag`, `username`, `createdtime`, `varvalues`, `profileName`, `siteName`, `userId`, `idx`, `userTriggeredTime`, `nodeTriggeredTime`, `clientExecutionTime`, `serverTime`, `type`, `dartStatus`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

    foreach ($res as $value) {
        $machine = $value['machine'];
        $sitename = $value['siteName'];
        $username = $value['username'];
        $servicetag = $value['servicetag'];
        $varValues = $value['varValues'];
        $profileName = $value['profileName'];
        $userTriggeredTime = $value['scheduleTime'];

        $insQry->execute([$bid, $machine, $servicetag, $username, $userTriggeredTime, $varValues, $profileName, $sitename, $userId, 0, $userTriggeredTime, $userTriggeredTime, $userTriggeredTime, $userTriggeredTime, 'SD', 6]);
    }

    $rest = $db->lastInsertId();
    return $rest;
}

function IsNullOrEmptyString($val)
{
    return (!isset($val) || trim($val) === '');
}

function getPreSoln()
{

    $db = pdo_connect();
    $profile_name = '';
    $nid = url::issetInRequest('nid') ? url::requestToText('nid') : '';

    $sqlQry = $db->prepare("select id,name,profile_name,auto_soln from  " . $GLOBALS['PREFIX'] . "event.Notifications where id=?");
    $sqlQry->execute([$nid]);
    $res = $sqlQry->fetch();

    if (safe_count($res) > 0) {
        $auto_soln = $res['auto_soln'];

        if ($auto_soln == 1 || $auto_soln == '1') {
            $profile_name = $res['profile_name'];
        } else {
            $profile_name = '';
        }
    }

    return $profile_name;
}

function getMEdetails()
{

    $db = pdo_connect();
    $cId = $_SESSION["user"]["cId"];

    $resetSql = $db->prepare("select A.cId,A.companyName,A.crmType,A.crmIP,A.crmKey,A.crmUsername,A.crmPassword from " . $GLOBALS['PREFIX'] . "agent.customerMaster A where A.cId=? limit ?");
    $resetSql->execute([$cId, 1]);
    $resetRes = $resetSql->fetch();

    if (safe_count($resetRes) > 0) {

        $customerId = $resetRes['cId'];
        $companyName = $resetRes['companyName'];
        $crmType = $resetRes['crmType'];
        $crmIP = $resetRes['crmIP'];
        $crmKey = $resetRes['crmKey'];
        $crmUsername = $resetRes['crmUsername'];
        $crmPassword = $resetRes['crmPassword'];
        $retVal = array("crmip" => $crmIP, "crmkey" => $crmKey, "crmType" => $crmType, "crmUsername" => $crmUsername, "crmPassword" => $crmPassword);
    } else {
        $retVal = array();
    }

    return $retVal;
}

function updateMETickets($JobRow, $userName)
{

    $crmDtl = getMEdetails();
    $db = pdo_connect();
    if (!empty($crmDtl)) {

        $crmIP = $crmDtl['crmip'];
        $crmkey = $crmDtl['crmkey'];
        $crmType = $crmDtl['crmType'];
        $crmUsername = $crmDtl['crmUsername'];
        $crmPassword = $crmDtl['crmPassword'];
        if ($crmType) {
            if (!empty($crmIP) && !empty($crmkey)) {

                $Jobs = explode('~~~~', $JobRow);
                $status = 'Resolved';
                for ($i = 0; $i < safe_count($Jobs); $i++) {
                    $Job = explode('~~', $Jobs[$i]);

                    $sitename = $Job[0];
                    $machine = $Job[1];
                    $versionNo = $Job[2];
                    $dartConfg = $Job[3];
                    $profileName = $Job[4];
                    $nid = $Job[5];
                    $consoleId = $Job[6];
                    $eventIdx = $Job[7];
                    $eventTime = $Job[8];
                    $notifyId = $Job[9];
                    $dartnum = $Job[10];

                    $sql = $db->prepare("select teid,consoleId,ticketId from  " . $GLOBALS['PREFIX'] . "event.ticketEvents where machineName=? and nid=? and eventDate=? and status=?");
                    $sql->execute([$machine, $nid, $eventTime, 'open']);
                    $notifyRes = $sql->fetchAll();
                    if (safe_count($notifyRes) > 0) {
                        foreach ($notifyRes as $value) {

                            $MEId = $value['ticketId'];
                            if ($crmType == "ME") {
                                $result = update_ME_notification($crmIP, $crmkey, $MEId, $profileName, $userName);
                                if ($result == 200) {
                                    $dt = time();

                                    $insQry = $db->prepare("update  " . $GLOBALS['PREFIX'] . "event.ticketEvents set status=?,actionid=?,actiontime = ? where nid = ? and eventDate=? and status =? and machineName=?");
                                    $insQry->execute(['close', $notifyId, $dt, $nid, $eventTime, 'open', $machine]);
                                }
                            } else if ($crmType == "SN") {
                                $dt = time();
                                $eventServertime = date("d-m-Y h:m:s", $dt);
                                $comment = $userName . " has taken action " . $profileName . ' on ' . $eventServertime;
                                close_SN_Notification($crmIP, $crmUsername, $crmPassword, $MEId, 7, $comment);
                                $dt = time();

                                $insQry = $db->prepare("update  " . $GLOBALS['PREFIX'] . "event.ticketEvents set status=?,actionid=?,actiontime =? where nid =? and eventDate=? and status =? and machineName=?");
                                $insQry->execute(['close', $notifyId, $dt, $nid, $eventTime, 'open', $machine]);
                            }
                        }
                    }
                }
            }
        }
    }
}

function update_ME_notification($crmIP, $crmKey, $MEId, $profileName, $userName)
{

    $operation_name = 'ADD_RESOLUTION';
    $dt = time();
    $eventServertime = date("d-m-Y h:m:s", $dt);
    $technician_key = $crmKey;
    $request_id = $MEId;
    $url = $crmIP . '/sdpapi/request/' . $request_id . '/resolution?OPERATION_NAME=' . $operation_name . '&TECHNICIAN_KEY=' . $technician_key . '&INPUT_DATA=';
    $comment = $userName . " has taken action " . $profileName . ' on ' . $eventServertime;
    $data1 = '<xml><Operation><Details><resolution><resolutiontext>' . $comment . '</resolutiontext></resolution></Details></Operation></xml>';

    $path = implode('/', array_map('rawurlencode', explode('/', $data1)));
    $url2 = $url . $path;
    $ch = curl_init();

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $url2));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    $result = curl_exec($ch);

    $xml = new SimpleXMLElement($result);
    $result = $xml->result->statuscode;
    curl_close($ch);

    if ($result == 200) {

        $ret = closeNotification($crmIP, $crmKey, $MEId, $comment);
    }
    return $result;
}

function closeNotification($meIP, $meKey, $meId, $comment)
{

    $operation_name = 'CLOSE_REQUEST';
    $technician_key = $meKey;

    $url = $meIP . '/sdpapi/request/' . $meId . '?OPERATION_NAME=' . $operation_name . '&TECHNICIAN_KEY=' . $technician_key . '&INPUT_DATA=';
    $data = '<xml><Operation><Details><closeAccepted>Accepted</closeAccepted><closeComment>' . $comment . '</closeComment></Details></Operation></xml>';

    $path = implode('/', array_map('rawurlencode', explode('/', $data)));
    $url2 = $url . $path;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, $url2);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $url2));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    $result = curl_exec($ch);
    $xml = new SimpleXMLElement($result);
    $reponse = array();
    $reponse = (array) $xml->response->operation->result[0]->statuscode;
    curl_close($ch);
    return $reponse[0];
}

function insertSWDMETickets($userName, $packageName, $selectiontype, $searchValue)
{

    $crmDtl = getMEdetails();
    $db = pdo_connect();
    if (!empty($crmDtl)) {

        $crmIP = $crmDtl['crmip'];
        $crmkey = $crmDtl['crmkey'];
        $crmType = $crmDtl['crmType'];

        if (!empty($crmIP) && !empty($crmkey)) {

            $priority = 2;
            $servertime = time();
            $eventServertime = date("d-m-Y h:m:s", $servertime);
            $eventServertime1 = date("Y-m-d", $servertime);
            $descriptionval = 'Package ' . $packageName . ' distributed to' . $selectiontype . 'on ' . $eventServertime;
            $status = 'open';
            $tickettype = 4;

            $ticketEventsql = $db->prepare("insert into  " . $GLOBALS['PREFIX'] . "event.ticketEvents set siteName=?,consoleId= ?,ticketType=?,idx = ?,scrip = ?,machineName = ?,"
                . "eventDate =?,servertime = ?,username =?,ticketSub =?,priority =?,ticketDescription =?,status =?,crmType =?,nid =?");
            $ticketEventsql->execute([$searchValue, 0, $tickettype, 0, 0, $searchValue, $eventServertime1, $servertime, $userName, $packageName, $priority, $descriptionval, $status, $crmType, 0]);
        }
    }
}

function close_SN_Notification($meIP, $crmUsername, $crmPassword, $ticketSysId, $status, $resolution)
{
    try {

        $username = $crmUsername;
        $password = $crmPassword;
        $header = array('Content-Type: application/json', 'Accept: application/json');
        $action = "PATCH";
        $tid = explode("##", $ticketSysId);
        $sysid = $tid[0];
        $url = $meIP . '/' . $sysid;
        $data_string = '{"state": "' . $status . '","close_code" : "Solved (Permanently)","close_notes":"' . $resolution . '"}';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $crmUsername . ":" . $crmPassword);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $action);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        $arrayData = safe_json_decode($result, true);
        return $arrayData;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function add_newprofileData()
{

    $postObj = safe_json_decode(url::requestToAny('data'));
    $res = getmgroupuniqueid();

    $api_response = DashboardAPI("POST", "/modifyBaseProfileDetails", array("function" => "modifyBaseProfileDetails", "mgroupuniq" => $res['mgroupuniq'], "parent_mgroupuniq" => $res['parentmgroupid'], "postdata" => $postObj));
    echo json_encode($api_response);
}

function edit_profileList()
{
    $postObj = safe_json_decode(url::requestToAny('edit_profile'));
    $res = getmgroupuniqueid();
    $api_response = DashboardAPI("POST", "/modifyBaseProfileDetails", array("function" => "modifyBaseProfileDetails", "mgroupuniq" => $res['mgroupuniq'], "parent_mgroupuniq" => $res['parentmgroupid'], "postdata" => $postObj));
    echo json_encode($api_response->status);
}

function getmgroupuniqueid()
{
    $db = pdo_connect();
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $rparentValue = $_SESSION['rparentName'];
    $pass = $_SESSION['passlevel'];
    if ($searchType == 'Sites') {

        $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$searchValue]);
        $sqlRes = $sql->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sqlRes['mgroupuniq'];
    } else if ($searchType == 'Groups') {

        $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$searchValue]);
        $sqlRes = $sql->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sqlRes['mgroupuniq'];
    } else {
        if ($pass == 'Groups' || $pass == '') {
            $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
            $sql->execute(["%$searchValue%"]);
            $sqlRes = $sql->fetch();

            $sqltest = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where host = ? order by last desc limit 1");
            $sqltest->execute([$searchValue]);
            $sqlRestest = $sqltest->fetch();
            $value = $sqlRestest['site'];

            $sql1 = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
            $sql1->execute([$value]);
            $sql1Res = $sql1->fetch();
            $mgroupid = $sqlRes['mgroupuniq'];
            $mgroupidParent = $sql1Res['mgroupuniq'];
        } else {
            $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
            $sql->execute(["%$rparentValue:$searchValue%"]);
            $sqlRes = $sql->fetch();

            $sql1 = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
            $sql1->execute([$rparentValue]);
            $sql1Res = $sql1->fetch();
            $mgroupid = $sqlRes['mgroupuniq'];
            $mgroupidParent = $sql1Res['mgroupuniq'];
        }
    }

    return array("mgroupuniq" => $mgroupid, "parentmgroupid" => $mgroupidParent);
}

function getL1tiles()
{
    $html = "";
    $type = url::requestToAny('type');
    $tiletype = url::requestToAny('tiletype');
    $Res = getmgroupuniqueid();
    $sortObject = new stdClass();
    $colname = "type";
    $sortObject->$colname = "L1";
    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    $main_arr = $arr_response["Main"];

    $sortObject = new stdClass();
    $colname = "type";
    $sortObject->$colname = "L2";
    $api_response2 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response2 = safe_json_decode(json_encode($api_response2), true);
    $main_arr2 = $arr_response2["Main"];

    $sortObject = new stdClass();
    $colname = "type";
    $sortObject->$colname = "L3";
    $api_response3 = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response3 = safe_json_decode(json_encode($api_response3), true);
    $main_arr3 = $arr_response3["Main"];

    if ($type == "L1") {
        $arr = [];
        $i = 1;
        foreach ($main_arr as $key => $val) {
            $L1_tiles = $val['profile'];
            $pageId = $val['page'];
            $parentId = $val['parentId'];
            $ena_dis = $val['Enable/Disable'];
            $arr[$i] = $L1_tiles;
            $finstr = $pageId . '##' . $parentId . '##' . $L1_tiles;
            if ($ena_dis == 1) {
                $html .= '<option selected="" id="profile' . $i . '" value="' . $finstr . '">' . $L1_tiles . '</option>';
            }
            $i++;
        }
        echo $html;
    } else if ($type == "L2") {
        $arr = [];
        $i = 0;
        foreach ($main_arr2 as $key => $val) {
            $pageId = $val['page'];
            $parentId = $val['parentId'];
            $L1_tiles = $val['profile'];
            $arr[$i] = $L1_tiles;
            $finstr = $pageId . '##' . $parentId . '##' . $L1_tiles;
            $html .= '<option selected="" id="profile' . $i . '" value="' . $finstr . '">' . $L1_tiles . '</option>';
            $i++;
        }
        echo $html;
    } else if ($type == "L3") {
        $arr = [];
        $i = 0;
        foreach ($main_arr3 as $key => $val) {
            $pageId = $val['page'];
            $parentId = $val['parentId'];
            $L1_tiles = $val['profile'];
            $arr[$i] = $L1_tiles;
            $finstr = $pageId . '##' . $parentId . '##' . $L1_tiles;
            $html .= '<option selected="" id="profile' . $i . '" value="' . $finstr . '">' . $L1_tiles . '</option>';
            $i++;
        }
        echo $html;
    }
}

function delete_profileList()
{
    $mid = url::requestToAny('mid');
    $Res = getmgroupuniqueid();
    $api_response = DashboardAPI("POST", "/deleteBaseProfileDetails/$mid", array("function" => "deleteBaseProfileDetails", "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    echo json_encode($api_response->status);
}

function getOrigin()
{
    $frmWindow = $_SESSION['fromwindow'];
    echo $frmWindow;
}

function notificationwin()
{
    $db = pdo_connect();
    $check = $_SESSION['notifyselArr'];
    $val = explode("~~", $check);
    $site = $val[2];
    $machine = $site . ':' . $val[3];
    $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
    $sql->execute(["%$machine%"]);
    $sqlRes = $sql->fetch();
    $sql1 = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ?");
    $sql1->execute([$site]);
    $sql1Res = $sql1->fetch();

    $mgroupid = $sqlRes['mgroupuniq'];
    $mgroupidParent = $sql1Res['mgroupuniq'];
    return array("mgroupuniq" => $mgroupid, "parentmgroupid" => $mgroupidParent);
}

function getTileNames()
{

    echo "Table analytics_test.AutoHealTable updated";
    return;
    $db = pdo_connect();
    $sql = $db->prepare("select distinct V.mgroupuniq as mgroupuniq,V.valu,M.name from " . $GLOBALS['PREFIX'] . "core.VarValues V join " . $GLOBALS['PREFIX'] . "core.MachineGroups M  where V.mgroupuniq = M.mgroupuniq and varuniq IN (select varuniq from " . $GLOBALS['PREFIX'] . "core.Variables where name = 'S00304_BaseProfiles');");
    $sql->execute();
    $res = $sql->fetchAll();

    foreach ($res as $key => $val) {
        $mgroupuniq = $val['mgroupuniq'];

        $sortObject = new stdClass();
        $colname = "type";
        $sortObject->$colname = "L3";

        $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "sort" => $sortObject, "mgroupuniq" => $mgroupuniq, "parent_mgroupuniq" => $mgroupuniq));
        $arr_response = safe_json_decode(json_encode($api_response), true);
        $main_arr = $arr_response["Main"];
        foreach ($main_arr as $key => $val) {
            if ($val['type'] == 'L3') {
                $profileVal = $main_arr[$key]['varValue'];
                $profileVal = "[" . $profileVal . "]";
                $tileName = $main_arr[$key]['shortDesc'];

                $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "analytics_test.AutoHealTable where profilename=?");
                $sql->execute([$profileVal]);
                $res = $sql->fetch();

                if (!$res) {
                    $sql1 = $db->prepare("delete from  " . $GLOBALS['PREFIX'] . "analytics_test.AutoHealTable where profilename=? and tilename=?");
                    $sql1->execute([$profileVal, $tileName]);
                    $res1 = $db->lastInsertId();

                    $sql2 = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "analytics_test.AutoHealTable (profilename,tilename) VALUES (?,?)");
                    $sql2->execute([$profileVal, $tileName]);
                    $res2 = $db->lastInsertId();
                }
            }
        }
    }

    echo "Table analytics_test.AutoHealTable updated";
}
