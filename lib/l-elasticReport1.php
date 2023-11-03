<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-db.php';
include_once 'l-sql.php';
include_once 'l-gsql.php';
include_once 'l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';

if (url::issetInRequest('functionToCall')) {
    funcCallViaAjax();
}

function funcCallViaAjax()
{
    $function = url::requestToAny('functionToCall');
    $function(TRUE);
}

function getMumData()
{
    $reptid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $reportData = getReportData($reptid, $db);

    $jsonData = report_cron($reptid, $reportData, $_SESSION['user']['userid'], $db);

    echo json_encode($jsonData);
}

function getAssetData()
{
    $reptid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $reportData = getReportData($reptid, $db);

    $jsonData = report_cron($reptid, $reportData, '10', $db);
    $jsonGraphData = getAssetGraphData($jsonData);
    $jsonData['graph'] = $jsonGraphData;
    echo json_encode($jsonData);
}


function getAssetGraphData($jsonData)
{
    $groupedData = $jsonData[0]['groupedData'];
    $tempArray = [];
    foreach ($groupedData as $key => $value) {
        $tempArray[$value['name']] = intval($value['count']);
    }
    return $tempArray;
}

function getReportData($id, $db)
{

    $sql = "SELECT name,global,include,username,created,infportal,emaillist,userid,type FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id =$id ";

    $report = find_one($sql, $db);
    $reportData['name'] = $report['name'];
    $reportData['global'] = $report['global'];
    $reportData['include'] = $report['include'];
    $reportData['username'] = $report['username'];
    $reportData['created'] = $report['created'];
    $reportData['infPortal'] = $report['infportal'];
    $reportData['emailList'] = $report['emaillist'];
    $reportData['userid'] = $report['userid'];
    $reportData['reportType'] = $report['type'];
    $sqlShed = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.Schedule WHERE reportid = $id";
    $schedData = find_one($sqlShed, $db);
    $reportData['schedData'] = array($schedData['schedtype'], $schedData['mnthday'], $schedData['weekday'], $schedData['hour'], $schedData['min']);
    $sqlSec = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedReportMap mr, ManagedSection ms WHERE reportid = $id and mr.sectionid = ms.id and ms.sectiontype = 2";
    $section = find_many($sqlSec, $db);

    foreach ($section as $value) {
        $secSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection WHERE id =" . $value['sectionid'];
        $res = find_one($secSql, $db);

        $sectionData = [];
        if (empty($res)) {
            $sectionData['sectionName'] = '';
            $sectionData['subHeaders'] = '';
            $sectionData['chartType'] = '';
            $sectionData['secType'] = '';
        } else {
            $sectionData['sectionName'] = $res['name'];
            $sectionData['subHeaders'] = $res['subheaders'];
            $sectionData['chartType'] = $res['charttype'];
            $sectionData['secType'] = $res['sectiontype'];
            $sectionData['secId'] = $res['id'];
            $eid = $_SESSION['user']['cId'];
        }
        $sqlSubSec = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE sectionid =" . $value['sectionid'];
        $subSec = find_many($sqlSubSec, $db);
        foreach ($subSec as $value1) {
            $sectionData['subSectionData'][] = array($value1['name'], $value1['filtertype'], $value1['filterid'], $value1['groupVal'], $value1['reportduration'], $value1['updatetype'], $value1['updatesize'], $value1['mnth'], $value1['year'], $value1['ostype']);
        }
        $reportData['sectionData'][] = $sectionData;
    }

    return $reportData;
}

function get_size_inbytes($updateSize)
{
    switch ($updateSize) {
        case '2':
            $sizeComp = " size > 0 and size <= 51200 ";
            break;
        case '3':
            $sizeComp = " size > 51200 and size <= 1048576 ";
            break;
        case '4':
            $sizeComp = " size > 1048576 and size <= 5242880 ";
            break;
        case '5':
            $sizeComp = " size > 5242880 ";
            break;
        default:
            $sizeComp = '';
            break;
    }
    return $sizeComp;
}

