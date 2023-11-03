<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 9-May-05   EWB     Created
24-May-05   EWB     Moved type_name, etc here.
 5-Aug-05   EWB     cat_legal
10-Aug-05   EWB     update_vmap
12-Sep-05   BTE     Added checksum invalidation code.
20-Sep-05   BTE     Added proper table specification in update_value and
                    update_vmap.
12-Oct-05   BTE     Lots of changes to support gconfig tables in core.
13-Oct-05   BTE     Support converting siteman into gconfig tables in core.
24-Oct-05   BTE     Moved local versions of sub_local to GCFG_SubLocal,
                    properly handle ValueMap.revl.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
18-Nov-05   BJS     GCFG_SetVariableValue handles semaphores.
28-Nov-05   BJS     GCFG_SetVariableValue invalidates valueid, updates valu 
                    with mgroupid and varid.
08-Dec-05   BJS     Added constSetVarValFailure, error checking to
                    GCFG_SetVariableValue()
12-Dec-05   BJS     Fixed GCFG_SetVariableValue to handle itype.
13-Dec-05   BJS     find_host_mgrp -> GCFG_find_host_mgrp, find_site_mgrp ->
                    GCFG_find_site_mgrp().
15-Dec-05   BTE     Added find_value to GCFG_FindValue, removed unused
                    map_valu, added GCFG primitives to retrieve values (using
                    VarValues.def as necessary).
16-Dec-05   BTE     Various bugfixes and improvements for GCFG_GetVariableInfo.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.  Bug 2969: Remove the siteman to gconfig
                    conversion.
27-Jan-06   BTE     Never update the mgroupuniq field in a MachineGroups entry.
13-Mar-06   BTE     Bug 3199: Remove unused core database columns.
11-Apr-06   BTE     Return MachineCategories.mcatid when needed.
24-Apr-06   BTE     Bugs 2963 and 2974.
27-Apr-06   BTE     Bug 3292: Add group assignment reset function.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
02-May-06   BTE     Several bugfixes in GCFG_HandleValueMapAction.
03-May-06   BTE     Bug 3362: Do general testing and bugfixing for Scrip config
                    status page.
19-May-06   BTE     Fixed improper invalidation in update_vmap (part of bug
                    3385).  Bug 3244: 439 errors in PHP log.
23-May-06   BTE     Bug 3290: Config Module: Client syncing with server caused
                    variable changes.
26-May-06   BTE     Bug 3293: Allow name changes for groups.
24-Jun-06   BTE     Bug 3500: Config Wizards fail for 2.1 client.
24-Jun-06   BTE     Moved mgrp_revision here from config/remote.php.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
16-Nov-06   BTE     Bug 3858: Remote control wizard does not work with 2.4
                    client and 4.3 server.
24-May-07   BTE     Bug 4046: Remote control: fails to create Scrip 245
                    variable values.
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.

*/


define('constSetVarValFailure', -1);

/* Constants for GCFG_HandleValueMapAction "action" */
define('constAssignActionModifyExpire', 1);
define('constAssignActionRevert',       2);
define('constAssignActionDelete',       3);

/* Constants for GCFG_HandleValueMapAction "scope" */
define('constAssignScopIndividual', 1);
define('constAssignScopIndVar',     2);
define('constAssignScopVariable',   3);
define('constAssignScopGroup',      4);

/*
    |  Here's the secret formula for creating the scrip name,
    |  as it is displayed, from the real name.
    */

function scrip_name($num, $name)
{
    return "Scrip $num - $name";
}


/*
    |  How to recognize a state variable.
    |  We want this to be known in just one place.
    */

function state_var($arg)
{
    return $arg . '_ConfState';
}


/*
    |   I want the state names to be known in
    |   only one place, and that is here.
    */

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


/*
    |   Same thing for debug tables ...
    */

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

/*
    |  Name for password confirmation.
    */

function confirm_var($arg)
{
    return $arg . '_confirmation';
}


function normalize($txt)
{
    return str_replace("\r\n", "\n", $txt);
}


