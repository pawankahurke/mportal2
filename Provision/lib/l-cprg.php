<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 3-Apr-03   EWB     Created
13-Jun-03   EWB     Delete site commands.
31-Oct-03   EWB     delete from Machine *before* AssetData
12-Feb-04   EWB     purge provision records.
18-Feb-04   EWB     purge_event_site removes console, filters.
18-Feb-04   EWB     purge_asset_site removes asset filters.
19-Feb-04   EWB     purge_crypt_site, purge_crypt_host
 1-Mar-04   EWB     Never remove from the middle of the Events table.
                    purge_event_host removed.
11-Mar-04   EWB     purge_event_host reinstated, just flags as deleted.
 2-Apr-04   EWB     purge_update_site does not touch Downloads table.
21-Apr-04   EWB     purge_config_host removes LocalCache record.
21-Apr-04   EWB     purge_config_site removes GlobalCache record.
15-Jun-04   EWB     Migration of "affected()"
14-Jul-04   EWB     purge from softinst and machine groups.
11-May-05   EWB     purge from new gconfig database.
12-May-05   EWB     purge site removes invalid site variables.
27-Jun-05   AAM     Changed purge_event_site and purge_event_host to not do
                    full-table operations on Events table that kill the server
                    until we can get this fixed.
27-Jul-05   EWB     purge group removes semclears
 1-Sep-05   BJS     Replace event.Events.deleted with core.Census.deleted.
12-Sep-05   BTE     Added checksum invalidation code.
20-Sep-05   BTE     Added proper table specification in kill_config_mgrp and
                    kill_config_host.
12-Oct-05   BTE     Changed references from gconfig to core.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
09-Nov-05   BJS     Removed event.RptSiteFilters & notifications.NotSiteFilters.
10-Nov-05   BTE     Some delete operations should not be permanent.
14-Dec-05   BJS     find_host_mgrp -> GCFG_find_host_mgrp().
16-Dec-05   BJS     find_site_mgrp -> GCFG_find_site_mgrp(), removed
                    ReportSiteFilters reference.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.
23-May-06   BTE     Bug 3360: Cannot remove a machine from an user-defined
                    group.
06-Jul-06   BTE     Bug 3406: Optimize server expunge.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.

*/

function purge_record($sql, $db)
{
    $res = redcommand($sql, $db);
    return affected($res, $db);
}

function purge_asset_host($site, $host, $db)
{
    $num = 0;
    if (mysqli_select_db($db, asset)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "select * from Machine\n"
            . " where host = '$qh' and\n"
            . " cust = '$qs'";
        $row = find_one($sql, $db);
        if ($row) {
            $mid = $row['machineid'];
            if ($mid > 0) {
                $d = "delete from";
                $w = "where machineid = $mid";
                $m = "$d Machine\n $w";
                $a = "$d AssetData\n $w";
                $num += purge_record($m, $db);
                $num += purge_record($a, $db);
            }
        }
    }
    mysqli_select_db($db, core);
    return $num;
}


function purge_asset_site($site, $db)
{
    $num = 0;
    if (mysqli_select_db($db, asset)) {
        $mids = array();
        $qs   = safe_addslashes($site);
        $sql  = "select machineid from Machine\n";
        $sql .= " where cust = '$qs'";
        $list = find_many($sql, $db);
        if ($list) {
            reset($list);
            foreach ($list as $key => $row) {
                $mids[] = $row['machineid'];
            }
        }
        if ($mids) {
            $txt = join(',', $mids);
            $a = "delete from AssetData where\n machineid in ($txt)";
            $m = "delete from Machine\n where cust = '$qs'";
            $num += purge_record($m, $db);
            $num += purge_record($a, $db);
        }
    }
    mysqli_select_db($db, core);
    return $num;
}


/*
    |  Removes all the value records for the
    |  specified machine group $gid.
    |
    |  For the moment, also removes the GroupSettings
    |  records for this groups.
    */

