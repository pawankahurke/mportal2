<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 1-Feb-05   EWB     Created.
 3-Feb-05   EWB     Alex title changes.
 8-Feb-05   EWB     More Alex title changes.
 2-May-05   EWB     Works in new database
 1-Jun-05   EWB     Legacy Checksum Cache
12-Sep-05   BTE     Added checksum invalidation code.
12-Oct-05   BTE     Changed references from gconfig to core.
24-Oct-05   BTE     Update revl in ValueMap when changing mgroupid in
                    site_done.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
07-Dec-05   BJS     find_site -> CWIZ_find_site.
13-Dec-05   BJS     find_site_mgrp -> GCFG_find_site_mgrp()
15-Dec-05   BTE     Cleanup work to unify find_value to GCFG_FindValue.
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
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-csum.php'  );
include_once ( '../lib/l-cwiz.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-tiny.php'  );


    define('constNamSSEC', 'System Start-up Environment Control');
    define('constCodSSEC', '27');
    define('constVarSSEC', 'scrip27Enabled');

    define('constNamIPRC', 'Intrusion Protection Control');
    define('constCodIPRC', '232');
    define('constVarIPRC', 'scripEnabled');

    define('constEnabNone',  0);
    define('constEnabVoid',  1);
    define('constEnabWarn',  2);
    define('constEnabNo',    3);
    define('constEnabYes',   4);

    function IPMI_Title($act,$site,$host,$env,$db)
    {
        $m = 'Malware Protection Enable-Disable';
        $mach = CWIZ_GetMachineString($site,$host,$env,$db);
        switch ($act)
        {
            case 'deny': return "$m - No Access";
            case 'csit': return "$m - Select Site";
            case 'chst': return "$m - Select Machine";
            case 'kill': ;
            case 'enab': return "$m - Enable or Disable$mach";
            case 'scop': return $m;
            case 'done': return "$m - Finished Configuration$mach";
            case 'rset': return "$m - Restore Defaults";
            case 'warn': return "$m - Advanced Interface$mach";
            default    : return $m;
        }
    }


    function IPMI_Again(&$env)
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

    function config_deny(&$env,$db)
    {
        $msg = 'You are not allowed to modify machine configurations.';
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


    function help_href($code)
    {
        return sprintf('help/s%05d.htm',$code);
    }

    function next_cancel_help($code)
    {
        $nxt = button(constButtonNxt);
        $can = button(constButtonCan);
        $hlp = button(constButtonHlp);
        $old = '>';
        $ref = help_href($code);
        $new = " onclick=\"window.open('$ref','helpwin');\">";
        $hlp = str_replace($old,$new,$hlp);
        return para("$nxt $can $hlp");
    }


    function enab_action($enab)
    {
        switch ($enab)
        {
            case constEnabNone: return 'enab';
            case constEnabVoid: return 'void';
            case constEnabWarn: return 'warn';
            case constEnabYes : return 'done';
            case constEnabNo  : return 'done';
            default           : return 'warn';
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


    function choose_site(&$env,$db)
    {
        $auth = $env['auth'];
        $cid  = $env['cid'];
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes(constVarIPRC);
        $cod = constCodIPRC;
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
            echo para('At which site would you like to enable or disable malware protection?');
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


    function choose_host(&$env,$db)
    {
        $set = array();
        $hid = $env['hid'];
        $cid = $env['cid'];
        if ($cid)
        {
            $qu  = safe_addslashes($env['auth']);
            $qn  = safe_addslashes(constVarIPRC);
            $cod = constCodIPRC;
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
            echo para('At which machine would you like to configure intrusion protection?');
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
        GCFG_CreateVariables($env,constCodIPRC,$db);
        GCFG_CreateVariables($env,constCodSSEC,$db);

        $scop = $env['scop'];

        if ($scop == constScopAll) return constEnabNone;
        if ($scop == constScopNone) return constEnabNone;
        $i = CWIZ_FindValue($env,constCodIPRC,constVarIPRC,$db);
        $s = CWIZ_FindValue($env,constCodSSEC,constVarSSEC,$db);
        if ($s == '') return constEnabVoid;
        if ($i == '') return constEnabVoid;
        if (($s) && ($i)) return constEnabYes;
        if ((!$s) && (!$i)) return constEnabNo;
        return constEnabWarn;
    }


    function INTR_SiteDone($env,$ss,$db)
    {
        $set  = array();
        $sgrp = GCFG_find_site_mgrp($ss,$db);
        $valu = enab_value($env['enab']);
        $ssec = find_var(constVarSSEC,constCodSSEC,$db);
        $iprc = find_var(constVarIPRC,constCodIPRC,$db);
        $sgid = ($sgrp)? $sgrp['mgroupid'] : 0;
        $vid1 = ($ssec)? $ssec['varid'] : 0;
        $vid2 = ($iprc)? $iprc['varid'] : 0;
        if ((0 <= $valu) && ($vid1) && ($vid2) && ($sgid))
        {
            debug_note("update values for site group <b>$ss</b>");
            $host = $env['serv'];
            set_value($vid1,$sgid,$host,$valu,constSourceScripMalwareWizard,
                $env['now'],$db);
            set_value($vid2,$sgid,$host,$valu,constSourceScripMalwareWizard,
                $env['now'],$db);
            $now = $env['now'];
            dirty_site($ss,$db);
            site_revision($ss,$now,$db);
            $qs  = safe_addslashes($ss);
            $sql = "select C.id, C.host from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.ValueMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V\n"
                 . " where C.site = '$qs'\n"
                 . " and M.censusuniq = C.censusuniq\n"
                 . " and M.varuniq = V.varuniq\n"
                 . " and V.varid in ($vid1,$vid2)\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid != $sgid\n"
                 . " group by C.id";
            $set = find_many($sql,$db);
        }

        if (($set) && ($sgid) && ($vid1) && ($vid2))
        {
            debug_note("remove local overrides");
            $last = time();
            reset($set);
            foreach ($set as $key => $row)
            {
                $hid = $row['id'];
                $gbl = constVarConfStateGlobal;
                $lon = constVarConfStateLocalOnly;

                update_vmap($hid, $sgid, $vid1, $gbl, $last,
                    constSourceScripMalwareWizard, $db);
                update_vmap($hid, $sgid, $vid2, $gbl, $last,
                    constSourceScripMalwareWizard, $db);
            }
        }
    }


    function host_done(&$env,$s,$h,$db)
    {
        $hid  = $env['hid'];
        $hgid = $env['hgid'];
        $valu = enab_value($env['enab']);
        $ssec = find_var(constVarSSEC,constCodSSEC,$db);
        $iprc = find_var(constVarIPRC,constCodIPRC,$db);
        $vid1 = ($ssec)? $ssec['varid'] : 0;
        $vid2 = ($iprc)? $iprc['varid'] : 0;
        if (($vid1) && ($vid2) && ($hgid) && ($hid))
        {
            debug_note("create local override");
            $loc = constVarConfStateLocal;
            $last = time();

            update_vmap($hid, $hgid, $vid1, $loc, $last,
                constSourceScripMalwareWizard, $db);
            update_vmap($hid, $hgid, $vid2, $loc, $last,
                constSourceScripMalwareWizard, $db);
        }

        if ((0 <= $valu) && ($vid1) && ($vid2))
        {
            debug_note("update local value");
            $now  = $env['now'];
            $host = $env['serv'];
            set_value($vid1,$hgid,$host,$valu,constSourceScripMalwareWizard,
                $now,$db);
            set_value($vid2,$hgid,$host,$valu,constSourceScripMalwareWizard,
                $now,$db);
            dirty_hid($hid,$db);
            hid_revision($hid,$now,$db);
        }
    }


    function wiz_done(&$env,$db)
    {
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
            $what = "all my machines";
        }
        if ($scop == constScopGroup)
        {
            $what = "a specific group";
        }
        $href = '../acct/census.php';
        $conf = html_link($self,'Configure more machines');
        $cens = html_link($href,'Go to the Census page');
        echo <<< DONE

        <p>
           You have configured malware protection for $what.
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
            INTR_SiteDone($env,$site,$db);
        }
        if ($scop == constScopAll)
        {
            $valu = enab_value($env['enab']);
            user_done($env,constCodIPRC,constVarIPRC,$valu,
                constSourceScripMalwareWizard,$env['now'],$db);
            user_done($env,constCodSSEC,constVarSSEC,$valu,
                constSourceScripMalwareWizard,$env['now'],$db);
        }
        if ($scop == constScopGroup)
        {
            $now = time();
            $valu = enab_value($env['enab']);
            $mgrp = find_mgrp_info($env['gid'], $db);
            push_mgrp($mgrp,1,$now,'',constCodSSEC,$valu,constVarSSEC,
                constSourceScripMalwareWizard,$db);
            push_mgrp($mgrp,1,$now,'',constCodIPRC,$valu,constVarIPRC,
                constSourceScripMalwareWizard,$db);
        }
        wiz_done($env,$db);
    }


    function dead_code(&$env,$name,$code,$db)
    {
        $self = $env['self'];
        $href = '../acct/census.php';
        $conf = html_link($self,'Configure more machines');
        $cens = html_link($href,'Go to the Census page');
        echo <<< DEAD

        <p>
           These clients do not support <b>$name</b>,
           i.e. they do not run scrip <b>$code</b>.
        </p>

        <p>
           What would you like to do next?
        </p>

        <ul>
           <li>$conf</li>
           <li>$cens</li>
        </ul>

DEAD;

    }


    function config_void(&$env,$db)
    {
        debug_note('config_void');
    }

    function config_warn(&$env,$db)
    {

        echo <<< WARN

        <p>
          The advanced interface (Scrip configuration) pages
          have been used to configure malware protection
          settings for these machines, and those settings
          are incompatible with the operation of this wizard.
        </p>

WARN;
        CWIZ_ChooseEnab($env, "Enable or Disable Malware Protection?", $db);
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
    $cid = get_integer('cid',0);
    $act = get_string('act','scop');
    $hlp = get_string('hlp','');
    $tid = get_integer('tid',0);
    $gid = get_integer('gid',0);
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
        $tid = 0;
        $gid  = 0;
    }

    if ($post == constButtonHlp)
    {
        $act  = $hlp;
    }

    $mgrp = find_mgrp_info($gid,$db);
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

    $name = IPMI_Title($act,$site,$host,$env,$db);
    $msg  = ob_get_contents();
    ob_end_clean();
    echo standard_html_header($name,$comp,$auth,'','',0,$db);

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    debug_array($debug,$_POST);

    db_change($GLOBALS['PREFIX'].'core',$db);
    echo IPMI_Again($env);

    switch ($act)
    {
        case 'csit': choose_site($env,$db);      break;
        case 'chst': choose_host($env,$db);      break;
        case 'scop':
            CWIZ_ChooseScop($env, "Where would you like to enable or disable "
                . "malware protection?", $db);
            break;
        case 'enab': CWIZ_ChooseEnab($env, "Enable or Disable Malware "
            . "Protection?", $db);      break;
        case 'warn': config_warn($env,$db);      break;
        case 'void': config_void($env,$db);      break;
        case 'done': config_done($env,$db);      break;
        case 'deny': deny($env,$db);             break;
        case 'ctid': CWIZ_ChooseCats($env, "Which category would you like to "
            . "enable or disable malware protection?", $db);  break;
        case 'cgid': CWIZ_ChooseGrps($env, "At which group would you like to "
            . "enable or disable malware protection?", $db);  break;
        default    : whatever($env,$db);         break;
    }
    echo IPMI_Again($env);
    echo head_standard_html_footer($auth,$db);
?>