/*
    |  For debug tables, text value of type.
    */

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

/*
    |  Returns the machine group for the specified
    |  site, or an empty array if not found.
    */

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


/*
    |  Returns variable description record for the
    |  specified variable.
    */

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


/*
    |  Returns the specified machine group,
    |  or an empty array if not found.
    */

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


/*
    |  This returns the revision record for
    |  a single machine in the machine group
    |  which has the latest client version
    |  and who has logged the most recently.
    */

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

/*
    |  Returns the specified machine group,
    |  or an empty array if not found.
    */

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


/*
    |  Returns the machine group for the specified site,
    |  or an empty array if not found.
    */

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


/*
    |  Returns the machine group for the specified
    |  machine, or an empty array if not found.
    */

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

    /*
        |  The automatic build process does not handle renaming a machine
        |  group if the machine decides to change it's name ... the
        |  problem is that on a large server, detecting this kind of
        |  name change would be very expensive.
        |
        |  So, instead of that, we'll just take care of the problem here.
        |  Note that this will work even if the machine has moved to
        |  a different site.
        */

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
                    logs::log(__FILE__, __LINE__, "GCFG_find_host_mgrp: PHP_DSYN_InvalidateRow"
                        . "returned " . $err, 0);
                }
            }

            if ($err == constAppNoErr) {
                $sql = "update " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
                    . " set name = '$qn',\n"
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


/*
    |  Returns the census record for the specified
    |  machine, or an empty array if not found.
    */

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


/*
    |  Returns the machine group for the specified
    |  machine, or an empty array if not found.
    */

function find_hgrp($site, $host, $db)
{
    $row = find_census_name($site, $host, $db);
    $hid = ($row) ? $row['id'] : 0;
    return GCFG_find_host_mgrp($hid, $site, $host, $db);
}


/*
    |  Value of variable $vid in machine group $gid.
    */

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


/*
    |  Value of variable $name, $scop in machine group $gid.
    */

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


/*
    |  Update the value of variable $vid in machine group $gid.
    |  Returns the number of records updated, which might be
    |  zero of the record does not exist, or the value is
    |  unchanged.
    */

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
            . " revldef = revldef+def,\n"
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


/*
    |  updates revision table for a single machine.
    */

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

/*
    |  updates revision table for a single machine.
    */

function host_revision($site, $host, $now, $db)
{
    $row = find_census_name($site, $host, $db);
    $hid = ($row) ? $row['id'] : 0;
    return hid_revision($hid, $now, $db);
}


/*
    |  Updates revision table for an entire site.
    |
    |  Note that the multi-table update is available
    |  in mysql 4.0.4 or better ... at some point we
    |  should drop support for mysql 3.
    */

function site_revision($site, $now, $db)
{
    $num = 0;
    /* --------------------------------- */
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
    /* ---------------------------------
        if ($site)
        {
            $qs  = safe_addslashes($site);
            $sql = "update\n"
                 . " core.Revisions as R,\n"
                 . " core.Census as C\n"
                 . " set R.stime = $now\n"
                 . " where R.censusid = C.id\n"
                 . " and C.site = '$qs'";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
   --------------------------------- */
    GCFG_DebugNote("$num machines at $site updated");
    return $num;
}


/*
    |  The revisions record, plus associated
    |  census information as well.
    */

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


/*
    |  Updates a single vmap record, returns
    |  number of records changed.
    */

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
            /* Create the variable if it does not exist first */
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
                    $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                    $err = PHP_DSYN_InvalidateRow(
                        CUR,
                        (int)$lastid,
                        "valueid",
                        constDataSetGConfigVarValues,
                        constOperationInsert
                    );
                    if ($err != constAppNoErr) {
                        logs::log(__FILE__, __LINE__, "update_vmap: PHP_DSYN_InvalidateRow "
                            . "returned " . $err, 0);
                    }
                    logs::log(__FILE__, __LINE__, 'update_vmap: created missing variable '
                        . "$vid for machine group $gid", 0);
                } else {
                    logs::log(__FILE__, __LINE__, 'update_vmap: failed to create variable', 0);
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
                . " " . $GLOBALS['PREFIX'] . "core.ValueMap.source = $source,\n"
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


/*
    |  Look at the client flag bits (VarVersions)
    |  to see if this category is legal to use
    |  for a valuemap record or not.
    */

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

/*
    |  This is a non-debug version of find_many
    */

function GCFG_FindSeveral($sql, $dbg, $db)
{
    $set = array();
    if ($dbg) {
        $set = find_many($sql, $db);
    } else {
        $res = mysqli_query($db, $sql);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $set[] = $row;
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
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
    mysqli_query($db, $sql);
}

function GCFG_DebugNote($msg)
{
    $show = @$GLOBALS['debug'];
    if (isset($show) && ($show)) {
        echo "<font color=\"green\">$msg</font><br>\n";
    }
}


/*
    |  multi-table update requires mysql 4.0.4
*/
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


/* GCFG_SetVariableValue

        Sets the current value of the variable $name in $scop that applies
        to the machine $uuid to the value $valu.
    */
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
    // get the valueid for PHP_DSYN_InvalidateRow
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
        logs::log(__FILE__, __LINE__, $err, 0);
        return constSetVarValFailure;
    }

    /* If we are updating a semaphore we do so by incrementing valu */
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
        logs::log(__FILE__, __LINE__, "GCFG_SetVariableValue: PHP_DSYN_InvalidateRow "
            . "returned " . $err, 0);
        return constSetVarValFailure;
    }
    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.VarValues left join "
        . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
        . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) left join "
        . $GLOBALS['PREFIX'] . "core.Variables on (" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq="
        . $GLOBALS['PREFIX'] . "core.Variables.varuniq) SET $valu revldef="
        . "def+revldef, def=0, revl=revl+1 WHERE varid=" . $varid
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
        logs::log(__FILE__, __LINE__, "GCFG_SetVariableValue: PHP_DSYN_InvalidateRow2 "
            . "returned " . $err, 0);
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


