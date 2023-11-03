<?php







function RESOL_Get_ProactiveData($key, $db, $orderValues, $searchVal, $limit)
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

        if ($searchVal != '') {
            $append = " and ProfileName like '%$searchVal%'";
        } else {
            $append = "";
        }
        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        if (isset($_SESSION['user']['ptsEnabled']) && $_SESSION['user']['ptsEnabled'] == 1) {
            $auditCond = '';
        } else {
            $auditCond = "CustomerNO in ($custList) and OrderNO in ($ordList) and";
        }

        $roleName = $_SESSION["user"]["role_name"];
        if ($user_email == 'admin@nanoheal.com' || $roleName == 'dthree') {
            $emailCond = "";
        } else {
            $emailCond = " and AgentUniqId='$user_email'";
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {


            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof,SelectionType from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$searchValue' AND $auditCond JobType IN "
                . "('Interactive') $emailCond and JobStatus NOT IN (6,7) and JobCreatedTime >= '$from' $append group by ProfileName $orderValues $limit";
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

            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof,SelectionType from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) or MachineTag IN ($machines)) AND $auditCond JobType IN "
                . "('Interactive') $emailCond and JobStatus NOT IN (6,7) and JobCreatedTime >= '$from' $append group by ProfileName $orderValues $limit";
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

            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof,SelectionType from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) or MachineTag IN ($machines)) AND $auditCond JobType IN "
                . "('Interactive') $emailCond and JobStatus NOT IN (6,7) and JobCreatedTime >= '$from' $append group by ProfileName $orderValues $limit";
        }
        mysqli_query("set names 'utf8'");
        $proactiveRes = find_many($proactiveSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $proactiveRes;
}

function RESOL_GetScheduleData($keys, $db, $orderValues, $searchVal, $limit)
{

    $proactiveRes = [];
    $key = DASH_ValidateKey($keys);
    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $user = $_SESSION["user"]["username"];
        $user_email = $_SESSION["user"]["adminEmail"];
        $userId = $_SESSION["user"]["userid"];
        $from = time() - (15 * 24 * 60 * 60);
        $append = "";

        if ($searchVal != '') {
            $append = " and ProfileName like '%$searchVal%'";
        } else {
            $append = "";
        }
        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {


            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$searchValue' AND CustomerNO in ($custList) and OrderNO in ($ordList) and JobType IN "
                . "('Notification','Interactive') and AgentUniqId='$user_email' and JobStatus = '0' and JobCreatedTime >= '$from' $append $orderValues $limit";
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

            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) or MachineTag IN ($machines)) AND CustomerNO in ($custList) and OrderNO in ($ordList) and JobType IN "
                . "('Notification','Interactive') and AgentUniqId='$user_email' and JobStatus = '0' and JobCreatedTime >= '$from' $append $orderValues $limit";
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

            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) or MachineTag IN ($machines)) AND CustomerNO in ($custList) and OrderNO in ($ordList) and JobType IN "
                . "('Notification','Interactive') and AgentUniqId='$user_email' and JobStatus = '0' and JobCreatedTime >= '$from' $append $orderValues $limit";
        }
        mysqli_query("set names 'utf8'");
        $proactiveRes = find_many($proactiveSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $proactiveRes;
}

function getUserSiteList($db, $userid)
{

    $sql_cust = "select customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where siteName in (select C.customer  from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username=U.username and U.userid = '$userid') group by siteName";
    $sqlRes = find_many($sql_cust, $db);
    $custList = '';
    $ordList = '';
    foreach ($sqlRes as $res) {
        $custNo = $res['customerNum'];
        $ordNo = $res['orderNum'];

        $custList .= "'" . $custNo . "',";
        $ordList .= "'" . $ordNo . "',";
    }
    $custList = rtrim($custList, ',');
    $ordList = rtrim($ordList, ',');
    return array("custNo" => $custList, "ordNo" => $ordList);
}



function RESOL_Get_PredictiveData($key, $db, $orderValues, $searchVal, $limit)
{
    $predictiveRes = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $from = time() - (15 * 24 * 60 * 60);

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        $rParent = $_SESSION['rparentName'];

        if (is_numeric($rParent)) {
            $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
            $groupres = find_one($groupsql, $db);
            $rParent = $groupres['name'];
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $machines = $searchValue;
            if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
                $rCensusId = $_SESSION['rcensusId'];
                $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
                $uuidres = find_one($uuidsql, $db);
                $uuid = $uuidres['uuid'];

                $sql_query = "(select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,EI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI ON ebd.itemid = EI.eventitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where EI.enabled = 1 and EI.name = '' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 9 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,RI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI ON RI.resitemid = ebd.itemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where RI.enabled=1 and RI.name ='' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 8 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems MI ON ebd.itemid = MI.monitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where MI.enabled=1 and MI.name = '' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 5 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,SI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI ON ebd.itemid = SI.secitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where SI.enabled=1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 7 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MA.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems MA ON ebd.itemid = MA.maintitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where MA.enabled=1 and MA.name in ('Scandisk succeeded','File cleanup') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 10 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select idx as dispid,aidx as status,count(aidx) as eventcount,DATE_FORMAT(from_unixtime(servertime), '%W, %M %d, %Y %H:%i:%s') servertime,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name,machine as host from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.scrip='69' and AE.uuid = '$uuid' and AE.servertime > '$from' group by text1)
                " . $orderValues;
            } else {
                $sql_query = "(select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,EI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI ON ebd.itemid = EI.eventitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where EI.enabled = 1 and EI.name = '' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 9 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,RI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI ON RI.resitemid = ebd.itemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where RI.enabled=1 and RI.name ='' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 8 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems MI ON ebd.itemid = MI.monitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where MI.enabled=1 and MI.name = '' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 5 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,SI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI ON ebd.itemid = SI.secitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where SI.enabled=1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 7 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MA.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems MA ON ebd.itemid = MA.maintitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where MA.enabled=1 and MA.name in ('Scandisk succeeded','File cleanup') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 10 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select idx as dispid,aidx as status,count(aidx) as eventcount,DATE_FORMAT(from_unixtime(servertime), '%W, %M %d, %Y %H:%i:%s') servertime,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name,machine as host from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.scrip='69' and AE.machine IN ('$machines') and AE.customer = '" . $rParent . "' and AE.servertime > '$from' group by text1)
                " . $orderValues;
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {

            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'" . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'$searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $sql_query = "(SELECT ebd.dispid,ebd.status,ebd.itemtype,itemid,ebd.crithtml,mi.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems mi
                ON mi.monitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE mi.enabled =1 and mi.name = '' and
                ebd.itemtype =5 and ebd.servertime >= '$from' and
                ebd.userid = " . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                $orderValues)
                UNION
                (SELECT ebd.dispid,ebd.status,ebd.itemtype,itemid,ebd.crithtml,mi.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems mi
                ON mi.maintitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE mi.enabled=1 and mi.name in ('Scandisk succeeded','File cleanup') and
                ebd.itemtype =10 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                $orderValues)
                UNION
                (SELECT ebd.dispid,ebd.status,ebd.itemtype,itemid,ebd.crithtml,RI.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI
                ON RI.resitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE RI.enabled=1 and RI.name ='' and
                ebd.itemtype =8 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                $orderValues)
                UNION
                (SELECT ebd.dispid,ebd.status,ebd.itemtype,itemid,ebd.crithtml,SI.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI
                ON SI.secitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE SI.enabled=1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and
                ebd.itemtype =7 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                $orderValues)
                UNION
                (SELECT ebd.dispid,ebd.status,ebd.itemtype,itemid,ebd.crithtml,EI.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI
                ON EI.eventitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE EI.enabled=1 and EI.name = '' and
                ebd.itemtype =9 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                $orderValues)
                UNION
                (select idx as dispid,aidx as status,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name  from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.scrip='69' and AE.machine in ($machines) and AE.customer IN ($selectionType) and AE.servertime > '$from' group by text1)
                " . $orderValues;
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines1 = "'" . implode("','", $data) . "'";
            $machines = "'" . implode("','", safe_array_keys($data)) . "'";
            $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
            $uuid = "'" . implode("','", $uuiddata) . "'";
            $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
            $custName = "'" . implode("','", $sitedata) . "'";

            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'" . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'$searchValue'";
            }

            $sql_query = "(select ebd.dispid,ebd.status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,EI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI ON ebd.itemid = EI.eventitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where EI.enabled = 1 and EI.name = '' and ebd.censusid in ($machines) and ebd.itemtype= 9 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,RI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI ON RI.resitemid = ebd.itemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where RI.enabled=1 and RI.name ='' and ebd.censusid in ($machines) and ebd.itemtype= 8 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems MI ON ebd.itemid = MI.monitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where MI.enabled=1 and MI.name = '' and ebd.censusid in ($machines) and ebd.itemtype= 5 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,SI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI ON ebd.itemid = SI.secitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where SI.enabled=1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and ebd.censusid in ($machines) and ebd.itemtype= 7 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MA.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems MA ON ebd.itemid = MA.maintitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where MA.enabled=1 and MA.name in ('Scandisk succeeded','File cleanup') and ebd.censusid in ($machines) and ebd.itemtype= 10 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select idx as dispid,aidx as status,count(aidx) as eventcount,DATE_FORMAT(from_unixtime(servertime), '%W, %M %d, %Y %H:%i:%s') servertime,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name,machine as host  from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.scrip='69' and AE.uuid IN ($uuid) and AE.customer IN ($selectionType) and AE.servertime > '$from' group by text1)
                " . $orderValues;
        }
        mysqli_query("set names 'utf8'");
        $predictiveRes = find_many($sql_query, $db);
    } else {
        echo "Your key has been expired";
    }
    return $predictiveRes;
}