function kill_config_mgrp($gid, $db)
{
    $num = 0;
    if ($gid) {
        debug_note("kill_config_mgrp($gid,db)");
        $del = "delete from\n core";
        $grp = "where mgroupid = $gid";

//        $err = PHP_DSYN_InvalidateRange(
//            CUR,
//            constDataSetGConfigVarValues,
//            constOperationPermanentDelete,
//            "DELETE FROM VarValues_cksum "
//                . "USING VarValues_cksum LEFT JOIN MachineGroups ON ("
//                . "VarValues_cksum.mgroupuniq=MachineGroups.mgroupuniq) "
//                . "WHERE MachineGroups.mgroupid=$gid",
//            "UPDATE VarValues_cksum SET VarValues_cksum.cksum='' WHERE "
//                . "(level=0)",
//            "INSERT INTO VarValues_deleted ("
//                . "mgroupuniq, mcatuniq, varscopuniq, varnameuniq, delrev) "
//                . "SELECT VarValues.mgroupuniq, VarValues.mcatuniq, "
//                . "VarValues.varscopuniq, VarValues.varnameuniq, 1 FROM "
//                . "VarValues LEFT JOIN MachineGroups ON (VarValues.mgroupuniq="
//                . "MachineGroups.mgroupuniq) WHERE MachineGroups.mgroupid=$gid"
//                . " ON DUPLICATE KEY UPDATE delrev=delrev+1",
//            NULL
//        );

        /* The middle SQL statement invalidates many more rows than it
                needs to, but a complex join would be required to fix this. */
//        $err2 = PHP_DSYN_InvalidateRange(
//            CUR,
//            constDataSetGConfigSemClears,
//            constOperationPermanentDelete,
//            "DELETE FROM SemClears_cksum "
//                . "USING SemClears_cksum LEFT JOIN MachineGroups ON ("
//                . "SemClears_cksum.mgroupuniq=MachineGroups.mgroupuniq) "
//                . "WHERE MachineGroups.mgroupid=$gid",
//            "UPDATE SemClears_cksum SET SemClears_cksum.cksum='' WHERE "
//                . "level IN (0,1,2)",
//            "INSERT INTO SemClears_deleted ("
//                . "censussiteuniq, mgroupuniq, censusuniq, mcatuniq, "
//                . "varscopuniq, varnameuniq, delrev) SELECT SemClears."
//                . "censussiteuniq, SemClears.mgroupuniq, SemClears.censusuniq,"
//                . " SemClears.mcatuniq, SemClears.varscopuniq, "
//                . "SemClears.varnameuniq, 1 FROM SemClears LEFT JOIN "
//                . "MachineGroups ON (SemClears.mgroupuniq="
//                . "MachineGroups.mgroupuniq) WHERE MachineGroups.mgroupid=$gid"
//                . " ON DUPLICATE KEY UPDATE delrev=delrev+1",
//            NULL
//        );

        if (($err == constAppNoErr) && ($err2 == constAppNoErr)) {
            $sql = "select setid from " . $GLOBALS['PREFIX'] . "core.GroupSettings left join "
                . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.GroupSettings.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
            $set2 = DSYN_DeleteSet(
                $sql,
                constDataSetGConfigGroupSettings,
                "setid",
                "kill_config_mgrp",
                2,
                1,
                constOperationPermanentDelete,
                $db
            );

            $val = "$del.VarValues using " . $GLOBALS['PREFIX'] . "core.VarValues left join "
                . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)\n $grp";
            $sem = "$del.SemClears using " . $GLOBALS['PREFIX'] . "core.SemClears left join "
                . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.SemClears.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)\n $grp";
            $set = "$del.GroupSettings using " . $GLOBALS['PREFIX'] . "core.GroupSettings left join "
                . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.GroupSettings.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)\n $grp";
            $num += purge_record($val, $db);
            $num += purge_record($sem, $db);
            if ($set2) {
                $num += purge_record($set, $db);
            }
        }
    }
    return $num;
}


/*
    |  Removes all the machine specific records for
    |  the specified machine.
    */

