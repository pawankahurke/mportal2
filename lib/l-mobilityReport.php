<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';

function machineReport($site, $machine, $fromDate, $toDate, $db)
{

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate )";

    $sql1 = "SELECT count(distinct machine) AS totaldevice FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName = '$site' AND machine='$machine' ";
    $totalMachine = find_one($sql1, $db);

    $sql2 = "SELECT count(timespoke) AS totalcalls, count(timespokeroaming) AS roaming FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName = '$site' AND machine='$machine' $filterQuery";
    $totalTime = find_one($sql2, $db);

    $sql3 = "SELECT count(id) AS incoming, sum(timespoke) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND siteName = '$site' AND timespokeroaming = 0  AND machine='$machine' $filterQuery";
    $totalin = find_one($sql3, $db);

    $sql4 = "SELECT count(id) AS outgoing, sum(timespoke) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND siteName = '$site' AND timespokeroaming = 0 AND machine='$machine' $filterQuery";
    $totalout = find_one($sql4, $db);

    $sql5 = "SELECT count(id) AS incoming, sum(timespokeroaming) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND siteName = '$site' AND timespoke  = 0 AND machine='$machine' $filterQuery";
    $totalRin = find_one($sql5, $db);

    $sql6 = "SELECT count(id) AS outgoing, sum(timespokeroaming) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND siteName = '$site' AND timespoke = 0 AND machine='$machine' $filterQuery";
    $totalRout = find_one($sql6, $db);

    $totalRcalls = $totalRin['incoming'] + $totalRout['outgoing'];
    $totalcalls = $totalin['incoming'] + $totalout['outgoing'] + $totalRcalls;

    $totalRcallSec = $totalRin['totalinsec'] + $totalRout['totaloutsec'];
    $totalcallSec = $totalin['totalinsec'] + $totalout['totaloutsec'] + $totalRcallSec;

    $sql7 = "SELECT sum(mobileDataUsage) AS datausage FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='$machine' $filterQuery";
    $datausage = find_one($sql7, $db);

    $sql8 = "SELECT DISTINCT machine AS machines FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName = '$site' AND machine='$machine'";
    $machines = find_many($sql8, $db);

    $json = '{"Title" : "' . $machine . '",';
    $json .= '"Details" : {';
    $json .= '"TotalDevices" : "' . $totalMachine['totaldevice'] . '",';
    $json .= '"TotalCalls" : "' . $totalcalls . '",';
    $json .= '"TotalIn" : "' . $totalin['incoming'] . '",';
    $json .= '"TotalOut" : "' . $totalout['outgoing'] . '",';
    $json .= '"TotalRIn" : "' . $totalRin['incoming'] . '",';
    $json .= '"TotalROut" : "' . $totalRout['outgoing'] . '",';
    $json .= '"TotalCallSec" : "' . $totalcallSec . '",';
    $json .= '"TotalInSec" : "' . $totalin['totalinsec'] . '",';
    $json .= '"TotalOutSec" : "' . $totalout['totaloutsec'] . '",';
    $json .= '"TotalRInSec" : "' . $totalRin['totalinsec'] . '",';
    $json .= '"TotalROutSec" : "' . $totalRout['totaloutsec'] . '",';
    $json .= '"DataUsage" : "' . $datausage['datausage'] . '"},';

    $json .= '"MachineDetails" : [';

    foreach ($machines as $key => $value) {
        $machinename = $value['machines'];

        $sql9 = "SELECT count(id) AS incoming, sum(timespoke) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND machine = '$machinename' AND siteName = '$site' AND timespokeroaming = 0 $filterQuery";
        $sqlres = find_one($sql9, $db);
        $devicein = $sqlres['incoming'];
        $deviceinsec = $sqlres['totalinsec'];

        $sql10 = "SELECT count(id) AS outgoing, sum(timespoke) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND machine = '$machinename' AND siteName = '$site' AND timespokeroaming = 0 $filterQuery";
        $sqlres = find_one($sql10, $db);
        $deviceout = $sqlres['outgoing'];
        $deviceoutsec = $sqlres['totaloutsec'];

        $sql11 = "SELECT count(id) AS incoming, sum(timespokeroaming) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND machine = '$machinename' AND siteName = '$site' AND timespoke = 0 $filterQuery";
        $sqlres = find_one($sql11, $db);
        $deviceRin = $sqlres['incoming'];
        $deviceRinsec = $sqlres['totalinsec'];

        $sql12 = "SELECT count(id) AS outgoing, sum(timespokeroaming) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND machine = '$machinename' AND siteName = '$site' AND timespoke = 0 $filterQuery";
        $sqlres = find_one($sql12, $db);
        $deviceRout = $sqlres['outgoing'];
        $deviceRoutsec = $sqlres['totaloutsec'];

        $deviceRtotal = $deviceRin + $deviceRout;
        $devicetotal = $devicein + $deviceout + $deviceRtotal;

        $deviceRtotalsec = $deviceRinsec + $deviceRoutsec;
        $devicetotalsec = $deviceinsec + $deviceoutsec + $deviceRtotalsec;

        $sql13 = "SELECT count(timespoke) AS totalcalls, count(timespokeroaming) AS roaming, sum(timespoke) AS totalsec, sum(timespokeroaming) AS roamingsec"
            . " FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName = '$site' AND machine = '$machinename' $filterQuery";

        $machineres = find_one($sql13, $db);

        $sql14 = "SELECT sum(mobileDataUsage) AS datausage FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='$machinename' $filterQuery";
        $machinedataUsage = find_one($sql14, $db);

        $json .= '{';
        $json .= '"MachineName" : "' . $machinename . '",';
        $json .= '"TotalCalls" : "' . $devicetotal . '",';
        $json .= '"TotalIN" : "' . $devicein . '",';
        $json .= '"TotalOut" : "' . $deviceout . '",';
        $json .= '"TotalRoaming" : "' . $deviceRtotal . '",';
        $json .= '"TotalSeconds" : "' . $devicetotalsec . '",';
        $json .= '"TotalINSec" : "' . $deviceinsec . '",';
        $json .= '"TotalOutSec" : "' . $deviceoutsec . '",';
        $json .= '"TotalRSec" : "' . $deviceRtotalsec . '",';
        $json .= '"DataUsage" : "' . $machinedataUsage['datausage'] . '"';
        $json .= '},';
    }
    $json = rtrim($json, ",");

    $json .= ']';
    $json .= '}';
    return $json;
}

