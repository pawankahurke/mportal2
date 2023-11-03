<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 5-May-04   EWB     Created.
 6-May-04   EWB     Populate Builtin Machine Groups
 7-May-04   EWB     Create Category
10-May-04   EWB     Create Machine Groups
12-May-04   EWB     Display Site Machines.
13-May-04   EWB     Category Up, Category Down.
17-May-04   EWB     Edit Machine Group
18-May-04   EWB     Confirm Update Group, Update Group
19-May-04   EWB     Actions in category details.
20-May-04   EWB     Edit Expression
21-May-04   EWB     Debug Expressions.
24-May-04   EWB     Deleting a category recalculates precedence ordering.
26-May-04   EWB     MachineGroupMaps refer to census codes.
27-May-04   EWB     Evaluate expression finally works.
28-May-04   EWB     Select by Asset Query
28-May-04   EWB     Query name as description for asset/event query.
28-May-04   EWB     Edit update of dynamic group recalculates group members.
 1-Jun-04   EWB     Create group generates unique name.
 2-Jun-04   EWB     Edit group generates unique name.
 2-Jun-04   EWB     Column sort categories
 3-Jun-04   EWB     Column sort groups, sites
16-Jun-04   EWB     Factored mcat_options, mgrp_options into library.
 6-Jul-04   EWB     Precedence is now called priority.
 8-Jul-04   EWB     Factored 'census_dirty' code into library.
 9-Aug-04   EWB     Reorder machine categories must invalidate wuconfigcache.
18-Aug-04   EWB     The server no longer attempts to invalidate the cache.
30-Aug-04   EWB     moved unique_mcat, mcat_exits to library.
 9-Sep-04   EWB     site selection also shows total number of machines at site.
15-Nov-04   EWB     many small security problems.
17-Nov-04   EWB     expanded categories restricted to accessable machines.
 2-Feb-05   EWB     AAM's dramatic mysql performance improvement
 8-Feb-05   EWB     Editing a manual group can rename it.
 9-Feb-05   EWB     Warning for duplicate names.
 1-Sep-05   BJS     Removed event.Events.deleted, replace with core.Census.deleted.
12-Sep-05   BTE     Added checksum invalidation code.
12-Sep-05   BJS     Added l-abld.php
14-Sep-05   BTE     Update the revision level when the precedence changes for
                    the table core.MachineCategories.
15-Sep-05   BJS     Removed check_change().
22-Sep-05   BJS     Added 'details off' menu option, lib/l-grps.php.
14-Oct-05   BJS     Added all_machine_group_SQL().
20-Oct-05   BJS     Moved machine group SQL queries into l-grps.php.
25-Oct-05   BJS     Added l-grpw.php, removed duplicate procedures, renamed
                    conflicting procedures.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
10-Nov-05   BTE     Some delete operations should not be permanent.
28-Nov-05   BJS     Added 3rd arg to build_group_category_content.
06-Jan-06   BJS     UI fix per Alex.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
27-Jan-06   BTE     New parameter for insert_mgrp.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.
06-Mar-06   BTE     Fixed duplicate include.
06-Mar-06   BJS     Added l-syst.php.
14-Mar-06   BTE     Part of bug 3204: assorted text changes for group
                    management.
15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on
                    4.3 server.
18-Mar-06   AAM     Bug 3214: default override_sites=1, config_search=0.
11-Apr-06   BTE     Added include for l-gcfg.php.
20-Apr-06   BTE     Bug 3285: User interface group management issues from
                    emails.
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
                    correctly.
23-May-06   BTE     Bug 3360: Cannot remove a machine from an user-defined
                    group.
26-May-06   BTE     Bug 3293: Allow name changes for groups.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
24-Nov-06   AAM     Bug 3865: implemented consistent use of stripslashes with
                    contents of event filters.
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-slct.php');
include('../lib/l-rcmd.php');
include('../lib/l-jump.php');
include('../lib/l-form.php');
include('../lib/l-user.php');
include('../lib/l-gsql.php');
include('../lib/l-dids.php');
include('../lib/l-grps.php');
include('../lib/l-qtbl.php');
include('../lib/l-tabs.php');
include('../lib/l-alib.php');
include('../lib/l-abld.php');
include('../lib/l-qury.php');
include('../lib/l-drty.php');
include('../lib/l-gdrt.php');
include('../lib/l-rlib.php');
include('../lib/l-head.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');
include('../lib/l-grpw.php');
include('../lib/l-gcfg.php');
include('../lib/l-syst.php');
include('../lib/l-core.php');
include('../lib/l-tiny.php');

define('constButtonCan',  'Cancel');


function groups_title($act, $cat, $grp, $site)
{
    switch ($act) {
        case 'cup':;
        case 'cdn':;
        case 'ccat':;
        case 'dc':;
        case 'cats':
            return 'Category Management';
        case 'init':
            return 'Create Machine Groups';
        case 'eup':;
        case 'edn':;
        case 'cexp':
            return 'Machine Category Details';
        case 'dcat':
            return 'Debug Categories';
        case 'dgrp':
            return "Debug $cat Groups";
        case 'dhst':
            return "Debug $grp Machines";
        case 'dexp':
            return 'Debug Expressions';
        case 'ddgp':
            return "Debug Delete $grp";
        case 'cdc':
            return "Confirm Delete Category $cat";
        case 'cadd':
            return 'Add Machine Group Category';
        case 'call':
            return "Confirm Add All $site to $grp";
        case 'cnon':
            return "Confirm Remove All $site from $grp";
        case 'amgc':
            return "Add Machine Group for $cat";
        case 'amgx':
            return "Select Machines for $cat";
        case 'ccmg':
            return "Confirm Create Machine Group";
        case 'sall':;
        case 'smmx':;
        case 'snon':;
        case 'smmu':;
        case 'smmg':
            return "Select Machines for Machine Group $cat:$grp - Sites";
        case 'smmc':;
        case 'smms':
            return "Select Machines for Machine Group $cat:$grp - Site $site";
        case 'urg':;
        case 'eed':;
        case 'dg':;
        case 'rg':;
        case 'mgrp':
            return "Machine Category - $cat";
        case 'emg':
            return "Edit Machine Group $cat: $grp";
        case 'crg':;
        case 'cug':
            return "Confirm Update Machine Group $cat: $grp";
        case 'cdg':
            return "Confirm Delete Machine Group $cat: $grp";
        case 'uee':;
        case 'eex':;
        case 'ee':
            return "Edit Expression for Machine Group $cat:$grp";
        case 'ccc':
            return 'Confirm Create Category';
        case 'dmmg':
            return "Machine group $grp - Category $cat";
        case 'dmms':
            return "Display Machines Machine Group $cat:$grp - Site $site";
        case 'invl':
            return 'Invalid Operation';
        default:
            return 'Machine Groups';
    }
}


function green($msg)
{
    return "<font color=\"green\">$msg</font>";
}

function debug_walk_array($debug, $p)
{
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = green("$key: $data");
            echo "$msg<br>\n";
        }
    }
}

function groups_again(&$env)
{
    $comp = component_installed();
    $odir = $comp['odir'];
    $self = $env['self'];
    $dbg  = $env['priv'];
    $refr = $env['refr'];
    $tid  = $env['tid'];
    $act  = $env['act'];
    $cmd  = "$self?tid=$tid&act";
    $cst  = customURL(constPageEntryTools);
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link("/$odir/config/groups.php?$cst", 'main');
    $tmp = "|$act|";
    $txt = '|||mgrp|dg|rg|eed|';
    if ((strpos($txt, $tmp)) && ($tid)) {
        $a[] = html_link("$cmd=amgc&$cst", 'add');
        $a[] = html_link("$cmd=cats", 'back');
    }
    $txt = '|||cats|cup|cdn|dc|ccat|';
    if (strpos($txt, $tmp)) {
        $a[] = html_link("$cmd=cadd&$cst", 'add');
        $a[] = html_link("$cmd=cexp", 'expand');
    }
    $txt = '|||cexp|eup|edn|';
    if (strpos($txt, $tmp)) {
        $a[] = html_link("$cmd=cadd", 'add');
        $a[] = html_link("$cmd=cats", 'collapse');
    }
    $txt = '|||smmg|dmmg|sall|snon|';
    if ((strpos($txt, $tmp)) && ($tid)) {
        $a[] = html_link("$cmd=mgrp", 'back');
    }
    if (($act == 'dmms') && ($refr)) {
        $a[] = html_link($refr, 'back');
    }

    if ($dbg) {
        $a[] = html_link("$cmd=dcat", 'debug');
        $a[] = html_link("$cmd=init", 'init');
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $a[] = html_link('index.php', 'home');
        $a[] = html_link($href, 'again');
        $a[] = html_link($self, 'categories');
    }
    return jumplist($a);
}


function short_date($x)
{
    $text = '<br>';
    if ($x > 0) {
        //      $date = date('m/d/y',$x);
        //      $time = date('H:i:s',$x);
        //      $text = "$date<br>$time";
        $text = date('m/d/y H:i:s', $x);
    }
    return $text;
}

function find_scalar($sql, $db)
{
    $val = '';
    $res = command($sql, $db);
    if ($res) {
        $val = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $val;
}


function find_host_name($site, $host, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select * from Census\n"
        . " where host = '$qh'\n"
        . " and site = '$qs'";
    return find_one($sql, $db);
}

function find_host_xid($xid, $db)
{
    $row = array();
    if ($xid > 0) {
        $sql = "select * from Census\n"
            . " where id = $xid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function cat_names($db)
{
    $cat = array();
    $sql = 'select * from MachineCategories';
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $tid = $row['mcatid'];
        $cat[$tid] = $row['category'];
    }
    return $cat;
}


function find_hst_hid($hid, $db)
{
    $row = array();
    if ($hid > 0) {
        $sql = "select M.*, G.mgroupid, C.id as censusid, B.mcatid from"
            . " MachineGroupMap as M,\n"
            . " MachineGroups as G,\n"
            . " Census as C,\n"
            . " MachineCategories as B\n"
            . " where mgmapid = $hid"
            . " and M.mgroupuniq = G.mgroupuniq"
            . " and M.censusuniq = C.censusuniq"
            . " and M.mcatuniq = B.mcatuniq";
        $row = find_one($sql, $db);
    }
    return $row;
}


function find_expr_gid($gid, $db)
{
    $set = array();
    if ($gid > 0) {
        $sql = "select * from GroupExpression\n"
            . " where mgroupid = $gid\n"
            . " order by orterm, item, exprid";
        $set = find_many($sql, $db);
    }
    return $set;
}

function find_site_cid($cid, $auth, $db)
{
    $row = array();
    if (($cid > 0) && ($auth != '')) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from Customers\n"
            . " where id = $cid\n"
            . " and username = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}

function count_cats($db)
{
    $sql = "select count(*) from MachineCategories";
    return find_scaler($sql, $db);
}


function build_expr($neg, $tid, $val, $blk, $gid, $db)
{
    $num = 0;
    if (($tid > 0) && ($blk > 0)) {
        $sql = "insert into GroupExpression set\n"
            . " item = $val,\n"
            . " orterm = $blk,\n"
            . " negation = $neg,\n"
            . " mcatid = $tid,\n"
            . " mgroupid = $gid";
        $res = redcommand($sql, $db);
        if (affected($res, $db) == 1) {
            $num = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        }
    }
    return $num;
}



function find_host($tid, $gid, $xid, $db)
{
    $sql = "select * from MachineGroupMap\n"
        . " left join Census on (MachineGroupMap.censusuniq="
        . "Census.censusuniq) left join MachineGroups on (MachineGroupMap"
        . ".mgroupuniq=MachineGroups.mgroupuniq) left join "
        . "MachineCategories on (MachineGroupMap.mcatuniq="
        . "MachineCategories.mcatuniq)\n"
        . " where mcatid = $tid\n"
        . " and mgroupid = $gid\n"
        . " and id = $xid";
    return find_one($sql, $db);
}


function delete_host($hid, $db)
{
    $num = 0;
    if ($hid > 0) {
        $test = global_def('test_sql', 0);
        $err = constAppNoErr;
        if (!($test)) {
            $sql = "SELECT censusuniq, mgroupuniq FROM MachineGroupMap "
                . "WHERE mgmapid=$hid";
            $row = find_one($sql, $db);
            if ($row) {
                $err = PHP_VARS_HandleDeletedGroup(
                    CUR,
                    $row['censusuniq'],
                    $row['mgroupuniq']
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "delete_host: PHP_VARS_HandleDeletedGroup "
                        . "returned $err", 0);
                    return;
                }
            }
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$hid,
                "mgmapid",
                constDataSetCoreMachineGroupMap,
                constOperationPermanentDelete
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "delete_host: PHP_DSYN_InvalidateRow returned "
                    . $err, 0);
            }
        }
        if ($err == constAppNoErr) {
            $sql = "delete from\n"
                . " MachineGroupMap\n"
                . " where mgmapid = $hid";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
        }
    }
    return $num;
}

