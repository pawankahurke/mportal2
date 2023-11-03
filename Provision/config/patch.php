<?php

/*
Revision history:

Date        Who     What
----        ---     ----
14-Feb-05   BJS     copied from intruder.php, changed constants.
15-Feb-05   BSJ     removed extraneous code.
 4-May-05   EWB     update for new database.
 1-Jun-05   EWB     legacy checksum cache
22-Jul-05   EWB     load variable information
25-Jul-05   EWB     support for gconfig clients
10-Aug-05   EWB     update by machine group.
12-Oct-05   BTE     Changed references from gconfig to core.
07-Nov-05   BJS     find_site -> CWIZ_find_site.
13-Dec-05   BJS     find_site_mgrp -> GCFG_find_site_mgrp, find_host_mgrp
                    -> GCFG_find_host_mgrp()
15-Dec-05   BTE     Cleanup work to unify find_value to GCFG_FindValue, added
                    some missing includes.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
24-Apr-06   BTE     Bugs 2963 and 2974.
27-Apr-06   BTE     Bug 3292: Add group assignment reset function.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
03-May-06   BTE     Bug 3362: Do general testing and bugfixing for Scrip config
                    status page.
24-May-06   BTE     Bug 3270: Fix titles throughout the Scrip configurator
                    interface.
26-May-06   BTE     Bug 3386: Group management wizard change.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

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
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-csum.php'  );
include_once ( '../lib/l-cwiz.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-tiny.php'  );


    define('constNamMSU', 'Microsoft Update');
    define('constCodMSU', '237');
    define('constVarMSU', 'Scrip237ScripRunEnableA');

    define('constEnabNone',  0);
    define('constEnabNo',    3);
    define('constEnabYes',   4);

    function PTCH_Title($act,$site,$host,$env,$db)
    {
        $m = 'Microsoft Update Enable-Disable';
        $mach = CWIZ_GetMachineString($site,$host,$env,$db);
        switch ($act)
        {
            case 'deny': return "$m - No Access";
            case 'csit': return "$m - Select Site";
            case 'chst': return "$m - Select Machine";
            case 'enab': return "$m - Enable or Disable$mach";
            case 'scop': return $m;
            case 'done': return "$m - Finished Configuration$mach";
            default    : return $m;
        }
    }


    function PTCH_Again(&$env)
    {
        $self= $env['self'];
        $dbg = $env['priv'];
        $cmd = "$self?act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link("$cmd=scop",'config');
        if ($dbg)
        {
            $args = $env['args'];
            $href = ($args)? "$self?$args" : $self;
            $a[] = html_link('census.php','census');
            $a[] = html_link('../acct/index.php','home');
            $a[] = html_link($href,'again');
            return jumplist($a);
        }
    }

    function whatever(&$env,$db)
    {
        $msg = 'Now What';
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

    function deny(&$env,$db)
    {
        $msg = 'You are not allowed to modifiy machine configurations';
        echo para($msg);
    }

    function enab_action($enab)
    {
        switch ($enab)
        {
            case constEnabNone: return 'enab';
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


    function choose_scop(&$env,$db)
    {
        $scop = $env['scop'];

        $s1 = radio('scop',constScopAll,$scop);
        $s2 = radio('scop',constScopSite,$scop);
        $s3 = radio('scop',constScopHost,$scop);
        $s4 = radio('scop',constScopGroup,$scop);

        echo post_self('myform');
        echo hidden('act','scop');
        echo hidden('pcn','cwiz');
        echo <<< BLAH

        <p>
          Where would you like to enable or disable Microsoft Update?
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


    function choose_site(&$env,$db)
    {
        $auth = $env['auth'];
        $cid  = $env['cid'];
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes(constVarMSU);
        $cod = constCodMSU;
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
            echo para('At which site would you like to enable or disable Microsoft Update?');
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
            echo para('You don\'t own any sites');
        }
    }


    function choose_host(&$env,$db)
    {
        $set = array();
        $hid = $env['hid'];
        $cid = $env['cid'];
        if ($cid)
        {
            $qu  = safe_addslashes($env['auth']);
            $qn  = safe_addslashes(constVarMSU);
            $op  = constCodMSU;
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
                 . " and V.scop = $op\n"
                 . " and V.varuniq = M.varuniq\n"
                 . " and M.censusuniq = C.censusuniq\n"
                 . " and R.censusid = C.id\n"
                 . " order by C.host";
            $set = find_many($sql,$db);
        }

        echo post_self('myform');
        if ($set)
        {
            $in = indent(5);
            echo hidden('act','scop');
            echo hidden('pcn','scop');
            echo hidden('cid', $env['cid']);
            echo hidden('scop',$env['scop']);
            echo para('At which machine would you like to configure Microsoft Update?');
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
        GCFG_CreateVariables($env,constCodMSU,$db);

        $scop = $env['scop'];
        if ($scop == constScopAll) return constEnabNone;
        if ($scop == constScopNone) return constEnabNone;
        $i = GCFG_FindValue($env,constCodMSU,constVarMSU,$db);
        if ($i)       return constEnabYes;
        if (!$i)      return constEnabNo;
    }


    function host_done(&$env,$s,$h,$db)
    {
        $now  = $env['now'];
        $hid  = $env['hid'];
        $hgid = $env['hgid'];
        $host = $env['serv'];
        $valu = enab_value($env['enab']);
        $msus = find_var(constVarMSU,constCodMSU,$db);
        $vid  = ($msus)? $msus['varid'] : 0;

        if ((0 <= $valu) && ($vid) && ($hgid) && ($hid))
        {
            $loc = constVarConfStateLocal;
            $last = time();
            $num = update_vmap($hid,$hgid,$vid,$loc,$last,
                constSourceScripUpdateWizard,$db);
            debug_note("create local override ($num)");
        }
        else
        {
            $stat = "val:$valu,v:$vid,hgid:$hgid,hid:$hid";
            debug_note("host_done failure ($stat)");
        }
        if ((0 <= $valu) && ($hid) && ($vid) && ($hgid))
        {
            debug_note("update local value");
            set_value($vid,$hgid,$host,$valu,constSourceScripUpdateWizard,
                $now,$db);
            dirty_hid($hid,$db);
            hid_revision($hid,$now,$db);
        }
    }


    function set_revisions($set,$now,$db)
    {
        $num = 0;
        if (($set) && ($now))
        {
            $txt = join(',',$set);
            $sql = "update Revisions set\n"
                 . " stime = $now\n"
                 . " where censusid in ($txt)";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            debug_note("Revisions: $num updates");
            $sql = "update LegacyCache set\n"
                 . " drty = 1,\n"
                 . " last = $now\n"
                 . " where censusid in ($txt)";
            $res = redcommand($sql,$db);
            $xxx = affected($res,$db);
            debug_note("LegacyCache: $xxx updates");
        }
        return $num;
    }


    function over_process($env,$vid,$chg,$set,$db)
    {
        $old  = array( );
        $new  = array( );
        $chng = array( );
        $hids = array( );
        $gset = array( );
        $now  = $env['now'];
        $mgrp = $env['mgrp'];
        $serv = $env['serv'];
        $valu = enab_value($env['enab']);
        if (($mgrp) && ($set) && ($vid))
        {
            $cat = $mgrp['category'];
            $gid = $mgrp['mgroupid'];
            $tmp = find_settings($vid,$gid,$db);

            reset($set);
            foreach ($set as $key => $row)
            {
                $hid = $row['censusid'];
                $chng[$hid] = 0;
                $gset[$hid] = 0;
            }
            reset($tmp);
            foreach ($tmp as $key => $row)
            {
                $hid = $row['censusid'];
                $gset[$hid] = 1;
            }
            reset($set);
            foreach ($set as $key => $row)
            {
                $hid  = $row['censusid'];
                $site = $row['site'];
                $host = $row['host'];
                $good = cat_legal($cat,$row);
                if (!$good)
                {
                    $good = $gset[$hid];
                }
                if ($good)
                {
                    debug_note("$host at $site ($hid) is new");
                    $chng[$hid] = $chg;
                    $new[] = $hid;
                }
                else
                {
                    $hgrp = GCFG_find_host_mgrp($hid,$site,$host,$db);
                    if ($hgrp)
                    {
                        $hgid = $hgrp['mgroupid'];
                        $old[$hid] = $hgid;
                        debug_note("$host at $site (h:$hid,g:$hgid) need local override");
                        $chng[$hid] = update_value($vid,$hgid,$now,$serv,$valu,
                            constSourceScripUpdateWizard,$db);
                    }
                }
            }
        }

       /*
        |  The old machines will need to use their
        |  host group value
        */
        $last = time();

        if (($old) && ($vid))
        {
            $loc = constVarConfStateLocal;
            reset($old);
            foreach ($old as $hid => $hgid)
            {
                $vval = $chng[$hid];
                $vmap = update_vmap($hid,$hgid,$vid,$loc,$last,
                    constSourceScripUpdateWizard,$db);
                if (($vval) || ($vmap))
                {
                    $hids[] = $hid;
                }
            }
        }


       /*
        |  The new machines can use the group value.
        */

        if (($new) && ($vid) && ($mgrp))
        {
            $gbl = constVarConfStateGlobal;
            $gid = $mgrp['mgroupid'];
            reset($new);
            foreach ($new as $key => $hid)
            {
                $vval = $chng[$hid];
                $vmap = update_vmap($hid,$gid,$vid,$gbl,$last,
                    constSourceScripUpdateWizard,$db);
                if (($vval) || ($vmap))
                {
                    $hids[] = $hid;
                }
            }
        }

        set_revisions($hids,$now,$db);
    }


    function group_done(&$env,$db)
    {
        $chg  = 0;
        $set  = array( );
        $now  = $env['now'];
        $mgrp = $env['mgrp'];
        $host = $env['serv'];
        $valu = enab_value($env['enab']);
        $msus = find_var(constVarMSU,constCodMSU,$db);

        if ((0 <= $valu) && ($msus) && ($mgrp))
        {
            $vid = $msus['varid'];
            $gid = $mgrp['mgroupid'];
            $chg = update_value($vid,$gid,$now,$host,$valu,
                constSourceScripUpdateWizard,$db);
            $sql = "select R.censusid, H.host, H.site, V.* from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as H,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
                 . " ".$GLOBALS['PREFIX']."core.VarVersions as V,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as A\n"
                 . " where M.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid = $gid\n"
                 . " and V.varuniq = A.varuniq\n"
                 . " and A.varid = $vid\n"
                 . " and R.vers = V.vers\n"
                 . " and M.censusuniq = H.censusuniq\n"
                 . " and H.id = R.censusid\n"
                 . " group by R.censusid\n"
                 . " order by R.censusid";
            $set = find_many($sql,$db);
        }
        if (($set) && ($msus))
        {
            $vid = $msus['varid'];
            over_process($env,$vid,$chg,$set,$db);
        }
    }



    function wiz_done(&$env,$db)
    {
        $scop = $env['scop'];
        $self = $env['self'];
        $href = '../patch/wu-confg.php';
        $what = "no machines";
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
        if (($scop == constScopGroup) && ($env['mgrp']))
        {
            $name = $env['mgrp']['name'];
            $what = "the <b>$name</b> group of machines";
        }
        if ($scop == constScopAll)
        {
            $what = "all my machines";
        }
        $conf = html_link($self,'Configure more machines');
        $cens = html_link($href,'Go to the Software Update page');
        echo <<< DONE

        <p>
           You have configured Microsoft Update for $what.
           What would you like to do next?
        </p>

        <ul>
           <li>$conf</li>
           <li>$cens</li>
        </ul>

DONE;

    }



    function config_done(&$env,$db)
    {
        $scop = $env['scop'];
        if (($scop == constScopHost) && ($env['revl']))
        {
            $hh = $env['revl']['host'];
            $ss = $env['revl']['site'];
            host_done($env,$ss,$hh,$db);
        }
        if (($scop == constScopSite) && ($env['site']))
        {
            $site = $env['site']['customer'];
            $valu = enab_value($env['enab']);
            site_done($env,$site,constCodMSU,constVarMSU,$valu,
                constSourceScripUpdateWizard,$db);
        }
        if (($scop == constScopGroup) && ($env['mgrp']))
        {
            group_done($env,$db);
        }
        if ($scop == constScopAll)
        {
            $valu = enab_value($env['enab']);
            user_done($env,constCodMSU,constVarMSU,$valu,
                constSourceScripUpdateWizard,$env['now'],$db);
        }
        wiz_done($env,$db);
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

    $now  = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();

    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?  1 : 0;
    $debug = @ ($user['priv_debug'])?  1 : 0;
    $admin = @ ($user['priv_admin'])?  1 : 0;
    $cnfg  = @ ($user['priv_config'])? 1 : 0;

    $hid = get_integer('hid',0);
    $cid = get_integer('cid',0);
    $tid = get_integer('tid',0);
    $gid = get_integer('gid',0);
    $act = get_string('act','scop');
    $hlp = get_string('hlp','');
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
        $hid  = 0;
        $tid  = 0;
        $gid  = 0;
    }

    if ($post == constButtonHlp)
    {
        $act  = $hlp;
    }

    $revl = full_revl($hid,$auth,$db);
    if (($revl) && (!$cid))
    {
        $cid = $revl['cid'];
    }
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

    if (!$scop)
    {
        $map = 'scop';
    }

    $hgrp = find_revl_mgrp($revl,$db);
    $sgrp = find_mgrp_cid($cid,$db);

    $env = array( );
    $env['hid']  = $hid;
    $env['cid']  = $cid;
    $env['tid']  = $tid;
    $env['gid']  = $gid;
    $env['now']  = $now;
    $env['act']  = $act;
    $env['mgrp'] = $mgrp;
    $env['priv'] = $priv;
    $env['site'] = $site;
    $env['revl'] = $revl;
    $env['hgrp'] = $hgrp;
    $env['sgrp'] = $sgrp;
    $env['hgid'] = ($hgrp)? $hgrp['mgroupid'] : 0;
    $env['sgid'] = ($sgrp)? $sgrp['mgroupid'] : 0;
    $env['auth'] = $auth;
    $env['scop'] = $scop;
    $env['enab'] = $enab;
    $env['admn'] = $admin;
    $env['serv'] = server_name($db);
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['priv'] = $priv;
    $env['stat'] = find_enab($env,$db);

    if (matchOld($act,'|||scop|host|'))
    {
        $act = $map;
    }

    if ($act == 'enab')
    {
         $code = enab_action($env['stat']);
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
        $host = $env['revl']['host'];
    }

    if ((!$cnfg) && ($act == 'done'))
    {
        $act = 'deny';
    }

    $name = PTCH_Title($act,$site,$host,$env,$db);
    $msg  = ob_get_contents();
    ob_end_clean();
    echo standard_html_header($name,$comp,$auth,'','',0,$db);

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    debug_array($debug,$_POST);

    db_change($GLOBALS['PREFIX'].'core',$db);
    echo PTCH_Again($env);
    switch ($act)
    {
        case 'ctid': CWIZ_ChooseCats($env, "Which category would you like to "
            . "enable or disable Microsoft Update?", $db); break;
        case 'cgid': CWIZ_ChooseGrps($env, "At which group would you like to "
            . "enable or disable Microsoft Update?", $db); break;
        case 'csit': choose_site($env,$db); break;
        case 'chst': choose_host($env,$db); break;
        case 'scop': choose_scop($env,$db); break;
        case 'enab': CWIZ_ChooseEnab($env, "Enable or Disable Microsoft "
            . "Update?", $db); break;
        case 'done': config_done($env,$db); break;
        case 'deny': deny($env,$db);        break;
        default    : whatever($env,$db);    break;
    }
    echo PTCH_Again($env);
    echo head_standard_html_footer($auth,$db);
?>
