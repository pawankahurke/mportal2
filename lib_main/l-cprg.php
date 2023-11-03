<?php



function purge_record($sql, $db)
{
    $res = redcommand($sql, $db);
    return affected($res, $db);
}

function purge_asset_host($site, $host, $db)
{
    $num = 0;
    if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'asset')) {
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
    mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
    return $num;
}


function purge_asset_site($site, $db)
{
    $num = 0;
    if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'asset')) {
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
    mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
    return $num;
}




function kill_config_mgrp($gid, $uuid, $db)
{
    $num = 0;
    if ($gid) {
        debug_note("kill_config_mgrp($gid,db)");
        $del = "delete from\n core";
        $grp = "where mgroupid = $gid";



        if ($err == constAppNoErr) {
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
            $set = "$del.GroupSettings using " . $GLOBALS['PREFIX'] . "core.GroupSettings left join "
                . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.GroupSettings.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)\n $grp";
            $num += purge_record($val, $db);
            if ($set2) {
                $num += purge_record($set, $db);
            }
        }
    }
    return $num;
}




function kill_config_host($hid, $site, $host, $db)
{
    $num  = 0;
    $hgrp = GCFG_find_host_mgrp($hid, $site, $host, $db);
    if (($hgrp) && ($hid)) {
        $census = find_census_name($site, $host, $db);
        $uuid = $census['uuid'] ? $census['uuid'] : 0;
        $gid = $hgrp['mgroupid'];
        $del = "delete from\n core";
        $cen = "where censusid = $hid";
        $idn = "where id = $hid";
        $map = "$del.ValueMap using " . $GLOBALS['PREFIX'] . "core.ValueMap left join " . $GLOBALS['PREFIX'] . "core.Census on"
            . " (" . $GLOBALS['PREFIX'] . "core.ValueMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq)\n $idn";
        $rev = "$del.Revisions  \n $cen";
        // $rev2 = "$del.RevisionsPerm  \n $cen";
        $inv = "$del.InvalidVars\n $cen";
        $sum = "$del.LegacyCache\n $cen";



        if ($err != constAppNoErr) {
            return 0;
        }



        debug_note("kill_config_host $host at $site (h:$hid,g:$gid)");
        $num += purge_record($rev, $db);
        // $num += purge_record($rev2, $db);
        $num += purge_record($map, $db);
        $num += purge_record($sum, $db);
        $num += purge_record($inv, $db);
        $num += kill_config_mgrp($gid, $uuid, $db);
    }
    return $num;
}




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




function purge_config_host($hid, $site, $host, $db)
{
    $num = kill_config_host($hid, $site, $host, $db);
    $set = config_list($site, $db);
    if (!$set) {
        $grp = GCFG_find_site_mgrp($site, $db);
        if ($grp) {
            $gid  = $grp['mgroupid'];
            $census = find_census_name($site, $host, $db);
            $uuid = $census['uuid'] ? $census['uuid'] : 0;
            $num += kill_config_mgrp($gid, $uuid, $db);
        }
    }
    return $num;
}




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
        $uuid = 0;
        foreach ($set as $key => $row) {
            $host = $row['host'];
            $hid  = $row['id'];
            $census = find_census_name($site, $host, $db);
            $uuid = $census['uuid'] ? $census['uuid'] : 0;
            $num += kill_config_host($hid, $site, $host, $db);
        }
        $num += kill_invalid($site, $db);
        $num += kill_config_mgrp($gid, $uuid, $db);
    }
    return $num;
}





function purge_event_site($site, $db)
{
    $num = 0;
    if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'event')) {
        $qs   = safe_addslashes($site);
        $sql  = "delete from Console\n"
            . " where site = '$qs'";
        $num += purge_record($sql, $db);

        $sql = "update " . $GLOBALS['PREFIX'] . "core.Census set\n"
            . " deleted = 1\n"
            . " where host = '$qs'";
        $res  = redcommand($sql, $db);
        $num += affected($res, $db);

        // $sql = "update " . $GLOBALS['PREFIX'] . "core.CensusPerm set\n"
        //     . " deleted = 1\n"
        //     . " where host = '$qs'";
        // $res  = redcommand($sql, $db);
    }
    mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
    return $num;
}


function purge_event_host($site, $host, $db)
{
    $num = 0;
    if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core')) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "update " . $GLOBALS['PREFIX'] . "core.Census set\n"
            . " deleted = 1\n"
            . " where host = '$qh'\n"
            . " and site = '$qs'";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);

        // $sql = "update " . $GLOBALS['PREFIX'] . "core.CensusPerm set\n"
        //     . " deleted = 1\n"
        //     . " where host = '$qh'\n"
        //     . " and site = '$qs'";
        // $res = redcommand($sql, $db);
    }
    mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
    return $num;
}

function purge_update_host($site, $host, $db)
{
    $num = 0;
    if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'swupdate')) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "delete from UpdateMachines\n"
            . " where machine='$qh'\n"
            . " and sitename='$qs'";
        $num = purge_record($sql, $db);
    }
    mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
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



function purge_update_site($site, $db)
{
    $num = 0;
    if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'swupdate')) {
        $qs  = safe_addslashes($site);
        $del = "delete from";
        $txt = "where sitename='$qs'";

        $um  = "$del UpdateMachines\n $txt";
        $us  = "$del UpdateSites\n $txt";

        $num += purge_record($us, $db);
        $num += purge_record($um, $db);
    }
    mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
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
        if (mysqli_select_db($db, $GLOBALS['PREFIX'] . 'softinst')) {
            $txt = join(',', $list);
            $sql = "delete from Machine\n"
                . " where id in ($txt)";
            $mac = purge_record($sql, $db);
            $sql = "delete from PatchStatus\n"
                . " where id in ($txt)";
            $psr = purge_record($sql, $db);
            $num = $mac + $psr;
            debug_note("removed $psr patchstatus, $mac machines");
            mysqli_select_db($db, $GLOBALS['PREFIX'] . 'core');
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
                if (!VARS_HandleDeletedGroup($row['censusuniq'], $row['mgroupuniq'], $db)) {
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

        $sql = "select censusuniq from " . $GLOBALS['PREFIX'] . "core.Census where id IN ($txt) limit 1";
        $res = find_one($sql, $db);
        if ($res) {
            $censusuniq = $res['censusuniq'];
        }

        $sql = "delete from MachineGroups where mgroupuniq = '" . $censusuniq . "' ";
        $num = purge_record($sql, $db);
        debug_note("$num machine groups records removed");
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