function delete_expr_eid($eid, $db)
{
    $num = 0;
    if ($eid > 0) {
        $sql = "delete from\n"
            . " GroupExpression\n"
            . " where exprid = $eid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function noprivs(&$env, $db)
{
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}


function unknown(&$env, $db)
{
    echo groups_again($env);
    $act = $env['act'];
    $msg = "Unknown action ($act)";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br><br>\n";
    echo groups_again($env);
}


function invalid(&$env, $db)
{
    echo groups_again($env);
    $msg = "Invalid Action";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br><br>\n";
    echo groups_again($env);
}



function rebuild(&$env, $db)
{
    echo groups_again($env);
    groups_init($db, constGroupsInitFull);
    echo groups_again($env);
}


function debug_cats(&$env, $db)
{
    echo groups_again($env);
    $sql = "select * from\n"
        . " MachineCategories\n"
        . " order by precedence";
    $set = find_many($sql, $db);
    if ($set) {
        $head = explode('|', 'id|priority|name|action');
        $cols = safe_count($head);
        $self = $env['self'];

        echo table_header();
        echo pretty_header('Categories', $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $tid  = $row['mcatid'];
            $name = $row['category'];
            $prec = $row['precedence'];
            $acts = "$self?act=dgrp&tid=$tid";
            $link = html_link($acts, '[groups]');
            $args = array($tid, $prec, $name, $link);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo groups_again($env);
}



function debug_grps(&$env, $db)
{
    echo groups_again($env);
    $set = array();
    if ($env['cat']) {
        $tid  = $env['cat']['mcatid'];
        $name = $env['cat']['category'];
        $sql = "select * from MachineGroups left join MachineCategories\n"
            . " on (MachineGroups.mcatuniq=MachineCategories.mcatuniq)\n"
            . " where mcatid = $tid\n"
            . " order by name";
        $set = find_many($sql, $db);
    } else {
        $name = 'All Groups';
        $sql = "select * from MachineGroups left join MachineCategories\n"
            . " on (MachineGroups.mcatuniq=MachineCategories.mcatuniq)\n"
            . " order by mcatid, name";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $head = explode('|', 'tid|gid|name|type|user|global|sid|qid|secs|action');
        $cols = safe_count($head);
        $rows = safe_count($set);
        $self = $env['self'];
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $tid  = $row['mcatuniq'];
            $gid  = $row['mgroupid'];
            $type = group_type($row['style']);
            $name = disp($row, 'name');
            $user = disp($row, 'username');
            $glob = $row['global'];
            $sid  = $row['eventquery'];
            $qid  = $row['assetquery'];
            $secs = $row['eventspan'];
            $act  = "$self?gid=$gid&tid=$tid&act";
            $view = html_link("$act=dhst", '[view]');
            $del  = html_link("$act=ddgp", '[delete]');
            $acts = "$view $del";
            $args = array($tid, $gid, $name, $type, $user, $glob, $sid, $qid, $secs, $acts);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo groups_again($env);
}

function debug_del_group($env, $db)
{
    if ($env['grp']) {
        $auth = $env['auth'];
        $gid  = $env['grp']['mgroupid'];
        $name = $env['grp']['name'];
        $type = $env['grp']['style'];
        debug_note("delete group '$name' ($gid)");
        if ($gid > 0) {
            kill_gid($gid, $db);
            $num = delete_host_gid($gid, $db);
            delete_expr_gid($gid, $db);
            //  invalidate_gid($gid,$db);
            delete_mgrp_gid($gid, $db);
            $stat = "g:$gid,n:$num,u:$auth";
            $text = "groups: mgrp removed ($stat) $name";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    debug_grps($env, $db);
}


function debug_hsts(&$env, $db)
{
    echo groups_again($env);
    $set = array();
    if ($env['grp']) {
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        $name = $env['grp']['name'];
        $sql = "select * from Census as C,\n"
            . " MachineGroupMap as M,\n"
            . " MachineGroups as G\n"
            . " where C.censusuniq = M.censusuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " order by site, host";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $head = explode('|', 'id|site|host|uuid|action');
        $cols = safe_count($head);
        $rows = safe_count($set);
        $self = $env['self'];
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = $row['mgmapid'];
            $tid  = $row['mcatid'];
            $gid  = $row['mgroupid'];
            $host = disp($row, 'host');
            $site = disp($row, 'site');
            $uuid = disp($row, 'uuid');
            $act  = "$self?gid=$gid&tid=$tid&act";
            $grp  = html_link("$act=dgrp", '[group]');
            $cat  = html_link("$act=dcat", '[cat]');
            $link = "$cat $grp";
            $args = array($hid, $site, $host, $uuid, $link);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo groups_again($env);
}



function debug_exps(&$env, $db)
{
    echo groups_again($env);
    $sql = "select * from\n"
        . " GroupExpression\n"
        . " order by mgroupid, orterm, item, exprid";
    $set = find_many($sql, $db);
    $sql = 'select * from MachineGroups';
    $grp = find_many($sql, $db);
    $cat = mcat_options($db);

    $gids = array();
    if ($grp) {
        $gids[0] = '<br>';
        reset($grp);
        foreach ($grp as $key => $row) {
            $gid  = $row['mgroupid'];
            $gids[$gid] = $row['name'];
        }
    }
    $grp = array();


    if (($set) && ($gids)) {
        $head = explode('|', 'gid|name|blk|neg|cat|val|name|eid');
        $cols = safe_count($head);
        $self = $env['self'];

        echo table_header();
        echo pretty_header('Expressions', $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $gid  = $row['mgroupid'];
            $tid  = $row['mcatid'];
            $blk  = $row['orterm'];
            $val  = $row['item'];
            $neg  = $row['negation'];
            $eid  = $row['exprid'];

            $not  = ($neg) ? 'not' : '<br>';
            $ggid = @trim($gids[$gid]);
            $gval = @trim($gids[$val]);
            $gcat = @trim($cat[$tid]);

            $args = array($gid, $ggid, $blk, $not, $gcat, $val, $gval, $eid);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo groups_again($env);
}


function groups_order($ord)
{
    switch ($ord) {
            // cats
        case  0:
            return 'precedence, mcatid';
        case  1:
            return 'precedence desc, mcatid';
        case  2:
            return 'category, mcatid';
        case  3:
            return 'category desc, mcatid';
        case  4:
            return 'number desc, category, mcatid';
        case  5:
            return 'number, category, mcatid';
            // groups
        case  6:
            return 'name, mgroupid';
        case  7:
            return 'name desc, mgroupid';
        case  8:
            return 'style, name, mgroupid';
        case  9:
            return 'style desc, name desc, mgroupid';
        case 10:
            return 'username, name, mgroupid';
        case 11:
            return 'username desc, name desc, mgroupid';
        case 12:
            return 'global, name, mgroupid';
        case 13:
            return 'global desc, name desc, mgroupid';
        case 14:
            return 'boolstring, mgroupid';
        case 15:
            return 'boolstring desc, mgroupid';
        case 16:
            return 'number desc, name, mgroupid';
        case 17:
            return 'number, name, mgroupid';
            // sites
        case 18:
            return 'site, cid';
        case 19:
            return 'site desc, cid';
        case 20:
            return 'number desc, site, cid';
        case 21:
            return 'number, site, cid';
        case 22:
            return 'total desc, site, cid';
        case 23:
            return 'total, site desc, cid';
        default:
            return groups_order(0);
    }
}



function display_cats(&$env, $db)
{
    debug_note("display_cats()");
    echo groups_again($env);
    echo "<br>\n";
    $ord = value_range(0, 5, $env['ord']);
    $wrd = groups_order($ord);
    $qu  = safe_addslashes($env['auth']);
    $sql = "select C.*, count(G.mcatuniq) as number\n"
        . " from MachineCategories as C\n"
        . " left join MachineGroups as G\n"
        . " on G.mcatuniq = C.mcatuniq\n"
        . " and (G.global = 1\n"
        . " or G.username = '$qu')\n"
        . " group by C.mcatid\n"
        . " order by $wrd";

    $set = find_many($sql, $db);
    $num = safe_count($set);
    $max = 0;

    if ($set) {
        $sql = 'select max(precedence) from MachineCategories';
        $max = intval(find_scalar($sql, $db));
    }

    if (($set) && ($max > 0)) {
        $self   = $env['self'];
        $admn   = $env['admn'];
        $cURL   = customURL($env['custom']);

        $o    = "$self?act=cats&ord";
        $pref = ($ord ==  0) ? "$o=1"  : "$o=0";
        $cref = ($ord ==  2) ? "$o=3"  : "$o=2";
        $nref = ($ord ==  4) ? "$o=5"  : "$o=4";

        $acts = 'Action';
        $cats = html_link($cref, 'Category');
        $prec = html_link($pref, 'Priority');
        $nums = html_link($nref, 'Number of Names');

        $head = array($acts, $cats, $prec, $nums);
        $rows = safe_count($set);
        $cols = safe_count($head);
        $text = "Categories &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $tid  = $row['mcatid'];
            $cats = $row['category'];
            $prec = $row['precedence'];
            $grps = $row['number'];
            $a    = array();
            $act  = "$self?tid=$tid&act";
            $a[]  = html_link("$act=cdc", '[delete]');
            if (($prec > 1) && ($ord == 0) && ($admn)) {
                $a[] = html_link("$act=cup", '[move up]');
            }
            if (($prec < $max) && ($ord == 0) && ($admn)) {
                $a[] = html_link("$act=cdn", '[move down]');
            }
            $acts = join('<br>', $a);
            $catlink = html_link("$act=mgrp&$cURL", $cats);
            $args = array($acts, $catlink, $prec, $grps);
            echo table_data($args, 0);
        }
        echo table_footer();

        debug_note("There are $num categories, max precedence is $max.");
    }

    echo groups_again($env);
}


/*
    |  moving a category up, which means trading places with the
    |  category above it ... and since the table is in precedence
    |  order moving up means decreasing precedence.
    */

function cat_up(&$env, $db)
{
    if ($env['cat']) {
        $tab  = 'MachineCategories';
        $tid  = $env['cat']['mcatid'];
        $name = $env['cat']['category'];
        $old  = $env['cat']['precedence'];
        $new  = $old - 1;
        debug_note("cat up id:$tid, name:$name, old:$old new:$new");
        if ($new > 0) {
            $uuu = "update $tab\n set revl=revl+1, precedence";
            $aaa = "$uuu = $old\n where precedence = $new";
            $bbb = "$uuu = $new\n where mcatid = $tid";

            $sql = "select mcatid from MachineCategories where "
                . "precedence=" . $new;
            $set = DSYN_DeleteSet(
                $sql,
                constDataSetCoreMachineCategories,
                "mcatid",
                "cat_up",
                0,
                1,
                constOperationDelete,
                $db
            );
            if ($set) {
                $test = global_def('test_sql', 0);
                $err = constAppNoErr;
                if (!($test)) {
                    $err = PHP_DSYN_InvalidateRow(
                        CUR,
                        (int)$tid,
                        "mcatid",
                        constDataSetCoreMachineCategories,
                        constOperationDelete
                    );
                    if ($err != constAppNoErr) {
                        logs::log(__FILE__, __LINE__, "cat_up: PHP_DSYN_InvalidateRow3 "
                            . "returned " . $err, 0);
                    }
                }

                if ($err == constAppNoErr) {
                    redcommand($aaa, $db);
                    redcommand($bbb, $db);

                    DSYN_UpdateSet(
                        $set,
                        constDataSetCoreMachineCategories,
                        "mcatid",
                        $db
                    );

                    DSYN_UpdateDependencies(
                        constDataSetCoreMachineCategories,
                        $tid,
                        $db
                    );
                }
            }
            //       invalidate_wcache($db);
        }
    }
    $act = $env['act'];

    if ($act == 'cup') {
        display_cats($env, $db);
    }
    if ($act == 'eup') {
        expand_cats($env, $db);
    }
}



/*
    |  moving a category down, which means trading places with the
    |  category below it ... and since the table is in precedence
    |  order moving down means increasing precedence.
    */

function cat_dn(&$env, $db)
{
    if ($env['cat']) {
        $tab  = 'MachineCategories';
        $tid  = $env['cat']['mcatid'];
        $name = $env['cat']['category'];
        $old  = $env['cat']['precedence'];
        $new  = $old + 1;
        debug_note("cat down id:$tid, name:$name, old:$old new:$new");
        $sql  = "select * from $tab where precedence = $new";
        $cat  = find_one($sql, $db);
        if ($cat) {
            $nid = $cat['mcatid'];
            $uuu = "update $tab\n set revl=revl+1, precedence";
            $aaa = "$uuu = $old\n where mcatid = $nid";
            $bbb = "$uuu = $new\n where mcatid = $tid";

            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$nid,
                "mcatid",
                constDataSetCoreMachineCategories,
                constOperationDelete
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "cat_dn: PHP_DSYN_InvalidateRow1 returned "
                    . $err, 0);
            }
            $err2 = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$tid,
                "mcatid",
                constDataSetCoreMachineCategories,
                constOperationDelete
            );
            if ($err2 != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "cat_dn: PHP_DSYN_InvalidateRow2 returned "
                    . $err2, 0);
            }

            if (($err == constAppNoErr) && ($err2 == constAppNoErr)) {
                /* These update statements are not changing any "uniq"
                        fields in MachineCategories and are fine as-is. */
                redcommand($aaa, $db);
                redcommand($bbb, $db);

                DSYN_UpdateDependencies(
                    constDataSetCoreMachineCategories,
                    $nid,
                    $db
                );
                DSYN_UpdateDependencies(
                    constDataSetCoreMachineCategories,
                    $tid,
                    $db
                );
            }
            //      invalidate_wcache($db);
        }
    }
    $act = $env['act'];
    if ($act == 'cdn') {
        display_cats($env, $db);
    }
    if ($act == 'edn') {
        expand_cats($env, $db);
    }
}


/*
    |
    | Here is a short discussion of the transformation:
    |
    | 1. Probably most important -- the way a LEFT JOIN works
    |    is that if there is nothing to join to in the right table, all the
    |    right table columns show up as NULL.  So, since you are already
    |    doing a LEFT JOIN between MachineGroups and MachineGroupMap,
    |    you can just select the ones where a column in MachineGroupMap is
    |    not NULL (you have to pick a column that is never NULL, preferably
    |    one that is defined as "NOT NULL").  You can do this in the WHERE
    |    clause, which is a lot faster than the unoptimized HAVING clause.
    |
    | 2. You want to do the ONs as early on as possible in the
    |    JOIN sequence.  So, I moved up the U.customer = C.site.
    |    Note that in order to do this I had to convert one of the commas
    |    into an INNER JOIN.  The mysql documentation says that a
    |    comma and an INNER JOIN are semantically equivalent, but
    |    I don't think a comma allows an ON.
    |
    | 3. You never want to do anything in an ON that can go into the
    |    WHERE, so I moved the U.username = '$qu' out of the ON into
    |    the WHERE.  In general, the ON is really only supposed to
    |    describe the conditions for the previous JOIN.
    */

function cat_groups(&$env, $db)
{
    debug_note("cat_groups()");
    $set = array();
    $cat = $env['cat'];
    $ord = value_range(6, 17, $env['ord']);

    echo groups_again($env);
    if ($cat) {
        $qu  = safe_addslashes($env['auth']);
        $wrd = groups_order($ord);
        $tid = $cat['mcatid'];
        /* ----------------------------------------------
            $sql = "select G.*, count(M.mgmapid) as number\n"
                 . " from MachineGroups as G,\n"
                 . " Customers as U,\n"
                 . " Census as C\n"
                 . " left join MachineGroupMap as M\n"
                 . " on M.mgroupid = G.mgroupid\n"
                 . " and M.censusid = C.id\n"
                 . " and C.site = U.customer\n"
                 . " and U.username = '$qu'\n"
                 . " where G.mcatid = $tid\n"
                 . " group by G.mgroupid\n"
                 . " having ((number > 0) or (G.username = '$qu'))\n"
                 . " order by $wrd";
            -------------------------------------------- */
        $sql = all_machine_group_SQL($tid, $qu, $wrd);
        $set = find_many($sql, $db);
    }

    if (($set) && ($cat)) {
        $self = $env['self'];
        $auth = $env['auth'];
        $cURL = customURL($env['custom']);
        $o    = "$self?act=mgrp&tid=$tid&ord";
        $nref = ($ord ==  6) ? "$o=7"  : "$o=6";
        $sref = ($ord ==  8) ? "$o=9"  : "$o=8";
        $oref = ($ord == 10) ? "$o=11" : "$o=10";
        $gref = ($ord == 12) ? "$o=13" : "$o=12";
        $bref = ($ord == 14) ? "$o=15" : "$o=14";
        $xref = ($ord == 16) ? "$o=17" : "$o=16";

        $aa   = array('Action');
        $aa[] = html_link($nref, 'Name');
        $aa[] = html_link($sref, 'Definition');
        $aa[] = html_link($bref, 'Definition Data');
        $aa[] = html_link($xref, 'Number of Machines');
        $aa[] = html_link($gref, 'Global');
        $aa[] = html_link($oref, 'Owner');

        $cols = safe_count($aa);
        $rows = safe_count($set);
        $name = $env['cat']['category'];
        $text = "Groups in Category $name&nbsp;($rows)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($aa, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $a    = array();
            $gid  = $row['mgroupid'];
            $type = $row['style'];
            $name = disp($row, 'name');
            $user = disp($row, 'username');
            $bool = disp($row, 'boolstring');
            $bool = nl2br($bool);
            $defs = group_type($row);
            $numb = $row['number'];
            $glob = ($row['global']) ? 'Yes' : 'No';
            $act  = "$self?$cURL&gid=$gid&tid=$tid&act";
            $dmmg = html_link("$act=dmmg", '[show machines]');
            $smmg = html_link("$act=smmg", '[show machines]');
            $edit = html_link("$act=emg", '[edit]');
            $del  = html_link("$act=cdg", '[delete]');
            $upd  = html_link("$act=crg", '[update]');
            if ($auth == $user) {
                $a[] = $edit;
                if ($type == constStyleManual)
                    $a[] = $smmg;
                else
                    $a[] = $dmmg;
                $a[] = $del;
                if (dynamic_group($type)) {
                    $a[] = $upd;
                }
            } else {
                $a[] = $dmmg;
            }

            $acts = join('<br>', $a);
            $args = array($acts, $name, $defs, $bool, $numb, $glob, $user);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo "<p>The category seems to be empty.</p>\n";
    }

    echo groups_again($env);
}


function group_display_host(&$env, $db)
{
    $set = array();

    echo groups_again($env);
    if (($env['hst']) && ($env['cen'])) {
        $tid = $env['hst']['mcatid'];
        $gid = $env['hst']['mgroupid'];
        $xid = $env['hst']['censusid'];
        $site = $env['cen']['site'];
        $qs  = safe_addslashes($site);
        $sql = "select C.*, M.mgmapid as hid\n"
            . " from Census as C,\n"
            . " MachineGroups as G\n"
            . " left join MachineGroupMap as M\n"
            . " on C.censusuniq = M.censusuniq\n"
            . " where C.site = '$qs'\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " order by host";
        $set = find_many($sql, $db);
        debug_note("site: $qs, gid:$gid, tid:$tid");
    }

    $num = safe_count($set);

    if ($set) {
        $head = explode('|', 'Selected|Machine');
        $cols = safe_count($head);

        echo table_header();
        echo pretty_header($site, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = @intval($row['hid']);
            $host = disp($row, 'host');
            $bool = ($hid) ? 'Yes' : 'No';
            $args = array($bool, $host);
            echo table_data($args, 0);
        }
        echo table_footer();
        debug_note("There are $num machines at this site.");
    } else {
        echo "<p>There are no machines at this site.</p>\n";
    }

    echo groups_again($env);
}

function create_cats(&$env, $db)
{
    echo groups_again($env);
    $in     = indent(5);
    $ok     = button('Ok');
    $cancel = cancel_link($env['custom']);
    $name   = textbox('name', 50, '');

    echo post_self('myform');
    echo hidden('act', 'ccc');
    echo "<p>Group Category: $name</p>";
    echo "<p>${ok}${in}${cancel}</p>";
    echo form_footer();
    echo groups_again($env);
}


function confirm_cat(&$env, $db)
{
    $name = $env['name'];
    echo groups_again($env);
    if ($name == '') {
        echo '<p>No name specified</p>';
    } else {
        $in  = indent(5);
        $yes = button('Yes');
        $no  = button('No');
        echo post_self('myform');
        echo hidden('act', 'ccat');
        echo hidden('name', $name);
        echo "<p>Would you like to create category <b>$name</b>?</p>\n";
        echo "<p>${yes}${in}${no}</p>\n";
        echo form_footer();
    }

    echo groups_again($env);
}

function confirm_del_cat(&$env, $db)
{
    echo groups_again($env);
    if ($env['cat']) {
        $in   = indent(5);
        $self = $env['self'];
        $tid  = $env['cat']['mcatid'];
        $name = $env['cat']['category'];
        $dc   = "$self?tid=$tid&act=dc";
        $yes  = html_link($dc, 'Yes');
        $no   = html_link($self, 'No');
        echo "<p>Are you sure you want to delete"
            .  " machine category <b>$name</b>?</p>"
            .  "<p>${yes}${in}${no}</p>\n";
    }
    echo groups_again($env);
}


function confirm_del_grp(&$env, $db)
{
    echo groups_again($env);
    if ($env['grp']) {
        $in   = indent(5);
        $self = $env['self'];
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        $name = $env['grp']['name'];
        $cURL = customURL($env['custom']);
        $act  = "$self?$cURL&gid=$gid&tid=$tid&act";
        $yes  = html_link("$act=dg", 'Yes');
        $no   = html_link("$act=mgrp", 'No');
        echo "<p>Are you sure you want to delete"
            .  " machine group <b>$name</b>?</p>"
            .  "<p>${yes}${in}${no}</p>\n";
    } else {
        /* if the user tries to delete an already removed group, log it */
        $gid = $env['gid'];
        echo "<p>Group has already been removed.</p>";
        logs::log(__FILE__, __LINE__, "groups: User tried to delete non-existent group(mgroupid:$gid)");
    }
    echo groups_again($env);
}


function delete_mgrp(&$env, $db)
{
    echo groups_again($env);
    if ($env['grp']) {
        $qu   = safe_addslashes($env['auth']);
        $tid  = $env['grp']['mcatid'];
        $name = $env['grp']['name'];
        $gid  = $env['grp']['mgroupid'];

        $sql = "SELECT mgroupuniq FROM MachineGroups WHERE mgroupid=$gid";
        $row = find_one($sql, $db);
        if ($row) {
            $err = PHP_VARS_HandleDeletedGroup(
                CUR,
                NULL,
                $row['mgroupuniq']
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "delete_mgrp: PHP_VARS_HandleDeletedGroup "
                    . "returned $err", 0);
                return;
            }
        }

        $sql = "select mgroupid from MachineGroups where mgroupid = "
            . "$gid and username = '$qu'";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreMachineGroups,
            "mgroupid",
            "delete_mgrp",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );
        $num = 0;
        if ($set) {
            $sql  = "delete from MachineGroups\n"
                . " where mgroupid = $gid\n"
                . " and username = '$qu'";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpInclude);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpExclude);
        }
        if ($num) {
            kill_gid($gid, $db);
            debug_note("group $name has been removed");
            $num = delete_host_gid($gid, $db);
            delete_expr_gid($gid, $db);
            //         invalidate_gid($gid,$db);

            $auth = $env['auth'];
            $stat = "g:$gid,n:$num,u:$auth";
            $text = "groups: mgrp removed ($stat) $name";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    cat_groups($env, $db);
}


function confirm_add_all(&$env, $db)
{
    echo groups_again($env);
    if (!$env['grp']) {
        echo "<p>Specified group not found.</p>\n";
    }

    if (!$env['sit']) {
        echo "<p>Specified site not found.</p>\n";
    }

    if (($env['grp']) && ($env['sit'])) {
        $in   = indent(5);
        $self = $env['self'];
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        $name = $env['grp']['name'];
        $site = $env['sit']['customer'];
        $cid  = $env['sit']['id'];
        $act  = "$self?cid=$cid&gid=$gid&tid=$tid&act";
        $yes  = html_link("$act=sall", 'Yes');
        $no   = html_link("$act=smmg", 'No');
        echo "<p>Are you sure you want to add"
            .  " all the machines<br> at <b>$site</b>"
            .  " to machine group <b>$name</b>?</p>"
            .  "<p>${yes}${in}${no}</p>";
    }
    echo groups_again($env);
}


function group_add_site(&$env, $db)
{
    if (!$env['grp']) {
        echo "<p>Specified group not found.</p>\n";
    }

    if (!$env['sit']) {
        echo "<p>Specified site not found.</p>\n";
    }

    if (($env['grp']) && ($env['sit'])) {
        $self = $env['self'];
        $gid  = $env['grp']['mgroupid'];
        $tid  = $env['grp']['mcatid'];
        $name = $env['grp']['name'];
        $site = $env['sit']['customer'];
        $cid  = $env['sit']['id'];
        $num  = mgrp_add_site($site, $tid, $gid, $db);
        if ($num) {
            $what = plural($num, 'machine');
            $text = "Added $what at <b>$site</b> to <b>$name</b>.";
            echo "<p>$text<p>\n";
        } else {
            echo "<p>No machines added.</p>\n";
        }
    }
    group_select_site($env, $db);
}

function group_del_site(&$env, $db)
{
    if (($env['grp']) && ($env['sit'])) {
        $self = $env['self'];
        $gid  = $env['grp']['mgroupid'];
        $name = $env['grp']['name'];
        $site = $env['sit']['customer'];
        $cid  = $env['sit']['id'];
        $qs   = safe_addslashes($site);

        $sql = "select distinct C.id\n"
            . " from Census as C,\n"
            . " MachineGroupMap as M,\n"
            . " MachineGroups as G\n"
            . " where C.censusuniq = M.censusuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and C.site = '$qs'";
        $num = 0;
        $set = find_many($sql, $db);
        if ($set) {
            $tmp = distinct($set, 'id');
            $txt = join(',', $tmp);

            $sql = "select mgmapid, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq, "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq from "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap "
                . "left join " . $GLOBALS['PREFIX'] . "core.Census on ( "
                . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq="
                . $GLOBALS['PREFIX'] . "core.Census.censusuniq) left join " . $GLOBALS['PREFIX'] . "core.MachineGroups "
                . "on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where "
                . "mgroupid=$gid and id in ($txt)";

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
                            "group_del_site: "
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
                "group_del_site",
                0,
                1,
                constOperationPermanentDelete,
                $db
            );

            $sql = "delete from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap using\n"
                . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap left join " . $GLOBALS['PREFIX'] . "core.Census on\n"
                . " (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq=\n"
                . $GLOBALS['PREFIX'] . "core.Census.censusuniq) left join " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
                . " on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)\n"
                . " where mgroupid = $gid and id in ($txt)";
            if ($set) {
                $res = redcommand($sql, $db);
                $num = affected($res, $db);
            }
        }
        echo "<p>$num machines removed from <b>$name</b>.</p>\n";
    }
    group_select_site($env, $db);
}

function confirm_add_none(&$env, $db)
{
    echo groups_again($env);
    if (!$env['grp']) {
        echo "<p>Specified group not found.</p>\n";
    }

    if (!$env['sit']) {
        echo "<p>Specified site not found.</p>\n";
    }

    if (($env['grp']) && ($env['sit'])) {
        $in   = indent(5);
        $self = $env['self'];
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        $name = $env['grp']['name'];
        $site = $env['sit']['customer'];
        $cid  = $env['sit']['id'];
        $act  = "$self?cid=$cid&gid=$gid&tid=$tid&act";
        $yes  = html_link("$act=snon", 'Yes');
        $no   = html_link("$act=smmg", 'No');
        echo "<p>Are you sure you want to remove"
            .  " all the machines<br>at <b>$site</b>"
            .  " from machine group <b>$name</b>?</p>"
            .  "<p>${yes}${in}${no}</p>";
    }
    echo groups_again($env);
}


/*
    |  The spec doesn't say who is allowed to delete
    |  a category, nor does it specify what happens
    |  afterwords.  It's sort of murky since categories
    |  have no owner, but groups can, and usually do.
    |
    |  So ... we walk the middle ground.
    |
    |     1. Anyone is allowed to attempt to delete a category.
    |     2. If they own groups in this category, all the groups
    |        they own in the category will be removed.
    |     3. Afterwords, if there are no groups left, the
    |        category itself will be deleted.
    |
    |  This means that anyone at all can remove a category
    |  without groups.
    |
    |  Note that built-in groups cannot be deleted, since
    |  they have no owner, and built-in categories cannot
    |  be deleted because they contain groups which cannot
    |  be removed.
    */

function delete_cat(&$env, $db)
{
    if ($env['cat']) {
        $gids = array();
        $auth = $env['auth'];
        $qu   = safe_addslashes($auth);
        $tid  = $env['cat']['mcatid'];
        $name = $env['cat']['category'];

        $sql = "select mgroupid from MachineGroups left join \n"
            . "MachineCategories on (MachineGroups.mcatuniq="
            . "MachineCategories.mcatuniq)\n where mcatid = $tid\n"
            . " and username = '$qu'";
        $set = find_many($sql, $db);
        foreach ($set as $key => $row) {
            $gid = $row['mgroupid'];
            //        invalidate_gid($gid,$db);
            delete_expr_gid($gid, $db);
            kill_gid($gid, $db);
            $gids[] = $gid;
        }
        $num = safe_count($gids);

        debug_note("there are $num groups found");

        if ($gids) {
            $txt = join(',', $gids);

            $sql = "select mgroupid, mgroupuniq from MachineGroups left "
                . "join MachineCategories on (MachineGroups.mcatuniq="
                . "MachineCategories.mcatuniq) where mcatid = "
                . "$tid and mgroupid in ($txt)";
            $set = find_many($sql, $db);
            if ($set) {
                foreach ($set as $key => $row) {
                    $err = PHP_VARS_HandleDeletedGroup(
                        CUR,
                        NULL,
                        $row['mgroupuniq']
                    );
                    if ($err != constAppNoErr) {
                        logs::log(__FILE__, __LINE__, "delete_cat: PHP_VARS_HandleDeletedGroup"
                            . " returned $err", 0);
                        return;
                    }
                }
            }

            $set = DSYN_DeleteSet(
                $sql,
                constDataSetCoreMachineGroups,
                "mgroupid",
                "delete_cat",
                0,
                1,
                constOperationPermanentDelete,
                $db
            );

            $sql = "delete from MachineGroups using MachineGroups\n"
                . " left join MachineCategories on ("
                . "MachineGroups.mcatuniq=MachineCategories.mcatuniq)\n"
                . " where mcatid = $tid and mgroupid in ($txt)";
            $num = 0;
            /* Wait until we have invalidated the MachineGroupMap records
                    before we delete the groups. */

            $sql = "select mgmapid from MachineGroupMap "
                . " left join MachineGroups on (MachineGroupMap."
                . "mgroupuniq=MachineGroups.mgroupuniq)\n left join "
                . "MachineCategories on (MachineGroupMap.mcatuniq="
                . "MachineCategories.mcatuniq)\n"
                . " where mcatid = $tid\n"
                . " and mgroupid in ($txt)";
            $set2 = DSYN_DeleteSet(
                $sql,
                constDataSetCoreMachineGroups,
                "mgmapid",
                "delete_cat",
                1,
                1,
                constOperationPermanentDelete,
                $db
            );

            if ($set && $set2) {
                $res = redcommand($sql, $db);
                $num = affected($res, $db);
                echo "<p>Removed $num groups.</p>\n";
            }

            $sql = "delete from MachineGroupMap using MachineGroupMap\n"
                . " left join MachineGroups on (MachineGroupMap."
                . "mgroupuniq=MachineGroups.mgroupuniq)\n left join "
                . "MachineCategories on (MachineGroupMap.mcatuniq="
                . "MachineCategories.mcatuniq)\n"
                . " where mcatid = $tid\n"
                . " and mgroupid in ($txt)";
            $xxx = 0;
            if ($set && $set2) {
                $res = redcommand($sql, $db);
                $xxx = affected($res, $db);
                echo "<p>Removed $xxx machines.</p>\n";
            }

            $stat = "t:$tid,g:$txt,n:$num,m:$xxx,u:$auth";
            $text = "groups: gang mgrp removed ($stat)";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);

            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpInclude);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpExclude);
        } else {
            echo "<p>You do not own any groups in this category.</p>";
        }
        $sql = "select mgroupid from MachineGroups\n left join "
            . "MachineCategories on (MachineGroups.mcatuniq="
            . "MachineCategories.mcatuniq)\n where mcatid = $tid";
        $set = find_many($sql, $db);
        if (!$set) {
            $pri = $env['cat']['precedence'];

            $test = global_def('test_sql', 0);
            $err = constAppNoErr;
            $num = 0;
            if (!($test)) {
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$tid,
                    "mcatid",
                    constDataSetCoreMachineCategories,
                    constOperationPermanentDelete
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "delete_cat: PHP_DSYN_InvalidateRow returned"
                        . " " . $err, 0);
                }
            }

            if ($err == constAppNoErr) {
                $sql = "delete from MachineCategories\n"
                    . " where mcatid = $tid";
                $res = redcommand($sql, $db);
                $num = affected($res, $db);
            }
            echo "<p>Removed $num categories.</p>\n";
            if (($num > 0) && (1 <= $pri)) {
                $name = $env['cat']['category'];
                $stat = "t:$tid,n:$num,p:$pri,u:$auth";
                $text = "groups: mcat removed ($stat) $name";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);

                $sql = "select mcatid from MachineCategories where "
                    . "precedence > " . $pri;
                $set_mcat = DSYN_DeleteSet(
                    $sql,
                    constDataSetCoreMachineCategories,
                    "mcatid",
                    "push_mcat",
                    1,
                    1,
                    constOperationDelete,
                    $db
                );

                /* This update statement will not affect any "uniq" columns
                        so it is fine as-is. */
                if ($set_mcat) {
                    $sql = "update MachineCategories\n"
                        . " set precedence = precedence-1,\n"
                        . " revl = revl+1\n"
                        . " where precedence > $pri";
                    $res = redcommand($sql, $db);

                    DSYN_UpdateSet(
                        $set_mcat,
                        constDataSetCoreMachineCategories,
                        "mcatid",
                        $db
                    );
                }
            }
        }
    }
    display_cats($env, $db);
}


