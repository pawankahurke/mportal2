<?php








define('constPageEntrySites',   2);
define('constPageEntryTools',   3);
define('constPageEntryWUconfg', 4);
define('constPageEntryNotfy',   5);
define('constPageEntryReports', 6);
define('constPageEntryAsset',   7);
define('constPageEntryScrpConf', 8);


define('constReturnGroupTypeOne',  0);
define('constReturnGroupTypeMany', 1);

define('constGroupIncludeTempTable', 'temp_g_include');
define('constGroupExcludeTempTable', 'temp_g_exclude');
define('constGroupSuspendTempTable', 'temp_g_suspend');

define('constGroupSuspend', 'group_suspend');
define('constGroupExclude', 'group_exclude');
define('constGroupInclude', 'group_include');

define('constMachineGroupMessage',    'No machine groups');
define('constMachineGroupDefaultALL', 1);

define('constEventReports',       'Reports');
define('constEventNotifications', 'Notifications');
define('constAssetReports',       'Asset');


define('constConfirm', 'ConfirmText');

define('constButtonExec',     'Execute');
define('constPassValue',      'n0n$3n$3');
define('constTypeSemaphore',  'semaphore');
define('constQueryRestrict',  1);
define('constQueryNoRestrict', 0);
define('constQueryIncludeMgroupid', 1);
define('constQueryExcludeMgroupid', 0);



function cancel_link($custom, $env = array())
{
    $href = return_custom_href($custom);
    if (($env) && (@$env['level'])) {
        $href .= "&level=" . $env['level'];
    }
    if (($env) && (@$env['addgroup']) && (@$env['allowdelete'])) {
        $href .= "&addgroup=" . $env['addgroup'] . "&delgroup="
            . $env['addgroup'];
    }
    $valu = constButtonCan;
    return create_custom_button($href,  $valu);
}


function ok_link($custom, $mgroupid)
{
    $href = return_custom_ok_href($custom, $mgroupid);
    $valu = constButtonOk;
    return create_custom_button($href, $valu);
}

function back_link($custom, $env)
{
    $href = back_href($custom, $env);
    $valu = constButtonBack;
    return create_custom_button($href, $valu);
}



function back_href($custom, $env)
{

    $scp = $env['scop'];
    $cid = $env['cid'];
    $act = $env['act'];
    $domap = $env['domap'];

    $group_name = '';
    $mgroupid   = '';
    $mcatid     = '';
    $hid        = '';
    $phid       = '';
    $pcid       = '';
    $pscop      = '';
    $site       = '';
    $censusid   = '';
    $snum       = '';
    $addact     = '';

    switch ($act) {

        case 'csit':
            $act = 'wapp';
            $scp = 0;
            $cid = 0;
            $hid = 0;
            break;


        case 'chst':
            $act = 'csit';
            $hid = 0;
            $cid = 0;
            break;


        case 'enab':
            $domap = 0;
            if ($scp == constScopAll) {
                $act = 'choose_map';
            } else if ($scp == constScopHost) {
                $act      = 'choose_map';
                $mgroupid = $env['sgrp']['mgroupid'];
                $mcatid   = $env['sgrp']['mcatid'];
                $site     = $env['site'];
                $phid     = $env['hid'];
            } else if ($scp == constScopUser) {
                $act = 'choose_map';
                $mgroupid = $env['mgroupid'];
                $mcatid   = $env['mcatid'];
                $grp  = constScopGroup;
                $href = "../config/scrpconf.php?custom=$custom"
                    . "&scop=$grp";
                if (@$env['level']) {
                    $href .= "&level=" . $env['level'];
                }
                if (@$env['isparent']) {
                    $href .= '&isparent=' . $env['isparent'];
                }
                return $href;
            } else {
                $act = 'choose_map';
            }
            break;


        case 'scrp':
            $act        = 'msel';
            $group_name = $env['group_name'];
            $mgroupid   = $env['mgroupid'];
            $mcatid     = $env['mcatid'];
            $hid        = $env['hid'];
            $phid       = $env['prev_hid'];
            $pcid       = $env['prev_cid'];
            $pscop      = $env['prev_scop'];
            $scp = $pscop;
            if ($scp == constScopSite) {
                $act = 'enab';
            }
            if ($scp == constScopUser) {
                $act = 'wapp';
                $addact = '&act=machine_selected';
            }
            break;


        case 'scop':
            $mgroupid = $env['sgrp']['mgroupid'];
            $mcatid   = $env['sgrp']['mcatid'];
            $act      = 'csit';
            break;

        case 'wapp':
            if ($scp == constScopGroup) {
                $act = '';
                $scp = 0;
                $cid = 0;
                $hid = 0;
            }
            break;

        case 'msel':
            if ($scp == constScopUser) {
                $grp  = constScopGroup;
                $href = "../config/scrpconf.php?custom=$custom"
                    . "&scop=$grp";
                if (@$env['level']) {
                    $href .= "&level=" . $env['level'];
                }
                if (@$env['isparent']) {
                    $href .= '$isparent=' . $env['isparent'];
                }
                return $href;
            } else {
                $domap = 0;
                $act = 'machine_selected';
                $mgroupid = $env['mgroupid'];
                $addact .= "&gid=$mgroupid";
            }
            break;


        case 'prmt':
            $act        = 'msel';
            $censusid   = $env['censusid'];
            $mgroupid   = $env['mgroupid'];
            $mcatid     = $env['mcatid'];
            $snum       = $env['snum'];
            $group_name = $env['group_name'];
            $pcid       = $env['prev_cid'];
            $phid       = $env['prev_hid'];
            $pscop      = $env['prev_scop'];

            break;

        case 'choose_map':
            $act = 'wapp';
            if ($scp == constScopSite) {
                $cid = 0;
                $act = 'csit';
            }
            if ($scp == constScopHost) {
                $hid = 0;
                $act = 'chst';
            }
            if ($scp == constScopAll) {
                $scp = 0;
            }
            break;

        case 'choose_os':
        case 'choose_ossite':
        case 'choose_local':
            $act = 'wapp';
            $scp = 0;
            break;
    }

    $cid  = ($pcid == 0) ? $cid : $pcid;

    $dbg = "<br>scp($scp) cid($cid) act($act) mgrp($mgroupid)"
        . " mcat($mcatid) grp($group_name) hid($hid) phid($phid)"
        . " pcid($pcid) pscop($pscop) site($site) censusid($censusid)"
        . " snum($snum)";
    debug_note($dbg);

    switch ($custom) {
        case constPageEntryScrpConf:
            $href = "../config/scrpconf.php?act=$act"
                . "&cid=$cid&custom=$custom&scop=$scp"
                . "&mgroupid=$mgroupid&mcatid=$mcatid"
                . "&group_name=$group_name&hid=$phid"
                . "&pscop=$pscop&site=$site&censusid=$censusid"
                . "&snum=$snum&domap=" . $domap . $addact;
            if (@$env['level']) {
                $href .= "&level=" . $env['level'];
            }
            if (@$env['isparent']) {
                $href .= '&isparent=' . $env['isparent'];
            }
            break;
    }
    return $href;
}

