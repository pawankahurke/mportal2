<?php




define('constSetVarValFailure', -1);


define('constAssignActionModifyExpire', 1);
define('constAssignActionRevert',       2);
define('constAssignActionDelete',       3);


define('constAssignScopIndividual', 1);
define('constAssignScopIndVar',     2);
define('constAssignScopVariable',   3);
define('constAssignScopGroup',      4);

define('constDefMapUnknown', 0);
define('constDefMapSite', 1);
define('constDefMapMachine', 2);



function scrip_name($num, $name)
{
    return "Scrip $num - $name";
}




function state_var($arg)
{
    return $arg . '_ConfState';
}




function state_name($stat)
{
    switch ($stat) {
        case constVarConfStateGlobal:
            return 'global';
        case constVarConfStateLocal:
            return 'local';
        case constVarConfStateLocalOnly:
            return 'localonly';
        default:
            return "unknown($arg)";
    }
}




function stat_name($stat)
{
    switch ($stat) {
        case constVarConfStateGlobal:
            return '(g)';
        case constVarConfStateLocal:
            return '(l)';
        case constVarConfStateLocalOnly:
            return '(lo)';
        default:
            return "($stat)";
    }
}



function confirm_var($arg)
{
    return $arg . '_confirmation';
}


function normalize($txt)
{
    return str_replace("\r\n", "\n", $txt);
}




function type_name($type)
{
    switch ($type) {
        case constVblTypeInteger:
            return 'integer';
        case constVblTypeDateTime:
            return 'datetime';
        case constVblTypeString:
            return 'string';
        case constVblTypeBoolean:
            return 'boolean';
        case constVblTypeMailSendList:
            return 'maillist';
        case constVblTypeLogInfoList:
            return 'infolist';
        case constVblTypeAList:
            return 'alist';
        case constVblTypeSemaphore:
            return 'semaphore';
        case constVblTypeQueue:
            return 'queue';
        default:
            return "unknown ($type)";
    }
}



function GCFG_find_site_mgrp($site, $db)
{
    $tag = constStyleBuiltin;
    $cat = safe_addslashes(constCatSite);
    $qs  = safe_addslashes($site);
    $sql = "select G.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
        . " where C.category = '$cat'\n"
        . " and G.mcatuniq = C.mcatuniq\n"
        . " and G.global = 1\n"
        . " and G.human = 0\n"
        . " and G.style = $tag\n"
        . " and G.name = '$qs'";
    return find_one($sql, $db);
}




function find_var($name, $scop, $db)
{
    $row = array();
    if (($scop) && ($name)) {
        $qn  = safe_addslashes($name);
        $sql = "select * from Variables\n"
            . " where name = '$qn'\n"
            . " and scop = $scop";
        $row = find_one($sql, $db);
    }
    return $row;
}




function find_mgrp_info($gid, $db)
{
    $row = array();
    if ($gid) {
        $sql = "select G.*, C.category, C.precedence from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
            . " where G.mgroupid = '$gid'\n"
            . " and G.mcatuniq = C.mcatuniq\n"
            . " group by G.mgroupuniq";
        $row = find_one($sql, $db);
    }
    return $row;
}




function find_mgrp_revl($gid, $db)
{
    $sql = "select R.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
        . " " . $GLOBALS['PREFIX'] . "core.VarVersions as V,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where M.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid = $gid\n"
        . " and M.censusuniq = C.censusuniq\n"
        . " and C.id = R.censusid\n"
        . " and R.vers = V.vers\n"
        . " group by R.censusid\n"
        . " order by vers desc, ctime desc\n"
        . " limit 1";
    return find_one($sql, $db);
}



function find_mgrp_hid($hid, $db)
{
    $row = array();
    if ($hid) {
        $tag = constStyleBuiltin;
        $cat = safe_addslashes(constCatMachine);
        $sql = "select G.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H\n"
            . " where M.censusuniq=H.censusuniq\n"
            . " and H.id = $hid\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and M.mcatuniq = C.mcatuniq\n"
            . " and M.mcatuniq = G.mcatuniq\n"
            . " and G.style = $tag\n"
            . " and G.human = 0\n"
            . " and G.global = 1\n"
            . " and C.category = '$cat'";
        $row = find_one($sql, $db);
    }
    return $row;
}




function find_mgrp_cid($cid, $db)
{
    $row = array();
    if ($cid) {
        $tag = constStyleBuiltin;
        $cat = safe_addslashes(constCatSite);
        $sql = "select G.*, C.mcatid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
            . " where U.id = $cid\n"
            . " and G.name = U.customer\n"
            . " and C.category = '$cat'\n"
            . " and G.mcatuniq = C.mcatuniq\n"
            . " and G.global = 1\n"
            . " and G.human = 0\n"
            . " and G.style = $tag";
        $row = find_one($sql, $db);
    }
    return $row;
}




