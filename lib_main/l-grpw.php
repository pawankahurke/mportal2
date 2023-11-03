<?php






    define('constScopGroup',        5);
    define('constScopOS',           6);
    define('constScopOSSite',       7);
    define('constScopLocalNet',     8);
    define('constMcSCOP',           'Wiz_SCOP_MC');
    define('constOk',               'Ok');
    define('constNext',             'Next');
    define('constButtonNxt',        'Next &gt');

    define('constWizUpdateForm',        'wizform');
    define('constWizCheckboxUpdate',    'typechkupdate');
    define('constWizCheckboxSP',        'typechksp');
    define('constWizCheckboxRollup',    'typechkroll');
    define('constWizCheckboxSecurity',  'typechksecurity');
    define('constWizCheckboxCritical',  'typechkcritical');
    define('constWizClickOk',           'wizclickok');
    define('constWizCheckboxUpdateM',   'typechkupdatem');
    define('constWizCheckboxSPM',       'typechkspm');
    define('constWizCheckboxRollupM',   'typechkrollm');
    define('constWizCheckboxSecurityM', 'typechksecuritym');
    define('constWizCheckboxCriticalM', 'typechkcriticalm');

    define('constWizTypeUpdateStr',         'Generic Update');
    define('constWizMandatoryUpdateStr',    'Mandatory Generic Update');

    function check_change($old,$new)
    {
        if ($old != $new)
        {
            $txt = "<b>$old</b> was not unique, and has been changed to <b>$new</b>.";
            echo para($txt);
        }
    }


    function title($act,$cat,$grp)
    {
        $m  = 'Microsoft';
        $s  = 'Software';
        $u  = 'Update';
        $c  = 'Configuration';
        $w  = 'Wizard';
        $x  = "$w -";

        $mu = "$m $u";
        $mc = 'Machine Configuration';
        $msuc = "$m $s $u $c";
	$grp  = ($grp == 0)? '' : $grp;

        switch ($act)
        {
            case 'dwid': ;
            case 'dpid': ;
            case 'site': ;
            case 'host': ;
            case 'uwid': ;
            case 'wdef': ;
            case 'wprb': ;
            case 'menu': return "$mu - Configuration";
            case 'iwid': ;
            case 'fwid': return "$mu $mc";
            case 'cpwc': ;
            case 'dwid': ;
            case 'wpxa': ;
            case 'lwid': return "Machine Configurations for $mu";
            case 'upid': ;
            case 'wssa': ;
            case 'cppc': ;
            case 'lpid': return "$s $u Configurations for $m $u";
            case 'twid': return "Test $mu $mc";
            case 'tpid': return "Test $msuc";
            case 'epid': return "Edit $msuc";
            case 'ewid': return "Edit $mu $mc";
            case 'apid': return "Add $msuc";
            case 'cata': return "Add New Category for $s $u Groups";
            case 'catb': return "Confirm Add Update Group Category";
            case 'grpa': return "Add Update Group for $cat";
            case 'grpb': return "Confirm Add $u Group for $cat";
            case 'awid': return "Add New $mc for $mu";
            case 'cwid': return "Confirm Add $mu $mc";
            case 'cpid': return "Confirm Add $msuc";
            case 'pwic': return "$w Confirm Delete $mu $mc";;
            case 'pwid': return "Confirm Delete $mu $mc";
            case 'ppic': return "$w Confirm Delete $msuc";
            case 'ppid': return "Confirm Delete $msuc";
            case 'dpca': return "Confirm Delete $cat";
            case 'dpga': return "Confirm Delete $cat:$grp";
            case 'dpcb': return "Delete Update Category $cat";
            case 'cpga': return "Copy Update Group $grp";
            case 'upgs': ;
            case 'upge': ;
            case 'epgx': ;
            case 'epgd': ;
            case 'epgb': ;
            case 'cpgb': ;
            case 'pgrp': return "$s $u Groups in Category $cat";
            case 'gdet': return 'Machine Group Details';

            case 'epgc': return "Confirm Edit Update Group $cat:$grp";
            case 'epgf': return "Edit $s $u Group $grp in Category $cat";
            case 'epgs': return "Define Text Search for $cat:$grp";
            case 'epgp': ;
            case 'epge': return "Define Expression for Update Group $cat:$grp";
            case 'epgm': return "Select Updates for Update Group $cat:$grp";
            case 'vpga': return "Display $u Group $grp in Category $cat";

            case 'help': return "$x Help";
            case 'wemg': return "$x Edit Machine Group";
            case 'wiz' : return "$m $u Management - ${w}s";
            case 'mset': ;
            case 'meth': ;
            case 'wmth': return "$x Select Update Method";
            case 'pmth': return "$x Update Method";
            case 'shed': return "$x Edit Machine Schedule";
            case 'sent': return "$x Update Machine Schedule";
            case 'prpa': ;
            case 'prop': ;
            case 'wprp': return "$x Configure update download and propagation";
            case 'pprp': return "$x Update download and propagation";
            case 'dwic': ;
            case 'dpic': ;
            case 'wsts': return "$m $u $w Status";
            case 'wtst': ;
            case 'wbdo': ;
            case 'wbfm': ;
            case 'wbac': ;
            case 'wbet': return "$x Select Test Machines";
            case 'pdec': ;
            case 'qqqd': ;
            case 'wdec': return "$x Decline Updates";
            case 'prem': ;
            case 'qqqr': ;
            case 'wrss': ;
            case 'wrem': return "$x Remove Updates";
            case 'papp': ;
            case 'qqqa': ;
            case 'wrsh': ;
            case 'wapp': return "$x Approve Updates";
            case 'pcrt': ;
            case 'qqqc': ;
            case 'wcrn': ;
            case 'wcrt': return "$x Critical Update";

            case 'upga': return "Confirm $u Group $grp in Category $cat";

            case 'ptch': ;
            case 'hpid': ;
            case 'spid': ;
            case 'ipid': ;
            case 'fpid': return $msuc;
            case 'cpwa': return "Copy $mu Machine Configuration";
            case 'cpwb': return "Confirm Copy $mu Machine Configuration";
            case 'cppa': return "Copy $msuc";
            case 'cppb': return "Confirm Copy $msuc";
            case 'dpgb': ;
            case 'pcup': ;
            case 'pcdn': ;
            case 'catc': ;
            case 'grpc': ;
            case 'pcat': return "$s $u Groups";
            case 'pedn': ;
            case 'peup': ;
            case 'pexp': return "$s $u Group Details";
            case 'invd': return "Invalid $mu Action";
            case 'init': return "Initialize Update Groups";
            case 'dmap': return "Debug Patch Group Map";
            case 'dgrp': return 'View PatchGroups Table';
            case 'dpgp': return 'Delete PatchGroup Record';
            case 'dwfg': return "Debug WUConfig Table";
            case 'dcfg': return 'View PatchConfig Table';
            case 'ddpc': return 'Delete PatchConfig';
            case 'dhid': return "Debug Machine Patches";
            case 'dbet': return 'Debug Beta Groups';
            case 'dclr': return 'Clear Beta Groups';
            case 'ddmg': return 'Delete MachineGroup';
            case 'ddmp': return 'Delete All MachineGroupMap';
            case 'dmpg': return "Debug Machine Groups";
            case 'dclc': return "Recalculate WUConfig Cache";
            case 'dinp': return 'Invalidate PatchConfig Cache';
            case 'dinw': return 'Invalidate WUConfigCache Cache';
            case 'dvlp': return 'Validate PatchConfig Cache';
            case 'sane': return 'Database Consistancy Check';
            case 'xxxx': return "Debug Patch Blast";
            case 'dbug': return 'Debug Patch Menu';
            case 'tool': return "Group Management";
            case 'rprt': return "Event Report Group Management - ${w}s";
            case 'notf': return "Event Notification Group Management - ${w}s";
            case 'syte': return "Group Management - ${w}s";
            default    : return "$m $u Management - ${w}s";
        }
    }



    function GRPW_return_title($custom, $act, $cat, $grp)
    {
        debug_note("groups.php: custom=$custom");
        switch($custom)
        {
    	    case constDashStatus_SelectMachineGroup :
	        return 'Dashboard - Select Machine Group';

            case constPageEntryAsset   : return title('aset', $cat, $grp);

            case constPageEntryTools   : return title('tool', $cat, $grp);

            case constPageEntryReports : return title('rprt', $cat, $grp);

            case constPageEntryNotfy   : return title('notf', $cat, $grp);

            case constPageEntrySites   : return title('syte', $cat, $grp);

            default                    : return title($act,   $cat, $grp);
        }
    }




    function obnoxious(&$env)
    {
        $p   = 'p style="font-size:8pt"';
        $msg = <<< XXXX

        <$p>
          Click on the <i>wizard</i> link below to go to
          the main Microsoft update wizard page.
        </p>

        <$p>
          Clicking on the <i>status</i> link will take you to the
          Microsoft update wizard action status page where you
          can review and manage wizard actions taken to date.
        </p>

XXXX;
        if ($env['done'])
        {
            $txt = '';
        }
        else
        {
            $env['done'] = 1;              $txt = $msg;
        }
        return $txt;
    }



    function again(&$env)
    {
        $custom = $env['custom'];
        debug_note("custom = $custom");
        $self   = $env['self'];
        $dbg    = $env['priv'];
        $act    = $env['act'];
        $kid    = $env['kid'];
        $a    = array( );
        $txt  = '';
        $cmd  = "$self?act";
        $wiz  = "$cmd=wiz";
        $a[]  = html_link('#top','top');
        $a[]  = html_link('#bottom','bottom');

        if ($custom == constPageEntryTools)
        {
            $cst = customURL($custom);
            $a[] = html_link("../acct/groups.php?$cst",'advanced');
        }

        if (matchOld($act,'|||lwid|uwid|wpxa|dwid|cpwc|'))
        {
            $a[] = html_link("$cmd=awid",'add');
        }
        if (matchOld($act,'|||lpid|upid|cppc|wssa|'))
        {
            $a[] = html_link("$cmd=apid",'add');
        }
        if (matchold($act,'|||pexp|peup|pedn|'))
        {
            $a[] = html_link("$cmd=cata",'add');
            $a[] = html_link("$cmd=pcat",'collapse');
        }
        if (matchOld($act,'|||pcat|grpc|catc|pcup|pcdn|'))
        {
            $a[] = html_link("$cmd=cata",'add');
            $a[] = html_link("$cmd=pexp",'expand');
        }
        if (matchold($act,'|||pgrp|upgs|upge|epgx|epgd|epgb|cpga|'))
        {
            if ($kid)
            {
                $add = "$cmd=grpa&kid=$kid";
                $a[] = html_link($add,'add');
            }
        }
        $stat = "$cmd=wsts";
        switch($custom)
        {

	    case constDashStatus_SelectMachineGroup : ;
            case constPageEntryScrpConf : ;
            case constPageEntryAsset    : ;
            case constPageEntrySites    : ;
            case constPageEntryTools    : ;
            case constPageEntryNotfy    : ;
            case constPageEntryReports  : break;

            case constPageEntryWUconfg  : $a[] = html_link($stat,'status');
                                          $txt = obnoxious($env);
                                         break;
        }
        if ($dbg)
        {
            $menu = "$cmd=menu";
            $dbug = "$cmd=dbug";
            $args = $env['args'];
            $href = ($args)? "$self?$args" : $self;
            $a[] = html_link('../acct/index.php','home');
            $a[] = html_link($href,'again');
            $a[] = html_link($dbug,'debug');
        }
        $tag = jumplist($a);

        return $txt . $tag;
    }


    function unknown_action(&$env,$db)
    {
        echo again($env);

        $act = $env['act'];
        $frm = $env['frm'];
        $sp  = str_repeat('<br>',8);

        debug_note("unknown action: act:$act frm:$frm");
        echo "\n$sp\n<p>Unknown Action</p>\n$sp\n";
        echo again($env);
    }


    function invalid_action(&$env,$db)
    {
        echo again($env);

        $act = $env['act'];
        $frm = $env['frm'];
        $sp  = str_repeat('<br>',8);

        debug_note("invalid action: act:$act frm:$frm");
        echo "\n$sp\n<p>Invalid Action</p>\n$sp\n";
        echo again($env);
    }



    function parabold($txt)
    {
        return "<p><b>$txt</b></p>\n";
    }





    function config_menu(&$env,$db)
    {
        echo again($env);
        $self = $env['self'];
        $act  = "$self?act";
        $grps = '../acct/groups.php';
        $lwid = html_link("$act=lwid",'Machine configuration');
        $lpid = html_link("$act=lpid",'Software update configuration');
        $pcat = html_link("$act=pcat",'Software update groups');
        $mcat = html_link($grps,'Machine groups');

        $text = '';
        if($env['priv'])
        {
            $text ='<p>'
                . "<b>$pcat</b>: this allows you to see, add, and edit"
                . ' software update groups.  These define groups of software'
                . ' updates that are used to select a software update'
                . ' configuration as described above.'
                . '</p>';
        }
        echo <<< MENU

        <p>
          This page lets you configure all aspects of the machines
          running the Windows Update process and the software updates
          that it installs.
        </p>

        <p>
          <b>$lwid</b>: this allows you to see, add, and edit
          machine configurations.  A machine configuration
          applies to a group of machines and controls how the
          Windows Update process runs on all the machines that
          are in the group.
        </p>

        <p>
          <b>$lpid</b>: this allows you to see, add, and edit
          software update configurations.  A software update
          configuration determines how certain updates are
          applied to specific machines.  The updates that are
          affected are chosen using a software update group,
          and the machines that are affected are chosen using
          a machine group.
        </p>

        $text

        <p>
          <b>$mcat</b>: this allows you to see, add, and edit
          machine groups.  These define groups of machines that
          are used to select a software update configuration,
          and also to select a machine configuration, both
          as described above.
        </p>

MENU;

        echo again($env);
    }




    function js_select($name,$opt,$val,$key)
    {
        $temp = html_select($name,$opt,$val,$key);
        $patn = 'size="1"';
        $chng = 'onChange="document.myform.submit();"';
        $repl = "$patn $chng";
        return str_replace($patn,$repl,$temp);
    }


    function js_radio($name,$valu,$var)
    {
        $temp = radio($name,$valu,$var);
        $patn = 'type="radio"';
        $chng = 'onChange="document.myform.submit();"';
        $repl = "$patn $chng";
        return str_replace($patn,$repl,$temp);
    }


    function add_wcfg(&$env,$db)
    {

        echo <<< AWID

        <p>
          A machine configuration applies to a group
          of machines and controls how the Windows
          Update process runs on all the machines
          that are in the group.
        </p>

AWID;

        echo again($env);
        $tid  = $env['tid'];
        $gid  = $env['gid'];
        $auth = $env['auth'];

        $cat  = mcat_options($db);
        $grp  = mgrp_options($auth,$db);
        $opt  = (isset($grp[$tid]))? $grp[$tid] : $grp[0];

        $tid = js_select('tid',$cat,$tid,1);
        $gid = html_select('gid',$opt,$gid,1);
        $in  = indent(5);

        echo post_self('myform');
        echo hidden('act','cwid');
        echo hidden('frm','awid');
        echo hidden('pcn','menu');
        echo para('Add Machine configuration for machine group:');
        echo "${in}category: $tid<br>\n";
        echo "${in}name: $gid<br>\n";
        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }


    function mgrp_error()
    {
        $txt = 'This machine group contains machines which you do not own.';
        return para($txt);
    }


    function find_pub_mgrp_own(&$env,$gid,$db)
    {
        $mgrp = array( );
        if (legal_mgrp($env,$gid,$db))
        {
            $auth = $env['auth'];
            $mgrp = find_pub_mgrp_gid($gid,$auth,$db);
        }
        else
        {
            echo mgrp_error();
        }
        return $mgrp;
    }



    function find_mgrp_own(&$env,$gid,$db)
    {
        $mgrp = array( );
        if (legal_mgrp($env,$gid,$db))
        {
            $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne,$db);
        }
        else
        {
            echo mgrp_error();
        }
        return $mgrp;
    }




    function legal_mgrp(&$env,$gid,$db)
    {
        $set = array( );
        $adm = $env['admn'];
        $dbg = $env['debug'];

        if ($dbg)
        {
            $auth = $env['auth'];
            $set  = mgrp_alien($gid,$auth,$db);
            if ($set)
            {
                reset($set);
                foreach ($set as $key => $row)
                {
                    $host = $row['host'];
                    $site = $row['site'];
                    debug_note("user $auth has no access to $host at $site");
                }
                if ($adm)
                {
                    debug_note('going ahead anyway ...');
                }
            }
        }
        else
        {
            if (!$adm)
            {
                $auth = $env['auth'];
                $set  = mgrp_alien($gid,$auth,$db);
            }
        }
        return (($adm) || (!$set));
    }


    function create_wcfg(&$env,$db)
    {
        echo again($env);
        $gid = $env['gid'];
        $grp = find_pub_mgrp_own($env,$gid,$db);
        if ($grp)
        {
            $name = $grp['name'];
            $row = find_wcfg_gid($gid,$db);
            if ($row)
            {
                $act = 'fwid';
                $msg = "Group <b>$name</b> already has"
                     . ' a machine configuration.<br>'
                     . 'Would you like to edit it?</p>';
            }
            else
            {
                $act = 'iwid';
                $msg = "Create a new machine configuration for <b>$name</b>?";
            }
            echo post_self('myform');
            echo hidden('act',$act);
            echo hidden('frm','cwid');
            echo hidden('pno','lwid');
            echo hidden('gid',$gid);
            echo para($msg);
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function create_pcfg(&$env,$db)
    {
        echo again($env);
        $auth = $env['auth'];
        $gid  = $env['gid'];
        $jid  = $env['jid'];
        $mgrp = find_pub_mgrp_own($env,$gid,$db);
        $pgrp = find_pub_pgrp_jid($jid,$auth,$db);
        $row  = find_pcfg($gid,$jid,$db);
        if (($mgrp) && ($pgrp))
        {
            $pval = $pgrp['name'];
            $mval = $mgrp['name'];
            if ($row)
            {
                $act = 'fpid';
                $msg = "Group <b>$pval</b> of <b>$mval</b> already has"
                     . ' a configuration.<br>'
                     . 'Would you like to edit it?</p>';
            }
            else
            {
                $act = 'ipid';
                $msg = "Create a new configuration for <b>$pval</b> of <b>$mval</b>?";
            }
            echo post_self('myform');
            echo hidden('act',$act);
            echo hidden('frm','cpid');
            echo hidden('pno','pcat');
            preserve($env,'gid,jid');
            echo "<p>$msg</p>";
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function add_pcfg(&$env,$db)
    {
        echo again($env);
        $tid  = $env['tid'];
        $gid  = $env['gid'];
        $kid  = $env['kid'];
        $jid  = $env['jid'];
        $auth = $env['auth'];

        $mcat = mcat_options($db);
        $pcat = pcat_options($db);
        $mgrp = mgrp_options($auth,$db);
        $pgrp = pgrp_options($auth,$db);
        $mopt = (isset($mgrp[$tid]))? $mgrp[$tid] : $mgrp[0];
        $popt = (isset($pgrp[$kid]))? $pgrp[$kid] : $mgrp[0];

        $tid = js_select('tid',$mcat,$tid,1);
        $kid = js_select('kid',$pcat,$kid,1);
        $jid = html_select('jid',$popt,$jid,1);
        $gid = html_select('gid',$mopt,$gid,1);
        $in  = indent(5);

        echo post_self('myform');
        echo hidden('act','cpid');
        echo hidden('frm','apid');
        echo hidden('pcn','menu');

        echo <<< APID

        <p>
          A software update configuration applies to a group
          of updates on a group of machines and controls how
          those updates are applied to those machines.
        </p>

        <p>
          The software updates that are selected are controlled
          using a software update group, and the machines that
          are selected are controlled using a machine group.
          (Since each software update configuration applies
          to a specific set of updates on a specific set
          of machines, both of these must always be defined.)
        </p>

        <p>
          You need to select the machine group and the software
          update group with the pulldowns below.
        </p>

        <p>
          If you want to review the existing machine groups, or
          you need to add a new one, click on "tools: groups"
          in the menu in the upper right of the page.
        </p>

        <p>
          If you want to review the existing software update
          groups, or you need to add a new one, click on
          "microsoft update: configuration" in the menu in
          the upper right of the page, then click on
          the "Software update configuration" link.
        </p>

        Add update configuration for machine group:<br>
        ${in}category: $tid<br>
        ${in}name: $gid<br>
        and software update group:<br>
        ${in}category: $kid<br>
        ${in}name: $jid<br>

APID;

        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }

    function edit_wconfig(&$env,$db)
    {
        echo again($env);
        $tid  = $env['tid'];
        $gid  = $env['gid'];
        $auth = $env['auth'];

        $cat = mcat_options($db);
        $grp = mgrp_options($auth,$db);
        $opt = (isset($grp[$tid]))? $grp[$tid] : $grp[0];

        $tid = js_select('tid',$cat,$tid,1);
        $gid = html_select('gid',$opt,$gid,1);
        $in  = indent(5);

        echo post_self('myform');
        echo hidden('act','fwid');
        echo hidden('frm','ewid');
        echo hidden('pcn','menu');
        echo para('Edit Machine configuration for machine group:');
        echo "${in}category: $tid<br>\n";
        echo "${in}name: $gid<br>\n";
        echo okcancel_link(5,$env['custom']);
        echo form_footer();
        echo again($env);
    }


    function form_wconfig(&$env,$db)
    {
        echo again($env);
        $gid = $env['gid'];
        $adm = $env['admn'];
        $grp = find_pub_mgrp_own($env,$gid,$db);
        $row = find_wcfg_gid($gid,$db);
        $cat = mcat_options($db);
        if (($grp) && ($row) && ($cat))
        {
            $tid   = $grp['mcatid'];
            $gname = $grp['name'];
            $cname = isset($cat[$tid])? $cat[$tid] : '';
            $gdisp = 'Group';
            $cdisp = 'Category';
            if ($cname == 'Machine')
            {
                $cen = census_gid($gid,$db);
                if ($cen)
                {
                    $cdisp = 'Site';
                    $gdisp = 'Machine';
                    $cname = $cen['site'];
                    $gname = $cen['host'];
                }
            }

            $wid  = $row['id'];
            $man  = $row['management'];
            $new  = $row['newpatches'];
            $src  = $row['patchsource'];
            $iday = $row['installday'];
            $hour = $row['installhour'];
            $url  = $row['serverurl'];
            $prop = $row['propagate'];
            $updc = $row['updatecache'];
            $csec = $row['cacheseconds'];
            $rest = $row['restart'];
            $chan = $row['chain'];
            $chas = $row['chainseconds'];
            $chas = intval(round($chas / 3600));

            $in   = indent(5);
            $xn   = indent(10);

            $serv = textbox('url',60,$url);
            $cday = intval(round($csec / 86400));
            $cday = value_range(1,31,$cday);
            $hour = value_range(0,23,$hour);
            $iday = value_range(1,8,$iday);
            $prop = value_range(0,2,$prop);
            $hopt = hour_options();
            $dopt = day_options();
            $copt = range(1,31);
            $bool = array('No','Yes');

            unset($hopt[24]);
            $time = html_select('hour',$hopt,$hour,1);
            $date = html_select('iday',$dopt,$iday,1);
            $cday = html_select('cday',$copt,$cday,0);

            $man = value_range(1,5,$man);
            $m1  = radio('man',constConfigManagementDisabled,$man);
            $m2  = radio('man',constConfigManagementServer,$man);
            $m3  = radio('man',constConfigManagementUser,$man);
            $m4  = radio('man',constConfigManagementInstallControl,$man);
            $m5  = radio('man',constConfigManagementAutomatic,$man);

            $new = value_range(1,2,$new);
            $n1  = radio('new',constConfigNewPatchesLastDefault,$new);
            $n2  = radio('new',constConfigNewPatchesWaitServer,$new);

            $src = value_range(1,2,$src);
            $s1  = radio('src',constConfigPatchSourceWebSite,$src);
            $s2  = radio('src',constConfigPatchSourceSUSServer,$src);

            $up = value_range(1,2,$updc);
            $u1 = radio('updc',constConfigCacheDisable,$up);
            $u2 = radio('updc',constConfigCacheEnable,$up);

            $p0 = radio('prop',constConfigPropVendorOnly,$prop);
            $p1 = radio('prop',constConfigPropLocalOnly,$prop);
            $p2 = radio('prop',constConfigPropSearch,$prop);

            $rest = value_range(1,2,$rest);
            $r1 = radio('rest',constConfigRebootDisable,$rest);
            $r2 = radio('rest',constConfigRebootAuto,$rest);

            $chas = value_range(1,8,$chas);
            $chan = value_range(1,3,$chan);
            $sopt = range(1,8);

            $c1  = radio('chan',constConfigChainTimeout,$chan);
            $c2  = radio('chan',constConfigChainInfinite,$chan);
            $c3  = radio('chan',constConfigChainDisabled,$chan);
            $chas = html_select('chas',$sopt,$chas,0);

            echo post_self('myform');
            echo hidden('act','uwid');
            echo hidden('frm','fwid');
            echo hidden('pcn','menu');
            echo hidden('wid',$wid);
            echo hidden('gid',$gid);

            echo <<< FWID

            <b>Group:</b><br>
            ${in}$cdisp: <b>$cname</b><br>
            ${in}$gdisp: <b>$gname</b><br>

            <br>
            <b>Source of Updates:</b><br>
            ${in}$s1 Microsoft Update Server<br>
            ${in}$s2 SUS server: $serv<br>

            <br>
            <b>Management:</b><br>
            ${in}$m1 Disable<br>
            ${in}$m3 User controlled download and install<br>
            ${in}$m4 Automated download, user controlled install<br>
            ${in}$m5 Automated download and install $date at $time<br>
            ${in}$m2 Manage from Server<br>

            <br>
            The following options only apply when
            <b>Manage from Server</b> is selected.<br>

            <br>
            $in<b>New Updates:</b><br>
            ${xn}$n1 Act based on last settings from server.<br>
            ${xn}$n2 Wait to get current settings from
                        server before taking action.<br>

            <br>
            $in<b>Downloading updates:</b><br>
            ${xn}$p0 Only download from vendor<br>
            ${xn}$p1 Only retrieve from local machines<br>
            ${xn}$p2 Try to retrieve from local machines,
                     then download from vendor if
                     unsuccessful<br>

            <br>
            $in<b>Retention policy:</b><br>
            ${xn}$u1 Do not keep updates on this machine for other machines to use<br>
            ${xn}$u2 Keep updates on this machine for $cday days, for other machines to use<br>

            <br>
            $in<b>Restart policy:</b><br>
            ${xn}$r1 Do not automatically restart when a restart is necessary
            after an installation.<br>
            ${xn}$r2 Automatically restart when a restart is necessary after an
            installation.<br>

            <br>
            $in<b>Multiple installations:</b><br>
            ${xn}$c1 Repeat install cycle until machine is up to date, but stop after $chas hours.<br>
            ${xn}$c2 Repeat install cycle until machine is up to date.<br>
            ${xn}$c3 Only do one install cycle.<br>
FWID;
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        else
        {
            echo para('There is some problem with this form.');
        }
        echo again($env);
    }


    function count_table($name,$db)
    {
        $sql = "select count(*) from $name";
        return intval(find_scalar($sql,$db));
    }


    function create_temp_table($name,$db)
    {
        $sql = "create temporary table $name\n"
             . " (id int(11) not null primary key)";
        $res = redcommand($sql,$db);
    }

    function drop_temp_table($name,$db)
    {
        $sql = "drop table $name";
        $res = redcommand($sql,$db);
    }


    function find_scop_mgrp(&$env,$db)
    {
        $mgrp = array( );
        $scop = $env['scop'];
        if ($scop == constScopAll)
        {
            $admn = $env['admn'];
            if ($admn)
            {
                $mgrp = find_mgrp_name(constCatAll,$db);
            }
        }

        if ($scop == constScopUser)
        {
            $auth = $env['auth'];
            if ($auth)
            {
                $name = mgrp_user($auth);
                $mgrp = find_mgrp_name($name,$db);
            }
        }

        if ($scop == constScopSite)
        {
            $cid  = $env['cid'];
            $site = $env['site'];
            if (($cid) && ($site))
            {
                $mgrp = find_site_mgrp($site,$db);
            }
        }
        if ($scop == constScopHost)
        {
            $hid  = $env['hid'];
            $mgrp = find_host_mgrp($hid,$db);
        }

        if ($scop == constScopGroup)
        {
            $gid  = $env['gid'];
            $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        }
        return $mgrp;
    }


    function wiz_prop_form(&$env,$db)
    {
        echo again($env);
        $gid  = $env['gid'];
        $act  = $env['act'];
        $scop = $env['scop'];
        debug_note("<b>$act</b> wiz_prop_form");

        $mgrp = ($scop)? find_scop_mgrp($env,$db) : find_mgrp_gid($gid,
                                                 constReturnGroupTypeOne, $db);
        $gid  = ($mgrp)? $mgrp['mgroupid'] : 0;
        $good = legal_mgrp($env,$gid,$db);
        $wcfg = find_wcfg_gid($gid,$db);
        if (!$wcfg)
        {
            $mall = find_mgrp_name(constCatAll,$db);
            $agid = ($mall)? $mall['mgroupid'] : 0;
            $wcfg = find_wcfg_gid($agid,$db);
        }
        if (!$wcfg)
        {
            $wcfg = default_wcfg();
        }
        if (!$good)
        {
            echo mgrp_error();
        }

        if (($good) && ($mgrp) && ($wcfg))
        {
            if ($scop)
            {
                $act = 'wprp';
                $frm = 'wprp';
                $pcn = 'wiz';
            }
            else
            {
                $act = 'prpa';
                $frm = 'prop';
                $pcn = 'wsts';
            }
            $name = $mgrp['name'];
            $prop = value_range(0,2,$wcfg['propagate']);
            $updc = value_range(1,2,$wcfg['updatecache']);
            $csec = $wcfg['cacheseconds'];
            $cday = value_range(1,31,round($csec / 86400));
            $copt = range(1,31);
            debug_note("update group <b>$name</b> gid:$gid, days:$cday prop:$prop, cache:$updc");
            $days = html_select('cday',$copt,$cday,0);

            $in = indent(5);
            $p0 = radio('prop',constConfigPropVendorOnly,$prop);
            $p1 = radio('prop',constConfigPropLocalOnly,$prop);
            $p2 = radio('prop',constConfigPropSearch,$prop);

            $up = value_range(1,2,$updc);
            $u1 = radio('updc',constConfigCacheDisable,$up);
            $u2 = radio('updc',constConfigCacheEnable,$up);

            echo post_self('myform');
            echo hidden('act', $act);
            echo hidden('frm', $frm);
            echo hidden('pcn', $pcn);
            echo hidden('ctl', '1');
            preserve($env,'scop,cid,hid,gid');

            echo <<< WPRP

            <p>
              How do you want the machines you selected
              to retrieve updates?
            </p>

            <br>
            <b>Updates should be:</b><br>
            ${in}$p0 Only downloaded from vendor.<br>
            ${in}$p1 Only retrieved from local machines.<br>
            ${in}$p2 Try local machines first, then from
                     vendor if unsuccessful.

            <br><br>
            <hr>
            <br><br>

            <b>Update retention policy</b>

            <p>
              How long would you like the machines you selected to
              download updates to keep copies of the update files?
            </p>

            ${in}$u1 Do not keep updates on the selected machine(s)
                     for other machines to use<br>
            ${in}$u2 Keep updates on the selected machine(s) for $days days,
                     for other machines to use<br>

WPRP;
            echo okcancel_link(5, $env['custom']);
        }
        echo again($env);
    }


    function wiz_prop_act(&$env,$db)
    {
        echo again($env);
        $save = false;
        $good = false;
        $scop = $env['scop'];
        $gid  = $env['gid'];
        $prop = value_range(0, 2, $env['prop']);
        $updc = value_range(1, 2, $env['updc']);
        $cday = value_range(1,31, $env['cday']);

        debug_note("<b>wprp</b> wiz_prop_act cday:$cday prop:$prop updc:$updc");
        $mgrp = ($scop)? find_scop_mgrp($env,$db) : find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        $gid  = ($mgrp)? $mgrp['mgroupid'] : 0;
        $wcfg = find_wcfg_gid($gid,$db);
        $good = legal_mgrp($env,$gid,$db);
        if (!$wcfg)
        {
            $save = true;
            $mall = find_mgrp_name(constCatAll,$db);
            $agid = ($mall)? $mall['mgroupid'] : 0;
            $wcfg = find_wcfg_gid($agid,$db);
        }
        if ((!$wcfg) && ($gid))
        {
            $save = true;
            $wcfg = default_wcfg();
        }

        if (!$good)
        {
            echo mgrp_error();
        }

        if (($save) && ($good) && ($gid))
        {
            $wid = insert_wcfg($gid,$wcfg,$db);
            if ($wid)
            {
                $wcfg['id'] = $wid;
                $wcfg['mgroupid'] = $gid;
            }
            else
            {
                $wcfg = array( );
            }
        }

        if (($good) && ($mgrp) && ($wcfg))
        {
            $txt = $mgrp['name'];
            $man = $wcfg['management'];
            $wid = $wcfg['id'];
            $sec = $cday * 86400;
            $new = constConfigManagementServer;
            $sql = "update WUConfig set\n"
                 . " management = $new,\n"
                 . " propagate = $prop,\n"
                 . " updatecache = $updc,\n"
                 . " cacheseconds = $sec\n"
                 . " where mgroupid = $gid";
            $sql2 = "SELECT * FROM WUConfig WHERE mgroupid=$gid";
            $newcfg = array();
            $newcfg['management'] = $new;
            $newcfg['propagate'] = $prop;
            $newcfg['updatecache'] = $updc;
            $newcfg['cacheseconds'] = $sec;
            GRPW_AuditMachineChange($env, $sql2, $newcfg, $db);
            debug_note("name:$txt cday:$cday prop:$prop updc:$updc");
            $res = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $good = true;
                if (touch_wid($wid,$db))
                {
                    if ($man != $new)
                    {
                        echo para('Managed from Server will be set.');
                    }
                }
            }
            else
            {
                $good = ($res)? true : false;
            }
        }
        if (($mgrp) && ($wcfg) && ($good))
        {
            $name = $mgrp['name'];
            $stat = "w:$wid,g:$gid,p:$prop,m:$new,u:$updc,s:$sec";
            $text = "patch: wizard propagation ($stat) $name";
                        debug_note($text);

            $scop = $env['scop'];
            $who  = scop_info($scop,$mgrp);
            $how  = long_prop($prop);
            $txt  = "$who will now download updates $how.";
        }
        else
        {
            $txt = 'Machine Propagation Unchanged.';
        }
        echo para($txt);
        if ($scop)
            wizard_return($env,$db);
        else
            wizard_stat_return($env,$db);
        echo again($env);
    }


    function legal_scope(&$env,$db)
    {
        switch ($env['scop'])
        {
            case constScopNone : return 0;
            case constScopAll  : return $env['admn'];
            case constScopUser : return 1;
            case constScopSite : return ($env['cid'] > 0)? 1 : 0;
            case constScopGroup: return ($env['gid'] > 0)? 1 : 0;
            case constScopHost : return ($env['hid'] > 0)? 1 : 0;
            default            : return 0;
        }
    }


    function wizard_site(&$env,$db)
    {
        $act  = $env['act'];
        $cid  = $env['cid'];
        $auth = $env['auth'];
        echo again($env);

        $scop = value_range(1,5,$env['scop']);
        $none = indent(20);
        $cids = site_options($auth,$none,$db);
        $size = safe_count($cids);

        debug_note("<b>$act</b> wizard_site");

        echo post_self('myform');
        echo hidden('act',$act);
        echo hidden('frm',$act);
        echo hidden('ctl',0);
        echo hidden('pcn','wiz');
        preserve($env,'dtc,int');


        if ($size > 1)
        {
            echo hidden('scop',$scop);

            if ($scop == constScopHost)
                $txt = 'Which site contains the machine where';
            else
                $txt = 'At which site would you';

            echo "<br><p>$txt like to perform this action?</p>\n";

            $sel = 0;
            reset($cids);
            foreach ($cids as $cid => $site)
            {
                if ($cid)
                {
                    if (!$sel) $sel = $cid;
                    radio_tag('cid',$cid,$sel,$site);
                }
            }
        }
        else
        {
            echo hidden('scop',constScopNone);
            $what = ($scop == constScopHost)? 'machines' : 'sites';
            $text = "You don't seem to have any appropriate $what ... would you like to start over?";
            echo para($text);
        }

        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }



    function wizard_host(&$env,$db)
    {
        $cid  = $env['cid'];
        $act  = $env['act'];
        $site = $env['site'];
        debug_note("<b>$act</b> wizard_host");
        if (($cid) && ($site))
        {
            $in   = indent(5);
            $hids = host_options($cid,$db);
            $size = safe_count($hids);

            echo again($env);
            echo post_self('myform');
            echo hidden('act',$act);
            echo hidden('frm',$act);
            echo hidden('ctl',0);
            echo hidden('pcn','wiz');
            echo hidden('cid',$cid);
            preserve($env,'dtc,int');

            echo okcancel_link(5, $env['custom']);

            echo para("Site: <b>$site</b>");
            if ($size > 1)
            {
                echo hidden('scop',constScopHost);
                $text = 'On which machine would you like'
                      . ' to perform this action?';

                echo para($text);

                $sel = 0;
                reset($hids);
                foreach ($hids as $hid => $host)
                {
                    if ($hid)
                    {
                        if (!$sel) $sel = $hid;
                        radio_tag('hid',$hid,$sel,$host);
                    }
                }
            }
            else
            {
                $text = "You don't seem to have any appropriate machines ..."
                      . ' would you like to start over?';
                echo hidden('scop',constScopNone);
                echo para($text);

            }
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
            echo again($env);
        }
        else
        {
            wizard_site($env,$db);
        }
    }


    function wiz_make_mgrp(&$env,$text,$tid,$db)
    {
        $mgrp = array( );
        $name = unique_mgrp($text,0,$db);
        if (($name) && ($tid))
        {
            $auth = $env['auth'];
            $glob = $env['glob'];
            $type = constStyleManual;
            $gid  = insert_mgrp($tid,$name,$auth,$glob,1,$type,0,0,0,'Manual',
                '',$db);
            $mgrp = find_mgrp_gid($gid,constReturnGroupTypeOne, $db);
        }
        if ($mgrp)
        {
            check_change($text,$name);
        }
        return $mgrp;
    }


    function wiz_site_frm(&$env,&$mgrp,&$mcat,$db)
    {
        $custom = $env['custom'];
        $act    = $env['act'];
        $tid    = $env['tid'];
        $cid    = $env['cid'];
        $sub    = $env['sub'];
        $int    = $env['int'];
        $dtc    = $env['dtc'];
        $dgid   = $env['dgid'];
        $self   = $env['self'];
        $scop   = $env['scop'];
        $auth   = $env['auth'];
        $n_id   = $env['notification_id'];
        $n_act  = $env['notification_act'];
        $r_id   = $env['report_id'];
        $r_act  = $env['report_act'];
        $a_id   = $env['asset_id'];
        $a_act  = $env['asset_act'];
        $cids   = array( );

        debug_note("<b>$act</b> wiz_site_frm <b>$sub</b> tid:$tid, dgid:$dgid");

        $qu  = safe_addslashes($auth);
        $sql = "select C.site, U.id,\n"
             . " count(C.id) as num from\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U\n"
             . " where C.site = U.customer\n"
             . " and U.username = '$qu'\n"
             . " group by site\n"
             . " order by site";
        $set = find_many($sql,$db);

        reset($set);
        foreach ($set as $key => $row)
        {
            $cid = $row['id'];
            $cids[$cid]['site'] = $row['site'];
            $cids[$cid]['num']  = $row['num'];
            $cids[$cid]['list'] = array( );
        }

        $sql = "select U.id, C.host\n"
             . " from ".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
             . " where M.mgroupuniq = G.mgroupuniq\n"
             . " and G.mgroupid = $dgid\n"
             . " and M.censusuniq = C.censusuniq\n"
             . " and C.site = U.customer\n"
             . " and U.username = '$qu'\n"
             . " order by host";
        $set = find_many($sql,$db);

        reset($set);
        foreach ($set as $key => $row)
        {
            $cid  = $row['id'];
            $cids[$cid]['list'][] = $row['host'];
        }

        if (($mgrp) && ($mcat) && ($cids))
        {
            $cat = $mcat['category'];
            $grp = $mgrp['name'];
            $cmd = "$self?act=$act&gid=-1&dgid=$dgid&scop=$scop"
                 . "&tid=$tid&custom=$custom";
            if(@$env['addgroup']!=0)
            {
                $cmd .= "&addgroup=" . $env['addgroup'];
            }

            echo GRPS_display_again($custom)? again($env) : '';
            echo post_self('myform');


	    switch( $custom )
	    {
	        case constPageEntryScrpConf :
		    $act  = 'machine_selected';
		    $scop = constScopUser;
		break;

	        case constDashStatus_SelectMachineGroup :
		    $act = $custom;
	        break;
	    }

            echo hidden('act',     $act);
            echo hidden('frm',     $act);
            echo hidden('ctl',     0);
            echo hidden('pcn',     'wiz');
            echo hidden('tid',     $tid);
            echo hidden('gid',     $dgid);
            echo hidden('mgroupid',$dgid);
            echo hidden('scop',    $scop);
            echo hidden('custom',  $custom);
            echo hidden('notification_id', $env['notification_id']);
            echo hidden('notification_act',$env['notification_act']);
            echo hidden('report_id',       $env['report_id']);
            echo hidden('report_act',      $env['report_act']);
            echo hidden('asset_id',        $env['asset_id']);
            echo hidden('asset_act',       $env['asset_act']);
            if(@$env['isparent'])
            {
                echo hidden('isparent', $env['isparent']);
            }

            $text =<<< GORK

        <p>
          Below, you can change the machines included in the group you want
          to use. Use <b>select all</b> to include all the machines at a
          single site. Use <b>deselect all</b> to remove all the machines
          at a single site. To de-select one or more previously selected
          machines click on <b>select/de-select individual</b>, then uncheck
          the box for the machine you want to de-select in the New column. Use
          <b>select/de-select individual</b> to select individual machines at
          that site.  When you finish making changes, click on the <b>OK</b>
          button.
        </p>

GORK;
            if(@$env['level'])
            {
                echo hidden('level',        $env['level']);
            }
            if(@$env['isparent'])
            {
                echo hidden('isparent',        $env['isparent']);
            }
            if(@$env['addgroup']!=0)
            {
                echo hidden('addgroup',     $env['addgroup']);
                $text =<<< GORK2

        <p>
          Below, you can add machines to the group you are creating. Use
          <b>select all</b> to include all the machines at a
          single site. Use <b>select/de-select individual</b> to select
          individual machines at that site.  When you finish making changes,
          click on the <b>OK</b> button.
        </p>

GORK2;
            }

            preserve($env,'int,dtc');

            $sall = 'select all';
            $dall = 'deselect all';
            $sind = 'select/de-select individual';

            echo $text;

            $text = 'Action|Site|Group Members from '
                  . 'Site|Number of Machines at Site';
            $head = explode('|',$text);
            $cols = safe_count($head);

            echo GRPW_display_wizard_buttons($custom, $dgid, $env);
            echo '<br><br>';
            echo table_header();
            echo pretty_header($grp,$cols);
            echo table_data($head,1);

            reset($cids);
            foreach ($cids as $cid => $row)
            {
                $totl = $row['num'];
                $list = $row['list'];
                $site = $row['site'];
                $numb = safe_count($list);
                $a    = array();
                $sub  = "$cmd&cid=$cid&int=$int&dct=$dtc"
                      . "&notification_id=$n_id&notification_act=$n_act"
                      . "&report_id=$r_id&report_act=$r_act"
                      . "&asset_id=$a_id&asset_act=$a_act";
                if(@$env['isparent'])
                {
                    $sub .= '&isparent=' . $env['isparent'];
                }
                $sub .= '&sub';

                if ($numb < $totl)
                {
                    $a[]  = html_link("$sub=call","[$sall]");
                }
                if ($numb)
                {
                    $a[]  = html_link("$sub=cnon","[$dall]");
                }
                if ($totl > 1)
                {
                    $a[]  = html_link("$sub=cind","[$sind]");
                }
                $text = ($list)? join('<br>',$list) : '<br>';
                $acts = join('<br>',$a);
                $args = array($acts,$site,$text,$totl);
                echo table_data($args,0);
            }
            echo table_footer();
            echo GRPW_display_wizard_buttons($custom, $dgid, $env);
            echo '<br><br>';
            echo form_footer();
            echo GRPS_display_again($custom)? again($env) : '';

        }
    }


    function wiz_site_add(&$env,&$mgrp,&$mcat,$db)
    {
        $cid  = $env['cid'];
        $tid  = $env['tid'];
        $site = $env['site'];
        $dgid = $env['dgid'];
        debug_note("wiz_site_add");
        if (($cid) && ($tid) && ($mgrp) && ($site))
        {
            $grp = $mgrp['name'];
            $num = mgrp_add_site($site,$tid,$dgid,$db);
            debug_note("$num machines added");
            if ($num)
            {
                $env['allowdelete'] = 1;
                $msg = plural($num,'machine');
                echo para("$msg from <b>$site</b> added to group <b>$grp</b>.");
            }
        }
        wiz_site_frm($env,$mgrp,$mcat,$db);
    }


    function dcheckbox($name,$checked)
    {
        $valu = ($checked)? 'checked' : '';
        return "<input type=\"checkbox\" disabled $valu name=\"$name\" value=\"1\">";
    }

    function wiz_site_box(&$env,&$mgrp,&$mcat,$db)
    {
        $act  = $env['act'];
        $cid  = $env['cid'];
        $tid  = $env['tid'];
        $dgid = $env['dgid'];
        $site = $env['site'];
        $post = $env['post'];
        debug_note("<b>$act</b> wiz_site_box $site");

        $qs  = safe_addslashes($env['site']);
        $sql = "select C.host,\n"
             . " C.id as mid,\n"
             . " M.mgmapid as hid\n"
             . " from (".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G)\n"
             . " left join ".$GLOBALS['PREFIX']."core.MachineGroupMap as M\n"
             . " on C.censusuniq = M.censusuniq\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " where C.site = '$qs'\n"
             . " and G.mgroupid = $dgid\n"
             . " group by C.host\n"
             . " order by host";
        $set = find_many($sql,$db);
        if (($set) && ($mgrp) && ($mcat))
        {
            $name = $mgrp['name'];
            $allc = ($post == constButtonAll)?  true : false;
            $none = ($post == constButtonNone)? true : false;

            echo again($env);
            echo post_self('myform');
            echo hidden('act',$act);
            echo hidden('frm',$act);
            echo hidden('ctl',0);
            echo hidden('pcn','wiz');
            echo hidden('tid',$tid);
            echo hidden('cid',$cid);
            echo hidden('dgid',$dgid);
            echo hidden('sub','chck');
            echo hidden('scop',constScopGroup);
            echo hidden('custom',$env['custom']);
            echo hidden('notification_id', $env['notification_id']);
            echo hidden('notification_act',$env['notification_act']);
            echo hidden('report_id', $env['report_id']);
            echo hidden('report_act',$env['report_act']);
            echo hidden('asset_id',  $env['asset_id']);
            echo hidden('asset_act', $env['asset_act']);
            if(@$env['level'])
            {
                echo hidden('level',        $env['level']);
            }
            if(@$env['addgroup']!=0)
            {
                echo hidden('addgroup',     $env['addgroup']);
            }
            if(@$env['isparent'])
            {
                echo hidden('isparent', $env['isparent']);
            }
            preserve($env,'int,dtc');

            echo para("Group: <b>$name</b>");

            echo <<< WBOX

        <p>
            Select the machines to be included in this group.
            When you are finished selecting the entire group,
            click on the <b>OK</b> button.
        </p>

WBOX;

            $head = explode('|','New|Already Member?|Machine Name');
            $cols = safe_count($head);

            echo okcancel_link(5, $env['custom']);
            echo checkallnone(5);

            echo table_header();
            echo pretty_header($site,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $hid  = @ intval($row['hid']);
                $mid  = $row['mid'];
                $olds = ($hid)?  true  : false;
                $news = ($allc)? true  : $olds;
                $news = ($none)? false : $news;
                $host = disp($row,'host');
                $acts = "mid:$mid, hid:$hid";
                $new  =  checkbox("mid_$mid",$news);
                $old  = dcheckbox("old_$mid",$olds);
                $args = array($new,$old,$host);
                echo table_data($args,0);
            }
            echo table_footer();
            echo checkallnone(5);
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
            echo again($env);
        }
    }


    function wiz_site_chk(&$env,&$mgrp,&$mcat,$db)
    {
        $set  = array( );
        $act  = $env['act'];
        $cid  = $env['cid'];
        $tid  = $env['tid'];
        $dgid = $env['dgid'];
        $site = $env['site'];
        $post = $env['post'];
        debug_note("<b>$act</b> wiz_site_chk $site");

        if ($post != constButtonOk)
        {
            wiz_site_box($env,$mgrp,$mcat,$db);
        }
        else
        {
            $qs  = safe_addslashes($env['site']);
            $sql = "select C.host,\n"
                 . " C.id as mid,\n"
                 . " M.mgmapid as hid\n"
                 . " from (".$GLOBALS['PREFIX']."core.Census as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G)\n"
                 . " left join ".$GLOBALS['PREFIX']."core.MachineGroupMap as M\n"
                 . " on C.censusuniq = M.censusuniq\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " where C.site = '$qs'\n"
                 . " and G.mgroupid = $dgid\n"
                 . " group by C.host\n"
                 . " order by host";
            $set = find_many($sql,$db);
        }
        if (($set) && ($mgrp) && ($mcat))
        {
            $adds = array( );
            $dels = array( );
            $name = $mgrp['name'];
            $stat = "t:$tid,g:$dgid";

            reset($set);
            foreach ($set as $key => $row)
            {
                $hid  = @ intval($row['hid']);
                $mid  = $row['mid'];
                $host = $row['host'];
                $form = UTIL_GetStoredInteger("mid_$mid",0);
                debug_note("host:$host hid:$hid mid:$mid form:$form");

                if (($hid) && (!$form))
                {
                    if (kill_host($dgid,$mid,$db))
                    {
                        $dels[] = $host;
                        $text = "groups: remove machine ($stat) $host at $site from $name";
                                                debug_note($text);
                    }
                }
                if ((!$hid) && ($form))
                {
                    if (build_host($tid,$dgid,$mid,$db))
                    {
                        $adds[] = $host;
                        $text = "groups: add machine ($stat) $host at $site to $name";
                                                debug_note($text);
                    }
                }
            }
            if ($adds)
            {
                $num = safe_count($adds);
                $who = ($num == 1)? 'this machine' : 'these machines';
                $txt = join(',',$adds);
                echo "<p>You have successfully added $who"
                  .  " to the group <b>$name</b>:</p>\n"
                  .  "<p>$txt.</p>\n";
            }

            if ($dels)
            {
                $num = safe_count($dels);
                $who = ($num == 1)? 'this machine' : 'these machines';
                $txt = join(',',$dels);
                echo "<p>You have successfully removed $who"
                  .  " from the group <b>$name</b>:</p>\n"
                  .  "<p>$txt.</p>\n";
            }
            wiz_site_frm($env,$mgrp,$mcat,$db);
        }
    }



    function wiz_conf_del(&$env, &$mgrp, $db)
    {
        $group_name = $mgrp['name'];
        $mgroupid   = $mgrp['mgroupid'];
        $pre_n      = preserve_notification_state($env['notification_id'],
                                                  $env['notification_act']);
        $pre_q      = preserve_report_state($env['report_id'],
                                            $env['report_act']);
        $pre_a      = preserve_asset_state($env['asset_id'],
                                           $env['asset_act']);
        $auth       = $env['auth'];
        $admin      = $env['admn'];
        $debug      = $env['debug'];
        $self       = $env['self'];
        $int        = $env['int'];
        $dtc        = $env['dtc'];
        $scop       = $env['scop'];
        $custom     = $env['custom'];
        $act        = $env['act'];
        $tid        = $env['tid'];
        $dgid       = $env['dgid'];

        $cmd        = "$self?act=$act&scop=$scop&dtc=$dtc&int=$int"
                    . "&custom=$custom&$pre_n&$pre_q&$pre_a";
        $rref       = "$cmd&sub=rtru&tid=$tid&dgid=$dgid&$pre_n";
        $back       = "$self?act=wmgs&custom=$custom&$pre_n";

        $cncl_link  = html_link($back, '[No]');
        $in         = indent(5);
        $msg_def    = "Are you sure you want to remove the group <b>$group_name</b>?";
        $msg_rem    = '';


        $set = GRPS_is_mgroupid_in_use($mgroupid, $auth,
                                       constEventNotifications, $db);

        $out = GRPS_build_inuse_list($set, constEventNotifications,
                                     $group_name);
        $ntfy_str  = $out['str'];
        $rref     .= $out['rref'];
        $ntfy_rem  = $out['msg_rem'];

        $set = GRPS_is_mgroupid_in_use($mgroupid, $auth, constEventReports,
                                       $db);

        $out = GRPS_build_inuse_list($set, constEventReports, $group_name);

        $report_str  = $out['str'];
        $rref       .= $out['rref'];
        $report_rem  = $out['msg_rem'];

        $set = GRPS_is_mgroupid_in_use($mgroupid, $auth, constAssetReports,
                                       $db);

        $out = GRPS_build_inuse_list($set, constAssetReports, $group_name);

        $asset_str = $out['str'];
        $rref     .= $out['rref'];
        $asset_rem = $out['msg_rem'];

        if(PHP_REPF_CheckDeleteMachineGroup(CUR, $canDelete, $usedItems,
            $mgrp['mgroupuniq'])!=constAppNoErr)
        {
            echo 'Error checking new reports.<br>';
        }

        $remv_link  = html_link($rref, '[Yes]');

        echo $ntfy_rem;
        echo '<br>';
        echo $ntfy_str;
        echo '<br><br>';
        echo $report_rem;
        echo '<br>';
        echo $report_str;
        echo '<br><br>';
        echo $asset_str;
        echo '<br><br>';
        echo $asset_rem;
        echo '<br><br>';
        if(!($canDelete))
        {
            echo "Removing the group <b>$group_name</b>"
                . " will also remove it from the following sections:<ul>";
            echo $usedItems;
            echo '</ul><br><br>';
        }
        echo $msg_def;

        $sql = 'SELECT valmapid FROM '.$GLOBALS['PREFIX'].'core.ValueMap LEFT JOIN '.$GLOBALS['PREFIX'].'core.MachineGroups ON ('
            . 'ValueMap.mgroupuniq=MachineGroups.mgroupuniq) WHERE MachineGroups.name=\''
            . safe_addslashes($group_name) . '\'';
        $maps = find_many($sql, $db);
        if(($maps) && (safe_count($maps)>0))
        {
            echo '<p>Warning!  This group is actively used for DART policies.  Maps will '
                . 'be adjusted accordingly.</p>';
        }

        echo "<p>${in}${remv_link}${in}${cncl_link}</p>";
    }


    function wiz_site_del(&$env,&$mgrp,&$mcat,$db)
    {
        $num  = 0;
        $set  = array( );
        $cid  = $env['cid'];
        $site = $env['site'];
        $dgid = $env['dgid'];
        if (($cid) && ($mgrp) && ($site))
        {
            $name = $mgrp['name'];
            $qs   = safe_addslashes($site);
            $sql  = "select id from ".$GLOBALS['PREFIX']."core.Census\n"
                  . " where site = '$qs'";
            $tmp  = find_many($sql,$db);
            $set  = distinct($tmp,'id');

            if ($set)
            {
                $txt = join(',',$set);
                $sql = "select mgmapid, ".$GLOBALS['PREFIX']."core.MachineGroupMap.censusuniq, "
                    .$GLOBALS['PREFIX']."core.MachineGroupMap.mgroupuniq from "
                    .$GLOBALS['PREFIX']."core.MachineGroupMap left join "
                    .$GLOBALS['PREFIX']."core.MachineGroups on (".$GLOBALS['PREFIX']."core.MachineGroupMap.mgroupuniq="
                    .$GLOBALS['PREFIX']."core.MachineGroups.mgroupuniq) left join ".$GLOBALS['PREFIX']."core.Census "
                    . "on(".$GLOBALS['PREFIX']."core.MachineGroupMap.censusuniq=".$GLOBALS['PREFIX']."core.Census."
                    . "censusuniq) where mgroupid=$dgid and id in ($txt)";
                $set = find_many($sql, $db);
                if($set)
                {
                    foreach ($set as $key => $row)
                    {
                        if(!VARS_HandleDeletedGroup($row['censusuniq'], $row['mgroupuniq'], $db))
                        {
                                                        return;
                        }
                    }
                }
                $set = DSYN_DeleteSet($sql, constDataSetCoreMachineGroupMap,
                    "mgmapid", "wiz_site_del", 0, 1,
                    constOperationPermanentDelete, $db);

                $sql = "delete from ".$GLOBALS['PREFIX']."core.MachineGroupMap\n"
                    . " using ".$GLOBALS['PREFIX']."core.MachineGroupMap left join "
                    .$GLOBALS['PREFIX']."core.MachineGroups on (".$GLOBALS['PREFIX']."core.MachineGroupMap.mgroupuniq="
                    .$GLOBALS['PREFIX']."core.MachineGroups.mgroupuniq) left join ".$GLOBALS['PREFIX']."core.Census "
                    . "on(".$GLOBALS['PREFIX']."core.MachineGroupMap.censusuniq=".$GLOBALS['PREFIX']."core.Census."
                    . "censusuniq) where mgroupid = $dgid and\n"
                    . " id in ($txt)";
                if($set)
                {
                    $res = redcommand($sql,$db);
                    $num = affected($res,$db);
                    if ($num)
                    {
                        $env['allowdelete'] = 1;
                        $text = "groups: removed $num machines at $site from "
                            . "$name";
                                                debug_note($text);
                    }
                }
            }
            if ($num)
            {
                $text = plural($num,'machine');
                echo para("Site <b>$site</b> ($text) removed from group <b>$name</b>.");
            }
            else
            {
                para("Group <b>$name</b> unchanged.");
            }
        }
        wiz_site_frm($env,$mgrp,$mcat,$db);
    }


    function wiz_mgrp_del(&$env,&$mgrp,&$mcat,$db)
    {
        if ($mgrp)
        {
            $auth = $env['auth'];
            $rnid = $env['notification_remove_id'];
            $enid = $env['report_remove_id'];
            $anid = $env['asset_remove_id'];
            $name = $mgrp['name'];
            $gid  = $mgrp['mgroupid'];
            kill_gid($gid,$db);
            delete_expr_gid($gid,$db);
            if (delete_mgrp_gid($gid,$db))
            {
                if ($rnid)
                {

                    GRPS_remove_mgroupid_from_event($gid, $rnid,
                                                    constEventNotifications,
                                                    $db);
                }
                if ($enid)
                {

                    GRPS_remove_mgroupid_from_event($gid, $enid,
                                                    constEventReports, $db);
                }
                if ($anid)
                {

                    GRPS_remove_mgroupid_from_event($gid, $anid,
                                                    constAssetReports, $db);
                }
                $num  = delete_host_gid($gid,$db);
                $stat = "g:$gid,n:$num,u:$auth";
                $text = "groups: mgrp removed ($stat) $name";
                                debug_note($text);
                echo para("Machine group <b>$name</b> has been removed.");
            }
        }
        $env['dgid'] = 0;
        wiz_mgrp_select($env,$mcat,$db);
    }




    function wiz_pop(&$env,$db)
    {
        $act  = $env['act'];
        $tid  = $env['tid'];
        $sub  = $env['sub'];
        $dgid = $env['dgid'];
        debug_note("<b>$act</b> wiz_pop <b>$sub</b> tid:$tid, dgid:$dgid");

        $mgrp = find_mgrp_gid($dgid, constReturnGroupTypeOne, $db);
        $mcat = find_mcat_tid($tid,$db);
        if (($mgrp) && ($mcat))
        {
            switch ($sub)
            {
                case 'remv': wiz_conf_del($env,$mgrp,$db);       break;
                case 'rtru': wiz_mgrp_del($env,$mgrp,$mcat,$db); break;
                case 'call': wiz_site_add($env,$mgrp,$mcat,$db); break;
                case 'cnon': wiz_site_del($env,$mgrp,$mcat,$db); break;
                case 'cind': wiz_site_box($env,$mgrp,$mcat,$db); break;
                case 'chck': wiz_site_chk($env,$mgrp,$mcat,$db); break;
                default    : wiz_site_frm($env,$mgrp,$mcat,$db); break;
            }
        }
    }


    function radio_tag($tag,$val,$sel,$txt)
    {
        $in = indent(5);
        $rd = radio($tag,$val,$sel);
        echo "${in}${rd}${txt}<br>\n";
    }


    function wiz_mgrp_name(&$env,&$mcat,$db)
    {
        debug_note("wiz_mgrp_name()");
        $tid = $mcat['mcatid'];
        $txt = textbox('name',60,'');
        $chk = checkbox('glob',0);
        echo again($env);
        echo post_self('myform');
        echo hidden('act',$env['act']);
        echo hidden('frm',$env['act']);
        echo hidden('ctl',0);
        echo hidden('pcn','wiz');
        echo hidden('tid',$tid);
        echo hidden('gid', -1);
        echo hidden('scop',constScopGroup);
        echo hidden('custom',$env['custom']);
        echo hidden('notification_id' ,$env['notification_id']);
        echo hidden('notification_act',$env['notification_act']);
        echo hidden('report_id' ,      $env['report_id']);
        echo hidden('report_act',      $env['report_act']);
        echo hidden('asset_id',        $env['asset_id']);
        echo hidden('asset_act',       $env['asset_act']);
        if(@$env['level'])
        {
            echo hidden('level',        $env['level']);
        }
        if(@$env['isparent'])
        {
            echo hidden('isparent', $env['isparent']);
        }
        preserve($env,'dtc,int');
        echo para('What would you like to name this new group?');
        echo para("Group name: $txt");
        echo para("Allow other users to use this group? $chk");
        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }


    function wizard_mgrp(&$env,$db)
    {
        debug_note('wizard_mgrp()');
        $tid  = 0;
        $cat  = constMcSCOP;
        $mcat = wiz_make_mcat($cat,$db);

        $gid  = $env['gid'];
        $act  = $env['act'];
        $dgid = $env['dgid'];
        $name = $env['name'];
        debug_note("<b>$act</b> wizard_mgrp");

        if (($mcat) && (!$gid) && (!$dgid))
        {
            wiz_mgrp_select($env,$mcat,$db);
        }
        if (($mcat) && (!$dgid) && ($gid) && (!$name))
        {
            wiz_mgrp_name($env,$mcat,$db);
        }
        if (($mcat) && ($gid) && (!$dgid) && ($name))
        {
            $tid  = $mcat['mcatid'];
            $mgrp = wiz_make_mgrp($env,$name,$tid,$db);
            if ($mgrp)
            {
                $env['dgid'] = $mgrp['mgroupid'];
                $env['addgroup'] = $mgrp['mgroupid'];
                $env['allowdelete'] = 1;
                wiz_pop($env,$db);
            }
        }
        if (($mcat) && ($dgid))
        {
            wiz_pop($env,$db);
        }
    }



    function wizard_scop(&$env,$txt,$sub,$db)
    {
        $mgroupid = 0;
        $act    = $env['act'];
        $adm    = $env['admn'];
        $custom = $env['custom'];
        $all  = (($adm) && (!$sub));
        $min  = ($all)? 1 : 2;
        $scop = value_range($min,5,$env['scop']);
        debug_note("<b>$act</b> wizard_scop sub:$sub");
        $in = indent(5);
        $j1 = radio('scop',constScopAll,   $scop);
        $j2 = radio('scop',constScopUser,  $scop);
        $j3 = radio('scop',constScopSite,  $scop);
        $j4 = radio('scop',constScopHost,  $scop);
        $j5 = radio('scop',constScopGroup, $scop);
        $remainCats = '';
        if(($adm) && ($custom==constPageEntryScrpConf))
        {
            $j6 = radio('scop',constScopOS,$scop);
            $j7 = radio('scop',constScopOSSite,$scop);
            $j8 = radio('scop',constScopLocalNet,$scop);

            $remainCats = <<< REMAINCATS

            ${in}$j6 A specific OS<br>
            ${in}$j7 A specific OS and Site<br>
            ${in}$j8 A local network<br>

REMAINCATS;

        }
        $o1 = '';
        if ($all)
        {
            $tag = "All machines<br>\n";
            $rad = ($adm)? $j1 : $j2;
            $o1  = "${in}$rad $tag";
        }

        $again = GRPS_display_again($custom)? again($env) : '';
        echo $again;
        echo post_self('myform');
        echo hidden('act',$act);
        echo hidden('frm',$act);
        echo hidden('ctl',0);
        echo hidden('pcn','wiz');
        echo hidden('custom',$custom);
        if(@$env['level'])
        {
            echo hidden('level', $env['level']);
        }
        if(@$env['isparent'])
        {
            echo hidden('isparent', $env['isparent']);
        }
        $title = GRPS_create_wizard_title($custom);
        preserve($env,'dtc,int');

        echo <<< SCOP

        $txt

        <p>$title</p>
        $o1
        ${in}$j3 A single site<br>
        ${in}$j4 A single machine<br>
        ${in}$j5 A group of machines<br>
        $remainCats

SCOP;

        echo '<br>';
        GRPW_display_wizard_buttons($custom, $mgroupid, $env);
        echo form_footer();
        $again = GRPS_display_again($custom)? again($env) : '';
        echo $again;
    }



    function GRPW_display_wizard_buttons($custom, $mgroupid, $env = array())
    {
        switch ( $custom )
        {
	    case constDashStatus_SelectMachineGroup :
	        $nxt = ok_link($custom, $mgroupid);
		$can = cancel_link(constDashStatus_SelectMachineGroup, $env);
		GRPW_display_buttons('', $nxt, $can);
	    break;

            case constPageEntryScrpConf :
                $nxt = next_cancel(constNext);
                $can = cancel_link(constPageEntryScrpConf, $env);
                GRPW_display_buttons('', $nxt, $can);
            break;

            default :
                echo okcancel_link(5, $custom);
            break;
        }
    }

    function GRPW_display_buttons($bck, $nxt, $can)
    {
        echo '<table>'
           . '<tr>'
           . "<td>$bck</td>"
           . '<td>&nbsp</td>'
           . "<td>$nxt</td>"
           . '<td>&nbsp</td>'
           . "<td>$can</td>"
           . '</tr>'
          . '</table>';
    }


    function GRPW_display_reset_button( $rset )
    {
        echo '<table>'
	   . '<tr>'
	   . "<td>$rset</td>"
           . '</tr>'
	   . '</table>';
    }




    function wiz_generic_disp(&$env,$name,$txt,$sub,$db)
    {
        if (($sub) && ($env['scop'] == constScopAll))
        {
            $env['scop'] = constScopNone;
        }
        if (legal_scope($env,$db))
        {
            $form = 'wiz_' . $name . '_form';
            $acts = 'wiz_' . $name . '_act';
            $post = $env['post'];
            $ctl  = $env['ctl'];
            if (($ctl) && ($post == constButtonOk))
            {
                $acts($env,$db);
            }
            else
            {
                $form($env,$db);
            }
        }
        else
        {
            switch ($env['scop'])
            {
                case constScopSite : wizard_site($env,$db);            break;
                case constScopHost : wizard_host($env,$db);            break;
                case constScopGroup: wizard_mgrp($env,$db);            break;
                default            : wizard_scop($env,$txt,$sub,$db);  break;
            }
        }
    }


    function wiz_prop_disp(&$env,$db)
    {
        debug_note('<b>wprp</b> wiz_prop_disp');
        $txt = <<< PROP

        <p>
          This wizard lets you define and implement an update download,
          retention and propagation policy covering one, some, or
          all machines at your sites.
        </p>

        <p>
          You can control the amount of bandwidth consumed by the update
          download process by selecting a subset of systems at each site to
          download updates.
        </p>

        <p>
          By controlling the amount of time downloaded updates are kept on
          the systems that downloaded them, you can ensure that all
          systems will receive the updates while at the same time minimizing
          the amount of local storage taken up by updates.
        </p>

        <p>
          For example, if you want three systems at each of your sites to
          download updates and keep them for two weeks, you would run the
          Download and Propagation Management wizard twice.
        </p>

        <p>
          The first time, you would apply it to all machines selecting the
          <i>Only retrieved from local machines</i> option, and disabling
          the retention policy (next page).
        </p>

        <p>
          The second time, you would apply the wizard to a group of machines
          whose members are three systems from each site. For the group, you
          would select the <i>Only downloaded from vendor</i> option together
          with a two-week update retention policy (next page).
        </p>

PROP;

        wiz_generic_disp($env,'prop',$txt,0,$db);
    }


    function wiz_meth_stat($good,$ins)
    {
        if ($good)
        {
            $app = 'Approve updates';
            $dec = 'Decline updates';
            $txt = 'Unknown schedule';
            if ($ins == constPatchInstallNever)
            {
                $txt = 'New updates will not be'
                     . ' applied until you appr'
                     . 'ove them, using the <i>'
                     . "$app</i> wizard.";
            }
            if ($ins == constPatchScheduleInstall)
            {
                $txt = 'New updates will be applied'
                     . ' automatically unless you'
                     . ' decline them, using the'
                     . " <i>$dec</i> wizard.";
            }
        }
        else
        {
            $txt = 'Nothing has changed.';
        }
        echo para($txt);
    }


    function wiz_meth_fin(&$env,&$mgrp,&$pgrp,&$pcfg,$db)
    {
        echo again($env);

        $good = true;
        $pid = $pcfg['pconfigid'];
        $ins = check_install($env['ins']);
        $txt = installation($ins);
        if ($ins == constPatchScheduleInstall)
        {
            update_shed($env,$pcfg);
        }

        $pcfg['installation'] = $ins;
        if (update_pcfg($env,$pid,$pcfg,$db))
        {
            $good = true;
        }

        $mgnm = $mgrp['name'];
        $pgnm = $pgrp['name'];
        $jid  = $pcfg['pgroupid'];
        $gid  = $pcfg['mgroupid'];
        $stat = "g:$gid,j:$jid,p:$pid,i:$ins";
        $text = "patch: wizard method ($stat) '$mgnm' / '$pgnm'";
                debug_note($text);

        wiz_meth_stat($good,$ins);
        wizard_return($env,$db);
        echo again($env);
    }


    function wiz_meth_err(&$env,$db)
    {
        echo again($env);
        echo para('There has been some sort of an error.');
        wizard_return($env,$db);
        echo again($env);
    }


    function pcfg_info(&$env,$db)
    {
        $info = array();
        $jid  = $env['jid'];
        $gid  = $env['gid'];
        $mgrp = find_mgrp_own($env,$gid,$db);
        $pgrp = find_pgrp_jid($jid,$db);
        $pcfg = find_pcfg($gid,$jid,$db);
        if (($mgrp) && ($pgrp) && ($pcfg))
        {
            $info['mgrp'] = $mgrp;
            $info['pgrp'] = $pgrp;
            $info['pcfg'] = $pcfg;
        }
        return $info;
    }




    function ident_shed($env, &$info, $db)
    {
        $in   = indent(5);
        $mnam = $info['mgrp']['name'];

        $sql = "SELECT PatchGroups.name FROM PatchConfig LEFT JOIN PatchGroups"
            . " ON (PatchConfig.pgroupid=PatchGroups.pgroupid) WHERE "
            . " PatchConfig.wpgroupid=" . $env['wpgroupid'] . " AND "
            . " PatchConfig.mgroupid=" . $info['mgrp']['mgroupid'];
        $set = find_many($sql, $db);
        $pnam = '';
        foreach ($set as $key => $row)
        {
            if(strcmp($pnam, ''))
            {
                $pnam .= ", ";
            }
            $pnam .= $row['name'];
        }

        echo <<< ZZZZ

        <b>Groups</b><br>
        ${in}Machine: <b>$mnam</b><br>
        ${in}Update: <b>$pnam</b>
        <br><br>
ZZZZ;

    }


    function edit_shed(&$env,$db)
    {
        echo again($env);
        $info = pcfg_info($env,$db);
        if ($info)
        {
            $pcfg = $info['pcfg'];
            ident_shed($env, $info, $db);
            echo post_self('myform');
            echo hidden('act','sent');
            echo hidden('frm','shed');
            echo hidden('pcn','wsts');
            preserve($env,'jid,gid,wpgroupid');
            schedule_common($pcfg);
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        echo again($env);
    }




    function update_shed(&$env,&$pcfg)
    {
        $nadv = value_range(0,  1,$env['nadv']);
        $rusr = value_range(0,  1,$env['rusr']);
        $pshd = value_range(0,  1,$env['pshd']);
        $nsch = value_range(0,  1,$env['nsch']);
        $mins = value_range(0,999,$env['mins']);

        $styp = value_range(1,  2,$env['styp']);
        $swek = value_range(0,  7,$env['swek']);
        $smon = value_range(0, 12,$env['smon']);
        $shor = value_range(0, 24,$env['shor']);
        $sday = value_range(0, 31,$env['sday']);
        $smin = value_range(0, 60,$env['smin']);
        $sdel = value_range(0, 99,$env['sdel']);
        $srnd = value_range(0,999,$env['srnd']);

        $ntyp = value_range(1,  2,$env['ntyp']);
        $nwek = value_range(0,  7,$env['nwek']);
        $nmon = value_range(0, 12,$env['nmon']);
        $nhor = value_range(0, 24,$env['nhor']);
        $nday = value_range(0, 31,$env['nday']);
        $nmin = value_range(0, 60,$env['nmin']);
        $nfal = value_range(0, 99,$env['nfal']);
        $nrnd = value_range(0,999,$env['nrnd']);
        $nat  = $mins * 60;
        $ssec = $sdel * 86400;

        $pcfg['schedday']    = $sday;
        $pcfg['schedhour']   = $shor;
        $pcfg['schedweek']   = $swek;
        $pcfg['schedtype']   = $styp;
        $pcfg['scheddelay']  = $ssec;
        $pcfg['schedmonth']  = $smon;
        $pcfg['schedminute'] = $smin;
        $pcfg['schedrandom'] = $srnd;

        $pcfg['notifyday']    = $nday;
        $pcfg['notifyhour']   = $nhor;
        $pcfg['notifyweek']   = $nwek;
        $pcfg['notifyfail']   = $nfal;
        $pcfg['notifytype']   = $ntyp;
        $pcfg['notifydelay']  = $ssec;
        $pcfg['notifymonth']  = $nmon;
        $pcfg['notifyminute'] = $nmin;
        $pcfg['notifyrandom'] = $nrnd;

        $pcfg['reminduser']        = $rusr;
        $pcfg['notifytext']        = $env['txt'];
        $pcfg['notifyadvance']     = $nadv;
        $pcfg['notifyschedule']    = $nsch;
        $pcfg['preventshutdown']   = $pshd;
        $pcfg['notifyadvancetime'] = $nat;

    }

    function save_shed(&$env,$db)
    {
        echo again($env);
        $text = "Nothing has changed";
        $info = pcfg_info($env,$db);
        if ($info)
        {
            ident_shed($env, $info, $db);
            $pid  = $info['pcfg']['pconfigid'];
            $pcfg = $info['pcfg'];
            update_shed($env,$pcfg);
            if (update_pcfg($env,$pid,$pcfg,$db))
            {
                $text = 'Schedule has been updated.';
            }
        }
        echo para($text);
        wizard_stat_return($env,$db);
        echo again($env);
    }

    function wiz_meth_sch(&$env,&$mgrp,&$pgrp,&$pcfg,$db)
    {
        echo again($env);

        echo post_self('myform');
        echo hidden('act','wmth');
        echo hidden('frm','wmth');
        echo hidden('pcn','wiz');
        preserve($env,'scop,cid,hid,gid,ins');
        echo hidden('sub', 'fin');
        echo hidden('ctl','1');

        echo <<< WMTH

        <p>
          Since you have selected the <b>All updates
          approved automatically</b> update method,
          you need to define a schedule for applying the
          updates, as well as any notification that
          will be issued to end-users before updates
          are installed.
        </p>

        <p>
          IMPORTANT: Please keep in mind that the schedule and
          notification configuration settings on this page apply
          to <b>ALL</b> systems at <b>ALL</b> sites. They are the
          <i>global defaults</i>, and we will refer to them as such
          wherever we discuss schedule and notification configurations.
        </p>

        <p>
          If you change either the <i>global default</i> software update
          operation schedule, or the schedule of individual software
          update operations, the new schedule applies only to software
          update operations whose scheduled execution time, without
          taking into account the delay, has not yet occurred. For
          example, suppose software update operation A is scheduled
          to occur at time <b>X</b> with a delay of <b>Y</b> (i.e.
          It's scheduled to occur at time <b>X+Y</b>).
          If time <b>X</b> has already gone by, changing software update
          operation A's scheduled execution time to a time after <b>X</b>
          but before <b>Y</b> will not change its execution time. It will
          still take place at time <b>X+Y</b>.
        </p>

WMTH;
        schedule_common($pcfg);

        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }


    function user_pcfg($auth,$build,$db)
    {
        $mgrp = array( );
        $pgrp = array( );
        $pcfg = array( );
        $save = false;
        if ($auth)
        {
            $name = mgrp_user($auth);
            $mgrp = find_mgrp_name($name,$db);
            $pgrp = find_pgrp_name(constPatchAll,$db);
        }
        if (($mgrp) && ($pgrp))
        {
            $gid  = $mgrp['mgroupid'];
            $jid  = $pgrp['pgroupid'];
            $pcfg = find_pcfg($gid,$jid,$db);

            if ((!$pcfg) && ($build))
            {
                $save = true;
                $mall = find_mgrp_name(constCatAll,$db);
                $pgid = ($mall)? $mall['mgroupid'] : 0;
                $pcfg = find_pcfg($pgid,$jid,$db);
            }
            if ((!$pcfg) && ($build))
            {
                $save = true;
                $pcfg = default_pcfg();
            }
            if (($save) && ($build))
            {
                $pid = insert_pcfg($gid,$jid,$pcfg,$jid,$db);
                if ($pid)
                {
                    debug_note("created pcfg (p:$pid,g:$gid,j:$jid) for $name");
                    $pcfg['pconfigid'] = $pid;
                    $pcfg['mgroupid']  = $gid;
                    $pcfg['pgroupid']  = $jid;
                }
            }
        }
        return $pcfg;
    }


    function seek_pcfg($auth,$db)
    {
        $pcfg = user_pcfg($auth,false,$db);
        if (!$pcfg)
        {
            $pcfg = find_glbl_pcfg($db);
        }
        if (!$pcfg)
        {
            $pcfg = default_pcfg();
        }
        return $pcfg;
    }


    function wiz_meth_act(&$env,$db)
    {
        $save = false;
        $good = false;
        $pcfg = array( );
        $pgrp = array( );
        $gcfg = find_glbl_pcfg($db);
        $mgrp = find_scop_mgrp($env,$db);
        if (($mgrp) && ($gcfg))
        {
            $gid  = $mgrp['mgroupid'];
            $jid  = $gcfg['pgroupid'];
            $pgrp = find_pgrp_jid($jid,$db);
            $pcfg = find_pcfg($gid,$jid,$db);
            $good = legal_mgrp($env,$gid,$db);
            if (!$pcfg)
            {
                $save = true;
                $auth = $env['auth'];
                $pcfg = seek_pcfg($auth,$db);
            }
        }

        if (($good) && ($mgrp) && ($pgrp) && ($pcfg))
        {
            $ins = $env['ins'];
            $sub = $env['sub'];
            if (($ins == constPatchScheduleInstall) && ($sub == 'sch'))
            {
                wiz_meth_sch($env,$mgrp,$pgrp,$pcfg,$db);
            }
            else
            {
                if ($save)
                {
                    $pid = insert_pcfg($gid,$jid,$pcfg,$jid,$db);
                    if ($pid)
                    {
                        $pcfg['mgroupid']  = $gid;
                        $pcfg['pgroupid']  = $jid;
                        $pcfg['pconfigid'] = $pid;
                    }
                    else
                    {
                        $pcfg = array();
                    }
                }
                if ($pcfg)
                {
                    wiz_meth_fin($env,$mgrp,$pgrp,$pcfg,$db);
                }
                else
                {
                    wiz_meth_err($env,$db);
                }
            }
        }
        else
        {
            if ($good)
            {
                wiz_meth_err($env,$db);
            }
            else
            {
                echo again($env);
                echo mgrp_error();
                wizard_return($env,$db);
                echo again($env);
            }
        }
    }


    function meth_disp(&$pcfg)
    {
        $ins = check_install($pcfg['installation']);
        $ins = value_range(1,4,$ins);

        $in = indent(5);
        $i1 = radio('ins',constPatchInstallNever,$ins);
        $i4 = radio('ins',constPatchScheduleInstall,$ins);
        $i5 = radio('ins',constPatchScheduleRemove,$ins);

        $d1 = 'Manually approve updates';
        $d4 = 'All updates approved automatically';

        echo <<< WMTH

        <p>
          Decide how <b>new</b> software updates should be installed on
          <b>all</b> machines you selected, and how <b>existing</b>
          software updates should be installed on <b>new</b> machines,
          where they have not been downloaded or installed.
        </p>

        <p>
          Existing software updates are those that have already been
          detected, downloaded, scheduled to be installed, or installed
          on at least one machine.
        </p>

        <p>
          <b>Please note that updates already scheduled to be installed,
          or installed, on existing machines are not affected by
          your selection.</b>
        </p>

        <p>
          If you select <i>$d4</i> then new updates will be installed
          automatically unless you decline them using the <i>Decline
          updates</i> wizard. This is the setting to use if you want
          to have updates installed most of the time, while still
          being able to occasionally decline the installation of a
          software update.
        </p>

        <p>
          After you select <i>$d4</i> you will be asked to select a
          schedule for installing software updates and, optionally,
          configure end-user notifications. These schedule and
          notification configuration settings apply to <b>ALL</b>
          systems at <b>ALL</b> sites. They are the <i>global
          defaults</i>.
        </p>

        <p>
          If you select <i>$d1</i> then new updates will not be
          installed until you approve them.
        </p>

        <b>Select Update Method:</b><br>
        ${in}$i4 $d4<br>
        ${in}$i1 $d1<br>
WMTH;

    }


    function wiz_dash_finish()
    {
        $link = cancel_link(constDashStatus_SelectMachineGroup);
	return $link;
    }


    function wiz_tools_finish()
    {
        $link = html_link('../config/groups.php?custom=3',
            'Return to group management');
        echo para($link);
        $link = html_link('../config/index.php?act=wiz',
            'Go to management wizards');
        echo para($link);
    }


    function wiz_asset_finish()
    {
        $a_url = return_asset_url();
        $link  = html_link("../asset/report.php?$a_url",
                        'Return to the Asset Reports page.');
        return para($link);
    }


    function wiz_notfy_finish()
    {
        $n_url = return_notification_url();
        $link  = html_link("../event/notify.php?$n_url",
                           'Return to the Notifications page.');
        return para($link);
    }


    function wiz_report_finish()
    {
        $r_url = return_report_url();
        $link  = html_link("../event/report.php?$r_url",
                           'Return to the Reports page.');
        return para($link);
    }

    function wiz_meth_ins(&$env,&$mgrp,&$pgrp,&$pcfg,$db)
    {
        echo again($env);
        if (($mgrp) && ($pgrp) && ($pcfg))
        {
            echo post_self('myform');
            echo hidden('act','wmth');
            echo hidden('frm','wmth');
            echo hidden('pcn','wiz');
            preserve($env,'scop,cid,hid,gid');
            echo hidden('sub', 'sch');
            echo hidden('ctl','1');
            meth_disp($pcfg);
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        echo again($env);
    }


    function meth_form(&$env,$db)
    {
        echo again($env);
        $info = pcfg_info($env,$db);
        if ($info)
        {
            $act  = $env['act'];
            $pcfg = $info['pcfg'];
            $name = $info['mgrp']['name'];
            echo "<h3>$name</h3>\n";
            echo post_self('myform');
            echo hidden('act','mset');
            echo hidden('frm',$act);
            echo hidden('pcn','wsts');
            preserve($env,'gid,jid');
            meth_disp($pcfg);
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        echo again($env);
    }


    function meth_set(&$env,$db)
    {
        echo again($env);
        $num  = 0;
        $ins  = $env['ins'];
        $info = pcfg_info($env,$db);
        $good = false;
        if ($info)
        {
            $name = $info['mgrp']['name'];
            $pcfg = $info['pcfg'];
            echo "<h3>$name</h3>\n";
            $ins  = check_install($ins);
            $pcfg['installation'] = $ins;
            $pid  = $pcfg['pconfigid'];
            $num  = update_pcfg($env,$pid,$pcfg,$db);
            $good = ($num == 1);
        }
        wiz_meth_stat($good,$ins);
        wizard_stat_return($env,$db);
        echo again($env);
    }


    function wiz_meth_form(&$env,$db)
    {
        $custom = $env['custom'];
        $pcfg   = array( );
        $f_msg  = '';
        $mgrp   = find_scop_mgrp($env,$db);
        $pgrp   = find_pgrp_name(constPatchAll,$db);
        if (($mgrp) && ($pgrp))
        {
            $gid = $mgrp['mgroupid'];
            $jid = $pgrp['pgroupid'];
            $pcfg = find_pcfg($gid,$jid,$db);
            if (!$pcfg)
            {
                $mall = find_mgrp_name(constCatAll,$db);
                $agid = ($mall)? $mall['mgroupid'] : 0;
                $pcfg = find_pcfg($agid,$jid,$db);
            }
            if (!$pcfg)
            {
                $pcfg = default_pcfg();
            }
        }
        if ( ($mgrp) || (($pgrp) && ($pcfg)) )
        {

	    if ($mgrp)
            {
	        $name = $mgrp['name'];
		echo "<p>Updated group '$name'.</p>";
            }

            switch($custom)
            {
                case constPageEntrySites   : ;
                case constPageEntryTools   : wiz_tools_finish();
                break;

	        case constDashStatus_SelectMachineGroup :
		    $f_msg = wiz_dash_finish( );
                break;

                case constPageEntryAsset   : $f_msg = wiz_asset_finish();
                break;

                case constPageEntryReports : $f_msg = wiz_report_finish();
                break;

                case constPageEntryNotfy   : $f_msg = wiz_notfy_finish();
                break;

                default                    : wiz_meth_ins($env,$mgrp,$pgrp,
                                                          $pcfg,$db);
                break;
            }
            echo $f_msg;
        }
        else
        {
            wiz_meth_err($env,$db);
        }
    }

    function wiz_meth_disp(&$env,$db)
    {
        debug_note('<b>wmth</b> wiz_meth_disp');
        wiz_generic_disp($env,'meth','',0,$db);
    }

    function plural($count,$name)
    {
        $text = "$count $name";
        if ($count != 1)
        {
            $text .= 's';
        }
        return $text;
    }

    function delay_time($secs)
    {
        $hh = 3600;
        $dd = $hh * 24;
        $ww = $dd * 7;

        $w = intval($secs / $ww);
        $d = $secs % $ww;
        $d = intval($d / $dd);
        $h = $secs % $dd;
        $h = intval($h / $hh);
        $week = plural($w,'week');
        $hour = plural($h,'hour');
        $days = plural($d,'day');
        return "$week, $days, $hour";
    }





    function wiz_beta_form(&$env,$db)
    {
        echo again($env);

        $ssec = 0;
        $sday = 7;
        $shor = 0;
        $swek = 0;
        $scop = $env['scop'];
        $auth = $env['auth'];
        $gid  = $env['gid'];
        $act  = $env['act'];
        debug_note('<b>wbet</b> wiz_beta_form');

        $set  = beta_candidates($auth,$ssec,$db);
        $mgrp = ($scop)? find_scop_mgrp($env,$db) : find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        $beta = real_beta_group($set,$auth,$ssec,$db);

        if ($ssec)
        {
            $hour = intval($ssec / 3600);
            $days = intval($hour / 24);
            $shor = intval($hour % 24);
            $sday = intval($days % 7);
            $swek = intval($days / 7);
            debug_note("current schedule (s:$ssec,h:$shor,d:$sday,w:$swek)");
        }

        $days = range(0,31);
        $hour = range(0,23);
        $week = range(0,10);

        $sday = html_select('sday',$days,$sday,0);
        $shor = html_select('shor',$hour,$shor,0);
        $swek = html_select('swek',$week,$swek,0);

        if ($scop)
        {
            $act = 'wbet';
            $frm = 'wbet';
            $pcn = 'wiz';
        }
        else
        {
            $act = 'wbac';
            $frm = 'wbfm';
            $pcn = 'wsts';
        }

        if (($set) && (!$beta))
        {
            echo <<< WARP

        <p>
            <b>Warning</b> There is already at least one
            existing test group which is already
            controlling some (meybe all) of your machines.
        </p>

        <p>
            This can happen sometimes when multiple users are
            sharing site access.
        </p>

        <p>
            You may create another test group if you wish,
            but this may lead to unexpected results.
        </p>

WARP;

        }


        echo post_self('myform');
        echo hidden('act',$act);
        echo hidden('frm',$frm);
        echo hidden('pcn',$pcn);
        echo hidden('ctl',1);
        preserve($env,'scop,cid,hid,gid');

        echo <<< WBET

        <p>
          The machines that you just selected will have updates installed
          on them according to the schedule defined for the installation
          of updates at your sites.
        </p>

        <p>
          The default update installation schedule is <b>3 AM every day</b>.
          You can change the default schedule by selecting the
          <i>All updates approved automatically</i> option in the
          <i>Select update method</i> wizard.
        </p>


        <p>
          On all machines not included in the test machine group,
          updates will be installed with the delay you specify below.
          During the delay you will be able to identify problems
          caused by updates and, if you so choose, decline them
          for the rest of the machines (the non-test machines)
          using the <i>Decline updates</i> wizard.
        </p>

        <p>
            What delay would you like to use?
        </p>

WBET;

        echo "<table border=\"0\">\n";
        echo double('Delay (hours)',$shor);
        echo double( 'Delay (days)',$sday);
        echo double('Delay (weeks)',$swek);
        echo table_footer();

        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }




    function wiz_beta_act(&$env,$db)
    {
        echo again($env);
        $scop = $env['scop'];
        $auth = $env['auth'];
        $act  = $env['act'];
        $gid  = $env['gid'];
        debug_note("<b>$act</b> wiz_beta_act");
        if ($scop)
        {
            $mgrp = find_scop_mgrp($env,$db);
        }
        else
        {
            $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        }
        $ucfg = user_pcfg($auth,true,$db);
        $sday = value_range(0,31,$env['sday']);
        $shor = value_range(0,23,$env['shor']);
        $swek = value_range(0,10,$env['swek']);
        $hour = 3600;
        $day  = 24 * $hour;
        $week =  7 * $day;
        $ssec = ($shor*$hour) + ($sday*$day) + ($swek * $week);
        $good = false;
        if (($mgrp) && ($ucfg) && ($ssec))
        {
            $save = false;
            $mall = array( );
            $scop = $env['scop'];
            $gid  = $mgrp['mgroupid'];
            $jid  = $ucfg['pgroupid'];
            $pcfg = find_pcfg($gid,$jid,$db);
            if (!$pcfg)
            {
                $save = true;
                $pcfg = $ucfg;
                $pcfg['mgroupid'] = $gid;
                $pcfg['pgroupid'] = $jid;
            }

            $pcfg['installation'] = constPatchScheduleInstall;
            $pcfg['configtype']   = constConfigTypeBeta;
            $pcfg['scheddelay']   = 0;
            $pcfg['notifydelay']  = 0;
            if ($save)
            {
                $pid = insert_pcfg($gid,$jid,$pcfg,$jid,$db);
                $good = ($pid > 0);
            }
            else
            {
                $pid = $pcfg['pconfigid'];
                if (update_pcfg($env,$pid,$pcfg,$db))
                {
                    $good = true;
                }
                else
                {
                    $good = true;
                    debug_note("beta pcfg unchanged");
                }
            }
            if ($good)
            {
                $num  = make_beta($auth,$pid,$ssec,$db);
                $name = $mgrp['name'];
                $stat = "g:$gid,j:$jid,p:$pid,n:$num,u:$auth,s:$ssec";
                $text = "patch: wizard beta ($stat) $name";
                                debug_note($text);

                $txt = delay_time($ssec);
                $who = scop_info($scop,$mgrp);

                echo <<< GOOZ

                <p>
                    $who will install updates as soon as they
                    are available, other machines will install
                    them <b>$txt</b> later.
                </p>
GOOZ;
            }
        }
        else
        {
            echo "<p>Specified groups not found.</p>";
        }
        if ($scop)
            wizard_return($env,$db);
        else
            wizard_stat_return($env,$db);
        echo again($env);
    }



    function wiz_beta_disp(&$env,$db)
    {
        debug_note('<b>wbet</b> wiz_beta_disp');
        $txt = <<< WBET

        <p>
          Select test machines where software updates will be
          installed according to the schedule for the
          installation of software updates at your sites, as configured
          in the <i>Select update method</i> wizard.
        </p>

        <p>
          After selecting the test machines, you will choose a delay
          that will be applied to the installation schedule of software
          updates on all other machines.
        </p>

        <p>
          In this way, you will have a chance to discover problems in a
          controlled environment before software updates are installed
          everywhere.
        </p>

        <p>
          <b>Important notes</b>
        </p>

        <ol>
          <li>
          <p>
            The delay applies to <b>ALL</b> software update operations that
            are performed on a schedule, including software update
            installations and removals. It is important to remember that
            the software update operations that are performed within and
            outside the test group are independent of each other except
            that the latter occur after the delay expires.
          </p>
          </li>
          <li>
          <p>
            If you change the software update delay, the new delay applies
            to all software updates that have not yet been installed outside
            the test group. Note that if you reduce the delay, software
            update operations which have already been delayed for a
            period of time equal to or greater to the new delay will
            take place immediately.
          </p>
          </li>
          <li>
          <p>
            If you change either the <i>global default</i> software update
            operation schedule, or the schedule of individual software
            update operations, the new schedule applies only to software
            update operations whose scheduled execution time, without
            taking into account the delay, has not yet occurred. For
            example, suppose software update operation A is scheduled
            to occur at time <b>X</b> with a delay of <b>Y</b> (i.e.
            It's scheduled to occur at time <b>X+Y</b>).
            If time <b>X</b> has already gone by, changing software update
            operation A's scheduled execution time to a time after <b>X</b>
            but before <b>Y</b> will not change its execution time. It will
            still take place at time <b>X+Y</b>.
          </p>
          </li>
          <li>
          <p>
            If the installation of software updates on systems in the test
            group fails for some reason, after the delay expires software
            update installation on systems outside the test group will
            proceed as scheduled in spite of the failure to install on
            systems in the test group. It is up to you to intervene and take
            corrective action, e.g. use the <i>Decline updates</i> wizard to
            temporarily halt installation.
          </p>
          </li>
          <li>
          <p>
            Should the installation of a software update on systems in the test
            group cause a problem, after the delay expires software update
            installation on systems outside the test group will proceed as
            scheduled unless you intervene and stop the installation by
            using the <i>Decline updates</i> wizard.
          </p>
          </li>
        </ol>

WBET;

        wiz_generic_disp($env,'beta',$txt,1,$db);
    }


    function beta_candidates($auth,&$secs,$db)
    {
        $secs = 0;
        $jid  = 0;
        $set  = array( );
        $pcfg = user_pcfg($auth,true,$db);
        if ($pcfg)
        {
            $secs = $pcfg['scheddelay'];
            $jid  = $pcfg['pgroupid'];
        }

        if (($pcfg) && ($jid))
        {
            debug_note("secs is $secs");
            $qu  = safe_addslashes($auth);
            $bet = constConfigTypeBeta;
            $ins = constPatchScheduleInstall;
            $sql = "select G.mgroupid as gid,\n"
                 . " G.name as grp,\n"
                 . " C.mcatid as tid,\n"
                 . " C.category as cat,\n"
                 . " P.pconfigid as pid from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " PatchConfig as P\n"
                 . " where U.username = '$qu'\n"
                 . " and U.customer = X.site\n"
                 . " and M.censusuniq = X.censusuniq\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and P.mgroupid = G.mgroupid\n"
                 . " and P.pgroupid = $jid\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " and P.configtype = $bet\n"
                 . " and P.installation = $ins\n"
                 . " and P.scheddelay = 0\n"
                 . " group by gid";
            $set = find_many($sql,$db);
        }
        return $set;
    }


    function real_beta_group(&$set,$auth,$sec,$db)
    {
        $num = 0;
        $grp = array();
        reset($set);
        foreach ($set as $key => $row)
        {
            $gid = $row['gid'];
            $tmp = mgrp_alien($gid,$auth,$db);
            if ($tmp)
            {
                $name = $row['grp'];
                debug_note("$name contains alien members");
            }
            else
            {
                $grp = $row;
                $num++;
            }
        }
        $beta = array( );
        if (($grp) && ($num == 1) && ($sec > 0))
        {
            $beta = $grp;
            $beta['secs'] = $sec;
        }
        return $beta;
    }


    function find_beta_group($auth,$db)
    {
        $sec = 0;
        $set = beta_candidates($auth,$sec,$db);
        return real_beta_group($set,$auth,$sec,$db);
    }


    function kill_beta($auth,$db)
    {
        $num = 0;
        $qu  = safe_addslashes($auth);
        $crt = constConfigTypeCritical;
        $nrm = constConfigTypeNormal;
        $sql = "select P.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " PatchConfig as P\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = X.site\n"
             . " and M.censusuniq = X.censusuniq\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " and P.mgroupid = G.mgroupid\n"
             . " and (P.configtype != $crt\n"
             . " or P.scheddelay > 0)\n"
             . " group by P.pconfigid";
        $set = find_many($sql,$db);
        reset($set);
        foreach ($set as $key => $row)
        {
            $pid = $row['pconfigid'];
            $gid = $row['mgroupid'];
            $tmp = mgrp_alien($gid,$auth,$db);
            if (!$tmp)
            {
                $pcfg = $row;
                $pcfg['scheddelay']  = 0;
                $pcfg['notifydelay'] = 0;
                $pcfg['configtype']  = $nrm;
                $fake = array();
                $fake['wpgroupid'] = 0;
                if (update_pcfg($fake,$pid,$pcfg,$db))
                {
                    $num++;
                }
            }
        }
        return $num;
    }


    function make_beta($auth,$pid,$secs,$db)
    {
        $num = 0;
        $qu  = safe_addslashes($auth);
        $crt = constConfigTypeCritical;
        $sql = "select P.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " PatchConfig as P\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = X.site\n"
             . " and M.censusuniq = X.censusuniq\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " and P.mgroupid = G.mgroupid\n"
             . " and P.configtype != $crt\n"
             . " and P.pconfigid != $pid\n"
             . " and P.scheddelay != $secs\n"
             . " group by P.pconfigid";
        $set = find_many($sql,$db);
        reset($set);
        foreach ($set as $key => $row)
        {
            $pid = $row['pconfigid'];
            $gid = $row['mgroupid'];
            $tmp = mgrp_alien($gid,$auth,$db);
            if (!$tmp)
            {
                $pcfg = $row;
                $pcfg['scheddelay']  = $secs;
                $pcfg['notifydelay'] = $secs;
                $fake = array();
                $fake['wpgroupid'] = 0;
                if (update_pcfg($fake,$pid,$pcfg,$db))
                {
                    $num++;
                }
            }
        }
        return $num;
    }


    function wiz_beta_undo(&$env,$db)
    {
        debug_note('<b>wbdo</b> wiz_beta_undo');
        $nadv = $env['nadv'];
        $auth = $env['auth'];
        $beta = find_beta_group($auth,$db);
        $enab = ($beta)? 'Enabled' : 'Disabled';
        if ($nadv)
        {
            $env['act'] = 'wbet';
            wiz_beta_disp($env,$db);
        }
        else
        {
            echo again($env);
            if ($beta)
            {
                $num = kill_beta($auth,$db);
                debug_note("$num pcfg records cleared.");
                if ($num > 0)
                {
                    $enab = 'Disabled';
                }
            }
            echo para("Wizard is now <b>$enab</b>.");
            wizard_return($env,$db);
            echo again($env);
        }
    }




    function wiz_beta_enab(&$env,$db)
    {
        debug_note('<b>wtst</b> wiz_beta_enab');
        echo again($env);
        $in   = indent(5);
        $auth = $env['auth'];
        $beta = find_beta_group($auth,$db);
        $enab = ($beta)? 'Enabled' : 'Disabled';


        if ($beta)
        {
            $grp = $beta['grp'];
            $cat = $beta['cat'];

            $ss = $beta['secs'];
            $hh = intval($ss / 3600);
            $dd = intval($hh / 24);
            $hh = intval($hh % 24);
            $ww = intval($dd / 7);
            $dd = intval($dd % 7);

            debug_note("beta group $grp $ss (w:$ww,d:$dd,h:$hh)");

        }
        $xx = ($beta)? 1 : 0;
        $c1 = checkbox('nadv',$xx);

        echo post_self('myform');
        echo hidden('act','wbdo');
        echo hidden('frm','wtst');
        echo hidden('pcn','wiz');
        echo <<< XXXX

        ${in}$c1 Enable Test Machines Wizard<br>

XXXX;

        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }


    function pcat_update_precedence($kid,$pre,$db)
    {
        $num = 0;
        if (($kid) && ($pre))
        {
            $sql = "update ".$GLOBALS['PREFIX']."softinst.PatchCategories\n"
                 . " set precedence = $pre\n"
                 . " where pcategoryid = $kid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        return $num;
    }

    function pgrp_human($db)
    {
        $out = array(constTagAny);
        $sql = "select pgroupid, name from\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchGroups\n"
             . " where human = 1\n"
             . " order by name";
        $set = find_many($sql,$db);
        foreach ($set as $key => $row)
        {
            $jid  = $row['pgroupid'];
            $out[$jid] = $row['name'];
        }
        return $out;
    }


    function distinct_opts($field,$db)
    {
        $out = array( );
        $out[-1] = constTagNone;
        $out[ 0] = constTagAny;
        $sql = "select distinct $field as tag,\n"
             . " min(patchid) as num from\n"
             . " ".$GLOBALS['PREFIX']."softinst.Patches\n"
             . " group by tag\n"
             . " order by tag";
        $set = find_many($sql,$db);
        foreach ($set as $key => $row)
        {
            $mid = $row['num'];
            $tag = $row['tag'];
            $out[$mid] = ($tag == '')?  '(empty)' : $tag;
        }
        return $out;
    }

    function age_options()
    {
        $out = array( );
        $out[-2] = '(unknown)';
        $out[-1] = constTagNone;
        $out[ 0] = constTagAny;
        $out[ 1] = '(today)';

        $now  = time();
        $noon = midnight($now) + (12*3600);
        $days = array(2,3,4,5,6,7,8,9,10,11,12,13,14,21,28,30,60,90,120,150,180,360,720);

        foreach ($days as $key => $day)
        {
            $time = days_ago($noon,$day);
            $text = date('D m/d',$time) . " ($day days)";
            $out[$day] = $text;
        }
        return $out;
    }

    function size_options()
    {
        return array
        (
            -2 => '(unknown)',
            -1 => constTagNone,
             0 => constTagAny,
             1 => '0 - 100 KB',
             2 => '100 KB - 500 KB',
             3 => '500 KB - 1 MB',
             4 => '1 MB - 10MB',
             5 => '10 MB - 50 MB',
             6 => 'over 50 MB'
        );
    }

    function can_options()
    {
        return array
        (
            -1 => constTagNone,
             0 => constTagAny,
             1 => 'Unknown',
             2 => 'Yes',
             3 => 'No'
        );
    }



    function size_restrict(&$env,&$trm)
    {
        $kb    = 1024;
        $kb100 = $kb * 100;
        $kb500 = $kb * 500;
        $mb    = $kb * $kb;
        $mb10  = $mb * 10;
        $mb50  = $mb * 50;
        $txt   = 'P.size';
        $bet   = "$txt between";
        switch ($env['siz'])
        {
            case -2: $trm[] = "$txt = 0";               break;
            case  1: $trm[] = "$bet 0 and $kb100";      break;
            case  2: $trm[] = "$bet $kb100 and $kb500"; break;
            case  3: $trm[] = "$bet $kb500 and $mb";    break;
            case  4: $trm[] = "$bet $mb and $mb10";     break;
            case  5: $trm[] = "$bet $mb10 and $mb50";   break;
            case  6: $trm[] = "$txt > $mb50";           break;
            default: break;
        }
    }




    function wiz_control(&$env,$pgroupid,$db)
    {
        $norm   = 150;
        $wide   = 5 + ($norm * 2);
        $pgrps  = pgrp_human($db);
        $comps  = distinct_opts('component',$db);
        $plats  = distinct_opts('platform',$db);
        $prios  = distinct_opts('prio',$db);
        $sizes  = size_options();
        $ages   = age_options();
        $ords   = wiz_ords();
        $cans   = can_options();
        $inst   = array(-1 => constTagAny, 0 => 'No', 1=> 'Yes');
        $sub    = button(constButtonSub);
          $act    = $env['act'];
        $href   = 'wu-confg.htm';
        $open   = "window.open('$href','help');";
        $hlp    = click(constButtonHlp,$open);
        $help   = html_page($href,'help');

        $types = PTCH_GetAllTypes();
        $mand = array(-1 => constTagNone,
            0 => constTagAny,
            constPatchMandatoryNo => 'No',
            constPatchMandatoryYes => 'Yes');
        $intprio = array(-1 => constTagNone, 0 => constTagAny);

        $lims = array(5,10,20,25,50,75,100,150,200,250,500,1000);
        $limt = $env['limt'];
        if (!in_array($limt,$lims))
        {
            $lims[] = $limt;
            sort($lims,SORT_NUMERIC);
        }

        $sel_jid  = tiny_select('jid', $pgrps,$env['jid'],1,$norm);
        $sel_size = tiny_select('siz', $sizes,$env['siz'],1,$norm);
        $sel_comp = tiny_select('cmp', $comps,$env['cmp'],1,$norm);
        $sel_plat = tiny_select('plt', $plats,$env['plt'],1,$norm);
        $sel_prio = tiny_select('pri', $prios,$env['pri'],1,$norm);
        $sel_ages = tiny_select('age', $ages, $env['age'],1,$norm);
        $sel_sort = tiny_select('ord', $ords, $env['ord'],1,$wide);
        $sel_cans = tiny_select('can', $cans, $env['can'],1,$norm);
        $sel_dtcs = tiny_select('dtc', $inst, $env['dtc'],1,$norm);
        $sel_inst = tiny_select('int', $inst, $env['int'],1,$norm);
        $sel_type = tiny_select('type', $types, $env['type'],1,$norm);
        $sel_mand = tiny_select('mand', $mand, $env['mand'],1,$norm);
        $sel_intprio = tiny_select('intprio', $intprio, $env['intprio'], 1,
            $norm);
        $sel_limt = tiny_select('limt',   $lims, $limt,0,$norm);

        $pattern  = tinybox('txt',40,$env['txt'],$norm);

        $head = table_header();
        $text = 'Search and Display Options';
        $srch = pretty_header($text,1);
        $in   = indent(5);
        $td   = 'td style="font-size: xx-small"';
        $ts   = $td . ' colspan="2"';

        $typeSelect = GRPW_BuildTypeSelect($env, $pgroupid, $db);

        echo post_self('myform');
        preserve($env,'act,scop,cid,hid,gid');
        echo hidden('frm', $env['act']);

        echo <<< XXXX

        <p>
          You can apply this action to individual updates and/or an
          automatically generated group of updates based on the update
          category.
        </p>

        <p>
          Apply this action automatically to the following type of updates.
          Each update type in the left column below includes mandatory updates.
          For example, "Critical" updates that are "Mandatory" are included in
          the "Critical" type. If you want to only apply this action to
          "Critical" updates that are "Mandatory", check only "Mandatory
          Critical".
        </p>

        <p>
            $typeSelect
        </p>

        <p>
          You can use the options in the <i>Search and Display
          Options</i> panel below to display only the subset
          of updates you want to work on.
        </p>

        <p>
          For example, you might want to only consider updates that
          contain <b>security</b> as part of their name.  Or you
          might be interested only in updates which were downloaded
          today.
        </p>

        <p>
          Please note that clicking on the <b>Search</b> button in the
          <i>Search and Display Options</i> panel clears all selections
          you may have made prior to clicking on the button.
        </p>

        <p>
          Click on this $help link to find out more about each search
          parameter.
        </p>


        $head

        $srch

        <tr><td>

          <table border="0" width="100%">
          <tr valign="bottom">
            <$td>Name Contains  <br>\n$pattern </td>
            <$td>Update Group   <br>\n$sel_jid </td>
            <$ts>Sort By        <br>\n$sel_sort</td>
          </tr>
          <tr valign="bottom">
            <$td>Detected       <br>\n$sel_dtcs</td>
            <$td>Platform       <br>\n$sel_plat</td>
            <$td>Update Size    <br>\n$sel_size</td>
            <$td>Component      <br>\n$sel_comp</td>
          </tr>
          <tr valign="bottom">
            <$td>Installed      <br>\n$sel_inst</td>
            <$td>Uninstallable  <br>\n$sel_cans</td>
            <$td>Priority       <br>\n$sel_prio</td>
            <$td>Date           <br>\n$sel_ages</td>
          </tr>
          <tr valign="botton">
            <$td>Type           <br>\n$sel_type</td>
            <$td>Mandatory      <br>\n$sel_mand</td>
            <$td>Internal Priority<br>\n$sel_intprio</td>
            <$td>Page Size      <br>\n$sel_limt</td>
          </tr>
          <tr valign="bottom" colspan="4">
            <$td>${sub}${in}${hlp}</td>
          </tr>
          </table>

        </td></tr>
        </table>

        <br clear="all">

XXXX;

        echo form_footer();

    }


    function wiz_nothing(&$env,$jid,$db)
    {
        $num = count_table($GLOBALS['PREFIX'].'softinst.Patches',$db);
        if ($num == 0)
        {
            echo para('There are no updates currently defined.');
        }
        else
        {
            echo <<< YYYY

            <p>
                The database currently contains <b>$num</b> update
                records, however none of them match your current search.
                You might try relaxing some of the search criteria.
            </p>

            <p>
                If you would like to see all the available updates,
                just click on the button below.
            </p>
YYYY;
            $go = button('Show All');
            $in = indent(5);

            if($jid)
            {
                GRPW_PrepareWizardUpdateForm($env, $jid, $db);
            }
            else
            {
                echo post_self('myform');
            }
            preserve($env,'act,frm,scop,cid,hid,gid');
            echo hidden('txt',  '');
            echo hidden('jid', '0');
            echo hidden('ord', '0');
            echo hidden('dtc','-1');
            echo hidden('int','-1');
            echo hidden('siz','-1');
            echo hidden('cmp','-1');
            echo hidden('plt','-1');
            echo hidden('pri','-1');
            echo hidden('can','-1');
            echo hidden('age','-1');
            echo hidden('ctl','0');
            echo para("${in}${go}");
            if($jid)
            {
                echo okcancel_link(5, $env['custom']);
            }
            echo form_footer();
        }
    }


    function wiz_build(&$env,&$mgrp,$db)
    {
        $akid = 0;
        $dkid = 0;
        $dnam = '';
        $anam = '';
        $name = $mgrp['name'];
        $qgid = $mgrp['mgroupid'];
        $mids = array( );
        $cats = array( );
        $data = array( );
        $rcat = build_pcat('Wiz_REMV_PC',3,$db);
        $dcat = build_pcat('Wiz_DECL_PC',3,$db);
        $acat = build_pcat('Wiz_APPR_PC',3,$db);
        $sql  = 'select max(precedence) as maxpreced from PatchCategories';
        $max  = intval(find_scalar($sql,$db));
        if ($max > 0)
        {
            $ccat = build_pcat('Wiz_CRIT_PC',$max+1,$db);
        }

        if ($dcat)
        {
            $dnam = sprintf('%s %s',constPgDECL,$name);
            $dkid = $dcat['pcategoryid'];
            $type = constStyleManual;
            $dgrp = build_pgrp($dnam,$type,$dkid,$db);
        }
        if ($acat)
        {
            $anam = sprintf('%s %s',constPgAPPR,$name);
            $akid = $acat['pcategoryid'];
            $type = constStyleManual;
            $agrp = build_pgrp($anam,$type,$akid,$db);
        }
        if ($rcat)
        {
            $rnam = sprintf('%s %s',constPgREMV,$name);
            $rkid = $rcat['pcategoryid'];
            $type = constStyleManual;
            $rgrp = build_pgrp($rnam,$type,$rkid,$db);
        }
        if ($ccat)
        {
            $cnam = sprintf('%s %s',constPgCRIT,$name);
            $ckid = $ccat['pcategoryid'];
            $type = constStyleManual;
            $cgrp = build_pgrp($cnam,$type,$ckid,$db);
        }

        $ajid = ($agrp)? $agrp['pgroupid'] : 0;
        $djid = ($dgrp)? $dgrp['pgroupid'] : 0;
        $rjid = ($rgrp)? $rgrp['pgroupid'] : 0;
        $cjid = ($cgrp)? $cgrp['pgroupid'] : 0;

        $trm = array( );
        $tab = array( );
        $tab[] = 'Patches as P';

        size_restrict($env,$trm);
        $txt = $env['txt'];
        $jid = $env['jid'];
        $cmp = $env['cmp'];
        $pri = $env['pri'];
        $plt = $env['plt'];
        $age = $env['age'];
        $can = $env['can'];
        $dtc = $env['dtc'];
        $int = $env['int'];
        if ($jid)
        {
            $tab[] = 'PatchGroupMap as M';
            $trm[] = "M.pgroupid = $jid";
            $trm[] = 'P.patchid = M.patchid';
        }



        if (0 <= $dtc)
        {
            $op    = ($dtc)? '>' : '=';
            $gid   = $mgrp['mgroupid'];
            $tab[] = 'PatchStatus as S';
            $tab[] =$GLOBALS['PREFIX'].'core.MachineGroupMap as N';
            $tab[] =$GLOBALS['PREFIX'].'core.MachineGroups as G';
            $tab[] =$GLOBALS['PREFIX'].'core.Census as C';
            $trm[] = 'N.mgroupuniq = G.mgroupuniq';
            $trm[] = "G.mgroupid = $gid";
            $trm[] = "N.censusuniq = C.censusuniq";
            $trm[] = "C.id = S.id";
            $trm[] = "S.patchid = P.patchid";
            $trm[] = "S.detected $op 0";
        }
        if (0 <= $int)
        {
            $op    = ($int)? '>' : '=';
            $gid   = $mgrp['mgroupid'];
            $tab[] = 'PatchStatus as X';
            $tab[] =$GLOBALS['PREFIX'].'core.MachineGroupMap as O';
            $tab[] =$GLOBALS['PREFIX'].'core.MachineGroups as Q';
            $tab[] =$GLOBALS['PREFIX'].'core.Census as R';
            $trm[] = 'O.mgroupuniq = Q.mgroupuniq';
            $trm[] = "Q.mgroupid = $gid";
            $trm[] = "O.censusuniq = R.censusuniq";
            $trm[] = "R.id = X.id";
            $trm[] = "X.patchid = P.patchid";
            $trm[] = "X.lastinstall $op 0";
        }
        if ($cmp > 0)
        {
            $tab[] = 'Patches as C';
            $trm[] = "C.patchid = $cmp";
            $trm[] = 'P.component = C.component';
        }
        if ($pri > 0)
        {
            $tab[] = 'Patches as R';
            $trm[] = "R.patchid = $pri";
            $trm[] = 'P.prio = R.prio';
        }
        if ($plt > 0)
        {
            $tab[] = 'Patches as L';
            $trm[] = "L.patchid = $plt";
            $trm[] = 'P.platform = L.platform';
        }
        if ($can > 0)
        {
            $tmp   = $can - 1;
            $trm[] = "P.canuninstall = $tmp";
        }
        if ($age > 0)
        {
            $midn  = midnight(time());
            $days  = $age - 1;
            $when  = days_ago($midn,$days);
            $trm[] = "P.date > $when";
        }
        if ($age == -2)
        {
            $trm[] = 'P.date <= 0';
        }
        if ($txt)
        {
            $txt = str_replace('%','\%',$txt);
            $txt = str_replace('_','\_',$txt);
        }
        if ($txt)
        {
            $txt = safe_addslashes($txt);
            $trm[] = "P.name like '%$txt%'";
        }
        if(($env['type']!=constPatchTypeAll) &&
            ($env['type']!=constPatchTypeNotDisplayed))
        {
            $trm[] = "P.type=" . $env['type'];
        }
        if($env['mand']>0)
        {
            $trm[] = 'P.mandatory=' . $env['mand'];
        }

        $tabs = join(",\n ",$tab);
        $trms = join("\n and ",$trm);
        $num  = safe_count($trm);
        $whr  = ($num)? "\n where" : '';
        $ords = wiz_order($env['ord']);
        $rowMin = $env['limt'] * $env['page'];
        $rowMax = $env['limt'];
        $limit = " limit $rowMin,$rowMax";

        $sql = "select P.* from\n"
             . " $tabs $whr $trms\n"
             . " group by P.patchid\n"
             . " order by $ords\n"
             . $limit;
        $set = find_many($sql,$db);

        $totalRows = 0;
        $sql = "select count(*) from\n"
             . " $tabs $whr $trms\n"
             . " group by P.patchid\n";
        $res = command($sql,$db);
        if ($res)
        {
            $totalRows = mysqli_num_rows($res);
            mysqli_free_result($res);
        }

        $env['totalRows'] = $totalRows;

        reset($set);
        foreach ($set as $key => $row)
        {
            $mid = $row['patchid'];
            $cats[$mid][$ajid] = 0;
            $cats[$mid][$cjid] = 0;
            $cats[$mid][$rjid] = 0;
            $cats[$mid][$djid] = 0;
            $mids[$mid] = $row['name'];
            $data[$mid] = $row;
        }

        $num = $totalRows;
        $txt = "$ajid,$djid,$rjid,$cjid";
        $sql = "select pgroupid, patchid\n"
             . " from PatchGroupMap\n"
             . " where pgroupid in ($txt)";
        $set = find_many($sql,$db);

        reset($set);
        foreach ($set as $key => $row)
        {
            $mid = $row['patchid'];
            $jid = $row['pgroupid'];
            $cats[$mid][$jid] = 1;
        }



        $wiz = array( );
        $wiz['name'] = $name;
        $wiz['qgid'] = $qgid;
        $wiz['anam'] = $anam;
        $wiz['dnam'] = $dnam;
        $wiz['rnam'] = $rnam;
        $wiz['cnam'] = $cnam;
        $wiz['akid'] = $akid;
        $wiz['dkid'] = $dkid;
        $wiz['rkid'] = $rkid;
        $wiz['ckid'] = $ckid;
        $wiz['ajid'] = $ajid;
        $wiz['djid'] = $djid;
        $wiz['rjid'] = $rjid;
        $wiz['cjid'] = $cjid;
        $wiz['cats'] = $cats;
        $wiz['mids'] = $mids;
        $wiz['data'] = $data;
        $wiz['size'] = $num;
        return $wiz;
    }

    function canuninstall($can)
    {
        switch ($can)
        {
            case constPatchCanUnknown: return 'Unknown';
            case     constPatchCanYes: return 'Yes';
            case      constPatchCanNo: return 'No';
            default: return canuninstall(constPatchCanUnknown);
        }
    }


    function add_column(&$env,&$aaa,$tag,$txt)
    {
        if (0 <= $env[$tag])
        {
            $aaa[] = $txt;
        }
    }

    function wiz_status_table(&$env,&$wiz,$word,$xjid,$head)
    {
        $name = $wiz['name'];
        $qgid = $wiz['qgid'];
        $ajid = $wiz['ajid'];
        $djid = $wiz['djid'];
        $ajid = $wiz['ajid'];
        $cjid = $wiz['cjid'];
        $rjid = $wiz['rjid'];
        $mids = $wiz['mids'];
        $scop = $env['scop'];
        $self = $env['self'];
        $post = $env['post'];

        $ord = $env['ord'];
        $hid = $env['hid'];
        $gid = $env['gid'];
        $cid = $env['cid'];
        $jid = $env['jid'];
        $act = $env['act'];
        $pri = $env['pri'];
        $siz = $env['siz'];
        $cmp = $env['cmp'];
        $plt = $env['plt'];
        $age = $env['age'];
        $can = $env['can'];
        $dtc = $env['dtc'];
        $int = $env['int'];
        $txt = $env['txt'];
        $limit = $env['limt'];
        $mand = $env['mand'];
        $intprio = $env['intprio'];
        $type = $env['type'];
        $url = urlencode($txt);
        $nox = '<br>';



        $args  = array("$self?act=$act&scop=$scop");
        if ($cid > 0)   $args[] = "cid=$cid";
        if ($hid > 0)   $args[] = "hid=$hid";
        if ($gid > 0)   $args[] = "gid=$gid";
        if ($jid > 0)   $args[] = "jid=$jid";
        if ($dtc != -1) $args[] = "dtc=$dtc";
        if ($int != -1) $args[] = "int=$int";
        if ($plt != -1) $args[] = "plt=$plt";
        if ($siz != -1) $args[] = "siz=$siz";
        if ($cmp != -1) $args[] = "cmp=$cmp";
        if ($age != -1) $args[] = "age=$age";
        if ($can != -1) $args[] = "can=$can";
        if ($txt != '') $args[] = "txt=$url";

        $o    = join('&',$args) . "&ord";
        $name = ($ord ==  0)? "$o=1"  : "$o=0";           $date = ($ord ==  2)? "$o=3"  : "$o=2";           $comp = ($ord ==  4)? "$o=5"  : "$o=4";           $plat = ($ord ==  6)? "$o=7"  : "$o=6";           $size = ($ord ==  8)? "$o=9"  : "$o=8";           $prio = ($ord == 10)? "$o=11" : "$o=10";          $canu = ($ord == 12)? "$o=13" : "$o=12";
        $text = 'Current Status';
        $stat = 'Status';
        $type = 'Type';
        $mandatory = 'Mandatory';
        $intprio = 'Internal Priority';
        $name = html_link($name,'Update');
        $date = html_link($date,'Date');
        $comp = html_link($comp,'Component');
        $plat = html_link($plat,'Platform');
        $prio = html_link($prio,'Priority');
        $size = html_link($size,'Size');
        $canu = html_link($canu,'Uninstallible');
        $args = array($text,$word,$name,$stat);

        add_column($env,$args,'age',$date);
        add_column($env,$args,'cmp',$comp);
        add_column($env,$args,'pri',$prio);
        add_column($env,$args,'siz',$size);
        add_column($env,$args,'plt',$plat);
        add_column($env,$args,'can',$canu);
        if($env['type']!=constPatchTypeNotDisplayed)
        {
            $args[] = $type;
        }
        add_column($env,$args,'mand',$mandatory);
        add_column($env,$args,'intprio',$intprio);

        $grp  = $wiz['name'];
        $cols = safe_count($args);
        $rows = safe_count($mids);
        $mesg = plural($rows,'update');
        $text = "$head for \"$grp\" &nbsp; ($mesg)";
        $cmd  = "wu-patch.php?act=dets&mid";
        $allc = ($post == constButtonAll)?  true : false;

        echo okcancel_link(5, $env['custom']);
        echo checkallnone(5);

        GRPW_WizardPagingText($word);

        echo prevnext($env, $env['totalRows']);

        echo mark('table');

        echo table_header();
        echo pretty_header($text,$cols);
        echo table_data($args,1);

        reset($mids);
        foreach ($mids as $mid => $name)
        {
            $txt = $nox;
            $box = checkbox("mid_$mid",$allc);

            if ($wiz['cats'][$mid][$ajid]) $txt = 'approved';
            if ($wiz['cats'][$mid][$rjid]) $txt = 'removed';
            if ($wiz['cats'][$mid][$cjid]) $txt = 'critical';
            if ($wiz['cats'][$mid][$djid]) $txt = 'declined';

            $tag = ($wiz['cats'][$mid][$xjid])? $nox : $box;

            $href = "wu-stats.php?gid=$qgid&mid=$mid";


            $noError = true;
            if($err!=constAppNoErr)
            {
                $noError = false;
                            }
            $err = PHP_INSW_GetPatchIDStr(CUR, $patchStr);
            if($err!=constAppNoErr)
            {
                $noError = false;
                            }
            $err = PHP_INSW_GetEndpointStr(CUR, $endpointStr,
                constOutputUpdateInt);
            if($err!=constAppNoErr)
            {
                $noError = false;
                            }

            $stat = '';
            if($noError)
            {
                $stat = html_page("insw.php?$actStr&$patchStr$mid&"
                    . "$endpointStr", '[analysis]');
                $stat .= '<br>';
            }
            $stat .= html_page($href,'[status]');
            $link = html_page("$cmd=$mid",$name);
            $args = array($txt,$tag,$link,$stat);
            $prio = disp($wiz['data'][$mid],'prio');
            $size = disp($wiz['data'][$mid],'size');
            $comp = disp($wiz['data'][$mid],'component');
            $plat = disp($wiz['data'][$mid],'platform');
            $date = timestamp($wiz['data'][$mid]['date']);
            $canu = canuninstall($wiz['data'][$mid]['canuninstall']);
            $typeint = $wiz['data'][$mid]['type'];
            $type = '&nbsp;';
            switch($typeint)
            {
            case constPatchTypeUndefined:
                $type = constPatchTypeUndefinedStr;
                break;
            case constPatchTypeUpdate:
                $type = constPatchTypeUpdateStr;
                break;
            case constPatchTypeServicePack:
                $type = constPatchTypeServicePackStr;
                break;
            case constPatchTypeRollup:
                $type = constPatchTypeRollupStr;
                break;
            case constPatchTypeSecurity:
                $type = constPatchTypeSecurityStr;
                break;
            case constPatchTypeCritical:
                $type = constPatchTypeCriticalStr;
                break;
            }
            $intprio = $wiz['data'][$mid]['priority'];
            $mandint = $wiz['data'][$mid]['mandatory'];
            $mand = '';
            switch($mandint)
            {
            case constPatchMandatoryUnknown:
                $mand = 'Unknown';
                break;
            case constPatchMandatoryNo:
                $mand = 'No';
                break;
            case constPatchMandatoryYes:
                $mand = 'Yes';
                break;
            }

            add_column($env,$args,'age',$date);
            add_column($env,$args,'cmp',$comp);
            add_column($env,$args,'pri',$prio);
            add_column($env,$args,'siz',$size);
            add_column($env,$args,'plt',$plat);
            add_column($env,$args,'can',$canu);
            if($env['type']!=constPatchTypeNotDisplayed)
            {
                $args[] = $type;
            }
            add_column($env,$args,'mand',$mand);
            add_column($env,$args,'intprio',$intprio);
            echo table_data($args,0);
        }
        echo table_footer();

        echo prevnext($env, $env['totalRows']);

        echo checkallnone(5);
        echo okcancel_link(5, $env['custom']);

        UTIL_StoreEnvironment($env);
    }


    function quad_check(&$env,$good,$size,$jid,$db)
    {
        if ($good)
        {
            if ((!$jid) || (!$size))
            {
                wiz_nothing($env,$jid,$db);
            }
        }
        else
        {
            echo mgrp_error();
            wizard_return($env,$db);
        }
    }



    function wiz_decl_form(&$env,$db)
    {
        echo again($env);
        debug_note("<b>wdec</b> wizard decline form");
        $djid = 0;
        $size = 0;
        $good = false;
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $gid  = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$gid,$db);
        }
        if (($good) && ($mgrp))
        {
            $wiz  = wiz_build($env,$mgrp,$db);
            $djid = $wiz['djid'];
            $size = $wiz['size'];
            wiz_control($env,$djid,$db);
        }
        if (($djid) && ($size) && ($good))
        {
            $acts = 'Decline';
            GRPW_ChooseUpdatesText($acts);

            GRPW_PrepareWizardUpdateForm($env, $djid, $db);
            echo hidden('act','wdec');
            echo hidden('frm','wdec');
            echo hidden('pcn','wiz');
            echo hidden('ctl',1);

            preserve($env,'scop,cid,hid,gid');
            preserve($env,'jid,siz,cmp,plt,pri,age,dtc,int,can,txt,ord');

            $head = 'Select Updates to Decline';
            wiz_status_table($env,$wiz,$acts,$djid,$head);
            echo form_footer();
        }
        quad_check($env,$good,$size,$djid,$db);
        echo again($env);
    }


    function show_changes($env,&$mids,$wpgroupid,$txt,$db)
    {
        $changes = '';
        if ($mids)
        {
            $rows = safe_count($mids);
            $mesg = plural($rows,'update');
            $text = "$txt &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,1);

            $act = 'wu-patch.php?act=dets&mid';

            reset($mids);
            foreach ($mids as $mid => $name)
            {
                $changes .= "$name<br>";
                $view = html_page("$act=$mid",$name);
                $args = array($view);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            $txt = 'No changes were made to the list'
                 . ' of updates.';
            echo para($txt);
        }

        if($changes)
        {
            $name = '';
            $sql = "SELECT name FROM PatchGroups WHERE pgroupid=$wpgroupid";
            $row = find_one($sql, $db);
            if($row)
            {
                $name = $row['name'];
            }
            $details = 'The following updates were added to the patch group '
                . "$name and are " . strtolower($text) . ":<br>" . $changes;
            $user = '';
            if(array_key_exists('username', $env))
            {
                $user = $env['username'];
            }
            $err = PHP_AUDT_LogLocalAudit(CUR, constMUMChangeLevel,
                constModuleMUM, constClassUser, constAuditGroupMUMChange,
                $user, $details);
            if($err!=constAppNoErr)
            {
                            }
        }
    }




    function process_mids(&$mids,$jid,$a,$b,$c,$db)
    {
        $num = 0;
        $set = array( );
        reset($mids);
        foreach ($mids as $mid => $name)
        {
            $set[] = $mid;
        }
        if ($set)
        {
            $txt = join(',',$set);
            $sql = "delete from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroupMap\n"
                 . " where patchid in ($txt)\n"
                 . " and pgroupid in ($a,$b,$c)";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num)
            {
                $stat = "n:$num,a:$a,b:$b,c:$c,t:$txt";
                $text = "patch: gang delete ($stat)";
                                debug_note($text);
            }
        }
        reset($mids);
        foreach ($mids as $mid => $name)
        {
            $nid = insert_pmap($jid,$mid,$db);
            if ($nid)
            {
                $stat = "j:$jid,m:$mid,n:$nid";
                $text = "patch: added patch ($stat) $name";
                                debug_note($text);
                $num++;
            }
        }
        return $num;
    }


    function wiz_decl_act(&$env,$db)
    {
        $djid = 0;
        $dgid = 0;
        $ajid = 0;
        $chng = 0;
        $good = false;
        $act  = $env['act'];
        $set  = array( );
        $mids = array( );

        debug_note("<b>$act</b> wiz_decl_act");

        echo again($env);
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $dgid = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$dgid,$db);
        }

        if (!$good)
        {
            echo mgrp_error();
            $mgrp = array( );
            $dgid = 0;
        }

        if ($mgrp)
        {
            $dgid = $mgrp['mgroupid'];
            $name = $mgrp['name'];
            $wiz  = wiz_build($env,$mgrp,$db);
            $dgrp = $wiz['dnam'];
            $djid = $wiz['djid'];
            $ajid = $wiz['ajid'];
            $cjid = $wiz['cjid'];
            $rgrp = $wiz['rnam'];
            $rjid = $wiz['rjid'];
        }

        if (($djid) && ($dgid))
        {
            $pgrp = find_pgrp_jid($djid,$db);
            debug_note("<b>wuna</b> wiz_decl_act djid:$djid dgrp:$dgrp");
            $set = find_patch_checks($djid,$db);
        }
        if (($set) && ($mgrp) && ($djid) && ($ajid))
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $nid  = @ intval($row['nid']);
                $here = ($nid)? 1 : 0;
                $form = UTIL_GetStoredInteger("mid_$mid",0);

                if ((!$here) && ($form))
                {
                    $mids[$mid] = $name;
                }
            }
        }

        $chng = process_mids($mids,$djid,$ajid,$cjid,$rjid,$db);
        if ($chng)
        {
            $scop = $env['scop'];
            $who  = scop_info($scop,$mgrp);
            echo para("$who will not install updates listed in the table below:");
        }

        if ($djid)
        {
            show_changes($env,$mids,$djid,'Now Declined',$db);
        }

        if (($mgrp) && ($djid))
        {
            $save = false;
            $pall = array( );
            $mall = array( );
            $dgid = $mgrp['mgroupid'];
            $pcfg = find_pcfg($dgid,$djid,$db);
            if (!$pcfg)
            {
                $save = true;
                $pall = find_pgrp_name(constPatchAll,$db);
                $pjid = ($pall)? $pall['pgroupid'] : 0;
                $pcfg = find_pcfg($dgid,$pjid,$db);
            }
            if (!$pcfg)
            {
                $save = true;
                $auth = $env['auth'];
                $pcfg = seek_pcfg($auth,$db);
            }

            $old = $pcfg['installation'];
            $new = constPatchInstallNever;
            if ($save)
            {
                $pcfg['installation'] = $new;
                $rpid = insert_pcfg($dgid,$djid,$pcfg,$djid,$db);
            }
            else
            {
                $rpid = $pcfg['pconfigid'];
                if ($old != $new)
                {
                    $pcfg['installation'] = $new;
                    update_pcfg($env,$rpid,$pcfg,$db);
                }
            }
            if (($mgrp) && ($pgrp) && ($rpid))
            {
                $mgnm = $mgrp['name'];
                $pgnm = $pgrp['name'];
                $stat = "g:$dgid,j:$djid,p:$rpid";
                $text = "patch: wizard decline ($stat) '$mgnm' / '$pgnm'";
                                debug_note($text);
            }
        }
        GRPW_ProcessSelection($env, $djid, $db);
        wizard_return($env,$db);
        echo again($env);
    }

    function wiz_decl_disp(&$env,$db)
    {
        debug_note('<b>wdec</b> wiz_decl_disp');
        wiz_generic_disp($env,'decl','',0,$db);
    }


    function site_options($auth,$name,$db)
    {
        $list = array($name);
        $qu   = safe_addslashes($auth);
        $sql  = "select U.customer, U.id from\n"
              . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
              . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
              . " ".$GLOBALS['PREFIX']."softinst.Machine as M\n"
              . " where X.id = M.id\n"
              . " and X.site = U.customer\n"
              . " and U.username = '$qu'\n"
              . " group by U.customer\n"
              . " order by U.customer";
        $set = find_many($sql,$db);
        foreach ($set as $key => $row)
        {
            $cid  = $row['id'];
            $site = $row['customer'];
            $list[$cid] = $site;
          }
        return $list;
    }

    function host_options($cid,$db)
    {
        $set  = array( );
        $none = indent(20);
        $hids = array($none);
        if ($cid)
        {
            $sql = "select M.id, X.host from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.Machine as M\n"
                 . " where X.id = M.id\n"
                 . " and X.site = U.customer\n"
                 . " and U.id = $cid\n"
                 . " order by host";
            $set = find_many($sql,$db);
        }
        foreach ($set as $key => $row)
        {
            $hid  = $row['id'];
            $host = $row['host'];
            $hids[$hid] = $host;
          }
        return $hids;
    }


    function patch_options($hid,$db)
    {
        $name = indent(20);
        $mids = array($name);
        if ($hid > 0)
        {
            $sql = "select S.patchid, P.name from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.Patches as P,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchStatus as S\n"
                 . " where S.id = $hid\n"
                 . " and S.patchid = P.patchid\n"
                 . " order by name";
        }
        else
        {
            $sql = "select patchid, name\n"
                 . " from Patches\n"
                 . " order by name";
        }
        $set = find_many($sql,$db);
        foreach ($set as $key => $row)
        {
            $mid  = $row['patchid'];
            $name = $row['name'];
            $mids[$mid] = $name;
            }
        return $mids;
    }



    function wizard_return(&$env,$db)
    {
        $self = $env['self'];
        $link = html_link($self,'Return to wizard page.');
        echo para($link);
    }

    function wizard_stat_return(&$env,$db)
    {
        $self = $env['self'];
        $href = "$self?act=wsts";
        $link = html_link($href,'Return to wizard status page.');
        echo para($link);
    }


    function quad_dispatch(&$env,$head,$acts,$tags,$text,$db)
    {
        echo again($env);
        $good = false;
        $size = 0;
        $xjid = 0;
        $wiz  = array( );
        $gid  = $env['gid'];
        $act  = $env['act'];
        $post = $env['post'];
        $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        $done = ($post == constButtonOk);
        $processed = 0;

        debug_note("<b>$act</b> quad_dispatch (g:$gid)");
        if ($mgrp)
        {
            $gid  = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$gid,$db);
        }
        if (($mgrp) && ($good))
        {
            $name = $mgrp['name'];
            $wiz  = wiz_build($env,$mgrp,$db);
        }
        if ($wiz)
        {
            $size = $wiz['size'];
            $xjid = $wiz[$tags[0]];
            $aaaa = $wiz[$tags[1]];
            $bbbb = $wiz[$tags[2]];
            $cccc = $wiz[$tags[3]];
        }
        if (($wiz) && ($good) && (!$done))
        {
            wiz_control($env,$xjid,$db);
        }
        if (($wiz) && ($size) && ($xjid) && ($good))
        {
            if($done)
            {
                $mids = array( );
                $set  = find_patch_checks($xjid,$db);
                reset($set);
                foreach ($set as $key => $row)
                {
                    $name = $row['name'];
                    $mid  = $row['mid'];
                    $nid  = @ intval($row['nid']);
                    $here = ($nid)? 1 : 0;
                    $form = UTIL_GetStoredInteger("mid_$mid",0);

                    if ((!$here) && ($form))
                    {
                        $mids[$mid] = $name;
                    }
                }
                $chng = process_mids($mids,$xjid,$aaaa,$bbbb,$cccc,$db);
                show_changes($env,$mids,$xjid,$text,$db);
                wizard_stat_return($env,$db);
            }
            else
            {
                GRPW_ChooseUpdatesText($acts);

                GRPW_PrepareWizardUpdateForm($env, $xjid, $db);
                echo hidden('act',$act);
                echo hidden('frm',$act);
                echo hidden('pcn','wsts');
                echo hidden('ctl',1);

                preserve($env,'scop,cid,hid,gid');
                preserve($env,'jid,siz,cmp,plt,pri,age,dtc,int,can,txt,ord');

                wiz_status_table($env,$wiz,$acts,$xjid,$head);
                echo form_footer();
            }
        }
        if (!$done)
        {
            quad_check($env,$good,$size,$xjid,$db);
        }
        else
        {
            GRPW_ProcessSelection($env, $xjid, $db);
        }
        echo again($env);
    }


    function quad_decl(&$env,$db)
    {
        $acts = 'Decline';
        $head = 'Select Updates to Decline';
        $tags = explode('|','djid|ajid|rjid|cjid');
        $text = 'Now Declined';
        quad_dispatch($env,$head,$acts,$tags,$text,$db);
    }


    function quad_crit(&$env,$db)
    {
        $acts = 'Critical';
        $head = 'Select Critical Updates';
        $tags = explode('|','cjid|djid|rjid|ajid');
        $text = 'Critical Updates to be Applied Immediately';
        quad_dispatch($env,$head,$acts,$tags,$text,$db);
    }


    function quad_remv(&$env,$db)
    {
        $acts = 'Remove';
        $head = 'Select Updates to Remove';
        $tags = explode('|','rjid|djid|cjid|ajid');
        $text = 'Now Removed';
        quad_dispatch($env,$head,$acts,$tags,$text,$db);
    }


    function quad_appr(&$env,$db)
    {
        $acts = 'Approve';
        $head = 'Select Updates to Approve';
        $tags = explode('|','ajid|djid|cjid|rjid');
        $text = 'Now Approved';
        quad_dispatch($env,$head,$acts,$tags,$text,$db);
    }


    function wiz_appr_form(&$env,$db)
    {
        echo again($env);
        debug_note("wiz_appr_form");

        $ajid = 0;
        $size = 0;
        $good = false;
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $gid  = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$gid,$db);
        }
        if (($good) && ($mgrp))
        {
            $wiz  = wiz_build($env,$mgrp,$db);
            $ajid = $wiz['ajid'];
            $size = $wiz['size'];
            wiz_control($env,$ajid,$db);
        }
        if (($ajid) && ($size) && ($good))
        {
            $acts = 'Approve';
            GRPW_ChooseUpdatesText($acts);

            GRPW_PrepareWizardUpdateForm($env, $ajid, $db);
            echo hidden('act','wapp');
            echo hidden('frm','wapp');
            echo hidden('pcn','wiz');
            echo hidden('ctl',1);

            preserve($env,'scop,cid,hid,gid');
            preserve($env,'jid,siz,cmp,plt,pri,age,dtc,int,can,txt,ord');

            $head = 'Select Updates to Approve';
            wiz_status_table($env,$wiz,$acts,$ajid,$head);
            echo form_footer();
        }
        quad_check($env,$good,$size,$ajid,$db);
        echo again($env);
    }


    function schedule_act(&$env,$wpgroupid,$db)
    {
        $num  = 0;
        $pid  = $env['pid'];
        $pcfgs = array();
        $mgrp = find_scop_mgrp($env,$db);
        $mgroupid = GRPW_DeriveMachineGroup($env, $db);
        $sql = "SELECT * FROM PatchConfig WHERE mgroupid=$mgroupid AND "
            . "wpgroupid=$wpgroupid";
        $pcfgs = find_many($sql, $db);

        if ($pcfgs)
        {
            foreach ($pcfgs as $key => $pcfg)
            {
                $pid = $pcfg['pconfigid'];
                $gid = $pcfg['mgroupid'];
                if (legal_mgrp($env,$gid,$db))
                {
                    update_shed($env,$pcfg);
                    if (update_pcfg($env,$pid,$pcfg,$db))
                    {
                        $num+=1;
                    }
                }
                else
                {
                    echo mgrp_error();
                }
        }
        }
        return $num;
    }


    function schedule_form(&$env,&$pcfg,$tag,$db)
    {
        echo post_self('myform');
        echo hidden('act', $tag);
        echo hidden('frm', $env['act']);
        echo hidden('pcn','wiz');
        echo hidden('pid', $pcfg['pconfigid']);
        preserve($env, 'scop,gid,hid,cid');
        echo okcancel_link(5, $env['custom']);
        schedule_common($pcfg);
        echo okcancel_link(5, $env['custom']);
        echo form_footer();
    }


    function wiz_appr_act(&$env,$db)
    {
        $ajid = 0;
        $djid = 0;
        $agid = 0;
        $chng = 0;
        $good = false;
        $set  = array( );
        $mids = array( );
        $pgrp = array( );
        $pcfg = array( );
        $act  = $env['act'];
        $mgrp = find_scop_mgrp($env,$db);
        echo again($env);
        if ($mgrp)
        {
            $agid = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$agid,$db);
        }

        if (!$good)
        {
            echo mgrp_error();
            $mgrp = array( );
        }

        if ($mgrp)
        {
            $agid = $mgrp['mgroupid'];
            $name = $mgrp['name'];
            $wiz  = wiz_build($env,$mgrp,$db);
            $agrp = $wiz['anam'];
            $ajid = $wiz['ajid'];
            $djid = $wiz['djid'];
            $cjid = $wiz['cjid'];
            $rgrp = $wiz['rnam'];
            $rjid = $wiz['rjid'];
        }
        debug_note("<b>$act</b> wiz_appr_act");
        if ($ajid)
        {
            debug_note("<b>$act</b> wiz_appr_act ajid:$ajid agrp:$agrp");
            $set  = find_patch_checks($ajid,$db);
            $pgrp = find_pgrp_jid($ajid,$db);
        }

        if (($set) && ($mgrp) && ($pgrp))
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $nid  = @ intval($row['nid']);
                $here = ($nid)? 1 : 0;
                $form = UTIL_GetStoredInteger("mid_$mid",0);

                if ((!$here) && ($form))
                {
                    $mids[$mid] = $name;
                }
            }
        }

        if ($mids)
        {
            $chng = process_mids($mids,$ajid,$djid,$cjid,$rjid,$db);
        }

        if ($ajid)
        {
            show_changes($env,$mids,$ajid,'Now Approved',$db);
        }

        if (($mgrp) && ($pgrp))
        {
            $save = false;
            $pall = array( );
            $mall = array( );
            $pcfg = find_pcfg($agid,$ajid,$db);
            if (!$pcfg)
            {
                $save = true;
                $pall = find_pgrp_name(constPatchAll,$db);
                $pjid = ($pall)? $pall['pgroupid'] : 0;
                $pcfg = find_pcfg($agid,$pjid,$db);
            }
            if (!$pcfg)
            {
                $save = true;
                $auth = $env['auth'];
                $pcfg = seek_pcfg($auth,$db);
            }

            $old = $pcfg['installation'];
            $new = constPatchScheduleInstall;
            if ($save)
            {
                $pcfg['installation'] = $new;
                $apid = insert_pcfg($agid,$ajid,$pcfg,$ajid,$db);
                $pcfg['pconfigid'] = $apid;
                $pcfg['mgroupid']  = $agid;
                $pcfg['pgroupid']  = $ajid;
            }
            else
            {
                $rpid = $pcfg['pconfigid'];
                if ($old != $new)
                {
                    $pcfg['installation'] = $new;
                    update_pcfg($env,$rpid,$pcfg,$db);
                }
            }
        }
        if (($mgrp) && ($pgrp) && ($pcfg))
        {
            $mgnm = $mgrp['name'];
            $pgnm = $pgrp['name'];
            $pid  = $pcfg['pconfigid'];
            $jid  = $pcfg['pgroupid'];
            $gid  = $pcfg['mgroupid'];
            $stat = "g:$gid,j:$jid,p:$pid";
            $text = "patch: wizard approve ($stat) '$mgnm' / '$pgnm'";
                        debug_note($text);

            echo <<< XZZZ

            <p>
                Select the schedule for applying the
                updates you approved. You can also set
                up and schedule a notification to alert
                end-users about the updates to be
                installed on their systems. When you are
                done, click on the <b>OK</b> button.
            </p>

XZZZ;
            schedule_form($env,$pcfg,'wrsh',$db);
        }
        else
        {
            wizard_return($env,$db);
        }
        GRPW_ProcessSelection($env, $ajid, $db);
        echo again($env);
    }


    function tiny_schedule(&$pcfg)
    {
        $ssec = $pcfg['scheddelay'];
        $smin = $pcfg['schedminute'];
        $shor = $pcfg['schedhour'];
        $sday = $pcfg['schedday'];
        $smon = $pcfg['schedmonth'];
        $swek = $pcfg['schedweek'];
        $srnd = $pcfg['schedrandom'];

        $twld = ((24 <= $shor) && (60 <= $smin));
        $dwld = ((!$smon) && (!$sday) && (7 <= $swek));

        if (($twld) && ($dwld))
        {
            $txt = 'asap';
        }
        else
        {
            $pm = ($shor <= 11)? 'AM' : 'PM';
            $hh = ($shor % 12)? $shor % 12 : 12;
            if (($shor < 24) && ($smin < 60))
            {
                $tt = sprintf('%d:%02d %s',$hh,$smin,$pm);
            }
            if (($shor < 24) && (60 <= $smin))
            {
                $tt = sprintf('%d %s, any minute',$hh,$pm);
            }
            if ((24 <= $shor) && ($smin < 60))
            {
                $tt = "every hour, $smin past";
            }
            if ($twld)
            {
                $tt = 'any time';
            }
            $dd  = ($dwld)? 'any day' : 'restricted';
            $txt = "$tt, $dd";
        }
        $del = '';
        if ($ssec)
        {
            $ddd = round($ssec / 86400);
            $hhh = round($ssec / 3600);
            $xxx = ($hhh <= 24)? "$hhh hours" : "$ddd days";
            $del = "delay $xxx, ";
        }
        $rnd = '';
        if ($srnd)
        {
            $rrr = round($srnd / 60);
            $rnd = ", random $rrr minutes";
        }
        return "${del}${txt}${rnd}";
    }

    function text_schedule(&$pcfg)
    {
        $hopt = hour_options();
        $mopt = month_options();
        $nopt = minute_options();
        $wopt = wday_options();
        $dopt = mday_options();


        $ssec = $pcfg['scheddelay'];
        $smin = $pcfg['schedminute'];
        $shor = $pcfg['schedhour'];
        $sday = $pcfg['schedday'];
        $smon = $pcfg['schedmonth'];
        $swek = $pcfg['schedweek'];
        $srnd = $pcfg['schedrandom'];

        $in   = indent(5);
        $xdel = round($ssec / 86400);
        $xrnd = round($srnd / 60);
        $xmon = $mopt[$smon];
        $xday = $dopt[$sday];
        $xmin = $nopt[$smin];
        $xhor = $hopt[$shor];
        $xwek = $wopt[$swek];

        return <<< SCHD

        {$in}Delay operation by <b>$xdel</b> days.<br>
        {$in}Month: <b>$xmon</b>
             Day: <b>$xday</b>
             Hour: <b>$xhor</b>
             Minute: <b>$xmin</b>
             Weekday: <b>$xwek</b><br>
        {$in}Random delay in minutes <b>$xrnd</b><br>
SCHD;

    }


    function tiny_notify(&$pcfg)
    {
        $args = array();
        $nadv = $pcfg['notifyadvance'];
        $nat  = $pcfg['notifyadvancetime'];
        $nsch = $pcfg['notifyschedule'];
        $nmin = $pcfg['notifyminute'];
        $nhor = $pcfg['notifyhour'];
        $nday = $pcfg['notifyday'];
        $nmon = $pcfg['notifymonth'];
        $nwek = $pcfg['notifyweek'];
        $nrnd = $pcfg['notifyrandom'];
        $ntyp = $pcfg['notifytype'];
        $ntxt = $pcfg['notifytext'];

        $pshd = $pcfg['preventshutdown'];
        $rusr = $pcfg['reminduser'];

        if ($pshd) $args[] = 'prevent shutdown';
        if ($rusr) $args[] = 'remind user';
        if (($nadv) && ($ntxt))
        {
            $args[] = 'notify in advance';
            $args[] = '"' . $ntxt . '"';
        }
        $ntfy = ($args)? join(', ',$args) : 'None';
        return $ntfy;
    }


    function text_notify(&$pcfg)
    {
        $hopt = hour_options();
        $mopt = month_options();
        $nopt = minute_options();
        $wopt = wday_options();
        $dopt = mday_options();

        $nadv = $pcfg['notifyadvance'];
        $nat  = $pcfg['notifyadvancetime'];
        $nsch = $pcfg['notifyschedule'];
        $nsec = $pcfg['notifydelay'];
        $nmin = $pcfg['notifyminute'];
        $nhor = $pcfg['notifyhour'];
        $nday = $pcfg['notifyday'];
        $nmon = $pcfg['notifymonth'];
        $nwek = $pcfg['notifyweek'];
        $nrnd = $pcfg['notifyrandom'];
        $ntyp = $pcfg['notifytype'];
        $ntxt = $pcfg['notifytext'];

        $in   = indent(5);
        $xdel = round($nsec / 86400);
        $xrnd = round($nrnd / 60);
        $mins = round($nat  / 60);

        $xmon = $mopt[$nmon];
        $xday = $dopt[$nday];
        $xmin = $nopt[$nmin];
        $xhor = $hopt[$nhor];
        $xwek = $wopt[$nwek];

        $ntfy = 'Notification Disabled';
        $adv  = "{$in}Notify <b>$mins</b> minutes before action.<br>\n";
        $txt  = "{$in}Notify text: <b>$ntxt</b><br>\n";
        $sch  = <<< NTFY

        {$in}Delay operation by <b>$xdel</b> days.<br>
        {$in}Month: <b>$xmon</b>
             Day: <b>$xday</b>
             Hour: <b>$xhor</b>
             Minute: <b>$xmin</b>
             Weekday: <b>$xwek</b><br>
        {$in}Random delay in minutes <b>$xrnd</b><br>

NTFY;

        if (($nadv) || ($nsch))
        {
            $ntfy = '';
            if ($nadv) $ntfy .= $adv;
            if ($nsch) $ntfy .= $sch;
            $ntfy .= $txt;
        }
        return $ntfy;
    }


    function wiz_rem_shed(&$env,$db)
    {
        echo again($env);
        $pid  = $env['pid'];
        $mgrp = find_scop_mgrp($env,$db);
        $wiz = wiz_build($env, $mgrp, $db);
        $wpgroupid = $wiz['rjid'];
        $good = schedule_act($env,$wpgroupid,$db);
        $text = ($good)? 'Updated' : 'Unchanged';
        $pcfg = find_pcfg_pid($pid,$db);
        if(!($pcfg))
        {
            $mgroupid = GRPW_DeriveMachineGroup($env, $db);
            $sql = "SELECT * FROM PatchConfig WHERE mgroupid=$mgroupid AND "
                . "wpgroupid=$wpgroupid LIMIT 1";
            $pcfg = find_one($sql, $db);
        }
        $shed = text_schedule($pcfg);
        $ntfy = text_notify($pcfg);

        echo <<< MARK

        <p>
            Wizard Remove Schedule $text.
        </p>

        <p>
            The removal schedule for the updates
            you approved to be removed is:
        </p>

        <p>
            $shed
        </p>


        <p>
            End-users will be notified with the following
            notification issued at:
        </p>

        <p>
            $ntfy
        </p>

MARK;

        wizard_return($env,$db);
        echo again($env);
    }


    function wiz_appr_sched(&$env,$db)
    {
        echo again($env);
        $pid  = $env['pid'];
        $mgrp = find_scop_mgrp($env,$db);
        $wiz = wiz_build($env, $mgrp, $db);
        $wpgroupid = $wiz['ajid'];
        $good = schedule_act($env,$wpgroupid,$db);
        $text = ($good)? 'Updated' : 'Unchanged';
        $pcfg = find_pcfg_pid($pid,$db);
        if(!($pcfg))
        {
            $mgroupid = GRPW_DeriveMachineGroup($env, $db);
            $sql = "SELECT * FROM PatchConfig WHERE mgroupid=$mgroupid AND "
                . "wpgroupid=$wpgroupid LIMIT 1";
            $pcfg = find_one($sql, $db);
        }
        $shed = text_schedule($pcfg);
        $ntfy = text_notify($pcfg);

        echo <<< MARK

        <p>
            Wizard Approve Schedule $text.
        </p>

        <p>
            The installation schedule for the updates
            you approved for installation is:
        </p>

        <p>
            $shed
        </p>


        <p>
            End-users will be notified with the following
            notification issued at:
        </p>

        <p>
            $ntfy
        </p>

MARK;

        wizard_return($env,$db);
        echo again($env);
    }

    function wiz_appr_disp(&$env,$db)
    {
        debug_note('<b>wapp</b> wiz_appr_disp');
        wiz_generic_disp($env,'appr','',0,$db);
    }


    function wiz_rem_form(&$env,$db)
    {
        echo again($env);
        $rjid = 0;
        $size = 0;
        $good = false;
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $gid  = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$gid,$db);
        }
        if (($mgrp) && ($good))
        {
            $name = $mgrp['name'];
            debug_note("<b>wrem</b> wiz_rem_form ($name)");
            $wiz  = wiz_build($env,$mgrp,$db);
            $rjid = $wiz['rjid'];
            $size = $wiz['size'];
            wiz_control($env,$rjid,$db);
        }
        if (($rjid) && ($size) && ($good))
        {
            $acts = 'Remove';
            GRPW_ChooseUpdatesText($acts);

            GRPW_PrepareWizardUpdateForm($env, $rjid, $db);
            echo hidden('act','wrem');
            echo hidden('frm','wrem');
            echo hidden('pcn','wiz');
            echo hidden('ctl',1);

            preserve($env,'scop,cid,hid,gid');
            preserve($env,'jid,siz,cmp,plt,pri,age,dtc,int,can,txt,ord');

            $head = 'Select Updates to Remove';
            wiz_status_table($env,$wiz,$acts,$rjid,$head);
            echo form_footer();
        }
        quad_check($env,$good,$size,$rjid,$db);
        echo again($env);
    }



    function wiz_rem_act(&$env,$db)
    {
        echo again($env);
        debug_note('<b>wrem</b> wiz_rem_act');
        $act  = $env['act'];
        $set  = array( );
        $mids = array( );
        $pcfg = array( );
        $djid = 0;
        $dgid = 0;
        $ajid = 0;
        $chng = 0;
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $rgid = $mgrp['mgroupid'];
            $name = $mgrp['name'];
            $wiz  = wiz_build($env,$mgrp,$db);
            $rgrp = $wiz['rnam'];
            $rjid = $wiz['rjid'];
            $agrp = $wiz['anam'];
            $ajid = $wiz['ajid'];
            $cjid = $wiz['cjid'];
            $djid = $wiz['djid'];
        }
        if (($rjid) && ($rgid))
        {
            $pgrp = find_pgrp_jid($rjid,$db);
            debug_note("<b>wrem</b> wiz_rem_act rjid:$rjid rgrp:$rgrp");
            $set = find_patch_checks($rjid,$db);
        }
        if (($set) && ($mgrp) && ($rjid) && ($ajid))
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $nid  = @ intval($row['nid']);
                $here = ($nid)? 1 : 0;
                $form = UTIL_GetStoredInteger("mid_$mid",0);

                if ((!$here) && ($form))
                {
                    $mids[$mid] = $name;
                }
            }
        }

        if ($mids)
        {
            para('The following updates will be removed:');
            $chng = process_mids($mids,$rjid,$djid,$cjid,$ajid,$db);
        }

        if ($rjid)
        {
            show_changes($env,$mids,$rjid,'Now Removed',$db);
        }

        if (($mgrp) && ($rjid))
        {
            $save = false;
            $pall = array( );
            $mall = array( );
            $rgid = $mgrp['mgroupid'];
            $pcfg = find_pcfg($rgid,$rjid,$db);
            if (!$pcfg)
            {
                $save = true;
                $pall = find_pgrp_name(constPatchAll,$db);
                $pjid = ($pall)? $pall['pgroupid'] : 0;
                $pcfg = find_pcfg($rgid,$pjid,$db);
                debug_note("check global pgrp (g:$rgid,j:$pjid)");
            }
            if (!$pcfg)
            {
                $save = true;
                $auth = $env['auth'];
                $pcfg = seek_pcfg($auth,$db);
            }

            $old = $pcfg['installation'];
            $new = constPatchScheduleRemove;
            if ($save)
            {
                $pcfg['installation'] = $new;
                $rpid = insert_pcfg($rgid,$rjid,$pcfg,$rjid,$db);
                if ($rpid)
                {
                    $pcfg['pconfigid'] = $rpid;
                    $pcfg['mgroupid']  = $rgid;
                    $pcfg['pgroupid']  = $rjid;
                }
                else
                {
                    $pcfg = array();
                }
            }
            else
            {
                $rpid = $pcfg['pconfigid'];
                if ($old != $new)
                {
                    $pcfg['installation'] = $new;
                    update_pcfg($env,$rpid,$pcfg,$db);
                }
            }
        }

        if (($mgrp) && ($pgrp) && ($pcfg))
        {
            $mgnm = $mgrp['name'];
            $pgnm = $pgrp['name'];
            $rpid = $pcfg['pconfigid'];
            $rjid = $pcfg['pgroupid'];
            $rgid = $pcfg['mgroupid'];
            $stat = "g:$rgid,j:$rjid,p:$rpid";
            $text = "patch: wizard remove ($stat) '$mgnm' / '$pgnm'";
                        debug_note($text);
        }

        if ($pcfg)
        {
            schedule_form($env,$pcfg,'wrss',$db);
        }
        else
        {
            wizard_return($env,$db);
        }
        GRPW_ProcessSelection($env, $rjid, $db);
        echo again($env);
    }

    function wiz_rem_disp(&$env,$db)
    {
        debug_note('<b>wrem</b> wiz_rem_disp');
        wiz_generic_disp($env,'rem','',0,$db);
    }




    function force_update(&$env,&$mgrp,$db)
    {
        $dbug = $env['debug'];
        $scop = 237;
        $semi = 'Scrip237RunNowPatchesA';
        $type = constVblTypeInteger;
        $stat = constVarConfStateLocalOnly;
        $lnum = 0;

        $qm   = safe_addslashes(constCatMachine);
        $qn   = safe_addslashes($semi);
        $grp = $mgrp['name'];
        $gid = $mgrp['mgroupid'];
        $sql = "select X.site, X.host, R.censusid,\n"
             . " R.ctime, R.stime, M.mgroupuniq from\n"
             . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
             . " ".$GLOBALS['PREFIX']."core.Variables as V\n"
             . " where M.mgroupuniq = G.mgroupuniq\n"
             . " and G.mgroupid = $gid\n"
             . " and M.censusuniq = X.censusuniq\n"
             . " and X.id = R.censusid\n"
             . " and G.mcatuniq = C.mcatuniq\n"
             . " and C.category = '$qm'\n"
             . " and V.scop = $scop\n"
             . " and V.name = '$qn'\n"
             . " group by M.censusuniq";
        $set = find_many($sql,$db);

        if ($set)
        {
            $hids = array( );

            if ($dbug)
                $text = 'site|host|hid|ctime|stime|last|value';
            else
                $text = 'Site|Machine|Last Contact|Last Update';

            $head = explode('|',$text);
            $cols = safe_count($head);
            $rows = safe_count($set);
            $mesg = plural($rows,'machine');
            $text = "$grp &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $site = $row['site'];
                $host = $row['host'];
                $hid  = $row['censusid'];
                $mgroupuniq = $row['mgroupuniq'];

                if(!VARS_GetValueGroup($value, $mgroupuniq, $semi, (int)$scop, $db))
                {
                                    }
                else
                {
                    $value = (string)(((int)$value)+1);
                    if(!VARS_SetVariableValueGroup($value, $semi, $scop, 0, $mgroupuniq,
                        time(), constSourceScripConfig, FALSE, $db))
                    {
                                            }
                }

                $last = timestamp($row['last']);
                $clnt = timestamp($row['ctime']);
                $serv = timestamp($row['stime']);
                if ($dbug)
                    $args = array($site,$host,$hid,$clnt,$serv,$last,$value);
                else
                    $args = array($site,$host,$clnt,$serv);
                echo table_data($args,0);
                $hids[] = $hid;
            }
            echo table_footer();

            $snum = 0;
            $rnum = 0;
            $cnum = 0;
            $time = time();

            if ($hids)
            {
                $rset = join(',',$hids);
                $rsql = "update ".$GLOBALS['PREFIX']."core.Revisions\n"
                      . " set stime = $time\n"
                      . " where censusid in ($rset)";
                $rres = redcommand($rsql,$db);
                $rnum = affected($rres,$db);

                $csql = "update ".$GLOBALS['PREFIX']."core.LegacyCache set\n"
                      . " last = $time,\n"
                      . " drty = 1\n"
                      . " where censusid in ($rset)";
                $cres = redcommand($csql,$db);
                $cnum = affected($cres,$db);
            }
            $mesg = plural($lnum,'machine');
            $have = ($snum == 1)? 'has' : 'have';
            echo para("$mesg $have been updated.");
            if (($snum) && ($mgrp))
            {
                $name = $mgrp['name'];
                $cgid = $mgrp['mgroupid'];
                $stat = "g:$cgid,s:$snum,r:$rnum,c:$cnum";
                $text = "patch: wizard force critical ($stat) $name";
                                debug_note($text);
            }
        }
        else
        {
            echo para('No appropriate machines found.');
        }
        return $lnum;
    }


    function wiz_crit_form(&$env,$db)
    {
        echo again($env);
        debug_note("<b>wcrt</b> wiz_crit_form");

        $cjid = 0;
        $size = 0;
        $good = false;
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $gid  = $mgrp['mgroupid'];
            $good = legal_mgrp($env,$gid,$db);
        }
        if (($mgrp) && ($good))
        {
            $wiz  = wiz_build($env,$mgrp,$db);
            $cjid = $wiz['cjid'];
            $size = $wiz['size'];
            wiz_control($env,$cjid,$db);
        }
        if (($cjid) && ($size) && ($good))
        {
            $acts = 'Critical';
            GRPW_ChooseUpdatesText($acts);

            GRPW_PrepareWizardUpdateForm($env, $cjid, $db);
            echo hidden('act','wcrt');
            echo hidden('frm','wcrt');
            echo hidden('pcn','wiz');
            echo hidden('ctl', 1);

            preserve($env,'scop,cid,hid,gid');
            preserve($env,'jid,siz,cmp,plt,pri,age,dtc,int,can,txt,ord');

            $head = 'Select Critical Updates';
            wiz_status_table($env,$wiz,$acts,$cjid,$head);
            echo form_footer();
        }
        quad_check($env,$good,$size,$cjid,$db);
        echo again($env);
    }


    function wiz_crit_fin(&$env,$db)
    {
        debug_note('<b>wcrn</b> wiz_crit_fin');
        echo again($env);
        $num  = 0;
        $ord  = $env['ord'];
        $pid  = $env['pid'];
        $jid  = $env['jid'];
        $pcfg = find_pcfg_pid($pid,$db);
        if ($pcfg)
        {
            $gid = $pcfg['mgroupid'];
            if (legal_mgrp($env,$gid,$db))
            {
                $pgrp = find_pgrp_jid($jid,$db);
            }
            else
            {
                $pcfg = array( );
                $pgrp = array( );
                echo mgrp_error();
            }
        }
        if (($pcfg) && ($pgrp))
        {
            $nadv = value_range(0,1,$env['nadv']);
            $pcfg['schedtype']     = constPatchTypeASAP;
            $pcfg['configtype']    = constConfigTypeCritical;
            $pcfg['notifytext']    = $env['txt'];
            $pcfg['installation']  = constPatchScheduleInstall;
            $pcfg['notifyadvance'] = $nadv;
            if (update_pcfg($env,$pid,$pcfg,$db))
            {
                $good = true;
            }
        }

        $num = 0;
        if (($ord) && ($pcfg))
        {
            $gid  = $pcfg['mgroupid'];
            $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
            if ($mgrp)
            {
                $num = force_update($env,$mgrp,$db);
            }
        }


        $note = '';
        if ($pcfg)
        {
            $txt = $pcfg['notifytext'];
            $adv = $pcfg['notifyadvance'];

            if (($adv) && ($txt))
            {
                $text = text_notify($pcfg);
                $note = <<< ZZZZ

                <p>
                    End-users will be notified with
                    the following notification issued
                    at the time of installation of the
                    critical updates you selected at the
                    beginning of this wizard.
                </p>

                $text
ZZZZ;

            }
        }
        if ($pcfg)
        {
            $asap = 'as soon as possible';
            $next = 'at the next scheduled time';
            $when = ($num)? $asap : $next;

            echo <<< ZRZX

            <p>
                The critical updates you selected will be applied $when.
            </p>

            $note
ZRZX;

        }
        wizard_return($env,$db);
        echo again($env);
    }


    function wiz_crit_act(&$env,$db)
    {
        echo again($env);
        debug_note('<b>wcrt</b> wiz_crit_act');
        $num  = 0;
        $pid  = 0;
        $cgid = 0;
        $chng = 0;
        $pcfg = array( );
        $mids = array( );
        $mid  = $env['mid'];
        $mgrp = find_scop_mgrp($env,$db);
        if ($mgrp)
        {
            $cgid = $mgrp['mgroupid'];
            if (legal_mgrp($env,$cgid,$db))
            {
                $wiz = wiz_build($env,$mgrp,$db);
            }
            else
            {
                $mgrp = array( );
                $cgid = 0;
                echo mgrp_error();
            }
        }

        if (($mgrp) && ($wiz))
        {
            $cgid = $mgrp['mgroupid'];
            $cjid = $wiz['cjid'];
            $djid = $wiz['djid'];
            $ajid = $wiz['ajid'];
            $rjid = $wiz['rjid'];
        }
        if ($cjid)
        {
            $set  = find_patch_checks($cjid,$db);
            $pgrp = find_pgrp_jid($cjid,$db);
        }
        if (($pgrp) && ($mgrp) && ($set))
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $nid  = @ intval($row['nid']);
                $here = ($nid)? 1 : 0;
                $form = UTIL_GetStoredInteger("mid_$mid",0);
                if ((!$here) && ($form))
                {
                    $mids[$mid] = $name;
                }
            }
        }
        if ($cjid)
        {
            $chng = process_mids($mids,$cjid,$djid,$ajid,$rjid,$db);
            $text = 'Critical Updates to be Applied Immediately';
            show_changes($env,$mids,$cjid,$text,$db);
        }

        if (($cgid) && ($cjid))
        {
            $save = false;
            $pall = array( );
            $mall = array( );
            $cgid = $mgrp['mgroupid'];
            $pcfg = find_pcfg($cgid,$cjid,$db);
            if (!$pcfg)
            {
                $save = true;
                $pall = find_pgrp_name(constPatchAll,$db);
                $pjid = ($pall)? $pall['pgroupid'] : 0;
                $pcfg = find_pcfg($cgid,$pjid,$db);
            }
            if (!$pcfg)
            {
                $save = true;
                $auth = $env['auth'];
                $pcfg = seek_pcfg($auth,$db);
            }

            $old = $pcfg['installation'];
            $new = constPatchScheduleInstall;
            if ($save)
            {
                $pcfg['installation'] = $new;
                $pcfg['scheddelay'] = 0;
                $pcfg['schedminute'] = 60;
                $pcfg['schedhour'] = 24;
                $pcfg['schedday'] = 0;
                $pcfg['schedmonth'] = 0;
                $pcfg['schedweek'] = 7;
                $pcfg['schedtype'] = constPatchTypeASAP;
                $pcfg['configtype'] = constConfigTypeCritical;
                $pid = insert_pcfg($cgid,$cjid,$pcfg,$cjid,$db);
                if ($pid)
                {
                    $pcfg['pconfigid'] = $pid;
                    $pcfg['mgroupid']  = $cgid;
                    $pcfg['pgroupid']  = $cjid;
                }
                else
                {
                    $pcfg = array();
                }
            }
            else
            {
                $pid = $pcfg['pconfigid'];
                if ($old != $new)
                {
                    $pcfg['configtype'] = constConfigTypeCritical;
                    $pcfg['installation'] = $new;
                    update_pcfg($env,$pid,$pcfg,$db);
                }
            }
        }

        if (($mgrp) && ($pgrp) && ($pcfg))
        {
            $mgnm = $mgrp['name'];
            $pgnm = $pgrp['name'];
            $cpid = $pcfg['pconfigid'];
            $stat = "g:$cgid,j:$cjid,p:$cpid";
            $text = "patch: wizard critical ($stat) '$mgnm' / '$pgnm'";
                        debug_note($text);
        }

        debug_note("<b>wcrn</b> gid:$cgid jid:$cjid pid:$pid");

        if (($pcfg) && ($pid))
        {
            $ntxt = $pcfg['notifytext'];
            $nadv = value_range(0,1,$pcfg['notifyadvance']);
            $area = areabox('txt',10,60,'',$ntxt);

            $in = indent(5);
            $c1 = checkbox('nadv',$nadv);

            $o0 = radio('ord',0,0);
            $o1 = radio('ord',1,0);

            echo post_self('myform');
            echo hidden('act','wcrn');
            echo hidden('frm','wcrt');
            echo hidden('pid', $pid);
            echo hidden('jid', $cjid);
            echo hidden('gid', $cgid);
            echo hidden('pcn','wiz');

            echo <<< WCRT

            <p>
              You can choose to have the critical updates listed above
              installed the next time the ASI MS Windows update
              management procedure (Scrip) is scheduled to run,
            </p>

            <p>
              ${in}$o0 Send update information to machines
              at next scheduled time
            </p>

            <p>
              or, you can have the the MS Windows update
              management procedure (Scrip) run immediately
              (as soon as the next Scrip configuration
              update on the ASI server performed by
              Scrip 177 occurs)
            </p>

            <p>
              ${in}$o1 Send update information to machines
              as soon as possible
            </p>

            <p>
              You can notify your users about this update
              as it is happening. To do so, check the box
              below and fill in the text you want to use
              for the notification. Please note that the
              notification will be displayed as installation
              of the update is about to start.
            </p>

            ${in}$c1 Enable Notification<br>
            <br>
            ${in}Notification Text:<br>
            ${in}$area<br>

WCRT;

            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        else
        {
            wizard_return($env,$db);
        }

        GRPW_ProcessSelection($env, $cjid, $db);
        echo again($env);
    }



    function wiz_crit_disp(&$env,$db)
    {
        debug_note('<b>wcrt</b> wiz_crit_disp');

        $txt = <<< WCRT

        <p>
          This wizard lets you install a critical
          update immediately. You should use it
          whenever there is a critical issue
          (e.g. security hole) whose resolution
          requires application of an update.
        </p>

        <p>
          Please note that <i>Install critical updates</i> wizard
          actions override settings configured using the <i>Select
          test machines to install updates before other machines</i>
          wizard. This means that if you selected a group of machines
          to test updates before installing them on all other machines,
          when you use the <i>Install critical updates</i> wizard to
          install updates on all machines, the updates selected with
          this wizard will be installed ignoring the settings
          configured with the <i>Select test machines to install
          updates before other machines</i> wizard.
        </p>

WCRT;

        wiz_generic_disp($env,'crit',$txt,0,$db);
    }



    function wiz_edit_mgrp(&$env,$db)
    {
        debug_note('<b>wemg</b> wiz_edit_mgrp');
        if ($env['gid'] > 0)
        {
            echo again($env);
            switch($env['custom'])
            {
                case constPageEntryTools   :
                case constPageEntrySites   : wiz_tools_finish();
                break;

	        case constDashStatus_SelectMachineGroup :
	            $f_msg = wiz_dash_finish( );
	        break;

                case constPageEntryAsset   : $f_msg = wiz_asset_finish();
                break;

                case constPageEntryNotfy   : $f_msg = wiz_notfy_finish();
                break;

                case constPageEntryReports : $f_msg = wiz_report_finish();
                break;

                default                    : wizard_return($env, $db);
                break;
            }
            echo $f_msg;
            echo again($env);
        }
        else
        {
            wiz_pop($env,$db);
        }
    }


    function schedule_common(&$pcfg)
    {
        $pid  = $pcfg['pconfigid'];
        $rusr = $pcfg['reminduser'];
        $pshd = $pcfg['preventshutdown'];

        $ssec = $pcfg['scheddelay'];
        $smin = $pcfg['schedminute'];
        $shor = $pcfg['schedhour'];
        $sday = $pcfg['schedday'];
        $smon = $pcfg['schedmonth'];
        $swek = $pcfg['schedweek'];
        $srnd = $pcfg['schedrandom'];
        $styp = $pcfg['schedtype'];

        $nmin = $pcfg['notifyminute'];
        $nhor = $pcfg['notifyhour'];
        $nday = $pcfg['notifyday'];
        $nmon = $pcfg['notifymonth'];
        $nwek = $pcfg['notifyweek'];
        $nrnd = $pcfg['notifyrandom'];
        $ntyp = $pcfg['notifytype'];
        $nfal = $pcfg['notifyfail'];

        $nadv = $pcfg['notifyadvance'];
        $nsch = $pcfg['notifyschedule'];
        $ntxt = $pcfg['notifytext'];
        $nat  = $pcfg['notifyadvancetime'];

        $in   = indent(5);
        $xn   = indent(10);

        $hopt = hour_options();
        $mopt = month_options();
        $nopt = minute_options();
        $wopt = wday_options();
        $dopt = mday_options();

        $sdel = textbox('sdel',2,round($ssec / 86400));
        $nfal = textbox('nfal',2,value_range(0, 99,$nfal));
        $srnd = textbox('srnd',3,value_range(0,999,$srnd));
        $nrnd = textbox('nrnd',3,value_range(0,999,$nrnd));

        $styp = value_range(1,2,$styp);
        $ntyp = value_range(1,2,$ntyp);
        $s1   = radio('styp',constPatchTypeASAP,$styp);
        $s2   = radio('styp',constPatchTypeNext,$styp);
        $n1   = radio('ntyp',constPatchTypeASAP,$ntyp);
        $n2   = radio('ntyp',constPatchTypeNext,$ntyp);

        $sday = html_select('sday',$dopt,$sday,1);
        $nday = html_select('nday',$dopt,$nday,1);
        $smon = html_select('smon',$mopt,$smon,1);
        $nmon = html_select('nmon',$mopt,$nmon,1);
        $shor = html_select('shor',$hopt,$shor,1);
        $nhor = html_select('nhor',$hopt,$nhor,1);
        $swek = html_select('swek',$wopt,$swek,1);
        $nwek = html_select('nwek',$wopt,$nwek,1);
        $smin = html_select('smin',$nopt,$smin,1);
        $nmin = html_select('nmin',$nopt,$nmin,1);

        $nadv = value_range(0,1,$nadv);
        $rusr = value_range(0,1,$rusr);
        $pshd = value_range(0,1,$pshd);
        $nsch = value_range(0,1,$nsch);

        $c1   = checkbox('nadv',$nadv);
        $c2   = checkbox('rusr',$rusr);
        $c3   = checkbox('pshd',$pshd);
        $c4   = checkbox('nsch',$nsch);

        $mins = intval(round($nat / 60));
        $mins = value_range(0,999,$mins);
        $mins = textbox('mins',3,$mins);
        $area = areabox('txt',10,60,'',$ntxt);

        $shed = mark('shed');
        $ntfy = mark('ntfy');

        echo <<< HERE

        $shed

        <font size="+1">Schedule</font>

        <br><br>
        $in<b>Schedule to use for scheduled operations:</b><br>
        {$xn}Delay operation by $sdel days.<br>
        {$xn}Month: $smon Day: $sday Hour: $shor Minute: $smin Weekday: $swek<br>
        {$xn}Random delay in minutes $srnd

        <br><br>
        $in<b>Action on missed schedule:</b><br>
        ${xn}$s1 run as soon as possible<br>
        ${xn}$s2 run at next scheduled time

        <br><br>
        <hr>
        <br><br>

        $ntfy

        <font size="+1">Notification</font>

        <p>
          Please note that the <i>Notify XX minutes before action</i> and
          <i>Schedule notification</i> options are mutually exclusive. Both
          notifications occur if the <i>Notify XX minutes before action</i>
          notification occurs before the scheduled notification (<i>Schedule
          notification</i> option enabled). If the scheduled notification
          (<i>Schedule notification</i> option enabled) occurs before the
          <i>Notify XX minutes before action</i> notification, the <i>
          Notify XX minutes before action</i> notification will not occur.
          For example if the <i>Notify XX minutes before action</i>
          notification is configured to occur 15 minutes before updates are
          installed, at 2.45 AM, and the scheduled notification (<i>Schedule
          notification</i> option enabled) is configured to occur daily at 4 PM,
          the <i>Notify XX minutes before action</i> notification will not
          occur.
        </p>

        <p>
          The <i>Remind user to leave system on</i> and <i>Prevent system
          shutdown</i> options can be used independently of, and together
          with, the other notification configuration options.
        </p>

        $in<b>Notification options:</b><br>
        ${xn}$c1 Notify $mins minutes before action<br>
        ${xn}$c2 Remind user to leave system on.<br>
        ${xn}$c3 Prevent system shutdown.<br>
        ${xn}$c4 Schedule notification

        <br><br>
        $in<b>Schedule to use for scheduled notifications:</b><br>
        ${xn}Month: $nmon Day: $nday Hour: $nhor Minute $nmin Weekday: $nwek<br>
        ${xn}Random delay in minutes $nrnd

        <br><br>
        $in<b>Action on missed schedule:</b><br>
        ${xn}$n1 run as soon as possible<br>
        ${xn}$n2 run at next scheduled time, report failure after $nfal misses<br>

        <br><br>
        $in<b>Notification text:</b><br><br>
        ${xn}$area<br>
HERE;

    }


    function form_pconfig(&$env,$db)
    {
        echo again($env);
        $gid  = $env['gid'];
        $jid  = $env['jid'];
        $auth = $env['auth'];
        $adm  = $env['admn'];
        $mgrp = find_pub_mgrp_own($env,$gid,$db);
        $pgrp = find_pub_pgrp_jid($jid,$auth,$db);
        $mcat = mcat_options($db);
        $pcat = pcat_options($db);
        $row  = find_pcfg($gid,$jid,$db);
        if (($mgrp) && ($pgrp) && ($mcat) && ($pcat) && ($row))
        {
            $tid  = $mgrp['mcatid'];
            $kid  = $pgrp['pcategoryid'];
            $mgnm = $mgrp['name'];
            $pgnm = $pgrp['name'];
            $mcnm = @ trim($mcat[$tid]);
            $pcnm = @ trim($pcat[$kid]);
            $mcp  = 'Category';
            $mgp  = 'Name';
            if ($mcnm == 'Machine')
            {
                $cen = census_gid($gid,$db);
                if ($cen)
                {
                    $mcp  = 'Site';
                    $mgp  = 'Machine';
                    $mcnm = $cen['site'];
                    $mgnm = $cen['host'];
                }
            }

            $now  = time();
            $pid  = $row['pconfigid'];
            $ins  = $row['installation'];

            $in   = indent(5);
            $xn   = indent(10);

            $ins = check_install($ins);
            $i1  = radio('ins',constPatchInstallNever,$ins);
            $i4  = radio('ins',constPatchScheduleInstall,$ins);
            $i5  = radio('ins',constPatchScheduleRemove,$ins);

            echo post_self('myform');
            echo hidden('act','upid');
            echo hidden('frm','fpid');
            echo hidden('pcn','lpid');
            echo hidden('pid',$pid);
            echo hidden('gid',$gid);
            echo hidden('jid',$jid);

            echo <<< HERE

            <br><b>Update Group:</b><br>
            ${in}Category: <b>$pcnm</b><br>
            ${in}Name: <b>$pgnm</b><br>

            <br><b>Machine Group:</b><br>
            ${in}$mcp: <b>$mcnm</b><br>
            ${in}$mgp: <b>$mgnm</b>

            <br><br>
            <b>Desired state:</b><br>
            ${in}$i4 Approve<br>
            ${in}$i1 Decline<br>
            ${in}$i5 Remove

            <br><br>
HERE;
            schedule_common($row);
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        else
        {
            echo para('There is a problem with this form.');
        }
        echo again($env);
    }

    function edit_pconfig(&$env,$db)
    {
        echo again($env);
        $tid  = $env['tid'];
        $gid  = $env['gid'];
        $kid  = $env['kid'];
        $jid  = $env['jid'];
        $auth = $env['auth'];

        $mcat = mcat_options($db);
        $pcat = pcat_options($db);
        $mgrp = mgrp_options($auth,$db);
        $pgrp = pgrp_options($auth,$db);
        $mopt = (isset($mgrp[$tid]))? $mgrp[$tid] : $mgrp[0];
        $popt = (isset($pgrp[$kid]))? $pgrp[$kid] : $mgrp[0];

        $tid = js_select('tid',$mcat,$tid,1);
        $kid = js_select('kid',$pcat,$kid,1);
        $jid = html_select('jid',$popt,$jid,1);
        $gid = html_select('gid',$mopt,$gid,1);
        $in  = indent(5);

        echo post_self('myform');
        echo hidden('act','cpid');
        echo hidden('frm','epid');
        echo hidden('pcn','menu');

        echo <<< EPID

        Edit update configuration for machine group:<br>
        ${in}category: $tid<br>
        ${in}name: $gid<br>
        and update group:<br>
        ${in}category: $kid<br>
        ${in}name: $jid<br>

EPID;

        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }




    function order($ord)
    {
        switch ($ord)
        {
                        case  0: return 'precedence, pcategoryid';
            case  1: return 'precedence desc, pcategoryid';
            case  2: return 'category, pcategoryid';
            case  3: return 'category desc, pcategoryid';
            case  4: return 'number desc, category, pcategoryid';
            case  5: return 'number, category, pcategoryid';
                        case  6: return 'ppre, pgrp, pid';
            case  7: return 'ppre desc, pgrp desc, pid';
            case  8: return 'mpre, mgrp, pid';
            case  9: return 'mpre desc, mgrp desc, pid';
            case 10: return 'pcat, pgrp, pid';
            case 11: return 'pcat desc, pgrp desc, pid desc';
            case 12: return 'mcat, mgrp, pid';
            case 13: return 'mcat desc, mgrp desc, pid desc';
            case 14: return 'pgrp, ppre, pid';
            case 15: return 'pgrp desc, ppre desc, pid';
            case 16: return 'mgrp, mpre, pid';
            case 17: return 'mgrp desc, mpre desc, pid';
            case 18: return 'ins, ppre, mpre, pid';
            case 19: return 'ins desc, ppre desc, mpre desc, pid';
            case 20: return 'ntfy, ppre, mpre, pid';
            case 21: return 'ntfy desc, ppre desc, mpre desc, pid';
                        case 22: return 'name, pgroupid desc';
            case 23: return 'name desc, pgroupid';
            case 24: return 'style, name, pgroupid';
            case 25: return 'style desc, name desc, pgroupid';
            case 26: return 'boolstring, name, pgroupid';
            case 27: return 'boolstring desc, name desc, pgroupid';
            case 28: return 'number desc, name, pgroupid';
            case 29: return 'number, name desc, pgroupid';
            case 30: return 'global desc, name, pgroupid';
            case 31: return 'global, name desc, pgroupid';
            case 32: return 'username, name, pgroupid';
            case 33: return 'username desc, name desc, pgroupid';
                        case 34: return 'pre, name, wid';
            case 35: return 'pre desc, name desc, wid';
            case 36: return 'cat, name, wid';
            case 37: return 'cat desc, name desc, wid';
            case 38: return 'name, cat, wid';
            case 39: return 'name desc, cat desc, wid';
            case 40: return 'man, name, wid';
            case 41: return 'man desc, name desc, wid desc';
            case 42: return 'new, name, wid';
            case 43: return 'new desc, name desc, wid desc';
            case 44: return 'src, name, wid';
            case 45: return 'src desc, name desc, wid desc';
            case 46: return 'prop, name, wid';
            case 47: return 'prop desc, name desc, wid desc';
            default: return order(0);
        }
    }

    function wiz_order($ord)
    {
        switch ($ord)
        {
            case  0: return 'name, date, patchid';
            case  1: return 'name desc, patchid';
            case  2: return 'date desc, name, patchid';
            case  3: return 'date, name desc, patchid';
            case  4: return 'component, name, patchid';
            case  5: return 'component desc, name, patchid';
            case  6: return 'platform, name, patchid';
            case  7: return 'platform desc, name, patchid';
            case  8: return 'size desc, name, patchid';
            case  9: return 'size, name desc, patchid';
            case 10: return 'prio desc, name, patchid';
            case 11: return 'prio, name, patchid';
            case 12: return 'canuninstall desc, name, patchid';
            case 13: return 'canuninstall, name, patchid';
            default: return wiz_order(0);
        }
    }




    function stat_order($ord)
    {
        switch ($ord)
        {
            case  0: return 'name';
            case  1: return 'name desc';
            case  2: return 'schedtype, scheddelay desc,'
                       .    ' schedmonth, schedweek desc,'
                       .    ' schedday, schedhour desc,'
                       .    ' schedminute desc, schedrandom';
            case  3: return 'schedtype desc, scheddelay,'
                       .    ' schedmonth desc,schedweek,'
                       .    ' schedday desc, schedhour,'
                       .    ' schedminute, schedrandom desc';
            case  4: return 'notifyadvance desc, notifyadvancetime';
            case  5: return 'notifyadvance, notifyadvancetime desc';
            case  6: return 'installation desc, name';
            case  7: return 'installation, name desc';
            case  8: return 'lastupdate desc, mgroupid';               case  9: return 'lastupdate, mgroupid desc';
            case 10: return 'propagate desc, name, id';
            case 11: return 'propagate, name desc, id';
            case 12: return 'updatecache, cacheseconds desc, name, id';
            case 13: return 'updatecache desc, cacheseconds, name, id';
            case 14: return 'name, id desc';
            case 15: return 'name desc, id';
            default: return stat_order(0);
        }
    }


    function wiz_ords()
    {
        $u = 'Update';
        $c = 'Component';
        $p = 'Platform';
        $s = 'Size';
        $r = 'Priority';
        $n = 'Uninstallable';
        $a = 'ascending';
        $d = 'descending';
        return array
        (
              0 => "$u ($a)",
              1 => "$u ($d)",
              2 => "Date / $u ($d)",
              3 => "Date / $u ($a)",
              4 => "$c / $u ($a)",
              5 => "$c / $u ($d)",
              6 => "$p / $u ($a)",
              7 => "$p / $u ($d)",
              8 => "$s / $u ($d)",
              9 => "$s / $u ($a)",
             10 => "$r / $u ($d)",
             11 => "$r / $u ($a)",
             12 => "$n / $u ($d)",
             13 => "$n / $u ($a)"
        );
    }



    function wcfg_explanation(&$env,$db)
    {
        echo <<< LWID

        <p>
          This is a list of all the machine configurations that are defined.
          Each row in the table represents a single machine configuration.
        </p>

        <p>
          A machine configuration applies to a group of machines
          and controls how the Windows Update process runs on all
          the machines that are in the group.  On this page, you
          can see all the machine configurations, edit or delete
          an existing machine configuration, add a completely new
          machine configuration, or make a copy of an existing machine
          configuration.
        </p>
LWID;

    }

    function pcfg_explanation(&$env,$db)
    {
        echo <<< LPID

        <p>
          This is a list of all the software update configurations that are
          defined.  Each row in the table represents a single software update
          configuration.
        </p>

        <p>
          A software update configuration applies to a group of updates on a
          group of machines and controls how those updates are applied to those
          machines.  The group of updates is defined using a software update
          group, and the group of machines is defined using a machine group.
          (Since each software update configuration applies to a specific set
          of updates on a specific set of machines, each row in this table
          specifies both the software update group and the machine group
          defining the configuration.)
        </p>

        <p>
          On this page, you can see all the software update configurations,
          edit or delete an existing software update configuration, add a
          completely new software update configuration, or make a copy of an
          existing machine configuration.
        </p>

LPID;

    }




    function list_wcfg(&$env,$db)
    {
        echo again($env);
        $ord = value_range(34,47,$env['ord']);
        $qu  = safe_addslashes($env['auth']);
        $wrd = order($ord);
        $sql = "select W.id as wid,\n"
             . " G.mgroupid as gid,\n"
             . " G.name as name,\n"
             . " C.category as cat,\n"
             . " C.precedence as pre,\n"
             . " W.management as man,\n"
             . " W.newpatches as new,\n"
             . " W.propagate as prop,\n"
             . " W.patchsource as src\n"
             . " from WUConfig as W,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = X.site\n"
             . " and M.censusuniq = X.censusuniq\n"
             . " and W.mgroupid = G.mgroupid\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " and G.mcatuniq = C.mcatuniq\n"
             . " group by gid\n"
             . " order by $wrd";
        $set = find_many($sql,$db);
        if ($set)
        {
            wcfg_explanation($env,$db);
            $bool = array('No','Yes');
            $self = $env['self'];
            $adm  = $env['admn'];
            $o    = "$self?act=lwid&ord";
            $pre  = ($ord == 34)? "$o=35" : "$o=34";               $cat  = ($ord == 36)? "$o=37" : "$o=36";               $name = ($ord == 38)? "$o=39" : "$o=38";               $man  = ($ord == 40)? "$o=41" : "$o=40";               $new  = ($ord == 42)? "$o=43" : "$o=42";               $src  = ($ord == 44)? "$o=45" : "$o=44";               $prop = ($ord == 46)? "$o=47" : "$o=46";
            $a   = array('Action');
            $a[] = html_link($cat, 'Category');
            $a[] = html_link($name,'Name');
            $a[] = html_link($pre, 'Priority');
            $a[] = html_link($man, 'Management');
            $a[] = html_link($new, 'Default');
            $a[] = html_link($src, 'Source');
            $a[] = html_link($prop,'Propagate');

            $rows = safe_count($set);
            $cols = safe_count($a);
            $text = "Machine Configurations &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($a,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $wid  = $row['wid'];
                $gid  = $row['gid'];
                $pre  = $row['pre'];
                $cat  = disp($row,'cat');
                $name = disp($row,'name');
                $all  = ($name == constCatAll);
                $new  = newpatch($row['new']);
                $man  = management($row['man']);
                $src  = source($row['src']);
                $prop = propagate($row['prop']);
                $cmd  = "$self?wid=$wid&gid=$gid&act";
                $a    = array();
                if (($adm) || (!$all))
                {
                    $a[]  = html_link("$cmd=fwid",'[edit]');
                    $a[]  = html_link("$cmd=pwid",'[delete]');
                }
                $a[]  = html_link("$cmd=cpwa",'[copy]');
                $name = html_link("$cmd=gdet",$name);
                $acts = join('<br>',$a);
                $args = array($acts,$cat,$name,$pre,$man,$new,$src,$prop);
                echo table_data($args,0);
            }

            echo table_footer();
            echo clear_all();
        }
        else
        {
            echo para('No machine configurations yet');
        }
        echo again($env);
    }



    function list_pcfg(&$env,$db)
    {
        echo again($env);
        $ord = value_range(6,21,$env['ord']);
        $qu  = safe_addslashes($env['auth']);
        $wrd = order($ord);
        $sql = "select P.pconfigid as pid,\n"
             . " G.mgroupid as gid,\n"
             . " J.pgroupid as jid,\n"
             . " G.name as mgrp,\n"
             . " J.name as pgrp,\n"
             . " C.category as mcat,\n"
             . " K.category as pcat,\n"
             . " C.precedence as mpre,\n"
             . " K.precedence as ppre,\n"
             . " P.installation as ins,\n"
             . " P.notifyadvance as ntfy\n"
             . " from PatchGroups as J,\n"
             . " PatchConfig as P,\n"
             . " PatchCategories as K,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as X,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
             . " where U.username = '$qu'\n"
             . " and U.customer = X.site\n"
             . " and M.censusuniq = X.censusuniq\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " and P.mgroupid = G.mgroupid\n"
             . " and P.pgroupid = J.pgroupid\n"
             . " and G.mcatuniq = C.mcatuniq\n"
             . " and K.pcategoryid = J.pcategoryid\n"
             . " group by pid\n"
             . " order by $wrd";
        $set = find_many($sql,$db);
        if ($set)
        {
            pcfg_explanation($env,$db);
            $bool = array('No','Yes');
            $self = $env['self'];
            $adm  = $env['admn'];
            $o    = "$self?act=lpid&ord";

            $ppre = ($ord ==  6)? "$o=7"  : "$o=6";                $mpre = ($ord ==  8)? "$o=9"  : "$o=8";                $pcat = ($ord == 10)? "$o=11" : "$o=10";               $mcat = ($ord == 12)? "$o=13" : "$o=12";               $pgrp = ($ord == 14)? "$o=15" : "$o=14";               $mgrp = ($ord == 16)? "$o=17" : "$o=16";               $ins  = ($ord == 18)? "$o=19" : "$o=18";               $ntfy = ($ord == 20)? "$o=21" : "$o=20";
            $a   = array('Action');
            $a[] = html_link($mcat,'Machine<br>Category');
            $a[] = html_link($mgrp,'Machine<br>Group');
            $a[] = html_link($mpre,'Machine<br>Priority');
            $a[] = html_link($pcat,'Update<br>Category');
            $a[] = html_link($pgrp,'Update<br>Group');
            $a[] = html_link($ppre,'Update<br>Priority');
            $a[] = html_link($ins, 'Installation');
            $a[] = html_link($ntfy,'Notification');

            $cols = safe_count($a);
            $rows = safe_count($set);
            $text = "Update Configurations &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($a,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $pid  = $row['pid'];
                $gid  = $row['gid'];
                $jid  = $row['jid'];
                $mpre = $row['mpre'];
                $ppre = $row['ppre'];
                $ntfy = $bool[$row['ntfy']];
                $mcat = disp($row,'mcat');
                $mgrp = disp($row,'mgrp');
                $pcat = disp($row,'pcat');
                $pgrp = disp($row,'pgrp');
                $all  = ($mgrp == constCatAll);
                $ins  = installation($row['ins']);
                $cmd  = "$self?jid=$jid&gid=$gid&act";
                $a    = array();
                if (($adm) || (!$all))
                {
                    $a[] = html_link("$cmd=fpid",'[edit]');
                    $a[] = html_link("$cmd=ppid",'[delete]');
                }
                $mgrp = html_link("$cmd=gdet",$mgrp);
                $a[]  = html_link("$cmd=cppa",'[copy]');
                $acts = join('<br>',$a);
                $args = array($acts,$mcat,$mgrp,$mpre,$pcat,$pgrp,$ppre,$ins,$ntfy);
                echo table_data($args,0);
            }

            echo table_footer();
            echo clear_all();
        }
        else
        {
            echo para('No software update configurations yet');
        }
        echo again($env);
    }


    function del_pcfg_perm(&$env,$gid,$jid,$db)
    {
        $info = array( );
        $mgrp = find_mgrp_own($env,$gid,$db);
        $pgrp = find_pgrp_jid($jid,$db);
        $pcfg = find_pcfg($gid,$jid,$db);

        if((($env['act']=='ppic') || ($env['act']=='dpic')) && ($pcfg))
        {
            $sql = "SELECT PatchConfig.*, PatchGroups.name FROM PatchConfig "
                . " LEFT JOIN PatchGroups ON (PatchConfig.pgroupid="
                . "PatchGroups.pgroupid) WHERE wpgroupid="
                . $pcfg['wpgroupid'] . " AND mgroupid=" . $mgrp['mgroupid'];
            $set = find_many($sql, $db);
        }
        else
        {
            $set = array();
            $set[] = $pcfg;
        }

        if (($mgrp) && ($pgrp) && ($set) && ($pcfg))
        {

            $tid = $mgrp['mcatid'];
            $kid = $pgrp['pcategoryid'];
            $mcat = find_mcat_tid($tid,$db);
            $pcat = find_pcat_kid($kid,$db);
            foreach ($set as $key => $row)
            {
                if (($mcat) && ($pcat))
                {
                    $auth = $env['auth'];
                    $admn = $env['admn'];
                    $mtyp = $mgrp['style'];
                    $ptyp = $pgrp['style'];
                    $musr = $mgrp['username'];
                    $pusr = $mgrp['username'];

                    $mok = (($musr == $auth) || ($mtyp == constStyleBuiltin));
                    $pok = (($pusr == $auth) || ($ptyp == constStyleBuiltin));

                    if (($admn) || (($mok) && ($pok)))
                    {
                        $info[$row['pconfigid']] = array();
                        $info[$row['pconfigid']]['mgrp'] = $mgrp;
                        $info[$row['pconfigid']]['pgrp'] = $pgrp;
                        $info[$row['pconfigid']]['pcfg'] = $row;
                        $info[$row['pconfigid']]['mcat'] = $mcat;
                        $info[$row['pconfigid']]['pcat'] = $pcat;
                        $info[$row['pconfigid']]['pgname'] = @$row['name'];
                    }
                }
            }
        }
        return $info;
    }


    function del_pcfg_conf(&$env,$db)
    {
        $jid = $env['jid'];
        $gid = $env['gid'];
        $act = $env['act'];
        $wiz = ($act == 'ppic');

        echo again($env);

        debug_note("<b>$act</b> del_pcfg_conf (g:$gid,j:$jid)");
        $info = del_pcfg_perm($env,$gid,$jid,$db);
        if ($info)
        {
            $pg = '';
            foreach ($info as $key => $row)
            {
                if(strcmp($pg, '')!=0)
                {
                    $pg .= ", ";
                }
                $mg = $row['mgrp']['name'];
                $pg .= $row['pgname'];
                $mc = $row['mcat']['category'];
                $pc = $row['pcat']['category'];
            }
            $in = indent(5);

            $nxt = ($wiz)? 'dpic' : 'dpid';
            $pno = ($wiz)? 'wsts' : 'lpid';

            echo post_self('myform');
            echo hidden('act',$nxt);
            echo hidden('frm',$act);
            echo hidden('pno',$pno);
            echo hidden('gid',$gid);
            echo hidden('jid',$jid);

            echo <<< HERE

            Delete update configuration for machine group:<br>
            ${in}category: <b>$mc</b><br>
            ${in}name: <b>$mg</b><br>
            and update group:<br>
            ${in}category: <b>$pc</b><br>
            ${in}name: <b>$pg</b><br>

HERE;
            echo askyesno(5);
            echo form_footer();
        }
        else
        {
            echo para('Permission denied');
        }
        echo again($env);
    }


    function del_pcfg_act(&$env,$db)
    {
        $jid = $env['jid'];
        $gid = $env['gid'];
        $act = $env['act'];
        $wiz = ($act == 'dpic');

        debug_note("<b>$act</b> del_pcfg_act (g:$gid,j:$jid) ");
        $info = del_pcfg_perm($env,$gid,$jid,$db);
        if ($info)
        {
            foreach ($info as $key => $row)
            {
                $sql = "DELETE FROM PatchConfig WHERE pconfigid=$key";
                redcommand($sql, $db);
                $name = $row['mgrp']['name'];
                echo para("Update configuration for <b>$name</b> (pconfigid "
                    . "$key for " . $row['pgname'] . ") removed.");
                 }
        }
        if ($wiz)
        {
            wiz_stat($env,$db);
        }
        else
        {
            list_pcfg($env,$db);
        }
    }


    function del_wcfg_perm(&$env,$wid,$gid,$db)
    {
        $info = array();
        $wcfg = find_wcfg_gid($gid,$db);
        $mgrp = find_mgrp_own($env,$gid,$db);
        if (($mgrp) && ($wcfg))
        {
            $admn = $env['admn'];
            $auth = $env['auth'];
            $wwid = $wcfg['id'];
            $user = $mgrp['username'];
            $type = $mgrp['style'];
            if ($wwid == $wid)
            {
                $own = ($user == $auth)? 1 : 0;
                $pub = ($type == constStyleBuiltin)? 1 : 0;
                if (($own) || ($pub) || ($admn))
                {
                    $info['mgrp'] = $mgrp;
                    $info['wcfg'] = $wcfg;
                }
            }
            debug_note("gid:$gid wid:$wid user:$user auth:$auth wwid:$wwid");
        }
        else
        {
            if (!$wcfg) debug_note("wcfg $wid does not exist");
            if (!$mgrp) debug_note("mgrp $gid does not exist");
        }
        return $info;
    }



    function del_wcfg_conf(&$env,$db)
    {
        echo again($env);

        $wid = $env['wid'];
        $gid = $env['gid'];
        $act = $env['act'];
        $wiz = ($act == 'pwic');
        debug_note("<b>$act</b> del_wcfg_conf (g:$gid,w:$wid)");
        $info = del_wcfg_perm($env,$wid,$gid,$db);
        if ($info)
        {
            $nxt  = ($wiz)? 'dwic' : 'dwid';
            $pno  = ($wiz)? 'wsts' : 'lwid';
            $name = $info['mgrp']['name'];
            echo post_self('myform');
            echo hidden('act',$nxt);
            echo hidden('frm',$act);
            echo hidden('pno',$pno);
            echo hidden('gid',$gid);
            echo hidden('wid',$wid);
            echo para("Delete machine configuration for <b>$name</b>?");
            echo askyesno(5);
            echo form_footer();
        }
        else
        {
            echo para('Machine configuration not found.');
        }
        echo again($env);
    }





    function del_wcfg_act(&$env,$db)
    {
        $wid = $env['wid'];
        $gid = $env['gid'];
        $act = $env['act'];
        $wiz = ($act == 'dwic');

        debug_note("<b>$act</b> del_wcfg_act (g:$gid,w:$wid)");

        $info = del_wcfg_perm($env,$wid,$gid,$db);
        if ($info)
        {
            $wid  = $info['wcfg']['id'];
            $name = $info['mgrp']['name'];
            if (kill_wcfg_gid($gid,$db))
            {
                    echo para("Machine configuration for <b>$name</b> deleted.");
            }
        }
        if ($wiz)
        {
            wiz_stat($env,$db);
        }
        else
        {
            list_wcfg($env,$db);
        }
    }


    function insert_wconfig(&$env,$db)
    {
        $gid  = $env['gid'];
        $auth = $env['auth'];
        $mgrp = find_pub_mgrp_own($env,$gid,$db);
        $wcfg = find_wcfg_gid($gid,$db);
        $save = false;
        if (($mgrp) && (!$wcfg))
        {
            $save = true;
            $mall = find_mgrp_name(constCatAll,$db);
            $agid = ($mall)? $mall['mgroupid'] : 0;
            $wcfg = find_wcfg_gid($agid,$db);
        }
        if (($mgrp) && (!$wcfg))
        {
            $save = true;
            $wcfg = default_wcfg();
        }
        if (($mgrp) && ($wcfg) && ($save))
        {
            $wid = insert_wcfg($gid,$wcfg,$db);
                        $env['wid'] = $wid;
        }
        form_wconfig($env,$db);
    }



    function pconfig_host(&$env,$db)
    {
        $hid  = $env['hid'];
        $mgrp = find_host_mgrp($hid,$db);
        $pgrp = find_pgrp_name(constPatchAll,$db);
        if (($mgrp) && ($pgrp))
        {
            $env['gid'] = $mgrp['mgroupid'];
            $env['jid'] = $pgrp['pgroupid'];
            check_pconfig($env,$db);
        }
        else
        {
            echo again($env);
            echo parabold('No machine group found.');
            echo again($env);
        }
    }


    function pconfig_site(&$env,$db)
    {
        $pgrp = array( );
        $cid  = $env['cid'];
        $site = $env['site'];
        $mgrp = find_site_mgrp($site,$db);
        if (($cid) && ($site))
        {
            $pgrp = find_pgrp_name(constPatchAll,$db);
        }
        if (($mgrp) && ($pgrp))
        {
            $env['gid'] = $mgrp['mgroupid'];
            $env['jid'] = $pgrp['pgroupid'];
            check_pconfig($env,$db);
        }
        else
        {
            echo again($env);
            echo parabold('No machine group found.');
            echo again($env);
        }
    }


    function wconfig_host(&$env,$db)
    {
        $hid = $env['hid'];
        $cen = find_host($hid,$db);
        $grp = find_host_mgrp($hid,$db);
        if (($grp) && ($cen))
        {
            $site = $cen['site'];
            $host = $cen['host'];
            echo <<< HERE

            <p>
              A machine configuration applies to a group of machines and
              controls how the Windows Update process runs on all the
              machines that are in the group.  You are about to create a
              new machine configuration that will apply to the machine
              group for the single machine <b>$host</b> at site
              <b>$site</b>.  Do you want to continue?  (Even if you create
              a machine configuration incorrectly, it is easy to delete.)
            </p>

HERE;

            $gid  = $grp['mgroupid'];
            $tid  = $grp['mcatid'];
            $row  = find_wcfg_gid($gid,$db);
            $env['tid'] = $tid;
            $env['gid'] = $gid;
            if ($row)
            {
                form_wconfig($env,$db);
            }
            else
            {
                create_wcfg($env,$db);
            }
        }
        else
        {
            echo para('The specified machine group was not found.');
        }
    }


    function config_site(&$env,$db)
    {
        $site = $env['site'];
        $grp  = find_site_mgrp($site,$db);
        if ($grp)
        {
            $gid  = $grp['mgroupid'];
            $tid  = $grp['mcatid'];
            $row  = find_wcfg_gid($gid,$db);
            $env['tid'] = $tid;
            $env['gid'] = $gid;
            if ($row)
            {
                form_wconfig($env,$db);
            }
            else
            {
                create_wcfg($env,$db);
            }
        }
        else
        {
            echo para("The specified group <b>$site</b> was not found.");
        }
    }





    function check_wconfig(&$env,$db)
    {
        $gid = $env['gid'];
        $grp = find_pub_mgrp_own($env,$gid,$db);
        $row = find_wcfg_gid($gid,$db);
        if (($grp) && ($row))
        {
            form_wconfig($env,$db);
        }
        else
        {
            echo again($env);
            if ($grp)
            {
                $name = $grp['name'];
                $text = "Create a new machine configuration for <b>$name</b>?";
                echo post_self('myform');
                echo hidden('act','iwid');
                echo hidden('frm','fwid');
                echo hidden('pno','menu');
                echo hidden('gid',$gid);
                echo para($text);
                echo askyesno(5);
                echo form_footer();
            }
            else
            {
                echo para('The specified groups have vanished.');
            }
            echo again($env);
        }
    }




    function check_pconfig(&$env,$db)
    {
        $auth = $env['auth'];
        $gid  = $env['gid'];
        $jid  = $env['jid'];
        $mgrp = find_pub_mgrp_own($env,$gid,$db);
        $pgrp = find_pub_pgrp_jid($jid,$auth,$db);
        $row  = find_pcfg($gid,$jid,$db);
        if (($mgrp) && ($pgrp) && ($row))
        {
            form_pconfig($env,$db);
        }
        else
        {
            echo again($env);
            if (($mgrp) && ($pgrp))
            {
                $pval = $pgrp['name'];
                $mval = $mgrp['name'];

                echo post_self('myform');
                echo hidden('act','ipid');
                echo hidden('frm','fpid');
                echo hidden('pno','lpid');
                echo hidden('gid',$gid);
                echo hidden('jid',$jid);
                echo "<p>Create a new configuration for <b>$pval</b> of <b>$mval</b>?</p>";
                echo askyesno(5);
                echo form_footer();
            }
            else
            {
                echo para('The specified groups have vanished.');
            }
            echo again($env);
        }
    }





    function insert_pconfig(&$env,$db)
    {
        $pid  = 0;
        $gid  = $env['gid'];
        $jid  = $env['jid'];
        $auth = $env['auth'];
        $mgrp = find_pub_mgrp_own($env,$gid,$db);
        $pgrp = find_pub_pgrp_jid($jid,$auth,$db);
        $pcfg = find_pcfg($gid,$jid,$db);
        if (($mgrp) && ($pgrp) && (!$pcfg))
        {
            $mall = find_mgrp_name(constCatAll,$db);
            $pall = find_pgrp_name(constPatchAll,$db);
            $pgid = ($mall)? $mall['mgroupid'] : 0;
            $pjid = ($pall)? $pall['pgroupid'] : 0;
            if ((!$pcfg) && ($pgid != $gid))
            {
                $pcfg = find_pcfg($pgid,$jid,$db);
            }
            if ((!$pcfg) && ($pjid != $jid))
            {
                $pcfg = find_pcfg($gid,$pjid,$db);
            }
            if (!$pcfg)
            {
                $pcfg = seek_pcfg($auth,$db);
            }
            if ($pcfg)
            {
                $pid = insert_pcfg($gid,$jid,$pcfg,$jid,$db);
            }
            if ($pid)
            {
                $env['pid'] = $pid;
            }
        }
        form_pconfig($env,$db);
    }



    function update_pconfig(&$env,$db)
    {
        $jid  = $env['jid'];
        $gid  = $env['gid'];
        $ins  = check_install($env['ins']);
        $pcfg = find_pcfg($gid,$jid,$db);
        $good = legal_mgrp($env,$gid,$db);
        if (($pcfg) && ($good))
        {
            $pid = $pcfg['pconfigid'];
            update_shed($env,$pcfg);
            $pcfg['installation'] = $ins;
            if (update_pcfg($env,$pid,$pcfg,$db))
            {
                $stat = "g:$gid,j:$jid,p:$pid,i:$ins";
                $text = "patch: update pcfg ($stat)";
                                debug_note($text);
                echo para('The change has been saved.');
            }
        }
        if (!$good)
        {
            echo mgrp_error();
        }
        list_pcfg($env,$db);
    }


    function show_cat_pgrp(&$env,$db)
    {
        echo again($env);
        $set = array( );
        $cat = $env['pcat'];
        if ($cat)
        {
            $ord  = value_range(22,33,$env['ord']);
            $wrd  = order($ord);
            $kid  = $env['pcat']['pcategoryid'];
            $name = $env['pcat']['category'];
            debug_note("patch category: kid:$kid, name:$name");

            $sql = "select G.*, count(M.pgroupmapid) as number\n"
                 . " from PatchGroups as G\n"
                 . " left join PatchGroupMap as M\n"
                 . " on M.pgroupid = G.pgroupid\n"
                 . " where G.pcategoryid = $kid\n"
                 . " group by G.pgroupid\n"
                 . " order by $wrd";

            $set = find_many($sql,$db);
        }

        $num = safe_count($set);

        if (($set) && ($cat))
        {
            echo <<< HERE

            <p>
              This is a list of the software update groups in a single
              category.  You can edit or delete groups that you own, and
              make copies of any group you can see.  For groups that are
              defined with a search or expression, clicking on "update"
              re-calculates the members of the group based on the search
              or expression.
            </p>

HERE;
            $self = $env['self'];
            $auth = $env['auth'];

            $o    = "$self?act=pgrp&kid=$kid&ord";
            $name = ($ord == 22)? "$o=23" : "$o=22";                $defs = ($ord == 24)? "$o=25" : "$o=24";                $bool = ($ord == 26)? "$o=27" : "$o=26";                $numb = ($ord == 28)? "$o=29" : "$o=28";                $glob = ($ord == 30)? "$o=31" : "$o=30";                $user = ($ord == 32)? "$o=33" : "$o=32";
            $a    = array('Action');
            $a[]  = html_link($name,'Name');
            $a[]  = html_link($defs,'Definition');
            $a[]  = html_link($bool,'Definition Data');
            $a[]  = html_link($numb,'Number of Updates');
            $a[]  = html_link($glob,'Global');
            $a[]  = html_link($user,'Owner');
            $cols = safe_count($a);
            $name = $env['pcat']['category'];

            echo table_header();
            echo pretty_header($name,$cols);
            echo table_data($a,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $a    = array();
                $jid  = $row['pgroupid'];
                $type = $row['style'];
                $name = disp($row,'name');
                $user = disp($row,'username');
                $bool = disp($row,'boolstring');
                $bool = nl2br($bool);
                $defs = group_type($row);
                $numb = $row['number'];
                $glob = ($row['global'])? 'Yes' : 'No';
                $act  = "$self?jid=$jid&kid=$kid&act";
                $edit = html_link("$act=epgf",'[edit]');
                $del  = html_link("$act=dpga",'[delete]');
                $upd  = html_link("$act=upga",'[update]');
                $cp   = html_link("$act=cpga",'[copy]');
                $view = html_link("$act=vpga",'[view]');
                if ($auth == $user)
                {
                    $a[] = $edit;
                    $a[] = $cp;
                    $a[] = $del;
                    if (dynamic_group($type))
                    {
                        $a[] = $upd;
                    }
                }
                else
                {
                    $a[] = $cp;
                    $a[] = $view;
                }

                $acts = join('<br>',$a);
                $args = array($acts,$name,$defs,$bool,$numb,$glob,$user);
                echo table_data($args,0);
            }
            echo table_footer();
            debug_note("There are $num groups for this category.");
        }
        else
        {
            echo <<< QQQQ

            <p>
                There aren't any update groups in
                this category yet.
                Click on <b>add</b> to create one.
            </p>\n\n
QQQQ;
        }
        echo again($env);
    }


    function pcat_explanation($env,$db)
    {

        echo <<< PCAT

        <p>
          This is a list of all the software update groups.
        </p>

        <p>
          A software update group is a collection of related software
          updates that you want to treat as a unit for configuration
          purposes.  Each group has a name and a category.  Clicking
          on "expand" or "collapse" will show and hide the names.  A
          single category usually holds a related set of
          non-overlapping groups.
        </p>

        <p>
          For example, you might have a category "Operating system",
          and within that category, group the software updates by the
          operating system where they apply, and use the operating
          system name as the name of the group.  Note that a single
          software update can be in more than one group, which is
          consistent in this example with the fact that a single
          software update may be used on more than one operating
          system.
        </p>

        <p>
          If a software update is in more than one group that applies
          to a certain operation, then the group with the higher
          priority, as shown in this table, is the one that is used.
          You can change the priority of a category by clicking on
          "move up" or "move down".
        </p>

        <p>
          There are three built-in software update group categories:
        </p>

        <ol>
          <li>
            "All" is a category with a single group in it, "All", that
            contains all of the software updates.
          </li>
          <li>
            "Update" is a category with a separate group in it for each
            software update.
          </li>
          <li>
            "Type" is a category with a group for each update type.
            The membership for these groups is automatically calculated.
          </li>
        </ol>

PCAT;

    }




    function manage_pcat(&$env,$db)
    {
        echo again($env);
        debug_note("<b>pcat</b> manage_pcat");
        pcat_explanation($env,$db);
        $ord = value_range(0,5,$env['ord']);
        $wrd = order($ord);
        $qu  = safe_addslashes($env['auth']);
        $sql = "select C.*, count(G.pcategoryid) as number\n"
             . " from PatchCategories as C\n"
             . " left join PatchGroups as G\n"
             . " on G.pcategoryid = C.pcategoryid\n"
             . " and (G.global = 1\n"
             . " or G.username = '$qu')\n"
             . " group by C.pcategoryid\n"
             . " order by $wrd";

        $set = find_many($sql,$db);
        $num = safe_count($set);
        $max = 0;

        if ($set)
        {
            $sql = 'select max(precedence) from PatchCategories';
            $max = intval(find_scalar($sql,$db));
        }

        if (($set) && ($max > 0))
        {
            $ord  = $env['ord'];
            $self = $env['self'];
            $admn = $env['admn'];
            $o    = "$self?act=pcat&ord";
            $pref = ($ord ==  0)? "$o=1"  : "$o=0";
            $cref = ($ord ==  2)? "$o=3"  : "$o=2";
            $nref = ($ord ==  4)? "$o=5"  : "$o=4";

            $acts = 'Action';
            $cats = html_link($cref,'Category');
            $prec = html_link($pref,'Priority');
            $nums = html_link($nref,'Number of Names');

            $head = array($acts,$cats,$prec,$nums);

            echo table_header();
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $kid  = $row['pcategoryid'];
                $cats = $row['category'];
                $prec = $row['precedence'];
                $grps = $row['number'];
                $a    = array();
                $act  = "$self?kid=$kid&act";
                $a[]  = html_link("$act=dpca",'[delete]');
                $a[]  = html_link("$act=pgrp",'[show names]');
                if (($admn) && ($ord == 0) && ($prec > 1))
                {
                    $a[]  = html_link("$act=pcup",'[move up]');
                }
                if (($admn) && ($ord == 0) && ($prec < $max))
                {
                    $a[]  = html_link("$act=pcdn",'[move down]');
                }
                $acts = join('<br>',$a);
                $args = array($acts,$cats,$prec,$grps);
                echo table_data($args,0);

            }
            echo table_footer();
            echo clear_all();

            debug_note("There are $num categories, max precedence is $max.");
        }
        else
        {
            echo para('None found.');
        }
        echo again($env);
    }



    function pcat_up(&$env,$db)
    {
        $act = $env['act'];
        debug_note("<b>$act</b> pcat_up");
        if (($env['pcat']) && ($env['admn']))
        {
            $tab  = 'PatchCategories';
            $kid  = $env['pcat']['pcategoryid'];
            $name = $env['pcat']['category'];
            $old  = $env['pcat']['precedence'];
            $new  = $old - 1;
            debug_note("pcat up id:$kid, name:$name, old:$old new:$new");
            if ($new > 0)
            {
                $uuu = "update $tab\n set precedence";
                $aaa = "$uuu = $old\n where precedence = $new";
                $bbb = "$uuu = $new\n where pcategoryid = $kid";
                redcommand($aaa,$db);
                redcommand($bbb,$db);
            }
        }
        if ($act == 'pcup') manage_pcat($env,$db);
        if ($act == 'peup') expand_pcat($env,$db);
    }




    function pcat_down(&$env,$db)
    {
        $act = $env['act'];
        debug_note("<b>$act</b> pcat_down");
        if (($env['pcat']) && ($env['admn']))
        {
            $tab  = 'PatchCategories';
            $kid  = $env['pcat']['pcategoryid'];
            $name = $env['pcat']['category'];
            $old  = $env['pcat']['precedence'];
            $new  = $old + 1;
            debug_note("pcat down id:$kid, name:$name, old:$old new:$new");
            $sql  = "select * from $tab where precedence = $new";
            $cat  = find_one($sql,$db);
            if ($cat)
            {
                $nid = $cat['pcategoryid'];
                $uuu = "update $tab\n set precedence";
                $aaa = "$uuu = $old\n where pcategoryid = $nid";
                $bbb = "$uuu = $new\n where pcategoryid = $kid";
                redcommand($aaa,$db);
                redcommand($bbb,$db);
            }
        }
        if ($act == 'pcdn') manage_pcat($env,$db);
        if ($act == 'pedn') expand_pcat($env,$db);
    }

    function expand_pcat(&$env,$db)
    {
        echo again($env);
        debug_note("<b>pexp</b> expand_pcat");
        pcat_explanation($env,$db);
        $ord = value_range(0,3,$env['ord']);
        $wrd = order($ord);
        $sql = "select * from PatchCategories\n"
             . " order by $wrd";
        $cat = find_many($sql,$db);
        $num = safe_count($cat);
        $max = 0;

        $qu  = safe_addslashes($env['auth']);
        $sql = "select * from PatchGroups\n"
             . " where global = 1\n"
             . " or username = '$qu'\n"
             . " order by pcategoryid, name";
        $grp = find_many($sql,$db);

        if ($cat)
        {
            $sql = 'select max(precedence) from PatchCategories';
            $max = intval(find_scalar($sql,$db));
        }

        if (($cat) && ($grp) && ($max > 1))
        {
            $self = $env['self'];
            $auth = $env['auth'];
            $admn = $env['admn'];
            $grps = array( );
            reset($cat);
            foreach ($cat as $key => $row)
            {
                $kid = $row['pcategoryid'];
                $grps[$kid] = array();
            }

            reset($grp);
            foreach ($grp as $key => $row)
            {
                $kid = $row['pcategoryid'];
                $jid = $row['pgroupid'];
                $grps[$kid][$jid] = $row;
            }

            $self = $env['self'];
            $o    = "$self?act=pexp&ord";
            $pref = ($ord == 0)? "$o=1" : "$o=0";
            $cref = ($ord == 2)? "$o=3" : "$o=2";

            $acts = 'Action';
            $cats = html_link($cref,'Category');
            $prec = html_link($pref,'Priority');
            $nums = 'Names';
            $head = array($acts,$cats,$prec,$nums);
            $cols = safe_count($head);
            $rows = safe_count($cat);
            $text = "Update Categories &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($cat);
            foreach ($cat as $key => $row)
            {
                $emgs = '';
                $kid  = $row['pcategoryid'];
                $cats = $row['category'];
                $prec = $row['precedence'];
                $mgrp = "$self?act=pgrp&kid=$kid";
                $link = html_link($mgrp,$cats);
                $set  = $grps[$kid];
                if ($set)
                {
                    reset($set);
                    foreach ($set as $jid => $grp)
                    {
                        $name = $grp['name'];
                        $user = $grp['username'];
                        $act  = "$self?kid=$kid&jid=$jid&act";
                        if ($user == $auth)
                            $acts = "$act=epgf";
                        else
                            $acts = "$act=vpga";
                        $emgs .= (html_link($acts,$name) . '<br>');
                    }
                }
                else
                {
                    $emgs = '<br>';
                }
                $a    = array();
                $act  = "$self?kid=$kid&act";
                $a[]  = html_link("$act=dpca",'[delete]');
                $a[]  = html_link("$act=pgrp",'[show names]');
                if (($admn) && ($ord == 0) && ($prec > 1))
                {
                    $a[]  = html_link("$act=peup",'[move up]');
                }
                if (($admn) && ($ord == 0) && ($prec < $max))
                {
                    $a[]  = html_link("$act=pedn",'[move down]');
                }
                $acts = join('<br>',$a);
                $args = array($acts,$link,$prec,$emgs);
                echo table_data($args,0);
            }
            echo table_footer();

        }

        echo again($env);
    }

    function add_pcat_form(&$env,$db)
    {
        echo <<< CATA

        <p>
          A software update group is a collection of related software
          updates that you want to treat as a unit for configuration
          purposes.
        </p>

        <p>
          These groups are organized in categories.  A single category
          usually holds a related set of non-overlapping groups.  For
          example, you might have a category "Operating system", and
          within that category, group the software updates by the
          operating system where they apply, and use the operating
          system name as the name of the group.  Note that a single
          software update can be in more than one group, which is
          consistent in this example with the fact that a single
          software update may be used on more than one operating
          system.
        </p>

        <p>
          This page lets you add a completely new category.  Usually,
          after adding a category, you will create new groups within
          that category and define which software updates belong in
          which of those groups.
        </p>

CATA;

        echo again($env);
        $txt = textbox('name',60,'');
        echo post_self('myform');
        echo hidden('act','catb');
        echo hidden('frm','cata');
        echo hidden('pcn','pcat');
        echo para("Group Category: $txt");
        echo okcancel_link(5, $env['custom']);
        echo form_footer();
        echo again($env);
    }


    function add_pcat_conf(&$env,$db)
    {
        echo again($env);
        $cat = $env['name'];
        if ($cat)
        {
            echo post_self('myform');
            echo hidden('act','catc');
            echo hidden('frm','catb');
            echo hidden('pno','pcat');
            echo hidden('name',$cat);
            echo para("Create update category <b>$cat</b>?");
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function add_pcat_act(&$env,$db)
    {
        $text = $env['name'];
        $num  = 0;
        if ($text)
        {
            $name = unique_pcat($text,0,$db);
            $sql = "select max(precedence) from PatchCategories";
            $max = intval(find_scalar($sql,$db));

            $new = $max + 1;
            $qn  = safe_addslashes($name);
            $sql = "insert into\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchCategories set\n"
                 . " category = '$qn',\n"
                 . " precedence = $new";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            check_change($text,$name);
            $txt = "Category <b>$name</b> has been created.";
        }
        else
        {
            $txt = 'No category created.';
        }
        echo para($txt);
        manage_pcat($env,$db);
    }


    function add_pgrp_form(&$env,$db)
    {
        echo again($env);
        if ($env['pcat'])
        {
            $kid  = $env['pcat']['pcategoryid'];
            $glob = checkbox('glob',1);
            $name = textbox('name',60,'');
            $type = constStyleManual;
            $tm   = radio('type',constStyleManual,$type);
            $te   = radio('type',constStyleExpr,$type);
            $ts   = radio('type',constStyleSearch,$type);
            $in   = indent(5);

            echo post_self('myform');
            echo hidden('act','grpb');
            echo hidden('frm','grpa');
            echo hidden('pcn','pgrp');
            echo hidden('kid',$kid);
            echo <<< HERE

            <br><br>
            Update group name: $name    <br>
            ${in}$glob Global
            <br><br>
            Select updates using:       <br>
            ${in}$tm Manual              <br>
            ${in}$te Expression          <br>
            ${in}$ts Search              <br>
            <br>
HERE;

            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        echo again($env);
    }




    function edit_pgrp_form(&$env,$db)
    {
        echo again($env);
        if (($env['pcat']) && ($env['pgrp']))
        {
            $kid  = $env['pcat']['pcategoryid'];
            $jid  = $env['pgrp']['pgroupid'];
            $cat  = $env['pcat']['category'];
            $grp  = $env['pgrp']['name'];
            $type = $env['pgrp']['style'];
            $text = $env['pgrp']['search'];
            $glob = $env['pgrp']['global'];

            debug_note("edit_pgrp_form cat:$cat grp:$grp glob:$glob type:$type");

            $in   = indent(5);
            $glob = checkbox('glob',$glob);
            $name = textbox('name',60,$grp);
            $tm   = radio('type',constStyleManual,$type);
            $te   = radio('type',constStyleExpr,$type);
            $ts   = radio('type',constStyleSearch,$type);

            echo post_self('myform');
            echo hidden('act','epgc');
            echo hidden('frm','epgf');
            echo hidden('pcn','pgrp');
            echo hidden('kid',$kid);
            echo hidden('jid',$jid);
            echo <<< HERE

            <br><br>
            Global: if checked, others can use and copy this group
            (but only you can change it)<br><br>

            Method used for selecting software updates that are members of the group:<br>
            ${in}Manual: select individually from a list<br>
            ${in}Search: select based on a text search of the name<br>
            ${in}Expression: select using combination of other groups<br>

            <br><br>
            Update group name: $name<br>
            ${in}$glob Global
            <br><br>
            Select updates using:<br>
            ${in}$tm Manual<br>
            ${in}$ts Search<br>
            ${in}$te Expression<br>
            <br>
HERE;

            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        echo again($env);
    }


    function edit_dispatch($type)
    {
        switch ($type)
        {
            case constStyleManual: return 'epgm';
            case constStyleExpr  : return 'epge';
            case constStyleSearch: return 'epgs';
            default              : return 'invd';
        }
    }

    function update_dispatch($type)
    {
        switch ($type)
        {
            case constStyleManual: return 'epgm';
            case constStyleExpr  : return 'upge';
            case constStyleSearch: return 'upgs';
            default              : return 'invd';
        }
    }


    function edit_pgrp_conf(&$env,$db)
    {
        echo again($env);
        if (($env['pcat']) && ($env['pgrp']))
        {
            $kid  = $env['pcat']['pcategoryid'];
            $jid  = $env['pgrp']['pgroupid'];
            $cat  = $env['pcat']['category'];
            $grp  = $env['pgrp']['name'];
            $type = $env['type'];
            $act  = edit_dispatch($type);

            debug_note("edit_pgrp_conf cat:$cat grp:$grp");

            echo post_self('myform');
            echo hidden('act',$act);
            echo hidden('frm','epgc');
            echo hidden('pno','pgrp');
            echo hidden('kid',$kid);
            echo hidden('jid',$jid);
            echo hidden('name',$env['name']);
            echo hidden('type',$env['type']);
            echo hidden('glob',$env['glob']);
            echo para("Update group <b>$grp</b> in category <b>$cat</b>?");
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }

    function calc_pgrp_conf(&$env,$db)
    {
        echo again($env);
        if (($env['pcat']) && ($env['pgrp']))
        {
            $kid  = $env['pcat']['pcategoryid'];
            $jid  = $env['pgrp']['pgroupid'];
            $cat  = $env['pcat']['category'];
            $grp  = $env['pgrp']['name'];
            $type = $env['pgrp']['style'];
            $act  = update_dispatch($type);

            debug_note("<b>upga</b> calc_pgrp_conf cat:$cat grp:$grp");

            echo post_self('myform');
            echo hidden('act',$act);
            echo hidden('frm','upga');
            echo hidden('pno','pgrp');
            echo hidden('kid',$kid);
            echo hidden('jid',$jid);
            echo para("Update group <b>$grp</b> in category <b>$cat</b>?");
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function calc_search($grp,$jid,$txt,$db)
    {
        $set = array( );

        if ($txt)
        {
            $qt  = safe_addslashes($txt);
            $sql = "select patchid from Patches\n"
                 . " where name like '%$qt%'";
            $set = find_many($sql,$db);
        }

        $num = kill_pmap_jid($jid,$db);
        if ($num)
        {
            echo para("Removed $num existing updates from group <b>$grp</b>.");
        }

        $num = 0;
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $mid = $row['patchid'];
                $nid = insert_pmap($jid,$mid,$db);
                if ($nid)
                {
                    $stat = "j:$jid,m:$mid,n:$nid";
                    $text = "patch: added patch ($stat) to $grp";
                                        debug_note($text);
                    $num++;
                }
            }
        }
        if ($num)
        {
            echo para("Added $num updates to group <b>$grp</b>.");
        }
    }


    function calc_pgrp_search(&$env,$db)
    {
        if (($env['pcat']) && ($env['pgrp']))
        {
            $jid  = $env['pgrp']['pgroupid'];
            $cat  = $env['pcat']['category'];
            $grp  = $env['pgrp']['name'];
            $txt  = $env['pgrp']['search'];
            $type = $env['pgrp']['style'];

            debug_note("<b>upgs</b> calc_pgrp_search cat:$cat grp:$grp");

            if ($type == constStyleSearch)
            {
                calc_search($grp,$jid,$txt,$db);
            }
        }
        show_cat_pgrp($env,$db);
    }



    function recalculate_expression($jid,$db)
    {
        $tree = find_expr_tree($jid,$db);
        if ($tree)
        {
            create_temp_table('block',$db);
            create_temp_table('final',$db);
            reset($tree);
            foreach ($tree as $blk => $d)
            {
                $sql = "insert into block\n"
                     . " select patchid as id\n"
                     . " from Patches";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                reset($d);
                foreach ($d as $eid => $row)
                {
                    $tag = $row['item'];
                    $neg = $row['negation'];
                    if ($neg)
                    {
                        $exp = "and not group $tag";
                        $sql = "select distinct b.id from\n"
                             . " block as b,\n"
                             . " PatchGroupMap as M\n"
                             . " where b.id = M.patchid\n"
                             . " and M.pgroupid = $tag"
                             . " order by id";
                    }
                    else
                    {
                        $exp = "and group $tag";
                        $sql = "select distinct b.id from\n"
                             . " block as b\n"
                             . " left join PatchGroupMap as M\n"
                             . " on M.pgroupid = $tag\n"
                             . " and M.patchid = b.id\n"
                             . " where M.pgroupmapid is NULL\n"
                             . " order by id";
                    }
                    $set = find_many($sql,$db);
                    if ($set)
                    {
                        $ids = distinct($set,'id');
                        $txt = join(',',$ids);
                        $sql = "delete from block\n"
                             . " where id in ($txt)";
                        $res = redcommand($sql,$db);
                        $num = affected($res,$db);
                        debug_note("block $blk term $eid: $exp ($num removed)");
                    }
                }
                $sql = "insert ignore into final\n"
                     . " select * from block";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                debug_note("block $blk: $num rows inserted");
                $sql = 'delete from block';
                redcommand($sql,$db);
            }



            kill_pmap_jid($jid,$db);
            $sql = "insert into PatchGroupMap\n"
                 . " select 0 as pgroupmapid,\n"
                 . " $jid as pgroupid,\n"
                 . " id as patchid\n"
                 . " from final";

            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            debug_note("there are now $num patches in this group");
            drop_temp_table('block',$db);
            drop_temp_table('final',$db);
        }
        else
        {
            kill_pmap_jid($jid,$db);
        }
    }


    function calc_pgrp_expr(&$env,$db)
    {
        $jid  = 0;
        $tree = array( );
        if (($env['pcat']) && ($env['pgrp']))
        {
            $kid  = $env['pcat']['pcategoryid'];
            $jid  = $env['pgrp']['pgroupid'];
            $cat  = $env['pcat']['category'];
            $grp  = $env['pgrp']['name'];
            $type = $env['pgrp']['style'];

            debug_note("<b>upge</b> calc_pgrp_expr cat:$cat grp:$grp");

            if ($type == constStyleExpr)
            {
                recalculate_expression($jid,$db);
            }
        }
        show_cat_pgrp($env,$db);
    }


    function edit_pgrp_act(&$env,$db)
    {
        $type = $env['type'];
        if (($env['pcat']) && ($env['pgrp']) && ($type))
        {
            $auth = $env['auth'];
            $kid  = $env['pcat']['pcategoryid'];
            $jid  = $env['pgrp']['pgroupid'];
            $cat  = $env['pcat']['category'];
            $grp  = $env['pgrp']['name'];
            $text = $env['name'];
            $type = $env['type'];
            $glob = $env['glob'];
            $name = unique_pgrp($text,$jid,$db);
            debug_note("edit_pgrp_act cat:$cat grp:$grp");

            $qa = safe_addslashes($auth);
            $qn = safe_addslashes($name);
            $sql = "update PatchGroups set\n"
                 . " name = '$qn',\n"
                 . " style = $type,\n"
                 . " global = $glob\n"
                 . " where pgroupid = $jid\n"
                 . " and pcategoryid = $kid\n"
                 . " and username = '$qa'";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num)
            {
                check_change($text,$name);
                $env['pgrp']['name'] = $name;
            }
        }
    }

    function edit_pgrp_search(&$env,$db)
    {
        echo again($env);
        edit_pgrp_act($env,$db);
        if (($env['pcat']) && ($env['pgrp']))
        {
            $auth = $env['auth'];
            $kid  = $env['pcat']['pcategoryid'];
            $jid  = $env['pgrp']['pgroupid'];
            $text = $env['pgrp']['search'];
            $tbox = textbox('text',50,$text);

            echo post_self('myform');
            echo hidden('act','epgx');
            echo hidden('frm','epgs');
            echo hidden('pcn','pgrp');
            echo hidden('kid',$kid);
            echo hidden('jid',$jid);
            echo para("Select updates whose name contains text: $tbox");
            echo okcancel_link(5, $env['custom']);
            echo form_footer();
        }
        echo again($env);
    }


    function find_patch_checks($jid,$db)
    {
        $set = array( );
        if ($jid > 0)
        {
            $sql = "select P.patchid as mid,\n"
                 . " N.pgroupmapid as nid,\n"
                 . " P.name as name\n"
                 . " from Patches as P\n"
                 . " left join PatchGroupMap as N\n"
                 . " on N.patchid = P.patchid\n"
                 . " and N.pgroupid = $jid\n"
                 . " order by name";
            $set = find_many($sql,$db);
        }
        return $set;
    }


    function edit_pgrp_man(&$env,$db)
    {
        echo again($env);
        edit_pgrp_act($env,$db);
        $set = array( );
        if (($env['pcat']) && ($env['pgrp']))
        {
            $kid = $env['pcat']['pcategoryid'];
            $cat = $env['pcat']['category'];
            $jid = $env['pgrp']['pgroupid'];
            $grp = $env['pgrp']['name'];
            $set = find_patch_checks($jid,$db);
            debug_note("edit_pgrp_man cat:$cat grp:$grp");
        }

        if ($set)
        {
            $post = $env['post'];
            $allc = ($post == constButtonAll)?  true : false;
            $none = ($post == constButtonNone)? true : false;

            $head = explode('|','Check|Update Name|Member');
            $cols = safe_count($head);



            echo post_self('myform');
            echo hidden('act','epgb');
            echo hidden('frm','epgm');
            echo hidden('pcn','pgrp');
            echo hidden('kid',$kid);
            echo hidden('jid',$jid);
            echo hidden('type',constStyleInvalid);

            echo okcancel_link(5, $env['custom']);
            echo checkallnone(5);

            echo table_header();
            echo pretty_header($grp,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $nid  = @ intval($row['nid']);
                $bool = ($nid)?  'Yes' : 'No';
                $chk  = ($nid)?   true : false;
                $chk  = ($allc)?  true : $chk;
                $chk  = ($none)? false : $chk;
                $box  = checkbox("mid_$mid",$chk);
                $args = array($box,$name,$bool);
                echo table_data($args,0);
            }

            echo table_footer();
            echo okcancel(5);
            echo form_footer();
        }
        else
        {
            echo para('No updates found.');
        }

        echo again($env);
    }


    function edit_pgrp_box(&$env,$db)
    {
        $set = array( );
        if (($env['pcat']) && ($env['pgrp']))
        {
            $kid = $env['pcat']['pcategoryid'];
            $cat = $env['pcat']['category'];
            $jid = $env['pgrp']['pgroupid'];
            $grp = $env['pgrp']['name'];
            $set = find_patch_checks($jid,$db);
            debug_note("edit_pgrp_box cat:$cat grp:$grp");
        }

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $nid  = @ intval($row['nid']);
                $here = ($nid)? 1 : 0;
                $form = UTIL_GetStoredInteger("mid_$mid",0);

                if (($here) && (!$form))
                {
                    if (delete_pmap($jid,$mid,$db))
                    {
                        echo "Update <b>$name</b> removed from <b>$grp</b>.<br>\n";
                    }
                }
                if ((!$here) && ($form))
                {
                    if (insert_pmap($jid,$mid,$db))
                    {
                        echo "Update <b>$name</b> added to <b>$grp</b>.<br>\n";
                    }
                }
            }
        }
        else
        {
            echo para('No updates found.');
        }

        show_cat_pgrp($env,$db);
    }

    function bold($text)
    {
        return "<b>$text</b>";
    }

    function table_span($cols,$txt)
    {
        $txt = bold($txt);
        $row = "<td colspan=\"$cols\" align=\"center\">$txt</td>";
        return "<tr>$row</tr>\n";
    }

    function update_expression(&$env,$db)
    {
        if ($env['pgrp'])
        {
            $b    = array();
            $auth = $env['auth'];
            $cat  = pcat_options($db);
            $opt  = pgrp_options($auth,$db);
            $ujid = $env['pgrp']['pgroupid'];
            $tree = find_expr_tree($ujid,$db);
            reset($tree);
            $cat[0] = '';
            $opt[0][0] = '';
            foreach ($tree as $blk => $d)
            {
                $a = array();
                reset($d);
                foreach ($d as $eid => $row)
                {
                    $xdel = UTIL_GetStoredInteger("xdel_$eid",0);
                    $xneg = UTIL_GetStoredInteger("xnot_$eid",0);
                    $xkid = UTIL_GetStoredInteger("xkid_$eid",0);
                    $xjid = UTIL_GetStoredInteger("xjid_$eid",0);

                    $xcat = @ trim($cat[$xkid]);
                    $xgrp = @ trim($opt[$xkid][$xjid]);

                    if (($xjid) && (!$xgrp)) $xjid = 0;
                    if (($xkid) && (!$xcat)) $xkid = 0;

                    debug_note("evaluate blk:$blk eid:$eid del:$xdel neg:$xneg kid:$xkid jid:$xjid");
                    if (($xdel) || ($xkid == 0))
                    {
                        debug_note("need to delete expr $eid");
                        delete_expr_eid($eid,$db);
                    }
                    else
                    {
                        $ekid = $row['pcatid'];
                        $ejid = $row['item'];
                        $eneg = $row['negation'];
                        if (($xjid != $ejid) || ($xneg != $eneg) || ($xkid != $ekid))
                        {
                            $sql = "update PatchExpression set\n"
                                 . " item = $xjid,\n"
                                 . " pcatid = $xkid,\n"
                                 . " negation = $xneg\n"
                                 . " where exprid = $eid";
                            $res = redcommand($sql,$db);
                            $num = affected($res,$db);
                            debug_note("update expr $eid, neg:$xneg, kid:$xkid, jid:$xjid, num:$num");
                        }
                        if (($xjid) && ($xkid) && ($xcat) && ($xgrp))
                        {
                            $not  = ($xneg)? 'not ' : '';
                            $name = "$xcat:$xgrp";
                            $txt  = $not . $name;
                            $a[]  = ($xneg)? "($txt)" : $txt;
                        }
                    }
                }
                $aneg = UTIL_GetStoredInteger("aneg_$blk",0);
                $ajid = UTIL_GetStoredInteger("ajid_$blk",0);
                $akid = UTIL_GetStoredInteger("akid_$blk",0);
                if ($akid)
                {
                    $eid = build_expr($aneg,$akid,$ajid,$blk,$ujid,$db);
                    if (($eid) && ($ajid))
                    {
                        $not  = ($aneg)? 'not ' : '';
                        $acat = $cat[$akid];
                        $agrp = $opt[$akid][$ajid];
                        $name = "$acat:$agrp";
                        $txt  = $not . $name;
                        $a[]  = ($aneg)? "($txt)" : $txt;
                        debug_note("added new ($eid) neg:$aneg cat:$akid ($acat) group:$ajid ($agrp) to blk:$blk");
                    }
                }
                if ($a)
                {
                    $txt = join(' and ',$a);
                    $b[] = "($txt)";
                }
            }
            $oblk = UTIL_GetStoredInteger('oblk',0);
            $ojid = UTIL_GetStoredInteger('ojid',0);
            $oneg = UTIL_GetStoredInteger('oneg',0);
            $okid = UTIL_GetStoredInteger('okid',0);
            if (($oblk) && ($okid))
            {
                $eid = build_expr($oneg,$okid,$ojid,$oblk,$ujid,$db);
                debug_note("new clause eid:$eid, blk:$oblk neg:$oneg kid:$okid jid:$ojid");
                if (($eid) && ($ojid))
                {
                    $not  = ($oneg)? 'not ' : '';
                    $ocat = $cat[$okid];
                    $ogrp = $opt[$okid][$ojid];
                    $name = "$ocat:$ogrp";
                    $txt  = $not . $name;
                    $b[]  = ($oneg)? "($txt)" : $txt;
                    debug_note("added new ($eid) neg:$oneg cat:$okid ($ocat) group:$ojid ($ogrp) to blk:$blk");
                }
            }
            if ($b)
            {
                $txt = join(" or \n",$b);
                $txt = "($txt)";
                $qb  = safe_addslashes($txt);
                $qu  = safe_addslashes($env['auth']);
                $jid = $env['pgrp']['pgroupid'];
                $sql = "update PatchGroups set\n"
                     . " boolstring = '$qb'\n"
                     . " where pgroupid = $jid\n"
                     . " and username = '$qu'";
                $res = redcommand($sql,$db);
                $env['pgrp']['boolstring'] = $txt;
            }
        }
    }



    function edit_pgrp_expr(&$env,$db)
    {
        echo again($env);
        $jid = $env['jid'];
        debug_note("<b>epge</b> edit_pgrp_expr: $jid");
        if ($env['pgrp'])
        {
            $in   = indent(5);
            $auth = $env['auth'];
            $bool = $env['pgrp']['boolstring'];
            $name = $env['pgrp']['name'];
            $jid  = $env['pgrp']['pgroupid'];
            $kid  = $env['pgrp']['pcategoryid'];

            $cat  = pcat_options($db);
            $opt  = pgrp_options($auth,$db);
            $tree = find_expr_tree($jid,$db);
            $not  = array('  ','not');

            $ok     = button(constButtonOk);
            $done   = button(constButtonDone);
            $cancel = button(constButtonCan);

            if ($bool)
            {
                $txt = str_replace("\n","<br>\n&nbsp;&nbsp;&nbsp;",$bool);
                echo "Current expression:<br><p><b>$txt</b></p>\n";
            }

            echo post_self('myform');
            echo hidden('act','epgp');
            echo hidden('frm','epge');
            echo hidden('pcn','pgrp');
            echo hidden('jid',$jid);
            echo hidden('kid',$kid);

            $head = explode('|','delete|negate|category|group');
            $cols = safe_count($head);
            $blok = 0;

            echo table_header();
            echo pretty_header($name,$cols);
            echo table_data($head,1);
            reset($tree);
            $none = array(indent(20));
            foreach ($tree as $blk => $d)
            {
                $blok = $blk;
                reset($d);
                foreach ($d as $eid => $row)
                {
                    $jid  = $row['item'];
                    $kid  = $row['pcatid'];
                    $neg  = $row['negation'];
                    $dval = "xdel_$eid";
                    $nval = "xnot_$eid";
                    $gval = "xjid_$eid";
                    $cval = "xkid_$eid";
                    $gopt = isset($opt[$kid])? $opt[$kid] : $none;
                    $box  = checkbox($dval,0);
                    $negs = html_select($nval,$not,$neg,1);
                    $cats = js_select($cval,$cat,$kid,1);
                    $name = html_select($gval,$gopt,$jid,1);
                    $args = array($box,$negs,$cats,$name);
                    echo table_data($args,0);
                }
                $box  = '<br>';
                $nval = "aneg_$blk";
                $gval = "ajid_$blk";
                $cval = "akid_$blk";
                $negs = html_select($nval,$not,0,1);
                $name = html_select($gval,$none,0,1);
                $cats = js_select($cval,$cat,0,1);
                $args = array($box,$negs,$cats,$name);
                echo table_data($args,0);
                echo table_span($cols,'OR');
            }

            $blk = $blok + 1;
            $negs = html_select('oneg',$not,0,1);
            $name = html_select('ojid',$none,0,1);
            $cats = js_select('okid',$cat,0,1);
            $args = array('<br>',$negs,$cats,$name);
            echo table_data($args,0);
            echo table_footer();

            echo hidden('oblk',$blk);
            echo "<p>$ok ${in}$done ${in}$cancel</p>";
            echo form_footer();
        }
        echo again($env);
    }

    function edit_pgrp_post(&$env,$db)
    {
        update_expression($env,$db);
        edit_pgrp_expr($env,$db);
    }


    function edit_pgrp_done(&$env,$db)
    {
        if ($env['pgrp'])
        {
            $jid = $env['pgrp']['pgroupid'];
            $grp = $env['pgrp']['name'];
            debug_note("<b>epgd</b> edit_pgrp_done $grp ($jid)");
            update_expression($env,$db);
            recalculate_expression($jid,$db);
        }
        show_cat_pgrp($env,$db);
    }


    function edit_pgrp_exec(&$env,$db)
    {
        if (($env['pcat']) && ($env['pgrp']))
        {
            $auth = $env['auth'];
            $txt  = $env['text'];
            $kid  = $env['pcat']['pcategoryid'];
            $cat  = $env['pcat']['category'];
            $jid  = $env['pgrp']['pgroupid'];
            $grp  = $env['pgrp']['name'];

            debug_note("edit_pgrp_exec cat:$cat grp:$grp");

            $qa = safe_addslashes($auth);
            $qt = safe_addslashes($txt);
            $sql = "update PatchGroups set\n"
                 . " search = '$qt'\n"
                 . " where pgroupid = $jid\n"
                 . " and pcategoryid = $kid\n"
                 . " and username = '$qa'";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);

            if ($num)
            {
                echo para("Changed search string for update group <b>$grp</b>.");
            }

            calc_search($grp,$jid,$txt,$db);
        }
        show_cat_pgrp($env,$db);
    }

    function view_pgrp(&$env,$db)
    {
        echo again($env);
        $set  = array( );
        $jid  = $env['jid'];
        $pgrp = find_pgrp_jid($jid,$db);
        if ($pgrp)
        {
            $grp = $pgrp['name'];
            debug_note("<b>vpga</b> view_pgrp jid:$jid $grp");
            $sql = "select P.patchid as mid,\n"
                 . " N.pgroupmapid as nid,\n"
                 . " P.name as name\n"
                 . " from Patches as P,\n"
                 . " PatchGroupMap as N\n"
                 . " where N.patchid = P.patchid\n"
                 . " and N.pgroupid = $jid\n"
                 . " order by name";
            $set = find_many($sql,$db);
        }

        if (($set) && ($pgrp))
        {
            $name = $pgrp['name'];
            $head = explode('|','Action|Name');
            $rows = safe_count($set);
            $cols = safe_count($head);
            $text = "$name ($rows members)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            $act = 'wu-patch.php?act=dets&mid';

            reset($set);
            foreach ($set as $key => $row)
            {
                $name = $row['name'];
                $mid  = $row['mid'];
                $view = html_link("$act=$mid",'[detail]');
                $args = array($view,$name);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            echo para('No members found.');
        }
        echo again($env);
    }

    function add_pgrp_conf(&$env,$db)
    {
        echo again($env);
        $grp = $env['name'];
        if (($env['pcat']) && ($grp))
        {
            $kid = $env['pcat']['pcategoryid'];
            $cat = $env['pcat']['category'];
            $txt = "Create new update group <b>$grp</b> in category <b>$cat</b>?";

            echo post_self('myform');
            echo hidden('act','grpc');
            echo hidden('frm','grpb');
            echo hidden('pno','pgrp');
            echo hidden('kid',$kid);
            echo hidden('glob',$env['glob']);
            echo hidden('name',$env['name']);
            echo hidden('type',$env['type']);
            echo para($txt);
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function add_pgrp_act(&$env,$db)
    {
        $text = $env['name'];
        if (($env['pcat']) && ($text))
        {
            $min  = constStyleManual;
            $max  = constStyleSearch;
            $type = value_range($min,$max,$env['type']);
            $glob = value_range(0,1,$env['glob']);
            $auth = $env['auth'];
            $kid  = $env['pcat']['pcategoryid'];
            $cat  = $env['pcat']['category'];
            $name = unique_pgrp($text,0,$db);
            $jid  = insert_pgrp($name,$type,$glob,1,$kid,$auth,$db);
            if ($jid)
            {
                check_change($text,$name);
                echo para("Created group <b>$name</b> in category <b>$cat</b>.");
                $env['name'] = $name;
            }
        }
        $pgrp = array( );
        if ($jid)
        {
            $pgrp = find_pgrp_jid($jid,$db);
        }
        if ($pgrp)
        {
            $env['pgrp'] = $pgrp;
            $env['jid']  = $jid;
            switch ($pgrp['style'])
            {
                case constStyleManual: edit_pgrp_man($env,$db);    break;
                case constStyleExpr:   edit_pgrp_expr($env,$db);   break;
                case constStyleSearch: edit_pgrp_search($env,$db); break;
            }
        }
        else
        {
            show_cat_pgrp($env,$db);
        }
    }

    function del_pgrp_conf(&$env,$db)
    {
        echo again($env);
        debug_note("<b>dpga</b> del_pgrp_conf");
        if (($env['pcat']) && ($env['pgrp']))
        {
            $auth = $env['auth'];
            $user = $env['pgrp']['username'];
            $jid  = $env['pgrp']['pgroupid'];
            $grp  = $env['pgrp']['name'];
            $kid  = $env['pcat']['pcategoryid'];
            $cat  = $env['pcat']['category'];

            if ($auth == $user)
            {
                echo post_self('myform');
                echo hidden('act','dpgb');
                echo hidden('frm','dpga');
                echo hidden('pno','pgrp');
                echo hidden('kid',$kid);
                echo hidden('jid',$jid);
                echo para("Delete update group <b>$grp</b> in category <b>$cat</b>?");
                echo askyesno(5);
                echo form_footer();
            }
            else
            {
                echo para('You do not own this group and cannot remove it.');
            }
        }
        echo again($env);
    }


    function del_pgrp_act(&$env,$db)
    {
        debug_note('<b>dpgb</b> del_pgrp_act');
        if (($env['pcat']) && ($env['pgrp']))
        {
            $auth = $env['auth'];
            $user = $env['pgrp']['username'];
            $jid  = $env['pgrp']['pgroupid'];
            $grp  = $env['pgrp']['name'];
            $kid  = $env['pcat']['pcategoryid'];
            $cat  = $env['pcat']['category'];
            $num  = 0;

            if ($auth == $user)
            {
                $num = kill_pgrp_jid($jid,$db);
            }
            else
            {
                echo para('You do not own this group and cannot remove it.');
            }
            if ($num)
            {
                $stat = "j:$jid,k:$kid";
                $text = "patch: pgrp '$cat' / '$grp' removed by $auth ($stat)";
                                debug_note($text);
            }
        }
        manage_pcat($env,$db);
    }



    function del_pcat_conf(&$env,$db)
    {
        echo again($env);
        $kid = $env['kid'];
        debug_note("<b>dpca</b> del_pcat_conf ($kid)");
        if ($env['pcat'])
        {
            $kid = $env['pcat']['pcategoryid'];
            $cat = $env['pcat']['category'];

            echo post_self('myform');
            echo hidden('act','dpcb');
            echo hidden('frm','dpca');
            echo hidden('pno','pcat');
            echo hidden('kid',$kid);
            echo para("Remove update category <b>$cat</b>?");
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function del_pcat_act(&$env,$db)
    {
        echo again($env);
        $kid = $env['kid'];
        debug_note("<b>dpcb</b> del_pcat_act ($kid)");
        if ($env['pcat'])
        {
            $admn = $env['admn'];
            $auth = $env['auth'];
            $kid  = $env['pcat']['pcategoryid'];
            $pre  = $env['pcat']['precedence'];
            $cat  = $env['pcat']['category'];
            $set  = find_pgrp_kid($kid,$db);
            $num  = 0;

            if ($set)
            {
                foreach ($set as $key => $row)
                {
                    $usr = $row['username'];
                    $grp = $row['name'];
                    $jid = $row['pgroupid'];
                    if (($usr == $auth) || ($admn))
                    {
                        if (kill_pgrp_jid($jid,$db))
                        {
                            $stat = "j:$jid,k:$kid";
                            $text = "patch: pgrp '$cat' / '$grp' removed by $auth ($stat)";
                                                        debug_note($text);
                            para("Removed group <b>$grp</b> from category <b>$cat</b>.");
                            $num++;
                        }
                    }
                    else
                    {
                        echo para("Group <b>$grp</b> not removed from category <b>$cat</b>.");
                    }
                }
            }

            if ($num)
            {
                $set = find_pgrp_kid($kid,$db);
            }


            if ($set)
            {
               echo para("Update category <b>$cat</b> cannot be removed.");
            }

            if (!$set)
            {
                $sql = "delete from PatchCategories\n"
                     . " where pcategoryid = $kid";
                $res = redcommand($sql,$db);
                if (affected($res,$db))
                {
                    $text = "patch: pcat $cat removed by $auth (k:$kid)";
                                        debug_note($text);
                    $sql = "update PatchCategories set\n"
                         . " precedence = precedence-1\n"
                         . " where precedence > $pre";
                    $res = redcommand($sql,$db);
                    echo para("Update category <b>$cat</b> has been removed.");
                }
            }
        }
        echo again($env);
    }



    function copy_pgrp_form(&$env,$db)
    {
        $jid  = $env['jid'];
        $kid  = $env['kid'];
        $pgrp = find_pgrp_jid($jid,$db);
        debug_note("<b>cpga</b> copy_pgrp jid:$jid kid:$kid");
        echo again($env);
        if ($pgrp)
        {
            $name = $pgrp['name'];
            $text = textbox('name',60,'');

            echo post_self('myform');
            echo hidden('act','cpgb');
            echo hidden('frm','cpba');
            echo hidden('pcn','pgrp');
            echo hidden('jid',$jid);
            echo hidden('kid',$kid);
            echo para("Copy Update Group $name");
            echo para("Enter name: $text");
            echo okcancel(5);
            echo form_footer();
        }
        echo again($env);
    }


    function copy_pgrp_act(&$env,$db)
    {
        $jid  = $env['jid'];
        $text = $env['name'];
        $auth = $env['auth'];
        $pgrp = find_pgrp_jid($jid,$db);
        debug_note("<b>cpgb</b> copy_pgrp_act jid:$jid");
        if (($pgrp) && ($text))
        {
            $kid  = $pgrp['pcategoryid'];
            $type = constStyleManual;
            $glob = 1;
            $name = unique_pgrp($text,0,$db);
            $djid = insert_pgrp($name,$type,$glob,1,$kid,$auth,$db);
            if (($jid) && ($djid))
            {
                check_change($text,$name);
                $temp = 'patch_copy_temp';
                create_temp_table($temp,$db);
                $sql = "insert into $temp\n"
                     . " select distinct patchid as id from\n"
                     . " ".$GLOBALS['PREFIX']."softinst.PatchGroupMap\n"
                     . " where pgroupid = $jid";
                $res = redcommand($sql,$db);
                $tmp = affected($res,$db);
                $sql = "insert into\n"
                     . " PatchGroupMap\n"
                     . " select 0 as pgroupmapid,\n"
                     . " $djid as pgroupid,\n"
                     . " id as patchid\n"
                     . " from $temp";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                drop_temp_table($temp,$db);
                if ($num == $tmp)
                {
                    echo para("Created Update Group $name ($num)");
                }
                else
                {
                    debug_note("duplicate pgrp failed, created $num of $tmp.");
                }
            }
        }
        show_cat_pgrp($env,$db);
    }


    function copy_pcfg(&$env,$db)
    {
        $gid  = $env['gid'];
        $jid  = $env['jid'];
        $auth = $env['auth'];
        $pgrp = ($env['pgrp'])? $env['pgrp'] : find_pgrp_jid($jid,$db);
        $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        $tid  = ($mgrp)? $mgrp['mcatid'] : 0;
        $kid  = ($pgrp)? $pgrp['pcategoryid'] : 0;
        $dtid = $env['dtid'];
        $dgid = $env['dgid'];
        $dkid = $env['dkid'];
        $djid = $env['djid'];
        $auth = $env['auth'];
        $pcfg = find_pcfg($gid,$jid,$db);
        $mcat = find_mcat_tid($tid,$db);
        $pcat = find_pcat_kid($kid,$db);
        $good = false;
        debug_note("<b>cppa</b> copy_pcfg (g:$gid,j:$jid)");
        echo again($env);
        debug_note("Source: (g:$gid,t:$tid,j:$jid,k:$kid)");
        debug_note("  Dest: (g:$dgid,t:$dtid,j:$djid,k:$dkid)");

        if (($pgrp) && ($mgrp) && ($pcfg) && ($mcat) && ($pcat))
        {
            $mcop = mcat_options($db);
            $pcop = pcat_options($db);
            $mgop = mgrp_options($auth,$db);
            $pgop = pgrp_options($auth,$db);

            if (($mcop) && ($pcop) && ($mgop) && ($pgop))
            {
                $good = true;
            }
        }

        if ($good)
        {
            $mopt = (isset($mgop[$dtid]))? $mgop[$dtid] : $mgop[0];
            $popt = (isset($pgop[$dkid]))? $pgop[$dkid] : $pgop[0];
            $dtid = js_select('dtid',$mcop,$dtid,1);
            $dkid = js_select('dkid',$pcop,$dkid,1);
            $dgid = html_select('dgid',$mopt,$dgid,1);
            $djid = html_select('djid',$popt,$dgid,1);
            $mcnm = $mcat['category'];
            $pcnm = $pcat['category'];
            $mgnm = $mgrp['name'];
            $pgnm = $pgrp['name'];
            $in   = indent(5);
            $xn   = indent(10);

            echo post_self('myform');
            echo hidden('act','cppb');
            echo hidden('frm','cppa');
            echo hidden('pcn','pgrp');
            echo hidden('gid',$gid);
            echo hidden('jid',$jid);
            echo hidden('tid',$tid);
            echo hidden('kid',$kid);
            echo <<< HERE

            <br><br>
            Copy update configuration from machine group:<br>
            ${xn} category: <b>$mcnm</b><br>
            ${xn} name: <b>$mgnm</b><br>
            ${in}and update group<br>
            ${xn} category: <b>$pcnm</b><br>
            ${xn} name: <b>$pgnm</b><br><br>

            To machine group:<br>
            ${xn} category: $dtid<br>
            ${xn} name: $dgid<br>
            ${in}and update group<br>
            ${xn} category: $dkid<br>
            ${xn} name: $djid<br>

HERE;
            echo okcancel(5);
            echo form_footer();
        }
        else
        {
            echo para('No such configuration.');
        }
        echo again($env);
    }


    function copy_wcfg(&$env,$db)
    {
        $gid  = $env['gid'];
        $dtid = $env['dtid'];
        $dgid = $env['dgid'];
        $auth = $env['auth'];
        $mgrp = find_pub_mgrp_gid($gid,$auth,$db);
        $wcfg = find_wcfg_gid($gid,$db);
        $tid  = ($mgrp)? $mgrp['mcatid'] : 0;
        $mcat = find_mcat_tid($tid,$db);
        debug_note("<b>cpwa</b> copy_wcfg gid:$gid dgid:$dgid dtid:$dtid");
        echo again($env);
        if (($mgrp) && ($mcat) && ($wcfg))
        {
            $cat = $mcat['category'];
            $grp = $mgrp['name'];
            $in  = indent(5);

            $dcat = mcat_options($db);
            $dgrp = mgrp_options($auth,$db);
            $dopt = (isset($dgrp[$dtid]))? $dgrp[$dtid] : $dgrp[0];
            $dtid = js_select('dtid',$dcat,$dtid,1);
            $dgid = html_select('dgid',$dopt,$dgid,1);

            echo post_self('myform');
            echo hidden('act','cpwb');
            echo hidden('frm','cpwa');
            echo hidden('pcn','lwid');
            echo hidden('gid',$gid);
            echo hidden('tid',$tid);
            echo <<< HERE

            <br><br>
            Copy machine configuration from machine group:<br>
            ${in}category: <b>$cat</b><br>
            ${in}name: <b>$grp</b><br>
            To machine group:<br>
            ${in}category: $dtid<br>
            ${in}name: $dgid<br>

HERE;
            echo okcancel(5);
            echo form_footer();

        }
        else
        {
            echo para('No such machine configuration.');
        }

        echo again($env);
   }


    function copy_wcfg_cnf(&$env,$db)
    {
        echo again($env);
        $gid  = $env['gid'];
        $tid  = $env['tid'];
        $dgid = $env['dgid'];
        $dtid = $env['dtid'];
        $auth = $env['auth'];
        $sgrp = find_pub_mgrp_gid($gid,$auth,$db);
        $scat = find_mcat_tid($tid,$db);
        $scfg = find_wcfg_gid($gid,$db);
        $dgrp = find_pub_mgrp_own($env,$dgid,$db);
        $dcat = find_mcat_tid($dtid,$db);
        $dcfg = find_wcfg_gid($dgid,$db);

        debug_note("<b>cpwb</b> copy_wcfg_cnf gid:$gid --> dgid:$dgid");

        if (($sgrp) && ($scat) && ($dgrp) && ($dcat) && ($scfg))
        {
            $sgnm = $sgrp['name'];
            $dgnm = $dgrp['name'];
            $scnm = $scat['category'];
            $dcnm = $dcat['category'];
            $in   = indent(5);

            echo post_self('myform');
            echo hidden('act','cpwc');
            echo hidden('frm','cpwb');
            echo hidden('pno','lwid');
            echo hidden('gid',$gid);
            echo hidden('dgid',$dgid);
            if ($dcfg)
            {
                $self = $env['self'];

                $lwid = "$self?act=lwid";
                $fwid = "$self?act=fwid&gid=$dgid";
                $yes  = html_link($fwid,'Yes');
                $no   = html_link($lwid,'No');
                echo  "<p>Note that the machine group <b>$dgnm</b> already<br>"
                  .   "has a configuration. &nbsp; Would you like to edit the<br>"
                  .   "existing configuration instead?</p>"
                  .   "<p>$yes ${in}$no</p>";
            }

            echo <<< HERE

            <p>Please confirm you want to
            copy a machine configuration.</p>

            From machine group:<br>
            ${in}category: <b>$scnm</b><br>
            ${in}name: <b>$sgnm</b><br>
            To machine group:<br>
            ${in}category: <b>$dcnm</b><br>
            ${in}name: <b>$dgnm</b><br>

HERE;
            echo askyesno(5);
            echo form_footer();
        }
        else
        {
            echo para('No such machine configuration.');
        }
        echo again($env);
    }




    function update_patchsource(&$env,$gid,$wid,$db)
    {
        debug_note("patch source has changed");
        $sql = "select M.id, M.wuconfigid\n"
             . " from ".$GLOBALS['PREFIX']."softinst.Machine as M,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as B,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as X\n"
             . " where G.mgroupuniq = B.mgroupuniq\n"
             . " and B.mgroupid = $gid\n"
             . " and G.censusuniq = X.censusuniq\n"
             . " and X.id = M.id";
        $set = find_many($sql,$db);
        update_wcfg_set($set,$db);

        $sql = "select id from Machine\n"
             . " where wuconfigid = $wid";
        $set = find_many($sql,$db);
        if ($set)
        {
            $dtec = constPatchStatusDetected;
            $dnld = constPatchStatusDownloaded;
            $tmp  = distinct($set,'id');
            $txt  = join(',',$tmp);
            $sql  = "delete from PatchStatus\n"
                  . " where id in ($txt)\n"
                  . " and status in ($dtec,$dnld)";
            $res  = redcommand($sql,$db);
            $num  = affected($res,$db);
            debug_note("$num patch status records removed");
        }
        else
        {
            debug_note("no machines changed");
        }
    }


    function copy_wcfg_act(&$env,$db)
    {
        $scfg = array( );
        $dcfg = array( );
        $sgid = $env['gid'];
        $dgid = $env['dgid'];
        $auth = $env['auth'];
        $sgrp = find_pub_mgrp_gid($sgid,$auth,$db);
        $dgrp = find_pub_mgrp_own($env,$dgid,$db);
        debug_note("<b>cpwc</b> copy_wcfg_act sgid:$sgid --> dgid:$dgid");

        if (($sgrp) && ($dgrp) && ($sgid != $dgid))
        {
            $scfg = find_wcfg_gid($sgid,$db);
            $dcfg = find_wcfg_gid($dgid,$db);
        }

        if ($scfg)
        {
            $snam = $sgrp['name'];
            $dnam = $dgrp['name'];
            debug_note("copy wcfg from $snam to $dnam");
            if ($dcfg)
            {
                $wid = $dcfg['id'];
                if (update_wcfg($env,$wid,$scfg,$db))
                {
                    $ssrc = $scfg['patchsource'];
                    $dsrc = $dcfg['patchsource'];
                    $surl = $scfg['serverurl'];
                    $durl = $dcfg['serverurl'];
                    $cmp  = ($durl == $surl)? 0 : 1;
                    $sus  = ($ssrc == constConfigPatchSourceSUSServer)? 1 : 0;
                    if (($ssrc != $dsrc) || (($sus) && ($cmp)))
                    {
                        update_patchsource($env,$dgid,$wid,$db);
                    }
                           debug_note("update wcfg $dnam (w:$wid,g:$dgid)");
                }
            }
            else
            {
                $wid = insert_wcfg($dgid,$scfg,$db);
                if ($wid)
                {
                    debug_note("create wcfg $dnam (w:$wid,g:$dgid)");
                }
                else
                {
                    debug_note("create wcfg failed.");
                }
            }
        }
        else
        {
            echo para('The specified machine configuration was not found.');
        }
        list_wcfg($env,$db);
    }


    function copy_pcfg_cnf(&$env,$db)
    {
        $sgid = $env['gid'];
        $sjid = $env['jid'];
        $dgid = $env['dgid'];
        $djid = $env['djid'];

        $good = false;
        $smgp = find_mgrp_gid($sgid, constReturnGroupTypeOne, $db);
        $dmgp = find_mgrp_gid($dgid, constReturnGroupTypeOne, $db);
        $spgp = ($env['pgrp'])? $env['pgrp'] : find_pgrp_jid($sjid,$db);
        $dpgp = find_pgrp_jid($djid,$db);

        $stid = ($smgp)? $smgp['mcatid'] : 0;
        $dtid = ($dmgp)? $dmgp['mcatid'] : 0;
        $skid = ($spgp)? $spgp['pcategoryid'] : 0;
        $dkid = ($dpgp)? $dpgp['pcategoryid'] : 0;

        $smct = find_mcat_tid($stid,$db);
        $dmct = find_mcat_tid($dtid,$db);
        $spct = find_pcat_kid($skid,$db);
        $dpct = find_pcat_kid($dkid,$db);
        $scfg = find_pcfg($sgid,$sjid,$db);
        $dcfg = find_pcfg($dgid,$djid,$db);
        $stat = "(g:$sgid,j:$sjid) --> (g:$dgid,j:$djid)";
        debug_note("<b>cppb</b> copy_pcfg_cnf $stat");

        echo again($env);

        if (($smgp) && ($spgp) && ($dmgp) && ($dpgp))
        {
            if (($smct) && ($dmct) && ($spct) && ($dpct))
            {
                if ($scfg)
                {
                    $good = true;
                }
            }
        }
        if ($good)
        {
            $smgn = $smgp['name'];              $dmgn = $dmgp['name'];              $spgn = $spgp['name'];              $dpgn = $dpgp['name'];
            $smcn = $smct['category'];              $dmcn = $dmct['category'];              $spcn = $spct['category'];              $dpcn = $dpct['category'];              $in   = indent(5);
            $xn   = indent(10);

            echo post_self('myform');
            echo hidden('act','cppc');
            echo hidden('frm','cppb');
            echo hidden('pno','lpid');
            echo hidden('gid',$sgid);
            echo hidden('jid',$sjid);
            echo hidden('dgid',$dgid);
            echo hidden('djid',$djid);

            if ($dcfg)
            {
                $self = $env['self'];

                $lpid = "$self?act=lpid";
                $fpid = "$self?act=fpid&gid=$dgid&jid=$djid";
                $yes  = html_link($fpid,'Yes');
                $no   = html_link($lpid,'No');
                echo  "<p>Note that there is already a configuration defined<br>"
                  .   "for <b>$dmgn</b> / <b>$dpgn</b>. &nbsp; Would you like to<br>"
                  .   "edit the existing configuration instead?</p>"
                  .   "<p>$yes${in}$no</p>";
            }

            echo <<< HERE

            <br><br>
            Confirm copy update configuration:<br><br>

            From machine group:<br>
            ${xn}category: <b>$smcn</b><br>
            ${xn}group: <b>$smgn</b><br>
            ${in}and update group<br>
            ${xn}category: <b>$spcn</b><br>
            ${xn}group: <b>$spgn</b><br><br>

            To machine group:<br>
            ${xn}category: <b>$dmcn</b><br>
            ${xn}group: <b>$dmgn</b><br>
            ${in}and update group<br>
            ${xn}category: <b>$dpcn</b><br>
            ${xn}group: <b>$dpgn</b><br>

HERE;
            echo askyesno(5);
            echo form_footer();
        }
        echo again($env);
    }


    function copy_pcfg_act(&$env,$db)
    {
        $gid  = $env['gid'];
        $jid  = $env['jid'];
        $dgid = $env['dgid'];
        $djid = $env['djid'];
        $psrc = ($env['pgrp'])? $env['pgrp'] : find_pgrp_jid($jid,$db);
        $msrc = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        $pdst = find_pgrp_jid($djid,$db);
        $mdst = find_mgrp_own($env,$dgid,$db);
        $scfg = find_pcfg($gid,$jid,$db);
        $dcfg = find_pcfg($dgid,$djid,$db);
        $stat = "(g:$gid,j:$jid) --> (g:$dgid,j:$djid)";
        debug_note("<b>cppc</b> copy_pcfg_act $stat");

        if (($msrc) && ($psrc) && ($mdst) && ($pdst) && ($scfg))
        {
            $msnm = $msrc['name'];
            $psnm = $psrc['name'];
            $mdnm = $mdst['name'];
            $pdnm = $pdst['name'];

            if ($dcfg)
            {
                debug_note("update existing pcfg record");
                $dpid = $dcfg['pconfigid'];
                $last = $dcfg['lastupdate'];
                $dcfg = $scfg;
                $dcfg['lastupdate'] = $last;
                $dcfg['mgroupid']   = $dgid;
                $dcfg['pgroupid']   = $djid;
                if (update_pcfg($env,$dpid,$dcfg,$db))
                {
                    echo para("Copied $msnm / $psnm to $mdnm / $pdnm");
                }
            }
            else
            {
                debug_note("create new pcfg record");
                $dcfg = $scfg;
                $dpid = insert_pcfg($dgid,$djid,$dcfg,$djid,$db);
                if ($dpid)
                {
                          echo para("Created configuration for <b>$mdnm</b> / <b>$pdnm</b>.");
                    debug_note("create pcfg $mdnm / $pdnm ($dgid)");
                }
                else
                {
                    debug_note("create pcfg failed.");
                }
            }
        }
        list_pcfg($env,$db);
    }




    function update_wconfig(&$env,$db)
    {
        $chng = 0;
        $old  = array( );
        $wid  = $env['wid'];
        $gid  = $env['gid'];
        $url  = $env['url'];
        $prop = value_range(0, 2,$env['prop']);
        $new  = value_range(1, 2,$env['new']);
        $updc = value_range(1, 2,$env['updc']);
        $rest = value_range(1, 2,$env['rest']);
        $src  = value_range(1, 3,$env['src']);
        $chan = value_range(1, 3,$env['chan']);
        $man  = value_range(1, 5,$env['man']);
        $iday = value_range(1, 8,$env['iday']);
        $chas = value_range(1, 8,$env['chas']);
        $hour = value_range(0,23,$env['hour']);
        $cday = value_range(1,31,$env['cday']);
        $ocfg = find_wcfg_gid($gid,$db);
        $mgrp = find_mgrp_own($env,$gid,$db);
        $scha = $chas * 3600;

        if (($mgrp) && ($ocfg) && ($wid > 0))
        {
            $sec = $cday * 86400;
            $qu  = safe_addslashes($url);
            $sql = "update WUConfig set\n"
                 . " management = $man,\n"
                 . " installhour = $hour,\n"
                 . " installday = $iday,\n"
                 . " patchsource = $src,\n"
                 . " propagate = $prop,\n"
                 . " newpatches = $new,\n"
                 . " serverurl = '$qu',\n"
                 . " updatecache = $updc,\n"
                 . " cacheseconds = $sec,\n"
                 . " restart = $rest,\n"
                 . " chain = $chan,\n"
                 . " chainseconds = $scha\n"
                 . " where id = $wid\n"
                 . " and mgroupid = $gid";
            $sql2 = "SELECT * FROM WUConfig WHERE mgroupid=$gid";
            $newcfg = array();
            $newcfg['management'] = $man;
            $newcfg['installhour'] = $hour;
            $newcfg['installday'] = $iday;
            $newcfg['patchsource'] = $src;
            $newcfg['propagate'] = $prop;
            $newcfg['newpatches'] = $new;
            $newcfg['serverurl'] = $url;
            $newcfg['updatecache'] = $updc;
            $newcfg['cacheseconds'] = $sec;
            $newcfg['restart'] = $rest;
            $newcfg['chain'] = $chan;
            $newcfg['chainseconds'] = $scha;
            GRPW_AuditMachineChange($env, $sql2, $newcfg, $db);
            $res = redcommand($sql,$db);
            if (affected($res,$db) == 1)
            {
                touch_wid($wid,$db);
                    $chng = 1;

                $name = $mgrp['name'];
                $stat = "w:$wid,g:$gid,p:$prop,m:$man,d:$iday,h:$hour,u:$updc,s:$sec";
                $text = "patch: update wcfg ($stat) $name";
                                debug_note($text);
            }
        }
        if (($chng) && ($ocfg))
        {
            $osrc = $ocfg['patchsource'];
            $ourl = $ocfg['serverurl'];
            $cmp  = ($url == $ourl)? 0 : 1;
            $sus  = ($src == constConfigPatchSourceSUSServer)? 1 : 0;
            if (($src != $osrc) || (($sus) && ($cmp)))
            {
                update_patchsource($env,$gid,$wid,$db);
            }
        }
        list_wcfg($env,$db);
    }







    function patch_rebuild($env,$db)
    {
        echo again($env);
        groups_init($db, constGroupsInitFull);
        clear_dirty(constPatchDirty,$db);
        patch_init($db);
        recalc_wcfg_all($db);
        echo again($env);
    }


    function test_wconfig($env,$db)
    {
        echo again($env);
        $hid  = $env['hid'];
        $row  = find_host($hid,$db);
        if ($row)
        {
            $site = $row['site'];
            $host = $row['host'];
            echo para("Test machine config for <b>$host</b> at <b>$site</b>.");

            $row = find_correct_wconfig($hid,$db);
            if ($row)
            {
                $wid = $row['wid'];
                $gid = $row['gid'];
                $tid = $row['tid'];
                $cat = $row['cat'];
                $pre = $row['pre'];
                $grp = $row['grp'];
                echo para("precedence:$pre, wid:$wid, gid:$gid, tid:$tid, grp:$grp, cat:$cat");
            }
            else
            {
                echo para('There is no correct wconfig record for this machine.');
            }
        }
        echo again($env);
    }




    function patch_mid(&$env,$db)
    {
        $mid  = $env['mid'];
        $ptch = find_patch_mid($mid,$db);
        $name = '';
        $pgrp = array( );
        $mgrp = array( );
        if ($ptch)
        {
            $name = $ptch['name'];
            $all  = constPatchAll;
            $pgrp = find_pgrp_name($name,$db);
            $mgrp = find_mgrp_name($all,$db);
        }
        if (($ptch) && ($pgrp) && ($mgrp))
        {
            $env['jid'] = $pgrp['pgroupid'];
            $env['gid'] = $mgrp['mgroupid'];
            check_pconfig($env,$db);
        }
        else
        {
            $was = ($name)? "<b>$name</b> was" : 'was';
            echo again($env);
            if (!$ptch) echo para("The update <b>$mid</b> was not found.");
            if (!$pgrp) echo para("The update group $was not found.");
            if (!$mgrp) echo para('The machine group <b>All</b> was not found.');
            echo again($env);
        }
    }



    function test_pconfig($env,$db)
    {
        echo again($env);
        $set  = array();
        $hid  = $env['hid'];
        $mid  = $env['mid'];
        $mach = find_host($hid,$db);
        $ptch = find_patch_mid($mid,$db);
        if (($mach) && ($ptch))
        {
            $site = $mach['site'];
            $host = $mach['host'];
            $name = $ptch['name'];
            echo "<p>Test software config.</p>\n"
              .  "<p>\n"
              .  "Site: <b>$site</b><br>\n"
              .  "Host: <b>$host</b><br>\n"
              .  "Name: <b>$name</b></p>\n";



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
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as X\n"
                 . " where M.censusuniq = X.censusuniq\n"
                 . " and X.id = $hid\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and P.mgroupid = G.mgroupid\n"
                 . " and P.pgroupid = J.pgroupid\n"
                 . " and P.pgroupid = N.pgroupid\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " and N.patchid = $mid\n"
                 . " and J.pcategoryid = K.pcategoryid\n"
                 . " order by ppre desc, mpre desc, ptim desc, mtim desc, pgrp, mgrp";
            $set = find_many($sql,$db);
        }


        if ($set)
        {
            $head = explode('|','pid|ppre|pcat|jid|pgrp|ptim|mpre|mcat|gid|mgrp|mtim');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "PatchConfig ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $pid  = $row['pid'];
                $gid  = $row['gid'];
                $jid  = $row['jid'];
                $mpre = $row['mpre'];
                $ppre = $row['ppre'];
                $mtim = shortdate($row['mtim']);
                $ptim = shortdate($row['ptim']);
                $mcat = @ disp($row,'mcat');
                $mgrp = @ disp($row,'mgrp');
                $pcat = @ disp($row,'pcat');
                $pgrp = @ disp($row,'pgrp');
                $args = array($pid,$ppre,$pcat,$jid,$pgrp,$ptim,$mpre,$mcat,$gid,$mgrp,$mtim);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            if (!$mach) echo para("Machine <b>$hid</b> not found.");
            if (!$ptch) echo para("Patch <b>$mid</b> not found.");
        }

        if (($mach) && (!$ptch))
        {
            $pcfg = search_pconfig($hid,$mid,$db);
            if ($pcfg)
            {
                $pid = $pcfg['pconfigid'];
                echo para("Found PatchConfig $pid");
            }
            else
            {
                echo para('Nothing found ...');
            }
        }

        echo again($env);
    }


    function simple_list(&$act,&$txt)
    {
        echo "\n\n<ol>\n";
        reset($txt);
        foreach ($txt as $key => $doc)
        {
            $cmd = html_link($act[$key],$doc);
            echo "<li>$cmd</li>\n";
        }
        echo "</ol>\n";
    }


    function command_list(&$act,&$txt)
    {
        echo para('What do you want to do?');
        simple_list($act,$txt);
    }


    function debug_menu($env,$db)
    {
        echo again($env);
        $self = $env['self'];
        $cmd  = "$self?act";

        $act = array( );
        $txt = array( );

        $act[] = $self;
        $txt[] = 'Wizard Home Page.';

        $act[] = "$cmd=menu";
        $txt[] = 'Config Home Page.';

        $act[] = "$cmd=dbug";
        $txt[] = 'Debug Home Page.';

        $act[] = "$cmd=dmap";
        $txt[] = 'Debug PatchGroupMap';

        $act[] = "$cmd=dwfg";
        $txt[] = 'Debug WUConfig';

        $act[] = "$cmd=dgrp";
        $txt[] = 'Debug PatchGroup';

        $act[] = "$cmd=dclc";
        $txt[] = 'Recalc WUConfig Cache';

        $act[] = "$cmd=dinp";
        $txt[] = 'Invalidate PatchConfig Cache';

        $act[] = "$cmd=dinw";
        $txt[] = 'Invalidate WUConfig Cache';

        $act[] = 'wu-sites.php?act=dmac';
        $txt[] = 'Debug Machines (sites)';

        $act[] = "$cmd=dhid";
        $txt[] = 'Debug Machines (config)';

        $act[] = "$cmd=dmpg";
        $txt[] = 'Debug MachineGroups';

        $act[] = 'wu-stats.php?act=dlst&debug=1&l=200';
        $txt[] = 'Debug Status';

        $act[] = "$cmd=dcfg";
        $txt[] = 'Debug PatchConfig';

        $act[] = "$cmd=dclr";
        $txt[] = 'Cancel All Beta Test';

        $act[] = "$cmd=dbet";
        $txt[] = 'Debug Beta Test';

        $act[] = "$cmd=dvlp";
        $txt[] = 'Validate PatchConfig Cache';

        $act[] = "$cmd=xxxx&glob=0";
        $txt[] = 'Remove All (except patches)';

        $act[] = "$cmd=xxxx&glob=1";
        $txt[] = 'Remove All (including patches)';

        $act[] = "$cmd=ddmp";
        $txt[] = 'Remove All MachineGroupMap';

        $act[] = "$cmd=sane";
        $txt[] = 'Database Consistancy Check';

        $act[] = "$cmd=init";
        $txt[] = 'Force Rebuild Builtin';

        $act[] = "$cmd=twid";
        $txt[] = 'Test Find WUConfig';

        $act[] = "$cmd=tpid";
        $txt[] = 'Test Find PatchConfig';

        $act[] = "$cmd=invd";
        $txt[] = 'Invalid Command';

        $act[] = "$cmd=????";
        $txt[] = 'Garbage Action';

        $act[] = 'wu-sites.php';
        $txt[] = 'Patch Sites';

        $act[] = 'wu-patch.php';
        $txt[] = 'Patch List';

        $act[] = 'wu-stats.php';
        $txt[] = 'Patch Status';

        command_list($act,$txt);
        echo again($env);
    }


    function debug_pmap($env,$db)
    {
        echo again($env);
        $sql = "select N.pgroupmapid as nid,\n"
             . " J.name as grp,\n"
             . " P.name as patch,\n"
             . " C.category as cat,\n"
             . " C.pcategoryid as kid,\n"
             . " J.pgroupid as jid,\n"
             . " P.patchid as mid\n"
             . " from PatchGroupMap as N,\n"
             . " PatchCategories as C\n"
             . " left join PatchGroups as J\n"
             . " on J.pgroupid = N.pgroupid\n"
             . " left join Patches as P\n"
             . " on P.patchid = N.patchid\n"
             . " where C.pcategoryid = J.pcategoryid\n"
             . " order by cat, grp, patch\n"
             . " limit 200";
        $set = find_many($sql,$db);
        if ($set)
        {
            $head = explode('|','nid|kid|cat|jid|group|mid|patch');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "Debug Patch Map ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $nid  = $row['nid'];
                $kid  = $row['kid'];
                $jid  = @ intval($row['jid']);
                $mid  = @ intval($row['mid']);
                $cat  = @ disp($row,'cat');
                $grp  = @ disp($row,'grp');
                $upd  = @ disp($row,'patch');
                $args = array($nid,$kid,$cat,$jid,$grp,$mid,$upd);
                echo table_data($args,0);
            }
            echo table_footer();
        }

        echo again($env);
    }


    function debug_pgrp($env,$db)
    {
        echo again($env);
        $sql = "select G.*,\n"
             . " C.category,\n"
             . " C.precedence from\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchCategories as C\n"
             . " where C.pcategoryid = G.pcategoryid\n"
             . " order by precedence desc, created desc, name, pgroupid\n"
             . " limit 200";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $head = explode('|','act|pre|kid|cat|jid|grp|user|glob|hman|type|date|text|bool');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "PatchGroups ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $jid  = $row['pgroupid'];
                $kid  = $row['pcategoryid'];
                $pre  = $row['precedence'];
                $date = timestamp($row['created']);
                $glob = ($row['global'])? 'Yes' : 'No';
                $hman = ($row['human'])? 'Yes' : 'No';
                $type = group_type($row);
                $text = disp($row,'search');
                $user = disp($row,'username');
                $grp  = disp($row,'name');
                $cat  = disp($row,'category');
                $bool = disp($row,'boolstring');
                $del  = "$self?jid=$jid&act=dpgp";
                $act  = html_link($del,'[delete]');
                $args = array($act,$pre,$kid,$cat,$jid,$grp,$user,$glob,$hman,$type,$date,$text,$bool);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        echo again($env);
    }



    function mgrp_details($env,$db)
    {
        debug_note("mgrp_details()");
        echo GRPS_display_again($env['custom'])? again($env) : '';
        $auth   = $env['auth'];
        $custom = $env['custom'];
        $n_id   = $env['notification_id'];
        $n_act  = $env['notification_act'];
        $r_id   = $env['report_id'];
        $r_act  = $env['report_act'];
        $a_id   = $env['asset_id'];
        $a_act  = $env['asset_act'];
        $set = array( );
        $gid = $env['gid'];
        $qu  = safe_addslashes($auth);
        $grp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        $set = GRPS_find_machines_from_mgroupid($gid, $qu, $db);
        if (($grp) && ($auth))
        {
            $good = legal_mgrp($env,$gid,$db);
            $tid  = $grp['mcatid'];
            $gid  = $grp['mgroupid'];
            $user = $grp['username'];
            $hman = $grp['human'];
            $type = $grp['style'];
            $mine = ($auth == $user);
            if (($hman) && ($mine) && ($good))
            {
                if ($type == constStyleManual)
                {
                    $page = $env['self'];
                    $scop = constScopGroup;
                    $args = "act=wemg&scop=$scop&tid=$tid&dgid=$gid";
                }
                else
                {
                    $page = '../acct/groups.php';
                    $args = "act=emg&tid=$tid&gid=$gid";
                }
                $href = "$page?$args&custom=$custom&notification_id="
                      . "$n_id&notification_act=$n_act"
                      . "&report_id=$r_id&report_ac=$r_act&asset_id="
                      . "$a_id&asset_act=$a_act";

                if(@$env['isparent'])
                {
                    $href .= '&isparent=' . $env['isparent'];
                }

                $text = 'Change machine selection.';
                $link = html_page($href,$text);
                echo para($link);
            }
            if (($hman) && (!$mine))
            {
                $text = 'You cannot edit this group because you do '
                      . 'not own it.';
                echo parabold($text);
            }
            if (!$hman)
            {
                $text = 'This is a machine-generated group and therefore cannot be modified.';
                echo parabold($text);
            }
            if (!$good)
            {
                $text = 'You cannot edit this group because it contains machines you do not own.';
                echo parabold($text);
            }
        }

        if (($set) && ($grp))
        {
            $rows = safe_count($set);
            $name = $grp['name'];
            $text = 'Site|Machine';
            $temp = '<br>|<br>';
            $mult = (12 <= $rows);

            if ($mult)
            {
                $sp   = indent(10);
                $temp = "$temp|$sp|$temp|$sp|$temp";
                $text = "$text|$sp|$text|$sp|$text";
            }
            $head = explode('|',$text);
            $cols = safe_count($head);
            $mesg = plural($rows,'machine');
            $text = "$name &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            $out = array( );

            if ($mult)
            {
                $none = explode('|',$temp);
                $max  = intval(($rows+2) / 3);
                for ($nnn = 0; $nnn < $max; $nnn++)
                {
                    $out[] = $none;
                }

                reset($set);
                $nnn = 0;
                foreach ($set as $key => $data)
                {
                    $row = intval($nnn % $max);
                    $col = intval($nnn / $max);
                    $xxs = $col * 3;
                    $xxh = $xxs + 1;
                    $out[$row][$xxs] = $data['site'];
                    $out[$row][$xxh] = $data['host'];
                    $nnn++;
                }
            }
            else
            {
                reset($set);
                foreach ($set as $key => $data)
                {
                    $site = $data['site'];
                    $host = $data['host'];
                    $out[] = array($site,$host);
                }
            }

            reset($out);
            foreach ($out as $key => $args)
            {
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            echo para('No machines found.');
        }

        echo GRPS_display_again($env['custom'])? again($env) : '';
    }


    function debug_mgrp($env,$db)
    {
        echo again($env);
        $qu  = safe_addslashes($env['auth']);
        $sql = "select G.*,\n"
             . " C.category,\n"
             . " C.precedence from\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
             . " where C.mcatuniq = G.mcatuniq\n"
             . " order by precedence desc, created desc, name, mgroupid\n"
             . " limit 200";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $head = explode('|','act|pre|tid|cat|gid|grp|user|glob|human|type|date|bool');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "MachineGroups &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $gid  = $row['mgroupid'];
                $tid  = $row['mcatid'];
                $pre  = $row['precedence'];
                $date = timestamp($row['created']);
                $glob = ($row['global'])? 'Yes' : 'No';
                $hman = ($row['human'])? 'Yes' : 'No';
                $type = group_type($row);
                $user = disp($row,'username');
                $grp  = disp($row,'name');
                $cat  = disp($row,'category');
                $bool = disp($row,'boolstring');
                $del  = "$self?gid=$gid&act=ddmg";
                $act  = html_link($del,'[delete]');
                $args = array($act,$pre,$tid,$cat,$gid,$grp,$user,$glob,$hman,$type,$date,$bool);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        echo again($env);
    }


    function debug_del_pgrp($env,$db)
    {
        $pgrp = $env['pgrp'];
        if ($pgrp)
        {
            $jid = $env['pgrp']['pgroupid'];
            $grp = $env['pgrp']['name'];
            debug_note("pgrp: $grp, jid:$jid");
            kill_pgrp_jid($jid,$db);
        }
        debug_pgrp($env,$db);
    }


    function debug_del_pcfg($env,$db)
    {
        $pid  = $env['pid'];
        $pcfg = find_pcfg_pid($pid,$db);
        if ($pcfg)
        {
            $gid = $pcfg['mgroupid'];
            $jid = $pcfg['pgroupid'];
            $num = kill_pcfg($gid,$jid,$db);
            debug_note("delete pcfg (p:$pid,g:$gid,j:$jid,n:$num)");
        }
        else
        {
            debug_note("not found: pcfg ($pid)");
        }
        debug_pcfg($env,$db);
    }


    function debug_del_mgrp($env,$showTable,$db)
    {
        $auth = $env['auth'];
        $gid  = $env['gid'];
        $mgrp = find_mgrp_gid($gid, constReturnGroupTypeOne, $db);
        if ($mgrp)
        {
            if (delete_mgrp_gid($gid,$db))
            {
                kill_gid($gid,$db);
                delete_expr_gid($gid,$db);

                $name = $mgrp['name'];
                $num  = delete_host_gid($gid,$db);
                $stat = "g:$gid,n:$num,u:$auth";
                $text = "groups: mgrp removed ($stat) $name";
                                debug_note($text);
            }
        }
        if($showTable)
        {
            debug_mgrp($env,$db);
        }
    }


    function debug_sanity($env,$db)
    {
        echo again($env);
        $auth = $env['auth'];
        if ($auth)
        {
            rebuild_kill($db);
            $sql = "select G.* from\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
                 . " left join ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
                 . " on G.mcatuniq = C.mcatuniq\n"
                 . " where C.mcatid is NULL\n"
                 . " group by G.mgroupid";
            $set = find_many($sql,$db);
            if ($set)
            {
                reset($set);
                foreach ($set as $key => $row)
                {
                    $gid  = $row['mgroupid'];
                    $name = $row['name'];
                    $num  = delete_mgrp_gid($gid,$db);
                    if ($num)
                    {
                        delete_host_gid($gid,$db);
                        delete_expr_gid($gid,$db);
                        kill_gid($gid,$db);
                        $text = "groups: remove extra (g:$gid) $name";
                                                debug_note($text);
                    }
                }
            }
            else
            {
                debug_note('core.MachineGroups.mcatid: OK.');
            }
            $sql = "select J.* from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J\n"
                 . " left join ".$GLOBALS['PREFIX']."softinst.PatchCategories as K\n"
                 . " on J.pcategoryid = K.pcategoryid\n"
                 . " where K.pcategoryid is NULL\n"
                 . " group by J.pcategoryid";
            $set = find_many($sql,$db);
            if ($set)
            {
                reset($set);
                foreach ($set as $key => $row)
                {
                    $jid  = $row['pgroupid'];
                    $name = $row['name'];
                    $num  = kill_pgrp_jid($jid,$db);
                    if ($num)
                    {
                        $text = "patch: remove extra (j:$jid) $name";
                                                debug_note($text);
                    }
                }
            }
            else
            {
                debug_note($GLOBALS['PREFIX'].'softinst.PatchGroups.pcategroryid: OK.');
            }
            $sql = "select W.mgroupid from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.WUConfig as W\n"
                 . " left join ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
                 . " on W.mgroupid = G.mgroupid\n"
                 . " where G.mgroupid is NULL\n"
                 . " group by W.mgroupid";
            $set = find_many($sql,$db);
            if ($set)
            {
                $tmp = distinct($set,'mgroupid');
                $num = safe_count($tmp);
                $txt = join(',',$tmp);
                $sql = "delete from\n"
                     . " ".$GLOBALS['PREFIX']."softinst.WUConfig\n"
                     . " where mgroupid in ($txt)";
                $res = redcommand($sql,$db);
                $del = affected($res,$db);
                debug_note("$num ($txt) not in groups, $del removed.");
            }
            else
            {
                debug_note($GLOBALS['PREFIX'].'softinst.WUConfig.mgroupid: OK.');
            }
            $sql = "select P.mgroupid from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P\n"
                 . " left join ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
                 . " on P.mgroupid = G.mgroupid\n"
                 . " where G.mgroupid is NULL\n"
                 . " group by P.mgroupid";
            $set = find_many($sql,$db);
            if ($set)
            {
                $tmp = distinct($set,'mgroupid');
                $num = safe_count($tmp);
                $txt = join(',',$tmp);
                $sql = "delete from\n"
                     . " ".$GLOBALS['PREFIX']."softinst.PatchConfig\n"
                     . " where mgroupid in ($txt)";
                $res = redcommand($sql,$db);
                $del = affected($res,$db);
                debug_note("$num ($txt) not in groups, $del removed.");
            }
            else
            {
                debug_note($GLOBALS['PREFIX'].'softinst.PatchConfig.mgroupid: OK.');
            }
            $sql = "select P.pgroupid from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P\n"
                 . " left join ".$GLOBALS['PREFIX']."softinst.PatchGroups as J\n"
                 . " on P.pgroupid = J.pgroupid\n"
                 . " where J.pgroupid is NULL\n"
                 . " group by P.pgroupid";
            $set = find_many($sql,$db);
            if ($set)
            {
                $tmp = distinct($set,'pgroupid');
                $num = safe_count($tmp);
                $txt = join(',',$tmp);
                $sql = "delete from\n"
                     . " ".$GLOBALS['PREFIX']."softinst.PatchConfig\n"
                     . " where pgroupid in ($txt)";
                $res = redcommand($sql,$db);
                $del = affected($res,$db);
                debug_note("$num ($txt) not in groups, $del removed.");
            }
            else
            {
                debug_note($GLOBALS['PREFIX'].'softinst.PatchConfig.pgroupid: OK.');
            }
        }
        echo again($env);
    }




    function debug_del_mmap($env,$db)
    {
        $auth = $env['auth'];
        if ($auth)
        {

            $sql = "select mgmapid from ".$GLOBALS['PREFIX']."core.MachineGroupMap";
            $set = DSYN_DeleteSet($sql, constDataSetCoreMachineGroupMap,
                "mgmapid", "debug_del_mmap", 0, 1,
                constOperationPermanentDelete, $db);

            $tab =$GLOBALS['PREFIX'].'core.MachineGroupMap';
            $num = count_table($tab,$db);
            if($set)
            {
                $sql = "delete from $tab";
                $res = redcommand($sql,$db);

                $stat = "n:$num,u:$auth";
                $text = "groups: membership revoked ($stat)";
                                debug_note($text);
            }
        }
        debug_mgrp($env,$db);
    }


    function configtype($type)
    {
        switch ($type)
        {
            case constConfigTypeNormal  : return 'norm';
            case constConfigTypeCritical: return 'crit';
            case constConfigTypeBeta    : return 'beta';
            default                     : return 'invd';
        }
    }




    function debug_pcfg($env,$db)
    {
        echo again($env);
        $sql = "select P.*,\n"
             . " J.name as pgrp,\n"
             . " G.name as mgrp,\n"
             . " G.mgroupid as gid,\n"
             . " J.pgroupid as jid,\n"
             . " G.created as mtim,\n"
             . " J.created as ptim,\n"
             . " C.category as mcat,\n"
             . " C.precedence as mpre,\n"
             . " K.precedence as ppre,\n"
             . " K.category as pcat from\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P,\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J,\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchCategories as K,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
             . " where P.mgroupid = G.mgroupid\n"
             . " and P.pgroupid = J.pgroupid\n"
             . " and G.mcatuniq = C.mcatuniq\n"
             . " and K.pcategoryid = J.pcategoryid\n"
             . " order by ppre desc, mpre desc, ptim desc, mtim desc, pgrp, mgrp\n"
             . " limit 200";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $cmd  = "$self?act=ddpc&pid";

            $head = explode('|','act|pid|ppre|jid|pgrp|mpre|gid|mgrp|type|days|ins|last');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "PatchConfig &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $gid  = $row['gid'];
                $jid  = $row['jid'];
                $mpre = $row['mpre'];
                $ppre = $row['ppre'];
                $pid  = $row['pconfigid'];
                $ssec = $row['scheddelay'];
                $type = configtype($row['configtype']);
                $days = round($ssec / 86400);
                $ins  = installation($row['installation']);
                $last = timestamp($row['lastupdate']);
                $mcat = @ disp($row,'mcat');
                $mgrp = @ disp($row,'mgrp');
                $pcat = @ disp($row,'pcat');
                $pgrp = @ disp($row,'pgrp');
                $acts = html_link("$cmd=$pid",'[delete]');
                $args = array($acts,$pid,$ppre,$jid,$pgrp,$mpre,$gid,$mgrp,$type,$days,$ins,$last);
                echo table_data($args,0);
            }
            echo table_footer();
        }

        echo again($env);
    }



    function debug_census(&$env,$db)
    {
        $sql = "select C.host, C.site, M.* from\n"
             . " ".$GLOBALS['PREFIX']."softinst.Machine as M,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C\n"
             . " where C.id = M.id\n"
             . " order by site, host, id";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $head = explode('|','act|site|host|id|wid|contact|change');
            $rows = safe_count($set);
            $cols = safe_count($head);
            $text = "Machine &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $site = disp($row,'site');
                $host = disp($row,'host');
                $id   = $row['id'];
                $wid  = $row['wuconfigid'];
                $href = "$self?act=dhid&hid=$id";
                $acts = html_link($href,'[detail]');
                $chng = timestamp($row['lastchange']);
                $last = timestamp($row['lastcontact']);
                $args = array($acts,$site,$host,$id,$wid,$last,$chng);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function debug_wcfg($env,$db)
    {
        echo again($env);
        $sql = "select W.*,\n"
             . " G.name as grp,\n"
             . " C.category as cat,\n"
             . " C.precedence as pre from\n"
             . " ".$GLOBALS['PREFIX']."softinst.WUConfig as W,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as C\n"
             . " where W.mgroupid = G.mgroupid\n"
             . " and G.mcatuniq = C.mcatuniq\n"
             . " order by pre, grp, id\n"
             . " limit 200";
        $set = find_many($sql,$db);
        if ($set)
        {
            $head = explode('|','wid|pre|cat|grp|gid|man|day|hour|new|url|prop|c|cday|last');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "".$GLOBALS['PREFIX']."softinst.WUConfig &nbsp; ($rows found)";
            $days = day_options();
            $hopt = hour_options();

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $gid  = $row['mgroupid'];
                $wid  = $row['id'];
                $grp  = disp($row,'grp');
                $cat  = disp($row,'cat');
                $pre  = $row['pre'];
                $man  = management($row['management']);
                $day  = $row['installday'];
                $hour = $row['installhour'];
                $updc = $row['updatecache'];
                $csec = $row['cacheseconds'];
                $cday = round($csec / 86400);
                $iday = $days[$day];
                $hour = $hopt[$hour];
                $new  = newpatch($row['newpatches']);
                $url  = disp($row,'serverurl');
                $prop = propagate($row['propagate']);
                $last = timestamp($row['lastupdate']);
                $args = array($wid,$pre,$cat,$grp,$gid,$man,$iday,$hour,$new,$url,$prop,$updc,$cday,$last);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        debug_census($env,$db);
        echo again($env);
    }


    function debug_host(&$env,$db)
    {
        echo again($env);
        $set  = array( );
        $hid  = $env['hid'];
        $mach = find_host($hid,$db);
        if ($mach)
        {
            $host = $mach['host'];
            $site = $mach['site'];
            $sql = "select S.*, P.name from\n"
                 . " ".$GLOBALS['PREFIX']."softinst.Patches as P,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchStatus as S\n"
                 . " where S.id = $hid\n"
                 . " and S.patchid = P.patchid\n"
                 . " order by name";
            $set = find_many($sql,$db);
        }
        else
        {
            debug_census($env,$db);
        }

        if ($set)
        {
            $self = $env['self'];
            $head = explode('|','act|name|mid|pid|sid|chng|dtect|inst');
            $rows = safe_count($set);
            $cols = safe_count($head);
            $text = ucwords("$host at $site &nbsp; ($rows found)");

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $name = disp($row,'name');
                $mid  = $row['patchid'];
                $pid  = $row['patchconfigid'];
                $sid  = $row['patchstatusid'];
                $href = "$self?act=tpid&hid=$hid&mid=$mid";
                $acts = html_link($href,'[search]');
                $chng = timestamp($row['lastchange']);
                $inst = timestamp($row['lastinstall']);
                $dtec = timestamp($row['detected']);
                $args = array($acts,$name,$mid,$pid,$sid,$chng,$dtec,$inst);
                echo table_data($args,0);
            }
            echo table_footer();
        }

        echo again($env);
    }


    function clear_beta(&$env,$db)
    {
        echo again($env);
        $now = time();
        $crt = constConfigTypeCritical;
        $bet = constConfigTypeBeta;
        $nrm = constConfigTypeNormal;
        $sql = "update PatchConfig set\n"
             . " lastupdate = $now,\n"
             . " scheddelay = 0,\n"
             . " notifydelay = 0,\n"
             . " configtype = $nrm\n"
             . " where configtype = $bet\n"
             . " or scheddelay != 0";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        debug_note("$num beta cleared");
        echo again($env);
    }


    function debug_beta(&$env,$db)
    {
        echo again($env);
        $row = find_glbl_pcfg($db);
        $jid = ($row)? $row['pgroupid'] : 0;
        $sql = "select P.*,\n"
             . " G.name as name from\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G\n"
             . " where P.mgroupid = G.mgroupid\n"
             . " and P.pgroupid = $jid\n"
             . " order by name, pconfigid\n"
             . " limit 200";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $cmd  = "$self?act=ddpc&pid";

            $head = explode('|','act|pid|jid|gid|name|type|days|ins|last');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "PatchConfig &nbsp; ($rows found)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $gid  = $row['mgroupid'];
                $jid  = $row['pgroupid'];
                $pid  = $row['pconfigid'];
                $name = $row['name'];
                $ssec = $row['scheddelay'];
                $type = configtype($row['configtype']);
                $days = round($ssec / 86400);
                $ins  = installation($row['installation']);
                $last = timestamp($row['lastupdate']);
                $acts = html_link("$cmd=$pid",'[delete]');
                $args = array($acts,$pid,$jid,$gid,$name,$type,$days,$ins,$last);
                echo table_data($args,0);
            }
            echo table_footer();
        }

        echo again($env);
    }

    function debug_calc(&$env,$db)
    {
        recalc_wcfg_all($db);
        debug_wcfg($env,$db);
    }


    function invalid_wcfg(&$env,$db)
    {
        echo again($env);
        $num = invalidate_wcache($db);
        echo para("$num Machine records updated.");
        echo again($env);
    }

    function invalid_pcfg(&$env,$db)
    {
        echo again($env);
        $num = invalidate_pcache($db);
        echo para("$num PatchStatus records updated.");
        echo again($env);
    }

    function validate_pcache(&$env,$db)
    {
        echo again($env);
        $sql = "select patchstatusid, patchid, id from\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchStatus\n"
             . " where patchconfigid = 0";
        $set = find_many($sql,$db);
        if ($set)
        {
            $num = 0;
            $inv = safe_count($set);
            echo para("Update <b>$inv</b> invalid PatchStatus records.");

            reset($set);
            foreach ($set as $key => $row)
            {
                $sid = $row['patchstatusid'];
                $mid = $row['patchid'];
                $hid = $row['id'];
                $tmp = find_correct_pconfig($hid, $mid, $db);
                if ($tmp)
                {
                    $now = time();
                    $pid = $tmp['pid'];
                    $sql = "update PatchStatus set\n"
                         . " patchconfigid = $pid,\n"
                         . " lastchange = $now\n"
                         . " where patchstatusid = $sid";
                    $res = command($sql,$db);
                    if (affected($res,$db))
                    {
                        $num++;
                    }
                }
            }
            echo para("There were <b>$num</b> PatchStatus records changed.");
        }
        else
        {
            echo para('Nothing currently flagged as invalid');
        }
        echo again($env);
    }

    function table_blast($tab,$db)
    {
        $sql = "delete from $tab";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
    }


    function reset_patch($env,$db)
    {
        $auth = $env['auth'];
        $glob = $env['glob'];
        echo again($env);



        table_blast($GLOBALS['PREFIX'].'softinst.PatchStatus',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.PatchGroupMap',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.PatchExpression',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.PatchGroups',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.PatchCategories',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.Machine',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.WUConfig',$db);
        table_blast($GLOBALS['PREFIX'].'softinst.PatchConfig',$db);
        if ($glob) table_blast($GLOBALS['PREFIX'].'softinst.Patches',$db);
        groups_init($db, constGroupsInitFull);
        update_opt(constPatchDirty,constDirtySet,$db);
        $text = "patch: blasted by $auth";
                echo again($env);
    }


    function none_yet()
    {
        echo para("There aren't any yet.");
    }

    function stat_stra(&$env,$db)
    {
        $set  = array( );
        $ord  = $env['ord'];
        $pcfg = find_glbl_pcfg($db);
        if ($pcfg)
        {
            $gpid = $pcfg['pconfigid'];
            $ggid = $pcfg['mgroupid'];
            $gjid = $pcfg['pgroupid'];

            $adm = $env['admn'];
            $qu  = safe_addslashes($env['auth']);
            $wrd = stat_order($ord);
            $sql = "select P.*,\n"
                 . " G.name as name from\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
                 . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P\n"
                 . " where P.pgroupid = $gjid\n"
                 . " and P.mgroupid = G.mgroupid\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and M.censusuniq = C.censusuniq\n"
                 . " and C.site = U.customer\n"
                 . " and U.username = '$qu'\n"
                 . " group by P.pconfigid\n"
                 . " order by $wrd";
            $set = find_many($sql,$db);
        }
        if ($set)
        {
            $self = $env['self'];
            $o    = "$self?act=pmth&ord";
            $name = ($ord == 0)? "$o=1" : "$o=0";               $shed = ($ord == 2)? "$o=3" : "$o=2";               $ntfy = ($ord == 4)? "$o=5" : "$o=4";               $meth = ($ord == 6)? "$o=7" : "$o=6";               $date = ($ord == 8)? "$o=9" : "$o=8";
            $acts = 'Action';
            $meth = html_link($meth,'Update Method');
            $name = html_link($name,'Machines');
            $shed = html_link($shed,'Schedule');
            $ntfy = html_link($ntfy,'Notification');
            $date = html_link($date,'Last');
            $head = array($acts,$name,$meth,$shed,$ntfy,$date);

            $rows = safe_count($set);
            $mesg = plural($rows,'action');
            $text = "Update Method &nbsp; ($mesg)";
            $cols = safe_count($head);

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $pcfg)
            {
                $name = $pcfg['name'];
                $inst = $pcfg['installation'];
                $gid  = $pcfg['mgroupid'];
                $jid  = $pcfg['pgroupid'];
                $last = $pcfg['lastupdate'];
                $wpgroupid = $pcfg['wpgroupid'];
                $date = shortdate($last);
                $cmd  = "$self?gid=$gid&jid=$jid&wpgroupid=$wpgroupid&act";
                $meth = method($inst);
                $shed = tiny_schedule($pcfg);
                $ntfy = tiny_notify($pcfg);
                $view = html_link("$cmd=gdet",$name);
                $kill = html_link("$cmd=ppic",'[delete]');
                if (($adm) || ($gid != $ggid))
                {
                    $meth = html_link("$cmd=meth",$meth);
                    $shed = html_link("$cmd=shed#shed",$shed);
                    $ntfy = html_link("$cmd=shed#ntfy",$ntfy);
                }
                $acts = ($gid == $ggid)? '<br>' : $kill;
                $args = array($acts,$view,$meth,$shed,$ntfy,$date);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            none_yet();
        }
    }




    function find_quad($env,$ins,$tag,$db)
    {
        $all = safe_addslashes(constCatAll);
        $pat = safe_addslashes("$tag %");
        $qu  = safe_addslashes($env['auth']);
        $tmp = ($env['admn'])? '' : " and G.name != '$all'\n";
        $wrd = stat_order($env['ord']);
        $sql = "select P.*,\n"
             . " G.name as name from\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchGroups as J,\n"
             . " ".$GLOBALS['PREFIX']."softinst.PatchConfig as P\n"
             . " where P.installation = $ins\n"
             . " and P.mgroupid = G.mgroupid\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " and P.pgroupid = J.pgroupid\n"
             . " and J.name like '$pat'\n$tmp"
             . " and M.censusuniq = C.censusuniq\n"
             . " and C.site = U.customer\n"
             . " and U.username = '$qu'\n"
             . " group by P.wpgroupid\n"
             . " order by $wrd";
        return find_many($sql,$db);
    }


    function stat_appr(&$env,$db)
    {
        $ord = $env['ord'];
        $ins = constPatchScheduleInstall;
        $adm = $env['admn'];
        $set = find_quad($env,$ins,constPgAPPR,$db);
        if ($set)
        {
            $self = $env['self'];
            $o    = "$self?act=papp&ord";

            $name = ($ord == 0)? "$o=1" : "$o=0";               $shed = ($ord == 2)? "$o=3" : "$o=2";               $ntfy = ($ord == 4)? "$o=5" : "$o=4";               $date = ($ord == 8)? "$o=9" : "$o=9";
            $acts = 'Action';
            $edit = 'Updates';
            $name = html_link($name,'Machines');
            $shed = html_link($shed,'Schedule');
            $ntfy = html_link($ntfy,'Notification');
            $date = html_link($date,'Last');

            $head = array($acts,$name,$edit,$shed,$ntfy,$date);
            $rows = safe_count($set);
            $cols = safe_count($head);
            $mesg = plural($rows,'action');
            $text = "Approved Updates &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $pcfg)
            {
                $name = $pcfg['name'];
                $gid  = $pcfg['mgroupid'];
                $jid  = $pcfg['pgroupid'];
                $last = $pcfg['lastupdate'];
                $date = shortdate($last);
                $shed = tiny_schedule($pcfg);
                $ntfy = tiny_notify($pcfg);
                $wpgroupid = $pcfg['wpgroupid'];
                $cmd  = "$self?gid=$gid&jid=$jid&wpgroupid=$wpgroupid&act";
                $tmp  = "$self?gid=$gid&jid=$jid&wpgroupid=$wpgroupid&dtc=-1"
                    . "&int=-1&act";
                $view = html_page("$cmd=gdet",$name);
                $kill = html_page("$cmd=ppic",'[delete]');
                $edit = html_page("$tmp=qqqa",'[approve]');
                $shed = html_page("$cmd=shed#shed",$shed);
                $ntfy = html_page("$cmd=shed#ntfy",$ntfy);
                $args = array($kill,$view,$edit,$shed,$ntfy,$date);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            none_yet();
        }
    }


    function stat_decl(&$env,$db)
    {
        $ord = $env['ord'];
        $ins = constPatchInstallNever;
        $set = find_quad($env,$ins,constPgDECL,$db);
        if ($set)
        {
            $self = $env['self'];
            $o    = "$self?act=pdec&ord";

            $name = ($ord == 0)? "$o=1" : "$o=0";              $date = ($ord == 8)? "$o=9" : "$o=8";
            $acts = 'Action';
            $edit = 'Updates';
            $name = html_link($name,'Machines');
            $date = html_link($date,'Last');

            $head = array($acts,$name,$edit,$date);
            $cols = safe_count($head);
            $rows = safe_count($set);
            $mesg = plural($rows,'action');
            $text = "Declined Updates &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $pcfg)
            {
                $name = $pcfg['name'];
                $inst = $pcfg['installation'];
                $gid  = $pcfg['mgroupid'];
                $jid  = $pcfg['pgroupid'];
                $last = $pcfg['lastupdate'];
                $date = shortdate($last);
                $cmd  = "$self?gid=$gid&jid=$jid&act";
                $tmp  = "$self?gid=$gid&dtc=1&int=0&act";
                $view = html_page("$cmd=gdet",$name);
                $kill = html_page("$cmd=ppic",'[delete]');
                $decl = html_page("$tmp=qqqd",'[decline]');
                $args = array($kill,$view,$decl,$date);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            none_yet();
        }
    }


    function stat_remv(&$env,$db)
    {
        $ord = $env['ord'];
        $ins = constPatchScheduleRemove;
        $set = find_quad($env,$ins,constPgREMV,$db);
        if ($set)
        {
            $self = $env['self'];
            $o    = "$self?act=prem&ord";
            $name = ($ord == 0)? "$o=1" : "$o=0";              $shed = ($ord == 2)? "$o=3" : "$o=2";              $ntfy = ($ord == 4)? "$o=5" : "$o=4";              $date = ($ord == 8)? "$o=9" : "$o=8";
            $acts = 'Action';
            $edit = 'Updates';
            $name = html_link($name,'Machines');
            $shed = html_link($shed,'Schedule');
            $ntfy = html_link($ntfy,'Notification');
            $date = html_link($date,'Last');
            $head = array($acts,$name,$edit,$shed,$ntfy,$date);

            $rows = safe_count($set);
            $mesg = plural($rows,'action');
            $text = "Remove Updates &nbsp; ($mesg)";
            $cols = safe_count($head);

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $pcfg)
            {
                $name = $pcfg['name'];
                $inst = $pcfg['installation'];
                $gid  = $pcfg['mgroupid'];
                $jid  = $pcfg['pgroupid'];
                $last = $pcfg['lastupdate'];
                $date = shortdate($last);
                $shed = tiny_schedule($pcfg);
                $ntfy = tiny_notify($pcfg);
                $cmd  = "$self?gid=$gid&jid=$jid&act";
                $tmp  = "$self?gid=$gid&dtc=1&int=1&act";
                $view = html_page("$cmd=gdet",$name);
                $kill = html_page("$cmd=ppic",'[delete]');
                $remv = html_page("$tmp=qqqr",'[remove]');
                $shed = html_page("$cmd=shed#shed",$shed);
                $ntfy = html_page("$cmd=shed#ntfy",$ntfy);
                $args = array($kill,$view,$remv,$shed,$ntfy,$date);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            none_yet();
        }
    }


    function stat_crit(&$env,$db)
    {
        $ord = $env['ord'];
        $ins = constPatchScheduleInstall;
        $set = find_quad($env,$ins,constPgCRIT,$db);
        if ($set)
        {
            $self = $env['self'];
            $o    = "$self?act=pcrt&ord";
            $name = ($ord == 0)? "$o=1" : "$o=0";               $shed = ($ord == 2)? "$o=3" : "$o=2";               $ntfy = ($ord == 4)? "$o=5" : "$o=4";               $date = ($ord == 8)? "$o=9" : "$o=8";
            $acts = 'Action';
            $edit = 'Updates';
            $name = html_link($name,'Machines');
            $shed = html_link($shed,'Schedule');
            $ntfy = html_link($ntfy,'Notification');
            $date = html_link($date,'Last');
            $head = array($acts,$name,$edit,$shed,$ntfy,$date);

            $cols = safe_count($head);
            $rows = safe_count($set);
            $mesg = plural($rows,'action');
            $text = "Critical Updates &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $pcfg)
            {
                $name = $pcfg['name'];
                $inst = $pcfg['installation'];
                $gid  = $pcfg['mgroupid'];
                $jid  = $pcfg['pgroupid'];
                $last = $pcfg['lastupdate'];
                $date = shortdate($last);
                $shed = tiny_schedule($pcfg);
                $ntfy = tiny_notify($pcfg);
                $cmd  = "$self?gid=$gid&jid=$jid&act";
                $tmp  = "$self?gid=$gid&dtc=1&int=0&act";
                $view = html_page("$cmd=gdet",$name);
                $kill = html_page("$cmd=ppic",'[delete]');
                $crit = html_page("$tmp=qqqc",'[install]');
                $shed = html_page("$cmd=shed#shed",$shed);
                $ntfy = html_page("$cmd=shed#ntfy",$ntfy);
                $args = array($kill,$view,$crit,$shed,$ntfy,$date);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            none_yet();
        }
    }




    function stat_beta(&$env,$db)
    {
        $self = $env['self'];
        $auth = $env['auth'];
        $beta = array();
        $enab = 'No';
        $time = 'None';
        $name = 'None';
        $pcfg = user_pcfg($auth,0,$db);
        if ($pcfg)
        {
            $beta = find_beta_group($auth,$db);
        }
        if ($beta)
        {
            $enab = 'Yes';
            $gid  = $beta['gid'];
            $cmd  = "$self?gid=$gid&act";
            $href = "$cmd=gdet";
            $form = "$cmd=wbfm";
            $name = html_page($href,$beta['grp']);
            $time = delay_time($beta['secs']);
            $time = html_page($form,$time);
        }
        $href = "$self?act=wtst";
        $acts = html_page($href,$enab);
        $text = 'Test Machines';
        $head = explode('|','Enabled|Machines|Delay');
        $cols = safe_count($head);
        $args = array($acts,$name,$time);

        echo table_header();
        echo pretty_header($text,$cols);
        echo table_data($head,1);
        echo table_data($args,0);
        echo table_footer();
    }




    function stat_prop(&$env,$db)
    {
        $mall = find_mgrp_name(constCatAll,$db);
        $ggid = ($mall)? $mall['mgroupid'] : 0;
        $ord = value_range(8,15,$env['ord']);
        $qu  = safe_addslashes($env['auth']);
        $wrd = stat_order($ord);
        $adm = $env['admn'];
        $sql = "select W.*,\n"
             . " G.name as name from\n"
             . " WUConfig as W,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C\n"
             . " where W.mgroupid = G.mgroupid\n"
             . " and M.mgroupuniq = G.mgroupuniq\n"
             . " and M.censusuniq = C.censusuniq\n"
             . " and C.site = U.customer\n"
             . " and U.username = '$qu'\n"
             . " group by W.id\n"
             . " order by $wrd";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $o    = "$self?act=pprp&ord";

            $date = ($ord == 8)?  "$o=9"  : "$o=8";                $prop = ($ord == 10)? "$o=11" : "$o=10";               $keep = ($ord == 12)? "$o=13" : "$o=12";               $name = ($ord == 14)? "$o=15" : "$o=14";
            $acts = 'Action';
            $name = html_link($name,'Machines');
            $prop = html_link($prop,'Download policy');
            $keep = html_link($keep,'Retention policy');
            $date = html_link($date,'Last');
            $head = array($acts,$name,$prop,$keep,$date);

            $cols = safe_count($head);
            $rows = safe_count($set);
            $mesg = plural($rows,'action');
            $text = "Propagation  &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $wcfg)
            {
                $wid  = $wcfg['id'];
                $gid  = $wcfg['mgroupid'];
                $name = $wcfg['name'];
                $prop = $wcfg['propagate'];
                $updc = $wcfg['updatecache'];
                $csec = $wcfg['cacheseconds'];
                $last = $wcfg['lastupdate'];
                $prop = semi_prop($prop);
                $days = round($csec / 86400);
                $date = shortdate($last);
                $cmd  = "$self?gid=$gid&act";
                $keep = ($updc)? "Keep $days days" : 'Do not keep';
                $kill = html_page("$cmd=pwic&wid=$wid",'[delete]');
                $view = html_page("$cmd=gdet",$name);
                if (($adm) || ($gid != $ggid))
                {
                    $prop = html_page("$cmd=prop",$prop);
                    $keep = html_page("$cmd=prop",$keep);
                }
                $acts = ($gid == $ggid)? '<br>' : $kill;
                $args = array($acts,$view,$prop,$keep,$date);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            none_yet();
        }
    }


    function page_meth(&$env,$db)
    {
        echo again($env);
        stat_stra($env,$db);
        echo again($env);
    }

    function page_appr(&$env,$db)
    {
        echo again($env);
        stat_appr($env,$db);
        echo again($env);
    }


    function page_decl(&$env,$db)
    {
        echo again($env);
        stat_decl($env,$db);
        echo again($env);
    }


    function page_remv(&$env,$db)
    {
        echo again($env);
        stat_remv($env,$db);
        echo again($env);
    }


    function page_crit(&$env,$db)
    {
        echo again($env);
        stat_crit($env,$db);
        echo again($env);
    }


    function page_prop(&$env,$db)
    {
        echo again($env);
        stat_prop($env,$db);
        echo again($env);
    }




    function redirect_action($act,$frm,$post,$gid,$jid,$dgid,$djid)
    {



        if (($frm == 'awid') || ($frm == 'ewid'))
        {
            if (!$gid) $act = $frm;
        }

        if ($frm == 'cpwa')
        {
            if (!$dgid) $act = $frm;
        }

        if (($frm == 'apid') || ($frm == 'epid'))
        {
            if ((!$gid) || (!$jid)) $act = $frm;
        }

        if ($frm == 'cppa')
        {
            if ((!$dgid) || (!$djid)) $act = $frm;
        }



        if ($frm == 'epge')
        {
            if ($post == constButtonDone) $act = 'epgd';
        }



        if ($frm == 'epgm')
        {
            if ($post == constButtonAll)  $act = $frm;
            if ($post == constButtonNone) $act = $frm;
        }
        return $act;
    }


    function section(&$env,&$set,$tag)
    {
        $name = $set[$tag];
        echo "<hr>\n";
        echo mark($tag);
        echo again($env);
        echo "<h3>$name</h3>\n<br>\n";
    }


    function wiz_names()
    {
        return array
        (
            'stra' => 'Select update method',
            'remv' => 'Remove updates',
            'appr' => 'Approve updates',
            'decl' => 'Decline updates',
            'crit' => 'Install critical updates',
            'beta' => 'Select test machines to install'
                   .  ' updates before other machines',
            'prop' => 'Configure update download and propagation',
            'stat' => 'Show wizard status'
        );
    }


    function wiz_help(&$env,$db)
    {
        $help = $env['frm'];
        $acts = 'window.close();';
        $clos = click('Close',$acts);
        echo again($env);
        echo para($clos);
        echo again($env);
    }


    function wiz_stat_blurb(&$env)
    {
        $self   = $env['self'];
        $status = html_link('wu-stats.php','MUM status page');
        $wizard = html_link($self,'MUM wizard page');
        echo <<< HERE

        <p>
          PLEASE READ CAREFULLY BEFORE PROCEEDING.
        </p>

        <p>
          On this page, you will find a complete record of all Microsoft Update
          Management (MUM) wizard actions taken on this ASI server, except for
          those actions you deleted.
        </p>

        <p>
          You can update a previously taken action at any time.
        </p>

        <p>
          MUM will run the latest update of an MUM wizard action on all systems
          the action applies to. If you change an MUM action before it has been
          executed on all systems it applies to, you will end up with some
          systems running both the original action and the updated action, and
          some systems running only the updated action. This will depend both
          on the action itself, and the time lag between action configuration
          changes on the ASI server, and their retrieval by the systems it
          applies to as  driven by the Scrip 177 (Scrip Configuration Changes)
          schedule.
        </p>

        <p>
          You can track the execution of MUM wizard actions reported on this
          page by visiting the $status on this ASI server.
        </p>

        <p>
          Please note that a deleted action will not be performed only on the
          machines where the action has not yet been taken. You can find this
          information by visiting the $status on this ASI server.
        </p>

        <p>
          When checking the information on the $status, it is very
          important that you take into account any time lag in the
          communication between your sites and the ASI server (driven by the
          Scrip 177 (Scrip Configuration Changes) schedule). You should check
          on the status of an action three to six hours after an action's
          scheduled execution time making sure that there are no connectivity
          issues between the sites and the ASI server.
        </p>

        <p>
          In order to undo or reverse a wizard action already taken please
          refer to the information on the main $wizard.
        </p>

        <p>
          At present, this page has no purge function.
        </p>

HERE;
    }

    function wiz_stat(&$env,$db)
    {
        wiz_stat_blurb($env);
        echo again($env);
        $set  = wiz_names();
        $stra = $set['stra'];
        $remv = $set['remv'];
        $appr = $set['appr'];
        $decl = $set['decl'];
        $crit = $set['crit'];
        $beta = $set['beta'];
        $prop = $set['prop'];

        unset($set['stat']);

        if ($set)
        {
            echo "<h3>Status for:</h3>\n";
            echo "<ol>\n";
            reset($set);
            foreach ($set as $tag => $name)
            {
                $text = "#$tag";
                $jump = marklink($text,$name);
                echo "<li>$jump</li>\n";
            }
            echo "</ol>\n";
        }
        echo <<< HERE

        <p>
          In each status section, wizard actions are arranged in
          tables with each row corresponding to an action.
        </p>

        <p>
          Clicking on a parameter (e.g. machines, updates, schedule or
          notification) in the entry for an action will open a new page
          where you can change that parameter.
        </p>

        <p>
          Clicking on a column header sorts the entries in a table by
          the values in that column, in descending order (one click),
          or ascending order (two clicks).
        </p>

HERE;
        section($env,$set,'stra');
        stat_stra($env,$db);
        section($env,$set,'remv');
        stat_remv($env,$db);
        section($env,$set,'appr');
        stat_appr($env,$db);
        section($env,$set,'decl');
        stat_decl($env,$db);
        section($env,$set,'crit');
        stat_crit($env,$db);
        section($env,$set,'beta');
        stat_beta($env,$db);
        section($env,$set,'prop');
        stat_prop($env,$db);
        echo again($env);
    }


    function wizard_menu(&$env,$db)
    {
        $self = $env['self'];
        $priv = $env['priv'];
        if ($priv)
        {
            echo again($env);
        }

        $name = wiz_names();
        $stra = $name['stra'];
        $remv = $name['remv'];
        $appr = $name['appr'];
        $decl = $name['decl'];
        $crit = $name['crit'];
        $beta = $name['beta'];
        $prop = $name['prop'];
        $stat = $name['stat'];
        $text = 'See wizard actions taken to date';
        $msut = 'enable the Microsoft Update management Scrip (#237)';
        $mums = html_link("wu-stats.php",'Microsoft Update Management status page');
        $link = html_link("$self?act=wsts",$text);
        $msul = html_link('../config/patch.php',$msut);

        echo <<< MENU

        <p>
        <b>
          Important: Before using any of the Microsoft update management
          wizards, you need to $msul at all the sites where you want to
          use the <i>Microsoft Update Management</i> facility.
        </b>
        </p>

        <p>
          The "$link" link below will take you to the Microsoft update
          wizard action status page where you can review and manage
          wizard actions taken to date.
        </p>

        <p>
           You can check the status of Microsoft software updates on systems at
           your sites by visiting the $mums.
        </p>

        <p>
          The software update management wizards listed on this page should
          help you perform software update management tasks easily and fast.
        </p>

        <p>
          Actions performed with the <i>$appr</i>,
          <i>$decl</i>, <i>$remv</i>, and,
          <i>$crit</i> wizards apply to specific
          updates and have to be repeated as new
          updates are published.
        </p>

        <p>
          Actions performed with the <i>$stra</i>, <i>$beta</i>,
          <i>$prop</i> affect the software update management
          configuration and persist until you run these wizards
          again to change the configuration. Any changes made using
          these wizards will be applied to all software update
          operations that have not yet taken place, as soon as the
          ASI client retrieves the changes.
        </p>

        <p>
          Please note that there is no way to <i>undo</i> an action performed
          with a wizard. This is why the following wizards are mutually exclusive:
        </p>

        <ul>
          <li><p><i>$appr</i></p></li>
          <li><p><i>$decl</i></p></li>
          <li><p><i>$remv</i></p></li>
          <li><p><i>$crit</i></p></li>
        </ul>

        <p>
          Taking an action on an update with one
          of the wizards listed above supersedes
          any other action you may have taken on
          the same update with another of these
          wizards.
        </p>

        <p>
          For example, you could first approve
          update <b>ABC</b> with the <i>$appr</i>
          wizard, then you could decline it using
          the <i>$decl</i> wizard, and then you
          could use the <i>$crit</i> wizard to
          install it immediately.  <b>Assuming
          that you run the wizards BEFORE these
          actions are communicated to the systems
          in the field</b>, the action taken with
          the <i>$crit</i> wizard is the one that
          will be applied to update <b>ABC</b>.
        </p>

        <p>
          <b>
            Please keep in mind that any action taken
            with a wizard can be easily reversed using
            another wizard as long as the action you
            want to reverse has not been downloaded
            by ASI clients on systems in the field.

            If an action specified with a wizard is
            actually taken on systems in the field,
            it's more difficult to reverse it, and
            in some cases it can't be.
          </b>

          In the example above, if the initial
          action specified with the <i>$appr</i>
          wizard is actually taken on systems
          in the field, you can use the <i>$remv</i>
          to uninstall update <b>ABC</b>.
        </p>

        <p>
          What do you want to do?
        </p>

        <ul>

           <li>$link</li>

           <p>

           <li>Use a wizard:

MENU;

        $d = array( );
        $c = array( );
        $act = "$self?act";

                $cst = customURL(constPageEntryWUconfg);
        $c[] = "$act=wmth&$cst";
        $d[] = $stra;

                $c[] = "$act=wapp&dtc=-1&int=-1";
        $d[] = $appr;

                $c[] = "$act=wdec&dtc=-1&int=-1";
        $d[] = $decl;

                $c[] = "$act=wcrt&dtc=-1&int=-1";
        $d[] = $crit;

                $c[] = "$act=wrem&dtc=-1&int=-1";
        $d[] = $remv;

                $c[] = "$act=wtst";
        $d[] = $beta;

                $c[] = "$act=wprp";
        $d[] = $prop;

        simple_list($c,$d);
        echo "\n</li></ul>\n";

        if ($priv)
        {
            echo again($env);
        }
    }



    function build_group_env()
    {
        $env = array();
        $env['sub']  = UTIL_GetStoredString('sub','');          $env['url']  = UTIL_GetStoredString('url','');          $env['txt']  = UTIL_GetStoredString('txt','');          $env['ord']  = UTIL_GetStoredInteger('ord',0);          $env['man']  = UTIL_GetStoredInteger('man',0);          $env['new']  = UTIL_GetStoredInteger('new',0);          $env['src']  = UTIL_GetStoredInteger('src',0);          $env['ins']  = UTIL_GetStoredInteger('ins',0);          $env['glob'] = UTIL_GetStoredInteger('glob',0);         $env['type'] = UTIL_GetStoredInteger('type',0);         $env['rusr'] = UTIL_GetStoredInteger('rusr',0);         $env['pshd'] = UTIL_GetStoredInteger('pshd',0);
        $env['sdel'] = UTIL_GetStoredInteger('sdel',0);         $env['ssec'] = UTIL_GetStoredInteger('ssec',0);         $env['smin'] = UTIL_GetStoredInteger('smin',0);         $env['shor'] = UTIL_GetStoredInteger('shor',0);         $env['sday'] = UTIL_GetStoredInteger('sday',0);         $env['smon'] = UTIL_GetStoredInteger('smon',0);         $env['swek'] = UTIL_GetStoredInteger('swek',0);         $env['srnd'] = UTIL_GetStoredInteger('srnd',0);         $env['styp'] = UTIL_GetStoredInteger('styp',0);
        $env['nmin'] = UTIL_GetStoredInteger('nmin',0);         $env['nhor'] = UTIL_GetStoredInteger('nhor',0);         $env['nday'] = UTIL_GetStoredInteger('nday',0);         $env['nmon'] = UTIL_GetStoredInteger('nmon',0);         $env['nwek'] = UTIL_GetStoredInteger('nwek',0);         $env['nrnd'] = UTIL_GetStoredInteger('nrnd',0);         $env['ntyp'] = UTIL_GetStoredInteger('ntyp',0);         $env['nfal'] = UTIL_GetStoredInteger('nfal',0);         $env['nadv'] = UTIL_GetStoredInteger('nadv',0);         $env['nsch'] = UTIL_GetStoredInteger('nsch',0);         $env['mins'] = UTIL_GetStoredInteger('mins',0);         $env['iday'] = UTIL_GetStoredInteger('iday',0);         $env['prop'] = UTIL_GetStoredInteger('prop',0);         $env['hour'] = UTIL_GetStoredInteger('hour',3);         $env['updc'] = UTIL_GetStoredInteger('updc',0);         $env['cday'] = UTIL_GetStoredInteger('cday',0);         $env['rest'] = UTIL_GetStoredInteger('rest',0);         $env['chan'] = UTIL_GetStoredInteger('chan',0);         $env['chas'] = UTIL_GetStoredInteger('chas',0);         $env['scop'] = UTIL_GetStoredInteger('scop',constScopNone);
        $env['ctl']  = UTIL_GetStoredInteger('ctl', 0);

        $env['siz']  = UTIL_GetStoredInteger('siz', -1);          $env['age']  = UTIL_GetStoredInteger('age', -1);          $env['cmp']  = UTIL_GetStoredInteger('cmp', -1);          $env['plt']  = UTIL_GetStoredInteger('plt', -1);          $env['pri']  = UTIL_GetStoredInteger('pri', -1);          $env['can']  = UTIL_GetStoredInteger('can', -1);          $env['int']  = UTIL_GetStoredInteger('int', -1);          $env['dtc']  = UTIL_GetStoredInteger('dtc', -1);
        $env['text'] = UTIL_GetStoredString('text','');         $env['name'] = UTIL_GetStoredString('name','');
        return $env;
    }



    function createGroupPageContent($page)
    {
        switch($page)
        {
            case constPageEntryScrpConf : ;
            case constPageEntryAsset    : ;
            case constPageEntryNotfy    : ;
            case constPageEntryReports  : ;
            case constPageEntryGroup    : return 0; break;
            case constPageEntryWUconfg  : return 1; break;
            default : return 1;
        }
    }



    function GRPW_return_group_from_mcatid($tid, $qu, $db)
    {
        $sql = "select mgroupid, name, username\n"
             . " from ".$GLOBALS['PREFIX']."core.MachineGroups\n"
             . " left join ".$GLOBALS['PREFIX']."core.MachineCategories on (\n"
             .$GLOBALS['PREFIX']."core.MachineGroups.mcatuniq=".$GLOBALS['PREFIX']."core.MachineCategories.mcatuniq)\n"
             . " where mcatid = $tid\n"
             . " and (username = '$qu'\n"
             . " or global = 1)\n"
             . " order by name";
        return find_many($sql,$db);
    }


    function wiz_mgrp_select(&$env,&$mcat,$db)
    {
        debug_note("wiz_mgrp_select()");
        $act    = $env['act'];
        $n_id   = $env['notification_id'];
        $n_act  = $env['notification_act'];
        $r_id   = $env['report_id'];
        $r_act  = $env['report_act'];
        $a_id   = $env['asset_id'];
        $a_act  = $env['asset_act'];
        $custom = $env['custom'];
        $auth = $env['auth'];
        $tid  = $mcat['mcatid'];
        $scop = constScopGroup;
        $add  = constButtonAdd;
        $in   = indent(5);
        $txt  = button($add);
        $qu   = safe_addslashes($auth);
        $gids = array( );
        $list = array( );
        $mcatidList = array( );
        $own  = array( );
        $enu_text = '';
        $use_text = '';
        $rmv_text = '';
        $edt_text = '';
        $can      = '';
        $return   = '';

        $set = GRPW_return_group_from_mcatid($tid, $qu, $db);
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $gid = $row['mgroupid'];
                if (legal_mgrp($env,$gid,$db))
                {
                    $own[$gid]  = $row['username'];
                    $gids[$gid] = $row['name'];
                    $list[$gid] = array();
                }
            }
        }
        $sql = "select C.site, C.host, A.mcatid, G.mgroupid\n"
             . " from ".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
             . " ".$GLOBALS['PREFIX']."core.MachineCategories as A\n"
             . " where A.mcatid = $tid\n"
             . " and G.mcatuniq = A.mcatuniq\n"
             . " and G.mgroupuniq = M.mgroupuniq\n"
             . " and M.censusuniq = C.censusuniq\n"
             . " and U.customer = C.site\n"
             . " and U.username = '$qu'\n"
             . " and G.username = '$qu'\n"
             . " order by site, host";
        $set = find_many($sql,$db);
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $mcatid = $row['mcatid'];
                $gid    = $row['mgroupid'];
                $host   = $row['host'];
                $site   = $row['site'];
                $list[$gid][] = "<b>$host</b> at <b>$site</b>";
                $mcatidList[$gid]['mcatid'] = $mcatid;
            }
        }


        switch($env['custom'])
        {
	    case constDashStatus_SelectMachineGroup :
	        $can = wiz_dash_finish( );
	    break;

            case constPageEntryAsset : $return = wiz_asset_finish();
            break;

            case constPageEntryNotfy : $return = wiz_notfy_finish();
            break;

            case constPageEntryReports : $return = wiz_report_finish();
            break;

            default :
                $return = '';
                $can    = cancel_link($custom, $env);
            break;
        }


        switch($env['custom'])
        {
                case constPageEntryAsset   : ;
                case constPageEntryTools   : ;
                case constPageEntrySites   : ;
                case constPageEntryNotfy   : ;
                case constPageEntryReports : ;
                 $rmv_text = <<< HERE
                <p>
                  If you would like to remove a group, click
                  on the <b>remove</b> link next to the group
                  name.
                </p>
HERE;

                $edt_text = <<< HERE
                <p>
                  If you would like to edit a group, click on
                  the <b>edit</b> link
                  next to the group name.
                </p>
HERE;
            break;

            case constPageEntryScrpConf ;
                $use_text  = <<< HERE
        <p>
          Select a group of machines on which
          you want to configure a Scrip.
        </p>

                <p>
                  Click on the <b>use</b> link next to
                  the name of the group you want to select.
                </p>

HERE;
                $enu_text = <<< HERE
                <p>
                  If you would like to edit a group before
                  using it, click on the <b>edit and use</b>
                  link next to the group name.
                </p>
HERE;
            break;

            case constDashStatus_SelectMachineGroup ;
            case constPageEntryWUconfg ;
            default :
                $use_text  = <<< HERE
		<p>
		  Select a group of machines on which
		  to apply the action taken with this wizard.
		</p>

                <p>
                  Click on the <b>use</b> link next to
                  the name of the group you want to select.
                </p>
HERE;

                $enu_text = <<< HERE
                <p>
                  If you would like to edit a group before
                  using it, click on the <b>edit and use</b>
                  link next to the group name.
                </p>
HERE;
        }

        $new  = para("${in}${txt}${in}${can}");

        $again = GRPS_display_again($custom)? again($env) : '';
        echo $again;
        echo post_self('myform');
        echo hidden('act',$act);
        echo hidden('frm',$env['act']);
        echo hidden('ctl',0);
        echo hidden('pcn','wiz');
        echo hidden('tid',$tid);
        echo hidden('gid', -1);
        echo hidden('scop',$scop);
        echo hidden('custom', $custom);
        echo hidden('notification_id', $n_id);
        echo hidden('notification_act',$n_act);
        echo hidden('report_id',       $r_id);
        echo hidden('report_act',      $r_act);
        echo hidden('asset_id',        $a_id);
        echo hidden('asset_act',       $a_act);
        if(@$env['level'])
        {
            echo hidden('level',        $env['level']);
        }
        if(@$env['isparent'])
        {
            echo hidden('isparent', $env['isparent']);
        }
        preserve($env,'int,dtc');
        echo <<< SPAZ

          $use_text

          <p>
          If you want to see which machines are included
          in a group, click on the group name itself.
          </p>

          $enu_text

          $edt_text

          <p>
          If you want to create a completely new group
          to use, click on the <b>$add</b> button.
          </p>

          $rmv_text

          $new

SPAZ;

        if (($gids) && ($list))
        {
            $pre_n= preserve_notification_state($n_id,$n_act);
            $pre_r= preserve_report_state($r_id,$r_act);
            $pre_a= preserve_asset_state($a_id,$a_act);
            $self = $env['self'];
            $int  = $env['int'];
            $dtc  = $env['dtc'];
            $cmd  = "$self?act=$act&scop=$scop&dtc=$dtc&int=$int"
                  . "&custom=$custom&$pre_n&$pre_r&$pre_a";
            $eref = "$cmd&tid=$tid&dgid";
            $rref = "$cmd&sub=remv&tid=$tid&dgid";
            $uref = "$cmd&gid";
            $dref = "$self?sub=$act&act=gdet&gid";
	    $sref = "../config/syst.php?act=" . constDashStatus_SelectDisplay;
            $head = explode('|','Action|Group|Machines');
            $cols = safe_count($head);
            $rows = safe_count($gids);
            $mesg = plural($rows,'group');
            $text = "Select Group &nbsp; ($mesg)";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);
            reset($gids);
            foreach ($gids as $id => $grp)
            {
                $mcatid = @ $mcatidList[$id]['mcatid'];
                $show_members = ($mcatid)? true : false;

                $temp  = $list[$id];
                $text  = ($temp)? join('<br>',$temp) : '<br>';
                $detl  = html_page("$dref=$id&custom=$custom&$pre_n",$grp);

                switch ($custom)
                {
                    case constPageEntryAsset   :
                    case constPageEntryNotfy   :
                    case constPageEntrySites   :
                    case constPageEntryTools   : $acts = '';
                    break;

                    case constPageEntryReports : $acts = '';
                    break;

                    case constPageEntryScrpConf :
                        $thisRef = "$uref=$id&act=machine_selected"
                            . "&mgroupid=$id";                          if(@$env['level'])
                        {
                            $thisRef .= "&level=" . $env['level'];
                        }
                        if(@$env['isparent'])
                        {
                            $thisRef .= '&isparent=' . $env['isparent'];
                        }
                        $acts = html_link($thisRef,'[use]');
                    break;

            case constDashStatus_SelectMachineGroup :
                $acts = html_link("$sref&mgroupid=$id", '[use]');
            break;

                    case constPageEntryWUconfg :
                    default                    :
                         $acts = html_link("$uref=$id",'[use]');
                    break;
                }


                if ($auth == $own[$id])
                {
                    switch($custom)
                    {
                        case constPageEntryAsset   :
                        case constPageEntryNotfy   :
                        case constPageEntryReports :
                        case constPageEntrySites   :
                            $acts .= html_link("$eref=$id",'[edit]<br>');
                            $acts .= html_link("$rref=$id",'[remove]<br>');
                        break;

                        case constPageEntryTools   :
                            $acts .= html_link("$eref=$id",'[edit]<br>');
                            $acts .= html_link("$rref=$id",'[remove]<br>');
                            if ($show_members)
                            {
                                $mem   = GRPW_GetGroupScripLink($id);
                                $acts .= html_link($mem,
                                    '[configure Scrips]<br>');
                            }
                        break;

                case constDashStatus_SelectMachineGroup :
                        case constPageEntryScrpConf :
                        case constPageEntryWUconfg  :
                        default                     :
                            $thisRef = "$eref=$id";
                            if(@$env['level'])
                            {
                                $thisRef .= "&level=" . $env['level'];
                            }
                            if(@$env['isparent'])
                            {
                                $thisRef .= "&isparent=" . $env['isparent'];
                            }
                            $acts .= html_link($thisRef,
                                               '<br>[edit and use]');
                            $acts .= '';
                        break;
                    }
                }
                if ($acts == '')
                {
                    $acts = '&nbsp';
                }

                $args = array($acts,$detl,$text);
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            echo para('No machine groups yet.');
        }

        echo $new;
        echo $return;
        echo form_footer();
        $again = GRPS_display_again($custom)? again($env) : '';
        echo $again;
    }



    function wiz_make_mcat($text,$db)
    {
        $mcat = array( );
        if ($text)
        {
            $mcat = find_mcat_name($text,$db);
        }
        if (($text) && (!$mcat))
        {
            $site = find_mcat_name(constCatSite,$db);
            $over = server_int('override_sites',1,$db);
            if ($site)
            {
                $pre = $site['precedence'];
                $pre = ($over)? $pre + 1 : $pre;
                $name = unique_mcat($text,0,$db);
                push_mcat($pre,$db);
                build_mcat($name,$pre,$db);
                $mcat = find_mcat_name($name,$db);
                if ($mcat)
                {
                    check_change($text,$name);
                }
            }
        }
        return $mcat;
    }


    function wiz_meth_prep($env, $db)
    {

        switch($env['custom'])
        {
	    case constDashStatus_SelectMachineGroup : ;
            case constPageEntryAsset   : ;
            case constPageEntrySites   : ;
            case constPageEntryTools   : ;
            case constPageEntryNotfy   : ;
            case constPageEntryReports : ;
                $env['act'] = 'wmth';
                break;


        }
        $mcatid = wiz_make_mcat(constMcSCOP, $db);
        wiz_mgrp_select($env, $mcatid, $db);
    }



    function GRPW_GetGroupScripLink($mgroupid)
    {
        return "scrpconf.php?act=wapp&scop=5&dtc=0&int=0&custom=8"
            . "&notification_id=0&notification_act=&report_id=0"
            . "&report_act=&asset_id=0&asset_act=&gid=$mgroupid"
            . "&act=machine_selected&mgroupid=$mgroupid&scop=2"
            . "&level=1";
    }



    function controlGroupState($act, $env, $db)
    {
        if(@$env['delgroup']!=0)
        {

            $env['gid'] = $env['delgroup'];
            debug_del_mgrp($env,0,$db);
        }

        switch ($act)
        {
            case 'site': config_site($env,$db);           break;
            case 'host': wconfig_host($env,$db);          break;
            case 'hpid': pconfig_host($env,$db);          break;
            case 'spid': pconfig_site($env,$db);          break;
            case 'menu': config_menu($env,$db);           break;
            case 'awid': add_wcfg($env,$db);              break;
            case 'apid': add_pcfg($env,$db);              break;
            case 'cwid': create_wcfg($env,$db);           break;
            case 'cpid': create_pcfg($env,$db);           break;
            case 'ewid': edit_wconfig($env,$db);          break;
            case 'epid': edit_pconfig($env,$db);          break;
            case 'fwid': check_wconfig($env,$db);         break;
            case 'fpid': check_pconfig($env,$db);         break;
            case 'lpid': list_pcfg($env,$db);             break;
            case 'lwid': list_wcfg($env,$db);             break;
            case 'pwic': ;
            case 'pwid': del_wcfg_conf($env,$db);         break;
            case 'ppic': ;
            case 'ppid': del_pcfg_conf($env,$db);         break;
            case 'dwic': ;
            case 'dwid': del_wcfg_act($env,$db);          break;
            case 'dpic': ;
            case 'dpid': del_pcfg_act($env,$db);          break;
            case 'iwid': insert_wconfig($env,$db);        break;
            case 'ipid': insert_pconfig($env,$db);        break;
            case 'uwid': update_wconfig($env,$db);        break;
            case 'upid': update_pconfig($env,$db);        break;
            case 'cpwa': copy_wcfg($env,$db);             break;
            case 'cpwb': copy_wcfg_cnf($env,$db);         break;
            case 'cpwc': copy_wcfg_act($env,$db);         break;
            case 'cppa': copy_pcfg($env,$db);             break;
            case 'cppb': copy_pcfg_cnf($env,$db);         break;
            case 'cppc': copy_pcfg_act($env,$db);         break;
            case 'cata': add_pcat_form($env,$db);         break;
            case 'catb': add_pcat_conf($env,$db);         break;
            case 'catc': add_pcat_act($env,$db);          break;
            case 'pgrp': show_cat_pgrp($env,$db);         break;
            case 'pcat': manage_pcat($env,$db);           break;
            case 'pexp': expand_pcat($env,$db);           break;
            case 'pedn': pcat_down($env,$db);             break;
            case 'pcdn': pcat_down($env,$db);             break;
            case 'pcup': pcat_up($env,$db);               break;
            case 'peup': pcat_up($env,$db);               break;
            case 'ptch': patch_mid($env,$db);             break;
            case 'grpa': add_pgrp_form($env,$db);         break;
            case 'grpb': add_pgrp_conf($env,$db);         break;
            case 'grpc': add_pgrp_act($env,$db);          break;
            case 'dpga': del_pgrp_conf($env,$db);         break;
            case 'dpgb': del_pgrp_act($env,$db);          break;
            case 'dpca': del_pcat_conf($env,$db);         break;
            case 'dpcb': del_pcat_act($env,$db);          break;
            case 'cpga': copy_pgrp_form($env,$db);        break;
            case 'cpgb': copy_pgrp_act($env,$db);         break;
            case 'epgf': edit_pgrp_form($env,$db);        break;
            case 'epgc': edit_pgrp_conf($env,$db);        break;
            case 'epgs': edit_pgrp_search($env,$db);      break;
            case 'epgm': edit_pgrp_man($env,$db);         break;
            case 'epgb': edit_pgrp_box($env,$db);         break;
            case 'epge': edit_pgrp_expr($env,$db);        break;
            case 'epgx': edit_pgrp_exec($env,$db);        break;
            case 'epgd': edit_pgrp_done($env,$db);        break;
            case 'epgp': edit_pgrp_post($env,$db);        break;
            case 'upga': calc_pgrp_conf($env,$db);        break;
            case 'upgs': calc_pgrp_search($env,$db);      break;
            case 'upge': calc_pgrp_expr($env,$db);        break;
            case 'vpga': view_pgrp($env,$db);             break;
            case 'wiz' : wizard_menu($env,$db);           break;
            case 'wrst': wiz_rst_form($env,$db);          break;
            case 'wdef': wiz_rst_act($env,$db);           break;
            case 'wmgs': wiz_meth_prep($env,$db);         break;
            case 'wmth': wiz_meth_disp($env,$db);         break;
            case 'wprp': wiz_prop_disp($env,$db);         break;
            case 'prop': wiz_prop_form($env,$db);         break;
            case 'prpa': wiz_prop_act($env,$db);          break;
            case 'wtst': wiz_beta_enab($env,$db);         break;
            case 'wbdo': wiz_beta_undo($env,$db);         break;
            case 'wbac': wiz_beta_act($env,$db);          break;
            case 'wbfm': wiz_beta_form($env,$db);         break;
            case 'wbet': wiz_beta_disp($env,$db);         break;
            case 'wdec': wiz_decl_disp($env,$db);         break;
            case 'wrem': wiz_rem_disp($env,$db);          break;
            case 'wrss': wiz_rem_shed($env,$db);          break;
            case 'wapp': wiz_appr_disp($env,$db);         break;
            case 'wrsh': wiz_appr_sched($env,$db);        break;
            case 'wcrt': wiz_crit_disp($env,$db);         break;
            case 'wcrn': wiz_crit_fin($env,$db);          break;
            case 'wemg': wiz_edit_mgrp($env,$db);         break;
            case 'wsts': wiz_stat($env,$db);              break;
            case 'help': wiz_help($env,$db);              break;
            case 'meth': meth_form($env,$db);             break;
            case 'mset': meth_set($env,$db);              break;
            case 'shed': edit_shed($env,$db);             break;
            case 'sent': save_shed($env,$db);             break;
            case 'qqqa': quad_appr($env,$db);             break;
            case 'qqqr': quad_remv($env,$db);             break;
            case 'qqqd': quad_decl($env,$db);             break;
            case 'qqqc': quad_crit($env,$db);             break;
            case 'pmth': page_meth($env,$db);             break;
            case 'papp': page_appr($env,$db);             break;
            case 'pdec': page_decl($env,$db);             break;
            case 'pcrt': page_crit($env,$db);             break;
            case 'prem': page_remv($env,$db);             break;
            case 'pprp': page_prop($env,$db);             break;
            case 'init': patch_rebuild($env,$db);         break;
            case 'gdet': mgrp_details($env,$db);          break;
            case 'dbug': debug_menu($env,$db);            break;
            case 'twid': test_wconfig($env,$db);          break;
            case 'tpid': test_pconfig($env,$db);          break;
            case 'dmap': debug_pmap($env,$db);            break;
            case 'dcfg': debug_pcfg($env,$db);            break;
            case 'dgrp': debug_pgrp($env,$db);            break;
            case 'dmpg': debug_mgrp($env,$db);            break;
            case 'dwfg': debug_wcfg($env,$db);            break;
            case 'dclc': debug_calc($env,$db);            break;
            case 'dhid': debug_host($env,$db);            break;
            case 'dbet': debug_beta($env,$db);            break;
            case 'dclr': clear_beta($env,$db);            break;
            case 'ddmp': debug_del_mmap($env,$db);        break;
            case 'ddpc': debug_del_pcfg($env,$db);        break;
            case 'dpgp': debug_del_pgrp($env,$db);        break;
            case 'ddmg': debug_del_mgrp($env,1,$db);        break;
            case 'sane': debug_sanity($env,$db);          break;
            case 'dinp': invalid_pcfg($env,$db);          break;
            case 'dinw': invalid_wcfg($env,$db);          break;
            case 'dvlp': validate_pcache($env,$db);       break;
            case 'xxxx': reset_patch($env,$db);           break;
            case 'invd': invalid_action($env,$db);        break;
            default    : wizard_menu($env,$db);           break;
        }
    }




    function user_done(&$env,$scop,$var,$valu,$source,$now,$db)
    {
        $user = constCatUser;
        $auth = $env['auth'];
        $admn = $env['admn'];
        $grp  = ($admn)? constCatAll : "$user:$auth";
        $set  = load_var_info($grp,$var,$scop,$db);
        if ($set)
        {
            group_process($env,$set,$scop,$var,$valu,$source,$db);
        }
        else
        {
            many_site_done($env,$scop,$var,$valu,$source,$db);
        }
    }


    function load_var_info($grp,$var,$scop,$db)
    {
        $set = array();
        if (($grp) && ($var) && ($scop))
        {
            debug_note("find $var:$scop for $grp");
            $qv  = safe_addslashes($var);
            $qg  = safe_addslashes($grp);
            $sql = "select H.host, H.site, R.censusid,\n"
                 . " G.mgroupid, X.*, V.varid from\n"
                 . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
                 . " ".$GLOBALS['PREFIX']."core.VarVersions as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.VarValues as Z,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V,\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as H,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M\n"
                 . " where G.name = '$qg'\n"
                 . " and V.name = '$qv'\n"
                 . " and V.scop = $scop\n"
                 . " and R.vers = X.vers\n"
                 . " and V.varuniq = X.varuniq\n"
                 . " and V.varuniq = Z.varuniq\n"
                 . " and M.censusuniq = H.censusuniq\n"
                 . " and H.id = R.censusid\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and M.mgroupuniq = Z.mgroupuniq\n"
                 . " group by R.censusid\n"
                 . " order by R.censusid";
            $set = find_many($sql,$db);
        }
        return $set;
    }


    function group_process(&$env,&$set,$scop,$var,$valu,$source,$db)
    {
        $old  = array( );
        $new  = array( );
        $gset = array( );
        $host = $env['serv'];
        $admn = $env['admn'];
        $now  = $env['now'];
        $gid  = $set[0]['mgroupid'];
        $vid  = $set[0]['varid'];
        $tmp  = find_settings($vid,$gid,$db);
        debug_note("update variable $vid in group $gid to $valu");
        $chng = update_value($vid,$gid,$now,$host,$valu,$source,$db);
        reset($set);
        reset($tmp);
        foreach ($set as $key => $row)
        {
            $hid  = $row['censusid'];
            $gset[$hid] = false;
        }
        foreach ($tmp as $key => $row)
        {
            $hid  = $row['censusid'];
            $gset[$hid] = true;
        }
        reset($set);
        foreach ($set as $key => $row)
        {
            $hid  = $row['censusid'];
            $gany = $row['grpany'];
            $gall = $row['grpall'];
            $gusr = $row['grpuser'];
            $site = $row['site'];
            $host = $row['host'];
            if (($gany) || ($gset[$hid]))
                $good = true;
            else
                $good = ($admn)? $gall : $gusr;
            if ($good)
            {
                debug_note("$host at $site ($hid) is new");
                $new[] = $hid;
            }
            else
            {
                debug_note("$host at $site ($hid) is old");
                $old[] = $hid;
            }
        }



        if ($old)
        {
            $txt = join(',',$old);
            $sql = "select U.* from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as H,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.VarValues as X,\n"
                 . " ".$GLOBALS['PREFIX']."core.Variables as V\n"
                 . " where H.id in ($txt)\n"
                 . " and X.varuniq = V.varuniq\n"
                 . " and V.varid = $vid\n"
                 . " and X.mgroupuniq = G.mgroupuniq\n"
                 . " and U.customer = G.name\n"
                 . " and U.customer = H.site\n"
                 . " and X.mgroupuniq = G.mgroupuniq\n"
                 . " group by U.customer\n"
                 . " order by U.customer";
            $set = find_many($sql,$db);
            walk_sites($env,$set,$scop,$var,$valu,$source,$db);
        }



        if ($new)
        {
            $gbl = constVarConfStateGlobal;
            reset($new);
            foreach ($new as $key => $hid)
            {
                $num = update_vmap($hid,$gid,$vid,$gbl,$now,$source,$db);
                if (($num) || ($chng))
                {
                    dirty_hid($hid,$db);
                    hid_revision($hid,$now,$db);
                }
            }
        }
    }


    function many_site_done(&$env,$scop,$var,$valu,$source,$db)
    {
        debug_note('doing this the old way');
        $qu  = safe_addslashes($env['auth']);
        $qn  = safe_addslashes($var);
        $op  = $scop;
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
        walk_sites($env,$set,$scop,$var,$valu,$source,$db);
    }


    function site_done($env,$ss,$scop,$var,$valu,$source,$db)
    {
        $set  = array();
        $sgrp = GCFG_find_site_mgrp($ss,$db);
        $msus = find_var($var,$scop,$db);
        $vid  = ($msus)? $msus['varid'] : 0;
        $now  = $env['now'];
        $host = $env['serv'];
        $sgid = ($sgrp)? $sgrp['mgroupid'] : 0;
        $last = time();
        if((isset($valu)) && ($vid) && ($sgid))
        {
            set_value($vid,$sgid,$host,$valu,$source,$now,$db);
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
                 . " and V.varid = $vid\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " and G.mgroupid != $sgid\n"
                 . " group by C.id";
            $set = find_many($sql,$db);
        }
        else
        {
            $stat = "val:$valu,v:$vid,sid:$sgid";
            debug_note("site_done failure ($stat)");
        }

        if (($set) && ($sgid) && ($vid))
        {
            debug_note("remove local overrides");
            $gbl = constVarConfStateGlobal;
            $lon = constVarConfStateLocalOnly;

            reset($set);
            foreach ($set as $key => $row)
            {
                $hid = $row['id'];
                update_vmap($hid,$sgid,$vid,$gbl,$last,$source,$db);
            }
        }
    }


    function walk_sites(&$env,&$set,$scop,$var,$valu,$source,$db)
    {
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $site = $row['customer'];
                debug_note("Configure <b>$site</b>.");
                site_done($env,$site,$scop,$var,$valu,$source,$db);
            }
        }
    }



    function GRPW_BuildTypeSelect(&$env, $wpgroupid, $db)
    {
        $ret = '';

        $begin = true;

        $set = GRPW_GetWizardCheckboxVals($env, $wpgroupid, $db);

        $ret .= '<table border="0">';
        foreach ($set as $key => $row)
        {

            if($begin)
            {
                $ret .= '<tr><td valign="top">';
            }
            else
            {
                $ret .= '<td valign="top">';
            }
            $text = $row['name'];

            if(strcmp($row['name'], constPatchTypeUpdateStr)==0)
            {
                $text = constWizTypeUpdateStr;
            }
            if(strcmp($row['name'], constPatchMandatoryUpdateStr)==0)
            {
                $text = constWizMandatoryUpdateStr;
            }
            $text = str_replace(' ', '&nbsp;', $text);

            if($row['disable'])
            {
                $ret .= '<input type="checkbox" name="'
                    . $row['chkname'] . ' value="1" disabled>';
                $ret .= '&nbsp;' . $text . '<i> (This option is disabled '
                    . 'because this type group is already used for this '
                    . 'machine group in another wizard.)</i>';
            }
            else
            {
                $ret .= checkbox_onclick($row['chkname'],
                    $row['found'],
                    "if(document." . constWizUpdateForm . "." . $row['chkname']
                    . ".value==1)"
                    . "{"
                    . "    document." . constWizUpdateForm . "."
                    . $row['chkname']
                    . ".value=0"
                    . "}"
                    . "else"
                    . "{"
                    . "    document." . constWizUpdateForm . "."
                    . $row['chkname']
                    . ".value=1"
                    . "}");
                $ret .= '&nbsp;' . $text;
            }

            if($begin)
            {
                $ret .= '</td>';
            }
            else
            {
                $ret .= '</td></tr>';
            }

            $begin = !$begin;
        }

        $ret .= '</table><br />';

        $ret .= indent(5);
        $ret .= click(constButtonOk, 'document.' . constWizUpdateForm . '.'
            . constWizClickOk . '.value=1;document.' . constWizUpdateForm
            . '.ctl.value=1;document.' . constWizUpdateForm . '.submit()');
        $ret .= indent(5);
        $ret .= cancel_link($env['custom']);

        return $ret;
    }



    function GRPW_ProcessSelection(&$env, $wpgroupid, $db)
    {
        $done = 0;
        $mgroupid = GRPW_DeriveMachineGroup($env, $db);


        $env['removal_cache'] = array();

        $set = GRPW_GetWizardCheckboxVals($env, $wpgroupid, $db);

        $boxes = array(
            constWizCheckboxUpdate,
            constWizCheckboxSP,
            constWizCheckboxRollup,
            constWizCheckboxSecurity,
            constWizCheckboxCritical,
            constWizCheckboxUpdateM,
            constWizCheckboxSPM,
            constWizCheckboxRollupM,
            constWizCheckboxSecurityM,
            constWizCheckboxCriticalM);
        foreach ($boxes as $key => $row)
        {
            if(($env[$row]) || ($env[$row]!=$set[$row]['found']))
            {
                GRPW_ProcessAssocChange($env, $wpgroupid, $row, $db);
            }
        }

        foreach ($env['removal_cache'] as $key => $sql)
        {
            redcommand($sql, $db);
        }
        unset($env['removal_cache']);

        return $done;
    }



    function GRPW_LocatePConfig($env, $wpgroupid, $db)
    {
        $set = array();

        $mgroupid = GRPW_DeriveMachineGroup($env, $db);

        $sql = "SELECT pgroupid, pconfigid, wpgroupid FROM PatchConfig WHERE "
            . "wpgroupid = " . $wpgroupid . " AND mgroupid=" . $mgroupid;
        $set = find_many($sql, $db);

        return $set;
    }



    function GRPW_DeriveMachineGroup($env, $db)
    {
        $mgroupid = $env['gid'];
        if($mgroupid==0)
        {
            $mgrp = find_scop_mgrp($env, $db);
            $mgroupid = $mgrp['mgroupid'];
        }

        return $mgroupid;
    }



    function GRPW_PrepareWizardUpdateForm($env, $wpgroupid, $db)
    {
        echo post_self(constWizUpdateForm);
        echo hidden(constWizClickOk, 0);

        $set = GRPW_GetWizardCheckboxVals($env, $wpgroupid, $db);
        foreach ($set as $key => $row)
        {
            echo hidden($row['chkname'], $row['found']);
        }
    }



    function GRPW_GetWizardCheckboxVals($env, $wpgroupid, $db)
    {
        $retSet = array();

        $sql = 'SELECT pcategoryid FROM PatchCategories WHERE category=\''
            . constPatchCategoryMandatory . '\'';
        $row = find_one($sql, $db);
        if(!($row))
        {
                        return $retSet;
        }

        $sql = 'SELECT pgroupid, name FROM PatchGroups WHERE style = '
            . constStyleType . ' OR (style = ' . constStyleDirectSearch
            . ' AND pcategoryid = ' . $row['pcategoryid'] . ')';
        $set = find_many($sql, $db);

        $set2 = GRPW_LocatePConfig($env, $wpgroupid, $db);

        $mgroupid = GRPW_DeriveMachineGroup($env, $db);

        $boxes = array(
            constPatchTypeCriticalStr => constWizCheckboxCritical,
            constPatchMandatoryCriticalStr => constWizCheckboxCriticalM,
            constPatchTypeSecurityStr => constWizCheckboxSecurity,
            constPatchMandatorySecurityStr => constWizCheckboxSecurityM,
            constPatchTypeRollupStr => constWizCheckboxRollup,
            constPatchMandatoryRollupStr => constWizCheckboxRollupM,
            constPatchTypeServicePackStr => constWizCheckboxSP,
            constPatchMandatoryServicePackStr => constWizCheckboxSPM,
            constPatchTypeUpdateStr => constWizCheckboxUpdate,
            constPatchMandatoryUpdateStr => constWizCheckboxUpdateM);


        foreach ($boxes as $key => $row)
        {
            $retSet[$row] = array();
        }

        foreach ($set as $key => $row)
        {

            $found = 0;
            reset($set2);

            foreach ($set2 as $key2 => $row2)
            {
                if($row2['pgroupid']==$row['pgroupid'])
                {
                    $found = 1;
                    break;
                }
            }

            $chkname = $boxes[$row['name']];
            $thisItem = array();
            $thisItem['chkname'] = $chkname;
            $thisItem['name'] = $row['name'];
            $thisItem['found'] = $found;
            $thisItem['pgroupid'] = $row['pgroupid'];
            $thisItem['disable'] = 0;
            if($found)
            {
                $thisItem['pconfigid'] = $row2['pconfigid'];
            }
            else
            {

                $sql = 'SELECT 1 FROM PatchConfig WHERE mgroupid='
                    . "$mgroupid AND pgroupid=" . $row['pgroupid'] . " AND "
                    . "wpgroupid!=$wpgroupid";
                $set3 = find_many($sql, $db);
                if($set3)
                {
                    $thisItem['disable'] = 1;
                }
            }

            $retSet[$chkname] = $thisItem;
        }

        return $retSet;
    }



    function GRPW_ProcessAssocChange(&$env, $wpgroupid, $str, $db)
    {
        $configs = GRPW_LocatePConfig($env, $wpgroupid, $db);
        $num = safe_count($configs);

        $set = GRPW_GetWizardCheckboxVals($env, $wpgroupid, $db);
        if($env[$str])
        {
            $mgroupid = GRPW_DeriveMachineGroup($env, $db);
            $now = time();

            $sql ="REPLACE INTO PatchConfig (pgroupid, mgroupid, "
                . "installation, notifyadvance, notifyadvancetime, "
                . "scheddelay, schedminute, schedhour, schedday, "
                . "schedmonth, schedweek, schedrandom, schedtype, "
                . "notifydelay, notifyminute, notifyhour, notifyday, "
                . "notifymonth, notifyweek, notifyrandom, notifytype, "
                . "notifyfail, configtype, reminduser, preventshutdown, "
                . "notifyschedule, notifytext, lastupdate, wpgroupid) "
                . "SELECT " . $set[$str]['pgroupid']
                . ", mgroupid, installation, notifyadvance, "
                . "notifyadvancetime, scheddelay, schedminute, schedhour, "
                . "schedday, schedmonth, schedweek, schedrandom, "
                . "schedtype, notifydelay, notifyminute, notifyhour, "
                . "notifyday, notifymonth, notifyweek, notifyrandom, "
                . "notifytype, notifyfail, configtype, reminduser, "
                . "preventshutdown, notifyschedule, notifytext, "
                . "$now, wpgroupid FROM PatchConfig WHERE mgroupid="
                . $mgroupid . " AND wpgroupid=" . $wpgroupid . " LIMIT 1";
            redcommand($sql, $db);
        }
        else
        {
            $sql = "DELETE FROM PatchConfig WHERE pconfigid="
                . $set[$str]['pconfigid'];
            $env['removal_cache'][] = $sql;
            $num = $num - 1;
                    }

        $configs = GRPW_LocatePConfig($env, $wpgroupid, $db);
        $num2 = safe_count($configs);

        if($num!=$num2)
        {
            $text = ($env[$str] ? "Added " : "Removed ") . $set[$str]['name']
                . " type association for this wizard.<br>";
            echo $text;
            $sql = "SELECT name FROM PatchGroups WHERE pgroupid=$wpgroupid";
            $row = find_one($sql,$db);
            $name = '';
            if($row)
            {
                $name = $row['name'];
            }
            $detail = ($env[$str] ? "Added " : "Removed ") . $set[$str]['name']
                . " type association for the wizard $name.";
            $user = '';
            if(array_key_exists('username', $env))
            {
                $user = $env['username'];
            }
            $err = PHP_AUDT_LogLocalAudit(CUR, constMUMChangeLevel,
                constModuleMUM, constClassUser, constAuditGroupMUMChange,
                $user, $detail);
            if($err!=constAppNoErr)
            {
                            }
        }
    }



    function GRPW_WizardPagingText($wizard)
    {
        $lower = strtolower($wizard);


        if($wizard=='Critical')
        {
            echo "Please note that if you select $lower updates (i.e. click "
                . "in their \"$wizard\" check box), then go to another "
                . "\"$wizard Updates\" page (i.e. click on one of the "
                . "page links above or below the \"Select $wizard Updates\" "
                . "table), the $lower update selection you made on the page "
                . "you came from will be lost.";
        }
        else
        {
            echo "Please note that if you $lower updates (i.e. click in their "
                . "\"$wizard\" check box), then go to another \"$wizard "
                . "Updates\" page (i.e. click on one of the page links "
                . "above or below the \"Select Updates to $wizard\" table), "
                . "the $lower update selection you made on the page you came "
                . "from will be lost.";
        }
    }



    function GRPW_ChooseUpdatesText($wizard)
    {
        echo <<< BEFORETEXT
        <p>
          By default, in the table below updates are ordered by descending
          "Internal Priority".
        </p>
BEFORETEXT;

        if($wizard=='Approve')
        {
            echo <<< WAPP
            <p>
                Select the updates you want to install by checking the box
                to the left of their name in the table below.
            </p>
WAPP;

        }
        if($wizard=='Decline')
        {
            echo <<< WDEC
            <p>
                Please note that declining an update does not remove it
                from machines where it is already installed. If you want
                to remove an update from machines where it is already
                installed, you should use the <i>Remove updates</i>
                wizard.
            </p>

            <p>
                Select the updates you want to decline by checking the box
                to the left of their name in the table below.
            </p>
WDEC;

        }
        if($wizard=='Remove')
        {
            echo <<< WREM

            <p>
              Please note that not all updates can be removed, and even
              updates that can be removed may have an order dependency.
              You should retrieve the relevant Microsoft support knowledge
              base article to find out. If possible, you should also test
              the removal of an update on one machine before doing it
              on a large number of machines.
            </p>

            <p>
              Select the updates you want to remove by checking the box
              to the left of their name in the table below.
            </p>

WREM;

        }
        if($wizard=='Critical')
        {
            echo <<< WCRT

            <p>
              Select the updates you want to install immediately by
              checking the box to the left of their name in the table
              below.
            </p>

WCRT;

        }

        echo <<< COMMONTEXT

            <p>
              Selecting individual updates of a type you selected above (e.g.
              you select an update of type "Security" below after selecting
              "Security" type above), will have no adverse effect.
            </p>

            <p>
              When you finish selecting updates,
              click on the <b>OK</b> button.
            </p>

            <p>
              To see more information about a particular update, click
              on its name. Clicking on the <i>Status</i> link in the
              rightmost column will display in a new window the current
              status of the software update, whose <i>Status</i> link
              you clicked on, on the machines you selected.
            </p>

            <p>
              Clicking on the "analysis" link for an update will take you to
              the analysis page for that update opened in a new window.  There
              follow the on-screen instructions to determine the current
              installation status for that update.
            </p>

            <p>
              For each update listed below, the leftmost column,
              labeled <i>Current Status</i>, reports the last action
              taken with one of the Microsoft update wizards. For
              example, if you previously declined an update, the
              status reported will be <i>declined</i>.
            </p>

COMMONTEXT;

    }



    function GRPW_AuditMachineChange($env, $sql2, $newcfg, $db)
    {
        $changes = '';
        $row = find_one($sql2, $db);
        if($row)
        {
            foreach ($newcfg as $key => $value)
            {
                $changes .= PCFG_PrintChange($key, $row, $newcfg);
            }
        }

        if($changes)
        {
            $name = '';
            $sql = "SELECT name FROM ".$GLOBALS['PREFIX']."core.MachineGroups WHERE mgroupid="
                . $row['mgroupid'];
            $row2 = find_one($sql, $db);
            if($row2)
            {
                $name = $row2['name'];
            }
            $detail = "Updated the machine configuration for group $name:<br>"
                . $changes;
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
