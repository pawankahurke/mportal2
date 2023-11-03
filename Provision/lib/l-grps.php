<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Jun-04   EWB     Created.
16-Jun-04   EWB     cat_options, grp_options
17-Jun-04   EWB     Made styles into library.
22-Jun-04   EWB     constStyleSearch
 8-Jul-04   EWB     dirty flag moved to l-gdrt.php
 9-Jul-04   EWB     many constants moved to l-rlib.php
17-Nov-04   EWB     mgrp_options ignores foreign groups.
12-Sep-05   BTE     Added checksum invalidation code.
23-Sep-05   BJS     Added cancel_link(), customURL(), done_link().
26-Sep-05   BJS     Removed done_link().
13-Oct-05   BJS     Added constPageEntryNotifications. Return user to
                    current notification when finished.
14-Oct-05   BJS     Added all_machine_group_SQL(), single_machine_groups().
20-Oct-05   BJS     Added build_machine_group_content/list(),
                    build_group_category_content/list(), build_group_category_list(),
                    prep_for_multiple_select().
21-Oct-05   BJS     Added prep_for_SQL_IN(). Modified find_mgrp_gid().
24-Oct-05   BJS     Added build_group_list(). Added find_mgrp_gid() 3rd arg.
                    Pass in the notification id and notification action from
                    /event/notify.php so we can return the user to it when they
                    are done configuring groups.
26-Oct-05   BJS     Added find_mgrp_list().
27-Oct-05   BJS     Added add_parents, remove_trailing_comma,
                    insert_valid_entries & insert_into_group_temp.
28-Oct-05   BJS     is_mgroupid_in_user().
31-Oct-05   BJS     Added notification count to remove_mgroupid_from_notifications.
01-Nov-05   BJS     Appened GRPS_ to all new l-grps procs.
02-Nov-05   BJS     Added GRPS_get_multiselect_values().
03-Nov-05   BJS     Added GRPS_build_inuse_list(), added $type argument to
                    procs to handle both Notifications & Reports.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation code.
07-Nov-05   BJS     Suspend removed from event.Reports.
08-Nov-05   BJS     Added conditionals to SQL queries; reports do not have the
                    group_suspend field.
10-Nov-05   BTE     Some delete operations should not be permanent.
28-Nov-05   BJS     Added many group related procs to handle scrip configuration
                    for groups (config/scrpconf.php).
29-Nov-05   BJS     Added GRPS_include/exclude_instructions().
30-Nov-05   BJS     Search option include/exclude/suspend handles
                    event/asset/notifications. Added GRPS_find_machineid_from_mgroupid().
02-Dec-05   BJS     Added support for Asset Report groups.
05-Dec-05   BJS     Added GRPS_update_AssetReports_group_include().
08-Dec-05   BJS     Fixed a bug in GRPS_build_version_dependent_scrip_list()
                    not selecting for EVERY client version, only the first.
14-Dec-05   BJS     Added back button support to scrip config wizard.
15-Dec-05   BJS     Added GRPS_return_group_from_mgroupid()
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
26-Jan-06   BTE     Fixed a syntax error in GRPS_find_machineid_from_mgroupid.
30-Jan-06   BTE     Fixed a few broken queries.
30-Jan-06   BJS     Expanded existing procs to support entering wizard from the
                    dashboard.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.
13-Apr-06   BTE     Fixed several state machine errors and a SQL statement.
14-Apr-06   BTE     Added wizard "level" tracking to some buttons.
17-Apr-06   BTE     Bug 3202: Group management server issues.
19-Apr-06   BJS     Bug 3287: SQL error in GRPS_return_group_from_mgroupid( ).
06-May-06   BTE     Minor change to work with publish.php.
23-May-06   BTE     Bug 3360: Cannot remove a machine from an user-defined
                    group.
03-Jul-06   BTE     Bug 3514: Problems with Notifications.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
07-Aug-06   BTE     Bug 3251: Asset and Event Report Group Include broken.
20-Sep-06   BTE     Handle built-in type groups.
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.

*/


/* constant definitions
     |
     | These are used to control the wizards behavior.
     |
     | 1. When entering the wizard from sites:wizard we
     |    set $custom to constPageEntrySites.
     |    We can edit, remove and add new groups.
     |    When finished, we return to sites:wizard page.
     |
     | 2. When entering the wizard from tools:groups we
     |    set $custom to constPageEntryTools.
     |    We can edit, remove and add new groups.
     |    We may also click the 'advanced' link to present
     |    the (acct/groups.php) wizard. Clicking on the
     |    'wizard' link will return us to (config/groups.php)
     |    the tools:groups wizard. When finished we return to
     |    sites:wizard page.
     |
     | 3. When entering the wizard from microsoft updates: wizard
     |    and clicking 'Select update method' we set $custom to
     |    constPageEntryWUConfg. We can use, edit & use and add new
     |    groups. Once a groups has been chosen, we can select an
     |    update method for MS updates. This is a step #1 and #2
     |    cannot reach. When finished we return to the microsoft
     |    updates:wizard.
     |
     | 4. Notifications now have an option to configure groups
     |    when editing, creating or copying a report. We enter
     |    the wizard as custom=5 (constPageEntryNotfy). The user
     |    can remove/edit/add groups. When finished we return to
     |    the event:notifications page.
     |
     | 5. The same (#4) is true for Event (custom=6) and
     |    Asset (custom=7) reports.
    */