function GCFG_find_host_mgrp($hid, $site, $host, $db)
{
    $tag = constStyleBuiltin;
    $cat = safe_addslashes(constCatMachine);
    $qn  = safe_addslashes("$site:$host");
    $sql = "select G.*, C.mcatid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
        . " where G.mcatuniq = C.mcatuniq\n"
        . " and C.category = '$cat'\n"
        . " and G.name = '$qn'\n"
        . " and G.style = $tag\n"
        . " and G.human = 0\n"
        . " and G.global = 1";
    $row = find_one($sql, $db);



    if (!$row) {
        $grp = find_mgrp_hid($hid, $db);
        if ($grp) {
            $gid = $grp['mgroupid'];

            $test = global_def('test_sql', 0);
            $err = constAppNoErr;
            if (!($test)) {
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$gid,
                    "mgroupid",
                    constDataSetCoreMachineGroups,
                    constOperationDelete
                );
                if ($err != constAppNoErr) {
                }
            }

            if ($err == constAppNoErr) {
                $sql = "update " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
                    . " set name = '$qn', updated=UNIX_TIMESTAMP(),\n"
                    . " revlname = revlname + 1\n"
                    . " where mgroupid = $gid";
                redcommand($sql, $db);

                if (!($test)) {
                    DSYN_UpdateDependencies(
                        constDataSetCoreMachineGroups,
                        $gid,
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
            }

            $row = find_mgrp_info($gid, $db);
        }
    }
    if ($row) {
        $row['site'] = $site;
        $row['host'] = $host;
        $row['hid']  = $hid;
    }
    return $row;
}




function find_census_name($site, $host, $db)
{
    $row = array();
    if (($site) && ($host)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census\n"
            . " where site = '$qs'\n"
            . " and host = '$qh'";
        $row = find_one($sql, $db);
    }
    return $row;
}




function find_hgrp($site, $host, $db)
{
    $row = find_census_name($site, $host, $db);
    $hid = ($row) ? $row['id'] : 0;
    return GCFG_find_host_mgrp($hid, $site, $host, $db);
}




function mgrp_valu($gid, $vid, $db)
{
    $set = GCFG_GetSimpleVariableInfo('', 0, $vid, $gid, '', 0, $db);
    foreach ($set as $key => $row) {
        if ($set[$key]['def']) {
            $set[$key]['valu'] = $set[$key]['defval'];
        }
    }
    return $set;
}




function find_valu($gid, $name, $scop, $db)
{
    $set = GCFG_GetSimpleVariableInfo('', 0, 0, $gid, $name, $scop, $db);
    foreach ($set as $key => $row) {
        if ($set[$key]['def']) {
            $set[$key]['valu'] = $set[$key]['defval'];
        }
    }
    return $set;
}




function update_value($vid, $gid, $time, $host, $valu, $source, $db)
{
    $num = 0;
    if (($vid) && ($gid)) {
        $qv  = safe_addslashes(normalize($valu));
        $qh  = safe_addslashes($host);

        $set = mgrp_valu($gid, $vid, $db);
        if ($set) {
            $oldvalu = $set[0]['valu'];
        } else {
            $oldvalu = "";
        }

        $sql = "select valueid from " . $GLOBALS['PREFIX'] . "core.VarValues left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join "
            . $GLOBALS['PREFIX'] . "core.Variables on (" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq="
            . $GLOBALS['PREFIX'] . "core.Variables.varuniq) where mgroupid=$gid"
            . " and varid=$vid and valu!='$qv'";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigVarValues,
            "valueid",
            "update_value",
            0,
            1,
            constOperationDelete,
            $db
        );

        $sql = "update " . $GLOBALS['PREFIX'] . "core.VarValues left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join "
            . $GLOBALS['PREFIX'] . "core.Variables on (" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq="
            . $GLOBALS['PREFIX'] . "core.Variables.varuniq) set\n"
            . " valu = '$qv',\n"
            . " host = '$qh',\n"
            . " last = $time,\n"
            . " revl = revl+1,\n"
            . " revldef = revldef+def, lastchange=UNIX_TIMESTAMP(),\n"
            . " def = 0\n"
            . " where varid = $vid\n"
            . " and mgroupid = $gid\n"
            . " and valu != '$qv'";
        if ($set) {
            $res = redcommand($sql, $db);
            $num = affected($res, $db);

            DSYN_UpdateSet(
                $set,
                constDataSetGConfigVarValues,
                "valueid",
                $db
            );

            GCFG_UpdateHistory($oldvalu, $gid, $vid, $time, $source, $db);
        }
    }
    return $num;
}


function set_value($vid, $gid, $host, $valu, $source, $now, $db)
{
    return update_value($vid, $gid, $now, $host, $valu, $source, $db);
}




function hid_revision($hid, $now, $db)
{
    $num = 0;
    if ($hid) {
        $sql = "update " . $GLOBALS['PREFIX'] . "core.Revisions set\n"
            . " stime = $now\n"
            . " where censusid = $hid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}



function host_revision($site, $host, $now, $db)
{
    $row = find_census_name($site, $host, $db);
    $hid = ($row) ? $row['id'] : 0;
    return hid_revision($hid, $now, $db);
}




function site_revision($site, $now, $db)
{
    $num = 0;

    $set = array();
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select id from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census\n"
            . " where site = '$qs'";
        $set = find_many($sql, $db);
    }
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $hid = $row['id'];
            $num += hid_revision($hid, $now, $db);
        }
    }

    GCFG_DebugNote("$num machines at $site updated");
    return $num;
}




function full_revl($hid, $auth, $db)
{
    $row = array();
    if (($hid) && ($auth)) {
        $qa  = safe_addslashes($auth);
        $sql = "select C.site, C.host,\n"
            . " C.uuid, R.*, U.id as cid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where R.censusid = $hid\n"
            . " and C.id = R.censusid\n"
            . " and C.site = U.customer\n"
            . " and U.username = '$qa'";
        $row = find_one($sql, $db);
    }
    return $row;
}




function update_vmap($hid, $gid, $vid, $stat, $last, $source, $db)
{
    $num = 0;
    if (($hid) && ($gid) && ($vid)) {
        $sql = "select valmapid from " . $GLOBALS['PREFIX'] . "core.ValueMap left join " . $GLOBALS['PREFIX'] . "core.Census "
            . "on (" . $GLOBALS['PREFIX'] . "core.ValueMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq) left "
            . "join " . $GLOBALS['PREFIX'] . "core.Variables on (" . $GLOBALS['PREFIX'] . "core.ValueMap.varuniq="
            . $GLOBALS['PREFIX'] . "core.Variables.varuniq) "
            . "where id=$hid and varid=$vid";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigValueMap,
            "valmapid",
            "update_vmap",
            0,
            1,
            constOperationDelete,
            $db
        );

        if ($set) {

            if (!(GCFG_CheckVarValueExists($gid, $vid, $db))) {
                $valu = GCFG_GetDefaultValue($gid, $vid, '', 0, $db);

                $clientconf = 0;
                $sql = 'SELECT category FROM ' . $GLOBALS['PREFIX'] . 'core.MachineGroups LEFT JOIN '
                    . $GLOBALS['PREFIX'] . 'core.MachineCategories ON (' . $GLOBALS['PREFIX'] . 'core.MachineGroups.'
                    . 'mcatuniq=' . $GLOBALS['PREFIX'] . 'core.MachineCategories.mcatuniq) WHERE '
                    . "mgroupid=$gid";
                $row = find_one($sql, $db);
                if ($row) {
                    if ((strcmp($row['category'], 'Site') == 0) ||
                        (strcmp($row['category'], 'Machine') == 0)
                    ) {
                        $clientconf = 1;
                    }
                }
                $sql = GCFG_CreateVarValues(
                    $gid,
                    $vid,
                    $valu,
                    '',
                    2,
                    time(),
                    0,
                    $clientconf,
                    0,
                    $db
                );
                $res = redcommand($sql, $db);
                if (affected($res, $db)) {
                    $lastid = mysqli_insert_id($db);
                    $err = PHP_DSYN_InvalidateRow(
                        CUR,
                        (int)$lastid,
                        "valueid",
                        constDataSetGConfigVarValues,
                        constOperationInsert
                    );
                    if ($err != constAppNoErr) {
                    }
                } else {
                }
            }

            $sql = "update " . $GLOBALS['PREFIX'] . "core.ValueMap left join " . $GLOBALS['PREFIX'] . "core.Census "
                . "on (" . $GLOBALS['PREFIX'] . "core.ValueMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq)"
                . " left join " . $GLOBALS['PREFIX'] . "core.Variables on (" . $GLOBALS['PREFIX'] . "core.ValueMap.varuniq="
                . $GLOBALS['PREFIX'] . "core.Variables.varuniq) join " . $GLOBALS['PREFIX'] . "core.MachineGroups set"
                . "\n stat = $stat,\n"
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.oldmgroupuniq = " . $GLOBALS['PREFIX'] . "core.ValueMap.mgroupuniq"
                . ",\n " . $GLOBALS['PREFIX'] . "core.ValueMap.last = $last,\n"
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.oldvalu = '',\n"
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.expire = 0,\n"
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq"
                . ",\n srev = srev+1,\n"
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,\n"
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.source = $source, lastchange=UNIX_TIMESTAMP(),\n"
                . " revl = revl+1\n"
                . " where id = $hid\n"
                . " and varid = $vid\n"
                . " and mgroupid = $gid";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);

            DSYN_UpdateSet($set, constDataSetGConfigValueMap, "valmapid", $db);
        }
    }
    return $num;
}




