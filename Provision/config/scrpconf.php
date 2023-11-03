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
14-Nov-05   BJS     Copied from intruder.php.
18-Nov-05   BJS     Added ability to configure a scrip for a group.
06-Dec-05   BJS     All group no longer hardwired to 1, call
                    GRPS_ReturnAllMgroupid() instead.
07-Dec-05   BJS     Fixed Formating.
08-Dec-05   BJS     Fixed conditional statement on checkbox value, all
                    GRPS_get_XXXXXX return -1 instead of false.
                    Added support for group wizard.
12-Dec-05   BJS     Added itype support when calling GCFG_SetVariableValue().
                    Pass itype to each page.
13-Dec-05   BJS     Added 'back' buttons.
14-Dec-05   BJS     No back buttons for dangerous variable config.
                    Removed extraneous defines.
29-Dec-05   BJS     Added group name to confirmation link.
06-Jan-06   BJS     Added l-syst.php
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
11-Apr-06   BTE     Part of bug 3240: implemented the regular group Scrip
                    configuration page.
13-Apr-06   BTE     Lots of bugfixes for 3240: regular group Scrip config page.
14-Apr-06   BTE     Bug 3240: Implement full group Scrip configuration page.
17-Apr-06   BTE     Bug 3202: Group management server issues.  Also fixed a bug
                    where the user could not configure variables for an user-
                    defined group.
19-Apr-06   BTE     Bug 3204: Assorted text changes for group management.
20-Apr-06   BTE     Bug 3285: User interface group management issues from
                    emails.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
03-May-06   BTE     Bug 3359: Add links to the status page.
06-May-06   BTE     Bug 3282: Need to add warning for configuration of
                    variables in All group.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.  Bug 3486: Group Scrip Configurator: Traceback for
                    dangerous variables.
26-Aug-06   BTE     Fixed a bug where Scrip configuration will cease to work
                    when the All group is rebuilt.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.
04-Dec-06   BTE     Spelling fix for Alex.