/*
    |  l-head.php currently relies on constPageEntryTools
    |  to be equal to 3. If you change the value here,
    |  you must change the hardcoded value in l-head.php
    |  'custom=3' as well.
    */
define('constPageEntrySites',   2);
define('constPageEntryTools',   3);
define('constPageEntryWUconfg', 4);
define('constPageEntryNotfy',   5);
define('constPageEntryReports', 6);
define('constPageEntryAsset',   7);
define('constPageEntryScrpConf', 8);

/*
    | These are passed into find_mgrp_gid()
    | to control if we call find_many()
    | or find_one(). The array returned
    | will be indexed differently based
    | on the proc called.
   */
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

/* if we see a dangerous variable */
define('constConfirm', 'ConfirmText');

define('constButtonExec',     'Execute');
define('constPassValue',      'n0n$3n$3');
define('constTypeSemaphore',  'semaphore');
define('constQueryRestrict',  1);
define('constQueryNoRestrict', 0);
define('constQueryIncludeMgroupid', 1);
define('constQueryExcludeMgroupid', 0);


/*
     |  $custom = 2|constPageEntrySites
     |            3|constPageEntryTools
     |            5|constPageEntryNotfy
     |            6|constPageEntryReports
     |            8|constPageEntryScrpConf
     |          101|constDashStatus_SelectMachineGroup
     |
     |   This function will present the user with a cancel
     |   button. When clicked, it will open a new page within
     |   it _self.
    */
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

/* functions the same as cancel_link, but uses an 'ok' button */
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


/*
    | $custom = page where we came from
    | $env = global array
    |
    | We can reach the scrip list and scrip config
    | from any of the 4 choices when we enter wizard.
    | We want to return to user to the previous page
    | and must pass the correct variables.
   */
function back_href($custom, $env)
{
    //print_r($env);

    $scp = $env['scop'];
    $cid = $env['cid'];
    $act = $env['act'];

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
            /* return to the select method */
        case 'csit':
            $act = 'wapp';
            $scp = 0;
            $cid = 0;
            $hid = 0;
            break;

            /* return to the select site */
        case 'chst':
            $act = 'wapp';
            break;

            /* return to select machine */
        case 'enab':
            if ($scp == constScopAll) {
                $act = 'wapp';
                $scp = 0;
                $cid = 0;
                $hid = 0;
            } else if ($scp == constScopHost) {
                $act      = 'chst';
                $mgroupid = $env['sgrp']['mgroupid'];
                $mcatid   = $env['sgrp']['mcatid'];
                $site     = $env['site'];
                $hid      = 0;
            } else if ($scp == constScopUser) {
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
                $act = 'wapp';
                $scp = 0;
                $cid = 0;
                $hid = 0;
            }
            break;

            /* return to select a scrip from configure */
        case 'scrp':
            $act        = 'selm';
            $group_name = $env['group_name'];
            $mgroupid   = $env['mgroupid'];
            $mcatid     = $env['mcatid'];
            $hid        = $env['hid'];
            $phid       = $env['prev_hid'];
            $pcid       = $env['prev_cid'];
            $pscop      = $env['prev_scop'];
            $scp = $pscop;
            if ($scp == constScopSite) {
                $act = 'scop';
            }
            if ($scp == constScopUser) {
                $act = 'wapp';
                $addact = '&act=machine_selected';
            }
            break;

            /* a single site: scrip list */
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
            }
            break;

            /* a single site, dangerours config */
        case 'prmt':
            $act        = 'selm';
            $censusid   = $env['censusid'];
            $mgroupid   = $env['mgroupid'];
            $mcatid     = $env['mcatid'];
            $snum       = $env['snum'];
            $group_name = $env['group_name'];
            $pcid       = $env['prev_cid'];
            $phid       = $env['prev_hid'];
            $pscop      = $env['prev_scop'];

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
                . "&snum=$snum" . $addact;
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
    /*
         | Where ever we entered the wizard from is
         | where we want to return the user to.
        */
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

/* $const = any const value used to set custom
    |  returns a string that can be appended to the
    |  url.
    */
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