function cat_legal($cat, &$row)
{
    if ($row['grpany']) {
        return true;
    } else {
        switch ($cat) {
            case constCatAll:
                return $row['grpall'];
            case constCatSite:
                return $row['grpsite'];
            case constCatMachine:
                return $row['grpmach'];
            case constCatUser:
                return $row['grpuser'];
            default:
                return $row['grpany'];
        }
    }
}


function GCFG_BuildGConfig($db)
{
    CBLD_BuildGConfig('core', $db);
}



function GCFG_FindSeveral($sql, $dbg, $db)
{
    $set = array();
    if ($dbg) {
        $set = find_many($sql, $db);
    } else {
        $res = mysqli_query($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $set[] = $row;
            }
            mysqli_free_result($res);
        }
    }
    return $set;
}


function GCFG_LegalName($name)
{
    if (!is_string($name)) {
        GCFG_DebugNote('not a string type');
        return false;
    }
    $len = strlen($name);
    if (($len < 1) || ($len > 50)) {
        GCFG_DebugNote("name length ($len) invalid.");
        return false;
    }
    $ch = $name[0];
    $good = ((('a' <= $ch) && ($ch <= 'z')) ||
        (('A' <= $ch) && ($ch <= 'Z')));
    if (!$good) {
        GCFG_DebugNote("$name does not begin correctly");
        return false;
    }
    for ($i = 0; $i < $len; $i++) {
        $ch = $name[$i];
        $good = ((('a' <= $ch) && ($ch <= 'z')) ||
            (('A' <= $ch) && ($ch <= 'Z')) ||
            (('0' <= $ch) && ($ch <= '9')) ||
            (($ch  == '_')));
        if (!$good) {
            GCFG_DebugNote("weird character $ch at position $i");
            return false;
        }
    }
    return true;
}


function GCFG_InsertTemp($name, $scop, $db)
{
    GCFG_DebugNote("temp: $name:$scop");
    $qn  = safe_addslashes($name);
    $sql = "insert into InvalidTemp set\n"
        . " scop = $scop,\n"
        . " name = '$qn'";
    mysqli_query($sql, $db);
}

function GCFG_DebugNote($msg)
{
    $show = @$GLOBALS['debug'];
    if (isset($show) && ($show)) {
        echo "<font color=\"green\">$msg</font><br>\n";
    }
}



function GCFG_SubLocal($hid, $gid, $host, $code, $valu, $name, $last, $source, $db)
{
    $var = find_var($name, $code, $db);
    if (($var) && ($gid) && ($hid)) {
        $vid = $var['varid'];
        $num = set_value($vid, $gid, $host, $valu, $source, $last, $db);
        $loc = constVarConfStateLocal;
        $lon = constVarConfStateLocalOnly;

        update_vmap($hid, $gid, $vid, $loc, $last, $source, $db);
    } else {
        $stat = "name:$name,g:$gid,c:$code,h:$hid";
        debug_note("sub local ($stat) failure");
    }
}



function GCFG_SetVariableValue(
    $valu,
    $varid,
    $itype,
    $mgroupid,
    $censusid,
    $source,
    $now,
    $db
) {
    $sql = "select valueid from " . $GLOBALS['PREFIX'] . "core.VarValues left join "
        . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq=" . $GLOBALS['PREFIX'] . "core."
        . "MachineGroups.mgroupuniq) left join " . $GLOBALS['PREFIX'] . "core.Variables on ("
        . $GLOBALS['PREFIX'] . "core.VarValues.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq)\n"
        . " where mgroupid in ($mgroupid)\n"
        . " and varid = $varid";
    $set = find_one($sql, $db);
    if (isset($set['valueid'])) {
        $valueid = $set['valueid'];
    } else {
        $err = "l-gcfg.php: valueid not set for mgroupid($mgroupid)"
            . " varid($varid) censusid($censusid)";
        return constSetVarValFailure;
    }


    if ($itype == constVblTypeSemaphore) {
        $valu = " valu = valu+1,\n";
    } else {
        $qValu = safe_addslashes($valu);
        $valu  = " valu = '$qValu',\n";
    }

    $set = mgrp_valu($mgroupid, $varid, $db);
    if ($set) {
        $oldvalu = $set[0]['valu'];
    } else {
        $oldvalu = "";
    }

    $err = PHP_DSYN_InvalidateRow(
        CUR,
        (int)$valueid,
        "valueid",
        constDataSetGConfigVarValues,
        constOperationDelete
    );
    if ($err != constAppNoErr) {

        return constSetVarValFailure;
    }
    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.VarValues left join "
        . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
        . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join "
        . $GLOBALS['PREFIX'] . "core.Variables on (" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq="
        . $GLOBALS['PREFIX'] . "core.Variables.varuniq) SET $valu revldef="
        . "def+revldef, def=0, revl=revl+1, lastchange=UNIX_TIMESTAMP() WHERE varid=" . $varid
        . " and mgroupid=" . $mgroupid;
    $res = redcommand($sql, $db);

    $err = PHP_DSYN_InvalidateRow(
        CUR,
        (int)$valueid,
        "valueid",
        constDataSetGConfigVarValues,
        constOperationInsert
    );
    if ($err != constAppNoErr) {

        return constSetVarValFailure;
    }

    GCFG_UpdateHistory($oldvalu, $mgroupid, $varid, $now, $source, $db);

    $time = time();
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Revisions\n"
        . " set stime = $time\n"
        . " where censusid IN (" . $censusid . ')';
    $res = command($sql, $db);

    $sql = "update " . $GLOBALS['PREFIX'] . "core.LegacyCache set\n"
        . " last = $time,\n"
        . " drty = 1\n"
        . " where censusid IN (" . $censusid . ')';
    $res = command($sql, $db);
}