function create_custom_button($href, $valu)
{
    $link = "window.open('$href','_self')";
    return "<input type=\"button\" value=\"$valu\" onclick=\"$link\">";
}


function return_custom_ok_href($custom, $mgroupid)
{
    $href = '';
    switch ($custom) {
        case constDashStatus_SelectMachineGroup:
            $href = '../config/syst.php?act='
                . constDashStatus_SelectDisplay . "&mgroupid=$mgroupid";
            break;
    }
    return $href;
}


function return_custom_href($custom)
{

    $href = '';
    switch ($custom) {
        case constDashStatus_SelectMachineGroup:
            $href = '../config/syst.php';
            break;

        case constPageEntryScrpConf:
            $href = '../config/scrpconf.php?custom='
                . constPageEntryScrpConf;
            break;

        case constPageEntryAsset:
            $href = '../asset/report.php?'
                . return_asset_url();
            break;

        case constPageEntrySites:
        case constPageEntryTools:
            $href = '../config/index.php?act=wiz';
            break;

        case constPageEntryNotfy:
            $href  = '../event/notify.php?'
                . return_notification_url();
            break;

        case constPageEntryReports:
            $href  = '../event/report.php?'
                . return_report_url();
            break;

        case constPageEntryWUconfg:
            $href = '../patch/wu-confg.php';
            break;

        default:
            $href = '../patch/wu-confg.php';
            break;
    }
    return $href;
}


function customURL($const)
{
    return ('custom=' . $const);
}


function group_type($row)
{
    $type = @intval($row['style']);
    switch ($type) {
        case constStyleInvalid:
            return 'Invalid';
        case constStyleBuiltin:
            return 'Built-In';
        case constStyleManual:
            return 'Manual';
        case constStyleEvent:
            return 'Event query';
        case constStyleAsset:
            return 'Asset query';
        case constStyleExpr:
            return 'Expression';
        case constStyleSearch:
            return 'Search';
        case constStyleType:
            return 'Type';
        default:
            return "Unknown ($type)";
    }
}

function dynamic_group($type)
{
    switch ($type) {
        case constStyleBuiltin:
            return true;
        case constStyleManual:
            return false;
        case constStyleEvent:;
        case constStyleAsset:;
        case constStyleExpr:
            return true;
        case constStyleSearch:
            return true;
        default:
            return false;
    }
}


function find_mcat_name($name, $db)
{
    $row = array();
    if ($name) {
        $qn  = safe_addslashes($name);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
            . " where category = '$qn'";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_mcat_tid($tid, $db)
{
    $row = array();
    if ($tid > 0) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
            . " where mcatid = $tid";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_mgrp_name($name, $db)
{
    $row = array();
    if ($name != '') {
        $qn  = safe_addslashes($name);
        $sql = "select G.*, C.mcatid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
            . " where G.name = '$qn'\n"
            . " and G.mcatuniq = C.mcatuniq";
        $row = find_one($sql, $db);
    }
    return $row;
}



function find_mgrp_gid($gid, $type, $db)
{
    $row = array();
    if ($gid) {
        $sql = "select G.*, C.mcatid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
            . " where mgroupid IN ($gid)"
            . " and G.mcatuniq = C.mcatuniq";
        $row = ($type == constReturnGroupTypeMany) ? find_many($sql, $db) :
            find_one($sql, $db);
    }
    return $row;
}


function mcat_options($db)
{
    $txt = str_repeat('&nbsp;', 10);
    $opt = array($txt);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
        . " order by precedence";
    $set = find_many($sql, $db);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $tid = $row['mcatid'];
            $cat = $row['category'];
            $opt[$tid] = $cat;
        }
    }
    return $opt;
}




function mgrp_options($auth, $db)
{
    $opt = array();
    $set = array();
    $own = array();
    if ($auth) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups left join " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
            . " on (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq="
            . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq)\n"
            . " where username = '$qu'\n"
            . " order by mcatid, name";
        $own = find_many($sql, $db);

        $sql = "select G.*, B.mcatid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as B\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and C.site = U.customer\n"
            . " and U.username = '$qu'\n"
            . " and G.mcatuniq = B.mcatuniq\n"
            . " group by G.mgroupid\n"
            . " order by B.mcatid, G.name";
        $set = find_many($sql, $db);
    }

    if (($set) || ($own)) {
        $none = str_repeat('&nbsp;', 10);
        $opt[0][0] = $none;
    }

    reset($set);
    foreach ($set as $key => $row) {
        $gid = $row['mgroupid'];
        $tid = $row['mcatid'];
        $grp = $row['name'];
        $opt[$tid][0] = $none;
        $opt[$tid][$gid] = $grp;
    }

    reset($own);
    foreach ($own as $key => $row) {
        $gid = $row['mgroupid'];
        $tid = $row['mcatid'];
        $grp = $row['name'];
        $opt[$tid][0] = $none;
        $opt[$tid][$gid] = $grp;
    }

    return $opt;
}




function mgrp_alien($gid, $auth, $db)
{
    $qu  = safe_addslashes($auth);
    $sql = "select X.* from\n"
        . " (" . $GLOBALS['PREFIX'] . "core.Census as X,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G)\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.Customers as C\n"
        . " on C.username = '$qu'\n"
        . " and C.customer = X.site\n"
        . " where M.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid = $gid\n"
        . " and M.censusuniq = X.censusuniq\n"
        . " and C.id is NULL";
    return find_many($sql, $db);
}