/* GCFG_GetVariableInfo

        Returns a row (or several rows) in the format VarValues.* based on the
        conditions passed in.  Always assumes that at least one condition is
        present.  Automatically handles the default value in such a way that
        the caller can use ['valu'] to see the value of the variable.

        Specify data to return in $selSQL.  This is a list of table.column
        pairs that will be added to the SELECT portion.  Please
        ensure that these pairs return names that will be unique, use AS if
        necessary.  This is an optional parameter, by default the data returned
        will be VarValues.*.  If you specify a custom selection, you must
include_once VarValues.valu and VarValues.def.  Never include
        VarVersions.defval, this will be handled internally.

        Specify additional group by/order by information in $addSQL.

        If $addGroupInfo is TRUE, then both the MachineGroup and
        MachineCategory tables will be joined and their columns will be
        available for use in $selSQL.

        If $dovmap is TRUE, then if the ValueMap table is joined the rows will
        restricted based on $censusid and $mgroupid (if either are provided).

        If $domgmap is TRUE, then if the MachineGroupMap table is joined the
        rows will be restricted based on $censusid and $mgroupid (if either are
        provided).
    */
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
    /* Construct a SQL statement based on the parameters. */
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
            logs::log(__FILE__, __LINE__, "GCFG_GetVariableInfo: census data was specified but"
                . " all potential joins were disallowed!", 0);
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
                    /* This is very simple, just replace valu with defval
                        */
                    $set[$key]['valu'] = $set[$key]['defval'];
                } else {
                    /* First, figure out which variable we want */
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
                    /* This is a little harder, we need to find the
                            "default" value for a variable that is not tied to
                            a single census record. */
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


/* GCFG_GetDefaultValue

        Gets the "default" value for a variable within a machine group using
        either a variable identifier $varid or $varname combined with $scop.
        The default value for a variable that cannot be tied directly to the
        VarVersions table is equal to the highest version of the client who
        is a member of the group $mgroupid.
    */
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
        /* We have a version, select the applicable default value. */
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
            logs::log(__FILE__, __LINE__, "GCFG_GetDefaultValue: version has no data, SQL="
                . $sql, 0);
        }
    } else {
        logs::log(__FILE__, __LINE__, "GCFG_GetDefaultValue: no configuration data, SQL="
            . $sql, 0);
    }
}


