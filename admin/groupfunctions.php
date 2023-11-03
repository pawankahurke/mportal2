<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once("../include/common_functions.php");
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-elasticReport.php';
include_once '../lib/l-group.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/l-setTimeZone.php';

global $pdo;
$pdo = pdo_connect();
$function = '';

nhRole::dieIfnoRoles(['addAdvgrp']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'get_UsersList') { //roles: addAdvgrp
    get_UsersList();
} else if (url::postToText('function') === 'get_MachineList') { //roles: addAdvgrp
    get_MachineList();
} else if (url::postToText('function') === 'get_add_groupcsv') { //roles: addAdvgrp
    get_add_groupcsv();
} else if (url::postToText('function') === 'check_GroupEditAccess') { //roles: addAdvgrp
    check_GroupEditAccess();
} else if (url::postToText('function') === 'get_edit_groupname') { //roles: addAdvgrp
    get_edit_groupname();
} else if (url::postToText('function') === 'get_edit_groupcsv') { //roles: addAdvgrp
    get_edit_groupcsv();
} else if (url::postToText('function') === 'get_ManualGroup_Add') { //roles: addAdvgrp
    get_ManualGroup_Add();
} else if (url::postToText('function') === 'get_ManualGroup_Edit') { //roles: addAdvgrp
    get_ManualGroup_Edit();
} else if (url::postToText('function') === 'getSiteList') { //roles: addAdvgrp
    getSiteList();
} else if (url::postToText('function') === 'createAdvanceGrp') { //roles: addAdvgrp
    createAdvanceGrp();
} else if (url::postToText('function') === 'deleteAdvGroup') { //roles: addAdvgrp
    deleteAdvGroup();
} else if (url::postToText('function') === 'adv_groupviewDetail') { //roles: addAdvgrp
    adv_groupviewDetail();
} else if (url::postToText('function') === 'checkAdvGrpEditAccess') { //roles: addAdvgrp
    checkAdvGrpEditAccess();
} else if (url::postToText('function') === 'getEditUsersList') { //roles: addAdvgrp
    getEditUsersList();
} else if (url::postToText('function') === 'updateAdvGrpDetails') { //roles: addAdvgrp
    updateAdvGrpDetails();
} else if (url::postToText('function') === 'editadvgroupValues') { //roles: addAdvgrp
    editadvgroupValues();
} else if (url::postToText('function') === 'refreshAdvGroup') { //roles: addAdvgrp
    refreshAdvGroup();
} else if (url::postToText('function') === 'get_viewadvncdgroups') { //roles: addAdvgrp
    get_viewadvncdgroups();
} else if (url::postToText('function') === 'get_DefaultSiteDetails') { //roles: addAdvgrp
    getDefaultSiteDetails();
} else if (url::postToText('function') === 'addNewSite') { //roles: addAdvgrp
    addNewSite();
} else if (url::postToText('function') === 'get_MachineListEdit') { //roles: addAdvgrp
    get_MachineListEdit();
} else if (url::postToText('function') === 'get_FilterList') { //roles: addAdvgrp
    get_FilterList();
} else if (url::postToText('function') === 'check_EditAccess') { //roles: addAdvgrp
    check_EditAccess();
} else if (url::postToText('function') === 'get_sample_fileDownload') { //roles: addAdvgrp
    get_sample_fileDownload();
} else if (url::getToText('function') === 'get_viewgroupdetailexportList') { //roles: addAdvgrp
    get_viewgroupdetailexportList();
} else if (url::getToText('function') === 'get_view_groupexportList') { //roles: addAdvgrp
    get_view_groupexportList();
} else if (url::getToText('function') === 'get_view_exportGrpList') { //roles: addAdvgrp
    get_view_exportGrpList();
} else if (url::postToText('function') === 'get_FilterListL1') { //roles: addAdvgrp
    get_Filter_ListL1();
} else if (url::postToText('function') === 'get_FilterListL2') { //roles: addAdvgrp
    get_Filter_ListL2();
} else if (url::postToText('function') === 'createAdvanceGrpCensus') { //roles: addAdvgrp
    create_AdvanceGrpCensus();
} else if (url::postToText('function') === 'checkSavedValues') { //roles: addAdvgrp
    check_SavedValues();
}

if (url::getToText('function') === 'get_groupListDelete') { //roles: addAdvgrp
    get_groupListDelete();
}

function check_SavedValues()
{
    $pdo = pdo_connect();
    $grpname = url::postToText('grpname');
    $type = url::postToText('type');

    $q = "SELECT * FROM " . $GLOBALS['PREFIX'] . "asset.assetFilters WHERE grpname = ? and grptype = ? limit 1";
    $userSql = $pdo->prepare($q);
    $userSql->execute([$grpname, $type]);
    $userRes = $userSql->fetch(PDO::FETCH_ASSOC);

    $siteList = $_SESSION['user']['site_list'];
    $savedSites = $userRes['site'];
    $savedSites = explode(',', $userRes['site']);

    $newArr = array();
    $siteOption = '';
    $temp = [];
    $selected = '';
    foreach ($savedSites as $i => $value) {
        $temp[$i] = $value;
    }

    foreach ($siteList as $key => $value) {
        if (!in_array($value, $selected)) {
            $customer = $value;
            if (in_array($value, $temp)) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $siteOption .= "<option value='$customer' $selected>$customer</option>";
        }
    }

    $recordList = array(
        "id" => $userRes['id'],
        "site" => $siteOption,
        "grpname" => $userRes['grpname'],
        "stringVal" => $userRes['stringVal'],
        "dataid" => $userRes['dataid'],
        "str" => $userRes['str'],
        "grptype" => $userRes['grptype'],
        "operator" => $userRes['operator']
    );

    echo json_encode($recordList);
}


function get_viewgroups()
{

    global $pdo;
    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $ch_id = $_SESSION['user']['cId'];
    $grpCategory = 'Wiz_SCOP_MC';

    $groupresult = GRP_ViewGroups($key, $username, $ch_id, $grpCategory, $pdo);
    $totalrecords = safe_count($groupresult);


    if ($totalrecords > 0) {

        foreach ($groupresult as $key => $value) {
            $id = $value['mgroupid'];
            $tempGroupName = UTIL_GetTrimmedGroupName($value['name']);
            $group = utf8_decode($tempGroupName);
            $created = date('m/d/Y H:i A', $value['created']);
            $GroupUsername = $value['username'];
            $machinescount = GRP_GetMachineList($id, $GroupUsername, $pdo);
            $count = $machinescount;
            $boolstring = $value['boolstring'];
            $type = $value['style'] == 2 ? 'Manual' : 'Advance';
            $global = $value['global'] == 1 ? 'Yes' : 'No';

            $recordList[] = array($group, $count, $type, $global, $created, $id, $boolstring);
        }
    } else {

        $recordList = array();
    }

    echo json_encode($recordList);
}



function get_groupviewDetail()
{

    $key = '';
    $pdo = pdo_connect();
    $groupid = url::requestToAny('grpid');

    $machineslist = GRP_ViewGroupDetail($key, $groupid, $pdo);
    $totalRecords = safe_count($machineslist);

    if ($totalRecords > 0) {

        foreach ($machineslist as $key => $value) {

            $recordList[] = array($value['host'], UTIL_GetTrimmedGroupName($value['site']));
        }
    } else {
        $recordList = array();
    }

    echo json_encode($recordList);
}



function get_add_groupcsv()
{
    $key = '';
    $pdo = pdo_connect();
    $Gname = url::postToText('groupname');
    $user_name = $_SESSION["user"]["username"];
    $eid = $_SESSION["user"]["cId"];
    $grpCategory = 'Wiz_SCOP_MC';
    $global = url::postToText('global');
    $userList = url::postToText('userlist');

    $jsonData = GRP_AddGroup($key, $Gname, $user_name, $grpCategory, $pdo, $global, $userList);

    $auditRes = create_auditLog('Group', 'Create', 'Success');
    echo json_encode($jsonData);
}

function get_edit_groupname()
{
    $pdo = pdo_connect();
    $gid = url::postToAny('editgid');
    $count = url::postToAny('count');
    $username = $_SESSION['user']['username'];

    $key = '';
    $editId = array();
    $searchVal = $_SESSION['searchValue'];
    $nameresult = GRP_EditGroupName($key, $gid, $pdo, $count, $searchVal);
    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp "
        . "where m.mgroupuniq = mp.mgroupuniq and m.mgroupid = ? limit 1");
    $sql->execute([$gid]);
    $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    $boolstring = $sqlRes['boolstring'];
    $global = $sqlRes['global'];
    $gname = $sqlRes['name'];
    $g_orgname =  $sqlRes['name'];
    $stylenumber = $sqlRes['style'];

    $siteList = $_SESSION["user"]["site_list"];
    $siteListArr = array();
    foreach ($siteList as $k => $v) {
        array_push($siteListArr, $v);
    }
    $dataArr = ['admin', 'hfn', $username];

    $site_in  = str_repeat('?,', safe_count($siteListArr) - 1) . '?';
    $data_in  = str_repeat('?,', safe_count($dataArr) - 1) . '?';


    $params = array_merge($siteListArr, $dataArr);
    $userSql = "select C.username, U.userid from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U "
        . "where C.customer IN ($site_in) and U.username = C.username and "
        . "C.username != '' and C.username NOT IN ($data_in) "
        . "group by C.username";
    $stmt = $pdo->prepare($userSql);
    $stmt->execute($params);
    $userRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $grpMapSql = $pdo->prepare("select username from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupname = ? and username != ? ");  //and username != ?
    $grpMapSql->execute([$g_orgname, $username]); //$username
    $grpMapRes = $grpMapSql->fetchAll(PDO::FETCH_ASSOC);
    $enabledUsers = [];
    //echo $gname." - ".$username." - ".json_encode($grpMapRes);
    foreach ($grpMapRes as $value) {
        $enabledUsers[] = $value['username'];
    }
    $userListData = '';
    $machid = '';
    if (safe_count($userRes) > 0) {
        foreach ($userRes as $value) {
            $selected = '';
            if (in_array($value['username'], $enabledUsers)) {
                $selected = 'selected';
            }
            $userListData .= '<option value="' . $value['userid'] . '" ' . $selected . '>' . $value['username'] . '</option>';
        }
    } else {
        $userListData = '<option value="">No Users Available</option>';
    }
    foreach ($nameresult as $key => $val) {
        $id = $val['id'];
        if (isset($val['id'])) {
            array_push($editId, $val['id']);
        }
        $machid .= $val['id'] . ",";
    }
    //Get styles
    $styleListData = '';
    $enabledStyle = $stylenumber;
    $grpStyleSql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.group_styles");
    $grpStyleSql->execute();
    $grpStyleRes = $grpStyleSql->fetchAll(PDO::FETCH_ASSOC);
    if (safe_count($grpStyleRes) > 0) {
        foreach ($grpStyleRes as $value) {
            $selected = '';
            if ($value['style_number'] ==  $enabledStyle) {
                $selected = 'selected';
            }
            $styleListData .= '<option value="' . $value['style_number'] . '" ' . $selected . '>' . $value['style_name'] . '</option>';
        }
    } else {
        $styleListData = '<option value="">No Category Available</option>';
    }



    $jsonData = array("gname" => $gname, "option" => $editId, "type" => $boolstring, 'global' => $global, 'machinelist' => $machid, 'userlist' => $userListData, 'stylelist' => $styleListData);

    echo json_encode($jsonData);
}