function siteReport($site, $machine, $fromDate, $toDate, $db)
{

    if (is_array($site)) {
        foreach ($site as $row => $val) {
            $siteList .= "'" . $val . "',";
        }
        $siteName = rtrim($siteList, ',');
    } else {
        $siteName = "'" . $site . "'";
    }

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate)";

    $sql1 = "SELECT count(distinct machine) AS totaldevice FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName IN( $siteName )";
    $totaldevice = find_one($sql1, $db);
    $sql2 = "SELECT count(timespoke) AS totalcalls, count(timespokeroaming) AS roaming"
        . " FROM TEMDetail WHERE siteName IN( $siteName ) $filterQuery";
    $totaltime = find_one($sql2, $db);

    $sql3 = "SELECT count(id) AS incoming, sum(timespoke) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND siteName IN( $siteName ) AND timespokeroaming = 0 $filterQuery";
    $totalin = find_one($sql3, $db);

    $sql4 = "SELECT count(id) AS outgoing, sum(timespoke) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND siteName IN( $siteName ) AND timespokeroaming = 0 $filterQuery";
    $totalout = find_one($sql4, $db);

    $sql5 = "SELECT count(id) AS incoming, sum(timespokeroaming) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND siteName IN( $siteName ) AND timespoke  = 0 $filterQuery";
    $totalRin = find_one($sql5, $db);

    $sql6 = "SELECT count(id) AS outgoing, sum(timespokeroaming) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND siteName IN( $siteName ) AND timespoke = 0 $filterQuery";
    $totalRout = find_one($sql6, $db);

    $totalRcalls = $totalRin['incoming'] + $totalRout['outgoing'];
    $totalcalls = $totalin['incoming'] + $totalout['outgoing'] + $totalRcalls;

    $totalRcallSec = $totalRin['totalinsec'] + $totalRout['totaloutsec'];
    $totalcallSec = $totalin['totalinsec'] + $totalout['totaloutsec'] + $totalRcallSec;

    $sql7 = "SELECT sum(mobileDataUsage) AS datausage FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName IN( $siteName ) $filterQuery";
    $datausage = find_one($sql7, $db);

    $sql8 = "SELECT DISTINCT machine AS machines,siteName As siteName FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName IN( $siteName )";
    $machines = find_many($sql8, $db);

    $json = '{"Title" : "' . $site . '",';
    $json .= '"Details" : {';
    $json .= '"TotalDevices" : "' . $totaldevice['totaldevice'] . '",';
    $json .= '"TotalCalls" : "' . $totalcalls . '",';
    $json .= '"TotalIn" : "' . $totalin['incoming'] . '",';
    $json .= '"TotalOut" : "' . $totalout['outgoing'] . '",';
    $json .= '"TotalRIn" : "' . $totalRin['incoming'] . '",';
    $json .= '"TotalROut" : "' . $totalRout['outgoing'] . '",';
    $json .= '"TotalRCalls" : "' . $totalRcalls . '",';
    $json .= '"TotalCallSec" : "' . $totalcallSec . '",';
    $json .= '"TotalInSec" : "' . $totalin['totalinsec'] . '",';
    $json .= '"TotalOutSec" : "' . $totalout['totaloutsec'] . '",';
    $json .= '"TotalRInSec" : "' . $totalRin['totalinsec'] . '",';
    $json .= '"TotalROutSec" : "' . $totalRout['totaloutsec'] . '",';
    $json .= '"DataUsage" : "' . $datausage['datausage'] . '"},';

    $json .= '"MachineDetails" : [';

    foreach ($machines as $key => $value) {
        $machinename = $value['machines'];
        $site = $value['siteName'];

        $sql9 = "SELECT count(id) AS incoming, sum(timespoke) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND machine = '$machinename' AND siteName = '$site' AND timespokeroaming = 0 $filterQuery";
        $sqlres = find_one($sql9, $db);
        $devicein = $sqlres['incoming'];
        $deviceinsec = $sqlres['totalinsec'];

        $sql10 = "SELECT count(id) AS outgoing, sum(timespoke) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND machine = '$machinename' AND siteName = '$site' AND timespokeroaming = 0 $filterQuery";
        $sqlres = find_one($sql10, $db);
        $deviceout = $sqlres['outgoing'];
        $deviceoutsec = $sqlres['totaloutsec'];

        $sql11 = "SELECT count(id) AS incoming, sum(timespokeroaming) AS totalinsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'IN' AND machine = '$machinename' AND siteName = '$site' AND timespoke = 0 $filterQuery";
        $sqlres = find_one($sql11, $db);
        $deviceRin = $sqlres['incoming'];
        $deviceRinsec = $sqlres['totalinsec'];

        $sql12 = "SELECT count(id) AS outgoing, sum(timespokeroaming) AS totaloutsec FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE typeOfCall = 'OUT' AND machine = '$machinename' AND siteName = '$site' AND timespoke = 0 $filterQuery";
        $sqlres = find_one($sql12, $db);
        $deviceRout = $sqlres['outgoing'];
        $deviceRoutsec = $sqlres['totaloutsec'];

        $deviceRtotal = $deviceRin + $deviceRout;
        $devicetotal = $devicein + $deviceout + $deviceRtotal;

        $deviceRtotalsec = $deviceRinsec + $deviceRoutsec;
        $devicetotalsec = $deviceinsec + $deviceoutsec + $deviceRtotalsec;

        $sql13 = "SELECT count(timespoke) AS totalcalls, count(timespokeroaming) AS roaming, sum(timespoke) AS totalsec, sum(timespokeroaming) AS roamingsec"
            . " FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName = '$site' AND machine = '$machinename' $filterQuery";

        $machineres = find_one($sql13, $db);

        $sql14 = "SELECT sum(mobileDataUsage) AS datausage FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='$machinename' $filterQuery";
        $machinedataUsage = find_one($sql14, $db);

        $json .= '{';
        $json .= '"MachineName" : "' . $machinename . '",';
        $json .= '"TotalCalls" : "' . $devicetotal . '",';
        $json .= '"TotalIN" : "' . $devicein . '",';
        $json .= '"TotalOut" : "' . $deviceout . '",';
        $json .= '"TotalRoaming" : "' . $deviceRtotal . '",';
        $json .= '"TotalSeconds" : "' . $devicetotalsec . '",';
        $json .= '"TotalINSec" : "' . $deviceinsec . '",';
        $json .= '"TotalOutSec" : "' . $deviceoutsec . '",';
        $json .= '"TotalRSec" : "' . $deviceRtotalsec . '",';
        $json .= '"DataUsage" : "' . $machinedataUsage['datausage'] . '"';
        $json .= '},';
    }
    $json = rtrim($json, ",");

    $json .= ']';
    $json .= '}';

    return $json;
}

