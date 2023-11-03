<?php





    define('constButtonYes',  'Yes');
    define('constButtonNo',   'No');
    define('constButtonOk',   'OK');
    define('constButtonCan',  'Cancel');
    define('constButtonAll',  'Check all');
    define('constButtonNone', 'Uncheck all');
    define('constButtonDone', 'Done');
    define('constButtonAdd',  'Add new group');
    define('constButtonHlp',  'Help');
    define('constButtonSub',  'Search');
    define('constButtonBack', '< Back');
    define('constButtonReset', 'Reset Filename');

    define('constScopNone',  0);
    define('constScopAll',   1);
    define('constScopUser',  2);
    define('constScopSite',  3);
    define('constScopHost',  4);

    define('constPgCRIT', 'Wiz_CRIT_PG');
    define('constPgAPPR', 'Wiz_APPR_PG');
    define('constPgDECL', 'Wiz_DECL_PG');
    define('constPgREMV', 'Wiz_REMV_PG');


    function timestamp($x)
    {
        return ($x)? date('m/d/y H:i:s',$x) : 'never';
    }


    function shortdate($x)
    {
        return ($x)? date('m/d H:i',$x) : 'never';
    }


    function matchOld($act,$txt)
    {
        $tmp = "|$act|";
        return strpos($txt,$tmp);
    }


    function askyesno($n)
    {
        $in  = indent($n);
        $yes = button(constButtonYes);
        $no  = button(constButtonNo);
        return "<p>${in}${yes}${in}${no}</p>\n";
    }


    function okcancel_link($n, $custom)
    {
        if ($custom)
        {
            $n      = indent($n);
            $cancel = cancel_link($custom);
            $ok     = button(constButtonOk);
            return "<p>${n}${ok}${n}${cancel}</p>\n";
        }
        else
            return okcancel($n);
    }


    function okcancel($n)
    {
        $in  = indent($n);
        $ok  = button(constButtonOk);
        $can = button(constButtonCan);
        return "<p>${in}${ok}${in}${can}</p>\n";
    }


    function checkallnone($n)
    {
        $in = indent($n);
        $ck = button(constButtonAll);
        $un = button(constButtonNone);
        return "<p>${in}${ck}${in}${un}</p>\n";
    }


    function find_pub_mgrp_gid($gid,$auth,$db)
    {
        $row = array( );
        if ($gid > 0)
        {
            $qu  = safe_addslashes($auth);
            $sql = "select G.*, C.mcatid from\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
                 . " where mgroupid = $gid\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " and (username = '$qu'\n"
                 . " or global = 1)";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_pub_pgrp_jid($jid,$auth,$db)
    {
        $row = array( );
        if ($jid > 0)
        {
            $qu  = safe_addslashes($auth);
            $sql = "select * from\n"
                 . " PatchGroups\n"
                 . " where pgroupid = $jid\n"
                 . " and (username = '$qu'\n"
                 . " or global = 1)";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function census_gid($gid,$db)
    {
        $row = array( );
        if ($gid > 0)
        {
            $sql = "select C.* from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
                 . " where M.censusuniq = C.censusuniq\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid = $gid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_patch_mid($mid,$db)
    {
        $row = array( );
        if ($mid > 0)
        {
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.Patches\n"
                 . " where patchid = $mid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_wcfg_gid($gid,$db)
    {
        $row = array( );
        if ($gid > 0)
        {
            $sql = "select * from WUConfig\n"
                 . " where mgroupid = $gid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function kill_wcfg_gid($gid,$db)
    {
        $num = 0;
        if ($gid > 0)
        {
            $sql = "delete from WUConfig\n"
                 . " where mgroupid = $gid";
            $res = redcommand($sql,$db);
            $num = affected($sql,$db);
        }
        return $num;
    }


    function kill_pmap_jid($jid,$db)
    {
        $num = 0;
        if ($jid > 0)
        {
            $sql = "delete from PatchGroupMap\n"
                 . " where pgroupid = $jid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            debug_note("removed $num members from group $jid");
        }
        return $num;
    }


    function kill_pgrp_jid($jid,$db)
    {
        $num = 0;
        if ($jid > 0)
        {
            $sql = "delete from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups\n"
                 . " where pgroupid = $jid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num)
            {
                $text = "patch: pgrp removed (j:$jid,n:$num)";
                                debug_note($text);

                $sql = "delete from\n"
                     . " ".$GLOBALS['PREFIX']."softinst.PatchConfig\n"
                     . " where pgroupid = $jid";
                $res = redcommand($sql,$db);
                $tmp = affected($res,$db);
                debug_note("PatchConfig: $tmp removed");
                $sql = "delete from\n"
                     . " ".$GLOBALS['PREFIX']."softinst.PatchGroupMap\n"
                     . " where pgroupid = $jid";
                $res = redcommand($sql,$db);
                $tmp = affected($res,$db);
                debug_note("PatchGroupMaps: $tmp removed");
            }
        }
        return $num;
    }


    function kill_pcfg($gid,$jid,$db)
    {
        $num = 0;
        if (($gid > 0) && ($jid > 0))
        {
            $sql = "delete from PatchConfig\n"
                 . " where mgroupid = $gid\n"
                 . " and pgroupid = $jid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }

    function invalidate_pid($pid,$db)
    {
        $num = 0;
        if ($pid > 0)
        {
            $now = time();
            $sql = "update PatchStatus set\n"
                 . " patchconfigid = 0,\n"
                 . " lastchange = $now,\n"
                 . " lastconfigid = $pid\n"
                 . " where patchconfigid = $pid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }



    function touch_pid($pid,$db)
    {
        $num = 0;
        if ($pid > 0)
        {
            $now = time();
            $sql = "update PatchConfig set\n"
                 . " lastupdate = $now\n"
                 . " where pconfigid = $pid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }


    function touch_wid($wid,$db)
    {
        $num = 0;
        if ($wid > 0)
        {
            $now = time();
            $sql = "update WUConfig set\n"
                 . " lastupdate = $now\n"
                 . " where id = $wid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }


    function find_pcat_kid($kid,$db)
    {
        $row = array( );
        if ($kid > 0)
        {
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchCategories\n"
                 . " where pcategoryid = $kid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_pgrp_jid($jid,$db)
    {
        $row = array( );
        if ($jid > 0)
        {
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups\n"
                 . " where pgroupid = $jid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_pgrp_kid($kid,$db)
    {
        $set = array( );
        if ($kid > 0)
        {
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups\n"
                 . " where pcategoryid = $kid";
            $set = find_many($sql,$db);
        }
        return $set;
    }


    function find_expr_jid($jid,$db)
    {
        $set = array( );
        if ($jid > 0)
        {
            $sql = "select * from PatchExpression\n"
                 . " where pgroupid = $jid\n"
                 . " order by orterm, item, exprid";
            $set = find_many($sql,$db);
        }
        return $set;
    }


    function find_expr_tree($jid,$db)
    {
        $tree = array( );
        $set  = find_expr_jid($jid,$db);
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $eid = $row['exprid'];
                $blk = $row['orterm'];
                $tree[$blk][$eid] = $row;
                }
        }
        return $tree;
    }


    function find_site($cid,$auth,$db)
    {
        $row  = array( );
        $site = '';
        if (($cid > 0) && ($auth))
        {
            $qu  = safe_addslashes($auth);
            $sql = "select * from ".$GLOBALS['PREFIX']."core.Customers\n"
                 . " where id = $cid\n"
                 . " and username = '$qu'";
            $row = find_one($sql,$db);
        }
        if ($row)
        {
            $site = $row['customer'];
        }
        return $site;
    }


    function find_host($hid,$db)
    {
        $row = array( );
        if ($hid > 0)
        {
            $sql = "select * from ".$GLOBALS['PREFIX']."core.Census\n"
                 . " where id = $hid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function valid_host($cid,$hid,$db)
    {
        $row = array( );
        if (($hid) && ($cid))
        {
            $sql = "select X.* from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U\n"
                 . " where X.id = $hid\n"
                 . " and U.id = $cid\n"
                 . " and X.site = U.customer";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function delete_pmap($jid,$mid,$db)
    {
        $num = 0;
        if (($jid) && ($mid))
        {
            $sql = "delete from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroupMap\n"
                 . " where pgroupid = $jid\n"
                 . " and patchid = $mid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }

    function delete_expr_eid($eid,$db)
    {
        $num = 0;
        if ($eid > 0)
        {
            $sql = "delete from\n"
                 . " PatchExpression\n"
                 . " where exprid = $eid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }


    function delete_expr_jid($jid,$db)
    {
        $num = 0;
        if ($jid > 0)
        {
            $sql = "delete from\n"
                 . " PatchExpression\n"
                 . " where item = $jid\n"
                 . " or pgroupid = $jid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }


    function build_expr($neg,$kid,$val,$blk,$jid,$db)
    {
        $num = 0;
        if (($kid > 0) && ($blk > 0))
        {
            $sql = "insert into PatchExpression set\n"
                 . " item = $val,\n"
                 . " orterm = $blk,\n"
                 . " negation = $neg,\n"
                 . " pcatid = $kid,\n"
                 . " pgroupid = $jid";
            $res = redcommand($sql,$db);
            if (affected($res,$db) == 1)
            {
                $num = mysqli_insert_id($db);
            }
        }
        return $num;
    }




    function find_host_mgrp($hid,$db)
    {
        $row = array( );
        $cen = find_host($hid,$db);
        if ($cen)
        {
            $host = $cen['host'];
            $site = $cen['site'];
            $name = "$site:$host";
            $qn   = safe_addslashes($name);
            $tag  = constStyleBuiltin;
            $sql  = "select * from\n"
                  . " ".$GLOBALS['PREFIX']."core.MachineGroups\n"
                  . " where name = '$qn'\n"
                  . " and style = $tag\n"
                  . " and global = 1";
            $row  = find_one($sql,$db);
        }



        if (($cen) && (!$row))
        {
            $cat = safe_addslashes(constCatMachine);
            $xxx = "select G.mgroupid from\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as A\n"
                 . " where M.censusuniq = A.censusuniq\n"
                 . " and A.id = $hid\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and M.mcatuniq = C.mcatuniq\n"
                 . " and M.mcatuniq = G.mcatuniq\n"
                 . " and C.category = '$cat'";
            $grp = find_one($xxx,$db);
            if ($grp)
            {
                $gid = $grp['mgroupid'];
                $test = global_def('test_sql',0);
                $xxx = "update ".$GLOBALS['PREFIX']."core.MachineGroups\n"
                     . " set name = '$qn',\n"
                     . " revlname = revlname + 1, updated=UNIX_TIMESTAMP()\n"
                     . " where mgroupid = $gid";
                $err = constAppNoErr;
                if(!($test))
                {
                    $err = PHP_DSYN_InvalidateRow(CUR, (int)$gid, "mgroupid",
                        constDataSetCoreMachineGroups, constOperationDelete);
                    if($err!=constAppNoErr)
                    {
                                            }
                }
                if($err==constAppNoErr)
                {
                    redcommand($xxx,$db);
                    if(!($test))
                    {
                        DSYN_UpdateDependencies(constDataSetCoreMachineGroups,
                            $gid, $db);
                        PHP_REPF_UpdateDynamicList(CUR,
                            constJavaListEventMgrpInclude);
                        PHP_REPF_UpdateDynamicList(CUR,
                             constJavaListEventMgrpExclude);
                    }
                }

                $sql = "select * from ".$GLOBALS['PREFIX']."core.MachineGroups\n"
                     . " where mgroupid = $gid";
                $row = find_one($sql,$db);
            }
        }
        if (($row) && ($cen))
        {
            $row['site'] = $cen['site'];
            $row['host'] = $cen['host'];
        }
        return $row;
    }




    function scop_info($scop,&$mgrp)
    {
        if ($mgrp)
        {
            switch ($scop)
            {
                case constScopAll:
                case constScopUser:
                    return 'All machines';
                case constScopSite:
                    $site = $mgrp['name'];
                    return "Machines at the site <b>$site</b>";
                case constScopHost:
                    $host = $mgrp['host'];
                    $site = $mgrp['site'];
                    return "Machine <b>$host</b> at site <b>$site</b>";
                default:
                    $name = $mgrp['name'];
                    return "Machines in the machine group <b>$name</b>";
            }
        }
        return 'An Invalid Machine Group';
    }


    function find_site_mgrp($site,$db)
    {
        $row = array( );
        if ($site)
        {
            $qs  = safe_addslashes($site);
            $tag = constStyleBuiltin;
            $sql = "select * from\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups\n"
                 . " where name = '$qs'\n"
                 . " and style = $tag\n"
                 . " and human = 0\n"
                 . " and global = 1";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function preserve(&$env,$txt)
    {
        $tags = explode(',',$txt);
        if ($tags)
        {
            reset($tags);
            foreach ($tags as $key => $tag)
            {
                if(array_key_exists($tag, $env))
                {
                    echo hidden($tag,$env[$tag]);
                }
            }
        }
    }


    function hour_options()
    {
        return array
        (

            0 => '12 AM',  1 => ' 1 AM',  2 => ' 2 AM',  3 => ' 3 AM',
            4 => ' 4 AM',  5 => ' 5 AM',  6 => ' 6 AM',  7 => ' 7 AM',
            8 => ' 8 AM',  9 => ' 9 AM', 10 => '10 AM', 11 => '11 AM',
           12 => '12 PM', 13 => ' 1 PM', 14 => ' 2 PM', 15 => ' 3 PM',
           16 => ' 4 PM', 17 => ' 5 PM', 18 => ' 6 PM', 19 => ' 7 PM',
           20 => ' 8 PM', 21 => ' 9 PM', 22 => '10 PM', 23 => '11 PM',
           24 => '  Any'

        );
    }


    function minute_options()
    {
        $a1 = range(0,59);
        $a2 = array(60 => 'Any');
        return array_merge($a1,$a2);
    }




    function mday_options()
    {
        $a1 = array(0 => 'Any');
        $a2 = range(1,31);
        return array_merge($a1,$a2);
    }



    function month_options()
    {
        return array(

            0 => 'Any      ',
            1 => 'January  ',
            2 => 'February ',
            3 => 'March    ',
            4 => 'April    ',
            5 => 'May      ',
            6 => 'June     ',
            7 => 'July     ',
            8 => 'August   ',
            9 => 'September',
           10 => 'October  ',
           11 => 'November ',
           12 => 'December '

        );
    }



    function day_options()
    {
        return array(

            constConfigInstallDayEveryDay  => 'Every Day',
            constConfigInstallDaySunday    => 'Every Sunday',
            constConfigInstallDayMonday    => 'Every Monday',
            constConfigInstallDayTuesday   => 'Every Tuesday',
            constConfigInstallDayWednesday => 'Every Wednesday',
            constConfigInstallDayThursday  => 'Every Thursday',
            constConfigInstallDayFriday    => 'Every Friday',
            constConfigInstallDaySaturday  => 'Every Saturday'

        );
    }



    function wday_options()
    {
        return array(

            0 => 'Sunday   ',
            1 => 'Monday   ',
            2 => 'Tuesday  ',
            3 => 'Wednesday',
            4 => 'Thursday ',
            5 => 'Friday   ',
            6 => 'Saturday ',
            7 => 'Any      '

        );
    }


    function installation($ins)
    {
        switch ($ins)
        {
            case  constPatchInstallInvalid: return 'invalid';
            case    constPatchInstallNever: return 'decline';
            case constPatchScheduleInstall: return 'approve';
            case  constPatchScheduleRemove: return 'remove';
            default: return installation(constPatchInstallInvalid);
        }
    }




    function method($ins)
    {
        switch ($ins)
        {
            case    constPatchInstallNever: return 'manual';
            case constPatchScheduleInstall: return 'automatic';
            default: return installation($ins);
        }
    }


    function check_install($ins)
    {
        switch ($ins)
        {
            case    constPatchInstallNever: return $ins;
            case constPatchScheduleInstall: return $ins;
            case  constPatchScheduleRemove: return $ins;
            default: return constPatchInstallNever;
        }
    }

    function management($man)
    {
        switch ($man)
        {
            case        constConfigManagementInvalid: return 'invalid';
            case       constConfigManagementDisabled: return 'disabled';
            case         constConfigManagementServer: return 'managed';
            case           constConfigManagementUser: return 'user';
            case constConfigManagementInstallControl: return 'user install';
            case      constConfigManagementAutomatic: return 'automatic';
            default: return management(constConfigManagementInvalid);
        }
    }

    function propagate($prop)
    {
        switch ($prop)
        {
            case constConfigPropVendorOnly: return 'vendor';
            case  constConfigPropLocalOnly: return 'local';
            case     constConfigPropSearch: return 'search';
            default:                        return 'invalid';
        }
    }


    function semi_prop($prop)
    {
        switch ($prop)
        {
            case constConfigPropVendorOnly:
                return 'Only from vendor';
            case constConfigPropLocalOnly:
                return 'Only from local machines';
            case constConfigPropSearch:
                return 'Try local, then vendor';
            default:
                return propagate($prop);
        }
    }


    function long_prop($prop)
    {
        switch ($prop)
        {
            case constConfigPropVendorOnly:
                return 'only from the vendor';
            case constConfigPropLocalOnly:
                return 'only from local machines';
            case constConfigPropSearch:
                return 'from local machines first, then'
                   .   ' from the vendor if unsuccessful';
            default:
                return long_prop(constConfigPropVendorOnly);
        }
    }


    function newpatch($new)
    {
        switch ($new)
        {
            case     constConfigNewPatchesInvalid: return 'invalid';
            case constConfigNewPatchesLastDefault: return 'last default';
            case  constConfigNewPatchesWaitServer: return 'wait server';
            default: return newpatch(constConfigNewPatchesInvalid);
        }
    }


    function source($src)
    {
        switch ($src)
        {
            case   constConfigPatchSourceInvalid: return 'unknown';
            case   constConfigPatchSourceWebSite: return 'microsoft';
            case constConfigPatchSourceSUSServer: return 'sus';
            default: return source(constConfigPatchSourceInvalid);
        }
    }



    function update_pcfg($env,$pid,$pcfg,$db)
    {
        $num = 0;
        if (($pid) && ($pcfg))
        {
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

            $pshd = $pcfg['preventshutdown'];
            $nadv = $pcfg['notifyadvance'];
            $nsch = $pcfg['notifyschedule'];
            $nat  = $pcfg['notifyadvancetime'];

            $sql  = "update PatchConfig set\n"
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
                  . " notifytext = '$qt'\n";
            $where = '';
            if($env['wpgroupid'])
            {
                $sql2 = "SELECT mgroupid FROM PatchConfig WHERE "
                    . "pconfigid=$pid";
                $row = find_one($sql2, $db);
                $where .= " where wpgroupid=" . $env['wpgroupid'] . " AND "
                    . "mgroupid=" . $row['mgroupid'];
            }
            else
            {
                $where .= " where pconfigid = $pid";
            }
            $sql .= $where;
            $getsql = "SELECT * FROM PatchConfig $where";
            PCFG_AuditChange($env, $getsql, $pcfg, $db);
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num)
            {
                touch_pid($pid,$db);
            }
        }
        return $num;
    }


    function update_pcfg_default($pid,$db)
    {
        $pcfg = default_pcfg();
        $fake = array();
        $fake['wpgroupid'] = 0;
        return update_pcfg($fake,$pid,$pcfg,$db);
    }




    function update_wcfg($env,$wid,$wcfg,$db)
    {
        $num = 0;
        if (($wid) && ($wcfg))
        {
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

            $sql  = "update WUConfig set\n"
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
                  . " chainseconds = $chas\n"
                  . " where id = $wid";

            $sql2 = "SELECT * FROM WUConfig WHERE id=$wid";
            $newcfg = array();
            $newcfg['management'] = $man;
            $newcfg['installhour'] = $hour;
            $newcfg['installday'] = $iday;
            $newcfg['patchsource'] = $psrc;
            $newcfg['propagate'] = $prop;
            $newcfg['newpatches'] = $newp;
            $newcfg['serverurl'] = $wcfg['serverurl'];
            $newcfg['updatecache'] = $updc;
            $newcfg['cacheseconds'] = $csec;
            $newcfg['restart'] = $rest;
            $newcfg['chain'] = $chan;
            $newcfg['chainseconds'] = $chas;
            GRPW_AuditMachineChange($env, $sql2, $newcfg, $db);

            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num)
            {
                touch_wid($wid,$db);
                    }
        }
        return $num;
    }


    function update_pcfg_ins($pid,$ins,$db)
    {
        $num = 0;
        if ($pid)
        {
            $now = time();
            $sql = "update PatchConfig set\n"
                 . " lastupdate = $now,\n"
                 . " installation = $ins\n"
                 . " where pconfigid = $pid\n"
                 . " and installation != $ins";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);

        }
        return $num;
    }



    function PCFG_AuditChange($env, $getsql, $pcfg, $db)
    {
        $details = '';
        $set = find_many($getsql, $db);
        if($set)
        {
            foreach ($set as $key => $row)
            {
                $changes = '';
                $changes .= PCFG_PrintChange('installation', $row, $pcfg);
                $changes .= PCFG_PrintChange('reminduser', $row, $pcfg);
                $changes .= PCFG_PrintChange('configtype', $row, $pcfg);
                $changes .= PCFG_PrintChange('scheddelay', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedminute', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedhour', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedday', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedmonth', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedweek', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedrandom', $row, $pcfg);
                $changes .= PCFG_PrintChange('schedtype', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifydelay', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyminute', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyhour', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyday', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifymonth', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyweek', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyrandom', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifytype', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyfail', $row, $pcfg);
                $changes .= PCFG_PrintChange('preventshutdown', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyadvance', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyschedule', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifyadvancetime', $row, $pcfg);
                $changes .= PCFG_PrintChange('notifytext', $row, $pcfg);

                if($changes)
                {
                    $pgroupname = '';
                    $mgroupname = '';
                    $sql = "SELECT name FROM PatchGroups where pgroupid="
                        . $row['pgroupid'];
                    $row2 = find_one($sql, $db);
                    if($row2)
                    {
                        $pgroupname = $row2['name'];
                    }
                    if($row['pgroupid']!=$row['wpgroupid'])
                    {

                        $sql = "SELECT name FROM PatchGroups where pgroupid="
                            . $row['wpgroupid'];
                        $row2 = find_one($sql, $db);
                        if($row2)
                        {
                            $pgroupname .= " (wizard group " . $row2['name']
                                . ")";
                        }
                    }
                    $sql = "SELECT name FROM ".$GLOBALS['PREFIX']."core.MachineGroups WHERE "
                        . "mgroupid=" . $row['mgroupid'];
                    $row2 = find_one($sql, $db);
                    if($row2)
                    {
                        $mgroupname = $row2['name'];
                    }


                    if($details)
                    {
                        $details .= "<br><br>";
                    }

                    $details .= 'The update configuration for the update '
                        . "group $pgroupname for the machine group $mgroupname"
                        . ' was changed:<br>';
                    $details .= $changes;
                }
            }
        }

        if($details)
        {
            $user = '';
            if(array_key_exists('username', $env))
            {
                $user = $env['username'];
            }
            if($err!=constAppNoErr)
            {
                            }
        }
    }

    function PCFG_PrintChange($key, $oldRow, $newRow)
    {
        if($oldRow[$key]!=$newRow[$key])
        {
            return "$key changed from " . $oldRow[$key] . ' to '
                . $newRow[$key] . '<br>';
        }

        return '';
    }

?>