function RESOL_Get_PredictiveDataCount($key, $db, $orderValues, $searchVal, $limit)
{
    $predictiveRes = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $from = time() - (15 * 24 * 60 * 60);

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        $rParent = $_SESSION['rparentName'];
        if (is_numeric($rParent)) {
            $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
            $groupres = find_one($groupsql, $db);
            $rParent = $groupres['name'];
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $machines = $searchValue;
            if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
                $rCensusId = $_SESSION['rcensusId'];
                $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
                $uuidres = find_one($uuidsql, $db);
                $uuid = $uuidres['uuid'];
                $sql = "(select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,EI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI ON ebd.itemid = EI.eventitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where EI.enabled = 1 and EI.name = '' and ebd.censusid = {$_SESSION['rcensusId']} and ebd.itemtype = 9 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,RI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI ON RI.resitemid = ebd.itemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where RI.enabled=1 and RI.name ='' and ebd.censusid = {$_SESSION['rcensusId']} and ebd.itemtype = 8 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems MI ON ebd.itemid = MI.monitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where MI.enabled=1 and MI.name = '' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 5 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,SI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI ON ebd.itemid = SI.secitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where SI.enabled = 1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 7 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MA.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems MA ON ebd.itemid = MA.maintitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where MA.enabled=1 and MA.name in ('Scandisk succeeded','File cleanup') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 10 and ebd.servertime >= '$from' group by ebd.itemid)
               UNION
                (select idx as dispid, aidx as status,count(aidx) as eventcount,DATE_FORMAT(from_unixtime(servertime), '%W, %M %d, %Y %H:%i:%s') servertime,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name,machine as host from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.scrip='69' and AE.uuid = '$uuid' and AE.servertime > '$from' group by text1)";
            } else {
                $sql = "(select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,EI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI ON ebd.itemid = EI.eventitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where EI.enabled = 1 and EI.name = '' and ebd.censusid = {$_SESSION['rcensusId']} and ebd.itemtype = 9 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,RI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI ON RI.resitemid = ebd.itemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where RI.enabled=1 and RI.name ='' and ebd.censusid = {$_SESSION['rcensusId']} and ebd.itemtype = 8 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems MI ON ebd.itemid = MI.monitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where MI.enabled=1 and MI.name = '' and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 5 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,SI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI ON ebd.itemid = SI.secitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where SI.enabled = 1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 7 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select ebd.dispid,ebd.eventcount as status,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MA.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems MA ON ebd.itemid = MA.maintitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where MA.enabled=1 and MA.name in ('Scandisk succeeded','File cleanup') and ebd.censusid={$_SESSION['rcensusId']} and ebd.itemtype= 10 and ebd.servertime >= '$from' group by ebd.itemid)
                UNION
                (select idx as dispid, aidx as status,count(aidx) as eventcount,DATE_FORMAT(from_unixtime(servertime), '%W, %M %d, %Y %H:%i:%s') servertime,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name,machine as host  from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where  AE.scrip='69' and AE.machine IN ('$machines') and AE.customer = '" . $rParent . "' and AE.servertime > '$from' group by text1)";
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {

            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'" . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'$searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $sql = "(SELECT ebd.dispid,ebd.itemtype,itemid,ebd.crithtml,mi.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems mi
                ON mi.monitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE mi.enabled =1 and mi.name = '' and
                ebd.itemtype =5 and ebd.servertime >= '$from' and
                ebd.userid = " . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                ORDER BY status desc)
                UNION
                (SELECT ebd.dispid,ebd.itemtype,itemid,ebd.crithtml,mi.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems mi
                ON mi.maintitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE mi.enabled=1 and mi.name in ('Scandisk succeeded','File cleanup') and
                ebd.itemtype =10 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                ORDER BY status desc)
                UNION
                (SELECT ebd.dispid,ebd.itemtype,itemid,ebd.crithtml,RI.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI
                ON RI.resitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE RI.enabled=1 and RI.name ='' and
                ebd.itemtype =8 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                ORDER BY status desc)
                UNION
                (SELECT ebd.dispid,ebd.itemtype,itemid,ebd.crithtml,SI.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI
                ON SI.secitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE SI.enabled=1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and
                ebd.itemtype =7 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and host in ($machines)
                group by ebd.itemid
                ORDER BY status desc)
                UNION
                (SELECT ebd.dispid,ebd.itemtype,itemid,ebd.crithtml,EI.name  FROM
                " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI
                ON EI.eventitemid=ebd.itemid
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "core.Census c
                ON c.id=ebd.censusid
                WHERE EI.enabled=1 and EI.name = '' and
                ebd.itemtype =9 and ebd.servertime >= '$from' and
                ebd.userid=" . $_SESSION['user']['adminid'] . " and c.host in ($machines)
                group by ebd.itemid
                ORDER BY status desc)
                UNION
                (select idx as dispid,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name  from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.scrip='69' and AE.machine in ($machines) and AE.customer IN ($selectionType) and AE.servertime > '$from' group by text1)";
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
            $uuid = "'" . implode("','", $uuiddata) . "'";
            $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
            $custName = "'" . implode("','", $sitedata) . "'";

            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'" . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'$searchValue'";
            }

            $sql = "(select ebd.dispid,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,EI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.EventItems EI ON ebd.itemid = EI.eventitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where EI.enabled = 1 and EI.name = '' and ebd.censusid in ($machines) and ebd.itemtype = 9 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,RI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.ResourceItems RI ON RI.resitemid = ebd.itemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where RI.enabled=1 and RI.name ='' and ebd.censusid in ($machines) and ebd.itemtype = 8 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MonitorItems MI ON ebd.itemid = MI.monitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where MI.enabled=1 and MI.name = '' and ebd.censusid in ($machines) and ebd.itemtype= 5 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,SI.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.SecurityItems SI ON ebd.itemid = SI.secitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id = ebd.censusid
                where SI.enabled = 1 and SI.name IN ('Intrusion protection item rejected','Intrusion protection startup item rejected','Intrusion protection config item rejected') and ebd.censusid in ($machines) and ebd.itemtype= 7 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select ebd.dispid,ebd.eventcount,DATE_FORMAT(from_unixtime(ebd.servertime), '%W, %M %d, %Y %H:%i:%s') servertime,ebd.itemtype,ebd.itemid,ebd.crithtml,MA.name,C.host from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd
                RIGHT JOIN " . $GLOBALS['PREFIX'] . "dashboard.MaintenanceItems MA ON ebd.itemid = MA.maintitemid
                LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census C ON C.id=ebd.censusid
                where MA.enabled=1 and MA.name in ('Scandisk succeeded','File cleanup') and ebd.censusid in ($machines) and ebd.itemtype= 10 and ebd.servertime >= '$from' group by ebd.itemid ORDER BY status desc)
                UNION
                (select idx as dispid,count(aidx) as eventcount,DATE_FORMAT(from_unixtime(servertime), '%W, %M %d, %Y %H:%i:%s') servertime,'autohealevent' as itemtype,1000 as itemid,text1 as crithtml,description as name,machine as host  from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE AE.scrip='69' and where AE.uuid IN ($uuid) and AE.customer IN ($selectionType) and AE.servertime > '$from' group by text1)";
        }
        mysqli_query("set names 'utf8'");
        $predictiveRes = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $predictiveRes;
}