function GCFG_FindValue(&$env, $code, $name, $db)
{
    $valu = '';
    $scop = $env['scop'];
    $row  = array();
    if (($scop == constScopHost) && ($env['revl'])) {
        $hid = $env['revl']['censusid'];
        $qn  = safe_addslashes($name);
        $sql = "select X.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where V.name = '$qn'\n"
            . " and V.scop = $code\n"
            . " and M.varuniq = V.varuniq\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and C.id = $hid\n"
            . " and M.mgroupuniq = X.mgroupuniq\n"
            . " and X.varuniq = V.varuniq";
        $row = find_one($sql, $db);
    }
    if (($scop == constScopSite) && ($env['site'])) {
        $qs  = safe_addslashes($env['site']['customer']);
        $qn  = safe_addslashes($name);
        $sql = "select X.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " where V.name = '$qn'\n"
            . " and V.scop = $code\n"
            . " and V.varuniq = X.varuniq\n"
            . " and G.name = '$qs'\n"
            . " and G.mgroupuniq = X.mgroupuniq\n"
            . " and X.varuniq = V.varuniq";
        $row = find_one($sql, $db);
    }
    if ($row) {
        $valu = $row['valu'];
    }
    return $valu;
}



function GCFG_GetVariableInfo(
    $site,
    $censusid,
    $varid,
    $mgroupid,
    $varname,
    $scop,
    $selSQL,
    $addSQL,
    $addGroupInfo,
    $dovmap,
    $domgmap,
    $db
) {
    $joinVarVersions = 0;

    $sql = "SELECT ";
    if ($selSQL) {
        $sql .= $selSQL;
    } else {
        $sql .= "VarValues.*, Variables.varid, MachineGroups.mgroupid";
    }

    if ($site || $censusid) {
        $sql .= ", VarVersions.defval FROM VarValues";
        $joinVarVersions = 1;
        $joinCensus = 0;
        if ($dovmap) {
            $sql .= " LEFT JOIN ValueMap ON ((VarValues.varuniq="
                . "ValueMap.varuniq) AND (VarValues.mgroupuniq="
                . "ValueMap.mgroupuniq))";
            $sql .= " LEFT JOIN Census ON (ValueMap.censusuniq="
                . "Census.censusuniq)";
            $joinCensus = 1;
        }
        if ($domgmap) {
            $sql .= " LEFT JOIN MachineGroupMap ON (VarValues.mgroupuniq="
                . "MachineGroupMap.mgroupuniq)";
            if (!($dovmap)) {
                $sql .= " LEFT JOIN Census ON (MachineGroupMap.censusuniq="
                    . "Census.censusuniq)";
            }
            $joinCensus = 1;
        }
        if (!($joinCensus)) {
        }
        $sql .= " LEFT JOIN Revisions ON (Census.id=Revisions.censusid)";
        $sql .= " LEFT JOIN VarVersions ON ((VarValues.varuniq="
            . "VarVersions.varuniq) AND (Revisions.vers="
            . "VarVersions.vers))";
    } else {
        $sql .= " FROM VarValues";
    }

    $sql .= " LEFT JOIN Variables ON (VarValues.varuniq="
        . "Variables.varuniq)";

    $sql .= " LEFT JOIN MachineGroups ON (VarValues.mgroupuniq="
        . "MachineGroups.mgroupuniq) LEFT JOIN MachineCategories ON ("
        . "MachineGroups.mcatuniq=MachineCategories.mcatuniq)";

    $addAND = 0;
    if ($site) {
        $sql .= " WHERE";
        $qsite = safe_addslashes($site);
        $sql .= " Census.site='" . $qsite . "'";
        $addAND = 1;
    }
    if ($censusid) {
        if ($addAND) {
            $sql .= " AND";
        } else {
            $sql .= " WHERE";
        }
        if ($dovmap) {
            $sql .= " ValueMap.censusuniq=Census.censusuniq";
        } else {
            $sql .= " MachineGroupMap.censusuniq=Census.censusuniq";
        }
        $addAND = 1;
        $sql .= " AND Census.id = " . $censusid;
    }
    if ($varid) {
        if ($addAND) {
            $sql .= " AND";
        } else {
            $sql .= " WHERE";
        }
        $sql .= " VarValues.varuniq=Variables.varuniq";
        $sql .= " AND Variables.varid=" . $varid;
        $addAND = 1;
    }
    if ($mgroupid) {
        if ($addAND) {
            $sql .= " AND";
        } else {
            $sql .= " WHERE";
        }
        $sql .= " VarValues.mgroupuniq = MachineGroups.mgroupuniq";
        $sql .= " AND MachineGroups.mgroupid=" . $mgroupid;
        $addAND = 1;
    }
    if ($varname) {
        if ($addAND) {
            $sql .= " AND";
        } else {
            $sql .= " WHERE";
        }
        $qname = safe_addslashes($varname);
        $sql .= " Variables.name='" . $qname . "'";
        $addAND = 1;
    }
    if ($scop) {
        if ($addAND) {
            $sql .= " AND";
        } else {
            $sql .= " WHERE";
        }
        $sql .= " Variables.scop=" . $scop;
        $addAND = 1;
    }

    if ($addSQL) {
        $sql .= " " . $addSQL;
    }

    debug_note($sql);

    $set = find_many($sql, $db);
    if ($set) {
        foreach ($set as $key => $row) {
            if ($row['def'] == 1) {
                if ($joinVarVersions) {

                    $set[$key]['valu'] = $set[$key]['defval'];
                } else {

                    $getvarid = 0;
                    if (!($varid)) {
                        if ($selSQL) {
                            $pos = strpos($selSQL, "Variables.varid");
                            if (!($pos === false)) {
                                $getvarid = $set[$key]['varid'];
                            }
                        } else {
                            $getvarid = $set[$key]['varid'];
                        }
                    } else {
                        $getvarid = $varid;
                    }

                    if ($mgroupid) {
                        $set[$key]['valu'] = GCFG_GetDefaultValue(
                            $mgroupid,
                            $getvarid,
                            $varname,
                            $scop,
                            $db
                        );
                    } else {
                        $set[$key]['valu'] = GCFG_GetDefaultValue(
                            $set[$key]['mgroupid'],
                            $getvarid,
                            $varname,
                            $scop,
                            $db
                        );
                    }
                }
            }
        }
    }

    return $set;
}



