<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dashboard.php';

function GRP_ViewGroups($key, $username, $ch_id, $grpCategory, $pdo)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

        $sibling_users = $_SESSION['user']['logged_username'];

        if ($mcatid != '') {

            $allgroupsql = $pdo->prepare("select mg.mgroupid,mg.username,mg.name,mc.mcatid,created,mg.boolstring,mg.style,mg.global from " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg join " . $GLOBALS['PREFIX'] . "core.MachineCategories"
                . " as mc on mg.mcatuniq = mc.mcatuniq where mc.mcatid = ? and (username = ? or (global = '1' and username in "
                . "('" . $sibling_users . "')))");
            $allgroupsql->execute([$mcatid, $username]);
        } else {
            return array();
        }
        $allgroupres = $allgroupsql->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $allgroupres;
}



function GRP_GetAllSiblingUsers($ch_id, $pdo)
{

    $sql = $pdo->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where ch_id = ?");
    $sql->execute([$ch_id]);
    $res = $sql->fetchAll();
    $sibling_users = [];
    foreach ($res as $key => $val) {
        $sibling_users[] = "'" . $val['username'] . "'";
    }
    return $sibling_users;
}



function GRP_GetMachineGrpCategory($key, $pdo, $categoryname)
{
    $key = DASH_ValidateKey($key);
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



function GRP_GetMachineList($id, $GroupUsername, $pdo)
{

    $machinelistsql = $pdo->prepare("select count(c.host) host from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,core.MachineGroupMap mgm,core.Census c where mg.username = ?"
        . " and mg.mgroupuniq = mgm.mgroupuniq and mgm.censusuniq = c.censusuniq and mg.mgroupid = ? order by name");
    $machinelistsql->execute([$GroupUsername, $id]);
    $machinelistres = $machinelistsql->fetch();
    $machinecount = $machinelistres['host'];
    return $machinecount;
}



function GRP_ViewGroupDetail($key, $groupid, $pdo)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $viewdtlsql = $pdo->prepare("select c.host host,c.site as site, c.last from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,core.MachineGroupMap mgm,core.Census c where "
            . "mg.mgroupuniq = mgm.mgroupuniq and mgm.censusuniq = c.censusuniq and mg.mgroupid = ?");
        $viewdtlsql->execute([$groupid]);
        $viewdtlres = $viewdtlsql->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $viewdtlres;
}

function hasCsvContent($filename)
{
    $file = $_FILES[$filename]['tmp_name'];
    $handle = fopen($file, "r");
    $count = 0;

    for ($i = 0; $i < 2; $i++) {
        $line = fgetcsv($handle);
        if ($i == 1) {
            $hasContent = (isset($line) && $line) ? true : false;
            break;
        }
    }

    return $hasContent;
}