function delete_host_gid($gid, $db)
{
    $num = 0;
    if ($gid > 0) {
        $sql = "select mgmapid, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq, "
            . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap "
            . "left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
        $set = find_many($sql, $db);
        if ($set) {
            foreach ($set as $key => $row) {
                if (!VARS_HandleDeletedGroup($row['censusuniq'], $row['mgroupuniq'], $db)) {
                    logs::log(
                        __FILE__,
                        __LINE__,
                        "delete_host_gid: "
                            . "VARS_HandleDeletedGroup returned error",
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
            "delete_host_gid",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
            . " using " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)"
            . " where mgroupid = $gid";
        if ($set) {
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
        }
    }
    return $num;
}


function all_machine_group_SQL($tid, $qu, $wrd)
{
    $sql = "select G.*, count(M.mgmapid) as number\n"
        . " from (MachineGroups as G,\n"
        . " Customers as U,\n"
        . " MachineCategories as A)\n"
        . " inner join Census as C\n"
        . "   on U.customer = C.site\n"
        . " left join MachineGroupMap as M\n"
        . "   on M.mgroupuniq = G.mgroupuniq\n"
        . "   and M.censusuniq = C.censusuniq\n"
        . " where G.mcatuniq = A.mcatuniq\n"
        . "   and A.mcatid = $tid\n"
        . "   and U.username = '$qu'\n"
        . "   and ((M.mgmapid is not NULL) or (G.username = '$qu'))\n"
        . " group by G.mgroupid\n"
        . " order by $wrd";
    return $sql;
}



function single_machine_groups($db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.MachineCategories as M\n"
        . " join " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " where M.category = 'Machine'\n"
        . " and M.mcatuniq = G.mcatuniq";
    return find_many($sql, $db);
}



function GRPS_ReturnAllMgroupid($db)
{
    $sql = "select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups as MG\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineCategories as MC\n"
        . " on MG.mcatuniq = MC.mcatuniq\n"
        . " where MC.category = 'All'\n"
        . " and MG.name  = 'All'\n"
        . " and MG.human = 0";
    $set = find_one($sql, $db);
    return $set['mgroupid'];
}



function return_mcatid($mgroupid, $db)
{
    $sql = "select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineCategories on (\n"
        . "  " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq)"
        . " where mgroupid = $mgroupid";
    $res = find_one($sql, $db);
    return $res['mcatid'];
}



function return_mgroup_name($mgroupid, $db)
{
    $sql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " where mgroupid = $mgroupid";
    $set = find_one($sql, $db);
    return $set['name'];
}


function GRPS_return_group_from_mgroupid($mgroupid, $user, $include, $db)
{
    $select  = "select distinct C.site, C.host";
    $select .= ($include == constQueryIncludeMgroupid) ?
        ", C.id, G.mgroupid\n"
        : "\n";

    $sql = $select
        . " from " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " where G.mgroupid IN ($mgroupid)\n"
        . " and M.censusuniq = C.censusuniq\n"
        . " and U.customer = C.site\n"
        . " and U.username = '$user'\n"
        . " and G.mgroupuniq = M.mgroupuniq\n"
        . " order by site, host";
    $set = find_many($sql, $db);
    reset($set);
    if (!$set) {
        $dbg = "l-grps: GRPS_return_group_from_mgroupid()"
            . " set is empty for user($user)";
        logs::log(__FILE__, __LINE__, $dbg, 0);
        return false;
    }
    return $set;
}



function GRPS_return_censusid_from_mcatid_mgroupid($mgroupid, $mcatid, $db)
{
    $quote = false;
    $sql  = "select id as censusid from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on (\n"
        . "  " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
        . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) \n left join "
        . $GLOBALS['PREFIX'] . "core.MachineCategories on (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq="
        . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq) \n left join "
        . " " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
        . $GLOBALS['PREFIX'] . "core.Census.censusuniq)\n"
        . " where " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupid = $mgroupid\n"
        . " and " . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatid = $mcatid\n"
        . " and id IS NOT NULL";
    $set  = find_many($sql, $db);
    return get_field_values('censusid', $quote, $set);
}



function get_field_values($field, $quote, &$set)
{
    $tmp = array();
    reset($set);
    foreach ($set as $i => $db_entry) {
        $t = $db_entry[$field];
        $t = ($quote) ? "'$t'" : $t;
        $tmp[] = $t;
    }
    if ($tmp) {
        $tmp = join(",", $tmp);
    }
    return $tmp;
}



function GRPS_return_client_versions($censusid, $db)
{
    $sql   = "select distinct vers from " . $GLOBALS['PREFIX'] . "core.Revisions\n"
        . " where censusid in ($censusid)\n"
        . " order by vers desc";
    return find_many($sql, $db);
}



function GRPS_build_version_dependent_scrip_list($client_v, $db)
{
    $sql = "drop table if exists temp_scrip_list";
    redcommand($sql, $db);

    $sql = "create temporary table temp_scrip_list(\n"
        . " scrip_num  int(11) not null primary key,\n"
        . " scrip_name text    not null ,\n"
        . " unique index uniq (scrip_num)\n"
        . ")";
    $res = redcommand($sql, $db);
    if (($res) && ($client_v)) {
        reset($client_v);
        foreach ($client_v as $i => $db_entry) {
            $client_version = $db_entry['vers'];
            $client_version = "'" . $client_version . "'";

            $sql = "insert ignore into temp_scrip_list\n"
                . " select S.num, S.name from\n"
                . " " . $GLOBALS['PREFIX'] . "core.Scrips as S\n"
                . " where S.vers in ($client_version)\n"
                . " order by S.num";
            $res = redcommand($sql, $db);
            if (!$res) {
                $etxt = "l-grps: build_version_dependent_scrip_list()"
                    . " error";
                logs::log(__FILE__, __LINE__, $etxt, 0);
                $sql = "drop table if exists temp_scrip_list";
                redcommand($sql, $db);
                return false;
            }
        }
        $sql = "select * from temp_scrip_list order by scrip_num";
        return find_many($sql, $db);
    }
    return false;
}



function GRPS_build_TempVars($tbl, $db)
{
    $sql = "create temporary table $tbl(\n"
        . " varid  int(11)    not null primary key,\n"              . " name   text       not null default '',\n"              . " itype  int(11)    not null default  0,\n"              . " pwsc   int(11)    not null default  0,\n"              . " dngr   tinyint(1) not null default  0,\n"              . " defval text       not null default '',\n"              . " config tinyint(1) not null default  0,\n"              . " cfgord int(11)    not null default  0,\n"              . " valu   text       not null default '',\n"              . " def    tinyint(1) not null default  0,\n"              . " dval   text       not null default  ''\n"              . ")";
    $res = redcommand($sql, $db);
    if (!$res) {
        logs::log(__FILE__, __LINE__, "'l-grps.php: build_scrip_descriptions failed building $tbl", 0);
        $sql = "drop table if exists $tbl";
        redcommand($sql, $db);
    }
}



