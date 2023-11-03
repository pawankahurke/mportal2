<?php


function RESOL_GetSiteScheduleData_EL($db, $dataScope, $searchValue, $from, $now)
{
    $rParent = $_SESSION['rparentName'];
    $from = date(strtotime("-15 days"));
    $now  = time();

    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    $data = DASH_GetMachinesSites($key, $db, $dataScope);
    $machines = "'" . implode("','", $data) . "'";

    if ($searchValue == 'All') {
        foreach ($dataScope as $value) {
            $site .= "'" . $value . "',";
        }
        $sites = rtrim($site, ',');
    } else {
        $sites = $rParent;
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('customer' => $sites, 'evntType' => 'Scheduled');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');

    return $result;
}

function RESOL_GetMachineScheduleData_EL($db, $rparentname, $searchValue, $from, $now)
{
    $from = date(strtotime("-15 days"));
    $now  = time();
    $rparentname = $_SESSION['rparentName'];

    if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
        $rCensusId = $_SESSION['rcensusId'];
        $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
        $uuidres = find_one($uuidsql, $db);
        $uuid = $uuidres['uuid'];

        $condition = array('uuid' => $uuid, 'evntType' => 'Scheduled');
    } else {
        $condition = array('customer' => $rparentname, 'machine' => $searchValue, 'evntType' => 'Scheduled');
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');

    return $result;
}

function RESOL_GetGroupScheduleData_EL($db, $Scope, $from, $now)
{

    $from = date(strtotime("-15 days"));
    $now  = time();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    $machines  = DASH_GetGroupsMachines($key, $db, $Scope);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    foreach ($machines as $value) {
        $machine .= "'" . $value . "',";
    }
    $machine = rtrim($machine, ',');
    $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
    $uuid = "'" . implode("','", $uuiddata) . "'";
    $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
    $custName = "'" . implode("','", $sitedata) . "'";

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Scheduled');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');
    return $result;
}

function RESOL_GetSiteScheduleData_export_EL($db, $dataScope, $searchValue, $from, $now)
{
    $from = date(strtotime("-15 days"));
    $now  = time();
    $rParent = $_SESSION['rparentName'];

    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    $data = DASH_GetMachinesSites($key, $db, $dataScope);
    $machines = "'" . implode("','", $data) . "'";

    if ($searchValue == 'All') {
        foreach ($dataScope as $value) {
            $site .= "'" . $value . "',";
        }
        $sites = rtrim($site, ',');
    } else {
        $sites = $rParent;
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('customer' => $sites, 'evntType' => 'Scheduled');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');

    return $result;
}

function RESOL_GetMachineScheduleData_export_EL($db, $rparentname, $searchValue, $from, $now)
{
    $from = date(strtotime("-15 days"));
    $now  = time();
    if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
        $rCensusId = $_SESSION['rcensusId'];
        $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
        $uuidres = find_one($uuidsql, $db);
        $uuid = $uuidres['uuid'];

        $condition = array('uuid' => $uuid, 'evntType' => 'Scheduled');
    } else {
        $condition = array('customer' => $rparentname, 'machine' => $searchValue, 'evntType' => 'Scheduled');
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');

    return $result;
}

function RESOL_GetGroupScheduleData_export_EL($db, $machines, $from, $now)
{
    $from = date(strtotime("-15 days"));
    $now  = time();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    foreach ($machines as $value) {
        $machine .= "'" . $value . "',";
    }
    $machine = rtrim($machine, ',');
    $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
    $uuid = "'" . implode("','", $uuiddata) . "'";
    $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
    $custName = "'" . implode("','", $sitedata) . "'";

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Scheduled');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');
    return $result;
}

function RESOL_GetScheduleDetail_EL($db, $namelist, $from, $now)
{
    $name = trim($namelist);
    $from = date(strtotime("-15 days"));
    $now  = time();

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentname = $_SESSION["rparentName"];

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $rParent = $_SESSION['rparentName'];

    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    if ($searchType == 'Sites') {

        if ($searchValue == 'All') {
            foreach ($dataScope as $value) {
                $site .= "'" . $value . "',";
            }
            $sites = rtrim($site, ',');
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('machine' => $machines, 'customer' => $sites, 'evntType' => 'Scheduled', 'description.keyword' => $name);
        } else {
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('machine' => $machines, 'customer' => $rParent, 'evntType' => 'Scheduled', 'description.keyword' => $name);
        }
    } else if ($searchType == 'ServiceTag') {
        if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
            $rCensusId = $_SESSION['rcensusId'];
            $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
            $uuidres = find_one($uuidsql, $db);
            $uuid = $uuidres['uuid'];

            $condition = array('uuid' => $uuid, 'evntType' => 'Scheduled', 'description.keyword' => $name);
        } else {
            $condition = array('machine' => $searchValue, 'customer' => $rParent, 'evntType' => 'Scheduled', 'description.keyword' => $name);
        }
    } else if ($searchType == 'Groups') {
        foreach ($machines as $value) {
            $machine .= "'" . $value . "',";
        }
        $machine = rtrim($machine, ',');

        $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
        $uuid = "'" . implode("','", $uuiddata) . "'";
        $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
        $custName = "'" . implode("','", $sitedata) . "'";

        $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Scheduled', 'description.keyword' => $name);
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime","customer","clientversion","clientsize","uuid"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');
    return $result;
}