function group_description($env, $db)
{
    $bool = '';
    if ($env['grp']) {
        $bool = $env['grp']['boolstring'];
    }
    $sid  = intval($env['sid']);
    $qid  = intval($env['qid']);
    $type = intval($env['type']);
    if ($type == constStyleManual) {
        $bool = 'Manual';
    }
    if ($type == constStyleAsset) {
        $qury = find_aquery($qid, $db);
        if ($qury) {
            $bool = $qury['name'];
        }
    }
    if ($type == constStyleEvent) {
        $qury = find_equery($sid, $db);
        if ($qury) {
            $bool = $qury['name'];
        }
    }
    return $bool;
}


function update_group(&$env, $db)
{
    if ($env['grp']) {
        $bool = $env['grp']['boolstring'];
        $text = $env['name'];
        $qu   = safe_addslashes($env['auth']);
        $gid  = $env['grp']['mgroupid'];
        $sid  = intval($env['sid']);
        $qid  = intval($env['qid']);
        $dd   = intval($env['day']);
        $hh   = intval($env['hour']);
        $mm   = intval($env['min']);
        $glob = ($env['glob']) ? 1 : 0;
        $type = intval($env['type']);
        $secs = ($dd * 86400) + ($hh * 3600) + ($mm * 60);
        $bool = group_description($env, $db);
        $name = unique_mgrp($text, $gid, $db);
        $qb   = safe_addslashes($bool);
        $qn   = safe_addslashes($name);

        $sql = "select mgroupid from MachineGroups where mgroupid=$gid"
            . " and username='$qu'";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreMachineGroups,
            "mgroupid",
            "update_group",
            0,
            1,
            constOperationDelete,
            $db
        );

        $sql  = "update MachineGroups set\n"
            . " name = '$qn',\n"
            . " global = $glob,\n"
            . " style = $type,\n"
            . " eventquery = $sid,\n"
            . " eventspan = $secs,\n"
            . " assetquery = $qid,\n"
            . " boolstring = '$qb',\n"
            . " revlname = revlname +1\n"
            . " where mgroupid = $gid and username = '$qu'";
        $num = 0;
        if ($set) {
            $res  = redcommand($sql, $db);
            $num  = affected($res, $db);
        }
        if ($num) {
            check_change($text, $name);
            $env['grp']['name'] = $name;
        }
        if ($set) {
            DSYN_UpdateSet(
                $set,
                constDataSetCoreMachineGroups,
                "mgroupid",
                $db
            );
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpInclude);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpExclude);
        }
    }
}



