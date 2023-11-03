<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-quer.php';
include_once 'l-util.php';
include_once 'l-elastic.php';
include_once 'l-elasticCapacity.php';
include_once 'l-assetnew.php';

/**
 * Just return `true`. Nothing more.
 */

if (!function_exists("DASH_ValidateKey")) {
    function DASH_ValidateKey($key)
    {
        return true;
    }
}

function DASH_GetSites($key, $pdo, $user)
{
    $sites = [];
    if ($key) {

        $sitesql = $pdo->prepare("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? order by lower(customer)");
        $sitesql->execute([$user]);
        $siteres = $sitesql->fetchAll();

        foreach ($siteres as $key => $val) {
            $sites[] = utf8_encode($val['name']);
        }
    } else {
        echo "Your key has been expired";
    }
    return $sites;
}

function DASH_GetGroups($key, $db, $user)
{
    $groups = [];
    $ch_id = $_SESSION['user']['cId'];
    $userid = $_SESSION['user']['userid'];
    $username = $_SESSION['user']['username'];
    if ($key) {
        $sql = "select mg.mgroupid,mg.mgroupuniq,mg.username,mg.name,mc.mcatid,created,mg.boolstring,mg.style,mg.global "
            . "from " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg join " . $GLOBALS['PREFIX'] . "core.MachineCategories as mc on mg.mcatuniq = mc.mcatuniq "
            . "join " . $GLOBALS['PREFIX'] . "core.GroupMappings gm on mg.mgroupid = gm.groupid where
                    mc.mcatid in (select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category = 'Wiz_SCOP_MC') and gm.username = ?;";
        $stm = $db->prepare($sql);
        $stm->execute([$username]);
        $groupres = $stm->fetchAll(PDO::FETCH_ASSOC);
        foreach ($groupres as $key => $val) {
            $stmc = $db->prepare("select mgmapid from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap where mgroupuniq = '" . $val['mgroupuniq'] . "'");
            $stmc->execute();
            $grpMachCnt = safe_count($stmc->fetchAll(PDO::FETCH_ASSOC));
            if ($grpMachCnt > 0) {
                $txt = utf8_decode(trim($val['name']));
                $groups[$txt] = $val['mgroupid'];
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $groups;
}

function DASH_GatewayStatus($db, $site)
{

    $key = '';
    $db = pdo_connect();
    $json = [];

    $gateWayStatus = 0;
    $custsql = $db->prepare("select siteName,gateWayStatus from " . $GLOBALS['PREFIX'] . "agent.customerOrder where siteName = ? and gateWayStatus = '1' limit 1");
    $custsql->execute([$site]);
    $custres = $custsql->fetch();
    if (safe_count($custres) > 0) {
        $gateWayStatus = $custres['gateWayStatus'];
    } else {
        $gateWayStatus = 0;
    }

    return $gateWayStatus;
}

function DASH_GetGroupName($key, $db, $mgroupId)
{
    $group = '';

    $groupsql = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ? ");
    $groupsql->execute([$mgroupId]);
    $groupres = $groupsql->fetch();
    $group = $groupres['name'];

    return $group;
}

function DASH_GetMachinesSites($key, $pdo, $site, $limit = 0)
{

    $machine = [];

    $start = $limit;
    if (is_array($site)) {

        if (empty($site)) {
            return $machine;
        }

        $in = str_repeat('?,', safe_count($site) - 1) . '?';
        $start = is_numeric($start) ? $start : 0;

        $machinesql = $pdo->prepare("select host,id from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in)");
        $machinesql->execute($site);
    } else {
        $start = is_numeric($start) ? $start : 0;
        $machinesql = $pdo->prepare("select host,id from " . $GLOBALS['PREFIX'] . "core.Census where site = ? limit " . $start . ",100");
        $machinesql->execute([$site]);
    }

    $machineres = $machinesql->fetchAll();

    foreach ($machineres as $key => $val) {
        $machine[$val['id']] = $val['host'];
    }
    return $machine;
}

function DASH_GetMachLastRprtSites($key, $db, $site)
{

    $machine = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        if (is_array($site)) {
            $sitelist = array();
            foreach ($site as $value) {
                array_push($sitelist, $value);
            }
            $in = str_repeat('?,', safe_count($sitelist) - 1) . '?';
            $machinesql = $db->prepare("select site,host,id,last,born from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in)");
            $machinesql->execute($sitelist);
        } else {
            $machinesql = $db->prepare("select site,host,id,last,born from " . $GLOBALS['PREFIX'] . "core.Census where site = ?");
            $machinesql->execute([$site]);
        }
        $machineres = $machinesql->fetchAll();
        foreach ($machineres as $key => $val) {
            $machine[$val['host']]['last'] = $val['last'];
            $machine[$val['id']]['host'] = $val['host'];
            $machine[$val['id']]['site'] = $val['site'];
            $machine[$val['host']]['born'] = $val['born'];
            $machine[$val['host']]['id'] = $val['id'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $machine;
}

/**
 * @param $key - not used
 */
function DASH_GetGroupsMachines($key, $db, $groupID, $limit = 0)
{
    $machine = [];
    $groupIdlist = array();

    $wh = '';

    if (is_array($groupID)) {
        $groupIdlist = implode(",", $groupID);
        $groupIdlistArr = explode(',', $groupIdlist);
        $in = str_repeat('?,', safe_count($groupIdlistArr) - 1) . '?';
        if ($limit != 0) {
            $start = $limit == 100 ? 0 : $limit;
            $sql = $db->prepare("select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           M.mgroupid in ($in) and style>=2
                           group by C.host limit ?,100");
            $params = array_merge($groupIdlistArr, [$start]);
        } else {
            $sql = $db->prepare("select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           M.mgroupid in ($in) and style>=2
                           group by C.host");
            $params = array_merge($groupIdlistArr);
        }
        $sql->execute($params);
    } else {
        if ($limit != 0) {
            $start = $limit == 100 ? 0 : $limit;
            $sql = $db->prepare("select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                               (M.mgroupid = ? or name=?) and style>=2
                               group by C.host limit ?,100");
            $sql->execute([$groupID, $groupID, $start]);
        } else {
            $sql = $db->prepare("select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                               " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                               GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                               and GP.mgroupuniq = M.mgroupuniq and
                               (M.mgroupid = ? or name=?) and style>=2
                               group by C.host");
            $sql->execute([$groupID, $groupID]);
        }
    }
    $machineres = $sql->fetchAll();

    foreach ($machineres as $key => $val) {
        $machine[$val['id']] = $val['host'];
    }

    return $machine;
}

function DASH_GetGroupsUUID($key, $db, $groupID)
{

    $machine = [];

    if (is_array($groupID)) {
        $in = str_repeat('?,', safe_count($groupID) - 1) . '?';
        $machinesql = $db->prepare("select C.host,C.id,C.uuid from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           M.mgroupid in ($in) and style>=2
                           group by C.host");
        $machinesql->execute($groupID);
    } else {
        $machinesql = $db->prepare("select C.host,C.id,C.uuid from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           (M.mgroupid = ? or name = ?) and style>=2
                           group by C.host");
        $machinesql->execute([$groupID, $groupID]);
    }
    $machineres = $machinesql->fetchAll();
    foreach ($machineres as $key => $val) {
        $machine[$val['id']] = $val['uuid'];
    }

    return $machine;
}

function DASH_GetGroupsCustName($key, $db, $uuid)
{
    $uuidArr = array();
    $uuidArr = explode(',', $uuid);
    $site = [];
    $key = DASH_ValidateKey($key);

    $in = str_repeat('?,', safe_count($uuidArr) - 1) . '?';
    $sitesql = $db->prepare("SELECT id,site FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE uuid IN ($in)");
    $sitesql->execute($uuidArr);
    $siteres = $sitesql->fetchAll();

    foreach ($siteres as $key => $val) {
        $site[$val['id']] = $val['site'];
    }
    return $site;
}

function DASH_GetGroupsMachLastRprt($key, $db, $groupID)
{

    $machine = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        if (is_array($groupID)) {
            $in = str_repeat('?,', safe_count($groupID) - 1) . '?';
            $machinesql = $db->prepare("select C.site,C.host,C.id,C.last,C.born  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           M.mgroupid in ($in) and style>=2
                           group by C.host");
            $machinesql->execute($groupID);
        } else {
            $machinesql = $db->prepare("select C.site,C.host,C.id,C.last,C.born  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           M.mgroupid = ? and style>=2
                           group by C.host");
            $machinesql->execute([$groupID]);
        }
        $machineres = $machinesql->fetchAll();
        foreach ($machineres as $key => $val) {
            $machine[$val['host']]['last'] = $val['last'];
            $machine[$val['id']]['host'] = $val['host'];
            $machine[$val['host']]['born'] = $val['born'];
            $machine[$val['host']]['id'] = $val['id'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $machine;
}

function DASH_GetMachLastRprt($key, $db, $censusId)
{

    $machinesql = $db->prepare("select site,host,id,last from " . $GLOBALS['PREFIX'] . "core.Census where id = ?");
    $machinesql->execute([$censusId]);
    $machineres = $machinesql->fetch();

    $machine[$machineres['host']]['last'] = $machineres['last'];
    $machine[$machineres['id']]['host'] = $machineres['host'];
    $machine[$machineres['id']]['site'] = $machineres['site'];
    $machine[$machineres['host']]['born'] = $machineres['born'];
    $machine[$machineres['host']]['id'] = $machineres['id'];

    return $machine;
}

function DASH_GetMachSite($key, $db, $censusId)
{

    $siteName = '';
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = $db->prepare("select site from " . $GLOBALS['PREFIX'] . "core.Census where id = ? ");
        $sql->execute([$censusId]);
        $site = $sql->fetch();
        $siteName = $site['site'];
    } else {
        echo "Your key has been expired";
    }
    return $siteName;
}

function DASH_GetOverAllCompliance($key, $db)
{

    $complianceStatus = [];

    $complianceSql = $db->prepare("select max(status) as stat,censusid from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay group by censusid");
    $complianceSql->execute();
    $compliance = $complianceSql->fetchAll();
    foreach ($compliance as $key => $val) {
        $complianceStatus[$val['censusid']] = $val['stat'];
    }

    return $complianceStatus;
}

function DASH_GetComplianceSite($key, $db, $site)
{

    $complianceStatus = [];

    if (is_array($site)) {
        $in = str_repeat('?,', safe_count($site) - 1) . '?';
        $complianceSql = $db->prepare("select max(status) as stat,censusid from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay as EBD "
            . "join " . $GLOBALS['PREFIX'] . "core.Census as C on C.id = EBD.censusid where c.site in ($in) group by censusid");
        $complianceSql->execute($site);
    } else {
        $complianceSql = $db->prepare("select max(status) as stat,censusid from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay as EBD join " . $GLOBALS['PREFIX'] . "core.Census as C "
            . "on C.id = EBD.censusid where c.site = ? group by censusid");
        $complianceSql->execute([$site]);
    }
    $compliance = $complianceSql->fetchAll();
    foreach ($compliance as $key => $val) {
        $complianceStatus[$val['censusid']] = $val['stat'];
    }

    return $complianceStatus;
}

function DASH_GetComplianceGroup($key, $db, $groupID)
{

    $complianceStatus = [];

    $machines = DASH_GetGroupsMachines($key, $db, $groupID);
    if (safe_count($machines) > 0) {
        $censusId = safe_array_keys($machines);
        $in = str_repeat('?,', safe_count($censusId) - 1) . '?';
        $complianceSql = $db->prepare("select max(status) as stat,censusid from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay where censusid in ($in) group by censusid");
        $complianceSql->execute($censusId);
        $compliance = $complianceSql->fetchAll();

        foreach ($compliance as $key => $val) {
            $complianceStatus[$val['censusid']] = $val['stat'];
        }
    }

    return $complianceStatus;
}

function DASH_GetComplianceMachine($key, $db, $censusId)
{

    $complianceStatus = [];

    $complianceSql = $db->prepare("select max(status) as stat,censusid from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay where censusid = ? group by censusid");
    $complianceSql->execute([$censusId]);
    $compliance = $complianceSql->fetchAll();
    foreach ($compliance as $key => $val) {
        $complianceStatus[$val['censusid']] = $val['stat'];
    }

    return $complianceStatus;
}

function getOsInfo($array)
{
    $arrayInfo = [];
    foreach ($array as $value) {
        if (stripos($value, 'Offline') !== false) {
            $arrayInfo['status'] = 'offline';
        }

        if (stripos($value, 'Online') !== false) {
            $arrayInfo['status'] = 'online';
        }

        if (stripos($value, 'Windows') !== false) {
            $arrayInfo['os'] = 'Windows';
        } elseif (stripos($value, 'Android') !== false) {
            $arrayInfo['os'] = 'Android';
        } elseif (stripos($value, 'OS X') !== false) {
            $arrayInfo['os'] = 'OS X';
        } elseif (stripos($value, 'Linux') !== false) {
            $arrayInfo['os'] = 'Linux';
        } elseif (stripos($value, 'IOS') !== false) {
            $arrayInfo['os'] = 'IOS';
        }

        if (empty($arrayInfo['status'])) {
            $arrayInfo['status'] = 'offline';
        }
        if (empty($arrayInfo['os'])) {
            $arrayInfo['os'] = 'Windows';
        }
    }

    return $arrayInfo;
}

function DASH_GetAllMachineStatus($key, $db, $machines)
{
    $machineStatus = [];
    if (safe_count($machines) > 0) {
        try {
            $redis = RedisLink::connect();

            foreach ($machines as $host) {
                $res = $redis->lrange($host, 0, -1);
                if (!empty($res)) {
                    $osInfo = getOsInfo($res);
                    $machineStatus[$host][] = $osInfo['status'];
                    if ($osInfo['os'] === 'Windows') {
                        $machineStatus[$host][] = 1;
                    } else if ($osInfo['os'] === 'Android') {
                        $machineStatus[$host][] = 2;
                    } else if ($osInfo['os'] === 'OS X') {
                        $machineStatus[$host][] = 3;
                    } else if ($osInfo['os'] === 'Linux') {
                        $machineStatus[$host][] = 4;
                    } else if ($osInfo['os'] === 'IOS') {
                        $machineStatus[$host][] = 5;
                    } else {
                        $machineStatus[$host][] = 1;
                    }
                } else {
                    $machineStatus[$host][] = 'offline';
                    $machineStatus[$host][] = 1;
                }
            }
        } catch (RedisException $ex) {
            logs::log($ex);
            return false;
        }
    }

    return $machineStatus;
}

function DASH_GetAllMachineStatusNOs($key, $db, $machines)
{
    $machineStatus = [];
    if (safe_count($machines) > 0) {
        $redis = RedisLink::connect();
        foreach ($machines as $host) {
            $res = $redis->lrange($host, 0, -1);
            if (!empty($res)) {
                $machineStatus[$host][] = $res[5];
                $machineStatus[$host][] = $res[4];
            } else {
                $machineStatus[$host][] = 'offline';
                $machineStatus[$host][] = 'Windows';
            }
        }
    }
    return $machineStatus;
}

function DASH_GetDeviceInfoSite($key, $db, $site)
{

    if (is_array($site)) {
        $in = str_repeat('?,', safe_count($site) - 1) . '?';
        $sql = $db->prepare("select C.site,C.host,A.hostName,GROUP_CONCAT(A.groupId) grpid,GROUP_CONCAT(CONCAT(A.value1,'##',A.value2,'##',A.value3),'@' ) "
            . "details  from " . $GLOBALS['PREFIX'] . "core.Census C left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId in (1,3) "
            . "where C.site in ($in) group by C.site,C.host order by A.groupId");
        $sql->execute($site);
    } else {
        $sql = $db->prepare("select C.site,C.host,A.hostName,GROUP_CONCAT(A.groupId) grpid,GROUP_CONCAT(CONCAT(A.value1,'##',A.value2,'##',A.value3),'@' ) "
            . "details  from " . $GLOBALS['PREFIX'] . "core.Census C left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId in (1,3) "
            . "where C.site = ? group by C.site,C.host order by A.groupId");
        $sql->execute([$site]);
    }
    $sqlResult = $sql->fetchAll();

    return $sqlResult;
}

function DASH_GetDeviceInfoMach($key, $db, $censusId)
{
    $censusIdArr = array();
    $censusIdArr = explode(',', $censusId);
    $in = str_repeat('?,', safe_count($censusIdArr) - 1) . '?';

    $sql = $db->prepare("select C.site,C.host,A.hostName,GROUP_CONCAT(A.groupId) grpid,GROUP_CONCAT(CONCAT(A.value1,'##',A.value2,'##',A.value3),'@' ) "
        . "details  from " . $GLOBALS['PREFIX'] . "core.Census C left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId in (1,3) "
        . "where C.id in ($in) group by C.site,C.host order by A.groupId");
    $sql->execute($censusIdArr);
    $sqlResult = $sql->fetchAll();

    return $sqlResult;
}

function DASH_GetDeviceInfoGrp($key, $db, $censusArray)
{

    $in = str_repeat('?,', safe_count($censusArray) - 1) . '?';
    if ($census_list != '') {
        $sql = $db->prepare("select C.site,C.host,A.hostName,GROUP_CONCAT(A.groupId) grpid,GROUP_CONCAT(CONCAT(A.value1,'##',A.value2,'##',A.value3),'@' ) "
            . "details  from " . $GLOBALS['PREFIX'] . "core.Census C left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId in (1,3) "
            . "where C.id in ($in) group by C.site,C.host order by A.groupId");
        $sql->execute($censusArray);
        $sqlResult = $sql->fetchAll();
    }

    return $sqlResult;
}

function DASH_GetDeviceInfoForMachines($key, $db, $machineArray)
{
    $devInfoRes = [];
    $in = str_repeat('?,', safe_count($machineArray) - 1) . '?';
    $devInfoSql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary WHERE groupId IN (1,3,4) AND machineName IN ($in) ORDER BY mid,groupId DESC");
    $devInfoSql->execute($machineArray);
    $devInfoRes = $devInfoSql->fetchAll();
    return $devInfoRes;
}

function DASH_GetComplaincePercSite($key, $db, $site)
{

    $compliancePerc = [];
    $compliancePerc['availability'] = 0;
    $compliancePerc['maintenance'] = 0;
    $compliancePerc['resources'] = 0;
    $compliancePerc['security'] = 0;
    $compliancePerc['events'] = 0;
    $compliancePerc['total'] = 0;

    if (is_array($site)) {
        $sitelist = '';
        foreach ($site as $value) {
            $sitelist .= "'" . $value . "',";
        }
        $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
    } else {
        $scope = " C.site = '$site'";
    }
    $compliancePerc = Dash_GetCompliancePerc($db, $scope);

    return $compliancePerc;
}

function DASH_GetComplaincePercGrp($key, $db, $machines)
{
    $compliancePerc = [];
    $compliancePerc['availability'] = 0;
    $compliancePerc['maintenance'] = 0;
    $compliancePerc['resources'] = 0;
    $compliancePerc['security'] = 0;
    $compliancePerc['events'] = 0;
    $compliancePerc['total'] = 0;

    $censusIds = safe_array_keys($machines);
    if (safe_count($censusIds) > 0) {
        $scope = " C.id in (" . implode(",", $censusIds) . ") ";
        $compliancePerc = Dash_GetCompliancePerc($db, $scope);
    }

    return $compliancePerc;
}

function DASH_GetComplaincePercMach($key, $db, $censusId)
{

    $compliancePerc = [];
    $compliancePerc['availability'] = 0;
    $compliancePerc['maintenance'] = 0;
    $compliancePerc['resources'] = 0;
    $compliancePerc['security'] = 0;
    $compliancePerc['events'] = 0;
    $compliancePerc['total'] = 0;

    $scope = " C.id = $censusId ";
    $compliancePerc = Dash_GetCompliancePerc($db, $scope);

    return $compliancePerc;
}

function Dash_GetCompliancePerc($db, $scope)
{

    $now = strtotime('Today');
    $compliancePerc = [];
    $compliancePerc['availability'] = 0;
    $compliancePerc['maintenance'] = 0;
    $compliancePerc['resources'] = 0;
    $compliancePerc['security'] = 0;
    $compliancePerc['events'] = 0;
    $compliancePerc['total'] = 0;

    $cDataQry = $db->prepare("select  itemtype, count(status) as count, status
                      from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD
                        where   EBD.censusid in (select C.id from " . $GLOBALS['PREFIX'] . "core.Census C  where ? )
                      and EBD.userid = 2 and status in (1,2,3) and clienttime > ?  group by EBD.status,EBD.itemtype
                      order by EBD.itemtype,EBD.status");
    $cDataQry->execute([$scope, $now]);
    $compData = $cDataQry->fetchAll();
    $availability = [0, 0, 0];
    $maintenance = [0, 0, 0];
    $resources = [0, 0, 0];
    $security = [0, 0, 0];
    $events = [0, 0, 0];
    foreach ($compData as $key1 => $val) {
        $status = $val['status'];
        $count = $val['count'];
        $item = $val['itemtype'];
        if ($item == 5) {
            $availability[$status - 1] += $count;
        } else if ($item == 7) {
            $security[$status - 1] += $count;
        } else if ($item == 8) {
            $resources[$status - 1] += $count;
        } elseif ($item == 9) {
            $events[$status - 1] += $count;
        } elseif ($item == 10) {
            $maintenance[$status - 1] += $count;
        }
    }
    if ($availability[1] == 0 && $availability[2] == 0 && $maintenance[1] == 0 && $maintenance[2] == 0 && $resources[1] == 0 && $resources[2] == 0 && $security[1] == 0 && $security[2] == 0 && $events[1] == 0 && $events[2] == 0) {
        $compliancePerc['availability'] = 100;
        $compliancePerc['maintenance'] = 100;
        $compliancePerc['resources'] = 100;
        $compliancePerc['security'] = 100;
        $compliancePerc['events'] = 100;
        $compliancePerc['total'] = 100;
        return $compliancePerc;
    } else if ($availability[0] == 0 && $availability[1] == 0 && $availability[2] == 0 && $maintenance[0] == 0 && $maintenance[1] == 0 && $maintenance[2] == 0 && $resources[0] == 0 && $resources[1] == 0 && $resources[2] == 0 && $security[0] == 0 && $security[1] == 0 && $security[2] == 0 && $events[0] == 0 && $events[1] == 0 && $events[2] == 0) {
        $compliancePerc['availability'] = 100;
        $compliancePerc['maintenance'] = 100;
        $compliancePerc['resources'] = 100;
        $compliancePerc['security'] = 100;
        $compliancePerc['events'] = 100;
        $compliancePerc['total'] = 100;
        return $compliancePerc;
    }
    $compliancePerc['availability'] = ($availability > 0) ? round(($availability[0] / (array_sum($availability))) * 100, 2) : 0;
    $compliancePerc['maintenance'] = ($maintenance > 0) ? round(($maintenance[0] / (array_sum($maintenance))) * 100, 2) : 0;
    $compliancePerc['resources'] = ($resources > 0) ? round(($resources[0] / (array_sum($resources))) * 100, 2) : 0;
    $compliancePerc['security'] = ($security > 0) ? round(($security[0] / (array_sum($security))) * 100, 2) : 0;
    $compliancePerc['events'] = ($events > 0) ? round(($events[0] / (array_sum($events))) * 100, 2) : 0;
    $compliancePerc['total'] = round((($availability[0] + $maintenance[0] + $resources[0] + $security[0] + $events[0]) / (array_sum($availability) + array_sum($maintenance) + array_sum($resources) + array_sum($security) + array_sum($events))) * 100, 2);
    return $compliancePerc;
}

function Dash_GetTableName($itemtype)
{
    if ($itemtype == 8) {
        $colname[] = "ResourceItems";
        $colname[] = "resitemid";
    }
    if ($itemtype == 5) {
        $colname[] = "MonitorItems";
        $colname[] = "monitemid";
    }
    if ($itemtype == 7) {
        $colname[] = "SecurityItems";
        $colname[] = "secitemid";
    }
    if ($itemtype == 10) {
        $colname[] = "MaintenanceItems";
        $colname[] = "maintitemid";
    }
    if ($itemtype == 9) {
        $colname[] = "EventItems";
        $colname[] = "eventitemid";
    }
    return $colname;
}

function DASH_GetComplainceDetailsSite($key, $db, $site, $itemtype, $itemid, $status, $detail)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    global $API_enable_comp;

    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " c.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " c.site = '$site'";
        }

        $dbusage = $_SESSION["user"]["usage"];

        if ($dbusage == 1 && $API_enable_comp == 1) {
            $complianceDetails = DASH_GetComplianceDetailsSite_EL($db, $scope, $itemtype, $itemid, $status, $detail);
        } else {

            $complianceDetails = DASH_GetComplianceDetailsSite($db, $scope, $itemtype, $itemid, $status, $detail);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplianceDetailsSite_EL($db, $scope, $itemtype, $itemid, $status, $detail)
{

    global $elastic_url;

    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $Startdate = date('Y-m-d', $time2);
    $Enddate = date('Y-m-d', $time1);

    $CompIndex = createComplianceIndex($Startdate, $Enddate);
    $url = $elastic_url . $CompIndex . "/_search?pretty&size=10000";
    $scope = trim($scope);
    $draw = url::issetInRequest('draw') ? url::requestToAny('draw') : 1;
    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length = url::issetInRequest('length') ? url::requestToAny('length') : 25;

    if (strpos($scope, 'in') !== false) {
        $r = explode("c.site in ", $scope);
        $strSite = rtrim(ltrim($r[1], "("), ")");
        $replace = '"';
        $siteList = "[" . str_replace("'", "$replace", $strSite) . "]";
        $siteName = '{"terms": {"site": ' . $siteList . '}},'
            . '{"term": {"itemtype": ' . $status . '}}';

        if ($itemid != 0) {
            $siteName .= ',{"term": {"itemid": ' . $itemid . '}}';
        }
        if (strpos($status, ',') !== false) {
            $siteName .= ',{"terms": {"status": [' . $status . ']}}';
        } else {
            $siteName .= ',{"term": {"status": ' . $status . '}}';
        }
    }
    if (strpos($scope, 'c.site = ') !== false) {

        $r = explode("c.site = ", $scope);

        $strSite = '"' . rtrim(ltrim($r[1], "'"), "'") . '"';

        $siteList = "[" . $strSite . "]";
        $siteName = '{"term": {"site": ' . $strSite . '}},'
            . '{"term": {"itemtype": ' . $itemtype . '}}';
        if ($itemid != 0) {
            $siteName .= ',{"term": {"itemid": ' . $itemid . '}}';
        }
        if (strpos($status, ',') !== false) {
            $siteName .= ',{"terms": {"status": [' . $status . ']}}';
        } else {
            $siteName .= ',{"term": {"status": ' . $status . '}}';
        }
    }

    $params = '{
         "from" : ' . $start . ', "size" : ' . $length . ',
	"query": {
		"constant_score": {
			"filter": {
				"bool": {

					"must": [
							' . $siteName . '

					]
				}
			}
		}
	},
	"sort": [{
		"status": {
			"order": "asc"
		}
	}, {
		"itemtype": {
			"order": "asc"
		}
	}]
}';

    $params1 = '{

	"query": {
		"constant_score": {
			"filter": {
				"bool": {

					"must": [
							' . $siteName . '

					],"filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
				}
			}
		}
	}
}';
    $tempRes = EL_GetCurlWithLimit($CompIndex, $params);
    $tempRes1 = curlCommonFunction($url, $params1);
    $resultData = EL_FormatCurldata_new($tempRes);
    $resultData1 = safe_json_decode($tempRes1, true);

    $hits = $resultData1['hits']['hits'];
    foreach ($hits as $val) {
        $host = $val['_source']['host'];
        $temp[$host] = $val['_source'];
    }
    $totalCount = $resultData['total'];
    $eventsitesdata = $resultData['result'];

    if (($resultData['total']) > 0) {
        $recordList = array();
        foreach ($eventsitesdata as $row) {
            $time = time();
            $twofourtime = strtotime('-24 hours', $time);
            $row['servertime'];
            $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="user_check gridcheck" value="' . $row['itemtype'] . "_" . $row['itemid'] . "_" . $row['status'] . '" id="' . $row['csid'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" name="' . $row['csid'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" /><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
            $device = '<p class="ellipsis" title="' . $row['host'] . '">' . $row['host'] . '</p>';
            $lastEvent = date("m/d/Y h:i A", $row['clienttime']);
            $eventcnt = '<p class="ellipsis" title="' . $row['eventcnt'] . '">' . $row['eventcnt'] . '</p>';
            $idx = $row['csid'];
            $itemid = $row['itemid'];
            $itemtype = $row['itemtype'];
            $status = $row['status'];
            $itemname = $row['itemname'];
            $recordRytList[$row['host']] = array('checkbox-btn' => $checkBox, 'machine' => $device, 'servertime' => $lastEvent, 'eventcount' => $eventcnt);
            $recordLeftList[$row['itemname']] = array("id" => $row['csid'], "itemname" => $itemname, "itemid" => $itemid, "itemtype" => $itemtype, "status" => $status);
        }
        foreach ($recordRytList as $val) {
            $recordList[] = $val;
        }

        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($temp), "recordsFiltered" => safe_count($temp), "data" => $recordList, "leftData" => $recordLeftList);
    } else {
        $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array(), "leftData" => array());
    }
    return $jsonData;
}

function DASH_GetComplianceDetailsGroup_EL($db, $scope, $itemtype, $itemid, $status, $detail)
{

    global $elastic_url;
    $url = $elastic_url . $CompIndex . "/_search?pretty&size=10000";
    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $Startdate = date('Y-m-d', $time2);
    $Enddate = date('Y-m-d', $time1);
    $CompIndex = createComplianceIndex($Startdate, $Enddate);
    $scope = trim($scope);
    $draw = url::issetInRequest('draw') ? url::requestToAny('draw') : 1;
    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length = url::issetInRequest('length') ? url::requestToAny('length') : 25;

    $sql = $db->prepare("select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com'");
    $sql->execute();
    $sqlRes = $sql->fetch();
    $userid = $sqlRes['userid'];

    if ($itemid != 0) {
        $siteName .= '{"term": {"itemid": ' . $itemid . '}}';
    }
    if (strpos($status, ',') !== false) {
        $siteName .= ',{"terms": {"status": [' . $status . ']}}';
    } else {
        $siteName .= ',{"term": {"status": ' . $status . '}}';
    }

    $params = '{
            "from" : ' . $start . ', "size" : ' . $length . ',
            "query" : {
               "constant_score" : {
                  "filter" : {
                     "bool" : {
                        "must":[
                            {
                               "bool":{
                                    "minimum_should_match":1,
                                    "should":[' . $scope . ']
                                }
                            },
                            {"term": {"userid": "' . $userid . '"}},
                             ' . $siteName . '
                        ],"filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
                    }
                  }
               }
            }
         }';

    $params1 = '{

            "query" : {
               "constant_score" : {
                  "filter" : {
                     "bool" : {
                        "must":[
                            {
                               "bool":{
                                    "minimum_should_match":1,
                                    "should":[' . $scope . ']
                                }
                            },
                            {"term": {"userid": "' . $userid . '"}},
                             ' . $siteName . '
                        ],"filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
                    }
                  }
               }
            }
         }';
    $tempRes = EL_GetCurlWithLimit($CompIndex, $params);
    $resultData = EL_FormatCurldata_new($tempRes);
    $totalCount = $resultData['total'];
    $eventsitesdata = $resultData['result'];

    $tempRes1 = curlCommonFunction($url, $params1);
    $resultData1 = safe_json_decode($tempRes1, true);
    $hits = $resultData1['hits']['hits'];
    foreach ($hits as $val) {
        $host = $val['_source']['host'];
        $temp[$host] = $val['_source'];
    }

    if ($resultData['total'] > 0) {
        $recordRytList = [];
        $recordLeftList = [];
        foreach ($eventsitesdata as $row) {
            $time = time();
            $twofourtime = strtotime('-24 hours', $time);
            if ($row['servertime'] >= $twofourtime) {
                $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="user_check gridcheck" value="' . $row['itemtype'] . "_" . $row['itemid'] . "_" . $row['status'] . '" id="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" name="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" /><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
                $device = '<p class="ellipsis" title="' . $row['host'] . '">' . $row['host'] . '</p>';
                $lastEvent = date("m/d/Y h:i A", $row['clienttime']);
                $eventcnt = '<p class="ellipsis" title="' . $row['eventcnt'] . '">' . $row['eventcnt'] . '</p>';
                $idx = $row['csid'];
                $itemid = $row['itemid'];
                $itemtype = $row['itemtype'];
                $status = $row['status'];
                $itemname = $row['itemname'];
                $recordRytList[$row['host']] = array('checkbox-btn' => $checkBox, 'machine' => $device, 'servertime' => $lastEvent, 'eventcount' => $eventcnt);
            }
        }
        $recordLeftList[] = array("id" => $row['csid'], "itemname" => $itemname, "itemid" => $itemid, "itemtype" => $itemtype, "status" => $status);
        foreach ($recordRytList as $val) {
            $recordList[] = $val;
        }
    } else {
        $recordList = array();
        $recordLeftList = array();
    }
    $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($temp), "recordsFiltered" => safe_count($temp), "data" => $recordList, "leftData" => $recordLeftList);

    return $jsonData;
}

function DASH_GetComplianceDetailsServiceTag_EL($db, $scope, $itemtype, $itemid, $status, $detail)
{

    global $elastic_url;
    $url = $elastic_url . $CompIndex . "/_search?pretty&size=10000";

    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $Startdate = date('Y-m-d', $time2);
    $Enddate = date('Y-m-d', $time1);
    $CompIndex = createComplianceIndex($Startdate, $Enddate);
    $scope = trim($scope);
    $draw = url::issetInRequest('draw') ? url::requestToAny('draw') : 1;
    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length = url::issetInRequest('length') ? url::requestToAny('length') : 25;

    if (strpos($scope, 'in') !== false) {
        $r = explode("c.id in ", $scope);
        $strSite = rtrim(ltrim($r[1], "("), ")");
        $replace = "'";
        $siteList = str_replace("'", "", $strSite);
        $siteName = '{"terms": {"censusid": ' . $siteList . '}},'
            . '{"term": {"itemtype": ' . $itemtype . '}}';
        if ($itemid != 0) {
            $siteName .= ',{"term": {"itemid": ' . $itemid . '}}';
        }
        if (strpos($status, ',') !== false) {
            $siteName .= ',{"terms": {"status": [' . $status . ']}}';
        } else {
            $siteName .= ',{"term": {"status": ' . $status . '}}';
        }
    } else {
        $r = explode("c.id = ", $scope);
        $strSite = rtrim(ltrim($r[1], "("), ")");
        $replace = "'";
        $siteList = str_replace("'", "", $strSite);
        $siteName = '{"term": {"censusid": ' . $siteList . '}},'
            . '{"term": {"itemtype": ' . $itemtype . '}}';
        if ($itemid != 0) {
            $siteName .= ',{"term": {"itemid": ' . $itemid . '}}';
        }
        if (strpos($status, ',') !== false) {
            $siteName .= ',{"terms": {"status": [' . $status . ']}}';
        } else {
            $siteName .= ',{"term": {"status": ' . $status . '}}';
        }
    }

    $params = '{
         "from" : ' . $start . ', "size" : ' . $length . ',
	"query": {
		"constant_score": {
			"filter": {
				"bool": {
					"must": [
						' . $siteName . '
					],"filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
				}
			}
		}
	}
}';

    $params1 = '{
	"query": {
		"constant_score": {
			"filter": {
				"bool": {
					"must": [
						' . $siteName . '
					],"filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
				}
			}
		}
	}
}';

    $tempRes = EL_GetCurlWithLimit($CompIndex, $params);
    $resultData = EL_FormatCurldata_new($tempRes);
    $totalCount = $resultData['total'];
    $eventsitesdata = $resultData['result'];

    $tempRes1 = curlCommonFunction($url, $params1);
    $resultData1 = safe_json_decode($tempRes1, true);
    $hits = $resultData1['hits']['hits'];
    foreach ($hits as $val) {
        $host = $val['_source']['host'];
        $temp[$host] = $val['_source'];
    }

    if ($resultData['total'] > 0) {
        $recordRytList = [];
        $recordLeftList = [];
        foreach ($eventsitesdata as $row) {
            $time = time();
            $twofourtime = strtotime('-24 hours', $time);
            if ($row['servertime'] >= $twofourtime) {
                $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="user_check gridcheck" value="' . $row['itemtype'] . "_" . $row['itemid'] . "_" . $row['status'] . '" id="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" name="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" /><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
                $device = '<p class="ellipsis" title="' . $row['host'] . '">' . $row['host'] . '</p>';
                $lastEvent = date("m/d/Y h:i A", $row['clienttime']);
                $eventcnt = '<p class="ellipsis" title="' . $row['eventcnt'] . '">' . $row['eventcnt'] . '</p>';
                $idx = $row['csid'];
                $itemid = $row['itemid'];
                $itemtype = $row['itemtype'];
                $status = $row['status'];
                $itemname = $row['itemname'];
                $recordRytList[$row['host']] = array('checkbox-btn' => $checkBox, 'machine' => $device, 'servertime' => $lastEvent, 'eventcount' => $eventcnt);
            }
        }
        $recordLeftList[] = array("id" => $row['csid'], "itemname" => $itemname, "itemid" => $itemid, "itemtype" => $itemtype, "status" => $status);
        foreach ($recordRytList as $val) {
            $recordList[] = $val;
        }
    } else {
        $recordList = array();
        $recordLeftList = array();
    }
    $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($temp), "recordsFiltered" => safe_count($temp), "data" => $recordList, "leftData" => $recordLeftList);

    return $jsonData;
}

function DASH_GetComplainceDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    global $API_enable_comp;
    $dbusage = $_SESSION["user"]["usage"];

    if ($key) {
        $censusIds = safe_array_keys($machines);
        if (safe_count($censusIds) > 0) {
            $scope = " c.id in (" . implode(",", $censusIds) . ") ";
            if ($dbusage == 1 && $API_enable_comp == 1) {
                foreach ($machines as $key => $value) {
                    $machinelist .= '{"match": {"host": "' . $value . '"}},';
                }
                $machinelist = rtrim($machinelist, ',');
                $complianceDetails = DASH_GetComplianceDetailsGroup_EL($db, $machinelist, $itemtype, $itemid, $status, $detail);
            } else {
                $complianceDetails = DASH_GetComplianceDetails($db, $scope, $itemtype, $itemid, $status, $detail);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status, $detail)
{

    $complianceDetails = [];

    global $API_enable_comp;

    $scope = " c.id = $censusId ";
    $dbusage = $_SESSION["user"]["usage"];

    if ($dbusage == 1 && $API_enable_comp == 1) {
        $complianceDetails = DASH_GetComplianceDetailsServiceTag_EL($db, $scope, $itemtype, $itemid, $status, $detail);
    } else {
        $complianceDetails = DASH_GetComplianceDetails($db, $scope, $itemtype, $itemid, $status, $detail);
    }

    return $complianceDetails;
}

function DASH_GetComplianceDetailsSite($db, $scope, $itemtype, $itemid, $status, $detail)
{

    $complianceDetails = [];
    $now = strtotime('Today');
    if ($itemtype == 0) {
        return $complianceDetails;
    }
    if ($status == 0) {
        $statusStr = ' ';
    } else {
        $statusStr = " and status = $status ";
    }
    if ($detail == 0) {
        $groupBy = " group by ebd.itemid";
    } else {
        $groupBy = '';
    }
    $dbusage = $_SESSION["user"]["usage"];
    if ($dbusage == 1 || $dbusage == '1') {

        $complItemsEL = DASH_GetComplianceDetails_EL($db, $scope, $itemtype, $itemid, $status, $detail);
        return $complItemsEL;
    } else {
        $userSql = $db->prepare("select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com' ");
        $userSql->execute();
        $userRes = $userSql->fetch();
        $userId = $userRes['userid'];

        if ($itemtype == '1') {

            $sql = "(SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,mi.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems mi
            ON mi.monitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE mi.enabled =1 and
            ebd.itemtype =5 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,mi.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems mi
            ON mi.maintitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE mi.enabled=1 and
            ebd.itemtype = 10 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,RI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI
            ON RI.resitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE RI.enabled=1 and
            ebd.itemtype =8 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,SI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI
            ON SI.secitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE SI.enabled=1 and
            ebd.itemtype =7 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,EI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN  " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI
            ON EI.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE EI.enabled=1 and
            ebd.itemtype = 9 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY c.id,status desc)";
        } else {

            $tableName = Dash_GetTableName($itemtype);
            $otherConstraint = '';

            if ($itemid != 0) {
                $otherConstraint .= " and EBD.itemid = $itemid";
            }

            if ($status != 0) {
                $otherConstraint .= " and EBD.status = $status";
            }

            if ($itemtype != 1) {
                $otherConstraint .= " and EBD.itemtype = $itemtype";
            }

            if ($detail == 0) {
                $groupBy = " group by EBD.itemid";
            } else {
                $groupBy = '';
            }

            $sql = "select EBD.itemtype,status,EBD.clienttime,EBD.itemid,c.id,c.host,T.name,EBD.eventcount
                    from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD
                    join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid
                    join " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] as T
                    on T.$tableName[1] = EBD.itemid
                    where $scope  and clienttime > $now
                    and EBD.userid = $userId $otherConstraint
                    $groupBy order by c.id,EBD.itemtype,EBD.status";
        }

        $complianceDetails = find_many($sql, $db);
        return $complianceDetails;
    }
}

function DASH_GetComplianceDetails($db, $scope, $itemtype, $itemid, $status, $detail)
{

    $complianceDetails = [];
    $now = strtotime('Today');
    if ($itemtype == 0) {
        return $complianceDetails;
    }
    if ($status == 0) {
        $statusStr = ' ';
    } else {
        $statusStr = " and status = $status ";
    }
    if ($detail == 0) {
        $groupBy = " group by ebd.itemid";
    } else {
        $groupBy = '';
    }
    $dbusage = $_SESSION["user"]["usage"];
    if ($dbusage == 1 || $dbusage == '1') {

        $complItemsEL = DASH_GetComplianceDetails_EL($db, $scope, $itemtype, $itemid, $status, $detail);
        return $complItemsEL;
    } else {
        $userSql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com' ";
        $userRes = find_one($userSql, $db);
        $userId = $userRes['userid'];

        if ($itemtype == '1') {

            $sql = "(SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,mi.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems mi
            ON mi.monitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE mi.enabled =1 and
            ebd.itemtype =5 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,mi.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems mi
            ON mi.maintitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE mi.enabled=1 and
            ebd.itemtype = 10 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,RI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI
            ON RI.resitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE RI.enabled=1 and
            ebd.itemtype =8 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,SI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI
            ON SI.secitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE SI.enabled=1 and
            ebd.itemtype =7 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,EI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN  " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI
            ON EI.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE EI.enabled=1 and
            ebd.itemtype = 9 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY c.id,status desc)";
        } else {

            $tableName = Dash_GetTableName($itemtype);
            $otherConstraint = '';

            if ($itemid != 0) {
                $otherConstraint .= " and EBD.itemid = $itemid";
            }

            if ($status != 0) {
                $otherConstraint .= " and EBD.status = $status";
            }

            if ($itemtype != 1) {
                $otherConstraint .= " and EBD.itemtype = $itemtype";
            }

            if ($detail == 0) {
                $groupBy = " group by EBD.itemid";
            } else {
                $groupBy = '';
            }

            $sql = "select EBD.itemtype,status,EBD.clienttime,EBD.itemid,c.id,c.host,T.name,EBD.eventcount
                    from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD
                    join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid
                    join " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] as T
                    on T.$tableName[1] = EBD.itemid
                    where $scope  and clienttime > $now
                    and EBD.userid = $userId $otherConstraint
                    $groupBy order by c.id,EBD.itemtype,EBD.status";
        }

        $complianceDetails = find_many($sql, $db);
        return $complianceDetails;
    }
}

function DASH_GetComplianceDetails_EL($db, $scope, $itemtype, $itemid, $status, $detail)
{

    $complianceDetails = [];
    $now = strtotime('Today');
    if ($itemtype == 0) {
        return $complianceDetails;
    }
    if ($status == 0) {
        $statusStr = ' ';
    } else {
        $statusStr = " and status = $status ";
    }
    if ($detail == 0) {
        $groupBy = " group by ebd.itemid";
    } else {
        $groupBy = '';
    }

    $userSql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com' ";
    $userRes = find_one($userSql, $db);
    $userId = $userRes['userid'];

    if ($itemtype == '1') {

        $sql = "(SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,mi.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems mi
            ON mi.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE mi.enabled =1 and
            ebd.itemtype =5 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,mi.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems mi
            ON mi.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE mi.enabled=1 and
            ebd.itemtype = 10 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,RI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems RI
            ON RI.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE RI.enabled=1 and
            ebd.itemtype =8 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,SI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems SI
            ON SI.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE SI.enabled=1 and
            ebd.itemtype =7 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY status desc)
            UNION
            (SELECT ebd.itemtype,itemid,ebd.`status`,ebd.clienttime,EI.name,c.id,c.host,ebd.eventcount  FROM
            " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
            JOIN  " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI
            ON EI.eventitemid=ebd.itemid
            JOIN " . $GLOBALS['PREFIX'] . "core.Census c
            ON c.id=ebd.censusid
            WHERE EI.enabled=1 and
            ebd.itemtype = 9 and clienttime > $now and
            ebd.userid = $userId and $scope $statusStr
            $groupBy
            ORDER BY c.id,status desc)";
    } else {

        $tableName = Dash_GetTableName(9);
        $otherConstraint = '';

        if ($itemid != 0) {
            $otherConstraint .= " and EBD.itemid = $itemid";
        }

        if ($status != 0) {
            $otherConstraint .= " and EBD.status = $status";
        }

        if ($itemtype != 1) {
            $otherConstraint .= " and EBD.itemtype = $itemtype";
        }

        if ($detail == 0) {
            $groupBy = " group by EBD.itemid";
        } else {
            $groupBy = '';
        }

        $sql = "select EBD.itemtype,status,EBD.clienttime,EBD.itemid,c.id,c.host,T.name,EBD.eventcount
                    from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD
                    join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid
                    join " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] as T
                    on T.$tableName[1] = EBD.itemid
                    where $scope  and clienttime > $now
                    and EBD.userid = $userId $otherConstraint
                    $groupBy order by c.id,EBD.itemtype,EBD.status";
    }

    $complianceDetails = find_many($sql, $db);
    return $complianceDetails;
}

function DASH_GetComplainceFilterDetailsSite($key, $db, $site, $itemtype, $itemid, $status, $detail, $FilterDetail)
{
    $dbusage = $_SESSION["user"]["usage"];
    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    global $API_enable_comp;

    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " c.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " c.site = '$site'";
        }
        if ($dbusage == 1 && $API_enable_comp == 1) {
            $searchType = $_SESSION['searchType'];
            $searchValue = $_SESSION['searchValue'];
            if ($searchType == "Sites" && $searchValue == "All") {
                $sites = DASH_GetSites("", $db, $user);
                foreach ($site as $sites) {
                    $filterString .= '{"match": {"site": "' . $sites . '"}},';
                }
                $filterString = rtrim($filterString, ',');
            } else if ($searchType == "Sites") {
                $filterString .= '{"match": {"site": "' . $searchValue . '"}}';
            }

            $complianceDetails = DASH_GetComplianceFilterDetails_EL($db, $filterString, $itemtype, $itemid, $status, $detail, $FilterDetail);
        } else {
            $complianceDetails = DASH_GetComplianceFilterDetails($db, $scope, $itemtype, $itemid, $status, $detail);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceFilterDetailsGrp($key, $db, $machines, $itemtype, $itemid, $status, $detail, $FilterDetail)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    $dbusage = $_SESSION["user"]["usage"];
    global $API_enable_comp;

    if ($key) {
        $censusIds = safe_array_keys($machines);
        if (safe_count($censusIds) > 0) {
            $scope = " c.id in (" . implode(",", $censusIds) . ") ";
            if ($dbusage == 1 && $API_enable_comp == 1) {
                foreach ($machines as $key => $value) {
                    $sitefilter .= '{"match": {"host": "' . $value . '"}},';
                }
                $sitefilter = rtrim($sitefilter, ',');

                $complianceDetails = DASH_GetComplianceFilterDetails_EL($db, $sitefilter, $itemtype, $itemid, $status, $detail, $FilterDetail);
            } else {
                $complianceDetails = DASH_GetComplianceFilterDetails($db, $scope, $itemtype, $itemid, $status, $detail);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceFilterDetailsMach($key, $db, $censusId, $itemtype, $itemid, $status, $detail, $FilterDetail)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    global $API_enable_comp;
    $dbusage = $_SESSION["user"]["usage"];
    if ($key) {
        $scope = " c.id = $censusId ";
        if ($dbusage == 1 && $API_enable_comp == 1) {
            $searchType = $_SESSION['searchType'];
            $searchValue = $_SESSION['searchValue'];
            $parentValue = $_SESSION['rparentName'];

            $siteName = DASH_getSiteName($db, $searchValue);
            $machineStr = '{"term": {"host": "' . $searchValue . '"}}';
            $siteStr = '{"match": {"site": "' . $siteName . '"}},{"term": {"host": "' . $searchValue . '"}}';

            $complianceDetails = DASH_GetComplianceFilterDetails_EL($db, $siteStr, $itemtype, $itemid, $status, $detail, $FilterDetail);
        } else {
            $complianceDetails = DASH_GetComplianceFilterDetails($db, $scope, $itemtype, $itemid, $status, $detail);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplianceFilterDetails($db, $scope, $itemtype, $itemid, $status, $detail)
{
    $complianceDetails = [];
    $now = strtotime('Today');
    if ($itemtype == 0) {
        return $complianceDetails;
    }
    if ($status == 0) {
        $statusStr = ' ';
    } else {
        $statusStr = " and status in ($status) ";
    }

    if ($detail == 0) {
        $groupBy = " group by EBD.itemid";
    } else {
        $groupBy = '';
    }
    $otherConstraint = "";
    if ($itemid != 0) {
        $otherConstraint = " and EBD.itemid = $itemid";
    }

    $userSql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com' ";
    $userRes = find_one($userSql, $db);
    $userId = $userRes['userid'];

    $dbusage = $_SESSION["user"]["usage"];
    if ($dbusage == 1 || $dbusage == '1') {

        $itemtypeexp = explode(",", $itemtype);
        foreach ($itemtypeexp as $key => $val) {
            $tableName = Dash_GetTableName(9);

            $sql .= "(select EBD.itemtype,EBD.servertime,status,EBD.clienttime,EBD.itemid,c.id,c.host,T.name,EBD.eventcount
                        from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD
                        join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid
                        join " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] as T
                        on T.$tableName[1] = EBD.itemid
                        where $scope  and clienttime > $now
                        and EBD.userid = $userId and EBD.itemtype = $val $statusStr $otherConstraint
                        $groupBy order by c.id,EBD.itemtype,EBD.status)UNION";
        }
    } else {
        $itemtypeexp = explode(",", $itemtype);
        foreach ($itemtypeexp as $key => $val) {
            $tableName = Dash_GetTableName($val);
            $sql .= "(select EBD.itemtype,status,EBD.clienttime,EBD.itemid,c.id,c.host,T.name,EBD.eventcount
                        from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD
                        join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid
                        join " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] as T
                        on T.$tableName[1] = EBD.itemid
                        where $scope  and clienttime > $now
                        and EBD.userid = $userId and EBD.itemtype = $val $statusStr $otherConstraint
                        $groupBy order by c.id,EBD.itemtype,EBD.status)UNION";
        }
    }

    $sql = rtrim($sql, "UNION");
    $complianceDetails = find_many($sql, $db);
    return $complianceDetails;
}

function DASH_GetComplianceFilterDetails_EL($db, $scope, $itemtype, $itemid, $status, $detail, $FilterDetail)
{
    $draw = url::issetInRequest('draw') ? url::requestToAny('draw') : 1;
    $complianceDetails = array();
    $time1 = time();
    $time2 = strtotime("-24 hours", $time1);
    $Startdate = date('Y-m-d', $time2);
    $Enddate = date('Y-m-d', $time1);
    $start = url::issetInRequest('start') ? url::requestToAny('start') : 0;
    $length = url::issetInRequest('length') ? url::requestToAny('length') : 10000;

    $CompIndex = createComplianceIndex($Startdate, $Enddate);

    if ($itemtype == 0) {
        return array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => $complianceDetails, "leftData" => $complianceDetails);
    } else if ($FilterDetail != 'FilterDetail') {
        $itemVal = "[" . $itemtype . "]";
        $itemtypeVal .= '{"terms": {"itemtype": ' . $itemVal . '}}';
        if ($status == 0) {
            $statusStr = ' ';
        } else {
            $siteVal = "[" . rtrim($status, ',') . "]";
            $statusStr .= '{"terms": {"status": ' . $siteVal . '}}';
        }
        $userSql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com' ";
        $userRes = find_one($userSql, $db);
        $userId = $userRes['userid'];

        if (($_SESSION['searchType'] == 'Sites' && $_SESSION['searchValue'] == 'All') || $_SESSION['searchType'] == 'Groups') {
            $params = '{
                        "from" : ' . $start . ', "size" : ' . $length . ',
                        "query" : {
                           "constant_score" : {
                              "filter" : {
                                 "bool" : {
                                    "must":[
                                        {
                                           "bool":{
                                                "minimum_should_match":1,
                                                "should":[' . $scope . ']
                                            }
                                        },
                                        {"term": {"userid": "' . $userId . '"}},
                                         ' . $statusStr . ',
                                        ' . $itemtypeVal . '
                                    ],"filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
                                }
                              }
                           }
                        }
                     }';
        } else {
            $siteName = $scope
                . ',{"term": {"userid": ' . $userId . '}}';

            $params = '{
            "from" : ' . $start . ', "size" : ' . $length . ',
            "query" : {
              "constant_score" : {
                  "filter" : {
                     "bool" : {
                       "must" : [
                       ' . $statusStr . ',
                          ' . $itemtypeVal . ',
                         ' . $siteName . '
                        ],
                        "filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
                    }
                  }
               }
            }
        }';
        }
        $tempRes = EL_GetCurlWithLimit($CompIndex, $params);
        $resultData = EL_FormatCurldata_new($tempRes);

        $totalCount = $resultData['total'];
        $eventsitesdata = $resultData['result'];

        if (isset($resultData['total']) > 0) {
            $recordRytList = array();
            $recordLeftList = array();
            foreach ($eventsitesdata as $row) {
                $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="user_check gridcheck" value="' . $row['itemtype'] . "_" . $row['itemid'] . "_" . $row['status'] . '" id="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" name="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" /><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
                $device = '<p class="ellipsis" title="' . $row['host'] . '">' . $row['host'] . '</p>';
                $lastEvent = date("m/d/Y h:i A", $row['clienttime']);
                $eventcnt = '<p class="ellipsis" title="' . $row['eventcnt'] . '">' . $row['eventcnt'] . '</p>';
                $idx = $row['csid'];
                $itemid = $row['itemid'];
                $itemtype = $row['itemtype'];
                $status = $row['status'];
                $itemname = $row['itemname'];

                $recordRytList[$row['host']] = array('checkbox-btn' => $checkBox, 'machine' => $device, 'servertime' => $lastEvent, 'eventcount' => $eventcnt);
                $recordLeftList[$itemname] = array("id" => $idx, "itemname" => $itemname, "itemid" => $itemid, "itemtype" => $itemtype, "status" => $status);
            }
            foreach ($recordRytList as $val) {
                $recordRight[] = $val;
            }
        } else {
            $recordRight = array();
        }

        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($recordRight), "recordsFiltered" => safe_count($recordRight), "data" => $recordRight, "leftData" => $recordLeftList);
        return $jsonData;
    } else {

        $scope = trim($scope);
        if ($status == 0) {
            $statusStr = ' ';
        } else {

            $siteVal = "[" . rtrim($status, ',') . "]";
            $statusStr .= '{"terms": {"status": ' . $siteVal . '}}';
        }

        $siteName = '{"term": {"itemtype": ' . $itemtype . '}},' . $scope . ',' . $statusStr;

        $params = '{
            "from" : ' . $start . ', "size" : ' . $length . ',
            "query": {
                    "constant_score": {
                            "filter": {
                                    "bool": {
                                            "must": [
                                                            ' . $siteName . '
                                            ],
                                            "filter": [ { "range": { "servertime": { "gte": "' . $time2 . '" , "lte": "' . $time1 . '" }}} ]
                                    }
                            }
                    }
            },
            "sort": [{
                    "status": {
                            "order": "asc"
                    }
            }, {
                    "itemtype": {
                            "order": "asc"
                    }
            }]
    }';
        $tempRes = EL_GetCurlWithLimit($CompIndex, $params);
        $resultData = EL_FormatCurldata_new($tempRes);

        $totalCount = $resultData['total'];
        $eventsitesdata = $resultData['result'];

        if (isset($resultData['total']) > 0) {
            foreach ($eventsitesdata as $row) {
                $time = time();
                $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="user_check gridcheck" value="' . $row['itemtype'] . "_" . $row['itemid'] . "_" . $row['status'] . '" id="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" name="' . $row['id'] . "_" . $row['itemtype'] . "_" . $row['itemid'] . '_rc" /><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
                $device = '<p class="ellipsis" title="' . $row['host'] . '">' . $row['host'] . '</p>';
                $lastEvent = date("m/d/Y h:i A", $row['clienttime']);
                $eventcnt = '<p class="ellipsis" title="' . $row['eventcnt'] . '">' . $row['eventcnt'] . '</p>';
                $idx = $row['csid'];
                $itemid = $row['itemid'];
                $itemtype = $row['itemtype'];
                $status = $row['status'];
                $itemname = $row['itemname'];
                $recordRytList[$row['host']] = array('checkbox-btn' => $checkBox, 'machine' => $device, 'servertime' => $lastEvent, 'eventcount' => $eventcnt);
            }
            foreach ($recordRytList as $val) {
                $recordRight[] = $val;
            }
        } else {
            $recordRight = array();
        }
        $jsonData = array("recordsTotal" => safe_count($recordRight), "recordsFiltered" => safe_count($recordRight), "data" => $recordRight, "draw" => $draw);
        return $jsonData;
    }
}

function DASH_ResetComplianceItem($key, $db, $censusId, $itemType, $itemId)
{

    $key = DASH_ValidateKey($key);
    $dbUsage = $_SESSION['user']['usage'];
    global $API_enable_comp;

    if ($key) {
        $userSql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com' ";
        $userRes = find_one($userSql, $db);
        $userId = $userRes['userid'];

        if ($dbUsage == 1 && $API_enable_comp == 1) {
            $sqlres = resetCompliance($censusId);
        } else {
            $sql = "delete from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay "
                . "where censusid in($censusId) and itemtype in($itemType) and itemid in($itemId) and userid = $userId";
            $sqlres = redcommand($sql, $db);
        }
        if ($sqlres) {
            return 1;
        } else {
            return 0;
        }
    }
}

function DASH_GetSiteOsCount($key, $db, $site)
{

    $osDetails = [];
    $key = DASH_ValidateKey($key);

    $dataId = DASH_getDataId($db, 'operating system');

    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $sql = "select count(C.machineid) as count,value as value1 from " . $GLOBALS['PREFIX'] . "asset.Machine C left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId where C.cust in (" . rtrim($sitelist, ",") . ") group by value";
        } else {

            $sql = "select count(C.machineid) as count,value as value1 from " . $GLOBALS['PREFIX'] . "asset.Machine C left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId where C.cust = '$site' group by value";
        }

        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $osDetails[$val['value1']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $osDetails;
}

function DASH_GetMachineOsCount($key, $db, $censusId)
{

    $osDetails = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "select count(id) as count, value1 from (select C.id, value1 from " . $GLOBALS['PREFIX'] . "core.Census C  "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
            . "on t.machineName = C.host and t.site = C.site and t.groupId = 1 "
            . "where C.id = $censusId "
            . "group by t.mid) as k group by value1 limit 1";
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $osDetails[$val['value1']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $osDetails;
}

function DASH_GetGrpOsCount($key, $db, $machines)
{

    $osDetails = [];
    $key = DASH_ValidateKey($key);

    $dataId = DASH_getDataId($db, 'operating system');

    if ($key) {
        $cIds = safe_array_keys($machines);

        $hostName = "'" . implode("'" . ',' . "'", $machines) . "'";

        if (safe_count($cIds) != 0) {

            $sql = "select count(C.machineid) as count,value as value1 " .
                "from " . $GLOBALS['PREFIX'] . "asset.Machine C left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId " .
                "where C.host in ($hostName) group by value";
            $sqlResult = find_many($sql, $db);
            foreach ($sqlResult as $key => $val) {
                $osDetails[$val['value1']] = $val['count'];
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $osDetails;
}

function DASH_GetSiteChassisMakeCount($key, $db, $site)
{
    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $sql = "select count(C.id) as count, value3 from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.site in (" . rtrim($sitelist, ",") . ") "
                . "group by value3";
        } else {
            $sql = "select count(C.id) as count, value3 from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.site = '$site' "
                . "group by value3";
        }
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $chassisDetails[$val['value3']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_GetMachineChassisMakeCount($key, $db, $censusId)
{
    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select count(C.id) as count, value3 from " . $GLOBALS['PREFIX'] . "core.Census C  "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
            . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
            . "where C.id = $censusId "
            . "group by value3 limit 1";
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $chassisDetails[$val['value3']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_GetGrpChassisMakeCount($key, $db, $machines)
{
    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $sql = "select count(C.id) as count, value3 from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.id in (" . implode(",", $cIds) . ")"
                . "group by value3";
            $sqlResult = find_many($sql, $db);
            foreach ($sqlResult as $key => $val) {
                $chassisDetails[$val['value3']] = $val['count'];
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_GetAllChassisTypeCount($key, $db)
{

    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select count(mid) as count,value2 "
            . "from " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary "
            . "where groupId = 3 and site = '$site' "
            . "group by value2";
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $chassisDetails[$val['value2']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_GetSiteChassisTypeCount($key, $db, $site)
{

    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $sql = "select count(C.id) as count, value2 from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.site in (" . rtrim($sitelist, ",") . ") "
                . "group by value2";
        } else {
            $sql = "select count(C.id) as count, value2 from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.site = '$site' "
                . "group by value2";
        }
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $chassisDetails[$val['value2']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_GetMachineChassisTypeCount($key, $db, $censusId)
{

    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select count(C.id) as count, value2 from " . $GLOBALS['PREFIX'] . "core.Census C  "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
            . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
            . "where C.id = $censusId "
            . "group by value2 limit 1";
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $chassisDetails[$val['value2']] = $val['count'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_GetGrpChassisTypeCount($key, $db, $machines)
{

    $chassisDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $sql = "select count(C.id) as count, value2 from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.id in (" . implode(",", $cIds) . ")"
                . "group by value2";
            $sqlResult = find_many($sql, $db);
            foreach ($sqlResult as $key => $val) {
                $chassisDetails[$val['value2']] = $val['count'];
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $chassisDetails;
}

function DASH_HostNSerialNoMapSite($key, $db, $site)
{

    $map = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $sql = "select C.id,C.host,t.hostName,t.value3 "
                . "from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $sql = "select C.id,C.host,t.hostName,t.value3 "
                . "from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.site = '$site' ";
        }
        $sqlResult = find_many($sql, $db);
        foreach ($sqlResult as $key => $val) {
            $map[$val['id']][] = $val['host'];
            $map[$val['id']][] = $val['hostName'];
            $map[$val['id']][] = $val['value3'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $map;
}

function DASH_HostNSerialNoMapGrp($key, $db, $machines)
{

    $map = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $sql = "select C.id,C.host,t.hostName,t.value3 "
                . "from " . $GLOBALS['PREFIX'] . "core.Census C  "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary t "
                . "on t.machineName = C.host and t.site = C.site and t.groupId = 3 "
                . "where C.id in (" . implode(",", $cIds) . ")";
            $sqlResult = find_many($sql, $db);
            foreach ($sqlResult as $key => $val) {
                $map[$val['id']][] = $val['host'];
                $map[$val['id']][] = $val['hostName'];
                $map[$val['id']][] = $val['value3'];
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $map;
}

function DASH_HostNSerialNoMapMach($key, $db, $censusId)
{

    $map = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select C.id,C.host,hostName,value3 "
            . "from " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary as t "
            . "join " . $GLOBALS['PREFIX'] . "core.Census as C "
            . "on C.host = t.machineName "
            . "where groupId = 3 and C.id = $censusId limit 1";
        $sqlResult = find_one($sql, $db);
        $map[$sqlResult['id']][] = $sqlResult['host'];
        $map[$sqlResult['id']][] = $sqlResult['hostName'];
        $map[$sqlResult['id']][] = $sqlResult['value3'];
    } else {
        echo "Your key has been expired";
    }
    return $map;
}

function DASH_GetSummaryRprtSite($key, $db, $site, $itemId, $reportdurtn)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {

        $dbusage = 1;
        if (isset($dbusage) && $dbusage == 1) {
            $complianceDetails = DASH_GetGetSummaryRprt_EL($db, $site, $itemId, $reportdurtn);
        } else {
            if (is_array($site)) {
                $sitelist = '';
                foreach ($site as $value) {
                    $sitelist .= "'" . $value . "',";
                }
                $scope = " c.site in (" . rtrim($sitelist, ",") . ")";
            } else {
                $scope = " c.site = '$site'";
            }
            $complianceDetails = DASH_GetGetSummaryRprt_OLD($db, $scope, $itemId, $reportdurtn);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetGetSummaryRprtGrp($key, $db, $machines, $itemId, $reportdurtn)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    $dbusage = 1;
    if ($key) {
        if (isset($dbusage) && $dbusage == 1) {
            $complianceDetails = DASH_GetGetSummaryRprt_EL($db, $machines, $itemId, $reportdurtn);
        } else {
            $censusIds = safe_array_keys($machines);
            if (safe_count($censusIds) > 0) {
                $scope = " c.id in (" . implode(",", $censusIds) . ") ";
                $complianceDetails = DASH_GetGetSummaryRprt_OLD($db, $scope, $itemId, $reportdurtn);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetSummaryRprtMach($key, $db, $censusId, $itemId, $reportdurtn)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    $dbusage = 1;
    if ($key) {
        if (isset($dbusage) && $dbusage == 1) {
            $complianceDetails = DASH_GetGetSummaryRprt_EL($db, $censusId, $itemId, $reportdurtn);
        } else {
            $scope = " c.id = $censusId ";
            $complianceDetails = DASH_GetGetSummaryRprt_OLD($db, $scope, $itemId, $reportdurtn);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetGetSummaryRprt_OLD($db, $scope, $itemId, $reportdurtn)
{

    $summaryDetails = [];
    $itemIds = [];
    foreach ($itemId as $value) {
        $itemIds[] = $value['eventitemid'];
    }
    $itemIds = implode(",", $itemIds);

    $sql = "select EBD.itemtype,status,EBD.servertime,EBD.itemid,c.id,c.host,T.name
                 from " . $GLOBALS['PREFIX'] . "dashboard.ComplianceSummary EBD
                 join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid
                 join " . $GLOBALS['PREFIX'] . "dashboard.EventItems as T
                 on T.eventitemid = EBD.itemid
                 where $scope and servertime > $reportdurtn[0] and servertime < $reportdurtn[1]
                 and EBD.userid = 2 and T.name != 'win log'
                 order by EBD.servertime,c.id,EBD.itemtype,EBD.status";

    $summaryDetails = find_many($sql, $db);
    return $summaryDetails;
}

function DASH_GetGetSummaryRprt($db, $machineArray, $itemId, $reportdurtn)
{

    $summaryDetails = [];
    $itemIds1 = [];
    $itemIds2 = [];

    $sql = "select eventitemid,name from " . $GLOBALS['PREFIX'] . "dashboard.EventItems";
    $itemIdRes = find_many($sql, $db);

    foreach ($itemIdRes as $value1) {
        $itemIds1[$value1['eventitemid']] = $value1['name'];
        $itemIds2[] = $value1['eventitemid'];
    }

    if (is_array($machineArray)) {
        foreach ($machineArray as $key => $value) {
            $censusIds .= $key . ' ';
        }
    } else {
        $censusIds = $machineArray;
    }

    $itemIds = implode(" ", $itemIds2);

    $params = '{
                "query": {
                  "bool": {
                    "must": [
                      { "match": { "censusid": "' . $censusIds . '"}}
                    ],
                    "filter": [
                      { "range": { "servertime": { "gte": "' . $reportdurtn[0] . '", "lte" : "' . $reportdurtn[1] . '" }}}
                    ]
                  }
                }
              }';

    $tempRes = EL_GetCurl("compliancesummary", $params);
    $tempSummaryDetails = EL_FormatCurldata($tempRes);

    foreach ($tempSummaryDetails as $key => $value) {
        $summaryDetails[$key]['name'] = $itemIds1[$value['itemid']];
        $summaryDetails[$key]['host'] = $machineArray[$value['censusid']];
        $summaryDetails[$key]['censusid'] = $value['censusid'];
        $summaryDetails[$key]['servertime'] = $value['servertime'];
        $summaryDetails[$key]['serverdate'] = $value['serverdate'];
        $summaryDetails[$key]['status'] = $value['status'];
    }
    return $summaryDetails;
}

function DASH_GetSiteCapacityCounts($key, $db, $site)
{

    $key = DASH_ValidateKey($key);
    $result = [];
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $ram_sql = "select count(C.id) as ramcount from " . $GLOBALS['PREFIX'] . "core.Census C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
                . "on C.site=D.siteName and C.host=D.machine WHERE C.site in (" . rtrim($sitelist, ",") . ") and (ramUsage <= 9 and ramUsage !='0' and ramUsage != '' ) limit 1";
            $ram_res = find_one($ram_sql, $db);

            $disk_sql = "select count(C.id) as diskcount from " . $GLOBALS['PREFIX'] . "core.Census C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
                . "on C.site=D.siteName and C.host=D.machine WHERE C.site in (" . rtrim($sitelist, ",") . ") and (hardDiskUsage <= 19 and hardDiskUsage !='0' and hardDiskUsage != '' ) limit 1";
            $disk_res = find_one($disk_sql, $db);
        } else {

            $ram_sql = "select count(C.id) as ramcount from " . $GLOBALS['PREFIX'] . "core.Census C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
                . "on C.site=D.siteName and C.host=D.machine WHERE C.site = '$site' and (ramUsage <= 9 and ramUsage !='0' and ramUsage != '' ) limit 1";
            $ram_res = find_one($ram_sql, $db);

            $disk_sql = "select count(C.id) as diskcount from " . $GLOBALS['PREFIX'] . "core.Census C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
                . "on C.site=D.siteName and C.host=D.machine WHERE C.site = '$site' and (hardDiskUsage <= 19 and hardDiskUsage !='0' and hardDiskUsage != '' ) limit 1";
            $disk_res = find_one($disk_sql, $db);
        }

        $result = UTIL_MakeCapacityCountArray($ram_res['ramcount'], $disk_res['diskcount']);
    } else {
        echo "Your key has been expired";
    }
    return $result;
}

function DASH_GetGrpCapacityCounts($key, $db, $machines)
{

    $key = DASH_ValidateKey($key);
    $result = [];

    if ($key) {
        $censusArray = safe_array_keys($machines);
        $census_list = implode(",", $censusArray);

        $ram_sql = "select count(C.id) as ramcount from " . $GLOBALS['PREFIX'] . "core.Census C "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
            . "on C.site=D.siteName and C.host=D.machine WHERE C.id in ($census_list) and (ramUsage <= 9 and ramUsage !='0' and ramUsage != '' ) limit 1";
        $ram_res = find_one($ram_sql, $db);

        $disk_sql = "select count(C.id) as diskcount from " . $GLOBALS['PREFIX'] . "core.Census C "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
            . "on C.site=D.siteName and C.host=D.machine WHERE C.id in ($census_list) and (hardDiskUsage <= 19 and hardDiskUsage !='0' and hardDiskUsage != '' ) limit 1";
        $disk_res = find_one($disk_sql, $db);

        $result = UTIL_MakeCapacityCountArray($ram_res['ramcount'], $disk_res['diskcount']);
    } else {
        echo "Your key has been expired";
    }
    return $result;
}

function DASH_GetMachCapacityCounts($key, $db, $machine)
{

    $key = DASH_ValidateKey($key);
    $result = [];
    $censusId = $_SESSION['rcensusId'];
    if ($key) {
        $ram_sql = "select count(C.id) as ramcount from " . $GLOBALS['PREFIX'] . "core.Census C "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
            . "on C.site=D.siteName and C.host=D.machine WHERE C.id =  $censusId and (ramUsage <= 9 and ramUsage !='0' and ramUsage != '' ) limit 1";
        $ram_res = find_one($ram_sql, $db);

        $disk_sql = "select count(C.id) as diskcount from " . $GLOBALS['PREFIX'] . "core.Census C "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
            . "on C.site=D.siteName and C.host=D.machine WHERE C.id =  $censusId and (hardDiskUsage <= 19 and hardDiskUsage !='0' and hardDiskUsage != '' ) limit 1";
        $disk_res = find_one($disk_sql, $db);
        $result = UTIL_MakeCapacityCountArray($ram_res['ramcount'], $disk_res['diskcount']);
    } else {
        echo "Your key has been expired";
    }
    return $result;
}

function DASH_GetProfileImagePath($key, $userid, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $stmt = $db->prepare("select imgPath from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
        $stmt->execute([$userid]);
        $getImgres = $stmt->fetch(PDO::FETCH_ASSOC);
        $image = $getImgres['imgPath'];
    } else {
        echo "Your key has been expired";
    }
    return $image;
}

function DASH_GetUploadImage($key, $image, $uid, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $stmt = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET imgPath=? where userid = ?");
        $proRes = $stmt->execute([$image, $uid]);
    } else {
        echo "Your key has been expired";
    }
    return $proRes;
}

function DASH_EditProfile($key, $fstname, $lstname, $phnum, $userid, $timeZone, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $updatesql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET firstName=?, lastName=?, user_phone_no=?, timezone=? where userid=?";
        $stmt = $db->prepare($updatesql);
        $updateres = $stmt->execute([$fstname, $lstname, $phnum, $timeZone, $userid]);
    } else {
        echo "Your key has been expired";
    }
    return $updateres;
}

function DASH_GetComplainceTrendSite($key, $db, $site, $itemtype, $trend, $itemId)
{
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $filterString = "";
    global $API_enable_comp;

    $complianceDetails = [];
    $dbusage = $_SESSION["user"]["usage"];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($dbusage == 1 && $API_enable_comp == 1) {
            if ($searchType == "Sites" && $searchValue == "All") {
                $sites = DASH_GetSites("", $db, $user);
                foreach ($site as $sites) {
                    $filterString .= '{"match": {"site": "' . $sites . '"}},';
                }
            } else if ($searchType == "Sites") {
                $filterString .= '{"match": {"site": "' . $searchValue . '"}}';
            }
            $sqlWh = rtrim($filterString, ",");
            $complianceDetails = DASH_GetComplianceTrendSite_EL($db, $itemtype, $trend, $itemId, $sqlWh, '');
        } else {
            if (is_array($site)) {
                $sitelist = '';
                foreach ($site as $value) {
                    $sitelist .= "'" . $value . "',";
                }
                $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
            } else {
                $scope = " C.site = '$site'";
            }
            $complianceDetails = DASH_GetComplianceTrend($db, $scope, $itemtype, $trend, $itemId);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceTrendGrp($key, $db, $machines, $itemtype, $trend, $itemId)
{

    $complianceDetails = [];
    $dbusage = $_SESSION["user"]["usage"];
    $key = DASH_ValidateKey($key);
    global $API_enable_comp;

    if ($key) {

        if ($dbusage == 1 && $API_enable_comp == 1) {
            $dataScope = UTIL_GetSiteScope($db, $_SESSION['searchValue'], $searchType);
            $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
            foreach ($machines as $key => $value) {
                $sitefilter .= '{"match": {"host": "' . $value . '"}},';
            }
            $sitefilter = rtrim($sitefilter, ',');
            $complianceDetails = DASH_GetComplianceTrendSite_EL($db, $itemtype, $trend, $itemId, $sitefilter, '');
        } else {
            $censusIds = safe_array_keys($machines);
            if (safe_count($censusIds) > 0) {
                $scope = " C.id in (" . implode(",", $censusIds) . ") ";
                $complianceDetails = DASH_GetComplianceTrend($db, $scope, $itemtype, $trend, $itemId);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceTrendMach($key, $db, $censusId, $itemtype, $trend, $itemId)
{

    $complianceDetails = [];
    $dbusage = $_SESSION["user"]["usage"];
    $key = DASH_ValidateKey($key);
    global $API_enable_comp;

    if ($key) {
        if ($dbusage == 1 && $API_enable_comp == 1) {

            $searchType = $_SESSION['searchType'];
            $searchValue = $_SESSION['searchValue'];
            $parentValue = $_SESSION['rparentName'];

            $siteName = DASH_getSiteName($db, $searchValue);
            $machineStr = '{"term": {"host": "' . $searchValue . '"}}';
            $siteStr = '{"match": {"site": "' . $siteName . '"}}';
            $complianceDetails = DASH_GetComplianceTrendSite_EL($db, $itemtype, $trend, $itemId, $siteStr, $machineStr);
        } else {
            $scope = " C.id = $censusId ";
            $complianceDetails = DASH_GetComplianceTrend($db, $scope, $itemtype, $trend, $itemId);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplianceTrend($db, $scope, $itemType, $trend, $itemId)
{

    $now = strtotime('Today');
    $lastHr = strtotime('-1 hour');
    $last15Days = strtotime('-14 days');
    $data = [];
    $date = date("M d");
    $onemonth = strtotime($date) - (15 * 24 * 60 * 60);
    $datelabel = [];
    $total = 15;
    $i = 1;
    while ($i <= $total) {
        $datelabel[] = date("d", $onemonth + ($i * 24 * 60 * 60));
        $i += 1;
    }

    if ($itemType == '1') {
        $itemType = "5,7,8,9,10";
    }

    if ($itemId != '') {
        $itemStr = " and itemid = $itemId ";
    } else {
        $itemStr = " ";
    }

    $cGraphQry2 = "select From_UNIXTIME(EBD.servertime,'%d') as day, EBD.itemtype, count(EBD.status) as count, EBD.status
                  from " . $GLOBALS['PREFIX'] . "dashboard.ComplianceSummary EBD  where  EBD.censusid in (select C.id from " . $GLOBALS['PREFIX'] . "core.Census C  where  $scope)
                   and EBD.servertime >= $last15Days and
                  EBD.itemType in ( $itemType ) $itemStr and EBD.userid = 2 group by EBD.status,EBD.itemtype,
                  From_UNIXTIME(EBD.servertime,'%d-%m-%y') order by EBD.status";
    $cGraphQry3 = "select From_UNIXTIME(EBD.servertime,'%d') as day, EBD.itemtype, count(EBD.status) as count, EBD.status
                  from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay EBD  where EBD.censusid in (select C.id from " . $GLOBALS['PREFIX'] . "core.Census C  where  $scope)
                  and EBD.clienttime >= $lastHr and
                  EBD.itemType in ( $itemType ) $itemStr and EBD.userid = 2 group by EBD.status,EBD.itemtype,
                  From_UNIXTIME(EBD.servertime,'%d-%m-%y') order by EBD.status";

    $compGraph2 = find_many($cGraphQry2, $db);
    $compGraph3 = find_many($cGraphQry3, $db);
    $okcount = 0;
    $warningcount = 0;
    $alertcount = 0;

    $comp_ok = [];
    $comp_warning = [];
    $comp_alert = [];

    $lastHr_ok = 0;
    $lastHr_warn = 0;
    $lastHr_alert = 0;

    foreach ($datelabel as $value) {
        $comp_ok[$value] = 0;
        $comp_warning[$value] = 0;
        $comp_alert[$value] = 0;
    }

    foreach ($compGraph2 as $key => $val) {
        $day = $val['day'];
        $status = $val['status'];
        $count = $val['count'];
        $item = $val['itemtype'];

        if ($status == 1) {
            $comp_ok[$day] += $count;
        } else if ($status == 2) {
            $comp_warning[$day] += $count;
        } else if ($status == 3) {
            $comp_alert[$day] += $count;
        }
    }

    foreach ($compGraph3 as $key => $val) {
        $day = $val['day'];
        $status = $val['status'];
        $count = $val['count'];
        $item = $val['itemtype'];

        if ($status == 1) {
            $lastHr_ok += $count;
        } else if ($status == 2) {
            $lastHr_warn += $count;
        } else if ($status == 3) {
            $lastHr_alert += $count;
        }
    }

    $i = 0;
    if ($trend) {
        foreach ($datelabel as $value) {
            $data[$i][0] = $comp_ok[$value];
            $data[$i][1] = $comp_alert[$value];
            $data[$i][2] = $comp_warning[$value];
            $i++;
        }
        $returnData['graphData'] = $data;
    } else {

        $data['last24'] = $comp_ok[$datelabel[14]];
        $data['last48'] = $comp_ok[$datelabel[13]];
        $data['last1'] = $lastHr_ok;
        $returnData['percData24'] = $data['last24'];
        $returnData['percData1'] = $data['last1'];
        $returnData['diffData'] = $data['last24'] - $data['last48'];
        $returnData['diffSign'] = $data['last24'] - $data['last48'] > 0 ? 'positive' : 'negative';
    }
    return $returnData;
}

function DASH_GetComplianceTrendSite_EL($db, $itemType, $trend, $itemId, $site, $machine = null)
{

    $now = strtotime('Today');
    $lastHr = strtotime('-1 hour');
    $last24hr = strtotime('-24 hour');
    $data = [];
    $date = date("M d");
    $onemonth = strtotime($date) - (15 * 24 * 60 * 60);
    $datelabel = [];
    $total = 15;
    $i = 1;
    while ($i <= $total) {
        $datelabel[] = date("d", $onemonth + ($i * 24 * 60 * 60));
        $i += 1;
    }

    if ($itemType == '1') {
        $itemType = "5,7,8,9,10";
    }

    if ($itemId != '') {
        $itemStr = " and itemid = $itemId ";
    } else {
        $itemStr = " ";
    }

    $sql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com'";
    $sqlRes = find_one($sql, $db);
    $userId = $sqlRes['userid'];

    $cGraphQry = getComplianceTrend($site, $userId, $itemType, $machine);
    $cGraphQry1 = getComplianceLastHourTrend($site, $userId, $lastHr, $itemType, $machine);
    $cGraphQry2 = getComplianceTwentyFourHourTrend($site, $userId, $last24hr, $lastHr, $itemType, $machine);

    $okcount = 0;
    $warningcount = 0;
    $alertcount = 0;

    $comp_ok = [];
    $comp_warning = [];
    $comp_alert = [];

    $lastHr_ok = 0;
    $lastHr_warn = 0;
    $lastHr_alert = 0;

    foreach ($datelabel as $value) {
        $comp_ok[$value] = 0;
        $comp_warning[$value] = 0;
        $comp_alert[$value] = 0;
    }

    foreach ($cGraphQry as $key => $val) {
        $day = $val['day'];
        $status = $val['status'];
        $count = $val['count'];
        $item = $val['itemtype'];

        if ($status == 1) {
            $comp_ok[$day] += $count;
        } else if ($status == 2) {
            $comp_warning[$day] += $count;
        } else if ($status == 3) {
            $comp_alert[$day] += $count;
        }
    }

    foreach ($cGraphQry1 as $key => $val) {
        $day = $val['day'];
        $status = $val['status'];
        $count = $val['count'];
        $item = $val['itemtype'];

        if ($status == 1) {
            $lastHr_ok += $count;
        } else if ($status == 2) {
            $lastHr_warn += $count;
        } else if ($status == 3) {
            $lastHr_alert += $count;
        }
    }

    foreach ($cGraphQry2 as $key => $val) {
        $day = $val['day'];
        $status = $val['status'];
        $count = $val['count'];
        $item = $val['itemtype'];

        if ($status == 1) {
            $last24_ok += $count;
        } else if ($status == 2) {
            $last24_warning += $count;
        } else if ($status == 3) {
            $last24_alert += $count;
        }
    }

    $i = 0;
    if ($trend) {
        foreach ($datelabel as $value) {
            $data[$i][0] = $comp_ok[$value];
            $data[$i][1] = $comp_alert[$value];
            $data[$i][2] = $comp_warning[$value];
            $i++;
        }
        $returnData['graphData'] = $data;
    } else {
        $data['last24'] = isset($last24_ok) ? $last24_ok : 0;
        $data['last48'] = isset($comp_ok[$datelabel[13]]) ? $comp_ok[$datelabel[13]] : 0;
        $data['last1'] = $lastHr_ok;
        $returnData['percData24'] = $data['last24'];
        $returnData['percData1'] = $data['last1'];
        $returnData['diffData'] = $data['last24'] - $data['last48'];
        $returnData['diffSign'] = $data['last24'] - $data['last48'] > 0 ? 'positive' : 'negative';
    }

    return $returnData;
}

function DASH_GetComplainceCalendarMonthGraphSite($key, $db, $site)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " c.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " c.site = '$site'";
        }
        $complianceDetails = DASH_GetComplianceCalendarMonthGraph($db, $scope);
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceCalendarMonthGraphGroup($key, $db, $machines)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $censusIds = safe_array_keys($machines);
        if (safe_count($censusIds) > 0) {
            $scope = " c.id in (" . implode(",", $censusIds) . ") ";
            $complianceDetails = DASH_GetComplianceCalendarMonthGraph($db, $scope);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceCalendarMonthGraphMachine($key, $db, $censusId)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " c.id = $censusId ";
        $complianceDetails = DASH_GetComplianceCalendarMonthGraph($db, $scope);
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplianceCalendarMonthGraph($db, $scope)
{
    $thismonth = time();
    $knowmonth = date("n", $thismonth);
    $knowyear = date("o", $thismonth);
    $days = cal_days_in_month(CAL_GREGORIAN, $knowmonth, $knowyear);
    $firstdayyear = mktime(0, 0, 10, 1, 1, $knowyear);
    $lastdayyear = mktime(23, 59, 0, 12, 31, $knowyear);

    $itemType = "5,7,8,9,10";

    $calendarGraphQry = "select From_UNIXTIME(EBD.servertime,'%e') as day,EBD.servertime as weekday, EBD.itemid, EBD.itemtype, count(EBD.status) as count, EBD.status
                  from " . $GLOBALS['PREFIX'] . "dashboard.ComplianceSummary EBD join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid  where
                  $scope and EBD.servertime BETWEEN $firstdayyear AND $lastdayyear and
                  EBD.itemType in ( $itemType ) and EBD.userid = 2 group by EBD.status,EBD.itemtype,
                  From_UNIXTIME(EBD.servertime,'%u') order by EBD.status";

    $calendarGraphRes = find_many($calendarGraphQry, $db);
    $insEmptyCalendar = $calendarGraphRes;

    $comparray = ["year" => $knowyear, "month" => $knowmonth, "days" => $days, "html" => "", "list" => ""];

    if (safe_count($calendarGraphRes) > 0) {
        foreach ($insEmptyCalendar as $row => $value) {
            $item1 = $value['itemtype'];
            $itemid1 = $value['itemid'];

            for ($k = 1; $k <= 12; $k++) {
                $knowfirstofmonth = mktime(0, 0, 0, $k, 1, $knowyear);
                $days = cal_days_in_month(CAL_GREGORIAN, $k, $knowyear);
                $knowlastofmonth = mktime(0, 0, 0, $k, $days, $knowyear);
                $monthnum = date("F", $knowfirstofmonth);
                $firstdayofmonth = date("W", $knowfirstofmonth);
                $lastdayofmonth = date("W", $knowlastofmonth);

                if (($firstdayofmonth == 52 || $firstdayofmonth == 53) && ($lastdayofmonth < 6)) {
                    for ($y = 1; $y <= $lastdayofmonth; $y++) {
                        $comparray["html"][$monthnum][$item1][$itemid1][ltrim($y, "0")] = "";
                    }
                } else {
                    for ($y = $firstdayofmonth; $y <= $lastdayofmonth; $y++) {
                        $comparray["html"][$monthnum][$item1][$itemid1][ltrim($y, "0")] = "";
                    }
                }
            }
        }

        foreach ($calendarGraphRes as $key => $val) {
            $month = date("F", $val['weekday']);
            $week = date("W", $val['weekday']);
            $status = $val['status'];
            $count = $val['count'];
            $item = $val['itemtype'];
            $itemid = $val['itemid'];

            $tableName = Dash_GetTableName($item);

            $compNameSql = "SELECT name FROM " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] WHERE $tableName[1] = $itemid LIMIT 1";
            $compNameRes = find_one($compNameSql, $db);

            if (in_array($compNameRes['name'], $comparray["list"]["Li" . $item])) {
            } else {
                $comparray["list"]["Li" . $item][] .= $compNameRes['name'];
            }

            $split_atag = explode("</a>", $comparray["html"][$month][$item][$itemid][ltrim($week, "0")]);
            $order = "first";

            $orderstatus = safe_count($split_atag);

            switch ($orderstatus) {
                case 1:
                    $order = "first";
                    break;
                case 2:
                    $order = "second";
                    break;
                case 3:
                    $order = "third";
                    break;
                default:
                    $order = "first";
                    break;
            }

            switch ($status) {
                case 1:
                    $comparray["html"][$month][$item][$itemid][ltrim($week, "0")] .= "<a title='Ok : " . $count . "' href='javascript:;' class='green " . $order . "' tabindex='0'></a>";
                    break;
                case 2:
                    $comparray["html"][$month][$item][$itemid][ltrim($week, "0")] .= "<a title='Warning : " . $count . "' href='javascript:;' class='orange " . $order . "' tabindex='0'></a>";
                    break;
                case 3:
                    $comparray["html"][$month][$item][$itemid][ltrim($week, "0")] .= "<a title='Alert : " . $count . "' href='javascript:;' class='red " . $order . "' tabindex='0'></a>";
                    break;
                default:
                    $comparray["html"][$month][$item][$itemid][ltrim($week, "0")] .= "";
                    break;
            }
        }
    } else {
        $dummyarray = array("5" => '', "7" => '', "8" => '', "9" => '', "10" => '');
        foreach ($dummyarray as $item1 => $value) {
            $itemid1 = 0;
            for ($k = 1; $k <= 12; $k++) {
                $knowfirstofmonth = mktime(0, 0, 0, $k, 1, $knowyear);
                $days = cal_days_in_month(CAL_GREGORIAN, $k, $knowyear);
                $knowlastofmonth = mktime(0, 0, 0, $k, $days, $knowyear);
                $monthnum = date("F", $knowfirstofmonth);
                $firstdayofmonth = date("W", $knowfirstofmonth);
                $lastdayofmonth = date("W", $knowlastofmonth);

                if (($firstdayofmonth == 52 || $firstdayofmonth == 53) && ($lastdayofmonth < 6)) {
                    for ($y = 1; $y <= $lastdayofmonth; $y++) {
                        $comparray["html"][$monthnum][$item1][$itemid1][ltrim($y, "0")] = "";
                    }
                } else {
                    for ($y = $firstdayofmonth; $y <= $lastdayofmonth; $y++) {
                        $comparray["html"][$monthnum][$item1][$itemid1][ltrim($y, "0")] = "";
                    }
                }
            }
        }
    }
    return $comparray;
}

function DASH_GetComplainceCalendarWeekGraphSite($key, $db, $site, $timestamp)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " c.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " c.site = '$site'";
        }
        $complianceDetails = DASH_GetComplianceCalendarWeekGraph($db, $scope, $timestamp);
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceCalendarWeekGraphGroup($key, $db, $machines, $timestamp)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $censusIds = safe_array_keys($machines);
        if (safe_count($censusIds) > 0) {
            $scope = " c.id in (" . implode(",", $censusIds) . ") ";
            $complianceDetails = DASH_GetComplianceCalendarWeekGraph($db, $scope, $timestamp);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceCalendarWeekGraphMachine($key, $db, $censusId, $timestamp)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " c.id = $censusId ";
        $complianceDetails = DASH_GetComplianceCalendarWeekGraph($db, $scope, $timestamp);
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplianceCalendarWeekGraph($db, $scope, $timestamp)
{
    $thismonth = $timestamp;
    $knowmonth = date("n", $thismonth);
    $knowmonthWord = date("F", $thismonth);
    $knowyear = date("o", $thismonth);
    $days = cal_days_in_month(CAL_GREGORIAN, $knowmonth, $knowyear);
    $firstday = mktime(0, 0, 10, $knowmonth, 1, $knowyear);
    $lastday = mktime(23, 59, 59, $knowmonth, $days, $knowyear);
    $week_day = date("N", mktime(0, 0, 0, $knowmonth, 1, $knowyear));

    $itemType = "5,7,8,9,10";

    $calendarGraphQry = "select From_UNIXTIME(EBD.servertime,'%e') as day,EBD.servertime as weekday, EBD.itemid, EBD.itemtype, count(EBD.status) as count, EBD.status
                  from " . $GLOBALS['PREFIX'] . "dashboard.ComplianceSummary EBD join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid  where
                  $scope and EBD.servertime BETWEEN $firstday AND $lastday and
                  EBD.itemType in ( $itemType ) and EBD.userid = 2 group by EBD.status,EBD.itemtype,
                  From_UNIXTIME(EBD.servertime,'%d-%m-%y') order by EBD.status";

    $calendarGraphRes = find_many($calendarGraphQry, $db);
    $insEmptyCalendar = $calendarGraphRes;

    $comparray = ["month" => $knowmonthWord, "monthnumber" => $knowmonth, "year" => $knowyear, "days" => $days, "weekday" => $week_day, "html" => "", "list" => ""];

    if (safe_count($calendarGraphRes) > 0) {
        foreach ($insEmptyCalendar as $row => $value) {
            $item1 = $value['itemtype'];
            $itemid1 = $value['itemid'];

            for ($k = 1; $k <= $days; $k++) {
                $knowdatetime = mktime(0, 0, 0, $knowmonth, $k, $knowyear);
                $weeknum = date("W", $knowdatetime);
                if ($k == 1 || $k == "1") {
                    for ($l = 0; $l < $week_day - 1; $l++) {
                        $comparray["html"][$weeknum][$item1][$itemid1][$l * 35] = "";
                    }
                }
                $comparray["html"][$weeknum][$item1][$itemid1][$k] = "";
            }
        }

        foreach ($calendarGraphRes as $key => $val) {
            $day = $val['day'];
            $week = date("W", $val['weekday']);
            $status = $val['status'];
            $count = $val['count'];
            $item = $val['itemtype'];
            $itemid = $val['itemid'];

            $tableName = Dash_GetTableName($item);

            $compNameSql = "SELECT name FROM " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] WHERE $tableName[1] = $itemid LIMIT 1";
            $compNameRes = find_one($compNameSql, $db);

            if (in_array($compNameRes['name'], $comparray["list"]["Li" . $item])) {
            } else {
                $comparray["list"]["Li" . $item][] .= $compNameRes['name'];
            }

            if ($day == 1 || $day == "1") {
                for ($i = 0; $i < $week_day - 1; $i++) {
                    $comparray["html"][$week][$item][$itemid][$i * 35] = "";
                }
            }

            $split_atag = explode("</a>", $comparray["html"][$week][$item][$itemid][$day]);
            $order = "first";

            $orderstatus = safe_count($split_atag);

            switch ($orderstatus) {
                case 1:
                    $order = "first";
                    break;
                case 2:
                    $order = "second";
                    break;
                case 3:
                    $order = "third";
                    break;
                default:
                    $order = "first";
                    break;
            }

            switch ($status) {
                case 1:
                    $comparray["html"][$week][$item][$itemid][$day] .= "<a title='Ok : " . $count . "' href='javascript:;' class='green " . $order . "' tabindex='0'></a>";
                    break;
                case 2:
                    $comparray["html"][$week][$item][$itemid][$day] .= "<a title='Warning : " . $count . "' href='javascript:;' class='orange " . $order . "' tabindex='0'></a>";
                    break;
                case 3:
                    $comparray["html"][$week][$item][$itemid][$day] .= "<a title='Alert : " . $count . "' href='javascript:;' class='red " . $order . "' tabindex='0'></a>";
                    break;
                default:
                    $comparray["html"][$week][$item][$itemid][$day] .= "";
                    break;
            }
        }
    } else {
        $dummyarray = array("5" => '', "7" => '', "8" => '', "9" => '', "10" => '');
        foreach ($dummyarray as $item1 => $value) {
            $itemid1 = 0;
            for ($k = 1; $k <= $days; $k++) {
                $knowdatetime = mktime(0, 0, 0, $knowmonth, $k, $knowyear);
                $weeknum = date("W", $knowdatetime);
                if ($k == 1 || $k == "1") {
                    for ($l = 0; $l < $week_day - 1; $l++) {
                        $comparray["html"][$weeknum][$item1][$itemid1][$l * 35] = "";
                    }
                }
                $comparray["html"][$weeknum][$item1][$itemid1][$k] = "";
            }
        }
    }

    return $comparray;
}

function DASH_GetComplainceCalendarDailyGraphSite($key, $db, $site, $timestamp)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " c.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " c.site = '$site'";
        }
        $complianceDetails = DASH_GetComplianceCalendarDailyGraph($db, $scope, $timestamp);
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceCalendarDailyGraphGroup($key, $db, $machines, $timestamp)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $censusIds = safe_array_keys($machines);
        if (safe_count($censusIds) > 0) {
            $scope = " c.id in (" . implode(",", $censusIds) . ") ";
            $complianceDetails = DASH_GetComplianceCalendarDailyGraph($db, $scope, $timestamp);
        }
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplainceCalendarDailyGraphMachine($key, $db, $censusId, $timestamp)
{

    $complianceDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " c.id = $censusId ";
        $complianceDetails = DASH_GetComplianceCalendarDailyGraph($db, $scope, $timestamp);
    } else {
        echo "Your key has been expired";
    }
    return $complianceDetails;
}

function DASH_GetComplianceCalendarDailyGraph($db, $scope, $timestamp)
{
    $thismonth = $timestamp;
    $thismonth7 = $thismonth + 518400;
    $seldate = date("j", $thismonth);
    $knowmonth = date("n", $thismonth);
    $knowmonthWord = date("F", $thismonth);
    $knowyear = date("o", $thismonth);
    $days = cal_days_in_month(CAL_GREGORIAN, $knowmonth, $knowyear);
    $week_day = date("N", mktime(0, 0, 0, $knowmonth, 1, $knowyear));

    $itemType = "5,7,8,9,10";

    $calendarGraphQry = "select From_UNIXTIME(EBD.servertime,'%e') as day,EBD.servertime as hour, EBD.itemid, EBD.itemtype, count(EBD.status) as count, EBD.status
                  from " . $GLOBALS['PREFIX'] . "dashboard.ComplianceSummary EBD join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = EBD.censusid  where
                  $scope and EBD.servertime BETWEEN $thismonth AND $thismonth7 and
                  EBD.itemType in ( $itemType ) and EBD.userid = 2 group by EBD.status,EBD.itemtype,
                  From_UNIXTIME(EBD.servertime,'%d-%m-%y') order by EBD.status";

    $calendarGraphRes = find_many($calendarGraphQry, $db);
    $insEmptyCalendar = $calendarGraphRes;

    $comparray = ["firstday" => $timestamp, "month" => $knowmonthWord, "year" => $knowyear, "monthnum" => $knowmonth, "days" => $days, "weekday" => $week_day, "html" => "", "list" => ""];

    if (safe_count($calendarGraphRes) > 0) {
        foreach ($insEmptyCalendar as $row => $value) {
            $item1 = $value['itemtype'];
            $itemid1 = $value['itemid'];

            for ($k = $seldate; $k <= $seldate + 6; $k++) {
                $knowdatetime = mktime(0, 0, 0, $knowmonth, $k, $knowyear);
                $hournum = date("H", $knowdatetime);
                for ($h = 0; $h <= 22; $h++) {
                    if ($h % 2 == 0) {
                        $hour = $h;
                    } else {
                        $hour = $h + 1;
                    }
                    $comparray["html"][$k][$item1][$itemid1][$hour + 2] = "";
                }
            }
        }

        foreach ($calendarGraphRes as $key => $val) {
            $day = $val['day'];
            $hournum = date("H", $val['hour']);
            $status = $val['status'];
            $count = $val['count'];
            $item = $val['itemtype'];
            $itemid = $val['itemid'];

            $tableName = Dash_GetTableName($item);

            $compNameSql = "SELECT name FROM " . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] WHERE $tableName[1] = $itemid LIMIT 1";
            $compNameRes = find_one($compNameSql, $db);

            if (in_array($compNameRes['name'], $comparray["list"]["Li" . $item])) {
            } else {
                $comparray["list"]["Li" . $item][] .= $compNameRes['name'];
            }

            if ($hournum % 2 == 0) {
                $hour = ltrim($hournum, '0');
            } else {
                $hour = ltrim($hournum + 1, '0');
            }

            $split_atag = explode("</a>", $comparray["html"][$day][$item][$itemid][$hour]);
            $order = "first";
            $orderstatus = safe_count($split_atag);

            switch ($orderstatus) {
                case 1:
                    $order = "first";
                    break;
                case 2:
                    $order = "second";
                    break;
                case 3:
                    $order = "third";
                    break;
                default:
                    $order = "first";
                    break;
            }

            switch ($status) {
                case 1:
                    $comparray["html"][$day][$item][$itemid][$hour] .= "<a title='Ok : " . $count . "' href='javascript:;' class='green " . $order . "' tabindex='0'></a>";
                    break;
                case 2:
                    $comparray["html"][$day][$item][$itemid][$hour] .= "<a title='Warning : " . $count . "' href='javascript:;' class='orange " . $order . "' tabindex='0'></a>";
                    break;
                case 3:
                    $comparray["html"][$day][$item][$itemid][$hour] .= "<a title='Alert : " . $count . "' href='javascript:;' class='red " . $order . "' tabindex='0'></a>";
                    break;
                default:
                    $comparray["html"][$day][$item][$itemid][$hour] .= "";
                    break;
            }
        }
    } else {
        $dummyarray = array("5" => '', "7" => '', "8" => '', "9" => '', "10" => '');
        foreach ($dummyarray as $item1 => $value) {
            $itemid1 = 0;

            for ($k = $seldate; $k <= $seldate + 6; $k++) {
                $knowdatetime = mktime(0, 0, 0, $knowmonth, $k, $knowyear);
                $hournum = date("H", $knowdatetime);
                for ($h = 0; $h <= 22; $h++) {
                    if ($h % 2 == 0) {
                        $hour = $h;
                    } else {
                        $hour = $h + 1;
                    }
                    $comparray["html"][$k][$item1][$itemid1][$hour + 2] = "";
                }
            }
        }
    }

    return $comparray;
}

function DASH_GetNotifPercSite($key, $db, $site)
{

    $from = date('m/d/Y', strtotime('-14 days'));
    $to = date('m/d/Y');
    $key = DASH_ValidateKey($key);
    $priorities = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);

    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = rtrim($sitelist, ",");
        } else {
            $scope = "'$site'";
        }
        $notifSql = "select priority, sum(tcount) as tcount from "
            . "" . $GLOBALS['PREFIX'] . "event.SummaryGraphSite where site in ($scope) and "
            . "graphDate >= '" . $from . "' and graphDate <= '" . $to . "' "
            . "and username='admin' group by priority";
        $notifRes = find_many($notifSql, $db);

        if (empty($notifRes)) {
            $priorities['p1_perc'] = 0;
            $priorities['p2_perc'] = 0;
            $priorities['p3_perc'] = 0;
            $priorities['p4_perc'] = 0;
            $priorities['p5_perc'] = 0;
            $totalcount = 0;
        } else {

            foreach ($notifRes as $key => $value) {
                $priorities[$value['priority']] = $value['tcount'];
            }
            $totalcount = array_sum($priorities);
            $priorities['p1_perc'] = round(($priorities[1] / $totalcount) * 100, 2);
            $priorities['p2_perc'] = round(($priorities[2] / $totalcount) * 100, 2);
            $priorities['p3_perc'] = round(($priorities[3] / $totalcount) * 100, 2);
            $priorities['p4_perc'] = round(($priorities[4] / $totalcount) * 100, 2);
            $priorities['p5_perc'] = round(($priorities[5] / $totalcount) * 100, 2);
        }
        $priorities['total'] = $totalcount;
    } else {
        echo "Your key has been expired";
    }
    return $priorities;
}

function DASH_GetNotifPercGrp($key, $db, $machines)
{

    $priorities = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        foreach ($machines as $value) {
            $host_list .= "'" . $value . "',";
        }
        $scope = rtrim($host_list, ",");
        $priorities = DASH_GetNotifPerc($db, $scope);
    } else {
        echo "Your key has been expired";
    }
    return $priorities;
}

function DASH_GetNotifPercMach($key, $db, $hostName)
{

    $priorities = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = "'$hostName'";
        $priorities = DASH_GetNotifPerc($db, $scope);
    } else {
        echo "Your key has been expired";
    }
    return $priorities;
}

function DASH_GetNotifPerc($db, $scope)
{

    $from = date('Y-m-d', strtotime('-14 days'));
    $to = date('Y-m-d');

    $p1 = $p2 = $p3 = $p4 = $p5 = 0;

    $notifSql = "select servertime, eventIdx, nocStatus as status, nocName, nid, site, machine, T.priority "
        . "from  " . $GLOBALS['PREFIX'] . "event.tempNotification T join  " . $GLOBALS['PREFIX'] . "event.Notifications N ON T.nid = N.id "
        . "where servertime >= " . strtotime($from) . " and servertime <= " . strtotime($to . ' 23:59:59') . " "
        . "and machine IN($scope) and T.username='admin' "
        . "and N.enabled = 1 group by machine,nocName,FROM_UNIXTIME(servertime,'%d')";

    $notifRes = find_many($notifSql, $db);
    foreach ($notifRes as $key => $value) {
        if ($value['priority'] == '1' || $value['priority'] == 1) {
            $p1++;
        } else if ($value['priority'] == '2' || $value['priority'] == 2) {
            $p2++;
        } else if ($value['priority'] == '3' || $value['priority'] == 3) {
            $p3++;
        } else if ($value['priority'] == '4' || $value['priority'] == 4) {
            $p4++;
        } else if ($value['priority'] == '5' || $value['priority'] == 5) {
            $p5++;
        }
    }
    $priorities[1] = $p1;
    $priorities[2] = $p2;
    $priorities[3] = $p3;
    $priorities[4] = $p4;
    $priorities[5] = $p5;
    $totalcount = $p1 + $p2 + $p3 + $p4 + $p5;
    $priorities['p1_perc'] = round(($priorities[1] / $totalcount) * 100, 2);
    $priorities['p2_perc'] = round(($priorities[2] / $totalcount) * 100, 2);
    $priorities['p3_perc'] = round(($priorities[3] / $totalcount) * 100, 2);
    $priorities['p4_perc'] = round(($priorities[4] / $totalcount) * 100, 2);
    $priorities['p5_perc'] = round(($priorities[5] / $totalcount) * 100, 2);
    $priorities['total'] = $totalcount;

    return $priorities;
}

function DASH_GetCostPerCall($key, $db)
{

    $returnData = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select name,value from " . $GLOBALS['PREFIX'] . "core.Options where name in ('cost_saved_per_call','cost_saved_proactive')";
        $res = find_many($sql, $db);
        foreach ($res as $key => $val) {
            $returnData[$val['name']] = $val['value'];
        }
    } else {
        echo "Your key has been expired";
    }

    return $returnData;
}

function DASH_GetSiteOsDetails($key, $db, $gridType, $site, $searchVal, $orderValues, $limit)
{

    $osDetails = [];
    $key = DASH_ValidateKey($key);

    $dataId = DASH_getDataId($db, 'operating system');
    if ($gridType == 'Windows' || $gridType == 'Android' || $gridType == 'Linux' || $gridType == 'MAC' || $gridType == 'iOS') {
        $gridTypes = "and value like '%$gridType%'";
    } else if ($gridType == 'empty') {
        $gridTypes = "and (C.slatest = 0 or t.value is NULL)";
    } else if ($gridType == 'chart') {
        $gridTypes = '';
    }

    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }

            $sql = "select C.machineid as id,value as value1,C.slatest last,C.searliest as born,C.host,C.cust as site from " . $GLOBALS['PREFIX'] . "asset.Machine C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId "
                . "where C.cust in (" . rtrim($sitelist, ",") . ") $gridTypes group by host $orderValues $limit";
        } else {

            $sql = "select C.machineid as id,value as value1,C.slatest last,C.searliest as born,C.host,C.cust as site from " . $GLOBALS['PREFIX'] . "asset.Machine C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId "
                . "where C.cust = '$site' $gridTypes group by host $orderValues $limit";
        }
        $sqlResult = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    if ($gridType == 'chart' || $gridType == 'empty') {
        $censusmach = DASH_GetCensusMachines($key, $db, $site);
        if (safe_count($censusmach) > 0) {
            $i = 0;
            foreach ($censusmach as $key => $vals) {
                $finArr[$i]['id'] = $vals['id'];
                $finArr[$i]['value1'] = '-';
                $finArr[$i]['last'] = '-';
                $finArr[$i]['born'] = '-';
                $finArr[$i]['host'] = $vals['host'];
                $finArr[$i]['site'] = $site;
                $i++;
            }
            $result = array_merge($finArr, $sqlResult);
        } else {
            $result = $sqlResult;
        }
    } else {
        $result = $sqlResult;
    }
    return $result;
}

function DASH_GetGrpOsDetail($key, $db, $gridType, $machines, $searchVal, $orderValues, $limit)
{
    $osDetails = [];
    $search = '';
    $key = DASH_ValidateKey($key);
    if ($gridType == 'Windows' || $gridType == 'Android' || $gridType == 'Linux' || $gridType == 'MAC' || $gridType == 'iOS') {
        $gridTypes = "and value like '%$gridType%'";
    } else if ($gridType == 'empty') {
        $gridTypes = "and value IS NULL";
    } else if ($gridType == 'chart') {
        $gridTypes = '';
    }

    if ($searchVal != '') {
        $search = "and (host like '%$searchVal%' || value1 like '%$searchVal%')";
    }
    $dataId = DASH_getDataId($db, 'operating system');
    if ($key) {
        $sitelist = '';
        foreach ($machines as $keys => $value) {
            $siteid .= "'" . $keys . "',";
        }
        $sitelist = "'" . implode("'" . ',' . "'", $machines) . "'";

        $sql = "select C.machineid as id,value as value1,C.slatest last,C.searliest as born,C.host,C.cust as site " .
            "from " . $GLOBALS['PREFIX'] . "asset.Machine C left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId " .
            "where C.host in ($sitelist) $gridTypes group by host";
        $sqlResult = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $sqlResult;
}

function DASH_GetMachineOsDetail($key, $db, $gridType, $censusId, $searchVal, $orderValues, $limit)
{

    $osDetails = [];
    $key = DASH_ValidateKey($key);

    if ($gridType == 'Windows' || $gridType == 'Android' || $gridType == 'Linux' || $gridType == 'MAC' || $gridType == 'iOS') {
        $gridTypes = "and value like '%$gridType%'";
    } else if ($gridType == 'empty') {
        $gridTypes = "and value IS NULL";
    } else if ($gridType == 'chart') {
        $gridTypes = '';
    }
    $dataId = DASH_getDataId($db, 'operating system');
    if ($key) {

        $sql = "select C.machineid as id,value as value1,C.slatest last,C.searliest as born,C.host,C.cust as site " .
            "from " . $GLOBALS['PREFIX'] . "asset.Machine C left join " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest t on t.machineid = C.machineid and t.dataid = $dataId" .
            " where C.host = '$searchVal' $gridTypes group by host";
        $sqlResult = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    if (safe_count($sqlResult) == 0) {
        $sqlResult[0]['id'] = 1;
        $sqlResult[0]['value1'] = '-';
        $sqlResult[0]['last'] = '-';
        $sqlResult[0]['born'] = '-';
        $sqlResult[0]['host'] = $searchVal;
        $sqlResult[0]['cust'] = $_SESSION['rparentName'];
    }

    return $sqlResult;
}

function DASH_GetSitesEventList_OLD($key, $db, $site, $time1, $time2)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        if (is_array($site)) {
            $res = safe_array_keys($site);
            foreach ($res as $row) {
                $siteList .= "'" . $site[$row] . "',";
            }
            $lableDisply = rtrim($siteList, ',');

            $sql = "select idx, id, scrip, entered, customer, machine, description, executable,  replace(replace( replace(text1, '', ' '), '', ' '), '', ' ') "
                . "as text1, text2, text3, text4, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between $time2 AND $time1 and customer in ($lableDisply)";
        } else {
            $sql = "select idx, id, scrip, entered, customer, machine, description, executable,  replace(replace( replace(text1, '', ' '), '', ' '), '', ' ') "
                . "as text1, text2, text3, text4, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between $time2 AND $time1 and customer = '$site'";
        }

        $eventsitesdata = find_many($sql, $db);
    } else {

        echo "Your key has been expired";
    }

    return $eventsitesdata;
}

function DASH_GetSitesEventList($key, $db, $site, $time1, $time2)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        if (is_array($site)) {
            $res = safe_array_keys($site);
            foreach ($res as $row) {
                $siteList .= "'" . $site[$row] . "',";
            }
            $lableDisply = rtrim($siteList, ',');
        } else {
            $lableDisply = "$site";
        }

        $searchType = $_SESSION['searchType'];
        $sitename = $_SESSION['searchValue'];
        $machinename = $_SESSION['searchValue'];

        if ($_SESSION['searchValue'] == 'All') {
            $key = "";
            $dataScope = UTIL_GetSiteScope($db, $_SESSION['searchValue'], $searchType);
            $machines = DASH_GetMachinesSites($key, $db, $dataScope);
            foreach ($machines as $key => $value) {
                $sitefilter .= '{"match": {"machine": "' . $value . '"}},';
            }
            $sitefilter = rtrim($sitefilter, ',');
        } else {
            if ($searchType == 'Sites') {
                $sitefilter = '{"match": {"customer": "' . $sitename . '"}}';
            } else if ($searchType == 'ServiceTag') {
                $sitefilter = '{"match": {"machine": "' . $machinename . '"}}';
            }
        }

        $queryString = '';
        foreach ($_SESSION['AdvEventFilter'] as $key => $value) {
            if ($value != '') {
                $queryString .= '{
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [{"match": {"' . $key . '": "' . $value . '"}}]
                            }
                          },';
            }
        }

        $params = '{
                    "query": {
                      "bool": {
                        "must": [
                          {
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [' . $sitefilter . ']
                            }
                          },' . $queryString . '
                          {
                            "range": {
                              "entered": {
                                "gte": ' . $time2 . ',
                                "lte": ' . $time1 . ',
                                "format": "epoch_millis"
                              }
                            }
                          }
                        ]
                      }
                    }
          }';

        $tempRes = EL_GetCurl("event", $params);
        $eventsitesdata = EL_FormatCurldata($tempRes);
    } else {

        echo "Your key has been expired";
    }

    return $eventsitesdata;
}

function DASH_GetMachineEventList_EL($key, $time1, $time2, $machine_name, $site_name, $db)
{
    global $API_enable_Event;
    $key = DASH_ValidateKey($key);

    if ($key) {
        $searchValue = $_SESSION['searchValue'];
        $searchType = $_SESSION['searchType'];

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $min_cond = 1;
        if ($_SESSION['passlevel'] == 'Groups') {
            $sitefilter .= '{ "term": { "machine.keyword": "' . $searchValue . '" }}';
        } else {
            $censusId = $_SESSION['rcensusId'];
            $site = DASH_GetMachSite($key, $db, $censusId);
            $sitefilter .= '{ "term": { "machine.keyword": "' . $dataScope . '" }},
                            { "term": { "customer.keyword": "' . $_SESSION['rparentName'] . '" }}';
        }

        $queryString = '';
        foreach ($_SESSION['AdvEventFilter'] as $key => $value) {
            if ($value != '') {
                $queryString .= '{
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [{"match": {"' . $key . '": "' . $value . '"}}]
                            }
                          },';
            }
        }

        $paramsTotal = '{
                    "query": {
                      "bool": {
                        "must": [
                          ' . $sitefilter . '
                          ,{
                            "range": {
                              "entered": {
                                "gte": ' . $time2 . ',
                                "lte": ' . $time1 . ',
                                "format": "epoch_millis"
                              }
                            }
                          }
                        ]
                      }
                    }
          }';

        if ($API_enable_Event == 1) {
            $fromDate = date('Y-m-d', $time2);
            $toDate = date('Y-m-d', $time1);
            $indexName = createEventIndex($fromDate, $toDate);
        } else {
            $indexName = 'event';
        }

        $tempResTotal = EL_GetCurlRecordsCount($indexName, $paramsTotal);
        $totalArray = safe_json_decode($tempResTotal, true);

        $totalCount = isset($totalArray['count']) ? $totalArray['count'] : 0;

        getObjectExcelnew();

        if ($totalCount > 0) {
            $params = '{
                "from" : 0, "size" : 2000,
                    "query": {
                      "bool": {
                        "must": [
                          ' . $sitefilter . '
                          ,{
                            "range": {
                              "entered": {
                                "gte": ' . $time2 . ',
                                "lte": ' . $time1 . ',
                                "format": "epoch_millis"
                              }
                            }
                          }
                        ]
                      }
                    }
                }';
            $scrollresult = getAllAssets_scroll($params, $indexName);
            $response = safe_json_decode($scrollresult, true);
            while (isset($response['hits']['hits']) && safe_count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $key => $val) {
                    $result = $val['_source'];
                    $objPHPExcel = loopexportdataEvent($result);
                }

                $scroll_id = $response['_scroll_id'];
                $scrollresult = getAllAssets_scrollid($scroll_id);
                $response = safe_json_decode($scrollresult, true);

                if ($scroll_id != $response['_scroll_id']) {
                    deleteScrollId($scroll_id);
                }
            }
        }
    } else {

        echo "Your key has been expired";
    }
}

function DASH_GetSitesEventList_EL($key, $db, $site, $time1, $time2)
{
    global $API_enable_Event;
    $key = DASH_ValidateKey($key);

    if ($key) {
        if (is_array($site)) {
            $res = safe_array_keys($site);
            foreach ($res as $row) {
                $siteList .= "'" . $site[$row] . "',";
            }
            $lableDisply = rtrim($siteList, ',');
        } else {
            $lableDisply = "$site";
        }

        $searchType = $_SESSION['searchType'];
        $sitename = $_SESSION['searchValue'];
        $machinename = $_SESSION['searchValue'];

        if ($_SESSION['searchValue'] == 'All') {
            $key = "";
            $dataScope = UTIL_GetSiteScope($db, $_SESSION['searchValue'], $searchType);
            foreach ($dataScope as $row) {
                $sitefilter .= '{"match": {"customer.keyword": "' . $row . '"}},';
            }
            $sitefilter = rtrim($sitefilter, ',');
        } else {
            if ($searchType == 'Sites') {
                $sitefilter = '{"match": {"customer.keyword": "' . $sitename . '"}}';
            } else if ($searchType == 'ServiceTag') {
                $sitefilter = '{"match": {"machine.keyword": "' . $machinename . '"}}';
            }
        }

        $queryString = '';
        foreach ($_SESSION['AdvEventFilter'] as $key => $value) {
            if ($value != '') {
                $queryString .= '{
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [{"match": {"' . $key . '": "' . $value . '"}}]
                            }
                          },';
            }
        }

        $paramsTotal = '{
                    "query": {
                      "bool": {
                        "must": [
                          {
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [' . $sitefilter . ']
                            }
                          },' . $queryString . '
                          {
                            "range": {
                              "entered": {
                                "gte": ' . $time2 . ',
                                "lte": ' . $time1 . ',
                                "format": "epoch_millis"
                              }
                            }
                          }
                        ]
                      }
                    }
          }';

        if ($API_enable_Event == 1) {
            $fromDate = date('Y-m-d', $time2);
            $toDate = date('Y-m-d', $time1);
            $indexName = createEventIndex($fromDate, $toDate);
        } else {
            $indexName = 'event';
        }

        $tempResTotal = EL_GetCurlRecordsCount($indexName, $paramsTotal);
        $totalArray = safe_json_decode($tempResTotal, true);

        $totalCount = isset($totalArray['count']) ? $totalArray['count'] : 0;

        getObjectExcelnew();
        if ($totalCount > 0) {
            $params = '{
                "from" : 0, "size" : 2000,
                      "query": {
                        "bool": {
                          "must": [
                            {
                              "bool": {
                                "minimum_should_match": 1,
                                "should": [' . $sitefilter . ']
                              }
                            },' . $queryString . '
                            {
                              "range": {
                                "entered": {
                                  "gte": ' . $time2 . ',
                                  "lte": ' . $time1 . ',
                                  "format": "epoch_millis"
                                }
                              }
                            }
                          ]
                        }
                      }
                  }';
            $scrollresult = getAllAssets_scroll($params, $indexName);
            $response = safe_json_decode($scrollresult, true);
            while (isset($response['hits']['hits']) && safe_count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $key => $val) {
                    $result = $val['_source'];
                    $objPHPExcel = loopexportdataEvent($result);
                }

                $scroll_id = $response['_scroll_id'];
                $scrollresult = getAllAssets_scrollid($scroll_id);
                $response = safe_json_decode($scrollresult, true);

                if ($scroll_id != $response['_scroll_id']) {
                    deleteScrollId($scroll_id);
                }
            }
        }
    } else {

        echo "Your key has been expired";
    }
}