function RESOL_GetSiteSelfHelpData_EL($db, $dataScope, $searchValue, $from, $now)
{

    $from = date(strtotime("-15 days"));
    $now  = time();
    $rParent = $_SESSION['rparentName'];
    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    if ($searchValue == 'All') {
        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";
        foreach ($dataScope as $value) {
            $site .= "'" . $value . "',";
        }
        $sites = rtrim($site, ',');
    } else {
        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";
        $sites = $rParent;
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('machine' => $machines, 'customer' => $sites, 'evntType' => 'Selfhelp');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');

    return $result;
}

function RESOL_GetMachineSelfHelpData_EL($db, $rparentname, $searchValue, $from, $now)
{
    $from = date(strtotime("-15 days"));
    $now  = time();
    $rparentname = $_SESSION["rparentName"];

    if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
        $rCensusId = $_SESSION['rcensusId'];
        $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
        $uuidres = find_one($uuidsql, $db);
        $uuid = $uuidres['uuid'];
        $condition = array('uuid' => $uuid, 'evntType' => 'Selfhelp');
    } else {
        $condition = array('machine' => $searchValue, 'customer' => $rparentname, 'evntType' => 'Selfhelp');
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');

    return $result;
}

function RESOL_GetGroupSelfHelpData_EL($db, $scope, $from, $now)
{

    $from = date(strtotime("-15 days"));
    $now  = time();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    $machines  = DASH_GetGroupsMachines($key, $db, $dscope);
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    foreach ($machines as $value) {
        $machine .= "'" . $value . "',";
    }
    $machine = rtrim($machine, ',');
    $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
    $uuid = "'" . implode("','", $uuiddata) . "'";
    $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
    $custName = "'" . implode("','", $sitedata) . "'";

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Selfhelp');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');

    return $result;
}

function RESOL_GetSiteSelfHelpData_export_EL($db, $dataScope, $searchValue, $from, $now)
{

    $from = date(strtotime("-15 days"));
    $now  = time();
    $rParent = $_SESSION['rparentName'];
    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    if ($searchValue == 'All') {
        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";
        foreach ($dataScope as $value) {
            $site .= "'" . $value . "',";
        }
        $sites = rtrim($site, ',');
    } else {
        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";
        $sites = $rParent;
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('machine' => $machines, 'customer' => $sites, 'evntType' => 'Selfhelp');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');

    return $result;
}

function RESOL_GetMachineSelfHelpData_export_EL($db, $rparentname, $searchValue, $from, $now)
{

    $from = date(strtotime("-15 days"));
    $now  = time();
    if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
        $rCensusId = $_SESSION['rcensusId'];
        $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
        $uuidres = find_one($uuidsql, $db);
        $uuid = $uuidres['uuid'];
        $condition = array('uuid' => $uuid, 'evntType' => 'Selfhelp');
    } else {
        $condition = array('machine' => $searchValue, 'customer' => $rparentname, 'evntType' => 'Selfhelp');
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');

    return $result;
}

function RESOL_GetGroupSelfHelpData_export_EL($db, $machines, $from, $now)
{

    $from = date(strtotime("-15 days"));
    $now  = time();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    foreach ($machines as $value) {
        $machine .= "'" . $value . "',";
    }
    $machine = rtrim($machine, ',');
    $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
    $uuid = "'" . implode("','", $uuiddata) . "'";
    $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
    $custName = "'" . implode("','", $sitedata) . "'";

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Selfhelp');
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');

    return $result;
}

function RESOL_GetselfDetail_EL($db, $name)
{
    $name = trim($name);
    $from = date(strtotime("-15 days"));
    $now  = time();

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentname = $_SESSION["rparentName"];
    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);

    $rParent = $_SESSION['rparentName'];

    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    if ($searchType == 'Sites') {

        if ($searchValue == 'All') {
            foreach ($dataScope as $value) {
                $site .= "'" . $value . "',";
            }
            $sites = rtrim($site, ',');
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('machine' => $machines, 'customer' => $sites, 'evntType' => 'Selfhelp', 'description.keyword' => $name);
        } else {
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $condition = array('machine' => $machines, 'customer' => $rParent, 'evntType' => 'Selfhelp', 'description.keyword' => $name);
        }
    } else if ($searchType == 'ServiceTag') {
        if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
            $rCensusId = $_SESSION['rcensusId'];
            $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
            $uuidres = find_one($uuidsql, $db);
            $uuid = $uuidres['uuid'];

            $condition = array('uuid' => $uuid, 'evntType' => 'Selfhelp', 'description.keyword' => $name);
        } else {
            $condition = array('machine' => $searchValue, 'customer' => $rParent, 'evntType' => 'Selfhelp', 'description.keyword' => $name);
        }
    } else if ($searchType == 'Groups') {
        foreach ($machines as $value) {
            $machine .= "'" . $value . "',";
        }
        $machine = rtrim($machine, ',');

        $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
        $uuid = "'" . implode("','", $uuiddata) . "'";
        $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
        $custName = "'" . implode("','", $sitedata) . "'";

        $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Selfhelp', 'description.keyword' => $name);
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');
    return $result;
}