function get_other_crit($subSecData)
{
    $updateType = $subSecData[5];
    $updateSize = $subSecData[6];
    $mnth = $subSecData[7];
    $year = $subSecData[8];
    $osType = $subSecData[9];
    $where = '';
    if ($mnth == 0) {
        $where .= " date BETWEEN '" . strtotime("1 January $year") . "' AND '" . strtotime("31 December $year") . "'";
    } else {
        $to_date = date("Y-m-t", strtotime("1 $mnth $year"));
        $where .= " date BETWEEN '" . strtotime("1 $mnth $year") . "' AND '" . strtotime($to_date) . "'";
    }

    $where .= " and type in ($updateType) ";

    if ($updateSize != 0 && $updateSize != 1) {
        $sizeInBytes = get_size_inbytes($updateSize);
        $where .= " and $sizeInBytes";
    }

    return $where;
}

function prepare_sql_mum($subSecData)
{
    $patchId = $subSecData[2];
    $where .= get_other_crit($subSecData);
    $where .= " and patchid in ($patchId)";
    $sql = "select patchid,name,type from " . $GLOBALS['PREFIX'] . "softinst.Patches where $where";
    return $sql;
}

function fetch_mum_data($subSecData, $machines, $db)
{
    $summaryName = $subSecData[0];
    $summaryHdrs = $subSecData[3];
    db_change($GLOBALS['PREFIX'] . 'softinst', $db);
    $sql = prepare_sql_mum($subSecData);
    $res = find_many($sql, $db);
    foreach ($res as $key => $val) {
        $patchid .= $val['patchid'] . ",";
        $patchName[$val['patchid']] = $val['name'];
        switch ($val['type']) {
            case '1':
                $type = 'Update';
                break;
            case '2':
                $type = 'Service Pack';
                break;
            case '3':
                $type = 'Roll Up';
                break;
            case '4':
                $type = 'Security';
                break;
            case '5':
                $type = 'Critical';
                break;
            default:
                $type = 'Undefined';
                break;
        }
        $patchType[$val['patchid']] = $type;
    }
    $censusid = implode(",", $machines['censusid']);
    $sqlPatchStatus = "select C.host,FROM_UNIXTIME(P.detected,'%Y-%m-%d') as detected,P.patchid, "
        . "case"
        . " when  P.`status` = 8 then 'Installed'"
        . " when  P.`status` = 10 then 'Downloaded'"
        . " when  P.`status` = 11 then 'Detected'"
        . " when  P.`status` = 15 then 'Superseded'"
        . " when  P.`status` = 16 then 'Waiting'"
        . " else 'other'"
        . " end as status "
        . " from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as P "
        . "join " . $GLOBALS['PREFIX'] . "core.Census as C on C.id = P.id where P.patchid "
        . "in (" . rtrim($patchid, ",") . ") and C.id in ($censusid) "
        . "and status in (" . rtrim($summaryHdrs, ',') . ") order by status";
    $res = find_many($sqlPatchStatus, $db);
    $sqlPatchGrpdStatus = "select count(P.status) as cnt, "
        . " case"
        . " when  P.`status` = 8 then 'Installed'"
        . " when  P.`status` = 10 then 'Downloaded'"
        . " when  P.`status` = 11 then 'Detected'"
        . " when  P.`status` = 15 then 'Superseded'"
        . " when  P.`status` = 16 then 'Waiting'"
        . " else 'other'"
        . " end as status "
        . " from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as P "
        . "join " . $GLOBALS['PREFIX'] . "core.Census as C on C.id = P.id where P.patchid "
        . "in (" . rtrim($patchid, ",") . ") and C.id in ($censusid) "
        . "and status in (" . rtrim($summaryHdrs, ',') . ") group by P.status order by status";
    $groupedata = find_many($sqlPatchGrpdStatus, $db);
    $i = 0;
    foreach ($groupedata as $key => $val) {
        $res1[$i]['count'] = $val['cnt'];
        $res1[$i]['name'] = $val['status'];
        $i++;
    }
    $return['type'] = 'mum';
    $return['details'] = $res;
    $return['groupedData'] = $res1;
    $return['patchname'] = $patchName;
    $return['patchtype'] = $patchType;
    $return['name'] = $summaryName;
    return $return;
}

function process_sections($secName, $subSections, $groupby, $chartType, $machines, $db)
{

    $result = fetch_asset_data($subSections[0], TRUE, $machines, $db);

    return $result;
}