function kill_config_host($hid, $site, $host, $db)
{
    $num  = 0;
    $hgrp = GCFG_find_host_mgrp($hid, $site, $host, $db);
    if (($hgrp) && ($hid)) {
        $gid = $hgrp['mgroupid'];
        $del = "delete from\n core";
        $cen = "where censusid = $hid";
        $idn = "where id = $hid";
        $map = "$del.ValueMap using " . $GLOBALS['PREFIX'] . "core.ValueMap left join " . $GLOBALS['PREFIX'] . "core.Census on"
            . " (" . $GLOBALS['PREFIX'] . "core.ValueMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq)\n $idn";
        $rev = "$del.Revisions  \n $cen";
        $sem = "$del.SemClears using " . $GLOBALS['PREFIX'] . "core.SemClears left join " . $GLOBALS['PREFIX'] . "core.Census "
            . "on (" . $GLOBALS['PREFIX'] . "core.SemClears.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq)\n "
            . "$idn";
        $inv = "$del.InvalidVars\n $cen";
        $sum = "$del.LegacyCache\n $cen";

        /* Note that there's a race condition here, but it's extremely
                slim - someone would have to hit the recompute checksums
                button while an expunge is running. */
//        $err = PHP_DSYN_InvalidateRange(
//            CUR,
//            constDataSetGConfigValueMap,
//            constOperationPermanentDelete,
//            "DELETE FROM ValueMap_cksum "
//                . "USING ValueMap_cksum LEFT JOIN Census ON (ValueMap_cksum."
//                . "censusuniq=Census.censusuniq) WHERE Census.id=$hid",
//            "UPDATE ValueMap_cksum LEFT JOIN Census ON (ValueMap_cksum."
//                . "censussiteuniq=Census.censussiteuniq) SET "
//                . "ValueMap_cksum.cksum='' WHERE ("
//                . "Census.id=$hid AND level=1) "
//                . "OR (level=0)",
//            "INSERT INTO ValueMap_deleted ("
//                . "censussiteuniq,censusuniq,varscopuniq,varnameuniq,delrev) "
//                . "SELECT ValueMap.censussiteuniq,ValueMap.censusuniq,"
//                . "ValueMap.varscopuniq,ValueMap.varnameuniq,"
//                . "1 FROM ValueMap LEFT JOIN Census ON (ValueMap.censusuniq="
//                . "Census.censusuniq) WHERE Census.id=$hid ON DUPLICATE KEY "
//                . "UPDATE delrev=delrev+1",
//            NULL
//        );
//        if ($err != constAppNoErr) {
//            logs::log(__FILE__, __LINE__, "kill_config_host: PHP_DSYN_InvalidateRange "
//                . "returned $err invalidating ValueMap", 0);
//            echo "An error occurred invalidating ValueMap checksums.";
//            return 0;
//        }

//        $err = PHP_DSYN_InvalidateRange(
//            CUR,
//            constDataSetGConfigValueMap,
//            constOperationPermanentDelete,
//            "DELETE FROM SemClears_cksum "
//                . "USING SemClears_cksum LEFT JOIN Census ON (SemClears_cksum"
//                . ".censusuniq=Census.censusuniq) WHERE Census.id=$hid",
//            "UPDATE SemClears_cksum LEFT JOIN Census ON (SemClears_cksum."
//                . "censussiteuniq=Census.censussiteuniq) SET SemClears_cksum."
//                . "cksum='' WHERE (Census.id=$hid AND level=1) "
//                . "OR (level=0)",
//            "INSERT INTO SemClears_deleted ("
//                . "censussiteuniq,mgroupuniq,censusuniq,mcatuniq,varscopuniq,"
//                . "varnameuniq,delrev) SELECT SemClears.censussiteuniq,"
//                . "SemClears.mgroupuniq,SemClears.censusuniq, SemClears."
//                . "mcatuniq, SemClears.varscopuniq, SemClears.varnameuniq,"
//                . "1 FROM SemClears LEFT JOIN Census ON (SemClears.censusuniq="
//                . "Census.censusuniq) WHERE Census.id=$hid ON DUPLICATE KEY "
//                . "UPDATE delrev=delrev+1",
//            NULL
//        );
//        if ($err != constAppNoErr) {
//            logs::log(__FILE__, __LINE__, "kill_config_host: PHP_DSYN_InvalidateRange "
//                . "returned $err invalidating SemClears", 0);
//            echo "An error occurred invalidating SemClears checksums.";
//            return 0;
//        }

        debug_note("kill_config_host $host at $site (h:$hid,g:$gid)");
        $num += purge_record($rev, $db);
        $num += purge_record($map, $db);
        $num += purge_record($sem, $db);
        $num += purge_record($sum, $db);
        $num += purge_record($inv, $db);
        $num += kill_config_mgrp($gid, $db);
    }
    return $num;
}


