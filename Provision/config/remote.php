<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Jan-05   EWB     Created.
12-Jan-05   EWB     Error checking.
13-Jan-05   EWB     Updates the checksum cache
14-Jan-05   EWB     Reset database command
17-Jan-05   EWB     Setting globals removes local overrides.
17-Jan-05   EWB     Honors priv_config setting.
25-Jan-05   EWB     Fixed bug in "localize".
27-Jan-05   EWB     Configuring one scrip clears the other.
28-Jan-05   EWB     Fixed a scoping problem.
 2-Feb-05   AAM     Added listener port to summary table, added more text
                    per Alex.
 2-Feb-05   EWB     Handle case of connect to machine sans config records.
 2-Feb-05   EWB     Configure in "connect" mode ends at connect page.
 2-Feb-05   EWB     Cancel in "connect" mode does NOT cancel connect mode.
 2-Feb-05   EWB     More Alex text changes.
 4-Feb-05   EWB     Cancel in connect mode returns to census page.
11-Feb-05   EWB     Factored common code into library.
16-Feb-05   EWB     Option to take control of just-configured machine.
 2-Mar-05   EWB     Help page link in GoToAssist form.
14-Mar-05   EWB     Assorted prompt changes (for Alex)
 8-Apr-05   EWB     Even further Alex changes.
 5-May-05   EWB     Uses new database.
19-May-05   AAM     Fixed a typo in form_goto that had swapped gcmp and gnam.
16-Jun-05   BJS     Added 'change configuration' link in conf_goto/uvnc.
29-Jun-05   BJS     Added client_version_content().
30-Jun-05   BJS     Create GotoAssit content based on client version >= 2.001.0929.
                    removed make_int use strnatcasecmp()
 5-Jul-05   BJS     Added quoteslash(), ctyp to wiz_done() $href.
11-Aug-05   EWB     update by machine group.
12-Sep-05   BTE     Added checksum invalidation code.
26-Sep-05   BJS     Text changes for Alex.
12-Oct-05   BTE     Changed references from gconfig to core.
24-Oct-05   BTE     Moved sub_local to GCFG_SubLocal in l-gcfg.php.
03-Nov-05   BTE     Changed VarValues.* statements to explicit columns.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
05-Dec-05   BJS     Text fixes per Alex.
07-Dec-05   BJS     find_site -> CWIZ_find_site.
13-Dec-05   BJS     find_site_mgrp -> GCFG_find_site_mgrp, find_host_mgrp ->
                    GCFG_find_host_mgrp()
15-Dec-05   BTE     Cleanup work from find_value to GCFG_FindValue.
06-Jan-06   BJS     Added drop_temp_table().
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
05-Apr-06   AAM     Added link to UltraVNC connection ID page (bug 3237).
07-Apr-06   AAM     Put "Remote Control - " at the start of each page title.
24-Apr-06   BTE     Bugs 2963 and 2974.
27-Apr-06   BTE     Bug 3292: Add group assignment reset function.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
03-May-06   BTE     Bug 3362: Do general testing and bugfixing for Scrip config
                    status page.
24-May-06   BTE     Bug 3270: Fix titles throughout the Scrip configurator
                    interface.