function get_edit_groupcsv()
{

    $key = '';
    $pdo = pdo_connect();
    $groupid = url::postToText('grpid');
    $grpname = url::postToText('groupname');
    $grpCategory = 'Wiz_SCOP_MC';
    $global = url::postToText('global');
    $userlist = url::postToText('userlist');

    $_SESSION['rparentName'] = $grpname;

    $jsonData = GRP_EditGroup($key, $groupid, $grpname, $grpCategory, $pdo, $global, $userlist);

    $auditRes = create_auditLog('Group', 'Modification', 'Success');
    echo json_encode($jsonData);
}



function get_groupListDelete()
{
    $pdo = pdo_connect();
    $strid = url::requestToText('value');

    $res = checkGroupAccess($strid, 'grpname');
    if (!$res) {
        echo "Permission Denied";
        exit;
    }

    $count = GRP_GroupDelete($strid, $pdo);
    if ($count == 1) {
        $auditRes = create_auditLog('Group', 'Delete', 'Success', $_REQUEST);
        $jsonData = array('msg' => 'success');
        unset($_SESSION['searchType']);
        unset($_SESSION['searchValue']);
        unset($_SESSION['rparentName']);
    } else {
        $auditRes = create_auditLog('Group', 'Delete', 'Failed');
        $jsonData = array('msg' => 'invalid');
    }
    echo json_encode($jsonData);
}



function get_sample_fileDownload()
{

    GRP_SampleFileExport();
}



function get_view_groupexportList()
{

    global $pdo;
    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $ch_id = $_SESSION['user']['cId'];
    $grpCategory = 'Wiz_SCOP_MC';

    GRP_ViewGroupExport($key, $username, $ch_id, $grpCategory, $pdo);
}



function get_viewgroupdetailexportList()
{

    global $pdo;
    $key = '';
    $pdo = pdo_connect();
    $groupid = url::requestToAny('grupid');
    $grpname = url::requestToAny('grpname');
    $parentId = $_SESSION['user']['parentid'];

    $userid = $_SESSION['user']['userid'];
    $parents = getParent($userid);
    // $res1 = checkGroupAccess($groupid, 'grpid');
    // $res2 = checkGroupAccess($grpname, 'grpname');
    // if (!$res1 || !$res2) {
    //     echo "Permission Denied";
    //     exit;
    // }

    if ($parentId != $parents) {
        echo "Permission Denied";
        exit;
    }
    $auditRes = create_auditLog('Group', 'Export', 'Success', $_REQUEST);
    GRP_ViewGroupDetailExport($key, $groupid, $grpname, $pdo);
}

function get_view_exportGrpList()
{
    global $pdo;
    $key = '';
    $pdo = pdo_connect();
    $auditRes = create_auditLog('Group', 'Export', 'Success', $_REQUEST);
    GRP_GroupDetailExport($key, $pdo);
}



function getallviewgroupData()
{

    $key = '';
    global $pdo;
    $groupname = url::requestToAny('gname');
    $groupCategory = 'Wiz_SCOP_MC';
    $groupid = url::requestToAny('grpid');
    $recordList = [];

    $result = GRP_viewGroupDataList($key, $groupname, $groupCategory, $groupid, $pdo);

    $totalrecords = safe_count($result);

    if ($totalrecords > 0) {
        foreach ($result as $key => $value) {

            $mgroupid = $value['mgroupid'];
            $grpname = $value['name'];
            $global = $value['global'];
            $username = $value['username'];
            $count = $value['count'];

            $recordList[] = array("grpid" => $mgroupid, "grpname" => $grpname, "global" => $global, "username" => $username, "count" => $count);
        }
    } else {

        $recordList = array("grpid" => '', "grpname" => '', "global" => '', "username" => '', "count" => '');
    }

    echo json_encode($recordList);
}

function GRP_viewGroupDataList($key, $groupname, $groupCategory, $groupid, $pdo)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $pdo->prepare("select mgroupid,name,global,username,count from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ? and name = ?");
        $sql->execute([$groupid, $groupname]);
        $sqlresult = $sql->fetchAll();
    } else {
        $msg = "Your key has been expired";
        echo $msg;
    }

    return $sqlresult;
}

function all_levelsiblings($key, $mg_id, $pdo)
{

    $key = DASH_ValidateKey($key);
    $user_name = [];

    if ($key) {

        $sql = $pdo->prepare("select username of " . $GLOBALS['PREFIX'] . "core.Users where eid = ?");
        $sql->execute([$mg_id]);
        $sqlresult = $sql->fetchAll();

        foreach ($sqlresult as $key => $value) {

            $user_name[] = "'" . $value['username'] . "'";
        }
    }

    return $user_name;
}



function get_ManualGroup_Add()
{

    $key = '';
    $pdo = pdo_connect();
    $gname_temp = url::postToText('groupname');
    if (preg_match('/\s/', $gname_temp)) {
        $gname = str_replace(' ', '_', $gname_temp);
    } else {
        $gname = $gname_temp;
    }

    $grplist = url::postToText('machinelist');
    $grpmachlist = explode(',', $grplist);
    $grpCategory = 'Wiz_SCOP_MC';
    $username = $_SESSION["user"]["username"];
    $eid = $_SESSION['user']['cId'];
    $global = url::postToText('global');
    $userList = url::postToText('userlist');

    $filteredGroupName = $gname;
    $checkname = GRP_CheckGroupPresent($filteredGroupName, $pdo);

    if ($checkname == '') {

        $auditRes = create_auditLog('Group', 'Create', 'Success', $_REQUEST);
        $recordlist = GRP_AddManualGroup($key, $pdo, $filteredGroupName, $grpCategory, $username, $grpmachlist, $global, $userList);
    } else {
        $auditRes = create_auditLog('Group', 'Create', 'Failed', $_REQUEST);
        $recordlist = array('status' => 'Failed');
    }

    echo json_encode($recordlist);
}



function get_ManualGroup_Edit()
{

    $res = check_GroupEditAccess();
    if ($res['msg'] != 'success') {
        echo json_encode($res);
        exit();
    }
    $grpid = url::postToText('groupid');
    $global = url::postToText('global');
    $edituserlist = url::postToAny('userList');
    GRP_ManualGrupEdit($grpid,   $global, $edituserlist);
    $auditRes = create_auditLog('Group', 'Modification', 'Success', $_POST);
    echo json_encode($editgroup);
    exit;
}



function get_viewadvncdgroups()
{
    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        if ($orderVal == 'modifiedby') {
            $orderStr = 'order by  GM.' . $orderVal . ' ' . $sortVal;
        } else if ($orderVal == 'modifiedtime') {
            $orderStr = 'order by  GM.' . $orderVal . ' ' . $sortVal;
        } else if ($orderVal == 'number') {
            $orderStr = 'order by  MGM.mgmapid ' . $sortVal;
        } else {
            $orderStr = 'order by  MG.' . $orderVal . ' ' . $sortVal;
        }
    } else {
        $orderStr = 'order by MG.created desc';
    }

    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }

    $notifSearch = url::postToText('notifSearch');
    if ($notifSearch != '') {
        $whereSearch = " and  (GM.modifiedby LIKE '%" . $notifSearch . "%'
            OR GM.modifiedtime LIKE '%" . $notifSearch . "%'
            OR MG.name LIKE '%" . $notifSearch . "%'
            OR MG.boolstring LIKE '%" . $notifSearch . "%'
            OR MG.username LIKE '%" . $notifSearch . "%'
            OR MG.created LIKE '%" . $notifSearch . "%')";
    } else {
        $whereSearch = '';
    }
    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $grpCategory = 'Wiz_SCOP_MC';

    $category = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
    //Advacned Group
    $result1 = GRP_GetAdvancedGroupGridData($key, $pdo, $username, $category);
    //Normal Group

    $result2 = GRP_GetGroupGridData($key, $pdo, $username, $orderStr, $limitStr, $whereSearch);
    $result2 = safe_json_decode($result2, true);

    $data = $result2['data'];
    $totCount = $result2['totCount'];
    $result = array();

    if (safe_sizeof($result2) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
        $dataArr['html'] =    Format_GroupDataMysql($data);
        echo json_encode($dataArr);
    }

    $auditRes = create_auditLog('Advanced Group', 'View', 'Success');
}


function Format_GroupDataMysql($result)
{
    $recordList = [];
    $i = 0;
    foreach ($result as $key => $value) {
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $ctime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['created'], "m/d/Y h:i A");
            $mtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['modifiedtime'], "m/d/Y h:i A");
        } else {
            $ctime = date("m/d/Y h:i A", $value['created']);
            $mtime = date("m/d/Y h:i A", $value['modifiedtime']);
        }
        $created_Time = ($value['created'] != '') ? $ctime : '-';
        $modified_Time = ($value['modifiedtime'] != '') ? $mtime : '-';
        $modified_By = ($value['modifiedby'] != '') ? $value['modifiedby'] : '-';

        $id = $value['mgroupid'];
        if ($value['boolstring'] == '') {
            $groupType = "Dynamic Group";
        } else {
            $groupType = $value['boolstring'];
        }
        $groupname = $value['name'];

        $recordList[$i][] = "<p class='ellipsis' value ='" . utf8_encode(UTIL_GetTrimmedGroupName($value['name'])) . "' title='" . utf8_encode(UTIL_GetTrimmedGroupName($value['name'])) . "'>" . utf8_encode(UTIL_GetTrimmedGroupName($value['name'])) . "</p>";
        $recordList[$i][] = "<p class='ellipsis' value ='" . $value['number'] . "' title='" . $value['number'] . "'>" . $value['number'] . "</p>";
        $recordList[$i][] = $groupType;
        $recordList[$i][] = "<p class='ellipsis' value ='" . $value['username'] . "' title='" . $value['username'] . "'>" . $value['username'] . "</p>";
        $recordList[$i][] = "<p class='ellipsis' value ='$created_Time' title='$created_Time'>$created_Time</p>";
        $recordList[$i][] = "<p class='ellipsis' value ='$modified_By' title='$modified_By'>$modified_By</p>";
        $recordList[$i][] = "<p class='ellipsis' value ='$modified_Time' title='$modified_Time'>$modified_Time</p>";
        $recordList[$i][] =  $id;
        $recordList[$i][] = $value['name'];

        $i++;
    }
    return $recordList;
}

function advgroupDelete()
{
    $pdo = pdo_connect();
    $strid = url::requestToAny('value');

    $count = GRP_GroupDelete($strid, $pdo);
    if ($count == 1) {
        $jsonData = array('msg' => 'success');
    } else {
        $jsonData = array('msg' => 'invalid');
    }
    echo json_encode($jsonData);
}



function get_addadvData()
{
    $pdo = pdo_connect();

    $username = $_SESSION["user"]["username"];
    $eventfilter = GRP_EventFilter($pdo, $username);
    $assetquery = GRP_Assetquery($pdo, $username);

    $data = array('event' => $eventfilter, 'asset' => $assetquery);

    echo json_encode($data);
}