function GRPS_build_scrip_descriptions($scop, $censusid, $mgroupid, $db)
{
    $temp_vars_1 = 'temp_vars_1';
    $temp_vars_2 = 'temp_vars_2';

    GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db);

    GRPS_build_TempVars($temp_vars_1, $db);
    GRPS_build_TempVars($temp_vars_2, $db);

    $client_v = GRPS_return_client_versions($censusid, $db);
    reset($client_v);
    foreach ($client_v as $i => $db_entry) {
        $client_version = $db_entry['vers'];
        $sql = "replace into $temp_vars_1\n"
            . " select distinct V.varid, V.name, V.itype,\n"
            . " VV.pwsc, VV.dngr, VV.defval,\n"
            . " VV.config, VV.configorder,\n"
            . " '', '', VV.descval as valu from\n"
            . " VarVersions as VV\n"
            . " left join Variables as V\n"
            . " on (VV.varuniq = V.varuniq)\n"
            . " where (V.scop = $scop)\n"
            . " and (VV.vers = '$client_version')";
        $res = redcommand($sql, $db);
    }
    if (!$res) {
        logs::log(__FILE__, __LINE__, "l-grps.php: build_scrip_descriptions failed populating $temp_vars_1", 0);
        GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db);
    }


    $sql = "insert into $temp_vars_2\n"
        . " select * from $temp_vars_1";
    $res = redcommand($sql, $db);
    if (!$res) {
        logs::log(__FILE__, __LINE__, "l-grps.php: insert into $temp_vars_2 from $temp_vars_1 failed", 0);
        GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db);
    }



    $sql = "replace into $temp_vars_1\n"
        . " select V.varid, TV.name, TV.itype, TV.pwsc, TV.dngr,\n"
        . " TV.defval, TV.config, TV.cfgord, VV.valu, VV.def, TV.dval\n"
        . " from $temp_vars_2 as TV\n"
        . " left join MachineGroups as G\n"
        . " on (G.mgroupid = $mgroupid)\n"
        . " left join Variables as V\n"
        . " on  (TV.varid = V.varid)"
        . " left join VarValues as VV\n"
        . " on  (V.varuniq = VV.varuniq)\n"
        . " and (VV.mgroupuniq = G.mgroupuniq)\n";

    $res = redcommand($sql, $db);
    if (!$res) {
        logs::log(__FILE__, __LINE__, "l-grps.php: replace into $temp_vars_1 from $temp_vars_2 failed", 0);
        return GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db);
    }
    return $res;
}



function GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db)
{
    $sql = "drop table if exists $temp_vars_1";
    redcommand($sql, $db);

    $sql = "drop table if exists $temp_vars_2";
    redcommand($sql, $db);
}



function GRPS_variable(&$db_entry)
{
    $proc = GRPS_proc_map($db_entry);
    switch ($proc) {
        case 'text':
            return GRPS_text($db_entry);
        case 'checkbox':
            return GRPS_checkbox($db_entry);
        case 'execute':
            return GRPS_execute($db_entry);
        case 'password':
            return GRPS_password($db_entry);
        case 'textarea':
            return GRPS_textarea($db_entry);

        default:
            echo GRPS_scrip_row('Unknown', 'Error');
            $t = "l-grps: GRPS_variable() returned"
                . "unknown proc:$proc";
            logs::log(__FILE__, __LINE__, $t, 0);
            break;
    }
}



function GRPS_text(&$db_entry)
{
    $text = $db_entry['dval'];
    $name = $db_entry['name'];
    $valu = $db_entry['valu'];

    $nput = input_text($name, $valu);
    return GRPS_scrip_row($text, $nput);
}



function GRPS_scrip_row($text, $valu)
{
    return "<tr><td>$text</td><td>$valu</td></tr>";
}



function GRPS_checkbox(&$db_entry)
{
    $valu = $db_entry['valu'];
    $name = $db_entry['name'];
    $dval = $db_entry['dval'];
    $valu = intval($valu);
    $chbox = checkbox($name, $valu);
    return GRPS_scrip_row($dval, $chbox);
}



function GRPS_execute(&$db_entry)
{
    $text  = $db_entry['valu'];
    $name  = $db_entry['name'];
    $dval  = $db_entry['dval'];
    $b_val = constButtonExec;
    $butn  = "<input type=\"submit\" name=\"$name\" value=\"$b_val\">";
    return GRPS_scrip_row($dval, $butn);
}



function GRPS_textarea(&$db_entry)
{
    $vid  = $db_entry['varid'];
    $valu = $db_entry['valu'];
    $name = $db_entry['name'];
    $dval = $db_entry['dval'];
    $x = 0;
    $y = 0;
    $r = 0;
    $c = 0;
    $r = 3;
    $class = '';
    $msg   = ($class) ? "class=\"$class\" " : '';
    $ta    =  "<textarea $msg"
        . "rows=\"$r\" style=\"width:800px;\" name=\"$name\">"
        . $valu
        . '</textarea>';
    return GRPS_scrip_row($dval, $ta);
}



function GRPS_password(&$db_entry)
{
    $vid  = $db_entry['varid'];
    $text = $db_entry['dval'];
    $name = $db_entry['name'];
    $valu = $db_entry['valu'];

    $conf = confirm_var($name);
    $p1   = GRPS_create_password($name, $valu);
    $p2   = GRPS_create_password($conf, $valu);
    $pass = "$p1\n<br>$p2\n";
    return GRPS_scrip_row($text, $pass);
}



function GRPS_create_password($name, $text)
{
    $pass = constPassValue;
    $valu = ($text) ? " value=\"$pass\"" : '';
    $type = 'type="password"';
    return "<input $type name=\"$name\"$valu>";
}



function GRPS_user_selected($db_entry)
{
    $proc = GRPS_proc_map($db_entry);
    $post_valu = false;
    switch ($proc) {
        case 'text':;
        case 'textarea':
            $post_valu = GRPS_get_string_text($db_entry);
            break;

        case 'checkbox':
            $post_valu = GRPS_get_integer_cbox($db_entry);
            break;

        case 'password':
            $post_valu = GRPS_get_password_text($db_entry);
            break;

        case 'execute':
            $post_valu = GRPS_get_execute_valu($db_entry);
            break;
    }
    return $post_valu;
}



