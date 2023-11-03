<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

include_once 'l-setTimeZone.php';

function Notify_getNotifications_1($key, $db, $priority, $user, $searchType, $searchVal)
{


    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    }
    $key = DASH_ValidateKey($key);
    if ($key) {
        $today = time();
        $from = date('Y-m-d', strtotime('-14 days'));
        $preTime = strtotime($from);
        $wh = '';
        $priority = trim($priority);
        if ($priority == 'critical') {
            $wh = 'and priority in(1,2)';
        } elseif ($priority == 'major') {

            $wh = 'and priority in(3,4)';
        } elseif ($priority == 'minor') {
            $wh = 'and priority in(5)';
        } elseif ($priority == 0 || $priority == '0') {

            $wh = '';
        }


        $reseller_sql = "select C.name,C.priority,C.nid from  " . $GLOBALS['PREFIX'] . "event.Console C where C.nid in (select id from  " . $GLOBALS['PREFIX'] . "event.Notifications where enabled=1 and global=1  $wh) and C.servertime between $preTime and $today $sqlWh group by C.nid order by C.servertime desc";
        $reseller_res = find_many($reseller_sql, $db);
        if (safe_count($reseller_res) > 0) {
            return $reseller_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getNotifications($key, $db, $priority, $user, $searchType, $searchVal)
{
    $username = $_SESSION["user"]["username"];
    $sqlWh = '';
    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $passlevel = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups' || $searchType == 'Group') {
        $dataScope = UTIL_GetSiteScope($db, $searchVal, $searchType);
        $data = DASH_GetGroupsMachines($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        $sqlWh .= "and machine in ($machines)";
    }
    $key = DASH_ValidateKey($key);
    if ($key) {

        $wh = '';
        $priority = trim($priority);
        if ($priority == 'critical') {
            $wh = 'and priority in(1,2)';
        } elseif ($priority == 'major') {

            $wh = 'and priority in(3,4)';
        } elseif ($priority == 'minor') {
            $wh = 'and priority in(5)';
        } elseif ($priority == 0 || $priority == '0') {

            $wh = '';
        } else if ($priority == 7 || $priority == '7') {
            $wh = 'and priority = ""';
        }
        $from = date('Y-m-d', strtotime('-14 days'));
        $today = time();
        $preTime = strtotime($from);
        $lnids = Notify_getEnabledNotifications($db, $wh);
        if ($searchType == 'Site' || $searchType == 'Sites') {
            $reseller_sql = "select count(tid) ncnt,nocName,nid from (select tid, nocName, status, nid,  machine from (select tid,  servertime , nocName, nocStatus as status, nid,  machine from (select tid, servertime, nocName, nocStatus, nid,  machine from  " . $GLOBALS['PREFIX'] . "event.tempNotification where (username='admin' or username='$username') and servertime between $preTime and $today and nid in ($lnids) $sqlWh   ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),machine,nid ORDER BY servertime desc) as v where status IS NULL group by machine,nid) as s group by nid";
        } else if ($searchType == 'ServiceTag' || $searchType == 'Groups' || $searchType == 'Group') {
            $reseller_sql = " select count(tid) ncnt,nocName,nid from (select tid, nocName, status, nid,  machine from (select tid,  servertime , nocName, nocStatus as status, nid,  machine from (select tid, servertime, nocName, nocStatus, nid,  machine from  " . $GLOBALS['PREFIX'] . "event.tempNotification where (username='admin' or username='$username') and servertime between $preTime and $today and nid in ($lnids) $sqlWh ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),machine,nid ORDER BY servertime desc) as v where status IS NULL group by machine,nid) as s group by nid";
        }
        $reseller_res = find_many($reseller_sql, $db);
        if (safe_count($reseller_res) > 0) {
            return $reseller_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getEnabledNotifications($db, $wh)
{

    $str = '';
    $nid_sql = "select id from  " . $GLOBALS['PREFIX'] . "event.Notifications where enabled=1 and global=1 $wh";
    $reseller_res = find_many($nid_sql, $db);
    foreach ($reseller_res as $value) {
        $str .= $value['id'] . ',';
    }
    return rtrim($str, ',');
}

function Notify_getNotificationDetails($key, $db, $nid, $searchType, $searchVal, $status, $user, $passlevel)
{

    if ($status == 'pending') {
        $stat = " and T.nocStatus IS NULL";
    } elseif ($status == 'fixed') {
        $stat = " and T.nocStatus != ''";
    } elseif ($status == 'action') {
        $stat = " and (T.nocStatus IS NOT NULL OR T.nocStatus = '')";
    } else if ($status == 'ALL') {
        $stat = "";
    }

    $sqlWh = '';

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups' || $searchType == 'Group') {
        $dataScope = UTIL_GetSiteScope($db, $searchVal, $searchType);
        $data = DASH_GetGroupsMachines($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        $sqlWh .= "and machine in ($machines)";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $from = date('Y-m-d', strtotime('-14 days'));
        $today = time();
        $preTime = strtotime($from);

        $query = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') eventtime,FROM_UNIXTIME(servertime, '%Y-%m-%d') notifyDt, nocName, eventIdx, nocStatus as status, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat, count(*) as eventCount,notes from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat,notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification T where  username='admin' and servertime between $preTime and $today and nid ='$nid' $sqlWh $stat group by eventIdx ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc";
        $notfCnt = find_many($query, $db);
        if (safe_count($notfCnt) > 0) {
            return $notfCnt;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function Notify_getLimitedNotificationDetails($key, $db, $nid, $searchType, $searchVal, $status, $user, $passlevel, $limit, $search)
{
    $username = $_SESSION["user"]["username"];
    if ($status == 'pending') {
        $stat = " and T.nocStatus IS NULL";
    } elseif ($status == 'fixed') {
        $stat = " and T.nocStatus != ''";
    } elseif ($status == 'action') {
        $stat = " and (T.nocStatus IS NOT NULL OR T.nocStatus = '')";
    } else if ($status == 'ALL') {
        $stat = "";
    }

    $sqlWh = '';

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal) and machine != ''";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "' and machine != ''";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups' || $searchType == 'Group') {
        $dataScope = UTIL_GetSiteScope($db, $searchVal, $searchType);
        $data = DASH_GetGroupsMachines($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        $sqlWh .= "and machine in ($machines)";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $from = date('Y-m-d', strtotime('-14 days'));
        $today = time();
        $preTime = strtotime($from);
        if ($nid != '') {
            if ($limit == "") {
                $query = "select tid from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat,notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification T where  (username='admin' or username='$username') and servertime between $preTime and $today and nid ='$nid' $sqlWh $search $stat group by eventIdx ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc";
                $notfCnt = find_many($query, $db);
                if (safe_count($notfCnt) > 0) {
                    return safe_count($notfCnt);
                } else {
                    return 0;
                }
            } else {
                $query = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') as eventtime,FROM_UNIXTIME(servertime, '%Y-%m-%d') as notifyDt, nocName, eventIdx, nocStatus as status, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat, count(*) as eventCount,notes from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat,notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification T where  (username='admin' or username='$username') and servertime between $preTime and $today and nid ='$nid' $sqlWh $search $stat group by eventIdx ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc $limit";
                $notfCnt = find_many($query, $db);
                if (safe_count($notfCnt) > 0) {
                    return $notfCnt;
                } else {
                    return array();
                }
            }
        } else {
            return 0;
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getMultiNidNotificationDetails($key, $db, $nid, $searchType, $searchVal, $status, $user, $dayInterval, $passlevel)
{
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $stat = "";
    $sqlWh = '';
    $username = $_SESSION["user"]["username"];

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $from = date('Y-m-d', strtotime('-30 days'));
        $today = time();
        $preTime = strtotime($from);
        $timestamp = strtotime($dayInterval);
        $today = strtotime(date('Y-m-01', $timestamp));
        $preTime = strtotime(date('Y-m-t', $timestamp));
        $query = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%Y-%m-%d %h:%m') "
            . "eventtime,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') notifyDt, nocName, eventIdx, nocStatus as status, "
            . "nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, "
            . "machineOs, dartExecutionStat, count(*) as eventCount,notes from (select tid, consoleId, servertime, "
            . "nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, "
            . "customerNum, orderNum, machineOs, dartExecutionStat,notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification where (username='admin' or username='$username') "
            . "and servertime  between $today and $preTime and nid in ($nid) $sqlWh $stat group by eventIdx ORDER BY tid desc) as x group by "
            . "FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc";

        $notfCnt = find_many($query, $db);
        if (safe_count($notfCnt) > 0) {
            return $notfCnt;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_exportNotificationDetails($key, $db, $nid, $searchType, $searchVal, $status, $user, $passlevel)
{

    $username = $_SESSION["user"]["username"];

    if ($status == 'pending') {
        $stat = " and T.nocStatus IS NULL";
    } elseif ($status == 'fixed') {
        $stat = " and T.nocStatus != ''";
    } elseif ($status == 'action') {
        $stat = " and (T.nocStatus IS NOT NULL OR T.nocStatus = '')";
    } else if ($status == 'ALL') {
        $stat = "";
    }

    $sqlWh = '';

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $from = date('Y-m-d', strtotime('-14 days'));
        $today = time();
        $preTime = strtotime($from);
        $query = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') eventtime,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') notifyDt, nocName, eventIdx, nocStatus as status, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat, count(*) as eventCount,notes,text1,text2,text3,text4,solutionPush from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat,notes,text1,text2,text3,text4,solutionPush from  " . $GLOBALS['PREFIX'] . "event.tempNotification where (username='admin' or username='$username') and servertime  between $preTime and $today and nid in (select id from  " . $GLOBALS['PREFIX'] . "event.Notifications where enabled=1 and global=1) $sqlWh $stat group by eventIdx ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc";
        $notfCnt = find_many($query, $db);
        if (safe_count($notfCnt) > 0) {
            return $notfCnt;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_exportSelNotificationDetails($key, $db, $nid, $searchType, $searchVal, $status, $user, $passlevel)
{
    $username = $_SESSION["user"]["username"];

    if ($status == 'pending') {
        $stat = " and T.nocStatus IS NULL";
    } elseif ($status == 'fixed') {
        $stat = " and T.nocStatus != ''";
    } elseif ($status == 'action') {
        $stat = " and (T.nocStatus IS NOT NULL OR T.nocStatus = '')";
    } else if ($status == 'ALL') {
        $stat = "";
    }
    $selList = rtrim(url::requestToAny('selList'), ',');
    $sqlWh = '';

    if ($selList != '') {
        $sqlWh .= "and tid in ($selList) ";
    }
    $nidVal = '';
    if ($nid != '') {
        $nidVal = "and nid in ($nid)";
    }

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }



    $key = DASH_ValidateKey($key);
    if ($key) {

        $lnids = Notify_getEnabledNotifications($db, '');
        $from = date('Y-m-d', strtotime('-14 days'));
        $today = time();
        $preTime = strtotime($from);
        $query = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') eventtime,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') notifyDt, nocName, eventIdx, nocStatus as status, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat, count(*) as eventCount,notes,text1,text2,text3,text4,solutionPush from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat,notes,text1,text2,text3,text4,solutionPush from  " . $GLOBALS['PREFIX'] . "event.tempNotification where (username='admin' or username='$username') and nid in ($lnids) and servertime  between $preTime and $today $nidVal $sqlWh $stat group by eventIdx,nid ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid,nocStatus ORDER BY servertime desc";
        $notfCnt = find_many($query, $db);

        if (safe_count($notfCnt) > 0) {
            return $notfCnt;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_pushOtherAction($key, $db, $sql)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $resQry = redcommand($sql, $db);
        if ($resQry) {
            return 'Done';
        } else {
            return 'Fail';
        }
    }
}

function Notify_getNotificationsEvents($key, $db, $machine, $date, $nid, $status, $sitename)
{
    $username = $_SESSION["user"]["username"];
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select * from  " . $GLOBALS['PREFIX'] . "event.tempNotification where machine='$machine' and site='$sitename' and FROM_UNIXTIME(servertime,'%Y-%m-%d')= '$date' and nid='$nid' and (username='admin' or username='$username') group by eventIdx ORDER BY servertime desc";
        $reseller_res = find_many($sql, $db);
        if (safe_count($reseller_res) > 0) {
            return $reseller_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_addOrupdateNotes($key, $db, $nid, $cidList, $tid, $notesVal)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "update  " . $GLOBALS['PREFIX'] . "event.tempNotification set notes='" . $notesVal . "' where tid='$tid' and nid = '$nid'";
        $reseller_res = redcommand($sql, $db);
        if ($reseller_res) {
            return '<span style="color:green !important;">Notes added successfully.</span>';
        } else {
            return '<span>Fail to add notes.</span>';
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getNotificationNotes($key, $db, $nid, $cidList, $tid)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification where tid='$tid' and nid = '$nid'";
        $reseller_res = find_one($sql, $db);
        if (safe_count($reseller_res) > 0) {
            return $reseller_res['notes'];
        } else {
            return '';
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getGroupName($db, $grpid)
{

    $sql = "select M.name from " . $GLOBALS['PREFIX'] . "core.MachineGroups M where M.mgroupid = '$grpid'";
    $reseller_res = find_one($sql, $db);
    if (safe_count($reseller_res) > 0) {
        return $reseller_res['name'];
    } else {
        return '';
    }
}

function Notify_getSiteGraphdata($key, $db, $priority, $user, $searchType, $searchVal)
{

    $sqlWh = '';
    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $passlevel = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and site = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }
    $key = DASH_ValidateKey($key);
    if ($key) {

        $today = date('Y-m-d');
        $preTime = Date('Y-m-d', strtotime("-15 days"));

        $wh = '';
        if ($priority != '') {
            $wh = 'and priority =' . $priority;
        }

        $reseller_sql = "select sum(fixedCount)  fixedCnt,sum(noActionCount) noCnt,sum(othersCount) othrCnt,graphDate from " . $GLOBALS['PREFIX'] . "event.SummaryGraphSite where  nid in (select id from  " . $GLOBALS['PREFIX'] . "event.Notifications where enabled=1 and global=1 $wh) and graphDate>= '$preTime' and graphDate <= '$today' group by graphDate order by graphDate desc";
        $reseller_res = find_many($reseller_sql, $db);
        $recordList = [];
        if (safe_count($reseller_res) > 0) {

            foreach ($reseller_res as $value) {

                $dt_var = $value['graphDate'];
                $dt = date("d M", strtotime($dt_var));
                $recordList[] = array($dt, $value['noCnt'], $value['othrCnt'], $value['fixedCnt']);
            }
            return $recordList;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getGrpGraphdata($key, $db, $priority, $nid, $user, $searchType, $searchVal)
{

    $sqlWh = '';
    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and sitename in ($siteVal)";
        } else {
            $sqlWh .= "and sitename = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $passlevel = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and sitename = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }
    $key = DASH_ValidateKey($key);
    if ($key) {

        $wh = '';
        if ($priority != '') {
            $wh = 'and priority =' . $priority;
        }

        $from = date('Y-m-d', strtotime('-15 days'));
        $today = date('Y-m-d', time());
        $preTime = strtotime($from);

        if ($nid != '') {
            $reseller_sql = "select * from (select id, eventIdx, sitename,machine, nid, nocstatus, reportDate,priority from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where reportDate between '$from' and '$today' "
                . "and nid=$nid $sqlWh order by id desc) as t group by sitename,machine,nid,reportDate";
        } else {
            $reseller_sql = "select * from (select id, eventIdx, sitename,machine, nid, nocstatus, reportDate,priority from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where reportDate between '$from' and '$today' "
                . "and priority=$priority $sqlWh order by id desc) as t group by sitename,machine,priority,reportDate,nid";
        }
        $reseller_res = find_many($reseller_sql, $db);
        if (safe_count($reseller_res) > 0) {
            $recordList = [];

            foreach ($reseller_res as $value) {

                $dateStamp = $value['reportDate'];
                $status = trim($value['nocstatus']);



                if ($status == 'Fixed') {
                    $arr[$dateStamp]['fixed_count'][$value['id']]['idx'][] = $value['eventIdx'];
                } else if ($status == '' || $status == null) {
                    $val = $value['nocstatus'] . '----' . $value['eventIdx'] . '\n';
                    $arr[$dateStamp]['no_action_taken_count'][$value['id']]['idx'][] = $value['eventIdx'];
                } else {
                    $arr[$dateStamp]['others_count'][$value['id']]['idx'][] = $value['eventIdx'];
                    $val = $value['nocstatus'] . '----' . $value['eventIdx'] . '\n';
                }
            }
            if (isset($arr)) {
                foreach ($arr as $key => $value) {
                    $count = 0;
                    $count1 = 0;
                    $count2 = 0;
                    $count3 = 0;



                    if (isset($value['fixed_count'])) {
                        $count1 = safe_count($value['fixed_count']);
                        $count = $count + $count1;
                    }
                    if (isset($value['no_action_taken_count'])) {
                        $count2 = safe_count($value['no_action_taken_count']);
                        $count = $count + $count2;
                    }
                    if (isset($value['others_count'])) {
                        $count3 = safe_count($value['others_count']);
                        $count = $count + $count3;
                    }


                    $dt_var = $key;
                    $dt = date("d M", strtotime($dt_var));
                    $recordList[] = array($dt, $count2, $count3, $count1);
                }
                return $recordList;
            } else {
                return array();
            }
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}

function Notify_GetdateLabelForMonth()
{

    $date = date("M d");
    $onemonth = strtotime($date) - (15 * 24 * 60 * 60);
    $datelabel = '';
    $total = 15;
    $i = 1;
    while ($i <= $total) {
        $datelabel[] = date("d M", $onemonth + ($i * 24 * 60 * 60));
        $i += 1;
    }
    return $datelabel;
}


function Notify_GetHoursTrend($key, $db, $user, $searchType, $searchVal, $priority, $hours)
{
    $sqlWh = '';

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and sitename in ($siteVal)";
        } else {
            $sqlWh .= "and sitename = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $passlevel = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and sitename = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }

    if ($hours == 1) {
        $current1Hour = date("Y-m-d H");
        $last1Hour = date("Y-m-d H", time() - 3600);
        $current1HourSql = "select id from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where reportTime='$current1Hour' and priority=$priority $sqlWh ;";

        $current1HourResult = find_many($current1HourSql, $db);
        $hr1 = safe_count($current1HourResult);

        $last1HourSql = "select id from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where  reportTime='$last1Hour' and priority=$priority $sqlWh";
        $last1HourResult = find_many($last1HourSql, $db);
        $hr1Diff = safe_count($last1HourResult);
        if ($hr1Diff > 0) {
            $difference1 = $hr1Diff - $hr1;
        } else {
            $difference1 = $hr1;
        }
        return array('1hrDiff' => $difference1, '1hr' => $hr1);
    } else if ($hours == 24) {
        $current24HourStart = date("Y-m-d H");
        $current24HourEnd = date("Y-m-d H", strtotime('-23 hours'));
        $last24HourStart = $current24HourEnd;
        $last24HourEnd = date("Y-m-d H", strtotime('-48 hours'));

        $current24HourSql = "select id,priority from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where  reportTime between "
            . "'$current24HourEnd' and '$current24HourStart' and priority=$priority $sqlWh group by sitename, machine, nid";
        $current24HourResult = find_many($current24HourSql, $db);

        $prio24 = safe_count($current24HourResult);

        $last24HourSql = "select id,priority from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where  reportTime between "
            . "'$last24HourEnd' and '$last24HourStart' and priority=$priority $sqlWh group by sitename, machine, nid";
        $last24HourResult = find_many($last24HourSql, $db);

        $prioLast24 = safe_count($last24HourResult);

        if ($prioLast24 > 0) {
            $difference2 = $prioLast24 - $prio24;
        } else {
            $difference2 = $prio24;
        }
        return array('24hrsDiff' => $difference2, '24hrs' => $prio24);
    }
}


function Notify_GetDetailedTrend($key, $db, $user, $searchType, $searchVal, $nid, $priority, $hours)
{


    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and sitename in ($siteVal)";
        } else {
            $sqlWh .= "and sitename = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $passlevel = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "'";
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= " and sitename = '" . $passlevel . "' and machine = '" . $searchVal . "'";
            } else {
                $sqlWh .= " and machine = '" . $searchVal . "'";
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= "and machine in ($fmchStr)";
    }


    if ($hours == 1) {
        $current1Hour = $date = date("Y-m-d H");
        $last1Hour = date("Y-m-d H", time() - 3600);

        $curr1HourSql = "select id from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where  reportTime='$current1Hour' "
            . " and priority=$priority and nid = $nid $sqlWh ;";
        $curr1HourResult = find_many($curr1HourSql, $db);
        $curr1Hr = safe_count($curr1HourResult);

        $last1HourSql = "select id from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where reportTime='$last1Hour' "
            . " and priority=$priority and nid = $nid $sqlWh";
        $last1HourResult = find_many($last1HourSql, $db);
        $last1Hr = safe_count($last1HourResult);






        if ($last1Hr > 0) {
            $difference1 = $last1Hr - $curr1Hr;
        } else {
            $difference1 = $curr1Hr;
        }
        return array('1hrDiff' => $difference1, '1hr' => $curr1Hr);
    } else if ($hours == 24) {
        $current24HourStart = $date = date("Y-m-d H");
        $current24HourEnd = date("Y-m-d H", strtotime('-23 hours'));
        $last24HourStart = $current24HourEnd;
        $last24HourEnd = date("Y-m-d H", strtotime('-48 hours'));

        $currentSql = "select id from  " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where reportTime between '$current24HourEnd'  and '$current24HourStart' "
            . " and priority=$priority and nid=$nid $sqlWh group by sitename,machine";
        $currentResult = find_many($currentSql, $db);

        $hrs24 = safe_count($currentResult);

        $lastSql = " select id from   " . $GLOBALS['PREFIX'] . "event.tempGraphSummary where reportTime between "
            . " '$last24HourEnd' and '$last24HourStart' and  priority=$priority and nid=$nid $sqlWh group by sitename,machine";
        $lastResult = find_many($lastSql, $db);
        $hrs24Diff = safe_count($lastResult);

        if ($hrs24Diff > 0) {
            $difference2 = $hrs24Diff - $hrs24;
        } else {
            $difference2 = $hrs24;
        }
        return array('24hrsDiff' => $difference2, '24hrs' => $hrs24);
    }
}

function Notify_GetAllNids($key, $db, $priority)
{
    $sql = "select id, name from  " . $GLOBALS['PREFIX'] . "event.Notifications where enabled = 1 and global = 1 and priority = $priority";
    $result = find_many($sql, $db);
    return $result;
}

function Notify_GetServiceLog($key, $db, $searchType, $searchVal, $dateRangeStart, $dateRangeEnd, $tableName)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        if ($searchType == 'Site' || $searchType == 'Sites') {
            if ($searchVal == 'All') {
                $sites = DASH_GetSites($key, $db, $user);
                $siteVal = '';
                foreach ($sites as $value) {
                    $siteVal .= "'" . $value . "',";
                }
                $siteVal = rtrim($siteVal, ',');

                $sqlWh .= " siteName in ($siteVal)";
            } else {
                $sqlWh .= " siteName = '" . $searchVal . "'";
            }
        } else if ($searchType == 'ServiceTag') {

            $sqlWh .= " machine = '" . $searchVal . "'";
        } else if ($searchType == 'Groups' || $searchType == 'Group') {
            $dataScope = UTIL_GetSiteScope($db, $searchVal, $searchType);
            $data = DASH_GetGroupsMachines($key, $db, $dataScope);
            $machines = "'" . implode("','", $data) . "'";

            $sqlWh .= " machine in ($machines)";
        }

        $result = Notify_GetServiceLogData($db, $sqlWh, $dateRangeStart, $dateRangeEnd, $tableName);
        return $result;
    } else {
    }
}

function Notify_GetServiceLogData($db, $whereClause, $dateRangeStart, $dateRangeEnd, $tableName)
{
    $sql = "SELECT S.tileName tileName, S.siteName siteName, S.machine, S.dartNo dartNumber, S.text1, S.text2, "
        . "from_unixtime(S.clientTime) clientTime, S.successDesc FROM  " . $GLOBALS['PREFIX'] . "event.$tableName S WHERE "
        . "$whereClause AND S.clientTime BETWEEN $dateRangeEnd AND $dateRangeStart ";
    $result = find_many($sql, $db);

    if (safe_count($result) > 0) {
        return $result;
    } else {
        return array("message" => "No records found", "status" => "failed");
    }
}


function Notify_doActionOnNotification($db, $userName, $agentId, $tidArr, $searchValue)
{
    $cNode = "NODEJS";
    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);

    $Redisres = $redis->lrange("$searchValue", 0, -1);
    $OperatingSystem = '';

    if (safe_count($Redisres) > 0) {

        $OperatingSystem = $Redisres[4];
        $APIKEY = $Redisres[6];
        $onlineOffline = "Offline";
    }

    foreach ($tidArr as $tid) {

        $sql_notify = "select tid,machine,nid from tempNotification where tid ='$tid'";
        $res_notify = find_one($sql_notify, $db);
        if (safe_count($res_notify) > 0) {
            $res_nid = $res_notify['nid'];
            $res_machine = $res_notify['machine'];
            $res_tid = $res_notify['tid'];
            Notify_pushJobs($res_tid, $res_nid, $res_machine, $OperatingSystem, $redis, $db, $agentId);
        }
    }
    return array("status" => "success", "msg" => "Solutions pushed successfully");
}

function Notify_pushJobs($ptid, $pnid, $machinename, $OperatingSystem, $redis, $db, $agentId)
{

    $sqlSchedule = array();
    $sql = array();
    $ServiceTagSupported = '';
    $resID = 0;
    $sql_tempNotify = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%Y-%m-%d'), nocName, "
        . " eventIdx, nocStatus as status, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, "
        . " customerNum, orderNum, machineOs, dartExecutionStat, count(*) as eventCount from (select tid, consoleId, "
        . " servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture, "
        . " agentId, customerNum, orderNum, machineOs, dartExecutionStat from tempNotification where tid in(select tid from "
        . "  " . $GLOBALS['PREFIX'] . "event.tempNotification T where machine = '$machinename' and action=0 and nid = '$pnid') ORDER BY tid desc) as x "
        . " group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ";
    $res_tempNotify = find_many($sql_tempNotify, $db);

    if (safe_count($res_tempNotify) > 0) {
        $v = safe_count($res_tempNotify) - 1;
        $j = 0;
        $machineOs = $OperatingSystem;
        $varValueRes = Notify_GetProfileData($db, $pnid, $machineOs);
        $resStatus = $varValueRes['status'];

        if ($resStatus == 'success') {

            foreach ($res_tempNotify as $nval) {

                $varvalue = $varValueRes['varVal'];
                $varConfg = "VarName=S00286ProfileName;VarType=2;VarVal=" . $varvalue . ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;";
                $profilename = $varValueRes['profile_name'];

                $bid = 0;
                $Type = 'Notification';
                $Status = "Fixed";
                $currTime = time();
                $Site = $nval['site'];
                $ServiceTag = $nval['machine'];
                $versionNo = $nval['clientversion'];
                $dartConfg = $varConfg;
                $profileName = $profilename;
                $nid = $nval['nid'];
                $consoleId = $nval['consoleId'];
                $eventIdx = $nval['eventIdx'];
                $eventTime = $nval['eventTime'];
                $notifyId = $nval['tid'];
                $DartNumber = $nval['scrip'];
                $CustomerNumber = $nval['customerNum'];
                $OrderNumber = $nval['orderNum'];
                $agentName = 'nanoheal';
                $agentUniqId = 'support@nanoheal.com';

                if ($j == 0) {

                    $Selectiontype = 'Machine : ' . $ServiceTag;
                    $sqlAudit[] = '(' . $bid . ',"' . $CustomerNumber . '","' . $OrderNumber . '","' . $ServiceTag . '","' . $currTime . '","' . $Selectiontype . '","' . $DartNumber . '","' . $agentName . '","' . $agentUniqId . '","' . $notifyId . '","' . $Type . '","' . $machineOs . '","' . $profileName . '","' . $dartConfg . '")';

                    $resID = Notify_insertJobsIntoAudit($db, $sqlAudit);
                    $redis->select(1);
                    for ($x = 0; $x < safe_count($sqlAudit); $x++) {
                        $val = explode(',', $sqlAudit[$x]);
                        $redis->rpush(trim($val[3], '"') . ":" . $resID, trim($val[3], '"'), $resID, trim($val[13], '")'), trim($val[9], '"'), trim($val[8], '"'), $DartNumber);
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

                $sql[] = '("' . $nid . '", "' . $Site . '","' . $ServiceTag . '","' . $Status . '","' . $currTime . '","' . $DartNumber . '","' . $consoleId . '","' . $eventTime . '","' . $agentUniqId . '","' . $eventIdx . '","' . $resID . '","' . $profileName . '")';
                $j++;
            }
            Notify_updateNocStatusGrp($db, $sql, $agentId);
        }
    }
}

function Notify_GetProfileData($db, $nid, $machineOs)
{

    $sql_notif = "SELECT profile_name FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE id = '$nid' LIMIT 1";
    $res_notif = find_one($sql_notif, $db);

    $profile_name = $res_notif['profile_name'];

    if ($profile_name == NULL) {
        $resultArr['status'] = 'failed';
    } else {

        $profileOs = Notify_getProfileOS($machineOs);
        $resultArr = array();

        $sql = "select mid,menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,description,image,tileSize,tileDesc,"
            . "iconPos,OS,page,lang,status,themeColo,themeFont,theme,follow,addon,addonDart FROM " . $GLOBALS['PREFIX'] . "event.profile WHERE profile = '$profile_name' and (OS like '%$profileOs%' or OS = 'common') and type = 'L3' limit 1";
        $res = find_one($sql, $db);
        if (safe_count($res) > 0) {



            $menuItem = $res['menuItem'];
            $type = $res['type'];
            $parentId = $res['parentId'];
            $profile = $res['profile'];
            $dart = $res['dart'];
            $variable = $res['variable'];
            $shortDesc = $res['shortDesc'];
            $description = $res['tileDesc'];
            $page = $res['page'];

            $resultArr = array("status" => 'success', "parentId" => $parentId, "profile" => safe_addslashes($profile), "dart" => $dart, "variable" => $variable, "shortDesc" => $shortDesc, "description" => urlencode($description), "page" => $page, "menuItem" => $menuItem, "clickfun" => "clickl3level");
        } else {
            $resultArr['status'] = 'failed';
            $resultArr['varVal'] = '';
        }
    }
    return $resultArr;
}

function Notify_getNotifyProfiles($db, $nid)
{
    db_change($GLOBALS['PREFIX'] . "event", $db);

    $sql = "select nid,name,id,dartconfig,description,tileDesc from NotificationProfile where nid='$nid' and enabled='1'";
    $res = find_many($sql, $db);
    return $res;
}

function Notify_getNotifyProfilesDtl($db, $nid, $profId)
{
    db_change($GLOBALS['PREFIX'] . "event", $db);
    $sql = "select nid,name,id,dartconfig,description,tileDesc,dartnum from NotificationProfile where nid='$nid' and id='$profId' and enabled='1' limit 1";
    $res = find_one($sql, $db);
    return $res;
}

function Notify_getProfileOS($machineOs)
{
    $profileOs = 'common';

    if (stripos($machineOs, 'xp') !== false) {
        $profileOs = "xp";
    }

    if (stripos($machineOs, 'vista') !== false) {
        $profileOs = "vista";
    }

    if (stripos($machineOs, '7') !== false) {
        $profileOs = "7";
    }

    if (stripos($machineOs, '8') !== false) {
        $profileOs = "8";
    }

    if (stripos($machineOs, '10') !== false) {
        $profileOs = "10";
    }

    return $profileOs;
}

function Notify_insertIntoSchedule($db, $sql)
{
    db_change($GLOBALS['PREFIX'] . "node", $db);

    $sqlQry = "INSERT INTO " . $GLOBALS['PREFIX'] . "node.schedule (bid, siteName, username, servicetag, type, scheduleTime, varValues, "
        . " profileName, userId, idx, version, nid) VALUES " . implode(',', $sql);
    $resQry = redcommand($sqlQry, $db);

    return $resQry;
}

function Notify_insertIntoAudit($db, $sql)
{
    db_change($GLOBALS['PREFIX'] . "node", $db);

    $sqlQry = "INSERT INTO " . $GLOBALS['PREFIX'] . "node.audit (bid, siteName, username, servicetag, type, createdtime, varValues, profileName, "
        . " userId, idx, version, nid) VALUES " . implode(',', $sql);
    $resQry = redcommand($sqlQry, $db);

    return $resQry;
}

function Notify_updateNocStatusGrp($db, $sql, $agentId)
{
    $unixtime = time();

    $sqlQry = "INSERT INTO  " . $GLOBALS['PREFIX'] . "event.NotificationStatus (nid, sitename, machine, status, timeExecuted, dartnum, consoleId, eventTime, agentId, eventIdx,auditId,solutionPush)
		VALUES " . implode(',', $sql) . " ON DUPLICATE KEY UPDATE status =values(status),timeExecuted = $unixtime,
		dartnum =values(dartnum),eventTime =values(eventTime),agentId ='" . $agentId . "'";
    $resQry = redcommand($sqlQry, $db);

    return $resQry;
}

function Notify_insertJobsIntoAudit($db, $sql)
{
    db_change($GLOBALS['PREFIX'] . "communication", $db);

    $sqlQry = "INSERT INTO " . $GLOBALS['PREFIX'] . "communication.Audit (BID, CustomerNO, OrderNO, MachineTag, JobCreatedTime, SelectionType, Dart, AgentName, AgentUniqId, IDX, JobType, MachineOs, ProfileName, ProfileSequence) VALUES " . implode(',', $sql);
    redcommand($sqlQry, $db);

    $FirstInsertId = mysql_insert_id();
    return $FirstInsertId;
}

function Notify_getProfileTiles($db, $searchValue)
{

    global $redis_url;
    global $redis_port;
    global $redis_pwd;

    $redis = new Redis();
    $redis->connect($redis_url, $redis_port);
    $redis->auth($redis_pwd);

    $redis->select(0);

    $Redisres = $redis->lrange("$searchValue", 0, -1);

    if (safe_count($Redisres) > 0) {

        $OperatingSystem = $Redisres[4];
        $APIKEY = $Redisres[6];
        $onlineOffline = "Offline";
    }

    $profileOs = Notify_getProfileOS($OperatingSystem);
    $profiles = Notify_Get_Level_One($db, $profileOs);
    return $profiles;
}


function Notify_Get_Level_One($db, $profileOs)
{
    $profile_array = [];
    $sql_one = "SELECT menuItem, type, profile, parentId, page FROM " . $GLOBALS['PREFIX'] . "event.profile WHERE type = 'L1'";
    $res_one = find_many($sql_one, $db);

    if (safe_count($res_one) > 0) {
        foreach ($res_one as $key => $value) {
            $page = $value['page'];
            $type = $value['type'];
            $menuItem = $value['menuItem'];
            $profile_array['L1'][$menuItem] = Notify_Get_Level_Two($db, $page, $profileOs);
        }
    } else {
    }

    return $profile_array;
}


function Notify_Get_Level_Two($db, $page, $profileOs)
{
    $profile_array = [];
    $sql_two = "SELECT menuItem, type, profile, parentId, page, OS, varValue FROM " . $GLOBALS['PREFIX'] . "event.profile WHERE parentId = '$page' AND OS LIKE '%$profileOs%'";
    $res_two = find_many($sql_two, $db);

    if (safe_count($res_two) > 0) {
        $j = 0;
        foreach ($res_two as $key => $value) {
            $temp_page = $value['page'];
            $type = $value['type'];
            $menuItem = $value['menuItem'];

            if ($type == 'L3') {
                $profile_array['L3'][$j]['name'] = $menuItem;
                $profile_array['L3'][$j]['os'] = $value['OS'];
                $profile_array['L3'][$j]['varvalue'] = $value['varValue'];
            } else {
                $profile_array['L2'][$menuItem] = Notify_Get_Level_Three($db, $temp_page, $profileOs);
            }
            $j++;
        }
    } else {
        $profile_array = [];
    }
    return $profile_array;
}


function Notify_Get_Level_Three($db, $page, $profileOs)
{
    $profile_array = [];
    $sql_two = "SELECT menuItem, type, profile, parentId, page , OS, varValue FROM " . $GLOBALS['PREFIX'] . "event.profile WHERE parentId = '$page' AND OS LIKE '%$profileOs%';";
    $res_two = find_many($sql_two, $db);
    $i = 0;
    if (safe_count($res_two) > 0) {
        foreach ($res_two as $key => $value) {
            $profile_array['L3'][$i]['name'] = $value['menuItem'];
            $profile_array['L3'][$i]['os'] = $value['OS'];
            $profile_array['L3'][$i]['varvalue'] = $value['varValue'];
            $i++;
        }
    } else {
        $profile_array = [];
    }
    return $profile_array;
}

function Notify_getNotificationNames($db, $isEnabled, $priority)
{

    if ($priority == "") {
        $nid_sql = "SELECT id, name, username, priority, enabled FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE enabled IN $isEnabled AND global=1";
        $reseller_res = find_many($nid_sql, $db);
    } else {
        $nid_sql = "SELECT id, name, username, priority, enabled FROM  " . $GLOBALS['PREFIX'] . "event.Notifications WHERE enabled IN $isEnabled AND global=1 AND priority IN ($priority)";
        $reseller_res = find_many($nid_sql, $db);
    }

    return $reseller_res;
}


function Notify_GetWhereClause($key, $db, $user, $searchType, $passlevel, $searchVal, $rparentname)
{
    $sqlWh = '';
    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= "and site in ($siteVal)";
        } else {
            $sqlWh .= "and site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= " and machine = '" . $searchVal . "' and site in ($rparentname)";
        } else {
            $sites = DASH_GetSites($key, $db, $user);
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');
            $sqlWh .= " and machine = '" . $searchVal . "' and site in ($rparentname)";
        }
    } else if ($searchType == 'Groups' || $searchType == 'Group') {
        $dataScope = UTIL_GetSiteScope($db, $searchVal, $searchType);;
        $data = DASH_GetGroupsMachines($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";
        $sites = DASH_GetSites($key, $db, $user);
        foreach ($sites as $value) {
            $siteVal .= "'" . $value . "',";
        }
        $siteVal = rtrim($siteVal, ',');
        $sqlWh .= "and machine in ($machines)";
    }
    return $sqlWh;
}


function Notify_getAllEnabledNotifications($key, $db, $priority, $user, $status, $searchType, $searchVal)
{

    $sqlWh = Notify_GetWhereClause($key, $db, $user, $searchType, $searchVal, $searchVal);

    if ($status == 'pending') {
        $stat = " and nocStatus IS NULL";
    } elseif ($status == 'fixed') {
        $stat = " and nocStatus != ''";
    } elseif ($status == 'action') {
        $stat = " and (nocStatus IS NOT NULL OR nocStatus = '')";
    } else {
        $stat = "";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $wh = '';
        $priority = trim($priority);
        if ($priority == 'critical') {
            $wh = 'and priority in(1,2)';
        } elseif ($priority == 'major') {

            $wh = 'and priority in(3,4)';
        } elseif ($priority == 'minor') {
            $wh = 'and priority in(5)';
        } elseif ($priority == 0 || $priority == '0') {

            $wh = '';
        }
        $from = date('Y-m-d', strtotime('-14 days'));
        $today = time();
        $preTime = strtotime($from);
        $lnids = Notify_getEnabledNotifications($db, $wh);

        $notif_sql = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%m/%d/%Y %h:%i %p') eventtime, "
            . "FROM_UNIXTIME(servertime, '%Y-%m-%d') notifyDt, nocName, eventIdx, nocStatus as status, nid, site, machine, "
            . "clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat, "
            . "count(*) as eventCount,notes from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, "
            . "machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, "
            . "dartExecutionStat,notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification T where  username='admin' and servertime between "
            . "$preTime and $today $sqlWh $stat group by eventIdx ORDER BY tid desc) as x "
            . "group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc";

        $notif_res = find_many($notif_sql, $db);
        if (safe_count($notif_res) > 0) {
            return $notif_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}




function NOTIFY_GetIncidentGraphCount($key, $db, $user, $searchVal, $searchType, $startDate, $endDate, $siteVal)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $result = [];
        $whereClause = Notify_GetWhereClause($key, $db, $user, $searchType, $searchVal, $searchVal, $siteVal);
        $lnids = Notify_getEnabledNotifications($db, "");
        if ($searchType == "Sites" || $searchType == "Site") {
            $sql = "select graphDate, sum(fixedCount) fixed, sum(noActionCount) pending, sum(othersCount) other from (SELECT graphDate, fixedCount, noActionCount, othersCount "
                . "FROM " . $GLOBALS['PREFIX'] . "event.SummaryGraphSite S WHERE S.graphDate BETWEEN '$startDate' AND  '$endDate' AND nid IN "
                . "($lnids) $whereClause GROUP BY S.graphDate,S.nid) as t group by graphDate";
            $res = find_many($sql, $db);
            if (safe_count($res) > 0) {
                $result = $res;
            }
        } else {
            $startDate = strtotime($startDate);
            $endDate = strtotime($endDate);
            $sql = "select * from (SELECT tid, FROM_UNIXTIME(servertime, '%Y-%m-%d') graphDate, priority, nocName, nocStatus, nid,  machine, dartExecutionStat FROM  " . $GLOBALS['PREFIX'] . "event.tempNotification WHERE  "
                . " servertime BETWEEN '$startDate' AND '$endDate' AND nid IN ($lnids) $whereClause group by eventIdx) as M group by graphDate,nid,dartExecutionStat";
            $res = find_many($sql, $db);
            $result = NOTIFY_GetMachineIncidentGraphCount($key, $db, $user, $searchVal, $searchType, $startDate, $endDate, $res);
        }
        return $result;
    } else {
        echo "Your key has been expired";
    }
}


function NOTIFY_GetIncidentRecords($key, $db, $user, $searchType, $searchVal, $start_day, $end_day, $limit, $rparentname)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $whereClause = Notify_GetWhereClause($key, $db, $user, $searchType, $searchVal, $searchVal, $rparentname);
        $lnids = Notify_getEnabledNotifications($db, "");

        if ($limit === "0") {
            $sql = "SELECT count(tid) as count FROM  " . $GLOBALS['PREFIX'] . "event.tempNotification WHERE  servertime BETWEEN "
                . "$start_day AND $end_day AND nid IN ($lnids) $whereClause ORDER BY tid DESC";
            $res = find_one($sql, $db);
            return $res['count'];
        } else {
            $sql = "SELECT tid, servertime, priority, nocName, nocStatus, nid,  machine, dartExecutionStat FROM  " . $GLOBALS['PREFIX'] . "event.tempNotification WHERE  "
                . " servertime BETWEEN $start_day AND $end_day AND nid IN ($lnids) $whereClause $limit";
            $res = find_many($sql, $db);

            return $res;
        }
    } else {
        echo "Your key has been expired";
    }
}

function NOTIFY_GetMachineIncidentGraphCount($key, $db, $user, $searchVal, $searchType, $startDate, $endDate, $incidentArray)
{
    $tempArray1 = [];
    $tempArray2 = [];

    if (safe_count($incidentArray) > 0) {
        foreach ($incidentArray as $key => $value) {
            $tempArray1[$value['graphDate']][] = array('status' => $value['nocStatus'], 'dartExecustionStat' => $value['dartExecutionStat']);
            $tempArray2[$value['graphDate']] = array('graphDate' => $value['graphDate'], 'fixed' => 0, 'pending' => 0, 'other' => 0);
        }

        foreach ($incidentArray as $key => $value) {
            $statusVal = $value['nocStatus'];
            $dartExeStat = $value['dartExecutionStat'];

            if ($statusVal == 'Fixed' && $dartExeStat != '') {
                $tempArray2[$value['graphDate']]['fixed'] = $tempArray2[$value['graphDate']]['fixed'] + 1;
            } else if ($statusVal == 'Fixed' && ($dartExeStat == '' || $dartExeStat == NULL)) {
                $tempArray2[$value['graphDate']]['pending'] = $tempArray2[$value['graphDate']]['pending'] + 1;
            } else if ($statusVal != 'Fixed' && $statusVal != '' && $statusVal != NULL) {
                $tempArray2[$value['graphDate']]['pending'] = $tempArray2[$value['graphDate']]['pending'] + 1;
            } else if (($statusVal == '' || $statusVal == NULL) && ($dartExeStat == '' || $dartExeStat == NULL)) {
                $tempArray2[$value['graphDate']]['pending'] = $tempArray2[$value['graphDate']]['pending'] + 1;
            } else if ($statusVal == '') {
                $tempArray2[$value['graphDate']]['other'] = $tempArray2[$value['graphDate']]['other'] + 1;
            }
        }
    }
    return $tempArray2;
}

function Notify_getNotificationSummary($key, $db, $searchVal, $sitename)
{


    $stat = " and T.nocStatus IS NULL";
    $sqlWh = '';
    $machines = "'" . implode("','", $data) . "'";
    $sqlWh .= "and machine in ($machines)";

    $key = DASH_ValidateKey($key);
    if ($key) {

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
    } else {
        echo "Your key has been expired";
    }
}

function Notify_getNotificationSummaryDtls($key, $db, $searchVal, $sitename)
{


    $stat = " and T.nocStatus IS NULL";
    $sqlWh = '';
    $machines = "'" . implode("','", $data) . "'";
    $sqlWh .= "and machine in ($machines)";



    $key = DASH_ValidateKey($key);
    if ($key) {

        $from = date('Y-m-d', strtotime('-15 days'));
        $today = time();
        $preTime = strtotime($from);


        $criticalCnt = 0;
        $majorCnt    = 0;
        $minorCnt    = 0;

        $criticalList = [];
        $majorList = [];
        $minorList = [];


        $sql_query = "select  nocName, nid, priority,count(tid) Cnt1 from (select tid, servertime, nocName, status, nid,  machine,priority,serverdate,count(tid) Cnt from (select tid, servertime, nocName, nocStatus as status, nid,  machine,priority,FROM_UNIXTIME(servertime, '%Y-%m-%d') serverdate from  " . $GLOBALS['PREFIX'] . "event.tempNotification where username='admin' and servertime between $preTime and $today and machine = '$searchVal' and site='$sitename'   ORDER BY tid desc) as K  group by nid,serverdate order by nid,serverdate,priority) as J group by nid";
        $notf_Cnt = find_many($sql_query, $db);
        if (safe_count($notf_Cnt) > 0) {
            foreach ($notf_Cnt as $key => $value) {

                $nid1         = $value['nid'];
                $nocName1      = $value['nocName'];
                $eventCount1  = $value['Cnt1'];
                $priority1    = $value['priority'];

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

                        $tid             = $value1['tid'];
                        $servertime      = $value1['servertime'];
                        $nocName         = $value1['nocName'];
                        $statusVal       = $value1['status'];
                        $nid             = $value1['nid'];
                        $machine         = $value1['machine'];
                        $priority        = $value1['priority'];
                        $notifyDt        = $value1['serverdate'];
                        $machineOS       = $value1['machineOs'];
                        $consoleId       = $value1['consoleId'];
                        $scrip           = $value1['scrip'];
                        $eventIdx        = $value1['eventIdx'];
                        $eventCount      = $value1['Cnt'];
                        $site            = $value1['site'];
                        $version         = $value1['clientversion'];
                        $dartExeStat     = '';


                        if ($statusVal == '') {
                            $status = 'New';
                        } else if ($statusVal == 'Fixed' && $dartExeStat != '') {
                            $status =  'Completed';
                        } else {
                            $status = 'Actioned';
                        }

                        if ($machineOS == '') {
                            $machineOS      = 'NULL';
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

function Notify_getNotifiySNDtls($key, $db, $nid, $searchVal, $sitename)
{

    $stat = " and T.nocStatus IS NULL";
    $sqlWh = '';
    $machines = "'" . implode("','", $data) . "'";
    $sqlWh .= "and machine in ($machines)";

    $key = DASH_ValidateKey($key);
    if ($key) {

        $from = date('Y-m-d', strtotime('-15 days'));
        $today = time();
        $preTime = strtotime($from);


        $query = "select tid, servertime, nocName, status, nid,machine,priority,serverdate,count(tid) Cnt,machineOs,consoleId,scrip,eventIdx,site,clientversion from (select tid, servertime, nocName, nocStatus as status, nid,  machine,priority,FROM_UNIXTIME(servertime, '%Y-%m-%d') serverdate,machineOs,consoleId,scrip,eventIdx,site,clientversion from  " . $GLOBALS['PREFIX'] . "event.tempNotification where username='admin' and servertime between $preTime and $today and machine = '$searchVal' and site='$sitename' and nid = '$nid' ORDER BY tid desc) as K  group by nid,serverdate order by nid,serverdate,priority";
        $notfCnt = find_many($query, $db);
        if (safe_count($notfCnt) > 0) {
            return $notfCnt;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function Notify_getGrpGraphdata_EL($db, $priority, $nid, $user, $searchType, $searchVal)
{

    $from = date('Y-m-d', strtotime('-15 days'));
    $today = date('Y-m-d', time());

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');
        } else {
            $siteVal = $searchVal;
        }
        $siteName = '"sitename": "' . $siteVal . '"';
        $sqlRes = getNotifyGroupSummary($priority, $siteName, $from, $today);
    } else if ($searchType == 'ServiceTag') {
        $machine = '"machine": "' . $searchVal . '"';
        $sqlRes = getNotifyGroupSummary($priority, $machine, $from, $today);
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);

        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');

        $machine = '"machine": "' . $fmchStr . '"';
        $sqlRes = getNotifyGroupSummary($priority, $machine, $from, $today);
    }

    $resArray = safe_json_decode($sqlRes, TRUE);
    foreach ($resArray['aggregations']['id1_count']['buckets'] as $val) {
        $dateStamp = $val['key'];
        foreach ($val['id2_count']['buckets'] as $index) {
            foreach ($index['id3_count']['buckets'] as $vals) {
                $status = $vals['top_sales_hits ']['hits']['hits'][0]['_source']['nocstatus'];
                $idx = $vals['top_sales_hits ']['hits']['hits'][0]['_source']['eventIdx'];
                $id = $vals['top_sales_hits ']['hits']['hits'][0]['_source']['id'];

                if ($status == 'Fixed') {
                    $arr[$dateStamp]['fixed_count'][$id]['idx'][] = $idx;
                } else if ($status == '' || $status == null) {
                    $val = $status . '----' . $idx . '\n';
                    $arr[$dateStamp]['no_action_taken_count'][$id]['idx'][] = $idx;
                } else {
                    $arr[$dateStamp]['others_count'][$id]['idx'][] = $idx;
                    $val = $status . '----' . $idx . '\n';
                }
            }
        }
    }

    if (isset($arr)) {
        foreach ($arr as $key => $value) {
            $count = 0;
            $count1 = 0;
            $count2 = 0;
            $count3 = 0;

            if (isset($value['fixed_count'])) {
                $count1 = safe_count($value['fixed_count']);
                $count = $count + $count1;
            }
            if (isset($value['no_action_taken_count'])) {
                $count2 = safe_count($value['no_action_taken_count']);
                $count = $count + $count2;
            }
            if (isset($value['others_count'])) {
                $count3 = safe_count($value['others_count']);
                $count = $count + $count3;
            }


            $dt_var = $key;
            $dt = date("d M", strtotime($dt_var));
            $recordList[] = array($dt, $count2, $count3, $count1);
        }
        return $recordList;
    } else {
        return array();
    }
}

function Notify_GetHoursTrend_EL($key, $db, $user, $searchType, $searchVal, $priority, $hours)
{
    $sqlWh = '';

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= '{"term": {"sitename": "' . $siteVal . '"}}';
        } else {
            $sqlWh .= '{"term": {"sitename": "' . $searchVal . '"}}';
        }
    } else if ($searchType == 'ServiceTag') {
        $passlevel = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= '{"term": {"machine": "' . $searchVal . '"}}';
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= '{"term": {"sitename": "' . $passlevel . '"}},{"term": {"machine": "' . $searchVal . '"}}';
            } else {
                $sqlWh .= '{"term": {"machine": "' . $searchVal . '"}}';
            }
        }
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= '{"term": {"machine": "' . $fmchStr . '"}}';
    }

    if ($hours == 1) {
        $current1Hour = date("Y-m-d H");
        $last1Hour = date("Y-m-d H", time() - 3600);

        $sql1 = getNotificationOneHourTrend($current1Hour, $sqlWh, $priority);
        $current1HourSql = formatELDataNotify($sql1);
        $hr1 = safe_count($current1HourResult);

        $sql2 = getNotificationOneHourTrend($last1Hour, $sqlWh, $priority);
        $last1HourResult = formatELDataNotify($sql2);
        $hr1Diff = safe_count($last1HourResult);
        if ($hr1Diff > 0) {
            $difference1 = $hr1Diff - $hr1;
        } else {
            $difference1 = $hr1;
        }
        return array('1hrDiff' => $difference1, '1hr' => $hr1);
    } else if ($hours == 24) {
        $current24HourStart = date("Y-m-d H");
        $current24HourEnd = date("Y-m-d H", strtotime('-23 hours'));
        $last24HourStart = $current24HourEnd;
        $last24HourEnd = date("Y-m-d H", strtotime('-48 hours'));

        $sql3 = getNotificationTwentyFourHourTrend($sqlWh, $priority, $current24HourEnd, $current24HourStart);
        $current24HourResult =  format24ElNotifyData($sql3);
        $prio24 = safe_count($current24HourResult);

        $sql4 = getNotificationTwentyFourHourTrend($sqlWh, $priority, $last24HourEnd, $last24HourStart);
        $last24HourResult =  format24ElNotifyData($sql4);
        $prioLast24 = safe_count($last24HourResult);

        if ($prioLast24 > 0) {
            $difference2 = $prioLast24 - $prio24;
        } else {
            $difference2 = $prio24;
        }
        return array('24hrsDiff' => $difference2, '24hrs' => $prio24);
    }
}


function Notify_getLimitedNotificationDetails_EL($db, $nid, $searchType, $searchVal, $status, $user, $passlevel)
{

    $username = $_SESSION["user"]["username"];
    $sqlWh = '';

    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= '{"term": {"site": "' . $siteVal . '"}}';
        } else {
            $sqlWh .= '{"term": {"site": "' . $searchVal . '"}}';
        }
    } else if ($searchType == 'ServiceTag') {
        if ($passlevel == 'All' || $passlevel == '') {
            $sqlWh .= '{"term": {"machine": "' . $searchVal . '"}}';
        } else {
            $levelType = isset($_SESSION['passlevel']) ? $_SESSION['passlevel'] : '';
            if ($levelType == 'Sites') {
                $sqlWh .= '{"term": {"site": "' . $passlevel . '"}},{"term": {"machine": "' . $searchVal . '"}}';
            } else {
                $sqlWh .= '{"term": {"machine": "' . $searchVal . '"}}';
            }
        }
    } else if ($searchType == 'Groups' || $searchType == 'Group') {
        $dataScope = UTIL_GetSiteScope($db, $searchVal, $searchType);
        $data = DASH_GetGroupsMachines($key, $db, $dataScope);
        $machines = "'" . implode("','", $data) . "'";

        $sqlWh .= '{"term": {"machine": "' . $machines . '"}}';
    }
    if ($nid != '') {
        $sqlWh .= ',{"term": {"nid": "' . $nid . '"}}';
    }

    $from = date('Y-m-d', strtotime('-14 days'));
    $today = time();
    $preTime = strtotime($from);
    $result = getNotifyDetails($nid, $sqlWh, $preTime, $today, $username);
    $resultArra = formatNotifyDetailsEL($result);
    return $resultArra;
}

function NOTIFY_GetIncidentRecords_EL($db, $user, $searchType, $searchValue, $start_day, $end_day, $rparentname)
{

    $whereClause = Notify_GetWhereClause($db, $user, $searchType, $searchVal, $searchVal, $rparentname);
    $lnids = Notify_getEnabledNotifications($db, "");

    if ($limit === "0") {
        $sql = "SELECT count(tid) as count FROM  " . $GLOBALS['PREFIX'] . "event.tempNotification WHERE  servertime BETWEEN "
            . "$start_day AND $end_day AND nid IN ($lnids) $whereClause ORDER BY tid DESC";
        $res = find_one($sql, $db);
        return $res['count'];
    } else {
        $sql = "SELECT tid, servertime, priority, nocName, nocStatus, nid,  machine, dartExecutionStat FROM  " . $GLOBALS['PREFIX'] . "event.tempNotification WHERE  "
            . " servertime BETWEEN $start_day AND $end_day AND nid IN ($lnids) $whereClause $limit";
        $res = find_many($sql, $db);

        return $res;
    }
}

function formatELDataNotify($arr)
{

    $json_array = safe_json_decode($arr, TRUE);
    $value = array();

    if (safe_count($json_array['hits']['hits']) > 0) {
        foreach ($json_array['hits']['hits'] as $key => $val) {
            $value[$key] = $val['_source']['id'];
        }
    }
    return $value;
}

function  format24ElNotifyData($arr)
{

    $jsonArr = safe_json_decode($arr, TRUE);
    $value = array();
    if (safe_count($jsonArr['aggregations']['id1_count']['buckets']) > 0) {
        foreach ($jsonArr['aggregations']['id1_count']['buckets'] as $key => $val) {
            $value[$key] = $val['doc_count'];
        }
    }
    return $value;
}

function formatNotifyDetailsEL($arr)
{

    $jsonArray = safe_json_decode($arr, TRUE);
    $value = array();
    if (safe_count($jsonArray['aggregations']['id1_count']['buckets']) > 0) {
        $aggr1 = $jsonArray['aggregations']['id1_count']['buckets'];
        $i = 0;
        foreach ($aggr1 as $val) {
            $aggr2 = $val['id2_count']['buckets'];
            foreach ($aggr2 as $vals) {
                $value[$i]['eventCount'] = $vals['top_sales_hits']['hits']['total'];
                $value[$i]['consoleId'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['consoleId'];
                $value[$i]['site'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['site'];
                $value[$i]['machine'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['machine'];
                $value[$i]['nid'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['nid'];
                $value[$i]['customerNum'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['customerNum'];
                $value[$i]['eventtime'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['servertime'];
                $value[$i]['orderNum'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['orderNum'];
                $value[$i]['machineOs'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['machineOs'];
                $value[$i]['priority'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['priority'];
                $value[$i]['nocstatus'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['nocstatus'];
                $value[$i]['dartExecutionStat'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['dartExecutionStat'];
                $value[$i]['tid'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['tid'];
                $value[$i]['notes'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['notes'];
                $value[$i]['text1'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['text1'];
                $value[$i]['eventIdx'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['eventIdx'];
                $value[$i]['solutionPush'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['solutionPush'];
                $value[$i]['action'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['action'];
                $value[$i]['nocName'] = $vals['top_sales_hits']['hits']['hits'][0]['_source']['nocName'];


                $i++;
            }
        }
    }
    return $value;
}

function formatNotifyGridData($resultArra)
{

    $recordList = '';
    if ($resultArra > 0) {
        foreach ($resultArra as $key => $value) {
            $machine = $value['machine'];
            $servertime = date('m/d/Y H:i:s', $value['eventtime']);
            $eventCount = $value['eventCount'];
            $statusVal = $value['nocstatus'];
            $notfName = $value['nocName'];
            $eventList = $value['eventIdx'];
            $cidList = $value['consoleId'];
            $machineName = $value['machine'];
            $machineOS = $value['machineOs'];
            $clientVer = $value['clientversion'];
            $notifyDt = $value['notifyDt'];
            $notes = $value['notes'];
            $dartExeStat = $value['dartExecutionStat'];
            if ($clientVer == '') {
                $clientVer = 'NULL';
            }

            if ($statusVal == 'Fixed' && $dartExeStat != '') {
                $status = '<p style="color:green;">Completed</p>';
                $statCheck = "Completed";
            } else if ($statusVal == 'Fixed' && ($dartExeStat == '' || $dartExeStat == NULL)) {
                $status = '<p style="color:orange;">Pending</p>';
                $statCheck = "Pending";
            } else if ($statusVal != 'Fixed' && $statusVal != '' && $statusVal != NULL) {
                $status = '<p style="color:orange;">Pending</p>';
                $statCheck = "Pending";
            } else if ($statusVal == '') {
                $status = '<p style="color:red;">New</p>';
                $statCheck = "New";
            }

            if ($machineOS == '') {
                $machineOS = 'NULL';
            }

            $tid = $value['tid'];
            $nid = $value['nid'];

            $id = $value['nid'] . "~~" . $notfName . "~~" . $value['site'] . "~~" . $machineName . "~~" . $clientVer . "~~" . $cidList . "~~" . $eventList . "~~" . $notifyDt . "~~" . $value['tid'] . "~~" . $machineOS;
            $machineText = '<p class="text-overflow" title="' . $machine . '">' . $machine . '</p>';
            if ($notes == '') {
                $notestr = '<p onclick="addNotes(' . $tid . ',' . $cidList . ',' . $eventList . ',' . $nid . ');" style="cursor: pointer;text-decoration: none;color:#0096D6;" title="Add Note">Add</p>';
            } else {
                $notestr = '<p onclick="addNotes(' . $tid . ',' . $cidList . ',' . $eventList . ',' . $nid . ');" style="cursor: pointer;text-decoration: none;color:#0096D6;" title="View/Edit Note">View/Edit</p>';
            }

            $chkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="check user_check" name="checkNoc" id="' . $tid . '" value="' . $id . '" onclick="uniqueCheckBox(\'' . $statCheck . '\');"><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
            $recordList[] = array("check_data" => $chkBox, "machine" => $machineText, "servertime" => safe_addslashes($servertime), "eventCount" => $value['eventCount'], "nocStatus" => $status, "notes" => $notestr);
        }
    } else {
        $recordList = [];
    }
    return $recordList;
}