function loopexportdata($indexName, $params, $objPHPExcel, $index)
{

    $tempRes = EL_GetCurlWithLimit($indexName, $params);
    $resultData = EL_FormatCurldata_new($tempRes);
    $totalCount = $resultData['total'];
    $gridlist = $resultData['result'];

    if ($totalCount > 0) {
        foreach ($gridlist as $key => $value) {

            $description = safe_addslashes(utf8_encode($value['description']));
            $text1 = strip_tags(wordwrap(safe_addslashes(utf8_encode($value['text1'])), 50, PHP_EOL));
            $executable = safe_addslashes(utf8_encode($value['executable']));
            $clienttime = date("m/d/Y H:i:s", $value['entered']);
            $servertime = date("m/d/Y H:i:s", $value['servertime']);
            $machines = $value['machine'];
            $scrip = $value['scrip'];

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $machines);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $description);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $scrip);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $clienttime);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $servertime);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, strip_tags($value['text1']));
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, strip_tags($value['text2']));
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, strip_tags($value['text3']));
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, strip_tags($value['text4']));
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    return $objPHPExcel;
}

function createEventIndex($fromDate, $toDate)
{

    $indexName = '';
    $date = $fromDate;
    $end_date = $toDate;
    while (strtotime($date) <= strtotime($end_date)) {

        $indexName .= 'events_' . $date . ',';

        $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
    }
    return rtrim($indexName, ',');
}

