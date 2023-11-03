<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 8-Jul-04   EWB     Created.
 9-Jul-04   EWB     Automatically create default wcfg / pcfg.
12-Jul-04   EWB     default wcfg / pcfg from shared code.
13-Jul-04   EWB     removed informative messages.
14-Jul-04   EWB     removed: softinst.WUConfig.installdelay
19-Jul-04   EWB     removed: softinst.PatchCConfig.installdelay
10-Aug-04   EWB     added: constWizCritical
20-Aug-04   EWB     Wizard_Removed becomes Wizard_Declined.
13-Sep-04   EWB     Removed PatchConfig.schedfail
14-Sep-04   EWB     Improved error logging.
16-Sep-04   EWB     insert_pcfg / insert_wcfg
10-Nov-04   EWB     PatchConfig.configtype
28-Dec-04   BTE     PatchConfig.chain and PatchConfig.chainseconds,
                    WUConfig.restart.
29-Dec-04   BTE     Moved PatchConfig.chain, .chainseconds to WUConfig.chain,
                    .chainseconds.
21-Feb-05   EWB     Note patch group creation in logfile.
24-Feb-06   BTE     Removed unused check_groups_dirty.
20-Sep-06   BTE     Bug 2826: Make MUM approve/decline wizards a little easier
                    to use (not so large).
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.

*/

define('constPatchAll',    'All');
define('constPatchUpdate', 'Update');
define('constPatchCategoryType',    'Type');
define('constPatchCategoryMandatory',   'Mandatory');


function insert_pcat($name, $pre, $db)
{
    $kid = 0;
    $qn  = safe_addslashes($name);
    $sql = "update " . $GLOBALS['PREFIX'] . "softinst.PatchCategories set\n"
        . " precedence = precedence+1\n"
        . " where $pre <= precedence";
    $res = redcommand($sql, $db);
    $sql = "insert into\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.PatchCategories set\n"
        . " category = '$qn',\n"
        . " precedence = $pre";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $kid  = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $stat = "k:$kid,p:$pre";
        $acts = 'created';
    } else {
        $stat = "p:$pre";
        $acts = 'create failed';
    }
    $text = "patch: pcat $name $acts ($stat)";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
    return $kid;
}


function insert_pgrp($name, $type, $glob, $hman, $kid, $user, $db)
{
    $jid = 0;
    $now = time();
    $qn  = safe_addslashes($name);
    $qu  = safe_addslashes($user);
    $sql = "insert into\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroups set\n"
        . " name = '$qn',\n"
        . " username = '$qu',\n"
        . " global = $glob,\n"
        . " human = $hman,\n"
        . " style = $type,\n"
        . " created = $now,\n"
        . " pcategoryid = $kid";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $jid  = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $stat = "t:$type,g:$glob,h:$hman,k:$kid,j:$jid,u:$user";
        $acts = 'created';
    } else {
        $stat = "t:$type,g:$glob,h:$hman,k:$kid,u:$user";
        $acts = 'create failed';
    }
    $text = "patch: pgrp $acts ($stat) $name";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
    return $jid;
}


function build_pgrp($name, $type, $kid, $db)
{
    $grp = find_pgrp_name($name, $db);
    if (!$grp) {
        $jid = insert_pgrp($name, $type, 1, 0, $kid, '', $db);
        if ($jid) {
            $grp = find_pgrp_name($name, $db);
        }
    }
    return $grp;
}

/*
    |  Just checking for existance, we don't care who the
    |  owner is.
    */

function pgrp_exists($name, $id, $db)
{
    $qn  = safe_addslashes($name);
    $sql = "select pgroupid\n"
        . " from " . $GLOBALS['PREFIX'] . "softinst.PatchGroups\n"
        . " where name = '$qn'\n"
        . " and pgroupid != $id";
    $row = find_one($sql, $db);
    return ($row) ? true : false;
}


function pcat_exists($name, $id, $db)
{
    $qn  = safe_addslashes($name);
    $sql = "select pcategoryid\n"
        . " from " . $GLOBALS['PREFIX'] . "softinst.PatchCategories\n"
        . " where category = '$qn'\n"
        . " and pcategoryid != $id";
    $row = find_one($sql, $db);
    return ($row) ? true : false;
}