function RESOL_Get_PredictiveGridData($key, $db, $temp0, $temp1, $temp2, $orderValues, $where, $limit)
{
    $predictiveRes = [];
    $key = DASH_ValidateKey($key);

    db_change($GLOBALS['PREFIX'] . 'dashboard', $db);

    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $from = time() - (15 * 24 * 60 * 60);

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        $rParent = $_SESSION['rparentName'];
        $passParent = $rParent;
        if (is_numeric($rParent)) {
            $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
            $groupres = find_one($groupsql, $db);
            $rParent = $groupres['name'];
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $machines = $searchValue;
            if ($temp2 == 'autohealevent') {
                $sql_1 = "select text1 from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.aidx='$temp0'";
                $result_1 = find_one($sql_1, $db);
                $nameVal = $result_1['text1'];
                if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
                    $rCensusId = $_SESSION['rcensusId'];
                    $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
                    $uuidres = find_one($uuidsql, $db);
                    $uuid = $uuidres['uuid'];
                    $sql_query = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1,count(aidx) as eventcount,count(aidx) as itemcount,servertime,machine from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.uuid = '$uuid' and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,machine " . $where . $orderValues . $limit;
                } else {
                    $sql_query = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1,count(aidx) as eventcount,count(aidx) as itemcount,servertime,AE.machine from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.machine= '$machines' and AE.customer = '" . $rParent . "' and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,machine " . $where . $orderValues . $limit;
                }
            } else {
                $sql_query = "SELECT ebd.dispid,ebd.userid, itemid,ebd.itemtype,c.id as censusid,status stat,c.site site,c.host machine
    ,ebd.eventcount eventcount,ebd.itemcount,ebd.crithtml as text1,ebd.servertime FROM
    " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd,
    " . $GLOBALS['PREFIX'] . "core.Census c
    WHERE c.id=ebd.censusid and ebd.itemtype =$temp2  and
             ebd.userid=" . $_SESSION['user']['adminid'] . " and ebd.itemid =$temp1 and c.host = '$machines'" . $where . $orderValues . $limit;
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {

            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'" . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'$searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $searchValue = "'" . implode("','", $data) . "'";
            if ($temp2 == 'autohealevent') {
                $sql_1 = "select text1 from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.aidx='$temp0'";
                $result_1 = find_one($sql_1, $db);
                $nameVal = $result_1['text1'];

                $sql_query = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1,count(aidx) as eventcount,count(aidx) as itemcount,servertime from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.machine in ($searchValue) and AE.customer IN ($selectionType) and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,machine " . $where . $orderValues . $limit;
            } else {
                $sql_query = "SELECT ebd.dispid,ebd.userid, itemid,ebd.itemtype,c.id as censusid,status stat,c.site site,c.host machine
    ,ebd.eventcount eventcount,ebd.itemcount,ebd.crithtml as text1,ebd.servertime FROM
    " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd,
    " . $GLOBALS['PREFIX'] . "core.Census c
    WHERE c.id=ebd.censusid and ebd.itemtype =$temp2  and
             ebd.userid=" . $_SESSION['user']['adminid'] . " and ebd.itemid =$temp1 and c.host in ($searchValue)" . $where . " " . $orderValues . $limit;
            }
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
            $uuid = "'" . implode("','", $uuiddata) . "'";
            $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
            $custName = "'" . implode("','", $sitedata) . "'";

            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'" . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'$searchValue'";
            }

            if ($temp2 == 'autohealevent') {
                $sql_1 = "select text1 from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.aidx='$temp0'";
                $result_1 = find_one($sql_1, $db);
                $nameVal = $result_1['text1'];
                $sql_query = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1,count(aidx) as eventcount,count(aidx) as itemcount,servertime from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.uuid in ($uuid) and AE.customer in ($selectionType) and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,uuid " . $where . $orderValues . $limit;
            } else {
                $sql_query = "SELECT ebd.dispid,ebd.userid, itemid,ebd.itemtype,c.id as censusid,status stat,c.site site,c.host machine
    ,ebd.eventcount eventcount,ebd.itemcount,ebd.crithtml as text1,ebd.servertime FROM
    " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd,
    " . $GLOBALS['PREFIX'] . "core.Census c
    WHERE c.id=ebd.censusid and ebd.itemtype =$temp2  and
             ebd.userid=" . $_SESSION['user']['adminid'] . " and ebd.itemid =$temp1 and c.host in ($machines)" . $where . " " . $orderValues . $limit;
            }
        }
        $predictiveRes = find_many($sql_query, $db);
    } else {
        echo "Your key has been expired";
    }
    return $predictiveRes;
}

