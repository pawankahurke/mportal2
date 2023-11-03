<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-db.php';
include_once 'l-sql.php';
include_once 'l-gsql.php';
include_once 'l-rcmd.php';
require_once '../include/common_functions.php';
include_once '../include/NH-Config_API.php';
include_once '../lib/l-setTimeZone.php';
include_once '../JSONSchema/schemaWizFunc.php';

nhRole::dieIfnoRoles(['profilewizard']); // roles: profilewizard


//Replace $routes['post'] with if else
if (url::postToText('function') === 'save_ProfileDetails') { // roles: profilewizard
    save_ProfileDetails();
} else if (url::postToText('function') === 'update_VariablesData') { // roles: profilewizard
    update_VariablesData();
} else if (url::postToText('function') === 'check_ProfileAttachStatus') { // roles: profilewizard
    check_ProfileAttachStatus();
} else if (url::postToText('function') === 'attach_ProfileData') { // roles: profilewizard
    attach_ProfileData();
} else if (url::postToText('function') === 'duplicate_Profile') { // roles: profilewizard
    duplicate_Profile();
} else if (url::postToText('function') === 'update_ProfileDetails') { // roles: profilewizard
    update_ProfileDetails();
} else if (url::postToText('function') === 'v_render_ProfileDetails') { // roles: profilewizard
    v_render_ProfileDetails();
} else if (url::postToText('function') === 'v_render_ProfileDetails') { // roles: profilewizard
    v_render_ProfileDetails();
} else if (url::postToText('function') === 'v_render_ClientProfileDetails') { // roles: profilewizard
    v_render_ClientProfileDetails();
} else if (url::postToText('function') === 'update_ClientProfileData') { // roles: profilewizard
    update_ClientProfileData();
} else if (url::postToText('function') === 'e_update_ClientProfileData') { // roles: profilewizard
    e_update_ClientProfileData();
} else if (url::postToText('function') === 'render_ProfileDetails') { // roles: profilewizard
    render_ProfileDetails();
} else if (url::postToText('function') === 'render_ClientProfileDetails') { // roles: profilewizard
    render_ClientProfileDetails();
} else if (url::postToText('function') === 'delete_Profile') { // roles: profilewizard
    delete_Profile();
}


//Replace $routes['get'] with if else
if (url::postToText('function') === 'check_ProfileAccess') { // roles: profilewizard
    check_ProfileAccess();
} else if (url::postToText('function') === 'e_update_ProfileData') { // roles: profilewizard
    e_update_ProfileData();
} else if (url::postToText('function') === 'e_render_ProfileDetails') { // roles: profilewizard
    e_render_ProfileDetails();
} else if (url::postToText('function') === 'e_render_LevelTwoProfile') { // roles: profilewizard
    e_render_LevelTwoProfile();
} else if (url::postToText('function') === 'get_ProfileWizardDetails') { // roles: profilewizard
    get_ProfileWizardDetails();
} else if (url::postToText('function') === 'update_ProfileData') { // roles: profilewizard
    update_ProfileData();
} else if (url::postToText('function') === 'render_LevelTwoProfile') { // roles: profilewizard
    render_LevelTwoProfile();
} else if (url::postToText('function') === 'getDartName') { // roles: profilewizard
    getDartName();
} else if (url::postToText('function') === 'viewSelectedData') { // roles: profilewizard
    view_Selected_Data();
} else if (url::postToText('function') === 'e_save_ProfileDetails') {
    e_save_ProfileDetails();
}

global $mainVarArr;

function getDartName()
{
    $pdo = pdo_connect();
    $dartno = url::postToAny('dartId');

    $sql = "select name from " . $GLOBALS['PREFIX'] . "core.Scrips where num = ?";
    $sqlWN = $pdo->prepare($sql);
    $sqlWN->execute([$dartno]);
    $resWN = $sqlWN->fetch(PDO::FETCH_ASSOC);

    $result = array("dartName" => $resWN['name']);
    echo json_encode($result);
}

function view_Selected_Data()
{
    $selected = url::postToInt('selVal');

    $siteList = $_SESSION['user']['site_list']; // TODO: not work, needs relogin or restarting container
    $groupList = PRWZ_getGroupDetails();

    $data = NanoDB::find_one("select scopvalue from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?", null, [$selected]);

    $Data = safe_json_decode($data['scopvalue'], true);
    $siteData = $Data['sites'];
    $groupData = $Data['groups'];

    $siteOption = "";
    $groupOption = "";

    foreach ($siteList as $value) {
        if (!is_null($siteData) && in_array($value, $siteData)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $siteOption .= "<option value='$value' $selected>$value</option>";
    }

    foreach ($groupList as $val) {
        $value = $val['name'];
        if (!is_null($groupData) && in_array($value, $groupData)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $groupOption .= "<option value='$value' $selected>$value</option>";
    }

    $responseArr = array("sites" => $siteOption, "groups" => $groupOption);

    echo json_encode($responseArr);
}

function PRWZ_getProfileName($pw_id, $type)
{
    $pdo = pdo_connect();

    if ($type == 'view') {
        $sql = $pdo->prepare("select pid, profilename from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
        $sql->execute([$pw_id]);
    } else if ($type == 'edit') {
        $sql = $pdo->prepare("select pid, profilename from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ? and createdby = ?");
        $sql->execute([$pw_id, $_SESSION['user']['username']]);
    }

    $data = $sql->fetch(PDO::FETCH_ASSOC);

    return $data['profilename'];
}

function PRWZ_getProfileSeqToken($pw_id, $type)
{
    $pdo = pdo_connect();

    if ($type == 'view') {
        $data['pwseqtoken'] = uniqid('pwt');
    } else if ($type == 'edit') {
        $sql = $pdo->prepare("select pwseqtoken from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
        $sql->execute([$pw_id]);
        $data = $sql->fetch(PDO::FETCH_ASSOC);
    }


    return $data['pwseqtoken'];
}

function check_ProfileAccess()
{
    $pdo = pdo_connect();

    $profid = url::getToText('profid');

    $sql = $pdo->prepare("select pid, profilename from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $sql->execute([$profid]);
    $data = $sql->fetch(PDO::FETCH_ASSOC);

    if ($data && $data['profilename'] == 'Default') {
        $msg = 'no';
    } else {
        $msg = 'ok';
    }

    ob_clean();
    echo $msg;
}

function get_ProfileWizardDetails()
{
    $pdo = pdo_connect();

    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');


    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    $notifSearch = url::postToText('notifSearch');

    if ($notifSearch != '') {
        $whereSearch = " and (profilename like '%$notifSearch%'
        OR createdby like '%$notifSearch%'
        OR createdtime like '%$notifSearch%'
        OR modifiedtime like '%$notifSearch%'
        OR scopvalue like '%$notifSearch%'
        ) ";;
    } else {
        $whereSearch = ''; //'or profilename = "Default"';
    }

    if ($orderVal != '') {
        if ($orderVal == 'sites') {
            $orderStr = "order by scopvalue->>'$.sites' " . $sortVal;
        } else if ($orderVal == 'groups') {
            $orderStr = "order by scopvalue->>'$.groups' " . $sortVal;
        } else {
            $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
        }
    } else {
        $orderStr = 'order by profilename asc';
    }

    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }

    $draw = 1;
    $recordList = [];

    $sql = $pdo->prepare("select pid, profilename, createdby, createdtime, scoplevel, scopvalue, "
        . "usagelevel, modifiedtime from " . $GLOBALS['PREFIX'] . "profile.profileJson where createdby = ? $whereSearch $orderStr $limitStr");
    $sql->execute([$_SESSION['user']['username']]);
    $res = $sql->fetchAll();

    $sql2 = $pdo->prepare("select pid, profilename, createdby, createdtime, scoplevel, scopvalue, "
        . "usagelevel, modifiedtime from " . $GLOBALS['PREFIX'] . "profile.profileJson where createdby = ? $whereSearch $orderStr");
    $sql2->execute([$_SESSION['user']['username']]);
    $totCount = safe_count($sql2->fetchAll());

    (isset($_SESSION['user']['usertimezone']) && !empty($_SESSION['user']['usertimezone']) ) ? date_default_timezone_set($_SESSION['user']['usertimezone']) : '';

    if (safe_sizeof($res) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $nocName = '';
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
        $dataArr['html'] = FormatProfileMYSQL($res);
        echo json_encode($dataArr);
    }
}

function FormatProfileMYSQL($user_res)
{
    $key = '';
    $conn = pdo_connect();
    $userStatusArray = array("Active" => 'Active', "Disabled" => 'In Active', "In Active" => 'Pending');
    $i = 0;
    foreach ($user_res as $key => $value) {
        $profid = $value['pid'];
        $profname = $value['profilename'];
        $createdby = $value['createdby'];

        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $ctime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['createdtime'], "m/d/Y h:i A");
            $mtime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['modifiedtime'], "m/d/Y h:i A");
        } else {
            $ctime = date("m/d/Y h:i A", $value['createdtime']);
            $mtime = date("m/d/Y h:i A", $value['modifiedtime']);
        }

        $createdtime = isset($value['createdtime']) ? $ctime : '';
        $scoplevel = $value['scoplevel'];
        $modifiedtime = isset($value['modifiedtime']) ? $mtime : '-';
        $profscopdata = safe_json_decode($value['scopvalue'], true);

        $linkedsites = '-';
        $linkedgroups = '-';
        if (isset($profscopdata['sites']) && safe_count($profscopdata['sites']) > 0) {
            $linkedsites = implode(',', $profscopdata['sites']);
        }
        if (isset($profscopdata['groups']) && safe_count($profscopdata['groups']) > 0) {
            $linkedgroups = implode(',', $profscopdata['groups']);
        }

        $usagelevel = $value['usagelevel'];
        if ($usagelevel == 0) {
            $usageleveldata = 'Yes';
        } else {
            $usageleveldata = 'No';
        }

        $recordList[$i][] =  $profname;
        $recordList[$i][] =  $createdby;
        $recordList[$i][] =  $createdtime;
        $recordList[$i][] =  $modifiedtime;
        $recordList[$i][] =  $linkedsites; //str_replace(',', PHP_EOL, $linkedsites) ;
        $recordList[$i][] =  $linkedgroups; //str_replace(',', PHP_EOL, $linkedgroups);
        $recordList[$i][] =  $profid;
        $i++;
    }
    return $recordList;
}

