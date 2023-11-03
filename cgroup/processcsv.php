<?php

require_once("../include/common_functions.php");
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
$pdo = pdo_connect();
$user_name = $_SESSION["user"]["username"];
$grpCategory = 'Wiz_SCOP_MC';
$global = url::postToText('global');
$groupList = url::postToAny('groups');

if ($_FILES['file']['size'] > 0) {
    $file = $_FILES['file']['tmp_name'];
    $insertedRecords = 0;
    $failedRecords = 0;
    $key = '';
    deleteExistingGroupMachineMap($pdo, $groupList, $user_name);
    $styleName = array();
    $handle = fopen($file, "r");
    $styleName = getStylesName($pdo, $styleName);
    $headerName = array('Site', 'Machine');
    $headerName = array_merge($headerName, $styleName);
    $groupdet = array();
    $total = count(file($file, FILE_SKIP_EMPTY_LINES)) - 1;
    while ($data = fgetcsv($handle, 1000, ",", "'")) {
        if ($data[0] && $data[0] != 'Site') {

            $realSiteName = GRP_GetRealSiteName($key, $pdo, $data[0]);

            $sqlcheck = $pdo->prepare("Select id, censusuniq, censussiteuniq,host from " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? AND UCASE(host) = UCASE(?)");
            $sqlcheck->execute([$realSiteName, $data[1]]);
            $sqlcheckrsultres = $sqlcheck->fetch(PDO::FETCH_ASSOC);

            $data[1] = $sqlcheckrsultres['host'];

            if ($sqlcheckrsultres['id'] != '') {

                foreach ($headerName as $index => $sty) {
                    if ($sty !== 'Site' && $sty != "Machine") {
                        if (!empty($sty)) {
                            $groupdet = array();
                            $styleDet = $pdo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'core.group_styles where style_name = ? limit 1');
                            $styleDet->execute([$sty]);
                            $styleres = $styleDet->fetch(PDO::FETCH_ASSOC);
                            $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
                            logs::log("Mysql in ", $styleres);
                            $human = 1;
                            $style = $styleres['style_number'];
                            $created = time();
                            $event_query = 0;
                            $event_span = 0;
                            $asset_query = 0;
                            $manual = $styleres['style_name'];
                            $det = strpos($data[$index], "|") != false ? array_map('trim', explode("|", $data[$index])) : trim($data[$index]);

                            if (is_array($det)) {
                                $groupdet = array_merge($groupdet, $det);
                            } else {
                                array_push($groupdet, $det);
                            }
                            logs::log("Group det", $groupdet);
                            foreach ($groupdet as $group) {
                                if ($group) {
                                    logs::log("Group detetail", $group);
                                    $chkgroup = GRP_CheckGroupPresentStyle($group, $style, $pdo);
                                    if ($chkgroup == '') {

                                        $mcatuniq = GRP_GetMachineGrpCategoryUniq($key, $pdo, $grpCategory);

                                        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,boolstring,mgroupuniq,mcatuniq) values(?,?,?,?,?,?,?,?,?,?,?,?)";
                                        NanoDB::query($sql, [$group, $user_name, $global, $human, $style, $created, $event_query, $event_span, $asset_query, $manual, md5($manual . $group), $mcatuniq]);
                                        logs::log("Write Mysql insertion", [$group, $user_name, $global, $human, $style, $created, $event_query, $event_span, $asset_query, $manual, md5($manual . $group), $mcatuniq]);
                                        $groupid = GRP_GetMgroupId($group, $user_name, $pdo);
                                        $userlistkey = 'userlist-' . $manual . $group;
                                        $userlistkey = str_replace(' ', '', $userlistkey);

                                        if (isset($_POST[$userlistkey])) {
                                            $userList = $_POST[$userlistkey];
                                            logs::log("Mysql in Users ", $userlistkey . " - " . json_encode($userList));
                                            GRP_GroupMappingUserList($pdo, $userList, $groupid, $user_name, $group);
                                        } else {
                                            GRP_GroupMappingUserList($pdo, '', $groupid, $user_name, $group);
                                        }

                                        $groupuniq = GRP_GetMgroupUniq($group, $user_name, $style, $pdo);

                                        $censusuniq = $sqlcheckrsultres['censusuniq'];
                                        $censussiteuniq = $sqlcheckrsultres['censussiteuniq'];
                                        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,updated) values(?,?,?,?,?)";
                                        $stmt_fi = $pdo->prepare($sql);
                                        $res = $stmt_fi->execute([$groupuniq, $mcatuniq, $censusuniq, $censussiteuniq, time()]);
                                        if ($res == 1 || $res == '1') {
                                            $insertedRecords++;
                                        }
                                        logs::log("If condition");
                                    } else {

                                        $mcatuniq = GRP_GetMachineGrpCategoryUniq($key, $pdo, $grpCategory);
                                        $groupuniq = GRP_GetMgroupUniq($group, $user_name, $style, $pdo);

                                        $censusuniq = $sqlcheckrsultres['censusuniq'];
                                        $censussiteuniq = $sqlcheckrsultres['censussiteuniq'];
                                        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,updated) values(?,?,?,?,?)";
                                        $stmt_fi = $pdo->prepare($sql);
                                        $res = $stmt_fi->execute([$groupuniq, $mcatuniq, $censusuniq, $censussiteuniq, time()]);
                                        if ($res == 1 || $res == '1') {
                                            $insertedRecords++;
                                        }
                                        logs::log("else condition");
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                logs::log("Failed Mysql in no machine Log");
                $failedRecords++;
            }
        }
    }
    $auditRes = create_auditLog('Group', 'Create', 'Success');
    echo json_encode(array("total" => $total, "inserted" => $insertedRecords, "failed" => $failedRecords, "status" => "success"));
} else {
    $auditRes = create_auditLog('Group', 'Create', 'Failed');
    echo json_encode(array("total" => $total, "inserted" => 0, "failed" => 0, "status" => "failed"));
}



function deleteExistingGroupMachineMap($pdo, $groupList, $user_name)
{

    $groupList = array_unique(explode(',', $groupList));

    if ($groupList != '') {

        foreach ($groupList as $group) {
            $chkgroup = GRP_CheckGroupPresent($group, $pdo);
            if ($chkgroup != '') {

                $groupids = GRP_GetMgroupIds($group, $user_name, $pdo);
                foreach ($groupids as $groupid) {

                    GRP_deleteMachineGroupMap($pdo, $groupid['mgroupid']);
                }
            }
        }
    }
}



function GRP_GetRealSiteName($key, $pdo, $siteName)
{
    $key = true;
    $realSiteName = '';
    if ($key) {

        $sql = $pdo->prepare("SELECT customer FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE customer = ? LIMIT 1");
        $sql->execute([$siteName]);
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        if (safe_count($res) > 0) {
            $realSiteName = $res['customer'];
        } else {
            $realSiteName = $siteName;
        }

        return trim($realSiteName);
    } else {
        echo "Your key has been expired";
    }
}

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

function GRP_GetMgroupIds($Gname, $user_name, $pdo)
{

    $sql = $pdo->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? and username = ?");
    $sql->execute([$Gname, $user_name]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    return $result;
}

function GRP_GroupMappingUserList($pdo, $userList, $groupid, $user_name, $Gname)
{

    $insDefSql = $pdo->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
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