function machineDataAnalytics($site, $machine, $fromDate, $toDate, $db)
{

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate)";

    $dSql1 = "SELECT COUNT(DISTINCT machine) AS totaldevice FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machine' ";
    $totalDevice = find_one($dSql1, $db);

    $dSql2 = "SELECT COUNT(DISTINCT exename) AS totalAppsInst, sum(timespentonapp) AS totalTimeSpent FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machine' $filterQuery ";
    $totalAppInst = find_one($dSql2, $db);

    $dSql3 = "SELECT DISTINCT machine AS machines FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machine'";
    $machines = find_many($dSql3, $db);

    $dSql4 = "select DISTINCT exename AS appNames from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail where siteName = '$site' AND machine='$machine' $filterQuery";
    $appNames = find_many($dSql4, $db);

    $dJson = '{"Title" : "' . $machine . '",';
    $dJson .= '"Details" : {';
    $dJson .= '"TotalDevices" : "' . $totalDevice['totaldevice'] . '",';
    $dJson .= '"TotalAppsInst" : "' . $totalAppInst['totalAppsInst'] . '",';
    $dJson .= '"TotalTimeSpent" : "' . gmdate("H:i:s", $totalAppInst['totalTimeSpent']) . '",';
    $dJson .= '"AppInfo" : {';
    foreach ($appNames as $key => $value) {
        $appName = $value['appNames'];
        $dSql5 = "SELECT sum(timespentonapp) AS timeSpentPerApp FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machine' AND exename = '$appName' $filterQuery ";
        $siteCountRes = find_one($dSql5, $db);
        $totSiteLevel = $siteCountRes['timeSpentPerApp'];
        $dJson .= '"' . $appName . '" : "' . $totSiteLevel . '",';
    }
    $dJson = rtrim($dJson, ",");
    $dJson .= '},';

    $dJson .= '"AppUsage" : {';
    foreach ($appNames as $key => $value) {
        $appName = $value['appNames'];
        $dSql5 = "SELECT count(id) AS AppViewCount FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machine' AND exename = '$appName' $filterQuery";
        $siteCountRes = find_one($dSql5, $db);
        $appViewed = $siteCountRes['AppViewCount'];
        $dJson .= '"' . $appName . '" : "' . $appViewed . '",';
    }
    $dJson = rtrim($dJson, ",");
    $dJson .= '},';

    $dJson = rtrim($dJson, ",");
    $dJson .= '},';

    $dJson .= '"MachineDetails" : [';

    foreach ($machines as $key => $value) {
        $machinename = $value['machines'];

        $dSql6 = "SELECT COUNT(DISTINCT exename) AS totalAppsInst, sum(timespentonapp) AS totalTimeSpent FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName='$site' AND machine='$machinename' $filterQuery ";
        $totalAppInst = find_one($dSql6, $db);
        $appInstRes = $totalAppInst['totalAppsInst'];
        $timeSpentRes = $totalAppInst['totalTimeSpent'];

        $dSql7 = "select DISTINCT exename AS appNames from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail where siteName = '$site' AND machine='$machinename'";
        $appNames = find_many($dSql7, $db);

        $dJson .= '{';
        $dJson .= '"MachineName" : "' . $machinename . '",';
        $dJson .= '"TotalAppsInst" : "' . $appInstRes . '",';
        $dJson .= '"TotalTimeSpent" : "' . gmdate("H:i:s", $timeSpentRes) . '",';
        $dJson .= '"AppInfo" : {';
        foreach ($appNames as $key => $value) {
            $appName = $value['appNames'];
            $dSql8 = "SELECT exename, sum(timespentonapp) AS timeSpentPerApp, count(id) AS AppViewCount FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machinename' AND exename = '$appName' $filterQuery ";
            $appRes = find_one($dSql8, $db);
            $appNameR = $appRes['exename'];
            $appViewed = $appRes['AppViewCount'];
            $dJson .= '"' . $appNameR . '" : "' . $appViewed . '",';
        }
        $dJson = rtrim($dJson, ",");
        $dJson .= '},';
        $dJson = rtrim($dJson, ",");
        $dJson .= '},';
    }
    $dJson = rtrim($dJson, ",");

    $dJson .= ']';
    $dJson .= '}';

    return $dJson;
}