function GRP_AddGroup($key, $Gname, $user_name, $grpCategory, $pdo, $global, $userList)
{

    $file = $_FILES['csvname']['tmp_name'];
    $handle = fopen($file, "r");

    $minimumTwoMachinesFound = true;
    for ($z = 0; $z <= 2; $z++) {
        $line = fgets($handle);
        if ($z >= 2) {
            if (!isset($line) || !$line || empty($line)) {
                $minimumTwoMachinesFound = false;
                break;
            }
        }
    }

    if (!$minimumTwoMachinesFound) {
        $jsonreturn = array('error' => 'no-minimum-machines');
        return $jsonreturn;
    }

    if (!hasCsvContent('csvname')) {
        $jsonreturn = array('error' => 'nodata');
        return $jsonreturn;
    }

    $checkGname = GRP_CheckGroupPresent($Gname, $pdo);

    if ($checkGname == '') {
        $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

        $human = 1;
        $style = 2;
        $created = time();
        $event_query = 0;
        $event_span = 0;
        $asset_query = 0;
        $manual = 'CSV';

        if ($_FILES['csvname']['size'] > 0) {

            $file = $_FILES['csvname']['tmp_name'];
            $handle = fopen($file, "r");
            $success = 0;
            $failed = 0;
            $total = 0;


            do {

                if ($data[0] && $data[0] != 'Site') {

                    $realSiteName = GRP_GetRealSiteName($key, $pdo, $data[0]);

                    $sqlcheck = $pdo->prepare("Select id from " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? AND host = ?");
                    $sqlcheck->execute([$realSiteName, $data[1]]);
                    $sqlcheckrsultres = $sqlcheck->fetch(PDO::FETCH_ASSOC);

                    if ($sqlcheckrsultres['id'] != '') {



                        $sql = "insert ignore into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,boolstring,mgroupuniq,mcatuniq)"
                            . "select ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, md5(concat(mcatuniq,',', ?)), mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?";
                        NanoDB::query($sql, [$Gname, $user_name, $global, $human, $style, $created, $event_query, $event_span, $asset_query, $manual, $Gname, $mcatid]);

                        $groupid = GRP_GetMgroupId($Gname, $user_name, $pdo);

                        $stmt_on = $pdo->prepare('DROP TABLE ' . $GLOBALS['PREFIX'] . 'core.temp_table_1');
                        $stmt_on->execute();
                        $maketemp = "CREATE TABLE " . $GLOBALS['PREFIX'] . "core.temp_table_1 (`group` varchar(50), `site` varchar(50), `machine` varchar(50),`status` varchar(11))";
                        $stmt_tw = $pdo->prepare($maketemp);
                        $stmt_tw->execute();



                        $sql_c = "select id from " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? AND host = ?";
                        $stmt_th = $pdo->prepare($sql_c);
                        $stmt_th->execute([$realSiteName, $data[1]]);
                        $row_c = $stmt_th->fetch(PDO::FETCH_ASSOC);
                        if ($row_c) {
                            $machines[] = $row_c['id'];
                            $inserttemp = "insert INTO " . $GLOBALS['PREFIX'] . "core.temp_table_1 (`group`, `site`, `machine`, `status`) values (?, ?, ?, ?)";
                            $stmt_fo = $pdo->prepare($inserttemp);
                            $stmt_fo->execute([$Gname, $realSiteName, $data[1], 'Success']);
                        } else {
                            $inserttemp = "insert INTO " . $GLOBALS['PREFIX'] . "core.temp_table_1 (`group`, `site`, `machine`, `status`) values (?, ?, ?, ?)";
                            $stmt_fo = $pdo->prepare($inserttemp);
                            $stmt_fo->execute([$Gname, $realSiteName, $data[1], 'Failed']);
                        }
                    } else {
                        $jsonreturn = array('error' => 'Invalid');
                    }
                    $total++;
                } else {
                    $jsonreturn = array('error' => 'nodata');
                }
            } while ($data = fgetcsv($handle, 1000, ",", "'"));

            $insDefSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
            $insDefSql->execute([$groupid, $Gname, $user_name]);
            $userList = explode(',', $userList);

            if ($userList != '') {
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
        } else {
            $jsonreturn = array('error' => 'Invalid');
        }

        $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
            . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq = " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join " . $GLOBALS['PREFIX'] . "core.Census on "
            . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq = " . $GLOBALS['PREFIX'] . "core.Census.censusuniq) where mgroupid = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$groupid]);

        if (safe_count($machines) > 0) {
            $insertedRecords = 0;
            foreach ($machines as $m) {
                $sql = "insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT mgroupuniq, "
                    . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, censusuniq, censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, "
                    . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?";
                $stmt_fi = $pdo->prepare($sql);
                $res = $stmt_fi->execute([$m, $mcatid, $groupid]);
                if ($res == 1 || $res == '1') {
                    $insertedRecords++;
                }
            }
            $failed = $total - $insertedRecords;

            if ($insertedRecords > 1 && $failed > 1) {
                $jsonreturn = array('msg' => $insertedRecords . ' machines updated.' . $failed . ' machines failed to update.');
            } else if ($insertedRecords > 1) {
                $jsonreturn = array('msg' => $insertedRecords . ' machines updated.' . $failed . ' machine failed to update.');
            } else if ($failed > 1) {
                $jsonreturn = array('msg' => $insertedRecords . ' machine updated.' . $failed . ' machines failed to update.');
            } else {
                $jsonreturn = array('msg' => $insertedRecords . ' machine updated.' . $failed . ' machine failed to update.');
            }
        }
    } else {
        $jsonreturn = array('status' => 'Failed');
    }

    return $jsonreturn;
}



function GRP_CheckGroupPresent($Gname, $pdo)
{

    $checksql = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ? limit 1");
    $checksql->execute([strtolower($Gname)]);
    $checkres = $checksql->fetch(PDO::FETCH_ASSOC);
    $mgroupid = $checkres['mgroupid'];

    return $mgroupid;
}



function GRP_GetMgroupId($Gname, $user_name, $pdo)
{

    $sql = $pdo->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? and username = ?");
    $sql->execute([$Gname, $user_name]);
    $result = $sql->fetch(PDO::FETCH_ASSOC);
    $mgroupid = $result['mgroupid'];

    return $mgroupid;
}



