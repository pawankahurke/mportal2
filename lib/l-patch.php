<?php


function PATCH_GetDetails($key, $db)
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $key = DASH_ValidateKey($key);
    if ($key) {

        $mgroupidsql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
        $mgroupidsql->execute([$searchValue]);
        $mgroupidres = $mgroupidsql->fetch();

        $mgroupid = $mgroupidres['mgroupid'];

        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.WUConfig where mgroupid = ?");
        $sql->execute([$mgroupid]);
        $rowres = $sql->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "Your key has been expired";
    }

    return $rowres;
}



function PATCH_GetInstallValue($db, $name)
{

    $key = '';
    $updatemethod = '';
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $username = $_SESSION['user']['username'];
    $rparent = $_SESSION['rparentName'];

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    switch ($searchType) {
        case 'Sites':
            $Configuresql = PATCH_GetConfigureSiteDetails($key, $db, $dataScope);
            break;
        case 'Groups':
            $Configuresql = PATCH_GetConfigureGroupDetails($key, $db, $name, $dataScope);
            break;
        case 'ServiceTag':
            $Configuresql = PATCH_GetConfigureMachineDetails($key, $db, $rparent, $searchValue);
            break;
        default:
            break;
    }
    $totalrecors = safe_count($Configuresql);
    if ($totalrecors > 0) {

        foreach ($Configuresql as $key => $value) {
            $updatemethod = $value['installation'];
        }
    }

    return $updatemethod;
}

function PATCH_GetPatchListCount($key, $db, $site, $type)
{
    $key = DASH_ValidateKey($key);
    $siteListArr = array();
    $siteArr = array();
    if ($key) {
        if ($type === 'Sites') {
            if (is_array($site)) {
                $res = safe_array_keys($site);
                $siteList = "";
                foreach ($res as $row) {
                    array_push($siteListArr, $site[$row]);
                }
                $in1 = str_repeat('?,', safe_count($siteListArr) - 1) . '?';
                $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.serverfile,p.date,ps.patchconfigid,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                    . "p.patchid = ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in1) group by patchid");
                $sql->execute($siteListArr);
            } else {
                array_push($siteArr, $site);
                $in1 = str_repeat('?,', safe_count($siteArr) - 1) . '?';
                $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                    . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in1) group by patchid");
                $sql->execute($siteArr);
            }
        } else if ($type === 'ServiceTag') {
            $sql1 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where id = ?");
            $sql1->execute([$site]);
            $result1 = $sql1->fetch(PDO::FETCH_ASSOC);
            $nameVal = $result1['host'];
            $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where host = ? group by patchid");
            $sql->execute([$nameVal]);
            $result = $sql->fetchAll();
        } else {
            foreach ($site as $row => $value) {
                array_push($machArr, $row);
            }
            $in  = str_repeat('?,', safe_count($machArr) - 1) . '?';
            $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where c.id in ($in) group by patchid");
            $sql->execute($machArr);
            $result = $sql->fetchAll();
        }
        $totcount = safe_count($sql->fetchAll(PDO::FETCH_ASSOC));
    }

    return $totcount;
}

