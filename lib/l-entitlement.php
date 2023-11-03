<?php

function Entl_getGraphCnt($key, $db, $user, $searchType, $searchVal)
{


    if ($searchType == 'Site' || $searchType == 'Sites') {
        if ($searchVal == 'All') {
            $sites = DASH_GetSites($key, $db, $user);
            $siteVal = '';
            foreach ($sites as $value) {
                $siteVal .= "'" . $value . "',";
            }
            $siteVal = rtrim($siteVal, ',');

            $sqlWh .= " C.site in ($siteVal)";
        } else {
            $sqlWh .= " C.site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $sqlWh .= " C.host = '" . $searchVal . "'";
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= " C.host in ($fmchStr)";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $entitl_sql = "select C.site,C.host,A.hostName,D.startDate,D.endDate,D.obligationIdentifier,D.obligationTypeCode,D.obligationStatus,D.warrantyTypeCode,A.value3 from " . $GLOBALS['PREFIX'] . "core.Census C left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN " . $GLOBALS['PREFIX'] . "core.hpEntitlement D on C.site=D.siteName and C.host=D.machine where $sqlWh";
        $entitl_res = find_many($entitl_sql, $db);
        if (safe_count($entitl_res) > 0) {
            return $entitl_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function Entl_getEntitlementDetails($key, $db, $searchType, $searchVal, $status, $user)
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

            $sqlWh .= " C.site in ($siteVal)";
        } else {
            $sqlWh .= " C.site = '" . $searchVal . "'";
        }
    } else if ($searchType == 'ServiceTag') {
        $sqlWh .= " C.host = '" . $searchVal . "'";
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $mchStr = '';
        foreach ($machines as $value) {
            $mchStr .= "'" . $value . "',";
        }
        $fmchStr = rtrim($mchStr, ',');
        $sqlWh .= " C.host in ($fmchStr)";
    }

    $wh = '';

    if ($status == 'carePack') {
        $wh = 'and D.obligationTypeCode="P" and D.obligationStatus="1"';
    } elseif ($status == 'warranty') {
        $wh = 'and D.obligationTypeCode="W" and D.obligationStatus="1"';
    } elseif ($status == 'outWarranty') {
        $wh = 'and D.obligationTypeCode="W" and D.obligationStatus="0"';
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $entitl_sql = "select C.site,C.host,A.hostName,D.startDate,D.endDate,D.obligationIdentifier,D.obligationTypeCode,D.obligationStatus,D.warrantyTypeCode,A.value3 from " . $GLOBALS['PREFIX'] . "core.Census C left join " . $GLOBALS['PREFIX'] . "asset.tempAssetSummary A on C.site=A.site and C.host=A.machineName and A.groupId=3 LEFT JOIN " . $GLOBALS['PREFIX'] . "core.hpEntitlement D on C.site=D.siteName and C.host=D.machine where $sqlWh $wh";
        $entitl_res  = find_many($entitl_sql, $db);
        if (safe_count($entitl_res) > 0) {
            return $entitl_res;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}


function Entl_exportNotificationDetails($key, $db, $nid, $searchType, $searchVal, $status, $user)
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
        $sqlWh .= "and machine = '" . $searchVal . "'";
    } else if ($searchType == 'Groups') {
        $machines = DASH_GetGroupsMachines($key, $db, $searchVal);
        $sqlWh .= "and machine in ($machines)";
    }

    $key = DASH_ValidateKey($key);
    if ($key) {

        $from       = date('Y-m-d', strtotime('-14 days'));
        $today      = time();
        $preTime    = strtotime($from);

        $query = "select tid, consoleId, servertime as servertimeUNIX,FROM_UNIXTIME(servertime, '%Y-%m-%d %h:%m') eventtime,FROM_UNIXTIME(servertime, '%Y-%m-%d') notifyDt, nocName, eventIdx, nocStatus as status, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat, count(*) as eventCount,notes from (select tid, consoleId, servertime, nocName, eventIdx, nocStatus, nid, site, machine, clientversion, timeExecuted, machineManufacture,agentId, customerNum, orderNum, machineOs, dartExecutionStat,notes from  " . $GLOBALS['PREFIX'] . "event.tempNotification where servertime between $preTime and $today $sqlWh $stat ORDER BY tid desc) as x group by FROM_UNIXTIME(servertime, '%Y-%m-%d'),site,machine,nid ORDER BY servertime desc";
        $notfCnt  = find_many($query, $db);
        if (safe_count($notfCnt) > 0) {
            return $notfCnt;
        } else {
            return array();
        }
    } else {
        echo "Your key has been expired";
    }
}