function siteDataAnalytics($site, $machine, $fromDate, $toDate, $db)
{

    if (is_array($site)) {
        foreach ($site as $row => $val) {
            $siteList .= "'" . $val . "',";
        }
        $siteName = rtrim($siteList, ',');
    } else {
        $siteName = "'" . $site . "'";
    }

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate)";

    $dSql1 = "SELECT COUNT(DISTINCT machine) AS totaldevice FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName IN($siteName) ";
    $totalDevice = find_one($dSql1, $db);

    $dSql2 = "SELECT COUNT(DISTINCT exename) AS totalAppsInst, sum(timespentonapp) AS totalTimeSpent FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName IN($siteName) $filterQuery";
    $totalAppInst = find_one($dSql2, $db);

    $dSql3 = "SELECT DISTINCT machine AS machines,siteName AS siteName FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName IN($siteName) $filterQuery";
    $machines = find_many($dSql3, $db);

    $dSql4 = "select DISTINCT exename AS appNames,siteName as siteName from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail where siteName IN($siteName) $filterQuery";
    $appNames = find_many($dSql4, $db);

    $dJson = '{"Title" : "' . $site . '",';
    $dJson .= '"Details" : {';
    $dJson .= '"TotalDevices" : "' . $totalDevice['totaldevice'] . '",';
    $dJson .= '"TotalAppsInst" : "' . $totalAppInst['totalAppsInst'] . '",';
    $dJson .= '"TotalTimeSpent" : "' . gmdate("H:i:s", $totalAppInst['totalTimeSpent']) . '",';
    $dJson .= '"AppInfo" : {';
    foreach ($appNames as $key => $value) {
        $appName = $value['appNames'];
        $site    = $value['siteName'];
        $dSql5 = "SELECT sum(timespentonapp) AS timeSpentPerApp FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND exename = '$appName' $filterQuery";
        $siteCountRes = find_one($dSql5, $db);
        $totSiteLevel = $siteCountRes['timeSpentPerApp'];
        $dJson .= '"' . $appName . '" : "' . $totSiteLevel . '",';
    }
    $dJson = rtrim($dJson, ",");
    $dJson .= '},';

    $dJson .= '"AppUsage" : {';
    foreach ($appNames as $key => $value) {
        $appName = $value['appNames'];
        $site    = $value['siteName'];
        $dSql5 = "SELECT count(id) AS AppViewCount FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND exename = '$appName' $filterQuery";
        $siteCountRes = find_one($dSql5, $db);
        $appViewed = $siteCountRes['AppViewCount'];
        $dJson .= '"' . $appName . '" : "' . $appViewed . '",';
    }
    $dJson = rtrim($dJson, ",");
    $dJson .= '},';

    $dJson = rtrim($dJson, ",");
    $dJson .= '},';
    $dJson .= '"MachineDetails" : [';

    foreach ($machines as $key => $value) {
        $machinename = $value['machines'];
        $site        = $value['siteName'];

        $dSql6 = "SELECT COUNT(DISTINCT exename) AS totalAppsInst, sum(timespentonapp) AS totalTimeSpent FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName='$site' AND machine='$machinename' $filterQuery";
        $totalAppInst = find_one($dSql6, $db);
        $appInstRes = $totalAppInst['totalAppsInst'];
        $timeSpentRes = $totalAppInst['totalTimeSpent'];

        $dSql7 = "select DISTINCT exename AS appNames,siteName AS siteName from " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail where siteName = '$site' AND machine='$machinename'";
        $appNames = find_many($dSql7, $db);

        $dJson .= '{';
        $dJson .= '"MachineName" : "' . $machinename . '",';
        $dJson .= '"TotalAppsInst" : "' . $appInstRes . '",';
        $dJson .= '"TotalTimeSpent" : "' . gmdate("H:i:s", $timeSpentRes) . '",';
        $dJson .= '"AppInfo" : {';
        foreach ($appNames as $key => $value) {
            $appName = $value['appNames'];
            $site    = $value['siteName'];
            $dSql8 = "SELECT exename, sum(timespentonapp) AS timeSpentPerApp, count(id) AS AppViewCount FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName = '$site' AND machine='$machinename' AND exename = '$appName' $filterQuery ";
            $appRes = find_one($dSql8, $db);

            $appNameR = $appRes['exename'];
            $appViewed = $appRes['AppViewCount'];
            $appSpent  = $appRes['timeSpentPerApp'];
            $dJson .= '"' . $appNameR . '" : "' . $appViewed . '",';
        }
        $dJson = rtrim($dJson, ",");
        $dJson .= '},';
        $dJson = rtrim($dJson, ",");
        $dJson .= '},';
    }
    $dJson = rtrim($dJson, ",");

    $dJson .= ']';
    $dJson .= '}';
    return $dJson;
}

