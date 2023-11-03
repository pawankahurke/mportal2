<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';


nhRole::dieIfnoRoles(['site', 'siteexport']); // roles: site, siteexport


if (url::requestToAny('function') != '') { // roles: site, siteexport
    $function  = url::requestToAny('function'); // roles: site, siteexport
    $dartnum = url::requestToAny('dart');
    $function($dartnum);
}


function updateIosProfile($data)
{

    $db = pdo_connect();

    if (isset($data['action'])) {
        $searchname = $data['siteName'] . ':' . $data['machineName'];
    } else {
        $searchVal = $_SESSION['searchValue'];
        $searchType = $_SESSION['searchType'];

        if ($searchType == 'Sites') {
            $searchname = $searchVal;
        } else if ($searchType == 'ServiceTag') {
            $searchname = $_SESSION['rsiteName'] . ':' . $searchVal;
        }
    }

    $now = time();
    $dartNumber = $data['dartNum'];

    foreach ($data as $key => $value) {
        if ($key != 'function') {
            if ($key != 'dartNum' && $key != 'dartName') {
                $varName = '';

                if ($dartNumber == '2001') {
                    $varName = $data['value'];
                    $value = '';
                } else {
                    $names = explode('_', $key);
                    $varName = $names[0];
                }

                $sql1 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "iosprofile.mdmVariables where varName = ? AND dartNum = ? ");
                $sql1->execute([$varName, $dartNumber]);
                $result = $sql1->fetch();

                $varId = $result['varId'];

                $sql2 = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmModifiedVariables (varId, dartNum, scope,scopeValue, varName, varValue, lastModified ) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE varValue = ? , lastModified = ? ");
                $sql2->execute([$varId, $dartNumber, $searchType, $searchVal, $varName, $value, $now, $value, $now]);
                $id = $db->lastInsertId();
            }
        }
    }
    if ($id) {
        echo "Successfully Update";
    } else {
        echo "Failure in Update";
    }
}


function updateConfiguration($scope, $priority, $dartNum, $xml, $time, $data)
{

    $db = pdo_connect();
    $time = strtotime($time);
    $level = '';
    $userName = $_SESSION['user']['logged_username'];

    if ($dartNum == '2001') {
        $level = 2;
    } else {
        $level = 1;
    }

    if ($priority == 'p3') {


        $machines = $db->prepare("SELECT host FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? ");
        $machines->execute([$scope]);
        $machinesRes = $machines->fetchAll();

        foreach ($machinesRes as $id => $values) {
            $machineName = $values['host'];
            foreach ($xml as $key => $value) {

                $sql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, priority, dartNum, xml, executeTime,level,status,machine,agentName) VALUES (?,?,?,?,?,?,?,?,?)");
                $sql->execute([$scope, $priority, $dartNum, $value, $time, $level, 0, $machineName, $userName]);
                $xmlUpdate = $db->lastInsertId();
            }
        }
        if ($dartNum == '2001') {
            syncDevice($machinesRes);
        }
    } else if ($priority == 'p1') {
        if (isset($data['action'])) {
            $machineName = $data['machineName'];
            $userName    = $data['parentName'];
            $scope       = $data['siteName'];
        } else {
            $machineName = $_SESSION['searchValue'];
        }

        foreach ($xml as $key => $value) {
            $sql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, priority, dartNum, xml, executeTime,level,status,machine,agentName) VALUES (?,?,?,?,?,?,?,?,?)");
            $sql->execute([$scope, $priority, $dartNum, $value, $time, $level, 0, $machineName, $userName]);
            $xmlUpdate = $db->lastInsertId();
        }
        if ($dartNum == '2001') {
            $machine[]['host'] = $machineName;
            syncDevice($machine);
        }
    } else if ($priority == 'p2') {

        $groupName = $_SESSION['rparentName'];


        $group = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? ");
        $group->execute([$groupName]);
        $groupRes = $group->fetch();

        $groupId = $groupRes['mgroupid'];

        $groupMac = $db->prepare("SELECT site,host FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as mgm " .
            "on mg.mgroupuniq = mgm.mgroupuniq join " . $GLOBALS['PREFIX'] . "core.Census as c on mgm.censusuniq = c.censusuniq WHERE mg.mgroupid = ?");
        $groupMac->execute([$groupId]);
        $groupMacRes = $groupMac->fetchAll();

        foreach ($groupMacRes as $keys => $val) {
            $siteName = $val['site'];
            $machine  = $val['host'];
            foreach ($xml as $key => $value) {
                $groupInsert = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope,machine,priority,dartNum,xml,executeTime,level,status,agentName) VALUES (?,?,?,?,?,?,?,?,?) ");
                $groupInsert->execute([$siteName, $machine, $priority, $dartNum, $value, $time, $level, 0, $userName]);
                $xmlUpdate = $db->lastInsertId();
            }
        }
        if ($dartNum == '2001') {
            syncDevice($groupMacRes);
        }
    }

    if ($xmlUpdate) {
        echo "xml insertion successful";
    } else {
        echo "xml insertion failed";
    }
}