function RESOL_Get_PredictiveGridDataCount($key, $db, $temp0, $temp1, $temp2)
{
    $predictiveCountRes = [];
    $key = DASH_ValidateKey($key);

    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $from = time() - (15 * 24 * 60 * 60);

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        $rParent = $_SESSION['rparentName'];
        $passParent = $rParent;
        if (is_numeric($rParent)) {
            $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
            $groupres = find_one($groupsql, $db);
            $rParent = $groupres['name'];
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $machines = $searchValue;
            if ($temp2 == 'autohealevent') {
                $sql_1 = "select text1 from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.aidx='$temp0'";
                $result_1 = find_one($sql_1, $db);
                $nameVal = $result_1['text1'];
                if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
                    $rCensusId = $_SESSION['rcensusId'];
                    $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
                    $uuidres = find_one($uuidsql, $db);
                    $uuid = $uuidres['uuid'];
                    $sql = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1 as crithtml,count(aidx) as eventcount,count(aidx) as itemcount,servertime from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.uuid= '$uuid' and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,machine";
                } else {
                    $sql = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1 as crithtml,count(aidx) as eventcount,count(aidx) as itemcount,servertime from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.machine= '$machines' and AE.customer = '" . $rParent . "' and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,machine";
                }
            } else {
                $sql = "SELECT ebd.dispid,ebd.userid, itemid,ebd.itemtype,c.id as censusid,status stat,c.site site,c.host machine
    ,ebd.eventcount eventcount,ebd.itemcount,ebd.crithtml,ebd.servertime FROM
    " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd,
    " . $GLOBALS['PREFIX'] . "core.Census c
    WHERE c.id=ebd.censusid and ebd.itemtype =$temp2  and
             ebd.userid=" . $_SESSION['user']['adminid'] . " and ebd.itemid =$temp1 and c.host = '$machines'";
            }
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {

            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'" . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'$searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $searchValue = "'" . implode("','", $data) . "'";
            if ($temp2 == 'autohealevent') {
                $sql_1 = "select text1 from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.aidx='$temp0'";
                $result_1 = find_one($sql_1, $db);
                $nameVal = $result_1['text1'];

                $sql = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1 as crithtml,count(aidx) as eventcount,count(aidx) as itemcount,servertime from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.machine in ($searchValue) and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.customer IN ($selectionType) and AE.servertime > '$from' group by text1,machine";
            } else {

                $sql = "SELECT ebd.dispid,ebd.userid, itemid,ebd.itemtype,c.id as censusid,status stat,c.site site,c.host machine
    ,ebd.eventcount eventcount,ebd.itemcount,ebd.crithtml,ebd.servertime FROM
    " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd,
    " . $GLOBALS['PREFIX'] . "core.Census c
    WHERE c.id=ebd.censusid and ebd.itemtype =$temp2  and
             ebd.userid=" . $_SESSION['user']['adminid'] . " and ebd.itemid =$temp1 and c.host in ($searchValue)";
            }
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $uuiddata = DASH_GetGroupsUUID($key, $db, $dataScope);
            $uuid = "'" . implode("','", $uuiddata) . "'";
            $sitedata = DASH_GetGroupsCustName($key, $db, $uuid);
            $custName = "'" . implode("','", $sitedata) . "'";

            if ($searchValue == "All") {
                foreach ($dataScope as $key => $res) {
                    $key = UTIL_GetTrimmedGroupName($key);
                    $selectionValue .= "'" . $key . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $searchValue = $_SESSION['rparentName'];
                $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                $selectionType = "'$searchValue'";
            }

            if ($temp2 == 'autohealevent') {
                $sql_1 = "select text1 from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.aidx='$temp0'";
                $result_1 = find_one($sql_1, $db);
                $nameVal = $result_1['text1'];
                $sql = "select idx as dispid,username as userid,'autohealevent' as itemtype,1000 as itemid,aidx as censusid,customer as site,machine,text1 as crithtml,count(aidx) as eventcount,count(aidx) as itemcount,servertime from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal AE where AE.uuid in ($uuid) and AE.customer in ($selectionType) and text1='" . mysqli_real_escape_string($nameVal) . "' and AE.servertime > '$from' group by text1,uuid";
            } else {
                $sql = "SELECT ebd.dispid,ebd.userid, itemid,ebd.itemtype,c.id as censusid,status stat,c.site site,c.host machine
    ,ebd.eventcount eventcount,ebd.itemcount,ebd.crithtml,ebd.servertime FROM
    " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay ebd,
    " . $GLOBALS['PREFIX'] . "core.Census c
    WHERE c.id=ebd.censusid and ebd.itemtype =$temp2  and
             ebd.userid=" . $_SESSION['user']['adminid'] . " and ebd.itemid =$temp1 and c.host in ($machines)";
            }
        }
        $predictiveCountRes = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $predictiveCountRes;
}

function RESOL_Get_ServicesListData($key, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $listSQL = "SELECT parent_name FROM " . $GLOBALS['PREFIX'] . "profile.WizardNameNH WHERE profile_type = 2 GROUP BY parent_name ORDER BY CASE parent_name
                WHEN 'System Scanner' THEN 1
                WHEN 'Real Time Protection' THEN 2
                WHEN 'Web Protection' THEN 3
                WHEN 'Mail Protection' THEN 4
                WHEN 'Update and Proxy Settings' THEN 5
                WHEN 'Device Protection' THEN 6
                WHEN 'Threat Categories' THEN 7
                WHEN 'Advanced Protection' THEN 8
                WHEN 'Password' THEN 9
                WHEN 'Security' THEN 10
                WHEN 'Scheduler' THEN 11
                END";
        $listRES = find_many($listSQL, $db);
    } else {
        echo "Your key has been expired";
    }
    return $listRES;
}

function RESOL_Get_ServicesGridData($key, $db, $username, $ProfileName)
{
    $servicesGridRes = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = url::requestToAny('site');
        $from = time() - (15 * 24 * 60 * 60);
        $cond = "";
        if ($ProfileName == "Scheduler") {
            $cond = "";
        } else {
            $cond = " and ProfileName = '$ProfileName'";
        }

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $selectionValue .= "Machine : " . $searchValue . "";
            $servicesGridSql = "select AID,BID,MachineTag,SelectionType,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType = '$selectionValue' "
                . "and JobType = 'Wmi Command' and JobCreatedTime >= '$from' $cond and AgentName = '$username'";
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

            $servicesGridSql = "select AID,BID,MachineTag,SelectionType,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType in ($selectionType) "
                . "and JobType = 'Wmi Command' and JobCreatedTime >= '$from' $cond and AgentName = '$username' ";
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'Group : " . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'Group : $searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $servicesGridSql = "select AID,BID,MachineTag,SelectionType,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where SelectionType in ($selectionType) "
                . "and JobType = 'Wmi Command' and JobCreatedTime >= '$from' $cond and AgentName = '$username' ";
        }
        $servicesGridRes = find_many($servicesGridSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $servicesGridRes;
}

function RESOL_Get_NHConfigListData($key, $db)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $listSQL = "SELECT parent_name,Id FROM " . $GLOBALS['PREFIX'] . "profile.WizardNameNH WHERE profile_type = 1 GROUP BY parent_name ORDER BY CASE parent_name
                WHEN 'Problem Automation' THEN 1
                WHEN 'Device Management' THEN 2
                WHEN 'Device Policies' THEN 3
                WHEN 'System Management' THEN 4
                WHEN 'Software Update' THEN 5
                WHEN 'Proactive Resolution' THEN 6
                END";
        $listRES = find_many($listSQL, $db);
    } else {
        echo "Your key has been expired";
    }
    return $listRES;
}