function DASH_GetMachineEventList_OLD($key, $time1, $time2, $machine_name, $site_name, $db)
{

    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select idx, id, scrip, entered, customer, machine, description, executable,  replace(replace( replace(text1, '', ' '), '', ' '), '', ' ') "
            . "as text1, text2, text3, text4, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between $time2 AND $time1 AND machine in ('$machine_name') "
            . "and customer = '$site_name'";

        $eventdata = find_many($sql, $db);
    } else {

        echo "Your key has been expired";
    }

    return $eventdata;
}

function DASH_GetMachineEventList($key, $time1, $time2, $machine_name, $site_name, $db)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        $params = '{
                "from" : 0, "size" : 200000,
                "query": {
                  "bool": {
                    "must": [
                      { "match": { "machine.keyword":"' . $machine_name . '"}},
                      { "match": { "customer.keyword": "' . $site_name . '" }}
                    ],
                    "filter": [
                      { "range": { "servertime": { "gte": "' . $time2 . '", "lte" : "' . $time1 . '" }}}
                    ]
                      }
                        }
              }';
        $tempRes = EL_GetCurl("event", $params);
        $eventmachinesdata = EL_FormatCurldata($tempRes);
    } else {

        echo "Your key has been expired";
    }

    return $eventmachinesdata;
}