function GRP_EventFilter($pdo, $user)
{

    $sql = $pdo->prepare("select id,name, username from " . $GLOBALS['PREFIX'] . "event.SavedSearches where (username = ? OR global='1') order by name asc");
    $sql->execute([$user]);
    $sqlres = $sql->fetchAll();
    $option = "";
    foreach ($sqlres as $key => $value) {

        $option .= "<option value='" . $value['id'] . "'>" . safe_addslashes(utf8_encode($value['name'])) . "</option>";
    }

    return $option;
}

function GRP_Assetquery($pdo, $user)
{

    $sql1 = $pdo->prepare("select id,name from " . $GLOBALS['PREFIX'] . "asset.AssetSearches where (username = ? OR global='1') order by name asc");
    $sql1->execute([$user]);
    $sqlres1 = $sql1->fetchAll();
    $option = "";
    foreach ($sqlres1 as $key => $value1) {

        $option .= "<option value='" . $value1['id'] . "'>" . safe_addslashes(utf8_encode($value1['name'])) . "</option>";
    }
    return $option;
}

function addadvgroupValues()
{

    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];
    $gname_temp = url::requestToAny('gname');
    if (preg_match('/\s/', $gname_temp)) {
        $gname = str_replace(' ', '_', $gname_temp);
    } else {
        $gname = $gname_temp;
    }
    $global = url::requestToAny('global');
    $evntid = url::requestToAny('evntid');
    $asstid = url::requestToAny('asstid');
    $eventradio = url::requestToAny('evntrdo');
    $assetradio = url::requestToAny('asstrdo');
    $days = url::requestToAny('days');
    $hours = url::requestToAny('hours');
    $minutes = url::requestToAny('min');
    $grpCategory = 'Wiz_SCOP_MC';
    $time = time();
    $entspan = ($days * 86400) + ($hours * 3600) + ($minutes * 60);
    $eid = $_SESSION["user"]["cId"];


    if ($eventradio == 1) {

        $evntsucs = GRP_AdvInsert($pdo, $evntid);

        $customerNumber = GRP_GetCustomerNumber($key, $pdo, $eid);
        $filteredGroupName = UTIL_GetFilteredGroupName($gname, $customerNumber);
        $groupprsnt = GRP_GnamePrsnt($pdo, $filteredGroupName);
        $category = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

        if ($groupprsnt['mgroupid'] == '') {
            $eventname = $evntsucs['name'];

            $sqlinsert = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,"
                . "boolstring,mgroupuniq,mcatuniq) select '$filteredGroupName','$username',$global,1,3,$time,$evntid,$entspan,0,'$eventname',"
                . "md5(concat(mcatuniq,',','$filteredGroupName')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?;");
            $sqlinsert->execute([$category]);

            $result = $pdo->lastInsertId();


            $lastinsertid = inserted_id($pdo);
            $groupinsert = GRP_GroupPresent($lastinsertid, $pdo);
            $grpsvdsrch = GRP_AdvInsert($pdo, $evntid);
            $eventmachcal = GRP_MachCal($grpsvdsrch['searchstring'], $days, $pdo);
            $recordcount = safe_count($eventmachcal);
            if ($recordcount > 0) {

                $grpcencount = GRP_Censusrecord($pdo, $lastinsertid);

                foreach ($eventmachcal as $key => $value) {
                    $host = $value['machine'];
                    $site = $value['customer'];

                    $sqlcensus = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where host = ? and site = ? limit 1;");
                    $sqlcensus->execute([$host, $site]);
                    $sqlcensusres = $sqlcensus->fetch();
                    $censusid = $sqlcensusres['id'];
                    // / !
                    $sqlgrpmach = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,"
                        . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, "
                        . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?;");
                    $sqlgrpmach->execute([$censusid, $category, $lastinsertid]);

                    $pdo->lastInsertId();
                }

                $recordData = array('msg' => 'success');
            } else {
                $recordData = array('msg' => 'nomachine');
            }
        } else {
            $recordData = array('msg' => 'error');
        }
    } else if ($assetradio == 1) {

        $assetsucs = GRP_AdvAssetInsert($pdo, $asstid);
        $customerNumber = GRP_GetCustomerNumber($key, $pdo, $eid);
        $filteredGroupName = UTIL_GetFilteredGroupName($gname, $customerNumber);
        $assetgrppsnt = GRP_GnamePrsnt($pdo, $filteredGroupName);
        $astctgry = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

        if ($assetgrppsnt['mgroupid'] == '') {
            $assetname = $assetsucs['name'];

            $sqlinsert = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,"
                . "boolstring,mgroupuniq,mcatuniq) select '$filteredGroupName','$username',$global,1,4,$time,0,$entspan,$asstid,'$assetname',"
                . "md5(concat(mcatuniq,',','$filteredGroupName')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?;");
            $sqlinsert->execute([$astctgry]);
            $result = $pdo->lastInsertId();

            $lastinsertid = inserted_id($pdo);
            $groupinsert = GRP_GroupPresent($lastinsertid, $pdo);
            $assetData = assetReportFunctionEL_new($asstid);
            $assetcompare = fetchMachineId($assetData);

            $assetcensus = GRP_Assetcensusid($pdo, $assetcompare);
            foreach ($assetcensus as $key => $value) {

                $id = $value['id'];

                $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id=? AND mcatid=? AND mgroupid=?; ");
                $sql->execute([$id, $astctgry, $lastinsertid]);

                $pdo->lastInsertId();
            }
            $recordData = array('msg' => 'success');
        } else {
            $recordData = array('msg' => 'error');
        }
    }

    echo json_encode($recordData);
}

function GRP_AdvInsert($pdo, $evntid)
{

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id = ? limit 1;");
    $sql->execute([$evntid]);
    $sqlres = $sql->fetch();

    return $sqlres;
}

function GRP_GnamePrsnt($pdo, $gname)
{

    $sql = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? and mgroupid != 0 limit 1;");
    $sql->execute([$gname]);
    $sqlres = $sql->fetch();
    return $sqlres;
}