function update_grp(&$env, $db)
{
    echo groups_again($env);
    update_group($env, $db);
    echo groups_again($env);
}

function update_calc_grp(&$env, $db)
{
    update_group($env, $db);
    recalc_grp($env, $db);
}


function edit_mgroup(&$env, $db)
{
    debug_note("edit_mgroup()");
    echo groups_again($env);

    if ($env['grp']) {
        $name = $env['grp']['name'];
        $sid  = $env['grp']['eventquery'];
        $secs = $env['grp']['eventspan'];
        $qid  = $env['grp']['assetquery'];
        $glob = $env['grp']['global'];
        $type = $env['grp']['style'];

        $min  = round($secs / 60);
        $hour = round($secs / 3600);
        $day  = round($secs / 86400);

        $min  = intval($min  % 60);
        $hour = intval($hour % 24);
        $day  = intval($day);

        $in   = indent(5);
        $xn   = indent(12);
        $ok   = button('Ok');
        $can  = cancel_link($env['custom']);
        $name = textbox('name', 50, $name);

        $manu = radio('type', constStyleManual, $type);
        $evnt = radio('type', constStyleEvent, $type);
        $asst = radio('type', constStyleAsset, $type);
        $expr = radio('type', constStyleExpr, $type);

        $eopt = select_equery($env['auth'], $db);
        $aopt = select_aquery($env['auth'], $db);
        $sid  = html_select('sid', $eopt, $sid, 1);
        $qid  = html_select('qid', $aopt, $qid, 1);
        $day  = textbox('day', 3, $day);
        $hour = textbox('hour', 3, $hour);
        $min  = textbox('min', 3, $min);

        $glob = checkbox('glob', $glob);
        $mach = button('Select machines');
        $edit = button('Edit expression');
        echo post_self('myform');
        echo hidden('act', 'cug');
        echo hidden('tid',   $env['tid']);
        echo hidden('gid',   $env['gid']);
        echo hidden('custom', $env['custom']);
        echo "Name: $name<br>\n";
        echo "Global: $glob<br>\n";
        echo "Definition:<br>\n";
        echo "${in}$manu Manual $mach<br>\n";
        echo "${in}$evnt Event Query $sid<br>\n";
        echo "${xn}with time span $day days $hour hours $min minutes<br>\n";
        echo "${in}$asst Asset Query $qid<br>\n";
        echo "${in}$expr Expression $edit<br>\n";
        echo "${ok}${in}${can}<br>\n";
        echo form_footer();
    }
    echo groups_again($env);
}


