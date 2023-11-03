<?php





function get_deviceCensus($user_sites, $db)
{

    $sites = $user_sites;
    $in  = str_repeat('?,', safe_count($sites) - 1) . '?';
    $sql = $db->prepare("select count(id) as totalMachines from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in)");
    $sql->execute($sites);
    $res = $sql->fetch();
    $machineCount = $res['totalMachines'];
    return $machineCount;
}



function get_assetCount($user_sites, $db)
{

    $sites = $user_sites;
    $in  = str_repeat('?,', safe_count($sites) - 1) . '?';
    $sql = $db->prepare("select count(host) as assetmachine from " . $GLOBALS['PREFIX'] . "asset.Machine where cust in ($in)");
    $sql->execute($sites);
    $res = $sql->fetch();
    $assetmachine = $res['assetmachine'];

    return $assetmachine;
}



function get_configCount($user_sites, $db)
{

    $sites = $user_sites;
    $in  = str_repeat('?,', safe_count($sites) - 1) . '?';
    $sql = $db->prepare("select count(R.censusid) as number from " . $GLOBALS['PREFIX'] . "core.Revisions as R, " . $GLOBALS['PREFIX'] . "core.Census as X, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineCategories as B where X.site in ($in) and X.id = R.censusid and M.censusuniq = X.censusuniq and G.mgroupuniq = M.mgroupuniq and G.mcatuniq = B.mcatuniq and B.category='Site' group by X.site");
    $sql->execute($sites);
    $resultConfig = $sql->fetchAll();

    $configCount = 0;
    foreach ($resultConfig as $row) {
        $configCount += $row['number'];
    }

    return $configCount;
}



function get_overview_last4Hours($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(id) as last4hours from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in) and last >= UNIX_TIMESTAMP( NOW() - INTERVAL 4 HOUR) and last <= UNIX_TIMESTAMP(SUBDATE(NOW(), 0))");
        $sql->execute($user_sites);
        $res = $sql->fetch();
        $resultCount = $res['last4hours'];
        return $resultCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_overview_last24Hours($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(id) as last4hours from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in) and last >= UNIX_TIMESTAMP( NOW() - INTERVAL 4 HOUR) and last <= UNIX_TIMESTAMP(SUBDATE(NOW(), 0))");
        $sql->execute($user_sites);
        $res = $sql->fetch();

        $returnCount = $res['last24hours'];
        return $returnCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_overview_last7Days($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(id) as last7days from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in) and last >= UNIX_TIMESTAMP(SUBDATE(NOW(), 7))  and last <= UNIX_TIMESTAMP( NOW() - INTERVAL 24 HOUR)");
        $sql->execute($user_sites);
        $total = $sql->fetch();
        $resultCount = 0;
        if (safe_count($total) > 0) {
            $resultCount = $total['last7days'];
        }
        return $resultCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_overview_above30Days($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(id) as below30days from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in) and last >= UNIX_TIMESTAMP(SUBDATE(NOW(), 30)) and last < UNIX_TIMESTAMP(SUBDATE(NOW(), 7))");
        $sql->execute($user_sites);
        $res = $sql->fetch();
        $returnCount = $res['below30days'];

        return $returnCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}