function GRP_GroupPresent($groupid, $pdo)
{
    $sql = $pdo->prepare("select G.*, C.mcatid from
            " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,
            " . $GLOBALS['PREFIX'] . "core.MachineCategories as C
            where mgroupid IN (?) and G.mcatuniq = C.mcatuniq;");
    $sql->execute([$groupid]);

    $sqlres = $sql->fetch();

    return $sqlres;
}

function GRP_MachCal($searchstring, $days, $pdo)
{
    $time1 = time();
    $date = "";
    $time2 = strtotime($date . ' -' . $days . ' day');
    $site = $_SESSION['user']['user_sites'];

    foreach ($site as $value) {
        $sites .= "" . $value . ",";
    }
    $sites = rtrim($sites, ',');
    $sql = $pdo->prepare("select machine, customer
    from  " . $GLOBALS['PREFIX'] . "event.Events as E
    left join " . $GLOBALS['PREFIX'] . "core.Census as C
    on (E.customer = C.site)
    where C.deleted = 0
    and servertime between ? and ?
    and customer in (?)
    and (?);");
    $sql->execute([$time2, $time1, $sites, $searchstring]);
    $sqlres = $sql->fetchAll();

    return $sqlres;
}

function GRP_Censusrecord($pdo, $lastinsertid)
{

    $sql = $pdo->prepare("select mgmapid, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups "
        . "on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=?; ");
    $sql->execute([$lastinsertid]);

    $sqlres = $sql->fetchAll();

    return $sqlres;
}

function advgroupUpdate()
{
    $pdo = pdo_connect();
    $id = url::requestToAny('id');
    $defs = url::requestToAny('def');
    $groupid = url::requestToAny('grpid');
    $days = '25';
    $grpCategory = 'Wiz_SCOP_MC';
    $username = $_SESSION['user']['username'];

    if ($defs == '3') {

        $category = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
        $evntsucs = GRP_AdvInsert($pdo, $id);
        $eventmachcal = GRP_MachCal($evntsucs['searchstring'], $days, $pdo);
        $recordcount = safe_count($eventmachcal);
        if ($recordcount > 0) {
            $grpcencount = GRP_Censusrecord($pdo, $lastinsertid);

            $sqldelete = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
                . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = ?; ");
            $sqldelete->execute([$groupid]);
            $pdo->lastInsertId();

            foreach ($eventmachcal as $key => $value) {
                $host = $value['machine'];
                $site = $value['customer'];

                $sqlcensus = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where host = ? and site = ? limit 1;");
                $sqlcensus->execute([$host, $site]);
                $sqlcensusres = $sqlcensus->fetch();
                $censusid = $sqlcensusres['id'];

                $sqlgrpmach = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,"
                    . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, "
                    . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?;");
                $sqlgrpmach->execute([$censusid, $category, $groupid]);

                $pdo->lastInsertId();

                $recordData = array('msg' => 'success');
            }
        } else {
            $recordData = array('msg' => 'nomachine');
        }
    } else if ($defs == '4') {
        $astctgry = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
        $assetsucs = GRP_AdvAssetInsert($pdo, $id);

        $sqldelete = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
            . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = ?; ");
        $sqldelete->execute([$groupid]);
        $pdo->lastInsertId();

        $assetdataid = GRP_AssetDataId($pdo, $assetsucs['displayfields']);
        $assetData = assetReportFunctionEL_new($id);
        $assetcompare = fetchMachineId($assetData);

        $assetcensus = GRP_Assetcensusid($pdo, $assetcompare);
        foreach ($assetcensus as $key => $value) {

            $id = $value['id'];

            $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,
                " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories,
                " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id=? AND mcatid=? AND mgroupid=?; ");
            $sql->execute([$id, $astctgry, $groupid]);

            $pdo->lastInsertId();
        }
        $recordData = array('msg' => 'success');
    }

    echo json_encode($recordData);
}

function advgroupedit()
{
    $pdo = pdo_connect();
    $id = url::requestToAny('id');
    $defs = url::requestToAny('def');
    $gid = url::requestToAny('gid');
    $result = GRP_GroupDetails($gid, $pdo);
    $username = $_SESSION["user"]["username"];
    $minute = 0;
    $hour = 0;
    $day = 0;

    if ($defs == '3') {

        $event = GRP_EditEventFilter($pdo, $id, $username);
        $asset = GRP_Assetquery($pdo, $username);
        $time = $result['eventspan'];
        if ($time > 0) {
            $min = round($time / 60);
            $hour = floor($time / 3600);
            $day = floor($time / 86400);

            $minute = intval($min % 60);
            $hour = intval($hour % 24);
            $day = intval($day);
        }
    } else if ($defs == '4') {
        $asset = GRP_EditAssetquery($pdo, $id, $username);
        $event = GRP_EventFilter($pdo, $username);
    }

    $gname = UTIL_GetTrimmedGroupName($result['name']);
    $global = $result['global'];
    $eventSpan = $result['eventspan'];

    $recordlist = array('name' => $gname, 'global' => $global, 'event' => $event, 'asset' => $asset, 'defs' => $defs, 'minute' => $minute, 'hour' => $hour, 'day' => $day);
    echo json_encode($recordlist);
}

function GRP_GroupDetails($gid, $pdo)
{

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid= ? limit 1;");
    $sql->execute([$gid]);
    $sqlres = $sql->fetch();

    return $sqlres;
}

function GRP_EditEventFilter($pdo, $evntid, $user)
{

    $sql = $pdo->prepare("select id,name from " . $GLOBALS['PREFIX'] . "event.SavedSearches where (username = ? OR global='1')");
    $sql->execute([$user]);
    $sqlres = $sql->fetchAll();
    $option = "";
    foreach ($sqlres as $key => $value) {
        if ($evntid == $value['id']) {
            $option .= "<option value='" . $value['id'] . "' selected>" . utf8_encode($value['name']) . "</option>";
        } else {
            $option .= "<option value='" . $value['id'] . "'>" . utf8_encode($value['name']) . "</option>";
        }
    }
    return $option;
}

function GRP_EditAssetquery($pdo, $assetid, $user)
{

    $sql = $pdo->prepare("select id,name from " . $GLOBALS['PREFIX'] . "asset.AssetSearches where (username = ? OR global='1')");
    $sql->execute([$user]);
    $sqlres = $sql->fetchAll();
    $option = "";
    foreach ($sqlres as $key => $value) {
        if ($assetid == $value['id']) {
            $option .= "<option value='" . $value['id'] . "' selected>" . utf8_encode($value['name']) . "</option>";
        } else {
            $option .= "<option value='" . $value['id'] . "'>" . utf8_encode($value['name']) . "</option>";
        }
    }
    return $option;
}

function editadvgroupValues()
{

    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];
    $gname_temp = url::requestToAny('gname');
    if (preg_match('/\s/', $gname_temp)) {
        $gname = str_replace(' ', '_', $gname_temp);
    } else {
        $gname = $gname_temp;
    }
    $global = url::requestToAny('global');
    $evntid = url::requestToAny('evntid');
    $asstid = url::requestToAny('asstid');
    $eventradio = url::requestToAny('evntrdo');
    $assetradio = url::requestToAny('asstrdo');
    $days = url::requestToAny('days');
    $gid = url::requestToAny('groupid');
    $grpCategory = 'Wiz_SCOP_MC';

    if ($eventradio == 1) {

        $evntsucs = GRP_AdvInsert($pdo, $evntid);
        $sql = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? and mgroupid != ? limit 1;");
        $sql->execute([$gname, $gid]);
        $sqlres = $sql->fetch();

        if ($sqlres['mgroupid'] == '') {
            $eventname = $evntsucs['name'];

            $sqlupdate = $pdo->prepare("update MachineGroups set name = ?,global = ?,style = 3,eventquery = ?,eventspan = 2073600,
                    assetquery = 0,boolstring = ?,revlname = revlname +1 where mgroupid = ? and username = ?;");
            $sqlupdate->execute([$gname, $global, $evntid, $eventname, $gid, $username]);

            $result = $pdo->lastInsertId();

            $grpsvdsrch = GRP_AdvInsert($pdo, $evntid);
            $eventmachcal = GRP_MachCal($grpsvdsrch['searchstring'], $days, $pdo);
            $recordcount = safe_count($eventmachcal);
            if ($recordcount > 0) {

                $grpcencount = GRP_Censusrecord($pdo, $gid);

                foreach ($eventmachcal as $key => $value) {
                    $host = $value['machine'];
                    $site = $value['customer'];

                    $sqlcensus = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where host = ? and site = ? limit 1;");
                    $sqlcensus->execute([$host, $site]);
                    $sqlcensusres = $sqlcensus->fetch();
                    $censusid = $sqlcensusres['id'];

                    $sqlgrpmach = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,"
                        . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, "
                        . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?;");
                    $sqlgrpmach->execute([$censusid, $category, $gid]);

                    $pdo->lastInsertId();
                }

                $recordData = array('msg' => 'success');
            } else {
                $recordData = array('msg' => 'nomachine');
            }
        } else {
            $recordData = array('msg' => 'error');
        }
    } else {

        $astctgry = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
        $assetsucs = GRP_AdvAssetInsert($pdo, $asstid);
        $sql = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? and mgroupid != ? limit 1;");
        $sql->execute([$gname, $gid]);
        $sqlres = $sql->fetch();
        if ($sqlres['mgroupid'] == '') {

            $assetname = $assetsucs['name'];

            $sqlupdate = $pdo->prepare("update MachineGroups set name = ?,global = ?,style = 4,eventquery = 0,eventspan = 2073600,
                assetquery = ?,boolstring = ?,revlname = revlname +1 where mgroupid = ? and username = ?;");
            $sqlupdate->execute([$gname, $global, $asstid, $assetname, $gid, $username]);

            $result = $pdo->lastInsertId();

            $sqldelete = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
                . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = ?; ");
            $sqldelete->execute([$gid]);
            $pdo->lastInsertId();

            $assetdataid = GRP_AssetDataId($pdo, $assetsucs['displayfields']);
            $assetData = assetReportFunctionEL_new($asstid);
            $assetcompare = fetchMachineId($assetData);

            $assetcensus = GRP_Assetcensusid($pdo, $assetcompare);
            foreach ($assetcensus as $key => $value) {

                $id = $value['id'];

                $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id=? AND mcatid=? AND mgroupid=?; ");
                $sql->execute([$id, $astctgry, $gid]);

                $pdo->lastInsertId();
            }
            $recordData = array('msg' => 'success');
        } else {
            $recordData = array('msg' => 'error');
        }
    }

    echo json_encode($recordData);
}



function GRP_AdvAssetInsert($pdo, $id)
{

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "asset.AssetSearches where id = ? limit 1;");
    $sql->execute([$id]);
    $sqlres = $sql->fetch();

    return $sqlres;
}

function GRP_AssetDataId($pdo, $displayfields)
{

    $disp = explode(':', $displayfields);
    foreach ($disp as $value) {
        $names .= "'" . $value . "',";
    }
    $names = rtrim($names, ',');

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "asset.DataName D where D.name in (?)");
    $sql->execute([$names]);
    $sqlres = $sql->fetchAll();

    foreach ($sqlres as $val) {
        $dataid .= "'" . $val['dataid'] . "',";
    }
    $dataid = rtrim($dataid, ',');
    return $dataid;
}

function GRP_AssetmachId($pdo)
{
    $site = $_SESSION['user']['user_sites'];

    foreach ($site as $value) {
        $sites .= "" . $value . ",";
    }
    $sites = rtrim($sites, ',');

    $sql = $pdo->prepare("select M.machineid,M.host,M.cust from " . $GLOBALS['PREFIX'] . "asset.Machine M join " . $GLOBALS['PREFIX'] . "core.Census C on M.host = C.host and M.cust = C.site where "
        . "C.site in (?);");
    $sql->execute([$sites]);

    $sqlres = $sql->fetchAll();

    foreach ($sqlres as $value) {
        $macid .= "'" . $value['machineid'] . "',";
    }
    $macid = rtrim($macid, ',');

    return $macid;
}

function GRP_AssetComp($pdo, $dataid, $machid)
{

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "asset.AssetData
    where machineid in (?)
    and dataid in (?) group by machineid");
    $sql->execute([$machid, $dataid]);

    $sqlres = $sql->fetchAll();

    foreach ($sqlres as $value) {
        $machid .= "'" . $value['machineid'] . "',";
    }
    $machid = rtrim($machid, ',');

    return $machid;
}

function GRP_Assetcensusid($pdo, $machneid)
{

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census C," . $GLOBALS['PREFIX'] . "asset.Machine m where C.host = m.host and C.site = m.cust and m.machineid in (?)");
    $sql->execute([$machneid]);
    $sqlres = $sql->fetchAll();

    return $sqlres;
}

function group_type($row)
{
    $type = $row;
    switch ($type) {
        case 0:
            $result = 'Invalid';
            break;
        case 1:
            $result = 'Built-In';
            break;
        case 2:
            $result = 'Manual';
            break;
        case 3:
            $result = 'Event query';
            break;
        case 4:
            $result = 'Asset query';
            break;
        case 5:
            $result = 'Expression';
            break;
        case 6:
            $result = 'Search';
            break;
        case 7:
            $result = 'Type';
            break;
        default:
            $result = "Unknown ($type)";
            break;
    }
    return $result;
}

function assetReportFunction($filterId)
{
    global $url;

    $pdo = pdo_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $pdo);

    $searchValue = $_SESSION['searchValue'];
    $userid = $_SESSION["user"]["userid"];

    $sqlSubSec = $pdo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE filterid =?");
    $sqlSubSec->execute([$filterId]);
    $subSec = $sqlSubSec->fetchAll();
    foreach ($subSec as $value1) {
        $subSectionData['subSectionData'] = array($value1['name'], $value1['filtertype'], $value1['filterid'], $value1['groupVal'], $value1['reportduration'], $value1['updatetype'], $value1['updatesize'], $value1['mnth'], $value1['year'], $value1['ostype']);
    }

    $machGrpList = fetch_machine($userid, $pdo);
    if ($machGrpList != '') {

        $machines = fetch_machines_list($machGrpList, $pdo);
    }

    $res = get_DisplayFields($pdo, $filterId);
    $fields = $res['displayfields'] . 'Machine Name:Site Name:';
    $terms = get_SearchTerms($pdo, $filterId);

    if (safe_count($terms) > 1) {
        $return = getMultipleBlockAssets($pdo, $subSectionData['subSectionData'], $terms, $fields, $machines);
    } else {
        $return = getSingleBlockAssets($pdo, $subSectionData['subSectionData'], $terms[1], $fields, $machines);
    }

    return $return;
}