/*
    |  $gid  = group id
    |  $type = call find_many() or find_one().
    |
    |  constMachineGroupReturnTypeOne
    |   will return: array ([mgroupid] => 67, ...)
    |
    |  constMachineGroupReturnTypeMany
    |   will return: array ([0] => array ([mgroupid] => 67, ...)
    |
    |  $db   = database handle
   */
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


/*
    |  Returns a set of machine groups.
    |
    |  Each of these should contain at least one machine
    |  controlled by the current user, or it should be
    |  owned by the current user.
    */

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


/*
    |  Check for aliens ... these are machines which
    |  belong to this machine group, but we have no
    |  acess to and should not be allowed to modify.
    */

function mgrp_alien($gid, $auth, $db)
{
    $qu  = safe_addslashes($auth);
    $sql = "select X.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
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
                $err = PHP_VARS_HandleDeletedGroup(
                    CUR,
                    $row['censusuniq'],
                    $row['mgroupuniq']
                );
                if ($err != constAppNoErr) {
                    logs::log(
                        __FILE__,
                        __LINE__,
                        "delete_host_gid: "
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


/*
    |  Returns all single machine groups from the
    |  category 'Machine' in the MachineCategoies
    |  table joined on the MachineGroups table
    |  by mcatid.
    |
   */
function single_machine_groups($db)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.MachineCategories as M\n"
        . " join " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " where M.category = 'Machine'\n"
        . " and M.mcatuniq = G.mcatuniq";
    return find_many($sql, $db);
}


/*
    | $db = database handle
    | Returns the mgroupid for the 'All' group.
   */
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


/*
    | $mgroupid = machine group id
    | $db       = database handle
    | Returns the machine category id for the given
    | machine group id
   */
function return_mcatid($mgroupid, $db)
{
    $sql = "select mcatid from " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineCategories on (\n"
        . "  " . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq)"
        . " where mgroupid = $mgroupid";
    $res = find_one($sql, $db);
    return $res['mcatid'];
}


/*
    | $mgroupid = machine group id
    | $db       = database handle
    | Returns the group name for the given mgroupid.
   */
function return_mgroup_name($mgroupid, $db)
{
    $sql = "select name from " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " where mgroupid = $mgroupid";
    $set = find_one($sql, $db);
    return $set['name'];
}

/*
    | $mgroupid = machine group id
    | $user     = current user
    | $include  = include or exclude mgroupid
    | $db       = database handle
    | Returns an array of all the machines and site in the
    | mgroupid or false on error.
    |
    | Array
    | (
    | [0] => Array
    |  (
    |   [site] => HFN BJ Test
    |   [host] => brawny
    |   [mgroupid] => 3
    |  )
   */
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


/*
    | $mgroupid = machine groupid
    | $mcatid   = machine category id
    | $db       = database handle
    | Returns all the censusid for the given mcatid and mgroupid
   */
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


/*
    | $field = the database field we want to save
    | $quote = true we quote, false we don't.
    | $set   = the array of results from the query
    |
    | This proc gets called to return the values
    | from a single field for a SQL result, comma
    | seperated.
   */
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


/*
    | $censusid = census id
    | $db       = database handle
    | Returns the distinct client versions for the given
    | list of census ids.
   */
function GRPS_return_client_versions($censusid, $db)
{
    $sql   = "select distinct vers from " . $GLOBALS['PREFIX'] . "core.Revisions\n"
        . " where censusid in ($censusid)\n"
        . " order by vers desc";
    return find_many($sql, $db);
}


/*
    | $client_v = array of client versions (string)
    | $db       = database handle
    | Returns 1 on success, false on error.
    |
    | Builds a temp table, temp_scrip_list, that will be used
    | to hold the unique scrip names and numbers for all the
    | applicable client versions in the user selected group.
   */
function GRPS_build_version_dependent_scrip_list($client_v, $db)
{
    $sql = "drop table if exists temp_scrip_list";
    redcommand($sql, $db);

    $sql = "create temporary table temp_scrip_list(\n"
        . " scrip_num  int(11) not null primary key,\n"
        . " scrip_name text    not null default '',\n"
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


/*
    | $tbl = table name
    | $db  = database handle
    | Builds the temp_vars tables.
   */
function GRPS_build_TempVars($tbl, $db)
{
    $sql = "create temporary table $tbl(\n"
        . " varid  int(11)    not null primary key,\n" // Variables.varid
        . " name   text       not null default '',\n" // Variables.name
        . " itype  int(11)    not null default  0,\n" // Variables.itype
        . " pwsc   int(11)    not null default  0,\n" // VarVersions.pwsc
        . " dngr   tinyint(1) not null default  0,\n" // VarVersions.dngr
        . " defval text       not null default '',\n" // VarVersions.defval
        . " config tinyint(1) not null default  0,\n" // VarVersions.config
        . " cfgord int(11)    not null default  0,\n" // VarVersions.configorder
        . " valu   text       not null default '',\n" // VarValues.valu
        . " def    tinyint(1) not null default  0,\n" // VarValues.def
        . " dval   text       not null default  ''\n" // Descriptions.valu
        . ")";
    $res = redcommand($sql, $db);
    if (!$res) {
        logs::log(__FILE__, __LINE__, "'l-grps.php: build_scrip_descriptions failed building $tbl", 0);
        $sql = "drop table if exists $tbl";
        redcommand($sql, $db);
    }
}


/*
    | $scop     = the variable's scope
    | $censusid = list of censusids
    | $mroupid  = machine group id
    | $db       = database handle
    |
    | Creates two temporary tables, temp_vars_1 & 2 so we can join them
    | with eachother, instead of having a static table and risk it not
    | being dropped.
    |
    | We select into temp_vars_1 all the data we need except the
    | actual value of the variable (valu) and if we should use the
    | default value (def), by client version and variable scope.
    |
    | Next copy all the data in table 1 into table 2.
    |
    | Next insert into table 1 the VarValues def and valu, joined on
    | table 2 selecting by varid and mgroupid.
    |
    | We now have a populated temp_vars table.
   */
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

    /* Copy temp_vars_1 into temp_vars_2 */
    $sql = "insert into $temp_vars_2\n"
        . " select * from $temp_vars_1";
    $res = redcommand($sql, $db);
    if (!$res) {
        logs::log(__FILE__, __LINE__, "l-grps.php: insert into $temp_vars_2 from $temp_vars_1 failed", 0);
        GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db);
    }

    /*
        $sql = "select * from temp_vars";
        $set = find_many($sql, $db);
        print_r($set);
        */

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
    /*
        $sql = "select * from temp_vars";
        $set = find_many($sql, $db);
        print_r($set);
        */
}


/*
    | $temp_vars_1 = first temp table
    | $temp_vars_2 = second temp table
    | $db          = database handle
    | When any sql query fails in GRPS_build_scrip_descriptions
    | we call this to remove the temp tables.
   */
function GRPS_drop_temp_vars($temp_vars_1, $temp_vars_2, $db)
{
    $sql = "drop table if exists $temp_vars_1";
    redcommand($sql, $db);

    $sql = "drop table if exists $temp_vars_2";
    redcommand($sql, $db);
}


/*
    | $db_entry = single db row for a variable
    | Call a GRPS_xxxxx function for the appropriate datatype.
   */
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


/*
    | $db_entry = single db row for a variable
    | Create a textbox with the current variable value.
   */
function GRPS_text(&$db_entry)
{
    $text = $db_entry['dval'];
    $name = $db_entry['name'];
    $valu = $db_entry['valu'];

    $nput = input_text($name, $valu);
    return GRPS_scrip_row($text, $nput);
}


/*
    | $text  = descriptive text
    | $value = variable value
   */
function GRPS_scrip_row($text, $valu)
{
    return "<tr><td>$text</td><td>$valu</td></tr>";
}


/*
    | $db_entry = single db row for a variable
    | Create a checkbox with the variable value.
   */
function GRPS_checkbox(&$db_entry)
{
    $valu = $db_entry['valu'];
    $name = $db_entry['name'];
    $dval = $db_entry['dval'];
    $valu = intval($valu);
    $chbox = checkbox($name, $valu);
    return GRPS_scrip_row($dval, $chbox);
}


/*
    | $db_entry = single db row for a variable
    | Create a button with the constButtonExec value
   */
function GRPS_execute(&$db_entry)
{
    $text  = $db_entry['valu'];
    $name  = $db_entry['name'];
    $dval  = $db_entry['dval'];
    $b_val = constButtonExec;
    $butn  = "<input type=\"submit\" name=\"$name\" value=\"$b_val\">";
    return GRPS_scrip_row($dval, $butn);
}


/*
    | $db_entry = single db row for a variable
    | Return a text area box with the variable value
   */
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
    //$class = ($y < 80)? 'footnote' : '';
    $class = '';
    $msg   = ($class) ? "class=\"$class\" " : '';
    $ta    =  "<textarea $msg"
        . "rows=\"$r\" style=\"width:800px;\" name=\"$name\">"
        . $valu
        . '</textarea>';
    return GRPS_scrip_row($dval, $ta);
}


