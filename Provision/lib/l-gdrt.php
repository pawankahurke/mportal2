<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 8-Jul-04   EWB     Created.
13-Jul-04   EWB     Added push_mcat, build_mcat
 6-Aug-04   EWB     invalidate_gid
 9-Aug-04   EWB     invalidate_wid
11-Aug-04   EWB     invalidate_pcache
11-Aug-04   EWB     invalidate_hid
18-Aug-04   EWB     The server no longer attempts to invalidate the cache.
11-Nov-04   EWB     User machine groups.
19-Nov-04   EWB     Renamed User machine groups.
23-Nov-04   EWB     Clean up human groups after losing site access.
 7-Feb-05   EWB     mysql performance testing.
 9-Feb-05   EWB     rebuild_cat_machine uses temp table for speed.
21-Feb-05   EWB     log mgrp rebuild timings.
22-Feb-05   EWB     remove "orphaned" MachineGroupMap records.
23-Feb-05   EWB     Handle site migration.
12-Sep-05   BTE     Added checksum invalidation code.
14-Sep-05   BTE     Update the revision level when the precedence changes for
                    the table core.MachineCategories.
14-Sep-05   BJS     Fixed undefined $test & $sql syntax error.
20-Sep-05   BJS     Added gconfig. to kill_gid().
20-Sep-05   BTE     Added proper table specification to push_mcat.
12-Oct-05   BTE     Changed references from gconfig to core.
13-Oct-05   BTE     Added HEX() calls to support binary strings.
15-Oct-05   BTE     Added missing HEX() call to rebuild_cat_user.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
10-Nov-05   BTE     Some delete operations should not be permanent.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
27-Jan-06   BTE     Bug 3080: Compute mgroupuniq using UUID instead of mcatuniq
                    and name.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.  Removed check_census_dirty.
15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on
                    4.3 server.
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
                    correctly.
23-May-06   BTE     Bug 3360: Cannot remove a machine from an user-defined
                    group.
26-May-06   BTE     Bug 3293: Allow name changes for groups.
19-Jun-06   BTE     Bug 3467: groups_init has broken mysql query.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
07-Aug-06   BTE     Bug 3574: PHP warnings during expunge.
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.

*/

define('constUserName', 'User:');

/* Definitions for groups_init */
define('constGroupsInitFull',           0);
define('constGroupsInitBuildOnly',      1);


function distinct($set, $field)
{
    $list = array();
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $list[] = $row[$field];
        }
    }
    return $list;
}


/*
    |  Just checking for existance, we don't care who the
    |  owner is.
    */

function mgrp_exists($name, $id, $db)
{
    $qn  = safe_addslashes($name);
    $sql = "select mgroupid\n"
        . " from " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " where name = '$qn'\n"
        . " and mgroupid != $id";
    $row = find_one($sql, $db);
    return ($row) ? true : false;
}


function mcat_exists($name, $id, $db)
{
    $qn  = safe_addslashes($name);
    $sql = "select mcatid\n"
        . " from " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
        . " where category = '$qn'\n"
        . " and mcatid != $id";
    $row = find_one($sql, $db);
    return ($row) ? true : false;
}

function delete_expr_gid($gid, $db)
{
    $num = 0;
    if ($gid > 0) {
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.GroupExpression\n"
            . " where item = $gid\n"
            . " or mgroupid = $gid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}

function delete_mgrp_gid($gid, $db)
{
    $num = 0;
    if ($gid > 0) {
        $test = global_def('test_sql', 0);
        if (!($test)) {
            $sql = "SELECT mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE "
                . "mgroupid=$gid";
            $row = find_one($sql, $db);
            if ($row) {
                $err =  VARS_HandleDeletedGroup(
                    CUR,
                    NULL,
                    $row['mgroupuniq']
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "delete_mgrp_gid: "
                        . "PHP_VARS_HandleDeletedGroup returned $err", 0);
                    return 0;
                }
            }

            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$gid,
                "mgroupid",
                constDataSetCoreMachineGroups,
                constOperationPermanentDelete
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "delete_mgrp_gid: PHP_DSYN_InvalidateRow "
                    . "returned " . $err, 0);
                return 0;
            }
        }
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
            . " where mgroupid = $gid";

        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpInclude);
        PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpExclude);
    }
    return $num;
}

function unique_mgrp($text, $id, $db)
{
    $uniq = 0;
    $name = $text;
    if (mgrp_exists($name, $id, $db)) {
        do {
            $uniq++;
            $xxx  = sprintf('%03d', $uniq);
            $name = "$text $xxx";
        } while (mgrp_exists($name, $id, $db));
    }
    return $name;
}