/* GCFG_GetSimpleVariableInfo

        A simplified wrapper for GCFG_GetVariableInfo.  See the comment for
        GCFG_GetVariableInfo for details.
    */
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

function GCFG_CreateSemClears(
    $censusid,
    $mgroupid,
    $varid,
    $val,
    $revl,
    $last
) {
    return "insert into " . $GLOBALS['PREFIX'] . "core.SemClears (censusuniq, censussiteuniq, "
        . "mgroupuniq, mcatuniq, varuniq, varscopuniq, varnameuniq, "
        . "valu, revl, last) select censusuniq, "
        . "censussiteuniq, mgroupuniq, mcatuniq, varuniq, "
        . "varscopuniq, varnameuniq, $val, $revl, $last from " . $GLOBALS['PREFIX'] . "core.Census, "
        . $GLOBALS['PREFIX'] . "core.MachineGroups, " . $GLOBALS['PREFIX'] . "core.Variables where id=$censusid "
        . "and mgroupid=$mgroupid and varid=$varid";
}


/* GCFG_CreateVarValues

        Returns a SQL statement for adding a new row into ".$GLOBALS['PREFIX']."core.VarValues.
    */
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
            . "mgroupuniq, clientconf, revlclientconf) VALUES ('" . $valu
            . "',$revl,$revldef,$last,'" . $host . "'," . $row['scop']
            . ",'" . $qname . "','" . $row['varnameuniq'] . "','"
            . $row['varscopuniq'] . "','" . $row['varuniq'] . "','"
            . $row['mcatuniq'] . "','" . $row['mgroupuniq']
            . "',$clientconf,$revlclientconf)";
        return $sql;
    } else {
        logs::log(__FILE__, __LINE__, "GCFG_CreateVarValues: failed to locate mgroupid "
            . "$mgroupid and varid $varid", 0);
        return "";
    }
}


/* GCFG_CreateValueMap

        Returns a SQL statement for adding a new row into ".$GLOBALS['PREFIX']."core.ValueMap.
    */
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
            . "stat, srev, revl) VALUES ('" . $row['censusuniq'] . "','"
            . $row['censussiteuniq'] . "','" . $row['mgroupuniq'] . "','"
            . $row['mcatuniq'] . "','" . $row['varuniq'] . "','"
            . $row['varscopuniq'] . "','" . $row['varnameuniq'] . "',$stat"
            . ",$srev, $revl)";
        return $sql;
    } else {
        logs::log(__FILE__, __LINE__, "GCFG_CreateValueMap: failed to find censusid $censusid"
            . " mgroupid $mgroupid and varid $varid", 0);
        return "";
    }
}