function createComplianceIndex($fromDate, $toDate)
{

    $indexName = '';
    $date = $fromDate;
    $end_date = $toDate;
    while (strtotime($date) <= strtotime($end_date)) {

        $indexName .= 'compliancesummary_' . trim($date) . ',';

        $date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
    }
    return rtrim($indexName, ',');
}

function DASH_GetGroupEventList($key, $time1, $time2, $machines, $db)
{

    $key = DASH_ValidateKey($key);

    $res = safe_array_keys($machines);
    foreach ($res as $row) {
        $siteList .= "'" . $machines[$row] . "',";
    }
    $lableDisply = rtrim($siteList, ',');
    if ($key) {
        $sql = "select idx, id, scrip, entered, customer, machine, description, executable,  replace(replace( replace(text1, '', ' '), '', ' '), '', ' ') "
            . "as text1, text2, text3, text4, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between $time2 AND $time1 AND machine in ($lableDisply) ";

        $eventdata = find_many($sql, $db);
    } else {

        echo "Your key has been expired";
    }

    return $eventdata;
}

function DASH_GetEventDetailList($key, $eid, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        mysqli_query("set names 'utf8'");
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.Events where idx = $eid";
        $eventlist = find_one($sql, $db);
    } else {

        echo "Your key has been expired";
    }

    return $eventlist;
}