function unique_mcat($text, $id, $db)
{
    $uniq = 0;
    $name = $text;
    if (mcat_exists($name, $id, $db)) {
        do {
            $uniq++;
            $xxx  = sprintf('%03d', $uniq);
            $name = "$text $xxx";
        } while (mcat_exists($name, $id, $db));
    }
    return $name;
}



function insert_mgrp(
    $tid,
    $name,
    $user,
    $glob,
    $hman,
    $style,
    $sid,
    $secs,
    $qid,
    $desc,
    $uuid,
    $db
) {
    $gid = 0;
    $now = time();
    $qn  = safe_addslashes($name);
    $qu  = safe_addslashes($user);
    $qd  = safe_addslashes($desc);
    if ($uuid) {
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,"
            . "created,eventquery,eventspan,assetquery,boolstring,"
            . "mgroupuniq,mcatuniq) select '$qn','$qu',$glob,$hman,"
            . "$style,$now,$sid,$secs,$qid,'$qd',md5('$uuid'),mcatuniq "
            . "from " . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid = $tid\n";
    } else {
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,username,global,human,style,"
            . "created,eventquery,eventspan,assetquery,boolstring,"
            . "mgroupuniq,mcatuniq) select '$qn','$qu',$glob,$hman,"
            . "$style,$now,$sid,$secs,$qid,'$qd',md5(concat(mcatuniq,',',"
            . "'$qn')),mcatuniq from " . $GLOBALS['PREFIX'] . "core.MachineCategories where "
            . "mcatid = $tid\n";
    }

    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $gid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $test = global_def('test_sql', 0);
        if (!($test)) {
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$gid,
                "mgroupid",
                constDataSetCoreMachineGroups,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "insert_mgrp: PHP_DSYN_InvalidateRow "
                    . "returned " . $err, 0);
            }
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpInclude);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpExclude);
        }
        $stat = "s:$style,g:$glob,h:$hman,t:$tid,g:$gid,u:$user";
        $acts = 'create';
    } else {
        $stat = "s:$style,g:$glob,h:$hman,t:$tid,u:$user";
        $acts = 'create failed';
    }
    $text = "groups: mgrp $acts ($stat) $name";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
    return $gid;
}


/*
    |  we've modified a wcfg record ...
    |
    |  we want to update the timestamp of
    |  the affected machines.
    */

// function modify_wid($wid,$db)
// {
//     $num = 0;
//     if ($wid > 0)
//     {
//         $now = time();
//         $sql = "update softinst.Machine set\n"
//              . " lastchange = $now\n"
//              . " where wuconfigid = $wid";
//         $res = redcommand($sql,$db);
//         $num = affected($res,$db);
//         debug_note("$num machine cache updated");
//     }
//     return $num;
// }


/*
    |  we want to invalidate a particular wcfg record.
    |
    |  This is appropriate when a wcfg record is removed, either
    |  by itself, or along with it's associated machine group.
    */

// function invalidate_wid($wid,$db)
// {
//     $num = 0;
//     if ($wid > 0)
//     {
//         $now = time();
//         $sql = "update softinst.Machine set\n"
//              . " wuconfigid = 0,\n"
//              . " lastchange = $now,\n"
//              . " lastconfigid = $wid\n"
//              . " where wuconfigid = $wid";
//         $res = redcommand($sql,$db);
//         $num = affected($res,$db);
//         debug_note("$num machine cache affected");
//     }
//     return $num;
// }


/*
    |  This invalidates all the wcfg cache records.  At the
    |  moment I'm calling this whenever a machine catagories
    |  get reordered, or whenever a new wcfg record is created.
    |
    |  Really only the lower-priority machines need to be
    |  recalculated, but this will do for now.
    */