function PRWZ_getProfileDetails($pw_id = 0)
{
    $pdo = pdo_connect();
    if (!$pw_id) {
        $sql = $pdo->prepare("select pid, profilename, profilevaluejson from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = ?");
        $sql->execute(['Default']);
    } else {
        $sql = $pdo->prepare("select pid, profilename, profilevaluejson from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
        $sql->execute([$pw_id]);
    }
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    $profileJSONData = safe_json_decode($res['profilevaluejson'], TRUE);

    $_SESSION['pwdefdata'] = $profileJSONData;

    return $profileJSONData;
}

function PRWZ_getTilesList()
{
    $dartData = [];
    $pdo = pdo_connect();

    $dartres = NanoDB::find_many("select opsys, dartlist from " . $GLOBALS['PREFIX'] . "profile.profileDarts where opsys in (?,?,?,?,?,?)", null, ['windows', 'android', 'mac', 'ios', 'linux', 'ubuntu']);

    $dartList = "";
    if (safe_count($dartres) > 0) {
        foreach ($dartres as $key => $value) {
            $dartList .= $value['dartlist'] . ',';
        }
    }
    $dartList = rtrim($dartList, ',');

    $dartListArr = explode(',', $dartList);
    foreach ($dartListArr as $value) {
        $dartsql = $pdo->prepare("select num, name from " . $GLOBALS['PREFIX'] . "core.Scrips where num = ? limit 1");
        $dartsql->execute([$value]);
        $dartsqlres = $dartsql->fetch(PDO::FETCH_ASSOC);

        if ($dartsqlres && safe_count($dartsqlres) > 0) {
            $dartData[$value] = 'Dart ' . $value . ' - ' . $dartsqlres['name'];
        }
    }
    return $dartData;
}

function createTree(&$list, $parent)
{
    $tree = array();
    foreach ($parent as $l) {
        if (isset($list[$l['mid']])) {
            $l['children'] = createTree($list, $list[$l['mid']]);
        }
        $tree[] = $l;
    }
    return $tree;
}

function getFormattedArray($profileJSONData)
{
    $finalArray = [];
    foreach ($profileJSONData as $key => $value) {
        $finalArray[$key]['status'] = $value['Enable/Disable'];
        $finalArray[$key]['mid'] = $value['mid'];
        $finalArray[$key]['parentid'] = $value['parentId'];
        $finalArray[$key]['name'] = $value['menuItem'];
        $finalArray[$key]['type'] = $value['type'];
        $finalArray[$key]['os'] = $value['OS'];
    }

    $new = array();
    foreach ($finalArray as $a) {
        $new[$a['parentid']][] = $a;
    }
    $tree = createTree($new, array($finalArray[0]));

    return $tree;
}

function getFormattedArrayEnabled($profileJSONData)
{
    $finalArray = [];
    foreach ($profileJSONData as $key => $value) {
        if ($value['Enable/Disable'] == 1 || $value['Enable/Disable'] == 3) {
            $finalArray[$key]['status'] = $value['Enable/Disable'];
            $finalArray[$key]['mid'] = $value['mid'];
            $finalArray[$key]['parentid'] = $value['parentId'];
            $finalArray[$key]['name'] = $value['menuItem'];
            $finalArray[$key]['type'] = $value['type'];
            $finalArray[$key]['os'] = $value['OS'];
        }
    }

    $new = array();
    foreach ($finalArray as $a) {
        $new[$a['parentid']][] = $a;
    }
    $tree = createTree($new, array($finalArray[0]));

    return $tree;
}

function update_ProfileData()
{
    $nProData = [];
    $lev_2_pid = [];
    $profData = url::postToAny('pdata');
    $dProData = $_SESSION['pwdefdata'];

    if(!empty($dProData)){
        foreach ($dProData as $dkey => $value) {
            if (in_array($value['mid'], $profData)) {
                $value['Enable/Disable'] = 3;
                $nProData[$dkey] = $value;
                $lev_2_pid[$dkey] = $value['parentId'];
            } else {
                if (($value['type'] == "L0" && $value['parentId'] == 0) || ($value['menuItem'] == 'Troubleshooters')) {
                    $value['Enable/Disable'] = 1;
                } else {
                    $value['Enable/Disable'] = 0;
                }
                $nProData[$dkey] = $value;
            }
        }
    }
    foreach ($nProData as $nkey => $nproval) {
        if ($nproval['type'] == 'L1') {
            if (in_array($nproval['page'], $lev_2_pid)) {
                $nproval['Enable/Disable'] = 3;
                $nProData[$nkey] = $nproval;
            }
        }
    }

    $_SESSION['pwnewdata'] = $nProData;
    $_SESSION['pwprofdefdata'] = $nProData;
    $_SESSION['ProfileStateOne'] = $nProData;

    echo json_encode($nProData);
}

function update_ClientProfileData()
{
    $cliProfData = url::postToArrayWithException('cliprofdata'); // !!!!

    foreach ($_SESSION['ProfileStateOne'] as $key => $value) {
        if (in_array($value['mid'], $cliProfData)) {
            $value['Enable/Disable'] = 1;
            $cliProfDetails[$key] = $value;
        } else {
            $cliProfDetails[$key] = $value;
        }
    }

    $_SESSION['pwnewdata'] = $cliProfDetails;
    $_SESSION['pwprofdefdata'] = $cliProfDetails;
    echo 'Success';
}

function update_VariablesData()
{

    $vardata = $_POST['vdata'];
    $dartno = url::postToInt('dartno');
    $dartindx = url::postToText('dartindx');
    $dartseqn = url::postToText('dartseqn');
    $pwseqToken = url::postToText('dartToken');

    NanoDB::query("DELETE FROM " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence WHERE seq_token = ? AND seq_dartno = ? AND seq_index = ?", [$pwseqToken, $dartno, $dartindx]);

    $res = NanoDB::find_one("select variabledata from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = 'Default' limit 1");
    $defvardata = $res['variabledata'];

    $checkvdata = end(explode('#NXTVAR#', $defvardata));
    $lastvarid = explode(':', explode(PHP_EOL, $checkvdata)[1])[1];

    foreach ($vardata as $varname => $vardata) {
        NanoDB::query(
            "insert into " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence (seq_dartno, seq_index, seq_order, seq_varid, seq_token, seq_name, seq_value) values (?,?,?,?,?,?,?)",
            [$dartno, $dartindx, $dartseqn, 0, $pwseqToken, $varname, $vardata]
        );
    }

    $all_profile_vars = NanoDB::find_many("select pw_seqid from " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence where seq_token = ? order by seq_index, seq_order, seq_dartno, seq_name like \"%RunNow%\";", [$pwseqToken]);

    $newvarid = $lastvarid + 1;
    foreach ($all_profile_vars as $key => $value) {
        NanoDB::query("update " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence set seq_varid = ? where pw_seqid = ?", [$newvarid, $value['pw_seqid']]);
        $newvarid++;
    }

    echo 'DONE';
}

function render_ProfileDetails()
{
    $pdo = pdo_connect();

    $custProfArr = [];
    $pmidArr = [];
    $outputArr = [];
    $profTileData = $_POST;

    $nProData = $_SESSION['pwprofdefdata'];
    $totalCount = safe_count($profTileData['tile-name']);
    // $totalCount = count(strip_tags($profTileData['tile-name']));
    if ($totalCount > 0 && $profTileData['tile-name'][0] != '') {
        $cstmCnt = 1;
        $customid = $custTileMid = 2000;
        $customTileName = 'Custom Tiles';

        $custProfArr[0]['Enable/Disable'] = 1;
        $custProfArr[0]['mid'] = ($customid - 1);
        $custProfArr[0]['menuItem'] = $customTileName;
        $custProfArr[0]['type'] = 'L1';
        $custProfArr[0]['parentId'] = 1;
        $custProfArr[0]['profile'] = $customTileName;
        $custProfArr[0]['dart'] = 'NA';
        $custProfArr[0]['variable'] = '';
        $custProfArr[0]['varValue'] = '';
        $custProfArr[0]['shortDesc'] = $customTileName;
        $custProfArr[0]['description'] = $customTileName;
        $custProfArr[0]['tileDesc'] = $customTileName;
        $custProfArr[0]['OS'] = 'common';
        $custProfArr[0]['page'] = $customid;
        $custProfArr[0]['status'] = 'enable';
        $custProfArr[0]['authFalg'] = 'F';
        $custProfArr[0]['usageType'] = 1;
        $custProfArr[0]['dynamic'] = 0;
        $custProfArr[1]['Enable/Disable'] = 1;
        $custProfArr[1]['mid'] = $customid;
        $custProfArr[1]['menuItem'] = $customTileName;
        $custProfArr[1]['type'] = 'L2';
        $custProfArr[1]['parentId'] = $customid;
        $custProfArr[1]['profile'] = $customTileName;
        $custProfArr[1]['dart'] = 'NA';
        $custProfArr[1]['variable'] = '';
        $custProfArr[1]['varValue'] = '';
        $custProfArr[1]['shortDesc'] = $customTileName;
        $custProfArr[1]['description'] = $customTileName;
        $custProfArr[1]['tileDesc'] = $customTileName;
        $custProfArr[1]['OS'] = 'common';
        $custProfArr[1]['page'] = 2;
        $custProfArr[1]['status'] = 'enable';
        $custProfArr[1]['authFalg'] = 'F';
        $custProfArr[1]['usageType'] = 1;
        $custProfArr[1]['dynamic'] = 0;

        for ($i = 0; $i < $totalCount; $i++) {
            $oslist = [];
            $tileName = strip_tags($profTileData['tile-name'][$i]);
            $tileDesc = strip_tags($profTileData['tile-description'][$i]);
            $isClientTile = strip_tags($profTileData['visibility'][$i]);
            $customShowTypeArr[] = strip_tags($profTileData['visibility'][$i]);


            isset($profTileData['os-win'][$i]) ? $oslist[] = strip_tags($profTileData['os-win'][$i]) : '';
            isset($profTileData['os-android'][$i]) ? $oslist[] = strip_tags($profTileData['os-android'][$i]) : '';
            isset($profTileData['os-mac'][$i]) ? $oslist[] = strip_tags($profTileData['os-mac'][$i]) : '';
            isset($profTileData['os-ios'][$i]) ? $oslist[] = strip_tags($profTileData['os-ios'][$i]) : '';
            isset($profTileData['os-linux'][$i]) ? $oslist[] = strip_tags($profTileData['os-linux'][$i]) : '';
            isset($profTileData['os-ubuntu'][$i]) ? $oslist[] = strip_tags($profTileData['os-ubuntu'][$i]) : '';
            $oslist_data = implode(',', $oslist);

            $cstmCnt++;
            $custTileMid = $custTileMid + 1;
            $custProfArr[$cstmCnt]['Enable/Disable'] = $isClientTile;
            $custProfArr[$cstmCnt]['mid'] = $custTileMid;
            $custProfArr[$cstmCnt]['menuItem'] = $tileName;
            $custProfArr[$cstmCnt]['type'] = 'L3';
            $custProfArr[$cstmCnt]['parentId'] = $customid;
            $custProfArr[$cstmCnt]['profile'] = $tileName;
            $custProfArr[$cstmCnt]['dart'] = 286;
            $custProfArr[$cstmCnt]['variable'] = 'S00286SeqRunNow';
            $custProfArr[$cstmCnt]['varValue'] = $tileName;
            $custProfArr[$cstmCnt]['shortDesc'] = $tileDesc;
            $custProfArr[$cstmCnt]['description'] = $tileDesc;
            $custProfArr[$cstmCnt]['tileDesc'] = $tileDesc;
            $custProfArr[$cstmCnt]['OS'] = 'common';
            $custProfArr[$cstmCnt]['page'] = $customid;
            $custProfArr[$cstmCnt]['status'] = 'enable';
            $custProfArr[$cstmCnt]['authFalg'] = 'F';
            $custProfArr[$cstmCnt]['usageType'] = 1;
            $custProfArr[$cstmCnt]['dynamic'] = 0;
        }


        if (in_array(1, $customShowTypeArr)) {
            $custProfArr[0]['Enable/Disable'] = 1;
            $custProfArr[1]['Enable/Disable'] = 1;
        } else {
            $custProfArr[0]['Enable/Disable'] = 3;
            $custProfArr[1]['Enable/Disable'] = 3;
        }
    }
    $newProfileData = array_merge($nProData, $custProfArr);
    $_SESSION['pwconfprofdata'] = $custProfArr;
    $_SESSION['pwnewdata'] = $newProfileData;


    $level_2 = [];
    foreach ($newProfileData as $value) {
        if ($value['type'] == 'L2') {
            $level_2[] = $value;
        }
    }
    $levelOneStr = '';
    foreach ($level_2 as $val_2) {
        if ($val_2['Enable/Disable'] == 1 || $val_2['Enable/Disable'] == 3) {
            $pmidArr[] = $val_2['parentId'];
            $levelOneStr .= '<li class="tileactive">
                                <input type="hidden" value="" class="hidden_mid midselected">
                                <a href="javascript:void(0);" onclick="renderLevelTwoTiles(\'' . $val_2['parentId'] . '\')" title="' . $val_2['profile'] . '">' . $val_2['profile'] . '</a>
                            </li>';
        }
    }
    $outputArr['startmid'] = $pmidArr[0];
    $outputArr['datalist'] = $levelOneStr;

    echo json_encode($outputArr);
}

function render_ClientProfileDetails()
{
    $profileJSONData = $_SESSION['pwnewdata'];

    $level_2 = [];
    if(!empty($profileJSONData) && is_array($profileJSONData)){
        foreach ($profileJSONData as $value) {
            if ($value['type'] == 'L2') {
                $level_2[] = $value;
            }
        }
    }

    $levelOneStr = '';
    foreach ($level_2 as $val_2) {
        if ($val_2['Enable/Disable'] == 1) {
            $pmidArr[] = $val_2['parentId'];
            $levelOneStr .= '<li class="tileactive">
                                <input type="hidden" value="" class="hidden_mid midselected">
                                <a href="javascript:void(0);" onclick="renderLevelTwoTiles(\'' . $val_2['parentId'] . '\', \'cli\')" title="' . $val_2['profile'] . '">' . $val_2['profile'] . '</a>
                            </li>';
        }
    }

    $outputArr['startmid'] = $pmidArr[0];
    $outputArr['datalist'] = $levelOneStr;

    echo json_encode($outputArr);
}

function render_LevelTwoProfile()
{
    $profmid = url::postToText('mid');
    $showtype = url::postToText('showtype');
    $childLvlStr = '';
    $outputArr = [];

    $newProfileData = $_SESSION['pwnewdata'];

    $level_3 = [];
    foreach ($newProfileData as $value) {
        if ($value['type'] == 'L2') {
            if ($profmid == $value['parentId']) {
                $tileHead = $value['profile'];
                $tileDesc = $value['tileDesc'];
            }
        } else if ($value['type'] == 'L3') {
            if ($profmid == $value['page']) {
                $level_3[] = $value;
            }
        }
    }

    foreach ($level_3 as $val_3) {
        if (true) {
            // if ($showtype == 'cli') {
            if ($val_3['Enable/Disable'] == 1 || $val_3['Enable/Disable'] == 3) {
                $childLvlStr .= '<div class="card">
                                    <div class="card-header resetTroub" id="heading-1-2">
                                        <a class="collapsed" role="button" data-bs-toggle="collapse" href="#collapse' . $val_3['mid'] . '" aria-expanded="false" aria-controls="collapse-1-2">
                                            <i class="tim-icons icon-link-72 green"></i>
                                            <h5>' . $val_3['profile'] . '</h5>
                                            <p class="txt" data-qa="l-profilewiz1_val_3">' . $val_3['tileDesc'] . '</p>
                                            <p class="rightBtn">
                                                <button type="button" class="swal2-confirm btn btn-success btn-sm rightBtn" aria-label="">Run the Troubleshooter</button>
                                            </p>
                                        </a>
                                    </div>
                                </div>';
            }
        } else {
            $childLvlStr .= '<div class="card">
                                    <div class="card-header resetTroub" id="heading-1-2">
                                        <a class="collapsed" role="button" data-bs-toggle="collapse" href="#collapse' . $val_3['mid'] . '" aria-expanded="false" aria-controls="collapse-1-2">
                                            <i class="tim-icons icon-link-72 green"></i>
                                            <h5>' . $val_3['profile'] . '</h5>
                                            <p class="txt" data-qa="l-profilewiz2_val_3">' . $val_3['profile'] . '</p>
                                            <p class="rightBtn">
                                                <button type="button" class="swal2-confirm btn btn-success btn-sm rightBtn" aria-label="">Run the Troubleshooter</button>
                                            </p>
                                        </a>
                                    </div>
                                </div>';
        }
    }

    $outputArr['heading'] = $tileHead;
    $outputArr['description'] = $tileDesc;
    $outputArr['datalist'] = $childLvlStr;

    echo json_encode($outputArr);
}

function filterRunNow($key){
    return strpos($key, 'RunNow') !== false;
}

function save_ProfileDetails()
{
    $dartTileToken = url::requestToText('dartTileToken');

    $pdo = pdo_connect();
    $stmt = $pdo->prepare("select variabledata from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = 'Default' limit 1");
    $stmt->execute();
    $res = $stmt->fetch();
    $defvardata = $res['variabledata'];

    // $checkvdata = end(explode('#NXTVAR#', $defvardata));
    // $lastvarid = explode(':', explode(PHP_EOL, $checkvdata)[1])[1];

    $pwvardata = NanoDB::find_many("select * from " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence where seq_token = ? order by seq_varid", null, [$dartTileToken]);

    $addvdata = '';
    foreach ($pwvardata as $dart) {
        $value = $dart['seq_value'];
        $key = $dart['seq_name'];
        $key = $dart['seq_name'];
        $varid = $dart['seq_varid'];
        $value = preg_replace('/\\\/', '\\\\\\', $value);
        $addvdata .= PHP_EOL . '#NXTVAR#' . PHP_EOL . 'VID:' . $varid . PHP_EOL
            . 'VN:' . $key . PHP_EOL . 'VALUE:' . $value;
    }
    $finalVarData = $defvardata . $addvdata;

    $custProfArr = [];

    $mpriv = checkModulePrivilege('addprofile', 2);
    if (!$mpriv) {
        exit(json_encode(['status' => 'error', 'msg' => 'Access Denied!']));
    }

    $profTileData = $_POST;

    $dartTileToken = strip_tags($profTileData['dartTileToken']);
    $profileName = strip_tags($profTileData['profile-name']);

    foreach ($profTileData['tile-name'] as $key => $value) {
        $profTileData['tile-name'][$key] = strip_tags($value);
    }

    $totalCount = safe_count($profTileData['tile-name']);


    $cpsql = "select count(pid) as prof from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = ?";
    $cpstmt = $pdo->prepare($cpsql);
    $cpstmt->execute([$profileName]);
    $cpres = $cpstmt->fetch();

    if ($cpres['prof'] > 0) {
        $response = array('status' => 'error', 'msg' => 'Profile name already exists!');
    } else {
        $stm1 = $pdo->prepare("select darttiledata, profilesequence from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = 'Default' limit 1");
        $stm1->execute();
        $res1 = $stm1->fetch();

        $loggedUserName = $_SESSION['user']['username'];
        $createdTime = time();

        $profileData = json_encode($_SESSION['pwnewdata'], JSON_PRETTY_PRINT);
        //\\\
        $variabledata = $finalVarData;

        $dartTileData = $res1['darttiledata'];
        $profileSequence = $res1['profilesequence'];

        $psql = "insert into " . $GLOBALS['PREFIX'] . "profile.profileJson (profilename, createdby, createdtime, "
            . "profilevaluejson, variabledata, darttiledata, profilesequence, pwseqtoken) values (?, ?, ?, ?, ?, ?, ?, ?)";
        $pstmt = $pdo->prepare($psql);
        $pstmt->execute([$profileName, $loggedUserName, $createdTime, $profileData, $variabledata, $dartTileData, $profileSequence, $dartTileToken]);
        $profileid = $pdo->lastInsertId();

        $newConfProfData = $_SESSION['pwconfprofdata'];

        for ($i = 0; $i < $totalCount; $i++) {
            $oslist = [];
            $tileName = strip_tags($profTileData['tile-name'][$i]);
            if ($tileName != '') {
                $tileDesc = strip_tags($profTileData['tile-description'][$i]);
                $visiblty = isset($profTileData['visibility'][$i]) ? strip_tags($profTileData['visibility'][$i]) : '';

                isset($profTileData['os-win'][$i]) ? $oslist[] = strip_tags($profTileData['os-win'][$i]) : '';
                isset($profTileData['os-android'][$i]) ? $oslist[] = strip_tags($profTileData['os-android'][$i]) : '';
                isset($profTileData['os-mac'][$i]) ? $oslist[] = strip_tags($profTileData['os-mac'][$i]) : '';
                isset($profTileData['os-ios'][$i]) ? $oslist[] = strip_tags($profTileData['os-ios'][$i]) : '';
                isset($profTileData['os-linux'][$i]) ? $oslist[] = strip_tags($profTileData['os-linux'][$i]) : '';
                isset($profTileData['os-ubuntu'][$i]) ? $oslist[] = strip_tags($profTileData['os-ubuntu'][$i]) : '';
                $oslist_data = implode(',', $oslist);

                $tileDarts = $profTileData['tile-darts'][$i];

                $tileDartList = implode(',', $tileDarts);
                $tileDartName = $profTileData['tile-dart-name'][$i];
                $tileDSeqn = $profTileData['dart-sequence'][$i];
                $tileCount = safe_count($tileDarts);
                $tileArray = [];

                setDartSequenceDetails($tileDarts, $tileDartName, $profileid, $dartTileToken, $i, $tileName);

                for ($t = 0; $t < $tileCount; $t++) {
                    $tileArray[$tileDSeqn[$t]]['dartid'] = $tileDarts[$t];
                    $tileArray[$tileDSeqn[$t]]['dartname'] = $tileDartName[$t];
                }
                $tileDartJson = json_encode($tileArray);

                $tsql = "insert into " . $GLOBALS['PREFIX'] . "profile.customProfileTiles (profid, tilename, "
                    . "tiledescription, visibility, ostypes, dartlist, tiledartseq) "
                    . "values (?, ?, ?, ?, ?, ?, ?)";
                $tstmt = $pdo->prepare($tsql);
                $tstmt->execute([
                    $profileid, $tileName, $tileDesc, $visiblty, $oslist_data,
                    $tileDartList, $tileDartJson
                ]);

                $profiletileid = $pdo->lastInsertId();

                $newConfProfData[$i + 1]['varValue'] = $tileName;
            } else {
                $profiletileid = '0';
            }
        }

        $newProfileData = array_merge($_SESSION['pwprofdefdata'], $newConfProfData);
        $profilevaluejson = json_encode($newProfileData);
        $updstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "profile.profileJson set profilevaluejson = ? where pid = ?");
        $updstmt->execute([$profilevaluejson, $profileid]);

        if ($profileid != '' || $profiletileid != '') {
            $response = array('status' => 'success', 'msg' => 'Profile has been created successfully');
        } else {
            $response = array('status' => 'success', 'msg' => 'Profile creation failed');
        }
    }

    echo json_encode($response);
}


function e_save_ProfileDetails()
{
    $profileid = url::requestToText('profileID');
    $dartTileToken = url::requestToText('dartTileToken');

    $pdo = pdo_connect();
    $res = NanoDB::find_one("select variabledata from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = 'Default' limit 1");
    $defvardata = $res['variabledata'];
    $checkvdata = end(explode('#NXTVAR#', $defvardata));
    $lastvarid = explode(':', explode(PHP_EOL, $checkvdata)[1])[1];
    $newvarid = $lastvarid + 1;

    $pwvardata = NanoDB::find_many("select * from " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence where seq_token = ? order by seq_varid", null, [$dartTileToken]);

    $addvdata = '';
    foreach ($pwvardata as $dart) {
        $value = $dart['seq_value'];
        $key = $dart['seq_name'];
        $key = $dart['seq_name'];
        $varid = $dart['seq_varid'];
        $value = preg_replace('/\\\/', '\\\\\\', $value);
        $addvdata .= PHP_EOL . '#NXTVAR#' . PHP_EOL . 'VID:' . $varid . PHP_EOL
            . 'VN:' . $key . PHP_EOL . 'VALUE:' . $value;
    }

    $finalVarData = $defvardata . $addvdata;
    logs::log('DEBUG: addvdata - ', [ $addvdata ]);
    // upper works


    $custProfArr = [];

    $mpriv = checkModulePrivilege('editprofile', 2);
    if (!$mpriv) {
        exit(json_encode(['status' => 'error', 'msg' => 'Access Denied!']));
    }

    $profTileData = $_POST;

    $dartTileToken = strip_tags($profTileData['dartTileToken']);
    $profileName = strip_tags($profTileData['profile-name']);

    foreach ($profTileData['tile-name'] as $key => $value) {
        $profTileData['tile-name'][$key] = strip_tags($value);
    }

    $totalCount = safe_count($profTileData['tile-name']);

    $stm1 = $pdo->prepare("select darttiledata, profilesequence from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = 'Default' limit 1");
    $stm1->execute();
    $res1 = $stm1->fetch();

    $loggedUserName = $_SESSION['user']['username'];
    $createdTime = time();

    $profileData = json_encode($_SESSION['pwnewdata'], JSON_PRETTY_PRINT);

    $variabledata = $finalVarData;

    $dartTileData = $res1['darttiledata'];
    $profileSequence = $res1['profilesequence'];


    NanoDB::query("update " . $GLOBALS['PREFIX'] . "profile.profileJson set profilename = ?, createdby = ?, createdtime = ?, "
    . "profilevaluejson = ?, variabledata = ?, darttiledata = ?, profilesequence = ?, pwseqtoken = ? where pid = ?", [
        $profileName, $loggedUserName, $createdTime, $profileData, $variabledata, $dartTileData, $profileSequence, $dartTileToken,
        $profileid
    ]);

    NanoDB::query("DELETE FROM " . $GLOBALS['PREFIX'] . "profile.customProfileTiles where profid = ?", [$profileid]);

    $newConfProfData = $_SESSION['pwconfprofdata'];

    for ($i = 0; $i < $totalCount; $i++) {
        $oslist = [];
        $tileName = strip_tags($profTileData['tile-name'][$i]);
        if ($tileName != '') {
            $tileDesc = strip_tags($profTileData['tile-description'][$i]);
            $visiblty = isset($profTileData['visibility'][$i]) ? strip_tags($profTileData['visibility'][$i]) : '';

            isset($profTileData['os-win'][$i]) ? $oslist[] = strip_tags($profTileData['os-win'][$i]) : '';
            isset($profTileData['os-android'][$i]) ? $oslist[] = strip_tags($profTileData['os-android'][$i]) : '';
            isset($profTileData['os-mac'][$i]) ? $oslist[] = strip_tags($profTileData['os-mac'][$i]) : '';
            isset($profTileData['os-ios'][$i]) ? $oslist[] = strip_tags($profTileData['os-ios'][$i]) : '';
            isset($profTileData['os-linux'][$i]) ? $oslist[] = strip_tags($profTileData['os-linux'][$i]) : '';
            isset($profTileData['os-ubuntu'][$i]) ? $oslist[] = strip_tags($profTileData['os-ubuntu'][$i]) : '';
            $oslist_data = implode(',', $oslist);

            $tileDarts = $profTileData['tile-darts'][$i];

            $tileDartList = implode(',', $tileDarts);
            $tileDartName = $profTileData['tile-dart-name'][$i];
            $tileDSeqn = $profTileData['dart-sequence'][$i];
            $tileCount = safe_count($tileDarts);
            $tileArray = [];

            setDartSequenceDetails($tileDarts, $tileDartName, $profileid, $dartTileToken, $i, $tileName);

            for ($t = 0; $t < $tileCount; $t++) {
                $tileArray[$tileDSeqn[$t]]['dartid'] = $tileDarts[$t];
                $tileArray[$tileDSeqn[$t]]['dartname'] = $tileDartName[$t];
            }
            $tileDartJson = json_encode($tileArray);

            $tsql = "INSERT INTO " . $GLOBALS['PREFIX'] . "profile.customProfileTiles (profid, tilename, "
                . "tiledescription, visibility, ostypes, dartlist, tiledartseq) "
                . "values (?, ?, ?, ?, ?, ?, ?)";
            $tstmt = $pdo->prepare($tsql);
            $tstmt->execute([
                $profileid, $tileName, $tileDesc, $visiblty, $oslist_data,
                $tileDartList, $tileDartJson
            ]);

            $profiletileid = $pdo->lastInsertId();

            $newConfProfData[$i + 1]['varValue'] = $tileName;
        } else {
            $profiletileid = '0';
        }
    }

    $newProfileData = array_merge($_SESSION['pwprofdefdata'], $newConfProfData);
    $profilevaluejson = json_encode($newProfileData);
    $updstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "profile.profileJson set profilevaluejson = ? where pid = ?");
    $updstmt->execute([$profilevaluejson, $profileid]);

    if ($profileid != '' || $profiletileid != '') {
        $response = array('status' => 'success', 'msg' => 'Profile has been created successfully');
    } else {
        $response = array('status' => 'success', 'msg' => 'Profile creation failed');
    }

    echo json_encode($response);
}


function setDartSequenceDetails($tileDarts, $tileDartName, $profileid, $dartTileToken, $seq_indx, $tileName)
{
    $pdo = pdo_connect();
    $tileDartStr = '';
    $seqDataArr = [];
    $profSeqVal = [];

    $res1 = NanoDB::find_one("SELECT darttiledata, profilesequence from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?", null, [ $profileid ]);

    $dartTileSeqData = $res1['darttiledata'];
    $profileSequence = $res1['profilesequence'];
    $seqData = explode(PHP_EOL, $dartTileSeqData);
    $seqLastId = explode(',', end($seqData))[0];

    $stm2 = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.profwiz_sequence where seq_token = ? and seq_index = ?");

    $stm2->execute([$dartTileToken, $seq_indx]);
    $res2 = $stm2->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($res2) > 0) {
        foreach ($res2 as $value) {
            $seqDataArr[$value['seq_order']][] = $value['seq_varid'];
        }

        $totDartCnt = safe_count($tileDarts);
        $seqIncVal = $seqLastId + 1;
        for ($i = 0; $i < $totDartCnt; $i++) {
            $vidseq = implode(':', $seqDataArr[$i]);
            $tileDartStr .= PHP_EOL . $seqIncVal . ',' . $tileDarts[$i] . ',' . $tileDartName[$i] . ',' . $vidseq;
            $profSeqVal[] = $seqIncVal;
            $seqIncVal++;
        }
        $dartTileSeqData .= $tileDartStr;

        $profSeqData = PHP_EOL . $tileName . ':' . implode(',', $profSeqVal);

        $profileSequence .= $profSeqData;

        NanoDB::query("UPDATE " . $GLOBALS['PREFIX'] . "profile.profileJson set darttiledata = ?, profilesequence = ? where pid = ?", [ $dartTileSeqData, $profileSequence, (int)$profileid ]);
    }
}

function PRWZ_getGroupDetails()
{
    $pdo = pdo_connect();
    $username = $_SESSION['user']['username'];

    $sql = $pdo->prepare("select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category = 'Wiz_SCOP_MC'");
    $sql->execute();
    $categoryres = $sql->fetch();

    $mcatid = $categoryres['mcatid'];

    $sql = "select mg.mgroupid,mg.mgroupuniq,mg.username,mg.name,mc.mcatid,created,mg.boolstring,mg.style,mg.global "
        . "from " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg join " . $GLOBALS['PREFIX'] . "core.MachineCategories as mc on mg.mcatuniq = mc.mcatuniq join "
        . $GLOBALS['PREFIX'] . "core.GroupMappings gm on mg.mgroupid = gm.groupid where mc.mcatid = ? and gm.username = ? group by mg.name;";
    $stm = $pdo->prepare($sql);
    $stm->execute([$mcatid, $username]);
    $groupres = $stm->fetchAll();

    return $groupres;
}

function check_ProfileAttachStatus()
{
    $prof_id = url::postToText('profid');
    $scop = url::postToText('attchType');
    $scopdata = url::postToArrayWithException('attchVal');

    $pstmtdata = NanoDB::find_many(
        "select pid, scoplevel, scopvalue from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid != ? and profilename != ?",
        null,
        [
            $prof_id,
            'Default'
        ]
    );

    $isExist = false;
    foreach ($pstmtdata as $key => $value) {
        $pscopdata = safe_json_decode($value['scopvalue'], TRUE);

        foreach ($scopdata as $scopval) {
            if ($scop == 'sites') {
                if (!is_null($pscopdata['sites']) && in_array($scopval, $pscopdata['sites'])) {
                    $isExist = true;
                }
            } else if ($scop == 'groups') {
                if (!is_null($pscopdata['groups']) && in_array($scopval, $pscopdata['groups'])) {
                    $isExist = true;
                }
            }
        }
    }

    if ($isExist) {
        $msg = 'notify';
    } else {
        $msg = 'notdone';
    }

    // ob_clean();
    echo $msg;
}

function attach_ProfileData()
{
    $atchType = url::postToStringAz09('type');
    // $grpatchValu = url::postToAny('sitetypeval');
    // $siteatchValu = url::postToAny('grptypeval');
    $atchValu = url::postToArrayWithException('typeval'); //array_merge($grpatchValu,$siteatchValu);
    $profValu = url::postToInt('profval');
    $username = $_SESSION['user']['username'];
    $mpriv = checkModulePrivilege('attachprofile', 2);
    if (!$mpriv) {
        exit(json_encode(['status' => 'failed', 'msg' => 'Access Denied!']));
    }

    $profileArr = [1 => 'S00304_VariableIdConfig', 2 => 'S00304_SequenceDetails', 3 => 'S00304_ProfileSequence', 4 => 'S00304_BaseProfiles'];
    $parseProfArr = [];
    $pdo = pdo_connect();

    $in = str_repeat('?,', safe_count($atchValu) - 1) . '?';
    $sql = $pdo->prepare("select mgroupid, mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name in ($in)");
    $sql->execute($atchValu);
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    $mGroupRes = $res;

    $stmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $stmt->execute([$profValu]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $varIdConfig = $result['variabledata'];
    $seqnceDetls = $result['darttiledata'];
    $profileSeqn = $result['profilesequence'];
    $extjsondata = safe_json_decode($result['scopvalue'], true);
    $baseProfile = parseBaseProfile($result['profilevaluejson']);

    foreach ($mGroupRes as $key => $value) {
        $parseProfMastArr = [];

        foreach ($profileArr as $valuprof) {
            $parseProfArr['name'] = (string) $valuprof;
            $parseProfArr['dart'] = 304;
            $parseProfArr['group'] = (string) $value['mgroupuniq'];
            $parseProfArr['loggeduser'] = $username;

            switch ($valuprof) {
                case 'S00304_VariableIdConfig':
                    $dartProfValu = $varIdConfig;
                    break;
                case 'S00304_SequenceDetails':
                    $dartProfValu = $seqnceDetls;
                    break;
                case 'S00304_ProfileSequence':
                    $dartProfValu = $profileSeqn;
                    break;
                case 'S00304_BaseProfiles':
                    $dartProfValu = $baseProfile;
                    break;
            }
            $parseProfArr['value'] = $dartProfValu;

            $parseProfMastArr[] = $parseProfArr;
        }

        $result = MAKE_CURL_CALL($parseProfMastArr, 'POST');
    }

    if ($atchType == 'sites') {
        $atchTypeData = 'site(s)';
    } else {
        $atchTypeData = 'Group(s)';
    }

    if ($result) {

        $sample_data = json_decode('{"S00304_BaseProfiles":"0","S00304_AddoOnProfiles":"0","S00304_ProfileSequence":"0","S00304_SequenceDetails":"0","S00304_VariableIdConfig":"0","S00304_ProfileSched":"0","S00304DynamicProfileConf":"0"}');
        $sample_data->S00304_BaseProfiles = $baseProfile;
        $sample_data->S00304_VariableIdConfig = stripslashes($varIdConfig);
        $sample_data->S00304_SequenceDetails = $seqnceDetls;
        $sample_data->S00304_ProfileSequence = $profileSeqn;
        $sample_data = json_encode($sample_data);


        if ($atchType == 'sites') {
            $jsondata['sites'] = $atchValu;
            $jsondata['groups'] = $extjsondata['groups'];

            foreach ($atchValu as $site) {
                submit_JsonSchema($sample_data, 304, 'Sites', $site);
            }
        } else if ($atchType == 'groups') {
            $jsondata['groups'] = $atchValu;
            $jsondata['sites'] = $extjsondata['sites'];

            foreach ($atchValu as $group) {
                submit_JsonSchema($sample_data, 304, 'Groups', $group);
            }
        }


        // if (is_array($extjsondata)) {
        //     $finaljsondata = array_merge($extjsondata, $jsondata);
        // } else {
        $finaljsondata = $jsondata;
        // }
        // return;
        $ustmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "profile.profileJson set scoplevel = ?, scopvalue = ? where pid = ?");
        $ustmt->execute([$atchType, json_encode($finaljsondata), $profValu]);

        detachProfileScop($pdo, $atchType, $atchValu, $profValu);

        $response = array('status' => 'success', 'msg' => 'Profile has been attached with the ' . $atchTypeData . ' successfully.');
    } else {
        $response = array('status' => 'failed', 'msg' => 'Failed to attach profile with the ' . $atchTypeData . '!<br/>Please try again.');
    }
    echo json_encode($response);
}

function detachProfileScop($pdo, $scop, $scopdata, $cprofid)
{
    $pstmt = $pdo->prepare("select pid, scopvalue from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid != ? and profilename != ?");
    $pstmt->execute([$cprofid, 'Default']);
    $pstmtdata = $pstmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pstmtdata as $key => $value) {
        $profid = $value['pid'];
        $pscopdata = safe_json_decode($value['scopvalue'], TRUE);

        foreach ($scopdata as $scopval) {

            if ($scop == 'sites') {
                if (!is_null($pscopdata['sites']) && in_array($scopval, $pscopdata['sites'])) {
                    $pscopdata['sites'] = array_diff($pscopdata['sites'], [$scopval]);
                }
            } else if ($scop == 'groups') {
                if (!is_null($pscopdata['groups']) && in_array($scopval, $pscopdata['groups'])) {
                    $pscopdata['groups'] = array_diff($pscopdata['groups'], [$scopval]);
                }
            }
        }

        $updatedjsondata = json_encode($pscopdata);
        if ($updatedjsondata == 'null') {
            $updatedjsondata = null;
        }
        $updtstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "profile.profileJson set scopvalue = ? where pid = ?");
        $updtstmt->execute([$updatedjsondata, $profid]);
    }
}