function RESOL_Get_ProactiveData_EL($db)
{

    $proactiveRes = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $user = $_SESSION["user"]["username"];
        $user_email = $_SESSION["user"]["adminEmail"];
        $userId = $_SESSION["user"]["userid"];
        $from = time() - (30 * 24 * 60 * 60);
        $append = "";

        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

            $condition = array('MachineTag' => $searchValue, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive');
            $notCondition = array('JobStatus' => '6,7');
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'Site : " . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'Site : $searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('MachineTag' => $machines, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive');
            $notCondition = array('JobStatus' => '6,7');
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'Group : " . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'Group : $searchValue'";
            }

            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('MachineTag' => $machines, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive');
            $notCondition = array('JobStatus' => '6,7');
        }

        $tableName = 'resolutionaudit';
        $source = '"AID","BID","MachineTag","JobCreatedTime","AgentName","JobType","MachineOs","ProfileName",'
            . '"ClientTimeZone","ClientExecutedTime","JobStatus","DartExecutionProof"';
        $range = array('column' => 'JobCreatedTime', 'gte' => $from);
        $aggrCol = 'ProfileName.keyword';

        $proactiveRes = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, $notCondition, $aggrCol, 'ProfileName');
    } else {
        echo "Your key has been expired";
    }
    return $proactiveRes;
}

function RESOL_GetResolutionDetail_EL($db, $name, $from, $now)
{

    $proactiveRes = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $user = $_SESSION["user"]["username"];
        $user_email = $_SESSION["user"]["adminEmail"];
        $userId = $_SESSION["user"]["userid"];
        $from = time() - (30 * 24 * 60 * 60);
        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

            $condition = array('MachineTag' => $searchValue, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive', 'ProfileName.keyword' => $name);
            $notCondition = array('JobStatus' => '6,7');
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'Site : " . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'Site : $searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('MachineTag' => $machines, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive', 'AgentUniqId' => $user_email, 'ProfileName.keyword' => $name);
            $notCondition = array('JobStatus' => '6,7');
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'Group : " . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'Group : $searchValue'";
            }

            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('MachineTag' => $machines, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive', 'AgentUniqId' => $user_email, 'ProfileName.keyword' => $name);
            $notCondition = array('JobStatus' => '6,7');
        }

        $tableName = 'resolutionaudit';
        $source = '"AID","BID","MachineTag","JobCreatedTime","AgentName","JobType","MachineOs","ProfileName",'
            . '"ClientTimeZone","ClientExecutedTime","JobStatus","DartExecutionProof","SelectionType"';
        $range = array('column' => 'JobCreatedTime', 'gte' => $from);

        $proactiveRes = RESOLEL_getData($tableName, $source, $condition, $range, $notCondition);
    } else {
        echo "Your key has been expired";
    }
    return $proactiveRes;
}