function RESOL_Get_NHConfigGridData($key, $db, $username, $ProfileName, $searchtype, $searchval)
{
    $servicesGridRes = [];
    $key = DASH_ValidateKey($key);
    $from = time() - (15 * 24 * 60 * 60);
    if ($key) {
        $WNsql = "SELECT Name,Id FROM " . $GLOBALS['PREFIX'] . "profile.WizardNameNH WHERE parent_name = '" . $ProfileName . "'";
        $WNres = find_many($WNsql, $db);

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $cond = " AND Level = '" . $searchval . "' ";
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            $cond = " AND Level = '" . $searchval . "' ";
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            $rParent = $_SESSION['rparentName'];
            if (is_numeric($rParent)) {
                $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
                $groupres = find_one($groupsql, $db);
                $rParent = $groupres['name'];
            }
            $cond = " AND Level = '" . $rParent . "' ";
        }

        foreach ($WNres as $key => $val) {
            $WVsql = "SELECT MasterId,Ordr,Level,AgentName,ConfigPushTime FROM " . $GLOBALS['PREFIX'] . "profile.wizardValues WHERE wn_id = '" . $val['Id'] . "' $cond LIMIT 1";
            $WVres = find_one($WVsql, $db);

            if (safe_count($WVres) > 0) {
                if ($val['Name'] == "") {
                    $Name = "--";
                } else {
                    $Name = $val['Name'];
                }
                if ($WVres['Level'] == "") {
                    $Level = "--";
                } else {
                    $Level = UTIL_GetTrimmedGroupName($WVres['Level']);
                }
                if ($WVres['AgentName'] == "") {
                    $AgentName = "--";
                } else {
                    $agentname = explode("_", $WVres['AgentName']);
                    $AgentName = $agentname[0];
                }
                if ($WVres['ConfigPushTime'] == "" || $WVres['ConfigPushTime'] == null) {
                    $ConfPushTime = "--";
                } else {
                    $ConfPushTime = date("m/d/Y h:i A", $WVres['ConfigPushTime']);
                }

                $onClick = "ViewProfileDetails(&quot;" . $val['Id'] . "&quot;,&quot;" . $val['Name'] . "&quot;)";
                $style = "cursor: pointer; color: #008bbc;";

                $Detail = '<a href="javascript:;" style="' . $style . '" onclick="' . $onClick . '">Details</a>';
                $servicesGridRes[] = array(0 => $Name, 1 => $AgentName, 2 => $Level, 3 => $ConfPushTime, 4 => $Detail);
            }
        }
        return $servicesGridRes;
    } else {
        echo "Your key has been expired";
    }
}

function RESOL_Get_NHConfigProfileDetails($key, $db, $ProfileName, $searchtype, $searchval)
{


    $sql = "select * from " . $GLOBALS['PREFIX'] . "profile.WizardNameNH wn where  wn.parent_name='" . $ProfileName . "'";

    $result = find_many($sql, $db);
    $sql_wm = '';
    if (safe_count($result) > 0) {

        foreach ($result as $key => $val) {
        }

        $dart_list = explode(",", $result['profiles']);

        $jsondataID = safe_json_decode($result['variables'], TRUE);

        if (!empty($jsondataID["var"])) {
            for ($x = 0; $x < safe_count($dart_list); $x++) {
                foreach ($jsondataID["var"] as $key => $value) {
                    $var_list = str_replace(",", "','", $value[$dart_list[$x]]);
                    $sql_wm .= "select * from " . $GLOBALS['PREFIX'] . "profile.WizardMasterNH wm where wm.VarID in ('" . $var_list . "') and wm.DartNo in ('" . $dart_list[$x] . "') group by wm.MasterId " . "\r\n" . "union ";
                }
            }
        } else {
            echo "empty";
        }

        $sql_wm = substr($sql_wm, 0, -6);

        $result_wm = find_many($sql_wm, $db);
        echo json_encode($result_wm);
    } else {
        echo "noresult";
    }
}

function RESOL_Get_AviraSchedulerData($key, $db)
{
    $schedulerGridRes = [];
    $key = DASH_ValidateKey($key);
    if ($key) {
        $searchtype = $_SESSION["searchType"];
        $searchValue = url::requestToAny('site');

        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        $rParent = $_SESSION['rparentName'];
        if (is_numeric($rParent)) {
            $groupsql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $rParent ";
            $groupres = find_one($groupsql, $db);
            $rParent = $groupres['name'];
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {
            $selectionValue .= $searchValue;

            $schedulerGridSql = "SELECT Id,Name,Action,Frequency,DisplayMode,Enabled,Status "
                . "FROM " . $GLOBALS['PREFIX'] . "profile.WizardAviraScheduler WHERE ActionLevel in ('$selectionValue','$rParent') AND ActionParent = '$rParent'";
        } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'" . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'$searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $schedulerGridSql = "SELECT Id,Name,Action,Frequency,DisplayMode,Enabled,Status "
                . "FROM " . $GLOBALS['PREFIX'] . "profile.WizardAviraScheduler WHERE ActionLevel in ($selectionType) AND ActionParent = '$rParent'";
        } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
            if ($searchValue == "All") {
                foreach ($dataScope as $res) {
                    $selectionValue .= "'" . $res . "',";
                }
                $selectionType = rtrim($selectionValue, ',');
            } else {
                $selectionType = "'$searchValue'";
            }

            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $schedulerGridSql = "SELECT Id,Name,Action,Frequency,DisplayMode,Enabled,Status "
                . "FROM " . $GLOBALS['PREFIX'] . "profile.WizardAviraScheduler WHERE ActionLevel in ($selectionType) AND ActionParent = '$rParent'";
        }
        $schedulerGridRes = find_many($schedulerGridSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $schedulerGridRes;
}

function RESOL_GETManageJobsData($keys, $db, $type, $orderValues, $searchVal, $limit)
{
    $manageJobsRes = [];
    $key = DASH_ValidateKey($keys);
    if ($key) {

        $searchtype = $_SESSION["searchType"];
        $searchValue = $_SESSION["searchValue"];
        $user_email = $_SESSION["user"]["adminEmail"];
        $userId = $_SESSION["user"]["userid"];
        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);
        $append = "";
        if ($type != 4) {
            if ($type == 1) {
                $typeval = "Interactive";
            } else if ($type == 2) {
                $typeval = "Notification";
            } else if ($type == 3) {
                $typeval = "Software Distribution";
            }
            if ($searchVal != '') {
                $append = " AND (ProfileName like '%$searchVal%' OR AgentName like '%$searchVal%' OR MachineTag like '%$searchVal%' OR SelectionType like '%$searchVal%') ";
            } else {
                $append = "";
            }
            if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

                $jobssql = "select AID,BID,MachineTag,SelectionType,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                    . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$searchValue' AND CustomerNO in ($custList) "
                    . "and OrderNO in ($ordList) and JobType = '$typeval' and AgentUniqId='$user_email' and JobStatus = '0' $append $orderValues $limit";
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
                $jobssql = "select AID,BID,MachineTag,JobCreatedTime,SelectionType,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                    . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) AND MachineTag IN ($machines)) AND CustomerNO in ($custList) and OrderNO in ($ordList) and JobType IN "
                    . "('$typeval') and AgentUniqId='$user_email' and JobStatus = '0' $append $orderValues $limit";
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

                $jobssql = "select AID,BID,MachineTag,SelectionType,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                    . "JobStatus,DartExecutionProof from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) AND MachineTag IN ($machines)) AND CustomerNO in ($custList) and OrderNO in ($ordList) and JobType IN "
                    . "('$typeval') and AgentUniqId='$user_email' and JobStatus = '0' $append $orderValues $limit";
            }
        } else {
            if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {

                $jobssql = "SELECT sid,machineOS,MobileID,serviceTag FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest where serviceTag = '" . $searchValue . "' and machineOS IN ('Android','iOS') $append $orderValues $limit";
            } else if ($searchtype == 'Site' || $searchtype == 'Sites') {
                if ($searchValue == "All") {
                    foreach ($dataScope as $res) {
                        $selectionValue .= "'" . $res . "',";
                    }
                    $selectionType = rtrim($selectionValue, ',');
                } else {
                    $selectionType = "'$searchValue'";
                }
                $data = DASH_GetMachinesSites($key, $db, $dataScope);
                $machines = "'" . implode("','", $data) . "'";

                $jobssql = "SELECT sid,machineOS,MobileID,serviceTag FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest where siteName in (" . $selectionType . ") and machineOS IN ('Android','iOS') $append $orderValues $limit";
            } else if ($searchtype == 'Group' || $searchtype == 'Groups') {
                if ($searchValue == "All") {
                    foreach ($dataScope as $key => $res) {
                        $key = UTIL_GetTrimmedGroupName($key);
                        $selectionValue .= "'" . $key . "',";
                    }
                    $selectionType = rtrim($selectionValue, ',');
                } else {
                    $searchValue = $_SESSION['rparentName'];
                    $searchValue = UTIL_GetTrimmedGroupName($searchValue);
                    $selectionType = "'$searchValue'";
                }
                $jobssql = "SELECT sid,machineOS,MobileID,serviceTag FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest where siteName in (" . $selectionType . ") and machineOS IN ('Android','iOS') $append $orderValues $limit";
            }
        }
        $manageJobsRes = find_many($jobssql, $db);
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }
    return $manageJobsRes;
}