function parseBaseProfile($baseProfile)
{

    $baseProfStr = '';
    $baseProfileData = safe_json_decode($baseProfile, TRUE);

    foreach ($baseProfileData as $key => $value) {
        if ($value['Enable/Disable'] == 3) {
            $value['Enable/Disable'] = 1;
        }
        $baseProfHStr = '';
        foreach ($value as $key1 => $val) {
            if ($key == 0) {
                $baseProfHStr .= $key1 . '#NXT#';
            }
            if ($val == '' && $val != 0) {
                $val = 'null';
            }
            $baseProfStr .= $val . '#NXT#';
        }
        $baseProfStr = rtrim($baseProfStr, '#NXT#');
        $baseProfStr .= PHP_EOL;
    }
    $baseProfHStr = rtrim($baseProfHStr, '#NXT#') . PHP_EOL;

    return $baseProfHStr . $baseProfStr;
}




function e_update_ProfileData()
{
    $nProData = [];
    $lev_2_pid = [];
    $profData = url::postToAny('pdata');
    $dProData = $_SESSION['pwdefdata'];

    foreach ($dProData as $dkey => $value) {
        if (in_array($value['mid'], $profData)) {
            $value['Enable/Disable'] = 3;
            $nProData[$dkey] = $value;
            $lev_2_pid[$dkey] = $value['parentId'];
        } else {
            if (($value['type'] == "L0" && $value['parentId'] == 0) || ($value['menuItem'] == 'Troubleshooters')) {
                $value['Enable/Disable'] = 1;
            } else {
                $value['Enable/Disable'] = 0;
            }
            $nProData[$dkey] = $value;
        }
    }

    foreach ($nProData as $nkey => $nproval) {
        if ($nproval['type'] == 'L1') {
            if (in_array($nproval['page'], $lev_2_pid)) {
                $nproval['Enable/Disable'] = 3;
                $nProData[$nkey] = $nproval;
            }
        }
    }
    $_SESSION['pwnewdata'] = $nProData;
    $_SESSION['pwprofdefdata'] = $nProData;
    $_SESSION['ProfileStateOne'] = $nProData;

    echo json_encode($nProData);
}