function GRP_EditGroup($key, $groupid, $grpname, $grpCategory, $pdo, $global, $userList)
{

    $stmt = $pdo->prepare("select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
    $stmt->execute([$grpname]);
    $namechekres = $stmt->fetch(PDO::FETCH_ASSOC);
    $grpchkmgroupid = isset($namechekres['mgroupid']) ? $namechekres['mgroupid'] : false;

    if ($grpchkmgroupid == '' || $groupid == $grpchkmgroupid) {

        $file = $_FILES['csvname']['tmp_name'];
        $handle = fopen($file, "r");

        if (!isset($_FILES['csvname'])) {
            $sqlupdate = GRP_GetEditGroup($key, $groupid, $grpname, $pdo, $global);

            if ($sqlupdate) {
                $jsonreturn = array('msg' => 'Successfully updated csv group info', 'status' => 'success');
                return true;
            }
        }

        if (isset($_FILES['csvname'])) {

            $minimumTwoMachinesFound = true;
            for ($z = 0; $z <= 2; $z++) {
                $line = fgets($handle);
                if ($z >= 2) {
                    if (!isset($line) || !$line || empty($line)) {
                        $minimumTwoMachinesFound = false;
                        break;
                    }
                }
            }

            if (!$minimumTwoMachinesFound) {
                $jsonreturn = array('error' => 'no-minimum-machines');
                return $jsonreturn;
            }

            $sqlupdate = GRP_GetEditGroup($key, $groupid, $grpname, $pdo, $global);
            $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

            $stmt_on = $pdo->prepare('DROP TABLE ' . $GLOBALS['PREFIX'] . 'core.temp_table_1');
            $stmt_on->execute();
            $maketemp = "CREATE TABLE " . $GLOBALS['PREFIX'] . "core.temp_table_1 (`group` varchar(50), "
                . "`site` varchar(50), `machine` varchar(50),`status` varchar(11))";
            $stmt_tw = $pdo->prepare($maketemp);
            $stmt_tw->execute();

            $existList = [];
            $sql_machines = "select id from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap  left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
                . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq = " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join "
                . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq = " . $GLOBALS['PREFIX'] . "core.Census.censusuniq) "
                . "where mgroupid = ?";
            $stmt_th = $pdo->prepare($sql_machines);
            $stmt_th->execute([$groupid]);
            $group_machines = $stmt_th->fetchAll(PDO::FETCH_ASSOC);

            foreach ($group_machines as $value) {
                $existList[] = $value['id'];
            }

            $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
                . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq = " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join " . $GLOBALS['PREFIX'] . "core.Census on "
                . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq = " . $GLOBALS['PREFIX'] . "core.Census.censusuniq) where mgroupid = ?";
            $stmt_fo = $pdo->prepare($sql);
            $stmt_fo->execute([$groupid]);

            if ($_FILES['csvname']['size'] > 0) {
                $sql_g = $pdo->prepare("select c.id from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mm,core.MachineGroups mg, "
                    . $GLOBALS['PREFIX'] . "core.Census c where mm.mgroupuniq = mg.mgroupuniq and mm.censusuniq = c.censusuniq and mg.mgroupid = ?");
                $sql_g->execute([$groupid]);
                $query_g = $sql_g->fetchAll(PDO::FETCH_ASSOC);
                foreach ($query_g as $value) {
                    $machines[] = $value['id'];
                }

                $file = $_FILES['csvname']['tmp_name'];
                $handle = fopen($file, "r");
                $success = 0;
                $failed = 0;
                do {
                    if ($data[0] && $data[0] != 'Site') {
                        $realSiteName = GRP_GetRealSiteName($key, $pdo, $data[0]);
                        $sql_c = $pdo->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census WHERE site = ? AND host = ?");
                        $sql_c->execute([$realSiteName, $data[1]]);

                        $query_c = $sql_c->fetchAll(PDO::FETCH_ASSOC);
                        mysqli_query($pdo, $sql_c);
                        $num_rows = mysqli_num_rows($query_c);
                        if ($num_rows == 1) {
                            $row_c = mysqli_fetch_assoc($query_c);
                            if (!in_array($row_c['id'], $machines)) {
                                $machines[] = $row_c['id'];
                            }
                            $inserttemp = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.temp_table_1 (`group`, `site`, `machine`, `status`) values "
                                . "('{$grpname}','$realSiteName', '$data[1]','Success')");
                            $inserttemp->execute();
                            $success++;
                        } else {
                            $inserttemp = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.temp_table_1 (`group`, `site`, `machine`, `status`) values "
                                . "('{$grpname}','$realSiteName', '$data[1]','Failed')");
                            $inserttemp->execute();
                            $failed++;
                        }
                    }
                } while ($data = fgetcsv($handle, 1000, ",", "'"));
                $updatedCnt = safe_count($machines);
                $failedCont = $success - safe_count($machines);
                $failedEnty = ($failedCont > 1) ? 'machines' : 'machine';
                $jsonreturn = array('msg' => $updatedCnt . ' devices updated. ' . $failedCont . $failedEnty . ' failed to update.', 'status' => 'success');
            } else {
                $jsonreturn = array('error' => 'Invalid');
            }

            $deleted_machines = array_diff($existList, $machines);

            if (safe_count($machines) > 0) {
                foreach ($machines as $m) {
                    $sql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) SELECT "
                        . "mgroupuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, censusuniq, censussiteuniq FROM " . $GLOBALS['PREFIX'] . "core.Census, "
                        . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?");
                    $sql->execute([$m, $mcatid, $groupid]);
                }
            } else {
                $sql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on "
                    . "(" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = ?");
                $sql->execute([$groupid]);
            }

            if (safe_count($deleted_machines) > 0) {

                foreach ($deleted_machines as $dm) {
                    $sql_map = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap_deleted (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,delrev) SELECT mgroupuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, "
                        . "censusuniq, censussiteuniq,1 FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND "
                        . "mgroupid = ? ON DUPLICATE KEY UPDATE delrev=delrev+1");
                    $sql_map->execute([$dm, $mcatid, $groupid]);

                    $sql_permmap = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMapPerm_deleted (mgroupuniq,mcatuniq,censusuniq,censussiteuniq,delrev) SELECT mgroupuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, "
                        . "censusuniq, censussiteuniq,1 FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND "
                        . "mgroupid = ? ON DUPLICATE KEY UPDATE delrev=delrev+1");
                    $sql_permmap->execute([$dm, $mcatid, $groupid]);
                }
            }
        } else {
            $jsonreturn = array('msg' => 'Successfully updated csv group info', 'status' => 'success');
        }

        if ($userList != '') {
            $logged_username = $_SESSION['user']['username'];
            $timenow = time();

            $delSql = $pdo->prepare("delete FROM " . $GLOBALS['PREFIX'] . "core.GroupMappings WHERE groupname = ?");
            $delSql->execute([$grpname]);

            $insDefSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username, modifiedby, modifiedtime) "
                . "VALUES (?,?,?,?,?)");
            $insDefSql->execute([$groupid, $grpname, $logged_username, $logged_username, $timenow]);

            $userSql = $pdo->prepare("select username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($userList)");
            $userSql->execute();
            $userRes = $userSql->fetchAll();
            foreach ($userRes as $value) {
                $grpusername = $value['username'];
                $insSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username, modifiedby, modifiedtime) "
                    . "VALUES (?,?,?,?,?)");
                $insSql->execute([$groupid, $grpname, $grpusername, $logged_username, $timenow]);
            }
        }
    } else {
        $jsonreturn = array('status' => 'duplicate');
    }

    return $jsonreturn;
}