function DASH_EventQuerySitesFilter($key, $time1, $time2, $sites, $where, $db)
{

    if (is_array($sites)) {

        foreach ($sites as $site) {
            $siteList .= "'" . $site . "',";
        }
        $lableDisply = rtrim($siteList, ',');

        $sql = "select idx, id, scrip, entered, customer, machine, description, executable,text1, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between "
            . "$time2 AND $time1 AND customer in ($lableDisply) $where";
    } else {

        $sql = "select idx, id, scrip, entered, customer, machine, description, executable,text1, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between "
            . "$time2 AND $time1 AND  customer = '$sites' $where";
    }

    $sqlresult = find_many($sql, $db);

    return $sqlresult;
}

function DASH_EventQueryMachineFilter($key, $time1, $time2, $machines, $where, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select idx, id, scrip, entered, customer, machine, description, executable,text1, servertime from  " . $GLOBALS['PREFIX'] . "event.Events  where servertime between "
            . "$time2 AND $time1 AND machine = $machines $where";

        $sqlresult = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlresult;
}

function DASH_EventQueryGroupFilter($key, $time1, $time2, $groupmachine, $where, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $res = safe_array_keys($groupmachine);
        foreach ($res as $row) {
            $siteList .= "'" . $groupmachine[$row] . "',";
        }
        $machinelist = rtrim($siteList, ',');

        $sql = "select idx, id, scrip, entered, customer, machine, description, executable,text1, servertime from  " . $GLOBALS['PREFIX'] . "event.Events where servertime between "
            . "$time2 AND $time1 AND machine in ($machinelist) $where";

        $sqlresult = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlresult;
}

function DASH_GetEventQuery($key, $eq_id, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select * from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id =" . $eq_id . " limit 1";
        $res = find_one($sql, $db);
        $srchString = $res['searchstring'];
        $where .= " and $srchString";
    } else {
        echo "Your key has been expired";
    }

    return $where;
}

function DASH_GetAssetDetails($key, $db, $datanames)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $assetTable = $assetArray['asset'];
        $machineTable = $assetArray['machine'];
        $dataid = DASH_GetAssetDataId($db, $datanames);
        $dataids = safe_array_keys($dataid);
        $sql = "select * from " . $GLOBALS['PREFIX'] . "asset.$assetTable "
            . "where dataid in (" . implode(",", $dataids) . ") "
            . "order by machineid ";
        $result = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $result;
}

function DASH_GetAssetMulDetails($key, $db, $datanames)
{

    $result = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $assetTable = $assetArray['asset'];
        $machineTable = $assetArray['machine'];
        $dataid = DASH_GetAssetDataId($db, $datanames);
        $dataids = safe_array_keys($dataid);
        $sql = "select A.machineid,A.dataid,A.ordinal,A.value "
            . "from " . $GLOBALS['PREFIX'] . "asset.$assetTable A join " . $GLOBALS['PREFIX'] . "asset.DataName D "
            . "on D.dataid = A.dataid where  A.dataid in "
            . "(" . implode(",", $dataids) . ") "
            . "order by machineid,A.ordinal,D.name ";
        $result = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $result;
}

function DASH_GetAssetDataId($db, $datanames, $format = 1)
{

    $dataids = [];
    $namesStr = '';
    foreach ($datanames as $value) {
        $namesStr .= "'" . $value . "',";
    }
    $sql = "select dataid,name,groups from "
        . "" . $GLOBALS['PREFIX'] . "asset.DataName where name "
        . "in (" . rtrim($namesStr, ",") . ")";
    $result = find_many($sql, $db);
    foreach ($result as $key => $val) {
        if ($format == 1) {
            $dataids[$val['dataid']] = $val['name'];
        } else {
            $dataids[$val['name']] = $val['dataid'];
        }
    }
    if ($format == 2) {
        $dataids['group'] = $result[0]['groups'];
    }

    return $dataids;
}

function DASH_GetMachineMap($key, $db)
{

    $map = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select machineid,host,cust,slatest from "
            . "" . $GLOBALS['PREFIX'] . "asset.Machine";
        $result = find_many($sql, $db);
        foreach ($result as $key => $val) {
            $map[$val['machineid']][] = $val['host'];
            $map[$val['machineid']][] = $val['slatest'];
            $map[$val['machineid']][] = $val['cust'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $map;
}

function DASH_GetBasicInfoSite($key, $db, $site, $restrict)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetBasicInfo($key, $db, $scope, $restrict);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetBasicInfoGroup($key, $db, $machines, $restrict)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
            $assetDetails = DASH_GetBasicInfo($key, $db, $scope, $restrict);
        }
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetBasicInfoMach($key, $db, $censusId, $restrict)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetBasicInfo($key, $db, $scope, $restrict);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetBasicInfo($key, $db, $scope, $restrict)
{

    $datanames = array('Chassis Manufacturer', 'Chassis Type', 'Operating System', 'OS Version Number', 'NT Product Type', 'System Product', 'Serial Number', 'Physical Memory Total (Kbytes)', 'Machine Name', 'Site Name', 'Time Zone', 'User Name', 'Processor Version', 'Processor Manufacturer');
    $basicInfo = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $assetTable = $assetArray['asset'];
        $machineTable = $assetArray['machine'];
        $dataid = DASH_GetAssetDataId($db, $datanames);
        $dataids = safe_array_keys($dataid);
        $sql = "select A.machineid,A.dataid,A.ordinal,A.value
                from " . $GLOBALS['PREFIX'] . "asset.$machineTable M
                join " . $GLOBALS['PREFIX'] . "core.Census C
                on M.host = C.host and M.cust = C.site
                join " . $GLOBALS['PREFIX'] . "asset.$assetTable A
                on M.machineid = A.machineid
                where $scope and A.dataid in (" . implode(",", $dataids) . ")
                order by machineid,A.ordinal,A.dataid ";
        $result = find_many($sql, $db);
        $machineMap = DASH_GetMachineMap($key, $db);
        $basicInfo = UTIL_FormatAssetData($result, $dataid, $restrict, $machineMap);
    } else {
        echo "Your key has been expired";
    }
    return $basicInfo;
}

function DASH_GetSoftInfoSite($key, $db, $site, $softwarenames)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetSoftInfoGridData($key, $db, $scope, $softwarenames);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSystemInfoSite($key, $db, $site)
{
    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetSystemInfo($key, $db, $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSystemInfoGroup($key, $db, $machines)
{
    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
            $assetDetails = DASH_GetSystemInfo($key, $db, $scope);
        }
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSystemInfoMach($key, $db, $censusId)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetSystemInfo($key, $db, $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSystemInfo($key, $db, $scope)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $_SESSION['AdhocType'] = '';
        $fields = ':Operating System:Chassis Type:Chassis Manufacturer:Processor Version:Machine Name:User Name:OS Version Number:NT Installed Service Pack:Processor Manufacturer:Processor Type:Chassis Serial Number:System Product:';
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $machineTable = $assetArray['machine'];
        $assetTable = $assetArray['asset'];
        $sql = "select M.machineid
                from " . $GLOBALS['PREFIX'] . "asset.$machineTable M
                join " . $GLOBALS['PREFIX'] . "core.Census C
                on M.host = C.host and M.cust = C.site
                where $scope";
        $result = find_many($sql, $db);
        $mids = array();
        foreach ($result as $value) {
            $mids[] = $value['machineid'];
        }
        $terms = [];
        $response = run_adhoc_query($db, $mids, $terms, $fields, 0, 0, '');
        $result = [];
        $finalresult = [];
        $griddata = [];
        $os_grid_columns = array('Machine Name' => 0, 'User Name' => 1, 'Operating System' => 2, 'OS Version Number' => 3, 'NT Installed Service Pack' => 4);
        $chassis_type_grid_columns = array('Machine Name' => 0, 'User Name' => 1, 'Chassis Type' => 2, 'Chassis Serial Number' => 3, 'System Product' => 4);
        $chassis_manufacturer_grid_columns = array('Machine Name' => 0, 'User Name' => 1, 'Chassis Manufacturer' => 2, 'Chassis Serial Number' => 3, 'System Product' => 4);
        $processor_grid_columns = array('Machine Name' => 0, 'User Name' => 1, 'Processor Manufacturer' => 2, 'Processor Version' => 3, 'Processor Type' => 4);
        $griddata['operatingSystemGrid'] = [];
        $griddata['chassisTypeGrid'] = [];
        $griddata['chassisManufacturerGrid'] = [];
        $griddata['processorGrid'] = [];
        foreach ($response['columns'] as $type => $id) {
            $i = 0;
            foreach ($response['rows'] as $key => $data) {
                $val = !empty(current($data[$id])) ? current($data[$id]) : 'NA';
                if (!empty(current($data[$id]))) {
                    $result[$type][] = current($data[$id]);
                }

                switch ($type) {
                    case 'Operating System':
                        $griddata['operatingSystemGrid'][$i][$os_grid_columns['Operating System']] = $val;
                        break;
                    case 'Chassis Type':
                        $griddata['chassisTypeGrid'][$i][$chassis_type_grid_columns['Chassis Type']] = $val;
                        break;
                    case 'Chassis Manufacturer':
                        $griddata['chassisManufacturerGrid'][$i][$chassis_manufacturer_grid_columns['Chassis Manufacturer']] = $val;
                        break;
                    case 'Processor Version':
                        $griddata['processorGrid'][$i][$processor_grid_columns['Processor Version']] = $val;
                        break;
                    case 'OS Version Number':
                        $griddata['operatingSystemGrid'][$i][$os_grid_columns['OS Version Number']] = $val;
                        break;
                    case 'NT Installed Service Pack':
                        $griddata['operatingSystemGrid'][$i][$os_grid_columns['NT Installed Service Pack']] = $val;
                        break;
                    case 'Processor Manufacturer':
                        $griddata['processorGrid'][$i][$processor_grid_columns['Processor Manufacturer']] = $val;
                        break;
                    case 'Processor Type':
                        $griddata['processorGrid'][$i][$processor_grid_columns['Processor Type']] = $val;
                        break;
                    case 'Chassis Serial Number':
                        $griddata['chassisManufacturerGrid'][$i][$chassis_manufacturer_grid_columns['Chassis Serial Number']] = $val;
                        $griddata['chassisTypeGrid'][$i][$chassis_type_grid_columns['Chassis Serial Number']] = $val;
                        break;
                    case 'System Product':
                        $griddata['chassisTypeGrid'][$i][$chassis_type_grid_columns['System Product']] = $val;
                        $griddata['chassisManufacturerGrid'][$i][$chassis_manufacturer_grid_columns['System Product']] = $val;
                        break;
                    case 'Machine Name':
                        $griddata['operatingSystemGrid'][$i][$os_grid_columns['Machine Name']] = $val;
                        $griddata['chassisTypeGrid'][$i][$chassis_type_grid_columns['Machine Name']] = $val;
                        $griddata['chassisManufacturerGrid'][$i][$chassis_manufacturer_grid_columns['Machine Name']] = $val;
                        $griddata['processorGrid'][$i][$processor_grid_columns['Machine Name']] = $val;
                        break;
                    case 'User Name':
                        $griddata['operatingSystemGrid'][$i][$os_grid_columns['User Name']] = $val;
                        $griddata['chassisTypeGrid'][$i][$chassis_type_grid_columns['User Name']] = $val;
                        $griddata['chassisManufacturerGrid'][$i][$chassis_manufacturer_grid_columns['User Name']] = $val;
                        $griddata['processorGrid'][$i][$processor_grid_columns['User Name']] = $val;
                        break;
                    default:
                        break;
                }
                ksort($griddata['operatingSystemGrid'][$i]);
                ksort($griddata['chassisTypeGrid'][$i]);
                ksort($griddata['chassisManufacturerGrid'][$i]);
                ksort($griddata['processorGrid'][$i]);
                $i++;
            }
            $data_count_array = array_count_values($result[$type]);
            foreach ($data_count_array as $key => $value) {
                $finalresult['graphdata'][$type][] = array('name' => $key, 'y' => $value);
            }
        }
        $finalresult['griddata'] = $griddata;
        return $finalresult;
    } else {
        echo "Your key has been expired";
    }
}

function DASH_GetSoftInfoSiteGraphData($key, $db, $site, $softwarenames)
{
    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetAssetGraphData($key, $db, 'soft', $scope, $softwarenames);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSoftInfoGroup($key, $db, $machines, $softwarenames)
{
    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
        }
        $assetDetails = DASH_GetSoftInfoGridData($key, $db, $scope, $softwarenames);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSoftInfoGroupGraphdata($key, $db, $machines, $softwarenames)
{
    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
            $assetDetails = DASH_GetAssetGraphData($key, $db, 'soft', $scope, $softwarenames);
        }
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSoftInfoMach($key, $db, $censusId, $softwarenames)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetSoftInfoGridData($key, $db, $scope, $softwarenames);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetSoftInfoMachGraphdata($key, $db, $censusId, $softwarenames)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetAssetGraphData($key, $db, 'soft', $scope, $softwarenames);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetPatchInfoSite($key, $db, $site)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetAssetMulInfo($key, $db, 'patch', $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetPatchInfoGroup($key, $db, $machines)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
            $assetDetails = DASH_GetAssetMulInfo($key, $db, 'patch', $scope);
        }
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetPatchInfoMach($key, $db, $censusId)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetAssetMulInfo($key, $db, 'patch', $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetResourceInfoSite($key, $db, $site)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetAssetMulInfo($key, $db, 'resource', $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetResourceInfoGroup($key, $db, $machines)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
            $assetDetails = DASH_GetAssetMulInfo($key, $db, 'resource', $scope);
        }
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetResourceInfoMach($key, $db, $censusId)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetAssetMulInfo($key, $db, 'resource', $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetNetworkInfoSite($key, $db, $site)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $assetDetails = DASH_GetAssetMulInfo($key, $db, 'network', $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetNetworkInfoGroup($key, $db, $machines)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $cIds = safe_array_keys($machines);
        if (safe_count($cIds) != 0) {
            $scope = " C.id in (" . implode(",", $cIds) . ") ";
            $assetDetails = DASH_GetAssetMulInfo($key, $db, 'network', $scope);
        }
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetNetworkInfoMach($key, $db, $censusId)
{

    $assetDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $assetDetails = DASH_GetAssetMulInfo($key, $db, 'network', $scope);
    } else {
        echo "Your key has been expired";
    }
    return $assetDetails;
}

function DASH_GetAssetMulInfo($key, $db, $assetType, $scope)
{

    switch ($assetType) {
        case 'soft':
            $datanames = array('User Name', 'Installed Software Names', 'Installation Date', 'Version');
            break;
        case 'patch':
            $datanames = array('User Name', 'Description Name', 'Installed On', 'KB ID');
            break;
        case 'resource':
            $datanames = array('User Name', 'Physical Memory Total (Kbytes)', 'Logical Disk Name', 'Logical Disk KBytes Total', 'Logical Disk KBytes Used');
            break;
        case 'network':
            $datanames = array('User Name', 'Domain', 'Network Adapter', 'IP address', 'Subnet Mask', 'MAC address');
            break;
        default:
            break;
    }
    $multiInfo = [];

    $key = DASH_ValidateKey($key);
    if ($key) {
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $assetTable = $assetArray['asset'];
        $machineTable = $assetArray['machine'];
        $dataid = DASH_GetAssetDataId($db, $datanames);
        $dataids = safe_array_keys($dataid);
        $sql = "select A.machineid,A.dataid,A.ordinal,A.value
                from " . $GLOBALS['PREFIX'] . "asset.$machineTable M
                join " . $GLOBALS['PREFIX'] . "core.Census C
                on M.host = C.host and M.cust = C.site
                join " . $GLOBALS['PREFIX'] . "asset.$assetTable A
                on M.machineid = A.machineid
                where $scope and A.dataid in (" . implode(",", $dataids) . ")
                order by machineid,A.ordinal,A.dataid";
        $result = find_many($sql, $db);
        $machineMap = DASH_GetMachineMap($key, $db);
        $multiInfo = UTIL_FormatAssetMulData($result, $dataid, $machineMap);
    } else {
        echo "Your key has been expired";
    }
    return $multiInfo;
}

function DASH_GetAssetGraphData($key, $db, $assetType, $scope, $softwarenames)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $_SESSION['AdhocType'] = '';
        $fields = ':Installed Software Names:Version:';
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $machineTable = $assetArray['machine'];
        $assetTable = $assetArray['asset'];
        $datanames = array('Installed Software Names', 'Version');
        $dataid = DASH_GetAssetDataId($db, $datanames, 2);
        $dataids = array_values($dataid);
        $sql = "select M.machineid
                from " . $GLOBALS['PREFIX'] . "asset.$machineTable M
                join " . $GLOBALS['PREFIX'] . "core.Census C
                on M.host = C.host and M.cust = C.site
                where $scope";
        $result = find_many($sql, $db);
        $mids = array();
        foreach ($result as $value) {
            $mids[] = $value['machineid'];
        }
        $i = 1;
        $grphdata = array();
        $drilldowndata = array();
        $result = array();
        foreach ($softwarenames as $value) {
            $terms = [];
            $terms[] = array('dataid' => $dataid['Installed Software Names'], 'comparison' => 3, 'value' => $value, 'block' => $i, 'groups' => $dataid['group'], 'ordinal' => 1);
            $response = run_adhoc_query($db, $mids, $terms, $fields, 0, 0, 'info');
            $software_name = $value;
            $grphdata[] = array('name' => $software_name, 'y' => $response['total'], 'drilldown' => $software_name);
            $version_dataid = $response['columns']['Version'];
            $versions = [];
            foreach ($response['rows'] as $versiondata) {
                $versions[] = current($versiondata[$version_dataid]);
            }
            $version_count = array_count_values($versions);
            $version_count_array = [];
            foreach ($version_count as $key => $value) {
                $version_count_array[] = array($key, $value);
            }
            if (safe_count($version_count_array) > 0) {
                $drilldowndata[] = array('name' => $software_name, 'maxPointWidth' => 10, 'id' => $software_name, 'data' => $version_count_array);
            }
        }
        $result['data'] = $grphdata;
        $result['drilldowndata'] = $drilldowndata;
        return $result;
    } else {
        echo "Your key has been expired";
    }
}

function DASH_GetSoftwareUpdateData($key, $searchValue, $searchType, $dataScope, $searchVal, $orderValues, $limit, $pdo)
{

    $search = '';

    if ($searchVal != '') {
        $search = "and sitename like ?";
    }
    $siteList = [];

    $res = safe_array_keys($dataScope);
    foreach ($res as $row) {
        $siteList[] = $dataScope[$row];
    }
    $in = str_repeat('?,', safe_count($siteList) - 1) . '?';
    if ($searchType == 'Sites') {
        if ($searchValue == 'All') {
            $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename in ($in) $search $orderValues $limit";
            $stmt = $pdo->prepare($sql);
            $params = array_merge($siteList, ["%$searchVal%"]);
            $stmt->execute($params);
        } else {
            $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename = ? $search $orderValues $limit";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([$searchValue]);
        }
        $softwarelist = $stmt->fetchAll();
    }

    return $softwarelist;
}

function DASH_UpdateCensus($key, $searchValue, $searchType, $dataScope, $pdo)
{
    $census = array();
    $key = DASH_ValidateKey($key);
    if ($key) {
        $res = safe_array_keys($dataScope);
        foreach ($res as $row) {
            $siteList[] = $dataScope[$row];
        }

        $in = str_repeat('?,', safe_count($siteList) - 1) . '?';
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename in ($in)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($siteList);
            } else {
                $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$searchValue]);
            }
        }
        $list = $stmt->fetchAll();

        if ($list) {
            reset($list);
            foreach ($list as $key => $row) {
                $site = $row['sitename'];
                $census[$site] = 0;
            }
            reset($list);
            foreach ($list as $key => $row) {
                $site = $row['sitename'];
                $census[$site]++;
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $census;
}

function DASH_GetDeleteOS($key, $id, $pdo)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $stmt = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where id = ?");
        $stmt->execute([$id]);
        $deletesql = $stmt->rowCount();
    } else {
        echo "Your key has been expired";
    }

    if (safe_count($deletesql) > 0) {
        return true;
    } else {
        return false;
    }
}

