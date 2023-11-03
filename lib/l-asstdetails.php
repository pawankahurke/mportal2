<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
$db = db_connect();

$result = getAssetDataTableName($db);
$machineTableName = $result['machine'];
$asssetDataTableName = $result['asset'];
$_SESSION['machineTableName'] = $machineTableName;


function getSessionSearchParams()
{
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    $sql = getSQlDB($db);
    $res = find_many($sql, $db);
    $machineIdStr = '';
    foreach ($res as $value) {
        $machineIdStr .= "'" . $value['machineid'] . "',";
    }
    $machineIdStr = rtrim($machineIdStr, ',');
    return array("mid", $machineIdStr);
}

function getTickSize($value)
{
    if (intval($value) > 5) {
        return $TickSize = floor((intval($value) / 5));
    } else {
        return $TickSize = 1;
    }
}

function getSoftNameVersionArr($data, $softwareList1, $softwareList2)
{
    $versionArray = array();
    foreach ($data as $key => $val) {
        $value = utf8_encode($val['value1']);
        $json = safe_json_decode($value);
        $flag = array();

        for ($i = 0; $i < safe_count($json->softwareversion); $i++) {
            $flag[$json->softwarename[$i]] = TRUE;
        }
        for ($i = 0; $i < safe_count($json->softwareversion); $i++) {

            if (customStrngMatch($json->softwarename[$i], $softwareList1)) {
                if ($flag[$json->softwarename[$i]]) {
                    $versionArray[$json->softwarename[$i]][] = $json->softwareversion[$i];
                    $flag[$json->softwarename[$i]] = FALSE;
                }
            } else if (customStrngMatch($json->softwarename[$i], $softwareList2)) {
                if ($flag[$json->softwarename[$i]]) {
                    $versionArray[$json->softwarename[$i]][] = $json->softwareversion[$i];
                    $flag[$json->softwarename[$i]] = FALSE;
                }
            }
        }
    }
    return $versionArray;
}

function getSeriesArray($versions)
{

    $seriesArray = array();
    foreach ($versions as $key => $val) {
        $seriesArray[trim($key)][] = safe_count($val);
    }
    return $seriesArray;
}



function generateSeriesString($series, $multiDrillDown)
{

    $str = '';
    $flag = TRUE;
    foreach ($multiDrillDown as $value) {
        $count = 0;
        $name = '';
        foreach ($series as $key => $val) {
            if (stripos($key, trim($value)) === 0) {
                $name = $value;
                $count += $val[0];
            }
            if (!customStrngMatch($key, $multiDrillDown) && $flag) {
                $str .= prepareSeries($key, $val[0], TRUE);
            }
        }
        if ($name != '')
            $str .= prepareSeries($name, $count, TRUE);
        $flag = FALSE;
        reset($series);
    }
    if ($str != '') {
        $str = rtrim($str, '@@@');
    } else {
        $str .= '@@@';
    }
    return $str;
}

function genarateVersionsString($versions, $multiDrillDown)
{
    $str = '';
    foreach ($versions as $key => $val) {
        $str1 = '';

        $dataStr = '';
        $tempArray = array_count_values($val);
        foreach ($tempArray as $key1 => $val1) {
            $dataStr .= '[ "' . $key1 . '",' . $val1 . '],';
        }
        $dataStr = rtrim($dataStr, ',');
        $str .= prepareDrill($key, $dataStr);
    }
    foreach ($multiDrillDown as $value) {
        reset($versions);
        $count = 0;
        $str1 = '';
        foreach ($versions as $key => $val) {
            if (stripos($key, trim($value)) === 0) {
                $name = $value;
                $count = safe_count($val);
                $str1 .= prepareSeries($key, $count, FALSE);
                $flag = TRUE;
            }
        }
        if ($flag) {
            $name = str_replace(' 2', '', $name);
            $str1 = rtrim($str1, ',');
            $str .= prepareDrill($name, $str1);
            $flag = FALSE;
        }
    }
    if ($str != '') {
        $str = rtrim($str, '@@@');
    } else {
        $str .= '@@@';
    }
    return $str;
}


function getSQlDB($db)
{

    $searchType  = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $machineTableName = $_SESSION['machineTableName'];
    if (($searchType == 'Sites' || $searchType == 'Site')) {
        if ($searchValue == 'All') {
            $searchValueRes = get_all_search_list($searchType);
            $sql = "select M.machineid,M.host,M.cust "
                . "from " . $GLOBALS['PREFIX'] . "asset.$machineTableName M "
                . "join " . $GLOBALS['PREFIX'] . "core.Census C "
                . "on M.host = C.host and C.site = M.cust "
                . "where C.site in ($searchValueRes)";
        } else {
            $sql = "select M.machineid,M.host,M.cust "
                . "from " . $GLOBALS['PREFIX'] . "asset.$machineTableName M "
                . "join " . $GLOBALS['PREFIX'] . "core.Census C "
                . "on M.host = C.host and C.site = M.cust "
                . "where C.site = '$searchValue'";
        }
    } else if ($searchType == 'Service Tag' || $searchType == 'Host Name') {
        $leveltwoval = $_SESSION["leveltwoval"];
        $censusId    = $_SESSION["rcensusId"];
        $sql = "select M.machineid,M.host,M.cust "
            . "from " . $GLOBALS['PREFIX'] . "asset.$machineTableName M "
            . "join " . $GLOBALS['PREFIX'] . "core.Census C "
            . "on M.host = C.host "
            . "and C.id = '$censusId' order by C.id desc limit 1";
    } else {
        $sql = groupsDeviceInfoMachineData($searchValue, $db);
    }
    return $sql;
}