function unique_pgrp($text, $id, $db)
{
    $uniq = 0;
    $name = $text;
    if (pgrp_exists($name, $id, $db)) {
        do {
            $uniq++;
            $xxx  = sprintf('%03d', $uniq);
            $name = "$text $xxx";
        } while (pgrp_exists($name, $id, $db));
    }
    return $name;
}


function unique_pcat($text, $id, $db)
{
    $uniq = 0;
    $name = $text;
    if (pcat_exists($name, $id, $db)) {
        do {
            $uniq++;
            $xxx  = sprintf('%03d', $uniq);
            $name = "$text $xxx";
        } while (pcat_exists($name, $id, $db));
    }
    return $name;
}


function insert_pmap($jid, $mid, $db)
{
    $nid = 0;
    if (($jid) && ($mid)) {
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap set\n"
            . " pgroupid = $jid,\n"
            . " patchid = $mid";
        $res = redcommand($sql, $db);
        if (affected($res, $db)) {
            $nid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        } else {
            $text = "patch: failed to insert patch $mid into pgrp $jid";
            debug_note($text);
            logs::log(__FILE__, __LINE__, $text, 0);
        }
    }
    return $nid;
}


function build_pcat($name, $pre, $db)
{
    $cat = find_pcat_name($name, $db);
    if (!$cat) {
        $kid = insert_pcat($name, $pre, $db);
        if ($kid) {
            $cat = find_pcat_name($name, $db);
        }
    }
    return $cat;
}