function assetReportFunctionEL_new($filterId)
{

    $pdo = pdo_connect();
    $machineid = '';
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $userid = $_SESSION["user"]["userid"];
    $from = 0;
    $size = 10000;
    $export = 0;

    $res = get_DisplayFields($pdo, $filterId);
    $fields = $res['displayfields'] . ":Machine Name:Site Name:";
    $searchString = $res['searchstring'];
    $terms = get_SearchTerms($pdo, $filterId);

    $compareDataid = $terms[0]['dataid'];
    $subSectionData = array();
    $filterName = $subSectionData['subSectionData'][0];

    $machGrpList = fetch_machine($userid, $pdo);
    if ($machGrpList != '') {
        $machines = fetch_machines_list($machGrpList, $pdo);
    }
    $machineCount = safe_count($machines['mid']);
    foreach ($machines['mid'] as $val) {
        $machineid .= '"' . $val . '",';
    }
    $machineid = rtrim($machineid, ',');;

    if ($searchString != '') {
        $time = time();
        $indexname = 'asset_' . $filterId . '_' . $time;
        $tempArray = array();
        $machineCount = safe_count($machines['mid']);
        if ($machineCount > 5) {
            $k = floor($machineCount / 5) + 1;
            for ($i = 0; $i < $k; $i++) {
                $machine = array();
                $start = $i * 5;
                $end = 5;
                $machine = array_slice($machines['mid'], $start, $end);
                $machineid = '';
                foreach ($machine as $val) {
                    $machineid .= '"' . $val . '",';
                }
                $machineids = rtrim($machineid, ',');
                $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineids . ']}}';

                $return = __getAssetELQry($filterId, $machineid, $indexname, $from, $size, $export, $pdo);
            }
            $res = $tempArray;
        } else {
            $machineid = '';
            foreach ($machines['mid'] as $key => $val) {
                $machineid .= '"' . $val . '",';
            }
            $machineids = rtrim($machineid, ',');
            $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineids . ']}}';
            $return = __getAssetELQry($filterId, $machineid, $indexname, $from, $size, $export, $pdo);

            $res1 = safe_json_decode($return, TRUE);
            array_push($tempArray, $res1);
            $res = $tempArray;
        }
        updateWindowSize($indexname);
    } else {
        $indexname = 'assetdata';
    }

    if (!empty($machines['mid'])) {
        foreach ($machines['mid'] as $val) {
            $machineid .= '"' . $val . '",';
        }
        $machineid = rtrim($machineid, ',');
        if ($searchType == 'Sites' && $searchValue != 'All') {
            $machineid = ' ,"filter": {"term": {"sitename.keyword": "' . $searchValue . '"}}';
        } else if ($searchType == 'Sites' && $searchValue == 'All') {
            $sql = $pdo->prepare("select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = ?");
            $sql->execute([$userid]);
            $result = $sql->fetchAll();
            $names = '';
            foreach ($result as $val) {
                $names .= '"' . $val['customer'] . '",';
            }
            $names = rtrim($names, ',');
            $machineid = ' ,"filter": {"terms": {"sitename.keyword": [' . $names . ']}}';
        } else if ($searchType == 'ServiceTag') {
            $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineid . ']}}';
        } else {
            $machineid = ' ,"filter": {"terms": {"machineid": [' . $machineid . ']}}';
        }
        if ($indexname == 'assetdata') {
            $indexname = 'assetdata';
            $result = __getAssetELQry($filterId, $machineid, $indexname, $start, $end, $export, $pdo);
        } else {
            $result = __getFilerIndexData($indexname, $from, $size);
        }

        if (safe_count($terms) > 1) {
            $displayFields = asset_display_criteria($pdo, $fields, $terms, $machines['mid']);
            $return = getAssetResponseArray_new1($displayFields, $result['result'], $fields, $filterName, $terms, $compareDataid);
        } else {
            $displayFields = asset_display_criteria($pdo, $fields, $terms[1], $machines['mid']);
            $return = getAssetResponseArray_new1($displayFields, $result['result'], $fields, $filterName, $terms[1], $compareDataid);
        }
        return $return;
    }
}

function getAssetResponseArray_new($displayFields, $res, $fields, $filterName, $terms, $compareDataid)
{
    $result = array();
    foreach ($res as $key => $val) {
        $i = explode('_', $key)[1];
        $ind = 0;
        $coun = safe_count($val);
        foreach ($val as $temp) {
            if ($temp[$i] == '-') {
                $ind++;
            }
        }
        if ($ind == ($coun - 2)) {
        } else {
            $result[$key] = $val;
        }
        $i++;
    }

    $search = $displayFields['criteria'];
    unset($displayFields['criteria']);
    $return[0]['details'] = $displayFields;
    $return[0]['details']['rows'] = $result;
    $return[0]['details']['total'] = safe_count($result);
    $return[0]['details']['pages'] = 0;
    $return[0]['details']['block'] = 1;
    $return[0]['details']['search'] = $search;
    $return[0]['details']['fields'] = $fields;
    $return[0]['details']['dataId'] = $compareDataid;
    $return[0]['details']['showGraph'] = safe_count($terms);
    $return[0]['groupedData']['count'] = safe_count($result);
    $return[0]['groupedData']['name'] = $filterName;
    $return[0]['type'] = 'asset';
    $return['graph'] = $result;
    return $return;
}

function fetchMachineId($assetData)
{

    $arr = $assetData[0]['details']['rows'];
    $machineId = '';
    foreach ($arr as $key => $val) {

        $machineId .= $val['machineid'] . ',';
    }
    return rtrim($machineId, ',');
}

function fetch_machine($userid, $pdo)
{

    $sql = $pdo->prepare("select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = ?");
    $sql->execute([$userid]);
    $result = $sql->fetchAll();
    $names = [];

    foreach ($result as $val) {
        $names[] = "'" . $val['customer'] . "'";
    }

    $sql1 = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in (" . implode(",", $names) . ")");
    $sql1->execute();
    $res = $sql1->fetchAll();
    foreach ($res as $key => $val) {
        $machGrpsUniqs .= "'" . $val['mgroupuniq'] . "',";
    }

    return rtrim($machGrpsUniqs, ",");
}

function check_EditAccess()
{

    $pdo = pdo_connect();
    $userName = $_SESSION['user']['username'];

    $grpId = url::postToText('groupid');

    $sql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE lower(name) = ?");
    $sql->execute([strtolower($grpId)]);

    $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    if ($sqlRes['username'] == $userName) {
        $result = array('msg' => 'success');
    } else {
        $result = array('msg' => 'failed');
    }
    echo json_encode($result);
}

function check_GroupEditAccess()
{
    $pdo = pdo_connect();
    $siteCnt = 0;
    $userName = $_SESSION['user']['username'];
    $siteAccessList = $_SESSION['user']['site_list'];
    logs::log('function check_GroupEditAccess');

    $grpId = url::postToText('groupid');

    $sql = $pdo->prepare("SELECT mgroupid, name, username, mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups "
        . "WHERE mgroupid = ? ");
    $sql->execute([$grpId]);
    $sqlRes = $sql->fetch(PDO::FETCH_ASSOC);
    $mgroupuniq = $sqlRes['mgroupuniq'];
    $grpusername = $sqlRes['username'];

  logs::log(['function'=>'check_GroupEditAccess','point'=>1,'mgroupuniq'=>$mgroupuniq]);

    if ($mgroupuniq != '') {

//        $siteSql = "select c.site, mg.mgroupuniq from MachineGroupMap mg, Census c "
//            . "where mg.censussiteuniq = c.censussiteuniq and mg.mgroupuniq = ? "
//            . "group by mg.censussiteuniq;";
      $siteSql = "select distinct c.site, mg.mgroupuniq from MachineGroupMap mg, Census c  where mg.censussiteuniq = c.censussiteuniq and mg.mgroupuniq = ?";
        $stmt = $pdo->prepare($siteSql);
        $stmt->execute([$mgroupuniq]);
        $siteRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $grpSiteCnt = safe_count($siteRes);

      logs::log(['function'=>'check_GroupEditAccess','point'=>2,'grpSiteCnt'=>$grpSiteCnt]);

        foreach ($siteAccessList as $svalue) {
            foreach ($siteRes as $gsvalue) {
                if ($svalue == $gsvalue['site']) {
                    $siteCnt++;
                }
            }
        }
      logs::log(['function'=>'check_GroupEditAccess','point'=>3,'siteCnt'=>$siteCnt]);
        $chnguser = false;
        if ($userName == $grpusername) {
            $chnguser = true;
        }

        if ($grpSiteCnt == $siteCnt) {
            $result = array('msg' => 'success', 'usrstat' => $chnguser);
        } else {
            $result = array('msg' => 'failed');
        }
    } else {
        $result = array('msg' => 'advfailed');
    }
    echo json_encode($result);
}

function get_MachineList()
{

    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];
    $user_sites = $_SESSION['user']['user_sites'];
    $manualgrplist = GRP_ManualGrpList($key, $pdo, $user_sites, $username, '');

    $html = '';
    $temp = array();
    if (safe_count($manualgrplist) > 0) {
        foreach ($manualgrplist as $val) {
            $site = UTIL_GetTrimmedGroupName($val['site']);
            $temp[$site][$val['id']] = $val['host'];
        }

        foreach ($temp as $key => $value) {
            $key = str_replace(' ', '', $key);
            $html .= '<li>
                        <a data-bs-toggle="collapse" href="#A_' . $key . '">
                                    <p>' . $key . '<b class="caret"></b></p>
                                </a>
                        <div class="collapse" id="A_' . $key . '">
                                    <ul class="nav">';

            foreach ($value as $k => $v) {
                if (isset($v)) {
                    $html .= '<li>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" name="type" type="checkbox" value="' . $k . '">
                                            <span class="form-check-sign"></span>
                                    </label>
                                </div>
                                <a href="#"><span class="sidebar-normal">' . $v . '</span></a>
                            </li>';
                }
            }
            $html .= '</ul>
                        </div>
                    </li>';
        }
    }
    echo json_encode(array("state" => "success", "option" => $html));
}

function get_MachineListEdit()
{
    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];
    $user_sites = $_SESSION['user']['user_sites'];
    $mid = url::getToText('mid');
    $machinelist = explode(',', rtrim($mid, ','));

    $manualgrplist = GRP_ManualGrpList($key, $pdo, $user_sites, $username, '');

    $html = '';
    $temp = array();
    if (safe_count($manualgrplist) > 0) {
        foreach ($manualgrplist as $val) {
            $site = UTIL_GetTrimmedGroupName($val['site']);
            $temp[$site][$val['id']] = $val['host'];
        }
        foreach ($temp as $key => $value) {
            $html .= '<li>
                                <a data-bs-toggle="collapse" href="#AA_' . $key . '">
                                    <p>' . $key . '<b class="caret"></b></p>
                                </a>
                                <div class="collapse" id="AA_' . $key . '">
                                    <ul class="nav">';
            foreach ($value as $k => $v) {
                if (in_array($k, $machinelist)) {
                    $html .= '<li>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" name="type" type="checkbox" value="' . $k . '" checked>
                                                    <span class="form-check-sign"></span>
                                                </label>
                                </div><a href="#"><span class="sidebar-normal">' . $v . '</span></a>
                            </li>';
                } else {
                    $html .= '<li>
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" name="type" type="checkbox" value="' . $k . '">
                                                    <span class="form-check-sign"></span>
                                                </label>
                                </div><a href="#"><span class="sidebar-normal">' . $v . '</span></a>
                            </li>';
                }
            }
            $html .= '</ul>
                                </div>
                            </li>';
        }
    }
    echo json_encode(array("state" => "success", "option" => $html));
}


function get_UsersList($return = '', $enabledUsers = array())
{
    $pdo = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $userid = $_SESSION["user"]["userid"];
    $siteList = $_SESSION["user"]["site_list"];

    $siteList = array_filter($siteList, function ($value) {
        return trim(preg_replace('/\s+/', ' ', $value));
    });

    $siteList = join("','", $siteList);

    $childUserIds = getChildDetails($userid, 'userid');
    $userin = '';
    if (safe_count($childUserIds) > 0) {
        $childUserIdsData = implode(',', $childUserIds);
        $userin = " and U.userid IN ($childUserIdsData)";
    } else {
        $userin = " and U.userid = ''";
    }

    $userSqlQuery = "select C.username, U.userid from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U "
        . "where C.customer IN ('" . $siteList . "') and U.username = C.username and "
        . "C.username != '' and C.username NOT IN ('admin', 'hfn', '" . $username . "') "
        . " $userin group by C.username order by C.username ";
    $userSql = $pdo->prepare($userSqlQuery);

    $userSql->execute();
    $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);

    $userListData = '';
    if (safe_count($userRes) > 0) {
        foreach ($userRes as $value) {
            $selected = (in_array($value['username'], $enabledUsers)) ? ' selected ' : '';
            $userListData .= '<option ' . $selected . ' data-tokens="' . $value['username'] . '" value="' . $value['userid'] . '">' . $value['username'] . '</option>';
        }
    } else {
        $userListData = '<option value="">No Users Available</option>';
    }
    if ($return == '1')
        return $userListData;
    ob_clean();
    echo $userListData . json_encode($enabledUsers);
}

