<?php






function CWIZ_find_site($cid, $auth, $db)
{
    $row = array();
    if (($cid) && ($auth)) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where id = $cid\n"
            . " and username = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}




function host_value($site, $host, $code, $name, $db)
{
    $qh  = safe_addslashes($host);
    $qs  = safe_addslashes($site);
    $sql = "SELECT Census.id FROM Census WHERE Census.host='" . $qh . "'"
        . " AND Census.site='" . $qs . "'";
    $row = find_one($sql, $db);
    if ($row) {
        $row = GCFG_GetVariableInfo(
            $site,
            $row['id'],
            0,
            0,
            $name,
            $code,
            '',
            '',
            0,
            1,
            0,
            $db
        );
        return ($row) ? $row[0]['valu'] : '';
    }

    return '';
}




function site_value($site, $code, $name, $db)
{
    $grp = find_site_mgrp($site, $db);
    $var = find_var($name, $code, $db);
    $gid = ($grp) ? $grp['mgroupid'] : 0;
    $vid = ($var) ? $var['varid'] : 0;
    return mgrp_valu($gid, $vid, $db);
}





function clear_site_over($site, $code, $last, $source, $db)
{
    $num = 0;
    $set = array();
    $grp = find_site_mgrp($site, $db);

    if ($grp) {
        $qs  = safe_addslashes($site);
        $gid = $grp['mgroupid'];
        $lon = constVarConfStateLocalOnly;
        $sql = "select C.id, V.varid from\n"
            . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
            . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " where C.site = '$qs'\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and V.scop = $code\n"
            . " and M.varuniq = V.varuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid != $gid\n"
            . " and M.stat != $lon";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $gbl = constVarConfStateGlobal;

        reset($set);
        foreach ($set as $key => $row) {
            $vid = $row['varid'];
            $hid = $row['id'];

            $num += update_vmap(
                $hid,
                $gid,
                $vid,
                $gbl,
                $last,
                $source,
                $db
            );
        }
    }
    debug_note("$num local overrides removed");
    return $num;
}

function find_revl_mgrp($revl, $db)
{
    $mgrp = array();
    if ($revl) {
        $site = @$revl['site'] ? $revl['site'] : 0;
        $host = @$revl['host'] ? $revl['host'] : 0;
        $hid  = @$revl['censusid'] ? $revl['censusid'] : 0;
        $mgrp = GCFG_find_host_mgrp($hid, $site, $host, $db);
    }
    return $mgrp;
}

function CWIZ_create_temp_table($name, $db)
{
    $sql = "create temporary table $name\n"
        . " (id varchar(32) BINARY not null primary key)";
    redcommand($sql, $db);
}

function CWIZ_drop_temp_table($name, $db)
{
    $sql = "drop temporary table if exists $name";
    redcommand($sql, $db);
}

function debug_temp($name, $db)
{
    $lst = array();
    $sql = "select id from $name order by id";
    $tmp = find_many($sql, $db);
    reset($tmp);
    foreach ($tmp as $key => $row) {
        $lst[] = $row['id'];
    }
    $num = safe_count($lst);
    $txt = implode(',', $lst);
    debug_note("$name: $num ($txt)");
}




function build_config_table($name, $db)
{
    if ($name) {
        CWIZ_create_temp_table($name, $db);
        $sql = "insert into $name\n"
            . " select G.mgroupuniq from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " group by G.mgroupuniq\n"
            . " order by G.mgroupuniq";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        debug_temp($name, $db);
    }
}




function build_unconfig_table($name, $db)
{
    if ($name) {
        CWIZ_create_temp_table($name, $db);
        $sql = "insert into $name\n"
            . " select G.mgroupuniq from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.VarValues as V\n"
            . " on G.mgroupuniq = V.mgroupuniq\n"
            . " where V.mgroupuniq is NULL\n"
            . " group by G.mgroupuniq\n"
            . " order by G.mgroupuniq";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        debug_temp($name, $db);
    }
}




function build_alien_table($name, $auth, $db)
{
    if (($name) && ($auth)) {
        CWIZ_create_temp_table($name, $db);
        $qa  = safe_addslashes($auth);
        $sql = "insert into $name\n"
            . " select H.censusuniq from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
            . " on U.username = '$qa'\n"
            . " and U.customer = H.site\n"
            . " where U.id is NULL\n"
            . " group by H.censusuniq\n"
            . " order by H.censusuniq";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        debug_temp($name, $db);
    }
}



function build_access_table($name, $auth, $db)
{
    if (($name) && ($auth)) {
        CWIZ_create_temp_table($name, $db);
        $qa  = safe_addslashes($auth);
        $sql = "insert into $name\n"
            . " select G.mgroupuniq from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
            . " where U.username = '$qa'\n"
            . " and U.customer = H.site\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and M.censusuniq = H.censusuniq\n"
            . " group by G.mgroupuniq\n"
            . " order by G.mgroupuniq";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        debug_temp($name, $db);
    }
}


function CWIZ_ChooseScop(&$env, $txt, $db)
{
    $scop = $env['scop'];

    $s1 = radio('scop', constScopAll, $scop);
    $s2 = radio('scop', constScopSite, $scop);
    $s3 = radio('scop', constScopHost, $scop);
    $s4 = radio('scop', constScopGroup, $scop);

    echo post_self('myform');
    echo hidden('act', 'scop');
    echo hidden('pcn', 'cwiz');
    echo <<< BLAH

        <p>
          $txt
        </p>

        <p>
          $s1 All sites<br>
          $s2 A single site<br>
          $s3 A single machine<br>
          $s4 A group of machines<br>
        </p>

BLAH;
    echo next_only();
    echo form_footer();
}


function CWIZ_MapScop(
    $scop,
    $revl,
    $meth,
    $site,
    &$tid,
    $mgrp,
    $isRemote,
    $db
) {
    $map = '';
    switch ($scop) {
        case constScopHost:
            if ($revl) {
                if ($isRemote) {
                    $map = meth_action($meth);
                } else {
                    $map = enab_action($meth);
                }
            } else {
                $map = ($site) ? 'chst' : 'csit';
            }
            break;
        case constScopGroup:
            if ($mgrp) {
                if ($isRemote) {
                    $map = meth_action($meth);
                } else {
                    $map = enab_action($meth);
                }
            } else {
                if (!($tid)) {

                    $sql = "SELECT mcatid FROM " . $GLOBALS['PREFIX'] . "core.MachineCategories WHERE "
                        . "category='" . constMcSCOP . "'";
                    $row = find_one($sql, $db);
                    if ($row) {
                        $tid = $row['mcatid'];
                    }
                }

                $map = ($tid) ? 'cgid' : 'ctid';
            }
            break;
        case constScopSite:
            if ($site) {
                if ($isRemote) {
                    $map = meth_action($meth);
                } else {
                    $map = enab_action($meth);
                }
            } else {
                $map = 'csit';
            }
            break;
        case constScopAll:
            if ($isRemote) {
                $map = meth_action($meth);
            } else {
                $map = enab_action($meth);
            }
            break;
    }
    return $map;
}




function CWIZ_ChooseCats(&$env, $msg, $db)
{
    debug_note('choose_cats');
    $auth = $env['auth'];
    $tid  = $env['tid'];
    $gcfg = 'GroupConfig';
    $alen = 'GroupAliens';
    build_config_table($gcfg, $db);
    build_alien_table($alen, $auth, $db);
    $sql = "SELECT " . $GLOBALS['PREFIX'] . "core.MachineCategories.* FROM " . $GLOBALS['PREFIX'] . "core.MachineCategories "
        . "LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroups ON (" . $GLOBALS['PREFIX'] . "core.MachineCategories."
        . "mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Machine"
        . "GroupMap ON (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroupMap"
        . ".mgroupuniq) LEFT JOIN $gcfg ON (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq="
        . "$gcfg.id) LEFT JOIN $alen ON (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
        . "$alen.id) WHERE " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgmapid IS "
        . "NULL GROUP BY " . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatid ORDER BY "
        . $GLOBALS['PREFIX'] . "core.MachineCategories.precedence";
    $set = find_many($sql, $db);
    drop_temp_table($gcfg, $db);
    drop_temp_table($alen, $db);

    $num = safe_count($set);
    debug_note("final set $num");
    if ($set) {
        $in = indent(5);
        echo post_self('myform');
        echo hidden('act', 'scop');
        echo hidden('pcn', 'scop');
        echo hidden('scop', $env['scop']);
        echo para($msg);
        reset($set);
        foreach ($set as $key => $row) {
            $id  = $row['mcatid'];
            $cat = $row['category'];
            $rad = radio('tid', $id, $tid);
            echo "${in}$rad $cat<br>\n";
        }
        echo next_cancel();
        echo form_footer();
    } else {
        echo para('No appropriate categories ...');
    }
}




function CWIZ_ChooseGrps(&$env, $msg, $db)
{
    $set  = array();
    $tid  = $env['tid'];
    $gid  = $env['gid'];
    $auth = $env['auth'];
    if (($tid) && ($auth)) {
        $gcfg = 'GroupConfig';
        $alen = 'GroupAliens';
        $excludeGroups = 'ExcludedGroups';
        build_config_table($gcfg, $db);
        build_alien_table($alen, $auth, $db);

        CWIZ_create_temp_table($excludeGroups, $db);

        $sql = "INSERT INTO $excludeGroups SELECT " . $GLOBALS['PREFIX'] . "core.MachineGroups."
            . "mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups LEFT JOIN " . $GLOBALS['PREFIX'] . "core."
            . "MachineGroupMap ON (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq) LEFT JOIN $alen ON ("
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq=$alen.id) WHERE $alen.id"
            . " IS NOT NULL";
        redcommand($sql, $db);
        $sql = "select G.mgroupid, G.name from\n"
            . " (" . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " $gcfg as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as E)\n"
            . " left join $excludeGroups as A on(\n"
            . " G.mgroupuniq = A.id)\n"
            . " where G.mcatuniq = E.mcatuniq\n"
            . " and E.mcatid = $tid\n"
            . " and G.mgroupuniq = C.id\n"
            . " and A.id is NULL\n";
        if (!($env['admn'])) {

            $sql .= " and (G.username='$auth' OR G.global=1)\n";
        }
        $sql .= " group by G.mgroupid\n"
            . " order by G.name";
        $set = find_many($sql, $db);
        drop_temp_table($gcfg, $db);
        drop_temp_table($alen, $db);
        drop_temp_table($excludeGroups, $db);
    }
    if ($set) {
        $in = indent(5);
        echo post_self('myform');
        echo hidden('act', 'scop');
        echo hidden('pcn', 'scop');
        echo hidden('tid', $tid);
        echo hidden('scop', $env['scop']);
        echo para($msg);

        if (!$gid) {
            reset($set);
            $row = current($set);
            $gid = $row['mgroupid'];
        }

        reset($set);
        foreach ($set as $key => $row) {
            $id   = $row['mgroupid'];
            $name = $row['name'];
            $rad  = radio('gid', $id, $gid);
            echo "${in}$rad $name<br>\n";
        }
        echo next_cancel();
        echo form_footer();
    } else {
        echo para('No appropriate groups');
    }
}


