<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Dec-02   EWB     Created
 8-Jan-03   EWB     Consistant with 3.0 navigation
13-Jan-03   EWB     Changed label 'config' --> 'configuration'
15-Jan-03   EWB     Implement new "configui" spec.
23-Jan-03   EWB     Removed $revl.
 6-Mar-03   NL      Removed exec code (calls to config_navigate & config_info
 6-Mar-03   NL      Remove calls to config_navigate() & config_info()
19-Mar-03   NL      Removed brackets around llink (moved to logout_link()).
15-Apr-03   NL      Remove user, sitefltr, date from local_inf; bold vers & machine
15-Apr-03   NL      Change local_inf font tag to CSS span class
29-Apr-03   EWB     Add a help link.
29-Apr-03   EWB     ... if the help file exists.
 1-May-03   EWB     Help link (scrphelp.htm) for config page.
 7-Jul-05   EWB     Update continue link for new user interface.
19-Jul-05   EWB     Don't add machines link unless we know the site.
13-Apr-06   BTE     Added config_wiz_navigate and associated constants.
14-Apr-06   BTE     Added wizard "level" tracking to some links.
03-May-06   BTE     Bug 3359: Add links to the status page.
24-Jul-06   BTE     Bug 3539: Minor text changes.

*/

    /* Formatting constants for config_wiz_navigate */
    define('constLinkFormatNavBar',     0);
    define('constLinkFormatList',       1);

    if (!isset($auth)) $auth = '';
    if (!isset($vers)) $vers = '';
    if (!isset($host)) $host = '';
    if (!isset($cid))  $cid  = 0;
    if (!isset($hid))  $hid  = 0;
    if (!isset($sid))  $sid  = 0;


    function config_navigate($cid,$hid,$sid)
    {
        $aa   = array( );
        $aa[] = html_link('status.php','status');
        $aa[] = html_link('index.php','sites');
        if ($cid > 0)
        {
            $href = "index.php?cid=$cid&act=host";
            $aa[] = html_link($href,'machines');
        }
        if ($hid > 0)
        {
            $href = "config.php?hid=$hid";
            $aa[] = html_link($href,'scrips');
            if ($sid <= 0)
            {
                $href = 'help/scrphelp.htm';
                if (file_exists($href))
                {
                    $aa[] = html_page($href,'help');
                }
            }
            else
            {
                $href = sprintf('help/s%05d.htm',$sid);
                if (file_exists($href))
                {
                    $aa[] = html_page($href,'help');
                }
                $href = "config.php?act=show&hid=$hid&sid=$sid";
                $aa[] = html_link($href,'continue');
            }
        }

        $msg = implode(" | \n",$aa);
        return "<b>configuration:</b> $msg<br><br>\n";
    }


    /* config_wiz_navigate

        Creates a set of links based on the current $env (environment) of a
        wizard using a predefined $format (one of the constants at the
        beginning of this file).

        Pass in text titles for the following:
            $wizLink - text for link back to site:wizards page.
            $groupsLink - text for link back to site:wizards:group Scrip config
            $scripsLink - text for link back to Scrip list for current group
            $contLink - a "continue" link while a Scrip is being edited
            $regLink - a link back to the regular Scrip configurator when a
                single machine group is being edited
            $addHelp - pass in 1 to display help links
            $addRegLink - pass in 1 to display regular Scrip config link
    */
    function config_wiz_navigate($env, $wizLink, $groupsLink, $scripsLink,
        $contLink, $regLink, $addHelp, $addRegLink, $statusLink, $format, $db)
    {
        $aa   = array( );
        $aa[] = html_link('index.php?act=wiz', $wizLink);
        $aa[] = html_link('status.php', $statusLink);
        $aa[] = html_link('index.php','sites');

        $href = 'scrpconf.php?custom=8';
        if(@$env['level'])
        {
            $href .= "&level=" . $env['level'];
        }
        $aa[] = html_link($href, $groupsLink);

        $mgroupid = 0;
        $mcatid = 0;

        /* Collect the relevant information based on our state */
        $act = 'selm';
        $scop = $env['scop'];
        if($scop==0)
        {
            $scop = $env['prev_scop'];
        }
        switch ($scop)
        {
            case constScopAll  : 
                $mgroupid = GRPS_ReturnAllMgroupid($db);
                $mcatid   = constScopAll;
                $grp_name = constGroupAll;
            break;
            case constScopSite : 
                if(($env['sgrp']) && ($env['sgrp']['mgroupid']))
                {
                    $mgroupid = $env['sgrp']['mgroupid'];
                    $mcatid   = $env['sgrp']['mcatid'];
                    $grp_name = $env['sgrp']['name'];
                }
                $act = 'scop';
            break;
            case constScopHost : 
                if(($env['hgrp']) && ($env['hgrp']['mgroupid']))
                {
                    $mgroupid = $env['hgrp']['mgroupid'];
                    $mcatid   = $env['hgrp']['mcatid'];
                    $grp_name = $env['hgrp']['name'];
                }
                break;
            default :
                if($env['mgroupid'])
                {
                    $mgroupid = $env['mgroupid']; 
                    $mcatid   = return_mcatid($mgroupid, $db);
                    $grp_name = return_mgroup_name($mgroupid, $db);
                }
            break;
        }
        $cid = $env['cid'];
        if($cid==0)
        {
            $cid = $env['prev_cid'];
        }
        $hid = $env['hid'];
        if($hid==0)
        {
            $hid = $env['prev_hid'];
        }

        if((!($mgroupid)) && ($env['mgroupid']))
        {
            $mgroupid = $env['mgroupid']; 
            $mcatid   = return_mcatid($mgroupid, $db);
            $grp_name = return_mgroup_name($mgroupid, $db);
        }

        if(($mgroupid) && ($mcatid))
        {
            switch($scop)
            {
            case constScopUser:
                /* For some reason, the URL has to be vastly different for
                    an user defined group. */
                $href = "scrpconf.php?act=wapp&scop=5&dtc=0&int=0&custom=8&"
                    . "notification_id=0&notification_act=&report_id=0&"
                    . "report_act=&asset_id=0&asset_act=&gid=$mgroupid&"
                    . "act=machine_selected&mgroupid=$mgroupid&scop=2";
            break;
            default:
                $href = "scrpconf.php?act=$act&cid=$cid&custom=8&scop=$scop&"
                    . "mgroupid=$mgroupid&mcatid=$mcatid&group_name=$grp_name&"
                    . "hid=$hid&pscop=$scop&site=&censusid=&snum=";
            break;
            }

            if(@$env['level'])
            {
                $href .= "&level=" . $env['level'];
            }
            $aa[] = html_link($href, $scripsLink);

            if($env['snum'])
            {
                $scrip = $env['snum'];
                if($addHelp)
                {
                    $href = sprintf('help/s%05d.htm',$env['snum']);
                    if (file_exists($href))
                    {
                        $aa[] = html_page($href,'help');
                    }
                }

                $href = "scrpconf.php?act=scrp&snum=$scrip&mgroupid=$mgroupid&"
                    . "mcatid=$mcatid&prev_hid=$hid&prev_cid=$cid&"
                    . "prev_scop=$scop&group_name=$grp_name";
                if(@$env['level'])
                {
                    $href .= "&level=" . $env['level'];
                }
                $aa[] = html_link($href, $contLink);

                if(($addRegLink) && ($hid))
                {
                    $href = "getconf.php?act=show&hid=$hid&sid=$scrip";
                    $aa[] = html_link($href, $regLink);
                }
            }
            else if($addHelp)
            {
                $href = 'help/scrphelp.htm';
                if (file_exists($href))
                {
                    $aa[] = html_page($href,'help');
                }
            }

        }

        switch($format)
        {
        case constLinkFormatNavBar:
            $msg = implode(" | \n",$aa);
            return "<b>configuration:</b> $msg<br><br>\n";
            break;
        case constLinkFormatList:
            $msg = implode("<li>",$aa);
            return "<li>$msg";
            break;
        default:
            return "";
            break;
        }
    }


    function config_info($auth,$vers,$host)
    {
        $msg = '';
        if ($vers) $msg .= "<b>version:</b> $vers<br>\n";
        if ($host) $msg .= "<b>machine:</b> $host<br>\n";
        return ($msg)? "<span class=\"footnote\">$msg</class>" : '';
    }


?>