function checkAdvGrpEditAccess()
{
    $pdo = pdo_connect();

    $username = $_SESSION["user"]["username"];
    $advgrpid = url::requestToAny('advgrpid');

    $advgrpsql = $pdo->prepare("select count(mgroupid) grpcnt from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ? and username = ?");
    $advgrpsql->execute([$advgrpid, $username]);
    $advgrpres = $advgrpsql->fetch();

    ob_clean();
    if ($advgrpres['grpcnt'] > 0) {
        echo 'ok';
    } else {
        echo 'no';
    }
}

function getEditUsersList()
{
    $pdo = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $siteList = $_SESSION["user"]["site_list"];
    foreach ($siteList as $value) {
        $siteData .= $value . ",";
    }
    $siteData = rtrim($siteData, ',');
    $siteData = explode(',', $siteData);

    $advgrpid = url::requestToAny('advgrpid');
    $DataArr = ['admin', 'hfn', $username];
    $in  = str_repeat('?,', safe_count($siteData) - 1) . '?';
    $in2 = str_repeat('?,', safe_count($DataArr) - 1) . '?';
    $params = array_merge($siteData, $DataArr);
    $userSql = $pdo->prepare("select C.username, U.userid from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U "
        . "where C.customer IN ($in) and U.username = C.username and "
        . "C.username != '' and C.username NOT IN ($in2) "
        . "group by C.username order by C.username ");
    $userSql->execute($params);
    $userRes = $userSql->fetchAll();

    $advgrpsql = $pdo->prepare("select GROUP_CONCAT(username) as userlist from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupid = ?");
    $advgrpsql->execute([$advgrpid]);
    $advgrpres = $advgrpsql->fetch();
    $advgrplist = explode(',', $advgrpres['userlist']);

    $userListData = '';
    if (safe_count($userRes) > 0) {
        foreach ($userRes as $value) {
            $selected = '';
            if (in_array($value['username'], $advgrplist)) {
                $selected = 'selected';
            }
            $userListData .= '<option data-tokens="' . $value['username'] . '" value="' . $value['userid'] . '" ' . $selected . '>' . $value['username'] . '</option>';
        }
    } else {
        $userListData = '<option value="">No Users Available</option>';
    }
    ob_clean();
    echo $userListData;
}

function get_FilterList()
{
    $pdo = pdo_connect();
    $siteList = $_SESSION['user']['site_list'];
    $assetOption = $eventOption = $siteOption = '';

    $sql = $pdo->prepare("select filter_name FROM " . $GLOBALS['PREFIX'] . "asset.filter");
    $sql->execute();
    $sqlRes = $sql->fetchAll();

    $eventsql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "event.SavedSearches");
    $eventsql->execute();
    $eventRes = $eventsql->fetchAll();

    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $val) {
            $assetOption .= "<option data-tokens=\"" . $val['filter_name'] . "\" value=\"" . $val['filter_name'] . "\">" . $val['filter_name'] . "</option>";
        }
    }

    if (safe_count($eventRes) > 0) {
        foreach ($eventRes as $vals) {
            $id = $val['id'];
            $name = $val['name'];
            $eventOption .= "<option value='$name'>$name</option>";
        }
    }

    if (safe_count($siteList) > 0) {
        foreach ($siteList as $key => $val) {
            if (!empty($val)) {
                $siteOption .= "<option value=$val>$val</option>";
            }
        }
    }

    $jsonArray = array("asset" => $assetOption, "event" => $eventOption, "site" => $siteOption);

    echo json_encode($jsonArray);
}

function get_Filter_ListL1()
{
    $pdo = pdo_connect();
    $type = url::postToAny('type');
    $dataid = url::postToAny('dataid');

    $assetOption = $eventOption = $siteOption = '';

    $sql = $pdo->prepare("select name,dataid FROM " . $GLOBALS['PREFIX'] . "asset.DataName");
    $sql->execute();
    $sqlRes = $sql->fetchAll();

    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $val) {
            if ($val['dataid'] == $dataid) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $assetOption .= "<option data-tokens=\"" . $val['name'] . "\" value=\"" . $val['name'] . "\" $selected>" . $val['name'] . "</option>";
        }
    }

    $sql2 = $pdo->prepare("select name FROM " . $GLOBALS['PREFIX'] . "asset.DataName where dataid=?");
    $sql2->execute([$dataid]);
    $sqlRes2 = $sql2->fetch();

    $jsonArray = array("asset" => $assetOption, "selectedDataName" => $sqlRes2['name']);

    echo json_encode($jsonArray);
}

function get_Filter_ListL2()
{
    $SelectedValue = url::postToAny('SelectedValue');

    $operator = url::postToAny('operator');
    $dataid = url::postToAny('dataid');

    $pdo = pdo_connect();
    if ($dataid) {
        $sql2 = $pdo->prepare("select name FROM " . $GLOBALS['PREFIX'] . "asset.DataName where dataid=?");
        $sql2->execute([$dataid]);
        $sqlRes2 = $sql2->fetch();

        $dataName = $sqlRes2['name'];
        $dataStr = $dataName . "." . url::postToAny('dataStr');
    } else {
        $dataStr = '';
    }

    $assetOption = '';

    $sql = $pdo->prepare("select filter_name FROM " . $GLOBALS['PREFIX'] . "asset.filter where filter_name like '%$SelectedValue.%'");
    $sql->execute();
    $sqlRes = $sql->fetchAll();


    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $val) {
            if ($dataStr == $val['filter_name']) {
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $assetOption .= "<option data-tokens=\"" . $val['filter_name'] . "\" value=\"" . $val['filter_name'] . "\" $selected >" . $val['filter_name'] . "</option>";
        }
    }

    $filterArr = array("1" => 'Equal', "2" => 'Not Equal', "3" => 'Contains');

    foreach ($filterArr as $key => $val) {
        if ($val == $operator) {
            $selected = 'selected';
        } else {
            $selected = '';
        }

        $filterOption .= "<option value='$key' $selected>$val</option>";
    }



    $jsonArray = array("asset" => $assetOption, "filter" => $filterOption);

    echo json_encode($jsonArray);
}

function getSiteList()
{
    $siteList = $_SESSION['user']['site_list'];
    $siteOption = '';
    if (safe_count($siteList) > 0) {
        foreach ($siteList as $key => $val) {
            if (!empty($val)) {
                $siteOption .= "<option value=$val>$val</option>";
            }
        }
    }

    $jsonArray = array("site" => $siteOption);

    echo json_encode($jsonArray);
}

function getCGSiteList()
{
    $siteList = $_SESSION['user']['site_list'];
    $siteOption = '';
    if (safe_count($siteList) > 0) {
        foreach ($siteList as $key => $val) {
            if (!empty($val)) {
                $siteOption .= "<option value=$val>$val</option>";
            }
        }
    }

    $jsonArray =  $siteOption;

    echo json_encode($jsonArray);
}


function createAdvanceGrpOld()
{

    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];
    $gname_temp = url::requestToAny('gname');
    if (preg_match('/\s/', $gname_temp)) {
        $gname = str_replace(' ', '_', $gname_temp);
    } else {
        $gname = $gname_temp;
    }

    $newarray = array();
    $userList = url::requestToAny('userlist');
    $str = url::requestToAny('str');
    $cond = url::requestToAny('condition');
    $strVal = url::requestToAny('strval');
    $sitelist = url::requestToAny('site');
    $grpCategory = 'Wiz_SCOP_MC';
    $time = time();
    $newarray['string'] = $str;
    $newarray['condition'] = $cond;
    $newarray['stringvalue'] = $strVal;
    $newarray['sitelist'] = $sitelist;
    $newarray['groupname'] = $gname;
    SaveConfigurations($newarray);

    $filteredGroupName = $gname;
    $assetgrppsnt = GRP_GnamePrsnt($pdo, $filteredGroupName);
    $astctgry = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

    if ($assetgrppsnt['mgroupid'] == '') {
        $assetsucs = array();
        $assetname = $assetsucs['name'];

        $sqlinsert = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,"
            . "boolstring,mgroupuniq,mcatuniq) select '$filteredGroupName','$username',1,1,4,$time,0,0,0,'$assetname',"
            . "md5(concat(mcatuniq,',','$filteredGroupName')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?;");
        $sqlinsert->execute([$astctgry]);


        $lastinsertid = $pdo->lastInsertId();
        $groupinsert = GRP_GroupPresent($lastinsertid, $pdo);
        $machineData = GRP_fetchMachineList($str, $cond, $strVal, $sitelist);

        $assetcensus = GRP_getcensusid($pdo, $machineData, $sitelist);
        foreach ($assetcensus as $key => $value) {

            $id = $value['id'];

            $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id=? AND mcatid=? AND mgroupid=?; ");
            $sql->execute([$id, $astctgry, $lastinsertid]);

            $pdo->lastInsertId();
        }
        $insDefSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) "
            . "VALUES (?, ?, ?)");
        $insDefSql->execute([$lastinsertid, $filteredGroupName, $username]);
        $pdo->lastInsertId();

        if ($userList != '') {
            $userSql = $pdo->prepare("select username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN (?)");
            $userSql->execute([$userList]);
            $userRes = $userSql->fetchAll();
            foreach ($userRes as $value) {
                $insSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) "
                    . "VALUES (?, ?, '" . $value['username'] . "')");
                $insSql->execute([$lastinsertid, $filteredGroupName]);
                $pdo->lastInsertId();
            }
        }
        $auditRes = create_auditLog('Advanced Group', 'Create', 'Success', $_REQUEST);

        $recordData = array('msg' => 'success');
    } else {
        $auditRes = create_auditLog('Advanced Group', 'Create', 'Failed', $_REQUEST);

        $recordData = array('msg' => 'error');
    }

    echo json_encode($recordData);
}