function DASH_get_desiredversionlist($key, $site, $pdo)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $stmt = $pdo->prepare("select id,version,os from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and version != ''");
        $stmt->execute([$site]);
        $siteres = $stmt->fetchAll();
    } else {
        echo "Your key has been expired";
    }
    return $siteres;
}

function DASH_GetVersionUpdate($key, $window, $android, $Linux, $mac, $ios, $sites, $pdo)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $stmt1 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'Windows'");
        $stmt1->execute([$sites]);
        $oscheckwindowres = $stmt1->fetch();
        $oscheckwindowid = $oscheckwindowres['id'];

        $stmt2 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'Android'");
        $stmt2->execute([$sites]);
        $oscheckandroidres = $stmt2->fetch();
        $oscheckandroidid = $oscheckandroidres['id'];

        $stmt3 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'Linux'");
        $stmt3->execute([$sites]);
        $oschecklinuxres = $stmt3->fetch();
        $oschecklinuxid = $oschecklinuxres['id'];

        $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'MAC'");
        $stmt->execute([$sites]);
        $oscheckmacres = $stmt->fetch();
        $oscheckmacid = $oscheckmacres['id'];

        $stmt4 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename = ? and os = 'iOS'");
        $stmt4->execute([$sites]);
        $oscheckiosres = $stmt4->fetch();
        $oscheckiosid = $oscheckiosres['id'];

        if ($window != '') {
            if ($oscheckwindowid != '') {
                $sql = "update " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set version=? where os = 'Windows' and sitename = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$window, $sites]);
            } else {
                $sql = "insert into " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set sitename=?, os = 'Windows', version = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$sites, $window]);
            }
        }

        if ($android != '') {
            if ($oscheckandroidid != '') {
                $sql = "update " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set version=? where os = 'Android' and sitename = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$android, $sites]);
            } else {
                $sql = "insert into " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set sitename=?,os = 'Android', version = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$sites, $android]);
            }
        }

        if ($Linux != '') {
            if ($oschecklinuxid != '') {
                $sql = "update " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set version=? where os = 'Linux' and sitename = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$Linux, $sites]);
            } else {
                $sql = "insert into " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set sitename=?,os = 'Linux',version = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$sites, $Linux]);
            }
        }

        if ($mac != '') {
            if ($oscheckmacid != '') {
                $sql = "update " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set version=? where os = 'MAC' and sitename = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$mac, $sites]);
            } else {
                $sql = "insert into " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set sitename=?, os = 'MAC',version = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$sites, $mac]);
            }
        }

        if ($ios != '') {
            if ($oscheckiosid != '') {
                $sql = "update " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set version=? where os = 'iOS' and sitename = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$ios, $sites]);
            } else {
                $sql = "insert into " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites set sitename=?, os = 'iOS',version = ?";
                $stmt = $pdo->prepare($sql);
                $res = $stmt->execute([$sites, $ios]);
            }
        }
    } else {
        echo "Your key has been expired";
    }

    return $res;
}

function DASH_GetMachines($key, $sitename, $orderValues, $limit, $pdo)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select sitename,machine,timecontact,timeupdate,lastversion,oldversion, "
            . "newversion from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename = ? $orderValues $limit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sitename]);
        $siteres = $stmt->fetchAll();
    } else {
        echo "Your key has been expired";
    }
    return $siteres;
}

function DASH_allversionlist($key, $pdo, $ctype, $orderValues, $limit)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where global = 1 or owner in (?,'') "
            . "order by name, version $orderValues $limit";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ctype]);
        $siteres = $stmt->fetchAll();
    } else {
        echo "Your key has been expired";
    }
    return $siteres;
}

function DASH_GetVersionDetailList($key, $vid, $pdo)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "swupdate.Downloads where id = ?");
        $stmt->execute([$vid]);
        $siteres = $stmt->fetch();
    } else {
        echo "Your key has been expired";
    }
    return $siteres;
}

function DASH_GetAssetFilterList($key, $db, $authuser)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select id, name from " . $GLOBALS['PREFIX'] . "asset.AssetSearches where username = '$authuser' or global = 1 order by name, global";
        $assetres = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $assetres;
}

function DASH_GetAssetReportResult($key, $db, $qid, $site, $siteType, $auth, $time, $offset, $totalMachine)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = 'select name from ' . $GLOBALS['PREFIX'] . 'asset.AssetSearches where id=' . $qid . '';
        $row = find_one($sql, $db);

        $queryInsert = "insert into " . $GLOBALS['PREFIX'] . "asset.AssetQueryResult( `pid`,  `qid`,  `sitename`,`sitetype` ,`machine`, `offset`, `queryName`,  `status`,`global`,"
            . " `pathName`, `fileName`, `userName`, `startTime`,  `endTime`)values('', $qid, '" . $site . "', '" . $siteType . "' ,'" . $totalMachine . "',"
            . " $offset,'" . $row['name'] . "', 'Process initiated', 0, 0, '', '$auth',$time, '')";

        $result = redcommand($queryInsert, $db);
    } else {
        echo "Your key has been expired";
    }
    return $result;
}

function DASH_GetEventFilerList($key, $pdo, $authuser)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        mysqli_query("set names 'utf8'");
        $stmt = $pdo->prepare("select id, name, eventtag from " . $GLOBALS['PREFIX'] . "event.SavedSearches where username = ? or global = ? order by name, global");
        $stmt->execute([$authuser, 1]);
        $eventres = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Your key has been expired";
    }
    return $eventres;
}

function DASH_GetAlertNWarnCompSite($key, $db, $itemtype, $day, $site)
{

    $alrtwarnDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $scope = " C.site in (" . rtrim($sitelist, ",") . ")";
        } else {
            $scope = " C.site = '$site'";
        }
        $alrtwarnDetails = DASH_GetAlertNWarnComp($db, $scope, $itemtype);
    } else {
        echo "Your key has been expired";
    }
    return $alrtwarnDetails;
}

function DASH_GetAlertNWarnCompGrp($key, $db, $itemtype, $day, $machines)
{

    $alrtwarnDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $censusIds = safe_array_keys($machines);
        if (safe_count($censusIds) > 0) {
            $scope = " C.id in (" . implode(",", $censusIds) . ") ";
            $alrtwarnDetails = DASH_GetAlertNWarnComp($db, $scope, $itemtype);
        }
    } else {
        echo "Your key has been expired";
    }
    return $alrtwarnDetails;
}

function DASH_GetAlertNWarnCompMach($key, $db, $itemtype, $day, $censusId)
{

    $alrtwarnDetails = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $scope = " C.id = $censusId ";
        $alrtwarnDetails = DASH_GetAlertNWarnComp($db, $scope, $itemtype);
    } else {
        echo "Your key has been expired";
    }
    return $alrtwarnDetails;
}

function DASH_GetAlertNWarnComp($db, $scope, $itemtype)
{

    $dayFrom = 14 - intval($day);
    $now = strtotime('Today');
    $date_from = strtotime('-' . $dayFrom . ' days', $now);
    $date_to = $date_from + 86340;

    if ($itemtype == '1') {
        $itemtype = "5,7,8,9,10";
    }

    if ($day == 14) {
        $tablename = 'EventBasedDisplay';
    } else {
        $tablename = 'ComplianceSummary';
    }

    $cGraphQry = "select From_UNIXTIME(EBD.servertime,'%d') as day, EBD.itemtype, count(EBD.status) as count, EBD.status
                      from " . $GLOBALS['PREFIX'] . "dashboard.$tablename EBD join " . $GLOBALS['PREFIX'] . "core.Census C on C.id = EBD.censusid  where
                      $scope and EBD.servertime between $date_from and $date_to and
                      EBD.itemType in ( $itemtype ) and EBD.userid = 2 group by EBD.status,EBD.itemtype,
                      From_UNIXTIME(EBD.servertime,'%d-%m-%y') order by EBD.status";
    $compGraph = find_many($cGraphQry, $db);
    $warningcount = 0;
    $alertcount = 0;

    foreach ($compGraph as $key => $val) {
        $status = $val['status'];
        $count = $val['count'];

        if ($status == 2) {
            $warningcount += $count;
        } else if ($status == 3) {
            $alertcount += $count;
        }
    }
    $return['warningcount'] = $warningcount;
    $return['alertcount'] = $alertcount;

    return $return;
}

function DASH_GetComplianceHomeItems($key, $db, $itemtype)
{

    $itemIds = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $tableName = Dash_GetTableName($itemtype);
        $sql = "select $tableName[1],name from "
            . "" . $GLOBALS['PREFIX'] . "dashboard.$tableName[0] where "
            . "enabled = 1";
        $result = find_many($sql, $db);

        foreach ($result as $key => $val) {
            $itemIds[$val['name']] = $val[$tableName[1]];
        }
    } else {
        echo "Your key has been expired";
    }
    return $itemIds;
}

function DASH_IsRedisEnabled($key, $db)
{
    return true;
    // $key = DASH_ValidateKey($key);
    // if ($key) {
    //     $flag = false;
    //     $sql = "SELECT value FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE name = 'redis_enable' LIMIT 1";
    //     $result = find_one($sql, $db);
    //     if (safe_count($result) > 0) {
    //         if ($result['value'] == 1 || $result['value'] == '1') {
    //             $flag = true;
    //         } else {
    //             $flag = false;
    //         }
    //     } else {
    //         $flag = false;
    //     }
    //     return $flag;
    // } else {
    //     echo "Your key has been expired";
    // }
}

function DASH_GetCensusId($apikey, $db, $machineName)
{
    $sql = "SELECT C.id FROM " . $GLOBALS['PREFIX'] . "core.Census C where C.host = '$machineName' ORDER BY C.id DESC LIMIT 1";
    $result = find_one($sql, $db);

    if (safe_count($result) > 0) {
        return $result['id'];
    } else {
        return 0;
    }
}

function DASH_GetAllCapacityData($searchType, $dataScope, $siteVal)
{
    $db = db_connect();
    $key = '';
    switch ($searchType) {
        case 'Sites':
            $machineArray = DASH_GetMachinesSites($key, $db, $dataScope);
            $capResult = DASH_GetSiteCapacityInfo($key, $db, $dataScope);
            break;
        case 'Groups':
            $machineArray = DASH_GetGroupsMachines($key, $db, $dataScope);
            $capResult = DASH_GetSiteCapacityInfoGroup($key, $db, $machineArray);
            break;
        case 'ServiceTag':
            $machineArray = array($searchValue);
            $censusId = $_SESSION['rcensusId'];
            $machine = $_SESSION['searchValue'];
            $capResult = DASH_GetSiteCapacityInfoMachine($key, $db, $machine, $siteVal);
            break;
        default:
            break;
    }
    return $capResult;
}

function DASH_GetAllAdocQueryData($searchType, $dataScope, $siteVal)
{
    global $db;
    $db = db_connect();
    $recordListData = array();

    $username = $_SESSION['user']['logged_username'];
    $sqlevent = "select * from " . $GLOBALS['PREFIX'] . "report.FilterReport where username='$username' order by reportid desc";

    $eventRes = find_many($sqlevent, $db);

    $recordList = [];

    if (safe_count($eventRes) > 0) {

        $serialNum = 1;
        foreach ($eventRes as $key => $value) {
            $reportid = $value['reportid'];
            $reportname = $value['reportname'];
            $reportModule = $value['reportModule'];
            $emaillist = $value['emaillist'];
            $scope = $value['scope'];
            if (strpos($value['parentVal'], '_') !== false) {
                $parentVal = UTIL_GetTrimmedGroupName($value['parentVal']);
            } else {
                $parentVal = $value['parentVal'];
            }

            if (strpos($value['scopeVal'], '_') !== false) {
                $scopeVal = UTIL_GetTrimmedGroupName($value['scopeVal']);
            } else {
                $scopeVal = $value['scopeVal'];
            }

            $status = $value['status'];
            if ($status == '1') {
                $status = "Active";
            } else {
                $status = "In-Active";
            }
            $reportSchedule = $value['reportSchedule'];

            if ($scope == "Sites") {
                $scopeDtls = $scopeVal;
            } else if ($scope == "ServiceTag") {
                $scopeDtls = $parentVal . ":" . $scopeVal;
            } else if ($scope == "Groups") {
                if ($parentVal == '') {
                    $scopeDtls = $scopeVal;
                } else {

                    $scopeDtls = $parentVal . ":" . $scopeVal;
                }
            }

            $reportname = '<p class="ellipsis" id="' . $reportname . '" value="' . $reportname . '" title="' . $reportname . '">' . $reportname . '</p>';
            $emaillist = '<p class="ellipsis" id="' . $emaillist . '" value="' . $emaillist . '" title="' . $emaillist . '">' . $emaillist . '</p>';
            $reportModule = '<p class="ellipsis" id="' . $reportModule . '" value="' . $reportModule . '" title="' . $reportModule . '">' . $reportModule . '</p>';
            $status = '<p class="ellipsis" id="' . $status . '" value="' . $status . '" title="' . $status . '">' . $status . '</p>';
            $reportSchedule = '<p class="ellipsis" id="' . $reportSchedule . '" value="' . $reportSchedule . '" title="' . $reportSchedule . '">' . $reportSchedule . '</p>';
            $scopeDtls = '<p class="ellipsis" id="' . $scopeDtls . '" value="' . $scopeDtls . '" title="' . $scopeDtls . '">' . $scopeDtls . '</p>';

            $recordListData[] = array("DT_RowId" => $reportid, $reportname, $emaillist, $reportModule, $status, $reportSchedule, $scopeDtls);
            $serialNum++;
        }
    } else {
    }

    return $recordListData;
}

function DASH_GetEventData($eventType, $db)
{
    $resp = "";

    $sql = "select scrip,description from  " . $GLOBALS['PREFIX'] . "event.EventScrips order by scrip ASC";
    $res = find_many($sql, $db);

    $resp .= '<div class="form-group clearfix row event_div" style="margin: 0px;">
                    <label for="user-name" class="col-sm-3 align-label">Scrip Number</label>
                    <div class="col-sm-9">
                    <select class="form-control dropdown-submenu" data-container="body" id="dart_num" data-size="5" onchange="add_dart()">
                    <option value="null">Please select Scrip Number</option>';
    foreach ($res as $value) {
        $resp .= '<option value="' . $value['scrip'] . '" >' . $value['description'] . '</option>';
    }
    $resp .= '</select>
            <span style="float: right;color:red;">*</span>
            </div>
            </div>
            <div class="form-groupclearfix row event_div" style="margin: 0px;">
            <label for="user-name" class="col-sm-3 align-label" style=""></label>
            <label for="user-name" class="col-sm-2 align-label" style=""></label>
            <div class="col-sm-7">
            <label for="user-name" class="col-sm-7 align-label" style="color:#000;">OR</label>
            </div>
            </div>';
    $sql1 = "select id,name,searchstring from " . $GLOBALS['PREFIX'] . "event.SavedSearches order by name ASC";
    $res1 = find_many($sql1, $db);
    $resp .= '<div class="form-group clearfix row event_div" style="margin: 0px;">
                    <label for="user-name" class="col-sm-3 align-label">Event Filters</label>
                    <div class="col-sm-9">
        <select class="form-control dropdown-submenu" data-container="body" id="scrp_num" data-size="5" onchange="add_scrip()">
        <option value="null" >Please select Event Filters</option>';

    foreach ($res1 as $value1) {
        $resp .= '<option value="' . $value1['id'] . '" >' . $value1['name'] . '</option>';
    }
    $resp .= '</select>
        <span style="float: right;color:red;">*</span>
        </div>
        </div>';

    $sql2 = "select id,name from " . $GLOBALS['PREFIX'] . "asset.AssetSearches order by name ASC";
    $res2 = find_many($sql2, $db);
    $resp .= '<div class="form-group clearfix row asset_div"  style="margin: 0px;display: none;" >
                                                <label for="user-name" class="col-sm-3 align-label" >Asset Queries</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control dropdown-submenu" data-container="body" id="assSearch_num" data-size="5" >
                                                    <option value="null">Please select Asset Queries</option>';
    foreach ($res2 as $value2) {
        $resp .= '<option value="' . $value2['id'] . '" >' . $value2['name'] . '</option>';
    }
    $resp .= '</select>
                <span style="float: right;color:red;">*</span>
                </div>
                </div>';

    return $resp;
}

function GET_Edit_eventData($eventType, $db)
{

    $resp = "";

    $sql = "select scrip,description from  " . $GLOBALS['PREFIX'] . "event.EventScrips order by scrip ASC";
    $res = find_many($sql, $db);

    $resp .= '<div class="form-group clearfix row event_div" style="margin: 0px;">
                    <label for="user-name" class="col-sm-3 align-label">Scrip Number</label>
                    <div class="col-sm-9">
                    <select class="form-control dropdown-submenu" data-container="body" id="dart_num" data-size="5" onchange="query_dart()">
                    <option value="null">Please select Scrip Number</option>';
    foreach ($res as $value) {

        $resp .= '<option value="' . $value['scrip'] . '" >' . $value['description'] . '</option>';
    }
    $resp .= '</select>
            <span style="float: right;color:red;">*</span>
            </div>
            </div>
            <div class="form-groupclearfix row event_div" style="margin: 0px;">
            <label for="user-name" class="col-sm-3 align-label" style=""></label>
            <label for="user-name" class="col-sm-2 align-label" style=""></label>
            <div class="col-sm-7">
            <label for="user-name" class="col-sm-7 align-label" style="color:#000;">OR</label>
            </div>
            </div>';
    $sql1 = "select id,name,searchstring from " . $GLOBALS['PREFIX'] . "event.SavedSearches order by name ASC";
    $res1 = find_many($sql1, $db);
    $resp .= '<div class="form-group clearfix row event_div" style="margin: 0px;">
                    <label for="user-name" class="col-sm-3 align-label">Event Filters</label>
                    <div class="col-sm-9">
        <select class="form-control dropdown-submenu" data-container="body" id="scrp_num" data-size="5" onchange="query_script()">
        <option value="null" >Please select Event Filters</option>';

    foreach ($res1 as $value1) {

        $resp .= '<option value="' . $value1['id'] . '" >' . $value1['name'] . '</option>';
    }
    $resp .= '</select>
        <span style="float: right;color:red;">*</span>
        </div>
        </div>';

    $sql2 = "select id,name from " . $GLOBALS['PREFIX'] . "asset.AssetSearches order by name ASC";
    $res2 = find_many($sql2, $db);
    $resp .= '<div class="form-group clearfix row asset_div"  style="margin: 0px;display: none;" >
                                                <label for="user-name" class="col-sm-3 align-label" >Asset Queries</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control dropdown-submenu" data-container="body" id="assSearch_num" data-size="5" >
                                                    <option value="null">Please select Asset Queries</option>';
    foreach ($res2 as $value2) {
        $resp .= '<option value="' . $value2['id'] . '" >' . $value2['name'] . '</option>';
    }
    $resp .= '</select>
                <span style="float: right;color:red;">*</span>
                </div>
                </div>';

    return $resp;
}

function DASH_GetEvent_DelData($selectedDataId, $db)
{
    $sql = "delete from " . $GLOBALS['PREFIX'] . "report.FilterReport where reportid='$selectedDataId'";

    $res = redcommand($sql, $db);
    if ($res) {
        $resp = "success";
    } else {
        $resp = "failed";
    }
    return $resp;
}