function deleteXml($dartNum)
{

    $searchVal = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    $db = pdo_connect();

    if ($searchType == 'Sites') {
        $searchname = $searchVal;
        $priority = 'p3';
    } else if ($searchType == 'ServiceTag') {
        $searchname = $_SESSION['rparentName'] . ':' . $searchVal;
        $priority  = 'p1';
    } else if ($searchType == 'Groups') {
        $priority = 'p2';
        $groupName = $_SESSION['searchValue'];

        $group = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? ");
        $group->execute([$groupName]);
        $groupRes = $group->fetch();

        $groupId = $groupRes['mgroupid'];
        $groupMac = $db->prepare("SELECT site,host FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as mgm " .
            "on mg.mgroupuniq = mgm.mgroupuniq join " . $GLOBALS['PREFIX'] . "core.Census as c on mgm.censusuniq = c.censusuniq WHERE mg.mgroupid = ?");
        $groupMac->execute([$groupId]);
        $groupMacRes = $groupMac->fetchAll();
    }

    if ($searchType == 'Groups') {
        foreach ($groupMacRes as $key => $value) {
            $machineName = $value['host'];

            $sql2 = $db->prepare("DELETE from " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig WHERE dartNum = ? and machine = ? ");
            $sql2->execute([$dartNum, $machineName]);
            $xmlDelete = $sql2->lastInsertId();
        }
    } elseif ($searchType == 'Sites') {
        $sql2 = $db->prepare("DELETE from " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig WHERE dartNum = ? and scope = ? ");
        $sql2->execute([$dartNum, $searchVal]);
        $xmlDelete = $sql2->lastInsertId();
    } elseif ($searchType == 'ServiceTag') {
        $sql2 = $db->prepare("DELETE from " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig WHERE dartNum = ? and machine = ? ");
        $sql2->execute([$dartNum, $searchVal]);
        $xmlDelete = $sql2->lastInsertId();
    }

    $sql3 = $db->prepare("SELECT defaultXml from " . $GLOBALS['PREFIX'] . "iosprofile.Scripsnew WHERE num = ? ");
    $sql3->execute([$dartNum]);
    $xml = $sql3->fetch();

    $siteName = $searchVal;
    $time = date('d-m-Y H:i:s');

    if ($dartNum == '2021') {
        $profileName = '';

        $sql4 = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime WHERE dartNum = ?");
        $sql4->execute([$dartNum]);
        $id = $db->lastInsertId();

        updateProfileConfiguration($siteName, $priority, $dartNum, $xml, $time, $profileName, 0, '');
    } else {
        updateConfiguration($siteName, $priority, $dartNum, $xml, $time, '');
    }

    if ($xmlDelete) {
        echo "scrip deletion successful";
    } else {
        echo "scrip deletion failed";
    }
}


function updateCronTable($profile, $time, $configuration, $status, $dartNum)
{

    $timeIndex = [];
    $startTime = '';
    $endTime   = '';

    $db = pdo_connect();

    if (!empty($time)) {
        $timeIndex = explode('-', $time);
        if (strpos($timeIndex[0], ':') !== false) {
            $time1 = explode(':', $timeIndex[0]);
            $startTime = mktime($time1[0], $time1[1]);
        } else {
            $startTime = mktime($timeIndex[0]);
        }
        if (strpos($timeIndex[1], ':') !== false) {
            $time2 = explode(':', $timeIndex[1]);
            $endTime = mktime($time2[0], $time2[1]);
        } else {
            $endTime = mktime($timeIndex[1]);
        }
    } else {
        $startTime = time();
    }

    if ($startTime < time()) {
        $startTime = $startTime + 86400;
    }

    $sql = $db->prepare("INSERT INTO cronRunTime (profileName,startTime,endTime,configuration,status,dartNum) VALUES (?,?,?,?,?,) "
        . "ON DUPLICATE KEY UPDATE startTime = ?,endTime =?, configuration =?, status = ?");
    $sql->execute([$profile, $startTime, $endTime, $configuration, $status, $dartNum, $startTime, $endTime, $configuration, $status]);
    $resultId = $db->lastInsertId();


    $sql1 = $db->prepare("INSERT INTO cronRunTime (profileName,startTime,endTime,configuration,status,dartNum) VALUES (?,?,?,?,?,) "
        . "ON DUPLICATE KEY UPDATE startTime = ?,endTime =?, configuration =?, status = ?");
    $sql->execute([$profile . Remove, $startTime, $endTime, $configuration, $status, $dartNum, $startTime, $endTime, '', $status]);
    $resultId = $db->lastInsertId();

    if ($resultId) {
        echo "cron run time inserted successfully";
    } else {
        echo "cron run time insertion failed";
    }
}


function updateProfileConfiguration($siteName, $priority, $dartNum, $xml, $time, $profileName, $level, $data)
{

    $db = pdo_connect();
    if ($time != '') {
        $time = strtotime($time);
    } else {
        $time = '';
    }
    $userName = $_SESSION['user']['logged_username'];

    $removePolicySql = $db->prepare("SELECT defaultXml from " . $GLOBALS['PREFIX'] . "iosprofile.Scripsnew WHERE num = 2021 ");
    $removePolicySql->execute();
    $removePolciyRes = $removePolicySql->fetch();
    $removeXml = $removePolciyRes['defaultXml'];

    if ($priority == 'p3') {
        $machines = $db->prepare("SELECT host FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE site =?");
        $machines->execute([$siteName]);
        $machinesRes = $machines->fetchAll();

        $str1 = [];
        $str2 = [];

        foreach ($machinesRes as $id => $machineid) {

            foreach ($xml as $key => $value) {
                $temp = $profileName[$key];
                $machineName = $machineid['host'];

                if ($time == '') {
                    $time = time();

                    $insertSql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, priority, dartNum, xml, executeTime,level,machine,mapId,agentName) VALUES (?,?,?,?,?,?,?,?,? )");
                    $insertSql->execute([$siteName, $priority, $dartNum, $removeXml, $time, $level, $machineName, 1, $userName]);
                    $xmlId = $db->lastInsertId();
                    $str1[$temp] .= $xmlId . ',';
                }

                $sql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, priority, dartNum, xml, executeTime,level,machine,mapId,agentName) VALUES (?,?,?,?,?,?,?,?,? )");
                $sql->execute([$siteName, $priority, $dartNum, $value, $time, $level, $machineName, 2, $userName]);
                $xmlUpdate = $db->lastInsertId();
                $xmlId2 = $db->lastInsertId();
                $str1[$temp] .= $xmlId2 . ',';
            }
        }
        foreach ($str1 as $profilename1 => $xmlid1) {
            $xmlid1 = rtrim($xmlid1, ',');
            $sql1 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime SET xmlid = ? WHERE profileName = ?");
            $sql1->execute([$xmlid1, $profilename1 . 'Remove']);
            $cronInsert1 = $db->lastInsertId();
        }
        foreach ($str2 as $profilename2 => $xmlid2) {
            $xmlid2 = rtrim($xmlid2, ',');
            $sql2 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime SET xmlid = ? WHERE profileName = ?");
            $sql2->execute([$xmlid2, $profilename2]);
            $cronInsert1 = $db->lastInsertId();
        }
    } elseif ($priority == 'p1') {

        if (isset($data['action'])) {
            $machineName = $data['machineName'];
            $userName    = $data['parentName'];
        } else {
            $machineName = $_SESSION['searchValue'];
        }

        foreach ($xml as $key => $value) {
            if ($time == '') {
                $time = time();
                $insertSql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, priority, dartNum, xml, executeTime,level,machine,mapId,agentName) VALUES (?,?,?,?,?,?,?,?,?)");
                $insertSql->execute([$siteName, $priority, $dartNum, $removeXml, $time, $level, $machineName, 1, $userName]);
                $xmlId = $db->lastInsertId();

                $sql1 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime SET xmlid =?  WHERE profileName = ?");
                $sql1->execute([$xmlId, $profileName[$key] . 'Remove']);
                $cronInsert1 = $db->lastInsertId();
            }

            $sql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope, priority, dartNum, xml, executeTime,level,machine,mapId,agentName) VALUES (?,?,?,?,?,?,?,?,?)");
            $sql->execute([$siteName, $priority, $dartNum, $value, $time, $level, $machineName, 2, $userName]);
            $xmlUpdate = $db->lastInsertId();
            $xmlId2 = $db->lastInsertId();

            $sql1 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime SET xmlid =?  WHERE profileName =? ");
            $sql1->execute([$xmlId2, $profileName[$key]]);
            $cronInsert = $db->lastInsertId();
        }
    } elseif ($priority == 'p2') {

        $groupName = $_SESSION['searchValue'];
        $str1 = [];
        $str2 = [];

        $group = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? ");
        $group->execute([$groupName]);
        $groupRes = $sql->fetch();
        $groupId = $groupRes['mgroupid'];

        $groupMac = $db->prepare("SELECT site,host FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as mgm " .
            "on mg.mgroupuniq = mgm.mgroupuniq join " . $GLOBALS['PREFIX'] . "core.Census as c on mgm.censusuniq = c.censusuniq WHERE mg.mgroupid = ?");
        $groupMac->execute([$groupId]);
        $groupMacRes = $groupMac->fetchAll();

        foreach ($groupMacRes as $id => $value) {
            $site  = $value['site'];
            $machine = $value['host'];

            foreach ($xml as $key => $val) {
                $temp = $profileName[$key];

                if ($time == '') {
                    $time = time();
                    $removeSql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope,machine,priority,dartNum,xml,executeTime,level,mapId,agentName) VALUES (" .
                        "?,?,?,?,?,?,?,?,?)");
                    $removeSql->execute([$site, $machine, $priority, $dartNum, $removeXml, $time, $level, 1, $userName]);
                    $xmlId = $db->lastInsertId();
                    $str1[$temp] .= $xmlId . ',';
                }

                $applySql = $db->prepare("INSERT into " . $GLOBALS['PREFIX'] . "iosprofile.mdmConfig (scope,machine,priority,dartNum,xml,executeTime,level,mapId,agentName) VALUES (" .
                    "?,?,?,?,?,?,?,?,?)");
                $applySql->execute([$site, $machine, $priority, $dartNum, $val, $time, $level, 2, $userName]);
                $xmlUpdate = $db->lastInsertId();
                $xmlId2 = $db->lastInsertId();
                $str2[$temp] .= $xmlId2 . ',';
            }
        }
        foreach ($str1 as $profilename1 => $xmlid1) {
            $xmlid1 = rtrim($xmlid1, ',');
            $sql1 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime SET xmlid = ? WHERE profileName = ? ");
            $sql1->execute([$xmlid1, $profilename1 . 'Remove']);
            $cronInsert1 = $db->lastInsertId();
        }
        foreach ($str2 as $profilename2 => $xmlid2) {
            $xmlid2 = rtrim($xmlid2, ',');
            $sql2 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "iosprofile.cronRunTime SET xmlid = ? WHERE profileName = ? ");
            $sql2->execute([$xmlid2, $profilename2]);
            $cronInsert1 = $db->lastInsertId();
        }
    }

    if ($xmlUpdate && $cronInsert1) {
        echo "successfully inserted";
    } else {
        echo "xml insertion failed";
    }
}