function createAdvanceGrp()
{
    $key = '';
    $pdo = pdo_connect();
    $gname_temp = url::postToText('gname');
    if (preg_match('/\s/', $gname_temp)) {
        $gname = str_replace(' ', '_', $gname_temp);
    } else {
        $gname = $gname_temp;
    }
    $action = url::postToAny('action');
    $grplist = url::postToText('machinelist');
    $grpmachlist = explode(',', $grplist);
    $grpCategory = 'Wiz_SCOP_MC';
    $username = $_SESSION["user"]["username"];
    $eid = $_SESSION['user']['cId'];
    $global = 1; //url::postToText('global');
    $userList = url::postToText('userlist');

    $str = url::requestToAny('str');
    $cond = url::requestToAny('condition');

    $strValue = explode('.', $str);

    $DataName = $strValue[0];
    $assetFilter = $strValue[1];

    $strVal1 = url::requestToAny('strval');
    $strVal2 = strtoupper(url::requestToAny('strval'));
    $filterCondition = '';

    if ($cond == 1) {
        $condition = 'Equal';
        $filterCondition = "and (A.value->>'$.\"$assetFilter\"' = '$strVal1' or A.value->>'$.\"$assetFilter\"' = '$strVal2') ";
    } else if ($cond == 2) {
        $condition = 'Not Equal';
        $filterCondition = "and (A.value->>'$.\"$assetFilter\"' != '%$strVal1%' or A.value->>'$.\"$assetFilter\"' != '$strVal2') ";
    } else if ($cond == 3) {
        $condition = 'Contains';
        $filterCondition = "and (A.value->>'$.\"$assetFilter\"' like '%$strVal1%' or A.value->>'$.\"$assetFilter\"' like '%$strVal2%') ";
    }

    $sitelist = url::requestToAny('site');
    $sitelist = explode(',', $sitelist);

    $filteredGroupName = $gname;
    $checkname = GRP_CheckGroupPresent($filteredGroupName, $pdo);

    $stm1 = $pdo->prepare("select dataid from " . $GLOBALS['PREFIX'] . "asset.DataName where name = ?");
    $stm1->execute([$DataName]);
    $res1 = $stm1->fetch(PDO::FETCH_ASSOC);

    $dataid = $res1['dataid'];

    //StoreData in ".$GLOBALS['PREFIX']."asset.assetFilters
    if ($action == 'edit') {
        $s1 = "update " . $GLOBALS['PREFIX'] . "asset.assetFilters set site =? , stringVal = ? , dataid =? , str = ?, grptype =?, operator = ? where grpname = ?";
        $sr1 = $pdo->prepare($s1);
        $sr1->execute([url::requestToAny('site'), $str, $dataid, $assetFilter, 'Dynamic', $condition, $filteredGroupName]);
    } else {
        $s1 = "INSERT INTO " . $GLOBALS['PREFIX'] . "asset.assetFilters (`site`, `grpname`, `stringVal`, `dataid`, `str`, `grptype` , `operator`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sr1 = $pdo->prepare($s1);
        $sr1->execute([url::requestToAny('site'), $filteredGroupName, $strVal1, $dataid, $assetFilter, 'Dynamic', $condition]);
    }

    $in  = str_repeat('?,', safe_count($sitelist) - 1) . '?';
    $stm2 = $pdo->prepare("select distinct A.machineid as machineid from " . $GLOBALS['PREFIX'] . "asset.AssetData A join " . $GLOBALS['PREFIX'] . "asset.Machine M on A.machineid = M.machineid
    where A.dataid = $dataid  and M.cust in ($in) $filterCondition");
    $stm2->execute($sitelist);
    $grplist = $stm2->fetchAll(PDO::FETCH_ASSOC);

    $grpmachlist = array();
    foreach ($grplist as $k => $v) {
        $sql = $pdo->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where host = (select host from " . $GLOBALS['PREFIX'] . "asset.Machine where cust in ('NH_Testing') and machineid = ?)");
        $sql->execute([$v['machineid']]);
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        array_push($grpmachlist, $res['id']);
    }

    if ($checkname == '') {
        $auditRes = create_auditLog('Group', 'Create', 'Success', $_REQUEST);
        $recordlist = GRP_AddManualGroup($key, $pdo, $filteredGroupName, $grpCategory, $username, $grpmachlist, $global, $userList, "Dynamic Group");
    } else if ($checkname != '' && $action == 'edit') {
        $auditRes = create_auditLog('Group', 'Update', 'Success', $_REQUEST);
        $recordlist = GRP_UpdateManualGroup($key, $pdo, $filteredGroupName, $grpCategory, $username, $grpmachlist, $global, $userList, "Dynamic Group");
    } else {
        $auditRes = create_auditLog('Group', 'Create', 'Failed', $_REQUEST);
        $recordlist = array('status' => 'Failed');
    }

    echo json_encode($recordlist);
}

function create_AdvanceGrpCensus()
{
    $key = '';
    $pdo = pdo_connect();
    $gname_temp = url::postToText('gname');
    $action = url::postToAny('action');

    if (preg_match('/\s/', $gname_temp)) {
        $gname = str_replace(' ', '_', $gname_temp);
    } else {
        $gname = $gname_temp;
    }

    //New Values
    $userList = url::requestToAny('userlist');
    $str = url::requestToAny('str');
    $sitelist = url::requestToAny('site');
    $sitelist = explode(',', $sitelist);

    $in  = str_repeat('?,', safe_count($sitelist) - 1) . '?';
    $stm2 = $pdo->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where site in ($in) and host like '%$str%'");
    $stm2->execute($sitelist);
    $grplist = $stm2->fetchAll(PDO::FETCH_ASSOC);

    $grpmachlist = array();
    foreach ($grplist as $k => $v) {
        array_push($grpmachlist, $v['id']);
    }

    // $grpmachlist = implode(',', $machineList);

    $grpCategory = 'Wiz_SCOP_MC';
    $username = $_SESSION["user"]["username"];
    $eid = $_SESSION['user']['cId'];
    $global = 1; //url::postToText('global');
    $userList = url::postToText('userlist');


    $filteredGroupName = $gname;
    $checkname = GRP_CheckGroupPresent($filteredGroupName, $pdo);
    //StoreData in ".$GLOBALS['PREFIX']."asset.assetFilters
    if ($action == 'edit') {
        $s1 = "update " . $GLOBALS['PREFIX'] . "asset.assetFilters set site =? , stringVal = ? , dataid =? , str = ?, grptype =?, operator = ? where grpname = ?";
        $sr1 = $pdo->prepare($s1);
        $sr1->execute([url::requestToAny('site'), $str, '', '', 'Census', '', $filteredGroupName]);
    } else {
        $s1 = "INSERT INTO " . $GLOBALS['PREFIX'] . "asset.assetFilters (`site`, `grpname`, `stringVal`, `dataid`, `str`, `grptype` , `operator`) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sr1 = $pdo->prepare($s1);
        $sr1->execute([url::requestToAny('site'), $filteredGroupName, $str, '', '', 'Census', '']);
    }

    if ($checkname == '') {
        $auditRes = create_auditLog('Group', 'Create', 'Success', $_REQUEST);
        $recordlist = GRP_AddManualGroup($key, $pdo, $filteredGroupName, $grpCategory, $username, $grpmachlist, $global, $userList, "Census Group");
    } else if ($checkname != '' && $action == 'edit') {
        $auditRes = create_auditLog('Group', 'Update', 'Success', $_REQUEST);
        $recordlist = GRP_UpdateManualGroup($key, $pdo, $filteredGroupName, $grpCategory, $username, $grpmachlist, $global, $userList, "Census Group");
    } else {
        $auditRes = create_auditLog('Group', 'Create', 'Failed', $_REQUEST);
        $recordlist = array('status' => 'Failed');
    }

    echo json_encode($recordlist);
}

function SaveConfigurations($newarray)
{
    $gname = $newarray['groupname'];
    $str = $newarray['string'];
    $cond = $newarray['condition'];
    $strVal = $newarray['stringvalue'];
    $sitelist = $newarray['sitelist'];
    $gname = $newarray['groupname'];

    $pdo = pdo_connect();

    $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "asset.`SavedAdvGroupConfig` (`string`, `condition`, `stringvalue`, `site`, `groupname`) VALUES (?,?,?,?,?)");
    $sql->execute([$str, $cond, $strVal, $sitelist, $gname]);
    $pdo->lastInsertId();
}

function adv_groupviewDetail()
{

    $key = '';
    $pdo = pdo_connect();
    $groupid = url::requestToInt('selid');
    $res = checkGroupAccess($groupid, 'grpid');
    if (!$res) {
        $recordList = array("msg" => "Permission Denied");
        echo json_encode($recordList);
        exit;
    }

    $machineslist = GRP_ViewGroupDetail($key, $groupid, $pdo);
    $totalRecords = safe_count($machineslist);

    if ($totalRecords > 0) {

        foreach ($machineslist as $key => $value) {
            $lastEvent = ($value['last'] != '') ? date('m/d/Y H:i A', $value['last']) : '';
            $lastEvent = '<p class="ellipsis" title="' . $lastEvent . '">' . $lastEvent . '</p>';
            $host = '<p class="ellipsis" title="' . $value['host'] . '">' . $value['host'] . '</p>';
            $grpname = UTIL_GetTrimmedGroupName($value['site']);
            $grp = '<p class="ellipsis" title="' . $grpname . '">' . $grpname . '</p>';
            $recordList[] = array($host, $grp, $lastEvent);
        }
    } else {
        $recordList = array();
    }

    echo json_encode($recordList);
}

function deleteAdvGroup()
{
    $pdo = pdo_connect();
    $grpid = url::requestToAny('selid');

    $res = checkGroupAccess($grpid, 'grpid');
    if (!$res) {
        echo "Permission Denied";
        exit;
    }

    $sql = $pdo->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ?");
    $sql->execute([$grpid]);
    $res = $sql->fetch();
    $selmgroupuniq = $res['mgroupuniq'];

    $deleteGesql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap where mgroupuniq = ? ");
    $deleteGesql->execute([$selmgroupuniq]);
    $pdo->lastInsertId();

    $deletemgsql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ?");
    $deletemgsql->execute([$grpid]);
    $deletemgres = $pdo->lastInsertId();

    $delgmapsql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupid = ?");
    $delgmapsql->execute([$grpid]);
    $pdo->lastInsertId();

    $auditRes = create_auditLog('Advanced Group', 'Delete', 'Success', $_REQUEST);

    echo json_encode("Success");
    $auditRes = create_auditLog('Advanced Group', 'Delete', 'Failed', $_REQUEST);
}

function refreshAdvGroup()
{
    $pdo = pdo_connect();
    $grpid = url::requestToAny('selectedGrp');
    $grpname = url::requestToAny('groupname');

    $seldata = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "asset.`SavedAdvGroupConfig` where groupname = ?");
    $seldata->execute([$grpname]);
    $getdata = $seldata->fetch();

    $username = $_SESSION['user']['username'];
    $gname_temp = url::requestToAny('gname');
    if (preg_match('/\s/', $grpname)) {
        $gname = str_replace(' ', '_', $grpname);
    } else {
        $gname = $grpname;
    }
    $userList = $getdata['userlist'];
    $str = $getdata['str'];
    $cond = $getdata['condition'];
    $strVal = $getdata['strval'];
    $sitelist = $getdata['site'];
    $grpCategory = 'Wiz_SCOP_MC';
    $time = time();

    $filteredGroupName = $gname;

    $assetgrppsnt = GRP_GnamePrsnt($pdo, $filteredGroupName);
    $astctgry = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

    if ($assetgrppsnt['mgroupid'] == '') {
        $assetsucs = array();
        $assetname = $assetsucs['name'];

        $sqlinsert = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,"
            . "boolstring,mgroupuniq,mcatuniq) select '$filteredGroupName','$username',1,1,4,$time,0,0,0,'$assetname',"
            . "md5(concat(mcatuniq,',','$filteredGroupName')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?;");
        $sqlinsert->execute([$astctgry]);

        $result = $pdo->lastInsertId();

        $lastinsertid = inserted_id($pdo);
        $groupinsert = GRP_GroupPresent($lastinsertid, $pdo);
        $machineData = GRP_fetchMachineList($str, $cond, $strVal, $sitelist);
        $assetcensus = GRP_getcensusid($pdo, $machineData, $sitelist);

        foreach ($assetcensus as $key => $value) {

            $id = $value['id'];

            $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id=? AND mcatid=? AND mgroupid=?; ");
            $sql->execute([$id, $astctgry, $lastinsertid]);

            $pdo->lastInsertId();

            $insDefSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) "
                . "VALUES (?,?,?)");
            $insDefSql->execute([$lastinsertid, $filteredGroupName, $username]);
            $pdo->lastInsertId();

            if ($userList != '') {
                $userSql = $pdo->prepare("select username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN (?)");
                $userSql->execute([$userList]);
                $userRes = $userSql->fetchAll();
                foreach ($userRes as $value) {
                    $insSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) "
                        . "VALUES (?,?, '" . $value['username'] . "')");
                    $insSql->execute([$lastinsertid, $filteredGroupName]);
                    $pdo->lastInsertId();
                }
            }
        }
        $recordData = array('msg' => 'success');
    } else {
        $machineData = GRP_fetchMachineList($str, $cond, $strVal, $sitelist);
        $assetcensus = GRP_getcensusid($pdo, $machineData, $sitelist);
        $lastinsertid = $assetgrppsnt['mgroupid'];
        foreach ($assetcensus as $key => $value) {

            $id = $value['id'];

            $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories,
                    " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id=? AND mcatid=? AND mgroupid=?; ");
            $sql->execute([$id, $astctgry, $lastinsertid]);

            $pdo->lastInsertId();

            $insDefSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) "
                . "VALUES (?,?,?)");
            $insDefSql->execute([$lastinsertid, $filteredGroupName, $username]);
            $pdo->lastInsertId();

            if ($userList != '') {
                $userSql = $pdo->prepare("select username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($userList)");
                $sql->execute();
                $userRes = $sql->fetchAll($userSql, $pdo);
                foreach ($userRes as $value) {
                    $insSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) "
                        . "VALUES (?,?, '" . $value['username'] . "')");
                    $insSql->execute([$lastinsertid, $filteredGroupName]);
                    $pdo->lastInsertId();
                }
            }
        }
        $recordData = array('msg' => 'success');
    }
    create_auditLog('Advanced Group', 'Refresh', 'Success');

    echo json_encode($recordData);
}