function invalidate_wcache($db)
{
    $now = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "softinst.Machine set\n"
        . " lastconfigid = wuconfigid,\n"
        . " wuconfigid = 0,\n"
        . " lastchange = $now\n"
        . " where wuconfigid != 0";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function invalidate_pcache($db)
{
    $now = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "softinst.PatchStatus set\n"
        . " lastconfigid = patchconfigid,\n"
        . " patchconfigid = 0,\n"
        . " lastchange = $now\n"
        . " where patchconfigid != 0";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function kill_gid($gid, $db)
{
    if ($gid) {
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.WUConfig\n"
            . " where mgroupid = $gid";
        $res = redcommand($sql, $db);
        $wid = affected($res, $db);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchConfig\n"
            . " where mgroupid = $gid";
        $res = redcommand($sql, $db);
        $pid = affected($res, $db);
        $vid = 0;
        $mid = 0;
        $sid = 0;

        $sql = "select valueid from " . $GLOBALS['PREFIX'] . "core.VarValues left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigVarValues,
            "valueid",
            "kill_gid",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "select semid from " . $GLOBALS['PREFIX'] . "core.SemClears left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.SemClears.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
        $set2 = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigSemClears,
            "semid",
            "kill_gid",
            1,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "select setid from " . $GLOBALS['PREFIX'] . "core.GroupSettings left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.GroupSettings.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
        $set3 = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigGroupSettings,
            "setid",
            "kill_gid",
            2,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues using " . $GLOBALS['PREFIX'] . "core.VarValues left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = $gid";
        if ($set) {
            $res = redcommand($sql, $db);
            $vid = affected($res, $db);
        }

        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.GroupSettings using " . $GLOBALS['PREFIX'] . "core.GroupSettings left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.GroupSettings.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = $gid";
        if ($set2) {
            $res = redcommand($sql, $db);
            $mid = affected($res, $db);
        }

        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.SemClears using " . $GLOBALS['PREFIX'] . "core.SemClears left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.SemClears.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = $gid";
        if ($set3) {
            $res = redcommand($sql, $db);
            $sid = affected($res, $db);
        }

        debug_note("removed (w:$wid,p:$pid,v:$vid,m:$mid,s:$sid)");
    }
}


/*
    |  This removes all the map records after
    |  the corresponding census record has vanished.
    |
    |  Then it removes all the builtin groups which
    |  have no members.
    */

function rebuild_kill($db)
{
    $sql = "select M.mgmapid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " on M.censusuniq = C.censusuniq\n"
        . " where C.id is NULL\n"
        . " group by M.mgmapid";
    $set = NanoDB::findManyCached($sql);
    if ($set) {
        $tmp = distinct($set, 'mgmapid');
        $num = safe_count($tmp);
        $txt = join(',', $tmp);

        $sql = "select mgmapid, mgroupuniq, censusuniq from "
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap where mgmapid in ($txt)";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            foreach ($set as $key => $row) {
                $err = PHP_VARS_HandleDeletedGroup(
                    CUR,
                    $row['censusuniq'],
                    $row['mgroupuniq']
                );
                if ($err != constAppNoErr) {
                    logs::log(
                        __FILE__,
                        __LINE__,
                        "rebuild_kill: "
                            . "PHP_VARS_HandleDeletedGroup returned $err",
                        0
                    );
                    return;
                }
            }
        }
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreMachineGroupMap,
            "mgmapid",
            "rebuild_kill",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
            . " where mgmapid in ($txt)";
        $del = 0;
        if ($set) {
            $res = redcommand($sql, $db);
            $del = affected($res, $db);
        }
        debug_note("$num ($txt) not in census, $del removed.");
    } else {
        debug_note('No extra group members found.');
    }

    $sql = "select M.mgroupuniq from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " on M.mgroupuniq = G.mgroupuniq\n"
        . " where G.mgroupid is NULL\n"
        . " group by M.mgroupuniq";
    $set = NanoDB::findManyCached($sql);
    if ($set) {
        $tmp = distinct($set, 'mgroupuniq');
        $num = safe_count($tmp);
        $txt = join(',', $tmp);

        $sql = "select mgmapid from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap where mgroupuniq "
            . "in ($txt)";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreMachineGroupMap,
            "mgmapid",
            "rebuild_kill",
            1,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap"
            . " where mgroupuniq in ($txt)";
        if ($set) {
            $res = redcommand($sql, $db);
            $del = affected($res, $db);
            debug_note("$num ($txt) orphaned members, $del removed.");
        }
    } else {
        debug_note('No orphaned group members found.');
    }

    $tmp = constStyleBuiltin;
    $sql = "select G.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
        . " on M.mgroupuniq = G.mgroupuniq\n"
        . " where G.style = $tmp\n"
        . " and M.mgmapid is NULL";
    $set = NanoDB::findManyCached($sql);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $gid  = $row['mgroupid'];
            $name = $row['name'];
            $num  = delete_mgrp_gid($gid, $db);
            if ($num) {
                delete_expr_gid($gid, $db);
                kill_gid($gid, $db);
                $text = "groups: remove empty (g:$gid) $name";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);
            }
        }
    } else {
        debug_note("Every builtin group has at least one member.");
    }
}