function generate_random_letters()
{
    $random = '';
    for ($i = 0; $i < 4; $i++) {
        $random .= chr(rand(ord('A'), ord('E')));
        $random .= chr(rand(ord('1'), ord('9')));
    }
    $random .= "-";
    for ($i = 0; $i < 2; $i++) {
        $random .= chr(rand(ord('A'), ord('E')));
        $random .= chr(rand(ord('1'), ord('9')));
    }
    $random .= "-";
    for ($i = 0; $i < 2; $i++) {
        $random .= chr(rand(ord('A'), ord('E')));
        $random .= chr(rand(ord('1'), ord('9')));
    }
    $random .= "-";
    for ($i = 0; $i < 2; $i++) {
        $random .= chr(rand(ord('A'), ord('E')));
        $random .= chr(rand(ord('1'), ord('9')));
    }
    $random .= "-";
    for ($i = 0; $i < 6; $i++) {
        $random .= chr(rand(ord('A'), ord('E')));
        $random .= chr(rand(ord('1'), ord('9')));
    }
    return $random;
}


function get_payload_details($dartnum, $index)
{

    $db = pdo_connect();
    $sql = $db->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "iosprofile.iosPayload WHERE dartNum = ? AND level = ? ");
    $sql->execute([$dartnum, $index]);
    $result = $db->lastInsertId();
    return $result;
}


