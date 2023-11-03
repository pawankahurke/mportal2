<?php

require_once("../include/common_functions.php");
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
$pdo = pdo_connect();

$key = '';
$gname_temp = url::postToText('groupname');
if (preg_match('/\s/', $gname_temp)) {
    $group = str_replace(' ', '_', $gname_temp);
} else {
    $group = $gname_temp;
}

$machinelist = url::postToText('machinelist');

$grpmachlist = explode(',', $machinelist);
$grpCategory = 'Wiz_SCOP_MC';
$user_name = $_SESSION["user"]["username"];
$global = url::postToText('global');
$userList = url::postToText('userlist');
$sty = url::postToText('style');
$styleDet = $pdo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'core.group_styles where style_name = ? limit 1');
$styleDet->execute([$sty]);
$styleres = $styleDet->fetch(PDO::FETCH_ASSOC);
$style = $styleres['style_number'];
$manual = $styleres['style_name'];


$checkname = GRP_CheckGroupPresentStyle($group, $style, $pdo);

if ($checkname == '') {

    $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
    $human = 1;
    $created = time();
    $event_query = 0;
    $event_span = 0;
    $asset_query = 0;

    $mcatuniq = GRP_GetMachineGrpCategoryUniq($key, $pdo, $grpCategory);

    $sql = "insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,boolstring,mgroupuniq,mcatuniq) values(?,?,?,?,?,?,?,?,?,?,?,?)";
    NanoDB::query($sql, [$group, $user_name, $global, $human, $style, $created, $event_query, $event_span, $asset_query, $manual, md5($manual . $group), $mcatuniq]);
    logs::log(__FILE__, __LINE__, "Write Mysql insertion" . json_encode([$group, $user_name, $global, $human, $style, $created, $event_query, $event_span, $asset_query, $manual, md5($manual . $group), $mcatuniq]) . "\n\n", 0);
    $groupid = GRP_GetMgroupId($group, $user_name, $pdo);
    $userlistkey = 'userlist';
    $userlistkey = str_replace(' ', '', $userlistkey);

    if (isset($_POST[$userlistkey])) {
        $userList = $_POST[$userlistkey];
        GRP_GroupMappingUserList($pdo, $userList, $groupid, $user_name, $group);
    } else {
        GRP_GroupMappingUserList($pdo, '', $groupid, $user_name, $group);
    }




    $groupuniq = GRP_GetMgroupUniq($group, $user_name, $style, $pdo);




    if (safe_count($grpmachlist) > 0) {

        foreach ($grpmachlist as $m) {

            $sqlcheck = $pdo->prepare("Select id,censusuniq,censussiteuniq from " . $GLOBALS['PREFIX'] . "core.Census WHERE id = ? limit 1");
            $sqlcheck->execute([$m]);
            $sqlcheckrsultres = $sqlcheck->fetch(PDO::FETCH_ASSOC);
            $censusuniq =  $sqlcheckrsultres['censusuniq'];
            $censussiteuniq =  $sqlcheckrsultres['censussiteuniq'];
            $sql = "insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,updated) values(?,?,?,?,?)";
            $stmt_fi = $pdo->prepare($sql);
            $res = $stmt_fi->execute([$groupuniq, $mcatuniq, $censusuniq, $censussiteuniq, time()]);
        }
        $auditRes = create_auditLog('Group', 'Create', 'Success');
        $jsonreturn = array('status' => 'success');
    } else {
        $auditRes = create_auditLog('Group', 'Create', 'Success');
        $jsonreturn = array('status' => 'notupdated');
    }
} else {
    $auditRes = create_auditLog('Group', 'Create', 'Failed');
    $jsonreturn = array('status' => 'Failed');
}

echo json_encode($jsonreturn);



function getStylesName($pdo, $header)
{
    $stylesel = $pdo->query('select * from ' . $GLOBALS['PREFIX'] . 'core.group_styles');
    $styleres = $stylesel->fetchAll();
    foreach ($styleres as $styler) {
        array_push($header, $styler['style_name']);
    }
    return $header;
}

function GRP_GetMgroupId($Gname, $user_name, $pdo)
{

    $sql = $pdo->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? and username = ?");
    $sql->execute([$Gname, $user_name]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $mgroupid = $result['mgroupid'];

    return $mgroupid;
}

function GRP_GetMgroupUniq($Gname, $user_name, $style, $pdo)
{

    $sql = $pdo->prepare("SELECT mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE lower(name) = ? and username = ? and style = ?");
    $sql->execute([strtolower($Gname), $user_name, $style]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $mgroupuniq = $result['mgroupuniq'];

    return $mgroupuniq;
}

function GRP_CheckGroupPresent($Gname, $pdo)
{

    $checksql = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ? limit 1");
    $checksql->execute([strtolower($Gname)]);
    $checkres = $checksql->fetch(PDO::FETCH_ASSOC);
    $mgroupid = $checkres['mgroupid'];

    return $mgroupid;
}

function GRP_CheckGroupPresentStyle($Gname, $style, $pdo)
{

    $checksql = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ? and style = ? limit 1");
    $checksql->execute([strtolower($Gname), $style]);
    $checkres = $checksql->fetch(PDO::FETCH_ASSOC);
    $mgroupid = $checkres['mgroupid'];

    return $mgroupid;
}

function GRP_GetMachineGrpCategory($key, $pdo, $categoryname)
{
    $key = true;
    if ($key) {
        $categorysql = $pdo->prepare("select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category = ?");
        $categorysql->execute([$categoryname]);
        $categoryres = $categorysql->fetch(PDO::FETCH_ASSOC);
        $mcatid = $categoryres['mcatid'];
    } else {
        echo "Your key has been expired";
    }
    return $mcatid;
}

function GRP_GetMachineGrpCategoryUniq($key, $pdo, $categoryname)
{
    $key = true;
    if ($key) {
        $categorysql = $pdo->prepare("select mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category = ?");
        $categorysql->execute([$categoryname]);
        $categoryres = $categorysql->fetch(PDO::FETCH_ASSOC);
        $mcatuniq = $categoryres['mcatuniq'];
    } else {
        echo "Your key has been expired";
    }
    return $mcatuniq;
}

function GRP_deleteMachineGroupMap($pdo, $groupid)
{


    $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
        . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq = " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join " . $GLOBALS['PREFIX'] . "core.Census on "
        . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq = " . $GLOBALS['PREFIX'] . "core.Census.censusuniq) where mgroupid = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$groupid]);
}

function GRP_GroupMappingUserList($pdo, $userList, $groupid, $user_name, $Gname)
{

    $insDefSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
    $insDefSql->execute([$groupid, $Gname, $user_name]);


    if ($userList != '') {
        $userList = explode(',', $userList);
        $user_in = str_repeat('?,', safe_count($userList) - 1) . '?';
        $userSql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($user_in)");
        $userSql->execute($userList);
        $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($userRes as $value) {
            $username = $value['username'];
            $insSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
            $insSql->execute([$groupid, $Gname, $username]);
        }
    }
}