26-May-06   BTE     Bug 3386: Group management wizard change.
09-Jun-06   JRN     Bug 3192: Adding repeater code from the 4.2 client.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
24-Jun-06   BTE     Moved mgrp_revision to l-gcfg.php.
04-Jul-06   BTE     Bug 3506: Sites: Wizard - Remote control configuration bug.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.
16-Nov-06   BTE     Bug 3858: Remote control wizard does not work with 2.4
                    client and 4.3 server.

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
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-cwiz.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-rest.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( 'local.php'          );

    define('constNamGoto',    'Citrix&reg; GoToAssist&trade;');
    define('constCodGoto',    '245');
    define('constVarGoto',    'scrip245HelpDeskID');
    define('constSemGoto',    'scrip245RunNow');
    define('constVarSSUGoto', 'scrip245StartSessionURL');

    define('constNamUVNC', 'Ultr@VNC');
    define('constCodUVNC', '236');
    define('constVarUVNC', 'Scrip236Host');
    define('constSemUVNC', 'Scrip236InstallRunNowA');
    define('constPrtUVNC', 'Scrip236Port');
    define('constDefaultVNCURL',
           'http://www.handsfreenetworks.com/download/UltraVNC-Setup.exe');

    define('constClientVersion', '2.001.0929.BM');

    define('constNoUVNC',     '[Host]');

    define('constMethVoid',  0);
    define('constMethNone',  1);
    define('constMethGoto',  2);
    define('constMethUVNC',  3);
    define('constMethBoth',  4);

    function REMT_Title($act,$site,$host,$env,$db)
    {
        $u = constNamUVNC;
        $g = constNamGoto;

        $mach = CWIZ_GetMachineString($site,$host,$env,$db);

        switch ($act)
        {
            case 'deny': return 'Remote Control - No Access';
            case 'csit': return 'Remote Control - Select Site';
            case 'chst': return 'Remote Control - Select Machine';
            case 'kill': ;
            case 'meth': return "Remote Control - Select Connection Method"
                . "$mach";
            case 'goto': return "Remote Control - $g Setup$mach";
            case 'uvnc': return "Remote Control - $u Setup$mach";
            case 'scop': return 'Remote Control - Configuration';
            case 'uact': return "Remote Control - Invalid $u$mach";
            case 'gact': return "Remote Control - Invalid $g$mach";
            case 'gdon': return "Remote Control - Finished Configuration$mach";
            case 'udon': return "Remote Control - Finished Configuration$mach";
            case 'ucnf': return "Remote Control - $u Connection Confirmation"
                . "$mach";
            case 'gcnf': return "Remote Control - $g Connection Confirmation"
                . "$mach";
            case 'ucon': return "Remote Control - $u Connecting ...";
            case 'gcon': return "Remote Control - $g Connecting ...";
            case 'rset': return "Remote Control - Restore Defaults";
            case 'warn': return "Remote Control - Advanced Interface$mach";
            case 'none': return "Remote Control - Cannot connect to machine '$host' at site '$site'";
            default    : return 'Remote Control - Wizard';
        }
    }


    function REMT_Again(&$env)
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


    function goto_names()
    {
        return array
        (
            'scrip245HelpDeskID',
            'scrip245HelpDeskAdmin',
            'scrip245Company',
            'scrip245Name',
            'scrip245CloseBrowser',
            'scrip245StartSessionURL'
        );
    }


    function find_host_name($site,$host,$db)
    {
        $mid = 0;
        if (($site) && ($host))
        {
            $qs  = safe_addslashes($site);
            $qh  = safe_addslashes($host);
            $sql = "select id from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census\n"
                 . " where site = '$qs'\n"
                 . " and host = '$qh'";
            $row = find_one($sql,$db);
            $mid = ($row)? $row['id'] : 0;
        }
        return $mid;
    }


    function whatever(&$env,$db)
    {
        $msg = "Now What";
        echo para($msg);
    }

    function deny(&$env,$db)
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

    function next_return()
    {
        $href = '../acct/census.php';
        $back = "window.open('$href','_self');";
        $nxt  = button(constButtonNxt);
        $can  = click(constButtonCan,$back);
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
        $ref = help_href($code);
        $act = "window.open('$ref','helpwin');";
        $hlp = click(constButtonHlp,$act);
        return para("$nxt $can $hlp");
    }


    function meth_action($meth)
    {
        if ($meth)
        {
            $act = ($meth == constMethGoto)? 'goto' : 'uvnc';
        }
        else
        {
            $act = 'meth';
        }
        return $act;
    }


    function find_all_sites($auth,$db)
    {
        $qu  = safe_addslashes($auth);
        $qn  = safe_addslashes(constVarUVNC);
        $op  = constCodUVNC;
        $sql = "select U.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
             . " ".$GLOBALS['PREFIX']."core.VarValues as X\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = G.name\n"
             . " and V.name = '$qn'\n"
             . " and V.scop = $op\n"
             . " and V.varuniq = X.varuniq\n"
             . " and X.mgroupuniq = G.mgroupuniq\n"
             . " group by customer\n"
             . " order by CONVERT(customer USING latin1)";
        return find_many($sql,$db);
    }


    function choose_site(&$env,$db)
    {
        $auth = $env['auth'];
        $cid  = $env['cid'];
        $rcon = $env['rcon'];
        $set  = find_all_sites($auth,$db);
        if ($set)
        {
            $in = indent(5);
            echo post_self('myform');
            echo hidden('act','scop');
            echo hidden('pcn','scop');
            echo hidden('tid',0);
            echo hidden('gid',0);
            echo hidden('rcon',$env['rcon']);
            echo hidden('scop',$env['scop']);

            /* Provide a link to the UltraVNC connection listing, since this
                page is the entry point into the remote control wizard. */
            echo para(html_link('listuvnc.php', 'Click here') .
                ' if UltraVNC is already running and you just want a list' .
                ' of connection IDs for the machines.  Otherwise ...');

            /* rcon is only here for the case that a person with
               restricted access is trying to make a remote connection. */
            if ($rcon == 1)
            {
                echo para('Where would you like to take remote control of?');
            }
            else
            {
                echo para('At which site would you like to configure remote control access?');
            }

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
        $rcon = $env['rcon'];
        if ($cid)
        {
            $qu  = safe_addslashes($env['auth']);
            $op  = constCodUVNC;
            $qn  = safe_addslashes(constVarUVNC);
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

            /* This will keep track if the user is configuring a machine or running the
                remote connection on a machine. */
            if ($rcon == 1)
            {
                echo para('At which machine would you like take remote control access of?');
                echo hidden('act','host');
            }
            else if ($rcon == 0)
            {
                echo para('At which machine would you like to configure remote control access?');
                echo hidden('act','scop');
            }
            echo hidden('pcn','scop');
            echo hidden('rcon',$env['rcon']);
            echo hidden('tid', 0);
            echo hidden('gid', 0);
            echo hidden('cid', $env['cid']);
            echo hidden('scop',$env['scop']);
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
            echo hidden('mid', $env['mid']);
            echo hidden('scop',0);
            echo para('There aren\'t any appropriate machines at this site.');
        }
        echo next_cancel();
        echo form_footer();
    }



    function choose_meth(&$env,$db)
    {
        $xx = find_meth($env,$db);
        $m1 = radio('meth',constMethGoto,$xx);
        $m2 = radio('meth',constMethUVNC,$xx);
        $p1 = constNamGoto;
        $p2 = constNamUVNC;
        $in = indent(5);
        echo post_self('myform');
        echo hidden('act','scop');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('tid', $env['tid']);
        echo hidden('gid', $env['gid']);
        echo hidden('scop',$env['scop']);
        echo <<< METH

        <p>How would you like to connect?</p>

        ${in}$m1 $p1<br>
        ${in}$m2 $p2<br>

METH;
        $acts = ($env['conn'])? next_return() : next_cancel();
        echo $acts;
        echo form_footer();
    }


    function find_meth(&$env,$db)
    {
        $scop = $env['scop'];
        $meth = $env['meth'];

        if (!$meth)
        {
            $g = ctyp_find_value($env,$db);

            $u = CWIZ_FindValue($env,constCodUVNC,constVarUVNC,$db);

            if ($u == constNoUVNC) $u = '';

            if ((!$g) && (!$u)) $meth = constMethNone;
            if (( $g) && (!$u)) $meth = constMethGoto;
            if ((!$g) && ( $u)) $meth = constMethUVNC;
            if (( $g) && ( $u)) $meth = constMethBoth;
        }
        return $meth;
    }


    function mgrp_vars(&$env,$gid,$code,$db)
    {
        $set = array();
        $grp = array();
        if (($gid) && ($env['priv']))
        {
            $grp = find_mgrp_info($gid,$db);
            $sql = "select V.name, V.itype, X.valueid, V.varid, G.mgroupid,"
                 . " X.valu, X.revl, X.def, X.revldef, X.clientconf,"
                 . " X.revlclientconf, X.last, X.host from\n"
                 . " VarValues as X,\n"
                 . " Variables as V,\n"
                 . " MachineGroups as G\n"
                 . " where V.scop = $code\n"
                 . " and V.varuniq = X.varuniq\n"
                 . " and X.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid = $gid\n"
                 . " order by V.name";
            $set = find_many($sql,$db);
        }
        if (($set) && ($grp))
        {
            $name = $grp['name'];
            $args = explode('|','name|type|last|host|xid|vid|revl|valu');
            $rows = safe_count($set);
            $cols = safe_count($args);
            $text = "$name($gid) &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($args,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $gid  = $row['mgroupid'];
                $xid  = $row['valueid'];
                $vid  = $row['varid'];
                $last = $row['last'];
                $revl = $row['revl'];
                $type = type_name($row['itype']);
                $host = disp($row,'host');
                $name = disp($row,'name');
                $valu = disp($row,'valu');
                $when = date('m/d/y H:i:s',$last);
                $args = array($name,$type,$when,$host,$xid,$vid,$revl,$valu);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }



    function site_uvnc($env,$ss,$db)
    {
        $now  = $env['now'];
        $uadr = $env['uadr'];  // Scrip236Host
        $udld = $env['udld'];  // Scrip236InstallPath
        $uprt = $env['uprt'];  // Scrip236Port
        $urpt = $env['urpt'];  // useRepeater
        $urad = $env['urad'];  // Scrip236Repeater
        $udsk = $env['udsk'];  // CreateDesktopIcon
        $ucid = $env['ucid'];  // Scrip236ServerID

        $sgrp = GCFG_find_site_mgrp($ss,$db);
        $sgid = ($sgrp)? $sgrp['mgroupid'] : 0;

        $hh = $env['serv'];
        $oo = constCodUVNC;
        $xx = constCodGoto;
        $vv = constVarGoto;

        $row  = site_value($ss,$xx,$vv,$db);
        $valu = ($row)? $row['valu'] : '';
        if ($valu)
        {
            sub_global($sgid,$hh,$xx,'','scrip245StartSessionURL',$now,$db);
            sub_global($sgid,$hh,$xx,'','scrip245HelpDeskAdmin',$now,$db);
            sub_global($sgid,$hh,$xx, 1,'scrip245CloseBrowser',$now,$db);
            sub_global($sgid,$hh,$xx,'','scrip245HelpDeskID',$now,$db);
            sub_global($sgid,$hh,$xx,'','scrip245Company',$now,$db);
            sub_global($sgid,$hh,$xx,'','scrip245Name',$now,$db);
            clear_site_over($ss,$xx,$now,constSourceScripRemoteWizard,$db);
        }
        if ($uadr == '' && $urpt)
        {
            $uadr = '[host]';
        }
        sub_global($sgid,$hh,$oo,$uadr,'Scrip236Host',$now,$db);
        sub_global($sgid,$hh,$oo,$udld,'Scrip236InstallPath',$now,$db);
        if (($uprt == '') || (is_numeric($uprt)))
        {
            sub_global($sgid,$hh,$oo,$uprt,'Scrip236Port',$now,$db);
        }
        sub_global($sgid,$hh,$oo,$urpt,'useRepeater',$now,$db);
        sub_global($sgid,$hh,$oo,$urad,'Scrip236Repeater',$now,$db);

        if ($udsk)
        {
            sub_global($sgid,$hh,$oo,0,'Scrip236AutoCon',$now,$db);
        }
        else
        {
            sub_global($sgid,$hh,$oo,1,'Scrip236AutoCon',$now,$db);
        }
        sub_global($sgid,$hh,$oo,$udsk,'CreateDesktopIcon',$now,$db);

        if (($ucid == '') || (is_numeric($ucid)))
        {
            sub_global($sgid,$hh,$oo,$ucid,'Scrip236ServerID',$now,$db);
        }


        dirty_site($ss,$db);
        site_revision($ss,$now,$db);
        clear_site_over($ss,$oo,$now,constSourceScripRemoteWizard,$db);
        mgrp_vars($env,$sgid,$oo,$db);
    }


    function site_goto(&$env,$ss,$db)
    {
        $now  = $env['now'];
        $ghdi = $env['ghdi'];  // scrip245HelpDeskID
        $ghda = $env['ghda'];  // scrip245HelpDeskAdmin
        $gcmp = $env['gcmp'];  // scrip245Company
        $gnam = $env['gnam'];  // scrip245Name
        $gcls = $env['gcls'];  // scrip245CloseBrowser
        $gssu = $env['gssu'];  // script245StartSessionURL
        $hh   = $env['serv'];
        $sgrp = GCFG_find_site_mgrp($ss,$db);
        $sgid = ($sgrp)? $sgrp['mgroupid'] : 0;

        $oo   = constCodGoto;
        $xx   = constCodUVNC;
        $vv   = constVarUVNC;
        $row  = site_value($ss,$xx,$vv,$db);
        $valu = ($row)? $row[0]['valu'] : '';
        if ($valu)
        {
            sub_global($sgid,$hh,$xx,'','Scrip236Host',$now,$db);
            sub_global($sgid,$hh,$xx,'','Scrip236Port',$now,$db);
            sub_global($sgid,$hh,$xx,'','Scrip236InstallPath',$now,$db);
            sub_global($sgid,$hh,$xx,'','useRepeater',$now,$db);
            sub_global($sgid,$hh,$xx,'','Scrip236Repeater',$now,$db);
            sub_global($sgid,$hh,$xx,'','CreateDesktopIcon',$now,$db);
            sub_global($sgid,$hh,$xx,'','Scrip236ServerID',$now,$db);
            clear_site_over($ss,$xx,$now,$db,constSourceScripRemoteWizard);
        }
        sub_global($sgid,$hh,$oo,$ghdi,'scrip245HelpDeskID',$now,$db);
        sub_global($sgid,$hh,$oo,$ghda,'scrip245HelpDeskAdmin',$now,$db);
        sub_global($sgid,$hh,$oo,$gcmp,'scrip245Company',$now,$db);
        sub_global($sgid,$hh,$oo,$gnam,'scrip245Name',$now,$db);
        sub_global($sgid,$hh,$oo,$gcls,'scrip245CloseBrowser',$now,$db);
        sub_global($sgid,$hh,$oo,$gssu,'scrip245StartSessionURL',$now,$db);
        dirty_site($ss,$db);
        site_revision($ss,$now,$db);
        clear_site_over($ss,$oo,$now,constSourceScripRemoteWizard,$db);
        mgrp_vars($env,$sgid,$oo,$db);
    }


    function many_sites_goto(&$env,$db)
    {
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes(constVarGoto);
        $op  = constCodGoto;
        $sql = "select U.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.VarValues as X,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = G.name\n"
             . " and V.name = '$qn'\n"
             . " and V.scop = $op\n"
             . " and V.varuniq = X.varuniq\n"
             . " and X.mgroupuniq = G.mgroupuniq\n"
             . " group by customer\n"
             . " order by customer";
        $set = find_many($sql,$db);
        if ($set)
        {
            $name = constNamGoto;
            foreach ($set as $key => $row)
            {
                $site = $row['customer'];
                echo para("Configure <b>$site</b> for <b>$name</b>");
                site_goto($env,$site,$db);
            }
        }
    }


    function user_goto(&$env,$db)
    {
        $vars = array();
        $ugrp = all_machines($env,$db);
        if ($ugrp)
        {
            $scop = constCodGoto;
            $ugid = $ugrp['mgroupid'];
            $vars = mgrp_scop_valu($ugid,$scop,$db);
        }
        if (($ugrp) && ($vars))
        {
            mgrp_goto($env,$ugrp,1,$db);
        }
        else
        {
            many_sites_goto($env,$db);
        }
    }


    function many_sites_uvnc(&$env,$db)
    {
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes(constVarUVNC);
        $op  = constCodUVNC;
        $sql = "select U.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.VarValues as X,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = G.name\n"
             . " and V.name = '$qn'\n"
             . " and V.scop = $op\n"
             . " and V.varuniq = X.varuniq\n"
             . " and X.mgroupuniq = G.mgroupuniq\n"
             . " group by customer\n"
             . " order by customer";
        $set = find_many($sql,$db);
        if ($set)
        {
            $name = constNamUVNC;
            foreach ($set as $key => $row)
            {
                $site = $row['customer'];
                echo para("Configure <b>$site</b> for <b>$name</b>");
                site_uvnc($env,$site,$db);
            }
        }
    }


    function user_uvnc(&$env,$db)
    {
        $vars = array();
        $ugrp = all_machines($env,$db);
        if ($ugrp)
        {
            $scop = constCodUVNC;
            $ugid = $ugrp['mgroupid'];
            $vars = mgrp_scop_valu($ugid,$scop,$db);
        }
        if (($ugrp) && ($vars))
        {
            mgrp_uvnc($env,$ugrp,1,$db);
        }
        else
        {
            many_sites_uvnc($env,$db);
        }
    }


    /* creates the content based on the type of clients
       we have, mix, new or old.
    */
    function fetch_client_content($ctyp,$ghdi,$gssu)
    {
        if ($ctyp >= 2)
        {
            $cvc = '<tr>'
                 . '<td>Session Start URL</td>'
                 . "<td>$gssu</td>"
                 . '</tr>';

            $cvc = '<tr>'
                 . '<td>Partner ID</td>'
                 . "<td>$ghdi</td>"
                 . '</tr>';
        }
        if ($ctyp == 1)
        {
            $cvc = '<tr>'
                 . '<td>Session Start URL</td>'
                 . "<td>$gssu</td>"
                 . '</tr>';
        }
        if ($ctyp <= 0)
        {
            $cvc = '<tr>'
                 . '<td>Parent ID</td>'
                 . "<td>$ghdi</td>"
                 . '</tr>';
        }
        return $cvc;
    }


    function quoteslash($str)
    {
        return "'" . safe_addslashes($str) . "'";
    }


    /* creates a string of all sites user has access to */
    function find_all_vers($site,$db)
    {
        $set = array();
        reset($site);
        foreach ($site as $key => $data)
        {
            $set[] = quoteslash($data['customer']);
        }
        return join(",",$set);
    }


    /* determines if the clients in a site are of type
       old : < constClientVersion    returns 0
       new : >= constClientVersion   returns 1
       mix : both                    returns 2
    */
    function find_site_type($set)
    {
        $old = false;
        $new = false;

        reset($set);
        foreach ($set as $key => $data)
        {
            $client_version = $data['vers'];
            $type = strnatcasecmp($client_version,constClientVersion);
            switch($type)
            {
                case -1 : $old = true; break;
                case  0 : $new = true; break;
                case  1 : $new = true; break;
            }
        }

        if ($old == $new)
            return 2;

        if ( (!$old) && (!$new) )
            return 2;

        if ( (!$old) && ($new) )
            return 1;

        if ( ($old) && (!$new) )
            return 0;


    }


    function find_site_vers($site,$db)
    {
        $sql = "select R.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C\n"
             . " where R.censusid = C.id\n"
             . " and C.site IN ($site)";
        return find_many($sql,$db);
    }

    function find_mgrp_vers($gid,$db)
    {
        $set = array();
        if ($gid)
        {
            $sql = "select R.* from\n"
                 . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C\n"
                 . " where M.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid = $gid\n"
                 . " and M.censusuniq = C.censusuniq\n"
                 . " and R.censusid = C.id\n"
                 . " group by R.vers";
            $set = find_many($sql,$db);
        }
        return $set;
    }


    function sub_global($gid,$host,$code,$valu,$name,$now,$db)
    {
        $num = 0;
        $var = find_var($name,$code,$db);
        if (($var) && ($gid))
        {
            $vid = $var['varid'];
            $num = set_value($vid,$gid,$host,$valu,
                constSourceScripRemoteWizard,$now,$db);
        }
        else
        {
            $stat = "name:$name,g:$gid,c:$code";
            debug_note("sub global ($stat) failure");
        }
        return $num;
    }



    function host_goto(&$env,$s,$h,$db)
    {
        debug_note("host goto");

        $now  = $env['now'];
        $hid  = $env['hid'];
        $gid  = $env['hgid'];
        $ghdi = $env['ghdi'];  // scrip245HelpDeskID
        $ghda = $env['ghda'];  // scrip245HelpDeskAdmin
        $gcmp = $env['gcmp'];  // scrip245Company
        $gnam = $env['gnam'];  // scrip245Name
        $gcls = $env['gcls'];  // scrip245CloseBrowser
        $gssu = $env['gssu'];  // scrip245StartSessionURL

        $x = $env['serv'];
        $c = constCodUVNC;
        $v = constVarUVNC;
        $row = host_value($s,$h,$c,$v,$db);
        $val = ($row)? $row['valu'] : '';
        if ($val)
        {
            GCFG_SubLocal($hid,$gid,$x,$c,'','Scrip236Host',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','Scrip236InstallPath',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','Scrip236Port',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','useRepeater',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','Scrip236Repeater',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','Scrip236ServerID',$env['now'],
                constSourceScripRemoteWizard,$db);
        }

        $c = constCodGoto;
        scop_local($env,$c,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$ghdi,'scrip245HelpDeskID',$env['now'],
            constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$ghda,'scrip245HelpDeskAdmin',
            $env['now'],constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$gcmp,'scrip245Company',$env['now'],
            constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$gnam,'scrip245Name',$env['now'],
            constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$gcls,'scrip245CloseBrowser',$env['now'],
            constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$gssu,'scrip245StartSessionURL',
            $env['now'],constSourceScripRemoteWizard,$db);
        dirty_hid($hid,$db);
        hid_revision($hid,$now,$db);
        scop_local($env,$c,$db);
    }


    function host_uvnc(&$env,$s,$h,$db)
    {
        $now  = $env['now'];
        $hid  = $env['hid'];
        $gid  = $env['hgid'];
        $uadr = $env['uadr'];  // Scrip236Host
        $udld = $env['udld'];  // Scrip236InstallPath
        $uprt = $env['uprt'];  // Scrip236Port
        $urpt = $env['urpt'];  // useRepeater
        $urad = $env['urad'];  // Scrip236Repeater
        $ucid = $env['ucid'];  // Scrip236ServerID
        $udsk = $env['udsk'];  // CreateDesktopIcon

        $x = $env['serv'];
        $c = constCodGoto;
        $v = constVarGoto;

        /* Check for 2 variables: scrip245HelpDeskID and
            scrip245StartSessionURL since some clients may not have one or the
            other. */
        $row = host_value($s,$h,$c,$v,$db);
        $row2 = host_value($s,$h,$c,'scrip245StartSessionURL',$db);
        $val = ($row)? $row['valu'] : '';
        $val2 = ($row2)? $row2['valu'] : '';
        if ($val || $val2)
        {
            GCFG_SubLocal($hid,$gid,$x,$c,'','scrip245HelpDeskID',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','scrip245HelpDeskAdmin',
                $env['now'],constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','scrip245Company',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','scrip245Name',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c, 1,'scrip245CloseBrowser',
                $env['now'],constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$x,$c,'','scrip245StartSessionURL',
                $env['now'],constSourceScripRemoteWizard,$db);
        }
        $c = constCodUVNC;
        scop_local($env,$c,$db);
        if ($uadr == '' && $urpt)
        {
           $uadr = '[host]';
        }
        GCFG_SubLocal($hid,$gid,$x,$c,$uadr,'Scrip236Host',$env['now'],
            constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$udld,'Scrip236InstallPath',$env['now'],
            constSourceScripRemoteWizard,$db);
        if (($uprt == '') || (is_numeric($uprt)))
        {
            GCFG_SubLocal($hid,$gid,$x,$c,$uprt,'Scrip236Port',$env['now'],
                constSourceScripRemoteWizard,$db);
        }
        GCFG_SubLocal($hid,$gid,$x,$c,$urpt,'useRepeater',$env['now'],
                       constSourceScripRemoteWizard,$db);
        GCFG_SubLocal($hid,$gid,$x,$c,$urad,'Scrip236Repeater',$env['now'],
                      constSourceScripRemoteWizard,$db);
        /* Note that we do not store udsk in the scrip configuration */
        if (is_numeric($ucid));
        {
         GCFG_SubLocal($hid,$gid,$x,$c,$ucid,'Scrip236ServerID',$env['now'],
                        constSourceScripRemoteWizard,$db);
        }

        if ($udsk)
        {
            GCFG_SubLocal($hid,$gid,$x,$c,0,'Scrip236AutoCon',$env['now'],
                        constSourceScripRemoteWizard,$db);
        }
        else
        {
            GCFG_SubLocal($hid,$gid,$x,$c,1,'Scrip236AutoCon',$env['now'],
                        constSourceScripRemoteWizard,$db);
        }
        GCFG_SubLocal($hid,$gid,$x,$c,$udsk,'CreateDesktopIcon',$env['now'],
                    constSourceScripRemoteWizard,$db);

        scop_local($env,$c,$db);
        dirty_hid($hid,$db);
        hid_revision($hid,$now,$db);
    }


    function mgrp_goto(&$env,$mgrp,$glob,$db)
    {
        $goto = constNamGoto;
        $now  = $env['now'];
        $ghdi = $env['ghdi'];  // scrip245HelpDeskID
        $ghda = $env['ghda'];  // scrip245HelpDeskAdmin
        $gcmp = $env['gcmp'];  // scrip245Company
        $gnam = $env['gnam'];  // scrip245Name
        $gcls = $env['gcls'];  // scrip245CloseBrowser
        $gssu = $env['gssu'];  // script245StartSessionURL
        $hh   = $env['serv'];
        $oo   = constCodGoto;
        $xx   = constCodUVNC;
        $vv   = constVarUVNC;
        $name = $mgrp['name'];
        $gid  = $mgrp['mgroupid'];
        debug_note("Configure $goto for group <b>$name</b> ($gid)");
        $data = find_valu($gid,$vv,$xx,$db);
        $valu = ($data)? $data[0]['valu'] : '';

        if($env['scop']==constScopAll)
        {
            $env['gid'] = $gid;
            if ($valu)
            {
                user_done($env,$xx,'Scrip236Host','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'Scrip236Port','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'Scrip236InstallPath','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'useRepeater',$urpt,
                      constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'Scrip236Repeater',$urad,
                      constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'Scrip236ServerID',$ucid,
                      constSourceScripRemoteWizard,$now,$db);
            }
            user_done($env,$oo,'scrip245HelpDeskID',$ghdi,
                constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'scrip245HelpDeskAdmin',$ghda,
                constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'scrip245Company',$gcmp,
                constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'scrip245Name',$gnam,
                constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'scrip245CloseBrowser',$gcls,
                constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'scrip245StartSessionURL',$gssu,
                constSourceScripRemoteWizard,$now,$db);
        }
        else
        {
            if($valu)
            {
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','Scrip236Host',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','Scrip236Port',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','Scrip236InstallPath',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,$urpt,'useRepeater',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,$urad,'Scrip236Repeater',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,$ucid,'Scrip236ServerID',
                   constSourceScripRemoteWizard,$db);

            }
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$ghdi,'scrip245HelpDeskID',
                constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$ghda,'scrip245HelpDeskAdmin',
                constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$gcmp,'scrip245Company',
                constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$gnam,'scrip245Name',
                constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$gcls,'scrip245CloseBrowser',
                constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$gssu,'scrip245StartSessionURL',
                constSourceScripRemoteWizard,$db);
        }
        mgrp_revision($gid,$now,$db);
        mgrp_vars($env,$gid,$oo,$db);
    }


    function mgrp_uvnc(&$env,$mgrp,$glob,$db)
    {
        $uvnc = constNamUVNC;
        $now  = $env['now'];
        $uadr = $env['uadr'];  // Scrip236Host
        $udld = $env['udld'];  // Scrip236InstallPath
        $uprt = $env['uprt'];  // Scrip236Port
        $urpt = $env['urpt'];  // useRepeater
        $urad = $env['urad'];  // Scrip236Repeater
        $ucid = $env['ucid'];  // Scrip236ServerID
        $name = $mgrp['name'];
        $gid  = $mgrp['mgroupid'];

        debug_note("Configure $uvnc for group <b>$name</b> ($gid)");

        $hh = $env['serv'];
        $oo = constCodUVNC;
        $xx = constCodGoto;
        $vv = constVarGoto;

        $row  = find_valu($gid,$vv,$xx,$db);
        $valu = ($row)? $row['valu'] : '';

        if($env['scop']==constScopAll)
        {
            if ($valu)
            {
                user_done($env,$xx,'scrip245StartSessionURL','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'scrip245HelpDeskAdmin','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'scrip245CloseBrowser','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'scrip245HelpDeskID','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'scrip245Company','',
                    constSourceScripRemoteWizard,$now,$db);
                user_done($env,$xx,'scrip245Name','',
                    constSourceScripRemoteWizard,$now,$db);
            }
            if ($uadr == '' && $urpt)
            {
                $uadr = '[host]';
            }
            user_done($env,$oo,'Scrip236Host',$uadr,
                constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'Scrip236InstallPath',$udld,
                constSourceScripRemoteWizard,$now,$db);
            if (($uprt == '') || (is_numeric($uprt)))
            {
                user_done($env,$oo,'Scrip236Port',$uprt,
                    constSourceScripRemoteWizard,$now,$db);
            }
            user_done($env,$oo,'useRepeater',$urpt,
                      constSourceScripRemoteWizard,$now,$db);
            user_done($env,$oo,'Scrip236Repeater',$urad,
                      constSourceScripRemoteWizard,$now,$db);
            if (is_numeric($ucid))
            {
                user_done($env,$oo,'Scrip236ServerID',$ucid,
                          constSourceScripRemoteWizard,$now,$db);
            }
            user_done($env,$oo,'CreateDesktopIcon',$udsk,
                      constSourceScripRemoteWizard,$now,$db);
        }
        else
        {
            if ($valu)
            {
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'',
                    'scrip245StartSessionURL',constSourceScripRemoteWizard,
                    $db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','scrip245HelpDeskAdmin',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx, 1,'scrip245CloseBrowser',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','scrip245HelpDeskID',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','scrip245Company',
                    constSourceScripRemoteWizard,$db);
                push_mgrp($mgrp,$glob,$now,$hh,$xx,'','scrip245Name',
                    constSourceScripRemoteWizard,$db);
            }
            if ($uadr == '' && $urpt)
            {
                $uadr = '[host]';
            }
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$uadr,'Scrip236Host',
                constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$udld,'Scrip236InstallPath',
                constSourceScripRemoteWizard,$db);
            if (($uprt == '') || (is_numeric($uprt)))
            {
                push_mgrp($mgrp,$glob,$now,$hh,$oo,$uprt,'Scrip236Port',
                    constSourceScripRemoteWizard,$db);
            }
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$urpt,'useRepeater',
                      constSourceScripRemoteWizard,$db);
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$urad,'Scrip236Repeater',
                      constSourceScripRemoteWizard,$db);
            if (is_numeric($ucid))
            {
                push_mgrp($mgrp,$glob,$now,$hh,$oo,$ucid,'Scrip236ServerID',
                          constSourceScripRemoteWizard,$db);
            }

            if ($udsk)
            {
                push_mgrp($mgrp,$glob,$now,$hh,$oo,0,'Scrip236AutoCon',
                          constSourceScripRemoteWizard,$db);
            }
            else
            {
                push_mgrp($mgrp,$glob,$now,$hh,$oo,1,'Scrip236AutoCon',
                          constSourceScripRemoteWizard,$db);
            }
            push_mgrp($mgrp,$glob,$now,$hh,$oo,$udsk,'CreateDesktopIcon',
                      constSourceScripRemoteWizard,$db);

        }
        mgrp_revision($gid,$now,$db);
        mgrp_vars($env,$gid,$oo,$db);
    }


    function set_goto(&$env,$db)
    {
        $scop = $env['scop'];
        if (($scop == constScopHost) && ($env['revl']))
        {
            $hh = $env['revl']['host'];
            $ss = $env['revl']['site'];
            host_goto($env,$ss,$hh,$db);
        }
        if (($scop == constScopSite) && ($env['site']))
        {
            $site = $env['site']['customer'];
            site_goto($env,$site,$db);
        }
        if (($scop == constScopGroup) && ($env['mgrp']))
        {
            $mgrp = $env['mgrp'];
            mgrp_goto($env,$mgrp,0,$db);
        }
        if ($scop == constScopAll)
        {
            user_goto($env,$db);
        }
    }


    function set_uvnc(&$env,$db)
    {
        $scop = $env['scop'];
        if (($scop == constScopHost) && ($env['revl']))
        {
            $host = $env['revl']['host'];
            $site = $env['revl']['site'];
            host_uvnc($env,$site,$host,$db);
        }
        if (($scop == constScopSite) && ($env['site']))
        {
            $site = $env['site']['customer'];
            site_uvnc($env,$site,$db);
        }
        if (($scop == constScopGroup) && ($env['mgrp']))
        {
            $mgrp = $env['mgrp'];
            mgrp_uvnc($env,$mgrp,0,$db);
        }
        if ($scop == constScopAll)
        {
            user_uvnc($env,$db);
        }
    }


    function wiz_done(&$env,$db)
    {
        $scop = $env['scop'];
        $self = $env['self'];
        $ctyp = $env['ctyp'];
        $mid  = 0;
        $what = "no machines";
        if (($scop == constScopHost) && ($env['revl']))
        {
            $site = $env['revl']['site'];
            $host = $env['revl']['host'];
            $mid  = $env['revl']['censusid'];
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
            $what = "machine in group <b>$name</b>";
        }
        if ($scop == constScopAll)
        {
            $what = "all my machines";
        }
        $href = '../acct/census.php';
        $conn = '';
        $conf = html_link($self,'Configure more machines');
        $cens = html_link($href,'Go to the Census page');
        if ($mid)
        {
            $href = "$self?act=host&mid=$mid&ctyp=$ctyp";
            $link = html_link($href,'Take control of this machine');
            $conn = "\n<li>$link</li>";
        }
        echo <<< DONE

        <p>
           You have configured remote control access for $what.
           What would you like to do next?
        </p>

        <ul>$conn
           <li>$conf</li>
           <li>$cens</li>
        </ul>

DONE;

    }


   /*
    |   We've specified Ultr@VNC ... if we're configuring
    |   in "connect" mode just procede to the connection
    |   page afterwards.
    */

    function done_uvnc(&$env,$db)
    {
        $uadr = $env['uadr'];
        $udld = $env['udld'];
        $uprt = $env['uprt'];
        $urpt = $env['urpt'];  /* boolean: whether to ues the repeater */

        /* If not using the repeater, the remote address has to be filled in. */
        if ((!$urpt) && (($uadr == '') || ($uadr == constNoUVNC)))
        {
            echo para('<font color=red><b>You must enter '
                      . '"Address of remote computer" to use a direct connection'
                      . '</b></font>');
            echo para('Please fill your public address ...');
            form_uvnc($env,$db);
        }
        else
        {
            set_uvnc($env,$db);
            if ($env['host'])
            {
                conn_uvnc($env,$db);
            }
            else
            {
                wiz_done($env,$db);
            }
        }
    }


    function dead_code(&$env,$name,$code,$db)
    {
        $self = $env['self'];
        $href = '../acct/census.php';
        $conf = html_link($self,'Configure more machines');
        $cens = html_link($href,'Go to the Census page');
        echo <<< DEAD

        <p>
           The systems where you would like configure remote
           control using <b>$name</b> do not have a version
           of the ASI client that supports this function.
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


    function create_gssu($gssu)
    {
        // scrip245StartSessionURL
        $size = strlen($gssu);
        if ($size < 20)
            $size = 20;

        $gssu = textbox('gssu',$size,$gssu);

        return <<< HERE
        <tr>
        <td>Start Session URL:</td>
        <td>$gssu</td>
        </tr>
HERE;
    }


    function create_ghdi($ghdi)
    {
        // scrip245HelpDeskID
        $size = 20;
        $ghdi = textbox('ghdi',$size,$ghdi);

        return <<< HERE
        <tr>
        <td>Name of partner:</td>
        <td>$ghdi</td>
        <td>Partner ID</td>
        </tr>
HERE;
    }


    /*   We need to create content based on the version of the client.
         If it is less than const (2.001.0929) we create the Partner field.
         If it is equal or greater than const, we create the start session
         url field.
    */
    function client_version_content(&$env,$db)
    {
        $scop = $env['scop'];
        $type = 145;

        if ( ($scop == constScopHost) && ($env['revl']) )
        {
            $vers = $env['revl']['vers'];
            $type = strnatcasecmp($vers,constClientVersion);
        }
        if ( ($scop == constScopSite) && ($env['site']) )
        {
            $site = quoteslash($env['site']['customer']);
            $set  = find_site_vers($site,$db);
            $type = find_site_type($set);
        }
        if ( ($scop == constScopGroup) && ($env['mgrp']) )
        {
            $gid  = $env['mgrp']['mgroupid'];
            $set  = find_mgrp_vers($gid,$db);
            $type = find_site_type($set);
        }
        if ($scop == constScopAll)
        {
            $site = find_all_sites($env['auth'],$db);
            $site = find_all_vers($site,$db);
            $set  = find_site_vers($site,$db);
            $type = find_site_type($set);
        }
        if ($type >= 2)
        {
            /* client mix */
            $cvc  = create_gssu($env['gssu']);
            $cvc .= create_ghdi($env['ghdi']);
            $cvc .= hidden('ctyp',2);
            return $cvc;
        }
        if ($type == 1)
        {
            /* all new clients */
            return (create_gssu($env['gssu']) . hidden('ctyp',1));
        }
        if ($type <= 0)
        {
            /* all old clients */
            return (create_ghdi($env['ghdi']) . hidden('ctyp',0));
        }
    }


    function form_goto(&$env,$db)
    {
        $ghda = $env['ghda'];  // scrip245HelpDeskAdmin
        $gcmp = $env['gcmp'];  // scrip245Company
        $gnam = $env['gnam'];  // scrip245Name
        $gcls = $env['gcls'];  // scrip245CloseBrowser

        echo post_self('myform');
        echo hidden('act', 'gdon');
        echo hidden('hlp', 'goto');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('gid', $env['gid']);
        echo hidden('tid', $env['tid']);
        echo hidden('scop',$env['scop']);

        /* fetch the version of the client and
           create the appropriate content */
        $cvc = client_version_content($env,$db);

        $size = 20;
        $code = constCodGoto;
        $name = constNamGoto;
        $href = help_href($code);
        $here = html_page($href,'here');
        $ghda = textbox('ghda',$size,$ghda);
        $gnam = textbox('gnam',$size,$gnam);
        $gcmp = textbox('gcmp',$size,$gcmp);
        $gcls = checkbox('gcls',$gcls);

        echo <<< GOTO

        <p>
          Note that you must already have a GoToAssist account set
          up in order for this to work.  Please enter the information
          for your GoToAssist account below.
        </p>

        <p>
          Please click $here for more information on the configuration
          of the $name remote control function (Scrip $code).
        </p>

        <p>
          Important &ndash; Please remember that the GoToAssist
          configuration you define below will take effect on the
          system(s) you are configuring the next time the ASI client
          from these systems contacts the ASI server.  This will
          happen at a frequency determined by the execution
          schedule of Scrip 177.  By default, Scrip 177 runs once
          per hour with a 50-minute random interval.  To change
          the Scrip 177 execution schedule, please click on
          the <i>sites</i> link on the <i>configuration</i> bar
          at the top right-hand corner of this page.  Then, click
          on the <i>view machines</i> link for the site where you
          want to change the Scrip 177 schedule.
        </p>

        <table border=0>
        <tr>
          <td><b>Required:</b></td>
          <td><br></td>
          <td><br></td>
        </tr>
          $cvc
        <tr>
          <td>Name of agent or representative:</td>
          <td>$ghda</td>
          <td>Help desk name</td>
        </tr>
        <tr>
          <td><b>Optional:</b></td>
          <td><br></td>
          <td><br></td>
        </tr>
        <tr>
          <td>Close feedback page?</td>
          <td align="right">$gcls</td>
          <td>Close user feedback page</td>
        </tr>
        <tr>
          <td>Name: (Blank defaults to system user)</td>
          <td>$gnam</td>
          <td>User name to use for logging session</td>
        </tr>
        <tr>
          <td>Company: (Blank defaults to site name)</td>
          <td>$gcmp</td>
          <td>Company name to use for logging session</td>
        </tr>
        </table>

GOTO;
        echo next_cancel_help(constCodGoto);
        echo form_footer();
    }


   /*
    |   We've specified Goto ... if we're configuring
    |   in "connect" mode just procede to the connection
    |   page afterwards.
    */

    function done_goto(&$env,$db)
    {
        $ghdi = $env['ghdi'];
        $ghda = $env['ghda'];
        $gssu = $env['gssu'];

        if (($ghdi != '' || $gssu != '') && ($ghda != ''))
        {
            set_goto($env,$db);
            if ($env['host'])
            {
                conn_goto($env,$db);
            }
            else
            {
                wiz_done($env,$db);
            }
        }
        else
        {
            echo para('Please fill in all the required values.');
            form_goto($env,$db);
        }
    }


    /* find the appropriate value dependent on the client version */
    function ctyp_find_value($env,$db)
    {
        $ctyp = $env['ctyp'];
        $g    = 0;
        if ($ctyp >= 2)
        {
            $g = CWIZ_FindValue($env,constCodGoto,constVarGoto,$db);
            $g = CWIZ_FindValue($env,constCodGoto,constVarSSUGoto,$db);
        }
        if ($ctyp == 1)
            $g = CWIZ_FindValue($env,constCodGoto,constVarSSUGoto,$db);

        if ($ctyp <= 0)
            $g = CWIZ_FindValue($env,constCodGoto,constVarGoto,$db);

        return $g;
    }



   /*
    |  Returns all the values for one scrip for one
    |  machine group.
    */

    function mgrp_scop_valu($gid,$scop,$db)
    {
        $set = array();
        if (($gid) && ($scop))
        {
            $sql = "select X.valu, V.name from\n"
                 . " ".$GLOBALS['PREFIX']."core.VarValues as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
                 . " where V.scop = $scop\n"
                 . " and X.varuniq = V.varuniq\n"
                 . " and X.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid = $gid";
            $set = find_many($sql,$db);
        }
        return $set;
    }


   /*
    |  Returns all the values for one scrip on
    |  one single machine.
    */

    function host_scop_valu($hid,$scop,$db)
    {
        $set = array();
        if (($hid) && ($scop))
        {
            $sql = "select V.name, X.valu from\n"
                 . " ".$GLOBALS['PREFIX']."core.ValueMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.VarValues as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C\n"
                 . " where V.scop = $scop\n"
                 . " and M.censusuniq = C.censusuniq\n"
                 . " and C.id = $hid\n"
                 . " and M.mgroupuniq = X.mgroupuniq\n"
                 . " and M.varuniq = V.varuniq\n"
                 . " and X.varuniq = V.varuniq";
            $set = find_many($sql,$db);
        }
        return $set;
    }


   /*
    |  We want to configure a scrip for every site that
    |  this user owns.  The difficulty is that we want
    |  to show the user reasonable default values, but
    |  the sites might be currently configured differently.
    |
    |  So ... assuming the unconfigured sites will be empty,
    |  we'll just pick the site with the longest current value.
    */

    function find_target(&$env,$code,$db)
    {
        $gid  = 0;
        $name = ($code == constCodGoto)? constVarGoto : constVarUVNC;
        $var  = find_var($name,$code,$db);
        if ($var)
        {
            $vid = $var['varid'];
            $qu  = safe_addslashes($env['auth']);

            $sql = "select G.mgroupid from\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.VarValues as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V\n"
                 . " where U.username = '$qu'\n"
                 . " and U.customer = G.name\n"
                 . " and X.mgroupuniq = G.mgroupuniq\n"
                 . " and X.varuniq = V.varuniq\n"
                 . " and V.varid = $vid\n"
                 . " order by length(valu) desc\n"
                 . " limit 1";
            $row = find_one($sql,$db);
            $gid = ($row)? $row['mgroupid'] : 0;
        }
        GCFG_CreateScripValues($code, 0, $gid, constScopAll, $db);
        return mgrp_scop_valu($gid,$code,$db);
    }


    function load_code(&$env,$code,$db)
    {
        $scop = $env['scop'];
        $out  = array( );
        $set  = array( );
        if (($scop == constScopHost) && ($env['revl']))
        {
            $hid = $env['revl']['censusid'];
            GCFG_CreateScripValues($code, $hid, 0, $scop, $db);
            $set = host_scop_valu($hid,$code,$db);
        }
        if (($scop == constScopSite) && ($env['sgid']))
        {
            $gid = $env['sgid'];
            GCFG_CreateScripValues($code, 0, $gid, $scop, $db);
            $set = mgrp_scop_valu($gid,$code,$db);
        }
        if (($scop == constScopGroup) && ($env['mgrp']))
        {
            $gid = $env['mgrp']['mgroupid'];
            GCFG_CreateScripValues($code, 0, $gid, $scop, $db);
            $set = mgrp_scop_valu($gid,$code,$db);
        }
        if ($scop == constScopAll)
        {
            $grp = all_machines($env,$db);
            $gid = ($grp)? $grp['mgroupid'] : 0;
            GCFG_CreateScripValues($code, 0, $gid, $scop, $db);
            $set = mgrp_scop_valu($gid,$code,$db);
            if (!$set)
            {
                $set = find_target($env,$code,$db);
            }
        }

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $valu = $row['valu'];
                $out[$name] = $valu;
                debug_note("name:$name value:$valu");
            }
        }
        return $out;
    }


    function conf_goto(&$env,$db)
    {
        $site = '';
        $host = '';
        $self = $env['self'];
        $hid  = $env['hid'];
        $cid  = $env['cid'];
        $scop = $env['scop'];
        $ctyp = $env['ctyp'];
        $name = constNamGoto;
        $code = constCodGoto;
        $ghdi = CWIZ_FindValue($env,$code,'scrip245HelpDeskID',$db);
        $ghda = CWIZ_FindValue($env,$code,'scrip245HelpDeskAdmin',$db);
        $gssu = CWIZ_FindValue($env,$code,'scrip245StartSessionURL',$db);
        $href = help_href($code);
        $here = html_page($href,'click here');
        if ($env['revl'])
        {
            $site = $env['revl']['site'];
            $host = $env['revl']['host'];
        }
        $cvc  = fetch_client_content($ctyp,$ghdi,$gssu);

        $yref = "$self?act=gcon&hid=$hid";
        $nref = '../acct/census.php';
        $cref = "$self?act=scop&hid=$hid&cid=$cid&scop=$scop&meth=2";
        $ylnk = html_link($yref,'Yes, initiate remote control');
        $nlnk = html_link($nref,'No, do not initiate remote control');
        $clnk = html_link($cref,'Change configuration');

        echo <<< GCNF

        <p>
          You have chosen to initiate a $name remote
          control session using the following setup:
        </p>

        <table border="1">
        <tr>
           <td>Site</td>
           <td>$site</td>
        </tr>
        <tr>
          <td>Machine</td>
          <td>$host</td>
        </tr>

        <tr>
          <td>Method</td>
          <td>$name</td>

          $cvc

          <td>Help desk name</td>
          <td>$ghda</td>
        </tr>
        </table>

        <p>
          Please make sure the controlling software is set up
          ($here for more information), and select one of the
          following:
        </p>

        <ul>
          <li>$ylnk</li>
          <li>$nlnk</li>
          <li>$clnk</li>
        </ul>

GCNF;

    }

    function conf_uvnc(&$env,$db)
    {
        $site = '<br>';
        $host = '<br>';
        $name = constNamUVNC;
        $self = $env['self'];
        $hid  = $env['hid'];
        $cid  = $env['cid'];
        $scop = $env['scop'];
        $addr = CWIZ_FindValue($env,constCodUVNC,constVarUVNC,$db);
        $port = CWIZ_FindValue($env,constCodUVNC,constPrtUVNC,$db);

        $href = help_href(constCodUVNC);
        $here = html_page($href,'click here');
        if ($env['revl'])
        {
            $site = $env['revl']['site'];
            $host = $env['revl']['host'];
        }

        /* Some additional variables we need.*/
        $urpt = CWIZ_FindValue($env,constCodUVNC,'useRepeater',$db);
        $urad = CWIZ_FindValue($env,constCodUVNC,'Scrip236Repeater', $db);
        /* We can get here having set up udsk, so check to see if that
           is the case, and if so, initialize if from the database.  */
        $udsk = $env['udsk'];
        if ($udsk == 'notset')
        {
            $udsk = CWIZ_FindValue($env,constCodUVNC,'CreateDesktopIcon', $db);
        }
        $ucid = CWIZ_FindValue($env, constCodUVNC, 'Scrip236ServerID', $db);

        $yref = "$self?act=ucon&hid=$hid";
        $nref = '../acct/census.php';
        $cref = "$self?act=scop&hid=$hid&cid=$cid&scop=$scop&meth=3";
        $ylnk = html_link($yref,'Yes, initiate remote control');
        $nlnk = html_link($nref,'No, do not initiate remote control');
        $clnk = html_link($cref,'Change configuration');

        if ($urad == '')
        {
            $urad = '<i>use ASI server</i>';
        }
        if ($ucid == 0)
        {
            $ucid = '<i>generate automatically</i>';
        }
        if ($port == '')
        {
            $port = '<i>default to 5500</i>';
        }

        /* Make text versions of booleans. */
        $urptText = $urpt ? 'yes' : 'no';
        $udskText = intval($udsk) ? 'by end user' : 'automatically';

        echo <<< UCNF
        <p>
            You have chosen to initiate an UltraVNC remote
            control session using the following setup:
        </p>

        <table border="1">
            <tr>
                <td>Site</td>
                <td>$site</td>
            </tr>
            <tr>
                <td>Machine</td>
                <td>$host</td>
            </tr>
            <tr>
                <td>Method</td>
                <td>$name</td>
            </tr>
            <tr>
                <td>Connection initiation</td>
                <td>$udskText</td>
            </tr>
            <tr>
                <td>Listener port (if blank, = 5500)</td>
                <td>$port</td>
            </tr>
            <tr>
                <td>Use intermediate server</td>
                <td>$urptText</td>
            </tr>
UCNF;

        if ($urpt)
        {
            echo <<< UCNF
            <tr>
                <td>Domain or IP of intermediate server</td>
                <td>$urad</td>
            </tr>
            <tr>
                <td>Connection ID</td>
                <td>$ucid</td>
            </tr>
            </table>
UCNF;
        }
        else
        {
            echo <<< UCNF
            <tr>
                <td>Public address at site where remote system is located</td>
                <td>$addr</td>
            </tr>
            </table>
            <p>
                <br>
                <br>
            </p>
            <p>
                Before proceeding, please make sure that <b>one</b> copy
                of UltraVNC client process is running in listen mode on the
                remote system (the one used to take remote control of the
                target system), and that all incoming traffic to the
                <i>listener port</i> at the site where the remote system
                is located is forwarded to the remote system.
            </p>
UCNF;
        }

        echo <<< UCNF

        <p>
            <br>
            <br>
        </p>
        <p>
            Please $here for more information on the configuration
            of the UltraVNC remote control function (Scrip 236).
        </p>
        <ul>
            <li>$ylnk</li>
            <li>$nlnk</li>
            <li>$clnk</li>
        </ul>
UCNF;
    }

    function config_warn(&$env,$db)
    {
        $self = $env['self'];
        $hid  = $env['hid'];
        $site = '';
        $host = '';

        if ($env['revl'])
        {
            $hid  = $env['revl']['id'];
            $site = $env['revl']['site'];
            $host = $env['revl']['host'];
        }

        $wtxt = 'Clear the manual settings and use the wizard to set up remote access';
        $ctxt = 'Just go back to the census page';
        $wref = "$self?act=kill&hid=$hid";
        $cref = '../acct/census.php';
        $wlnk = html_link($wref,$wtxt);
        $clnk = html_link($cref,$ctxt);

        echo <<< WARN

        <p>
          The advanced interface (Scrip configuration) pages
          have been used to configure remote control settings
          for the machine <b>$host</b> at site <b>$site</b>,
          and those settings are incompatible with the
          operation of this wizard.
        </p>

        <p>
          You can do one of the following:
        </p>

        <ul>
          <li>$wlnk</li>
          <li>$clnk</li>
        </ul>
WARN;
    }


   /*
    |  There's no record of the specified machine in the core database.
    |  Either the machine hasn't logged yet, or it was deleted or expired.
    |  Or perhaps it logs to a different server.
    */

    function config_none(&$env,$db)
    {
        $host = '';
        $site = '';
        $ctxt = 'Return to census page.';
        $cref = '../acct/census.php';
        $link = html_link($cref,$ctxt);
        if ($env['host'])
        {
            $site = $env['host']['site'];
            $host = $env['host']['host'];
            echo <<< NONE

            <p>
              There is no configuration information for machine
              <b>$host</b> at <b>$site</b>.  The machine needs to
              log it's configuration information first.
            </p>
NONE;
        }
        echo para($link);
    }


    function config_kill(&$env,$db)
    {
        $now = $env['now'];
        $gid = $env['hgid'];
        if (($env['revl']) && ($gid))
        {
            $hid = $env['revl']['censusid'];
            $srv = $env['serv'];

            GCFG_SubLocal($hid,$gid,$srv,236,'','Scrip236Host',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,236,'','Scrip236InstallPath',
                $env['now'],constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,236,'','Scrip236Port',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,236,1,'useRepeater',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,236,'','Scrip236Repeater',$env['now'],
                constSourceScripRemoteWizard,$db);
             /* we do ot change CreareDesktop icon in the database*/
            GCFG_SubLocal($hid,$gid,$srv,236,0,'Scrip236ServerID',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,236,0,'CreateDesktopIcon',$env['now'],
                constSourceScripRemoteWizard,$db);

            GCFG_SubLocal($hid,$gid,$srv,245,'','scrip245HelpDeskID',
                $env['now'],constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,245,'','scrip245HelpDeskAdmin',
                $env['now'],constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,245,'','scrip245Company',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,245,'','scrip245Name',$env['now'],
                constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$srv,245, 1,'scrip245CloseBrowser',
                $env['now'],constSourceScripRemoteWizard,$db);
            GCFG_SubLocal($hid,$gid,$src,245,'','scrip245StartSessionURL',
                $env['now'],constSourceScripRemoteWizard,$db);
            dirty_hid($hid,$db);
            hid_revision($hid,$now,$db);
            $env['hid']  = $hid;
            $env['act']  = 'meth';
            $env['scop'] = constScopHost;
            $env['meth'] = constMethVoid;
            choose_meth($env,$db);
        }
    }


    function setup_goto(&$env,$db)
    {
        $set = load_code($env,constCodGoto,$db);
        if ($set)
        {
            $env['ghdi'] = @ strval($set['scrip245HelpDeskID']);
            $env['ghda'] = @ strval($set['scrip245HelpDeskAdmin']);
            $env['gcmp'] = @ strval($set['scrip245Company']);
            $env['gnam'] = @ strval($set['scrip245Name']);
            $env['gcls'] = @ intval($set['scrip245CloseBrowser']);
            $env['gssu'] = @ strval($set['scrip245StartSessionURL']);
        }

        if ($set)
        {
            form_goto($env,$db);
        }
        else
        {
            dead_code($env,constNamGoto,constCodGoto,$db);
        }
    }



    function scop_local(&$env,$code,$db)
    {
        $set  = array();
        if (($env['revl']) && ($env['priv']))
        {
            $hid = $env['revl']['censusid'];
            $sql = "select V.name, V.itype, V.varid,\n"
                 . " M.stat, M.srev, X.*, G.mgroupid\n"
                 . " from ValueMap as M,\n"
                 . " Variables as V,\n"
                 . " VarValues as X,\n"
                 . " MachineGroups as G,\n"
                 . " Census as C\n"
                 . " where M.censusuniq = C.censusuniq\n"
                 . " and C.id = $hid\n"
                 . " and V.scop = $code\n"
                 . " and M.varuniq = V.varuniq\n"
                 . " and X.varuniq = V.varuniq\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and X.mgroupuniq = M.mgroupuniq\n"
                 . " order by V.name";
            $set = find_many($sql,$db);
        }
        if ($set)
        {
            $host = $env['revl']['host'];
            $site = $env['revl']['site'];
            $args = explode('|','name|last|type|revl|stat|srev|gid|vid|xid|valu');
            $rows = safe_count($set);
            $cols = safe_count($args);
            $text = "$host at $site &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($args,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $gid  = $row['mgroupid'];
                $xid  = $row['valueid'];
                $vid  = $row['varid'];
                $last = $row['last'];
                $revl = $row['revl'];
                $stat = $row['stat'];
                $srev = $row['srev'];
                $type = type_name($row['itype']);
                $name = disp($row,'name');
                $valu = disp($row,'valu');
                $when = date('m/d/y H:i:s',$last);
                $args = array($name,$when,$type,$revl,$stat,$srev,$gid,$vid,$xid,$valu);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function uvnc_fail_message()
    {
        echo <<< HELP

        <p>Did you</p>

        <ol>
          <li>
            Start <b>only one</b> copy of the Ultr@VNC
            client in Listen mode on the remote system
            (the one used to take remote control of the
            target system)?
          </li>
          <li>
            Use the correct public IP address or domain for
            the site where the remote system is located?
          </li>
          <li>
            Configure the gateway device at the location
            where the remote system is located to forward
            traffic coming into the <i>listener port</i>
            to the remote system?
          </li>
          <li>
            Configure port forwarding on the gateway device
            where the remote system is located with the right
            <i>listener port</i> number? Remember that if you
            did not change it, by default the listener port
            number is <b>5500</b>.
          </li>
          <li>
            Ensure that the ASI client is running on the
            target system?
          </li>
          <li>
            Ensure that the site where the remote system is
            located, and the one where the target system is
            located are connected to the Internet?
          </li>
        </ol>

HELP;

    }


    function uvnc_good_message()
    {
        echo <<< HELP

        <p>
          Please note that depending on a number of factors related
          to connectivity, the need to download and install Ultr@VNC,
          and start the server session, there may be a delay in the
          posting of event logs reporting the status of the Ultr@VNC
          connection, ranging from a few to several minutes.
        </p>
HELP;

    }


    function good_message()
    {
        echo <<< HELP

        <p>
          Click on the <i>Query</i> button to see the status
          of your connection.  On the page that is displayed,
          you can click on your browser's <i>Refresh</i> button
          to update the status.
        </p>
HELP;

    }


    function push_connect(&$env,$code,$name,$db)
    {
        $gid = $env['hgid'];
        $var = find_var($name,$code,$db);
        if (($env['revl']) && ($var) && ($gid))
        {
            $time = time();
            $site = $env['revl']['site'];
            $host = $env['revl']['host'];
            $uuid = $env['revl']['uuid'];
            $hid = $env['revl']['censusid'];
            $vid = $var['varid'];

            $sql = "select semid from SemClears left join "
                . "MachineGroups on (SemClears.mgroupuniq="
                . "MachineGroups.mgroupuniq) left join Census on ("
                . "SemClears.censusuniq=Census.censusuniq) left join "
                . "Variables on (SemClears.varuniq=Variables.varuniq) where"
                . " mgroupid=$gid and id =$hid and varid=$vid";
            $set = DSYN_DeleteSet($sql, constDataSetGConfigSemClears, "semid",
                "push_connect", 0, 1, constOperationDelete, $db);
            if($set)
            {
                $sql = "update SemClears left join "
                    . "MachineGroups on (SemClears.mgroupuniq="
                    . "MachineGroups.mgroupuniq) left join Census on ("
                    . "SemClears.censusuniq=Census.censusuniq) left join "
                    . "Variables on (SemClears.varuniq=Variables.varuniq) set"
                    . "\n valu = valu-1,\n"
                    . " revl = revl+1,\n"
                    . " SemClears.last = $time\n"
                    . " where mgroupid = $gid\n"
                    . " and id = $hid\n"
                    . " and varid = $vid";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                DSYN_UpdateSet($set, constDataSetGConfigSemClears, "semid",
                    $db);
                if ($num)
                {
                    dirty_hid($hid,$db);
                    hid_revision($hid,$time,$db);
                    scop_local($env,$code,$db);

                    $res = GCFG_SetVariableValue(0, $vid,
                        constVblTypeSemaphore, $gid, $hid,
                        constSourceScripRemoteWizard, $time, $db);
                    if($res==constSetVarValFailure)
                    {
                        logs::log(__FILE__, __LINE__, "remote.php: failed setting value for "
                            . "varid $vid mgroupid $gid censusid $hid", 0);
                    }
                }
            }

            $urpt = CWIZ_FindValue($env, constCodUVNC, 'useRepeater', $db);
            $serverName = CWIZ_FindValue($env, constCodUVNC, 'Scrip236Repeater', $db);
            $ucid = CWIZ_FindValue($env, constCodUVNC, 'Scrip236ServerID', $db);

            /* Calculate the other info we need in the message. */
            $instLink = '<a href="' . constDefaultVNCURL . '">here</a>';
            if ($serverName == '')
            {
                $serverName = server_name($db);
            }

            /* This has to be specially passed in through the link it
               isn't stored  in the database. */
            $udsk = CWIZ_FindValue($env, constCodUVNC, 'CreateDesktopIcon', $db);

            if ($udsk)
            {
                /*Don't click on the button, but tell them that the user should*/
                $icon = CWIZ_FindValue($env, constCodUVNC, 'DesktopIconLabel', $db);

                if (icon == '')
                {
                    $icon = 'Start remote support';
                }
                echo <<< USER

                  <p>
                      You must now contact the end user of the machine <b>$host</b> at site
                      <b>$site</b> and ask them to initiate the connection by double-clicking
                      on the desktop Icon labeled <b>$icon</b>.
                  </p>
USER;
            }
            else
            {
                /* Click on the button and show the status. */
                $txt = "Connection to <b>$host</b> at <b>$site</b>";
                $msg = ($num)? 'Started' : 'Failed';
                echo para("$txt $msg");
            }

            if ($urpt)
            {
                /* Calculate the connection ID to use.  This has to match the
                   algorithm that the client uses. */
                if ($ucid == 0)
                {
                    $ucid = (hexdec(substr($uuid,30)) % (constIDMax-constIDMin+1))
                      + constIDMin;
                }
            }

            echo <<< VIEW
                <p>
                    Now you need to start the viewer to take remote control.  Follow
                    these steps:
                </p>

                <table>
                    <tr>
                        <td valign=top>
                        <ol>
                            <li>If you have not already done so, please install UltraVNC on your
                                system.  You must have UltraVNC version 1.0.1 to use the intermediate
                                server.  If you need the installation, you can download it $instLink.
                            <li>Click on "Start ... (All) Programs ... UltraVNC ... UltraVNC
                                Viewer".
                            <li>Fill in the text box labeled "VNC server", as shown, with the string:
                                <font size=+1><b>ID:$ucid</b></font>
                            <li>Check the box labeled "Proxy repeater", and fill in the text box
                                next to it, as shown, with the string:
                                <font size=+1><b>$serverName</b></font>
                            <li>Click on the "Connect" button.
                        </ol>
                        </td>
                        <td>
                            <img width=442 height=375 src="vncview.gif">
                        </td>
                    </tr>
                    </table>
VIEW;

                good_message();

                if ($code == constCodUVNC)
                {
                    if ($num)
                    {
                        uvnc_good_message();
                    }
                    else
                    {
                        uvnc_fail_message();
                    }
                }

                $umin = $time - 600;
                $umax = $time + (6*3600);
                $text = button('Query');
                $page = '../event/pager.php';

                echo post_meth('myform','get',$page);
                echo hidden('sel_customer', $site);
                echo hidden('sel_machine',  $host);
                echo hidden('sel_scrip',    $code);
                echo hidden('umin',         $umin);
                echo hidden('umax',         $umax);
                echo para($text);
                echo form_footer();
            }
            else
            {
                echo para('Unexpected Sudden Failure');
            }

    }



    function conn_goto(&$env,$db)
    {
        push_connect($env,constCodGoto,constSemGoto,$db);
    }

    function conn_uvnc(&$env,$db)
    {
        push_connect($env,constCodUVNC,constSemUVNC,$db);
    }


    function setup_uvnc(&$env,$db)
    {
        $set = load_code($env,constCodUVNC,$db);
        if ($set)
        {
            $env['uadr'] = @ strval($set['Scrip236Host']);
            $env['udld'] = @ strval($set['Scrip236InstallPath']);
            $env['uprt'] = @ strval($set['Scrip236Port']);
            $env['urpt'] = @ $set['useRepeater'];
            $env['urad'] = @ strval($set['Scrip236Repeater']);
            /* Only initialize this if it is not set up already. */
            if ($env['udsk'] == 'notset')
            {
                $env['udsk'] = @ $set['CreateDesktopIcon'];
            }
            $env['ucid'] = @ $set['Scrip236ServerID'];
        }
        if ($env['uadr'] == constNoUVNC || '[host]')
        {
           $env['uadr'] = '';
        }
        if ($set)
        {
            form_uvnc($env,$db);
        }
        else
        {
            dead_code($env,constNamUVNC,constCodUVNC,$db);
        }
    }



    function form_uvnc(&$env,$db)
    {
        $self = $env['self'];
        $uadr = $env['uadr'];  /* string: direct-conect address */
        $udld = $env['udld'];  /* string: download URL for UltraVNC install */
        $uprt = $env['uprt'];  /* string: listener port */
        $urpt = $env['urpt'];  /* boolean: whether to ues the repeater */
        $urad = $env['urad'];  /* string: repeater address */
        $udsk = $env['udsk'];  /* boolean: desktop icon present */
        $ucid = $env['ucid'];  /* integer: connection ID */

        $href = help_href(constCodUVNC);
        $here = html_page($href,'click here');

        echo post_self('myform');
        echo hidden('act','udon');
        echo hidden('hlp','uvnc');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('mid', $env['mid']);
        echo hidden('tid', $env['tid']);
        echo hidden('gid', $env['gid']);
        echo hidden('scop',$env['scop']);

        $size = 20;
        $addr = textbox('uadr',$size,$uadr);
        $dnld = textbox('udld',$size,$udld);
        $port = textbox('uprt',$size,$uprt);
        $href = 'http://www.handsfreenetworks.com/download/UltraVNC--100-RC18d-Setup.exe';
        $dloc = html_page($href,$href);
        $defVNC = constDefaultVNCURL;

        /* Radion buttons to select user-initiated vs. auto-initiated. */
        $selAutoStart = radio('udsk', 0, $udsk);
        $selUserStart = radio('udsk', 1, $udsk);

        /* Radio buttons to select repeater vs. direct-connect. */
        $selDirect   = radio('urpt', 0, $urpt);
        $selRepeater = radio('urpt', 1, $urpt);

        /* Text boxes for repeater address and connection ID. */
        $repAddr = textbox('urad', $size, $urad);
        $connID = textbox('ucid', $size, $ucid);

        /* Indent on page. */
        $indent = 20;

        echo <<< UVNC

        <p>
           Throughout the Ultr@VNC configuration wizard, the computer
           you want to take remote control of is called the <b>target</b>
           computer while the computer taking control of the target
           computer is called the <b>remote</b> computer.
        </p>

        <p>
           Before entering the configuration information requested
           below, please make sure that:
        </p>

        <ol>

          <li>
            <b>One</b> (and only one) copy of the Ultr@VNC client process
            is running in listen mode on the remote system(s) (those used
            to take control of the target system(s)).
          </li>

          <li>
           If the remote system(s) access the Internet via a gateway
           device, the port configured as the <i>listener port</i> (see
           below) is open for incoming traffic.
          </li>

          <li>
           Traffic coming into the <i>listener port</i>, be forwarded
           to the remote system (the system taking control of the
           target system(s)).
          </li>

          <li>
           You have the public address (IP or domain) either of the remote
           system itself, if it has one or, more likely, of the
           gateway device used by the remote system to access the
           Internet.
          </li>

        </ol>

        <p>
           Please $here for more information on the configuration
           of the Ultr@VNC remote control function (Scrip 236).
        </p>

        <p>
          <b>Important &ndash; Please remember that the Ultr@VNC configuration
          you define below will take effect on the system(s) you are
          configuring the next time the ASI client from these systems
          contacts the ASI server. This will happen at a frequency
          determined by the execution schedule of Scrip 177. By
          default, Scrip 177 runs once per hour with a 50-minute
          random interval. To change the Scrip 177 execution
          schedule, please click on the <i>sites</i> link on the
          <i>configuration</i> bar at the top right-hand corner of this
          page. Then, click on the <i>view machines</i> link for the site
          where you want to change the Scrip 177 schedule.</b>
        </p>

      <table border=0>
          <tr>
          <tr colspan=4>
              $selRepeater <b>Use intermediate server</b> (recommended)
          </tr>
          </tr>
          <tr>
          <td width=$indent>&nbsp;</td>
              <td colspan=3><b>Optional:</b></td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td>Address of intermediate server</td>
              <td>$repAddr</td>
              <td>
                  The domain or IP address of the intermediate server.
                  If blank, the address of your ASI server is used.
              </td>
              </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td>Connection ID</td>
              <td>$connID</td>
              <td>
                  The connection ID that identifies this connection.  If
                  you leave this at zero (recommended), the ASI server and client
                  will automatically synchronize it for you.
              </td>
          </tr>
          <tr>
              <td colspan=4>
                  $selDirect<b>Use direct connection</b>
              </td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td colspan=3><b>Required:</b></td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td>Address of remote computer</td>
              <td>$addr</td>
              <td>
                  The public address (IP or domain) at the site where the
                  remote computer (the one taking control of the target
                  computer) is located.
              </td>
          </tr>
          <tr>
              <td colspan=4>
                  <b>For both connection types:</b>
              </td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td colspan=3><b>Required:</b></td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td colspan=3>
                  $selAutoStart Connection will be initiated automatically
              </td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td colspan=3>
                  $selUserStart End user will initiate the connection
              </td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td colspan=3><b>Optional:</b></td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td>
                  Remote control software installation<br>
                  executable download URL
              </td>
              <td>$dnld</td>
              <td>
                  URL to use for downloading UltraVNC. If left blank,
                  default download URL, $defVNC, will be used.
              </td>
          </tr>
          <tr>
              <td width=$indent>&nbsp;</td>
              <td>Listener port (Blank defaults to 5500)</td>
              <td>$port</td>
              <td>
                  Port number used by UltraVNC on the target system to communicate
                  with the UltraVNC client on the remote system.
              </td>
          </tr>
      </table>

UVNC;

        echo next_cancel_help(constCodUVNC);
        echo form_footer();
    }


    function clear_over(&$env,$op,$db)
    {
        $qu  = safe_addslashes($env['auth']);
        $lcl = constVarConfStateLocal;
        $gbl = constVarConfStateGlobal;
        $sql = "select site from\n"
             . " ".$GLOBALS['PREFIX']."core.Census\n"
             . " group by site\n"
             . " order by site";
        $set = find_many($sql,$db);
        if ($set)
        {
            $now = $env['now'];
            $num = safe_count($set);
            echo para("$num sites found");
            foreach ($set as $key => $row)
            {
                $site = $row['site'];
                if (clear_site_over($site,$op,$now,
                    constSourceScripRemoteWizard,$db))
                {
                    dirty_site($site,$db);
                    site_revision($site,$now,$db);
                }
            }
        }
    }



    function reset_code(&$env,$db)
    {
        /* UltraVNC */
        $env['uadr'] = '';  // Scrip236Host
        $env['udld'] = '';  // Scrip236InstallPath
        $env['uprt'] = '';  // Scrip236Port
        $env['urpt'] = 1;   // useRepeater
        $env['urad'] = '';  // Scrip236Repeater
        $env['udsk'] = 0;   // CreateDesktopIcon
        $env['ucid'] = '';   // Scrip236ServerID

        /* GotoAssist */
        $env['ghdi'] = '';  // scrip245HelpDeskID
        $env['ghda'] = '';  // scrip245HelpDeskAdmin
        $env['gcmp'] = '';  // scrip245Company
        $env['gnam'] = '';  // scrip245Name
        $env['gcls'] = 1;   // scrip245CloseBrowser
        $env['gssu'] = '';  // scrip245StartSessionURL
        $env['scop'] = constScopAll;

        user_uvnc($env,$db);
        user_goto($env,$db);
        clear_over($env,constCodGoto,$db);
        clear_over($env,constCodUVNC,$db);
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
    $auth = restrict_login($db);
    $comp = component_installed();

    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?  1 : 0;
    $debug = @ ($user['priv_debug'])?  1 : 0;
    $admin = @ ($user['priv_admin'])?  1 : 0;
    $cnfg  = @ ($user['priv_config'])? 1 : 0;
    $rest  = @ ($user['priv_restrict'])? 1 : 0;

    if (!$rest) $cnfg = 1;
    if ($admin) $rest = 0;

    $hid = get_integer('hid',0);
    $mid = get_integer('mid',0);
    $cid = get_integer('cid',0);
    $tid = get_integer('tid',0);
    $gid = get_integer('gid',0);
    $act = get_string('act','scop');
    $hlp = get_string('hlp','');
    $map = $act;

    $post = get_string('button','');
    $scop = get_integer('scop',constScopNone);
    $meth = get_integer('meth',constMethVoid);
    $gcls = get_integer('gcls',0);
    $ctyp = get_integer('ctyp',2);
    $rcon = get_integer('rcon',0);

    $ghdi = get_string('ghdi','');
    $ghda = get_string('ghda','');
    $gcmp = get_string('gcmp','');
    $gnam = get_string('gnam','');
    $gssu = get_string('gssu','');
    $uadr = get_string('uadr','');
    $udld = get_string('udld','');
    $uprt = get_string('uprt','');
    $urpt = get_integer('urpt',1);
    $urad = get_string('urad','');
    $udsk = get_string('udsk','notset');
    $ucid = get_integer('ucid',0);


    if ($post == constButtonCan)
    {
        $scop = constScopNone;
        $meth = constMethVoid;
        /*This allows for our case in which
          we want the users to be able to have some
          configuration powers but not allowed to change
          main configuration page.*/
        if ($rest)
        {
            $scop   = constScopHost;
        }
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

    $host = find_host($mid,$db);
    $mgrp = find_mgrp_info($gid,$db);
    if (($host) && (!$hid))
    {
        $hid = $host['id'];
    }
    $revl = full_revl($hid,$auth,$db);
    if (($revl) && (!$cid))
    {
        $cid = $revl['cid'];
    }
    $site = CWIZ_find_site($cid,$auth,$db);

    if (($host) && (!$revl))
    {
        $act = 'none';
    }
    if (($revl) && (!$scop))
    {
        $scop = constScopHost;
    }
    if (($site) && (!$scop))
    {
        $scop = constScopSite;
    }

    $map = CWIZ_MapScop($scop, $revl, $meth, $site, $tid, $mgrp, 1, $db);

    if (!$scop)
    {
        $map = 'scop';
    }

    $hgrp = find_revl_mgrp($revl,$db);
    $sgrp = find_mgrp_cid($cid,$db);

    $env = array( );
    $env['hid']  = $hid;
    $env['mid']  = $mid;
    $env['tid']  = $tid;
    $env['gid']  = $gid;
    $env['cid']  = $cid;
    $env['now']  = $now;
    $env['act']  = $act;
    $env['priv'] = $priv;
    $env['admn'] = $admin;
    $env['host'] = $host;
    $env['site'] = $site;
    $env['revl'] = $revl;
    $env['mgrp'] = $mgrp;
    $env['hgrp'] = $hgrp;
    $env['sgrp'] = $sgrp;
    $env['hgid'] = ($hgrp)? $hgrp['mgroupid'] : 0;
    $env['sgid'] = ($sgrp)? $sgrp['mgroupid'] : 0;
    $env['auth'] = $auth;
    $env['scop'] = $scop;
    $env['meth'] = $meth;
    $env['cnfg'] = $cnfg;
    $env['conn'] = ($env['host'])? 1 : 0;
    $env['ctyp'] = $ctyp;  // 0 = all old < 2.001.0929
                           // 1 = all new >= 2.001.0929
                           // 2 = mix of old and new
    $env['rcon'] = $rcon;  // 0 = wizard, 1 = remote connection

    $env['uadr'] = $uadr;  // Scrip236Host
    $env['udld'] = $udld;  // Scrip236InstallPath
    $env['uprt'] = $uprt;  // Scrip236Port
    $env['urpt'] = $urpt;  // useRepeater
    $env['urad'] = $urad;  // Scrip236Repeater
    $env['udsk'] = $udsk;  // CreateDesktopIcon
    $env['ucid'] = $ucid;  // Scrip236ServerID

    $env['ghdi'] = $ghdi;  // scrip245HelpDeskID
    $env['ghda'] = $ghda;  // scrip245HelpDeskAdmin
    $env['gcmp'] = $gcmp;  // scrip245Company
    $env['gnam'] = $gnam;  // scrip245Name
    $env['gcls'] = $gcls;  // scrip245CloseBrowser
    $env['gssu'] = $gssu;  // scrip245StartSessionURL

    $env['serv'] = server_name($db);
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['priv'] = $priv;

    if (($act == 'rset') && (!$priv))
    {
        $act == 'scop';
    }

    if ($act == 'gdon')
    {
        if ( ($ghdi == '' && $gssu == '') || ($ghda == '') )
        {
            $act = 'gact';
        }
    }

    if ($act == 'udon')
    {
      if ((!$urpt) && (($uadr == '') || ($uadr == constNoUVNC)))
        {
            $act = 'uact';
        }
    }

    if (($act == 'host') && ($scop == constScopHost) && ($revl))
    {
        $meth = find_meth($env,$db);
        switch ($meth)
        {
            case constMethVoid: $act = 'meth';  break;
            case constMethNone: $act = 'meth';  break;
            case constMethGoto: $act = 'gcnf';  break;
            case constMethUVNC: $act = 'ucnf';  break;
            case constMethBoth: $act = 'warn';  break;
            default           : $act = 'meth';  break;
        }
    }
    if (matchOld($act,'|||scop|host|'))
    {
        $act = $map;
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
    if ((!$host) && ($env['host']))
    {
        $site = $env['host']['site'];
        $host = $env['host']['host'];
    }
    if (!$cnfg)
    {
        if (matchOld($act,'|||udon|uact|gdon|gact|kill|rset|'))
        {
            $act = 'deny';
        }
    }

    $name = REMT_Title($act,$site,$host,$env,$db);
    $msg  = ob_get_contents();
    ob_end_clean();
    /* For some reason, IE likes to cache this page, and that is almost
       never the right thing to do since certain invocations of the page
       change content based on the database.  So, disable caching always. */
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');

    if ($rest)
    {
        echo restricted_html_header($name,$comp,$auth,$db);
    }
    else
    {
        echo standard_html_header($name,$comp,$auth,'','',0,$db);
    }

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    debug_array($debug,$_POST);

    db_change($GLOBALS['PREFIX'].'core',$db);
    echo REMT_Again($env);
    switch ($act)
    {
        case 'csit': choose_site($env,$db);  break;
        case 'chst': choose_host($env,$db);  break;
        case 'ctid': CWIZ_ChooseCats($env, "Which category would you like to "
            . "configure remote control access?", $db);  break;
        case 'cgid': CWIZ_ChooseGrps($env, "At which group would you like to "
            . "configure remote control access?", $db);  break;
        case 'scop':
            CWIZ_ChooseScop($env,"Where would you like to configure "
                . "remote control access?", $db);
            break;
        case 'meth': choose_meth($env,$db);  break;
        case 'warn': config_warn($env,$db);  break;
        case 'kill': config_kill($env,$db);  break;
        case 'none': config_none($env,$db);  break;
        case 'goto': setup_goto($env,$db);   break;
        case 'uvnc': setup_uvnc($env,$db);   break;
        case 'gact': ;
        case 'gdon': done_goto($env,$db);    break;
        case 'uact': ;
        case 'udon': done_uvnc($env,$db);    break;
        case 'gcon': conn_goto($env,$db);    break;
        case 'ucon': conn_uvnc($env,$db);    break;
        case 'gcnf': conf_goto($env,$db);    break;
        case 'ucnf': conf_uvnc($env,$db);    break;
        case 'rset': reset_code($env,$db);   break;
        case 'deny': deny($env,$db);         break;
        default    : whatever($env,$db);     break;
    }
    echo REMT_Again($env);
    echo head_standard_html_footer($auth,$db);
?>