function e_update_ClientProfileData()
{
    $cliProfData = url::postToArrayWithException('cliprofdata');
    foreach ($_SESSION['ProfileStateOne'] as $key => $value) {
        if (!is_null($cliProfData) && in_array($value['mid'], $cliProfData)) {
            $value['Enable/Disable'] = 1;
            $cliProfDetails[$key] = $value;
        } else {
            $cliProfDetails[$key] = $value;
        }
    }
    $_SESSION['pwnewdata'] = $cliProfDetails;
    $_SESSION['pwprofdefdata'] = $cliProfDetails;
    echo 'Success';
}

function e_render_ProfileDetails()
{
    $custProfArr = [];
    $pmidArr = [];
    $outputArr = [];
    $profTileData = $_GET;

    $nProData = $_SESSION['pwprofdefdata'];
    $customTileData = PRWZ_getCustomTileDetails(url::getToText('profileID'));
    $customTileCnt = safe_count($customTileData);

    // @warn strange code safe_count expected array but string given.
    // $totalCount = safe_count(strip_tags($profTileData['tile-name']));
    $totalCount = safe_count($profTileData['tile-name']);
    if ($totalCount > $customTileCnt && strip_tags($profTileData['tile-name'][0]) != '') {
        $cstmCnt = 1;
        if (url::getToText('global') < 2000) {
            $customid = $custTileMid = 2000;
            $customTileName = 'Custom Tiles';
            if ($customTileCnt == 0) {
                $custProfArr[0]['Enable/Disable'] = 1;
                $custProfArr[0]['mid'] = ($customid - 1);
                $custProfArr[0]['menuItem'] = $customTileName;
                $custProfArr[0]['type'] = 'L1';
                $custProfArr[0]['parentId'] = 1;
                $custProfArr[0]['profile'] = $customTileName;
                $custProfArr[0]['dart'] = 'NA';
                $custProfArr[0]['variable'] = '';
                $custProfArr[0]['varValue'] = '';
                $custProfArr[0]['shortDesc'] = $customTileName;
                $custProfArr[0]['description'] = $customTileName;
                $custProfArr[0]['tileDesc'] = $customTileName;
                $custProfArr[0]['OS'] = 'common';
                $custProfArr[0]['page'] = $customid;
                $custProfArr[0]['status'] = 'enable';
                $custProfArr[0]['authFalg'] = 'F';
                $custProfArr[0]['usageType'] = 1;
                $custProfArr[0]['dynamic'] = 0;
                $custProfArr[1]['Enable/Disable'] = 1;
                $custProfArr[1]['mid'] = $customid;
                $custProfArr[1]['menuItem'] = $customTileName;
                $custProfArr[1]['type'] = 'L2';
                $custProfArr[1]['parentId'] = $customid;
                $custProfArr[1]['profile'] = $customTileName;
                $custProfArr[1]['dart'] = 'NA';
                $custProfArr[1]['variable'] = '';
                $custProfArr[1]['varValue'] = '';
                $custProfArr[1]['shortDesc'] = $customTileName;
                $custProfArr[1]['description'] = $customTileName;
                $custProfArr[1]['tileDesc'] = $customTileName;
                $custProfArr[1]['OS'] = 'common';
                $custProfArr[1]['page'] = 2;
                $custProfArr[1]['status'] = 'enable';
                $custProfArr[1]['authFalg'] = 'F';
                $custProfArr[1]['usageType'] = 1;
                $custProfArr[1]['dynamic'] = 0;
            }
        } else {
            $customid = 2000;
            $custTileMid = url::getToText('global');
        }

        for ($i = $customTileCnt; $i < $totalCount; $i++) {
            $oslist = [];
            $tileName = strip_tags($profTileData['tile-name'][$i]);
            $tileDesc = strip_tags($profTileData['tile-description'][$i]);
            $isClientTile = strip_tags($profTileData['visibility'][$i]);
            $customShowTypeArr[] = strip_tags($profTileData['visibility'][$i]);

            isset($profTileData['os-win'][$i]) ? $oslist[] = strip_tags($profTileData['os-win'][$i]) : '';
            isset($profTileData['os-android'][$i]) ? $oslist[] = strip_tags($profTileData['os-android'][$i]) : '';
            isset($profTileData['os-mac'][$i]) ? $oslist[] = strip_tags($profTileData['os-mac'][$i]) : '';
            isset($profTileData['os-ios'][$i]) ? $oslist[] = strip_tags($profTileData['os-ios'][$i]) : '';
            isset($profTileData['os-linux'][$i]) ? $oslist[] = strip_tags($profTileData['os-linux'][$i]) : '';
            isset($profTileData['os-ubuntu'][$i]) ? $oslist[] = strip_tags($profTileData['os-ubuntu'][$i]) : '';
            // $oslist_data = implode(',', $oslist);

            $cstmCnt++;
            $custTileMid = $custTileMid + 1;
            $custProfArr[$cstmCnt]['Enable/Disable'] = $isClientTile;
            $custProfArr[$cstmCnt]['mid'] = $custTileMid;
            $custProfArr[$cstmCnt]['menuItem'] = $tileName;
            $custProfArr[$cstmCnt]['type'] = 'L3';
            $custProfArr[$cstmCnt]['parentId'] = $customid;
            $custProfArr[$cstmCnt]['profile'] = $tileName;
            $custProfArr[$cstmCnt]['dart'] = 286;
            $custProfArr[$cstmCnt]['variable'] = 'S00286SeqRunNow';
            $custProfArr[$cstmCnt]['varValue'] = $tileName;
            $custProfArr[$cstmCnt]['shortDesc'] = $tileDesc;
            $custProfArr[$cstmCnt]['description'] = $tileDesc;
            $custProfArr[$cstmCnt]['tileDesc'] = $tileDesc;
            $custProfArr[$cstmCnt]['OS'] = 'common';
            $custProfArr[$cstmCnt]['page'] = $customid;
            $custProfArr[$cstmCnt]['status'] = 'enable';
            $custProfArr[$cstmCnt]['authFalg'] = 'F';
            $custProfArr[$cstmCnt]['usageType'] = 1;
            $custProfArr[$cstmCnt]['dynamic'] = 0;
        }

        if (url::getToText('global') < 2000) {
            if (in_array(1, $customShowTypeArr)) {
                $custProfArr[0]['Enable/Disable'] = 1;
                $custProfArr[1]['Enable/Disable'] = 1;
            } else {
                $custProfArr[0]['Enable/Disable'] = 3;
                $custProfArr[1]['Enable/Disable'] = 3;
            }
        }
    }

    $newProfileData = array_merge($nProData, $custProfArr);
    $_SESSION['pwconfprofdata'] = $custProfArr;
    $_SESSION['pwnewdata'] = $newProfileData;

    $level_2 = [];
    foreach ($newProfileData as $value) {
        if ($value['type'] == 'L2') {
            $level_2[] = $value;
        }
    }

    $levelOneStr = '';
    foreach ($level_2 as $val_2) {
        if ($val_2['Enable/Disable'] == 1 || $val_2['Enable/Disable'] == 3) {
            $pmidArr[] = $val_2['parentId'];
            $levelOneStr .= '<li class="tileactive">
                                <input type="hidden" value="" class="hidden_mid midselected">
                                <a href="javascript:void(0);" onclick="renderLevelTwoTiles(\'' . $val_2['parentId'] . '\')" title="' . $val_2['profile'] . '">' . $val_2['profile'] . '</a>
                            </li>';
        }
    }

    $outputArr['startmid'] = $pmidArr[0];
    $outputArr['datalist'] = $levelOneStr;

    echo json_encode($outputArr);
}

