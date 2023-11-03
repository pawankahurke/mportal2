<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 9-Jul-04   EWB     Created.
12-Jul-04   EWB     Added default_wcfg.
14-Jul-04   EWB     removed: softinst.WUConfig.installdelay
14-Jul-04   EWB     added:   softinst.PatchConfig.installdelay
16-Jul-04   EWB     constants for softinst.Patches.canuninstall
19-Jul-04   EWB     removed: softinst.PatchConfig.installdelay
27-Jul-04   EWB     constants for softinst.WUConfig.propagate
 5-Aug-04   EWB     site proxy machine category
 5-Aug-04   EWB     fixed a typo in find_pcfg_pid
10-Aug-04   EWB     remap "any" flag value.
16-Aug-04   EWB     find default, global pconfig
17-Aug-04   EWB     removed the site proxies category.
25-Aug-04   EWB     precedence goes by priority, age, name.
 2-Sep-04   EWB     WUConfig.updatecache, cacheseconds.
13-Sep-04   EWB     removed PatchConfig.schedfail.
24-Sep-04   EWB     config searches return name of associated pgrp/mgrp
 3-Nov-04   EWB     fixed a bug in find_glbl_pcfg
10-Nov-04   EWB     PatchConfig.configtype.
29-Nov-04   EWB     constPatchStatusSuperceded
 3-Dec-04   EWB     constPatchStatusSuperseded
28-Dec-04   BTE     Added constants for restart and chain features.
29-Dec-04   BTE     Moved chain constants from PatchConfig to WUConfig.
18-Dec-04   EWB     New machine granted access to site pcfg.
 1-Apr-05   EWB     Shared Memory / Semaphore constants
16-Sep-05   BTE     Bug 2850: changed default repeat install cycle to a timeout
                    with three hours.
05-Oct-05   BTE     Bug 2850: changed default repeat install cycle to a timeout
                    with two hours.
14-Dec-05   AAM     Add constants for accessing shared memory as a single array.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
24-Feb-06   BTE     Removed census and groups dirty bits.
22-May-06   AAM     Added constShmDataBytes.
26-Aug-06   BTE     Bug 3601: Superseded status can be misleading in MUM.
20-Sep-06   BTE     Bug 2826: Make MUM approve/decline wizards a little easier
                    to use (not so large).
02-Oct-06   BTE     Added constPatchStatusDownloadFailed.
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.
26-Jan-07   BTE     Bug 4015: Add TX performance counters.
06-Mar-07   BTE     Bug 4042: MUM: change the default installation schedule to
                    next.

