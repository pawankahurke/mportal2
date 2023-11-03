<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
20-Feb-06   NL      Create file. 
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/


    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)    
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( 'local.php'   );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-disp.php'  );

    function confirm_restart($itemtype,$itemid,$db)
    {
        if ($itemtype == constDisplayItemDisplay)
        {
            $self = server_var('PHP_SELF');
            $href = "$self?act=restartconf&itemtype=" . $itemtype . "&itemid=" . $itemid;
            $yes  = html_link($href,'Yes');
            $no   = html_link('display.php','No');
            $sql  = "SELECT name FROM Displays\n"
                  . "WHERE dispid = " . $itemid;
            $row  = find_one($sql,$db);
            $dispname = $row['name'];
            $msg  = "Are you sure you want to restart monitoring for display <b>" . $dispname . "</b>?<br>";
            $msg .= "<br>";
            $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        }
        else
        {
            $msg = "You must first select a display for which to restart monitoring.";
        }
        echo fontspeak($msg . '<br><br>');
    }
    
    /*----------------------------------------------------------\
    |  restart() is invoked when a user click on the            |
    |  'restart monitoring' action in the Display display.      |
    |  Restarting monitoring means setting Displays.starttime   |
    |  to the current time for that display (we would have      |
    |  liked to make this per user (i.e. DisplayUsers.starttime)|
    |  but can't, since the data is precomputed (via cron) for  |
    |  all displays, not computed on the fly.                   |
    |                                                           |
    \*---------------------------------------------------------*/
    function restart($itemtype,$itemid,$db)
    {
        if ($itemtype == constDisplayItemDisplay)
        {
            $dispid = $itemid;
            $now = time();
            $sql  = "UPDATE Displays\n"
                  . "SET starttime = " . $now . "\n"
                  . "WHERE dispid = " . $dispid;
            $res = redcommand($sql,$db);
            if (affected($res,$db) == 1)
            {
                $sql  = "SELECT name FROM Displays\n"
                      . "WHERE dispid = " . $dispid;
                $row  = find_one($sql,$db);
                $dispname = $row['name'];
                $log  = "monitoring restarted for display '$dispname' (dispid $dispid)";
                logs::log(__FILE__, __LINE__, $log,0);
                $msg  = "Monitoring has been restarted for display <b>$dispname</b>.<br>\n";
            }
            else
            {
                $msg = "Displayid <b>$dispid</b> does not exist.";
            }
        }
        else
        {
            $msg = "You must first select a display for which to restart monitoring.";
        }
        echo fontspeak($msg . '<br><br>');
    }

    function confirm_delete($itemtype,$itemid,$parent,$db,$catdispid=0)
    {
        $deletables = array(constDisplayItemMachineGroup,constDisplayItemMonItemGroup,
                            constDisplayItemSecurity, constDisplayItemResources);
        if ( in_array($itemtype,$deletables) )
        {
            $self = server_var('PHP_SELF');
            $href = "$self?act=deleteconf&itemtype=" . $itemtype . "&itemid=" 
                  . $itemid . "&parent=" . $parent . "&catdispid=" . $catdispid;
            $yes  = html_link($href,'Yes');
            $no   = html_link('display.php','No');

            switch ($itemtype)
            {
                case constDisplayItemMachineGroup :
                    $type = "machine group";
                    $sql  = "SELECT name FROM ".$GLOBALS['PREFIX']."core.MachineGroups WHERE mgroupid = " . $itemid;
                break;
                case constDisplayItemMonItemGroup :
                    $type = "monitored item group";
                    $sql  = "SELECT name FROM MonitorGroups WHERE mongroupid = " . $itemid;
                break;
                case constDisplayItemSecurity :
                    $type = "security item";
                    $sql  = "SELECT name FROM SecurityItems I, SecurityDisplay AS D\n"
                          . "WHERE I.secitemid=D.secitemid AND D.secdispid = " . $catdispid;
                break;
                case constDisplayItemResources :
                    $type = "resource item";
                    $sql = "SELECT name FROM ResourceItems I, ResourceDisplay AS D\n"
                          . "WHERE I.resitemid=D.resitemid AND D.resdispid = " . $catdispid;
                break;
            }
            $row  = find_one($sql,$db);
            $name = $row['name'];

            $msg  = "Are you sure you want to delete $type <b>" . $name . "</b>?<br>";
            $msg .= "<br>";
            $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        }
        else
        {
            $msg = "You must first select a machine group, monitored item group, "
                 . "security item, or resource item to delete.";
        }
        echo fontspeak($msg . '<br><br>');
    }
    
    /*----------------------------------------------------------\
    |  delete() is invoked when a user click on the             |
    |  'delete' action next to a machine or monitem group       |
    |  in the Display display or next to a resource or security |
    |  item in the Security or Resources display.               |
    |  For Display, it removes the group from the display, but  |
    |  also stops collecting information from this group (this  |
    |  involves updates to Scrip configuration).                |
    |  For Security or Resources display, it removes the        |
    |  security item or resource from being monitored on this   |
    |  machine in this display.                                 |
    |                                                           |
    \*---------------------------------------------------------*/
    
    // NINA: DELETE action only available to owner!
    // IMPORTANT: at the detail category level, $itemid and $parent 
    // are of corresponding machine; catdispid is of the detail category item
    function delete_act($itemtype,$itemid,$parent,$db,$catdispid=0)
    {
        switch ($itemtype)
        {
            case constDisplayItemMachineGroup :
                $type = "Machine group";
                $sql  = "SELECT name FROM ".$GLOBALS['PREFIX']."core.MachineGroups WHERE mgroupid = " . $itemid;
            break;
            case constDisplayItemMonItemGroup :
                $type = "Monitored item group";
                $sql  = "SELECT name FROM MonitorGroups WHERE mongroupid = " . $itemid;
            break;
            case constDisplayItemSecurity :
                $type = "Security item";
                $sql  = "SELECT name FROM SecurityItems I, SecurityDisplay AS D\n"
                      . "WHERE I.secitemid=D.secitemid AND D.secdispid = " . $catdispid;
            break;
            case constDisplayItemResources :
                $type = "Resource item";
                $sql = "SELECT name FROM ResourceItems I, ResourceDisplay AS D\n"
                      . "WHERE I.resitemid=D.resitemid AND D.resdispid = " . $catdispid;
            break;
        }
        $row  = find_one($sql,$db);
        $name = $row['name'];
            
            
        if ( $itemtype == constDisplayItemMachineGroup || $itemtype == constDisplayItemMonItemGroup )
        {   
            $sql = "SELECT itemid FROM Expansions\n"
                 . "WHERE expandid = " . $parent;
            $row = find_one($sql1,$db);
            $dispid = $row['itemid'];

            $table = ($itemtype == constDisplayItemMachineGroup) ? 
                    'DisplayMachineGroups' :  
                    'DisplayMonitorGroups' ;
                    
            $sql = "DELETE FROM $table\n"
                 . "WHERE dispid = " . $dispid . "\n"
                 . "AND mgroupid = " . $itemid;
            $res = redcommand($sql2,$db);
            $r   = affected($res2,$db);
            
            /*NINA: need to "stop collecting information from this group
              (this involves updates to Scrip configuration)"
             but this group could be in more than 1 display....
             so, maybe only stop collecting if not in any displays?
             What does "stop collecting information from this group
              (this involves updates to Scrip configuration)" mean?
            I think it means:
            FOR MACHINE GROUP:
                Delete record from ResourceItems for this mgroupid,
                Delete corresponding* records from ResourceDisplay (*resitemids deleted above) 
                Delete corresponding* records from Schedule (*resitemids deleted above) 
                Repeat above for Profile, Sec, Events, Maintenance, AND MONITEMS??  !! 
            FOR MONITEM GROUP:
                Delete record from MonitorGroups for this mongroupid,
                Delete corresponding records from MonitorGroupMap
             */   
            $sql2 = ""; //?
            $res2 = redcommand($sql3,$db);
            $r2 = affected($res3,$db);
        } 
        elseif ( $itemtype == constDisplayItemSecurity )
        {
            $sql= "DELETE FROM SecurityDisplay\n"
                . "WHERE secitemid = " . $catdispid;
            $res = redcommand($sql,$db);
            $r = affected($res,$db);
        }
        elseif ( $itemtype == constDisplayItemResources )
        {
            $sql= "DELETE FROM ResourceDisplay\n"
                . "WHERE resdispid = " . $catdispid;
            $res = redcommand($sql,$db);
            $r = affected($res,$db);
        }
        else
        {
            $msg = "You must first select a machine group, monitored item group, "
                 . "security item, or resource item to delete.";
        }
        
        
        if ($r == 1)
        {
            if ( $itemtype == constDisplayItemMachineGroup || $itemtype == constDisplayItemMonItemGroup )
            {
                $sql  = "SELECT name FROM Displays\n"
                      . "WHERE dispid = " . $dispid;
                $row  = find_one($sql,$db);
                $dispname = $row['name'];
                
                $log  = "$type '$name' (id $itemid) deleted from display $dispname (dispid $dispid)";
                $msg  = "$type <b>$name</b> has been deleted from display $dispname.<br>\n";
            }
            else
            {
                $log  = "$type '$name' (id $itemid) deleted.";
                $msg  = "$type <b>$name</b> has been deleted.<br>\n";
            }
            logs::log(__FILE__, __LINE__, $log,0);
        }
        else
        {
            $msg = $type . " <b>$name</b> does not exist.";
        }

        echo fontspeak($msg . '<br><br>');
    }

       /*
    |  Main program
    */
    
    /* Perform authentication */
    $db = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();
    
    $now = time();

    $user  = user_data($authuser,$db);
    $debug = @ ($user['priv_debug'])? 1 : 0;
    $admin = @ ($user['priv_admin'])? 1 : 0;
    $userid =  ($user['userid']);
    $username = ($user['username']);

    define('constDisplayItemDisplay',       1);
    define('constDisplayItemMachineGroup',  2);
    define('constDisplayItemMonItemGroup',  3);
    define('constDisplayItemMachine',       4);
    define('constDisplayItemMonItem',       5);
    define('constDisplayItemProfile',       6);
    define('constDisplayItemSecurity',      7);
    define('constDisplayItemResources',     8);
    define('constDisplayItemEvents',        9);
    define('constDisplayItemMaintenance',  10);
            
    $act        = get_string('act','select');
    $itemtype   = get_integer('itemtype', 0);
    $itemid     = get_integer('itemid',   0);
    $expandid   = get_integer('expandid', 0);
    $parent     = get_integer('parent',   0);
    $catdispid  = get_integer('catdispid', 0);

    $env                = array( );
    $env['self']        = server_var('PHP_SELF');
    $env['args']        = server_var('QUERY_STRING');
    $env['db']          = $db;
    $env['userid']      = $userid;
    $env['username']    = $username;
    $env['act']         = $act;
    $env['itemtype']    = $itemtype;
    $env['itemid']      = $itemid;
    $env['expandid']    = $expandid;
    $env['parent']      = $parent;
    $env['catdispid']   = $catdispid;
    
    
    db_change($GLOBALS['PREFIX'].'dashboard',$db);

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer) 
    
    $title = "ASI Site and System Status Dashboard";    
    echo standard_html_header($title,$comp,$authuser,0,0,0,$db);

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    if($act == 'delete')        confirm_delete($itemtype,$itemid,$parent,$db,$catdispid);
    if($act == 'deleteconf')    delete_act($itemtype,$itemid,$parent,$db,$catdispid);
    if($act == 'restart')       confirm_restart($itemtype,$itemid,$db);
    if($act == 'restartconf')   restart($itemtype,$itemid,$db);

    echo head_standard_html_footer($authuser,$db);