function e_render_LevelTwoProfile()
{
    $profmid = url::postToText('mid');
    // $showtype = url::postToText('showtype');
    $childLvlStr = '';
    $outputArr = [];

    $newProfileData = $_SESSION['pwnewdata'];
    $level_3 = [];
    foreach ($newProfileData as $value) {
        if ($value['type'] == 'L2') {
            if ($profmid == $value['parentId']) {
                $tileHead = $value['profile'];
                $tileDesc = $value['tileDesc'];
            }
        } else if ($value['type'] == 'L3') {
            if ($profmid == $value['page']) {
                $level_3[] = $value;
            }
        }
    }

    foreach ($level_3 as $val_3) {

        if (true) {
            // if ($showtype == 'cli') {
            if ($val_3['Enable/Disable'] == 1 || $val_3['Enable/Disable'] == 3) {
                $childLvlStr .= '<div class="card">
                                    <div class="card-header resetTroub" id="heading-1-2">
                                        <a class="collapsed" role="button" data-bs-toggle="collapse" href="#collapse' . $val_3['mid'] . '" aria-expanded="false" aria-controls="collapse-1-2">
                                            <i class="tim-icons icon-link-72 green"></i>
                                            <h5>' . $val_3['profile'] . '</h5>
                                            <p class="txt" data-qa="l-profilewiz3_val_3">' . $val_3['tileDesc'] . '</p>
                                            <p class="rightBtn">
                                                <button type="button" class="swal2-confirm btn btn-success btn-sm rightBtn" aria-label="">Run the Troubleshooter</button>
                                            </p>
                                        </a>
                                    </div>
                                </div>';
            }
        } else {
            $childLvlStr .= '<div class="card">
                                    <div class="card-header resetTroub" id="heading-1-2">
                                        <a class="collapsed" role="button" data-bs-toggle="collapse" href="#collapse' . $val_3['mid'] . '" aria-expanded="false" aria-controls="collapse-1-2">
                                            <i class="tim-icons icon-link-72 green"></i>
                                            <h5>' . $val_3['profile'] . '</h5>
                                            <p class="txt" data-qa="l-profilewiz4_val_3">' . $val_3['tileDesc'] . '</p>
                                            <p class="rightBtn">
                                                <button type="button" class="swal2-confirm btn btn-success btn-sm rightBtn" aria-label="">Run the Troubleshooter</button>
                                            </p>
                                        </a>
                                    </div>
                                </div>';
        }
    }

    $outputArr['heading'] = $tileHead;
    $outputArr['description'] = $tileDesc;
    $outputArr['datalist'] = $childLvlStr;

    echo json_encode($outputArr);
}