function GCFG_GetDefaultValue($mgroupid, $varid, $varname, $scop, $db)
{
    $sql = "SELECT DISTINCT Revisions.vers FROM Revisions LEFT JOIN "
        . "Census ON (Revisions.censusid=Census.id) LEFT JOIN "
        . " MachineGroupMap ON (Census.censusuniq="
        . " MachineGroupMap.censusuniq) LEFT JOIN MachineGroups ON ("
        . "MachineGroupMap.mgroupuniq=MachineGroups.mgroupuniq) WHERE "
        . "MachineGroups.mgroupid=" . $mgroupid . " ORDER BY "
        . "Revisions.vers DESC LIMIT 1";
    $row = find_one($sql, $db);
    if ($row) {

        $sql = "SELECT VarVersions.defval FROM VarVersions";
        $sql .= " LEFT JOIN Variables ON (VarVersions.varuniq="
            . "Variables.varuniq)";
        $sql .= " WHERE VarVersions.vers='" . $row['vers'] . "'";
        if ($varid) {
            $sql .= " AND Variables.varid=" . $varid;
        }
        if ($varname) {
            $qname = safe_addslashes($varname);
            $sql .= " AND Variables.name='" . $qname . "'";
        }
        if ($scop) {
            $sql .= " AND Variables.scop=" . $scop;
        }
        $row = find_one($sql, $db);
        if ($row) {
            return $row['defval'];
        } else {
        }
    } else {
    }
}



function GCFG_GetSimpleVariableInfo(
    $site,
    $censusid,
    $varid,
    $mgroupid,
    $varname,
    $scop,
    $db
) {
    return GCFG_GetVariableInfo(
        $site,
        $censusid,
        $varid,
        $mgroupid,
        $varname,
        $scop,
        '',
        '',
        0,
        0,
        0,
        $db
    );
}



function GCFG_CreateVarValues(
    $mgroupid,
    $varid,
    $valu,
    $host,
    $revl,
    $last,
    $revldef,
    $clientconf,
    $revlclientconf,
    $db
) {
    $sql = "select Variables.scop, Variables.name, varnameuniq, "
        . "varscopuniq, varuniq, mcatuniq, "
        . "mgroupuniq, " . $clientconf . ", " . $revlclientconf
        . " from " . $GLOBALS['PREFIX'] . "core.Variables, " . $GLOBALS['PREFIX'] . "core.MachineGroups where "
        . "mgroupid=$mgroupid and varid=$varid";
    $row = find_one($sql, $db);
    if ($row) {
        $qname = safe_addslashes($row['name']);
        $sql = "insert into " . $GLOBALS['PREFIX'] . "core.VarValues (valu,revl,revldef,last,host,"
            . "scop,name,varnameuniq,varscopuniq,varuniq,mcatuniq,"
            . "mgroupuniq, clientconf, revlclientconf,lastchange) VALUES ('" . $valu
            . "',$revl,$revldef,$last,'" . $host . "'," . $row['scop']
            . ",'" . $qname . "','" . $row['varnameuniq'] . "','"
            . $row['varscopuniq'] . "','" . $row['varuniq'] . "','"
            . $row['mcatuniq'] . "','" . $row['mgroupuniq']
            . "',$clientconf,$revlclientconf,UNIX_TIMESTAMP())";
        return $sql;
    } else {

        return "";
    }
}



function GCFG_CreateValueMap(
    $censusid,
    $mgroupid,
    $varid,
    $stat,
    $srev,
    $revl,
    $db
) {
    $sql = "select censusuniq, censussiteuniq, mgroupuniq, mcatuniq, "
        . "varuniq, varscopuniq, varnameuniq FROM Census, "
        . "MachineGroups, Variables WHERE id=$censusid AND "
        . "mgroupid=$mgroupid AND varid=$varid";
    $row = find_one($sql, $db);
    if ($row) {
        $sql = "insert into ValueMap (censusuniq, censussiteuniq, "
            . "mgroupuniq, mcatuniq, varuniq, varscopuniq, varnameuniq, "
            . "stat, srev, revl, lastchange) VALUES ('" . $row['censusuniq'] . "','"
            . $row['censussiteuniq'] . "','" . $row['mgroupuniq'] . "','"
            . $row['mcatuniq'] . "','" . $row['varuniq'] . "','"
            . $row['varscopuniq'] . "','" . $row['varnameuniq'] . "',$stat"
            . ",$srev, $revl, UNIX_TIMESTAMP())";
        return $sql;
    } else {

        return "";
    }
}