function RESOL_GetGCMIDFn($keys, $db, $id)
{
    $key = DASH_ValidateKey($keys);
    $data = [];
    if ($key) {
        $sql = "SELECT sid,MobileID,serviceTag,machineOS FROM " . $GLOBALS['PREFIX'] . "agent.serviceRequest WHERE sid = $id";
        $res = find_one($sql, $db);
        $data['sid'] = $res['sid'];
        $data['MobileID'] = $res['MobileID'];
        $data['serviceTag'] = $res['serviceTag'];
        $data['machineOS'] = $res['machineOS'];
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }
    return $data;
}

function RESOL_SubmitGCMIDFn($keys, $db, $id, $gcmid)
{
    $key = DASH_ValidateKey($keys);
    if ($key) {
        $editsql = "UPDATE " . $GLOBALS['PREFIX'] . "agent.serviceRequest SET MobileID = '" . $gcmid . "' WHERE sid = $id";
        $editres = redcommand($editsql, $db);
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }

    if ($editres) {
        return "success";
    } else {
        return "failed";
    }
}

function RESOL_DeleteJobsFromAuditFn1($keys, $db, $ids)
{
    $key = DASH_ValidateKey($keys);

    if ($key) {

        try {
            global $redis_url;
            global $redis_port;
            global $redis_pwd;

            $redis = new Redis();
            $redis->connect($redis_url, $redis_port);
            $redis->auth($redis_pwd);

            $db = db_connect();
            db_change($GLOBALS['PREFIX'] . "communication", $db);

            $selectedRow = rtrim($ids, ',');

            if ($selectedRow === "") {
                echo "ERROR";
                die();
            }

            $tempArray = explode(",", $selectedRow);

            $redis->select(1);

            $allResult = new stdClass();
            $deleRecord = new stdClass();

            $AID_7 = "";
            $AID_6 = "";

            $JobType = "";
            $flag = false;

            foreach ($tempArray as $value) {
                $sql_Audit = "select a.CustomerNO, a.OrderNO, a.MachineTag, a.MachineOs, a.JobType, s.uninsdormatStatus from " . $GLOBALS['PREFIX'] . "communication.Audit a, " . $GLOBALS['PREFIX'] . "agent.serviceRequest s where a.AID = '$value' and s.customerNum = a.CustomerNO and s.orderNum = a.OrderNO and a.MachineTag = s.serviceTag order by s.sid limit 1";
                $res_Audit = find_one($sql_Audit, $db);

                if (safe_count($res_Audit) > 0) {

                    $flag = true;
                    $JobType = $res_Audit['JobType'];

                    $machineTag = $res_Audit["MachineTag"];
                    $Redisres = $redis->lrange($machineTag . ":" . $value, 0, -1);

                    if (!isset($allResult->$machineTag)) {
                        $allResult->$machineTag = $res_Audit;
                    }

                    if (trim($res_Audit['uninsdormatStatus']) === "FORCEUNIN") {

                        $AID_7 .= $value . ",";

                        if (safe_count($Redisres) > 0) {
                            $redis->delete($machineTag . ":" . $value);
                        }
                    } else {
                        if (safe_count($Redisres) > 0) {
                            $redis->delete($machineTag . ":" . $value);

                            $AID_6 .= $value . ",";
                        } else {
                            if (!isset($deleRecord->$machineTag)) {
                                $deleRecord->$machineTag = [];
                            }

                            array_push($deleRecord->$machineTag, $value);
                        }
                    }
                }
            }

            if ($AID_6 !== "") {
                $AID_6 = rtrim($AID_6, ",");
                $sql_updt_Audit = "update " . $GLOBALS['PREFIX'] . "communication.Audit set JobStatus = '6' where AID in ($AID_6)";

                redcommand($sql_updt_Audit, $db);
            }

            if ($AID_6 !== "") {
                $AID_7 = rtrim($AID_7, ",");
                $sql_updt_Audit = "update " . $GLOBALS['PREFIX'] . "communication.Audit set JobStatus = '7' where AID in ($AID_7)";

                redcommand($sql_updt_Audit, $db);
            }
            if (count(get_object_vars($deleRecord)) > 0) {
                foreach ($deleRecord as $key => $value) {
                    $str = "deleteJobs---";
                    $aid = "";
                    $tempObj = $allResult->$key;

                    foreach ($value as $value1) {
                        $str .= "$value1,";
                        $aid .= "$value1,";
                    }

                    $currTime = time();
                    $Selectiontype = 'Machine : ' . $key;

                    $str = rtrim($str, ",");
                    $aid = rtrim($aid, ",");




                    $sqlQry = "INSERT INTO Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence,JobStatus) VALUES "
                        . "(0, '" . $tempObj["CustomerNO"] . "', '" . $tempObj["OrderNO"] . "', '" . $key . "', $currTime, '$Selectiontype' ,'', '" . $_SESSION["user"]["username"] . "',"
                        . "'" . $_SESSION['user']['adminEmail'] . "', 0, '" . $JobType . "', '" . $tempObj["MachineOs"] . "', 'Delete Jobs', '" . $str . "',6)";

                    redcommand($sqlQry, $db);

                    $new_AID = mysqli_insert_id();


                    $redis->rpush($key . ":" . $new_AID, $key, $new_AID, $str, 0, $_SESSION['user']['adminEmail'], '');

                    $trigger .= $key . "##";
                }
            }
            $redis->close();
            $trigger = rtrim($trigger, "##");

            if ($flag) {
                echo "~~AS_DONE~~$trigger";
            } else {
                echo '~~AS_FAIL~~';
            }
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
            echo '~~AS_FAIL~~';
        }
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }
}