function update_ProfileDetails()
{
    $pdo = pdo_connect();

    $timenow = time();
    $profTileData = $_POST;

    $mpriv = checkModulePrivilege('editprofile', 2);
    if (!$mpriv) {
        exit(json_encode(['status' => 'error', 'msg' => 'Access Denied!']));
    }

    $error = FALSE;
    $dartTileToken = strip_tags($profTileData['dartTileToken']);
    $profileName = strip_tags($profTileData['profile-name']);
    $totalCount = safe_count($profTileData['tile-name']);
    $profileid = strip_tags($profTileData['profileID']);

    $cpstmt = $pdo->prepare("select profilename, count(pid) as prof from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $cpstmt->execute([$profileid]);
    $cpres = $cpstmt->fetch();

    if ($profileName != $cpres['profilename']) {
        $cpnstmt = $pdo->prepare("select profilename, count(pid) as prof from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = ?");
        $cpnstmt->execute([$profileName]);
        $cpnres = $cpnstmt->fetch();
        if ($cpnres['prof'] > 0) {
            $error = TRUE;
            $response = array('status' => 'error', 'msg' => 'Profile name already exists!');
        }
    }
    if (!$error) {
        $profileData = json_encode($_SESSION['pwnewdata'], JSON_PRETTY_PRINT);
        $variabledata = $_SESSION['pwvardata'];

        $psql = "update " . $GLOBALS['PREFIX'] . "profile.profileJson set profilename = ?, profilevaluejson = ?, variabledata = ?, modifiedtime = ? where pid = ?";
        $pstmt = $pdo->prepare($psql);
        $pstmt->execute([$profileName, $profileData, $variabledata, $timenow, $profileid]);

        $newConfProfData = $_SESSION['pwconfprofdata'];

        for ($i = 0; $i < $totalCount; $i++) {
            $oslist = [];
            $customTileId = strip_tags($profTileData['custom-tile-id'][$i]);
            $tileName = strip_tags($profTileData['tile-name'][$i]);
            if ($tileName != '') {
                $tileDesc = strip_tags($profTileData['tile-description'][$i]);
                $visiblty = isset($profTileData['visibility'][$i]) ? strip_tags($profTileData['visibility'][$i]) : '';

                isset($profTileData['os-win'][$i]) ? $oslist[] = strip_tags($profTileData['os-win'][$i]) : '';
                isset($profTileData['os-android'][$i]) ? $oslist[] = strip_tags($profTileData['os-android'][$i]) : '';
                isset($profTileData['os-mac'][$i]) ? $oslist[] = strip_tags($profTileData['os-mac'][$i]) : '';
                isset($profTileData['os-ios'][$i]) ? $oslist[] = strip_tags($profTileData['os-ios'][$i]) : '';
                isset($profTileData['os-linux'][$i]) ? $oslist[] = strip_tags($profTileData['os-linux'][$i]) : '';
                isset($profTileData['os-ubuntu'][$i]) ? $oslist[] = strip_tags($profTileData['os-ubuntu'][$i]) : '';
                $oslist_data = implode(',', $oslist);

                $tileDarts = strip_tags($profTileData['tile-darts'][$i]);
                $tileDartList = implode(',', $tileDarts);
                $tileDartName = strip_tags($profTileData['tile-dart-name'][$i]);

                $tileDSeqn = strip_tags($profTileData['dart-sequence'][$i]);
                $tileCount = safe_count($tileDarts);
                $tileArray = [];

                setDartSequenceDetails($tileDarts, $tileDartName, $profileid, $dartTileToken, $i, $tileName);

                for ($t = 0; $t < $tileCount; $t++) {
                    $tileArray[$tileDSeqn[$t]]['dartid'] = $tileDarts[$t];
                    $tileArray[$tileDSeqn[$t]]['dartname'] = $tileDartName[$t];
                }
                $tileDartJson = json_encode($tileArray);

                if ($customTileId != '') {
                    $tsql = "update " . $GLOBALS['PREFIX'] . "profile.customProfileTiles set tilename = ?, tiledescription = ?, "
                        . "visibility = ?, ostypes = ?, dartlist = ?, tiledartseq = ? where ctid = ? ";
                    $tstmt = $pdo->prepare($tsql);
                    $tstmt->execute([$tileName, $tileDesc, $visiblty, $oslist_data, $tileDartList, $tileDartJson, $customTileId]);
                } else {
                    $tsql = "insert into " . $GLOBALS['PREFIX'] . "profile.customProfileTiles (profid, tilename, tiledescription, visibility, "
                        . "ostypes, dartlist, tiledartseq) values (?, ?, ?, ?, ?, ?, ?)";
                    $tstmt = $pdo->prepare($tsql);
                    $tstmt->execute([$profileid, $tileName, $tileDesc, $visiblty, $oslist_data, $tileDartList, $tileDartJson]);
                }

                $newConfProfData[$i + 1]['varValue'] = $tileName;
            }
        }

        $newProfileData = array_merge($_SESSION['pwprofdefdata'], $newConfProfData);
        $profilevaluejson = json_encode($newProfileData);
        $updstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "profile.profileJson set profilevaluejson = ? where pid = ?");
        $updstmt->execute([$profilevaluejson, $profileid]);

        if ($profileid != '') {
            $response = array('status' => 'success', 'msg' => 'Profile has been updated successfully');
        } else {
            $response = array('status' => 'success', 'msg' => 'Profile creation failed');
        }
    }

    echo json_encode($response);
}