function GRP_GetEditGroup($key, $groupid, $grpname, $pdo, $global)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $updatesql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.MachineGroups set name = ?, global = ? where mgroupid = ?");
        $updatesql->execute([$grpname, $global, $groupid]);
    } else {
        echo "Your key has been expired";
    }
}



function GRP_updateGroupGlobal($groupid, $pdo, $global)
{

    $updatesql = $pdo->prepare("update " . $GLOBALS['PREFIX'] . "core.MachineGroups set global = ? where mgroupid = ?");
    $updatesql->execute([$global, $groupid]);
}

function GRP_EditGroupName($key, $gid, $pdo, $count, $groupname)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($count == '0') {
            $namesql = $pdo->prepare("select name,boolstring,global from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ?");
            $namesql->execute([$gid]);
        } else {

            $sql = $pdo->prepare("select censusuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp "
                . "where m.mgroupuniq=mp.mgroupuniq and m.mgroupid = ?");
            $sql->execute([$gid]);
            $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($sqlRes as $val) {
                $censusuniq[] = $val['censusuniq'];
            }
            $cen_in = str_repeat('?,', safe_count($censusuniq) - 1) . '?';
            $namesql = $pdo->prepare("select site,host,born,last,id,os from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($cen_in)");
            $namesql->execute($censusuniq);
        }
        $nameresult = $namesql->fetchAll(PDO::FETCH_ASSOC);
    } else {
        echo "Your key has been expired";
    }

    return $nameresult;
}



function GRP_GroupDelete($strid, $pdo)
{
    $strname = $strid;
    $rparent = $_SESSION['rparentName'];
    $groupsql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ?  limit 1");
    $groupsql->execute([strtolower($strname)]);
    $selectres = $groupsql->fetch(PDO::FETCH_ASSOC);
    $strid = $selectres['mgroupid'];
    $struniq = $selectres['mgroupuniq'];

    $ins_del = "insert into MachineGroups_deleted(mcatuniq,mgroupuniq,delrev) "
        . "select mcatuniq,mgroupuniq,1 from MachineGroups where mgroupid = ? "
        . "ON DUPLICATE KEY UPDATE delrev=delrev+1";
    $stmt = $pdo->prepare($ins_del);
    $stmt->execute([$strid]);

    $deletemgmsql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap where mgroupuniq = ?");
    $deletemgmres = $deletemgmsql->execute([$struniq]);

    $deletemgsql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ?");
    $deletemgres = $deletemgsql->execute([$strid]);

    $deleteGesql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.GroupExpression where mgroupid = ?");
    $deleteGesql->execute([strtolower($strid)]);

    $deletePcsql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "softinst.PatchConfig where mgroupid = ?");
    $deletePcsql->execute([$strid]);

    $deleteWusql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "softinst.WUConfig where mgroupid = ?");
    $deleteWusql->execute([$strid]);

    $delGMapsSql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupid = ?");
    $delGMapsSql->execute([$strid]);

    if (safe_count($deletemgres) > 0) {
        return true;
    } else {
        return false;
    }
}



function GRP_SampleFileExport()
{

    $index = 2;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Site');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Machine');

    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Nanoheal_Customer1');
    $objPHPExcel->getActiveSheet()->setCellValue('B2', '5CG52822LW');
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Nanoheal_Customer2');
    $objPHPExcel->getActiveSheet()->setCellValue('B3', 'C07M81RYDWYL');
    $index;

    $fn = "SampleGroupList.csv";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
    ob_end_clean();
    $objWriter->save('php://output');
}