function updateAdvGrpDetails()
{
    $pdo = pdo_connect();

    $advgrpid = url::requestToAny('advgrpid');
    $advgrpname = url::requestToAny('advgrpname');
    $userlist = url::requestToAny('userlist');
    $lgdusername = $_SESSION['user']['username'];
    $timenow = time();

    $userlist = explode(",", $userlist);

    $in  = str_repeat('?,', safe_count($userlist) - 1) . '?';
    $userstmt = $pdo->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid IN ($in)");
    $userstmt->execute($userlist);
    $userdata = $userstmt->fetchAll(PDO::FETCH_ASSOC);

    $delstmt = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupid = ? and username != ?");
    $delstmt->execute([$advgrpid, $lgdusername]);

    $updstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.GroupMappings set modifiedby = ?, modifiedtime = ? where groupid = ? and username = ?");
    $updstmt->execute([$lgdusername, $timenow, $advgrpid, $lgdusername]);

    foreach ($userdata as $value) {
        $instmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, "
            . "username, modifiedby, modifiedtime) values (?, ?, ?, ?, ?)");
        $instmt->execute([$advgrpid, $advgrpname, $value['username'], $lgdusername, $timenow]);
    }
    ob_clean();
    create_auditLog('Advanced Group', 'Modification', 'Success', $_REQUEST);

    echo 'success';
}


//New Site Addition Function

function getDefaultSiteDetails()
{
    $pdo = pdo_connect();

    global $dash_emailSubject;
    global $dash_emailSender;

    //Fetch data from install.skuOfferings
    $consoleSql1 = $pdo->prepare("select name,amount,quantity,billingcycle from " . $GLOBALS['PREFIX'] . "install.skuOfferings");
    $consoleSql1->execute();
    $data1 = $consoleSql1->fetch(PDO::FETCH_ASSOC);
    $skuName = $data1['name'];
    $amount = $data1['amount'];
    $quantity = $data1['quantity'];
    $billinCycle = $data1['billingcycle'];

    $recordList = array(
        "emailSubject" => $dash_emailSubject,
        "emailSender" => $dash_emailSender,
        "skuName" => $skuName,
        "amount" => $quantity . "/" . $amount,
        "billingcycle" => $billinCycle
    );
    echo json_encode($recordList);
}

function addNewSite()
{

    global $dash_tanentId;
    global $dash_deployId;
    global $wsurl;
    global $dash_domainName;
    global $dash_emailBounce;
    global $dash_emailHeaders;

    global $dash_msgTxt;
    global $dash_delay;
    global $dash_startupid;
    global $dash_followonid;

    global $dash_delayon;
    global $dash_uninstall;
    global $dash_deploypath32;
    global $dash_deploypath64;
    global $dash_fcmUrl;

    global $dash_client_android;
    global $dash_client_mac;
    global $dash_client_ios;
    global $dash_client_linux;
    global $dash_proxy;

    $dash_downloadUrl  = 'https://' . $_SERVER["HTTP_HOST"] . "/Dashboard/Provision/download";
    $wsurl = 'wss://' . $wsurl;
    //Fetch Data from install.Servers
    $data = NanoDB::find_one("select * from " . $GLOBALS['PREFIX'] . "install.Servers limit 1");

    $client_32_name = url::postToText('client32_name');
    $client_64_name = url::postToText('client64_name');
    $dash_brandingUrl = url::postToText('branding_url');

    // $client_32_name = $data['client_32_name'] == '' ? $dash_client_32 : $data['client_32_name'];
    // $client_64_name = $data['client_64_name'] == '' ? $dash_client_64 : $data['client_64_name'];
    $client_android_name = $data['client_android_name'] == '' ? $dash_client_android : $data['client_android_name'];
    $client_mac_name = $data['client_mac_name'] == '' ? $dash_client_mac : $data['client_mac_name'];
    $client_ios_name = $data['client_ios_name'] == '' ? $dash_client_ios : $data['client_ios_name'];
    $client_linux_name = $data['client_linux_name'] == '' ? $dash_client_linux : $data['client_linux_name'];

    //Fetch Data from install.skuOfferings
    $data2 = NanoDB::find_one("select sid from " . $GLOBALS['PREFIX'] . "install.skuOfferings");
    $skuId = $data2['sid'];

    $deploy_sitename = url::postToText('deploy_sitename');
    $deploy_emailsub = url::postToText('deploy_emailsub');
    $deploy_emailsender = url::postToText('deploy_emailsender');



    $installuserId = $_SESSION['user']['userid'];
    $username = $_SESSION['user']['username'];
    $emailId = $_SESSION["user"]["cemail"];
    $firstcontact = time();

    //Check for duplicate site name

    $siteChkData = NanoDB::find_one('select * from ' . $GLOBALS['PREFIX'] . 'install.Sites where sitename = ? and serverid = ? and cid = ? limit 1', [$deploy_sitename, $dash_tanentId, $dash_deployId]);

    if ($siteChkData) {
        $response = array("msg" => "The Site Name already exists for the Deployement");
    } else {
        $siteChkData = NanoDB::find_one('select * from ' . $GLOBALS['PREFIX'] . 'install.Sites where sitename = ? limit 1', [$deploy_sitename]);

        if ($siteChkData) {
            $siteName = $deploy_sitename . '_' . $dash_deployId;
        } else {
            $siteName = $deploy_sitename;
        }

        $regcode = gen_regcode($siteName);

        NanoDB::query("insert into " . $GLOBALS['PREFIX'] . "core.Customers (username, customer) values (?, ?)", [$username, $siteName]);

        $params = [
            $siteName, $dash_domainName, $installuserId, $emailId, $skuId, $wsurl, $dash_tanentId, $dash_deployId, $dash_proxy, $dash_startupid,
            $dash_followonid, $dash_uninstall, $dash_delay, $dash_delayon, $dash_deploypath32, $dash_deploypath64, $dash_fcmUrl,
            $dash_emailBounce, $dash_downloadUrl, $dash_msgTxt, $deploy_emailsub, $deploy_emailsender, $dash_emailHeaders, $regcode, $firstcontact, $dash_brandingUrl,
            $client_32_name, $client_64_name, $client_android_name, $client_mac_name, $client_ios_name, $client_linux_name
        ];

        // insert into Sites table [1]
        $q = "INSERT INTO " . $GLOBALS['PREFIX'] . "install.Sites
        (sitename,domain,installuserid,email,skuids,wsurl,serverid,cid,
        proxy,startupid,followonid,uninstall,delay,delayon,deploypath32,deploypath64,fcmUrl,
        emailbounce,urldownload,messagetext,emailsubject,emailsender,emailxheaders,regcode,
        firstcontact,brandingurl,client_32_name,client_64_name,client_android_name,
        client_mac_name,client_ios_name,client_linux_name) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $res = NanoDB::insert($q, $params);
        logs::log("addNewSite:" . $q, $params);
        TokenChecker::calcSites($siteName,  'sitename');

        // MachineGroups::createNewMgroupuniqId($siteName, MachineCategories::getCatIdByName('Site'));

        $sesql = "insert into " . $GLOBALS['PREFIX'] . "install.Siteemail set siteid = ?, installuserid = ?, userid = ?, "
            . "email = ?, createdtime=?";
        NanoDB::query($sesql, [$res, $installuserId, $installuserId, $emailId, $firstcontact]);

        if ($res) {
            $response = array("msg" => "success");
        } else {
            $response = array("msg" => "failed");
        }
    }
    echo json_encode($response);
}


function gen_regcode($sitename, $regen = 0)
{
    $step1 = md5($sitename);
    $step2 = hexdec(substr($step1, 0, 8));
    $positive = hexdec("7FFFFFFF");
    $step3 = $step2 & $positive;
    $step4 = $step3 % 1000000000;
    $servergen = hexdec("FFFFFFF0");
    $step5 = $step4 & $servergen;

    if ($regen) { // came back b/c previously generated reg code was a dup of existing.
        $log = "install: The regcode generated for site '$sitename' is a duplicate" .
            " of existing; generating new regcode.";
        logs::log(__FILE__, __LINE__, $log, 0);

        // seed with microseconds
        srand(make_seed());
        $rand = rand(1, 99);
        $incr = $rand * 16;   // use 16, not 1, since lowest 4 bits are reserved
        $step5 += $incr;
        $step5 = $step5 % 1000000000;  // in case it got too long
    }

    // pad number if less than 9 digits
    $number = sprintf("%09d", $step5);

    // get first 9 digits
    $dig1 = substr($number, 0, 1);
    $dig2 = substr($number, 1, 1);
    $dig3 = substr($number, 2, 1);
    $dig4 = substr($number, 3, 1);
    $dig5 = substr($number, 4, 1);
    $dig6 = substr($number, 5, 1);
    $dig7 = substr($number, 6, 1);
    $dig8 = substr($number, 7, 1);
    $dig9 = substr($number, 8, 1);

    // generate check digit
    $intermediate = ($dig1 * 10) + ($dig2 * 9) + ($dig3 * 8) + ($dig4 * 7) + ($dig5 * 6) + ($dig6 * 5) + ($dig7 * 4) + ($dig8 * 3) + ($dig9 * 2);
    $remainder = $intermediate % 11;
    $checkdig = (11 - $remainder) % 11;
    if ($checkdig == 10)
        $checkdig = 'X';

    $regcode = $dig1 . $dig2 . $dig3 . $dig4 . $dig5 . $dig6 . $dig7 . $dig8 . $dig9 . $checkdig;

    return $regcode;
}