function PRWZ_getCustomTileDetails($pw_id)
{
    if (!$pw_id) {
        return [];
    }
    return NanoDB::find_many("select * from " . $GLOBALS['PREFIX'] . "profile.customProfileTiles where profid = ?", null, [$pw_id]);
}


function v_render_ProfileDetails()
{
    $pdo = pdo_connect();
    $profid = url::postToText('profid');
    $outputArr = [];

    $sql = $pdo->prepare("select pid, profilename, profilevaluejson from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $sql->execute([$profid]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    $profileJSONData = safe_json_decode($res['profilevaluejson'], TRUE);

    $_SESSION['pwnewdata'] = $profileJSONData;

    $level_2 = [];
    if(!empty($profileJSONData) && is_array($profileJSONData)){
        foreach ($profileJSONData as $value) {
            if ($value['type'] == 'L2') {
                $level_2[] = $value;
            }
        }
    }

    $levelOneStr = '';
    foreach ($level_2 as $val_2) {
        if ($val_2['Enable/Disable'] == 1 || $val_2['Enable/Disable'] == 3) {
            $pmidArr[] = $val_2['parentId'];
            $levelOneStr .= '<li class="tileactive">
                                <input type="hidden" value="" class="hidden_mid midselected">
                                <a href="javascript:void(0);" onclick="renderLevelTwoTiles(\'' . $val_2['parentId'] . '\')" title="' . $val_2['profile'] . '">' . $val_2['profile'] . '</a>
                            </li>';
        }
    }

    $outputArr['startmid'] = $pmidArr[0];
    $outputArr['datalist'] = $levelOneStr;

    echo json_encode($outputArr);
}

function v_render_ClientProfileDetails()
{
    $pdo = pdo_connect();
    $profid = url::postToText('profid');
    $outputArr = [];

    $sql = $pdo->prepare("select pid, profilename, profilevaluejson from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $sql->execute([$profid]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    $profileJSONData = safe_json_decode($res['profilevaluejson'], TRUE);

    $level_2 = [];
    if(!empty($profileJSONData) && is_array($profileJSONData)){
        foreach ($profileJSONData as $value) {
            if ($value['type'] == 'L2') {
                $level_2[] = $value;
            }
        }
    }

    $levelOneStr = '';
    foreach ($level_2 as $val_2) {
        if ($val_2['Enable/Disable'] == 1) {
            $pmidArr[] = $val_2['parentId'];
            $levelOneStr .= '<li class="tileactive">
                                <input type="hidden" value="" class="hidden_mid midselected">
                                <a href="javascript:void(0);" onclick="renderLevelTwoTiles(\'' . $val_2['parentId'] . '\', \'cli\')" title="' . $val_2['profile'] . '">' . $val_2['profile'] . '</a>
                            </li>';
        }
    }

    $outputArr['startmid'] = $pmidArr[0];
    $outputArr['datalist'] = $levelOneStr;

    echo json_encode($outputArr);
}

function duplicate_Profile()
{
    $selprofid = url::postToText('selprofid');
    $dupProfName = url::postToText('dprofname');
    $error = FALSE;

    $mpriv = checkModulePrivilege('duplicateprofile', 2);
    if (!$mpriv) {
        exit(json_encode(['status' => 'failed', 'msg' => 'Access Denied!']));
    }

    $pdo = pdo_connect();
    $cpstmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $cpstmt->execute([$selprofid]);
    $cpres = $cpstmt->fetch(PDO::FETCH_ASSOC);

    $cpnstmt = $pdo->prepare("select profilename, count(pid) as prof from " . $GLOBALS['PREFIX'] . "profile.profileJson where profilename = ?");
    $cpnstmt->execute([$dupProfName]);
    $cpnres = $cpnstmt->fetch(PDO::FETCH_ASSOC);
    if ($cpnres['prof'] > 0) {
        $error = TRUE;
        $response = array('status' => 'failed', 'msg' => 'Profile name already exists!');
    }

    if (!$error) {
        $loggedUserName = $_SESSION['user']['username'];
        $createdTime = time();

        $dupstmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "profile.profileJson (profilename, createdby, createdtime) values (?, ?, ?)");
        $dupstmt->execute([$dupProfName, $loggedUserName, $createdTime]);
        $dupprofileid = $pdo->lastInsertId();

        if ($dupprofileid) {
            $updtstmt = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "profile.profileJson set profilevaluejson = ?, variabledata = ?, darttiledata = ?, profilesequence = ? where pid = ?");
            $updtstmt->execute([$cpres['profilevaluejson'], $cpres['variabledata'], $cpres['darttiledata'], $cpres['profilesequence'], $dupprofileid]);

            $ctilestmt = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.customProfileTiles where profid = ?");
            $ctilestmt->execute([$selprofid]);
            $ctileres = $ctilestmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($ctileres as $value) {
                $ctileistmt = $pdo->prepare("insert into " . $GLOBALS['PREFIX'] . "profile.customProfileTiles (profid, tilename, tiledescription, "
                    . "visibility, ostypes, dartlist, tiledartseq) values (?, ?, ?, ?, ?, ?, ?)");
                $ctileistmt->execute([
                    $dupprofileid, $value['tilename'], $value['tiledescription'], $value['visibility'],
                    $value['ostypes'], $value['dartlist'], $value['tiledartseq']
                ]);
            }
            $response = array('status' => 'success', 'msg' => 'Profile has been duplicated successfully.');
        } else {
            $response = array('status' => 'failed', 'msg' => 'Failed to duplicate profile.');
        }
    }
    echo json_encode($response);
}



