<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Sep-05   BJS     Added l-grpw.php, factored most code
                    into l-grpw.php.
07-Dec-05   BJS     Added l-upar.php, l-upnt.php,
                    report/asset/notification_id/act.
20-Feb-06   BJS     Added l-syst.php
24-Feb-06   BTE     Removed check for census dirty.
09-Mar-06   AAM     Added warning text for "Machine Configurations" page, as
                    described by Alex.  (no bug number)
15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on
                    4.3 server.
11-Apr-06   BTE     Added include for l-gcfg.php.
17-Apr-06   BTE     Bug 3202: Group management server issues.
20-Sep-06   BTE     Bug 2826: Make MUM approve/decline wizards a little easier
                    to use (not so large).
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
21-Oct-06   BTE     Bug 3043: Track MUM configuration changes in the audit log.
24-Nov-06   AAM     Bug 3866: temporary fix; change page limit from 250 to 1000.
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.  Bug 3843:
                    MUM update selection pages need to be mix and match.

*/

    ob_start();
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-slav.php'  );
include_once ( '../lib/l-cmth.php'  );
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-ptch.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-date.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-drty.php'  );
include_once ( '../lib/l-gdrt.php'  );
include_once ( '../lib/l-pdrt.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-upnt.php'  );
include_once ( '../lib/l-upar.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-syst.php'  );
include_once ( '../lib/l-core.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( 'local.php' );


    function page_href(&$env,$page,$ord)
    {
        $self = $env['self'];
        $limt = $env['limt'];
        $priv = $env['priv'];
        $dbug = $env['debug'];

        $a    = array("$self?p=$page");
        $a[]  = "o=$ord";
        $a[]  = "l=$limt";

        if (($priv) && ($dbug))
        {
            $a[] = "debug=1";
        }

        $sessionID = session_id();
        $a[]  = "sessionid=$sessionID";

        return join('&',$a);
    }

   /*
    |  Main program
    */

    /* Session handling */
    $sessionID = get_string('sessionid', '');
    if($sessionID)
    {
        session_id($sessionID);
    }
    

    if(!($sessionID))
    {
        /* Do not preserve any existing session data if we choose not to
            use a session based on the input URL.  Note that this cannot be
            called before session_start. */
        UTIL_ClearSession();
    }

    $sessionID = session_id();

    $now  = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();
    $nav  = patch_navigate($comp);
    $act  = UTIL_GetStoredString('act','wiz');
    $frm  = UTIL_GetStoredString('frm','');
    $post = UTIL_GetStoredString('button','');
    if($post != constButtonOk)
    {
        $didclick = UTIL_GetStoredInteger(constWizClickOk, 0);
        if($didclick)
        {
            $post = constButtonOk;
        }
    }
    $pno  = UTIL_GetStoredString('pno','');
    $pcn  = UTIL_GetStoredString('pcn','');

    $dbg   = UTIL_GetStoredInteger('debug',1);
    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?   1  : 0;
    $admin = @ ($user['priv_admin'])?   1  : 0;
    $debug = @ ($user['priv_debug'])? $dbg : 0;

    if (!$priv)
    {
        $txt = '||||||dbug|dmap|dcfg|twid|tpid|dgrp|dbet|dwfg|ddmp|'
             . '|ddmg|dmpg|dpgp|dclc|dhid|dvlp|ddpc|dclr|xxxx|sane|';
        if (matchOld($act,$txt))
        {
            $act = 'menu';
        }
    }

    $hid = UTIL_GetStoredInteger('hid',0);   // core.Census.id
    $cid = UTIL_GetStoredInteger('cid',0);   // core.Customers.id
    $tid = UTIL_GetStoredInteger('tid',0);   // core.MachineCategories.mcatid
    $gid = UTIL_GetStoredInteger('gid',0);   // core.MachineGroups.mgroupid
    $pid = UTIL_GetStoredInteger('pid',0);   // softinst.PatchConfig.pconfigid
    $wid = UTIL_GetStoredInteger('wid',0);   // softinst.WUConfig.id
    $kid = UTIL_GetStoredInteger('kid',0);
        // softinst.PatchCategories.pcategoryid
    $jid = UTIL_GetStoredInteger('jid',0);   // softinst.PatchGroups.pgroupid
    $sid = UTIL_GetStoredInteger('sid',0);
        // softinst.PatchStatus.patchstatusid
    $mid = UTIL_GetStoredInteger('mid',0);   // softinst.Patches.patchid
    $nid = UTIL_GetStoredInteger('nid',0);
        // softinst.PatchGroupMap.patchgroupmapid

    $dgid = UTIL_GetStoredInteger('dgid',0); // core.MachineGroups.mgroupid
    $dtid = UTIL_GetStoredInteger('dtid',0); // core.MachineCategories.mcatid
    $djid = UTIL_GetStoredInteger('djid',0); // softinst.PatchGroups.pgroupid
    $dkid = UTIL_GetStoredInteger('dkid',0);
        // softinst.PatchCategories.pcategoryid

    $custom = UTIL_GetStoredInteger('custom',0);

    $n_id   = UTIL_GetStoredInteger('notification_id', 0);
    $r_id   = UTIL_GetStoredInteger('report_id',       0);
    $a_id   = UTIL_GetStoredInteger('asset_id',        0);

    $n_act  = UTIL_GetStoredString('notification_act', '');
    $r_act  = UTIL_GetStoredString('report_act',       '');
    $a_act  = UTIL_GetStoredString('asset_act',        '');

    $pcat = find_pcat_kid($kid,$db);
    $pgrp = find_pgrp_jid($jid,$db);
    $act  = redirect_action($act,$frm,$post,$gid,$jid,$dgid,$djid);

   /*
    |  'No'
    |     cwid: create_wcfg
    |     cpid: create_pcfg
    |     fwid: check_wconfig
    |     fpid: check_pconfig
    |     epgc: edit_pgrp_conf
    |     grpb: add_pgrp_conf
    |     catb: add_pcat_conf
    |     dpca: del_pcat_conf
    |     pwid: del_wcfg_conf
    |     ppid: del_pcfg_conf
    |     cppb: copy_pcfg_cnf
    |     cpwb: copy_pcfg_cnf
    */

    if ($post == constButtonNo)
    {
        $act = $pno;
    }

   /*
    |  'Cancel'
    |     awid: add_wcfg
    |     apid: add_pcfg
    |     ewid: edit_wconfig
    |     epid: edit_pconfig
    |     fwid: form_wconfig
    |     fpid: form_pconfig
    |     cata: add_pcat_form
    |     grpa: add_pgrp_form
    |     epgf: edit_pgrp_form
    |     egps: edit_pgrp_search
    */

    if ($post == constButtonCan)
    {
        $act = $pcn;
    }

    $site  = find_site($cid,$auth,$db);
    $cat   = ($pcat)? $pcat['category']  : '';
    $grp   = ($pgrp)? $pgrp['name']      : '';
    $title = title($act,$cat,$grp);

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$auth,$nav,0,0,$db);

    /* Additional warning -- this helps prevent future configuration problems
        when new customers stray into this page and decide to tinker. */
    if ($act == 'lwid')
    {
        echo '<font size=+1><b>Please do not add a machine configuration for'
           . ' a machine, site, or user, unless you have a good reason for'
           . ' doing so.  If in doubt, please contact technical support.'
           . '</b></font>';
    }

    $date = datestring(time());

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    if ($debug) echo "<h2>$date</h2>";

    debug_array($debug,$_POST);

    db_change($GLOBALS['PREFIX'].'softinst',$db);
    check_patch_dirty($db);

    $tmp_env    = array();
    $env        = array();
    $env['db']  = $db;
    $env['pno'] = $pno;   // post 'No'
    $env['pcn'] = $pcn;   // post 'Cancel'
    $env['cid'] = $cid;   // core.Customers.id
    $env['hid'] = $hid;   // core.Census.id
    $env['tid'] = $tid;   // core.MachineCategories.mcatid
    $env['gid'] = $gid;   // core.MachineGroups.mgroupid
    $env['wid'] = $wid;   // softinst.WUConfig.id
    $env['pid'] = $pid;   // softinst.PatchConfig.pconfigid
    $env['kid'] = $kid;   // softinst.PatchCategories.pconfigid
    $env['jid'] = $jid;   // softinst.PatchGroups.pgroupid
    $env['sid'] = $sid;   // softinst.PatchStatus.patchstatusid
    $env['mid'] = $mid;   // softinst.Patches.patchid
    $env['act']  = $act;  // proposed action
    $env['frm']  = $frm;  // source page, if any

    $tmp_env = build_group_env();
    $env     = array_merge($env, $tmp_env);
    unset($tmp_env);

    $env['dgid'] = $dgid;
    $env['dtid'] = $dtid;
    $env['dkid'] = $dkid;
    $env['djid'] = $djid;
    $env['pcat'] = $pcat;
    $env['pgrp'] = $pgrp;
    $env['priv'] = $priv;
    $env['site'] = $site;
    $env['auth'] = $auth;
    $env['post'] = $post;
    $env['done'] = 0;
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['refr'] = server_var('HTTP_REFERER');
    $env['admn'] = $admin;
    $env['debug'] = $debug;
    $env['limit'] = UTIL_GetStoredInteger('limit',50);
    $env['custom'] = $custom;
    $env['addgroup'] = UTIL_GetStoredInteger('addgroup', 0);
    $env['delgroup'] = UTIL_GetStoredInteger('delgroup', 0);

   /* We track asset/event/notifications so we can return the user
    | to the report they were working on prior to selecting
    | configure groups. These variables must be included in l-grpw.php
    | and first declared here.
   */

    $env['notification_id']  = $n_id;
    $env['report_id']        = $r_id;
    $env['asset_id']         = $a_id;
    $env['notification_act'] = $n_act;
    $env['report_act']       = $r_act;
    $env['asset_act']        = $a_act;

    $env[constWizCheckboxUpdate] =
        UTIL_GetStoredInteger(constWizCheckboxUpdate, 0);
    $env[constWizCheckboxSP] =
        UTIL_GetStoredInteger(constWizCheckboxSP, 0);
    $env[constWizCheckboxRollup] =
        UTIL_GetStoredInteger(constWizCheckboxRollup, 0);
    $env[constWizCheckboxSecurity] =
        UTIL_GetStoredInteger(constWizCheckboxSecurity, 0);
    $env[constWizCheckboxCritical] =
        UTIL_GetStoredInteger(constWizCheckboxCritical, 0);

    $env[constWizCheckboxUpdateM] =
        UTIL_GetStoredInteger(constWizCheckboxUpdateM, 0);
    $env[constWizCheckboxSPM] =
        UTIL_GetStoredInteger(constWizCheckboxSPM, 0);
    $env[constWizCheckboxRollupM] =
        UTIL_GetStoredInteger(constWizCheckboxRollupM, 0);
    $env[constWizCheckboxSecurityM] =
        UTIL_GetStoredInteger(constWizCheckboxSecurityM, 0);
    $env[constWizCheckboxCriticalM] =
        UTIL_GetStoredInteger(constWizCheckboxCriticalM, 0);

    $env['wpgroupid'] = UTIL_GetStoredInteger('wpgroupid',0);
    $env['type'] = UTIL_GetStoredInteger('type',constPatchTypeAll);
    $env['mand'] = UTIL_GetStoredInteger('mand',0);
    $env['intprio'] = UTIL_GetStoredInteger('intprio',0);
    $env['limt'] = UTIL_GetStoredInteger('limt',1000);
    $env['page'] = UTIL_GetStoredInteger('p',0);
    $env['jump'] = '#table';
    $env['href'] = 'page_href';
    $env['ord'] = get_integer('o',0);
    $env['username' ] = $user['username'];

    /* The state of the wizard is controlled by $act.
    |  *This is not actually true. There are other
    |  state variables.
    |
    |  The content of the wizard and exit url is controlled
    |  by the pageEntry value.
    |
    */
    controlGroupState($act, $env, $db);

    echo head_standard_html_footer($auth,$db);
?>