function PATCH_GetPatchSitesList($db, $site, $limitStr = '', $notifSearch = '', $orderStr = '')
{
    if ($notifSearch == '') {
        $searchStr = '';
    } else {
        if (strtolower($notifSearch) == 'undefined') {
            $searchStr = " and  (p.type  = 0)";
        } else if (strtolower($notifSearch) == 'update') {
            $searchStr = " and  (p.type   = 1)";
        } else if (strtolower($notifSearch) == 'service pack' || strtolower($notifSearch) == 'service' || strtolower($notifSearch) == 'service pack') {
            $searchStr = " and  (p.type  = 2)";
        } else if (strtolower($notifSearch) == 'roll up' || strtolower($notifSearch) == 'rollup' || strtolower($notifSearch) == 'roll') {
            $searchStr = " and  (p.type  = 3)";
        } else if (strtolower($notifSearch) == 'security') {
            $searchStr = " and  (p.type = 4 )";
        } else if (strtolower($notifSearch) == 'critical') {
            $searchStr = " and  (p.type = 5)";
        } else {
            $searchStr = " and  (p.title LIKE '%" . $notifSearch . "%')";
        }
    }

    $siteListArr = array();
    $siteArr = array();
    array_push($siteArr, $site);
    $in1 = str_repeat('?,', safe_count($siteArr) - 1) . '?';

    //get remove patch group
    $removePatchGroupName = "Wiz_REMV_PG ".$site;
    $removePatchGroup = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name = ?", null, [$removePatchGroupName]);
    $removePatchGroupId = $removePatchGroup['pgroupid'];

    $sql = $db->prepare("select DISTINCT c.id, c.host, p.patchid, p.title, p.type, p.size, p.priority, p.date, p.serverfile, c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches as p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as ps on " . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census as c on ps.id=c.id where not exists (select * from " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap where " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.patchid = p.patchid  and " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.pgroupid = $removePatchGroupId ) and site in ($in1) $searchStr group by patchid $orderStr $limitStr");
    $sql->execute($siteArr);

  $sql2 = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
    . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where not exists (select * from " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap where " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.patchid = p.patchid  and " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.pgroupid = $removePatchGroupId ) and site in ($in1) $searchStr group by patchid $orderStr");

//  $sql2 = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site
//      from " . $GLOBALS['PREFIX'] . "softinst.Patches p
//      join " . $GLOBALS['PREFIX'] . "core.Census c on p.patchid=c.id
//      where site in ($in1) $searchStr
//      group by patchid $orderStr");
    $sql2->execute($siteArr);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    $result2 = safe_count($sql2->fetchAll(PDO::FETCH_ASSOC));

    $resultArr = array("count" => $result2, "data" => $result);
    return json_encode($resultArr);
}

function PATCH_GetPatchSList($key, $db, $site)
{
    $key = DASH_ValidateKey($key);
    $siteList = array();
    if ($key) {
        if (is_array($site)) {
            $res = safe_array_keys($site);
            $siteList = "";
            foreach ($res as $row) {
                array_push($siteList, $site[$row]);
            }
            $in  = str_repeat('?,', safe_count($siteList) - 1) . '?';
            $sql = $db->prepare("select  p.patchid,p.title,p.type,p.size,p.priority,p.serverfile,p.date,ps.patchconfigid,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                . "p.patchid = ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in)");
            $sql->execute($siteList);
        } else {
            $in  = str_repeat('?,', safe_count($site) - 1) . '?';
            $sql = $db->prepare("select  p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in)");
            $sql->execute($site);
        }
        $result = $sql->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $result;
}



function PATCH_GetPatchGroupList($db, $machines, $limitStr = '', $notifSearch = '', $orderStr = '')
{
    if ($notifSearch == '') {
        $searchStr = '';
    } else {
        if (strtolower($notifSearch) == 'undefined') {
            $searchStr = " and  (p.type  = 0)";
        } else if (strtolower($notifSearch) == 'update') {
            $searchStr = " and  (p.type   = 1)";
        } else if (strtolower($notifSearch) == 'service pack' || strtolower($notifSearch) == 'service' || strtolower($notifSearch) == 'service pack') {
            $searchStr = " and  (p.type  = 2)";
        } else if (strtolower($notifSearch) == 'roll up' || strtolower($notifSearch) == 'rollup' || strtolower($notifSearch) == 'roll') {
            $searchStr = " and  (p.type  = 3)";
        } else if (strtolower($notifSearch) == 'security') {
            $searchStr = " and  (p.type = 4 )";
        } else if (strtolower($notifSearch) == 'critical') {
            $searchStr = " and  (p.type = 5)";
        } else {
            $searchStr = " and  (p.title LIKE '%" . $notifSearch . "%')";
        }
    }
    $machArr = array();
    // $key = DASH_ValidateKey($key);
    // if ($key) {

    foreach ($machines as $row => $value) {
        array_push($machArr, $row);
    }
    $in  = str_repeat('?,', safe_count($machArr) - 1) . '?';
    $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
        . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where c.id in ($in) $searchStr group by patchid $orderStr $limitStr");
    $sql->execute($machArr);

    $sql2 = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
        . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where c.id in ($in) $searchStr group by patchid $orderStr");
    $sql2->execute($machArr);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    $countresult = safe_count($sql2->fetchAll(PDO::FETCH_ASSOC));

    $resultArr = array('count' => $countresult, 'data' => $result);
    // } else {

    //     echo "Your key has been expired";
    // }

    return json_encode($resultArr);
}



function PATCH_GetPatchMachineList($db, $censusId, $limitStr = '', $notifSearch = '', $orderStr = '')
{
    if ($notifSearch == '') {
        $searchStr = '';
    } else {
        if (strtolower($notifSearch) == 'undefined') {
            $searchStr = " and  (p.type  = 0)";
        } else if (strtolower($notifSearch) == 'update') {
            $searchStr = " and  (p.type   = 1)";
        } else if (strtolower($notifSearch) == 'service pack' || strtolower($notifSearch) == 'service' || strtolower($notifSearch) == 'service pack') {
            $searchStr = " and  (p.type  = 2)";
        } else if (strtolower($notifSearch) == 'roll up' || strtolower($notifSearch) == 'rollup' || strtolower($notifSearch) == 'roll') {
            $searchStr = " and  (p.type  = 3)";
        } else if (strtolower($notifSearch) == 'security') {
            $searchStr = " and  (p.type = 4 )";
        } else if (strtolower($notifSearch) == 'critical') {
            $searchStr = " and  (p.type = 5)";
        } else {
            $searchStr = " and  (p.title LIKE '%" . $notifSearch . "%')";
        }
    }
    //get remove patch group
    $removePatchMachineName = "Wiz_REMV_PG ".$_SESSION['rparentName'].":".$_SESSION['searchValue'];
    $removePatchMachine = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name = ?", null, [$removePatchMachineName]);
    $removePatchMachineId = $removePatchMachine['pgroupid'];


    $sql1 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where id = ?");
    $sql1->execute([$censusId]);
    $result1 = $sql1->fetch(PDO::FETCH_ASSOC);
    $nameVal = $result1['host'];
    $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
        . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where not exists (select * from " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap where " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.patchid = p.patchid  and " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.pgroupid = ?) and host = ? $searchStr group by patchid $limitStr");
    $sql->execute([$removePatchMachineId,$nameVal]);
    $result = $sql->fetchAll();

    $sql2 = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
        . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where not exists (select * from " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap where " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.patchid = p.patchid  and " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap.pgroupid = ?) and host = ? $searchStr group by patchid");
    $sql2->execute([$removePatchMachineId,$nameVal]);
    $countresult = safe_count($sql2->fetchAll(PDO::FETCH_ASSOC));
    $resultArr = array('count' => $countresult, 'data' => $result);
    return json_encode($resultArr);
}

function MUM_AllPatchUsed($key, $groupMachines, $db)
{
    $groupMachinesArr = array();
    $groupMachinesArr = explode(',', $groupMachines);
    $patchidArr = array();

    $key = DASH_ValidateKey($key);
    if ($key) {
        $patchids = '';
        $in1  = str_repeat('?,', safe_count($groupMachinesArr) - 1) . '?';
        $sql = $db->prepare("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name in ($in1)");
        $sql->execute($groupMachinesArr);
        $query = $sql->fetchAll();

        if (safe_count($query) > 0) {

            foreach ($query as $key => $value) {
                array_push($patchidArr, $value['pgroupid']);
            }
            $in2  = str_repeat('?,', safe_count($patchidArr) - 1) . '?';
            $sql_p = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid in ($in2) and"
                . " P.patchid = M.patchid group by P.patchid order by name, date, patchid");
            $sql_p->execute($patchidArr);
        }
    } else {
        echo "Your key has been expired";
    }
    return $sql_p;
}

function formatBytes($size)
{
    $size_mb = round($size / 1048576, 4);
    return "{$size_mb} MB";
}



function PATCH_GetPatchType($ptype)
{

    if ($ptype == 0) {
        $type = 'Undefined';
    }
    if ($ptype == 1) {
        $type = 'Update';
    }
    if ($ptype == 2) {
        $type = 'Service Pack';
    }
    if ($ptype == 3) {
        $type = 'Roll Up';
    }
    if ($ptype == 4) {
        $type = 'Security';
    }
    if ($ptype == 5) {
        $type = 'Critical';
    }
    return $type;
}



function getpatchstatus($pstatus)
{

    if ($pstatus == 8 || $pstatus == 19) {
        $status = 'Installed';
    }
    if ($pstatus == 11) {
        $status = 'Detected';
    }
    if ($pstatus == 10) {
        $status = 'Downloaded';
    }
    if ($pstatus == 2) {
        $status = 'Pending Install';
    }
    if ($pstatus == 3) {
        $status = 'Pending UnInstall';
    }
    if ($pstatus == 4) {
        $status = 'Scheduled Install';
    }
    if ($pstatus == 5) {
        $status = 'Scheduled UnInstall';
    }
    if ($pstatus == 6) {
        $status = 'Disable';
    }
    if ($pstatus == 9) {
        $status = 'UnInstall';
    }

    if ($pstatus == 12) {
        $status = 'Downloaded';
    }
    if ($pstatus == 14) {
        $status = 'Potential Install Failure';
    }
    if ($pstatus == 13) {
        $status = 'Pending Reboot';
    }
    if ($pstatus == 15) {
        $status = 'Superseded';
    }
    if ($pstatus == 16) {
        $status = 'Waiting';
    }
    if ($pstatus == 7) {
        $status = 'Error';
    }
    if ($pstatus == 18) {
        $status = 'Already Installed';
    }
    if ($pstatus == 20) {
        $status = 'Uninstall Error';
    }

    return $status;
}

function MUM_AllPatchId($key, $groupMachines, $db)
{
    $groupMachinesArr = array();
    $groupMachinesArr = explode(',', $groupMachines);
    $in  = str_repeat('?,', safe_count($groupMachinesArr) - 1) . '?';
    $key = DASH_ValidateKey($key);
//    if ($key) {
        $patchids = '';
        $query = NanoDB::find_many("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name in ($in)",null,$groupMachinesArr);
//        $sql = $db->prepare("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name in ($in)");
//        $sql->execute($groupMachinesArr);
//        $query = $sql->fetchAll();
        foreach ($query as $key => $value) {
            $patchids .= "" . $value['pgroupid'] . ",";
        }
        $patchid = rtrim($patchids, ',');
//    } else {
//        echo "Your key has been expired";
//    }
    return $patchid;
}



function PATCH_ApprovePatchId($key, $searchValue, $searchType, $db)
{

    // $key = DASH_ValidateKey($key);
    // if ($key) {
    if ($searchType == 'Sites') {
        // if ($searchValue == 'All') {
        //     $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
        //     $res = safe_array_keys($sitesDisply);
        //     foreach ($res as $row) {
        //         $siteList .= "'Wiz_APPR_PG " . $sitesDisply[$row] . "',";
        //     }
        //     $lableDisply = rtrim($siteList, ',');
        //     $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
        // } else {
        $lableDisply = "Wiz_APPR_PG " . $searchValue;
        $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
        // }
    } else if ($searchType == 'ServiceTag') {
        $searchValue = $_SESSION['rparentName'] . ':' . $_SESSION['searchValue'];
        $lableDisply = "Wiz_APPR_PG " . $searchValue;
        $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
    } else if ($searchType == 'Groups') {

        $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
        $res = safe_array_keys($groupMachine);
        $groupidList = '';
        foreach ($res as $row) {
            $groupidList .= "Wiz_APPR_PG " . $groupMachine[$row] . ",";
        }
        $groupMachines = rtrim($groupidList, ',');
        if ($searchValue == 'All') {
            $patchid_appr = MUM_AllPatchId('', $groupMachines, $db);
        } else {
            $name = $searchValue;
            $groupname = "Wiz_APPR_PG " . $name;
            $patchid_appr = MUM_AllPatchId('', $groupname, $db);
        }
    }
    // } else {
    //     echo "Your key has been expired";
    // }
    return $patchid_appr;
}

function PATCH_RetryPatchId($key, $searchValue, $searchType, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                $siteList = '';
                foreach ($res as $row) {
                    $siteList .= "'Wiz_RETRY_PC " . $sitesDisply[$row] . "',";
                }
                $lableDisply = rtrim($siteList, ',');
                $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
            } else {
                $lableDisply = "Wiz_RETRY_PC " . $searchValue;
                $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
            }
        } else if ($searchType == 'ServiceTag') {

            $lableDisply = "Wiz_RETRY_PC " . $searchValue;
            $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
        } else if ($searchType == 'Groups') {

            $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
            $res = safe_array_keys($groupMachine);
            foreach ($res as $row) {
                $groupidList .= "Wiz_RETRY_PC " . $groupMachine[$row] . ",";
            }
            $groupMachines = rtrim($groupidList, ',');
            if ($searchValue == 'All') {
                $patchid_appr = MUM_AllPatchId('', $groupMachines, $db);
            } else {
                $name = $searchValue;
                $groupname = "Wiz_RETRY_PC " . $name;
                $patchid_appr = MUM_AllPatchId('', $groupname, $db);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $patchid_appr;
}

function PATCH_CriticalPatchId($key, $searchValue, $searchType, $db)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                foreach ($res as $row) {
                    $siteList .= "'Wiz_CRIT_PG " . $sitesDisply[$row] . "',";
                }
                $lableDisply = rtrim($siteList, ',');
                $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
            } else {
                $lableDisply = "Wiz_CRIT_PG " . $searchValue;
                $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
            }
        } else if ($searchType == 'ServiceTag') {

            $lableDisply = "Wiz_CRIT_PG " . $searchValue;
            $patchid_appr = MUM_AllPatchId('', $lableDisply, $db);
        } else if ($searchType == 'Groups') {

            $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
            $res = safe_array_keys($groupMachine);
            foreach ($res as $row) {
                $groupidList .= "Wiz_CRIT_PG " . $groupMachine[$row] . ",";
            }
            $groupMachines = rtrim($groupidList, ',');
            if ($searchValue == 'All') {
                $patchid_appr = MUM_AllPatchId('', $groupMachines, $db);
            } else {
                $name = $searchValue;
                $groupname = "Wiz_CRIT_PG " . $name;
                $patchid_appr = MUM_AllPatchId('', $groupname, $db);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $patchid_appr;
}





function PATCH_DeclinePatchId($key, $searchValue, $searchType, $db)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                foreach ($res as $row) {
                    $siteList .= "'Wiz_DECL_PG " . $sitesDisply[$row] . "',";
                }
                $lableDisply = rtrim($siteList, ',');
                $patchid_decl = MUM_AllPatchId('', $lableDisply, $db);
            } else {
                $lableDisply = "Wiz_DECL_PG " . $searchValue;
                $patchid_decl = MUM_AllPatchId('', $lableDisply, $db);
            }
        } else if ($searchType == 'ServiceTag') {

            $lableDisply = "Wiz_DECL_PG " . $searchValue;
            $patchid_decl = MUM_AllPatchId('', $lableDisply, $db);
        } else if ($searchType == 'Groups') {

            $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
            $res = safe_array_keys($groupMachine);
            foreach ($res as $row) {
                $groupidList .= "Wiz_DECL_PG " . $groupMachine[$row] . ",";
            }
            $groupMachines = rtrim($groupidList, ',');
            if ($searchValue == 'All') {
                $patchid_decl = MUM_AllPatchId('', $groupMachines, $db);
            } else {
                $name = $searchValue;
                $groupname = "Wiz_DECL_PG " . $name;

                $patchid_decl = MUM_AllPatchId('', $groupname, $db);
            }
        }
    } else {
        echo "Your key has been expired";
    }
    return $patchid_decl;
}



