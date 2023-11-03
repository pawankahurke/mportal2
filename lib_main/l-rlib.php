<?php



    define('constCatAll',      'All');
    define('constCatUser',     'User');
    define('constCatSite',     'Site');
    define('constCatMachine',  'Machine');

    define('constStyleInvalid',  0);
    define('constStyleBuiltin',  1);
    define('constStyleManual',   2);
    define('constStyleEvent',    3);      define('constStyleAsset',    4);      define('constStyleExpr',     5);
    define('constStyleSearch',   6);      define('constStyleType',     7);      define('constStyleDirectSearch',    8); 
    define('constDirtySet',  '1');
    define('constDirtyClr',  '0');

    define('constPatchDirty',  'patch_dirty');

   

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

   

    define('constConfigManagementInvalid',        0);
    define('constConfigManagementDisabled',       1);
    define('constConfigManagementServer',         2);
    define('constConfigManagementUser',           3);
    define('constConfigManagementInstallControl', 4);
    define('constConfigManagementAutomatic',      5);

   

    define('constConfigCacheInvalid',   0);
    define('constConfigCacheDisable',   1);
    define('constConfigCacheEnable',    2);

   

    define('constConfigInstallDayEveryDay',     1);
    define('constConfigInstallDaySunday',       2);
    define('constConfigInstallDayMonday',       3);
    define('constConfigInstallDayTuesday',      4);
    define('constConfigInstallDayWednesday',    5);
    define('constConfigInstallDayThursday',     6);
    define('constConfigInstallDayFriday',       7);
    define('constConfigInstallDaySaturday',     8);

   

    define('constConfigNewPatchesInvalid',      0);
    define('constConfigNewPatchesLastDefault',  1);
    define('constConfigNewPatchesWaitServer',   2);

   

    define('constConfigPatchSourceInvalid',     0);
    define('constConfigPatchSourceWebSite',     1);
    define('constConfigPatchSourceSUSServer',   2);

   

    define('constConfigPropVendorOnly',   0);
    define('constConfigPropLocalOnly',    1);
    define('constConfigPropSearch',       2);

   

    define('constPatchInstallInvalid',  0);
    define('constPatchInstallNever',    1);
      define('constPatchScheduleInstall', 4);
    define('constPatchScheduleRemove',  5);
 

   

    define('constPatchTypeASAP', 1);
    define('constPatchTypeNext', 2);

   

    define('constConfigTypeNormal',   0);
    define('constConfigTypeCritical', 1);
    define('constConfigTypeBeta',     2);

   

    define('constPatchCanUnknown', 0);
    define('constPatchCanYes',     1);
    define('constPatchCanNo',      2);

   

    define('constConfigRebootUnknown',  0);
    define('constConfigRebootDisable',  1);
    define('constConfigRebootAuto',     2);

   

    define('constConfigChainUnknown',  0);
    define('constConfigChainTimeout',  1);
    define('constConfigChainInfinite', 2);
    define('constConfigChainDisabled', 3);

    
    define('constPatchTypeAll',             -2);    
    define('constPatchTypeNotDisplayed',    -1);    
    define('constPatchTypeUndefined',       0);
    define('constPatchTypeUpdate',          1);
    define('constPatchTypeServicePack',     2);
    define('constPatchTypeRollup',          3);
    define('constPatchTypeSecurity',        4);
    define('constPatchTypeCritical',        5);
    
    define('constPatchTypeMandatory',       6);

    define('constPatchTypeUndefinedStr',    'Undefined');
    define('constPatchTypeUpdateStr',       'Update');
    define('constPatchTypeServicePackStr',  'Service Pack');
    define('constPatchTypeRollupStr',       'Rollup');
    define('constPatchTypeSecurityStr',     'Security');
    define('constPatchTypeCriticalStr',     'Critical');
    

    
    define('constPatchMandatoryUnknown',    0);
    define('constPatchMandatoryNo',         1);
    define('constPatchMandatoryYes',        2);

    
    define('constPatchMandatoryUpdateStr', 'Mandatory Update');
    define('constPatchMandatoryServicePackStr', 'Mandatory Service Pack');
    define('constPatchMandatoryRollupStr', 'Mandatory Rollup');
    define('constPatchMandatorySecurityStr', 'Mandatory Security');
    define('constPatchMandatoryCriticalStr', 'Mandatory Critical');

   

    define('constShmKey',  2721);
    define('constSemKey',  2722);
    define('constShmSize', 8192);
    define('constShmEnab',  1);         define('constShmData',  2);         define('constShmTime',  3);         
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


   

    function default_wcfg()
    {
        return array
        (
            'id'           => 0,             'mgroupid'     => 0,             'management'   => constConfigManagementServer,
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


   

    function default_pcfg()
    {
        return array
        (
            'pconfigid'         => 0,             'mgroupid'          => 0,             'pgroupid'          => 0,             'installation'      => constPatchScheduleInstall,
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