function prepare_sections($reportData, $machines, $machGrpList, $db)
{
    $i = 0;
    $data = [];
    end($reportData['sectionData']);
    $lastIndex = key($reportData['sectionData']);

    reset($reportData['sectionData']);

    foreach ($reportData['sectionData'] as $key => $value) {
        $resData[] = process_sections($value['sectionName'], $value['subSectionData'], $value['subHeaders'], $value['chartType'], $machines, $db);
    }

    return $resData;
}

function fetch_mach_grps($machGrpsNms, $userid, $db)
{
    if (stripos($machGrpsNms, 'All') !== FALSE) {
        $sql = "select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = $userid";
        $result = find_many($sql, $db);
        $names = [];
        foreach ($result as $val) {
            $names[] = "'" . $val['customer'] . "'";
        }
    } else {
        $names = explode(",", $machGrpsNms);
        foreach ($names as $value) {
            $list .= "'" . $value . "',";
        }
        $sql = "select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = $userid and C.customer in (" . rtrim($list, ",") . ")";
        $result = find_many($sql, $db);
        $names = [];
        foreach ($result as $val) {
            $names[] = "'" . $val['customer'] . "'";
        }
    }

    $machGrpsUniqs = '';

    $sql = "select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in (" . implode(",", $names) . ")";
    $res = find_many($sql, $db);

    foreach ($res as $key => $val) {
        $machGrpsUniqs .= "'" . $val['mgroupuniq'] . "',";
    }

    return rtrim($machGrpsUniqs, ",");
}

function fetch_machines_list($machGrps, $db)
{
    $return['mid'] = [];
    $return['host'] = [];
    $return['censusid'] = [];
    $host = '';
    $sql = "select distinct C.host,C.id,C.site from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M "
        . "join " . $GLOBALS['PREFIX'] . "core.Census as C on C.censusuniq = M.censusuniq "
        . "where M.mgroupuniq in  ($machGrps)";

    $res = find_many($sql, $db);

    foreach ($res as $key => $val) {
        $host .= "'" . $val['host'] . "',";
        $return['censusid'][] = $val['id'];
        $return[$val['host']] = $val['site'];
    }

    $sql = "select machineid,host from " . $GLOBALS['PREFIX'] . "asset.Machine where host in (" . rtrim($host, ",") . ")";
    $res = find_many($sql, $db);
    foreach ($res as $key => $val) {
        $return['mid'][] = $val['machineid'];
    }
    $return['host'][0] = rtrim($host, ",");
    return $return;
}

function report_cron($id, $reportData, $userid, $db)
{

    $machGrpsNms = 'All';

    $machGrpList = fetch_mach_grps($machGrpsNms, $userid, $db);

    if ($machGrpList != '') {

        $machines = fetch_machines_list($machGrpList, $db);


        $finalRes = prepare_sections($reportData, $machines, $machGrpList, $db);
    }
    return $finalRes;
}



function fetch_asset_data($subSecData, $single, $machines, $db)
{
    $filterName = $subSecData[0];
    $filterId   = $subSecData[2];
    $grpVal     = $subSecData[3];
    $cntArr = [];
    db_change($GLOBALS['PREFIX'] . 'asset', $db);

    $result = run_query($db, $filterId, $machines['mid'], 0, 0, 0);
    if ($single) {
        $res = [];
        foreach ($result['rows'] as $mid => $assets) {
            $did = $result['columns'][$grpVal];
            if (isset($assets[$did])) {
                foreach ($assets[$did] as $ord => $val) {
                    $cntArr[$val][] = 1;
                }
            }
        }
        $i = 0;
        foreach ($cntArr as $key => $value) {
            $res[$i]['count'] = safe_count($value);
            $res[$i]['name'] = utf8_encode($key);
            $i++;
        }
        $return['details'] = $result;
        $return['groupedData'] = $res;
        $return['type'] = 'asset';
        return $return;
    } else {
        $return['type'] = 'asset';
        $return['details'] = $result;
        $res['count'] = safe_count($result['rows']);
        $res['name'] = $filterName;
        $return['groupedData'] = $res;
        return $return;
    }
}
