<?php




global $db;
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';





function GetMachinesSites($db, $site)
{

    $machine = [];
    if (is_array($site)) {
        $sitelist = '';
        foreach ($site as $value) {
            $sitelist .= "'" . $value . "',";
        }
        $machinesql = "select host,id from "
            . $GLOBALS['PREFIX'] . "core.Census where site in (" . rtrim($sitelist, ",") . ")";
    } else {
        $machinesql = "select host,id from "
            . $GLOBALS['PREFIX'] . "core.Census where site = '$site'";
    }
    $machineres = find_many($machinesql, $db);
    foreach ($machineres as $key => $val) {
        $machine[$val['id']] = $val['host'];
    }

    return $machine;
}



function GetGroupsMachines($db, $groupID)
{

    $machine = [];
    if (is_array($groupID)) {
        $groupIdlist = implode(",", $groupID);

        $machinesql = "select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP, 
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where 
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq 
                           and GP.mgroupuniq = M.mgroupuniq and 
                           M.mgroupid in ($groupIdlist) and style>=2 
                           group by C.host";
    } else {
        $machinesql = "select C.host,C.id  from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP, 
                           " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M where 
                           GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq 
                           and GP.mgroupuniq = M.mgroupuniq and 
                           M.mgroupid = $groupID and style>=2 
                           group by C.host";
    }
    $machineres = find_many($machinesql, $db);
    foreach ($machineres as $key => $val) {
        $machine[$val['id']] = $val['host'];
    }

    return $machine;
}



function GetSiteScope($db, $selectedItem, $selectedType)
{

    if ($selectedItem == 'All') {
        $user  = $_SESSION['user']['username'];
        $scope = [];
        switch ($selectedType) {
            case 'Sites':
                $scope = GetSites($db, $user);
                break;
            case 'Groups':
                $scope = GetGroups($db, $user);
                break;
            default:
                break;
        }
        return $scope;
    } else {
        return $selectedItem;
    }
}


function GetSiteScope_PDO($db, $selectedItem, $selectedType)
{

    if ($selectedItem == 'All') {
        $user  = $_SESSION['user']['username'];
        $scope = [];
        switch ($selectedType) {
            case 'Sites':
                $scope = GetSites_PDO($db, $user);
                break;
            case 'Groups':
                $scope = GetGroups_PDO($db, $user);
                break;
            default:
                break;
        }
        return $scope;
    } else {
        return $selectedItem;
    }
}



function GetSites($db, $user)
{
    $sites = [];

    $sitesql = "select customer as name from \n"
        . $GLOBALS['PREFIX'] . "core.Customers where username='$user'\n"
        . "order by lower(customer)";
    $siteres = find_many($sitesql, $db);
    foreach ($siteres as $key => $val) {
        $sites[] = $val['name'];
    }

    return $sites;
}




function GetSites_PDO($db, $user)
{
    $sites = [];
    $sitesql = "select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? order by lower(customer)";
    $pdo = $db->prepare($sitesql);
    $pdo->execute([$user]);
    $siteres = $pdo->fetchAll(PDO::FETCH_ASSOC);

    foreach ($siteres as $key => $val) {
        $sites[] = $val['name'];
    }

    return $sites;
}



function GetGroups($db, $user)
{

    $groups   = [];
    $groupsql = "select mgroupid,name from \n"
        . $GLOBALS['PREFIX'] . "core.MachineGroups where \n"
        . "username = '$user'  and style>=2 \n"
        . "and name != '' order by lower(name)";
    $groupres = find_many($groupsql, $db);
    foreach ($groupres as $key => $val) {
        $groups[$val['name']] = $val['mgroupid'];
    }

    return $groups;
}



function GetGroups_PDO($db, $user)
{

    $groups   = [];
    $groupsql = "select mgroupid,name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where username=? and style>=2 and name != '' order by lower(name)";
    $pdo = $db->prepare($groupsql);
    $pdo->execute([$user]);
    $groupres = $pdo->fetchAll(PDO::FETCH_ASSOC);

    foreach ($groupres as $key => $val) {
        $groups[$val['name']] = $val['mgroupid'];
    }

    return $groups;
}



function GetGroupName($db, $mgroupId)
{
    $groupsql = "select name from \n"
        . $GLOBALS['PREFIX'] . "core.MachineGroups \n"
        . "where mgroupid = $mgroupId ";
    $groupres = find_one($groupsql, $db);
    $group    = $groupres['name'];

    return $group;
}



function COMM_GetAuditData($resType, $db)
{

    $commRes     = [];
    $searchtype  = $_SESSION["searchType"];
    $searchValue = $_SESSION["searchValue"];
    $user        = $_SESSION["user"]["username"];

    $dataScope = GetSiteScope($db, $searchValue, $searchtype);

    if ($searchtype == 'Service Tag' || $searchtype == 'Host Name') {

        if ($resType == "softdist") {
            $commSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$searchValue' and JobType = 'Software Distribution'";
        } else {
            $commSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$searchValue' and JobType IN ('Notification','Interactive')";
        }
    } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
        $data     = GetMachinesSites($db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        if ($resType == "softdist") {
            $commSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN ($machines) and JobType = 'Software Distribution'";
        } else {
            $commSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN ($machines) and JobType IN ('Notification','Interactive')";
        }
    } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
        $dataScope = GetSiteScope($db, $searchValue, $searchtype);
        $data      = GetGroupsMachines($db, $dataScope);
        $machines  = "'" . implode("','", $data) . "'";

        if ($resType == "softdist") {
            $commSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN ($machines) and JobType = 'Software Distribution'";
        } else {
            $commSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag IN ($machines) and JobType IN ('Notification','Interactive')";
        }
    }

    $commRes = find_many($commSql, $db);

    return $commRes;
}

function getGroupMachines($searchVal, $db)
{
    $machine  = "";
    $groupsql = "select C.host host from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP, " . $GLOBALS['PREFIX'] . "core.Census as C, " . $GLOBALS['PREFIX'] . "core.MachineGroups M 
                where GP.censusuniq = C.censusuniq and GP.mcatuniq = M.mcatuniq 
                and GP.mgroupuniq = M.mgroupuniq and M.name = '$searchVal' and style>=2 group by C.host";
    $groupres = find_many($groupsql, $db);

    if (safe_count($groupres) > 0) {
        foreach ($groupres as $row) {
            if ($machine === '') {
                $machine = $row['host'];
            } else {
                $machine .= "," . $row['host'];
            }
        }
    }
    return $machine;
}

function getSiteMachines($searchVal, $db)
{
    $machine  = "";
    $sitesql = "select host host from " . $GLOBALS['PREFIX'] . "core.Census where site = '$searchVal'";
    $siteres = find_many($sitesql, $db);

    if (safe_count($siteres) > 0) {
        foreach ($siteres as $row) {
            if ($machine === '') {
                $machine = $row['host'];
            } else {
                $machine .= "," . $row['host'];
            }
        }
    }
    return $machine;
}