function GRPS_get_execute_valu($db_entry)
{
    $scrp_name = $db_entry['name'];
    $post_valu = get_string($scrp_name, 0);
    if ($post_valu) {
        return $post_valu;
    }
    return -1;
}



function GRPS_get_password_text($db_entry)
{
    $scrp_name = $db_entry['name'];
    $scrp_conf = $scrp_name . '_confirmation';
    $scrp_valu = $db_entry['valu'];

    $p1_valu = get_string($scrp_name, '');
    $p2_valu = get_string($scrp_conf, '');

    $p1_valu = normalize($p1_valu);
    $p2_valu = normalize($p2_valu);

    if (($p1_valu == $p2_valu) && ($p1_valu != $scrp_valu) && ($p1_valu != constPassValue)) {

        return $p1_valu;
    }
    return -1;
}



function GRPS_get_integer_cbox($db_entry)
{
    $scrp_name = $db_entry['name'];
    $scrp_valu = $db_entry['valu'];
    $post_valu = get_integer($scrp_name, 0);
    if ($post_valu != $scrp_valu) {
        return $post_valu;
    }
    return -1;
}



function GRPS_get_string_text($db_entry)
{
    $sv_set = array();
    $pv_set = array();

    $scrp_name = $db_entry['name'];
    $scrp_valu = $db_entry['valu'];
    $post_valu = get_string($scrp_name, '');

    $scrp_valu = normalize($scrp_valu);
    $post_valu = normalize($post_valu);
    $comp      = strcmp($scrp_valu, $post_valu);
    if ($comp != 0) {

        return $post_valu;
    }

    return -1;
}


function GRPS_dangerous(&$set)
{
    $out = array();
    reset($set);
    foreach ($set as $key => $row) {
        if ($row['dngr']) {
            $out[] = $row['name'];
        }
    }
    return $out;
}



function GRPS_proc_map(&$row)
{
    $type = $row['itype'];
    $pass = $row['pwsc'];
    switch ($type) {
        case constVblTypeInteger:
            return 'text';
        case constVblTypeBoolean:
            return 'checkbox';
        case constVblTypeSemaphore:
            return 'execute';
        case constVblTypeString:
            return ($pass) ? 'password' : 'textarea';
        default:
            return "unknown($type)";
    }
}


function build_machine_group_content($wrd, $db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
        . " order by $wrd";
    return find_many($sql, $db);
}



function build_group_category_content($user, $restrict, $db)
{
    $mgrp   = '';
    $dist   = '';
    $VVjoin = '';
    if ($restrict == constQueryRestrict) {
        $mgrp   = " and VV.mgroupuniq = M.mgroupuniq\n";
        $dist   = " distinct ";
        $VVjoin = " " . $GLOBALS['PREFIX'] . "core.VarValues as VV,\n";
    }
    $qu  = safe_addslashes($user);
    $sql = "select $dist G.*, D.mcatid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as D,\n"
        . $VVjoin
        . " " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
        . " where U.username = '$qu'\n"
        . " and U.customer = C.site\n"
        . " and C.censusuniq = M.censusuniq\n"
        . " and M.mgroupuniq = G.mgroupuniq\n"
        . " and G.mcatuniq = D.mcatuniq\n"
        . $mgrp
        . " order by G.name";
    return find_many($sql, $db);
}


function build_group_category_order($user, $db)
{
    $qu  = safe_addslashes($user);
    $sql = "select G.*, C.mcatid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
        . " where username = '$qu'\n"
        . " and G.mcatuniq = C.mcatuniq\n"
        . " order by name";
    return find_many($sql, $db);
}


function build_max_precedence($db)
{
    $sql = 'select max(precedence) from MachineCategories';
    return intval(find_scalar($sql, $db));
}


function build_category_list($cat)
{
    $grps = array();
    reset($cat);
    foreach ($cat as $key => $row) {
        $tid = $row['mcatid'];
        $grps[$tid] = array();
    }
    return $grps;
}

function build_group_category_list($one, $grps)
{
    reset($one);
    foreach ($one as $key => $row) {
        $tid = $row['mcatid'];
        $gid = $row['mgroupid'];
        $grps[$tid][$gid] = $row;
    }
    return $grps;
}



function build_group_list($auth, $restrict, $db)
{
    $cat  = build_machine_group_content('precedence', $db);
    $one  = build_group_category_content($auth, $restrict, $db);
    if ($restrict == constQueryNoRestrict) {
        $own  = build_group_category_order($auth, $db);
    }
    $grps = build_category_list($cat);
    $grps = build_group_category_list($one, $grps);
    if ($restrict == constQueryNoRestrict) {
        $grps = build_group_category_list($own, $grps);
    }
    reset($grps);
    return $grps;
}



function prep_for_multiple_select($grps)
{
    $out = array();
    reset($grps);
    foreach ($grps as $precedence => $groups) {
        reset($groups);
        foreach ($groups as $mgroupid => $groups_value) {
            $out[$mgroupid] = $groups_value['name'];
        }
    }
    return $out;
}



function GRPS_find_mgrp_list($auth, $type, $db)
{
    $qa  = safe_addslashes($auth);
    $cfg = GRPS_create_from_join($type);
    $N   = GRPS_create_select($type);
    $sql = "select $N\n"
        . $cfg
        . " on N.name = X.name\n"
        . " and X.global = 0\n"
        . " and X.username = '$qa'\n"
        . " where ( (N.username = '$qa')\n"
        . " or (N.global = 1 and X.id is NULL) )";
    return find_many($sql, $db);
}



function GRPS_mgrp_arrange_list(
    $list,
    $def_option,
    $pos,
    $all_option,
    $all_pos,
    $def_message
) {
    $out           = array();
    $out[$pos]     = $def_option;
    $out[$all_pos] = $all_option;
    if ($list) {
        reset($list);
        foreach ($list as $index => $db_entry) {
            $mgroupid       = $db_entry['mgroupid'];
            $name           = $db_entry['name'];
            $out[$mgroupid] = $name;
        }
    }
    return $out;
}



