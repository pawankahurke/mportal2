<?php


require_once("../include/common_functions.php");
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
$pdo = pdo_connect();
$user_name = $_SESSION["user"]["username"];
$groupid = url::postToText('grpid');
$grpname = url::postToText('groupname');
$grpCategory = 'Wiz_SCOP_MC';
$global = url::postToText('global');
$userlist = url::postToText('userlist');
$stylelist = url::postToText('stylelist');
$accessstmt = $pdo->prepare("select mgroupid,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=? and username=?");
$accessstmt->execute([$grpname, $user_name]);
$accesscheck = $accessstmt->fetch(PDO::FETCH_ASSOC);
$accessgroupstmt = $pdo->prepare("select groupname from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupname=? and username=?");
$accessgroupstmt->execute([$grpname, $user_name]);
$accessgroupcheck = $accessgroupstmt->fetch(PDO::FETCH_ASSOC);
$file = $_FILES['csvname']['tmp_name'];
$total = count(file($file, FILE_SKIP_EMPTY_LINES)) - 1;

if (
    isset($accesscheck['mgroupid']) &&
    !empty($accesscheck['mgroupid']) &&
    isset($accessgroupcheck['groupname']) &&
    !empty($accessgroupcheck['groupname'])
) {
  if ($_FILES['csvname']['size'] > 0) {
    $insertedRecords = 0;
    $failedRecords = 0;
    $key = '';
    deleteExistingGroupMachineMap($pdo, $groupid, $user_name);
    $style = $stylelist;
    $handle = fopen($file, "r");
    $headerName = array('Site', 'Machine');
    $groupdet = array();
        $stmt = $pdo->prepare("select mgroupid,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $stmt->execute([$grpname]);
        $namechekres = $stmt->fetch(PDO::FETCH_ASSOC);
        $grpchkmgroupid = isset($namechekres['mgroupid']) ? $namechekres['mgroupid'] : false;
        $mgroupuniq = $namechekres['mgroupuniq'];
        if ($grpchkmgroupid == '' || $groupid == $grpchkmgroupid) {

            $file = $_FILES['csvname']['tmp_name'];
            $handle = fopen($file, "r");

            if (!isset($_FILES['csvname'])) {
                $sqlupdate = GRP_GetEditGroup($key, $groupid, $grpname, $pdo, $global, $stylelist);

                if ($sqlupdate) {
                    $jsonreturn = array('msg' => 'Successfully updated csv group info', 'status' => 'success');
                    return true;
                }
            }
          logs::log('Inside');
          GRP_GroupMappingUserList($pdo, $userlist, $groupid, $user_name, $grpname);
          $mcatuniq = GRP_GetMachineGrpCategoryUniq($key, $pdo, $grpCategory);
          $groupuniq = GRP_GetMgroupUniq($grpname, $user_name, $style, $pdo);
          $conInsert = "";
          $valInsert = [];
            while ($data = fgetcsv($handle, 1000, ",", "'")) {
                if ($data[0] && $data[0] != 'Site') {
                  logs::log('Inside while');
                    $realSiteName = GRP_GetRealSiteName($key, $pdo, $data[0]);

//                    $sqlcheck = $pdo->prepare("Select id,censusuniq,censussiteuniq from " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? AND host = ?");
//                    $sqlcheck->execute([$realSiteName, $data[1]]);
//                    $sqlcheckrsultres = $sqlcheck->fetch(PDO::FETCH_ASSOC);

                  $sqlcheck = "Select id,censusuniq,censussiteuniq from " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? AND host = ?";
                  $sqlcheckrsultres = NanoDB::findOneCached($sqlcheck,[$realSiteName, $data[1]]);

                  if ($sqlcheckrsultres['id'] != '') {
                      logs::log('Inside while if');

//                        GRP_GroupMappingUserList($pdo, $userlist, $groupid, $user_name, $grpname);
//                        $mcatuniq = GRP_GetMachineGrpCategoryUniq($key, $pdo, $grpCategory);

//                        $groupuniq = GRP_GetMgroupUniq($grpname, $user_name, $style, $pdo);
                        $censusuniq = $sqlcheckrsultres['censusuniq'];
                        $censussiteuniq = $sqlcheckrsultres['censussiteuniq'];
                      logs::log('\n\n Census unique vlaue ' . $censusuniq . '\n\n');


//                        $sql = "insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,updated) values(?,?,?,?,?)";
//                        $stmt_fi = $pdo->prepare($sql);
//                        $res = $stmt_fi->execute([$groupuniq, $mcatuniq, $censusuniq, $censussiteuniq, time()]);

                    $sql = "insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,updated) values";;
                    $conInsert = $conInsert.",(?,?,?,?,?)";
                    array_push($valInsert,$groupuniq);
                    array_push($valInsert,$mcatuniq);
                    array_push($valInsert,$censusuniq);
                    array_push($valInsert,$censussiteuniq);
                    $time = time();
                    array_push($valInsert,$time);


                    logs::log('\n\n get response ' . $res . '\n\n');
                        if ($res == 1 || $res == '1') {
                            $insertedRecords++;
                          logs::log('Inside while if' . $insertedRecords);
                        }
                    } else {
                      logs::log("Failed Mysql in no machine Log");
                        $failedRecords++;
                    }
                }
            }
          $conInsert = substr($conInsert, 1);
          $sql = $sql.$conInsert;
          NanoDB::insert($sql,$valInsert);
        } else {
            echo json_encode(array("total" => $total, "inserted" => $insertedRecords, "failed" => $failedRecords, "status" => "duplicate"));
        }
        $auditRes = create_auditLog('Group', 'Modify', 'Success');
        echo json_encode(array("total" => $total, "inserted" => $insertedRecords, "failed" => $failedRecords, "status" => "success", 'msg' => 'Successfully updated csv group info'));
    } else {

        $auditRes = create_auditLog('Group', 'Modify', 'Failed');
        echo json_encode(array("total" => $total, "inserted" => 0, "failed" => 0, "status" => "Invalid", "msg" => 'Group updation process failed'));
    }
} else {

    $auditRes = create_auditLog('Group', 'Modify', 'Failed');
    echo json_encode(array("total" => $total, "inserted" => 0, "failed" => 0, "status" => "Invalid", "msg" => "User doesn't have permission to update this group "));
}



function deleteExistingGroupMachineMap($pdo, $grouid, $user_name)
{

    if ($grouid) {

        GRP_deleteMachineGroupMap($pdo, $grouid);
    }
}



function GRP_GetEditGroup($key, $groupid, $grpname, $pdo, $global, $stylelist)
{
    $key = true;
    if ($key) {

        $stylequery = $pdo->prepare('select * from ' . $GLOBALS['PREFIX'] . 'core.group_styles where style_number = ? limit 1');
        $stylequery->execute([$stylelist]);
        $styleRes = $stylequery->fetch(PDO::FETCH_ASSOC);
        $style = $styleRes['style_number'];
        $boolstring = $styleRes['style_name'];
        $updatesql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.MachineGroups set name = ?, global = ?,style= ?,boolstring =? where mgroupid = ?");
        $updatesql->execute([$grpname, $global, $groupid, $style, $boolstring]);
    } else {
        echo "Your key has been expired";
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

    $userchkSql = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.GroupMappings WHERE username = ? and groupid = ?");
    $userchkSql->execute([$user_name, $groupid]);
    $userchkRes = $userchkSql->fetchAll(PDO::FETCH_ASSOC);
    if (safe_count($userchkRes) == 0) {
        $insSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username,modifiedby,modifiedtime) VALUES (?, ?, ?,?,?)");
        $insSql->execute([$groupid, $Gname, $user_name, $user_name, time()]);
    } else {

        $insSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.GroupMappings set modifiedby = ?,modifiedtime = ? where groupid =? and username = ?  ");
        $insSql->execute([$user_name, time(), $groupid, $user_name]);
    }



    if ($userList != '') {
        $userList = explode(',', $userList);
        $user_in = str_repeat('?,', safe_count($userList) - 1) . '?';
        $userSql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($user_in)");
        $userSql->execute($userList);
        $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($userRes as $value) {

            $username = $value['username'];
            $userchkSql = $pdo->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.GroupMappings WHERE username = ? and groupid = ?");
            $userchkSql->execute([$username, $groupid]);
            $userchkRes = $userchkSql->fetchAll(PDO::FETCH_ASSOC);
            if (safe_count($userchkRes) == 0) {
                $insSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username,modifiedby,modifiedtime) VALUES (?, ?, ?,?,?)");
                $insSql->execute([$groupid, $Gname, $username, $user_name, time()]);
            } else {

                $insSql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.GroupMappings set modifiedby = ?,modifiedtime = ? where groupid =? and username = ?  ");
                $insSql->execute([$user_name, time(), $groupid, $username]);
            }
        }
    }
}