function PATCH_RemovePatchId($key, $searchValue, $searchType, $db)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                foreach ($res as $row) {
                    $siteList .= "'Wiz_REMV_PG " . $sitesDisply[$row] . "',";
                }
                $lableDisply = rtrim($siteList, ',');
                $removed_arr = MUM_AllPatchId('', $lableDisply, $db);
            } else {

                $lableDisply = "'Wiz_REMV_PG " . $searchValue . "'";
                $removed_arr = MUM_AllPatchId('', $lableDisply, $db);
            }
        } else if ($searchType == 'ServiceTag') {

            $lableDisply = "'Wiz_REMV_PG " . $searchValue . "'";
            $removed_arr = MUM_AllPatchId('', $lableDisply, $db);
        } else if ($searchType == 'Groups') {

            $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
            $res = safe_array_keys($groupMachine);
            foreach ($res as $row) {
                $groupidList .= "'Wiz_REMV_PG " . $groupMachine[$row] . "',";
            }
            $groupMachines = rtrim($groupidList, ',');
            if ($searchValue == 'All') {
                $removed_arr = MUM_AllPatchId('', $groupMachines, $db);
            } else {
                $name = $searchValue;
                $groupname = "'Wiz_REMV_PG " . $name . "'";
                $removed_arr = MUM_AllPatchId('', $groupname, $db);
            }
        }
    } else {
        echo "Your key has been expired";
    }

    return $removed_arr;
}