function delete_Profile()
{
    $pdo = pdo_connect();

    $profileid = url::requestToText('profileid');

    $mpriv = checkModulePrivilege('deleteprofile', 2);
    if (!$mpriv) {
        exit(json_encode(['status' => 'error', 'msg' => 'Access Denied!']));
    }

    $chkstmt = $pdo->prepare("select scoplevel, scopvalue from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
    $chkstmt->execute([$profileid]);
    $chkstmtres = $chkstmt->fetch(PDO::FETCH_ASSOC);
    $scopvalue = safe_json_decode($chkstmtres['scopvalue'], true);

    $delete_flag = false;
    if ($scopvalue == '') {
        $delete_flag = true;
    } else if (empty($scopvalue['sites']) && empty($scopvalue['groups'])) {
        $delete_flag = true;
    }

    if (!$delete_flag) {
        $response = array('status' => 'failed', 'msg' => 'Action Denied! Can\'t delete profile.<br/>Profile has been already attached to site(s)/group(s).');
    } else {
        $delprofstmt = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "profile.profileJson where pid = ?");
        $delprofstmt->execute([$profileid]);

        $delcuststmt = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "profile.customProfileTiles where profid = ?");
        $delcuststmt->execute([$profileid]);

        $response = array('status' => 'success', 'msg' => 'Profile has been deleted successfully.');
    }
    echo json_encode($response);
}
