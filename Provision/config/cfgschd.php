<?php

/*
Revision history:

Date        Who     What
----        ---     ----
12-Apr-05   BJS     copied from intruder.php
13-Apr-05   BJS     added explode_schedule(), compute_min/hour_list()
                    return_legit(), build_local_schedule()
14-Apr-05   BJS     added set_local_schedule(), list_to_string(),
                    conf_schedule()
15-Apr-05   BJS     added site settings, displaying all machines in a site.
                    moved string to assoc array, used to sort by
                    machine, minute, hour.
19-Apr-05   BJS     added _sort(),  string_to_list error checking &
                    replaced qoute w/addslashes.
20-Apr-05   BJS     added set_globalchache(), set_localcache(), set_revisions()
                    added sitewide setting table, and site exception table.
21-Apr-05   BJS     added site config, fixed machine config status tables.
22-Apr-05   BJS     added random value, fixed a 24hour single machine bug,
24-Apr-05   BJS     fixed a bug in config_gsit, fixed a few sql statements.
25-Apr-05   BJS     removed a '\n', +1 revl in sql. UI tweaks.
26-Apr-05   BJS     changed default schedule, update revl in set valu,
                    removed update global_cache() for single machine,
                    call site_revision for update site and host_revision
                    for a single machine, dont check for stat on each machine
                    get a list of all machines at selected site who are stat >0
27-Apr-05   BJS     intead of checking valu of each machine, get all stat > 0
                    vals. 'pack' table vals into procedures.
28-Apr-05   BJS     fixed a pre-select bug. fixed 'sorry try again'.
                    fixed set_global_schedule->set_global, set_local_schedule->
                    set_local, set_revision->host_revision, set_localcache->
                    host_dirty, return_local_schedule->one_locl. Fixed a single
                    machine schedule problem.
 4-May-05   BJS     eliminated needless sql queries, etc, etc.
 5-May-05   BJS     ui changes per Alex.
11-May-05   BJS     added detailed schedule.
12-May-05   BJS     dont use HTTP_POST, fixed links && schedule errors.
18-May-05   BJS     removed code from get_posted_data()
30-May-05   BJS     removed legacy procedures, fixed bug in build_local_schedule
                    when setting detailed schedule of every min or every hour.
31-May-05   BJS     compute_gl_time 'mins' to 'minutes', removed legacy procedures.
 1-Jun-05   BJS     replaced compute/min/hour_sp with compute_sp.
23-Jul-05   BJS     began changing to gconfig database.
03-Aug-05   BJS     gconfig added for machine, site & all sites.
04-Aug-05   BJS     working gconfig to replace siteman.
12-Sep-05   BTE     Added checksum invalidation code.
20-Sep-05   BJS     Removed bcmod() from compute_rand().
21-Sep-05   BJS     Fixed host_value(), must return string into array['valu'].
                    Use [*] not [1 thur n] when user selects every minute.
                    Random minute threshold set to 2.
                    Bugfix: 2864
                    Added: debug_note in many procedures to follow execution.
12-Oct-05   BTE     Changed references from gconfig to core.
24-Oct-05   BTE     Moved sub_local to GCFG_SubLocal in l-gcfg.php.
03-Nov-05   BTE     Changed VarValues.* statements to explicit columns.
07-Dec-05   BJS     find_site -> CWIZ_find_site.
15-Dec-05   BTE     Removed unused scop_local and some return columns in a SQL
                    statement.
15-Dec-05   BJS     find_site_mgrp() -> GCFG_find_site_mgrp()
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
30-Jan-06   BJS     Fixed explode_schedule( ) bug.
24-Apr-06   BTE     Bugs 2963 and 2974.
27-Apr-06   BTE     Bug 3292: Add group assignment reset function.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
03-May-06   BTE     Bug 3362: Do general testing and bugfixing for Scrip config
                    status page.
24-May-06   BTE     Bug 3270: Fix titles throughout the Scrip configurator
                    interface.
26-May-06   BTE     Bug 3386: Group management wizard change.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.  Fixed an expensive join that was not used.
24-Jun-06   BTE     Bug 3500: Config Wizards fail for 2.1 client.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
06-Dec-06   AAM     Bug 3936: fixed error in list_to_string that caused
                    incorrect schedule to be set.
06-Dec-06   AAM     More on bug 3936: correctly get old schedule value for
                    groups and use it to create new value.

*/

    ob_start();
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-csum.php'  );
include_once ( '../lib/l-cwiz.php'  );
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( 'common.php'  );


    /* constant defintions */

    define('constCodS177', 177);
    define('constVarS177', 'S00177Schedule');

    define('constEnabNone',  0);
    define('constEnabVoid',  1);
    define('constEnabWarn',  2);
    define('constEnabNo',    3);
    define('constEnabYes',   4);

    define('constMinSelected',1);
    define('constHourSelected',2);

    define('constMin',0);
    define('constHour',1);

    define('constNotSet','not set');
    define('constStatGlobal',0);
    define('constStatLocal',1);
    define('constStatLocalOverride',2);

    define('constGetPreset', 'constGetPreset');

    define('constRandPercent', .9);

    define('constAnyMonth',  'mnth_xx');
    define('constAnyHour',   'hour_xx');
    define('constAnyMDay',   'mday_xx');
    define('constAnyWDay',   'wday_xx');
    define('constAnyMint',   'mint_xx');

    define('constPostMonth',  'mnth');
    define('constPostHour',   'hour');
    define('constPostMinute', 'mint');
    define('constPostWeek',   'week');
    define('constPostMDay',   'mday');
    define('constPostWDay',   'wday');
    define('constPostYDay',   'yday');
    define('constPostMint',   'mint');

    function CFGS_Title($act,$site,$host,$env,$db)
    {
        $m = 'Scrip Execution Frequency';
        $mach = CWIZ_GetMachineString($site,$host,$env,$db);

        switch ($act)
        {
            case 'deny': return "$m - No Access";
            case 'csit': return "$m - Select Site";
            case 'chst': return "$m - Select Machine";
            case 'kill': ;
            case 'enab': return "$m - Schedule$mach";
            case 'scop':
                if(strcmp($mach,'')==0)
                {
                    return "$m";
                }
                else
                {
                    return "$m - Schedule$mach";
                }
                break;
            case 'done': return "$m - Finished Configuration$mach";
            case 'rset': return "$m - Restore Defaults$mach";
            case 'schd': return "$m - Change Confirmation$mach";
            case 'gsit': return "$m - Change Confirmation$mach";
            case 'alsg': return "$m - Change Confirmation$mach";
            case 'alds': return "$m - Schedule - All Machines";
            default    : return "$m$mach";
        }
    }


    function CFGS_Again(&$env)
    {
        $self= $env['self'];
        $dbg = $env['priv'];
        $cmd = "$self?act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link("$cmd=scop",'config');
        $a[] = html_link('../acct/census.php','census');
        if ($dbg)
        {
            $args = $env['args'];
            $href = ($args)? "$self?$args" : $self;
            $a[] = html_link("$cmd=rset",'reset');
            $a[] = html_link('../acct/index.php','home');
            $a[] = html_link($href,'again');
            return jumplist($a);
        }
    }


    function whatever(&$env,$db)
    {
        $msg = "Now What";
        echo para($msg);
    }

    function next_cancel()
    {
        $nxt = button(constButtonNxt);
        $can = button(constButtonCan);
        return para("$nxt $can");
    }

    function next_only()
    {
        $nxt = button(constButtonNxt);
        return para($nxt);
    }

    function enab_action($enab)
    {
        switch ($enab)
        {
            case constEnabNone: return 'enab';
            case constEnabVoid: return 'void';
            case constEnabYes : return 'done';
            case constEnabNo  : return 'done';
        }
    }

    function enab_value($enab)
    {
        switch ($enab)
        {
            case constEnabYes : return 1;
            case constEnabNo  : return 0;
            default: return -1;
        }
    }


    function tag_gen($name,$min,$max)
    {
        $out = array( );
        $tag = $name . '_%02d';
        for ($xx = $min; $xx <= $max; $xx++)
        {
            $out[$xx] = sprintf($tag,$xx);
        }
        return $out;
    }

    function month_array()
    {
        return array
          (
           1 => 'Jan',  2 => 'Feb',  3 => 'Mar',  4 => 'Apr',
           5 => 'May',  6 => 'Jun',  7 => 'Jul',  8 => 'Aug',
           9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
           );
    }

    function week_array()
    {
        return array
          (
           'Sun','Mon','Tue','Wed','Thu','Fri','Sat'
           );
    }

    function hour_array()
    {
        return array
          (
           0 => 'Midn.',  1 => ' 1 AM',  2 => ' 2 AM',  3 => ' 3 AM',
           4 => ' 4 AM',  5 => ' 5 AM',  6 => ' 6 AM',  7 => ' 7 AM',
           8 => ' 8 AM',  9 => ' 9 AM', 10 => '10 AM', 11 => '11 AM',
           12 => 'Noon ', 13 => ' 1 PM', 14 => ' 2 PM', 15 => ' 3 PM',
           16 => ' 4 PM', 17 => ' 5 PM', 18 => ' 6 PM', 19 => ' 7 PM',
           20 => ' 8 PM', 21 => ' 9 PM', 22 => '10 PM', 23 => '11 PM'
           );
    }

    function draw_schedule(&$env,&$shed,$db)
    {
        $out  = array( );
        $temp = array( );
        $cols = 6;

        $out[] = table_header();
        $out[] = pretty_header('Minutes',$cols);

        $set = tag_gen(constPostMinute,0,59);
        reset($set);
        foreach ($set as $mm => $tag)
        {
            $row = intval($mm % 10);
            $col = intval($mm / 10);
            $box = checkbox($tag,$shed[$tag]);
            $txt = sprintf('%s%02d',$box,$mm);
            $temp[$row][$col] = $txt;
        }

        $out[] = table_data($temp[0],0);
        $out[] = table_data($temp[1],0);
        $out[] = table_data($temp[2],0);
        $out[] = table_data($temp[3],0);
        $out[] = table_data($temp[4],0);
        $out[] = table_data($temp[5],0);
        $out[] = table_data($temp[6],0);
        $out[] = table_data($temp[7],0);
        $out[] = table_data($temp[8],0);
        $out[] = table_data($temp[9],0);

        $hor = hour_array();
        $set = tag_gen(constPostHour,0,23);
        reset($set);
        foreach ($set as $hh => $tag)
        {
            $row = intval($hh % 4);
            $col = intval($hh / 4);
            $txt = $hor[$hh];
            //   $txt = sprintf('%02d',$hh);
            $box = checkbox($tag,$shed[$tag]);
            $temp[$row][$col] = $box . $txt;
        }
        $out[] = pretty_header('Hours',$cols);
        $out[] = table_data($temp[0],0);
        $out[] = table_data($temp[1],0);
        $out[] = table_data($temp[2],0);
        $out[] = table_data($temp[3],0);

        $mon = month_array();
        $set = tag_gen(constPostMonth,1,12);
        reset($set);
        foreach ($set as $mm => $tag)
        {
            $xxx = $mm - 1;
            $row = intval($xxx % 2);
            $col = intval($xxx / 2);
            $txt = $mon[$mm];
            $box = checkbox($tag,$shed[$tag]);
            $txt = $box . $txt;
            $temp[$row][$col] = $txt;
        }
        $out[] = pretty_header('Months',$cols);
        $out[] = table_data($temp[0],0);
        $out[] = table_data($temp[1],0);
        $out[] = "</table>\n";

        $t1 = join("\n",$out);

        $out = array();
        $temp = array( );
        $rows = 5;
        $cols = 7;
        for ($row = 0; $row < $rows; $row++)
        {
            for ($col = 0; $col < $cols; $col++)
            {
                $temp[$row][$col] = '<br>';
            }
        }
        $wax = week_array();
        $set = tag_gen(constPostWDay,0,6);
        reset($set);
        foreach ($set as $ww => $tag)
        {
            $box = checkbox($tag,$shed[$tag]);
            $txt = $box . $wax[$ww];
            $temp[0][$ww] = $txt;
        }

        $out[] = table_header();
        $out[] = pretty_header('Days Of Week',$cols);
        $out[] = table_data($temp[0],0);
        $out[] = pretty_header('Days Of Month',$cols);
        $set = tag_gen(constPostMDay,1,31);
        reset($set);
        foreach ($set as $dd => $tag)
        {
            $xxx = $dd - 1;
            $col = intval($xxx % 7);
            $row = intval($xxx / 7);
            $box = checkbox($tag,$shed[$tag]);
            $txt = $box . $dd;
            $temp[$row][$col] = $txt;
        }
        $set = tag_gen(constPostWeek,1,5);
        reset($set);
        foreach ($set as $ww => $tag)
        {
            $row = $ww - 1;
            $box = checkbox($tag,$shed[$tag]);
            $out[] = table_data($temp[$row],0);
        }

        $out[] = "</table>\n";
        $t2 = join("\n",$out);
        return array($t1,$t2);
    }


    function sub_global($env,$gid,$host,$code,$valu,$name,$db)
    {
        $num = 0;
        $var = find_var($name,$code,$db);
        if (($var) && ($gid))
        {
            $vid = $var['varid'];
            $env['gid'] = $gid;
            GCFG_CreateScripValues(constCodS177, 0, $env['gid'], $env['scop'],
                $db);
            site_done($env,$env['site']['customer'],constCodS177,constVarS177,
                $valu,constSourceScripFreqWizard,$db);
        }
        else
        {
            $stat = "name:$name,g:$gid,c:$code";
            debug_note("sub global ($stat) failure");
        }
        return $num;
    }


    function choose_site(&$env,$db)
    {
        $auth= $env['auth'];
        $cid = $env['cid'];
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes(constVarS177);
        $cod = constCodS177;
        $sql = "select U.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
             . " ".$GLOBALS['PREFIX']."core.VarValues as X\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = G.name\n"
             . " and V.name = '$qn'\n"
             . " and V.scop = $cod\n"
             . " and V.varuniq = X.varuniq\n"
             . " and X.mgroupuniq = G.mgroupuniq\n"
             . " group by customer\n"
             . " order by CONVERT(customer USING latin1)";
        $set = find_many($sql,$db);
        if ($set)
        {
            $in = indent(5);
            echo post_self('myform');
            echo hidden('act','scop');
            echo hidden('pcn','scop');
            echo hidden('scop',$env['scop']);
            echo para('At which site would you like to set'
                    . ' frequency scheduler?');
            reset($set);
            foreach ($set as $key => $row)
            {
                $id   = $row['id'];
                $site = $row['customer'];
                $rad  = radio('cid',$id,$cid);
                echo "${in}$rad $site<br>\n";
            }
            echo next_cancel();
            echo form_footer();
        }
        else
        {
            echo para("You don't own any sites");
        }
    }


    function find_hosts(&$env,$db)
    {
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes(constVarS177);
        $cod = constCodS177;
        $cid = $env['cid'];
        $sql = "select C.id, C.host from\n"
             . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
             . " ".$GLOBALS['PREFIX']."core.ValueMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U\n"
             . " where U.id = $cid\n"
             . " and U.customer = C.site\n"
             . " and U.username = '$qu'\n"
             . " and V.name = '$qn'\n"
             . " and V.scop = $cod\n"
             . " and V.varuniq = M.varuniq\n"
             . " and M.censusuniq = C.censusuniq\n"
             . " group by C.host\n"
             . " order by CONVERT(C.host USING latin1)";
        return find_many($sql,$db);
    }


    function choose_host(&$env,$db)
    {
        $set = array();
        $hid = $env['hid'];
        $cid = $env['cid'];
        if ($cid)
        {
            $set = find_hosts($env,$db);
        }

        echo post_self('myform');
        if ($set)
        {
            $in = indent(5);
            echo hidden('act','scop');
            echo hidden('pcn','scop');
            echo hidden('cid', $env['cid']);
            echo hidden('scop',$env['scop']);
            echo para('At which machine would you like to set'
                    . ' frequency scheduler?');
            reset($set);
            foreach ($set as $key => $row)
            {
                $id   = $row['id'];
                $name = $row['host'];
                $rad = radio('hid',$id,$hid);
                echo "${in}$rad $name<br>\n";
            }
        }
        else
        {
            echo hidden('act','scop');
            echo hidden('pcn','scop');
            echo hidden('scop',0);
            echo para('There aren\'t any appropriate machines at this site.');
        }
        echo next_cancel();
        echo form_footer();
    }


    function find_enab(&$env,$db)
    {
        $scop = $env['scop'];
        if ($scop == constScopAll) return constEnabNone;
        if ($scop == constScopNone) return constEnabNone;
        @ $gid = $env['sgid'];
        $i = return_mgroup_value($gid,$db);
        if ($i == '') return constEnabVoid;
        return constEnabWarn;
    }

    function set_localcache(&$env,$db)
    {
        $hid = @$env['revl']['censusid'];
        return dirty_hid($hid,$db);
    }


    function set_global_schedule($env,$db)
    {
        debug_note('set_global_schedule()');
        $hid  = $env['hid'];
        $gid  = $env['sgid'];
        $x    = $env['serv'];
        $site = $env['site']['customer'];
        $host = $env['serv'];
        $code = constCodS177;
        $valu = $env['list_str'];
        $name = constVarS177;
        return sub_global($env,$gid,$host,$code,$valu,$name,$db);
    }


    function find_local($gid,$db)
    {
        $fcw = find_var(constVarS177, constCodS177, $db);
        $vid = ($fcw)? $fcw['varid'] : 0;

        $set = array();
        $set2 = array();
        if (($gid) && ($vid))
        {
            $qm  = safe_addslashes(constCatMachine);
            $sql = "select H.host, H.censusuniq from\n"
                 . " ".$GLOBALS['PREFIX']."core.ValueMap as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as H,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as A\n"
                 . " where G.mgroupid = $gid\n"
                 . " and X.varuniq = A.varuniq\n"
                 . " and A.varid = $vid\n"
                 . " and C.category = '$qm'\n"
                 . " and X.censusuniq = M.censusuniq\n"
                 . " and X.censusuniq = H.censusuniq\n"
                 . " and G.mgroupuniq = M.mgroupuniq\n"
                 . " group by X.censusuniq\n"
                 . " order by H.site, H.host";
            $set = find_many($sql,$db);

            if($set)
            {
                foreach ($set as $key => $row)
                {
                    $sql = "SELECT ".$GLOBALS['PREFIX']."core.MachineGroups.mgroupid FROM "
                        .$GLOBALS['PREFIX']."core.MachineGroups LEFT JOIN ".$GLOBALS['PREFIX']."core.MachineGroupMap "
                        . "ON (".$GLOBALS['PREFIX']."core.MachineGroups.mgroupuniq=".$GLOBALS['PREFIX']."core."
                        . "MachineGroupMap.mgroupuniq) LEFT JOIN ".$GLOBALS['PREFIX']."core.Machine"
                        . "Categories ON (".$GLOBALS['PREFIX']."core.MachineGroups.mcatuniq="
                        .$GLOBALS['PREFIX']."core.MachineCategories.mcatuniq) WHERE ".$GLOBALS['PREFIX']."core.Machine"
                        . "Categories.category='Machine' AND ".$GLOBALS['PREFIX']."core."
                        . "MachineGroupMap.censusuniq='" . $row['censusuniq']
                        . "'";
                    $thisRow = find_one($sql, $db);
                    if($thisRow)
                    {
                        $newRow = array();
                        $newRow['host'] = $row['host'];
                        $newRow['mgroupid'] = $thisRow['mgroupid'];
                        $set2[] = $newRow;
                    }
                }
            }
        }
        return $set2;
    }


    function return_global_schedule($cust,$db)
    {
         $row = site_value($cust,constCodS177,constVarS177,$db);
         return ($row)? $row['valu'] : '';
    }


    function set_local_schedule(&$env,$db)
    {
        debug_note('set_local_schedule()');
        $hid  = $env['hid'];
        $gid  = $env['hgid'];
        $x    = $env['serv'];
        $site = @$env['revl']['site'];
        $host = @$env['revl']['host'];
        $valu = $env['list_str'];
        if(!($gid))
        {
            $gid = $env['gid'];
        }
        return GCFG_SubLocal($hid,$gid,$x,constCodS177,$valu,constVarS177,
            $env['now'],constSourceScripFreqWizard,$db);
    }


    function explode_schedule($row)
    {
        /* explode doesn't work w/array's
         so get the string @ indx 'valu' */
        if (isset($row[0]))
        {
            $tmp = $row[0]['valu'];
        }
        else
        {
            $tmp = @$row['valu'];
        }

        /* " NOT ' to work correctly */
        $tmp = str_replace("\r","",$tmp);
        return explode("\n", $tmp);
    }

    /* PHP 5.0 doesn't package bc math utils by default */
    function mod($num, $base)
    {
        return ($num - $base * floor($num / $base));
    }


    /* returns 90% of the mod value
       if the value is not 0, else
       return 90% of the $time argv */
    function compute_rand($time,$morh)
    {
        if ($morh == constHourSelected)
        {
            $t = mod(1440,$time);
            if (!$t)
                return intval($time * constRandPercent);
            else
                return intval($t * constRandPercent);
        }
        if ($morh == constMinSelected && $time >= 2)
        {
            $t = mod(60,$time);
            if (!$t)
                return intval($time * constRandPercent);
            else
                return intval($t * constRandPercent);
        }
        else
            return 0;
    }


    /* compute a list for mins or hours
       explicitly does not include spec.
       ints                           */
    function compute_list($type,$avg)
    {
        debug_note('compute_list()');
        $list = array( );
        if ( ($type == 1) && ($avg == 1) )
        {
            // user selected every minute
            // so use shorthand notation
            $list[0] = '*';
            return $list;
        }
        $qt   = ($type)? (60 / $avg) : (24 / $avg);
        for ($i = 0; $i <= $qt; $i++)
        {
            $t = $avg * $i;

            /* min */
            if (($type) && ($t!=60))
                $list[$i] = $t;

            /* hour */
            if ((!$type) && ($t!=24))
                $list[$i] = $t;
        }
        return $list;
    }

   /* $list = (mins or hours) $c_list = (current schedule)
      $morh = (user's selected mins || hours)
      User selects mins: c_list[0] = user min list
                         c_list[1] = *
      User selects hour: c_list[0] = 0;
                         c_list[1] = user hour list
      Then copy entire array into str. Add "\n" w/each indx
      If a value is missing from c_list[n], set to default
      Copies in pre-computed $rand value.                */

    function list_to_string($list,$c_list,$morh,$rand)
    {
        debug_note('list_to_string()');
        $str = '';
        $str_list = implode(",",$list);

        if ($morh == constMinSelected) /* minutes */
        {
            $c_list[0] = $str_list;
            $c_list[1] = '*';
            $c_list[5] = $rand;
        }
        if ($morh == constHourSelected) /* hours */
        {
            $c_list[0] = 0;
            $c_list[1] = $str_list;
            $c_list[5] = $rand;
        }
        for ($i = 0; $i <= 7; $i++)
        {
            if (isset($c_list[$i]) && $c_list != '')
            {
                if ($i < 7)
                {
                    $str .= $c_list[$i] . "\n";
                }
                else /* because we don't want a newline on the last entry */
                    $str .= $c_list[$i];
            }
            else
            /* there is a problem, and the value's are missing
               so lets set it to defaults (every 1 hour)   */
           {
                switch($i)
                {
                    case 0: $t = 0;   break;
                    case 1: $t = '*'; break;
                    case 2: $t = '*'; break;
                    case 3: $t = '*'; break;
                    case 4: $t = '*'; break;
                    case 5: $t = 50;  break;
                    case 6: $t = 1;   break;
                    case 7: $t = 4;   break;
                }
                if ($i < 7)
                {
                    $str .= $t . "\n";
                }
                else
                    $str .= $t;
            }
        }
        return $str;
    }


    /* type 0 = min
       type 1 = hour
    */
    function return_legit_time($type)
    {
        $set = array();
        $max = ($type)? 24 : 60;
        for ($c=1; $c<$max; $c++)
        {
            $set[$c] = $c;
        }
        return $set;
    }


    function build_local_schedule(&$val)
    {
        debug_note('build_local_schedule()');
        reset($val);
        $list = array();
        $avg  = 1;
        $m    = 'Minutes';
        $h    = 'Hours';

        $e_min  = explode(',',@$val[0]);
        $e_hour = explode(',',@$val[1]);

        /* we don't know if the min field has more
           than one value. If it does we want the
           difference between the 1st and 2nd */
        if ( (isset($e_min[1])) && $e_min[0] >= 0)
        {
            $avg = ($e_min[1] - $e_min[0]);
            $list['des'] = $m;
        }
        /* we also do the same for the hour field,
           unless its set to run every hour */
        if ( (isset($e_hour[1])) && $e_hour[0] != '*')
        {
            $avg = ($e_hour[1] - $e_hour[0]);
            $list['des'] = $h;
        }

        /* this can only happen if the schedule is set via the detailed schedule,
           and 'every hour' or 'every min' checkbox is selected, Or it is set
           manually via config. If it is set to every minute, the hour field
           will = *,  min field will = * or 00. If set to every hour, the min
           field will = 00, and the hour field will = *                        */

        if ( ($e_hour[0] == '*') && ($e_min[0] == '00') && (!isset($e_min[1])) )
            $list['des'] = $h;

        if ( ($e_min[0] == '*') && ($e_hour[0] == '*') )
            $list['des'] = $m;

        $list['avg'] = $avg;
        return $list;
    }

    /* 1=min 0=hour */
    function compute_sp($type,$valu)
    {
        $d = ($type)? ' minutes.' : ' hours.';

        if ($valu == 1)
            $d = ($type)? ' minute.' : ' hour.';

        return(" to make scrip 177 execute every <b>$valu $d</b>");
    }


    /* compare_min/hour called by _sort() */
    function compare_hour($a,$b)
    {
        $ah = $a['hour'];
        $bh = $b['hour'];

        if ($ah != $bh)
            $cmp = ($ah > $bh)? 1 : -1;
        else
            $cmp = 0;
        return $cmp;
    }

    function compare_min($a,$b)
    {
        $am = $a['min'];
        $bm = $b['min'];

        if ($am != $bm)
            $cmp = ($am > $bm)? 1: -1;
        else
           $cmp = 0;
        return $cmp;
    }


    /* here I will break up the $tmp array for each
       set by min, hour and unset.
       Then I will sort each, and return the concated
       (sorted) array.
    */
    function _sort(&$tmp)
    {
        $s_array = array();
        $unset_a = array();
        $hour_a  = array();
        $min_a   = array();

        /* split the arrays into: min, hour, and unset. */
        reset($tmp);
        foreach ($tmp as $key => $data)
        {
            /* its an hour */
            if ($data['min'] == 0 && $data['hour'] > 0)
            {
                $hour_a[] = $data;
            }
            /* its a minute */
            else if ($data['hour'] == '*' && $data['min'] > 0)
            {
                $min_a[] = $data;
            }
            /* its not set .. these don't get sorted
               store them and tac them onto the end */
            else if($data['min'] == constNotSet || $data['hour'] == constNotSet)
            {
                $unset_a[] = $data;
            }
        }
        reset($unset_a);

        reset($hour_a);
        usort($hour_a,'compare_hour');

        reset($min_a);
        usort($min_a,'compare_min');

        return array_merge($min_a,$hour_a,$unset_a);
    }

    /* this displays the detailed schedule confirmation */
    function detailed_msg($dtl)
    {
        $shed = explode("\n",$dtl);
        $str  = '';

        for ($c=0; $c<8; $c++)
        {
            switch($c)
            {
                case 0: $str .= "<br>Minutes:"      . $shed[$c]; break;
                case 1: $str .= "<br>Hours:"        . $shed[$c]; break;
                case 2: $str .= "<br>Day of Month:" . $shed[$c]; break;
                case 3: $str .= "<br>Month:"        . $shed[$c]; break;
                case 4: $str .= "<br>Day of Week:"  . $shed[$c]; break;
                case 5: $str .= "<br>Random:"       . $shed[$c]; break;
                case 6: $str .= "<br>Type:"         . $shed[$c]; break;
                case 7: $str .= "<br>Fail limit:"   . $shed[$c]; break;
            }
        }
        return $str . '<br>';
    }


    function build_site_table(&$list, &$chost)
    {
        /* for each host, build array:
                                  [n] (
                                      [host] =>
                                      [hour] =>
                                      [min]  =>
                                      )
        pre-set drop boxes and radio buttons */

        $tmp = array();
        $set = array();
        $set[1] = 0;
        $set[2] = 0;
        $set[3] = 0;
        $set[4] = 0;
        $c      = 0;

        reset($list);
        foreach ($list as $key => $data)
        {
            $avg = '';
            $des = '';

            if (isset($data['avg']) && isset($data['des']))
            {
                $avg = $data['avg'];
                $des = $data['des'];

                if ($data['des'] == 'Hours')
                {
                    if($chost['host'] == $key ||
                       $chost['host'] == constGetPreset)
                    {
                        /* user selected this machine */
                        /* set the hour time */
                        /* hour radio button will be preselected */
                        /* save the hour, radio button and bit */

                        $pre_h = $data['avg'];
                        $xx = constHourSelected;
                        $set[2] = $pre_h;
                        $set[3] = $xx;
                        $set[4] = true;
                    }
                    $tmp[$c]['cust'] = $key;
                    $tmp[$c]['hour'] = $avg;
                    $tmp[$c]['min']  = 0;
                }
                if ($data['des'] == 'Minutes')
                {
                    if ($chost['host'] == $key ||
                        $chost['host'] == constGetPreset)
                    {
                        /* user selected this machine */
                        $pre_m = $data['avg'];
                        $xx = constMinSelected;
                        $set[1] = $pre_m;
                        $set[3] = $xx;
                        $set[4] = true;
                    }
                    $tmp[$c]['cust'] = $key;
                    $tmp[$c]['hour'] = '*';
                    $tmp[$c]['min']  = $avg;
                }
            }
            else
            {
                $tmp[$c]['cust'] = $key;
                $tmp[$c]['hour'] = constNotSet;
                $tmp[$c]['min']  = constNotSet;
            }
            $c++;
        }
        reset($tmp);
        $set[0] = $tmp;
        return $set;
    }


    /* this gets called from config_enab() */
    function build_enab_table(&$pack)
    {
        $min_static  = $pack['min_static'];
        $hour_static = $pack['hour_static'];
        $dhost       = $pack['dhost'];
        $m1          = $pack['m1'];
        $h1          = $pack['h1'];
        unset($pack);

        echo <<< METH

        <table border=1>
          <td>
          Update <b>$dhost</b> every:
          </td>

          <td>
          <table>

            <tr>
              <td>
              $m1 $min_static Minute
              </td>
            </tr>
            <tr>
              <td>
              $h1 $hour_static Hour
              </td>
            </tr>
          </table>
          </td>
        </tr>
        </table>

METH;

    }


    /* generic error message */
    function error_out()
    {
        $can = button(constButtonCan);
        echo post_self('myform');
        echo hidden('act','scop');
        echo hidden('pcn','scop');
        echo hidden('scop',0);
        echo "Could not successfully update schedule, please try again. <br><br> $can\n";
        echo form_footer();
    }


    function confirmation_msg(&$env,$msg)
    {
        $self = $env['self'];
        $href = '../acct/census.php';
        $wizh = 'index.php?act=wiz';
        $conf = html_link($self,'Go to the Scrip 177 Execution Frequency page');
        $wizp = html_link($wizh,'Go to the Configuration Wizard page');
        $cens = html_link($href,'Go to the Census page');
        echo <<< DONE
        <p>
           $msg
          <br>
           What would you like to do next?
        </p>

        <ul>
           <li>$conf</li>
           <li>$wizp</li>
           <li>$cens</li>
        </ul>
DONE;
    }


    /* detailed schedule link */
    function create_details(&$env,$act,$goto)
    {
        $self  = $env['self'];
        $cid   = $env['cid'];
        $hid   = $env['hid'];
        $gid   = $env['gid'];
        $self .= "?act=$act&goto=$goto&cid=$cid&hid=$hid&gid=$gid";
        return html_link($self,"<br>[ I want to specify schedule details ]");
    }


    function explode_detail_schedule($row)
    {
        $tmp = normalize($row);
        return explode("\n",$tmp);
    }


    /* this is the detailed schedule page for a single machine */
    function detail_enab(&$env,$db)
    {
        $l_tmp = array();
        $g_tmp = array();
        $l_row = array();

        $host  = @$env['revl']['host'] ? $env['revl']['host'] : 0;
        $cust  = @$env['revl']['site'];
        $gid   = $env['sgid'];

        $act   = 'schd';
        $pcn   = 'smds';
        $l_val = '';

        $env['xcpt']   = 1;
        $chost['host'] = $host;

        $mgroup_set = GCFG_find_site_mgrp($cust,$db);

        $mgroupid = @$mgroup_set['mgroupid'];
        if(!($mgroupid))
        {
            $mgroupid = $env['gid'];
        }

        /* here we build the site schedule */
        $g_row         = return_mgroup_value($gid,$db);
        $g_val         = explode_schedule($g_row);
        $g_list[$host] = build_local_schedule($g_val);

        /* check for machines using own schedule */
        $site_stat = find_local($gid,$db);
        if ($site_stat)
        {
            /* build the schedule */
            $mgrp_id       = $site_stat[0]['mgroupid'];
            $mgrp_valu     = return_mgroup_value($mgrp_id,$db);
            $l_val         = explode_schedule($mgrp_valu);
            $l_list[$host] = build_local_schedule($l_val);
        }
        if ($l_val) /* if there are local overrides */
        {
            /* we build the detailed schedule using those values */
            $set          = build_site_table($l_list,$chost);
            $l_tmp[$cust] = _sort($set[0]);
            $pre          = build_shed_detail($l_val);
            $schd         = config_detail($env,$pre,$db);
        }
        else /* we use the global schedule to set the detailed schedule */
        {
            /* builds an array of values to be fed to config_detail
               govern what preselects are done when drawing the
               detailed schedule */
            $pre = build_shed_detail($g_val);

            /* returns a completed detailed table, w/preselects */
            $schd = config_detail($env,$pre,$db);
        }
        /* build the global table */
        $set = build_site_table($g_list,$chost);
        $tmp = $set[0];
        $g_tmp[$cust] = $tmp;

        /* build the complete table */
        $fini = build_globlocl_table($g_tmp,$l_tmp);

        echo post_self('myform');
        echo hidden('act',$act);
        echo hidden('pcn',$pcn);
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('gid', $mgroupid);

        echo "Configure a schedule for machine <b>$host</b>.<Br>";
        echo $fini;
        echo $schd;
        echo form_footer();
    }


    /* scop = 3 */
    /* for a single machines */
    function choose_enab(&$env,$db)
    {
        debug_note('choose_enab()');
        $set       = array();
        $list      = array();
        $tmp       = array();
        $l_tmp     = array();
        $site_stat = array();
        $g_list    = array();
        $l_list    = array();

        $in     = indent(5);
        $flag   = false;
        $scop   = $env['scop'];
        $self   = $env['self'];
        $act    = 'schd';
        $goto   = 'smds'; //S.ingle M.achine D.etail S.chedule
        $header = '';

        /* current host and site */
        $host = @$env['revl']['host'] ? $env['revl']['host'] : 0;
        $cust = @$env['revl']['site'];

        $gid  = $env['sgid'];

        $schd_details = create_details($env,$act,$goto);

        /* default pre-sets */
        $pre_m = 1;
        $pre_h = 0;
        $xx    = 1;

        $mgroupid = $env['hgid'];
        if(!$mgroupid)
        {
            $mgroupid = $gid;
            if(!$mgroupid)
            {
                $mgroupid = $env['gid'];
            }
        }

        /* Create the variable now if it doesn't exist */
        GCFG_CreateScripValues(constCodS177, @$env['rev']['censusid'],
            $mgroupid, $scop, $db);

        echo post_self('myform');
        echo hidden('act','schd');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('scop',$env['scop']);
        echo hidden('gid', $mgroupid);

        switch($scop)
        {
        case constScopGroup:
            $mgroup_set = array();
            $mgroup_set['mgroupid'] = $mgroupid;

            $sql = "SELECT ".$GLOBALS['PREFIX']."core.MachineGroups.name FROM ".$GLOBALS['PREFIX']."core.MachineGroups "
                . "WHERE ".$GLOBALS['PREFIX']."core.MachineGroups.mgroupid=$mgroupid";
            $row = find_one($sql, $db);
            if($row)
            {
                $pack['dhost'] = "<b>group \"" . $row['name'] . "\"</b>";
                $cust = $row['name'];
            }
            else
            {
                $pack['dhost'] = '';
                $cust = '';
            }
            break;
        default:
            $mgroup_set = GCFG_find_site_mgrp($cust,$db);
            /* display selected host */
            $pack['dhost'] = '<b>' . $host . '</b>';
            break;
        }

        /* build the site schedule */
        $g_row         = return_mgroup_value($mgroup_set['mgroupid'],$db);
        $g_val         = explode_schedule($g_row);
        $g_list[$host] = build_local_schedule($g_val);
        $bg_list       = $g_list;
        /*--*/

        /* check for machine using own schedule */
        $site_stat = find_local($gid,$db);
        if ($site_stat)
        {
            /* build the machine schedule */
            $mgrp_id       = $site_stat[0]['mgroupid'];
            $mgrp_valu     = return_mgroup_value($mgrp_id,$db);
            $l_val         = explode_schedule($mgrp_valu);
            $l_list[$host] = build_local_schedule($l_val);
        }
        /*--*/

        /* unpack following values from set */
        /* tmp    = unsorted array */
        /* pre_m  = if minutes is pre-selected & its value */
        /* pre_h  = if hours ... */
        /* xx     = pre-selected radio button */
        /* set[4] = true if this is the machine you are updating */
        if ($g_list) /* global settings */
        {
            if (!isset($l_list))
            {
              /* presets (radio, html_select) only work when
                 the machine the user selected doesnt have a
                 local setting. In this case, if none exist,
                 we want to use the global setting for machine's
                 presets.                                  */
                 $chost['host'] = constGetPreset;
            }

            $set = build_site_table($g_list,$chost);
            $tmp = $set[0];
            if ($set[4])
            {
                $pre_m = $set[1];
                $pre_h = $set[2];
                $xx    = $set[3];
                $flag  = true;
            }
            $g_tmp[$cust] = $tmp;
        }
        if ($l_list) /* local overrides */
        {
            $set = build_site_table($l_list,$chost);
            if ($set[4])
            {
                $pre_m = $set[1];
                $pre_h = $set[2];
                $xx    = $set[3];
                $flag  = true;
            }
            $l_tmp[$cust] = _sort($set[0]);
        }
        if (!$flag)
        {
          /* !comment-outdated! */
          /* it means the machine selected doesn't have a local override, it needs
             the global schedule, but it didn't match any machines, because we only
             need to retrieve the global schedule once, it doesn't neccessarily match
             up with a machines siteman.local schedule, and it doesn't have a stat > 0
             so its not an exception, and won't meet that requirement as well. It
             will end up having pre-selects, not pre-selected. So we are going to
             use the global settings as its pre-selects.                          */
             $chost['host'] = constGetPreset;

             $set = build_site_table($bg_list,$chost);

             $pre_m = $set[1];
             $pre_h = $set[2];
             $xx    = $set[3];
        }

        $fini = build_globlocl_table($g_tmp,$l_tmp);

        $pack['m1'] = radio('time_radio',constMinSelected,$xx);
        $pack['h1'] = radio('time_radio',constHourSelected,$xx);

        $pack['min_static']  = html_select('min_choice',return_legit_time(0),$pre_m,0);
        $pack['hour_static'] = html_select('hour_choice',return_legit_time(1),$pre_h,0);

        echo $fini;
        echo build_enab_table($pack);

        echo $schd_details;

        echo next_cancel();
        echo form_footer();
    }


    function default_schedule()
    {
        $out = array
        (
           constAnyMonth => 1,
           constAnyMDay  => 1,
           constAnyWDay  => 1,
           constAnyHour  => 1,
           constAnyMint  => 0,
        );
        add_entries($out,constPostMonth,1,12);
        add_entries($out,constPostWeek,1,5);
        add_entries($out,constPostWDay,0,6);
        add_entries($out,constPostMDay,1,31);
        add_entries($out,constPostHour,0,23);
        add_entries($out,constPostMinute,0,60);
        $out['type_radio'] = 1;
        $out['fail']       = 5;
        $out['rand']       = 3;
        return $out;
}


    function add_entries(&$out,$name,$min,$max)
    {
        $set = tag_gen($name,$min,$max);
        reset($set);
        foreach ($set as $key => $tag)
        {
            $out[$tag] = 0;
        }
    }


    function get_posted_data(&$args)
    {
        $set = array();

        $set[constPostMint]  = array();
        $set[constPostHour]  = array();
        $set[constPostMDay]  = array();
        $set[constPostWDay]  = array();
        $set[constPostMonth] = array();

        /* schedule defaults can be set here for the following values */
        $set['type_radio'] = get_integer('type_radio',1);
        $set['fail']       = get_integer('fail',2);
        $set['rand']       = get_integer('rand',1);

        $set[constAnyMonth] = get_integer(constAnyMonth,0);
        $set[constAnyHour]  = get_integer(constAnyHour,0);
        $set[constAnyMDay]  = get_integer(constAnyMDay,0);
        $set[constAnyWDay]  = get_integer(constAnyWDay,0);
        $set[constAnyMint]  = get_integer(constAnyMint,0);

        reset($args);
        foreach ($args as $key => $data)
        {
            $key_type = substr($key,0,4);
            $key_valu = substr($key,-2,2);

            if ($key_valu != 'xx' && $data)
            {
                switch($key_type)
                {
                    case constPostMint  : if (!$set[constAnyMint])  $set[constPostMint][]  = $key_valu; break;
                    case constPostHour  : if (!$set[constAnyHour])  $set[constPostHour][]  = $key_valu; break;
                    case constPostWeek  : if (!$set[constAnyWeek])  $set[constPostWeek][]  = $key_valu; break;
                    case constPostMDay  : if (!$set[constAnyMDay])  $set[constPostMDay][]  = $key_valu; break;
                    case constPostWDay  : if (!$set[constAnyWDay])  $set[constPostWDay][]  = $key_valu; break;
                    case constPostMonth : if (!$set[constAnyMonth]) $set[constPostMonth][] = $key_valu; break;
                    default : break;
                }
            }
        }
        return $set;
    }


    function return_error_free(&$set)
    {
        debug_note('return_error_free()');
        if (!$set[constAnyMint] && !$set[constPostMint])
             $set[constPostMint][0] = 0;

        if (!$set[constAnyHour] && !$set[constPostHour])
             $set[constAnyHour] = 1;

        if (!$set[constAnyMDay] && !$set[constPostMDay])
             $set[constAnyMDay] = 1;

        if (!$set[constAnyMonth] && !$set[constPostMonth])
             $set[constAnyMonth] = 1;

        if (!$set[constAnyWDay] && !$set[constPostWDay])
             $set[constAnyWDay] = 1;

        return $set;
    }


    /* this function takes a detailed array schedule
       and creates a string that can be fed to the db */
    function schedule_to_string($set)
    {
        debug_note('schedule_to_string()');
        $str = '';
        $nl  = "\n";
        reset($set);

        if ($set[constAnyMint])
            $str .= '*' . $nl;
        else
            $str .= implode(",", $set[constPostMint]) . $nl;

        if ($set[constAnyHour])
            $str .= '*' . $nl;
        else
            $str .= implode(",", $set[constPostHour]) . $nl;

        if ($set[constAnyMDay])
            $str .= '*' . $nl;
        else
            $str .= implode(",", $set[constPostMDay]) . $nl;

        if ($set[constAnyMonth])
            $str .= '*' . $nl;
        else
            $str .= implode(",", $set[constPostMonth]) . $nl;

        if ($set[constAnyWDay])
            $str .= '*' . $nl;
        else
            $str .= implode(",", $set[constPostWDay]) . $nl;

        /* random */
        $str .= $set['rand'] . $nl;

        /* type I or II */
        $str .= $set['type_radio'] . $nl;

        /* fail limit */
        $str .= $set['fail'] . $nl;

        return $str;
    }


    /* called from config_enab() or detail_enab()
       sets the input for the database */
    function config_schd(&$env,$db)
    {
        debug_note('config_schd()');

        $list     = array();
        $list_str = '';
        $m        = ' minutes.';
        $h        = ' hours.';
        $rand     = 0;
        $num      = 0;

        $pcn    = $env['pcn'];
        $morh   = $env['morh'];
        $cust   = @$env['revl']['site'];
        $host   = @$env['revl']['host'] ? $env['revl']['host'] : 0;
        $hid    = @$env['revl']['censusid'];
        $code   = constCodS177;
        $name   = constVarS177;
        $set[0] = $name; /* localize needs an array of names */

        switch($env['scop'])
        {
        case constScopSite:
            $msg = "Successfully updated scrip schedule for machine "
                . "<b>$host</b> at site <b>$cust</b>";
            break;
        default:
            $sql = "SELECT MachineGroups.name FROM MachineGroups WHERE "
                . "MachineGroups.mgroupid=" . $env['gid'];
            $row = find_one($sql, $db);
            if($row)
            {
                $msg = "Successfully updated scrip schedule for group "
                    . "<b>" . $row['name'] . "</b>";
            }
            else
            {
                echo "gid=" . $env['gid'];
            }
            break;
        }

        /* this means we came from the config_enab details schedule
           and in this case, we want to process the posted variables,
           create a string with those values, and save it to the db.
        */
        if ($pcn == 'smds')
        {
            $post  = $env['post_vars'];
            $rand  = $env['rand'];
            $typer = $env['type_radio'];

            $detailed_schd   = get_posted_data($post);
            $detailed_schd   = return_error_free($detailed_schd);
            $env['list_str'] = schedule_to_string($detailed_schd);
            $msg .= ':<br>'  . detailed_msg($env['list_str']);
        }
        else /* user did not use the detailed scheduler */
        {
            /* user selected mins */
            if ($morh == constMinSelected)
            {
                $min  = $env['min'];
                $list = compute_list(1,$min);
                $rand = compute_rand($min,$morh);
                $msg .= compute_sp(1,$min);
            }
            /* user selected hours */
            if ($morh == constHourSelected)
            {
                $hour = $env['hour'];
                $list = compute_list(0,$hour);
                $rand = compute_rand($hour,$morh);
                $msg .= compute_sp(0,$hour);
            }
            if ($list) /* the list has been set */
            {
                /* Get the current schedule setting.  This is a pretty big
                    kludge. */
                switch ($env['scop'])
                {
                case constScopSite:
                    $mgrp = GCFG_find_site_mgrp($cust, $db);
                    $schd = return_mgroup_value($mgrp, $db);
                    break;
                case constScopHost:
                    $schd['valu'] = host_value($cust,$host,$code,$name,$db);
                    break;
                default:
                    $mgrp = $env['gid'];
                    $schd = return_mgroup_value($mgrp, $db);
                    break;
                }

                /* schedule (only mins || hours) */
                $x_schd = explode_schedule($schd);

                $printSched = print_r($x_schd, 1);
                debug_note("Current schedule = " . $printSched);

                /* entire newline seperated string schedule */
                $env['list_str'] = list_to_string($list,$x_schd,$morh,$rand);
            }
        }

        /* these database updates happen for both detailed and classic schedules */
        switch($env['scop'])
        {
        case constScopSite:
            set_local_schedule($env,$db);
            $num = set_localcache($env,$db);
            hid_revision($hid,time(),$db);
            break;
        case constScopAll:
            GCFG_CreateScripValues(constCodS177, 0, $env['gid'], $scop, $db);
            user_done($env,constCodS177,constVarS177,$env['list_str'],
                constSourceScripFreqWizard, $env['now'], $db);
            mgrp_revision($env['gid'], time(), $db);
            dirty_group($env['gid'], $db);
            break;
        default:
            $now = time();
            $mgrp = array();
            $mgrp = find_mgrp_info($env['gid'], $db);
            push_mgrp($mgrp,1,$now,'',constCodS177,$env['list_str'],
                constVarS177,constSourceScripFreqWizard,$db);
            mgrp_revision($env['gid'], time(), $db);
            dirty_group($env['gid'], $db);
            break;
        }

        /* localcache is not used for the newer client, so don't error_out if
            we cannot set it. */
        confirmation_msg($env,$msg);
    }


    function config_glob(&$env,$db)
    {
        debug_note('config_glob');
        $set = list_sites($env,$db);
        reset($set);
        if ($set)
        {
            $in = indent(5);
            echo post_self('myform');
            echo hidden('act','site');
            echo hidden('pcn','scop');
            echo hidden('scop',$env['scop']);
            echo para('At which site would you like to set'
                    . ' frequency scheduler?');
            reset($set);
            foreach ($set as $key => $row)
            {
                $id   = $row['id'];
                $site = $row['customer'];
                $rad  = radio('cid',$id,$cid);
                echo "${in}$rad $site<br>\n";
            }
            echo next_cancel();
            echo form_footer();
        }
        else
        {
            echo para("You don't own any sites");
        }
    }


    /* this is called from config_site(), and is used to
       create the detailed schedule for an entire site,
       as well as update the exceptions (localoverrides)*/
    function detail_site(&$env,$db)
    {
        debug_note('detail_site()');
        $l_tmp     = array();
        $site_stat = array();

        $xcpt = checkbox('xcpt',1);
        $xcpt = "Update Exceptions $xcpt";
        $chost['host'] = '';

        $cust = $env['site']['customer'];
        $gid  = $env['sgid'];

        $mgroup_set = GCFG_find_site_mgrp($cust,$db);
        $set        = find_hosts($env,$db);

        if ($set)
        {
            /* build the site schedule */
            $host          = $set[0]['host'];
            $g_row         = return_mgroup_value($mgroup_set['mgroupid'],
                $db);
            $g_val         = explode_schedule($g_row);
            $g_list[$host] = build_local_schedule($g_val);

            /* build the machine schedules */
            $site_stat = find_local($gid,$db);
            reset($site_stat);
            foreach ($site_stat as $key => $data)
            {
                $host          = $data['host'];
                $mgrp_id       = $data['mgroupid'];
                $mgrp_valu     = return_mgroup_value($mgrp_id,$db);
                $l_val         = explode_schedule($mgrp_valu);
                $l_list[$host] = build_local_schedule($l_val);
            }
            if ($l_list)
            {
                $bst           = build_site_table($l_list,$chost);
                $l_tmp[$cust]  = _sort($bst[0]);
            }

            /* populate the detailed schedule; set preselects */
            $g_row = explode_detail_schedule($g_row[0]['valu']);
            $pre   = build_shed_detail($g_row);
            $schd  = config_detail($env,$pre,$db);

            /* build the site table */
            $set          = build_site_table($g_list,$chost);
            $g_tmp[$cust] = _sort($set[0]);

            $fini = build_globlocl_table($g_tmp,$l_tmp);

            echo post_self('myform');
            echo hidden('act','stds');
            echo hidden('cid', $env['cid']);
            echo hidden('hid', $env['hid']);
            echo hidden('mid', $env['mid']);
            echo hidden('scop',$env['scop']);
            echo hidden('gid', $env['gid']);
            echo hidden('pcn','gsit');
            echo hidden('dtl',1);

            echo "Update Frequency for site <b>$cust</b>.<br>";
            echo $fini;
            echo $xcpt;
            echo "<br>";
            echo $schd;
            echo form_footer();
        }
    }


    /* called from config_site() */
    function build_csit_table(&$pack)
    {
        $min_static  = $pack['min_static'];
        $hour_static = $pack['hour_static'];
        $xcpt        = $pack['xcpt'];
        $cust        = $pack['cust'];
        $m1          = $pack['m1'];
        $h1          = $pack['h1'];
        unset($pack);

        echo <<< OKAY
        <table border=1>
          <td>
          Update <b>$cust</b> every:
          </td>

          <td>
          <table>

            <tr>
              <td>
              $m1 $min_static Minute
              </td>
            </tr>
            <tr>
              <td>
              $h1 $hour_static Hour
              </td>
            </tr>
          </table>
          $xcpt Update Exceptions
          </td>
        </tr>
        </table>

OKAY;

    }

    function return_mgroup_value($gid,$db)
    {
        $scop = constCodS177;
        $code = constVarS177;
        return find_valu($gid,$code,$scop,$db);
    }


    /*
      configure an entire site
      (single site)           */
    function CFGS_ConfigSite(&$env,$db)
    {
        debug_note('config_site()');
        $list      = array();
        $g_table   = array();
        $l_table   = array();
        $g_list    = array();
        $l_list    = array();
        $pack      = array();
        $site_stat = array();

        $chost  = '';
        $xx     = '';
        $pre_m  = 1;
        $pre_h  = 1;
        $bit    = true;

        $self = $env['self'];
        $cust = $env['site']['customer'];
        $gid  = $env['sgid'];

        $pack['cust'] = $cust;

        /* build_site_table() needs this value set */
        $chost['host'] = '';

        $mgroup_set = GCFG_find_site_mgrp($cust,$db);
        $set        = find_hosts($env,$db);

        echo post_self('myform');

        /* if $set is empty, user has no sites, or no access */
        if ($set)
        {
            $host = $set[0]['host'];

            echo hidden('act','gsit');
            echo hidden('cid', $env['cid']);
            echo hidden('hid', $env['hid']);
            echo hidden('mid', $env['mid']);
            echo hidden('scop',$env['scop']);
            echo hidden('gid', $env['gid']);
            echo hidden('pcn','gsit');
            echo hidden('dtl',0);

            /* build site schedule */
            $g_row         = return_mgroup_value($mgroup_set['mgroupid'],
                $db);
            $g_val         = explode_schedule($g_row);
            $g_list[$host] = build_local_schedule($g_val);

            /* build machine schedule(s) */
            $site_stat = find_local($gid,$db);
            reset($site_stat);
            foreach ($site_stat as $key => $data)
            {
                $host          = $data['host'];
                $mgrp_id       = $data['mgroupid'];
                $mgrp_valu     = return_mgroup_value($mgrp_id,$db);
                $l_val         = explode_schedule($mgrp_valu);
                $l_list[$host] = build_local_schedule($l_val);
            }

            /* get preselect values */
            $chost['host'] = constGetPreset;
            $set = build_site_table($g_list,$chost);

            $tmp   = $set[0];
            $pre_m = $set[1];
            $pre_h = $set[2];
            $xx    = $set[3];
            $g_table[$cust]  = $tmp;

            /* machine schedules are set */
            if ($l_list)
            {
                $set = build_site_table($l_list,$chost);
                $l_table[$cust] = _sort($set[0]);
            }

            $pack['g_table']     = ($g_table)? $g_table : '';
            $pack['l_table']     = ($l_table)? $l_table : '';
            $pack['m1']          = radio('time_radio',constMinSelected,$xx);
            $pack['h1']          = radio('time_radio',constHourSelected,$xx);
            $pack['min_static']  = html_select('min_choice',return_legit_time(0),$pre_m,0);
            $pack['hour_static'] = html_select('hour_choice',return_legit_time(1),$pre_h,0);
            $pack['xcpt']        = checkbox('xcpt',1);

            $act  = 'scop';
            $goto = 'stds';
            $schd_details = create_details($env,$act,$goto);
            $fini_table   = build_globlocl_table($g_table,$l_table);

            echo $fini_table;
            echo build_csit_table($pack);
            echo $schd_details;
        }
        else
        {
            echo hidden('act','scop');
            echo hidden('pcn','scop');
            echo hidden('scop',0);
            echo para('There aren\'t any appropriate machines at this site.');
        }

        echo next_cancel();
        echo form_footer();
    }


    /* this gets called after config_site()
       and does all the database operations
       (single site)
    */
    function config_gsit(&$env,$db)
    {
        debug_note('config_gsit()');
        $cust = $env['site']['customer'];
        $morh = $env['morh'];
        $xcpt = $env['xcpt'];
        $host = $env['serv'];
        $pcn  = $env['pcn'];
        $dtl  = $env['dtl'];
        $code = constCodS177;
        $name = constVarS177;
        $msg  = "Successfully updated scrip schedule for site <b>$cust</b>";
        $m    = ' minutes.';
        $h    = ' hours.';
        $rand = 0;
        $num  = 0;
        $local_hosts  = array();
        $global_hosts = array();
        $list         = array();
        $mgroup_set   = GCFG_find_site_mgrp($cust,$db);

        /* this means we came from config_site details schedule */
        if ($pcn == 'gsit' && $dtl)
        {
            $post = $env['post_vars'];
            $detailed_schd = get_posted_data($post);
            $detailed_schd = return_error_free($detailed_schd);
            $valu = schedule_to_string($detailed_schd);
            $msg .= ':<br>' . detailed_msg($valu);
        }
        else /* user did not come from detailed schedule */
        {
            if ($morh == constMinSelected)
            {
                $min  = $env['min'];
                $list = compute_list(1,$min);
                $rand = compute_rand($min, $morh);
                $msg .= compute_sp(1,$min);
            }
            if ($morh == constHourSelected)
            {
                $hour = $env['hour'];
                $list = compute_list(0,$hour);
                $rand = compute_rand($hour, $morh);
                $msg .= compute_sp(0,$hour);
            }
            if ($list) /* if the list is not set, we error out */
            {
                /* we always want to update the global schedule */
                /* check to see if global schedule exists */
                $c_list = return_mgroup_value($mgroup_set['mgroupid'],$db);
                if ($c_list)
                {
                    $c_list = explode_schedule($c_list);
                    $valu   = list_to_string($list,$c_list,$morh,$rand);
                }
            }
        }/* end else */

        if ($xcpt) /* machines are not excluded from update */
        {
            $last = time();
            clear_site_over($cust,constCodS177,$last,
                constSourceScripFreqWizard,$db);
        }
        /* update the database */
        $env['list_str'] = $valu;
        set_global_schedule($env,$db);

        dirty_site($cust,$db);
        site_revision($cust,time(),$db);
        confirmation_msg($env,$msg);
    }


    /* this gets called from config_alst */
    function detail_alst(&$env,$db)
    {
        debug_note('detail_alst()');
        $l_table = array();
        $l_list  = array();
        $tmp     = array();
        $ret     = array();

        $xcpt  = checkbox('xcpt',1);
        $xcpt  = "Update Exceptions $xcpt";
        $auth  = $env['auth'];
        $hosts = list_sites($env,$db);
        reset($hosts);
        foreach ($hosts as $key => $data)
        {
            $cust      = $data['customer'];
            $row       = GCFG_find_site_mgrp($cust,$db);
            $site_stat = find_local($row['mgroupid'],$db);
            reset($site_stat);
            foreach ($site_stat as $key => $data)
            {
                $host                 = $data['host'];
                $mgrp_id              = $data['mgroupid'];
                $mgrp_valu            = return_mgroup_value($mgrp_id,$db);
                $l_val                = explode_schedule($mgrp_valu);
                $l_list[$cust][$host] = build_local_schedule($l_val);
            }
        }
        reset($hosts);
        foreach ($hosts as $key => $data)
        {
            $tmp  = array();
            $cust = $data['customer'];

            $mgroup_set  = GCFG_find_site_mgrp($cust,$db);
            $g_row       = return_mgroup_value($mgroup_set['mgroupid'],$db);
            $g_val       = explode_schedule($g_row);

            $g_list[$cust]['valu'] = build_local_schedule($g_val);
        }
        reset($g_list);
        foreach ($g_list as $key => $data)
        {
            $chost['host'] = constGetPreset;
            $set = build_site_table($data,$chost);

            $tmp   = $set[0];
            $pre_m = $set[1];
            $pre_h = $set[2];
            $xx    = $set[3];
            $g_table[$key] = $tmp;
        }

        if ($l_list)
        {
            reset($l_list);
            foreach ($l_list as $key => $data)
            {
                $set = build_site_table($data,$chost);
                $l_table[$key] = _sort($set[0]);
            }
        }

        $pre   = build_shed_detail($g_val);
        $schd  = config_detail($env,$pre,$db);
        $fini  = build_globlocl_table($g_table,$l_table);

        echo post_self('myform');
        echo hidden('act','alsg');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('gid', $env['gid']);
        echo hidden('scop',$env['scop']);
        echo hidden('pcn','aldg');

        echo "Update Frequency for <b>all sites</b>.<br>";
        echo $fini;
        echo $xcpt;
        echo "<br><br>";
        echo $schd;
        echo form_footer();
    }


    /*
       configure all sites
                            */
    function config_alst(&$env,$db)
    {
        debug_note('config_alst()');
        $self      = $env['self'];
        $auth      = $env['auth'];
        $ret       = array();
        $l_list    = array();
        $g_table   = array();
        $l_table   = array();
        $pack      = array();
        $tmp       = array();
        $host_set  = array();
        $site_stat = array();
        $c         = 0;

        $schd_details = create_details($env,'alsg','alds');

        echo post_self('myform');
        echo hidden('act','alsg');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('gid', $env['gid']);
        echo hidden('scop',$env['scop']);
        echo hidden('pcn','alsg');

        /* for each site, build the list of machine schedules */
        $hosts = list_sites($env,$db);
        reset($hosts);
        foreach ($hosts as $key => $data)
        {
            $cust       = $data['customer'];
            $row        = GCFG_find_site_mgrp($cust,$db);
            $site_stat  = find_local($row['mgroupid'],$db);
            reset($site_stat);
            foreach ($site_stat as $key => $data)
            {
                $host                 = $data['host'];
                $mgrp_id              = $data['mgroupid'];
                $mgrp_valu            = return_mgroup_value($mgrp_id,$db);
                $l_val                = explode_schedule($mgrp_valu);
                $l_list[$cust][$host] = build_local_schedule($l_val);
            }
        }

        /* build each sites' schedule */
        reset($hosts);
        foreach ($hosts as $key => $data)
        {
            $cust = $data['customer'];

            $mgroup_set            = GCFG_find_site_mgrp($cust,$db);
            $g_row                 = return_mgroup_value($mgroup_set['mgroupid'],$db);
            $g_val                 = explode_schedule($g_row);
            $g_list[$cust]['valu'] = build_local_schedule($g_val);
        }

        /* create site tables, they will always be set */
        reset($g_list);
        foreach ($g_list as $key => $data)
        {
            $chost['host'] = constGetPreset;
            $set = build_site_table($data,$chost);

            $tmp   = $set[0];
            $pre_m = $set[1];
            $pre_h = $set[2];
            $xx    = $set[3];
            $g_table[$key] = $tmp;
        }
        if ($l_list)
        {
            reset($l_list);
            foreach ($l_list as $key => $data)
            {
                $header = pretty_header('Site Exception',3);
                $set = build_site_table($data,$chost);
                $l_table[$key] = _sort($set[0]);
            }
        }
        $pack['finished_table'] = build_globlocl_table($g_table,$l_table);

        $pack['m1'] = radio('time_radio',constMinSelected,$xx);
        $pack['h1'] = radio('time_radio',constHourSelected,$xx);

        $pack['min_static']  = html_select('min_choice',return_legit_time(0),$pre_m,0);
        $pack['hour_static'] = html_select('hour_choice',return_legit_time(1),$pre_h,0);

        $pack['xcpt'] = checkbox('xcpt',1);

        echo build_alst_table($pack);
        echo $schd_details;
        echo next_cancel();
        echo form_footer();
    }

    function build_alst_table(&$pack)
    {
        $finished_table = $pack['finished_table'];
        $min_static     = $pack['min_static'];
        $hour_static    = $pack['hour_static'];
        $xcpt           = $pack['xcpt'];
        $m1             = $pack['m1'];
        $h1             = $pack['h1'];
        unset($pack);

        return <<< YESS

        <table>
          <tr>
            <td>$finished_table</td>
          </tr>
        </table>

        <table border=1>
          <tr>
            <td>
              Update <b>all sites</b> every:
            </td>
            <td>

            <table>
              <tr>
                <td>
                  $m1 $min_static Minute
                </td>
              </tr>
              <tr>
                <td>
                  $h1 $hour_static Hour
                </td>
              </tr>
            </table>
                    $xcpt Update Exceptions
            </td>
          </tr>
        </table>

YESS;

    }

    /* builds the global and localoverride tables */
    function build_globlocl_table(&$g_table,&$l_table)
    {
        reset($g_table);
        reset($l_table);
        $finished_table = '<table border=1>'
                        . '<td colspan=2 bgcolor=333399>'
                        . '<font color=white><b>Site</b></td>'
                        . '<td colspan=1 bgcolor=333399>'
                        . '<font color=white><b>Scrip Execution Frequency</b></td>';
        foreach ($g_table as $key => $data)
        {
            reset($data);
            foreach ($data as $k => $d)
            {
                $time = compute_gl_time($d);
                /* sitewide settings */
                $finished_table .= '<tr>'
                                 . '<td colspan=2><b>' . $key . '</b></td>'
                                 . '<td align=center><b>' . $time . '</b></td>'
                                 . '</tr>';
                if (isset($l_table[$key]))
                {
                    /* if a local override is set for that site ($key)
                       then the table will be built into the sitewide
                       table.                                       */
                    foreach ($l_table[$key] as $site => $mach)
                    {
                        $time = compute_gl_time($mach);
                        $finished_table .= '<tr>'
                                        .  '<td>&nbsp&nbsp</td>'
                                        .  '<td>' . $mach['cust'] . '</td>'
                                        .  '<td align=center>' . $time . '</td>'
                                        .  '</tr>';
                    }
                }
            }
            /* finished a single site here */
        }
        $finished_table .= '</table><br><br>';
        return $finished_table;
    }

    function compute_gl_time($d)
    {
        $time = '';
        if ($d['hour'] >= 1 && $d['min'] == 0)
        {
            if ($d['hour'] == 1)
                return $d['hour'] . ' hour';
            else
                return $d['hour'] . ' hours';
        }
        if ($d['min'] >= 1 && $d['hour'] == '*')
        {
            if ($d['min'] == 1)
                return $d['min'] . ' minute';
            else
                return $d['min'] . ' minutes';
        }
    }

    /* returns an array of sites user has access to */
    function list_sites(&$env,$db)
    {
        $auth = $env['auth'];
        $cid  = $env['cid'];
        $qu   = safe_addslashes($env['auth']);
        $qn   = safe_addslashes(constVarS177);
        $cod  = constCodS177;
        $sql  = "select U.* from\n"
              . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
              . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
              . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
              . " ".$GLOBALS['PREFIX']."core.VarValues as X\n"
              . " where U.username = '$qu'\n"
              . " and U.customer = G.name\n"
              . " and V.name = '$qn'\n"
              . " and V.scop = $cod\n"
              . " and V.varuniq = X.varuniq\n"
              . " and X.mgroupuniq = G.mgroupuniq\n"
              . " group by customer\n"
              . " order by customer";
        return find_many($sql,$db);
    }


    /* this is called after config_alst,
       and sets all database values.
       (config_ all sites global)     */

    function config_alsg(&$env,$db)
    {
        debug_note('config_alsg()');
        $local_hosts  = array();
        $global_hosts = array();
        $list         = array();
        $morh = $env['morh'];
        $xcpt = $env['xcpt'];
        $host = $env['serv'];
        $pcn  = $env['pcn'];
        $code = constCodS177;
        $name = constVarS177;
        $msg  = "Successfully updated scrip schedule for <b>all sites</b>";
        $num  = 0;
        $last = time();

        if ($pcn == 'aldg')
        {
            $post          = $env['post_vars'];
            $detailed_schd = get_posted_data($post);
            $detailed_schd = return_error_free($detailed_schd);
            $valu          = schedule_to_string($detailed_schd);
            $list          = 1; /* want to re-use the code below */
            $msg .= ':<br>' . detailed_msg($valu);
        }
        else
        {
            if ($morh == constMinSelected)
            {
                $min  = $env['min'];
                $list = compute_list(1,$min);
                $rand = compute_rand($min, $morh);
                $msg .= compute_sp(1,$min);
            }
            if ($morh == constHourSelected)
            {
                $hour = $env['hour'];
                $list = compute_list(0,$hour);
                $rand = compute_rand($hour, $morh);
                $msg .= compute_sp(0,$hour);
            }
        }
        if ($list)
        {
            /* get a list of all sites user has access to */
            $set = list_sites($env,$db);

            /* update all exceptions for all sites */
            if ($xcpt)
            {
                reset($set);
                foreach ($set as $key => $site)
                {
                    $cust = $site['customer'];
                    clear_site_over($cust,constCodS177,$last,
                        constSourceScripFreqWizard,$db);
                }
            }

            /* update site schedule for each site */
            reset($set);
            foreach ($set as $key => $data)
            {
                $cust       = $data['customer'];
                $mgroup_set = GCFG_find_site_mgrp($cust,$db);
                if ($pcn != 'aldg')
                {
                    /* if a detailed schedule was selected, that is currently in
                       $valu. If a simple schedule was selected, we use the current
                       site schedule and only update the hour, min and rand field. */
                    $c_list = return_mgroup_value($mgroup_set['mgroupid'],$db);
                    if ($c_list)
                    {
                        $c_list = explode_schedule($c_list);
                        $valu   = list_to_string($list,$c_list,$morh,$rand);
                    }
                }

                /* set the database values, for each $customer
                   $valu with be a simple or detailed schedule */
                $env['sgid']             = $mgroup_set['mgroupid'];
                $env['list_str']         = $valu;
                $env['site']['customer'] = $cust;

                set_global_schedule($env,$db);

                dirty_site($cust,$db);

                $num = site_revision($data['customer'],time(),$db);
            } /* end while */
        }     /* end if ($list) */

        confirmation_msg($env,$msg);
    }


    function build_shed_detail($g_list)
    {
        $pre  = default_schedule();
        $fini = default_schedule();

        $mint_list = explode(',',$g_list[0]);
        $hour_list = explode(',',$g_list[1]);
        $mday_list = explode(',',$g_list[2]);
        $mnth_list = explode(',',$g_list[3]);
        $wday_list = explode(',',$g_list[4]);
        $rand      = $g_list[5];
        $type      = $g_list[6];
        $fail      = $g_list[7];

        reset($pre);
        foreach ($pre as $key => $data)
        {
            /* for type I or II */
            if ($key == 'type_radio')
            {
                $fini[$key] = $type;
            }
            /* for rand */
            if ($key == 'rand')
            {
                $fini[$key] = $rand;
            }
            /* for fail */
            if ($key == 'fail')
            {
                $fini[$key] = $fail;
            }

            $type_key = substr($key,0,4);
            $valu_key = substr($key,-2,2);

            /* for minutes */
            if ($type_key == 'mint')
            {
                if ($mint_list[0] == '*')
                {
                    $fini['mint_xx'] = 1;
                }
                else
                {
                    for ($c=0;$c<=60;$c++)
                    {
                        if (isset($mint_list[$c]) && ($mint_list[$c] == $valu_key))
                        {
                            $fini[$type_key . "_$valu_key"] = 1;
                         }
                    }
                }
            }
            /* for hours */
            if ($type_key == 'hour')
            {
                if ($hour_list[0] == '*')
                {
                    $fini['hour_xx'] = 1;
                }
                else
                {
                    $fini['hour_xx'] = 0;
                    for ($c=0;$c<23;$c++)
                    {
                        if (isset($hour_list[$c]) && ($hour_list[$c] == $valu_key))
                        {
                            $fini[$type_key . "_$valu_key"] = 1;
                        }
                    }
                }
            }
            /* for day of month */
            if ($type_key == 'mday')
            {
                if ($mday_list[0] == '*')
                {
                    $fini['mday_xx'] = 1;
                }
                else
                {
                    $fini['mday_xx'] = 0;
                    for ($c=0; $c<=31;$c++)
                    {
                        if (isset($mday_list[$c]) && ($mday_list[$c] == $valu_key))
                        {
                            $fini[$type_key . "_$valu_key"] = 1;
                        }
                    }
                }
            }
            /* for month */
            if ($type_key == 'mnth')
            {
                if ($mnth_list[0] == '*')
                {
                    $fini['mnth_xx'] = 1;
                }
                else
                {
                    $fini['mnth_xx'] = 0;
                    for ($c=0;$c<=12;$c++)
                    {
                        if (isset($mnth_list[$c]) && ($mnth_list[$c] == $valu_key))
                        {
                            $fini[$type_key . "_$valu_key"] = 1;
                        }
                    }
                }
            }
            /* for weekday */
            if ($type_key == 'wday')
            {
                if ($wday_list[0] == '*')
                {
                    $fini['wday_xx'] = 1;
                }
                else
                {
                    $fini['wday_xx'] = 0;
                    for ($c=0;$c<=6;$c++)
                    {
                        if (isset($wday_list[$c]) && ($wday_list[$c] == $valu_key))
                        {
                            $fini[$type_key . "_$valu_key"] = 1;
                        }
                    }
                }
            }

        }/* end while */
        return $fini;
    }


    /* this is the advanced detail table */
    function config_detail($env,$pre,$db)
    {
        debug_note('config_detail()');
        $self = $env['self'];
        $self = '../config/help/s00177.htm';
        $link = html_link($self,'Scrip 177 Help page');

        $schd  = draw_schedule($env,$pre,$db);
        $schd0 = $schd[0];
        $schd1 = $schd[1];

        $mm  = checkbox(constAnyMonth,$pre[constAnyMonth]);
        $dd  = checkbox(constAnyMDay,$pre[constAnyMDay]);
        $ww  = checkbox(constAnyWDay,$pre[constAnyWDay]);
        $hh  = checkbox(constAnyHour,$pre[constAnyHour]);
        $mt  = checkbox(constAnyMint,$pre[constAnyMint]);
        $nxt = button(constButtonNxt);
        $can = button(constButtonCan);

        $t1 = radio('type_radio',1,$pre['type_radio']);
        $t2 = radio('type_radio',2,$pre['type_radio']);

        $r = textbox('rand',5,$pre['rand']);

        $f = html_select('fail',array(1,2,3,4,5,6,7,8,9,10),$pre['fail'],0);

        $xn = indent(4);


return <<< WOOT

           $link<br>
           <br><b>Schedule:</b><br>
           ${xn}$mm Any Month of Year<br>
           ${xn}$dd Any Day Of Month<br>
           ${xn}$ww Any Day Of Week<br>
           ${xn}$hh Any Hour Of Day<br>
           ${xn}$mt Any Minute Of Hour<br>
           ${xn}$t1 Run at scheduled time or as soon as possible after its scheduled time. <br>
           ${xn}$t2 Run at schedule time or next scheduled time if previous is missed. <br>
           ${xn}Choose a random delay $r <i>(in minutes)</i><br>
           ${xn}Failure Notify $f <i>(0 = Never, 1 = Every failure,
                                      2 = 2 consecutive failes,
                                      3 = 3 consecutive failuers)</i><br>
        <br clear="all">

        <table width="100%">
        <tr valign="top">
          <td align="left" width="60%">
            $schd0
            $schd1
          </td>
        </tr>
        </table>
        <br>
            $nxt $can
      </td>
    </tr>
    </table>

WOOT;

    }


    function wiz_done(&$env,$db)
    {
        debug_note('wiz_done()');
        $scop = $env['scop'];
        $self = $env['self'];
        $href = "$self?act=ghlp";
        $here = html_page($href,'click here');
        if (($scop == constScopHost) && ($env['revl']))
        {
            $site = $env['revl']['site'];
            $host = $env['revl']['host'];
            $what = "machine <b>$host</b> at site <b>$site</b>";
        }
        if (($scop == constScopSite) && ($env['site']))
        {
            $site = $env['site']['customer'];
            $what = "machines at site <b>$site</b>";
        }
        if ($scop == constScopNone)
        {
            $what = "no machines";
        }
        if ($scop == constScopAll)
        {
            $what = "all machines";
        }
        $href = '../acct/census.php';
        $conf = html_link($self,'Configure more machines');
        $cens = html_link($href,'Go to the Census page');
        echo <<< DONE

        <p>
           You have configured frequency scheduler for $what.
           What would you like to do next?
        </p>

        <ul>
           <li>$conf</li>
           <li>$cens</li>
        </ul>

DONE;

    }

    function config_void(&$env,$db)
    {
        debug_note('config_void');
    }


    function green($msg)
    {
        return "<font color=\"green\">$msg</font>";
    }

    function debug_array($debug,$p)
    {
        if ($debug)
        {
            reset($p);
            foreach ($p as $key => $data)
            {
                $msg = green("$key: $data");
                echo "$msg<br>\n";
            }
        }
    }

   /*
    |  Main program
    */

    $now = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();

    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?  1 : 0;
    $debug = @ ($user['priv_debug'])?  1 : 0;
    $admin = @ ($user['priv_admin'])?  1 : 0;
    $cnfg  = @ ($user['priv_config'])? 1 : 0;

    $hid = get_integer('hid',0);
    $mid = get_integer('mid',0);
    $cid = get_integer('cid',0);
    $tid = get_integer('tid',0);
    $gid = get_integer('gid',0);

    /* user value for min and hour fields */
    $min  = get_integer('min_choice',0);
    $hour = get_integer('hour_choice',0);

    /* user selected min or hour */
    $morh = get_integer('time_radio',0);

    /* user wants to update site exceptions (config site)*/
    $xcpt = get_integer('xcpt',0);

    /* user came from detailed schedule */
    $dtl = get_integer('dtl',0);

    $act  = get_string('act','scop');
    $hlp  = get_string('hlp','');
    $pcn  = get_string('pcn','');
    $goto = get_string('goto','');
    //$rand = get_string('rand','');

    $map = $act;

    $post = get_string('button','');
    $scop = get_integer('scop',constScopNone);
    $enab = get_integer('enab',constEnabNone);

    if ($post == constButtonCan)
    {
        $scop = constScopNone;
        $enab = constEnabNone;
        $act  = 'scop';
        $cid  = 0;
        $mid  = 0;
        $hid  = 0;
    }

    if ($post == constButtonHlp)
    {
        $act  = $hlp;
    }

    $revl = full_revl($hid,$auth,$db);
    $site = CWIZ_find_site($cid,$auth,$db);

    if (($revl) && (!$scop))
    {
        $scop = constScopHost;
    }
    if (($site) && (!$scop))
    {
        $scop = constScopSite;
    }

    $mgrp = find_mgrp_info($gid,$db);
    $map = CWIZ_MapScop($scop, $revl, $enab, $site, $tid, $mgrp, 0, $db);

    if ($scop == constScopHost)
    {
        if ($revl)
        {
            $map = enab_action($enab);
        }
        else
        {
            $map = ($site)? 'chst' : 'csit';
        }
    }

    if ($scop == constScopSite)
    {
        if ($site)
        {
            $map = enab_action($enab);
        }
        else
        {
            $map = 'csit';
        }
    }

    if ($scop == constScopAll && $pcn != 'alsg'
                              && $act != 'fini')
    {
        $map = enab_action($enab);
        $act = 'alst';
    }

    if (!$scop)
    {
        $map = 'scop';
    }

    if ($scop == constScopSite && $pcn != 'gsit'
                   && $act != 'fini'
                   && $pcn != 'alsg')
    {
        $act = 'scop';
    }

    if ($scop == constScopSite && $act == 'stds' && $pcn == 'gsit')
    {
        $act = 'gsit';
    }

    if ($scop == constScopSite && $pcn == 'scop'
                   && $act == 'scop')
    {
        $act = 'site';
    }
    $hgrp = find_revl_mgrp($revl,$db);
    $sgrp = find_mgrp_cid($cid,$db);

    $env = array( );
    $env['pcn']  = $pcn;
    $env['hid']  = $hid;
    $env['mid']  = $mid;
    $env['cid']  = $cid;
    $env['tid']  = $tid;
    $env['gid']  = $gid;
    $env['now']  = $now;
    $env['act']  = $act;
    $env['priv'] = $priv;
    $env['site'] = $site;
    $env['revl'] = $revl;
    $env['auth'] = $auth;
    $env['scop'] = $scop;
    $env['enab'] = $enab;
    $env['admn']   = $admin;

    $env['hgid'] = ($hgrp)? $hgrp['mgroupid'] : 0;
    $env['sgid'] = ($sgrp)? $sgrp['mgroupid'] : 0;

    $env['min']  = $min;
    $env['hour'] = $hour;
    $env['morh'] = $morh;
    $env['xcpt'] = $xcpt;
    $env['dtl']  = $dtl;

    $env['serv'] = server_name($db);
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['priv'] = $priv;
    $env['stat'] = find_enab($env,$db);

    $env['rand'] = get_integer('rand',0);
    $env['type_radio'] = get_integer('type_radio',0);

    if (($act == 'rset') && (!$priv))
    {
        $act == 'scop';
    }

    if (matchOld($act,'|||scop|host|'))
    {
        $act = $map;
    }

    if ($act == 'enab')
    {
         $code = enab_action($env['stat']);
         if (matchold($code,'|||void|warn|'))
         {
             $act = $code;
         }
    }

    $site = '';
    $host = '';

    if ($env['site'])
    {
        $site = $env['site']['customer'];
    }
    if ($env['revl'])
    {
        $site = $env['revl']['site'];
        $host = @$env['revl']['host'] ? $env['revl']['host'] : 0;
    }

    if ((!$cnfg) && ($act == 'done'))
    {
        $act = 'deny';
    }

    /* from choose_enab() to enab_details() */
    if ($goto == 'smds')
    {
        $act = 'smds';
    }

    /* from config_site to site_details() */
    if ($goto == 'stds')
    {
        $act = 'stds';
    }

    /* from config_alst to alst_details() */
    if ($goto == 'alds')
    {
        $act = 'alds';
    }

    /* the user came from the choose_enab() detailed schedule
       page. We need to get all the variables posted */
    if ( ($pcn == 'smds' && $act == 'schd') ||
         ($pcn == 'gsit' && $act == 'gsit') ||
         ($pcn == 'aldg' && $act == 'alsg') )
    {
        $set   = array();
        $dschd = default_schedule();

        reset($dschd);
        foreach ($dschd as $key => $data)
        {
            $set[$key] = get_integer($key,0);
        }
        $env['post_vars'] = $set;
    }

    $name = CFGS_Title($act,$site,$host,$env,$db);
    $msg  = ob_get_contents();
    ob_end_clean();
    echo standard_html_header($name,$comp,$auth,'','',0,$db);

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    debug_array($debug,$_POST);

    db_change($GLOBALS['PREFIX'].'core',$db);

    echo CFGS_Again($env);

    switch ($act)
    {
        case 'csit': choose_site($env,$db);      break;
        case 'chst': choose_host($env,$db);      break;
        case 'scop': CWIZ_ChooseScop($env, "Where would you like to set "
            . "frequency scheduler?", $db);      break;
        case 'enab': choose_enab($env,$db);      break;
        case 'void': config_void($env,$db);      break;
        case 'schd': config_schd($env,$db);      break;
        case 'glsd': config_glob($env,$db);      break;
        case 'site': CFGS_ConfigSite($env,$db);      break;
        case 'gsit': config_gsit($env,$db);      break;
        case 'alst': config_alst($env,$db);      break;
        case 'alsg': config_alsg($env,$db);      break;
        case 'deny': deny($env,$db);             break;
        case 'fini': wiz_done($env,$db);         break;
        case 'smds': detail_enab($env,$db);      break;
        case 'stds': detail_site($env,$db);      break;
        case 'alds': detail_alst($env,$db);      break;
        case 'ctid': CWIZ_ChooseCats($env, "Which category would you like to "
            . "configure Scrip execution frequency?", $db); break;
        case 'cgid': CWIZ_ChooseGrps($env, "At which group would you like to "
            . "configure Scrip execution frequency?", $db); break;
        default    : whatever($env,$db);         break;
    }
    echo CFGS_Again($env);
    echo head_standard_html_footer($auth,$db);

?>
