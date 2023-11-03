<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-patch.php';
include_once '../lib/l-export.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-formatGrid.php';
require_once("../include/common_functions.php");
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';


nhRole::dieIfnoRoles(['patchmanagement']); //roles: patchmanagement

//Replace $routes['post'] with if else
if (url::postToText('function') === 'Declinepatch') { //roles: patchmanagement
    Declinepatch();
} else if (url::postToText('function') === 'getdefaultconfiguration') { //roles: patchmanagement
    getdefaultconfiguration();
} else if (url::postToText('function') === 'getallpatchData') { //roles: patchmanagement
    getallpatchData();
} else if (url::postToText('function') === 'mumgetscheduleset') { //roles: patchmanagement
    mumgetscheduleset();
} else if (url::postToText('function') === 'getretrypatchData') { //roles: patchmanagement
    getretrypatchData();
} else if (url::postToText('function') === 'Approve_patch') { //roles: patchmanagement
    Approve_patch();
} else if (url::postToText('function') === 'mumupdatemethod') { //roles: patchmanagement
    mumupdatemethod();
} else if (url::postToText('function') === 'getapprovepatchData') { //roles: patchmanagement
    getapprovepatchData();
} else if (url::postToText('function') === 'getCriticalUpdatepatchData') { //roles: patchmanagement
    getCriticalUpdatepatchData();
} else if (url::postToText('function') === 'getremovepatchData') { //roles: patchmanagement
    getremovepatchData();
} else if (url::postToText('function') === 'Remove_patch') { //roles: patchmanagement
    Remove_patch();
} else if (url::postToText('function') === 'update_MachConfig') { //roles: patchmanagement
    update_MachConfig();
} else if (url::postToText('function') === 'getapprdefaultconfiguration') { //roles: patchmanagement
    getapprdefaultconfiguration();
} else if (url::postToText('function') === 'Kbs_patchdetailList') { //roles: patchmanagement
    Kbs_patchdetailList();
} else if (url::postToText('function') === 'get_patchStatusNewUIFunc') { //roles: patchmanagement
    get_patchStatusNewUIFunc();
} else if (url::postToText('function') === 'configureMUM') { //roles: patchmanagement
    configureMUM();
} else if (url::postToText('function') === 'getConfigUpdateDetails') { //roles: patchmanagement
    getConfigUpdateDetails();
}


//Replace $routes['get'] with if else
if (url::getToText('function') === 'MUMGetPatchStatusExport') { //roles: patchmanagement
    MUMGetPatchStatusExport();
} else if (url::getToText('function') === 'PatchExportDetails') { //roles: patchmanagement
    PatchExportDetails();
} else if (url::postToText('function') === 'getDateSelection') { //roles: patchmanagement
    getDateSelection();
} else if (url::postToText('function') === 'getHourSelection') { //roles: patchmanagement
    getHourSelection();
} else if (url::postToText('function') === 'getAllSitesDetails') { //roles: patchmanagement
    getAllSitesDetails();
} else if (url::postToText('function') === 'GetConfigDetails') { //roles: patchmanagement
    GetConfigDetails();
} else if (url::postToText('function') === 'mumgetfilterData') { //roles: patchmanagement
    mumgetfilterData();
}

// global $db;
// $db = pdo_connect();


function getdefaultconfiguration()
{
    $wintype = url::requestToAny('wintype');
    $db = pdo_connect();
    $rparentName = $_SESSION['rparentName'];
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }

    if ($searchType == 'Sites') {
        if ($searchValue == 'All') {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $res = safe_array_keys($dataScope);
            foreach ($res as $row) {
                $siteList .= "'" . $dataScope[$row] . "',";
                $grouplist .= "'" . $row . "',";
            }
            $lableDisplyArr = array();
            $lableDisply = rtrim($siteList, ',');
            $lableDisplyArr = explode(',', $lableDisply);
            $in1 = str_repeat('?,', safe_count($lableDisplyArr) - 1) . '?';

            $labelgroup = rtrim($grouplist, ',');
            $sqlres = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
            $sqlres->execute($lableDisplyArr);
        } else {
            $searchValueArr = array();
            $searchValueArr = explode(',', $searchValue);
            $in1 = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
            $sqlres = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
            $sqlres->execute($searchValueArr);
        }
        $result = $sqlres->fetchAll();
    } else if ($searchType == 'ServiceTag') {
        $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
        $sql->execute([$rparentName . ':' . $searchValue]);
        $result = $sql->fetchAll();
    } else if ($searchType == 'Groups') {
        if ($searchValue == 'All') {
            $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $res = safe_array_keys($dataScope);
            foreach ($res as $row) {
                $siteList .= "'" . $dataScope[$row] . "',";
                $grouplist .= "'" . $row . "',";
            }
            $labelgroupArr = array();
            $lableDisply = rtrim($siteList, ',');
            $labelgroup = rtrim($grouplist, ',');
            $labelgroupArr = explode(',', $labelgroup);
            $in1 = str_repeat('?,', safe_count($labelgroupArr) - 1) . '?';

            $sqlres = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
            $sqlres->execute($searchValueArr);
        } else {
            $searchValueArr = array();
            $searchValueArr = explode(',', $searchValue);
            $in1 = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
            $sqlres = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
            $sqlres->execute($searchValueArr);
        }
        $result = $sqlres->fetchAll();
    }
    $mgrpidArr = array();
    if (safe_count($result) > 0) {
        $mroupid = "";
        foreach ($result as $value) {
            array_push($mgrpidArr, $value['mgroupid']);
        }
        $in1 = str_repeat('?,', safe_count($mgrpidArr) - 1) . '?';
    } else {
        $mgrpid = "''";
    }
    $configData = array();
    $approvepgupid = PATCH_ApprovePatchId('', $lableDiaplay, $searchType, $db);

    $sql = $db->prepare("select * from  " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = 'All'");
    $sql->execute();
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    $allPatchId = $res['pgroupid'];
    $retrygupid = PATCH_RetryPatchId('', $lableDiaplay, $searchType, $db);
    if ($wintype == 'retry') {
        $params = array_merge($mgrpidArr, [$retrygupid]);
        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid in ($in1) and pgroupid = ? ORDER BY lastupdate DESC LIMIT 1");
        $sql->execute($params);
    } else if ($wintype == 'normal') {
        $params = array_merge($mgrpidArr, [$allPatchId]);
        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid in ($in1) and pgroupid = ?  ORDER BY lastupdate DESC LIMIT 1");
        $sql->execute($params);
    } else {
        $params = array_merge($mgrpidArr, [$approvepgupid]);
        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid in ($in1) and pgroupid = ? ORDER BY lastupdate DESC LIMIT 1");
        $sql->execute($params);
    }
    $row = $sql->fetch(PDO::FETCH_ASSOC);
    $count = safe_count($row);
    if (($count != 0) || ($count > 0)) {
        $configData['pconfigid'] = $row['pconfigid'];
        $configData['pgroupid'] = $row['pgroupid'];
        $configData['mgroupid'] = $row['mgroupid'];
        $configData['installation'] = $row['installation'];
        $configData['notifyadvance'] = $row['notifyadvance'];
        $configData['notifyadvancetime'] = $row['notifyadvancetime'];
        $configData['scheddelay'] = $row['scheddelay'];
        $configData['schedminute'] = $row['schedminute'];
        $configData['schedhour'] = $row['schedhour'];
        $configData['schedday'] = $row['schedday'];
        $configData['schedmonth'] = $row['schedmonth'];
        $configData['schedweek'] = $row['schedweek'];
        $configData['schedrandom'] = $row['schedrandom'];
        $configData['schedtype'] = $row['schedtype'];
        $configData['notifydelay'] = $row['notifydelay'];
        $configData['notifyminute'] = $row['notifyminute'];
        $configData['notifyhour'] = $row['notifyhour'];
        $configData['notifyday'] = $row['notifyday'];
        $configData['notifymonth'] = $row['notifymonth'];
        $configData['notifyweek'] = $row['notifyweek'];
        $configData['notifyrandom'] = $row['notifyrandom'];
        $configData['notifytype'] = $row['notifytype'];
        $configData['notifyfail'] = $row['notifyfail'];
        $configData['notifytext'] = $row['notifytext'];
        $configData['configtype'] = $row['configtype'];
        $configData['notifyrandom'] = $row['notifyrandom'];
        $configData['apprid'] = $approvepgupid;
        $configData['retryid'] = $retrygupid;
        $configData['data'] = 'data';
    } else {
        $configData['data'] = 'nodata';
    }

    $configDatavals = json_encode($configData, true);
    echo $configDatavals;
}

function getallpatchData($windowType = '')
{
    $db = pdo_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $rparentValue = $_SESSION['rparentName'];
    $key = '';
    $dartVal = '';

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '' && $orderVal !== 'count') {
        if ($orderVal == 'p.size') {
            $orderStr = 'order by round(p.size/1048576,4)' . $sortVal;
        } else {
            $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
        }
    } else {
        $orderStr = 'order by p.date desc';
    }

    if ($searchType == 'ServiceTag') {
        $sname = $rparentValue;
    } else {
        $sname = $searchValue;
    }

    if ($searchType == 'ServiceTag') {
        //        $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like '%$sname%'");
        //        $sql->execute();
        //        $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
        $sqlRes = NanoDB::find_one("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like '%$sname%'", null, []);
        logs::log("PDO::Error", ["Select_mgroupuniq" => $sqlRes]);
    } else {
        $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$sname]);
        $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    }


    $mgroupidParent = $sqlRes['mgroupuniq'];

    $sqlres2 = $db->prepare("select varuniq from " . $GLOBALS['PREFIX'] . "core.Variables where scop = 237 and name = 'Scrip237ScripRunEnableA'");
    $sqlres2->execute();
    $result2 = $sqlres2->fetch(PDO::FETCH_ASSOC);
    $varuniq = $result2['varuniq'];

    $sqlres = $db->prepare("select valu from " . $GLOBALS['PREFIX'] . "core.VarValues where varuniq = ? and mgroupuniq = ?");
    $sqlres->execute([$varuniq, $mgroupidParent]);
    $result = $sqlres->fetch(PDO::FETCH_ASSOC);
    $enableValue = $result['valu'];

    if ($enableValue == 1) {
        $dartVal = "enabled";
    } else {
        $dartVal = "disabled";
    }

    if ($dartVal == 'disabled') {
        $dataArr['largeDataPaginationHtml'] = '';
        $dataArr['html'] =    '';
        $dataArr['status'] = 'disabled';
        echo json_encode($dataArr);
    } else {
        $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
        $curPage = url::postToInt('nextPage') - 1;
        $limitStart = $limitCount * $curPage;
        $limitEnd = $limitStart + $limitCount;
        $notifSearch = url::postToText('notifSearch');

        if ($searchType == 'ServiceTag') {
            $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        } else {
            $lableDiaplay = $_SESSION['searchValue'];
        }
        if ($windowType == 'patch_export') {
            $limitStr = '';
        } else {
            if ($limitStart > 0) {
                $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
            } else {
                $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
            }
            // $limitStr = " LIMIT 10 OFFSET ".$limitStart;
        }
        $res = array();
        switch ($searchType) {
            case 'Sites':
                $mumpatchlist = PATCH_GetPatchSitesList($db, $searchValue, $limitStr, $notifSearch, $orderStr);
                $sql = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where site=?");
                $sql->execute([$searchValue]);
                $sqlres = $sql->fetchAll(PDO::FETCH_ASSOC);
                foreach ($sqlres as $k => $v) {
                    $res[] = $v['id'];
                }
                $machineId = implode(',', $res);
                break;
            case 'Groups':
                $machines = DASH_GetGroupsMachines($key, $db, $searchValue);
                $mumpatchlist = PATCH_GetPatchGroupList($db, $machines, $limitStr, $notifSearch, $orderStr);
                $machineId = getCensusId_grp($searchValue, $db);
                break;
            case 'ServiceTag':
                if ($_SESSION['rcensusId'] == "") {
                    $censusId = getCensusId($_SESSION["rparentName"], $_SESSION['searchValue'], $db);
                } else {
                    $censusId = $_SESSION['rcensusId'];
                }
                $mumpatchlist = PATCH_GetPatchMachineList($db, $censusId, $limitStr, $notifSearch, $orderStr);
                logs::log("PDO::Error", ["GET_PATCHES" => 'ServiceTag', '$censusId' => $censusId, "patches" => $mumpatchlist]);
                $machineId = $censusId;
                break;
            default:
                break;
        }
        $result = safe_json_decode($mumpatchlist, true);
        $totCount = $result['count'];
        $data = $result['data'];

        $approvepgupid = PATCH_ApprovePatchId('', $lableDiaplay, $searchType, $db);
        $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
        $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
        $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
        $retrypatch = PATCH_GetRetrypatch('', $lableDiaplay, $searchType, $db);
        $totalRecords = safe_count($result);
        if (safe_sizeof($data) == 0) {
            $dataArr['largeDataPaginationHtml'] =  '';
            $dataArr['html'] =   '';
            $dataArr['status'] = 'enabled';
            if ($windowType == 'patch_export') {
                return $dataArr['html'];
            } else {
                echo json_encode($dataArr);
            }
        } else {
            $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
            $dataArr['html'] = Format_mumDataMysql($db, $data, $approvepgupid, $approvepatch, $declinepatch, $removepatch, $retrypatch, $windowType);
            $dataArr['status'] = 'enabled';
            if ($windowType == 'patch_export') {
                return $dataArr['html'];
            } else {
                echo json_encode($dataArr);
            }
        }
    }
}