function RESOL_Get_ProactiveData_export_EL($db)
{

    $proactiveRes = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $user = $_SESSION["user"]["username"];
        $user_email = $_SESSION["user"]["adminEmail"];
        $userId = $_SESSION["user"]["userid"];
        $from = time() - (30 * 24 * 60 * 60);
        $append = "";

        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

            $condition = array('MachineTag' => $searchValue, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive');
            $notCondition = array('JobStatus' => '6,7');
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'Site : " . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'Site : $searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('MachineTag' => $machines, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive');
            $notCondition = array('JobStatus' => '6,7');
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'Group : " . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'Group : $searchValue'";
            }

            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('MachineTag' => $machines, 'CustomerNO' => $custList, 'OrderNO' => $ordList, 'JobType' => 'Interactive');
            $notCondition = array('JobStatus' => '6,7');
        }

        $tableName = 'resolutionaudit';
        $source = '"AID","BID","MachineTag","JobCreatedTime","AgentName","JobType","MachineOs","ProfileName",'
            . '"ClientTimeZone","ClientExecutedTime","JobStatus","DartExecutionProof","SelectionType"';
        $range = array('column' => 'JobCreatedTime', 'gte' => $from);
        $aggrCol = 'ProfileName.keyword';

        $proactiveRes = RESOLEL_getData($tableName, $source, $condition, $range, $notCondition);
    } else {
        echo "Your key has been expired";
    }
    return $proactiveRes;
}

function RESOL_Get_PredictiveData_EL($db)
{

    $searchtype = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $user = $_SESSION["user"]["username"];
    $rparentname = $_SESSION['rparentName'];
    $from = time() - (15 * 24 * 60 * 60);
    $now = time();

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

    if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

        $condition = array('customer' => $rparentname, 'machine' => $searchValue, 'evntType' => 'Autoheal');
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        if (is_numeric($rparentname)) {
            $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rparentname ";
            $groupres = find_one($groupsql, $db);
            $rparentname = $groupres['name'];
        }

        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        if ($searchValue == 'All') {
            foreach ($dataScope as $value) {
                $site .= "'" . $value . "',";
            }
            $sites = rtrim($site, ',');
        } else {
            $sites = $rparentname;
        }
        $condition = array('customer' => $sites, 'evntType' => 'Autoheal');
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $machines  = DASH_GetGroupsMachines($key, $db, $dataScope);
        $dataScopes = UTIL_GetSiteScope($db, $searchValue, $searchType);

        foreach ($machines as $value) {
            $machine .= "'" . $value . "',";
        }
        $machine = rtrim($machine, ',');
        $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScopes);
        $uuid = "'" . implode("','", $uuiddata) . "'";
        $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
        $custName = "'" . implode("','", $sitedata) . "'";

        $condition = array('customer' => $custName, 'evntType' => 'Autoheal');
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);
    $aggrCol = 'description.keyword';

    $result = RESOLEL_getData_aggregate($tableName, $source, $condition, $range, '', $aggrCol, 'description');

    return $result;
}

function RESOL_GetPredicitveDetail_EL($db, $namelist, $from, $now)
{
    $name = trim($namelist);
    $from = date(strtotime("-15 days"));
    $now  = time();

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentname = $_SESSION["rparentName"];

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $rParent = $_SESSION['rparentName'];

    if (is_numeric($rParent)) {
        $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
        $groupres = find_one($groupsql, $db);
        $rParent = $groupres['name'];
    }

    if ($searchType == 'Sites') {

        if ($searchValue == 'All') {
            foreach ($dataScope as $value) {
                $site .= "'" . $value . "',";
            }
            $sites = rtrim($site, ',');
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('machine' => $machines, 'customer' => $sites, 'evntType' => 'Autoheal', 'description.keyword' => $name);
        } else {
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $condition = array('machine' => $machines, 'customer' => $rParent, 'evntType' => 'Autoheal', 'description.keyword' => $name);
        }
    } else if ($searchType == 'ServiceTag') {
        if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
            $rCensusId = $_SESSION['rcensusId'];
            $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
            $uuidres = find_one($uuidsql, $db);
            $uuid = $uuidres['uuid'];

            $condition = array('uuid' => $uuid, 'evntType' => 'Autoheal', 'description.keyword' => $name);
        } else {
            $condition = array('machine' => $searchValue, 'customer' => $rParent, 'evntType' => 'Autoheal', 'description.keyword' => $name);
        }
    } else if ($searchType == 'Groups') {
        foreach ($machines as $value) {
            $machine .= "'" . $value . "',";
        }
        $machine = rtrim($machine, ',');

        $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
        $uuid = "'" . implode("','", $uuiddata) . "'";
        $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
        $custName = "'" . implode("','", $sitedata) . "'";

        $condition = array('uuid' => $uuid, 'customer' => $custName, 'evntType' => 'Autoheal', 'description.keyword' => $name);
    }

    $tableName = 'eventsautoheal';
    $source = '"aidx","description","machine","text1","servertime","customer","clientversion","clientsize","uuid"';
    $range = array('column' => 'servertime', 'gte' => $from, 'lte' => $now);

    $result = RESOLEL_getData($tableName, $source, $condition, $range, '');
    return $result;
}