function machineNetworkUsage($site, $machine, $fromDate, $toDate, $db)
{

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate)";

    $netsql1 = "SELECT sum(mobileDataUsage) AS datausage, sum(wifiDataUsage) AS wifiUsage, count(distinct machine) AS totaldevice FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='machine' $filterQuery";
    $sitenetusage = find_one($netsql1, $db);

    $netsql2 = "SELECT DISTINCT machine AS machines FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='$machine'";
    $machines = find_many($netsql2, $db);

    $machineJson = '{"Title" : "' . machine . '",';
    $machineJson .= '"Details" : {';
    $machineJson .= '"TotalDevices" : "' . $sitenetusage['totaldevice'] . '",';
    $machineJson .= '"WifiUsage" : "' . $sitenetusage['wifiUsage'] . '",';
    $machineJson .= '"DataUsage" : "' . $sitenetusage['datausage'] . '"},';

    $machineJson .= '"MachineDetails" : [';
    foreach ($machines as $key => $value) {
        $machinename = $value['machines'];

        $sql3 = "SELECT sum(mobileDataUsage) AS datausage, sum(wifiDataUsage) AS wifiUsage FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='$machinename' $filterQuery";
        $machinenetUsage = find_one($sql3, $db);

        $machineJson .= '{';
        $machineJson .= '"MachineName" : "' . $machinename . '",';
        $machineJson .= '"WifiUsage" : "' . $machinenetUsage['wifiUsage'] . '",';
        $machineJson .= '"DataUsage" : "' . $machinenetUsage['datausage'] . '"';
        $machineJson .= '},';
    }
    $machineJson = rtrim($machineJson, ",");

    $machineJson .= ']';
    $machineJson .= '}';

    return $machineJson;
}