function generateStringTag($xml, $dict, $string_arr)
{

    foreach ($string_arr as $keys => $val) {
        $key        = $xml->createElement("key");
        $keyText    = $xml->createTextNode($keys);
        $key->appendChild($keyText);

        $string     = $xml->createElement("string");
        $stringText = $xml->createTextNode($val);
        $string->appendChild($stringText);

        $dict->appendChild($key);
        $dict->appendChild($string);
    }
    return;
}


function PayloadVersion($xml, $dict)
{
    $key     = $xml->createElement("key");
    $keyText = $xml->createTextNode("PayloadVersion");
    $key->appendChild($keyText);

    $string     = $xml->createElement("integer");
    $stringText = $xml->createTextNode("1");
    $string->appendChild($stringText);

    $dict->appendChild($key);
    $dict->appendChild($string);
}

function processRequest()
{
    $data = $_REQUEST;
    createXml($data);
}

function createXml($dataParse)
{
    $dartnum = $dataParse['dartNum'];
    echo $dartnum;
    $payload_uuid = generate_random_letters();
    $payloadDetails1 = get_payload_details($dartnum, 1);
    $payloadDetails2 = get_payload_details($dartnum, 2);

    $count = 0;
    $index = 0;
    $profile = [];
    foreach ($dataParse as $key => $value) {
        if (strpos($key, '_profile')) {
            array_push($profile, $key);
            $count++;
        }
    }

    $xml = new DOMDocument("1.0");
    $xml->formatOutput = true;
    $dict1 = $xml->createElement("dict");
    $key1     = $xml->createElement("key");
    $keyText1 = $xml->createTextNode('PayloadContent');
    $key1->appendChild($keyText1);
    $dict1->appendChild($key1);

    $array = $xml->createElement("array");
    $dict = $xml->createElement("dict");

    $str1 = array(
        "PayloadDescription" => $payloadDetails1[0]['PayloadDescription'], "PayloadDisplayName" => $payloadDetails1[0]['PayloadDisplayName'],
        "PayloadIdentifier" => $payloadDetails1[0]['PayloadIdentifier'], "PayloadOrganization" => $payloadDetails1[0]['PayloadOrganization'],
        "PayloadType" => $payloadDetails1[0]['PayloadType'], "PayloadUUID" => $payload_uuid
    );

    generateStringTag($xml, $dict, $str1);
    PayloadVersion($xml, $dict);

    if ($dartnum == '2001') {
        $xmlGenerated = [];
        $commandVar = $dataParse['value'];
        $command = 'CMD:' . $commandVar;
        array_push($xmlGenerated, $command);
    } elseif ($dartnum == '2021') {

        $textData = trim($dataParse['Profile_profile_6']);

        $val = explode(PHP_EOL, $textData);
        $xmlGenerated = [];
        $profileName = [];
        $profile_config = [];

        for ($i = 0; $i < safe_sizeof($val); $i++) {

            $xml = new DOMDocument("1.0");
            $xml->formatOutput = true;
            $dict1 = $xml->createElement("dict");
            $key1 = $xml->createElement("key");
            $keyText1 = $xml->createTextNode('PayloadContent');
            $key1->appendChild($keyText1);
            $dict1->appendChild($key1);

            $array = $xml->createElement("array");
            $dict = $xml->createElement("dict");

            $str1 = array(
                "PayloadDescription" => $payloadDetails1[0]['PayloadDescription'], "PayloadDisplayName" => $payloadDetails1[0]['PayloadDisplayName'],
                "PayloadIdentifier" => $payloadDetails1[0]['PayloadIdentifier'], "PayloadOrganization" => $payloadDetails1[0]['PayloadOrganization'],
                "PayloadType" => $payloadDetails1[0]['PayloadType'], "PayloadUUID" => $payload_uuid
            );

            $data = explode(',', $val[$i]);
            $type = $data[0];
            array_push($profileName, $data[1]);

            $configuration = '';
            for ($y = 2; $y < safe_sizeof($data); $y++) {
                $configuration .= $data[$y];
                $configuration .= ',';
            }
            $configuration = rtrim($configuration, ',');
            $profile_config[$data[1]] = $configuration;

            if ($type == '1' || $type == 1) {

                $str1['PayloadType'] = 'com.apple.app.lock';
                generateStringTag($xml, $dict, $str1);
                PayloadVersion($xml, $dict);

                $key = $xml->createElement("key");
                $keyText = $xml->createTextNode("App");
                $key->appendChild($keyText);
                $dict->appendChild($key);
                $array1 = $xml->createElement("dict");
                for ($j = 2; $j < safe_sizeof($data); $j++) {
                    $key1 = $xml->createElement("key");
                    $key1Text = $xml->createTextNode("Identifier");
                    $key1->appendChild($key1Text);
                    $array1->appendChild($key1);
                    $package = $xml->createElement("string");
                    $packageText = $xml->createTextNode(trim($data[$j]));
                    $package->appendChild($packageText);
                    $array1->appendChild($package);
                }
            } elseif ($type == '0' || $type == 0) {

                generateStringTag($xml, $dict, $str1);
                PayloadVersion($xml, $dict);

                $key = $xml->createElement("key");
                $keyText = $xml->createTextNode("whitelistedAppBundleIDs");
                $key->appendChild($keyText);
                $dict->appendChild($key);
                $array1 = $xml->createElement("array");
                for ($j = 2; $j < safe_sizeof($data); $j++) {
                    $package = $xml->createElement("string");
                    $packageText = $xml->createTextNode(trim($data[$j]));
                    $package->appendChild($packageText);
                    $array1->appendChild($package);
                }
            } elseif ($type == '2' || $type == 2) {

                generateStringTag($xml, $dict, $str1);
                PayloadVersion($xml, $dict);

                $key = $xml->createElement("key");
                $keyText = $xml->createTextNode("blacklistedAppBundleIDs");
                $key->appendChild($keyText);
                $dict->appendChild($key);
                $array1 = $xml->createElement("array");
                for ($j = 2; $j < safe_sizeof($data); $j++) {
                    $package = $xml->createElement("string");
                    $packageText = $xml->createTextNode(trim($data[$j]));
                    $package->appendChild($packageText);
                    $array1->appendChild($package);
                }
            }
            $dict->appendChild($array1);
            $array->appendChild($dict);
            $dict1->appendChild($array);
            $xml->appendChild($dict1);

            $str2 = array(
                "PayloadDescription" => $payloadDetails2[0]['PayloadDescription'], "PayloadDisplayName" => $payloadDetails2[0]['PayloadDisplayName'],
                "PayloadIdentifier" => $payloadDetails2[0]['PayloadIdentifier'], "PayloadOrganization" => $payloadDetails2[0]['PayloadOrganization'],
                "PayloadType" => $payloadDetails2[0]['PayloadType'], "PayloadUUID" => $payload_uuid
            );

            generateStringTag($xml, $dict1, $str2);
            PayloadVersion($xml, $dict1);

            array_push($xmlGenerated, $xml->saveXML());
        }

        $tableData = trim($dataParse['Configuration_profile_6']);
        $vals = explode(PHP_EOL, $tableData);

        if (safe_count($vals) > 1) {
            for ($x = 0; $x < safe_sizeof($vals); $x++) {
                $profileName1 = '';
                $cronExecuteTime = '';
                $data = explode(',', $val[$x]);
                $tableValue = explode(',', $vals[$x]);

                $profileName1 = trim($tableValue[0]);
                $cronExecuteTime = isset($tableValue[1]) ? $tableValue[1] : '';
                if ($cronExecuteTime != '') {
                    $status = 0;
                } else {
                    $status = 1;
                }
                $package = $profile_config[$profileName1];
                updateCronTable($profileName1, $cronExecuteTime, $package, $status, $dartnum);
            }
        }
    } else if ($dartnum == '2024') {
        $string = trim($dataParse['webDetails_profile_6']);
        $var = explode(PHP_EOL, $string);
        $xmlGenerated = [];

        $title  = $xml->createElement("key");
        $titleText = $xml->createTextNode("WhitelistedBookmarks");
        $title->appendChild($titleText);
        $dict->appendChild($title);
        $array1 = $xml->createElement("array");
        $dict->appendChild($array1);
        for ($i = 1; $i < safe_sizeof($var); $i++) {
            $title = '';
            $url   = '';
            $bookmark = '';
            $data = explode(',', $var[$i]);

            $title = $data[0];
            $url   = $data[1];
            $bookmark = trim($data[2]);

            $innerdict = $xml->createElement("dict");
            $key = $xml->createElement("key");
            $keyText = $xml->createTextNode("BookmarkPath");
            $key->appendChild($keyText);
            $innerdict->appendChild($key);
            $val = $xml->createElement("string");
            $valText = $xml->createTextNode($bookmark);
            $val->appendChild($valText);
            $innerdict->appendChild($val);

            $key = $xml->createElement("key");
            $keyText = $xml->createTextNode("Title");
            $key->appendChild($keyText);
            $innerdict->appendChild($key);
            $val = $xml->createElement("string");
            $valText = $xml->createTextNode($title);
            $val->appendChild($valText);
            $innerdict->appendChild($val);

            $key = $xml->createElement("key");
            $keyText = $xml->createTextNode("URL");
            $key->appendChild($keyText);
            $innerdict->appendChild($key);
            $val = $xml->createElement("string");
            $valText = $xml->createTextNode($url);
            $val->appendChild($valText);
            $innerdict->appendChild($val);
            $array1->appendChild($innerdict);
        }
        $array->appendChild($dict);
        $dict1->appendChild($array);
        $xml->appendChild($dict1);
        $str2 = array(
            "PayloadDescription" => $payloadDetails2[0]['PayloadDescription'], "PayloadDisplayName" => $payloadDetails2[0]['PayloadDisplayName'],
            "PayloadIdentifier" => $payloadDetails2[0]['PayloadIdentifier'], "PayloadOrganization" => $payloadDetails2[0]['PayloadOrganization'],
            "PayloadType" => $payloadDetails2[0]['PayloadType'], "PayloadUUID" => $payload_uuid
        );

        generateStringTag($xml, $dict1, $str2);
        PayloadVersion($xml, $dict1);

        array_push($xmlGenerated, $xml->saveXML());
    } elseif ($dartnum == '2023') {

        $xmlGenerated = [];
        $xml = new DOMDocument("1.0");
        $xml->formatOutput = true;
        if ($dataParse['LostModeValue_profile_1'] == "1") {
            $dict = $xml->createElement("dict");
            $key = $xml->createElement("key");
            $keyText = $xml->createTextNode("Command");
            $key->appendChild($keyText);
            $dict->appendChild($key);
            $dict1 = $xml->createElement("dict");
            $key1 = $xml->createElement("key");
            $key1Text = $xml->createTextNode("RequestType");
            $key1->appendChild($key1Text);
            $dict1->appendChild($key1);
            $string1 = $xml->createElement("string");
            $string1Text = $xml->createTextNode("EnableLostMode");
            $string1->appendChild($string1Text);
            $dict1->appendChild($string1);
            $key2 = $xml->createElement("key");
            $key2Text = $xml->createTextNode("Message");
            $key2->appendChild($key2Text);
            $dict1->appendChild($key2);
            $string2 = $xml->createElement("string");
            $string2Text = $xml->createTextNode($dataParse['Message_profile_2']);
            $string2->appendChild($string2Text);
            $dict1->appendChild($string2);
            $dict->appendChild($dict1);
            $key3 = $xml->createElement("key");
            $key3Text = $xml->createTextNode("CommandUUID");
            $key3->appendChild($key3Text);
            $dict->appendChild($key3);
            $string3 = $xml->createElement("string");
            $string3Text = $xml->createTextNode("Enablelostmode");
            $string3->appendChild($string3Text);
            $dict->appendChild($string3);
            $xml->appendChild($dict);

            $xmlResult = $xml->saveXML();
            $xmlResult = str_replace('<?xml version="1.0"?>', '', $xmlResult);
            $xmlFinal .= 'POLICY#2023#' . $xmlResult;
            array_push($xmlGenerated, $xmlFinal);

            if ($dataParse['DeviceLocation_profile_1'] == "1") {
                $xml = new DOMDocument("1.0");
                $xml->formatOutput = true;
                $dict = $xml->createElement("dict");
                $key = $xml->createElement("key");
                $keyText = $xml->createTextNode("Command");
                $key->appendChild($keyText);
                $dict->appendChild($key);
                $dict1 = $xml->createElement("dict");
                $key1 = $xml->createElement("key");
                $key1Text = $xml->createTextNode("RequestType");
                $key1->appendChild($key1Text);
                $dict1->appendChild($key1);
                $string1 = $xml->createElement("string");
                $string1Text = $xml->createTextNode("DeviceLocation");
                $string1->appendChild($string1Text);
                $dict1->appendChild($string1);
                $dict->appendChild($dict1);
                $key3 = $xml->createElement("key");
                $key3Text = $xml->createTextNode("CommandUUID");
                $key3->appendChild($key3Text);
                $dict->appendChild($key3);
                $string3 = $xml->createElement("string");
                $string3Text = $xml->createTextNode("Devicelocation");
                $string3->appendChild($string3Text);
                $dict->appendChild($string3);
                $xml->appendChild($dict);

                $xmlResult = $xml->saveXML();
                $xmlResult = str_replace('<?xml version="1.0"?>', '', $xmlResult);
                $xml1 .= 'POLICY#2023#' . $xmlResult;
                array_push($xmlGenerated, $xml1);
            }

            if ($dataParse['PlayLostModeSound_profile_1'] == "1") {
                $xml = new DOMDocument("1.0");
                $xml->formatOutput = true;
                $dict = $xml->createElement("dict");
                $key = $xml->createElement("key");
                $keyText = $xml->createTextNode("Command");
                $key->appendChild($keyText);
                $dict->appendChild($key);
                $dict1 = $xml->createElement("dict");
                $key1 = $xml->createElement("key");
                $key1Text = $xml->createTextNode("RequestType");
                $key1->appendChild($key1Text);
                $dict1->appendChild($key1);
                $string1 = $xml->createElement("string");
                $string1Text = $xml->createTextNode("PlayLostModeSound");
                $string1->appendChild($string1Text);
                $dict1->appendChild($string1);
                $dict->appendChild($dict1);
                $key3 = $xml->createElement("key");
                $key3Text = $xml->createTextNode("CommandUUID");
                $key3->appendChild($key3Text);
                $dict->appendChild($key3);
                $string3 = $xml->createElement("string");
                $string3Text = $xml->createTextNode("Playlostmodesound");
                $string3->appendChild($string3Text);
                $dict->appendChild($string3);
                $xml->appendChild($dict);

                $xmlResult = $xml->saveXML();
                $xmlResult = str_replace('<?xml version="1.0"?>', '', $xmlResult);
                $xml2 .= 'POLICY#2023#' . $xmlResult;
                array_push($xmlGenerated, $xml2);
            }
        } else {
            $dict = $xml->createElement("dict");
            $key = $xml->createElement("key");
            $keyText = $xml->createTextNode("Command");
            $key->appendChild($keyText);
            $dict->appendChild($key);
            $dict1 = $xml->createElement("dict");
            $key1 = $xml->createElement("key");
            $key1Text = $xml->createTextNode("RequestType");
            $key1->appendChild($key1Text);
            $dict1->appendChild($key1);
            $string1 = $xml->createElement("string");
            $string1Text = $xml->createTextNode("DisableLostmode");
            $string1->appendChild($string1Text);
            $dict1->appendChild($string1);
            $dict->appendChild($dict1);
            $key3 = $xml->createElement("key");
            $key3Text = $xml->createTextNode("CommandUUID");
            $key3->appendChild($key3Text);
            $dict->appendChild($key3);
            $string3 = $xml->createElement("string");
            $string3Text = $xml->createTextNode("Disablelostmode");
            $string3->appendChild($string3Text);
            $dict->appendChild($string3);
            $xml->appendChild($dict);
            $xmlResult = $xml->saveXML();
            $xmlResult = str_replace('<?xml version="1.0"?>', '', $xmlResult);
            $xmlFinal .= 'REMOVEPOLICY#2023#' . $xmlResult;
            array_push($xmlGenerated, $xmlFinal);
        }
    } elseif ($dartnum == '2022') {
        $xmlGenerated = addRemoveApplication($dataParse);
    } elseif ($dartnum == '2027') {
        $xmlGenerated = [];
        $dict = new SimpleXMLElement('<dict></dict>');
        $dict->addChild('key', 'Command');
        $cmdDict = $dict->addChild('dict');
        $cmdDict->addChild('key', 'RequestType');
        $cmdDict->addChild('string', $dataParse['dartName']);
        $cmdDict->addChild('key', 'Updates');
        $arrayVal = $cmdDict->addChild('array');
        $valDict = $arrayVal->addChild('dict');
        $valDict->addChild('key', 'ProductKey');
        $valDict->addChild('string', explode(PHP_EOL, trim($dataParse['ProductKey_profile_2']))[0]);
        $valDict->addChild('key', 'InstallAction');
        $valDict->addChild('string', explode(PHP_EOL, trim($dataParse['InstallAction_profile_4']))[0]);
        $dict->addChild('key', 'CommandUUID');
        $dict->addChild('string', 'Scheduleosupdate');
        $xmlResult = str_replace('<?xml version="1.0"?>', '', $dict->saveXML());
        $xml1 = 'POLICY#2027#' . $xmlResult;
        array_push($xmlGenerated, $xml1);
    } else {
        $xmlGenerated = [];
        foreach ($profile as  $index => $val) {
            $vartype = '';
            $policy  = '';
            $booleanVal = '';

            $names = explode('_', $val);
            $vartype = $names[2];
            $policy = $names[0];
            if ($val == 'SSID_STR_profile_2') {
                $vartype = 2;
                $policy = 'SSID_STR';
            } elseif ($val == 'HIDDEN_NETWORK_profile_1') {
                $vartype = 1;
                $policy = 'HIDDEN_NETWORK';
            }
            if ($vartype == 6) {
                if (!empty($dataParse[$val])) {
                    $key        = $xml->createElement("key");
                    $keyText    = $xml->createTextNode($policy);
                    $key->appendChild($keyText);
                    $arrstr     = $xml->createElement("array");

                    $arr_val = explode("\n", $dataParse[$val]);

                    foreach ($arr_val as $entries) {
                        $string     = $xml->createElement("string");
                        $stringText = $xml->createTextNode(trim($entries));
                        $string->appendChild($stringText);
                        $arrstr->appendChild($string);
                    }
                    $dict->appendChild($key);
                    $dict->appendChild($arrstr);
                }
            } else {
                $key  = $xml->createElement("key");
                $keyText = $xml->createTextNode($policy);
                $key->appendChild($keyText);
                $dict->appendChild($key);
                if ($vartype == 1) {
                    if (empty($dataParse[$val])) {
                        $booleanVal = $xml->createElement('false');
                    } else {
                        $booleanVal = $xml->createElement('true');
                    }
                    $dict->appendChild($booleanVal);
                } else {
                    if ($vartype == 2 || $vartype == 9) {
                        $formatData = $xml->createElement('string');
                        $StringVal = $xml->createTextNode($dataParse[$val]);
                    } elseif ($vartype == 3 || $vartype == 8) {
                        $formatData = $xml->createElement('integer');
                        $StringVal = $xml->createTextNode($dataParse[$val]);
                    } elseif ($vartype == 4) {
                        if (is_numeric($dataParse[$val])) {
                            $formatData = $xml->createElement('integer');
                        } else {
                            $formatData = $xml->createElement('string');
                        }
                        $StringVal = $xml->createTextNode($dataParse[$val]);
                    } elseif ($vartype == 5) {
                        $formatData = $xml->createElement('data');
                        if ($dartnum == '2008') {
                            $path = $_FILES['Icon_profile_5']['tmp_name'];
                            $data = file_get_contents($path);
                            $icon = base64_encode($data);
                        } else {
                            $path = $_FILES['addProfile_profile_5']['tmp_name'];
                            $data = file_get_contents($path);
                            $icon = base64_encode($data);
                        }
                        $StringVal = $xml->createTextNode($icon);
                    }
                    $formatData->appendChild($StringVal);
                    $dict->appendChild($formatData);
                }
            }
        }
        $array->appendChild($dict);
        $dict1->appendChild($array);
        $xml->appendChild($dict1);
        $str2 = array(
            "PayloadDescription" => $payloadDetails2[0]['PayloadDescription'], "PayloadDisplayName" => $payloadDetails2[0]['PayloadDisplayName'],
            "PayloadIdentifier" => $payloadDetails2[0]['PayloadIdentifier'], "PayloadOrganization" => $payloadDetails2[0]['PayloadOrganization'],
            "PayloadType" => $payloadDetails2[0]['PayloadType'], "PayloadUUID" => $payload_uuid
        );

        generateStringTag($xml, $dict1, $str2);
        PayloadVersion($xml, $dict1);
        array_push($xmlGenerated, $xml->saveXML());
    }

    $dartNum = $dataParse['dartNum'];
    $time = date('d-m-Y H:i:s');
    $priority = '';
    $xmlInsert = [];
    $siteName = '';

    if (isset($dataParse['action'])) {
        $siteName   = $dataParse['siteName'];
        $priority   = 'p1';
    } else {
        if ($_SESSION['searchType'] == 'Sites') {
            $priority = 'p3';
            $siteName = $_SESSION['searchValue'];
        } elseif ($_SESSION['searchType'] == 'Groups') {
            $priority = 'p2';
            $siteName = $_SESSION['rparentName'];
        } elseif ($_SESSION['searchType'] == 'ServiceTag') {
            $priority = 'p1';
            $siteName    = $_SESSION['rsiteName'];
        }
    }

    foreach ($xmlGenerated as $key => $val) {
        $xmlFinal = '';

        if ($dartNum == '2001' || $dartNum == '2023' || $dartNum == '2022' || $dartNum == '2027') {
            array_push($xmlInsert, $val);
        } elseif ($dartNum == '2006') {
            $xmlFinal .= $val;
            $xmlFinal = str_replace('<?xml version="1.0"?>', '', $xmlFinal);
            array_push($xmlInsert, $xmlFinal);
        } else {
            $xmlFinal .= 'POLICY#';
            $xmlFinal .= $dartNum . '#';
            $xmlFinal .= $val;
            $xmlFinal = str_replace('<?xml version="1.0"?>', '', $xmlFinal);
            array_push($xmlInsert, $xmlFinal);
        }
    }
    updateIosProfile($dataParse);

    if ($dataParse['dartNum'] == '2021') {
        updateProfileConfiguration($siteName, $priority, $dartNum, $xmlInsert, '', $profileName, 0, $dataParse);
    } else {
        updateConfiguration($siteName, $priority, $dartNum, $xmlInsert, $time, $dataParse);
    }
}