/*
    |  Returns the set of all the machines
    |  at the specified site which have
    |  logged configuration information.
    |
    |  Sometimes there aren't any.
    */

function config_list($site, $db)
{
    $qs  = safe_addslashes($site);
    $sql = "select C.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
        . " where C.site = '$qs'\n"
        . " and R.censusid = C.id";
    return find_many($sql, $db);
}


/*
    |  Removes all the machine specific records for this
    |  machine.  Once we're done, check to see if this
    |  was the only machine in the site ... if so, remove
    |  the site configuration as well.
    */

function purge_config_host($hid, $site, $host, $db)
{
    $num = kill_config_host($hid, $site, $host, $db);
    $set = config_list($site, $db);
    if (!$set) {
        $grp = GCFG_find_site_mgrp($site, $db);
        if ($grp) {
            $gid  = $grp['mgroupid'];
            $num += kill_config_mgrp($gid, $db);
        }
    }
    return $num;
}


/*
    |  The multi-table delete was added to mysql in 4.0.0.
    |  We can change this when we drop support for mysql 3.x
    */

function kill_invalid($site, $db)
{
    $num = 0;
    $qs  = safe_addslashes($site);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where username = ''\n"
        . " and customer = '$qs'";
    $row = find_one($sql, $db);
    if ($row) {
        $cid = $row['id'];
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "core.InvalidVars\n"
            . " where siteid = $cid";
        $num = purge_record($sql, $db);
    }
    return $num;
}


function purge_config_site($site, $db)
{
    $num = 0;
    $set = config_list($site, $db);
    $grp = GCFG_find_site_mgrp($site, $db);
    if (($set) && ($grp)) {
        $gid = $grp['mgroupid'];
        debug_note("purge_config_site $site ($gid)");
        reset($set);
        foreach ($set as $key => $row) {
            $host = $row['host'];
            $hid  = $row['id'];
            $num += kill_config_host($hid, $site, $host, $db);
        }
        $num += kill_invalid($site, $db);
        $num += kill_config_mgrp($gid, $db);
    }
    return $num;
}


/*
    |  It turns out that myisam tables can do concurrent selects and
    |  inserts, but only in the case where there are no holes in the
    |  file.  So ... we never delete from the middle of the Events
    |  table ... we just have to wait for it to expire.
    */


function purge_event_site($site, $db)
{
    $num = 0;
    if (mysqli_select_db($db, event)) {
        $qs   = safe_addslashes($site);
        $sql  = "delete from Console\n"
            . " where site = '$qs'";
        $num += purge_record($sql, $db);

        $sql = "update " . $GLOBALS['PREFIX'] . "core.Census set\n"
            . " deleted = 1\n"
            . " where host = '$qs'";
        $res  = redcommand($sql, $db);
        $num += affected($res, $db);
    }
    mysqli_select_db($db, core);
    return $num;
}


function purge_event_host($site, $host, $db)
{
    $num = 0;
    if (mysqli_select_db($db, core)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "update " . $GLOBALS['PREFIX'] . "core.Census set\n"
            . " deleted = 1\n"
            . " where host = '$qh'\n"
            . " and site = '$qs'";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    mysqli_select_db($db, core);
    return $num;
}

function purge_update_host($site, $host, $db)
{
    $num = 0;
    if (mysqli_select_db($db, swupdate)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "delete from UpdateMachines\n"
            . " where machine='$qh'\n"
            . " and sitename='$qs'";
        $num = purge_record($sql, $db);
    }
    mysqli_select_db($db, core);
    return $num;
}

function purge_provis_host($site, $host, $db)
{
    $num = 0;
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);

    $del = "delete from\n provision";
    $txt = "where machine='$qh'\n"
        . " and sitename='$qs'";

    $ma  = "$del.MachineAssignments\n $txt";
    $mt  = "$del.Meter\n $txt";
    $au  = "$del.Audit\n $txt";

    $num += purge_record($ma, $db);
    $num += purge_record($mt, $db);
    $num += purge_record($au, $db);
    return $num;
}