/* GCFG_CreateScripValues

        Creates default values for all variables in the Scrip $scrip for the
        local machine group if $censusid is non-zero and/or for the machine
        group $mgroupid if $mgroupid is non-zero.
    */
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
        /* Each variable in $set must be added to the group. */
        foreach ($set as $key => $row) {
            $clientconf = 0;
            if (($scop == constScopHost) || ($scop == constScopSite)) {
                $clientconf = 1;
            }
            $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.VarValues (mgroupuniq,mcatuniq,"
                . "varuniq,varscopuniq,varnameuniq,valu,revl,def,revldef,"
                . "clientconf,revlclientconf,seminit) VALUES ('"
                . $row['mgroupuniq'] . "', '" . $row['mcatuniq'] . "', '"
                . $row['varuniq'] . "', '" . $row['varscopuniq'] . "', '"
                . $row['varnameuniq'] . "', '', 2, 1, 0, $clientconf, 0, "
                . "1)";
            $res = command($sql, $db);
            if (affected($res, $db)) {
                $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$lastid,
                    "valueid",
                    constDataSetGConfigVarValues,
                    constOperationInsert
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "GCFG_CreateScripValues: "
                        . "PHP_DSYN_InvalidateRow returned " . $err, 0);
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

/*
    |  Returns a list of machines which
    |  have group settings for group $gid
    |  on variable $vid
    */

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

/*
    |  appropriate "all machines" group.
    */

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


/* GCFG_HandleValueMapAction

        Can modify an expiration date or revert an assignment given the
        appropriate $action and a $scope for that action.  Pass in the relevant
        ValueMap.valmapid in $valmapid and, if applicable, an $expire date as
        a future POSIX date.
    */
function GCFG_HandleValueMapAction(
    $action,
    $scope,
    $valmapid,
    $expire,
    $db
) {
    $sql = "UPDATE ValueMap LEFT JOIN MachineGroups ON (ValueMap."
        . "oldmgroupuniq=MachineGroups.mgroupuniq) SET ";
    $sql2 = "UPDATE VarValues LEFT JOIN ValueMap ON (VarValues.varuniq="
        . "ValueMap.varuniq AND VarValues.mgroupuniq=ValueMap.mgroupuniq) "
        . "SET ";
    $sql3 = "UPDATE ValueMap SET ";
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
                logs::log(__FILE__, __LINE__, "GCFG_HandleValueMapAction: failed to find "
                    . "$valmapid", 0);
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
                logs::log(__FILE__, __LINE__, "GCFG_HandleValueMapAction: failed to find "
                    . "$valmapid", 0);
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
                logs::log(__FILE__, __LINE__, "GCFG_HandleValueMapAction: failed to find "
                    . "$valmapid", 0);
                return 0;
            }
            break;
        default:
            logs::log(__FILE__, __LINE__, "GCFG_HandleValueMapAction: unknown scope $scope", 0);
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
        logs::log(
            __FILE__,
            __LINE__,
            "GCFG_UpdateHistory: failed to find mgroupid $mgroupid",
            0
        );
    }
    $mgroupuniq = $row['mgroupuniq'];
    $sql = "SELECT varuniq FROM Variables WHERE varid=$varid";
    $row = find_one($sql, $db);
    if (!($row)) {
        logs::log(__FILE__, __LINE__, "GCFG_UpdateHistory: failed to find varid $varid", 0);
    }
    $varuniq = $row['varuniq'];

    $qvalu = safe_addslashes($oldvalu);

    /* This is a little complicated - if the configuration change includes
            both assignment and attachment changes make sure they are grouped
            together - but don't keep old information from a prior
            configuration change.  So, first, we update ValueMap and eliminate
            any old information from prior changes. */
    $sql = "UPDATE ValueMap SET oldmgroupuniq='', oldvalu='', last=0, "
        . "expire=0 WHERE mgroupuniq='" . $mgroupuniq . "' AND varuniq='"
        . $varuniq . "' AND last!=$last";
    $res = redcommand($sql, $db);

    /* Prevent recording the oldvalu IF there was an assignment change
            to prevent ambiguity */
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


/* GCFG_CheckVarValueExists

        Checks if a value exists for the group $mgroupid for the variable
        $varid.
    */
function GCFG_CheckVarValueExists($mgroupid, $varid, $db)
{
    $sql = 'SELECT mgroupuniq FROM ' . $GLOBALS['PREFIX'] . 'core.MachineGroups WHERE mgroupid='
        . $mgroupid;
    $row = find_one($sql, $db);
    if (!($row)) {
        logs::log(__FILE__, __LINE__, 'GCFG_CheckVarValueExists: failed to find machine group '
            . $mgroupid, 0);
        return false;
    }
    $mgroupuniq = $row['mgroupuniq'];
    $sql = "SELECT varuniq FROM " . $GLOBALS['PREFIX'] . "core.Variables WHERE varid=$varid";
    $row = find_one($sql, $db);
    if (!($row)) {
        logs::log(__FILE__, __LINE__, 'GCFG_CheckVarValueExists: failed to find variable '
            . $varid, 0);
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