function GRP_ViewGroupExport($key, $username, $ch_id, $grpCategory, $pdo)
{
    $index = 2;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Group');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Machine Count');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Type');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Created Time');

    $groupresult = GRP_ViewGroups($key, $username, $ch_id, $grpCategory, $pdo);;

    if ($groupresult) {
        foreach ($groupresult as $key => $value) {
            $id = $value['mgroupid'];
            $group = UTIL_GetTrimmedGroupName($value['name']);
            $created = $value['created'];
            $GroupUsername = $value['username'];
            $machinescount = GRP_GetMachineList($id, $GroupUsername, $pdo);
            $count = $machinescount;
            $type = $value['boolstring'];
            $global = $value['global'] == '1' ? 'Yes' : 'No';

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $group);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $count);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $type);
            $objPHPExcel = UTIL_GetExcelFormattedDate($objPHPExcel, $created, $index, 'D', 'mm/dd/yyyy hh:mm:ss AM/PM');
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
    $fn = "GroupDataList.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}



function GRP_ViewGroupDetailExport($key, $groupid, $grpname, $pdo)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $strname = $_SESSION['searchValue'];
        $rparent = $_SESSION['rparentName'];
        $groupsql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "core.MachineGroups where lower(name) = ?  limit 1");
        $groupsql->execute([strtolower($strname)]);
        $selectres = $groupsql->fetch(PDO::FETCH_ASSOC);
        $gname = $grpname;
        $censusuniq = '';
        $index = 2;
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Group Name');
        $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Group Type');
        $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Created By');
        $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Created Date Time');
        $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Modified By');
        $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Modified Date Time');
        $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Site Name');
        $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Machine Name');
        $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Machine OS');
        $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Born Date');
        $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Last event');
        $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Timezone');
        $timezone = $_SESSION['user']['usertimezone'];
        $machinelist = GRP_ViewGroupDetail($key, $groupid, $pdo);
        $searchVal = $_SESSION['searchValue'];
        $sql = $pdo->prepare("select censusuniq, boolstring, m.username, m.created from " . $GLOBALS['PREFIX'] . "core.MachineGroups m inner join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap mp where m.mgroupuniq=mp.mgroupuniq and m.mgroupid =?");
        $sql->execute([$groupid]);
        $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);

        $groupType = $sqlRes[0]['boolstring'];
        $createdBy = $sqlRes[0]['username'];
        $createdTime = date('m/d/Y g:i:s A', $sqlRes[0]['created']);
        foreach ($sqlRes as $val) {
            $censusuniq .= $val['censusuniq'] . ",";
        }
        $censusuniq = rtrim($censusuniq, ',');
        $censusuniq = explode(',', $censusuniq);
        $gmsql = "select modifiedby, modifiedtime from " . $GLOBALS['PREFIX'] . "core.GroupMappings where groupname IN "
            . "(select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = ?) limit 1;";
        $gmstmt = $pdo->prepare($gmsql);
        $gmstmt->execute([$groupid]);
        $gmres = $gmstmt->fetch(PDO::FETCH_ASSOC);
        $modifiedBy = ($gmres['modifiedby'] != '') ? $gmres['modifiedby'] : '-';
        $modifiedTime = ($gmres['modifiedtime'] != '') ? date('m/d/Y g:i:s A', $gmres['modifiedtime']) : '-';

        $in  = str_repeat('?,', safe_count($censusuniq) - 1) . '?';
        $sql1 = $pdo->prepare("select site,host,born,last,id,os from " . $GLOBALS['PREFIX'] . "core.Census c where c.censusuniq in ($in)");
        $sql1->execute($censusuniq);
        $sql1Res = $sql1->fetchAll();


        if ($machinelist) {
            foreach ($sql1Res as $val) {
                $host = $val['host'];
                $os = $val['os'];
                $born = ($val['born'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['born']);
                $last = ($val['last'] == 0) ? '-' : date('m/d/Y g:i:s A', $val['last']);
                $id = $val['id'];
                if ($os == '') {
                    $os = '-';
                }
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $gname);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $groupType);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $createdBy);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $createdTime);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $modifiedBy);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $modifiedTime);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, '' . UTIL_GetTrimmedGroupName($val["site"]) . '');
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, '' . $val["host"] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, '' . $val["os"] . '');
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, $born);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, $last);
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $index, $timezone);
                $index++;
            }
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
        }
        $fn = "GroupDetailsList.xls";
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fn . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');
    } else {
        echo "Your key has been expired";
    }
}