function groupsDeviceInfoMachineData($searchValue, $db)
{
    global $machineTableName;
    try {
        $sqlGroups = "select mcatuniq,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name='$searchValue'";
        $resultGroups = find_one($sqlGroups, $db);

        $sqlMachines = "select C.site,C.host,M.name,A.machineid from " . $GLOBALS['PREFIX'] . "core.MachineGroups M,"
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap P,core.Census C," . $GLOBALS['PREFIX'] . "asset.$machineTableName A where "
            . "M.mgroupuniq=P.mgroupuniq and P.censusuniq=C.censusuniq "
            . "and C.site=A.cust and C.host=A.host and  "
            . "M.mgroupuniq='" . $resultGroups['mgroupuniq'] . "' group by C.host,C.site";
        return $sqlMachines;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return NULL;
    }
}

function createGridSql($gridType, $where)
{
    $searchParams = getSessionSearchParams();
    switch ($gridType) {
        case 'Os':
            $sql = "select * from tempAssetSummary where groupId = 1 and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        case 'ChassisMan':
        case 'ChassisType':
            $sql = "select * from tempAssetSummary where groupId = 3 and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        case 'Proc':
            $sql = "select * from tempAssetSummary where groupId = 2 and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        case 'BasicInfo':
            $sql = "select * from tempAssetSummary where groupId in (1,3,4) and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        case 'softGrid':
            $sql = "select * from tempAssetSummary where groupId = 5 and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        case 'netGrid':
            $sql = "select * from tempAssetSummary where groupId = 4 and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        case 'resourceGrid':
            $sql = "select * from tempAssetSummary where groupId = 6 and $searchParams[0] in ($searchParams[1]) " . $where;
            break;
        default:
            break;
    }
    return $sql;
}






function prepareSeries($name, $count, $flag)
{
    $color = "";
    if ($flag) {
        if ($color == '')
            return '{"name":"' . $name . '","y":' . $count . ',"drilldown":"' . $name . '"}@@@';
        else {
            return '{"name":"' . $name . '","y":' . $count . ',"drilldown":"' . $name . '","color":"' . $color . '"}@@@';
        }
    } else {
        return '{"name":"' . $name . '","y":' . $count . ',"drilldown":"' . $name . '"},';
    }
}

function prepareDrill($name, $data)
{
    return '{"name":"' . $name . '","id":"' . $name . '","data":[' . $data . ']}@@@';
}

function getLatestVersion($versionArray)
{
    $latestVersion = '{';
    foreach ($versionArray as $key => $val) {
        $max = '0.0.0.0';
        foreach ($val as $key1 => $value) {
            $i = 0;
            if ($max != $value) {
                $temp1 = explode('.', $value);
                $temp2 = explode('.', $max);
                foreach ($temp1 as $key2 => $value1) {
                    if (intval($value1) > intval($temp2[$i])) {
                        $max = $value;
                        break;
                    }
                    $i++;
                }
            }
        }
        $latestVersion .= '"' . $key . '" : "' . $max . '",';
    }
    return rtrim($latestVersion, ',') . '}';
}

function customStrngMatch($needle, $haystack)
{

    foreach ($haystack as $key => $value) {
        if (stripos($needle, trim($value)) === 0) {
            return TRUE;
        }
    }
    return FALSE;
}

function parseResourceDataArr($resourceArr)
{
    $flag = TRUE;
    $json = safe_json_decode($resourceArr);
    $ramSize = $json->ResourceData[3]->primaryDrive[0];

    if (safe_count($json->ResourceData[0]->drivename) !== safe_count($json->ResourceData[1]->totalSpace)) {
        if ($json->ResourceData[0]->drivename[$i] == "A:") {
            $k = 1;
        } else {
            $k = 0;
        }
        for ($i = 0; $i < safe_count($json->ResourceData[1]->totalSpace); $i++) {
            $drivename .= $json->ResourceData[0]->drivename[$k] . ",";
            $totalSpace .= $json->ResourceData[1]->totalSpace[$i] . ",";
            $freeSpace .= $json->ResourceData[2]->freeSpace[$i] . ",";
            $k++;
        }
    } else {
        for ($i = 0; $i < safe_count($json->ResourceData[0]->drivename); $i++) {
            $drivename .= $json->ResourceData[0]->drivename[$i] . ",";
            $totalSpace .= $json->ResourceData[1]->totalSpace[$i] . ",";
            $freeSpace .= $json->ResourceData[2]->freeSpace[$i] . ",";
        }
    }
    return $drivename . '----' . $totalSpace . '----' . $freeSpace . '----' . $ramSize;
}

