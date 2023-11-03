<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 5-Aug-05   EWB     Created
 8-Aug-05   EWB     delete, Add / Remove sites
 9-Aug-05   EWB     checks usage before removing host from group
 9-Aug-05   EWB     duplicate / remove group
12-Sep-05   BTE     Added checksum invalidation code.
15-Sep-05   BJS     Removed most code.
22-Sep-05   BJS     Working with l-grpw.php.
13-Oct-05   BJS     Added l-upnt.php.
28-Oct-05   BJS     When removing a group, also remove
                    its mgroupid from any Notification field
                    (group_suspend/include/exclude) that contains
                    it.
05-Nov-05   BTE     Restored revision history for config/groups.php.
08-Nov-05   BJS     Added event report id and action.
11-Nov-05   BJS     GRPW_return_title().
02-Dec-05   BJS     Added l-upar.php
22-Dec-05   BJS     Added next_cancel().
29-Dec-05   BJS     Added group name to title.
06-Jan-06   BJS     Added l-syst.php
26-Jan-06   BTE     Added l-core.php.
24-Feb-06   BTE     Removed check for census dirty.
11-Apr-06   BTE     Added include for l-gcfg.php.
17-Apr-06   BTE     Bug 3202: Group management server issues.
19-Apr-06   BTE     Bug 3204: Assorted text changes for group management.
20-Apr-06   BTE     Bug 3285: User interface group management issues from
                    emails.
24-May-06   BTE     Bug 3270: Fix titles throughout the Scrip configurator
                    interface.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.