function find_pcfg($gid, $jid, $db)
{
    $row = array();
    if (($gid > 0) && ($jid > 0)) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchConfig\n"
            . " where mgroupid = $gid\n"
            . " and pgroupid = $jid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function insert_wcfg($gid, $wcfg, $db)
{
    $wid = 0;
    if (($gid) && ($wcfg)) {
        $qu   = safe_addslashes($wcfg['serverurl']);
        $iday = $wcfg['installday'];
        $man  = $wcfg['management'];
        $newp = $wcfg['newpatches'];
        $psrc = $wcfg['patchsource'];
        $hour = $wcfg['installhour'];
        $prop = $wcfg['propagate'];
        $updc = $wcfg['updatecache'];
        $csec = $wcfg['cacheseconds'];
        $rest = $wcfg['restart'];
        $chan = $wcfg['chain'];
        $chas = $wcfg['chainseconds'];

        $now  = time();
        $sql  = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.WUConfig set\n"
            . " mgroupid = $gid,\n"
            . " lastupdate = $now,\n"
            . " management = $man,\n"
            . " installhour = $hour,\n"
            . " installday = $iday,\n"
            . " patchsource = $psrc,\n"
            . " propagate = $prop,\n"
            . " serverurl = '$qu',\n"
            . " newpatches = $newp,\n"
            . " updatecache = $updc,\n"
            . " cacheseconds = $csec,\n"
            . " restart = $rest,\n"
            . " chain = $chan,\n"
            . " chainseconds = $chas";

        $res = redcommand($sql, $db);
        if (affected($res, $db)) {
            $wid = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
        }
    }
    return $wid;
}


function insert_pcfg($gid, $jid, $pcfg, $wpgroupid, $db)
{
    $pgroupid = $jid;
    if ($pgroupid == 0) {
        $pgroupid = $wpgroupid;
    }

    $pid  = 0;
    if (($gid) && ($pgroupid) && ($pcfg)) {
        $qt   = safe_addslashes($pcfg['notifytext']);

        $ins  = $pcfg['installation'];
        $rusr = $pcfg['reminduser'];
        $ctyp = $pcfg['configtype'];

        $ssec = $pcfg['scheddelay'];
        $smin = $pcfg['schedminute'];
        $shor = $pcfg['schedhour'];
        $sday = $pcfg['schedday'];
        $smon = $pcfg['schedmonth'];
        $swek = $pcfg['schedweek'];
        $srnd = $pcfg['schedrandom'];
        $styp = $pcfg['schedtype'];

        $nsec = $pcfg['notifydelay'];
        $nmin = $pcfg['notifyminute'];
        $nhor = $pcfg['notifyhour'];
        $nday = $pcfg['notifyday'];
        $nmon = $pcfg['notifymonth'];
        $nwek = $pcfg['notifyweek'];
        $nrnd = $pcfg['notifyrandom'];
        $ntyp = $pcfg['notifytype'];
        $nfal = $pcfg['notifyfail'];

        $nadv = $pcfg['notifyadvance'];
        $nsch = $pcfg['notifyschedule'];
        $pshd = $pcfg['preventshutdown'];
        $nat  = $pcfg['notifyadvancetime'];

        $now  = time();
        $sql  = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchConfig set\n"
            . " mgroupid = $gid,\n"
            . " pgroupid = $pgroupid,\n"
            . " lastupdate = $now,\n"
            . " installation = $ins,\n"
            . " preventshutdown = $pshd,\n"
            . " reminduser = $rusr,\n"
            . " configtype = $ctyp,\n"
            . " scheddelay = $ssec,\n"
            . " schedminute = $smin,\n"
            . " schedhour = $shor,\n"
            . " schedday = $sday,\n"
            . " schedmonth = $smon,\n"
            . " schedweek = $swek,\n"
            . " schedrandom = $srnd,\n"
            . " schedtype = $styp,\n"
            . " notifydelay = $nsec,\n"
            . " notifyminute = $nmin,\n"
            . " notifyhour = $nhor,\n"
            . " notifyday = $nday,\n"
            . " notifymonth = $nmon,\n"
            . " notifyweek = $nwek,\n"
            . " notifyrandom = $nrnd,\n"
            . " notifytype = $ntyp,\n"
            . " notifyfail = $nfal,\n"
            . " notifyadvance = $nadv,\n"
            . " notifyschedule = $nsch,\n"
            . " notifyadvancetime = $nat,\n"
            . " notifytext = '$qt',\n"
            . " wpgroupid = $wpgroupid";
        $res  = redcommand($sql, $db);
        if (affected($res, $db)) {
            $pid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        }
    }
    return $pid;
}

function insert_default_pcfg($gid, $jid, $db)
{
    $pcfg = default_pcfg();
    return insert_pcfg($gid, $jid, $pcfg, $jid, $db);
}

function insert_default_wcfg($gid, $db)
{
    $wcfg = default_wcfg();
    return insert_wcfg($gid, $wcfg, $db);
}


function patch_init($db)
{
    $t0   = microtime();
    $all  = constPatchAll;
    $upd  = constPatchUpdate;
    $cupd = build_pcat($upd, 1, $db);
    $call = build_pcat($all, 1, $db);
    $type = constStyleBuiltin;
    $gall = array();
    if ($call) {
        $kid  = $call['pcategoryid'];
        $gall = build_pgrp($all, $type, $kid, $db);
    }

    /*
        |
        |  Add new patches to the "All" group.
        |
        */

    if (($call) && ($gall)) {
        $kid = $call['pcategoryid'];
        $jid = $gall['pgroupid'];
        $sql = "select P.patchid as mid,\n"
            . " P.name as name from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Patches as P\n"
            . " left join " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as M\n"
            . " on M.pgroupid = $jid\n"
            . " and M.patchid = P.patchid\n"
            . " where isnull(M.patchid)";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            $mid  = $row['mid'];
            $name = $row['name'];
            $nid  = insert_pmap($jid, $mid, $db);
            if ($nid) {
                $stat = "j:$jid,m:$mid,n:$nid";
                $text = "patch: pgrp added to '$all' ($stat) $name";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);
            }
        }
    }

    /*
        |
        |  Add new groups to the "Update" category.
        |
        */

    $t1 = microtime();
    if ($cupd) {
        $kid = $cupd['pcategoryid'];
        $sql = "select P.patchid as mid,\n"
            . " P.name as name from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Patches as P\n"
            . " left join " . $GLOBALS['PREFIX'] . "softinst.PatchGroups as G\n"
            . " on G.name = P.name\n"
            . " and G.pcategoryid = $kid\n"
            . " where isnull(G.pgroupid)";
        $set = find_many($sql, $db);
        foreach ($set as $key => $row) {
            $mid  = $row['mid'];
            $name = $row['name'];
            $pgrp = build_pgrp($name, $type, $kid, $db);
            if ($pgrp) {
                $jid = $pgrp['pgroupid'];
                $nid = insert_pmap($jid, $mid, $db);
                if ($nid) {
                    $stat = "j:$jid,m:$mid,n:$nid";
                    $text = "patch: pgrp added single ($stat) $name";
                    logs::log(__FILE__, __LINE__, $text, 0);
                    debug_note($text);
                }
            }
        }
    }

    $mall = constCatAll;
    $wcfg = find_wcfg_name($mall, $db);
    $mgrp = find_mgrp_name($mall, $db);
    $pgrp = find_pgrp_name($mall, $db);
    if (($mgrp) && (!$wcfg)) {
        $gid = $mgrp['mgroupid'];
        if (insert_default_wcfg($gid, $db)) {
            debug_note("added default wcfg record.");
            //           echo "<p>Added default machine configuration.</p>\n";
        }
    }

    if (($mgrp) && ($pgrp)) {
        $gid  = $mgrp['mgroupid'];
        $jid  = $pgrp['pgroupid'];
        $pcfg = find_pcfg($gid, $jid, $db);
        if (!$pcfg) {
            if (insert_default_pcfg($gid, $jid, $db)) {
                debug_note("added default pcfg record.");
                //          echo "<p>Added default update configuration.</p>\n";
            }
        }
    }

    $sql = "select N.patchid\n"
        . " from " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as N\n"
        . " left join " . $GLOBALS['PREFIX'] . "softinst.Patches as P\n"
        . " on P.patchid = N.patchid\n"
        . " where P.patchid is NULL\n"
        . " group by N.patchid";
    $set = find_many($sql, $db);
    if ($set) {
        $num = safe_count($set);
        $tmp = distinct($set, 'patchid');
        $txt = join(',', $tmp);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap\n"
            . " where patchid in ($txt)";
        $res = redcommand($sql, $db);
        $del = affected($res, $db);
        debug_note("$num ($txt) orphaned members, $del removed.");
    }

    $sql = "select N.pgroupid\n"
        . " from " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap as N\n"
        . " left join " . $GLOBALS['PREFIX'] . "softinst.PatchGroups as P\n"
        . " on P.pgroupid = N.pgroupid\n"
        . " where P.pgroupid is NULL\n"
        . " group by N.pgroupid";
    $set = find_many($sql, $db);
    if ($set) {
        $num = safe_count($set);
        $tmp = distinct($set, 'pgroupid');
        $txt = join(',', $tmp);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap\n"
            . " where pgroupid in ($txt)";
        $res = redcommand($sql, $db);
        $del = affected($res, $db);
        debug_note("$num ($txt) orphaned members, $del removed.");
    }

    $t2 = microtime();
    $categoryType = build_pcat(constPatchCategoryType, 2, $db);
    if ($categoryType) {
        $kid  = $categoryType['pcategoryid'];

        /* Now, older servers could have the old Mandatory patch type,
                so put them in the critical group and mark them as mandatory */
        $sql = 'UPDATE Patches SET mandatory=' . constPatchMandatoryYes
            . ' WHERE type=' . constPatchTypeMandatory;
        redcommand($sql, $db);

        /* In the older client, mandatory updates are only critical
                updates, so change the type to critical. */
        $sql = 'UPDATE Patches SET type=' . constPatchTypeCritical
            . ' WHERE type=' . constPatchTypeMandatory;
        redcommand($sql, $db);

        $sql = 'SELECT pgroupid FROM PatchGroups WHERE style='
            . constStyleType . " AND search='" . constPatchTypeMandatory
            . "'";
        $row = find_one($sql, $db);
        if ($row) {
            $sql = 'DELETE FROM PatchGroupMap WHERE pgroupid='
                . $row['pgroupid'];
            redcommand($sql, $db);
            $sql = 'DELETE FROM PatchGroups WHERE pgroupid='
                . $row['pgroupid'];
            redcommand($sql, $db);
        }

        PDRT_BuildTypeGroup(
            constPatchTypeUpdateStr,
            $kid,
            constPatchTypeUpdate,
            $db
        );
        PDRT_BuildTypeGroup(
            constPatchTypeServicePackStr,
            $kid,
            constPatchTypeServicePack,
            $db
        );
        PDRT_BuildTypeGroup(
            constPatchTypeRollupStr,
            $kid,
            constPatchTypeRollup,
            $db
        );
        PDRT_BuildTypeGroup(
            constPatchTypeSecurityStr,
            $kid,
            constPatchTypeSecurity,
            $db
        );
        PDRT_BuildTypeGroup(
            constPatchTypeCriticalStr,
            $kid,
            constPatchTypeCritical,
            $db
        );
    }

    $t3 = microtime();
    $categoryType = build_pcat(constPatchCategoryMandatory, 3, $db);
    if ($categoryType) {
        $kid  = $categoryType['pcategoryid'];

        /* Note: if the where clause format changes here, it will also
                have to change in PDRT_AddMandatoryPatch */
        PDRT_BuildMandatoryGroup(
            constPatchMandatoryUpdateStr,
            $kid,
            'type=' . constPatchTypeUpdate . ' AND mandatory='
                . constPatchMandatoryYes,
            $db
        );
        PDRT_BuildMandatoryGroup(
            constPatchMandatoryServicePackStr,
            $kid,
            'type=' . constPatchTypeServicePack . ' AND mandatory='
                . constPatchMandatoryYes,
            $db
        );
        PDRT_BuildMandatoryGroup(
            constPatchMandatoryRollupStr,
            $kid,
            'type=' . constPatchTypeRollup . ' AND mandatory='
                . constPatchMandatoryYes,
            $db
        );
        PDRT_BuildMandatoryGroup(
            constPatchMandatorySecurityStr,
            $kid,
            'type=' . constPatchTypeSecurity . ' AND mandatory='
                . constPatchMandatoryYes,
            $db
        );
        PDRT_BuildMandatoryGroup(
            constPatchMandatoryCriticalStr,
            $kid,
            'type=' . constPatchTypeCritical . ' AND mandatory='
                . constPatchMandatoryYes,
            $db
        );
    }

    $t4 = microtime();

    show_timer($t0, $t1, 'Category "All"');
    show_timer($t1, $t2, 'Category "Updates"');
    show_timer($t2, $t3, 'Category "Type"');
    show_timer($t3, $t4, 'Category "Mandatory"');
    show_timer($t0, $t3, 'Patch Total');
}