/*
    |  17-Nov-04 EWB
    |
    |  This used to show the user all the global machine groups,
    |  plus all the groups that he personally owned.  The problem
    |  with that is that we don't want to show him any sites or
    |  machines or groups that he has no access to.
    |
    |  So, we are now restricting the set of machine groups
    |  so that a machine group must either be owned by the user,
    |  or the user must have access to at least one machine
    |  in that group.
    |
    |  Strangely, I've found that mysql takes over 40 seconds
    |  to directly calculate this list even on a tiny server
    |  like hollis, but it finishes in microseconds if we
    |  ask for each case separately.
    |
    |  So ... that's how we do it.
    */

function expand_cats(&$env, $db)
{
    echo groups_again($env);

    $auth = $env['auth'];
    $ord  = value_range(0, 3, $env['ord']);
    $wrd  = groups_order($ord);
    $cat  = build_machine_group_content($wrd, $db);
    $one  = build_group_category_content($auth, constQueryNoRestrict, $db);
    $num  = safe_count($cat);
    $grp  = array();
    $max  = 0;
    $own  = build_group_category_order($auth, $db);
    $grps = array();

    $grps = build_category_list($cat);
    $grps = build_group_category_list($one, $grps);
    $grps = build_group_category_list($own, $grps);

    if ($cat) {
        $max = build_max_precedence($db);
    }

    $own = array();
    $one = array();
    if (($cat) && ($grps) && ($max > 1)) {
        $self = $env['self'];
        $auth = $env['auth'];
        $admn = $env['admn'];

        $self = $env['self'];
        $o    = "$self?act=cexp&ord";
        $pref = ($ord == 0) ? "$o=1" : "$o=0";
        $cref = ($ord == 2) ? "$o=3" : "$o=2";

        $acts = 'Action';
        $cats = html_link($cref, 'Category');
        $prec = html_link($pref, 'Priority');
        $nums = 'Groups';
        $head = array($acts, $cats, $prec, $nums);
        $cols = safe_count($head);
        $rows = safe_count($cat);
        $text = "Machine Categories &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($cat);
        foreach ($cat as $key => $row) {
            $emgs = '';
            $tid  = $row['mcatid'];
            $cats = $row['category'];
            $prec = $row['precedence'];
            $mgrp = "$self?act=mgrp&tid=$tid";
            $link = html_link($mgrp, $cats);
            $set  = $grps[$tid];
            if ($set) {
                reset($set);
                foreach ($set as $gid => $grp) {
                    $name = $grp['name'];
                    $user = $grp['username'];
                    $act  = "$self?tid=$tid&gid=$gid&act";
                    $acts = "$act=dmmg";
                    $emgs .= (html_link($acts, $name) . '<br>');
                }
            } else {
                $emgs = '<br>';
            }
            $a    = array();
            $act  = "$self?tid=$tid&act";
            $a[]  = html_link("$act=cdc", '[delete]');
            $a[]  = html_link("$act=mgrp", '[show names]');
            if (($prec > 1) && ($ord == 0) && ($admn)) {
                $a[]  = html_link("$act=eup", '[move up]');
            }
            if (($prec < $max) && ($ord == 0) && ($admn)) {
                $a[]  = html_link("$act=edn", '[move down]');
            }
            $acts = join('<br>', $a);
            $args = array($acts, $link, $prec, $emgs);
            echo table_data($args, 0);
        }
        echo table_footer();
    }

    echo groups_again($env);
}


function select_equery($auth, $db)
{
    $list = array('   ');
    $prev = '';
    $qu  = safe_addslashes($auth);
    $sql = "select id, name\n"
        . " from event.SavedSearches\n"
        . " where username = '$qu'\n"
        . " or global = 1\n"
        . " order by name, global";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $name = $row['name'];
        $sid  = $row['id'];
        if ($name != $prev) {
            $list[$sid] = $name;
        }
    }
    return $list;
}


function select_aquery($auth, $db)
{
    $list = array('   ');
    $prev = '';
    $qu  = safe_addslashes($auth);
    $sql = "select id, name\n"
        . " from " . $GLOBALS['PREFIX'] . "asset.AssetSearches\n"
        . " where username = '$qu'\n"
        . " or global = 1\n"
        . " order by name, global";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $name = $row['name'];
        $qid  = $row['id'];
        if ($name != $prev) {
            $list[$qid] = $name;
        }
    }
    return $list;
}

