<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';


function getmgroupId($searchValue)
{
    $pdo = pdo_connect();
    try {
        $adminId = $_SESSION["user"]["adminid"];
        $ostmt = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "dashboard.LeftPane where name in(?) and userid =? and itemtype=2 limit 1");
        $ostmt->execute([$searchValue, $adminId]);
        $ores = $ostmt->fetch(PDO::FETCH_ASSOC);
        return $ores['mgroupid'];
    } catch (Exception $exception) {
        logs::log(__FILE__, __LINE__, $exception, 0);
        return null;
    }
}


function groupsMachineData($searchValue)
{
    $pdo = pdo_connect();
    try {
        $sqlGroups = $pdo->prepare("select mcatuniq,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=? limit 1");
        $sqlGroups->execute([$searchValue]);
        $resultGroups = $sqlGroups->fetch(PDO::FETCH_ASSOC);

        $sqlMachines = $pdo->prepare("select C.host from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,Census as C where GP.censusuniq = C.censusuniq and mcatuniq = ? and mgroupuniq = ?");
        $sqlMachines->execute([$resultGroups['mcatuniq'], $resultGroups['mgroupuniq']]);
        $grouomachineRes = $sqlGroups->fetchAll(PDO::FETCH_ASSOC);
        $machine = array();
        if (safe_count($grouomachineRes) > 0) {
            foreach ($grouomachineRes as $row) {
                $machine[] = "'" . $row['host'] . "'";
            }
        }

        $searchValues = implode(",", $machine);
        return $searchValues;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return NULL;
    }
}


function getViewId($db)
{
    $pdo = pdo_connect();
    $userid = $_SESSION['user']['adminid'];

    $sqlGroupId = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "dashboard.ViewMachineGroups where viewid IN (select viewid from " . $GLOBALS['PREFIX'] . "dashboard.ViewUsers where userid=?)");
    $sqlGroupId->execute([$userid]);
    $res = $sqlGroupId->fetchAll(PDO::FETCH_ASSOC);
    $mgroupid = "";
    foreach ($res as $key => $value) {
        $mgroupid .= $value['mgroupid'] . ',';
    }
    $mgroupid = rtrim($mgroupid, ',');
    return $mgroupid;
}


function SitesMachineData($searchValue)
{
    $pdo = pdo_connect();
    try {
        $sqlGroups = $pdo->prepare("select mcatuniq,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=? limit 1");
        $sqlGroups->execute([$searchValue]);
        $resultGroups = $sqlGroups->fetch($sqlGroups);

        $sqlMachines = $pdo->prepare("select C.host from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as GP,Census as C where GP.censusuniq = C.censusuniq and mcatuniq = '{$resultGroups['mcatuniq']}' and mgroupuniq = '{$resultGroups['mgroupuniq']}'");
        $sqlMachines->execute();
        $grouomachineRes = $sqlMachines->fetchAll($sqlMachines);
        $machine = array();
        if (safe_count($grouomachineRes) > 0) {
            foreach ($grouomachineRes as $row) {
                $machine[] = "'" . $row['host'] . "'";
            }
        }

        $searchValues = implode(",", $machine);
        return $searchValues;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return NULL;
    }
}


function getSiteMachinesData($searchValue)
{
    $pdo = pdo_connect();
    $sitemachine = '';
    try {
        $sitesql = $pdo->prepare("select host from " . $GLOBALS['PREFIX'] . "core.Census where site IN (?)");
        $sitesql->execute([$searchValue]);
        $siteres = $sitesql->fetchAll($sitesql);

        foreach ($siteres as $value) {
            if ($sitemachine === '') {
                $sitemachine = $value['host'];
            } else {
                $sitemachine .= "," . $value['host'];
            }
        }

        $sitemachineData = rtrim($sitemachine, ',');
        return $sitemachineData;
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return 'Error : ' . $ex;
    }
}


function getGroupMachinesData($searchValue)
{
    $pdo = pdo_connect();
    try {
        $sqlGroups = $pdo->prepare("select mcatuniq,mgroupuniq from MachineGroups where name=? limit 1");
        $sqlGroups->execute([$searchValue]);
        $resultGroups = $sqlGroups->fetch($sqlGroups);

        $sqlMachines = $pdo->prepare("select C.host from MachineGroupMap as GP,Census as C where GP.censusuniq = C.censusuniq and mcatuniq = '{$resultGroups['mcatuniq']}' and mgroupuniq = '{$resultGroups['mgroupuniq']}'");
        $sqlMachines->execute();
        $groupmachineRes = $sqlMachines->fetchAll($sqlMachines);

        $groupmachine = '';

        foreach ($groupmachineRes as $value) {
            if ($groupmachine === '') {
                $groupmachine = $value['host'];
            } else {
                $groupmachine .= "," . $value['host'];
            }
        }

        $groupmachineData = rtrim($groupmachine, ',');
        return $groupmachineData;
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        return 'Error : ' . $ex;
    }
}