function GRP_GroupDetailExport($key, $pdo)
{

    $key = DASH_ValidateKey($key);
    $key = '';
    $pdo = pdo_connect();
    $username = $_SESSION["user"]["username"];
    $grpCategory = 'Wiz_SCOP_MC';

    $category = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);
    // $result1 = GRP_GetAdvancedGroupGridData($key, $pdo, $username, $category);
    $result2 = GRP_GetGroupGridData($key, $pdo, $username, "", "", "");
    $result2 = safe_json_decode($result2, true);
    // $result = array_merge($result1, $result2['data']);
    $result = $result2['data'];
    $index = 2;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);

    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(55);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(55);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(55);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Group Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Machine Count');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Group Type');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Created By');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Created Date Time');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Modified By');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Modified Date Time');
    $objPHPExcel->getActiveSheet()->setTitle("Group Details");
    if (safe_count($result) > 0) {
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
            $gname = utf8_encode(UTIL_GetTrimmedGroupName($value['name']));
            $machine = $value['number'];
            $createdBy = $value['username'];
            $createdTime = $created_Time;
            $modifiedBy = $modified_By;
            $modifiedTime = $modified_Time;
            $groupname = $value['name'];
            if ($value['boolstring'] == '') {
                $groupType = "Dynamic Group";
            } else {
                $groupType = $value['boolstring'];
            }
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $gname);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $machine);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $groupType);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $createdBy);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $createdTime);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $modifiedBy);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, $modifiedTime);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    $fn = "GroupDetails.xlsx";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    ob_end_clean();
    $objWriter->save('php://output');
}


function GRP_ManualGrpList($key, $pdo, $user_sites, $user_name, $list)
{
    $where = '';
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($list != '') {
            $list_in = str_repeat('?,', safe_count($list) - 1) . '?';
            $where = "and c.id not in ($list_in)";
        }

        $newUserSites = array();
        foreach ($user_sites as $site) {
            $site = preg_replace('/\s+/', ' ', $site);
            if ($site != '' and $site != "''" and $site != '""' and $site != "' '" and $site != '\' \'' and $site) {
                $newUserSites[] = $site;
            }
        }

        $user_in = str_repeat('?,', safe_count($newUserSites) - 1) . '?';
        if ($list != '') {
            $bindings = array_merge($newUserSites, [$user_name], $list);
        } else {
            $bindings = array_merge($newUserSites, [$user_name]);
        }

        $newUserSites = array_filter($newUserSites, function ($value) {
            return trim(preg_replace('/\s+/', ' ', $value));
        });

        $newUserSites = join(",", $newUserSites);

        $sql = "select distinct c.host as host,c.site as site, c.id id from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,"
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap mgm,core.Census c ,core.Customers u where mg.mgroupuniq = "
            . "mgm.mgroupuniq and mgm.censusuniq = c.censusuniq and c.site = u.customer and "
            . "u.customer in (" . $newUserSites . ") and u.username = '" . $user_name . "' $where";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $sqlres = $stmt->fetchAll();
    } else {
        echo "Your key has been expired";
    }

    return $sqlres;
}

function GRP_AddManualGroup($key, $pdo, $Gname, $grpCategory, $user_name, $machines, $global, $userList, $type = '')
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

        $human = 1;
        $style = 2;
        $created = time();
        $event_query = 0;
        $event_span = 0;
        $asset_query = 0;
        // $manual = 'Manual';
        $type = '' ? 'Manual' : $type;

        $sql = "insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,boolstring,mgroupuniq,mcatuniq)"
            . "select '$Gname','$user_name',$global,$human,$style,$created,$event_query,$event_span,$asset_query,'$type',"
            . "md5(concat(mcatuniq,',','$Gname')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?";
        NanoDB::query($sql, [$mcatid]);

        $groupid = GRP_GetMgroupId($Gname, $user_name, $pdo);

        $insDefSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
        $insDefSql->execute([$groupid, $Gname, $user_name]);
        $userList = explode(',', $userList);
        if ($userList != '') {
            $user_in = str_repeat('?,', safe_count($userList) - 1) . '?';
            $userSql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($user_in)");
            $userSql->execute($userList);
            $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($userRes as $value) {
                $insSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
                $insSql->execute([$groupid, $Gname, $value['username']]);
            }
        }

        $sql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap "
            . "left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq = " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) "
            . "left join " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq = " . $GLOBALS['PREFIX'] . "core.Census.censusuniq) where mgroupid = ?");
        $sql->execute([$groupid]);

        if (safe_count($machines) > 0) {

            foreach ($machines as $m) {
                $sql = "insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) "
                    . "SELECT mgroupuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, censusuniq, censussiteuniq FROM "
                    . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$m, $mcatid, $groupid]);
            }
            $jsonreturn = array('status' => 'success');
        } else {

            $jsonreturn = array('status' => 'notupdated');
        }
    } else {
        echo "Your key has been expired";
    }
    return $jsonreturn;
}