function GCFG_CreateScripValues($scrip, $censusid, $mgroupid, $scop, $db)
{
    debug_note("GCFG_CreateScripValues: scrip:$scrip censusid:$censusid "
        . "mgroupid:$mgroupid scop:$scop");
    if ($censusid) {
        $sql = "SELECT " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupid FROM "
            . $GLOBALS['PREFIX'] . "core.MachineGroups LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineCategories ON ("
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories."
            . "mcatuniq) LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap ON ("
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroupMap."
            . "mgroupuniq) LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Census ON (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap."
            . "censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq) WHERE " . $GLOBALS['PREFIX'] . "core.Census.id="
            . "$censusid AND " . $GLOBALS['PREFIX'] . "core.MachineCategories.category='Machine'";
        $row = find_one($sql, $db);
        if ($row) {
            $mgroupid = $row['mgroupid'];
        }
    }

    if ($mgroupid) {
        $sql = "SELECT " . $GLOBALS['PREFIX'] . "core.Variables.varuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups."
            . "mgroupuniq, " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq, " . $GLOBALS['PREFIX'] . "core.Variables."
            . "varscopuniq, " . $GLOBALS['PREFIX'] . "core.Variables.varnameuniq FROM " . $GLOBALS['PREFIX'] . "core.Variables "
            . "JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroups LEFT JOIN " . $GLOBALS['PREFIX'] . "core.VarValues ON "
            . "(" . $GLOBALS['PREFIX'] . "core.Variables.varuniq=" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq AND "
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq)"
            . " WHERE " . $GLOBALS['PREFIX'] . "core.Variables.scop=$scrip AND " . $GLOBALS['PREFIX'] . "core.MachineGroups."
            . "mgroupid=$mgroupid AND " . $GLOBALS['PREFIX'] . "core.VarValues.valueid IS NULL";
    }
    if (!$mgroupid) {
        debug_note("GCFG_CreateScripValues: invalid argument(s)");
        return;
    }
    $set = find_many($sql, $db);
    if ($set) {

        foreach ($set as $key => $row) {
            $clientconf = 0;
            if (($scop == constScopHost) || ($scop == constScopSite)) {
                $clientconf = 1;
            }
            $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.VarValues (mgroupuniq,mcatuniq,"
                . "varuniq,varscopuniq,varnameuniq,valu,revl,def,revldef,"
                . "clientconf,revlclientconf,seminit,lastchange) VALUES ('"
                . $row['mgroupuniq'] . "', '" . $row['mcatuniq'] . "', '"
                . $row['varuniq'] . "', '" . $row['varscopuniq'] . "', '"
                . $row['varnameuniq'] . "', '', 2, 1, 0, $clientconf, 0, "
                . "1, UNIX_TIMESTAMP())";
            $res = command($sql, $db);
            if (affected($res, $db)) {
                $lastid = mysqli_insert_id($db);
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$lastid,
                    "valueid",
                    constDataSetGConfigVarValues,
                    constOperationInsert
                );
                if ($err != constAppNoErr) {
                }
            }
        }
    }
}


function push_mgrp($mgrp, $glob, $now, $host, $scop, $valu, $name, $source, $db)
{
    $vid = 0;
    $gid = 0;
    $set = array();
    $var = find_var($name, $scop, $db);
    if (($var) && ($mgrp)) {
        $gid = $mgrp['mgroupid'];
        $vid = $var['varid'];
        $chg = update_value($vid, $gid, $now, $host, $valu, $source, $db);
    }
    if (($vid) && ($gid)) {
        $sql = "select R.censusid, H.site, H.host, V.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarVersions as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as A\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and V.varuniq = A.varuniq\n"
            . " and A.varid = $vid\n"
            . " and R.censusid = H.id\n"
            . " and H.censusuniq = M.censusuniq\n"
            . " and R.vers = V.vers\n"
            . " group by R.censusid\n"
            . " order by H.site, H.host";
        $set = find_many($sql, $db);
    }
    if (($set) && ($mgrp)) {
        $tmp  = find_settings($vid, $gid, $db);
        $cat  = $mgrp['category'];
        $stat = ($cat == constCatMachine) ? 1 : 0;
        $sss  = ($glob) ? 0 : 1;
        reset($set);
        reset($tmp);
        foreach ($set as $key => $row) {
            $hid  = $row['censusid'];
            $gset[$hid] = false;
        }
        foreach ($tmp as $key => $row) {
            $hid  = $row['censusid'];
            $gset[$hid] = true;
        }
        reset($set);
        foreach ($set as $key => $row) {
            $hid  = $row['censusid'];
            $site = $row['site'];
            $host = $row['host'];
            $good = cat_legal($cat, $row);
            if (!$good) {
                $good = $gset[$hid];
            }
            if ($good) {
                debug_note("$host at $site is new");
                $num = update_vmap($hid, $gid, $vid, $stat, $now, $source, $db);
            } else {
                if ($glob)
                    $grp = GCFG_find_site_mgrp($site, $db);
                else
                    $grp = GCFG_find_host_mgrp($hid, $site, $host, $db);
                if ($grp) {
                    $ggg = $grp['mgroupid'];
                    debug_note("$host at $site is old ($ggg)");
                    $xxx = update_value(
                        $vid,
                        $ggg,
                        $now,
                        $host,
                        $valu,
                        $source,
                        $db
                    );
                    $num = update_vmap(
                        $hid,
                        $ggg,
                        $vid,
                        $sss,
                        $now,
                        $source,
                        $db
                    );
                }
            }
        }
    }
}



function find_settings($vid, $gid, $db)
{
    $set = array();
    if (($vid) && ($gid)) {
        $sql = "select R.censusid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " GroupSettings as G,\n"
            . " Revisions as R,\n"
            . " VarVersions as V,\n"
            . " MachineGroups as E,\n"
            . " Census as F,\n"
            . " Variables as A\n"
            . " where G.mgroupuniq = E.mgroupuniq\n"
            . " and E.mgroupid = $gid\n"
            . " and V.varuniq = A.varuniq\n"
            . " and A.varid = $vid\n"
            . " and V.vers = R.vers\n"
            . " and M.censusuniq = F.censusuniq\n"
            . " and F.id = R.censusid\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.varversuniq = V.varversuniq\n"
            . " group by R.censusid";
        $set = find_many($sql, $db);
    }
    return $set;
}



function all_machines(&$env, $db)
{
    $user = constCatUser;
    $auth = $env['auth'];
    $admn = $env['admn'];
    $name = ($admn) ? constCatAll : "$user:$auth";
    $ugrp = find_mgrp_name($name, $db);
    $ugid = ($ugrp) ? $ugrp['mgroupid'] : 0;
    return find_mgrp_info($ugid, $db);
}

function GCFG_CreateVariables($env, $scrip, $db)
{
    $scop = $env['scop'];
    $censusid = 0;
    $mgroupid = 0;
    switch ($scop) {
        case constScopAll:
            $grp = all_machines($env, $db);
            $mgroupid = ($grp) ? $grp['mgroupid'] : 0;
            break;
        case constScopSite:
            if (@$env['sgid']) {
                $mgroupid = $env['sgid'];
            }
            break;
        case constScopHost:
            if (@$env['revl']) {
                $censusid = $env['revl']['censusid'];
            }
            break;
        case constScopGroup:
            if (@$env['mgrp']) {
                $mgroupid = $env['mgrp']['mgroupid'];
            } else {
                $mgroupid = $env['gid'];
            }
            break;
        case constScopUser:
        default:
            return constEnabNone;
            break;
    }

    GCFG_CreateScripValues($scrip, $censusid, $mgroupid, $scop, $db);
}