function PATCH_AllPatchDetail($key, $searchValue, $searchType, $db)
{
    $ptype_arr = array();
    $patch_arr = array();
    // if ($searchType == 'Sites') {
    //     $lableDisply = "Wiz_APPR_PG " . $searchValue;
    //     $aaprv = "Wiz_APPR_PG " . $searchValue;
    //     $decl = "Wiz_DECL_PG " . $searchValue;
    //     $remv = "Wiz_REMV_PG " . $searchValue;
    //     $retry = "Wiz_RETRY_PC " . $searchValue;
    //     $ptype_arr['approve'] = $aaprv ;
    //     $ptype_arr['decline'] = $decl ;
    //     $ptype_arr['remove'] = $remv ;
    //     $ptype_arr['retry'] = $retry ;
    //     $sql_p = MUM_PUsed('', $ptype_arr, $db);
    // } else if ($searchType == 'ServiceTag') {
    //     $aaprv = "Wiz_APPR_PG " . $searchValue;
    //     $decl = "Wiz_DECL_PG " . $searchValue;
    //     $remv = "Wiz_REMV_PG " . $searchValue;
    //     $retry = "Wiz_RETRY_PC " . $searchValue;
    //     $ptype_arr['approve'] = $aaprv ;
    //     $ptype_arr['decline'] = $decl ;
    //     $ptype_arr['remove'] = $remv ;
    //     $ptype_arr['retry'] = $retry ;
    //     $sql_p = MUM_PUsed('', $ptype_arr, $db);
    // } else if ($searchType == 'Groups') {
    // $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
    // $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
    // $res = safe_array_keys($groupMachine);
    // foreach ($res as $row) {
    //     $groupidList .= "Wiz_APPR_PG " . $groupMachine[$row] . ",";
    // }
    // $groupMachines = rtrim($groupidList, ',');
    $aaprv = "Wiz_APPR_PG " . $searchValue;
    $decl = "Wiz_DECL_PG " . $searchValue;
    $remv = "Wiz_REMV_PG " . $searchValue;
    $retry = "Wiz_RETRY_PC " . $searchValue;
    // array_push($ptype_arr,$aaprv);
    // array_push($ptype_arr,$decl);
    // array_push($ptype_arr,$remv);
    // array_push($ptype_arr,$retry);
    $ptype_arr['approve'] = $aaprv;
    $ptype_arr['decline'] = $decl;
    $ptype_arr['remove'] = $remv;
    $ptype_arr['retry'] = $retry;
    // $name = $searchValue;
    // $groupname = "Wiz_APPR_PG " . $name;
    $sql_p = MUM_PUsed('', $searchValue, $db);
    // print_R($sql_p);exit;
    // }
    if ($sql_p == '') {
        $patch_arr = array();
    } else {
        $patch_arr =  $sql_p;
        // print_r($query_p['approve']);exit;
        // foreach($query_p['approve'] as $key => $row){
        //         array_push($patch_arr,$row['patchid']);
        // }
    }
    return $patch_arr;
}

function MUM_PUsed($key, $searchValue, $db)
{
    // print_R($groupMachines);exit;
    // // $groupMachinesArr = array();
    // $groupMachinesArr = explode(',',$groupMachines);
    // print_R($groupMachinesArr);exit;
    $patchidArr = array();

    $apprArr = array();
    $declArr = array();
    $remArr = array();
    $retryArr = array();
    $aaprv = "Wiz_APPR_PG " . $searchValue;
    $decl = "Wiz_DECL_PG " . $searchValue;
    $remv = "Wiz_REMV_PG " . $searchValue;
    $retry = "Wiz_RETRY_PC " . $searchValue;
    $sql = $db->prepare("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
    $sql->execute([$aaprv]);
    $query = $sql->fetch(PDO::FETCH_ASSOC);
    $apprpatchid = $query['pgroupid'];

    $sql = $db->prepare("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
    $sql->execute([$decl]);
    $query = $sql->fetch(PDO::FETCH_ASSOC);
    $declpatchid = $query['pgroupid'];

    $sql = $db->prepare("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
    $sql->execute([$remv]);
    $query = $sql->fetch(PDO::FETCH_ASSOC);
    $remvpatchid = $query['pgroupid'];

    $sql = $db->prepare("select pgroupid from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
    $sql->execute([$retry]);
    $query = $sql->fetch(PDO::FETCH_ASSOC);
    $retrypatchid = $query['pgroupid'];

    $sql_p = $db->prepare("select P.patchid as patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ? and"
        . " P.patchid = M.patchid group by P.patchid order by name, date, patchid");
    $sql_p->execute([$apprpatchid]);
    $result1 = $sql_p->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result1 as $key => $row) {
        array_push($apprArr, $row['patchid']);
    }

    $sql_p = $db->prepare("select P.patchid as patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ? and"
        . " P.patchid = M.patchid group by P.patchid order by name, date, patchid");
    $sql_p->execute([$declpatchid]);
    $result2 = $sql_p->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result2 as $key => $row) {
        array_push($declArr, $row['patchid']);
    }

    $sql_p = $db->prepare("select P.patchid as patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ? and"
        . " P.patchid = M.patchid group by P.patchid order by name, date, patchid");
    $sql_p->execute([$remvpatchid]);
    $result3 = $sql_p->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result3 as $key => $row) {
        array_push($remArr, $row['patchid']);
    }

    $sql_p = $db->prepare("select P.patchid as patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ? and"
        . " P.patchid = M.patchid group by P.patchid order by name, date, patchid");
    $sql_p->execute([$retrypatchid]);

    $result4 = $sql_p->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result4 as $key => $row) {
        array_push($retryArr, $row['patchid']);
    }

    $result1 = implode(',', $apprArr);
    $result2 = implode(',', $declArr);
    $result3 = implode(',', $remArr);
    $result4 = implode(',', $retryArr);

    $patchidArr['approve'] = $result1;
    $patchidArr['decline'] = $result2;
    $patchidArr['retry'] = $result3;
    $patchidArr['remove'] = $result4;

    return $patchidArr;
}


function PATCH_GetApprovePatch($key, $searchValue, $searchType, $db)
{

    $patch_arr = array();
    // $key = DASH_ValidateKey($key);
    // if ($key) {
    if ($searchType == 'Sites') {
        // if ($searchValue == 'All') {

        //     $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
        //     $res = safe_array_keys($sitesDisply);
        //     foreach ($res as $row) {
        //         $siteList .= "'Wiz_APPR_PG " . $sitesDisply[$row] . "',";
        //     }
        //     $lableDisply = rtrim($siteList, ',');
        //     $sql_p = MUM_AllPatchUsed('', $lableDisply, $db);
        // } else {
        $lableDisply = "Wiz_APPR_PG " . $searchValue;
        $sql_p = MUM_AllPatchUsed('', $lableDisply, $db);
        // }
    } else if ($searchType == 'ServiceTag') {

        $lableDisply = "Wiz_APPR_PG " . $searchValue;
        $sql_p = MUM_AllPatchUsed('', $lableDisply, $db);
    } else if ($searchType == 'Groups') {

        $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
        $res = safe_array_keys($groupMachine);
        foreach ($res as $row) {
            $groupidList .= "Wiz_APPR_PG " . $groupMachine[$row] . ",";
        }
        $groupMachines = rtrim($groupidList, ',');
        if ($searchValue == 'All') {
            $sql_p = MUM_AllPatchUsed('', $groupMachines, $db);
        } else {
            $name = $searchValue;
            $groupname = "Wiz_APPR_PG " . $name;
            $sql_p = MUM_AllPatchUsed('', $groupname, $db);
        }
    }
    if ($sql_p == '') {
        $patch_arr = array();
    } else {
        $query_p = $sql_p->fetchAll(PDO::FETCH_ASSOC);
        foreach ($query_p as $key => $row) {
            array_push($patch_arr, $row['patchid']);
        }
    }

    // } else {
    //     echo "Your key has been expired";
    // }
    return $patch_arr;
}