function create_builtin_group($tid, $name, $uuid, $db)
{
    $gid = 0;
    $grp = find_mgrp_name($name, $db);
    if ($grp) {
        $gtid = $grp['mcatid'];
        $ggid = $grp['mgroupid'];
        $gtyp = $grp['style'];
        if (($gtid == $tid) && ($gtyp == constStyleBuiltin)) {
            $gid = $ggid;
        } else {
            $txt = unique_mgrp($name, 0, $db);
            $qn  = safe_addslashes($txt);

            $test = global_def('test_sql', 0);
            $err = constAppNoErr;
            if (!($test)) {
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$ggid,
                    "mgroupid",
                    constDataSetCoreMachineGroups,
                    constOperationDelete
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "create_builtin_group: "
                        . "PHP_DSYN_InvalidateRow returned " . $err, 0);
                }
            }

            if ($err == constAppNoErr) {
                $sql = "update " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
                    . " set name = '$qn',\n"
                    . " revlname = revlname + 1\n"
                    . " where mgroupid = $ggid";
                $res = redcommand($sql, $db);

                if (!($test)) {
                    DSYN_UpdateDependencies(
                        constDataSetCoreMachineGroups,
                        $ggid,
                        $db
                    );
                    PHP_REPF_UpdateDynamicList(
                        CUR,
                        constJavaListEventMgrpInclude
                    );
                    PHP_REPF_UpdateDynamicList(
                        CUR,
                        constJavaListEventMgrpExclude
                    );
                }
                $msg = "groups: name for group '$name' changed to '$txt'";
                logs::log(__FILE__, __LINE__, $msg, 0);
            }
        }
    }
    if ($gid == 0) {
        $hmn = 0;
        $gbl = 1;
        $typ = constStyleBuiltin;
        $txt = 'Built-In';
        $gid = insert_mgrp(
            $tid,
            $name,
            '',
            $gbl,
            $hmn,
            $typ,
            0,
            0,
            0,
            $txt,
            $uuid,
            $db
        );
    }
    return $gid;
}


function build_host($tid, $gid, $xid, $db)
{
    $hid = 0;
    $sql = CORE_CreateMachineGroupMap($xid, $tid, $gid);
    $res = redcommand($sql, $db);
    if (affected($res, $db) == 1) {
        $hid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $test = global_def('test_sql', 0);
        if (!($test)) {
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$hid,
                "mgmapid",
                constDataSetCoreMachineGroupMap,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "build_host: PHP_DSYN_InvalidateRow "
                    . " returned " . $err, 0);
            }
        }
    }
    return $hid;
}


function kill_host($gid, $hid, $db)
{
    $num = 0;
    if (($gid) && ($hid)) {

        $sql = "select mgmapid, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq, "
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap "
            . "left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join " . $GLOBALS['PREFIX'] . "core.Census on ("
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq) "
            . "where mgroupid = $gid and id = $hid";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            foreach ($set as $key => $row) {
                $err = PHP_VARS_HandleDeletedGroup(
                    CUR,
                    $row['censusuniq'],
                    $row['mgroupuniq']
                );
                if ($err != constAppNoErr) {
                    logs::log(
                        __FILE__,
                        __LINE__,
                        "kill_host: "
                            . "PHP_VARS_HandleDeletedGroup returned $err",
                        0
                    );
                    return;
                }
            }
        }
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreMachineGroupMap,
            "mgmapid",
            "kill_host",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
            . " using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join " . $GLOBALS['PREFIX'] . "core.Census on ("
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq) "
            . "where mgroupid = $gid and id = $hid";
        if ($set) {
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
        }
    }
    return $num;
}


/*
    |  Gang assign site to group, this is much
    |  faster than one at a time.  We want insert
    |  ignore in case some of the machines are
    |  already members ... the unique index will
    |  prevent duplicates.
    */