function Format_mumDataMysql($db, $result, $approvepgupid, $approvepatch, $declinepatch, $removepatch, $retrypatch, $windowType)
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    if ($searchType == 'Sites') {
        $patch_group_name = "All";
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute([$patch_group_name]);
        $queryp = $sqlpgrpid->fetch();
        $pgroupId = $queryp['pgroupid'];


        $sqlmgrpid = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name=? LIMIT 1");
        $sqlmgrpid->execute([$searchValue]);
        $resmgrpid = $sqlmgrpid->fetch();
        $mgroupid = $resmgrpid['mgroupid'];
    }
    if ($searchType == 'ServiceTag') {
        $patch_group_name = $_SESSION["rparentName"] . ":" . $searchValue;
        $sqlmgrpid = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name=? LIMIT 1");
        $sqlmgrpid->execute([$patch_group_name]);
        $resmgrpid = $sqlmgrpid->fetch();
        $mgroupid = $resmgrpid['mgroupid'];
    }


    foreach ($result as $key => $row) {

        //        if (in_array($row['patchid'], $approvepatch)) {
        //            $patch_id = $row['patchid'];
        //            $astatus = 'Approved';
        //            $checkBox = '';
        //        } else if (in_array($row['patchid'], $declinepatch)) {
        //            $patch_id = $row['patchid'];
        //            $astatus = 'Declined';
        //            $checkBox = '<span style="margin-left: 10px;">Declined</span>';
        //        } else if (in_array($row['patchid'], $removepatch)) {
        //            $patch_id = $row['patchid'];
        //            $astatus = 'Removed';
        //            $checkBox = '';
        //        } else if (in_array($row['patchid'], $retrypatch)) {
        //            $patch_id = $row['patchid'];
        //            $astatus = 'Error';
        //            $checkBox = '';
        //        $select_status_patch = $db->prepare("SELECT action FROM " . $GLOBALS['PREFIX'] . "softinst.PatchActions WHERE pgroupid=? and mgroupid=? and patchid=? LIMIT 1");
        //        $select_status_patch->execute([$pgroupId,$mgroupid,$row['patchid']]);
        $select_status_patch = $db->prepare("SELECT action FROM " . $GLOBALS['PREFIX'] . "softinst.PatchActions WHERE mgroupid=? and patchid=? LIMIT 1");
        $select_status_patch->execute([$mgroupid, $row['patchid']]);
        $patch_action = $select_status_patch->fetch();

        if ($patch_action['action'] == 1) {
            $patch_id = $row['patchid'];
            $astatus = 'Declined';
            $checkBox = '';
        } else if ($patch_action['action'] == 4) {
            $patch_id = $row['patchid'];
            $astatus = 'Approved';
            $checkBox = '';
        } else if ($patch_action['action'] == 3) {
            $patch_id = $row['patchid'];
            $astatus = 'Removed';
            $checkBox = '';
        } else {
            $patch_id = $row['patchid'];
            $machineId = $row['id'];
            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            //            $astatus = getPatch_status($patch_id, $db, $machineId);
            $checkStatus = NanoDB::find_one('select COUNT(patchid) from  ' . $GLOBALS['PREFIX'] . 'softinst.PatchGroupMap where patchid = ?  ', null, [$patch_id]);
            if ($checkStatus['COUNT(patchid)'] > 0) {
                $astatus = "Other";
            } else {
                $astatus = "";
            }
        }
        if (($row['date'] == '0') || ($row['date'] == 0)) {
            $date = '-';
        } else {
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                if ($windowType == 'patch_export') {
                    $date = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $row['date'], "Y/m/d");
                } else {
                    $date = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $row['date'], "Y/m/d");
                }
            } else {
                if ($windowType == 'patch_export') {
                    $date = date("Y/m/d", $row['date']);
                } else {
                    $date = date("Y/m/d", $row['date']);
                }
            }
        }
        $count = PATCH_GetPatcheCount('', $searchValue, $searchType, $row['patchid'], $db);
        $types = PATCH_GetPatchType($row['type']);
        $SfileCount = substr_count($row['serverfile'], 'http');

        if ($windowType == 'patch_export') {
            $SfileCount = $SfileCount;
            $patchname = utf8_encode($row['title']);
            $count = $count;
        } else {
            if (($SfileCount == 0) || ($SfileCount == '0')) {
                $SfileCount = '0';
                $count = '<a href="#" data-toggle="modal" style="color:#5882FA;" onclick="patchStatus_NewUI(\'' . $patch_id . '\',\'' . $patchType . '\')">' . $count . '</a>';
            } else {
                $SfileCount = '<a href="#" data-toggle="modal" style="color:#5882FA;" onclick="getkbs(\'' . $patch_id . '\',\'' . $SfileCount . '\')">' . $SfileCount . '</a>';
                $count = '<a href="#" data-toggle="modal" style="color:#5882FA;" onclick="patchStatus_NewUI(\'' . $patch_id . '\',\'' . $patchType . '\')">' . $count . '</a>';
            }
            $patchname = '<p class="ellipsis" style="white-space: pre-wrap;" title="' . utf8_encode($row['title']) . '">' . utf8_encode($row['title']) . '</p>';
        }

        //        var_dump($approvepgupid);
        //        exit();

        if ($astatus == "Detected") {
            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            $astatus = "-";
            if ($windowType == 'patch_export') {
                $checkBox = 'Not Approved';
            }
        } elseif ($astatus == "Installed") {
            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            $astatus = "-";
            if ($windowType == 'patch_export') {
                $checkBox = 'Not Approved';
            }
        } elseif ($astatus == "Declined") {
            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            $astatus = "Declined";
            if ($windowType == 'patch_export') {
                $checkBox = 'Not Approved';
            }
        } else if ($astatus == "Approved") {
            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            $astatus = "Approved";
            if ($windowType == 'patch_export') {
                $checkBox = 'Approved';
            }
        } else if ($astatus == "Removed") {
            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            $astatus = "Removed";
            if ($windowType == 'patch_export') {
                $checkBox = 'Approved';
            }
        } else {
            $astatus = "-";
            if ($windowType == 'patch_export') {
                $checkBox = 'Not Approved';
            }
        }

        $size = $row['size'];

        if (empty($size) || ($size === 'NAN')) {
            $Patchsize = '-';
        } else {
            $Patchsize = formatBytes($row['size']);
        }
        if ($types == 'Undefined') {
            //            $types = 'Undefined';
            $types = 'Other';
        }
        if ($windowType == 'patch_export') {
            $recordList[] = array("checkbox" => $checkBox, "patchname" => $patchname, "type" => $types, "date" => $date, "patchsize" => $Patchsize, "count" => $count, "filecount" => $SfileCount, "astatus" => $astatus);
        } else {
            $recordList[] = array($checkBox, $astatus, $patchname, $types, $date, $Patchsize, $count, $SfileCount, $approvepgupid, $astatus);
        }
    }

    return $recordList;
}

function getCensusId($parentVal, $searchValue, $db)
{

    $sql = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where site='$parentVal' and host='$searchValue'");
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $count = safe_count($res);
    if (($count != '0') || ($count != 0)) {
        $cId = $res['id'];
        return $cId;
    } else {
        $sql1 = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where host='$searchValue' order by id desc limit 1");
        $sql1->execute();
        $res1 = $sql1->fetch(PDO::FETCH_ASSOC);
        $cId1 = $res1['id'];
        return $cId1;
    }
}

function getCensusId_host($groupname, $db)
{

    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
    $sql->execute([$groupname]);
    $Res = $sql->fetch(PDO::FETCH_ASSOC);
    $mgrpid = $Res['mgroupid'];

    $censusuniqArr = array();
    $sql1 = $db->prepare("select censusuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp where m.mgroupuniq=mp.mgroupuniq and m.mgroupid=?");
    $sql1->execute([$mgrpid]);
    $sqlRes = $sql1->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sqlRes as $val) {
        array_push($censusuniqArr, $val['censusuniq']);
    }

    $in = str_repeat('?,', safe_count($censusuniqArr) - 1) . '?';
    $sql1 = $db->prepare("select id,host from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($in)");
    $sql1->execute($censusuniqArr);
    $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);
    $id = '';
    $hostlistArr = array();
    foreach ($sql1Res as $vals) {
        array_push($hostlistArr, $vals['host']);
    }
    $hostlist = implode(',', $hostlistArr);

    return $hostlist;
}

function getPatch_status($patch_id, $db, $machineId = '')
{
    $censusId = $machineId;
    $cId = $machineId;
    if ($searchType == 'Groups') {
        $sql = "select status from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus where patchid='$patch_id' and id in($censusId)";
    } else {
        $sql = "select status from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus where patchid='$patch_id' and id in($cId)";
    }
    $res = find_many($sql, $db);
    foreach ($res as $values) {
        $sval = $values['status'];
    }
    $sttsuval = patchstatus($sval);
    return $sttsuval;
}

function Revpatchstatus($stat)
{

    if ($stat == 'detected') {
        $status = 11;
    }
    if ($stat == 'downloaded') {
        $status = 10;
    }
    if ($stat == 'installed') {
        $status = 8;
    }
    if ($stat == 'pendinginstall') {
        $status = 2;
    }
    if ($stat == 'pendinguninstall') {
        $status = 3;
    }
    if ($stat == 'scheduledinstall') {
        $status = 4;
    }
    if ($stat == 'scheduledunInstall') {
        $status = 5;
    }
    if ($stat == 'disable') {
        $status = 6;
    }
    if ($stat == 'uninstall') {
        $status = 9;
    }
    if ($stat == 'pendingdownload') {
        $status = 12;
    }
    if ($stat == 'pendingreboot') {
        $status = 13;
    }
    if ($stat == 'potentialinstallfailure') {
        $status = 14;
    }
    if ($stat == 'superseded') {
        $status = 15;
    }
    if ($stat == 'waiting') {
        $status = 16;
    }
    if ($stat == 'error') {
        $status = 7;
    }
    if ($stat == 'declined') {
        $status = 1;
    }
    if ($stat == 'alreadyinstalled') {
        $status = 18;
    }
    return $status;
}

function patchstatus($stat)
{
    if ($stat == 11) {
        $status = 'Detected';
    }
    if ($stat == 10) {
        $status = 'Downloaded';
    }
    if ($stat == 8 || $stat == 19) {
        $status = 'Installed';
    }
    if ($stat == 2) {
        $status = 'Pending Install';
    }
    if ($stat == 3) {
        $status = 'Pending UnInstall';
    }
    if ($stat == 4) {
        $status = 'Scheduled Install';
    }
    if ($stat == 5) {
        $status = 'Scheduled UnInstall';
    }
    if ($stat == 6) {
        $status = 'Disable';
    }
    if ($stat == 9) {
        $status = 'UnInstall';
    }
    if ($stat == 12) {
        $status = 'Pending Download';
    }
    if ($stat == 13) {
        $status = 'Pending Reboot';
    }
    if ($stat == 14) {
        $status = 'Potential Install Failure';
    }
    if ($stat == 15) {
        $status = "Superseded";
    }
    if ($stat == 16) {
        $status = "Waiting";
    }
    if ($stat == 7) {
        $status = 'Error';
    }
    if ($stat == 1) {
        $status = 'Declined';
    }
    if ($stat == 18) {
        $status = 'Already Installed';
    }
    if ($stat == 20) {
        $status = 'Uninstall Error';
    }
    return $status;
}