function RESOL_DeleteJobsFromAuditFn($keys, $db1, $selectedRow)
{

    try {
        global $redis_url;
        global $redis_port;
        global $redis_pwd;

        $redis = new Redis();
        $redis->connect($redis_url, $redis_port);
        $redis->auth($redis_pwd);

        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "communication", $db);


        if ($selectedRow === "") {
            echo "ERROR";
            die();
        }

        $tempArray = explode(",", $selectedRow);

        $redis->select(1);

        $allResult = new stdClass();
        $deleRecord = new stdClass();

        $AID_7 = "";
        $AID_6 = "";

        $flag = $comm = false;

        foreach ($tempArray as $value) {
            $sql_Audit = "select a.CustomerNO, a.OrderNO, a.MachineTag, a.MachineOs, s.uninsdormatStatus from " . $GLOBALS['PREFIX'] . "communication.Audit a, " . $GLOBALS['PREFIX'] . "agent.serviceRequest s where a.AID = '$value' and s.customerNum = a.CustomerNO and s.orderNum = a.OrderNO and a.MachineTag = s.serviceTag order by s.sid limit 1";
            $res_Audit = find_one($sql_Audit, $db);

            if (safe_count($res_Audit) > 0) {

                $flag = true;

                $machineTag = $res_Audit["MachineTag"];
                $Redisres = $redis->lrange($machineTag . ":" . $value, 0, -1);

                if (!isset($allResult->$machineTag)) {
                    $allResult->$machineTag = $res_Audit;
                }

                if (trim($res_Audit['uninsdormatStatus']) === "FORCEUNIN") {

                    $AID_7 .= $value . ",";

                    if (safe_count($Redisres) > 0) {
                        $redis->delete($machineTag . ":" . $value);
                    }
                } else {
                    if (safe_count($Redisres) > 0) {
                        $redis->delete($machineTag . ":" . $value);

                        $AID_6 .= $value . ",";
                    } else {
                        if (!isset($deleRecord->$machineTag)) {
                            $deleRecord->$machineTag = [];
                        }

                        array_push($deleRecord->$machineTag, $value);
                    }
                }
            }
        }

        if ($AID_6 !== "") {
            $AID_6 = rtrim($AID_6, ",");
            $sql_updt_Audit = "update " . $GLOBALS['PREFIX'] . "communication.Audit set JobStatus = '6' where AID in ($AID_6)";

            redcommand($sql_updt_Audit, $db);

            $comm = true;
        }

        if ($AID_7 !== "") {
            $AID_7 = rtrim($AID_7, ",");
            $sql_updt_Audit = "update " . $GLOBALS['PREFIX'] . "communication.Audit set JobStatus = '7' where AID in ($AID_7)";

            redcommand($sql_updt_Audit, $db);

            $comm = true;
        }

        if (count(get_object_vars($deleRecord)) > 0) {
            foreach ($deleRecord as $key => $value) {
                $str = "deleteJobs---";

                $tempObj = $allResult->$key;

                foreach ($value as $value1) {
                    $str .= "$value1,";
                    $aid .= "$value1,";
                }

                $aid = rtrim($aid, ",");

                $currTime = time();
                $Selectiontype = 'Machine : ' . $key;

                $str = rtrim($str, ",");
                if (!$comm) {
                    $sql_updt_Audit = "update " . $GLOBALS['PREFIX'] . "communication.Audit set JobStatus = '7' where AID in ($aid)";
                    redcommand($sql_updt_Audit, $db);
                }


                $sqlQry = "INSERT INTO Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence, JobStatus) VALUES "
                    . "(0, '" . $tempObj["CustomerNO"] . "', '" . $tempObj["OrderNO"] . "', '" . $key . "', $currTime, '$Selectiontype' ,'', '" . $_SESSION["user"]["username"] . "',"
                    . "'" . $_SESSION['user']['adminEmail'] . "', 0, 'Interactive', '" . $tempObj["MachineOs"] . "', 'Delete Jobs', '" . $str . "',6)";

                redcommand($sqlQry, $db);

                $new_AID = mysqli_insert_id();


                $redis->rpush($key . ":" . $new_AID, $key, $new_AID, $str, 0, $_SESSION['user']['adminEmail'], '');

                $trigger .= $key . "##";
            }
        }

        $redis->close();
        $trigger = rtrim($trigger, "##");

        if ($flag) {
            echo "~~AS_DONE~~$trigger";
        } else {
            echo '~~AS_FAIL~~';
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        echo '~~AS_FAIL~~';
    }
}

function RESOL_GetSiteSelfHelpData($db, $dataScope, $searchValue, $from, $now)
{

    $key = 1;
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
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: On-Demand:Consumer%' AND customer in ($sites) AND machine IN ($machines) AND servertime BETWEEN $from AND $now;";
    } else {
        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: On-Demand:Consumer%' AND customer = '$rParent' AND machine IN ($machines) AND servertime BETWEEN $from AND $now;";
    }

    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);
    return $sqlres;
}

function RESOL_GetMachineSelfHelpData($db, $rparentname, $searchValue, $from, $now)
{
    $key = 1;
    if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
        $rCensusId = $_SESSION['rcensusId'];
        $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
        $uuidres = find_one($uuidsql, $db);
        $uuid = $uuidres['uuid'];
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: On-Demand:Consumer%' AND uuid = '$uuid' AND servertime BETWEEN $from AND $now;";
    } else {
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: On-Demand:Consumer%' AND machine = '$searchValue' AND customer = '$rparentname' AND machine = '$searchValue' AND servertime BETWEEN $from AND $now;";
    }
    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);

    return $sqlres;
}

function RESOL_GetGroupSelfHelpData($db, $machines, $from, $now)
{

    $key = 1;
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
    $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: On-Demand:Consumer%' AND uuid in ($uuid) AND customer IN ($custName) AND servertime BETWEEN $from AND $now;";

    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);

    return $sqlres;
}

function RESOL_GetselfDetail($db, $name)
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

            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: On-Demand:Consumer%' AND customer in ($sites) AND machine IN ($machines) AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
        } else {
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: On-Demand:Consumer%' AND customer = '$rParent' AND machine IN ($machines) AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
            $rCensusId = $_SESSION['rcensusId'];
            $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
            $uuidres = find_one($uuidsql, $db);
            $uuid = $uuidres['uuid'];
            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: On-Demand:Consumer%' AND uuid = '$uuid' AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
        } else {
            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: On-Demand:Consumer%' AND machine = '$searchValue' AND customer = '$rParent' AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
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
        $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: On-Demand:Consumer%' AND uuid in ($uuid) AND customer IN ($custName) AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
    }

    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);

    return $sqlres;
}