function PATCH_GetDeclinePatch($key, $searchValue, $searchType, $db)
{
    $decl_arr = array();
    // $key = DASH_ValidateKey($key);

    // if ($key) {
    if ($searchType == 'Sites') {
        // if ($searchValue == 'All') {
        //     $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
        //     $res = safe_array_keys($sitesDisply);
        //     foreach ($res as $row) {
        //         $siteList .= "'Wiz_DECL_PG " . $sitesDisply[$row] . "',";
        //     }
        //     $lableDisply = rtrim($siteList, ',');
        //     $sql_d = MUM_AllPatchUsed('', $lableDisply, $db);
        // } else {
        $lableDisply = "Wiz_DECL_PG " . $searchValue;
        $sql_d = MUM_AllPatchUsed('', $lableDisply, $db);
        // }
    } else if ($searchType == 'ServiceTag') {

        $lableDisply = "Wiz_DECL_PG " . $searchValue;
        $sql_d = MUM_AllPatchUsed('', $lableDisply, $db);
    } else if ($searchType == 'Groups') {

        $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
        $res = safe_array_keys($groupMachine);
        foreach ($res as $row) {
            $groupidList .= "Wiz_DECL_PG " . $groupMachine[$row] . ",";
        }
        $groupMachines = rtrim($groupidList, ',');
        if ($searchValue == 'All') {
            $sql_d = MUM_AllPatchUsed('', $groupMachines, $db);
        } else {
            $name = $searchValue;
            $groupname = "Wiz_DECL_PG " . $searchValue;
            $sql_d = MUM_AllPatchUsed('', $groupname, $db);
        }
    }
    if ($sql_d == '') {
        $decl_arr = array();
    } else {
        $query_d = $sql_d->fetchAll();
        foreach ($query_d as $key => $row) {
            $decl_arr[] = $row['patchid'];
        }
    }

    // } else {
    //     echo "Your key has been expired";
    // }

    return $decl_arr;
}



function PATCH_GetRemovepatch($key, $searchValue, $searchType, $db)
{
    $removed_arr = array();
    // $key = DASH_ValidateKey($key);

    // if ($key) {
    if ($searchType == 'Sites') {
        // if ($searchValue == 'All') {
        //     $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
        //     $res = safe_array_keys($sitesDisply);
        //     foreach ($res as $row) {
        //         $siteList .= "'Wiz_REMV_PG " . $sitesDisply[$row] . "',";
        //     }
        //     $lableDisply = rtrim($siteList, ',');
        //     $sql_r = MUM_AllPatchUsed('', $lableDisply, $db);
        // } else {

        $lableDisply = "Wiz_REMV_PG " . $searchValue;
        $sql_r = MUM_AllPatchUsed('', $lableDisply, $db);
        // }
    } else if ($searchType == 'ServiceTag') {

        $lableDisply = "Wiz_REMV_PG " . $searchValue . "";
        $sql_r = MUM_AllPatchUsed('', $lableDisply, $db);
    } else if ($searchType == 'Groups') {

        $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
        $res = safe_array_keys($groupMachine);
        foreach ($res as $row) {
            $groupidList .= "Wiz_REMV_PG " . $groupMachine[$row] . ",";
        }
        $groupMachines = rtrim($groupidList, ',');
        if ($searchValue == 'All') {
            $sql_r = MUM_AllPatchUsed('', $groupMachines, $db);
        } else {
            $name = $searchValue;
            $groupname = "Wiz_REMV_PG " . $name;
            $sql_r = MUM_AllPatchUsed('', $groupname, $db);
        }
    }

    if ($sql_r == '') {
        $removed_arr = array();
    } else {
        $query_r = $sql_r->fetchAll();
        foreach ($query_r as $key => $row) {
            $removed_arr[] = $row['patchid'];
        }
    }

    // } else {
    //     echo "Your key has been expired";
    // }

    return $removed_arr;
}

function PATCH_GetRetrypatch($key, $searchValue, $searchType, $db)
{
    $removed_arr = array();
    // $key = DASH_ValidateKey($key);

    // if ($key) {
    if ($searchType == 'Sites') {
        // if ($searchValue == 'All') {
        //     $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
        //     $res = safe_array_keys($sitesDisply);
        //     foreach ($res as $row) {
        //         $siteList .= "'Wiz_RETRY_PC " . $sitesDisply[$row] . "',";
        //     }
        //     $lableDisply = rtrim($siteList, ',');
        //     $sql_r = MUM_AllPatchUsed('', $lableDisply, $db);
        // } else {

        $lableDisply = "Wiz_RETRY_PC " . $searchValue;
        $sql_r = MUM_AllPatchUsed('', $lableDisply, $db);
        // }
    } else if ($searchType == 'ServiceTag') {

        $lableDisply = "Wiz_RETRY_PC " . $searchValue . "";
        $sql_r = MUM_AllPatchUsed('', $lableDisply, $db);
    } else if ($searchType == 'Groups') {

        $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
        $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
        $res = safe_array_keys($groupMachine);
        foreach ($res as $row) {
            $groupidList .= "Wiz_RETRY_PC " . $groupMachine[$row] . ",";
        }
        $groupMachines = rtrim($groupidList, ',');
        if ($searchValue == 'All') {
            $sql_r = MUM_AllPatchUsed('', $groupMachines, $db);
        } else {
            $name = $searchValue;
            $groupname = "Wiz_RETRY_PC " . $name;
            $sql_r = MUM_AllPatchUsed('', $groupname, $db);
        }
    }
    if ($sql_r == '') {
        $removed_arr = array();
    } else {
        $query_r = $sql_r->fetchAll();
        foreach ($query_r as $key => $row) {
            $removed_arr[] = $row['patchid'];
        }
    }
    // } else {
    //     echo "Your key has been expired";
    // }

    return $removed_arr;
}



function MUM_GetCriticalpatch($key, $searchValue, $searchType, $db)
{
    $critic_arr = array();
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                foreach ($res as $row) {
                    $siteList .= "'Wiz_CRIT_PG " . $sitesDisply[$row] . "',";
                }
                $lableDisply = rtrim($siteList, ',');
                $sql_c = MUM_AllPatchUsed('', $lableDisply, $db);
            } else {

                $lableDisply = "Wiz_CRIT_PG " . $searchValue;
                $sql_c = MUM_AllPatchUsed('', $lableDisply, $db);
            }
        } else if ($searchType == 'ServiceTag') {

            $lableDisply = "Wiz_CRIT_PG " . $searchValue;
            $sql_c = MUM_AllPatchUsed('', $lableDisply, $db);
        } else if ($searchType == 'Groups') {
            $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
            $res = safe_array_keys($groupMachine);
            foreach ($res as $row) {
                $groupidList .= "Wiz_CRIT_PG :" . $groupMachine[$row] . ",";
            }
            $groupMachines = rtrim($groupidList, ',');
            if ($searchValue == 'All') {
                $sql_c = MUM_AllPatchUsed('', $groupMachines, $db);
            } else {
                $name = $_SESSION['searchValue'];
                $groupname = "Wiz_CRIT_PG " . $name;
                $sql_c = MUM_AllPatchUsed('', $groupname, $db);
            }
        }
        if ($query_c){
          $query_c = $sql_c->fetchAll();
          foreach ($query_c as $key => $row) {
            $critic_arr[] = $row['patchid'];
          }
        }else{
          $critic_arr = [];
        }

    } else {
        echo "Your key has been expired";
    }
    return $critic_arr;
}