function GRP_UpdateManualGroup($key, $pdo, $Gname, $grpCategory, $user_name, $machines, $global, $userList, $type = '')
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $mcatid = GRP_GetMachineGrpCategory($key, $pdo, $grpCategory);

        $human = 1;
        $style = 2;
        $created = time();
        $event_query = 0;
        $event_span = 0;
        $asset_query = 0;
        // $manual = 'Manual';
        $type = '' ? 'Manual' : $type;

        $dsql1 = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = '$Gname'");
        $dsql1->execute();
        $dsql2 = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.GroupMappings  where groupname = '$Gname'");
        $dsql2->execute();

        $sql = "insert into " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,created,eventquery,eventspan,assetquery,boolstring,mgroupuniq,mcatuniq)"
            . "select '$Gname','$user_name',$global,$human,$style,$created,$event_query,$event_span,$asset_query,'$type',"
            . "md5(concat(mcatuniq,',','$Gname')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = ?";
        // $stmt = $pdo->prepare($sql);
        NanoDB::query($sql, [$mcatid]);

        $groupid = GRP_GetMgroupId($Gname, $user_name, $pdo);

        $insDefSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
        $insDefSql->execute([$groupid, $Gname, $user_name]);
        $userList = explode(',', $userList);
        if ($userList != '') {
            $user_in = str_repeat('?,', safe_count($userList) - 1) . '?';
            $userSql = $pdo->prepare("SELECT username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($user_in)");
            $userSql->execute($userList);
            $userRes = $userSql->fetchAll(PDO::FETCH_ASSOC);
            foreach ($userRes as $value) {
                $insSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username) VALUES (?, ?, ?)");
                $insSql->execute([$groupid, $Gname, $value['username']]);
            }
        }

        $sql = $pdo->prepare("delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap "
            . "left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq = " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) "
            . "left join " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq = " . $GLOBALS['PREFIX'] . "core.Census.censusuniq) where mgroupid = ?");
        $sql->execute([$groupid]);

        if (safe_count($machines) > 0) {

            foreach ($machines as $m) {
                $sql = "insert INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,censusuniq,censussiteuniq) "
                    . "SELECT mgroupuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, censusuniq, censussiteuniq FROM "
                    . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE id = ? AND mcatid = ? AND mgroupid = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$m, $mcatid, $groupid]);
            }
            $jsonreturn = array('status' => 'success');
        } else {

            $jsonreturn = array('status' => 'notupdated');
        }
    } else {
        echo "Your key has been expired";
    }
    return $jsonreturn;
}

function GRP_ManualGrupEdit($groupid,   $global, $userListArr)
{


    $pdo = pdo_connect();
    //   GRP_updateGroupGlobal($groupid, $pdo, $global);

    $logged_username = $_SESSION['user']['username'];
    $timenow = time();

    $userList = implode(',', array_values($userListArr));

    $q = "SELECT username FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid IN ($userList)";
    $userSql = $pdo->prepare($q);
    $userSql->execute();
    $userRes = $userSql->fetchAll();

    $q = "SELECT groupname FROM " . $GLOBALS['PREFIX'] . "core.GroupMappings WHERE groupid = " . $groupid . " AND groupname != '' LIMIT 0,1";
    $grpSql = $pdo->prepare($q);
    $grpSql->execute();
    $grpname = $grpSql->fetchColumn();

    $delSql = $pdo->prepare("delete FROM " . $GLOBALS['PREFIX'] . "core.GroupMappings WHERE groupid = ?");
    $delSql->execute([$groupid]);
    foreach ($userRes as $value) {
        $grpusername = $value['username'];
        $insSql = $pdo->prepare("insert INTO " . $GLOBALS['PREFIX'] . "core.GroupMappings (groupid, groupname, username, modifiedby, modifiedtime) "
            . "VALUES (?,?,?,?,?)");
        $insSql->execute([$groupid, $grpname, $grpusername, $logged_username, $timenow]);
    }

    echo json_encode(array('msg' => 'success'));
    exit;
}

function GRP_GetCustomerNumber($key, $pdo, $eid)
{
    $key = DASH_ValidateKey($key);
    $customerNumber = "";
    if ($key) {
        $sql = $pdo->prepare("select  eid, customerNo FROM " . $GLOBALS['PREFIX'] . "agent.channel WHERE eid = ? LIMIT 1");
        $sql->execute([$eid]);
        $res = $sql->fetch();
        if ($res['customerNo'] == NULL || $res['customerNo'] == 'NULL' || $res['customerNo'] == "NULL") {
            $customerNumber = $eid;
        } else {
            $customerNumber = $res['customerNo'];
        }
        return $customerNumber;
    } else {
        echo "Your key has been expired";
    }
}


function GRP_GetRealSiteName($key, $pdo, $siteName)
{
    $key = DASH_ValidateKey($key);
    $realSiteName = '';
    if ($key) {
        $eid = $_SESSION['user']['cId'];

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

function GRP_GetAdvancedGroupGridData($key, $pdo, $username, $category)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = $pdo->prepare("select MG.*, GM.modifiedby, GM.modifiedtime, count(MGM.mgmapid) as number from " . $GLOBALS['PREFIX'] . "core.MachineGroups MG LEFT JOIN " . $GLOBALS['PREFIX'] . "core.GroupMappings GM on MG.mgroupid = GM.groupid LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap MGM on MG.mgroupuniq = MGM.mgroupuniq LEFT JOIN MachineCategories MC ON MC.mcatid = ? where MG.style IN (3,4) and GM.username = ? GROUP BY MG.name order by MG.created desc");
        $sql->execute([$category, $username]);
        $sqlres = $sql->fetchAll();
        return $sqlres;
    } else {
        $msg = "Your key has been expired";
        print_data($msg);
    }
}