*/

  /*
   | scrpconf.php by default calls into the wu-confg.php/l-grpw.php
   | state machine. We do this because we want to utilize the code
   | that creates the groups, to allow a user greater flexibility
   | and an overall consistency in the UI.
   | Once the user has selected a single site, All site, or a single
   | machine, control is handed back to scrpconf.php.
   | If the user is selecting a group of machines, they are given the
   | same ability to edit, user, delete, create that they would have
   | if they entered from tools:groups. Once they select 'use' on a
   | group, we return control to scrpconf.php.
   | scrpconf.php is responsbile for fetching & displaying all
   | applicable scrips, and allowing the user to select one,
   | then fetching and displaying its variables/values. The user may
   | edit values for the given mgroupid (site, all, group, machine)
   | and save those results.
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
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-upar.php' );
include_once ( '../lib/l-upnt.php' );
include_once ( '../lib/l-uprp.php' );
include_once ( '../lib/l-gdrt.php' );
include_once ( '../lib/l-cmth.php' );
include_once ( '../lib/l-slct.php' );
include_once ( '../lib/l-syst.php' );
include_once ( '../lib/l-core.php' );
include_once ( '../lib/l-tiny.php' );
include_once ( 'local.php'         );

//    define('constButtonNxt',  'Next &gt;');
    define('constGroupAll', 'All');

    define('constEnabNone',  0);
    define('constEnabVoid',  1);
    define('constEnabWarn',  2);
    define('constEnabNo',    3);
    define('constEnabYes',   4);

    define('constCncl', 'cncl');
    define('constBoth', 'both');
    define('constYes',  'yes');

    define('constVersionSelectName',    'version_select');


    function SCRPCONF_title($act,$site,$host,$grpName,$scripNum,$varid,$env,
        $db)
    {
        $m = 'Scrip Configuration';
        $scripName = '';
        if($scripNum)
        {
            $sql = "SELECT name FROM Scrips WHERE num=$scripNum ORDER BY "
                . "vers DESC LIMIT 1";
            $row = find_one($sql,$db);
            if($row)
            {
                $scripName = $row['name'];
            }
        }
        if($varid)
        {
            $sql = "SELECT descval FROM Variables LEFT JOIN VarVersions ON ("
                . "Variables.varuniq=VarVersions.varuniq) WHERE varid=$varid"
                . " ORDER BY vers DESC LIMIT 1";
            $row = find_one($sql,$db);
            if($row)
            {
                $varName = $row['descval'];
            }
        }

        switch ($act)
        {
            case 'deny': return "$m - No Access";
            case 'csit': return "$m - Select Site";
            case 'chst': return "$m - Site \"" . $env['site'] . "\" - Select "
                . "Machine";
            case 'kill': ;
            case 'enab':
            case 'msel':
                switch($env['scop'])
                {
                    case constScopSite:
                        return "$m - Select Scrip - Site \"$grpName\"";
                    case constScopHost:
                        $sql = "SELECT Census.host FROM Census WHERE "
                            . "Census.id=" . $env['hid'];
                        $row = find_one($sql, $db);
                        if($row)
                        {
                            return "$m - Select Scrip - Machine \""
                                . $row['host'] . "\"";
                        }
                        else
                        {
                            return "$m - Select Scrip";
                        }
                    case constScopUser:
                        $sql = "SELECT name FROM MachineGroups WHERE mgroupid="
                            . $env['mgroupid'];
                        $row = find_one($sql, $db);
                        if($row)
                        {
                            return "$m - Select Scrip - Group \""
                                . $row['name'] . "\"";
                        }
                        else
                        {
                            return "$m - Select Scrip";
                        }
                    default:
                        return "$m - Select Scrip";
                }
            case 'scop': return $m;
            case 'done': return "$m - Finished Configuration";
            case 'rset': return "$m - Restore Defaults";
            case 'warn': return "$m - Advanced Interface - $site - $host";
            case 'prmt': ;
            case 'cfrm': ;
            case 'cdng':
                return "$m Change Confirmation - Group \"$grpName\" for "
                    . "Scrip $scripNum - $scripName";
            case 'scrp':
                return "$m - Group \"$grpName\" for Scrip $scripNum - "
                    . "$scripName";
            case 'lall':
                return "Scrip $scripNum - $scripName - $varName - Values for "
                    . "all systems in group \"$grpName\"";
            case 'lval':
                return "Scrip $scripNum - $scripName - $varName - Unique "
                    . "values in group \"$grpName\"";
            case 'wapp':
                if($grpName)
                {
                    return "$m - Group \"$grpName\"";
                }
                else
                {
                    if($env['dgid'])
                    {
                        $sql = "SELECT MachineGroups.name FROM MachineGroups "
                            . "WHERE mgroupid=" . $env['dgid'];
                        $row = find_one($sql, $db);
                        if($row)
                        {
                            return "$m - Group \"" . $row['name']
                                . "\" - Edit a Group";
                        }
                        else
                        {
                            return "$m - Edit a Group";
                        }
                    }
                    if(@$env['gid']==-1)
                    {
                        return "$m - Add a Group";
                    }
                    return "$m - Select a Group";
                }
                /* Otherwise use the default */
            default    : return $m;
        }
    }



    function SCRPCONF_again(&$env)
    {
        $self= $env['self'];
        $dbg = $env['priv'];
        $cmd = "$self?act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link('../acct/census.php','census');
        if ($dbg)
        {
            $args = $env['args'];
            $href = ($args)? "$self?$args" : $self;
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

    function next_cancel($type)
    {
        $nxt = '';
        $can = '';
        switch($type)
        {
            case constNext : $nxt = button(constButtonNxt); break;
            case constCncl : $can = button(constButtonCan); break;
            case constBoth : $nxt = button(constButtonNxt);
                             $can = button(constButtonCan); break;
        }
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


    function choose_site(&$env,$db)
    {

        $auth = $env['auth'];
        $cid  = $env['cid'];
        $qu  = safe_addslashes($env['auth']);
        $sql = "select U.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
             . " ".$GLOBALS['PREFIX']."core.VarValues as X\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = G.name\n"
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
            echo hidden('level',$env['level']);
            echo para('At which site would you like to configure scrips?');
            reset($set);
            foreach ($set as $key => $row)
            {
                $id   = $row['id'];
                $site = $row['customer'];
                $rad  = radio('cid',$id,$cid);
                echo "${in}$rad $site<br>\n";
            }
            $nxt = next_cancel(constNext);
            $can = cancel_link(constPageEntryScrpConf, $env);
            $bck = back_link(constPageEntryScrpConf, $env);
            echo '<br>';
            GRPW_display_buttons($bck, $nxt, $can);
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
            $sql = "select C.id, C.host from\n"
                 . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
                 . " ".$GLOBALS['PREFIX']."core.ValueMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U\n"
                 . " where U.id = $cid\n"
                 . " and U.customer = C.site\n"
                 . " and U.username = '$qu'\n"
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
            echo hidden('act','selm');
            echo hidden('pcn','scop');
            echo hidden('cid', $env['cid']);
            echo hidden('scop',$env['scop']);
            echo hidden('level',$env['level']);
            echo para('At which machine would you like to configure scrips?');
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
            echo hidden('act','wapp');
            echo hidden('custom',constPageEntryScrpConf);
            echo hidden('pcn','scop');
            echo hidden('scop',0);
            echo hidden('level',$env['level']);
            echo para('There aren\'t any appropriate machines at this site.');
        }
        $nxt = next_cancel(constNext);
        $can = cancel_link(constPageEntryScrpConf, $env);
        $bck = back_link(constPageEntryScrpConf, $env);
        echo '<br>';
        GRPW_display_buttons($bck, $nxt, $can);
        echo form_footer();
    }


   /*
    | $mstr = an array indexed by mgroupids with value name
    | Displays a radio button for each mgroupid/name entry
    | in the array.
   */
    function build_radio_list($mstr)
    {
        reset($mstr);
        foreach ($mstr as $mgroupid => $name)
        {
            $radio_tmp = radio('mgroupid', $mgroupid, '');
            echo $radio_tmp . " $name <br>";
        }
    }


    function choose_mach($env, $db)
    {
        debug_note('choose_mach');
        $auth = $env['auth'];
        $grps = build_group_list($auth, constQueryRestrict, $db);
        $mstr = prep_for_multiple_select($grps);

        echo post_self('myform');
        echo hidden('act','machine_selected');
        echo hidden('scop',$env['scop']);
        echo hidden('level',$env['level']);

        echo "<p>At which group would you like to configure scrips?</p>";
        build_radio_list($mstr);
        $nxt = next_cancel(constNext);
        $can = cancel_link(constPageEntryScrpConf, $env);
        echo '<br>';
        GRPW_display_buttons($nxt, $can);
        echo form_footer();
    }


   /*
    | $env = global variables
    | $db  = database handle
    | We have completed the confirmation messages, and
    | will now update the scrip variables.
   */
    function completed($env, $db)
    {
        $now       = $env['now'];
        $host      = $env['serv'];
        $mgroupid  = $env['mgroupid'];
        $mcatid    = $env['mcatid'];
        $scrp_vars = $env['scrp_vars'];
        $env['scrip_updates'] = true;

        $census_id = GRPS_return_censusid_from_mcatid_mgroupid($mgroupid,
                                                               $mcatid, $db);
        /* Update each scrip variable */
        reset($scrp_vars);
        foreach ($scrp_vars as $Sn => $entry)
        {
            $post_valu  = $entry['post_valu'];
            $varid      = $entry['varid'];
            $itype      = $entry['itype'];

            $res = GCFG_SetVariableValue($post_valu, $varid, $itype, $mgroupid,
                $census_id, $env['level'] ? constSourceScripGroupAdvConfig :
                constSourceScripGroupConfig, $env['now'], $db);
            if ($res == constSetVarValFailure)
            {
                $env['scrip_updates'] = false;
            }
        }
        wiz_done($env, $scrp_vars);
        return 1;
    }


    function confrm_cdng($env, $db)
    {
        debug_note('confrm_cdng()');

        $scrp_vars = $env['scrp_vars'];
        $mgroupid  = $env['mgroupid'];
        $mcatid    = $env['mcatid'];
        $grpn      = $env['group_name'];
        $snum      = $env['snum'];
        $c         = 0;
        $act       = 'comp';
        $confirm_textbox = textbox('confirm_text', 3, '');

        $env['vers'] = get_string('vers', '');
        if(strcmp($env['vers'], '')==0)
        {
            $env['vers'] = SCRPCONF_ChooseVersion($mgroupid, $db);
        }

        echo post_self('myform');
        echo hidden('act',        'comp');
        echo hidden('mgroupid',   $mgroupid);
        echo hidden('mcatid',     $mcatid);
        echo hidden('snum',       $snum);
        echo hidden('group_name', $grpn);
        echo hidden('scop',       $env['scop']);
        echo hidden('level',      $env['level']);
        echo hidden('vers',       $env['vers']);

        $sql = "SELECT mgroupuniq FROM MachineGroups WHERE mgroupid=$mgroupid";
        $row = find_one($sql, $db);
        if($row)
        {
            $err = PHP_SCNF_ProcessConfigVars(CUR, $html,
                $GLOBALS["HTTP_RAW_POST_DATA"], constPageTypeConfirm2,
                $env['level'] ? CFGFRMT_SRVGROUPADV : CFGFRMT_SRVGROUP,
                NULL, $row['mgroupuniq'],
                $env['level'] ? $env['vers'] : NULL, $env['auth'],
                $env['level'] ? constSourceScripGroupAdvConfig :
                constSourceScripGroupConfig);

            if($err!=constAppNoErr)
            {
                echo "An error has occurred processing this page.  See ";
                echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.";
            }
            else
            {
                echo $html;
            }
        }
        else
        {
            echo "Group not found.";
        }

        echo '<p>';
        echo form_footer();
        echo SCRPCONF_BottomLinks($env, $db);
    }


    function config_prmt($env, $db)
    {
        debug_note('config_prmt()');

        $now       = $env['now'];
        $host      = $env['serv'];
        $mgroupid  = $env['mgroupid'];
        $mcatid    = $env['mcatid'];
        $s_num     = $env['snum'];
        $grpn      = $env['group_name'];
        $env['vers'] = get_string('vers', '');
        if(strcmp($env['vers'], '')==0)
        {
            $env['vers'] = SCRPCONF_ChooseVersion($mgroupid, $db);
        }

        $updated_scrp_vars = array();
        $updated_scrp_dngr = array();
        $updated_scrp_sema = array();
        $update_msg        = '';
        $scrp_vars         = '';
        $conf_txt          = '';
        $c    = 0;
        $good = true;
        $showbuttons = 1;

        $vers_set = get_string('version_set', '');

        $sql = "SELECT mgroupuniq FROM MachineGroups WHERE mgroupid=$mgroupid";
        $row = find_one($sql, $db);
        if($row)
        {
            $err = constAppNoErr;

            /* DO NOT ALLOW variable pollution by parsing if someone just
                changed the version! */
            if(!($vers_set))
            {
                $err = PHP_SCNF_ProcessConfigVars(CUR, $html,
                    $GLOBALS["HTTP_RAW_POST_DATA"], constPageTypeScripConfig,
                    $env['level'] ? CFGFRMT_SRVGROUPADV : CFGFRMT_SRVGROUP,
                    NULL, $row['mgroupuniq'],
                    $env['level'] ? $env['vers'] : NULL, $env['auth'],
                    $env['level'] ? constSourceScripGroupAdvConfig :
                    constSourceScripGroupConfig);

                if($err!=constAppNoErr)
                {
                    echo "An error has occurred processing this page.  See ";
                    echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.";
                }
            }
            else
            {
                /* Someone clicked change version so state that nothing
                    happened and display the configuration */
                $html= '';
            }
            if($err==constAppNoErr)
            {
                if(strlen($html)==0)
                {
                    /* This indicates "Next" was clicked without making any
                        changes or the "Add" button was pushed.  Send the user
                        back. */
                    $env['act'] = 'scrp';
                    config_scrp($env, $db);
                }
                else
                {
                    echo post_self('myform');
                    echo '<br>';
                    echo hidden('act',        'cfrm');
                    echo hidden('mgroupid',   $mgroupid);
                    echo hidden('mcatid',     $mcatid);
                    echo hidden('snum',       $s_num);
                    echo hidden('group_name', $grpn);
                    echo hidden('scop',       $env['scop']);
                    echo hidden('level',      $env['level']);
                    echo hidden('vers',       $env['vers']);
                    echo $html;
                    echo '<p>';
                    echo form_footer();

                    echo SCRPCONF_BottomLinks($env, $db);
                }
            }
        }
        else
        {
            echo "Group not found.";
        }


    }


    function config_cfrm($env, $db)
    {
        debug_note('config_cfrm()');

        $now       = $env['now'];
        $host      = $env['serv'];
        $mgroupid  = $env['mgroupid'];
        $mcatid    = $env['mcatid'];
        $s_num     = $env['snum'];
        $grpn      = $env['group_name'];

        $updated_scrp_vars = array();
        $updated_scrp_dngr = array();
        $updated_scrp_sema = array();
        $update_msg        = '';
        $scrp_vars         = '';
        $conf_txt          = '';
        $c    = 0;
        $good = true;

        $env['vers'] = get_string('vers', '');
        if(strcmp($env['vers'], '')==0)
        {
            $env['vers'] = SCRPCONF_ChooseVersion($mgroupid, $db);
        }

        echo post_self('myform');
        echo hidden('act',        'cdng');
        echo hidden('mgroupid',   $mgroupid);
        echo hidden('mcatid',     $mcatid);
        echo hidden('snum',       $s_num);
        echo hidden('group_name', $grpn);
        echo hidden('scop',       $env['scop']);
        echo hidden('level',      $env['level']);
        echo hidden('vers',       $env['vers']);

        $sql = "SELECT mgroupuniq FROM MachineGroups WHERE mgroupid=$mgroupid";
        $row = find_one($sql, $db);
        if($row)
        {
            $err = PHP_SCNF_ProcessConfigVars(CUR, $html,
                $GLOBALS["HTTP_RAW_POST_DATA"], constPageTypeConfirm1,
                $env['level'] ? CFGFRMT_SRVGROUPADV : CFGFRMT_SRVGROUP,
                NULL, $row['mgroupuniq'],
                $env['level'] ? $env['vers'] : NULL, $env['auth'],
                $env['level'] ? constSourceScripGroupAdvConfig :
                constSourceScripGroupConfig);

            if($err!=constAppNoErr)
            {
                echo "An error has occurred processing this page.  See ";
                echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.";
            }
            else
            {
                echo $html;
            }
        }
        else
        {
            echo "Group not found.";
        }

        echo '<p>';
        echo form_footer();
        echo SCRPCONF_BottomLinks($env, $db);
    }


   /*
    | $env = global variables
    | $db  = database handle
    |
    | config_scrip is used after the user selects an mgroupid
    | and a scrip. We load all the variables for the selected
    | clients for the selected scrip.
   */
    function config_scrp(&$env, $db)
    {
        debug_note('config_scrip()');

        $censusid = $env['censusid'];
        $mgroupid = $env['mgroupid'];
        $mcatid   = $env['mcatid'];
        $s_num    = $env['snum'];
        $grpn     = $env['group_name'];
        $cid      = $env['cid'];
        $pcid     = $env['prev_cid'];

        echo post_self('myform');
        $nxt = next_cancel(constNext);
        $can = cancel_link(constPageEntryScrpConf, $env);
        $bck = back_link(constPageEntryScrpConf, $env);
        echo '<br>';
        GRPW_display_buttons($bck, $nxt, $can);
        echo "<p>";
        $vers = get_string('version_select', '');
        $versSet = get_string('version_set', '');
        if($versSet)
        {
            /* Someone clicked change version */
            $env['vers'] = $vers;
        }
        if(@$env['level']==1)
        {
            echo SCRPCONF_VersionSelect($env['mgroupid'], $env['vers'], $db);
        }
        echo hidden('act',        'prmt');
        echo hidden('mgroupid',   $mgroupid);
        echo hidden('mcatid',     $mcatid);
        echo hidden('snum',       $s_num);
        echo hidden('group_name', $grpn);
        echo hidden('cid',        $cid);
        echo hidden('censusid',   $censusid);
        echo hidden('prev_cid',   $pcid);
        echo hidden('scop',       $env['scop']);
        echo hidden('prev_scop',  $env['prev_scop']);
        echo hidden('prev_hid',   $env['prev_hid']);
        echo hidden('level',      $env['level']);

        if(strcmp($env['vers'], '')==0)
        {
            $env['vers'] = SCRPCONF_ChooseVersion($mgroupid, $db);
        }
        echo hidden('vers',       $env['vers']);

        $sql = "SELECT mgroupuniq FROM MachineGroups WHERE mgroupid=$mgroupid";

        $row = find_one($sql, $db);
        if($row)
        {
            SCRPCONF_GetWarning($env['prev_scop']);
            $err = PHP_SCNF_MakeHtmlScripConfig(CUR, $html, $s_num,
                $env['level'] ? CFGFRMT_SRVGROUPADV : CFGFRMT_SRVGROUP,
                NULL, $row['mgroupuniq'],
                $env['level'] ? $env['vers'] : NULL);

            switch($err)
            {
            case constAppNoErr:
                echo $html;
                break;
            case constErrNoConfigVars:
                /* Someone is trying to view a Scrip that doesn't exist. */
                echo "This Scrip is not available in " . $env['vers'];
                break;
            default:
                echo "An error has occurred processing this page.  See ";
                echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.";
                break;
            }
        }
        else
        {
            echo "Group not found.";
        }
        $nxt = next_cancel(constNext);
        $can = cancel_link(constPageEntryScrpConf, $env);
        $bck = back_link(constPageEntryScrpConf, $env);
        echo '<p>';
        GRPW_display_buttons($bck, $nxt, $can);
        echo form_footer();
        echo SCRPCONF_BottomLinks($env, $db);
    }


   /*
    | $env = global array
    | $db  = database handle
    |
    | Called when the user selects All, a single site,
    | single machine or a user defined group. Get the
    | mgroupid, mcatid and group name. Then we get all
    | the censusids and clientversions used in the group.
    | We then build a table listing all the Scrip name/nums
    | included in the applicable groups which the user can
    | select to configure.
   */
    function config_mach($env, $db)
    {
        debug_note('config_mach()');
        //print_r($env);

        $set  = false;
        $self = $env['self'];
        $hid  = $env['hid'];
        $cid  = $env['cid'];
        $scop = $env['scop'];
       /*
        | Get the mgroupid, mcatid & censusid for the group the user selected
       */
        switch ($env['scop'])
        {
            case constScopAll  :
                $mgroupid = GRPS_ReturnAllMgroupid($db);
                $mcatid   = return_mcatid($mgroupid, $db);
                $grp_name = constGroupAll;
            break;
            case constScopSite :
                $mgroupid = $env['sgrp']['mgroupid'];
                $mcatid   = $env['sgrp']['mcatid'];
                $grp_name = $env['sgrp']['name'];
            break;
            case constScopHost :
                $mgroupid = $env['hgrp']['mgroupid'];
                $mcatid   = $env['hgrp']['mcatid'];
                $grp_name = $env['hgrp']['name'];
            break;
            case constScopUser :
                $mgroupid = $env['mgroupid'];
                $mcatid   = return_mcatid($mgroupid, $db);
                $grp_name = return_mgroup_name($mgroupid, $db);
            break;
            default :
                $mgroupid = $env['mgroupid'];
                $mcatid   = return_mcatid($mgroupid, $db);
                $grp_name = return_mgroup_name($mgroupid, $db);
            break;
        }

        /* Use the mcatid and mgroupid to get a list of all the census ids */
        $censusid = GRPS_return_censusid_from_mcatid_mgroupid($mgroupid,
                                                              $mcatid, $db);
        if (empty($censusid))
        {
            logs::log(__FILE__, __LINE__, 'scrpconf.php: config_mach() censusid is empty', 0);
        }
        else
        {
            /* Use the censusids to get a list of all the client versions */
            $client_v = GRPS_return_client_versions($censusid, $db);

           /*
            | Use the clientversions to populate the temp table with applicable
            | scrip names and numbers.
           */
            $set = GRPS_build_version_dependent_scrip_list($client_v, $db);
        }

        /* in case we error out, the user wants to
         | click cancel so we begin the form here.
        */
        echo post_self('myform');

        if ($set)
        {
            $h_link = html_link('../doc/index.php', 'help');
            echo "Click on a Scrip name to configure it.<br>"
               . "Click on the '${h_link}' link for important notes on"
               . " configuring Scrips.<br><br>";

            $can = cancel_link(constPageEntryScrpConf, $env);
            $bck = back_link(constPageEntryScrpConf, $env);
            GRPW_display_buttons($bck, '', $can);

            echo table_header();
            echo "<tr><td colspan=2><center><b>$grp_name</b> Scrip list"
               . "</center></td></tr>";

            reset($set);
            foreach ($set as $i => $db_entry)
            {
                $scrip_num  = $db_entry['scrip_num'];
                $scrip_name = $db_entry['scrip_name'];

                $href = "$self?act=scrp&snum=$scrip_num&mgroupid=$mgroupid"
                      . "&mcatid=$mcatid"
                      . "&prev_hid=$hid"
                      . "&prev_cid=$cid&prev_scop=$scop"
                      . "&group_name=$grp_name";
                if($env['level']==1)
                {
                    $href .= "&level=1";
                }
                $link = html_link($href, $scrip_name);
                $args = array($scrip_num, $link);
                echo table_data($args, 0);
            }
            echo table_footer();
            echo '<br><br>';
            GRPW_display_buttons($bck, '', $can);
        }
        else
        {
            echo "An error has occurred accessing the variables for group"
               . " $grp_name.";

            $user = $env['auth'];
            $err  = 'scrpconf.php: config_mach() empty set for mgroupid'
                  . "($mgroupid) mcatid($mcatid) group($grp_name) user($user)";
            logs::log(__FILE__, __LINE__, $err, 0);
        }
        echo form_footer();

        /*
        $dbgtxt = "mgroupid  = $mgroupid |"
                . " mcatid   = $mcatid   |"
                . " censusid = $censusid |"
                . " client_v = $client_v";
        debug_note($dbgtxt);
        */

    }


    function wiz_done(&$env, $updated_scrp)
    {
        $self = $env['self'] . '?custom=' . constPageEntryScrpConf;
        $gnam = $env['group_name'];

        $xurl = 'mgroupid=' . $env['mgroupid'];
        $href = '../config/index.php?act=wiz';
        $xhrf = "../config/chckconf.php?$xurl";

        $conf = html_link($self,'Configure more Scrips');
        $cens = html_link($href,'Return to the Configuration Wizards page');
        $exam = html_link($xhrf,"Group '$gnam' Machines");

        $msg  = SCRPCONF_build_confirmation_message($env, $updated_scrp);
        echo <<< DONE

        <p>
          $msg
        </p>

        <ul>
           <li>$conf</li>
           <li>$cens</li>
           <li>$exam</li>
        </ul>

DONE;

    }


   /*
    | $env = global array
    | $updated_scrps = array of all the scrips we updated,
    | containing the values:
    | [DefaultScripTimeout] => Array
    |    (
    |        [valu] => 43531
    |        [varid] => 579
    |        [itype] => 0
    |        [dval] => Scrip timeout when unspecified (seconds)
    |        [defvl] => 43530
    |    )
   */
    function SCRPCONF_build_confirmation_message($env, $updated_scrps)
    {
        $msg     = '';
        $group   = $env['group_name'];
        $updates = $env['scrip_updates'];

        if (!$updates)
        {
            return "No variable changes applied to $group.";
        }
        else
        {
            reset($updated_scrps);
            foreach ($updated_scrps as $var_name => $db_entry)
            {
                $itype = $db_entry['itype'];
                $dval  = $db_entry['dval'];
                $defvl = $db_entry['defvl'];
                $dangr = @ $db_entry['dangerous'];
                if ($dangr)
                {
                    $valu = $db_entry['post_valu'];
                }
                else
                {
                    $valu  = $db_entry['valu'];
                }
                switch ($itype)
                {
                    case constVblTypeBoolean :
                        $valu  = ($valu)?  'checked' : 'unchecked';
                        $defvl = ($defvl)? 'checked' : 'unchecked';
                    break;
                    case constVblTypeSemaphore :
                        $valu  = 'execute now';
                        $defvl = 'waiting';
                    break;
                }
                $msg .= "'$dval' set from $defvl to $valu for $group.<br>";
            }
            return $msg;
        }
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
          have been used to configure Scrips
          settings for these machines, and those settings
          are incompatible with the operation of this wizard.
        </p>

WARN;
        choose_enab($env,$db);
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


    /* ListVariables

        Returns a list of all machines in the group $env['mgroupid'],
        the current assignment (ValueMap mgroupuniq) for the variable
        $env['varid'] on that machine, and the value of the variable in
        the group indicated by ValueMap mgroupuniq.

        If $listmachines is 0, will omit the site/machine name output from
        the results and will return only distinct rows.
    */
    function ListVariables(&$env,$db,$listmachines)
    {
        $mgroupuniq = $env['mgroupuniq'];
        $scrip = $env['snum'];
        $varid = $env['varid'];

        $sql = "SELECT Revisions.vers FROM MachineGroupMap LEFT JOIN Census "
            . "ON (MachineGroupMap.censusuniq=Census.censusuniq) LEFT JOIN "
            . "Revisions ON (Census.id=Revisions.censusid) LEFT JOIN "
            . "MachineGroups ON (MachineGroupMap.mgroupuniq=MachineGroups."
            . "mgroupuniq) WHERE MachineGroups.mgroupuniq = '" . $mgroupuniq
            . "' ORDER BY Revisions.vers DESC LIMIT 1";
        $row = find_one($sql, $db);
        if($row)
        {
            $sql = "SELECT ";
            if($listmachines)
            {
                $sql .= "Census.site, Census.host, ";
            }
            else
            {
                $sql .= "DISTINCT MachineGroups.name, ";
            }
            $sql .= "Variables.itype, "
                . "VarValues.valu, VarValues.def, VarVersions.defval FROM "
                . "MachineGroupMap LEFT JOIN Census ON ("
                . "MachineGroupMap.censusuniq=Census.censusuniq) LEFT JOIN "
                . "ValueMap ON (Census.censusuniq=ValueMap.censusuniq) LEFT "
                . "JOIN MachineGroups ON (ValueMap.mgroupuniq="
                . "MachineGroups.mgroupuniq) LEFT JOIN MachineCategories ON ("
                . "MachineGroups.mcatuniq=MachineCategories.mcatuniq) LEFT "
                . "JOIN Variables ON (ValueMap.varuniq=Variables.varuniq) "
                . "LEFT JOIN VarVersions ON (Variables.varuniq=VarVersions."
                . "varuniq) LEFT JOIN VarValues ON (Variables.varuniq="
                . "VarValues.varuniq AND ValueMap.mgroupuniq=VarValues."
                . "mgroupuniq) WHERE VarVersions.vers='" . $row['vers']
                . "' AND MachineGroupMap.mgroupuniq='" . $mgroupuniq
                . "' AND Variables.varid=$varid";
            if($listmachines)
            {
                $sql .=  " ORDER BY site, host";
            }
            $set = find_many($sql, $db);

            if($set)
            {
                echo "<table border=1><tr>";
                if($listmachines)
                {
                    echo "<th>Site</th><th>Machine</th>";
                }
                else
                {
                    echo "<th>Source group</th>";
                }
                echo "<th>Value</th></tr>";
                reset($set);
                foreach ($set as $key => $row)
                {
                    echo "<tr>";
                    if($listmachines)
                    {
                        echo "<td>" . $row['site'] . "</td><td>" . $row['host']
                            . "</td>";
                    }
                    else
                    {
                        echo "<td>" . $row['name'] . "</td>";
                    }
                    echo "<td>";
                    if(strcmp($row['def'],'1')==0)
                    {
                        $valu = $row['defval'];
                    }
                    else
                    {
                        $valu = $row['valu'];
                    }

                    if($row['itype']==constVblTypeBoolean)
                    {
                        if(strcmp($valu, "0")==0)
                        {
                            echo "Disabled";
                        }
                        else
                        {
                            echo "Enabled";
                        }
                    }
                    else
                    {
                        echo $valu;
                    }
                    echo "</td></tr>";
                }
                echo "</table>";
            }
        }
    }

    function SCRPCONF_BottomLinks($env, $db)
    {
        $msg = "<p>What do you want to do?<p>";
        $msg .= "<ul>";

        $msg .= config_wiz_navigate($env, 'Site Wizards', "Configure Scrips"
            . " for other groups", "Configure other Scrips for group "
            . $env['group_name'], "Continue configuring this Scrip",
            "Go to the regular Scrip configurator for this machine", 0, 1,
            'Scrip Configuration Status Page', constLinkFormatList, $db);
        $msg .= "</ul><p>";
        return $msg;
    }

    function SCRPCONF_ChooseVersion($mgroupid, $db)
    {
        $sql = "SELECT Revisions.vers FROM Revisions LEFT JOIN Census ON ("
            . "Revisions.censusid=Census.id) LEFT JOIN MachineGroupMap ON ("
            . "Census.censusuniq=MachineGroupMap.censusuniq) LEFT JOIN "
            . "MachineGroups ON (MachineGroupMap.mgroupuniq=MachineGroups."
            . "mgroupuniq) WHERE MachineGroups.mgroupid=$mgroupid ORDER BY "
            . "Revisions.vers DESC LIMIT 1";
        $row = find_one($sql, $db);
        if($row)
        {
            return $row['vers'];
        }
        else
        {
            $sql = "SELECT Revisions.vers FROM Revisions ORDER BY Revisions"
                . ".vers DESC LIMIT 1";
            $row = find_one($sql, $db);
            if($row)
            {
                return $row['vers'];
            }
        }
        return '';
    }

    function SCRPCONF_VersionSelect($mgroupid, $vers, $db)
    {
        $retStr = "Current version: ";

        $sql = "SELECT DISTINCT vers FROM Revisions LEFT JOIN Census ON ("
            . "Revisions.censusid=Census.id) LEFT JOIN MachineGroupMap ON ("
            . "Census.censusuniq=MachineGroupMap.censusuniq) LEFT JOIN "
            . "MachineGroups ON (MachineGroupMap.mgroupuniq=MachineGroups."
            . "mgroupuniq) WHERE MachineGroups.mgroupid=$mgroupid ORDER BY "
            . "Revisions.vers DESC";
        $set = find_many($sql, $db);
        if($set)
        {
            $retStr .= "<select name=\"" . constVersionSelectName . "\">";
            foreach ($set as $key => $row)
            {
                if(strcmp($row['vers'], $vers)==0)
                {
                    $retStr .= "<option selected value=\"";
                }
                else
                {
                    $retStr .= "<option value=\"";
                }
                $retStr .= $row['vers'] . "\">" . $row['vers'] . "</option>";
            }
            $retStr .= "</select>&nbsp;&nbsp;&nbsp;&nbsp;<input "
                . "type=\"submit\" name=\"version_set\" "
                . "value=\"Change Version\">";
        }
        else
        {
            $retStr .= $vers;
        }
        $retStr .= "<p>";
        return $retStr;
    }

    function SCRPCONF_GetWarning($scop)
    {
        if($scop==constScopAll)
        {
            echo "<font face=\"verdana,helvetica\" size=\"+2\" color=\"red\">"
                . "The changes you are making will affect every machine, so "
                . "be careful.</font>";
        }
    }


   /*
    | Main program
   */

    $now   = time();
    $db    = db_connect();
    $auth  = process_login($db);
    $comp  = component_installed();

    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?  1 : 0;
    $debug = @ ($user['priv_debug'])?  1 : 0;
    $admin = @ ($user['priv_admin'])?  1 : 0;
    $cnfg  = @ ($user['priv_config'])? 1 : 0;

    $hid = get_integer('hid',0);
    $cid = get_integer('cid',0);

    //$act = get_string('act','scop');
    $act = get_string('act','wapp');
    $hlp = get_string('hlp','');
    $map = $act;

    $post = get_string('button','');
    $csid = get_string('censusid', 0);
    $ctxt = get_string('confirm_text', '');
    $grpn = get_string('group_name', '');
    $scop = get_integer('scop',constScopNone);
    $enab = get_integer('enab',constEnabNone);
    $mgrp = get_integer('mgroupid',  0);
    $mcat = get_integer('mcatid',    0);
    $snum = get_integer('snum',      0);
    $phid = get_integer('prev_hid',  0);
    $pscop = get_integer('prev_scop', 0);
    $pcid  = get_integer('prev_cid', 0);
    $custom = get_integer('custom',  0);
    $site   = get_string('site', '');
    $scrp_vars   = array();
    $scrp_vars_c = get_integer('scrp_vars_c', 0);
    if ($scrp_vars_c)
    {
       /*
        | Get all the posted scrip values: S0 thru SN in the form:
        | S{N}scrip_name
        | S{N}post_valu
        | S{N}dval
        | S{N}dangerous
        | S{N}varid
        | S{N}itype
        | in an array of variables to update.
        |
        | scrp_vars_c = the number of posted scrips.
       */
        $set = array();
        for($x=0; $x<$scrp_vars_c; $x++)
        {
            $Sn = 'S' . $x;

            $scrp_vars[$Sn]['scrip_name'] = get_string($Sn . 'scrip_name', '');
            $scrp_vars[$Sn]['post_valu']  = get_string($Sn . 'post_valu' , '');
            $scrp_vars[$Sn]['dval']       = get_string($Sn . 'dval'      , '');
            $scrp_vars[$Sn]['defvl']      = get_string($Sn . 'defvl'     , '');
            $scrp_vars[$Sn]['dangerous']  = get_integer($Sn . 'dangerous',  0);
            $scrp_vars[$Sn]['varid']      = get_integer($Sn . 'varid',      0);
            $scrp_vars[$Sn]['itype']      = get_integer($Sn . 'itype',      0);
        }
    }

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
    if (($revl) && (!$cid))
    {
        $cid = $revl['cid'];
    }
    $site = find_site($cid,$auth,$db);
    if (($revl) && (!$scop))
    {
        $scop = constScopHost;
    }
    if (($site) && (!$scop))
    {
        $scop = constScopSite;
    }

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

    if ( ($scop == constScopSite)
         && ($site == '')  && ($act == 'wapp') )
    {
       /* The user selected a single site,
        | now we want to list the sites
        | and let them choose one. We no
        | longer process this request inside
        | controlGroupState().
       */
        $act = 'csit';
    }

    if ($scop == constScopHost)
    {
        /* we start at wu-confg */
        if ($act == 'wapp')
        {
            $act = 'csit';
        }
        /* selected a site, but not a machine */
        else if ( ($site) && ($act != 'selm') )
        {
            $act = 'chst';
        }
        /* selected a site, and machine */
        else
        {
            $act = 'enab';
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

    /* user selected all machines */
    if ($scop == constScopAll)
    {
        $map = enab_action($enab);
        $act = 'enab';
    }

    if ($scop == constScopUser)
    {
        if ($act == 'machine_selected')
        {
            $act = 'msel';
        }
        else
        {
            $map = 'mach';
        }
    }

    if (!$scop)
    {
        $map = 'scop';
    }

    $hgrp = find_revl_mgrp($revl,$db);
    $sgrp = find_mgrp_cid($cid,$db);

    $env = array( );
    $env['hid']  = $hid;
    $env['cid']  = $cid;
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
    $env['serv'] = server_name($db);
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['priv'] = $priv;
    $env['censusid'] = $csid;
    $env['mgroupid'] = $mgrp;
    $env['mcatid']   = $mcat;
    $env['snum']     = $snum;
    $env['scrp_vars']   = $scrp_vars;
    $env['scrp_vars_c'] = $scrp_vars_c;
    $env['group_name']  = $grpn;

    $env['prev_hid']    = $phid;
    $env['prev_scop']   = $pscop;
    $env['prev_cid']    = $pcid;

    /* needed to enter l-grpw.php */
    $env['custom'] = $custom;
    $env['admn']   = $admin;
    $env['kid']    = get_integer('kid',  0);
    $env['jid']    = get_integer('jid',  0);
    $env['dtc']    = get_integer('dtc',  0);
    $env['ctl']    = get_integer('ctl',  0);
    $env['int']    = get_integer('int',  0);
    $env['tid']    = get_integer('tid',  0);
    $env['gid']    = get_integer('gid',  0);
    $env['sub']    = get_string('sub',  '');
    $env['post']   = get_string('button','');
    $env['dgid']   = get_integer('dgid', 0);
    $env['addgroup'] = get_integer('addgroup', 0);
    $env['delgroup'] = get_integer('delgroup', 0);
    $env['name']   = get_string('name',  '');
    $env['notification_id']  = get_integer('notification_id', 0);
    $env['report_id']        = get_integer('report_id', 0);
    $env['asset_id']         = get_integer('asset_id', 0);
    $env['notification_act'] = get_string('notification_act', '');
    $env['report_act']       = get_string('report_act', '');
    $env['asset_act']        = get_string('asset_act', '');
    $env['glob']             = get_integer('glob', 0);
    $env['debug'] = $debug;
    $env['varid']            = get_integer('varid', 0);
    $env['mgroupuniq']       = get_string('mgroupuniq', '');
    $env['level']            = get_integer('level', 0);
    $env['vers']             = get_string('vers', '');

    if(($env['mgroupuniq']) && !($env['group_name']))
    {
        /* Look up the group name */
        $sql = "SELECT name FROM MachineGroups WHERE mgroupuniq='"
            . $env['mgroupuniq'] . "'";
        $row = find_one($sql, $db);
        if($row)
        {
            $env['group_name'] = $row['name'];
            $grpn = $row['name'];
        }
    }
    if(($env['varid']) && !($env['snum']))
    {
        /* Look up the Scrip associated with this variable */
        $sql = "SELECT scop FROM Variables WHERE varid=" . $env['varid'];
        $row = find_one($sql, $db);
        if($row)
        {
            $env['snum'] = $row['scop'];
            $snum = $row['scop'];
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

    if ((!$cnfg) && ($act == 'done'))
    {
        $act = 'deny';
    }
    if ($act == 'comp')
    {
        if ($ctxt != constYes)
        {
            // if the user doesn't type in yes we bring them to the menu page
            $act = 'smnu';
            $env['scrip_updates'] = false;
        }
    }

    $name = SCRPCONF_title($act,$site,$host, $env['group_name'], $snum,
        $env['varid'], $env, $db);
    $local_nav = config_wiz_navigate($env, 'wizards', 'start', 'scrips',
        'continue', '', 1, 0, 'status', constLinkFormatNavBar, $db);

    $msg  = ob_get_contents();
    ob_end_clean();

    /* Disable browser caching */
    echo extended_html_header($name,$comp,$auth,$local_nav,'',0,1,$db);

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    debug_array($debug,$_POST);

    db_change($GLOBALS['PREFIX'].'core',$db);
    echo SCRPCONF_again($env);

    //print_r($env);
    //debug_note("switch_act($act)");

    switch ($act)
    {
        case 'csit': choose_site($env,$db);      break;
        case 'chst': choose_host($env,$db);      break;
        case 'enab': config_mach($env,$db);      break;
        case 'mach': choose_mach($env,$db);      break;
        case 'msel': config_mach($env,$db);      break;
        case 'scrp': config_scrp($env,$db);      break;
        case 'prmt': config_prmt($env,$db);      break;
        case 'cdng': confrm_cdng($env,$db);      break;
        case 'comp': completed($env,$db);        break;
        case 'warn': config_warn($env,$db);      break;
        case 'void': config_void($env,$db);      break;
        case 'deny': deny($env,$db);             break;
        case 'smnu': wiz_done($env,array());     break;
        case 'cfrm': config_cfrm($env,$db);      break;
        case 'lall': ListVariables($env, $db, 1);break;
        case 'lval': ListVariables($env, $db, 0);break;
        default  :   controlGroupState($act, $env, $db); break;
    }

    echo SCRPCONF_again($env);
    echo head_standard_html_footer($auth,$db);
?>