function find_equery($sid, $db)
{
    $row = array();
    if ($sid > 0) {
        $sql = "select * from\n"
            . " event.SavedSearches\n"
            . " where id = $sid";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_aquery($qid, $db)
{
    $row = array();
    if ($qid > 0) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "asset.AssetSearches\n"
            . " where id = $qid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function group_add(&$env, $db)
{
    echo groups_again($env);
    $ok   = button('Ok');
    $in   = indent(5);
    $xn   = indent(12);
    $sp   = '&nbsp;';
    $can  = cancel_link($env['custom']);
    $name = textbox('name', 50, '');

    $type = constStyleManual;
    $manu = radio('type', constStyleManual, $type);
    $evnt = radio('type', constStyleEvent, $type);
    $asst = radio('type', constStyleAsset, $type);
    $expr = radio('type', constStyleExpr, $type);

    $eopt = select_equery($env['auth'], $db);
    $aopt = select_aquery($env['auth'], $db);
    $sid  = html_select('sid', $eopt, 0, 1);
    $qid  = html_select('qid', $aopt, 0, 1);
    $day  = textbox('day', 3, '0');
    $hour = textbox('hour', 3, '0');
    $min  = textbox('min', 3, '0');

    $glob = checkbox('glob', 1);
    $mach = button('Select machines');
    $edit = button('Edit expression');
    echo post_self('myform');
    echo hidden('act', 'ccmg');
    echo hidden('tid', $env['tid']);
    echo "Name: $name<br>\n";
    echo "Global: $glob<br>\n";
    echo "Definition:<br>\n";
    echo "${in}$manu Manual $mach<br>\n";
    echo "${in}$evnt Event Query $sid<br>\n";
    echo "${xn}with time span $day days $hour hours $min minutes<br>\n";
    echo "${in}$asst Asset Query $qid<br>\n";
    echo "${in}$expr Expression $edit<br>\n";
    echo "${ok}${in}${can}<br>\n";
    echo form_footer();
    echo groups_again($env);
}



function group_confirm_add(&$env, $db)
{
    echo groups_again($env);

    $name = $env['name'];
    $tid  = $env['tid'];
    $in   = indent(5);
    $yes  = button('Yes');
    $no   = button('No');
    $good = true;

    if ($name == '') {
        $good = false;
        echo "<p>Bad group name ... </p>";
    }

    if ($tid <= 0) {
        $good = false;
        echo "<p>Bad category ... </p>";
    }

    if ($good) {
        echo post_self('myform');
        echo hidden('act', 'smmx');
        echo hidden('qid', $env['qid']);
        echo hidden('sid', $env['sid']);
        echo hidden('tid', $env['tid']);
        echo hidden('glob', $env['glob']);
        echo hidden('name', $env['name']);
        echo hidden('type', $env['type']);
        echo hidden('hour', $env['hour']);
        echo hidden('day', $env['day']);
        echo hidden('min', $env['min']);

        echo "<p>Create new machine group <b>$name</b>?</p>\n";
        echo "<p>${yes}${in}${no}</p>\n";
        echo form_footer();
    }
    echo groups_again($env);
}


/*
    |  Decide what to do after we've done the update.
    |
    |  For dynamic groups, we want to recalculate them.
    |
    |  For manual groups, we want to edit the group membership.
    */

function groups_update_dispatch($type)
{
    switch ($type) {
        case constStyleInvalid:
            return 'invl';
        case constStyleBuiltin:
            return 'invl';
        case constStyleManual:
            return 'smmu';
        case constStyleEvent:
            return 'urg';
        case constStyleAsset:
            return 'urg';
        case constStyleExpr:
            return 'uee';
        default:
            return 'invl';
    }
}



function group_confirm_update(&$env, $db)
{
    debug_note("group_confirm_update()");
    echo groups_again($env);

    if ($env['grp']) {
        $in  = indent(5);
        $yes = button('Yes');
        $no  = button('No');

        $name = $env['grp']['name'];
        $act  = groups_update_dispatch($env['type']);
        echo post_self('myform');
        echo hidden('act',   $act);
        echo hidden('qid',   $env['qid']);
        echo hidden('gid',   $env['gid']);
        echo hidden('sid',   $env['sid']);
        echo hidden('tid',   $env['tid']);
        echo hidden('glob',  $env['glob']);
        echo hidden('name',  $env['name']);
        echo hidden('type',  $env['type']);
        echo hidden('hour',  $env['hour']);
        echo hidden('day',   $env['day']);
        echo hidden('min',   $env['min']);
        echo hidden('custom', $env['custom']);
        echo "<p>Update machine group <b>$name</b>?</p>\n";
        echo "<p>${yes}${in}${no}</p>\n";
        echo form_footer();
    }
    echo groups_again($env);
}

function confirm_recalc_grp(&$env, $db)
{
    debug_note("confirm_recalc_grp()");
    echo groups_again($env);

    if ($env['grp']) {

        $yes = button('Yes');
        $no  = button('No');
        $in  = indent(5);

        $name = $env['grp']['name'];
        echo post_self('myform');
        echo hidden('act', 'rg');
        echo hidden('gid', $env['gid']);
        echo hidden('tid', $env['tid']);

        echo "<p>Update machine group <b>$name</b>?</p>\n";
        echo "<p>${yes}${in}${no}</p>\n";
        echo form_footer();
    }
    echo groups_again($env);
}


function repopulate_group($list, $name, $tid, $gid, $db)
{
    delete_host_gid($gid, $db);

    if (($list) && ($gid) && ($tid)) {
        $num = 0;

        reset($list);
        foreach ($list as $site => $d) {
            foreach ($d as $host => $x) {
                $cen = find_host_name($site, $host, $db);
                if ($cen) {
                    $xid = $cen['id'];
                    $hid = build_host($tid, $gid, $xid, $db);
                    if ($hid) {
                        //                    invalidate_hid($xid,$db);
                        echo "<p>Adding <b>$host</b> to group <b>$name</b>.</p>\n";
                        $num++;
                    }
                }
            }
        }
        debug_note("added $num machines to group");
    }
}


function recalc_asset_query(&$env, $db)
{
    $gid  = 0;
    $mids = array();
    $list = array();
    $name = '';
    if ($env['grp']) {
        debug_note("relcalc_asset_query");
        $user = $env['grp']['username'];
        $name = $env['grp']['name'];
        $gid  = $env['grp']['mgroupid'];
        $tid  = $env['grp']['mcatid'];
        $qid  = $env['grp']['assetquery'];
        $type = $env['grp']['style'];
        $qury = find_aquery($qid, $db);

        if (($type == constStyleAsset) && ($qury)) {
            if (mysqli_select_db($db, asset)) {
                $auth = $env['auth'];
                $carr = $env['carr'];
                $ords = array(0, 0, 0, 0);
                $env['db'] = $db;
                $env['ords'] = $ords;
                $env['cron'] = 0;
                $env['site'] = '';
                $env['names'] = asset_names($db);
                $env['hosts'] = asset_machines($db);
                $env['access'][$auth] = db_access($carr);
                $q = query_query($env, $auth, $qid, $ords);
                $mids = $q['mids'];
                mysqli_select_db($db, core);
            }
        }
    }

    if ($mids) {
        foreach ($mids as $key => $mid) {
            $site = $env['hosts'][$mid]['cust'];
            $host = $env['hosts'][$mid]['host'];
            $list[$site][$host] = true;
        }
        $mids = array();
    }

    repopulate_group($list, $name, $tid, $gid, $db);
}


function recalc_event_query(&$env, $db)
{
    $list = array();
    $name = '';
    $set  = array();
    $gid  = 0;
    if (($env['grp']) && ($env['carr'])) {
        $carr = $env['carr'];
        $user = $env['grp']['username'];
        $name = $env['grp']['name'];
        $gid  = $env['grp']['mgroupid'];
        $tid  = $env['grp']['mcatid'];
        $sid  = $env['grp']['eventquery'];
        $secs = $env['grp']['eventspan'];
        $qid  = $env['grp']['assetquery'];
        $type = $env['grp']['style'];

        if (($type == constStyleEvent) && ($sid > 0) && ($secs > 0)) {
            $txt = db_access($carr);
            $row = find_equery($sid, $db);
            if ($row) {
                $msg = $row['name'];
                $ss  = stripslashes($row['searchstring']);
                $max = time();
                $min = $max - $secs;
                $sql = "select machine, customer\n"
                    . " from  " . $GLOBALS['PREFIX'] . "event.Events as E\n"
                    . " left join " . $GLOBALS['PREFIX'] . "core.Census as C\n"
                    . " on (E.customer = C.site)\n"
                    . " where C.deleted = 0\n"
                    . " and servertime between $min and $max\n"
                    . " and customer in ($txt)\n"
                    . " and ($ss)";
                debug_note("Using search '$msg' for past $secs seconds.");
                $set = find_many($sql, $db);
            }
        }
    }

    if ($set) {
        $num = safe_count($set);
        debug_note("The saved search found $num events.");
        reset($set);
        foreach ($set as $key => $row) {
            $site = $row['customer'];
            $host = $row['machine'];
            $list[$site][$host] = true;
        }

        $set = array();
    }

    repopulate_group($list, $name, $tid, $gid, $db);
}



function recalc_grp(&$env, $db)
{
    if ($env['grp']) {
        $type = $env['grp']['style'];
        $name = $env['grp']['name'];
        if (dynamic_group($type)) {
            echo "<p>Recalculate group <b>$name</b> ... </p>\n";
            switch ($type) {
                case constStyleBuiltin:
                    debug_note('not yet');
                    break;
                case constStyleEvent:
                    recalc_event_query($env, $db);
                    break;
                case constStyleAsset:
                    recalc_asset_query($env, $db);
                    break;
                case constStyleExpr:
                    recalc_expression($env, $db);
                    break;
                default:
                    debug_note("invalid recalc group $type");
            }
        }
    }
    cat_groups($env, $db);
}


function group_select_site(&$env, $db)
{
    debug_note("group_select_site");
    echo groups_again($env);
    $set = array();
    $grp = $env['grp'];
    $ord = value_range(18, 23, $env['ord']);
    if ($env['grp']) {
        $wrd = groups_order($ord);
        $qu  = safe_addslashes($env['auth']);
        $gid = $env['grp']['mgroupid'];
        $sql = "select X.site,\n"
            . " C.id as cid,\n"
            . " X.host as host\n"
            . " from Census as X,\n"
            . " Customers as C,\n"
            . " MachineGroups as G\n"
            . " left join MachineGroupMap as M\n"
            . " on  M.censusuniq = X.censusuniq\n"
            . " where C.username = '$qu'\n"
            . " and X.site = C.customer\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " order by $wrd";
        $set = find_many($sql, $db);
    }

    if (($set) && ($grp)) {
        $size = 0;
        $self = $env['self'];
        $tid  = $env['grp']['mcatid'];
        $name = $env['grp']['name'];
        $cURL = customURL($env['custom']);
        $o    = "$self?act=smmg&tid=$tid&gid=$gid&ord";
        $sref = ($ord == 18) ? "$o=19" : "$o=18";
        $nref = ($ord == 20) ? "$o=21" : "$o=20";
        $tref = ($ord == 22) ? "$o=23" : "$o=22";

        $head   = array('Action');
        $head[] = html_link($sref, 'Site Name');
        $head[] = 'Selected Machines';
        $head[] = 'Site Machines';

        reset($set);
        foreach ($set as $key => $row) {
            $size++;
        }

        $cols = safe_count($head);
        $msgs = plural($size, 'machine');
        $text = "$name &nbsp; ($msgs)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $cid = $row['cid'];
            $machines[$cid][] = $row['host'];
        }

        $sites = array();

        reset($set);
        foreach ($set as $key => $row) {
            $thisSite = $row['site'];
            if (@$sites[$thisSite]) {
                /* We already displayed this site. */
                continue;
            }
            $sites[$thisSite] = '1';
            $cid  = $row['cid'];
            $members = join('<br>', $machines[$cid]);
            $totl = safe_count($machines[$cid]);
            $site = disp($row, 'site');
            $a    = array();
            $act  = "$self?$cURL&cid=$cid&gid=$gid&tid=$tid&act";
            $a[]  = html_link("$act=call", '[select all]');
            $a[]  = html_link("$act=cnon", '[deselect all]');
            $a[]  = html_link("$act=smms", '[select/de-select individual]');
            $acts = join('<br>', $a);
            $args = array($acts, $site, $members, $totl);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo groups_again($env);
}


function group_select_host(&$env, $db)
{
    debug_note("group_select_host()");
    echo groups_again($env);
    $set = array();
    $grp = $env['grp'];
    $sit = $env['sit'];
    if (($env['grp']) && ($env['sit'])) {
        $qs  = safe_addslashes($env['sit']['customer']);
        $gid = $env['grp']['mgroupid'];
        $cid = $env['sit']['id'];
        $sql = "select C.host,\n"
            . " C.id as mid,\n"
            . " M.mgmapid as hid\n"
            . " from Census as C,\n"
            . " MachineGroups as G\n"
            . " left join MachineGroupMap as M\n"
            . " on C.censusuniq = M.censusuniq\n"
            . " where C.site = '$qs'\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " group by C.host\n"
            . " order by host";
        $set = find_many($sql, $db);
    }

    if ((!$set) || (!$sit)) {
        echo "<p>No machines found.</p>\n";
    }
    if (!$grp) {
        echo "<p>No group found.</p>\n";
    }

    if (($set) && ($grp) && ($sit)) {
        $all = 'Select all';
        $non = 'Deselect all';

        $flag_all = ($env['post'] == $all) ? true : false;
        $flag_non = ($env['post'] == $non) ? true : false;

        $site = $env['sit']['customer'];
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        $cid  = $env['sit']['id'];
        $in   = indent(5);
        $ok   = button('Ok');
        $can  = cancel_link($env['custom']);
        $all  = button($all);
        $non  = button($non);

        echo post_self('myform');
        echo hidden('act',   'smmc');
        echo hidden('gid',   $gid);
        echo hidden('tid',   $tid);    // for cancel
        echo hidden('cid',   $cid);
        echo hidden('custom', $env['custom']);
        echo "<p>${ok}${in}${can}</p>\n";
        echo "<p>${all}${in}${non}</p>\n";

        $head = explode('|', 'New|Old|Machine Name');
        $cols = safe_count($head);

        echo table_header();
        echo pretty_header($site, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = @intval($row['hid']);
            $olds = ($hid) ?       true : false;
            $news = ($flag_all) ?  true : $olds;
            $news = ($flag_non) ? false : $news;
            $mid  = $row['mid'];
            $host = disp($row, 'host');
            $acts = "mid:$mid, hid:$hid";
            $new  = checkbox("mid_$mid", $news);
            $old  = dcheckbox("old_$mid", $olds);
            $args = array($new, $old, $host);
            echo table_data($args, 0);
        }
        echo table_footer();
        echo "<br><p>${ok}${in}${can}</p>\n";
        echo form_footer();
    }
    echo groups_again($env);
}


function find_expr_tree($gid, $db)
{
    $tree = array();
    $set  = find_expr_gid($gid, $db);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $eid = $row['exprid'];
            $blk = $row['orterm'];
            $tree[$blk][$eid] = $row;
            //          debug_note("tree blk:$blk eid:$eid");
        }
    }
    return $tree;
}


function edit_expr(&$env, $db)
{
    echo groups_again($env);
    if ($env['grp']) {
        $in   = indent(5);
        $auth = $env['auth'];
        $bool = $env['grp']['boolstring'];
        $name = $env['grp']['name'];
        $gid  = $env['grp']['mgroupid'];
        $tid  = $env['grp']['mcatid'];

        $cat  = mcat_options($db);
        $opt  = mgrp_options($auth, $db);
        $tree = find_expr_tree($gid, $db);
        $not  = array('  ', 'not');

        $ok     = button('Ok');
        $done   = button('Done');
        $cancel = cancel_link($env['custom']);
        if ($bool) {
            $txt = str_replace("\n", "<br>\n&nbsp;&nbsp;&nbsp;", $bool);
            echo "Current expression:<br><p><b>$txt</b></p>\n";
        }

        echo post_self('myform');
        echo hidden('act', 'eex');
        echo hidden('gid', $gid);
        echo hidden('tid', $tid);

        $head = explode('|', 'delete|negate|category|group');
        $cols = safe_count($head);
        $blok = 0;

        echo table_header();
        echo pretty_header($name, $cols);
        echo table_data($head, 1);

        reset($tree);
        $none = array(indent(20));
        foreach ($tree as $blk => $d) {
            $blok = $blk;
            reset($d);
            foreach ($d as $eid => $row) {
                $gid  = $row['item'];
                $tid  = $row['mcatid'];
                $neg  = $row['negation'];
                $dval = "xdel_$eid";
                $nval = "xnot_$eid";
                $gval = "xgid_$eid";
                $cval = "xtid_$eid";
                $gopt = isset($opt[$tid]) ? $opt[$tid] : $none;
                $box  = checkbox($dval, 0);
                $negs = html_select($nval, $not, $neg, 1);
                $cats = js_select($cval, $cat, $tid, 1);
                $name = html_select($gval, $gopt, $gid, 1);
                $args = array($box, $negs, $cats, $name);
                echo table_data($args, 0);
            }
            $box  = '<br>';
            $nval = "aneg_$blk";
            $gval = "agid_$blk";
            $cval = "atid_$blk";
            $negs = html_select($nval, $not, 0, 1);
            $name = html_select($gval, $none, 0, 1);
            $cats = js_select($cval, $cat, 0, 1);
            $args = array($box, $negs, $cats, $name);
            echo table_data($args, 0);
            table_span($cols, 'OR');
        }

        $blk = $blok + 1;
        $negs = html_select('oneg', $not, 0, 1);
        $name = html_select('ogid', $none, 0, 1);
        $cats = js_select('otid', $cat, 0, 1);
        $args = array('<br>', $negs, $cats, $name);
        echo table_data($args, 0);
        echo table_footer();

        echo hidden('oblk', $blk);
        echo "<p>${ok}${in}${done}${in}${cancel}</p>";
        echo form_footer();
    }

    echo groups_again($env);
}

function update_expr(&$env, $db)
{
    update_group($env, $db);
    edit_expr($env, $db);
}

function groups_update_expression(&$env, $db)
{
    if ($env['grp']) {
        $b    = array();
        $auth = $env['auth'];
        $cat  = mcat_options($db);
        $opt  = mgrp_options($auth, $db);
        $ugid = $env['grp']['mgroupid'];
        $tree = find_expr_tree($ugid, $db);
        reset($tree);
        $cat[0] = '';
        $opt[0][0] = '';
        foreach ($tree as $blk => $d) {
            $a = array();
            reset($d);
            foreach ($d as $eid => $row) {
                $xdel = get_integer("xdel_$eid", 0);
                $xneg = get_integer("xnot_$eid", 0);
                $xtid = get_integer("xtid_$eid", 0);
                $xgid = get_integer("xgid_$eid", 0);

                $xcat = @trim($cat[$xtid]);
                $xgrp = @trim($opt[$xtid][$xgid]);

                if (($xgid) && (!$xgrp)) $xgid = 0;
                if (($xtid) && (!$xcat)) $xtid = 0;

                debug_note("evaluate blk:$blk eid:$eid del:$xdel neg:$xneg tid:$xtid gid:$xgid");
                if (($xdel) || ($xtid == 0)) {
                    debug_note("need to delete expr $eid");
                    delete_expr_eid($eid, $db);
                } else {
                    $etid = $row['mcatid'];
                    $egid = $row['item'];
                    $eneg = $row['negation'];
                    if (($xgid != $egid) || ($xneg != $eneg) || ($xtid != $etid)) {
                        $sql = "update GroupExpression set\n"
                            . " item = $xgid,\n"
                            . " mcatid = $xtid,\n"
                            . " negation = $xneg\n"
                            . " where exprid = $eid";
                        $res = redcommand($sql, $db);
                        $num = affected($res, $db);
                        debug_note("update expr $eid, neg:$xneg, tid:$xtid, gid:$xgid, num:$num");
                    }
                    if (($xgid) && ($xtid) && ($xcat) && ($xgrp)) {
                        $not  = ($xneg) ? 'not ' : '';
                        $name = "$xcat:$xgrp";
                        $txt  = $not . $name;
                        $a[]  = ($xneg) ? "($txt)" : $txt;
                    }
                }
            }
            $aneg = get_integer("aneg_$blk", 0);
            $agid = get_integer("agid_$blk", 0);
            $atid = get_integer("atid_$blk", 0);
            if ($atid) {
                $eid = build_expr($aneg, $atid, $agid, $blk, $ugid, $db);
                if (($eid) && ($agid)) {
                    $not  = ($aneg) ? 'not ' : '';
                    $acat = $cat[$atid];
                    $agrp = $opt[$atid][$agid];
                    $name = "$acat:$agrp";
                    $txt  = $not . $name;
                    $a[]  = ($aneg) ? "($txt)" : $txt;
                    debug_note("added new ($eid) neg:$aneg cat:$atid ($acat) group:$agid ($agrp) to blk:$blk");
                }
            }
            if ($a) {
                $txt = join(' and ', $a);
                $b[] = "($txt)";
            }
        }
        $oblk = get_integer('oblk', 0);
        $ogid = get_integer('ogid', 0);
        $oneg = get_integer('oneg', 0);
        $otid = get_integer('otid', 0);
        if (($oblk) && ($otid)) {
            $eid = build_expr($oneg, $otid, $ogid, $oblk, $ugid, $db);
            debug_note("new clause eid:$eid, blk:$oblk neg:$oneg tid:$otid gid:$ogid");
            if (($eid) && ($ogid)) {
                $not  = ($oneg) ? 'not ' : '';
                $ocat = $cat[$otid];
                $ogrp = $opt[$otid][$ogid];
                $name = "$ocat:$ogrp";
                $txt  = $not . $name;
                $b[]  = ($oneg) ? "($txt)" : $txt;
                debug_note("added new ($eid) neg:$oneg cat:$otid ($ocat) group:$ogid ($ogrp) to blk:$blk");
            }
        }
        if ($b) {
            $txt = join(" or \n", $b);
            $txt = "($txt)";
            $qb  = safe_addslashes($txt);
            $qu  = safe_addslashes($env['auth']);
            $gid = $env['grp']['mgroupid'];

            $sql = "select mgroupid from MachineGroups where "
                . "mgroupid=$gid and username='$qu'";
            $set = DSYN_DeleteSet(
                $sql,
                constDataSetCoreMachineGroups,
                "mgroupid",
                "groups_update_expression",
                0,
                1,
                constOperationDelete,
                $db
            );

            /* This update does not change any "uniq" fields and is fine
                    as-is. */
            $sql = "update MachineGroups set\n"
                . " boolstring = '$qb'\n"
                . " where mgroupid = $gid\n"
                . " and username = '$qu'";
            if ($set) {
                $res = redcommand($sql, $db);
                $env['grp']['boolstring'] = $txt;

                DSYN_UpdateSet(
                    $set,
                    constDataSetCoreMachineGroups,
                    "mgroupid",
                    $db
                );
            }
        }
    }
}


function update_expr_expr(&$env, $db)
{
    groups_update_expression($env, $db);
    edit_expr($env, $db);
}


/*
    |   The expression is an and/or tree.
    |
    |   For each block, we start out with the list of
    |   all the mechines we are allowed to access.
    |
    |   Then to evaluate each AND term, we just figure
    |   out what needs to be removed.  If it is an
    |   AND NOT term we just need to remove the specified
    |   group.
    |
    |   If the term is an AND then we need to remove all
    |   the machines that DO NOT belong to the specified
    |   group.
    |
    |   Currently this must be done in php because multi-table
    |   delete doesn't exist in mysql 3.
    |
    |   When we finish evaluating the and terms, whatevers still
    |   left in "block" is our answer, and we perform the OR
    |   operation with an insert ignore into "final"
    |
    |   There is nothing stopping the user from creating a
    |   circular loop of definitions ... but remember that
    |   update only goes one step.
    */

function recalc_expression(&$env, $db)
{
    $gid  = 0;
    $tree = array();
    $type = constStyleInvalid;
    debug_note("recalc_expression");
    if ($env['grp']) {
        $name = $env['grp']['name'];
        $type = $env['grp']['style'];
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        if ($type == constStyleExpr) {
            $tree = find_expr_tree($gid, $db);
        }
    }
    if ($tree) {
        $qu = safe_addslashes($env['auth']);
        create_temp_table('access', $db);
        create_temp_table('final', $db);
        create_temp_table('block', $db);
        $sql = "insert into access\n"
            . " select C.id from Census as C,\n"
            . " Customers as U\n"
            . " where U.username = '$qu'\n"
            . " and U.customer = C.site";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        debug_note("access contains $num machines");
        reset($tree);
        foreach ($tree as $blk => $d) {
            $sql = "insert into block\n"
                . " select id from access";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            reset($d);
            foreach ($d as $eid => $row) {
                $tag = $row['item'];
                $neg = $row['negation'];
                if ($neg) {
                    $exp = "and not group $tag";
                    $sql = "select distinct b.id from\n"
                        . " block as b,\n"
                        . " MachineGroupMap as m,\n"
                        . " Census as c,\n"
                        . " MachineGroups as g\n"
                        . " where c.censusuniq = m.censusuniq\n"
                        . " and b.id = c.id\n"
                        . " and m.mgroupuniq = g.mgroupuniq\n"
                        . " and g.mgroupid = $tag"
                        . " order by id";
                } else {
                    $exp = "and group $tag";
                    $sql = "select distinct b.id from\n"
                        . " block as b,\n"
                        . " MachineGroups as g,\n"
                        . " Census as c\n"
                        . " left join MachineGroupMap as m\n"
                        . " on (m.mgroupuniq = g.mgroupuniq and"
                        . " m.censusuniq = c.censusuniq) "
                        . " where m.mgmapid is null\n"
                        . " and g.mgroupid = $tag\n"
                        . " and c.id = b.id\n"
                        . " order by id";
                }
                $set = find_many($sql, $db);
                if ($set) {
                    $ids = distinct($set, 'id');
                    $txt = join(',', $ids);
                    $sql = "delete from block\n"
                        . " where id in ($txt)";
                    $res = redcommand($sql, $db);
                    $num = affected($res, $db);
                    debug_note("block $blk term $eid: $exp ($num removed)");
                }
            }
            $sql = "insert ignore into final\n"
                . " select * from block";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            debug_note("block $blk: $num rows inserted");
            $sql = 'delete from block';
            redcommand($sql, $db);
        }

        /*
            |  We postpone this step until just before
            |  stuffing the final result into the group,
            |  just in case the expression contained a
            |  circular definition.
            */

        delete_host_gid($gid, $db);

        $test = global_def('test_sql', 0);
        $sql = "select id from final";
        $set = find_many($sql, $db);
        $num = 0;
        if ($set) {
            foreach ($set as $key => $row) {
                $sql = CORE_CreateMachineGroupMap($row['id'], $tid, $gid);
                $res = redcommand($sql, $db);
                $num += affected($res, $db);
                if (!($test)) {
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
                            "recalc_expression: "
                                . "PHP_DSYN_InvalidateRow returned " . $err,
                            0
                        );
                    }
                }
            }
        }

        debug_note("there are now $num machines in this group");
        drop_temp_table('block', $db);
        drop_temp_table('access', $db);
        drop_temp_table('final', $db);
    } else {
        delete_host_gid($gid, $db);
    }
}