function get_overview_census($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(id) as above30days from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in) and last <= UNIX_TIMESTAMP(SUBDATE(NOW(), 30))");
        $sql->execute($user_sites);
        $res = $sql->fetch();
        $returnCount = $res['above30days'];

        return $returnCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_SWUpdateSite($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(*) updateSiteCn from " . $GLOBALS['PREFIX'] . "swupdate.UpdateSites where sitename in ($in) group by sitename");
        $sql->execute($user_sites);
        $res = $sql->fetchAll();
        $configCount = 0;
        foreach ($res as $row) {
            $configCount += $row['updateSiteCn'];
        }

        return $configCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_SWUpdateMachine($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(*) updateMachineCn from " . $GLOBALS['PREFIX'] . "swupdate.UpdateMachines where sitename in ($in)");
        $sql->execute($user_sites);
        $res = $sql->fetch();
        $updateCount = $res['updateMachineCn'];
        return $updateCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_onlineMachine($user_sites, $db)
{

    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select count(id) as onlineCount from " . $GLOBALS['PREFIX'] . "core.tempMachine where site in ($in) and last >= UNIX_TIMESTAMP(SUBDATE(NOW(), 30)) and last <= UNIX_TIMESTAMP(SUBDATE(NOW(), 0)) and status='online' ");
        $sql->execute($user_sites);
        $res = $sql->fetch();
        $returnCount = $res['onlineCount'];
        return $returnCount;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 0;
    }
}



function get_notificationSiteData($from, $to, $user_sites, $db)
{

    try {
        $params = array_merge($user_sites, [$from, $to]);
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $sql = $db->prepare("select sum(fixedCount+othersCount) as actionCount, sum(fixedCount) as fixed, sum(tcount) as notificationCount, graphDate from " . $GLOBALS['PREFIX'] . "event.SummaryGraphSite where site IN ($in) and graphDate >= ? and graphDate <= ? group by graphDate ");
        $sql->execute($user_sites);
        $res = $sql->fetchAll();

        $nTotal = 0;
        $nAction = 0;
        $nFix = 0;
        foreach ($resultConfig as $row) {

            $nTotal = $nTotal + $row['notificationCount'];
            $nAction = $nAction + $row['actionCount'];
            $nFix = $nFix + $row['fixed'];
        }

        $graphData = array($nTotal, $nFix, $nAction);

        return $graphData;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}



function get_callBackSparkSiteData($from, $to, $user_sites, $db)
{


    try {
        $params = array_merge([$from, $to], $user_sites);
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $callbackGraph = $db->prepare("select sum(callbackCount) as callbackCount,sum(progressCount) as progressCount, sum(fixedCount) as fixedCount,callbackDate from summarycallback where callbackDate >= ? and callbackDate <= ? and site in ($in)  group by callbackDate order by scId ");
        $callbackGraph->execute($params);
        $callbackdata = $callbackGraph->fetchAll();
        $callback = array();
        $progress = array();
        $fixed = array();

        foreach ($callbackdata as $row) {

            $callback[$row['callbackDate']] = $row['callbackCount'];
            $progress[$row['callbackDate']] = $row['progressCount'];
            $fixed[$row['callbackDate']] = $row['fixedCount'];
        }

        $totalCallbk = array_sum($fixed) + array_sum($progress) + array_sum($callback);

        $totalFixed = array_sum($fixed) + array_sum($progress);

        if ($totalFixed > 0) {
            $fixper = round(($totalFixed / $totalCallbk) * 100);
        } else {
            $fixper = 0;
        }

        $graphData = array($totalCallbk, $fixper, $totalFixed);

        return $graphData;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}



function get_resolutionPushData($from, $to, $user_sites, $db)
{

    try {
        $params = array_merge([$from, $to], $user_sites);
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $nTotal = 0;
        $resolutionGraph = $db->prepare("select sum(auditcount+schedulecount) as resCount,sum(auditcount) as auditcount,sum(schedulecount) as schedulecount,createdtime from " . $GLOBALS['PREFIX'] . "node.summaryAuditSchedule where createdtime >= ? and createdtime <= ? and sitename in ($in) group by createdtime");
        $resolutionGraph->execute($params);
        $resolutiondata = $resolutionGraph->fetchAll();

        $ra = array();
        $rs = array();
        $rp = array();

        foreach ($resolutiondata as $row) {

            $ra[$row['createdtime']] = $row['auditcount'];
            $rs[$row['createdtime']] = $row['schedulecount'];
            $rp[$row['createdtime']] = 0;

            $nTotal = $nTotal + $row['resCount'];
        }

        $auditCount = array_sum($ra);

        if ($auditCount > 0) {
            $resPer = round(($auditCount / $nTotal) * 100);
        } else {
            $resPer = 0;
        }

        $resolData = array($nTotal, $resPer);

        return $resolData;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function serviceLogAllTopBarData($searchType, $searchValue, $loggedUser)
{

    try {
        $db = pdo_connect();
        $okCnt = 0;
        $warnCnt = 0;
        $alertCnt = 0;
        $chdCnt = 0;
        $totalCnt = 0;

        $params = array_merge($searchValue, [$loggedUser]);
        $in  = str_repeat('?,', safe_count($searchValue) - 1) . '?';
        $serviceLogGraph = $db->prepare("select count(status) as count, status from " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay where censusid in(select id from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in)) and userid =? group by status order by status");
        $serviceLogGraph->execute($params);
        $serviceLogData = $serviceLogGraph->fetchAll();

        foreach ($serviceLogData as $row) {

            $slStatus = $row['status'];
            $totalCnt = $totalCnt + $row['count'];

            if ($slStatus == '1') {
                $okCnt = $okCnt + $row['count'];
            } else if ($slStatus == '2') {
                $warnCnt = $warnCnt + $row['count'];
            } else if ($slStatus == '3') {
                $alertCnt = $alertCnt + $row['count'];
            } else {
                if ($slStatus == '4') {
                    $chdCnt = $chdCnt + $row['count'];
                } else {
                    $chdCnt = $chdCnt + 0;
                }
            }
        }

        $resolData = array($okCnt, $warnCnt, $alertCnt, $chdCnt, $totalCnt);
        return $resolData;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}



function get_serviceLogStackGraph($user_sites, $admin_name, $db)
{

    $loggedUser = $admin_name;

    $params = array_merge([$loggedUser], $user_sites, [$loggedUser], $user_sites, [$loggedUser], $user_sites, [$loggedUser], $user_sites);
    $in  = str_repeat('?,', safe_count($searchValue) - 1) . '?';
    $serviceLogGraph = $db->prepare("select DATE_FORMAT(FROM_UNIXTIME(servertime),'%Y-%m-%d') tm,status,sum(count) as count,case itemtype when 5 then 'MonItems' when 7 then 'Security' when 8 then 'Resources' when 10 then 'Maintenance' when 9 then 'EventItems' end itemtype,itemtype itemvalue from
		(SELECT servertime,case status when 1 then  'OK' when 2 then 'Warning' when 3 then 'Alert' when 4 then 'Changed' end status,count(*)count,itemtype FROM " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay E," . $GLOBALS['PREFIX'] . "core.Census C
		where itemtype in (5) and status in(1,2,3,4)  and userid=? and itemid in(select monitemid from MonitorItems where enabled=1) and E.censusid = C.id and C.site in ($in) group by servertime,status
		union all
		SELECT servertime,case status when 1 then  'OK' when 2 then 'Warning' when 3 then 'Alert' when 4 then 'Changed' end status,count(*)count,itemtype FROM " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay E," . $GLOBALS['PREFIX'] . "core.Census C
		where itemtype in (7) and status in(1,2,3,4)   and userid=? and itemid in(select secitemid from SecurityItems where enabled=1) and E.censusid = C.id and C.site in ($in) group by servertime,status
		union all
		SELECT servertime,case status when 1 then  'OK' when 2 then 'Warning' when 3 then 'Alert' when 4 then 'Changed' end status,count(*)count,itemtype FROM " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay E," . $GLOBALS['PREFIX'] . "core.Census C
		where itemtype in (8) and status in(1,2,3,4)  and userid=? and itemid in(select resitemid from ResourceItems where enabled=1) and E.censusid = C.id and C.site in ($in) group by servertime,status
		union all
		SELECT servertime,case status when 1 then  'OK' when 2 then 'Warning' when 3 then 'Alert' when 4 then 'Changed' end status,count(*)count,itemtype FROM " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay E," . $GLOBALS['PREFIX'] . "core.Census C
		where itemtype in (9) and status in(1,2,3,4)  and userid=?  and itemid in(select eventitemid from EventItems where enabled=1) and E.censusid = C.id and C.site in ($in) group by servertime,status
		union all
		SELECT servertime,case status when 1 then  'OK' when 2 then 'Warning' when 3 then 'Alert' when 4 then 'Changed' end status,count(*)count,itemtype FROM " . $GLOBALS['PREFIX'] . "dashboard.EventBasedDisplay E," . $GLOBALS['PREFIX'] . "core.Census C
		where itemtype in (10) and status in(1,2,3,4)  and userid=? and itemid in(select maintitemid from MaintenanceItems where enabled=1) and E.censusid = C.id and C.site in ($in) group by servertime,status
		)a group by status,itemtype,status order by tm");
    $serviceLogGraph->execute($params);
    $serviceLogData = $serviceLogGraph->fetchAll();

    $avalOK = 0;
    $avalALERT = 0;
    $avalWARNING = 0;
    $avalCHANGE = 0;

    $secOK = 0;
    $secALERT = 0;
    $secWARNING = 0;
    $secCHANGE = 0;

    $resOK = 0;
    $resALERT = 0;
    $resWARNING = 0;
    $resCHANGE = 0;

    $mainOK = 0;
    $mainALERT = 0;
    $mainWARNING = 0;
    $mainCHANGE = 0;

    $eveOK = 0;
    $eveALERT = 0;
    $eveWARNING = 0;
    $eveCHANGE = 0;

    if (safe_count($serviceLogData) > 0) {


        foreach ($serviceLogData as $row) {

            $itemVal = $row['itemvalue'];
            $slStatus = $row['status'];

            if ($itemVal == 5) {

                if ($slStatus == 'OK') {
                    $avalOK = $row['count'];
                } elseif ($slStatus == 'Warning') {
                    $avalWARNING = $row['count'];
                } elseif ($slStatus == 'Alert') {
                    $avalALERT = $row['count'];
                } elseif ($slStatus == 'Changed') {
                    $avalCHANGE = $row['count'];
                }
            } elseif ($itemVal == 7) {

                if ($slStatus == 'OK') {
                    $secOK = $row['count'];
                } elseif ($slStatus == 'Warning') {
                    $secWARNING = $row['count'];
                } elseif ($slStatus == 'Alert') {
                    $secALERT = $row['count'];
                } elseif ($slStatus == 'Changed') {
                    $secCHANGE = $row['count'];
                }
            } elseif ($itemVal == 8) {

                if ($slStatus == 'OK') {
                    $resOK = $row['count'];
                } elseif ($slStatus == 'Warning') {
                    $resWARNING = $row['count'];
                } elseif ($slStatus == 'Alert') {
                    $resALERT = $row['count'];
                } elseif ($slStatus == 'Changed') {
                    $resCHANGE = $row['count'];
                }
            } elseif ($itemVal == 9) {

                if ($slStatus == 'OK') {
                    $eveOK = $row['count'];
                } elseif ($slStatus == 'Warning') {
                    $eveWARNING = $row['count'];
                } elseif ($slStatus == 'Alert') {
                    $eveALERT = $row['count'];
                } elseif ($slStatus == 'Changed') {
                    $eveCHANGE = $row['count'];
                }
            } elseif ($itemVal == 10) {

                if ($slStatus == 'OK') {
                    $mainOK = $row['count'];
                } elseif ($slStatus == 'Warning') {
                    $mainWARNING = $row['count'];
                } elseif ($slStatus == 'Alert') {
                    $mainALERT = $row['count'];
                } elseif ($slStatus == 'Changed') {
                    $mainCHANGE = $row['count'];
                }
            }
        }

        $okval = array();
        $warningval = array();
        $alertval = array();
        $changeval = array();

        $okval[] = $avalOK;
        $okval[] = $secOK;
        $okval[] = $resOK;
        $okval[] = $mainOK;
        $okval[] = $eveOK;


        $warningval[] = $avalWARNING;
        $warningval[] = $secWARNING;
        $warningval[] = $resWARNING;
        $warningval[] = $mainWARNING;
        $warningval[] = $eveWARNING;

        $alertval[] = $avalALERT;
        $alertval[] = $secALERT;
        $alertval[] = $resALERT;
        $alertval[] = $mainALERT;
        $alertval[] = $eveALERT;

        $changeval[] = $avalCHANGE;
        $changeval[] = $secCHANGE;
        $changeval[] = $resCHANGE;
        $changeval[] = $mainCHANGE;
        $changeval[] = $eveCHANGE;

        $okSum = array_sum($okval);
        $warSum = array_sum($warningval);
        $alertSum = array_sum($alertval);
        $changeSum = array_sum($changeval);

        $totalSL = $okSum + $warSum + $alertSum + $changeSum . '_' . $warSum;
        return $totalSL;
    } else {
        $totalSum = 0;
        $totalWar = 0;
        return $totalSum . '_' . $totalWar;
    }
}

function get_sitelist($user_sites, $db)
{

    $siteList = array();
    try {
        $in  = str_repeat('?,', safe_count($user_sites) - 1) . '?';
        $siteQuery = $db->prepare("select customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where  customer in ($in) group by customer");
        $siteQuery->execute($user_sites);
        $siteListdata = $siteQuery->fetchAll();

        foreach ($siteListdata as $value) {
            $siteList[$value['name']] = $value['name'];
        }

        return $siteList;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);

        return NULL;
    }
}

function getDefaultSite($db, $username)
{
    db_change($GLOBALS['PREFIX'] . "core", $db);

    try {
        $sql = $db->prepare("select C.id as cid, C.customer as site, count(H.id) as number from " . $GLOBALS['PREFIX'] . "core.Census as H, Customers as C "
            . "where H.site = C.customer and C.username = ? group by H.site order by CONVERT(H.site USING latin1)");
        $sql->execute([$username]);
        $res = $sql->fetchAll();
        $total = safe_count($res);

        $siteQuery = $db->prepare("select count(id) as totalSites, customer as name from " . $GLOBALS['PREFIX'] . "core.Customers where username=? limit 1");
        $siteQuery->execute([$username]);
        $siteQueryRes = $siteQuery->fetch();
        $defaultSite['defsite'] = $siteQueryRes['name'];
        $defaultSite['totalSitesCount'] = $total;

        return $defaultSite;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);

        return NULL;
    }
}




function get_customerDownloadURL($custid, $db)
{
    global $configServer;
    try {

        $downloadUrl = '';
        $rdownloadUrl = '';

        $custOrder = $db->prepare("select customerNum,orderNum,processId,compId,downloadId from " . $GLOBALS['PREFIX'] . "agent.customerOrder C where C.compId=?");
        $custOrder->execute([$custid]);
        $res = $custOrder->fetchAll();
        if (safe_count($res) == 1) {
            foreach ($res as $value) {
                $custNum   = $value['customerNum'];
                $orderNum  = $value['orderNum'];
                $processId = $value['processId'];

                $custSer = $db->prepare("select sid from " . $GLOBALS['PREFIX'] . "agent.serviceRequest where customerNum=? and orderNum = ? and processId = ? and compId = ?");
                $custSer->execute([$custNum, $orderNum, $processId, $custid]);
                $serRes = $custSer->fetchAll();
                if (safe_count($serRes) == 0) {
                    $downloadUrl = $value['downloadId'];
                }
            }
        }

        if ($downloadUrl == '') {
            $rdownloadUrl = 'NotFound';
        } else {
            $rdownloadUrl = $configServer . 'eula.php?id=' . $downloadUrl;
        }
        return $rdownloadUrl;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        return 'NotFound';
    }
}