function genCellValues($resourceArr)
{

    $resourceStr = parseResourceDataArr($resourceArr);
    $resourceParsedArr = explode('----', $resourceStr);

    $table .= '<table class="noborder"><tr><td>Drive Name</td><td>Total Size(GB)</td><td>Used Space(GB)</td><td>Free Space(GB)</td>';
    $trElements = createCellTable($resourceParsedArr);

    $table .= $trElements . "</table>";

    return $table . "----" . $resourceParsedArr[3];
}

function createCellTable($resourceParsedArr)
{

    $driveNameArr = explode(",", $resourceParsedArr[0]);
    $totalSpaceArr = explode(",", $resourceParsedArr[1]);
    $freeSpaceArr = explode(",", $resourceParsedArr[2]);

    for ($i = 0; $i < safe_count($driveNameArr) - 1; $i++) {
        $total = floor(intval($totalSpaceArr[$i]) / 1048576);
        $freespace = floor(intval($freeSpaceArr[$i]) / 1048576);
        $usedSpace = $total - $freespace;
        $trElements .= "<tr><td>$driveNameArr[$i]</td><td>$total</td><td>$usedSpace</td><td>$freespace</td></tr>";
    }
    return $trElements;
}

function genCellValues1($resourceArr)
{

    $resourceStr = parseResourceDataArr($resourceArr);
    $resourceParsedArr = explode('----', $resourceStr);

    $table .= '<table class="noborder" ><tr><td>Drive Name</td><td>Total Size</td><td>Free Space</td>';
    $trElements = createCellTable($resourceParsedArr);

    $table .= $trElements . "</table>";

    return $table . "----" . $resourceParsedArr[3];
}


function getSitesNMachines($searchType, $username)
{
    $menuLevel = $_SESSION['menuLevelOne'];
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'core', $db);

    if ($searchType == 'Service Tag') {
        $searchtype = $_SESSION['menuLevelOne'];
    } else {
        $searchtype = $searchType;
    }
    if ($searchType == '') {
        $searchtype = url::requestToAny('searchType');
    }

    if ($searchtype == 'Sites' || $menuLevel == 'Sites') {

        $sql = "select customer as name from Customers where username='$username'";
        $sqlRes = find_many($sql, $db);

        foreach ($sqlRes as $key => $value) {
            $sites .= "'" . safe_addslashes($value['name']) . "',";
        }
        $searchVal = rtrim($sites, ',');
    } else if ($searchtype == 'Groups' || $menuLevel == 'Sites') {

        $wh = "select name from MachineGroups where (username = '$username' or global=1) and style=2";

        $sql = "select C.host from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq and GP.mgroupuniq = M.mgroupuniq and M.name IN ($wh) group by C.host";
        $resultSite = find_many($sql, $db);

        foreach ($resultSite as $row) {
            $agent_sites .= "'" . $row['host'] . "',";
        }
        $searchVal = rtrim($agent_sites, ',');
    } else {
        $sql = "select mgroupid from " . $GLOBALS['PREFIX'] . "dashboard.ViewMachineGroups where viewid IN (select viewid from " . $GLOBALS['PREFIX'] . "dashboard.ViewUsers where userid = " . $_SESSION['user']['adminid'] . ")";
        $res = find_one($sql, $db);
        $groupid = $res['mgroupid'];

        $sql = "select c.host as host from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,core.MachineGroupMap mgm,core.Census c where mg.mgroupuniq=mgm.mgroupuniq and mgm.censusuniq=c.censusuniq and mg.mgroupid in ('$groupid') and c.host != '0' group by c.host";
        $res = find_many($sql, $db);

        foreach ($res as $row) {
            $agent_sites .= "'" . $row['host'] . "',";
        }
        $searchVal = rtrim($agent_sites, ',');
    }


    return $searchVal;
}

function getAssetDataTableName($db)
{
    $sql = "SELECT value FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE name = 'advanced_asset' LIMIT 1";
    $res = find_one($sql, $db);
    if (safe_count($res) > 0) {
        if ($res['value'] == '0' || $res['value'] == 0) {
            $array['machine'] = 'Machine';
            $array['asset'] = 'AssetDataLatest';
        } else if ($res['value'] == '1' || $res['value'] == 1) {
            $array['machine'] = 'MachineLatest';
            $array['asset'] = 'AssetDataLatestTest';
        }
    } else {
        $array['machine'] = 'Machine';
        $array['asset'] = 'AssetDataLatest';
    }
    return $array;
}