function siteNetworkusage($site, $machine, $fromDate, $toDate, $db)
{

    if (is_array($site)) {
        foreach ($site as $row => $val) {
            $siteList .= "'" . $val . "',";
        }
        $siteName = rtrim($siteList, ',');
    } else {
        $siteName = "'" . $site . "'";
    }

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate)";

    $netsql1 = "SELECT sum(mobileDataUsage) AS datausage, sum(wifiDataUsage) AS wifiUsage, count(distinct machine) AS totaldevice FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName IN($siteName) $filterQuery";
    $sitenetusage = find_one($netsql1, $db);

    $netsql2 = "SELECT DISTINCT machine AS machines,siteName AS siteName FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName IN($siteName) ";
    $machines = find_many($netsql2, $db);

    $siteJson = '{"Title" : "' . $site . '",';
    $siteJson .= '"Details" : {';
    $siteJson .= '"TotalDevices" : "' . $sitenetusage['totaldevice'] . '",';
    $siteJson .= '"WifiUsage" : "' . $sitenetusage['wifiUsage'] . '",';
    $siteJson .= '"DataUsage" : "' . $sitenetusage['datausage'] . '"},';

    $siteJson .= '"MachineDetails" : [';
    foreach ($machines as $key => $value) {
        $machinename = $value['machines'];
        $site        = $value['siteName'];

        $sql3 = "SELECT sum(mobileDataUsage) AS datausage, sum(wifiDataUsage) AS wifiUsage FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName = '$site' AND machine='$machinename' $filterQuery";
        $machinenetUsage = find_one($sql3, $db);

        $siteJson .= '{';
        $siteJson .= '"MachineName" : "' . $machinename . '",';
        $siteJson .= '"WifiUsage" : "' . $machinenetUsage['wifiUsage'] . '",';
        $siteJson .= '"DataUsage" : "' . $machinenetUsage['datausage'] . '"';
        $siteJson .= '},';
    }
    $siteJson = rtrim($siteJson, ",");

    $siteJson .= ']';
    $siteJson .= '}';

    return $siteJson;
}