function CWIZ_ChooseEnab(&$env, $msg, $db)
{
    $xx = $env['stat'];
    $m1 = radio('enab', constEnabNo, $xx);
    $m2 = radio('enab', constEnabYes, $xx);
    $in = indent(5);
    echo post_self('myform');
    echo hidden('act', 'scop');
    echo hidden('cid', $env['cid']);
    echo hidden('hid', $env['hid']);
    echo hidden('gid', $env['gid']);
    echo hidden('tid', $env['tid']);
    echo hidden('scop', $env['scop']);
    echo <<< METH

        <p>$msg</p>

        ${in}$m1 Disable<br>
        ${in}$m2 Enable<br>

METH;
    echo next_cancel();
    echo form_footer();
}

function CWIZ_FindValue(&$env, $code, $name, $db)
{
    $scop = $env['scop'];
    $valu = GCFG_FindValue($env, $code, $name, $db);
    $row = array();
    if (($scop == constScopGroup) && (@$env['mgrp'])) {
        $gid = $env['mgrp']['mgroupid'];
        $row = find_valu($gid, $name, $code, $db);
    }
    if ($scop == constScopAll) {
        $grp = all_machines($env, $db);
        $gid = ($grp) ? $grp['mgroupid'] : 0;
        $row = find_valu($gid, $name, $code, $db);
    }
    if ($row) {

        $valu = $row[0]['valu'];
    }
    return $valu;
}



function CWIZ_GetMachineString($site, $host, $env, $db)
{
    $mach = '';
    if ($env['scop']) {
        switch ($env['scop']) {
            case constScopAll:
                $mach = ' - All Machines';
                break;

            case constScopUser:
            case 5:
                if ($env['gid']) {
                    $sql = "SELECT name FROM MachineGroups WHERE mgroupid="
                        . $env['gid'];
                    $row = find_one($sql, $db);
                    if ($row) {
                        $mach = " - Group \"" . $row['name'] . "\"";
                    }
                }
                break;
            case constScopSite:
                $mach = " - Site \"$site\"";
                break;
            case constScopHost:
                $mach = " - Site \"$site\" - Machine \"$host\"";
                break;
        }
    }
    return $mach;
}