/*
    |  4/2/04 Downloads.sitename is no longer used.
    |  These records are no longer site specific.
    */

function purge_update_site($site, $db)
{
    $num = 0;
    if (mysqli_select_db($db, swupdate)) {
        $qs  = safe_addslashes($site);
        $del = "delete from";
        $txt = "where sitename='$qs'";

        $um  = "$del UpdateMachines\n $txt";
        $us  = "$del UpdateSites\n $txt";

        $num += purge_record($us, $db);
        $num += purge_record($um, $db);
    }
    mysqli_select_db($db, core);
    return $num;
}

function purge_provis_site($site, $db)
{
    $num = 0;
    $qs  = safe_addslashes($site);
    $del = "delete from\n provision";
    $txt = "where sitename='$qs'";

    $sa = "$del.SiteAssignments\n $txt";
    $ma = "$del.MachineAssignments\n $txt";
    $au = "$del.Audit\n $txt";
    $mt = "$del.Meter\n $txt";

    $num += purge_record($sa, $db);
    $num += purge_record($ma, $db);
    $num += purge_record($au, $db);
    $num += purge_record($mt, $db);
    return $num;
}


function purge_crypt_site($site, $db)
{
    $num = 0;
    $set = config_list($site, $db);
    $uuu = array();
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $uuu[] = $row['uuid'];
        }
    }
    if ($uuu) {
        $ids = db_access($uuu);
        $del = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.CryptKeys\n"
            . " where uuid in ($ids)";
        $num = purge_record($del, $db);
    }
    return $num;
}


function purge_crypt_host($site, $host, $db)
{
    $num = 0;
    $row = find_census_name($site, $host, $db);
    if ($row) {
        $qu  = safe_addslashes($row['uuid']);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.CryptKeys\n"
            . " where uuid = '$qu'";
        $num = purge_record($sql, $db);
    }
    return $num;
}


function site_list($site, $db)
{
    $list = array();
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select distinct id\n"
            . " from " . $GLOBALS['PREFIX'] . "core.Census\n"
            . " where site = '$qs'";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $list[] = $row['id'];
        }
    }
    return $list;
}


function purge_patch_list($list, $db)
{
    $num = 0;
    if ($list) {
        if (mysqli_select_db($db, 'softinst')) {
            $txt = join(',', $list);
            $sql = "delete from Machine\n"
                . " where id in ($txt)";
            $mac = purge_record($sql, $db);
            $sql = "delete from PatchStatus\n"
                . " where id in ($txt)";
            $psr = purge_record($sql, $db);
            $num = $mac + $psr;
            debug_note("removed $psr patchstatus, $mac machines");
            mysqli_select_db($db, 'core');
        }
    }
    return $num;
}

function purge_groups_list($list, $db)
{
    $num = 0;
    if ($list) {
        $txt = join(',', $list);

        $sql = "select mgmapid, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq, "
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap "
            . "left join "
            . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
            . $GLOBALS['PREFIX'] . "core.Census.censusuniq) where id in ($txt)";
        $set = find_many($sql, $db);
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
                        "purge_groups_list: "
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
            "purge_groups_list",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "delete from MachineGroupMap using MachineGroupMap left "
            . "join " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
            . $GLOBALS['PREFIX'] . "core.Census.censusuniq)\n"
            . " where id in ($txt)";
        if ($set) {
            $num = purge_record($sql, $db);
            debug_note("$num machine group map records removed");
        }
    }
    return $num;
}

function purge_patch_host($mid, $db)
{
    $list = array($mid);
    return purge_patch_list($list, $db);
}

function purge_groups_host($mid, $db)
{
    $list = array($mid);
    return purge_groups_list($list, $db);
}