function DASH_GetEvent_EditData($selectedDataId, $db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "report.FilterReport where reportid='$selectedDataId'";
    $res = find_one($sql, $db);

    if ($res['reportRange'] == 1) {

        $selected1 = "selected";
        $scheduDay = "display:block;";
        $dailytime = date("m/d/Y H:i:s", $res['reportTime']);
        $dal = explode(" ", $dailytime);
        $reportTime = $res['reportTime'];
        $hm = explode(":", $reportTime);
        $Dayhour = $hm[0];
        $Daymin = $hm[1];
    } elseif ($res['reportRange'] == 7) {
        $selected2 = "selected";
        $scheduWeek = "display:block;";
        $reptDay = $res['reportDay'];
        $dailytime = date("m/d/Y H:i:s", $res['reportTime']);
        $dal = explode(" ", $dailytime);
        $reportTime = $res['reportTime'];
        $hm = explode(":", $reportTime);
        $Dayhour = $hm[0];
        $Daymin = $hm[1];
    } elseif ($res['reportRange'] == 30) {
        $selected3 = "selected";
        $scheduMon = "display:block;";
        $reptDay = $res['reportDay'];
        $dailytime = date("m/d/Y H:i:s", $res['reportTime']);
        $dal = explode(" ", $dailytime);
        $reportTime = $res['reportTime'];
        $hm = explode(":", $reportTime);

        $Dayhour = $hm[0];
        $Daymin = $hm[1];
    }

    if ($res['reportModule'] == "Event") {
        $checked1 = "checked";
        $eventType = "Event";

        $searchId = $res['searchId'];
    } else if ($res['reportModule'] == "Asset") {
        $checked2 = "checked";
        $eventType = "Asset";
        $searchId = $res['searchId'];
    }
    $reportModule = $res['reportModule'];
    $reportON = $res['reportOn'];

    $lists = getEvent_Lists($reportModule, $reportON, $searchId, $db);
    $tmezone = '("' . date_default_timezone_get() . '")';
    $resp = "";
    $resp .= '

                                            <div class="form-group clearfix row">
                                                <label for="search_id" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3 customscroll">
                                                <label><input type="radio" ' . $checked1 . ' name="EditeventType" class="Editreport_type event-radio" id="edit_event" onclick="edit_reportRdio(this)" value="Event" > Event Report</label>
                                                </div>
                                                <div class="col-sm-3 customscroll">
                                                <label><input type="radio" ' . $checked2 . ' name="EditeventType" class="Editreport_type asset-radio" id="edit_asset" onclick="edit_reportRdio(this)" value="Asset" > Asset Report</label>
                                                </div>
                                                     <label for="search_id" class="col-sm-3 align-label"></label>


                                            </div>
                                        <div class="form-group clearfix row">
                                                <label for="search_id" class="col-sm-3 align-label">Report Name</label>
                                                <div class="col-sm-9 customscroll">
                                                    <input class="form-control required" id="Editreport_name" type="text" value="' . $res['reportname'] . '">
                                                    <input class="form-control required" id="editReportID" type="hidden" value="' . $selectedDataId . '">
                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>
                                            ' . $lists . '<div class="form-group clearfix row">
                                                <label for="search_id" class="col-sm-3 align-label">Email Ids</label>
                                                <div class="col-sm-9 customscroll">
                                                    <input class="form-control required" id="Editreport_email" type="text" value="' . $res['emaillist'] . '">
                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>
                                            <div class="form-group clearfix row">
                                                <label for="search_id" class="col-sm-3 align-label">Report Shedule</label>
                                                <div class="col-sm-9 customscroll">

                                                    <select class="form-control dropdown-submenu" data-container="body" id="Editreport_duration" onchange="_getEditScheduleView();" data-size="5" >
                                                        <option value="" >Please select Report Schedule</option>
                                                        <option value="1"' . $selected1 . '>Daily</option>
                                                        <option value="7"' . $selected2 . '>Weekly</option>
                                                        <option value="30"' . $selected3 . '>Monthly</option>
                                                    </select>
                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
    if ($res['reportRange'] == 1) {
        $resp .= '<div class="form-group clearfix row EditdaysDIV" style="margin: 0px;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="Editdays_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>';
        for ($i = 0; $i < 24; $i++) {

            if ($Dayhour == $i) {
                $DHselected = "selected";
            } else {
                $DHselected = "";
            }
            $resp .= '<option value="' . $i . '" ' . $DHselected . '>' . $i . '</option>';
        }

        $resp .= '</select>';

        $resp .= '<span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="Editdays_min" >';
        $arr = array("00", "10", "20", "30", "40", "50");
        if (in_array($Daymin, $arr)) {
            for ($i = 0; $i < safe_count($arr); $i++) {
                if ($Daymin == $arr[$i]) {
                    $DMselected = "selected";
                } else {
                    $DMselected = "";
                }
                $resp .= '<option value="' . $arr[$i] . '" ' . $DMselected . '>' . $arr[$i] . '</option>';
            }
        } else {
            for ($i = 0; $i < safe_count($arr); $i++) {
                $resp .= '<option value="' . $arr[$i] . '" >' . $arr[$i] . '</option>';
            }
            $DMselected = "selected";
            $resp .= '<option value="' . $Daymin . '" selected>' . $Daymin . '</option>';
        }

        $resp .= '</select>';

        $resp .= '<span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-1">
                                                </div>
                                            </div>';
        $resp .= '<div class="form-group clearfix row EditweekDIV" style="margin: 0px;display:none;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweekday" style="display: inline-block;">
                                                        <option value="null">Select weekDay</option>
                                                        <option value="1">Sunday</option>
                                                        <option value="2">Monday</option>
                                                        <option value="3">Tuesday</option>
                                                        <option value="4">Wednesday</option>
                                                        <option value="5">Thursday</option>
                                                        <option value="6">Friday</option>
                                                        <option value="7">Saturday</option>
                                                    </select>

                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweek_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>
                                                        <option value="0">00</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweek_min">
                                                        <option value="null" >Select Min</option>
                                                        <option value="0" >00</option>
                                                        <option value="10" >10</option>
                                                        <option value="20" >20</option>
                                                        <option value="30" >30</option>
                                                        <option value="40" >40</option>
                                                        <option value="50" >50</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
        $resp .= '<div class="form-group clearfix row EditmonthDIV" style="margin: 0px;display:none;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_day" style="display: inline-block;">
                                                        <option value="null">Select Day</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                        <option value="24">24</option>
                                                        <option value="25">25</option>
                                                        <option value="26">26</option>
                                                        <option value="27">27</option>
                                                        <option value="28">28</option>
                                                        <option value="29">29</option>
                                                        <option value="30">30</option>
                                                        <option value="30">31</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>
                                                        <option value="0">00</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_min" >
                                                        <option value="null" >Select Min</option>
                                                        <option value="0" >00</option>
                                                        <option value="10" >10</option>
                                                        <option value="20" >20</option>
                                                        <option value="30" >30</option>
                                                        <option value="40" >40</option>
                                                        <option value="50" >50</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
    }
    if ($res['reportRange'] == 7) {
        $resp .= '<div class="form-group clearfix row EditdaysDIV" style="margin: 0px;display:none;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="Editdays_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>
                                                        <option value="0">00</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="Editdays_min" >
                                                        <option value="null">Select Min</option>
                                                        <option value="0" >00</option>
                                                        <option value="10" >10</option>
                                                        <option value="20" >20</option>
                                                        <option value="30" >30</option>
                                                        <option value="40" >40</option>
                                                        <option value="50" >50</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-1">
                                                </div>
                                            </div>';
        $resp .= '<div class="form-group clearfix row EditweekDIV" style="margin: 0px;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweekday" style="display: inline-block;">
                                                        <option value="null">Select weekDay</option>';

        $arrvals = array("", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");

        $reptDayL = $reptDay;

        for ($i = 1; $i < 7; $i++) {
            if ($reptDayL == $i) {
                $WKselected = "selected";
            } else {
                $WKselected = "";
            }
            $resp .= '<option value="' . $i . '" ' . $WKselected . '>' . $arrvals[$i] . '</option>';
        }
        $resp .= '</select>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweek_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>';
        for ($i = 0; $i < 24; $i++) {

            if ($Dayhour == $i) {
                $DHselected = "selected";
            } else {
                $DHselected = "";
            }
            $resp .= '<option value="' . $i . '" ' . $DHselected . '>' . $i . '</option>';
        }

        $resp .= '</select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweek_min">';
        $arr = array("00", "10", "20", "30", "40", "50");
        if (in_array($Daymin, $arr)) {

            for ($i = 0; $i < safe_count($arr); $i++) {
                if (trim($Daymin) == $arr[$i]) {
                    $DMselected = "selected";
                } else {
                    $DMselected = "";
                }

                $resp .= '<option value="' . $arr[$i] . '" ' . $DMselected . '>' . $arr[$i] . '</option>';
            }
        } else {

            for ($i = 0; $i < safe_count($arr); $i++) {
                $resp .= '<option value="' . $arr[$i] . '" >' . $arr[$i] . '</option>';
            }
            $DMselected = "selected";
            $resp .= '<option value="' . $Daymin . '" selected>' . $Daymin . '</option>';
        }

        $resp .= '</select>';

        $resp .= '<span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
        $resp .= '<div class="form-group clearfix row EditmonthDIV" style="margin: 0px;display:none;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_day" style="display: inline-block;">
                                                        <option value="null">Select Day</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                        <option value="24">24</option>
                                                        <option value="25">25</option>
                                                        <option value="26">26</option>
                                                        <option value="27">27</option>
                                                        <option value="28">28</option>
                                                        <option value="29">29</option>
                                                        <option value="30">30</option>
                                                        <option value="30">31</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>
                                                        <option value="0">00</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_min" >
                                                        <option value="null" >Select Min</option>
                                                        <option value="0" >00</option>
                                                        <option value="10" >10</option>
                                                        <option value="20" >20</option>
                                                        <option value="30" >30</option>
                                                        <option value="40" >40</option>
                                                        <option value="50" >50</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
    }
    if ($res['reportRange'] == 30) {
        $resp .= '<div class="form-group clearfix row EditdaysDIV" style="margin: 0px;display:none;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="Editdays_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>
                                                        <option value="0">00</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <select class="form-control" id="Editdays_min" >
                                                        <option value="null">Select Min</option>
                                                        <option value="0" >00</option>
                                                        <option value="10" >10</option>
                                                        <option value="20" >20</option>
                                                        <option value="30" >30</option>
                                                        <option value="40" >40</option>
                                                        <option value="50" >50</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-1">
                                                </div>
                                            </div>';
        $resp .= '<div class="form-group clearfix row EditweekDIV" style="margin: 0px;display:none;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweekday" style="display: inline-block;">
                                                        <option value="null">Select weekDay</option>
                                                        <option value="1">Sunday</option>
                                                        <option value="2">Monday</option>
                                                        <option value="3">Tuesday</option>
                                                        <option value="4">Wednesday</option>
                                                        <option value="5">Thursday</option>
                                                        <option value="6">Friday</option>
                                                        <option value="7">Saturday</option>
                                                    </select>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweek_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>
                                                        <option value="0">00</option>
                                                        <option value="1">1</option>
                                                        <option value="2">2</option>
                                                        <option value="3">3</option>
                                                        <option value="4">4</option>
                                                        <option value="5">5</option>
                                                        <option value="6">6</option>
                                                        <option value="7">7</option>
                                                        <option value="8">8</option>
                                                        <option value="9">9</option>
                                                        <option value="10">10</option>
                                                        <option value="11">11</option>
                                                        <option value="12">12</option>
                                                        <option value="13">13</option>
                                                        <option value="14">14</option>
                                                        <option value="15">15</option>
                                                        <option value="16">16</option>
                                                        <option value="17">17</option>
                                                        <option value="18">18</option>
                                                        <option value="19">19</option>
                                                        <option value="20">20</option>
                                                        <option value="21">21</option>
                                                        <option value="22">22</option>
                                                        <option value="23">23</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editweek_min">
                                                        <option value="null" >Select Min</option>
                                                        <option value="0" >00</option>
                                                        <option value="10" >10</option>
                                                        <option value="20" >20</option>
                                                        <option value="30" >30</option>
                                                        <option value="40" >40</option>
                                                        <option value="50" >50</option>
                                                    </select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
        $resp .= '<div class="form-group clearfix row EditmonthDIV" style="margin: 0px;">
                                                <label for="user-name" class="col-sm-3 align-label"></label>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_day" style="display: inline-block;">
                                                        <option value="null">Select Day</option>';
        for ($i = 1; $i < 32; $i++) {
            if ($reptDay == $i) {
                $MDselected = "selected";
            } else {
                $MDselected = "";
            }
            $resp .= '<option value="' . $i . '" ' . $MDselected . '>' . $i . '</option>';
        }

        $resp .= '</select>

                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_hour" style="display: inline-block;">
                                                        <option value="null">Select Hour</option>';
        for ($i = 0; $i < 24; $i++) {

            if ($Dayhour == $i) {
                $DHselected = "selected";
            } else {
                $DHselected = "";
            }
            $resp .= '<option value="' . $i . '" ' . $DHselected . '>' . $i . '</option>';
        }

        $resp .= '</select>';

        $resp .= '<span style="float: right;color:red;">*</span>
                                                </div>
                                                <div class="col-sm-3">
                                                    <select class="form-control" id="Editmonth_min" >
                                                        <option value="null">Select Min</option>';
        $arr = array("00", "10", "20", "30", "40", "50");
        if (in_array($Daymin, $arr)) {
            for ($i = 0; $i < safe_count($arr); $i++) {
                if ($Daymin == $arr[$i]) {
                    $DMselected = "selected";
                } else {
                    $DMselected = "";
                }
                $resp .= '<option value="' . $arr[$i] . '" ' . $DMselected . '>' . $arr[$i] . '</option>';
            }
        } else {
            for ($i = 0; $i < safe_count($arr); $i++) {
                $resp .= '<option value="' . $arr[$i] . '" >' . $arr[$i] . '</option>';
            }
            $DMselected = "selected";
            $resp .= '<option value="' . $Daymin . '" selected>' . $Daymin . '</option>';
        }

        $resp .= '</select>

                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>';
    }

    if (($res['status'] == '1') || ($res['status'] == 1)) {

        $selected1 = "selected";
        $selected2 = "";
    } else if (($res['status'] == '0') || ($res['status'] == 0)) {

        $selected1 = "";
        $selected2 = "selected";
    }
    $resp .= '<div class="form-group clearfix row">
                                                <label for="search_id" class="col-sm-3 align-label">Status</label>
                                                <div class="col-sm-9 customscroll">
                                                <select class="form-control dropdown-submenu" data-container="body" id="Editreport_Status" data-size="5" >
                                                        <option value="1" ' . $selected1 . '>Active</option>
                                                        <option value="0" ' . $selected2 . '>In-Active</option>

                                                    </select>
                                                    <span style="float: right;color:red;">*</span>
                                                </div>
                                            </div>
                                            ';

    return $resp;
}

function getEvent_Lists($reportModule, $reportON, $searchId, $db)
{
    if ($reportModule == "Event") {

        if ($reportON == "Dart") {
            $dartDis = "";
            $filtrDis = "disabled";
            $assetDis = "display:none";
            $eventDiapl = 'display:block;';
            $searchIddrt = $searchId;
        } else if ($reportON == "Filter") {
            $dartDis = "disabled";
            $assetDis = "display:none";
            $filtrDis = "";
            $eventDis = "";
            $eventDiapl = 'display:block;';
            $searchIdftr = $searchId;
        }
    } else if ($reportModule == "Asset") {
        $eventDis = "disabled";
        $dartDis = "";
        $eventDiapl = 'display:none;';
        $assetDis = "display:block";
        $searchIdass = $searchId;
    }

    $resp = "";
    $sql = "select scrip,description from  " . $GLOBALS['PREFIX'] . "event.EventScrips order by scrip ASC";
    $res = find_many($sql, $db);
    $resp .= '<div class="form-group clearfix row Editevent_div" style="margin: 0px;' . $eventDiapl . '">
                    <label for="user-name" class="col-sm-3 align-label">Scrip Number</label>
                    <div class="col-sm-9">
                    <select class="form-control dropdown-submenu Editdart_num" data-container="body" id="editevent_dart" ' . $dartDis . ' data-size="5" onchange="query_dart()">
                    <option value="null">Please select Scrip Number</option>';
    foreach ($res as $value) {
        if ($searchIddrt == $value['scrip']) {
            $selectDart = "selected";
        } else {
            $selectDart = "";
        }
        $resp .= '<option value="' . $value['scrip'] . '" ' . $selectDart . ' >' . $value['description'] . '</option>';
    }
    $resp .= '</select>
            <span style="float: right;color:red;">*</span>
            </div>
            </div>
            <div class="form-groupclearfix row Editevent_div" style="margin: 0px;' . $eventDiapl . '">
            <label for="user-name" class="col-sm-3 align-label" style=""></label>
            <label for="user-name" class="col-sm-2 align-label" style=""></label>
            <div class="col-sm-7">
            <label for="user-name" class="col-sm-7 align-label" style="color:#000;">OR</label>
            </div>
            </div>';
    $sql1 = "select id,name,searchstring from " . $GLOBALS['PREFIX'] . "event.SavedSearches order by name ASC";
    $res1 = find_many($sql1, $db);
    $resp .= '<div class="form-group clearfix row Editevent_div" style="margin: 0px;' . $eventDiapl . '">
                    <label for="user-name" class="col-sm-3 align-label">Event Filters</label>
                    <div class="col-sm-9">
        <select class="form-control dropdown-submenu Editscrp_num" data-container="body" id="editevent_filter"  data-size="5" ' . $filtrDis . ' onchange="query_script()">
        <option value="null" >Please select Event Filters</option>';

    foreach ($res1 as $value1) {
        if ($searchIdftr == $value1['id']) {
            $selectScr = "selected";
        } else {
            $selectScr = "";
        }
        $resp .= '<option value="' . $value1['id'] . '" ' . $selectScr . ' >' . $value1['name'] . '</option>';
    }
    $resp .= '</select>
        <span style="float: right;color:red;">*</span>
        </div>
        </div>';

    $sql2 = "select id,name from " . $GLOBALS['PREFIX'] . "asset.AssetSearches order by name ASC";
    $res2 = find_many($sql2, $db);
    $resp .= '<div class="form-group clearfix row Editasset_div"  style="margin: 0px;' . $assetDis . '" >
                                                <label for="user-name" class="col-sm-3 align-label" >Asset</label>
                                                <div class="col-sm-9">
                                                    <select class="form-control dropdown-submenu EditassSearch_num" data-container="body" id="editAsset_select" data-size="5" >
                                                    <option value="null">Please select Asset Queries</option>';
    foreach ($res2 as $value2) {
        if ($searchIdass == $value2['id']) {
            $selectAsset = "selected";
        } else {
            $selectAsset = "";
        }
        $resp .= '<option value="' . $value2['id'] . '" ' . $selectAsset . ' >' . $value2['name'] . '</option>';
    }
    $resp .= '</select>
                <span style="float: right;color:red;">*</span>
                </div>
                </div>';

    return $resp;
}

function DASH_GetEvent_ViewData($selectedDataId, $db)
{

    global $db;
    $db = db_connect();
    $sqlevent = "select * from " . $GLOBALS['PREFIX'] . "report.FilterReport f inner join " . $GLOBALS['PREFIX'] . "report.FilterReportDtl fd on f.reportid=fd.reportId where f.reportid='$selectedDataId'";

    $eventRes = find_many($sqlevent, $db);

    $recordList = [];

    if (safe_count($eventRes) > 0) {

        $serialNum = 1;
        foreach ($eventRes as $key => $value) {
            $reportid = $value['reportid'];
            $reportname = $value['reportname'];
            $reportModule = $value['reportModule'];
            $emaillist = $value['emaillist'];
            $status = $value['status'];
            $reportSchedule = $value['reportSchedule'];
            $path = $value['filePath'];
            $filename = $value['filename'];
            $crtd = $value['createdrp'];
            $crtd = $value['created'];
            $scope = $value['scope'];
            $parentVal = UTIL_GetTrimmedGroupName($value['parentVal']);
            $scopeVal = UTIL_GetTrimmedGroupName($value['scopeVal']);
            $created = date('m/d/Y H:i:s', $crtd);
            if ($status == '1') {
                $status = "Active";
            } else {
                $status = "In-Active";
            }
            if ($scope == "Sites") {
                $scopeDtls = $scopeVal;
            } else if ($scope == "ServiceTag") {
                $scopeDtls = $parentVal . ":" . $scopeVal;
            } else if ($scope == "Groups") {
                if ($parentVal == '') {
                    $scopeDtls = $scopeVal;
                } else {

                    $scopeDtls = $parentVal . ":" . $scopeVal;
                }
            }
            $link = "<a style='color:#48b2e4;' href='$path" . '/' . "$filename'>Download</a>";
            $repotName = htmlentities(substr($reportname, 0, 18)) . '...';
            $reportname = '<p class="ellipsis" id="' . $reportname . '" value="' . $reportname . '" title="' . $reportname . '">' . $repotName . '</p>';
            $emaillist = '<p class="ellipsis" id="' . $emaillist . '" value="' . $emaillist . '" title="' . $emaillist . '">' . $emaillist . '</p>';
            $reportModule = '<p class="ellipsis" id="' . $reportModule . '" value="' . $reportModule . '" title="' . $reportModule . '">' . $reportModule . '</p>';
            $status = '<p class="ellipsis" id="' . $status . '" value="' . $status . '" title="' . $status . '">' . $status . '</p>';
            $created = '<p class="ellipsis" id="' . $created . '" value="' . $created . '" title="' . $created . '">' . $created . '</p>';
            $reportSchedule = '<p class="ellipsis" id="' . $reportSchedule . '" value="' . $reportSchedule . '" title="' . $reportSchedule . '">' . $reportSchedule . '</p>';
            $scopeDtls = '<p class="ellipsis" id="' . $scopeDtls . '" value="' . $scopeDtls . '" title="' . $scopeDtls . '">' . $scopeDtls . '</p>';
            $download = '<p class="ellipsis" id="' . $link . '" value="' . $link . '" title="Download" >' . $link . '</p>';

            $recordListData[] = array("DT_RowId" => $reportid, $reportname, $emaillist, $reportModule, $status, $reportSchedule, $scopeDtls, $created, $download);
        }
    } else {

        $noRecord1 = "No data available in table";
        $noRecord = "";
        $recordListData[] = array("DT_RowId" => $noRecord, $noRecord, $noRecord, $noRecord, $noRecord, $noRecord1, $noRecord, $noRecord, $noRecord);
    }

    return $recordListData;
}

function DASH_addQueryData($data, $db)
{
    $report_name = str_replace(" ", "_", $data['report_name']);
    $userName = $data['userName'];
    $dart_num = $data['dart_num'];
    $report_type = $data['report_type'];
    $report_duration = $data['report_duration'];
    $report_email = $data['report_email'];
    $scrp_num = $data['scrp_num'];
    $created = time();
    $modified = time();

    if ($report_type == "Event") {
        if ($dart_num != "null") {

            $searchid = $data['dart_num'];
            $reportOn = "Dart";
        } else if ($scrp_num != "null") {
            $searchid = $data['scrp_num'];
            $reportOn = "Filter";
        }
        $reportModule = 'Event';

        $emailSub = "Event Report";
        $emailbody = "Hi " . $userName;
    } elseif ($report_type == "Asset") {

        $searchid = $data['assSearch_num'];
        $emailSub = "Asset Report";
        $emailbody = "Hi " . $userName;
        $reportOn = "Filter";
        $reportModule = "Asset";
    }

    $email_from = "noreply@nanoheal.com";

    if ($data['searchType'] == 'Sites') {
        $scope = "Sites";
        $parent = "";
        $scopeVal = $data['searchValue'];
    } elseif ($data['searchType'] == 'ServiceTag') {
        $scope = "ServiceTag";
        $parent = $data['rparentName'];
        $scopeVal = $data['searchValue'];
    } elseif ($data['searchType'] == 'Groups') {
        $scope = "Groups";
        $parent = "";
        $scopeVal = $data['rparentName'];
    }

    if ($report_duration == '1') {

        $reportSchedule = "Daily";
        $dayshour = $data['dayshour'];
        $days_min = $data['days_min'];

        $start = date('Y-m-d', time()) . ' 00:00:00';

        $startDate = date('Y-m-d H:i', strtotime('+' . $dayshour . ' hour +' . $days_min . ' minutes', strtotime($start)));
        $timeCre = strtotime($startDate);

        $currntTime = time();
        if ($currntTime < $timeCre) {

            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        } else {

            $startDate = date('Y-m-d H:i', strtotime('+1 days +' . $dayshour . ' hour +' . $days_min . ' minutes', strtotime($start)));
            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        }

        $timeCre = $dayshour . ":" . $days_min;
        $reportDay = "1";
    } else if ($report_duration == '7') {
        $start = date('Y-m-d', time()) . ' 00:00:00';
        $week_hour = $data['week_hour'];
        $week_min = $data['week_min'];
        $reportSchedule = "Weekly";
        $weekdayNum = $data['weekday'];
        $weekDays = array("1" => "Sunday", "2" => "Monday", "3" => "Tuesday", "4" => "Wednesday", "5" => "Thursday", "6" => "Friday", "7" => "Saturday");

        $Usrday = $weekDays[$weekdayNum];

        $jd = cal_to_jd(CAL_GREGORIAN, date("m"), date("d"), date("Y"));
        $today = (jddayofweek($jd, 1));
        $keyDays = array("Sunday" => "1", "Monday" => "2", "Tuesday" => "3", "Wednesday" => "4", "Thursday" => "5", "Friday" => "6", "Saturday" => "7");
        $keyVal = $keyDays[$Usrday];
        $TodayWeek = $keyDays[$today];

        if ($weekdayNum > $TodayWeek) {
            $noadd = $weekdayNum - $TodayWeek;
            $startDate = date('Y-m-d H:i', strtotime('+' . $noadd . ' days +' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        } else if ($weekdayNum < $TodayWeek) {
            $noaddCal = 7 - $weekdayNum;
            $noadd = $noaddCal + 1;
            $startDate = date('Y-m-d H:i', strtotime('+' . $noadd . ' days +' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        } else if ($weekdayNum == $TodayWeek) {
            $start = date('Y-m-d', time()) . ' 00:00:00';
            $startDate = date('Y-m-d H:i', strtotime('+' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
            $timeCre = strtotime($startDate);
            $currntTime = time();
            if ($currntTime < $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime > $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+7 days +' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime == $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            }
        }

        $timeCre = $week_hour . ":" . $week_min;
        $reportDay = $weekdayNum;
    } else if ($report_duration == '30') {
        $start = date('Y-m-d', time()) . ' 00:00:00';
        $reportSchedule = "Monthly";
        $month_min = $data['month_min'];
        $month_day = $data['month_day'];
        $month_hour = $data['month_hour'];
        $strtime = "$month_day days $month_hour hours $month_min minutes";
        $timeCre = strtotime($strtime);
        $noDays = date("t");
        $tday = date("d");

        if ($month_day < $tday) {
            $daydiff = $tday - $month_day;
            $startDate = date('Y-m-d', strtotime('-' . $daydiff . ' days')) . ' 00:00:00';
            $startDate1 = date('Y-m-d H:i', strtotime('+1 month +' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($startDate)));
            $nxtRunDate = $startDate1;
            $nxtRunTme = strtotime($startDate1);
        } elseif ($month_day > $tday) {
            $start = date('Y-m-d', time()) . ' 00:00:00';
            $daydiff = $month_day - $tday;
            $startDate = date('Y-m-d', strtotime('+' . $daydiff . ' days')) . ' 00:00:00';
            $startDate1 = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($startDate)));
            $nxtRunDate = $startDate1;
            $nxtRunTme = strtotime($startDate1);
        } else if ($month_day == $tday) {
            $start = date('Y-m-d', time()) . ' 00:00:00';
            $startDate = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
            $timeCre = strtotime($startDate);
            $currntTime = time();
            if ($currntTime < $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime > $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+1 month +' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime == $timeCre) {

                $startDate = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            }

            $timeCre = $month_hour . ":" . $month_min;
            $reportDay = $month_day;
        }
    }

    $sqlVeridy = "select reportname from " . $GLOBALS['PREFIX'] . "report.FilterReport where reportname='$report_name'";
    $sqlqry = find_one($sqlVeridy, $db);
    $exicnt = safe_count($sqlqry);
    if ($exicnt > 0) {
        $res = "exist";
    } else {
        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "report.FilterReport (`reportname`, `username`, `global`, `emaillist`, `emailbodytext`, `emailsubject`, `emailfrom`, `createdrp`, `modified`, `status`, `reportuniq`, `scope`, `parentVal`, `scopeVal`, "
            . "`reportModule`, `reportOn`, `searchId`, `lastRunTime`, `nextRunTime`, `nextRunDate`, `reportRange`, `reportSchedule`, `reportDay`, `reportTime`) "
            . "VALUES ('$report_name', '$userName', 0, '$report_email', '$emailbody', '$emailSub', '$email_from', '$created', '$modified', '1', '', '$scope', '$parent', '$scopeVal', '$report_type', '$reportOn', '$searchid', '$lstRunTime', '$nxtRunTme', '$nxtRunDate', '$report_duration', '$reportSchedule','$reportDay','$timeCre')";

        $res = redcommand($sql, $db);

        if ($res) {
            $res = "success";
        } else {
            $res = "failed";
        }
    }

    return $res;
}

function DASH_editQueryData($data, $db)
{
    $report_name = str_replace(" ", "_", $data['report_name']);
    $userName = $data['userName'];
    $dart_num = $data['dart_num'];
    $report_type = $data['report_type'];
    $report_duration = $data['report_duration'];
    $report_email = $data['report_email'];
    $scrp_num = $data['scrp_num'];
    $editReportID = $data['editReportID'];
    $Editreport_Status = $data['Editreport_Status'];
    $created = date();
    $modified = date();

    if ($report_type == "Event") {
        $emailbody = "Hi " . $userName;
        if ($scrp_num == "null") {
            $reportOn = "Dart";
            $report = "Event Report";
            $searchid = $data['dart_num'];
        } else if ($dart_num == "null") {
            $reportOn = "Filter";
            $report = "Dart";
            $searchid = $data['scrp_num'];
        }
    } elseif ($report_type == "Asset") {
        $emailbody = "Hi " . $userName;
        $reportOn = "Filter";
        $report = "Filter";
        $searchid = $data['assSearch_num'];
    }

    if ($data['searchType'] == 'Sites') {
        $scope = "Sites";
        $parent = "";
        $scopeVal = $data['searchValue'];
    } elseif ($data['searchType'] == 'ServiceTag') {
        $scope = "ServiceTag";
        $parent = $data['rparentName'];
        $scopeVal = $data['searchValue'];
    } elseif ($data['searchType'] == 'Groups') {
        $scope = "Groups";
        $parent = "";
        $scopeVal = $data['rparentName'];
    }

    if ($report_duration == '1') {

        $reportSchedule = "Daily";
        $dayshour = $data['dayshour'];
        $days_min = $data['days_min'];

        $start = date('Y-m-d', time()) . ' 00:00:00';

        $startDate = date('Y-m-d H:i', strtotime('+' . $dayshour . ' hour +' . $days_min . ' minutes', strtotime($start)));
        $timeCre = strtotime($startDate);

        $currntTime = time();
        if ($currntTime < $timeCre) {

            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        } else {

            $startDate = date('Y-m-d H:i', strtotime('+1 days +' . $dayshour . ' hour +' . $days_min . ' minutes', strtotime($start)));
            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        }

        $timeCre = $dayshour . ":" . $days_min;
        $reportDay = "1";
    } else if ($report_duration == '7') {

        $start = date('Y-m-d', time()) . ' 00:00:00';
        $week_hour = $data['week_hour'];
        $week_min = $data['week_min'];
        $reportSchedule = "Weekly";
        $weekdayNum = $data['weekday'];
        $weekDays = array("1" => "Sunday", "2" => "Monday", "3" => "Tuesday", "4" => "Wednesday", "5" => "Thursday", "6" => "Friday", "7" => "Saturday");

        $Usrday = $weekDays[$weekdayNum];

        $jd = cal_to_jd(CAL_GREGORIAN, date("m"), date("d"), date("Y"));
        $today = (jddayofweek($jd, 1));
        $keyDays = array("Sunday" => "1", "Monday" => "2", "Tuesday" => "3", "Wednesday" => "4", "Thursday" => "5", "Friday" => "6", "Saturday" => "7");
        $keyVal = $keyDays[$Usrday];
        $TodayWeek = $keyDays[$today];

        if ($weekdayNum > $TodayWeek) {
            $noadd = $weekdayNum - $TodayWeek;
            $startDate = date('Y-m-d H:i', strtotime('+' . $noadd . ' days +' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        } else if ($weekdayNum < $TodayWeek) {
            $noaddCal = 7 - $weekdayNum;
            $noadd = $noaddCal + 1;
            $startDate = date('Y-m-d H:i', strtotime('+' . $noadd . ' days +' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
            $nxtRunDate = $startDate;
            $nxtRunTme = strtotime($nxtRunDate);
        } else if ($weekdayNum == $TodayWeek) {
            $start = date('Y-m-d', time()) . ' 00:00:00';
            $startDate = date('Y-m-d H:i', strtotime('+' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
            $timeCre = strtotime($startDate);
            $currntTime = time();
            if ($currntTime < $timeCre) {
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime > $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+1 days +' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime == $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+' . $week_hour . ' hour +' . $week_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            }
        }

        $timeCre = $week_hour . ":" . $week_min;
        $reportDay = $weekdayNum;
    } else if ($report_duration == '30') {
        $start = date('Y-m-d', time()) . ' 00:00:00';
        $reportSchedule = "Monthly";
        $month_min = $data['month_min'];
        $month_day = $data['month_day'];
        $month_hour = $data['month_hour'];
        $strtime = "$month_day days $month_hour hours $month_min minutes";
        $timeCre = strtotime($strtime);
        $noDays = date("t");
        $tday = date("d");

        if ($month_day < $tday) {
            $daydiff = $tday - $month_day;
            $startDate = date('Y-m-d', strtotime('-' . $daydiff . ' days')) . ' 00:00:00';
            $startDate1 = date('Y-m-d H:i', strtotime('+1 month +' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($startDate)));
            $nxtRunDate = $startDate1;
            $nxtRunTme = strtotime($startDate1);
        } elseif ($month_day > $tday) {
            $start = date('Y-m-d', time()) . ' 00:00:00';
            $daydiff = $month_day - $tday;
            $startDate = date('Y-m-d', strtotime('+' . $daydiff . ' days')) . ' 00:00:00';
            $startDate1 = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($startDate)));
            $nxtRunDate = $startDate1;
            $nxtRunTme = strtotime($startDate1);
        } else if ($month_day == $tday) {
            $start = date('Y-m-d', time()) . ' 00:00:00';
            $startDate = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
            $timeCre = strtotime($startDate);
            $currntTime = time();
            if ($currntTime < $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime > $timeCre) {
                $startDate = date('Y-m-d H:i', strtotime('+1 month +' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            } else if ($currntTime == $timeCre) {

                $startDate = date('Y-m-d H:i', strtotime('+' . $month_hour . ' hour +' . $month_min . ' minutes', strtotime($start)));
                $nxtRunDate = $startDate;
                $nxtRunTme = strtotime($nxtRunDate);
            }

            $timeCre = $month_hour . ":" . $month_min;
            $reportDay = $month_day;
        }

        $timeCre = $month_hour . ":" . $month_min;
        $reportDay = $month_day;
    }

    $sqlVeridy = "select reportname from " . $GLOBALS['PREFIX'] . "report.FilterReport where reportname='$report_name' and reportid !='$editReportID'";

    $sqlqry = find_one($sqlVeridy, $db);
    $exicnt = safe_count($sqlqry);
    if ($exicnt > 0) {
        $res = "exist";
    } else {

        $sql = "Update IGNORE " . $GLOBALS['PREFIX'] . "report.FilterReport set reportname='$report_name', username='$userName', global='0', emaillist='$report_email', emailbodytext='$emailbody',emailsubject='$report',emailfrom='', modified='$nxtRunTme',status='$Editreport_Status', reportuniq='', scope='$scope',"
            . "parentVal='$parent',scopeVal='$scopeVal', "
            . "reportModule='$report_type',reportOn='$reportOn',searchId='$searchid', nextRunTime='$nxtRunTme',nextRunDate='$nxtRunDate', reportRange='$report_duration', reportSchedule='$reportSchedule',reportDay='$reportDay',reportTime='$timeCre' where reportid='$editReportID'";

        $res = redcommand($sql, $db);
        if ($res) {
            $res = "success";
        } else {
            $res = "failed";
        }
    }

    return $res;
}

function DASH_GetSiteCapacityInfo($key, $db, $site)
{

    $key = DASH_ValidateKey($key);
    $cap_res = [];
    if ($key) {
        if (is_array($site)) {
            $sitelist = '';
            foreach ($site as $value) {
                $sitelist .= "'" . $value . "',";
            }
            $site = rtrim($sitelist, ',');
            $cap_sql = "select C.site,C.host as machine,A.hostName,D.cpuState,D.cpuUsage,D.ramUsage,D.hardDiskUsage,D.bateryState,A.value3 from " . $GLOBALS['PREFIX'] . "core.Census C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
                . "on C.site=D.siteName and C.host=D.machine WHERE C.site in (" . rtrim($sitelist, ",") . ") group by C.site,C.host";
        } else {
            $cap_sql = "select C.site,C.host,A.hostName as machine,D.cpuState,D.cpuUsage,D.ramUsage,D.hardDiskUsage,D.bateryState,A.value3 from " . $GLOBALS['PREFIX'] . "core.Census C "
                . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
                . "on C.site=D.siteName and C.host=D.machine WHERE C.site = '$site' group by C.site,C.host";
        }

        if ($dbusage == 1 || $dbusage == '1') {
            $tableName = 'capacitydetails';
            $source = '"cpuState","cpuUsage","ramUsage","hardDiskUsage","bateryState","machine"';
            $condition = array("siteName" => $site);
            $cap_res = getDataFromEL($tableName, $source, $condition);
        } else {
            $cap_res = find_many($cap_sql, $db);
        }
    } else {
        echo "Your key has been expired";
    }
    return $cap_res;
}

function DASH_GetAssetDataTableName($key, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
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
    } else {
        echo "Your key has been expired";
    }
}

function DASH_GetSoftInfoGridData($key, $db, $scope, $softwarenames)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $_SESSION['AdhocType'] = 'SOFTINFO';
        $fields = ':Installed Software Names:Version:Installation Date:User Name:Machine Name:';
        $assetArray = DASH_GetAssetDataTableName($key, $db);
        $machineTable = $assetArray['machine'];
        $assetTable = $assetArray['asset'];
        $datanames = array('Installed Software Names');
        $dataid = DASH_GetAssetDataId($db, $datanames, 2);
        $dataids = array_values($dataid);
        $sql = "select M.machineid
                from " . $GLOBALS['PREFIX'] . "asset.$machineTable M
                join " . $GLOBALS['PREFIX'] . "core.Census C
                on M.host = C.host and M.cust = C.site
                where $scope";
        $result = find_many($sql, $db);
        $mids = array();
        foreach ($result as $value) {
            $mids[] = $value['machineid'];
        }
        $i = 1;
        $grphdata = array();
        $drilldowndata = array();
        $result = array();
        $i = 0;
        foreach ($softwarenames as $value) {
            $terms = [];
            $terms[] = array('dataid' => $dataid['Installed Software Names'], 'comparison' => 3, 'value' => $value, 'block' => $i, 'groups' => $dataid['group'], 'ordinal' => 1);
            $response = run_adhoc_query($db, $mids, $terms, $fields, 0, 0, 'info');
            $columns = $response['columns'];
            $software_name = $value;
            foreach ($response['rows'] as $row) {
                $machinename = current($row[$columns['Machine Name']]);
                $result[$i][] = '<p class="ellipsis" title="' . $machinename . '" >' . $machinename . '</p>';
                $username = current($row[$columns['User Name']]);
                $result[$i][] = '<p class="ellipsis" title="' . $username . '" >' . $username . '</p>';
                $result[$i][] = '<p class="ellipsis" title="' . current($row[$columns['Installed Software Names']]) . '" >' . current($row[$columns['Installed Software Names']]) . '</p>';
                $installation_date = current($row[$columns['Installation Date']]);
                $result[$i][] = '<p class="ellipsis" title="' . $installation_date . '" >' . $installation_date . '</p>';
                $version = current($row[$columns['Version']]);
                $result[$i][] = '<p class="ellipsis" title="' . $version . '" >' . $version . '</p>';
                $i++;
            }
        }
        return $result;
    } else {
        echo "Your key has been expired";
    }
}

function DASH_GetOwner($key, $db)
{
    $str = '';
    $sql = "SELECT  userid,username FROM " . $GLOBALS['PREFIX'] . "core.Users";
    $res = find_many($sql, $db);

    return $res;
}

function DASH_GetSiteCapacityInfoMachine($key, $db, $machine, $siteVal)
{
    $key = DASH_ValidateKey($key);
    $dbusage = $_SESSION["user"]["usage"];
    if ($key) {
        $cap_sql = "select C.site,C.host as machine,A.hostName,D.cpuState,D.cpuUsage,D.ramUsage,D.hardDiskUsage,D.bateryState,A.value3 from " . $GLOBALS['PREFIX'] . "core.Census C "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
            . "on C.site=D.siteName and C.host=D.machine WHERE C.host = '$machine' and C.site in ($siteVal) group by C.site,C.host";

        if ($dbusage == 1 || $dbusage == '1') {
            $tableName = 'capacitydetails';
            $source = '"cpuState","cpuUsage","ramUsage","hardDiskUsage","bateryState","machine"';
            $condition = array("machine" => $machine, "siteName" => $siteVal);
            $cap_res = getDataFromEL($tableName, $source, $condition);
        } else {
            $cap_res = find_many($cap_sql, $db);
        }
    } else {
        echo "Your key has been expired";
    }
    return $cap_res;
}

function DASH_GetSiteCapacityInfoGroup($key, $db, $machineArray)
{
    $key = DASH_ValidateKey($key);
    $hostid = '';
    if ($key) {
        $censusArray = safe_array_keys($machineArray);
        foreach ($censusArray as $key => $value) {
            $hostid .= "'" . $value . "',";
        }

        $cap_sql = "select C.site,C.host as machine,A.hostName,D.cpuState,D.cpuUsage,D.ramUsage,D.hardDiskUsage,D.bateryState,A.value3 from " . $GLOBALS['PREFIX'] . "core.Census C "
            . "left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN  " . $GLOBALS['PREFIX'] . "event.capacityDetails D "
            . "on C.site=D.siteName and C.host=D.machine WHERE C.id in(" . rtrim($hostid, ",") . ") group by C.site,C.host";
        if ($dbusage == 1 || $dbusage == '1') {
            foreach ($machineArray as $key => $value) {
                $machine .= "'" . $value . "',";
            }
            $machine = rtrim($machine, ',');
            $tableName = 'capacitydetails';
            $source = '"cpuState","cpuUsage","ramUsage","hardDiskUsage","bateryState","machine"';
            $condition = array("machine" => $machine);
            $cap_res = getDataFromEL($tableName, $source, $condition);
        } else {
            $cap_res = find_many($cap_sql, $db);
        }
    } else {
        echo "Your key has been expired";
    }
    return $cap_res;
}

function DASH_GetMirrorSiteInfo($key, $db, $dataScope)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.EventsMirror where customer='$dataScope'";
        $result = find_many($sql, $db);
        $count = safe_count($result);
        if ($count > '0') {
            foreach ($result as $key => $value) {
                $machineNames = $value['machine'];
                $machinetext4Data = $value['text4'];
                $siteMirroData[] = array("Machine Name" => $machineNames, "machinetext4Data" => $machinetext4Data);
            }

            return $siteMirroData;
        }
    } else {
        echo "Your key has been expired";
    }
}

function DASH_GetMirrorMachineInfo($key, $db, $dataScope)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.EventsMirror where machine='$dataScope'";
        $result = find_many($sql, $db);
        $count = safe_count($result);
        if ($count > '0') {
            foreach ($result as $key => $value) {
                $machineNames = $value['machine'];
                $machinetext4Data = $value['text4'];
                $siteMirroData[] = array("Machine Name" => $machineNames, "machinetext4Data" => $machinetext4Data);
            }
            return $siteMirroData;
        }
    } else {
        echo "Your key has been expired";
    }
}

function DASH_GetMirrorGroupInfo($key, $db, $machines)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        foreach ($machines as $key => $values) {
            $machineStr .= "'" . $values . "',";
        }
        $machineStr = rtrim($machineStr, ',');
        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.EventsMirror where machine in($machineStr) group by machine";

        $result = find_many($sql, $db);
        $count = safe_count($result);
        if ($count > '0') {
            foreach ($result as $key => $value) {
                $machineNames = $value['machine'];
                $machinetext4Data = $value['text4'];
                $siteMirroData[] = array("Machine Name" => $machineNames, "machinetext4Data" => $machinetext4Data);
            }
            return $siteMirroData;
        }
    } else {
        echo "Your key has been expired";
    }
}

function DASH_CreatPTag($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == null || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}

function DASH_GetMachinesSites_new($key, $pdo, $site, $host)
{
    $machine = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        if (is_array($site)) {
            foreach ($site as $value) {
                $sitelist[] = $value;
            }
            $sites_in = str_repeat('?,', safe_count($sitelist) - 1) . '?';
            $machinesql = "select host,id from " . $GLOBALS['PREFIX'] . "core.Census where site in ($sites_in) and lower(host) like lower(?)";
            $machinestmt = $pdo->prepare($machinesql);
            $params = array_merge($sitelist, ["%$host%"]);
            $machinestmt->execute($params);
        } else {
            $machinesql = "select host,id from " . $GLOBALS['PREFIX'] . "core.Census where site = ? and lower(host) like lower(?)";
            $machinestmt = $pdo->prepare($machinesql);
            $machinestmt->execute([$site, "%$host%"]);
        }
        $machineres = $machinestmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($machineres as $key => $val) {
            $machine[$val['id']] = $val['host'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $machine;
}

function DASH_GetGroupsMachines_new($key, $pdo, $groupID, $limit = 0)
{
    $username = $_SESSION['user']['username'];
    $machine = [];
    $key = DASH_ValidateKey($key);

    $wh = '';
    if ($limit != 0) {
        $start = $limit == 100 ? 0 : $limit;
        $wh = 'limit ' . $start . ',100';
    }

    if ($key) {
        if (is_array($groupID)) {
            $in = str_repeat('?,', safe_count($groupID) - 1) . '?';
            $machinesql = "select C.host,C.id from " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.Customers as U, "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G where "
                . "M.censusuniq = C.censusuniq and M.mgroupuniq = G.mgroupuniq "
                . "and G.mgroupid IN ($in) and U.customer = C.site and U.username = ? $wh";
            $machineres = $pdo->prepare($machinesql);
            $params = array_merge($groupID, [$username]);
            $machineres->execute($params);
        } else {
            $machinesql = "select C.host,C.id from " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.Customers as U, "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G where "
                . "M.censusuniq = C.censusuniq and M.mgroupuniq = G.mgroupuniq "
                . "and (G.mgroupid = ? or G.name = ?) and U.customer = C.site and "
                . "U.username = ? $wh";
            $machinesql = $pdo->prepare($machinesql);
            $machinesql->execute([$groupID, $groupID, $username]);
        }

        $machineres = $machinesql->fetchAll();
        foreach ($machineres as $key => $val) {
            $machine[$val['id']] = $val['host'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $machine;
}

function DASH_getNewLiForSearchMachine($pdo, $siteName, $hostVal)
{
    $sql = $pdo->prepare("select id,host,site from " . $GLOBALS['PREFIX'] . "core.Census where lower(site) = ? and lower(host) like ?");
    $sql->execute([strtolower($siteName), strtolower("%$hostVal%")]);
    $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);

    return $sqlRes;
}

function DASH_GetGroupEventList_EL($key, $db, $machines, $time1, $time2)
{
    global $API_enable_Event;
    $key = DASH_ValidateKey($key);

    if ($key) {
        if ($API_enable_Event == 1) {
            foreach ($machines as $row) {
                $sitefilter .= '{ "term": { "machine": "' . $row . '" }},';
            }
        } else {
            foreach ($machines as $row) {
                $sitefilter .= '{ "term": { "machine.keyword": "' . $row . '" }},';
            }
        }

        $sitefilter = rtrim($sitefilter, ',');

        $queryString = '';
        foreach ($_SESSION['AdvEventFilter'] as $key => $value) {
            if ($value != '') {
                $queryString .= '{
                            "bool": {
                              "minimum_should_match": 1,
                              "should": [{"match": {"' . $key . '": "' . $value . '"}}]
                            }
                          },';
            }
        }

        $paramsTotal = '{

                "query": {
                  "bool": {
                    "must": [
                      {
                        "bool": {
                          "minimum_should_match": 1,
                          "should": [' . $sitefilter . ']
                        }
                      },' . $queryString . '
                      {
                        "range": {
                          "entered": {
                            "gte": ' . $time2 . ',
                            "lte": ' . $time1 . ',
                            "format": "epoch_millis"
                          }
                        }
                      }
                    ]
                  }
                }
            }';

        if ($API_enable_Event == 1) {
            $fromDate = date('Y-m-d', $time2);
            $toDate = date('Y-m-d', $time1);
            $indexName = createEventIndex($fromDate, $toDate);
        } else {
            $indexName = 'event';
        }

        $tempResTotal = EL_GetCurlRecordsCount($indexName, $paramsTotal);
        $totalArray = safe_json_decode($tempResTotal, true);

        $totalCount = isset($totalArray['count']) ? $totalArray['count'] : 0;
        getObjectExcelnew();

        if ($totalCount > 0) {
            $params = '{
                "from" : 0, "size" : 2000,
                  "query": {
                    "bool": {
                      "must": [
                        {
                          "bool": {
                            "minimum_should_match": 1,
                            "should": [' . $sitefilter . ']
                          }
                        },' . $queryString . '
                        {
                          "range": {
                            "entered": {
                              "gte": ' . $time2 . ',
                              "lte": ' . $time1 . ',
                              "format": "epoch_millis"
                            }
                          }
                        }
                      ]
                    }
                  }
              }';
            $scrollresult = getAllAssets_scroll($params, $indexName);
            $response = safe_json_decode($scrollresult, true);
            while (isset($response['hits']['hits']) && safe_count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $key => $val) {
                    $result = $val['_source'];
                    $objPHPExcel = loopexportdataEvent($result);
                }

                $scroll_id = $response['_scroll_id'];
                $scrollresult = getAllAssets_scrollid($scroll_id);
                $response = safe_json_decode($scrollresult, true);

                if ($scroll_id != $response['_scroll_id']) {
                    deleteScrollId($scroll_id);
                }
            }
        }
    } else {
        echo "Your key has been expired";
    }
}

function DASH_getDataId($db, $dataName)
{

    $sql = "select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName where name ='$dataName'";
    $sqlRes = find_one($sql, $db);

    return $sqlRes['dataid'];
}

function DASH_getSiteName($db, $machine)
{

    $sql = "select site from " . $GLOBALS['PREFIX'] . "core.Census where host='$machine' order by id desc limit 1;";
    $sqlRes = find_one($sql, $db);

    if (safe_count($sqlRes) > 0) {
        return $sqlRes['site'];
    } else {
        return 0;
    }
}

function DASH_GetGetSummaryRprt_EL($db, $site, $itemId, $reportdurtn)
{
    global $elastic_url;

    $itemIds1 = [];
    $itemIds2 = [];

    $sql = "select eventitemid,name from " . $GLOBALS['PREFIX'] . "dashboard.EventItems where name not like '%win log%'";
    $itemIdRes = find_many($sql, $db);

    foreach ($itemIdRes as $value1) {
        $itemIds1[$value1['eventitemid']] = $value1['name'];
        $itemIds2[] = $value1['eventitemid'];
    }

    $sql = "select userid from " . $GLOBALS['PREFIX'] . "core.Users where user_email='admin@nanoheal.com'";
    $sqlRes = find_one($sql, $db);

    $searchType = $_SESSION['searchType'];
    $cond = '';

    if ($searchType == 'Sites') {
        if (safe_count($site) > 0) {
            $siteArr = explode(",", $site);
            foreach ($siteArr as $val) {
                $cond .= '{"term":{"site":"' . $val . '"}},';
            }
            $cond = rtrim($cond, ',');
        } else {
            $cond = '{"term":{"site":"' . $site . '"}}';
        }
    } else if ($searchType == 'ServiceTag') {
        $cond = '{"term":{"censusid":"' . $site . '"}}';
    } else {
        if (safe_count($site) > 0) {
            foreach ($site as $val) {
                $cond .= '{"term":{"host":"' . $val . '"}},';
            }
            $cond = rtrim($cond, ',');
        } else {
            $cond = '{"term":{"censusid":"' . $site . '"}}';
        }
    }

    $fromDate = date('Y-m-d', $reportdurtn[0]);
    $toDate = date('Y-m-d', $reportdurtn[1]);
    $indexName = createComplaincInd($fromDate, $toDate);
    $url = $elastic_url . $indexName . "/_search?pretty&size=10000";

    $query = '{
                "query": {
                  "bool": {
                    "must": [
                      {
                        "bool":{
                             "minimum_should_match":1,
                             "should":[' . $cond . ']
                         }
                        }
                    ],
                    "filter": [
                      { "range": { "servertime": { "gte": "' . $reportdurtn[0] . '", "lte" : "' . $reportdurtn[1] . '" }}}
                    ]
                  }
                }
              }';

    $result = curlCommonFunction($url, $query);

    $tempSummaryDetails = EL_FormatCurldata($result);

    foreach ($tempSummaryDetails as $key => $value) {
        $summaryDetails[$key]['name'] = $itemIds1[$value['itemid']];
        $summaryDetails[$key]['host'] = $value['host'];
        $summaryDetails[$key]['censusid'] = $value['censusid'];
        $summaryDetails[$key]['servertime'] = $value['servertime'];
        $summaryDetails[$key]['serverdate'] = $value['serverdate'];
        $summaryDetails[$key]['status'] = $value['status'];
    }

    return $summaryDetails;
}

function getObjectExcelnew()
{

    $header = array("Device", "Customer", "Description", "Scrip No.", "Client Time", "Server Time", "Text1", "Text2", "Text3", "Text4", "client Version");
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-Description: File Transfer');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename=EventList.csv;');
    header('Content-Transfer-Encoding: binary');

    $fpo = fopen('php://output', 'w');

    $cvsHeadings = $header;
    fputcsv($fpo, $cvsHeadings);
}

function loopexportdataEvent($value)
{
    $fp = fopen('php://output', 'a');
    if (safe_count($value) > 0) {
        $tempArray = array();
        $description = safe_addslashes(utf8_encode($value['description']));
        $executable = safe_addslashes(utf8_encode($value['executable']));
        $clienttime = date("m/d/Y H:i:s", $value['entered']);
        $servertime = date("m/d/Y H:i:s", $value['servertime']);
        $machines = $value['machine'];
        $scrip = $value['scrip'];
        $text1 = strip_tags($value['text1']);
        $text2 = strip_tags($value['text2']);
        $text3 = strip_tags($value['text3']);
        $text4 = strip_tags($value['text4']);
        $clientversion = $value['clientversion'];
        $sitename = UTIL_GetTrimmedGroupName($value['customer']);

        $tempArray = array($machines, $sitename, $description, $scrip, $clienttime, $servertime, $text1, $text2, $text3, $text4, $clientversion);
        fputcsv($fp, $tempArray);
    } else {
        $tempArray = array('No Data Available');
        fputcsv($fp, $tempArray);
    }
}

function DASH_GetCensusMachines($key, $db, $site)
{

    $sql = "select C.host from " . $GLOBALS['PREFIX'] . "core.Census C LEFT JOIN " . $GLOBALS['PREFIX'] . "asset.Machine M ON C.site=M.cust "
        . "and C.host=M.host where M.cust IS NULL and M.host IS NULL and C.site='$site'";
    $sqlRes = find_many($sql, $db);
    return $sqlRes;
}

function DASH_GetSites_PDO($key, $pdo, $user)
{
    $sites = [];
    $key = DASH_ValidateKey($key);
    if ($key) {

        $siteres = NanoDB::find_many("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? order by lower(customer)", null, [$user]);
        foreach ($siteres as $key => $val) {
            $sites[] = utf8_encode($val['name']);
        }
    } else {
        echo "Your key has been expired";
    }

    return $sites;
}

function DASH_GetGroups_PDO($key, $pdo, $user)
{

    $groups = [];
    $ch_id = $_SESSION['user']['cId'];
    $key = DASH_ValidateKey($key);
    if ($key) {

        $categorysql = $pdo->prepare("select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category=?");
        $categorysql->execute(['Wiz_SCOP_MC']);
        $categoryres = $categorysql->fetch(PDO::FETCH_ASSOC);
        $mcatid = $categoryres['mcatid'];

        $sql = $pdo->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
        $sql->execute([$ch_id]);
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);

        $sibling_users = [];
        foreach ($res as $key => $val) {
            $sibling_users[] = "'" . $val['username'] . "'";
        }
        $sibling_users = implode(",", $sibling_users);

        if ($mcatid != '') {

            $allgroupsql = $pdo->prepare("select mg.mgroupid,mg.username,mg.name,mc.mcatid,created,mg.boolstring,mg.style,mg.global from " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg join " . $GLOBALS['PREFIX'] . "core.MachineCategories as mc on mg.mcatuniq = mc.mcatuniq where mc.mcatid = ? and (username = ? or (global = 1 and username in (?)))");
            $allgroupsql->execute([$mcatid, $user, $sibling_users]);
            $groupres = $allgroupsql->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return array();
        }
        foreach ($groupres as $key => $val) {
            $txt = utf8_decode(trim($val['name']));
            $groups[$txt] = $val['mgroupid'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $groups;
}

function DASH_GetGroupsMachines_PDO($key, $db, $groupID, $limit = 0)
{

    $machine = [];
    $key = DASH_ValidateKey($key);

    $wh = '';
    if ($limit != 0) {
        $start = $limit == 100 ? 0 : $limit;
        $wh = 'limit ' . $start . ',100';
    }

    if ($key) {
        if (is_array($groupID)) {
            $groupIdlist = implode(",", $groupID);

            $machinesql = "select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           M.mgroupid in ($groupIdlist) and style>=2
                           group by C.host $wh";
        } else {
            $machinesql = "select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq
                           and GP.mgroupuniq = M.mgroupuniq and
                           (M.mgroupid = '$groupID' or name='$groupID') and style>=2
                           group by C.host $wh";
        }


        $machineres = NanoDB::find_many($machinesql, null, []);

        foreach ($machineres as $key => $val) {
            $machine[$val['id']] = $val['host'];
        }
    } else {
        echo "Your key has been expired";
    }
    return $machine;
}