function mumgetscheduleset()
{
    $db = pdo_connect();
    $rp = array();
    $key = '';
    $recordlist = [];
    if ($_SESSION['searchType'] == 'ServiceTag') {
        $lDisply = $_SESSION['rparentName'] . ':' . $_SESSION['searchValue'];
    } else {
        $lDisply = $_SESSION['searchValue'];
    }
    $rparentName = $_SESSION["rparentName"];
    $name = $_SESSION['rparentName'];
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $username = $_SESSION['user']['username'];
    $rparent = $_SESSION['rparentName'];
    $date = time();

    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    switch ($searchType) {
        case 'Sites':
            $Configuresql = PATCH_GetConfigureSiteDetails($key, $db, $dataScope, $searchValue);
            break;
        case 'Groups':
            $name = PATCH_GroupName($db, $searchValue);
            $Configuresql = PATCH_GetConfigureGroupDetails($key, $db, $name, $dataScope);
            break;
        case 'ServiceTag':
            $Configuresql = PATCH_GetConfigureMachineDetails($key, $db, $rparent, $searchValue);
            break;
        default:
            break;
    }
    $pconfig = '';
    $labelpconfigid = '';
    $pconfigid = '';
    foreach ($Configuresql as $key => $value) {
        $pconfig .= "" . $value['pconfigid'] . ",";
        $labelpconfigid = rtrim($pconfig, ',');
        $pconfigid = $labelpconfigid;
    }
    if ($pconfigid == '') {
        $res = safe_array_keys($dataScope);
        $siteListArr = array();
        $grouplistArr = array();
        if (is_array($res) && !empty($res)) {
            foreach ($res as $row) {
                array_push($siteListArr, $dataScope[$row]);
                array_push($grouplistArr, $row);
            }
        }

        if ($searchType == 'Sites') {

            if ($searchValue == 'All') {
                $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
                $sql->execute($siteslistArr);
            } else {
                $searchValueArr = array();
                $searchValueArr = explode(',', $searchValue);
                $in  = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
                $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in) limit 1");
                $sql->execute($searchValueArr);
            }
        } else if ($searchType == 'ServiceTag') {
            $lDisplyArr = array();
            $lDisplyArr = explode(',', $lDisply);
            $in  = str_repeat('?,', safe_count($$lDisplyArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in) limit 1");
            $sql->execute($lDisplyArr);
        } else if ($searchType == 'Groups') {
            $idArr = array();
            $sitesArr = array();
            foreach ($machines as $value) {
                $machine .= "'" . $value . "',";
            }
            $machine = rtrim($machine, ',');
            foreach ($machines as $key => $value) {
                array_push($idArr, $key);
            }

            $in  = str_repeat('?,', safe_count($idArr) - 1) . '?';
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where id in ($in)");
            $sql->execute($idArr);
            $sqlres = $sql->fetchAll();

            foreach ($sqlres as $val) {
                $sites .= "'" . $val['site'] . ':' . $val['host'] . "',";
            }
            $sites = rtrim($sites, ',');
            $sitesArr = explode(',', $sites);

            $in  = str_repeat('?,', safe_count($sitesArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid,name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name  in (" . rtrim($sites, ',') . ")");
            $sql->execute($sitesArr);
        }

        $result = $sql->fetchAll();
        if ($searchType == 'Sites' || $searchType == 'ServiceTag') {

            if ($searchType == 'Sites') {
                $lDisply = $_SESSION['searchValue'];
            } else if ($searchType == 'ServiceTag') {
                $lDisply = $_SESSION['rparentName'];
            }

            $sql = $db->prepare("select U.customer, U.id from " . $GLOBALS['PREFIX'] . "core.Customers as U, " . $GLOBALS['PREFIX'] . "core.Census as X, " . $GLOBALS['PREFIX'] . "softinst.Machine as M where X.id = M.id
                            and X.site = U.customer and U.username = ? group by U.customer order by U.customer");
            $sql->execute([$username]);
            $query_check = $sql->fetchAll();
            foreach ($query_check as $key => $rowc) {
                $rp[] = $rowc['customer'];
            }
        } else if ($searchType == 'Groups') {

            if ($_SESSION['searchValue'] == 'All') {
                $lDisply = 'All';
            } else {

                $sql = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ? limit 1");
                $sql->execute([$lDisply]);
                $sqlres = $sql->fetch();
                $lDisply = $lDisply;
                $sql = $db->prepare("select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category = 'Wiz_SCOP_MC' limit 1");
                $sql->execute();
                $cat_query = $sql->fetch();

                $sql = $db->prepare("select mgroupid, name, username from " . $GLOBALS['PREFIX'] . "core.MachineGroups left join " . $GLOBALS['PREFIX'] . "core.MachineCategories on (
                        " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq = " . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq) where mcatid = ?
                        and (username = ? or global = 1) order by name");
                $sql->execute([$cat_query['mcatid'], $username]);
                $query_check = $sql->fetchAll();
                foreach ($query_check as $key => $rowc) {
                    $rp[] = $rowc['name'];
                }
            }
        }
        if ($lDisply == 'All') {
            $jsondata = array('msg' => "success");
        } else {
            if (in_array($lDisply, $rp)) {
                if ($searchType == 'Sites') {
                    $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ', '9' => 'Wiz_RETRY_PC ');
                    foreach ($pcategory_id as $key => $value) {
                        $insert_patch_groups = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PatchGroups (pcategoryid, name, global, human, style, created, boolstring, whereclause)
                          VALUES (?,?,?,?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE
                          name = VALUES(name)
                          ");
                        $insert_patch_groups->execute([$key, $value . $lDisply, "1", "0", "2", $date, "", ""]);
                    }
                    $jsondata = array('msg' => "success");
                } else if ($searchType == 'ServiceTag') {
                    $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ', '9' => 'Wiz_RETRY_PC ');
                    foreach ($pcategory_id as $key => $value) {
                        $lDisplymach = $_SESSION['rparentName'] . ':' . $_SESSION['searchValue'];
                        $lDisplymach = $_SESSION['rparentName'] . ':' . $_SESSION['searchValue'];
                        $insert_patch_groups = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PatchGroups (pcategoryid, name, global, human, style, created, boolstring, whereclause)
                          VALUES (?,?,?,?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE
                          name = VALUES(name)
                          ");
                        $insert_patch_groups->execute([$key, $value . $lDisplymach, "1", "0", "2", $date, "", ""]);
                    }
                    $jsondata = array('msg' => "success");
                } else if ($searchType == 'Groups') {
                    $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ', '9' => 'Wiz_RETRY_PC ');
                    foreach ($pcategory_id as $key => $value) {
                        $insert_patch_groups = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.PatchGroups (pcategoryid, name, global, human, style, created, boolstring, whereclause)
                          VALUES (?,?,?,?,?,?,?,?)
                          ON DUPLICATE KEY UPDATE
                          name = VALUES(name)
                          ");
                        $insert_patch_groups->execute([$key, $value . $lDisply, "1", "0", "2", $date, "", ""]);
                    }
                    $jsondata = array('msg' => "success");
                }
            } else {
                $assettable = $_SESSION['assetTableName'];
                $machtable = $_SESSION['machineTableName'];

                if ($searchType == 'Sites') {

                    $sqlos = $db->prepare("SELECT value FROM " . $GLOBALS['PREFIX'] . "asset.$assettable A, " . $GLOBALS['PREFIX'] . "asset.$machtable M WHERE M.cust=? AND "
                        . "A.machineid=M.machineid AND A.dataid IN (SELECT dataid FROM " . $GLOBALS['PREFIX'] . "asset.DataName D WHERE D.name='Operating System') "
                        . "ORDER BY A.machineid DESC LIMIT 1");
                    $sqlos->execute([$_SESSION['searchValue']]);
                } else if ($searchType == 'ServiceTag') {

                    $sqlos = $db->prepare("SELECT value FROM " . $GLOBALS['PREFIX'] . "asset.$assettable A, " . $GLOBALS['PREFIX'] . "asset.$machtable M WHERE M.host=? AND M.cust=? AND "
                        . "A.machineid=M.machineid AND A.dataid IN (SELECT dataid FROM " . $GLOBALS['PREFIX'] . "asset.DataName D WHERE D.name='Operating System') "
                        . "ORDER BY A.machineid DESC LIMIT 1");
                    $sqlos->execute([$_SESSION['searchValue'], $_SESSION['rparentName']]);
                } else if ($searchType == 'Groups') {
                    $machineValueArr = array();
                    $srchVal = $_SESSION['searchValue'];
                    $machine_sql = $db->prepare("select c.host host from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mgm on mg.mgroupuniq = mgm.mgroupuniq
                                            join " . $GLOBALS['PREFIX'] . "core.Census c on c.censusuniq = mgm.censusuniq
                                            where mg.name = ? order by name");
                    $machine_sql->execute([$srchVal]);
                    $machine_query = $machine_sql->fetchAll();

                    foreach ($machine_query as $key => $row) {
                        array_push($machineValueArr, $row['host']);
                    }
                    $in  = str_repeat('?,', safe_count($machineValueArr) - 1) . '?';
                    $sqlos = $db->prepare("SELECT value FROM " . $GLOBALS['PREFIX'] . "asset.$assettable A, " . $GLOBALS['PREFIX'] . "asset.$machtable M WHERE M.host in ($in) AND "
                        . "A.machineid=M.machineid AND A.dataid IN (SELECT dataid FROM " . $GLOBALS['PREFIX'] . "asset.DataName D WHERE D.name='Operating System') "
                        . "ORDER BY A.dataid DESC LIMIT 1;");
                    $sqlos->execute($machineValueArr);
                }


                $sqlosres = $sqlos->fetchAll();
                $opersys = explode(' ', $sqlosres['value']);
                $operating = $opersys[0];
                if ($operating == 'Windows') {
                    $msg = 'windows';
                } else if ($operating == 'MAC') {
                    $msg = 'Mac';
                }
                $jsondata = array('msg' => $msg);
            }
        }
    } else {
        if ($searchType == 'Sites') {
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.`PatchGroups` where name like '%$lDisply%'");
            $sql->execute();
            $sqlRes = safe_count($sql->fetchAll(PDO::FETCH_ASSOC));
            if ($sqlRes <= 0) {
                $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ', '9' => 'Wiz_RETRY_PC ');
                foreach ($pcategory_id as $key => $value) {
                    $insert_patch_groups = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroups` (`pcategoryid`,`name`,`global`,`human`,`style`,`created`,`boolstring`,`whereclause`) VALUES (?,?,?,?,?,?,?,?)");
                    $insert_patch_groups->execute([$key, $value . $lDisply, "1", "0", "2", $date, "", ""]);
                }
                $jsondata = array('msg' => "success");
            }
        } else if ($searchType == 'ServiceTag') {
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.`PatchGroups` where name like '%$lDisplymach%'");
            $sql->execute();
            $sqlRes = safe_count($sql->fetchAll(PDO::FETCH_ASSOC));
            if ($sqlRes <= 0) {
                $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ', '9' => 'Wiz_RETRY_PC ');
                foreach ($pcategory_id as $key => $value) {
                    $lDisplymach = $_SESSION['rparentName'] . ':' . $_SESSION['searchValue'];
                    $lDisplymach = $_SESSION['rparentName'] . ':' . $_SESSION['searchValue'];
                    $insert_patch_groups = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroups` (`pcategoryid`,`name`,`global`,`human`,`style`,`created`,`boolstring`,`whereclause`) VALUES (?,?,?,?,?,?,?,?)");
                    $insert_patch_groups->execute([$key, $value . $lDisplymach, "1", "0", "2", $date, "", ""]);
                }
                $jsondata = array('msg' => "success");
            }
        } else if ($searchType == 'Groups') {
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.`PatchGroups` where name like '%$lDisply%'");
            $sql->execute();
            $sqlRes = safe_count($sql->fetchAll(PDO::FETCH_ASSOC));
            if ($sqlRes <= 0) {
                $pcategory_id = array('6' => 'Wiz_DECL_PG ', '7' => 'Wiz_APPR_PG ', '5' => 'Wiz_REMV_PG ', '8' => 'Wiz_CRIT_PG ', '9' => 'Wiz_RETRY_PC ');
                foreach ($pcategory_id as $key => $value) {
                    $insert_patch_groups = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroups` (`pcategoryid`,`name`,`global`,`human`,`style`,`created`,`boolstring`,`whereclause`) VALUES (?,?,?,?,?,?,?,?)");
                    $insert_patch_groups->execute([$key, $value . $lDisply, "1", "0", "2", $date, "", ""]);
                }
                $jsondata = array('msg' => "success");
            }
        }
    }

    echo json_encode($jsondata);
}

function getCensusId_grp($groupname, $db)
{
    $censusuniqArr = array();
    $sql = $db->prepare("select censusuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp where m.mgroupuniq=mp.mgroupuniq and m.name=?");
    $sql->execute([$groupname]);
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);

    foreach ($sqlRes as $val) {
        array_push($censusuniqArr, $val['censusuniq']);
    }

    $in  = str_repeat('?,', safe_count($censusuniqArr) - 1) . '?';
    $sql = $db->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($in)");
    $sql->execute($censusuniqArr);
    $sql1Res = $sql->fetchAll(PDO::FETCH_ASSOC);

    $id = '';
    foreach ($sql1Res as $vals) {
        $id .= $vals['id'] . ",";
    }
    $censusid = rtrim($id, ',');
    return $censusid;
}

function mumgetfilterData()
{
    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;
    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    //    $platform = url::requestToText('platform');
    //    $ptype = url::requestToText('type');
    //    $leveltype = url::requestToText('leveltype');
    //    $actiontype = url::requestToText('actiontype');
    //    $patchstatus = url::requestToText('patchstatus');
    //    $fromdate = url::requestToText('from');
    //    $todate = url::requestToText('to');

    $platform = url::postToRegExp('platform', '#[^A-z0-9,\' ]#');
    $ptype =  url::postToRegExp('type', '#[^A-z0-9,\']#');
    $leveltype = url::postToRegExp('leveltype', '#[^A-z0-9,\']#');
    $actiontype = url::postToRegExp('actiontype', '#[^A-z0-9,\']#');
    $patchstatus = url::postToRegExp('patchstatus', '#[^A-z0-9,\']#');
    $fromdate = url::postToRegExp('from', '#[^A-z0-9,\']#');
    $todate = url::postToRegExp('to', '#[^A-z0-9,\']#');

    if ($leveltype === 'filter') {
        $limitStr = 'LIMIT ' . $limitStart . ',' . $limitEnd;
    } else {
        $limitStr = '';
    }

    $todaystime = time();
    if (($fromdate == "") || ($todate == "")) {
        $fromdate = date("m/d/Y", strtotime("-150 days"));
        $todate = date("m/d/Y");
        $where1 = "";
    } else {
        $fromdate = url::requestToText('from');
        $todate = url::requestToText('to');
        $where1 = "and date BETWEEN '" . strtotime("$fromdate") . "' AND '" . strtotime("$todate") . "'";
    }

    $searchValue = $_SESSION['searchValue'];
    $username = $_SESSION['user']['username'];
    $rrparentName = $_SESSION['rparentName'];
    $groupid = url::getToText('grp_id');
    $db = pdo_connect();
    $patch_arr = array();
    $decl_arr = array();

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $censusId = $_SESSION['rcensusId'];
    $key = '';

    if ($searchType == 'ServiceTag') {
        if ($_SESSION["rparentName"] == 'All') {

            $lableDiaplay = $_SESSION['searchValue'];
        } else {

            $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        }
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }
    $statusArray = [];

    if ($patchstatus == "''") {
        $patchstatus = '';
    } else {
        if ($patchstatus == "'all','detected','downloaded','installed','pendinginstall','declined','alreadyinstalled','pendinguninstall','scheduledinstall','scheduledunInstall','disable','uninstall','pendingdownload','pendingreboot','potentialinstallfailure','superseded','waiting'") {
            $patchstatus = "and status in ('1','2','3','4','5','6','8','9','10','11','12','13','14','15','16','18')";
        } else {
            $patchstatus = explode(',', $patchstatus);
            foreach ($patchstatus as $key => $val) {
                $val = rtrim($val, "'");
                $val = ltrim($val, "'");
                $status = Revpatchstatus($val);
                array_push($statusArray, $status);
            }
            $status = implode(',', $statusArray);
            $patchstatus = "and status in ($status)";
        }
    }
    if ($ptype != "''") {
        if ($ptype == "'all','0','1','3','4','5'") {
            $patchType = " and type in ('0','1','3','4','5') ";
        } else {
            $patchType = " and type in($ptype) ";
        }
    } else {
        $patchType = "";
    }

    if ($platform != "''") {
        if ($platform == "'Others'") {
            $platformType = " and p.platform NOT IN ('Windows 10','Windows 8.1','Windows 7','Windows 8')";
        } else if ($platform == "'all','Windows 10','Windows 7','Windows 8','Windows 8.1','Others'") {
            $platformType = " and p.platform IN ('Windows 10','Windows 8.1','Windows 7','Windows 8')";
        } else {
            $platformType = " and p.platform in($platform) ";
        }
    } else {
        $platformType = "";
    }
    if ($actiontype != "''") {
        if ($actiontype == "'All'" || $actiontype == "All") {
            if ($searchType == 'Sites') {
                $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.serverfile,p.date,ps.patchconfigid,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                    . "p.patchid = ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site=?  group by patchid");
                $sql->execute([$searchValue]);
                $query_p = $sql->fetchAll(PDO::FETCH_ASSOC);
                foreach ($query_p as $key => $val) {
                    array_push($patch_arr, $val['patchid']);
                }
            } else if ($searchType == 'ServiceTag') {
                $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.serverfile,p.date,ps.patchconfigid,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                    . "p.patchid = ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where host=?  group by patchid");
                $sql->execute([$searchValue]);
                $query_p = $sql->fetchAll(PDO::FETCH_ASSOC);
                foreach ($query_p as $key => $val) {
                    array_push($patch_arr, $val['patchid']);
                }
            } else if ($searchType == 'Groups') {
                $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $machineArrrr = array();
                $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
                foreach ($machines as $key => $val) {
                    array_push($machineArrrr, $val);
                }
                $in1 = str_repeat('?,', safe_count($machineArrrr) - 1) . '?';
                $sql = $db->prepare("select DISTINCT c.id,c.host,p.patchid,p.title,p.type,p.size,p.priority,p.serverfile,p.date,ps.patchconfigid,c.site from " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on "
                    . "p.patchid = ps.patchid join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where host in ($in1)  group by patchid");
                $sql->execute($machineArrrr);
                $query_p = $sql->fetchAll(PDO::FETCH_ASSOC);

                foreach ($query_p as $key => $val) {
                    array_push($patch_arr, $val['patchid']);
                }
            }
            $combinedArr = implode(',', $patch_arr);
            $actionType = "and p.patchid in ($combinedArr)";
        } else if ($actiontype == "'approved'") {
            $patch_arr = array();

            $sql1 = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
            $sql1->execute(["Wiz_APPR_PG " . $lableDiaplay]);
            $row1 = $sql1->fetch();
            $pgroupid = $row1['pgroupid'];
            $sql_p = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ?
                    and P.patchid = M.patchid group by P.patchid order by name, date, patchid");
            $sql_p->execute([$pgroupid]);
            $query_p = $sql_p->fetchAll();
            foreach ($query_p as $key => $val) {
                array_push($patch_arr, $val['patchid']);
            }
            $combinedArr = implode(',', $patch_arr);
            $actionType = "and p.patchid in ($combinedArr)";
        } elseif ($actiontype == "'declined'") {
            $sql2 = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
            $sql2->execute(["Wiz_DECL_PG " . $lableDiaplay]);
            $query2 = $sql2->fetch();
            $pgroupid = $query2['pgroupid'];

            $sql_d = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ?
                    and P.patchid = M.patchid group by P.patchid order by name, date, patchid");
            $sql_d->execute([$pgroupid]);
            $query_d = $sql_d->fetchAll();
            $patch_arr = array();
            foreach ($query_d as $key => $val) {
                array_push($patch_arr, $val['patchid']);
            }
            $combinedArr = implode(',', $patch_arr);
            $actionType = "and p.patchid in ($combinedArr)";
        } elseif ($actiontype == "'critical'") {
            $sqlc = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
            $sqlc->execute(["Wiz_CRIT_PG " . $lableDiaplay]);
            $query2 = $sqlc->fetch();
            $pgroupid = $query2['pgroupid'];

            $sql_c = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ?
                    and P.patchid = M.patchid group by P.patchid order by name, date, patchid");
            $sql_c->execute([$pgroupid]);
            $query_c = $sql_c->fetchAll();
            $patch_arr = array();
            foreach ($query_c as $key => $val) {
                array_push($patch_arr, $val['patchid']);
            }
            $combinedArr = implode(',', $patch_arr);
            $actionType = "and p.patchid in ($combinedArr)";
        } elseif ($actiontype == "'removed'") {
            $sqlr = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = ?");
            $sqlr->execute(["Wiz_REMV_PG " . $lableDiaplay]);
            $queryr = $sqlr->fetch();
            $pgroupid = $queryr['pgroupid'];

            $sql_r = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid = ?
                    and P.patchid = M.patchid group by P.patchid order by name, date, patchid");
            $sql_r->execute([$pgroupid]);
            $query_r = $sql_r->fetchAll();
            $patch_arr = array();
            foreach ($query_r as $key => $val) {
                array_push($patch_arr, $val['patchid']);
            }
            $combinedArr = implode(',', $patch_arr);
            $actionType = "and p.patchid in ($combinedArr)";
        } else {
            $newVal = array();
            $strArray = array();
            $array = explode(',', $actiontype);
            foreach ($array as $key => $val) {
                if ($val == "Approved") {
                    $str = "'Wiz_APPR_PG $lableDiaplay'";
                    array_push($strArray, $str);
                } elseif ($val == "Declined") {
                    $str = "'Wiz_APPR_PG $lableDiaplay'";
                    array_push($strArray, $str);
                } elseif ($val == "Critical") {
                    $str = "'Wiz_APPR_PG $lableDiaplay'";
                    array_push($strArray, $str);
                } elseif ($val == "Removed") {
                    $str = "'Wiz_APPR_PG $lableDiaplay'";
                    array_push($strArray, $str);
                } else if ($val == "All") {
                    $str = "'Wiz_APPR_PG $lableDiaplay'";
                    array_push($strArray, $str);
                }
            }
            $in  = str_repeat('?,', safe_count($strArray) - 1) . '?';
            $sqlr = $db->prepare("select pgroupid from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name in($in)");
            $sqlr->execute($strArray);
            $rowr = $sqlr->fetchAll();

            $newArr = array();
            foreach ($rowr as $key => $val) {
                array_push($newArr, $val['pgroupid']);
            }
            $in  = str_repeat('?,', safe_count($newArr) - 1) . '?';
            $sql_r = $db->prepare("select P.patchid from " . $GLOBALS['PREFIX'] . "softinst.Patches as P," . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M where M.pgroupid in ($in)
                and P.patchid = M.patchid group by P.patchid order by name, date, patchid");
            $sql_r->execute($newArr);
            $query_r = $sql_r->fetchAll();
            $combinedArr = array();
            foreach ($query_r as $key => $val) {
                array_push($combinedArr, $val['patchid']);
            }

            $combinedArr = implode(',', $combinedArr);
            $actionType = "and p.patchid in ($combinedArr)";
        }
    } else {
        $actionType = "";
    }
    $searchValue = $_SESSION['searchValue'];
    if ($_SESSION['searchType'] == 'Site' || $_SESSION['searchType'] == 'Sites') {
        $sql = $db->prepare("select  p.patchid,p.title,p.type,p.size,p.priority,p.date,c.site,p.serverfile from
                        " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid
                join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where c.site = ? " . $patchstatus . $patchType . $actionType . $platformType . $where1 . " group by patchid $limitStr");
        $sql->execute([$searchValue]);
        $sql2 = $db->prepare("select  p.patchid,p.title,p.type,p.size,p.priority,p.date,c.site,p.serverfile from
                        " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid
                join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where site = ? " . $patchstatus . $patchType . $actionType . $platformType . $where1 . " group by patchid");
        $sql2->execute([$searchValue]);
    } else if ($_SESSION['searchType'] == 'Service Tag' || $_SESSION['searchType'] == 'Host Name' || $_SESSION['searchType'] == 'ServiceTag') {
        $sql = $db->prepare("select p.patchid,p.title,p.type,p.size,p.priority,p.date,c.host,p.serverfile from
                        " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid
                join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where host = ? " . $patchstatus . $patchType . $actionType . $platformType . $where1 . " group by patchid $limitStr");
        $sql->execute([$searchValue]);

        $sql2 = $db->prepare("select p.patchid,p.title,p.type,p.size,p.priority,p.date,c.host,p.serverfile from
                        " . $GLOBALS['PREFIX'] . "softinst.Patches p join " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps on p.patchid=ps.patchid
                join " . $GLOBALS['PREFIX'] . "core.Census c on ps.id=c.id where host = ? " . $patchstatus . $patchType . $actionType . $where1 . " group by patchid");
        $sql2->execute([$searchValue]);
    } else if ($_SESSION['searchType'] == 'Groups') {
        $lableDiaplay = $_SESSION['searchValue'];
        $hosts = getCensusId_host($lableDiaplay, $db);
        $hostsArr = array();
        $hostsArr = explode(',', $hosts);
        $in  = str_repeat('?,', safe_count($hostsArr) - 1) . '?';
        $sql = $db->prepare("select distinct p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile from
                " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps
                left join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = ps.id
                left join " . $GLOBALS['PREFIX'] . "softinst.Patches p on p.patchid = ps.patchid
               where c.host in($in) $patchstatus $patchType $actionType $platformType $where1 group by patchid $limitStr");
        $sql->execute($hostsArr);
        $sql2 = $db->prepare("select distinct p.patchid,p.title,p.type,p.size,p.priority,p.date,p.serverfile from
                " . $GLOBALS['PREFIX'] . "softinst.PatchStatus ps
                left join " . $GLOBALS['PREFIX'] . "core.Census c on c.id = ps.id
                left join " . $GLOBALS['PREFIX'] . "softinst.Patches p on p.patchid = ps.patchid
               where c.host in($in) $patchstatus $patchType $actionType $platformType $where1 group by patchid");
        $sql2->execute($hostsArr);
    }
    if ($searchType == 'ServiceTag') {
        if ($_SESSION["rparentName"] == 'All') {

            $lableDiaplay = $_SESSION['searchValue'];
        } else {

            $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
            if ($_SESSION['rcensusId'] == "") {
                $censusId = getCensusId($_SESSION["rparentName"], $_SESSION['searchValue'], $db);
            } else {
                $censusId = $_SESSION['rcensusId'];
            }
        }
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }
    $approvepgupid = PATCH_ApprovePatchId('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
    $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
    $retrypatch = PATCH_GetRetrypatch('', $lableDiaplay, $searchType, $db);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);
    $totCount = safe_count($sql2->fetchAll(PDO::FETCH_ASSOC));
    $patchidArr = array();
    foreach ($result as $key => $value) {
        array_push($patchidArr, $value['patchid']);
    }
    $totalRecords = safe_count($result);
    if ($leveltype == 'filter') {
        if (safe_sizeof($result) == 0) {
            $dataArr['largeDataPaginationHtml'] =  '';
            $dataArr['html'] =   '';
            echo json_encode($dataArr);
        } else {
            $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
            $dataArr['html'] = Format_mumDataMysql($db, $result, $approvepgupid, $approvepatch, $declinepatch, $removepatch, $retrypatch, '');
            echo json_encode($dataArr);
        }
    } else {
        $_SESSION['patchIds'] = json_encode($patchidArr);
        $_SESSION['statusIds'] = json_encode($statusArray);
        echo json_encode($patchidArr);
    }
}

function MUMGetPatchStatusExport()
{
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="patchStatusList.csv"');
    $patchid = $_SESSION['patchIds'];
    $statusID = $_SESSION['statusIds'];
    $patchid = safe_json_decode($patchid, true);
    $patchid = implode(',', $patchid);
    $statusID = safe_json_decode($statusID, true);
    $statusID = implode(',', $statusID);
    $db = pdo_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $index = 2;

    $titleArray = array();
    $finalArray =  array();
    $patchstatus = array();
    $patchStatus = PATCH_GetPatchStatus('', $patchid, $searchValue, $searchType, $db, $statusID);
    $totalRecords = safe_count($patchStatus);
    if ($totalRecords > 0) {
        foreach ($patchStatus as $key => $row) {
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $detected = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $row['detected'], "Y/m/d");
                $downloaded = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $row['lastdownload'], "Y/m/d");
                $install = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $row['lastinstall'], "Y/m/d");
                $releaseDate = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $row['date'], "Y/m/d");
            } else {
                $detected = date("Y/m/d", $row['detected']);
                $downloaded = date("Y/m/d", $row['lastdownload']);
                $install = date("Y/m/d", $row['lastinstall']);
                $releaseDate = date("Y/m/d", $row['date']);
            }
            $detected = ($row['detected'] != 0) ? $detected : 'Not detected';
            $downloaded = ($row['lastdownload'] != 0) ? $downloaded : 'Not downloaded';
            $install = ($row['lastinstall'] != 0) ? $install : 'Not installed';
            $releaseDate = ($row['date'] != 0) ? $releaseDate : 'Not released';
            $os = $row['os'];
            $type = PATCH_GetPatchType($row['type']);
            $sitename = $row['site'];
            $hostname = $row['host'];
            $patchname = $row['title'];
            $patchsta = getpatchstatus($row['status']);
            $detected = $detected;
            $lastdownload = $downloaded;
            $installdata =  $install;
            $error = $row['lasterror'];

            $patchstatus[] = array("Site" => $sitename, "Host" => $hostname, "Patch Name" => $patchname, "Status" => $patchsta, "OS" => $os, "Patch Type" => $type, "Release Date" => $releaseDate, "Detected Date" => $detected, "Download Date" => $lastdownload, "Install Date" => $installdata, "Last Error" => $error);
        }
    } else {
        $patchstatus = array();
    }
    $columnArray = safe_array_keys($patchstatus[safe_array_keys($patchstatus)[0]]);
    foreach ($columnArray as $key => $eachColumns) {
        if ($eachColumns != '') {
            array_push($titleArray, $eachColumns);
        }
    }
    $finalArray[0] = $titleArray;
    foreach ($patchstatus as $key => $eachAssoc) {
        array_push($finalArray, $eachAssoc);
    }
    ob_clean();
    $fp = fopen('php://output', 'wb');
    foreach ($finalArray as $line) {
        fputcsv($fp, $line, ',');
    }
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    $auditRes = create_auditLog('Patch Management', 'Export', 'Success', NULL, $gpname);

    fclose($fp);
}

function getretrypatchData()
{
    $db = pdo_connect();
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $rsitename = $_SESSION['rparentName'];

    if ($searchType == 'ServiceTag') {
        if ($_SESSION["rparentName"] == 'All') {

            $lableDiaplay = $_SESSION['searchValue'];
        } else {

            $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
            if ($_SESSION['rcensusId'] == "") {
                $censusId = getCensusId($_SESSION["rparentName"], $_SESSION['searchValue'], $db);
            } else {
                $censusId = $_SESSION['rcensusId'];
            }
        }
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }

    $sql = $db->prepare("select pcategoryid from " . $GLOBALS['PREFIX'] . "softinst.PatchCategories where category = 'Wiz_RETRY_PC' limit 1");
    $sql->execute();
    $sqlresult = $sql->fetch();

    $pcategoryid = $sqlresult['pcategoryid'];
    if ($pcategoryid == '') {
        $sqlmakecategoryEntry = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "softinst.PatchCategories set category = 'Wiz_RETRY_PC' , precedence = '9'");
        $sqlmakecategoryEntry->execute();
    }

    if ($searchType == 'Sites' || $searchType == 'Site') {

        $sqlretry = PATCH_sitesretryPatches($key, $db, $rsitename);
    } else if ($searchType == 'Service Tag' || $searchType == 'Host Name') {

        $sqlretry = PATCH_machineretryPatches($key, $rsitename, $searchValue, $db);
    } else if ($searchType == 'Groups' || $searchType == 'Group') {

        $sqlretry = PATCH_groupretryPatches($key, $searchValue, $db);
    }

    $total = safe_count($sqlretry);
    foreach ($sqlretry as $key => $value) {
        $patchids .= "" . $value['patchid'] . " ";
    }
    $result = $sqlretry;
    $retrypatchid = PATCH_RetryPatchId('', $lableDiaplay, $searchType, $db);
    $criticalpatchid = MUM_GetCriticalpatch('', $lableDiaplay, $searchType, $db);
    $declinepgupid = PATCH_DeclinePatchId('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $approvepgupid = PATCH_ApprovePatchId('', $lableDiaplay, $searchType, $db);
    $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
    $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
    $totalRecords = safe_count($result);
    if ($totalRecords > 0) {
        foreach ($result as $key => $row) {
            if (in_array($row['patchid'], $approvepatch)) {
                $patch_id = $row['patchid'];
                $astatus = '<b>Approved</b>';
                $checkBox = '<input type="hidden" id="hidden_pgroupid" value="' . $patch_id . '">'
                    . '<input type="hidden" id="selected_appr_patchname" value="' . utf8_encode($row['title']) . '">'
                    . '<input type="hidden" id="hidden_approvedgrpid" value="' . $approvepgupid . '">'
                    . '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="checkboxsel" id="' . $retrypatchid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
            } else if (in_array($row['patchid'], $declinepatch)) {
                $patch_id = $row['patchid'];
                $astatus = '<b>Declined</b>';
                $checkBox = '';
            } else if (in_array($row['patchid'], $removepatch)) {
                $patch_id = $row['patchid'];
                $astatus = '<b>Removed</b>';
                $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="form-check-input user_check actionchkptch" name="checkboxsel" id="' . $retrypatchid  . '"><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
            } else if (in_array($row['patchid'], $criticalpatch)) {
                $patch_id = $row['patchid'];
                $astatus = '<b>Critical</b>';
                $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="form-check-input user_check actionchkptch" name="checkboxsel" id="' . $retrypatchid  . '"><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
            } else {
                $patch_id = $row['patchid'];
                $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input user_check actionchkptch" name="checkboxsel" id="' . $retrypatchid  . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
                $astatus = '';
            }

            $patchname = '<p class="ellipsis" title="' . $row['title'] . '">' . $row['title'] . '</p>';
            $astatus = $astatus;
            $count = PATCH_GetPatcheCount('', $searchValue, $searchType, $row['patchid'], $db);
            $types = PATCH_GetPatchType($row['type']);
            $Sql = "select * from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus where patchid = $patch_id";
            $Sqlresult = find_one($Sql, $db);
            $status = $row['status'];
            if ($status == '7') {
                $astatus = 'Error';
            }
            $size = $row['size'];
            if (empty($size) || ($size == 'NAN')) {
                $Patchsize = '-';
            } else {
                $Patchsize = formatBytes($row['size']);
            }
            if ($types == 'Undefined') {
                $types = 'Undefined';
            }
            $error = $row['lasterror'];
            $machine = $row['host'];
            $recordList[] = array($checkBox, $patchname, $machine, $astatus, $error, $types);
        }
    } else {
        $recordList = array();
    }
    $auditRes = create_auditLog('Patch Management', 'Retry Patch', 'Success');
    echo json_encode($recordList);
}

function mumupdatemethod()
{
    $db = pdo_connect();
    $_GET['ins'] = url::requestToText('method');
    $updatetype = url::requestToText('updatetype');
    $wintype = url::requestToText('wintype');
    $rparentName = $_SESSION['rparentName'];
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $sername = url::requestToText('susservar');
    $management = url::requestToText('management');
    $manday = url::requestToText('manageday');
    $manhour = url::requestToText('manageminute');
    $newup = url::requestToText('newupdate');
    $download = url::requestToText('downloading');
    $retpolicy = url::requestToText('retentionpol');
    $retdays = url::requestToText('retentiondays');
    if ($retdays == "") {
        $retdays = 1;
    }
    $retentionday = ($retdays * 86400);
    $restart = url::requestToText('restartpolicy');
    $multiple = url::requestToText('multipleinstall');
    $multihour = url::requestToText('multiplehour');
    if ($multihour == "") {
        $multihour = 1;
    }
    $multipleday = ($multihour * 3600);

    $updatemthod = url::requestToText('method');
    $schedmon = url::requestToText('shedmonth');
    $schedday = url::requestToText('schedday');

    $schedlemin = url::requestToText('schedlemin');
    $schedlehour = url::requestToText('schedlehour');
    $schedhour = ($schedlehour != '') ? $schedlehour : 0;
    $schedmin = ($schedlemin != '') ? $schedlemin : 0;
    $schedweek = url::requestToText('scheduleweek');
    $schedaction = url::requestToAny('scheduleaction');
    $scheddelay = (url::requestToAny('scheduledelay') != '') ? url::requestToText('scheduledelay') : 0;
    $scheddoper = (url::requestToAny('sdelayoper') != '') ? url::requestToText('sdelayoper') : 0;


    $notify1 = url::requestToText('notifyadvance') === 'on' ? 1 : 0;
    $notifyopt = url::requestToText('notiopt');
    $notifyremind = url::requestToText('notiremind');
    $notifysched = url::requestToText('notisched');
    $notifyprev = url::requestToText('notiprev');

    $notifhour = url::requestToText('notifhour');
    $notifmin = url::requestToText('notifmin');
    $notifhour = ($notifhour != '') ? $notifhour : 0;
    $notifmin = ($notifmin != '') ? $notifmin : 0;

    $notifyhour = url::requestToText('notihour');
    $notifyminute = url::requestToText('notimin');
    $notifyweek = url::requestToText('notiweek');
    $notifymon = url::requestToText('notifmon');
    $notifyday = url::requestToText('notifday');
    $notifyact = url::requestToText('notiaction');
    $notifydelay = (url::requestToAny('notirdelay') != '') ? url::requestToText('notirdelay') : 0;
    $notifytext = url::requestToText('notif_text');

    $pconfigid = url::requestToText('pconfig');
    $date = time();
    $apprpgroupid = PATCH_ApprovePatchId('', $searchValue, $searchType, $db);
    $retrypgroupid = PATCH_RetryPatchId('', $searchValue, $searchType, $db);
    $criticalpgroupid = PATCH_CriticalPatchId('', $searchValue, $searchType, $db);


    $dataScope = UTIL_GetSiteScope($db, $searchValue, $searchType);
    $siteListArr = array();
    $grouplistArr = array();
    $res = safe_array_keys($dataScope);
    foreach ($res as $row) {
        array_push($siteListArr, $dataScope[$row]);
        array_push($grouplistArr, $row);
    }
    if (safe_count($siteListArr) > 0) {
        $in1  = str_repeat('?,', safe_count($siteListArr) - 1) . '?';
    } else {
        $in1  = "";
    }

    if (safe_count($siteListArr) > 0) {
        $in2  = str_repeat('?,', safe_count($grouplistArr) - 1) . '?';
    } else {
        $in2  = "";
    }



    if ($searchType == 'Sites') {
        if ($searchValue == 'All') {
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in1)");
            $sql->execute($siteListArr);
        } else {
            $searchValueArr = explode(',', $searchValue);
            $in3  = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in3)");
            $sql->execute($searchValueArr);
        }
        $result = $sql->fetchAll();
    } else if ($searchType == 'Groups') {
        if ($searchValue == 'All') {
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in2)");
            $sql->execute($grouplistArr);
        } else {
            $searchValueArr = explode(',', $searchValue);
            $in4  = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
            $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in4)");
            $sql->execute($searchValueArr);
        }
        $result = $sql->fetchAll();
    } else if ($searchType == 'ServiceTag') {

        $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
        $sql->execute([$rparentName . ':' . $searchValue]);
        $result = $sql->fetchAll();
    }
    $mroupidArr = array();
    if (safe_count($result) > 0) {
        foreach ($result as $value) {
            $mroupid .= $value['mgroupid'] . ",";
        }
        $mgrpid = rtrim($mroupid, ',');
        $mroupidArr = explode(',', $mgrpid);
    } else {
        $mgrpid = "''";
        $mroupidArr = array();
    }


    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups where name = 'All' ");
    $sql->execute();
    $patchidRes = $sql->fetch();
    $pgroupidAll = $patchidRes['pgroupid'];

    if ($updatetype === 'settingsubmit') {
        $pgrpid = $pgroupidAll;
    } else {
        if ($wintype == 'retry') {
            $pgrpid = $retrypgroupid;
        } else if ($wintype == 'critical') {
            $pgrpid = $criticalpgroupid;
        } else {
            $pgrpid = $apprpgroupid;
        }
    }

    if ($updatemthod == '4' || $updatemthod == 4) {
        if ($searchType == 'Sites') {

            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where site = ?");
            $sql->execute([$searchValue]);
            $res1 = $sql->fetchAll();

            foreach ($res1 as $key => $value) {
                $id = $value['id'];
                $sql2 = $db->prepare("select patchid from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus where id = ?");
                $sql2->execute([$id]);
                $res2 = $sql2->fetch();
                foreach ($res2 as $k => $v) {
                    $patchid = $v['patchid'];
                    $sql3 = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap set pgroupid =? where patchid=?");
                    $sql3->execute([$pgroupidAll, $patchid]);
                }
            }
        }
    }
    $MgrpIn = str_repeat('?,', safe_count($mroupidArr) - 1) . '?';
    $configinfo = getConfigCount($mgrpid, $pgrpid, $db);

    if ($configinfo > 0) {
        if ($updatetype === 'settingsubmit') {
            $params = array_merge([
                $updatemthod, $pgroupidAll, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date
            ], $mroupidArr, [$pgrpid]);
            $result = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?,pgroupid = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                . "notifyfail = 0, notifyadvance = ?, notifyschedule =?, notifyadvancetime = 900, notifytext = ?,"
                . "lastupdate = ? where mgroupid in ($MgrpIn) and pgroupid = ?");
            $result->execute($params);
        } else {
            if ($wintype == 'retry') {
                $params = array_merge([
                    $retrypgroupid, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                    $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date
                ], $mroupidArr, [$retrypgroupid]);
                $result = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = 11,pgroupid = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                    . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                    . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                    . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                    . "notifyfail = 0, notifyadvance = ?, notifyschedule =?, notifyadvancetime = 900, notifytext = ?,"
                    . "lastupdate = ? where mgroupid in ($MgrpIn) and pgroupid = ?");
                $result->execute($params);
            } else if ($wintype == 'critical') {
                $params = array_merge([
                    $updatemthod, $criticalpgroupid, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                    $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date
                ], $mroupidArr, [$criticalpgroupid]);
                $result = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?,pgroupid = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                    . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                    . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                    . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                    . "notifyfail = 0, notifyadvance = ?, notifyschedule =?, notifyadvancetime = 900, notifytext = ?,"
                    . "lastupdate = ? where mgroupid in ($MgrpIn) and pgroupid = ?");
                $result->execute($params);
            } else {
                $params = array_merge([
                    $updatemthod, $apprpgroupid, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                    $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date
                ], $mroupidArr, [$apprpgroupid]);
                $result = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?,pgroupid = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                    . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                    . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                    . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                    . "notifyfail = 0, notifyadvance = ?, notifyschedule =?, notifyadvancetime = 900, notifytext = ?,"
                    . "lastupdate = ? where mgroupid in ($MgrpIn) and pgroupid = ?");
                $result->execute($params);
            }
        }
    } else {
        if ($updatetype === 'settingsubmit') {
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid = ?  and pgroupid = 1");
            $delete->execute([$mgrpid]);
            $db->lastInsertId();
            $params = array_merge([
                $updatemthod, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date, $mgrpid, $pgroupidAll
            ]);
            $result = $db->prepare("insert ignore into " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                . "notifyfail = 0, notifyadvance = ?, notifyschedule = ?, notifyadvancetime = 900, notifytext = ?,"
                . "lastupdate = ?,mgroupid=?, pgroupid = ?");
            $result->execute($params);
        } else {

            if ($wintype == 'retry') {
                $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid = ?  and pgroupid = ?");
                $delete->execute([$mgrpid, $retrypgroupid]);
                $db->lastInsertId();
                $params = array_merge([
                    '11', $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                    $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date, $mgrpid, $retrypgroupid
                ]);
                $result = $db->prepare("insert ignore into " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                    . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                    . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                    . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                    . "notifyfail = 0, notifyadvance = ?, notifyschedule = ?, notifyadvancetime = 900, notifytext = ?,"
                    . "lastupdate = ?,mgroupid=?, pgroupid = ?");
                $result->execute($params);
            } else if ($wintype == 'critical') {
                $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid = ?  and pgroupid = ?");
                $delete->execute([$mgrpid, $criticalpgroupid]);
                $db->lastInsertId();
                $params = array_merge([
                    $updatemthod, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                    $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date, $mgrpid, $criticalpgroupid
                ]);
                $result = $db->prepare("insert ignore into " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                    . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                    . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                    . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                    . "notifyfail = 0, notifyadvance = ?, notifyschedule = ?, notifyadvancetime = 900, notifytext = ?,"
                    . "lastupdate = ?,mgroupid=?, pgroupid = ?");
                $result->execute($params);
            } else {
                $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid = ?  and pgroupid = ?");
                $delete->execute([$mgrpid, $apprpgroupid]);
                $db->lastInsertId();
                $params = array_merge([
                    $updatemthod, $notifyprev, $notifyremind, $scheddoper, $schedmin, $schedhour, $schedday, $schedmon, $schedweek, $scheddelay,
                    $schedaction, $notifmin, $notifhour, $notifyday, $notifymon, $notifyweek, $notifydelay, $notifyact, $notify1, $notifysched, $notifytext, $date, $mgrpid, $apprpgroupid
                ]);
                $result = $db->prepare("insert ignore into " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set installation = ?, preventshutdown = ?, reminduser = ?, configtype = 0, scheddelay = ?,"
                    . "schedminute = ?, schedhour = ?, schedday = ?, schedmonth = ?, schedweek = ?,"
                    . "schedrandom = ?, schedtype = ?, notifydelay = 0, notifyminute = ?, notifyhour = ?,"
                    . "notifyday = ?, notifymonth = ?, notifyweek = ?, notifyrandom = ?, notifytype = ?,"
                    . "notifyfail = 0, notifyadvance = ?, notifyschedule = ?, notifyadvancetime = 900, notifytext = ?,"
                    . "lastupdate = ?,mgroupid=?, pgroupid = ?");
                $result->execute($params);
            }
        }
    }

    $updateResult = $result->rowCount();
    if ($updateResult) {
        echo json_encode(array("msg" => 'success'));
    } else {
        echo json_encode(array("msg" => 'failed'));
    }
}

function getConfigCount($mgrpid, $pgrpid, $db)
{
    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid=? and pgroupid = ?");
    $sql->execute([$mgrpid, $pgrpid]);
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    $count = safe_count($res);
    return $count;
}




function Approve_patch()
{

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $pagetype = url::postToText('page');
    $db = pdo_connect();
    $patchidArr = array();
    $patchidcheckArr = array();

    //=================================
    $patch_id = url::requestToAny('check');
    foreach ($patch_id as $key => $val) {
        if (is_numeric($val)) {
            $patchidcheckArr[] = $val;
        }
    }
    $patch_split = $patchidcheckArr;
    $pgroup_spl = url::requestToAny('pgroupid');
    $pgroup_spl = explode(',', $pgroup_spl[0]);
    foreach ($pgroup_spl as $key => $val) {
        if (is_numeric($val)) {
            $patchidArr[] = $val;
        }
    }
    $pgroup_id = $patchidArr;
    //    $pgroup_id = $pgroup_id[0];
    //    var_dump($pgroup_id);
    //    exit();
    //    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE pgroupid = ? limit 1");
    //    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE mgroupid = ? limit 1");
    //    $check_config->execute([$pgroup_id[0]]);
    //    $check_config_query = $check_config->fetch();
    //    $pgr_config['pgroupid'] = $check_config_query['pgroupid'];
    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);

        $valm = 'Wiz_APPR_PG ' . $lableDiaplay;
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute([$valm]);
        $servisTagGroupId = $sqlpgrpid->fetch();
        $servisTagGroupId = $servisTagGroupId['pgroupid'];
    } else if ($searchType == 'Sites') {
        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);

        $patch_group_name = "All";
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        //        $sqlpgrpid->execute(['Wiz_APPR_PG ' . $lableDiaplay]);
        $sqlpgrpid->execute([$patch_group_name]);
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name =? LIMIT 1");
        $sql->execute([$searchValue]);

        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute(['Wiz_APPR_PG ' . $searchValue]);
    }
    $query = $sql->fetch();
    $mgroupid = $query['mgroupid'];
    $queryp = $sqlpgrpid->fetch();
    if ($servisTagGroupId > 0) {
        $pgroupId = $servisTagGroupId;
    } else {
        $pgroupId = $queryp['pgroupid'];
    }
    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE mgroupid = ? limit 1");
    $check_config->execute([$mgroupid]);
    $check_config_query = $check_config->fetch();
    //  var_dump($check_config_query);
    //  exit();
    // на этом этапе правильные данные


    //    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE pgroupid = ? and mgroupid=? LIMIT 1");
    //    $check_config->execute([$pgroupId, $mgroupid]);
    //    $check_config_query = $check_config->fetch();
    if ($searchType == 'ServiceTag' && $servisTagGroupId == "") {
        $sql_insert_group_machine = "insert into " . $GLOBALS['PREFIX'] . "softinst.PatchGroups(pcategoryid,name,global,human,style,created) VALUES (?,?,?,?,?,?)";
        $data_insert = time();
        $name_group = "Wiz_APPR_PG " . $lableDiaplay;
        NanoDB::insert($sql_insert_group_machine, [9, $name_group, 1, 0, 2, $data_insert]);
        $pgroupId = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=?", null, [$name_group]);
        $pgroupId = $pgroupId['pgroupid'];
    }

    $date = time();



    foreach ($patch_id as $item) {
        $check_action_query = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchActions WHERE pgroupid = ? and mgroupid=? and pconfigid=? and	patchid=?	LIMIT 1");
        $check_action_query->execute([$pgroupId, $mgroupid, $check_config_query['pconfigid'], $item]);
        $check_action_query = $check_action_query->fetch();
        if ($check_action_query['id'] == '') {
            $query = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "softinst.PatchActions set pgroupid =?, mgroupid=?, pconfigid=?, patchid=?, action=?, lastupdate=?");
            $query->execute([$pgroupId, $mgroupid, $check_config_query['pconfigid'], $item, 4, $date]);
        } else {
            $query = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchActions set action=?, lastupdate=? where pgroupid =? and mgroupid=? and pconfigid=? and patchid=?");
            $query->execute([4, $date, $pgroupId, $mgroupid, $check_config_query['pconfigid'], $item]);
        }
    }

    echo "success";
}

function Declinepatch()
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $db = pdo_connect();
    $patchidcheckArr = array();
    $patchidArr = array();
    $patch_id = url::requestToAny('check');

    foreach ($patch_id as $key => $val) {
        if (is_numeric($val)) {
            $patchidcheckArr[] = $val;
        }
    }
    $patch_split = $patchidcheckArr;
    // $pgroup_spl = url::requestToAny('pgroupid');
    // $pgroup_id = $pgroup_spl;

    $patch_split = $patchidcheckArr;

    $pgroup_spl = url::requestToAny('pgroupid');
    foreach ($pgroup_spl as $key => $val) {
        if (is_numeric($val)) {
            $patchidArr[] = $val;
        }
    }
    $pgroup_id = $patchidArr;
    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);

        $valm = 'Wiz_DECL_PG ' . $lableDiaplay;
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute([$valm]);
    } else if ($searchType == 'Sites') {

        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
        $patch_group_name = "All";
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        //        $sqlpgrpid->execute(['Wiz_DECL_PG ' . $lableDiaplay]);
        $sqlpgrpid->execute([$patch_group_name]);
    } else {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$searchValue]);
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute(['Wiz_DECL_PG ' . $searchValue]);
    }
    $query = $sql->fetch();
    $mgroupid = $query['mgroupid'];
    $date = time();
    $queryp = $sqlpgrpid->fetch();
    $pgroupid = $queryp['pgroupid'];

    if ($searchType == 'ServiceTag' && $pgroupid == "") {
        $sql_insert_group_machine = "insert into " . $GLOBALS['PREFIX'] . "softinst.PatchGroups(pcategoryid,name,global,human,style,created) VALUES (?,?,?,?,?,?)";
        $data_insert = time();
        $name_group = "Wiz_DECL_PG " . $lableDiaplay;
        NanoDB::insert($sql_insert_group_machine, [9, $name_group, 1, 0, 2, $data_insert]);
        $pgroupid = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=?", null, [$name_group]);
        $pgroupid = $pgroupid['pgroupid'];
    }
    //  $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE pgroupid = ? LIMIT 1");
    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE mgroupid = ? LIMIT 1");
    $check_config->execute([$mgroupid]);
    $check_config_query = $check_config->fetch();
    //  var_dump($pgroupid);
    //  exit();


    foreach ($patch_id as $item) {
        $check_action_query = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchActions WHERE pgroupid = ? and mgroupid=? and pconfigid=? and	patchid=?	LIMIT 1");
        $check_action_query->execute([$pgroupid, $mgroupid, $check_config_query['pconfigid'], $item]);
        $check_action_query = $check_action_query->fetch();
        if ($check_action_query['id'] == '') {
            $query = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "softinst.PatchActions set pgroupid =?, mgroupid=?, pconfigid=?, patchid=?, action=?, lastupdate=?");
            $query->execute([$pgroupid, $mgroupid, $check_config_query['pconfigid'], $item, 1, $date]);
        } else {
            $query = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchActions set action=?, lastupdate=? where pgroupid =? and mgroupid=? and pconfigid=? and patchid=?");
            $query->execute([1, $date, $pgroupid, $mgroupid, $check_config_query['pconfigid'], $item]);
        }
    }

    $criticalpatch = MUM_GetCriticalpatch('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
    $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
    $total = safe_count($patch_split);
    $total_split = safe_count($pgroup_id);
    for ($i = 0; $i < $total; $i++) {
        if (in_array($patch_split[$i], $approvepatch)) {
            //====delete from approve=======
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        } else if (in_array($patch_split[$i], $criticalpatch)) {
            //====delete from critical=======
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        } else if (in_array($patch_split[$i], $removepatch)) {
            //====delete from removed=======
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        } else {
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        }
        //        }
    }
}

function getapprovepatchData()
{
    $db = pdo_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $censusId = $_SESSION['rcensusId'];

    if ($searchType == 'ServiceTag') {

        if ($_SESSION["rparentName"] == 'All') {

            $lableDiaplay = $_SESSION['searchValue'];
        } else {

            $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        }
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }

    $dataScope = UTIL_GetSiteScope($db, $lableDiaplay, $searchType);
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    switch ($searchType) {
        case 'Sites':
            $mumpatchlist = PATCH_GetPatchSitesList($key, $db, $dataScope);
            break;
        case 'Groups':
            $mumpatchlist = PATCH_GetPatchGroupList($key, $db, $machines);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $mumpatchlist = PATCH_GetPatchMachineList($key, $db, $censusId);
            break;
        default:
            break;
    }
    $result = $mumpatchlist;
    $approvepgupid = PATCH_ApprovePatchId('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $totalRecords = safe_count($result);
    if ($totalRecords > 0) {
        foreach ($approvepatch as $k => $v) {
            foreach ($result as $key => $row) {
                if ($row['patchid'] == $v) {
                    $patch_id = $row['patchid'];
                    $astatus = '<b>Approved</b>';
                    $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $approvepgupid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
                    $patchname = '<p class="ellipsis" title="' . utf8_encode($row['title']) . '">' . utf8_encode($row['title']) . '</p>';
                    $astatus = $astatus;
                    $count = PATCH_GetPatcheCount('', $searchValue, $searchType, $row['patchid'], $db);
                    $types = PATCH_GetPatchType($row['type']);
                    $status = '<a href="#" data-toggle="modal" style="color:#5882FA;" data-bs-target="#patch_status_popup" onclick="patchStatus(\'' . $row["patchid"] . '\',\'' . $patchType . '\')">Status</a>';
                    $size = $row['size'];
                    if (empty($size) || ($size == 'NAN')) {
                        $Patchsize = '-';
                    } else {
                        $Patchsize = formatBytes($row['size']);
                    }
                    if ($types == 'Undefined') {
                        $types = 'Undefined';
                    }
                }
            }
            $recordList[] = array($checkBox, $patchname, $Patchsize, $types, $patch_id, $approvepgupid);
        }
    } else {
        $recordList = array();
    }
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    $auditRes = create_auditLog('Patch Management', 'Approve Patch', 'Success', NULL, $gpname);

    echo json_encode($recordList);
}

function getCriticalUpdatepatchData()
{

    $db = pdo_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }

    $dataScope = UTIL_GetSiteScope($db, $lableDiaplay, $searchType);
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    switch ($searchType) {
        case 'Sites':
            $mumpatchlist = PATCH_GetPatchSitesList($key, $db, $dataScope);
            break;
        case 'Groups':
            $mumpatchlist = PATCH_GetPatchGroupList($key, $db, $machines);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $mumpatchlist = PATCH_GetPatchMachineList($key, $db, $censusId);
            break;
        default:
            break;
    }

    foreach ($mumpatchlist as $key => $value) {
        $patchids .= "" . $value['patchid'] . " ";
    }
    $result = $mumpatchlist;
    $approvepgupid = PATCH_ApprovePatchId('', $lableDiaplay, $searchType, $db);
    $criticalpatchid = MUM_GetCriticalpatch('', $lableDiaplay, $searchType, $db);
    $declinepgupid = PATCH_DeclinePatchId('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
    $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
    $totalRecords = safe_count($result);
    if ($totalRecords > 0) {
        foreach ($result as $key => $row) {

            if (in_array($row['patchid'], $approvepatch)) {
                $patch_id = $row['patchid'];
                $astatus = 'Approved';
                $checkBox = '<input type="hidden" id="hidden_pgroupid" value="' . $patch_id . '">'
                    . '<input type="hidden" id="selected_appr_patchname" value="' . utf8_encode($row['title']) . '">'
                    . '<input type="hidden" id="hidden_approvedgrpid" value="' . $approvepgupid . '">';
            } else if (in_array($row['patchid'], $declinepatch)) {
                $patch_id = $row['patchid'];
                $astatus = 'Declined';
                $checkBox = '';
            } else if (in_array($row['patchid'], $removepatch)) {
                $patch_id = $row['patchid'];
                $astatus = 'Removed';
                $checkBox = '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $criticalpatchid . '"><span class="form-check-sign"><span class="check"></span></span></label></div>';
            } else if (in_array($row['patchid'], $criticalpatch)) {
                $patch_id = $row['patchid'];
                $astatus = 'Critical';
                $checkBox = '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $criticalpatchid . '"><span class="form-check-sign"><span class="check"></span></span></label></div>';
            } else {
                $patch_id = $row['patchid'];
                $checkBox = $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $criticalpatchid . '" type="checkbox" value="' . $patch_id . '"><span class="form-check-sign"></span></label></div>';
                $astatus = getPatch_status($patch_id, $db);
            }
            $patchname = '<p class="ellipsis" title="' . $row['title'] . '">' . $row['title'] . '</p>';
            $count = PATCH_GetPatcheCount('', $searchValue, $searchType, $row['patchid'], $db);
            $types = PATCH_GetPatchType($row['type']);
            $status = '<a href="#" data-toggle="modal" style="color:#5882FA;" data-bs-target="#patch_status_popup" onclick="patchStatus_NewUI(\'' . $patch_id . '\',\'' . $patchType . '\')">Status</a>';
            $size = $row['size'];
            if (empty($size) || ($size == 'NAN')) {
                $Patchsize = '-';
            } else {
                $Patchsize = formatBytes($row['size']);
            }
            if ($types == 'Undefined') {
                $types = '-';
            }
            if ($types == 'Critical') {
                $recordList[] = array($checkBox, $astatus, $patchname, $status, $types);
            }
        }
    } else {
        $recordList = array();
    }
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    $auditRes = create_auditLog('Patch Management', 'Install Crtical Update', 'Success', NULL, $gpname);


    echo json_encode($recordList);
}

function getremovepatchData()
{
    $db = pdo_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $censusId = $_SESSION['rcensusId'];

    if ($searchType == 'ServiceTag') {

        if ($_SESSION["rparentName"] == 'All') {

            $lableDiaplay = $_SESSION['searchValue'];
        } else {

            $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        }
    } else {
        $lableDiaplay = $_SESSION['searchValue'];
    }

    $dataScope = UTIL_GetSiteScope($db, $lableDiaplay, $searchType);
    $machines = DASH_GetGroupsMachines($key, $db, $dataScope);
    switch ($searchType) {
        case 'Sites':
            $mumpatchlist = PATCH_GetPatchSList($key, $db, $dataScope);
            break;
        case 'Groups':
            $mumpatchlist = PATCH_GetPatchGroupList($key, $db, $machines);
            break;
        case 'ServiceTag':
            $censusId = $_SESSION['rcensusId'];
            $mumpatchlist = PATCH_GetPatchMachineList($key, $db, $censusId);
            break;
        default:
            break;
    }

    $result = $mumpatchlist;
    foreach ($mumpatchlist as $key => $value) {
        $patchids .= "" . $value['patchid'] . " ";
    }

    $removepgupid = PATCH_RemovePatchId('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
    $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
    $totalRecords = safe_count($result);
    if ($totalRecords > 0) {
        foreach ($result as $key => $row) {

            if (in_array($row['patchid'], $approvepatch)) {
                $patch_id = $row['patchid'];
                $astatus = '<b>Approved</b>';
                $checkBox = '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $removepgupid . '"><span class="form-check-sign"></span></label></div>';
            } else if (in_array($row['patchid'], $declinepatch)) {
                $patch_id = $row['patchid'];
                $astatus = '<b>Declined</b>';
                $checkBox = '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $removepgupid . '"><span class="form-check-sign"></span></label></div>';
            } else {
                $patch_id = $row['patchid'];
                $checkBox = '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input actionchkptch" name="' . $patch_id . '" id="' . $removepgupid . '"><span class="form-check-sign"></span></label></div>';
                $astatus = getPatch_status($patch_id, $db);
            }

            $patchname = '<p class="ellipsis" style="white-space: pre-wrap;" title="' . $row['title'] . '">' . $row['title'] . '</p>';
            $astatus = $astatus;
            $count = PATCH_GetPatcheCount('', $searchValue, $searchType, $row['patchid'], $db);
            $types = PATCH_GetPatchType($row['type']);
            $status = '<a href="#" data-toggle="modal" style="color:#5882FA;" data-bs-target="#patch_status_popup" onclick="patchStatus(\'' . $row["patchid"] . '\')">Status</a>';
            $size = $row['size'];
            if (($row['date'] == '0') || ($row['date'] == 0)) {
                $date = '-';
            } else {
                $date = date('m/d/Y', $row['date']);
            }
            if (empty($size) || ($size == 'NAN')) {
                $Patchsize = '-';
            } else {
                $Patchsize = formatBytes($row['size']);
            }

            if ($types == 'Undefined') {
                $types = '-';
            }
            $recordList[] = array($checkBox, $patchname, $astatus, $types, $date, $Patchsize,  $patch_id, $removepgupid);
        }
    } else {
        $recordList = array();
    }

    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    $auditRes = create_auditLog('Patch Management', 'Remove Patch Data', 'Success', NULL, $gpname);

    echo json_encode($recordList);
}

/*function Remove_patch() {

    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $db = pdo_connect();

    $patch_id = url::requestToAny('check');
     $patch_split = $patch_id;
    $pgroup_spl = url::requestToAny('pgroupid');
    $pgroup_id = $pgroup_spl;
    $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
    $lableDiaplaySite = $_SESSION["rparentName"];
    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
    } else if ($searchType == 'Sites') {
        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
    } else {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM core.MachineGroups WHERE mgroupid = ? LIMIT 1");
        $sql->execute([$searchValue]);
    }
    $query = $sql->fetch();
    $mgroupid = $query['mgroupid'];
    $check_config = $db->prepare("SELECT pgroupid FROM ".$GLOBALS['PREFIX']."softinst.PatchConfig WHERE pgroupid = ?  LIMIT 1");
    $check_config->execute([$pgroup_id[0]]);
    $check_config_query = $check_config->fetch();
    $pgroup_id1 = $check_config_query['pgroupid'];
    if ($pgroup_id1 == '') {
        $date = time();
        $insert = $db->prepare("insert into ".$GLOBALS['PREFIX']."softinst.PatchConfig set mgroupid = ?,pgroupid = ?,lastupdate = ?,installation = 5,preventshutdown = 0,
            reminduser = 0,configtype = 0,scheddelay = 0,schedminute = 60,schedhour = 24,schedday = 0,schedmonth = 0,schedweek = 7,schedrandom = 0,
            schedtype = 1,notifydelay = 0,notifyminute = 60,notifyhour = 24, notifyday = 0, notifymonth = 0, notifyweek = 7,notifyrandom = 0,
            notifytype = 1,notifyfail = 0, notifyadvance = 0, notifyschedule = 0,notifyadvancetime = 900,notifytext = '', wpgroupid = ?");
        $insert->execute([$mgroupid,$pgroup_id[0],$date,$pgroup_id[0]]);
        $check = $db->lastInsertId();
    }
    $total = safe_count($patch_split);
    $total_split = safe_count($pgroup_id);
    for ($i = 0; $i < $total; $i++) {
        for ($j = 0; $j < $total_split; $j++) {
            if (in_array($patch_split[$i], $app_arr) || in_array($patch_split[$i], $decl_arr) || in_array($patch_split[$i], $critic_arr) ) {
                $prgoupidsql = $db->prepare("select pgroupid from ".$GLOBALS['PREFIX']."softinst.PatchGroupMap where patchid =?");
                $prgoupidsql->execute([$patch_split[$i]]);
                $pgroupid = $prgoupidsql->fetchAll();
                $parray = array();
                foreach($pgroupid as $values){
                    $final_values = $values['pgroupid'];
                    array_push($parray,$final_values);
                }
                $in = str_repeat('?,', safe_count($parray) - 1) . '?';
                // $delete1 = $db->prepare("DELETE FROM ".$GLOBALS['PREFIX']."softinst.PatchGroups where pgroupid in ($in)");
                // $delete1->execute($parray);
                $delete2 = $db->prepare("DELETE FROM ".$GLOBALS['PREFIX']."softinst.Patches where patchid = ?");
                $delete2->execute([$patch_split[$i]]);
                $delete = $db->prepare("DELETE FROM ".$GLOBALS['PREFIX']."softinst.PatchGroupMap WHERE patchid = ?");
                $delete->execute([$patch_split[$i]]);
                $res = $delete->rowCount();
            } else {
                $prgoupidsql = $db->prepare("select pgroupid from ".$GLOBALS['PREFIX']."softinst.PatchGroupMap where patchid =?");
                $prgoupidsql->execute([$patch_split[$i]]);
                $pgroupid = $prgoupidsql->fetchAll();
                $parray = array();
                foreach($pgroupid as $values){
                    $final_values = $values['pgroupid'];
                    array_push($parray,$final_values);
                }
                $in = str_repeat('?,', safe_count($parray) - 1) . '?';
                // $delete1 = $db->prepare("DELETE FROM ".$GLOBALS['PREFIX']."softinst.PatchGroups where pgroupid in ($in)");
                // $delete1->execute($parray);
                $delete2 = $db->prepare("DELETE FROM ".$GLOBALS['PREFIX']."softinst.Patches where patchid = ?");
                $delete2->execute([$patch_split[$i]]);
                $delete = $db->prepare("DELETE FROM ".$GLOBALS['PREFIX']."softinst.PatchGroupMap WHERE patchid = ?");
                $delete->execute([$patch_split[$i]]);
                $res = $delete->rowCount();
            }
        }
    }
    if($res){
        echo "Success";
    }else{
        echo "Failed";
    }
}*/

function Remove_patch()
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $db = pdo_connect();
    $patchidcheckArr = array();
    $patchidArr = array();
    $patch_id = url::requestToAny('check');

    foreach ($patch_id as $key => $val) {
        if (is_numeric($val)) {
            $patchidcheckArr[] = $val;
        }
    }
    $patch_split = $patchidcheckArr;

    $pgroup_spl = url::requestToAny('pgroupid');

    foreach ($pgroup_spl as $key => $val) {
        if (is_numeric($val)) {
            $patchidArr[] = $val;
        }
    }
    $pgroup_id = $patchidArr;
    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);

        $valm = 'Wiz_REMV_PG ' . $lableDiaplay;
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute([$valm]);
    } else if ($searchType == 'Sites') {

        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute(['Wiz_REMV_PG ' . $lableDiaplay]);
    } else {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$searchValue]);
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute(['Wiz_REMV_PG ' . $searchValue]);
    }
    $query = $sql->fetch();
    $mgroupid = $query['mgroupid'];
    $date = time();
    $queryp = $sqlpgrpid->fetch();
    $pgroup_id = $queryp['pgroupid'];

    if (($searchType == 'Sites' || $searchType == 'ServiceTag') && !$pgroup_id) {
        if ($searchType == "Sites") {
            $category = 3;
        } else {
            $category = 9;
        }
        $sql_insert_group_machine = "insert into " . $GLOBALS['PREFIX'] . "softinst.PatchGroups(pcategoryid,name,global,human,style,created) VALUES (?,?,?,?,?,?)";
        $data_insert = time();
        $name_group = "Wiz_REMV_PG " . $lableDiaplay;
        NanoDB::insert($sql_insert_group_machine, [$category, $name_group, 1, 0, 2, $data_insert]);
        $pgroupId = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=?", null, [$name_group]);
        $pgroupId = $pgroupId['pgroupid'];
    }

    if ($searchType == 'Sites') {
        $patch_group_name = "All";
        $sqlpgrpid = $db->prepare("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=? LIMIT 1");
        $sqlpgrpid->execute([$patch_group_name]);
        $queryp = $sqlpgrpid->fetch();
        $pgroupId = $queryp['pgroupid'];
    }
    if ($searchType == 'ServiceTag' && $pgroupId == "") {
        $name_group = "Wiz_REMV_PG " . $lableDiaplay;
        $pgroupId = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=?", null, [$name_group]);
        $pgroupId = $pgroupId['pgroupid'];
        if ($pgroupId == '') {
            $sql_insert_group_machine = "insert into " . $GLOBALS['PREFIX'] . "softinst.PatchGroups(pcategoryid,name,global,human,style,created) VALUES (?,?,?,?,?,?)";
            $data_insert = time();
            NanoDB::insert($sql_insert_group_machine, [9, $name_group, 1, 0, 2, $data_insert]);
            $pgroupId = NanoDB::find_one("SELECT pgroupid FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroups WHERE name=?", null, [$name_group]);
            $pgroupId = $pgroupId['pgroupid'];
        }
    }

    //    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE pgroupid = ? and mgroupid=? LIMIT 1");
    $check_config = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE mgroupid=? LIMIT 1");
    $check_config->execute([$mgroupid]);
    $check_config_query = $check_config->fetch();

    foreach ($patch_id as $item) {
        $check_action_query = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchActions WHERE pgroupid = ? and mgroupid=? and pconfigid=? and	patchid=?	LIMIT 1");
        $check_action_query->execute([$pgroupId, $mgroupid, $check_config_query['pconfigid'], $item]);
        $check_action_query = $check_action_query->fetch();
        if ($check_action_query['id'] == '') {
            $query = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "softinst.PatchActions set pgroupid =?, mgroupid=?, pconfigid=?, patchid=?, action=?, lastupdate=?");
            $query->execute([$pgroupId, $mgroupid, $check_config_query['pconfigid'], $item, 3, $date]);
        } else {
            $query = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchActions set action=?, lastupdate=? where pgroupid =? and mgroupid=? and pconfigid=? and patchid=?");
            $query->execute([3, $date, $pgroupId, $mgroupid, $check_config_query['pconfigid'], $item]);
        }
    }


    $criticalpatch = MUM_GetCriticalpatch('', $lableDiaplay, $searchType, $db);
    $approvepatch = PATCH_GetApprovePatch('', $lableDiaplay, $searchType, $db);
    $declinepatch = PATCH_GetDeclinePatch('', $lableDiaplay, $searchType, $db);
    $removepatch = PATCH_GetRemovepatch('', $lableDiaplay, $searchType, $db);
    $total = safe_count($patch_split);
    $total_split = safe_count($pgroup_id);
    for ($i = 0; $i < $total; $i++) {
        if (in_array($patch_split[$i], $approvepatch)) {
            //====delete from approve=======
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        } else if (in_array($patch_split[$i], $criticalpatch)) {
            //====delete from critical=======
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        } else if (in_array($patch_split[$i], $removepatch)) {
            //====delete from removed=======
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        } else {
            $delete = $db->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap WHERE patchid =?");
            $delete->execute([$patch_split[$i]]);
            $$deleteres = $db->lastInsertId();
            $ins_patchGroupMap = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`PatchGroupMap` (`pgroupid`,`patchid`) VALUES (?,?)");
            $ins_patchGroupMap->execute([$check_config_query['pgroupid'], $patch_split[$i]]);
            $ins_patchGroupMapres = $db->lastInsertId();
        }
    }
}

function PatchExportDetails()
{
    $titleArray = array();
    $dataArray = array();
    $finalArray = array();
    $sfinalArray = array();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="patchDetails.csv"');
    $result = getallpatchData('patch_export');
    $columnArray = safe_array_keys($result[safe_array_keys($result)[0]]);
    foreach ($columnArray as $key => $eachColumns) {
        if ($eachColumns != '') {
            array_push($titleArray, $eachColumns);
        }
    }
    $finalArray[0] = $titleArray;
    foreach ($result as $key => $eachAssoc) {
        array_push($finalArray, $eachAssoc);
    }
    ob_clean();
    $fp = fopen('php://output', 'wb');
    foreach ($finalArray as $line) {
        fputcsv($fp, $line, ',');
    }
    $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : NULL;
    $auditRes = create_auditLog('Patch Management', 'Export Patch Details', 'Success', NULL, $gpname);
    fclose($fp);
}

function update_MachConfig()
{
    $db = pdo_connect();
    $selectedSearchType = url::requestToAny('SelectedVal');
    $server = url::requestToAny('ServerSelected');
    $site = url::requestToAny('SiteName');
    $susurl = url::requestToAny('url');

    if ($selectedSearchType == '1') {
        $url = '';
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups  WHERE NAME  = 'All'");
        $sql->execute();
        $sqlres = $sql->fetch();
    } else {
        $url = $susurl;
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups  WHERE NAME  = ?");
        $sql->execute([$site]);
        $sqlres = $sql->fetch();
    }
    $mgroupid = $sqlres['mgroupid'];
    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.WUConfig where mgroupid = ?");
    $sql->execute([$mgroupid]);
    $Result = $sql->fetch();

    $checkExisting = safe_count($Result);
    if ($checkExisting == 0) {
        $sqlUpdate = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "softinst.`WUConfig` (`mgroupid`, `management`, `installday`, `installhour`, `newpatches`, `patchsource`, `serverurl`, `propagate`, `lastupdate`, `updatecache`, `cacheseconds`, `restart`, `chain`, `chainseconds`) "
            . "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $sqlUpdate->execute([$mgroupid, '2', '1', '3', '1', $server, $url, '2', '1568639759', '2', '1209600', '2', '1', '7200']);
        $res = $db->lastInsertId();
    } else {
        $sqlUpdate = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.WUConfig set patchsource = ?,serverurl=? where mgroupid = ?");
        $sqlUpdate->execute([$server, $url, $mgroupid]);
        $res = $db->lastInsertId();
    }
    if ($res) {
        echo "Success";
    } else {
        echo "Failed";
    }
}

function getapprdefaultconfiguration()
{
    $db = pdo_connect();
    $name = url::requestToAny('name');
    $pgroupid = url::requestToAny('pgroupid');
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $patch_arr = array();
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($searchType == 'Sites') {
            if ($searchValue == 'All') {

                $sitesDisply = UTIL_GetSiteScope($db, $searchValue, $searchType);
                $res = safe_array_keys($sitesDisply);
                foreach ($res as $row) {
                    $siteList .= "Wiz_APPR_PG " . $sitesDisply[$row] . ",";
                }
                $lableDisply = rtrim($siteList, ',');
                $sql_p = MUM_AllPatchUsed('', $lableDisply, $db, $pgroupid);
            } else {
                $lableDisply = "Wiz_APPR_PG " . $searchValue;
                $sql_p = MUM_AllPatchUsed('', $lableDisply, $db, $pgroupid);
            }
        } else if ($searchType == 'ServiceTag') {

            $lableDisply = "Wiz_APPR_PG " . $searchValue;
            $sql_p = MUM_AllPatchUsed('', $lableDisply, $db, $pgroupid);
        } else if ($searchType == 'Groups') {

            $groupID = UTIL_GetSiteScope($db, $searchValue, $searchType);
            $groupMachine = DASH_GetGroupsMachines($key, $db, $groupID);
            $res = safe_array_keys($groupMachine);
            foreach ($res as $row) {
                $groupidList .= "Wiz_APPR_PG " . $groupMachine[$row] . ",";
            }
            $groupMachines = rtrim($groupidList, ',');
            if ($searchValue == 'All') {
                $sql_p = MUM_AllPatchUsed('', $groupMachines, $db, $pgroupid);
            } else {
                $name = PATCH_GroupName($db, $searchValue);
                $groupname = "Wiz_APPR_PG " . $name;
                $sql_p = MUM_AllPatchUsed('', $groupname, $db, $pgroupid);
            }
        }
        $query_p = $sql_p->fetch();
    } else {
        echo "Your key has been expired";
    }
    $pgroupid =  $query_p['pgroupid'];

    $configData = array();
    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where pgroupid =? limit 1");
    $sql->execute([$pgroupid]);
    $row = $sql->fetch();

    $count = safe_count($row);
    if (($count != 0) || ($count > 0)) {
        $configData['pconfigid'] = $row['pconfigid'];
        $configData['pgroupid'] = $row['pgroupid'];
        $configData['mgroupid'] = $row['mgroupid'];
        $configData['installation'] = $row['installation'];
        $configData['notifyadvance'] = $row['notifyadvance'];
        $configData['notifyadvancetime'] = $row['notifyadvancetime'];
        $configData['scheddelay'] = $row['scheddelay'];
        $configData['schedminute'] = $row['schedminute'];
        $configData['schedhour'] = $row['schedhour'];
        $configData['schedday'] = $row['schedday'];
        $configData['schedmonth'] = $row['schedmonth'];
        $configData['schedweek'] = $row['schedweek'];
        $configData['schedrandom'] = $row['schedrandom'];
        $configData['schedtype'] = $row['schedtype'];
        $configData['notifydelay'] = $row['notifydelay'];
        $configData['notifyminute'] = $row['notifyminute'];
        $configData['notifyhour'] = $row['notifyhour'];
        $configData['notifyday'] = $row['notifyday'];
        $configData['notifymonth'] = $row['notifymonth'];
        $configData['notifyweek'] = $row['notifyweek'];
        $configData['notifyrandom'] = $row['notifyrandom'];
        $configData['notifytype'] = $row['notifytype'];
        $configData['notifyfail'] = $row['notifyfail'];
        $configData['notifytext'] = $row['notifytext'];
        $configData['configtype'] = $row['configtype'];
        $configData['notifyrandom'] = $row['notifyrandom'];

        $configData['data'] = 'data';
    } else {
        $configData['data'] = 'nodata';
    }

    $configDatavals = json_encode($configData, true);
    echo $configDatavals;
    exit;
}


function get_patchStatusNewUIFunc()
{
    $db = pdo_connect();
    $patchid = url::requestToAny('pid');
    $patchtype = url::requestToAny('type');
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $statusID = '';
    $patchstatus = PATCH_GetPatchStatus('', $patchid, $searchValue, $searchType, $db, $statusID);
    $totalRecords = safe_count($patchstatus);

    if ($totalRecords > 0) {
        foreach ($patchstatus as $key => $row) {
            $detected = ($row['detected'] != 0) ? date("m/d/Y", $row['detected']) : 'Not detected';
            $downloaded = ($row['lastdownload'] != 0) ? date("m/d/Y", $row['lastdownload']) : 'Not downloaded';
            $install = ($row['lastinstall'] != 0) ? date("m/d/Y", $row['lastinstall']) : 'Not installed';
            $sitename = '<p class="ellipsis" title="' . UTIL_GetTrimmedGroupName($row['site']) . '">' . UTIL_GetTrimmedGroupName($row['site']) . '</p>';
            $hostname = '<p class="ellipsis" title="' . $row['host'] . '">' . $row['host'] . '</p>';
            $patchname = '<p class="ellipsis"  style="white-space: pre-wrap;" title="' . $row['title'] . '">' . $row['title'] . '</p>';
            $patchsta = getpatchstatus($row['status']);
            $detected = '<p class="ellipsis" title="' . $detected . '">' . $detected . '</p>';
            $lastdownload = '<p class="ellipsis" title="' . $downloaded . '">' . $downloaded . '</p>';
            $installdata = '<p class="ellipsis" title="' . $install . '">' . $install . '</p>';
            $error = '<p class="ellipsis" title="' . $row['lasterror'] . '">' . $row['lasterror'] . '</p>';

            $recordList[] = array($hostname, $patchsta, $detected, $lastdownload, $installdata, $error);
        }
    } else {
        $recordList = array();
    }

    echo json_encode($recordList);
}

function Kbs_patchdetailList()
{

    $db = pdo_connect();
    $searchValue = $_SESSION['searchValue'];
    $searchType  = $_SESSION['searchType'];
    $rparentName = $_SESSION['rparentName'];
    $pid = url::requestToAny('patchid');
    $pid = url::requestToAny('patchid');

    if ($searchType == 'Sites' || $searchType == 'Site') {
        $sql = $db->prepare("select P.serverfile,P.patchid,C.host,C.site,P.title,PS.status,PS.lastdownload,PS.lastinstall,PS.detected from "
            . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND "
            . "C.id = PS.id AND PS.patchid = ? AND C.site = ? limit 1");
        $sql->execute([$pid, $searchValue]);
    } else if ($searchType == 'Service Tag' || $searchType == 'Host Name' || $searchType == 'ServiceTag') {
        $pname = PATCH_GetParentName($searchValue);
        $sql = $db->prepare("select P.serverfile,P.patchid,C.host,C.site,P.title,PS.status,PS.lastdownload,PS.lastinstall,PS.detected from "
            . "" . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS,core.Census C," . $GLOBALS['PREFIX'] . "softinst.Patches P where P.patchid = PS.patchid AND "
            . "C.id = PS.id AND PS.patchid = ? AND C.host = ? and C.site = ? limit 1");
        $sql->execute([$pid, $searchValue, $pname]);
    } else {
        $machineValueArr = array();
        $machine_sql = $db->prepare("select c.host host from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mgm on "
            . "mg.mgroupuniq = mgm.mgroupuniq join " . $GLOBALS['PREFIX'] . "core.Census c on c.censusuniq = mgm.censusuniq where "
            . "mg.name = ? order by name");
        $machine_sql->execute([$rparentName]);
        $machine_query = $machine_sql->fetchAll();
        foreach ($machine_query as $value) {
            $machineValue .= $value['host'] . ",";
        }
        $machineValueArr = explode(',', $machineValue);
        $sql = $db->prepare("select P.patchid,P.serverfile,C.host,C.site,P.title,PS.status,PS.lastdownload,PS.lastinstall,PS.detected "
            . "from " . $GLOBALS['PREFIX'] . "softinst.PatchStatus PS left join " . $GLOBALS['PREFIX'] . "core.Census C on C.id = PS.id left join " . $GLOBALS['PREFIX'] . "softinst.Patches P "
            . "on P.patchid = PS.patchid where PS.patchid = ? group by P.patchid limit 1");
        $sql->execute([$pid]);
    }
    $sqlresult = $sql->fetchAll();
    $totalrecord = safe_count($sqlresult);

    if ($totalrecord > 0) {
        $i = 0;
        $responce = "";
        foreach ($sqlresult as $key => $value) {
            $serverfile = $value['serverfile'];
            $title = $value['title'];

            $recordList[] = array("PatchName" => $title, "ServerFile" => $serverfile);
        }
    } else {
        $recordList = array();
    }
    echo json_encode($recordList);
}

function getHourSelection()
{
    $className = url::requestToAny('type');
    $html .= '<select class="' . $className . '" id="' . $className . '" name="' . $className . '" data-style="btn btn-info" data-size="7">'
        . '<option value="0">00</option>'
        . '<option value="1">01</option>'
        . '<option value="2">02</option>'
        . '<option value="3">03</option>'
        . '<option value="4">04</option>'
        . '<option value="5">05</option>'
        . '<option value="6">06</option>'
        . '<option value="7">07</option>'
        . '<option value="8">08</option>'
        . '<option value="9">09</option>'
        . '<option value="10">10</option>'
        . '<option value="11">11</option>'
        . '<option value="12">12</option>'
        . '<option value="13">13</option>'
        . '<option value="14">14</option>'
        . '<option value="15">15</option>'
        . '<option value="16">16</option>'
        . '<option value="17">17</option>'
        . '<option value="18">18</option>'
        . '<option value="19">19</option>'
        . '<option value="20">20</option>'
        . '<option value="21">21</option>'
        . '<option value="22">22</option>'
        . '<option value="23">23</option>'
        . '<option value="24" selected>Any</option>'
        . '</select>';
    echo $html;
}

function getDateSelection()
{
    $className = url::requestToAny('type');
    $html .=  '<select class="' . $className . '" id="' . $className . '" name="' . $className . '" data-style="btn btn-info" data-size="7">'
        . '<option value="0" selected>Any</option>'
        . '<option value="1">1</option>'
        . '<option value="2">2</option>'
        . '<option value="3">3</option>'
        . '<option value="4">4</option>'
        . '<option value="5">5</option>'
        . '<option value="6">6</option>'
        . '<option value="7">7</option>'
        . '<option value="8">8</option>'
        . '<option value="9">9</option>'
        . '<option value="10">10</option>'
        . '<option value="11">11</option>'
        . '<option value="12">12</option>'
        . '<option value="13">13</option>'
        . '<option value="14">14</option>'
        . '<option value="15">15</option>'
        . '<option value="16">16</option>'
        . '<option value="17">17</option>'
        . '<option value="18">18</option>'
        . '<option value="19">19</option>'
        . '<option value="20">20</option>'
        . '<option value="21">21</option>'
        . '<option value="22">22</option>'
        . '<option value="23">23</option>'
        . '<option value="24">24</option>'
        . '<option value="25">25</option>'
        . '<option value="26">26</option>'
        . '<option value="27">27</option>'
        . '<option value="28">28</option>'
        . '<option value="29">29</option>'
        . '<option value="30">30</option>'
        . '<option value="31">31</option>'
        . '</select>';
    echo $html;
}

function getAllSitesDetails()
{
    $db = pdo_connect();
    $username = $_SESSION['logged_username'];
    $sql1 = $db->prepare("select distinct site  from " . $GLOBALS['PREFIX'] . "core.Census ;");
    $sql1->execute();
    $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);
    $mgroupidArr = array();
    foreach ($sql1Res as $key => $valu) {
        $sitename = $valu['site'];
        $sql = $db->prepare("select mgroupid,name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? ");
        $sql->execute([$sitename]);
        $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);

        $mgroupid = $sqlRes['mgroupid'];
        $siteName = $sqlRes['name'];

        $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "softinst.PatchConfigure where mgroupid = ? ");
        $sql->execute([$mgroupid]);
        $Res = $sql->fetch(PDO::FETCH_ASSOC);

        if (!$Res) {
            $result = $db->prepare("insert ignore into " . $GLOBALS['PREFIX'] . "softinst.PatchConfigure (mgroupid,Site,Configured,ConfiguredBy,ConiguredDate) VALUES (?,?,?,?,?)");
            $result->execute([$mgroupid, $siteName, 'No', '-', 0]);
            $updateResult = $db->lastInsertId();
        }
    }
}

function configureMUM()
{
    $db = pdo_connect();
    $searchType = $_SESSION['searchType'];

    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
    } else if ($searchType == 'Sites') {
        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
    } else {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name =? LIMIT 1");
        $sql->execute([$_SESSION['searchValue']]);
    }
    $query = $sql->fetch();
    $mgrpid = $query['mgroupid'];

    $sername = (url::requestToText('susselurl') == '') ? url::requestToText('susselurl') : '';
    $management = url::issetInRequest('managementSel') ? url::requestToText('managementSel') : '0';
    $manday = url::issetInRequest('showDay') ? url::requestToText('showDay') : '0';
    $manhour = url::issetInRequest('showHour') ? url::requestToText('showHour') : '0';
    $newup = url::issetInRequest('selUpdate') ? url::requestToText('selUpdate') : '1';
    $patchSource = url::issetInRequest('patchSource') ? url::requestToText('patchSource') : '0';
    $download = url::issetInRequest('selDownUpdate') ? url::requestToText('selDownUpdate') : '0';
    $retpolicy = url::issetInRequest('RetenSel') ? url::requestToText('RetenSel') : '0';
    $retdays = url::issetInRequest('showDays2') ? url::requestToText('showDays2') : '0';
    $retentionday = ($retdays * 86400);
    $restart = url::issetInRequest('restartSel') ? url::requestToText('restartSel') : '0';
    $multiple = url::issetInRequest('multipleInstallSel') ? url::requestToText('multipleInstallSel') : '0';
    $multihour = url::issetInRequest('showHour2') ? url::requestToText('showHour2') : '0';
    $multipleday = ($multihour * 3600);
    mumserverConfigure($patchSource, $sername, $management, $manday, $manhour, $newup, $download, $retpolicy, $retentionday, $restart, $multiple, $multipleday, $db, $mgrpid);
    updatePatchConfig($mgrpid);
}

function updatePatchConfig($mgrpid)
{
    $db = pdo_connect();
    $username = $_SESSION['user']['username'];
    $time = time();
    $result = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.PatchConfigure set Configured=?,ConfiguredBy=?,ConiguredDate=? where mgroupid = ?");
    $result->execute(['Yes', $username, $time, $mgrpid]);
    $updateResult = $db->lastInsertId();
}

function mumserverConfigure($patchSource, $sername, $management, $manday, $manhour, $newup, $download, $retpolicy, $retentionday, $restart, $multiple, $multipleday, $db, $mgroupid = '')
{
    $sql1 = $db->prepare("SELECT * from " . $GLOBALS['PREFIX'] . "softinst.WUConfig where mgroupid=?");
    $sql1->execute([$mgroupid]);
    $sql1Res = $sql1->fetch();
    $lastupdate = time();
    if ($sql1Res) {
        $result = $db->prepare("update " . $GLOBALS['PREFIX'] . "softinst.WUConfig set patchsource = ?,serverurl = ?,management = ? ,installday = ?,installhour = ?,"
            . "newpatches = ? , propagate = ? , updatecache = ? ,cacheseconds = ?,restart = ? ,"
            . " chain = ?, chainseconds = ? ,lastupdate = ? where mgroupid = ?");
        $result->execute([$patchSource, $sername, $management, $manday, $manhour, $newup, $download, $retpolicy, $retentionday, $restart, $multiple, $multipleday, $lastupdate, $mgroupid]);
    } else {
        $result = $db->prepare("insert into " . $GLOBALS['PREFIX'] . "softinst.WUConfig (patchsource,serverurl,management,installday,installhour,newpatches,propagate,updatecache,cacheseconds,restart,chain,chainseconds,lastupdate,mgroupid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $params = array_merge([$patchSource, $sername, $management, $manday, $manhour, $newup, $download, $retpolicy, $retentionday, $restart, $multiple, $multipleday, $lastupdate, $mgroupid]);
        $result->execute([$patchSource, $sername, $management, $manday, $manhour, $newup, $download, $retpolicy, $retentionday, $restart, $multiple, $multipleday, $lastupdate, $mgroupid]);
    }
}


function getConfigUpdateDetails()
{
    $db = pdo_connect();
    $rparentName = $_SESSION['rparentName'];
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    if ($searchType == 'Sites') {
        $searchValueArr = explode(',', $searchValue);
        $in3  = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
        $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in3)");
        $sql->execute($searchValueArr);
        $result = $sql->fetchAll();
    } else if ($searchType == 'Groups') {
        $searchValueArr = explode(',', $searchValue);
        $in4  = str_repeat('?,', safe_count($searchValueArr) - 1) . '?';
        $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in4)");
        $sql->execute($searchValueArr);
        $result = $sql->fetchAll();
    } else if ($searchType == 'ServiceTag') {
        $sql = $db->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ?");
        $sql->execute([$rparentName . ':' . $searchValue]);
        $result = $sql->fetchAll();
    }
    $mroupidArr = array();
    if (safe_count($result) > 0) {
        foreach ($result as $value) {
            $mroupid = $value['mgroupid'];
        }
        array_push($mroupidArr, $mroupid);
    } else {
        $mgrpid = "''";
        $mroupidArr = array();
    }
    $MgrpIn = str_repeat('?,', safe_count($mroupidArr) - 1) . '?';
    $sql1 = $db->prepare("select * from  " . $GLOBALS['PREFIX'] . "softinst.WUConfig where mgroupid in ($MgrpIn) ");
    $sql1->execute($mroupidArr);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $sqlArr = array();
    if (!$sql1Res) {
        $sqlArr['management'] = '1';
        $sqlArr['installday'] = '1';
        $sqlArr['installhour'] = '3';
        $sqlArr['newpatches'] = '1';
        $sqlArr['patchsource'] = '1';
        $sqlArr['serverurl'] = '';
        $sqlArr['propagate'] = '2';
        $sqlArr['updatecache'] = '2';
        $sqlArr['cacheseconds'] = '1209600';
        $sqlArr['restart'] = '2';
        $sqlArr['chain'] = '1';
        $sqlArr['chainseconds'] = '7200';
        echo json_encode($sqlArr);
    } else {
        echo json_encode($sql1Res);
    }
}

function GetConfigDetails()
{
    $searchValue = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];
    $db = pdo_connect();

    if ($searchType == 'ServiceTag') {
        $lableDiaplay = $_SESSION["rparentName"] . ':' . $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
    } else if ($searchType == 'Sites') {
        $lableDiaplay = $_SESSION['searchValue'];
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? LIMIT 1");
        $sql->execute([$lableDiaplay]);
    } else {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE mgroupid =? LIMIT 1");
        $sql->execute([$searchValue]);
    }
    $query = $sql->fetch();
    $mgroupid = $query['mgroupid'];

    $sql1 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfigure WHERE mgroupid=?");
    $sql1->execute([$mgroupid]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);

    $configuredOn = $sql1Res['ConiguredDate'] ? $sql1Res['ConiguredDate'] : 'NA';
    if ($configuredOn != 'NA') {
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $configuredOn = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $configuredOn, "m/d/Y g:i:s A");
        } else {
            $configuredOn = date("m/d/Y g:i:s A", $configuredOn);
        }
    } else {
        $configuredOn = 'NA';
    }
    $configuredBy = $sql1Res['ConfiguredBy'] ? $sql1Res['ConfiguredBy'] : 'NA';
    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "softinst.PatchConfig WHERE mgroupid=?");
    $sql2->execute([$mgroupid]);
    $sql2Res = $sql2->fetch(PDO::FETCH_ASSOC);

    $patchleftdata = PATCH_GetDetails($key, $db);
    $update = PATCH_GetInstallValue($db, $name);
    $chain = $patchleftdata['chainseconds'] / 3600;
    $cache = $patchleftdata['cacheseconds'] / 86400;

    // $updatemethod = $update == 1 ? Manual : Automatic;
    $server = $patchleftdata['serverurl'] ? $patchleftdata['serverurl'] : 'NA';
    $patchSource = $patchleftdata['patchsource'];

    if ($patchSource == '1') {
        $SourceVal = 'Microsoft Update Server';
    } else if ($patchSource == '2') {
        $SourceVal = 'SUS Server :' . $server;
    } else {
        $SourceVal = 'Microsoft Update Server';
    }

    $manage = $patchleftdata['management'];
    $strmanage = '';
    if ($manage == '') {
        $manage = '1';
    }
    switch ($manage) {
        case 1:
            $strmanage = "Disable";
            break;
        case 2:
            $strmanage = "Manage from Server";
            break;
        case 3:
            $strmanage = "User controlled download and install";
            break;
        case 4:
            $strmanage = "Automated download, user controlled install";
            break;
        case 5:
            $strmanage = "Automated download and install";
            break;
        default:
            $strmanage = "Not Configured";
    }


    $newupdate = $patchleftdata['newpatches'] == 1 ? 'Act based on last settings from server' : 'Wait to get current settings from server before taking action';


    $propagate = $patchleftdata['propagate'];
    $downlaod = '';
    switch ($propagate) {
        case 0:
            $downlaod = 'Only download from vendor';
            break;
        case 1:
            $downlaod = 'Only retrieve from local machines';
            break;
        case 2:
            $downlaod = 'Try to retrieve from local machines, then download from vendor if unsuccessful';
            break;
        default:
            $downlaod = "Not Configured";
    }


    $retention = $patchleftdata['updatecache'] == 1 ? 'Do not keep updates on this machine for other machines to use' : 'Keep updates on this machine' . $cache . ' days, for other machines to use';

    $resartpolicy = $patchleftdata['restart'] == 1 ? 'Do not automatically restart when a restart is necessary after an installation.' : 'Automatically restart when a restart is necessary after an installation';

    $chaindata = $patchleftdata['chain'];
    $strchain = '';
    switch ($chaindata) {
        case 1:
            $strchain = 'Repeat install cycle until machine is up to date, but stop after' . $chain . 'hours';
            break;
        case 2:
            $strchain = 'Repeat install cycle until machine is up to date.';
            break;
        case 3:
            $strchain = 'Only do one install cycle';
            break;
        default:
            $strchain = "Not Configured";
    }

    $recordlist = array(
        'configuredOn' => $configuredOn, 'configuredBy' => $configuredBy, 'SourceVal' => $SourceVal, 'management' => $strmanage, 'srvrurl' => $server, 'newupdates' => $newupdate,
        'Download' => $downlaod, 'retention' => $retention, 'restartpolicy' => $resartpolicy, 'multipleinstall' => $strchain
    );
    echo json_encode($recordlist);
}