function GRPS_insert_valid_entries($list, $db)
{
    $g_inc_str = '';
    $g_exc_str = '';
    $g_sus_str = '';

    reset($list);
    foreach ($list as $index => $db_entry) {

        if (($db_entry['group_include'] != '')
            && ($db_entry['group_include'])
        ) {
            $g_inc_str .= $db_entry['group_include'] . ',';
        }
        if (($db_entry['group_exclude'] != '')
            && ($db_entry['group_exclude'])
        ) {
            $g_exc_str .= $db_entry['group_exclude'] . ',';
        }
        if (
            (isset($db_entry['group_suspend']))
            && ($db_entry['group_suspend'] != '')
            && ($db_entry['group_suspend'])
        ) {
            $g_sus_str .= $db_entry['group_suspend'] . ',';
        }
    }

    $dbg_txt = " ginclude:($g_inc_str)"
        . " gexclude:($g_exc_str)"
        . " gsuspend:($g_sus_str)";
    debug_note($dbg_txt);


    GRPS_populate_group_temp($g_inc_str, constGroupIncludeTempTable, $db);
    GRPS_populate_group_temp($g_exc_str, constGroupExcludeTempTable, $db);
    GRPS_populate_group_temp($g_sus_str, constGroupSuspendTempTable, $db);
}



function GRPS_populate_group_temp($str, $tbl, $db)
{
    if ($str) {
        $str = remove_trailing_comma($str);
        $str = add_parens($str);
        GRPS_insert_into_group_temp($str, $tbl, $db);
    } else {
        logs::log(__FILE__, __LINE__, "l-grps: table $tbl empty", 0);
        GRPS_insert_into_group_temp('', $tbl, $db);
    }
}


function GRPS_return_scrip_data($db)
{
    $sql = "select * from temp_vars_1\n"
        . " where config in (0,1)\n"
        . " order by cfgord";
    return find_many($sql, $db);
}



function GRPS_insert_into_group_temp($list, $tbl_name, $db)
{
    $sql = "drop table if exists $tbl_name";
    $res = redcommand($sql, $db);

    $sql = "create temporary table $tbl_name(\n"
        . " m_id int(11) not null primary key\n"
        . ")";
    $res = redcommand($sql, $db);
    if ($res) {
        $sql = "insert ignore into $tbl_name\n"
            . " values ($list)";
        $res = redcommand($sql, $db);
        if (!$res) {
            logs::log(__FILE__, __LINE__, "l-grps: table $tbl_name populate error");
        }
    }
    if (!$res) {
        logs::log(__FILE__, __LINE__, "l-grps: table $tbl_name create error");
    }
}



function add_parens($str)
{
    return str_replace(",", "),(", $str);
}



function remove_trailing_comma($str)
{
    return rtrim($str, ',');
}



function GRPS_find_mgrp_name_pairs($tbl, $db)
{
    $sql = "select mgroupid, name from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as MG\n"
        . " left join $tbl as T\n"
        . " on T.m_id = MG.mgroupid\n"
        . " where T.m_id = MG.mgroupid";
    return find_many($sql, $db);
}



function GRPS_is_mgroupid_in_use($mgroupid, $auth, $type, $db)
{
    $sa  = safe_addslashes($auth);
    $cfj = GRPS_create_from_join($type);
    $rgx = GRPS_create_regexp($type, $mgroupid);
    $sql = "select N.*\n"
        . $cfj
        . " on N.name = X.name\n"
        . " and X.global = 0\n"
        . " and X.username = '$sa'\n"
        . " where ( (N.username = '$sa')\n"
        . " or (N.global = 1 and X.id is NULL) )\n"
        . $rgx;
    return find_many($sql, $db);
}



function GRPS_create_from_join($type)
{
    $tbl = '';
    $str = '';

    switch ($type) {
        case constEventNotifications:
            $tbl = $GLOBALS['PREFIX'] . 'event.Notifications';
            break;
        case constEventReports:
            $tbl = $GLOBALS['PREFIX'] . 'event.Reports';
            break;
        case constAssetReports:
            $tbl = $GLOBALS['PREFIX'] . 'asset.AssetReports';
            break;
        default:
            logs::log(__FILE__, __LINE__, "l-grps.php: GRPS_create_from_join() unknown type($type)");
    }
    if ($tbl) {
        $str  = " from $tbl as N\n"
            . " left join $tbl as X\n";
    }
    return $str;
}



function GRPS_create_select($type)
{
    return ($type == constEventNotifications) ? 'N.group_include, N.group_exclude, N.group_suspend' :
        'N.group_include, N.group_exclude';
}



function GRPS_create_regexp($type, $mgroupid)
{
    $str = GRPS_create_regexp_groups($mgroupid);
    return ($type == constEventNotifications) ? $str . " or N.group_suspend regexp '(^|,)$mgroupid(,|$)')\n"
        : $str . ")\n";
}



function GRPS_create_regexp_groups($mgroupid)
{
    $tmp = " and (N.group_include regexp '(^|,)$mgroupid(,|$)'\n"
        . "  or  N.group_exclude regexp '(^|,)$mgroupid(,|$)'\n";
    return $tmp;
}