function PATCH_GetPatcheCount($key, $searchValue, $searchType, $patchid, $db)
{
    $siteListArr = array();
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                foreach ($res as $row) {
                    array_push($siteListArr, $sitesDisply[$row]);
                }
                $in  = str_repeat('?,', safe_count($siteListArr) - 1) . '?';
                $params = array_merge([$patchid], $siteListArr);
                $sql = $db->prepare("select  P.patchid from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND"
                    . " C.id = PS.id AND PS.patchid = ? AND C.site in ($in)");
                $sql->execute($params);
            } else {
                $sql = $db->prepare("select  P.patchid from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND "
                    . "C.id = PS.id AND PS.patchid = ? AND C.site = ?");
                $sql->execute([$patchid, $searchValue]);
            }
        } else if ($searchType == 'ServiceTag') {
            $sql = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND "
                . "C.id = PS.id AND PS.patchid = ? AND C.host = ?");
            $sql->execute([$patchid, $searchValue]);
        } else if ($searchType == 'Groups') {
            $groupMachinesArr = array();
            $groupMachines = PATCH_GroupCaseMachineList($db, $searchValue, $searchType);
            $groupMachinesArr = explode(',', $groupMachines);
            $in  = str_repeat('?,', safe_count($groupMachinesArr) - 1) . '?';
            $params = array_merge([$patchid], $groupMachinesArr);
            $sql = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS left join " . $GLOBALS['PREFIX'] . "core.Census C on C.id = PS.id left join " . $GLOBALS['PREFIX'] . "softinst.Patches P on P.patchid "
                . "= PS.patchid where PS.patchid = ? AND C.id IN ($in)");
            $sql->execute($params);
        }
    } else {
        echo "Your key has been expired";
    }
    $queryf = $sql->fetchAll();
    $patchcount = safe_count($queryf);
    return $patchcount;
}



function PATCH_GetPatchStatus($key, $patchid, $searchValue, $searchType, $db, $statusID = '')
{
    $patchidArr = array();
    $patchidArr = explode(',', $patchid);

    $statusIDArr = array();
    $statusIDArr = explode(',', $statusID);
    $in1 = str_repeat('?,', safe_count($patchidArr) - 1) . '?';
    $in3 = str_repeat('?,', safe_count($statusIDArr) - 1) . '?';
    if (strlen($statusID) > 0) {
        $pstatus = "and PS.status in ($in3)";
    } else {
        $pstatus = "";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {
                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                $siteListArr = array();
                foreach ($res as $row) {
                    array_push($siteListArr, $sitesDisply[$row]);
                }

                $in2 = str_repeat('?,', safe_count($siteListArr) - 1) . '?';
                if (strlen($statusID) > 0) {
                    $params = array_merge($patchidArr, $siteListArr, $statusIDArr);
                } else {
                    $params = array_merge($patchidArr, $siteListArr);
                }

                $sql = $db->prepare("select  C.os,P.type,P.patchid,P.date,P.size,PS.lastdownload,P.kbnumber,C.site,C.host,P.title,PS.detected,PS.status,PS.lastdownload,PS.lastinstall,PS.lasterror from "
                    . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND C.id = PS.id AND "
                    . "PS.patchid in ( $in1 ) AND C.site in ($in2) $pstatus");
                $sql->execute($params);
            } else {
                if (strlen($statusID) > 0) {
                    $params = array_merge($patchidArr, [$searchValue], $statusIDArr);
                } else {
                    $params = array_merge($patchidArr, [$searchValue]);
                }

                $sql = $db->prepare("select  C.os,P.type,P.patchid,P.date,P.size,PS.lastdownload,P.kbnumber,C.site,C.host,P.title,PS.detected,PS.status,PS.lastdownload,PS.lastinstall,PS.lasterror from "
                    . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND C.id = PS.id AND "
                    . "PS.patchid in ($in1) AND C.site = ? $pstatus");
                $sql->execute($params);
            }
        } else if ($searchType == 'ServiceTag') {
            $searchValueArr = array();
            $searchValueArr = explode(',', $searchValue);
            $in2 = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
            $pname = PATCH_GetParentName($searchValue);
            if (strlen($statusID) > 0) {
                $params = array_merge($patchidArr, $searchValueArr, $statusIDArr);
            } else {
                $params = array_merge($patchidArr, $searchValueArr);
            }

            $sql = $db->prepare("select C.os,P.type,P.patchid,P.date,P.size,PS.lastdownload,P.kbnumber,C.site,C.host,P.title,PS.detected,PS.status,PS.lastdownload,PS.lastinstall,PS.lasterror from "
                . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND C.id = PS.id AND "
                . "PS.patchid in ( $in1 ) AND C.host in ($in2)  $pstatus");
            $sql->execute($params);
        } else if ($searchType == 'Groups') {
            $groupMachines = PATCH_GroupCaseMachineList($db, $searchValue, $searchType, $statusIDArr);
            $groupMachinesArr = explode(',', $groupMachines);
            $in2 = str_repeat('?,', safe_count($groupMachinesArr) - 1) . '?';

            if (strlen($statusID) > 0) {
                $params = array_merge($patchidArr, $groupMachinesArr, $statusIDArr);
            } else {
                $params = array_merge($patchidArr, $groupMachinesArr);
            }

            $sql = $db->prepare("select C.os,P.type,P.patchid,P.date,P.size,PS.lastdownload,P.kbnumber,C.site,C.host,P.title,PS.detected,PS.status,PS.lastdownload,PS.lastinstall,PS.lasterror from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus"
                . " PS left join " . $GLOBALS['PREFIX'] . "core.Census C on C.id = PS.id left join " . $GLOBALS['PREFIX'] . "softinst.Patches P on P.patchid = PS.patchid where PS.patchid in ( $in1 ) "
                . "AND C.id IN ($in2) $pstatus");
            $sql->execute($params);
        }
        $patchstatuslist = $sql->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $patchstatuslist;
}

function PATCH_GroupCaseMachineList($db, $searchValue, $searchType)
{

    $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
    foreach ($groupMachine as $row => $value) {
        $groupidList .= $row . ",";
    }
    $groupMachines = rtrim($groupidList, ',');
    return $groupMachines;
}