function mgrp_add_site($site, $tid, $gid, $db)
{
    $num = 0;
    if (($site) && ($gid) && ($tid)) {
        $qs  = safe_addslashes($site);
        $sql = "select id from " . $GLOBALS['PREFIX'] . "core.Census where site='$qs'";
        $set = NanoDB::findManyCached($sql);
        $num = 0;
        if ($set) {
            foreach ($set as $key => $row) {
                $sql = CORE_CreateMachineGroupMap($row['id'], $tid, $gid);
                $res = redcommand($sql, $db);
                $num += affected($res, $db);
                $test = global_def('test_sql', 0);
                if ((!($test)) && ($num)) {
                    $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                    $err = PHP_DSYN_InvalidateRow(
                        CUR,
                        (int)$lastid,
                        "mgmapid",
                        constDataSetCoreMachineGroupMap,
                        constOperationInsert
                    );
                    if ($err != constAppNoErr) {
                        logs::log(
                            __FILE__,
                            __LINE__,
                            "mgrp_add_site: "
                                . "PHP_DSYN_InvalidateRow returned " . $err,
                            0
                        );
                    }
                }
            }
        }
        if ($num) {
            $stat = "t:$tid,g:$gid,n:$num";
            $text = "groups: add site ($stat) $site";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    return $num;
}


function find_mgrp_hids($gid, $db)
{
    $set = array();
    if ($gid > 0) {
        $sql = "select distinct id as censusid\n"
            . " from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq"
            . "=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq) left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on ("
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups."
            . "mgroupuniq)"
            . " where mgroupid = $gid\n"
            . " order by censusid";
        $tmp = NanoDB::findManyCached($sql);
        $set = distinct($tmp, 'censusid');
    }
    return $set;
}


function push_mcat($min, $db)
{
    $num = 0;
    if ($min > 0) {
        $sql = "select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineCategories where precedence"
            . " >=" . $min;
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreMachineCategories,
            "mcatid",
            "push_mcat",
            0,
            1,
            constOperationDelete,
            $db
        );

        $sql = "update " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
            . " set precedence = precedence+1,\n"
            . " revl = revl+1\n"
            . " where precedence >= $min";
        if ($set) {
            $res = redcommand($sql, $db);
            $num = affected($res, $db);

            DSYN_UpdateSet(
                $set,
                constDataSetCoreMachineCategories,
                "mcatid",
                $db
            );
        }
    }
    return $num;
}

function build_mcat($name, $precedence, $db)
{
    $num = 0;
    if (($name) && ($precedence > 0)) {
        $qn  = safe_addslashes($name);
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories set\n"
            . " category = '$qn',\n"
            . " mcatuniq = md5('$qn'),\n"
            . " precedence = $precedence";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        if ($num) {
            $tid  = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$tid,
                "mcatid",
                constDataSetCoreMachineCategories,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "build_mcat: PHP_DSYN_InvalidateRow returned "
                    . $err, 0);
            }
            $stat = "t:$tid,p:$precedence";
            $acts = 'create';
        } else {
            $stat = "p:$precedence";
            $acts = 'create failed';
        }
        $text = "groups: mcat $acts ($stat) $name";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $num;
}


function rebuild_cat_all($db)
{
    $all = constCatAll;
    $cat = find_mcat_name($all, $db);
    if (!$cat) {
        push_mcat(1, $db);
        build_mcat($all, 1, $db);
        $cat = find_mcat_name($all, $db);
    }
    $grp = find_mgrp_name($all, $db);
    if (($cat) && (!$grp)) {
        $tid = $cat['mcatid'];
        $gid = create_builtin_group($tid, $all, '', $db);
        $grp = find_mgrp_name($all, $db);
    }

    if ($grp) {
        $gid = $grp['mgroupid'];
        $tid = $grp['mcatid'];
        $sql = "select id, host, site\n"
            . " from (" . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G)\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
            . " on M.censusuniq = C.censusuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " where M.mgmapid is NULL\n"
            . " and G.mgroupid = $gid";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            reset($set);
            foreach ($set as $key => $row) {
                $xid = $row['id'];
                $hid = build_host($tid, $gid, $xid, $db);
                if ($hid) {
                    $host = $row['host'];
                    $site = $row['site'];
                    $stat = "t:$tid,g:$gid";
                    $text = "groups: add machine ($stat) $host at $site to $all";
                    logs::log(__FILE__, __LINE__, $text, 0);
                    debug_note($text);
                }
            }
        } else {
            debug_note("No machines are missing from group $all.");
        }
    }
}


/*
    |  Previously, we had the user groups just have the same name
    |  as the user they were representing.  This is convenient and
    |  it makes the SQL a bit simpler.
    |
    |  We also do the same thing for sites.
    |
    |  We discovered the problem with this scheme last night when we
    |  did an install at umassmed ...
    |
    |  They have both a site named UMassMed and a user named umassmed.
    |
    |  Live and learn.
    */

function mgrp_user($name)
{
    return constUserName . $name;
}


/*
    |  Take care of creating the Users category
    |  and populating it with groups and machines.
    |
    |  1. Add in all the new machines that each
    |     user has access to.
    |  2. Remove any old machines that the user
    |     has lost access to.
    */