function GRP_GetGroupGridData($key, $pdo, $username, $orderStr, $limitStr, $whereSearch)
{
    if ($orderStr == '') {
        $orderStr = 'order by MG.created desc';
    }
    $db = $pdo;
    $sql = $db->prepare("select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where category = 'Wiz_SCOP_MC'");
    $sql->execute();
    $categoryres = $sql->fetch();
    $responseArr = array();
    $mcatid = $categoryres['mcatid'];

    if ($mcatid != '') {
        $stm = $pdo->prepare("select MG.*, GM.modifiedby, GM.modifiedtime, count(MGM.mgmapid) as number from " . $GLOBALS['PREFIX'] . "core.MachineGroups MG LEFT JOIN " . $GLOBALS['PREFIX'] . "core.GroupMappings GM on MG.mgroupid = GM.groupid LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap MGM on MG.mgroupuniq = MGM.mgroupuniq LEFT JOIN MachineCategories MC ON MC.mcatid = ? where GM.username = ? and MG.style != 1 $whereSearch GROUP BY MG.name $orderStr $limitStr");
        $stm->execute([$mcatid, $username]);
        $groupres = $stm->fetchAll(PDO::FETCH_ASSOC);

        $stm2 = $pdo->prepare("select MG.*, GM.modifiedby, GM.modifiedtime, count(MGM.mgmapid) as number from " . $GLOBALS['PREFIX'] . "core.MachineGroups MG LEFT JOIN " . $GLOBALS['PREFIX'] . "core.GroupMappings GM on MG.mgroupid = GM.groupid LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap MGM on MG.mgroupuniq = MGM.mgroupuniq LEFT JOIN MachineCategories MC ON MC.mcatid = ? where GM.username = ? and MG.style != 1 $whereSearch GROUP BY MG.name $orderStr");
        $stm2->execute([$mcatid, $username]);
        $totCount = safe_count($stm2->fetchAll(PDO::FETCH_ASSOC));

        $responseArr['data'] = $groupres;
        $responseArr['totCount'] = $totCount;
        return json_encode($responseArr);
    }
}

function GRP_fetchMachineList($str, $cond, $strVal, $sitelist)
{
    $string = $str . '.keyword';

    $site = explode(',', $sitelist);
    foreach ($site as $key => $value) {
        $sitefilter .= '{"term": {"site.keyword": "' . $value . '"}},';
    }
    $sitefilter = rtrim($sitefilter, ',');

    if ($cond == '1') {
        $params = '{
                    "_source": ["machine", "site"],
                    "query": {
                        "bool": {
                            "minimum_should_match": 1,
                            "should" : [
                                ' . $sitefilter . '
                            ],
                        "must": [
                                {
                                    "term": {
                                            "' . $string . '": "' . $strVal . '"

                                    }
                                }
                            ]
                      }
                    }
        }';
    } else if ($cond == '2') {
        $params = '';
    } else if ($cond == '3') {
        $params = '{
            "_source": ["machine", "site"],
               "query": {
                    "bool": {
                        "must": [
                            {
                                "wildcard": {
                                    "' . $string . '": {
                                        "value": "' . $strVal . '*"
                                    }
                                }
                            },
                            ' . $sitefilter . '
                        ]
                    }
               }
           }';
    }

    $tempRes = GRP_getELData('asset*', $params, '');
    $res = GRP_formatData($tempRes);
    return $res;
}

function GRP_getELData($index, $params)
{

    global $elastic_url;
    global $elastic_username;
    global $elastic_password;
    $url = $elastic_url . $index . "/_search?size=10000&pretty";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_USERPWD, "$elastic_username:$elastic_password");


    $headers = array();

    if (isset($requestHeaders) && is_array($requestHeaders) && safe_sizeof($requestHeaders) > 0) {
        $headers = $requestHeaders;
    } else {
        $headers[] = "Content-Type: application/json";
    }

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function GRP_formatData($tempRes)
{

    $dataArr = array();
    $jsonData = safe_json_decode($tempRes, true);
    $data = $jsonData['hits']['hits'];

    foreach ($data as $key => $val) {
        $site = $val['_source']['site'];
        $machine = $val['_source']['machine'];
        $dataArr[$machine] = $site;
    }
    return $dataArr;
}

function GRP_getcensusid($pdo, $machineData, $sitelist)
{

    $site = explode(',', $sitelist);
    $machineArr = array();
    foreach ($machineData as $k => $v) {
        array_push($machineArr, $k);
    }
    $in1  = str_repeat('?,', safe_count($site) - 1) . '?';
    $in2  = str_repeat('?,', safe_count($machineArr) - 1) . '?';
    $params = array_merge($site, $machineArr);
    $sql = $pdo->prepare("select id from " . $GLOBALS['PREFIX'] . "core.Census where host in (?) and site in (?) group by host order by id desc ");
    $sql->execute([$machine, $siteListData]);
    $sqlres = $sql->fetchAll();

    return $sqlres;
}