function edit_expr_done(&$env, $db)
{
    groups_update_expression($env, $db);
    recalc_expression($env, $db);
    cat_groups($env, $db);
}

function site_checks(&$env, $db)
{
    if (($env['sit']) && ($env['grp'])) {
        $name = $env['grp']['name'];
        $tid  = $env['grp']['mcatid'];
        $gid  = $env['grp']['mgroupid'];
        $qs   = safe_addslashes($env['sit']['customer']);
        $sql  = "select * from Census\n"
            . " where site = '$qs'";
        $set  = find_many($sql, $db);

        reset($set);
        foreach ($set as $key => $row) {
            $site = $row['site'];
            $host = $row['host'];
            $uuid = $row['uuid'];
            $mid  = $row['id'];
            $sel  = get_integer("mid_$mid", 0);

            debug_note("mid:$mid sel:$sel site:$site host:$host");
            $map  = find_host($tid, $gid, $mid, $db);
            if (($map) && (!$sel)) {
                $hid = $map['mgmapid'];
                delete_host($hid, $db);
                //            invalidate_hid($mid,$db);
                echo "<p>Removed machine <b>$host</b> from <b>$name</b>.<p>\n";
            }
            if (($sel) && (!$map)) {
                $xxx = build_host($tid, $gid, $mid, $db);
                if ($xxx) {
                    //               invalidate_hid($mid,$db);
                    echo "<p>Added machine <b>$host</b> to <b>$name</b>.<p>\n";
                }
            }
        }
    }
}