function update_wcfg_set($set, $db)
{
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $hid = $row['id'];
            $old = $row['wuconfigid'];
            $tmp = find_correct_wconfig($hid, $db);
            if ($tmp) {
                $new = $tmp['wid'];
                if ($new != $old) {
                    $grp = $tmp['grp'];
                    $now = time();
                    $sql = "update " . $GLOBALS['PREFIX'] . "softinst.Machine set\n"
                        . " wuconfigid = $new,\n"
                        . " lastchange = $now\n"
                        . " where id = $hid";
                    redcommand($sql, $db);
                    debug_note("machine $hid moves to group $grp");
                }
            }
        }
    }
}


function recalc_wcfg_invalid($db)
{
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.Machine\n"
        . " where wuconfigid = 0";
    $set = find_many($sql, $db);
    update_wcfg_set($set, $db);
}


function recalc_wcfg_all($db)
{
    $sql = "select id, wuconfigid\n"
        . ' from ' . $GLOBALS['PREFIX'] . 'softinst.Machine';
    $set = find_many($sql, $db);
    update_wcfg_set($set, $db);
}

// function wcfg_init($db)
// {
//     recalc_wcfg_invalid($db);
// }


function check_patch_dirty($db)
{
    $val = constDirtySet;
    $opt = constPatchDirty;
    $row = find_dirty($opt, $db);
    if ($row) {
        $val = $row['value'];
    } else {
        if (create_dirty($opt, $db)) {
            debug_note("Option <b>$opt</b> has been created.");
        }
    }
    if ($val != constDirtyClr) {
        while (clear_dirty($opt, $db)) {
            patch_init($db);
        }
    }
}