function RESOLEL_getData_aggregate($tableName, $source, $cond, $range, $notCond, $aggrCol, $colName)
{

    global $elastic_url;
    $url = $elastic_url . $tableName . "/_search?pretty&size=10000";

    $condition = '';

    foreach ($cond as $key => $value) {
        $condition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
    }
    $must = rtrim($condition, ',');
    if (safe_count($range) > 2) {
        $filter = '{ "range": { "' . $range['column'] . '": { "gte": "' . $range['gte'] . '", "lte" : "' . $range['lte'] . '" }}}';
    } else {
        $filter = '{ "range": { "' . $range['column'] . '": { "gte": "' . $range['gte'] . '" }}}';
    }

    if ($notCond == '') {
        $params = '{
                "_source": [' . $source . '],
                "query": {
                    "bool": {
                        "must" : [
                              ' . $must . '
                         ],
                        "filter" : [
                            ' . $filter . '
                         ]
                     }
                 },"aggs": {
                    "machine": {
                      "terms": {
                        "field": "' . $aggrCol . '",
                        "size": 100
                      }
                    }
              }
             }';
    } else {
        foreach ($notCond as $key => $value) {
            $notcondition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
        }
        $notcondition = rtrim($notcondition, ',');
        $params = '{
                "_source": [' . $source . '],
                "query": {
                    "bool": {
                        "must" : [
                              ' . $must . '
                         ],
                         "must_not" : [
                            ' . $notcondition . '
                         ],
                        "filter" : [
                            ' . $filter . '
                         ]
                     }
                 },"aggs": {
                    "machine": {
                      "terms": {
                        "field": "' . $aggrCol . '",
                        "size": 100
                      }
                    }
              }
             }';
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $res = curl_exec($curl);
    curl_close($curl);

    $result = FORMAT_RESOL_Aggre_Data($res, $colName);

    return $result;
}

function RESOLEL_getData($tableName, $source, $cond, $range, $notcond)
{

    global $elastic_url;
    $url = $elastic_url . $tableName . "/_search?pretty&size=10000";

    $condition = '';

    foreach ($cond as $key => $value) {
        $condition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
    }
    $must = rtrim($condition, ',');
    if (safe_count($range) > 2) {
        $filter = '{ "range": { "' . $range['column'] . '": { "gte": "' . $range['gte'] . '", "lte" : "' . $range['lte'] . '" }}}';
    } else {
        $filter = '{ "range": { "' . $range['column'] . '": { "gte": "' . $range['gte'] . '" }}}';
    }

    if ($notcond == '') {
        $params = '{
                "_source": [' . $source . '],
                "query": {
                    "bool": {
                        "must" : [
                              ' . $must . '
                         ],
                        "filter" : [
                            ' . $filter . '
                         ]
                     }
                 }
             }';
    } else {
        foreach ($notcond as $key => $value) {
            $notcondition .= '{ "match": { "' . $key . '": "' . $value . '"}},';
        }
        $notcondition = rtrim($notcondition, ',');
        $params = '{
                "_source": [' . $source . '],
                "query": {
                    "bool": {
                        "must" : [
                              ' . $must . '
                         ],
                         "must_not" : [
                            ' . $notcondition . '
                         ],
                        "filter" : [
                            ' . $filter . '
                         ]
                     }
                 }
             }';
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");

    $res = curl_exec($curl);
    curl_close($curl);

    $result = FORMAT_RESOL_Data($res);

    return $result;
}

function FORMAT_RESOL_Aggre_Data($result, $colName)
{
    $curlArray = safe_json_decode($result, TRUE);

    if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 1) {
        $loopsArray = $curlArray['aggregations']['machine']['buckets'];
        foreach ($loopsArray as $key => $value) {
            $data[$key][$colName] = $value['key'];
        }
    } else if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
        $data[0][$colName] = $curlArray['aggregations']['machine']['buckets'][0]['key'];
    } else {
        $data = array();
    }
    return $data;
}

function FORMAT_RESOL_Data($result)
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