function group_check_host(&$env, $db)
{
    debug_note("group_check_host()");
    if ($env['post'] == 'Ok') {
        /* In the event the user has clicked ok,
            | we need to perform the requested action
            | (if any) and return the user to the
            | previous page. 
           */
        site_checks($env, $db);
        update_manual($env, $db);
    } else {
        group_select_host($env, $db);
    }
}


function create_grp(&$env, $db)
{
    $min  = $env['min'];
    $day  = $env['day'];
    $sid  = $env['sid'];
    $qid  = $env['qid'];
    $tid  = $env['tid'];
    $text = $env['name'];
    $auth = $env['auth'];
    $glob = $env['glob'];
    $type = $env['type'];
    $hour = $env['hour'];
    $bool = group_description($env, $db);
    $secs = ($day * 86400) + ($hour * 3600) + ($min * 60);
    $name = unique_mgrp($text, 0, $db);
    $gid  = insert_mgrp(
        $tid,
        $name,
        $auth,
        $glob,
        1,
        $type,
        $sid,
        $secs,
        $qid,
        $bool,
        '',
        $db
    );

    if ($gid) {
        check_change($text, $name);
        $env['gid'] = $gid;
        $env['grp'] = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        if ($type == constStyleManual) group_select_site($env, $db);
        if ($type == constStyleEvent)  recalc_grp($env, $db);
        if ($type == constStyleAsset)  recalc_grp($env, $db);
        if ($type == constStyleExpr)   edit_expr($env, $db);
    } else {
        echo groups_again($env, $db);
        echo "<p>Could not create a new group.</p>\n";
        echo groups_again($env, $db);
    }
}


/*
    |  This shows a list of sites for some specified
    |  machine group.  If it turns out that the user
    |  shouldn't be allowed to access the site, we'll
    |  just lie and say the site has no machines.
    */

function group_display_site(&$env, $db)
{
    $set = array();
    $gid = $env['gid'];
    $ord = value_range(18, 21, $env['ord']);
    echo groups_again($env);
    if ($env['grp']) {
        $qu  = safe_addslashes($env['auth']);
        $tid = $env['grp']['mcatid'];
        $gid = $env['grp']['mgroupid'];
        $wrd = groups_order($ord);
        $sql = "select C.site,\n"
            . " min(C.id) as cid,\n"
            . " count(C.site) as number,\n"
            . " min(M.mgmapid) as hid\n"
            . " from Census as C,\n"
            . " Customers as U,\n"
            . " MachineGroupMap as M,\n"
            . " MachineGroups as G\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and C.censusuniq = M.censusuniq\n"
            . " and C.site = U.customer\n"
            . " and U.username = '$qu'\n"
            . " group by site\n"
            . " order by $wrd";
        $set = find_many($sql, $db);
    }


    if ($set) {
        $self = $env['self'];
        $name = $env['grp']['name'];
        $o    = "$self?act=dmmg&tid=$tid&gid=$gid&ord";
        $sref = ($ord == 18) ? "$o=19" : "$o=18";
        $nref = ($ord == 20) ? "$o=21" : "$o=20";

        $aa   = array('Action');
        $aa[] = html_link($sref, 'Site Name');
        $aa[] = html_link($nref, 'Number of Machines Selected');
        $cols = safe_count($aa);
        $rows = safe_count($set);
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($aa, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $site = $row['site'];
            $numb = $row['number'];
            $hid  = $row['hid'];
            $dmms = "$self?act=dmms&hid=$hid";
            $acts = html_link($dmms, '[show machines]');
            $args = array($acts, $site, $numb);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo "<p>This group contains no machines.</p>";
    }
    echo groups_again($env);
}



/*
    |  The precedence of a new category is controlled
    |  by a server variable override_sites.  If
    |  override_sites is enabled (the default), then we assume that
    |  new categories are more important than sites
    |  and we will displace ($sites+1).
    |
    |  If override_sites is disabled, we'll assume that the new
    |  category is less important than sites are, and
    |  therefore we will take over the current precedence
    |  of sites, and promote sites and everything above it.
    */

function make_cat(&$env, $db)
{
    $text = $env['name'];

    if ($text != '') {
        $site = find_mcat_name(constCatSite, $db);
        $over = server_int('override_sites', 1, $db);
        if ($site) {
            $pre = $site['precedence'];
            $pre = ($over) ? $pre + 1 : $pre;
            $name = unique_mcat($text, 0, $db);
            push_mcat($pre, $db);
            build_mcat($name, $pre, $db);
        }
    }
    display_cats($env, $db);
}


function update_manual($env, $db)
{
    update_group($env, $db);
    group_select_site($env, $db);
}


/*
    |  Cancel out of update group goes back to groups page.
    */

function groups_redirect_action($act, $post)
{
    if ($post == 'Cancel') {
        if ($act == 'usp')  $act = 'cats';
        if ($act == 'ump')  $act = 'cats';
        if ($act == 'smmc') $act = 'mgrp';
        if ($act == 'cug')  $act = 'mgrp';
        if ($act == 'eex')  $act = 'mgrp';
        if ($act == 'ccc')  $act = 'cats';
    }
    if ($post == 'Done') {
        if ($act == 'eex')  $act = 'eed';
        if ($act == 'ee')   $act = 'eed';
    }
    if ($post == 'No') {
        if ($act == 'ccat') $act = 'cats';
        if ($act == 'ccmg') $act = 'cats';
        if ($act == 'ump')  $act = 'cats';
        if ($act == 'umg')  $act = 'mgrp';
    }
    return $act;
}



/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$act  = get_string('act', 'cats');
$post = get_string('button', '');
$name = get_string('name', '');

$tid = get_integer('tid', 0);   // core.MachineCategory
$gid = get_integer('gid', 0);   // core.MachineGroups
$cid = get_integer('cid', 0);   // core.Customers
$hid = get_integer('hid', 0);   // core.MachineGroupMap
$xid = get_integer('xid', 0);   // core.Census

$custom = get_integer('custom', 0);

$hst = find_hst_hid($hid, $db);
if ($hst) {
    if (!$gid) $gid = $hst['mgroupid'];
    if (!$tid) $tid = $hst['mcatid'];
    if (!$xid) $xid = $hst['censusid'];
}
$grp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
$cat = find_mcat_tid($tid, $db);
$sit = find_site_cid($cid, $auth, $db);
$cen = find_host_xid($xid, $db);

$catname = ($cat) ? $cat['category']  : '';
$grpname = ($grp) ? $grp['name']      : '';
$site    = ($cen) ? $cen['site']      : '';

if (($sit) && (!$site)) {
    $site = $sit['customer'];
}

$act   = groups_redirect_action($act, $post);
$title = groups_title($act, $catname, $grpname, $site);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, '', '', 0, $db);

$dbg  = get_integer('debug', 1);
$secs = get_integer('secs', 300);
$limt = get_integer('limit', 10);
$user  = user_data($auth, $db);
$priv  = @($user['priv_debug']) ?   1  : 0;
$debug = @($user['priv_debug']) ? $dbg : 0;
$admin = @($user['priv_admin']) ?   1  : 0;
$flt   = @($user['filtersites']) ?  1  : 0;
$carr  = site_array($auth, $flt, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($priv) {
    $date = datestring($now);
    echo "<h2>$date</h2>";
}

$tmp = "|$act|";
if ($priv) {
    debug_walk_array($debug, $_POST);
} else {
    $txt = '|||dcat|dgrp|dhst|dexp|ddg|init|';
    if (strpos($txt, $tmp)) {
        $act = 'cats';
    }
}
if (!$admin) {
    if (strpos('|||cup|cdn|', $tmp)) {
        $act = 'cats';
    }
    if (strpos('|||eup|edn|', $tmp)) {
        $act = 'cexp';
    }
}

$env = array();
$env['custom'] = $custom;
$env['sp']   = '&nbsp;';
$env['now']  = time();
$env['tid']  = $tid;
$env['hid']  = $hid;
$env['cid']  = $cid;
$env['gid']  = $gid;
$env['act']  = $act;
$env['cat']  = $cat;
$env['grp']  = $grp;
$env['hst']  = $hst;
$env['sit']  = $sit;
$env['cen']  = $cen;
$env['sid']  = get_integer('sid', 0);
$env['qid']  = get_integer('qid', 0);
$env['day']  = get_integer('day', 0);
$env['min']  = get_integer('min', 0);
$env['ord']  = get_integer('ord', -1);
$env['glob'] = get_integer('glob', 0);
$env['name'] = $name;
$env['post'] = $post;
$env['desc'] = get_string('desc', '');
$env['hour'] = get_integer('hour', 0);
$env['type'] = get_integer('type', 0);
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['refr'] = server_var('HTTP_REFERER');
$env['limt'] = value_range(3, 500, $limt);
$env['secs'] = value_range(1, 86400, $secs);
$env['auth'] = $auth;
$env['priv'] = $priv;
$env['admn'] = $admin;
$env['carr'] = $carr;

switch ($act) {
    case 'cup':
        cat_up($env, $db);
        break;
    case 'eup':
        cat_up($env, $db);
        break;
    case 'cdn':
        cat_dn($env, $db);
        break;
    case 'edn':
        cat_dn($env, $db);
        break;
    case 'cats':
        display_cats($env, $db);
        break;
    case 'ccat':
        make_cat($env, $db);
        break;
    case 'dc':
        delete_cat($env, $db);
        break;
    case 'cexp':
        expand_cats($env, $db);
        break;
    case 'cadd':
        create_cats($env, $db);
        break;
    case 'call':
        confirm_add_all($env, $db);
        break;
    case 'cnon':
        confirm_add_none($env, $db);
        break;
    case 'cdc':
        confirm_del_cat($env, $db);
        break;
    case 'ccc':
        confirm_cat($env, $db);
        break;
    case 'cdg':
        confirm_del_grp($env, $db);
        break;
    case 'dg':
        delete_mgrp($env, $db);
        break;
    case 'emg':
        edit_mgroup($env, $db);
        break;
    case 'cug':
        group_confirm_update($env, $db);
        break;
    case 'crg':
        confirm_recalc_grp($env, $db);
        break;
    case 'rg':
        recalc_grp($env, $db);
        break;
    case 'umg':
        update_grp($env, $db);
        break;
    case 'urg':
        update_calc_grp($env, $db);
        break;
    case 'mgrp':
        cat_groups($env, $db);
        break;
    case 'ee':
        edit_expr($env, $db);
        break;
    case 'eed':
        edit_expr_done($env, $db);
        break;
    case 'uee':
        update_expr($env, $db);
        break;
    case 'eex':
        update_expr_expr($env, $db);
        break;
    case 'amgc':
        group_add($env, $db);
        break;
    case 'ccmg':
        group_confirm_add($env, $db);
        break;
    case 'dmmg':
        group_display_site($env, $db);
        break;
    case 'dmms':
        group_display_host($env, $db);
        break;
    case 'sall':
        group_add_site($env, $db);
        break;
    case 'snon':
        group_del_site($env, $db);
        break;
    case 'smmu':
        update_manual($env, $db);
        break;
    case 'smmg':
        group_select_site($env, $db);
        break;
    case 'smms':
        group_select_host($env, $db);
        break;
    case 'smmc':
        group_check_host($env, $db);
        break;
    case 'smmx':
        create_grp($env, $db);
        break;
    case 'priv':
        noprivs($env, $db);
        break;
    case 'init':
        rebuild($env, $db);
        break;
    case 'dcat':
        debug_cats($env, $db);
        break;
    case 'dgrp':
        debug_grps($env, $db);
        break;
    case 'dhst':
        debug_hsts($env, $db);
        break;
    case 'dexp':
        debug_exps($env, $db);
        break;
    case 'ddgp':
        debug_del_group($env, $db);
        break;
    case 'invl':
        invalid($env, $db);
        break;
    default:
        display_cats($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