function rebuild_cat_user($db)
{
    $user = constCatUser;
    $cat  = find_mcat_name($user, $db);
    if (!$cat) {
        push_mcat(2, $db);
        build_mcat($user, 2, $db);
        $cat = find_mcat_name($user, $db);
    }

    if ($cat) {
        $usr = safe_addslashes(constUserName);
        $tid = $cat['mcatid'];
        $sql = "select distinct U.username from\n"
            . " (" . $GLOBALS['PREFIX'] . "core.Users as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as A)\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroups as M\n"
            . " on M.mcatuniq = A.mcatuniq\n"
            . " and HEX(M.name) = HEX(concat('$usr',X.username))\n"
            . " where HEX(C.site) = HEX(X.customer)\n"
            . " and HEX(U.username) = HEX(X.username)\n"
            . " and A.mcatid = $tid\n"
            . " and M.mgroupuniq is NULL";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            reset($set);
            foreach ($set as $key => $row) {
                $user = $row['username'];
                $name = mgrp_user($user);
                debug_note("User '$user' name '$name' is missing.");
                create_builtin_group($tid, $name, '', $db);
            }
        } else {
            debug_note('All the users have groups.');
        }

        /*
            |  Some new machines could have shown up, or
            |  a user may have changed his site access.
            |  Either way, this will add the new machines.
            */

        /*$sql = "select C.id, C.host, C.site,\n"
                 . " G.name, G.mgroupid as gid from\n"
                 . " (".$GLOBALS['PREFIX']."core.Census as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as A)\n"
                 . " left join ".$GLOBALS['PREFIX']."core.MachineGroupMap as M\n"
                 . " on M.mgroupuniq = G.mgroupuniq\n"
                 . " and C.censusuniq = M.censusuniq\n"
                 . " where A.mcatid = $tid\n"
                 . " and G.name = concat('$usr',X.username)\n"
                 . " and X.customer = C.site\n"
                 . " and M.mgmapid is NULL";*/
        $sql = "";
        $set = NanoDB::findManyCached($sql);
        reset($set);
        foreach ($set as $key => $row) {
            $id   = $row['id'];
            $gid  = $row['gid'];
            $hid = build_host($tid, $gid, $id, $db);
            if ($hid) {
                $host = $row['host'];
                $site = $row['site'];
                $name = $row['name'];
                $stat = "t:$tid,g:$gid";
                $text = "groups: add machine ($stat) $host at $site to $name";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);
            }
        }

        /*
            |  A user may have been removed, or perhaps his
            |  site access has changed.  Either way, this
            |  will hunt down and remove those machines
            |  he no longer has access to.
            */

        $id  = array();
        $sql = "select M.mgmapid, C.host, C.site, G.name from\n"
            . " (" . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as A)\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.Customers as X\n"
            . " on X.customer = C.site\n"
            . " and G.name = concat('$usr',X.username)\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and M.mcatuniq = A.mcatuniq\n"
            . " and A.mcatid = $tid\n"
            . " and C.censusuniq = M.censusuniq\n"
            . " and X.id is NULL";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            reset($set);
            foreach ($set as $key => $row) {
                $id[] = $row['mgmapid'];
                $host = $row['host'];
                $site = $row['site'];
                $user = $row['name'];
                $text = "Group $user loses $host at $site";
                debug_note($text);
            }
            $sql = "select M.mgmapid, C.host, C.site, G.name from\n"
                . " (" . $GLOBALS['PREFIX'] . "core.Census as C,\n"
                . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
                . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M)\n"
                . " left join " . $GLOBALS['PREFIX'] . "core.Customers as X\n"
                . " on X.customer = C.site\n"
                . " and G.username = X.username\n"
                . " where M.mgroupuniq = G.mgroupuniq\n"
                . " and G.human = 1\n"
                . " and C.censusuniq = M.censusuniq\n"
                . " and X.id is NULL";
            $set = NanoDB::findManyCached($sql);
            reset($set);
            foreach ($set as $key => $row) {
                $id[] = $row['mgmapid'];
                $host = $row['host'];
                $site = $row['site'];
                $name = $row['name'];
                $text = "Group $name loses $host at $site";
                debug_note($text);
            }
        } else {
            debug_note('No users have lost site access.');
        }
        if ($id) {
            $txt = join(',', $id);

            $sql = "select mgmapid, censusuniq, mgroupuniq from "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap where mgmapid in ($txt)";
            $set = NanoDB::findManyCached($sql);
            if ($set) {
                foreach ($set as $key => $row) {
                    $err = PHP_VARS_HandleDeletedGroup(
                        CUR,
                        $row['censusuniq'],
                        $row['mgroupuniq']
                    );
                    if ($err != constAppNoErr) {
                        logs::log(
                            __FILE__,
                            __LINE__,
                            "rebuild_cat_user: "
                                . "PHP_VARS_HandleDeletedGroup returned $err",
                            0
                        );
                        return;
                    }
                }
            }

            $set = DSYN_DeleteSet(
                $sql,
                constDataSetCoreMachineGroupMap,
                "mgmapid",
                "rebuild_cat_user",
                0,
                1,
                constOperationPermanentDelete,
                $db
            );

            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
                . " where mgmapid in ($txt)";
            if ($set) {
                $res = redcommand($sql, $db);
                $num = affected($res, $db);
                debug_note("$num machines removed.");
            }
        }
    }
}