function GCFG_HandleValueMapAction(
    $action,
    $scope,
    $valmapid,
    $expire,
    $db
) {
    $sql = "UPDATE ValueMap LEFT JOIN MachineGroups ON (ValueMap."
        . "oldmgroupuniq=MachineGroups.mgroupuniq) SET lastchange=UNIX_TIMESTAMP(), ";
    $sql2 = "UPDATE VarValues LEFT JOIN ValueMap ON (VarValues.varuniq="
        . "ValueMap.varuniq AND VarValues.mgroupuniq=ValueMap.mgroupuniq) "
        . "SET lastchange=UNIX_TIMESTAMP(), ";
    $sql3 = "UPDATE ValueMap SET lastchange=UNIX_TIMESTAMP(), ";
    switch ($action) {
        case constAssignActionModifyExpire:
            $sql .= "expire=$expire";
            $sql3 .= "expire=$expire";
            break;
        case constAssignActionRevert:
        case constAssignActionDelete:
            $sql .= "ValueMap.mgroupuniq=oldmgroupuniq, ValueMap.revl="
                . "ValueMap.revl+1, "
                . "oldmgroupuniq='', ValueMap.mcatuniq=MachineGroups."
                . "mcatuniq";
            $sql2 .= "valu=oldvalu, VarValues.revl=VarValues.revl+1, "
                . "revldef=revldef+def, def=0";
            $sql3 .= "oldvalu=''";
            break;
    }

    $where = "";
    $where2 = "";
    $where3 = "";
    $where4 = "";
    switch ($scope) {
        case constAssignScopIndividual:
            $where .= " WHERE valmapid=$valmapid";
            $where2 .= " WHERE ValueMap.valmapid=$valmapid";
            $where3 .= " WHERE valmapid=$valmapid";
            $where4 .= " WHERE valmapid=$valmapid";
            break;
        case constAssignScopIndVar:
            $sql4 = "SELECT varuniq, last, mgroupuniq FROM ValueMap WHERE "
                . "valmapid=" . "$valmapid";
            $row = find_one($sql4, $db);
            if ($row) {
                $where .= " WHERE "
                    . "last=" . $row['last'] . " AND oldmgroupuniq!=''";
                $where2 .= " WHERE "
                    . "ValueMap.last=" . $row['last']
                    . " AND oldvalu!=''";
                $where3 .= " WHERE "
                    . "last=" . $row['last'] . " AND oldvalu!=''";
                $where4 .= " WHERE last=" . $row['last'];
            } else {

                return 0;
            }
            break;
        case constAssignScopVariable:
            $sql4 = "SELECT varuniq, mgroupuniq, last FROM ValueMap WHERE "
                . "valmapid=" . "$valmapid";
            $row = find_one($sql4, $db);
            if ($row) {
                $where .= " WHERE varuniq='" . $row['varuniq']
                    . "' AND ValueMap.mgroupuniq='"
                    . $row['mgroupuniq'] . "' AND oldmgroupuniq!=''";
                $where2 .= " WHERE ValueMap.varuniq='" . $row['varuniq']
                    . "' AND ValueMap.mgroupuniq='"
                    . $row['mgroupuniq'] . "' AND "
                    . "ValueMap.oldvalu!=''";
                $where3 .= " WHERE varuniq='" . $row['varuniq']
                    . "' AND mgroupuniq='"
                    . $row['mgroupuniq'] . "' AND oldvalu!=''";
                $where4 .= " WHERE last=" . $row['last'];
            } else {

                return 0;
            }
            break;
        case constAssignScopGroup:
            $sql4 = "SELECT mgroupuniq, last FROM ValueMap WHERE "
                . "valmapid=" . "$valmapid";
            $row = find_one($sql4, $db);
            if ($row) {
                $where .= " WHERE ValueMap.mgroupuniq='"
                    . $row['mgroupuniq'] . "' AND oldmgroupuniq!=''";
                $where2 .= " WHERE ValueMap.mgroupuniq='"
                    . $row['mgroupuniq'] . "' AND ValueMap.oldvalu!=''";
                $where3 .= " WHERE mgroupuniq='"
                    . $row['mgroupuniq'] . "' AND oldvalu!=''";
                $where4 .= " WHERE last=" . $row['last'];
            } else {

                return 0;
            }
            break;
        default:
            return 0;
            break;
    }

    $invSQL = "SELECT valmapid FROM ValueMap " . $where;

    $num = 0;
    $set = DSYN_DeleteSet(
        $invSQL,
        constDataSetGConfigValueMap,
        "valmapid",
        "GCFG_HandleValueMapAction",
        1,
        1,
        constOperationDelete,
        $db
    );
    if (!($set)) {
        return 0;
    }
    $invSQL = "SELECT DISTINCT valueid FROM VarValues LEFT JOIN ValueMap "
        . "ON (VarValues.varuniq=ValueMap.varuniq AND "
        . "VarValues.mgroupuniq=ValueMap.mgroupuniq) " . $where2;
    $set2 = DSYN_DeleteSet(
        $invSQL,
        constDataSetGConfigVarValues,
        "valueid",
        "GCFG_HandleValueMapAction",
        1,
        1,
        constOperationDelete,
        $db
    );
    if (!($set)) {
        return 0;
    }

    $sql .= $where;
    debug_note($sql);

    $res = redcommand($sql, $db);
    if ($res) {
        $num += affected($res, $db);
    }

    if ($action != constAssignActionModifyExpire) {
        $sql2 .= $where2;
        debug_note($sql2);
        $res = redcommand($sql2, $db);
    }

    $sql3 .= $where3;
    debug_note($sql3);
    $res = redcommand($sql3, $db);
    if ($res) {
        $num += affected($res, $db);
    }

    if ($action != constAssignActionModifyExpire) {
        $sql4 = "UPDATE ValueMap SET expire=0, last=0, oldmgroupuniq='', "
            . "oldvalu=''" . $where4;
        $res = redcommand($sql4, $db);
    }

    DSYN_UpdateSet($set, constDataSetGConfigValueMap, "valmapid", $db);
    DSYN_UpdateSet($set2, constDataSetGConfigVarValues, "valueid", $db);

    return $num;
}

function GCFG_UpdateHistory(
    $oldvalu,
    $mgroupid,
    $varid,
    $last,
    $source,
    $db
) {
    $sql = "SELECT mgroupuniq FROM MachineGroups WHERE mgroupid=$mgroupid";
    $row = find_one($sql, $db);
    if (!($row)) {
    }
    $mgroupuniq = $row['mgroupuniq'];
    $sql = "SELECT varuniq FROM Variables WHERE varid=$varid";
    $row = find_one($sql, $db);
    if (!($row)) {
    }
    $varuniq = $row['varuniq'];

    $qvalu = safe_addslashes($oldvalu);


    $sql = "UPDATE ValueMap SET oldmgroupuniq='', oldvalu='', last=0, "
        . "expire=0 WHERE mgroupuniq='" . $mgroupuniq . "' AND varuniq='"
        . $varuniq . "' AND last!=$last";
    $res = redcommand($sql, $db);


    $sql = "UPDATE ValueMap SET oldvalu='" . $qvalu . "', last=$last, "
        . "source=$source, expire=0 WHERE mgroupuniq='" . $mgroupuniq
        . "' AND varuniq='" . $varuniq . "' AND oldmgroupuniq=''";
    $res = redcommand($sql, $db);
}