function PATCH_GetConfigSites($key, $db, $dataScope, $searchValue)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
        $sql->execute([$dataScope]);
        $result = $sql->fetchAll();
        foreach ($result as $value) {
        }
        if ($searchValue == 'All') {
        } else {
            $searchValue = explode(',', $searchValue);
            $in = str_repeat('?,', safe_count($searchValue) - 1) . '?';
            $sql = $db->prepare("select p.patchid,p.title,p.type,p.size,p.priority,p.date,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                . "p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in) $filter $typ $plfm $pri $sz $DT $where"
                . " $orderValues $limit");
            $sql->execute($searchValue);
        }
    } else {
        echo "Your key has been expired";
    }
    $result = $sql->fetchAll();
    return $result;
}



function PATCH_GetConfigureSiteDetails($key, $db, $site)
{
    $result = array();
    $siteslistArr = array();
    $sitesValueArr = array();
    $mgroupidArr = array();
    $key = DASH_ValidateKey($key);

    if ($key) {

        if (is_array($site)) {
            $res = safe_array_keys($site);
            foreach ($res as $row) {
                array_push($siteslistArr, $site[$row]);
                array_push($sitesValueArr, $site[$row]);
            }

            $in1  = str_repeat('?,', safe_count($siteslistArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
            $sql->execute($siteslistArr);
            $result = $sql->fetchAll();

            foreach ($result as $val) {
                array_push($mgroupidArr, $val['mgroupid']);
            }

            $in2  = str_repeat('?,', safe_count($sitesValueArr) - 1) . '?';
            $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name in ($in2)");
            $sql->execute($sitesValueArr);
            $res = $sql->fetchAll();

            foreach ($res as $value) {
                array_push($mgroupidArr, $value['pgroupid']);
            }

            $in  = str_repeat('?,', safe_count($mgroupidArr) - 1) . '?';
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where pgroupid = '1' and mgroupid in ($in)");
            $sql->execute($mgroupidArr);
        } else {
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? limit 1");
            $sql->execute([$site]);
            $result = $sql->fetch();
            $mgroupid = $result['mgroupid'];

            $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
            $sql->execute(["Wiz_APPR_PG " . $site]);
            $res = $sql->fetch();
            $pgroupid = $res['pgroupid'];
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where pgroupid = '1' and mgroupid = ?");
            $sql->execute([$mgroupid]);
        }
        $result = $sql->fetchAll();
    } else {
        echo "Your key has been expired";
    }
    return $result;
}



function PATCH_GetConfigureMachineDetails($key, $db, $rparent, $searchValue)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name =? LIMIT 1");
        $sql->execute([$rparent . ":" . $searchValue]);
        $result = $sql->fetch();

        $mgroupid = $result['mgroupid'];

        $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
        $sql->execute(["Wiz_APPR_PG " . $rparent . ":" . $searchValue]);
        $res = $sql->fetch();
        $pgroupid = $res['pgroupid'];

        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where pgroupid = '1' and  mgroupid = ?");
        $sql->execute([$mgroupid]);
    } else {
        echo "Your key has been expired";
    }
    $result = $sql->fetchAll();
    return $result;
}