/*
    | $db_entry = single db row of a variable
    | Return two password boxes, both with *'s
    | for the value of constPassValue. The second
    | box for confirmation.
   */
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


/*
    | $name = name of the password box
    | $text = text for the password box
    |
    | Since we store md5 hashes, the passwords
    | are not decoded and displayed, we just
    | display constPassValue encoded w/* instead.
   */
function GRPS_create_password($name, $text)
{
    $pass = constPassValue;
    $valu = ($text) ? " value=\"$pass\"" : '';
    $type = 'type="password"';
    return "<input $type name=\"$name\"$valu>";
}


/*
    | $db_entry = single db row for a variable
    |
    | GRPS_proc_map returns the data type of the variable
    | Call the appropriate GRPS_get_xxxx_xxxx() to fetch
    | the changed value, if any.
   */
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


/*
    | $db_entry = single db row for a variable
    | If the user clicked a button, it will exist,
    | otherwise it wont.
   */
function GRPS_get_execute_valu($db_entry)
{
    $scrp_name = $db_entry['name'];
    $post_valu = get_string($scrp_name, 0);
    if ($post_valu) {
        return $post_valu;
    }
    return -1;
}


/*
     | $db_entry = single db row for a variable
     | If the passwords dont match, or they have
     | not changed we do nothing. Otherwise
     | they both must change and be equal.
    */
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
        /* password changed and both new ones match */
        return $p1_valu;
    }
    return -1;
}