function mgrp_revision($gid, $now, $db)
{
    $num = 0;
    $set = array();
    $ids = array();
    if (($gid) && ($now)) {
        $sql = "select R.censusid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and R.censusid = C.id\n"
            . " group by R.censusid\n"
            . " order by R.censusid";
        $set = find_many($sql, $db);
    }
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $ids[] = $row['censusid'];
        }
    }
    if (($ids) && ($now)) {
        $txt = join(',', $ids);
        $sql = "update Revisions set\n"
            . " stime = $now\n"
            . " where censusid in ($txt)";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}



function GCFG_CheckVarValueExists($mgroupid, $varid, $db)
{
    $sql = 'SELECT mgroupuniq FROM ' . $GLOBALS['PREFIX'] . 'core.MachineGroups WHERE mgroupid='
        . $mgroupid;
    $row = find_one($sql, $db);
    if (!($row)) {

        return false;
    }
    $mgroupuniq = $row['mgroupuniq'];
    $sql = "SELECT varuniq FROM " . $GLOBALS['PREFIX'] . "core.Variables WHERE varid=$varid";
    $row = find_one($sql, $db);
    if (!($row)) {

        return false;
    }
    $varuniq = $row['varuniq'];

    $sql = "SELECT valueid FROM " . $GLOBALS['PREFIX'] . "core.VarValues WHERE varuniq='$varuniq'"
        . " AND mgroupuniq='$mgroupuniq'";
    $row = find_one($sql, $db);
    if (!($row)) {
        return false;
    }
    return true;
}



function GCFG_GetMapDetails($mgroupuniq, $varid, $db)
{
    if ((!$mgroupuniq) || (!$varid)) {
        return array();
    }

    $var = array();
    $qvid = safe_addslashes($varid);
    $sql = 'SELECT varuniq FROM Variables WHERE varid=' . $qvid;
    $var = find_one($sql, $db);
    if (!$var) {
        return array();
    }

    $set = array();
    $sql = 'SELECT Census.censusuniq, site, host, vers FROM Census LEFT JOIN Revisions ON '
        . '(Census.id=Revisions.censusid) LEFT JOIN MachineGroupMap ON '
        . '(Census.censusuniq=MachineGroupMap.censusuniq) WHERE mgroupuniq=\''
        . $mgroupuniq . '\' ORDER BY site, host';
    $set = find_many($sql, $db);
    if (!$set) {
        return array();
    }

    $retArr = array();

    foreach ($set as $key => $row) {

        $valu = array();
        $source = '';

        $sql = "SELECT ValueMap.mgroupuniq, name FROM ValueMap LEFT JOIN MachineGroups ON ("
            . "ValueMap.mgroupuniq=MachineGroups.mgroupuniq) WHERE censusuniq='" . $row['censusuniq']
            . "' AND varuniq='" . $var['varuniq'] . "'";
        $map = find_one($sql, $db);
        if ($map) {
            $sql = "SELECT valu, def FROM VarValues WHERE varuniq='" . $var['varuniq']
                . "' AND mgroupuniq='" . $map['mgroupuniq'] . "'";
            $valu = find_one($sql, $db);
            $source = 'explicit map - group "' . $map['name'] . '"';
        }

        $maxPrecGroup = '';
        $maxPrec = -1;

        if (!$valu) {
            $sql = "SELECT MachineGroupMap.mgroupuniq, MachineGroups.name, valu, def, precedence, category "
                . "FROM MachineGroupMap LEFT JOIN MachineGroups ON ("
                . "MachineGroupMap.mgroupuniq=MachineGroups.mgroupuniq) LEFT JOIN MachineCategories "
                . "ON (MachineGroups.mcatuniq=MachineCategories.mcatuniq) LEFT JOIN VarValues ON ("
                . "MachineGroupMap.mgroupuniq=VarValues.mgroupuniq) WHERE censusuniq='"
                . $row['censusuniq'] . "' AND varuniq='" . $var['varuniq'] . "' ORDER BY precedence DESC";
            $groups = find_many($sql, $db);

            if (($groups) && (safe_count($groups) > 0)) {
                $maxPrecGroup = $groups[0]['mgroupuniq'];
                $maxPrec = $groups[0]['precedence'];
            }
        }

        $precDef = -1;
        $defCategory = '';
        $sql = "SELECT defmap, defval FROM VarVersions WHERE vers='"
            . safe_addslashes($row['vers']) . "' AND varuniq='" . $var['varuniq'] . "'";
        $vers = find_one($sql, $db);
        if ($vers) {
            switch ($vers['defmap']) {
                case constDefMapSite:
                    $precDef = GRPS_GetPrecedence('Site', $db);
                    $defCategory = 'Site';
                    break;
                case constDefMapMachine:
                    $precDef = GRPS_GetPrecedence('Machine', $db);
                    $defCategory = 'Machine';
                    break;
                case constDefMapUnknown:
                default:
                    break;
            }
        }

        if ($maxPrec > $precDef) {
            if (!$source) {
                $source = 'precedence - group "' . $groups[0]['name'] . '"';
                $valu = array();
                $valu['valu'] = $groups[0]['valu'];
                $valu['def'] = $groups[0]['def'];
            }
        }

        if ((!$source) && ($defCategory)) {
            foreach ($groups as $key => $group) {
                if ($group['category'] == $defCategory) {
                    $source = 'default map - group "' . $group['name'] . '"';
                    $valu = array();
                    $valu['valu'] = $group['valu'];
                    $valu['def'] = $group['def'];
                }
            }
        }

        if ((!$valu) || ($valu['def'] == 1)) {
            $valu['valu'] = $vers['defval'];
            $source .= ' - default';
        }

        $thisMachine = array();
        $thisMachine['name'] = $row['site'] . ':' . $row['host'];
        $thisMachine['value'] = $valu;
        $thisMachine['source'] = $source;

        $retArr[] = $thisMachine;
    }

    return $retArr;
}