function addRemoveApplication($dataParse)
{

    $xmlGenerated = [];
    $xml = new DOMDocument("1.0");
    $xml->formatOutput = true;
    if ($dataParse['addApplication_profile_1'] == "1") {

        $dict = $xml->createElement("dict");
        $key = $xml->createElement("key");
        $keyText = $xml->createTextNode("Command");
        $key->appendChild($keyText);
        $dict->appendChild($key);
        $dict1 = $xml->createElement("dict");
        $key1 = $xml->createElement("key");
        $key1Text = $xml->createTextNode("ChangeManagementState");
        $key1->appendChild($key1Text);
        $dict1->appendChild($key1);
        $string1 = $xml->createElement("string");
        $string1Text = $xml->createTextNode("Managed");
        $string1->appendChild($string1Text);
        $dict1->appendChild($string1);
        $key2 = $xml->createElement("key");
        $key2Text = $xml->createTextNode("ManagementFlags");
        $key2->appendChild($key2Text);
        $dict1->appendChild($key2);
        $string2 = $xml->createElement("integer");
        $string2Text = $xml->createTextNode("1");
        $string2->appendChild($string2Text);
        $dict1->appendChild($string2);

        $key3 = $xml->createElement("key");
        $key3Text = $xml->createTextNode("RequestType");
        $key3->appendChild($key3Text);
        $dict1->appendChild($key3);
        $string3 = $xml->createElement("string");
        $string3Text = $xml->createTextNode("InstallApplication");
        $string3->appendChild($string3Text);
        $dict1->appendChild($string3);
        $key4 = $xml->createElement("key");
        $key4Text = $xml->createTextNode("iTunesStoreID");
        $key4->appendChild($key4Text);
        $dict1->appendChild($key4);
        $data = $dataParse['applicationId_profile_2'];
        if (is_numeric($data)) {
            $string4 = $xml->createElement("integer");
        } else {
            $string4 = $xml->createElement("string");
        }
        $string4Text = $xml->createTextNode($dataParse['applicationId_profile_2']);
        $string4->appendChild($string4Text);
        $dict1->appendChild($string4);
        $dict->appendChild($dict1);
        $key5 = $xml->createElement("key");
        $key5Text = $xml->createTextNode("CommandUUID");
        $key5->appendChild($key5Text);
        $dict->appendChild($key5);
        $string5 = $xml->createElement("string");
        $string5Text = $xml->createTextNode("AppInstallation");
        $string5->appendChild($string5Text);
        $dict->appendChild($string5);
        $xml->appendChild($dict);

        $xmlResult = $xml->saveXML();
        $xmlResult = str_replace('<?xml version="1.0"?>', '', $xmlResult);
        $xmlFinal .= 'POLICY#2022#' . $xmlResult;
        array_push($xmlGenerated, $xmlFinal);
    } elseif ($dataParse['addApplication_profile_1'] == "0") {

        $dict = $xml->createElement("dict");
        $key = $xml->createElement("key");
        $keyText = $xml->createTextNode("Command");
        $key->appendChild($keyText);
        $dict->appendChild($key);
        $dict1 = $xml->createElement("dict");
        $key1 = $xml->createElement("key");
        $key1Text = $xml->createTextNode("RequestType");
        $key1->appendChild($key1Text);
        $dict1->appendChild($key1);
        $string1 = $xml->createElement("string");
        $string1Text = $xml->createTextNode("RemoveApplication");
        $string1->appendChild($string1Text);
        $dict1->appendChild($string1);
        $key2 = $xml->createElement("key");
        $key2Text = $xml->createTextNode("Identifier");
        $key2->appendChild($key2Text);
        $dict1->appendChild($key2);
        $string2 = $xml->createElement("string");
        $string2Text = $xml->createTextNode($dataParse['bundleId_profile_2']);
        $string2->appendChild($string2Text);
        $dict1->appendChild($string2);
        $dict->appendChild($dict1);
        $key3 = $xml->createElement("key");
        $key3Text = $xml->createTextNode("CommandUUID");
        $key3->appendChild($key3Text);
        $dict->appendChild($key3);
        $string3 = $xml->createElement("string");
        $string3Text = $xml->createTextNode("AppUnInstallation" . ":" . $dataParse['bundleId_profile_2']);
        $string3->appendChild($string3Text);
        $dict->appendChild($string3);
        $xml->appendChild($dict);

        $xmlResult = $xml->saveXML();
        $xmlResult = str_replace('<?xml version="1.0"?>', '', $xmlResult);
        $xmlFinal .= 'POLICY#2022#' . $xmlResult;
        array_push($xmlGenerated, $xmlFinal);
    }
    return $xmlGenerated;
}

function syncDevice($inputArr)
{

    $db = db_connect();
    global $policyServer_url;

    foreach ($inputArr as $key => $val) {
        $searchname = $val['host'];

        $sql = $db->prepare("SELECT UDID, Device_Token, Push_magic FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine = ? ");
        $sql->execute([$searchname]);
        $result = $db->lastInsertId();
    }
    $resArray = array();
    foreach ($result as $key => $val) {
        if (!empty($val)) {
            $val['Command'] = 'blankpush';
            array_push($resArray, $val);
        }
    }
    $resArray = json_encode($resArray);
    $content = $resArray;
    $curl = curl_init($policyServer_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Type:application/json',
        'Content-Length:' . strlen($content)
    ));
    $json_response = curl_exec($curl);
    $curl_errorno = curl_errno($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    echo $status;
}