function GRPS_remove_mgroupid_from_event($gid, $rnid, $type, $db)
{
    $out  = array();
    $num  = 0;

    switch ($type) {
        case constAssetReports:
            $sql = "select id, group_exclude, group_include\n"
                . " from " . $GLOBALS['PREFIX'] . "asset.AssetReports\n"
                . " where id in ($rnid)";
            break;
        case constEventNotifications:
            $sql  = "select id, group_suspend, group_exclude,"
                . " group_include\n"
                . " from  " . $GLOBALS['PREFIX'] . "event.Notifications\n"
                . " where id in ($rnid)";
            break;
        case constEventReports:
            $sql = "select id, group_exclude, group_include\n"
                . " from  " . $GLOBALS['PREFIX'] . "event.Reports\n"
                . " where id in ($rnid)";
            break;
    }

    $set  = find_many($sql, $db);
    reset($set);
    foreach ($set as $index => $event) {
        $n_id = $event['id'];

        $n_suspend = @$event['group_suspend'];
        $n_exclude =   $event['group_exclude'];
        $n_include =   $event['group_include'];

        $dbg_msg   = "<br>[ current mgroupid ]"
            . " include:($n_include) "
            . " exclude:($n_exclude) "
            . " suspend:($n_suspend) ";

        $n_include =   explode(",", $n_include);
        $n_exclude =   explode(",", $n_exclude);
        $n_suspend = @explode(",", $n_suspend);

        debug_note($dbg_msg);
        $dbg_msg = '';


        if ($n_suspend) {
            $n_suspend = GRPS_remove_mgroupid($gid, $n_suspend);
            $n_suspend = join(",", $n_suspend);
            $out[$n_id]['group_suspend'] = $n_suspend;
        }
        if ($n_exclude) {
            $n_exclude = GRPS_remove_mgroupid($gid, $n_exclude);
            $n_exclude = join(",", $n_exclude);
            $out[$n_id]['group_exclude'] = $n_exclude;
        }
        if ($n_include) {
            $n_include = GRPS_remove_mgroupid($gid, $n_include);
            $n_include = join(",", $n_include);
            $out[$n_id]['group_include'] = $n_include;
        }

        $dbg_msg  = " [ removed mgroupid:$gid ]"
            . " include:($n_include) "
            . " exclude:($n_exclude) "
            . " suspend:($n_suspend) ";
        debug_note($dbg_msg);
    }
    if ($out) {
        $now = time();
        reset($out);
        foreach ($out as $id => $event) {
            $group_include =   $event['group_include'];
            $group_exclude =   $event['group_exclude'];
            $group_suspend = @$event['group_suspend'];

            switch ($type) {
                case constAssetReports:
                    $sql = "update " . $GLOBALS['PREFIX'] . "asset.AssetReports set\n"
                        . " group_include = '$group_include',\n"
                        . " group_exclude = '$group_exclude',\n"
                        . " modified = $now\n"
                        . " where id = $id";
                    break;
                case constEventNotifications:
                    $sql = "update  " . $GLOBALS['PREFIX'] . "event.Notifications set\n"
                        . " group_include = '$group_include',\n"
                        . " group_exclude = '$group_exclude',\n"
                        . " group_suspend = '$group_suspend',\n"
                        . " modified = $now\n"
                        . " where id = $id";
                    break;
                case constEventReports:
                    $sql = "update  " . $GLOBALS['PREFIX'] . "event.Reports set\n"
                        . " group_include = '$group_include',\n"
                        . " group_exclude = '$group_exclude',\n"
                        . " modified = $now\n"
                        . " where id = $id";
                    break;
            }
            $res = redcommand($sql, $db);
            if ($res) {
                $num++;
            }
        }
    }
    echo "$num $type updated.\n";
}



function GRPS_remove_mgroupid($gid, $list)
{
    $out = array();
    reset($list);
    foreach ($list as $index => $mgroupid) {
        if ($mgroupid != $gid) {
            $out[] = $mgroupid;
        }
    }

    if (!$out) {
        $out[] = '';
    }
    return $out;
}



function GRPS_translate_groupname_to_mgroupid($machines_list, $db)
{
    $machines_mgroupid = '';
    reset($machines_list);
    foreach ($machines_list as $m_key => $m_val) {
        $tmp_val = GRPS_find_machine_mgroupid($m_val, $db);

        if (isset($tmp_val[0])) {
            reset($tmp_val);
            foreach ($tmp_val as $k => $v) {

                if ($machines_mgroupid) {
                    $machines_mgroupid .= ',' . $v['mgroupid'];
                } else {
                    $machines_mgroupid = $v['mgroupid'];
                }
                echo "<br>translating machine(<b>$m_val</b>) to "
                    . "mgroupid(<b>" . $v['mgroupid'] . '</b>).<br><br>';
            }
        }
    }
    return $machines_mgroupid;
}



function GRPS_find_machine_mgroupid($machine, $db)
{
    $sql = "select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap on (" . $GLOBALS['PREFIX'] . "core.MachineGroups."
        . "mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq)\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
        . $GLOBALS['PREFIX'] . "core.Census.censusuniq)\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineCategories on (" . $GLOBALS['PREFIX'] . "core.MachineGroups."
        . "mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq)\n"
        . " where " . $GLOBALS['PREFIX'] . "core.Census.host='$machine'\n"
        . " and " . $GLOBALS['PREFIX'] . "core.MachineCategories.category='Machine'\n"
        . " and human = 0";
    return find_many($sql, $db);
}



function GRPS_get_multiselect_values($field)
{
    $set = get_argument($field, 0, array());
    $str = join(',', $set);
    return ($str) ? $str : '';
}




function GRPS_edit_group_detail($grp)
{
    $links = '';

    reset($grp);
    foreach ($grp as $int_index => $db_entry) {
        $tid    = $db_entry['mcatid'];
        $gid    = $db_entry['mgroupid'];
        $type   = $db_entry['style'];
        $name   = $db_entry['name'] . "<br>";

        $act    = ($type == constStyleManual) ? 'smmg' : 'emg';
        $args   = "act=$act&gid=$gid&tid=$tid";
        $href   = "../acct/groups.php?$args";
        $links .= html_page($href, $name);
    }
    return $links;
}



function GRPS_build_inuse_list($set, $type, $group_name)
{
    $out        = array();
    $report_ids = array();
    $report_str = '<table>';
    $rref       = '';
    $msg_rem    = '';

    if ($set) {

        switch ($type) {
            case constAssetReports:
                $nid = 'anid';
                $url = '../asset/report.php?act=edit&rid=';
                break;
            case constEventNotifications:
                $nid = 'rnid';
                $url = '../event/notify.php?act=edit&nid=';
                break;
            case constEventReports:
                $nid = 'enid';
                $url = '../event/report.php?act=edit&rid=';
                break;
        }
        $msg_rem = "Removing the group <b>$group_name</b>"
            . " will also remove it from the following $type:";

        reset($set);
        foreach ($set as $key => $report) {

            $id   = $report['id'];
            $name = $report['name'];
            $name = "<a href=$url${id}>$name</a>";
            $report_str  .= '<tr><td>' . $name . '</td></tr>';
            $report_ids[] = $id;
        }
        reset($report_ids);
        $report_ids_list = join(",", $report_ids);
        $rref           .= "&$nid=$report_ids_list";
        $report_str     .= '</table>';
    }
    $out['str']     = $report_str;
    $out['rref']    = $rref;
    $out['msg_rem'] = $msg_rem;
    return $out;
}