*/

    define('constCatAll',      'All');
    define('constCatUser',     'User');
    define('constCatSite',     'Site');
    define('constCatMachine',  'Machine');

    define('constStyleInvalid',  0);
    define('constStyleBuiltin',  1);
    define('constStyleManual',   2);
    define('constStyleEvent',    3);  // mgroup only
    define('constStyleAsset',    4);  // mgroup only
    define('constStyleExpr',     5);
    define('constStyleSearch',   6);  // pgroup only
    define('constStyleType',     7);  // pgroup only
    define('constStyleDirectSearch',    8); // pgroup only

    define('constDirtySet',  '1');
    define('constDirtyClr',  '0');

    define('constPatchDirty',  'patch_dirty');

   /*
    | Used for:
    |
    |   softinst.PatchStatus.status
    |   INST_SetStatus
    */

    define('constPatchStatusInvalid',                    0);
    define('constPatchStatusNotHandledOnServer',         1);
    define('constPatchStatusPendingImmediateInstall',    2);
    define('constPatchStatusPendingImmediateUninstall',  3);
    define('constPatchStatusPendingScheduledInstall',    4);
    define('constPatchStatusPendingScheduledUninstall',  5);
    define('constPatchStatusInstallDisabled',            6);
    define('constPatchStatusInstallFailed',              7);
    define('constPatchStatusInstalled',                  8);
    define('constPatchStatusUninstalled',                9);
    define('constPatchStatusDownloaded',                10);
    define('constPatchStatusDetected',                  11);
    define('constPatchStatusPendingDownload',           12);
    define('constPatchStatusPendingReboot',             13);
    define('constPatchStatusPotentialFailure',          14);
    define('constPatchStatusSuperseded',                15);
    define('constPatchStatusWaiting',                   16);
    define('constPatchStatusDownloadFailed',            17);

   /*
    | Used for:
    |
    |   softinst.WUConfig.management
    |   INST_GetWUConfig
    */

    define('constConfigManagementInvalid',        0);
    define('constConfigManagementDisabled',       1);
    define('constConfigManagementServer',         2);
    define('constConfigManagementUser',           3);
    define('constConfigManagementInstallControl', 4);
    define('constConfigManagementAutomatic',      5);

   /*
    | Used for:
    |
    |   softinst.WUConfig.updatecache
    |   INST_GetWUConfig
    */

    define('constConfigCacheInvalid',   0);
    define('constConfigCacheDisable',   1);
    define('constConfigCacheEnable',    2);

   /*
    | Used for:
    |
    |   softinst.WUConfig.installday
    |   INST_GetWUConfig
    */

    define('constConfigInstallDayEveryDay',     1);
    define('constConfigInstallDaySunday',       2);
    define('constConfigInstallDayMonday',       3);
    define('constConfigInstallDayTuesday',      4);
    define('constConfigInstallDayWednesday',    5);
    define('constConfigInstallDayThursday',     6);
    define('constConfigInstallDayFriday',       7);
    define('constConfigInstallDaySaturday',     8);

   /*
    | Used for:
    |
    |   softinst.WUConfig.newpatches
    |   INST_GetWUConfig
    */

    define('constConfigNewPatchesInvalid',      0);
    define('constConfigNewPatchesLastDefault',  1);
    define('constConfigNewPatchesWaitServer',   2);

   /*
    | Used for:
    |
    |   softinst.WUConfig.patchsource
    |   INST_GetWUConfig
    */

    define('constConfigPatchSourceInvalid',     0);
    define('constConfigPatchSourceWebSite',     1);
    define('constConfigPatchSourceSUSServer',   2);

   /*
    | Used for:
    |
    |   softinst.WUConfig.propagate
    |   INST_GetWUConfig
    */

    define('constConfigPropVendorOnly',   0);
    define('constConfigPropLocalOnly',    1);
    define('constConfigPropSearch',       2);

   /*
    | Used for:
    |
    |   softinst.PatchConfig.installation
    */

    define('constPatchInstallInvalid',  0);
    define('constPatchInstallNever',    1);
 // define('constPatchInstallLater',    2);
 // define('constPatchRemoveNow',       3);
    define('constPatchScheduleInstall', 4);
    define('constPatchScheduleRemove',  5);
 // define('constPatchDownloadOnly',    6);


   /*
    | Used for:
    |
    |   softinst.PatchConfig.schedtype
    |   softinst.PatchConfig.notifytype
    */

    define('constPatchTypeASAP', 1);
    define('constPatchTypeNext', 2);

   /*
    | Used for:
    |
    |   softinst.PatchConfig.configtype
    */

    define('constConfigTypeNormal',   0);
    define('constConfigTypeCritical', 1);
    define('constConfigTypeBeta',     2);

   /*
    | Used for:
    |
    |   softinst.Patches.canuninstall
    |   wu-patch.php
    */

    define('constPatchCanUnknown', 0);
    define('constPatchCanYes',     1);
    define('constPatchCanNo',      2);

   /*
    | Used for:
    |
    |   softinst.WUConfig.restart
    */

    define('constConfigRebootUnknown',  0);
    define('constConfigRebootDisable',  1);
    define('constConfigRebootAuto',     2);

   /*
    | Used for:
    |
    |   softinst.WUConfig.chain
    */

    define('constConfigChainUnknown',  0);
    define('constConfigChainTimeout',  1);
    define('constConfigChainInfinite', 2);
    define('constConfigChainDisabled', 3);

    /* Used for softinst.Patches.type */
    define('constPatchTypeAll',             -2);    /* display option only */
    define('constPatchTypeNotDisplayed',    -1);    /* display option only */
    define('constPatchTypeUndefined',       0);
    define('constPatchTypeUpdate',          1);
    define('constPatchTypeServicePack',     2);
    define('constPatchTypeRollup',          3);
    define('constPatchTypeSecurity',        4);
    define('constPatchTypeCritical',        5);
    /* There is no longer a mandatory patch type, however, this still exists
        to update older servers. */
    define('constPatchTypeMandatory',       6);

    define('constPatchTypeUndefinedStr',    'Undefined');
    define('constPatchTypeUpdateStr',       'Update');
    define('constPatchTypeServicePackStr',  'Service Pack');
    define('constPatchTypeRollupStr',       'Rollup');
    define('constPatchTypeSecurityStr',     'Security');
    define('constPatchTypeCriticalStr',     'Critical');
    /* Retired: define('constPatchTypeMandatoryStr',    'Mandatory'); */

    /* Used for: softinst.Patches.mandatory */
    define('constPatchMandatoryUnknown',    0);
    define('constPatchMandatoryNo',         1);
    define('constPatchMandatoryYes',        2);

    /* Friendly names for mandatory type groups */
    define('constPatchMandatoryUpdateStr', 'Mandatory Update');
    define('constPatchMandatoryServicePackStr', 'Mandatory Service Pack');
    define('constPatchMandatoryRollupStr', 'Mandatory Rollup');
    define('constPatchMandatorySecurityStr', 'Mandatory Security');
    define('constPatchMandatoryCriticalStr', 'Mandatory Critical');

   /*
    | Used for counting remote procedure calls
    |
    |    see rpc/server.php acct/perf.php
    */

    define('constShmKey',  2721);
    define('constSemKey',  2722);
    define('constShmSize', 8192);
    define('constShmEnab',  1);     // enable
    define('constShmData',  2);     // data
    define('constShmTime',  3);     // timestamp
    /* columns for constShmData variable */
    define('constShmDataStart',     0);
    define('constShmDataFinish',    1);
    define('constShmDataUserSec',   2);
    define('constShmDataUserUSec',  3);
    define('constShmDataSysSec',    4);
    define('constShmDataSysUSec',   5);
    define('constShmDataWallSec',   6);
    define('constShmDataWallUSec',  7);
    define('constShmDataBytes',     8);
    define('constShmDataUBytes',    9);

   /*
    |  Finds a wcfg record based upon the
    |  name of the associated machine group.
    */

    function find_wcfg_name($name,$db)
    {
        $row = array( );
        if ($name)
        {
            $qn  = safe_addslashes($name);
            $tag = constStyleBuiltin;
            $sql = "select W.* from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.WUConfig as W,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
                 . " where G.mgroupid = W.mgroupid\n"
                 . " and G.style = $tag\n"
                 . " and G.name = '$qn'";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  This should only be used in the unusual
    |  event that there is no default wcfg record yet.
    */

    function default_wcfg()
    {
        return array
        (
            'id'           => 0, // impossible
            'mgroupid'     => 0, // illegal
            'management'   => constConfigManagementServer,
            'installday'   => constConfigInstallDayEveryDay,
            'installhour'  => 3,
            'newpatches'   => constConfigNewPatchesLastDefault,
            'patchsource'  => constConfigPatchSourceWebSite,
            'serverurl'    => '',
            'propagate'    => constConfigPropSearch,
            'lastupdate'   => 0,
            'updatecache'  => constConfigCacheEnable,
            'cacheseconds' => 86400 * 14,
            'restart'      => constConfigRebootAuto,
            'chain'        => constConfigChainTimeout,
            'chainseconds' => 7200
        );
    }


   /*
    |  This should only be used in the unusual event
    |  that there is no default pcfg record yet.
    */

    function default_pcfg()
    {
        return array
        (
            'pconfigid'         => 0, // impossible
            'mgroupid'          => 0, // illegal
            'pgroupid'          => 0, // illegal
            'installation'      => constPatchScheduleInstall,
            'notifyadvance'     => 0,
            'notifyadvancetime' => 900,
            'scheddelay'        => 0,
            'schedminute'       => 0,
            'schedhour'         => 3,
            'schedday'          => 0,
            'schedmonth'        => 0,
            'schedweek'         => 7,
            'schedrandom'       => 0,
            'schedtype'         => constPatchTypeNext,
            'notifydelay'       => 0,
            'notifyminute'      => 0,
            'notifyhour'        => 16,
            'notifyday'         => 0,
            'notifymonth'       => 0,
            'notifyweek'        => 7,
            'notifyrandom'      => 0,
            'notifytype'        => constPatchTypeASAP,
            'notifyfail'        => 0,
            'configtype'        => constConfigTypeNormal,
            'reminduser'        => 0,
            'preventshutdown'   => 0,
            'notifyschedule'    => 0,
            'notifytext'        => '',
            'lastupdate'        => 0
        );
    }


   /*
    |  Finds a wcfg record based
    |  upon its own id code.
    */

    function find_wcfg_wid($wid,$db)
    {
        $row = array( );
        if ($wid > 0)
        {
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.WUConfig\n"
                 . " where id = $wid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  Finds a pcfg record based
    |  upon its own id code.
    */

    function find_pcfg_pid($pid,$db)
    {
        $row = array( );
        if ($pid > 0)
        {
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig\n"
                 . " where pconfigid = $pid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  Return the machine configuration
    |  for the specified machine.
    |
    |  It is likely that the machine may
    |  belong to many groups, in which
    |  case we want the group with the
    |  highest precedence.
    |
    |  If two groups have the same precedence,
    |  we'll pick the newest one, followed by
    |  the one that comes first in alphabetical
    |  order.
    */

    function find_correct_wconfig($hid,$db)
    {
        $row = array( );
        if ($hid > 0)
        {
            $sql = "select W.id as wid,\n"
                 . " G.mgroupid as gid,\n"
                 . " G.name as grp,\n"
                 . " G.created as tim,\n"
                 . " C.category as cat,\n"
                 . " C.mcatid as tid,\n"
                 . " C.precedence as pre\n"
                 . " from ".$GLOBALS['PREFIX']."softinst.WUConfig as W,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as A\n"
                 . " where M.censusuniq = A.censusuniq\n"
                 . " and A.id = $hid\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid = W.mgroupid\n"
                 . " and M.mcatuniq = C.mcatuniq\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " order by pre desc, tim desc, grp\n"
                 . " limit 1";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  Returns the correct pcfg record for the
    |  specified patch on the specified machine.
    |
    |   $hid -- ".$GLOBALS['PREFIX']."core.Census.id
    |   $mid -- softinst.Patches.patchid
    |
    |  Note there may be no correct pcfg record.
    |  In this case the return value will be empty.
    |
    |  Precedence goes by category priority,
    |  followed by age, and then last by
    |  name in alphabetical order.
    */

    function find_correct_pconfig($hid,$mid,$db)
    {
        $row = array( );
        if (($hid > 0) && ($mid > 0))
        {
            $sql = "select P.pconfigid as pid,\n"
                 . " G.mgroupid as gid,\n"
                 . " J.pgroupid as jid,\n"
                 . " G.name as mgrp,\n"
                 . " J.name as pgrp,\n"
                 . " G.created as mtim,\n"
                 . " J.created as ptim,\n"
                 . " C.category as mcat,\n"
                 . " K.category as pcat,\n"
                 . " C.mcatid as tid,\n"
                 . " K.pcategoryid as kid,\n"
                 . " C.precedence as mpre,\n"
                 . " K.precedence as ppre\n from"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroupMap as N,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchCategories as K,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as A\n"
                 . " where M.censusuniq = A.censusuniq\n"
                 . " and A.id = $hid\n"
                 . " and G.mgroupuniq = M.mgroupuniq\n"
                 . " and P.mgroupid = G.mgroupid\n"
                 . " and P.pgroupid = J.pgroupid\n"
                 . " and P.pgroupid = N.pgroupid\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " and N.patchid = $mid\n"
                 . " and J.pcategoryid = K.pcategoryid\n"
                 . " and M.mcatuniq = C.mcatuniq\n"
                 . " order by ppre desc, mpre desc, ptim desc, mtim desc, pgrp, mgrp\n"
                 . " limit 1";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  This attempts to find the default PatchConfig record
    |  for the specified machine.
    |
    |  This means that the pcfg record must be associated
    |  with the patch group 'All'.
    |
    |  It must also be associated with some machine group that
    |  the machine actually belongs to.
    |
    |  Note that this should work even if there are no patches
    |  yet.
    */

    function find_default_pconfig($hid,$db)
    {
        $row = array( );
        if ($hid > 0)
        {
            $all = safe_addslashes(constCatAll);
            $sql = "select P.*,\n"
                 . " G.name as mgrp,\n"
                 . " J.name as pgrp from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as A\n"
                 . " where M.censusuniq = A.censusuniq\n"
                 . " and A.id = $hid\n"
                 . " and P.mgroupid = G.mgroupid\n"
                 . " and G.mgroupuniq = M.mgroupuniq\n"
                 . " and P.pgroupid = J.pgroupid\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " and M.mcatuniq = C.mcatuniq\n"
                 . " and J.name = '$all'\n"
                 . " order by C.precedence desc, G.name\n"
                 . " limit 1";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  This comes up when a new machine has just been
    |  added to an existing site.   We want to allow
    |  this new machine to access the site pcfg record,
    |  even though it may not have been added to the
    |  site machine group yet.
    */

    function find_site_pconfig($hid,$db)
    {
        $row = array( );
        if ($hid > 0)
        {
            $all = safe_addslashes(constCatAll);
            $sql = "select P.*,\n"
                 . " G.name as mgrp,\n"
                 . " J.name as pgrp from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C\n"
                 . " where C.id = $hid\n"
                 . " and G.name = C.site\n"
                 . " and J.name = '$all'\n"
                 . " and P.mgroupid = G.mgroupid\n"
                 . " and P.pgroupid = J.pgroupid\n"
                 . " limit 1";
            $row = find_one($sql,$db);
        }
        return $row;
    }


   /*
    |  This attempts to find the 'global' PatchConfig
    |  record.  This is the one associated with the
    |  patch group 'All' and the machine group 'All'.
    |
    |  This should work even for new machines that have
    |  not yet been added to any machine groups.
    */

    function find_glbl_pcfg($db)
    {
        $all = safe_addslashes(constCatAll);
        $sql = "select P.* from\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P,\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
             . " where G.name = '$all'\n"
             . " and J.name = '$all'\n"
             . " and P.mgroupid = G.mgroupid\n"
             . " and P.pgroupid = J.pgroupid\n"
             . " limit 1";
        return find_one($sql,$db);
    }


   /*
    |  If the machine is new, it might not have been added
    |  to any machine groups yet.  However, we know that
    |  it should soon be added at least to the site group,
    |  and the global group.
    |
    |  So, we'll try the site group.  If it's there we'll
    |  use that, otherwise we'll revert to the global.
    |
    |  If the global isn't there we'll use a precomputed
    |  default record.
    */

    function search_wconfig($hid,$site,$db)
    {
        $wcfg = array( );
        $mgrp = '(none)';
        $row  = find_correct_wconfig($hid,$db);
        if ($row)
        {
            $wid  = $row['wid'];
            $mgrp = $row['grp'];
            $wcfg = find_wcfg_wid($wid,$db);
        }
        if (!$wcfg)
        {
            $mgrp = $site;
            $wcfg = find_wcfg_name($mgrp,$db);
        }
        if (!$wcfg)
        {
            $mgrp = constCatAll;
            $wcfg = find_wcfg_name($mgrp,$db);
        }
        if (!$wcfg)
        {
            $wcfg = default_wcfg();
        }
        if ($wcfg)
        {
            $wcfg['mgrp'] = $mgrp;
        }
        return $wcfg;
    }



   /*
    |  Attempt to find the correct pcfg record for the
    |  specified patch on the specified machine.
    |
    |   $hid -- core.Census.id
    |   $mid -- softinst.Patches.patchid
    |
    |  If we can't find any such pcfg record (for example
    |  if there isn't a patch group yet) then we'll just
    |  return a default patch configuration.
    |
    |  Note that passing in zero for the patch id will
    |  always return the default pcfg record.
    */

    function search_pconfig($hid,$mid,$db)
    {
        $save = true;
        $pgrp = '(none)';
        $mgrp = '(none)';
        $pcfg = array();
        $row  = find_correct_pconfig($hid,$mid,$db);
        if ($row)
        {
            $pid  = $row['pid'];
            $pgrp = $row['pgrp'];
            $mgrp = $row['mgrp'];
            $pcfg = find_pcfg_pid($pid,$db);
        }
        if ((!$pcfg) && (!$mid))
        {
            $pcfg = find_default_pconfig($hid,$db);
            $save = ($pcfg)? false : true;
        }
        if ((!$pcfg) && (!$mid))
        {
            $pcfg = find_site_pconfig($hid,$db);
            $save = ($pcfg)? false : true;
        }
        if (!$pcfg)
        {
            $mgrp = constCatAll;
            $pgrp = constCatAll;
            $pcfg = find_glbl_pcfg($db);
        }
        if (!$pcfg)
        {
            $pcfg = default_pcfg();
        }
        if (($pcfg) && ($save))
        {
            $pcfg['pgrp'] = $pgrp;
            $pcfg['mgrp'] = $mgrp;
        }
        return $pcfg;
    }