function rebuild_cat_site($db)
{
    $site = constCatSite;
    $cat  = find_mcat_name($site, $db);
    if (!$cat) {
        push_mcat(3, $db);
        build_mcat($site, 3, $db);
        $cat = find_mcat_name($site, $db);
    }

    if ($cat) {
        $tid = $cat['mcatid'];
        $sql = "select distinct site\n"
            . " from (" . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as A)\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroups as M\n"
            . " on M.mcatuniq = A.mcatuniq\n"
            . " and M.name = C.site\n"
            . " where A.mcatid = $tid\n"
            . " and M.mgroupuniq is NULL";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            reset($set);
            foreach ($set as $key => $row) {
                $site = $row['site'];
                debug_note("Site '$site' is missing.");
                create_builtin_group($tid, $site, '', $db);
            }
        } else {
            debug_note('All the sites have groups.');
        }

        /*
            |  A machine may have moved from one site to
            |  another.  This takes care of removing the
            |  record from the old site.
            */

        $sql = "select C.id as censusid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as A\n"
            . " where M.mcatuniq = A.mcatuniq\n"
            . " and A.mcatid = $tid\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.name != C.site\n"
            . " group by censusid";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            $num = safe_count($set);
            $tmp = distinct($set, 'censusid');
            $txt = join(',', $tmp);

            $sql = "select mgmapid, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq, "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq from "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join "
                . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
                . $GLOBALS['PREFIX'] . "core.Census.censusuniq) left join "
                . $GLOBALS['PREFIX'] . "core.MachineCategories on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap."
                . "mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq) where "
                . "id in ($txt) and mcatid = $tid";
            $set = NanoDB::findManyCached($sql);
            if ($set) {
                foreach ($set as $key => $row) {
                    $err = PHP_VARS_HandleDeletedGroup(
                        CUR,
                        $row['censusuniq'],
                        $row['mgroupuniq']
                    );
                    if ($err != constAppNoErr) {
                        logs::log(
                            __FILE__,
                            __LINE__,
                            "rebuild_cat_site: "
                                . "PHP_VARS_HandleDeletedGroup returned $err",
                            0
                        );
                        return;
                    }
                }
            }
            $set = DSYN_DeleteSet(
                $sql,
                constDataSetCoreMachineGroupMap,
                "mgmapid",
                "rebuild_cat_site",
                0,
                1,
                constOperationPermanentDelete,
                $db
            );

            $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
                . " using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join "
                . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
                . $GLOBALS['PREFIX'] . "core.Census.censusuniq) left join "
                . $GLOBALS['PREFIX'] . "core.MachineCategories on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap."
                . "mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq)"
                . " where id in ($txt)\n"
                . " and mcatid = $tid";
            if ($set) {
                $res = redcommand($sql, $db);
                $del = affected($res, $db);

                $stat = "n:$num,d:$del";
                $text = "groups: site migration ($stat) $txt";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);
            }
        } else {
            debug_note('No site migrations found.');
        }

        /*
            |  Add new machines to their site group.  If a
            |  machine has changed sites, it should also
            |  be added here.
            */

        $sql = "select C.*\n"
            . " from (" . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as G)\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
            . " on M.mcatuniq = G.mcatuniq\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " where M.mgmapid is NULL\n"
            . " and G.mcatid = $tid";
        $set = NanoDB::findManyCached($sql);
        if ($set) {
            $gids = array();
            reset($set);
            foreach ($set as $key => $row) {
                $site = $row['site'];
                $host = $row['host'];
                $id   = $row['id'];
                $gid  = @intval($gids[$site]);

                if (!$gid) {
                    $grp = find_mgrp_name($site, $db);
                    if ($grp) {
                        $gid = $grp['mgroupid'];
                        $gids[$site] = $gid;
                    }
                }
                if ($gid) {
                    $hid = build_host($tid, $gid, $id, $db);
                    if ($hid) {
                        $host = $row['host'];
                        $site = $row['site'];
                        $stat = "t:$tid,g:$gid,m:$id";
                        $text = "groups: add machine ($stat) $host to $site";
                        logs::log(__FILE__, __LINE__, $text, 0);
                        debug_note($text);
                    }
                }
            }
        } else {
            debug_note('All the sites have all their members.');
        }
    }
}


function make_map_table($db)
{
    $sql = 'create temporary table MT(censusid int(11))';
    return redcommand($sql, $db);
}

function drop_map_table($db)
{
    $sql = 'drop table MT';
    return redcommand($sql, $db);
}