04-Oct-07   BTE     Increased the size of the click here text.

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
include_once ( '../lib/l-head2.php' );
include_once ( '../lib/l-drty.php'  );
include_once ( '../lib/l-gdrt.php'  );
include_once ( '../lib/l-pdrt.php'  );
include_once ( '../lib/l-pcfg.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-dsyn.php'  );
include_once ( '../lib/l-grpw.php'  );
include_once ( '../lib/l-upnt.php'  );
include_once ( '../lib/l-uprp.php'  );
include_once ( '../lib/l-upar.php'  );
include_once ( '../lib/l-gcfg.php'  );
include_once ( '../lib/l-syst.php'  );
include_once ( '../lib/l-core.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( 'local.php' );


    function next_cancel($type)
    {
        $nxt = '';
        $can = '';
        switch($type)
        {
            case constNext : $nxt = button(constButtonNxt); break;
            case constOk   : $nxt = button(constButtonOk); break;
            case constCncl : $can = button(constButtonCan); break;
            case constBoth : $nxt = button(constButtonNxt);
                $can = button(constButtonCan); break;
        }
        return para("$nxt $can");
    }

    function GROUPS_GetTitle($custom, $act, $cat, $grp, $gid, $dgid, $db)
    {
        $begin = 'Group Management';
        switch($act)
        {
            case 'wmth':
                if(strcmp(get_string('sub',''), 'remv')==0)
                {
                    return "$begin - Remove a Group";
                }
                if($dgid!=0)
                {
                    $sql = "SELECT MachineGroups.name FROM MachineGroups "
                        . "WHERE mgroupid=$dgid";
                    $row = find_one($sql, $db);
                    if($row)
                    {
                        return "$begin - Edit a Group - Group \""
                            . $row['name'] . "\"";
                    }
                    else
                    {
                        return "$begin - Edit a Group";
                    }
                }
                return "$begin - Add a Group";
            default:        /* go on to the other cases */
        }
        if($gid==0)
        {
            return "$begin - Select or Add a Group";
        }
        return GRPW_return_title($custom, $act, $cat, $grp);
    }


   /*
    |  Main program
    */

    $now  = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();
    $act  = get_string('act','wmgs');
    $frm  = get_string('frm','');
    $post = get_string('button','');
    $pno  = get_string('pno','');
    $pcn  = get_string('pcn','');

    $dbg   = get_integer('debug',1);
    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?   1  : 0;
    $admin = @ ($user['priv_admin'])?   1  : 0;
    $debug = @ ($user['priv_debug'])? $dbg : 0;
    $isparent = get_integer('isparent',-1);

    if (!$priv)
    {
        $txt = '||||||dbug|dmap|dcfg|twid|tpid|dgrp|dbet|dwfg|ddmp|'
             . '|ddmg|dmpg|dpgp|dclc|dhid|dvlp|ddpc|dclr|xxxx|sane|';
        if (matchOld($act,$txt))
        {
            $act = 'menu';
        }
    }

    $hid = get_integer('hid',0);   // core.Census.id
    $cid = get_integer('cid',0);   // core.Customers.id
    $tid = get_integer('tid',0);   // core.MachineCategories.mcatid
    $gid = get_integer('gid',0);   // core.MachineGroups.mgroupid
    $pid = get_integer('pid',0);   // softinst.PatchConfig.pconfigid
    $wid = get_integer('wid',0);   // softinst.WUConfig.id
    $kid = get_integer('kid',0);   // softinst.PatchCategories.pcategoryid
    $jid = get_integer('jid',0);   // softinst.PatchGroups.pgroupid
    $sid = get_integer('sid',0);   // softinst.PatchStatus.patchstatusid
    $mid = get_integer('mid',0);   // softinst.Patches.patchid
    $nid = get_integer('nid',0);   // softinst.PatchGroupMap.patchgroupmapid

    $dgid = get_integer('dgid',0); // core.MachineGroups.mgroupid
    $dtid = get_integer('dtid',0); // core.MachineCategories.mcatid
    $djid = get_integer('djid',0); // softinst.PatchGroups.pgroupid
    $dkid = get_integer('dkid',0); // softinst.PatchCategories.pcategoryid

    // wizard customization
    $custom = get_integer('custom',0);
    // what is the current notification we are working with
    // and what action are we taking.
    $n_id  = get_integer('notification_id', 0);
    $n_act = get_string('notification_act', '');
    $r_id  = get_integer('report_id',       0);
    $r_act = get_string('report_act',      '');
    $a_id  = get_integer('asset_id',        0);
    $a_act = get_string('asset_act',      '');

   /*
    | The id of the notification(s), report(s), asset(s)
    | we want to remove may be a single value or comma seperated.
   */
    $rnid = get_string('rnid', 0);
    $enid = get_string('enid', 0);
    $anid = get_string('anid', 0);

    $pcat = find_pcat_kid($kid,$db);
    $pgrp = find_pgrp_jid($jid,$db);

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

    $permit = true;

    $mgroupuniq = get_string('mgroupuniq','');
    if($mgroupuniq)
    {
        $sql = 'SELECT username, mgroupid, mcatid FROM '.$GLOBALS['PREFIX'].'core.MachineGroups '
            . 'LEFT JOIN '.$GLOBALS['PREFIX'].'core.MachineCategories ON (MachineGroups.mcatuniq='
            . 'MachineCategories.mcatuniq) WHERE mgroupuniq=\''
            . $mgroupuniq . '\'';
        $row = find_one($sql, $db);
        if($row)
        {
            if($row['username']!=$user['username'])
            {
                if(!($user['priv_admin']))
                {
                    $permit = false;
                }
                if($act!='gdet')
                {
                    /* Not even admins can change the built in groups or
                        groups they do not own */
                    $permit = false;
                }
            }
            switch($act)
            {
            case 'gdet':
                $env['gid'] = $row['mgroupid'];
                $gid = $row['mgroupid'];
                break;
            default:
                $env['dgid'] = $row['mgroupid'];
                $env['tid'] = $row['mcatid'];
                $dgid = $row['mgroupid'];
                $tid = $row['mcatid'];
                break;
            }
        }
    }

    $site  = find_site($cid,$auth,$db);
    $cat   = ($pcat)? $pcat['category']  : '';
    //$grp   = ($pgrp)? $pgrp['name']    : '';
    $grp   = get_integer('dgid', 0);
    if ($grp)
    {
        /* get the group name to display in the page title */
        $sql = "SELECT name from ".$GLOBALS['PREFIX']."core.MachineGroups\n"
             . " WHERE mgroupid = $grp";
        $set = find_one($sql, $db);
        $grp = $set['name'];
    }


    //debug_note("grp($grp)");
    $title = GROUPS_GetTitle($custom, $act, $cat, $grp, $gid, $dgid, $db);

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo custom_html_header($title,$comp,$auth,'',0,0,0,
        '<LINK href="control.css" rel="stylesheet" type="text/css"> '
        . '<script type="text/javascript" language="JavaScript" src="'
        . '../report/control.js"></script>', $db);

    $date = datestring(time());

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    if ($debug) echo "<h2>$date</h2>";

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

   /*
    | We enter the wizard via groupControlState(),
    | so we get the value of the following variables
    | before that call. These include any 'configure groups'
    | links, etc.
   */
    $env['custom']                 = $custom;
    $env['notification_id']        = $n_id;
    $env['notification_act']       = $n_act;
    $env['notification_remove_id'] = $rnid;
    $env['report_id']              = $r_id;
    $env['report_act']             = $r_act;
    $env['report_remove_id']       = $enid;
    $env['asset_id']               = $a_id;
    $env['asset_act']              = $a_act;
    $env['asset_remove_id']        = $anid;

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
    $env['limit'] = get_integer('limit',50);
    $env['addgroup'] = get_integer('addgroup',0);
    $env['delgroup'] = get_integer('delgroup',0);
    $env['isparent'] = $isparent;

    if($permit)
    {
        controlGroupState($act, $env, $db);
    }
    else
    {
        echo 'You are not the owner of this group, so you cannot view or edit '
            . 'it.';
    }

    if(($gid!=0) && ($gid!=-1) && ($isparent!=-1))
    {
        $sql = 'SELECT mgroupuniq,name FROM '.$GLOBALS['PREFIX'].'core.MachineGroups WHERE '
            . "mgroupid=$gid";
        $row = find_one($sql, $db);
        if($row)
        {
            $mgroupuniq = $row['mgroupuniq'];
            $name = $row['name'];
            echo constFontSizeClickHere
                . 'Click <a href="#" onclick=" addDynamicItemButton('
                . $isparent . ',\'' . $mgroupuniq . '\',\'' . $name
                . '\');window.close();">here</a> to add this new group to the '
                . 'section you are defining.</font>';
        }
    }

    echo head_standard_html_footer($auth,$db);
?>