/*
    | $db_entry = a single db row for a variable
    |
    | Checkboxes only exists if they are clicked.
    | However, it may be clicked by default, so for
    | every checkbox we compare its current state
    | to the default. If they are different, its changed.
   */
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


/*
    | $db_entry = a single db row for a variable
    |
    | Because we are comparing textarea boxes
    | we must first call normalize on the values,
    | and then compare with strcmp(). The value returned
    | will be zero if nothing changed.
   */
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
        /* something changed */
        return $post_valu;
    }
    /* nothing changed */
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


/*
    | $row = current row of the database from temp_vars
    | Return the type of variable we are working with
    | in order to correctly build a text box, radio
    | button, textarea of password boxes.
   */
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


/*
    | $user     = current user
    | $restrict = if we want to restrict the query to mgroupids
    |  that have a VarValues.valu set.
    | $db       = database handle.
   */
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


/*
    | $auth     = current user
    | $restrict = to restrict the query or not
    | $db       = db handle
    |
    | Returns a complete array of all the groups you have
    | access to. If restricted we limit the result to only
    | mgroupids that have VarValues.valu set.
   */
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


/*
    |  $grps is the array of all machine groups, indexed by precedence.
    |  As the name implies, we are building an array to feed to
    |  select_multiple().
    |
    |  Return an array in the form:
    |   Array ( [1] => All
    |           [8] => User:Jack
    |           [78]=> User:New
    |   Indexed by mgroupid in order of precedence.
   */
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


/*
    |  $auth = current user
    |  $db   = database handle
    |  $type = constEventNofitications or constEventReports
    |
    |  Returns a list of all the mgroupids
    |  used in all notifications that the
    |  user has access too.
   */
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


/*
    |  $list        = array of machine group ids & names
    |  $def_option  = (not displayed) pulldown option
    |  $pos         = index value of option
    |  $all_option  = (all) pulldown option
    |  $all_pos     = index value of option
    |  $def_message = we use this msg if the list is empty
    |
    |  This is used to create the single select
    |  drop-down options for the machine groups
    |  on the search page.
   */
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


/*
    |  $list = array with as many indexes as there
    |  are notifications. Each index contains 3
    |  fields, group_include, group_exclude &
    |  group_suspend. The value will be the current
    |  list of mgroupids for each notification, or
    |  blank if none are set.
    |  $db = database handle
    |
    |  Take all similiar fields and concat into a string.
    |  Do not take values that are blank or zero.
    |  Insert each string (include, exclude, suspend)
    |  into a temporary table.
    |
    |  We can't supress a mysql error if a temp table
    |  is missing. Since this code will get executed
    |  everytime someone browses the notifications page
    |  we are inserting a '' into any temp table that
    |  will otherwise be noexistent.
   */
function GRPS_insert_valid_entries($list, $db)
{
    $g_inc_str = '';
    $g_exc_str = '';
    $g_sus_str = '';

    reset($list);
    foreach ($list as $index => $db_entry) {
        /* concat the mgroupids together for each group */
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

    /*
        | take all the set strings and insert them into the db.
        | if the string is blank, insert a single zero entry.
       */
    GRPS_populate_group_temp($g_inc_str, constGroupIncludeTempTable, $db);
    GRPS_populate_group_temp($g_exc_str, constGroupExcludeTempTable, $db);
    GRPS_populate_group_temp($g_sus_str, constGroupSuspendTempTable, $db);
}


/*
    | $str = string of mgroupids
    | $tbl = temp table
    | $db  = database handle
   */
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


/*
    |  $list     = list of mgroupids in the form;
    |    1),(2),(3
    |  $tbl_name = temporary table name
    |  $db       = database handle
    |
    |  Drop the table if it already exists.
    |  Create the tempoary table.
    |  Insert our mgroupids into the table.
    |  Log any create or insert error.
   */
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
        /*
             $sql = "select * from $tbl_name";
             $set = find_many($sql, $db);
             print_r($set);
             */
    }
    if (!$res) {
        logs::log(__FILE__, __LINE__, "l-grps: table $tbl_name create error");
    }
}