/*
    |  This takes care of adding new machines to the Machines
    |  category ... and of course the hard part is figuring
    |  out which are new.  The query to do this runs in
    |  milliseconds on a small server but it was taking
    |  about 40 seconds on cbe, with 2059 machines in the
    |  census and 15267 map records.  So we figure using
    |  the temp table will help for big servers.
    |
    |  Note that we do *NOT* attempt to deal with the
    |  problem of machine migration here ... instead
    |  we'll allow find_host_mgrp() to fix them as
    |  needed.
    */

function rebuild_cat_machine($db)
{
    $tmp = constCatMachine;
    $cat = find_mcat_name($tmp, $db);
    if (!$cat) {
        push_mcat(4, $db);
        build_mcat($tmp, 4, $db);
        $cat = find_mcat_name($tmp, $db);
    }

    if ($cat) {
        $set = array();
        $tid = $cat['mcatid'];
        $res = make_map_table($db);
        if (!$res) {
            drop_map_table($db);
            $res = make_map_table($db);
        }

        if ($res) {
            $sql = "insert into MT (censusid)\n"
                . " select id from\n"
                . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.Census on ("
                . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census."
                . " censusuniq) left join " . $GLOBALS['PREFIX'] . "core.MachineCategories on ("
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories."
                . "mcatuniq) where mcatid = $tid";
            $res = redcommand($sql, $db);
            if ($res) {
                /*$sql = "select C.* from\n"
                         . " ".$GLOBALS['PREFIX']."core.Census as C\n"
                         . " left join MT as M\n"
                         . " on C.id = M.censusid\n"
                         . " where M.censusid is NULL"; */
                $sql = "";
                $set = NanoDB::findManyCached($sql);
                drop_map_table($db);
            }
        } else {
            $sql = "select C.* from (" . $GLOBALS['PREFIX'] . "core.Census as C,\n"
                . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as A)\n"
                . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
                . " on C.censusuniq = M.censusuniq\n"
                . " and M.mcatuniq = A.mcatuniq\n"
                . " where M.mgmapid is NULL\n"
                . " and A.mcatid = $tid";
            $set = NanoDB::findManyCached($sql);
        }
        if ($set) {
            reset($set);
            foreach ($set as $key => $row) {
                $site = $row['site'];
                $host = $row['host'];
                $id   = $row['id'];
                $uuid = $row['uuid'];
                $name = "$site:$host";
                $gid  = 0;
                $hid  = 0;
                $grp  = find_mgrp_name($name, $db);
                if ($grp) {
                    $gid = $grp['mgroupid'];
                }
                if (!$grp) {
                    $gid = create_builtin_group($tid, $name, $uuid, $db);
                    debug_note("Created new group '$name' ($gid).");
                }
                if ($gid) {
                    $hid = build_host($tid, $gid, $id, $db);
                }
                debug_note("New machine '$host' at '$site' gid:$gid, tid:$tid, hid:$hid.");
            }
        } else {
            debug_note('All the machine groups exist.');
        }
    }
}


function show_timer($min, $max, $txt)
{
    $msec = microtime_diff($min, $max);
    $secs = microtime_show($msec);
    $text = "time: $txt ($secs)";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
}


/*
    |  Note we want kill after user.
    |
    |  When a user gets deleted, or loses access to sites,
    |  the user process can cause his group can become empty,
    |  and rebuild_kill will remove it.
    */

/* groups_init

        Initializes the groups for the server.  Requires the following
        libraries:
            lib/l-gdrt.php
            lib/l-grps.php
            lib/l-dsyn.php
            lib/l-db.php
            lib/l-head.php
            lib/l-gsql.php
            lib/l-rcmd.php OR rpc/server.php
            lib/l-core.php
    */
function groups_init($db, $operation)
{
    $t0 = microtime();
    rebuild_cat_all($db);
    $t1 = microtime();
    rebuild_cat_user($db);
    $t2 = microtime();
    rebuild_cat_site($db);
    $t3 = microtime();
    rebuild_cat_machine($db);
    switch ($operation) {
        case constGroupsInitFull:
            /* Cleanup the tables */
            $t4 = microtime();
            rebuild_kill($db);
            break;
        case constGroupsInitBuildOnly:
            /* We're only building, no cleanup necessary */
            $t4 = microtime();
            break;
        default:
            logs::log(__FILE__, __LINE__, "groups_init: invalid operation $operation", 0);
            break;
    }

    $t5 = microtime();
    show_timer($t0, $t1, 'Category "All"');
    show_timer($t1, $t2, 'Category "User"');
    show_timer($t2, $t3, 'Category "Site"');
    show_timer($t3, $t4, 'Category "Machines"');
    show_timer($t4, $t5, 'Rebuild Kill');
    show_timer($t0, $t5, 'Mgroup total');
}