function RESOL_GetSelfHelpCount($key, $db)
{

    $recordList = [];

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentname = $_SESSION["rparentName"];
    $from = date(strtotime("-15 days"));
    $now = time();

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    if ($searchType == 'Sites') {

        $result = RESOL_GetSiteSelfHelpData($db, $dataScope, $searchValue, $from, $now);
    } else if ($searchType == 'ServiceTag') {

        $result = RESOL_GetMachineSelfHelpData($db, $rparentname, $searchValue, $from, $now);
    } else if ($searchType == 'Groups') {

        $result = RESOL_GetGroupSelfHelpData($db, $machines, $from, $now);
    }

    $totalrecord = safe_count($result);

    if ($totalrecord > 0) {

        foreach ($result as $key => $value) {
            $description = $value['description'];
            if (!in_array($description, $recordList)) {
                array_push($recordList, $description);
            }
        }
    } else {
        $recordList = array();
    }
    return $recordList;
}


function RESOL_GetSiteScheduleData($db, $dataScope, $searchValue, $from, $now)
{
    $key = 1;
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
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: Scheduled%' AND customer in ($sites) AND machine IN ($machines) AND servertime BETWEEN $from AND $now;";
    } else {
        $data = DASH_GetMachinesSites($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: Scheduled%' AND customer = '$rParent' AND machine IN ($machines) AND servertime BETWEEN $from AND $now;";
    }
    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);
    return $sqlres;
}

function RESOL_GetMachineScheduleData($db, $rparentname, $searchValue, $from, $now)
{
    $key = 1;
    if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
        $rCensusId = $_SESSION['rcensusId'];
        $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
        $uuidres = find_one($uuidsql, $db);
        $uuid = $uuidres['uuid'];
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: Scheduled%' AND uuid = '$uuid' AND servertime BETWEEN $from AND $now;";
    } else {
        $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: Scheduled%' AND machine = '$searchValue' AND customer = '$rparentname' AND machine = '$searchValue' AND servertime BETWEEN $from AND $now;";
    }
    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);

    return $sqlres;
}

function RESOL_GetGroupScheduleData($db, $machines, $from, $now)
{
    $key = 1;
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
    $sql = "SELECT * FROM  " . $GLOBALS['PREFIX'] . "event.Events_autoheal WHERE text2 like '%Type of run: Scheduled%' AND uuid in ($uuid) AND customer IN ($custName) AND servertime BETWEEN $from AND $now;";
    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);

    return $sqlres;
}

function RESOL_GetScheduleDetail($db, $namelist, $from, $now)
{
    $name = trim($namelist);

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

            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: Scheduled%' AND customer in ($sites) AND machine IN ($machines) AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
        } else {
            $data = DASH_GetMachinesSites($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";
            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: Scheduled%' AND customer = '$rParent' AND machine IN ($machines) AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($_SESSION['passlevel'] == "Group" || $_SESSION['passlevel'] == "Groups") {
            $rCensusId = $_SESSION['rcensusId'];
            $uuidsql = "SELECT uuid from " . $GLOBALS['PREFIX'] . "core.Census where id = $rCensusId";
            $uuidres = find_one($uuidsql, $db);
            $uuid = $uuidres['uuid'];
            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: Scheduled%' AND uuid = '$uuid' AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
        } else {
            $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: Scheduled%' AND machine = '$searchValue' AND customer = '$rParent' AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
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
        $sql = "select count(aidx) as count,machine,text1,servertime,customer,clientversion,clientsize,uuid from  " . $GLOBALS['PREFIX'] . "event.Events_autoheal where text2 like '%Type of run: Scheduled%' AND uuid in ($uuid) AND customer IN ($custName) AND description like '%" . $name . "%' AND servertime BETWEEN $from AND $now group by text1,machine;";
    }

    mysqli_query("set names 'utf8'");
    $sqlres = find_many($sql, $db);
    return $sqlres;
}

function RESOL_GetScheduleCount($key, $db)
{

    $recordList = [];

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentname = $_SESSION["rparentName"];
    $from = date(strtotime("-15 days"));
    $now = time();

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    if ($searchType == 'Sites') {

        $result = RESOL_GetSiteScheduleData($db, $dataScope, $searchValue, $from, $now);
    } else if ($searchType == 'ServiceTag') {

        $result = RESOL_GetMachineScheduleData($db, $rparentname, $searchValue, $from, $now);
    } else if ($searchType == 'Groups') {

        $result = RESOL_GetGroupScheduleData($db, $machines, $from, $now);
    }

    $totalrecord = safe_count($result);

    if ($totalrecord > 0) {

        foreach ($result as $key => $value) {
            $description = $value['description'];
            if (!in_array($description, $recordList)) {
                array_push($recordList, $description);
            }
        }
    } else {
        $recordList = array();
    }
    return $recordList;
}

function RESOL_GetResolutionDetail($db, $name, $from, $now)
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

        if ($searchVal != '') {
            $append = " and ProfileName like '%$searchVal%'";
        } else {
            $append = "";
        }
        $siteName = UTIL_GetUserSiteList($db, $userId);
        $custList = $siteName['custNo'];
        $ordList = $siteName['ordNo'];
        $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchtype);

        if (isset($_SESSION['user']['ptsEnabled']) && $_SESSION['user']['ptsEnabled'] == 1) {
            $auditCond = '';
        } else {
            $auditCond = "CustomerNO in ($custList) and OrderNO in ($ordList) and";
        }

        $roleName = $_SESSION["user"]["role_name"];
        if ($user_email == 'admin@nanoheal.com' || $roleName == 'dthree') {
            $emailCond = "";
        } else {
            $emailCond = " and AgentUniqId='$user_email'";
        }

        if ($searchtype == 'ServiceTag' || $searchtype == 'Service Tag' || $searchtype == 'Host Name') {


            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof,SelectionType from " . $GLOBALS['PREFIX'] . "communication.Audit where MachineTag = '$searchValue' AND $auditCond JobType IN "
                . "('Interactive') $emailCond and JobStatus NOT IN (6,7) and JobCreatedTime >= '$from' and ProfileName like '%$name%' $append $orderValues $limit";
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

            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof,SelectionType from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) or MachineTag IN ($machines)) AND $auditCond JobType IN "
                . "('Interactive') $emailCond and JobStatus NOT IN (6,7) and JobCreatedTime >= '$from' and ProfileName like '%$name%' $append $orderValues $limit";
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

            $proactiveSql = "select AID,BID,MachineTag,JobCreatedTime,AgentName,JobType,MachineOs,ProfileName,ClientTimeZone,ClientExecutedTime,"
                . "JobStatus,DartExecutionProof,SelectionType from " . $GLOBALS['PREFIX'] . "communication.Audit where (SelectionType in ($selectionType) or MachineTag IN ($machines)) AND $auditCond JobType IN "
                . "('Interactive') $emailCond and JobStatus NOT IN (6,7) and JobCreatedTime >= '$from' and ProfileName like '%$name%' $append $orderValues $limit";
        }
        mysqli_query("set names 'utf8'");
        $proactiveRes = find_many($proactiveSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $proactiveRes;
}