/* PDRT_BuildTypeGroup

        Creates the type group indentified by the numeric $type with a name
        of $name in the patch category $categoryid.  Adds all patches of
        the same $type to itself.
    */
function PDRT_BuildTypeGroup($name, $categoryid, $type, $db)
{
    $now = time();
    $pgroupid = 0;
    $sql = "INSERT IGNORE INTO PatchGroups SET name='$name', global=1, "
        . "human=0, style=" . constStyleType . ", created=$now, "
        . "search='$type', pcategoryid=$categoryid";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $pgroupid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $text = "patch: built type group $name";
        logs::log(__FILE__, __LINE__, $text, 0);
    } else {
        $sql = "SELECT pgroupid FROM PatchGroups WHERE name='$name' AND "
            . "style=" . constStyleType;
        $row = find_one($sql, $db);
        if ($row) {
            $pgroupid = $row['pgroupid'];
        }
    }

    if (!($pgroupid)) {
        logs::log(__FILE__, __LINE__, "patch: no group found for name $name, "
            . "style " . constStyleType, 0);
        return;
    }

    $sql = "INSERT IGNORE INTO PatchGroupMap (pgroupid, patchid) SELECT "
        . "$pgroupid, patchid FROM Patches WHERE type = $type";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if ($num > 0) {
        logs::log(__FILE__, __LINE__, "patch: added $num patches to type group $pgroupid", 0);
    }
}