/*
    |  $str = string of mgroupids in the form
    |    1,2,3
    |  Return a string in the form 1),(2),(3
   */
function add_parens($str)
{
    return str_replace(",", "),(", $str);
}


/*
    |  $str = string of mgroupids in the
    |    form 1,2,3,
    |  PHP function rtrim removes the trailling
    |  character, ','.
   */
function remove_trailing_comma($str)
{
    return rtrim($str, ',');
}


/*
    |  $tbl = table name
    |  $db  = database handle
    |
    |  Join core.MacineGroups to $tbl
    |  and select mgroupid and name
   */
function GRPS_find_mgrp_name_pairs($tbl, $db)
{
    $sql = "select mgroupid, name from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as MG\n"
        . " left join $tbl as T\n"
        . " on T.m_id = MG.mgroupid\n"
        . " where T.m_id = MG.mgroupid";
    return find_many($sql, $db);
}


/*
    |  $mgroupid = group id
    |  $db       = database handle
    |  Returns all distinct report names and ids
    |  that contain this group in include/exclude
    |  or suspend.
    |
    |  10/03/05; This proc was updated to handle both notifications and
    |  reports by passing in the arg $type, which can be either
    |  constReportNotifications or constReportEvents.
   */
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


/*
    | $type = notification, report or asset report
    | query uses the table specified by $type.
    | Returns SQL or an empty string.
   */
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


/*
    | $type = notification or report
    | Asset & Event Reports don't have
    | a group_suspend field.
   */
function GRPS_create_select($type)
{
    return ($type == constEventNotifications) ? 'N.group_include, N.group_exclude, N.group_suspend' :
        'N.group_include, N.group_exclude';
}


/*
    | $type     = notification or report
    | $mgroupid = the machine group id
    | Reports don't have a group_suspend field.
   */
function GRPS_create_regexp($type, $mgroupid)
{
    $str = GRPS_create_regexp_groups($mgroupid);
    return ($type == constEventNotifications) ? $str . " or N.group_suspend regexp '(^|,)$mgroupid(,|$)')\n"
        : $str . ")\n";
}


/*
    | $mgroupid = machine group id
    | Both reports and notifications have the group_include & group_exclude fields.
   */
function GRPS_create_regexp_groups($mgroupid)
{
    $tmp = " and (N.group_include regexp '(^|,)$mgroupid(,|$)'\n"
        . "  or  N.group_exclude regexp '(^|,)$mgroupid(,|$)'\n";
    return $tmp;
}


/*
    |  $gid  = mgroupid we wish to remove
    |  $rnid = comma seperated string of notification/report/asset ids
    |  $db   = database handle
    |
    |  Select the id & 3 group values from the list of
    |  notifications/reports that contain the mgroupid.
    |
    |  Loop on each notification/report/asset, retrieve the id & group values.
    |  Place each value into an array.
    |
    |  If the mgroupid does not exist in the array, we save that
    |  value. If it does exists, we don't save that value. What
    |  we are doing is creating a new comma seperated string
    |  that we will insert it to the database. This string will
    |  contain only the mgroupid's we want to insert.
    |
    |  If we are removing groupid 7 from a single report:
    |  array before: (863 is the id)
    |  Array ([863] => Array
    |                 (  [group_suspend] => 7,23
    |                    [group_exclude] => 0
    |                    [group_include] => 7
    |                  )
    |  After:
    |  Array ([863] => Array
    |                 (  [group_suspend] => 23
    |                    [group_exclude] => 0
    |                    [group_include] => 0
    |                  )
    |
    |  Then we loop through the array and update the 3 group fields
    |  for each notification id.
    |
    |  12/02/05; argument $type can be constEventReports, constAssetReports
    |  or constEventNotifications.
   */
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

        /* if group value is zero, then we don't have to check it.
             |
             | otherwise we traverse the array and remove any entry
             | that matches our mgroupid.
            */
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


/*
    | $gid  = the mgroupid we want to remove
    | $list = list of all mgroupids in the given
    |  report or notification.
    |
    | Returns an array containing the mgroupids
    | we want to keep.
   */
function GRPS_remove_mgroupid($gid, $list)
{
    $out = array();
    reset($list);
    foreach ($list as $index => $mgroupid) {
        if ($mgroupid != $gid) {
            $out[] = $mgroupid;
        }
    }
    /*
         | if all the values where removed from a specific
         | group, we insert a '' to keep things consistent.
        */
    if (!$out) {
        $out[] = '';
    }
    return $out;
}


/*
    |  $machines_list = array of machine names.
    |  Array ( [0] => joem01 [1] => motorola [2])
    |  $db            = database handle.
    |
    |  Returns an a comma seperated string of mgroupids
    |  corresponding to the supplied machine name.
   */