function GRPS_create_select_box(
    $auth,
    $table_type,
    $s_g,
    $env_g,
    $r_type,
    $db
) {

    $group_sel_list = GRPS_find_mgrp_list($auth, $r_type, $db);


    GRPS_insert_valid_entries($group_sel_list, $db);


    $list = GRPS_find_mgrp_name_pairs($table_type, $db);


    $list = GRPS_mgrp_arrange_list(
        $list,
        '(not displayed)',
        -1,
        '(all)',
        0,
        constMachineGroupMessage
    );

    return tiny_select($s_g, $list, $env_g, 1, 128);
}



function GRPS_find_machines_from_mgroupid($mgroupid, $auth, $db)
{
    $sql = "select C.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " where M.censusuniq = C.censusuniq\n"
        . " and M.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid IN ($mgroupid)\n"
        . " and U.customer = C.site\n"
        . " and U.username = '$auth'";
    return find_many($sql, $db);
}



function GRPS_create_list($type, $group, $auth, $db)
{
    $list = array();
    $set  = GRPS_find_machines_from_mgroupid($group, $auth, $db);

    reset($set);
    foreach ($set as $key => $db_entry) {
        $list[] = "'" . $db_entry['id'] . "'";
    }

    $str = join(",", $list);


    return ($type == constGroupInclude) ? " and temp_id in ($str)\n"    :
        " and temp_id not in ($str)\n";
}



function GRPS_build_host_list($group_include, $group_exclude, $auth, $db)
{
    debug_note("l-grps: group_include:($group_include) group_exclude:($group_exclude)");

    $exception = '';


    if (($group_include) && ($group_include != constMachineGroupDefaultALL)) {

        $exception  = GRPS_create_list(
            constGroupInclude,
            $group_include,
            $auth,
            $db
        );
    }
    if ($group_exclude) {

        $exception .= GRPS_create_list(
            constGroupExclude,
            $group_exclude,
            $auth,
            $db
        );
    }
    return $exception;
}



function GRPS_please_note()
{
    $please_note = <<< PLEASE
        <i>
        <font size=-2>
          Please note that clicking on 'configure groups' will cause you to
          lose any information you have entered on this page so far. If you
          right-click on 'configure groups' and open a new page, any new items
          will not be available on this page.
        </font>
        </i>
PLEASE;

    return $please_note;
}



function GRPS_include_instructions()
{
    $inc_ins = "<font size=-2>"
        . "The include parameter lets you specify a group of machines"
        . " that the report should cover. Only machines belonging to"
        . " this group can be included in the report. By default all"
        . " machines belonging to the report owner are included."
        . "</font>";
    return $inc_ins;
}



function GRPS_exclude_instructions($type)
{
    $s = GRPS_return_report_type($type);
    $exc_ins = "<font size=-2>"
        . "The exclude parameter lets you limit the $s"
        . " coverage to machines that may require special attention"
        . " by excluding all others. By default no machines are"
        . " excluded. The $s will cover all of 'included'"
        . " machines which are not 'excluded'"
        . "</font>";
    return $exc_ins;
}



function GRPS_return_report_type($type)
{
    switch ($type) {
        case constEventReports:
            return 'event report';
        case constAssetReports:
            return 'asset report';
        case constEventNotifications:
            return 'notification';
        default:
            return '';
    }
}



function preserve_report_state($rid, $act)
{
    return "report_id=$rid&report_act=$act";
}



function group_detail($grp)
{
    $name = '';
    reset($grp);
    foreach ($grp as $int_index => $db_entry) {
        $name .= $db_entry['name'] . "<br>";
    }
    return $name;
}



function GRPS_find_machineid_from_mgroupid($mgroupid, $db)
{
    $out = array();

    $sql = "drop table if exists temp_mgroupid";
    redcommand($sql, $db);

    $sql = "create temporary table temp_mgroupid(\n"
        . " mgid int(11) not null default 0)";
    redcommand($sql, $db);


    $mgroupid = explode(",", $mgroupid);

    reset($mgroupid);
    foreach ($mgroupid as $key => $mgid) {

        $sql = "insert into temp_mgroupid\n"
            . " set mgid = $mgid";
        redcommand($sql, $db);
    }

    $sql = "select distinct machineid from        \n"
        . " (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap as MGM,         \n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G)              \n"
        . " left join temp_mgroupid as TM        \n"
        . "   on (G.mgroupid = TM.mgid)          \n"
        . " left join " . $GLOBALS['PREFIX'] . "core.Census as C           \n"
        . "   on (MGM.censusuniq = C.censusuniq) \n"
        . " left join " . $GLOBALS['PREFIX'] . "asset.Machine as AM        \n"
        . "   on ( (HEX(AM.cust) = HEX(C.site))  \n"
        . "   and  (HEX(AM.host) = HEX(C.host)) )\n"
        . " where machineid IS NOT NULL          \n"
        . "  and MGM.mgroupuniq = G.mgroupuniq   \n"
        . "  and TM.mgid IS NOT NULL             \n";
    $set = find_many($sql, $db);
    reset($set);
    foreach ($set as $key => $db_entry) {
        $out[] = $db_entry['machineid'];
    }
    return $out;
}



function GRPS_find_common_from_machineid(
    $g_include,
    $g_exclude
) {

    if (($g_include) && (!$g_exclude)) {
        return $g_include;
    }


    if (($g_include) && ($g_exclude)) {
        return array_diff($g_include, $g_exclude);
    }


    return array();
}



function GRPS_update_AssetReports_group_include($db)
{
    $all_mgroupid = GRPS_ReturnAllMgroupid($db);
    $sql = "update " . $GLOBALS['PREFIX'] . "asset.AssetReports\n"
        . " set group_include = '$all_mgroupid'";
    redcommand($sql, $db);
}



function GRPS_create_wizard_title($type)
{
    switch ($type) {
        case constPageEntryScrpConf:
            return "Where would you like to configure scrips?";

        default:
            return "Where would you like to perform this action?";
    }
}



function GRPS_display_again($type)
{
    switch ($type) {
        case constPageEntryScrpConf:
            return false;
        default:
            return true;
    }
}


function GRPS_set_action($type)
{
    switch ($type) {
        case constPageEntryScrpConf:
            return '';
    }
}

function GRPS_GetPrecedence($category, $db)
{
    $qcategory = safe_addslashes($category);
    $sql = "SELECT precedence FROM MachineCategories WHERE category='$qcategory'";
    $row = find_one($sql, $db);
    if ($row) {
        return $row['precedence'];
    }
    return -1;
}