function PATCH_GetConfigureGroupDetails($key, $db, $grpname, $group)
{
    $key = DASH_ValidateKey($key);
    $labelgroupArr = array();
    $grpnameArr = array();
    $mgrpidArr = array();
    if ($key) {

        if (is_array($group)) {
            $res = safe_array_keys($group);
            foreach ($res as $row) {
                array_push($labelgroupArr, $row);
            }
            $in  = str_repeat('?,', safe_count($labelgroupArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in)");
            $sql->execute($labelgroupArr);
        } else {
            $grpnameArr = explode(',', $grpname);
            $in  = str_repeat('?,', safe_count($grpnameArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
            $sql->execute([$group]);
        }
        //	print_R($sql);
        $res = $sql->fetchAll();

        foreach ($res as $value) {
            array_push($mgrpidArr, $value['mgroupid']);
        }

        $in  = str_repeat('?,', safe_count($mgrpidArr) - 1) . '?';
        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where pgroupid = '1' and mgroupid in ($in)");
        $sql->execute($mgrpidArr);
        $result = $sql->fetchAll();
    } else {
        echo "Your Key has been expired";
    }


    return $result;
}

function PATCH_GetMumFilterSitesList($key, $db, $site, $status, $plfm, $pri, $sz, $typ, $DT)
{
    $key = DASH_ValidateKey($key);
    $siteList = array();
    if ($searchVal != '') {
        $search = "and p.title like '%$searchVal%'";
    }

    if ($key) {
        if (is_array($site)) {

            $res = safe_array_keys($site);
            foreach ($res as $row) {
                array_push($siteList, $site[$row]);
            }
            $in  = str_repeat('?,', safe_count($siteList) - 1) . '?';
            $sql = $db->prepare("select p.patchid,p.title,p.size,c.site,c.host,ps.status,p.date,ps.detected,ps.lastdownload,ps.lastchange,ps.lasterror,ps.lastinstall from " . $GLOBALS['PREFIX'] . "softinst.Patches p join "
                . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in) $status "
                . "$typ $plfm $pri $sz $DT");
            $sql->execute($siteList);
        } else {
            $in  = str_repeat('?,', safe_count($site) - 1) . '?';
            $sql = $db->prepare("select p.patchid,p.title,p.size,c.site,c.host,ps.status,p.date,ps.detected,ps.lastdownload,ps.lastchange,ps.lasterror,ps.lastinstall from " . $GLOBALS['PREFIX'] . "softinst.Patches p join "
                . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site in ($in) $status "
                . "$typ $plfm $pri $sz $DT");
            $sql->execute([]);
        }
        $result = $sql->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $result;
}

function PATCH_GetMumFilterMachineList($key, $db, $searchValue, $status, $plfm, $pri, $sz, $typ, $DT)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = $db->prepare("select p.patchid,p.title,p.size,c.site,c.host,ps.status,ps.detected,ps.lastdownload,ps.lastchange,ps.lasterror,p.date,ps.lastinstall from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus "
            . "ps on p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where host = ? $status $typ $plfm $pri $sz $DT $search");
        $sql->execute([$searchValue]);
        $result = $sql->fetchAll();
    } else {

        echo "Your key has been expired";
    }

    return $result;
}

function PATCH_GetMumFilterGroupList($key, $db, $machines, $filter, $plfm, $pri, $sz, $typ, $DT)
{
    $key = DASH_ValidateKey($key);
    $groupidList = array();
    if ($key) {
        if (is_array($machines)) {
            $res = safe_array_keys($machines);
            foreach ($res as $row) {
                array_push($groupidList, $machines[$row]);
            }
            $in  = str_repeat('?,', safe_count($groupidList) - 1) . '?';
            $sql = $db->prepare("select p.patchid,p.title,p.size,C.site,C.host,ps.status,ps.detected,ps.lastdownload,ps.lastchange,ps.lasterror,p.date,ps.lastinstall from "
                . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps left join " . $GLOBALS['PREFIX'] . "core.Census C on C.id = ps.id left join " . $GLOBALS['PREFIX'] . "softinst.Patches p on p.patchid = ps.patchid where "
                . "C.host IN ($in) $search $filter $typ $plfm $pri $sz $DT");
            $sql->execute($groupidList);
        } else {
            $sql = $db->prepare("select p.patchid,p.title,p.size,C.site,C.host,ps.status,ps.detected,ps.lastdownload,ps.lastchange,ps.lasterror,p.date,ps.lastinstall from "
                . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps left join " . $GLOBALS['PREFIX'] . "core.Census C on C.id = ps.id left join " . $GLOBALS['PREFIX'] . "softinst.Patches p on p.patchid = ps.patchid where "
                . "C.host IN ($in) $search $filter $typ $plfm $pri $sz $DT");
            $sql->execute($groupidList);
        }
    } else {
        echo "Your key has been expired";
    }
    $result = $sql->fetchAll();
    return $result;
}

function PATCH_sitesretryPatches($key, $db, $site)
{
    $key = DASH_ValidateKey($key);
    $lableDisplyArr = array();
    $pgroupidArr = array();
    if ($key) {
        if (is_array($site)) {
            $res = safe_array_keys($site);
            foreach ($res as $row) {
                $siteList .= "Wiz_APPR_PG " . $site[$row] . ",";
            }
            $lableDisplyArr = explode(',', $lableDisply);

            $in  = str_repeat('?,', safe_count($lableDisplyArr) - 1) . '?';
            $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name in ($in)");
            $sql->execute($lableDisplyArr);
            $sqlresult = $sql->fetchAll(PDO::FETCH_ASSOC);

            foreach ($sqlresult as $val) {
                array_push($pgroupidArr, $val['pgroupid']);
            }
            $sqlpatch = $db->prepare("select p.patchid ,c.host,p.title,ps.`status`,ps.lasterror,p.type  from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where c.site in ($in)  and ps.status = 7");
            $sqlpatch->execute($lableDisplyArr);
        } else {
            $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = 'All'");
            $sql->execute();
            $sqlresult = $sql->fetch();
            $pgroupid = $sqlresult['pgroupid'];

            $sqlpatch = $db->prepare("select p.patchid ,c.host,p.title,ps.`status`,ps.lasterror,p.type  from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where c.site = ? and ps.status = 7 ");
            $sqlpatch->execute([$site]);
        }
        $res = $sqlpatch->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Your key has been expired";
    }
    return $res;
}

function PATCH_machineretryPatches($key, $rsitename, $searchValue, $db)
{

    $key = DASH_ValidateKey($key);

    if ($key) {

        $lableDisply = $rsitename . ':' . $searchValue;

        $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
        $sql->execute(['Wiz_APPR_PG ' . $lableDisply]);
        $sqlresult = $sql->fetch();
        $pgroupid = $sqlresult['pgroupid'];

        $sqlpatch = $db->prepare("select c.host,p.patchid,p.title,ps.`status`,ps.lasterror,p.type from " . $GLOBALS['PREFIX'] . "softinst.Patches as p join " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as m on p.patchid = m.patchid "
            . "join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as ps on p.patchid = ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census as C on .id = ps.id and m.pgroupid = ? and ps.status = 7 and C.site = ?");
        $sqlpatch->execute([$pgroupid, $rsitename]);
        $sqlretryres = $sqlpatch->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $sqlretryres;
}

function PATCH_groupretryPatches($key, $searchValue, $db)
{

    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name =?");
        $sql->execute(['Wiz_APPR_PG ' . $searchValue]);
        $sqlresult = $sql->fetch();
        $pgroupid = $sqlresult['pgroupid'];

        $sql = $db->prepare("select p.patchid,p.title,ps.`status`,ps.lasterror,p.type from " . $GLOBALS['PREFIX'] . "softinst.Patches as p "
            . " join " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as m on p.patchid = m.patchid join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as ps"
            . " on p.patchid = ps.patchid and m.pgroupid = ? and ps.status = 7");
        $sql->execute([$pgroupid]);
        $sqlretryres = $sql->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $sqlretryres;
}



function ELPATCH_GetPatches($db, $patchIds)
{
    global $elastic_url;

    $params = '{
                "query": {
                  "bool": {
                    "must": [
                      { "match": { "patchid":"' . $patchIds . '"}}
                    ]
                }
            }
        }';

    $patches = EL_GetCurl("patches", $params);
    $patchesData = EL_FormatCurldata($patches);
    return $patchesData;
}

function PATCH_GroupName($db, $grpid)
{
    $sql = $db->prepare("select mgroupid,name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ? limit 1");
    $sql->execute([$grpid]);
    $sqlres = $sql->fetch();

    return $sqlres['name'];
}

function GET_RefhreshCount($siteValue)
{
    $count = 0;

    $msql = $db->prepare("select mgroupid,name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
    $msql->execute([$siteValue]);
    $mquery = $msql->fetchAll();
    $total = safe_count($mquery);
    if ($total != 0) {
        while ($row = mysqli_fetch_assoc($mquery)) {
            $psql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid = ? and pgroupid = '1' limit 1");
            $psql->execute([$row['mgroupid']]);
            $pquery = $psql->fetchAll();
            $ptotal = safe_count($pquery);
            if ($ptotal == 0) {

                $date = time();
                $insert = $db->prepare("insert into
                PatchConfig set
                mgroupid = ?,
                pgroupid = 1,
                lastupdate = ?,
                installation = 1,
                preventshutdown = 0,
                reminduser = 0,
                configtype = 0,
                scheddelay = 0,
                schedminute = 0,
                schedhour = 22,
                schedday = 0,
                schedmonth = 0,
                schedweek = 7,
                schedrandom = 0,
                schedtype = 1,
                notifydelay = 0,
                notifyminute = 0,
                notifyhour = 16,
                notifyday = 0,
                notifymonth = 0,
                notifyweek = 7,
                notifyrandom = 0,
                notifytype = 1,
                notifyfail = 0,
                notifyadvance = 0,
                notifyschedule = 0,
                notifyadvancetime = 900,
                notifytext = '',
                wpgroupid = 1");
                $sql->execute([$row['mgroupid'], $row['mgroupid']]);
                $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ');
                foreach ($pcategory_id as $key => $value) {
                    $insert_patch_groups = $db->prepare("INSERT INTO `PatchGroups` (`pcategoryid`,`name`,`global`,`human`,`style`,`created`,`boolstring`,`whereclause`) VALUES (?,?,?,?,?,?,?,?)");
                    $insert_patch_groups->execute([$key, $value . $row['name'], 1, 0, 2, $date, '', '']);
                }
                $count++;
            }
        }
        echo $count;
    }
}

function PATCH_GetParentName($searchValue)
{

    $db = pdo_connect();
    $sql = $db->prepare("select site from " . $GLOBALS['PREFIX'] . "core.Census where host=? order by id desc limit 1");
    $sql->execute([$searchValue]);
    $sqlres = $sql->fetch();
    $site = $sqlres['site'];
    return $site;
}

function PATCH_GETCensusId($db, $dataScope, $level)
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $parentVal = $_SESSION["rparentName"];
    $db = pdo_connect();
    $res = array();
    if ($level == 'Sites') {
        $sql = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where site=?");
        $sql->execute([$dataScope]);
    } else if ($level == 'ServiceTag') {
        $sql = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where site=?");
        $sql->execute([$dataScope]);
    }
    $sqlres = $sql->fetchAll();

    foreach ($sqlres as $k => $v) {
        array_push($res, $v['id']);
    }
    $res = implode(',', $res);
    return $res;
}