/* PDRT_AddNewPatchToType

        Adds the single patch $itemid to the type group identified by $type.
    */
function PDRT_AddNewPatchToType($itemid, $type, $db)
{
    $sql = "SELECT patchid FROM Patches WHERE itemid=$itemid";
    $row = find_one($sql, $db);

    $sql = "SELECT pgroupid FROM PatchGroups WHERE style=" . constStyleType
        . " and search='$type'";
    $row2 = find_one($sql, $db);

    if (($row) && ($row2)) {
        $sql = "INSERT IGNORE INTO PatchGroupMap (pgroupid, patchid) "
            . "VALUES (" . $row2['pgroupid'] . ", " . $row['patchid']
            . ")";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        if ($num > 0) {
            logs::log(__FILE__, __LINE__, "patch: added update $itemid to update type group "
                . $row2['pgroupid'], 0);
        }
    } else {
        if (!($row)) {
            logs::log(__FILE__, __LINE__, "patch: failed to find itemid $itemid", 0);
        }
        if (!($row2)) {
            logs::log(__FILE__, __LINE__, "patch: failed to find type group $type", 0);
        }
    }
}

/* PDRT_BuildMandatoryGroup

        Creates the type group indentified by the numeric $type with a name
        of $name in the patch category $categoryid.  Adds all patches of
        the same $type to itself.
    */
function PDRT_BuildMandatoryGroup($name, $categoryid, $clause, $db)
{
    $now = time();
    $pgroupid = 0;
    $sql = "INSERT IGNORE INTO PatchGroups SET name='$name', global=1, "
        . "human=0, style=" . constStyleDirectSearch . ", created=$now, "
        . "whereclause='$clause', pcategoryid=$categoryid";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $pgroupid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $text = "patch: built mandatory group $name";
        logs::log(__FILE__, __LINE__, $text, 0);
    } else {
        $sql = "SELECT pgroupid FROM PatchGroups WHERE name='$name' AND "
            . "style=" . constStyleDirectSearch;
        $row = find_one($sql, $db);
        if ($row) {
            $pgroupid = $row['pgroupid'];
        }
    }

    if (!($pgroupid)) {
        logs::log(__FILE__, __LINE__, "patch: no group found for name $name, "
            . "style " . constStyleDirectSearch, 0);
        return;
    }

    $sql = "INSERT IGNORE INTO PatchGroupMap (pgroupid, patchid) SELECT "
        . "$pgroupid, patchid FROM Patches WHERE $clause";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if ($num > 0) {
        logs::log(
            __FILE__,
            __LINE__,
            "patch: added $num patches to mandatory group $pgroupid",
            0
        );
    }
}


/* PDRT_AddMandatoryPatch

        Adds the patch $itemid to the mandatory type group for the $type.
    */
function PDRT_AddMandatoryPatch($itemid, $type, $db)
{
    /* Note: if the where clause format changes here, it will also
            have to change in patch_init */
    $sql = "SELECT pgroupid FROM PatchGroups WHERE whereclause='type="
        . "$type and mandatory=" . constPatchMandatoryYes . "' AND style="
        . constStyleDirectSearch;
    $row = find_one($sql, $db);
    if ($row) {
        $pgroupid = $row['pgroupid'];
    }

    if (!($pgroupid)) {
        logs::log(__FILE__, __LINE__, "patch: no group found for mandatory type $type, "
            . "style " . constStyleDirectSearch, 0);
        return;
    }

    $sql = "INSERT IGNORE INTO PatchGroupMap (pgroupid, patchid) SELECT "
        . "$pgroupid, patchid FROM Patches WHERE itemid=$itemid";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if ($num > 0) {
        logs::log(
            __FILE__,
            __LINE__,
            "patch: added $num patches to mandatory group $pgroupid",
            0
        );
    }
}