function getMapdetails($fromDate, $toDate, $machine, $db)
{

    $sql = "select DISTINCT host from " . $GLOBALS['PREFIX'] . "core.Census where host ='$machine'";

    $site = find_one($sql, $db);
    $machineName = $site['host'];

    $sql1 = "SELECT machine,latitude,longitude,clientTime FROM " . $GLOBALS['PREFIX'] . "mdm.LocationDetail " .
        " WHERE machine='" . $machineName . "' and clientTime >= $fromDate and clientTime <= $toDate";

    $location = find_many($sql1, $db);

    if (safe_count($location) > 0) {
        foreach ($location as $value) {
            $lat = $value['latitude'];
            $lng = $value['longitude'];
            $machinedet[] = $lat . '|' . $lng;
        }
    }

    return $machinedet;
}

function rowReport($site, $machine, $fromDate, $toDate, $reportType, $rowToDisplay, $db)
{

    $filterQuery = "AND (clientTime BETWEEN $fromDate AND $toDate)";

    if (is_array($site)) {
        foreach ($site as $row => $val) {
            $siteList .= "'" . $val . "',";
        }
        $siteName = rtrim($siteList, ',');
    } else {
        $siteName = "'" . $site . "'";
    }

    $rowToDisplay = str_replace('clientTime', 'from_unixtime(clientTime,"%m/%d/%y %h:%i") as clientTime', $rowToDisplay);

    if ($reportType == 'tem') {
        if ($machine == '') {
            $sql = "SELECT $rowToDisplay FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName IN ($siteName) AND " .
                "timespoke != 0 $filterQuery order by typeOfCall desc ";
        } else {
            $sql = "SELECT $rowToDisplay FROM " . $GLOBALS['PREFIX'] . "mdm.TEMDetail WHERE siteName IN ($siteName) AND " .
                "machine = '$machine' AND timespoke != 0 $filterQuery order by typeOfCall desc";
        }
    } elseif ($reportType == 'dataAnaly') {
        if ($machine == '') {
            $sql =  "SELECT $rowToDisplay FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName IN ($siteName) " .
                " $filterQuery ";
        } else {
            $sql = "SELECT $rowToDisplay FROM " . $GLOBALS['PREFIX'] . "mdm.DataAnalysisDetail WHERE siteName IN ($siteName) AND " .
                " machine = '$machine' $filterQuery ";
        }
    } elseif ($reportType == 'netUsage') {
        if ($machine == '') {
            $sql = "SELECT $rowToDisplay FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName IN ($siteName) " .
                " $filterQuery ";
        } else {
            $sql = "SELECT $rowToDisplay FROM " . $GLOBALS['PREFIX'] . "mdm.NetworkUsageDetail WHERE siteName IN ($siteName) AND " .
                "machine = '$machine' $filterQuery ";
        }
    }
    $data = [];
    $result = find_many($sql, $db);
    foreach ($result as $key => $val) {
        $data[] = $val;
    }
    return $result;
}