function GRPS_translate_groupname_to_mgroupid($machines_list, $db)
{
    $machines_mgroupid = '';
    reset($machines_list);
    foreach ($machines_list as $m_key => $m_val) {
        $tmp_val = GRPS_find_machine_mgroupid($m_val, $db);
        /*
             | $tmp_val will be an empty array if the machine group does not
             | exist. It may also contain more than one mgroupid, in the case
             | of a machine belonging to multiple sites.
            */
        if (isset($tmp_val[0])) {
            reset($tmp_val);
            foreach ($tmp_val as $k => $v) {
                /*
                    | If $machines_mgroupid is an emtpy string
                    | we don't append the comma because its the
                    | first entry. Otherwise $machines_mgroupid
                    | is already set, so we append the value and
                    | the comma.
                   */
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


/*
    |  $machine = machine name as string
    |  $db      = database handle
    |  Returns all mgroupid's associated with the
    |  given machine name for the local machine group.
    */
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


/*
    | field = name used in saved_search()
    | Returns a comma seperated list or an
    | empty string.
   */
function GRPS_get_multiselect_values($field)
{
    $set = get_argument($field, 0, array());
    $str = join(',', $set);
    return ($str) ? $str : '';
}



/*
    |  $igrp    = array of machines.
    |  $default = default message if machine array
    |  is empty.
    |
    |  Returns an link for each machine group in the array.
    |  The link contains the mgrouid id
   */
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


/*
    |  $set  = an array containing notifications or reports
    |  indexed by an integer value.
    |  $type = either constEventNotifications or constEventReports.
    |  $group_name = The name of the group we are removing.
    |
    |  Returns 3 values;
    |  $str  = A string of all the effected reports/notifications.
    |   Each report name will be a link to edit the report.
    |  $rref = A link that contains the report/notification id of
    |  the reports that will have a particular mgroupid removed from.
    |  $msg_rem = A generic alert message for reports/notifications.
    |
    |  This proc gets called once for Notifications and once for Reports.
   */
function GRPS_build_inuse_list($set, $type, $group_name)
{
    $out        = array();
    $report_ids = array();
    $report_str = '<table>';
    $rref       = '';
    $msg_rem    = '';

    if ($set) {
        /* Create a link to each report */
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
            /*
                 | $report     = the contents of a single notification
                 | $report_str = list of report names displayed to user
                 | $report_ids = array of report ids passed on the url
                */
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


/*
    | $auth = name of current user
    | $table_type = name of the temp table
    | $s_g    = name that will be posted containg the selected group
    | $env_g  = default group values.
    | $r_type = type of report, Notification, Event or Asset.
    | $db     = database handle
    |
    | This proc is responsible for creating the drop down box
    | populated with only mgroupid/names that are contained in
    | a report. It gets all the mgroupid for each field
    | (group_suspend/include/exclude) from all Reports and
    | saves EACH one to a seperate temp table. Then it the temp
    | table is joined on core.MachineGroups to get the name
    | of each group a particular mgroupid relates to.
    | The select list is then built once for each field
    | (group_suspend/include/exclude) and contains only relevant
    | groups.
    |
    | Default values for the select box are (all) and (not displayed).
   */
function GRPS_create_select_box(
    $auth,
    $table_type,
    $s_g,
    $env_g,
    $r_type,
    $db
) {
    /* list of all machine group ids in the Reports */
    $group_sel_list = GRPS_find_mgrp_list($auth, $r_type, $db);

    /* save include, exclude, suspend values to a temp table */
    GRPS_insert_valid_entries($group_sel_list, $db);

    /*
        | Retrieve the name/mgroupid pair from core.MachineGroups that
        | matches each mgroupid entry in the temp table.
       */
    $list = GRPS_find_mgrp_name_pairs($table_type, $db);

    /* Prep the mgroupid/name array to be passed to tiny_select */
    $list = GRPS_mgrp_arrange_list(
        $list,
        '(not displayed)',
        -1,
        '(all)',
        0,
        constMachineGroupMessage
    );
    /*
        | return a complete select box populated with only mgroupid/names
        | that are contained in a report. It is not limited to reports
        | currently on the screen, but includes all reports in the db.s
       */
    return tiny_select($s_g, $list, $env_g, 1, 128);
}


/*
    | $mgroupid = a group id
    | $auth     = current user
    | $db       = database handle
   */
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


/*
    |  $type  = include or exclude
    |  $group = mgroupid
    |  $auth  = current user, already addslashed
    |  $db    = database handle
    |  Returns a list of included or excluded census ids
    |  retrieved by mgroupid.
   */
function GRPS_create_list($type, $group, $auth, $db)
{
    $list = array();
    $set  = GRPS_find_machines_from_mgroupid($group, $auth, $db);

    reset($set);
    foreach ($set as $key => $db_entry) {
        $list[] = "'" . $db_entry['id'] . "'";
    }

    $str = join(",", $list);

    /* The row name "temp_id" is from l-core.php's TempCensus_sql. */
    return ($type == constGroupInclude) ? " and temp_id in ($str)\n"    :
        " and temp_id not in ($str)\n";
}


/*
    | $group_include = cs list of mgroupids
    | $group_exclude = cs list of mgroupids
    | $auth          = current user
    | $db            = database handle
    |
    | For include or exclude, translates each mgroupid into the corresponding
    | host names. Builds a string w/each host name single quoted and comma
    | seperated to is included in the sql query.
   */
function GRPS_build_host_list($group_include, $group_exclude, $auth, $db)
{
    debug_note("l-grps: group_include:($group_include) group_exclude:($group_exclude)");

    $exception = '';

    /* First exception: limit rows to those on the include list */
    if (($group_include) && ($group_include != constMachineGroupDefaultALL)) {
        /*
            | If the user selected the all field, we don't behave any differently.
            | However, if the user specified a group, we must find out what
            | machines are part of that group.
           */
        $exception  = GRPS_create_list(
            constGroupInclude,
            $group_include,
            $auth,
            $db
        );
    }
    if ($group_exclude) {
        /*
             | if the user excludes the 'all' group, or a group that contains
             | all the machines they have access to, they will create a zero
             | event report.
            */
        $exception .= GRPS_create_list(
            constGroupExclude,
            $group_exclude,
            $auth,
            $db
        );
    }
    return $exception;
}


/*
    | Returns the fine print warning
   */
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


/*
    | Returns the group include instructions.
   */
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


/*
    | Returns the group exclude instructions.
   */
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


/*
    | $type = type of report
    | When creating the instruction text for including
    | and excluding groups, we have to taylor the message
    | for whatever report type we are working with.
   */
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


/*
    | $act = report action
    | Appends the string to the url to keep track of
    | what report the user was configuring when they
    | clicked 'configure groups'.
   */
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


/*
    | $mgroupid = comma seperated string of mgroupids
    |             include or exclude
    | $db       = database handle
    |
    | Build a temp table to store all the mgroupid entries.
    | Join the core.MachineGroupMap table against the temp table
    |  on mgroupids,
    | join the core.Census table on the MachineGroupMap table by
    |  censusid,
    | join the asset.Machine table to the core.Census table by
    |  both cust and host. We will return an array of machineids
    | corresponding to the $mgroupid we are passed.
   */
function GRPS_find_machineid_from_mgroupid($mgroupid, $db)
{
    $out = array();

    $sql = "drop table if exists temp_mgroupid";
    redcommand($sql, $db);

    $sql = "create temporary table temp_mgroupid(\n"
        . " mgid int(11) not null default 0)";
    redcommand($sql, $db);

    /* its currently a string */
    $mgroupid = explode(",", $mgroupid);

    reset($mgroupid);
    foreach ($mgroupid as $key => $mgid) {
        /* populate the temp_mgroupid table */
        $sql = "insert into temp_mgroupid\n"
            . " set mgid = $mgid";
        redcommand($sql, $db);
    }

    $sql = "select distinct machineid from        \n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as MGM,         \n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G              \n"
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


/*
    | $g_include = array of machineid to include
    | $g_exclude = array of machineid to exclude
    | Returns the machines to cover in the report.
   */
function GRPS_find_common_from_machineid(
    $g_include,
    $g_exclude
) {
    /* include set, no excludes, return includes */
    if (($g_include) && (!$g_exclude)) {
        return $g_include;
    }

    /* includes & excludes set, return includes minus the excludes */
    if (($g_include) && ($g_exclude)) {
        return array_diff($g_include, $g_exclude);
    }

    /* g_include is empty - return empty array */
    return array();
}


/*
    | You cannot set a text field when creating it. So we call this
    | to set the group_include field to the mgroupid of the all group.
   */
function GRPS_update_AssetReports_group_include($db)
{
    $all_mgroupid = GRPS_ReturnAllMgroupid($db);
    $sql = "update " . $GLOBALS['PREFIX'] . "asset.AssetReports\n"
        . " set group_include = '$all_mgroupid'";
    redcommand($sql, $db);
}


/*
    | $type = refers to constPageEntry______
    | Creates the title of the wizard based
    | on where you started.
   */
function GRPS_create_wizard_title($type)
{
    switch ($type) {
        case constPageEntryScrpConf:
            return "Where would you like to configure scrips?";

        default:
            return "Where would you like to perform this action?";
    }
}


/*
    | $type = refers to constPageEntry______
    | Some wizard pages will display their own version
    | of again with custom links, this will display
    | l-grpw.php (config/wu-congf.php)'s again()
    | by default.
   */
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
